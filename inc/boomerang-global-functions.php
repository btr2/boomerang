<?php
/**
 * Functions that relate to the plugin as a whole - global functionality.
 */
namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Getters **/

/**
 * Gets the base slug for boomerangs.
 *
 * @return false|mixed|string|null
 */
function boomerang_get_base() {
	if ( ! empty( get_option( 'boomerang_base' ) ) ) {
		return get_option( 'boomerang_base' );
	}

	return 'boomerang';
}

/**
 * Gets the base slug for boards.
 *
 * @return false|mixed|string|null
 */
function boomerang_board_get_base() {
	if ( ! empty( get_option( 'boomerang_board_base' ) ) ) {
		return get_option( 'boomerang_board_base' );
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

/**
 * Sends an email.
 *
 * @param $to
 * @param $subject
 * @param $message
 * @param bool $headers
 *
 * @return void
 */
function boomerang_send_email( $to, $subject, $body, $headers = false ) {
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	wp_mail( $to, $subject, $body, $headers );
}
