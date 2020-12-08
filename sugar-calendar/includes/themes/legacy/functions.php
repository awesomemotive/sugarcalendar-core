<?php

/**
 * Sugar Calendar Legacy Functions.
 *
 * Try not to use these in any code after 2.0.0.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Query events by a given day, month, and year.
 *
 * Also accepts a category.
 *
 * @since 2.0.0
 *
 * @param int    $day
 * @param int    $month
 * @param int    $year
 * $param string $category
 *
 * @return array
 */
function sc_get_events_for_calendar( $day = '01', $month = '01', $year = '1970', $category = '' ) {

	// Sanitize
	$day   = str_pad( $day,   2, '0', STR_PAD_LEFT );
	$month = str_pad( $month, 2, '0', STR_PAD_LEFT );
	$year  = str_pad( $year,  4, '0', STR_PAD_LEFT );

	// Boundaries
	$view_start  = "{$year}-{$month}-01 00:00:00";
	$month_start = mysql2date( 'U', $view_start );
	$month_end   = strtotime( '+1 month -1 second', $month_start );
	$view_end    = gmdate( 'Y-m-d H:i:s', $month_end );
	$number      = sc_get_number_of_events();

	// Default arguments
	$args = array(
		'no_found_rows' => true,
		'number'        => $number,
		'object_type'   => 'post',
		'status'        => 'publish',
		'orderby'       => 'start',
		'order'         => 'ASC',
		'date_query'    => sugar_calendar_get_date_query_args( 'month', $view_start, $view_end )
	);

	// Maybe add category if non-empty
	if ( ! empty( $category ) ) {
		$tax          = sugar_calendar_get_calendar_taxonomy_id();
		$args[ $tax ] = $category; // Sanitized later
	}

	// Query for events
	$events = sugar_calendar_get_events( $args );

	// Return the events
	return $events;
}

/**
 * Return if an event overlaps a day, month, and year combination
 *
 * @since 2.0.0
 *
 * @param array  $event
 * @param string $day
 * @param string $month
 * @param string $year
 *
 * @return boolean
 */
function sc_is_event_for_day( $event, $day = '01', $month = '01', $year = '1970' ) {

	// Make start & end
	$start = gmmktime( '00', '00', '00', $month, $day, $year );
	$end   = gmmktime( '23', '59', '59', $month, $day, $year );

	// Return
	return $event->overlaps( $start, $end );
}

/**
 * Return if doing events, either singular, archive, or related taxonomy
 *
 * @since 2.0.19
 *
 * @return boolean
 */
function sc_doing_events() {

	// Get post types and taxonomies
	$pts = sugar_calendar_allowed_post_types();
	$tax = sugar_calendar_get_object_taxonomies( $pts );

	// Return true if single event, event archive, or allowed taxonomy archive
	if ( is_singular( $pts ) || is_post_type_archive( $pts ) || is_tax( $tax ) ) {
		return true;
	}

	// Default false
	return false;
}

/**
 * Retrieves recurring events
 *
 * @since 2.0.0
 *
 * @param array $events
 * @param string $day
 * @param string $month
 * @param string $year
 *
 * @return array
 */
function sc_filter_events_for_day( $events = array(), $day = '01', $month = '01', $year = '1970' ) {

	// Default return value
	$retval = array();

	// Bail if no events
	if ( empty( $events ) ) {
		return $retval;
	}

	// Loop through events
	foreach ( $events as $event ) {

		// Skip if event is not for day
		if ( ! sc_is_event_for_day( $event, $day, $month, $year ) ) {
			continue;
		}

		// Add event to return array
		$retval[] = $event;
	}

	// Return events for day
	return $retval;
}

/**
 * Get the HTML class attribute contents for an item in a theme-side calendar
 * cell. It exists to encapsulate a term-cache check, and code that was repeated
 * a few times in calendar functions defined in this file.
 *
 * This new function is in the legacy theme folder, and as such should not be
 * used in new code anywhere else. If this kind of functionality is needed
 * elsewhere, please consider writing a newer better function that does not
 * accept a post ID only.
 *
 * @since 2.0.15
 *
 * @param int $object_id
 * @return string
 */
function sc_get_event_class( $object_id = false ) {

	// This function only accepts a post ID
	if ( empty( $object_id ) || ! is_numeric( $object_id ) ) {
		return '';
	}

	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Check term cache first
	$terms = get_object_term_cache( $object_id, $tax );

	// No cache, so query for terms
	if ( false === $terms ) {
		$terms = wp_get_object_terms( $object_id, $tax );
	}

	// Bail if no terms
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	// Pluck the slugs
	$slugs  = array_unique( wp_list_pluck( $terms, 'slug' ) );

	// Sanitize and string'ify slugs
	$retval = implode( ' ', array_map( 'sanitize_html_class', $slugs ) );

	// Return the string
	return $retval;
}

