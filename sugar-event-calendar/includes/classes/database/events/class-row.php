<?php
/**
 * Events Row Class.
 *
 * @package     Sugar Calendar
 * @subpackage  Database\Schemas
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
	 * Type of event.
	 *
	 * @since 3.0.0
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
	 * @since 3.0.0
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
	public $start = '';

	/**
	 * The time zone for the start date & time.
	 *
	 * @since 3.0.0
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
	public $end = '';

	/**
	 * The time zone for the end date & time.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $end_tz = '';

	/**
	 * All day.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var bool
	 */
	public $all_day = false;

	/**
	 * Type of event recurrence.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $recurrence = null;

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
	 * The recurrence end date and time in ISO 8601 date format.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var mixed null|DateTime
	 */
	public $recurrence_end = null;

	/**
	 * The time zone for the recurrence end date & time.
	 *
	 * @since 3.0.0
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
	 * This check includes recurrence.
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

		// Define the boundary parameters
		$boundaries = array(
			'start'         => $start,
			'end'           => $end,
			'start_year'    => date_i18n( 'Y', $start ),
			'start_month'   => date_i18n( 'm', $start ),
			'start_day'     => date_i18n( 'd', $start ),
			'start_dow'     => date_i18n( 'w', $start ),
			'start_doy'     => date_i18n( 'z', $start ),
			'start_woy'     => date_i18n( 'W', $start ),
			'start_hour'    => date_i18n( 'H', $start ),
			'start_minutes' => date_i18n( 'i', $start ),
			'start_seconds' => date_i18n( 's', $start ),
			'end_year'      => date_i18n( 'Y', $end ),
			'end_month'     => date_i18n( 'm', $end ),
			'end_day'       => date_i18n( 'd', $end ),
			'end_dow'       => date_i18n( 'w', $end ),
			'end_doy'       => date_i18n( 'z', $end ),
			'end_woy'       => date_i18n( 'W', $end ),
			'end_hour'      => date_i18n( 'H', $end ),
			'end_minutes'   => date_i18n( 'i', $end ),
			'end_seconds'   => date_i18n( 's', $end )
		);

		// Turn datetimes to timestamps for easier comparisons
		$item_start = $this->start_date( 'U' );
		$item_end   = $this->end_date( 'U' );
		$recur_end  = $this->is_empty_date( $this->recurrence_end )
			? false
			: $this->format_date( 'U', $this->recurrence_end );

		// Break it down
		$item_month     = $this->start_date( 'm' );
		$item_day       = $this->start_date( 'd' );
		$item_dow       = $this->start_date( 'w' );
		$item_woy       = $this->start_date( 'W' );
		$item_hour      = $this->start_date( 'H' );

		// Break it down
		$item_end_month = $this->end_date( 'm' );
		$item_end_day   = $this->end_date( 'd' );
		$item_end_dow   = $this->end_date( 'w' );
		$item_end_woy   = $this->end_date( 'W' );
		$item_end_hour  = $this->end_date( 'H' );

		// Bail if recurring ended after cell start (inclusive of last cell)
		if ( ! empty( $this->recurrence ) && ! empty( $recur_end ) && ( $start > $recur_end ) ) {
			return $retval;
		}

		// Boundary fits inside current cell
		if ( ( $item_end <= $end ) && ( $item_start >= $start ) ) {
			$retval = true;

		// Boundary fits outside current cell
		} elseif ( ( $item_end >= $start ) && ( $item_start <= $end ) ) {
			$retval = true;

		// Boundary does not fit, so must be recurring
		} elseif ( ! empty( $this->recurrence ) ) {
			$multi_year  = $this->is_multi( 'year'  );
			$multi_month = $this->is_multi( 'month' );
			$multi_week  = $this->is_multi( 'week'  );
			$multi_day   = $this->is_multi( 'day'   ); // Not used (yet)

			// Daily recurring
			if ( 'daily' === $this->recurrence ) {

				// Month mode
				if ( ( 'month' === $mode ) && ( $item_start <= $end ) ) {
					$retval = true;

				// Week mode
				} elseif ( ( 'week' === $mode ) && ( $item_hour <= $boundaries['end_hour' ] ) && ( $item_end_hour >= $boundaries['start_hour' ] ) ) {
					$retval = true;

				// Day mode
				} elseif ( ( 'day' === $mode ) && ( $item_hour <= $boundaries['end_hour' ] ) && ( $item_end_hour >= $boundaries['start_hour' ] ) ) {
					$retval = true;
				}

			// Weekly recurring
			} elseif ( 'weekly' === $this->recurrence ) {

				if (

						// Same week
						(
							( false === $multi_week )
							&&
							(
								// Starts before end
								( $item_dow <= $boundaries['end_dow'] )

								&&

								// Ends before start
								( $item_end_dow >= $boundaries['start_dow'] )

								&&

								// Same start month
								( $item_hour <= $boundaries['end_hour'] )

								&&

								// Same end month
								( $item_end_hour >= $boundaries['start_hour'] )

								&&

								// Not earlier week
								( $item_woy <= $boundaries['start_woy'] )

								&&

								// Not earlier week
								( $item_end_woy <= $boundaries['end_woy'] )
							)
						)

						||

						// Different weeks
						(
							( true === $multi_week )
							&&
							(
								// Before start & end
								(
									( $item_dow <= $boundaries['start_dow'] )
									&&
									( $item_end_dow <= $boundaries['end_dow'] )
									&&
									( $item_hour <= $boundaries['end_hour'] )
								)

								||

								// After end & start
								(
									( $item_dow >= $boundaries['end_dow'] )
									&&
									( $item_end_dow >= $boundaries['start_dow'] )
									&&
									( $item_end_hour >= $boundaries['end_hour'] )
								)
							)
						)
					) {
					$retval = true;
				}

			// Monthly recurring
			} elseif ( 'monthly' === $this->recurrence ) {

				if (
						// Same month
						(
							( false === $multi_month )
							&&
							(
								// Starts before end
								( $item_day <= $boundaries['end_day'] )

								&&

								// Ends before start
								( $item_end_day >= $boundaries['start_day'] )
							)
						)

						||

						// Different months
						(
							( true === $multi_month )
							&&
							(
								// Before start & end
								(
									( $item_day <= $boundaries['start_day'] )
									&&
									( $item_end_day <= $boundaries['end_day'] )
								)

								||

								// After end & start
								(
									( $item_day >= $boundaries['end_day'] )
									&&
									( $item_end_day >= $boundaries['start_day'] )
								)
							)
						)
					) {
					$retval = true;
				}

			// Yearly recurring
			} elseif ( 'yearly' === $this->recurrence ) {

				if (

						// Same year
						(
							( false === $multi_year )
							&&
							(
								// Starts before end
								( $item_day <= $boundaries['end_day'] )

								&&

								// Ends before start
								( $item_end_day >= $boundaries['start_day'] )

								&&

								// Same start month
								( $item_month === $boundaries['start_month'] )

								&&

								// Same end month
								( $item_end_month === $boundaries['end_month'] )
							)
						)

						||

						// Different years
						(
							( true === $multi_year )
							&&
							(
								// Before start & end
								(
									( $item_day <= $boundaries['start_day'] )
									&&
									( $item_end_day <= $boundaries['end_day'] )
									&&
									( $item_month === $boundaries['start_month'] )
								)

								||

								// After end & start
								(
									( $item_day >= $boundaries['end_day'] )
									&&
									( $item_end_day >= $boundaries['start_day'] )
									&&
									( $item_end_month === $boundaries['end_month'] )
								)
							)
						)

						||

						// Different months
						(
							( true === $multi_month )
							&&
							(
								// Before start & end
								(
									( $item_month === $boundaries['start_month'] )
									&&
									( $item_day === $boundaries['start_day'] )
								)

								||

								// After end & start
								(
									( $item_end_month === $boundaries['end_month'] )
									&&
									( $item_end_day === $boundaries['end_day'] )
								)
							)
						)
					) {
					$retval = true;
				}
			}
		}

		return (bool) $retval;
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
	 * Formate a datetime value.
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
		return date_i18n( $format, $date );
	}
}
