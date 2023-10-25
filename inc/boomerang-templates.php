<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Boomerangs ********************************************************************************************************/

/**
 * Tags
 */

/**
 * Checks if a boomerang has tags, and return an array if so.
 *
 * @param $post
 *
 * @return void
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

	if ( boomerang_has_tags( $post ) ) {
		$terms = get_the_terms( $post->ID, 'boomerang_tag' );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		$links = array();

		foreach ( $terms as $term ) {
			$links[] = '<span rel="tag">' . $term->name . '</span>';
		}

		return implode( $links );
	}

	return false;
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

	if ( has_term( '', 'boomerang_tag', $post ) ) {
		return true;
	}

	return false;
}

/** Statuses **********************************************************************************************************/

/**
 * @return void
 */
function boomerang_the_status() {
	echo boomerang_get_status( get_post() );
}

function boomerang_get_status( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	}

	$terms = get_the_terms( $post->ID, 'boomerang_status' );

	return $terms[0]->name;
}

/** Meta **************************************************************************************************************/

/**
 * @return void
 */
function boomerang_posted_on() {
	$meta = get_post_meta( wp_get_post_parent_id(), 'board_meta', true );

	if ( ! $meta['show_date'] ) {
		return;
	}

	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	$time_string = sprintf(
		$time_string,
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
	echo '<span class="posted-on">';
	printf(
	/* translators: %s: Publish date. */
		esc_html__( 'Published %s', 'boomerang' ),
		$time_string // phpcs:ignore WordPress.Security.EscapeOutput
	);
	echo '</span>';
}

function boomerang_posted_by() {
	$meta = get_post_meta( wp_get_post_parent_id(), 'board_meta', true );

	if ( ! $meta['show_author'] ) {
		return;
	}

	$user_email = get_the_author_meta( 'user_email' );

	echo get_avatar( $user_email, '24' );
	echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" rel="author">' . esc_html( get_the_author() ) . '</a>';

}

/** Attachments and Featured Images ***********************************************************************************/

/**
 *
 */
function boomerang_thumbnail() {
	if ( ! boomerang_board_thumbnails_enabled() ) {
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
