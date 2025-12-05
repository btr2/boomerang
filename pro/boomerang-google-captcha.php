<?php
/**
 * Google Captcha Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function enqueue_google_recaptcha_scripts() {
	if ( is_user_logged_in() ) {
		// We don't need to check logged-in users for spam.
		return;
	}

	$site_key = boomerang_get_google_recaptcha_keys__premium_only()['key'];

	$url = 'https://www.google.com/recaptcha/api.js?render=' . $site_key;
	wp_enqueue_script( 'google-recaptcha', esc_url( $url ), array(), null, false );

	wp_add_inline_script(
		'boomerang',
		'const google_recaptcha = ' . wp_json_encode(
			array(
				'key' => $site_key,
			)
		),
		'before'
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_google_recaptcha_scripts' );

function is_valid_captcha_response( $captcha ) {
	$captcha_postdata = http_build_query(
	array(
		'secret'   => boomerang_get_google_recaptcha_keys__premium_only()['secret'],
		'response' => $captcha,
		'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
	)
);
	$captcha_opts     = array(
		'http' => array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $captcha_postdata,
		),
	);
	$captcha_context  = stream_context_create( $captcha_opts );
	$captcha_response = json_decode( file_get_contents( 'https://www.google.com/recaptcha/api/siteverify', false, $captcha_context ), true );

	$minimum_score = apply_filters( 'boomerang_recaptcha_score', 0.5 );

	if ( $captcha_response['success'] && $captcha_response['score'] > $minimum_score ) {
		return true;
	} else {
		return false;
	}
}

function verify_google_recaptcha( $args ) {
	$board = $args['post_parent'];

	if ( is_user_logged_in() || ! boomerang_board_recaptcha_enabled( $board ) ) {
		// We don't need to check logged-in users for spam, or if the current board has recaptcha disabled.
	return;
}

// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in boomerang form submission
$recaptcha = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

if ( empty( $recaptcha ) ) {
		$error = new \WP_Error(
			'Boomerang: Spam Error (empty token)',
			esc_html__( 'There was a problem', 'boomerang' )
		);

		wp_send_json_error( $error );

		wp_die();
	} elseif ( ! is_valid_captcha_response( $recaptcha ) ) {
		$error = new \WP_Error(
			'Boomerang: Spam Error (score too low)',
			esc_html__( 'There was a problem', 'boomerang' )
		);

		wp_send_json_error( $error );

		wp_die();
	}
}
add_action( 'boomerang_new_boomerang_before_save', __NAMESPACE__ . '\verify_google_recaptcha' );

function add_google_links( $board ) {
	if ( ! boomerang_get_option( 'boomerang_google_hide_logo' ) || is_user_logged_in() || ! boomerang_board_recaptcha_enabled( $board ) ) {
		return;
	}

	printf(
	/* translators: 1: Name of a city 2: ZIP code */
		'<p class="google-branding">%1$s <a href="https://policies.google.com/privacy">%2$s</a> %3$s <a href="https://policies.google.com/terms">%4$s</a> %5$s.</p>',
		esc_html__( 'This site is protected by reCAPTCHA and the Google', 'boomerang' ),
		esc_html__( 'Privacy Policy', 'boomerang' ),
		esc_html__( 'and', 'boomerang' ),
		esc_html__( 'Terms of Service', 'boomerang' ),
		esc_html__( 'apply', 'boomerang' ),
	);
}
add_action( 'boomerang_form_footer', __NAMESPACE__ . '\add_google_links' );

function remove_google_recaptcha_badge() {
	if ( boomerang_get_option( 'boomerang_google_hide_logo' ) ) {
		echo '<style>.grecaptcha-badge { visibility: hidden; }</style>';
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\remove_google_recaptcha_badge' );

