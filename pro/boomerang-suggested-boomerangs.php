<?php
/**
 * Suggested Boomerang Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks to see if suggested boomerangs are enabled.
 *
 * @param $post
 *
 * @return false|mixed
 */
function suggested_boomerangs_enabled( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['enable_suggested_boomerangs'] ?? false;
}

/**
 * Gets the title for the suggested Boomerangs area.
 *
 * @param $post
 *
 * @return false|mixed
 */
function get_suggested_boomerangs_title( $post = false  ) {
	$post = boomerang_get_post( $post );

	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	if ( empty($meta['suggested_boomerangs_label'])) {
		return 'Suggested Ideas';
	} else {
		return $meta['suggested_boomerangs_label'];
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
function display_suggested_ideas( $board ) {
	if ( ! suggested_boomerangs_enabled( $board ) ) {
		return;
	}
	?>

	<div class="boomerang-suggested-ideas-container">
		<header>
			<h2><?php echo esc_html( get_suggested_boomerangs_title( $board ) ); ?>:</h2>
			<?php if ( boomerang_google_fonts_disabled() ) : ?>
				<span class="chevron">&#x276F;</span>
			<?php else : ?>
				<span class="material-symbols-outlined chevron">chevron_right</span>
			<?php endif; ?>
		</header>

		<ul class="boomerang-suggested-ideas-list">

		</ul>
	</div>

<?php
}
add_action( 'boomerang_form_below_title', __NAMESPACE__ . '\display_suggested_ideas' );

/**
 * Displays related ideas on single Boomerangs.
 *
 * @param $post_data
 * @param $post_id
 *
 * @return void
 */
function find_suggested_ideas() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
		'boomerang-title-nonce'
	) ) {
		$error = new \WP_Error(
			'Boomerang: Failed Security Check on Title Suggested Ideas processing',
			__( 'Something went wrong.', 'boomerang' )
		);

		wp_send_json_error( $error );

		wp_die();
	}

	if ( ! empty( $_POST['board'] ) ) {
		$board = absint( $_POST['board'] );
	}

	$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

	$args = array(
		'post_type'      => 'boomerang',
		'post_parent'    => $board ?? '',
		'numberposts' => 5,
		's'              => $value,
		'orderby'        => 'relevance',
		'order'          => 'DESC',
	);

	$post_list = get_posts( $args );

	$result = array();

	foreach ( $post_list as $post ) {
		$result[] = render_suggested_idea( $post );
	}

	$return = array(
		'content' => $result,
	);

	wp_send_json_success( $return );

	wp_die();
}
add_action( 'wp_ajax_find_suggested_ideas', __NAMESPACE__ . '\find_suggested_ideas' );
add_action( 'wp_ajax_nopriv_find_suggested_ideas', __NAMESPACE__ . '\find_suggested_ideas' );

function render_suggested_idea( $post ) {
	$terms = get_the_terms( $post->ID, 'boomerang_status' );

	if ( $terms ) {
		$status = 'boomerang_status-' . $terms[0]->slug;
	}

	ob_start();
	?>

	<li class="boomerang-suggestion <?php echo esc_attr( $status ?? '' ); ?>">
		<a href="<?php echo esc_url( get_permalink( $post ) ) ?>">
			<div class="suggestion-left">
				<div class="votes-container">
					<span class="boomerang-vote-count"><?php echo esc_html( boomerang_get_votes( $post ) ); ?></span>
					<span class="boomerang-vote-label">
						<?php echo esc_html( sprintf( _n( 'vote','votes',	boomerang_get_votes( $post ),'boomerang' ) ) ); ?>
					</span>
				</div>
			</div>
			<div class="suggestion-right">
				<h2 class="entry-title">
					<?php echo esc_html( get_the_title( $post ) ) ?>
				</h2>
				<p class="entry-content">
					<?php echo wp_kses_post( $post->post_content ); ?>
				</p>
				<div class="meta">
					<div class="meta-left">
						<?php
						if ( boomerang_board_author_enabled( $post->post_parent ) ) {
							if ( boomerang_board_author_avatar_enabled( $post->post_parent ) ) {
								$user_email = get_the_author_meta( 'user_email', $post->post_author );
								echo get_avatar( $user_email, '36' );
							}
						}
						?>
						<?php if ( boomerang_board_date_enabled( $post->post_parent ) ) : ?>
							<div class="boomerang-posted-on"><?php boomerang_posted_on( $post ); ?></div>
						<?php endif; ?>
				</div>
				<div class="meta-right">
					<?php if ( boomerang_board_statuses_enabled( $post->post_parent ) ) : ?>
						<div class="boomerang-status" <?php echo boomerang_has_status( $post ) ? '' : 'style="display: none"'; ?>><?php echo wp_kses_post( boomerang_get_status( $post ) ); ?></div>
					<?php endif; ?>
					<?php if ( boomerang_board_comments_enabled( $post->post_parent ) ) : ?>
							<div class="boomerang-comment-count">
								<?php boomerang_get_comments_count_html( $post ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</a>
	</li>

<?php return ob_get_clean();
}
