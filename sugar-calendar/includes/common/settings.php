<?php
/**
 * Sugar Calendar Common Settings
 *
 * @since 2.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Registers settings in the various options sections
 *
 * @since 1.0.0
 */
function sugar_calendar_register_settings() {

	// Date/Time Formatting
	register_setting( 'sc_main_display', 'sc_number_of_events', array(
		'default' => 30
	) );
	register_setting( 'sc_main_display', 'sc_start_of_week', array(
		'default' => get_option( 'start_of_week' )
	) );
	register_setting( 'sc_main_display', 'sc_date_format', array(
		'default' => get_option( 'date_format' )
	) );
	register_setting( 'sc_main_display', 'sc_time_format', array(
		'default' => get_option( 'time_format' )
	) );
	register_setting( 'sc_main_display', 'sc_day_color_style', array(
		'default' => 'none'
	) );

	// Time Zones
	register_setting( 'sc_main_timezones', 'sc_timezone_convert' );
	register_setting( 'sc_main_timezones', 'sc_timezone_type' );
	register_setting( 'sc_main_timezones', 'sc_timezone', array(
		'default' => get_option( 'timezone_string' )
	) );

	// Editor
	register_setting( 'sc_main_editing', 'sc_editor_type' );
	register_setting( 'sc_main_editing', 'sc_custom_fields' );
	register_setting( 'sc_main_editing', sugar_calendar_get_default_calendar_option_name() );

	do_action( 'sugar_calendar_register_settings' );
}
