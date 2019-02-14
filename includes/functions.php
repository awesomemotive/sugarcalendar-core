<?php

/**
 * Build Calendar for Event post type
 * @author Syamil MJ
 * @credit http://davidwalsh.name/php-calendar
 *
 * @param $month
 * @param $year
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar( $month, $year, $size = 'large', $category = null ) {

	//start draw table
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar sc-table">';

	$day_names_large = array(
		0 => __( 'Sunday', 'pippin_sc' ),
		1 => __( 'Monday', 'pippin_sc' ),
		2 => __( 'Tuesday', 'pippin_sc' ),
		3 => __( 'Wednesday', 'pippin_sc' ),
		4 => __( 'Thursday', 'pippin_sc' ),
		5 => __( 'Friday', 'pippin_sc' ),
		6 => __( 'Saturday', 'pippin_sc' )
	);

	$day_names_small = array(
		0 => __( 'Sun', 'pippin_sc' ),
		1 => __( 'Mon', 'pippin_sc' ),
		2 => __( 'Tue', 'pippin_sc' ),
		3 => __( 'Wed', 'pippin_sc' ),
		4 => __( 'Thr', 'pippin_sc' ),
		5 => __( 'Fri', 'pippin_sc' ),
		6 => __( 'Sat', 'pippin_sc' )
	);

	$week_start_day = sc_get_week_start_day();

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	// adjust day names for sites with Monday set as the start day
	if ( $week_start_day == 1 ) {
		$end_day = $day_names[0];
		array_shift( $day_names );
		$day_names[] = $end_day;
	}

	if ( $size == 'small' ) {
		foreach ( $day_names as $key => $day ) {
			$day_names[ $key ] = substr( $day, 0, 1 );
		}
	}

	$calendar.= '<tr class="calendar-row">';
	for ( $i = 0; $i <= 6; $i++ ) {
		$calendar .= '<th class="calendar-day-head">' . $day_names[$i] .'</th>';
	}
	$calendar .= '</tr>';

	//days and weeks vars now
	$running_day = date( 'w', mktime( 0, 0, 0, $month, 1, $year ) );
	if ( $week_start_day == 1 )
		$running_day = ( $running_day > 0 ) ? $running_day - 1 : 6;
		$days_in_month = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
		$days_in_this_week = 1;
		$day_counter = 0;

		//get today's date
		$time = current_time( 'timestamp' );
		$today_day = date( 'j', $time );
		$today_month = date( 'm', $time );
		$today_year = date( 'Y', $time );

		//row for week one */
		$calendar.= '<tr class="calendar-row">';

		//print "blank" days until the first of the current week
		for ( $x = 0; $x < $running_day; $x++ ):

			$calendar.= '<td class="calendar-day-np" valign="top"></td>';
			$days_in_this_week++;

		endfor;

		//keep going with days
		for ( $list_day = 1; $list_day <= $days_in_month; $list_day++ ) :

			$today = ( $today_day == $list_day && $today_month == $month && $today_year == $year ) ? 'today' : '';

			$events = sc_get_events_for_day( $list_day, $month, $year, $category );

			$cal_event = '';

			foreach ( $events as $event ) :

				$id = $event;
				$categories = array();

				// Collect event categories for css classes
				$event_categories = wp_get_object_terms( $id, 'sc_event_category', array( 'fields' => 'slugs' ) );
				if ( ! empty( $event_categories ) ) {
					if ( ! is_wp_error( $event_categories ) ) {
						foreach( $event_categories as $slug ) {
							$categories[] = $slug;
						}
					}
				}
				$categories = array_unique( $categories );
				$category_string = implode( ' ', $categories );

				if ( $size == 'small' ) {
					$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '" title="' . esc_html( get_the_title( $id ) ) . '">&bull;</a>';
				} else {
					$link = '<a href="'. get_permalink( $id ) .'" class="' .  $category_string . '">'. get_the_title( $id )  .'</a><br/>';
				}

				$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $id, $size );

			endforeach;

			$cal_day = '<td class="calendar-day '. $today .'" valign="top"><div class="sc_day_div">';

			// add in the day numbering
			$cal_day .= '<div class="day-number day-' . $list_day . '">'.$list_day.'</div>';

			$calendar .= $cal_day;

			$calendar .= $cal_event ? $cal_event : '';

			$calendar .= '</div></td>';

			if ( $running_day == 6 ):

				if ( ( $list_day < $days_in_month ) ) {

					$calendar.= '</tr>';
					$calendar .= '<tr class="calendar-row">';
					$running_day = -1;
					$days_in_this_week = 0;
				}

			endif;

			$days_in_this_week++; $running_day++; $day_counter++;

		endfor;

		//finish the rest of the days in the week
		if ( $days_in_this_week < 8 ):
			for ( $x = 1; $x <= ( 8 - $days_in_this_week ); $x++ ):
				$calendar.= '<td class="calendar-day-np" valign="top"><div class="sc_day_div"></div></td>';
			endfor;
		endif;

	wp_reset_postdata();

	//final row
	$calendar.= '</tr>';

	//end the table
	$calendar.= '</table>';

	//all done, return the completed table
	return $calendar;
}

