<?php

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang admin email notifications functionality.
 */
class Boomerang_Admin_Email_Notifications {
	/**
	 * Define the admin email notifications functionality of the plugin.
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
		add_action( 'boomerang_board_notification_settings', array( $this, 'add_notification_settings' ) );
	}

	/**
	 * Adds additional sections to the Boomerang Board settings.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function add_notification_settings( $settings ) {
		return $settings;
	}
}
$admin_email_notifications = new Boomerang_Admin_Email_Notifications();
