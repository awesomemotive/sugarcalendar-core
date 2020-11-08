<?php
/**
 * Time Zone Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the time zone.
 *
 * @since 2.1.0
 * @return string
 */
function sugar_calendar_get_timezone() {

	// Get user time zone preference - default "Floating"
	$retval = sugar_calendar_get_user_preference( 'sc_timezone', '' );

	// Filter & return
	return apply_filters( 'sugar_calendar_get_timezone', $retval );
}

/**
 * Get the difference between two time zones, at a specific time.
 *
 * @since 2.1.0
 *
 * @param string $timezone1 First Olson time zone ID.
 * @param string $timezone2 Optional. Default: 'UTC'. Second Olson time zone ID.
 * @param mixed  $datetime  Optional. Default: 'now'. Time to use for diff.
 *
 * @return int Number of seconds between two time zones at that time.
 */
function sugar_calendar_get_timezone_diff( $timezone1 = '', $timezone2 = 'UTC', $datetime = 'now' ) {

	// Use now if empty
	if ( empty( $datetime ) ) {
		$datetime = 'now';
	}

	// Get the time zones
	$tz1 = sugar_calendar_get_timezone_object( $timezone1 );
	$tz2 = sugar_calendar_get_timezone_object( $timezone2 );

	// Get the datetimes, using the time zones
	$dt1 = new \DateTime( $datetime, $tz1 );
	$dt2 = new \DateTime( $datetime, $tz2 );

	// Get the two offsets
	$off1 = $tz1->getOffset( $dt1 );
	$off2 = $tz2->getOffset( $dt2 );

	// Get the difference
	$retval = $off1 - $off2;

	// Filter & return
	return apply_filters( 'sugar_calendar_get_timezone_diff', $retval, $datetime, $timezone1, $timezone2 );
}

/**
 * Get a human readable representation of the time between two time zones at a
 * given date and time.
 *
 * This function will return a human-readable representation using hours:
 * - 2 hours
 * - 4.25 hours
 * - -13.5 hours
 *
 * @param string $timezone1 First Olson time zone ID
 * @param string $timezone2 Optional. Default: 'UTC'. Second Olson time zone ID
 * @param mixed  $datetime  Optional. Default: 'now'. Time to use for diff
 *
 * @return string String representing the time difference - "2.5 hours"
 */
function sugar_calendar_human_diff_timezone( $timezone1, $timezone2 = 'UTC', $datetime = 'now' ) {

	// Default return value
	$retval = '';

	// Get the difference
	$difference = sugar_calendar_get_timezone_diff( $timezone1, $timezone2, $datetime );

	// Time change text
	if ( ! empty( $difference ) ) {

		// Calculate the change
		$change = abs( $difference / HOUR_IN_SECONDS );

		// Positive or negative
		$posneg = ( $difference > 0 )
			? esc_html_x( '+', 'Plus',  'sugar-calendar' )
			: esc_html_x( '-', 'Minus', 'sugar-calendar' );

		// Format the text
		$number = number_format_i18n( $change );
		$string = _n( '%s hour', '%s hours', $change, 'sugar-calendar' );
		$retval = sprintf( $string, $posneg . $number );
	}

	/**
	 * Filters the human readable representation of the time elapsed since a
	 * given date.
	 *
	 * @since 2.0.0
	 *
	 * @param string $retval    Final return value
	 * @param string $timezone1 First Olson time zone ID.
	 * @param string $timezone2 Optional. Default: 'UTC'. Second Olson time zone ID.
	 * @param string $datetime  Optional. Default: 'now'. (current time).
	 */
	return apply_filters( 'sugar_calendar_human_diff_timezone', $retval, $timezone1, $timezone2, $datetime );
}

/**
 * Get the offset of a specific time zone.
 *
 * Defaults to UTC offset, returned in "-0500" format.
 *
 * @since 2.1.0
 * @param array
 * @return mixed
 */
function sugar_calendar_get_timezone_offset( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'datetime' => 'now',
		'timezone' => 'UTC',
		'format'   => 'RFC2822'
	) );

	// Get objects
	$tzo = sugar_calendar_get_timezone_object( $r['timezone'] );
	$dt  = new \DateTime( $r['datetime'] );

	// Format the return value
	$retval = sugar_calendar_format_timezone_offset( array(
		'offset'   => timezone_offset_get( $tzo, $dt ),
		'timezone' => $r['timezone'],
		'format'   => $r['format']
	) );

	// Filter & return
	return apply_filters( 'sugar_calendar_get_timezone_offset', $retval, $r, $args );
}

