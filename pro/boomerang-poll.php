<?php
/**
 * Poll Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks that a given slug isn't already in use by another poll, sitewide.
 *
 * @param $slug
 *
 * @return bool
 */
function check_unique_poll_slug( $slug ) {
	$polls = get_polls( false, true );
	$slugs = array_column( $polls, 'poll_slug' );

	if ( in_array( $slug, $slugs, false ) ) {
		return false;
	}

	return true;
}

/**
 * Generates a unique ID for a new poll.
 *
 * @return int|mixed|string
 */
function get_next_poll_id() {
	$counter = get_option( 'boomerang_poll_counter' );

	if ( ! $counter ) {
		add_option( 'boomerang_poll_counter', 1 );
		return 1;
	} else {
		$counter = ++$counter;

		update_option( 'boomerang_poll_counter', $counter );
		return $counter;
	}
}

/**
 * Checks to see if any polls exist on the site, or for a given board.
 *
 * @param $post
 *
 * @return bool
 */
function has_polls( $post = false, $disabled = false ) {
	if ( $post ) {
		$posts = array( $post );
	} else {
		$args = array(
			'fields'      => 'ids',
			'numberposts' => -1,
			'post_type'   => 'boomerang_board',
		);

		$posts = get_posts( $args );
	}

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post_id ) {
			$meta = get_post_meta( $post_id, 'boomerang_board_options', true );

			if ( ! empty( $meta['polls'] ) ) {
				if ( ! $disabled ) {
					foreach ( $meta['polls'] as $poll ) {
						if ( ! empty( $poll['poll_enabled'] ) ) {
							return true;
						}
					}
				} else {
					return true;
				}
			}
		}
	}
}

/**
 * Gets an array of polls on the site, or for a given board.
 *
 * @param $post
 *
 * @return array|mixed
 */
function get_polls( $post = false, $disabled = false ) {
	if ( $post ) {
		$posts = array( $post );
	} else {
		$args = array(
			'fields'      => 'ids',
			'numberposts' => -1,
			'post_type'   => 'boomerang_board',
		);

		$posts = get_posts( $args );
	}

	$polls = array();

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post_id ) {
			$meta = get_post_meta( $post_id, 'boomerang_board_options', true );

			if ( ! empty( $meta['polls'] ) ) {
				if ( ! $disabled ) {
					foreach ( $meta['polls'] as $poll ) {
						if ( ! empty( $poll['poll_enabled'] ) ) {
							$polls[] = $poll;
						}
					}
				} else {
					foreach ( $meta['polls'] as $poll ) {
						$polls[] = $poll;
					}
				}
			}
		}
	}

	return $polls;
}

/**
 * Checks whether a given poll should be visible on the current page.
 *
 * @param $poll
 *
 * @return bool
 */
function poll_is_visible( $poll ) {
	$id = $poll['poll_id'];

	$user_polls = get_user_meta( get_current_user_id(), 'boomerang_submitted_polls', true );

	if ( empty( $poll['poll_debug_enabled'] ) ) {
		// If not in debug mode, Admins won't see the poll.
		if ( current_user_can( 'manage_options' ) ) {
			return false;
		}

		// If not in debug mode, don't allow multiple voting.
		if ( ! empty( $user_polls ) && in_array( $id, $user_polls ) ) {
			return false;
		}
	}

	$visibility = $poll['poll_visibility'];

	switch ( $visibility ) {
		case 'all':
			return true;
			break;
		case 'home':
			if ( is_home() ) {
				return true;
			}
			break;
		case 'board':
			if ( is_singular( 'boomerang' ) ) {
				return true;
			} else {
				global $post;
				if ( $post && ( has_shortcode( $post->post_content,
							'boomerang_board' ) || has_block( 'boomerang-block/shortcode-gutenberg',
							$post->post_content ) ) ) {
					return true;
				}
			}
			break;
		case 'board_archive':
			global $post;
			if ( $post && ( has_shortcode( $post->post_content,
						'boomerang_board' ) || has_block( 'boomerang-block/shortcode-gutenberg',
						$post->post_content ) ) ) {
				return true;
			}
			break;
	}

	return false;
}

