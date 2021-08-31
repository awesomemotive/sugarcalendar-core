<?php
/**
 * iCalendar Utility
 *
 * @package iCalendar/Utilities
 */
namespace Sugar_Calendar\Utilities\iCalendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Ingest an iCalendar URI and parse it into a multidimensional array.
 *
 * This class accepts a URI in its constructor, and will parse its results into
 * an array. This array can then looped through and converted into the database
 * model of your choosing.
 *
 * @since 1.0.0
 * @param string $file The URI, on construct
 */
class ToArray {

	/** Internal **************************************************************/

	/**
	 * Array of arguments used to change the way this class works.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $args = array(
		'verbose'    => false,
		'log'        => false,
		'expiration' => 3600
	);

	/**
	 * Amount of memory used when certain steps are taken.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $memory = array(
		'start'     => 0,
		'loaded'    => 0,
		'sanitized' => 0,
		'split'     => 0,
		'parsed'    => 0
	);

	/**
	 * Time spent on each action being taken.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $times = array(
		'start'     => 0,
		'loaded'    => 0,
		'sanitized' => 0,
		'split'     => 0,
		'parsed'    => 0
	);

	/** File Properties *******************************************************/

	/**
	 * URI to the file
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_uri = '';

	/**
	 * Location of the file (remote, local, or unknown)
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_location = '';

	/**
	 * Raw contents of the file as a string
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file = '';

	/**
	 * Sanitized contents of the file as a string
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_text = '';

	/**
	 * Sanitized contents of the file as an array
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $file_array = array();

	/**
	 * Calendar data after it has been fully parsed
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $data = array();

	/** Component Counts ******************************************************/

	/**
	 * Number of Event Entries
	 *
	 * Used as an iterator when parsing Component Properties.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $event_count = 0;

	/**
	 * Number of Todo Entries
	 *
	 * Used as an iterator when parsing Component Properties.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $todo_count = 0;

	/**
	 * Number of Journal Entries
	 *
	 * Used as an iterator when parsing Component Properties.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $journal_count = 0;

	/** Line Properties *******************************************************/

	/**
	 * Contents of the current line
	 *
	 * Components or Properties are parsed line-by-line, each one on their own.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $line = '';

	/**
	 * Key of current line
	 *
	 * Everything to the left of the first ":" character
	 *
	 * May be a Component, or a Property of a Component
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $line_key = '';

	/**
	 * Value of the current line
	 *
	 * Everything to the right of the first ":" character
	 *
	 * Usually a single value, but some values contain multiple
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $line_value = '';

	/**
	 * Components of the current line
	 *
	 * The ascendant Components for the current line
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $line_components = array();

	/**
	 * Property Parameters of the current line
	 *
	 * Usually empty. Populated when a Property has multiple Parameters.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $line_parameters = array();

	/**
	 * Values of the current line
	 *
	 * Usually empty. Populated when a Value consists of multiple Values.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $line_values = array();

	/**
	 * Values for temporary fallback usort'ing
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $usort_vals = array();

	/** Public Methods ********************************************************/

	/**
	 * Constructor. Pass in a URI to read a file (local or remote) immediately.
	 *
	 * @since 1.0.0
	 * @param string $file
	 */
	public function __construct( $file = '', $args = array() ) {
		if ( ! empty( $file ) ) {
			$this->read_file( $file, $args );
		}
	}

	/**
	 * Reset an already constructed object back to default values.
	 *
	 * @since 1.0.0
	 */
	public function reset() {
		foreach ( get_class_vars( get_class( $this ) ) as $var => $default ) {
			$this->{$var} = $default;
		}
	}

	/**
	 * Return an array of all Calendar data in the file header
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_calendar_data() {
		return ! empty( $this->data[ 'VCALENDAR' ] )
			? $this->data[ 'VCALENDAR' ]
			: array();
	}

	/**
	 * Return an array of all Event Entries in the file.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_list( $type = 'VEVENT' ) {
		return ! empty( $this->data[ $type ] )
			? $this->data[ $type ]
			: array();
	}

	/**
	 * Return an array of all Event Entries in the file, sorted by Start.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_list_sort( $type = 'VEVENT', $orderby = 'DTSTART', $order = 'ASC' ) {

		// Get events
		$list = $this->get_list( $type );

		// Value'ize the orderby column (silenced because there ain't no other way)
		$value = @ array( $orderby )[ 'formatted' ];

		// Return the sorted list
		return $this->list_sort( $list, $value, $order );
	}

	/**
	 * Return the memory logged at specific actions
	 *
	 * @since 1.0.0
	 * @param string $action
	 * @return array
	 */
	public function get_memory( $action = '' ) {
		return ! empty( $action )
			? $this->memory[ $action ]
			: $this->memory;
	}

