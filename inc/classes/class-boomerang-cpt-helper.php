<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles displays and hooks for the Boomerang custom post type(s).
 */
class Boomerang_CPT_Helper {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Registers the custom post type and taxonomies.
	 */
	public function register_post_types() {
		$status_singular = 'Status';
		$status_plural   = 'Statuses';

		register_taxonomy(
			'boomerang_status',
			array( 'boomerang' ),
			array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => $status_plural,
					'singular_name'     => $status_singular,
					// translators: Placeholder %s is the plural label of the boomerang 'status' taxonomy.
					'search_items'      => sprintf( __( 'Search %s', 'boomerang' ), $status_plural ),
					// translators: Placeholder %s is the plural label of the boomerang 'status' taxonomy.
					'all_items'         => sprintf( __( 'All %s', 'boomerang' ), $status_plural ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'parent_item'       => sprintf( __( 'Parent %s', 'boomerang' ), $status_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'parent_item_colon' => sprintf( __( 'Parent %s:', 'boomerang' ), $status_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'edit_item'         => sprintf( __( 'Edit %s', 'boomerang' ), $status_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'update_item'       => sprintf( __( 'Update %s', 'boomerang' ), $status_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'add_new_item'      => sprintf( __( 'Add New %s', 'boomerang' ), $status_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'new_item_name'     => sprintf( __( 'New %s', 'boomerang' ), $status_singular ),
					'menu_name'         => sprintf( '%s', $status_plural ),
				),
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => 'status',
					'with_front' => true,
				),
			)
		);

		// Register the Post Type

		$slug         = 'boomerang';
		$cpt_singular = 'Boomerang';
		$cpt_plural   = 'Boomerangs';

		register_post_type(
			'boomerang',
			apply_filters(
				'register_post_type_boomerang',
				array(
					'labels'                => array(
						'name'               => $cpt_plural,
						'singular_name'      => $cpt_singular,
						'menu_name'          => $cpt_plural,
						// translators: Placeholder %s is the plural label of the boomerang post type.
						'all_items'          => sprintf( __( 'All %s', 'boomerang' ), $cpt_plural ),
						'add_new'            => __( 'Add New', 'boomerang' ),
						// translators: Placeholder %s is the singular label of the boomerang post type.
						'add_new_item'       => sprintf( __( 'Add %s', 'boomerang' ), $cpt_singular ),
						'edit'               => __( 'Edit', 'boomerang' ),
						// translators: Placeholder %s is the singular label of the boomerang post type.
						'edit_item'          => sprintf( __( 'Edit %s', 'boomerang' ), $cpt_singular ),
						// translators: Placeholder %s is the singular label of the boomerang post type.
						'new_item'           => sprintf( __( 'New %s', 'boomerang' ), $cpt_singular ),
						// translators: Placeholder %s is the singular label of the boomerang post type.
						'view'               => sprintf( __( 'View %s', 'boomerang' ), $cpt_singular ),
						// translators: Placeholder %s is the singular label of the boomerang post type.
						'view_item'          => sprintf( __( 'View %s', 'boomerang' ), $cpt_singular ),
						// translators: Placeholder %s is the singular label of the boomerang post type.
						'search_items'       => sprintf( __( 'Search %s', 'boomerang' ), $cpt_plural ),
						// translators: Placeholder %s is the singular label of the boomerang post type.
						'not_found'          => sprintf( __( 'No %s found', 'boomerang' ), $cpt_plural ),
						// translators: Placeholder %s is the plural label of the boomerang post type.
						'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'boomerang' ), $cpt_plural ),
						// translators: Placeholder %s is the singular label of the boomerang post type.
						'parent'             => sprintf( __( 'Parent %s', 'boomerang' ), $cpt_singular ),
					),
					'public'                => true,
					'show_ui'               => true,
					'capability_type'       => 'post',
					'map_meta_cap'          => true,
					'publicly_queryable'    => true,
					'exclude_from_search'   => false,
					'hierarchical'          => false,
					'rewrite'               => array( 'slug' => $slug ),
					'query_var'             => true,
					'supports'              => array(
						'title',
						'editor',
						'custom-fields',
						'publicize',
						'thumbnail',
						'author',
					),
					'has_archive'           => true,
					'show_in_nav_menus'     => true,
					'delete_with_user'      => true,
					'show_in_rest'          => true,
					'rest_base'             => 'boomerang',
					'rest_controller_class' => 'WP_REST_Posts_Controller',
					'template'              => array( array( 'core/freeform' ) ),
					'template_lock'         => 'all',
					'menu_position'         => 30,
					'menu_icon'             => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyOTYgMjk2IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8Zz4KPHBhdGggZD0iTTExNi45NzcgMTI2LjI1TDI1MS4xODIgMTAwLjE2OUMyNjAuNDY1IDk5LjEzNDIgMjY0LjA3NyA5My4zNjY2IDI2MC4xMjYgNzYuMjM1NUMyNTcuNDI0IDY0LjUxOCAyNTEuNTI4IDM3LjQwNCAyMjMuMTM3IDI2LjQxMTJDMTkyLjQyOCAxNC41MjE0IDEwNS43OTcgNDguMjMyMiA4NC43OTYxIDkyLjk4MjRDNjcuOTk1OCAxMjguNzgyIDk5LjI0OTYgMTMwLjA3NyAxMTYuOTc3IDEyNi4yNVoiIGZpbGw9IiNmMGYwZjEiLz4KPHBhdGggZD0iTTE4Ni4yOTkgMTc1Ljc3Nkw1Mi43ODUzIDIwNS4xMDlDNDMuNTMxMyAyMDYuMzY4IDQwLjA2NDMgMjEyLjIyMiA0NC40Mzk2IDIyOS4yNTJDNDcuNDMyMiAyNDAuOTAxIDUzLjk5OTcgMjY3Ljg2NCA4Mi42NTU0IDI3OC4xNjVDMTEzLjY1IDI4OS4zMDYgMTk5LjQxNSAyNTMuNTAxIDIxOS4yOTYgMjA4LjI1M0MyMzUuMjAxIDE3Mi4wNTUgMjAzLjkyNSAxNzEuNTE5IDE4Ni4yOTkgMTc1Ljc3NloiIGZpbGw9IiNmMGYwZjEiLz4KPHBhdGggZD0iTTE3NC4zNzEgMTE0LjUxOEwyMDEuODQ5IDI0NS44NzdDMjAyLjk2NCAyNTQuOTY3IDIwOC44NzIgMjU4LjQ3MSAyMjYuMzI3IDI1NC40OTRDMjM4LjI2NiAyNTEuNzc0IDI2NS44OTUgMjQ1LjgyOSAyNzYuOTI5IDIxNy45MzhDMjg4Ljg2NCAxODcuNzcxIDI1My45MDcgMTAzLjA4MiAyMDguMTA5IDgyLjc3NzVDMTcxLjQ3MSA2Ni41MzM5IDE3MC4zNTEgOTcuMTcgMTc0LjM3MSAxMTQuNTE4WiIgZmlsbD0iI2YwZjBmMSIvPgo8cGF0aCBkPSJNMTI1LjQxOCAxODAuNDIyTDk5LjUzMjUgNDguODA4OEM5OC41MjgxIDM5LjcxMTIgOTIuNjU4MyAzNi4xMzExIDc1LjE0MjIgMzkuODY5OEM2My4xNjEzIDQyLjQyNzEgMzUuNDM5OSA0Ny45OTQ2IDI0LjA1NTggNzUuNzEyOEMxMS43NDI4IDEwNS42OTMgNDUuNjg0OCAxOTAuNzc4IDkxLjI2NjEgMjExLjY4MUMxMjcuNzMxIDIyOC40MDMgMTI5LjIyOCAxOTcuODA5IDEyNS40MTggMTgwLjQyMloiIGZpbGw9IiNmMGYwZjEiLz4KPC9nPgo8L3N2Zz4K',
				)
			)
		);
	}
}
