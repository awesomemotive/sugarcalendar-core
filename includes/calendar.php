<?php

/**
* Sets up the HTML, including forms, around the calendar
*
*/

function sc_get_events_calendar( $year_override = null ) {
	ob_start();

	do_action('sc_before_calendar');
	?>
	<div id="sc_events_calendar_<?php echo uniqid(); ?>" class="sc_events_calendar">
		<div id="sc_events_calendar_head">
			<?php
			$time = current_time('timestamp');

			// default month and year
			$today_month = date('n', $time);
			$today_year = date('Y', $time);

			// check for posted month/year
			if(isset($_POST['sc_nonce']) && wp_verify_nonce($_POST['sc_nonce'], 'sc_calendar_nonce')) {
				$today_month 	= isset( $_POST['sc_month'] )         ? absint( $_POST['sc_month'] )         : date( 'n' );
				$today_year 	= isset( $_POST['sc_year'] )          ? absint( $_POST['sc_year'] )          : date( 'Y' );
				$current_month 	= isset( $_POST['sc_current_month'] ) ? absint( $_POST['sc_current_month'] ) : date( 'n' );
				if( isset( $_POST['sc_prev'] ) ) {
					$today_year = $current_month == 1 ? $today_year - 1 : $today_year;
				} elseif( isset( $_POST['sc_next'] ) ) {
					$today_year = $current_month == 12 ? $today_year + 1 : $today_year;
				}
			}

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
				<select name="sc_month">
				  <?php
				  foreach( $months as $key => $month ) {
				  	echo '<option value="' . absint( $key ) . '" ' . selected( $key, $today_month, false ) . '>'. esc_attr( $month ) .'</option>';
				  }
				  ?>
				</select>
				<select name="sc_year">
					<?php
					$start_year = date('Y') - 1;
					$end_year = $start_year + 5;
					$years = range($start_year, $end_year, 1);
					foreach( $years as $year ) {
						echo '<option value="'. absint( $year ) .'" '. selected( $year, $today_year, false ) .'>'. esc_attr( $year ) .'</option>';
					}
					?>
				</select>
				<input id="sc_submit" type="submit" class="sc_calendar_submit" value="<?php _e('Go', 'pippin_sc'); ?>"/>
				<input type="hidden" name="action" value="sc_load_calendar"/>
				<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>" />
			</form>

			<h2 id="sc_calendar_title"><?php echo esc_html( $months[$today_month] . ' ' . $today_year ); ?></h2>
			<?php echo sc_calendar_next_prev( $today_month, $today_year ); ?>
		</div><!--end #sc_events_calendar_head-->
		<div id="sc_calendar">
			<?php echo sc_draw_calendar( $today_month, $today_year ); ?>
		</div>
	</div><!-- end #sc_events_calendar -->
	<?php
	do_action('sc_after_calendar');
	return ob_get_clean();
}

function sc_calendar_next_prev( $today_month, $today_year ) {
	?>
	<div id="sc_event_nav_wrap">
		<?php
			$next_month = $today_month + 1;
			$next_month = $next_month > 12 ? 1 : $next_month;
			$next_year 	= $next_month > 12 ? $today_year + 1 : $today_year;

			$prev_month = $today_month - 1;
			$prev_month = $prev_month < 1 ? 12 : $prev_month;
			$prev_year 	= $prev_month < 1 ? $today_year - 1 : $today_year;
		?>
		<form id="sc_event_nav_prev" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" name="sc_month" value="<?php echo absint( $prev_month ); ?>">
			<input type="hidden" name="sc_year" value="<?php echo absint( $prev_year ); ?>">
			<input type="hidden" name="sc_current_month" value="<?php echo absint( $today_month ); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_prev" value="<?php _e('Previous', 'pippin_sc'); ?>"/>
			<input type="hidden" name="sc_nonce" value="<?php echo wp_create_nonce( 'sc_calendar_nonce' ) ?>" />
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="action_2" value="prev_month"/>
		</form>
		<form id="sc_event_nav_next" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" name="sc_month" class="month" value="<?php echo absint( $next_month ); ?>">
			<input type="hidden" name="sc_year" class="year" value="<?php echo absint( $next_year ); ?>">
			<input type="hidden" name="sc_current_month" value="<?php echo absint( $today_month ); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_next" value="<?php _e( 'Next', 'pippin_sc' ); ?>"/>
			<input type="hidden" name="sc_nonce" value="<?php echo wp_create_nonce( 'sc_calendar_nonce' ) ?>" />
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="action_2" value="next_month"/>
		</form>
	</div>
	<?php
}