/**
 * Build Calendar for Event post type
 * @author Syamil MJ
 * @credit http://davidwalsh.name/php-calendar
 *
 * @since 1.0.0
 *
 * @param $month
 * @param $year
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar( $month, $year, $size = 'large', $category = null ) {
	global $wp_locale;

	$day_names_large = $wp_locale->weekday;
	$day_names_small = array_values( $wp_locale->weekday_initial );

	$week_start_day = sc_get_week_start_day();

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	// adjust day names for sites with Monday set as the start day
	if ( $week_start_day == 1 ) {
		$end_day = $day_names[ 0 ];
		array_shift( $day_names );
		$day_names[] = $end_day;
	}

	//start draw table
	$calendar  = '<table cellpadding="0" cellspacing="0" class="calendar sc-table">';
	$calendar .= '<tr class="calendar-row">';

	for ( $i = 0; $i <= 6; $i++ ) {
		$calendar .= '<th class="calendar-day-head">' . esc_html( $day_names[ $i ] ) . '</th>';
	}
	$calendar .= '</tr>';

	//days and weeks vars now
	$running_day = gmdate( 'w', gmmktime( 0, 0, 0, $month, 1, $year ) );
	if ( $week_start_day == 1 ) {
		$running_day = ( $running_day > 0 ) ? $running_day - 1 : 6;
	}
	$days_in_month = gmdate( 't', gmmktime( 0, 0, 0, $month, 1, $year ) );
	$days_in_this_week = 1;
	$day_counter = 0;

	//get today's date
	$time        = (int) sugar_calendar_get_request_time();
	$today_day   = gmdate( 'j', $time );
	$today_month = gmdate( 'm', $time );
	$today_year  = gmdate( 'Y', $time );

	// Get the events
	$all_events = sc_get_events_for_calendar( '01', $month, $year, $category );

	//row for week one */
	$calendar .= '<tr class="calendar-row">';

	//print "blank" days until the first of the current week
	for ( $x = 0; $x < $running_day; $x++ ) {
		$calendar .= '<td class="calendar-day-np past" valign="top"></td>';
		$days_in_this_week++;
	}

	//keep going with days
	for ( $list_day = 1; $list_day <= $days_in_month; $list_day++ ) {
		$cal_event = '';
		$today = ( $today_day == $list_day && $today_month == $month && $today_year == $year )
			? 'today'
			: ( ( $today_day > $list_day && $today_month >= $month && $today_year >= $year )
				? 'past'
				: 'upcoming' );

		// Filter events
		$events = sc_filter_events_for_day( $all_events, $list_day, $month, $year );

		// Loop through events
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$class = sc_get_event_class( $event->object_id );
				$link  = ( $size === 'small' )
					? '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '" title="' . esc_attr( strip_tags( get_the_title( $event->object_id ) ) ) . '">&bull;</a>'
					: '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '">' . esc_html( get_the_title( $event->object_id ) ) . '</a><br/>';

				$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $event->object_id, $size );
			}
		}

		$cal_day = '<td class="calendar-day ' . $today . '" valign="top"><div class="sc_day_div">';

		// add in the day numbering
		$cal_day  .= '<div class="day-number day-' . $list_day . '">' . $list_day . '</div>';
		$calendar .= $cal_day;
		$calendar .= $cal_event ? $cal_event : '';
		$calendar .= '</div></td>';

		if ( $running_day == 6 ) {
			if ( ( $list_day < $days_in_month ) ) {
				$calendar .= '</tr>';
				$calendar .= '<tr class="calendar-row">';
				$running_day = -1;
				$days_in_this_week = 0;
			}
		}

		$days_in_this_week++;
		$running_day++;
		$day_counter++;
	}

	//finish the rest of the days in the week
	if ( $days_in_this_week < 8 ) {
		for ( $x = 1; $x <= ( 8 - $days_in_this_week ); $x++ ) {
			$calendar .= '<td class="calendar-day-np upcoming" valign="top"><div class="sc_day_div"></div></td>';
		}
	}

	//final row
	$calendar .= '</tr>';

	//end the table
	$calendar .= '</table>';

	// Clean up
	wp_reset_postdata();

	//all done, return the completed table
	return $calendar;
}

/**
 * Added function to call default sc_draw_calendar()
 *
 * @since 1.0.0
 *
 * @param $display_time
 * @param string $size
 * @param null $category
 *
 * @return string
 */
function sc_draw_calendar_month( $display_time, $size = 'large', $category = null ) {
	$month = gmdate( 'n', $display_time );
	$year  = gmdate( 'Y', $display_time );

	return sc_draw_calendar( $month, $year, $size, $category );
}

