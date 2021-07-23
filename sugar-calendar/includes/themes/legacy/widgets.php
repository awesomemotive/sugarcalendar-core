<?php

/**
 * Return an array of registered widget IDs.
 *
 * @since 2.0.13
 *
 * @return array
 */
function sc_get_widget_ids() {
	return array(
		'sc_calendar_widget',
		'sc_category_widget',
		'sc_event_list_widget',
		'sc_filter_widget'
	);
}

/**
 * Register Widgets
 *
 * Registers the Sugar Calendar Widgets.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function sc_register_widgets() {
	register_widget( 'sc_events_widget' );
	register_widget( 'sc_events_list_widget' );
	register_widget( 'sc_event_categories_widget' );
	register_widget( 'sc_event_filter_widget' );
}

/**
 * Calendar Widget
 *
 * Shows the events calendar.
 *
 * @since 1.0.0
 */
class sc_events_widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'sc_calendar_widget',
			esc_html__( '(Sugar Calendar) Event Calendar', 'sugar-calendar' ),
			array(
				'description' => esc_html__( 'Displays a monthly event calendar widget.', 'sugar-calendar' )
			)
		);
	}

	/**
	 * Output the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args = array(), $instance = array() ) {
		$before_widget = ! empty( $args[ 'before_widget' ] )
			? $args[ 'before_widget' ]
			: '';
		$before_title = ! empty( $args[ 'before_title' ] )
			? $args[ 'before_title' ]
			: '';
		$after_title = ! empty( $args[ 'after_title' ] )
			? $args[ 'after_title' ]
			: '';
		$after_widget = ! empty( $args[ 'after_widget' ] )
			? $args[ 'after_widget' ]
			: '';
		$title = ! empty( $instance[ 'title' ] )
			? $instance[ 'title' ]
			: '';
		$size = ! empty( $instance[ 'size' ] )
			? $instance[ 'size' ]
			: 'small';
		$category = ! empty( $instance[ 'category' ] )
			? $instance[ 'category' ]
			: null;

		// New in 2.0.3
		$year = ! empty( $instance[ 'year' ] )
			? $instance[ 'year' ]
			: null;
		$month = ! empty( $instance[ 'month' ] )
			? $instance[ 'month' ]
			: null;

		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		do_action( 'sc_before_calendar_widget' );

		echo '<div id="sc_calendar_wrap_widget">';
		echo sc_get_events_calendar( $size, $category, 'month', $year, $month );
		echo '</div>';

		do_action( 'sc_after_calendar_widget' );

		echo $after_widget;
	}

	/**
	 * Update the widget
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance               = $old_instance;
		$instance[ 'title' ]    = strip_tags( $new_instance[ 'title' ]    );
		$instance[ 'size' ]     = strip_tags( $new_instance[ 'size' ]     );
		$instance[ 'category' ] = strip_tags( $new_instance[ 'category' ] );
		$instance[ 'month' ]    = strip_tags( $new_instance[ 'month' ]    );
		$instance[ 'year' ]     = strip_tags( $new_instance[ 'year' ]     );

		return $instance;
	}

	/**
	 * Output the widget form
	 *
	 * @param array $instance
	 */
	public function form( $instance = array() ) {

		$title = ! empty( $instance[ 'title' ] )
			? $instance[ 'title' ]
			: '';
		$size = ! empty( $instance[ 'size' ] )
			? $instance[ 'size' ]
			: '';
		$category = ! empty( $instance[ 'category' ] )
			? $instance[ 'category' ]
			: '';
		$month = ! empty( $instance[ 'month' ] )
			? $instance[ 'month' ]
			: '';
		$year = ! empty( $instance[ 'year' ] )
			? $instance[ 'year' ]
			: '';

		$tax   = sugar_calendar_get_calendar_taxonomy_id();
		$terms = get_terms( $tax ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'sugar-calendar' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php esc_html_e( 'Size:', 'sugar-calendar' ); ?></label>
			<select class="widefat <?php echo $this->get_field_name( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" id="<?php echo $this->get_field_id( 'size' ); ?>">
				<option value="small" <?php selected( 'small', $size ); ?>><?php esc_html_e( 'Small', 'sugar-calendar' ); ?></option>
				<option value="large" <?php selected( 'large', $size ); ?>><?php esc_html_e( 'Large', 'sugar-calendar' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php esc_html_e( 'Calendar:', 'sugar-calendar' ); ?></label>
			<select class="widefat <?php echo $this->get_field_name( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" id="<?php echo $this->get_field_id( 'category' ); ?>">
				<option value="0" <?php selected( 0, $category ); ?>><?php esc_html_e( 'All', 'sugar-calendar' ); ?></option><?php

				if ( ! empty( $terms  ) ) {
					foreach ( $terms as $term ) {
						echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $term->slug, $category, false ) . '>' . esc_html( $term->name ) . '</option>';
					}
				}

			?></select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'month' ); ?>"><?php esc_html_e( 'Default Month & Year:', 'sugar-calendar' ); ?></label><br>
			<input class="small" id="<?php echo $this->get_field_id( 'month' ); ?>" name="<?php echo $this->get_field_name( 'month' ); ?>" type="number" min="1" max="12" pattern="\d{1,2}" autocomplete="off" value="<?php echo esc_attr( $month ); ?>">
			<input class="small" id="<?php echo $this->get_field_id( 'year' ); ?>" name="<?php echo $this->get_field_name( 'year' ); ?>" type="number" min="1900" max="9999" pattern="\d{1,4}" autocomplete="off" value="<?php echo esc_attr( $year ); ?>">
			<br><span class="description"><?php esc_html_e( 'Leave empty for current', 'sugar-calendar' ); ?></span>
		</p>

		<?php
	}
}

