<?php
/**
 * Recurrence Utility
 *
 * @package Plugins/Site/Events/DateCollider
 */
namespace Sugar_Calendar\Utilities;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class for generating recurring Event sequences from a set of VEVENT and RRULE
 * parameters from the iCalendar RFC 5545 specification.
 *
 * See: https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
 */
class Recur {

	/** VEVENT parameters *****************************************************/

	protected $dtstart;
	protected $dtend;
	protected $rdate = array();
	protected $exdate = array();
	protected $duration;
	protected $tzid;

	/** RRULE parameters ******************************************************/

	protected $freq;
	protected $interval = 1;
	protected $count;
	protected $until;
	protected $bymonth = array();
	protected $byweekno = array();
	protected $byyearday = array();
	protected $bymonthday = array();
	protected $byday = array();
	protected $byhour = array();
	protected $byminute = array();
	protected $bysecond = array();
	protected $bysetpos = array();
	protected $wkst = 'MO';
	protected $wkst_seq = array( 'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU' );

	/** Boundaries ************************************************************/

	/**
	 * Start of range to compute dates in
	 *
	 * @var string
	 */
	protected $after;

	/**
	 * End of range to compute dates in
	 *
	 * @var string
	 */
	protected $before;

	/** Stashes ***************************************************************/

	/**
	 * DateTimeZone object
	 *
	 * @var DateTimeZone
	 */
	protected $timezone;

	/**
	 * DateTime object
	 *
	 * @var DateTime
	 */
	protected $datetime;

	/**
	 * Duration in seconds
	 *
	 * @var int
	 */
	protected $duration_time;

	/**
	 * All expansions
	 *
	 * @var array
	 */
	protected $expansions = array();

	/**
	 * All limitations
	 *
	 * @var array
	 */
	protected $limitations = array();

	/**
	 * Number of expansions set by rule
	 *
	 * @var int
	 */
	protected $expansion_count = 0;

	/**
	 * Number of iterations
	 *
	 * @var int
	 */
	protected $iteration = 0;

	/**
	 * Number of occurrences
	 *
	 * @var int
	 */
	protected $current_count = 0;

	/**
	 * Last computed date
	 *
	 * @var string
	 */
	protected $current_date;

	/** Caches ****************************************************************/

	/**
	 * Previously computed dates
	 *
	 * @var array
	 */
	protected $cached_dates = array();

	/**
	 * Dates from rdate rule
	 *
	 * @var array
	 */
	protected $cached_rdates = array();

	/**
	 * Cached year, month and week details
	 *
	 * @var array
	 */
	protected $cached_details = array();

	/** Settings **************************************************************/

	/**
	 * True if error in properties found
	 *
	 * @var bool
	 */
	public $error = false;

	/**
	 * Return format
	 *
	 * @var mixed
	 */
	public $format = 'Y-m-d H:i:s';

	/**
	 * Skip expanding of dates not in range to save time
	 *
	 * @var bool
	 */
	public $skip_not_in_range = false;

	/**
	 * Avoid infinite loops by breaking at this number.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $safety_break = 1000;

	/** Methods ***************************************************************/

	/**
	 * Constructor
	 *
	 * @param type $args
	 * @return type
	 */
	public function __construct( $args = array() ) {

		// Bail if no arguments to parse
		if ( ! $this->parse_args( $args ) ) {
			return;
		}

		// Validate arguments
		$this->validate_args();

		// Validate class properties from arguments
		$this->validate_properties();
	}