/**
 * Draw the weekly calendar
 *
 * @since 1.0.0
 *
 * @param $display_time
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar_week( $display_time, $size = 'large', $category = null ) {
	global $wp_locale;

	$day_names_large = $wp_locale->weekday;
	$day_names_small = array_values( $wp_locale->weekday_initial );

	$week_start_day = sc_get_week_start_day();

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	// adjust day names for sites with Monday set as the start day
	if ( $week_start_day == 1 ) {
		$end_day = $day_names[ 0 ];
		array_shift( $day_names );
		$day_names[] = $end_day;
	}

	//start draw table
	$calendar  = '<table cellpadding="0" cellspacing="0" class="calendar sc-table">';
	$calendar .= '<tr class="calendar-row">';

	for ( $i = 0; $i <= 6; $i++ ) {
		$calendar .= '<th class="calendar-day-head">' . esc_html( $day_names[ $i ] ) . '</th>';
	}
	$calendar .= '</tr>';

	// get the values for the first day of week where $display_time occurs
	$day_of_week   = gmdate( 'w', $display_time );
	$display_time  = strtotime( '-' . $day_of_week . ' days', $display_time );
	$display_day   = gmdate( 'j', $display_time );
	$display_month = gmdate( 'n', $display_time );
	$display_year  = gmdate( 'Y', $display_time );

	//get today's date
	$time        = (int) sugar_calendar_get_request_time();
	$today_day   = gmdate( 'j', $time );
	$today_month = gmdate( 'm', $time );
	$today_year  = gmdate( 'Y', $time );

	// start row
	$calendar .= '<tr class="calendar-row">';

	// Get the events
	$all_events = sc_get_events_for_calendar( $display_day, $display_month, $display_year, $category );

	// output seven days
	for ( $list_day = 1; $list_day <= 7; $list_day++ ) {
		$cal_event = '';
		$today = ( $today_day == $display_day && $today_month == $display_month && $today_year == $display_year )
			? 'today'
			: ( ( $today_day > $display_day && $today_month >= $display_month && $today_year >= $display_year )
				? 'past'
				: 'upcoming' );

		// Filter events
		$events = sc_filter_events_for_day( $all_events, $display_day, $display_month, $display_year );

		// Loop through events
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$class = sc_get_event_class( $event->object_id );
				$link  = ( $size === 'small' )
					? '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '" title="' . esc_attr( strip_tags( get_the_title( $event->object_id ) ) ) . '">&bull;</a>'
					: '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '">' . esc_html( get_the_title( $event->object_id ) ) . '</a><br/>';

				$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $event->object_id, $size );
			}
		}

		$cal_day = '<td class="calendar-day ' . $today . '" valign="top"><div class="sc_day_div">';

		// add in the day numbering
		$cal_day  .= '<div class="day-number day-' . $display_day . '">' . $display_day . '</div>';
		$calendar .= $cal_day;
		$calendar .= $cal_event ? $cal_event : '';
		$calendar .= '</div></td>';

		$display_time  = strtotime( '+1 day', $display_time );
		$display_day   = gmdate( 'j', $display_time );
		$display_month = gmdate( 'n', $display_time );
		$display_year  = gmdate( 'Y', $display_time );
	}

	// finish row
	$calendar .= '</tr>';

	// end the calendar
	$calendar .= '</table>';

	// Clean up
	wp_reset_postdata();

	//all done, return the completed table
	return $calendar;
}

/**
 * Draw the two week calendar
 *
 * @since 1.0.0
 *
 * @param $display_time
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar_2week( $display_time, $size = 'large', $category = null ) {
	global $wp_locale;

	$day_names_large = $wp_locale->weekday;
	$day_names_small = array_values( $wp_locale->weekday_initial );

	$week_start_day = sc_get_week_start_day();

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	// adjust day names for sites with Monday set as the start day
	if ( $week_start_day == 1 ) {
		$end_day = $day_names[ 0 ];
		array_shift( $day_names );
		$day_names[] = $end_day;
	}

	//start draw table
	$calendar  = '<table cellpadding="0" cellspacing="0" class="calendar sc-table">';
	$calendar .= '<tr class="calendar-row">';

	for ( $i = 0; $i <= 6; $i++ ) {
		$calendar .= '<th class="calendar-day-head">' . esc_html( $day_names[ $i ] ) . '</th>';
	}
	$calendar .= '</tr>';

	// get the values for the first day of week where $display_time occurs
	$day_of_week   = gmdate( 'w', $display_time );
	$display_time  = strtotime( '-' . $day_of_week . ' days', $display_time );
	$display_day   = gmdate( 'j', $display_time );
	$display_month = gmdate( 'n', $display_time );
	$display_year  = gmdate( 'Y', $display_time );

	//get today's date
	$time        = (int) sugar_calendar_get_request_time();
	$today_day   = gmdate( 'j', $time );
	$today_month = gmdate( 'm', $time );
	$today_year  = gmdate( 'Y', $time );

	// start row
	$calendar .= '<tr class="calendar-row">';

	// Get the events
	$all_events = sc_get_events_for_calendar( $display_day, $display_month, $display_year, $category );

	// output seven days
	for ( $list_day = 1; $list_day <= 14; $list_day++ ) {
		$cal_event = '';
		$today = ( $today_day == $display_day && $today_month == $display_month && $today_year == $display_year )
			? 'today'
			: ( ( $today_day > $display_day && $today_month >= $display_month && $today_year >= $display_year )
				? 'past'
				: 'upcoming' );

		// Filter events
		$events = sc_filter_events_for_day( $all_events, $display_day, $display_month, $display_year );

		// Loop through events
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$class = sc_get_event_class( $event->object_id );
				$link  = ( $size === 'small' )
					? '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '" title="' . esc_attr( strip_tags( get_the_title( $event->object_id ) ) ) . '">&bull;</a>'
					: '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '">' . esc_html( get_the_title( $event->object_id ) ) . '</a><br/>';

				$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $event->object_id, $size );
			}
		}

		$cal_day = '<td class="calendar-day ' . $today . '" valign="top"><div class="sc_day_div">';

		// add in the day numbering
		$cal_day  .= '<div class="day-number day-' . $display_day . '">' . $display_day . '</div>';
		$calendar .= $cal_day;
		$calendar .= $cal_event ? $cal_event : '';
		$calendar .= '</div></td>';

		if ( $list_day == 7 ) {
			$calendar .= '</tr>';
			$calendar .= '<tr class="calendar-row">';
		}

		$display_time  = strtotime( '+1 day', $display_time );
		$display_day   = gmdate( 'j', $display_time );
		$display_month = gmdate( 'n', $display_time );
		$display_year  = gmdate( 'Y', $display_time );
	}

	// finish row
	$calendar .= '</tr>';

	// end the calendar
	$calendar .= '</table>';

	// Clean up
	wp_reset_postdata();

	//all done, return the completed table
	return $calendar;
}

/**
 * Draw the daily calendar
 *
 * @since 1.0.0
 *
 * @param $display_time
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar_day( $display_time, $size = 'large', $category = null ) {
	global $wp_locale;

	$day_names_large = $wp_locale->weekday;
	$day_names_small = array_values( $wp_locale->weekday_initial );

	$day_of_week = gmdate( 'w', $display_time );

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	//start draw table
	$calendar  = '<table cellpadding="0" cellspacing="0" class="calendar">';
	$calendar .= '<tr class="calendar-row">';
	$calendar .= '<th class="calendar-day-head">' . esc_html( $day_names[ $day_of_week ] ) . '</th>';
	$calendar .= '</tr>';

	$display_day   = gmdate( 'j', $display_time );
	$display_month = gmdate( 'n', $display_time );
	$display_year  = gmdate( 'Y', $display_time );

	//get today's date
	$time        = (int) sugar_calendar_get_request_time();
	$today_day   = gmdate( 'j', $time );
	$today_month = gmdate( 'm', $time );
	$today_year  = gmdate( 'Y', $time );

	// start row
	$calendar .= '<tr class="calendar-row">';

	// Get the events
	$all_events = sc_get_events_for_calendar( $display_day, $display_month, $display_year, $category );

	// output current day
	$today = ( $today_day == $display_day && $today_month == $display_month && $today_year == $display_year )
		? 'today'
		: ( ( $today_day > $display_day && $today_month >= $display_month && $today_year >= $display_year )
			? 'past'
			: 'upcoming' );

	$cal_event = '';

	// Filter events
	$events = sc_filter_events_for_day( $all_events, $display_day, $display_month, $display_year );

	if ( ! empty( $events ) ) {
		foreach ( $events as $event ) {
			$class = sc_get_event_class( $event->object_id );
			$link  = ( $size === 'small' )
				? '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '" title="' . esc_attr( strip_tags( get_the_title( $event->object_id ) ) ) . '">&bull;</a>'
				: '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '">' . esc_html( get_the_title( $event->object_id ) ) . '</a><br/>';

			$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $event->object_id, $size );
		}
	}

	$cal_day = '<td class="calendar-day ' . $today . '" valign="top"><div class="sc_day_div">';

	// add in the day numbering
	$cal_day  .= '<div class="day-number day-' . $display_day . '">' . $display_day . '</div>';
	$calendar .= $cal_day;
	$calendar .= $cal_event;
	$calendar .= '</div></td>';

	// finish row
	$calendar .= '</tr>';

	// end the calendar
	$calendar .= '</table>';

	// Clean up
	wp_reset_postdata();

	//all done, return the completed table
	return $calendar;
}

/**
 * Draw the four day calendar
 *
 * @since 1.0.0
 *
 * @param $display_time
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar_4day( $display_time, $size = 'large', $category = null ) {
	global $wp_locale;

	$day_names_large = $wp_locale->weekday;
	$day_names_small = array_values( $wp_locale->weekday_initial );

	$day_of_week = gmdate( 'w', $display_time );

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	//start draw table
	$calendar  = '<table cellpadding="0" cellspacing="0" class="calendar sc-table">';
	$calendar .= '<tr class="calendar-row">';

	for ( $i = 0; $i <= 3; $i++ ) {
		$calendar .= '<th class="calendar-day-head">' . esc_html( $day_names[ $day_of_week ] ) . '</th>';
		if ( $day_of_week == 6 ) {
			$day_of_week = 0;
		} else {
			$day_of_week++;
		}
	}
	$calendar .= '</tr>';

	$display_day   = gmdate( 'j', $display_time );
	$display_month = gmdate( 'n', $display_time );
	$display_year  = gmdate( 'Y', $display_time );

	//get today's date
	$time        = (int) sugar_calendar_get_request_time();
	$today_day   = gmdate( 'j', $time );
	$today_month = gmdate( 'm', $time );
	$today_year  = gmdate( 'Y', $time );

	// start row
	$calendar .= '<tr class="calendar-row">';

	// Get the events
	$all_events = sc_get_events_for_calendar( $display_day, $display_month, $display_year, $category );

	// output four days
	for ( $list_day = 0; $list_day <= 3; $list_day++ ) {
		$cal_event = '';
		$today = ( $today_day == $display_day && $today_month == $display_month && $today_year == $display_year )
			? 'today'
			: ( ( $today_day > $display_day && $today_month >= $display_month && $today_year >= $display_year )
				? 'past'
				: 'upcoming' );

		// Filter events
		$events = sc_filter_events_for_day( $all_events, $display_day, $display_month, $display_year );

		// Loop through events
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$class = sc_get_event_class( $event->object_id );
				$link  = ( $size === 'small' )
					? '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '" title="' . esc_attr( strip_tags( get_the_title( $event->object_id ) ) ) . '">&bull;</a>'
					: '<a href="' . get_permalink( $event->object_id ) . '" class="' . esc_attr( $class ) . '">' . esc_html( get_the_title( $event->object_id ) ) . '</a><br/>';

				$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $event->object_id, $size );
			}
		}

		$cal_day = '<td class="calendar-day ' . $today . '" valign="top"><div class="sc_day_div">';

		// add in the day numbering
		$cal_day  .= '<div class="day-number day-' . $display_day . '">' . $display_day . '</div>';
		$calendar .= $cal_day;
		$calendar .= $cal_event ? $cal_event : '';
		$calendar .= '</div></td>';

		$display_time  = strtotime( '+1 day', $display_time );
		$display_day   = gmdate( 'j', $display_time );
		$display_month = gmdate( 'n', $display_time );
		$display_year  = gmdate( 'Y', $display_time );
	}

	// finish row
	$calendar .= '</tr>';

	// end the calendar
	$calendar .= '</table>';

	// Clean up
	wp_reset_postdata();

	//all done, return the completed table
	return $calendar;
}

/**
 * Month number To Name
 *
 * Takes a month number and returns the
 * three letter name of it.
 *
 * @access      public
 * @since       1.0.0
 * @return      string
 */
