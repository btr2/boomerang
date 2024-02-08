<?php
/**
 * Defines all functionality relating to voting.
 */
namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles our voting system.
 */
class Boomerang_Votes {
	/**
	 * Define the core functionality of our voting system.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Decouple our hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wp_ajax_process_vote', array( $this, 'process_vote' ) );
		add_action( 'wp_ajax_nopriv_process_vote', array( $this, 'process_vote' ) );
	}

	/**
	 * Ajax handler for processing vote events.
	 *
	 * @return void
	 */
	public function process_vote() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['boomerang_process_vote'] ) ), 'boomerang_process_vote' ) ) {
			$error = new WP_Error(
				'Boomerang: Failed Security Check on Vote Submission',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );
		}

		$post_id  = sanitize_text_field( $_POST['post_id'] );
		$modifier = sanitize_text_field( $_POST['modifier'] );
		$current  = intval( get_post_meta( $post_id, 'boomerang_votes', true ) ?? 0 );
		$post     = get_post( $post_id );
		$can_vote = false;

		if ( is_user_logged_in() ) {
			$can_vote = $this->user_can_vote( $post_id, $modifier );
		}

		/**
		 * Filter whether a user can vote, immediately before processing begins.
		 */
		$can_vote = apply_filters( 'boomerang_process_vote_before', $can_vote, $post_id, get_current_user_id() );

		if ( true === $can_vote ) {
			switch ( $modifier ) {
				case '1':
					++ $current;
					$current = apply_filters( 'boomerang_upvoted', $current, $post_id );
					break;
				case '-1':
					-- $current;
					$current = apply_filters( 'boomerang_downvoted', $current, $post_id );
					break;
			}

			$labels  = boomerang_get_labels( $post->post_parent );
			$message = $labels['message_vote_recorded'];

			$this->record_vote( $post_id, $post->post_parent, get_current_user_id(), $modifier, $current );

			do_action( 'boomerang_new_vote', $post_id, $post->post_parent, get_current_user_id() );
		} else {
			$message = $can_vote['message'];
		}

		$content = boomerang_get_votes_html( $post );

		$return = array(
			'message' => $message,
			'count'   => $current,
			'content' => boomerang_get_votes_html( get_post( $post_id ) ),
		);

		wp_send_json_success( $return );

		wp_die();
	}

	/**
	 * Checks to see if the logged-in user can vote on a Boomerang.
	 *
	 * @param $post_id
	 * @param $modifier
	 *
	 * @return array|bool
	 */
	public function user_can_vote( $post_id, $modifier ) {
		$user_id = get_current_user_id();
		$result  = false;
		$post    = get_post( $post_id );
		$board   = $post->post_parent;
		$labels  = boomerang_get_labels( $board );

		// get the votes array from user's meta.
		$user_votes = get_user_meta( get_current_user_id(), 'boomerang_user_votes', true ) ?? array();

		// check to see if user has already voted on this Boomerang.
		if ( empty( $user_votes ) ) {
			// empty array - user hasn't voted yet.
			$user_votes = array();

			$user_votes[ $post_id ] = $modifier;
			update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
			$result = true;
		} else {
			$vote_status = array_key_exists( $post_id, $user_votes ) ? $user_votes[ $post_id ] : 0;

			if ( ! boomerang_board_downvoting_enabled( $post_id ) ) {
				switch ( $vote_status ) {
					case '1':
						if ( '1' === $modifier ) {
							$result = array(
								'message' => $labels['already_voted'],
							);
						} elseif ( '-1' === $modifier ) {
							$user_votes[ $post_id ] = '0';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							$result = true;
						}
						break;
					case '0':
						if ( '1' === $modifier ) {
							$user_votes[ $post_id ] = '1';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							$result = true;
						} elseif ( '-1' === $modifier ) {
							$result = array(
								'message' => $labels['already_voted'],
							);
						}
						break;
				}
			} else {
				switch ( $vote_status ) {
					case '1':
						if ( '1' === $modifier ) {
							$result = array(
								'message' => $labels['already_voted'],
							);
						} elseif ( '-1' === $modifier ) {
							$user_votes[ $post_id ] = '0';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							$result = true;
						}
						break;
					case '0':
						if ( '1' === $modifier ) {
							$user_votes[ $post_id ] = '1';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							$result = true;
						} elseif ( '-1' === $modifier ) {
							$user_votes[ $post_id ] = '-1';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							$result = true;
						}
						break;
					case '-1':
						if ( '1' === $modifier ) {
							$user_votes[ $post_id ] = '0';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							$result = true;
						} elseif ( '-1' === $modifier ) {
							$result = array(
								'message' => $labels['already_voted'],
							);
						}
						break;
				}
			}
		}

		return $result;
	}

	/**
	 * Create a record of the vote as Boomerang metadata.
	 *
	 * @param $post_id
	 * @param $board_id
	 * @param $user_id
	 * @param $modifier
	 * @param $score
	 *
	 * @return void
	 */
	public function record_vote( $post_id, $board_id, $user_id, $modifier, $score ) {
		// Add metadata to record the latest number of votes.
		update_post_meta( $post_id, 'boomerang_votes', $score );

		// Build an array of votedata
		$newdata = array(
			'user'     => intval( $user_id ),
			'datetime' => current_datetime(),
			'modifier' => intval( $modifier ),
		);

		$newdata = apply_filters( 'boomerang_vote_data', $newdata, $post_id, $board_id, $score );

		$current_data = get_post_meta( $post_id, 'boomerang_vote_data', true );
		if ( empty( $current_data ) ) {
			$current_data = array();
		}

		$current_data[] = $newdata;

		update_post_meta( $post_id, 'boomerang_vote_data', $current_data );
	}
}
