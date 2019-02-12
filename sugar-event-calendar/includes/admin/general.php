<?php

/**
 * Event Admin
 *
 * @package Plugins/Site/Events/Admin
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
 * @return boolean
 */
function sugar_calendar_is_admin() {
	$screen = get_current_screen();

	if (

		// Add if the event post type
		post_type_supports( $screen->post_type, 'events' )

		||

		// Or if the Sugar Calendar page
		sugar_calendar_get_admin_page_id() === $screen->id
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
 * Set the placeholder text for the title field for this post type.
 *
 * @since 2.0.0
 *
 * @param   string   $title The placeholder text
 * @param   WP_Post  $post  The current post
 *
 * @return  string          The updated placeholder text
 */
function sugar_calendar_enter_title_here( $title, WP_Post $post ) {

	// Override if primary post type
	if ( sugar_calendar_get_event_post_type_id() === $post->post_type ) {
		$title = esc_html__( 'Name this event', 'sugar-calendar' );
	}

	// Return possibly modified title
	return $title;
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
 * Add class to the admin area if on a Sugar Calendar page.
 *
 * @since 2.0.0
 *
 * @param string $class
 */
function sugar_calendar_admin_body_class( $class = '' ) {

	// Add class if in an admin page
	if ( sugar_calendar_is_admin() ) {
		$class .= 'sugar-calendar';
	}

	// Return class string
	return $class;
}

/**
 * Hides the inline-edit-group from the admin form
 *
 * @since 2.0.0
 */
function sugar_calendar_hide_quick_bulk_edit() {

	// Bail if not an event post type
	if ( ! post_type_supports( get_current_screen()->post_type, 'events' ) ) {
		return;
	}

	?>
	<script>
		jQuery( document ).ready( function( $ ) {
			$("#the-list").on("click", "a.editinline", function () {
				jQuery(".inline-edit-group").hide();
				jQuery(".inline-edit-date").hide();
			} );
		});
	</script>
	<?php
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
	$ugh   = '&#8212; WordPress';
	$pre   = esc_html__( 'Events', 'sugar-calendar' );
	$ugh   = __( 'All Posts' );
	$page  = get_admin_page_parent();
	$event = 'edit.php?post_type=' . sugar_calendar_get_event_post_type_id();

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

/**
 * Detect & redirect away from any old admin-area post-type pages.
 *
 * This is necessary to prevent savvy users from accidentally finding hidden
 * Event Post Type pages that are still hanging around behind the scenes.
 *
 * Currently hooked to `load-edit.php` but could be changed later to handle more
 * specific cases.
 *
 * @since 2.0.0
 */
function sugar_calendar_admin_redirect_old_post_type() {
	global $typenow;

	// Redirect if global post-type matches our Event post type
	if ( sugar_calendar_get_event_post_type_id() === $typenow ) {
		wp_safe_redirect( sugar_calendar_get_admin_base_url() );
		exit();
	}
}

/**
 * Sanitize a hex color, and make sure it's 6 chars long.
 *
 * @since 2.0.0
 *
 * @param string $hex_color A hexadecimal color value
 *
 * @return string A 6 char long hexadecimal value, with a preceding `#`
 */
function sugar_calendar_sanitize_hex_color( $hex_color = '' ) {

	// Trim spaces and strip tags
	$hex_color = trim( strip_tags( $hex_color ) );

	// Only sanitize if not empty
	if ( ! empty( $hex_color ) ) {

		// Ensure there's a `#` prefix
		$prefix    = '#';
		$hex_color = ltrim( $hex_color, $prefix );
		$hex_color = $prefix . $hex_color;

		// Make it 6 characters long if it's only 3
		if ( 4 === strlen( $hex_color ) ) {
			$hex_color = $prefix
				. $hex_color[1]
				. $hex_color[1]
				. $hex_color[2]
				. $hex_color[2]
				. $hex_color[3]
				. $hex_color[3];
		}
	}

	// Sanitize and return
	return sanitize_hex_color( $hex_color );
}

/**
 * Get the contrasting color to a hexadecimal color value.
 *
 * This is generally used to provide a text-color from a background color, but
 * is also used to style WordPress radio buttons for calendar colors.
 *
 * @since 2.0.0
 *
 * @param string $hex_color   Color to contrast. Defaults to lightest value.
 * @param string $black_color Darkest value. Defaults to `#000000`
 * @param string $white_color Lightest value. Defaults to `#ffffff`
 *
 * @return string
 */
function sugar_calendar_get_contrast_color( $hex_color = '#ffffff', $black_color = '#000000', $white_color = '#ffffff' ) {

	// Sanitize the return value
	$hex_color   = sugar_calendar_sanitize_hex_color( $hex_color );
	$black_color = sugar_calendar_sanitize_hex_color( $black_color );
	$white_color = sugar_calendar_sanitize_hex_color( $white_color );

	// Fix black
	if ( empty( $black_color ) ) {
		$black_color = '#000000';
	}

	// Fix white
	if ( empty( $white_color ) ) {
		$white_color = '#ffffff';
	}

	// If no hex passed, assume the background is white
	if ( empty( $hex_color ) ) {
		$hex_color = $white_color;
	}

	// RGB
	$r1 = hexdec( substr( $hex_color, 1, 2 ) );
	$g1 = hexdec( substr( $hex_color, 3, 2 ) );
	$b1 = hexdec( substr( $hex_color, 5, 2 ) );

	// Black RGB
	$r2_black_color = hexdec( substr( $black_color, 1, 2 ) );
	$g2_black_color = hexdec( substr( $black_color, 3, 2 ) );
	$b2_black_color = hexdec( substr( $black_color, 5, 2 ) );

	// Calc contrast ratios
	$l1 = 0.2126 * pow( $r1 / 255, 2.2 ) +
		0.7152 * pow( $g1 / 255, 2.2 ) +
		0.0722 * pow( $b1 / 255, 2.2 );

	$l2 = 0.2126 * pow( $r2_black_color / 255, 2.2 ) +
		0.7152 * pow( $g2_black_color / 255, 2.2 ) +
		0.0722 * pow( $b2_black_color / 255, 2.2 );

	$contrast = ( $l1 > $l2 )
		? (int) ( ( $l1 + 0.05 ) / ( $l2 + 0.05 ) )
		: (int) ( ( $l2 + 0.05 ) / ( $l1 + 0.05 ) );

	// Set to white or black based on contrast ratio
	$retval = ( $contrast > 5 )
		? $black_color
		: $white_color;

	// Filter & return
	return apply_filters( 'sugar_calendar_get_contrast_color', $retval, $hex_color, $black_color, $white_color );
}
