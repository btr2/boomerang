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

/**
 * Gets the singular form of a name for a Boomerang.
 *
 * @param $board
 *
 * @return mixed
 */
function get_singular_global() {
	return boomerang_get_option( 'global_label_singular', 'boomerang' );
}

/**
 * Gets the plural form of a name for a Boomerang.
 *
 * @param $board
 *
 * @return mixed
 */
function get_plural_global() {
	return boomerang_get_option( 'global_label_plural', 'boomerangs' );
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
 * Checks whether Select2 has been disabled.
 *
 * @return bool
 */
function boomerang_select2_disabled() {
	return boomerang_get_option( 'disable_select2', false );
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

/**
 * Gets the user-provided Google site keys, or false if either is missing.
 *
 * @param $post
 *
 * @return array|false
 */
function boomerang_get_google_recaptcha_keys__premium_only() {
	if ( empty( boomerang_get_option( 'boomerang_google_site_key' ) ) || empty( boomerang_get_option( 'boomerang_google_secret_key' ) ) ) {
		return false;
	} else {
		return array(
			'key'    => boomerang_get_option( 'boomerang_google_site_key' ),
			'secret' => boomerang_get_option( 'boomerang_google_secret_key' ),
		);
	}
}

/**
 * Checks to see if Simple Feature Requests is active.
 *
 * @return bool|void
 */
function boomerang_is_simple_feature_requests_active() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( ! function_exists( 'is_plugin_active' ) ) {
		return;
	}

	if ( is_plugin_active( 'simple-feature-requests/simple-feature-requests.php' ) || is_plugin_active( 'simple-feature-requests-pro/simple-feature-requests.php' ) ) {
		return true;
	}

	return false;
}
