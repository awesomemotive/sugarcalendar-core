<?php
/**
 * Event Functions
 *
 * @package     Sugar Calendar
 * @subpackage  Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add an event.
 *
 * @since 2.0
 *
 * @param array $data
 * @return int
 */
function sugar_calendar_add_event( $data = array() ) {

	// An object ID and object type must be supplied for every event that is
	// inserted into the database.
	if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
		return false;
	}

	// Instantiate a query object
	$events = new \Sugar_Calendar\Event_Query();

	return $events->add_item( $data );
}

/**
 * Copy an event.
 *
 * @since 2.1.7
 *
 * @param int   $event_id event ID.
 * @param array $data     event data.
 * @return int
 */
function sugar_calendar_copy_event( $event_id = 0, $data = array() ) {
	$events = new \Sugar_Calendar\Event_Query();

	return $events->copy_item( $event_id, $data );
}

/**
 * Delete an event.
 *
 * @since 2.0
 *
 * @param int $event_id event ID.
 * @return int
 */
function sugar_calendar_delete_event( $event_id = 0 ) {
	$events = new \Sugar_Calendar\Event_Query();

	return $events->delete_item( $event_id );
}

/**
 * Delete many events using an array of arguments.
 *
 * @since 2.0.0
 *
 * @param array $args
 *
 * @return array Array of event IDs that were deleted. Empty array on failure.
 */
function sugar_calendar_delete_events( $args = array() ) {

	// Default return value
	$retval = array();

	// Parse args
	$r = wp_parse_args( $args, array(
		'object_id'         => 0,
		'object_type'       => 'post',
		'number'            => false,
		'update_item_cache' => false,
		'update_meta_cache' => false,
		'no_found_rows'     => true
	) );

	// Get events to delete
	$events = sugar_calendar_get_events( $r );

	// Bail if no events to delete
	if ( empty( $events ) ) {
		return $retval;
	}

	// Loop through events and delete them one at a time to ensure all hooks fire
	foreach ( $events as $event ) {

		// Delete event
		$deleted = sugar_calendar_delete_event( $event->id );

		// Add event ID to return value
		if ( ! empty( $deleted ) ) {
			array_push( $retval, $event->id );
		}
	}

	// Return
	return $retval;
}

/**
 * Update an event.
 *
 * @since 2.0
 *
 * @param int   $event_id event ID.
 * @param array $data    Updated event data.
 * @return bool Whether or not the event was updated.
 */
function sugar_calendar_update_event( $event_id = 0, $data = array() ) {
	$events = new \Sugar_Calendar\Event_Query();

	return $events->update_item( $event_id, $data );
}

/**
 * Get an event by ID.
 *
 * @since 2.0
 *
 * @param int $event_id event ID.
 * @return Sugar_Calendar\Event
 */
function sugar_calendar_get_event( $event_id = 0 ) {
	$events = new \Sugar_Calendar\Event_Query();

	// Return event
	return $events->get_item( $event_id );
}

/**
 * Get an event by a specific field value.
 *
 * @since 2.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return Sugar_Calendar\Event
 */
function sugar_calendar_get_event_by( $field = '', $value = '' ) {
	$events = new \Sugar_Calendar\Event_Query();

	// Return event
	return $events->get_item_by( $field, $value );
}

/**
 * Get an event by a specific object ID and Type.
 *
 * @since 2.0
 *
 * @param int    $object_id   Object ID
 * @param string $object_type Object type
 * @return Sugar_Calendar\Event
 */
function sugar_calendar_get_event_by_object( $object_id = 0, $object_type = 'post' ) {

	// Get events
	$events = sugar_calendar_get_events( array(
		'object_id'     => $object_id,
		'object_type'   => $object_type,
		'number'        => 1,
		'no_found_rows' => true
	) );

	// Bail if no events
	if ( empty( $events ) ) {
		return new Sugar_Calendar\Event();
	}

	// Return the first event
	return reset( $events );
}

/**
 * Query for events.
 *
 * @since 2.0
 *
 * @param array $args
 * @return array
 */
function sugar_calendar_get_events( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30
	) );

	// Instantiate a query object
	$events = new \Sugar_Calendar\Event_Query();

	// Return events
	return $events->query( $r );
}

/**
 * Count events.
 *
 * @since 2.0
 *
 * @param array $args Arguments.
 * @return int
 */
function sugar_calendar_count_events( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true
	) );

	// Query for count(s)
	$events = new \Sugar_Calendar\Event_Query( $r );

	// Return count(s)
	return absint( $events->found_items );
}

/**
 * Query for and return array of event counts, keyed by status.
 *
 * @since 2.0.0
 *
 * @param array $args Arguments.
 * @return array Counts keyed by status.
 */