/**
 * Added function to call default sc_draw_calendar()
 *
 * @param $display_time
 * @param string $size
 * @param null $category
 *
 * @return string
 */
function sc_draw_calendar_month( $display_time, $size = 'large', $category = null ) {
	$month = date('n', $display_time );
	$year  = date('Y', $display_time );
	return sc_draw_calendar( $month, $year, $size, $category );
}

/**
 * Draw the weekly calendar
 *
 * @param $display_time
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar_week( $display_time, $size = 'large', $category = null ) {

	//start draw table
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar sc-table">';

	$day_names_large = array(
		0 => __( 'Sunday', 'pippin_sc' ),
		1 => __( 'Monday', 'pippin_sc' ),
		2 => __( 'Tuesday', 'pippin_sc' ),
		3 => __( 'Wednesday', 'pippin_sc' ),
		4 => __( 'Thursday', 'pippin_sc' ),
		5 => __( 'Friday', 'pippin_sc' ),
		6 => __( 'Saturday', 'pippin_sc' )
	);

	$day_names_small = array(
		0 => __( 'Sun', 'pippin_sc' ),
		1 => __( 'Mon', 'pippin_sc' ),
		2 => __( 'Tue', 'pippin_sc' ),
		3 => __( 'Wed', 'pippin_sc' ),
		4 => __( 'Thr', 'pippin_sc' ),
		5 => __( 'Fri', 'pippin_sc' ),
		6 => __( 'Sat', 'pippin_sc' )
	);

	$week_start_day = sc_get_week_start_day();

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	// adjust day names for sites with Monday set as the start day
	if ( $week_start_day == 1 ) {
		$end_day = $day_names[0];
		array_shift( $day_names );
		$day_names[] = $end_day;
	}

	if ( $size == 'small' ) {
		foreach ( $day_names as $key => $day ) {
			$day_names[ $key ] = substr( $day, 0, 1 );
		}
	}

	$calendar.= '<tr class="calendar-row">';
	for ( $i = 0; $i <= 6; $i++ ) {
		$calendar .= '<th class="calendar-day-head">' . $day_names[$i] .'</th>';
	}
	$calendar .= '</tr>';

	// get the values for the first day of week where $display_time occurs
	$day_of_week = date('w', $display_time);
	$display_time  = strtotime('-'.$day_of_week.' days',$display_time );
	$display_day   = date('j', $display_time);
	$display_month = date('n', $display_time );
	$display_year  = date('Y', $display_time );

	//get today's date
	$time = current_time( 'timestamp' );
	$today_day = date( 'j', $time );
	$today_month = date( 'm', $time );
	$today_year = date( 'Y', $time );

	// start row
	$calendar.= '<tr class="calendar-row">';

	// output seven days
	for ( $list_day = 1; $list_day <= 7; $list_day++ ) :

		$today = ( $today_day == $display_day && $today_month == $display_month && $today_year == $display_year ) ? 'today' : '';

		$events = sc_get_events_for_day( $display_day, $display_month, $display_year, $category );

		$cal_event = '';

		foreach ( $events as $event ) :

			$id = $event;
			$categories = array();

			// Collect event categories for css classes
			$event_categories = wp_get_object_terms( $id, 'sc_event_category', array( 'fields' => 'slugs' ) );
			if ( ! empty( $event_categories ) ) {
				if ( ! is_wp_error( $event_categories ) ) {
					foreach( $event_categories as $slug ) {
						$categories[] = $slug;
					}
				}
			}
			$categories = array_unique( $categories );
			$category_string = implode( ' ', $categories );

			if ( $size == 'small' ) {
				$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '" title="' . get_the_title( $id ) . '">&bull;</a>';
			} else {
				$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '">'. get_the_title( $id )  .'</a><br/>';
			}

			$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $id, $size );


		endforeach;


		$cal_day = '<td class="calendar-day '. $today .'" valign="top"><div class="sc_day_div">';

		// add in the day numbering
		$cal_day .= '<div class="day-number day-' . $display_day . '">'.$display_day.'</div>';

		$calendar .= $cal_day;

		$calendar .= $cal_event ? $cal_event : '';

		$calendar .= '</div></td>';

		$display_time  = strtotime('+1 day',$display_time );
		$display_day   = date('j', $display_time);
		$display_month = date('n', $display_time );
		$display_year  = date('Y', $display_time );

	endfor;

	wp_reset_postdata();

	// finish row
	$calendar.= '</tr>';

	// end the calendar
	$calendar.= '</table>';

	//all done, return the completed table
	return $calendar;
}

/**
 * Draw the two week calendar
 *
 * @param $display_time
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar_2week( $display_time, $size = 'large', $category = null ) {

	//start draw table
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar sc-table">';

	$day_names_large = array(
		0 => __( 'Sunday', 'pippin_sc' ),
		1 => __( 'Monday', 'pippin_sc' ),
		2 => __( 'Tuesday', 'pippin_sc' ),
		3 => __( 'Wednesday', 'pippin_sc' ),
		4 => __( 'Thursday', 'pippin_sc' ),
		5 => __( 'Friday', 'pippin_sc' ),
		6 => __( 'Saturday', 'pippin_sc' )
	);

	$day_names_small = array(
		0 => __( 'Sun', 'pippin_sc' ),
		1 => __( 'Mon', 'pippin_sc' ),
		2 => __( 'Tue', 'pippin_sc' ),
		3 => __( 'Wed', 'pippin_sc' ),
		4 => __( 'Thr', 'pippin_sc' ),
		5 => __( 'Fri', 'pippin_sc' ),
		6 => __( 'Sat', 'pippin_sc' )
	);

	$week_start_day = sc_get_week_start_day();

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	// adjust day names for sites with Monday set as the start day
	if ( $week_start_day == 1 ) {
		$end_day = $day_names[0];
		array_shift( $day_names );
		$day_names[] = $end_day;
	}

	if ( $size == 'small' ) {
		foreach ( $day_names as $key => $day ) {
			$day_names[ $key ] = substr( $day, 0, 1 );
		}
	}

	$calendar.= '<tr class="calendar-row">';
	for ( $i = 0; $i <= 6; $i++ ) {
		$calendar .= '<th class="calendar-day-head">' . $day_names[$i] .'</th>';
	}
	$calendar .= '</tr>';

	// get the values for the first day of week where $display_time occurs
	$day_of_week = date('w', $display_time);
	$display_time  = strtotime('-'.$day_of_week.' days',$display_time );
	$display_day   = date('j', $display_time);
	$display_month = date('n', $display_time );
	$display_year  = date('Y', $display_time );

	//get today's date
	$time = current_time( 'timestamp' );
	$today_day = date( 'j', $time );
	$today_month = date( 'm', $time );
	$today_year = date( 'Y', $time );

	// start row
	$calendar.= '<tr class="calendar-row">';

	// output seven days
	for ( $list_day = 1; $list_day <= 14; $list_day++ ) :

		$today = ( $today_day == $display_day && $today_month == $display_month && $today_year == $display_year ) ? 'today' : '';

		$events = sc_get_events_for_day( $display_day, $display_month, $display_year, $category );

		$cal_event = '';

		foreach ( $events as $event ) :

			$id = $event;
			$categories = array();

			// Collect event categories for css classes
			$event_categories = wp_get_object_terms( $id, 'sc_event_category', array( 'fields' => 'slugs' ) );
			if ( ! empty( $event_categories ) ) {
				if ( ! is_wp_error( $event_categories ) ) {
					foreach( $event_categories as $slug ) {
						$categories[] = $slug;
					}
				}
			}
			$categories = array_unique( $categories );
			$category_string = implode( ' ', $categories );

			if ( $size == 'small' ) {
				$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '" title="' . get_the_title( $id ) . '">&bull;</a>';
			} else {
				$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '">'. get_the_title( $id )  .'</a><br/>';
			}

			$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $id, $size );


		endforeach;

		$cal_day = '<td class="calendar-day '. $today .'" valign="top"><div class="sc_day_div">';

		// add in the day numbering
		$cal_day .= '<div class="day-number day-' . $display_day . '">'.$display_day.'</div>';

		$calendar .= $cal_day;

		$calendar .= $cal_event ? $cal_event : '';

		$calendar .= '</div></td>';

		if ( $list_day == 7 ){
			$calendar.= '</tr>';
			$calendar .= '<tr class="calendar-row">';
		}

		$display_time  = strtotime('+1 day',$display_time );
		$display_day   = date('j', $display_time);
		$display_month = date('n', $display_time );
		$display_year  = date('Y', $display_time );

	endfor;

	wp_reset_postdata();

	// finish row
	$calendar.= '</tr>';

	// end the calendar
	$calendar.= '</table>';

	//all done, return the completed table
	return $calendar;
}

/**
 * Draw the daily calendar
 *
 * @param $display_time
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar_day( $display_time, $size = 'large', $category = null ) {

	//start draw table
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';

	$day_names_large = array(
		0 => __( 'Sunday', 'pippin_sc' ),
		1 => __( 'Monday', 'pippin_sc' ),
		2 => __( 'Tuesday', 'pippin_sc' ),
		3 => __( 'Wednesday', 'pippin_sc' ),
		4 => __( 'Thursday', 'pippin_sc' ),
		5 => __( 'Friday', 'pippin_sc' ),
		6 => __( 'Saturday', 'pippin_sc' )
	);

	$day_names_small = array(
		0 => __( 'Sun', 'pippin_sc' ),
		1 => __( 'Mon', 'pippin_sc' ),
		2 => __( 'Tue', 'pippin_sc' ),
		3 => __( 'Wed', 'pippin_sc' ),
		4 => __( 'Thr', 'pippin_sc' ),
		5 => __( 'Fri', 'pippin_sc' ),
		6 => __( 'Sat', 'pippin_sc' )
	);

	$day_of_week = date('w', $display_time);

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	if ( $size == 'small' ) {
		foreach ( $day_names as $key => $day ) {
			$day_names[ $key ] = substr( $day, 0, 1 );
		}
	}

	$calendar.= '<tr class="calendar-row">';
	$calendar .= '<th class="calendar-day-head">' . $day_names[$day_of_week] .'</th>';
	$calendar .= '</tr>';

	$display_day   = date('j', $display_time);
	$display_month = date('n', $display_time );
	$display_year  = date('Y', $display_time );

	//get today's date
	$time = current_time( 'timestamp' );
	$today_day = date( 'j', $time );
	$today_month = date( 'm', $time );
	$today_year = date( 'Y', $time );

	// start row
	$calendar.= '<tr class="calendar-row">';

	// output current day
	$today = ( $today_day == $display_day && $today_month == $display_month && $today_year == $display_year ) ? 'today' : '';

	$events = sc_get_events_for_day( $display_day, $display_month, $display_year, $category );

	$cal_event = '';

	foreach ( $events as $event ) :

		$id = $event;
		$categories = array();

		// Collect event categories for css classes
		$event_categories = wp_get_object_terms( $id, 'sc_event_category', array( 'fields' => 'slugs' ) );
		if ( ! empty( $event_categories ) ) {
			if ( ! is_wp_error( $event_categories ) ) {
				foreach( $event_categories as $slug ) {
					$categories[] = $slug;
				}
			}
		}
		$categories = array_unique( $categories );
		$category_string = implode( ' ', $categories );

		if ( $size == 'small' ) {
			$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '" title="' . esc_html( get_the_title( $id ) ) . '">&bull;</a>';
		} else {
			$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '">'. get_the_title( $id )  .'</a><br/>';
		}

		$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $id, $size );

	endforeach;

	$cal_day = '<td class="calendar-day '. $today .'" valign="top"><div class="sc_day_div">';

	// add in the day numbering
	$cal_day .= '<div class="day-number day-' . $display_day . '">'.$display_day.'</div>';

	$calendar .= $cal_day;

	$calendar .= $cal_event ? $cal_event : '';

	$calendar .= '</div></td>';

	wp_reset_postdata();

	// finish row
	$calendar.= '</tr>';

	// end the calendar
	$calendar.= '</table>';

	//all done, return the completed table
	return $calendar;
}

/**
 * Draw the four day calendar
 *
 * @param $display_time
 * @param string $size
 * @param null|string $category
 *
 * @return string
 */
