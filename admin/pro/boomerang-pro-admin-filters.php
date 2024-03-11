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
			'id' => 'safety',
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
			'id' => 'guest_submissions',
			'title'  => 'Guest Submissions',
			'fields' => render_guest_fields(),
		)
	);

	if ( class_exists( 'ACF' ) ) {
		\CSF::createSection(
			$prefix,
			array(
				'custom_fields',
				'title'  => 'Custom Fields',
				'fields' => render_custom_fields_section(),
			)
		);
	}

	\CSF::createSection(
		$prefix,
		array(
			'id' => 'other_boomerangs',
			'title'  => 'Other Boomerangs',
			'fields' => array(
				array(
					'id'    => 'enable_related_boomerangs',
					'type'  => 'switcher',
					'title' => esc_html__( 'Show Related Boomerangs', 'boomerang' ),
					'desc'  => esc_html__( 'Display related Boomerangs in the sidebar of a single Boomerang. Helps users to see if someone has already posted something similar.', 'boomerang' ),
				),
				array(
					'id'         => 'related_boomerangs_label',
					'type'       => 'text',
					'title'      => esc_html__( 'Title for related Boomerang area', 'boomerang' ),
					'dependency' => array( 'enable_related_boomerangs', '==', 'true' ),
				),
				array(
					'id'    => 'enable_suggested_boomerangs',
					'type'  => 'switcher',
					'title' => esc_html__( 'Show Suggested Boomerangs', 'boomerang' ),
					'desc'  => esc_html__( 'Display suggested Boomerangs when a user types a title into the form. Helps reduce the number of duplicated Boomerangs.', 'boomerang' ),
				),
				array(
					'id'         => 'suggested_boomerangs_label',
					'type'       => 'text',
					'title'      => esc_html__( 'Title for suggested Boomerang area', 'boomerang' ),
					'dependency' => array( 'enable_suggested_boomerangs', '==', 'true' ),
				),
			),
		)
	);

	\CSF::createSection(
		$prefix,
		array(
			'id' => 'polls',
			'title'  => 'Polls',
			'fields' => render_polls_fields(),
		)
	);
}
add_action( 'boomerang_board_settings_section_end', __NAMESPACE__ . '\add_board_pro_sections' );


function get_poll_results( $board_id ) {
	if ( ! $board_id ) {
		return array();
	}

	$results = get_post_meta( $board_id, 'polls', true );

	if ( empty( $results ) ) {
		return array();
	}

	$data = array();

	foreach ( $results as $result => $value ) {
		$counted_values = array_count_values( $value );
		$boomerang_data = array();

		foreach ( $counted_values as $counted_value => $votes ) {
			$boomerang_data[ $counted_value ] = array(
				'title' => get_the_title( $counted_value ),
				'votes' => $votes,
			);
		}

		$data[ $result ] = array(
			'poll_id' => $result,
			'max'     => max( $counted_values ),
			'data'    => $boomerang_data,
		);

	}

	return $data;
}

function render_poll_results( $board_id = false ) {
	$results = get_poll_results( $board_id );

	ob_start();

	foreach ( $results as $result ) : ?>
			<div class="boomerang-result" data-id="<?php echo $result['poll_id']; ?>" style="display: none; padding: 20px 0;">
				<?php
				foreach ( $result['data'] as $boomerang ) :
					$title = ! empty( $boomerang['title'] ) ? $boomerang['title'] : 'None';

					$width = $boomerang['votes'] / $result['max'] * 100;
					?>
			<p><?php echo $title; ?></p>
			<div class="outer-bar" style="height: 10px; display: flex; align-items: center; gap: 10px; font-size: 16px; font-weight: 500">
				<div class="inner-bar" style="width: <?php echo $width; ?>%; height: 100%; background: darkred"></div>
				<span style="white-space: nowrap"><?php echo $boomerang['votes']; ?> votes</span>
			</div>

				<?php endforeach; ?>
			</div>
	<?php endforeach; ?>
	<div class="boomerang-result-null" style="display: none"><?php esc_html_e( 'Data will appear once the first vote is submitted...', 'boomerang' ); ?></div>


			<?php

			return ob_get_clean();
}