function sc_month_num_to_name( $n ) {
	$timestamp = gmmktime( 0, 0, 0, $n, 1, 2005 );

	// Uses WordPress locale
	return sugar_calendar_format_date_i18n( 'F', $timestamp );
}

/**
 * Determines whether the current page has a calendar on it
 *
 * @access      public
 * @since       1.0.0
 * @return      string
 */
function sc_is_calendar_page() {
	$post = get_post();

	if ( ! is_object( $post ) ) {
		return false;
	}

	if ( has_shortcode( $post->post_content, 'sc_events_calendar' ) ) {
		return true;
	}

	return false;
}

/**
 * Determines whether a widget is in use
 *
 * @since 2.0.0
 * @return boolean
 */
function sc_using_widget() {

	// Default return value
	$retval = false;

	// Array of widget IDs
	$widget_ids = sc_get_widget_ids();

	// Bail if there are no widgets
	if ( empty( $widget_ids ) ) {
		return $retval;
	}

	// Loop through Legacy widgets, and check if any are active
	foreach ( $widget_ids as $widget_id ) {
		if ( is_active_widget( false, false, $widget_id ) ) {
			$retval = true;
			continue;
		}
	}

	// Return if using a widget
	return (bool) $retval;
}

/**
 * Return array of valid calendar types.
 *
 * @since 2.0.0
 *
 * @return array
 */