/**
 * Format a time zone string.
 *
 * @since 2.1.0
 *
 * @param string $timezone Default ''. Olson time zone ID.
 * @return string
 */
function sugar_calendar_format_timezone( $timezone = '' ) {

	// Default return value
	$retval = $timezone;

	// Empty time zone is floating
	if ( empty( $timezone ) ) {
		$retval = esc_html__( 'Floating', 'sugar-calendar' );

	// Manual offset looks like "Etc/GMT-5"
	} elseif ( sugar_calendar_is_manual_timezone_offset( $timezone ) ) {
		$retval = sugar_calendar_format_manual_timezone_offset( $timezone );

	// Olson IDs should not break because of spaces
	} else {
		$retval = str_replace( '_', '&nbsp;', $timezone );
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_format_timezone', $retval, $timezone );
}

/**
 * Format a manual time zone offset.
 *
 * IANA time zone database that provides PHP's time zone support uses
 * (i.e. reversed) POSIX style signs
 *
 * @see https://www.php.net/manual/en/timezones.others.php
 * @see https://bugs.php.net/bug.php?id=45543
 * @see https://bugs.php.net/bug.php?id=45528
 *
 * @since 2.1.0
 * @param string $timezone Default ''. Olson time zone ID.
 * @return string
 */
function sugar_calendar_format_manual_timezone_offset( $timezone = '' ) {

	// Get the manual offset
	$offset = sugar_calendar_get_manual_timezone_offset( 'now', $timezone );

	// Make the offset string
	$offset_st = ( $offset > 0 )
		? "-{$offset}"
		: '+' . absint( $offset );

	// Make the Unknown time zone string
	$retval  = "Etc/GMT{$offset_st}";

	// Filter & return
	return $retval;
}

/**
 * Format a time zone offset.
 *
 * @since 2.1.0
 * @param array $args
 * @return mixed
 */
function sugar_calendar_format_timezone_offset( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'offset'   => 0,
		'timezone' => 'UTC',
		'format'   => 'RFC2822'
	) );

	// Return value formatting
	switch ( strtoupper( $r['format'] ) ) {

		// +/-0000
		case 'RFC822' :
		case 'RFC850' :
		case 'RFC1036' :
		case 'RFC1123' :
		case 'RFC2822' :
		case 'RFC3339' :
		case 'RSS' :

			// Math
			$hours    = absint( floor( $r['offset'] / HOUR_IN_SECONDS ) );
			$minutes  = absint( $r['offset'] % MINUTE_IN_SECONDS );
			$negative = ( $r['offset'] < 0 );

			// Format
			$hours    = str_pad( $hours,   2, '0', STR_PAD_LEFT );
			$minutes  = str_pad( $minutes, 2, '0', STR_PAD_LEFT );
			$mod      = empty( $negative ) && ! empty( $hours )
				? '+'
				: '-';

			// Return value
			$retval = "{$mod}{$hours}{$minutes}";
			break;

		// +/-00:00
		case 'ATOM' :
		case 'COOKIE' :
		case 'ISO8601' :
		case 'W3C' :

			// Math
			$hours    = absint( floor( $r['offset'] / HOUR_IN_SECONDS ) );
			$minutes  = absint( $r['offset'] % MINUTE_IN_SECONDS );
			$negative = ( $r['offset'] < 0 );

			// Format
			$hours    = str_pad( $hours,   2, '0', STR_PAD_LEFT );
			$minutes  = str_pad( $minutes, 2, '0', STR_PAD_LEFT );
			$mod      = empty( $negative ) && ! empty( $hours )
				? '+'
				: '-';

			// Return value
			$retval = "{$mod}{$hours}:{$minutes}";
			break;

		// -18000
		case 'SECONDS' :
			$retval = $r['offset'];
			break;

		// -300
		case 'MINUTES' :
			$retval = ! empty( $r['offset'] )
				? ( $r['offset'] / MINUTE_IN_SECONDS )
				: 0;
			break;

		// 4.5
		case 'HOURS' :
		default :
			$retval = ! empty( $r['offset'] )
				? ( $r['offset'] / HOUR_IN_SECONDS )
				: 0;
			break;
	}

	// Filter & Return
	return apply_filters( 'sugar_calendar_format_timezone_offset', $retval, $r, $args );
}

/**
 * Get the time zone type.
 *
 * @since 2.1.0
 * @return string
 */
function sugar_calendar_get_timezone_type() {

	// Get user time zone preference - default "off"
	$retval = sugar_calendar_get_user_preference( 'sc_timezone_type', 'off' );

	// Filter & return
	return apply_filters( 'sugar_calendar_get_timezone_type', $retval );
}

