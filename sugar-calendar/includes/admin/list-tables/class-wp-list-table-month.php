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
		$this->grid_start = strtotime( $view_start );
		$this->grid_end   = strtotime( '+1 month -1 second', $this->grid_start );

		// View end
		$view_end = gmdate( 'Y-m-d H:i:s', $this->grid_end );

		// Set the view
		$this->set_view( $view_start, $view_end );

		// Filter the default hidden columns
		add_filter( 'default_hidden_columns', array( $this, 'hide_week_column' ), 10, 2 );
	}

	/**
	 * Maybe hide the week column in Month view.
	 *
	 * It's hidden by default in Month view, but could be made optionally
	 * visible at a later date.
	 *
	 * @since 2.0.15
	 *
	 * @param array  $columns The columns that are hidden by default
	 * @param object $screen  The current screen object
	 *
	 * @return array
	 */
	public function hide_week_column( $columns = array(), $screen = false ) {

		// Bail if not on the correct screen
		if ( sugar_calendar_get_admin_page_id() !== $screen->id ) {
			return $columns;
		}

		// Add Week column to default hidden columns
		array_push( $columns, 'week');

		// Return merged columns
		return $columns;
	}

	/**
	 * Setup the list-table columns.
	 *
	 * Overrides base class to add the hidden "week" column.
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

		// Get the start
		$start = $this->get_current_cell( 'start_dto' );

		// Get cells
		$day   = $start->format( 'd' );
		$month = $start->format( 'm' );
		$year  = $start->format( 'Y' );

		// Week for row
		$week  = $this->get_week_for_timestamp( $start->format( 'U' ) );

		// Calculate link to week view
		$link_to_day = add_query_arg( array(
			'mode' => 'week',
			'cy'   => $year,
			'cm'   => $month,
			'cd'   => $day
		), $this->get_base_url() );

		// No row classes
		$classes = array(
			'week',
			"week-{$week}"
		);

		// Is this the current week?
		if ( $this->get_week_for_timestamp( $this->now ) === $week ) {
			$classes[] = 'this-week';
		}

		// Week column
		$columns = $this->get_hidden_columns();
		$hidden  = in_array( 'week', $columns, true )
			? 'hidden'
			: '';

		// Start an output buffer
		ob_start(); ?>

		<tr class="<?php echo implode( ' ', $classes ); ?>">
			<th class="week column-week <?php echo esc_attr( $hidden ); ?>">
				<a href="<?php echo esc_url( $link_to_day ); ?>">
					<span><?php echo esc_html( $week ); ?></span>
				</a>
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

		// Get the start
		$start = $this->get_current_cell( 'start_dto' );

		// Calculate the day of the month
		$day   = $start->format( 'd' );

		// Arguments
		$args = array(
			'mode' => 'day',
			'cy'   => $this->year,
			'cm'   => $this->month,
			'cd'   => $day
		);

		// Calculate link to day view
		$link_to_day = add_query_arg( $args, $this->get_base_url() );

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

		// Get offset
		$offset = $this->get_day_offset( $this->grid_start );

		// Loop through days of the month
		foreach ( $this->cells as $cell ) {

			// Set the current cell
			$this->current_cell = $cell;

			// Maybe start a new row?
			if ( $this->start_row() ) {
				echo $this->get_row_start();
			}

			// Get the index
			$index = $this->get_current_cell( 'index' );

			// Pad day(s)
			if ( $index < $offset ) {
				echo $this->get_row_pad();

			// Month day
			} else {
				echo $this->get_row_cell();
			}

			// Maybe end the row?
			if ( $this->end_row() ) {
				echo $this->get_row_end();
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
