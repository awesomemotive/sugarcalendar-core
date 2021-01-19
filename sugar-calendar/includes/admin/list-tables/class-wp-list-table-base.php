<?php
/**
 * Calendar List Table Base Class
 *
 * @package Plugins/Site/Events/Admin/ListTables/Base
 */
namespace Sugar_Calendar\Admin\Mode;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Include the main list table class if it's not included
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// No list table class, so something went very wrong
if ( class_exists( '\WP_List_Table' ) ) :
/**
 * Event table
 *
 * This list table is responsible for showing events in a traditional table,
 * even though it extends the `WP_List_Table` class. Tables & lists & tables.
 */
class Base_List_Table extends \WP_List_Table {

	/**
	 * The mode of the current view
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $mode = 'list';

	/**
	 * What day does a calendar week start on?
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $start_of_week = '1';

	/**
	 * The days of the week
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $week_days = array();

	/**
	 * Number of days per week
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $day_count = 7;

	/**
	 * How should dates be formatted?
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $date_format = 'F j, Y';

	/**
	 * How should times be formatted?
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $time_format = 'g:i a';

	/**
	 * The beginning boundary for the current view
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $view_start = '';

	/**
	 * The end boundary for the current view
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $view_end = '';

	/**
	 * Duration of view, from start to end
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	public $view_duration = 0;

	/**
	 * The current time zone object, derived from $view_timezone
	 *
	 * @since 2.1.0
	 *
	 * @var object
	 */
	public $view_timezone = false;

	/**
	 * The items with pointers
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $pointers = array();

	/**
	 * The start year being viewed (for list-mode)
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $start_year = 2020;

	/**
	 * The year being viewed
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $year = 2020;

	/**
	 * The month being viewed
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $month = 1;

	/**
	 * The day being viewed
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $day = 1;

	/**
	 * The exact day being viewed based on year/month/day
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $today = '';

	/**
	 * The time zone for the current view
	 *
	 * @since 2.1.0
	 *
	 * @var object
	 */
	protected $timezone = 'UTC';

	/**
	 * The timestamp for this exact microsecond.
	 *
	 * We cache this as a reference to avoid repeated calls to time(),
	 * particularly when the accuracy of time comparisons is critical.
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $now = 0;

	/**
	 * Whether the week column is shown
	 *
	 * @since 2.0.0
	 *
	 * @var boolean
	 */
	protected $show_week_column = false;

	/**
	 * Current item details
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $item = null;

	/**
	 * The events query for the current view items
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	protected $query = null;

	/**
	 * The events query for recurring items
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	protected $recurring_query = null;

	/**
	 * Unix time month start
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $grid_start = 0;

	/**
	 * Unix time month end
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $grid_end = 0;

	/**
	 * The properties for the current cell
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $current_cell = array(
		'day'    => null,
		'month'  => null,
		'year'   => null,
		'start'  => null,
		'end'    => null,
		'index'  => null,
		'offset' => null
	);

	/**
	 * The properties for all of the cells
	 *
	 * @since 2.1.3
	 *
	 * @var array
	 */
	protected $cells = array();

	/**
	 * Array of queried items, filtered, usually by status
	 *
	 * @since 2.1.2
	 *
	 * @var array
	 */
	protected $filtered_items = array();

	/**
	 * The main constructor method
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {

		// Ready the pointer content
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_pointers_footer' ) );

		// Set class properties
		$this->init_globals();
		$this->init_timezone();
		$this->init_boundaries();
		$this->init_week_days();
		$this->init_max();
		$this->init_modes();

		// Setup arguments
		$r = wp_parse_args( $args, array(
			'singular' => esc_html__( 'Event',  'sugar-calendar' ),
			'plural'   => esc_html__( 'Events', 'sugar-calendar' )
		) );

		// Pass arguments into parent
		parent::__construct( $r );
	}

	/** Init ******************************************************************/

	/**
	 * Force the order and orderby, so default view is correct
	 *
	 * @since 2.0.0
	 */
	protected function init_globals() {
		$_GET['order']   = $this->get_order();
		$_GET['orderby'] = $this->get_orderby();
	}

	/**
	 * Set the time zone
	 *
	 * @since 2.1.2
	 */
	protected function init_timezone() {
		$this->timezone = sugar_calendar_get_timezone();
	}

	/**
	 * Set the boundaries
	 *
	 * @since 2.0.0
	 */
	protected function init_boundaries() {

		// Set now once, so everything uses the same timestamp
		$this->now = $this->get_current_time();

		// Set formatting options
		$this->start_of_week = $this->get_start_of_week();
		$this->date_format   = $this->get_date_format();
		$this->time_format   = $this->get_time_format();

		// Set year, month, & day
		$this->year  = $this->get_year();
		$this->month = $this->get_month();
		$this->day   = $this->get_day();

		// Set list-mode specific year
		$this->start_year = $this->get_start_year();

		// Set "today" based on current request
		$this->today = strtotime( "{$this->year}/{$this->month}/{$this->day}" );
	}

	/**
	 * Set the modes
	 *
	 * @since 2.0.0
	 */
	protected function init_modes() {
		$this->modes = array(
			'month' => esc_html__( 'Month', 'sugar-calendar' ),
			'week'  => esc_html__( 'Week',  'sugar-calendar' ),
			'day'   => esc_html__( 'Day',   'sugar-calendar' ),
			'list'  => esc_html__( 'List',  'sugar-calendar' )
		);
	}

	/**
	 * Set the maximum number of items per iteration
	 *
	 * @since 2.0.0
	 */
	protected function init_max() {
		$this->max = sugar_calendar_get_user_preference( 'sc_events_max_num', 100 );
	}

	/**
	 * Set the days of the week
	 *
	 * @since 2.0.0
	 */
	protected function init_week_days() {

		// Day values
		$days = array(
			'0' => 'sunday',
			'1' => 'monday',
			'2' => 'tuesday',
			'3' => 'wednesday',
			'4' => 'thursday',
			'5' => 'friday',
			'6' => 'saturday'
		);

		// Get the day index
		$index  = array_search( $this->start_of_week, array_keys( $days ) );
		$start  = array_slice( $days, $index, count( $days ), true );
		$finish = array_slice( $days, 0,      $index,         true );

		// Set days for week
		$this->week_days = $start + $finish;
	}

	/** Setters ***************************************************************/

	/**
	 * Set the start, end, and duration of the current view
	 *
	 * @since 2.0.0
	 */
	protected function set_view( $start = '', $end = '' ) {

		// Convert to timestamps
		$start_time = strtotime( $start );
		$end_time   = strtotime( $end   );

		// Set view boundaries
		$this->view_start    = ( $end_time > $start_time ) ? $start : $end;
		$this->view_end      = ( $end_time < $start_time ) ? $start : $end;
		$this->view_duration = ( $end_time - $start_time );

		// Set view time zone
		$this->view_timezone = sugar_calendar_get_timezone_object( $this->timezone );
	}

	/**
	 * Set the filtered items
	 *
	 * @since 2.1.2
	 */
	protected function set_filtered_items() {

		// Get the filter
		$filter = $this->get_items_filter();

		// No queried items
		if ( empty( $this->query->items ) ) {
			$this->filtered_items = array();

		// No filter
		} elseif ( empty( $filter ) ) {
			$this->filtered_items = $this->query->items;

		// Filter queried items
		} else {
			$this->filtered_items = wp_list_filter( $this->query->items, $filter );
		}
	}

	/** Getters ***************************************************************/

	/**
	 * Return the post type of the current screen
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_screen_post_type() {
		return ! empty( $this->screen->post_type )
			? $this->screen->post_type
			: '';
	}

	/**
	 * Return the page of the current screen
	 *
	 * @since 2.0.0
	 */
	protected function get_page() {
		return 'sugar-calendar';
	}

	/**
	 * Return the primary post type.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_primary_post_type() {
		return sugar_calendar_get_event_post_type_id();
	}

	/**
	 * Return the base URL
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_base_url() {
		return sugar_calendar_get_admin_base_url();
	}

	/**
	 * Return array of filters used on queried items
	 *
	 * @since 2.1.2
	 *
	 * @return array
	 */
	protected function get_items_filter() {

		// Get the status
		$status = $this->get_status();

		// Bail if viewing all
		if ( 'all' === $status ) {
			return array();
		}

		// Return filter by status
		return array(
			'status' => $this->get_status()
		);
	}

	/**
	 * Return a properly formatted, multi-dimensional array of event counts,
	 * grouped by status.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_item_counts() {

		// Default return value
		$retval = array(
			'total' => 0
		);

		// Default statuses
		$statuses = array();

		// Items to count
		if ( ! empty( $this->query->items ) ) {

			// Pluck all queried statuses
			$statuses = wp_list_pluck( $this->query->items, 'status' );

			// Get unique statuses only
			$statuses = array_unique( $statuses );

			// Set total to count of all items
			$retval['total'] = count( $this->query->items );

			// Loop through statuses
			foreach ( $statuses as $status ) {

				// Get items of this status
				$items = wp_filter_object_list(
					$this->query->items,
					array(
						'status' => $status
					)
				);

				// Add count to return value
				$retval[ $status ] = count( $items );
			}
		}

		// Filter & return
		return apply_filters( 'sugar_calendar_list_table_get_item_counts', $retval, $statuses );
	}

	/**
	 * Return array of intervals.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_recurrence_types() {
		return sugar_calendar_get_recurrence_types();
	}

	/**
	 * Return array of date query arguments, used for `date_query` parameter.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_date_query_args() {
		return sugar_calendar_get_date_query_args( $this->get_mode(), $this->view_start, $this->view_end );
	}

	/**
	 * Return a human-readable time difference as a string.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $older_date The earlier time from which you're calculating
	 *                               the time elapsed. Enter either as an integer Unix timestamp,
	 *                               or as a date string of the format 'Y-m-d H:i:s'.
	 * @param int|bool   $newer_date Optional. Unix timestamp of date to compare older
	 *                               date to. Default: false (current time).
	 *
	 * @return string String representing the time since the older date, eg
	 *         "2 hours and 50 minutes".
	 */
	protected function get_human_diff_time( $older_date, $newer_date = false ) {
		return sugar_calendar_human_diff_time( $older_date, $newer_date );
	}

