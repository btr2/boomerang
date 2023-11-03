<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area boomerang-container">
		<main id="main" class="site-main" role="main">
			<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

			<?php
			// Start the loop.
			while ( have_posts() ) :
				the_post();
				?>

				<header class="entry-header">
					<?php
					if ( boomerang_board_title_enabled() ) {
						the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
					}

					if ( boomerang_board_image_enabled() ) {
						boomerang_thumbnail();                  }
					?>
					<div class="boomerang-meta">
						<?php if ( boomerang_board_votes_enabled() ) : ?>
						<div class="votes-container"
							 data-id="<?php echo esc_attr( get_the_ID() ); ?>"
							 data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_process_vote' ) ); ?>">
							<?php echo boomerang_get_votes_html(); ?>
						</div>
						<?php endif; ?>
						<div class="non-voting-meta">
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
									<?php echo boomerang_get_comments_count_html(); ?>
								</div>
							<?php endif; ?>
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

						</div>
						<?php if ( current_user_can( 'manage_options' ) ) : ?>
							<div class="boomerang-admin-toggle">
								<?php if ( ! boomerang_google_fonts_disabled() ) : ?>
									<span class="boomerang-admin-toggle-button material-symbols-outlined">more_horiz</span>
								<?php else: ?>
									<span class="boomerang-admin-toggle-button">&#x2630;</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php echo boomerang_get_admin_area_html(); ?>
				</header><!-- .entry-header -->

				<div class="entry-content">

					<?php the_content(); ?>

				</div><!-- .entry-content -->

				<footer class="entry-footer">
					<?php if ( boomerang_board_comments_enabled() ) : ?>

					<div class="boomerang-comments">
						<?php
						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
						?>
					</div>

					<?php endif; ?>
				</footer><!-- .entry-footer -->
			</article><!-- .post -->

				<?php
			endwhile;
			?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
