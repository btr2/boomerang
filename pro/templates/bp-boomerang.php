<?php
/**
 * The template for displaying Boomerangs on a members profile page.
 */

namespace Bouncingsprout_Boomerang;

?>

<?php do_action( 'boomerang_profile_page_start' ); ?>
<div class="bp-boomerang-container">
<div class="bp-document-listing">
	<h2 class="bb-title"><?php echo esc_html( ucwords( get_plural_global() ) ); ?></h2>
</div>
	<div class="document-data-table-head boomerang-data-table-head">
		<div class="data-head data-head-title " data-target="title">
			<span><?php esc_html_e( 'Title', 'boomerang' ); ?></span>
		</div>
		<div class="data-head data-head-modified " data-target="modified">
			<span><?php esc_html_e( 'Modified', 'boomerang' ); ?></span>
		</div>
		<div class="data-head data-head-status " data-target="status">
			<span><?php esc_html_e( 'Status', 'boomerang' ); ?></span>
		</div>
		<div class="data-head data-head-votes " data-target="votes">
			<span><?php esc_html_e( 'Votes', 'boomerang' ); ?></span>
		</div>
	</div>
	<?php

	$args = array(
		'post_type'      => 'boomerang',
		'post_status'    => boomerang_can_manage() ? array( 'publish', 'pending', 'draft' ) : 'publish',
		'post_parent'    => '',
		'posts_per_page' => 10,
		'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
	);

	// $args = wp_parse_args( $args, $defaults );

	$the_query = new \WP_Query( $args );

	if ( $the_query->have_posts() ) :

		while ( $the_query->have_posts() ) :
			$the_query->the_post();

			global $post;
			?>
			<article <?php post_class( 'boomerang' ); ?> id="post-<?php the_ID(); ?>">
				<?php do_action( 'boomerang_profile_page_boomerang_start', $post ); ?>
				<div class="boomerang-inner">
					<div class="boomerang-title">
						<?php
						the_title(
							'<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">',
							'</a></h2>'
						);
						?>
					</div>
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

						</header><!-- .entry-header -->

						<div class="entry-content">

							<?php the_excerpt(); ?>

							<div class="boomerang-meta alignwide">
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

		// Fallback if there is no base set.
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
					get_plural_global()
				);
				?>
			</p></div>
		<?php
	endif;



	?>



</div>
