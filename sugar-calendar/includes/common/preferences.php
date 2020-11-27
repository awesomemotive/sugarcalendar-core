<?php
/**
 * User Preferences
 *
 * @package Plugins/Site/Event/User/Prefs
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get a user preference
 *
 * @since 2.0.0
 *
 * @param string $key
 * @param mixed  $default
 * @param int    $user_id
 *
 * @return mixed
 */
function sugar_calendar_get_user_preference( $key = '', $default = '', $user_id = 0 ) {

	// Default empty values
	$empties = array( '', false );

	// Get user/site/network preference map
	$keys = sugar_calendar_map_user_preference_key( $key );

	// Use current user ID if none passed
	$user_id = empty( $user_id )
		? get_current_user_id()
		: absint( $user_id );

	// Check usermeta first
	$retval = get_user_meta( $user_id, $keys['user'], true );

	// Meta may return false or an empty string if empty, but 0 or '0' are allowed
	if ( in_array( $retval, $empties, true ) ) {

		// Check the site option
		$retval = get_option( $keys['site'] );

		// No site option and multisite
		if ( ( false === $retval ) && is_multisite() ) {

			// So check network option
			$retval = get_site_option( $keys['network'] );
		}
	}

	// Fallback to default if empty
	if ( in_array( $retval, $empties, true ) ) {
		$retval = $default;
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_get_user_preference', $retval, $key, $default, $user_id );
}

/**
 * Set a user preference
 *
 * @since 2.0.0
 *
 * @param string $key
 * @param mixed  $value
 * @param int    $user_id
 *
 * @return mixed
 */
function sugar_calendar_set_user_preference( $key = '', $value = '', $user_id = 0 ) {

	// Get user/site/network preference map
	$keys = sugar_calendar_map_user_preference_key( $key );

	// Use current user ID if none passed
	$user_id = empty( $user_id )
		? get_current_user_id()
		: absint( $user_id );

	// Check usermeta first
	$retval = update_user_meta( $user_id, $keys['user'], $value );

	// Filter & return
	return apply_filters( 'sugar_calendar_set_user_preference', $retval, $key, $value, $user_id );
}

/**
 * Delete a user preference
 *
 * @since 2.0.0
 *
 * @param string $key
 * @param int    $user_id
 *
 * @return mixed
 */
function sugar_calendar_delete_user_preference( $key = '', $user_id = 0 ) {

	// Get user/site/network preference map
	$keys = sugar_calendar_map_user_preference_key( $key );

	// Use current user ID if none passed
	$user_id = empty( $user_id )
		? get_current_user_id()
		: absint( $user_id );

	// Check usermeta first
	$retval = delete_user_meta( $user_id, $keys['user'] );

	// Filter & return
	return apply_filters( 'sugar_calendar_set_user_preference', $retval, $key, $user_id );
}

/**
 * Return an array of key/value pairs for user/site/network settings, based on
 * the usermeta key being passed in.
 *
 * This function exists because not all user, site, & network metadata keys are
 * the same, even though they return the same type of data back. By passing in
 * the usermeta key, we can make decisions about what site & network keys map to
 * a user preference, and fallback to them if no user preference is set yet.
 *
 * @since 2.0.0
 *
 * @param   string  $key
 *
 * @return  array
 */
function sugar_calendar_map_user_preference_key( $key = '' ) {

	// Which usermeta key are we mapping to?
	switch ( $key ) {

		// These keys are the same between user/site/network
		case 'admin_color' :
		case 'timezone' :
		case 'time_format' :
		case 'date_format' :
		case 'start_of_week' :
		case 'WPLANG' :
		default :

			// Default return value
			$retval = array(
				'user'    => $key,
				'site'    => $key,
				'network' => $key
			);

			break;
	}

	// Filter & return
	return (array) apply_filters( 'sugar_calendar_map_user_preference_key', $retval, $key );
}
