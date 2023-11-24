<?php
/**
 * Our main class that kicks everything off.
 */
namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles displays and hooks for the Boomerang custom post type(s).
 */
class Boomerang {
	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		// Require our board function file.
		require BOOMERANG_PATH . '/inc/boomerang-board-functions.php';

		// Require our boomerang function file.
		require BOOMERANG_PATH . '/inc/boomerang-functions.php';

		// Require our template file.
		require BOOMERANG_PATH . '/inc/boomerang-templates.php';

		// Register and populate shortcodes.
		require BOOMERANG_PATH . '/inc/boomerang-shortcodes.php';

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			// Pro version filters
			require BOOMERANG_PATH . '/inc/boomerang-pro-filters.php';
		}

		$this->init_hooks();

		// Do this early, so that CSF can boot up.
		$this->initialise_admin();

		// Block stuff.
		$this->initialise_block();
	}

	public function init_hooks() {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'initialise_front_end' ) );
		add_action( 'init', array( $this, 'initialise_voting' ) );
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

		$boomerang_frontend = new Boomerang_Frontend();
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

		$boomerang_admin = new Boomerang_Admin();
	}

	/**
	 * Boot up our block.
	 *
	 * @return void
	 */
	public function initialise_block(  ) {
		if ( ! class_exists( 'Boomerang_Block' ) ) {
			require_once BOOMERANG_PATH . '/admin/inc/classes/class-boomerang-block.php';
		}

		$boomerang_block = new Boomerang_Block();
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

		$boomerang_cpt = new Boomerang_CPT_Helper();

		$boomerang_cpt->register_post_types();
	}

	/**
	 * Boot up our votes system.
	 *
	 * @return void
	 */
	public function initialise_voting(  ) {
		if ( ! class_exists( 'Boomerang_Votes' ) ) {
			require_once BOOMERANG_PATH . '/inc/classes/class-boomerang-votes.php';
		}

		$boomerang_voting = new Boomerang_Votes();
	}

}
