<?php
/**
 * Plugin Name:         Boomerang - Feature Request Platform
 * Plugin URI:          https://www.bouncingsprout.com/
 * Description:         A slick, modern feature request and feedback platform for WordPress.
 * Version:             1.1.1
 * Requires at least:   5.2
 * Requires PHP:        7.0
 * Author:              Bouncingsprout Studio
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         boomerang
 * Domain Path:         /languages
 *
 * @fs_premium_only /pro, /admin/pro
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'boo_fs' ) ) {
	boo_fs()->set_basename( true, __FILE__ );
} else {
	// DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
	if ( ! function_exists( 'boo_fs' ) ) {
		// Create a helper function for easy SDK access.
		function boo_fs() {
			global $boo_fs;

			if ( ! isset( $boo_fs ) ) {
				// Include Freemius SDK.
				require_once dirname(__FILE__) . '/freemius/start.php';

				$boo_fs = fs_dynamic_init( array(
					'id'                  => '14215',
					'slug'                => 'boomerang',
					'premium_slug'        => 'boomerang-pro',
					'type'                => 'plugin',
					'public_key'          => 'pk_227b2104cb4396d666b43182e1861',
					'is_premium'          => true,
					'premium_suffix'      => 'Professional',
					// If your plugin is a serviceware, set this option to false.
					'has_premium_version' => true,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'menu'                => array(
						'slug'           => 'edit.php?post_type=boomerang',
						'support'        => false,
					),
				) );
			}

			return $boo_fs;
		}

		// Init Freemius.
		boo_fs();
		// Signal that SDK was initiated.
		do_action( 'boo_fs_loaded' );
	}

	define( 'BOOMERANG_PATH', plugin_dir_path( __FILE__ ) );
	define( 'BOOMERANG_URL', plugin_dir_url( __FILE__ ) );
	define( 'BOOMERANG_BASENAME', plugin_basename( __FILE__ ) );
	define( 'BOOMERANG_VERSION', '1.1.1' );

	/**
	 * Get the plugin's version number.
	 *
	 * @deprecated
	 *
	 * @return mixed
	 */
	function boomerang_get_version() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_data = get_plugin_data( __FILE__ );

		return $plugin_data['Version'];
	}

	if ( ! function_exists( 'boomerang_init' ) ) {
		/**
		 * Bootstrap plugin.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		function boomerang_init() {
			require_once BOOMERANG_PATH . 'vendor/codestar-framework/codestar-framework.php';
			require BOOMERANG_PATH . '/inc/classes/class-boomerang.php';

			do_action( 'boomerang_before_init' );
			$boomerang = new Boomerang();
			do_action( 'boomerang_after_init' );
		}
	}
	add_action( 'plugins_loaded', __NAMESPACE__ . '\boomerang_init' );

	/**
	 * Tasks to run on plugin activation.
	 */
	function boomerang_activate() {
		if ( ! class_exists( 'Boomerang_CPT_Helper' ) ) {
			require_once BOOMERANG_PATH . '/inc/classes/class-boomerang-cpt-helper.php';
		}

		$cpt = new Boomerang_CPT_Helper();
		$cpt->register_post_types();

		flush_rewrite_rules();
	}

	register_activation_hook( __FILE__, __NAMESPACE__ . '\boomerang_activate' );
}
