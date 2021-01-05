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
	if ( empty( $_POST[ 'sc_nonce' ] ) ) {
		return;
	}

	// Bail if nonce verification fails
	if ( ! wp_verify_nonce( $_POST[ 'sc_nonce' ], 'sc_calendar_nonce' ) ) {
		return;
	}

	$tax = sugar_calendar_get_calendar_taxonomy_id();

	// Get calendar attributes
	$category = ! empty( $_REQUEST[ $tax ] )
		? sanitize_text_field( $_REQUEST[ $tax ] )
		: '';
	$type     = ! empty( $_POST[ 'type' ] )
		? sanitize_text_field( $_POST[ 'type' ] )
		: '';
	$size     = ! empty( $_POST[ 'sc_calendar_size' ] ) && ( 'small' === sanitize_key( $_POST[ 'sc_calendar_size' ] ) )
		? 'small'
		: 'large';
	$sow      = isset( $_POST[ 'sow' ] )
		? sanitize_text_field( $_POST[ 'sow' ] )
		: null;

	// Output the calendar
	echo sc_get_events_calendar( $size, $category, $type, null, null, $sow );

	// Done!
	die();
}
