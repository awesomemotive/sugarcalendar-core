<?php

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
 * @access      private
 * @since       1.0
 * @return      void
 */
class sc_events_widget extends WP_Widget {

	/** constructor */
	public function __construct() {
		parent::__construct( 'sc_calendar_widget', __('(Sugar Calendar) Event Calendar', 'sugar-calendar'), array('description' => __('Displays a monthly event calendar widget.', 'sugar-calendar')));
	}

	/**
	 *
	 * @see WP_Widget::widget
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$before_widget = isset($args['before_widget']) ? $args['before_widget'] : '';
		$before_title  = isset($args['before_title']) ? $args['before_title'] : '';
		$after_title   = isset($args['after_title']) ? $args['after_title'] : '';
		$after_widget  = isset($args['after_widget']) ? $args['after_widget'] : '';
		$title         = isset($instance['title']) ? $instance['title'] : '';
		$title         = apply_filters('widget_title', $title );
		$size          = isset($instance['size']) ? $instance['size'] : 'small';
		$category      = isset($instance['category']) ? $instance['category'] : null;

		// New in 2.0.3
		$year          = ! empty($instance['year']) ? $instance['year'] : null;
		$month         = ! empty($instance['month']) ? $instance['month'] : null;

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		do_action('sc_before_calendar_widget');

		echo '<div id="sc_calendar_wrap_widget">';
		echo sc_get_events_calendar( $size, $category, 'month', $year, $month );
		echo '</div>';

		do_action('sc_after_calendar_widget');

		echo $after_widget;
	}

	/**
	 * @see WP_Widget::update
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update($new_instance, $old_instance) {
		$instance             = $old_instance;
		$instance['title']    = strip_tags($new_instance['title']);
		$instance['size']     = strip_tags($new_instance['size']);
		$instance['category'] = strip_tags($new_instance['category']);
		$instance['month']    = strip_tags($new_instance['month']);
		$instance['year']     = strip_tags($new_instance['year']);

		return $instance;
	}

	/**
	 * @see WP_Widget::form
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$title    = isset($instance['title']) ? $instance['title'] : '';
		$size     = isset($instance['size']) ? $instance['size'] : '';
		$category = isset($instance['category']) ? $instance['category'] : '';
		$month    = isset($instance['month']) ? $instance['month'] : '';
		$year     = isset($instance['year']) ? $instance['year'] : ''; ?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sugar-calendar'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Size:', 'sugar-calendar'); ?></label>
			<select class="widefat <?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" id="<?php echo $this->get_field_id('size'); ?>">
				<option value="small" <?php selected('small', $size); ?>><?php _e('Small', 'sugar-calendar'); ?></option>
				<option value="large" <?php selected('large', $size); ?>><?php _e('Large', 'sugar-calendar'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Calendar:', 'sugar-calendar'); ?></label>
			<select class="widefat <?php echo $this->get_field_name('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" id="<?php echo $this->get_field_id('category'); ?>">
				<option value="0" <?php selected(0, $category); ?>><?php _e('All', 'sugar-calendar'); ?></option><?php
					$terms = get_terms('sc_event_category');
					if ($terms) {
						foreach ($terms as $term) {
							echo '<option value="' . $term->slug . '" ' . selected($term->slug, $category, false) . '>' . esc_html( $term->name ) . '</option>';
						}
					}
			?></select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('month'); ?>"><?php _e('Month & Year:', 'sugar-calendar'); ?></label><br>
			<input class="small" id="<?php echo $this->get_field_id('month'); ?>" name="<?php echo $this->get_field_name('month'); ?>" type="number" min="1" max="12" pattern="\d{1,2}" autocomplete="off" value="<?php echo esc_attr( $month ); ?>">
			<input class="small" id="<?php echo $this->get_field_id('year'); ?>" name="<?php echo $this->get_field_name('year'); ?>" type="number" min="1900" max="9999" pattern="\d{1,4}" autocomplete="off" value="<?php echo esc_attr( $year ); ?>">
			<br><span><?php esc_html_e( 'Leave empty for current', 'sugar-calendar' ); ?></span>
		</p>
	<?php
	}
}

/**
 * Event List Widget
 *
 * Shows a list of events.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
class sc_events_list_widget extends WP_Widget
{

	/** constructor */
	public function __construct() {
		parent::__construct('sc_event_list_widget', __('(Sugar Calendar) Event List', 'sugar-calendar'), array('description' => __('Displays all/upcoming/past events as a list.', 'sugar-calendar')));
	}

