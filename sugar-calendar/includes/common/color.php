<?php
/**
 * Color Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Sanitize a hex color, and make sure it's 6 chars long.
 *
 * @since 2.0.0
 *
 * @param string $hex_color A hexadecimal color value
 *
 * @return string A 6 char long hexadecimal value, with a preceding `#`
 */
function sugar_calendar_sanitize_hex_color( $hex_color = '' ) {

	// Trim spaces and strip tags
	$hex_color = trim( strip_tags( $hex_color ) );

	// Only sanitize if not empty
	if ( ! empty( $hex_color ) ) {

		// Ensure there's a `#` prefix
		$prefix    = '#';
		$hex_color = ltrim( $hex_color, $prefix );
		$hex_color = $prefix . $hex_color;

		// Make it 6 characters long if it's only 3
		if ( 4 === strlen( $hex_color ) ) {
			$hex_color = $prefix
				. $hex_color[1]
				. $hex_color[1]
				. $hex_color[2]
				. $hex_color[2]
				. $hex_color[3]
				. $hex_color[3];
		}
	}

	// Sanitize and return
	return sanitize_hex_color( $hex_color );
}

/**
 * Get the contrasting color to a hexadecimal color value.
 *
 * This is generally used to provide a text-color from a background color, but
 * is also used to style WordPress radio buttons for calendar colors.
 *
 * @since 2.0.0
 *
 * @param string $hex_color   Color to contrast. Defaults to lightest value.
 * @param string $black_color Darkest value. Defaults to `#000000`
 * @param string $white_color Lightest value. Defaults to `#ffffff`
 *
 * @return string
 */
function sugar_calendar_get_contrast_color( $hex_color = '#ffffff', $black_color = '#000000', $white_color = '#ffffff' ) {

	// Sanitize the return value
	$hex_color   = sugar_calendar_sanitize_hex_color( $hex_color );
	$black_color = sugar_calendar_sanitize_hex_color( $black_color );
	$white_color = sugar_calendar_sanitize_hex_color( $white_color );

	// Fix black
	if ( empty( $black_color ) ) {
		$black_color = '#000000';
	}

	// Fix white
	if ( empty( $white_color ) ) {
		$white_color = '#ffffff';
	}

	// If no hex passed, assume the background is white
	if ( empty( $hex_color ) ) {
		$hex_color = $white_color;
	}

	// Get the contrast score
	$contrast = sugar_calendar_get_contrast_score( $hex_color, $black_color, $white_color );

	// Set to white or black based on contrast ratio
	$retval = ( $contrast > 5 )
		? $black_color
		: $white_color;

	// Filter & return
	return apply_filters( 'sugar_calendar_get_contrast_color', $retval, $hex_color, $black_color, $white_color );
}

/**
 * Get the contrast score of a hexadecimal color, compared to black & white.
 *
 * This is generally used to provide a text-color from a background color, but
 * is also used to style WordPress radio buttons for calendar colors.
 *
 * @since 2.0.3
 *
 * @param string $hex_color   Color to contrast. Defaults to lightest value.
 * @param string $black_color Darkest value. Defaults to `#000000`
 * @param string $white_color Lightest value. Defaults to `#ffffff`
 *
 * @return string
 */
function sugar_calendar_get_contrast_score( $hex_color = '#ffffff', $black_color = '#000000', $white_color = '#ffffff' ) {

	// Sanitize the return value
	$hex_color   = sugar_calendar_sanitize_hex_color( $hex_color );
	$black_color = sugar_calendar_sanitize_hex_color( $black_color );
	$white_color = sugar_calendar_sanitize_hex_color( $white_color );

	// Fix black
	if ( empty( $black_color ) ) {
		$black_color = '#000000';
	}

	// Fix white
	if ( empty( $white_color ) ) {
		$white_color = '#ffffff';
	}

	// If no hex passed, assume the background is white
	if ( empty( $hex_color ) ) {
		$hex_color = $white_color;
	}

	// RGB
	$r1 = hexdec( substr( $hex_color, 1, 2 ) );
	$g1 = hexdec( substr( $hex_color, 3, 2 ) );
	$b1 = hexdec( substr( $hex_color, 5, 2 ) );

	// Black RGB
	$r2_black_color = hexdec( substr( $black_color, 1, 2 ) );
	$g2_black_color = hexdec( substr( $black_color, 3, 2 ) );
	$b2_black_color = hexdec( substr( $black_color, 5, 2 ) );

	// Calc contrast ratios
	$l1 = 0.2126 * pow( $r1 / 255, 2.2 ) +
		0.7152 * pow( $g1 / 255, 2.2 ) +
		0.0722 * pow( $b1 / 255, 2.2 );

	$l2 = 0.2126 * pow( $r2_black_color / 255, 2.2 ) +
		0.7152 * pow( $g2_black_color / 255, 2.2 ) +
		0.0722 * pow( $b2_black_color / 255, 2.2 );

	$contrast = ( $l1 > $l2 )
		? (int) ( ( $l1 + 0.05 ) / ( $l2 + 0.05 ) )
		: (int) ( ( $l2 + 0.05 ) / ( $l1 + 0.05 ) );

	// Return the contrast score
	return apply_filters( 'sugar_calendar_get_contrast_score', $contrast, $hex_color, $black_color, $white_color );
}

/**
 * Return an RGBA value from a hex color and an opacity value.
 *
 * @since 2.0.3
 *
 * @param string $hex_color
 * @param mixed  $opacity
 *
 * @return string
 */
function sugar_calendar_get_rgba_from_hex( $hex_color = '#000000', $opacity = false ) {

	// Default
	$retval = 'rgba(0,0,0,1)';

	// Sanitize the hex color
	$hex_color = sugar_calendar_sanitize_hex_color( $hex_color );

	//Return default if no color provided
	if ( empty( $hex_color ) ) {
		return $retval;
	}

	// Opacity cannot be greater than 1
	if ( absint( $opacity ) > 1 ) {
		$opacity = 1.0;
	}

	// Remove "#" prefix provided
	if ( $hex_color[ 0 ] == '#' ) {
		$hex_color = substr( $hex_color, 1 );
	}

	// Split hex up into array of values
	$hex = array(
		$hex_color[ 0 ] . $hex_color[ 1 ],
		$hex_color[ 2 ] . $hex_color[ 3 ],
		$hex_color[ 4 ] . $hex_color[ 5 ]
	);

	// Convert all hexadecimal values to rgb
	$rgb_array  = array_map( 'hexdec', $hex );
	$rgb_string = implode( ',', $rgb_array );

	// Combine into RGBA value
	$retval = "rgba({$rgb_string},{$opacity})";

	// Return RGBA color
	return $retval;
}
