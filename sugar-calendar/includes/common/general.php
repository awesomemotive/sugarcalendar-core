<?php
/**
 * General Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the updater
 *
 * This function provides an abstraction layer for add-ons, so that they are not
 * required to include their own bespoke copies of it.
 *
 * @since 2.0.12
 *
 * @return mixed
 */
function sugar_calendar_get_updater() {

	// Name of core updater function
	$updater = '\\Sugar_Calendar\\Standard\\License\\get_updater';

	// Return the updater if callable
	if ( function_exists( $updater ) && is_callable( $updater ) ) {
		return call_user_func( $updater );
	}

	// Return false if updater not found
	return false;
}

/**
 * Abstraction for WordPress Script-Debug checking to avoid code duplication.
 *
 * @since 2.0.18
 *
 * @return bool
 */
function sugar_calendar_doing_script_debug() {
	return Sugar_Calendar\Common\Assets\doing_debug();
}

/**
 * Return the current asset version.
 *
 * @since 2.0.18
 *
 * @return string
 */
function sugar_calendar_get_assets_version() {
	return Sugar_Calendar\Common\Assets\get_version();
}

/**
 * Format a timestamp, possibly by time zone.
 *
 * To transmogrify the date format into a language-specific variant, please use
 * sugar_calendar_date_i18n() instead.
 *
 * @since 2.1.0
 * @param string $format    Compatible with DateTime::format().
 * @param mixed  $timestamp Defaults to "now".
 * @param string $timezone1 Defaults to time zone preference.
 * @param string $timezone2 Defaults null.
 * @return string
 */
function sugar_calendar_format_date( $format = 'Y-m-d H:i:s', $timestamp = null, $timezone1 = null, $timezone2 = null ) {

	// Get DateTime object (with time zone) and use it to format
	$dto = sugar_calendar_get_datetime_object( $timestamp, $timezone1, $timezone2 );

	// Format
	$retval = $dto->format( $format );

	// Filter & return
	return apply_filters( 'sugar_calendar_format_date', $retval, $format, $timestamp, $timezone1, $timezone2 );
}

/**
 * Translate a timestamp into a specific format, possibly by time zone.
 *
 * Loosely based on wp_date() but without the site-specific time zone fallback.
 *
 * @since 2.1.0
 * @param string $format    Compatible with DateTime::format().
 * @param mixed  $timestamp Defaults to "now".
 * @param string $timezone1 Defaults to time zone preference.
 * @param string $timezone2 Defaults null.
 * @param string $locale    Defaults to user/site preference.
 * @return string
 */
