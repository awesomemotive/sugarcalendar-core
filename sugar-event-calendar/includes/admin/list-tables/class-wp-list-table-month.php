<?php
/**
 * Calendar Month List Table
 *
 * @package Plugins/Site/Events/Admin/ListTables/Month
 *
 * @see WP_events_List_Table
 */
namespace Sugar_Calendar\Admin\Mode;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Calendar Month List Table
 *
 * This list table is responsible for showing events in a traditional table,
 * even though it extends the `WP_List_Table` class. Tables & lists & tables.
 *
 * @since 2.0.0
 */
class Month extends Base_List_Table {

	/**
	 * The mode of the current view
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $mode = 'month';

	/**
	 * The main constructor method
	 *
	 * @since 2.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// View start
		$view_start = "{$this->year}-{$this->month}-01 00:00:00";

		// Month boundaries
		$this->grid_start = mysql2date( 'U', $view_start );
		$this->grid_end   = strtotime( '+1 month -1 second', $this->grid_start );

		// View end
		$view_end = date_i18n( 'Y-m-d H:i:s', $this->grid_end );

		// Set the view
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
				'week' => esc_html__( 'Week', 'sugar-calendar' )
			);

			// PHP day => day ID
			$days = $this->get_week_days();

			// Loop through days and add them to the return value
			foreach ( $days as $key => $day ) {
				$retval[ $day ] = $GLOBALS['wp_locale']->get_weekday( $key );
			}
		}

		// Return columns
		return $retval;
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 2.0.0
	 */
	protected function get_row_start() {

		// Hour for row
		$week = date_i18n( 'W', $this->get_current_cell( 'start' ) );

		// No row classes
		$classes = array(
			'week',
			"week-{$week}"
		);

		// Is this the current week?
		if ( date_i18n( 'W', $this->now ) === $week ) {
			$classes[] = 'this-week';
		}

		// Week column
		$hidden = in_array( 'week', $this->get_hidden_columns(), true )
			? 'hidden'
			: '';

		// Start an output buffer
		ob_start(); ?>

		<tr class="<?php echo implode( ' ', $classes ); ?>"><th class="week <?php echo esc_attr( $hidden ); ?>"><?php echo esc_html( $week ); ?></th>

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
	protected function get_row_pad() {

		// Start an output buffer
		ob_start(); ?>

			<th class="padding <?php echo $this->get_cell_classes(); ?>"></th>

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

		// Calculate the day of the month
		$day = $this->get_current_cell( 'start_day' );

		// Calculate link to day view
		$link_to_day  = add_query_arg( array(
			'mode' => 'day',
			'cy'   => $this->year,
			'cm'   => $this->month,
			'cd'   => $day
		), $this->get_base_url() );

		// Link to add new event on this day
		$add_event_for_day = add_query_arg( array(
			'post_type' => $this->get_primary_post_type(),
			'start_day' => strtotime( "{$this->year}/{$this->month}/{$day}" )
		), admin_url( 'post-new.php' ) );

		// Start an output buffer
		ob_start(); ?>

		<td class="<?php echo $this->get_cell_classes(); ?>">
			<a href="<?php echo esc_url( $link_to_day ); ?>" class="day-number">
				<?php echo (int) $day; ?>
			</a>

			<a href="<?php echo esc_url( $add_event_for_day ); ?>" class="add-event-for-day">
				<i class="dashicons dashicons-plus"></i>
			</a>

			<div class="events-for-cell">
				<?php echo $this->get_events_for_cell(); ?>
			</div>
		</td>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Display a calendar by month and year
	 *
	 * @since 2.0.0
	 */
	protected function display_mode() {

		// Get maximum, offset, and row
		$maximum = ceil( ( $this->grid_end - $this->grid_start ) / DAY_IN_SECONDS );
		$offset  = $this->get_day_offset( $this->grid_start );
		$row     = 0;

		// Loop through days of the month
		for ( $i = 0; $i < ( $maximum + $offset ); $i++ ) {

			// Setup the day
			$day = ( $i - $offset ) + 1;

			// Setup cell boundaries
			$this->set_current_cell( array(
				'start'  => mktime( 0,  0,  0,  $this->month, $day, $this->year ),
				'end'    => mktime( 23, 59, 59, $this->month, $day, $this->year ),
				'row'    => $row,
				'index'  => $i,
				'offset' => $offset
			) );

			// Maybe start a new row?
			if ( $this->start_row() ) {
				echo $this->get_row_start();
			}

			// Pad day(s)
			if ( $i < $offset ) {
				echo $this->get_row_pad();

			// Month day
			} else {
				echo $this->get_row_cell();
			}

			// Maybe end the row?
			if ( $this->end_row() ) {
				echo $this->get_row_end();
				++$row;
			}
		}
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
			'small'  => '1 month',
			'large'  => '1 year',
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
}
