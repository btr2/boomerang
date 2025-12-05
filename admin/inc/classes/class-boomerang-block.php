<?php
/**
 * Manage The Boomerang Block
 */
namespace Bouncingsprout_Boomerang;

if ( ! class_exists( 'Boomerang_Block' ) ) {
	/**
	 * Class to manage our Boomerang Block
	 */
	class Boomerang_Block {

		/**
		 * Initialize the class and set its properties.
		 */
		public function __construct() {
			$this->init();
		}

		/**
		 * Decoupler.
		 *
		 * @return void
		 */
		public function init() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_head', array( $this, 'render_block_styles' ) );
		}

		/**
		 * Register the JavaScript And Stylesheet for the Ultimeter Gutenberg Block.
		 *
		 * @since    2.4.0
		 */
		public function enqueue_scripts() {
			if ( is_admin() ) {
				wp_enqueue_script( 'boomerang-block', BOOMERANG_URL . 'admin/assets/js/boomerang-block.js', array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor', 'wp-compose' ), BOOMERANG_VERSION, false );
			}

			register_block_type(
				'boomerang-block/shortcode-gutenberg',
				array(
					'editor_script'   => 'boomerang-block',
					'render_callback' => '\Bouncingsprout_Boomerang\render_boomerang_full',
					'attributes'      => array(
						'board' => array(
							'type' => 'string',
						),
					),
				)
			);

			/* Define plugin logo image url global variable */
			wp_localize_script(
				'boomerang-block',
				'boomerangGlobal',
				array(
					'logoUrl' => BOOMERANG_URL . 'admin/assets/images/logo-white.png',
					'iconUrl' => BOOMERANG_URL . 'admin/assets/images/logo-small.png',
				)
			);

		}

		public function render_block_styles() {
			?>

		<style>
            .placeholder-boomerang-block .components-placeholder {
                background: #027AD0;
            }
            .placeholder-boomerang-block .components-placeholder.is-large {
                align-items: center !important;
            }
            .placeholder-boomerang-block .components-base-control {
                width: 100%;
                text-align: center;

            }
            .placeholder-boomerang-block .components-placeholder__label{
                display: block;
                text-align: center;
                width: 100%;
            }
            .placeholder-boomerang-block .boomerang-logo{
                display: block !important;
                margin: 0 auto;
                width: 150px !important;
                height: auto !important;
                max-width: 150px !important;
            }

            .placeholder-boomerang-block select{
                min-height: 40px !important;
            }
		</style>

			<?php
		}
	}
}
