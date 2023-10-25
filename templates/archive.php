<div class="boomerang-directory <?php echo esc_attr( boomerang_get_board_slug( $a['board'] ) ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>">

	<?php

	$args      = array(
		'post_type'   => 'boomerang',
		'post_status' => boomerang_show_drafts(),
		'post_parent' => $a['board'] ?? '',
	);
	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) :
		?>
		<?php
		while ( $the_query->have_posts() ) :
			$the_query->the_post();
			?>
			<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
				<header>
					<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
					<div class="boomerang-posted-on"><?php boomerang_posted_on(); ?></div>
					<div class="boomerang-posted-by"><?php boomerang_posted_by(); ?></div>
					<div class="boomerang-status"><?php boomerang_the_status(); ?></div>
					<div class="boomerang-comment-count"><?php echo esc_attr( get_comments_number() ); ?></div>
				</header><!-- .entry-header -->

				<div class="entry-content">

					<?php the_excerpt(); ?>

				</div><!-- .entry-content -->

				<footer class="entry-footer">
					<div id="boomerang-tags" class="post-taxonomies">
						<?php echo boomerang_get_tag_list(); // phpcs:ignore ?>
					</div>
				</footer><!-- .entry-footer -->

			</article><!-- .post -->
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
	<?php endif; ?>

</div>
