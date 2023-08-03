<?php
/**
 *
 *   Plugin Name: Boomerang
 *   Plugin URI: https://www.bouncingsprout.com/
 *   Description: Collect feature requests from your users.
 *   Author: Ben Roberts
 *   Text Domain: boomerang
 *   Domain Path: /languages
 *   Version: 1.0.0
 */

define( 'BOOMERANG_PATH', plugin_dir_path( __FILE__ ) );
define( 'BOOMERANG_URL', plugin_dir_url( __FILE__ ) );
define( 'BOOMERANG_BASENAME', plugin_basename( __FILE__ ) );
define( 'BOOMERANG_VERSION', boomerang_get_version() );

/**
 * Get the plugin's version number.
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

/**
 * Start the engines, Captain...
 */
function boomerang_init() {
	require BOOMERANG_PATH . '/inc/classes/class-boomerang-boomerang.php';

	$boomerang = new Boomerang_Boomerang();
}
add_action( 'init', 'boomerang_init' );

/**
 * Tasks to run on plugin activation.
 */
function activate_boomerang() {

	if ( ! class_exists( 'Boomerang_CPT_Helper' ) ) {
		require_once BOOMERANG_PATH . '/inc/classes/class-boomerang-cpt-helper.php';
	}

	$cpt = new Boomerang_CPT_Helper();
	$cpt->register_post_types();

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'activate_boomerang' );
