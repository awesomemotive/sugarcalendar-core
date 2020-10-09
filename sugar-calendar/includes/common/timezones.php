<?php
/**
 * Timezone Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the timezone for the site.
 *
 * @since 1.1.3
 *
 * @param string $timezone The timezone to use
 * @return string
 */
function sugar_calendar_get_site_timezone( $timezone = '' ) {

	wp_timezone();

	// Get site settings (will use term meta eventually!
	$tzstring = get_option( 'timezone_string', $timezone );
	$offset   = get_option( 'gmt_offset' );

	/**
	 * Discourage manual offset
	 *
	 * IANA timezone database that provides PHP's timezone support uses
	 * (i.e. reversed) POSIX style signs
	 *
	 * @see http://us.php.net/manual/en/timezones.others.php
	 * @see https://bugs.php.net/bug.php?id=45543
	 * @see https://bugs.php.net/bug.php?id=45528
	 */
	if ( empty( $tzstring ) && ( 0 !== $offset ) && ( floor( $offset ) === $offset ) ) {

		// Make the offset string
		$offset_st = ( $offset > 0 )
			? "-{$offset}"
			: '+' . absint( $offset );

		// Make the Unknown timezone string
		$tzstring  = "Etc/GMT{$offset_st}";
	}

	// Issue with the timezone selected, set to 'UTC'
	if ( empty( $tzstring ) ) {
		$tzstring = 'UTC';
	}

	// Set the timezone
	$this->timezone = new \DateTimeZone( $tzstring );
}

/**
 * Output a <select> HTML element of Timezones
 *
 * @since 2.1.0
 * @param array $args
 */
function sugar_calendar_timezone_dropdown( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'id'      => 'sc_timezone',
		'name'    => 'sc_timezone',
		'class'   => 'sc-select-chosen',
		'current' => get_option( 'sc_timezone' )
	) );

	// Sanitize ID & Name
	$id      = sanitize_key( $r['id'] );
	$name    = sanitize_key( $r['name'] );

	// Sanitize classes
	$classes = array_map( 'sanitize_html_class', explode( ' ', $r['class'] ) );

	// Output the Select HTML element
	?><select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="<?php echo implode( ' ', $classes ); ?>"><?php

		// Output the timezone chooser
		echo wp_timezone_choice( $r['current'] );

	// Close the HTML element
	?></select><?php
}
