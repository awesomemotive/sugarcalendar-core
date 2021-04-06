jQuery( document ).ready( function( $ ) {

	var chosenVars = {
		disable_search_threshold: 13,
		search_contains: true,
		inherit_select_classes: true,
		single_backstroke_delete: false,
		placeholder_text_single: '',
		placeholder_text_multiple: '',
		no_results_text: ''
	};

	/**
	 * Determine the variables used to initialie Chosen on an element.
	 *
	 * @param {Object} el select element.
	 * @return {Object} Variables for Chosen.
	 */
	var getChosenVars = ( el ) => {
		let inputVars = chosenVars;

		// Ensure <select data-search-type="download"> or similar can use search always.
		// These types of fields start with no options and are updated via AJAX.
		if ( el.data( 'search-type' ) ) {
			delete inputVars.disable_search_threshold;
		}

		// Allow a lists to configure single deselection
		if ( el.data( 'single-deselect' ) ) {
			inputVars.allow_single_deselect = true;
		}

		return inputVars;
	};

	// Globally apply to elements on the page.
	$( '.sc-select-chosen' ).each( function() {
		const el = $( this );
		el.chosen( getChosenVars( el ) );
	} );

	$( '.sc-select-chosen .chosen-search input' ).each( function() {
		// Bail if placeholder already set
		if ( $( this ).attr( 'placeholder' ) ) {
			return;
		}

		const selectElem = $( this ).parent().parent().parent().prev( 'select.sc-select-chosen' ),
			placeholder = selectElem.data( 'search-placeholder' );

		if ( placeholder ) {
			$( this ).attr( 'placeholder', placeholder );
		}
	} );

	// Add placeholders for Chosen input fields
	$( '.chosen-choices' ).on( 'click', function() {
		let placeholder = $( this ).parent().prev().data( 'search-placeholder' );
		if ( typeof placeholder === 'undefined' ) {
			placeholder = ''; // edd_vars.type_to_search; // to localize
		}
		$( this ).children( 'li' ).children( 'input' ).attr( 'placeholder', placeholder );
	} );

	// Clear all Calendar taxonomy radios
	$( '#sc_event_category-clear' ).on( 'click', function( event ) {
		$( '#sc_event_categorychecklist input' ).removeAttr( 'checked' );
		event.preventDefault();
	} );
} );