function render_polls_fields() {
	$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : '';

	$fields = array();

	if ( empty( get_poll_results( $post_id ) ) ) {
		$text = '<p>To get started with polls, click the \'Add New\' button below.</p>';

		$fields[] = array(
			'type'    => 'content',
			'content' => wp_kses_post( $text ),
		);
	}

	$fields[] = array(
		'id'     => 'polls',
		'type'   => 'group',
		'class'  => 'boomerang-poll',
		'fields' => array(
			array(
				'id'    => 'poll_heading',
				'type'  => 'text',
				'title' => esc_html__( 'Poll Title', 'boomerang' ),
				'desc'  => esc_html__( 'A name for your poll.', 'boomerang' ),
			),
			array(
				'type'    => 'content',
				'title'   => esc_html__( 'Latest Result', 'boomerang' ),
				'content' => render_poll_results( $post_id ),
			),
			array(
				'id'      => 'poll_heading_show',
				'type'    => 'switcher',
				'default' => true,
				'title'   => esc_html__( 'Show Title', 'boomerang' ),
				'desc'    => esc_html__( 'This will show the title at the top of the poll.', 'boomerang' ),
			),
			array(
				'id'         => 'poll_slug',
				'type'       => 'text',
				'class'      => 'hidden',
				'attributes' => array(
					'type' => 'hidden',
				),
			),
			array(
				'id'         => 'poll_id',
				'type'       => 'text',
				'class'      => 'hidden poll_id',
				'attributes' => array(
					'type' => 'hidden',
				),
			),
			array(
				'id'         => 'poll_board',
				'type'       => 'text',
				'class'      => 'hidden',
				'attributes' => array(
					'type' => 'hidden',
				),
			),
			array(
				'id'    => 'poll_enabled',
				'type'  => 'switcher',
				'title' => esc_html__( 'Enable this Poll', 'boomerang' ),
				'desc'  => esc_html__( 'Switch this poll on or off.', 'boomerang' ),
			),
			array(
				'id'    => 'poll_description',
				'type'  => 'text',
				'title' => esc_html__( 'Poll Description', 'boomerang' ),
				'desc'  => esc_html__( 'Additional text to describe your poll.', 'boomerang' ),
			),
			array(
				'id'          => 'poll_boomerangs',
				'type'        => 'select',
				'title'       => esc_html__( 'Boomerangs', 'boomerang' ),
				'placeholder' => esc_html__( 'Select one or more Boomerangs', 'boomerang' ),
				'options'     => 'posts',
				'chosen'      => true,
				'ajax'        => true,
				'multiple'    => true,
				'sortable'    => true,
				'desc'        => esc_html__( 'Choose which Boomerangs will feature in your poll. We recommend a maximum of two or three.', 'boomerang' ),
				'query_args'  => array(
					'post_type'      => 'boomerang',
					'posts_per_page' => -1,
					'post_parent'    => $post_id,
				),
			),
			array(
				'id'    => 'poll_null_enabled',
				'type'  => 'switcher',
				'title' => esc_html__( 'Allow \'None of the above\'', 'boomerang' ),
				'desc'  => esc_html__( 'If enabled, users may signal that they would vote for none of the options. This will be included in reports.', 'boomerang' ),
			),
			array(
				'id'          => 'poll_null_label',
				'type'        => 'text',
				'title'       => esc_html__( 'Label for \'None of the above\'', 'boomerang' ),
				'placeholder' => esc_html__( 'None of the above', 'boomerang' ),
				'default'     => 'None of the above',
				'dependency'  => array( 'poll_null_enabled', '==', 'true' ),
			),
			array(
				'id'          => 'poll_location',
				'type'        => 'select',
				'title'       => esc_html__( 'Poll Location', 'boomerang' ),
				'placeholder' => esc_html__( 'Where should the poll be displayed?', 'boomerang' ),
				'options'     => array(
					'top-left'     => 'Top Left',
					'top-right'    => 'Top Right',
					'bottom-left'  => 'Bottom Left',
					'bottom-right' => 'Bottom Right',
					'center'       => 'Center',
				),
			),
			array(
				'id'          => 'poll_visibility',
				'type'        => 'select',
				'title'       => esc_html__( 'Poll Visibility', 'boomerang' ),
				'placeholder' => esc_html__( 'Which pages should feature a poll?', 'boomerang' ),
				'options'     => array(
					'all'           => 'Whole Site',
					'home'          => 'Homepage Only',
					'board'         => 'Board Directory and all its Boomerangs',
					'board_archive' => 'Board Directory Page',
				),
			),
			array(
				'id'          => 'poll_success_message',
				'type'        => 'text',
				'placeholder' => esc_html__( 'Thanks for your feedback!', 'boomerang' ),
				'title'       => esc_html__( 'Success Message', 'boomerang' ),
				'desc'        => esc_html__( 'A message to display when a user has successfully submitted their vote.', 'boomerang' ),
			),
			array(
				'id'    => 'poll_debug_enabled',
				'type'  => 'switcher',
				'title' => esc_html__( 'Enable Debug Mode', 'boomerang' ),
				'desc'  => esc_html__( 'Allows administrators to vote in polls, and also multiple voting. Useful for checking how a new poll looks and behaves.', 'boomerang' ),
			),
		),
	);

	return $fields;
}

