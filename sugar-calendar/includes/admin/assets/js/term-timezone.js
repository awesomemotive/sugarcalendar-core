jQuery( document ).ready( function() {
    'use strict';

	// Target the parent
    jQuery( 'table.wp-list-table.tags #the-list' ).on( 'click', function( e ) {
		var target = jQuery( e.target );

		// Bail if not the "Quick Edit" button
		if ( ! target.hasClass( 'editinline' ) ) {
			return;
		}

		// Get the color from the data attribute
        var tag_id   = jQuery( target ).parents( 'tr' ).attr( 'id' ),
			timezone = jQuery( 'td.timezone span', '#' + tag_id ).attr( 'data-timezone' );

		// Update the input value
        jQuery( 'select[name="term-timezone"]', '.inline-edit-row' ).val( timezone );
    } );
} );