	/**
	 * Return the times logged at specific actions
	 *
	 * @since 1.0.0
	 * @param string $action
	 * @return array
	 */
	public function get_times( $action = '' ) {
		return ! empty( $action )
			? $this->times[ $action ]
			: $this->times;
	}

	/**
	 * Return the entire contents of the fully parsed file
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_all_data() {
		return ! empty( $this->data )
			? $this->data
			: array();
	}

	/** Protected Methods *****************************************************/

	/**
	 * Retrieve the contents of a file as a string.
	 *
	 * This method attempts to identify whether a file is local or remote to the
	 * current server, and then tries to get its contents/body.
	 *
	 * @since 1.0.0
	 * @param string $file
	 * @return string
	 */
	protected function load_file( $file = '' ) {

		// Default return value
		$retval = '';

		// Bail if no file or file does not exist
		if ( empty( $file ) ) {
			return $retval;
		}

		// Stash the original file
		$this->file_uri = $this->sanitize_file_uri( $file );

		// Cache
		if ( 'cache' === $this->file_location ) {
			$retval = $this->get_file_from_cache( $this->file_uri );

		// Local file
		} elseif ( 'local' === $this->file_location ) {
			$retval = file_get_contents( $this->file_uri );

			// Try to cache
			$this->set_file_cache( $this->file_uri, $retval );

		// Remote URI
		} elseif ( 'remote' === $this->file_location ) {

			// Explicit WordPress Support (but not fol webcal:// protocol)
			if ( function_exists( 'wp_remote_get' ) && ( 0 !== strpos( $this->file_uri, 'webcal://' ) ) ) {

				// Make the remote request
				$cal    = wp_remote_get( $this->file_uri );

				// Request succeeded, so use the body as the return value
				$retval = ( 200 === wp_remote_retrieve_response_code( $cal ) )
					? wp_remote_retrieve_body( $cal )
					: '';

				// Try to cache
				$this->set_file_cache( $this->file_uri, $retval );
			}
		}

		// Return array of file contents
		return $retval;
	}

	/**
	 * Read an iCalendar file into memory.
	 *
	 * Each of the methods called inside this method are protected, and perform
	 * their own error handling as needed to avoid errors here.
	 *
	 * @since 1.0.0
	 * @param string $file
	 * @param array $args
	 */
	protected function read_file( $file = '', $args = array() ) {

		// Bail if no file to read
		if ( empty( $file ) || ! is_string( $file ) ) {
			return;
		}

		// Maybe reset the class object
		if ( ! empty( $this->data ) ) {
			$this->reset();
		}

		// Maybe override the default arguments
		if ( ! empty( $args ) && is_array( $args ) ) {
			$this->args = $args;
		}

		// Log the start memory
		$this->log( 'start' );

		// Stash the file contents
		$this->file       = $this->load_file( $file );

		// Log the memory used to load this
		$this->log( 'loaded' );

		// Sanitize the file contents into a big text blob
		$this->file_text  = $this->sanitize_file_contents( $this->file );

		// Log the memory used to sanitize
		$this->log( 'sanitized' );

		// Prepare the text blob to be parsed, line by line
		$this->file_array = $this->prepare_file_to_parse( $this->file_text );

		// Log the memory used to split
		$this->log( 'split' );

		// Parse the file from the file array
		$this->parse_file_contents( $this->file_array );

		// Log the memory used to parse
		$this->log( 'parsed' );

		// Log the totals
		$this->log_totals();
	}

	/**
	 * Sanitize the file URI.
	 *
	 * This method checks for remote & local URIs and sanitizes it as needed.
	 *
	 * @since 1.0.0
	 * @param string $file
	 * @return string
	 */
	protected function sanitize_file_uri( $file = '' ) {

		// Get the scheme
		$scheme = @ parse_url( $file, PHP_URL_SCHEME );

		// Cache
		if ( $this->get_file_from_cache( $file ) ) {
			$this->file_location = 'cache';
			$retval              = $file;

		// Remote URL
		} elseif ( in_array( $scheme, $this->allowed_protocols(), true ) ) {
			$this->file_location = 'remote';
			$retval              = $file;

		// Local file
		} elseif ( @ is_file( $file ) ) {
			$this->file_location = 'local';
			$retval              = $this->normalize_path( $file );

		// Unknown
		} else {
			$this->file_location = 'unknown';
			$retval              = $file;
		}

		// Return
		return $retval;
	}

