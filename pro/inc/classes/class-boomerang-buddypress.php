<?php
/**
 * BuddyPress/BuddyBoss Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang BuddyPress functionality.
 */
class Boomerang_BuddyPress {
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
		add_action( 'bp_setup_nav', array( $this, 'boomerang_nav_item' ) );

		add_filter( 'bp_get_template_stack', array( $this, 'bp_plugin_add_template_stack' ) );
	}

	public function boomerang_nav_item() {
		bp_core_new_nav_item(
			array(
				'name'            => ucwords( get_plural_global() ),
				'slug'            => boomerang_get_base(),
				'screen_function' => $this->render_screen(),
			)
		);
	}

	public function bp_plugin_add_template_stack( $templates ) {
		$templates[] = BOOMERANG_PATH . 'pro/templates/';

		return $templates;
	}

	/**
	 * Create a view for the BuddyBoss Profile Space screen.
	 */
	public function render_screen() {
		do_action( 'boomerang_render_screen_start' );

		add_action( 'bp_template_content', array( $this, 'render_screen_content' ) );

		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	public function render_screen_content() {
		bp_get_template_part( 'bp-boomerang' );
	}
}