function sc_draw_calendar_4day( $display_time, $size = 'large', $category = null ) {
	//start draw table
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar sc-table">';

	$day_names_large = array(
		0 => __( 'Sunday', 'pippin_sc' ),
		1 => __( 'Monday', 'pippin_sc' ),
		2 => __( 'Tuesday', 'pippin_sc' ),
		3 => __( 'Wednesday', 'pippin_sc' ),
		4 => __( 'Thursday', 'pippin_sc' ),
		5 => __( 'Friday', 'pippin_sc' ),
		6 => __( 'Saturday', 'pippin_sc' )
	);

	$day_names_small = array(
		0 => __( 'Sun', 'pippin_sc' ),
		1 => __( 'Mon', 'pippin_sc' ),
		2 => __( 'Tue', 'pippin_sc' ),
		3 => __( 'Wed', 'pippin_sc' ),
		4 => __( 'Thr', 'pippin_sc' ),
		5 => __( 'Fri', 'pippin_sc' ),
		6 => __( 'Sat', 'pippin_sc' )
	);

	$day_of_week = date('w', $display_time);

	$day_names = $size == 'small' ? $day_names_small : $day_names_large;

	if ( $size == 'small' ) {
		foreach ( $day_names as $key => $day ) {
			$day_names[ $key ] = substr( $day, 0, 1 );
		}
	}

	$calendar.= '<tr class="calendar-row">';
	for ( $i = 0; $i <= 3; $i++ ) {
		$calendar .= '<th class="calendar-day-head">' . $day_names[$day_of_week] .'</th>';
		if ($day_of_week == 6 ){
			$day_of_week = 0;
		} else {
			$day_of_week++;
		}
	}
	$calendar .= '</tr>';

	$display_day   = date('j', $display_time);
	$display_month = date('n', $display_time );
	$display_year  = date('Y', $display_time );

	//get today's date
	$time = current_time( 'timestamp' );
	$today_day = date( 'j', $time );
	$today_month = date( 'm', $time );
	$today_year = date( 'Y', $time );

	// start row
	$calendar.= '<tr class="calendar-row">';

	// output four days
	for ( $list_day = 0; $list_day <= 3; $list_day++ ) :

		$today = ( $today_day == $display_day && $today_month == $display_month && $today_year == $display_year ) ? 'today' : '';

		$events = sc_get_events_for_day( $display_day, $display_month, $display_year, $category );

		$cal_event = '';

		foreach ( $events as $event ) :

			$id = $event;
			$categories = array();

			// Collect event categories for css classes
			$event_categories = wp_get_object_terms( $id, 'sc_event_category', array( 'fields' => 'slugs' ) );
			if ( ! empty( $event_categories ) ) {
				if ( ! is_wp_error( $event_categories ) ) {
					foreach( $event_categories as $slug ) {
						$categories[] = $slug;
					}
				}
			}
			$categories = array_unique( $categories );
			$category_string = implode( ' ', $categories );

			if ( $size == 'small' ) {
				$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '" title="' . esc_html( get_the_title( $id ) ) . '">&bull;</a>';
			} else {
				$link = '<a href="'. get_permalink( $id ) . '" class="' .  $category_string . '">'. get_the_title( $id )  .'</a><br/>';
			}

			$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $id, $size );

		endforeach;

		$cal_day = '<td class="calendar-day '. $today .'" valign="top"><div class="sc_day_div">';

		// add in the day numbering
		$cal_day .= '<div class="day-number day-' . $display_day . '">'.$display_day.'</div>';

		$calendar .= $cal_day;

		$calendar .= $cal_event ? $cal_event : '';

		$calendar .= '</div></td>';

		$display_time  = strtotime('+1 day',$display_time );
		$display_day   = date('j', $display_time);
		$display_month = date('n', $display_time );
		$display_year  = date('Y', $display_time );

	endfor;

	wp_reset_postdata();

	// finish row
	$calendar.= '</tr>';

	// end the calendar
	$calendar.= '</table>';

	//all done, return the completed table
	return $calendar;
}

