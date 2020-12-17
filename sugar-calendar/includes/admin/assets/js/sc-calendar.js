jQuery( document ).ready( function ( $ ) {
    'use strict';

	// When a section nav item is clicked.
	$( '.tablenav .tablenav-pages a.screen-options' ).on( 'click',
		function( j ) {

			// Prevent the default browser action when a link is clicked.
			j.preventDefault();

			// Click the settings link
			$( '#show-settings-link' ).click();
		}
	);

	// Typing in custom Date or Time boxes
	$( 'input[name="sc_date_format_custom"], input[name="sc_time_format_custom"]' ).on( 'input', function() {

		// Get elements
		var format   = $( this ),
			val      = $.trim( format.val() ),
			type     = ( 'sc_date_format_custom' === format.attr( 'name' ) )
				? 'date'
				: 'time',
			radio    = $( '#sc_' + type + '_format_custom_radio' );

		// Set the radio value
		radio.val( val );
	} );
} );