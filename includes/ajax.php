<?php

function sc_load_calendar_via_ajax() {
	if(isset($_POST['sc_nonce']) && wp_verify_nonce($_POST['sc_nonce'], 'sc_calendar_nonce')) {

		$current_month 	= isset( $_POST['sc_current_month'] ) ? absint( $_POST['sc_current_month'] ) : 0;
		$year 			= absint( $_POST['sc_year'] );

		if( $current_month == 12 && $_POST['action_2'] == 'next_month' )
			$year++;
		elseif( $current_month == 1 && $_POST['action_2'] == 'prev_month' )
			$year--;

		die( sc_get_events_calendar( $year ) );
	}
}
add_action('wp_ajax_sc_load_calendar', 'sc_load_calendar_via_ajax');
add_action('wp_ajax_nopriv_sc_load_calendar', 'sc_load_calendar_via_ajax');

