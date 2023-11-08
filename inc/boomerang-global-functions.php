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
 * Checks whether Google Fonts are disabled.
 *
 * @return bool
 */
function boomerang_google_fonts_disabled() {
	return boomerang_get_option( 'disable_google_fonts', false );
}

/**
 * Checks whether house styles are enabled.
 *
 * @return bool
 */
function boomerang_default_styles_disabled() {
	return boomerang_get_option( 'disable_default_styles', false );
}

