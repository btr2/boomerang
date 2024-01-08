<?php
/**
 * Bug Report Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks if 'mark as bug' is enabled for a Boomerang's board.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_mark_as_bug_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_mark_as_bug'] ?? false;
}

/**
 * Checks to see if a Boomerang is marked as a bug.
 *
 * @param $post
 *
 * @return mixed
 */
function is_a_bug( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	return get_post_meta( $post->ID, 'marked_as_bug', true );
}

/**
 * Adds an action button in Boomerang's admin area.
 *
 * @param $post
 *
 * @return void
 */
function add_mab_action( $post ) {
	if ( ! boomerang_board_mark_as_bug_enabled( $post->post_parent ) ) {
		return;
	}
	?>

	<a title="<?php esc_attr_e( 'Mark as Bug', 'boomerang' ); ?>" id="boomerang_mark_as_bug" class="boomerang-action <?php echo is_a_bug( $post ) ? 'bug' : ''; ?>" data-bug="<?php echo esc_attr( is_a_bug( $post ) ); ?>">
		<?php if ( boomerang_google_fonts_disabled() ) : ?>
			<span><?php esc_html_e( 'Mark as Bug', 'boomerang' ); ?></span>
		<?php else : ?>
			<span class="material-symbols-outlined">bug_report</span>
		<?php endif; ?>
	</a>

	<?php
}
add_action( 'boomerang_admin_actions_start', __NAMESPACE__ . '\add_mab_action' );

/**
 * Ajax handler to mark/unmark a Boomerang as a bug within the frontend admin area.
 *
 * @return void
 */
function process_mark_as_bug() {
	if ( ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
		'boomerang_admin_area'
	) ) {
		$error = new WP_Error(
			'Boomerang: Failed Security Check on Change of Bug Status',
			__( 'Something went wrong.', 'boomerang' )
		);

		wp_send_json_error( $error );
	}

	$post_id = sanitize_text_field( $_POST['post_id'] );

	if ( isset( $post_id ) ) {
		if ( is_a_bug( get_post( $post_id ) ) ) {
			delete_post_meta( $post_id, 'marked_as_bug' );
			$new_bug_status = false;
			do_action( 'boomerang_unmarked_as_bug', $post_id );
		} else {
			update_post_meta( $post_id, 'marked_as_bug', true );
			$new_bug_status = true;
			do_action( 'boomerang_marked_as_bug', $post_id );
		}
	}

	$return = array(
		'message'    => __( 'Bug Status Changed', 'boomerang' ),
		'bug_status' => $new_bug_status,
	);

	wp_send_json_success( $return );

	wp_die();
}
add_action( 'wp_ajax_process_mark_as_bug', __NAMESPACE__ . '\process_mark_as_bug' );

/**
 * Add a banner to top of Boomerangs to show it is marked as a bug.
 *
 * @param $post
 *
 * @return void
 */
function add_marked_as_bug_banner( $post ) {
	if ( ! boomerang_board_mark_as_bug_enabled( $post->post_parent ) ) {
		return;
	}

	if ( ! is_a_bug( $post ) ) {
		return;
	}

	if ( boomerang_can_manage() || get_current_user_id() === intval( $post->post_author ) ) {
		echo '<div class="boomerang-banner bug-banner">';

		if ( ! boomerang_google_fonts_disabled() ) {
			echo '<span class="material-symbols-outlined">bug_report</span>';
		}

		$text = sprintf(
		/* translators: %s: Singular form of this board's Boomerang name */
			__( 'This %s has been marked as a bug.', 'boomerang' ),
			get_singular( $post->post_parent ),
		);

		echo '<p>' . esc_html( $text ) . '</p>';

		echo '</div>';

	}
}
add_action( 'boomerang_archive_boomerang_start', __NAMESPACE__ . '\add_marked_as_bug_banner' );
add_action( 'boomerang_single_boomerang_start', __NAMESPACE__ . '\add_marked_as_bug_banner' );

/**
 * Filter posts so bug reports are only shown to managers and the author.
 *
 * @param $post
 *
 * @return void
 */
function filter_bug_reports( $posts, $query ) {
	if ( ! is_admin() && 'boomerang' === $query->get( 'post_type' ) ) {
		if ( $query->is_single() ) {
			if ( ! boomerang_board_mark_as_bug_enabled( $posts[0]->post_parent ) ) {
				return $posts;
			}
		} else {
			if ( ! boomerang_board_mark_as_bug_enabled( $query->get( 'post_parent' ) ) ) {
				return $posts;
			}
		}

		if ( boomerang_can_manage() ) {
			return $posts;
		}

		foreach ( $posts as $key => $post ) {
			$marked_as_bug = get_post_meta( $post->ID, 'marked_as_bug', true );

			if ( $marked_as_bug && get_current_user_id() !== intval( $post->post_author ) ) {
				unset( $posts[ $key ] );
			}
		}

		$posts = array_values( $posts );
	}

	return $posts;
}
add_filter( 'the_posts', __NAMESPACE__ . '\filter_bug_reports', 10, 2 );
