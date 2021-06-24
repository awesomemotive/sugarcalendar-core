<?php
/**
 * Recurrence Utility
 *
 * @package Plugins/Site/Events/Utilities/Dates/Recur
 */
namespace Sugar_Calendar\Utilities\Recur;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class for generating recurring Event text from a set of VEVENT and RRULE
 * parameters from the iCalendar RFC 5545 specification.
 *
 * See: https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
 */
class Text {

	/** Arguments *************************************************************/

	/**
	 * The original array of arguments.
	 *
	 * @var array
	 */
	public $args = array();

	/** Boundaries ************************************************************/

	/**
	 * Start of range to compute dates in.
	 *
	 * @var string
	 */
	protected $after;

	/**
	 * End of range to compute dates in.
	 *
	 * @var string
	 */
	protected $before;

	/** Settings **************************************************************/

	/**
	 * Time Zone Identifier.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.2.19
	 * @var string
	 */
	protected $tzid;

	/**
	 * Date Time Start.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.6.1
	 * @var string
	 */
	protected $dtstart;

	/**
	 * Date Time End.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.6.1
	 * @var string
	 */
	protected $dtend;

	/**
	 * Exception Date Times.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.8.5.1
	 * @var array
	 */
	protected $exdate = array();

	/**
	 * Recurrence Date Times.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.8.5.2
	 * @var array
	 */
	protected $rdate = array();

	/** RRULE parameters ******************************************************/

	/**
	 * Recurrence Frequency.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var string
	 */
	protected $freq;

	/**
	 * Recurrence Interval.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var int
	 */
	protected $interval = 1;

	/**
	 * Recurrence Count.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var int
	 */
	protected $count;

	/**
	 * Recurrence Until.
	 *
	 * End Date.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var string
	 */
	protected $until;

	/**
	 * Recurrence by Month.
	 *
	 * +/- 1 to 12
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $bymonth = array();

	/**
	 * Recurrence by Week Number.
	 *
	 * +/- 1 to 53
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byweekno = array();

	/**
	 * Recurrence by Year Day Number.
	 *
	 * +/- 1 to 366
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byyearday = array();

	/**
	 * Recurrence by Month Day Number.
	 *
	 * +/- 1 to 31
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $bymonthday = array();

	/**
	 * Recurrence by Day Abbreviation.
	 *
	 * SU, MU, TU, WE, TH, FR, SA
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byday = array();

	/**
	 * Recurrence by Hour.
	 *
	 * 0 to 23
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byhour = array();

	/**
	 * Recurrence by Minute.
	 *
	 * 0 to 59
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $byminute = array();

	/**
	 * Recurrence by Second.
	 *
	 * 0 to 59
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $bysecond = array();

	/**
	 * Recurrence by Position.
	 *
	 * +/- 1 to 366
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var array
	 */
	protected $bysetpos = array();

	/**
	 * Recurrence Week Start.
	 *
	 * SU, MU, TU, WE, TH, FR, SA
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5545#section-3.3.10
	 * @var string
	 */
	protected $wkst = 'MO';

	/** Stashes ***************************************************************/

	/**
	 * DateTimeZone object.
	 *
	 * @var DateTimeZone
	 */
	protected $timezone;

	/**
	 * DateTime object.
	 *
	 * @var DateTime
	 */
	protected $datetime;

	/**
	 * DateInterval object.
	 *
	 * @var DateInterval
	 */
	protected $duration;

	/**
	 * Duration in seconds.
	 *
	 * @var int
	 */
	protected $duration_time;

	/**
	 * All expansions.
	 *
	 * @var array
	 */
	protected $expansions = array();

	/**
	 * All limitations.
	 *
	 * @var array
	 */
	protected $limitations = array();

	/**
	 * Number of expansions set by rule.
	 *
	 * @var int
	 */
	protected $expansion_count = 0;

	/**
	 * Number of iterations.
	 *
	 * @var int
	 */
	protected $iteration = 0;

	/**
	 * Number of occurrences.
	 *
	 * @var int
	 */
	protected $current_count = 0;

	/**
	 * Last computed date.
	 *
	 * @var string
	 */
	protected $current_date;

	/**
	 * Week sequence.
	 *
	 * @var array
	 */
	protected $wkst_seq = array( 'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU' );

	/** Caches ****************************************************************/

