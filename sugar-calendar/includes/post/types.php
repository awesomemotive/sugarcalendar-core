<?php

/**
 * Event Post Types
 *
 * @package Plugins/Site/Events/PostTypes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Common\Editor as Editor;

/**
 * Return the post type ID for the primary event post-type.
 *
 * This remains named `sc_event` for backwards compatibility reasons,
 * and is abstracted out here to avoid naming confusion.
 *
 * @since 2.0
 *
 * @return string
 */
function sugar_calendar_get_event_post_type_id() {
	return 'sc_event';
}

/**
 * Return an array of the post types that calendars are available on
 *
 * You can filter this to enable a post calendar for just about any kind of
 * post type with an interface.
 *
 * @since 2.0.0
 *
 * @return array
 */
function sugar_calendar_allowed_post_types() {
	global $_wp_post_type_features;

	// Get which post types support events
	$supports = wp_filter_object_list( $_wp_post_type_features, array( 'events' => true ) );
	$types    = array_keys( $supports );

	// Filter & return
	return apply_filters( 'sugar_calendar_allowed_post_types', $types, $supports );
}

/**
 * Register the Event post types
 *
 * If you want to manipulate these arguments, use the `register_post_type_args`
 * filter that's built into WordPress since version 4.4.
 *
 * @since 2.0.0
 */
