<?php
/**
 * Templates for rendering content into our frontend offer.
 */
namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Boomerangs ********************************************************************************************************/

/**
 * Get Boomerangs in HTML.
 *
 * @return false|string
 */
function boomerang_get_boomerangs( $board = false, $args = false, $base = false ) {
	$defaults = array(
		'post_type'      => 'boomerang',
		'post_status'    => boomerang_can_manage() ? array( 'publish', 'pending', 'draft' ) : 'publish',
		'post_parent'    => $board ?? '',
		'posts_per_page' => 10,
		'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
	);

	$args = wp_parse_args( $args, $defaults );

	$the_query = new \WP_Query( $args );

	ob_start();

	if ( $the_query->have_posts() ) :

		while ( $the_query->have_posts() ) :
			$the_query->the_post();

			global $post;
			?>
			<article <?php post_class( 'boomerang' ); ?> id="post-<?php the_ID(); ?>">
				<?php do_action( 'boomerang_archive_boomerang_start', $post ); ?>
				<div class="boomerang-inner">
				<div class="boomerang-left">
					<?php if ( boomerang_board_votes_enabled() ) : ?>
					<div class="votes-container-outer">
						<?php echo boomerang_get_votes_html(); ?>
					</div>
					<?php endif; ?>
				</div>
				<div class="boomerang-right">
					<?php do_action( 'boomerang_above_title' ); ?>
					<div class="boomerang-messages-container"></div>
					<header class="entry-header">
						<?php
						the_title(
							'<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">',
							'</a></h2>'
						);
						?>
					</header><!-- .entry-header -->

					<div class="entry-content">

						<?php the_excerpt(); ?>

						<div class="boomerang-meta">
							<div class="boomerang-meta-left">
								<?php if ( boomerang_board_author_enabled() ) : ?>
									<div class="boomerang-posted-by"><?php boomerang_posted_by(); ?>
										<span>&#x2022;</span></div>
								<?php endif; ?>
								<?php if ( boomerang_board_date_enabled() ) : ?>
									<div class="boomerang-posted-on"><?php boomerang_posted_on(); ?></div>
								<?php endif; ?>
							</div>
							<div class="boomerang-meta-right">
								<?php if ( boomerang_board_statuses_enabled() && boomerang_has_status() ) : ?>
									<div class="boomerang-status"><?php boomerang_the_status(); ?></div>
								<?php endif; ?>
								<?php if ( boomerang_board_comments_enabled() ) : ?>
									<div class="boomerang-comment-count">
										<?php boomerang_get_comments_count_html(); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>

					</div><!-- .entry-content -->

					<footer class="entry-footer">
						<?php
						echo wp_kses(
							boomerang_get_tag_list(),
							array(
								'span' => array(
									'rel'     => array(),
									'class'   => array(),
									'data-id' => array(),
								),
								'div'  => array(
									'class'      => array(),
									'id'         => array(),
									'data-nonce' => array(),
								),
							)
						);
						?>
					</footer><!-- .entry-footer -->
				</div>
				</div>
				<?php do_action( 'boomerang_archive_boomerang_end', $post ); ?>
			</article><!-- .post -->


		<?php endwhile; ?>

		<?php
		$big = 999999999; // need an unlikely integer

		// Fallback if there is not base set.
		$fallback_base = str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );

		echo wp_kses_post(
			paginate_links(
				array(
					'base'    => isset( $base ) ? trailingslashit( $base ) . '%_%' : $fallback_base,
					'format'  => '?paged=%#%',
					'current' => max( 1, get_query_var( 'paged' ) ),
					'total'   => $the_query->max_num_pages,
					'type'    => 'list',
				)
			)
		);
		?>

	<?php else : ?>
		<div><p>
		<?php
		print_r(
			esc_html( 'Sorry, no %s matched your criteria.' ),
			get_plural( $board )
		);
		?>
				</p></div>
		<?php
	endif;

	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * Get an HTML formatted filter section for Boomerang directories.
 *
 * @return false|string
 */
