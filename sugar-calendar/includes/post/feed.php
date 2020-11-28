<?php

/**
 * Sugar Calendar Legacy Feeds.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add Event specific XML fields to the RSS Feed
 *
 * @since 1.6.0
 */
function sc_add_fields_to_rss() {
	if ( get_post_type() === sugar_calendar_get_event_post_type_id() ) {
		$post_id = get_the_ID();

		$event_day   = get_post_meta( $post_id, 'sc_event_day_of_month', true );
		$event_month = get_post_meta( $post_id, 'sc_event_month', true );
		$event_year  = get_post_meta( $post_id, 'sc_event_year', true );
		$timestamp   = get_post_meta( $post_id, 'sc_event_date_time', true );
		$event_time  = gmdate( 'Y-m-d', $timestamp );

		$event_end_day   = get_post_meta( $post_id, 'sc_event_end_day_of_month', true );
		$event_end_month = get_post_meta( $post_id, 'sc_event_end_month', true );
		$event_end_year  = get_post_meta( $post_id, 'sc_event_end_year', true );
		$timestamp       = get_post_meta( $post_id, 'sc_event_end_date_time', true );
		$event_end_time  = gmdate( 'Y-m-d', $timestamp );

?><event_day><?php echo $event_day ?></event_day>
<event_month><?php echo $event_month ?></event_month>
<event_year><?php echo $event_year ?></event_year>
<event_time><?php echo $event_time ?></event_time>
<event_end_day><?php echo $event_end_day ?></event_end_day>
<event_end_month><?php echo $event_end_month ?></event_end_month>
<event_end_year><?php echo $event_end_year ?></event_end_year>
<event_end_time><?php echo $event_end_time ?></event_end_time>
<?php
	}
}
