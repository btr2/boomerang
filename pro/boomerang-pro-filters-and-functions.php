<?php
/**
 * Pro functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

/** Locked (private) Boomerangs ***************************************************************************************/

/**
 * Add a banner to top of Boomerangs to warn that Boomerang is locked.
 *
 * @param $post
 *
 * @return void
 */
function add_locked_banner( $post ) {
	if ( 'boomerang_locked' !== $post->post_status ) {
		return;
	}

	if ( boomerang_can_manage() ) {
		echo '<div class="locked-banner">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">lock</span>';
		}

		echo '<p>' . esc_html__( 'This Boomerang is private and only visible to team members.', 'boomerang' ) . '</p>';

		echo '</div>';

	}
}
add_action( 'boomerang_archive_boomerang_start', __NAMESPACE__ . '\add_locked_banner' );
add_action( 'boomerang_single_boomerang_start', __NAMESPACE__ . '\add_locked_banner' );

/**
 * Adds a visibility menu item in our frontend admin area.
 *
 * @return false|string
 */
function add_visibility_control() {
	$status = get_post_status();

	if ( 'pending' === $status ) {
		$heading      = __( 'Approve', 'boomerang' );
		$text         = __( 'Publish this new entry.', 'boomerang' );
		$button_label = __( 'Approve now', 'boomerang' );
		$action       = 'publish';
	} elseif ( 'boomerang_locked' === $status ) {
		$heading      = __( 'Unlock', 'boomerang' );
		$text         = __( 'Make this entry visible to all users.', 'boomerang' );
		$button_label = __( 'Make public', 'boomerang' );
		$action       = 'publish';
	} else {
		$heading      = __( 'Lock', 'boomerang' );
		$text         = __( 'Hide this entry from non-team members.', 'boomerang' );
		$button_label = __( 'Make private', 'boomerang' );
		$action       = 'boomerang_locked';
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
				<div id="boomerang-post-status-submit" class="control-content-inline-button" data-action="<?php echo esc_attr( $action ); ?>">
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
 * Add a 'locked' post status, so we can hide Boomerangs from non-team members.
 *
 * @return void
 */
function custom_post_status() {
	register_post_status(
		'boomerang_locked',
		array(
			'label'                     => _x( 'Locked', 'post' ),
			'public'                    => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Archive <span class="count">(%s)</span>', 'Archive <span class="count">(%s)</span>' ),
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\custom_post_status' );

/**
 * Adds our new locked status to the admin Boomerang edit screen.
 *
 * @return void
 */
function add_locked_post_status_to_admin() {
	global $post;
	$complete = '';
	$label    = '';
	if ( $post->post_type == 'boomerang' ) {
		if ( $post->post_status == 'locked' ) {
			$complete = ' selected="selected"';
			$label    = '<span id="post-status-display"> Locked</span>';
		}
		echo '
          <script>
          jQuery(document).ready(function($){
               $("select#post_status").append("<option value=\"archive\" ' . $complete . '>Locked</option>");
               $(".misc-pub-section label").append("' . $label . '");
          });
          </script>
          ';
	}
}
add_action( 'admin_footer-post.php', __NAMESPACE__ . '\add_locked_post_status_to_admin' );

/**
 * Ajax handler to change post status on frontend admin area.
 *
 * @return void
 */
function process_post_status_submit() {
	if ( ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
		'boomerang_admin_area'
	) ) {
		$error = new WP_Error(
			'Boomerang: Failed Security Check on Post Status Change',
			__( 'Something went wrong.', 'boomerang' )
		);

		wp_send_json_error( $error );
	}

	$post_id    = sanitize_text_field( $_POST['post_id'] );
	$the_action = sanitize_text_field( $_POST['the_action'] );

	if ( isset( $the_action ) && isset( $post_id ) ) {
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => $the_action,
			)
		);
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