function boomerang_get_filters( $board ) {
	ob_start();
	?>

	<div id="boomerang-board-filters" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_filters' ) ); ?>">
		<fieldset>
			<label for="boomerang-order">
				<?php if ( boomerang_google_fonts_disabled() ) : ?>
					<span><?php esc_html_e( 'Sort by', 'boomerang' ); ?>:</span>
				<?php else : ?>
					<span class="material-symbols-outlined">sort</span>
				<?php endif; ?>
			</label>
			<select id="boomerang-order" name="boomerang_order">
				<?php
				$ordering_values = boomerang_board_get_ordering_values( $board );

				foreach ( $ordering_values as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
				}
				?>
			</select>
		</fieldset>
		<fieldset>
			<label for="boomerang-status">
				<?php if ( boomerang_google_fonts_disabled() ) : ?>
					<span><?php esc_html_e( 'Status', 'boomerang' ); ?>:</span>
				<?php else : ?>
					<span class="material-symbols-outlined">filter_alt</span>
				<?php endif; ?>
			</label>
			<?php
			$args = array(
				'taxonomy'         => 'boomerang_status',
				'id'               => 'boomerang-status',
				'name'             => 'boomerang_status',
				'orderby'          => 'name',
				'show_option_none' => 'All Statuses',
			);

			wp_dropdown_categories( $args );
			?>
		</fieldset>
		<fieldset>
			<label for="boomerang-tags">
				<?php if ( boomerang_google_fonts_disabled() ) : ?>
					<span><?php esc_html_e( 'tags', 'boomerang' ); ?>:</span>
				<?php else : ?>
					<span class="material-symbols-outlined">sell</span>
				<?php endif; ?>
			</label>
			<?php
			$args = array(
				'taxonomy'         => 'boomerang_tag',
				'id'               => 'boomerang-tags',
				'name'             => 'boomerang_tags',
				'orderby'          => 'name',
				'show_option_none' => 'All Tags',
			);

			wp_dropdown_categories( $args );
			?>
		</fieldset>
		<fieldset>
			<label for="boomerang-search"></label>
			<input style="background-image: url('<?php echo esc_url( BOOMERANG_URL . 'assets/images/search.svg' ); ?>')" id="boomerang-search" name="boomerang_search" type="text" placeholder="<?php esc_attr_e( 'Search', 'boomerang' ); ?>">
		</fieldset>
	</div>

	<?php
	return ob_get_clean();
}

/** Tags **************************************************************************************************************/

/**
 * Checks if a boomerang has tags, and return an array if so.
 *
 * @param $post
 *
 * @return array|false|WP_Error|WP_Term[]
 */
function boomerang_get_tags( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	if ( boomerang_has_tags( $post ) ) {
		return get_the_terms( $post, 'boomerang_tag' );
	}

	return array();
}

/**
 * Gets a formatted string of boomerang tags.
 *
 * @param $post
 *
 * @return false|string|WP_Error|WP_Term[]
 */
function boomerang_get_tag_list( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	if ( ! boomerang_has_tags( $post ) ) {
		return;
	}

	$terms = get_the_terms( $post->ID, 'boomerang_tag' );

	if ( is_wp_error( $terms ) ) {
		return $terms;
	}

	$links = array();

	foreach ( $terms as $term ) {
		$links[] = '<span class="boomerang-tag" rel="tag" data-id="' . esc_attr( $term->term_id ) . '">#' . esc_html( $term->name ) . '</span>';
	}

	$nonce = wp_create_nonce( 'boomerang_select_tag' );

	return '<div class="boomerang-tags" data-nonce="' . esc_attr( $nonce ) . '" class="post-taxonomies">' . implode( $links ) . '</div>';
}

/**
 * Checks if a given boomerang has tags.
 *
 * @param $post
 *
 * @return bool
 */
function boomerang_has_tags( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	if ( ! boomerang_board_tags_enabled( $post->post_parent ) ) {
		return false;
	}

	if ( has_term( '', 'boomerang_tag', $post ) ) {
		return true;
	}

	return false;
}

/** Statuses **********************************************************************************************************/

/**
 * Prints the Boomerang's status.
 *
 * @return void
 */
function boomerang_the_status() {
	echo esc_attr( boomerang_get_status( get_post() ) );
}

/**
 * Get a given Boomerang's status.
 *
 * @param $post
 *
 * @return string|void
 */
function boomerang_get_status( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	if ( $post ) {
		$terms = get_the_terms( $post->ID, 'boomerang_status' );

		if ( $terms ) {
			return $terms[0]->name;
		}
	}
}

