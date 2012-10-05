<?php

function sc_events_calendar_shortcode( $atts, $content = null ) {
	
	return '<div id="sc_calendar_wrap">' . sc_get_events_calendar() . '</div>';
}
add_shortcode( 'sc_events_calendar', 'sc_events_calendar_shortcode' );