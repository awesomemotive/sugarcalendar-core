/* global sc_vars */
jQuery( document ).ready( function( $ ) {
	'use strict';

	// Get license key elements (for togglin')
	var license    = $( 'input.sc-license-key' ),
		verify     = $( '.sc-license-verify' ),
		deactivate = $( '.sc-license-deactivate' ),
		refresh    = $( '.sc-license-refresh' ),
		status     = $( '.sc-license-status' ),
		bubbles    = $( '.sc-settings-bubble' ),
		feedback   = $( '.sc-license-feedback span' );

	// License key changes
	license.on( 'input', function( e ) {

		// Get the data attributes
		var oclass = status.attr( 'class' );

		// Empty
		function empty() {
			status.removeClass( 'valid invalid empty verifying' );
			status.addClass( oclass );

			verify.attr( 'data-action', '' );
			verify.attr( 'disabled', true );
			verify.html( sc_vars.label_btn_default );

			deactivate.attr( 'disabled', true );

			refresh.hide();

			feedback.html( sc_vars.feedback_empty );
		}

		// Not empty
		function not_empty() {
			status.removeClass( 'valid invalid empty verifying' );
			status.addClass( 'empty' );

			verify.attr( 'data-action', '' );
			verify.attr( 'disabled', false );
			verify.html( sc_vars.label_btn_default );

			refresh.hide();
		}

		// Trim spaces
		var license_key = license.val().trim();

		// Decide which to call based on trimmed length
		if ( ! license_key.length ) {
			empty();
		} else {
			not_empty();
		}
	} );

	/**
	 * Checking based on clicks
	 *
	 * @since 2.0.0
	 *
	 * @param {string} method
	 */
	function click( method = 'activate' ) {

			// Get the data attributes
		var daction   = verify.attr( 'data-action' ),
			dactivate = deactivate.attr( 'disabled' ),
			dbubbles  = bubbles.is( ':hidden' ),

			// Get the original values
			oclass    = status.attr( 'class' ),
			ofeedback = feedback.html(),

			// Get the license & nonce
			key       = license.val(),
			nonce     = sc_vars.license_nonce;

		/**
		 * Reset the license form back to original
		 *
		 * @since 2.0.0
		 */
		function reset() {
			status.removeClass( 'valid invalid empty verifying' );
			status.addClass( oclass );

			verify.attr( 'data-action', '' );
			verify.attr( 'disabled', false );
			verify.html( sc_vars.label_btn_default );

			license.attr( 'disabled', false );

			deactivate.attr( 'disabled', dactivate );

			refresh.show();

			if ( dbubbles ) {
				bubbles.hide();
			} else {
				bubbles.show();
			}

			feedback.html( ofeedback );
		}

		/**
		 * Update the license form based on response feedback
		 *
		 * @since 2.0.0
		 *
		 * @param {object} data Data used to update UI
		 */
		function update( data ) {
			status.removeClass( 'valid invalid empty verifying' );
			status.addClass( data.feedback.class );

			verify.attr( 'data-action', '' );
			verify.html( sc_vars.label_btn_default );

			license.attr( 'disabled', false );
			license.val( data.key );

			if ( 'valid' === data.feedback.id ) {
				verify.attr( 'disabled', true );
				deactivate.attr( 'disabled', false );
				refresh.show();
				bubbles.hide();
			} else {
				verify.attr( 'disabled', false );
				bubbles.show();
			}

			feedback.html( data.feedback.message );
		}

		/**
		 * Check the license with a remote request
		 *
		 * @since 2.0.0
		 */
		function check() {

			// Bail if no key
			if ( ! key.trim().length ) {
				return;
			}

			status.removeClass( 'valid invalid empty' );
			status.addClass( 'verifying' );

			verify.attr( 'data-action', 'verifying' );
			verify.attr( 'disabled', true );
			verify.html( sc_vars.label_btn_clicked );

			license.attr( 'disabled', true );

			deactivate.attr( 'disabled', true );

			refresh.hide();

			feedback.html( sc_vars.label_feedback );

			// Remote POST
			$.post(

				// URL
				sc_vars.ajax_url,

				// Arguments
				{
					action  : 'sc_license_verify',
					license : key,
					nonce   : nonce,
					method  : method
				},

				// Response
				function ( response ) {

					// Bail if fatal error occurred
					if ( ! response.length ) {
						reset();
						return;
					}

					// Parse JSON
					var data = $.parseJSON( response );

					// Success
					if ( true === data.success ) {
						update( data );
					} else {
						reset();
					}
				}
			);
		}

		// Already verifying, so cancel
		if ( 'verifying' === daction ) {
			reset();

		// Attempt to verify
		} else {
			check();
		}
	}

	// Deactivate a license
	deactivate.on( 'click', function( e ) {
		e.preventDefault();
		click( 'deactivate' );
	} );

	// Refresh triggers verify
	refresh.on( 'click', function( e ) {
		e.preventDefault();
		click( 'check' );
	} );

	// AJAX verify license
	verify.on( 'click', function( e ) {
		e.preventDefault();
		click( 'activate' );
	} );
} );