function sc_get_valid_calendar_types() {
	return array(
		'day',
		'4day',
		'week',
		'2week',
		'month',

		// See: https://github.com/sugarcalendar/standard/issues/300
		'4days',
		'2weeks'
	);
}

/**
 * Retrieves the calendar date for an event
 *
 * @access      public
 * @since       1.0.0
 * @param 		int $event_id  int The ID number of the event
 * @param 		bool $formatted bool Whether to return a time stamp or the nicely formatted date
 * @return      string
 */
function sc_get_event_date( $event_id = 0, $formatted = true ) {

	// Get start & end dates & times
	$retval = get_post_meta( $event_id, 'sc_event_date_time', true );

	// Bail if no event start datetime (how'd this happen?)
	if ( empty( $retval ) ) {
		return $retval;
	}

	// Return date if not formatting
	if ( empty( $formatted ) ) {
		return $retval;
	}

	// Get the event
	$event  = sugar_calendar_get_event_by_object( $event_id );

	// Get the date format, and format start
	$format = sc_get_date_format();
	$dt     = $event->start_date( 'Y-m-d' );

	// Default time zone
	$tz     = 'floating';

	// Maybe use the start time zone
	if ( ! empty( $event->start_tz ) ) {
		$tz = $event->start_tz;
	}

	$start_date = sugar_calendar_format_date_i18n( $format, $retval );
	$start_html = '<span class="sc-date-start"><time datetime="' . esc_attr( $dt ) . '" data-timezone="' . esc_attr( $tz ) . '">' . esc_html( $start_date ) . '</time></span>';

	// Get the end date
	$end = get_post_meta( $event_id, 'sc_event_end_date_time', true );

	// Maybe append the end date
	if ( empty( $end ) ) {
		return $start_html;
	}

	// End date
	$end_date = sugar_calendar_format_date_i18n( $format, $end );

	// Add end to start, with separator
	if ( $end_date !== $start_date ) {

		// Default time zone
		$tz = 'floating';

		// All-day Events have floating time zones
		if ( ! empty( $event->end_tz ) && ! $event->is_all_day() ) {
			$tz = $event->end_tz;

		// Maybe fallback to the start time zone
		} elseif ( empty( $event->end_tz ) && ! empty( $event->start_tz ) ) {
			$tz = $event->start_tz;
		}

		// End date
		$dt       = $event->end_date( 'Y-m-d' );

		// Output
		$end_html = '<span class="sc-date-start-end-sep"> - </span><span class="sc-date-end"><time datetime="' . esc_attr( $dt ) . '" data-timezone="' . esc_attr( $tz ) . '">' . esc_html( $end_date ) . '</time></span>';
		$retval   = $start_html . $end_html;

	// Just the start
	} else {
		$retval = $start_html;
	}

	// Return the dates & times
	return $retval;
}

/**
 * Returns a formatted date for an event and given timestamp.
 * The timestamp is given because this could be a recurrence of an event.
 * Note: This does not display multi-day events, only start times.
 *
 * @access      public
 * @since       1.6.0
 * @param       int $event_id
 * @param 		int $timestamp
 * @return      string
 */
function sc_get_formatted_date( $event_id = 0, $timestamp = null ) {

	// Default return value
	$retval = '';

	// Bail if no event and no timestamp to derive a date from
	if ( empty( $event_id ) && empty( $timestamp ) ) {
		return $retval;
	}

	// Get a timestamp from the start date & time
	if ( ! empty( $event_id ) && empty( $timestamp ) ) {
		$timestamp = get_post_meta( $event_id, 'sc_event_date_time', true );
	}

	// Maybe format a timestamp if one was found
	if ( ! empty( $timestamp ) ) {
		$format = sc_get_date_format();
		$retval = sugar_calendar_format_date_i18n( $format, $timestamp );
	}

	// Return a possibly formatted start date & time
	return $retval;
}

/**
 * Retrieves the time for an event
 *
 * @access      public
 * @since       1.0.0
 * @param       int $event_id int The ID number of the event
 * @return      array
 */
function sc_get_event_time( $event_id ) {

	// Get start & end times
	$start_time = sc_get_event_start_time( $event_id );
	$end_time   = sc_get_event_end_time( $event_id );

	// Return array of start & end times
	return apply_filters( 'sc_event_time', array(
		'start' => $start_time,
		'end'   => $end_time
	) );
}

/**
 * Retrieves the start time for an event
 *
 * @access      public
 * @since       1.0.0
 * @param       int $event_id int The ID number of the event
 * @return      string
 */
function sc_get_event_start_time( $event_id = 0 ) {

	// Get the start date
	$start = get_post_meta( $event_id, 'sc_event_date', true );

	// Bail if no start time
	if ( empty( $start ) ) {
		return '';
	}

	// Use meta keys for back-compat
	$day    = get_post_meta( $event_id, 'sc_event_day_of_month', true );
	$month  = get_post_meta( $event_id, 'sc_event_month',        true );
	$year   = get_post_meta( $event_id, 'sc_event_year',         true );
	$hour   = get_post_meta( $event_id, 'sc_event_time_hour',    true );
	$minute = get_post_meta( $event_id, 'sc_event_time_minute',  true );
	$am_pm  = get_post_meta( $event_id, 'sc_event_time_am_pm',   true );

	// Adjust for meridiem
	if ( ( $am_pm === 'pm' ) && ( $hour < 12 ) ) {
		$hour += 12;
	} elseif ( ( $am_pm === 'am' ) && ( $hour >= 12 ) ) {
		$hour -= 12;
	}

	// Default return value
	$time = null;

	// Format time value if not null
	if ( ( false !== $hour ) && ( false !== $minute ) ) {
		$format = sc_get_time_format();
		$mktime = gmmktime( $hour, $minute, 0, $month, $day, $year );

		// @todo needs time zone support
		$time   = sugar_calendar_format_date_i18n( $format, $mktime );
	}

	return apply_filters( 'sc_event_start_time', $time, $hour, $minute, $am_pm );
}

