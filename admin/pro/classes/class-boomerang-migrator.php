<?php

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Boomerang Migration functionality.
 */
class Boomerang_Migrator {
	/**
	 * Define the Migration functionality of the plugin.
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
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'wp_ajax_process_sfr', array( $this, 'process_sfr' ) );
	}

	/**
	 * Add a settings page.
	 *
	 * @return void
	 */
	public function add_plugin_page() {
		add_submenu_page(
			'edit.php?post_type=boomerang',
			esc_html__( 'Data Import', 'boomerang' ),
			esc_html__( 'Data Import', 'boomerang' ),
			'manage_options',
			'boomerang_data_import',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Render Data Import page.
	 *
	 * @return void
	 */
	public function create_admin_page() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html__( 'Data Import', 'boomerang' ); ?></h2>
			<p><?php echo esc_html__( 'Use the settings below to migrate ideas from other feature request platforms to Boomerang', 'boomerang' ); ?></p>
			<div id="accordion-container">
			<?php if ( boomerang_is_simple_feature_requests_active() ) : ?>

			<div id="simple-feature-requests" class="accordion-item" data-nonce="<?php echo wp_create_nonce( 'boomerang-sfr-import-nonce'); ?>">
				<div class="accordion-header"><?php echo esc_html__( 'Simple Feature Requests', 'boomerang' ); ?></div>
				<div class="accordion-body">
					<?php

					$args  = array(
						'numberposts' => -1,
						'post_type'   => 'cpt_feature_requests',
					);
					$posts = get_posts( $args );

					if ( empty( $posts ) ) {
						echo '<p>' . esc_html__( 'No Simple Feature Requests have bene created', 'boomerang' ) . '</p>';
					}

					if ( ! empty( $posts ) ) :
						?>

						<?php
						$count = count( $posts );
						echo '<p>';
						printf(
							esc_html(
								/* translators: the number of found Simple Feature Requests */
								_n(
									'We found %d Simple Feature Request.',
									'We found %d Simple Feature Requests.',
									esc_attr( $count ),
									'boomerang'
								)
							),
							esc_attr( $count )
						);
						echo '</p>';

						?>

						<p><?php esc_html_e( 'When you click the Import button, the following things will happen:', 'boomerang' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'The title, content, author and date will be used to create a new Boomerang.', 'boomerang' ); ?></li>
						<li><?php esc_html_e( 'Any existing votes will be imported.', 'boomerang' ); ?></li>
						<li><?php esc_html_e( 'If you select a board, the Boomerang will automatically link to that board. If you haven\'t created any boards, we recommend you do that first.', 'boomerang' ); ?></li>
						<li><?php esc_html_e( 'If you tick the checkbox, any existing comments for this feature request will be assigned to the new Boomerang. This effectively moves the comments, rather than copies them.', 'boomerang' ); ?></li>
						<li><?php esc_html_e( 'You will need to set the Boomerang status manually.', 'boomerang' ); ?></li>
					</ul>

					<?php endif; ?>
					<div class="controls">
						<?php
						$dropdown_args = array(
							'post_type'        => 'boomerang_board',
							'name'             => 'board',
							'show_option_none' => __('None (I will set manually)'),
						);

						$boards = wp_dropdown_pages( $dropdown_args );
						?>

						<label for="move-comments-sfr">
							<?php esc_html_e( 'Also move comments?', 'boomerang' ); ?>
							<input type="checkbox" id="move-comments-sfr" name="move_comments">
						</label>

						<button id="import-button-sfr" class="button button-primary button-large import-button">
							<?php esc_html_e( 'Import Now', 'boomerang' ); ?>
							<span class="import-spinner"></span>
						</button>
						<span class="import-result"></span>
					</div>



				</div>
			</div>

			<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Ajax Handler for processing Simple Feature Requests.
	 *
	 * @return void
	 */
	public function process_sfr() {
		if ( ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
			'boomerang-sfr-import-nonce'
		) ) {
			$error = new \WP_Error(
				'Boomerang: Failed Security Check on Import',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );

			wp_die();
		}

		$args  = array(
			'numberposts' => -1,
			'post_type'   => 'cpt_feature_requests',
		);
		$posts = get_posts( $args );

		if ( empty( $posts ) )  {
			$error = new \WP_Error(
				'Boomerang: No posts to import',
				__( 'Something went wrong.', 'boomerang' )
			);

			wp_send_json_error( $error );

			wp_die();
		}

		$board = $_POST['board'] ?? '';
		$boomerangs = array();

		foreach ( $posts as $post ) {
			$args = array(
				'post_title'     => sanitize_text_field( $post->post_title ),
				'post_content'   => wp_kses_post( $post->post_content ),
				'post_author'    => intval( $post->post_author ),
				'post_date'      => sanitize_text_field( $post->post_date ),
				'post_parent'    => intval( $board ),
				'post_status'    => 'publish',
				'post_type'      => 'boomerang',
				'comment_status' => 'open',
				'meta_input'   => array(
					'imported_from' => 'sfr',
					'boomerang_votes' => intval( get_post_meta( $post->ID, 'jck_sfr_votes', true ) ),
				),
			);

			$post_id = wp_insert_post( $args, true );
			$boomerangs[] = $post_id;

			if ( 'yes' === $_POST['move_comments'] ) {
				$comments = get_comments( array( 'post_id' => $post->ID ) );

				foreach ( $comments as $comment ) :
					$commentarr = array();
					$commentarr['comment_ID'] = $comment->comment_ID;
					$commentarr['comment_post_ID'] = $post_id;
					wp_update_comment( $commentarr );

				endforeach;
			}
		}

		$count = count( $boomerangs );
		$message = sprintf(
			esc_html(
			/* translators: the number of found Simple Feature Requests */
				_n(
					'%d Simple Feature Request successfully imported',
					'%d Simple Feature Requests successfully imported',
					esc_attr( $count ),
					'boomerang'
				)
			),
			esc_attr( $count )
		);

		$return = array(
			'message' => $message,
		);

		wp_send_json_success( $return );

		wp_die();

	}
}

if ( boomerang_is_simple_feature_requests_active() ) {
	$boomerang_migrator = new Boomerang_Migrator();
}
