<?php
/**
 * Time Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the current time, according to the very beginning of the current page
 * being loaded.
 *
 * This is a more precise & predictable version of the current_time() function
 * included with WordPress.
 *
 * The 'mysql' type will return the time in the format for MySQL DATETIME field.
 *
 * The 'timestamp' type will return the current timestamp.
 *
 * Other strings will be interpreted as PHP date formats (e.g. 'Y-m-d').
 *
 * If $gmt is set to either '1' or 'true', then both types will use GMT time.
 *
 * If $gmt is false, the output is adjusted with the GMT offset in the WordPress option.
 *
 * @since 2.0.3
 *
 * @param string $type     Type of time to retrieve. Accepts 'mysql',
 *                         'timestamp', or PHP date format string (e.g. 'Y-m-d').
 * @param string $timezone Optional. Whether to use GMT time zone. Default false.
 *
 * @return int|string Integer if $type is 'timestamp', string otherwise.
 */
function sugar_calendar_get_request_time( $type = 'timestamp', $timezone = 'UTC' ) {
	global $timestart;

	// Should never be empty, but just in case...
	if ( empty( $timestart ) ) {
		$timestart = microtime( true );
	}

	// Get the offset if not UTC
	if ( 'UTC' !== $timezone ) {
		$offset = sugar_calendar_get_timezone_offset( array(
			'time'     => (int) $timestart,
			'timezone' => $timezone,
			'format'   => 'seconds'
		) );
	}

	// What type of
	switch ( $type ) {

		// 'Y-m-d H:i:s'
		case 'mysql' :
		case 'Y-m-d H:i:s' :
			$retval = ( 'UTC' === $timezone )
				? gmdate( 'Y-m-d H:i:s' )
				: gmdate( 'Y-m-d H:i:s', ( $timestart + $offset ) );
			break;

		// Unix timestamp
		case 'timestamp' :
		case '' :
			$retval = ( 'UTC' === $timezone )
				? $timestart
				: $timestart + $offset;
			break;

		// Mixed string
		default :
			$retval = ( 'UTC' === $timezone )
				? gmdate( $type )
				: gmdate( $type, ( $timestart + $offset ) );
			break;
	}

	// Return
	return $retval;
}

/**
 * Get a human readable representation of the time elapsed since a given date.
 *
 * Based on function created by Dunstan Orchard - http://1976design.com
 *
 * This function will return a human-readable representation of the time elapsed
 * since a given date.
 * - 2 hours and 50 minutes
 * - 4 days
 * - 4 weeks and 6 days
 *
 * Note that fractions of minutes are not represented in the return string. So
 * an interval of 3 minutes will be represented by "3 minutes", as will an
 * interval of 3 minutes 59 seconds.
 *
 * @param int|string $older_date The earlier time from which you're calculating
 *                               the time elapsed. Enter either as an integer Unix timestamp,
 *                               or as a date string of the format 'Y-m-d H:i:s'.
 * @param int|bool   $newer_date Optional. Unix timestamp of date to compare older
 *                               date to. Default: false (current time).
 *
 * @return string String representing the time since the older date -
 *         "2 hours and 50 minutes".
 */
