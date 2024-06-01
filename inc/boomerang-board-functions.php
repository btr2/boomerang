<?php
/**
 * Functions that relate to individual boards.
 */
namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Getters **/

/**
 * Ghe the slug for a given board. Helper function as WordPress doesn't really provide a good method.
 *
 * @param $post
 *
 * @return string
 */
function boomerang_get_board_slug( $post )  {
	$post = get_post( $post );

	return $post->post_name;
}

/** Conditionals **/

// ToDo: may be added at a later date.
// function is_board( $board_id = false ) {
// 	if ( $board_id ) {
// 		// We can only check blocks for individual Boards.
// 		global $post;
//
// 		if ( has_blocks() ) {
// 			$blocks = parse_blocks( $post->post_content );
// 			foreach( $blocks as $block ) {
// 				if( $block['blockName'] === 'boomerang-block/shortcode-gutenberg' ) {
//
// 				}
// 			}
// 			return true;
// 		}
// 	} else {
// 		global $post;
// 		if ( $post && ( has_shortcode( $post->post_content,
// 					'boomerang_board' ) || has_block( 'boomerang-block/shortcode-gutenberg',
// 					$post->post_content ) ) ) {
// 			return true;
// 		}
// 	}
//
// }

/**
 * Checks whether a given user can manage Boomerangs, or the current user if none specified.
 *
 * @return mixed|true|null
 */
function boomerang_can_manage( $user = false ) {
	if ( ! $user ) {
		$user = wp_get_current_user();
	}

	// Site admins can always manage Boomerangs.
	if ( user_can( $user, 'manage_options') ) {
		return true;
	}

	// todo: More to be added soon.

	return apply_filters('boomerang_can_manage', false, $user );
}



/**
 * Checks if titles are displayed for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_title_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_title'] ?? false;
}

/**
 * Checks if featured images for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_image_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_image'] ?? false;
}

/**
 * Checks if comments are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_comments_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_comments'] ?? false;
}

/**
 * Checks if votes are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_votes_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_votes'] ?? false;
}

/**
 * Checks if bulk votes are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_bulk_votes_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_bulk_votes'] ?? false;
}

/**
 * Checks if down-voting is enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_downvoting_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_downvoting'] ?? false;
}

/**
 * Checks if tags are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_tags_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_tags'] ?? false;
}

/**
 * Checks if statuses are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_statuses_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_statuses'] ?? false;
}

/**
 * Checks if filters are enabled for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_filters_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_filters'] ?? false;
}

/**
 * Checks if authors are displayed for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_author_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_author'] ?? false;
}

/**
 * Checks if author avatars are displayed for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_author_avatar_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_author_avatar'] ?? false;
}

/**
 * Checks if published dates are displayed for a given board or boomerang.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_date_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_date'] ?? true;
}

/**
 * Checks if published dates are displayed in a friendly way.
 *
 * @see human_time_diff()
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_friendly_date_enabled( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['show_friendly_date'] ?? false;
}

/**
 * Returns the default status for new Boomerangs.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_get_default_status( $post ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	if ( ! $meta['require_approval'] ) {
		return 'publish';
	} else {
		return 'pending';
	}
}

/**
 * Returns the container width for Boomerang pages, or 100%.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_get_container_width( $post = false ) {
	$post = boomerang_get_post( $post );

	if ( ! $post) {
		return;
	}

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	if ( empty( $meta['container_width']['width'] ) || empty( $meta['container_width']['unit'] ) ) {
		return '100%';
	} else {
		return implode( $meta['container_width'] );
	}
}

/**
 * Helper function that retrieves the WP_Post object for either a Boomerang, or it's parent board,
 * or the current WP_Post if none is provided.
 *
 * @param $post
 *
 * @return array|WP_Post|null
 */
function boomerang_get_post( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	} else {
		$post = get_post( $post );
	}

	if ( $post && 'boomerang' === $post->post_type ) {
		$post = get_post( $post->post_parent );
	}

	return $post;
}

/** Styles ************************************************************************************************************/

