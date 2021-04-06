<?php
/**
 * Sugar Calendar Event Hooks
 *
 * @package Plugins/Site/Events/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Post types
add_action( 'init', 'sugar_calendar_register_post_types' );
add_action( 'init', 'sugar_calendar_register_meta_data'  );

// Taxonomies
add_action( 'init', 'sugar_calendar_register_calendar_taxonomy', 9 );
add_action( 'init', 'sugar_calendar_relate_taxonomy_to_post_types', 11 );

// Settings
add_action( 'init', 'sugar_calendar_register_settings' );

// Caps
add_filter( 'map_meta_cap', 'sugar_calendar_post_meta_caps',     10, 4 );
add_filter( 'map_meta_cap', 'sugar_calendar_category_meta_caps', 10, 4 );

// Post statuses
add_action( 'transition_post_status', 'sugar_calendar_transition_post_status', 10, 3 );
add_action( 'deleted_post',           'sugar_calendar_delete_post_events' );

// Taxonomy query
add_filter( 'sc_events_query_clauses', 'sugar_calendar_join_by_taxonomy_term',      10, 2 );
add_action( 'sc_parse_events_query',   'sugar_calendar_pre_get_events_by_taxonomy', 10    );

// Default Calendar
add_filter( 'pre_option_default_sc_event_category',      'sugar_calendar_pre_get_default_calendar_option', 10, 3 );
add_filter( 'pre_option_default_term_sc_event_category', 'sugar_calendar_pre_get_default_calendar_option', 10, 3 );
