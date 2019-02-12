<?php

/**
 * Events Admin Menu
 *
 * @package Plugins/Site/Events/Admin/Menu
 *
 * @see WP_Posts_List_Table
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add the "Calendar" submenu
 *
 * @since 2.0.0
 */
function sugar_calendar_admin_register_page() {

	// Add an invisible upgrades page
	add_submenu_page(
		null,
		esc_html__( 'Sugar Calendar Upgrade', 'sugar-calendar' ),
		esc_html__( 'Sugar Calendar Upgrade', 'sugar-calendar' ),
		'manage_options',
		'sc-upgrades',
		'sugar_calendar_admin_upgrade_page'
	);

	// Default hooks array
	$hooks = array();

	// Main plugin page
	$hooks[] = add_menu_page(
		esc_html__( 'Calendar', 'sugar-calendar' ),
		esc_html__( 'Calendar', 'sugar-calendar' ),
		'read_calendar',
		'sugar-calendar',
		'sugar_calendar_admin_calendar_page',
		'dashicons-calendar-alt',
		2
	);

	// Get the main post type object
	$post_type = sugar_calendar_get_event_post_type_id();
	$pt_object = get_post_type_object( $post_type );

	// "Add New" page
	$hooks[] = add_submenu_page(
		'sugar-calendar',
		esc_html__( 'Add New', 'sugar-calendar' ),
		esc_html__( 'Add New', 'sugar-calendar' ),
		$pt_object->cap->create_posts,
		'post-new.php?post_type=' . $post_type,
		false
	);

	// Highlight helper
	foreach ( $hooks as $hook ) {
		add_action( "admin_head-{$hook}", 'sugar_calendar_admin_add_pointers'       );
		add_action( "admin_head-{$hook}", 'sugar_calendar_admin_add_help_tabs'      );
		add_action( "admin_head-{$hook}", 'sugar_calendar_admin_add_screen_options' );
	}
}

/**
 * Override the pointer dismiss button text, to make it clear that "Dismiss"
 * does not mean the event itself is being dismissed in same way.
 *
 * @since 2.0.0
 */
function sugar_calendar_admin_add_pointers() {
	wp_localize_script( 'wp-pointer', 'wpPointerL10n', array(
		'dismiss' => esc_html__( 'Close', 'sugar-calendar' ),
	) );
}

/**
 * This tells WordPress to highlight the Events > Calendar submenu.
 *
 * @since 2.0.0
 *
 * @global string $parent_file
 * @global array  $submenu_file
 * @global array  $pagenow
 */
function sugar_calendar_admin_fix_menu_highlight() {
	global $parent_file, $submenu_file, $pagenow;

	// Highlight both, since they're the same thing.
	if ( sugar_calendar_is_admin() ) {

		// Always set the parent file to the main menu
		$parent_file  = 'sugar-calendar';

		// Fix "Add New Event" and "Edit Event" highlights
		if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
			$submenu_file = 'post-new.php?post_type=sc_event';

		// Set to calendar
		} else {
			$submenu_file = 'sugar-calendar';
		}
	}
}

/**
 * Output the admin calendar page
 *
 * @since 2.0.0
 */
function sugar_calendar_admin_calendar_page() {

	// Get the post type for easy caps checking
	$mode             = sugar_calendar_get_admin_view_mode();
	$post_type        = sugar_calendar_get_admin_post_type();
	$post_type_object = get_post_type_object( $post_type );

	// Load the list table based on the mode
	switch ( $mode ) {
		case 'day' :
			$wp_list_table = new Sugar_Calendar\Day_Table();
			break;
		case 'week' :
			$wp_list_table = new Sugar_Calendar\Week_Table();
			break;
		case 'month' :
			$wp_list_table = new Sugar_Calendar\Month_Table();
			break;
		case 'list' :
		default :
			$wp_list_table = new Sugar_Calendar\List_Table();
			break;
	}

	// Query for calendar content
	$wp_list_table->prepare_items();

	// Set the help tabs
	$wp_list_table->set_help_tabs(); ?>

	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Events', 'sugar-calendar' ); ?></h1>

		<?php sugar_calendar_admin_navigation(); ?>

		<hr class="wp-header-end">

		<?php $wp_list_table->views(); ?>

		<form id="posts-filter" method="get">

			<?php $wp_list_table->search_box( $post_type_object->labels->search_items, $post_type ); ?>

			<input type="hidden" name="page" value="sugar-calendar" />

			<?php $wp_list_table->display(); ?>

		</form>

		<div id="ajax-response"></div>
		<br class="clear">
	</div>

<?php
}
