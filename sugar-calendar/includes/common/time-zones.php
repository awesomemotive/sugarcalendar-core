<?php
/**
 * Time Zone Functions
 *
 * @package Plugins/Site/Events/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the time zone for the site.
 *
 * @since 1.1.3
 *
 * @param string $timezone The time zone to use
 * @return string
 */
function sugar_calendar_get_timezone( $timezone = '' ) {

	// Get site settings (will use term meta eventually!
	$tzstring = ! empty( $timezone )
		? get_option( 'timezone_string' )
		: get_option( 'timezone_string' );

	$offset   = get_option( 'gmt_offset' );

	/**
	 * Discourage manual offset
	 *
	 * IANA time zone database that provides PHP's time zone support uses
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

		// Make the Unknown time zone string
		$tzstring  = "Etc/GMT{$offset_st}";
	}

	// Issue with the time zone selected, set to 'UTC'
	if ( empty( $tzstring ) ) {
		$tzstring = 'UTC';
	}

	// Set the time zone
	return new \DateTimeZone( $tzstring );
}

/**
 * Output a <select> HTML element of Time Zones
 *
 * @since 2.1.0
 * @param array $args
 */
function sugar_calendar_timezone_dropdown( $args = array() ) {
	static $mo_loaded = false, $locale_loaded = null;

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'id'          => 'sc_timezone',
		'name'        => 'sc_timezone',
		'class'       => 'sc-select-chosen',
		'locale'      => '',
		'current'     => get_option( 'sc_timezone' ),
		'allow_empty' => true,
		'placeholder' => esc_html__( 'Select a time zone', 'sugar-calendar' ),
		'none'        => esc_html__( 'Floating',           'sugar-calendar' )
	) );

	// Sanitize ID & Name
	$id            = sanitize_key( $r['id'] );
	$name          = sanitize_key( $r['name'] );
	$locale        = $r['locale'];
	$selected_zone = $r['current'];
	$none_text     = $r['none'];
	$placeholder   = $r['placeholder'];

	// Sanitize classes
	$classes = array_map( 'sanitize_html_class', explode( ' ', $r['class'] ) );

	// Start the HTML structure
	$structure = array(
		'<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" class="' . implode( ' ', $classes ) . '" placeholder="' . esc_attr( $placeholder ) . '">'
	);

	$continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific' );

	// Load translations for continents and cities.
	if ( ! $mo_loaded || $locale !== $locale_loaded ) {
		$locale_loaded = $locale ? $locale : get_locale();
		$mofile        = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
		unload_textdomain( 'continents-cities' );
		load_textdomain( 'continents-cities', $mofile );
		$mo_loaded = true;
	}

	$zonen = array();
	foreach ( timezone_identifiers_list() as $zone ) {
		$zone = explode( '/', $zone );
		if ( ! in_array( $zone[0], $continents, true ) ) {
			continue;
		}

		// This determines what gets set and translated - we don't translate Etc/* strings here, they are done later.
		$exists    = array(
			0 => ( isset( $zone[0] ) && $zone[0] ),
			1 => ( isset( $zone[1] ) && $zone[1] ),
			2 => ( isset( $zone[2] ) && $zone[2] ),
		);
		$exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
		$exists[4] = ( $exists[1] && $exists[3] );
		$exists[5] = ( $exists[2] && $exists[3] );

		// phpcs:disable WordPress.WP.I18n.LowLevelTranslationFunction,WordPress.WP.I18n.NonSingularStringLiteralText
		$zonen[] = array(
			'continent'   => ( $exists[0] ? $zone[0] : '' ),
			'city'        => ( $exists[1] ? $zone[1] : '' ),
			'subcity'     => ( $exists[2] ? $zone[2] : '' ),
			't_continent' => ( $exists[3] ? translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
			't_city'      => ( $exists[4] ? translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
			't_subcity'   => ( $exists[5] ? translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' ),
		);
		// phpcs:enable
	}
	usort( $zonen, '_wp_timezone_choice_usort_callback' );

	if ( ! empty( $r['allow_empty'] ) ) {
		$structure[] = '<option selected="selected" value="">' . esc_html( $none_text ) . '</option>';
	}

	foreach ( $zonen as $key => $zone ) {
		// Build value in an array to join later.
		$value = array( $zone['continent'] );

		if ( empty( $zone['city'] ) ) {
			// It's at the continent level (generally won't happen).
			$display = $zone['t_continent'];
		} else {
			// It's inside a continent group.

			// Continent optgroup.
			if ( ! isset( $zonen[ $key - 1 ] ) || $zonen[ $key - 1 ]['continent'] !== $zone['continent'] ) {
				$label       = $zone['t_continent'];
				$structure[] = '<optgroup label="' . esc_attr( $label ) . '">';
			}

			// Add the city to the value.
			$value[] = $zone['city'];

			$display = $zone['t_city'];
			if ( ! empty( $zone['subcity'] ) ) {
				// Add the subcity to the value.
				$value[]  = $zone['subcity'];
				$display .= ' - ' . $zone['t_subcity'];
			}
		}

		// Build the value.
		$value    = join( '/', $value );
		$selected = '';
		if ( $value === $selected_zone ) {
			$selected = 'selected="selected" ';
		}
		$structure[] = '<option ' . $selected . 'value="' . esc_attr( $value ) . '">' . esc_html( $display ) . '</option>';

		// Close continent optgroup.
		if ( ! empty( $zone['city'] ) && ( ! isset( $zonen[ $key + 1 ] ) || ( isset( $zonen[ $key + 1 ] ) && $zonen[ $key + 1 ]['continent'] !== $zone['continent'] ) ) ) {
			$structure[] = '</optgroup>';
		}
	}

	// Do UTC.
	$structure[] = '<optgroup label="' . esc_attr__( 'UTC' ) . '">';
	$selected    = '';
	if ( 'UTC' === $selected_zone ) {
		$selected = 'selected="selected" ';
	}
	$structure[] = '<option ' . $selected . 'value="' . esc_attr( 'UTC' ) . '">' . __( 'UTC' ) . '</option>';
	$structure[] = '</optgroup>';

	// Do manual UTC offsets.
	$structure[]  = '<optgroup label="' . esc_attr__( 'Manual Offsets' ) . '">';
	$offset_range = array(
		-12,
		-11.5,
		-11,
		-10.5,
		-10,
		-9.5,
		-9,
		-8.5,
		-8,
		-7.5,
		-7,
		-6.5,
		-6,
		-5.5,
		-5,
		-4.5,
		-4,
		-3.5,
		-3,
		-2.5,
		-2,
		-1.5,
		-1,
		-0.5,
		0,
		0.5,
		1,
		1.5,
		2,
		2.5,
		3,
		3.5,
		4,
		4.5,
		5,
		5.5,
		5.75,
		6,
		6.5,
		7,
		7.5,
		8,
		8.5,
		8.75,
		9,
		9.5,
		10,
		10.5,
		11,
		11.5,
		12,
		12.75,
		13,
		13.75,
		14,
	);
	foreach ( $offset_range as $offset ) {
		if ( 0 <= $offset ) {
			$offset_name = '+' . $offset;
		} else {
			$offset_name = (string) $offset;
		}

		$offset_value = $offset_name;
		$offset_name  = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $offset_name );
		$offset_name  = 'UTC' . $offset_name;
		$offset_value = 'UTC' . $offset_value;
		$selected     = '';
		if ( $offset_value === $selected_zone ) {
			$selected = 'selected="selected" ';
		}
		$structure[] = '<option ' . $selected . 'value="' . esc_attr( $offset_value ) . '">' . esc_html( $offset_name ) . '</option>';

	}
	$structure[] = '</optgroup>';
	$structure[] = '</select>';

	echo join( "\n", $structure );
}
