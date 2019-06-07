<?php

/**
 * Sugar Calendar Legacy Theme Event Calendar.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Sets up the HTML, including forms around the calendar
 *
 * @since 1.0.0
 *
 * @param string $size
 * @param mixed  $category
 * @param string $type
 * @param mixed  $year_override
 *
 * @return string
 */
function sc_get_events_calendar( $size = 'large', $category = null, $type = 'month', $year_override = null, $month_override = null ) {

	// Default display time
	$display_time = sugar_calendar_get_request_time();

	// Check for posted display time
	if ( isset( $_POST['sc_nonce'] ) && wp_verify_nonce( $_POST['sc_nonce'], 'sc_calendar_nonce' ) ) {

		if ( isset( $_POST['display_time'] ) ) {
			$display_time = sanitize_text_field( $_POST['display_time'] );

		} elseif ( isset( $_POST['sc_year'] ) && isset( $_POST['sc_month'] ) ) {
			$month_override = sanitize_text_field( $_POST['sc_month'] );
			$year_override  = sanitize_text_field( $_POST['sc_year'] );
			$display_time   = mktime( 0, 0, 0, $month_override, 1, $year_override );
		}
	}

	// Year can be set via function parameter
	if ( ! is_null( $year_override ) ) {
		$today_year = absint( $year_override );
	} else {
		$today_year = date( 'Y', $display_time );
	}

	// Month can be set via function parameter
	if ( ! is_null( $month_override ) ) {
		$today_month = absint( $month_override );
	} else {
		$today_month = date( 'n', $display_time );
	}

	// Category
	$category = isset( $_REQUEST['sc_event_category'] )
		? sanitize_text_field( $_REQUEST['sc_event_category'] )
		: $category;

	// Recalculate display time for $calendar_func below
	$display_time = mktime( 0, 0, 0, $today_month, 1, $today_year );

	$months = array(
		1  => sc_month_num_to_name(1),
		2  => sc_month_num_to_name(2),
		3  => sc_month_num_to_name(3),
		4  => sc_month_num_to_name(4),
		5  => sc_month_num_to_name(5),
		6  => sc_month_num_to_name(6),
		7  => sc_month_num_to_name(7),
		8  => sc_month_num_to_name(8),
		9  => sc_month_num_to_name(9),
		10 => sc_month_num_to_name(10),
		11 => sc_month_num_to_name(11),
		12 => sc_month_num_to_name(12)
	);

	// Arguments for category dropdown
	$args = apply_filters( 'sc_calendar_dropdown_categories_args', array(
		'name'             => 'sc_event_category',
		'id'               => 'sc_event_category',
		'show_option_all'  => __( 'All Calendars', 'sugar-calendar' ),
		'selected'         => $category,
		'value_field'      => 'slug',
		'taxonomy'         => 'sc_event_category',
		'show_option_none' => __( 'No Calendars', 'sugar-calendar' ),
	) );

	// Validate type
	$types = sc_get_valid_calendar_types();
	$type  = in_array( $type, $types, true )
		? $type
		: 'month';

	// Draw function to use
	$calendar_func = "sc_draw_calendar_{$type}";

	$start_year = $today_year - 1;
	$end_year   = $start_year + 5;
	$years      = range( $start_year, $end_year, 1 );

	// Start a buffer
	ob_start();

	do_action( 'sc_before_calendar' ); ?>

	<div id="sc_events_calendar_<?php echo uniqid(); ?>" class="sc_events_calendar sc_<?php echo $size; ?>">
		<div id="sc_events_calendar_head" class="sc_clearfix">
			<form id="sc_event_select" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
				<label for="sc_month" style="display:none"><?php _e('Month', 'sugar-calendar'); ?></label>
				<select class="sc_month" name="sc_month" id="sc_month"><?php
					foreach ( $months as $key => $month ) {
					  echo '<option value="' . absint( $key ) . '" ' . selected( $key, $today_month, false ) . '>' . esc_attr( $month ) . '</option>';
					}
				?></select>
				<label for="sc_year" style="display:none"><?php _e('Year', 'sugar-calendar'); ?></label>
				<select class="sc_year" name="sc_year" id="sc_year"><?php
					foreach ( $years as $year ) {
						echo '<option value="' . absint( $year ) . '" '. selected( $year, $today_year, false ) .'>' . esc_attr( $year ) . '</option>';
					}
				?></select>
				<label for="sc_event_category" style="display:none"><?php _e( 'Calendar', 'sugar-calendar' ); ?></label>
				<?php wp_dropdown_categories( $args ); ?>
				<input type="submit" id="sc_submit" class="sc_calendar_submit" value="<?php _e( 'Go', 'sugar-calendar' ); ?>"/>
				<input type="hidden" name="action" value="sc_load_calendar"/>
				<input type="hidden" name="category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
				<input type="hidden" name="type" value="<?php echo $type; ?>" />
				<input type="hidden" name="sc_nonce" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>" />
				<?php if ( 'small' === $size ) : ?>
					<input type="hidden" name="sc_calendar_size" value="small"/>
				<?php endif; ?>
			</form>

			<?php if ( 'small' !== $size ) : ?>
				<h2 id="sc_calendar_title"><?php echo esc_html( $months[ $today_month ] .' '. $today_year ); ?></h2>

				<?php sc_get_next_prev( $display_time, $size, $category, $type );
			endif; ?>

		</div><!--end #sc_events_calendar_head-->

		<div id="sc_calendar">
			<?php echo call_user_func( $calendar_func, $display_time, $size, $category ); ?>
		</div>

		<?php if ( 'small' === $size ) :
			sc_get_next_prev( $display_time, $size, $category, $type );
		endif; ?>

	</div><!-- end #sc_events_calendar -->
	<?php

	do_action( 'sc_after_calendar' );

	// Return the current buffer
	return ob_get_clean();
}