/**
 * Retrieves the end time for an event
 *
 * @access      public
 * @since       1.0.0
 * @param       int $event_id int The ID number of the event
 * @return      string
 */
function sc_get_event_end_time( $event_id = 0 ) {

	// Get the end date
	$end = get_post_meta( $event_id, 'sc_event_end_date', true );

	// Bail if no end in sight (ha!)
	if ( empty( $end ) ) {
		return '';
	}

	// Use meta keys for back-compat
	$day    = get_post_meta( $event_id, 'sc_event_end_day_of_month', true );
	$month  = get_post_meta( $event_id, 'sc_event_end_month',        true );
	$year   = get_post_meta( $event_id, 'sc_event_end_year',         true );
	$hour   = get_post_meta( $event_id, 'sc_event_end_time_hour',    true );
	$minute = get_post_meta( $event_id, 'sc_event_end_time_minute',  true );
	$am_pm  = get_post_meta( $event_id, 'sc_event_end_time_am_pm',   true );

	// Adjust for meridiem
	if ( ( $am_pm === 'pm' ) && ( $hour < 12 ) ) {
		$hour += 12;
	} elseif ( ( $am_pm === 'am' ) && ( $hour >= 12 ) ) {
		$hour -= 12;
	}

	// Default return value
	$time = null;

	// Format time value if not null
	if ( ( false !== $hour ) && ( false !== $minute ) ) {
		$format = sc_get_time_format();
		$mktime = gmmktime( $hour, $minute, 0, $month, $day, $year );

		// @todo needs time zone support
		$time   = sugar_calendar_format_date_i18n( $format, $mktime );
	}

	return apply_filters( 'sc_event_end_time', $time, $hour, $minute );
}

/**
 * Checks if an event is recurring
 *
 * @access      public
 * @since       1.1
 * @param  		int $event_id int The ID number of the event
 * @return      array
 */
function sc_is_recurring( $event_id ) {
	$recurring = get_post_meta( $event_id, 'sc_event_recurring', true );
	$retval    = ! empty( $recurring ) && ( 'none' !== $recurring );

	return $retval;
}

/**
 * Retrieves the recurring period for an event
 *
 * @access      public
 * @since       1.2
 * @param  		int $event_id int The ID number of the event
 * @return      string
 */
function sc_get_recurring_period( $event_id ) {
	$period = get_post_meta( $event_id, 'sc_event_recurring', true );

	return apply_filters( 'sc_recurring_period', $period, $event_id );
}

/**
 * Retrieves the recurring stop date for an event
 *
 * @access      public
 * @since       1.2
 * @param  		int $event_id int The ID number of the event
 * @return      mixed
 */
function sc_get_recurring_stop_date( $event_id ) {

	$recur_until = get_post_meta( $event_id, 'sc_recur_until', true );

	if ( ! sc_is_recurring( $event_id ) ) {
		$recur_until = false;
	}

	if ( strlen( trim( $recur_until ) ) == 0 ) {
		$recur_until = false;
	}

	return apply_filters( 'sc_recurring_stop_date', $recur_until, $event_id );
}

/**
 * Shows the date of a recurring event
 *
 * @access      public
 * @since       1.1.1
 * @return      array
 */
function sc_show_single_recurring_date( $event_id = 0 ) {
	echo sc_get_recurring_description( $event_id );
}

/**
 * Get the date of a recurring event
 *
 * @access      public
 * @since       2.0.9
 * @return      array
 */
function sc_get_recurring_description( $event_id = 0 ) {

	// Default return value
	$retval = '';

	$recurring_schedule = get_post_meta( $event_id, 'sc_event_recurring', true );
	$event_date_time    = get_post_meta( $event_id, 'sc_event_date_time', true );
	$recur_until        = sc_get_recurring_stop_date( $event_id );
	$date_format        = sc_get_date_format();

	$format = apply_filters( 'sc_recurring_date_format', array(), $event_date_time, $recur_until );

	if ( $recur_until ) :

		switch ( $recurring_schedule ) {

			case 'weekly':

				if ( isset( $format[ 'weekly' ] ) ) {
					$retval = $format[ 'weekly' ];
				} else {
					$retval = sprintf( __( 'Starts %s then every %s until %s', 'sugar-calendar' ),

						// @todo needs time zone support
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'l', $event_date_time ),
						date_i18n( $date_format, $recur_until ) );
				}
				break;

			case 'monthly':

				if ( isset( $format[ 'monthly' ] ) ) {
					$retval = $format[ 'monthly' ];
				} else {
					$retval = sprintf( __( 'Starts %s then every month on the %s until %s', 'sugar-calendar' ),

						// @todo needs time zone support
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'jS', $event_date_time ),
						date_i18n( $date_format, $recur_until ) );
				}
				break;

			case 'yearly':

				if ( isset( $format[ 'yearly' ] ) ) {
					$retval = $format[ 'yearly' ];
				} else {
					$retval = sprintf( __( 'Starts %s then every year on the %s of %s until %s', 'sugar-calendar' ),

						// @todo needs time zone support
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'jS', $event_date_time ),
						date_i18n( 'F', $event_date_time ),
						date_i18n( $date_format, $recur_until ) );
				}
				break;
		}

	else :

		switch ( $recurring_schedule ) {

			case 'weekly':

				if ( isset( $format[ 'weekly' ] ) ) {
					$retval = $format[ 'weekly' ];
				} else {
					$retval = sprintf( __( 'Starts %s then every %s', 'sugar-calendar' ),

						// @todo needs time zone support
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'l', $event_date_time ) );
				}
				break;

			case 'monthly':

				if ( isset( $format[ 'monthly' ] ) ) {
					$retval = $format[ 'monthly' ];
				} else {
					$retval = sprintf( __( 'Starts %s then every month on the %s', 'sugar-calendar' ),

						// @todo needs time zone support
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'jS', $event_date_time ) );
				}
				break;

			case 'yearly':

				if ( isset( $format[ 'yearly' ] ) ) {
					$retval = $format[ 'yearly' ];
				} else {
					$retval = sprintf( __( 'Starts %s then every year on the %s of %s', 'sugar-calendar' ),

						// @todo needs time zone support
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'jS', $event_date_time ),
						date_i18n( 'F', $event_date_time ) );
				}
				break;
		}
	endif;

	// Return the formatted recurring description
	return $retval;
}

