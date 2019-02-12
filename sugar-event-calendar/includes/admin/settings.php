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
		esc_html__( 'Settings', 'sugar-calendar' ),
		'manage_options',
		'sc-settings',
		'Sugar_Calendar\\Admin\\Settings\\page'
	);
}

/**
 * Get the license key
 *
 * @since 2.0.0
 *
 * @return string
 */
function get_license_key() {
	return trim( get_option( 'sc_license_key' ) );
}

/**
 * Get the license status
 *
 * @since 2.0.0
 *
 * @return string
 */
function get_license_status() {
	return trim( get_option( 'sc_license_status' ) );
}

/**
 * Remotely verify a license key.
 *
 * @since 2.0.0
 *
 * @param array $args
 *
 * @return object
 */
function verify_license( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'body'      => array(),
		'timeout'   => 15,
		'sslverify' => false
	) );

	// Return
	return wp_remote_post( 'https://sugarcalendar.com/', $r );
}

/**
 * Return array of API parameters
 *
 * @since 2.0.0
 *
 * @param array $args
 *
 * @return array
 */
function get_api_params( $args = array() ) {
	return wp_parse_args( $args, array(
		'edd_action'=> 'check_license',
		'license' 	=> get_license_key(),
		'item_name' => urlencode( 'Sugar Calendar' ),
		'url'       => home_url()
	) );
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
		$retval = array(
			'main' => array(
				'name' => esc_html__( 'Settings', 'sugar-calendar' ),
				'url'  => admin_url( 'admin.php?page=sc-settings' ),
				'func' => 'Sugar_Calendar\\Admin\\Settings\\license_section'
			)
		);
	}

	return $retval;
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
		$retval = array(
			'main' => array(
				'main' => array(
					'name' => esc_html__( 'General', 'sugar-calendar' ),
					'url'  => admin_url( 'admin.php?page=sc-settings' ),
					'func' => 'Sugar_Calendar\\Admin\\Settings\\license_section'
				),
				'display' => array(
					'name' => esc_html__( 'Display', 'sugar-calendar' ),
					'url'  => admin_url( 'admin.php?page=sc-settings' ),
					'func' => 'Sugar_Calendar\\Admin\\Settings\\datetime_section'
				),
				'misc' => array(
					'name' => esc_html__( 'Updates', 'sugar-calendar' ),
					'url'  => admin_url( 'admin.php?page=sc-settings' ),
					'func' => 'Sugar_Calendar\\Admin\\Settings\\updates_section'
				)
			)
		);
	}

	// Maybe return a secific set of subsection
	if ( isset( $retval[ $section ] ) ) {
		return $retval[ $section ];
	}

	// Return all subsections
	return $retval;
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
		'main' => array(
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
				echo esc_html( $tab['name'] );
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
 * @param array  $sections
 */
function secondary_nav( $section = 'main', $subsection = 'main' ) {

	// Get all sections
	$sections = get_subsections( $section );

	// Default links array
	$links = array();

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

		// No main tab
		if ( 'main' === $section ) {
			array_push( $removables, 'section' );
			unset( $args['section'] );
		}

		// No main tab
		if ( 'main' === $subsection_id ) {
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
		$links[ $subsection_id ] = '<li class="' . esc_attr( $class ) . '"><a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . esc_html( $sub['name'] ) . '</a><li>';
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

	// Get the section
	$section = ! empty( $_GET['section'] )
		? sanitize_key( $_GET['section'] )
		: 'main';

	$subsection = ! empty( $_GET['subsection'] )
		? sanitize_key( $_GET['subsection'] )
		: 'main';

	if ( ! empty( $_GET['settings-updated'] ) && ( 'true' === $_GET['settings-updated'] ) ) : ?>

		<div class="notice updated fade is-dismissible">
			<p><strong><?php esc_html_e( 'Settings updated.', 'sugar-calendar' ); ?></strong></p>
		</div>

	<?php elseif ( ! empty( $_GET['error'] ) ) : ?>

		<div class="notice error fade is-dismissible">
			<p><strong><?php printf( esc_html__( 'License key not activated. Error: %s', 'sugar-calendar' ), sanitize_key( $_GET['error'] ) ); ?></strong></p>
		</div>

	<?php endif; ?>

	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Settings', 'sugar-calendar' ); ?></h1>

		<?php primary_nav( $section ); ?>

		<?php secondary_nav( $section, $subsection ); ?>

		<hr class="wp-header-end">

		<form method="post" action="options.php">

			<?php section( $section, $subsection ); ?>

			<?php submit_button(); ?>

			<?php settings_fields( "sc_{$section}_{$subsection}" ); ?>

		</form>
	</div>

<?php
}

/**
 * Creates our settings in the options table
 *
 * @since 1.0.0
 */
function register_options() {
	register_setting( 'sc_main_main',    'sc_license_key', 'sugar_calendar_sanitize_license' );
	register_setting( 'sc_main_display', 'sc_start_of_week' );
	register_setting( 'sc_main_display', 'sc_date_format' );
	register_setting( 'sc_main_display', 'sc_time_format' );
	register_setting( 'sc_main_misc',    'sc_beta_opt_in' );
}

/** License *******************************************************************/

/**
 * Callback for setting up the sc_license_key setting
 *
 * @since 2.0.0
 * @param $new
 *
 * @return string
 */
function sanitize_license( $new = '' ) {
	$old = get_license_key();

	// New license has been entered, so must reactivate
	if ( ! empty( $old ) && ( $old != $new ) ) {
		delete_option( 'sc_license_status' );
	}

	return $new;
}

/**
 * License activation check
 *
 * @since 1.0.0
 */
function activate_license() {

	// listen for our activate button to be clicked
	if ( ! isset( $_POST['sc_license_activate'] ) ) {
		return;
	}

	// Run a quick security check
	if ( ! check_admin_referer( 'sc_license_nonce', 'sc_license_nonce' ) ) {
		return;
	}

	// Data to send in our API request
	$api_params = get_api_params( array(
		'edd_action' => 'activate_license'
	) );

	// Call the custom API
	$response = verify_license( array(
		'body' => $api_params
	) );

	// Make sure the response came back okay
	if ( is_wp_error( $response ) ) {
		return;
	}

	// Decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	update_option( 'sc_license_status', $license_data->license );
	delete_transient( 'sc_license_check' );

	if ( ( 'valid' !== $license_data->license ) && ( 'missing' !== $license_data->error ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=sc-settings&error=' . $license_data->error ) );
		exit();
	}
}

/**
 * Deactivate license
 *
 * @since 1.0.0
 */
function deactivate_license() {

	// Listen for our activate button to be clicked
	if ( ! isset( $_POST['sc_license_deactivate'] ) ) {
		return;
	}

	// Run a quick security check
	if ( ! check_admin_referer( 'sc_license_nonce', 'sc_license_nonce' ) ) {
		return;
	}

	// Data to send in our API request
	$api_params = get_api_params( array(
		'edd_action' => 'deactivate_license'
	) );

	// Call the custom API
	$response = verify_license( array(
		'body' => $api_params
	) );

	// Make sure the response came back okay
	if ( is_wp_error( $response ) ) {
		return;
	}

	// Decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( 'deactivated' === $license_data->license ) {
		delete_option( 'sc_license_status' );
		delete_transient( 'sc_license_check' );
	}
}

/**
 * Check if license is valid
 *
 * @since 1.0.0
 * @return void|string
 */
function check_license() {

	// Don't fire when saving settings
	if ( ! empty( $_POST['sc_license_activate'] ) || ! empty( $_POST['sc_license_deactivate'] ) ) {
		return;
	}

	$status = get_transient( 'sc_license_check' );

	// Run the license check a maximum of once per day
	if ( false !== $status ) {
		return $status;
	}

	// Data to send in our API request
	$api_params = get_api_params( array(
		'edd_action' => 'check_license'
	) );

	// Call the custom API
	$response = verify_license( array(
		'body' => $api_params
	) );

	// make sure the response came back okay
	if ( is_wp_error( $response ) ) {
		return;
	}

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	update_option( 'sc_license_status', $license_data->license );

	set_transient( 'sc_license_check', $license_data->license, DAY_IN_SECONDS );

	return $license_data->license;
}

/**
 * Show messages if license is expired or not valid
 *
 * @since 1.0.0
 */
function show_license_notice() {

	// Get the license status
	$status = check_license();

	if ( 'expired' === $status ) {
		echo '<div class="error info"><p>' . __( 'Your license key for Sugar Calendar has expired. Please renew your license to re-enable automatic updates.', 'sugar-calendar' ) . '</p></div>';

	} elseif ( sugar_calendar_is_admin() && ( 'valid' !== $status ) ) {
		$url = admin_url( 'admin.php?page=sc-settings' );

		echo '<div class="notice notice-info"><p>' . sprintf( __( 'Please <a href="%s">enter and activate</a> your <strong>Sugar Calendar</strong> license key to enable automatic updates.', 'sugar-calendar' ), esc_url( $url ) ) . '</p></div>';
	}
}

/** Sections ******************************************************************/

/**
 * Output the license settings section
 *
 * @since 2.0.0
 */
function license_section() {

	// License settings
	$license = get_license_key();
	$status  = get_license_status();

	if ( ! empty( $license ) && ( 'invalid' === $status ) ) : ?>
		<div class="error fade"><p class="align-left"><?php esc_html_e( 'The supplied license key is invalid.', 'sugar-calendar' ); ?></p></div>
	<?php endif;

	if ( ( false !== $license ) && empty( $status ) ) : ?>
		<div class="updated fade"><p class="align-left"><?php esc_html_e( 'Please activate your license key.', 'sugar-calendar' ); ?></p></div>
	<?php endif; ?>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_license_key"><?php esc_html_e( 'License Key' ); ?></label>
				</th>
				<td>
					<input id="sc_license_key" name="sc_license_key" type="text" class="code" value="<?php esc_attr_e( $license ); ?>" /><br/>
					<p class="description"><?php esc_html_e( 'Please enter and activate your license key in order to get automatic updates and support', 'sugar-calendar' ); ?></p>
				</td>
			</tr>

			<?php if ( ! empty( $license ) ) : ?>

				<tr valign="top">
					<th scope="row" valign="top">
						<label><?php esc_html_e( 'Activate License', 'sugar-calendar' ); ?></label>
					</th>
					<td>
						<?php if ( $status !== false && $status == 'valid' ) { ?>
							<span style="color:green;"><?php esc_html_e( 'active' ); ?></span>
							<?php wp_nonce_field( 'sc_license_nonce', 'sc_license_nonce' ); ?>
							<input type="submit" class="button-secondary" name="sc_license_deactivate" value="<?php esc_attr_e( 'Deactivate License', 'sugar-calendar' ); ?>"/>
						<?php } else { ?>
							<?php wp_nonce_field( 'sc_license_nonce', 'sc_license_nonce' ); ?>
							<input type="submit" class="button-secondary" name="sc_license_activate" value="<?php esc_attr_e( 'Activate License', 'sugar-calendar' ); ?>"/>
						<?php } ?>
					</td>
				</tr>

			<?php endif; ?>

		</tbody>
	</table>

<?php
}

/**
 * Output the admin settings datetime section
 *
 * @since 2.0.0
 */
function datetime_section() {
	$start_of_week  = sc_get_week_start_day();
	$sc_date_format = sc_get_date_format();
	$sc_time_format = sc_get_time_format(); ?>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_start_of_week"><?php esc_html_e( 'Start of Week' ); ?></label>
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
					<label for="sc_date_format"><?php esc_html_e( 'Date Format' ); ?></label>
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
					<label for="sc_time_format"><?php esc_html_e( 'Time Format' ); ?></label>
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
 * Output the admin settings updates section
 *
 * @since 2.0.0
 */
function updates_section() {
	$beta_opt_in = (bool) get_option( 'sc_beta_opt_in' ); ?>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_beta_opt_in"><?php esc_html_e( 'Software Updates', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<label class="sc-toggle">
						<input type="checkbox" name="sc_beta_opt_in" id="sc_beta_opt_in" value="1" <?php checked( true, $beta_opt_in ); ?>>
						<span class="label"><?php esc_html_e( 'Get Beta Versions', 'sugar-calendar' ) ?></span>
					</label>
					<p class="description">
						<?php esc_html_e( 'Receive update notifications about beta versions instead.', 'sugar-calendar' ) ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>

<?php
}