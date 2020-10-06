jQuery( document ).ready( function( $ ) {
	'use strict';

	// Get the vars necessary to transform the "Add New" form into a modal
	var body     = $( 'body' ),
		add_new  = $( 'h2.sc-nav-tab-wrapper .page-title-action' ),
		wrapper  = $( '#col-left' ),
		form     = wrapper.find( 'form' ),
		submit   = form.find( '#submit' ),
		tag_name = form.find( '#tag-name' ),
		an_class = 'sc-adding-new-tax';

	/**
	 * Hide wrapper when clicking outside of it
	 *
	 * @param {object} e
	 */
	$( document ).mousedown( function( e ) {

		// Bail if clicking inside pointer
		if ( $( e.target ).closest( '#col-left .col-wrap' ).length <= 0 ) {
			hide_wrapper();
		}
	} );

	/**
	 * Hide on "Escape" key up
	 *
	 * @since 2.1.0
	 * @param {object} e
	 */
	$( document ).keyup( function( e ) {
		if ( ( 'Escape' === e.key ) && body.hasClass( an_class ) ) {
			hide_wrapper();
		}
	} );

	/**
	 * Show on "Add New" click
	 *
	 * @since 2.1.0
	 * @param {object} e
	 */
	add_new.on( 'click', function( e ) {
		e.preventDefault();

		// Show the wrapper
		show_wrapper();
	} );

	/**
	 * Show on "Add New" click
	 *
	 * @since 2.1.0
	 * @param {object} e
	 */
	submit.on( 'click', function( e ) {
		setTimeout( function() {

			// Look for invalid fields
			var invalid = form.find( '.form-field.form-invalid' );

			// Hide if no invalid fields
			if ( 0 === invalid.length ) {
				hide_wrapper();
			}
		}, 100 );
	} );

	/**
	 * Show the "Add New" wrapper
	 *
	 * @since 2.1.0
	 */
	function show_wrapper() {

		// Helper class for escape key-up check
		body.addClass( an_class );

		// Show the wrapper
		wrapper.show();

		// Set focus on the first input
		setTimeout( function() {
			tag_name.focus();
		} );
	}

	/**
	 * Hide the "Add New" wrapper
	 *
	 * @since 2.1.0
	 */
	function hide_wrapper() {

		// Helper class for escape key-up check
		body.removeClass( an_class );

		// Hide the wrapper
		wrapper.hide();
	}
} );
