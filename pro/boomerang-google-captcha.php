<?php
/**
 * Google Captcha Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function add_google_recaptcha( $board ) {
	if ( is_user_logged_in() ) {
		// We don't need to check logged-in users for spam.
		// return;
	}

	echo '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">';
}
add_action( 'boomerang_form_footer', __NAMESPACE__ . '\add_google_recaptcha' );

function enqueue_google_recaptcha_scripts() {
	$site_key = boomerang_get_google_recaptcha_keys__premium_only()['key'];

	// $url = 'https://www.google.com/recaptcha/api.js?render=' . $site_key;
	$url = 'https://www.google.com/recaptcha/api.js';
	wp_enqueue_script( 'google-recaptcha', esc_url_raw( $url ) );

	wp_add_inline_script(
		'boomerang',
		'const google_recaptcha = ' . json_encode(
			array(
				'key' => $site_key,
			)
		),
		'before'
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_google_recaptcha_scripts' );


