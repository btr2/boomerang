<?php
/**
 * Pro functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Board Getters *****************************************************************************************************/

/**
 * Checks to see if a Google reCAPTCHA is enabled for our board.
 *
 * @param $board
 *
 * @return false|mixed
 */
function boomerang_board_recaptcha_enabled( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_recaptcha'] ?? false;
}

/**
 * Gets the required time gap between votes.
 *
 * @param $post
 *
 * @return int|mixed
 */
function boomerang_board_get_guest_vote_time_gap( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['guest_vote_time_gap'] ?? 60;
}

/**
 * Get the form headings from a boards settings screen.
 *
 * @param $board
 *
 * @return array
 */
function boomerang_board_get_form_headings( $board ) {
	$board = get_post( $board );

	$meta = get_post_meta( $board->ID, 'boomerang_board_options', true );

	return array(
		'heading' => $meta['label_form_heading'] ?? '',
		'subheading' => $meta['label_form_subheading'] ?? '',
	);
}

/**
 * Get the value of the `display_voter_avatars` option for a Boomerang board.
 *
 * @param mixed $post The board post object or ID (optional).
 *
 * @return bool The value of the `display_voter_avatars` option, or false if not set.
 */
function boomerang_board_display_voter_avatars( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['display_voter_avatars'] ?? false;
}

/** Single Boomerangs *************************************************************************************************/

/**
 * Add a lightbox for our Boomerang attachments.
 *
 * @return void
 */
function lightbox_enqueues() {
	if ( is_singular( 'boomerang' ) ) {
		wp_enqueue_style( 'boomerang-lightbox', BOOMERANG_URL . 'pro/assets/css/simple-lightbox.min.css', null, BOOMERANG_VERSION );
		wp_enqueue_script(
			'boomerang-lightbox',
			BOOMERANG_URL . 'pro/assets/js/simple-lightbox.jquery.min.js',
			array( 'jquery' ),
			BOOMERANG_VERSION
		);
	}
}
// add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\lightbox_enqueues' );

/**
 * Add an attachment area under the Boomerang content.
 *
 * @param $post
 *
 * @return void
 */
function attachment_area( $post ) {
	echo '<div class="boomerang-attachments">';

	do_action( 'boomerang_attachment_area_start', $post );

	$images = get_attached_media( 'image', $post );

	$images = apply_filters( 'boomerang_attachment_images', $images );

	if ( $images ) {
		echo '<div class="boomerang-image-attachments">';

		do_action( 'boomerang_image_attachment_area_start', $post );

		foreach ( $images as $image ) {
			echo '<a href="' . esc_url( wp_get_attachment_url( $image->ID, 'full' ) ) . '">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">image</span>';
		}

		echo esc_html( basename( get_attached_file( $image->ID ) ) );
		echo '</a>';
		}

		do_action( 'boomerang_image_attachment_area_end', $post );

		echo '</div>';
	}

	$files = get_attached_media( 'application', $post );

	$files = apply_filters( 'boomerang_attachment_files', $files );

	if ( $files ) {
		echo '<div class="boomerang-file-attachments">';

		do_action( 'boomerang_file_attachment_area_start', $post );

		foreach ( $files as $file ) {
			echo '<a href="' . esc_url( wp_get_attachment_url( $file->ID, 'full' ) ) . '">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">attach_file</span>';
		}

		echo esc_html( basename( get_attached_file( $file->ID ) ) );
		echo '</a>';
		}

		do_action( 'boomerang_file_attachment_area_end', $post );

		echo '</div>';
	}


	do_action( 'boomerang_attachment_area_end', $post );

	echo '</div>';
}
add_action( 'boomerang_single_boomerang_footer_start', __NAMESPACE__ . '\attachment_area' );

/** Avatar Votes ******************************************************************************************************/

function add_voter_avatars( $post ) {
	if ( ! boomerang_board_display_voter_avatars( $post ) ) {
		return;
	}

	$voter_data = get_post_meta( $post->ID, 'boomerang_unique_voters', true );

	if ( $voter_data && ! empty( $voter_data ) ) {
		$count = count( $voter_data );
		$max = apply_filters( 'boomerang_max_voter_avatars', 5 );
		$remainder = $count - $max;

		echo '<div class="voter-avatar-container"><div class="voter-avatars">';

		foreach ( array_slice( $voter_data, 0, $max ) as $voter ) {
			echo '<div class="voter-avatar">';
			echo get_avatar( $voter, 32 );
			echo '</div>';
		}

	echo '</div>';

	if ( $remainder > 0 ) {
		echo '<span class="remainder">+' . esc_html( $remainder ) . '</span>';
	}

	echo '</div>';
	}
}
add_action( 'boomerang_after_meta_left', __NAMESPACE__ . '\add_voter_avatars' );

