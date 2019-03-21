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
		'object_id'     => 0,
		'object_type'   => 'post',
		'number'        => -1,
		'update_cache'  => false,
		'no_found_rows' => true
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
 * @return Sugar_Calendar\events\event
 */
function sugar_calendar_get_event( $event_id = 0 ) {
	return sugar_calendar_get_event_by( 'id', $event_id );
}

/**
 * Get an event by a specific field value.
 *
 * @since 2.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 * @return Sugar_Calendar\events\event
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
 * @return Sugar_Calendar\events\event
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
 * @return type
 */
function sugar_calendar_get_date_query_args( $mode = 'month', $start = '', $end = '' ) {
	return array(
		'relation'      => 'OR',
		'recurring'     => sugar_calendar_get_recurring_date_query_args( $mode, $start, $end ),
		'non-recurring' => sugar_calendar_get_non_recurring_date_query_args( $mode, $start, $end )
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
	return array(
		'relation' => 'AND',
		array(
			'column'  => 'recurrence',
			'compare' => 'NOT EXISTS',
			'value'   => ''
		),
		array(
			'column'    => 'start',
			'inclusive' => true,
			'before'    => $end
		),
		array(
			'column'    => 'end',
			'inclusive' => true,
			'after'     => $start
		)
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

	// Default values
	$yearly  = 'm';
	$monthly = 'j';
	$weekly  = 'w';

	// Default keys
	$year_key  = 'month';
	$month_key = 'day';
	$week_key  = 'week';

	// Week mode
	if ( 'week' === $mode ) {
		$yearly    = 'w';
		$monthly   = 'w';
		$year_key  = 'week';
		$month_key = 'week';

	// Day mode
	} elseif ( 'day' === $mode ) {
		$yearly    = 'j';
		$monthly   = 'j';
		$year_key  = 'day';
		$month_key = 'day';
	}

	$view_start = strtotime( $start );
	$view_end   = strtotime( $end   );

	// Return array
	return array(

		/** All ***************************************************************/

		'relation' => 'AND',

		// Always starts before the end
		array(
			'column'    => 'start',
			'inclusive' => true,
			'before'    => $end
		),

		// Recurring Ends
		array(
			'relation' => 'OR',

			// No end (recurs forever) - exploits math in Date_Query
			// This might break someday. Works great now though!
			array(
				'column'    => 'recurrence_end',
				'inclusive' => true,
				'before'    => '0000-01-01 00:00:00'
			),

			// Ends after the beginning of this view
			array(
				'column'    => 'recurrence_end',
				'inclusive' => true,
				'after'     => $start
			)
		),

		/** Types *************************************************************/

		// Different recurrence types
		array(
			'relation' => 'OR',

			/** Yearly ********************************************************/

			// Starts or ends this month
			array(
				'relation' => 'AND',
				array(
					'column'  => 'recurrence',
					'compare' => '=',
					'value'   => 'yearly'
				),
				array(
					'relation' => 'OR',
					array(
						'relation' => 'AND',
						array(
							'column'  => 'start',
							'compare' => '<=',
							$year_key => date_i18n( $yearly, $view_end )
						),
						array(
							'column'  => 'start',
							'compare' => '=',
							'month'   => date_i18n( 'm', $view_end )
						)
					),
					array(
						'relation' => 'AND',
						array(
							'column'  => 'end',
							'compare' => '>=',
							$year_key => date_i18n( $yearly, $view_start )
						),
						array(
							'column'  => 'end',
							'compare' => '=',
							'month'   => date_i18n( 'm', $view_end )
						)
					)
				)
			),

			/** Monthly *******************************************************/

			// Starts before the end or ends after the start
			array(
				'relation' => 'AND',
				array(
					'column'  => 'recurrence',
					'compare' => '=',
					'value'   => 'monthly'
				),
				array(
					'relation' => 'OR',
					array(
						'column'   => 'start',
						'compare'  => '<=',
						$month_key => date_i18n( $monthly, $view_end )
					),
					array(
						'column'   => 'end',
						'compare'  => '>=',
						$month_key => date_i18n( $monthly, $view_start )
					)
				)
			),

			/** Weekly ********************************************************/

			// Starts or ends this month
			array(
				'relation' => 'AND',
				array(
					'column'  => 'recurrence',
					'compare' => '=',
					'value'   => 'weekly'
				),
				array(
					'relation' => 'OR',
					array(
						'column'  => 'start',
						'compare' => '<=',
						$week_key => date_i18n( $weekly, $view_end )
					),
					array(
						'column'  => 'end',
						'compare' => '>=',
						$week_key => date_i18n( $weekly, $view_start )
					)
				)
			),

			/** Daily *********************************************************/

			// Daily event
			array(
				'relation' => 'AND',
				array(
					'column'  => 'recurrence',
					'compare' => '=',
					'value'   => 'daily'
				)
			)
		)
	);
}
