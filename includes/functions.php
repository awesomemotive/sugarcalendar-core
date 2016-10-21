<?php

/**
* Build Calendar for Event post type
*
* Credits : http://davidwalsh.name/php-calendar
*
*/

function sc_draw_calendar( $month, $year, $size = 'large', $category = null ) {

	// Layout conditional 1
	if($size == 'responsive') {
		$calendar = "<div id=\"calendar\" class=\"responsive\">\n";
	} else {
		$calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';
	}

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
		$start_day = $day_names[1];
		array_shift( $day_names );
		$day_names[] = $end_day;
	}

	if ( $size == 'small' ) {
		foreach ( $day_names as $key => $day ) {
			$day_names[ $key ] = substr( $day, 0, 1 );
		}
	}

	// Layout conditional 2
	if($size == 'responsive') {
		$calendar .= "\t\t\t\t<ul class=\"calendar-day-head\">\n";
		for ( $i = 0; $i <= 6; $i++ ) {
			$calendar .= "\t\t\t\t\t<li>" . $day_names[$i] . "</li>\n";
		}
		$calendar .= "\t\t\t\t</ul>\n";
	} else {
		$calendar.= '<tr class="calendar-row">';
		for ( $i = 0; $i <= 6; $i++ ) {
			$calendar .= '<th class="calendar-day-head">' . $day_names[$i] .'</th>';
		}
		$calendar .= '</tr>';
	}

	//days and weeks vars now
	$running_day = date( 'w', mktime( 0, 0, 0, $month, 1, $year ) );
	if ( $week_start_day == 1 )
		$running_day = ( $running_day > 0 ) ? $running_day - 1 : 6;
		$days_in_month = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
		$days_in_this_week = 1;
		$day_counter = 0;
		$dates_array = array();

		//get today's date
		$time = current_time( 'timestamp' );
		$today_day = date( 'j', $time );
		$today_month = date( 'm', $time );
		$today_year = date( 'Y', $time );

		//row for week one */
		// Layout conditional 3
		if($size == 'responsive') {
			$calendar .= "\t\t\t\t<ul class=\"calendar-row\">\n";
		} else {
			$calendar .= '<tr class="calendar-row">';
		}

		//print "blank" days until the first of the current week
		for ( $x = 0; $x < $running_day; $x++ ):
			// Layout conditional 4
			if($size == 'responsive') {
				$calendar .= "\t\t\t\t\t<li class=\"calendar-day calendar-day-np\"></li>\n";
			} else {
				$calendar .= '<td class="calendar-day-np" valign="top"></td>';
			}
			$days_in_this_week++;

		endfor;

		//keep going with days
		for ( $list_day = 1; $list_day <= $days_in_month; $list_day++ ) :

			$today = ( $today_day == $list_day && $today_month == $month && $today_year == $year ) ? 'today' : '';

			$args = array(
				'numberposts' 	=> -1,
				'post_type' 	=> 'sc_event',
				'post_status' 	=> 'publish',
				'orderby' 		=> 'meta_value_num',
				'order' 		=> 'asc',
				'meta_query'    => array(
					'relation'  => 'AND',
					array(
						'key'   => 'sc_event_date',
						'value' => mktime( 0, 0, 0, $month, $list_day, $year ),
						'compare'=>'<=',
					),
					array(
						'key'   => 'sc_event_end_date',
						'value' => mktime( 0, 0, 0, $month, $list_day, $year ),
						'compare'=>'>=',
					),
				),
			);

			if ( !is_null( $category ) ) {
				$args['sc_event_category'] = $category;
			}

			$events = get_posts( apply_filters( 'sc_calendar_query_args', $args ) );

			$cal_event = '';

			$categories = array();

			$shown_events = array();

			$recurring_timestamp = mktime( 0, 0, 0, $month, $list_day, $year );

			foreach ( $events as $event ) :

				$id = $event->ID;

				$shown_events[] = $id;
				// Layout conditional 4
				if($size == 'responsive') {
					$link = "\t\t\t\t\t\t<div class=\"event\" itemscope itemtype=\"http://schema.org/Event\"><meta itemprop=\"startDate\" content=\"";
					$epoch = get_post_meta($id, 'sc_event_date_time');
					$link .= date('Y-m-d', $epoch[0]) . '"><div class="event-desc"><a itemprop="url" href="' . get_permalink($id) . '"><span itemprop="name">' . esc_html(get_the_title($id)) . "</span></a></div></div>\n";
				} elseif ( $size == 'small' ) {
					$link = '<a href="'. get_permalink( $id ) .'" title="' . esc_html( get_the_title( $id ) ) . '">&bull;</a>';
				} else {
					$link = '<a href="'. get_permalink( $id ) .'">'. esc_html( get_the_title( $id ) )  .'</a><br/>';
				}

				$cal_event .= apply_filters( 'sc_event_calendar_link', $link, $id, $size );

					// collect event categories for css classes
					$event_categories = wp_get_object_terms( $id, 'sc_event_category', array( 'fields' => 'slugs' ) );
					if ( ! empty( $event_categories ) ) {
						if ( ! is_wp_error( $event_categories ) ) {
							foreach( $event_categories as $slug ) {
								$categories[] = $slug;
							}
						}
					}

					// Layout conditional 5
					if ( $size == 'responsive' ) {
						$link = "\t\t\t\t\t\t<div class=\"event\" itemscope itemtype=\"http://schema.org/Event\"><meta itemprop=\"startDate\" content=\"";
						$epoch = get_post_meta($id, 'sc_event_date_time');
						$link .= date('Y-m-d', $epoch[0]) . '"><div class="event-desc"><a itemprop="url" href="' . get_permalink($id) . '"><span itemprop="name">' . esc_html(get_the_title($id)) . "</span></a></div></div>\n";
					} elseif ( $size == 'small' ) {
						$link = '<a href="'. get_permalink( $id ) .'" title="' . esc_html( get_the_title( $id ) ) . '">&bull;</a>';
					} else {
						$link = '<a href="'. get_permalink( $id ) .'">'. esc_html( get_the_title( $id ) )  .'</a><br/>';
					}

			endforeach;

			$recurring_timestamp = mktime( 0, 0, 0, $month, $list_day, $year );

			$cal_event .= sc_show_recurring_events( $recurring_timestamp, $size, $category, $shown_events );

			$categories = array_unique( $categories );
			$category_string = ' ' . implode( ' ', $categories );

			// Layout conditional 6
			if($size == 'responsive') {
				$cal_day = "\t\t\t\t\t<li class=\"calendar-day " . $today . $category_string . "\">\n";
			} else {
				$cal_day = '<td class="calendar-day '. $today . $category_string .'" valign="top"><div class="sc_day_div">';
			}

			// add in the day numbering
			// Layout conditional 7
			if($size == 'responsive') {
				$cal_day .= "\t\t\t\t\t\t<div class=\"day-number day-" . $list_day . '">'.$list_day."</div>\n";
			} else {
				$cal_day .= '<div class="day-number day-' . $list_day . '">'.$list_day.'</div>';
			}

			$calendar .= $cal_day;

			$calendar .= $cal_event ? $cal_event : '';

			// Layout conditional 8
			if($size == 'responsive') {
				$calendar .= "\t\t\t\t\t</li>\n";
			} else {
				$calendar .= '</div></td>';
			}

			if ( $running_day == 6 ):

				if ( ( $list_day < $days_in_month ) ) {

					// Layout conditional 9
					if($size == 'responsive') {
						$calendar .= "\t\t\t\t</ul>\n";
						$calendar .= "\t\t\t\t<ul class=\"calendar-row\">\n";
					} else {
						$calendar.= '</tr>';
						$calendar .= '<tr class="calendar-row">';
					}
					$running_day = -1;
					$days_in_this_week = 0;
				}

			endif;

			$days_in_this_week++; $running_day++; $day_counter++;

		endfor;

		//finish the rest of the days in the week
		if ( $days_in_this_week < 8 ):
			for ( $x = 1; $x <= ( 8 - $days_in_this_week ); $x++ ):
				// Layout conditional 10
				if($size == 'responsive') {
					$calendar .= "\t\t\t\t\t<li class=\"calendar-day calendar-day-np\"></li>\n";
				} else {
					$calendar .= '<td class="calendar-day-np" valign="top"><div class="sc_day_div"></div></td>';
				}
			endfor;
		endif;

	wp_reset_postdata();

	// Layout conditonal 11
	if($size == 'responsive') {
		// close list
		$calendar .= "\t\t\t\t</ul>\n";
		// close div
		$calendar .= '</div>';
	} else {
		//final row
		$calendar.= '</tr>';
		//end the table
		$calendar.= '</table>';
	}

	//all done, return the completed calendar
	return $calendar;
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

