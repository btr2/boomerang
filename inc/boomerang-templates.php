<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Boomerangs ********************************************************************************************************/

/**
 * Get Boomerangs in HTML.
 *
 * @return false|string
 */
function boomerang_get_boomerangs( $board, $args = false ) {
	$defaults = array(
		'post_type'      => 'boomerang',
		'post_status'    => current_user_can( 'manage_options' ) ? array( 'publish', 'pending', 'draft' ) : 'publish',
		'post_parent'    => $board ?? '',
		'posts_per_page' => 10,
		'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
	);

	$args = wp_parse_args( $args, $defaults );

	$the_query = new WP_Query( $args );

	ob_start();

	if ( $the_query->have_posts() ) :

		while ( $the_query->have_posts() ) :
			$the_query->the_post();
			?>
			<article <?php post_class( 'boomerang' ); ?> id="post-<?php the_ID(); ?>">
				<header class="entry-header">
					<?php
					if ( boomerang_board_title_enabled() ) {
						the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
					}
					?>
					<div class="boomerang-meta">
						<?php if ( boomerang_board_votes_enabled() ) : ?>
							<div class="votes-container" data-id="<?php echo esc_attr( get_the_ID() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_process_vote' ) ); ?>">
								<?php boomerang_get_votes_html(); ?>
							</div>
						<?php endif; ?>
						<?php if ( boomerang_board_date_enabled() ) : ?>
							<div class="boomerang-posted-on"><?php boomerang_posted_on(); ?></div>
						<?php endif; ?>
						<?php if ( boomerang_board_author_enabled() ) : ?>
							<div class="boomerang-posted-by"><?php boomerang_posted_by(); ?></div>
						<?php endif; ?>
						<?php if ( boomerang_board_statuses_enabled() && boomerang_has_status() ) : ?>
							<div class="boomerang-status"><?php boomerang_the_status(); ?></div>
						<?php endif; ?>
						<?php if ( boomerang_board_comments_enabled() ) : ?>
							<div class="boomerang-comment-count">
								<?php boomerang_get_comments_count_html(); ?>
							</div>
						<?php endif; ?>
						<?php if ( current_user_can( 'manage_options' ) ) : ?>
							<div class="boomerang-admin-toggle">
								<?php if ( ! boomerang_google_fonts_disabled() ) : ?>
									<span class="boomerang-admin-toggle-button material-symbols-outlined">more_horiz</span>
								<?php else : ?>
									<span class="boomerang-admin-toggle-button">&#x2630;</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php echo boomerang_get_admin_area_html(); ?>
				</header><!-- .entry-header -->

				<div class="entry-content">

					<?php the_excerpt(); ?>

				</div><!-- .entry-content -->

				<footer class="entry-footer">
					<?php
					echo wp_kses(
						boomerang_get_tag_list(),
						array(
							'span' => array(
								'rel'   => array(),
								'class' => array(),
							),
							'div'  => array(
								'class' => array(),
								'id'    => array(),
							),
						)
					);
					?>
				</footer><!-- .entry-footer -->

			</article><!-- .post -->



		<?php endwhile; ?>

		<?php
		$big = 999999999; // need an unlikely integer
		echo wp_kses_post(
			paginate_links(
				array(
					'base'    => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
					'format'  => '?paged=%#%',
					'current' => max( 1, get_query_var( 'paged' ) ),
					'total'   => $the_query->max_num_pages,
					'type'    => 'list',
				)
			)
		);
		?>

	<?php else : ?>
		<p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
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
function boomerang_get_filters() {
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
				<option value="popular"><?php esc_html_e( 'Popular', 'boomerang' ); ?></option>
				<option value="latest"><?php esc_html_e( 'Latest', 'boomerang' ); ?></option>
				<option value="mine"><?php esc_html_e( 'Created by me', 'boomerang' ); ?></option>
				<option value="voted"><?php esc_html_e( 'Voted on by me', 'boomerang' ); ?></option>
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
				'show_option_none' => 'All',
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
				'show_option_none' => 'All',
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
		$links[] = '<span rel="tag">' . $term->name . '</span>';
	}

	if ( boomerang_google_fonts_disabled() ) {
		$html = sprintf(
		/* translators: %s: Publish date. */
			esc_html__( 'Tags: %s', 'boomerang' ),
			implode( $links )
		);
	} else {
		$html = '<span class="material-symbols-outlined">sell</span>' . implode( $links );
	}

	return '<div id="boomerang-tags" class="post-taxonomies">' . $html . '</div>';
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

	$terms = get_the_terms( $post->ID, 'boomerang_status' );

	if ( $terms ) {
		return $terms[0]->name;
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

/** Meta **************************************************************************************************************/

/**
 * Formatted date and time for a Boomerang's publication.
 *
 * @return void
 */
function boomerang_posted_on() {
	$meta = get_post_meta( wp_get_post_parent_id(), 'board_meta', true );

	if ( ! $meta['show_date'] ) {
		return;
	}

	$datetime = esc_attr( get_the_date( DATE_W3C ) );

	if ( boomerang_board_friendly_date_enabled() ) {
		$formatted_time = sprintf(
		/* translators: time */
			__( '%s ago', 'boomerang' ),
			human_time_diff( get_the_time( 'U' ), strtotime( wp_date( 'Y-m-d H:i:s' ) ) )
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
function boomerang_posted_by() {
	$meta = get_post_meta( wp_get_post_parent_id(), 'board_meta', true );

	if ( ! $meta['show_author'] ) {
		return;
	}

	$user_email = get_the_author_meta( 'user_email' );

	echo get_avatar( $user_email, '24' );
	echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" rel="author">' . esc_html( get_the_author() ) . '</a>';

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

	$count = get_comments_number();

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

	<?php if ( is_singular() ) : ?>

		<figure class="post-thumbnail">
			<?php
			// Lazy-loading attributes should be skipped for thumbnails since they are immediately in the viewport.
			the_post_thumbnail( 'post-thumbnail', array( 'loading' => false ) );
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

	$html = '';

	$has_voted = boomerang_user_has_voted( get_current_user_id(), $post );

	$count = '<span class="boomerang-vote-count">' . boomerang_get_votes( $post ) . '</span>';

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

	echo wp_kses_post( $html );
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

	<div class="boomerang-admin-area" style="display: none;" data-id="<?php echo esc_attr( $post->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_admin_area' ) ); ?>">
		<?php do_action( 'boomerang_admin_area_start', $post ); ?>
		<?php
		if ( current_user_can( 'edit_posts' ) ) {
			edit_post_link( __( 'Edit', 'textdomain' ), '', '', null, 'btn button btn-edit-post-link' );
			echo '<a href="' . get_delete_post_link() . '" class="btn button">' . esc_html__( 'Delete', 'boomerang' ) . '</a>';
		}
		?>
		<?php wp_dropdown_categories( $args ); ?>
			<input name="change_status" id="boomerang-admin-area-submit" type="submit" value="<?php esc_attr_e( 'Submit', 'boomerang' ); ?>">
		<?php do_action( 'boomerang_admin_area_end', $post ); ?>
	</div>


	<?php
	return ob_get_clean();
}
