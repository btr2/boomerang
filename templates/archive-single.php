<?php
/**
 * The template for displaying a single Boomerang in an archive.
 */

use function Bouncingsprout_Boomerang\boomerang_board_author_enabled;
use function Bouncingsprout_Boomerang\boomerang_board_comments_enabled;
use function Bouncingsprout_Boomerang\boomerang_board_date_enabled;
use function Bouncingsprout_Boomerang\boomerang_board_statuses_enabled;
use function Bouncingsprout_Boomerang\boomerang_board_votes_enabled;
use function Bouncingsprout_Boomerang\boomerang_get_comments_count_html;
use function Bouncingsprout_Boomerang\boomerang_get_tag_list;
use function Bouncingsprout_Boomerang\boomerang_get_votes_html;
use function Bouncingsprout_Boomerang\boomerang_has_status;
use function Bouncingsprout_Boomerang\boomerang_posted_by;
use function Bouncingsprout_Boomerang\boomerang_posted_on;
use function Bouncingsprout_Boomerang\boomerang_the_status;

?>
<article <?php post_class( 'boomerang' ); ?> id="post-<?php the_ID(); ?>">
	<?php do_action( 'boomerang_archive_boomerang_start', get_post() ); ?>
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
	<?php do_action( 'boomerang_archive_boomerang_end', get_post() ); ?>
</article><!-- .post -->
