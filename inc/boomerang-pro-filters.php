<?php
/**
 * Pro version filters.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function add_pending_banner( $post ) {
	if ( 'pending' !== $post->post_status ) {
		return;
	}

	if ( boomerang_can_manage() ) {
		echo '<div class="pending-banner">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">lock</span>';
		}

		echo '<p>' . esc_html__( 'This Boomerang is private and only visible to team members.', 'boomerang' ) . '</p>';

		echo '</div>';

	}
}
add_action( 'boomerang_archive_boomerang_start', __NAMESPACE__ . '\add_pending_banner' );

/** Comments **********************************************************************************************************/

/**
 * Adds a class if a comment is set to a private note.
 *
 * @param $classes
 * @param $comment
 *
 * @return mixed
 */
function boomerang_add_comment_classes( $classes, $comment ) {
	$private_note = get_comment_meta( $comment->comment_ID, 'boomerang_private_note', true );

	if ( $private_note ) {
		$classes[] = 'private_note';
	}

	return $classes;
}
add_filter( 'boomerang_comment_classes', __NAMESPACE__ . '\boomerang_add_comment_classes', 10, 2 );

function boomerang_add_private_note_label( $comment ) {
	$private_note = get_comment_meta( $comment->comment_ID, 'boomerang_private_note', true );

	if ( $private_note ) {

		echo '<p class="private-note-label">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">lock</span>';
		}

		esc_html_e( 'Private', 'boomerang' );

		echo '</p>';
	}
}
add_action( 'boomerang_comment_above_author_name', __NAMESPACE__ . '\boomerang_add_private_note_label' );

/**
 * Filter comments so only Boomerang managers can see private notes.
 *
 * @param $comments
 * @param $post_id
 *
 * @return mixed
 */
function boomerang_filter_comments( $comments, $post_id ) {
	foreach ( $comments as $index => $comment ) {
		$private_note = get_comment_meta( $comment->comment_ID, 'boomerang_private_note', true );

		if ( $private_note && ! boomerang_can_manage() ) {
			unset( $comments[ $index ] );
		}
	}

	return $comments;
}
add_filter( 'comments_array', __NAMESPACE__ . '\boomerang_filter_comments', 10, 2 );

/**
 * Filter recent comments widget, because, being WordPress it has to use a completely different system...
 */
function boomerang_filter_recent_comments_widget( $args ) {
	$args['meta_query'] = array(
		array(
			'key'     => 'boomerang_private_note',
			'value'   => '1',
			'compare' => 'NOT EXISTS',
		),
	);

	return $args;
}
add_filter( 'widget_comments_args', __NAMESPACE__ . '\boomerang_filter_recent_comments_widget', 10, 2 );

/**
 * Filter recent comments RSS feed, because, being WordPress it has to use a completely different system...
 */
function boomerang_filter_comments_rss( $args ) {
	global $wpdb;

	return $where . " AND {$wpdb->posts}.post_type NOT IN ( 'boomerang' )";
}
add_filter( 'comment_feed_where', __NAMESPACE__ . '\boomerang_filter_comments_rss', 10, 2 );

/**
 * Rebuild comment array, so non-managers don't see private notes in comment count.
 *
 * @param $count
 * @param $post
 *
 * @return int|int[]|mixed|\WP_Comment[]
 */
function boomerang_comments_count( $count, $post ) {
	if ( boomerang_can_manage() ) {
		return $count;
	}

	$args = array(
		'post_id'    => $post->ID,   // Use post_id, not post_ID
		'count'      => true, // Return only the count
		'meta_query' => array(
			array(
				'key'     => 'boomerang_private_note',
				'value'   => '1',
				'compare' => 'NOT EXISTS',
			),
		),
	);

	return get_comments( $args );
}
add_filter( 'boomerang_comments_count', __NAMESPACE__ . '\boomerang_comments_count', 10, 2 );
