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
	$post    = get_post( $post );
	$event   = sugar_calendar_get_event_by_object( $post->ID, 'post' );
	$date    = $event->start;
	$all_day = $event->all_day;

	// Start an output buffer
	ob_start();

	if ( ! empty( $date ) ) {
		$date = strtotime( $date );
		$df   = get_option( 'date_format' );
		$tf   = get_option( 'time_format' );

		echo date_i18n( $df, $date );

		// Time
		if ( empty( $all_day ) ) {
			echo '<br>' . date_i18n( $tf, $date );
		}

	// No start date
	} else {
		echo '&mdash;';
	}

	// Get the output buffer
	$retval = ob_get_clean();

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_start_date_time', $retval, $post, $date );
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
	$post    = get_post( $post );
	$event   = sugar_calendar_get_event_by_object( $post->ID );
	$date    = $event->end;
	$all_day = $event->all_day;

	// Start an output buffer
	ob_start();

	if ( ! empty( $date ) ) {
		$date = strtotime( $date );
		$df   = get_option( 'date_format' );
		$tf   = get_option( 'time_format' );

		echo date_i18n( $df, $date );

		// Time
		if ( empty( $all_day ) ) {
			echo '<br>' . date_i18n( $tf, $date );
		}

	} else {
		echo '&mdash;';
	}

	// Get the output buffer
	$retval = ob_get_clean();

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_end_date_time', $retval, $post, $date );
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

	// Start an output buffer
	ob_start();

	if ( ! empty( $recurrence ) && isset( $intervals[ $recurrence ] ) ) {
		echo $intervals[ $recurrence ];

	} else {
		echo '&mdash;';
	}

	// Get the output buffer
	$retval = ob_get_clean();

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

	// Start an output buffer
	ob_start();

	// All day event
	if ( true === $all_day ) {

		// 1 day
		if ( $event->format_date( 'd', $start_date ) === $event->format_date( 'd', $end_date ) ) {
			esc_html_e( 'All Day', 'sugar-calendar' );

		// More than 1 day
		} else {
			echo sugar_calendar_human_diff_time(
				strtotime( 'midnight', $start_date ),
				strtotime( 'midnight', $end_date   )
			);
		}

	// Specific times
	} else {
		echo sugar_calendar_human_diff_time( $start_date, $end_date );
	}

	// Get the output buffer
	$retval = ob_get_clean();

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_end_date_time', $retval, $post, $all_day, $start_date, $end_date );
}
