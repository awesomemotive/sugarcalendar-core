<?php

/**
 * Setup Events Post Type
 *
 * Registers the Events CPT.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_setup_post_types() {
	
	$event_labels =  apply_filters('sc_event_labels', array(
		'name' 				=> __( 'Events', 'pippin_sc' ),
		'singular_name' 	=> __( 'Event', 'pippin_sc' ),
		'add_new' 			=> __( 'Add New', 'pippin_sc' ),
		'add_new_item' 		=> __( 'Add New Event', 'pippin_sc' ),
		'edit_item' 		=> __( 'Edit Event', 'pippin_sc' ),
		'new_item' 			=> __( 'New Event', 'pippin_sc' ),
		'all_items' 		=> __( 'All Events', 'pippin_sc' ),
		'view_item' 		=> __( 'View Event', 'pippin_sc' ),
		'search_items' 		=> __( 'Search Events', 'pippin_sc' ),
		'not_found' 		=> __( 'No Events found', 'pippin_sc' ),
		'not_found_in_trash'=> __( 'No Events found in Trash', 'pippin_sc') , 
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Events', 'pippin_sc')
	) );
	
	$event_args = array(
		'labels' 				=> $event_labels,
		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'show_ui' 				=> true, 
		'show_in_menu' 			=> true, 
		'query_var' 			=> true,
		'rewrite' 				=> apply_filters( 'sc_event_rewrite', array( 'slug' => 'events', 'with_front' => false ) ),
		'capability_type' 		=> apply_filters( 'sc_event_capability_type', 'post' ),
		'has_archive' 			=> true, 
		'hierarchical' 			=> false,
		'supports' 				=> apply_filters( 'sc_event_supports', array( 'title', 'editor', 'thumbnail' ) ),
	); 
	register_post_type('sc_event', $event_args);

}
add_action('init', 'sc_setup_post_types', 100);


function sc_event_admin_menu_icon() {
?>
<style type="text/css">
#adminmenu #menu-posts-sc_event div.wp-menu-image{
	background: transparent url(<?php echo SC_PLUGIN_URL; ?>assets/images/calendar.png) no-repeat 6px -17px;
}
#adminmenu #menu-posts-sc_event:hover div.wp-menu-image,
#adminmenu #menu-posts-sc_event.wp-has-current-submenu div.wp-menu-image {
	background-position: 6px 6px;
</style>
<?php
}
add_action( 'admin_head', 'sc_event_admin_menu_icon' );