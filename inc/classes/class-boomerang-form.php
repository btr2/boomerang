<?php
/**
 * Defines all functionality for our form.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles displays and hooks for the Boomerang form.
 */
class Boomerang_Form {
	public $board;

	public $post;

	public $title;

	public $content;

	public $tags;

	/**
	 * Initiate a new form.
	 */
	public function __construct( $board, $post = false ) {
		if ( $board ) {
			$this->board = $board;
		}

		if ( $post ) {
			$this->post = $post;
			$this->populate( $post );
		}
	}

	/**
	 * Populate our form object with values from a given Boomerang.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function populate( $post ) {
		$this->title   = $post->post_title;
		$this->content = $post->post_content;
		$term_obj_list = get_the_terms( $post->ID, 'boomerang_tag' );
		$this->tags    = wp_list_pluck( $term_obj_list, 'slug' );
	}

	/**
	 * Render a Boomerang form.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! $this->board ) {
			return;
		} else {
			$board = $this->board;
		}

		$can_submit = boomerang_user_can_submit( $board, get_current_user_id() );

		if ( is_array( $can_submit ) ) {
			echo '<div id="boomerang-form-wrapper" class="boomerang-container boomerang-form-error ' . esc_attr( get_post_field( 'post_name', get_post( $board ) ) ) . '" data-board="' . esc_attr( $board ) . '">';
			echo '<div class="boomerang-form-error-inner"><p>' . esc_html( $can_submit['message'] ) . '</p></div>';
			echo '</div>';

			return;
		}

		$labels = boomerang_get_labels( $board );
		?>

		<div id="boomerang-form-wrapper" class="boomerang-container <?php echo esc_attr( get_post_field( 'post_name', get_post( $board ) ) ); ?>" data-board="<?php echo esc_attr( $board ); ?>">
			<form id="boomerang-form" method="post" enctype='multipart/form-data' data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang-form-nonce' ) ); ?>">

				<?php do_action( 'boomerang_form_fields_start', $board ); ?>

				<fieldset>
					<label for="title"><?php echo esc_html( $labels['title'] ); ?></label>
					<input data-board="<?php echo esc_attr( $board ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang-title-nonce' ) ); ?>" type="text" id="boomerang-title" value="" tabindex="1" size="20" name="title"/>
				</fieldset>

				<?php do_action( 'boomerang_form_below_title', $board ); ?>

				<?php if ( boomerang_board_tags_enabled( $board ) ) : ?>
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

				<?php if ( boomerang_board_image_enabled( $board ) ) : ?>

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

				<?php do_action( 'boomerang_form_fields_end', $board ); ?>

				<div id="bf-footer">
					<input name="boomerang_board" id="boomerang-board" type="hidden" value="<?php echo esc_attr( $board ); ?>">
					<?php
					if ( boomerang_board_honeypot_enabled( $board ) ) {
						echo '<p class="antispam">Leave this empty: <input type="text" id="boomerang_hp" name="boomerang_hp" /></p>';
					}
					?>
					<button id="bf-submit"><?php echo esc_html( $labels['submit'] ); ?>
						<div id="bf-spinner"></div>
					</button>
					<?php do_action( 'boomerang_form_footer', $board ); ?>
					<span id="bf-result"></span>
				</div>

			</form>
		</div>

		<?php
	}
}