	/**
	 * Return a human-readable time difference as a string.
	 *
	 * @since 2.1.0
	 *
	 * @param string $timezone1 First Olson time zone ID.
	 * @param string $timezone2 Optional. Default: 'UTC' Second Olson time zone ID.
	 * @param mixed  $datetime  Optional. Default: 'now' Time to use for diff
	 *
	 * @return string String representing the time difference - "2.5 hours"
	 */
	protected function get_human_diff_timezone( $timezone1 = '', $timezone2 = 'UTC', $datetime = 'now' ) {
		return sugar_calendar_human_diff_timezone( $timezone1, $timezone2, $datetime );
	}

	/**
	 * Return the color of an event.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_item_color( $object ) {
		return sugar_calendar_get_event_color( $object->id, $object->type );
	}

	/**
	 * Get the current time
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_current_time() {
		return sugar_calendar_get_request_time( 'timestamp', $this->timezone );
	}

	/**
	 * Get the day each week starts on
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $start
	 *
	 * @return string
	 */
	protected function get_start_of_week( $start = '1' ) {
		return (string) sugar_calendar_get_user_preference( 'sc_start_of_week', (string) $start );
	}

	/**
	 * Get the date format
	 *
	 * @since 2.0.0
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	protected function get_date_format( $format = 'F j, Y' ) {
		return sugar_calendar_get_user_preference( 'sc_date_format', $format );
	}

	/**
	 * Get the time format
	 *
	 * @since 2.0.0
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	protected function get_time_format( $format = 'g:i a' ) {
		return sugar_calendar_get_user_preference( 'sc_time_format', $format );
	}

	/**
	 * Get the URL with persistent arguments.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Array of arguments to override
	 *
	 * @return string
	 */
	protected function get_persistent_url( $args = array() ) {

		// Get tax terms
		$tax_terms = $this->get_tax_terms();

		// Query arg defaults
		$defaults = array(
			'page'        => $this->get_page(),
			'cystart'     => $this->get_start_year(),
			'cy'          => $this->get_year(),
			'cm'          => $this->get_month(),
			'cd'          => $this->get_day(),
			'cz'          => $this->get_timezone(),
			'mode'        => $this->get_mode(),
			'max'         => $this->get_max(),
			'status'      => $this->get_status(),
			'object_type' => $this->get_object_type(),
			's'           => $this->get_search()
		);

		// Parse arguments
		$r = wp_parse_args( $args, array_merge( $defaults, $tax_terms ) );

		// Maybe unset default status
		if ( 'all' === $r['status'] ) {
			unset( $r['status'] );
		}

		// Maybe unset default object type
		if ( 'post' === $r['object_type'] ) {
			unset( $r['object_type'] );
		}

		// Maybe unset default search
		if ( empty( $r['s'] ) ) {
			unset( $r['s'] );
		}

		// Maybe unset default time zone
		if ( empty( $r['cz'] ) || ( $this->timezone === $r['cz'] ) ) {
			unset( $r['cz'] );
		}

		// Maybe unset list-years
		if ( 'list' !== $r['mode'] ) {
			unset( $r['cystart'] );
		}

		// Use the base URL
		$url = $this->get_base_url();

		// Add args & return
		return add_query_arg( $r, $url );
	}

	/**
	 * Get the URL for today.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_today_url() {
		return $this->get_persistent_url( array(
			'cy' => gmdate( 'Y', $this->now ),
			'cm' => gmdate( 'n', $this->now ),
			'cd' => gmdate( 'j', $this->now ),
		) );
	}

	/**
	 * Setup the list-table columns
	 *
	 * @since 2.0.0
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {
		static $retval = null;

		// Calculate if not calculated already
		if ( null === $retval ) {

			// PHP day => day ID
			$days = $this->get_week_days();

			// Setup return value
			$retval = array();
			foreach ( $days as $key => $day ) {
				$retval[ $day ] = $GLOBALS['wp_locale']->get_weekday( $key );
			}
		}

		return $retval;
	}

	/**
	 * Get the days for any given week
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_week_days() {
		return $this->week_days;
	}

	/**
	 * Get the offset for a given day, based on the start-of-week setting
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $datetime
	 * @return int
	 */
	protected function get_day_offset( $datetime = '' ) {

		// Maybe make datetime into timestamp
		$timestamp = ! is_int( $datetime )
			? strtotime( $datetime )
			: $datetime;

		// Get date properties
		$this_month = (int) gmdate( 'w', $timestamp );
		$days       = array_keys( $this->get_week_days() );

		// Return the offset
		return (int) array_search(
			$this_month,
			$days,
			true
		);
	}

	/**
	 * No columns are sortable
	 *
	 * @since 2.0.0
	 *
	 * @return array An associative array containing the sortable columns
	 */
	protected function get_sortable_columns() {
		return array();
	}

	/**
	 * No columns are hidden
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_hidden_columns() {

		// Get hidden columns for screen
		$screen  = get_current_screen();
		$columns = get_hidden_columns( $screen );

		// Return any hidden columns
		return $columns;
	}

	/**
	 * No bulk actions
	 *
	 * @since 2.0.0
	 *
	 * @return array An associative array containing all the bulk actions
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Get the possible list table modes
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_modes() {
		return $this->modes;
	}

	/**
	 * Get the current mode
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_mode() {
		return $this->mode;
	}

	/**
	 * Get the current object type
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_object_type() {
		return $this->get_request_var( 'object_type', 'sanitize_key', 'post' );
	}

	/**
	 * Get the current month
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	protected function get_month() {
		$default = gmdate( 'n', $this->now );

		return $this->get_request_var( 'cm', 'intval', $default );
	}

	/**
	 * Get the current day
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	protected function get_day() {
		$default = gmdate( 'j', $this->now );

		return $this->get_request_var( 'cd', 'intval', $default );
	}

	/**
	 * Get the current year
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	protected function get_year() {
		$default = gmdate( 'Y', $this->now );

		return $this->get_request_var( 'cy', 'intval', $default );
	}

	/**
	 * Get the requested start year for the list boundary
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	protected function get_start_year() {
		$default = gmdate( 'Y', $this->now );

		return $this->get_request_var( 'cystart', 'intval', $default );
	}

	/**
	 * Get the current time zone
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	protected function get_timezone() {
		$default = $this->timezone;

		return $this->get_request_var( 'cz', 'urldecode', $default );
	}

	/**
	 * Get the current event status
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_status() {
		return $this->get_request_var( 'status', 'sanitize_key', 'all' );
	}

	/**
	 * Get the current term for a taxonomy
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	protected function get_tax_term( $taxonomy_name = '', $default = '' ) {
		return $this->get_request_var( $taxonomy_name, 'sanitize_key', $default );
	}

	/**
	 * Get taxonomy term requests
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_tax_terms() {

		// Default return value
		$retval = array();

		// Get the taxonomies
		$taxonomies = sugar_calendar_get_object_taxonomies(
			$this->get_primary_post_type()
		);

		// Maybe add taxonomies to tabs array
		if ( empty( $taxonomies ) ) {
			return $retval;
		}

		// Loop through each taxonomy
		foreach ( $taxonomies as $tax ) {

			// Look for term lookup
			$term = $this->get_tax_term( $tax );

			// Skip if no request
			if ( empty( $term ) ) {
				continue;
			}

			// Set
			$retval[ $tax ] = $term;
		}

		// Return any taxonomy requests
		return $retval;
	}

	/**
	 * Get the current page number
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_orderby() {
		return $this->get_request_var( 'orderby', 'sanitize_key', 'start' );
	}

	/**
	 * Get the current page number
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_order() {
		return $this->get_request_var( 'order', 'strtolower', 'asc' );
	}

	/**
	 * Get the current page number
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_search() {
		return $this->get_request_var( 's', 'wp_unslash' );
	}

	/**
	 * Get the maximum number of events per iteration.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	protected function get_max() {
		return $this->get_request_var( 'max', 'absint', $this->max );
	}

	/**
	 * Get a global request variable
	 *
	 * @since 2.0.0
	 *
	 * @param string $var
	 * @return string
	 */
	protected function get_request_var( $var = '', $sanitize = 'sanitize_text_field', $default = '' ) {
		return isset( $_REQUEST[ $var ] )
			? call_user_func( $sanitize, $_REQUEST[ $var ] )
			: $default;
	}

