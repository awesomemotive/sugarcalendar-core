<?php

/**
 * Sets up the HTML, including forms, around the calendar
 *
 * @param string $size
 * @param null $category
 * @param string $type
 * @param null $year_override
 *
 * @return string
 */
function sc_get_events_calendar( $size = 'large', $category = null, $type = 'month', $year_override = null ) {
	ob_start();

	do_action('sc_before_calendar');
	?>
	<div id="sc_events_calendar_<?php echo uniqid(); ?>" class="sc_events_calendar sc_<?php echo $size; ?>">
		<div id="sc_events_calendar_head" class="sc_clearfix">
			<?php
			// Default display to today
			$display_time = current_time('timestamp');

			// check for posted display time
			if(isset($_POST['sc_nonce']) && wp_verify_nonce($_POST['sc_nonce'], 'sc_calendar_nonce')) {
				if ( isset( $_POST['display_time'] ) ) {
					$display_time = $_POST['display_time'];
				}
				if ( isset( $_POST['sc_year']) && isset( $_POST['sc_month'] ) ){
					$display_time = mktime( 0, 0, 0, $_POST['sc_month'], 1, $_POST['sc_year'] );
				}
			}
			$category = isset( $_REQUEST['sc_category'] ) ? sanitize_text_field( $_REQUEST['sc_category'] ) : $category;

			$today_day   = date('j', $display_time);
			$today_month = date('n', $display_time);
			$today_year  = date('Y', $display_time);
			
			// Year can be set via function parameter
			if( !is_null( $year_override ) )
				$today_year = absint( $year_override );

			$months = array(
				1 => sc_month_num_to_name(1),
				2 => sc_month_num_to_name(2),
				3 => sc_month_num_to_name(3),
				4 => sc_month_num_to_name(4),
				5 => sc_month_num_to_name(5),
				6 => sc_month_num_to_name(6),
				7 => sc_month_num_to_name(7),
				8 => sc_month_num_to_name(8),
				9 => sc_month_num_to_name(9),
				10 => sc_month_num_to_name(10),
				11 => sc_month_num_to_name(11),
				12 => sc_month_num_to_name(12)
			);
			?>
			<form id="sc_event_select" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
				<label for="sc_month" style="display:none"><?php _e('Month', 'pippin_sc'); ?></label>
				<select class="sc_month" name="sc_month" id="sc_month">
				  <?php
				  foreach($months as $key => $month) {
				  	echo '<option value="' . absint( $key ) . '" ' . selected($key, $today_month, false) . '>'. esc_attr( $month ) .'</option>';
				  }
				  ?>
				</select>
				<label for="sc_year" style="display:none"><?php _e('Year', 'pippin_sc'); ?></label>
				<select class="sc_year" name="sc_year" id="sc_year">
					<?php
					$start_year = date('Y') - 1;
					$end_year = $start_year + 5;
					$years = range($start_year, $end_year, 1);
					foreach($years as $year) {
						echo '<option value="'. absint( $year ) .'" '. selected($year, $today_year, false) .'>'. esc_attr( $year ) .'</option>';
					}
					?>
				</select>
				<?php
				$args = apply_filters( 'sc_calendar_dropdown_categories_args', array(
					'name'             => 'sc_category',
					'id'               => 'sc_category',
					'show_option_all'  => __( 'All Categories', 'pippin_sc' ),
					'selected'         => $category,
					'value_field'      => 'slug',
					'taxonomy'         => 'sc_event_category',
					'show_option_none' => __( 'No Categories', 'pippin_sc' ),
				));
				wp_dropdown_categories( $args );
				?>
				<input id="sc_submit" type="submit" class="sc_calendar_submit" value="<?php _e('Go', 'pippin_sc'); ?>"/>
				<input type="hidden" name="action" value="sc_load_calendar"/>
				<input type="hidden" name="category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
				<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>" />
				<input type="hidden" name="type" value="<?php echo $type; ?>" />
				<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			</form>
			<?php if($size != 'small') { ?>
				<h2 id="sc_calendar_title"><?php echo esc_html( $months[$today_month] .' '. $today_year ); ?></h2>
			<?php
				sc_get_next_prev($display_time, $size, $category, $type);
			} ?>
		</div><!--end #sc_events_calendar_head-->
		<div id="sc_calendar">
			<?php
			$draw_calendar = 'sc_draw_calendar_' . $type;
			echo $draw_calendar($display_time, $size, $category);
			?>
		</div>
		<?php if($size == 'small') { sc_get_next_prev($display_time, $size, $category, $type); } ?>
	</div><!-- end #sc_events_calendar -->
	<?php
	do_action('sc_after_calendar');
	return ob_get_clean();
}

