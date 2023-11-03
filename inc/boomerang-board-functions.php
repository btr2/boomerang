<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Getters **/

/**
 * Ghe the slug for a given board. Helper function as WordPress doesn't really provide a good method.
 *
 * @param $post
 *
 * @return string
 */
function boomerang_get_board_slug( $post )  {
	$post = get_post( $post );

	return $post->post_name;
}

/** Conditionals **/

/**
 * Checks whether a given user can manage Boomerangs, or the current user if none specified.
 *
 * @return mixed|true|null
 */
function boomerang_can_manage( $user = false ) {
	if ( ! $user ) {
		$user = wp_get_current_user();
	}

	// Site admins can always manage Boomerangs.
	if ( user_can( $user, 'manage_options') ) {
		return true;
	}

	return apply_filters('boomerang_can_manage', false, $user );
}

/**
 * Checks if titles are displayed for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_title_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_title'] ?? false;
}

/**
 * Checks if featured images for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_image_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_image'] ?? false;
}

/**
 * Checks if comments are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_comments_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_comments'] ?? false;
}

/**
 * Checks if votes are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_votes_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_votes'] ?? false;
}

/**
 * Checks if down-voting is enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_downvoting_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_downvoting'] ?? false;
}

/**
 * Checks if tags are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_tags_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_tags'] ?? false;
}

/**
 * Checks if statuses are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_statuses_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_statuses'] ?? false;
}

/**
 * Checks if filters are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_filters_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_filters'] ?? false;
}

/**
 * Checks if authors are displayed for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_author_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_author'] ?? false;
}

/**
 * Checks if published dates are displayed for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_date_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_date'] ?? false;
}

/**
 * Checks if published dates are displayed in a friendly way.
 *
 * @see human_time_diff()
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_friendly_date_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_friendly_date'] ?? false;
}

/**
 * Returns the default status for new Boomerangs.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_get_default_status( $post ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	if ( ! $meta['require_approval'] ) {
		return 'publish';
	} else {
		return 'pending';
	}
}

/**
 * Helper function that retrieves the WP_Post object for either a Boomerang, or it's parent board,
 * or the current WP_Post if none is provided.
 *
 * @param $post
 *
 * @return array|WP_Post|null
 */
function boomerang_get_post( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	} else {
		$post = get_post( $post );
	}

	if ( 'boomerang' === $post->post_type ) {
		$post = get_post( $post->post_parent );
	}

	return $post;
}

/** Boomerang Form ****************************************************************************************************/


function boomerang_get_form_labels( $board ) {
	$board = get_post( $board );

	$meta = get_post_meta( $board->ID, 'boomerang_board_options', true );

	return array(
		'title' => $meta['label_title'] ?? 'Title',
		'content' => $meta['label_content'] ?? 'Content',
		'tags' => $meta['label_tags'] ?? 'Tags',
		'submit' => $meta['label_submit'] ?? 'Submit',
	);
}