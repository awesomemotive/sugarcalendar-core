jQuery( document ).ready( function( $ ) {
	'use strict';

	// Get the vars necessary to transform the "Add New" form into a modal
	var add_new  = $( 'h2.sc-nav-tab-wrapper .page-title-action' ),
		wrapper  = $( '#col-left' ),
		form     = wrapper.find( 'form' ),
		submit   = form.find( '#submit' ),
		tag_name = form.find( '#tag-name' ),

		// Search
		sinput   = $( '#tag-search-input' ),
		ssubmit  = $( '#search-submit' ),

		// URL
		hash     = window.location.hash,

		// Dialog
		vwidth   = $( window ).width(),
		vheight  = $( window ).height(),
		dwidth   = ( vwidth <= 782 ) ? vwidth  - 40 : 600,
		dheight  = ( vwidth <= 782 ) ? vheight - 40 : 500,
		title    = wrapper.find( 'h2' ).text(),
		dialog   = wrapper.dialog( {
			modal     : true,
			autoOpen  : false,
			resizable : false,
			draggable : false,
			title     : title,
			width     : dwidth,
			height    : dheight,
			create: function() {
				$( this )
					.parent()
						.css( 'maxWidth',  '90vw' )
						.css( 'maxHeight', '70vh' );
			},
		} );

	// Set search placeholder text, since the button is now hidden
	sinput.attr( 'placeholder', ssubmit.attr( 'value' ) );

	/**
	 * Maybe open the dialog
	 *
	 * Done here, like this, to avoid rendering issues with autoOpen in Safari.
	 */
	if ( '#tag-name' === hash ) {
		setTimeout( function() {
			dialog.dialog( 'open' );
		}, 100 );
	}

	/**
	 * Show on "Add New" click
	 *
	 * @since 2.1.0
	 * @param {object} e
	 */
	add_new.on( 'click', function( e ) {
		e.preventDefault();

		// Open the dialog
		dialog.dialog( 'open' );

		// Set focus on the first input
		setTimeout( function() {
			tag_name.focus();
		} );
	} );

	/**
	 * Hide on form submission
	 *
	 * @since 2.1.0
	 */
	submit.on( 'click', function() {
		setTimeout( function() {

			// Look for invalid fields
			var invalid = form.find( '.form-field.form-invalid' );

			// Hide if no invalid fields
			if ( 0 === invalid.length ) {
				dialog.dialog( 'close' );
			}
		}, 100 );
	} );
} );
