<?php
/**
 * Events Row Class.
 *
 * @package     Sugar Calendar
 * @subpackage  Database\Rows
 * @since       2.0
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Database\Row;

/**
 * Event Class
 *
 * @since 2.0.0
 */
final class Event extends Row {

	/**
	 * Event ID.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var int
	 */
	public $id;

	/**
	 * The ID of the event's object.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $object_id = 0;

	/**
	 * The type of object this Event is related to.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $object_type = 'post';

	/**
	 * The title for the event.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $title = '';

	/**
	 * The content for the event.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $content = '';

	/**
	 * The status of the event.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $status = '';

	/**
	 * The start date & time for the event.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string Date in MySQL's datetime format.
	 */
	public $start = '0000-00-00 00:00:00';

	/**
	 * The time zone for the start date & time.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $start_tz = '';

	/**
	 * The end date & time for the event.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string Date in MySQL's datetime format.
	 */
	public $end = '0000-00-00 00:00:00';

	/**
	 * The time zone for the end date & time.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $end_tz = '';

	/**
	 * The flag to specify if this Event spans the entire 24 hour period for any
	 * days that it happens to overlap, including recurrences.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var bool
	 */
	public $all_day = false;

	/**
	 * Type of event recurrence.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $recurrence = '';

	/**
	 * The recurrence interval, how often to recur.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var int
	 */
	public $recurrence_interval = 0;

	/**
	 * The recurrence count, how many times to recur.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var int
	 */
	public $recurrence_count = 0;

	/**
	 * The recurrence end date and time, when to stop recurring.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string Date in ISO 8601 date format.
	 */
	public $recurrence_end = '0000-00-00 00:00:00';

	/**
	 * The time zone for the recurrence end date & time.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string
	 */
	public $recurrence_end_tz = '';

	/**
	 * Issetter.
	 *
	 * @since 2.0.3
	 *
	 * @param string $key Property to check.
	 * @return bool True if set, False if not
	 */
	public function __isset( $key = '' ) {
		return (bool) $this->__get( $key );
	}

	/**
	 * Getter.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key Property to get.
	 * @return mixed Value of the property. Null if not available.
	 */
	public function __get( $key = '' ) {
		$retval = parent::__get( $key );

		// Check event meta
		if ( is_null( $retval ) ) {
			$retval = get_event_meta( $this->id, $key, true );
		}

		return $retval;
	}

	/**
	 * Return if event is an "all day" event.
	 *
	 * "All day" is classified either by the property, or the start & end being
	 * 00:00:00 and 23:59:59.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_all_day() {

		// Return true
		if ( ! empty( $this->all_day ) ) {
			return true;
		}

		// Get time for start & end
		$start = $this->start_date( 'H:i:s' );
		$end   = $this->end_date( 'H:i:s' );

		// Return whether start & end hour values are midnight & almost-midnight
		return (bool) ( ( '00:00:00' === $start ) && ( '23:59:59' === $end ) );
	}

	/**
	 * Return if start & end datetime parts do not match.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_multi( $format = 'd' ) {

		// Helpers for not remembering date() formatting
		switch ( $format ) {

			// Hour
			case 'hour' :
				$format = 'H';
				break;

			// Day
			case 'day' :
				$format = 'd';
				break;

			// Week
			case 'week' :
				$format = 'W';
				break;

			// Month
			case 'month' :
				$format = 'm';
				break;

			// Year
			case 'year' :
				$format = 'Y';
				break;
		}

		// Return if start & end do not match
		return (bool) ( $this->start_date( $format ) !== $this->end_date( $format ) );
	}

	/**
	 * Does an event overlap a specific start & end time?
	 *
	 * @since 2.0.1
	 *
	 * @param int    $start Unix timestamp
	 * @param int    $end   Unix timestamp
	 * @param string $mode  day|week|month|year
	 *
	 * @return bool
	 */
	public function overlaps( $start = '', $end = '', $mode = 'month' ) {

		// Default return value
		$retval = false;

		// Bail if start or end are empty
		if ( empty( $start ) || empty( $end ) ) {
			return $retval;
		}

		// Turn datetimes to timestamps for easier comparisons
		$item_start = $this->start_date( 'U' );
		$item_end   = $this->end_date( 'U' );

		// Boundary fits inside current cell
		if ( ( $item_end <= $end ) && ( $item_start >= $start ) ) {
			$retval = true;

		// Boundary fits outside current cell
		} elseif ( ( $item_end >= $start ) && ( $item_start <= $end ) ) {
			$retval = true;
		}

		// Filter and return
		return (bool) apply_filters( 'sugar_calendar_event_overlaps', $retval, $this, $start, $end, $mode );
	}

	/**
	 * Return if a datetime value is "empty" or "0000-00-00 00:00:00".
	 *
	 * @since 2.0.0
	 *
	 * @param string $datetime
	 *
	 * @return boolean
	 */
	public function is_empty_date( $datetime = '' ) {

		// Define the empty date
		$value = '0000-00-00 00:00:00';

		// Compare the various empties
		$empty     = empty( $datetime );
		$default   = ( $value === $datetime );
		$formatted = ( $value === $this->format_date( 'Y-m-d H:i:s', $datetime ) );

		// Return the conditions
		return $empty || $default || $formatted;
	}

	/**
	 * Return a part of the start datetime.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function start_date( $format = 'Y-m-d H:i:s' ) {
		return $this->format_date( $format, $this->start );
	}

	/**
	 * Return a part of the start datetime.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function end_date( $format = 'Y-m-d H:i:s' ) {
		return $this->format_date( $format, $this->end );
	}

	/**
	 * Return a part of the start datetime.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function recurrence_end_date( $format = 'Y-m-d H:i:s' ) {
		return $this->format_date( $format, $this->recurrence_end );
	}

	/**
	 * Format a datetime value.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format
	 * @param string $datetime
	 *
	 * @return string
	 */
	public static function format_date( $format = 'Y-m-d H:i:s', $datetime = '' ) {

		// Maybe format
		$date = is_string( $datetime )
			? strtotime( $datetime )
			: (int) $datetime;

		// Return date part
		return gmdate( $format, $date );
	}
}
