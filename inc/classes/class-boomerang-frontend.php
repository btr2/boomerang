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
		add_action( 'wp_ajax_process_admin_action', array( $this, 'process_admin_action' ) );
		add_action( 'wp_ajax_process_filter', array( $this, 'process_filter' ) );
		add_action( 'wp_ajax_nopriv_process_filter', array( $this, 'process_filter' ) );
		add_action( 'wp_head', array( $this, 'google_fonts' ) );

		add_filter( 'single_template', array( $this, 'do_single_template' ) );
		add_filter( 'body_class', array( $this, 'enable_house_styles' ) );
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

		if ( ! boomerang_get_option( 'disable_google_fonts' ) ) {
			wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', false );
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

	public function google_fonts() {
		?>

		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

		<?php
	}

	/**
	 * Save a Boomerang.
	 *
	 * @return void
	 */
	public function save_boomerang() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['boomerang_form_nonce'] ) ), 'boomerang-form-nonce' ) ) {
			$error = new WP_Error(
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
			$error = new WP_Error(
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
			$allowed_file_types = apply_filters( 'boomerang_upload_types', array( 'image/jpeg', 'image/jpg', 'image/png' ) );

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

		$return = array(
			'message' => __( 'Saved!', 'boomerang' ),
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

		ob_start();
		?>

		<div id="boomerang-full <?php echo esc_attr( $a['board'] ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>">
			<?php
			echo $this->render_boomerang_form( $a ); // phpcs:ignore -- escaped later
			?>
			<?php $this->render_boomerang_directory( $a ); ?>
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

		<div id="boomerang-form-wrapper" class="<?php echo esc_attr( $a['board'] ); ?>" data-board="<?php echo esc_attr( $a['board'] ); ?>">
			<form id="boomerang-form" method="post" enctype='multipart/form-data' data-nonce="<?php echo esc_attr( wp_create_nonce( 'boomerang-form-nonce' ) ); ?>">

				<fieldset>
					<label for="title"><?php echo esc_html( $labels['title'] ); ?></label><br/>
					<input type="text" id="boomerang-title" value="" tabindex="1" size="20" name="title"/>
				</fieldset>

				<?php if ( boomerang_board_tags_enabled( $a['board'] ) ) : ?>
				<fieldset>
					<label for="tags"><?php echo esc_html( $labels['tags'] ); ?></label><br/>
					<select class="boomerang_select select2" id="boomerang-tags" name="tags[]" multiple="multiple">';

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
					<label for="content"><?php echo esc_html( $labels['content'] ); ?></label><br/>
					<textarea id="boomerang-content" tabindex="3" name="content" cols="50" rows="6"></textarea>
				</fieldset>

				<fieldset>
					<input type="file" name="boomerang_image_upload" id="boomerang_image_upload" accept="image/*" multiple="false" />
				</fieldset>

				<p id="bf-footer">
					<div id="bf-spinner"></div>
					<input name="boomerang_board" id="boomerang-board" type="hidden" value="<?php echo esc_attr( $a['board'] ); ?>">
					<button id="bf-submit"><?php echo esc_html( $labels['submit'] ); ?></button>
					<span id="bf-result"></span>
				</p>

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

		require_once BOOMERANG_PATH . '/templates/archive.php';

		?>



		<?php

		return ob_get_flush();
	}

	public function do_single_template( $single_template ) {
		global $post;

		if ( 'boomerang' === $post->post_type ) {
			$single_template = BOOMERANG_PATH . '/templates/single.php';
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
	 * Adds a class to the body if house styles have been enabled.
	 *
	 * @param $classes
	 *
	 * @return array|void
	 */
	public function enable_house_styles( $classes ) {
		if ( ! boomerang_house_styles_enabled() ) {
			return $classes;
		}

		return array_merge( $classes, array( 'boomerang-house-styles' ) );
	}
}
