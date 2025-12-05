<?php
/**
 * Related Boomerang Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks to see if related boomerangs are enabled.
 *
 * @param $post
 *
 * @return false|mixed
 */
function related_boomerangs_enabled( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_related_boomerangs'] ?? false;
}

/**
 * Gets the title for the related Boomerangs area.
 *
 * @param $post
 *
 * @return false|mixed
 */
function get_related_boomerangs_title( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	if ( empty($meta['related_boomerangs_label'])) {
		return 'Related Items';
	} else {
		return $meta['related_boomerangs_label'];
	}
}

/**
 * Displays related ideas on single Boomerangs.
 *
 * @param $post_data
 * @param $post_id
 *
 * @return void
 */
function display_related_ideas( $post ) {
	if ( ! related_boomerangs_enabled() ) {
		return;
	}

	$args = array(
		'post_type'      => 'boomerang',
		'post_parent'    => $post->post_parent ?? '',
		'posts_per_page' => 3,
		'post__not_in'   => array( $post->ID ),
		's'              => $post->post_title,
		'orderby'        => 'relevance',
		'order'          => 'DESC',
	);

	$the_query = new \WP_Query( $args );

	if ( $the_query->have_posts() ) {
		echo '<div class="boomerang-related-ideas">';
		echo '<h2>' . esc_html( get_related_boomerangs_title( $post->post_parent ) ) . '</h2>';
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			$terms = get_the_terms( get_the_ID(), 'boomerang_status' );

			if ( $terms ) {
			$status = 'boomerang_status-' . $terms[0]->slug;
		}
		?>

		<article id="post-<?php the_ID(); ?>" class="boomerang-related-idea <?php echo esc_attr( $status ?? '' ); ?>">

		<header class="entry-header">
				<a href="<?php the_permalink(); ?>"><h2 class="entry-title"><?php echo esc_html( get_the_title() ); ?></h2></a>
			</header>

			<div class="boomerang-meta">
				<div class="boomerang-meta-left">
					<?php
					if ( boomerang_board_author_enabled() ) {
						if ( boomerang_board_author_avatar_enabled() ) {
							$user_email = get_the_author_meta( 'user_email' );
							echo get_avatar( $user_email, '36' );
						}

						echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" rel="author"></a>';
					}
					?>
					<?php if ( boomerang_board_date_enabled() ) : ?>
						<div class="boomerang-posted-on"><?php boomerang_posted_on(); ?></div>
					<?php endif; ?>
				</div>
				<div class="boomerang-meta-right">
					<?php if ( boomerang_board_statuses_enabled() ) : ?>
						<div class="boomerang-status" <?php echo boomerang_has_status() ? '' : 'style="display: none"'; ?>><?php boomerang_the_status(); ?></div>
					<?php endif; ?>
					<?php if ( boomerang_board_comments_enabled() ) : ?>
						<div class="boomerang-comment-count">
							<?php boomerang_get_comments_count_html(); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			</article>

			<?php
		}
		echo '</div>';
	}

	wp_reset_postdata();
}
add_action( 'boomerang_single_boomerang_aside_end', __NAMESPACE__ . '\display_related_ideas' );
