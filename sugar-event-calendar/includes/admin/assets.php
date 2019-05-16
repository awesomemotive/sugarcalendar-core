<?php
/**
 * Event Admin Assets
 *
 * @package Plugins/Site/Events/Admin/Assets
 */
namespace Sugar_Calendar\Admin\Assets;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register assets.
 *
 * @since 2.0
 */
function register() {

	// URL & Version
	$url  = SC_PLUGIN_URL . 'includes/admin/assets/';
	$ver  = SC_PLUGIN_VERSION;
	$deps = array();
	$js_chos = array( 'sugar_calendar_admin_chosen' );
	$js_deps = array( 'sugar_calendar_admin_general' );

	// Suffixes
	$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

	// Default CSS path
	$css_path = '';

	// Minify?
	if ( empty( $debug ) ) {
		$css_path = trailingslashit( 'min' );
	}

	// Right-to-Left?
	if ( is_rtl() ) {
		$css_path .= 'rtl';
	} else {
		$css_path .= 'ltr';
	}

	// Maybe add a trailing slash
	if ( ! empty( $css_path ) ) {
		$css_path = trailingslashit( $css_path );
	}

	/** Scripts ***************************************************************/

	// Chosen
	wp_register_script( 'sugar_calendar_admin_chosen',    "{$url}js/chosen.js",      array(), $ver, false );

	// Admin
	wp_register_script( 'sugar_calendar_admin_general',   "{$url}js/sc-admin.js",    $js_chos,  $ver, false );

	// Meta Box
	wp_register_script( 'sugar_calendar_admin_meta_box',  "{$url}js/sc-meta-box.js", $js_deps, $ver, false );

	// Calendar
	wp_register_script( 'sugar_calendar_admin_calendar',  "{$url}js/sc-calendar.js", $js_deps, $ver, false );

	// Calendar
	wp_register_script( 'sugar_calendar_admin_settings',  "{$url}js/sc-settings.js", $js_deps, $ver, false );

	/** Styles ****************************************************************/

	// Chosen
	wp_register_style( 'sugar_calendar_admin_chosen',     "{$url}css/{$css_path}chosen.css",     $deps,   $ver, 'all' );
	wp_register_style( 'sugar_calendar_admin_chosen_sc',  "{$url}css/{$css_path}sc-chosen.css", array( 'sugar_calendar_admin_chosen' ), $ver, 'all' );

	// Menu
	wp_register_style( 'sugar_calendar_admin_menu',       "{$url}css/{$css_path}sc-menu.css",       $deps,   $ver, 'all' );

	// Nav
	wp_register_style( 'sugar_calendar_admin_nav',        "{$url}css/{$css_path}sc-nav.css",        $deps,   $ver, 'all' );

	// Calendar
	wp_register_style( 'sugar_calendar_admin_calendar',   "{$url}css/{$css_path}sc-calendar.css",   $deps,   $ver, 'all' );
	wp_register_style( 'sugar_calendar_admin_datepicker', "{$url}css/{$css_path}sc-datepicker.css", $deps,   $ver, 'all' );

	// Meta-box
	wp_register_style( 'sugar_calendar_admin_meta_box',   "{$url}css/{$css_path}sc-meta-box.css",   $deps,   $ver, 'all' );

	// Settings
	wp_register_style( 'sugar_calendar_admin_settings',   "{$url}css/{$css_path}sc-settings.css",   $deps,   $ver, 'all' );
}

/**
 * Enqueue assets.
 *
 * @since 2.0.0
 */
function enqueue() {

	// Menu styling
	wp_enqueue_style( 'sugar_calendar_admin_menu'     );

	// Nav styling
	wp_enqueue_style( 'sugar_calendar_admin_nav' );

	// Settings Pages
	if ( \Sugar_Calendar\Admin\Settings\in() ) {
		wp_enqueue_style( 'sugar_calendar_admin_settings' );
		wp_enqueue_script( 'sugar_calendar_admin_settings' );
	}

	// Events Pages
	if ( sugar_calendar_admin_is_events_page() ) {

		// General
		wp_enqueue_script( 'sugar_calendar_admin_general' );

		// Chosen
		wp_enqueue_script( 'sugar_calendar_admin_chosen' );
		wp_enqueue_style( 'sugar_calendar_admin_chosen_sc' );

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
		'start_of_week' => sugar_calendar_get_user_preference( 'start_of_week' ),
		'date_format'   => sugar_calendar_get_user_preference( 'date_format' ),
		'time_format'   => sugar_calendar_get_user_preference( 'time_format' ),
	) );
}
