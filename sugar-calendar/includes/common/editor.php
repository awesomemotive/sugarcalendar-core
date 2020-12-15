<?php
/**
 * Sugar Calendar Common Editor Functions
 *
 * @since 2.0.0
 */
namespace Sugar_Calendar\Common\Editor;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the currently selected Editor interface setting.
 *
 * @since 2.0.20
 *
 * @return string
 */
function current() {
	$default = fallback();
	$retval  = get_option( 'sc_editor_type', $default );

	// Filter & return
	return apply_filters( 'sugar_calendar_get_current_editor', $retval, $default );
}

/**
 * Get the fallback Editor interface setting.
 *
 * This is subject to change in future versions of Sugar Calendar.
 *
 * @since 2.0.20
 *
 * @return string
 */
function fallback() {

	// Return value
	$retval = 'classic';

	// Filter & return
	return apply_filters( 'sugar_calendar_get_fallback_editor', $retval );
}

/**
 * Get the array of currently registered Editors.
 *
 * @since 2.0.20
 *
 * @return array
 */
function registered() {

	// Filter & return
	return apply_filters( 'sugar_calendar_get_registered_editors', array(

		// Block Editor
		array(
			'id'       => 'block',
			'label'    => esc_html__( 'Block Editor', 'sugar-calendar' ),
			'disabled' => ! function_exists( 'register_block_type' )
		),

		// Classic Editor
		array(
			'id'       => 'classic',
			'label'    => esc_html__( 'Classic Editor', 'sugar-calendar' ),
			'disabled' => false
		),
	) );
}

/**
 * Get the currently selected Editor custom fields setting.
 *
 * @since 2.1.0
 *
 * @return bool
 */
function custom_fields() {
	$retval = get_option( 'sc_custom_fields', false );

	// Filter & return
	return apply_filters( 'sugar_calendar_get_custom_fields', $retval, false );
}
