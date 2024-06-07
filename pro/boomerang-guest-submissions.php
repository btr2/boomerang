<?php
/**
 * Guest Submission Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks to see if guest submissions are enabled for our board.
 *
 * @param $board
 *
 * @return false|mixed
 */
function boomerang_board_guest_boomerangs_enabled( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_guest_boomerangs'] ?? false;
}

/**
 * Checks to see if we should ask for a name for guest submissions to our board.
 *
 * @param $board
 *
 * @return false|mixed
 */
function boomerang_board_guest_boomerangs_request_name_enabled( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_guest_boomerangs_name_request'] ?? false;
}

/**
 * Checks to see if we should ask for an email address for guest submissions to our board.
 *
 * @param $board
 *
 * @return false|mixed
 */
function boomerang_board_guest_boomerangs_request_email_enabled( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_guest_boomerangs_email_request'] ?? false;
}

/**
 * Checks to see what criteria is required for guest submissions.
 *
 * @param $board
 *
 * @return false|mixed
 */
function boomerang_board_guest_boomerangs_criteria( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_guest_boomerang_criteria'] ?? false;
}

/**
 * Checks to see if guest voting is enabled for our board.
 *
 * @param $board
 *
 * @return false|mixed
 */
function boomerang_board_guest_voting_enabled( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_guest_voting'] ?? false;
}

/**
 * Checks to see what criteria is required for guest voting.
 *
 * @param $board
 *
 * @return false|mixed
 */
function boomerang_board_guest_voting_criteria( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_guest_voting_criteria'] ?? false;
}

/** Name and Email **/

/**
 * Adds a name and email input field to the form if guest submissions enabled and user is a guest.
 * These will be saved as meta for the Boomerang.
 *
 * @param $board
 *
 * @return void
 */
function add_name_and_email_to_form( $board ) {
	// If user logged in, or guest subs disabled, just bail.
	if ( is_user_logged_in() || ! boomerang_board_guest_boomerangs_enabled( $board ) ) {
		return;
	}

	do_action( 'boomerang_above_guest_name_field' );

	if ( boomerang_board_guest_boomerangs_request_name_enabled( $board ) ) : ?>
		<fieldset>
			<label for="guest_name"><?php esc_html_e( 'Display Name', 'boomerang' ); ?></label>
			<input type="text" id="boomerang-guest-name" value="" tabindex="1" size="20" name="guest_name"/>
		</fieldset>
	<?php endif;

	do_action( 'boomerang_below_guest_name_field' );

	do_action( 'boomerang_above_guest_email_field' );

	if ( boomerang_board_guest_boomerangs_request_email_enabled( $board ) ) : ?>
		<fieldset>
			<label for="guest_email"><?php esc_html_e( 'Email Address', 'boomerang' ); ?></label>
			<input type="text" id="boomerang-guest-email" value="" tabindex="1" size="20" name="guest_email"/>
		</fieldset>
	<?php endif;

	do_action( 'boomerang_below_guest_email_field' );
}
add_filter( 'boomerang_form_fields_start', __NAMESPACE__ . '\add_name_and_email_to_form' );

/**
 * Saves a given guest name or guest email as meta.
 *
 * @param $post_id
 * @param $board
 *
 * @return void
 */
function save_name_and_email( $post_id, $board ) {
	if ( ! empty( $_POST['guest_name'] ) ) {
		update_post_meta( $post_id, 'guest_user_name', sanitize_text_field( $_POST['guest_name'] ) );
	}

	if ( ! empty( $_POST['guest_email'] ) ) {
		update_post_meta( $post_id, 'guest_user_email', sanitize_text_field( $_POST['guest_email'] ) );
	}
}
add_action( 'boomerang_new_boomerang', __NAMESPACE__ . '\save_name_and_email', 10, 2 );

function filter_guest_user_name( $string, $post ) {
	$guest_boomerang = get_post_meta( $post->ID, 'guest_created', true );

	if ( $guest_boomerang ) {
		$guest_name = get_post_meta( $post->ID, 'guest_user_name', true );

		if ( $guest_name ) {
			$string = $guest_name;
		} else {
			$string = esc_html__( 'Anonymous User', 'boomerang' );
		}

		if ( current_user_can( 'manage_options' ) ) {
			$guest_email = get_post_meta( $post->ID, 'guest_user_email', true );

			if ( $guest_email ) {
				$string .= ' (' . $guest_email . ')';
			}
		}
	}

	return $string;
}
add_filter( 'boomerang_posted_by_string', __NAMESPACE__ . '\filter_guest_user_name', 10, 2 );

/** Query Vars **/

function add_boomerang_query_var( $vars ) {
	$vars[] = "boo_auth";

	return $vars;
}
add_filter( 'query_vars', __NAMESPACE__ . '\add_boomerang_query_var' );

/** IP Address Management *********************************************************************************************/

function boomerang_get_ip() {
	if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else {
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = ( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		} else {
			$ip = ( $_SERVER['REMOTE_ADDR'] );
		}
	}

	return $ip;
}

