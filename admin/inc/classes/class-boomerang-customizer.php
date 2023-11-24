<?php

namespace Bouncingsprout_Boomerang;

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
		$this->init_hooks();
	}

	/**
	 * Decouple our hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_scripts' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_script' ) );
		add_action( 'csf_loaded', array( $this, 'render_customizer' ) );
	}

	public function enqueue_customizer_scripts() {
		wp_enqueue_style( 'boomerang-customizer', BOOMERANG_URL . 'admin/assets/css/boomerang-admin.css', array(), BOOMERANG_VERSION );
	}

	public function enqueue_preview_script() {
		wp_enqueue_script(
			'boomerang-customizer',
			BOOMERANG_URL . 'admin/assets/js/boomerang-customizer.js',
			array( 'jquery', 'customize-preview' ),
			BOOMERANG_VERSION,
			true
		);
	}

	public function render_customizer() {
		// Control core classes for avoid errors
		if ( class_exists( 'CSF' ) ) {

			//
			// Set a unique slug-like ID
			$prefix = 'boomerang_customizer';

			//
			// Create customize options
			\CSF::createCustomizeOptions(
				$prefix,
				array(
					'database'        => 'option',
					'transport'       => 'postMessage',
					'enqueue_webfont' => false,
					'async_webfont'   => false,
				)
			);

			//
			// Create a top-tab
			\CSF::createSection(
				$prefix,
				array(
					'id'    => 'boomerang_menu', // Set a unique slug-like ID
					'title' => 'Boomerang',
				)
			);

			//
			// Create a sub-tab
			\CSF::createSection(
				$prefix,
				array(
					'parent' => 'boomerang_menu', // The slug id of the parent section
					'title'  => 'Boomerang Global Styles',
					'fields' => array(
						array(
							'id'      => 'primary_color',
							'type'    => 'color',
							'title'   => esc_html__( 'Primary Color', 'boomerang' ),
							'default' => '#027AD0',
							'desc'    => esc_html__( 'Changes the color of buttons, borders and other elements', 'boomerang' ),
						),
						array(
							'id'      => 'private_note_color',
							'type'    => 'color',
							'title'   => esc_html__( 'Private Note Color', 'boomerang' ),
							'default' => '#fab347',
							'desc'    => esc_html__( 'Color used for all Private Note functionality', 'boomerang' ),
						),
					),
				)
			);

			//
			// Create a sub-tab
			\CSF::createSection(
				$prefix,
				array(
					'parent' => 'boomerang_menu', // The slug id of the parent section
					'title'  => 'Boomerang Directory',
					'fields' => array(

						// A text field
						array(
							'id'        => 'archive_layout',
							'type'      => 'image_select',
							'title'     => esc_attr__( 'Layout', 'boomerang' ),
							'transport' => 'postMessage',
							'default'   => 'vertical',
							'options'   => array(
								'vertical'   => BOOMERANG_URL . 'admin/assets/images/vertical.png',
								'horizontal' => BOOMERANG_URL . 'admin/assets/images/horizontal.png',
							),
						),
					),
				)
			);

			//
			// Create a sub-tab
			\CSF::createSection(
				$prefix,
				array(
					'parent' => 'boomerang_menu',
					'title'  => 'Single Boomerang',
					'fields' => array(

						// A textarea field
						array(
							'id'    => 'opt-textarea',
							'type'  => 'textarea',
							'title' => 'Simple Textarea',
						),

					),
				)
			);

		}

	}

	public function customize_register( $wp_customize ) {
		/**
		 * The panel that holds all settings for a single Boomerang's page.
		 */
		$wp_customize->add_panel(
			'boomerang_archive',
			array(
				'title' => esc_html__( 'Boomerang Archive Page', 'boomerang' ),
			)
		);

		$wp_customize->add_section(
			'layout',
			array(
				'title'       => __( 'Layout' ),
				'description' => __( 'Make changes to the layout' ),
				'panel'       => 'boomerang_archive',
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
