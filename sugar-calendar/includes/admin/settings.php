<?php
/**
 * Sugar Calendar Admin Settings Screen
 *
 * @since 2.0.0
 */
namespace Sugar_Calendar\Admin\Settings;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Common\Editor as Editor;

/**
 * Admin settings for the calendar
 *
 * @since 2.0.0
 */
function menu() {
	add_submenu_page(
		'sugar-calendar',
		esc_html__( 'Settings', 'sugar-calendar' ),
		append_submenu_bubble( esc_html__( 'Settings', 'sugar-calendar' ) ),
		'manage_options',
		'sc-settings',
		'Sugar_Calendar\\Admin\\Settings\\page',
		99
	);
}

/**
 * Get the Settings screen ID
 *
 * @since 2.0.2
 *
 * @return string
 */
function get_screen_id() {
	return 'calendar_page_sc-settings';
}

/**
 * Is the current admin screen a settings page?
 *
 * @since 2.0.2
 *
 * @return bool
 */
function in() {
	return ( get_screen_id() === get_current_screen()->id );
}

/**
 * Get the number of notifications to show inside the settings bubble.
 *
 * @since 2.0.0
 * @return int
 */
function get_bubble_count() {
	return apply_filters( 'sugar_calendar_admin_get_bubble_count', 0 );
}

/**
 * Get the HTML used to display a notification-style bubble.
 *
 * @since 2.0.0
 *
 * @param int $count
 * @return string
 */
function get_bubble_html( $count = 0 ) {
	return ' <span class="awaiting-mod sc-settings-bubble count-' . absint( $count ) . '"><span class="pending-count">' . number_format_i18n( $count ) . '</span></span>';
}

/**
 * Get the HTML used to display a bubble in the "Settings" submenu.
 *
 * @since 2.0.3
 */
function append_submenu_bubble( $html = '' ) {

	// Default return value
	$retval = $html;

	// Get the bubble count
	$count = get_bubble_count();

	// Append the count to the string
	if ( ! empty( $count ) ) {
		$suffix = get_bubble_html( $count );
		$retval = "{$html}{$suffix}";
	}

	// Return the original HTML, possibly with a bubble behind it
	return $retval;
}

/**
 * Return array of settings sections
 *
 * @since 2.0.0
 *
 * @return array
 */
function get_sections() {
	static $retval = null;

	// Store statically to avoid thrashing the gettext API
	if ( null === $retval ) {

		// Setup
		$retval = array(
			'main' => array(
				'id'   => 'main',
				'name' => esc_html__( 'Settings', 'sugar-calendar' ),
				'url'  => admin_url( 'admin.php?page=sc-settings' ),
				'func' => 'Sugar_Calendar\\Admin\\Settings\\display_subsection'
			)
		);

		// Filter
		$retval = apply_filters( 'sugar_calendar_settings_sections', $retval );
	}

	// Return
	return $retval;
}

/**
 * Return the first/main section ID.
 *
 * @since 2.0.3
 *
 * @return string
 */
function get_main_section_id() {
	return key( get_sections() );
}

/**
 * Return array of settings sub-sections
 *
 * @since 2.0.0
 *
 * @return array
 */
function get_subsections( $section = '' ) {
	static $retval = null;

	// Store statically to avoid thrashing the gettext API
	if ( null === $retval ) {

		// Setup
		$retval = array(
			get_main_section_id() => array(
				'display' => array(
					'id'   => 'display',
					'name' => esc_html__( 'Display', 'sugar-calendar' ),
					'url'  => admin_url( 'admin.php?page=sc-settings' ),
					'func' => 'Sugar_Calendar\\Admin\\Settings\\display_subsection'
				),
				'editing' => array(
					'id'   => 'editing',
					'name' => esc_html__( 'Editing', 'sugar-calendar' ),
					'url'  => admin_url( 'admin.php?page=sc-settings' ),
					'func' => 'Sugar_Calendar\\Admin\\Settings\\editing_subsection'
				),
				'timezones' => array(
					'id'   => 'timezones',
					'name' => esc_html__( 'Time Zones', 'sugar-calendar' ),
					'url'  => admin_url( 'admin.php?page=sc-settings' ),
					'func' => 'Sugar_Calendar\\Admin\\Settings\\time_zone_subsection'
				)
			)
		);

		// Filter
		$retval = apply_filters( 'sugar_calendar_settings_subsections', $retval, $section );
	}

	// Maybe return a secific set of subsection
	if ( ! empty( $section ) && isset( $retval[ $section ] ) ) {
		return $retval[ $section ];
	}

	// Return all subsections
	return $retval;
}