/** Boomerang Form ****************************************************************************************************/

/**
 * Display headings at the top of a Boomerang form.
 *
 * @param $board
 *
 * @return void
 */
function add_form_headings( $board ) {
	$headings = boomerang_board_get_form_headings( $board );

	echo '<h2 class="boomerang-form-heading">' . esc_html( $headings['heading'] ) . '</h2>';
	echo '<h3 class="boomerang-form-subheading">' . esc_html( $headings['subheading'] ) . '</h3>';
}
add_action( 'boomerang_form_fields_start', __NAMESPACE__ . '\add_form_headings' );

/** Locked (private) Boomerangs ***************************************************************************************/

/**
 * Checks to see if a Boomerang is locked.
 *
 * @param $post
 *
 * @return mixed
 */
function is_locked( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	return get_post_meta( $post->ID, 'is_locked', true );
}

/**
 * Add a banner to top of Boomerangs to warn that Boomerang is locked.
 *
 * @param $post
 *
 * @return void
 */
function add_locked_banner( $post ) {
	if ( ! is_locked( $post ) ) {
		return;
	}

	if ( boomerang_can_manage() ) {
		echo '<div class="boomerang-banner locked-banner">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">lock</span>';
		}

		$text = sprintf(
		/* translators: %s: Singular form of this board's Boomerang name */
			__( 'This %s is private and only visible to team members.', 'boomerang' ),
			get_singular( $post->post_parent ),
		);

		echo '<p>' . esc_html( $text ) . '</p>';

		echo '</div>';

	}
}
add_action( 'boomerang_archive_boomerang_start', __NAMESPACE__ . '\add_locked_banner' );
add_action( 'boomerang_single_boomerang_start', __NAMESPACE__ . '\add_locked_banner' );

/**
 * Filter posts so bug reports are only shown to managers and the author.
 *
 * @param $post
 *
 * @return void
 */
function filter_locked_boomerangs( $query ) {
	if ( 'boomerang' === $query->get( 'post_type' ) ) {
		if ( ! boomerang_can_manage() ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'     => 'is_locked',
						'compare' => 'NOT EXISTS',
					),
				)
			);
		}
	}

	return $query;
}
add_filter( 'pre_get_posts', __NAMESPACE__ . '\filter_locked_boomerangs', 20 );


/**
 * Adds a visibility menu item in our frontend admin area.
 *
 * @return false|string
 */
function add_visibility_control() {
	if ( 'pending' === get_post_status() ) {
		$heading      = __( 'Approve', 'boomerang' );
		$text         = __( 'Publish this new entry.', 'boomerang' );
		$button_label = __( 'Approve now', 'boomerang' );
		$action       = 'publish';
	} else if ( is_locked( get_post() ) ) {
		$heading      = __( 'Unlock', 'boomerang' );
		$text         = __( 'Make this entry visible to all users.', 'boomerang' );
		$button_label = __( 'Make public', 'boomerang' );
		$action       = 'unlock';
	} else {
		$heading      = __( 'Lock', 'boomerang' );
		$text         = __( 'Hide this entry from non-team members.', 'boomerang' );
		$button_label = __( 'Make private', 'boomerang' );
		$action       = 'lock';
	}

	ob_start();
	?>

	<div class="boomerang-visibility boomerang-control style-2">
		<div class="control-header">
			<?php if ( ! boomerang_google_fonts_disabled() ) : ?>
				<span class="material-symbols-outlined icon">visibility</span>
			<?php endif; ?>
			<h3><?php esc_html_e( 'Visibility', 'boomerang' ); ?></h3>
			<?php if ( boomerang_google_fonts_disabled() ) : ?>
				<span class="chevron">&#x276F;</span>
			<?php else : ?>
				<span class="material-symbols-outlined chevron">chevron_right</span>
			<?php endif; ?>
		</div>
		<div class="control-content">
			<fieldset>
				<h4><?php echo esc_html( $heading ); ?></h4>
				<p><?php echo esc_html( $text ); ?></p>
				<div id="boomerang-post-status-submit" class="wp-element-button button control-content-inline-button" data-action="<?php echo esc_attr( $action ); ?>">
					<span><?php echo esc_html( $button_label ); ?></span>
				</div>
			</fieldset>
		</div>
	</div>

	<?php
	return ob_get_flush();
}
add_action( 'boomerang_admin_controls_end', __NAMESPACE__ . '\add_visibility_control' );

