<?php

/**
 * Provide the calendar via ajax when Next/Previous buttons are pressed, or
 * year/month/category is updated.
 *
 * @since 1.0.0
 */
function sc_load_calendar_via_ajax() {
	if(isset($_POST['sc_nonce']) && wp_verify_nonce($_POST['sc_nonce'], 'sc_calendar_nonce')) {
		$size     = ( isset( $_POST['sc_calendar_size'] ) && $_POST['sc_calendar_size'] == 'small' ) ? 'small' : 'large';
		$category = sanitize_text_field( $_REQUEST['sc_category'] );
		$type     = sanitize_text_field( $_POST['type'] );

		die( sc_get_events_calendar( $size, $category, $type, null ) );
	}
}
add_action('wp_ajax_sc_load_calendar', 'sc_load_calendar_via_ajax');
add_action('wp_ajax_nopriv_sc_load_calendar', 'sc_load_calendar_via_ajax');