function sugar_calendar_format_date_i18n( $format = 'Y-m-d H:i:s', $timestamp = null, $timezone1 = null, $timezone2 = null, $locale = null ) {
	global $wp_locale;

	// Switch!
	if ( ! empty( $locale ) ) {
		switch_to_locale( $locale );
	}

	// Get DateTime object (with time zones) and use it to format
	$dto = sugar_calendar_get_datetime_object( $timestamp, $timezone1, $timezone2 );

	// No locale available, so fallback to regular date formatting
	if ( empty( $wp_locale->month ) || empty( $wp_locale->weekday ) ) {
		$retval = $dto->format( $format );

	// Can localize, so try...
	} else {

		// We need to unpack shorthand `r` format because it has parts that might be localized.
		$format = preg_replace( '/(?<!\\\\)r/', DATE_RFC2822, $format );

		$new_format    = '';
		$slashes       = '\\A..Za..z';
		$format_length = strlen( $format );
		$month         = $wp_locale->get_month( $dto->format( 'm' ) );
		$weekday       = $wp_locale->get_weekday( $dto->format( 'w' ) );

		for ( $i = 0; $i < $format_length; $i ++ ) {
			switch ( $format[ $i ] ) {
				case 'D' :
					$str         = $wp_locale->get_weekday_abbrev( $weekday );
					$new_format .= addcslashes( $str, $slashes );
					break;

				case 'F' :
					$new_format .= addcslashes( $month, $slashes );
					break;

				case 'l' :
					$new_format .= addcslashes( $weekday, $slashes );
					break;

				case 'M' :
					$str         = $wp_locale->get_month_abbrev( $month );
					$new_format .= addcslashes( $str, $slashes );
					break;

				case 'a' :
					$str         = $wp_locale->get_meridiem( $dto->format( 'a' ) );
					$new_format .= addcslashes( $str, $slashes );
					break;

				case 'A' :
					$str         = $wp_locale->get_meridiem( $dto->format( 'A' ) );
					$new_format .= addcslashes( $str, $slashes );
					break;

				case 'e' :
					$str         = sugar_calendar_format_timezone( $dto->format( 'e' ) );
					$new_format .= addcslashes( $str, $slashes );
					break;

				case '\\' :
					$new_format .= $format[ $i ];

					// If character follows a slash, we add it without translating.
					if ( $i < $format_length ) {
						$new_format .= $format[ ++$i ];
					}
					break;

				default :
					$new_format .= $format[ $i ];
					break;
			}
		}

		// Use the new format
		$date   = $dto->format( $new_format );

		// Prevent impossible dates
		$retval = wp_maybe_decline_date( $date, $format );
	}

	// Unswitch!
	if ( ! empty( $locale ) ) {
		restore_previous_locale();
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_format_date_i18n', $retval, $format, $timestamp, $timezone1, $timezone2, $locale );
}

/**
 * Generate the HTML for a <time> tag.
 *
 * @since 2.1.0
 * @param array $args
 * @return string HTML for a <time> tag
 */
function sugar_calendar_get_time_tag( $args = array() ) {

	// Default arrays
	$ddata = array( 'timezone' => '' );
	$dattr = array( 'datetime' => '', 'title' => '' );

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'time'     => null,
		'timezone' => null,
		'format'   => null,
		'dtformat' => DATE_ISO8601,
		'data'     => $ddata,
		'attr'     => $dattr
	) );

	// Make sure data is array
	if ( ! is_array( $r['data'] ) ) {
		$r['data'] = $ddata;
	}

	// Make sure attr is array
	if ( ! is_array( $r['attr'] ) ) {
		$r['attr'] = $dattr;
	}

	// Set timezone in data
	$r['data']['timezone'] = ! empty( $r['timezone'] )
		? $r['timezone']
		: 'floating';

	// Get the DateTime object
	$dto = sugar_calendar_get_datetime_object( $r['time'] );

	// Non-floating so set time zone and get the offset
	if ( ! empty( $r['timezone'] ) && ( 'floating' !== $r['timezone'] ) ) {

		// Maybe set the time zone
		$dto = sugar_calendar_set_datetime_timezone( $dto, $r['timezone'] );

		// Add the offset
		$r['data']['offset'] = $dto->getOffset();
	}

	// Format the time
	$dt  = sugar_calendar_format_date( $r['format'],   $r['time'], $r['timezone'] );
	$dtf = sugar_calendar_format_date( $r['dtformat'], $r['time'], $r['timezone'] );

	// Default attribute string
	$r['attr']['datetime'] = esc_attr( $dtf );
	$r['attr']['title']    = esc_attr( $dtf );

	// Default array
	$arr = $r['attr'];

	// Add data attributes
	if ( ! empty( $r['data'] ) ) {
		foreach ( $r['data'] as $key => $value ) {
			$arr[ 'data-' . $key ] = esc_attr( $value );
		}
	}

	// Default attribute string
	$attr = '';

	// Concatenate HTML tag attributes
	if ( ! empty( $arr ) ) {

		// Remove empties and duplicates
		$arr = array_filter( $arr );

		// Build
		foreach ( $arr as $key => $value ) {
			$attr .= ' ' . sanitize_key( $key ) . '="' . esc_attr( $value ) . '"';
		}
	}

	// Setup return value
	$retval = '<time' . $attr . '>' . esc_html( $dt ) . '</time>';

	return $retval;
}
