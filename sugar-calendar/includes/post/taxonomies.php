<?php

/**
 * Event Taxonomies
 *
 * @package Plugins/Site/Events/Taxonomies
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Common\Editor as Editor;

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

	// Get the taxonomy ID
	$tax = sugar_calendar_get_calendar_taxonomy_id();

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
		'query_var'             => $tax,
		'show_tagcloud'         => true,
		'hierarchical'          => true,
		'show_in_nav_menus'     => false,
		'public'                => true,
		'show_ui'               => true,
		'colors'                => true,
		'timezones'             => true,
		'source'                => 'sugar-calendar',
		'meta_box_cb'           => 'Sugar_Calendar\\Admin\\Editor\\Meta\\calendars'
	);

	// Default Calendar
	$default = sugar_calendar_get_default_calendar();

	// Default exists
	if ( ! empty( $default ) ) {

		// Get term (does not use taxonomy, because it's not registered yet!)
		$term = get_term( $default );

		// Term exists
		if ( ! empty( $term->name ) ) {
			$args['default_term'] = array( 'name' => $term->name );
		}
	}

	// Get the editor type
	$editor = Editor\current();

	// Maybe supports the block editor
	if ( 'block' === $editor ) {
		$args['show_in_rest'] = true;
	}

	// Register
	register_taxonomy(
		$tax,
		sugar_calendar_get_event_post_type_id(),
		$args
	);
}

/**
 * Relate taxonomy to post types.
 *
 * @since 2.0.6
 */
function sugar_calendar_relate_taxonomy_to_post_types() {

	// Get the taxonomy
	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Get the types
	$types = sugar_calendar_allowed_post_types();

	// Bail if no types
	if ( empty( $types ) ) {
		return;
	}

	// Loop through types and relate them
	foreach ( $types as $type ) {
		register_taxonomy_for_object_type( $tax, $type );
	}
}

/**
 * Get all taxonomies for all supported Event relationships.
 *
 * @since 2.0.19
 *
 * @param string|array $types
 * @return array
 */
function sugar_calendar_get_object_taxonomies( $types = '', $output = 'names' ) {

	// Default return value
	$retval = array();

	// Fallback types to an array of post types that support Events
	if ( empty( $types ) ) {
		$types = sugar_calendar_allowed_post_types();
	}

	// Bail if no post types allow Events (weird!)
	if ( empty( $types ) ) {
		return $retval;
	}

	// Default output to names
	if ( ! in_array( $output, array( 'objects', 'names' ), true ) ) {
		$output = 'names';
	}

	// Cast strings to array
	if ( is_string( $types ) ) {
		$types = (array) $types;
	}

	// Loop through types
	foreach ( $types as $type ) {

		// Get taxonomies for post type
		$taxonomies = get_object_taxonomies( $type, $output );

		// Skip if empty
		if ( empty( $taxonomies ) ) {
			continue;
		}

		// Merge
		$retval = array_merge( $retval, $taxonomies );
	}

	// Filter & return
	return (array) apply_filters( 'sugar_calendar_get_taxonomies', $retval );
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

/**
 * Return the name of the option used to store the default Event Calendar.
 *
 * @since 2.1.9
 *
 * @return string
 */
function sugar_calendar_get_default_calendar_option_name() {
	return 'sc_default_calendar';
}

/**
 * Return the value of the default Event Calendar.
 *
 * @since 2.1.9
 *
 * @return string
 */
function sugar_calendar_get_default_calendar() {

	// Get the option
	$name   = sugar_calendar_get_default_calendar_option_name();
	$retval = get_option( $name );

	// Return
	return $retval;
}

/**
 * Filter the default WordPress option names, and use our custom one instead.
 *
 * @since 2.1.9
 *
 * @param string $value
 * @param string $option

 * @return string
 */
function sugar_calendar_pre_get_default_calendar_option( $value = false, $option = '', $default = '' ) {

	// Bail if not filtering the correct option
	if ( ! in_array( $option, array( 'default_sc_event_category', 'default_term_sc_event_category' ), true ) ) {
		return $value;
	}

	// Get the correct ption
	$value = sugar_calendar_get_default_calendar();

	// Return the filtered option
	return $value;
}
