<?php
/**
 * Pro version filters (admin).
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a safety section to the Boomerang Global settings.
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
		array(
			'id'    => 'boomerang_google_hide_logo',
			'type'  => 'switcher',
			'title' => esc_html__( 'Hide Google reCAPTCHA Badge', 'boomerang' ),
			'desc'  => sprintf(
				// translators: %1$s: text 1, %2$s: text 2
				'%1$s <a href="https://developers.google.com/recaptcha/docs/faq#are-there-any-qps-or-daily-limits-on-my-use-of-recaptcha"> %2$s</a>',
				esc_html__( 'If you wish to remove the Google reCAPTCHA badge, click here. Boomerang will add the required text to your form. For more information, click', 'boomerang' ),
				esc_html__( 'here', 'boomerang' ),
			),
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

/**
 * Adds additional sections to the Boomerang Board settings.
 *
 * @param $post
 *
 * @return void
 */
function add_board_pro_sections( $prefix ) {
	\CSF::createSection(
		$prefix,
		array(
			'title'  => 'Safety',
			'fields' => array(
				array(
					'id'    => 'enable_recaptcha',
					'type'  => 'switcher',
					'title' => esc_html__( 'Enable Google reCAPTCHA v3', 'boomerang' ),
					'desc'  => esc_html__( 'Switch on Google reCAPTCHA for this board. Ensure you have set your Google API keys under Boomerang\'s Global Settings.' ),
				),
			),
		)
	);

	\CSF::createSection(
		$prefix,
		array(
			'title'  => 'Guest Submissions',
			'fields' => render_guest_fields(),
		)
	);
}
add_action( 'boomerang_board_settings_section_end', __NAMESPACE__ . '\add_board_pro_sections' );

/**
 * Render the fields for the Guest Submissions section.
 *
 * @return array
 */
function render_guest_fields() {
	$text = '<h3>Guest Submissions</h3><p>Allowing guest submissions means your site visitors don\'t need to create accounts to post new Boomerangs or vote on existing ones. While this offers a quick and efficient experience for your visitors, there are disadvantages, including spam, malicious posts, duplicated statistics and human error. By using some or all of the settings below, you can reduce this. For more information, read our full documentation <a href="https://www.boomerangwp.com/docs">here</a>.</p><p>When you first turn on guest submissions, a new user will be created. You can find this user under the username <i>boomerang_guest</i>. All guest submissions will be attributed to this user.</p>';

	if ( username_exists( 'boomerang_guest' ) ) {
		$user = get_user_by( 'login', 'boomerang_guest' );
		$url  = get_edit_user_link( $user->ID );

		$text .= '<p><i>boomerang_guest</i> has been created, and you can configure them <a href="' . esc_url( $url ) . '">here</a>.';
	}

	$fields = array();

	$fields[] = array(
		'type'    => 'content',
		'content' => wp_kses_post( $text ),
	);

	$fields[] = array(
		'id'    => 'enable_guest_boomerangs',
		'type'  => 'switcher',
		'title' => esc_html__( 'Enable Guest Submission', 'boomerang' ),
		'desc'  => esc_html__( 'Allow guests to submit Boomerangs. ' ),
	);

	$fields[] = array(
		'id'         => 'enable_guest_boomerang_criteria',
		'type'       => 'checkbox',
		'title'      => esc_html__( 'Guest Submission Criteria', 'boomerang' ),
		'desc'       => esc_html__( 'Pick any criteria that must be fulfilled, when a guest submits a Boomerang.' ),
		'options'    => array(
			'ip'     => 'Unique IP Address',
			'params' => 'A link with correct parameters must be used',
		),
		'dependency' => array( 'enable_guest_boomerangs', '==', 'true' ),
	);

	$post_id     = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : '';
	$params_text = '<i>?boo_auth=' . $post_id . '</i>';

	$fields[] = array(
		'title'      => esc_html__( 'URL Parameter', 'boomerang' ),
		'type'       => 'content',
		'content'    => wp_kses_post( $params_text ),
		'dependency' => array( 'enable_guest_boomerangs', '==', 'true' ),
	);

	$fields[] = array(
		'id'    => 'enable_guest_voting',
		'type'  => 'switcher',
		'title' => esc_html__( 'Enable Guest Voting', 'boomerang' ),
		'desc'  => esc_html__( 'Allow guests to vote on Boomerangs. ' ),
	);

	return $fields;
}

/**
 * Creates a system generated guest user, to hold all guest submissions.
 *
 * @param $data
 *
 * @return void
 */
function create_guest_user( $data ) {
	if ( ! $data['enable_guest_boomerangs'] && ! $data['enable_guest_voting'] ) {
		return;
	}

	if ( username_exists( 'boomerang_guest' ) ) {
		return;
	}

	$args = array(
		'user_login'   => 'boomerang_guest',
		'user_pass'    => wp_generate_password(),
		'display_name' => esc_html__( 'Anonymous User', 'boomerang' ),
		'description'  => esc_html__( 'A system generated user to hold all Boomerang guest submissions.', 'boomerang' ),
		'meta_input'   => array(
			'boomerang_submission_ips' => array(),
			'boomerang_votes_ips'      => array(),
			'boomerang_ids'            => array(),
			'boomerang_ids_voted_on'   => array(),
		),
	);

	$userdata = apply_filters( 'boomerang_guest_args', $args );

	$user_id = wp_insert_user( $userdata );

}
add_action( 'csf_boomerang_board_options_save_after', __NAMESPACE__ . '\create_guest_user', 10, 2 );
