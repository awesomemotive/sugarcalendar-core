<?php

/**
 * Sugar Calendar Legacy Theme AJAX.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Provide the calendar via AJAX when Next/Previous buttons are pressed, or
 * year/month/category is updated.
 *
 * @since 1.0.0
 */
function sc_load_calendar_via_ajax() {

	// Bail if no nonce
	if ( ! isset( $_POST[ 'sc_nonce' ] ) ) {
		return;
	}

	// Bail if nonce verification fails
	if ( ! wp_verify_nonce( $_POST[ 'sc_nonce' ], 'sc_calendar_nonce' ) ) {
		return;
	}

	// Get calendar attributes
	$category = isset( $_REQUEST[ 'sc_event_category' ] )
		? sanitize_text_field( $_REQUEST[ 'sc_event_category' ] )
		: '';
	$type     = isset( $_POST[ 'type' ] )
		? sanitize_text_field( $_POST[ 'type' ] )
		: '';
	$size     = isset( $_POST[ 'sc_calendar_size' ] ) && ( 'small' === sanitize_key( $_POST[ 'sc_calendar_size' ] ) )
		? 'small'
		: 'large';

	// Output the calendar
	echo sc_get_events_calendar( $size, $category, $type );

	// Done!
	die();
}
