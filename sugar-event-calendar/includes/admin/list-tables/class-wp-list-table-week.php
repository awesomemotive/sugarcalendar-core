<?php
/**
 * Calendar Week List Table
 *
 * @since 2.0.0
 *
 * @package Plugins/Site/Events/Admin/ListTables/Week
 *
 * @see WP_events_List_Table
 */
namespace Sugar_Calendar\Admin\Mode;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Calendar Week List Table
 *
 * This list table is responsible for showing events in a traditional table,
 * even though it extends the `WP_List_Table` class. Tables & lists & tables.
 *
 * @since 2.0.0
 */
class Week extends Base_List_Table {

	/**
	 * The mode of the current view
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $mode = 'week';

	/**
	 * The main constructor method
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// Get days
		$days       = array_values( $this->get_week_days() );
		$first_day  = $days[ 0 ];
		$last_day   = $days[ count( $days ) - 1 ];
		$week_start = ( $this->start_of_week == date_i18n( 'w', $this->today ) );

		// Reset the week
		$where_is_thumbkin = ( true === $week_start )
			? "this {$first_day} midnight"
			: "last {$first_day} midnight";

		// Get fresh for the weekend
		$here_i_am = ( true === $week_start )
			? "this {$last_day} midnight -1 second"
			: "next {$first_day} midnight -1 second";

		// Setup the week ranges
		$this->grid_start = strtotime( $where_is_thumbkin, $this->today );
		$this->grid_end   = strtotime( $here_i_am,         $this->today );

		// Setup the week ranges
		$view_start = date_i18n( 'Y-m-d H:i:s', $this->grid_start );
		$view_end   = date_i18n( 'Y-m-d H:i:s', $this->grid_end   );

		// Setup views
		$this->set_view( $view_start, $view_end );
	}

	/**
	 * Setup the list-table columns.
	 *
	 * Overrides base class to add the "week" column.
	 *
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @since 2.0.0
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {
		static $retval = null;

		// Calculate if not calculated already
		if ( null === $retval ) {

			// Setup return value
			$retval = array(
				'hour' => sprintf( esc_html_x( 'Wk. %s', 'Week number', 'sugar-calendar' ), date_i18n( 'W', $this->today ) )
			);

			// PHP day => day ID
			$days = $this->get_week_days();

			// Set initial time
			$time = $this->grid_start;

			// Loop through days and add them to the return value
			foreach ( $days as $day ) {
				$retval[ $day ] = date_i18n( 'D, M. j', $time );
				$time           = $time + ( DAY_IN_SECONDS * 1 );
			}
		}

		// Return columns
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
			'small'  => '1 week',
			'large'  => '1 month',
			'labels' => array(
				'today'      => esc_html__( 'Today',          'sugar-calendar' ),
				'next_small' => esc_html__( 'Next Week',      'sugar-calendar' ),
				'next_large' => esc_html__( 'Next Month',     'sugar-calendar' ),
				'prev_small' => esc_html__( 'Previous Week',  'sugar-calendar' ),
				'prev_large' => esc_html__( 'Previous Month', 'sugar-calendar' )
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

		// Start an output buffer
		ob_start(); ?>

		<tr class="all-day">
			<th><?php esc_html_e( 'All-day', 'sugar-calendar' ); ?></th><?php

			// Loop through days
			for ( $i = 0; $i <= $this->day_count - 1; $i++ ) {

				// Setup the row and offset
				$offset = ceil( DAY_IN_SECONDS * $i );

				// Calc the day/month/year
				$day    = date_i18n( 'd', $this->grid_start + $offset );
				$month  = date_i18n( 'm', $this->grid_start + $offset );
				$year   = date_i18n( 'Y', $this->grid_start + $offset );

				// Set the current cell
				$this->set_current_cell( array(
					'start' => mktime( 0,  0,  0, $month, $day, $year ),
					'end'   => mktime( 23, 59, 0, $month, $day, $year ),
					'type'  => 'all_day',
					'index' => $i
				) );

				?><td><?php echo $this->get_day_row_cell(); ?></td><?php

				// Bump the day
				++$day;
			}
			?>
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

		// Start an output buffer
		ob_start(); ?>

		<tr class="multi-day">
			<th><?php esc_html_e( 'Multi-day', 'sugar-calendar' ); ?></th><?php

			// Loop through days
			for ( $i = 0; $i <= $this->day_count - 1; $i++ ) {

				// Setup the row and offset
				$offset = ceil( DAY_IN_SECONDS * $i );

				// Calc the day/month/year
				$day    = date_i18n( 'd', $this->grid_start + $offset );
				$month  = date_i18n( 'm', $this->grid_start + $offset );
				$year   = date_i18n( 'Y', $this->grid_start + $offset );

				// Set the current cell
				$this->set_current_cell( array(
					'start' => mktime( 0,  0,  0, $month, $day, $year ),
					'end'   => mktime( 23, 59, 0, $month, $day, $year ),
					'type'  => 'multi_day',
					'index' => $i
				) );

				?><td><?php echo $this->get_day_row_cell(); ?></td><?php

				// Bump the day
				++$day;
			}
			?>
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

		// Default column
		$column = 0;

		// Loop through hours in days of week
		for ( $i = 0; $i < ( $this->day_count * 24 ); $i++ ) {

			// Setup the row and offset
			$row    = floor( $i / $this->day_count );
			$offset = ceil( DAY_IN_SECONDS * $column );

			// Calc the day/month/year
			$day    = date_i18n( 'd', $this->grid_start + $offset );
			$month  = date_i18n( 'm', $this->grid_start + $offset );
			$year   = date_i18n( 'Y', $this->grid_start + $offset );

			// Setup cell boundaries
			$this->set_current_cell( array(
				'start'  => mktime( $row, 0,  0, $month, $day, $year ),
				'end'    => mktime( $row, 59, 0, $month, $day, $year ),
				'row'    => $row,
				'index'  => $i,
				'offset' => $column
			) );

			// New row
			if ( $this->start_row() ) {
				echo $this->get_row_start();
			}

			// Get this table cell
			echo $this->get_row_cell();

			// Bump offset
			++$column;

			// Close row
			if ( $this->end_row() ) {
				echo $this->get_row_end();
				$column = 0;
			}
		}
	}
}
