<?php
/**
 * DateCollider Trait
 *
 * @package Plugins/Site/Events/DateCollider
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class trait
 *
 * @since 2.2.0
 */
trait DateCollider {

	/** Boundaries ************************************************************/

	/**
	 * Start boundary
	 *
	 * @since 2.2.0
	 * @var DateTime
	 */
	public $boundary_start = null;

	/**
	 * End boundary
	 *
	 * @since 2.2.0
	 * @var DateTime
	 */
	public $boundary_end = null;

	/**
	 * Event start
	 *
	 * @since 2.2.0
	 * @var DateTime
	 */
	public $event_start = null;

	/**
	 * Event end
	 *
	 * @since 2.2.0
	 * @var DateTime
	 */
	public $event_end = null;

	/** Sources ***************************************************************/

	/**
	 * Event
	 *
	 * @since 2.2.0
	 * @var Sugar_Calendar\Event
	 */
	public $event = null;

	/** Results ***************************************************************/

	/**
	 * Return value
	 *
	 * @since 2.2.0
	 * @var bool
	 */
	public $intersects = false;

	/**
	 * The name of the method that claims to have intersected
	 *
	 * @since 2.2.0
	 * @var string
	 */
	public $intersector = false;

	/** Patterns **************************************************************/

	/**
	 * Array of arrays, keyed by recurring name, with values based on
	 * DateTimeInterface::format().
	 *
	 * @since 2.2.0
	 * @var array
	 */
	public $patterns = array(

		// Yearly recurring checks Month, Day, and Hour
		'yearly'  => array( 'n', 'j', 'G' ),

		// Monthly recurring checks Day and Hour
		'monthly' => array( 'j', 'G' ),

		// Weekly recurring checks Day-of-week (0 index) and Hour
		'weekly'  => array( 'N', 'G' ),

		// Daily recurring checks Hour
		'daily'   => array( 'G' )
	);

	/**
	 * Array of formats that will trigger a complex match
	 *
	 * Currently multi-Month and multi-Day which is ideal for Calendar
	 * applications, but adjustable for your own needs
	 *
	 * @since 2.2.0
	 * @var array
	 */
	public $complex_formats = array( 'n', 'j' );

	/** Matches ***************************************************************/

	/**
	 * Array of matches
	 *
	 * @since 2.2.0
	 * @var array
	 */
	public $matches = array();

	/**
	 * Name currently being matched
	 *
	 * @since 2.2.0
	 * @var string
	 */
	public $match_name = '';

	/**
	 * Pattern currently being matched
	 *
	 * @since 2.2.0
	 * @var string
	 */
	public $match_pattern = array();

	/**
	 * Index currently being matched
	 *
	 * @since 2.2.0
	 * @var string
	 */
	public $match_index = '';

	/**
	 * Format currently being matched
	 *
	 * @since 2.2.0
	 * @var string
	 */
	public $match_format = '';

	/**
	 * Array of values to compare to make matches with
	 *
	 * @since 2.2.0
	 * @var array
	 */
	public $match_values = array();

	/** Methods ***************************************************************/

	/**
	 * Perform all checks
	 *
	 * @since 2.2.0
	 *
	 * @param Event    $event
	 * @param DateTime $boundary_start
	 * @param DateTime $boundary_end
	 */
	private function check( $event = false, $boundary_start = false, $boundary_end = false ) {

		// Set object vars
		$this->event          = $event;
		$this->boundary_start = $boundary_start;
		$this->boundary_end   = $boundary_end;

		// Recurrence is over
		if ( $this->recurrence_end_date_passed() ) {
			return;
		}

		// Set Event DateTimes
		$this->set_event_datetimes();

		// Bail if Event starts after boundary ends
		if ( $this->event_start > $this->boundary_end ) {
			return;
		}

		// Loop through matches
		foreach ( $this->patterns as $name => $formats ) {

			// Skip if not the correct recurrence
			if ( $name !== $this->event->recurrence ) {
				continue;
			}

			// Match
			$this->try( $name, $formats );
		}
	}

	/**
	 * Try to match patterns to boundaries
	 *
	 * @since 2.2.0
	 * @param string $name
	 * @param array $pattern
	 */
	private function try( $name = '', $pattern = array() ) {

		// Set match name & pattern
		$this->match_name    = $name;
		$this->match_pattern = $pattern;

		// Bail if no pattern
		if ( empty( $this->match_pattern ) ) {
			return;
		}

		// Setup matches
		$this->setup();

		// Bail if no matches
		if ( empty( $this->matches ) ) {
			return;
		}

		// Look for matches
		$this->look();
	}

	/**
	 * Setup all of the possible boundaries to match.
	 *
	 * @since 2.2.0
	 */
	private function setup() {

		// Default return value
		$retval = array();

		// Loop through pattern and try to match them
		foreach ( $this->match_pattern as $index => $format ) {

			// Skip if empty key
			if ( empty( $format ) ) {
				continue;
			}

			// Set match index & key
			$this->match_index  = $index;
			$this->match_format = $format;

			// Prepare values to match
			$this->prepare();

			// Initialize
			if ( ! isset( $retval['simple'] ) ) {
				$retval['simple'] = array();
			}

			// Always try the simple match
			$retval['simple'] = $this->match_simple( $retval['simple'] );

			// Complex matching
			if ( $this->do_complex_match() ) {

				// Initialize
				if ( ! isset( $retval['complex'] ) ) {
					$retval['complex'] = array();
				}

				// Complex matches
				$retval['complex'] = $this->match_complex( $retval['complex'] );
			}
		}

		// Set matches
		$this->matches = $retval;
	}