/**
 * Render a poll.
 *
 * @param $poll
 *
 * @return false|string
 */
function render_polls() {
	$polls = get_polls();

	if ( empty( $polls ) || is_admin() || ! is_user_logged_in()  ) {
		return;
	}

	foreach ( $polls as $poll ) {
		$location   = empty( $poll['poll_location'] ) ? 'bottom-left' : $poll['poll_location'];
		$board = $poll['poll_board'];

		if ( poll_is_visible( $poll ) ) : ?>
			<div class="boomerang-poll-wrapper <?php echo esc_attr( $location ) ?>"
			     data-board="<?php echo esc_attr( $board ); ?>"
			     data-id="<?php echo esc_attr( $poll['poll_id'] ); ?>"
			     data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_poll_handler' ) ); ?>">
				<?php require BOOMERANG_PATH . 'pro/partials/poll.php'; ?>
			</div>

		<?php endif;

	}
}
add_action( 'wp_footer', __NAMESPACE__ . '\render_polls' );

/**
 * Ajax handler to process a poll.
 *
 * @return void
 */
function poll_handler() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
		'boomerang_poll_handler'
	) ) {
		$error = new \WP_Error(
			'Boomerang: Failed Security Check on processing of poll',
			__( 'Something went wrong.', 'boomerang' )
		);

		wp_send_json_error( $error );
	}

	$poll_id = isset( $_POST['poll_id'] ) ? absint( $_POST['poll_id'] ) : 0;
	$board   = isset( $_POST['board'] ) ? absint( $_POST['board'] ) : 0;
	$value   = isset( $_POST['value'] ) ? absint( $_POST['value'] ) : 0;
	$user_id = get_current_user_id();

	// Save poll ID to user meta
	$user_meta = get_user_meta( $user_id, 'boomerang_submitted_polls', true );
	if ( ! $user_meta ) {
		update_user_meta( $user_id, 'boomerang_submitted_polls', array( $poll_id ) );
	} else {
		if ( ! in_array( $poll_id, $user_meta ) ) {
			$user_meta[] = $poll_id;
			update_user_meta( $user_id, 'boomerang_submitted_polls', $user_meta );
		}
	}

	if ( 'none' != $value ) {
		// Save user ID to Boomerang
		$boomerang_meta = get_post_meta( $value, 'user_poll_vote', true );
		if ( ! $boomerang_meta ) {
			update_post_meta( $value, 'user_poll_vote', array( $user_id ) );
		} else {
			if ( ! in_array( $user_id, $boomerang_meta ) ) {
				$boomerang_meta[] = $user_id;
				update_post_meta( $value, 'user_poll_vote', $boomerang_meta );
			}
		}
	}

	// Save results as board meta to display as report on admin side
	$board_meta = get_post_meta( $board, 'polls', true );
	if ( ! $board_meta ) {
		$board_meta = array(
			$poll_id => array( $value ),
		);
		update_post_meta( $board, 'polls', $board_meta );
	} else {
		if ( array_key_exists( $poll_id, $board_meta ) ) {
			$poll_voters = $board_meta[$poll_id];
			$poll_voters[] = $value;
			$board_meta[$poll_id] = $poll_voters;
			update_post_meta( $board, 'polls', $board_meta );
		} else {
			$board_meta[$poll_id] = array( $value );
			update_post_meta( $board, 'polls', $board_meta );
		}
	}

	$return = array(
		'message'    => __( 'Poll Processed Successfully', 'boomerang' ),
	);

	wp_send_json_success( $return );

	wp_die();
}
add_action( 'wp_ajax_poll_handler', __NAMESPACE__ . '\poll_handler' );

function has_voted_in_poll( $user_id, $poll_id ) {

}
