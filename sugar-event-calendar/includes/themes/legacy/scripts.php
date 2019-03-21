<?php

/**
 * Sugar Calendar Legacy Theme Scripts.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Front End Scripts
 *
 * Loads all of the scripts for front end.
 *
 * @access      private
 * @since       1.0.0
 * @return      void
 */
function sc_load_front_end_scripts() {
	global $post;

	// Get raw post content, if exists
	$content = ! empty( $post->post_content )
		? $post->post_content
		: '';

	if ( sc_is_calendar_page() || sc_using_widget() || is_singular( 'sc_event' ) || has_shortcode( $content, 'sc_events_list' ) ) {
		sc_enqueue_scripts();
		sc_enqueue_styles();
	}
}

/**
 * Enqueue scripts callback
 *
 * @since 1.0.0
 */
function sc_enqueue_scripts() {
	wp_enqueue_script( 'sc-ajax', SC_PLUGIN_URL . 'includes/themes/legacy/js/sc-ajax.js', array( 'jquery' ), SC_PLUGIN_VERSION, false );
	wp_localize_script( 'sc-ajax', 'sc_vars', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	) );
}

/**
 * Enqueue styles callback
 *
 * @since 1.0.0
 */
function sc_enqueue_styles() {
	wp_enqueue_style( 'sc-events', SC_PLUGIN_URL . 'includes/themes/legacy/css/sc-events.css', array(), SC_PLUGIN_VERSION );
}
