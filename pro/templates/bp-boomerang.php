<?php
/**
 * The template for displaying Boomerangs on a members profile page.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php do_action( 'boomerang_profile_page_start' ); ?>
<div class="bp-boomerang-container">
	<h2 class="bb-title"><?php echo esc_html( ucwords( get_plural_global() ) ); ?></h2>
<div class="bp-boomerang-listing">
	<div class="boomerang-data-table-head">
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
		<div class="data-head data-head-comments " data-target="comments">
			<span><?php esc_html_e( 'Comments', 'boomerang' ); ?></span>
		</div>
	</div>
	<?php

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
	$args = array(
		'post_type'      => 'boomerang',
		'post_status'    => boomerang_can_manage() ? array( 'publish', 'pending', 'draft' ) : 'publish',
		'post_parent'    => '',
		'author__in'     => bp_displayed_user_id(),
		'posts_per_page' => 10,
		'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
	);

	// $args = wp_parse_args( $args, $defaults );

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
	$the_query = new \WP_Query( $args );

	if ( $the_query->have_posts() ) :
		?>

	<div class="boomerang-data-table">

		<?php
		while ( $the_query->have_posts() ) :
			$the_query->the_post();

			global $post;
			?>
			<article <?php post_class( 'boomerang' ); ?> id="post-<?php the_ID(); ?>">
				<?php do_action( 'boomerang_profile_page_boomerang_start', $post ); ?>
					<div class="data-table data-table-title">
						<?php
						the_title(
							'<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">',
							'</a></h2>'
					);
					?>
				</div>
				<div class="data-table data-table-modified">
					<?php echo esc_html( get_the_modified_date() ); ?>
				</div>
				<div class="data-table data-table-status">
					<div class="boomerang-status">
						<?php boomerang_the_status( $post ); ?>
					</div>
				</div>
				<div class="data-table data-table-votes">
					<?php echo esc_html( boomerang_get_votes( $post ) ); ?>
				</div>
				<div class="data-table data-table-comments">
					<?php echo esc_html( get_comments_number( $post ) ); ?>
				</div>
				<?php do_action( 'boomerang_archive_boomerang_end', $post ); ?>
			</article><!-- .post -->


		<?php endwhile; ?>

	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
	$big = 999999999; // need an unlikely integer

	// Fallback if there is no base set.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
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

	</div>

	<?php else : ?>
		<div class="boomerang-data-none"><p>
			<?php
			printf(
				esc_html( 'Sorry, no %s matched your criteria.' ),
				esc_html( get_plural_global() )
			);
			?>
			</p></div>
		<?php
	endif;



	?>

</div>

</div>
