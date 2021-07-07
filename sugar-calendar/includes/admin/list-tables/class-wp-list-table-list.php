<?php
/**
 * Calendar List Table List Class
 *
 * @package Plugins/Site/Events/Admin/ListTables/List
 */
namespace Sugar_Calendar\Admin\Mode;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Event table
 *
 * This list table is responsible for showing events in a traditional table.
 * It will look a lot like `WP_Posts_List_Table` but extends our base, and shows
 * events in a monthly way.
 */
class Basic extends Base_List_Table {

	/**
	 * The mode of the current view
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $mode = 'list';

	/**
	 * Whether an item has an end
	 *
	 * @since 2.0.15
	 *
	 * @var bool
	 */
	private $item_ends = false;

	/**
	 * The main constructor method
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// Compensate for inverted user supplied values
		if ( $this->start_year >= $this->year ) {
			$view_start = "{$this->year}-01-01 00:00:00";
			$view_end   = "{$this->start_year}-12-31 23:59:59";
		} else {
			$view_start = "{$this->start_year}-01-01 00:00:00";
			$view_end   = "{$this->year}-12-31 23:59:59";
		}

		// Set the view
		$this->set_view( $view_start, $view_end );

		// Filter the Date_Query arguments for this List Table
		$this->filter_date_query_arguments();
	}

	/**
	 * Get the current page number
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_order() {
		return $this->get_request_var( 'order', 'strtolower', 'desc' );
	}

	/**
	 * Juggle the filters used on Date_Query arguments, so that the List Table
	 * of Events only shows current-year, including ones that may cross over
	 * between multiple years, but not their recurrences.
	 *
	 * @since 2.0.15
	 */
	protected function filter_date_query_arguments() {

		/**
		 * First, we need to remove the Recurring arguments that may exist in
		 * Standard Recurring, included in non-Lite versions.
		 */
		$removed = remove_filter( 'sugar_calendar_get_recurring_date_query_args', 'Sugar_Calendar\\Standard\\Recurring\\query_args', 10, 4 );

		// Bail if not removed
		if ( empty( $removed ) ) {
			return;
		}

		/**
		 * Last, we need to add a new filter for Recurring arguments so that
		 * they conform better to a List Table view (vs. a Calendar view.)
		 */
		add_filter( 'sugar_calendar_get_recurring_date_query_args', array( $this, 'filter_recurring_query_args' ), 10 );
	}

	/**
	 * Return array of recurring query arguments, used in Date_Query.
	 *
	 * This method is only made public so that it can use WordPress hooks. Do
	 * not rely on calling this method directly. Consider it private.
	 *
	 * Recurring events
	 * - recurrence starts before the view ends
	 * - recurrence ends after the view starts
	 * - start and end do not matter
	 *
	 * @since 2.0.15
	 *
	 * @access private
	 *
	 * @return array
	 */
	public function filter_recurring_query_args( $args = array() ) {

		// Override filtered query arguments completely
		$args = array(
			'relation' => 'AND',

			// Recurring Ends
			array(
				'relation' => 'OR',

				// No end (recurs forever) - exploits math in Date_Query
				// This might break someday. Works great now though!
				array(
					'column'    => 'recurrence_end',
					'inclusive' => true,
					'before'    => '0000-01-01 00:00:00'
				),

				// Ends after the beginning of this view
				array(
					'column'    => 'recurrence_end',
					'inclusive' => true,
					'after'     => $this->view_start
				)
			),

			// Make sure events are only for this year
			array(
				'relation' => 'AND',
				array(
					'column'  => 'start',
					'compare' => '>=',
					'year'    => $this->year
				),
				array(
					'column'  => 'end',
					'compare' => '<=',
					'year'    => $this->year
				)
			),
		);

		// Return the newly filtered query arguments
		return $args;
	}

	/**
	 * Prevent base class from setting cells
	 *
	 * @since 2.1.6
	 */
	protected function set_cells() {

	}

