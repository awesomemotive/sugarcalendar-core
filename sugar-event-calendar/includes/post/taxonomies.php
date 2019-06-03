<?php

/**
 * Event Taxonomies
 *
 * @package Plugins/Site/Events/Taxonomies
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the taxonomy ID for the primary calendar taxonomy.
 *
 * This remains named `sc_event_category` for backwards compatibility reasons,
 * and is abstracted out here to avoid naming confusion.
 *
 * @since 2.0
 *
 * @return string
 */
function sugar_calendar_get_calendar_taxonomy_id() {
	return 'sc_event_category';
}

/**
 * Event calendar taxonomy
 *
 * @since 2.0.0
 */
function sugar_calendar_register_calendar_taxonomy() {

	// Labels
	$labels = array(
		'name'                       => esc_html__( 'Calendars',                           'sugar-calendar' ),
		'singular_name'              => esc_html__( 'Calendar',                            'sugar-calendar' ),
		'search_items'               => esc_html__( 'Search Calendars',                    'sugar-calendar' ),
		'popular_items'              => esc_html__( 'Popular Calendars',                   'sugar-calendar' ),
		'all_items'                  => esc_html__( 'All Calendars',                       'sugar-calendar' ),
		'parent_item'                => esc_html__( 'Parent Calendar',                     'sugar-calendar' ),
		'parent_item_colon'          => esc_html__( 'Parent Calendar:',                    'sugar-calendar' ),
		'edit_item'                  => esc_html__( 'Edit Calendar',                       'sugar-calendar' ),
		'view_item'                  => esc_html__( 'View Calendar',                       'sugar-calendar' ),
		'update_item'                => esc_html__( 'Update Calendar',                     'sugar-calendar' ),
		'add_new_item'               => esc_html__( 'Add New Calendar',                    'sugar-calendar' ),
		'new_item_name'              => esc_html__( 'New Calendar Name',                   'sugar-calendar' ),
		'separate_items_with_commas' => esc_html__( 'Separate calendars with commas',      'sugar-calendar' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove calendars',             'sugar-calendar' ),
		'choose_from_most_used'      => esc_html__( 'Choose from the most used calendars', 'sugar-calendar' ),
		'no_terms'                   => esc_html__( 'No Calendars',                        'sugar-calendar' ),
		'not_found'                  => esc_html__( 'No calendars found',                  'sugar-calendar' ),
		'items_list_navigation'      => esc_html__( 'Calendars list navigation',           'sugar-calendar' ),
		'items_list'                 => esc_html__( 'Calendars list',                      'sugar-calendar' ),		
		'back_to_items'              => esc_html__( '&larr; Back to Calendars',            'sugar-calendar' )
	);

	// Rewrite rules
	$rewrite = array(
		'slug'       => 'events/calendar',
		'with_front' => false
	);

	// Capabilities
	$caps = array(
		'manage_terms' => 'manage_event_calendars',
		'edit_terms'   => 'edit_event_calendars',
		'delete_terms' => 'delete_event_calendars',
		'assign_terms' => 'assign_event_calendars'
	);

	// Arguments
	$args = array(
		'labels'                => $labels,
		'rewrite'               => $rewrite,
		'capabilities'          => $caps,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => sugar_calendar_get_calendar_taxonomy_id(),
		'show_tagcloud'         => true,
		'hierarchical'          => true,
		'show_in_nav_menus'     => false,
		'public'                => false,
		'show_ui'               => true,
		'colors'                => true,
		'source'                => 'sugar-calendar',
		'meta_box_cb'           => 'Sugar_Calendar\\Admin\\Editor\\Meta\\calendars'
	);

	// Register
	register_taxonomy(
		sugar_calendar_get_calendar_taxonomy_id(),
		sugar_calendar_allowed_post_types(),
		$args
	);
}

/**
 * Get the color of a calendar
 *
 * @since 2.0.0
 *
 * @param int $calendar_id
 * @return string
 */
function sugar_calendar_get_calendar_color( $calendar_id = 0 ) {
	$meta = get_term_meta( $calendar_id, 'color', true );

	return sugar_calendar_sanitize_hex_color( $meta );
}

/**
 * Get the color for an event
 *
 * @since 2.0.0
 *
 * @param int    $object_id   ID of the object
 * @param string $object_type The type of object
 *
 * @return string
 */
function sugar_calendar_get_event_color( $object_id = 0, $object_type = 'post' ) {

	// Posts are explicitly supported
	if ( 'post' === $object_type ) {
		$post = get_post( $object_id );

		// Bail if no post
		if ( empty( $post ) ) {
			return;
		}

		// Setup the relationship vars
		$object_id = $post->ID;
		$taxonomy  = sugar_calendar_get_calendar_taxonomy_id();

	// Nothing else supported yet
	} else {
		return;
	}

	// Look for a single calendar
	$calendar = wp_get_post_terms( $object_id, $taxonomy, array(
		'number' => 1
	) );

	// Bail if no calendar
	if ( empty( $calendar ) ) {
		return false;
	}

	// Use the first result
	$calendar = reset( $calendar );
	$color    = sugar_calendar_get_calendar_color( $calendar->term_id );

	// Return color or "none"
	return ! empty( $color )
		? $color
		: 'none';
}