	/**
	 * Get available statuses
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_supported_post_stati() {
		return array(
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'hidden',
			'trash'
		);
	}

	/**
	 * Get removable query arguments
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_removable_args() {
		return array(
			'filter_action',
			's',
			'order',
			'orderby',
			'status',
			'mode'
		);
	}

	/**
	 * Get a list of CSS classes for the list table table tag.
	 *
	 * @since 2.0.0
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array(
			'widefat',
			'fixed',
			'striped',
			'calendar',
			$this->get_mode(),
			$this->get_status(),
			$this->_args['plural']
		);
	}

	/**
	 * Get the calendar views
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_views() {

		// Output the event filter above the views
		$this->event_filter();

		// Screen
		$base_url = $this->get_persistent_url();

		// Get the event counts for the current view
		$event_counts = $this->get_item_counts();

		// Statuses
		$avail_post_stati = $this->get_supported_post_stati();
		$event_statuses   = get_post_stati( array( 'show_in_admin_all_list' => false ) );

		// "All" link class
		$class = ( 'all' === $this->get_status() )
			? 'current'
			: '';

		// "All" link text
		$all_inner_html = sprintf(
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$event_counts['total'],
				'List table: all statuses (excluding trash)',
				'sugar-calendar'
			),
			number_format_i18n( $event_counts['total'] )
		);

		// Setup status links
		$status_links = array(
			'all' => '<a href="' . esc_url( remove_query_arg( 'status', $base_url ) ) . '" class="' . $class . '">' . $all_inner_html . '</a>'
		);

		// Other links
		$event_statuses = get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' );

		// Loop through statuses and compile array of available ones
		if ( ! empty( $event_statuses ) ) {
			foreach ( $event_statuses as $status ) {

				// Set variable to trick PHP
				$status_name = $status->name;

				// Skip if not available status
				if ( ! in_array( $status_name, $avail_post_stati, true ) ) {
					continue;
				}

				// Skip if no event count
				if ( empty( $event_counts[ $status_name ] ) ) {
					continue;
				}

				// Set the class value
				if ( $this->get_status() === $status_name ) {
					$class = 'current';
				} else {
					$class = '';
				}

				// Calculate the status text
				$status_html = sprintf( translate_nooped_plural( $status->label_count, $event_counts[ $status_name ] ), number_format_i18n( $event_counts[ $status_name ] ) );
				$status_url  = add_query_arg( array( 'status' => $status_name ), $base_url );

				// Add link to array
				$status_links[ $status_name ] = '<a href="' . esc_url( $status_url ) . '" class="' . $class . '">' . $status_html . '</a>';
			}
		}

		return $status_links;
	}

	/**
	 * Get the query arguments used to get events from the database.
	 *
	 * This is split up into a few separate methods to make overriding
	 * individual query arguments easier.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function all_query_args( $args = array() ) {

		// Maybe add `post` to to object_type's to query for
		if ( post_type_supports( $this->get_screen_post_type(), 'events' ) ) {
			$args['object_type'] = ! empty( $args['object_type'] )
				? array_unshift( $args['object_type'], 'post' )
				: array( 'post' );
		}

		// Setup default args
		$defaults = array(
			'number'     => $this->get_max(),
			'orderby'    => $this->get_orderby(),
			'order'      => $this->get_order(),
			'search'     => $this->get_search(),
			'date_query' => $this->get_date_query_args()
		);

		// Parse the arguments
		$r = wp_parse_args( $args, $defaults );

		// Return parsed arguments
		return $r;
	}

	/**
	 * Set a queried item in its proper array position
	 *
	 * @since 2.0.0
	 *
	 * @param int    $cell
	 * @param string $type
	 * @param int    $item_id
	 * @param mixed  $data
	 */
	protected function set_queried_item( $cell = 1, $type = 'items', $item_id = 0, $data = array() ) {

		// Prevent debug notices if type is not set
		if ( ! isset( $this->{$type}[ $cell ] ) ) {
			$this->{$type}[ $cell ] = array();
		}

		// Set the queried item
		$this->{$type}[ $cell ][ $item_id ] = $data;
	}

	/**
	 * Get the already queried items for a given day
	 *
	 * @since 2.0.0
	 *
	 * @param int $cell
	 *
	 * @return array
	 */
	protected function get_queried_items( $cell = 1, $type = 'items' ) {
		return ! empty( $this->{$type} ) && isset( $this->{$type}[ $cell ] )
			? $this->{$type}[ $cell ]
			: array();
	}

	/**
	 * Take a datetime string, and modify it based on an array of values.
	 *
	 * @since 2.0.0
	 *
	 * @param string $datetime
	 * @param array  $args
	 *
	 * @return string
	 */
	protected function modify_datetime( $datetime = '', $args = array() ) {

		// Maybe make datetime into timestamp
		$timestamp = ! is_int( $datetime )
			? strtotime( $datetime )
			: $datetime;

		// Parse arguments
		$r = wp_parse_args( $args, array(
			'Y' => gmdate( 'Y', $timestamp ),
			'm' => gmdate( 'm', $timestamp ),
			'd' => gmdate( 'd', $timestamp ),
			'H' => gmdate( 'H', $timestamp ),
			'i' => gmdate( 'i', $timestamp ),
			's' => gmdate( 's', $timestamp )
		) );

		// Return merged
		return gmdate( 'Y-m-d H:i:s', gmmktime(
			$r['H'],
			$r['i'],
			$r['s'],
			$r['m'],
			$r['d'],
			$r['Y']
		) );
	}

	/**
	 * Maybe skip an item in a cell.
	 *
	 * @since 2.0.0
	 *
	 * @param object $item
	 * @return boolean
	 */
	protected function skip_item_in_cell( $item = false ) {
		return empty( $item );
	}

	/**
	 * Does an event belong inside the current cell?
	 *
	 * @since 2.0.0
	 * @since 2.1.2 Prefers Event::intersects() over Event::overlaps()
	 *
	 * @param object $item
	 * @return boolean
	 */
	protected function is_item_for_cell( $item = false ) {

		// Bail if skipping
		if ( $this->skip_item_in_cell( $item ) ) {
			return false;
		}

		// Start boundary
		$start  = $this->get_current_cell( 'start_dto' );

		// End boundary
		$end    = $this->get_current_cell( 'end_dto' );

		// Get the mode
		$mode   = $this->get_mode();

		// Get intersects
		$retval = $item->intersects( $start, $end, $mode );

		// Return if event belongs in cell
		return $retval;
	}

	/**
	 * Get events for a given cell
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_events_for_cell() {

		// Default return value
		$retval = '';

		// Default items array
		$items = $this->get_current_cell( 'items', array() );

		// Bail if no items
		if ( empty( $items ) ) {
			return $retval;
		}

		// Loop through today's events
		foreach ( $items as $item ) {
			$retval .= $this->get_event( $item );
		}

		// Return the output buffer
		return $retval;
	}

	/**
	 * Get number of events in a specific cell
	 *
	 * @since 2.0.0
	 *
	 * @param int    $cell
	 * @param string $type
	 *
	 * @return int
	 */
	protected function get_event_count_for_cell( $cell = 1, $type = 'items' ) {
		$events = $this->get_queried_items( $cell, $type );

		// Return 0 or number
		return ! empty( $events )
			? count( $events )
			: 0;
	}

	/**
	 * Get an event link for use inside a table cell
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return string
	 */
	protected function get_event( $event = false ) {

		// Bail if event is empty
		if ( empty( $event ) ) {
			return '';
		}

		// Get the cell
		$cell = $this->get_current_cell( 'index' );

		// Get the link
		$link = $this->get_event_link( $event );

		// Setup the pointer ID
		$pointer_id = "calendar-pointer-{$event->id}-{$cell}";

		// Setup the pointer for this event
		$this->setup_pointer( $event, $cell );

		// Prepare the link HTML
		$html  = '<div id="%s">%s</div>';
		$event = sprintf( $html, $pointer_id, $link );

		// Return the event link
		return $event;
	}

	/**
	 * Return the HTML for linking to an event.
	 *
	 * @since 2.0.3
	 *
	 * @param object $event
	 * @return string
	 */
	protected function get_event_link( $event = false ) {

		// Bail if event is empty
		if ( empty( $event ) ) {
			return '';
		}

		// Get the cell
		$cell = $this->get_current_cell( 'index' );

		// Get the edit url
		$event_edit_url = $this->get_event_edit_url( $event );

		// Handle empty titles
		$event_title = ! empty( $event->title )
			? apply_filters( 'the_title', $event->title )
			: esc_html__( '(No title)', 'sugar-calendar' );

		// Filter all event attributes
		$attributes = array(
			'href'  => esc_url( $event_edit_url ),
			'class' => $this->get_event_classes( $event, $cell ),
			'style' => $this->get_event_link_styling( $event )
		);

		// Default attribute string
		$attr = '';

		// Loop through attributes and combine them (previously sanitized)
		foreach ( $attributes as $key => $value ) {
			$attr .= ' ' . $key . '="' . $value . '"';
		}

		// Setup the pointer for this event
		$this->setup_pointer( $event, $cell );

		// Prepare the link HTML
		$html = '<a %s>%s</a>';
		$link = sprintf( $html, $attr, $event_title );

		// Return the event link
		return $link;
	}

	/**
	 * @since 2.0.3
	 *
	 * @param object $event
	 *
	 * @return string
	 */
	protected function get_event_link_styling( $event = false ) {

		// Default anchor style
		$retval = '';

		// Enforce a color
		if ( empty( $event->color ) ) {
			return $retval;
		}

		// Get the color contrast score so a background color can be applied
		$score = sugar_calendar_get_contrast_score( $event->color );

		// Contrast is OK
		if ( $score < 5 ) {
			$retval = 'color: ' . $event->color . ' !important;';

		// Contrast is not OK
		} else {
			$color    = sugar_calendar_get_contrast_color( $event->color );
			$bg_color = $event->color;
			$retval   = 'background-color: ' . $bg_color . '; color: ' . $color . ' !important;';
		}

		// Return the link styling, if any
		return $retval;
	}

	/** Cell ******************************************************************/

	/**
	 * Whether to start a new row in the table.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	protected function start_row() {
		$i = (int) $this->get_current_cell( 'index' );

		return ( $i % $this->day_count ) === 0;
	}

	/**
	 * Whether to end the current row in the table.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	protected function end_row() {
		$i = (int) $this->get_current_cell( 'index' );

		return ( $i % $this->day_count ) === ( $this->day_count - 1 );
	}

	/**
	 * Set the items for the current cell
	 *
	 * @since 2.1.3
	 */
	protected function set_cell_items() {

		// Loop through items
		foreach ( $this->filtered_items as $item ) {

			// Skip if event is not for cell
			if ( ! $this->is_item_for_cell( $item ) ) {
				continue;
			}

			// Add item to return value
			array_push( $this->current_cell['items'], $item );
		}

		// Add the current cell to the cells array
		$this->cells[] = $this->get_current_cell();
	}

	/**
	 * Set the items for all of the cells
	 *
	 * @since 2.1.3
	 */
	protected function set_cells() {

		// Get maximum, offset, and row
		$maximum = ceil( ( $this->grid_end - $this->grid_start ) / DAY_IN_SECONDS );
		$offset  = $this->get_day_offset( $this->grid_start );
		$row     = 0;

		// Loop through days of the month
		for ( $i = 0; $i < ( $maximum + $offset ); $i++ ) {

			// Setup the day
			$day = ( $i - $offset ) + 1;

			// Setup cell boundaries
			$this->set_cell_boundaries( array(
				'start'  => gmmktime( 0,  0,  0,  $this->month, $day, $this->year ),
				'end'    => gmmktime( 23, 59, 59, $this->month, $day, $this->year ),
				'row'    => $row,
				'index'  => $i,
				'offset' => $offset
			) );

			// Setup cell items
			$this->set_cell_items();

			// Maybe end the row?
			if ( $this->end_row() ) {
				++$row;
			}
		}

		// Cleanup
		$this->current_cell = array();
	}

