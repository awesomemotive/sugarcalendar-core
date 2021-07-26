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
function sc_modify_events_archive( $query = false ) {

	// Bail if in admin
	if ( is_admin() ) {
		return $query;
	}

	// Bail if not the main query
	if ( ! $query->is_main_query() ) {
		return $query;
	}

	// Get post types and taxonomies
	$pts = sugar_calendar_allowed_post_types();
	$tax = sugar_calendar_get_object_taxonomies( $pts );

	// Only proceed if an Event post type or Calendar taxonomy
	if ( is_post_type_archive( $pts ) || is_tax( $tax ) ) {

		// Get the current post type
		$post_type = $query->get( 'post_type' );

		// Fallback to default post type
		if ( empty( $post_type ) ) {
			$post_type = sugar_calendar_get_event_post_type_id();
		}

		// Events table alias
		$alias = 'sce';

		// Get timezone
		$timezone = sugar_calendar_get_timezone_object( sc_get_timezone() );

		// Get time, to query before/after/in-progress
		$time = ! empty( $_GET['event-time'] )
			? absint( urldecode( $_GET['event-time'] ) )
			: gmdate( 'Y-m-d H:i:s' );

		// Get datetime
		$datetime = sugar_calendar_get_datetime_object( $time, 'UTC', $timezone );

		// Display argument
		$display_arg = ! empty( $_GET[ 'event-display' ] )
			? strtolower( sanitize_key( urldecode( $_GET[ 'event-display' ] ) ) )
			: '';

		// Order argument
		$order_arg = ! empty( $_GET[ 'event-order' ] )
			? strtoupper( sanitize_key( urldecode( $_GET[ 'event-order' ] ) ) )
			: '';

		// Default order, based on display arg
		$default_order = ( 'upcoming' === $display_arg )
			? 'ASC'
			: 'DESC';

		// Maybe force a default
		if ( ! in_array( $order_arg, array( 'ASC', 'DESC' ), true ) ) {
			$order_arg = $default_order;
		}

		// Force the post type
		$query->set( 'post_type', $post_type );

		// Custom query args
		$query->set( '_sc_datetime', $datetime    );
		$query->set( '_sc_alias',    $alias       );
		$query->set( '_sc_object',   $post_type   );
		$query->set( '_sc_order',    $order_arg   );
		$query->set( '_sc_display',  $display_arg );

		// Add query filters
		add_filter( 'posts_where',   'sc_modify_events_archive_where',   10, 2 );
		add_filter( 'posts_join',    'sc_modify_events_archive_join',    10, 2 );
		add_filter( 'posts_orderby', 'sc_modify_events_archive_orderby', 10, 2 );

		// Return the modified query
		return $query;
	}
}

/**
 * Filter Events archive WHERE query clause.
 *
 * Appends before or after today.
 *
 * @since 2.0.2
 *
 * @param string $where
 * @param object $query
 *
 * @return string
 */
function sc_modify_events_archive_where( $where = '', $query = false ) {

	// Get the alias
	$alias = $query->get( '_sc_alias' );

	// Bail if no alias
	if ( empty( $alias ) ) {
		return $where;
	}

	// Get the other args
	$datetime = $query->get( '_sc_datetime' );
	$display  = $query->get( '_sc_display' );

	// Get the time
	$time     = $datetime->format( 'Y-m-d H:i:s' );

	// In-Progress
	if ( 'in-progress' === $display ) {
		$where .= " AND ( {$alias}.start <= '{$time}' AND {$alias}.end >= '{$time}' )";

	// Upcoming (includes in-progress)
	} elseif ( 'upcoming' === $display ) {
		$where .= " AND {$alias}.end >= '{$time}'";

	// Past (excludes in-progress)
	} elseif ( 'past' === $display ) {
		$where .= " AND {$alias}.end <= '{$time}'";
	}

	// Return new where
	return $where;
}

/**
 * Filter Events archive JOIN query clause.
 *
 * Appends right join on Events table of the current type & subtype.
 *
 * @since 2.0.2
 *
 * @param string $join
 * @param object $query
 *
 * @return string
 */
function sc_modify_events_archive_join( $join = '', $query = false ) {
	global $wpdb;

	// Get the alias arg
	$alias = $query->get( '_sc_alias' );

	// Bail if no var
	if ( empty( $alias ) ) {
		return $join;
	}

	// Get the object type
	$pt    = $query->get( '_sc_object' );

	// Add a right join
	$join .= " RIGHT JOIN {$wpdb->sc_events} AS {$alias} ON ({$wpdb->posts}.ID = {$alias}.object_id AND {$alias}.object_type = 'post' AND {$alias}.object_subtype = '{$pt}')";

	// Return new join
	return $join;
}

/**
 * Filter Events archive ORDERBY query clause.
 *
 * Overrides orderby to use Events start.
 *
 * @since 2.0.2
 *
 * @param string $orderby
 * @param object $query
 *
 * @return string
 */
function sc_modify_events_archive_orderby( $orderby = '', $query = false ) {

	// Get the alias arg
	$alias = $query->get( '_sc_alias' );

	// Bail if no var
	if ( empty( $alias ) ) {
		return $orderby;
	}

	// Get the order arg
	$order   = $query->get( '_sc_order' );

	// Replace orderby
	$orderby = "{$alias}.start {$order}";

	// Return new orderby
	return $orderby;
}
