<?php

/**
 * Post Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the start date & time of an event
 *
 * @since 2.0.0
 *
 * @param  mixed $post
 *
 * @return string
 */
function sugar_calendar_get_event_start_date_time( $post = false ) {

	// Get the post object & start date
	$post  = get_post( $post );
	$event = sugar_calendar_get_event_by_object( $post->ID, 'post' );

	// Default return value
	$retval = '&mdash;';

	if ( ! empty( $event->start ) ) {

		// Date
		$retval = $event->start_date( get_option( 'date_format' ) );

		// Time
		if ( empty( $event->all_day ) ) {
			$retval .= '<br>' . $event->start_date( get_option( 'time_format' ) );
		}
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_start_date_time', $retval, $post, $event->start );
}

/**
 * Return the end date & time of an event
 *
 * @since 2.0.0
 *
 * @param  mixed $post
 *
 * @return string
 */
function sugar_calendar_get_event_end_date_time( $post = false ) {

	// Get the post object & start date
	$post  = get_post( $post );
	$event = sugar_calendar_get_event_by_object( $post->ID );

	// Default return value
	$retval = '&mdash;';

	if ( ! empty( $event->end ) ) {

		// Date
		$retval = $event->end_date( get_option( 'date_format' ) );

		// Time
		if ( empty( $event->all_day ) ) {
			$retval .= '<br>' . $event->end_date( get_option( 'time_format' ) );
		}
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_end_date_time', $retval, $post, $event->end );
}

/**
 * Return the end date & time of an event
 *
 * @since 2.0.0
 *
 * @param  mixed $post
 *
 * @return string
 */
function sugar_calendar_get_event_recurrence( $post = false ) {

	// Get the post object & start date
	$post       = get_post( $post );
	$event      = sugar_calendar_get_event_by_object( $post->ID );
	$recurrence = $event->recurrence;
	$intervals  = sugar_calendar_get_recurrence_types();

	// Default return value
	$retval = '&mdash;';

	if ( ! empty( $recurrence ) && isset( $intervals[ $recurrence ] ) ) {
		$retval = $intervals[ $recurrence ];
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_recurrence', $retval, $post, $recurrence );
}

/**
 * Return the duration of an event
 *
 * @since 2.0.0
 *
 * @param  mixed $post
 *
 * @return string
 */
function sugar_calendar_get_event_duration( $post = false ) {

	// Get the post object & start date
	$post       = get_post( $post );
	$event      = sugar_calendar_get_event_by_object( $post->ID );
	$all_day    = $event->is_all_day();
	$start_date = $event->start;
	$end_date   = $event->end;

	// Default return value
	$retval = '';

	// All day event
	if ( true === $all_day ) {

		// 1 day
		if ( $event->start_date( 'd' ) === $event->end_date( 'd' ) ) {
			$retval .= esc_html__( 'All Day', 'sugar-calendar' );

		// More than 1 day
		} else {
			$retval .= sugar_calendar_human_diff_time(
				strtotime( 'midnight', $start_date ),
				strtotime( 'midnight', $end_date   )
			);
		}

	// Specific times
	} else {
		$retval .= sugar_calendar_human_diff_time( $start_date, $end_date );
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_duration', $retval, $post, $all_day, $start_date, $end_date );
}
