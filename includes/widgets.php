<?php
add_filter('widget_text', 'do_shortcode');

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
		parent::__construct( 'sc_calendar_widget', __('Event Calendar', 'pippin_sc'), array('description' => __('Displays an events widget', 'pippin_sc')));
	}

	/**
	 *
	 * @see WP_Widget::widget
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		$before_widget = isset($args['before_widget']) ? $args['before_widget'] : '';
		$before_title  = isset($args['before_title']) ? $args['before_title'] : '';
		$after_title   = isset($args['after_title']) ? $args['after_title'] : '';
		$after_widget  = isset($args['after_widget']) ? $args['after_widget'] : '';
		$title         = isset($instance['title']) ? $instance['title'] : '';
		$title         = apply_filters('widget_title', $title );
		$size          = isset($instance['size']) ? $instance['size'] : 'small';
		$category      = isset($instance['category']) ? $instance['category'] : null;

		sc_enqueue_styles();
		sc_enqueue_scripts();

		echo $before_widget;
		if ($title) {
			echo $before_title . $title . $after_title;
		}
		do_action('sc_before_calendar_widget');
		echo '<div id="sc_calendar_wrap_widget">';
		echo sc_get_events_calendar($size, $category);
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
	function update($new_instance, $old_instance) {
		$instance             = $old_instance;
		$instance['title']    = strip_tags($new_instance['title']);
		$instance['size']     = strip_tags($new_instance['size']);
		$instance['category'] = strip_tags($new_instance['category']);

		return $instance;
	}

	/**
	 * @see WP_Widget::form
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$size = isset($instance['size']) ? esc_attr($instance['size']) : '';
		$category = isset($instance['category']) ? esc_attr($instance['category']) : '';
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'pippin_sc'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
		<p>
			<select class="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" id="<?php echo $this->get_field_id('size'); ?>">
				<option value="small" <?php selected('small', $size); ?>><?php _e('Small', 'pippin_sc'); ?></option>
				<option value="large" <?php selected('large', $size); ?>><?php _e('Large', 'pippin_sc'); ?></option>
			</select>
			<label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Calendar Size', 'pippin_sc'); ?></label>
		</p>
	<p>
		<select class="<?php echo $this->get_field_name('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" id="<?php echo $this->get_field_id('category'); ?>">
			<option value="0" <?php selected(0, $category); ?>><?php _e('All', 'pippin_sc'); ?></option>
<?php
		$terms = get_terms('sc_event_category');
		if ($terms) {
			foreach ($terms as $term) {
				echo '<option value="' . $term->slug . '" ' . selected($term->slug, $category, false) . '>' . $term->name . '</option>';
			}
		}
?>
		</select>
		<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Event Category', 'pippin_sc'); ?></label>
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
		parent::__construct('sc_event_list_widget', __('Events List', 'pippin_sc'), array('description' => __('Displays upcoming/past events in a list view', 'pippin_sc')));
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		$before_widget   = isset($args['before_widget']) ? $args['before_widget'] : '';
		$before_title    = isset($args['before_title']) ? $args['before_title'] : '';
		$after_title     = isset($args['after_title']) ? $args['after_title'] : '';
		$after_widget    = isset($args['after_widget']) ? $args['after_widget'] : '';
		$title           = isset($instance['title']) ? $instance['title'] : '';
		$title           = apply_filters('widget_title', $title );
		$display         = isset($instance['display']) ? $instance['display'] : 'all';
		$category        = isset($instance['category']) ? $instance['category'] : null;
		$number          = isset($instance['number']) ? $instance['number'] : null;
		$show_title      = isset($instance['show_title']) ? $instance['show_title'] : null;
		$show_date       = isset($instance['show_date']) ? $instance['show_date'] : null;
		$show_time       = isset($instance['show_time']) ? $instance['show_time'] : null;
		$show_categories = isset($instance['show_categories']) ? $instance['show_categories'] : null;


		sc_enqueue_styles();
		sc_enqueue_scripts();

		echo $before_widget;
		if ($title && $show_title) {
				echo $before_title . $title . $after_title;
		}
		do_action('sc_before_event_list_widget');
		echo '<div id="sc_list_wrap">';
		echo sc_get_events_list($display, $category, $number, array( 'date' => $show_date, 'time' => $show_time, 'categories' => $show_categories ) );
		echo '</div>';
		do_action('sc_after_event_list_widget');
		echo $after_widget;
	}
		
	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['title']           = strip_tags($new_instance['title']);
		$instance['display']         = strip_tags($new_instance['display']);
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
	function form( $instance ) {
		$title           = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$display         = isset($instance['display']) ? esc_attr($instance['display']) : '';
		$category        = isset($instance['category']) ? esc_attr($instance['category']) : '';
		$number          = isset($instance['number']) ? esc_attr($instance['number']) : 5;
		$show_title      = isset($instance['show_title']) ? esc_attr($instance['show_title']) : null;
		$show_date       = isset($instance['show_date']) ? esc_attr($instance['show_date']) : null;
		$show_time       = isset($instance['show_time']) ? esc_attr($instance['show_time']) : null;
		$show_categories = isset($instance['show_categories']) ? esc_attr($instance['show_categories']) : null;
?>
		<p>
			<label for="<?php
				echo $this->get_field_id('title'); ?>"><?php
				_e('Title:', 'pippin_sc'); ?></label>
					<input class="widefat" id="<?php
				echo $this->get_field_id('title'); ?>"
							 name="<?php
				echo $this->get_field_name('title'); ?>" type="text" value="<?php
				echo $title; ?>"/>
		</p>
		<p>
			<select class="<?php
				echo $this->get_field_name('display'); ?>" name="<?php
				echo $this->get_field_name('display'); ?>" id="<?php
				echo $this->get_field_id('display'); ?>">
						<option value="all" <?php selected('all', $display); ?>><?php _e('All', 'pippin_sc'); ?></option>
				<option value="upcoming" <?php
				selected('upcoming', $display); ?>><?php
				_e('Upcoming', 'pippin_sc'); ?></option>
				<option value="past" <?php
				selected('past', $display); ?>><?php
				_e('Past', 'pippin_sc'); ?></option>
			</select>
			<label for="<?php echo $this->get_field_id('display'); ?>"><?php _e('All, Past, or Upcoming', 'pippin_sc'); ?></label>
		</p>
		<p>
			<select class="<?php echo $this->get_field_name('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" id="<?php echo $this->get_field_id('category'); ?>">
				<option value="0" <?php selected(0, $category); ?>><?php _e('All', 'pippin_sc'); ?></option>
				<?php
				$terms = get_terms('sc_event_category');
				if ($terms) {
					foreach ($terms as $term) {
						echo '<option value="' . $term->slug . '" ' . selected($term->slug, $category, false) . '>' . $term->name . '</option>';
					}
				}
			?>
			</select>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Event Category', 'pippin_sc'); ?></label>
		</p>
		<p>
			<input class="widefat" id="<?php
			echo $this->get_field_id('number'); ?>" style="width: 40px;"
						 name="<?php
			echo $this->get_field_name('number'); ?>" type="text" value="<?php
			echo $number; ?>"/>
				<label for="<?php
			echo $this->get_field_id('number'); ?>"><?php
			_e('Number to show', 'pippin_sc'); ?></label>
		</p>
		<p>
			<input class="checkbox" id="<?php
			echo $this->get_field_id('show_title'); ?>" name="<?php
			echo $this->get_field_name('show_title'); ?>" type="checkbox" <?php
			echo checked( $show_title, 1 ); ?>"/>
			<label for="<?php
			echo $this->get_field_id('show_title'); ?>"><?php
				_e('Show widget title', 'pippin_sc'); ?></label>
		</p>
		<p>
			<input class="checkbox" id="<?php
			echo $this->get_field_id('show_date'); ?>" name="<?php
			echo $this->get_field_name('show_date'); ?>" type="checkbox" <?php
			echo checked( $show_date, 1 ); ?>"/>
			<label for="<?php
			echo $this->get_field_id('show_date'); ?>"><?php
				_e('Show date', 'pippin_sc'); ?></label>
		</p>
		<p>
			<input class="checkbox" id="<?php
			echo $this->get_field_id('show_time'); ?>" name="<?php
			echo $this->get_field_name('show_time'); ?>" type="checkbox" <?php
			echo checked( $show_time, 1 ); ?>"/>
			<label for="<?php
			echo $this->get_field_id('show_time'); ?>"><?php
				_e('Show event time', 'pippin_sc'); ?></label>
		</p>
		<p>
			<input class="checkbox" id="<?php
			echo $this->get_field_id('show_categories'); ?>" name="<?php
			echo $this->get_field_name('show_categories'); ?>" type="checkbox" <?php
			echo checked( $show_categories, 1 ); ?>"/>
			<label for="<?php
			echo $this->get_field_id('show_categories'); ?>"><?php
				_e('Show event categories', 'pippin_sc'); ?></label>
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
		parent::__construct( 'sc_category_widget', __('Event Categories', 'pippin_sc'), array('description' => __('Display the event categories', 'pippin_sc')));
	}

	/**
	 * @see WP_Widget::widget
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
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
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'pippin_sc'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
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
		parent::__construct( 'sc_filter_widget', __('Event Filter', 'pippin_sc'), array('description' => __('Filtering controls for upcoming and past events display. Use only on /events archive.', 'pippin_sc')));
	}

	/**
	 * @see WP_Widget::widget
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
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
		echo '<li class="sc_event_filter"><a href="' . remove_query_arg('event-display') . '" ' . __('View all events', 'pippin_sc') . '>' . __('View all events', 'pippin_sc') . '</a></li>';
		echo '<li class="sc_event_filter"><a href="' . add_query_arg('event-display', 'upcoming') . '" ' . __('View upcoming events', 'pippin_sc') . '>' . __('View upcoming events', 'pippin_sc') . '</a></li>';
		echo '<li class="sc_event_filter"><a href="' . add_query_arg('event-display', 'past') . '" ' . __('View past events', 'pippin_sc') . '>' . __('View past events', 'pippin_sc') . '</a></li>';
		echo '<li class="sc_event_filter sc_event_order">';
		echo '<span class="sc_event_order_label">' . __('Order:', 'pippin_sc') . '</span>&nbsp;';
		echo '<a href="' . add_query_arg('event-order', 'asc') . '">' . __('ASC', 'pippin_sc') . '</a>';
		echo '<span class="sc_event_order_sep"> - </span>';
		echo '<a href="' . add_query_arg('event-order', 'desc') . '">' . __('DESC', 'pippin_sc') . '</a>';
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
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	/**
	 * @see WP_Widget::form
	 * @param array $instance
	 */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'pippin_sc'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
	<?php
	}
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
	register_widget('sc_events_widget');
	register_widget('sc_events_list_widget');
	register_widget('sc_event_categories_widget');
	register_widget('sc_event_filter_widget');
}
add_action('widgets_init', 'sc_register_widgets');
