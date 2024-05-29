<?php
/**
 * WP Crowdfunding Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a dropdown to select a WP Crowdfunding project from inside an admin area.
 *
 * @return void
 */
function render_wp_crowdfunding_dropdown() {

	// Get external products.
	$args     = array(
		'type'   => 'crowdfunding',
		'status' => 'publish',
	);
	$products = wc_get_products( $args );

	$selected = get_post_meta( get_the_ID(), 'wpc_crowdfunding_product', true );

	?>

	<div class="crowdfund-dropdown">
		<label><?php esc_html_e( 'Select a WP Crowdfunding Product', 'boomerang' ); ?></label>
		<fieldset>
			<select id="wp-crowdfunding-dropdown" name="crowdfunding_product">
				<option value="0"><?php esc_html_e( 'None', 'boomerang' ); ?></option>
				<?php
				foreach ( $products as $product ) {
					echo '<option' . selected( $product->get_id(), intval( $selected ), false ) . ' value="' . intval( $product->get_id() ) . '">' . esc_html( $product->get_name() ) . '</option>';
				}
				?>
			</select>
			<div class="control-content-inline-button crowdfund-submit icon-only" id="wpc-crowdfund-submit" data-plugin="wpc">
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
 * Adds a crowdfunding section to a Boomerang.
 *
 * @return false|string
 */
function render_crowdfund_info( $post ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	if ( ! is_plugin_active( 'wp-crowdfunding-pro/wp-crowdfunding-pro.php' ) && ! is_plugin_active( 'wp-crowdfunding/wp-crowdfunding.php' ) ) {
		return;
	}

	if ( ! get_post_meta( $post->ID, 'wpc_crowdfunding_product', true ) ) {
		return;
	}

	echo '<div class="boomerang-crowdfunding-panel-container">';

	$product = get_post_meta( $post->ID, 'wpc_crowdfunding_product', true );

	if ( $product ) {
		echo wp_kses_post( get_crowdfunding_info_html( $post, $product ) );
	}

	echo '</div>';
}
add_action( 'boomerang_above_meta', __NAMESPACE__ . '\render_crowdfund_info' );

/**
 * Generate HTML to populate the crowdfunding info panel.
 *
 * @param $post
 *
 * @return void
 */
function get_crowdfunding_info_html( $post, $product ) {
	echo '<div class="boomerang-crowdfunding-panel-inner">';

	$end_method     = get_post_meta( $product, 'wpneo_campaign_end_method', true );
	$raised_percent = wpcf_function()->get_fund_raised_percent_format();
	$button_text    = 'Contribute';
	?>
	<div class="campaign-funding-info">
		<ul>
			<li>
				<p class="funding-amount">
					<?php
					$price = wpcf_function()->total_goal( $product );
					if ( $price ) {
						echo wp_kses_post( wpcf_function()->price( $price ) );
					} else {
						esc_html_e( 'Not Set', 'boomerang' );
					}
					?>
				</p>
				<span class="info-text"><?php esc_html_e( 'Funding Goal', 'boomerang' ); ?></span>
			</li>
			<li>
				<p class="funding-amount"><?php echo wp_kses_post( wpcf_function()->price( wpcf_function()->fund_raised( $product ) ) ); ?></p>
				<span class="info-text"><?php esc_html_e( 'Funds Raised', 'boomerang' ); ?></span>
			</li>
			<?php
			if ( 'never_end' !== $end_method ) {
				?>
				<li>
					<?php if ( wpcf_function()->is_campaign_started( $product ) ) { ?>
						<p class="funding-amount"><?php echo esc_html( wpcf_function()->get_date_remaining( $product ) ); ?></p>
						<span class="info-text"><?php esc_html_e( 'Days to go', 'boomerang' ); ?></span>
					<?php } else { ?>
						<p class="funding-amount"><?php echo esc_html( days_until_launch( $product ) ); ?></p>
						<span class="info-text"><?php esc_html_e( 'Days Until Launch', 'boomerang' ); ?></span>
					<?php } ?>
				</li>
			<?php } ?>

			<li>
				<p class="funding-amount">
					<?php
					if ( 'target_goal' === $end_method ) {
						esc_html_e( 'Target Goal', 'boomerang' );
					} elseif ( 'target_date' === $end_method ) {
						esc_html_e( 'Target Date', 'boomerang' );
					} elseif ( 'target_goal_and_date' === $end_method ) {
						esc_html_e( 'Goal and Date', 'boomerang' );
					} else {
						esc_html_e( 'Campaign Never Ends', 'boomerang' );
					}
					?>
				</p>
				<span class="info-text"><?php esc_html_e( 'Campaign End Method', 'boomerang' ); ?></span>
			</li>
		</ul>
	</div>

	<div class="percent-button-container">
		<div class="percent-bar-container">
			<div class="wpneo-raised-percent">
				<div class="wpneo-meta-name"><?php esc_html_e( 'Raised Percent', 'boomerang' ); ?> :</div>
				<div class="wpneo-meta-desc" ><?php echo esc_html( wpcf_function()->get_raised_percent( $product ) . '%' ); ?></div>
			</div>
			<div class="wpneo-raised-bar">
				<div id="neo-progressbar">
					<?php
					$css_width = wpcf_function()->get_raised_percent( $product );
					if ( $css_width >= 100 ) {
						$css_width = 100; }
					?>
					<div style="width: <?php echo ( esc_html( $css_width ) ); ?>%"></div>
				</div>
			</div>
		</div>

		<a class="contribute-button button btn" href="<?php echo esc_url( get_permalink( $product ) ); ?>"><?php echo esc_html( $button_text ); ?></a>
	</div>


	<?php
	echo '</div>';
}

/**
 * Fixes a bug in WP Crowdfunding's code, where the global $post variable is used, despite calling for a $post_id.
 *
 * @see wpcf_function()->days_until_launch()
 *
 * @param $post_id
 *
 * @return false|float|int
 */
function days_until_launch( $post_id = 0 ) {
	$_nf_duration_start = get_post_meta( $post_id, '_nf_duration_start', true );

	if ( ( strtotime( $_nf_duration_start ) ) > time() ) {
		$diff = strtotime( $_nf_duration_start ) - time();
		$temp = $diff / 86400; // 60 sec/min*60 min/hr*24 hr/day=86400 sec/day
		$days = floor( $temp );
		return $days >= 1 ? $days : 1; //Return min one days, though if remain only 1 min
	}

	return 0;
}