/**
 * Return the first/main subsection ID.
 *
 * @since 2.0.3
 *
 * @param string $section
 * @return string
 */
function get_main_subsection_id( $section = '' ) {
	return key( get_subsections( $section ) );
}

/**
 * Return a subsection
 *
 * @since 2.0.0
 *
 * @param string $section
 * @param string $subsection
 * @return array
 */
function get_subsection( $section = 'main', $subsection = '' ) {
	$subs = get_subsections( $section );

	// Default
	$default = array(
		get_main_section_id() => array(
			'name' => esc_html__( 'General', 'sugar-calendar' )
		)
	);

	// Return the subsection
	return isset( $subs[ $subsection ] )
		? $subs[ $subsection ]
		: $default;
}

/**
 * Get the Settings navigation tabs
 *
 * @since 2.0.0
 */
function primary_nav( $section = 'sc-settings' ) {

	// Get sections
	$tabs = get_sections();

	// Get the nav
	$nav  = \Sugar_Calendar\Admin\Nav\get( $tabs, $section );

	// Output the nav
	echo $nav;
}

/**
 * Output the secondary options page navigation
 *
 * @since 2.0.0
 *
 * @param string $section
 * @param array  $subsection
 */
function secondary_nav( $section = 'main', $subsection = 'main' ) {

	// Get all sections
	$sections = get_subsections( $section );

	// Default links array
	$links = array();

	// Fudge if no main subsection exists
	$main_section    = get_main_section_id();
	$main_subsection = get_main_subsection_id( $section );

	// Maybe fallback to main
	if ( ! isset( $sections[ $subsection ] ) ) {
		$subsection = $main_subsection;
	}

	// Loop through sections
	foreach ( $sections as $subsection_id => $sub ) {

		// Setup args
		$args = array(
			'page'       => 'sc-settings',
			'section'    => $section,
			'subsection' => $subsection_id
		);

		// Setup removable args
		$removables = array(
			'settings-updated',
			'error'
		);

		// No main section in URL
		if ( $main_section === $section ) {
			array_push( $removables, 'section' );
			unset( $args['section'] );
		}

		// No main subsection in URL
		if ( $main_subsection === $subsection_id ) {
			array_push( $removables, 'subsection' );
			unset( $args['subsection'] );
		}

		// Tab & Section
		$tab_url = add_query_arg( $args );

		// Settings not updated
		$tab_url = remove_query_arg( $removables, $tab_url );

		// Class for link
		$class = ( $subsection === $subsection_id )
			? 'current'
			: '';

		// Add to links array
		$links[ $subsection_id ] = '<li class="' . esc_attr( $class ) . '"><a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . $sub['name'] . '</a><li>';
	} ?>

	<ul class="subsubsub sc-settings-sub-nav">
		<?php echo implode( '', $links ); ?>
	</ul>

	<?php
}

/**
 * Output a settings section
 *
 * Kinda rough for now, but fine enough
 *
 * @since 2.0.0
 *
 * @param string $section
 */
function section( $section = '', $subsection = 'main' ) {

	// Subsection func
	$subsection = get_subsection( $section, $subsection );
	$func       = isset( $subsection['func'] )
		? $subsection['func']
		: '';

	// Maybe call the function
	if ( is_callable( $func ) || function_exists( $func ) ) {
		call_user_func( $func );
	}
}

/**
 * Callback for add_submenu_page
 *
 * @since 1.0.0
 */