/**
 * Retrieves the maximum number of events to include in a theme-side query.
 *
 * @access      public
 * @since       2.0.7
 * @return      string
 */
function sc_get_number_of_events() {

	$number = get_option( 'sc_number_of_events', false );

	// default to WordPress value
	if ( false === $number ) {
		$number = 30;
	}

	// Filter and return
	return (int) apply_filters( 'sc_number_of_events', $number );
}

/**
 * Retrieves the date format
 *
 * @access      public
 * @since       1.5
 * @return      string
 */
function sc_get_date_format() {

	$format = get_option( 'sc_date_format' );

	// default to WordPress value
	if ( empty( $format ) ) {
		$format = get_option( 'date_format' );
	}

	// Filter and return
	return apply_filters( 'sc_date_format', $format );
}

/**
 * Retrieves the time format
 *
 * @access      public
 * @since       1.5
 * @return      string
 */
function sc_get_time_format() {

	$format = get_option( 'sc_time_format' );

	// default to WordPress value
	if ( empty( $format ) ) {
		$format = get_option( 'time_format' );
	}

	// Filter and return
	return apply_filters( 'sc_time_format', $format );
}

/**
 * Retrieves the week start day. 0 = Sunday, 1 = Monday, etc
 *
 * @access      public
 * @since       1.5
 * @return      string
 */
function sc_get_week_start_day() {

	$start_day = (int) get_option( 'sc_start_of_week' );

	// default to WordPress value
	if ( empty( $start_day ) && ( 0 !== $start_day ) ) {
		$start_day = get_option( 'start_of_week' );
	}

	// Filter and return
	return apply_filters( 'sc_week_start_day', $start_day );
}

/**
 * Retrieves the time zone.
 *
 * @access      public
 * @since       2.1.1
 * @return      string
 */
function sc_get_timezone() {

	$timezone = get_option( 'sc_timezone' );

	// default to WordPress value
	if ( empty( $timezone ) ) {
		$timezone = get_option( 'timezone' );
	}

	// Filter and return
	return apply_filters( 'sc_timezone', $timezone );
}

/**
 * For recurring events, calculate recurrences, then save to sc_all_recurring meta
 *
 * @since 1.6.0
 * @param int $event_id
 */
function sc_update_recurring_events( $event_id = 0 ) {

	if ( ! empty( $event_id ) ) {
		$events[] = $event_id;

	} else {
		$events = get_posts( array(
			'numberposts' => - 1,
			'post_type'   => sugar_calendar_get_event_post_type_id(),
			'post_status' => 'publish',
			'fields'      => 'ids',
			'order'       => 'asc'
		) );
	}

	foreach ( $events as $event_id ) {

		$type = get_post_meta( $event_id, 'sc_event_recurring', true );

		if ( ! empty( $type ) && ( 'none' !== $type ) ) {
			$recurring = sc_calculate_recurring( $event_id );
			update_post_meta( $event_id, 'sc_all_recurring', $recurring );
		}
	}
}

/**
 * This function calculates all occurrences for an event.
 * @param $event_id
 *
 * @return array
 */
function sc_calculate_recurring( $event_id ) {
	$start = get_post_meta( $event_id, 'sc_event_date_time', true );
	$until = get_post_meta( $event_id, 'sc_recur_until',     true );
	$type  = get_post_meta( $event_id, 'sc_event_recurring', true );

	$recurring = array();

	// add first occurrence of event
	$recurring[] = $start;
	$current = $start;

	while ( $until > $current ) {
		switch ( $type ) {
			case 'weekly':
				$current = strtotime( "+1 week", $current );
				break;
			case 'monthly':
				$current = strtotime( "+1 month", $current );
				break;
			case 'yearly':
				$current = strtotime( "+1 year", $current );
				break;
		}

		if ( $until > $current ) {
			$recurring[] = $current;
		}
	}

	return apply_filters( 'sc_calculate_recurring', $recurring );
}

/**
 * Get an array of all events keyed by start time timestamp
 *
 * Array will be sorted ascending by timestamp
 *
 * @since 1.6.0
 * @param string $category
 * @return array $full_list
 */
function sc_get_all_events( $category = null ) {
	$args = array(
		'numberposts' => -1,
		'post_type'   => sugar_calendar_get_event_post_type_id(),
		'post_status' => 'publish',
		'orderby'     => 'meta_value_num',
		'fields'      => 'ids',
		'order'       => 'asc',
	);

	if ( ! is_null( $category ) ) {
		$tax          = sugar_calendar_get_calendar_taxonomy_id();
		$args[ $tax ] = $category;
	}

	$full_list = array();

	$events = get_posts( apply_filters( 'sc_calendar_query_args', $args ) );

	foreach ( $events as $event_id ) {

		$start = get_post_meta( $event_id, 'sc_event_date_time', true );
		$type  = get_post_meta( $event_id, 'sc_event_recurring', true );

		if ( ! empty( $type ) && 'none' != $type ) {

			$recurring = get_post_meta( $event_id, 'sc_all_recurring', true );

			if ( $recurring ) {
				foreach ( $recurring as $time ) {
					$full_list[ $time ][] = $event_id;
				}
			}
		} else {
			$full_list[ $start ][] = $event_id;
		}
	}

	ksort( $full_list );

	return apply_filters( 'sc_get_all_events', $full_list );
}

/**
 * Order the given list of event post_ids by the time of day they start
 *
 * @param array $events
 * @return array $events_time
 */