/**
 * Modifies the WHERE flag for event queries
 *
 * @since 1.5.0
 * @return string
 */
function sc_events_where( $where ){
	global $wpdb;

	//$where = " AND (($wpdb->postmeta.meta_key = 'sc_event_date' AND $wpdb->postmeta.meta_value >= $start_timestamp) AND ($wpdb->postmeta.meta_key = 'sc_event_end_date' AND $wpdb->postmeta.meta_value <= $end_timestamp)) ";

	return $where;
}

/**
 * Month Num To Name
 *
 * Takes a month number and returns the
 * three letter name of it.
 *
 * @access      public
 * @since       1.0
 * @return      string
 */
function sc_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );
	return date_i18n( 'F', $timestamp );
}

/**
 * Determines whether the current page has a calendar on it
 *
 * @access      public
 * @since       1.0
 * @return      string
 */
function sc_is_calendar_page() {
	global $post;


	if ( !is_object( $post ) ) {
		return false;
	}

	if ( strpos( $post->post_content, '[sc_events_calendar' ) !== false ) {
		return true;
	}

	return false;
}


/**
 * Retrieves the calendar date for an event
 *
 * @access      public
 * @since       1.0
 * @param 		int $event_id  int The ID number of the event
 * @param 		bool $formatted bool Whether to return a time stamp or the nicely formatted date
 * @return      string
 */