/**
 * Checks to see if a given Boomerang has a status.
 *
 * @param $post
 *
 * @return bool
 */
function boomerang_has_status( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	$terms = get_the_terms( $post->ID, 'boomerang_status' );

	if ( $terms ) {
		return true;
	}

	return false;
}

/**
 * Gets the unique color attached to a status, or black as a default.
 *
 * @param $post
 *
 * @return mixed|void
 */
function boomerang_get_status_color( $post ) {
	if ( ! $post ) {
		$post = get_post();
	}

	$terms = get_the_terms( $post->ID, 'boomerang_status' );

	if ( $terms ) {
		$color = get_term_meta( $terms[0]->term_id, 'color', true );

		if ( $color ) {
			return $color;
		} else {
			return '#000000';
		}
	}
}

/** Meta **************************************************************************************************************/

/**
 * Formatted date and time for a Boomerang's publication.
 *
 * @return void
 */
function boomerang_posted_on( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	if ( ! boomerang_board_date_enabled( $post->post_parent ) ) {
		return;
	}

	$datetime = esc_attr( get_the_date( DATE_W3C ) );

	if ( boomerang_board_friendly_date_enabled( $post->post_parent ) ) {
		$formatted_time = sprintf(
		/* translators: time */
			__( '%s ago', 'boomerang' ),
			human_time_diff( get_the_time( 'U', $post ), strtotime( wp_date( 'Y-m-d H:i:s' ) ) )
		);
	} else {
		$formatted_time = sprintf(
		/* translators: %s: Publish date. */
			__( 'Published %s', 'boomerang' ),
			get_the_date()
		);
	}

	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	echo '<span class="posted-on"><time class="entry-date published updated" datetime="' . esc_attr( $datetime ) . '">' . esc_html( $formatted_time ) . '</time></span>';
}

/**
 * Formatted author HTML for a Boomerang.
 *
 * @return void
 */
function boomerang_posted_by( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	if ( ! boomerang_board_author_enabled( $post->post_parent ) ) {
		return;
	}

	if ( boomerang_board_author_avatar_enabled() ) {
		$user_email = get_the_author_meta( 'user_email' );
		echo get_avatar( $user_email, '36' );
	}

	$posted_by_string = '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" rel="author">' . esc_html( get_the_author() ) . '</a>';

	echo wp_kses_post( apply_filters( 'boomerang_posted_by_string', $posted_by_string, $post ) );
}

/**
 * Gets an HTML formatted count of comments with icons or text depending on whether Google Fonts are enabled.
 *
 * @param $post
 *
 * @return void
 */
function boomerang_get_comments_count_html( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	$count = apply_filters( 'boomerang_comments_count', get_comments_number( $post ), $post );

	if ( boomerang_google_fonts_disabled() ) {
		printf(
		/* translators: %s: Publish date. */
			esc_html__( 'Comments: %d', 'boomerang' ),
			esc_attr( $count )
		);
	} else {
		echo '<span class="material-symbols-outlined">chat_bubble</span>' . esc_attr( $count );
	}
}

/** Attachments and Featured Images ***********************************************************************************/

/**
 * Retrieve the Boomerang's featured image.
 */
