<?php
/**
 * Events Admin Menu
 *
 * @package Plugins/Site/Events/Admin/Menu
 */
namespace Sugar_Calendar\Admin\Menu;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add the "Calendar" submenu
 *
 * @since 2.0.0
 */
function register_page() {

	// Add an invisible upgrades page
	add_submenu_page(
		null,
		esc_html__( 'Sugar Calendar Upgrade', 'sugar-calendar' ),
		esc_html__( 'Sugar Calendar Upgrade', 'sugar-calendar' ),
		'manage_options',
		'sc-upgrades',
		'Sugar_Calendar\\Admin\\Upgrades\\page'
	);

	// Default hooks array
	$hooks = array();

	// Main plugin page
	$hooks[] = add_menu_page(
		esc_html__( 'Calendar', 'sugar-calendar' ),
		esc_html__( 'Calendar', 'sugar-calendar' ),
		'read_calendar',
		'sugar-calendar',
		'Sugar_Calendar\\Admin\\Menu\\calendar_page',
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
		add_action( "admin_head-{$hook}", 'Sugar_Calendar\\Admin\\Menu\\add_pointers' );
		add_action( "admin_head-{$hook}", 'Sugar_Calendar\\Admin\\Help\\add_calendar_tabs' );
		add_action( "admin_head-{$hook}", 'Sugar_Calendar\\Admin\\Screen\\Options\\add' );
	}
}

/**
 * Override the pointer dismiss button text, to make it clear that "Dismiss"
 * does not mean the event itself is being dismissed in same way.
 *
 * @since 2.0.0
 */
function add_pointers() {
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
function fix_menu_highlight() {
	global $parent_file, $submenu_file, $pagenow;

	// Highlight both, since they're the same thing.
	if ( sugar_calendar_admin_is_events_page() ) {

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
 * This wrapper function is hooked early in the page load process, ensuring that
 * the List Table is invoked correctly so commonly used filters are fired.
 *
 * The get_list_table() function maintains a local static variable of this List
 * Table for later reuse.
 *
 * @since 2.0.15
 */
function preload_list_table() {
	get_list_table();
}

/**
 * Get the List Table object for the current view mode.
 *
 * @since 2.0.15
 *
 * @staticvar object $retval
 *
 * @return object
 */
function get_list_table() {
	static $retval = null;

	// Avoid overriding the current list table
	if ( is_null( $retval ) ) {

		// Get the current view mode
		$mode = sugar_calendar_get_admin_view_mode();

		// Load the list table based on the mode
		switch ( $mode ) {
			case 'day' :
				$retval = new \Sugar_Calendar\Admin\Mode\Day;
				break;
			case 'week' :
				$retval = new \Sugar_Calendar\Admin\Mode\Week;
				break;
			case 'month' :
				$retval = new \Sugar_Calendar\Admin\Mode\Month;
				break;
			case 'list' :
			default :
				$retval = new \Sugar_Calendar\Admin\Mode\Basic;
				break;
		}
	}

	// Return the list table
	return $retval;
}

/**
 * Output the admin calendar page
 *
 * @since 2.0.0
 */
function calendar_page() {

	// Setup necessary vars for the Search Box
	$post_type        = sugar_calendar_get_admin_post_type();
	$post_type_object = get_post_type_object( $post_type );
	$search_label     = $post_type_object->labels->search_items;

	// Get the list table
	$list_table = get_list_table();

	// Query for calendar content
	$list_table->prepare_items();

	// Set the help tabs
	$list_table->set_help_tabs(); ?>

	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Events', 'sugar-calendar' ); ?></h1>

		<?php \Sugar_Calendar\Admin\Nav\display(); ?>

		<hr class="wp-header-end">

		<div id="sugar-calendar-admin-calendar-wrapper">

			<?php $list_table->views(); ?>

			<form id="posts-filter" method="get">

				<?php $list_table->search_box( $search_label, $post_type ); ?>

				<input type="hidden" name="page" value="sugar-calendar" />

				<?php $list_table->display(); ?>

			</form>

			<div id="ajax-response"></div>
			<br class="clear">
		</div>
	</div>

<?php
}

/**
 * Add class to the admin area if on a Sugar Calendar page.
 *
 * @since 2.0.0
 *
 * @param string $class
 */
function body_class( $class = '' ) {

	// Add class if in an admin page
	if ( sugar_calendar_admin_is_events_page() ) {
		$class .= 'sugar-calendar';
	}

	// Return class string
	return $class;
}

/**
 * Maybe empty event trash
 *
 * Hooked onto a specific action, and empties the event trash after a series of
 * nonce and capability checks.
 *
 * @since 2.0.0
 */
function maybe_empty_trash() {

	// Bail if not asking to delete all trashed events
	if ( ! isset( $_REQUEST['delete_all_trashed_events'] ) ) {
		return;
	}

	check_admin_referer( 'event-actions' );

	// Get the post type object
	$pt_obj = get_post_type_object( sugar_calendar_get_event_post_type_id() );

	// Bail if current user cannot trash events
	if ( ! current_user_can( $pt_obj->cap->delete_posts ) ) {
		return;
	}

	// Get trashed events
	$trashed = sugar_calendar_get_events( array(
		'number' => -1,
		'status' => 'trash'
	) );

	// Bail if nothing in trash
	if ( empty( $trashed ) ) {
		return;
	}

	// Get posts
	$post_ids = wp_filter_object_list( $trashed, array( 'object_type' => 'post' ), 'and', 'object_id' );

	// Maybe delete posts
	if ( ! empty( $post_ids ) ) {
		foreach ( $post_ids as $post_id ) {
			if ( current_user_can( 'delete_post', $post_id ) ) {
				wp_delete_post( $post_id );
			}
		}
	}

	// Delete all trashed events, regardless of their object relationships
	sugar_calendar_delete_events( array(
		'number' => -1,
		'status' => 'trash'
	) );

	// Redirect
	wp_safe_redirect( remove_query_arg( 'delete_all_trashed_events', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	exit();
}
