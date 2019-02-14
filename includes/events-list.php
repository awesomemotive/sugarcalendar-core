<?php

/**
 * Get a formatted list of upcoming or past events from today's date.
 *
 * @see sc_evetns_list_widget
 * @since 1.0.0
 * @param string $display
 * @param null $category
 * @param int $number
 * @param array $show
 *
 * @return string
 */
function sc_get_events_list($display = 'upcoming', $category = null, $number = 5, $show = array()) {

	$time   = current_time( 'timestamp' );

	$events = sc_get_all_events( $category );

	if ( 'upcoming' == $display ){
		foreach ( $events as $starttime => $post_id) {
			if ( $starttime < $time )
				unset( $events[$starttime] );
		}
	}

	if ( 'past' == $display ){
		foreach ( $events as $starttime => $post_id) {
			if ( $starttime > $time )
				unset( $events[$starttime] );
		}
		krsort( $events );
	}

	wp_reset_postdata();

	ob_start();

	$number_displayed = 0;
	if( $events ) {
		echo '<ul class="sc_events_list">';
		foreach( $events as $timestamp => $day_events ) {
			foreach ( $day_events as $key => $event_id ) {
				if ( $number_displayed < $number ) {
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
						if ( $end_time ) {
							echo '<span class="sc_event_time">' . $start_time . '&nbsp;&ndash;&nbsp;' . $end_time . '</span>';
						} elseif ( $start_time ) {
							echo '<span class="sc_event_time">' . $start_time . '</span>';
						} else {
							echo '<span class="sc_event_time">' . __( 'All day event', 'pippin_sc' ) . '</span>';
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
						echo __( 'Read More', 'pippin_sc' );
						echo '</a>';
					}

					do_action( 'sc_after_event_list_item',$event_id );
					echo '</li>';
					$number_displayed++;
				}
			}
		}
		echo '</ul>';
	}
	return ob_get_clean();
}
