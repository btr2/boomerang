<?php
/**
 * Defines all functionality relating to our Boomerang Custom Post Types.
 */
namespace Bouncingsprout_Boomerang;

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
		require_once BOOMERANG_PATH . '/inc/boomerang-global-functions.php';

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'admin_init', array( $this, 'add_define_slug_setting' ) );
		add_action( 'admin_init', array( $this, 'save_slug_setting' ) );
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
					'name'              => __( 'Status', 'boomerang' ),
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
					'new_item_name'     => sprintf( $status_singular ),
					// translators: Name of the Boomerang status menu label.
					'menu_name'         => __( 'Status Center', 'boomerang' ),
				),
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => 'status',
					'with_front' => true,
				),
			)
		);

		$tag_singular = 'Tag';
		$tag_plural   = 'Tags';

		register_taxonomy(
			'boomerang_tag',
			array( 'boomerang' ),
			array(
				'hierarchical'      => false,
				'labels'            => array(
					'name'              => $tag_plural,
					'singular_name'     => $tag_singular,
					// translators: Placeholder %s is the plural label of the boomerang 'status' taxonomy.
					'search_items'      => sprintf( __( 'Search %s', 'boomerang' ), $tag_plural ),
					// translators: Placeholder %s is the plural label of the boomerang 'status' taxonomy.
					'all_items'         => sprintf( __( 'All %s', 'boomerang' ), $tag_plural ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'parent_item'       => sprintf( __( 'Parent %s', 'boomerang' ), $tag_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'parent_item_colon' => sprintf( __( 'Parent %s:', 'boomerang' ), $tag_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'edit_item'         => sprintf( __( 'Edit %s', 'boomerang' ), $tag_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'update_item'       => sprintf( __( 'Update %s', 'boomerang' ), $tag_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'add_new_item'      => sprintf( __( 'Add New %s', 'boomerang' ), $tag_singular ),
					// translators: Placeholder %s is the singular label of the boomerang 'status' taxonomy.
					'new_item_name'     => sprintf( __( 'New %s', 'boomerang' ), $tag_singular ),
					'menu_name'         => sprintf( '%s', $tag_plural ),
				),
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => 'boomerang_tag',
					'with_front' => true,
				),
			)
		);

		// Register the Boomerang Post Type

		$slug         = 'boomerang';
		$cpt_singular = 'Boomerang';
		$cpt_plural   = 'Boomerangs';
		$menu_icon    = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDE4MSAyOTIiIGZpbGw9Im5vbmUiPg0KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0yNS4wMjMzIDE2Ljc3MUMxMC4xMTU4IDI3Ljc3NDEgMi42NjIwNiA0MS43MDU2IDIuNjYyMDYgNTguNTY1M0MyLjY2MjA2IDY3Ljk3MTIgNi4zMDAyIDc2LjIyMzYgMTMuNTc2NSA4My4zMjI0QzIwLjg1MjggOTAuNDIxMiAzMS4yMzQ4IDk0LjIzNjggNDQuNzIyNSA5NC43NjkyTDQ3LjY1MDggNzUuMDdDNDEuMjYxOSA3NC4zNjAxIDM2LjExNTIgNzIuNDA3OSAzMi4yMTA5IDY5LjIxMzVDMjguMzA2NSA2NS44NDE1IDI2LjM1NDQgNjEuNzU5NyAyNi4zNTQ0IDU2Ljk2OEMyNi4zNTQ0IDQ3LjAyOTcgMzEuODU1OSAzOS4xMzIyIDQyLjg1OTEgMzMuMjc1N0M1My44NjIzIDI3LjI0MTcgNjUuMTMxNiAyNC4yMjQ3IDc2LjY2NzIgMjQuMjI0N0M5NC4yMzY4IDI0LjIyNDcgMTA4LjA3OSAyNy44NjI5IDExOC4xOTUgMzUuMTM5MkMxMjcuNjAxIDQyLjA2MDUgMTMyLjEyNyA1MC41NzkxIDEzMS43NzIgNjAuNjk0OUMxMzEuNTk0IDcwLjgxMDcgMTI3Ljc3OSA3OC40NDE5IDEyMC4zMjUgODMuNTg4NkMxMTMuMDQ5IDg4LjU1NzggMTA0LjA4NiA5MS4wNDIzIDkzLjQzODIgOTEuMDQyM0w5MS4wNDIzIDExMC43NDJDMTA2LjQ4MiAxMTAuNzQyIDExOC42MzkgMTE0LjIwMiAxMjcuNTEyIDEyMS4xMjRDMTM2LjU2MyAxMjguMDQ1IDE0MS4wODkgMTM2LjU2NCAxNDEuMDg5IDE0Ni42NzlDMTQxLjA4OSAxNTYuNzk1IDEzNy44MDYgMTY0Ljk1OSAxMzEuMjM5IDE3MS4xN0MxMjQuNjczIDE3Ny4yMDQgMTE2LjE1NCAxODAuMjIxIDEwNS42ODQgMTgwLjIyMUM5NS4zOTA0IDE4MC4yMjEgODYuMzM5NCAxNzguMTggNzguNTMwNyAxNzQuMDk5TDcwLjI3ODMgMTk2LjQ2Qzc3LjM3NzEgMjAxLjYwNiA5MC42ODc0IDIwNC4xOCAxMTAuMjA5IDIwNC4xOEMxMjkuOTA4IDIwNC4xOCAxNDYuNTkxIDE5OS4yMTEgMTYwLjI1NiAxODkuMjcyQzE3My45MjEgMTc5LjMzNCAxODAuNzU0IDE2NS40OTEgMTgwLjc1NCAxNDcuNzQ0QzE4MC43NTQgMTM0LjI1NiAxNzUuMTYzIDEyMy4wNzYgMTYzLjk4MyAxMTQuMjAyQzE1Mi45NzkgMTA1LjMyOSAxMzkuOTM1IDEwMC4zNiAxMjQuODUgOTkuMjk0N0MxMzYuOTE4IDk4LjkzOTggMTQ3LjIxMiA5NC41MDMgMTU1LjczIDg1Ljk4NDRDMTY0LjI0OSA3Ny40NjU4IDE2OC41MDggNjcuNzkzNyAxNjguNTA4IDU2Ljk2OEMxNjguNjg2IDQ1Ljk2NDggMTY1LjQwMiAzNi4wMjY1IDE1OC42NTkgMjcuMTUzQzE1NC41NzcgMjIuMDA2MyAxNDkuNTE5IDE3LjQ4MDggMTQzLjQ4NSAxMy41NzY1QzEzNy40NTEgOS42NzIxNCAxMjkuMjg3IDYuNDc3NjcgMTE4Ljk5NCAzLjk5MzA4QzEwOC43MDEgMS4zMzEwMyA5Ny4wNzYzIDAgODQuMTIxIDBDNTkuNjMwMSAwIDM5LjkzMDggNS41OTAzMiAyNS4wMjMzIDE2Ljc3MVpNNjIuMjkyMSAxOTEuOTM0QzcwLjQ1NTcgMTgzLjk0OCA3NS45NTczIDE3MC40NiA3OC43OTY5IDE1MS40NzFMOTUuNTY3OCAzNy44MDEyQzkxLjQ4NiAzNi45MTM5IDg3LjIyNjcgMzYuNDcwMiA4Mi43ODk5IDM2LjQ3MDJDNzMuMjA2NSAzNi40NzAyIDY0Ljc3NjcgMzguNzc3MyA1Ny41MDA0IDQzLjM5MTVDNTcuMTQ1NSA0NS44NzYxIDU2LjQzNTYgNTEuMDIyOCA1NS4zNzA4IDU4LjgzMTVDNTQuNDgzNCA2Ni42NDAyIDUzLjE1MjQgNzYuOTMzNCA1MS4zNzc3IDg5LjcxMTNDNDkuNzgwNSAxMDIuMzEyIDQ4LjUzODIgMTEyLjMzOSA0Ny42NTA4IDExOS43OTNDMTUuODgzNiAxMzQuNTIzIDAgMTUyLjM1OCAwIDE3My4zQzAgMTgyLjE3MyAyLjgzOTUzIDE4OS40NSA4LjUxODU4IDE5NS4xMjlDMTQuMTk3NiAyMDAuODA4IDIyLjUzODcgMjAzLjY0NyAzMy41NDE5IDIwMy42NDdDNDQuNTQ1MSAyMDMuNjQ3IDU0LjEyODUgMTk5Ljc0MyA2Mi4yOTIxIDE5MS45MzRaTTI4Ljc1MDIgMTc5LjQyM0MyMi43MTYyIDE3OS40MjMgMTkuNjk5MiAxNzYuMjI4IDE5LjY5OTIgMTY5LjgzOUMxOS42OTkyIDE2MC45NjYgMjguMDQwMyAxNTEuODI2IDQ0LjcyMjUgMTQyLjQyTDQyLjMyNjcgMTU4LjkyNUM0MS4yNjE5IDE2Ni4wMjQgMzkuMzA5NyAxNzEuMjU5IDM2LjQ3MDIgMTc0LjYzMUMzMy44MDgxIDE3Ny44MjUgMzEuMjM0OCAxNzkuNDIzIDI4Ljc1MDIgMTc5LjQyM1pNMTc2LjcwNyAyMTQuMjAxQzE3Ny4zIDIxMi44MzYgMTc5LjI5NSAyMTIuODg1IDE3OS42MzQgMjE0LjMzNEMxODAuNDg2IDIxNy45NzMgMTgwLjkyOSAyMjEuNzE0IDE4MC45MjkgMjI1LjUzQzE4MC45MjkgMjYyIDE0MC40MyAyOTEuNTY1IDkwLjQ3MjQgMjkxLjU2NUM0MC41MTQ1IDI5MS41NjUgMC4wMTU2NTQyIDI2MiAwLjAxNTY1MjYgMjI1LjUzQzAuMDE1NjUyNSAyMjEuNzE0IDAuNDU5MTQ4IDIxNy45NzMgMS4zMTA1NiAyMTQuMzM0QzEuNjQ5NDIgMjEyLjg4NSAzLjY0NDc3IDIxMi44MzYgNC4yMzc4NSAyMTQuMjAxQzE1Ljg0MzQgMjQwLjg5NSA1MC4wNjM2IDI2MC4yMzggOTAuNDcyNCAyNjAuMjM4QzEzMC44ODEgMjYwLjIzOCAxNjUuMTAxIDI0MC44OTUgMTc2LjcwNyAyMTQuMjAxWiIgZmlsbD0iYmxhY2siLz4NCjwvc3ZnPg==';

		register_post_type(
	'boomerang',
	apply_filters(
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core auto-generated filter for post type
		'register_post_type_boomerang',
		array(
				'labels'                => array(
						'name'               => $cpt_plural,
						'singular_name'      => $cpt_singular,
						'menu_name'          => $cpt_singular,
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
					'rewrite'               => array(
						'slug'       => boomerang_get_base(),
						'with_front' => false,
					),
					'query_var'             => true,
					'supports'              => array(
						'title',
						'editor',
						'thumbnail',
						'author',
						'comments',
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
					'menu_icon'             => $menu_icon,
				)
			)
		);

		// Register the Boomerang Board Post Type

		$slug         = 'boards';
		$cpt_singular = 'Board';
		$cpt_plural   = 'Boards';
		$menu_icon    = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDE4MSAyOTIiIGZpbGw9Im5vbmUiPg0KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0yNS4wMjMzIDE2Ljc3MUMxMC4xMTU4IDI3Ljc3NDEgMi42NjIwNiA0MS43MDU2IDIuNjYyMDYgNTguNTY1M0MyLjY2MjA2IDY3Ljk3MTIgNi4zMDAyIDc2LjIyMzYgMTMuNTc2NSA4My4zMjI0QzIwLjg1MjggOTAuNDIxMiAzMS4yMzQ4IDk0LjIzNjggNDQuNzIyNSA5NC43NjkyTDQ3LjY1MDggNzUuMDdDNDEuMjYxOSA3NC4zNjAxIDM2LjExNTIgNzIuNDA3OSAzMi4yMTA5IDY5LjIxMzVDMjguMzA2NSA2NS44NDE1IDI2LjM1NDQgNjEuNzU5NyAyNi4zNTQ0IDU2Ljk2OEMyNi4zNTQ0IDQ3LjAyOTcgMzEuODU1OSAzOS4xMzIyIDQyLjg1OTEgMzMuMjc1N0M1My44NjIzIDI3LjI0MTcgNjUuMTMxNiAyNC4yMjQ3IDc2LjY2NzIgMjQuMjI0N0M5NC4yMzY4IDI0LjIyNDcgMTA4LjA3OSAyNy44NjI5IDExOC4xOTUgMzUuMTM5MkMxMjcuNjAxIDQyLjA2MDUgMTMyLjEyNyA1MC41NzkxIDEzMS43NzIgNjAuNjk0OUMxMzEuNTk0IDcwLjgxMDcgMTI3Ljc3OSA3OC40NDE5IDEyMC4zMjUgODMuNTg4NkMxMTMuMDQ5IDg4LjU1NzggMTA0LjA4NiA5MS4wNDIzIDkzLjQzODIgOTEuMDQyM0w5MS4wNDIzIDExMC43NDJDMTA2LjQ4MiAxMTAuNzQyIDExOC42MzkgMTE0LjIwMiAxMjcuNTEyIDEyMS4xMjRDMTM2LjU2MyAxMjguMDQ1IDE0MS4wODkgMTM2LjU2NCAxNDEuMDg5IDE0Ni42NzlDMTQxLjA4OSAxNTYuNzk1IDEzNy44MDYgMTY0Ljk1OSAxMzEuMjM5IDE3MS4xN0MxMjQuNjczIDE3Ny4yMDQgMTE2LjE1NCAxODAuMjIxIDEwNS42ODQgMTgwLjIyMUM5NS4zOTA0IDE4MC4yMjEgODYuMzM5NCAxNzguMTggNzguNTMwNyAxNzQuMDk5TDcwLjI3ODMgMTk2LjQ2Qzc3LjM3NzEgMjAxLjYwNiA5MC42ODc0IDIwNC4xOCAxMTAuMjA5IDIwNC4xOEMxMjkuOTA4IDIwNC4xOCAxNDYuNTkxIDE5OS4yMTEgMTYwLjI1NiAxODkuMjcyQzE3My45MjEgMTc5LjMzNCAxODAuNzU0IDE2NS40OTEgMTgwLjc1NCAxNDcuNzQ0QzE4MC43NTQgMTM0LjI1NiAxNzUuMTYzIDEyMy4wNzYgMTYzLjk4MyAxMTQuMjAyQzE1Mi45NzkgMTA1LjMyOSAxMzkuOTM1IDEwMC4zNiAxMjQuODUgOTkuMjk0N0MxMzYuOTE4IDk4LjkzOTggMTQ3LjIxMiA5NC41MDMgMTU1LjczIDg1Ljk4NDRDMTY0LjI0OSA3Ny40NjU4IDE2OC41MDggNjcuNzkzNyAxNjguNTA4IDU2Ljk2OEMxNjguNjg2IDQ1Ljk2NDggMTY1LjQwMiAzNi4wMjY1IDE1OC42NTkgMjcuMTUzQzE1NC41NzcgMjIuMDA2MyAxNDkuNTE5IDE3LjQ4MDggMTQzLjQ4NSAxMy41NzY1QzEzNy40NTEgOS42NzIxNCAxMjkuMjg3IDYuNDc3NjcgMTE4Ljk5NCAzLjk5MzA4QzEwOC43MDEgMS4zMzEwMyA5Ny4wNzYzIDAgODQuMTIxIDBDNTkuNjMwMSAwIDM5LjkzMDggNS41OTAzMiAyNS4wMjMzIDE2Ljc3MVpNNjIuMjkyMSAxOTEuOTM0QzcwLjQ1NTcgMTgzLjk0OCA3NS45NTczIDE3MC40NiA3OC43OTY5IDE1MS40NzFMOTUuNTY3OCAzNy44MDEyQzkxLjQ4NiAzNi45MTM5IDg3LjIyNjcgMzYuNDcwMiA4Mi43ODk5IDM2LjQ3MDJDNzMuMjA2NSAzNi40NzAyIDY0Ljc3NjcgMzguNzc3MyA1Ny41MDA0IDQzLjM5MTVDNTcuMTQ1NSA0NS44NzYxIDU2LjQzNTYgNTEuMDIyOCA1NS4zNzA4IDU4LjgzMTVDNTQuNDgzNCA2Ni42NDAyIDUzLjE1MjQgNzYuOTMzNCA1MS4zNzc3IDg5LjcxMTNDNDkuNzgwNSAxMDIuMzEyIDQ4LjUzODIgMTEyLjMzOSA0Ny42NTA4IDExOS43OTNDMTUuODgzNiAxMzQuNTIzIDAgMTUyLjM1OCAwIDE3My4zQzAgMTgyLjE3MyAyLjgzOTUzIDE4OS40NSA4LjUxODU4IDE5NS4xMjlDMTQuMTk3NiAyMDAuODA4IDIyLjUzODcgMjAzLjY0NyAzMy41NDE5IDIwMy42NDdDNDQuNTQ1MSAyMDMuNjQ3IDU0LjEyODUgMTk5Ljc0MyA2Mi4yOTIxIDE5MS45MzRaTTI4Ljc1MDIgMTc5LjQyM0MyMi43MTYyIDE3OS40MjMgMTkuNjk5MiAxNzYuMjI4IDE5LjY5OTIgMTY5LjgzOUMxOS42OTkyIDE2MC45NjYgMjguMDQwMyAxNTEuODI2IDQ0LjcyMjUgMTQyLjQyTDQyLjMyNjcgMTU4LjkyNUM0MS4yNjE5IDE2Ni4wMjQgMzkuMzA5NyAxNzEuMjU5IDM2LjQ3MDIgMTc0LjYzMUMzMy44MDgxIDE3Ny44MjUgMzEuMjM0OCAxNzkuNDIzIDI4Ljc1MDIgMTc5LjQyM1pNMTc2LjcwNyAyMTQuMjAxQzE3Ny4zIDIxMi44MzYgMTc5LjI5NSAyMTIuODg1IDE3OS42MzQgMjE0LjMzNEMxODAuNDg2IDIxNy45NzMgMTgwLjkyOSAyMjEuNzE0IDE4MC45MjkgMjI1LjUzQzE4MC45MjkgMjYyIDE0MC40MyAyOTEuNTY1IDkwLjQ3MjQgMjkxLjU2NUM0MC41MTQ1IDI5MS41NjUgMC4wMTU2NTQyIDI2MiAwLjAxNTY1MjYgMjI1LjUzQzAuMDE1NjUyNSAyMjEuNzE0IDAuNDU5MTQ4IDIxNy45NzMgMS4zMTA1NiAyMTQuMzM0QzEuNjQ5NDIgMjEyLjg4NSAzLjY0NDc3IDIxMi44MzYgNC4yMzc4NSAyMTQuMjAxQzE1Ljg0MzQgMjQwLjg5NSA1MC4wNjM2IDI2MC4yMzggOTAuNDcyNCAyNjAuMjM4QzEzMC44ODEgMjYwLjIzOCAxNjUuMTAxIDI0MC44OTUgMTc2LjcwNyAyMTQuMjAxWiIgZmlsbD0iYmxhY2siLz4NCjwvc3ZnPg==';

		register_post_type(
	'boomerang_board',
	apply_filters(
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core auto-generated filter for post type
		'register_post_type_boomerang_board',
		array(
				'labels'                => array(
						'name'               => $cpt_plural,
						'singular_name'      => $cpt_singular,
						'menu_name'          => $cpt_plural,
						// translators: Placeholder %s is the plural label of the boomerang board post type.
						'all_items'          => sprintf( __( 'All %s', 'boomerang' ), $cpt_plural ),
						'add_new'            => __( 'Add New', 'boomerang' ),
						// translators: Placeholder %s is the singular label of the boomerang board post type.
						'add_new_item'       => sprintf( __( 'Add %s', 'boomerang' ), $cpt_singular ),
						'edit'               => __( 'Edit', 'boomerang' ),
						// translators: Placeholder %s is the singular label of the boomerang board post type.
						'edit_item'          => sprintf( __( 'Edit %s', 'boomerang' ), $cpt_singular ),
						// translators: Placeholder %s is the singular label of the boomerang board post type.
						'new_item'           => sprintf( __( 'New %s', 'boomerang' ), $cpt_singular ),
						// translators: Placeholder %s is the singular label of the boomerang board post type.
						'view'               => sprintf( __( 'View %s', 'boomerang' ), $cpt_singular ),
						// translators: Placeholder %s is the singular label of the boomerang board post type.
						'view_item'          => sprintf( __( 'View %s', 'boomerang' ), $cpt_singular ),
						// translators: Placeholder %s is the singular label of the boomerang board post type.
						'search_items'       => sprintf( __( 'Search %s', 'boomerang' ), $cpt_plural ),
						// translators: Placeholder %s is the singular label of the boomerang board post type.
						'not_found'          => sprintf( __( 'No %s found', 'boomerang' ), $cpt_plural ),
						// translators: Placeholder %s is the plural label of the boomerang board post type.
						'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'boomerang' ), $cpt_plural ),
						// translators: Placeholder %s is the singular label of the boomerang board post type.
						'parent'             => sprintf( __( 'Parent %s', 'boomerang' ), $cpt_singular ),
					),
					'public'                => true,
					'show_ui'               => true,
					'capability_type'       => 'post',
					'map_meta_cap'          => true,
					'exclude_from_search'   => false,
					'hierarchical'          => true,
					'rewrite'               => array(
						'slug' => boomerang_board_get_base(),
					),
					'query_var'             => true,
					'supports'              => array(
						'title',
					),
					'show_in_nav_menus'     => true,
					'delete_with_user'      => true,
					'show_in_rest'          => true,
					'rest_base'             => 'boomerang_board',
					'rest_controller_class' => 'WP_REST_Posts_Controller',
					'template'              => array( array( 'core/freeform' ) ),
					'template_lock'         => 'all',
					'menu_position'         => 30,
					'menu_icon'             => $menu_icon,
				)
			)
		);
	}

	/**
	 * Add two settings fields ont he permalinks page to hold our bases.
	 *
	 * @return void
	 */
	public function add_define_slug_setting() {
		add_settings_field(
			'boomerang_board_slug',
			__( 'Boomerang Board base', 'boomerang' ),
			array( $this, 'define_boomerang_board_slug_setting_output' ),
			'permalink',
			'optional'
		);

		add_settings_field(
			'boomerang_slug',
			__( 'Boomerang base', 'boomerang' ),
			array( $this, 'define_boomerang_slug_setting_output' ),
			'permalink',
			'optional'
		);
	}

	/**
	 * Define the base setting for boards.
	 *
	 * @return void
	 */
	public function define_boomerang_board_slug_setting_output() {
		?>
		<input name="boomerang_board_slug" type="text" class="regular-text code" value="<?php echo esc_attr( get_option( 'boomerang_board_base' ) ); ?>" placeholder="<?php echo 'board'; ?>" />
		<?php
	}

	/**
	 * Define the base setting for boomerangs.
	 *
	 * @return void
	 */
	public function define_boomerang_slug_setting_output() {
		?>
		<input name="boomerang_slug" type="text" class="regular-text code" value="<?php echo esc_attr( get_option( 'boomerang_base' ) ); ?>" placeholder="<?php echo esc_attr( 'boomerang' ); ?>" />
		<?php
	}

	/**
	 * Process the base slugs when saved.
	 *
	 * @return void
	 */
	public function save_slug_setting() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled by WordPress settings API
		if ( isset( $_POST['permalink_structure'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled by WordPress settings API
			$board_slug = isset( $_POST['boomerang_board_slug'] ) ? sanitize_title( wp_unslash( $_POST['boomerang_board_slug'] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled by WordPress settings API
			$slug = isset( $_POST['boomerang_slug'] ) ? sanitize_title( wp_unslash( $_POST['boomerang_slug'] ) ) : '';
			
			update_option( 'boomerang_board_base', $board_slug );
			update_option( 'boomerang_base', $slug );
		}
	}
}