	/**
	 * Sanitize the contents of a file, loaded via the file() function.
	 *
	 * This method accepts a giant blob of text and ensures that it is correctly
	 * formatted to be parsed line-by-line by this class later.
	 *
	 * @since 1.0.0
	 * @param array $file
	 * @return string
	 */
	protected function sanitize_file_contents( $file = '' ) {

		// Bail if no file
		if ( empty( $file ) || ! is_string( $file ) ) {
			return;
		}

		// Join file() into one large text blob
		$retval = $file;

		// First, find any lines from the .ics that are broken up by a hard
		// return, and append the broken line to the line above it.
		$hard_returns = preg_replace( "'([\r\n])[ ]+'", '', $retval );

		// Next, normalize any weird whitespace
		$retval = preg_replace( '/[\r\n]{1,} ([:;])/', '\\1', $hard_returns );

		// Return
		return $retval;
	}

	/**
	 * Break file text up into array, by line-breaks.
	 *
	 * @since 1.0.0
	 * @param string $file_text
	 * @return array
	 */
	protected function prepare_file_to_parse( $file_text = '' ) {

		// Bail if empty or not a string
		if ( empty( $file_text ) || ! is_string( $file_text ) ) {
			return array();
		}

		// Return file text, exploded into an array
		return explode( "\r\n", $file_text );
	}

	/**
	 * Check whether the currently loaded file contains the correct starter
	 * Component.
	 *
	 * @since 1.0.0
	 * @param array $file_array
	 * @return bool
	 */
	protected function check_file_format( $file_array = array() ) {
		return ! stristr( $file_array[ 0 ], 'BEGIN:VCALENDAR' )
			? false
			: true;
	}

	/** Sanitization Methods **************************************************/

	/**
	 * Sanitizes a text value.
	 *
	 * Pretty much any value will start off as a string, but you should only
	 * use this method for values that you know actually should be strings when
	 * finalized on the Component or Property with which it is associated.
	 *
	 * You may also want to use this for strings that have maximum lengths, or
	 * that contain comma separated values allowing you to explode them cleanly.
	 *
	 * @since 1.0.0
	 * @param string $value
	 * @return string
	 */
	private function sanitize_text( $value = '' ) {

		// First, make sure lines are actual lines
		$retval = str_replace( '\n', "\r\n", $value );

		// Always strip slashes at the end
		$retval = stripslashes( $retval );

		// Return the sanitized text field
		return trim( $retval );
	}

	/**
	 * Convert a value to non-negative integer.
	 *
	 * @since 1.0.0
	 * @param mixed $maybe_int Value to convert to a non-negative integer.
	 * @return int A non-negative integer.
	 */
	private function sanitize_absint( $maybe_int = 0 ) {
		return abs( intval( $maybe_int ) );
	}

	/**
	 * Make sure an integer is within 2 possible other integers.
	 *
	 * Note that unlike sanitize_absint() these numbers can be negative.
	 *
	 * This is particularly useful with day/week/month ranges, where a negative
	 * value or a value being out of bounds is extremely bad.
	 *
	 * @since 1.0.0
	 * @param int $value
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	private function sanitize_ranged_int( $value = 0, $min = 0, $max = 366 ) {

		// Default return value
		$retval = $value;

		// Number can't be less than min
		if ( (int) $value <= (int) $min ) {
			$retval = $min;

		// Number can't be more than max
		} elseif ( (int) $value >= (int) $max ) {
			$retval = $max;
		}

		// Return the intval
		return int_val( $retval );
	}

	/**
	 * Make sure a value is one of the allowed ones.
	 *
	 * If not, the default will be used.
	 *
	 * @since 1.0.0
	 * @param string $value
	 * @param array  $allowed
	 * @param string $default
	 * @return string
	 */
	private function sanitize_allowed_values( $value = '', $allowed = array(), $default = '' ) {

		// Default return value
		$retval = $value;

		// Convert to uppercase to compare
		$uvalue   = strtoupper( $value );
		$uallowed = array_map( 'strtoupper', $allowed );

		// Maybe fallback to default
		if ( ! in_array( $uvalue, $uallowed, true ) ) {
			$retval = $default;
		}

		// Return
		return $retval;
	}

