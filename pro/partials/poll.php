<div class="boomerang-poll">
	<span class="close-button">X</span>
	<form class="boomerang-poll-form">
		<?php use function Bouncingsprout_Boomerang\boomerang_google_fonts_disabled;

		if ( ! empty( $poll['poll_heading'] ) && $poll['poll_heading_show'] ) : ?>
			<h1 class="poll-title"><?php echo esc_html( $poll['poll_heading'] ); ?></h1>
		<?php endif; ?>
		<?php if ( ! empty( $poll['poll_description'] ) ) : ?>
			<h2 class="poll-description"><?php echo esc_html( $poll['poll_description'] ); ?></h2>
		<?php endif; ?>
		<?php
		if ( ! empty( $poll['poll_boomerangs'] ) ) {
			echo '<div class="poll-options">';
			foreach ( $poll['poll_boomerangs'] as $boomerang ) {
				echo '<fieldset class="poll-option">';
				echo '<input class="poll-option" name="boomerang_poll_' . esc_attr( $poll['poll_id'] ) . '" type="radio" id="boomerang-' . esc_attr( $boomerang ) . '" value="' . esc_attr( $boomerang ) . '">';
				echo '<label for="boomerang-' . esc_attr( $boomerang ) . '">';
				echo '<h3>' . esc_html( get_the_title( $boomerang ) ) . '</h3>';
				$excerpt = get_the_excerpt( $boomerang );

				$excerpt = substr( $excerpt, 0, 100 );
				$result  = substr( $excerpt, 0, strrpos( $excerpt, ' ' ) );
				echo '<p>' . esc_html( $result ) . '...</p>';
				echo '</label>';
				echo '</fieldset>';
			}
			echo '</div>';
		}
		?>
		<div class="poll-footer">
			<?php if ( ! empty( $poll['poll_null_enabled'] ) ) : ?>
				<div class="poll-none">
					<input class="poll-option" name="boomerang_poll_<?php echo esc_attr( $poll['poll_id'] ); ?>" type="radio" id="boomerang-none_<?php echo esc_attr( $poll['poll_id'] ); ?>" value="none">
					<label for="boomerang-none_<?php echo esc_attr( $poll['poll_id'] ); ?>" class="poll-none"><?php echo esc_html( $poll['poll_null_label'] ); ?></label>
				</div>
			<?php endif; ?>
			<input class="boomerang-poll-submit" type="submit">
		</div>
	</form>
	<div class="boomerang-poll-message">
		<div class="boomerang-poll-message-content">
			<?php if ( ! boomerang_google_fonts_disabled() ) : ?>
				<span class="material-symbols-outlined icon">verified</span>
			<?php endif; ?>
			<?php if ( ! empty( $poll['poll_success_message'] ) ) : ?>
			<h2><?php echo esc_html( $poll['poll_success_message'] ); ?></h2>
			<?php endif; ?>
		</div>
		<button class="poll-success-close-button"><?php echo esc_html_e( 'Done', 'boomerang' ); ?></button>
	</div>
</div>

