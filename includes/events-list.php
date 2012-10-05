<?php

function sc_get_events_list($display = 'all', $category = null, $number = 5) {
		
	$event_args = array(
		'post_type' => 'sc_event',
		'posts_per_page' => $number,
		'meta_key' => 'sc_event_date_time',
		'orderby' => 'meta_value_num',
		'order' => 'asc',
		'post_status' => 'publish'
	);
	
	if($display == 'past') {
		$event_args['meta_compare'] = '<';
		$event_args['order'] = 'desc';
	} else if($display == 'upcoming') {
		$event_args['meta_compare'] = '>=';
	}

	if($display != 'all')
		$event_args['meta_value'] = time();
		
	if( !is_null($category) )
		$event_args['sc_event_category'] = $category;
		
	$events = get_posts( apply_filters('sc_event_list_query', $event_args) );
	
	ob_start();
	
		if( $events ) {
			echo '<ul class="sc_events_list">';
			foreach( $events as $event ) {
				echo '<li class="' . str_replace('hentry', '', implode(' ', get_post_class('sc_event', $event->ID) ) ) . '">';
					do_action( 'sc_before_event_list_item', $event->ID );
					echo '<a href="' . get_permalink($event->ID) . '" class="sc_event_link">';
						echo '<span class="sc_event_title">' . get_the_title($event->ID) . '</span>';
					echo '</a>';
					do_action( 'sc_after_event_list_item', $event->ID );
				echo '</li>';
			}
			echo '</ul>';
		}
	return ob_get_clean();
}