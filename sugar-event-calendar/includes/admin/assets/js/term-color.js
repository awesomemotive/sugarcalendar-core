jQuery( document ).ready( function() {
    'use strict';

	// Pick which color picker to use
    if ( typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function' ) {
        jQuery( '#term-color' ).wpColorPicker();
    } else {
        jQuery( '#colorpicker' ).farbtastic( '#term-color' );
    }

	// Target the parent
    jQuery( 'table.wp-list-table.tags #the-list' ).on( 'click', function( e ) {
		var target = jQuery( e.target );

		// Bail if not the "Quick Edit" button
		if ( ! target.hasClass( 'editinline' ) ) {
			return;
		}

		// Get the color from the data attribute
        var tag_id = jQuery( target ).parents( 'tr' ).attr( 'id' ),
			color  = jQuery( 'td.color i', '#' + tag_id ).attr( 'data-color' );

		// Update the input value
        jQuery( ':input[name="term-color"]', '.inline-edit-row' ).val( color );
    } );
} );