	/** @see WP_Widget::widget */
	public function widget( $args, $instance ) {
		$before_widget   = isset($args['before_widget']) ? $args['before_widget'] : '';
		$before_title    = isset($args['before_title']) ? $args['before_title'] : '';
		$after_title     = isset($args['after_title']) ? $args['after_title'] : '';
		$after_widget    = isset($args['after_widget']) ? $args['after_widget'] : '';
		$title           = isset($instance['title']) ? $instance['title'] : '';
		$title           = apply_filters('widget_title', $title );
		$display         = isset($instance['display']) ? $instance['display'] : 'all';
		$order           = isset($instance['order']) ? $instance['order'] : '';
		$category        = isset($instance['category']) ? $instance['category'] : null;
		$number          = isset($instance['number']) ? $instance['number'] : null;
		$show_title      = isset($instance['show_title']) ? $instance['show_title'] : null;
		$show_date       = isset($instance['show_date']) ? $instance['show_date'] : null;
		$show_time       = isset($instance['show_time']) ? $instance['show_time'] : null;
		$show_categories = isset($instance['show_categories']) ? $instance['show_categories'] : null;


		echo $before_widget;
		if ($title && $show_title) {
				echo $before_title . $title . $after_title;
		}
		do_action('sc_before_event_list_widget');
		echo '<div id="sc_list_wrap">';
		echo sc_get_events_list($display, $category, $number, array( 'date' => $show_date, 'time' => $show_time, 'categories' => $show_categories ), $order );
		echo '</div>';
		do_action('sc_after_event_list_widget');
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['title']           = strip_tags($new_instance['title']);
		$instance['display']         = strip_tags($new_instance['display']);
		$instance['order']           = strip_tags($new_instance['order']);
		$instance['category']        = strip_tags($new_instance['category']);
		$instance['number']          = strip_tags(intval($new_instance['number']));
		$instance['show_title']      = isset($new_instance['show_title']) ? 1 : null ;
		$instance['show_date']       = isset($new_instance['show_date']) ? 1 : null ;
		$instance['show_time']       = isset($new_instance['show_time']) ? 1 : null ;
		$instance['show_categories'] = isset($new_instance['show_categories']) ? 1 : null ;

		return $instance;
	}

