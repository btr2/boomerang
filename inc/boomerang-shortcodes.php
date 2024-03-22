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

	$classes = array();

	$classes[] = get_post_field( 'post_name', get_post( $a['board'] ) );

	if ( ! is_user_logged_in() ) {
		$classes[] = 'logged-out';
	}

	if ( boo_fs()->can_use_premium_code__premium_only() ) {
		$options = get_option( 'boomerang_customizer' );
		if ( $options['archive_layout'] ) {
			$classes[] = $options['archive_layout'];
		}
	}

	$width = boomerang_get_container_width( $a['board'] );

	if ( empty( array_filter( $a ) ) ) {
		return '<p><strong>Please ensure your Boomerang shortcode contains an ID, or your block has a board assigned</strong></p>';
	}

	ob_start();
	?>

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

	ob_start();
	?>

	<div class="boomerang-container boomerang-directory <?php echo esc_attr( boomerang_get_board_slug( $a['board'] ) ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>" data-base="<?php echo esc_url( $base ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_directory' ) ); ?>">

		<?php
		if ( boomerang_board_filters_enabled( $a['board'] ) ) {
			echo boomerang_get_filters();
		}
		?>

		<div class="boomerang-directory-list">
			<?php //echo boomerang_get_boomerangs( $a['board'] ); ?>
		</div>



	</div>

	<?php

	return ob_get_flush();
}
add_shortcode( 'boomerang_list', '\Bouncingsprout_Boomerang\render_boomerang_directory' );
