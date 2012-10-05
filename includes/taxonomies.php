<?php

/**
 * Setup Event Taxonomies
 *
 * Registers the custom taxonomies.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function sc_setup_event_taxonomies() {

	$category_labels = array(
		'name' 			=> _x( 'Categories', 'taxonomy general name', 'pippin_sc' ),
		'singular_name' => _x( 'Category', 'taxonomy singular name', 'pippin_sc' ),
		'search_items' 	=>  __( 'Search Categories', 'pippin_sc'  ),
		'all_items' 	=> __( 'All Categories', 'pippin_sc'  ),
		'parent_item' 	=> __( 'Parent Category', 'pippin_sc'  ),
		'parent_item_colon' => __( 'Parent Category:', 'pippin_sc'  ),
		'edit_item' => __( 'Edit Category', 'pippin_sc'  ), 
		'update_item' => __( 'Update Category', 'pippin_sc'  ),
		'add_new_item' => __( 'Add New Category', 'pippin_sc'  ),
		'new_item_name' => __( 'New Category Name', 'pippin_sc'  ),
		'menu_name' => __( 'Categories', 'pippin_sc'  ),
	); 	

	register_taxonomy('sc_event_category', array('sc_event'), array(
		'hierarchical' => true,
		'labels' => apply_filters('sc_event_category_labels', $category_labels),
		'show_ui' => true,
		'query_var' => 'sc_event_category',
		'rewrite' => apply_filters('sc_event_category_rewrite', array('slug' => 'events/category', 'with_front' => false))
	));
	
}
add_action('init', 'sc_setup_event_taxonomies', 10);