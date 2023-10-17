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
		add_shortcode( 'boomerang_list', array( $this, 'render_boomerang_directory' ) );
		add_shortcode( 'boomerang', array( $this, 'render_boomerang_full' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'wp_ajax_save_boomerang', array( $this, 'save_boomerang' ) );
	}

	/**
	 * Enqueue our scripts and styles.
	 *
	 * @return void
	 */
	public function frontend_scripts() {
		wp_enqueue_style( 'select2', BOOMERANG_URL . 'assets/css/select2.min.css', null, '4.1.0-rc.0' );
		wp_enqueue_script(
			'select2',
			BOOMERANG_URL . 'assets/js/select2.min.js',
			array( 'jquery' ),
			'4.1.0-rc.0',
			true
		);

		wp_enqueue_style( 'boomerang', BOOMERANG_URL . 'assets/css/boomerang.css', null, BOOMERANG_VERSION );
		wp_enqueue_script(
			'boomerang',
			BOOMERANG_URL . 'assets/js/boomerang.js',
			array( 'jquery', 'select2' ),
			BOOMERANG_VERSION,
			true
		);

		// set variables for script
		wp_localize_script(
			'boomerang',
			'settings',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'success' => __( 'Saved!', 'boomerang' ),
			)
		);
	}

	/**
	 * Save a Boomerang.
	 *
	 * @return void
	 */
	public function save_boomerang() {
		// Check that the nonce was set and valid
		if ( ! wp_verify_nonce( $_POST['boomerang_form_nonce'], 'boomerang-form-nonce' ) ) {
			$error = new WP_Error(
				'Boomerang: Failed Security Check on Form Submission',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );
		}

		parse_str( stripslashes( $_POST['boomerang_form'] ), $form );

		// Do some minor form validation to make sure there is content
		if ( strlen( $form['title'] ) < 3 ) {
			$error = new WP_Error(
				'Boomerang: User Input Error',
				__( 'Please enter a title. Titles must be at least three characters long.', 'boomerang' )
			);

			wp_send_json_error( $error );
		}

		// Add the content of the form to $post as an array
		$args = array(
			'post_title'   => sanitize_text_field( $form['title'] ),
			'post_content' => sanitize_textarea_field( $form['content'] ),
			'post_status'  => 'draft',   // Could be: publish
			'post_type'    => 'boomerang', // Could be: `page` or your CPT
		);

		$post_id = wp_insert_post( $args );

		if ( isset( $form['tags'] ) ) {
			// Sanitize array values
			$tags = array_map( 'sanitize_text_field', $form['tags'] );
			wp_set_post_terms( $post_id, $tags, 'boomerang_tag' );
		}

		$return = array(
			'message' => __( 'Saved!', 'boomerang' ),
			'content' => $this->get_boomerangs(),
		);

		wp_send_json_success( $return );

		wp_die();
	}

	public function get_boomerangs() {
		$args      = array(
			'post_type'   => 'boomerang',
			'post_status' => boomerang_show_drafts(),
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

		return ob_get_clean();
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
			<?php
			echo $this->render_boomerang_form(); // phpcs:ignore -- escaped later
			?>
			<?php $this->render_boomerang_directory(); ?>
		</div>

		<?php
		return ob_get_clean();
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

		ob_start();
		?>

		<div id="boomerang-form-wrapper">
			<form id="boomerang-form" method="post" enctype='multipart/form-data' data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang-form-nonce' ) ); ?>">

				<p><label for="title"><?php echo esc_html( boomerang_label_title() ); ?></label><br/>
					<input type="text" id="title" value="" tabindex="1" size="20" name="title"/>
				</p>

				<label for="tags">Tags:</label><br/>
				<select class="boomerang_select select2" id="tags" name="tags[]" multiple="multiple">';

					<?php

						$tags = get_terms(
							array(
								'taxonomy'   => 'boomerang_tag',
								'hide_empty' => false,
							)
						);

					if ( $tags ) {
						foreach ( $tags as $tag ) :
							?>
								<option value="<?php echo esc_attr( $tag->slug ); ?>"><?php echo esc_html( $tag->name ); ?></option>
							<?php
							endforeach;
					}
					?>

				</select>

				<p>
					<label for="content"><?php echo esc_html( boomerang_label_content() ); ?></label><br/>
					<textarea id="content" tabindex="3" name="content" cols="50" rows="6"></textarea>
				</p>

				<div id="bf-footer">
					<div id="bf-spinner"></div>
					<button id="bf-submit"><?php echo esc_html( boomerang_label_submit() ); ?></button>
					<span id="bf-result"></span>
				</div>

			</form>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Render a directory of Boomerangs.
	 *
	 * @return false|string
	 */
	public function render_boomerang_directory() {
		ob_start();
		?>

		<div class="boomerang-directory">

			<?php echo wp_kses( $this->get_boomerangs(), 'post' ); ?>

		</div>

		<?php

		return ob_get_flush();
	}
}