function sc_get_event_date( $event_id, $formatted = true ) {
	$date     = get_post_meta( $event_id, 'sc_event_date_time', true );
	$end_date = get_post_meta( $event_id, 'sc_event_end_date_time', true );

	if ( empty( $date ) || empty ( $end_date ) ) {
		return '';
	}

	if ( $formatted ) {
		$date     = date_i18n( sc_get_date_format(), $date );
		$end_date = date_i18n( sc_get_date_format(), $end_date );

		if( $end_date != $date ) {
			$date = $date . '<span class="sc-date-start-end-sep"> - </span>' . $end_date;
		}
	}

	return $date;
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
function sc_get_formatted_date( $event_id, $timestamp = null ) {

	$date     = date_i18n( sc_get_date_format(), $timestamp );

	return $date;
}



/**
 * Retrieves the time for an event
 *
 * @access      public
 * @since       1.0
 * @param unknown $event_id int The ID number of the event
 * @return      array
 */
function sc_get_event_time( $event_id ) {
	$start_time = sc_get_event_start_time( $event_id );
	$end_time = sc_get_event_end_time( $event_id );

	return apply_filters( 'sc_event_time', array( 'start' => $start_time, 'end' => $end_time ) );
}


/**
 * Retrieves the start time for an event
 *
 * @access      public
 * @since       1.0
 * @param unknown $event_id int The ID number of the event
 * @return      string
 */
function sc_get_event_start_time( $event_id ) {
	$start  = get_post_meta( $event_id, 'sc_event_date', true );

	if ( empty( $start ) ) {
		return '';
	}

	$day  = date( 'd', $start );
	$month  = date( 'm', $start );
	$year  = date( 'Y', $start );

	$hour  = absint( get_post_meta( $event_id, 'sc_event_time_hour', true ) );
	$minute = absint( get_post_meta( $event_id, 'sc_event_time_minute', true ) );
	$am_pm  = get_post_meta( $event_id, 'sc_event_time_am_pm', true );

	$hour  = $hour ? $hour : null;
	$minute = $minute ? $minute : null;
	$am_pm  = $am_pm ? $am_pm : null;

	if ( $am_pm == 'pm' && $hour < 12 ) {
		$hour += 12;
	} elseif ( $am_pm == 'am' && $hour >= 12 ) {
		$hour -= 12;
	}

	if ( $hour == null && $minute == null ) {
		$time = null;
	} else {
		$time = date_i18n( sc_get_time_format(), mktime( $hour, $minute, 0, $month, $day, $year ) );
	}

	return apply_filters( 'sc_event_start_time', $time, $hour, $minute, $am_pm );
}


/**
 * Retrieves the end time for an event
 *
 * @access      public
 * @since       1.0
 * @param unknown $event_id int The ID number of the event
 * @return      string
 */
function sc_get_event_end_time( $event_id ) {
	$end  = get_post_meta( $event_id, 'sc_event_end_date', true );

	if ( empty( $end )  ) {
		return '';
	}

	$day  = date( 'd', $end );
	$month  = date( 'm', $end );
	$year  = date( 'Y', $end );

	$hour  = get_post_meta( $event_id, 'sc_event_end_time_hour', true );
	$minute = get_post_meta( $event_id, 'sc_event_end_time_minute', true );
	$am_pm  = get_post_meta( $event_id, 'sc_event_end_time_am_pm', true );

	$hour  = $hour ? $hour : null;
	$minute = $minute ? $minute : null;
	$am_pm  = $am_pm ? $am_pm : null;

	if ( $am_pm == 'pm' && $hour < 12 )
		$hour += 12;
	elseif ( $am_pm == 'am' && $hour >= 12 )
		$hour -= 12;

	if ( $hour == null && $minute == null ){
		$time = null;
	} else {
		$time = date_i18n( sc_get_time_format(), mktime( $hour, $minute, 0, $month, $day, $year ) );
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
	$recurring = ( $recurring && $recurring != 'none' ) ? true : false;
	return $recurring;
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

	if( ! sc_is_recurring( $event_id ) ) {
		$recur_until = false;
	}

	if( strlen( trim( $recur_until ) ) == 0 ) {
		$recur_until = false;
	}

	return apply_filters( 'sc_recurring_stop_date', $recur_until, $event_id );
}


/**
 * Retrieves all recurring events
 *
 * @access      public
 * @since       1.1
 *
 * @param string $time Timestamp that recurring event should include
 * @param string $type type of recurring event to retrieve
 * @param string|null $category Category to limit events
 *
 * @return array
 */
function sc_get_recurring_events( $time, $type, $category = null ) {

	$start_key = '';
	$end_key = '';
	$date = '';

	switch ( $type ) {

	case 'weekly' :
		$start_key = 'sc_event_day_of_week';
		$end_key   = 'sc_event_end_day_of_week';
		$date      = date( 'w', $time );
		break;
	case 'monthly' :
		$start_key = 'sc_event_day_of_month';
		$end_key   = 'sc_event_end_day_of_month';
		$date      = date( 'd', $time );
		break;
	case 'yearly' :
		$key = ''; // just default values hre
		$date = ''; // these are reset below
		break;
	}

	$args = array(
		'post_type' => 'sc_event',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'fields' => 'ids',
		'order' => 'asc',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => $start_key,
				'value' => $date,
				'compare' => '<=',
			),
			array(
				'key' => $end_key,
				'value' => $date,
				'compare' => '>=',
			),
			array(
				'key' => 'sc_event_recurring',
				'value' => $type
			),
			array(
				'key' => 'sc_event_recurring',
				'value' => 'none',
				'compare' => '!='
			),
			array(
				'key' => 'sc_event_date_time',
				'value' => $time,
				'compare' => '<='
			)
		),
	);

	if ( $type == 'yearly' ) {
		// for yearly we have to completely reset the meta query
		$args['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key' => 'sc_event_day_of_month',
				'value' => date( 'j', $time ),
				'compare' => '<=',
			),
			array(
				'key' => 'sc_event_end_day_of_month',
				'value' => date( 'j', $time ),
				'compare' => '>=',
			),
			array(
				'key' => 'sc_event_month',
				'value' => date( 'm', $time ),
				'compare' => '<=',
			),
			array(
				'key' => 'sc_event_end_month',
				'value' => date( 'm', $time ),
				'compare' => '>=',
			),
			array(
				'key' => 'sc_event_date_time',
				'value' => $time,
				'compare' => '<='
			),
			array(
				'key' => 'sc_event_recurring',
				'value' => $type
			)
		);
	}
	if ( ! is_null( $category ) ) {
		$args['sc_event_category'] = $category;
	}

	return get_posts( apply_filters( 'sc_recurring_events_query', $args ) );
}

