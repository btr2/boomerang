<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Conditionals */

/**
 * Checks whether drafts should be retrieved.
 *
 * @return true
 */
function boomerang_show_drafts() {
	return true;
}

/**
 * Checks whether the tag system is enabled.
 *
 * @return true
 */
function boomerang_tags_enabled() {
	return true;
}

/**
 * Checks whether the status system is enabled.
 *
 * @return true
 */
function boomerang_statuses_enabled() {
	return true;
}

/** Labels */

/**
 * Gets the title label from settings.
 *
 * @return true
 */
function boomerang_label_title() {
	$label = 'Title';

	return apply_filters( 'boomerang_label_title', $label );
}

/**
 * Gets the content label from settings.
 *
 * @return true
 */
function boomerang_label_content() {
	$label = 'Content';

	return apply_filters( 'boomerang_label_content', $label );
}

/**
 * Gets the submit label from settings.
 *
 * @return true
 */
function boomerang_label_submit() {
	$label = 'Submit';

	return apply_filters( 'boomerang_label_submit', $label );
}
