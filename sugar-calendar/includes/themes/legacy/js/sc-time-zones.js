/* global wp, sc_vars, Intl */
jQuery( document ).ready( function( $ ) {

	// Get elements and browser time zone
	var dates   = $( '.sc-date-start time, .sc-date-end time' ),
		times   = $( '.sc_event_time time, .sc_event_start_time time, .sc_event_end_time time' ),
		tz      = Intl.DateTimeFormat().resolvedOptions().timeZone,
		convert = wp.date.dateI18n;

	// Bail if no browser time zone
	if ( ! tz.length ) {
		return;
	}

	// Update date HTML
	dates.each( function() {
		var date = $( this ),
			dt   = date.attr( 'datetime' ),
			org  = date.html(),
			html = convert( sc_vars.date_format, dt, tz );

		// Set original to data attribute, and update HTML
		date
			.attr( 'data-original', org )
			.html( html );
	} );

	// Update time HTML
	times.each( function() {
		var time = $( this ),
			dt   = time.attr( 'datetime' ),
			org  = time.html(),
			html = convert( sc_vars.time_format, dt, tz );

		// Set original to data attribute, and update HTML
		time
			.attr( 'data-original', org )
			.html( html );
	} );
} );
