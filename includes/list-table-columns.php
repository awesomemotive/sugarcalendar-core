<?php

/**
 * Event Columns
 *
 * Defines the custom columns and their order.
 *
 * @access      private
 * @since       1.0.0
 * @return      array
 */
function sc_event_columns( $event_columns ) {
	$event_columns = array(
		'cb'         => '<input type="checkbox"/>',
		'title'      => __( 'Title', 'pippin_sc' ),
		'event_date' => __( 'Event Date', 'pippin_sc' ),
		'event_time' => __( 'Time', 'pippin_sc' ),
		'category'   => __( 'Categories', 'pippin_sc' ),
		'date'       => __( 'Created', 'pippin_sc' )
	);
	return $event_columns;
}
add_filter( 'manage_edit-sc_event_columns', 'sc_event_columns' );


/**
 * Render Event Columns
 *
 * Render the custom columns content.
 *
 * @access private
 * @since 1.0.0
 * @param string $column_name
 * @param int $post_id
 * @return void
 */
function sc_render_event_columns( $column_name, $post_id ) {
	if ( get_post_type( $post_id ) == 'sc_event' ) {

		$date = get_post_meta( $post_id, 'sc_event_date_time', true );

		switch ( $column_name ) {
		case 'event_date':
			if ( $date )
				echo date_i18n( get_option( 'date_format' ), $date );
			break;
		case 'event_time':
			if ( $date )
				echo date_i18n( get_option( 'time_format' ), $date );
			break;
		case 'category':
			echo get_the_term_list( $post_id, 'sc_event_category', '', ', ', '' );
			break;
		}
	}
}
add_action( 'manage_posts_custom_column', 'sc_render_event_columns', 10, 2 );


/**
 * Sortable Event Columns
 *
 * Set the sortable columns content.
 *
 * @access      private
 * @since       1.0.0
 * @param       array $columns
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
 * @since       1.0.0
 * @param       array $vars
 * @return      array
 */
function sc_sort_events( $vars ) {
	// check if we're viewing the "sc_event" post type
	if ( isset( $vars['post_type'] ) && 'sc_event' == $vars['post_type'] ) {

		// check if 'orderby' is set to "event_date"
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


/**
 * Add Event Category Filters
 *
 * Add event category filters for Events.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function sc_add_event_filters() {
	global $typenow;

	// the current post type
	if ( $typenow == 'sc_event' ) {

		$terms = get_terms( 'sc_event_category' );
		if ( count( $terms ) > 0 ) {
			echo "<select name='sc_event_category' id='sc_event_category' class='postform'>";
			echo "<option value=''>" . __( 'Show all categories', 'pippin_sc' ) . "</option>";
			foreach ( $terms as $term ) {
				$selected = isset( $_GET['sc_event_category'] ) && $_GET['sc_event_category'] == $term->slug ? ' selected="selected"' : '';
				echo '<option value="' . $term->slug . '"' . $selected . '>' . $term->name .' (' . $term->count .')</option>';
			}
			echo "</select>";
		}

	}

}
add_action( 'restrict_manage_posts', 'sc_add_event_filters', 100 );
