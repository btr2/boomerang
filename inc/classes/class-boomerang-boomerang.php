<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles displays and hooks for the Boomerang custom post type(s).
 */
class Boomerang_Boomerang {
	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->init_hooks();
		$this->register_cpt();
		$this->initialise_front_end();
		$this->initialise_admin();
	}

	public function init_hooks() {

	}

	/**
	 * Boot up all frontend functionality, such as boomerang submission.
	 *
	 * @return void
	 */
	public function initialise_front_end(  ) {
		if ( ! class_exists( 'Boomerang_Frontend' ) ) {
			require_once BOOMERANG_PATH . '/inc/classes/class-boomerang-frontend.php';
		}

		$frontend = new Boomerang_Frontend();
	}

	/**
	 * Boot up all admin functionality.
	 *
	 * @return void
	 */
	public function initialise_admin(  ) {
		if ( ! class_exists( 'Boomerang_Admin' ) ) {
			require_once BOOMERANG_PATH . '/admin/inc/classes/class-boomerang-admin.php';
		}

		$admin = new Boomerang_Admin();
	}

	/**
	 * Load our CPT and taxonomies.
	 *
	 * @return void
	 */
	public function register_cpt(  ) {
		if ( ! class_exists( 'Boomerang_CPT_Helper' ) ) {
			require_once BOOMERANG_PATH . '/inc/classes/class-boomerang-cpt-helper.php';
		}

		$cpt = new Boomerang_CPT_Helper();

		$cpt->register_post_types();
	}

}
