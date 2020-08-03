/* global sc_vars */
jQuery( document ).ready( function( $ ) {

	/* Button Click */
	$( 'body' ).on( 'submit', '.sc_events_form', function() {

		document.body.style.cursor = 'wait';

		var calendar = $( this ).parents( 'div.sc_events_calendar' ).attr( 'id' ),
			data     = $( this ).serialize();

	    $.post( sc_vars.ajaxurl, data, function ( response ) {
	   		$( '#' + calendar ).parent().html( response );
			scResizeCal();

	 	} ).done( function() {
	 		document.body.style.cursor = 'default';
	 	} );

		return false;
	} );

	/* Page Resize */
	function scResizeCal() {
		var winwidth = $( window ).width();

		if ( winwidth <= 480 ) {
			if ( ! $( '.sc_events_calendar' ).hasClass( 'sc_small' ) ) {
				$( '#sc_event_select' ).hide();
			} else {
				$( '#sc_event_select' ).show();
			}
		} else {
			$( '#sc_event_select' ).show();
		}
	}

	/* Listen for resize */
	$( window ).resize( function() {
		scResizeCal();
	} );

	/* Resize on load */
	scResizeCal();
} );