/** Permissions **/

function allow_guest_submissions( $permission, $board, $user_id ) {
	// Site admins can always post.
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}

	if ( ! boomerang_board_guest_boomerangs_enabled( $board ) ) {
		return $permission;
	}

	// Guest submissions enabled. Let's check our criteria.
	$criteria = boomerang_board_guest_boomerangs_criteria( $board );

	if ( empty( $criteria ) ) {
		// Guest submissions enabled. No criteria. Unconditional submissions accepted.
		return true;
	}

	if ( in_array( 'ip', $criteria ) ) {
		// We need to check IP
		$current_ip = boomerang_get_ip();
		$ignore_localhost = apply_filters( 'boomerang_ignore_localhost_ip_filtering', true );
		if ( ! $ignore_localhost || ( '127.0.0.1' !== $current_ip && '::1' !== $current_ip ) ) {
			// Get our boomerang_guest user
			$guest_user = get_user_by( 'login', 'boomerang_guest' );
			$ip_array   = get_user_meta( $guest_user->ID, 'boomerang_submission_ips', true );
			if ( in_array( $current_ip, $ip_array ) ) {
				return array(
					'message' => esc_html__( 'You have already made a submission', 'boomerang' )
				);
			}
		}
	}

	if ( in_array( 'params', $criteria ) ) {
		$auth = get_query_var( 'boo_auth' );

		if ( empty( $auth ) || $board !== $auth ) {
			// User isn't using the right parameter and cannot submit
			return array(
				'message' => esc_html__( 'You are not able to make a submission', 'boomerang' )
			);
		}
	}

	return true;
}
add_filter( 'boomerang_user_can_submit', __NAMESPACE__ . '\allow_guest_submissions', 10, 3 );

/** Housekeeping ******************************************************************************************************/

function post_submission_housekeeping( $post_id, $board ) {
	$guest_user  = get_user_by( 'login', 'boomerang_guest' );
	$author_id = get_post_field( 'post_author', $post_id );

	if ( $guest_user && $author_id == 0 ) {
		// Post was created by our anonymous guest user

		// Guest submissions enabled. Let's check our criteria.
		$criteria = boomerang_board_guest_boomerangs_criteria( $board );
		if ( ! empty( $criteria ) && in_array( 'ip', $criteria ) ) {
			// Guest user IP tracking is enabled
			$author_ip = boomerang_get_ip();
			$ip_array   = get_user_meta( $guest_user->ID, 'boomerang_submission_ips', true );
			if ( ! in_array( boomerang_get_ip(), $ip_array ) ) {
				$ip_array[] = $author_ip;
				update_user_meta( $guest_user->ID, 'boomerang_submission_ips', $ip_array );
			}

			// Add the IP to the Boomerang
			update_post_meta( $post_id, 'author_ip', $author_ip );
		}

		// Add Boomerang to list
		$id_array = get_user_meta( $guest_user->ID, 'boomerang_ids', true );
		$id_array[] = $post_id;
		update_user_meta( $guest_user->ID, 'boomerang_ids', $id_array );

		// Update Boomerang to reflect new author
		$post_args = array(
			'ID' => $post_id,
			'post_author' => $guest_user->ID,
		);
		wp_update_post( $post_args );

		// Mark Boomerang as guest created.
		update_post_meta( $post_id, 'guest_created', true );
	}
}
add_action( 'boomerang_new_boomerang', __NAMESPACE__ . '\post_submission_housekeeping', 10, 2 );

/** Voting ************************************************************************************************************/

/**
 * If guest voting is enabled, we need to open up the voting container.
 * We could test each container for criteria, but this would use up a lot of server resources (checking IPs etc).
 * Therefore, we open up all containers, and then test when each vote is processed.
 *
 * @param $can_vote
 * @param $board
 * @param $user_id
 *
 * @return mixed|true
 */
function open_voting_container( $can_vote, $board, $user_id ) {
	if ( boomerang_board_guest_voting_enabled( $board ) ) {
		return true;
	}

	return $can_vote;
}
add_filter( 'boomerang_user_can_vote', __NAMESPACE__ . '\open_voting_container', 10, 3 );


/**
 * Process each vote as they come in, based on guest voting criteria.
 *
 * @param $can_vote
 * @param $post_id
 * @param $user_id
 *
 * @return array|mixed|true
 */
