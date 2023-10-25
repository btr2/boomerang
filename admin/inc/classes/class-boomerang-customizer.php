<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang Customizer functionality.
 */
class Boomerang_Customizer {
	/**
	 * Define the customizer functionality of the plugin.
	 */
	public function __construct() {
		// require_once BOOMERANG_PATH . 'vendor/codestar-framework/codestar-framework.php';

		$this->init_hooks();
	}

	/**
	 * Decouple our hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		// add_action( 'wp_head', array( $this, 'render_styles' ) );
	}

	public function customize_register( $wp_customize ) {
		/**
		 * The panel that holds all settings for a single Boomerang's page.
		 */
		$wp_customize->add_panel(
			'boomerang_single',
			array(
				'title' => esc_html__( 'Single Boomerang', 'boomerang' ),
			)
		);

		$wp_customize->add_section(
			'layout',
			array(
				'title'       => __( 'Layout' ),
				'description' => __( 'Make changes to the layout' ),
				'panel'       => 'boomerang_single',
				'capability'  => 'manage_options',
			)
		);

		$wp_customize->add_setting(
			'boomerang_customizer_options[container]',
			array(
				'default'           => '1200',
				'type'              => 'option',
				'section'           => 'layout',
				'sanitize_callback' => 'sanitize_text_field',
				'capability'        => 'manage_options',
			)
		);

		$wp_customize->add_control(
			'boomerang_customizer_options[container]',
			array(
				'label'   => __( 'Width' ),
				'type'    => 'text',
				'section' => 'layout',
			)
		);
	}

	public function render_styles() {
		$options = get_option( 'boomerang_customizer_options' );
		?>
			<style id="boomerang-customizer-options">
				.single-boomerang .boomerang-container {
					width: <?php echo esc_attr( $options['container'] ) ?? 1200; ?>px;
				}
			</style>
		<?php
	}
}