function boomerang_thumbnail() {
	if ( ! boomerang_board_image_enabled() ) {
		return;
	}
	?>

	<?php if ( has_post_thumbnail() ) : ?>

		<?php if ( is_singular() ) : ?>

		<figure class="post-thumbnail">
			<?php
			// Lazy-loading attributes should be skipped for thumbnails since they are immediately in the viewport.
			the_post_thumbnail( 'medium_large', array( 'loading' => false ) );
			?>
			<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
				<figcaption class="wp-caption-text"><?php echo wp_kses_post( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
			<?php endif; ?>
		</figure><!-- .post-thumbnail -->

	<?php else : ?>

		<figure class="post-thumbnail">
			<a class="post-thumbnail-inner alignwide" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php the_post_thumbnail( 'post-thumbnail' ); ?>
			</a>
			<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
				<figcaption class="wp-caption-text"><?php echo wp_kses_post( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
			<?php endif; ?>
		</figure><!-- .post-thumbnail -->

	<?php endif; ?>

	<?php endif; ?>
	<?php
}

/** Voting ************************************************************************************************************/

/**
 * Gets an HTML formatted container showing the current votes, and voting buttons.
 *
 * @return void
 */
function boomerang_get_votes_html( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	if ( ! boomerang_board_votes_enabled( $post->post_parent ) ) {
		return;
	}

	$html = '<div class="votes-container" data-id="' . esc_attr( $post->ID ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'boomerang_process_vote' ) ) . '">';

	$has_voted = boomerang_user_has_voted( get_current_user_id(), $post );

	$count = '<span class="boomerang-vote-count">' . boomerang_get_votes( $post ) . '</span>';

	$can_vote = boomerang_user_can_vote( $post->post_parent, get_current_user_id() );

	if ( true === $can_vote ) {
		if ( boomerang_google_fonts_disabled() ) {
			$up   = '<span class="vote-up status-' . $has_voted . ' boomerang-vote">&#x21e7;</span>';
			$down = '<span class="vote-down status-' . $has_voted . ' boomerang-vote">&#x21e9;</span>';
		} else {
			$up   = '<span class="material-symbols-outlined vote-up status-' . $has_voted . ' boomerang-vote">arrow_circle_up</span>';
			$down = '<span class="material-symbols-outlined vote-down status-' . $has_voted . ' boomerang-vote">arrow_circle_down</span>';
		}

		$html .= $up;
		$html .= $count;
		$html .= $down;
	} else {
		$html .= $count;
		$html .= '<span class="logged-out-text">' . esc_html__( 'votes', 'boomerang' ) . '</span>';
	}

	$html .= '</div>';

	return $html;
}

/** Admin Tools *******************************************************************************************************/

/**
 * Gets an HTML formatted container showing frontend admin controls.
 *
 * @return void
 */
function boomerang_get_admin_area_html( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	if ( ! boomerang_can_manage() ) {
		return;
	}

	$args = array(
		'show_option_none' => __( 'Select Status', 'boomerang' ),
		'hide_empty'       => 0,
		'orderby'          => 'name',
		'taxonomy'         => 'boomerang_status',
		'id'               => 'boomerang_status',
	);

	ob_start();
	?>

	<div class="boomerang-admin-area" data-id="<?php echo esc_attr( $post->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_admin_area' ) ); ?>">
		<h2 class="boomerang-admin-area-heading">
			<?php if ( boomerang_google_fonts_disabled() ) : ?>
				<span>&#x2630;</span>
			<?php else : ?>
				<span class="material-symbols-outlined">tune</span>
			<?php endif; ?>
			<?php esc_html_e( 'Admin', 'boomerang' ); ?>
		</h2>
		<div class="boomerang-admin-area-inner">
			<?php do_action( 'boomerang_admin_area_start', $post ); ?>
			<div class="boomerang-controls-container">
				<div class="boomerang-controls">
					<?php do_action( 'boomerang_admin_controls_start', $post ); ?>
					<?php if ( boomerang_board_statuses_enabled() ) : ?>
					<div class="boomerang-status boomerang-control">
						<div class="control-header">
							<?php if ( ! boomerang_google_fonts_disabled() ) : ?>
								<span class="material-symbols-outlined icon">target</span>
							<?php endif; ?>
							<h3><?php esc_html_e( 'Status', 'boomerang' ); ?></h3>
							<?php if ( boomerang_google_fonts_disabled() ) : ?>
								<span class="chevron">&#x276F;</span>
							<?php else : ?>
								<span class="material-symbols-outlined chevron">chevron_right</span>
							<?php endif; ?>
						</div>
						<div class="control-content">
							<fieldset>
								<?php wp_dropdown_categories( $args ); ?>
								<div class=control-content-inline-button icon-only" id="boomerang-status-submit">
									<?php if ( boomerang_google_fonts_disabled() ) : ?>
										<span><?php esc_attr_e( 'Submit', 'boomerang' ); ?></span>
									<?php else : ?>
										<span class="material-symbols-outlined">arrow_forward</span>
									<?php endif; ?>
								</div>
							</fieldset>
						</div>
					</div>
					<?php else : ?>
					<p class="boomerang-control-disabled"><?php esc_html_e( 'To change statuses, enable them under Board Settings', 'boomerang' ); ?></p>
	<?php endif; ?>

					<?php do_action( 'boomerang_admin_controls_end', $post ); ?>
				</div>
			</div>
			<div class="boomerang-actions-container">
				<?php do_action( 'boomerang_actions_container_start', $post ); ?>
				<h3 class="boomerang-actions-heading"><?php esc_html_e( 'Actions', 'boomerang' ); ?></h3>
				<div class="boomerang-actions">
					<?php do_action( 'boomerang_admin_actions_start', $post ); ?>
					<a class="boomerang-action" href="<?php echo get_edit_post_link(); ?>">
						<?php if ( boomerang_google_fonts_disabled() ) : ?>
							<span><?php esc_html_e( 'Edit', 'boomerang' ); ?></span>
						<?php else : ?>
							<span class="material-symbols-outlined">edit</span>
						<?php endif; ?>
					</a>
					<a class="boomerang-action" href="<?php echo esc_url( add_query_arg( 'frontend', 'true', get_delete_post_link() ) ); ?>">
						<?php if ( boomerang_google_fonts_disabled() ) : ?>
							<span><?php esc_html_e( 'Delete', 'boomerang' ); ?></span>
						<?php else : ?>
							<span class="material-symbols-outlined">delete</span>
						<?php endif; ?>
					</a>
					<?php do_action( 'boomerang_admin_actions_end', $post ); ?>
				</div>
				<?php do_action( 'boomerang_actions_container_end', $post ); ?>
			</div>
			<?php do_action( 'boomerang_admin_area_end', $post ); ?>
		</div>

	</div>


	<?php
	return ob_get_clean();
}

/** Comments **********************************************************************************************************/

/**
 * Custom callback template for displaying comments.
 *
 * @return void
 */
function boomerang_comment_template( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	if ( ! empty( $comment->user_id ) ) {
		$user = get_userdata( $comment->user_id );

		if ( get_comment_meta( $comment->comment_ID, 'system_note', true ) ) {
			$author = $user->display_name . ' (' . esc_html__( 'system generated', 'boomerang' ) . ')';
		} else {
			$author = $user->display_name;
		}
		$url = get_author_posts_url( $comment->user_id );
	}

	$classes = apply_filters( 'boomerang_comment_classes', array(), $comment );
	?>

<li id="li-comment-<?php comment_ID(); ?>" <?php comment_class( $classes ); ?>>
	<div class="comment-container">
		<div class="comment-author-avatar vcard">
			<a href="<?php echo esc_url_raw( $url ); ?>">
				<?php
				if ( 0 !== $args['avatar_size'] ) {
					echo get_avatar( $comment, $args['avatar_size'] );
				}
				?>
			</a>
		</div><!-- .comment-author-avatar -->

		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<?php do_action( 'boomerang_comment_above_author_name', $comment ); ?>
			<div class="comment-author vcard">
				<a href="<?php echo esc_url_raw( $url ); ?>"><?php echo esc_html( $author ); ?></a>
			</div><!-- .comment-author -->

			<div class="comment-content">
				<?php comment_text(); ?>
			</div><!-- .comment-content -->

			<footer class="comment-meta">
				<div class="comment-metadata">
					<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
						<time datetime="<?php comment_time( 'c' ); ?>">
							<?php

							if ( boomerang_board_friendly_date_enabled() ) {
								$formatted_time = sprintf(
								/* translators: time */
									__( '%s ago', 'boomerang' ),
									human_time_diff( get_comment_time( 'U' ), strtotime( wp_date( 'Y-m-d H:i:s' ) ) )
								);
							} else {
								$formatted_time = sprintf(
								/* translators: %s: Publish date. */
									__( 'Published %s', 'boomerang' ),
									get_comment_date( '', $comment )
								);
							}

							echo esc_html( $formatted_time );
							?>
						</time>
					</a>
					<?php edit_comment_link( __( 'Edit' ), '<span class="edit-link">', '</span>' ); ?>
					<?php
					if ( get_comment_type() == 'comment' ) {
						comment_reply_link(
							array_merge(
								$args,
								array(
									'add_below' => 'div-comment',
									'depth'     => $depth,
									'max_depth' => $args['max_depth'],
									'before'    => '<span class="reply">',
									'after'     => '</span>',
								)
							)
						);
					}
					?>
				</div><!-- .comment-metadata -->

				<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.' ); ?></p>
				<?php endif; ?>
			</footer><!-- .comment-meta -->
		</article><!-- .comment-body -->

	</div>

	<?php
}

/** Labels ************************************************************************************************************/