	/**
	 * Set the current cell properties
	 *
	 * @since 2.1.3
	 *
	 * @param array $args
	 */
	protected function set_cell_boundaries( $args = array() ) {

		// Parse arguments
		$r = wp_parse_args( $args, array(
			'index'  => null,
			'offset' => null,
			'start'  => null,
			'end'    => null,
			'type'   => 'normal',
			'items'  => array()
		) );

		// Get the time zone
		$timezone = $this->get_timezone();

		// Add date parts for start
		if ( ! empty( $r['start'] ) ) {
			$r['start_dto']     = sugar_calendar_get_datetime_object( $r['start'], $timezone );
			$r['start_year']    = $r['start_dto']->format( 'Y' );
			$r['start_month']   = $r['start_dto']->format( 'm' );
			$r['start_day']     = $r['start_dto']->format( 'd' );
			$r['start_dow']     = $r['start_dto']->format( 'w' );
			$r['start_doy']     = $r['start_dto']->format( 'z' );
			$r['start_woy']     = $r['start_dto']->format( 'W' );
			$r['start_hour']    = $r['start_dto']->format( 'H' );
			$r['start_minutes'] = $r['start_dto']->format( 'i' );
			$r['start_seconds'] = $r['start_dto']->format( 's' );
		}

		// Add date parts for end
		if ( ! empty( $r['end'] ) ) {
			$r['end_dto']       = sugar_calendar_get_datetime_object( $r['end'], $timezone );
			$r['end_year']      = $r['end_dto']->format( 'Y' );
			$r['end_month']     = $r['end_dto']->format( 'm' );
			$r['end_day']       = $r['end_dto']->format( 'd' );
			$r['end_dow']       = $r['end_dto']->format( 'w' );
			$r['end_doy']       = $r['end_dto']->format( 'z' );
			$r['end_woy']       = $r['end_dto']->format( 'W' );
			$r['end_hour']      = $r['end_dto']->format( 'H' );
			$r['end_minutes']   = $r['end_dto']->format( 'i' );
			$r['end_seconds']   = $r['end_dto']->format( 's' );
		}

		// Set the current cell
		$this->current_cell = $r;
	}

	/**
	 * Set the current cell using the $cells index
	 *
	 * @since 2.1.3
	 *
	 * @param array $args
	 */
	protected function set_current_cell( $args = array() ) {

		// Get all matching cells
		$cells = wp_list_filter( $this->cells, $args );

		// Pick the first matching cell
		$this->current_cell = ! empty( $cells )
			? reset( $cells )
			: array();
	}

	/**
	 * Get the current cell properties
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function get_current_cell( $key = '', $default = null ) {

		// Return a specific cell key
		if ( ! empty( $key ) && isset( $this->current_cell[ $key ] ) ) {
			return ! is_null( $this->current_cell[ $key ] )
				? $this->current_cell[ $key ]
				: $default;
		}

		// Return the entire array, or default return value
		return is_null( $default )
			? $this->current_cell
			: $default;
	}

	/** Formatting ************************************************************/

	/**
	 * Get the date of the event
	 *
	 * @since 2.0.0
	 *
	 * @param string $datetime
	 * @param string $timezone
	 *
	 * @return string
	 */
	protected function get_event_date( $datetime = '', $timezone = '' ) {
		return sugar_calendar_format_date_i18n( $this->date_format, $datetime, $timezone, $this->timezone );
	}

	/**
	 * Get the time of the event
	 *
	 * @since 2.0.0
	 *
	 * @param string $datetime
	 * @param string $timezone
	 *
	 * @return string
	 */
	protected function get_event_time( $datetime = '', $timezone = '' ) {
		return sugar_calendar_format_date_i18n( $this->time_format, $datetime, $timezone, $this->timezone );
	}

	/**
	 * Get the time zone offset
	 *
	 * @since 2.1.0
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	protected function get_time_zone_offset( $args = array() ) {
		return sugar_calendar_get_timezone_offset( $args );
	}

	/** Pointers **************************************************************/

	/**
	 * Add an event to the pointers array
	 *
	 * @since 2.0.0
	 *
	 * @param  object  $event
	 * @param  int     $cell
	 */
	protected function setup_pointer( $event = false, $cell = false ) {

		// Bail if no event or no cell (0 is OK for cell)
		if ( empty( $event ) || ! is_numeric( $cell ) ) {
			return;
		}

		// Get all pointer contents
		$pointer_content = array(
			'title' => '<h3 class="' . $this->get_event_classes( $event ) . '">' . $this->get_pointer_title( $event ) . '</h3>',
			'text'  => '<p>' . $this->get_pointer_text( $event ) . '</p>',
			'links' => '<div class="wp-pointer-actions">' . $this->get_pointer_links( $event ) . '</div>'
		);

		// Filter
		$pointer_content = (array) apply_filters( 'sugar_calendar_admin_pointer_content', $pointer_content, $event, $cell );

		// Add pointer to pointers array
		$this->pointers[] = array(
			'content'   => implode( '', $pointer_content ),
			'anchor_id' => "#calendar-pointer-{$event->id}-{$cell}",
			'edge'      => 'bottom',
			'align'     => 'center'
		);
	}

	/**
	 * Return the pointer title text
	 *
	 * @since 2.0.0
	 *
	 * @param   object $event
	 * @return  string
	 */
	protected function get_pointer_title( $event = false ) {

		// Handle empty titles
		$title = ! empty( $event->title )
			? $event->title
			: esc_html__( '(No title)', 'sugar-calendar' );

		// Default return value (no edit link; text only)
		$retval = esc_js( $title );

		// If user can edit, link to "edit object" page
		if ( $this->current_user_can_edit( $event ) ) {
			$retval = $this->get_event_edit_link( $event, $retval );

		// If user can view, link to permalink
		} elseif ( $this->current_user_can_view( $event ) ) {
			$retval = $this->get_event_view_link( $event, $retval );
		}

		// Return
		return $retval;
	}

	/**
	 * Return the pointer links HTML
	 *
	 * @since 2.1.0
	 *
	 * @param  object $event
	 * @return string
	 */
	protected function get_pointer_links( $event = false ) {

		// Default no links
		$links = array();

		// Trashed, so maybe offer to Restore or Delete
		if ( 'trash' === $event->status ) {

			// Maybe add restore link
			if ( $this->current_user_can_delete( $event ) ) {
				$links['restore'] = '<span class="action event-restore">' . $this->get_event_restore_link( $event, esc_html__( 'Restore', 'sugar-calendar' ) ) . '</span>';
			}

			// Maybe add delete link
			if ( $this->current_user_can_delete( $event ) ) {
				$links['delete']  = '<span class="action event-delete">' . $this->get_event_delete_link( $event, esc_html__( 'Delete', 'sugar-calendar' ) ) . '</span>';
			}

		// Not trashed, so offer to Edit or View
		} else {

			// Maybe add edit link
			if ( $this->current_user_can_edit( $event ) ) {
				$links['edit'] = '<span class="action event-edit">' . $this->get_event_edit_link( $event, esc_html__( 'Edit', 'sugar-calendar' ) ) . '</span>';
			}

			// Add view link
			if ( $this->current_user_can_view( $event ) )  {
				$links['view'] = '<span class="action event-view">' . $this->get_event_view_link( $event, esc_html_x( 'View', 'verb', 'sugar-calendar' ) ) . '</span>';
			}
		}

		// Return
		return implode( '', $links );
	}

	/**
	 * Get the link used to edit an event.
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 * @param string $link_text
	 *
	 * @return string
	 */
	protected function get_event_edit_link( $event = false, $link_text = '' ) {
		return '<a href="' . esc_url( $this->get_event_edit_url( $event ) ) . '">'  . $link_text . '</a>';
	}

	/**
	 * Get the link used to delete an event.
	 *
	 * @since 2.0.21
	 *
	 * @param object $event
	 * @param string $link_text
	 *
	 * @return string
	 */
	protected function get_event_delete_link( $event = false, $link_text = '' ) {
		return '<a href="' . esc_url( $this->get_event_delete_url( $event ) ) . '">'  . $link_text . '</a>';
	}

	/**
	 * Get the link used to restore an event.
	 *
	 * @since 2.0.21
	 *
	 * @param object $event
	 * @param string $link_text
	 *
	 * @return string
	 */
	protected function get_event_restore_link( $event = false, $link_text = '' ) {
		return '<a href="' . esc_url( $this->get_event_restore_url( $event ) ) . '">'  . $link_text . '</a>';
	}

	/**
	 * Get the link used to view an event.
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 * @param string $link_text
	 *
	 * @return string
	 */
	protected function get_event_view_link( $event = false, $link_text = '' ) {
		return '<a href="' . esc_url( $this->get_event_view_url( $event ) ) . '">'  . $link_text . '</a>';
	}

	/**
	 * Get the URL used to edit an event.
	 *
	 * @todo Create a relationship registration API
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return string
	 */
	protected function get_event_edit_url( $event = false ) {

		// Default return value
		$retval = '';

		// Type of object
		switch ( $event->object_type ) {
			case 'post' :
				$retval = get_edit_post_link( $event->object_id );
				break;

			case 'user' :
				$retval = get_edit_user_link( $event->object_id );
				break;

			case 'comment' :
				$retval = get_edit_comment_link( $event->object_id );
				break;
		}

		// Return the HTML
		return $retval;
	}

	/**
	 * Get the URL used to restore an event.
	 *
	 * @todo Create a relationship registration API
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return string
	 */
	protected function get_event_delete_url( $event = false ) {

		// Default return value
		$retval = '';

		// Type of object
		switch ( $event->object_type ) {
			case 'post' :
				$retval = wp_nonce_url( add_query_arg( array( 'action' => 'delete' ), get_edit_post_link( $event->object_id ) ), 'delete-post_' . $event->object_id );
				break;

			case 'user' :
				$retval = get_edit_user_link( $event->object_id );
				break;

			case 'comment' :
				$retval = get_edit_comment_link( $event->object_id );
				break;
		}

		// Return the HTML
		return $retval;
	}

	/**
	 * Get the URL used to restore an event.
	 *
	 * @todo Create a relationship registration API
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return string
	 */
	protected function get_event_restore_url( $event = false ) {

		// Default return value
		$retval = '';

		// Type of object
		switch ( $event->object_type ) {
			case 'post' :
				$retval = wp_nonce_url( add_query_arg( array( 'action' => 'untrash' ), get_edit_post_link( $event->object_id ) ), 'untrash-post_' . $event->object_id );
				break;

			case 'user' :
				$retval = get_edit_user_link( $event->object_id );
				break;

			case 'comment' :
				$retval = get_edit_comment_link( $event->object_id );
				break;
		}

		// Return the HTML
		return $retval;
	}