/**
 * Shows the date of recurring events
 *
 * @access      public
 * @since       1.1.1
 * @return      array
 */
function sc_show_single_recurring_date( $event_id ) {

	$recurring_schedule = get_post_meta( $event_id, 'sc_event_recurring', true );
	$recur_until 		= sc_get_recurring_stop_date( $event_id );
	$event_date_time 	= get_post_meta( $event_id, 'sc_event_date_time', true );
	$date_format		= sc_get_date_format();

	$format = apply_filters( 'sc_recurring_date_format', array(), $event_date_time, $recur_until );

	if( $recur_until ) :

		switch ( $recurring_schedule ) {

			case 'weekly':

				if ( isset( $format['weekly'] ) ) {
					echo $format['weekly'];
				} else {
					echo sprintf( __( 'Starts %s then every %s until %s', 'pippin_sc' ),
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'l', $event_date_time ),
						date_i18n( $date_format, $recur_until ) );
				}
				break;

			case 'monthly':

				if ( isset( $format['monthly'] ) ) {
					echo $format['monthly'];
				} else {
					echo sprintf( __( 'Starts %s then every month on the %s until %s', 'pippin_sc' ),
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'jS', $event_date_time ),
						date_i18n( $date_format, $recur_until ) );
				}
				break;

			case 'yearly':

				if ( isset( $format['yearly'] ) ) {
					echo $format['yearly'];
				} else {
					echo sprintf( __( 'Starts %s then every year on the %s of %s until %s', 'pippin_sc' ),
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

				if ( isset( $format['weekly'] ) ) {
					echo $format['weekly'];
				} else {
					echo sprintf( __( 'Starts %s then every %s', 'pippin_sc' ),
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'l', $event_date_time ) );
				}
				break;

			case 'monthly':

				if ( isset( $format['monthly'] ) ) {
					echo $format['monthly'];
				} else {
					echo sprintf( __( 'Starts %s then every month on the %s', 'pippin_sc' ),
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'jS', $event_date_time ) );
				}
				break;

			case 'yearly':

				if ( isset( $format['yearly'] ) ) {
					echo $format['yearly'];
				} else {
					echo sprintf( __( 'Starts %s then every year on the %s of %s', 'pippin_sc' ),
						date_i18n( $date_format, $event_date_time ),
						date_i18n( 'jS', $event_date_time ),
						date_i18n( 'F', $event_date_time ) );
				}
				break;

		}

	endif;
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

	if ( empty( $format ) ) {
		// default to WordPress value
		$format = get_option( 'date_format' );
	}

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

	if ( empty( $format ) ) {
		// default to WordPress value
		$format = get_option( 'time_format' );
	}

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

	if ( empty( $start_day ) && 0 !== $start_day ) {
		// default to WordPress value
		$start_day = get_option( 'start_of_week' );
	}

	return apply_filters( 'sc_week_start_day', $start_day );
}