function page() {

	// Get the section & subsection
	$section = ! empty( $_GET['section'] )
		? sanitize_key( $_GET['section'] )
		: get_main_section_id();

	$subsection = ! empty( $_GET['subsection'] )
		? sanitize_key( $_GET['subsection'] )
		: get_main_subsection_id( $section );

	// Find out if we're displaying a sidebar
	$maybe_display_sidebar = maybe_display_sidebar();
	$wrapper_class         = ( true === $maybe_display_sidebar )
		? ' sc-has-sidebar'
		: '';

	if ( ! empty( $_GET['settings-updated'] ) && ( 'true' === $_GET['settings-updated'] ) ) : ?>

		<div class="notice updated fade is-dismissible">
			<p><strong><?php esc_html_e( 'Settings updated.', 'sugar-calendar' ); ?></strong></p>
		</div>

	<?php endif; ?>

	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Settings', 'sugar-calendar' ); ?></h1>

		<?php primary_nav( $section ); ?>

		<?php secondary_nav( $section, $subsection ); ?>

		<hr class="wp-header-end">

		<div class="sc-settings-wrap<?php echo esc_attr( $wrapper_class ); ?> wp-clearfix">

			<div class="sc-settings-content">

				<form method="post" action="options.php">

					<?php section( $section, $subsection ); ?>

					<?php submit_button(); ?>

					<?php settings_fields( "sc_{$section}_{$subsection}" ); ?>

				</form>

			</div>

			<?php

			if ( true === $maybe_display_sidebar ) :
				display_sidebar();
			endif;

			?>

		</div>
	</div>

<?php
}

/** Sections ******************************************************************/

/**
 * Output the admin settings datetime section
 *
 * @since 2.0.0
 */
