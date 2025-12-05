<?php
/**
* Register and populate our shortcodes
*/
namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a complete instance of Boomerang on a page.
 *
 * @return false|string
 */
function render_boomerang_full( $atts ) {
	$a = shortcode_atts(
		array(
			'board' => false,
		),
		$atts
	);

	$styles = boomerang_get_styling( $a['board'] );

	$classes = array();

	$classes[] = get_post_field( 'post_name', get_post( $a['board'] ) );

	if ( ! is_user_logged_in() ) {
		$classes[] = 'logged-out';
	}

	$classes[] = boomerang_get_layout( $a['board'] );

	$width = boomerang_get_container_width( $a['board'] );

	if ( empty( array_filter( $a ) ) ) {
		return '<p><strong>Please ensure your Boomerang shortcode contains an ID, or your block has a board assigned</strong></p>';
	}

	ob_start();
	?>
	<style><?php echo esc_html( wp_strip_all_tags( $styles ) ); ?></style>
	<div id="boomerang-full" style="width: <?php echo esc_attr( $width ); ?>;" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>">
		<?php

		if ( boomerang_board_title_enabled() ) {
			the_title( '<h2 class="entry-title board-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
		}

		render_boomerang_form( $a ); // phpcs:ignore -- escaped later

		render_boomerang_directory( $a );

		?>
	</div>

	<?php
	return ob_get_clean();
}
add_shortcode( 'boomerang', '\Bouncingsprout_Boomerang\render_boomerang_full' );

/**
 * Renders a form to submit new Boomerangs.
 *
 * @return false|string
 */
function render_boomerang_form( $atts ) {
	$a = shortcode_atts(
		array(
			'board' => $atts['board'] ?? false,
		),
		$atts
	);

	$form = new Boomerang_Form( $a['board'] );

	return $form->render();
}
add_shortcode( 'boomerang_form', '\Bouncingsprout_Boomerang\render_boomerang_form' );

/**
 * Render a directory of Boomerangs.
 *
 * @return false|string
 */
function render_boomerang_directory( $atts ) {
	$a = shortcode_atts(
		array(
			'board' => $atts['board'] ?? false,
		),
		$atts
	);

	global $wp;
	$base = home_url( $wp->request ); // Gets the current page we are on.

	// Get pagination type for CSS class
	$pagination_type = boomerang_board_pagination_type( $a['board'] );
	$pagination_class = '';
	if ( 'infinite' === $pagination_type ) {
		$pagination_class = ' boomerang-infinite-scroll';
	} elseif ( 'none' === $pagination_type ) {
		$pagination_class = ' boomerang-pagination-none';
	}

	ob_start();
	?>

	<div class="boomerang-container boomerang-directory <?php echo esc_attr( boomerang_get_board_slug( $a['board'] ) . $pagination_class ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>" data-base="<?php echo esc_url( $base ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_directory' ) ); ?>">

	<?php
	if ( boomerang_board_filters_enabled( $a['board'] ) ) {
		$allowed_html = array(
			'div' => array(
				'id' => array(),
				'class' => array(),
				'data-nonce' => array(),
			),
			'fieldset' => array(),
			'label' => array(
				'for' => array(),
			),
			'span' => array(
				'class' => array(),
			),
			'select' => array(
				'name' => array(),
				'id' => array(),
				'class' => array(),
			),
			'option' => array(
				'value' => array(),
				'selected' => array(),
			),
			'input' => array(
				'type' => array(),
				'name' => array(),
				'id' => array(),
				'class' => array(),
				'placeholder' => array(),
				'value' => array(),
			),
		);
		echo wp_kses( boomerang_get_filters( $a['board'] ), $allowed_html );
	}
	?>

		<div class="boomerang-directory-list"></div>



	</div>

	<?php

	return ob_get_flush();
}
add_shortcode( 'boomerang_list', '\Bouncingsprout_Boomerang\render_boomerang_directory' );
