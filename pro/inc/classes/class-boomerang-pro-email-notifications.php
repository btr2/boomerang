<?php

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang admin email notifications functionality.
 */
class Boomerang_Pro_Email_Notifications extends Boomerang_Email_Notifications {
	protected $placeholders;

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
		add_action( 'set_object_terms', array( $this, 'status_change_notification' ), 10, 6 );
	}

	/**
	 * Populate our placeholders, where necessary adding values based on a current Boomerang.
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function populate_placeholders( $post = null ) {
		$terms  = get_the_terms( $post->ID, 'boomerang_status' );
		$status = $terms[0]->name;

		$placeholders = array(
			'{{title}}'  => $post->post_title ?? '',
			'{{board}}'  => $post->post_parent ?? '',
			'{{link}}'   => get_permalink( $post ) ?? '',
			'{{status}}' => $status ?? 'No Status',
		);

		$this->placeholders = $placeholders;

		return $placeholders;
	}

	/**
	 * Get our placeholders.
	 *
	 * @return mixed
	 */
	public function get_placeholders() {
		return $this->placeholders;
	}

	/**
	 * Sends email when a Boomerang's status changes.
	 *
	 * @param int $object_id
	 * @param array $terms
	 * @param array $tt_ids
	 * @param string $taxonomy
	 * @param bool $append
	 * @param array $old_tt_ids
	 *
	 * @return void
	 */
	public function status_change_notification( int $object_id, array $terms, array $tt_ids, string $taxonomy, bool $append, array $old_tt_ids ) {
		$post = get_post( $object_id );

		/**
		 * Bail if:
		 * Not a WP_Post
		 * WP_Post is not a Boomerang
		 * Taxonomy is not a Status
		 * The terms haven't changed (such as an update from admin edit screen)
		 */
		if ( ! $post || 'boomerang' !== $post->post_type || 'boomerang_status' !== $taxonomy || $tt_ids === $old_tt_ids ) {
			return;
		}

		if ( ! $this->is_enabled( 'status_change_email', $post->post_parent ) ) {
			return;
		}

		$notification = $this->get_notification( 'status_change_email', $post->post_parent );
		$author_id    = $post->post_author;
		$author_email = get_the_author_meta( 'email', $author_id );
		$subject      = $this->get_subject( $notification, $post );
		$content      = $this->get_content( $notification, $post );
		$headers      = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $author_email, $subject, $content, $this->get_headers() );
	}
}
