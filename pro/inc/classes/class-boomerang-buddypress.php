<?php
/**
 * BuddyPress/BuddyBoss Functionality.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang BuddyPress functionality.
 */
class Boomerang_BuddyPress {
	/**
	 * Define the admin functionality of the plugin.
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
		add_action( 'bp_setup_nav', array( $this, 'boomerang_nav_item' ) );
		add_action( 'wp_head', array( $this, 'set_status_colors' ) );
		add_action( 'buddyboss_theme_after_bb_setting_menu', array( $this, 'setup_user_profile_bar' ) );
		add_action( 'boomerang_new_boomerang', array( $this, 'add_activity' ) );

		add_filter( 'bp_get_template_stack', array( $this, 'add_template_stack' ) );
		add_filter( 'bp_nouveau_nav_has_count', array( $this, 'add_count' ), 10, 3 );
		add_filter( 'bp_nouveau_get_nav_count', array( $this, 'get_count' ), 10, 3 );
	}

	/**
	 * Add a new item in a member profile page menu.
	 *
	 * @return void
	 */
	public function boomerang_nav_item() {
		bp_core_new_nav_item(
			array(
				'name'            => ucwords( get_plural_global() ),
				'slug'            => boomerang_get_base(),
				'screen_function' => $this->render_screen(),
			)
		);
	}

	/**
	 * Add Menu in Profile section.
	 *
	 * @param $menus
	 */
	public function setup_user_profile_bar() {
		$user_domain = bp_loggedin_user_domain();
		$item_link   = trailingslashit( $user_domain . boomerang_get_base() );
		?>
		<li id="wp-admin-bar-my-account-boomerang" class="menupop">
			<a class="ab-item" aria-haspopup="true" href="<?php echo esc_attr( $item_link ); ?>">
				<i class="bb-icon-lightbulb bb-icon-l"></i>
				<span class="wp-admin-bar-arrow" aria-hidden="true"></span><?php echo ucwords( get_plural_global() ); ?>
			</a>
		</li>
		<?php
	}

	/**
	 * Adds the Boomerang BP templates to the BP template stack.
	 *
	 * @param $templates
	 *
	 * @return mixed
	 */
	public function add_template_stack( $templates ) {
		$templates[] = BOOMERANG_PATH . 'pro/templates/';

		return $templates;
	}

	/**
	 * Create a screen to display a user's Boomerangs.
	 */
	public function render_screen() {
		do_action( 'boomerang_render_screen_start' );

		add_action( 'bp_template_content', array( $this, 'render_screen_content' ) );

		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Render the content for the user Boomerang screen.
	 *
	 * @return void
	 */
	public function render_screen_content() {
		if ( boomerang_get_base() === bp_current_component() ) {
			bp_get_template_part( 'bp-boomerang' );
		}
	}

	public function set_status_colors() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'boomerang_status',
				'hide_empty' => false,
			)
		);

		if ( ! empty( $terms ) ) {
			echo '<style id="boomerang-status-colors">';
			foreach ( $terms as $term ) {
				$color_meta            = get_term_meta( $term->term_id, 'color', true );
				$background_color_meta = get_term_meta( $term->term_id, 'background_color', true );

				$color            = ! empty( $color_meta ) ? esc_attr( $color_meta ) : '#FFFFFF';
				$background_color = ! empty( $background_color_meta ) ? esc_attr( $background_color_meta ) : '#FFFFFF';

				echo '.boomerang_status-' . $term->slug . ' .data-table-status .boomerang-status {color:' . $color . ';border-color:' . $color . ';background-color:' . $background_color . ';}' . "\r\n";
			}
			echo '</style>';
		}
	}

	/**
	 * Adds a count box to the menu item.
	 *
	 * @param $value
	 * @param $nav_item
	 * @param $nav
	 *
	 * @return bool|mixed
	 */
	public function add_count( $value, $nav_item, $nav ) {
		if ( boomerang_get_base() === $nav_item->slug ) {
			return 0 !== (int) boomerang_get_count( bp_displayed_user_id() );
		}

		return $value;
	}

	/**
	 * Renders the count in the menu item.
	 *
	 * @param $value
	 * @param $nav_item
	 * @param $nav
	 *
	 * @return mixed|string
	 */
	public function get_count( $value, $nav_item, $nav ) {
		if ( boomerang_get_base() === $nav_item->slug ) {
			return boomerang_get_count( bp_displayed_user_id() );
		}

		return $value;
	}

	/**
	 * Check if board activity posts are enabled.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return bool Returns true if board activity posts are enabled, false otherwise.
	 */
	public function board_activity_posts_enabled( $post ) {
		$board = $post->post_parent;

		$meta = get_post_meta( $board, 'boomerang_board_options', true );

		return $meta['bp_activity_enabled'] ?? false;
	}

	public function add_activity( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $this->board_activity_posts_enabled( $post ) ) {
			return;
		}

		$label = get_singular( $post->post_parent );

		// Get our boomerang_guest user
		$guest_user = get_user_by( 'login', 'boomerang_guest' );

		// Check if post is guest created.
		if ( $guest_user && 0 == $post->post_author ) {
			// Post was created by our anonymous guest user
			$author = 'A guest user';
		} else {
			// Must be user created, so use the post author email.
			$author_id = $post->post_author;
			$author    = bp_core_get_userlink( $author_id );
		}

		$action = sprintf(
			esc_attr__( '%1$s posted a new %2$s', 'boomerang' ),
			$author,
			ucwords( $label )
		);

		ob_start();
		include BOOMERANG_PATH . 'pro/templates/bp-boomerang-activity.php';
		$content = ob_get_contents();
		ob_end_clean();

		$activity_id = bp_activity_add(
			array(
				'action'    => $action,
				'content'   => $content,
				'component' => 'boomerang',
				'type'      => 'boomerang',
			)
		);

		return $activity_id;
	}
}
