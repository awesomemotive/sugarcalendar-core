<?php
/**
 * Sugar Calendar Common Assets
 *
 * @package Plugins/Site/Events/Common/Assets
 */
namespace Sugar_Calendar\Core\Common\Assets;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Whether assets are currently being debugged.
 *
 * @since 1.0.1
 *
 * @return bool
 */
function doing_debug() {
	return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
}

/**
 * Get the asset version.
 *
 * @since 1.0.1
 *
 * @return string
 */
function get_version() {
	return doing_debug()
		? sugar_calendar_get_request_time()
		: SC_PLUGIN_VERSION;
}

/**
 * Get the CSS path.
 *
 * @since 1.0.1
 * @return string
 */
function get_css_path() {

	// Default CSS path
	$css_path = '';

	// Minify?
	if ( ! doing_debug() ) {
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

	// Return the CSS path
	return $css_path;
}