	/**
	 * Sanitize the Value of a Property, Parameter, or Multi-Value, based on the
	 * key for that specific Value.
	 *
	 * Developers Note: for now, this is just a big, ugly switch() statement. In
	 * the future, these Keys should be explicitly defined as their own type of
	 * class with their own attributes to self-manage their folding & unfolding.
	 *
	 * @since 1.0.0
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
	private function sanitize_value( $key = '', $value = '' ) {

		// Sanitize values based on their key type
		switch ( $key ) {

			// Attachment (binary)
			case 'ATTACH' :
				$retval = $value; // @todo
				break;

			// Text Properties
			case 'CALSCALE' :    // Default GREGORIAN
			case 'CATEGORIES' :  // Possibly comma separated
			case 'CLASS' :       // PUBLIC, PRIVATE, CONFIDENTIAL, ...
			case 'COMMENT' :     // Multiline
			case 'DESCRIPTION' : // Multiline
			case 'LOCATION' :    // Multiline
			case 'METHOD' :      // PUBLISH, REQUEST, ...
			case 'PRODID' :
			case 'VERSION' :

			// Text Parameters
			case 'ALTREP' :
			case 'CN' :
			case 'CUTYPE' :      // INDIVIDUAL, GROUP, RESOURCE, ROOM, UNKNOWN, ...
			case 'DELFROM' :
			case 'DELEGATED-FROM' :
			case 'DELTO' :
			case 'DELEGATED-TO' :
			case 'DIR' :         // URI
			case 'ENCODING' :    // 8BIT, BASE64, ...
			case 'FMTTYPE' :
			case 'FBTTYPE' :     // FREE, BUSY, BUSY-UNAVAILABLE, BUSY-TENTATIVE, ...
			case 'LANGUAGE' :    // us-EN, en, no, ...
			case 'MEMBER' :      // mailto:email@example.com

			/**
			 * Any
			 * - NEEDS-ACTION, ACCEPTED, DECLINED, TENTATIVE, DELEGATED
			 *
			 * VEVENT
			 * - NEEDS-ACTION, ACCEPTED, DECLINED, TENTATIVE, DELEGATED,
			 *   COMPLETED, IN-PROCESS
			 *
			 * VTODO
			 * - NEEDS-ACTION, ACCEPTED, DECLINED
			 *
			 * VJOURNAL
			 * - NEEDS-ACTION
			 */
			case 'PARTSTAT' :
//				$allowed = array( 'NEEDS-ACTION', 'ACCEPTED', 'DECLINED', 'TENTATIVE', 'DELEGATED', 'COMPLETED', 'IN-PROGRESS' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );
//				break;

			// THISANDPRIOR, THISANDFUTURE
			case 'RANGE' :
//				$allowed = array( 'THISANDPRIOR', 'THISANDFUTURE' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );
//				break;

			// START, END
			case 'RELATED' :
//				$allowed = array( 'START', 'END' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );
//				break;

			// PARENT, CHILD, SIBLING
			case 'RELTYPE' :
//				$allowed = array( 'PARENT', 'CHILD', 'SIBLING' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );
//				break;

			// CHAIR, REQ-PARTICIPANT, OPT-PARTICIPANT, NON-PARTICIPANT
			case 'ROLE' :
//				$allowed = array( 'CHAIR', 'REQ-PARTICIPANT', 'OPT-PARTICIPANT', 'NON-PARTICIPANT' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );
//				break;

			// TRUE, FALSE
			case 'RSVP' :
//				$allowed = array( 'TRUE', 'FALSE' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );
//				break;

			case 'RELATED-TO' :
			case 'SENTBY' :
			case 'TRIGREL' :
			case 'TZID' :
			case 'UID' :

			/**
			 * https://tools.ietf.org/html/rfc2445#section-4.2.20
			 *
			 * These need to figured out, too:
			 * - BINARY, BOOLEAN, CAL-ADDRESS, DATE, DATE-TIME, DURATION, FLOAT,
			 *   INTEGER, PERIOD, RECUR, TEXT, TIME, URI, UTC-OFFSET, ...
			 * @todo
			 */
			case 'VALUE' :
			case 'VALUETYPE' :

			case 'IANA' :
				$retval = $this->sanitize_text( $value );
				break;

			/**
			 * Start RRULE
			 */

			// SECONDLY, MINUTELY, HOURLY, DAILY, WEEKLY, MONTHLY, YEARLY
			case 'FREQ' :
//				$allowed = array( 'SECONDLY', 'MINUTELY', 'HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );
//				break;

			// 37.386013;-122.082932
			case 'GEO' :
//				$retval = $value;
//				break;

			// 0 - 9
			case 'COUNT' :
			case 'INTERVAL' :
//				$retval = $this->sanitize_ranged_int( $value, 0, 9 );
//				break;

			// 0 - 59
			case 'BYSECOND' :
			case 'BYMINUTE' :
//				$retval = $this->sanitize_ranged_int( $value, 0, 59 );
//				break;

			// 0 - 23
			case 'BYHOUR' :
//				$retval = $this->sanitize_ranged_int( $value, 0, 23 );
//				break;

			// (+/-, 0-9) SU, MO, TU, WE, TH, FR, SA
			// @todo
			case 'BYDAY' :
//				$allowed = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );
//				break;

			// 1 / 31 || -1 / -31
			case 'BYMONTHDAY' :
//				$retval = $this->sanitize_ranged_int( $value, -31, 31 );
//				break;

