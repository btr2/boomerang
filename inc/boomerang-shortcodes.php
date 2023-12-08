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

	add_action( 'wp_head', function () {
		acf_form_head();
	}
);

	$can_submit = boomerang_user_can_submit( $a['board'], get_current_user_id() );

	if ( is_array( $can_submit ) ) {
		echo '<div id="boomerang-form-wrapper" class="boomerang-container boomerang-form-error ' . esc_attr( get_post_field( 'post_name', get_post( $a['board'] ) ) ) . '" data-board="' . esc_attr( $a['board'] ) . '">';
		echo '<div class="boomerang-form-error-inner"><p>' . esc_html( $can_submit['message'] ) . '</p></div>';
		echo '</div>';

		return;
	}

	$labels = boomerang_get_labels( $a['board'] );

	ob_start();
	?>

	<div id="boomerang-form-wrapper" class="boomerang-container <?php echo esc_attr( get_post_field( 'post_name', get_post( $a['board'] ) ) ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>">
		<form id="boomerang-form" method="post" enctype='multipart/form-data' data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang-form-nonce' ) ); ?>">

			<?php do_action( 'boomerang_form_fields_start', $a['board'] ); ?>

			<fieldset>
				<label for="title"><?php echo esc_html( $labels['title'] ); ?></label>
				<input type="text" id="boomerang-title" value="" tabindex="1" size="20" name="title"/>
			</fieldset>

			<?php if ( boomerang_board_tags_enabled( $a['board'] ) ) : ?>
				<fieldset>
					<label for="tags"><?php echo esc_html( $labels['tags'] ); ?></label>
					<select class="boomerang_select select2" id="boomerang-tags" name="tags[]" multiple="multiple" style="width: 100%">';

						<?php

						$tags = get_terms(
							array(
								'taxonomy'   => 'boomerang_tag',
								'hide_empty' => false,
							)
						);

						if ( $tags ) {
							foreach ( $tags as $tag ) :
								?>
								<option value="<?php echo esc_attr( $tag->slug ); ?>"><?php echo esc_html( $tag->name ); ?></option>
								<?php
							endforeach;
						}
						?>

					</select>
				</fieldset>

			<?php endif; ?>

			<fieldset>
				<label for="content"><?php echo esc_html( $labels['content'] ); ?></label>
				<textarea id="boomerang-content" tabindex="3" name="content" cols="50" rows="6"></textarea>
			</fieldset>

			<?php if ( boomerang_board_image_enabled( $a['board'] ) ) : ?>

				<?php if ( ! boomerang_default_styles_disabled() ) : ?>

					<fieldset>
						<label for="boomerang_image_upload" class="drop-container primary-background" id="boomerang-dropcontainer">
								<span class="drop-title">
								<?php
								echo esc_html__(
									'Drop file here',
									'boomerang'
								);
								?>
										</span>
							<span class="drop-conjunction"><?php echo esc_html__( 'or', 'boomerang' ); ?></span>
							<input type="file" name="boomerang_image_upload" id="boomerang_image_upload" accept="image/*">
						</label>
					</fieldset>

				<?php else : ?>

					<fieldset>
						<input type="file" name="boomerang_image_upload" id="boomerang_image_upload" accept="image/*"/>
						<label for="boomerang_image_upload">
							<?php
							echo esc_html__(
								'Choose a file',
								'boomerang'
							);
							?>
						</label>
					</fieldset>

				<?php endif; ?>

			<?php endif; ?>

			<?php do_action( 'boomerang_form_fields_end', $a['board'] ); ?>

			<div id="bf-footer">
				<input name="boomerang_board" id="boomerang-board" type="hidden" value="<?php echo esc_attr( $a['board'] ); ?>">
				<?php
				if ( boomerang_board_honeypot_enabled( $a['board'] ) ) {
					echo '<p class="antispam">Leave this empty: <input type="text" id="boomerang_hp" name="boomerang_hp" /></p>';
				}
				?>
				<button id="bf-submit"><?php echo esc_html( $labels['submit'] ); ?>
					<div id="bf-spinner"></div>
				</button>
				<?php do_action( 'boomerang_form_footer', $a['board'] ); ?>
				<span id="bf-result"></span>
			</div>

		</form>
	</div>

	<?php
	return ob_get_flush();
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

	ob_start();

	?>

	<div class="boomerang-container boomerang-directory <?php echo esc_attr( boomerang_get_board_slug( $a['board'] ) ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>">

		<?php
		if ( boomerang_board_filters_enabled( $a['board'] ) ) {
			echo boomerang_get_filters();
		}
		?>

		<div class="boomerang-directory-list">
			<?php echo boomerang_get_boomerangs( $a['board'] ); ?>
		</div>



	</div>

	<?php

	return ob_get_flush();
}
add_shortcode( 'boomerang_list', '\Bouncingsprout_Boomerang\render_boomerang_directory' );