	/**
	 * @see WP_Widget::form
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$title           = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$display         = isset($instance['display']) ? esc_attr($instance['display']) : '';
		$display_order   = ( 'past' === $display ) ? 'DESC' : 'ASC';
		$category        = isset($instance['category']) ? esc_attr($instance['category']) : '';
		$order           = isset($instance['order']) ? esc_attr($instance['order']) : $display_order;
		$number          = isset($instance['number']) ? esc_attr($instance['number']) : 5;
		$show_title      = isset($instance['show_title']) ? esc_attr($instance['show_title']) : null;
		$show_date       = isset($instance['show_date']) ? esc_attr($instance['show_date']) : null;
		$show_time       = isset($instance['show_time']) ? esc_attr($instance['show_time']) : null;
		$show_categories = isset($instance['show_categories']) ? esc_attr($instance['show_categories']) : null;
?>
		<p>
			<label for="<?php
				echo $this->get_field_id('title'); ?>"><?php
				_e('Title:', 'sugar-calendar'); ?></label>
					<input class="widefat" id="<?php
				echo $this->get_field_id('title'); ?>"
							 name="<?php
				echo $this->get_field_name('title'); ?>" type="text" value="<?php
				echo $title; ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('display'); ?>"><?php _e('Time Period:', 'sugar-calendar'); ?></label>
			<select class="widefat <?php
				echo $this->get_field_name('display'); ?>" name="<?php
				echo $this->get_field_name('display'); ?>" id="<?php
				echo $this->get_field_id('display'); ?>">
				<option value="all" <?php selected('all', $display); ?>><?php _e('All', 'sugar-calendar'); ?></option>
				<option value="upcoming" <?php
				selected('upcoming', $display); ?>><?php
				_e('Upcoming', 'sugar-calendar'); ?></option>
				<option value="past" <?php
				selected('past', $display); ?>><?php
				_e('Past', 'sugar-calendar'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order:', 'sugar-calendar'); ?></label>
			<select class="widefat <?php
				echo $this->get_field_name('order'); ?>" name="<?php
				echo $this->get_field_name('order'); ?>" id="<?php
				echo $this->get_field_id('order'); ?>">
				<option value="" <?php selected('', $order); ?>><?php _e('Default for Period', 'sugar-calendar'); ?></option>
				<option value="asc" <?php selected('asc', $order); ?>><?php _e('Oldest First', 'sugar-calendar'); ?></option>
				<option value="desc" <?php selected('desc', $order); ?>><?php _e('Newest First', 'sugar-calendar'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Calendar:', 'sugar-calendar'); ?></label>
			<select class="widefat <?php echo $this->get_field_name('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" id="<?php echo $this->get_field_id('category'); ?>">
				<option value="0" <?php selected(0, $category); ?>><?php _e('All Calendars', 'sugar-calendar'); ?></option>
				<?php
				$terms = get_terms('sc_event_category');
				if (count( $terms) ) {
					foreach ($terms as $term) {
						echo '<option value="' . $term->slug . '" ' . selected($term->slug, $category, false) . '>' . $term->name . '</option>';
					}
				}
			?>
			</select>
		</p>
		<p>
			<input class="widefat" type="text" id="<?php
			echo $this->get_field_id('number'); ?>" style="width: 40px;" name="<?php
			echo $this->get_field_name('number'); ?>" value="<?php
			echo $number; ?>">
				<label for="<?php
			echo $this->get_field_id('number'); ?>"><?php
			_e('Number to show', 'sugar-calendar'); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php
			echo $this->get_field_id('show_title'); ?>" name="<?php
			echo $this->get_field_name('show_title'); ?>" <?php
			echo checked( $show_title, 1 ); ?>">
			<label for="<?php
			echo $this->get_field_id('show_title'); ?>"><?php
				_e('Show widget title', 'sugar-calendar'); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php
			echo $this->get_field_id('show_date'); ?>" name="<?php
			echo $this->get_field_name('show_date'); ?>" <?php
			echo checked( $show_date, 1 ); ?>>
			<label for="<?php
			echo $this->get_field_id('show_date'); ?>"><?php
				_ex('Show event dates', 'Start & end if available', 'sugar-calendar'); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php
			echo $this->get_field_id('show_time'); ?>" name="<?php
			echo $this->get_field_name('show_time'); ?>" <?php
			echo checked( $show_time, 1 ); ?>>
			<label for="<?php
			echo $this->get_field_id('show_time'); ?>"><?php
				_ex('Show event times', 'Start & end if available', 'sugar-calendar'); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php
			echo $this->get_field_id('show_categories'); ?>" name="<?php
			echo $this->get_field_name('show_categories'); ?>" <?php
			echo checked( $show_categories, 1 ); ?>>
			<label for="<?php
			echo $this->get_field_id('show_categories'); ?>"><?php
				_e('Show event categories', 'sugar-calendar'); ?></label>
		</p>
		<?php
	}
}

/**
 * Categories  Widget
 *
 * Event categories widget class.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */

class sc_event_categories_widget extends WP_Widget {

	/** constructor */
	public function __construct() {
		parent::__construct( 'sc_category_widget', __('(Sugar Calendar) Calendar List', 'sugar-calendar'), array('description' => __('Display all of the available calendars as a list.', 'sugar-calendar')));
	}

