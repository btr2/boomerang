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

		if ( ! class_exists( 'Boomerang_Form' ) ) {
			require_once BOOMERANG_PATH . '/inc/classes/class-boomerang-form.php';
		}

		$boomerang_frontend = new Boomerang_Frontend();

		if ( boo_fs()->can_use_premium_code__premium_only() ) {
			// Pro version functionality
			require BOOMERANG_PATH . '/pro/boomerang-pro-filters-and-functions.php';
			if ( boomerang_get_google_recaptcha_keys__premium_only() ) {
				require BOOMERANG_PATH . '/pro/boomerang-google-captcha.php';
			}
			require BOOMERANG_PATH . '/pro/boomerang-guest-submissions.php';
			require BOOMERANG_PATH . '/pro/boomerang-custom-fields.php';
			require BOOMERANG_PATH . '/pro/boomerang-related-boomerangs.php';
			require BOOMERANG_PATH . '/pro/boomerang-suggested-boomerangs.php';
			require BOOMERANG_PATH . '/pro/boomerang-edit.php';
			require BOOMERANG_PATH . '/pro/boomerang-bug-reporting.php';
			require BOOMERANG_PATH . '/pro/boomerang-audit-log.php';

			if (is_plugin_active('wp-crowdfunding-pro/wp-crowdfunding-pro.php') || is_plugin_active('wp-crowdfunding/wp-crowdfunding.php')) {
				require BOOMERANG_PATH . '/pro/boomerang-wp-crowdfunding.php';
			}
		}
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
