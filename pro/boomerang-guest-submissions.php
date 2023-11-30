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
add_filter( 'boomerang_can_user_submit', __NAMESPACE__ . '\allow_guest_submissions', 10, 3 );

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
	}
}
add_filter( 'boomerang_new_boomerang', __NAMESPACE__ . '\post_submission_housekeeping', 10, 2 );

