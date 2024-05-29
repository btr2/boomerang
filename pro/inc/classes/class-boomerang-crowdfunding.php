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
		add_action( 'boomerang_admin_controls_end', array( $this, 'add_crowdfunding_control' ) );
		add_action( 'wp_ajax_process_crowdfunding_product_submit', array( $this, 'process_crowdfunding_product_submit' ) );
	}

	/**
	 * Determines if there are Crowdfunding plugins active.
	 *
	 * @return bool
	 */
	public function can_crowdfund() {
		if ( $this->is_wpcrowdfunding() || $this->is_ignitiondeck() ) {
			require_once BOOMERANG_PATH . '/pro/integrations/boomerang-wp-crowdfunding.php';
			require_once BOOMERANG_PATH . '/pro/integrations/boomerang-ignitiondeck.php';

			return true;
		}

		return false;
	}

	/**
	 * Determines which crowdfunding plugins are active.
	 *
	 * @return false|string
	 */
	public function get_crowdfund_plugin() {
		if ( ! $this->can_crowdfund() ) {
			return false;
		}

		if ( $this->is_wpcrowdfunding() && ! $this->is_ignitiondeck() ) {
			return 'wpc';
		} else if ( ! $this->is_wpcrowdfunding() && $this->is_ignitiondeck() ) {
			return 'ign';
		} else if ( $this->is_wpcrowdfunding() && $this->is_ignitiondeck() ) {
			return 'both';
		} else {
			return false;
		}
	}

	/**
	 * Determines whether WP Crowdfunding is active.
	 *
	 * @return bool
	 */
	public function is_wpcrowdfunding(  ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'wp-crowdfunding-pro/wp-crowdfunding-pro.php' ) || is_plugin_active( 'wp-crowdfunding/wp-crowdfunding.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determines whether IgnitionDeck is active.
	 *
	 * @return bool
	 */
	public function is_ignitiondeck(  ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'ignitiondeck/idf.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Adds a crowdfunding menu item in our frontend admin area.
	 *
	 * @return false|string
	 */
	public function add_crowdfunding_control() {

		if ( ! $this->can_crowdfund() ) {
			return;
		}

		ob_start(); ?>

		<div class="boomerang-crowdfunding boomerang-control">
			<div class="control-header">
				<?php if ( ! boomerang_google_fonts_disabled() ) : ?>
					<span class="material-symbols-outlined icon">payments</span>
				<?php endif; ?>
				<h3><?php esc_html_e( 'Crowdfunding', 'boomerang' ); ?></h3>
				<?php if ( boomerang_google_fonts_disabled() ) : ?>
					<span class="chevron">&#x276F;</span>
				<?php else : ?>
					<span class="material-symbols-outlined chevron">chevron_right</span>
				<?php endif; ?>
			</div>
			<div class="control-content">
			<?php

			if ( $this->is_ignitiondeck() ) {
				echo render_ignitiondeck_dropdown();
			}

			if ( $this->is_wpcrowdfunding() ) {
				echo render_wp_crowdfunding_dropdown();
			}

			?>
			</div>
		</div>

		<?php return ob_get_flush();
	}


	/**
	 * Ajax handler to link a crowdfunding product with a Boomerang on frontend admin area.
	 *
	 * @return void
	 */
	public function process_crowdfunding_product_submit() {
		if ( ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
			'boomerang_admin_area'
		) ) {
			$error = new WP_Error(
				'Boomerang: Failed Security Check on Change of crowdfunding product',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );
		}

		$post_id    = sanitize_text_field( $_POST['post_id'] );
		$product_id = sanitize_text_field( $_POST['product_id'] );
		$plugin = sanitize_text_field( $_POST['plugin'] );

		if ( isset( $post_id ) && isset( $product_id ) && isset( $plugin ) ) {
			if ( '0' === $product_id ) {
				delete_post_meta( $post_id, $plugin . '_crowdfunding_product' );
			} else {
				update_post_meta( $post_id, $plugin . '_crowdfunding_product', $product_id );
			}
		}

		$return = array(
			'message' => __( 'Crowdfunding Product Linked', 'boomerang' ),
			'product' => $product_id,
		);

		wp_send_json_success( $return );

		wp_die();
	}

}

