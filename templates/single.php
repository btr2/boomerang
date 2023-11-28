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

				<?php echo boomerang_get_admin_area_html(); ?>

				<article <?php post_class( 'boomerang' ); ?> id="post-<?php the_ID(); ?>">
					<?php do_action( 'boomerang_single_boomerang_start', $post ); ?>
					<div class="boomerang-left">
						<?php if ( boomerang_board_votes_enabled() ) : ?>
							<div class="votes-container" data-id="<?php echo esc_attr( get_the_ID() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang_process_vote' ) ); ?>">
								<?php echo boomerang_get_votes_html(); ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="boomerang-right">
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

						<div class="boomerang-meta">
							<div class="boomerang-meta-left">
								<?php if ( boomerang_board_author_enabled() ) : ?>
									<div class="boomerang-posted-by"><?php boomerang_posted_by(); ?><span>&#x2022;</span></div>
								<?php endif; ?>
								<?php if ( boomerang_board_date_enabled() ) : ?>
									<div class="boomerang-posted-on"><?php boomerang_posted_on(); ?></div>
								<?php endif; ?>
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
										'class'      => array(),
										'id'         => array(),
										'data-nonce' => array(),
									),
								)
							);
							?>

							<?php do_action( 'boomerang_single_boomerang_after_footer_meta', $post ); ?>

							<?php

							if ( boomerang_board_comments_enabled() && ( comments_open() || get_comments_number() ) ) :
								comments_template();
							endif;
							?>
						</footer><!-- .entry-footer -->
					</div>
					<?php do_action( 'boomerang_single_boomerang_end', $post ); ?>
				</article><!-- .post -->
				<?php
			endwhile;
			?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