/**
 * Create the next and previous buttons for the calendar.
 *
 * @param $today_month
 * @param $today_year
 * @param string $size
 * @param null $category
 * @deprecated Use sc_get_next_prev() instead.
 */
function sc_calendar_next_prev($today_month, $today_year, $size = 'large', $category = null) {
	?>
	<div id="sc_event_nav_wrap">
		<?php
			$next_month = $today_month + 1;
			$next_month = $next_month > 12 ? 1 : $next_month;
			$next_year = $next_month > 12 ? $today_year + 1 : $today_year;

			$prev_month = $today_month - 1;
			$prev_month = $prev_month < 1 ? 12 : $prev_month;
			$prev_year = $prev_month < 1 ? $today_year - 1 : $today_year;
		?>
		<form id="sc_event_nav_prev" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" name="sc_month" value="<?php echo absint( $prev_month ); ?>">
			<input type="hidden" name="sc_year" value="<?php echo absint( $prev_year ); ?>">
			<input type="hidden" name="sc_current_month" value="<?php echo absint( $today_month ); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_prev" value="<?php _e('Previous', 'pippin_sc'); ?>"/>
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce'); ?>" />
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="action_2" value="prev_month"/>
			<input type="hidden" name="sc_category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
			<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			<input type="hidden" name="type" value="month"/>
		</form>
		<form id="sc_event_nav_next" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" name="sc_month" class="month" value="<?php echo absint( $next_month ); ?>">
			<input type="hidden" name="sc_year" class="year" value="<?php echo absint( $next_year ); ?>">
			<input type="hidden" name="sc_current_month" value="<?php echo absint( $today_month ); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_next" value="<?php _e('Next', 'pippin_sc'); ?>"/>
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>" />
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="action_2" value="next_month"/>
			<input type="hidden" name="sc_category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
			<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			<input type="hidden" name="type" value="month"/>
		</form>
	</div>
	<?php
}

/**
 * Output the next and previous buttons for the calendar
 *
 * @param int $display_time
 * @param string $size
 * @param null $category
 * @param string $type
 */
function sc_get_next_prev($display_time, $size = 'large', $category = null, $type = 'month') {
	?>
	<div id="sc_event_nav_wrap">
		<?php
		switch ( $type) {
			case 'day':
				$next_display_time = strtotime( '+1 day', $display_time );
				$prev_display_time = strtotime( '-1 day', $display_time );
				break;
			case '4day':
				$next_display_time = strtotime( '+4 day', $display_time );
				$prev_display_time = strtotime( '-4 day', $display_time );
				break;
			case 'week':
				$next_display_time = strtotime( '+1 week', $display_time );
				$prev_display_time = strtotime( '-1 week', $display_time );
				break;
			case '2week':
				$next_display_time = strtotime( '+2 week', $display_time );
				$prev_display_time = strtotime( '-2 week', $display_time );
				break;
			default:
				$next_display_time = strtotime( '+1 month', $display_time );
				$prev_display_time = strtotime( '-1 month', $display_time );
		}
		?>
		<form id="sc_event_nav_prev" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_prev" value="<?php _e('Previous', 'pippin_sc'); ?>"/>
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce'); ?>" />
			<input type="hidden" name="display_time" value="<?php echo $prev_display_time; ?>">
			<input type="hidden" name="type" value="<?php echo $prev_display_time; ?>">
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="sc_category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
			<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			<input type="hidden" name="type" value="<?php echo $type; ?>"/>
		</form>
		<form id="sc_event_nav_next" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_next" value="<?php _e('Next', 'pippin_sc'); ?>"/>
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>" />
			<input type="hidden" name="display_time" value="<?php echo $next_display_time; ?>">
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="sc_category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
			<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			<input type="hidden" name="type" value="<?php echo $type; ?>"/>
		</form>
	</div>
<?php
}