function sugar_calendar_get_event_counts( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count'   => true,
		'groupby' => 'status'
	) );

	// Query for count
	$counts = new \Sugar_Calendar\Event_Query( $r );

	// Format & return
	return sugar_calendar_format_counts( $counts, $r['groupby'] );
}

/**
 * Format an array of count objects, using the $groupby key.
 *
 * @since 2.0.0
 *
 * @param array  $counts
 * @param string $groupby
 * @return array
 */
function sugar_calendar_format_counts( $counts = array(), $groupby = '' ) {

	// Default array
	$c = array(
		'total' => 0
	);

	// Loop through counts and shape return value
	if ( ! empty( $counts->items ) ) {

		// Loop through statuses
		foreach ( $counts->items as $count ) {

			// Status, or default of "publish" (for now)
			$status = ! empty( $count[ $groupby ] )
				? $count[ $groupby ]
				: 'publish';

			// Set the count, keyed by status
			$c[ $status ] = absint( $count['count'] );
		}

		// Total minus trash
		if ( isset( $c['trash'] ) ) {
			$t = $c;
			unset( $t['trash'] );
			$c['total'] = array_sum( $t );
			unset( $t );

		// Total of all
		} else {
			$c['total'] = array_sum( $c );
		}
	}

	// Return array of counts
	return $c;
}

/**
 * Return array of date-query arguments between a range of time.
 *
 * Includes both recurring and non-recurring events.
 *
 * @since 2.0.0
 *
 * @param string $mode
 * @param string $start
 * @param string $end
 *
 * @return array
 */
function sugar_calendar_get_date_query_args( $mode = 'month', $start = '', $end = '' ) {

	// Get recurring & non-recurring query arguments
	$recurring     = sugar_calendar_get_recurring_date_query_args( $mode, $start, $end );
	$non_recurring = sugar_calendar_get_non_recurring_date_query_args( $mode, $start, $end );

	// Get the start-of-week preference
	$start_of_week = sugar_calendar_get_user_preference( 'sc_start_of_week' );

	// Setup the return value for all query arguments
	$r = array(

		// Makes sure that any week queries use the preference
		'start_of_week' => (int) $start_of_week,

		// Recurring OR non-recurring (treat them as separate)
		'relation'      => 'OR',

		// Queries
		'recurring'     => $recurring,
		'non-recurring' => $non_recurring
	);

	// Filter, cast, and return
	return (array) apply_filters(
		'sugar_calendar_get_date_query_args',
		$r,
		$mode,
		$start,
		$end
	);
}

/**
 * Return array of non-recurring date-query arguments.
 *
 * @since 2.0.0
 *
 * @param string $mode
 * @param string $start
 * @param string $end
 *
 * @return array
 */
function sugar_calendar_get_non_recurring_date_query_args( $mode = 'month', $start = '', $end = '' ) {

	// Non-recurring date query arguments do not currently change between modes.
	// We mute it here just to be safe.
	$mode = '';

	// Setup the return value for non-recurring query arguments
	$r = array(
		'relation'      => 'AND',

		// Not recurring
		'not_recurring' => array(
			'column'  => 'recurrence',
			'compare' => 'NOT EXISTS',
			'value'   => ''
		),

		// Starts before it ends
		'start_before_end' => array(
			'column'    => 'start',
			'inclusive' => true,
			'before'    => $end
		),

		// Ends after it starts
		'end_after_start' => array(
			'column'    => 'end',
			'inclusive' => true,
			'after'     => $start
		)
	);

	/**
	 * By default Sugar Calendar includes support for non-recurring events.
	 *
	 * This filter is also how Advanced Recurring works, so use extreme caution
	 * when modifying it in the future.
	 *
	 * @since 2.0.15
	 *
	 * @return array
	 */
	return (array) apply_filters(
		'sugar_calendar_get_non_recurring_date_query_args',
		$r,
		$mode,
		$start,
		$end
	);
}

/**
 * Return array of recurring date-query arguments.
 *
 * @since 2.0.0
 *
 * @param string $mode
 * @param string $start
 * @param string $end
 *
 * @return array
 */
function sugar_calendar_get_recurring_date_query_args( $mode = 'month', $start = '', $end = '' ) {

	// Default return value
	$r = array();

	/**
	 * By default Sugar Calendar (Lite) does not include support for recurring
	 * events. It is added via a drop-in, using the filter below.
	 *
	 * This filter is also how Advanced Recurring works, so use extreme caution
	 * when modifying it in the future.
	 *
	 * @since 2.0.15
	 *
	 * @return array
	 */
	return (array) apply_filters(
		'sugar_calendar_get_recurring_date_query_args',
		$r,
		$mode,
		$start,
		$end
	);
}
