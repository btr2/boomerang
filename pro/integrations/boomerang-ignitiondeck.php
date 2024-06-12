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
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	if ( ! is_plugin_active( 'ignitiondeck/idf.php' ) ) {
		return;
	}

	if ( ! get_post_meta( $post->ID, 'ign_crowdfunding_product', true ) ) {
		return;
	}

	$project_id = get_post_meta( $post->ID, 'ign_crowdfunding_product', true );
	$deck       = new \Deck( $project_id );
	$custom     = false;
	$deck_id    = get_board_deck( $post->post_parent );
	$settings   = \Deck::get_deck_attrs( $deck_id );

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

/**
 * Renders the dropdown containing IgnitionDeck products below the Crowdfunding admin control.
 *
 * @return void
 */
function render_ignitiondeck_dropdown() {
	$deck = new \Deck();

	$projects = $deck->get_all_projects();

	$selected = get_post_meta( get_the_ID(), 'ign_crowdfunding_product', true );
	?>

	<div class="crowdfund-dropdown">
		<label><?php esc_html_e( 'Select an IgnitionDeck Product', 'boomerang' ); ?></label>
		<fieldset>
			<select id="ignitiondeck-dropdown" name="crowdfunding_product">
				<option value="0"><?php esc_html_e( 'None', 'boomerang' ); ?></option>
				<?php
				foreach ( $projects as $project ) {
					echo '<option' . selected( $project->id, intval( $selected ), false ) . ' value="' . intval( $project->id ) . '">' . esc_html( $project->ign_product_title ) . '</option>';
				}
				?>
			</select>
			<div class="control-content-inline-button crowdfund-submit icon-only" id="ign-crowdfund-submit" data-plugin="ign">
				<?php if ( boomerang_google_fonts_disabled() ) : ?>
					<span><?php esc_attr_e( 'Submit', 'boomerang' ); ?></span>
				<?php else : ?>
					<span class="material-symbols-outlined">arrow_forward</span>
				<?php endif; ?>
			</div>
		</fieldset>
	</div>

	<?php
}

/**
 * Gets an array of Decks built using the Deck Builder.
 *
 * @return array
 */
function get_ign_decks() {
	if ( ! class_exists( 'Deck' ) ) {
		return array();
	}

	$deck_class = new \Deck();
	$deck_list  = $deck_class::get_deck_list();
	$decks      = array();

	foreach ( $deck_list as $item ) {
		$deck['id']           = $item->id;
		$deck['attrs']        = unserialize( $item->attributes );
		$decks[ $deck['id'] ] = $deck['attrs']['deck_title'];
	}

	return $decks;
}

/**
 * Gets the IgnitionDeck Deck from Board settings.
 *
 * @param $post
 *
 * @return mixed
 */
function get_board_deck( $post = false ) {
	$post = boomerang_get_post( $post );
	$meta = get_post_meta( $post->ID, 'boomerang_board_options', true );

	return $meta['ign_deck'] ?? false;
}
