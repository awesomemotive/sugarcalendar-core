<?php

/**
 * Sugar Calendar Event Query Filters
 *
 * @package Plugins/Site/Events/PostTypes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Update WP_Query to include meta query for sc_event
 *
 * @param WP_Query $query
 *
 * @return mixed
 */
function sc_modify_events_archive( $query ) {

	$post_type = sugar_calendar_get_event_post_type_id();

	if ( ( is_post_type_archive( $post_type ) || is_tax( 'sc_event_category' ) ) && $query->is_main_query() && ! is_admin() ) {

		if ( isset( $query->query_vars[ 'post_type' ] ) && $query->query_vars[ 'post_type' ] == 'nav_menu_item' ) {
			return $query;
		}

		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order',   'DESC' );

		if ( isset( $_GET[ 'event-display' ] ) ) {

			$mode = urldecode( $_GET[ 'event-display' ] );
			$time = current_time( 'timestamp' );

			switch ( $mode ) {

				case 'past':
					$query->set( 'meta_query', array(
						array(
							'key'     => 'sc_event_date_time',
							'value'   => $time,
							'compare' => '<',
						),
					) );
					break;

				case 'upcoming':
					$query->set( 'meta_query', array(
						'relation' => 'OR',
						array(
							'key'     => 'sc_event_end_date_time',
							'value'   => $time,
							'compare' => '>=',
						),
						array(
							'key'     => 'sc_recur_until',
							'value'   => $time,
							'compare' => '>=',
						)
					) );
					break;
			}
		}

		if ( isset( $_GET[ 'event-order' ] ) ) {
			$order = urldecode( $_GET[ 'event-order' ] );
			$query->set( 'order', $order );
		}
	}
}
