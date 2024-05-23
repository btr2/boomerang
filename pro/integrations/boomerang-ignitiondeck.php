<?php
/**
 * WP Crowdfunding Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a crowdfunding section to a Boomerang.
 *
 * @return false|string
 */
function render_ignitiondeck_panel( $post ) {
	$project_id = 1;
	$deck       = new \Deck( $project_id );
	$custom     = false;
	$deck_id  = 1;
	$settings = \Deck::get_deck_attrs( $deck_id );
	if ( ! empty( $settings ) ) {
		$attrs  = unserialize( $settings->attributes );
		$custom = true;
	}
	$the_deck = $deck->the_deck();
	$custom   = apply_filters( 'idcf_custom_deck', $custom, $the_deck->post_id );
	$attrs    = apply_filters( 'idcf_deck_attrs', ( isset( $attrs ) ? $attrs : null ), $the_deck->post_id );
	include BOOMERANG_PATH . 'pro/templates/ignitiondeckpanel.php';
}
add_action( 'boomerang_above_meta', __NAMESPACE__ . '\render_ignitiondeck_panel' );