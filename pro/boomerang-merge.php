<?php
/**
 * Merge Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create a modal to hold our merge form.
 *
 * @return void
 */
function create_merge_modal() {
	global $post;

	if ( ! is_singular() || 'boomerang' !== $post->post_type ) {
		return;
	}

	$board = $post->post_parent;
	$form  = new Boomerang_Form( $board, $post );

	$title   = $form->title ?? '';
	$content = $form->content ?? '';
	$tags    = $form->tags ?? array();

	$labels = boomerang_get_labels( $board );
	?>

	<div id="boomerang-merge-screen-modal">
		<div id="boomerang-merge-screen">
			<h2>
				<?php
				printf(
				/* translators: %s: Publish date. */
					esc_html__( 'Merge %s', 'boomerang' ),
					esc_html( ucwords( $labels['singular'] ) ),
				);
				?>
			</h2>
			<p><?php esc_html_e( 'Keep things organised by merging duplicate Boomerangs', 'boomerang' ); ?></p>
			<form id="boomerang-merge-form" method="post" enctype='multipart/form-data' data-id="<?php echo esc_attr( $post->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang-merge-nonce' ) ); ?>">
				<p class="merge-intro">
					<?php
					printf(
					/* translators: %s: Publish date. */
						esc_html__( 'Merge %s into:', 'boomerang' ),
						'<strong>' . esc_html( ucwords( $post->post_title ) ) . '</strong>',
					);
					?>
				</p>
				<fieldset>
					<select class="boomerang-merge-select">
					<?php

					$boomerangs = get_posts(
						array(
							'post_type'    => 'boomerang',
							'post_parent'  => $post->post_parent,
							'post_status'  => 'any',
							'numberposts'  => -1,
							'post__not_in' => array( $post->ID ),
						)
					);

					echo '<option></option>';
					foreach ( $boomerangs as $boomerang ) {
						echo '<option value="' . esc_attr( $boomerang->ID ) . '">' . esc_attr( $boomerang->post_title ) . '</option>';
					}

					?>
					</select>
				</fieldset>
				<p class="merge-disclaimer"><?php esc_html_e( 'Remember: votes will also merge into the selected Boomerang', 'boomerang' ); ?></p>
				<footer>
					<input name="boomerang_board" id="boomerang-board" type="hidden" value="<?php echo esc_attr( $board ); ?>">
					<button id="submit"><?php echo esc_html( $labels['submit'] ); ?>
					<button id="cancel"><?php esc_html_e( 'Cancel', 'boomerang' ); ?></button>
				</footer>
				<p class="merge-result"></p>
			</form>
		</div>
	</div>


	<?php
}
add_action( 'wp_footer', __NAMESPACE__ . '\create_merge_modal' );

/**
 * Add an edit link in Boomerang footer.
 *
 * @param $post
 *
 * @return void
 */
function add_merge_button( $post ) {
	if ( ! boomerang_can_manage() ) {
		return;
	}

	if ( is_merged( $post ) ) {
		return;
	}

	?>

	<a class="boomerang-action boomerang-merge-button" title="<?php esc_attr_e( 'Merge', 'boomerang' ); ?>">
		<?php if ( boomerang_google_fonts_disabled() ) : ?>
			<span><?php esc_html_e( 'Merge', 'boomerang' ); ?></span>
		<?php else : ?>
			<span class="material-symbols-outlined">merge</span>
		<?php endif; ?>
	</a>
	<?php
}
add_action( 'boomerang_admin_actions_start', __NAMESPACE__ . '\add_merge_button' );

/**
 * Ajax handler for submitting a Boomerang edit.
 *
 * @return void
 */
