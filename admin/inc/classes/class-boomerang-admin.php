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
		require_once BOOMERANG_PATH . '/admin/fields/better-accordion.php';

		$this->init_hooks();

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			require_once BOOMERANG_PATH . 'admin/pro/classes/class-boomerang-customizer.php';
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
		add_action( 'admin_notices', array( $this, 'add_upsell' ) );

		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
		add_filter( 'manage_boomerang_posts_columns', array( $this, 'add_boomerang_board_column' ) );
		add_filter( 'manage_boomerang_posts_columns', array( $this, 'position_boomerang_board_column' ) );
		add_filter( 'manage_posts_custom_column', array( $this, 'populate_boomerang_board_column' ), 10, 2 );

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			require BOOMERANG_PATH . 'admin/pro/boomerang-pro-admin-filters.php';

			add_action( 'boomerang_status_add_form_fields', array( $this, 'add_category_fields__premium_only' ), 10, 2 );
			add_action( 'boomerang_status_edit_form_fields', array( $this, 'add_category_fields__premium_only' ), 10, 2 );
			add_action( 'edited_boomerang_status', array( $this, 'save_category_fields__premium_only' ), 10, 2 );
			add_action( 'create_boomerang_status', array( $this, 'save_category_fields__premium_only' ), 10, 2 );

			// Migration Tools
			require_once BOOMERANG_PATH . 'admin/pro/classes/class-boomerang-migrator.php';
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
				wp_enqueue_script( 'boomerang', BOOMERANG_URL . 'admin/assets/js/boomerang.js', array( 'wp-color-picker', 'jquery-ui-droppable' ), BOOMERANG_VERSION, true );

				if ( boo_fs()->can_use_premium_code__premium_only() ) {
					wp_enqueue_script( 'boomerang-pro', BOOMERANG_URL . 'admin/pro/assets/js/boomerang-pro.js', array(), BOOMERANG_VERSION, true );

				}
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

			if ( 'boomerang_page_boomerang-contact' === $current_screen->base || 'boomerang_page_boomerang-pricing' === $current_screen->base ) {
				$drop = 'drop';
			} else {
				$drop = '';
			}

			if ( 'boomerang' === $current_screen->post_type || 'boomerang_board' === $current_screen->post_type ) : ?>
				<div class="boomerang-admin-header <?php echo esc_attr( $drop ); ?>">
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
	 * Add an upsell banner to our admin pages. Ensures that we don't spam any non-Boomerang page as per WordPress guidelines.
	 *
	 * @return void
	 */
	public function add_upsell() {
		if ( boo_fs()->is_paying() ) {
			return;
		}

		/**
		 * Check whether the get_current_screen function exists
		 * because it is loaded only after 'admin_init' hook.
		 */
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();

			if ( ( 'boomerang' === $current_screen->post_type || 'boomerang_board' === $current_screen->post_type ) && 'boomerang_page_boomerang-pricing' !== $current_screen->base ) :
				?>
		<a href="<?php echo esc_url( boo_fs()->get_upgrade_url() ); ?>" class="upsell">
			<div class="banner">
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="24" viewBox="0 0 18 24" fill="none">
					<path d="M13.8766 0.102022C14.0234 0.187129 14.137 0.319507 14.1988 0.477528C14.2607 0.635549 14.2671 0.809874 14.2171 0.972022L11.5156 9.75002H16.5001C16.6465 9.74996 16.7898 9.7928 16.9122 9.87325C17.0347 9.95369 17.1308 10.0682 17.1889 10.2027C17.247 10.3372 17.2644 10.4857 17.239 10.63C17.2136 10.7743 17.1465 10.9079 17.0461 11.0145L5.04605 23.7645C4.92994 23.888 4.77544 23.9685 4.60772 23.9929C4.43999 24.0173 4.26895 23.9842 4.12246 23.8989C3.97597 23.8137 3.86268 23.6813 3.80104 23.5235C3.73941 23.3656 3.73307 23.1915 3.78305 23.0295L6.48456 14.25H1.50006C1.35357 14.2501 1.21028 14.2072 1.08786 14.1268C0.965449 14.0463 0.869271 13.9318 0.811203 13.7973C0.753135 13.6629 0.735719 13.5143 0.761105 13.37C0.786491 13.2258 0.853568 13.0921 0.954055 12.9855L12.9541 0.235522C13.07 0.11222 13.2243 0.0317747 13.3918 0.00726457C13.5593 -0.0172455 13.7301 0.0156215 13.8766 0.100522V0.102022Z" fill="black"/>
				</svg>
				<h2>Early Bird Offer</h2>
				<p><strong>Lifetime</strong> Access to Boomerang Pro For Just <strong>$59.99</strong></p>
				<p><strong>Only 50 licences left!</strong></p>
				<div class="website">
					<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-155.5t86-127Q252-817 325-848.5T480-880q83 0 155.5 31.5t127 86q54.5 54.5 86 127T880-480q0 82-31.5 155t-86 127.5q-54.5 54.5-127 86T480-80Zm0-82q26-36 45-75t31-83H404q12 44 31 83t45 75Zm-104-16q-18-33-31.5-68.5T322-320H204q29 50 72.5 87t99.5 55Zm208 0q56-18 99.5-55t72.5-87H638q-9 38-22.5 73.5T584-178ZM170-400h136q-3-20-4.5-39.5T300-480q0-21 1.5-40.5T306-560H170q-5 20-7.5 39.5T160-480q0 21 2.5 40.5T170-400Zm216 0h188q3-20 4.5-39.5T580-480q0-21-1.5-40.5T574-560H386q-3 20-4.5 39.5T380-480q0 21 1.5 40.5T386-400Zm268 0h136q5-20 7.5-39.5T800-480q0-21-2.5-40.5T790-560H654q3 20 4.5 39.5T660-480q0 21-1.5 40.5T654-400Zm-16-240h118q-29-50-72.5-87T584-782q18 33 31.5 68.5T638-640Zm-234 0h152q-12-44-31-83t-45-75q-26 36-45 75t-31 83Zm-200 0h118q9-38 22.5-73.5T376-782q-56 18-99.5 55T204-640Z"/></svg>
					<p>boomerangwp.com</p>
				</div>

			</div>
		</a>
				<?php
		endif;
		}
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
					'menu_title'         => 'Settings',
					'menu_slug'          => 'settings',
					'menu_type'          => 'submenu',
					'menu_parent'        => 'edit.php?post_type=boomerang',
					'theme'              => 'light',
					'show_all_options'   => false,
					'framework_title'    => 'Boomerang Global Settings',
					'show_search'        => false,
					'show_reset_all'     => false,
					'show_reset_section' => false,
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

			do_action( 'boomerang_global_settings_section_end', $prefix );
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
					'id' => 'general',
					'title'  => 'General',
					'fields' => $this->general_settings(),
				)
			);

			\CSF::createSection(
				$prefix,
				array(
					'id' => 'labels',
					'title'  => 'Labels',
					'fields' => $this->label_settings(),
				)
			);

			\CSF::createSection(
				$prefix,
				array(
					'id' => 'notifications',
					'title'  => 'Notifications',
					'fields' => $this->notification_settings(),
				)
			);

			do_action( 'boomerang_board_settings_section_end', $prefix );
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
					esc_attr( $_GET['post'] )
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

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			$settings[] = array(
				'id'    => 'enable_edit',
				'type'  => 'switcher',
				'title' => esc_html__( 'Enable Editing', 'boomerang' ),
				'desc'  => esc_html__( 'This allows users to edit their Boomerangs after submitting.', 'boomerang' ),
			);

			$settings[] = array(
				'id'    => 'enable_mark_as_bug',
				'type'  => 'switcher',
				'title' => esc_html__( 'Enable Bug Report Management', 'boomerang' ),
				'desc'  => esc_html__( 'Boomerang is not built as a bug reporting tool, but inevitably people will report bugs using Boomerang. Switch this on if your industry in one that may receive bug reports, and you want them handled smartly.', 'boomerang' ),
			);
		}

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

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			$settings[] = array(
				'id'    => 'enable_bulk_votes',
				'type'  => 'switcher',
				'title' => esc_html__( 'Enable Bulk Votes', 'boomerang' ),
				'desc'  => esc_html__( 'This allows managers to vote multiple times, for debugging purposes.', 'boomerang' ),
				'dependency' => array( 'enable_votes', '==', 'true' ),
			);
		}

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

		$settings[] = array(
			'id'      => 'label_singular',
			'type'    => 'text',
			'default' => 'feature request',
			'title'   => esc_html__( 'Boomerang Singular Name', 'boomerang' ),
			'desc'    => esc_html__(
				'Choose what you want to call a single Boomerang. We suggest using lowercase. You may see this used in various places around the plugin.',
				'boomerang'
			),
		);
		$settings[] = array(
			'id'      => 'label_plural',
			'type'    => 'text',
			'default' => 'feature requests',
			'title'   => esc_html__( 'Boomerang Plural Name', 'boomerang' ),
			'desc'    => esc_html__(
				'Choose what you want to call a group of Boomerangs. We suggest using lowercase. You may see this used in various places around the plugin.',
				'boomerang'
			),
		);

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			$settings[] = array(
				'id'          => 'label_form_heading',
				'type'        => 'text',
				'default'     => '',
				'placeholder' => 'Suggest a feature',
				'title'       => esc_html__( 'A heading for the top of your form', 'boomerang' ),
			);
			$settings[] = array(
				'id'          => 'label_form_subheading',
				'type'        => 'text',
				'default'     => '',
				'placeholder' => 'What can we do to improve our product?',
				'title'       => esc_html__( 'A sub-heading for the top of your form', 'boomerang' ),
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

		$settings[] = array(
			'id'      => 'message_already_voted',
			'type'    => 'text',
			'default' => 'You have already voted',
			'title'   => esc_html__( 'Message if user has already voted', 'boomerang' ),
		);

		$settings[] = array(
			'id'      => 'message_vote_recorded',
			'type'    => 'text',
			'default' => 'Thank you for your vote',
			'title'   => esc_html__( 'Message if user has successfully voted', 'boomerang' ),
		);

		return apply_filters( 'boomerang_board_label_settings', $settings );
	}

	/**
	 * Populate our Notification Settings array.
	 *
	 * @return array
	 */
	public function notification_settings() {
		$settings = array();

		$settings[] = array(
			'id'         => 'notifications',
			'type'       => 'better_accordion',
			'class'      => 'notification-accordions',
			'accordions' => $this->notification_settings_accordions(),
		);

		return apply_filters( 'boomerang_board_notification_settings', $settings );
	}

	/**
	 * Populate our Notification Settings Accordions array.
	 *
	 * @return array
	 */
	public function notification_settings_accordions() {
		$accordions = array();

		$accordions[] = array(
			'id'     => 'send_new_boomerang_email',
			'title'  => 'Admin Notification',
			'fields' => array(
				array(
					'id'    => 'enabled',
					'type'  => 'switcher',
					'title' => esc_html__( 'Send New Boomerang Notification', 'boomerang' ),
				),
				array(
					'id'    => 'recipient',
					'type'  => 'text',
					'title' => esc_html__( 'Admin Email', 'boomerang' ),
					'desc'  => esc_html__(
						'Enter an email address to send notifications when Boomerangs are created.',
						'boomerang'
					),
				),
				array(
					'id'      => 'placeholders',
					'type'    => 'content',
					'title'   => esc_html__( 'Placeholders', 'boomerang' ),
					'desc'    => esc_html__(
						'Cut and paste any placeholder into the boxes below. Make sure the double brackets are also entered. These will then be replaced in any notification sent with live data.',
						'boomerang'
					),
					'content' => wp_kses_post( $this->get_placeholder_box() ),
				),
				array(
					'id'         => 'subject',
					'type'       => 'textarea',
					'title'      => esc_html__( 'Email Subject', 'boomerang' ),
					'attributes' => array(
						'rows'  => 3,
						'style' => 'min-height: 0;',
					),
				),
				array(
					'id'            => 'content',
					'type'          => 'wp_editor',
					'title'         => esc_html__( 'Email Content', 'boomerang' ),
					'quicktags'     => false,
					'media_buttons' => false,
				),
			),
		);

		return apply_filters( 'boomerang_board_notification_settings_accordions', $accordions );
	}

	/**
	 * Render a box to hold placeholders.
	 *
	 * @return string
	 */
	public function get_placeholder_box() {
		$placeholders = array(
			'{{title}}',
			'{{board}}',
			'{{link}}',
		);

		/**
		 * Filters the placeholder list. This should match the placeholder array in the main notifications class.
		 *
		 * @see Boomerang_Email_Notifications::populate_placeholders()
		 */
		$placeholders = apply_filters( 'boomerang_notification_placeholders', $placeholders );

		$placeholder_string = '<div class="boomerang-notification-placeholder-container">';

		foreach ( $placeholders as $placeholder ) {
			$placeholder_string .= '<span>' . $placeholder . '</span>';
		}

		$placeholder_string .= '</div>';

		$placeholder_string .= '<div class="csf-desc-text">' . __( 'Cut and paste any placeholder into the boxes below. Make sure the double brackets are also entered. These will then be replaced in any notification sent with live data.', 'boomerang' ) . '</div>';

		return $placeholder_string;
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
		if ( 'board' === $column_id ) {
			$ancestors     = get_ancestors( $post_id, 'subject', 'post_type' );
			$post_ancestor = end( $ancestors );
			if ( 0 != $post_ancestor ) {
				echo '<a href="' . get_edit_post_link( $post_ancestor ) . '">' . get_the_title( $post_ancestor ) . '</a>';
			} else {
				echo '-';
			}
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
