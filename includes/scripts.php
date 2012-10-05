<?php

/**
 * Admin Scripts
 *
 * Loads all of the scripts for admin
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_load_admin_scripts() {
	global $post;
	if( ! isset( $post ) )
		return; // not on an edit page
	
	if( !is_object( $post ) )
		return;

	if( 'sc_event' != $post->post_type )
		return; // not an an event post type

	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('sc-admin', SC_PLUGIN_URL . 'assets/js/sc-admin.js');
	
	
	if ( 'classic' == get_user_option( 'admin_color') )
		wp_enqueue_style('jquery-ui', SC_PLUGIN_URL . 'assets/css/jquery-ui-classic.css');
	else
		wp_enqueue_style('jquery-ui', SC_PLUGIN_URL . 'assets/css/jquery-ui-fresh.css');
	

}
add_action('admin_enqueue_scripts', 'sc_load_admin_scripts');


/**
 * Front End Scripts
 *
 * Loads all of the scripts for front end.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_load_front_end_scripts() {
	
	if( sc_is_calendar_page() || is_singular('sc_event') ) {
		sc_enqueue_scripts();
		sc_enqueue_styles();
	}
}
add_action('wp_enqueue_scripts', 'sc_load_front_end_scripts');

function sc_enqueue_scripts() {
	wp_enqueue_script('sc-ajax', SC_PLUGIN_URL . 'assets/js/sc-ajax.js', array('jquery'), '1.0', false);
	wp_localize_script('sc-ajax', 'sc_vars', array(
			'ajaxurl' => admin_url('admin-ajax.php')
		)
	);
}

function sc_enqueue_styles() {
	wp_enqueue_style('sc-events', SC_PLUGIN_URL . 'assets/css/sc-events.css');
}