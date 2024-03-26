<?php

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang admin email notifications functionality.
 */
class Boomerang_Email_Notifications {
	protected $placeholders;

	/**
	 * Define the admin email notifications functionality of the plugin.
	 */
	public function __construct() {
		$this->populate_placeholders();
		$this->init_hooks();
	}

	/**
	 * Decouple our hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'boomerang_new_boomerang', array( $this, 'send_new_boomerang_email' ) );
	}

	/**
	 * Checks to see if a notification is active for a given Board.
	 *
	 * @param $notification
	 * @param $board_id
	 *
	 * @return bool
	 */
	public function is_enabled( $notification, $board_id ) {
		$meta = get_post_meta( $board_id, 'boomerang_board_options', true );

		if ( $meta['notifications'][ $notification ]['enabled'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all the data for this notification.
	 *
	 * @param $notification
	 * @param $board_id
	 *
	 * @return mixed
	 */
	public function get_notification(  $notification, $board_id  ) {
		$meta = get_post_meta( $board_id, 'boomerang_board_options', true );

		return $meta['notifications'][ $notification ];
	}

	/**
	 * Populate our placeholders, where necessary adding values based on a current Boomerang.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function populate_placeholders( $post = false ) {
		$placeholders = array(
			'{{title}}'  => $post->post_title ?? '',
			'{{board}}'  => $post->post_parent ?? '',
			'{{link}}'   => get_permalink( $post ) ?? '',
		);

		$this->placeholders = $placeholders;
	}

	/**
	 * Gte our placeholders.
	 *
	 * @return mixed
	 */
	public function get_placeholders() {
		return $this->placeholders;
	}

	/**
	 * Get email headers.
	 *
	 * @return string[]
	 */
	public function get_headers() {
		return array( 'Content-Type: text/html; charset=UTF-8' );
	}

	/**
	 * Get the notification subject.
	 *
	 * @param $notification
	 * @param $post
	 *
	 * @return array|string|string[]
	 */
	public function get_subject( $notification, $post ) {
		$raw_subject = $notification['subject'];

		return $this->format_text( $raw_subject, $post );
	}

	/**
	 * Get the notification content.
	 *
	 * @param $notification
	 * @param $post
	 *
	 * @return array|string|string[]
	 */
	public function get_content( $notification, $post ) {
		$raw_subject = $notification['content'];

		return $this->format_text( $raw_subject, $post );
	}

	/**
	 * Get a formatted text, where placeholders are replaced with valid values.
	 *
	 * @param $text
	 * @param $post
	 *
	 * @return array|string|string[]
	 */
	public function format_text( $text, $post ) {
		$this->populate_placeholders( $post );
		$placeholders = $this->get_placeholders();

		// TODO Possible Debug?
		error_log( pathinfo(__FILE__ )['dirname'] . '/' . pathinfo(__FILE__ )['basename'] );
		error_log( print_r($placeholders, true) );


		$text = strtr( $text, $placeholders );

		$text = str_replace( 'http://http://', 'http://', $text);
		$text = str_replace( 'https://https://', 'https://', $text);

		return $text;
	}

	/**
	 * Sends email when a new Boomerang is created.
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function send_new_boomerang_email( $post_id = false ) {
		// Get this Boomerang's board.
		$post = get_post( $post_id );

		if ( ! $this->is_enabled( 'send_new_boomerang_email', $post->post_parent ) ) {
			return;
		}

		$notification = $this->get_notification( 'send_new_boomerang_email', $post->post_parent );
		$subject      = $this->get_subject( $notification, $post );
		$content      = $this->get_content( $notification, $post );
		$headers      = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $notification['recipient'], $subject, $content, $this->get_headers() );
	}
}
$email_notifications = new Boomerang_Email_Notifications();
