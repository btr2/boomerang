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
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'wp_ajax_save_boomerang', array( $this, 'save_boomerang' ) );
		add_action( 'wp_ajax_nopriv_save_boomerang', array( $this, 'save_boomerang' ) );
		add_action( 'wp_ajax_process_admin_action', array( $this, 'process_admin_action' ) );
		add_action( 'wp_ajax_process_filter', array( $this, 'process_filter' ) );
		add_action( 'wp_ajax_nopriv_process_filter', array( $this, 'process_filter' ) );
		add_action( 'wp_ajax_process_tag', array( $this, 'process_tag' ) );
		add_action( 'wp_ajax_process_approve_now', array( $this, 'process_approve_now' ) );
		add_action( 'wp_ajax_nopriv_process_tag', array( $this, 'process_tag' ) );
		add_action( 'boomerang_new_boomerang', array( $this, 'send_admin_email' ) );
		add_action( 'comment_post', array( $this, 'save_comment_meta_data' ) );
		add_action( 'boomerang_archive_boomerang_start', array( $this, 'add_pending_banner' ) );
		add_action( 'boomerang_single_boomerang_start', array( $this, 'add_pending_banner' ) );

		add_filter( 'single_template', array( $this, 'do_single_template' ) );
		add_filter( 'comments_template', array( $this, 'load_comments_template' ) );
		add_filter( 'body_class', array( $this, 'enable_default_styles' ) );
		add_filter( 'comment_form_submit_field', array( $this, 'add_additional_comment_fields' ), 10, 2 );
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
		wp_add_inline_style( 'boomerang', $this->render_inline_styles() );

		wp_enqueue_script(
			'boomerang',
			BOOMERANG_URL . 'assets/js/boomerang.js',
			array( 'jquery', 'select2' ),
			BOOMERANG_VERSION,
			true
		);

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			wp_enqueue_style(
				'boomerang-pro',
				BOOMERANG_URL . 'pro/assets/css/boomerang-pro.css',
				null,
				BOOMERANG_VERSION
			);

			wp_enqueue_script(
				'boomerang-pro',
				BOOMERANG_URL . 'pro/assets/js/boomerang-pro.js',
				array( 'jquery', 'select2' ),
				BOOMERANG_VERSION,
				true
			);
		}

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
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'success'  => __( 'Saved!', 'boomerang' ),
				'comment'  => __( 'Add comment', 'boomerang' ),
				'note'     => __( 'Add private note', 'boomerang' ),
				'approved' => esc_html__( 'Approved', 'boomerang' ),
			)
		);
	}

	/**
	 * Render our dynamic styles from board settings, customizers and so on.
	 *
	 * @return void
	 */
	public function render_inline_styles() {
		global $post;

		$custom_css = '';

		// Widths are generally handled by pages containing Boomerang shortcodes, so we defer to them
		$custom_css .= ':root {--boomerang-primary-color:#027AB0;}';
		$custom_css .= ':root {--boomerang-team-color:#fab347;}';
		$custom_css .= ':root {--boomerang-container-width:' . esc_attr( boomerang_get_container_width() ) . '}';

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			$options = get_option( 'boomerang_customizer' );
			$terms   = get_terms(
				array(
					'taxonomy'   => 'boomerang_status',
					'hide_empty' => false,
				)
			);

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$color_meta            = get_term_meta( $term->term_id, 'color', true );
					$background_color_meta = get_term_meta( $term->term_id, 'background_color', true );

					$color            = ! empty( $color_meta ) ? esc_attr( $color_meta ) : '#FFFFFF';
					$background_color = ! empty( $background_color_meta ) ? esc_attr( $background_color_meta ) : '#FFFFFF';

					$custom_css .= '.boomerang_status-' . $term->slug . ' .boomerang-meta .boomerang-status{color:' . $color . ';border-color:' . $color . ';background-color:' . $background_color . ';}';
					$custom_css .= '.boomerang-related-idea.boomerang_status-' . $term->slug . ' .boomerang-meta .boomerang-status{color:' . $color . ';border-color:' . $color . ';background-color:' . $background_color . ';}';
					$custom_css .= '.boomerang-suggestion.boomerang_status-' . $term->slug . ' .boomerang-status{color:' . $color . ';border-color:' . $color . ';background-color:' . $background_color . ';}';
				}
			}

			if ( $options['archive_layout'] && 'grid' === $options['archive_layout'] ) {
				$custom_css .= '.boomerang-default #boomerang-full{width:' . esc_attr( boomerang_get_container_width() ) . ';}';
			}

			if ( isset( $options['primary_color'] ) ) {
				$custom_css .= ':root {--boomerang-primary-color: ' . esc_attr( $options['primary_color'] ) . ';}';
			}

			if ( isset( $options['private_note_color'] ) ) {
				$custom_css .= ':root {--boomerang-team-color: ' . esc_attr( $options['private_note_color'] ) . ';}';
			}
		}

		return $custom_css;
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

		if ( isset( $_POST['boomerang_hp'] ) && '' !== $_POST['boomerang_hp'] ) {
			$error = new \WP_Error(
				'Boomerang: Failed Spam Check (honeypot)',
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

		do_action( 'boomerang_new_boomerang_before_save', $args );

		// Final check the current user can submit
		$can_submit = boomerang_user_can_submit( $board, get_current_user_id() );
		if ( is_array( $can_submit ) ) {
			// User cannot submit
			$error = new \WP_Error(
				'Boomerang: User Cannot Submit',
				esc_html( $can_submit['message'] )
			);

			wp_send_json_error( $error );

			wp_die();
		}

		$post_id = wp_insert_post( $args, true );

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

		if ( isset( $_POST['acf'] ) ) {
			do_action( 'boomerang_update_acf', $_POST['acf'], $post_id );
		}

		do_action( 'boomerang_new_boomerang', $post_id, $board );

		if ( 'publish' === $post_status ) {
			$message = __( 'Saved!', 'boomerang' );
		} else {
			$message = __( 'We will process your submission shortly. Thank you!', 'boomerang' );
		}

		$return = array(
			'id'      => $post_id,
			'message' => $message,
			'content' => boomerang_get_boomerangs( $board ),
		);

		wp_send_json_success( $return );

		wp_die();
	}

	/**
	 * Locate and serve a template for our Boomerang pages.
	 *
	 * @param $single_template
	 *
	 * @return mixed|string
	 */
	public function do_single_template( $single_template ) {
		global $post;

		if ( 'boomerang' === $post->post_type ) {
			if ( boo_fs()->can_use_premium_code__premium_only() ) {
				$theme_template = locate_template( 'boomerang/single.php' );

				if ( $theme_template ) {
					$single_template = $theme_template;
				} else {
					$single_template = BOOMERANG_PATH . '/templates/single.php';
				}
			} else {
				$single_template = BOOMERANG_PATH . '/templates/single.php';
			}
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
		$term    = '';

		if ( isset( $status ) ) {
			if ( '-1' === $status ) {
				wp_delete_object_term_relationships( $post_id, 'boomerang_status' );
			} else {
				wp_set_post_terms( $post_id, $status, 'boomerang_status' );
				$term = get_term( $status )->slug;
			}
		}

		$return = array(
			'message' => __( 'Status Set', 'boomerang' ),
			'content' => boomerang_get_status( get_post( $post_id ) ),
			'term'    => $term,
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

			case 'random':
				$args['orderby'] = 'rand';
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

		if ( boomerang_can_manage() ) {
			return array_merge( $classes, array( 'boomerang-default', 'boomerang-is-manager' ) );
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
			esc_url( get_permalink( $post_id ) )
		);

		boomerang_send_email( $to, $subject, $body );
	}

	/**
	 * Adds a wrapper around the comment submit button row.
	 *
	 * @param $submit_field
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public function add_additional_comment_fields( $submit_field, $args ) {
		global $post;
		if ( ! $post || ( 'boomerang' !== $post->post_type ) ) {
			return $submit_field;
		}

		$submit_before = '<div class="submit-button-container">';

		$additional_content = '';

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			if ( boomerang_can_manage() ) {
				$additional_content .= '<div class="private-note-toggle"><label class="switch"><input name="private_note" type="checkbox"><span class="slider round"></span></label>';
				$additional_content .= '<span class="private-note-label">' . esc_html__( 'Private note', 'boomerang' ) . '</span>';
				$additional_content .= '</div>';
			}
		}

		$submit_after = '</div>';

		return $submit_before . $additional_content . $submit_field . $submit_after;
	}

	/**
	 * Save any additional meta data for our comment form.
	 *
	 * @param $comment_id
	 *
	 * @return void
	 */
	public function save_comment_meta_data( $comment_id ) {
		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			if ( isset( $_POST['private_note'] ) && 'on' === $_POST['private_note'] ) {
				add_comment_meta( $comment_id, 'boomerang_private_note', true );
			}

			$comment        = get_comment( $comment_id );
			$comment_parent = $comment->comment_parent;

			if ( 0 !== $comment_parent ) {
				$private_note = get_comment_meta( $comment_parent, 'boomerang_private_note', true );

				if ( $private_note ) {
					add_comment_meta( $comment_id, 'boomerang_private_note', true );
				}
			}
		}
	}

	/**
	 * Add a banner to top of Boomerangs to warn that Boomerang is pending.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function add_pending_banner( $post ) {
		if ( 'pending' !== $post->post_status ) {
			return;
		}

		if ( boomerang_can_manage() || is_author( get_current_user_id() ) ) {
			echo '<div class="boomerang-banner pending-banner">';

			if ( ! boomerang_google_fonts_disabled() ) {
				echo '<span class="material-symbols-outlined">visibility_off</span>';
			}

			$text = sprintf(
			/* translators: %s: Singular form of this board's Boomerang name */
				__( 'This %s requires approval.', 'boomerang' ),
				get_singular( $post->post_parent )
			);

			$approve_now = '<span class="banner-action-link approve-now-link" data-id="' . $post->ID . '" data-nonce="' . wp_create_nonce( 'boomerang_approve_now' ) . '">' . __( 'Approve now?', 'boomerang' ) . '</span>';

			echo '<p>' . esc_html( $text ) . '</p>';

			echo wp_kses_post( $approve_now );

			echo '</div>';

		}
	}

	/**
	 * AJAX handler to approve Boomerangs.
	 *
	 * @return void
	 */
	public function process_approve_now() {
		if ( ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
			'boomerang_approve_now'
		) ) {
			$error = new WP_Error(
				'Boomerang: Failed Security Check on Boomerang Approval',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );
		}

		$post_id = sanitize_text_field( $_POST['post_id'] );

		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			)
		);

		$message = sprintf(
		/* translators: %s: Singular form of this board's Boomerang name */
			__( '%s approved.', 'boomerang' ),
			get_singular( get_post( $post_id )->post_parent )
		);

		$return = array(
			'message' => esc_html( ucfirst( $message ) ),
		);

		wp_send_json_success( $return );

		wp_die();
	}
}
