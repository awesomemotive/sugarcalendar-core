/* global wp, sc_vars, Intl */
jQuery( document ).ready( function( $ ) {

	// Get elements and browser time zone
	var dates   = $( '.sc-date-start time, .sc-date-end time' ),
		times   = $( '.sc_event_start_time time, .sc_event_end_time time' ),
		tz      = Intl.DateTimeFormat().resolvedOptions().timeZone,
		convert = wp.date.dateI18n;

	// Bail if no time zone
	if ( ! tz.length ) {
		return;
	}

	// Update date HTML
	dates.each( function() {
		var date = $( this ),
			dt   = date.attr( 'datetime' ),
			html = convert( sc_vars.date_format, dt, tz );

		date.html( html );
	} );

	// Update time HTML
	times.each( function() {
		var time = $( this ),
			dt   = time.attr( 'datetime' ),
			html = convert( sc_vars.time_format, dt, tz );

		time.html( html );
	} );
} );
