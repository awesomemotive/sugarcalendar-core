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

	// Primary type & tax
	$post_type = sugar_calendar_get_event_post_type_id();
	$tax       = sugar_calendar_get_calendar_taxonomy_id();

	// Only proceed if an Event post type or Calendar taxonomy
	if ( is_post_type_archive( $post_type ) || is_tax( $tax ) ) {

		// Events table alias
		$alias = 'sce';

		// Get today, to query before/after
		$today = date( 'Y-m-d 00:00:00' );

		// Display argument
		$display_arg = ! empty( $_GET[ 'event-display' ] )
			? strtolower( sanitize_key( urldecode( $_GET[ 'event-display' ] ) ) )
			: '';

		// Order argument
		$order_arg = ! empty( $_GET[ 'event-order' ] )
			? strtoupper( sanitize_key( urldecode( $_GET[ 'event-order' ] ) ) )
			: 'DESC';

		// Default order, based on display arg
		$default_order = ( 'past' === $display_arg )
			? 'DESC'
			: 'ASC';

		// Maybe force a default
		if ( ! in_array( $order_arg, array( 'ASC', 'DESC' ), true ) ) {
			$order_arg = $default_order;
		}

		// Custom query args
		$query->set( '_sc_alias',   $alias       );
		$query->set( '_sc_today',   $today       );
		$query->set( '_sc_object',  $post_type   );
		$query->set( '_sc_order',   $order_arg   );
		$query->set( '_sc_display', $display_arg );

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

	// Get the query args
	$alias   = $query->get( '_sc_alias' );
	$today   = $query->get( '_sc_today' );
	$display = $query->get( '_sc_display' );

	// Bail if no var
	if ( empty( $alias ) ) {
		return $where;
	}

	// Upcoming
	if ( 'upcoming' === $display ) {
		$where .= " AND {$alias}.start >= '{$today}'";

	// Past
	} elseif ( 'past' === $display ) {
		$where .= " AND {$alias}.start <= '{$today}'";
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

	// Get the query args
	$alias = $query->get( '_sc_alias' );
	$pt    = $query->get( '_sc_object' );

	// Bail if no var
	if ( empty( $alias ) ) {
		return $join;
	}

	// Add a right join
	$join .= "RIGHT JOIN {$wpdb->sc_events} AS {$alias} ON ({$wpdb->posts}.ID = {$alias}.object_id AND {$alias}.object_type = 'post' AND {$alias}.object_subtype = '{$pt}')";

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

	// Get the query args
	$alias = $query->get( '_sc_alias' );
	$order = $query->get( '_sc_order' );

	// Bail if no var
	if ( empty( $alias ) ) {
		return $orderby;
	}

	// Replace orderby
	$orderby = "{$alias}.start {$order}";

	// Return new orderby
	return $orderby;
}