/**
 * For recurring events, calculate recurrences, then save to sc_all_recurring meta
 *
 * @since 1.6.0
 * @param int $event_id
 */
function sc_update_recurring_events( $event_id = null ) {

	if ( $event_id ){
		$events[] = $event_id;
	} else {
		$args = array(
			'numberposts' => - 1,
			'post_type'   => 'sc_event',
			'post_status' => 'publish',
			'fields'      => 'ids',
			'order'       => 'asc',
		);

		$events = get_posts( $args );
	}

	foreach ( $events as $event_id ) {

		$type  = get_post_meta( $event_id, 'sc_event_recurring', true );

		if ( ! empty( $type ) && 'none' != $type ) {
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
function sc_calculate_recurring( $event_id ){
	$start = get_post_meta( $event_id, 'sc_event_date_time', true );
	$until = get_post_meta( $event_id, 'sc_recur_until', true );
	$type  = get_post_meta( $event_id, 'sc_event_recurring', true);

	$recurring = array();

	// add first occurrence of event
	$recurring[] = $start;
	$current = $start;

	while ( $until > $current ){
		switch ($type) {
			case 'weekly':
				$current = strtotime("+1 week", $current);
				break;
			case 'monthly':
				$current = strtotime("+1 month", $current);
				break;
			case 'yearly':
				$current = strtotime("+1 year", $current);
				break;
		}
		if ( $until > $current ){
			$recurring[] = $current;
		}

	}

	return apply_filters( 'sc_calculate_recurring', $recurring);
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
		'post_type'   => 'sc_event',
		'post_status' => 'publish',
		'orderby'     => 'meta_value_num',
		'fields'      => 'ids',
		'order'       => 'asc',
	);
	if ( ! is_null( $category ) ) {
		$args['sc_event_category'] = $category;
	}


	$full_list = array();

	$events = get_posts( apply_filters( 'sc_calendar_query_args', $args ) );

	foreach ( $events as $event_id ) {

		$start = get_post_meta( $event_id, 'sc_event_date_time', true );
		$type  = get_post_meta( $event_id, 'sc_event_recurring', true );

		if ( ! empty( $type ) && 'none' != $type ) {

			$recurring = get_post_meta( $event_id, 'sc_all_recurring', true);

			if( $recurring ) {
				foreach ($recurring as $time){
					$full_list[ $time ][] = $event_id;
				}
			}
			
		} else {
			$full_list[ $start ][] = $event_id;
		}

	}
	ksort($full_list);

	return apply_filters('sc_get_all_events', $full_list);
}

/**
 * Order the given list of event post_ids by the time of day they start
 *
 * @param array $events
 * @return array $events_time
 */
function sc_order_events_by_time( $events ){
	$events_time = array();

	foreach ( $events as $key => $event_id ){
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

		if ( 'pm' === $am_pm ){
			$hour += 12;
		}
		$events_time[ $hour . $minute . $event_id ] = $event_id;
	}

	ksort( $events_time );
	return $events_time;
}

/**
 * Get a list of event post ids ordered by start time for a specific day
 *
 * @param $display_day
 * @param $display_month
 * @param $display_year
 * @param $category
 *
 * @return array
 */
function sc_get_events_for_day( $display_day, $display_month, $display_year, $category ){

	$args = array(
		'numberposts' 	=> -1,
		'post_type' 	=> 'sc_event',
		'post_status' 	=> 'publish',
		'orderby' 		=> 'meta_value_num',
		'order' 		=> 'asc',
		'fields'        => 'ids',
		'meta_query'    => array(
			'relation'  => 'AND',
			array(
				'key'   => 'sc_event_date',
				'value' => mktime( 0, 0, 0, $display_month, $display_day, $display_year ),
				'compare'=>'<=',
			),
			array(
				'key'   => 'sc_event_end_date',
				'value' => mktime( 0, 0, 0, $display_month, $display_day, $display_year ),
				'compare'=>'>=',
			),
		),
	);

	if ( !is_null( $category ) ) {
		$args['sc_event_category'] = $category;
	}

	$single = get_posts( apply_filters( 'sc_calendar_query_args', $args ) );

	$recurring_timestamp = mktime( 0, 0, 0, $display_month, $display_day, $display_year );
	$yearly 	    = sc_get_recurring_events( $recurring_timestamp, 'yearly', $category );
	$monthly 	    = sc_get_recurring_events( $recurring_timestamp, 'monthly', $category );
	$weekly 	    = sc_get_recurring_events( $recurring_timestamp, 'weekly', $category );

	$all_recurring	= array_merge( $yearly, $monthly, $weekly );
	$recurring      = array();

	if ( ! empty( $all_recurring ) ) {
		foreach ( $all_recurring as $event_id ) {

			$stop_day = sc_get_recurring_stop_date( $event_id );

			if( $stop_day && $recurring_timestamp > $stop_day ) {
				continue;
			}

			if ( in_array( $event_id, $recurring )){
				continue;
			}

			$recurring[] = $event_id;

		}
	}

	$events 	= array_merge( $single, $recurring );

	// sort by sc_event_time_hour + sc_event_time_minute + sc_event_time_am_pm
	$events = sc_order_events_by_time( $events );

	return apply_filters( 'sc_get_events_for_day', $events, $display_day, $display_month, $display_year, $category );
}