<?php
/**
 * The template for displaying all single Boomerang Boards (which are actually archives).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
$board = get_the_ID();

echo do_shortcode( "[boomerang board='{$board}']" );

get_footer();

?>