	/**
	 * Parse the arguments
	 *
	 * @param array $args
	 * @return bool
	 */
	protected function parse_args( $args = array() ) {

		// Bail if no arguments
		if ( empty( $args ) ) {
			$this->error = true;
			return false;
		}

		// Lowercase argument keys
		$args = $this->lc_keys( $args );

		// Default time zone
		$timezone = ! empty( $args[ 'tzid' ] )
			? $args[ 'tzid' ]
			: date_default_timezone_get();

		// Get time zone object
		try {
			$this->timezone = new \DateTimeZone( $timezone );

		// Bail on error
		} catch ( \Exception $e ) {
			$this->error = true;

			return false;
		}

		// Parse RRULE if present
		if ( ! empty( $args[ 'rrule' ] ) && strstr( $args[ 'rrule' ], '=' ) ) {

			// Explode by separator, trimming off trailing
			$rules = explode( ';', rtrim( $args[ 'rrule' ], ';' ) );

			// Successfully exploded
			if ( ! empty( $rules ) ) {

				// Loop through parts
				foreach ( $rules as $part ) {

					// Skip if invalid
					if ( ! strstr( $part, '=' ) ) {
						continue;
					}

					// Explode by equals
					list( $rule, $value ) = explode( '=', $part );

					// Lowercase the key
					$rule = strtolower( $rule );

					// Avoid unknown properties and duplicates
					if ( property_exists( $this, $rule ) && ! array_key_exists( $rule, $args ) ) {
						$args[ $rule ] = $value;
					}
				}
			}
		}

		// Loop through arguments
		foreach ( $args as $key => $value ) {

			// Skip empty string values
			if ( '' === (string) $value ) {
				continue;
			}

			// Validate specific args
			switch ( $key ) {

				// DATETIME || DATE values
				case 'dtstart' :
				case 'dtend' :
				case 'until' :
				case 'after' :
				case 'before' :

					// Create timestamps
					if ( false === ( $this->{$key} = $this->strtotime( $value ) ) ) {
						$this->error = true;
					}

					break;

				// RDATE
				case 'rdate' :

					// Make sure value is an array
					if ( ! is_array( $value ) ) {
						$value = array( $value );
					}

					// Loop through values
					foreach ( $value as $entry ) {

						// Explode by separator
						$rdate = explode( ',', $entry );

						// Loop through dates
						foreach ( $rdate as $property ) {

							// Explode by date & period
							list( $date, $period ) = explode( '/', $property );

							// Create timestamp
							if ( false === ( $start = $this->strtotime( $date ) ) ) {
								$this->error = true;
							}

							// Try to get duration
							if ( ! empty( $period ) ) {
								try {
									$duration = new \DateInterval( $period );

								// Evaluate 2nd part as duration
								} catch ( \Exception $e ) {
									$duration = null;

									// Evaluate 2nd part as datestring
									if ( false === ( $end = $this->strtotime( $period ) ) ) {
										$this->error = true;
									}
								}
							}

							// Add recur date to array
							$this->rdate[] = array(
								'start'    => $start,
								'end'      => $end,
								'duration' => $duration
							);
						}
					}

					break;

				// EXDATE
				case 'exdate' :

					// Make sure value is an array
					if ( ! is_array( $value ) ) {
						$value = array( $value );
					}

					// Loop through values
					foreach ( $value as $entry ) {

						// Explode by separator
						$exdate = explode( ',', $entry );

						// Loop through dates
						foreach ( $exdate as $datestring ) {

							// Create timestamps
							if ( false === ( $this->exdate[] = $this->strtotime( $datestring ) ) ) {
								$this->error = true;
							}
						}
					}

					break;

				// DURATION
				case 'duration' :

					// Try to get duration
					try {
						$this->duration = new \DateInterval( $value );
					} catch ( \Exception $e ) {
						$this->error = false;
					}

					break;

				// FREQUENCY
				case 'freq' :

					// Valid frequencies
					$valid = array( 'SECONDLY', 'MINUTELY', 'HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY' );

					// Invalid
					if ( ! in_array( $this->freq = strtoupper( $value ), $valid, true ) ) {
						$this->error = true;
					}

					break;

				// NUMERIC VALUES
				case 'count' :
				case 'interval' :

					// Count and interval cannot be negative
					if ( ! is_numeric( $this->{$key} = $value ) || ( $this->{$key} < 1 ) ) {
						$this->error = true;
					}

					break;

				// WKST
				case 'wkst' :
					$valid = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );

					if ( ! in_array( $this->wkst = strtoupper( $value ), $valid, true ) ) {
						$this->error = true;
					}

					break;

				// BYMONTH (1-12)
				case 'bymonth' :
					$this->bymonth = explode( ',', $value );

					foreach ( $this->bymonth as $month ) {
						if ( ! is_numeric( $month ) || ( abs( $month ) < 1 ) || ( abs( $month ) > 12 ) ) {
							$this->error = true;
						}
					}

					break;

				// BYWEEKNO (1-53)
				case 'byweekno' :
					$this->byweekno = explode( ',', $value );

					foreach ( $this->byweekno as $week ) {
						if ( ! is_numeric( $week ) || ( abs( $week ) < 1 ) || ( abs( $week ) > 53 ) ) {
							$this->error = true;
						}
					}

					break;

				// BYYEARDAY (1-366)
				case 'byyearday' :

				// BYSETPOS (1-366)
				case 'bysetpos' :
					$this->{$key} = explode( ',', $value );

					foreach ( $this->{$key} as $value ) {
						if ( ! is_numeric( $value ) || ( abs( $value ) < 1 ) || ( abs( $value ) > 366 ) ) {
							$this->error = true;
						}
					}

					break;

				// BYMONTHDAY (1-31)
				case 'bymonthday' :
					$this->bymonthday = explode( ',', $value );

					foreach ( $this->bymonthday as $day ) {
						if ( ! is_numeric( $day ) || ( abs( $day ) < 1 ) || ( abs( $day ) > 31 ) ) {
							$this->error = true;
						}
					}

					break;

				// BYDAY
				case 'byday' :
					$valid = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );

					foreach ( explode( ',', $value ) as $key ) {
						$weekday = substr( $key, -2 );
						$pos     = substr( $key, 0, -2 );

						$this->byday[] = array(
							'weekday' => $weekday,
							'pos'     => $pos
						);

						if ( ! in_array( $weekday, $valid, true ) || ( ( $pos !== '' ) && ( ! is_numeric( $pos ) || ( $pos === 0 ) ) ) ) {
							$this->error = true;
						}
					}

					break;

				// BYHOUR (0-23)
				case 'byhour' :
					$this->byhour = explode( ',', $value );

					foreach ( $this->byhour as $hour ) {
						if ( ! is_numeric( $hour ) || ( $hour < 0 ) || ( $hour > 23 ) ) {
							$this->error = true;
						}
					}

					break;

				// BYMINUTE (0-59)
				case 'byminute' :

				// BYSECOND (0-59)
				case 'bysecond' :
					$this->{$key} = explode( ',', $value );

					foreach ( $this->{$key} as $value ) {
						if ( ! is_numeric( $value ) || ( $value < 0 ) || ( $value > 59 ) ) {
							$this->error = true;
						}
					}

					break;
			}
		}

		// Bail if error
		if ( ! empty( $this->error ) ) {
			return false;
		}

		// Return
		return true;
	}

	/**
	 * Validate all class arguments.
	 */
	protected function validate_args() {

		// Synchronize until / before
		if ( ! is_null ( $this->until ) && ( is_null( $this->before ) || ( $this->before > $this->until ) ) ) {
			$this->before = $this->until;
		}

		// Disable skipping
		if ( ! is_null( $this->count ) || is_null( $this->after ) ) {
			$this->skip_not_in_range = false;
		}

		// End exists
		if ( ! is_null( $this->dtend ) ) {

			/**
			 * Calculate a nominal duration for anniversary or all-day events
			 * instead of an exact duration.
			 *
			 * The value type of dtstart is not recognized by this script, so
			 * events starting and ending on midnight are handled as all-day.
			 */
			if ( array( '00', '00', '00' ) === $this->explode( 'H-i-s', '-', $this->dtstart ) && array( '00', '00', '00' ) === $this->explode( 'H-i-s', '-', $this->dtend ) ) {
				$number_of_days = round( ( $this->dtend - $this->dtstart ) / 86400 );
				$this->duration = new \DateInterval( 'P' . $number_of_days . 'D' );
				$this->dtend    = null;

			// Set duration_time through simple subtraction
			} else {
				$this->duration_time = ( $this->dtend - $this->dtstart );
			}
		}

		// Recur date exists
		if ( ! empty( $this->rdate ) ) {

			// Create rdate cache
			foreach ( $this->rdate as $option ) {
				$this->cached_rdates[] = $option[ 'start' ];
			}

			// Sort rdates
			array_multisort( $this->cached_rdates, SORT_NUMERIC, $this->rdate );

			// Exclude exdates
			if ( ! empty( $this->exdate ) ) {
				$this->cached_rdates = array_diff( $this->cached_rdates, $this->exdate );
			}
		}

		// Set the current date from start
		$this->current_date = $this->dtstart;

		/**
		 * Define expansions to do, and limitations to apply.
		 *
		 * Conflicting rules are grouped, and results from grouped expansions
		 * will be intersected.
		 *
		 * I.E.: BYYEARDAY=1,5,10;BYMONTHDAY=1,10 will be expanded to January 1, 10
		 */
		switch ( $this->freq ) {
			case 'YEARLY' :
				$this->expansions  = array( array( 'bymonth' ), array( 'byweekno' ), array( 'byyearday', 'bymonthday', 'byday' ), array( 'byhour' ), array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array();
				break;

			case 'MONTHLY' :
				$this->expansions  = array( array( 'bymonthday', 'byday' ), array( 'byhour' ), array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array( 'bymonth' );
				break;

			case 'WEEKLY' :
				$this->expansions  = array( array( 'byday' ), array( 'byhour' ), array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array( 'bymonth' );
				break;

			case 'DAILY' :
				$this->expansions  = array( array( 'byhour' ), array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array( 'bymonth', 'bymonthday', 'byday' );
				break;

			case 'HOURLY' :
				$this->expansions  = array( array( 'byminute' ), array( 'bysecond' ) );
				$this->limitations = array( 'bymonth', 'byyearday', 'bymonthday', 'byday', 'byhour' );
				break;

			case 'MINUTELY' :
				$this->expansions  = array( array( 'bysecond' ) );
				$this->limitations = array( 'bymonth', 'byyearday', 'bymonthday', 'byday', 'byhour', 'byminute' );
				break;

			case 'SECONDLY' :
				$this->expansions  = array();
				$this->limitations = array( 'bymonth', 'byyearday', 'bymonthday', 'byday', 'byhour', 'byminute', 'bysecond' );
				break;
		}

		// Count expansion to do
		foreach ( $this->expansions as $expansion_set ) {
			foreach ( $expansion_set as $expansion ) {
				if ( ! empty( $this->{$expansion} ) ) {
					$this->expansion_count++;
				}
			}
		}

		// Set weekday order according to wkst
		while ( $this->wkst !== $this->wkst_seq[ 0 ] ) {
			$this->wkst_seq[] = array_shift( $this->wkst_seq );
		}
	}

	/**
	 * Used after validating arguments, this method looks at those results and
	 * performs additional validation and error checking.
	 *
	 * If an error is found, meaning that invalid properties have been set, this
	 * method sets the $error property to true.
	 */
	protected function validate_properties() {

		// I can't believe this works...
		switch ( true ) {

			// Start
			case ( is_null( $this->dtstart ) ) :

			// Duration & count
			case ( ! empty( $this->duration ) && ! is_null( $this->dtend ) ) :
			case ( ! empty( $this->count    ) && ! is_null( $this->until ) ) :

			// Timezone & Frequency
			case ( empty( $this->timezone ) ) :
			case ( empty( $this->freq     ) ) :

			// Duration time
			case ( ! is_null( $this->duration_time ) && ( 0 >= $this->duration_time ) ) :

			// By
			case ( ! empty( $this->byweekno   ) && ( 'YEARLY' !== $this->freq ) ) :
			case ( ! empty( $this->bymonthday ) && ( 'WEEKLY' === $this->freq ) ) :
			case ( ! empty( $this->byyearday  ) && in_array( $this->freq, array( 'DAILY', 'WEEKLY', 'MONTHLY' ), true ) ) :
				$this->error = true;
		}
	}

	/**
	 * Move on to the next iteration.
	 *
	 * @return mixed
	 */
	public function next() {

		// Bail if error
		if ( ! empty( $this->error ) ) {
			return false;
		}

		// Bail if max count reached
		if ( ! empty( $this->count ) && ( $this->current_count >= $this->count ) ) {
			return false;
		}

		// Return cached dates first
		if ( ! empty( $this->cached_dates ) ) {
			return $this->get_result();
		}

		// Interval out of range
		if ( ! empty( $this->before ) && ( $this->current_date >= $this->before ) ) {

			// Rdates exist so get that result
			if ( ! empty( $this->cached_rdates ) ) {
				return $this->get_result();
			}

			// Bail
			return false;
		}

		// Default break to 0
		$safety_break = 0;

		// Start the open loop
		do {
			$this->iteration++;

			// Stop after X number of failed iterations
			if ( ++$safety_break > $this->safety_break ) {
				break;
			}

			// Apply interval from 2nd iteration onward (dtstart is first date to be expanded)
			if ( 1 < $this->iteration ) {
				$this->next_interval();
			}

			// Bail if current date out of range
			if ( $this->out_of_range() ) {
				return false;
			}

			// Bail if current date not in range
			if ( ! empty( $this->skip_not_in_range ) && $this->not_in_range() ) {
				continue;
			}

			// Start expanding with current date
			$dates = array( $this->current_date );

			// No EXPANSIONS, apply LIMITATIONS only
			if ( empty( $this->expansion_count ) ) {

				// Restricted by exdate: next interval
				if ( ! empty( $this->exdate ) && in_array( $this->current_date, $this->exdate, true ) ) {
					continue;
				}

				// Loop through limitations
				foreach ( $this->limitations as $limitation ) {

					// Restricted by rule: next interval
					if ( ! empty( $this->{$limitation} ) && ! $this->{'limit_' . $limitation}( $this->current_date ) ) {
						continue 2;
					}
				}

			// Has EXPANSIONS, may have LIMITATIONS
			} else {

				// Loop through expansions
				foreach ( $this->expansions as $expansion_set ) {
					$expanded_dates = array();

					// Loop through dates
					foreach ( $dates as $date ) {
						$result_dates = array();

						// Compute dates for each expansion in set
						foreach ( $expansion_set as $expansion ) {
							if ( ! empty( $this->{$expansion} ) ) {
								$result_dates[] = $this->{'expand_' . $expansion}( $date );
							}
						}

						// No expansions done: continue with next set
						if ( empty( $result_dates ) ) {
							continue 2;
						}

						// Merge result
						$this->merge_set( $expanded_dates, $result_dates );
					}

					// Skip to next interval if no dates
					if ( ! $dates = $expanded_dates ) {
						continue 2;
					}
				}

				// Remove doubles
				$dates = array_unique( $dates );

				// Reset limited dates
				$limited_dates = array();

				// Loop through dates
				foreach ( $dates as $date ) {

					// LIMITATIONS
					foreach ( $this->limitations as $limitation ) {

						// Restricted by rule: continue with next date
						if ( ! empty( $this->{$limitation} ) && ! $this->{'limit_' . $limitation}( $date ) ) {
							continue 2;
						}
					}

					// Add date to limited dates
					$limited_dates[] = $date;
				}

				// Apply bysetpos
				if ( ! empty( $this->bysetpos ) ) {
					$this->limit_bysetpos( $limited_dates );
				}

				// Apply exdate
				if ( ! empty( $this->exdate ) ) {
					$limited_dates = array_diff( $limited_dates, $this->exdate );
				}

				// Skip to next interval if no dates
				if ( ! $dates = $limited_dates ) {
					continue;
				}

				// Reset limited dates
				$limited_dates = array();

				// Loop through dates
				foreach ( $dates as $date ) {

					// Check range
					if ( ! is_null( $this->before ) && ( $date > $this->before ) ) {
						continue;
					}

					// Skip if less than
					if ( $date < $this->dtstart ) {
						continue;
					}

					// Add date to limited dates
					$limited_dates[] = $date;
				}

				// Skip to next interval if no dates
				if ( ! $dates = $limited_dates ) {
					continue;
				}
			}

			// CACHE dates
			$this->cached_dates = $dates;

			// Get result
			return $this->get_result();

		// Keep the loop alive
		} while ( 1 );

		// Iteration stopped, but rdates left
		if ( ! empty( $this->cached_rdates ) ) {
			return $this->get_result();
		}

		// Return default
		return false;
	}

	/**
	 * Get the result of this current iteration.
	 *
	 * @return mixed
	 */
	protected function get_result() {

		// If dtstart is not synchronized with the rrule
		if (

			// Count is 0
			( 0 === $this->current_count )

			&&

			// dtstart may be dropped before, according to the RFC
			( $this->dtstart != $this->cached_dates[ 0 ] )

			&&

			// but it can be returned anyways
			( $this->dtstart != current( $this->cached_rdates ) )

			&&

			// dtstart is not excluded
			! in_array( $this->dtstart, $this->exdate, true )
		) {

			$start    =& $this->dtstart;
			$end      =& $this->dtend;
			$duration =& $this->duration;

		// Get RDATE
		} elseif (

			// Has cached recur dates
			! empty( $this->cached_rdates )

			&&

			(

				// Does not have cached dates
				empty( $this->cached_dates )

				||

				// Has less-or-equal recur dates than cached dates
				( current( $this->cached_rdates ) <= $this->cached_dates[ 0 ] )
			)
		) {

			// Get values
			$key           =      key( $this->cached_rdates );
			$start         =  current( $this->cached_rdates );
			$end           =& $this->rdate[ $key ][ 'end' ];
			$duration      =& $this->rdate[ $key ][ 'duration' ];
			$duration_time =& $this->duration_time;

			// Remove duplicate entry
			if ( $start == $this->cached_dates[ 0 ] ) {
				array_shift( $this->cached_dates );
			}

		// Get CACHED DATE
		} elseif ( ! is_null( $start = array_shift( $this->cached_dates ) ) ) {
			$duration      =& $this->duration;
			$duration_time =& $this->duration_time;

		// No values left: next interval
		} else {
			return $this->next();
		}

		// Not in range
		if ( ! is_null( $this->after ) && ( $start < $this->after ) ) {
			$this->current_count++;

			// Done, done, onto the next one...
			return $this->get_result();
		}

		// Create result using start
		$retval = array(
			'dtstart' => $this->date( $this->format, $start )
		);

		// Calculate dtend from DURATION
		if ( ! empty( $duration ) ) {
			$this->datetime = new \DateTime( '@' . $start );
			$this->datetime->setTimezone( $this->timezone );
			$this->datetime->add( $duration );

			$retval[ 'dtend' ] = $this->datetime->format( $this->format );

		// Calculate dtend from rdate end
		} elseif ( ! empty( $end ) ) {
			$retval[ 'dtend' ] = $this->date( $this->format, $end );

		// Calculate dtend from duration time
		} elseif ( ! empty( $duration_time ) ) {
			$retval[ 'dtend' ] = $this->date( $this->format, $start + $duration_time );
		}

		$retval[ 'recurrence-id' ] = gmdate( 'Ymd\THis\Z', $start );

		if ( empty( $this->skip_not_in_range ) && ( $this->current_count > 0 ) ) {
			$retval[ 'x-recurrence' ] = $this->current_count;
		}

		// Remove used rdate from cache
		if ( isset( $key ) ) {
			unset( $this->cached_rdates[ $key ] );
		}

		$this->current_count++;

		return $retval;
	}

	/**
	 * Used when iterating, this method bumps the current_date property forward
	 * by the interval, relative to the recurring frequency.
	 */
	protected function next_interval() {
		list( $Y, $m, $d, $H, $i, $s ) = $this->explode( 'Y-m-d-H-i-s', '-', $this->current_date );

		switch ( $this->freq ) {
			case 'YEARLY' :
				$this->current_date = $this->mktime( $H, $i, $s, $m, $d, $Y + $this->interval );
				break;

			case 'MONTHLY' :

				// Default: take day part from dtstart
				if ( $d <= 28 ) {
					$this->current_date = $this->mktime( $H, $i, $s, $m + $this->interval, $d, $Y );

				// Day part is defined by rule:
				} elseif ( ! empty( $this->bymonthday ) || ! empty( $this->byday ) ) {
					$this->current_date = $this->mktime( $H, $i, $s, $m + $this->interval, 28, $Y );

				// Move day to a safe position
				} else {
					$n = 1;

					// Skip months with fewer days
					while ( $d > $this->date( 't', $this->mktime( 0, 0, 0, $m + $this->interval * $n, 1, $Y ) ) ) {
						$n++;
						$this->iteration++;
					}
					$this->current_date = $this->mktime( $H, $i, $s, $m + $this->interval * $n, $d, $Y );
				}
				break;

			case 'WEEKLY' :
				$this->current_date = $this->mktime( $H, $i, $s, $m, $d + $this->interval * 7, $Y );
				break;

			case 'DAILY' :
				$this->current_date = $this->mktime( $H, $i, $s, $m, $d + $this->interval, $Y );
				break;

			case 'HOURLY' :
				$this->current_date = $this->mktime( $H + $this->interval, $i, $s, $m, $d, $Y );
				break;

			case 'MINUTELY' :
				$this->current_date = $this->mktime( $H, $i + $this->interval, $s, $m, $d, $Y );
				break;

			case 'SECONDLY' :
				$this->current_date = $this->mktime( $H, $i, $s + $this->interval, $m, $d, $Y );
				break;
		}
	}

	/** Ranges ****************************************************************/

	/**
	 * Used when iterating, this method checks if an Event is out of range.
	 *
	 * @return bool
	 */
	protected function out_of_range() {

		// Bail if no before
		if ( is_null( $this->before ) ) {
			return false;
		}

		// No expansions: check current date only
		if ( empty( $this->expansion_count ) && ( $this->current_date > $this->before ) ) {
			return true;
		}

		// Check range of possible expansions
		switch ( $this->freq ) {
			case 'YEARLY' :
				$year_details = $this->get_year_details( $this->date( 'Y', $this->current_date ) );

				if ( ! empty( $this->byweekno ) && $year_details[ 'week_start' ] < $year_details[ 'start' ] ) {
					$start = $year_details[ 'week_start' ];
				} else {
					$start = $year_details[ 'start' ];
				}

				if ( $start > $this->before ) {
					return true;
				}

				break;

			case 'MONTHLY' :
				list( $Y, $m ) = $this->explode( 'Y-m', '-', $this->current_date );

				$month_details = $this->get_month_details( $m, $Y );

				if ( $month_details[ 'start' ] > $this->before ) {
					return true;
				}

				break;

			case 'WEEKLY' :
				list( $Y, $z ) = $this->explode( 'Y-z', '-', $this->current_date );

				$week_details = $this->get_week_details( $z, $Y );

				if ( $week_details[ 'start' ] > $this->before ) {
					return true;
				}

				break;

			case 'DAILY' :
				list( $Y, $m, $d ) = $this->explode( 'Y-m-d', '-', $this->current_date );

				$day_start = $this->mktime( 0, 0, 0, $m, $d, $Y );

				if ( $day_start > $this->before ) {
					return true;
				}

				break;

			case 'HOURLY' :
				list( $Y, $m, $d, $H ) = $this->explode( 'Y-m-d-H', '-', $this->current_date );

				$hour_start = $this->mktime( $H, 0, 0, $m, $d, $Y );

				if ( $hour_start > $this->before ) {
					return true;
				}

				break;

			case 'MINUTELY' :
				list( $Y, $m, $d, $H, $i ) = $this->explode( 'Y-m-d-H-i', '-', $this->current_date );

				$minute_start = $this->mktime( $H, $i, 0, $m, $d, $Y );

				if ( $minute_start > $this->before ) {
					return true;
				}

				break;

			case 'SECONDLY' :
				if ( $this->current_date > $this->before ) {
					return true;
				}

				break;
		}
		return false;
	}

	/**
	 * Used when iterating, this method checks if an Event is not in range.
	 *
	 * @return bool
	 */
	protected function not_in_range() {

		// Bail if no after
		if ( is_null( $this->after ) ) {
			return false;
		}

		// No expansions: check current date only
		if ( empty( $this->expansion_count ) && ( $this->current_date < $this->after ) ) {
			return true;
		}

		// Check range of possible expansions
		switch ( $this->freq ) {
			case 'YEARLY' :
				$year_details = $this->get_year_details( $this->date( 'Y', $this->current_date ) );

				if ( ! empty( $this->byweekno ) && ( $year_details[ 'week_end' ] > $year_details[ 'end' ] ) ) {
					$end = $year_details[ 'week_end' ];
				} else {
					$end = $year_details[ 'end' ];
				}

				if ( $end < $this->after ) {
					return true;
				}

				break;

			case 'MONTHLY' :
				list( $Y, $m ) = $this->explode( 'Y-m', '-', $this->current_date );

				$month_details = $this->get_month_details( $m, $Y );

				if ( $month_details[ 'end' ] < $this->after ) {
					return true;
				}

				break;

			case 'WEEKLY' :
				list( $Y, $z ) = $this->explode( 'Y-z', '-', $this->current_date );

				$week_details = $this->get_week_details( $z, $Y );

				if ( $week_details[ 'end' ] < $this->after ) {
					return true;
				}

				break;

			case 'DAILY' :
				list( $Y, $m, $d ) = $this->explode( 'Y-m-d', '-', $this->current_date );

				$day_end = $this->mktime( 23, 59, 59, $m, $d, $Y );

				if ( $day_end < $this->after ) {
					return true;
				}

				break;

			case 'HOURLY' :
				list( $Y, $m, $d, $H ) = $this->explode( 'Y-m-d-H', '-', $this->current_date );

				$hour_end = $this->mktime( $H, 59, 59, $m, $d, $Y );

				if ( $hour_end < $this->after ) {
					return true;
				}

				break;

			case 'MINUTELY' :
				list( $Y, $m, $d, $H, $i ) = $this->explode( 'Y-m-d-H-i', '-', $this->current_date );

				$minute_end = $this->mktime( $H, $i, 59, $m, $d, $Y );

				if ( $minute_end < $this->after ) {
					return true;
				}

				break;

			case 'SECONDLY' :
				if ( $this->current_date < $this->after ) {
					return true;
				}

				break;
		}

		return false;
	}

	/**
	 * Merge two sets of arrays.
	 *
	 * @param array $expanded_dates
	 * @param array $set
	 */
	protected function merge_set( & $expanded_dates = array(), $set = array() ) {

		// Bail if nothing to merge
		if ( empty( $set ) ) {
			$expanded_dates = array();
			return;
		}

		// Get the first set to merge
		$merge = $set[ 0 ];

		// Loop through and get the intersections
		for ( $i = 1; $i < count( $set ); $i++ ) {
			$merge = array_intersect( $merge, $set[ $i ] );
		}

		// Merge sets
		$expanded_dates = array_merge( $expanded_dates, $merge );
	}

	/** Expanders *************************************************************/

	protected function expand_bymonth( $date ) {

		// Default return value
		$dates = array();

		list( $Y, $d, $H, $i, $s, $L ) = $this->explode( 'Y-d-H-i-s-L', '-', $date );

		// Last day for each month
		$limit = array( '', 31, $L
			? 29
			: 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

		// Day part is defined by rule: shifting allowed
		if ( ! empty( $this->byweekno ) || ! empty( $this->byyearday ) || ! empty( $this->bymonthday ) || ! empty( $this->byday ) ) {
			$shift = true;
		}

		// Create one date for each MONTH
		foreach ( $this->bymonth as $month ) {

			// Out of range
			if ( $d > $limit[ $month ] ) {

				// Shift day
				if ( ! empty( $shift ) ) {
					$d = $limit[ $month ];

				// Skip month
				} else {
					continue;
				}
			}

			// Add to dates
			$dates[] = $this->mktime( $H, $i, $s, $month, $d, $Y );
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	protected function expand_byweekno( $date ) {

		// Default return value
		$dates = array();

		list( $Y, $m, $H, $i, $s ) = $this->explode( 'Y-m-H-i-s', '-', $date );

		// Results are limited to current year
		$year[ $Y ] = $this->get_year_details( $Y );
		$start      = $year[ $Y ][ 'start' ];
		$end        = $year[ $Y ][ 'end' ];

		// Previous applied BYMONTH limits the result
		if ( ! empty( $this->bymonth ) ) {
			$start = $this->mktime( 0, 0, 0, $m,     1, $Y );
			$end   = $this->mktime( 0, 0, 0, $m + 1, 1, $Y ) - 1;
		}

		// Check results from overlapping years too
		if ( $year[ $Y ][ 'week_end' ] < $end )	{
			$year[ $Y + 1 ] = $this->get_year_details( $Y + 1 );

		} elseif ( $year[ $Y ][ 'week_start' ] > $start ) {
			$year[ $Y - 1 ] = $this->get_year_details( $Y - 1 );
		}

		// Day part NOT defined by rule: take weekday from dtstart
		if ( empty( $this->byyearday ) && empty( $this->bymonthday ) && empty( $this->byday ) ) {
			$days    = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );
			$weekday = $days[ $this->date( 'w', $this->dtstart ) ];

			// Position of the weekday (depends on wkst)
			$pos_weekday = array_search( $weekday, $this->wkst_seq, true );
		}

		foreach ( $year as $Y => $year_details ) {

			// Create one date for each WEEK
			foreach ( $this->byweekno as $week ) {

				// Week number out of range: skip week
				if ( abs( $week ) > $year_details[ 'number_of_weeks' ] ) {
					continue;
				}

				if ( $week < 0 ) {
					$week = $year_details[ 'number_of_weeks' ] + $week + 1;
				}

				// Weekday is specified by dtstart: test weekday only
				if ( isset( $pos_weekday ) ) {
					$test_date = $this->mktime( $H, $i, $s, 1, 1 + $year_details[ 'week_offset' ] + $pos_weekday + ( $week - 1 ) * 7, $Y );

					// Date out of range: skip week
					if ( $test_date < $start ) {
						continue;
					}

					if ( $test_date > $end ) {
						continue;
					}

				// Weekday is expanded later, find one valid day of the week
				} else {
					$pos = 0;

					do {

						// Out of range: skip week
						if ( $pos > 6 ) {
							continue 2;
						}

						$test_date = $this->mktime( $H, $i, $s, 1, 1 + $year_details[ 'week_offset' ] + $pos + ( $week - 1 ) * 7, $Y );

						if ( ( $test_date >= $start ) && ( $test_date <= $end ) ) {
							break;
						}

						$pos++;

					} while ( 1 );
				}

				// Add to dates
				$dates[] = $test_date;
			}
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	protected function expand_byyearday( $date ) {

		// Default return value
		$dates = array();

		list( $Y, $m, $z, $H, $i, $s, $L ) = $this->explode( 'Y-m-z-H-i-s-L', '-', $date );

		$number_of_days = $this->year_days( $L );

		// Results are limited to current year
		$start = $this->mktime( 0,  0,  0,  1,  1,  $Y );
		$end   = $this->mktime( 23, 59, 59, 12, 31, $Y );

		// Previous applied BYMONTH limits the result
		if ( ! empty( $this->bymonth ) ) {
			$month_details = $this->get_month_details( $m, $Y );

			$start = $month_details[ 'start' ];
			$end   = $month_details[ 'end' ];
		}

		// Previous applied BYWEEKNO limits the result
		if ( ! empty( $this->byweekno ) ) {
			$week_details = $this->get_week_details( $z, $Y );

			if ( $start < $week_details[ 'start' ] ) {
				$start = $week_details[ 'start' ];
			}

			if ( $end > $week_details[ 'end' ] ) {
				$end = $week_details[ 'end' ];
			}
		}

		// Create one date for each YEARDAY
		foreach ( $this->byyearday as $day ) {

			if ( $day < 0 ) {
				$day = $number_of_days + $day + 1;
			}

			$date = $this->mktime( $H, $i, $s, 1, $day, $Y );

			// Out of range: skip day
			if ( $date < $start ) {
				continue;
			}

			if ( $date > $end ) {
				continue;
			}

			// Add to dates
			$dates[] = $date;
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	protected function expand_bymonthday( $date ) {

		// Default return value
		$dates = array();

		list( $Y, $m, $z, $H, $i, $s ) = $this->explode( 'Y-m-z-H-i-s', '-', $date );

		$month[ $m ] = $this->get_month_details( $m, $Y );

		// Results are limited to current year
		$start = $this->mktime( 0, 0, 0, 1, 1, $Y );
		$end   = $this->mktime( 23, 59, 59, 12, 31, $Y );

		// Previous applied BYMONTH limits the result
		if ( ! empty( $this->bymonth ) ) {
			$start = $month[ $m ][ 'start' ];
			$end   = $month[ $m ][ 'end' ];
		}

		// Previous applied BYWEEKNO limits the result
		if ( ! empty( $this->byweekno ) ) {

			$week_details = $this->get_week_details( $z, $Y );

			if ( $start < $week_details[ 'start' ] ) {
				$start = $week_details[ 'start' ];
			}

			if ( $end > $week_details[ 'end' ] ) {
				$end = $week_details[ 'end' ];
			}

			// If no BYMONTH specified, consider overlapping months too
			if ( empty( $this->bymonth ) ) {

				if ( $month[ $m ][ 'end' ] < $end ) {
					$month[ $m + 1 ] = $this->get_month_details( $m + 1, $Y );

				} elseif ( $month[ $m ][ 'start' ] > $start ) {
					$month[ $m - 1 ] = $this->get_month_details( $m - 1, $Y );
				}
			}
		}

		foreach ( $month as $m => $month_details ) {

			// Create one date for each MONTHDAY
			foreach ( $this->bymonthday as $day ) {

				// Out of range: skip day
				if ( abs( $day ) > $month_details[ 'number_of_days' ] ) {
					continue;
				}

				if ( $day < 0 ) {
					$day = $month_details[ 'number_of_days' ] + $day + 1;
				}

				$date = $this->mktime( $H, $i, $s, $m, $day, $Y );

				// Out of range: skip day
				if ( $date < $start ) {
					continue;
				}

				if ( $date > $end ) {
					continue;
				}

				// Add to dates
				$dates[] = $date;
			}
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	protected function expand_byday( $date ) {

		// Default return value
		$dates = array();

		// Special expand for WEEKLY
		if ( ! empty( $this->byweekno ) || ( 'WEEKLY' === $this->freq ) ) {

			list( $Y, $m, $z, $H, $i, $s ) = $this->explode( 'Y-m-z-H-i-s', '-', $date );

			// Previous applied BYMONTH limits the result
			if ( ! empty( $this->bymonth ) ) {
				$month_details = $this->get_month_details( $m, $Y );

				$start = $month_details[ 'start' ];
				$end   = $month_details[ 'end' ];
			}

			$week_details = $this->get_week_details( $z, $Y );

			list( $Y, $m, $d ) = $this->explode( 'Y-m-d', '-', $week_details[ 'start' ] );

			// Apply BYDAY
			foreach ( $this->byday as $option ) {

				// Position & WEEKLY is invalid: skip day
				if ( ! empty( $option[ 'pos' ] ) ) {
					continue;
				}

				// Position of day from week start
				$pos  = array_search( $option[ 'weekday' ], $this->wkst_seq, true );
				$date = $this->mktime( $H, $i, $s, $m, $d + $pos, $Y );

				// Out of range: skip day
				if ( isset( $start ) && ( $date < $start ) ) {
					continue;
				}

				if ( isset( $end ) && ( $date > $end ) ) {
					continue;
				}

				// Add to dates
				$dates[] = $date;
			}

			// Sort dates numerically
			sort( $dates, SORT_NUMERIC );

			// Return sorted dates
			return $dates;
		}

		// Special expand for MONTHLY
		if ( 'MONTHLY' === $this->freq || $this->bymonth ) {

			list( $Y, $m, $H, $i, $s ) = $this->explode( 'Y-m-H-i-s', '-', $date );

			$month_details = $this->get_month_details( $m, $Y );

			// Apply BYDAY
			foreach ( $this->byday as $option ) {

				// Position of day from month start
				$pos      = array_search( $option[ 'weekday' ], $month_details[ 'start_seq' ] );
				$all_days = array();

				while ( $pos < $month_details[ 'number_of_days' ] ) {
					$all_days[] = $this->mktime( $H, $i, $s, $m, $pos + 1, $Y );
					$pos += 7;
				}

				if ( empty( $all_days ) ) {
					continue;
				}

				// No position - merge all days
				if ( empty( $option[ 'pos' ] ) ) {
					$dates = array_merge( $dates, $all_days );
					continue;
				}

				// Position out of range
				if ( abs( $option[ 'pos' ] ) > count( $all_days ) ) {
					continue;
				}

				// Merge day by position
				if ( $option[ 'pos' ] < 0 ) {
					$dates[] = $all_days[ count( $all_days ) + $option[ 'pos' ] ];
				} else {
					$dates[] = $all_days[ $option[ 'pos' ] - 1 ];
				}
			}

			// Sort dates numerically
			sort( $dates, SORT_NUMERIC );

			// Return sorted dates
			return $dates;
		}

		// Special expand for YEARLY
		if ( 'YEARLY' === $this->freq ) {

			list( $Y, $H, $i, $s ) = $this->explode( 'Y-H-i-s', '-', $date );

			$year_details = $this->get_year_details( $Y );

			// Apply BYDAY
			foreach ( $this->byday as $option ) {

				// Position of day from year start
				$pos      = array_search( $option[ 'weekday' ], $year_details[ 'start_seq' ] );
				$all_days = array();

				while ( $pos < $year_details[ 'number_of_days' ] ) {
					$all_days[] = $this->mktime( $H, $i, $s, 1, $pos + 1, $Y );
					$pos += 7;
				}

				if ( empty( $all_days ) ) {
					continue;
				}

				// No position - merge all days
				if ( empty( $option[ 'pos' ] ) ) {
					$dates = array_merge( $dates, $all_days );
					continue;
				}

				// Position out of range
				if ( abs( $option[ 'pos' ] ) > count( $all_days ) ) {
					continue;
				}

				// Merge day by position
				if ( $option[ 'pos' ] < 0 ) {
					$dates[] = $all_days[ count( $all_days ) + $option[ 'pos' ] ];
				} else {
					$dates[] = $all_days[ $option[ 'pos' ] - 1 ];
				}
			}

			// Sort dates numerically
			sort( $dates, SORT_NUMERIC );

			// Return sorted dates
			return $dates;
		}
	}

	protected function expand_byhour( $date ) {

		// Default return value
		$dates = array();

		list( $Y, $m, $d, $i, $s ) = $this->explode( 'Y-m-d-i-s', '-', $date );

		foreach ( $this->byhour as $hour ) {
			$dates[] = $this->mktime( $hour, $i, $s, $m, $d, $Y );
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	protected function expand_byminute( $date ) {
		$dates = array();

		list( $Y, $m, $d, $H, $s ) = $this->explode( 'Y-m-d-H-s', '-', $date );

		foreach ( $this->byminute as $minute ) {
			$dates[] = $this->mktime( $H, $minute, $s, $m, $d, $Y );
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	protected function expand_bysecond( $date ) {
		$dates = array();

		list( $Y, $m, $d, $H, $i ) = $this->explode( 'Y-m-d-H-i', '-', $date );

		foreach ( $this->bysecond as $second ) {
			$dates[] = $this->mktime( $H, $i, $second, $m, $d, $Y );
		}

		// Sort dates numerically
		sort( $dates, SORT_NUMERIC );

		// Return sorted dates
		return $dates;
	}

	/** Limits ****************************************************************/

	protected function limit_bymonth( $timestamp ) {
		if ( in_array( $this->date( 'n', $timestamp ), $this->bymonth, true ) ) {
			return true;
		}

		return false;
	}

	protected function limit_byyearday( $timestamp ) {
		list( $z, $L ) = $this->explode( 'z-L', '-', $timestamp );

		if ( in_array( $z + 1, $this->byyearday, true ) ) {
			return true;
		}

		$number_of_days = $this->year_days( $L );

		if ( in_array( $z - $number_of_days, $this->byyearday, true ) ) {
			return true;
		}

		return false;
	}

	protected function limit_bymonthday( $timestamp ) {
		list( $j, $t ) = $this->explode( 'j-t', '-', $timestamp );

		if ( in_array( $j, $this->bymonthday, true ) ) {
			return true;
		}

		if ( in_array( $j - $t - 1, $this->bymonthday, true ) ) {
			return true;
		}

		return false;
	}

	protected function limit_byday( $timestamp ) {

		// Recombine position & weekday
		foreach ( $this->byday as $option )	{
			$byday[] = $option[ 'pos' ] . $option[ 'weekday' ];
		}

		$days = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );

		list( $w, $j, $z, $t, $L ) = $this->explode( 'w-j-z-t-L', '-', $timestamp );

		// Check weekday without position
		if ( in_array( $days[ $w ], $byday ) ) {
			return true;
		}

		// Previous applied BYMONTH: check position relative to month
		if ( ! empty( $this->bymonth ) ) {
			$try = ceil( $j / 7 );

			if ( in_array( $try . $days[ $w ], $byday ) ) {
				return true;
			}

			$try = ceil( ( $t - $j + 1 ) / 7 ) * -1;

			if ( in_array( $try . $days[ $w ], $byday ) ) {
				return true;
			}

		// Check position relative to year
		} else {
			$number_of_days = $this->year_days( $L );

			$try = ceil( ( $z + 1 ) / 7 );

			if ( in_array( $try . $days[ $w ], $byday ) ) {
				return true;
			}

			$try = ceil( ( $number_of_days - $z ) / 7 ) * -1;

			if ( in_array( $try . $days[ $w ], $byday ) ) {
				return true;
			}
		}

		return false;
	}

	protected function limit_byhour( $timestamp ) {
		if ( in_array( $this->date( 'G', $timestamp ), $this->byhour ) ) {
			return true;
		}

		return false;
	}

	protected function limit_byminute( $timestamp ) {
		if ( in_array( (int) $this->date( 'i', $timestamp ), $this->byminute ) ) {
			return true;
		}

		return false;
	}

	protected function limit_bysetpos( & $limited_dates = array() ) {
		$num = count( $limited_dates );

		foreach ( $this->bysetpos as $pos ) {
			if ( $pos < 0 ) {
				$pos = $num + $pos + 1;
			}

			$result[] = $limited_dates[ $pos - 1 ];
		}

		$limited_dates = $result;
	}

	/** Details ***************************************************************/

	protected function get_week_details( $day, $year ) {

		// Return cached values
		if ( $this->cached_details[ 'week' ][ $year ][ $day ] )	{
			return $this->cached_details[ 'week' ][ $year ][ $day ];
		}

		$year_details = $this->get_year_details( $year );

		// Difference to 1st week in year
		$week_diff = floor( ( $day - $year_details[ 'week_offset' ]) / 7 );
		$start     = $this->mktime( 0,  0,  0,  1, 1 + $year_details[ 'week_offset' ] + $week_diff * 7,     $year );
		$end       = $this->mktime( 23, 59, 59, 1, 1 + $year_details[ 'week_offset' ] + $week_diff * 7 + 6, $year );

		// Setup the return value
		$retval = $this->cached_details[ 'week' ][ $year ][ $day ] = array(
			'start' => $start,
			'end'   => $end
		);

		// Return
		return $retval;
	}

	protected function get_month_details( $month, $year ) {

		// Return cached values
		if ( $this->cached_details[ 'month' ][ $year ][ $month ] ) {
			return $this->cached_details[ 'month' ][ $year ][ $month ];
		}

		$start = $this->mktime( 0, 0, 0, $month, 1, $year );

		list( $number_of_days, $w ) = $this->explode( 't-w', '-', $start );

		$end = $this->mktime( 23, 59, 59, $month, $number_of_days, $year );

		// Weekday order at month start / end
		$start_seq     = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );
		$first_weekday = $start_seq[ $w ];

		while ( $first_weekday !== $start_seq[ 0 ] ) {
			$start_seq[] = array_shift( $start_seq );
		}

		// Setup the return value
		$retval = $this->cached_details[ 'month' ][ $year ][ $month ] = array(
			'start'          => $start,
			'end'            => $end,
			'start_seq'      => $start_seq,
			'number_of_days' => $number_of_days
		);

		// Return
		return $retval;
	}

	protected function get_year_details( $year ) {

		// Return cached values
		if ( $this->cached_details[ 'year' ][ $year ] ) {
			return $this->cached_details[ 'year' ][ $year ];
		}

		$start = $this->mktime( 0,  0,  0,  1,  1,  $year );
		$end   = $this->mktime( 23, 59, 59, 12, 31, $year );

		list( $L, $w ) = $this->explode( 'L-w', '-', $start );

		$number_of_days = $this->year_days( $L );

		// Weekday order at year start / end
		$start_seq     = $end_seq = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );
		$first_weekday = $start_seq[ $w ];
		$last_weekday  = $start_seq[ $this->date( 'w', $end ) ];

		while ( $first_weekday !== $start_seq[ 0 ] ) {
			$start_seq[] = array_shift( $start_seq );
		}

		while ( $last_weekday !== $end_seq[ 6 ] ) {
			$end_seq[] = array_shift( $end_seq );
		}

		// Week offset (negative = 1st week begins december)
		if ( ( $pos = array_search( $this->wkst, $start_seq ) ) < 4 ) {
			$week_offset = $pos;
		} else {
			$week_offset = $pos - 7;
		}

		// Last week offset (negative = last week ends january)
		if ( ( $pos = array_search( $this->wkst, $end_seq ) ) < 4 ) {
			$last_week_offset = 0 - $pos;
		} else {
			$last_week_offset = 7 - $pos;
		}

		// Number of calendar weeks
		$number_of_weeks = round( ( $number_of_days - $week_offset - $last_week_offset) / 7 );

		// Start of first week
		$week_start = $this->mktime( 0, 0, 0, 1, 1 + $week_offset, $year );

		// End of last week
		$week_end = $this->mktime( 23, 59, 59, 12, 31 - $last_week_offset, $year );

		// Setup the return value
		$retval = $this->cached_details[ 'year' ][ $year ] = array(
			'start'           => $start,
			'end'             => $end,
			'start_seq'       => $start_seq,
			'number_of_days'  => $number_of_days,
			'number_of_weeks' => $number_of_weeks,
			'week_offset'     => $week_offset,
			'week_start'      => $week_start,
			'week_end'        => $week_end
		);

		// Return
		return $retval;
	}

	/** Helpers ***************************************************************/

	/**
	 * DateTime and DateTimeZone aware wrapper for mktime().
	 *
	 * @param int $hour
	 * @param int $min
	 * @param int $sec
	 * @param int $mon
	 * @param int $day
	 * @param int $year
	 * @return mixed
	 */
	protected function mktime( $hour, $min, $sec, $mon, $day, $year ) {

		// Create DateTime
		if ( ! $this->datetime instanceof \DateTime ) {
			$this->datetime = new \DateTime( null, $this->timezone );
		}

		// Try to set the date and time
		try {
			$this->datetime->setDate( $year, $mon, $day );
			$this->datetime->setTime( $hour, $min, $sec );

		// Bail if failure
		} catch ( \Exception $e ) {
			return false;
		}

		// Return unix timestamp
		return $this->datetime->format( 'U' );
	}

	/**
	 * DateTime and DateTimeZone aware wrapper for strtotime().
	 *
	 * @param int $time
	 * @return mixed
	 */
	protected function strtotime( $time = '' ) {
		try {
			$this->datetime = new \DateTime( $time, $this->timezone );
		} catch ( \Exception $e ) {
			return false;
		}

		return $this->datetime->format( 'U' );
	}

	/**
	 * Explode a DateTime format by a delimeter, using the DateTime and
	 * DateTimeZone.
	 *
	 * @param string $format
	 * @param string $delimiter
	 * @param int    $timestamp
	 * @return array
	 */
	protected function explode( $format = '', $delimiter = '', $timestamp = 0 ) {
		try {
			$this->datetime = new \DateTime( '@' . $timestamp );
		} catch ( \Exception $e ) {
			return false;
		}

		$this->datetime->setTimezone( $this->timezone );

		return explode( $delimiter, $this->datetime->format( $format ) );
	}

	/**
	 * DateTime and DateTimeZone aware wrapper for date().
	 *
	 * @param string $format
	 * @param int    $timestamp
	 * @return mixed
	 */
	protected function date( $format = '', $timestamp = 0 ) {
		try {
			$this->datetime = new \DateTime( '@' . $timestamp );
		} catch ( \Exception $e ) {
			return false;
		}

		$this->datetime->setTimezone( $this->timezone );

		return $this->datetime->format( $format );
	}

	/** Private ***************************************************************/

	/**
	 * Get number of days in the year.
	 *
	 * @param int $leap
	 * @return int
	 */
	private function year_days( $leap = 0 ) {
		return ! empty( $leap )
			? 366
			: 365;
	}

	/**
	 * Lowercase all of the keys of an array.
	 *
	 * @param array $args
	 * @return array
	 */
	private function lc_keys( $args = array() ) {

		// Default return value
		$retval = array();

		// Loop through and
		foreach ( $args as $key => $value ) {
			$retval[ strtolower( $key ) ] = $value;
		}

		// Return
		return $retval;
	}
}
