<?php
/**
 * Custom Fields Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the field group for a given board.
 *
 * @param $post
 *
 * @return false|mixed
 */
function get_board_field_group( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['field_group'] ?? false;
}

/**
 * Display headings at the top of a Boomerang form.
 *
 * @param $board
 *
 * @return void
 */
function add_custom_fields( $board ) {
	$group = get_board_field_group( $board );

	if ( $group ) {
		$args = array(
			'field_groups' => array( $group ),
			'form'         => false,
			'honeypot'     => false,
		);

		acf_form( $args );
	}

}
add_action( 'boomerang_form_fields_end', __NAMESPACE__ . '\add_custom_fields' );

/**
 * Enqueue the ACF assets, if a Boomerang shortcode or block has been used in a post.
 *
 * @return void
 */
function enqueue_acf_assets() {
	global $post;
	if( has_shortcode( $post->post_content, 'boomerang' ) || has_block( 'boomerang-block/shortcode-gutenberg', $post->post_content ) ){
		acf_form_head();
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\enqueue_acf_assets' );

/**
 * Takes $_POST data, sanitizes, and updates the given post.
 *
 * @param $post_data
 * @param $post_id
 *
 * @return void
 */
function update_acf( $post_data, $post_id ) {
	$acf = json_decode( stripslashes( $post_data ) );

	foreach ( $acf as $field => $value ) {
		$value = wp_kses_post_deep($value);

		update_field( $field, $value, $post_id );

		$raw = get_field( $field, $post_id );

		if ( is_array( $raw ) ) {
			if ( ! empty( $raw['type'] ) ) {
				if ( 'image' === $raw['type'] || 'application' === $raw['type'] ) {
					// We need to swap where this image is attached to
					$update_attachment_post = array(
						'ID'            => $raw['ID'],
						'post_parent'   => $post_id
					);

					wp_update_post( $update_attachment_post );
				}
			}
		}
	}

}
add_action( 'boomerang_update_acf', __NAMESPACE__ . '\update_acf', 10, 2 );

/** Display ***********************************************************************************************************/

/**
 * Displays ACF fields on single Boomerangs.
 *
 * @param $post_data
 * @param $post_id
 *
 * @return void
 */
function display_acf( $post ) {
	$fields = get_field_objects( $post );

	if ( empty( $fields ) ) {
		return;
	}

	// TODO Possible Debug?
	error_log( pathinfo(__FILE__ )['dirname'] . '/' . pathinfo(__FILE__ )['basename'] );
	error_log( print_r($fields, true) );


		echo '<div class="boomerang-acf-container">';

		foreach ( $fields as $field ) {
			$value = get_field( $field['name'] );


				switch ( $field['type'] ) {
					default:
						if ( ! empty( $value ) ) {
							echo '<p><span class="acf-label">' . esc_html( $field['label'] ) . '</span>: ' . esc_html( get_field($field['name']) ) . '</p>';
						}
						break;
					case 'checkbox':
					case 'select':
						if ( ! empty( $value ) ) {
							if ( is_array( $value ) ) {
								echo '<p><span class="acf-label">' . esc_html( $field['label'] ) . '</span>: ' . implode( ', ', $value ) . '</p>';
							} else {
								echo '<p><span class="acf-label">' . esc_html( $field['label'] ) . '</span>: ' . esc_html( $value ) . '</p>';
							}
						}
						break;
					case 'true_false':
						$on  = $value['ui_on_text'] ?? 'Yes';
						$off = $value['ui_off_text'] ?? 'No';

						if ( 1 == $value ) {
							echo '<p><span class="acf-label">' . esc_html( $field['label'] ) . '</span>: ' . esc_html( $on )  . '</p>';
						} else {
							echo '<p><span class="acf-label">' . esc_html( $field['label'] ) . '</span>: ' . esc_html( $off )  . '</p>';
						}
						break;
					case 'wysiwyg':
						echo wp_kses_post( $value );
						break;
					case 'color_picker':
						if ( ! empty( $value ) ) {
							echo '<p class="acf-color">';
							echo '<span class="acf-label">' . esc_html( $field['label'] ) . '</span>: ';
							echo '<span class="acf-color-value" style="background: ' . esc_attr( $value ) . '; border-color: ' . esc_attr( $value ) . ';"></span>';
							echo '<span class="acf-color-value-text" style="border-color: ' . esc_attr( $value ) . ';">' . esc_html( get_field( $field['name'] ) ) . '</span>';
							echo '</p>';
						}
					case 'image';
					case 'file':
						break;




				}



		}

	echo '</div>';

}
add_action( 'boomerang_single_boomerang_before_footer', __NAMESPACE__ . '\display_acf' );