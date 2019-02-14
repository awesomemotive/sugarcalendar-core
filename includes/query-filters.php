<?php

/**
 * Update WP_Query to include meta query for sc_event
 *
 * @param WP_Query $query
 *
 * @return mixed
 */
function sc_modify_events_archive( $query ) {

	if( ( is_post_type_archive('sc_event') || is_tax('sc_event_category') ) && $query->is_main_query() && ! is_admin() ) {
		
		if( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == 'nav_menu_item' ) {
			return $query;
		}
		
		$query->set('orderby', 'meta_value_num');
		$query->set('order', 'DESC');
		
		if( isset( $_GET['event-display'] ) ) {

			$mode = urldecode($_GET['event-display']);

			switch( $mode ) {
	
				case 'past':
					$meta_query = array(
						array(
							'key'     => 'sc_event_date_time',
							'value'   => current_time('timestamp'),
							'compare' => '<',
						),
					);
					$query->set('meta_query', $meta_query);
					break;

				case 'upcoming':
					$meta_query = array(
						'relation' => 'OR',
						array(
							'key'     => 'sc_event_end_date_time',
							'value'   => current_time('timestamp'),
							'compare' => '>=',
						),
						array(
							'key'     => 'sc_recur_until',
							'value'   => current_time('timestamp'),
							'compare' => '>=',
						)
					);
					$query->set('meta_query', $meta_query);
					break;
			}
		}

		if( isset( $_GET['event-order'] ) ) {
			$order = urldecode( $_GET['event-order'] );
			$query->set('order', $order);
		}
		
	}
}
add_action('pre_get_posts', 'sc_modify_events_archive', 999);