	/**
	 * Get the URL used to view an event.
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return string
	 */
	protected function get_event_view_url( $event = false ) {

		// Default return value
		$retval = '';

		// Type of object
		switch ( $event->object_type ) {
			case 'post' :
				$retval = get_permalink( $event->object_id );
				break;

			case 'user' :
				$retval = get_author_posts_url( $event->object_id );
				break;

			case 'comment' :
				$retval = get_comment_link( $event->object_id );
				break;
		}

		// Return the HTML
		return $retval;
	}

	/**
	 * Get all of the pointer text.
	 *
	 * @since 2.0.0
	 *
	 * @param   object  $event
	 *
	 * @return  string
	 */
	protected function get_pointer_text( $event = false ) {

		// Get all pointer info
		$pointer = array(
			$this->get_pointer_dates( $event ),
			$this->get_pointer_meta( $event ),
			$this->get_pointer_details( $event )
		);

		// Filter out empties and merge
		$pointer_text = array_merge( array_filter( $pointer ) );

		// Remove HTML tags that are not allowed
		foreach ( $pointer_text as $key => $value ) {
			$pointer_text[ $key ] = wp_kses( $value, $this->get_allowed_pointer_tags() );
		}

		// Filter
		$retval = (array) apply_filters( 'sugar_calendar_admin_get_pointer_text', $pointer_text, $event );

		// Combine with line breaks
		return implode( '', $retval );
	}

	/**
	 * Get the pointer details
	 *
	 * @since 2.0.3
	 *
	 * @param   object  $event
	 *
	 * @return  string
	 */
	protected function get_pointer_details( $event = false ) {
		$pointer_text = array();

		// Special case for password protected events
		if ( ! empty( $event->post_password ) ) {
			$pointer_text['details_title'] = '<strong>' . esc_html__( 'Details', 'sugar-calendar' ) . '</strong>';
			$pointer_text['details']       = '<span>'   . esc_html__( 'Password protected', 'sugar-calendar' ) . '</span>';

		// Post is not protected
		} elseif ( ! empty( $event->content ) ) {

			// Trim content down to 25 words or less - no HTML, to be safe
			$content = wp_trim_words( $event->content, 25 );

			// Title
			$pointer_text['details_title'] = '<strong>' . esc_html__( 'Details', 'sugar-calendar' ) . '</strong>';

			// Texturize
			$pointer_text['details']       = '<span>'   . esc_html( $content ) . '</span>';
		}

		// Filter
		$retval = (array) apply_filters( 'sugar_calendar_admin_get_pointer_details', $pointer_text, $event );

		// Separate with line breaks
		return implode( '', $retval );
	}

