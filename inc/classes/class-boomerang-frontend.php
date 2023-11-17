<?php
/**
 * Defines all functionality for our public-facing frontend.
 */

namespace Bouncingsprout_Boomerang;

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
		add_action( 'wp_ajax_process_admin_action', array( $this, 'process_admin_action' ) );
		add_action( 'wp_ajax_process_filter', array( $this, 'process_filter' ) );
		add_action( 'wp_ajax_nopriv_process_filter', array( $this, 'process_filter' ) );
		add_action( 'wp_ajax_process_tag', array( $this, 'process_tag' ) );
		add_action( 'wp_ajax_nopriv_process_tag', array( $this, 'process_tag' ) );
		add_action( 'wp_head', array( $this, 'render_styles' ) );
		add_action( 'boomerang_new_boomerang', array( $this, 'send_admin_email' ) );

		add_filter( 'single_template', array( $this, 'do_single_template' ) );
		add_filter( 'comments_template', array( $this, 'load_comments_template' ) );
		add_filter( 'body_class', array( $this, 'enable_default_styles' ) );
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

		if ( ! boomerang_default_styles_disabled() ) {
			wp_enqueue_style(
				'boomerang-default',
				BOOMERANG_URL . 'assets/css/boomerang-default.css',
				null,
				BOOMERANG_VERSION
			);
		}

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
	 * Render our dynamic styles from board settings, customizers and so on.
	 *
	 * @return void
	 */
	public function render_styles() {
		global $post;

		// Widths are generally handled by pages containing Boomerang shortcodes, so we defer to them
		if ( $post && ( 'boomerang' === $post->post_type || 'boomerang_board' === $post->post_type ) ) :
			$container_width = boomerang_get_container_width();
			?>

			<style id="boomerang-dynamic-styles">
				.boomerang-container {
					width: <?php echo esc_attr( $container_width ); ?>;
				}
			</style>

			<?php
		endif;
	}

	/**
	 * Save a Boomerang.
	 *
	 * @return void
	 */
	public function save_boomerang() {
		if ( ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['boomerang_form_nonce'] ) ),
			'boomerang-form-nonce'
		) ) {
			$error = new \WP_Error(
				'Boomerang: Failed Security Check on Form Submission',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );

			wp_die();
		}

		$title   = sanitize_text_field( $_POST['title'] );
		$content = sanitize_textarea_field( $_POST['content'] );
		$board   = intval( $_POST['board'] );

		if ( ! empty( $_POST['tags'] ) ) {
			if ( is_array( $_POST['tags'] ) ) {
				$tags = array_map( 'sanitize_text_field', $_POST['tags'] );
			} else {
				$tags = sanitize_text_field( $_POST['tags'] );
			}
		}

		// Do some minor form validation to make sure there is content
		if ( strlen( $title ) < 3 ) {
			$error = new \WP_Error(
				'Boomerang: User Input Error',
				esc_html__( 'Please enter a title. Titles must be at least three characters long.', 'boomerang' )
			);

			wp_send_json_error( $error );

			wp_die();
		}

		if ( current_user_can( 'manage_options' ) ) {
			// Admin created Boomerangs are never held for review
			$post_status = 'publish';
		} else {
			$post_status = boomerang_get_default_status( $board );
		}

		// Add the content of the form to $post as an array
		$args = array(
			'post_title'     => $title,
			'post_content'   => $content,
			'post_status'    => $post_status,
			'post_type'      => 'boomerang',
			'post_parent'    => $board,
			'comment_status' => 'open',
		);

		$post_id = wp_insert_post( $args );

		if ( isset( $tags ) ) {
			wp_set_post_terms( $post_id, $tags, 'boomerang_tag' );
		}

		if ( ! empty( $_FILES ) ) {
			//Include the required files from backend
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			// Allowed file types -> search online for desired mime types
			$allowed_file_types = apply_filters(
				'boomerang_upload_types',
				array( 'image/jpeg', 'image/jpg', 'image/png' )
			);

			//Check if uploaded file doesn't contain any error
			if ( isset( $_FILES['boomerang_image_upload']['error'] ) && 0 === $_FILES['boomerang_image_upload']['error'] ) {
				// Check file type
				if ( ! in_array( $_FILES['boomerang_image_upload']['type'], $allowed_file_types, true ) ) {
					$error = new WP_Error(
						'Boomerang: User Input Error',
						esc_html__( 'Please upload one of the following filetypes: jpg, jpeg, png.', 'boomerang' )
					);

					wp_send_json_error( $error );
				}
				$file_id = media_handle_upload( 'boomerang_image_upload', $post_id );

				if ( ! is_wp_error( $file_id ) ) {
					set_post_thumbnail( $post_id, $file_id );
				}
			}
		}

		do_action( 'boomerang_new_boomerang', $post_id );

		if ( 'publish' === $post_status ) {
			$message = __( 'Saved!', 'boomerang' );
		} else {
			$message = __( 'We will process your submission shortly. Thank you!', 'boomerang' );
		}

		$return = array(
			'message' => $message,
			'content' => boomerang_get_boomerangs( $board ),
		);

		wp_send_json_success( $return );

		wp_die();
	}

	/**
	 * Render a complete instance of Boomerang on a page.
	 *
	 * @return false|string
	 */
	public function render_boomerang_full( $atts ) {
		$a = shortcode_atts(
			array(
				'board' => false,
			),
			$atts
		);

		if ( empty( array_filter( $a ) ) ) {
			return '<p><strong>Please ensure your Boomerang shortcode contains an ID, or your block has a board assigned</strong></p>';
		}

		ob_start();
		?>

		<div id="boomerang-full" class="
		<?php
		echo esc_attr(
			get_post_field(
				'post_name',
				get_post( $a['board'] )
			)
		);
		?>
			" data-board="<?php echo esc_attr( $a['board'] ); ?>">
			<?php

			if ( boomerang_board_title_enabled() ) {
				the_title( '<h2 class="entry-title board-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
			}

			$this->render_boomerang_form( $a ); // phpcs:ignore -- escaped later

			$this->render_boomerang_directory( $a );

			?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Renders a form to submit new Boomerangs.
	 *
	 * @return false|string
	 */
	public function render_boomerang_form( $atts ) {
		$a = shortcode_atts(
			array(
				'board' => $atts['board'] ?? false,
			),
			$atts
		);

		if ( ! is_user_logged_in() ) {
			echo esc_html__( 'You must be logged in to submit. Sorry.', 'boomerang' );

			return;
		}

		$labels = boomerang_get_form_labels( $a['board'] );

		ob_start();
		?>

		<div id="boomerang-form-wrapper" class="boomerang-container 
		<?php
		echo esc_attr(
			get_post_field(
				'post_name',
				get_post( $a['board'] )
			)
		);
		?>
			" data-board="<?php echo esc_attr( $a['board'] ); ?>">
			<form id="boomerang-form" method="post" enctype='multipart/form-data' data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang-form-nonce' ) ); ?>">

				<fieldset>
					<label for="title"><?php echo esc_html( $labels['title'] ); ?></label>
					<input type="text" id="boomerang-title" value="" tabindex="1" size="20" name="title"/>
				</fieldset>

				<?php if ( boomerang_board_tags_enabled( $a['board'] ) ) : ?>
					<fieldset>
						<label for="tags"><?php echo esc_html( $labels['tags'] ); ?></label>
						<select class="boomerang_select select2" id="boomerang-tags" name="tags[]" multiple="multiple" style="width: 100%">';

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
					</fieldset>

				<?php endif; ?>

				<fieldset>
					<label for="content"><?php echo esc_html( $labels['content'] ); ?></label>
					<textarea id="boomerang-content" tabindex="3" name="content" cols="50" rows="6"></textarea>
				</fieldset>

				<?php if ( boomerang_board_image_enabled() ) : ?>

					<?php if ( ! boomerang_default_styles_disabled() ) : ?>

						<fieldset>
							<label for="boomerang_image_upload" class="drop-container" id="boomerang-dropcontainer">
								<span class="drop-title">
								<?php
								echo esc_html__(
									'Drop file here',
									'boomerang'
								);
								?>
										</span>
								<span class="drop-conjunction"><?php echo esc_html__( 'or', 'boomerang' ); ?></span>
								<input type="file" name="boomerang_image_upload" id="boomerang_image_upload" accept="image/*">
							</label>
						</fieldset>

					<?php else : ?>

						<fieldset>
							<input type="file" name="boomerang_image_upload" id="boomerang_image_upload" accept="image/*"/>
							<label for="boomerang_image_upload">
							<?php
							echo esc_html__(
								'Choose a file',
								'boomerang'
							);
							?>
									</label>
						</fieldset>

					<?php endif; ?>

				<?php endif; ?>

				<div id="bf-footer">
					<input name="boomerang_board" id="boomerang-board" type="hidden" value="<?php echo esc_attr( $a['board'] ); ?>">
					<button id="bf-submit"><?php echo esc_html( $labels['submit'] ); ?>
						<div id="bf-spinner"></div>
					</button>
					<span id="bf-result"></span>
				</div>

			</form>
		</div>

		<?php
		return ob_get_flush();
	}

	/**
	 * Render a directory of Boomerangs.
	 *
	 * @return false|string
	 */
	public function render_boomerang_directory( $atts ) {
		$a = shortcode_atts(
			array(
				'board' => $atts['board'] ?? false,
			),
			$atts
		);

		ob_start();

		if ( boomerang_board_filters_enabled( $a['board'] ) ) {
			echo boomerang_get_filters();
		}

		?>

		<div class="boomerang-container boomerang-directory <?php echo esc_attr( boomerang_get_board_slug( $a['board'] ) ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>">

			<?php echo boomerang_get_boomerangs( $a['board'] ); ?>

		</div>

		<?php

		return ob_get_flush();
	}

	public function do_single_template( $single_template ) {
		global $post;

		if ( 'boomerang' === $post->post_type ) {
			$single_template = BOOMERANG_PATH . '/templates/single.php';
		}
		if ( 'boomerang_board' === $post->post_type ) {
			$single_template = BOOMERANG_PATH . '/templates/archive.php';
		}

		return $single_template;
	}

	/**
	 * Process an admin action.
	 *
	 * @return void
	 */
	public function process_admin_action() {
		if ( ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
			'boomerang_admin_area'
		) ) {
			$error = new WP_Error(
				'Boomerang: Failed Security Check on Admin Action',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );
		}

		$post_id = sanitize_text_field( $_POST['post_id'] );
		$status  = sanitize_text_field( $_POST['status'] );

		if ( isset( $status ) ) {
			if ( '-1' === $status ) {
				wp_delete_object_term_relationships( $post_id, 'boomerang_status' );
			} else {
				wp_set_post_terms( $post_id, $status, 'boomerang_status' );
			}
		}

		$return = array(
			'message' => __( 'Status Set', 'boomerang' ),
			'content' => boomerang_get_status( get_post( $post_id ) ),
		);

		wp_send_json_success( $return );

		wp_die();
	}

	/**
	 * Process a Boomerang filter.
	 *
	 * @return void
	 */
	public function process_filter() {
		if ( ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
			'boomerang_filters'
		) ) {
			$error = new WP_Error(
				'Boomerang: Failed Security Check on Filtering',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );
		}

		$board            = sanitize_text_field( $_POST['board'] );
		$boomerang_order  = sanitize_text_field( $_POST['boomerang_order'] );
		$boomerang_status = sanitize_text_field( $_POST['boomerang_status'] );
		$boomerang_tags   = sanitize_text_field( $_POST['boomerang_tags'] );
		$boomerang_search = sanitize_text_field( $_POST['boomerang_search'] );
		$args             = array();
		$tax_query        = array( 'relation' => 'AND' );

		if ( '-1' !== $boomerang_status ) {
			$tax_query[] = array(
				'taxonomy' => 'boomerang_status',
				'terms'    => $boomerang_status,
			);
		}

		if ( '-1' !== $boomerang_tags ) {
			$tax_query[] = array(
				'taxonomy' => 'boomerang_tag',
				'terms'    => $boomerang_tags,
			);
		}

		$args['tax_query'] = $tax_query;

		if ( $boomerang_search ) {
			$args['s'] = $boomerang_search;
		}

		switch ( $boomerang_order ) {
			case 'latest':
			default:
				$args['order'] = 'DESC';
				break;

			case 'popular':
				$args['orderby']  = 'meta_value_num date';
				$args['order']    = 'DESC';
				$args['meta_key'] = 'boomerang_votes';
				break;

			case 'mine':
				$args['author'] = get_current_user_id();
				break;

			case 'voted':
				$args['post__in'] = boomerang_get_user_voted( get_current_user_id() );
				break;
		}

		$return = array(
			'content' => boomerang_get_boomerangs( $board, $args ),
		);

		wp_send_json_success( $return );

		wp_die();
	}

	/**
	 * Process a Boomerang tag.
	 *
	 * @return void
	 */
	public function process_tag() {
		if ( ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
			'boomerang_select_tag'
		) ) {
			$error = new WP_Error(
				'Boomerang: Failed Security Check on Tag Selection',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );
		}

		$board            = sanitize_text_field( $_POST['board'] );
		$boomerang_order  = isset( $_POST['boomerang_order'] ) ? sanitize_text_field( $_POST['boomerang_order'] ) : '';
		$boomerang_status = isset( $_POST['boomerang_status'] ) ? sanitize_text_field( $_POST['boomerang_status'] ) : '';
		$boomerang_tags   = isset( $_POST['boomerang_tags'] ) ? sanitize_text_field( $_POST['boomerang_tags'] ) : '';
		$boomerang_search = isset( $_POST['boomerang_search'] ) ? sanitize_text_field( $_POST['boomerang_search'] ) : '';
		$args             = array();
		$tax_query        = array( 'relation' => 'AND' );

		if ( $boomerang_status && '-1' !== $boomerang_status ) {
			$tax_query[] = array(
				'taxonomy' => 'boomerang_status',
				'terms'    => $boomerang_status,
			);
		}

		if ( $boomerang_tags && '-1' !== $boomerang_tags ) {
			$tax_query[] = array(
				'taxonomy' => 'boomerang_tag',
				'terms'    => $boomerang_tags,
			);
		}

		$args['tax_query'] = $tax_query;

		if ( $boomerang_search ) {
			$args['s'] = $boomerang_search;
		}

		switch ( $boomerang_order ) {
			case 'latest':
			default:
				$args['order'] = 'DESC';
				break;

			case 'popular':
				$args['orderby']  = 'meta_value_num date';
				$args['order']    = 'DESC';
				$args['meta_key'] = 'boomerang_votes';
				break;

			case 'mine':
				$args['author'] = get_current_user_id();
				break;

			case 'voted':
				$args['post__in'] = boomerang_get_user_voted( get_current_user_id() );
				break;
		}

		$return = array(
			'content' => boomerang_get_boomerangs( $board, $args ),
		);

		wp_send_json_success( $return );

		wp_die();
	}


	/**
	 * Adds a class to the body if house styles have been enabled.
	 *
	 * @param $classes
	 *
	 * @return array|void
	 */
	public function enable_default_styles( $classes ) {
		if ( boomerang_default_styles_disabled() ) {
			return $classes;
		}

		return array_merge( $classes, array( 'boomerang-default' ) );
	}

	/**
	 * Load our custom comments template.
	 *
	 * @param $comment_template
	 *
	 * @return string|void
	 */
	public function load_comments_template( $comment_template ) {
		global $post;
		if ( ! ( is_singular() && ( have_comments() || 'open' === $post->comment_status ) ) ) {
			return;
		}
		if ( 'boomerang' === $post->post_type ) {
			return BOOMERANG_PATH . '/templates/comments.php';
		}
	}

	/**
	 * Sends email when a new Boomerang is created.
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function send_admin_email( $post_id ) {
		$to      = boomerang_board_new_boomerang_email_addresses( $post_id );
		$subject = sprintf(
		// translators: %s: Base for our Boomerang CPT
			esc_attr__( 'New %s created', 'boomerang' ),
			esc_attr( boomerang_get_base() )
		);
		$body = sprintf(
		// translators: %1$s: Base for our Boomerang CPT %2$s: Boomerang permalink
			__( 'A new %1$s has been created. You may review it <a href="%2$s">here</a>.', 'boomerang' ),
			esc_attr( boomerang_get_base() ),
			esc_url( get_permalink( $post_id ) ),
		);

		boomerang_send_email( $to, $subject, $body );
	}
}
