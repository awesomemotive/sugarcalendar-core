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
	 * @return boolean|static
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
	 * @return boolean
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
}