	/**
	 * Get event dates for display in a pointer
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return array
	 */
	protected function get_pointer_dates( $event = false ) {
		$pointer_dates = array();

		// Default time zone offset strings
		$stz = '';
		$etz = '';

		// Strip time zone formats from date & time formats
		$df = $this->strip_timezone_format( $this->date_format );
		$tf = $this->strip_timezone_format( $this->time_format );

		// Start time zone
		if ( ! empty( $event->start_tz ) ) {

			// Maybe show the original date, time, and zone
			if ( ! empty( $this->timezone ) && ( $this->timezone !== $event->start_tz ) ) {
				$to = sprintf(
					esc_html_x( '%s %s', 'Time Time Zone', 'sugar-calendar' ),
					sugar_calendar_format_date_i18n( $tf, $event->start, $event->start_tz ),
					sugar_calendar_format_timezone( $event->start_tz  )
				);

			// Single time zone
			} else {
				$to = sugar_calendar_format_timezone( $event->start_tz );
			}

			// Wrap in span
			$stz = '<span class="sc-timezone">' . esc_html( $to ) . '</span>';
		}

		// End time zone
		if ( ! empty( $event->end_tz ) ) {

			// Maybe show the original date, time, and zone
			if ( ! empty( $this->timezone ) && ( $this->timezone !== $event->end_tz ) ) {
				$to = sprintf(
					esc_html_x( '%s %s', 'Time Time Zone', 'sugar-calendar' ),
					sugar_calendar_format_date_i18n( $tf, $event->end, $event->end_tz ),
					sugar_calendar_format_timezone( $event->end_tz )
				);

			// Single time zone
			} else {
				$to = sugar_calendar_format_timezone( $event->end_tz );
			}

			// Wrap in span
			$etz = '<span class="sc-timezone">' . esc_html( $to ) . '</span>';

		// Use the start time zone string
		} elseif ( ! empty( $stz ) ) {
			$etz = $stz;
		}

		// Start & end
		$start = $this->get_event_date( $event->start, $event->start_tz );
		$end   = $this->get_event_date( $event->end,   $event->end_tz   );

		// All day, single-day event
		if ( $event->is_all_day() ) {

			// Multi-day
			if ( $event->is_multi( 'day' ) ) {

				// Yearly
				if ( 'yearly' === $event->recurrence ) {
					$pointer_dates['start_title'] = '<strong>' . esc_html__( 'Start', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['start']       = esc_html( $start ) . '</span>';
					$pointer_dates['end_title']   = '<strong>' . esc_html__( 'End', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['end']         = esc_html( $end ) . '</span>';

				// Monthly
				} elseif ( 'monthly' === $event->recurrence ) {
					$pointer_dates['start_title'] = '<strong>' . esc_html__( 'Start', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['start']       = esc_html( $start ) . '</span>';
					$pointer_dates['end_title']   = '<strong>' . esc_html__( 'End', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['end']         = esc_html( $end ) . '</span>';

				// No recurrence
				} else {
					$pointer_dates['start_title'] = '<strong>' . esc_html__( 'Start', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['start']       = esc_html( $start ) . '</span>';
					$pointer_dates['end_title']   = '<strong>' . esc_html__( 'End', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['end']         = esc_html( $end ) . '</span>';
				}

			// Single all-day
			} else {
				$pointer_dates['all_day_title'] = '<strong>' . esc_html__( 'All Day', 'sugar-calendar' ) . '</strong>';
				$pointer_dates['all_day']       = '<span>'   . esc_html( $start ) . '</span>';
			}

		// All other events
		} else {

			// Multi-day
			if ( $event->is_multi( 'day' ) ) {

				// Yearly
				if ( 'yearly' === $event->recurrence ) {
					$pointer_dates['start_title'] = '<strong>' . esc_html__( 'Start', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['start']       = '<span>'   . esc_html( $start ) . '</span>';
					$pointer_dates['end_title']   = '<strong>' . esc_html__( 'End', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['end']         = '<span>'   . esc_html( $end ) . '</span>';

				// Monthly
				} elseif ( 'monthly' === $event->recurrence ) {
					$pointer_dates['start_title'] = '<strong>' . esc_html__( 'Start', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['start']       = '<span>'   . esc_html( $start ) . '</span>';
					$pointer_dates['end_title']   = '<strong>' . esc_html__( 'End', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['end']         = '<span>'   . esc_html( $end ) . '</span>';

				// No recurrence
				} else {
					$pointer_dates['start_title'] = '<strong>' . esc_html__( 'Start', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['start']       = '<span>'   . esc_html( $start ) . '</span>';
					$pointer_dates['end_title']   = '<strong>' . esc_html__( 'End', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['end']         = '<span>'   . esc_html( $end ) . '</span>';
				}

			// Single day
			} else {

				// Date & Time
				if ( ! $event->is_empty_date( $event->start ) ) {
					$time  = $this->get_event_time( $event->start, $event->start_tz );
					$day   = sugar_calendar_format_date_i18n( 'w', $event->start, $event->start_tz, $this->timezone );
					$start = esc_html( sprintf(
						esc_html_x( '%s on %s', '20:00 on Friday', 'sugar-calendar' ),
						$time,
						$GLOBALS['wp_locale']->get_weekday( $day )
					) );

					// Maybe append time zone
					if ( ! empty( $stz ) ) {
						$start .= '<br>' . $stz;
					}

					$pointer_dates['start_title'] = '<strong>' . esc_html__( 'Start', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['start']       = '<span>'   . $start  . '</span>'; // Unescaped
				}

				// Date & Time
				if ( ! $event->is_empty_date( $event->end ) && ( $event->start !== $event->end ) ) {
					$time = $this->get_event_time( $event->end, $event->end_tz );
					$day  = sugar_calendar_format_date_i18n( 'w', $event->end, $event->end_tz, $this->timezone );
					$end  = esc_html( sprintf(
						esc_html_x( '%s on %s', '20:00 on Friday', 'sugar-calendar' ),
						$time,
						$GLOBALS['wp_locale']->get_weekday( $day )
					) );

					// Maybe append time zone
					if ( ! empty( $etz ) ) {
						$end .= '<br>' . $etz;
					}

					$pointer_dates['end_title'] = '<strong>' . esc_html__( 'End', 'sugar-calendar' ) . '</strong>';
					$pointer_dates['end']       = '<span>'   . $end . '</span>'; // Unescaped
				}
			}
		}

		// Recurrence
		if ( ! empty( $event->recurrence ) ) {
			$intervals = $this->get_recurrence_types();

			// Interval is known
			if ( isset( $intervals[ $event->recurrence ] ) ) {
				$pointer_dates['recurrence'] = '<strong>' . esc_html_x( 'Repeats', 'Noun', 'sugar-calendar' ) . '</strong>';

				// No end
				if ( empty( $event->recurrence_end ) ) {
					$pointer_dates['interval'] = esc_html( $intervals[ $event->recurrence ] );

				// Recurrence ends
				} elseif ( ! $event->is_empty_date( $event->recurrence_end ) ) {
					$recurring = sprintf(
						esc_html_x( '%s from %s until %s', 'Weekly from December 1, 2030 until December 31, 2030', 'sugar-calendar' ),
						$intervals[ $event->recurrence ],
						$this->get_event_date( $event->start, $event->start_tz ),
						$this->get_event_date( $event->recurrence_end, $event->recurrence_end_tz )
					);

					$pointer_dates['recurrence_end'] = '<span>' . esc_html( $recurring ) . '</span>';

				// Recurrence goes forever
				} elseif ( ! $event->is_empty_date( $event->end ) && ( $event->start === $event->end ) ) {
					$recurring = sprintf(
						esc_html_x( '%s starting %s', 'Weekly forever, starting May 15, 1980', 'sugar-calendar' ),
						$intervals[ $event->recurrence ],
						$this->get_event_date( $event->start, $event->start_tz )
					);

					$pointer_dates['recurrence_end'] = '<span>' . esc_html( $recurring ) . '</span>';

				} else {
					$recurring = sprintf(
						esc_html_x( '%s', 'Weekly forever', 'sugar-calendar' ),
						$intervals[ $event->recurrence ]
					);

					$pointer_dates['recurrence_end'] = '<span>' . esc_html( $recurring ) . '</span>';
				}
			}
		}

		// Filter
		$retval = (array) apply_filters( 'sugar_calendar_admin_get_pointer_dates', $pointer_dates, $event );

		// Separate with line breaks
		return implode( '', $retval );
	}

	/**
	 * Get event dates for display in a pointer
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return array
	 */
	protected function get_pointer_meta( $event = false ) {
		$pointer_meta = array();

		// Location
		if ( ! empty( $event->location ) ) {

			// Turn new lines into line breaks
			$location       = preg_replace( '/[\r\n]+/', '<br>', $event->location );

			// Title
			$pointer_meta['location_title'] = '<strong>' . esc_html__( 'Location', 'sugar-calendar' ) . '</strong>';

			// Location though kses, only allow breaks
			$pointer_meta['location']       = '<span>' . $location . '</span>';
		}

		// Filter
		$retval = (array) apply_filters( 'sugar_calendar_admin_get_pointer_meta', $pointer_meta, $event );

		// Separate with line breaks
		return implode( '', $retval );
	}

	/**
	 * Return array of allowed HTML tags to use in admin pointers
	 *
	 * @since 2.0.0
	 *
	 * @return allay Allowed HTML tags
	 */
	protected function get_allowed_pointer_tags() {
		return array(
			'a'      => array(),
			'strong' => array(),
			'span'   => array( 'class' => 1 ),
			'em'     => array(),
			'img'    => array(),
			'br'     => array()
		);
	}

	/**
	 * Output the pointers for each event
	 *
	 * This is a pretty horrible way to accomplish this, but it's currently the
	 * way WordPress's pointer API expects to work, so be it.
	 *
	 * @since 2.0.0
	 */
	public function admin_pointers_footer() {
		?>

<!-- Start Event Pointers -->
<script type="text/javascript" id="sugar-calendar-pointers">
	/* <![CDATA[ */
	( function( $ ) {

		// Prevent clicking on links from loading "edit" screen
		$( 'table.calendar .events-for-cell a' ).on( 'click', function( event ) {
			event.preventDefault();
		} );

		// Hide pointers on mousedown
		$( document ).mousedown( function( e ) {

			// Bail if clicking inside pointer
			if ( $( e.target ).closest( '.wp-pointer.sugar-calendar' ).length > 0 ) {
				return false;
			}

			// Close all other pointers
			$( '.wp-pointer.sugar-calendar .wp-pointer-buttons a.close' ).trigger( 'click' );
		} );

	<?php foreach ( $this->pointers as $item ) : ?>

		$( '<?php echo $item[ 'anchor_id' ]; ?>' ).pointer( {
			pointerClass: 'wp-pointer sugar-calendar',
			content: '<?php echo $item[ 'content' ]; ?>',
			position: {
				edge:  '<?php echo $item[ 'edge' ]; ?>',
				align: '<?php echo $item[ 'align' ]; ?>'
			}
		} );

		$( '<?php echo $item[ 'anchor_id' ]; ?>' ).click( function() {
			$( this ).pointer( 'open' );
		} );

	<?php endforeach; ?>

		// Hide all of the pointers
		$( '.wp-pointer.sugar-calendar' ).hide();
	} )( jQuery );
	/* ]]> */
</script>
<!-- End Event Pointers -->

		<?php
	}

	/** Permissions ***********************************************************/

	/**
	 * Can the current user delete an event?
	 *
	 * @since 2.0.21
	 *
	 * @param object $event
	 *
	 * @return boolean
	 */
	protected function current_user_can_delete( $event = false ) {
		return $this->user_can_delete( get_current_user_id(), $event );
	}
	/**
	 * Can a user ID delete an event?
	 *
	 * This method uses the object_type for the event to determine if the user
	 * can delete the related object_id.
	 *
	 * @since 2.0.21
	 *
	 * @return boolean Default false. True if user can delete event.
	 */
	protected function user_can_delete( $user_id = 0, $event = false ) {

		// Bail if no user was passed
		if ( empty( $user_id ) ) {
			return false;
		}

		// Get the cap, based on the object_type
		switch ( $event->object_type ) {
			case 'post' :
				$type = get_post_type( $event->object_id );
				$obj  = get_post_type_object( $type );

				// Map to `edit_post` if exists, or `do_not_allow` if not
				$cap = ! empty( $obj )
					? $obj->cap->delete_post
					: 'do_not_allow';

				break;

			case 'user' :
				$cap  = 'delete_user';
				break;

			case 'comment' :
				$cap  = 'delete_comment';
				break;

			default :
				$cap = 'delete_event';
				break;
		}

		// Cast and return
		return (bool) user_can( $user_id, $cap, $event->object_id );
	}

	/**
	 * Can the current user edit an event?
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return boolean
	 */
	protected function current_user_can_edit( $event = false ) {
		return $this->user_can_edit( get_current_user_id(), $event );
	}

	/**
	 * Can a user ID edit an event?
	 *
	 * This method uses the object_type for the event to determine if the user
	 * can edit the related object_id.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Default false. True if user can edit event.
	 */
	protected function user_can_edit( $user_id = 0, $event = false ) {

		// Bail if no user was passed
		if ( empty( $user_id ) ) {
			return false;
		}

		// Get the cap, based on the object_type
		switch ( $event->object_type ) {
			case 'post' :
				$type = get_post_type( $event->object_id );
				$obj  = get_post_type_object( $type );

				// Map to `edit_post` if exists, or `do_not_allow` if not
				$cap = ! empty( $obj )
					? $obj->cap->edit_post
					: 'do_not_allow';

				break;

			case 'user' :
				$cap  = 'edit_user';
				break;

			case 'comment' :
				$cap  = 'edit_comment';
				break;

			default :
				$cap = 'edit_event';
				break;
		}

		// Cast and return
		return (bool) user_can( $user_id, $cap, $event->object_id );
	}

	/**
	 * Can the current user view an event?
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 *
	 * @return boolean
	 */
	protected function current_user_can_view( $event = false ) {
		return $this->user_can_view( get_current_user_id(), $event );
	}

	/**
	 * Can a user ID view an event?
	 *
	 * This method uses the object_type for the event to determine if the user
	 * can view the related object_id.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Default false. True if user can view event.
	 */
	protected function user_can_view( $user_id = 0, $event = false ) {

		// Bail if no user was passed
		if ( empty( $user_id ) ) {
			return false;
		}

		// Get the cap, based on the object_type
		switch ( $event->object_type ) {
			case 'post' :
				$type = get_post_type( $event->object_id );
				$obj  = get_post_type_object( $type );

				// Map to `view_post` if exists, or `do_not_allow` if not
				$cap = ! empty( $obj )
					? $obj->cap->read_post
					: 'do_not_allow';

				break;

			case 'user' :
				$cap  = 'view_user';
				break;

			case 'comment' :
				$cap  = 'view_comment';
				break;

			default :
				$cap = 'read_event';
				break;
		}

		// Cast and return
		return (bool) user_can( $user_id, $cap, $event->object_id );
	}

	/** Output & Markup *******************************************************/

	/**
	 * Output default content for columns without explicit handlers.
	 *
	 * @since 2.0.15
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {

		// Default content
		$default = '&mdash;';

		// Filter the default content
		$to_echo = apply_filters( "sugar_calendar_list_table_{$column_name}_contents", $default, $item );

		// Output the content
		echo $to_echo;
	}

	/**
	 * Prepare items for list-table display
	 *
	 * @since 2.0.0
	 *
	 * @uses $this->_column_headers
	 * @uses $this->get_columns()
	 * @uses $this->get_orderby()
	 * @uses $this->get_order()
	 */
	public function prepare_items() {

		// Get query arguments
		$args = $this->all_query_args();

		// Query for events in the view
		$this->query = new \Sugar_Calendar\Event_Query( $args );

		// Set filtered items
		$this->set_filtered_items();

		// Set cells
		$this->set_cells();
	}

	/**
	 * Event filter to match the styling of the Media Filter
	 *
	 * This methods outputs the HTML used to switch modes, search events, filter
	 * with taxonomies
	 *
	 * @since 2.0.0
	 */
	public function event_filter() {
		?>

		<form id="events-filter" method="get">
			<div class="wp-filter">
				<div class="filter-items">
					<?php echo $this->mode_picker(); ?>

					<?php echo $this->extra_tablenav( 'bar' ); ?>
				</div>

				<div class="search-form">
					<label for="event-search-input" class="screen-reader-text"><?php esc_html_e( 'Search Events', 'sugar-calendar' ); ?></label>
					<input type="search" placeholder="<?php esc_attr_e( 'Search events...', 'sugar-calendar' ) ?>" id="event-search-input" class="search" name="s" value="<?php _admin_search_query(); ?>">
				</div>

				<input type="hidden" name="object_type" value="<?php echo esc_attr( $this->get_object_type() ); ?>" />
				<input type="hidden" name="status" value="<?php echo esc_attr( $this->get_status() ); ?>" />
				<input type="hidden" name="mode" value="<?php echo esc_attr( $this->get_mode() ); ?>" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->get_page() ); ?>" />
				<input type="hidden" name="cd" value="<?php echo esc_attr( $this->get_day() ); ?>" />
				<input type="hidden" name="cm" value="<?php echo esc_attr( $this->get_month() ); ?>" />
				<?php if ( 'list' === $this->get_mode() ) : ?>
					<input type="hidden" name="cy"      value="<?php echo esc_attr( $this->get_year() ); ?>" />
					<input type="hidden" name="cystart" value="<?php echo esc_attr( $this->get_start_year() ); ?>" />
				<?php else : ?>
					<input type="hidden" name="cy" value="<?php echo esc_attr( $this->get_year() ); ?>" />
				<?php endif; ?>
				<input type="hidden" name="cz" value="<?php echo esc_attr( $this->get_timezone() ); ?>" />
				<input type="hidden" name="order" value="<?php echo esc_attr( $this->get_order() ); ?>" />
				<input type="hidden" name="orderby" value="<?php echo esc_attr( $this->get_orderby() ); ?>" />
				<input type="hidden" name="max" value="<?php echo esc_attr( $this->get_max() ); ?>" />
			</div>
		</form>

		<?php
	}

	/**
	 * Display the table
	 *
	 * @since 2.0.0
	 */
	public function display() {

		// Start an output buffer
		ob_start();

		// Top
		$this->display_tablenav( 'top' ); ?>

		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tbody id="the-list" data-wp-lists='list:<?php echo $this->_args['singular']; ?>'>
				<?php $this->display_mode(); ?>
			</tbody>

			<tfoot>
				<tr>
					<?php $this->print_column_headers( false ); ?>
				</tr>
			</tfoot>
		</table>

		<?php

		// Bottom
		$this->display_tablenav( 'bottom' );

		// End and flush the buffer
		echo ob_get_clean();
	}

	/**
	 * Display a calendar by month and year
	 *
	 * @since 2.0.0
	 */
	protected function display_mode() {
		// Performed by subclass
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 2.0.0
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which = 'top' ) {
		?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>"><?php

			// Output Month, Year tablenav
			echo $this->extra_tablenav( $which );

			// Top only output
			if ( 'top' === $which ) :

				// Pagination
				echo $this->extra_tablenav( 'pagination' );

				// Tools
				echo $this->extra_tablenav( 'tools' );
			endif;

			?><br class="clear">
		</div>

		<?php
	}

	/**
	 * Method to avoid putting out the default search box
	 *
	 * @since 2.0.0
	 *
	 * @param string $text
	 * @param string $input_id
	 *
	 * return string
	 */
	public function search_box( $text = '', $input_id = '' ) {
		$text = $input_id = '';

		return $text;
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 2.0.0
	 */
	public function no_items() {
		// Do nothing; calendars always have rows
	}

	/**
	 * Handle bulk action requests
	 *
	 * @since 2.0.0
	 */
	public function process_bulk_action() {
		// No bulk actions
	}

	/**
	 * Always have items
	 *
	 * This method forces WordPress to always show the calendar rows, and never
	 * to trigger the `no_items()` method.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function has_items() {
		return true;
	}

	/**
	 * Get classes for event in day
	 *
	 * @since 2.0.0
	 *
	 * @param object $event
	 * @param int    $cell
	 */
	protected function get_event_classes( $event = 0, $cell = 0 ) {

		// Empty classes array
		$classes = array(
			'sugar-calendar'
		);

		// All day
		$classes[] = ! empty( $event->all_day )
			? 'all-day'
			: '';

		// Recurring
		$classes[] = ! empty( $event->recurrence )
			? 'recur-' . sanitize_key( $event->recurrence )
			: '';

		// Location
		$classes[] = get_event_meta( $event->id, 'location', true )
			? 'has-location'
			: '';

		// Color
		$classes[] = get_event_meta( $event->id, 'color', true )
			? 'has-color'
			: '';

		// Item position
		$positions = $this->get_queried_items( $cell, 'positions' );
		if ( ! empty( $positions[ $event->id ] ) ) {

			// Setup the position array
			$position  = array_map( 'intval', $positions[ $event->id ] );
			$classes[] = 'position-' . $position['current'];

			// Days
			$classes[] = ( $position['total'] > 0 )
				? 'multi-cell'
				: 'single-cell';

			// Start
			if ( 0 === $position['current'] ) {
				$classes[] = 'start';
			}

			// End
			if ( $position['current'] === $position['total'] ) {
				$classes[] = 'end';
			}

			// In-between
			if ( ! empty( $position['current'] ) && ( $position['current'] !== $position['total'] ) ) {
				$classes[] = 'middle';
			}
		}

		// Get taxonomies
		$taxos = sugar_calendar_get_object_taxonomies(
			$this->get_primary_post_type()
		);

		// Maybe loop through taxonomies, and add terms to
		if ( ! empty( $taxos ) && is_array( $taxos ) ) {

			// Loop through taxonomies...
			foreach ( $taxos as $tax ) {

				// Check term cache first
				$terms = get_object_term_cache( $event->object_id, $tax );

				// No cache, so query for terms
				if ( false === $terms ) {
					$terms = wp_get_object_terms( $event->object_id, $tax );
				}

				// Bail if no terms in this taxonomy
				if ( empty( $terms ) ) {
					continue;
				}

				// Add taxonomy to classes
				$classes[] = "tax-{$tax}";

				// Loop through terms and add them, too
				foreach ( $terms as $term ) {
					$classes[] = "term-{$term->slug}";
				}
			}
		}

		// Filter the event classes
		$classes = array_unique( get_post_class( $classes, $event->object_id ) );

		// Join & return
		return trim( join( ' ', $classes ) );
	}

	/**
	 * Is the current calendar view today
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	protected function is_today( $month, $day, $year ) {
		$_month = (bool) ( $month == gmdate( 'n', $this->now ) );
		$_day   = (bool) ( $day   == gmdate( 'j', $this->now ) );
		$_year  = (bool) ( $year  == gmdate( 'Y', $this->now ) );

		return (bool) ( true === $_month && true === $_day && true === $_year );
	}

	/**
	 * Get classes for table cell
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_cell_classes() {

		// Get the grid positions
		$cell = $this->get_current_cell( 'index' );

		// Day
		$day = $this->get_current_cell( 'start_day' );
		if ( 0 > $day ) {
			$day = 0;
		}

		// Day offset
		$offset = ( $this->get_mode() === 'month' )
			? $this->get_current_cell( 'offset' ) + 1
			: 0;

		// Don't allow negative offsets
		if ( $offset <= 0 ) {
			$offset = 0;
		}

		// Get day of week, and day key
		$dow           = ( $cell % $this->day_count );
		$days          = $this->get_week_days();
		$day_key       = array_values( $days )[ $dow ];
		$day_column    = "column-{$day_key}";

		// Position & day info
		$column_number = "column-{$dow}";
		$cell_number   = "cell-{$cell}";
		$day_number    = "day-{$day}";
		$month_number  = "month-{$this->month}";
		$year_number   = "year-{$this->year}";

		// Count
		$count         = 0;  //$this->get_event_count_for_cell( $cell );
		$count_number  = ''; //"count-{$count}";
		$has_events    = ! empty( $count )
			? 'not-empty'
			: '';

		// Today?
		$is_today = $this->is_today( $this->month, $day, $this->year )
			? 'today'
			: '';

		// Hidden?
		$hidden = in_array( $day_key, $this->get_hidden_columns(), true )
			? 'hidden'
			: '';

		// Assemble classes
		$classes = array_filter( array(
			$is_today,
			$hidden,
			$day_key,
			$day_column,
			$has_events,
			$count_number,
			$column_number,
			$cell_number,
			$day_number,
			$month_number,
			$year_number
		) );

		// Trim spaces and return
		return trim( implode( ' ', $classes ) );
	}

	/**
	 * Displays a taxonomy drop-downs for filtering in the bar table navigation.
	 */
	protected function dropdown_taxonomies() {

		// Look for post type screen
		$post_type = $this->get_primary_post_type();

		// Bail if no post types
		if ( empty( $post_type ) ) {
			return;
		}

		// Get taxonomies for this post type
		$taxonomies = sugar_calendar_get_object_taxonomies(
			$post_type,
			'objects'
		);

		// Bail if no taxonomies
		if ( empty( $taxonomies ) ) {
			return;
		}

		// Start an output buffer
		ob_start();

		// Loop through taxonomies and setup the dropdowns
		foreach ( $taxonomies as $tax ) {

			// Skip if private
			if ( empty( $tax->public ) ) {
				continue;
			}

			$current = $this->get_tax_term( $tax->name );

			// Label for dropdown
			echo '<label class="screen-reader-text" for="' . esc_attr( $tax->name ) . '">' . sprintf( esc_html__( 'Filter by %s', 'sugar-calendar' ), $tax->labels->name ) . '</label>';

			// Dropdown
			wp_dropdown_categories( array(
				'taxonomy'          => $tax->name,
				'name'              => $tax->name,
				'show_option_all'   => $tax->labels->all_items,
				'show_option_none'  => $tax->labels->no_terms,
				'option_none_value' => '__sc_none__',
				'hierarchical'      => $tax->hierarchical,
				'hide_empty'        => false,
				'show_count'        => false,
				'orderby'           => 'name',
				'value_field'       => 'slug',
				'class'             => 'sc-select-chosen',
				'selected'          => $current
			) );
		}

		return ob_get_clean();
	}

	/**
	 * Output month & year inputs, for viewing relevant events
	 *
	 * @since 2.0.0
	 *
	 * @param  string  $which
	 */
	protected function extra_tablenav( $which = '' ) {

		// Start an output buffer
		ob_start();

		// Before action
		do_action( "sugar_calendar_admin_before_extra_tablenav_{$which}", $this );

		// Bar
		if ( 'bar' === $which ) :

			// Get the taxonomies
			$drop = $this->dropdown_taxonomies();

			// Output taxonomies and "Filter" button
			if ( ! empty( $drop ) ) :
				echo $drop; ?>

				<div class="actions">
					<input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php echo esc_html_x( 'Filter', 'Verb', 'sugar-calendar' ); ?>">
				</div>

				<?php
			endif;

		// Top
		elseif ( 'top' === $which ) :

			// Hide the month picker UI in List mode
			if ( 'list' !== $this->get_mode() ) : ?>

				<label for="cm" class="screen-reader-text"><?php esc_html_e( 'Switch to this month', 'sugar-calendar' ); ?></label>
				<select name="cm" id="cm" class="sc-select-chosen">

					<?php for ( $month_index = 1; $month_index <= 12; $month_index++ ) : ?>

						<option value="<?php echo esc_attr( $month_index ); ?>" <?php selected( $month_index, $this->month ); ?>><?php echo $GLOBALS['wp_locale']->get_month( $month_index ); ?></option>

					<?php endfor; ?>

				</select>

			<?php endif;

			// Show the day input UI for day mode only
			if ( 'day' === $this->get_mode() ) : ?>

				<label for="cd" class="screen-reader-text"><?php esc_html_e( 'Set the day', 'sugar-calendar' ); ?></label>
				<input type="number" name="cd" id="cd" value="<?php echo (int) $this->day; ?>" size="2">

			<?php

			// Hide the day input UI for week mode
			elseif ( 'week' === $this->get_mode() ) : ?>

				<input type="hidden" name="cd" id="cd" value="<?php echo (int) $this->day; ?>">

			<?php endif;

			// Show start & end years for list mode
			if ( 'list' === $this->get_mode() ) : ?>

				<label for="cystart" class="screen-reader-text"><?php esc_html_e( 'Set the first year', 'sugar-calendar' ); ?></label>
				<input type="number" name="cystart" id="cystart" value="<?php echo (int) $this->get_start_year(); ?>">

				<span><?php esc_html_e( 'to', 'sugar-calendar' ); ?></span>

				<label for="cy" class="screen-reader-text"><?php esc_html_e( 'Set the last year', 'sugar-calendar' ); ?></label>
				<input type="number" name="cy" id="cy" value="<?php echo (int) $this->get_year(); ?>">

			<?php

			// Show single year for non-list modes
			else : ?>

				<label for="cy" class="screen-reader-text"><?php esc_html_e( 'Set the year', 'sugar-calendar' ); ?></label>
				<input type="number" name="cy" id="cy" value="<?php echo (int) $this->year; ?>">

			<?php endif; ?>

			<input type="hidden" name="mode" value="<?php echo esc_attr( $this->get_mode() ); ?>" />

			<input type="hidden" name="order" value="<?php echo esc_attr( $this->get_order() ); ?>" />
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $this->get_orderby() ); ?>" />
			<input type="hidden" name="s" value="<?php echo esc_attr( $this->get_search() ); ?>" />

			<?php

			// Taxonomies
			$tax_terms = $this->get_tax_terms();

			if ( ! empty( $tax_terms ) ) :
				foreach ( $tax_terms as $tax => $term ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $tax ); ?>" value="<?php echo esc_attr( $term ); ?>" />
				<?php endforeach;
			endif;

			// Output the "View" button
			submit_button( esc_html_x( 'View', 'verb', 'sugar-calendar' ), 'action', '', false, array( 'id' => 'doaction' ) );

			// Maybe output an "Empty Trash" button
			if ( ( 'trash' === $this->get_status() ) && current_user_can( get_post_type_object( $this->get_primary_post_type() )->cap->edit_others_posts ) && $this->has_items() ) :
				submit_button( esc_html__( 'Empty Trash', 'sugar-calendar' ), 'apply', 'delete_all_trashed_events', false );
			endif;

			// Nonce for event actions
			wp_nonce_field( 'event-actions' );

		// Output pagination
		elseif ( 'pagination' === $which ) :
			echo $this->pagination();

		// Output tools
		elseif ( 'tools' === $which ) :
			echo $this->tools();
		endif;

		// After action
		do_action( "sugar_calendar_admin_after_extra_tablenav_{$which}", $this );

		// Return
		return ob_get_clean();
	}

	/**
	 * Paginate through months & years
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 * @return string
	 */
	protected function pagination( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'small'  => '1 month',
			'large'  => '1 year',
			'labels' => array(
				'today'      => esc_html__( 'Today',          'sugar-calendar' ),
				'next_small' => esc_html__( 'Next month',     'sugar-calendar' ),
				'next_large' => esc_html__( 'Next year',      'sugar-calendar' ),
				'prev_small' => esc_html__( 'Previous month', 'sugar-calendar' ),
				'prev_large' => esc_html__( 'Previous year',  'sugar-calendar' )
			)
		) );

		// Base URLs
		$today = $this->get_today_url();

		// Calculate previous & next weeks & months
		$prev_small = strtotime( "-{$r['small']}", $this->today );
		$next_small = strtotime( "+{$r['small']}", $this->today );
		$prev_large = strtotime( "-{$r['large']}", $this->today );
		$next_large = strtotime( "+{$r['large']}", $this->today );

		// Week
		$prev_small_d = gmdate( 'j', $prev_small );
		$prev_small_m = gmdate( 'n', $prev_small );
		$prev_small_y = gmdate( 'Y', $prev_small );
		$next_small_d = gmdate( 'j', $next_small );
		$next_small_m = gmdate( 'n', $next_small );
		$next_small_y = gmdate( 'Y', $next_small );

		// Month
		$prev_large_d = gmdate( 'j', $prev_large );
		$prev_large_m = gmdate( 'n', $prev_large );
		$prev_large_y = gmdate( 'Y', $prev_large );
		$next_large_d = gmdate( 'j', $next_large );
		$next_large_m = gmdate( 'n', $next_large );
		$next_large_y = gmdate( 'Y', $next_large );

		// Setup month args
		$prev_small_args = array( 'cy' => $prev_small_y, 'cm' => $prev_small_m, 'cd' => $prev_small_d );
		$prev_large_args = array( 'cy' => $prev_large_y, 'cm' => $prev_large_m, 'cd' => $prev_large_d );
		$next_small_args = array( 'cy' => $next_small_y, 'cm' => $next_small_m, 'cd' => $next_small_d );
		$next_large_args = array( 'cy' => $next_large_y, 'cm' => $next_large_m, 'cd' => $next_large_d );

		// Setup links
		$prev_small_link = add_query_arg( $prev_small_args, $today );
		$next_small_link = add_query_arg( $next_small_args, $today );
		$prev_large_link = add_query_arg( $prev_large_args, $today );
		$next_large_link = add_query_arg( $next_large_args, $today );

		// Start an output buffer
		ob_start(); ?>

		<div class="tablenav-pages">

			<?php

			// Before action
			do_action( 'sugar_calendar_admin_before_pagination', $this ); ?>

			<a href="#" class="hide-if-no-js screen-options">
				<span class="screen-reader-text"><?php esc_html_e( 'Options', 'sugar-calendar' ); ?></span>
				<span aria-hidden="true" class="dashicons dashicons-admin-generic"></span>
			</a>

			<a class="previous-page button" href="<?php echo esc_url( $prev_large_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['prev_large'] ); ?></span>
				<span aria-hidden="true">&laquo;</span>
			</a>

			<a class="previous-page button" href="<?php echo esc_url( $prev_small_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['prev_small'] ); ?></span>
				<span aria-hidden="true">&lsaquo;</span>
			</a>

			<a href="<?php echo esc_url( $today ); ?>" class="previous-page button">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['today'] ); ?></span>
				<span aria-hidden="true">&Colon;</span>
			</a>

			<a class="next-page button" href="<?php echo esc_url( $next_small_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['next_small'] ); ?></span>
				<span aria-hidden="true">&rsaquo;</span>
			</a>

			<a class="next-page button" href="<?php echo esc_url( $next_large_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['next_large'] ); ?></span>
				<span aria-hidden="true">&raquo;</span>
			</a>

			<?php

			// After action
			do_action( 'sugar_calendar_admin_after_pagination', $this ); ?>

		</div>

		<?php

		// Return
		return ob_get_clean();
	}

	/**
	 * Additional tools
	 *
	 * @since 2.1.1
	 * @return string
	 */
	private function tools() {

		// Time zone
		$tztype   = sugar_calendar_get_timezone_type();
		$floating = sugar_calendar_is_timezone_floating();
		$timezone = sugar_calendar_format_timezone( $this->timezone );

		// Start an output buffer
		ob_start(); ?>

		<div class="tablenav-tools">

			<?php

			// Before action
			do_action( 'sugar_calendar_admin_before_tools', $this );

			// Output time zone if not floating or support is enabled
			if ( ( false === $floating ) || ( 'off' !== $tztype ) ) : ?>

				<span class="sc-timezone"><?php echo esc_html( $timezone ); ?></span>

			<?php endif;

			// Before action
			do_action( 'sugar_calendar_admin_after_tools', $this ); ?>

		</div>

		<?php

		// Return
		return ob_get_clean();
	}

	/**
	 * Display the mode switcher
	 *
	 * @since 2.0.0
	 *
	 * @param string $which
	 */
	public function mode_picker( $which = 'top' ) {

		// Only switch on top
		if ( 'top' !== $which ) {
			return;
		}

		// Get these ahead of the foreach loop
		$base_url  = $this->get_persistent_url();
		$modes     = $this->get_modes();
		$removable = $this->get_removable_args();
		$mode_url  = remove_query_arg( $removable, $base_url );

		// Start an output buffer
		ob_start(); ?>

		<div class="view-switch">
			<input type="hidden" name="mode" value="<?php echo esc_attr( $this->get_mode() ); ?>" />

			<?php

			// Loop through modes
			foreach ( $modes as $mode => $title ) :

				// Setup the URL by adding & removing args
				$url = add_query_arg(
					array( 'mode' => $mode ),
					$mode_url
				);

				// Setup classes
				$classes = array( 'view-' . $mode );
				if ( $this->get_mode() === $mode ) {
					$classes[] = 'current';
				} ?>

				<a href="<?php echo esc_url( $url ); ?>" class="<?php echo implode( ' ', $classes ); ?>" id="view-switch-<?php echo esc_attr( $mode ); ?>" title="<?php echo esc_attr( $title ); ?>">
					<span class='screen-reader-text'><?php echo esc_html( $title ); ?></span>
				</a>

			<?php endforeach; ?>

		</div>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Strip timezone formatting from a DateTime format string
	 *
	 * Used to avoid duplicate time zone output in the specific places where
	 * we manually always output a formatted time zone string.
	 *
	 * @since 2.1.2
	 * @param string $format
	 * @return string
	 */
	private function strip_timezone_format( $format = '' ) {

		// Time zone formats to remove
		$tz_formats = array( 'e', 'I', 'O', 'P', 'T', 'Z' );

		//
		return str_replace( $tz_formats, '', $format );
	}
}
endif;
