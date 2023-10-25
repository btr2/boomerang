<?php

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
		require_once BOOMERANG_PATH . 'admin/inc/classes/class-boomerang-customizer.php';

		$this->init_hooks();
		$this->init_customizer();
	}

	/**
	 * Decouple our hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		// add_action( 'admin_init', array( $this, 'init_customizer' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueues' ) );
		add_action( 'in_admin_header', array( $this, 'add_custom_header' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
		add_action( 'csf_loaded', array( $this, 'add_settings_page' ) );
		add_action( 'csf_loaded', array( $this, 'add_board_metabox' ) );
		add_action( 'add_meta_boxes_boomerang', array( $this, 'add_boomerang_parent_metabox' ), 10, 2 );
		add_filter( 'manage_boomerang_posts_columns', array( $this, 'add_boomerang_board_column' ) );
		add_filter( 'manage_boomerang_posts_columns', array( $this, 'position_boomerang_board_column' ) );
		add_filter( 'manage_posts_custom_column', array( $this, 'populate_boomerang_board_column' ), 10, 2 );
	}

	public function init_customizer() {
		$boomerang_customizer = new Boomerang_Customizer();
	}

	public function admin_enqueues() {
		/**
		 * Check whether the get_current_screen function exists
		 * because it is loaded only after 'admin_init' hook.
		 */
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();

			if ( 'boomerang' === $current_screen->post_type || 'boomerang_board' === $current_screen->post_type ) {
				wp_enqueue_style( 'boomerang', BOOMERANG_URL . 'admin/assets/css/boomerang-admin.css', null, BOOMERANG_VERSION );
				// wp_enqueue_script(
				// 	'boomerang',
				// 	BOOMERANG_URL . 'admin/assets/js/boomerang-admin.js',
				// 	array( 'jquery' ),
				// 	BOOMERANG_VERSION,
				// 	true
				// );
			}
		}
	}

	/**
	 * Add a smart header to our admin pages.
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
				<div class="boomerang-admin-header">
					<div class="boomerang-title">
						<img class="boomerang-logo" src="<?php echo esc_url( BOOMERANG_URL . 'admin/assets/images/logo-white.png' ); ?>" alt="Boomerang Logo">
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

			CSF::createOptions(
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

			CSF::createSection(
				$prefix,
				array(
					'id'     => 'general',
					'title'  => 'General',
					'fields' => array(
						array(
							'id'    => 'disable_google_fonts',
							'type'  => 'switcher',
							'title' => 'Disable Google Fonts',
							'desc'  => 'We use Google Icons inside Boomerang. If you would like to disable these, click the button.',
						),
					),
				)
			);
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

			//
			// Set a unique slug-like ID
			$prefix = 'boomerang_board_options';

			//
			// Create a metabox
			CSF::createMetabox(
				$prefix,
				array(
					'title'     => 'Board Settings',
					'post_type' => 'boomerang_board',
				)
			);

			//
			// Create a section
			CSF::createSection(
				$prefix,
				array(
					'id'     => 'boards',
					'type'   => 'repeater',
					'fields' => array(
						array(
							'id'    => 'enable_comments',
							'type'  => 'switcher',
							'title' => 'Enable Comments',
						),
						array(
							'id'    => 'enable_tags',
							'type'  => 'switcher',
							'title' => 'Enable Tags',
						),
						array(
							'id'    => 'enable_attachments',
							'type'  => 'switcher',
							'title' => 'Enable Attachments',
						),
						array(
							'id'    => 'enable_thumbnails',
							'type'  => 'switcher',
							'title' => 'Enable Featured Images',
						),
						array(
							'id'    => 'enable_votes',
							'type'  => 'switcher',
							'title' => 'Enable Votes',
						),
						array(
							'id'    => 'show_title',
							'type'  => 'switcher',
							'title' => 'Show Title',
						),
						array(
							'id'    => 'show_date',
							'type'  => 'switcher',
							'title' => 'Show Published Date',
						),
						array(
							'id'    => 'show_author',
							'type'  => 'switcher',
							'title' => 'Show Author',
						),
					),
				)
			);
		}

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
	 * @see add_boomerang_parent_metabox()
	 *
	 * @return void
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
	 * @see add_boomerang_board_column()
	 *
	 * @param $columns
	 *
	 * @return array
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
	 * @see add_boomerang_board_column()
	 *
	 * @param $column_id
	 * @param $post_id
	 *
	 * @return void
	 */
	public function populate_boomerang_board_column ( $column_id, $post_id ) {
		switch( $column_id ) {
			case 'board':
				$ancestors = get_ancestors($post_id, 'subject', 'post_type');
				$post_ancestor = end($ancestors);
				if ($post_ancestor != 0) {
					echo '<a href="' . get_edit_post_link($post_ancestor) . '">' . get_the_title($post_ancestor) . '</a>';
				} else {
					echo '-';
				}
				break;
		}
	}
}