/**
 * Get a time zone object.
 *
 * @since 2.1.0
 *
 * @param string $timezone Default ''. Olson time zone ID.
 * @return object
 */
function sugar_calendar_get_timezone_object( $timezone = '' ) {

	// Bail if time zone is invalid
	$timezone = sugar_calendar_validate_timezone( $timezone, array(
		'allow_utc'    => true,
		'allow_manual' => true,
		'allow_empty'  => true
	) );

	// Format the time zone
	$timezone = sugar_calendar_is_manual_timezone_offset( $timezone )
		? sugar_calendar_format_manual_timezone_offset( $timezone )
		: $timezone;

	// "Floating" is not valid, so set to "UTC" to avoid DateTimeZone erroring
	if ( empty( $timezone ) ) {
		$timezone = 'UTC';
	}

	// Create a time zone object
	$retval = new \DateTimeZone( $timezone );

	// Return the time zone object
	return $retval;
}

/**
 * Is a time zone a manual offset?
 *
 * @since 2.1.0
 * @param string $timezone Default ''. Olson time zone ID.
 * @return boolean
 */
function sugar_calendar_is_manual_timezone_offset( $timezone = '' ) {

	// Default return value
	$retval = false;

	// Is manual if: longer than 3 chars and starts with UTC
	if ( ( \strlen( $timezone ) > 3 ) && ( 0 === \strncmp( $timezone, 'UTC', \strlen( 'UTC' ) ) ) ) {

		// Get all manual offsets
		$manuals = sugar_calendar_get_manual_timezone_offsets();

		// Get the offset
		$offset  = (float) substr( $timezone, 3 );

		// A zero offset is still an offset
		if ( empty( $offset ) ) {
			$retval = true;

		// Check of non-zero offset is in the offsets array
		} else {
			$retval = in_array( $offset, $manuals, false );
		}
	}

	// Return if a time zone is a manual offset
	return $retval;
}

/**
 * Get the numeric offset from a manual offset.
 *
 * @since 2.1.0
 * @param mixed  $datetime Default: 'now'. Time to use for diff.
 * @param string $timezone Default: 'UTC'. Olson time zone ID.
 * @return float
 */
function sugar_calendar_get_manual_timezone_offset( $datetime = 'now', $timezone = 'UTC' ) {

	// Default return value
	$retval = 0;

	// Maybe get a manual offset
	if ( sugar_calendar_is_manual_timezone_offset( $timezone ) ) {
		$retval = substr( $timezone, 3 );

	// Not a manual offset
	} else {

		// Get the time zone object
		$tz = sugar_calendar_get_timezone_object( $timezone );

		// Bail if time zone is invalid
		if ( ! empty( $tz ) ) {

			// Get a date time object, based on this time zone
			$dt = new \DateTime( $datetime, $tz );

			// Calculate the offset
			$retval = $dt->getOffset() / HOUR_IN_SECONDS;
		}
	}

	// Return the offset
	return (float) $retval;
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
function sugar_calendar_get_olson_timezone_continents() {
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
function sugar_calendar_get_manual_timezone_offsets() {
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
	if ( ! empty( $r['allow_utc'] ) ) {
		$retval[] = 'UTC';
	}

	// Get identifiers
	$retval = array_merge( $retval, sugar_calendar_get_olson_timezones() );

	// Support manual offsets
	if ( ! empty( $r['allow_manual'] ) ) {

		// Do manual UTC offsets.
		$offset_range = sugar_calendar_get_manual_timezone_offsets();

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
 * @param string $timezone Default ''. Olson time zone ID.
 * @param array  $args     Default array().
 * @return string
 */
function sugar_calendar_validate_timezone( $timezone = '', $args = array() ) {

	// Get all valid time zones
	$timezones = sugar_calendar_get_valid_timezones( $args );

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
		'locale'       => get_user_locale( get_current_user_id() ),
		'current'      => '',

		// What time zones to allow
		'allow_empty'  => true,
		'allow_utc'    => true,
		'allow_manual' => false,

		// Labels
		'placeholder'  => esc_html__( 'Floating', 'sugar-calendar' ),
		'none'         => ''
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
		'<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" class="' . implode( ' ', $classes ) . '" data-placeholder="' . esc_attr( $placeholder ) . '" data-single-deselect="true">'
	);

	// Allowed continents (why not just disallow instead?)
	$continents = sugar_calendar_get_olson_timezone_continents();

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
		$offset_range = sugar_calendar_get_manual_timezone_offsets();

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