/**
 * Generates a slug for each poll, either the sanitized title, or a random number, and also a unique ID.
 *
 * @param $data
 * @param $post_id
 * @param $instance
 *
 * @return void
 */
function generate_poll_slug_and_id( $data, $post_id, $instance ) {
	if ( empty( $data['polls'] ) ) {
		return $data;
	}

	foreach ( $data['polls'] as $key => $poll ) {
		if ( empty( $poll['poll_slug'] ) ) {
			$title = $poll['poll_heading'];

			if ( empty( $title ) ) {
				$slug = wp_rand( 10000, 99999 );
			} else {
				$slug = sanitize_title( $title );
			}

			if ( check_unique_poll_slug( $slug ) ) {
				$data['polls'][ $key ]['poll_slug'] = $slug;
			} else {
				$data['polls'][ $key ]['poll_slug'] = $slug . '-' . wp_rand( 100, 999 );
			}
		}

		if ( empty( $poll['poll_id'] ) ) {
			$data['polls'][ $key ]['poll_id'] = get_next_poll_id();
		}

		if ( empty( $poll['poll_board'] ) ) {
			$data['polls'][ $key ]['poll_board'] = $post_id;
		}
	}

	return $data;

}
add_filter( 'csf_boomerang_board_options_save', __NAMESPACE__ . '\generate_poll_slug_and_id', 10, 3 );

/**
 * Render the fields for the Guest Submissions section.
 *
 * @return array
 */
