<?php

/**
 * Sugar Calendar Legacy Theme Scripts.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register front-end assets.
 *
 * @since 2.0.8
 */
function sc_register_assets() {

	// Script
	wp_register_script(
		'sc-ajax',
		SC_PLUGIN_URL . 'includes/themes/legacy/js/sc-ajax.js',
		array( 'jquery' ),
		SC_PLUGIN_VERSION,
		false
	);

	// Style
	wp_register_style(
		'sc-events',
		SC_PLUGIN_URL . 'includes/themes/legacy/css/sc-events.css',
		array(),
		SC_PLUGIN_VERSION
	);
}

/**
 * Load front-end scripts.
 *
 * @since 1.0.0
 */
function sc_load_front_end_scripts() {

	// Look for conditions where scripts should be enqueued
	if (
		sc_is_calendar_page()
		||
		sc_using_widget()
		||
		is_singular( 'sc_event' )
		||
		sc_content_has_shortcodes()
	) {
		sc_enqueue_scripts();
		sc_enqueue_styles();
	}
}

/**
 * Check if a string contains shortcodes.
 *
 * @since 2.0.8
 *
 * @global object $post
 * @param string $content
 *
 * @return boolean
 */
function sc_content_has_shortcodes( $content = '' ) {

	// Fallback to current post content
	if ( empty( $content ) ) {
		global $post;

		// Get raw post content, if exists
		$content = ! empty( $post->post_content )
			? $post->post_content
			: '';

		// Bail if content is empty
		if ( empty( $content ) ) {
			return false;
		}
	}

	// Look for Sugar Calendar shortcodes
	if (
		has_shortcode( $content, 'sc_events_list' )
		||
		has_shortcode( $content, 'sc_events_calendar' )
	) {
		return true;
	}

	// No shortcodes found
	return false;
}

/**
 * Peek into block content and check it for shortcode usage.
 *
 * @since 2.0.8
 *
 * @param string $content The block content
 *
 * @return string $content The block content
 */
function sc_enqueue_if_block_has_shortcodes( $content = '' ) {

	// Bail if content is empty
	if ( empty( $content ) ) {
		return $content;
	}

	// Check the block content for a shortcode
	if ( sc_content_has_shortcodes( $content ) ) {
		sc_enqueue_scripts();
		sc_enqueue_styles();
	}

	// Return the content, unchanged
	return $content;
}

/**
 * Enqueue scripts callback
 *
 * @since 1.0.0
 */
function sc_enqueue_scripts() {

	// Front-end AJAX
	wp_enqueue_script( 'sc-ajax' );

	// Front-end AJAX URL
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

	// Front-end styling
	wp_enqueue_style( 'sc-events' );
}