/**
 * Event List Widget
 *
 * Shows a list of events.
 *
 * @since 1.0.0
 */
class sc_events_list_widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'sc_event_list_widget',
			esc_html__( '(Sugar Calendar) Event List', 'sugar-calendar' ),
			array(
				'description' => esc_html__( 'Displays all/upcoming/past events as a list.', 'sugar-calendar' )
			)
		);
	}

	/**
	 * Output the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args = array(), $instance = array() ) {
		$before_widget = ! empty( $args[ 'before_widget' ] )
			? $args[ 'before_widget' ]
			: '';
		$before_title = ! empty( $args[ 'before_title' ] )
			? $args[ 'before_title' ]
			: '';
		$after_title = ! empty( $args[ 'after_title' ] )
			? $args[ 'after_title' ]
			: '';
		$after_widget = ! empty( $args[ 'after_widget' ] )
			? $args[ 'after_widget' ]
			: '';
		$title = ! empty( $instance[ 'title' ] )
			? $instance[ 'title' ]
			: '';
		$display = ! empty( $instance[ 'display' ] )
			? $instance[ 'display' ]
			: 'all';
		$order = ! empty( $instance[ 'order' ] )
			? $instance[ 'order' ]
			: '';
		$category = ! empty( $instance[ 'category' ] )
			? $instance[ 'category' ]
			: null;
		$number = ! empty( $instance[ 'number' ] )
			? $instance[ 'number' ]
			: null;
		$show_title = ! empty( $instance[ 'show_title' ] )
			? $instance[ 'show_title' ]
			: null;
		$show_date = ! empty( $instance[ 'show_date' ] )
			? $instance[ 'show_date' ]
			: null;
		$show_time = ! empty( $instance[ 'show_time' ] )
			? $instance[ 'show_time' ]
			: null;
		$show_categories = ! empty( $instance[ 'show_categories' ] )
			? $instance[ 'show_categories' ]
			: null;

		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		if ( ! empty( $title ) && ! empty( $show_title ) ) {
			echo $before_title . $title . $after_title;
		}

		do_action( 'sc_before_event_list_widget' );

		echo '<div id="sc_list_wrap">';
		echo sc_get_events_list( $display, $category, $number, array('date' => $show_date, 'time' => $show_time, 'categories' => $show_categories), $order );
		echo '</div>';

		do_action( 'sc_after_event_list_widget' );

		echo $after_widget;
	}

	/**
	 * Update the widget
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance               = $old_instance;
		$instance[ 'title' ]    = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'display' ]  = strip_tags( $new_instance[ 'display' ] );
		$instance[ 'order' ]    = strip_tags( $new_instance[ 'order' ] );
		$instance[ 'category' ] = strip_tags( $new_instance[ 'category' ] );
		$instance[ 'number' ]   = intval( $new_instance[ 'number' ] );
		$instance[ 'show_title' ] = ! empty( $new_instance[ 'show_title' ] )
			? 1
			: null;
		$instance[ 'show_date' ] = ! empty( $new_instance[ 'show_date' ] )
			? 1
			: null;
		$instance[ 'show_time' ] = ! empty( $new_instance[ 'show_time' ] )
			? 1
			: null;
		$instance[ 'show_categories' ] = ! empty( $new_instance[ 'show_categories' ] )
			? 1
			: null;

		return $instance;
	}

	/**
	 * Output the widget form
	 *
	 * @param array $instance
	 */
	public function form( $instance = array() ) {

		$title = ! empty( $instance[ 'title' ] )
			? $instance[ 'title' ]
			: '';
		$display = ! empty( $instance[ 'display' ] )
			? $instance[ 'display' ]
			: '';
		$display_order = ( 'past' === $display )
			? 'DESC'
			: 'ASC';
		$category = ! empty( $instance[ 'category' ] )
			? $instance[ 'category' ]
			: '';
		$order = ! empty( $instance[ 'order' ] )
			? $instance[ 'order' ]
			: $display_order;
		$number = ! empty( $instance[ 'number' ] )
			? $instance[ 'number' ]
			: 5;
		$show_title = ! empty( $instance[ 'show_title' ] )
			? $instance[ 'show_title' ]
			: null;
		$show_date = ! empty( $instance[ 'show_date' ] )
			? $instance[ 'show_date' ]
			: null;
		$show_time = ! empty( $instance[ 'show_time' ] )
			? $instance[ 'show_time' ]
			: null;
		$show_categories = ! empty( $instance[ 'show_categories' ] )
			? $instance[ 'show_categories' ]
			: null;

		$tax   = sugar_calendar_get_calendar_taxonomy_id();
		$terms = get_terms( $tax ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'sugar-calendar' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php esc_html_e( 'Time Period:', 'sugar-calendar' ); ?></label>
			<select class="widefat <?php echo $this->get_field_name( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>" id="<?php echo $this->get_field_id( 'display' ); ?>">
				<option value="all" <?php selected( 'all', $display ); ?>><?php esc_html_e( 'All', 'sugar-calendar' ); ?></option>
				<option value="upcoming" <?php selected( 'upcoming', $display ); ?>><?php esc_html_e( 'Upcoming', 'sugar-calendar' ); ?></option>
				<option value="past" <?php selected( 'past', $display ); ?>><?php esc_html_e( 'Past', 'sugar-calendar' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php esc_html_e( 'Order:', 'sugar-calendar' ); ?></label>
			<select class="widefat <?php echo $this->get_field_name( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" id="<?php echo $this->get_field_id( 'order' ); ?>">
				<option value="" <?php selected( '', $order ); ?>><?php esc_html_e( 'Default for Period', 'sugar-calendar' ); ?></option>
				<option value="asc" <?php selected( 'asc', $order ); ?>><?php esc_html_e( 'Oldest First', 'sugar-calendar' ); ?></option>
				<option value="desc" <?php selected( 'desc', $order ); ?>><?php esc_html_e( 'Newest First', 'sugar-calendar' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php esc_html_e( 'Calendar:', 'sugar-calendar' ); ?></label>
			<select class="widefat <?php echo $this->get_field_name( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" id="<?php echo $this->get_field_id( 'category' ); ?>">
				<option value="0" <?php selected( 0, $category ); ?>><?php esc_html_e( 'All Calendars', 'sugar-calendar' ); ?></option><?php

				if ( count( $terms ) ) {
					foreach ( $terms as $term ) {
						echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $term->slug, $category, false ) . '>' . esc_html( $term->name ) . '</option>';
					}
				}
			?></select>
		</p>
		<p>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'number' ); ?>" style="width: 40px;" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo esc_attr( $number ); ?>">
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number to show', 'sugar-calendar' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" <?php echo checked( $show_title, 1 ); ?>">
			<label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php esc_html_e( 'Show widget title', 'sugar-calendar' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" <?php echo checked( $show_date, 1 ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _ex( 'Show event dates', 'Start & end if available', 'sugar-calendar' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_time' ); ?>" name="<?php echo $this->get_field_name( 'show_time' ); ?>" <?php echo checked( $show_time, 1 ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_time' ); ?>"><?php _ex( 'Show event times', 'Start & end if available', 'sugar-calendar' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_categories' ); ?>" name="<?php echo $this->get_field_name( 'show_categories' ); ?>" <?php echo checked( $show_categories, 1 ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_categories' ); ?>"><?php esc_html_e( 'Show event categories', 'sugar-calendar' ); ?></label>
		</p>

		<?php
	}
}

/**
 * Categories Widget
 *
 * Event categories widget class.
 *
 * @since 1.0.0
 */
class sc_event_categories_widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'sc_category_widget',
			esc_html__( '(Sugar Calendar) Calendar List', 'sugar-calendar' ),
			array(
				'description' => esc_html__( 'Display all of the available calendars as a list.', 'sugar-calendar' )
			)
		);
	}

	/**
	 * Output the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args = array(), $instance = array() ) {

		$before_widget = ! empty( $args[ 'before_widget' ] )
			? $args[ 'before_widget' ]
			: '';
		$before_title = ! empty( $args[ 'before_title' ] )
			? $args[ 'before_title' ]
			: '';
		$after_title = ! empty( $args[ 'after_title' ] )
			? $args[ 'after_title' ]
			: '';
		$after_widget = ! empty( $args[ 'after_widget' ] )
			? $args[ 'after_widget' ]
			: '';
		$title = ! empty( $instance[ 'title' ] )
			? $instance[ 'title' ]
			: '';
		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		$tax   = sugar_calendar_get_calendar_taxonomy_id();
		$terms = get_terms( $tax );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		do_action( 'sc_before_category_widget' );

		echo "<ul class=\"sc-category-widget\">\n";
		foreach ( $terms as $term ) {
			echo '<li><a href="' . get_term_link( $term ) . '" title="' . esc_attr( $term->name ) . '" rel="bookmark">' . esc_html( $term->name ) . '</a></li>' . "\n";
		}
		echo "</ul>\n";

		do_action( 'sc_after_category_widget' );

		echo $after_widget;
	}

	/**
	 * Update the widget
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );

		return $instance;
	}

	/**
	 * Output the widget form
	 *
	 * @param array $instance
	 */
	public function form( $instance = array() ) {
		$title = ! empty( $instance[ 'title' ] )
			? $instance[ 'title' ]
			: '';
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'sugar-calendar' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php
	}
}