	/**
	 * Previously computed dates.
	 *
	 * @var array
	 */
	protected $cached_dates = array();

	/**
	 * Dates from rdate rule.
	 *
	 * @var array
	 */
	protected $cached_rdates = array();

	/**
	 * Cached year, month and week details.
	 *
	 * @var array
	 */
	protected $cached_details = array();

	/** Errors ****************************************************************/

	/**
	 * True if error found in arguments.
	 *
	 * @var bool
	 */
	public $error = false;

	/** Constants *************************************************************/

	/**
	 * Constant for days of week.
	 *
	 * @var array
	 */
	const DAYS = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );

	/** Methods ***************************************************************/

	/**
	 * Fragments.
	 *
	 * @var array
	 */
	protected $fragments = array();

	/**
	 * Constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		// Bail if no arguments to parse
		if ( empty( $args ) ) {
			return;
		}

		// Parse arguments
		$this->parse_args( $args );
	}

	/**
	 * Parse the arguments.
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

		// Set the original arguments
		$this->args = $args;

		// Reset fragments
		$this->fragments = array();

		// Reset the error flag
		$this->error = false;

		// Lowercase argument keys
		$args = $this->lc_keys( $args );

		// ID is required early
		$this->id = ! empty( $args[ 'id' ] )
			? $args[ 'id' ]
			: null;

		// Time zone is required early
		$this->tzid = ! empty( $args[ 'tzid' ] ) && is_string( $args[ 'tzid' ] )
			? trim( $args[ 'tzid' ] )
			: date_default_timezone_get();

		// Get time zone object
		try {
			$this->timezone = new \DateTimeZone( $this->tzid );

		// Bail on error
		} catch ( \Exception $e ) {
			$this->error = true;

			return false;
		}

		// Parse RRULE if present
		if ( ! empty( $args[ 'rrule' ] ) && strstr( $args[ 'rrule' ], '=' ) ) {

			// Explode by semicolon
			$rules = $this->safe_explode( ';', $args[ 'rrule' ] );

			// Successfully exploded
			if ( ! empty( $rules ) ) {

				// Loop through parts
				foreach ( $rules as $part ) {

					// Skip if invalid
					if ( ! strstr( $part, '=' ) ) {
						continue;
					}

					// Explode to get rule and value
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
			if ( empty( $value ) && is_string( $value ) ) {
				continue;
			}

			// Validate specific args
			switch ( $key ) {

				// DateTime::format() compatible string to return
				case 'format' :

					// Format to return
					if ( ! empty( $args[ 'format' ] ) && is_string( $args[ 'format' ] ) ) {
						$this->format = trim( $args[ 'format' ] );
					}

					break;

				// Prevent infinite loops
				case 'max' :

					// Maximum number of iterations
					if ( ! empty( $args[ 'max' ] ) && is_numeric( $args[ 'max' ] ) ) {
						$this->max = abs( $args[ 'max' ] );
					}

					break;

				// Whether or not to include sequences
				case 'sequence' :

					// Return sequence numbers?
					if ( isset( $args[ 'sequence' ] ) ) {
						$this->sequence = (bool) $args[ 'sequence' ];
					}

					break;

				// DATETIME || DATE values
				case 'dtstart' :
				case 'dtend' :
				case 'until' :
				case 'after' :
				case 'before' :

					// Create timestamps
					$this->{$key} = $this->strtotime( $value );

					// Invalid
					if ( false === $this->{$key} ) {
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

						// Explode by comma
						$rdate = $this->safe_explode( ',', $entry );

						// Loop through dates
						foreach ( $rdate as $property ) {

							// Explode to get date & period
							list( $date, $period ) = explode( '/', $property );

							// Create timestamp
							$start = $this->strtotime( $date );

							// Invalid
							if ( false === $start ) {
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
									$end = $this->strtotime( $period );

									// Invalid
									if ( false === $end ) {
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

						// Explode by comma
						$exdate = $this->safe_explode( ',', $entry );

						// Loop through dates
						foreach ( $exdate as $datestring ) {

							// Create timestamp
							$this->exdate[] = $exclude = $this->strtotime( $datestring );

							// Invalid
							if ( false === $exclude ) {
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

					// Force to uppercase
					$this->freq = strtoupper( $value );

					// Valid frequencies
					$valid = array( 'SECONDLY', 'MINUTELY', 'HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY' );

					// Invalid
					if ( ! in_array( $this->freq, $valid, true ) ) {
						$this->error = true;
					}

					break;

				// NUMERIC VALUES (> 0)
				case 'count' :
				case 'interval' :

					// Force to absolute integer
					$this->{$key} = (int) $value;

					// Invalid
					if ( ! is_numeric( $this->{$key} ) || ( $this->{$key} < 1 ) ) {
						$this->error = true;
					}

					break;

				// WKST
				case 'wkst' :

					// Force to uppercase
					$this->wkst = strtoupper( $value );

					// Invalid
					if ( ! in_array( $this->wkst, self::DAYS, true ) ) {
						$this->error = true;
					}

					break;

				// BYMONTH (+/- 1 to 12)
				case 'bymonth' :

					// Explode by comma
					$this->bymonth = $this->safe_explode( ',', $value );

					foreach ( $this->bymonth as $month ) {

						// Invalid
						if ( ! is_numeric( $month ) || ( abs( $month ) < 1 ) || ( abs( $month ) > 12 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYWEEKNO (+/- 1 to 53)
				case 'byweekno' :

					// Explode by comma
					$this->byweekno = $this->safe_explode( ',', $value );

					foreach ( $this->byweekno as $week ) {

						// Invalid
						if ( ! is_numeric( $week ) || ( abs( $week ) < 1 ) || ( abs( $week ) > 53 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYYEARDAY (+/- 1 to 366)
				case 'byyearday' :

				// BYSETPOS (+/- 1 to 366)
				case 'bysetpos' :

					// Explode by comma
					$this->{$key} = $this->safe_explode( ',', $value );

					foreach ( $this->{$key} as $value ) {

						// Invalid
						if ( ! is_numeric( $value ) || ( abs( $value ) < 1 ) || ( abs( $value ) > 366 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYMONTHDAY (+/- 1 to 31)
				case 'bymonthday' :

					// Explode by comma
					$this->bymonthday = $this->safe_explode( ',', $value );

					foreach ( $this->bymonthday as $day ) {

						// Invalid
						if ( ! is_numeric( $day ) || ( abs( $day ) < 1 ) || ( abs( $day ) > 31 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYDAY (SU, MO, TU, WE, TH, FR, SA)
				case 'byday' :

					// Explode by comma
					$byday = $this->safe_explode( ',', $value );

					foreach ( $byday as $key ) {
						$weekday = substr( $key, -2 );
						$pos     = substr( $key, 0, -2 );

						// Set byday
						$this->byday[] = array(
							'weekday' => $weekday,
							'pos'     => $pos
						);

						// Invalid
						if ( ! in_array( $weekday, self::DAYS, true ) || ( ( $pos !== '' ) && ( ! is_numeric( $pos ) || ( $pos === 0 ) ) ) ) {
							$this->error = true;
						}
					}

					break;

				// BYHOUR (0-23)
				case 'byhour' :

					// Explode by comma
					$this->byhour = $this->safe_explode( ',', $value );

					foreach ( $this->byhour as $hour ) {

						// Invalid
						if ( ! is_numeric( $hour ) || ( $hour < 0 ) || ( $hour > 23 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// BYMINUTE (0-59)
				case 'byminute' :

				// BYSECOND (0-59)
				case 'bysecond' :

					// Explode by comma
					$this->{$key} = $this->safe_explode( ',', $value );

					foreach ( $this->{$key} as $value ) {

						// Invalid
						if ( ! is_numeric( $value ) || ( $value < 0 ) || ( $value > 59 ) ) {
							$this->error = true;
							break 1;
						}
					}

					break;

				// Default
				default :

					// Just set it, but only if empty
					if ( empty( $this->{$key} ) ) {
						var_dump( $value );
						$this->{$key} = $value;
					}

					break;
			}
		}

		// Validate arguments
		$this->validate_args();

		// Validate class properties from arguments
		$this->validate_properties();

		// Return
		return empty( $this->error );
	}

	/**
	 * Validate all class arguments.
	 */
	protected function validate_args() {

		// Synchronize until / before
		if ( ! is_null( $this->until ) && ( is_null( $this->before ) || ( $this->before > $this->until ) ) ) {
			$this->before = $this->until;
		}

		// Disable skipping
		if ( ! is_null( $this->count ) || is_null( $this->after ) ) {
			$this->sequence = true;
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
			if ( ( array( '00', '00', '00' ) === $this->date_explode( 'H-i-s', '-', $this->dtstart ) ) && ( array( '00', '00', '00' ) === $this->date_explode( 'H-i-s', '-', $this->dtend ) ) ) {
				$number_of_days = round( ( $this->dtend - $this->dtstart ) / 86400 );
				$this->duration = new \DateInterval( 'P' . $number_of_days . 'D' );
				$this->dtend    = null;

			// Set duration_time through simple subtraction
			} else {
				$this->duration_time = ( $this->dtend - $this->dtstart );
			}

		// No end
		} else {
			$this->duration_time = 0;
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

			// Frequency cannot be empty
			case ( empty( $this->freq ) ) :

			// Timezone cannot be empty
			case ( empty( $this->timezone ) ) :

			// Start cannot be empty
			case ( is_null( $this->dtstart ) ) :

			// Duration time cannot be negative (0 is "no end")
			case ( ! is_null( $this->duration_time ) && ( 0 > $this->duration_time ) ) :

			// Duration cannot compete with End
			case ( ! empty( $this->duration ) && ! is_null( $this->dtend ) ) :

			// Count cannot compete with End Date
			case ( ! empty( $this->count    ) && ! is_null( $this->until ) ) :

			// By week not for Yearly frequency
			case ( ! empty( $this->byweekno   ) && ( 'YEARLY' !== $this->freq ) ) :

			// By month day not for Weekly frequency
			case ( ! empty( $this->bymonthday ) && ( 'WEEKLY' === $this->freq ) ) :

			// By year day not for Daily, Weekly, or Monthly frequencies
			case ( ! empty( $this->byyearday  ) && in_array( $this->freq, array( 'DAILY', 'WEEKLY', 'MONTHLY' ), true ) ) :
				$this->error = true;
		}
	}

	public function text() {

		// Frequency
		switch ( $this->freq ) {
			case 'YEARLY' :
				$this->add_yearly();
				break;
			case 'MONTHLY' :
				$this->add_monthly();
				break;
			case 'WEEKLY' :
				$this->add_weekly();
				break;
			case 'DAILY' :
				$this->add_daily();
				break;
			case 'HOURLY' :
				$this->add_hourly();
				break;
			case 'MINUTELY' :
			case 'SECONDLY' :
				return '';
		}

		// Recurring end date
		if ( $this->until instanceof \DateTimeInterface ) {
			$formatted = $this->day_date( array('date' => $this->until->format( 'U' ) ) );
			$this->add_fragment( $this->translate( 'until %date%', array( 'date' => $formatted ) ) );

		// Recurring end count
		} elseif ( ! empty( $this->count ) ) {

			// More than 1
			if ( $this->is_plural( $this->count ) ) {
				$this->add_fragment( $this->translate( 'for_plural_times', array( 'count' => $this->count ) ) );

			// One time
			} else {
				$this->add_fragment( $this->translate( 'for_singular_time' ) );
			}
		}

		// Approximate
		if ( ! $this->is_fully_convertible() ) {
			$this->add_fragment( $this->translate( 'approximate' ) );
		}

		// Get return value
		$retval = implode( ' ', $this->fragments );

		// Return
		return $retval;
	}

	protected function day_date( $args = array() ) {
		$month = gmdate( 'n', $args['date'] );

		return $this->month_names[ $month - 1 ] . ' '. gmdate( 'j, Y', $args['date'] );
	}

	protected function day_month( $args = array() ) {
		return $this->month_names[ $args['month'] - 1 ] . ' '. $args['day'];
	}

	protected function translate( $name = '', $args = array() ) {

		// Bail if no string
		if ( ! isset( $this->strings[ $name ] ) ) {
			return '';
		}

		// Setup return value
		$retval = $this->strings[ $name ];

		// Count
		if ( ! empty( $args[ 'count' ] ) ) {
			$retval = str_replace( '%s', $args[ 'count'], $retval );

		// Date
		} elseif ( ! empty( $args[ 'date' ] ) ) {
			$retval = str_replace( '%s', $args[ 'date'], $retval );
		}

		// Return
		return $retval;
	}

	protected function is_fully_convertible() {

		if ( ! in_array( $this->freq, array( 'YEARLY', 'MONTHLY', 'WEEKLY', 'DAILY', 'HOURLY' ), true ) ) {
			return false;
		}

		if ( ! empty( $this->until ) && ! empty( $this->count ) ) {
			return false;
		}

		if ( ! empty( $this->bysecond ) || ! empty( $this->byminute ) || ! empty( $this->byhour ) ) {
			return false;
		}

		if ( ( $this->freq !== 'YEARLY' ) && ( ! empty( $this->byweekno ) || ! empty( $this->byyearday ) ) ) {
			return false;
		}

		return true;
	}

	/** Adders ****************************************************************/

	protected function add_yearly() {

		if ( ! empty( $this->bymonth ) && count( $this->bymonth ) > 1 && $this->interval === 1 ) {
			$this->add_fragment( $this->translate( 'every_month_list' ) );
		} else {
			$count = $this->is_plural( $this->interval )
				? 'every_plural_years'
				: 'every_singular_year';

			$this->add_fragment( $this->translate( $count, array( 'count' => $this->interval ) ) );
		}

		$hasNoOrOneByMonth = is_null( $this->bymonth ) || count( $this->bymonth ) <= 1;

		if ( $hasNoOrOneByMonth && empty( $this->bymonthday ) && empty( $this->byday ) && empty( $this->byyearday ) && empty( $this->byweekno ) ) {
			$this->add_fragment( $this->translate( 'on' ) );

			$monthNum = ( is_array( $this->bymonth ) && count( $this->bymonth ) )
				? $this->bymonth[ 0 ]
				: $this->datetime->format( 'n' );

			$this->add_fragment(
				$this->day_month( array(
					'month' => $monthNum,
					'day'   => $this->datetime->format( 'd' )
				) )
			);

		} elseif ( ! empty( $this->bymonth ) ) {
			if ( $this->interval !== 1 ) {
				$this->add_fragment( $this->translate( 'in_month' ) );
			}

			$this->add_bymonth();
		}

		if ( ! empty( $this->bymonthday ) ) {
			$this->add_bymonth_day();
			$this->add_fragment( $this->translate( 'of_the_month' ) );
		} elseif ( ! empty( $this->byday ) ) {
			$this->add_byday();
		}

		if ( ! empty( $this->byyearday ) ) {
			$this->add_fragment( $this->translate( 'on the' ) );
			$this->add_fragment( $this->get_byyearday_as_text( $this->byyearday ) );
			$this->add_fragment( $this->translate( 'day' ) );
		}

		if ( ! empty( $this->byweekno ) ) {
			$this->add_fragment( $this->translate( 'in_week' ) );

			$count = $this->is_plural( count( $this->byweekno ) )
				? 'weeks'
				: 'week';
			$this->add_fragment( $this->translate( $count ) );

			$this->add_fragment( $this->get_byweekno_as_text( $this->byweekno ) );
		}

		if ( empty( $this->bymonthday ) && empty( $this->byyearday ) && empty( $this->byday ) && ! empty( $this->byweekno ) ) {
			$this->add_day_of_week();
		}
	}

	protected function add_monthly() {

		if ( ! empty( $this->bymonth ) && $this->interval === 1 ) {
			$this->add_fragment( $this->translate( 'every_month_list' ) );

		} else {
			$count = $this->is_plural( $this->interval )
				? 'every_plural_months'
				: 'every_singular_month';

			$this->add_fragment( $this->translate( $count, array( 'count' => $this->interval ) ) );
		}

		if ( ! empty( $this->bymonth ) ) {

			if ( $this->interval !== 1 ) {
				$this->add_fragment( $this->translate( 'in_month' ) );
			}

			$this->add_bymonth();
		}

		if ( ! empty( $this->bymonthday ) ) {
			$this->add_bymonth_day();
		} elseif ( ! empty( $this->byday ) ) {
			$this->add_byday();
		}
	}

	protected function add_weekly() {

		$count = $this->is_plural( $this->interval )
			? 'every_plural_weeks'
			: 'every_singular_week';

		$this->add_fragment( $this->translate( $count, array( 'count' => $this->interval ) ) );

		if ( empty( $this->bymonthday ) && empty( $this->byday ) ) {
			$this->add_day_of_week();
		}

		if ( ! empty( $this->bymonth ) ) {
			$this->add_fragment( $this->translate( 'in_month' ) );
			$this->add_bymonth();
		}

		if ( ! empty( $this->bymonthday ) ) {
			$this->add_bymonth_day();
			$this->add_fragment( $this->translate( 'of_the_month' ) );
		} elseif ( ! empty( $this->byday ) ) {
			$this->add_byday();
		}
	}

	protected function add_daily() {

		$count = $this->is_plural( $this->interval )
			? 'every_plural_days'
			: 'every_singular_day';

		$this->add_fragment( $this->translate( $count ), array( 'count' => $this->interval ) );

		if ( ! empty( $this->bymonth ) ) {
			$this->add_fragment( $this->translate( 'in_month' ) );
			$this->add_bymonth();
		}

		if ( ! empty( $this->bymonthday ) ) {
			$this->add_bymonth_day();
			$this->add_fragment( $this->translate( 'of_the_month' ) );
		} elseif ( ! empty( $this->byday ) ) {
			$this->add_byday();
		}
	}

	protected function add_hourly() {

		$count = $this->is_plural( $this->interval )
			? 'every_plural_hours'
			: 'every_singular_hour';

		$this->add_fragment( $this->translate( $count, array( 'count' => $this->interval ) ) );

		if ( ! empty( $this->bymonth ) ) {
			$this->add_fragment( $this->translate( 'in_month' ) );
			$this->add_bymonth();
		}

		if ( ! empty( $this->bymonthday ) ) {
			$this->add_bymonth_day();
			$this->add_fragment( $this->translate( 'of_the_month' ) );

		} elseif ( ! empty( $this->byday ) ) {
			$this->add_byday();
		}
	}

	protected function add_bymonth() {
		$this->add_fragment( $this->get_bymonth_as_text() );
	}

	protected function add_bymonth_day() {
		if ( ! empty( $this->byday ) ) {
			$this->add_fragment( $this->translate( 'on' ) );
			$this->add_fragment( $this->get_byday_as_text( 'or' ) );
			$this->add_fragment( $this->translate( 'the_for_monthday' ) );
			$this->add_fragment( $this->get_bymonthday_as_text( 'or' ) );
		} else {
			$this->add_fragment( $this->translate( 'on the' ) );
			$this->add_fragment( $this->get_bymonthday_as_text( 'and' ) );
		}
	}

	protected function add_byday() {
		$this->add_fragment( $this->translate( 'on' ) );
		$this->add_fragment( $this->get_byday_as_text() );
	}

	protected function add_day_of_week() {
		$this->add_fragment( $this->translate( 'on' ) );
		$this->add_fragment( $this->day_names[ $this->datetime->format( 'w' ) ] );
	}

	/** Getters ***************************************************************/

	public function get_bymonth_as_text() {

		// Bail if empty
		if ( empty( $this->bymonth ) ) {
			return '';
		}

		// Copy, for sorting
		$bymonth = $this->bymonth;

		// Sort
		if ( count( $bymonth ) > 1 ) {
			sort( $bymonth );
		}

		$bymonth = array_map(
			function ( $month_int ) {
				return $this->month_names[ $month_int - 1 ];
			},
			$bymonth
		);

		// Get return value
		$retval = $this->get_list_string_from_array( $bymonth );

		// Return
		return $retval;
	}

	public function get_byday_as_text( $separator = 'and' ) {

		// Bail if empty
		if ( empty( $this->byday ) ) {
			return '';
		}

		$map = array(
			'SU' => null,
			'MO' => null,
			'TU' => null,
			'WE' => null,
			'TH' => null,
			'FR' => null,
			'SA' => null
		);

		$timestamp = mktime( 1, 1, 1, 1, 12, 2014 ); // A Sunday

		foreach ( array_keys( $map ) as $short ) {
			$long = $this->day_names[ date( 'w', $timestamp ) ];
			$map[ $short ] = $long;
			$timestamp += 86400;
		}

		// Default return value
		$byday = array();

		// No ordinals
		$num_ords = 0;

		// Loop through
		foreach ( $this->byday as $key => $short ) {
			$day    = strtoupper( $short['weekday'] );
			$string = '';

			// Support days with positions like "-1SU" for "last Sunday"
			if ( preg_match( '/([+-]?)([0-9]*)([A-Z]+)/', $short['weekday'], $parts ) ) {

				// Plus or Minus
				$symbol = $parts[ 1 ];

				// 1st, 2nd, etc...
				$nth    = $parts[ 2 ];

				// Wednesday, Friday, etc...
				$day    = $parts[ 3 ];

				// Ordinal?
				if ( ! empty( $nth ) ) {
					++$num_ords;

					// Positive or Negative
					$ord = ( $symbol === '-' )
						? "-{$nth}"
						: $nth;

					// Get the ordinal number
					$string .= $this->get_ordinal_number( $ord );
				}
			}

			// Bail if unknown day
			if ( ! isset( $map[ $day ] ) ) {
				return '';
			}

			// Add space after ordinal
			if ( ! empty( $string ) ) {
				$string .= ' ';
			}

			// Add to return value
			$byday[ $key ] = ltrim( $string . $map[ $day ] );
		}

		$retval = ! empty( $num_ords )
			? $this->translate( 'the_for_weekday' ) . ' '
			: '';

		if ( $retval === ' ' ) {
			$retval = '';
		}

		$retval .= $this->get_list_string_from_array( $byday, $separator );

		return $retval;
	}

	public function get_bymonthday_as_text( $separator = 'and' ) {

		// Bail if empty
		if ( empty( $this->bymonthday ) ) {
			return '';
		}

		// Copy, for sorting
		$bymonthday = $this->bymonthday;

		// Sort negative indices in reverse order so we get e.g. 1st, 2nd, 4th, 3rd last, last day
		usort( $bymonthday, function ( $a, $b ) {
			if ( ( $a < 0 && $b < 0 ) || ( $a >= 0 && $b >= 0 ) ) {
				return $a - $b;
			}

			return $b - $a;
		} );

		/**
		 * Generate ordinal numbers and insert a "on the" for clarity in the
		 * middle if we have both positive and negative ordinals.
		 *
		 * This is to avoid confusing situations like:
		 * - monthly on the 1st and 2nd to the last day
		 *
		 * Which gets clarified to:
		 * - monthly on the 1st day and on the 2nd to the last day
		 */
		$had_positives = false;
		$had_negatives = false;

		// Loop through return values
		foreach ( $bymonthday as $index => $day ) {

			// No prefix
			$prefix = '';

			if ( $day >= 0 ) {
				$had_positives = true;
			}

			if ( $day < 0 ) {

				if ( $had_positives && ! $had_negatives && ( $separator === 'and' ) ) {
					$prefix = $this->translate( 'on the' ) . ' ';
				}

				$had_negatives = true;
			}

			$bymonthday[ $index ] = $prefix . $this->get_ordinal_number( $day, end( $bymonthday ) < 0 );
		}

		// Get return value
		$retval = $this->get_list_string_from_array( $bymonthday, $separator );

		// Return
		return $retval;
	}

	public function get_byyearday_as_text() {

		// Bail if empty
		if ( empty( $this->byyearday ) ) {
			return '';
		}

		// Copy, for sorting
		$byyearday = $this->byyearday;

		// Sort negative indices in reverse order so we get e.g. 1st, 2nd, 4th, 3rd last, last day
		usort( $byyearday, function ( $a, $b ) {
			if ( ( $a < 0 && $b < 0 ) || ( $a >= 0 && $b >= 0 ) ) {
				return $a - $b;
			}

			return $b - $a;
		} );

		// Map ordinals
		$byyearday = array_map(
			array( $this, 'get_ordinal_number' ),
			$byyearday,
			array_fill( 0, count( $byyearday ), end( $byyearday ) < 0 )
		);

		// Get return value
		$retval = $this->get_list_string_from_array( $byyearday );

		// Return
		return $retval;
	}

	public function get_byweekno_as_text() {

		// Bail if empty
		if ( empty( $this->byweekno ) ) {
			return '';
		}

		// Copy, for sorting
		$byweekno = $this->byweekno;

		// Sort
		if ( count( $byweekno ) > 1 ) {
			sort( $byweekno );
		}

		// Get return value
		$retval = $this->get_list_string_from_array( $byweekno );

		// Return
		return $retval;
	}

	/** Fragments *************************************************************/

	protected function add_fragment( $fragment = '' ) {
		if ( ! empty( $fragment ) ) {
			$this->fragments[] = $fragment;
		}
	}

	/** Helpers ***************************************************************/

	protected function is_plural( $number = 0 ) {
		return $number % 100 !== 1;
	}

	protected function get_ordinal_number( $number = '', $has_negatives = false ) {

		// Bail if not a number
		if ( ! preg_match( '{^-?\d+$}D', $number ) ) {
			return '';
		}

		// Endings
		$ends = array(
			'th', // 0th
			'st', // 1st
			'nd', // 2nd
			'rd', // 3rd
			'th', // 4th
			'th', // 5th
			'th', // 6th
			'th', // 7th
			'th', // 8th
			'th'  // 9th
		);

		// No suffix
		$suffix = '';

		// Is number negative?
		$is_negative = $number < 0;

		if ( $number === -1 ) {
			$abbreviation = 'last';

		} else {

			if ( ! empty( $is_negative ) ) {
				$number = abs( $number );
				$suffix = ' to the last';
			}

			if ( ( $number % 100 ) >= 11 && ( $number % 100 ) <= 13) {
				$abbreviation = $number . 'th';
			} else {
				$abbreviation = $number . $ends[ $number % 10 ];
			}
		}

		if ( ! empty( $has_negatives ) ) {
			$suffix .= ' day';
		}

		return $abbreviation . $suffix;
	}

	protected function get_list_string_from_array( $values = array(), $separator = 'and' ) {
		$separator = $this->translate( $separator );

		// Bail if no values
		if ( ! is_array( $values ) ) {
			return '';
		}

		// Get count
		$num_values = count( $values );

		// Bail if empty
		if ( empty( $num_values ) ) {
			return '';
		}

		// Bail if only 1
		if ( $num_values === 1 ) {
			reset( $values );

			return current( $values );
		}

		// Bail if exactly 2
		if ( $num_values === 2 ) {
			return implode( " {$separator} ", $values );
		}

		// Separate by comma if 3 or more
		$last   = array_pop( $values );
		$retval = implode( ', ', $values );
		$retval .= " {$separator} " . $last;

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
			$this->datetime->setTime( $hour, $min, $sec );
			$this->datetime->setDate( $year, $mon, $day );

		// Bail if error
		} catch ( \Exception $e ) {
			return false;
		}

		// Return unix timestamp
		return $this->datetime->format( 'U' );
	}

	/**
	 * DateTime and DateTimeZone aware wrapper for strtotime().
	 *
	 * @param string $datetime
	 * @return mixed
	 */
	protected function strtotime( $datetime = '' ) {

		// Try to get the DateTime
		try {
			$this->datetime = new \DateTime( $datetime, $this->timezone );

		// Bail if error
		} catch ( \Exception $e ) {
			return false;
		}

		// Return unix timestamp
		return $this->datetime->format( 'U' );
	}

	/**
	 * DateTime and DateTimeZone aware wrapper for date().
	 *
	 * @param string $format
	 * @param int    $timestamp
	 * @param mixed  $add
	 * @return mixed
	 */
	protected function date( $format = '', $timestamp = 0, $add = null ) {

		// Try to get the DateTime
		try {
			$this->datetime = new \DateTime( '@' . $timestamp );

		// Bail if error
		} catch ( \Exception $e ) {
			return false;
		}

		// Set the time zone
		$this->datetime->setTimezone( $this->timezone );

		// Maybe add
		if ( ! is_null( $add ) && ( $add instanceof \DateInterval ) ) {
			$this->datetime->add( $add );
		}

		// Return, formatted
		return $this->datetime->format( $format );
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
	protected function date_explode( $format = '', $delimiter = '', $timestamp = 0 ) {
		return explode( $delimiter, $this->date( $format, $timestamp ) );
	}

	/** Private ***************************************************************/

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
			$retval[ strtolower( $key ) ] = is_string( $value )
				? trim( $value )
				: $value;
		}

		// Return
		return $retval;
	}

	/**
	 * Trims to prevent empties, and explodes.
	 *
	 * @param string $delimiter
	 * @param string $string
	 * @return array
	 */
	private function safe_explode( $delimiter = ',', $string = '' ) {
		return explode( $delimiter, rtrim( $string, $delimiter ) );
	}
}