/**
 * Create the next and previous buttons for the calendar.
 *
 * @since 1.0.0
 *
 * @param $today_month
 * @param $today_year
 * @param string $size
 * @param null $category
 * @deprecated Use sc_get_next_prev() instead.
 */
function sc_calendar_next_prev( $today_month, $today_year, $size = 'large', $category = null ) {
	?>
	<div id="sc_event_nav_wrap">
		<?php
			$next_month = $today_month + 1;
			$next_month = $next_month > 12 ? 1 : $next_month;
			$next_year  = $next_month > 12 ? $today_year + 1 : $today_year;

			$prev_month = $today_month - 1;
			$prev_month = $prev_month < 1 ? 12 : $prev_month;
			$prev_year  = $prev_month < 1 ? $today_year - 1 : $today_year;
		?>
		<form id="sc_event_nav_prev" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" name="sc_month" value="<?php echo absint( $prev_month ); ?>">
			<input type="hidden" name="sc_year" value="<?php echo absint( $prev_year ); ?>">
			<input type="hidden" name="sc_current_month" value="<?php echo absint( $today_month ); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_prev" value="<?php echo esc_html_x( 'Previous', 'Previous month', 'sugar-calendar' ); ?>"/>
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce'); ?>" />
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="action_2" value="prev_month"/>
			<input type="hidden" name="sc_event_category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
			<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			<input type="hidden" name="type" value="month"/>
		</form>
		<form id="sc_event_nav_next" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" name="sc_month" class="month" value="<?php echo absint( $next_month ); ?>">
			<input type="hidden" name="sc_year" class="year" value="<?php echo absint( $next_year ); ?>">
			<input type="hidden" name="sc_current_month" value="<?php echo absint( $today_month ); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_next" value="<?php echo esc_html_x( 'Next', 'Next month', 'sugar-calendar' ); ?>"/>
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>" />
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="action_2" value="next_month"/>
			<input type="hidden" name="sc_event_category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
			<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			<input type="hidden" name="type" value="month"/>
		</form>
	</div>
	<?php
}

/**
 * Output the next and previous buttons for the calendar
 *
 * @since 1.2.0
 *
 * @param int $display_time
 * @param string $size
 * @param null $category
 * @param string $type
 */
function sc_get_next_prev( $display_time, $size = 'large', $category = null, $type = 'month' ) {

	switch ( $type ) {
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
	} ?>

	<div id="sc_event_nav_wrap">
		<form id="sc_event_nav_prev" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_prev" value="<?php _e('Previous', 'sugar-calendar'); ?>"/>
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce'); ?>" />
			<input type="hidden" name="display_time" value="<?php echo $prev_display_time; ?>">
			<input type="hidden" name="type" value="<?php echo $prev_display_time; ?>">
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="sc_event_category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
			<?php if ( 'small' === $size ) { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			<input type="hidden" name="type" value="<?php echo $type; ?>"/>
		</form>
		<form id="sc_event_nav_next" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_next" value="<?php _e('Next', 'sugar-calendar'); ?>"/>
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>" />
			<input type="hidden" name="display_time" value="<?php echo $next_display_time; ?>">
			<input type="hidden" name="action" value="sc_load_calendar"/>
			<input type="hidden" name="sc_event_category" value="<?php echo is_null( $category ) ? 0 : $category; ?>"/>
			<?php if( 'small' === $size ) { ?><input type="hidden" name="sc_calendar_size" value="small"/><?php } ?>
			<input type="hidden" name="type" value="<?php echo $type; ?>"/>
		</form>
	</div>

<?php
}
