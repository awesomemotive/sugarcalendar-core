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
	 * The start date & time as a DateTime object
	 *
	 * @since 2.2.0
	 * @var DateTime
	 */
	public $start_dto = null;

	/**
	 * The end date & time for the event.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string Date in MySQL's datetime format.
	 */
	public $end = '0000-00-00 00:00:00';

	/**
	 * The end date & time as a DateTime object
	 *
	 * @since 2.2.0
	 * @var DateTime
	 */
	public $end_dto = null;

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
	 * The recurrence end date & time as a DateTime object
	 *
	 * @since 2.2.0
	 * @var DateTime
	 */
	public $recurrence_end_dto = null;

	/**
	 * Construct
	 * @since 2.2.0
	 *
	 * @param mixed Null by default, or Array/Object
	 */
	public function __construct( $item = null ) {
		parent::__construct( $item );

		// Set all of the DateTime objects
		$this->set_datetime_objects();

		/**
		 * Filter the entire Event on construct
		 *
		 * This filter is handy when you need to automatically hoist parameters
		 * onto an Event object whenever it is created.
		 *
		 * @since 2.2.0
		 */
		apply_filters_ref_array( 'sugar_calendar_event_construct', array( &$this ) );
	}

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
	 * @return bool
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
	 * @param string $format
	 * @return bool
	 */
	public function is_multi( $format = 'j' ) {

		// Helpers for not remembering date() formatting
		switch ( $format ) {

			// Hour
			case 'hour' :
				$format = 'G';
				break;

			// Day
			case 'day' :
				$format = 'j';
				break;

			// Week
			case 'week' :
				$format = 'W';
				break;

			// Month
			case 'month' :
				$format = 'n';
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
	 * @deprecated 2.1.2 Use intersects() with DateTime objects instead
	 *
	 * @param int    $start    Unix timestamp
	 * @param int    $end      Unix timestamp
	 * @param string $mode     day|week|month|year
	 * @param string $timezone Default null. Olson time zone ID.
	 *
	 * @return bool
	 */
	public function overlaps( $start = '', $end = '', $mode = 'month', $timezone = null ) {

		// Bail if start or end are empty
		if ( empty( $start ) || empty( $end ) ) {
			return false;
		}

		// Turn datetimes to timestamps for easier comparisons
		$start_dto = sugar_calendar_get_datetime_object( $start, $timezone );
		$end_dto   = sugar_calendar_get_datetime_object( $end,   $timezone );

		// Call intersects
		$retval = $this->intersects( $start_dto, $end_dto );

		// Filter and return
		return (bool) apply_filters( 'sugar_calendar_event_overlaps', $retval, $this, $start, $end, $mode, $timezone );
	}

	/**
	 * Does an Event intersect some Start & End DateTime boundaries?
	 *
	 * @since 2.1.2
	 *
	 * @param DateTime $start Start boundary, with desired time zone
	 * @param DateTime $end   End boundary, with desired time zone
	 *
	 * @return bool True if intersects, False if not
	 */
	public function intersects( $start = '', $end = '' ) {

		// Default return value
		$retval = false;

		// Bail if start or end are empty
		if ( empty( $start ) || empty( $end ) ) {
			return $retval;
		}

		// Default to time zones for boundaries (to "float")
		$start_tz = $start->getTimezone();
		$end_tz   = $end->getTimezone();

		// Maybe adjust start time zone (loose comparison)
		$start_dto = ( $start_tz != $this->start_dto->getTimezone() )
			? sugar_calendar_set_datetime_timezone( $this->start_dto, $start_tz )
			: $this->start_dto;

		// Maybe adjust end time zone (loose comparison)
		$end_dto   = ( $end_tz != $this->end_dto->getTimezone() )
			? sugar_calendar_set_datetime_timezone( $this->end_dto, $end_tz )
			: $this->end_dto;

		// Boundary fits inside current cell
		if ( ( $end_dto <= $end ) && ( $start_dto >= $start ) ) {
			$retval = true;

		// Boundary fits outside current cell
		} elseif ( ( $end_dto >= $start ) && ( $start_dto <= $end ) ) {
			$retval = true;
		}

		// Filter and return
		return (bool) apply_filters( 'sugar_calendar_event_intersects', $retval, $this, $start, $end );
	}

	/**
	 * Return if a datetime value is "empty" or "0000-00-00 00:00:00".
	 *
	 * @since 2.0.0
	 *
	 * @param string $datetime
	 *
	 * @return bool
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
	 * @param string $format   Compatible with DateTime::format().
	 * @param string $timezone Used to offset from "start_tz".
	 * @return string
	 */
	public function start_date( $format = 'Y-m-d H:i:s', $timezone = null ) {
		return $this->format_date( $format, $this->start, $this->start_tz, $timezone );
	}

	/**
	 * Return a part of the start datetime.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format   Compatible with DateTime::format().
	 * @param string $timezone Used to offset from "end_tz".
	 * @return string
	 */
	public function end_date( $format = 'Y-m-d H:i:s', $timezone = null ) {
		return $this->format_date( $format, $this->end, $this->end_tz, $timezone );
	}

	/**
	 * Return a part of the start datetime.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format Compatible with DateTime::format().
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
	 * @param string $format    Compatible with DateTime::format().
	 * @param mixed  $timestamp Defaults to "now".
	 * @param string $timezone1 Defaults to time zone preference.
	 * @param string $timezone2 Used to offset from $timezone1.
	 * @param string $locale    Defaults to user/site preference.
	 *
	 * @return string
	 */
	public static function format_date( $format = 'Y-m-d H:i:s', $timestamp = null, $timezone1 = null, $timezone2 = null, $locale = null ) {
		return sugar_calendar_format_date_i18n( $format, $timestamp, $timezone1, $timezone2, $locale );
	}

	/**
	 * Set the DateTime objects
	 *
	 * @since 2.2.0
	 */
	public function set_datetime_objects() {

		// Default time zones
		$start_tz = $end_tz = $recurrence_end_tz = sugar_calendar_get_timezone();

		// All day checks simply match the boundaries
		if ( ! $this->is_all_day() && ! sugar_calendar_is_timezone_floating() ) {

			// Maybe use start time zone
			if ( ! empty( $this->start_tz ) ) {
				$start_tz = $this->start_tz;
			}

			// Maybe use end time zone
			if ( ! empty( $this->end_tz ) ) {
				$end_tz = $this->end_tz;
			}

			// Maybe use recurrence end time zone
			if ( ! empty( $this->recurrence_end_tz ) ) {
				$end_tz = $this->recurrence_end_tz;
			}
		}

		// Start
		if ( ! $this->is_empty_date( $this->start ) ) {
			$this->start_dto = sugar_calendar_get_datetime_object( $this->start, $start_tz );
		}

		// End
		if ( ! $this->is_empty_date( $this->end ) ) {
			$this->end_dto = sugar_calendar_get_datetime_object( $this->end, $end_tz );
		}

		// Recurrence end
		if ( ! $this->is_empty_date( $this->recurrence_end ) ) {
			$this->recurrence_end_dto = sugar_calendar_get_datetime_object( $this->recurrence_end, $recurrence_end_tz );
		}
	}
}