function sc_order_events_by_time( $events ) {
	$events_time = array();

	foreach ( $events as $event_id ) {

		// sort by sc_event_time_hour + sc_event_time_minute + sc_event_time_am_pm
		$hour = get_post_meta( $event_id, 'sc_event_time_hour', true );
		if ( empty( $hour ) ) {
			$hour = '00';
		}

		$minute = get_post_meta( $event_id, 'sc_event_time_minute', true );
		if ( empty( $minute ) ) {
			$minute = '00';
		}

		$am_pm = get_post_meta( $event_id, 'sc_event_time_am_pm', true );
		if ( 'pm' === $am_pm ) {
			$hour += 12;
		}

		$events_time[ $hour . $minute . $event_id ] = $event_id;
	}

	ksort( $events_time );

	return $events_time;
}

/** Deprecated ****************************************************************/

/**
 * Retrieves all recurring events.
 *
 * This function is no longer in use.
 *
 * @access      public
 * @since       1.1
 * @deprecated  2.0.0
 *
 * @param string $time Timestamp that recurring event should include
 * @param string $type type of recurring event to retrieve
 * @param string|null $category Category to limit events
 *
 * @return array
 */
function sc_get_recurring_events( $time, $type, $category = null ) {

	// Default variable values
	$start_key = $end_key = $date = '';

	switch ( $type ) {
		case 'weekly' :
			$start_key = 'sc_event_day_of_week';
			$end_key   = 'sc_event_end_day_of_week';
			$date      = gmdate( 'w', $time );
			break;

		case 'monthly' :
			$start_key = 'sc_event_day_of_month';
			$end_key   = 'sc_event_end_day_of_month';
			$date      = gmdate( 'd', $time );
			break;

		case 'yearly' :
			$date = ''; // these are reset below
			break;
	}

	$args = array(
		'post_type'      => sugar_calendar_get_event_post_type_id(),
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'order'          => 'asc',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => $start_key,
				'value'   => $date,
				'compare' => '<=',
			),
			array(
				'key'     => $end_key,
				'value'   => $date,
				'compare' => '>=',
			),
			array(
				'key'   => 'sc_event_recurring',
				'value' => $type
			),
			array(
				'key'     => 'sc_event_recurring',
				'value'   => 'none',
				'compare' => '!='
			),
			array(
				'key'     => 'sc_event_date_time',
				'value'   => $time,
				'compare' => '<='
			)
		),
	);

	if ( 'yearly' === $type ) {

		// for yearly we have to completely reset the meta query
		$args[ 'meta_query' ] = array(
			'relation' => 'AND',
			array(
				'key'     => 'sc_event_day_of_month',
				'value'   => gmdate( 'j', $time ),
				'compare' => '<=',
			),
			array(
				'key'     => 'sc_event_end_day_of_month',
				'value'   => gmdate( 'j', $time ),
				'compare' => '>=',
			),
			array(
				'key'     => 'sc_event_month',
				'value'   => gmdate( 'm', $time ),
				'compare' => '<=',
			),
			array(
				'key'     => 'sc_event_end_month',
				'value'   => gmdate( 'm', $time ),
				'compare' => '>=',
			),
			array(
				'key'     => 'sc_event_date_time',
				'value'   => $time,
				'compare' => '<='
			),
			array(
				'key'   => 'sc_event_recurring',
				'value' => $type
			)
		);
	}

	if ( ! is_null( $category ) ) {
		$tax          = sugar_calendar_get_calendar_taxonomy_id();
		$args[ $tax ] = $category;
	}

	return get_posts( apply_filters( 'sc_recurring_events_query', $args ) );
}

/**
 * Get a list of event post ids ordered by start time for a specific day
 *
 * This function is no longer in use.
 *
 * @since      1.1
 * @deprecated 2.0.0
 *
 * @param $display_day
 * @param $display_month
 * @param $display_year
 * @param $category
 *
 * @return array
 */
function sc_get_events_for_day( $display_day, $display_month, $display_year, $category ) {

	$args = array(
		'numberposts' => -1,
		'post_type'   => sugar_calendar_get_event_post_type_id(),
		'post_status' => 'publish',
		'orderby'     => 'meta_value_num',
		'order'       => 'asc',
		'fields'      => 'ids',
		'meta_query'  => array(
			'relation' => 'AND',
			array(
				'key'     => 'sc_event_date',
				'value'   => gmmktime( 0, 0, 0, $display_month, $display_day, $display_year ),
				'compare' => '<=',
			),
			array(
				'key'     => 'sc_event_end_date',
				'value'   => gmmktime( 0, 0, 0, $display_month, $display_day, $display_year ),
				'compare' => '>=',
			),
		),
	);

	if ( ! is_null( $category ) ) {
		$tax          = sugar_calendar_get_calendar_taxonomy_id();
		$args[ $tax ] = $category;
	}

	$single = get_posts( apply_filters( 'sc_calendar_query_args', $args ) );

	$recurring_timestamp = gmmktime( 0, 0, 0, $display_month, $display_day, $display_year );
	$yearly  = sc_get_recurring_events( $recurring_timestamp, 'yearly',  $category );
	$monthly = sc_get_recurring_events( $recurring_timestamp, 'monthly', $category );
	$weekly  = sc_get_recurring_events( $recurring_timestamp, 'weekly',  $category );

	$all_recurring = array_merge( $yearly, $monthly, $weekly );
	$recurring = array();

	if ( ! empty( $all_recurring ) ) {
		foreach ( $all_recurring as $event_id ) {

			$stop_day = sc_get_recurring_stop_date( $event_id );

			if ( $stop_day && $recurring_timestamp > $stop_day ) {
				continue;
			}

			if ( in_array( $event_id, $recurring ) ) {
				continue;
			}

			$recurring[] = $event_id;
		}
	}

	$events = array_merge( $single, $recurring );

	// sort by sc_event_time_hour + sc_event_time_minute + sc_event_time_am_pm
	$events = sc_order_events_by_time( $events );

	return apply_filters( 'sc_get_events_for_day', $events, $display_day, $display_month, $display_year, $category );
}
