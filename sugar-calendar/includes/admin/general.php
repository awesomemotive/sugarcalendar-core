<?php

/**
 * Event Admin
 *
 * @package Plugins/Site/Events/Admin
 */

/**
 * @todo
 *
 * Namespace all of these functions to Sugar_Calendar\Admin\General before
 * everyone starts using them in add-ons.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the page ID for the event calendar view.
 *
 * @since 2.0
 *
 * @return string
 */
function sugar_calendar_get_admin_page_id() {
	return 'toplevel_page_sugar-calendar';
}

/**
 * Returns the primary admin page.
 *
 * @since 2.0.0
 *
 * @return string
 */
function sugar_calendar_admin_get_primary_page() {
	return 'admin.php?page=sugar-calendar';
}

/**
 * Return whether we are inside the Sugar Calendar admin area, or not.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function sugar_calendar_is_admin() {

	if (

		// Is Events Page
		sugar_calendar_admin_is_events_page()

		||

		// Is Taxonomy Page
		sugar_calendar_admin_is_taxonomy_page()

		||

		// Or if Settings pages
		\Sugar_Calendar\Admin\Settings\in()
	) {
		return true;
	}

	return false;
}

/**
 * Is this an admin area page used for displaying or interacting with Events?
 *
 * @since 2.0.2
 *
 * @return bool
 */
function sugar_calendar_admin_is_events_page() {
	$screen = get_current_screen();

	if (

		// Add if the event post type
		post_type_supports( $screen->post_type, 'events' )

		||

		// Or if Events pages
		sugar_calendar_get_admin_page_id() === $screen->id
	) {
		return true;
	}

	return false;
}

/**
 * Is this an admin area page used for displaying or interacting with Events?
 *
 * @since 2.1.0
 *
 * @return bool
 */
function sugar_calendar_admin_is_taxonomy_page() {
	$screen = get_current_screen();

	if (

		// Add if the event post type
		post_type_supports( $screen->post_type, 'events' )

		&&

		// And Taxonomy page
		! empty( $screen->taxonomy )

		&&

		// And is supported Taxonomy
		in_array( $screen->taxonomy, sugar_calendar_get_object_taxonomies(), true )
	) {
		return true;
	}

	return false;
}

/**
 * Return the base admin-area URL.
 *
 * Use this to avoid typing all of it out a million times.
 *
 * @since 2.0
 *
 * @return string
 */
function sugar_calendar_get_admin_base_url() {

	// Default args
	$args = array(
		'page' => 'sugar-calendar'
	);

	// Default URL
	$admin_url = admin_url( 'admin.php' );

	// Get the base admin URL
	$url = add_query_arg( $args, $admin_url );

	// Filter & return
	return apply_filters( 'sugar_calendar_get_admin_base_url', $url, $args, $admin_url );
}

/**
 * Get the admin URL, maybe with arguments added
 *
 * @since 2.0
 *
 * @param array $args
 * @return string
 */
function sugar_calendar_get_admin_url( $args = array() ) {
	return add_query_arg( $args, sugar_calendar_get_admin_base_url() );
}

/**
 * Get the current admin post type
 *
 * @since 2.0.0
 *
 * @return string
 */
function sugar_calendar_get_admin_post_type() {

	// Use $typenow global if it's not empty
	if ( ! empty( $GLOBALS['typenow'] ) ) {
		return $GLOBALS['typenow'];
	}

	// Use $_GET post_type if it's not empty
	if ( ! empty( $_GET['post_type'] ) ) {
		return wp_unslash( $_GET['post_type'] );
	}

	// Use get parameter
	return sugar_calendar_get_event_post_type_id();
}

/**
 * Get the view mode.
 *
 * Defaults to `list` if no mode found.
 *
 * @since 2.0.0
 *
 * @return string
 */
function sugar_calendar_get_admin_view_mode() {
	$mode    = 'month';
	$allowed = array( 'month', 'week', 'day', 'list' );
	$request = ! empty( $_REQUEST['mode'] )
		? sanitize_key( $_REQUEST['mode'] )
		: false;

	return in_array( $request, $allowed, true )
		? $request
		: $mode;
}

/**
 * Filter admin area page title (add missing "Events" part when necessary)
 *
 * @since 2.0.0
 *
 * @param string $admin_title
 * @param string $title
 *
 * @return string
 */
function sugar_calendar_admin_title( $admin_title = '', $title = '' ) {

	// Bail if global title is not empty
	if ( ! empty( $title ) ) {
		return $admin_title;
	}

	// Event title page text
	$pt    = sugar_calendar_get_admin_post_type();
	$pre   = sugar_calendar_get_post_type_label( $pt, 'name', esc_html__( 'Events', 'sugar-calendar' ) );
	$ugh   = __( 'Posts', 'sugar-calendar' );
	$page  = get_admin_page_parent();
	$event = 'edit.php?post_type=' . $pt;

	// Conditions
	$is_page = ( $event !== $page );
	$has_pre = ( strpos( $admin_title, $pre ) === 0 );
	$has_ugh = ( strpos( $admin_title, $ugh ) === 0 );

	if ( ( true === $has_ugh ) && ( false === $is_page ) ) {
		$admin_title = str_replace( $ugh, '', $admin_title );
	}

	// Only append title if not already appended
	if ( ( false === $is_page ) && ( false === $has_pre ) ) {
		$admin_title = $pre . $admin_title;
	}

	// Return maybe corrected title
	return $admin_title;
}
