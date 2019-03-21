jQuery( document ).ready( function() {
    'use strict';

    if ( typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function' ) {
        jQuery( '#term-color' ).wpColorPicker();
    } else {
        jQuery( '#colorpicker' ).farbtastic( '#term-color' );
    }

    jQuery( '.editinline' ).on( 'click', function() {
        var tag_id = jQuery( this ).parents( 'tr' ).attr( 'id' ),
			color  = jQuery( 'td.color i', '#' + tag_id ).attr( 'data-color' );

        jQuery( ':input[name="term-color"]', '.inline-edit-row' ).val( color );
    } );
} );