function merge_boomerang() {
	if ( ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
		'boomerang-merge-nonce'
	) ) {
		$error = new \WP_Error(
			'Boomerang: Failed Security Check while editing Boomerang',
			esc_html__( 'Something went wrong', 'boomerang' )
		);

		wp_send_json_error( $error );

		wp_die();
	}

	if ( empty( $_POST['primary'] ) ) {
		$error = new \WP_Error(
			'Boomerang: No ID given to update Boomerang',
			__( 'Please choose a Boomerang to merge into', 'boomerang' )
		);

		wp_send_json_error( $error );

		wp_die();
	}

	update_post_meta( $_POST['ID'], 'merged_into', sanitize_text_field( $_POST['primary'] ) );

	$mergees = get_post_meta( $_POST['primary'], 'swallowed_up', true );
	if ( ! $mergees ) {
		$mergees = array( sanitize_text_field( $_POST['ID'] ) );
		update_post_meta( $_POST['primary'], 'swallowed_up', $mergees );
	} else {
		$mergees[] = intval( $_POST['ID'] );
		update_post_meta( $_POST['primary'], 'swallowed_up', $mergees );
	}

	$primary_votes   = get_post_meta( $_POST['primary'], 'boomerang_votes', true );
	$secondary_votes = get_post_meta( $_POST['ID'], 'boomerang_votes', true );

	// Combine votes.
	$merged_votes = (int) $primary_votes + (int) $secondary_votes;
	update_post_meta( $_POST['primary'], 'boomerang_votes', sanitize_text_field( $merged_votes ) );

	$comments = get_comments( array( 'post_id' => $_POST['ID'] ) );

	$comment_count = array();
	if ( $comments ) {
		foreach ( $comments as $comment ) {
			$private = get_comment_meta( $comment->comment_ID, 'boomerang_private_note', true );
			$system  = get_comment_meta( $comment->comment_ID, 'system_note', true );

			$data = array(
				'comment_post_ID'      => $_POST['primary'],
				'comment_content'      => $comment->comment_content,
				'user_id'              => $comment->user_id,
				'comment_author'       => $comment->comment_author,
				'comment_author_email' => $comment->comment_author_email,
				'comment_author_url'   => $comment->comment_author_url,
				'comment_date'         => $comment->comment_date,
				'comment_date_gmt'     => $comment->comment_date_gmt,
			);

			$comment_id = wp_insert_comment( $data );

			if ( $private ) {
				add_comment_meta( $comment_id, 'boomerang_private_note', 1 );
			}

			if ( $system ) {
				add_comment_meta( $comment_id, 'system_note', $system );
			}

			$comment_count[] = $comment_id;
		}
	}

	if ( empty( $comment_count ) ) {
		$migrated_comments = 0;
	} else {
		$migrated_comments = count( $comment_count );
	}



	$commentdata = array(
		'user_id'         => get_current_user_id(),
		'comment_post_ID' => $_POST['primary'],
		'comment_meta'    => array(
			'boomerang_private_note' => true,
			'system_note'            => 'boomerang_merged',
		),
	);

	$commentdata['comment_content'] = sprintf(
	/* translators: %1$s: Singular form of this board's Boomerang name %2$s: Link to Boomerang this was merged into %3$s: initial votes */
		__( '%1$s has been merged into this %2$s, %3$s and %4$s comments, have been carried over.', 'boomerang' ),
		'<a href="' . esc_url( get_the_permalink( $_POST['ID'] ) ) . '">' . esc_html( $_POST['ID'] ) . '</a>',
		get_singular( get_post( $_POST['ID'] )->post_parent ),
		isset( $secondary_votes ) ? sanitize_text_field( $secondary_votes ) . ' votes' : '',
		sanitize_text_field( $migrated_comments ),
	);

	wp_insert_comment( $commentdata );



	$return = array(
		'message' => esc_html__( 'Boomerangs merged successfully' ),
	);

	wp_send_json_success( $return );

	wp_die();
}
add_action( 'wp_ajax_merge_boomerang', __NAMESPACE__ . '\merge_boomerang' );

/**
 * Add a banner to top of Boomerangs to show if it was merged into another and is therefore dormant.
 *
 * @param $post
 *
 * @return void
 */
function add_merged_banner( $post ) {
	if ( ! is_merged( $post ) ) {
		return;
	}

	if ( boomerang_can_manage() ) {
		echo '<div class="boomerang-banner merge-banner">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">merge</span>';
		}

		$text = sprintf(
		/* translators: %1$s: Singular form of this board's Boomerang name %2$s: Link to Boomerang this was merged into */
			__( 'This %1$s has been merged into %2$s.', 'boomerang' ),
			get_singular( $post->post_parent ),
			'<a href="' . esc_url( get_the_permalink( is_merged( $post ) ) ) . '">' . esc_html( is_merged( $post ) ) . '</a>',
		);

		echo '<p>' . wp_kses_post( $text ) . '</p>';

		echo '</div>';

	}
}
add_action( 'boomerang_archive_boomerang_start', __NAMESPACE__ . '\add_merged_banner' );
add_action( 'boomerang_single_boomerang_start', __NAMESPACE__ . '\add_merged_banner' );

/**
 * Add a banner to top of Boomerangs to show if it was merged into another and is therefore dormant.
 *
 * @param $post
 *
 * @return void
 */
function add_mergee_list( $post ) {
	$mergees = get_swallowed_up_boomerangs( $post );
	if ( empty( $mergees ) ) {
		return;
	}

	?>

	<div class="boomerang-merge-container">

	<h3 class="boomerang-actions-heading"><?php esc_html_e( 'Contains the following merges:', 'boomerang' ); ?></h3>

		<ul>

	<?php foreach ( $mergees as $mergee ) : ?>

		<li class="boomerang-mergee">

		<?php if ( ! boomerang_google_fonts_disabled() ) : ?>
			<span class="material-symbols-outlined">merge</span>
		<?php endif; ?>

		<a class="boomerang-mergee" href="<?php echo esc_url( get_the_permalink( $mergee ) ); ?>">
			<?php echo esc_html( get_the_title( $mergee ) ); ?>
		</a>

		</li>

	<?php endforeach; ?>

		</ul>
	</div>

	<?php
}
add_action( 'boomerang_actions_container_start', __NAMESPACE__ . '\add_mergee_list' );

/**
 * Checks to see if a Boomerang has been merged into another.
 *
 * @param $post
 *
 * @return mixed
 */
function is_merged( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	return get_post_meta( $post->ID, 'merged_into', true );
}

/**
 * Checks to see if a Boomerang contains other Boomerangs from a merge operation.
 *
 * @param $post
 *
 * @return mixed
 */
function get_swallowed_up_boomerangs( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	return get_post_meta( $post->ID, 'swallowed_up', true );
}

/**
 * Filter posts so merged Boomerangs are only shown to managers and the author.
 *
 * @param $post
 *
 * @return void
 */
function filter_merged_boomerangs( $query ) {
	if ( 'boomerang' === $query->get( 'post_type' ) ) {
		if ( ! boomerang_can_manage() ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'     => 'merged_into',
						'compare' => 'NOT EXISTS',
					),
				)
			);
		}
	}

	return $query;
}
add_filter( 'pre_get_posts', __NAMESPACE__ . '\filter_merged_boomerangs', 30 );
