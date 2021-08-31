<?php

/**
 * Sugar Calendar Legacy Theme Event List.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get a formatted list of upcoming or past events from today's date.
 *
 * @see sc_events_list_widget
 *
 * @since 1.0.0
 * @param string $display
 * @param null $category
 * @param int $number
 * @param array $show
 * @param string $order
 *
 * @return string
 */
function sc_get_events_list( $display = 'upcoming', $category = null, $number = 5, $show = array(), $order = '' ) {

	// Get today, to query before/after
	$now = sugar_calendar_get_request_time( 'mysql' );

	// Mutate order to uppercase if not empty
	if ( ! empty( $order ) ) {
		$order = strtoupper( $order );
	} else {
		$order = ( 'past' === $display )
			? 'DESC'
			: 'ASC';
	}

	// Maybe force a default
	if ( ! in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ) {
		$order = 'ASC';
	}

	// In-Progress
	if ( 'in-progress' === $display ) {
		$args = array(
			'object_type' => 'post',
			'status'      => 'publish',
			'orderby'     => 'start',
			'order'       => $order,
			'number'      => $number,
			'start_query'   => array(
				'inclusive' => true,
				'after'     => $now
			),
			'end_query'   => array(
				'inclusive' => true,
				'before'    => $now
			)
		);

	// Upcoming
	} elseif ( 'upcoming' === $display ) {
		$args = array(
			'object_type' => 'post',
			'status'      => 'publish',
			'orderby'     => 'start',
			'order'       => $order,
			'number'      => $number,
			'end_query'   => array(
				'inclusive' => true,
				'after'     => $now
			)
		);

	// Past
	} elseif ( 'past' === $display ) {
		$args = array(
			'object_type' => 'post',
			'status'      => 'publish',
			'orderby'     => 'start',
			'order'       => $order,
			'number'      => $number,
			'end_query'   => array(
				'inclusive' => true,
				'before'    => $now
			)
		);

	// All events
	} else {
		$args = array(
			'object_type' => 'post',
			'status'      => 'publish',
			'orderby'     => 'start',
			'order'       => $order,
			'number'      => $number
		);
	}

	// Get the IDs
	$pt  = sugar_calendar_get_event_post_type_id();
	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Maybe filter by taxonomy term
	if ( ! empty( $category ) ) {
		$args[ $tax ] = $category;
	}

	// Do not query for all found rows
	$r = array_merge( $args, array(
		'no_found_rows' => true
	) );

	// Query for events
	$events = sugar_calendar_get_events( $r );

	// Bail if no events
	if ( empty( $events ) ) {
		return '';
	}

	// Start an output buffer to store these result
	ob_start();

	do_action( 'sc_before_events_list' );

	// Start an unordered list
	echo '<ul class="sc_events_list">';

	// Loop through all events
	foreach ( $events as $event ) {

		// Get the object ID and use it for the event ID (for back compat)
		$event_id = $event->object_id;

		echo '<li class="' . str_replace( 'hentry', '', implode( ' ', get_post_class( $pt, $event_id ) ) ) . '">';

		do_action( 'sc_before_event_list_item', $event_id );

		echo '<a href="' . get_permalink( $event_id ) . '" class="sc_event_link">';
		echo '<span class="sc_event_title">' . get_the_title( $event_id ) . '</span></a>';

		if ( ! empty( $show['date'] ) ) {
			$date_tag = sugar_calendar_get_time_tag( array(
				'time'     => $event->start,
				'timezone' => $event->start_tz,
				'format'   => sc_get_date_format(),
				'dtformat' => 'Y-m-dO'
			) );

			echo '<span class="sc_event_date">' . $date_tag . '</span>';
		}

		if ( ! empty( $show['time'] ) ) {
			$start_time = sc_get_event_start_time( $event_id );
			$end_time   = sc_get_event_end_time( $event_id );
			$tf         = sc_get_time_format();

			// Output all day
			if ( $event->is_all_day() ) {
				echo '<span class="sc_event_time">' . esc_html__( 'All-day', 'sugar-calendar' ) . '</span>';

			// Output both
			} elseif ( $end_time !== $start_time ) {

				$start_tag = sugar_calendar_get_time_tag( array(
					'time'     => $event->start,
					'timezone' => $event->start_tz,
					'format'   => $tf
				) );

				$end_tag = sugar_calendar_get_time_tag( array(
					'time'     => $event->end,
					'timezone' => $event->end_tz,
					'format'   => $tf
				) );

				echo '<span class="sc_event_time">' . $start_tag . '&nbsp;&ndash;&nbsp;' . $end_tag . '</span>';

			// Output only start
			} elseif ( ! empty( $start_time ) ) {

				$start_tag = sugar_calendar_get_time_tag( array(
					'time'     => $event->start,
					'timezone' => $event->start_tz,
					'format'   => $tf
				) );

				echo '<span class="sc_event_time">' . $start_tag . '</span>';
			}
		}

		if ( ! empty( $show['categories'] ) ) {
			$event_categories = get_the_terms( $event_id, $tax );

			if ( $event_categories ) {
				$categories = wp_list_pluck( $event_categories, 'name' );
				echo '<span class="sc_event_categories">' . implode( ', ', $categories ) . '</span>';
			}
		}

		if ( ! empty( $show['link'] ) ) {
			echo '<a href="' . get_permalink( $event_id ) . '" class="sc_event_link">';
			echo esc_html__( 'Read More', 'sugar-calendar' );
			echo '</a>';
		}

		do_action( 'sc_after_event_list_item', $event_id );

		echo '<br class="clear"></li>';
	}

	// Close the list
	echo '</ul>';

	// Reset post data - we'll be looping through our own
	wp_reset_postdata();

	do_action( 'sc_after_events_list' );

	// Return the current buffer and delete it
	return ob_get_clean();
}