			// 1 / 366 || -1 / -366
			case 'BYYEARDAY' :
//				$retval = $this->sanitize_ranged_int( $value, -366, 366 );
//				break;

			// 1 / 53 || -1 / -53
			case 'BYWEEKNO' :
//				$retval = $this->sanitize_ranged_int( $value, -53, 53 );
//				break;

			// 1 / 12 || -1 / -12
			case 'BYMONTH' :
//				$retval = $this->sanitize_ranged_int( $value, 1, 12 );
//				break;

			// 1 / 366 || -1 / -366
			case 'BYSETPOS' :
//				$retval = $this->sanitize_ranged_int( $value, -366, 366 );
//				break;

			// SU, MO, TU, WE, TH, FR, SA
			case 'WKST' :
//				$allowed = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );
//				$retval  = $this->sanitize_allowed_values( $value, $allowed );

			// Date/Time
			case 'UNTIL' :
//				$retval = $this->ical_date_to_unix( $value );
//				break;

			case 'RECUR' :
				$retval = $value;
				break;

			case 'RRULE' :
				$retval = $this->parse_multiple_values();
				break;

			/**
			 * End RRULE
			 */

			// Time
			case 'DTEND' :
			case 'DTSTART' :
			case 'DTSTAMP' :
			case 'CREATED' :
			case 'FREEBUSY' :
			case 'LAST-MODIFIED' :
				$retval = $this->ical_date_to_unix( $value );
				break;

			// Non-negative Integer
			case 'REPEAT' :
			case 'SEQUENCE' :
				$retval = $this->sanitize_absint( $value );
				break;

			// 0 to 100
			case 'PERCENT-COMPLETE' :
				$retval = $this->sanitize_absint( $value );

			// 0 to 9
			case 'PRIORITY' :
				$retval = $this->sanitize_absint( $value );

