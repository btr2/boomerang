<?php
/**
 * Edit Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks if editing is enabled for a Boomerang's board.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_editing_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_edit'] ?? false;
}

/**
 * Create a modal to hold our edit form.
 *
 * @return void
 */
function create_modal() {
	global $post;

	if ( ! is_singular() || 'boomerang' !== $post->post_type ) {
		return;
	}

	if ( ! boomerang_board_editing_enabled( $post->post_parent ) ) {
		return;
	}

	$board = $post->post_parent;
	$form  = new Boomerang_Form( $board, $post );

	$title   = $form->title ?? '';
	$content = $form->content ?? '';
	$tags    = $form->tags ?? array();

	$labels = boomerang_get_labels( $board );
	?>

	<div id="boomerang-edit-screen-modal">
		<div id="boomerang-edit-screen">
			<h2>
				<?php
				printf(
				/* translators: %s: Publish date. */
					esc_html__( 'Edit %s', 'boomerang' ),
					esc_html( $labels['singular'] ),
				);
				?>
			</h2>
			<form id="boomerang-edit-form" method="post" enctype='multipart/form-data' data-id="<?php echo esc_attr( $post->ID ); ?>" data-board="<?php echo esc_attr( $board ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang-edit-nonce' ) ); ?>">
				<fieldset>
					<label for="title"><?php echo esc_html( $labels['title'] ); ?></label>
					<input data-board="<?php echo esc_attr( $board ); ?>" type="text" id="boomerang-title" value="<?php echo esc_html( $title ); ?>" tabindex="1" size="20" name="title"/>
				</fieldset>
				<?php if ( boomerang_board_tags_enabled( $board ) ) : ?>
					<fieldset>
						<label for="tags"><?php echo esc_html( $labels['tags'] ); ?></label>
						<select data-selected='<?php echo wp_json_encode( $tags ); ?>' class="boomerang_edit_form_tags" id="boomerang-tags" name="tags[]" multiple="multiple" style="width: 100%">';

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
					<textarea id="boomerang-content" tabindex="3" name="content" cols="50" rows="6"><?php echo esc_html( $content ); ?> </textarea>
				</fieldset>
				<footer>
					<input name="boomerang_board" id="boomerang-board" type="hidden" value="<?php echo esc_attr( $board ); ?>">
					<button id="submit"><?php echo esc_html( $labels['submit'] ); ?>
						<button id="cancel"><?php esc_html_e( 'Cancel', 'boomerang' ); ?></button>
				</footer>
			</form>
		</div>
	</div>


<?php
}
add_action( 'wp_footer', __NAMESPACE__ . '\create_modal' );

/**
 * Add an edit link in Boomerang footer.
 *
 * @param $post
 *
 * @return void
 */
function add_edit_link( $post ) {
	if ( ! boomerang_can_manage() && get_current_user_id() !== intval( $post->post_author ) ) {
		return;
	}

	if ( ! boomerang_board_editing_enabled( $post->post_parent ) ) {
		return;
	}

	?>

	<a class="boomerang-edit-link">
		<?php if ( ! boomerang_google_fonts_disabled() ) : ?>
			<span class="material-symbols-outlined icon">edit</span>
		<?php endif; ?>
		<?php esc_html_e( 'Edit', 'boomerang' ); ?>
	</a>
<?php
}
add_action( 'boomerang_after_meta_left', __NAMESPACE__ . '\add_edit_link' );

/**
 * Ajax handler for submitting a Boomerang edit.
 *
 * @return void
 */
function edit_boomerang() {
	if ( ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['boomerang_edit_nonce'] ) ),
		'boomerang-edit-nonce'
	) ) {
		$error = new \WP_Error(
			'Boomerang: Failed Security Check while editing Boomerang',
			__( 'Something went wrong.', 'boomerang' )
		);

		wp_send_json_error( $error );

		wp_die();
	}

	if ( ! isset( $_POST['ID'] ) ) {
		$error = new \WP_Error(
			'Boomerang: No ID given to update Boomerang',
			__( 'Something went wrong.', 'boomerang' )
		);

		wp_send_json_error( $error );

		wp_die();
	}

	$args = array(
		'ID' => intval( $_POST['ID'] ),
	);

	if ( isset( $_POST['title'] ) ) {
		$args['post_title'] = sanitize_text_field( $_POST['title'] );
	}

	if ( isset( $_POST['content'] ) ) {
		$args['post_content'] = sanitize_textarea_field( $_POST['content'] );
	}

	$content = sanitize_textarea_field( $_POST['content'] );
	$board   = intval( $_POST['board'] );

	if ( ! empty( $_POST['tags'] ) ) {
		if ( is_array( $_POST['tags'] ) ) {
			$tags = array_map( 'sanitize_text_field', $_POST['tags'] );
		} else {
			$tags = sanitize_text_field( $_POST['tags'] );
		}
	}

	$post_id = wp_update_post( $args, true );

	if ( isset( $tags ) ) {
		wp_set_post_terms( $post_id, $tags, 'boomerang_tag' );
	} else {
		wp_set_post_terms( $post_id, array(), 'boomerang_tag' );
	}

	$return = array(
		'post' => get_post( $post_id ),
		'tags' => wp_kses(
			boomerang_get_tag_list( get_post( $post_id ) ),
			array(
				'span' => array(
					'rel'   => array(),
					'class' => array(),
				),
				'div'  => array(
					'class'      => array(),
					'id'         => array(),
					'data-nonce' => array(),
				),
			)
		),
	);

	wp_send_json_success( $return );

	wp_die();
}
add_action( 'wp_ajax_edit_boomerang', __NAMESPACE__ . '\edit_boomerang' );