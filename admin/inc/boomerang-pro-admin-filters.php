<?php
/**
 * Pro version filters (admin).
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a banner to top of Boomerangs to warn that Boomerang is locked.
 *
 * @param $post
 *
 * @return void
 */
function add_safety_section( $prefix ) {
	$text = '<h3>reCAPTCHA</h3><p>reCAPTCHA protects you against spam and other types of automated abuse. With Boomerang\'s reCAPTCHA integration module, you can block abusive Boomerang submissions by spam bots.</p><a href="https://www.boomerangwp.com/docs/recaptcha/">reCAPTCHA (v3)</a>';

	$fields = array(
		array(
			'type'    => 'content',
			'content' => wp_kses_post( $text ),
		),
		array(
			'id'    => 'boomerang_google_site_key',
			'type'  => 'text',
			'title' => esc_html__( 'Site Key', 'boomerang' ),
		),
		array(
			'id'    => 'boomerang_google_secret_key',
			'type'  => 'text',
			'title' => esc_html__( 'Secret Key', 'boomerang' ),
		),
	);

	\CSF::createSection(
		$prefix,
		array(
			'id'     => 'safety',
			'title'  => 'Safety',
			'fields' => $fields,
		)
	);
}
add_action( 'boomerang_global_settings_section_end', __NAMESPACE__ . '\add_safety_section' );
