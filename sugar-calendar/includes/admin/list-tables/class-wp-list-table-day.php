<?php
/**
 * Calendar Day List Table
 *
 * @since 2.0.0
 *
 * @package Plugins/Site/Events/Admin/ListTables/Day
 *
 * @see WP_events_List_Table
 */
namespace Sugar_Calendar\Admin\Mode;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Calendar Day List Table
 *
 * This list table is responsible for showing events in a traditional table,
 * even though it extends the `WP_List_Table` class. Tables & lists & tables.
 *
 * @since 2.0.0
 */
class Day extends Base_List_Table {

	/**
	 * The mode of the current view
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $mode = 'day';

	/**
	 * The main constructor method
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// Set the day count to 1
		$this->day_count = 1;

		// Setup the week ranges
		$this->grid_start = strtotime( 'midnight',           $this->today );
		$this->grid_end   = strtotime( 'tomorrow -1 second', $this->grid_start );

		// Setup the day ranges
		$view_start = gmdate( 'Y-m-d H:i:s', $this->grid_start );
		$view_end   = gmdate( 'Y-m-d H:i:s', $this->grid_end   );

		// Setup views
		$this->set_view( $view_start, $view_end );
	}

	/**
	 * Setup the list-table columns
	 *
	 * Overrides base class to add the "week" column.
	 *
	 * @since 2.0.0
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {

		// Set initial time
		$time     = $this->grid_start;

		// Lowercase day, for column key
		$day      = strtolower( gmdate( 'l', $time ) );

		// Day text
		$day_text = gmdate( 'l, F j, Y', $time );

		// Arguments
		$args = array(
			'mode' => 'week',
			'cy'   => gmdate( 'Y', $time ),
			'cm'   => gmdate( 'm', $time ),
			'cd'   => gmdate( 'd', $time )
		);

		// Calculate link to day view
		$link_to_week = add_query_arg( $args, $this->get_persistent_url() );

		// Link text
		$string = esc_html_x( 'Week %s', 'Week number', 'sugar-calendar' );
		$week   = $this->get_week_for_timestamp( $this->today );
		$text   = sprintf( $string, $week );

		// Return Week & Day
		return array(
			'hour' => '<a href="' . esc_url( $link_to_week ) . '">' . esc_html( $text ) . '</a>',
			$day   => '<span>' . esc_html( $day_text ) . '</span>',
		);
	}

	/**
	 * Paginate through days & weeks
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 */
	protected function pagination( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'small'  => '1 day',
			'large'  => '1 week',
			'labels' => array(
				'today'      => esc_html__( 'Today',         'sugar-calendar' ),
				'next_small' => esc_html__( 'Tomorrow',      'sugar-calendar' ),
				'next_large' => esc_html__( 'Next Week',     'sugar-calendar' ),
				'prev_small' => esc_html__( 'Yesterday',     'sugar-calendar' ),
				'prev_large' => esc_html__( 'Previous Week', 'sugar-calendar' )
			)
		) );

		// Return pagination
		return parent::pagination( $r );
	}

	/**
	 * Skip items based on their all-day or multi-day duration.
	 *
	 * @since 2.0.0
	 *
	 * @param object $item
	 *
	 * @return bool
	 */
	protected function skip_item_in_cell( $item = false ) {

		// Default return value
		$retval = false;

		// Get the current cell type
		$cell_type = $this->get_current_cell( 'type' );

		// Maybe skip an event in a cell
		switch ( $cell_type ) {

			// Only allow all-day events in all-day cells
			case 'all_day' :
				if ( ! $item->is_all_day() ) {
					$retval = true;
				}
				break;

			// Only allow multi-day events in multi-day cells
			case 'multi_day' :
				if ( $item->is_all_day() || ! $item->is_multi( 'j' ) ) {
					$retval = true;
				}
				break;

			// Prevent all-day and multi-day in regular cells
			default :
				if ( $item->is_all_day() || $item->is_multi( 'j' ) ) {
					$retval = true;
				}
				break;
		}

		// Return if skipping
		return (bool) $retval;
	}

	/**
	 * Return the class for the "hour" column.
	 *
	 * @since 2.0.15
	 *
	 * @return string
	 */
	protected function get_hour_class() {

		// Hour column
		$columns = $this->get_hidden_columns();

		// Return
		return in_array( 'hour', $columns, true )
			? ' hidden'
			: '';
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 2.0.0
	 */
	protected function get_all_day_row() {

		// Set the day
		$day   = gmdate( 'd', $this->grid_start );
		$month = gmdate( 'm', $this->grid_start );
		$year  = gmdate( 'Y', $this->grid_start );

		// Start an output buffer
		ob_start(); ?>

		<tr class="all-day">
			<th class="column-hour<?php echo $this->get_hour_class(); ?>"><?php

				esc_html_e( 'All-day', 'sugar-calendar' );

			?></th><?php

			// Set the boundaries
			$start = gmmktime( 0,  0,  0,  $month, $day, $year );
			$end   = gmmktime( 23, 59, 59, $month, $day, $year );

			// Arguments
			$args  = array(
				'start' => $start,
				'end'   => $end,
				'type'  => 'all_day',
				'index' => 0
			);

			// Set the current cell
			$this->set_current_cell( $args );

			?><td><?php echo $this->get_day_row_cell(); ?></td>
		</tr>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 2.0.0
	 */
	protected function get_multi_day_row() {

		// Set the day
		$day   = gmdate( 'd', $this->grid_start );
		$month = gmdate( 'm', $this->grid_start );
		$year  = gmdate( 'Y', $this->grid_start );

		// Start an output buffer
		ob_start(); ?>

		<tr class="multi-day">
			<th class="column-hour<?php echo $this->get_hour_class(); ?>"><?php

				esc_html_e( 'Multi-day', 'sugar-calendar' );

			?></th><?php

			// Set the boundaries
			$start = gmmktime( 0,  0,  0,  $month, $day, $year );
			$end   = gmmktime( 23, 59, 59, $month, $day, $year );

			// Arguments
			$args  = array(
				'start' => $start,
				'end'   => $end,
				'type'  => 'multi_day',
				'index' => 0
			);

			// Set the current cell
			$this->set_current_cell( $args );

			?><td><?php echo $this->get_day_row_cell(); ?></td>
		</tr>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row
	 *
	 * @since 2.0.0
	 */
	protected function get_day_row_cell() {

		// Start an output buffer
		ob_start(); ?>

		<div class="events-for-cell">
			<?php echo $this->get_events_for_cell(); ?>
		</div>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 2.0.0
	 */
	protected function get_row_start() {

		// Get the start time
		$start = $this->get_current_cell( 'start_dto' );

		// Hour for row
		$hour  = $start->format( 'H' );

		// No row classes
		$classes = array(
			'hour',
			"hour-{$hour}"
		);

		// Is this the current hour?
		if ( gmdate( 'H', $this->now ) === $hour ) {
			$classes[] = 'this-hour';
		}

		// Format based on clock type
		$format = ( '12' === sugar_calendar_get_clock_type() )
			? 'g:i a'
			: 'H:i';

		// Start an output buffer
		ob_start(); ?>

		<tr class="<?php echo implode( ' ', $classes ); ?>">
			<th class="column-hour<?php echo $this->get_hour_class(); ?>">
				<?php echo $start->format( $format ); ?>
			</th>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * End the week with a closed table row
	 *
	 * @since 2.0.0
	 */
	protected function get_row_end() {

		// Start an output buffer
		ob_start(); ?>

			</tr>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row
	 *
	 * @since 2.0.0
	 */
	protected function get_row_cell() {

		// Start an output buffer
		ob_start(); ?>

		<td class="<?php echo $this->get_cell_classes(); ?>">
			<div class="events-for-cell">
				<?php echo $this->get_events_for_cell(); ?>
			</div>
		</td>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Set the items for all of the cells
	 *
	 * @since 2.1.3
	 */
	protected function set_cells() {

		// All Day, Multi Day cells
		for ( $i = 0; $i <= $this->day_count - 1; $i++ ) {

			// Setup the row and offset
			$offset = ceil( DAY_IN_SECONDS * $i );
			$dt     = $this->grid_start + $offset;

			// Calc the day/month/year
			$day    = gmdate( 'd', $dt );
			$month  = gmdate( 'm', $dt );
			$year   = gmdate( 'Y', $dt );

			// Set the boundaries
			$start  = gmmktime( 0,  0,  0,  $month, $day, $year );
			$end    = gmmktime( 23, 59, 59, $month, $day, $year );

			// Arguments
			$args   = array(
				'start' => $start,
				'end'   => $end,
				'type'  => 'all_day',
				'index' => $i
			);

			// Setup all-daycell boundaries
			$this->set_cell_boundaries( $args );

			// Setup all-day cell items
			$this->set_cell_items();

			// Set type to multi-day
			$args['type'] = 'multi_day';

			// Setup multi-day cell boundaries
			$this->set_cell_boundaries( $args );

			// Setup multi-day cell items
			$this->set_cell_items();

			// Bump the day
			++$day;
		}

		// Loop through hours in day
		for ( $i = 0; $i <= ( $this->day_count * 24 ) - 1; $i++ ) {

			// Setup the row and offset
			$row   = floor( $i / $this->day_count );

			// Set the boundaries
			$start = gmmktime( $row, 0,  0,  $this->month, $this->day, $this->year );
			$end   = gmmktime( $row, 59, 59, $this->month, $this->day, $this->year );

			// Arguments
			$args  = array(
				'start'  => $start,
				'end'    => $end,
				'row'    => $row,
				'index'  => $i,
				'offset' => 0
			);

			// Setup cell boundaries
			$this->set_cell_boundaries( $args );

			// Setup cell items
			$this->set_cell_items();

			// Close row
			if ( $this->end_row() ) {
				echo $this->get_row_end();
			}
		}

		// Cleanup
		$this->current_cell = array();
	}

	/**
	 * Display a calendar by mode & range
	 *
	 * @since 2.0.0
	 */
	protected function display_mode() {

		// All day events
		echo $this->get_all_day_row();

		// Multi day events
		echo $this->get_multi_day_row();

		// Loop through days of the month
		for ( $i = 0; $i <= ( $this->day_count * 24 ) - 1; $i++ ) {

			// Setup the row
			$row   = floor( $i / $this->day_count );

			// Set the boundaries
			$start = gmmktime( $row, 0,  0,  $this->month, $this->day, $this->year );
			$end   = gmmktime( $row, 59, 59, $this->month, $this->day, $this->year );

			// Arrguments
			$args  = array(
				'start'  => $start,
				'end'    => $end,
				'row'    => $row,
				'index'  => $i,
				'offset' => 0
			);

			// Setup cell boundaries
			$this->set_current_cell( $args );

			// New row
			if ( $this->start_row() ) {
				echo $this->get_row_start();
			}

			// Get this table cell
			echo $this->get_row_cell();

			// Close row
			if ( $this->end_row() ) {
				echo $this->get_row_end();
			}
		}
	}
}
