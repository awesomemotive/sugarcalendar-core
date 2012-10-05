<?php

function sc_modify_events_archive( $query ) {
	if( is_post_type_archive('sc_event') && $query->is_main_query() && !is_admin() ) {
		
		if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'nav_menu_item' )
			return $query;
		
		$query->set('orderby', 'meta_value_num');
		$query->set('meta_key', 'sc_event_date_time');
		$query->set('order', 'DESC');
		
		if(isset($_GET['event-display'])) {
			$mode = urldecode($_GET['event-display']);
			$query->set('meta_value', current_time('timestamp') );
			switch($mode) {
				case 'past':
					$query->set('meta_compare', '<');
					break;
				case 'upcoming':
					$query->set('meta_compare', '>=');
					break;
			}
		}
		
		if(isset($_GET['event-order'])) {
			$order = urldecode($_GET['event-order']);
			$query->set('order', $order);
		}
		
	}
}
add_action('pre_get_posts', 'sc_modify_events_archive', 999);