function sugar_calendar_register_post_types() {

	// Labels
	$labels = apply_filters( 'sc_event_labels', array(
		'name'                     => esc_html_x( 'Events', 'post type general name', 'sugar-calendar' ),
		'singular_name'            => esc_html_x( 'Event', 'post type singular name', 'sugar-calendar' ),
		'menu_name'                => esc_html_x( 'Calendar', 'post type menu name', 'sugar-calendar' ),
		'submenu_name'             => esc_html_x( 'Calendar', 'post type menu name', 'sugar-calendar' ),
		'name_admin_bar'           => esc_html_x( 'Event', 'add new from admin bar', 'sugar-calendar' ),
		'add_new'                  => esc_html_x( 'Add New', 'event', 'sugar-calendar' ),
		'add_new_item'             => esc_html__( 'Add New Event', 'sugar-calendar' ),
		'edit_item'                => esc_html__( 'Edit Event', 'sugar-calendar' ),
		'new_item'                 => esc_html__( 'New Event', 'sugar-calendar' ),
		'view_item'                => esc_html__( 'View Event', 'sugar-calendar' ),
		'view_items'               => esc_html__( 'View Events', 'sugar-calendar' ),
		'search_items'             => esc_html__( 'Search Events', 'sugar-calendar' ),
		'search_items_ellipsis'    => esc_attr__( 'Search events...', 'sugar-calendar' ),
		'not_found'                => esc_html__( 'No events found.', 'sugar-calendar' ),
		'not_found_in_trash'       => esc_html__( 'No events found in trash.', 'sugar-calendar' ),
		'parent_item_colon'        => esc_html__( 'Parent Event:', 'sugar-calendar' ),
		'all_items'                => esc_html__( 'All Events', 'sugar-calendar' ),
		'archives'                 => esc_html__( 'Event Archives', 'sugar-calendar' ),
		'attributes'               => esc_html__( 'Event Attributes', 'sugar-calendar' ),
		'insert_into_item'         => esc_html__( 'Insert Into Event', 'sugar-calendar' ),
		'uploaded_to_this_item'    => esc_html__( 'Uploaded to this event', 'sugar-calendar' ),
		'featured_image'           => esc_html__( 'Featured Image', 'sugar-calendar' ),
		'set_featured_image'       => esc_html__( 'Set featured image', 'sugar-calendar' ),
		'remove_featured_image'    => esc_html__( 'Remove featured image', 'sugar-calendar' ),
		'use_featured_image'       => esc_html__( 'Use as featured image', 'sugar-calendar' ),
		'filter_items_list'        => esc_html__( 'Filter event list', 'sugar-calendar' ),
		'items_list_navigation'    => esc_html__( 'Events list navigation', 'sugar-calendar' ),
		'items_list'               => esc_html__( 'Events list', 'sugar-calendar' ),
		'item_published'           => esc_html__( 'Event created', 'sugar-calendar' ),
		'item_published_privately' => esc_html__( 'Private event created.', 'sugar-calendar' ),
		'item_reverted_to_draft'   => esc_html__( 'Event reverted to draft.', 'sugar-calendar' ),
		'item_scheduled'           => esc_html__( 'Event scheduled.', 'sugar-calendar' ),
		'item_updated'             => esc_html__( 'Event updated.', 'sugar-calendar' ),
	) );

	// Supports
	$supports = array(
		'title',
		'thumbnail',
		'revisions',
		'events',

		// Genesis (compat)
		'genesis-seo',
		'genesis-layouts',
		'genesis-simple-sidebars'
	);

	// Get the editor type
	$editor = Editor\current();

	// Maybe supports the editor
	if ( 'block' === $editor ) {
		array_push( $supports, 'editor' );
	}

	// Filter supports
	$supports = apply_filters( 'sc_event_supports', $supports );

	// Capability types
	$cap_types = apply_filters( 'sc_event_capability_type', array(
		'event',
		'events'
	) );

	// Capabilities
	$caps = array(

		// Meta caps
		'edit_post'              => 'edit_event',
		'read_post'              => 'read_event',
		'delete_post'            => 'delete_event',

		// Primitive/meta caps
		'read'                   => 'read',
		'create_posts'           => 'create_events',

		// Primitive caps (used outside of map_meta_cap)
		'edit_posts'             => 'edit_events',
		'edit_others_posts'      => 'edit_others_events',
		'publish_posts'          => 'publish_events',
		'read_private_posts'     => 'read_private_events',

		// Primitive caps (used inside of map_meta_cap)
		'delete_posts'           => 'delete_events',
		'delete_private_posts'   => 'delete_private_events',
		'delete_published_posts' => 'delete_published_events',
		'delete_others_posts'    => 'delete_others_events',
		'edit_private_posts'     => 'edit_private_events',
		'edit_published_posts'   => 'edit_published_events'
	);

	// Super easy slug override
	if ( ! defined( 'SC_EVENTS_SLUG' ) ) {
		define( 'SC_EVENTS_SLUG', 'events' );
	}

	// Rewrite
	$rewrite = apply_filters( 'sc_event_rewrite', array(
		'slug'       => SC_EVENTS_SLUG,
		'with_front' => false
	) );

	// Post type arguments
	$args = array(
		'labels'               => $labels,
		'supports'             => $supports,
		'description'          => '',
		'public'               => true,
		'hierarchical'         => false,
		'exclude_from_search'  => true,
		'publicly_queryable'   => true,
		'show_ui'              => true,
		'show_in_menu'         => false,
		'show_in_nav_menus'    => false,
		'archive_in_nav_menus' => false,
		'show_in_admin_bar'    => true,
		'menu_position'        => 2,
		'menu_icon'            => 'dashicons-calendar-alt',
		'capabilities'         => $caps,
		'capability_type'      => $cap_types,
		'register_meta_box_cb' => null,
		'taxonomies'           => array(),
		'has_archive'          => true,
		'rewrite'              => $rewrite,
		'query_var'            => true,
		'can_export'           => true,
		'delete_with_user'     => false,
		'source'               => 'sugar-calendar'
	);

	// Maybe supports the block editor
	if ( 'block' === $editor ) {
		$args['show_in_rest'] = true;
	}

	// Register the event type
	register_post_type(
		sugar_calendar_get_event_post_type_id(),
		$args
	);
}

/**
 * Return a label from a WP_Post_Type->labels object, given a post-type string
 * and the name of the label.
 *
 * Use this function to help eliminate some repetition, particularly around how
 * to fallback if no label exists for that type.
 *
 * The fallback is generally used for custom strings in specific areas of the
 * user interface where "Calendar" and "Events" are contextually accurate but
 * could be confusing when being filtered by third-party plugins.
 *
 * @since 2.2.0
 *
 * @param string $post_type The name of the post type
 * @param string $label     The name of the label
 * @param string $fallback  The string to fallback on if no label exists
 *
 * @return string|object String if single label. Object if all labels.
 */
function sugar_calendar_get_post_type_label( $post_type = '', $label = '', $fallback = '' ) {

	// Fallback to Events post type
	if ( empty( $post_type ) ) {
		$post_type = sugar_calendar_get_event_post_type_id();
	}

	// Get the object
	$pto = get_post_type_object( $post_type );

	// Return all labels
	if ( empty( $label ) ) {
		return $pto->labels;
	}

	// Return a single label, or fallback
	return ! empty( $pto->labels->{$label} )
		? $pto->labels->{$label}
		: $fallback;
}
