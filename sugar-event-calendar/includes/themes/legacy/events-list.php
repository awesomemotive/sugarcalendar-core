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
 *
 * @return string
 */
function sc_get_events_list( $display = 'upcoming', $category = null, $number = 5, $show = array(), $order = '' ) {

	// Get today, to query before/after
	$today = date( 'Y-m-d' );

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

	// Upcoming
	if ( 'upcoming' === $display ) {
		$args = array(
			'object_type' => 'post',
			'status'      => 'publish',
			'orderby'     => 'start',
			'order'       => $order,
			'number'      => $number,
			'start_query' => array(
				'inclusive' => true,
				'after'     => $today
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
			'start_query' => array(
				'inclusive' => true,
				'before'    => $today
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

	// Maybe filter by taxonomy term
	if ( ! empty( $category ) ) {
		$args[ sugar_calendar_get_calendar_taxonomy_id() ] = $category;
	}

	// Query for events
	$events = sugar_calendar_get_events( $args );

	wp_reset_postdata();

	ob_start();

	if ( ! empty( $events ) ) {
		echo '<ul class="sc_events_list">';

		foreach ( $events as $event ) {
			$event_id = $event->object_id;
			$timestamp = strtotime( $event->start );

			echo '<li class="' . str_replace( 'hentry', '', implode( ' ', get_post_class( 'sc_event',$event_id ) ) ) . '">';

			do_action( 'sc_before_event_list_item', $event_id );

			echo '<a href="' . get_permalink( $event_id ) . '" class="sc_event_link">';
			echo '<span class="sc_event_title">' . get_the_title( $event_id ) . '</span></a>';

			if ( isset( $show['date'] ) && $show['date'] ) {
				echo '<span class="sc_event_date">' . sc_get_formatted_date( $event_id, $timestamp ) . '</span>';
			}

			if ( isset( $show['time'] ) && $show['time'] ) {
				$start_time = sc_get_event_start_time( $event_id );
				$end_time   = sc_get_event_end_time( $event_id );

				if ( $end_time !== $start_time ) {
					echo '<span class="sc_event_time">' . esc_html( $start_time ) . '&nbsp;&ndash;&nbsp;' . esc_html( $end_time ) . '</span>';
				} elseif ( $start_time ) {
					echo '<span class="sc_event_time">' . esc_html( $start_time ) . '</span>';
				} else {
					echo '<span class="sc_event_time">' . __( 'All-day event', 'sugar-calendar' ) . '</span>';
				}
			}

			if ( isset( $show['categories'] ) && $show['categories'] ) {
				$event_categories = get_the_terms( $event_id, 'sc_event_category' );

				if ( $event_categories ) {
					$categories = wp_list_pluck( $event_categories, 'name' );
					echo '<span class="sc_event_categories">' . join( $categories, ', ' ) . '</span>';
				}
			}

			if ( isset( $show['link'] ) && $show['link'] ) {
				echo '<a href="' . get_permalink( $event_id ) . '" class="sc_event_link">';
				echo __( 'Read More', 'sugar-calendar' );
				echo '</a>';
			}

			do_action( 'sc_after_event_list_item', $event_id );

			echo '<br class="clear"></li>';
		}

		echo '</ul>';
	}

	return ob_get_clean();
}
