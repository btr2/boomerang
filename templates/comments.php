<?php
/**
 * The template for displaying Comments on single Boomerang pages.
 *
 * The area of the page that contains comments and the comment form.
 */
namespace Bouncingsprout_Boomerang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * If the current post is protected by a password and the visitor has not yet
 * entered the password we will return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

if ( is_user_logged_in() ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
	$class_form = 'comment-form';
} else {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
	$class_form = 'comment-form logged-out';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
$comment_form_args = array(
	'label_submit'  => esc_attr__( 'Add comment', 'boomerang' ),
	'title_reply'   => '',
	'logged_in_as'  => '',
	'comment_field' => '<textarea id="comment" name="comment" cols="45" rows="5" maxlength="65525" required="required"></textarea>',
	'class_form'    => $class_form,
);
?>

<div id="comments" class="comments-area">
	<?php comment_form( $comment_form_args ); ?>

	<?php if ( have_comments() ) : ?>
		<h2 class="boomerang-comment-main-title"><?php esc_html_e( 'Activity', 'boomerang' ); ?></h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 36,
					'callback'    => __NAMESPACE__ . '\boomerang_comment_template',
				)
			);
			?>
		</ol><!-- .comment-list -->

	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
		<nav class="navigation comment-navigation" role="navigation">

			<h1 class="screen-reader-text section-heading"><?php esc_html_e( 'Comment navigation', 'boomerang' ); ?></h1>
			<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'boomerang' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'boomerang' ) ); ?></div>
		</nav><!-- .comment-navigation -->
	<?php endif; // Check for comment navigation ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'boomerang' ); ?></p>
	<?php endif; ?>

	<?php endif; // have_comments() ?>



</div><!-- #comments -->
