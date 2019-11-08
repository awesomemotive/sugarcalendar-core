<?php
/**
 * Sugar Calendar Admin Settings Screen
 *
 * @since 2.0.0
 */
namespace Sugar_Calendar\Admin\Settings;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
		'Sugar_Calendar\\Admin\\Settings\\page'
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

	// Start a buffer
	ob_start() ?>

	<div class="clear"></div>
	<h2 class="nav-tab-wrapper sc-nav-tab-wrapper sc-tab-clear"><?php

		// Loop through tabs, and output links
		foreach ( $tabs as $tab_id => $tab ) :

			// Setup the class to denote a tab is active
			$active_class = ( $section === $tab_id )
				? 'nav-tab-active'
				: '';

			?><a href="<?php echo esc_url( $tab['url'] ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>"><?php
				echo $tab['name']; // May contain HTML
			?></a><?php

		endforeach;

	?></h2>

	<?php

	// Output the current buffer
	echo ob_get_clean();
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
	if ( function_exists( $func ) ) {
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
			if ( true === $maybe_display_sidebar ) {
				display_sidebar();
			}
			?>

		</div>
	</div>

<?php
}

/**
 * Registers our settings in the various options sections
 *
 * @since 1.0.0
 */
function register_settings() {

	// Date/Time Formatting
	register_setting( 'sc_main_display', 'sc_number_of_events' );
	register_setting( 'sc_main_display', 'sc_start_of_week' );
	register_setting( 'sc_main_display', 'sc_date_format' );
	register_setting( 'sc_main_display', 'sc_time_format' );

	do_action( 'sugar_calendar_register_settings' );
}

/** Sections ******************************************************************/

/**
 * Output the admin settings datetime section
 *
 * @since 2.0.0
 */
function display_subsection() {
	$events_max_num = sc_get_number_of_events();
	$start_of_week  = sc_get_week_start_day();
	$sc_date_format = sc_get_date_format();
	$sc_time_format = sc_get_time_format(); ?>

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
					<select name="sc_start_of_week" id="sc_start_of_week">
						<option value="0" <?php selected( $start_of_week, 0); ?>><?php esc_html_e( 'Sunday', 'sugar-calendar' ); ?></option>
						<option value="1" <?php selected( $start_of_week, 1); ?>><?php esc_html_e( 'Monday', 'sugar-calendar' ); ?></option>
						<option value="2" <?php selected( $start_of_week, 2); ?>><?php esc_html_e( 'Tuesday', 'sugar-calendar' ); ?></option>
						<option value="3" <?php selected( $start_of_week, 3); ?>><?php esc_html_e( 'Wednesday', 'sugar-calendar' ); ?></option>
						<option value="4" <?php selected( $start_of_week, 4); ?>><?php esc_html_e( 'Thursday', 'sugar-calendar' ); ?></option>
						<option value="5" <?php selected( $start_of_week, 5); ?>><?php esc_html_e( 'Friday', 'sugar-calendar' ); ?></option>
						<option value="6" <?php selected( $start_of_week, 6); ?>><?php esc_html_e( 'Saturday', 'sugar-calendar' ); ?></option>
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
					<label title="F j, Y"><input type="radio" name="sc_date_format" id="sc_date_format" value="F j, Y" <?php checked('F j, Y', $sc_date_format); ?>> <span><?php echo date( 'F j, Y' ); ?></span></label><br/>
					<label title="d/m/Y"><input type="radio" name="sc_date_format" value="d/m/Y" <?php checked('d/m/Y', $sc_date_format); ?>> <span><?php echo date( 'd/m/Y' ); ?></span></label><br/>
					<label title="m/d/Y"><input type="radio" name="sc_date_format" value="m/d/Y" <?php checked('m/d/Y', $sc_date_format); ?>> <span><?php echo date( 'm/d/Y' ); ?></span></label><br/>
					<label title="Y-m-d"><input type="radio" name="sc_date_format" value="Y-m-d" <?php checked('Y-m-d', $sc_date_format); ?>> <span><?php echo date( 'Y-m-d' ); ?></span></label><br/>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_time_format"><?php esc_html_e( 'Time Format', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<label title="g:i a"><input type="radio" name="sc_time_format" id="sc_time_format" value="g:i a" <?php checked('g:i a', $sc_time_format); ?>> <span><?php echo date( 'g:i a' ); ?></span></label><br/>
					<label title="g:i A"><input type="radio" name="sc_time_format" value="g:i A" <?php checked('g:i A', $sc_time_format); ?>> <span><?php echo date( 'g:i A' ); ?></span></label><br/>
					<label title="H:i"><input type="radio" name="sc_time_format" value="H:i" <?php checked('H:i', $sc_time_format); ?>> <span><?php echo date( 'H:i' ); ?></span></label><br/>
				</td>
			</tr>
		</tbody>
	</table>

<?php
}

/**
 * Check to see if we should be displaying a sidebar
 *
 * @since 2.0.10
 */
function maybe_display_sidebar() {

	// Set the date/time range based on UTC
	$start = strtotime( '2019-11-29 06:00:00' );
	$end   = strtotime( '2019-12-07 05:59:59' );
	$now   = time();

	// Only display sidebar if the page is loaded within the date range
	if ( ( $now ) > $start && ( $now < $end ) ) {
		return true;
	}

	return false;
}

/**
 * Output the admin settings sidebar
 *
 * @since 2.0.10
 */
function display_sidebar() {
	$coupon_code = 'BFCM2019';
	$utm_params  = '?utm_source=sc-settings&utm_medium=wp-admin&utm_campaign=bfcm2019&utm_content=sidebar-promo';
	?>

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
				<input id="sc-coupon-code" type="text" value="<?php echo $coupon_code; ?>" readonly>
				<p class="sc-coupon-note"><?php _e( 'Sale ends 23:59 PM December 6th CST. Save 25% on <a href="https://sandhillsdev.com/projects/" target="_blank">our other plugins</a>.', 'sugar-calendar' ); ?></p>
			</div>

			<div class="sc-sidebar-footer-section">
				<a class="sc-cta-button" href="https://sugarcalendar.com/pricing/<?php echo $utm_params; ?>" target="_blank"><?php _e( 'Upgrade Now!', 'sugar-calendar' ); ?></a>
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