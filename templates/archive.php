<?php
/**
 * The template for displaying all single Boomerang Boards (which are actually archives).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$board = get_the_ID();

echo do_shortcode( "[boomerang board='{$board}']" );

get_footer();

?>