function display_subsection() {
	global $wp_locale;

	// Get settings
	$events_max_num = sc_get_number_of_events();
	$start_of_week  = sc_get_week_start_day();
	$date_format    = sc_get_date_format();
	$time_format    = sc_get_time_format();
	$color_style    = sc_get_day_color_style();

	/**
	 * Filters the default date formats.
	 *
	 * @param string[] $default_date_formats Array of default date formats.
	 */
	$date_formats = array_unique( apply_filters( 'date_formats', array(
		esc_html__( 'F j, Y', 'sugar-calendar' ),
		'Y-m-d',
		'm/d/Y',
		'd/m/Y',
		'jS F, Y'
	) ) );

	// Is custom date checked?
	$custom_date_checked = ! in_array( $date_format, $date_formats, true );

	/**
	 * Filters the default time formats.
	 *
	 * @param string[] $default_time_formats Array of default time formats.
	 */
	$time_formats = array_unique( apply_filters( 'time_formats', array(
		esc_html__( 'g:i a', 'sugar-calendar' ),
		'g:i A',
		'H:i'
	) ) );

	/**
	 * Filters the default day color styles
	 */
	$color_styles = array_unique( apply_filters( 'sc_day_color_styles', array(
		'none'  => esc_html__( 'None',  'sugar-calendar' ),
		'each'  => esc_html__( 'Each',  'sugar-calendar' ),
		'first' => esc_html__( 'First', 'sugar-calendar' ),
		'blend' => esc_html__( 'Blend', 'sugar-calendar' )
	) ) );

	// Is custom time checked?
	$custom_time_checked = ! in_array( $time_format, $time_formats, true );

	// Format and translate
	$timezone        = sugar_calendar_get_timezone();
	$looks_like_date = sugar_calendar_format_date_i18n( $date_format, null, $timezone );
	$looks_like_time = sugar_calendar_format_date_i18n( $time_format, null, $timezone ); ?>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_number_of_events"><?php esc_html_e( 'Maximum Events', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<input type="number" inputMode="numeric" step="1" min="0" max="999" class="code" name="sc_number_of_events" id="sc_number_of_events" maxlength="3" value="<?php echo absint( $events_max_num ); ?>">
					<p class="description">
						<?php _e( 'Number of events to include in any theme-side calendar. Default <code>30</code>. Use <code>0</code> for no limit.', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_start_of_week"><?php esc_html_e( 'Start of Week', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<select name="sc_start_of_week" id="sc_start_of_week" class="sc-select-chosen">
						<option value="0" <?php selected( $start_of_week, 0 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 0 ) ); ?></option>
						<option value="1" <?php selected( $start_of_week, 1 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 1 ) ); ?></option>
						<option value="2" <?php selected( $start_of_week, 2 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 2 ) ); ?></option>
						<option value="3" <?php selected( $start_of_week, 3 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 3 ) ); ?></option>
						<option value="4" <?php selected( $start_of_week, 4 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 4 ) ); ?></option>
						<option value="5" <?php selected( $start_of_week, 5 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 5 ) ); ?></option>
						<option value="6" <?php selected( $start_of_week, 6 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 6 ) ); ?></option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select the first day of the week', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_date_format"><?php esc_html_e( 'Date Format', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span><?php esc_html_e( 'Date Format', 'sugar-calendar' ); ?></span>
						</legend>

						<?php foreach ( $date_formats as $key => $format ) :

							// Radio ID
							$id = ( 0 === $key )
								? ' id="sc_date_format"'
								: '';

							// Checked?
							$checked = checked( $format, $date_format, false );

							// Format and translate
							$date = sugar_calendar_format_date_i18n( $format, null, $timezone ); ?>

							<label>
								<input type="radio" <?php echo $id; ?> name="sc_date_format" value="<?php echo esc_attr( $format ); ?>"<?php echo $checked; ?> />
								<span class="date-time-text format-i18n"><?php echo esc_html( $date ); ?></span>
								<code><?php echo esc_html( $format ); ?></code>
							</label>
							<br />

						<?php endforeach; ?>

						<label>
							<input type="radio" name="sc_date_format" id="sc_date_format_custom_radio" value="<?php echo esc_attr( $date_format ); ?>" <?php checked( $custom_date_checked ); ?> />
							<span class="date-time-text date-time-custom-text"><?php esc_html_e( 'Custom:', 'sugar-calendar' ); ?>
								<span class="screen-reader-text"><?php esc_html_e( 'enter a custom date format in the following field', 'sugar-calendar' ); ?></span>
							</span>
						</label>

						<label for="sc_date_format_custom" class="screen-reader-text"><?php esc_html_e( 'Custom date format:', 'sugar-calendar' ); ?></label>
						<input type="text" name="sc_date_format_custom" id="sc_date_format_custom" value="<?php echo esc_attr( $date_format ); ?>" class="small-text" />
						<a href="#" class="hide-if-no-js screen-options sc-date-help">
							<span class="screen-reader-text"><?php esc_html_e( 'Options', 'sugar-calendar' ); ?></span>
							<span aria-hidden="true" class="dashicons dashicons-editor-help"></span>
						</a>

						<br />

						<p class="description">
							<strong><?php esc_html_e( 'Looks Like:', 'sugar-calendar' ); ?></strong>
							<span class="example"><?php echo esc_html( $looks_like_date ); ?></span>
							<span class='spinner'></span>
						</p>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_time_format"><?php esc_html_e( 'Time Format', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span><?php esc_html_e( 'Time Format', 'sugar-calendar' ); ?></span>
						</legend>

						<?php foreach ( $time_formats as $key => $format ) :

							// Radio ID
							$id = ( 0 === $key )
								? ' id="sc_time_format"'
								: '';

							// Checked?
							$checked = checked( $format, $time_format, false );

							// Format and translate
							$time = sugar_calendar_format_date_i18n( $format, null, $timezone ); ?>

							<label>
								<input type="radio" <?php echo $id; ?> name="sc_time_format" value="<?php echo esc_attr( $format ); ?>"<?php echo $checked; ?> />
								<span class="date-time-text format-i18n"><?php echo esc_html( $time );?></span>
								<code><?php echo esc_html( $format ); ?></code>
							</label>
							<br />

						<?php endforeach; ?>

						<label>
							<input type="radio" name="sc_time_format" id="sc_time_format_custom_radio" value="<?php echo esc_attr( $time_format ); ?>" <?php checked( $custom_time_checked ); ?> />
							<span class="date-time-text date-time-custom-text"><?php esc_html_e( 'Custom:', 'sugar-calendar' ); ?>
								<span class="screen-reader-text"><?php esc_html_e( 'enter a custom time format in the following field', 'sugar-calendar' ); ?></span>
							</span>
						</label>

						<label for="sc_time_format_custom" class="screen-reader-text"><?php esc_html_e( 'Custom time format:', 'sugar-calendar' ); ?></label>
						<input type="text" name="sc_time_format_custom" id="sc_time_format_custom" value="<?php echo esc_attr( $time_format ); ?>" class="small-text" />
						<a href="#" class="hide-if-no-js screen-options sc-time-help">
							<span class="screen-reader-text"><?php esc_html_e( 'Options', 'sugar-calendar' ); ?></span>
							<span aria-hidden="true" class="dashicons dashicons-editor-help"></span>
						</a>

						<br />

						<p class="description">
							<strong><?php esc_html_e( 'Looks Like:', 'sugar-calendar' ); ?></strong>
							<span class="example"><?php echo esc_html( $looks_like_time ); ?></span>
							<span class='spinner'></span>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_day_color_style"><?php esc_html_e( 'Calendar Day Colors', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<select name="sc_day_color_style" id="sc_day_color_style" class="sc-select-chosen">
						<?php foreach ( $color_styles as $style => $label ) : ?>

							<option value="<?php echo esc_attr( $style ); ?>" <?php selected( $color_style, $style ); ?>><?php echo esc_html( $label ); ?></option>

						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php _e( 'The theme-side Calendar Color styling strategy.<br>"None" by default (no colors).<br>"Each" uses a single color for each Event link.<br>"First" uses the first color found for the background.<br>"Blend" will use the average of all colors for the background.', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>

<?php
}

