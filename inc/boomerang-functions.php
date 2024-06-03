<?php
/**
 * Functions that relate to individual Boomerangs.
 */
namespace Bouncingsprout_Boomerang;

/**
 * Helper function that retrieves the WP_Post object for either a Boomerang,
 * or the current WP_Post if none is provided.
 *
 * @param $post
 *
 * @return array|WP_Post|null
 */
function boomerang_get_boomerang( $post = false ) {
	if ( ! $post ) {
		$post = get_post();
	} else {
		$post = get_post( $post );
	}

	if ( ! 'boomerang' === $post->post_type ) {
		return false;
	}

	return $post;
}

/**
 * Is this a Boomerang?
 *
 * @param $post
 *
 * @return bool
 */
function boomerang_is_boomerang( $post = false ) {
	if ( ! $post ) {
		$post = get_post();

		if ( ! $post ) {
			return false;
		}
	} else {
		$post = get_post( $post );
	}

	if ( 'boomerang' === $post->post_type ) {
		return true;
	}

	return false;
}

/**
 * Gets the total number of Boomerangs published by a user.
 *
 * @param $user_id
 *
 * @return string
 */
function boomerang_get_count( $user_id = false ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	return count_user_posts( $user_id, 'boomerang', true );
}

/** Statuses **********************************************************************************************************/

/** Comments **********************************************************************************************************/

/** Voting ************************************************************************************************************/

/**
 * Gets the current votes for a given Boomerang.
 *
 * @return void
 */
function boomerang_get_votes( $post ) {
	$votes = get_post_meta( $post->ID, 'boomerang_votes', true );

	if ( ! $votes ) {
		$votes = 0;
	}

	return apply_filters( 'boomerang_votes', $votes, $post );
}

/**
 * Checks to see if user has voted on a Boomerang, and if so, the vote status.
 *
 * @param $user_id
 * @param $post
 *
 * @return false|mixed
 */
function boomerang_user_has_voted( $user_id, $post = false ) {
	$post = boomerang_get_boomerang( $post );

	$user_votes = get_user_meta( get_current_user_id(), 'boomerang_user_votes', true ) ?? array();

	// check to see if user has already voted on this Boomerang.
	if ( ! empty( $user_votes ) ) {
		if ( array_key_exists( $post->ID, $user_votes ) ) {
			$vote_status = $user_votes[ $post->ID ];

			return $vote_status;
		}
	}

	return false;
}

/**
 * Checks to see if a user has voted.
 *
 * @param $user_id
 *
 * @return int[]|string[]
 */
function boomerang_get_user_voted( $user_id = false ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$user_votes = get_user_meta( $user_id, 'boomerang_user_votes', true ) ?? array();

	return array_keys( $user_votes, '1' );
}

