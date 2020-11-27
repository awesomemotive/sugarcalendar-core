<?php
/**
 * General Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the updater
 *
 * This function provides an abstraction layer for add-ons, so that they are not
 * required to include their own bespoke copies of it.
 *
 * @since 2.0.12
 *
 * @return mixed
 */
function sugar_calendar_get_updater() {

	// Name of core updater function
	$updater = '\\Sugar_Calendar\\Standard\\License\\get_updater';

	// Return the updater if callable
	if ( function_exists( $updater ) && is_callable( $updater ) ) {
		return call_user_func( $updater );
	}

	// Return false if updater not found
	return false;
}

/**
 * Abstraction for WordPress Script-Debug checking to avoid code duplication.
 *
 * @since 2.0.18
 *
 * @return boolean
 */
function sugar_calendar_doing_script_debug() {
	return Sugar_Calendar\Common\Assets\doing_debug();
}

/**
 * Return the current asset version.
 *
 * @since 2.0.18
 *
 * @return string
 */
function sugar_calendar_get_assets_version() {
	return Sugar_Calendar\Common\Assets\get_version();
}

/**
 * Format a timestamp, possibly by time zone.
 *
 * To transmogrify the date format into a language-specific variant, please use
 * sugar_calendar_date_i18n() instead.
 *
 * @since 2.1.0
 *
 * @param string $format    Defaults to MySQL datetime format.
 * @param mixed  $timestamp Defaults to "now".
 * @param string $timezone  Defaults to time zone preference.
 *
 * @return string
 */
function sugar_calendar_date( $format = 'Y-m-d H:i:s', $timestamp = null, $timezone = null ) {

	// Fallback to "now"
	if ( null === $timestamp ) {
		$timestamp = sugar_calendar_get_request_time();

	// Fallback to whatever strtotime() guesses at
	} elseif ( ! is_numeric( $timestamp ) ) {
		$timestamp = strtotime( $timestamp );
	}

	// Fallback to the user preference
	if ( null === $timezone ) {
		$timezone = sugar_calendar_get_timezone();
	}

	// Maybe try to get the timezone object
	if ( is_string( $timezone ) ) {
		$timezone = sugar_calendar_get_timezone_object( $timezone );
	}

	//
	$datetime = new \DateTime( '@' . $timestamp, $timezone );
	$retval   = $datetime->format( $format );

	return $retval;
}

/**
 * Translate a timestamp into a specific format, possibly by time zone.
 *
 * @since 2.1.0
 *
 * @param string $format    Defaults to MySQL datetime format.
 * @param mixed  $timestamp Defaults to "now".
 * @param string $timezone  Defaults to time zone preference.
 * @param string $locale    Defaults to user/site preference.
 *
 * @return string
 */
function sugar_calendar_date_i18n( $format = 'Y-m-d H:i:s', $timestamp = null, $timezone = null, $locale = null ) {

	// Switch!
	if ( ! empty( $locale ) ) {
		switch_to_locale( $locale );
	}

	// Fallback to "now"
	if ( null === $timestamp ) {
		$timestamp = sugar_calendar_get_request_time();

	// Fallback to whatever strtotime() guesses at
	} elseif ( ! is_numeric( $timestamp ) ) {
		$timestamp = strtotime( $timestamp );
	}

	// Fallback to the user preference
	if ( null === $timezone ) {
		$timezone = sugar_calendar_get_timezone();
	}

	// Maybe try to get the timezone object
	if ( is_string( $timezone ) ) {
		$timezone = sugar_calendar_get_timezone_object( $timezone );
	}

	// Format the date using the WordPress locale
	$retval = wp_date( $format, $timestamp, $timezone );

	// Unswitch!
	if ( ! empty( $locale ) ) {
		restore_previous_locale();
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_date_i18n', $retval, $format, $timestamp, $timezone, $locale );
}
