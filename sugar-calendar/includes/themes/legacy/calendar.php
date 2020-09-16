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
 * @param mixed  $month_override
 *
 * @return string
 */
function sc_get_events_calendar( $size = 'large', $category = null, $type = 'month', $year_override = null, $month_override = null ) {

	// Default display time
	$display_time = sugar_calendar_get_request_time();

	// Check for posted display time, year, or month
	if ( ! empty( $_POST['sc_nonce'] ) && wp_verify_nonce( $_POST['sc_nonce'], 'sc_calendar_nonce' ) ) {

		// Trust the new display_time value
		if ( ! empty( $_POST['display_time'] ) ) {
			$display_time = sanitize_text_field( $_POST['display_time'] );
		}

		// Override the Year & Month overrides
		if ( ! empty( $_POST['sc_year'] ) && ! empty( $_POST['sc_month'] ) ) {
			$month_override = sanitize_text_field( $_POST['sc_month'] );
			$year_override  = sanitize_text_field( $_POST['sc_year'] );
		}
	}

	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Category
	$category = ! empty( $_REQUEST[ $tax ] )
		? sanitize_text_field( $_REQUEST[ $tax ] )
		: $category;

	// Year can be set via function parameter
	$display_year = ! is_null( $year_override )
		? absint( $year_override )
		: date( 'Y', $display_time );

	// Month can be set via function parameter
	$display_month = ! is_null( $month_override )
		? absint( $month_override )
		: date( 'n', $display_time );

	// Day is always derived from time (for week & 4day views)
	$display_day = date( 'j', $display_time );

	// Recalculate display time for $calendar_func below
	$display_time = mktime( 0, 0, 0, $display_month, $display_day, $display_year );

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

	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Arguments for category dropdown
	$args = apply_filters( 'sc_calendar_dropdown_categories_args', array(
		'name'             => $tax,
		'id'               => $tax,
		'show_option_all'  => __( 'All Calendars', 'sugar-calendar' ),
		'selected'         => $category,
		'value_field'      => 'slug',
		'taxonomy'         => $tax,
		'show_option_none' => __( 'No Calendars', 'sugar-calendar' ),
	) );

	// Validate type
	$types = sc_get_valid_calendar_types();
	$type  = in_array( $type, $types, true )
		? $type
		: 'month';

	// Trim trailing 's' if is a mistakenly plural type
	// See: https://github.com/sugarcalendar/standard/issues/300
	if ( in_array( $type, array( '4days', '2weeks' ), true ) ) {
		$type = trim( $type, 's' );
	}

	// Draw function to use
	$calendar_func = "sc_draw_calendar_{$type}";

	// Ranges
	$years_back    = 1;
	$years_forward = 5;

	// Come up with a 6 year range
	$start_year    = $display_year - $years_back;
	$end_year      = $start_year + $years_forward;
	$years         = range( $start_year, $end_year, 1 );

	// Start a buffer
	ob_start();

	do_action( 'sc_before_calendar' ); ?>

	<div id="sc_events_calendar_<?php echo uniqid(); ?>" class="sc_events_calendar sc_<?php echo esc_attr( $size ); ?>">
		<div id="sc_events_calendar_head" class="sc_clearfix">
			<form id="sc_event_select" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">

				<label for="sc_month" style="display:none"><?php esc_html_e( 'Month', 'sugar-calendar' ); ?></label>
				<select class="sc_month" name="sc_month" id="sc_month"><?php

					foreach ( $months as $key => $month ) : ?>

					  <option value="<?php echo absint( $key ); ?>" <?php selected( $key, $display_month ); ?>><?php echo esc_html( $month ); ?></option>

					<?php endforeach;

				?></select>

				<label for="sc_year" style="display:none"><?php esc_html_e( 'Year', 'sugar-calendar' ); ?></label>
				<select class="sc_year" name="sc_year" id="sc_year"><?php

					foreach ( $years as $year ) : ?>

						<option value="<?php echo absint( $year ); ?>" <?php selected( $year, $display_year ); ?>><?php echo esc_html( $year ); ?></option>

					<?php endforeach;

				?></select>

				<label for="<?php echo esc_attr( $tax ); ?>" style="display:none"><?php esc_html_e( 'Calendar', 'sugar-calendar' ); ?></label>
				<?php wp_dropdown_categories( $args ); ?>

				<input type="submit" id="sc_submit" class="sc_calendar_submit" value="<?php esc_attr_e( 'Go', 'sugar-calendar' ); ?>">
				<input type="hidden" name="action" value="sc_load_calendar">
				<input type="hidden" name="category" value="<?php echo is_null( $category ) ? 0 : esc_attr( $category ); ?>">
				<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>">
				<input type="hidden" name="sc_nonce" value="<?php echo wp_create_nonce( 'sc_calendar_nonce' ); ?>">

				<?php if ( 'small' === $size ) : ?>
					<input type="hidden" name="sc_calendar_size" value="small">
				<?php endif; ?>
			</form>

			<?php if ( 'small' !== $size ) : ?>

				<h2 id="sc_calendar_title"><?php
					echo esc_html( $months[ $display_month ] . ' ' . $display_year );
				?></h2>

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
 * Deprecated. Do not use.
 *
 * @since 1.0.0
 * @deprecated Use sc_get_next_prev() instead.
 *
 * @param $display_month
 * @param $display_year
 * @param string $size
 * @param null $category
 */
function sc_calendar_next_prev( $display_month, $display_year, $size = 'large', $category = null ) {

	// Formally deprecated
	_deprecated_function( __FUNCTION__, '1.1.0', 'sc_get_next_prev' );

	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Next
	$next_month = $display_month + 1;
	$next_month = $next_month > 12 ? 1 : $next_month;
	$next_year  = $next_month > 12 ? $display_year + 1 : $display_year;

	// Prev
	$prev_month = $display_month - 1;
	$prev_month = $prev_month < 1 ? 12 : $prev_month;
	$prev_year  = $prev_month < 1 ? $display_year - 1 : $display_year;

	?>

	<div id="sc_event_nav_wrap">
		<form id="sc_event_nav_prev" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" name="sc_month" value="<?php echo absint( $prev_month ); ?>">
			<input type="hidden" name="sc_year" value="<?php echo absint( $prev_year ); ?>">
			<input type="hidden" name="sc_current_month" value="<?php echo absint( $display_month ); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_prev" value="<?php echo esc_html_x( 'Previous', 'Previous month', 'sugar-calendar' ); ?>">
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce'); ?>">
			<input type="hidden" name="action" value="sc_load_calendar">
			<input type="hidden" name="action_2" value="prev_month">
			<input type="hidden" name="<?php echo esc_attr( $tax ); ?>" value="<?php echo is_null( $category ) ? 0 : $category; ?>">
			<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"><?php } ?>
			<input type="hidden" name="type" value="month">
		</form>
		<form id="sc_event_nav_next" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="hidden" name="sc_month" class="month" value="<?php echo absint( $next_month ); ?>">
			<input type="hidden" name="sc_year" class="year" value="<?php echo absint( $next_year ); ?>">
			<input type="hidden" name="sc_current_month" value="<?php echo absint( $display_month ); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_next" value="<?php echo esc_html_x( 'Next', 'Next month', 'sugar-calendar' ); ?>">
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>">
			<input type="hidden" name="action" value="sc_load_calendar">
			<input type="hidden" name="action_2" value="next_month">
			<input type="hidden" name="<?php echo esc_attr( $tax ); ?>" value="<?php echo is_null( $category ) ? 0 : $category; ?>">
			<?php if($size == 'small') { ?><input type="hidden" name="sc_calendar_size" value="small"><?php } ?>
			<input type="hidden" name="type" value="month">
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

	$tax = sugar_calendar_get_calendar_taxonomy_id();

	switch ( $type ) {
		case 'day':
			$next_display_time = strtotime( '+1 day', $display_time );
			$prev_display_time = strtotime( '-1 day', $display_time );
			break;

		case '4day':
		case '4days': // See: https://github.com/sugarcalendar/standard/issues/300
			$next_display_time = strtotime( '+4 day', $display_time );
			$prev_display_time = strtotime( '-4 day', $display_time );
			break;

		case 'week':
			$next_display_time = strtotime( '+1 week', $display_time );
			$prev_display_time = strtotime( '-1 week', $display_time );
			break;

		case '2week':
		case '2weeks': // See: https://github.com/sugarcalendar/standard/issues/300
			$next_display_time = strtotime( '+2 week', $display_time );
			$prev_display_time = strtotime( '-2 week', $display_time );
			break;

		default:
			$next_display_time = strtotime( '+1 month', $display_time );
			$prev_display_time = strtotime( '-1 month', $display_time );
	} ?>

	<div id="sc_event_nav_wrap">
		<form id="sc_event_nav_prev" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_prev" value="<?php _e('Previous', 'sugar-calendar'); ?>">
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce'); ?>">
			<input type="hidden" name="display_time" value="<?php echo esc_attr( $prev_display_time ); ?>">
			<input type="hidden" name="type" value="<?php echo esc_attr( $prev_display_time ); ?>">
			<input type="hidden" name="action" value="sc_load_calendar">
			<input type="hidden" name="<?php echo esc_attr( $tax ); ?>" value="<?php echo is_null( $category ) ? 0 : esc_attr( $category ); ?>">
			<?php if ( 'small' === $size ) : ?>
				<input type="hidden" name="sc_calendar_size" value="small">
			<?php endif; ?>
			<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>">
		</form>
		<form id="sc_event_nav_next" class="sc_events_form" method="POST" action="#sc_events_calendar_<?php echo uniqid(); ?>">
			<input type="submit" class="sc_calendar_submit" name="sc_next" value="<?php _e('Next', 'sugar-calendar'); ?>">
			<input name="sc_nonce" type="hidden" value="<?php echo wp_create_nonce('sc_calendar_nonce') ?>">
			<input type="hidden" name="display_time" value="<?php echo esc_attr( $next_display_time ); ?>">
			<input type="hidden" name="action" value="sc_load_calendar">
			<input type="hidden" name="<?php echo esc_attr( $tax ); ?>" value="<?php echo is_null( $category ) ? 0 : esc_attr( $category ); ?>">
			<?php if ( 'small' === $size ) : ?>
				<input type="hidden" name="sc_calendar_size" value="small">
			<?php endif; ?>
			<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>">
		</form>
	</div>

<?php
}
