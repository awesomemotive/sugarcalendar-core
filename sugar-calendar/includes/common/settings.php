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
	register_setting( 'sc_main_display', 'sc_number_of_events' );
	register_setting( 'sc_main_display', 'sc_start_of_week' );
	register_setting( 'sc_main_display', 'sc_date_format' );
	register_setting( 'sc_main_display', 'sc_time_format' );

	// Timezones
	register_setting( 'sc_main_timezone', 'sc_timezone_view' );
	register_setting( 'sc_main_timezone', 'sc_timezone' );

	// Editor
	register_setting( 'sc_main_editing', 'sc_editor_type' );
	register_setting( 'sc_main_editing', 'sc_custom_fields' );

	do_action( 'sugar_calendar_register_settings' );
}
