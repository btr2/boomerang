<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Getters **/

function boomerang_get_slug() {
	if ( ! empty( get_option( 'boomerang_slug' ) ) ) {
		return get_option( 'boomerang_slug' );
	}

	return 'boomerang';
}

function boomerang_get_board_permalink() {
	if ( ! empty( get_option( 'boomerang_board_slug' ) ) ) {
		return get_option( 'boomerang_board_slug' );
	}

	return 'board';
}

/**
 * Helper function to retrieve an option from our global settings page.
 *
 * @param $option
 * @param $default
 *
 * @return mixed|null
 */
function boomerang_get_option( $option = '', $default = null ) {
	$options = get_option( 'boomerang_global_options' );
	return ( isset( $options[ $option ] ) ) ? $options[ $option ] : $default;
}

/** Conditionals **/

/**
 * Checks whether drafts should be retrieved.
 *
 * @return true
 */
function boomerang_show_drafts() {
	return false;
}

/**
 * Checks whether the tag system is enabled.
 *
 * @return true
 */
function boomerang_tags_enabled() {
	return true;
}

/**
 * Checks whether the status system is enabled.
 *
 * @return true
 */
function boomerang_statuses_enabled() {
	return true;
}

/** Labels */

/**
 * Gets the title label from settings.
 *
 * @return true
 */
function boomerang_label_title() {
	$label = 'Title';

	return apply_filters( 'boomerang_label_title', $label );
}

/**
 * Gets the content label from settings.
 *
 * @return true
 */
function boomerang_label_content() {
	$label = 'Content';

	return apply_filters( 'boomerang_label_content', $label );
}

/**
 * Gets the submit label from settings.
 *
 * @return true
 */
function boomerang_label_submit() {
	$label = 'Submit';

	return apply_filters( 'boomerang_label_submit', $label );
}
