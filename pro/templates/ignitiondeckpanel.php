
	<?php echo ( isset( $float ) ? '<div class="id-widget-wrap id-complete-deck">' : '<div class="id-widget-wrap nofloat">' ); ?>
	<div class="ignitiondeck id-widget id-full" data-projectid="<?php echo esc_attr( isset( $project_id ) ? $project_id : '' ); ?>">
	<div class="id-product-infobox">
		<div class="product-wrapper">
			<?php
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- IgnitionDeck plugin action
			do_action( 'id_widget_before', $project_id, $the_deck );
			?>
            <!-- Hook to insert social sharing tools -->
            <div id="ignitiondeck_share_public_project_page">
                <?php 
                    global $post;
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- IgnitionDeck plugin action
                    do_action( 'ignitiondeck_share_public_project_page', $post->ID );
                ?>
			</div>
			<div class="pledge">
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_title'] ) ) ) { ?>
					<h2 class="id-product-title"><a href="<?php echo esc_url( getProjectURLfromType( $project_id ) ); ?>"><?php echo esc_html( stripslashes( get_the_title( $the_deck->post_id ) ) ); ?></a></h2>
				<?php } ?>
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_description'] ) ) ) { ?>
					<!-- Project description -->
					<div class="id-product-description"><?php echo wp_kses_post( $the_deck->project_desc ); ?></div>
					<!-- end id product description -->
				<?php } ?>
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_bar'] ) ) ) { ?>
				<div class="progress-wrapper">
					<div class="progress-percentage"> <?php echo esc_html( $the_deck->rating_per ); ?>% </div>
					<div class="progress-bar-wrapper">
						<div class="progress-bar" style="width: <?php echo esc_attr( $the_deck->rating_per ); ?>%">
						</div>
						</div>
						<!-- end progress bar --> 
					</div>
					<!-- end progress wrapper --> 
					<?php } ?>
				</div>
				
				<!-- end pledge -->
				
				<div class="project-metrics">
			<div class="raised-metric">
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_pledged'] ) ) ) { ?>
					<div class="id-progress-raised"> <?php echo esc_html( $the_deck->p_current_sale ); ?> </div>
				<?php } ?>
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_goal'] ) ) ) { ?>
					<div class="id-product-funding"><?php esc_html_e( 'Pledged of', 'boomerang' ); ?> <?php echo esc_html( $the_deck->item_fund_goal ); ?> <?php esc_html_e( 'Goal', 'boomerang' ); ?></div>
				<?php } ?>
					</div>

		<?php if ( ! $custom || ( $custom && isset( $attrs['project_pledgers'] ) ) ) { ?>
			<div class="pledger-metric">
			<div class="id-product-total"><?php echo esc_html( $the_deck->p_count->p_number ); ?></div>
			<div class="id-product-pledges"><?php esc_html_e( 'Pledgers', 'boomerang' ); ?></div>
			</div>
		<?php } ?>

					<?php if ( ! $custom || ( $custom && isset( $attrs['days_left'] ) )  || ( $custom && isset( $attrs['project_end'] ) ) ) { ?>

					<div class="date-metric">
						<div class="days-left-metric">
		<?php if ( ! $custom || ( $custom && isset( $attrs['days_left'] ) ) ) { ?>
			<?php if ( isset( $the_deck->days_left ) && $the_deck->days_left > 0 ) { ?>
				<div class="id-product-days"><?php echo esc_html( ( $the_deck->days_left !== '' || $the_deck->days_left !== 0 ) ? $the_deck->days_left : '0' ); ?></div>
				<div class="id-product-days-to-go"><?php esc_html_e( 'Days Left', 'boomerang' ); ?></div>
			<?php } ?>
		<?php } ?>
						</div>
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_end'] ) ) ) { ?>
					<?php if ( $the_deck->item_fund_end !== '' ) { ?>
						<div class="id-product-proposed-end"><?php echo esc_html( $the_deck->days_left > 0 ? __( 'Ends on', 'boomerang' ) : __( 'Ended On', 'boomerang' ) ); ?>
							<div class="id-widget-date">
								<div class="id-widget-month"><?php echo esc_html( $the_deck->month ); ?></div>
								<div class="id-widget-day"><?php echo esc_html( $the_deck->day ); ?></div>
								<div class="id-widget-year"><?php echo esc_html( $the_deck->year ); ?></div>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
					</div>

					<?php } ?>
			</div>
			
			<!-- end product-wrapper -->	

		</div>
		<div class="separator">&nbsp;</div>
		<?php if ( ! $custom || ( $custom && isset( $attrs['project_button'] ) ) ) { ?>
			<div class="ign-supportnow" data-projectid="<?php echo esc_attr( isset( $project_id ) ? $project_id : '' ); ?>">
			<?php
			if ($the_deck->end_type == 'closed' && $the_deck->days_left <= 0) {
				?>
				<a href="" class="button"><?php esc_html_e('Project Closed', 'boomerang');?></a>
				<?php
			} else {
				if (function_exists('is_id_licensed') && is_id_licensed()) {
					if (empty($permalinks) || $permalinks == '') {
						echo '<a class="button" href="'.esc_url(get_permalink($project_id).'&purchaseform=500&amp;prodid='.( isset( $project_id ) ? $project_id : '' )).'">'.esc_html__('Support Now', 'boomerang').'</a>';
					} else {
						echo '<a class="button" href="'.esc_url(get_permalink($project_id).'?purchaseform=500&amp;prodid='.( isset( $project_id ) ? $project_id : '' )).'">'.esc_html__('Support Now', 'boomerang').'</a>';
					}
				}
			}
			?>
				</div>
			<?php } ?>
			

		<?php
		if ( ! $custom || ( $custom && isset( $attrs['project_levels'] ) ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
			$permalink_structure = get_option('permalink_structure');
			if (empty($permalink_structure)) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
				$url_suffix = '&';
			}
			else {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
				$url_suffix = '?';
			}
			global $post;
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
			$url           = get_permalink($post->ID).$url_suffix.'purchaseform=500&prodid='.$project_id;//getPurchaseURLFromType( $project_id, 'purchaseform' );
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable
			$level_invalid = getLevelLimitReached( $project_id, $the_deck->post_id, 1 );
			?>
		<!--Product Levels-->
			<div class="id-product-levels">
				<?php
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Loop variable
				foreach ( $the_deck->level_data as $level ) {
						if ( ! is_id_licensed() ) {
							$level->level_invalid = 1;
						}
						/*if ( isset( $the_deck->end_type ) && $the_deck->end_type == 'closed' ) {
							if ( isset( $the_deck->days_left ) && $the_deck->days_left > 0 ) {
								?>
								<a class="level-binding" <?php echo ( ! isset( $level->level_invalid ) || $level->level_invalid ? '' : 'href="' . apply_filters( 'id_level_' . $level->id . '_link', $url . '&level=' . $level->id, $project_id ) . '"' ); ?>>
								<?php
							} else {
								?>
								<a class="level-binding" <?php echo ( isset( $level->level_invalid ) && $level->level_invalid ? '' : '' ); ?>>
								<?php
							}
						} else {
							?>
							<a class="level-binding" <?php echo ( ! isset( $level->level_invalid ) || $level->level_invalid ? '' : 'href="' . apply_filters( 'id_level_' . $level->id . '_link', $url . '&level=' . $level->id, $project_id ) . '"' ); ?>>
						<?php }*/
						if ($the_deck->end_type == 'closed' && $the_deck->days_left <= '0') { ?>
							<a class="level-binding">
							<?php
					} 
					else {
					?>
						<a class="level-binding" <?php echo (isset($level->level_invalid) && $level->level_invalid ? '' : 'href="'.esc_url(apply_filters('id_level_'.$level->id.'_link', $url.'&level='.$level->id, $project_id)).'"'); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- IgnitionDeck plugin filter */ ?>>
						<?php 
						}
		?>
		<div class="level-group">
			<div class="id-level-title"><span><?php echo esc_html( isset( $level->meta_title ) ? wp_strip_all_tags( stripslashes( $level->meta_title ) ) : __( 'Level', 'boomerang' ) . ' ' . ( $level->id ) ); ?>:</span> <?php echo esc_html( isset( $level->meta_price ) && $level->meta_price > 0 ? apply_filters( 'id_price_selection', $level->meta_price, $the_deck->post_id ) : '' ); /* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- IgnitionDeck plugin filter */ ?></div>
			<div class="id-level-desc"><?php echo wp_kses_post( html_entity_decode( stripslashes( $level->meta_desc ) ) ); ?></div>
			<?php echo ( ! empty( $level->meta_limit ) ? '<div class="id-level-counts"><span>' . esc_html__( 'Limit', 'boomerang' ) . ': ' . esc_html( $level->meta_count ) . ' ' . esc_html__( 'of', 'boomerang' ) . ' ' . esc_html( $level->meta_limit ) . ' ' . esc_html__( 'Taken', 'boomerang' ) . '</span></div>' : '' ); ?>
			<?php
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- IgnitionDeck plugin action
			do_action( 'id_after_level', $level );
			?>
			</div>
				</a>
				<?php } ?>
		</div>
		<!-- end product levels -->
		<?php
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- IgnitionDeck plugin action
		do_action( 'id_after_levels', $project_id, $the_deck );
		?>
		<?php } ?>
	<?php
	if ( $the_deck->settings->id_widget_logo_on ) {
		?>
	<div class="poweredbyID"><span><a href="<?php echo esc_url( $the_deck->affiliate_link ); ?>" title="<?php esc_attr_e( 'Crowdfunding by IgnitionDeck', 'boomerang' ); ?>"><?php esc_html_e( 'Powered By IgnitionDeck', 'boomerang' ); ?></a></span></div>
	<?php } ?>
	</div>
	<!-- end product-infobox -->
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- IgnitionDeck plugin action
	do_action( 'id_widget_after', $project_id, $the_deck );
	?>
	</div>
	<!-- end id-widget -->
	<?php echo ( isset( $float ) ? '</div>' : '</div>' ); ?>