/**
 * Get styling for any Boomerang items.
 *
 * @param $board
 *
 * @return string|void
 */
function boomerang_get_styling( $board = false ) {
	if ( ! $board ) {
		$post = boomerang_get_post();

		if ( empty( $post ) || 'boomerang_board' !== $post->post_type) {
			return;
		} else {
			$board = $post->ID;
		}
	}

	$custom_css = '';

	$meta = get_post_meta( $board, 'boomerang_board_options', true );

	if ( empty( $meta['primary_color'] ) ) {
		$custom_css .= ':root {--boomerang-primary-color:#027AB0;}';
	} else {
		$custom_css .= ':root {--boomerang-primary-color:' . $meta['primary_color'] . ';}';
	}

	if ( empty( $meta['form_background_color'] ) ) {
		$custom_css .= ':root {--boomerang-form-background-color:#f3f4f4;}';
	} else {
		$custom_css .= ':root {--boomerang-form-background-color:' . $meta['form_background_color'] . ';}';
	}

	$custom_css .= ':root {--boomerang-team-color:#fab347;}';

	// Widths are generally handled by pages containing Boomerang shortcodes, so we defer to them
	$custom_css .= ':root {--boomerang-container-width:' . esc_attr( boomerang_get_container_width( $board ) ) . '}';

	if ( boo_fs()->can_use_premium_code__premium_only() ) {
		$options = get_option( 'boomerang_customizer' );
		$terms   = get_terms(
			array(
				'taxonomy'   => 'boomerang_status',
				'hide_empty' => false,
			)
		);

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$color_meta            = get_term_meta( $term->term_id, 'color', true );
				$background_color_meta = get_term_meta( $term->term_id, 'background_color', true );

				$color            = ! empty( $color_meta ) ? esc_attr( $color_meta ) : '#FFFFFF';
				$background_color = ! empty( $background_color_meta ) ? esc_attr( $background_color_meta ) : '#FFFFFF';

				$custom_css .= '.boomerang_status-' . $term->slug . ' .boomerang-meta .boomerang-status{color:' . $color . ';border-color:' . $color . ';background-color:' . $background_color . ';}';
				$custom_css .= '.boomerang-related-idea.boomerang_status-' . $term->slug . ' .boomerang-meta .boomerang-status{color:' . $color . ';border-color:' . $color . ';background-color:' . $background_color . ';}';
				$custom_css .= '.boomerang-suggestion.boomerang_status-' . $term->slug . ' .boomerang-status{color:' . $color . ';border-color:' . $color . ';background-color:' . $background_color . ';}';
			}
		}

		if ( $options['archive_layout'] && 'grid' === $options['archive_layout'] ) {
			$custom_css .= '.boomerang-default #boomerang-full{width:' . esc_attr( boomerang_get_container_width() ) . ';}';
		}

		// If no primary color set for the board, look for one in the customizer.
		if ( empty( $meta['primary_color'] ) ) {
			if ( isset( $options['primary_color'] ) ) {
				$custom_css .= ':root {--boomerang-primary-color: ' . esc_attr( $options['primary_color'] ) . ';}';
			}
		}

		if ( ! empty( $meta['admin_color'] ) ) {
			$custom_css .= ':root {--boomerang-team-color: ' . esc_attr( $meta['admin_color'] ) . ';}';
		} else if ( isset( $options['private_note_color'] ) ) {
			$custom_css .= ':root {--boomerang-team-color: ' . esc_attr( $options['private_note_color'] ) . ';}';
		}
	}

	return $custom_css;
}

/** Labels ************************************************************************************************************/

/**
 * Get the form labels from a boards settings screen.
 *
 * @param $board
 *
 * @return mixed
 */
function boomerang_get_labels( $board ) {
	$board = get_post( $board );

	$meta = get_post_meta( $board->ID, 'boomerang_board_options', true );

	return array(
		'singular' =>$meta['label_singular'] ?? 'feature request',
		'plural' =>$meta['label_plural'] ?? 'feature requests',
		'title' => $meta['label_title'] ?? 'Title',
		'content' => $meta['label_content'] ?? 'Content',
		'tags' => $meta['label_tags'] ?? 'Tags',
		'submit' => $meta['label_submit'] ?? 'Submit',
		'already_voted' => $meta['message_already_voted'] ?? 'You have already voted',
		'message_vote_recorded' => $meta['message_vote_recorded'] ?? 'Thank you for your vote',
	);
}

