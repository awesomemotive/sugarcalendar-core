<?php

/**
 * Metabox Functions
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/


/**
 * Add Event Meta Box
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_add_event_meta_box() {
	add_meta_box('sc_event_config', __('Event Details', 'pippin_sc'), 'sc_render_event_config_meta_box', 'sc_event', 'normal', 'default');
}
add_action('add_meta_boxes', 'sc_add_event_meta_box');


/**
 * Renders the Event Configuration Meta Box
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_render_event_config_meta_box() {
	global $post;
	
	$meta = get_post_custom($post->ID);
	
	echo '<input type="hidden" name="sc_meta_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';
	
	echo '<table class="form-table">';
		
		do_action('sc_event_meta_box_before');
		
		echo '<tr class="sc_meta_box_row">';
	
			echo '<td class="sc_meta_box_td" colspan="2"><label for="sc_event_date">' . __('Event Date', 'pippin_sc') . '</label></td>';
			
			echo '<td class="sc_meta_box_td" colspan="4">';
				$date = isset( $meta['sc_event_date'][0] ) ? date( 'm/d/Y', $meta['sc_event_date'][0] ) : '';
				echo '<input type="text" class="sc_datepicker" name="sc_event_date" value="' . esc_attr( $date ) . '" placeholder="mm/dd/yyyy"/>';
			echo '</td>';
			
		echo '</tr>';
		
		echo '<tr class="sc_meta_box_row">';
	
			echo '<td class="sc_meta_box_td" colspan="2"><label for="sc_event_time">' . __('Event Time', 'pippin_sc') . '</label></td>';

			$time 		= isset( $meta['sc_event_time'][0] ) 			? date('m/d/Y', $meta['sc_event_time'][0]) : '';
			$hour 		= isset( $meta['sc_event_time_hour'][0] ) 		? $meta['sc_event_time_hour'][0] : '00';
			if( $hour > 12 ) $hour = $hour - 12;
			$minute 	= isset( $meta['sc_event_time_minute'][0] ) 	? $meta['sc_event_time_minute'][0] : '00';
			$am_pm 		= isset( $meta['sc_event_time_am_pm'][0] ) 		? $meta['sc_event_time_am_pm'][0] : null;
			
			$end_hour 	= isset( $meta['sc_event_end_time_hour'][0] ) 	? $meta['sc_event_end_time_hour'][0] : '00';
			if($end_hour > 12) $end_hour = $end_hour - 12;
			$end_minute = isset( $meta['sc_event_end_time_minute'][0] ) ? $meta['sc_event_end_time_minute'][0] : '00';
			$end_am_pm 	= isset( $meta['sc_event_end_time_am_pm'][0] ) 	? $meta['sc_event_end_time_am_pm'][0] : null;
			
			echo '<td class="sc_meta_box_td" colspan="4">';
				echo '<input type="text" class="small-text" name="sc_event_time_hour" value="' . absint( $hour ) . '"/>';
				echo '<span class="sc_event_time_separator">&nbsp;:&nbsp;</span>';
				echo '<input type="text" class="small-text" name="sc_event_time_minute" value="' . absint( $minute ) . '"/>';
				echo '<select name="sc_event_time_am_pm">';
					echo '<option value="am" ' . selected( $am_pm, 'am', false ) . '>' . __('AM', 'pippin_sc') . '</option>';
					echo '<option value="pm" ' . selected( $am_pm, 'pm', false ) . '>' . __('PM', 'pippin_sc') . '</option>';
				echo '</select>';
				echo '&nbsp;<span class="sc_time_sep">' . __('to:', 'pippin_sc') . '</span>&nbsp;';
				echo '<input type="text" class="small-text" name="sc_event_end_time_hour" value="' . absint( $end_hour ) . '"/>';
				echo '<span class="sc_event_time_separator">&nbsp;:&nbsp;</span>';
				echo '<input type="text" class="small-text" name="sc_event_end_time_minute" value="' . absint( $end_minute ) . '"/>';
				echo '<select name="sc_event_end_time_am_pm">';
					echo '<option value="am" ' . selected($end_am_pm, 'am', false) . '>' . __('AM', 'pippin_sc') . '</option>';
					echo '<option value="pm" ' . selected($end_am_pm, 'pm', false) . '>' . __('PM', 'pippin_sc') . '</option>';
				echo '</select>';
			echo '</td>';
			
		echo '</tr>';
		
		do_action('sc_event_meta_box_after');
	
	echo '</table>';

}


/**
 * Download Meta Box Save
 *
 * Save data from meta box.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_meta_box_save($post_id) {
	global $post;
	
	// verify nonce
	if (!isset($_POST['sc_meta_box_nonce']) || !wp_verify_nonce($_POST['sc_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']) ) return $post_id;
	
	//don't save if only a revision
	if ( isset($post->post_type) && $post->post_type == 'revision' ) return $post_id;

	// check permissions
	if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
	
	// retrieve and store event date / time
	
	if(!isset($_POST['sc_event_date']))
		return $post_id;
		
	$date 			= sanitize_text_field( $_POST['sc_event_date'] );
	$hours 			= sanitize_text_field( absint( $_POST['sc_event_time_hour'] ) );
	$minutes		= sanitize_text_field( absint( $_POST['sc_event_time_minute'] ) );
	$am_pm 			= sanitize_text_field( $_POST['sc_event_time_am_pm']);
	$end_hour 		= sanitize_text_field( $_POST['sc_event_end_time_hour']);
	$end_minutes	= sanitize_text_field( $_POST['sc_event_end_time_minute']);
	$end_am_pm 		= sanitize_text_field( $_POST['sc_event_end_time_am_pm']);
	$recurring		= isset($_POST['sc_event_recurring']) ? $_POST['sc_event_recurring'] : '';
	
	if( $am_pm == 'pm' && $hour < 12 )
		$hour += 12;
	elseif( $am_pm == 'am' && $hour >= 12 )
		$hour -= 12;
	
	$day 	= date( 'd', strtotime( $date ) );
	$month 	= date( 'm', strtotime( $date ) );
	$year 	= date( 'Y', strtotime( $date ) );

	$final_date_time = mktime( $hours, $minutes, 0, $month, $day, $year );
	
	update_post_meta($post_id, 'sc_event_date_time', $final_date_time);
	update_post_meta($post_id, 'sc_event_date', strtotime($date));
	update_post_meta($post_id, 'sc_event_day', date('D', strtotime($date)));
	update_post_meta($post_id, 'sc_event_day_of_month', $day);
	update_post_meta($post_id, 'sc_event_month', $month);
	update_post_meta($post_id, 'sc_event_year', $year);
	update_post_meta($post_id, 'sc_event_time_hour', $hours);
	update_post_meta($post_id, 'sc_event_time_minute', $minutes);
	update_post_meta($post_id, 'sc_event_time_am_pm', $am_pm);
	update_post_meta($post_id, 'sc_event_end_time_hour', $end_hour);
	update_post_meta($post_id, 'sc_event_end_time_minute', $end_minutes);
	update_post_meta($post_id, 'sc_event_end_time_am_pm', $end_am_pm);
	update_post_meta($post_id, 'sc_event_recurring', $recurring);
		
}
add_action('save_post', 'sc_meta_box_save');