	/**
	 * Prepare values to match
	 *
	 * @since 2.2.0
	 */
	private function prepare() {
		$this->match_values = array(

			// Event start
			'es' =>    $this->event_start->format( $this->match_format ),

			// Event end
			'ee' =>      $this->event_end->format( $this->match_format ),

			// Boundary start
			'bs' => $this->boundary_start->format( $this->match_format ),

			// Boundary end
			'be' =>   $this->boundary_end->format( $this->match_format )
		);
	}

	/**
	 * Add simple match conditions
	 *
	 * @since 2.2.0
	 * @param array $match
	 * @return array
	 */
	private function match_simple( $match = array() ) {

		// Ends after boundary start
		$match[ "{$this->match_format}_ends" ]   = ( $this->match_values['ee'] >= $this->match_values['bs'] );

		// Starts before boundary end
		$match[ "{$this->match_format}_starts" ] = ( $this->match_values['es'] <= $this->match_values['be'] );

		// Return
		return $match;
	}

	/**
	 * Whether or not a complex match should be done
	 *
	 * @since 2.2.0
	 * @return bool
	 */
	private function do_complex_match() {

		// Defaults
		$do = array();

		// Multi format, where start is lower number than end
		$do['inverted'] = ( $this->match_values['es'] > $this->match_values['ee'] );

		// Only certain complex formats are used
		$do['in_formats'] = in_array( $this->match_format, $this->complex_formats, true );

		// Must all be true
		$retval = ! in_array( false, $do, true );

		// Return
		return $retval;
	}

	/**
	 * Add complex match conditions
	 *
	 * @todo multi-day/week/month/year
	 *
	 * @since 2.2.0
	 * @param array $match
	 * @return array
	 */
	private function match_complex( $match = array() ) {

		// Initialize
		if ( ! isset( $match['bse'] ) ) {
			$match['bse'] = array();
		}

		// Add these matches
		$match['bse'][ "{$this->match_format}_starts" ] = ( $this->match_values['es'] <= $this->match_values['bs'] );
		$match['bse'][ "{$this->match_format}_ends" ]   = ( $this->match_values['ee'] <= $this->match_values['be'] );

		// Initialize
		if ( ! isset( $match['aes'] ) ) {
			$match['aes'] = array();
		}

		// Add these matches
		$match['aes'][ "{$this->match_format}_starts" ] = ( $this->match_values['es'] >= $this->match_values['be'] );
		$match['aes'][ "{$this->match_format}_ends" ]   = ( $this->match_values['ee'] >= $this->match_values['bs'] );

		// Get the previous format
		$prev_format = isset( $this->match_pattern[ $this->match_index - 1 ] )
			? $this->match_pattern[ $this->match_index - 1 ]
			: '';

		// Skip if empty
		if ( ! empty( $prev_format ) ) {
			$match['bse'][ "{$prev_format}_equals" ] = ( $this->event_start->format( $prev_format ) === $this->boundary_start->format( $prev_format ) );
			$match['aes'][ "{$prev_format}_equals" ] = (   $this->event_end->format( $prev_format ) ===   $this->boundary_end->format( $prev_format ) );
		}

		// Return matches
		return $match;
	}

	/**
	 * Look for matches
	 *
	 * @since 2.2.0
	 */
	private function look() {

		// Loop through possible matches
		foreach ( $this->matches as $key => $to_match ) {

			// Simple match
			if ( 'simple' === $key ) {

				// Accept any simple match
				if ( ! in_array( false, $to_match, true ) ) {
					$this->set_intersect( __METHOD__ );
				}

			// Complex match
			} elseif ( 'complex' === $key ) {

				// Loop through all complex matches
				foreach ( $to_match as $sub_to_match ) {

					// Accept any complex match
					if ( ! in_array( false, $sub_to_match, true ) ) {
						$this->set_intersect( __METHOD__ );
						break;
					}
				}
			}
		}
	}

	/** Recurrence ************************************************************/

	/**
	 * Check if recurrence end date has already passed
	 *
	 * @since 2.2.0
	 * @return bool
	 */
	private function recurrence_end_date_passed() {

		// Turn datetimes to timestamps for easier comparisons
		$recur_end  = ! $this->event->is_empty_date( $this->event->recurrence_end )
			? sugar_calendar_get_datetime_object( $this->event->recurrence_end, $this->event->end_tz )
			: false;

		// Bail if recurring ended after cell start (inclusive of last cell)
		if ( ! empty( $recur_end ) && ( $this->event_start > $recur_end ) ) {
			return true;
		}

		return false;
	}

	/** Setters ***************************************************************/

	/**
	 * Set the Start & End Event DateTime objects
	 *
	 * @since 2.2.0
	 */
	private function set_event_datetimes() {

		// Default to "floating" time zone
		$og_start_tz = $start_tz = $this->boundary_start->getTimezone();
		$og_end_tz   = $end_tz   = $this->boundary_end->getTimezone();

		// All day checks simply match the boundaries
		if ( ! $this->event->is_all_day() && ! sugar_calendar_is_timezone_floating() ) {

			// Maybe use start time zone
			if ( ! empty( $this->event->start_tz ) ) {
				$start_tz = $this->event->start_tz;
			}

			// Maybe use end time zone
			if ( ! empty( $this->event->end_tz ) ) {
				$end_tz = $this->event->end_tz;
			}
		}

		// Turn datetimes to timestamps for easier comparisons
		$this->event_start = sugar_calendar_get_datetime_object( $this->event->start, $start_tz, $og_start_tz );
		$this->event_end   = sugar_calendar_get_datetime_object( $this->event->end,   $end_tz,   $og_end_tz   );
	}

	/**
	 * Set the intersect
	 *
	 * @since 2.2.0
	 * @param string $method
	 */
	private function set_intersect( $method = '' ) {
		$this->intersects  = true;
		$this->intersector = $method;
	}
}
