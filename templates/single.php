<?php
/**
 * The template for displaying all single Boomerangs
 */
namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<div id="primary" class="content-area boomerang-container">
		<main id="main" class="site-main" role="main">

			<?php
			// Start the loop.
			while ( have_posts() ) :
				the_post();
				?>

				<article <?php post_class( 'boomerang' ); ?> id="post-<?php the_ID(); ?>">
					<div class="boomerang-single-aside">
						<?php do_action( 'boomerang_single_boomerang_aside_start', $post ); ?>
						<?php echo boomerang_get_admin_area_html(); ?>
						<?php do_action( 'boomerang_single_boomerang_aside_end', $post ); ?>
					</div>
					<div class="boomerang-single-content">
						<?php do_action( 'boomerang_single_boomerang_start', $post ); ?>
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
								the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
								?>
							</header><!-- .entry-header -->

							<div class="entry-content">

								<div class="entry-content-inner">

									<?php the_content(); ?>

								</div><!-- .entry-content-inner -->
								<?php boomerang_thumbnail(); ?>
							</div><!-- .entry-content -->

							<?php do_action( 'boomerang_above_meta', $post ); ?>

							<div class="boomerang-meta">
								<div class="boomerang-meta-left">
									<?php if ( boomerang_board_author_enabled() ) : ?>
										<div class="boomerang-posted-by"><?php boomerang_posted_by(); ?><span>&#x2022;</span></div>
									<?php endif; ?>
									<?php if ( boomerang_board_date_enabled() ) : ?>
										<div class="boomerang-posted-on"><?php boomerang_posted_on(); ?></div>
									<?php endif; ?>
									<?php do_action( 'boomerang_after_meta_left', $post ); ?>
								</div>
								<div class="boomerang-meta-right">
									<?php if ( boomerang_board_statuses_enabled() ) : ?>
										<div class="boomerang-status" <?php echo boomerang_has_status() ? '' : 'style="display: none"'  ?>><?php boomerang_the_status(); ?></div>
									<?php endif; ?>
									<?php if ( boomerang_board_comments_enabled() ) : ?>
										<div class="boomerang-comment-count">
											<?php boomerang_get_comments_count_html(); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<?php do_action( 'boomerang_single_boomerang_before_footer', $post ); ?>

							<footer class="entry-footer">
								<?php do_action( 'boomerang_single_boomerang_footer_start', $post ); ?>
								<div class="boomerang-tags-container">
								<?php
								echo wp_kses(
									boomerang_get_tag_list(),
									array(
										'span' => array(
											'rel'   => array(),
											'class' => array(),
										),
										'div'  => array(
											'class'      => array(),
											'id'         => array(),
											'data-nonce' => array(),
										),
									)
								);
								?>
								</div>
								<?php do_action( 'boomerang_single_boomerang_footer_before_comments', $post ); ?>

								<?php

								if ( boomerang_board_comments_enabled() && ( comments_open() || get_comments_number() ) ) :
									comments_template();
								endif;
								?>

								<?php do_action( 'boomerang_single_boomerang_footer_end', $post ); ?>
							</footer><!-- .entry-footer -->
						</div>
						<?php do_action( 'boomerang_single_boomerang_end', $post ); ?>
					</div>

				</article><!-- .post -->
				<?php
			endwhile;
			?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