/**
 * Ajax handler to change post status on frontend admin area.
 *
 * @return void
 */
function process_post_status_submit() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
		'boomerang_admin_area'
	) ) {
		$error = new \WP_Error(
			'Boomerang: Failed Security Check on Post Status Change',
			__( 'Something went wrong.', 'boomerang' )
		);

		wp_send_json_error( $error );
	}

	$post_id    = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
	$the_action = isset( $_POST['the_action'] ) ? sanitize_text_field( wp_unslash( $_POST['the_action'] ) ) : '';

	if ( isset( $the_action ) && isset( $post_id ) ) {
		if ( 'publish' === $the_action ) {
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				)
			);
		} else if ( 'lock' === $the_action ) {
			update_post_meta( $post_id, 'is_locked', true );
			do_action( 'boomerang_locked', $post_id );
		} else if ( 'unlock' === $the_action ) {
			delete_post_meta( $post_id, 'is_locked' );
			do_action( 'boomerang_unlocked', $post_id );
		}

	}

	$return = array(
		'message' => __( 'Post Status Set', 'boomerang' ),
		'action'  => $the_action,
	);

	wp_send_json_success( $return );

	wp_die();
}
add_action( 'wp_ajax_process_post_status_submit', __NAMESPACE__ . '\process_post_status_submit' );

/** Comments **********************************************************************************************************/

/**
 * Adds a class if a comment is set to a private note.
 *
 * @param $classes
 * @param $comment
 *
 * @return mixed
 */
function boomerang_add_comment_classes( $classes, $comment ) {
	$private_note = get_comment_meta( $comment->comment_ID, 'boomerang_private_note', true );

	if ( $private_note ) {
		$classes[] = 'private_note';
	}

	return $classes;
}
add_filter( 'boomerang_comment_classes', __NAMESPACE__ . '\boomerang_add_comment_classes', 10, 2 );

function boomerang_add_private_note_label( $comment ) {
	$private_note = get_comment_meta( $comment->comment_ID, 'boomerang_private_note', true );

	if ( $private_note ) {

		echo '<p class="private-note-label">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">lock</span>';
		}

		esc_html_e( 'Private', 'boomerang' );

		echo '</p>';
	}
}
add_action( 'boomerang_comment_above_author_name', __NAMESPACE__ . '\boomerang_add_private_note_label' );

/**
 * Filter comments so only Boomerang managers can see private notes.
 *
 * @param $comments
 * @param $post_id
 *
 * @return mixed
 */
function boomerang_filter_comments( $comments, $post_id ) {
	foreach ( $comments as $index => $comment ) {
		$private_note = get_comment_meta( $comment->comment_ID, 'boomerang_private_note', true );

		if ( $private_note && ! boomerang_can_manage() ) {
			unset( $comments[ $index ] );
		}
	}

	return $comments;
}
add_filter( 'comments_array', __NAMESPACE__ . '\boomerang_filter_comments', 10, 2 );

/**
 * Filter recent comments widget, because, being WordPress it has to use a completely different system...
 */
function boomerang_filter_recent_comments_widget( $args ) {
	$args['meta_query'] = array(
		array(
			'key'     => 'boomerang_private_note',
			'value'   => '1',
			'compare' => 'NOT EXISTS',
		),
	);

	return $args;
}
add_filter( 'widget_comments_args', __NAMESPACE__ . '\boomerang_filter_recent_comments_widget', 10, 2 );

/**
 * Filter recent comments RSS feed, because, being WordPress it has to use a completely different system...
 */
function boomerang_filter_comments_rss( $args ) {
	global $wpdb;

	return $where . " AND {$wpdb->posts}.post_type NOT IN ( 'boomerang' )";
}
add_filter( 'comment_feed_where', __NAMESPACE__ . '\boomerang_filter_comments_rss', 10, 2 );

/**
 * Rebuild comment array, so non-managers don't see private notes in comment count.
 *
 * @param $count
 * @param $post
 *
 * @return int|int[]|mixed|\WP_Comment[]
 */
function boomerang_comments_count( $count, $post ) {
	if ( boomerang_can_manage() ) {
		return $count;
	}

	$args = array(
		'post_id'    => $post->ID,   // Use post_id, not post_ID
		'count'      => true, // Return only the count
		'meta_query' => array(
			array(
				'key'     => 'boomerang_private_note',
				'value'   => '1',
				'compare' => 'NOT EXISTS',
			),
		),
	);

	return get_comments( $args );
}
add_filter( 'boomerang_comments_count', __NAMESPACE__ . '\boomerang_comments_count', 10, 2 );
