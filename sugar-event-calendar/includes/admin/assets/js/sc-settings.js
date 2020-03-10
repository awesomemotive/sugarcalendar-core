/* global sc_vars */
jQuery( document ).ready( function( $ ) {
	'use strict';

	/** License Key ***********************************************************/

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

	/** Sortables *************************************************************/

	/**
	 * Sortables
	 *
	 * This makes certain settings sortable, and attempts to stash the results
	 * in the nearest .edd-order input value.
	 */
	const sc_sortables = $( 'ul.sc-sortable-list' );

	if ( sc_sortables.length > 0 ) {
		sc_sortables.sortable( {
			axis:        'y',
			items:       'li',
			cursor:      'move',
			tolerance:   'pointer',
			containment: 'parent',
			distance:    2,
			opacity:     0.7,
			scroll:      true,

			/**
			 * When sorting stops, assign the value to the previous input.
			 * This input should be a hidden text field
			 */
			stop: function() {
				var list = $( this );

				const keys = $.map( list.children( 'li' ), function( el ) {
					 return $( el ).data( 'key' );
				} );

				list.prev( 'input.sc-order' ).val( keys );
			}
		} );
	}

	/** Date/Time Formatting **************************************************/

	// Date format click
	$( 'input[name="sc_date_format"]' ).click( function() {

		// Bail on custom click
		if ( 'sc_date_format_custom_radio' === $( this ).attr( 'id' ) ) {
			return;
		}

		// Update the custom value
		$( 'input[name="sc_date_format_custom"]' )
			.val(
				$( this ).val()
			)
			.closest( 'fieldset' )
			.find( '.example' )
			.text(
				$( this )
				.parent( 'label' )
				.children( '.format-i18n' )
				.text()
			);
	} );

	// Check when clicked
	$( 'input[name="sc_date_format_custom"]' ).on( 'click input', function() {
		$( '#sc_date_format_custom_radio' ).prop( 'checked', true );
	} );

	// Time format click
	$( 'input[name="sc_time_format"]' ).click( function() {

		// Bail on custom click
		if ( 'sc_time_format_custom_radio' === $( this ).attr( 'id' ) ) {
			return;
		}

		// Update the custom value
		$( 'input[name="sc_time_format_custom"]' )
			.val(
				$( this ).val()
			)
			.closest( 'fieldset' )
			.find( '.example' )
			.text(
				$( this )
				.parent( 'label' )
				.children( '.format-i18n' )
				.text()
			);
	} );

	// Clicking custom Time
	$( 'input[name="sc_time_format_custom"]' ).on( 'click input', function() {
		$( '#sc_time_format_custom_radio' ).prop( 'checked', true );
	} );

	// Typing in custom Date or Time boxes
	$( 'input[name="sc_date_format_custom"], input[name="sc_time_format_custom"]' ).on( 'input', function() {

		// Get elements
		var format   = $( this ),
			val      = $.trim( format.val() ),
			fieldset = format.closest( 'fieldset' ),
			example  = fieldset.find( '.example' ),
			spinner  = fieldset.find( '.spinner' ),
			type     = ( 'sc_date_format_custom' === format.attr( 'name' ) )
				? 'date'
				: 'time',
			action   = 'sc_' + type + '_format',
			radio    = $( '#sc_' + type + '_format_custom_radio' );

		// Bail if empty
		if ( ! val ) {
			spinner.removeClass( 'is-active' );
			example.html( '&mdash;' );
			return;
		}

		// Trigger
		spinner.addClass( 'is-active' );

		// Set the radio value
		radio.val( val );

		// Debounce the event callback while users are typing.
		clearTimeout( $.data( this, 'timer' ) );

		$( this ).data( 'timer', setTimeout( function() {

			// If custom date is not empty.
			if ( ! val ) {
				return;
			}

			$.post(
				sc_vars.ajax_url,
				{
					action  : action,
					sc_date : val
				},
				function( d ) {
					spinner.removeClass( 'is-active' );
					example.text( d );
				}
			);
		}, 400 ) );
	} );

	// When a section nav item is clicked.
	$( '.sc-settings-content .form-table a.screen-options' ).on( 'click', function( j ) {

		// Prevent the default browser action when a link is clicked.
		j.preventDefault();

		// Click the settings link
		if ( $( '#contextual-help-wrap' ).is( ':hidden' ) ) {
			$( '#contextual-help-link' ).click();
		}

		// Select Date tab
		if ( $( this ).hasClass( 'sc-date-help' ) ) {
			$( '#tab-panel-date-formatting' ).trigger( 'click' );

		// Select Time tab
		} else if ( $( this ).hasClass( 'sc-time-help' ) ) {
			$( '#tab-panel-time-formatting' ).trigger( 'click' );
		}
	} );

	// Help date property clicks
	$( 'table.sc-custom-date-table tr' ).on( 'click', function() {
		var val   = $( this ).find( 'code.code' ).text(),
			input = $( '#sc_date_format_custom' );

		// Append
		if ( val ) {
			input.val( input.val() + val );
			input.trigger( 'input' );
			$( this ).hide().fadeIn();
		}
	} );

	// Help time property clicks
	$( 'table.sc-custom-time-table tr' ).on( 'click', function() {
		var val   = $( this ).find( 'code.code' ).text(),
			input = $( '#sc_time_format_custom' );

		// Append
		if ( val ) {
			input.val( input.val() + val );
			input.trigger( 'input' );
			$( this ).hide().fadeIn();
		}
	} );
} );
