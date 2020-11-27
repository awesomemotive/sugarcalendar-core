<?php
/**
 * Sugar Calendar WordPress Compatibility Functions
 *
 * These functions are here to shim things that are in versions of WordPress
 * that the site may not be running yet. That means these functions will be
 * periodically removed from this file as our system requirements increase.
 *
 * @since 2.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wp_date' ) ) :
/**
 * Retrieves the date, in localized format.
 *
 * This is a newer function, intended to replace `date_i18n()` without legacy quirks in it.
 *
 * Note that, unlike `date_i18n()`, this function accepts a true Unix timestamp, not summed
 * with timezone offset.
 *
 * @since 5.3.0
 *
 * @param string       $format    PHP date format.
 * @param int          $timestamp Optional. Unix timestamp. Defaults to current time.
 * @param DateTimeZone $timezone  Optional. Timezone to output result in. Defaults to timezone
 *                                from site settings.
 * @return string|false The date, translated if locale specifies it. False on invalid timestamp input.
 */
function wp_date( $format, $timestamp = null, $timezone = null ) {
	global $wp_locale;

	if ( null === $timestamp ) {
		$timestamp = time();
	} elseif ( ! is_numeric( $timestamp ) ) {
		return false;
	}

	if ( ! $timezone ) {
		$timezone = wp_timezone();
	}

	$datetime = date_create( '@' . $timestamp, $timezone );

	if ( empty( $wp_locale->month ) || empty( $wp_locale->weekday ) ) {
		$date = $datetime->format( $format );
	} else {
		// We need to unpack shorthand `r` format because it has parts that might be localized.
		$format = preg_replace( '/(?<!\\\\)r/', DATE_RFC2822, $format );

		$new_format    = '';
		$format_length = strlen( $format );
		$month         = $wp_locale->get_month( $datetime->format( 'm' ) );
		$weekday       = $wp_locale->get_weekday( $datetime->format( 'w' ) );

		for ( $i = 0; $i < $format_length; $i ++ ) {
			switch ( $format[ $i ] ) {
				case 'D':
					$new_format .= addcslashes( $wp_locale->get_weekday_abbrev( $weekday ), '\\A..Za..z' );
					break;
				case 'F':
					$new_format .= addcslashes( $month, '\\A..Za..z' );
					break;
				case 'l':
					$new_format .= addcslashes( $weekday, '\\A..Za..z' );
					break;
				case 'M':
					$new_format .= addcslashes( $wp_locale->get_month_abbrev( $month ), '\\A..Za..z' );
					break;
				case 'a':
					$new_format .= addcslashes( $wp_locale->get_meridiem( $datetime->format( 'a' ) ), '\\A..Za..z' );
					break;
				case 'A':
					$new_format .= addcslashes( $wp_locale->get_meridiem( $datetime->format( 'A' ) ), '\\A..Za..z' );
					break;
				case '\\':
					$new_format .= $format[ $i ];

					// If character follows a slash, we add it without translating.
					if ( $i < $format_length ) {
						$new_format .= $format[ ++$i ];
					}
					break;
				default:
					$new_format .= $format[ $i ];
					break;
			}
		}

		$date = $datetime->format( $new_format );
		$date = wp_maybe_decline_date( $date, $format );
	}

	/**
	 * Filters the date formatted based on the locale.
	 *
	 * @since 5.3.0
	 *
	 * @param string       $date      Formatted date string.
	 * @param string       $format    Format to display the date.
	 * @param int          $timestamp Unix timestamp.
	 * @param DateTimeZone $timezone  Timezone.
	 */
	$date = apply_filters( 'wp_date', $date, $format, $timestamp, $timezone );

	return $date;
}
endif;

if ( ! function_exists( 'wp_maybe_decline_date' ) ) :
/**
 * Determines if the date should be declined.
 *
 * If the locale specifies that month names require a genitive case in certain
 * formats (like 'j F Y'), the month name will be replaced with a correct form.
 *
 * @since 4.4.0
 * @since 5.4.0 The `$format` parameter was added.
 *
 * @global WP_Locale $wp_locale WordPress date and time locale object.
 *
 * @param string $date   Formatted date string.
 * @param string $format Optional. Date format to check. Default empty string.
 * @return string The date, declined if locale specifies it.
 */
function wp_maybe_decline_date( $date, $format = '' ) {
	global $wp_locale;

	// i18n functions are not available in SHORTINIT mode.
	if ( ! function_exists( '_x' ) ) {
		return $date;
	}

	/*
	 * translators: If months in your language require a genitive case,
	 * translate this to 'on'. Do not translate into your own language.
	 */
	if ( 'on' === _x( 'off', 'decline months names: on or off' ) ) {

		$months          = $wp_locale->month;
		$months_genitive = $wp_locale->month_genitive;

		/*
		 * Match a format like 'j F Y' or 'j. F' (day of the month, followed by month name)
		 * and decline the month.
		 */
		if ( $format ) {
			$decline = preg_match( '#[dj]\.? F#', $format );
		} else {
			// If the format is not passed, try to guess it from the date string.
			$decline = preg_match( '#\b\d{1,2}\.? [^\d ]+\b#u', $date );
		}

		if ( $decline ) {
			foreach ( $months as $key => $month ) {
				$months[ $key ] = '# ' . preg_quote( $month, '#' ) . '\b#u';
			}

			foreach ( $months_genitive as $key => $month ) {
				$months_genitive[ $key ] = ' ' . $month;
			}

			$date = preg_replace( $months, $months_genitive, $date );
		}

		/*
		 * Match a format like 'F jS' or 'F j' (month name, followed by day with an optional ordinal suffix)
		 * and change it to declined 'j F'.
		 */
		if ( $format ) {
			$decline = preg_match( '#F [dj]#', $format );
		} else {
			// If the format is not passed, try to guess it from the date string.
			$decline = preg_match( '#\b[^\d ]+ \d{1,2}(st|nd|rd|th)?\b#u', trim( $date ) );
		}

		if ( $decline ) {
			foreach ( $months as $key => $month ) {
				$months[ $key ] = '#\b' . preg_quote( $month, '#' ) . ' (\d{1,2})(st|nd|rd|th)?([-–]\d{1,2})?(st|nd|rd|th)?\b#u';
			}

			foreach ( $months_genitive as $key => $month ) {
				$months_genitive[ $key ] = '$1$3 ' . $month;
			}

			$date = preg_replace( $months, $months_genitive, $date );
		}
	}

	// Used for locale-specific rules.
	$locale = get_locale();

	if ( 'ca' === $locale ) {
		// " de abril| de agost| de octubre..." -> " d'abril| d'agost| d'octubre..."
		$date = preg_replace( '# de ([ao])#i', " d'\\1", $date );
	}

	return $date;
}
endif;

if ( ! function_exists( 'wp_timezone' ) ) :
/**
 * Retrieves the timezone from site settings as a `DateTimeZone` object.
 *
 * Timezone can be based on a PHP timezone string or a ±HH:MM offset.
 *
 * @since 5.3.0
 *
 * @return DateTimeZone Timezone object.
 */
function wp_timezone() {
	return new DateTimeZone( wp_timezone_string() );
}
endif;
