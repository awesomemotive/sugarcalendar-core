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
		$this->grid_start = strtotime( 'midnight',            $this->today );
		$this->grid_end   = strtotime( 'tomorrow  -1 second', $this->grid_start );

		// Setup the day ranges
		$view_start = date_i18n( 'Y-m-d H:i:s', $this->grid_start );
		$view_end   = date_i18n( 'Y-m-d H:i:s', $this->grid_end   );

		// Setup views
		$this->set_view( $view_start, $view_end );
	}

	/**
	 * Setup the list-table columns
	 *
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {

		// Lowercase day, for column key
		$day = strtolower( date_i18n( 'l', $this->grid_start ) );

		// Return Week & Day
		return array(
			'hour' => sprintf( esc_html_x( 'Wk. %s', 'Week number', 'sugar-calendar' ), date_i18n( 'W', $this->today ) ),
			$day   => date_i18n( 'l, F j, Y', $this->grid_start ),
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
	 * @return boolean
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
				if ( $item->is_all_day() || ! $item->is_multi( 'day' ) ) {
					$retval = true;
				}
				break;

			// Prevent all-day and multi-day in regular cells
			default :
				if ( $item->is_all_day() || $item->is_multi( 'day' ) ) {
					$retval = true;
				}
				break;
		}

		// Return if skipping
		return (boolean) $retval;
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 2.0.0
	 */
	protected function get_all_day_row() {

		// Set the day
		$day = date_i18n( 'd', $this->grid_start );

		// Start an output buffer
		ob_start(); ?>

		<tr class="all-day">
			<th><?php esc_html_e( 'All-day', 'sugar-calendar' ); ?></th><?php

			// Set the current cell
			$this->set_current_cell( array(
				'start' => mktime( 0,  0,  0, $this->month, $day, $this->year ),
				'end'   => mktime( 23, 59, 0, $this->month, $day, $this->year ),
				'type'  => 'all_day',
				'index' => 0
			) );

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
		$day = date_i18n( 'd', $this->grid_start );

		// Start an output buffer
		ob_start(); ?>

		<tr class="multi-day">
			<th><?php esc_html_e( 'Multi-day', 'sugar-calendar' ); ?></th><?php

			// Set the current cell
			$this->set_current_cell( array(
				'start' => mktime( 0,  0,  0, $this->month, $day, $this->year ),
				'end'   => mktime( 23, 59, 0, $this->month, $day, $this->year ),
				'type'  => 'multi_day',
				'index' => 0
			) );

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
		$start = $this->get_current_cell( 'start' );

		// Hour for row
		$hour = date_i18n( 'H', $start );

		// No row classes
		$classes = array(
			'hour',
			"hour-{$hour}"
		);

		// Is this the current hour?
		if ( date_i18n( 'H', $this->now ) === $hour ) {
			$classes[] = 'this-hour';
		}

		// Start an output buffer
		ob_start(); ?>

		<tr class="<?php echo implode( ' ', $classes ); ?>"><th><?php echo date_i18n( 'g:i a', $start ); ?></th>

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
			$row = floor( $i / $this->day_count );

			// Setup cell boundaries
			$this->set_current_cell( array(
				'start'  => mktime( $row, 0,  0, $this->month, $this->day, $this->year ),
				'end'    => mktime( $row, 59, 0, $this->month, $this->day, $this->year ),
				'row'    => $row,
				'index'  => $i,
				'offset' => 0
			) );

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
