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
 * Given a single Event ID, get an array of Event objects in a recurring sequence.
 *
 * @since 2.2.0
 *
 * @param int          $event_id
 * @param DateTime     $after
 * @param DateTime     $before
 * @param DateTimeZone $timezone
 * @param string       $start_of_week
 *
 * @return array
 */
function sugar_calendar_get_event_sequence( $event_id = 0, $after = null, $before = null, $timezone = '', $start_of_week = '' ) {

	// Get the event
	$event = sugar_calendar_get_event( $event_id );

	// Bail if Event was not found
	if ( empty( $event ) ) {
		return array();
	}

	// Event is not recurring, just return the single Event
	if ( empty( $event->recurrence ) ) {
		$retval = array( $event );

	// Event is recurring, so compute the sequence
	} else {

		// Default return value
		$retval = array();

		// Get time zone name
		$tzid = is_object( $timezone )
			? $timezone->getName()
			: $timezone;

		// Default arguments
		$args = array(

			// Identification
			'id'         => $event->id,

			// Range
			'after'      => $after->format( 'Ymd\THis' ),
			'before'     => $before->format( 'Ymd\THis' ),

			// Environment
			'tzid'       => $tzid,
			'wkst'       => $start_of_week,

			// Event
			'dtstart'    => $event->start_dto->format( 'Ymd\THis' ),
			'dtend'      => $event->end_dto->format( 'Ymd\THis' ),
			'freq'       => $event->recurrence,
			'interval'   => $event->recurrence_interval
		);

		// Ending by date
		if ( ! empty( $event->recurrence_end_dto ) ) {
			$args['until'] = $event->recurrence_end_dto->format( 'Ymd\THis\Z' );

		// Ending by count
		} elseif ( ! empty( $event->recurrence_count ) ) {
			$args['count'] = $event->recurrence_count;
		}

		// Filter arguments
		$args = apply_filters( 'sugar_calendar_get_event_sequence_args', $args, $event, $after, $before, $timezone, $start_of_week );

		// Initialize a Sequence object
		$recur = new \Sugar_Calendar\Utilities\iCalendar\Recur\Sequence( $args );

		// Initialize counter
		$n = 1;

		// Clone Events into sequences
		while ( $date = $recur->next() ) {

			// Clone the original Event to a new object
			$new_event = ( $n > 1 )
				? clone $event
				: $event;

			// Set the start & end
			$new_event->start = $date['dtstart'];
			$new_event->end   = $date['dtend'];

			// Set the recurrence ID
			$new_event->reccurence_id = $date['recurrence-id'];

			// Set the sequence
			$new_event->sequence = ! empty( $date['sequence'] )
				? abs( $date['sequence'] )
				: 0;

			// Reset all DateTime objects
			$new_event->set_datetime_objects();

			// Add new event to return values
			$retval[] = $new_event;

			// Bump the counter
			$n++;

			// Avoid infinite loops (500 is arbitrary; maybe change later)
			if ( $n >= 500 ) {
				break;
			}
		}
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_sequence', $retval, $event_id, $after, $before, $timezone );
}

/**
 * Given an array of Events, get a combined array of recurring sequences.
 *
 * @since 2.2.0
 *
 * @param int          $events
 * @param DateTime     $after
 * @param DateTime     $before
 * @param DateTimeZone $timezone
 * @param string       $start_of_week
 *
 * @return array
 */
function sugar_calendar_get_event_sequences( $events = array(), $after = null, $before = null, $timezone = '', $start_of_week = '' ) {

	// Default return value
	$retval = array();

	// Bail if boundaries are missing
	if ( empty( $events ) || empty( $after ) || empty( $before ) ) {
		return array();
	}

	// Get all sequences
	foreach ( $events as $event ) {

		// Get sequences
		$sequences = sugar_calendar_get_event_sequence(
			$event->id,
			$after,
			$before,
			$timezone,
			$start_of_week
		);

		// Merge arrays
		$retval = array_merge( $retval, $sequences );
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_get_event_sequences', $retval, $events, $after, $before, $timezone, $start_of_week );
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

/**
 * Sequence events for use in a list.
 *
 * By default, this function will:
 * -- Return unique results based on user preferences like "Start of Week" and
 *    "Timezone".
 * -- Looks forward & backward a maximum of 100 years (in each direction) for
 *    recurring event sequences.
 * -- Automatically expires every 15 minutes (900 seconds) to better support
 *    refreshing for "in progress" lists of events.
 *
 * @todo convert innards into a class for easier overrides
 *
 * @since 2.3.0
 *
 * @param array $args       Optional. Display arguments.
 * @param array $query_args Optional. Query arguments.
 *
 * @return array
 */
function sugar_calendar_get_events_list( $args = array(), $query_args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'cache_prefix'  => 'sc_',
		'order'         => 'DESC',
		'number'        => 5,
		'display'       => 'upcoming',
		'spread'        => 'P100Y',    // 100 years
		'expires'       => 'PT900S',   //  15 minutes
		'start_of_week' => sugar_calendar_get_user_preference( 'sc_start_of_week' ),
		'timezone'      => sugar_calendar_get_user_preference( 'sc_timezone' ),
	) );

	// Get the current request time
	$now = sugar_calendar_get_request_time();

	// Add rounded value to args, for cache key below
	$r['round'] = sugar_calendar_round_time( $now, $r['expires'], 'UTC', $r['timezone'] );

	// Get supported post type IDs
	$supported = get_post_types_by_support( array( 'events' ) );

	// Parse query args
	$qr = wp_parse_args( $query_args, array(
		'object_type'        => 'post',
		'object_subtype__in' => $supported,
		'status'             => 'publish',
		'orderby'            => 'start',
		'number'             => false,
		'no_found_rows'      => true
	) );

	// Turn all parsed args into a unique string used as the cache key
	$key = sanitize_key( $r['cache_prefix'] ) . md5( serialize( $r ) . serialize( $qr ) );

	// Get all events list caches
	$cache = get_option( 'sc_events_list_cache', array() );

	// Default return value
	$retval = false;

	// Check cache for key (false is valid)
	if ( isset( $cache[ $key ] ) ) {
		$retval = $cache[ $key ];

	// Not cached, so reprocess
	} else {

		// Query for all events
		$events = sugar_calendar_get_events( $qr );

		// Skip if no events
		if ( ! empty( $events ) ) {

			// Types
			$past        = ( false !== strpos( $r['display'], 'past' ) );
			$upcoming    = ( false !== strpos( $r['display'], 'upcoming' ) );
			$in_progress = ( false !== strpos( $r['display'], 'in-progress' ) );

			// Default start/end
			$after  = clone( $r['round'] );
			$before = clone( $r['round'] );

			// Give or take 100 years
			$interval = new DateInterval( $r['spread'] );

			// Past
			if ( true === $past ) {
				$after->sub( $interval );
			}

			// Upcoming
			if ( true === $upcoming ) {
				$before->add( $interval );
			}

			// Get sequences
			$sequences = sugar_calendar_get_event_sequences(
				$events,
				$after,
				$before,
				$r['timezone'],
				$r['start_of_week']
			);

			// Skip if no sequences
			if ( ! empty( $sequences ) ) {

				// Default events list
				$list = array();

				// Loop through sequences
				foreach ( $sequences as $item ) {

					// Past
					if ( true === $past ) {
						if ( $item->end_dto <= $r['round'] ) {
							$list[] = $item;
						}
					}

					// Upcoming
					if ( true === $upcoming ) {
						if ( $item->start_dto >= $r['round'] ) {
							$list[] = $item;
						}
					}

					// In-progress
					if ( true === $in_progress ) {
						if (
							( $item->start_dto <= $r['round'] )
							&&
							( $item->end_dto >= $r['round'] )
						) {
							$list[] = $item;
						}
					}
				}

				// Skip if no list
				if ( ! empty( $list ) ) {

					// Prevent infinite loop
					$max = ! empty( $r['number'] )
						? min( (int) $r['number'], 100 )
						: 5;

					// Sort by order
					$list = wp_list_sort( $list, 'end', $r['order'] );

					// Determine start of array slice, based on order
					$start = ( 'DESC' === $r['order'] )
						? ( - $max )
						: 0;

					// Slice list to get only the number needed
					$retval = array_slice( $list, $start, $max );
				}
			}
		}

		// Set the cache value
		$cache[ $key ] = $retval;

		// Update the cache (even if retval is empty)
		update_option( 'sc_events_list_cache', $cache );
	}

	// Filter & return
	return (array) apply_filters( 'sugar_calendar_get_events_list', $retval, $args, $query_args, $r, $qr );
}

/**
 * Clean the events list cache.
 *
 * @since 2.3.0
 *
 * @param int $post_id Optional. Default 0.
 */
function sugar_calendar_clean_events_list_cache( $post_id = 0 ) {

	// Bail if no post ID
	if ( empty( $post_id ) ) {
		return;
	}

	// Get the post-type of the post being saved
	$post_type = get_post_type( $post_id );

	// Bail if post-type does not support events
	if ( ! post_type_supports( $post_type, 'events' ) ) {
		return;
	}

	// Delete the entire list cache
	delete_option( 'sc_events_list_cache' );
}
