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
} );