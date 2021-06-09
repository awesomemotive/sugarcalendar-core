<?php
/**
 * DateTime Extension
 *
 * @package Plugins/Site/Events/DateTime
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Extend core DateTime class to add some useful customizations.
 *
 * @since 2.2.0
 */
class DateTime extends \DateTime {

	/**
	 * Create a DateTime object from a specific format.
	 *
	 * @since 2.2.0
	 *
	 * @param string $format
	 * @param string $time
	 * @param DateTimeZone $tzo
	 *
	 * @return bool|static
	 */
	public static function createFromFormat( $format = '', $time = '', $tzo = null ) {

		// Default return value
		$retval = new static();

		// Create from parent
		$parent = parent::createFromFormat( $format, $time, $tzo );

		// Bail if create failed
		if ( empty( $parent ) ) {
			return false;
		}

		// Set the timestamp
		$retval->setTimestamp( $parent->getTimestamp() );

		// Return
		return $retval;
	}

	/**
	 * Overrides DateTime::format() to add support for day-of-week ordinals.
	 *
	 * @since 2.2.0
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function format( $format = '' ) {

		// What format?
		switch ( $format ) {

			// Second Thursday = 2
			case 'q' :
				return $this->get_dow_ordinal_from_start_of_month();

			// Second to last Thursday = 2
			case 'Q' :
				return $this->get_dow_ordinal_from_end_of_month();

			// Everything else
			default :
				return parent::format( $format );
		}
	}

	/**
	 * Get the int of a day-of-week ordinal
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	private function get_dow_ordinal_from_start_of_month() {

		// Get the day
		$day = (int) $this->format( 'j' );

		// Return the ordinal of the day
		return (int) ceil( $day / 7 );
	}

	/**
	 * Is a year/month/day the last of its day-of-week
	 *
	 * @since 2.2.0
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 *
	 * @return bool
	 */
	private function get_dow_ordinal_from_end_of_month() {

		// Get the day
		$day  = $this->format( 'j' );

		// Get number of days in this month
		$days = (int) gmdate( 't', $this->getTimestamp() );

		// Is this day one of the final 7 in this month?
		$retval = (int) ceil( ( $days - 7 ) / $day );

		// Return
		return $retval;
	}

	/**
	 * Iterate forward, adding a DateInterval.
	 *
	 * @since 2.2.0
	 */
	public function advance( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'frequency'  => '',
			'interval'   => '',
			'bymonthday' => '',
			'byday'      => ''
		) );

		// Bail if no recurring frequency
		if ( empty( $r['frequency'] ) ) {
			return $this;
		}

		// Bail if no interval
		if ( empty( $r['interval'] ) ) {
			$r['interval'] = 1;
		}

		// Default to not advancing
		$advance = false;

		// No negative intervals
		$interval = abs( $r['interval'] );

		// Adjust by frequency
		switch ( strtolower( $r['frequency'] ) ) {

			// YEARLY
			case 'yearly' :
				$advance = "P{$interval}Y";
				break;

			// MONTHLY
			case 'monthly' :

				// Get the Day, Month, and Year
				$d = $this->format( 'd' );
				$m = $this->format( 'm' );
				$y = $this->format( 'Y' );

				// Less than or equal to the 28th day
				if ( $d <= 28 ) {
					$advance = "P{$interval}M";

				// Day part is defined by rrule
				} elseif ( ! empty( $r['bymonthday'] ) || ! empty( $r['byday'] ) ) {
					$this->setDate( $y, $m + $interval, 28 );

				// Greater than 28 - move day to a safe position
				} else {
					$n = 1;

					// Copy for looping
					$to_loop = clone $this;

					// Set the time to midnight
					$to_loop->setTime( 0, 0, 0 );

					// Skip subsequent months with less than $d days
					while ( $d > $to_loop->setDate( $y, ( $m + $interval * $n ), 1 )->format( 't' ) ) {
						$n++;
					}

					// Clean up
					unset( $to_loop );

					// Get return value
					$advance = "P{$n}M";
				}
				break;

			// WEEKLY
			case 'weekly' :
				$advance = "P{$interval}W";
				break;

			// DAILY
			case 'daily' :
				$advance = "P{$interval}D";
				break;

			// HOURLY
			case 'hourly' :
				$advance = "PT{$interval}H";
				break;

			// MINUTELY
			case 'minutely' :
				$advance = "PT{$interval}M";
				break;

			// SECONDLY
			case 'secondly' :
				$advance = "PT{$interval}S";
				break;
		}

		// Maybe advance
		if ( ! empty( $advance ) ) {
			$this->add( new \DateInterval( $advance ) );
		}

		// Return
		return $this;
	}
}
