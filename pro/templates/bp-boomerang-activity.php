<?php
/**
 * The template for displaying Boomerang Activity Items.
 */

namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php do_action( 'boomerang_activity_start' ); ?>
<?php echo get_the_post_thumbnail( $post, 'thumbnail', array( 'class' => 'alignleft' ) ); ?>
	<a href="<?php echo esc_url( get_the_permalink( $post ) ); ?>">
		<span><?php echo esc_html( get_the_title( $post ) ); ?></span>
	</a>
	<span><?php echo esc_html( get_the_excerpt( $post ) ); ?></span>
<?php do_action( 'boomerang_activity_end' ); ?>