/**
 * Output the "Editing" subsection.
 *
 * @since 2.1.0
 */
function editing_subsection() {

	// Get the current editor settings
	$type   = Editor\current();
	$fields = Editor\custom_fields();

	// Get the registered editors
	$editors = Editor\registered();

	// Get the taxonomy & args
	$tax     = get_taxonomy( sugar_calendar_get_calendar_taxonomy_id() );
	$current = sugar_calendar_get_default_calendar();
	$name    = sugar_calendar_get_default_calendar_option_name();
	$args    = array(
		'taxonomy'         => $tax->name,
		'hierarchical'     => $tax->hierarchical,
		'selected'         => $current,
		'name'             => $name,
		'hide_empty'       => false,
		'orderby'          => 'name',
		'class'            => 'sc-select-chosen',
		'show_option_none' => esc_html__( '&mdash; No Default &mdash;', 'sugar-calendar' )
	); ?>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_editor_type"><?php esc_html_e( 'Editor Type', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<select name="sc_editor_type" id="sc_editor_type" class="sc-select-chosen"><?php

					// Loop through editors
					foreach ( $editors as $editor ) :

						?><option value="<?php echo esc_attr( $editor['id'] ); ?>" <?php selected( $type, $editor['id'] ); ?> <?php disabled( $editor['disabled'] ); ?>><?php

							echo esc_html( $editor['label'] );

						?></option><?php

					endforeach;

					?></select>
					<p class="description">
						<?php esc_html_e( 'The interface to use when adding or editing Events.', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_custom_fields"><?php esc_html_e( 'Custom Fields', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="sc_custom_fields" id="sc_custom_fields" value="1" <?php checked( $fields ); ?> />
						<?php esc_html_e( 'Enable Custom Fields', 'sugar-calendar' ); ?>
					</label>
					<p class="description">
						<?php _e( 'Allow developers to extend post types that support <code>events</code>.', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Default Event Calendar', 'sugar-calendar' ); ?></label>
				</th>
				<td><?php

					// Output the dropdown select
					wp_dropdown_categories( $args );

					?><p class="description">
						<?php esc_html_e( 'When adding a new Event, this Calendar will be preselected.', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>

<?php
}

/**
 * Output the "Time Zone" subsection.
 *
 * @since 2.1.0
 */
function time_zone_subsection() {

	// Get the current settings
	$timezone  = get_option( 'sc_timezone',         ''    );
	$tztype    = get_option( 'sc_timezone_type',    'off' );
	$tzconvert = get_option( 'sc_timezone_convert', false );

	// Types
	$types = array(
		'off'    => esc_html__( 'Off',    'sugar-calendar' ),
		'single' => esc_html__( 'Single', 'sugar-calendar' ),
		'multi'  => esc_html__( 'Multi',  'sugar-calendar' )
	); ?>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_timezone_type"><?php esc_html_e( 'Time Zones', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<select name="sc_timezone_type" id="sc_timezone_type" class="sc-select-chosen">
						<?php foreach ( $types as $type => $label ) : ?>

							<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $tztype,  $type ); ?>><?php echo esc_html( $label ); ?></option>

						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php _e( '"Off" by default (Existing time zone data still appears).<br>"Single" allows Events to have one time zone.<br>"Multi" allows Events to have different start & end time zones.<br>"Single" and "Multi" will enable time zones for Calendars.', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_timezone"><?php esc_html_e( 'Default Time Zone', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<?php sugar_calendar_timezone_dropdown( array(
						'current' => $timezone
					) ); ?>
					<p class="description">
						<?php _e( 'When time zones are enabled, new Events will default to this.<br>If you are unsure, leave empty or pick the time zone you are in.', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_timezone_convert"><?php esc_html_e( 'Visitor Conversion', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="sc_timezone_convert" id="sc_timezone_convert" value="1" <?php checked( $tzconvert ); ?> />
						<?php esc_html_e( 'Enable Conversion', 'sugar-calendar' ); ?>
					</label>
					<p class="description">
						<?php _e( 'Attempts to update theme-side Event times according to visitor web browser location.<br>Depends on client-side browser support. May not work for all visitors.', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>

<?php
}

/**
 * Ajax handler for date formatting.
 *
 * @since 2.0.14
 */
function ajax_date_format() {

	// Sanitize
	$date = ! empty( $_POST['sc_date'] )
		? wp_unslash( $_POST['sc_date'] )
		: '';

	// Get format
	$format   = sanitize_option( 'date_format', $date );

	// Get the time zone
	$timezone = sugar_calendar_get_timezone();

	// Format and translate
	$retval   = sugar_calendar_format_date_i18n( $format, null, $timezone );

	// Output
	wp_die( $retval );
}

/**
 * Ajax handler for time formatting.
 *
 * @since 2.0.14
 */
function ajax_time_format() {

	// Sanitize
	$time = ! empty( $_POST['sc_date'] )
		? wp_unslash( $_POST['sc_date'] )
		: '';

	// Get format
	$format   = sanitize_option( 'time_format', $time );

	// Get the time zone
	$timezone = sugar_calendar_get_timezone();

	// Format and translate
	$retval   = sugar_calendar_format_date_i18n( $format, null, $timezone );

	// Output
	wp_die( $retval );
}

/**
 * Return whether or not to display the settings sidebar
 *
 * The contents of this function will change over time to accommodate special
 * promotional offers.
 *
 * @since 2.0.10
 */
function maybe_display_sidebar() {

	// Default return value
	$display = false;

	// Only show for non-standard versions
	if ( ! is_dir( SC_PLUGIN_DIR . 'includes/standard' ) ) {

		// Set the date/time range based on UTC
		$start = strtotime( '2019-11-29 06:00:00' );
		$end   = strtotime( '2019-12-07 05:59:59' );
		$now   = time();

		// Only display sidebar if the page is loaded within the date range
		$display = ( ( $now > $start ) && ( $now < $end ) );
	}

	// Filter & return
	return (bool) apply_filters( 'sugar_calendar_settings_maybe_display_sidebar', $display );
}

/**
 * Output the admin settings sidebar
 *
 * The contents of this function will change over time to accommodate special
 * promotional offers.
 *
 * @since 2.0.10
 */
function display_sidebar() {

	// Code & tracking args
	$coupon_code = 'BFCM2019';
	$utm_args    = array(
		'utm_source'   => 'settings',
		'utm_medium'   => 'wp-admin',
		'utm_campaign' => strtolower( $coupon_code ),
		'utm_content'  => 'sidebar-promo',
	);

	// Get the URL to the promotion
	$url = add_query_arg( $utm_args, 'https://sugarcalendar.com/pricing/' ); ?>

	<div class="sc-settings-sidebar">

		<div class="sc-settings-sidebar-content">

			<div class="sc-sidebar-header-section">
				<img class="sc-bcfm-header" src="<?php echo esc_url( SC_PLUGIN_URL . 'includes/admin/assets/images/bfcm-header.svg' ); ?>">
			</div>

			<div class="sc-sidebar-description-section">
				<p class="sc-sidebar-description"><?php _e( 'Save 25% on all Sugar Calendar purchases <strong>this week</strong>, including renewals and upgrades!', 'sugar-calendar' ); ?></p>
			</div>

			<div class="sc-sidebar-coupon-section">
				<label for="sc-coupon-code"><?php _e( 'Use code at checkout:', 'sugar-calendar' ); ?></label>
				<input id="sc-coupon-code" type="text" value="<?php echo esc_attr( $coupon_code ); ?>" readonly>
				<p class="sc-coupon-note"><?php _e( 'Sale ends 23:59 PM December 6th CST. Save 25% on <a href="https://sandhillsdev.com/projects/" target="_blank">our other plugins</a>.', 'sugar-calendar' ); ?></p>
			</div>

			<div class="sc-sidebar-footer-section">
				<a class="sc-cta-button" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php _e( 'Upgrade Now!', 'sugar-calendar' ); ?></a>
			</div>

		</div>

		<div class="sc-sidebar-logo-section">
			<div class="sc-logo-wrap">
				<img class="sc-logo" src="<?php echo esc_url( SC_PLUGIN_URL . 'includes/admin/assets/images/sugar-calendar-logo-light.svg' ); ?>">
			</div>
		</div>

	</div>

	<?php
}
