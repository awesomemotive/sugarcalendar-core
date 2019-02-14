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
	
			echo '<td class="sc_meta_box_td" colspan="2"><label for="sc_event_date">' . __('Event Start', 'pippin_sc') . '</label></td>';
			
			echo '<td class="sc_meta_box_td" colspan="2">';
				$date = (isset($meta['sc_event_date'][0]) && ('' != $meta['sc_event_date'][0]) )? date('m/d/Y', $meta['sc_event_date'][0]) : '';
				$recurring_display = isset($meta['sc_event_recurring'][0]) && $meta['sc_event_recurring'][0] != 'none' ? '' : ' style="display:none"'; 
				echo '<input type="text" class="sc_datepicker" name="sc_event_date" id="sc_event_date" value="' . $date . '" placeholder="mm/dd/yyyy"/>';
				echo '<br/><span class="sc_recurring_help description"' . $recurring_display . '>&nbsp;' . __('This is the first day that this event occurs', 'pippin_sc') . '</span>';
			echo '</td>';

			$hour = isset($meta['sc_event_time_hour'][0]) ? $meta['sc_event_time_hour'][0] : '';
			if($hour > 12) $hour = $hour - 12;
			$minute = isset($meta['sc_event_time_minute'][0]) ? $meta['sc_event_time_minute'][0] : '';
			$am_pm = isset($meta['sc_event_time_am_pm'][0]) ? $meta['sc_event_time_am_pm'][0] : null;

			echo '<td class="sc_meta_box_td" colspan="2">';
				echo '<input type="text" class="small-text" name="sc_event_time_hour" value="' . $hour . '" placeholder="00"/>';
				echo '<span class="sc_event_time_separator">&nbsp;:&nbsp;</span>';
				echo '<input type="text" class="small-text" name="sc_event_time_minute" value="' . $minute . '" placeholder="00"/>';
				echo '<select name="sc_event_time_am_pm">';
					echo '<option value="am" ' . selected($am_pm, 'am', false) . '>' . __('AM', 'pippin_sc') . '</option>';
					echo '<option value="pm" ' . selected($am_pm, 'pm', false) . '>' . __('PM', 'pippin_sc') . '</option>';
				echo '</select>';
			echo '</td>';

		echo '</tr>';


		echo '<tr class="sc_meta_box_row">';
	
			echo '<td class="sc_meta_box_td" colspan="2"><label for="sc_event_time">' . __('Event End', 'pippin_sc') . '</label></td>';

			echo '<td class="sc_meta_box_td" colspan="2">';
				$end_date = isset($meta['sc_event_end_date'][0]) ? date('m/d/Y', $meta['sc_event_end_date'][0]) : '';

				echo '<input type="text" class="sc_datepicker" name="sc_event_end_date" id="sc_event_end_date" value="' . $end_date . '" placeholder="mm/dd/yyyy" />';
			echo '</td>';


			$end_hour = isset($meta['sc_event_end_time_hour'][0]) ? $meta['sc_event_end_time_hour'][0] : '';
			if($end_hour > 12) $end_hour = $end_hour - 12;
			$end_minute = isset($meta['sc_event_end_time_minute'][0]) ? $meta['sc_event_end_time_minute'][0] : '';
			$end_am_pm = isset($meta['sc_event_end_time_am_pm'][0]) ? $meta['sc_event_end_time_am_pm'][0] : null;
			
			echo '<td class="sc_meta_box_td" colspan="2">';
				echo '<input type="text" class="small-text" name="sc_event_end_time_hour" value="' . $end_hour . '" placeholder="00"/>';
				echo '<span class="sc_event_time_separator">&nbsp;:&nbsp;</span>';
				echo '<input type="text" class="small-text" name="sc_event_end_time_minute" value="' . $end_minute . '" placeholder="00"/>';
				echo '<select class="sc_event_end_time_am_pm" name="sc_event_end_time_am_pm">';
					echo '<option value="am" ' . selected($end_am_pm, 'am', false) . '>' . __('AM', 'pippin_sc') . '</option>';
					echo '<option value="pm" ' . selected($end_am_pm, 'pm', false) . '>' . __('PM', 'pippin_sc') . '</option>';
				echo '</select>';
			echo '</td>';
			
		echo '</tr>';
		
		do_action('sc_event_meta_box_after');
	
	echo '</table>';

}


/**
 * Event Meta Box Save
 *
 * Save data from meta box.
 *
 * @access      private
 * @since       1.0
 *
 * @return      int|void
*/