function sc_month_num_to_name($n) {
    $timestamp = mktime( 0, 0, 0, $n, 1, 2005 );
    return date_i18n( 'M', $timestamp);
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

	if( !is_object( $post ) )
		return false;

	if ( strpos($post->post_content, '[sc_events_calendar') !== false )
		return true;
	return false;
}


/**
 * Retrieves the calendar date for an event
 *
 * @access      public
 * @since       1.0
 * @param		$event_id int The ID number of the event
 * @param		$formatted bool Whether to return a time stamp or the nicely formatted date
 * @return      string
*/
function sc_get_event_date( $event_id, $formatted = true ) {
	$date = get_post_meta( $event_id, 'sc_event_date_time', true );
	if( $formatted )
		$date = date_i18n( get_option('date_format' ), $date );

	return $date;
}


/**
 * Retrieves the time for an event
 *
 * @access      public
 * @since       1.0
 * @param		$event_id int The ID number of the event
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
 * @param		$event_id int The ID number of the event
 * @return      string
*/

function sc_get_event_start_time($event_id) {
	$start 	= get_post_meta($event_id, 'sc_event_date', true);

	$day 	= date( 'd', $start );
	$month 	= date( 'm', $start );
	$year 	= date( 'Y', $start );

	$hour 	= absint( get_post_meta( $event_id, 'sc_event_time_hour', true ) );
	$minute = absint( get_post_meta( $event_id, 'sc_event_time_minute', true ) );
	$am_pm 	= get_post_meta( $event_id, 'sc_event_time_am_pm', true );

	$hour 	= $hour ? $hour : null;
	$minute = $minute ? $minute : null;
	$am_pm 	= $am_pm ? $am_pm : null;

	if( $am_pm == 'pm' && $hour < 12 )
		$hour += 12;
	elseif( $am_pm == 'am' && $hour >= 12 )
		$hour -= 12;

	$time = date_i18n( get_option( 'time_format' ), mktime( $hour, $minute, 0, $month, $day, $year ) );

	return apply_filters( 'sc_event_start_time', $time, $hour, $minute, $am_pm );
}


/**
 * Retrieves the end time for an event
 *
 * @access      public
 * @since       1.0
 * @param		$event_id int The ID number of the event
 * @return      string
*/

function sc_get_event_end_time( $event_id ) {
	$start 	= get_post_meta( $event_id, 'sc_event_date', true );

	$day 	= date( 'd', $start );
	$month 	= date( 'm', $start );
	$year 	= date( 'Y', $start );

	$hour 	= get_post_meta( $event_id, 'sc_event_end_time_hour', true );
	$minute = get_post_meta( $event_id, 'sc_event_end_time_minute', true );
	$am_pm 	= get_post_meta( $event_id, 'sc_event_end_time_am_pm', true );

	$hour 	= $hour 	? $hour 	: null;
	$minute = $minute 	? $minute 	: null;
	$am_pm 	= $am_pm 	? $am_pm 	: null;

	if( $am_pm == 'pm' && $hour < 12 )
		$hour += 12;
	elseif( $am_pm == 'am' && $hour >= 12 )
		$hour -= 12;

	$time = date_i18n( get_option( 'time_format' ), mktime( $hour, $minute, 0, $month, $day, $year ) );

	return apply_filters( 'sc_event_end_time', $time, $hour, $minute );
}