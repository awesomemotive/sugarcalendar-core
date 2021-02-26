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
		$retval = $event->start_date( get_option( 'sc_date_format' ) );

		// Time
		if ( empty( $event->all_day ) ) {
			$retval .= '<br>' . $event->start_date( get_option( 'sc_time_format' ) );
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
		$retval = $event->end_date( get_option( 'sc_date_format' ) );

		// Time
		if ( empty( $event->all_day ) ) {
			$retval .= '<br>' . $event->end_date( get_option( 'sc_time_format' ) );
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

/**
 * Copy a post, its taxonomy terms, and all meta data
 *
 * @since 2.1.7
 *
 * @param int $original_id
 * @param array $data
 *
 * @return int Zero on failure. ID of new post on success.
 */
function sugar_calendar_copy_post( $original_id = 0, $data = array() ) {

	// Get the post
	$post = get_post( $original_id, 'ARRAY_A' );

	// Bail if no original post
	if ( empty( $post ) ) {
		return 0;
	}

	// Set post date timestamp
	$post['post_date'] = date( 'Y-m-d H:i:s' );

	// Unset some keys, to allow them to be set to their defaults
	unset(
		$post['guid'],
		$post['comment_count'],
		$post['post_date_gmt']
	);

	// Allow custom data overrides
	$save = array_merge( $post, $data );

	// Unset the ID column, so an update does not occur
	unset( $save['ID'] );

	// Insert the post into the database
	$new_id = wp_insert_post( $save, false, false );

	// Bail if insert failed
	if ( empty( $new_id ) ) {
		return 0;
	}

	// Get taxonomies for post
	$taxonomies = get_object_taxonomies( $post['post_type'] );

	// Skip if no taxonomies
	if ( ! empty( $taxonomies ) ) {

		// Loop through all taxonomies
		foreach ( $taxonomies as $taxonomy ) {

			// Get the terms for this taxonomy
			$terms = wp_get_post_terms( $original_id, $taxonomy, array(
				'fields' => 'names'
			) );

			// Maybe set terms for this taxonomy
			if ( ! empty( $terms ) ) {
				wp_set_object_terms( $new_id, $terms, $taxonomy );
			}
		}
	}

	// Get meta data for post
	$custom_fields = get_post_custom( $original_id );

	// Skip if no post meta
	if ( ! empty( $custom_fields ) ) {

		// Loop through all post meta
		foreach ( $custom_fields as $key => $value ) {

			// Unserialize, to be serialized again when added
			$value = maybe_unserialize( $value[0] );

			// Add the meta
			add_post_meta( $new_id, $key, $value );
		}
	}

	// Return the ID of the new post ID
	return $new_id;
}