function sc_meta_box_save($post_id) {
	global $post;
	
	// verify nonce
	if ( ! isset($_POST['sc_meta_box_nonce'] ) || ! wp_verify_nonce($_POST['sc_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave
	if ( ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined('DOING_AJAX') && DOING_AJAX) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	//don't save if only a revision
	if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
		return $post_id;
	}

	// check permissions
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	
	// retrieve and store event start date / time
	if( isset($_POST['sc_event_date']) && ( '' != $_POST['sc_event_date']) ) {

		$date    = isset( $_POST['sc_event_date'] ) ? sanitize_text_field( $_POST['sc_event_date'] ) : '';
		$day   = date( 'd', strtotime( $date ) );
		$month = date( 'm', strtotime( $date ) );
		$year  = date( 'Y', strtotime( $date ) );

		update_post_meta( $post_id, 'sc_event_date', strtotime( $date ) );
		update_post_meta( $post_id, 'sc_event_day', date( 'D', strtotime( $date ) ) );
		update_post_meta( $post_id, 'sc_event_day_of_week', date( 'w', strtotime( $date ) ) );
		update_post_meta( $post_id, 'sc_event_day_of_month', $day );
		update_post_meta( $post_id, 'sc_event_day_of_year', date( 'z', strtotime( $date ) ) );
		update_post_meta( $post_id, 'sc_event_month', $month );
		update_post_meta( $post_id, 'sc_event_year', $year );

		if ( isset( $_POST['sc_event_time_hour'] ) && isset( $_POST['sc_event_time_minute'] ) && isset( $_POST['sc_event_time_am_pm'] ) ){

			$hour    = isset( $_POST['sc_event_time_hour'] ) ? sanitize_text_field( $_POST['sc_event_time_hour'] ) : '00';
			$minutes = isset( $_POST['sc_event_time_minute'] ) ? sanitize_text_field( $_POST['sc_event_time_minute'] ) : '00';
			$am_pm   = isset( $_POST['sc_event_time_am_pm'] ) ? sanitize_text_field( $_POST['sc_event_time_am_pm'] ) : '';

			if ( $am_pm == 'pm' && $hour < 12 ) {
				$hour += 12;
			} elseif ( $am_pm == 'am' && $hour >= 12 ) {
				$hour -= 12;
			}
			update_post_meta( $post_id, 'sc_event_time_hour', $hour );
			update_post_meta( $post_id, 'sc_event_time_minute', $minutes );
			update_post_meta( $post_id, 'sc_event_time_am_pm', $am_pm );

			$final_date_time = mktime( intval( $hour ), intval( $minutes ), 0, $month, $day, $year );
			update_post_meta( $post_id, 'sc_event_date_time', $final_date_time );
		} else {
			update_post_meta( $post_id, 'sc_event_date_time', strtotime( $date ) );
		}


	}

	if( isset($_POST['sc_event_end_date']) && ( '' == $_POST['sc_event_end_date']) ) {
		$sc_event_end_date = sanitize_text_field( $_POST['sc_event_date'] );
	} else {
		$sc_event_end_date = sanitize_text_field( $_POST['sc_event_end_date'] );
	}

	// retrieve and store event start date / time
	if( '' != $sc_event_end_date ) {

		$end_date  = $sc_event_end_date;
		$end_day   = date( 'd', strtotime( $end_date ) );
		$end_month = date( 'm', strtotime( $end_date ) );
		$end_year  = date( 'Y', strtotime( $end_date ) );

		update_post_meta( $post_id, 'sc_event_end_date', strtotime( $end_date ) );
		update_post_meta( $post_id, 'sc_event_end_day', date( 'D', strtotime( $end_date ) ) );
		update_post_meta( $post_id, 'sc_event_end_day_of_week', date( 'w', strtotime( $end_date ) ) );
		update_post_meta( $post_id, 'sc_event_end_day_of_month', $end_day );
		update_post_meta( $post_id, 'sc_event_end_day_of_year', date( 'z', strtotime( $end_date ) ) );
		update_post_meta( $post_id, 'sc_event_end_month', $end_month );
		update_post_meta( $post_id, 'sc_event_end_year', $end_year );

		if ( isset( $_POST['sc_event_time_hour'] ) && isset( $_POST['sc_event_time_minute'] ) && isset( $_POST['sc_event_time_am_pm'] ) ) {


			$end_hour    = isset( $_POST['sc_event_end_time_hour'] ) ? sanitize_text_field( $_POST['sc_event_end_time_hour'] ) : 0;
			$end_minutes = isset( $_POST['sc_event_end_time_minute'] ) ? sanitize_text_field( $_POST['sc_event_end_time_minute'] ) : 0;
			$end_am_pm   = isset( $_POST['sc_event_end_time_am_pm'] ) ? sanitize_text_field( $_POST['sc_event_end_time_am_pm'] ) : '';


			if ( $end_am_pm == 'pm' && $end_hour < 12 ) {
				$end_hour += 12;
			} elseif ( $end_am_pm == 'am' && $end_hour >= 12 ) {
				$end_hour -= 12;
			}

			update_post_meta( $post_id, 'sc_event_end_time_hour', $end_hour );
			update_post_meta( $post_id, 'sc_event_end_time_minute', $end_minutes );
			update_post_meta( $post_id, 'sc_event_end_time_am_pm', $end_am_pm );

			$final_end_date_time = mktime( intval( $end_hour ), intval( $end_minutes ), 0, $end_month, $end_day, $end_year );
			update_post_meta( $post_id, 'sc_event_end_date_time', $final_end_date_time );
		} else {
			update_post_meta( $post_id, 'sc_event_end_date_time', $end_date );
		}



	}

	$recurring   = isset( $_POST['sc_event_recurring'] ) ? sanitize_key( $_POST['sc_event_recurring'] ) : '';
	$recur_until = isset( $_POST['sc_recur_until'] ) ? sanitize_text_field( $_POST['sc_recur_until'] ) : '';
	update_post_meta($post_id, 'sc_event_recurring', $recurring);
	update_post_meta($post_id, 'sc_recur_until', strtotime( $recur_until ) );

	$recurrences = sc_calculate_recurring( $post_id );
	update_post_meta($post_id, 'sc_all_recurring', $recurrences);

	do_action('sc_event_meta_box_save', $post_id );
		
}
add_action('save_post', 'sc_meta_box_save');