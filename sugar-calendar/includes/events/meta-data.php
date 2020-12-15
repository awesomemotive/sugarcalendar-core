<?php

/**
 * Event Meta Data
 *
 * @package Plugins/Site/Events/Meta data
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add metadata to an event.
 *
 * Event ID is *not* of type: Post, Term, Comment, or User. It is the ID of the
 * Event from a Sugar_Calendar\Event() object from the sc_events database table.
 *
 * @since 2.0.0
 *
 * @param int    $id         Event ID, from the sc_events database table.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added.
 *                           Default false.
 * @return int|false Meta ID on success, false on failure.
 */
function add_event_meta( $id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'sc_event', $id, $meta_key, $meta_value, $unique );
}

/**
 * Remove from an event, metadata matching key and/or value.
 *
 * Event ID is *not* of type: Post, Term, Comment, or User. It is the ID of the
 * Event from a Sugar_Calendar\Event() object from the sc_events database table.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @since 2.0.0
 *
 * @param int    $id         Event ID, from the sc_events database table.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 * @return bool True on success, false on failure.
 */
function delete_event_meta( $id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'sc_event', $id, $meta_key, $meta_value );
}

/**
 * Retrieve from an event, metadata value by key.
 *
 * Event ID is *not* of type: Post, Term, Comment, or User. It is the ID of the
 * Event from a Sugar_Calendar\Event() object from the sc_events database table.
 *
 * @since 2.0.0
 *
 * @param int    $id        Event ID, from the sc_events database table.
 * @param string $meta_key  Optional. The meta key to retrieve. By default, returns
 *                          data for all keys. Default empty.
 * @param bool   $single    Optional. Whether to return a single value. Default false.
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function get_event_meta( $id, $meta_key = '', $single = false ) {
	return get_metadata( 'sc_event', $id, $meta_key, $single );
}

/**
 * Update metadata for an event ID, and/or key, and/or value.
 *
 * Event ID is *not* of type: Post, Term, Comment, or User. It is the ID of the
 * Event from a Sugar_Calendar\Event() object from the sc_events database table.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and event ID.
 *
 * If the meta field for the event does not exist, it will be added.
 *
 * @since 2.0.0
 *
 * @param int    $id         Event ID, from the sc_events database table.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *                           Default empty.
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function update_event_meta( $id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'sc_event', $id, $meta_key, $meta_value, $prev_value );
}

/**
 * Updates metadata cache for list of event IDs.
 *
 * Performs SQL query to retrieve the metadata for the event IDs and
 * updates the metadata cache for the events. Therefore, the functions,
 * which call this function, do not need to perform SQL queries on their own.
 *
 * @since 2.0.0
 *
 * @param array $ids List of event IDs.
 * @return array|false Returns false if there is nothing to update or an array
 *                     of meta data.
 */
function update_eventmeta_cache( $ids ) {
	return update_meta_cache( 'sc_event', $ids );
}

/**
 * Register metadata keys & sanitization callbacks
 *
 * Note: Calendar Color metadata is saved in Sugar_Calendar\Term_Meta_UI
 *
 * @since 2.0.0
 */
function sugar_calendar_register_meta_data() {

	// Audience
	register_meta( 'sc_event', 'audience', array(
		'type'              => 'string',
		'description'       => '',
		'single'            => true,
		'sanitize_callback' => 'sugar_calendar_sanitize_audience',
		'auth_callback'     => null,
		'show_in_rest'      => false,
	) );

	// Capacity
	register_meta( 'sc_event', 'capacity', array(
		'type'              => 'string',
		'description'       => '',
		'single'            => true,
		'sanitize_callback' => 'sugar_calendar_sanitize_capacity',
		'auth_callback'     => null,
		'show_in_rest'      => false,
	) );

	// Language
	register_meta( 'sc_event', 'language', array(
		'type'              => 'string',
		'description'       => '',
		'single'            => true,
		'sanitize_callback' => 'sugar_calendar_sanitize_language',
		'auth_callback'     => null,
		'show_in_rest'      => false,
	) );

	// Location
	register_meta( 'sc_event', 'location', array(
		'type'              => 'string',
		'description'       => '',
		'single'            => true,
		'sanitize_callback' => 'sugar_calendar_sanitize_location',
		'auth_callback'     => null,
		'show_in_rest'      => false,
	) );

	// Color
	register_meta( 'sc_event', 'color', array(
		'type'              => 'string',
		'description'       => '',
		'single'            => true,
		'sanitize_callback' => 'sugar_calendar_sanitize_color',
		'auth_callback'     => null,
		'show_in_rest'      => false,
	) );
}

/**
 * Sanitize event audience for saving
 *
 * @since 2.0.0
 *
 * @param string $value
 */
function sugar_calendar_sanitize_audience( $value = '' ) {
	return trim( strip_tags( $value ) );
}

/**
 * Sanitize event capacity for saving
 *
 * @since 2.0.0
 *
 * @param int $value
 */
function sugar_calendar_sanitize_capacity( $value = 0 ) {
	return (int) $value;
}

/**
 * Sanitize event language for saving
 *
 * @since 2.0.0
 *
 * @param string $value
 */
function sugar_calendar_sanitize_language( $value = '' ) {
	return trim( strip_tags( $value ) );
}

/**
 * Sanitize event location for saving
 *
 * @since 2.0.0
 *
 * @param string $value
 */
function sugar_calendar_sanitize_location( $value = '' ) {
	return trim( strip_tags( $value ) );
}

/**
 * Sanitize event color for saving
 *
 * @since 2.0.0
 *
 * @param string $value
 */
function sugar_calendar_sanitize_color( $value = '' ) {
	return sugar_calendar_sanitize_hex_color( trim( strip_tags( $value ) ) );
}

/**
 * Sanitize time zone for saving
 *
 * @since 2.1.0
 *
 * @param string $value
 */
function sugar_calendar_sanitize_timezone( $value = '' ) {
	return sugar_calendar_validate_timezone( trim( strip_tags( $value ) ) );
}