	/**
	 * Set item counts for the List mode list-table.
	 *
	 * @since 2.1.6
	 */
	protected function set_item_counts() {

		// Default return value
		$this->item_counts = array(
			'total' => 0
		);

		// Items to count
		if ( ! empty( $this->query->items ) ) {

			// Pluck all queried statuses
			$statuses = wp_list_pluck( $this->query->items, 'status' );

			// Get unique statuses only
			$statuses = array_unique( $statuses );

			// Set total to count of all items
			$this->item_counts['total'] = count( $this->query->items );

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
				$this->item_counts[ $status ] = count( $items );
			}
		}
	}

	/**
	 * Mock function for custom list table columns.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_columns() {

		// Default columns
		$columns = array(
			'title'    => esc_html_x( 'Title',    'Noun', 'sugar-calendar' ),
			'start'    => esc_html_x( 'Start',    'Noun', 'sugar-calendar' ),
			'end'      => esc_html_x( 'End',      'Noun', 'sugar-calendar' ),
			'duration' => esc_html_x( 'Duration', 'Noun', 'sugar-calendar' )
		);

		// Repeat column
		if ( has_filter( 'sugar_calendar_get_recurring_date_query_args' ) ) {
			$columns['repeat'] = esc_html_x( 'Repeats',  'Noun', 'sugar-calendar' );
		}

		// Return columns
		return $columns;
	}

	/**
	 * Allow columns to be sortable
	 *
	 * @since 2.0.0
	 *
	 * @return array An associative array containing the sortable columns
	 */
	protected function get_sortable_columns() {
		return array(
			'title'  => array( 'title', true ),
			'start'  => array( 'start', true ),
			'end'    => array( 'end',   true )
		);
	}

	/**
	 * Return the "title" column as the primary column name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'title';
	}

	/**
	 * Return the contents for the "Title" column.
	 *
	 * @since 2.0.0
	 *
	 * @param object $item
	 * @return string
	 */
	public function column_title( $item = null ) {

		// Start an output buffer to make syntax easier to read
		ob_start();

		// Items in trash are not editable
		if ( 'trash' === $item->status ) {

			?><strong class="status-trash"><?php

				echo $this->get_event_title( $item );

			?></strong><?php

		// Items not in trash get linked
		} else {

			// Get the for event color
			$color = $this->get_item_color( $item );

			// Wrap output in a helper div
			?><div data-color="<?php echo esc_attr( $color ); ?>"><strong><?php

				echo $this->get_event_link( $item );

			?></strong></div><?php
		}

		// Output the row actions
		echo $this->row_actions(
			$this->get_pointer_links( $item )
		);

		// Return the buffer
		return ob_get_clean();
	}

	/**
	 * Return the contents for the "Start" column.
	 *
	 * @since 2.0.15
	 *
	 * @param object $item
	 * @return string
	 */
	public function column_start( $item = null ) {

		// Default return value
		$retval = '&mdash;';

		// Bail if empty date
		if ( $item->is_empty_date( $item->start ) ) {
			return $retval;
		}

		// Floating
		$format = 'Y-m-d\TH:i:s';
		$tz     = 'floating';

		// Non-floating
		if ( ! empty( $item->start_tz ) ) {

			// Get the offset
			$offset = sugar_calendar_get_timezone_offset( array(
				'time'     => $item->start,
				'timezone' => $item->start_tz
			) );

			// Add timezone to format
			$format = "Y-m-d\TH:i:s{$offset}";
		}

		// Format the date/time
		$dt = $item->start_date( $format );

		// All-day Events have floating time zones
		if ( ! empty( $item->start_tz ) && ! $item->is_all_day() ) {
			$tz = $item->start_tz;
		}

		// Start the <time> tag, with timezone data
		$retval  = '<time datetime="' . esc_attr( $dt ) . '" title="' . esc_attr( $dt ) . '" data-timezone="' . esc_attr( $tz ) . '">';
		$retval .= '<span class="sc-date">' . $this->get_event_date( $item->start, $item->start_tz ) . '</span>';

		// Maybe add time if not all-day
		if ( ! $item->is_all_day() ) {
			$retval .= '<br><span class="sc-time">' . $this->get_event_time( $item->start, $item->start_tz ) . '</span>';

			// Maybe add timezone
			if ( ! empty( $item->start_tz ) ) {
				$retval .= '<br><span class="sc-timezone">' . sugar_calendar_format_timezone( $tz ) . '</span>';
			}
		}

		// Close the <time> tag
		$retval .= '</time>';

		// Return the <time> tag
		return $retval;
	}

	/**
	 * Return the contents for the "End" column.
	 *
	 * @since 2.0.15
	 *
	 * @param object $item
	 * @return string
	 */
	public function column_end( $item = null ) {

		// Default return value
		$retval = '&mdash;';

		// Bail if empty date
		if ( $item->is_empty_date( $item->end ) ) {
			return $retval;
		}

		// Bail if start & end are exactly the same
		if ( $item->start === $item->end ) {
			return $retval;
		}

		// Bail if all-day and only 1 day
		if ( $item->is_all_day() && ( $item->start_date( 'Y-m-d' ) === $item->end_date( 'Y-m-d' ) ) ) {
			return $retval;
		}

		// Floating
		$format = 'Y-m-d\TH:i:s';
		$tz     = 'floating';

		// Non-floating
		if ( ! empty( $item->end_tz ) ) {

			// Get the offset
			$offset = sugar_calendar_get_timezone_offset( array(
				'time'     => $item->end,
				'timezone' => $item->end_tz
			) );

			// Add timezone to format
			$format = "Y-m-d\TH:i:s{$offset}";
		}

		// Format the date/time
		$dt = $item->end_date( $format );

		// All-day Events have floating time zones
		if ( ! empty( $item->end_tz ) && ! $item->is_all_day() ) {
			$tz = $item->end_tz;

		// Maybe fallback to the start time zone
		} elseif ( ! empty( $item->start_tz ) ) {
			$tz = $item->start_tz;
		}

		// Start the <time> tag, with timezone data
		$retval  = '<time datetime="' . esc_attr( $dt ) . '" title="' . esc_attr( $dt ) . '" data-timezone="' . esc_attr( $tz ) . '">';
		$retval .= '<span class="sc-date">' . $this->get_event_date( $item->end, $item->end_tz ) . '</span>';

		// Maybe add time if not all-day
		if ( ! $item->is_all_day() ) {
			$retval .= '<br><span class="sc-time">' . $this->get_event_time( $item->end, $item->end_tz ) . '</span>';

			// Maybe add timezone
			if ( ! empty( $item->end_tz ) ) {
				$retval .= '<br><span class="sc-timezone">' . sugar_calendar_format_timezone( $tz ) . '</span>';
			}
		}

		// Close the <time> tag
		$retval .= '</time>';

		// Return the <time> tag
		return $retval;
	}

	/**
	 * Return the contents for the "Duration" column.
	 *
	 * @since 2.0.15
	 *
	 * @param object $item
	 * @return string
	 */
	public function column_duration( $item = null ) {

		// Default return value
		$retval = '&mdash;';

		// Duration
		if ( $item->is_all_day() ) {
			$retval = esc_html__( 'All Day', 'sugar-calendar' );

			// Maybe add duration if multiple all-day days
			if ( $item->is_multi() ) {
				$retval .= '<br>' . $this->get_human_diff_time( $item->start, $item->end );
			}

		// Get diff only if end exists
		} elseif ( ( $item->start !== $item->end ) && ! $item->is_empty_date( $item->end ) ) {

			// Default date times
			$start  = strtotime( $item->start );
			$end    = strtotime( $item->end   );

			// Adjust start by time zone
			if ( ! empty( $item->start_tz ) ) {
				$str   = sprintf( '%s %s', $item->start, $item->start_tz );
				$start = strtotime( $str );
			}

			// Adjust end by time zone
			if ( ! empty( $item->end_tz ) ) {
				$str   = sprintf( '%s %s', $item->end, $item->end_tz );
				$end   = strtotime( $str );
			}

			// Get human readible date time difference
			$retval     = $this->get_human_diff_time( $start, $end );

			// Look for a time zone difference
			$difference = $this->get_human_diff_timezone( $item->start_tz, $item->end_tz );

			// Wrap difference in a decorative span
			if ( ! empty( $difference ) ) {
				$retval .= '<br><span class="sc-timechange">' . esc_html( $difference ) . '</span>';
			}
		}

		// Return the duration
		return $retval;
	}

	/**
	 * Return the contents for the "Repeats" column.
	 *
	 * @todo Abstract for Advanced Recurring
	 *
	 * @since 2.0.15
	 *
	 * @param object $item
	 * @return string
	 */
	public function column_repeat( $item = null ) {

		// Default return value
		$retval = '&mdash;';

		// Get recurrence type
		if ( ! empty( $item->recurrence ) ) {
			$intervals = $this->get_recurrence_types();

			// Interval is known
			if ( isset( $intervals[ $item->recurrence ] ) ) {
				$retval = $intervals[ $item->recurrence ];
			}
		}

		// Return the repeat
		return $retval;
	}

	/**
	 * Paginate through months & years
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 */
	protected function pagination( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'small'  => '1 year',
			'large'  => '10 years',
			'labels' => array(
				'today'      => esc_html__( 'Today',          'sugar-calendar' ),
				'next_small' => esc_html__( 'Next Month',     'sugar-calendar' ),
				'next_large' => esc_html__( 'Next Year',      'sugar-calendar' ),
				'prev_small' => esc_html__( 'Previous Month', 'sugar-calendar' ),
				'prev_large' => esc_html__( 'Previous Year',  'sugar-calendar' )
			)
		) );

		// Return pagination
		return parent::pagination( $r );
	}

	/**
	 * Output all rows
	 *
	 * @since 2.0.0
	 */
	public function display_mode() {

		// Attempt to display rows
		if ( ! empty( $this->filtered_items ) ) {

			// Loop through items and show them
			foreach ( $this->filtered_items as $item ) {
				$this->single_row( $item );
			}

		// No rows to display
		} else {
			$this->no_items();
		}
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 2.0.0
	 */
	public function no_items() {

		// Get the column count
		$count = $this->get_column_count(); ?>
		<tr>
			<td colspan="<?php echo absint( $count ); ?>"><?php
				esc_html_e( 'No events found.', 'sugar-calendar' );
			?></td>
		</tr>
		<?php
	}

	/**
	 * Output a single row.
	 *
	 * @since 2.0.0
	 *
	 * @param object $item
	 */
	public function single_row( $item ) {

		// Default item end back to false, for "Duration" column
		$this->item_ends = false; ?>

		<tr id="event-<?php echo $item->id; ?>" class="">
			<?php $this->single_row_columns( $item ); ?>
		</tr>

		<?php
	}
}