function process_guest_voting( $can_vote, $post_id, $user_id ) {
	if ( is_user_logged_in() ) {
		return $can_vote;
	}

	$post = get_post( $post_id );
	$board = $post->post_parent;
	$labels = boomerang_get_labels( $board );

	if ( ! boomerang_board_guest_voting_enabled( $board ) ) {
		return $can_vote;
	}

	// Guest voting enabled. Let's check our criteria.
	$criteria = boomerang_board_guest_voting_criteria( $board );

	if ( empty( $criteria ) ) {
		/**
		 * Guest voting enabled. No criteria. Unconditional voting accepted.
		 * As there is no user to tie a vote to, the user can vote multiple times for the same Boomerang.
		 * This is not recommended.
		 */
		return true;
	}

	if ( in_array( 'ip', $criteria ) ) {
		// We need to check IP
		$current_ip = boomerang_get_ip();
		$ignore_localhost = apply_filters( 'boomerang_ignore_localhost_ip_filtering', true );
		if ( ! $ignore_localhost || ( '127.0.0.1' !== $current_ip && '::1' !== $current_ip ) ) {
			// Get our boomerang_guest user
			$guest_user = get_user_by( 'login', 'boomerang_guest' );
			$ip_array   = get_user_meta( $guest_user->ID, 'boomerang_voted_ips', true );
			if ( in_array( $current_ip, $ip_array ) ) {
				// This user has voted on a Boomerang, using this IP. Let's find out which one.
				$data   = get_user_meta( $guest_user->ID, 'boomerang_vote_data', true );
				foreach ( $data as $ip => $posts ) {
					if ( $current_ip === $ip ) {
						if ( in_array( $post_id, $posts ) ) {
							return array(
								'message' => $labels['already_voted']
							);
						}
					}
				}
			}
		}
	}

	if ( in_array( 'time', $criteria ) ) {
		$votedata = get_post_meta( $post_id, 'boomerang_vote_data', true );

		if ( ! empty( $votedata ) ) {
			// We need to check IP
			$current_ip = boomerang_get_ip();

			foreach ( $votedata as $vote => &$data ) {
				if ( empty($data['ip']) || $current_ip !== $data['ip'] ) {
					unset($votedata[$vote]);
				}
			}

			if ( ! empty( $votedata ) ) {
				$vote = end($votedata)['datetime']->getTimestamp();
				$gap = boomerang_board_get_guest_vote_time_gap( $board );
				$diff = '-' . $gap . ' minutes';

				if ( $vote >= strtotime( $diff ) ) {
					return array(
						'message' =>  esc_html__( 'You need to wait longer before voting again', 'boomerang' )
					);
				}
			}
		}
	}

	return true;
}
add_filter( 'boomerang_process_vote_before', __NAMESPACE__ . '\process_guest_voting', 10, 3 );

function post_vote_housekeeping( $post_id, $board, $user_id ) {
	$guest_user  = get_user_by( 'login', 'boomerang_guest' );

	if ( $guest_user && $user_id == 0 ) {
		// Post was created by our anonymous guest user

		// Guest voting enabled. Let's check our criteria.
		$criteria = boomerang_board_guest_voting_criteria( $board );
		if ( ! empty( $criteria ) && in_array( 'ip', $criteria ) ) {
			// Guest user IP tracking is enabled
			$voter_ip = boomerang_get_ip();
			$ip_array   = get_user_meta( $guest_user->ID, 'boomerang_voted_ips', true );
			if ( ! in_array( $voter_ip, $ip_array ) ) {
				// Voter has never voted from this IP address
				$ip_array[] = $voter_ip;
				update_user_meta( $guest_user->ID, 'boomerang_voted_ips', $ip_array );

				// Add to the main data row too
				$data = get_user_meta( $guest_user->ID, 'boomerang_vote_data', true );
				if ( array_key_exists( $voter_ip, $data ) ) {
					$posts = $data[$voter_ip];
					if ( ! in_array( $post_id, $posts ) ) {
						$posts[] = $post_id;
						$data[$voter_ip] = $posts;
						update_user_meta( $guest_user->ID, 'boomerang_vote_data', $data );
					}
				} else {
					$data[$voter_ip] = array( $post_id );
					update_user_meta( $guest_user->ID, 'boomerang_vote_data', $data );
				}
			} else {
				// Voter has already voted for a Boomerang from this IP
				$data = get_user_meta( $guest_user->ID, 'boomerang_vote_data', true );
				$posts = $data[$voter_ip];

				if ( ! in_array( $post_id, $posts ) ) {
					$posts[] = $post_id;
					$data[$voter_ip] = $posts;
					update_user_meta( $guest_user->ID, 'boomerang_vote_data', $data );
				}
			}
		}
	}
}
add_filter( 'boomerang_new_vote', __NAMESPACE__ . '\post_vote_housekeeping', 10, 3 );

function record_guest_vote( $newdata, $post_id, $board, $score ) {
	if ( ! boomerang_board_guest_voting_enabled( $board ) ) {
		return $newdata;
	}

	if ( 0 == $newdata['user'] ) {
		// user 0 is a non-logged-in user. Let's change that to our real guest user.
		$guest_user  = get_user_by( 'login', 'boomerang_guest' );
		$newdata['user'] = $guest_user->ID;
		$newdata['anon'] = true;
		$voter_ip = boomerang_get_ip();
		$newdata['ip'] = $voter_ip;
	}



	return $newdata;
}
add_filter( 'boomerang_vote_data', __NAMESPACE__ . '\record_guest_vote', 10, 4 );
