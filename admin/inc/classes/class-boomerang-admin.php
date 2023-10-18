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
		$this->init_hooks();
	}

	/**
	 * Decouple our hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueues' ) );
		add_action( 'in_admin_header', array( $this, 'add_custom_header' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
	}

	public function admin_enqueues() {
		/**
		 * Check whether the get_current_screen function exists
		 * because it is loaded only after 'admin_init' hook.
		 */
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();

			if ( 'boomerang' === $current_screen->post_type ) {
				wp_enqueue_style( 'boomerang', BOOMERANG_URL . 'admin/assets/css/boomerang-admin.css', null, BOOMERANG_VERSION );
				wp_enqueue_script(
					'boomerang',
					BOOMERANG_URL . 'admin/assets/js/boomerang-admin.js',
					array( 'jquery' ),
					BOOMERANG_VERSION,
					true
				);
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

			if ( 'boomerang' === $current_screen->post_type ) : ?>
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
		if ( 'boomerang' === $post_type ) {
			return false;
		}

		return true;
	}
}
