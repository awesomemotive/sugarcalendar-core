<?php
/**
 * Sugar Calendar Post Relationships
 *
 * @package Plugins/Site/Events/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Transition event statuses when a post of the primary type also updates.
 *
 * @since 2.0.0
 */
function sugar_calendar_transition_post_status( $new_status = '', $old_status = '', $post = false ) {

	// Get the post type being transitioned
	$post_type = get_post_type( $post );

	// Bail if not our post type
	if ( sugar_calendar_get_event_post_type_id() !== $post_type ) {
		return;
	}

	// Get the event
	$event = sugar_calendar_get_event_by_object( $post->ID, 'post' );

	// Bail if no event
	if ( empty( $event ) || ! $event->exists() ) {
		return;
	}

	// Update the event status to match
	sugar_calendar_update_event( $event->id, array(
		'status' => $post->post_status
	) );
}

/**
 * Filter events query variables and maybe add the taxonomy and term.
 *
 * This filter is necessary to ensure events queries are cached using the
 * taxonomy and term they are queried by.
 *
 * @since 2.0.0
 *
 * @param object|Query $query
 */
function sugar_calendar_pre_get_events_by_taxonomy( $query ) {

	// Get the taxonomy ID
	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Sanitize the requested term
	$term = ! empty( $_REQUEST[ $tax ] )
		? sanitize_text_field( $_REQUEST[ $tax ] )
		: false;

	// Bail if sanitized term is empty
	if ( empty( $term ) ) {
		return;
	}

	// Add the taxonomy & term to query vars
	$query->set_query_var( $tax, $term );
}

/**
 * Filter events queries and maybe JOIN by taxonomy term relationships
 *
 * This is hard-coded (for now) to provide back-compat with the built-in
 * post-type & taxonomy. It can be expanded to support any/all in future versions.
 *
 * @since 2.0.0
 *
 * @param array $clauses
 *
 * @return array
 */
function sugar_calendar_join_by_taxonomy_term( $clauses = array() ) {

	// Get the taxonomy ID
	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Sanitize the requested term
	$term = ! empty( $_REQUEST[ $tax ] )
		? sanitize_text_field( $_REQUEST[ $tax ] )
		: false;

	// Bail if sanitized term is empty
	if ( empty( $term ) ) {
		return $clauses;
	}

	// No calendar (NOT EXISTS)
	if ( in_array( $term, array( '-1', '__sc_none__' ), true ) ) {
		$args = array(
			'taxonomy' => $tax,
			'operator' => 'NOT EXISTS'
		);

	// Specific calendar
	} else {
		$args = array(
			'taxonomy' => $tax,
			'terms'    => $term,
			'field'    => 'slug'
		);
	}

	// Get a taxonomy query object
	$tax_query = new WP_Tax_Query( array( $args ) );

	// Get clauses
	$sql_clauses   = $tax_query->get_sql( 'sc_e', 'object_id' );
	$join_clauses  = array( $clauses['join'], $sql_clauses['join'] );
	$where_clauses = array( $clauses['where'], $sql_clauses['where'] );

	// Join clauses
	$clauses['join']  = implode( '', array_filter( $join_clauses  ) );
	$clauses['where'] = implode( '', array_filter( $where_clauses ) );

	// Return new clauses
	return $clauses;
}

/**
 * Delete events for a given post ID.
 *
 * This is hooked to the `deleted_posts` action to ensure that all events
 * related to a post ID are deleted when the post is also deleted.
 *
 * @since 2.0.0
 *
 * @param int $post_id
 * @return array
 */
function sugar_calendar_delete_post_events( $post_id = 0 ) {
	return sugar_calendar_delete_events( array(
		'object_id'   => $post_id,
		'object_type' => 'post'
	) );
}