function sugar_calendar_human_diff_time( $older_date, $newer_date = false ) {

	// Format start if not numeric
	if ( ! is_numeric( $older_date ) ) {
		$older_date = strtotime( $older_date );
	}

	// Format end if not numeric
	if ( ! is_numeric( $newer_date ) ) {
		$newer_date = strtotime( $newer_date . ' +1 second' );
	}

	// Catch issues with flipped old vs. new dates
	$flipped = false;

	// array of time period chunks
	$chunks = array(
		YEAR_IN_SECONDS,
		30 * DAY_IN_SECONDS,
		WEEK_IN_SECONDS,
		DAY_IN_SECONDS,
		HOUR_IN_SECONDS,
		MINUTE_IN_SECONDS,
		1
	);

	if ( ! empty( $older_date ) && ! is_numeric( $older_date ) ) {
		$time_chunks = explode( ':', str_replace( ' ', ':', $older_date ) );
		$date_chunks = explode( '-', str_replace( ' ', '-', $older_date ) );
		$older_date  = gmmktime(
			(int) $time_chunks[1],
			(int) $time_chunks[2],
			(int) $time_chunks[3],
			(int) $date_chunks[1],
			(int) $date_chunks[2],
			(int) $date_chunks[0]
		);
	}

	/**
	 * $newer_date will equal false if we want to know the time elapsed between
	 * a date and the current time. $newer_date will have a value if we want to
	 * work out time elapsed between two known dates.
	 */
	$newer_date = empty( $newer_date )
		? sugar_calendar_get_request_time()
		: $newer_date;

	// Difference in seconds
	$since = $newer_date - $older_date;

	// Flipped
	if ( $since < 0 ) {
		$flipped = true;
		$since   = $older_date - $newer_date;
	}

	// Step one: the first chunk
	for ( $i = 0, $j = count( $chunks ); $i < $j; ++$i ) {
		$seconds = $chunks[$i];

		// Finding the biggest chunk (if the chunk fits, break)
		$count = floor( $since / $seconds );
		if ( 0 != $count ) {
			break;
		}
	}

	// Set output var
	switch ( $seconds ) {
		case YEAR_IN_SECONDS :
			$output = sprintf( _n( '%s year',   '%s years',   $count, 'sugar-calendar' ), $count );
			break;
		case 30 * DAY_IN_SECONDS :
			$output = sprintf( _n( '%s month',  '%s months',  $count, 'sugar-calendar' ), $count );
			break;
		case WEEK_IN_SECONDS :
			$output = sprintf( _n( '%s week',   '%s weeks',   $count, 'sugar-calendar' ), $count );
			break;
		case DAY_IN_SECONDS :
			$output = sprintf( _n( '%s day',    '%s days',    $count, 'sugar-calendar' ), $count );
			break;
		case HOUR_IN_SECONDS :
			$output = sprintf( _n( '%s hour',   '%s hours',   $count, 'sugar-calendar' ), $count );
			break;
		case MINUTE_IN_SECONDS :
			$output = sprintf( _n( '%s minute', '%s minutes', $count, 'sugar-calendar' ), $count );
			break;
		default:
			$output = sprintf( _n( '%s second', '%s seconds', $count, 'sugar-calendar' ), $count );
	}

	// Step two: the second chunk
	// A quirk in the implementation means that this
	// condition fails in the case of minutes and seconds.
	// We've left the quirk in place, since fractions of a
	// minute are not a useful piece of information for our
	// purposes
	if ( $i + 2 < $j ) {
		$seconds2 = $chunks[$i + 1];
		$count2   = floor( ( $since - ( $seconds * $count ) ) / $seconds2 );

		// Add to output var
		if ( 0 != $count2 ) {
			$output .= esc_html_x( ',', 'Separator in time since', 'sugar-calendar' ) . ' ';

			switch ( $seconds2 ) {
				case 30 * DAY_IN_SECONDS :
					$output .= sprintf( _n( '%s month',  '%s months',  $count2, 'sugar-calendar' ), $count2 );
					break;
				case WEEK_IN_SECONDS :
					$output .= sprintf( _n( '%s week',   '%s weeks',   $count2, 'sugar-calendar' ), $count2 );
					break;
				case DAY_IN_SECONDS :
					$output .= sprintf( _n( '%s day',    '%s days',    $count2, 'sugar-calendar' ), $count2 );
					break;
				case HOUR_IN_SECONDS :
					$output .= sprintf( _n( '%s hour',   '%s hours',   $count2, 'sugar-calendar' ), $count2 );
					break;
				case MINUTE_IN_SECONDS :
					$output .= sprintf( _n( '%s minute', '%s minutes', $count2, 'sugar-calendar' ), $count2 );
					break;
				default:
					$output .= sprintf( _n( '%s second', '%s seconds', $count2, 'sugar-calendar' ), $count2 );
			}
		}
	}

	if ( true === $flipped ) {
		$output = '-' . $output;
	}

	/**
	 * Filters the human readable representation of the time elapsed since a
	 * given date.
	 *
	 * @since 2.0.0
	 *
	 * @param string $output     Final string
	 * @param string $older_date Earlier time from which we're calculating time elapsed
	 * @param string $newer_date Unix timestamp of date to compare older time to
	 */
	return apply_filters( 'sugar_calendar_human_diff_time', $output, $older_date, $newer_date );
}

/**
 * Return array of recurrence types
 *
 * @since 2.0
 *
 * @return array
 */
function sugar_calendar_get_recurrence_types() {
	static $retval = null;

	// Store statically to avoid thrashing gettext
	if ( null === $retval ) {
		$retval = apply_filters( 'sugar_calendar_get_recurrence_types', array(
			'daily'   => esc_html__( 'Daily',   'sugar-calendar' ),
			'weekly'  => esc_html__( 'Weekly',  'sugar-calendar' ),
			'monthly' => esc_html__( 'Monthly', 'sugar-calendar' ),
			'yearly'  => esc_html__( 'Yearly',  'sugar-calendar' )
		) );
	}

	// Return
	return $retval;
}

