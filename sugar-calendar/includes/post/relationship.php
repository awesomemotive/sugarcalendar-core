<?php
/**
 * Sugar Calendar Post Relationships
 *
 * @package Plugins/Site/Events/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Posts *********************************************************************/

/**
 * Transition event statuses when a post of the primary type also updates.
 *
 * @since 2.0.0
 */
function sugar_calendar_transition_post_status( $new_status = '', $old_status = '', $post = false ) {

	// Get the post type being transitioned
	$post_type = get_post_type( $post );

	// Bail if no known post type
	if ( empty( $post_type ) ) {
		return;
	}

	// Bail if not supported post type
	if ( ! in_array( $post_type, sugar_calendar_allowed_post_types(), true ) ) {
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

/** Taxonomies ****************************************************************/

/**
 * Get which taxonomy term is being queried.
 *
 * First this checks the global $_REQUEST, then it checks the $query.
 *
 * @since 2.0.6
 *
 * @param string       $taxonomy
 * @param object|Query $query
 *
 * @return mixed False if no term, String(slug) if term
 */
function sugar_calendar_get_taxonomy_term_for_query( $taxonomy = '', $query = false ) {

	// Default return value
	$retval = false;

	// Sanitize the requested term
	if ( ! empty( $_REQUEST[ $taxonomy ] ) ) {
		$retval = sanitize_text_field( $_REQUEST[ $taxonomy ] );

	// Sanitize the queried term
	} elseif ( ! empty( $query->query_vars[ $taxonomy ] ) ) {
		$retval = sanitize_key( $query->query_vars[ $taxonomy ] );
	}

	// Return the term, or false
	return $retval;
}

/**
 * Get all of the requested terms being queried for.
 *
 * Eventually gets fed into WP_Tax_Query.
 *
 * @since 2.0.19
 *
 * @param object $query
 * @return array
 */
function sugar_calendar_get_requested_terms( $query = false ) {

	// Default return value
	$retval = array();

	// Get the taxonomies
	$taxos = sugar_calendar_get_object_taxonomies( '', 'names' );

	// Bail if no taxonomies
	if ( empty( $taxos ) ) {
		return $retval;
	}

	// Get the term slug
	foreach ( $taxos as $tax ) {

		// Look for requested term in query
		$term = sugar_calendar_get_taxonomy_term_for_query( $tax, $query );

		// Break out of loop if term found for query
		if ( ! empty( $term ) ) {
			array_push( $retval, array(
				'tax'  => $tax,
				'term' => $term
			) );
		}
	}

	// Filter & return
	return (array) apply_filters( 'sugar_calendar_get_requested_terms', $retval, $query );
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

	// Get the requested term
	$terms = sugar_calendar_get_requested_terms( $query );

	// Bail if terms are empty
	if ( empty( $terms ) ) {
		return;
	}

	// Loop through terms and add them to primary query vars
	foreach ( $terms as $term ) {

		// Add the taxonomy & term to query vars
		$query->set_query_var( $term['tax'], $term['term'] );
	}
}

/**
 * Filter events queries and maybe JOIN by taxonomy term relationships
 *
 * @since 2.0.0
 *
 * @param array $clauses
 * @param object|Query $query
 *
 * @return array
 */
function sugar_calendar_join_by_taxonomy_term( $clauses = array(), $query = false ) {

	// Get the requested terms
	$terms = sugar_calendar_get_requested_terms( $query );

	// Bail if terms are empty
	if ( empty( $terms ) ) {
		return $clauses;
	}

	// Default arguments
	$args = array();

	// Loop through terms
	foreach ( $terms as $term ) {

		// No term (NOT EXISTS)
		if ( in_array( $term['term'], array( '-1', '__sc_none__' ), true ) ) {
			array_push( $args, array(
				'taxonomy' => $term['tax'],
				'operator' => 'NOT EXISTS'
			) );

		// Specific term
		} elseif ( ! empty( $term['tax'] ) && ! empty( $term['term'] ) ) {
			array_push( $args, array(
				'taxonomy' => $term['tax'],
				'terms'    => $term['term'],
				'field'    => 'slug'
			) );
		}
	}

	// Bail if no arguments
	if ( empty( $args ) ) {
		return;
	}

	// Get a taxonomy query object
	$tax_query = new WP_Tax_Query( $args );

	// Get clauses
	$sql_clauses   = $tax_query->get_sql( 'sc_e', 'object_id' );
	$join_clauses  = array( $clauses['join'],  $sql_clauses['join']  );
	$where_clauses = array( $clauses['where'], $sql_clauses['where'] );

	// Join clauses
	$clauses['join']  = implode( '', array_filter( $join_clauses  ) );
	$clauses['where'] = implode( '', array_filter( $where_clauses ) );

	// Return new clauses
	return $clauses;
}
