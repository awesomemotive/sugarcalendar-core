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
 * @return boolean
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
 *
 * @param string $format   Defaults to MySQL datetime format.
 * @param mixed  $time     Defaults to "now".
 * @param string $timezone Defaults to time zone preference.
 *
 * @return string
 */
function sugar_calendar_format_date( $format = 'Y-m-d H:i:s', $time = null, $timezone = null ) {

	// Get DateTime object (with time zone) and use it to format
	$dto = sugar_calendar_get_datetime_object( $time, $timezone );

	// Format
	$retval = $dto->format( $format );

	// Filter & return
	return apply_filters( 'sugar_calendar_date', $retval, $format, $time, $timezone );
}

/**
 * Translate a timestamp into a specific format, possibly by time zone.
 *
 * Loosely based on wp_date() but without the site-specific time zone fallback.
 *
 * @since 2.1.0
 *
 * @param string $format   Defaults to MySQL datetime format.
 * @param mixed  $time     Defaults to "now".
 * @param string $timezone Defaults to time zone preference.
 * @param string $locale   Defaults to user/site preference.
 *
 * @return string
 */
function sugar_calendar_format_date_i18n( $format = 'Y-m-d H:i:s', $time = null, $timezone = null, $locale = null ) {
	global $wp_locale;

	// Switch!
	if ( ! empty( $locale ) ) {
		switch_to_locale( $locale );
	}

	// Get DateTime object (with time zone) and use it to format
	$dto = sugar_calendar_get_datetime_object( $time, $timezone );

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
	return apply_filters( 'sugar_calendar_date_i18n', $retval, $format, $time, $timezone, $locale );
}
