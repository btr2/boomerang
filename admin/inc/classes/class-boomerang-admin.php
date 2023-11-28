<?php
/**
 * Our admin class. Not much else to write here, really.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang backend.
 */
class Boomerang_Admin {
	/**
	 * Define the admin functionality of the plugin.
	 */
	public function __construct() {
		require_once BOOMERANG_PATH . 'vendor/codestar-framework/codestar-framework.php';

		$this->init_hooks();

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			require_once BOOMERANG_PATH . 'admin/inc/classes/class-boomerang-customizer.php';
			$boomerang_customizer = new Boomerang_Customizer();
		}
	}

	/**
	 * Decouple our hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueues' ) );
		add_action( 'in_admin_header', array( $this, 'add_custom_header' ) );
		add_action( 'csf_loaded', array( $this, 'add_settings_page' ) );
		add_action( 'csf_loaded', array( $this, 'add_board_metabox' ) );
		add_action( 'add_meta_boxes_boomerang', array( $this, 'add_boomerang_parent_metabox' ), 10, 2 );

		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
		add_filter( 'manage_boomerang_posts_columns', array( $this, 'add_boomerang_board_column' ) );
		add_filter( 'manage_boomerang_posts_columns', array( $this, 'position_boomerang_board_column' ) );
		add_filter( 'manage_posts_custom_column', array( $this, 'populate_boomerang_board_column' ), 10, 2 );

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			require BOOMERANG_PATH . '/admin/inc/boomerang-pro-admin-filters.php';

			add_action( 'boomerang_status_add_form_fields', array( $this, 'add_category_fields__premium_only' ), 10, 2 );
			add_action( 'boomerang_status_edit_form_fields', array( $this, 'add_category_fields__premium_only' ), 10, 2 );
			add_action( 'edited_boomerang_status', array( $this, 'save_category_fields__premium_only' ), 10, 2 );
			add_action( 'create_boomerang_status', array( $this, 'save_category_fields__premium_only' ), 10, 2 );
		}
	}

	/**
	 * Enqueues.
	 *
	 * @return void
	 */
	public function admin_enqueues() {
		/**
		 * Check whether the get_current_screen function exists
		 * because it is loaded only after 'admin_init' hook.
		 */
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();

			if ( 'boomerang' === $current_screen->post_type || 'boomerang_board' === $current_screen->post_type ) {
				wp_enqueue_style( 'boomerang', BOOMERANG_URL . 'admin/assets/css/boomerang-admin.css', null, BOOMERANG_VERSION );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'boomerang', BOOMERANG_URL . 'admin/assets/js/boomerang.js', array( 'wp-color-picker' ), BOOMERANG_VERSION, true );
			}
		}
	}

	/**
	 * Add a custom header to our admin pages.
	 *
	 * @return void
	 */
	public function add_custom_header() {
		/**
		 * Check whether the get_current_screen function exists
		 * because it is loaded only after 'admin_init' hook.
		 */
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();

			if ( 'boomerang' === $current_screen->post_type || 'boomerang_board' === $current_screen->post_type ) : ?>
				<div class="boomerang-admin-header <?php echo 'boomerang_page_boomerang-contact' === $current_screen->base ? 'drop' : ''; ?>">
					<div class="boomerang-title">
						<img class="boomerang-logo" src="<?php echo esc_url( BOOMERANG_URL . 'admin/assets/images/logo-white.png' ); ?>" alt="Boomerang Logo">
						<p>Version <?php echo esc_html( BOOMERANG_VERSION ); ?></p>
					</div>
					<h2 class="boomerang-notices-container"></h2>
				</div>
				<?php
			endif;
		}
	}

	/**
	 * Force our Boomerang Post type to use classic editor.
	 *
	 * @param $use_block_editor
	 * @param $post_type
	 *
	 * @return bool
	 */
	public function disable_block_editor( $use_block_editor, $post_type ) {
		if ( 'boomerang' === $post_type || 'boomerang_board' === $post_type ) {
			return false;
		}

		return true;
	}

	/**
	 * Create a settings page for the main plugin. Will hold our global settings.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		// Control core classes for avoid errors
		if ( class_exists( 'CSF' ) ) {
			$prefix = 'boomerang_global_options';

			\CSF::createOptions(
				$prefix,
				array(
					'menu_title'       => 'Settings',
					'menu_slug'        => 'settings',
					'menu_type'        => 'submenu',
					'menu_parent'      => 'edit.php?post_type=boomerang',
					'theme'            => 'light',
					'show_all_options' => false,
					'framework_title'  => 'Settings',
				)
			);

			\CSF::createSection(
				$prefix,
				array(
					'id'     => 'general',
					'title'  => 'General',
					'fields' => array(
						array(
							'id'    => 'disable_google_fonts',
							'type'  => 'switcher',
							'title' => esc_attr__( 'Disable Google Fonts', 'boomerang' ),
							'desc'  => esc_attr__(
								'We use Google Icons inside Boomerang. These icons are locally hosted and are therefore GDPR compliant. However, if you would like to disable these, click the button.',
								'boomerang'
							),
						),
						array(
							'id'    => 'disable_default_styles',
							'type'  => 'switcher',
							'title' => esc_attr__( 'Disable Boomerang\'s Own Styles', 'boomerang' ),
							'desc'  => esc_attr__(
								'Boomerang has a set of default styles. To disable these, and use your theme\'s native styles, click this.',
								'boomerang'
							),
						),
					),
				)
			);

			apply_filters( 'boomerang_global_settings_section_end', $prefix );
		}
	}

	/**
	 * Creates a settings metabox for our boards. Allows more granular control of individual boards.
	 *
	 * @return void
	 */
	public function add_board_metabox() {
		// Control core classes for avoid errors
		if ( class_exists( 'CSF' ) ) {
			$prefix = 'boomerang_board_options';

			\CSF::createMetabox(
				$prefix,
				array(
					'title'     => esc_html__( 'Board Settings', 'boomerang' ),
					'post_type' => 'boomerang_board',
				)
			);

			\CSF::createSection(
				$prefix,
				array(
					'id'     => 'boards',
					'title'  => 'General',
					'fields' => $this->general_settings(),
				)
			);

			\CSF::createSection(
				$prefix,
				array(
					'id'     => 'boards',
					'title'  => 'Labels',
					'fields' => $this->label_settings(),
				)
			);

			\CSF::createSection(
				$prefix,
				array(
					'id'     => 'boards',
					'title'  => 'Notifications',
					'fields' => array(
						array(
							'id'    => 'admin_email',
							'type'  => 'text',
							'title' => esc_html__( 'Admin Email', 'boomerang' ),
							'desc'  => esc_html__(
								'Enter an email address to send notifications when Boomerangs are created.',
								'boomerang'
							),
						),
						array(
							'id'    => 'send_email_new_boomerang',
							'type'  => 'switcher',
							'title' => esc_html__( 'Send New Boomerang Notification', 'boomerang' ),
						),
					),
				)
			);

			apply_filters( 'boomerang_board_settings_section_end', $prefix );
		}
	}

	/**
	 * Populate our General Settings array.
	 *
	 * @return array
	 */
	public function general_settings() {
		$settings = array();

		if ( ! empty( $_GET['post'] ) ) {
			$settings[] = array(
				'type'    => 'subheading',
				'style'   => 'success',
				'content' => sprintf(
					// translators: %s: ID of the current board
					esc_html__( 'Shortcode: [boomerang board="%s"]', 'boomerang' ),
					esc_attr( $_GET['post'] ),
				),
			);
		}

		$settings[] = array(
			'id'    => 'require_approval',
			'type'  => 'switcher',
			'title' => esc_html__( 'Require Approval', 'boomerang' ),
			'desc'  => esc_html__(
				'If turned on, new Boomerangs will be given the status of pending, and will need to be approved before publication.',
				'boomerang'
			),
		);
		$settings[] = array(
			'id'    => 'enable_comments',
			'type'  => 'switcher',
			'title' => esc_html__( 'Enable Comments', 'boomerang' ),
			'desc'  => esc_html__( 'This allows users to comment on individual Boomerangs.', 'boomerang' ),
		);
		$settings[] = array(
			'id'    => 'enable_tags',
			'type'  => 'switcher',
			'title' => esc_html__( 'Enable Tags', 'boomerang' ),
			'desc'  => esc_html__( 'Tags are a convenient way of grouping Boomerangs.', 'boomerang' ),
		);
		$settings[] = array(
			'id'    => 'enable_statuses',
			'type'  => 'switcher',
			'title' => esc_html__( 'Enable Statuses', 'boomerang' ),
			'desc'  => esc_html__( 'Statuses may be helpful for organising Boomerang priority.', 'boomerang' ),
		);
		$settings[] = array(
			'id'    => 'enable_votes',
			'type'  => 'switcher',
			'title' => esc_html__( 'Enable Votes', 'boomerang' ),
			'desc'  => esc_html__( 'This allows users to vote on individual Boomerangs.', 'boomerang' ),
		);
		$settings[] = array(
			'id'    => 'enable_downvoting',
			'type'  => 'switcher',
			'title' => esc_html__( 'Enable Downvoting', 'boomerang' ),
			'desc'  => esc_html__(
				'Downvoting allows users to register disapproval for a Boomerang rather than simply a neutral opinion.',
				'boomerang'
			),
		);
		$settings[] = array(
			'id'    => 'show_title',
			'type'  => 'switcher',
			'title' => esc_html__( 'Show Board Title', 'boomerang' ),
			'desc'  => esc_html__(
				'Show the board title in the archive view. If using as a shortcode, you may create your own heading instead.',
				'boomerang'
			),

		);
		$settings[] = array(
			'id'    => 'enable_image',
			'type'  => 'switcher',
			'title' => esc_html__( 'Enable Featured Image', 'boomerang' ),
			'desc'  => esc_html__(
				'This allows users to upload a picture that helps represent a Boomerang.',
				'boomerang'
			),
		);
		$settings[] = array(
			'id'    => 'show_date',
			'type'  => 'switcher',
			'title' => esc_html__( 'Show Published Date', 'boomerang' ),
			'desc'  => esc_html__( 'This displays the date the Boomerang was created.', 'boomerang' ),
		);
		$settings[] = array(
			'id'    => 'show_friendly_date',
			'type'  => 'switcher',
			'title' => esc_html__( 'Show Friendly Dates', 'boomerang' ),
			'desc'  => esc_html__( 'Shows the publication date in a friendly way.', 'boomerang' ),
		);
		$settings[] = array(
			'id'    => 'show_author',
			'type'  => 'switcher',
			'title' => esc_html__( 'Show Author', 'boomerang' ),
			'desc'  => esc_html__( 'This displays the details of the user who created the Boomerangs.', 'boomerang' ),
		);
		$settings[] = array(
			'id'         => 'show_author_avatar',
			'type'       => 'switcher',
			'title'      => esc_html__( 'Show Author\'s Avatar', 'boomerang' ),
			'desc'       => esc_html__(
				'Shows the profile picture of the author next to the author\'s username.',
				'boomerang'
			),
			'dependency' => array( 'show_author', '==', 'true' ),
		);
		$settings[] = array(
			'id'    => 'show_filters',
			'type'  => 'switcher',
			'title' => esc_html__( 'Show Filters', 'boomerang' ),
			'desc'  => esc_html__(
				'Show a set of filters on a board directory to assist users to find Boomerangs.',
				'boomerang'
			),
		);
		$settings[] = array(
			'id'    => 'enable_honeypot',
			'type'  => 'switcher',
			'title' => esc_html__( 'Enable Honeypot', 'boomerang' ),
			'desc'  => esc_html__(
				'Adds a honeypot to the form, to block a large amount of spam.',
				'boomerang'
			),
		);
		$settings[] = array(
			'id'     => 'container_width',
			'type'   => 'dimensions',
			'height' => false,
			'output' => 'string',
			'title'  => esc_html__( 'Container Width', 'boomerang' ),
			'desc'   => esc_html__(
				'Use this to match the width of Boomerang content with that of your theme.',
				'boomerang'
			),
		);

		return apply_filters( 'boomerang_board_general_settings', $settings );
	}

	/**
	 * Populate our Label Settings array.
	 *
	 * @return array
	 */
	public function label_settings() {
		$settings = array();

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			$settings[] = array(
				'id'      => 'label_form_heading',
				'type'    => 'text',
				'default' => '',
				'placeholder' => 'Suggest a feature',
				'title'   => esc_html__( 'A heading for the top of your form', 'boomerang' ),
			);
			$settings[] = array(
				'id'      => 'label_form_subheading',
				'type'    => 'text',
				'default' => '',
				'placeholder' => 'What can we do to improve our product?',
				'title'   => esc_html__( 'A sub-heading for the top of your form', 'boomerang' ),
			);
		}

		$settings[] = array(
			'id'      => 'label_title',
			'type'    => 'text',
			'default' => 'Title',
			'title'   => esc_html__( 'Label For Title Input', 'boomerang' ),
		);
		$settings[] = array(
			'id'      => 'label_content',
			'type'    => 'text',
			'default' => 'Content',
			'title'   => esc_html__( 'Label For Content Input', 'boomerang' ),
		);
		$settings[] = array(
			'id'      => 'label_tags',
			'type'    => 'text',
			'default' => 'Tags',
			'title'   => esc_html__( 'Label For Tags Input', 'boomerang' ),
		);
		$settings[] = array(
			'id'      => 'label_submit',
			'type'    => 'text',
			'default' => 'Submit',
			'title'   => esc_html__( 'Label For Submit Button', 'boomerang' ),
		);

		return apply_filters( 'boomerang_board_label_settings', $settings );
	}

	/**
	 * Adds a metabox within each Boomerang to choose which board it belongs to.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function add_boomerang_parent_metabox( $post ) {
		add_meta_box(
			'boomerang-board',
			__( 'Board' ),
			array( $this, 'output_boomerang_parent_metabox' ),
			'boomerang',
			'side',
			'default'
		);
	}

	/**
	 * Callback for metabox.
	 *
	 * @return void
	 * @see add_boomerang_parent_metabox()
	 *
	 */
	public function output_boomerang_parent_metabox() {
		global $post;

		$pages = wp_dropdown_pages(
			array(
				'post_type'        => 'boomerang_board',
				'selected'         => esc_attr( $post->post_parent ),
				'name'             => 'parent_id',
				'show_option_none' => esc_html__( 'None' ),
				'sort_column'      => 'menu_order, post_title',
				'echo'             => 0,
			)
		);

		if ( ! empty( $pages ) ) {
			echo $pages; // phpcs:ignore -- rendered via WordPress function.
		}
	}

	/**
	 * Add a column to the Boomerang post list table, to show the parent board for each Boomerang.
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function add_boomerang_board_column( $columns ) {
		$columns['board'] = 'Board';

		return $columns;
	}

	/**
	 * Position the column before the date.
	 *
	 * @param $columns
	 *
	 * @return array
	 * @see add_boomerang_board_column()
	 *
	 */
	public function position_boomerang_board_column( $columns ) {
		$n_columns = array();
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$n_columns['board'] = 'board';
			}
			$n_columns[ $key ] = $value;
		}

		return $n_columns;
	}

	/**
	 * Populate the parent board column.
	 *
	 * @param $column_id
	 * @param $post_id
	 *
	 * @return void
	 * @see add_boomerang_board_column()
	 *
	 */
	public function populate_boomerang_board_column( $column_id, $post_id ) {
		switch ( $column_id ) {
			case 'board':
				$ancestors     = get_ancestors( $post_id, 'subject', 'post_type' );
				$post_ancestor = end( $ancestors );
				if ( $post_ancestor != 0 ) {
					echo '<a href="' . get_edit_post_link( $post_ancestor ) . '">' . get_the_title( $post_ancestor ) . '</a>';
				} else {
					echo '-';
				}
				break;
		}
	}

	/**
	 * Adds new fields to the Boomerang Status Center.
	 *
	 * @param $term
	 *
	 * @return void
	 */
	public function add_category_fields__premium_only( $term ) {
		if ( current_filter() === 'boomerang_status_edit_form_fields' ) {
			$color = get_term_meta( $term->term_id, 'color', true );
			?>
			<tr class="form-field">
				<th scope="row">
					<label for="term_fields[color]"><?php esc_html_e( 'Color', 'boomerang' ); ?></label>
				</th>
				<td>
					<input class="boomerang-color-picker" type="text" value="<?php echo esc_html( $color ); ?>" id="term_fields[color]" name="term_fields[color]"><br/>
					<span class="description"><?php esc_html_e( 'A unique color for this Boomerang status', 'boomerang' ); ?></span>
					<input type="hidden" name="term_fields[background_color]" id="background-color">
				</td>
			</tr>
			<?php
		} elseif ( current_filter() === 'boomerang_status_add_form_fields' ) {
			?>
			<div class="form-field">
				<label for="term_fields[color]"><?php esc_html_e( 'Color', 'boomerang' ); ?></label>
				<input class="boomerang-color-picker" type="text" value="" id="term_fields[color]" name="term_fields[color]">
				<p class="description"><?php esc_html_e( 'A unique color for this Boomerang status', 'boomerang' ); ?></p>
				<input type="hidden" name="term_fields[background_color]" id="background-color">
			</div>
			<?php
		}
	}

	/**
	 * Sanitize and save our custom Boomerang Status fields.
	 *
	 * @param $term_id
	 *
	 * @return void
	 */
	public function save_category_fields__premium_only( $term_id ) {
		if ( ! isset( $_POST['term_fields'] ) ) {
			return;
		}

		foreach ( $_POST['term_fields'] as $key => $value ) {
			update_term_meta( $term_id, $key, sanitize_text_field( $value ) );
		}
	}
}