			// Pass values through as-is if unknown
			// @todo continue to identify and explicitly handle these cases
			default :
				$retval = $value;
		}

		return $retval;
	}

	/** Protected Parsers *****************************************************/

	/**
	 * Parse the entire file.
	 *
	 * This method accepts an array of lines of a file, and loops through them
	 * and parses them each for iCalendar Components, Properties, Parameters,
	 * and their respective values.
	 *
	 * @since 1.0.0
	 * @param array $file_array
	 */
	protected function parse_file_contents( $file_array = array() ) {

		// Bail if nothing to parse
		if ( empty( $file_array ) || ! is_array( $file_array ) ) {
			return;
		}

		// Bail if not an iCalendar file format
		if ( ! $this->check_file_format( $file_array ) ) {
			return;
		}

		// Set counts to -1
		$this->event_count   = -1;
		$this->todo_count    = -1;
		$this->journal_count = -1;

		// Loop through array of file contents one line at a time
		foreach ( $file_array as $line ) {

			// Trim whitespace from start & end of line
			$line = trim( $line );

			// Skip if line is empty
			if ( empty( $line ) ) {
				continue;
			}

			// Set the line
			$this->line = $line;

			// Parse this line
			$this->parse_line( $line );
		}
	}

	/**
	 * Parse a single line of a file.
	 *
	 * @since 1.0.0
	 * @param string $line
	 */
	protected function parse_line( $line = '' ) {

		// Bail if empty line or line is not a string
		if ( empty( $line ) || ! is_string( $line ) ) {
			return;
		}

		// Get key for line (includes
		$this->line_key   = $this->get_line_key( $line );

		// Get value for line
		$this->line_value = $this->get_line_value( $line );

		// Check for Components or Properties of
		switch ( $this->line ) {

			// Single Event Start
			case 'BEGIN:VEVENT' :
				$this->event_count = $this->event_count + 1;
				array_push( $this->line_components, 'VEVENT' );
				break;

			// Single Todo Start
			case 'BEGIN:VTODO' :
				$this->todo_count = $this->todo_count + 1;
				array_push( $this->line_components, 'VTODO' );
				break;

			// Single Todo Start
			case 'BEGIN:VJOURNAL' :
				$this->journal_count = $this->journal_count + 1;
				array_push( $this->line_components, 'VJOURNAL' );
				break;

			// Other Single Starting Values
			case 'BEGIN:DAYLIGHT' :
			case 'BEGIN:STANDARD' :
			case 'BEGIN:VCALENDAR' :
			case 'BEGIN:VTIMEZONE' :
				$line_component = $this->sanitize_value( $this->line_key, $this->line_value );
				array_push( $this->line_components, $line_component );
				break;

			// All Endings
			case 'END:DAYLIGHT' :
			case 'END:STANDARD' :
			case 'END:VCALENDAR' :
			case 'END:VEVENT' :
			case 'END:VTODO' :
			case 'END:VTIMEZONE' :
				array_pop( $this->line_components );
				break;

			// All Properties
			default :
				$this->add_value();
				break;
		}
	}

	/**
	 * Parse the Property from the current line key.
	 *
	 * @since 1.0.0
	 * @param $key string
	 * @param $value string
	 * @return array
	 */
	protected function parse_property( $key = '', $value = '' ) {

		// Split by semi-colons
		$this->line_parameters = explode( ';', $key );

		// Bail if no multiple Propreties to parse
		if ( count( $this->line_parameters ) <= 1 ) {

			// Reset line Parameters & Values
			$this->line_parameters = array();
			$this->line_values     = array();

			// Sanitize the single value
			$retval = $this->set_value( $key, $value );

			// Return the key/value pair
			return array(
				$key,
				$retval
			);
		}

		// Sanitize the initial value as the first item in the array
		$retval = $this->set_value( $this->line_parameters[0], $value );

		// Parse (and sanitize) any other Property Parameters
		$retval['params'] = $this->parse_parameters( $this->line_parameters );

		// Return Key & Value
		return array( $this->line_parameters[ 0 ], $retval );
	}

	/**
	 * Parse the Property from the current line key.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function parse_multiple_values( $separator = ';' ) {

		// Split by separator
		$this->line_values = explode( $separator, $this->line_value );

		// Return parsed & sanitized Property Parameters
		return $this->parse_parameters( $this->line_values );
	}

	/**
	 * Parse the Parameters for the current Property.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function parse_parameters( $parameters = array() ) {

		// Default return value
		$retval = array();

		// Bail if no Parameters
		if ( empty( $parameters ) ) {
			return $retval;
		}

		// Loop through Parameters
		foreach ( $parameters as $params ) {

			// Look for up to 2 total Parameters (key/value)
			$p = explode( '=', $params, 2 );

			// Skip if no Parameters
			if ( 1 === count( $p ) ) {
				continue;
			}

			// Set key/value from Parameters
			list( $key, $value ) = $p;

			// Sanitize the value
			$retval[ $key ] = $this->set_value( $key, $value );
		}

		// Return Property Parameters
		return $retval;
	}

	/** Values ****************************************************************/

	/**
	 * Set the value for a Component, Property, or Parameter
	 *
	 * For the sake of keeping this flexible, values consist of multiple
	 * attributes, including their original value and the formatted one.
	 *
	 * @since 1.0.0
	 * @param string $key
	 * @param string $value
	 * @return array
	 */
	private function set_value( $key = '', $value = '' ) {

		// Always return the formatted result
		$retval = array(
			'formatted' => $this->sanitize_value( $key, $value )
		);

		// Maybe add the original
		if ( ! empty( $this->args['verbose'] ) && ( true === $this->args['verbose'] ) ) {
			$retval['original'] = $value;
		}

		// Return the value
		return $retval;
	}

	/**
	 * Add a key & value to the correct
	 *
	 * @since 1.0.0
	 */
	private function add_value() {

		// Bail if no Component
		if ( empty( $this->line_components ) ) {
			return;
		}

		// Parse the Property
		list( $this->line_key, $this->line_value ) = $this->parse_property( $this->line_key, $this->line_value );

		// Get the last Component
		$line_component = end( $this->line_components );

		// Reset the internal Component pointer
		reset( $this->line_components );

		// Type of Component
		switch ( $line_component ) {

			// Todo
			case 'VTODO':
				$add_to = &$this->data[
					$line_component ][
						$this->todo_count ][
							$this->line_key
					];
				break;

			// Event
			case 'VEVENT':
				$add_to = &$this->data[
					$line_component ][
						$this->event_count ][
							$this->line_key
					];
				break;

			// Event
			case 'VJOURNAL':
				$add_to = &$this->data[
					$line_component ][
						$this->journal_count ][
							$this->line_key
					];
				break;

			// Calendar-wide Timezone
			case 'VTIMEZONE' :
			case 'STANDARD' :
			case 'DAYLIGHT' :

				// Standard & Daylight are hierarchical (wee!)
				if ( in_array( $line_component, array( 'STANDARD', 'DAYLIGHT' ), true ) ) {
					$add_to = &$this->data[
						'VTIMEZONE' ][
							$line_component ][
								$this->line_key
						];

				// All others belong to Timezone itself
				} else {
					$add_to = &$this->data[
						'VTIMEZONE' ][
							$this->line_key
						];
				}

				break;

			// Root
			default:
				$add_to = &$this->data[
					$line_component ][
						$this->line_key
					];

				break;
		}

		//
		$add_to = $this->line_value;
	}

	/** Key & Value ***********************************************************/

	/**
	 * Get the key from a string.
	 *
	 * @since 1.0.0
	 * @param string $line
	 * @return string
	 */
	protected function get_line_key( $line = '' ) {

		// Fallback to current line
		if ( empty( $line ) && ! empty( $this->line ) && is_string( $this->line ) ) {
			$line = $this->line;
		}

		// Bail if no line
		if ( empty( $line ) ) {
			return '';
		}

		// Parse the line according to the first colon
		$matches = $this->split_by_separator( $line, ':' );

		// Bail if no value
		if ( empty( $matches[1] ) ) {
			return rtrim( $line, ':' );
		}

		// Return key
		return trim( $matches[1] );
	}

	/**
	 * Get the value from a string.
	 *
	 * @since 1.0.0
	 * @param string $line
	 * @return string
	 */
	protected function get_line_value( $line = '' ) {

		// Fallback to current line
		if ( empty( $line ) && ! empty( $this->line ) && is_string( $this->line ) ) {
			$line = $this->line;
		}

		// Bail if no line
		if ( empty( $line ) ) {
			return '';
		}

		// Parse the line according to the first colon
		$matches = $this->split_by_separator( $line, ':' );

		// Bail if no value
		if ( empty( $matches[2] ) ) {
			return '';
		}

		// Return key
		return trim( $matches[2] );
	}

	/** Formatting Methods ****************************************************/

	/**
	 * Split a string in half by another string.
	 *
	 * @since 1.0.0
	 * @param string $text
	 * @param string $separator
	 * @return array
	 */
	protected function split_by_separator( $text = '', $separator = ':' ) {

		// Default return value
		$retval = array( $text );

		// Only split if not empty and separator exists in text
		if ( ! empty( $text ) && ( false !== strpos( $text, $separator ) ) ) {

			// The pattern
			$pattern = "/([^{$separator}]+)[{$separator}]([\w\W]+)/";

			// Populate the return value
			preg_match( $pattern, $text, $retval );
		}

		// Return array of split results
		return $retval;
	}

	/**
	 * Accepts an iCalendar format date/time value, and returns a unix timestamp.
	 *
	 * @since 1.0.0
	 * @param string $ical_date
	 * @return int
	 */
	protected function ical_date_to_unix( $ical_date = '' ) {

		// Maybe strip the "T" for time, "Z" for time zone, and spaces
		$to_strip = array( 'T', 'Z', ' ' );
		$replace  = str_replace( $to_strip, '', $ical_date );

		// The pattern to break the iCalendar date apart by
		$pattern =    '/([0-9]{4})'
					. '([0-9]{2})'
					. '([0-9]{2})'
					. '([0-9]{0,2})'
					. '([0-9]{0,2})'
					. '([0-9]{0,2})/';

		// Default date array
		$date = array();

		// Split the string up into an array of date & time values
		preg_match( $pattern, $replace, $date );

		// Bail if the date array is empty
		if ( empty( $date ) ) {
			return 'null';
		}

		// Do not allow dates beyond the Unix Epoch
		if ( $date[ 1 ] < 1970 ) {
			$date[ 1 ] = 1970;
		}

		// Do not allow negative values, because we do not support backwards time
		$date = array_map( array( $this, 'sanitize_absint' ), $date );

		// Convert date array to GMT Unix time
		$retval = gmmktime(
			$date[ 4 ],
			$date[ 5 ],
			$date[ 6 ],
			$date[ 2 ],
			$date[ 3 ],
			$date[ 1 ]
		);

		// Return a newly made time value with the date array chunks
		return $retval;
	}

	/** File Cache ************************************************************/

	/**
	 * Attempt to get the contents of a file from a cache.
	 *
	 * @since 1.0.0
	 * @param string $uri
	 * @return mixed
	 */
	private function get_file_from_cache( $uri = '' ) {

		// Get the hash of the URI
		$hash = $this->hash_uri( $uri );

		// Support WordPress directly
		if ( function_exists( 'get_transient' ) ) {
			$retval = get_transient( $hash );

		// No fallback
		} else {
			$retval = false;
		}

		// Return possibly cached file
		return $retval;
	}

	/**
	 * Attempt to set the contents of a URI in the cache.
	 *
	 * @since 1.0.0
	 * @param string $uri
	 * @param string $contents
	 * @return bool
	 */
	private function set_file_cache( $uri = '', $contents = '' ) {

		// Get the hash of the URI
		$hash = $this->hash_uri( $uri );

		// Support WordPress directly
		if ( function_exists( 'set_transient' ) ) {

			// Allow custom cache expiration (default 60 minutes)
			$expiration = ! empty( $this->args['expiration'] )
				? $this->sanitize_absint( $this->args['expiration'] )
				: HOUR_IN_SECONDS;

			// Attempt to cache
			$retval = set_transient( $hash, $contents, $expiration );

		// No fallback
		} else {
			$retval = false;
		}

		// Return possibly cached file
		return $retval;
	}

	/**
	 * Create a hash from a URI
	 *
	 * @since 1.0.0
	 * @param string $uri
	 * @return string
	 */
	private function hash_uri( $uri = '' ) {
		$algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
		$salt = defined( 'AUTH_SALT' ) && AUTH_SALT ? AUTH_SALT : (string) rand();

		// Return hash
		return 'ical_' . hash_hmac( $algo, $uri, $salt );
	}

	/**
	 * Log memory usage when a certain action happens.
	 *
	 * @since 1.0.0
	 * @param string $action
	 */
	private function log( $action = '' ) {

		// Bail if not logging
		if ( empty( $this->args['log'] ) ) {
			return;
		}

		// Logs
		$this->memory[ $action ] = memory_get_usage();
		$this->times[ $action ]  = microtime();
	}

	/**
	 * Log total time & usage.
	 *
	 * @since 1.0.0
	 */
	private function log_totals() {

		// Bail if not logging
		if ( empty( $this->args['log'] ) ) {
			return;
		}

		// Total memory used
		$this->memory['used']   = $this->memory['parsed'] - $this->memory['start'];

		// Total elapsed time
		$this->times['elapsed'] = ( substr( $this->times['parsed'], 11 ) - substr( $this->times['start'], 11 ) ) + ( substr( $this->times['parsed'], 0, 9 ) - substr( $this->times['start'], 0, 9 ) );
	}

	/** Helpers/Callbacks/Abstractions ****************************************/

	/**
	 * Return an array of allowed URI protocols.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function allowed_protocols() {

		// Support WordPress directly
		if ( function_exists( 'wp_allowed_protocols' ) ) {
			$retval = wp_allowed_protocols();

		// Protocols that might contain a valid .ics file
		} else {
			$retval = array(
				'http',
				'https',
				'ftp',
				'ftps',
				'mailto',
				'news',
				'irc',
				'gopher',
				'nntp',
				'feed',
				'telnet',
				'mms',
				'rtsp',
				'sms',
				'svn',
				'tel',
				'fax',
				'xmpp',
				'webcal',
				'urn'
			);
		}

		// Return array of allowed protocols
		return (array) $retval;
	}

	/**
	 * Takes the local path for a file and makes sure it is compatible with
	 * whatever the current operating system uses.
	 *
	 * @since 1.0.0
	 * @param string $file_path
	 * @return string
	 */
	private function normalize_path( $file_path = '' ) {

		// Support WordPress directly
		if ( function_exists( 'wp_normalize_path' ) ) {
			$retval = wp_normalize_path( $file_path );

		// Fallback to PHP realpath()
		} else {
			$retval = realpath( $file_path );
		}

		// Return the normalized path
		return $retval;
	}

	/**
	 * Sort an array of arrays or objects, by some value, in some order.
	 *
	 * @since 1.0.0
	 * @param array $list
	 * @param mixed $orderby
	 * @param mixed $order
	 * @return array
	 */
	private function list_sort( $list = array(), $orderby = array(), $order = 'ASC' ) {

		// Support WordPress directly
		if ( function_exists( 'wp_list_sort' ) ) {
			$retval = wp_list_sort( $list, $orderby, $order );

		// Fallback to our own implementation
		} else {

			// Store these for sorting
			$this->usort_vals = array(
				'orderby' => $orderby,
				'order'   => $order
			);

			// Setup the return value
			$retval = $list;

			// Sort the return values
			usort( $retval, array( $this, 'usort_compare' ) );
		}

		// Return the sorted list
		return $retval;
	}

	/**
	 * A simple usort callback used by get_list_sort() to sort all items
	 * by a value, defaulting to DTSTART.
	 *
	 * @since 1.0.0
	 * @param mixed $a
	 * @param mixed $b
	 * @return int
	 */
	private function usort_compare( $a = false, $b = false ) {

		// First
		$one = isset( $a[ $this->usort_vals['orderby'] ][ 'formatted' ] )
			? $a[ $this->usort_vals['orderby'] ][ 'formatted' ]
			: $a[ $this->usort_vals['orderby'] ];

		// Second
		$two = isset( $b[ $this->usort_vals['orderby'] ][ 'formatted' ] )
			? $b[ $this->usort_vals['orderby'] ][ 'formatted' ]
			: $b[ $this->usort_vals['orderby'] ];

		// Order
		$results = ( 'ASC' === $this->usort_vals['order'] )
			? array( 1, -1 )
			: array( -1, 1 );

		// Compare the values and figure out which direction to bump
		$bump = ( $one < $two )
			? $results[0]
			: $results[1];

		// Return the sort direction
		return (int) $bump;
	}
}
