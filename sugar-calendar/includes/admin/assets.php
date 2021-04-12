<?php
/**
 * Event Admin Assets
 *
 * @package Plugins/Site/Events/Admin/Assets
 */
namespace Sugar_Calendar\Admin\Assets;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Common\Assets as Assets;

/**
 * Register assets.
 *
 * @since 2.0
 */
function register() {

	// URL
	$url  = Assets\get_url() . 'includes/admin/assets/';

	// Version, and Path
	$ver  = Assets\get_version();
	$path = Assets\get_css_path();

	// Dependencies
	$deps    = array();
	$chosen  = array( 'sugar_calendar_vendor_chosen' );
	$general = array( 'sugar_calendar_admin_general' );
	$dialog  = array_push( $general, 'jquery-ui-dialog' );
	$wpui    = array( 'wp-jquery-ui-dialog' );

	/** Scripts ***************************************************************/

	// Chosen
	wp_register_script( 'sugar_calendar_vendor_chosen',    "{$url}js/chosen.js",     $deps,    $ver, false );

	// Admin
	wp_register_script( 'sugar_calendar_admin_general',   "{$url}js/sc-admin.js",    $chosen,  $ver, false );

	// Meta Box
	wp_register_script( 'sugar_calendar_admin_meta_box',  "{$url}js/sc-meta-box.js", $general, $ver, false );

	// Calendar
	wp_register_script( 'sugar_calendar_admin_calendar',  "{$url}js/sc-calendar.js", $general, $ver, false );

	// Settings
	wp_register_script( 'sugar_calendar_admin_settings',  "{$url}js/sc-settings.js", $general, $ver, false );

	// Taxonomy
	wp_register_script( 'sugar_calendar_admin_taxonomy',  "{$url}js/sc-taxonomy.js", $dialog,  $ver, false );

	/** Styles ****************************************************************/

	// Chosen
	wp_register_style( 'sugar_calendar_vendor_chosen',    "{$url}css/{$path}chosen.css",        $deps,   $ver, 'all' );
	wp_register_style( 'sugar_calendar_admin_chosen',     "{$url}css/{$path}sc-chosen.css",     $chosen, $ver, 'all' );

	// Menu
	wp_register_style( 'sugar_calendar_admin_menu',       "{$url}css/{$path}sc-menu.css",       $deps,   $ver, 'all' );

	// Nav
	wp_register_style( 'sugar_calendar_admin_nav',        "{$url}css/{$path}sc-nav.css",        $deps,   $ver, 'all' );

	// Calendar
	wp_register_style( 'sugar_calendar_admin_calendar',   "{$url}css/{$path}sc-calendar.css",   $deps,   $ver, 'all' );
	wp_register_style( 'sugar_calendar_admin_datepicker', "{$url}css/{$path}sc-datepicker.css", $deps,   $ver, 'all' );

	// Meta-box
	wp_register_style( 'sugar_calendar_admin_meta_box',   "{$url}css/{$path}sc-meta-box.css",   $deps,   $ver, 'all' );

	// Settings
	wp_register_style( 'sugar_calendar_admin_settings',   "{$url}css/{$path}sc-settings.css",   $deps,   $ver, 'all' );

	// Taxonomy
	wp_register_style( 'sugar_calendar_admin_taxonomy',   "{$url}css/{$path}sc-taxonomy.css",   $wpui,   $ver, 'all' );
}

/**
 * Enqueue assets.
 *
 * @since 2.0.0
 */
function enqueue() {

	// Menu styling
	wp_enqueue_style( 'sugar_calendar_admin_menu' );

	// Nav styling
	wp_enqueue_style( 'sugar_calendar_admin_nav' );

	// Settings Pages
	if ( \Sugar_Calendar\Admin\Settings\in() ) {

		// Settings
		wp_enqueue_script( 'sugar_calendar_admin_settings' );
		wp_enqueue_style( 'sugar_calendar_admin_settings' );

		// Chosen
		wp_enqueue_script( 'sugar_calendar_admin_chosen' );
		wp_enqueue_style( 'sugar_calendar_admin_chosen' );
	}

	// Events Pages
	if ( sugar_calendar_admin_is_events_page() ) {

		// General
		wp_enqueue_script( 'sugar_calendar_admin_general' );

		// Chosen
		wp_enqueue_script( 'sugar_calendar_admin_chosen' );
		wp_enqueue_style( 'sugar_calendar_admin_chosen' );

		// Pointer
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );

		// Date picker script
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// Calendar styling
		wp_enqueue_script( 'sugar_calendar_admin_calendar' );
		wp_enqueue_style( 'sugar_calendar_admin_calendar' );
		wp_enqueue_style( 'sugar_calendar_admin_datepicker' );

		// Meta-box
		wp_enqueue_script( 'sugar_calendar_admin_meta_box' );
		wp_enqueue_style( 'sugar_calendar_admin_meta_box' );
	}

	// Taxonomy Pages
	if ( sugar_calendar_admin_is_taxonomy_page() ) {

		// Taxonomy
		wp_enqueue_script( 'sugar_calendar_admin_taxonomy' );
		wp_enqueue_style( 'sugar_calendar_admin_taxonomy' );
	}
}

/**
 * Localize scripts
 *
 * @since 2.0.0
 */
function localize() {

	// License settings
	wp_localize_script( 'sugar_calendar_admin_settings', 'sc_vars', array(
		'ajax_url'          => admin_url( 'admin-ajax.php' ),
		'license_nonce'     => wp_create_nonce( 'sc_license_nonce' ),
		'label_btn_clicked' => esc_html__( 'Verifying',    'sugar-calendar' ),
		'label_btn_default' => esc_html__( 'Verify',       'sugar-calendar' ),
		'label_feedback'    => esc_html__( 'Verifying...', 'sugar-calendar' ),
		'feedback_empty'    => esc_html__( 'Please enter a valid license key.', 'sugar-calendar' )
	) );

	// User preferences
	wp_localize_script( 'sugar_calendar_admin_meta_box', 'sc_vars', array(
		'start_of_week' => sugar_calendar_get_user_preference( 'sc_start_of_week' ),
		'date_format'   => sugar_calendar_get_user_preference( 'sc_date_format' ),
		'time_format'   => sugar_calendar_get_user_preference( 'sc_time_format' ),
		'timezone'      => sugar_calendar_get_user_preference( 'sc_timezone' )
	) );
}
