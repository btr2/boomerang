<?php

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

		// add_filter( 'boomerang_upvoted', array( $this, 'user_has_upvoted' ), 10, 2 );
		// add_filter( 'boomerang_downvoted', array( $this, 'user_has_downvoted' ), 10, 2 );
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

		$can_vote = $this->user_can_vote( $post_id, $modifier );

		if ( true === $can_vote ) {switch ( $modifier ) {
				case '1':
					++ $current;
					$current = apply_filters( 'boomerang_upvoted', $current, $post_id );
					break;
				case '-1':
					-- $current;
					$current = apply_filters( 'boomerang_downvoted', $current, $post_id );
					break;
			}

			$message = '';

			update_post_meta( $post_id, 'boomerang_votes', $current );
		} else {
			$message = $can_vote;
		}

		$post = get_post( $post_id );

		$content = boomerang_get_votes_html( $post );

		$return = array(
			'message' => $message,
			'count'   => $current,
			'content' => boomerang_get_votes_html( get_post( $post_id ) ),
		);

		wp_send_json_success( $return );

		wp_die();
	}

	public function user_can_vote( $post_id, $modifier ) {
		$user_id = get_current_user_id();

		// get the votes array from user's meta.
		$user_votes = get_user_meta( get_current_user_id(), 'boomerang_user_votes', true ) ?? array();

		// check to see if user has already voted on this Boomerang.
		if ( empty( $user_votes ) ) {
			// empty array - user hasn't voted yet.
			$user_votes = array();

			$user_votes[ $post_id ] = $modifier;
			update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
			return true;
		} else {
			$vote_status = array_key_exists( $post_id, $user_votes ) ? $user_votes[ $post_id ] : 0;

			if ( ! boomerang_board_downvoting_enabled( $post_id ) ) {
				switch ( $vote_status ) {
					case '1':
						if ( '1' === $modifier ) {
							return 'Already voted';
						} elseif ( '-1' === $modifier ) {
							$user_votes[ $post_id ] = '0';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							return true;
						}
						break;
					case '0':
						if ( '1' === $modifier ) {
							$user_votes[ $post_id ] = '1';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							return true;
						} elseif ( '-1' === $modifier ) {
							return 'Already voted';
						}
						break;
				}
			} else {
				switch ( $vote_status ) {
					case '1':
						if ( '1' === $modifier ) {
							return 'Already voted';
						} elseif ( '-1' === $modifier ) {
							$user_votes[ $post_id ] = '0';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							return true;
						}
						break;
					case '0':
						if ( '1' === $modifier ) {
							$user_votes[ $post_id ] = '1';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							return true;
						} elseif ( '-1' === $modifier ) {
							$user_votes[ $post_id ] = '-1';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							return true;
						}
						break;
					case '-1':
						if ( '1' === $modifier ) {
							$user_votes[ $post_id ] = '0';
							update_user_meta( $user_id, 'boomerang_user_votes', $user_votes );
							return true;
						} elseif ( '-1' === $modifier ) {
							return 'Already voted';
						}
						break;
				}
			}
		}
	}

	/**
	 * Fires when a user has upvoted, so we can check they are allowed to.
	 *
	 * @param $post_id WP_Post Boomerang
	 * @param $current int     The current number of votes, including the change
	 *
	 * @return void
	 */
	public function user_has_upvoted( $post_id, $current ) {
		// // get the votes array from user's meta.
		// $user_votes = get_user_meta( get_current_user_id(), 'boomerang_user_votes' ) ?? array();
		//
		// // check to see if user has already voted on this Boomerang.
		// if ( empty( $user_votes ) ) {
		// 	// empty array - user hasn't voted on any Boomerangs. Return the current back to the Boomerang,
		// 	// and update the vote array.
		// 	$user_votes[ $post_id ] = 'up';
		// 	update_user_meta( get_current_user_id(), 'boomerang_user_votes', $user_votes );
		// 	return $current;
		// } else if (array_key_exists($post_id)) {
		//
		// }
	}

	/**
	 * Fires when a user has upvoted, so we can check they are allowed to.
	 *
	 * @param $post_id WP_Post Boomerang
	 * @param $current int     The current number of votes, including the change
	 *
	 * @return void
	 */
	public function user_has_downvoted( $post_id, $current ) {
		// // get the votes array from user's meta.
		// $user_votes = get_user_meta( get_current_user_id(), 'boomerang_user_votes' ) ?? array();
		//
		// // check to see if user has already voted on this Boomerang.
		// if ( empty( $user_votes ) ) {
		// 	// empty array - user hasn't voted on any Boomerangs. Return the current back to the Boomerang,
		// 	// and update the vote array.
		// 	$user_votes[ $post_id ] = 'up';
		// 	return $current;
		// }
	}
}