/**
 * Get the clock type, based on the user's preference and the site setting.
 *
 * Future versions of this may be able to guess a better default based on the
 * time zone or locale.
 *
 * @since 2.0.19
 *
 * @return string
 */
function sugar_calendar_get_clock_type() {

	// Get user time format preference
	$pref = sugar_calendar_get_user_preference( 'sc_time_format', 'g:i a' );

	// Base clock type on time format preference
	$retval = strstr( strtolower( $pref ), 'a' )
		? '12'
		: '24';

	// Filter & return
	return apply_filters( 'sugar_calendar_get_clock_type', $retval, $pref );
}

/**
 * Return array of hours
 *
 * @since 2.0.0
 *
 * @return array
 */
function sugar_calendar_get_hours() {

	// Get the clock type
	$clock = sugar_calendar_get_clock_type();

	// 12 hour clock
	if ( '12' === $clock ) {
		$retval = array(
			'01',
			'02',
			'03',
			'04',
			'05',
			'06',
			'07',
			'08',
			'09',
			'10',
			'11',
			'12'
		);

	// 24 hour clock
	} else {
		$retval = array(
			'00',
			'01',
			'02',
			'03',
			'04',
			'05',
			'06',
			'07',
			'08',
			'09',
			'10',
			'11',
			'12',
			'13',
			'14',
			'15',
			'16',
			'17',
			'18',
			'19',
			'20',
			'21',
			'22',
			'23'
		);
	}

	// Filter & return
	return (array) apply_filters( 'sugar_calendar_get_hours', $retval, $clock );
}

/**
 * Return array of minutes
 *
 * @since 2.0.0
 *
 * @return array
 */
function sugar_calendar_get_minutes() {

	// Filter & return
	return (array) apply_filters( 'sugar_calendar_get_minutes', array(
		'00',
		'05',
		'10',
		'15',
		'20',
		'25',
		'30',
		'35',
		'40',
		'45',
		'50',
		'55'
	) );
}

/**
 * Output a select dropdown for hours & minutes
 *
 * @since 2.0.0
 *
 * @param array $args
 */
function sugar_calendar_time_dropdown( $args = array() ) {

	// Parse the arguments
	$r = wp_parse_args( $args, array(
		'first'       => esc_html( 'Select One', 'sugar-calendar' ),
		'placeholder' => '&nbsp;',
		'id'          => '',
		'name'        => '',
		'class'       => 'sc-select-chosen sc-time',
		'items'       => array(),
		'selected'    => '',
		'multi'       => false,
		'echo'        => true,
		'width'       => 55
	) );

	// Is multi?
	$multi = ( true === $r['multi'] )
		? 'multi'
		: '';

	// Start an output buffer
	ob_start();

	// Start the select wrapper
	?><select data-placeholder="<?php echo esc_html( $r['placeholder'] ); ?>" name="<?php echo esc_attr( $r['name'] ); ?>" id="<?php echo esc_attr( $r['id'] ); ?>" class="<?php echo esc_attr( $r['class'] ); ?>" <?php echo $multi; ?> style="width: <?php echo esc_attr( $r['width'] ); ?>px;" ><?php

		// First item?
		if ( false !== $r['first'] ) : ?><option value=""><?php echo esc_html( $r['first'] ); ?></option><?php endif;

		// Loop through items
		foreach ( $r['items'] as $item ) :

			?><option value="<?php echo esc_attr( $item ); ?>" <?php selected( $r['selected'], $item ); ?>><?php echo esc_html( $item ); ?></option>

		<?php

		endforeach;

	?></select><?php

	// Get the current output buffer
	$retval = ob_get_clean();

	// Output or return
	if ( ! empty( $r['echo'] ) ) {
		echo $retval;
	}

	// Return
	return $retval;
}

/**
 * An strtotime() compatible minimum event duration. Default empty.
 *
 * Filter this to never allow an event to be shorter than a specific duration.
 *
 * Examples:
 * - "30 minutes"
 * - "1 hour"
 * - "1 day"
 *
 * @since 2.0.0
 *
 * @return string
 */
function sugar_calendar_get_minimum_event_duration() {
	return apply_filters( 'sugar_calendar_get_minimum_event_duration', '' );
}

/**
 * Convert a PHP numerical day number to an iCalendar two-letter one.
 *
 * @since 2.2.0
 *
 * @param int $day
 * @return string
 */
function sugar_calendar_daynum_to_ical( $day = 0 ) {

	// Default return value
	$retval = 'SU';

	// Day within boundaries
	if ( ( $day >= 0 ) && ( $day <= 6 ) ) {
		$allowed = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );
		$retval  = $allowed[ $day ];
	}

	// Return
	return $retval;
}
