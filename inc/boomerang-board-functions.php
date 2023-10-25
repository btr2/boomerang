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
 * Checks if comments are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_comments_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_comments'];
}

/**
 * Checks if thumbnails are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_thumbnails_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_thumbnails'];
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