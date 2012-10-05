<?php

/**
 * Event Columns
 *
 * Defines the custom columns and their order.
 *
 * @access      private
 * @since       1.0 
 * @return      array
*/

function sc_event_columns($event_columns){
	$event_columns = array(
		'cb' 		=> '<input type="checkbox"/>',
		'title' 	=> __('Title', 'pippin_sc'),
		'event_date'=> __('Event Date', 'pippin_sc'),
		'event_time'=> __('Time', 'pippin_sc'),
		'date' 		=> __('Created', 'pippin_sc')
	);
	return $event_columns;
}
add_filter('manage_edit-sc_event_columns', 'sc_event_columns');


/**
 * Render Event Columns
 *
 * Render the custom columns content.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_render_event_columns($column_name, $post_id) {	
	
	if(get_post_type($post_id) == 'sc_event') {
		
		switch ($column_name) {			
			case 'event_date':
				$date = get_post_meta($post_id, 'sc_event_date_time', true);
				if($date)
					echo date(get_option('date_format'), $date);
				break;
			case 'event_time':
				$hour = get_post_meta($post_id, 'sc_event_time_hour', true);
				$minute = get_post_meta($post_id, 'sc_event_time_minute', true);
				$am_pm = get_post_meta($post_id, 'sc_event_time_am_pm', true);
				echo $hour . ':' . $minute . strtoupper($am_pm);
				break;
		}
	}
}
add_action('manage_posts_custom_column', 'sc_render_event_columns', 10, 2);


/**
 * Sortable Event Columns
 *
 * Set the sortable columns content.
 *
 * @access      private
 * @since       1.0 
 * @return      array
*/

function sc_sortable_columns( $columns ) {

	$columns['event_date'] = 'event_date';

	return $columns;
}
add_filter( 'manage_edit-sc_event_sortable_columns', 'sc_sortable_columns' );


/**
 * Sorts Events
 *
 * Sorts the events.
 *
 * @access      private
 * @since       1.0 
 * @return      array
*/

function sc_sort_events( $vars ) {
	// check if we're viewing the "movie" post type
	if ( isset( $vars['post_type'] ) && 'sc_event' == $vars['post_type'] ) {

		// check if 'orderby' is set to "sales"
		if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'event_date' ) {
			$vars['meta_key'] = 'sc_event_date_time';
			$vars['orderby'] = 'meta_value_num';
		}
	}

	return $vars;
}


/**
 * Event List Load
 *
 * Sorts the events.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_event_list_load() {
	add_filter( 'request', 'sc_sort_events' );
}
add_action( 'load-edit.php', 'sc_event_list_load' );