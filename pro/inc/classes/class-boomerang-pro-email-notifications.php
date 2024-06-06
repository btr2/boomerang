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
		add_action( 'comment_post', array( $this, 'new_comment_notification' ), 10, 3 );
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
	 * Retrieves the email address of the author for a given post.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return string|null The author's email address, or null if not found.
	 */
	public function get_author_email( $post ) {
		// Check if post is guest created.
		if ( get_post_meta( $post->ID, 'guest_created', true ) ) {
			$guest_email = get_post_meta( $post->ID, 'guest_user_email', true );
			// If the guest supplied an address, use that.
			if ( $guest_email ) {
				return $guest_email;
			}
		} else {
			// Must be user created, so use the post author email.
			$author_id = $post->post_author;
			return get_the_author_meta( 'email', $author_id );
		}
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
		$author_email = $this->get_author_email( $post );
		$subject      = $this->get_subject( $notification, $post );
		$content      = $this->get_content( $notification, $post );
		$headers      = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $author_email, $subject, $content, $this->get_headers() );
	}

	/**
	 * Sends email when a new comment is posted on a Boomerang.
	 *
	 * @param int $comment_id The ID of the comment.
	 * @param string $comment_approved The approval status of the comment.
	 * @param array $commentdata The data of the comment.
	 *
	 * @return void
	 */
	public function new_comment_notification( $comment_id, $comment_approved, $commentdata ) {
		$post = get_post( $commentdata['comment_post_ID'] );

		if ( ! $post || 'boomerang' !== $post->post_type || 1 !== $comment_approved ) {
			return;
		}

		if ( ! $this->is_enabled( 'new_comment_email', $post->post_parent ) ) {
			return;
		}

		if ( get_comment_meta( $comment_id, 'boomerang_private_note', true ) ) {
			return;
		}

		$notification = $this->get_notification( 'new_comment_email', $post->post_parent );
		$author_email = $this->get_author_email( $post );
		$subject      = $this->get_subject( $notification, $post );
		$content      = $this->get_content( $notification, $post );
		$headers      = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $author_email, $subject, $content, $this->get_headers() );
	}
}
