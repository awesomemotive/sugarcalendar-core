<?php
/**
 * Time Zone Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get a time zone object.
 *
 * @since 2.1.0
 *
 * @param string $timezone The time zone to use
 * @return string
 */
function sugar_calendar_get_timezone( $timezone = '' ) {

	// Validate, or fallback to site option
	if ( ! sugar_calendar_validate_timezone( $timezone ) ) {

		// Fallback to Sugar Calendar time zone
		$timezone = get_option( 'sc_timezone' );

		// Fallback to WordPress Site time zone
		if ( empty( $timezone ) ) {
			$timezone = get_option( 'timezone_string' );
		}
	}

	// Get the site offset
	$offset = get_option( 'gmt_offset' );

	/**
	 * Discourage manual offset
	 *
	 * IANA time zone database that provides PHP's time zone support uses
	 * (i.e. reversed) POSIX style signs
	 *
	 * @see https://www.php.net/manual/en/timezones.others.php
	 * @see https://bugs.php.net/bug.php?id=45543
	 * @see https://bugs.php.net/bug.php?id=45528
	 */
	if ( empty( $timezone ) && ( 0 !== $offset ) && ( floor( $offset ) === $offset ) ) {

		// Make the offset string
		$offset_st = ( $offset > 0 )
			? "-{$offset}"
			: '+' . absint( $offset );

		// Make the Unknown time zone string
		$timezone  = "Etc/GMT{$offset_st}";
	}

	// Issue with the time zone selected, set to 'UTC'
	if ( empty( $timezone ) ) {
		$timezone = 'UTC';
	}

	// Create a time zone object
	$retval = new \DateTimeZone( $timezone );

	// Return the timezone object
	return $retval;
}

/**
 * Get all Olson time zone IDs.
 *
 * @since 2.1.0
 * @return array
 */
function sugar_calendar_get_olson_timezones() {
	return timezone_identifiers_list();
}

/**
 * Get all continents in Olson time zones.
 *
 * @since 2.1.0
 * @return array
 */
function sugar_calendar_get_olson_continents() {
	return array(
		'Africa',
		'America',
		'Antarctica',
		'Arctic',
		'Asia',
		'Atlantic',
		'Australia',
		'Europe',
		'Indian',
		'Pacific'
	);
}

/**
 * Get all manual time zone offsets.
 *
 * @since 2.1.0
 * @return array
 */
function sugar_calendar_get_manual_offsets() {
	return array(
		-12,
		-11.5,
		-11,
		-10.5,
		-10,
		-9.5,
		-9,
		-8.5,
		-8,
		-7.5,
		-7,
		-6.5,
		-6,
		-5.5,
		-5,
		-4.5,
		-4,
		-3.5,
		-3,
		-2.5,
		-2,
		-1.5,
		-1,
		-0.5,
		0,
		0.5,
		1,
		1.5,
		2,
		2.5,
		3,
		3.5,
		4,
		4.5,
		5,
		5.5,
		5.75,
		6,
		6.5,
		7,
		7.5,
		8,
		8.5,
		8.75,
		9,
		9.5,
		10,
		10.5,
		11,
		11.5,
		12,
		12.75,
		13,
		13.75,
		14
	);
}

/**
 * Get all valid time zone values. Used for validation.
 *
 * @since 2.1.0
 * @param array $args
 * @return array
 */
function sugar_calendar_get_valid_timezones( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'allow_empty'  => true,
		'allow_utc'    => true,
		'allow_manual' => false
	) );

	// Default return value
	$retval = array();

	// Support empty/floating time zone
	if ( ! empty( $r['allow_empty'] ) ) {
		$retval[] = '';
	}

	// Support UTC time zone
	if ( ! empty( $r['allow_empty'] ) ) {
		$retval[] = 'UTC';
	}

	// Get identifiers
	$retval = array_merge( $retval, sugar_calendar_get_olson_timezones() );

	// Support manual offsets
	if ( ! empty( $r['allow_manual'] ) ) {

		// Do manual UTC offsets.
		$offset_range = sugar_calendar_get_manual_offsets();

		// Loop through ranges
		foreach ( $offset_range as $offset ) {

			// Format the offset value
			$offset_value = ( 0 <= $offset )
				? "+{$offset}"
				: (string) $offset;

			// Add the value to the return array
			$retval[] = "UTC{$offset_value}";
		}
	}

	// Filter & return
	return (array) apply_filters( 'sugar_calendar_get_valid_timezones', $retval, $r, $args );
}

