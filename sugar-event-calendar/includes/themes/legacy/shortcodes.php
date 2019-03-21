<?php

/**
 * Add Event Calendar shortcodes
 *
 * @since 2.0.0
 */
function sc_add_shortcodes() {
	add_shortcode( 'sc_events_list',     'sc_events_list_shortcode'     );
	add_shortcode( 'sc_events_calendar', 'sc_events_calendar_shortcode' );
}

/**
 * Event Calendar shortcode callback
 *
 * @since 1.0.0
 *
 * @param array $atts
 * @param null|string $content
 *
 * @return string
 */
function sc_events_calendar_shortcode( $atts, $content = null ) {

	$atts = shortcode_atts( array(
		'size'     => 'large',
		'category' => null,
		'type'     => 'month'
	), $atts );

	$size        = isset( $atts['size']     ) ? $atts['size']     : '';
	$category    = isset( $atts['category'] ) ? $atts['category'] : '';
	$type        = isset( $atts['type']     ) ? $atts['type']     : 'month';
	$valid_types = sc_get_valid_calendar_types();

	if ( ! in_array( $type, $valid_types, true ) ) {
		$type = 'month';
	}

	return '<div id="sc_calendar_wrap">' . sc_get_events_calendar( $size, $category, $type ) . '</div>';
}

/**
 * Event list shortcode callback
 *
 * @since 1.0.0
 *
 * @param $atts
 * @param null $content
 *
 * @return string
 */
function sc_events_list_shortcode( $atts, $content = null ) {

	$atts = shortcode_atts( array(
		'display'         => 'upcoming',
		'order'           => '',
		'number'          => '5',
		'category'        => null,
		'show_date'       => null,
		'show_time'       => null,
		'show_categories' => null,
		'show_link'       =>  null,
	), $atts );

	// Escape all values
	$display         = esc_attr( $atts['display'] );
	$order           = esc_attr( $atts['order'] );
	$category        = esc_attr( $atts['category'] );
	$number          = esc_attr( $atts['number'] );
	$show_date       = esc_attr( $atts['show_date'] );
	$show_time       = esc_attr( $atts['show_time'] );
	$show_categories = esc_attr( $atts['show_categories'] );
	$show_link       = esc_attr( $atts['show_link'] );

	// Return arguments
	$args = array(
		'date'       => $show_date,
		'time'       => $show_time,
		'categories' => $show_categories,
		'link'       => $show_link
	);

	return sc_get_events_list( $display, $category, $number, $args, $order );
}
