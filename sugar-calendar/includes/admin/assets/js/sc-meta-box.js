/* global sc_vars */
jQuery( document ).ready( function ( $ ) {
    'use strict';

	// Date picker args
	var dpArgs = {
		dateFormat: 'yy-mm-dd',
		firstDay: sc_vars.start_of_week,
		showAnim: false,
		beforeShow: function() {

			// Swap out class to avoid CSS collisions
			$( '#ui-datepicker-div' )
				.removeClass( 'ui-datepicker' )
				.addClass( 'sc-datepicker' );
		}
	};

	/**
	 * Safely get a date from an input value
	 *
	 * @since 2.0.0
	 * @param {object} date
	 * @returns {mixed}
	 */
	function getDate(date) {
		var retval;

		try {
			retval = $.datepicker.parseDate( 'yy-mm-dd', date );
		} catch ( error ) {
			retval = null;
		}

		return retval;
	}

	// Date picker
	if ( $( '.sugar_calendar_datepicker' ).length > 0 ) {
		$( '.sugar_calendar_datepicker' )

			// Disable autocomplete to avoid it covering the calendar
			.attr( 'autocomplete', 'off' )

			// Invoke the datepickers
			.datepicker( dpArgs );
	}

	// Make start and end datepickers react to each other
    var start = $( '#start_date' )
			.on( 'change', function () {
				end.datepicker( 'option', 'minDate', getDate( this.value ) );
			} ),

		end = $( '#end_date' )
			.on( 'change', function () {
				start.datepicker( 'option', 'maxDate', getDate( this.value ) );
			} );

	// Set min & max on page load
	start.datepicker( 'option', 'maxDate', getDate( end.val() ) );
	end.datepicker( 'option', 'minDate', getDate( start.val() ) );

	// Toggle time field visibility if all-day
	$( '#all_day' ).on( 'click', function() {
		var checked = $( this ).prop( 'checked' ),
			times   = $( '.time-zone-row, .event-time-zone, .event-time' );

		// Toggle
		( true === checked )
			? times.hide()
			: times.show();
	} );

	// Toggle simple recurrence field visibility if not-never
	$( 'select#recurrence' ).on( 'change', function() {
		var val = $( this ).chosen().val(),
			rep = $( '.repeat-until input' );

		// Toggle
		( '0' === val )
			? rep.prop( 'disabled', true  )
			: rep.prop( 'disabled', false );
	} );

	// Hides the section content.
	$( '.sc-vertical-sections .section-content' ).hide();

	// Shows the first section's content.
	$( '.sc-vertical-sections .section-content:first-child' ).show();

	// Makes the 'aria-selected' attribute true for the first section nav item.
	$( '.section-nav button' ).attr( 'aria-selected', 'false' );

	// Makes the 'aria-selected' attribute true for the first section nav item.
	$( '.section-nav button:first-child' ).attr( 'aria-selected', 'true' );

	// Copies the current section item title to the box header.
	$( '.which-section' ).text( $( '.section-nav :first-child a' ).text() );

	// When a section nav item is clicked.
	$( '.section-nav button' ).on( 'click',
		function( j ) {

			// Prevent the default browser action when a link is clicked.
			j.preventDefault();

			// Get the `href` attribute of the item.
			var them  = $( this ),
				href  = them.attr( 'aria-controls' ),
				rents = them.parents( '.sc-vertical-sections' ),
				sibs  = them.siblings();

			// Hide all section content.
			rents.find( '.section-content' ).hide();

			// Find the section content that matches the section nav item and show it.
			rents.find( '#' + href ).show();

			// Set the `aria-selected` attribute to false for all section nav items.
			sibs.attr( 'aria-selected', 'false' );

			// Set the `aria-selected` attribute to true for this section nav item.
			them.attr( 'aria-selected', 'true' );

			// Copy the current section item title to the box header.
			$( '.which-section' ).text( them.text() );
		}
	); // click()
} );