	/**
	 * @see WP_Widget::widget
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$before_widget = isset($args['before_widget']) ? $args['before_widget'] : '';
		$before_title  = isset($args['before_title']) ? $args['before_title'] : '';
		$after_title   = isset($args['after_title']) ? $args['after_title'] : '';
		$after_widget  = isset($args['after_widget']) ? $args['after_widget'] : '';
		$title         = isset($instance['title']) ? $instance['title'] : '';
		$title         = apply_filters('widget_title', $title );

		echo $before_widget;
		if ($title) {
			echo $before_title . $title . $after_title;
		}

		do_action('sc_before_category_widget');
		$terms = get_terms('sc_event_category');

		if ( is_wp_error( $terms ) ) {
			return;
		} else {
			echo "<ul class=\"sc-category-widget\">\n";
			foreach ($terms as $term) {
				echo '<li><a href="' . get_term_link($term) . '" title="' . esc_attr($term->name) . '" rel="bookmark">' . $term->name . '</a></li>' . "\n";
			}
			echo "</ul>\n";
		}

		do_action('sc_after_category_widget');
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	/** @see WP_Widget::form */
	public function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sugar-calendar'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>">
		</p>
<?php
	}
}

/**
 * Event Filter  Widget
 *
 * Filter Upcoming / Past Events
 *
 * @access      private
 * @since       1.0
 * @return      void
 */

class sc_event_filter_widget extends WP_Widget {

	/** constructor */
	public function __construct() {
		parent::__construct( 'sc_filter_widget', __('(Sugar Calendar) Event Filters', 'sugar-calendar'), array('description' => __('Event Archive only. Controls for filtering how to list events.', 'sugar-calendar')));
	}

	/**
	 * @see WP_Widget::widget
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$before_widget = isset($args['before_widget']) ? $args['before_widget'] : '';
		$before_title  = isset($args['before_title']) ? $args['before_title'] : '';
		$after_title   = isset($args['after_title']) ? $args['after_title'] : '';
		$after_widget  = isset($args['after_widget']) ? $args['after_widget'] : '';
		$title         = isset($instance['title']) ? $instance['title'] : '';
		$title         = apply_filters('widget_title', $title );

		echo $before_widget;
		if ($title) {
				echo $before_title . $title . $after_title;
		}

		do_action('sc_before_filter_widget');

		echo '<ul class="sc_events_filter">';
		echo '<li class="sc_event_filter"><a href="' . remove_query_arg('event-display') . '" ' . __('View all events', 'sugar-calendar') . '>' . __('View all events', 'sugar-calendar') . '</a></li>';
		echo '<li class="sc_event_filter"><a href="' . add_query_arg('event-display', 'upcoming') . '" ' . __('View upcoming events', 'sugar-calendar') . '>' . __('View upcoming events', 'sugar-calendar') . '</a></li>';
		echo '<li class="sc_event_filter"><a href="' . add_query_arg('event-display', 'past') . '" ' . __('View past events', 'sugar-calendar') . '>' . __('View past events', 'sugar-calendar') . '</a></li>';
		echo '<li class="sc_event_filter sc_event_order">';
		echo '<span class="sc_event_order_label">' . __('Order:', 'sugar-calendar') . '</span>&nbsp;';
		echo '<a href="' . add_query_arg('event-order', 'asc') . '">' . __('ASC', 'sugar-calendar') . '</a>';
		echo '<span class="sc_event_order_sep"> - </span>';
		echo '<a href="' . add_query_arg('event-order', 'desc') . '">' . __('DESC', 'sugar-calendar') . '</a>';
		echo '</li>';
		echo '</ul>';

		do_action('sc_after_filter_widget');
		echo $after_widget;
	}

	/**
	 * @see WP_Widget::update
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	/**
	 * @see WP_Widget::form
	 * @param array $instance
	 */
	public function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sugar-calendar'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>">
		</p>
	<?php
	}
}
