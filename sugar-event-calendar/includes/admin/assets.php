<?php

/**
 * Event Admin Assets
 *
 * @package Plugins/Site/Events/Admin/Assets
 */

/**
 * Register assets.
 *
 * @since 2.0
 */
function sugar_calendar_admin_register_assets() {

	// URL & Version
	$url  = SC_PLUGIN_URL . 'includes/admin/assets/';
	$ver  = SC_PLUGIN_VERSION;
	$deps = array();

	// Menu
	wp_register_style( 'sugar_calendar_admin_menu',       $url . 'css/menu.css',       $deps, $ver, 'all' );

	// Nav
	wp_register_style( 'sugar_calendar_admin_nav',        $url . 'css/nav.css',        $deps, $ver, 'all' );

	// Calendar
	wp_register_style( 'sugar_calendar_admin_calendar',   $url . 'css/calendar.css',   $deps, $ver, 'all' );
	wp_register_style( 'sugar_calendar_admin_datepicker', $url . 'css/datepicker.css', $deps, $ver, 'all' );

	// Meta-box
	wp_register_style( 'sugar_calendar_admin_meta_box',   $url . 'css/meta-box.css',   $deps, $ver, 'all' );
	wp_register_script( 'sugar_calendar_admin_meta_box',  $url . 'js/meta-box.js',     $deps, $ver, false );

	// Settings
	wp_register_style( 'sugar_calendar_admin_settings',   $url . 'css/settings.css',   $deps, $ver, 'all' );

	// Calendar
	wp_register_script( 'sugar_calendar_admin_calendar',  $url . 'js/calendar.js',     $deps, $ver, false );
}

/**
 * Enqueue assets.
 *
 * @since 2.0.0
 */
function sugar_calendar_admin_event_assets() {

	// Menu styling
	wp_enqueue_style( 'sugar_calendar_admin_menu'     );
	wp_enqueue_style( 'sugar_calendar_admin_settings' );

	// Nav styling
	wp_enqueue_style( 'sugar_calendar_admin_nav' );

	// Bail if not an event post type
	if ( sugar_calendar_is_admin() ) {

		// Pointer
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );

		// Date picker script
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// Calendar styling
		wp_enqueue_style( 'sugar_calendar_admin_calendar' );
		wp_enqueue_style( 'sugar_calendar_admin_datepicker' );

		// Meta-box
		wp_enqueue_style( 'sugar_calendar_admin_meta_box' );
		wp_enqueue_script( 'sugar_calendar_admin_meta_box' );

		// Calendar
		wp_enqueue_script( 'sugar_calendar_admin_calendar' );
	}
}

/**
 * Localize scripts
 *
 * @since 2.0.0
 */
function sugar_calendar_admin_localize_scripts() {

	// Bail if not on a sugar calendar page
	if ( ! sugar_calendar_is_admin() ) {
		return;
	}

	// Localize some preferences and settings
	wp_localize_script( 'sugar_calendar_admin_meta_box', 'sc_vars', array(
		'start_of_week' => sugar_calendar_get_user_preference( 'start_of_week' ),
		'date_format'   => sugar_calendar_get_user_preference( 'date_format' ),
		'time_format'   => sugar_calendar_get_user_preference( 'time_format' ),
	) );
}