/**
 * Validate a time zone.
 *
 * @since 2.1.0
 * @param string $timezone
 * @return string
 */
function sugar_calendar_validate_timezone( $timezone = '' ) {

	// Return empty string if invalid
	if ( empty( $timezone ) || ! is_string( $timezone ) ) {
		return '';
	}

	// Get all valid time zones
	$timezones = sugar_calendar_get_valid_timezones();

	// Check if this time zone is valid
	$valid = in_array( $timezone, $timezones, true );

	// Return time zone or empty string
	return ! empty( $valid )
		? $timezone
		: '';
}

/**
 * Output a <select> HTML element of Time Zones
 *
 * @since 2.1.0
 * @param array $args
 */
function sugar_calendar_timezone_dropdown( $args = array() ) {
	static $mo_loaded = false, $locale_loaded = null;

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'id'           => 'sc_timezone',
		'name'         => 'sc_timezone',
		'class'        => 'sc-select-chosen',
		'locale'       => '',
		'current'      => get_option( 'sc_timezone' ),
		'allow_empty'  => true,
		'allow_utc'    => true,
		'allow_manual' => false,
		'placeholder'  => esc_html__( 'Select a time zone', 'sugar-calendar' ),
		'none'         => esc_html__( 'Floating',           'sugar-calendar' )
	) );

	// Sanitize ID & Name
	$id            = sanitize_key( $r['id'] );
	$name          = sanitize_key( $r['name'] );
	$locale        = $r['locale'];
	$selected_zone = $r['current'];
	$none_text     = $r['none'];
	$placeholder   = $r['placeholder'];

	// Sanitize classes
	$classes = array_map( 'sanitize_html_class', explode( ' ', $r['class'] ) );

	// Start the HTML structure
	$structure = array(
		'<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" class="' . implode( ' ', $classes ) . '" data-placeholder="' . esc_attr( $placeholder ) . '">'
	);

	// Allowed continents (why not just disallow instead?)
	$continents = sugar_calendar_get_olson_continents();

	$zones = array();

	// Load translations for continents and cities
	if ( empty( $mo_loaded ) || ( $locale !== $locale_loaded ) ) {
		$locale_loaded = ! empty( $locale )
			? $locale
			: get_locale();
		$mofile        = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
		unload_textdomain( 'continents-cities' );
		load_textdomain( 'continents-cities', $mofile );
		$mo_loaded = true;
	}

	// Get Olson identifiers
	$identifiers = sugar_calendar_get_olson_timezones();

	// Loop through all identifiers
	foreach ( $identifiers as $zone ) {
		$zone = explode( '/', $zone );

		// Skip unknown continents (UTC specifically is done later)
		if ( ! in_array( $zone[0], $continents, true ) ) {
			continue;
		}

		// This determines what gets set and translated - we don't translate
		// Etc/* strings here, they are done later
		$exists    = array(
			0 => ( isset( $zone[0] ) && $zone[0] ),
			1 => ( isset( $zone[1] ) && $zone[1] ),
			2 => ( isset( $zone[2] ) && $zone[2] )
		);
		$exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
		$exists[4] = ( $exists[1] && $exists[3] );
		$exists[5] = ( $exists[2] && $exists[3] );

		// phpcs:disable WordPress.WP.I18n.LowLevelTranslationFunction,WordPress.WP.I18n.NonSingularStringLiteralText
		$zones[] = array(
			'continent'   => ( $exists[0] ? $zone[0] : '' ),
			'city'        => ( $exists[1] ? $zone[1] : '' ),
			'subcity'     => ( $exists[2] ? $zone[2] : '' ),
			't_continent' => ( $exists[3] ? translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
			't_city'      => ( $exists[4] ? translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
			't_subcity'   => ( $exists[5] ? translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' )
		);
		// phpcs:enable
	}

	// Sort these zones
	usort( $zones, '_wp_timezone_choice_usort_callback' );

	// Support empty/floating time zone
	if ( ! empty( $r['allow_empty'] ) ) {
		$structure[] = '<optgroup label="' . esc_attr__( 'Default', 'sugar-calendar' ) . '">';
		$structure[] = '<option ' . selected( $selected_zone, false, false ) . ' value="">' . esc_html( $none_text ) . '</option>';
		$structure[] = '</optgroup>';
	}

	// Support UTC time zone
	if ( ! empty( $r['allow_empty'] ) ) {
		$structure[] = '<optgroup label="' . esc_attr__( 'UTC', 'sugar-calendar' ) . '">';
		$structure[] = '<option ' . selected( 'UTC', $selected_zone, false ) . 'value="' . esc_attr( 'UTC' ) . '">' . __( 'UTC', 'sugar-calendar' ) . '</option>';
		$structure[] = '</optgroup>';
	}

	// Loop through zones
	foreach ( $zones as $key => $zone ) {

		// Build value in an array to join later
		$value = array( $zone['continent'] );

		// It's at the continent level (generally won't happen)
		if ( empty( $zone['city'] ) ) {
			$display = $zone['t_continent'];

		// It's inside a continent group
		} else {

			// Continent optgroup
			if ( ! isset( $zones[ $key - 1 ] ) || $zones[ $key - 1 ]['continent'] !== $zone['continent'] ) {
				$label       = $zone['t_continent'];
				$structure[] = '<optgroup label="' . esc_attr( $label ) . '">';
			}

			// Add the city to the value
			$value[] = $zone['city'];

			$display = $zone['t_city'];

			// Add the subcity to the value
			if ( ! empty( $zone['subcity'] ) ) {
				$value[]  = $zone['subcity'];
				$display .= ' - ' . $zone['t_subcity'];
			}
		}

		// Build the value
		$value       = join( '/', $value );
		$structure[] = '<option ' . selected( $value, $selected_zone, false ) . 'value="' . esc_attr( $value ) . '">' . esc_html( $display ) . '</option>';

		// Close continent <optgroup>
		if ( ! empty( $zone['city'] ) && ( ! isset( $zones[ $key + 1 ] ) || ( isset( $zones[ $key + 1 ] ) && $zones[ $key + 1 ]['continent'] !== $zone['continent'] ) ) ) {
			$structure[] = '</optgroup>';
		}
	}

	// Support manual offsets
	if ( ! empty( $r['allow_manual'] ) ) {

		// Do manual UTC offsets
		$structure[]  = '<optgroup label="' . esc_attr__( 'Manual Offsets', 'sugar-calendar' ) . '">';
		$offset_range = sugar_calendar_get_manual_offsets();

		// Loop through offsets and create human readible options
		foreach ( $offset_range as $offset ) {

			$offset_name = ( 0 <= $offset )
				? "+{$offset}"
				: (string) $offset;

			// Format name & value
			$offset_value = $offset_name;
			$offset_name  = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $offset_name );

			// Prefix name & value
			$offset_name  = "UTC{$offset_name}";
			$offset_value = "UTC{$offset_value}";

			// Add option
			$structure[]  = '<option ' . selected( $offset_value, $selected_zone, false ) . 'value="' . esc_attr( $offset_value ) . '">' . esc_html( $offset_name ) . '</option>';
		}

		// Close the manual <optgroup>
		$structure[] = '</optgroup>';
	}

	// Close the <select>
	$structure[] = '</select>';

	// Output the HTML
	echo join( "\n", $structure );
}
