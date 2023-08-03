<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles displays and hooks for the Boomerang frontend functionality.
 */
class Boomerang_Frontend {
	/**
	 * Define the frontend functionality of the plugin.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Separate our hooks from our constructor.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_shortcode( 'boomerang_form', array( $this, 'render_boomerang_form' ) );
		add_shortcode( 'boomerangs', array( $this, 'render_boomerang_directory' ) );
		add_shortcode( 'boomerang', array( $this, 'render_boomerang_full' ) );
	}

	/**
	 * Renders a form to submit new Boomerangs.
	 *
	 * @return false|string
	 */
	public function render_boomerang_form() {
		if ( ! is_user_logged_in() ) {
			echo esc_html__( 'You must be logged in to submit. Sorry.', 'boomerang' );
			return;
		}

		$this->save_post_if_submitted();
		ob_start();
		?>

		<div id="boomerang-form-wrapper">
			<form id="boomerang_form" name="boomerang_form" method="post">

				<p><label for="title">Title</label><br />
					<input type="text" id="title" value="" tabindex="1" size="20" name="title" />
				</p>

				<p>
					<label for="content">Post Content</label><br />
					<textarea id="content" tabindex="3" name="content" cols="50" rows="6"></textarea>
				</p>

				<?php wp_nonce_field( 'boomerang_form_nonce' ); ?>

				<p><input type="submit" value="Submit" tabindex="6" id="submit" name="submit" /></p>

			</form>
		</div>

		<?php
		return ob_get_flush();
	}

	/**
	 * Save a Boomerang.
	 *
	 * @return void
	 */
	public function save_post_if_submitted() {
		// Stop running function if form wasn't submitted
		if ( ! isset( $_POST['title'] ) ) {
			return;
		}

		// Check that the nonce was set and valid
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'boomerang_form_nonce' ) ) {
			echo esc_html__( 'Did not save because your form seemed to be invalid. Sorry', 'boomerang' );
			return;
		}

		// Do some minor form validation to make sure there is content
		if ( strlen( $_POST['title'] ) < 3 ) {
			echo esc_html__( 'Please enter a title. Titles must be at least three characters long.', 'boomerang' );
			return;
		}

		if ( strlen( $_POST['content'] ) < 30 ) {
			echo esc_html__( 'Please enter content more than 30 characters in length', 'boomerang' );
			return;
		}

		// Add the content of the form to $post as an array
		$post = array(
			'post_title'   => sanitize_text_field( $_POST['title'] ),
			'post_content' => sanitize_textarea_field( $_POST['content'] ),
			'post_status'  => 'draft',   // Could be: publish
			'post_type'    => 'boomerang', // Could be: `page` or your CPT
		);

		wp_insert_post( $post );

		// Prevent form resubmission
		$new_url = add_query_arg( 'success', 1, get_permalink() );
		wp_safe_redirect( $new_url, 303 );
		exit;
	}

	/**
	 * Render a directory of Boomerangs.
	 *
	 * @return false|string
	 */
	public function render_boomerang_directory() {
		// The Query.
		$args      = array(
			'post_type' => 'boomerang',
		);
		$the_query = new WP_Query( $args );

		ob_start();
		?>

		<?php if ( $the_query->have_posts() ) : ?>
			<?php
			while ( $the_query->have_posts() ) :
				$the_query->the_post();
				?>
			<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

				<?php

				the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );

				?>

				<div class="post-inner">

					<div class="entry-content">

						<?php the_excerpt(); ?>

					</div><!-- .entry-content -->

				</div><!-- .post-inner -->

				<div class="section-inner">

				</div><!-- .section-inner -->

			</article><!-- .post -->
		<?php endwhile; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
		<?php endif; ?>

		<?php

		return ob_get_flush();
	}

	/**
	 * Render a complete instance of Boomerang on a page.
	 *
	 * @return false|string
	 */
	public function render_boomerang_full() {
		ob_start();
		?>

		<div id="boomerang-full">
			<?php $this->render_boomerang_form(); ?>
			<?php $this->render_boomerang_directory(); ?>
		</div>

		<?php
		return ob_get_clean();
	}
}
