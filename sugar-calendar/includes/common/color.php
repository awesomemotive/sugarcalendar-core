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
	list( $r1, $g1, $b1 ) = sugar_calendar_get_rgb_from_hex( $hex_color );

	// Black RGB
	list( $r2, $g2, $b2 ) = sugar_calendar_get_rgb_from_hex( $black_color );

	// Calc contrast ratios
	$l1 = 0.2126 * pow( $r1 / 255, 2.2 ) +
		0.7152 * pow( $g1 / 255, 2.2 ) +
		0.0722 * pow( $b1 / 255, 2.2 );

	$l2 = 0.2126 * pow( $r2 / 255, 2.2 ) +
		0.7152 * pow( $g2 / 255, 2.2 ) +
		0.0722 * pow( $b2 / 255, 2.2 );

	$contrast = ( $l1 > $l2 )
		? (int) ( ( $l1 + 0.05 ) / ( $l2 + 0.05 ) )
		: (int) ( ( $l2 + 0.05 ) / ( $l1 + 0.05 ) );

	// Return the contrast score
	return apply_filters( 'sugar_calendar_get_contrast_score', $contrast, $hex_color, $black_color, $white_color );
}

/**
 * Convert a hexadecimal color to an array of RGB values.
 *
 * @since 2.1.9
 *
 * @param string $hex_color
 * @return array
 */
function sugar_calendar_get_rgb_from_hex( $hex_color = '#000000' ) {

	// Sanitize
	$hex_color = sugar_calendar_sanitize_hex_color( $hex_color );

	// Strip the prefix
    $values    = str_replace( '#', '', $hex_color );

	// Map hex values to ints
	return array_map( 'hexdec', sscanf( $values, '%2s%2s%2s' ) );
}

/**
 * Return a hexadecimal value from an RGB value.
 *
 * @since 2.1.9
 *
 * @param array $rgb_color
 *
 * @return string
 */
function sugar_calendar_get_hex_from_rgb( $rgb_color = array( 0, 0, 0 ) ) {
	return sprintf( '#%02x%02x%02x', $rgb_color[0], $rgb_color[1], $rgb_color[2] );
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

	// Convert all hexadecimal values to rgb
	$rgb_array  = sugar_calendar_get_rgb_from_hex( $hex_color );
	$rgb_string = implode( ',', $rgb_array );

	// Combine into RGBA value
	$retval = "rgba({$rgb_string},{$opacity})";

	// Return RGBA color
	return $retval;
}

/**
 * Blend array of hexadecimal colors into a single hexadecimal color.
 *
 * @since 2.1.9
 *
 * @param array $colors
 */
function sugar_calendar_blend_colors( $colors = array( '#000000' ) ) {

	// Sanitize colors
	$hex_colors = array_map( 'sugar_calendar_sanitize_hex_color', $colors );

	// Default RGB colors array
	$rgb_colors = array(
		'r' => array(),
		'g' => array(),
		'b' => array()
	);

	// Loop through hex colors
	foreach ( $hex_colors as $hex_color ) {

		// Split hex up into array of values
		$rgb = sugar_calendar_get_rgb_from_hex( $hex_color );

		// Add colors to arrays
		$rgb_colors['r'][] = $rgb[0];
		$rgb_colors['g'][] = $rgb[1];
		$rgb_colors['b'][] = $rgb[2];
	}

	// Return value
	$retval = array(
		array_sum( $rgb_colors['r'] ) / count( $rgb_colors['r'] ),
		array_sum( $rgb_colors['g'] ) / count( $rgb_colors['g'] ),
		array_sum( $rgb_colors['b'] ) / count( $rgb_colors['b'] )
	);

	// Return colors
	return sugar_calendar_get_hex_from_rgb( $retval );
}