/**
 * Event Filter Widget
 *
 * Filter Upcoming / Past Events
 *
 * @since 1.0.0
 */
class sc_event_filter_widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'sc_filter_widget',
			esc_html__( '(Sugar Calendar) Event Filters', 'sugar-calendar' ),
			array(
				'description' => esc_html__( 'Event Archive only. Controls for filtering how to list events.', 'sugar-calendar' )
			)
		);
	}

	/**
	 * Output the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args = array(), $instance = array() ) {
		$before_widget = ! empty( $args[ 'before_widget' ] )
			? $args[ 'before_widget' ]
			: '';
		$before_title = ! empty( $args[ 'before_title' ] )
			? $args[ 'before_title' ]
			: '';
		$after_title = ! empty( $args[ 'after_title' ] )
			? $args[ 'after_title' ]
			: '';
		$after_widget = ! empty( $args[ 'after_widget' ] )
			? $args[ 'after_widget' ]
			: '';
		$title = ! empty( $instance[ 'title' ] )
			? $instance[ 'title' ]
			: '';
		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		// Current
		$current = ' class="current"';

		// Filtering?
		$display = ! empty( $_GET['event-display'] )
			? sanitize_key( $_GET['event-display'] )
			: false;

		// Defaults
		if ( false === $display ) {
			$is_upcoming    = '';
			$is_in_progress = '';
			$is_past        = '';
			$is_all         = $current;

		// Custom
		} else {

			// Upcoming?
			$is_upcoming = ( 'upcoming' === $display )
				? $current
				: '';

			// In-progress?
			$is_in_progress = ( 'in-progress' === $display )
				? $current
				: '';

			// Past?
			$is_past = ( 'past' === $display )
				? $current
				: '';

			// All?
			$is_all = empty( $is_upcoming ) && empty( $is_in_progress ) && empty( $is_past )
				? $current
				: '';
		}

		// Order?
		$order = ! empty( $_GET['event-order'] )
			? sanitize_key( $_GET['event-order'] )
			: false;

		// Defaults
		if ( false === $order ) {
			$is_asc  = '';
			$is_desc = $current;

		// Custom
		} else {

			// Ascending?
			$is_asc = ( 'asc' === $order )
				? $current
				: '';

			// Descending?
			$is_desc = ( 'desc' === $order )
				? $current
				: '';
		}

		// Before
		do_action( 'sc_before_filter_widget' );

		// Filters
		echo '<ul class="sc_events_filter">';
		echo '<li class="sc_event_filter"><a href="' . esc_url( add_query_arg( 'event-display', 'upcoming'    ) ) . '"' . $is_upcoming    . '>' . esc_html__( 'Upcoming events',    'sugar-calendar' ) . '</a></li>';
		echo '<li class="sc_event_filter"><a href="' . esc_url( add_query_arg( 'event-display', 'in-progress' ) ) . '"' . $is_in_progress . '>' . esc_html__( 'In-progress events', 'sugar-calendar' ) . '</a></li>';
		echo '<li class="sc_event_filter"><a href="' . esc_url( add_query_arg( 'event-display', 'past'        ) ) . '"' . $is_past        . '>' . esc_html__( 'Past events',        'sugar-calendar' ) . '</a></li>';
		echo '<li class="sc_event_filter"><a href="' . esc_url( remove_query_arg( 'event-display' ) ) . '"' . $is_all . '>' . esc_html__( 'All events', 'sugar-calendar' ) . '</a></li>';
		echo '<li class="sc_event_filter sc_event_order">';

		// Order
		echo '<span class="sc_event_order_label">' . esc_html__( 'Order:', 'sugar-calendar' ) . '</span>&nbsp;';
		echo '<a href="' . esc_url( add_query_arg( 'event-order', 'desc' ) ) . '"' . $is_desc . '>' . esc_html__( 'Newest first', 'sugar-calendar' ) . '</a>';
		echo '<span class="sc_event_order_sep"> - </span>';
		echo '<a href="' . esc_url( add_query_arg( 'event-order', 'asc'  ) ) . '"' . $is_asc  . '>' . esc_html__( 'Oldest first', 'sugar-calendar' ) . '</a>';
		echo '</li>';
		echo '</ul>';

		// After
		do_action( 'sc_after_filter_widget' );

		echo $after_widget;
	}

	/**
	 * Update the widget
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );

		return $instance;
	}

	/**
	 * Output the widget form
	 *
	 * @param array $instance
	 */
	public function form( $instance = array() ) {
		$title = ! empty( $instance[ 'title' ] )
			? $instance[ 'title' ]
			: '';
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'sugar-calendar' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php
	}
}
