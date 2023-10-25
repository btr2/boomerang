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
			while ( have_posts() ) : the_post(); ?>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">','</h1>' ); ?>
					<?php boomerang_thumbnail(); ?>
					<div class="boomerang-meta">
						<div class="boomerang-posted-on"><?php boomerang_posted_on(); ?></div>
						<div class="boomerang-posted-by"><?php boomerang_posted_by(); ?></div>
						<div class="boomerang-status"><?php boomerang_the_status(); ?></div>
						<div class="boomerang-comment-count"><span class="material-symbols-outlined">chat_bubble</span><?php echo esc_attr( get_comments_number() ); ?></div>
					</div>

				</header><!-- .entry-header -->

				<div class="entry-content">

					<?php the_content(); ?>

				</div><!-- .entry-content -->

				<footer class="entry-footer">
					<div id="boomerang-tags" class="post-taxonomies">
						<?php echo boomerang_get_tag_list(); // phpcs:ignore ?>
					</div>
				</footer><!-- .entry-footer -->
			</article><!-- .post -->

			<?php endwhile;
			?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>