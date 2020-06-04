<?php

/**
 * Sugar Calendar Legacy Theme Hooks.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Always Add Shortcodes
add_action( 'init', 'sc_add_shortcodes' );

// Always Register Front-end Scripts
add_action( 'init', 'sc_register_assets' );

// Always Add Widgets
add_action( 'widgets_init', 'sc_register_widgets' );
add_filter( 'widget_text',  'do_shortcode' );

// Legacy Posts
add_action( 'rss2_item',     'sc_add_fields_to_rss' );
add_action( 'pre_get_posts', 'sc_modify_events_archive', 999 );

// Front-end Hooks
if ( wp_using_themes() || wp_doing_ajax() ) {

	// Load front-end scripts
	add_action( 'wp_enqueue_scripts', 'sc_load_front_end_scripts' );

	// Load front-end scripts inline
	add_filter( 'render_block', 'sc_enqueue_if_block_has_shortcodes' );

	// Load calendar via AJAX
	add_action( 'wp_ajax_sc_load_calendar',        'sc_load_calendar_via_ajax' );
	add_action( 'wp_ajax_nopriv_sc_load_calendar', 'sc_load_calendar_via_ajax' );

	// Content hooks
	add_filter( 'the_content', 'sc_event_content_hooks' );
	add_filter( 'the_excerpt', 'sc_event_content_hooks' );

	// Event Details
	add_action( 'sc_before_event_content', 'sc_add_event_details'     );
	add_action( 'sc_event_details',        'sc_add_date_time_details' );
	add_action( 'sc_event_details',        'sc_add_location_details'  );
}
