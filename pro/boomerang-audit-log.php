<?php
/**
 * Audit Log Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function create_audit_log_entry( $post_id ) {
	$commentdata = array(
		'user_id'         => get_current_user_id(),
		'comment_post_ID' => $post_id,
		'comment_meta'    => array(
			'boomerang_private_note' => true,
			'system_note'            => current_action(),
		),
	);

	switch ( current_action() ) {
		case 'boomerang_marked_as_bug':
			$commentdata['comment_content'] = esc_html__( 'Marked as a bug', 'boomerang' );
			break;
		case 'boomerang_unmarked_as_bug':
			$commentdata['comment_content'] = esc_html__( 'Unmarked as a bug', 'boomerang' );
			break;
		case 'boomerang_locked':
			$commentdata['comment_content'] = esc_html__( 'Locked', 'boomerang' );
			break;
		case 'boomerang_unlocked':
			$commentdata['comment_content'] = esc_html__( 'Unlocked', 'boomerang' );
			break;
	}

	wp_insert_comment( $commentdata );

}
add_action( 'boomerang_marked_as_bug', __NAMESPACE__ . '\create_audit_log_entry' );
add_action( 'boomerang_unmarked_as_bug', __NAMESPACE__ . '\create_audit_log_entry' );
add_action( 'boomerang_locked', __NAMESPACE__ . '\create_audit_log_entry' );
add_action( 'boomerang_unlocked', __NAMESPACE__ . '\create_audit_log_entry' );