function render_guest_fields() {
	$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : '';

	$text = '<h3>Guest Submissions and Voting</h3><p>Allowing guest submissions means your site visitors don\'t need to create accounts to post new Boomerangs or vote on existing ones. While this offers a quick and efficient experience for your visitors, there are disadvantages, including spam, malicious posts, duplicated statistics and human error. By using some or all of the settings below, you can reduce this.</p><p>When you first turn on guest submissions, a new user will be created. You can find this user under the username <i>boomerang_guest</i>. All guest submissions will be attributed to this user.</p>';

	if ( username_exists( 'boomerang_guest' ) ) {
		$user = get_user_by( 'login', 'boomerang_guest' );
		$url  = get_edit_user_link( $user->ID );

		$text .= '<p><i>boomerang_guest</i> has been created, and you can configure them <a href="' . esc_url( $url ) . '">here</a>.';
	}

	$text .= '<p>If required, the URL parameter for this board is: <i>?boo_auth=' . $post_id . '</i></p>';

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
		'id'         => 'enable_guest_boomerangs_name_request',
		'type'       => 'switcher',
		'title'      => esc_html__( 'Request a Name', 'boomerang' ),
		'desc'       => esc_html__( 'Allow guests to enter their name. This will replace \'Anonymous User\' for that guest\'s Boomerang' ),
		'dependency' => array( 'enable_guest_boomerangs', '==', 'true' ),
	);

	$fields[] = array(
		'id'         => 'enable_guest_boomerangs_email_request',
		'type'       => 'switcher',
		'title'      => esc_html__( 'Request an Email', 'boomerang' ),
		'desc'       => esc_html__( 'Allow guests to enter an email. ' ),
		'dependency' => array( 'enable_guest_boomerangs', '==', 'true' ),
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

	$fields[] = array(
		'id'    => 'enable_guest_voting',
		'type'  => 'switcher',
		'title' => esc_html__( 'Enable Guest Voting', 'boomerang' ),
		'desc'  => esc_html__( 'Allow guests to vote on Boomerangs. ' ),
	);

	$fields[] = array(
		'id'         => 'enable_guest_voting_criteria',
		'type'       => 'checkbox',
		'title'      => esc_html__( 'Guest Voting Criteria', 'boomerang' ),
		'desc'       => esc_html__( 'Pick any criteria that must be fulfilled, when a guest votes on a Boomerang.' ),
		'options'    => array(
			'ip'   => 'Unique IP Address',
			'time' => 'Time based restrictions',
		),
		'dependency' => array( 'enable_guest_voting', '==', 'true' ),
	);

	$fields[] = array(
		'id'         => 'guest_vote_time_gap',
		'type'       => 'radio',
		'title'      => esc_html__( 'No voting within', 'boomerang' ),
		'desc'       => esc_html__( 'of the last vote from the same IP address.' ),
		'default'    => '60',
		'options'    => array(
			'1'     => '1 minute',
			'60'    => '1 hour',
			'1440'  => '1 day',
			'10080' => '1 week',
		),
		'dependency' => array(
			array( 'enable_guest_voting_criteria', 'any', 'time' ),
		),
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
			'boomerang_vote_data'      => array(),
			'boomerang_ids'            => array(),
			'boomerang_voted_ips'      => array(),
		),
	);

	$userdata = apply_filters( 'boomerang_guest_args', $args );

	$user_id = wp_insert_user( $userdata );

}
add_action( 'csf_boomerang_board_options_save_after', __NAMESPACE__ . '\create_guest_user', 10, 2 );

/**
 * Render a custom fields section.
 *
 * @return array
 */
function render_custom_fields_section() {
	$board  = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : '';
	$fields = array();

	if ( ! is_plugin_active( 'advanced-custom-fields/acf.php' ) && ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
		$fields[] = array(
			'type'    => 'content',
			'content' => 'To get started, blah, blah...',
		);

		return $fields;
	}

	$groups = get_posts( array( 'post_type' => 'acf-field-group' ) );

	if ( ! empty( $groups ) ) {
		$field_group_options = array(
			'0' => 'Select',
		);
		foreach ( $groups as $group ) {
			$field_group_options[ $group->ID ] = $group->post_title;
		}
	}

	$fields[] = array(
		'id'      => 'field_group',
		'type'    => 'select',
		'title'   => esc_html__( 'Field Group', 'boomerang' ),
		'options' => $field_group_options,
		'desc'    => sprintf(
			// translators: %1$s: text 1, %2$s: text 2
			'%1$s <a href="https://boomerangwp.com/support/"> %2$s</a>',
			esc_html__( 'Choose an ACF Field Group to display on the form for this board. Remember to set the location rule so that the post type is equal to Boomerang. For more information, click', 'boomerang' ),
			esc_html__( 'here', 'boomerang' ),
		),
	);

	return $fields;
}
