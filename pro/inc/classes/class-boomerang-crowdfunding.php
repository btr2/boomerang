<?php

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang crowdfunding functionality.
 */
class  Boomerang_Crowdfunding {
	/**
	 * Define the crowdfunding functionality of the plugin.
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
		// add_action( 'set_object_terms', array( $this, 'status_change_notification' ), 10, 6 );
	}

	public function can_crowdfund() {
		if ( $this->is_wpcrowdfunding() || $this->is_ignitiondeck() ) {
			return true;
		}

		return false;
	}

	public function is_wpcrowdfunding(  ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'wp-crowdfunding-pro/wp-crowdfunding-pro.php' ) || is_plugin_active( 'wp-crowdfunding/wp-crowdfunding.php' ) ) {
			return true;
		}

		return false;
	}

	public function is_ignitiondeck(  ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'ignitiondeck/idf.php' ) ) {
			return true;
		}

		return false;
	}
}