/**
 * Gets the singular form of a board's name for a Boomerang.
 *
 * @param $board
 *
 * @return mixed
 */
function get_singular( $board ) {
	if ( boomerang_get_labels( $board )['singular'] ) {
		return boomerang_get_labels( $board )['singular'];
	} else if ( get_singular_global() ) {
		return get_singular_global();
	} else {
		return 'boomerang';
	}
}

/**
 * Gets the plural form of a board's name for a Boomerang.
 *
 * @param $board
 *
 * @return mixed
 */
function get_plural( $board ) {
	if ( boomerang_get_labels( $board )['plural'] ) {
		return boomerang_get_labels( $board )['plural'];
	} else if ( get_plural_global() ) {
		return get_plural_global();
	} else {
		return 'boomerangs';
	}
}

/** Boomerang Form ****************************************************************************************************/

/**
 * Checks to see if the current user can submit Boomerangs via the Boomerang form.
 *
 * @param $board
 *
 * @return mixed
 */
function boomerang_user_can_submit( $board, $user_id ) {
	if ( ! is_user_logged_in() ) {
		$result = array(
			'message' => esc_html__( 'You must be logged in to post', 'boomerang' )
		);
	} else {
		$result = true;
	}

	/**
	 * Filter the result.
	 *
	 * @param true|array $result  The result to pass and filter
	 * @param string     $board   The ID of the current board
	 * @param int        $user_id The user ID of the current user trying to post a Boomerang
	 */
	$result = apply_filters( 'boomerang_user_can_submit', $result, $board, $user_id );

	return $result;
}

/**
 * Checks to see if a honeypot is enabled in our form.
 *
 * @param $board
 *
 * @return false|mixed
 */
function boomerang_board_honeypot_enabled( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_honeypot'] ?? false;
}


/** Notifications *****************************************************************************************************/

/**
 * Checks if notifications should be sent when Boomerangs are created.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_send_email_new_boomerang( $post = false ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['send_email_new_boomerang'] ?? false;
}

/**
 * Returns the emails set for notifications of new Boomerangs.
 *
 * @param $post
 *
 * @return mixed
 */
function boomerang_board_new_boomerang_email_addresses( $post = false ) {
	$post = boomerang_get_post( $post );
	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['admin_email'] ?? false;
}

/**
 * Are new Boomerang emails enabled?
 *
 * @param $post
 *
 * @return bool
 */
function send_new_boomerang_email_enabled( $post = false ) {

	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	if ( $meta['notifications']['send_new_boomerang_email']['enabled'] ) {
		return true;
	}

	return false;
}

// /**
//  * Are new Boomerang emails enabled?
//  *
//  * @param $post
//  *
//  * @return bool
//  */
// function send_new_boomerang_email_data( $post = false ) {
//
// 	$post = boomerang_get_post( $post );
//
// 	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );
//
// 	if ( ! $meta['notifications']['send_email_new_boomerang'] ) {
// 		return false;
// 	}
//
// 	return false;
// }

/** Voting **/

/**
 * Checks to see if the current user can vote on Boomerangs.
 *
 * @param $board
 *
 * @return mixed
 */
function boomerang_user_can_vote( $board, $user_id ) {
	if ( ! is_user_logged_in() ) {
		$result = array(
			'message' => esc_html__( 'You must be logged in to vote', 'boomerang' )
		);
	} else {
		$result = true;
	}

	/**
	 * Filter the result.
	 *
	 * @param true|array $result  The result to pass and filter
	 * @param string     $board   The ID of the current board
	 * @param int        $user_id The user ID of the current user trying to vote
	 */
	$result = apply_filters( 'boomerang_user_can_vote', $result, $board, $user_id );

	return $result;
}
