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
		append_license_bubble( esc_html__( 'Settings', 'sugar-calendar' ) ),
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
 * Update the license key
 *
 * @since 2.0.0
 *
 * @return string
 */
function update_license_key( $key = '' ) {
	! empty( $key )
		? update_option( 'sc_license_key', $key )
		: delete_option( 'sc_license_key' );
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
 * Update the license status
 *
 * @since 2.0.0
 *
 * @param object $license_data
 */
function update_license_status( $license_data = false ) {
	if ( empty( $license_data->license ) || ( 'empty' === $license_data->license ) ) {
		delete_license_status();
	} else {
		update_option( 'sc_license_status', $license_data->license );
		set_transient( 'sc_license_check', $license_data, DAY_IN_SECONDS );
	}
}

/**
 * Delete the license status
 *
 * @since 2.0.0
 */
function delete_license_status() {
	delete_option( 'sc_license_status' );
	delete_transient( 'sc_license_check' );
}

/**
 * Get the number of notifications to show inside the settings bubble.
 *
 * @since 2.0.0
 * @return int
 */
function get_bubble_count() {

	// Default return value
	$retval = 0;

	// Get the license status
	$license_data = check_license();

	// Bump return value if license is not valid
	if ( empty( $license_data->license ) || ( 'valid' !== $license_data->license ) ) {
		++$retval;
	}

	// Return the bubble count
	return absint( $retval );
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
 * @since 2.0.0
 */
function append_license_bubble( $html = '' ) {

	// Default return value
	$retval = $html;

	// Get the license status
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
		'sslverify' => true
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
		'edd_action' => 'check_license',
		'license' 	 => get_license_key(),
		'item_id'    => 16,
		'url'        => home_url()
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

	return apply_filters( 'sg_settings_sections', $retval );
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
					'name' => append_license_bubble( esc_html__( 'License', 'sugar-calendar' ) ),
					'url'  => admin_url( 'admin.php?page=sc-settings' ),
					'func' => 'Sugar_Calendar\\Admin\\Settings\\license_section'
				),
				'display' => array(
					'name' => esc_html__( 'Display', 'sugar-calendar' ),
					'url'  => admin_url( 'admin.php?page=sc-settings' ),
					'func' => 'Sugar_Calendar\\Admin\\Settings\\datetime_section'
				)
			)
		);
	}

	$retval = apply_filters( 'sugar_calendar_settings_subsections', $retval, $section );

	// Maybe return a secific set of subsection
	if ( ! empty( $section ) && isset( $retval[ $section ] ) ) {
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
		: 'main';

	$subsection = ! empty( $_GET['subsection'] )
		? sanitize_key( $_GET['subsection'] )
		: 'main';

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

		<form method="post" action="options.php">

			<?php section( $section, $subsection ); ?>

			<?php submit_button(); ?>

			<?php settings_fields( "sc_{$section}_{$subsection}" ); ?>

		</form>
	</div>

<?php
}

/**
 * Registers our settings in the various options sections
 *
 * @since 1.0.0
 */
function register_settings() {

	// Main/Main
	register_setting( 'sc_main_main',    'sc_license_key', __NAMESPACE__ . '\\sanitize_license' );

	// Main/Display
	register_setting( 'sc_main_display', 'sc_start_of_week' );
	register_setting( 'sc_main_display', 'sc_date_format' );
	register_setting( 'sc_main_display', 'sc_time_format' );

	// Main/Updates
	register_setting( 'sc_main_updates', 'sc_beta_opt_in' );

	do_action( 'sugar_calendar_register_settings' );
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
	if ( empty( $new ) || ! empty( $old ) && ( $old !== $new ) ) {
		delete_license_status();
	}

	return preg_replace( '/[^a-zA-Z0-9]/', '', $new );
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

	// Update the license status
	update_license_status( $license_data );

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

	// Delete the license status
	if ( 'deactivated' === $license_data->license ) {
		delete_license_status();
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

	// Get the license data
	$license_data = get_transient( 'sc_license_check' );

	// Run the license check a maximum of once per day
	if ( false !== $license_data ) {
		return $license_data;
	}

	// Default return value
	$retval = false;

	// Not an empty license
	if ( get_license_key() ) {

		// Data to send in our API request
		$api_params = get_api_params( array(
			'edd_action' => 'check_license'
		) );

		// Call the custom API
		$response = verify_license( array(
			'body' => $api_params
		) );

		// Make sure the response came back okay
		if ( ! is_wp_error( $response ) ) {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			$retval       = $license_data->license;
		}
	} else {
		$retval = 'empty';
		$license_data = (object) array(
			'license' => $retval
		);
	}

	// Update the license status
	update_license_status( $license_data );

	return $retval;
}

/**
 * AJAX handler for license verification
 *
 * @since 2.0.0
 */
function ajax_verify() {

	// Bail if no license or nonce
	if ( ! isset( $_REQUEST['license'] ) || ! isset( $_REQUEST['nonce'] ) || ! isset( $_REQUEST['method'] ) ) {
		wp_send_json_error();
	}

	// Bail if user cannot manage options
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	// Check the AJAX nonce
	check_ajax_referer( 'sc_license_nonce', 'nonce' );

	// Sanitize the license
	$license = sanitize_text_field( $_REQUEST['license'] );
	$action  = sanitize_key( $_REQUEST['method'] );
	$method  = in_array( $action, array( 'activate', 'check', 'deactivate' ), true )
		? "{$action}_license"
		: 'check_license';

	// Data to send in our API request
	$api_params = get_api_params( array(
		'edd_action' => $method,
		'license'    => $license
	) );

	// Call the custom API
	$response = verify_license( array(
		'body' => $api_params
	) );

	// Make sure the response came back okay
	if ( is_wp_error( $response ) ) {
		wp_send_json_error( $response );
	}

	// Get the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	$feedback     = get_license_feedback( $license_data->license );

	// Deactivate
	if ( in_array( $license_data->license, array( 'deactivated', 'failed' ), true ) ) {
		delete_license_status();

	// Activate or Check
	} else {
		update_license_key( $license );
		update_license_status( $license_data );

		$feedback['message'] = maybe_add_expiration( $license_data, $feedback['message'] );
	}

	// All done
	wp_send_json_success( array(
		'key'      => get_license_key(),
		'feedback' => $feedback
	) );
}

/**
 * Maybe add the expiration date to a valid license feedback message.
 *
 * @since 2.0.0
 *
 * @param object $license_data
 * @param string $feedback
 *
 * @return string
 */
function maybe_add_expiration( $license_data, $feedback ) {

	// Default return value
	$retval = $feedback;

	// Bail if not a valid license
	if ( 'valid' !== $license_data->license ) {
		return $retval;
	}

	// Bail if no expiration
	if ( empty( $license_data->expires ) ) {
		return $retval;
	}

	// Format date/time/text
	$date    = date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires ) );
	$time    = date_i18n( get_option( 'time_format' ), strtotime( $license_data->expires ) );
	$until   = __( ' until: <time datetime="%s">%s (at %s)</time>', 'sugar-calendar' );
	$expires = sprintf( $until, $license_data->expires, $date, $time );
	$retval  = sprintf( $feedback, $expires );

	// Return appended string
	return $retval;
}

/** Sections ******************************************************************/

/**
 * Get license feedback, based on a specific status.
 *
 * @since 2.0.0
 *
 * @staticvar array $retval
 * @param     string $status
 * @return    array
 */
function get_license_feedback( $status = '' ) {
	static $retval = array();

	// Stash array in local static var to avoid thrashing the gettext API
	if ( empty( $retval ) ) {
		$retval = array(

			// Empty
			'empty' => array(
				'id'      => 'empty',
				'message' => esc_html__( 'Please enter a valid license key.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Empty', 'sugar-calendar' ),
				'class'   => 'empty'
			),

			// Valid
			'valid' => array(
				'id'      => 'valid',
				'message' => esc_html__( 'This license key is valid%s.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Valid', 'sugar-calendar' ),
				'class'   => 'valid'
			),

			// Expired
			'expired' => array(
				'id'      => 'expired',
				'message' => esc_html__( 'This license key is expired', 'sugar-calendar' ),
				'text'    => esc_html__( 'Expired', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Disabled
			'disabled' => array(
				'id'      => 'disabled',
				'message' => esc_html__( 'This license key is disabled.', 'sugar-calendar' ),
				'text'    => esc_html__( 'Disabled', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Revoked
			'revoked' => array(
				'id'      => 'revoked',
				'message' => esc_html__( 'This license key is disabled.', 'sugar-calendar' ),
				'text'    => esc_html__( 'Revoked', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Invalid
			'invalid' => array(
				'id'      => 'invalid',
				'message' => esc_html__( 'This license key is not valid.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Invalid', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Inactive
			'inactive' => array(
				'id'      => 'site_inactive',
				'message' => esc_html__( 'This license key is saved but has not been verified.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Site Inactive', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Deactivated
			'deactivated' => array(
				'id'      => 'deactivated',
				'message' => esc_html__( 'This license key is saved but has not been verified.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Site Inactive', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Failed
			'failed' => array(
				'id'      => 'failed',
				'message' => esc_html__( 'This license key could not be deactivated.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Failed', 'sugar-calendar' ),
				'class'   => 'empty'
			),

			// Inactive
			'site_inactive' => array(
				'id'      => 'site_inactive',
				'message' => esc_html__( 'This license key is saved but has not been verified.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Site Inactive', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Wrong Product
			'item_name_mismatch' => array(
				'id'      => 'item_name_mismatch',
				'message' => esc_html__( 'This license key appears to be for another product.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Mismatch', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Item ID incorrect (check your arguments!)
			'invalid_item_id' => array(
				'id'      => 'invalid_item_id',
				'message' => esc_html__( 'This license key appears to be for another product.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Invalid', 'sugar-calendar' ),
				'class'   => 'invalid'
			),

			// Too many activations
			'no_activations_left' => array(
				'id'      => 'no_activations_left',
				'message' => esc_html__( 'This license key has reached its activation limit.', 'sugar-calendar'  ),
				'text'    => esc_html__( 'Out of Activations', 'sugar-calendar' ),
				'class'   => 'invalid'
			),
		);
	}

	// Maybe pluck a specific status
	if ( isset( $retval[ $status ] ) ) {
		$retval = $retval[ $status ];
	}

	// Return specific array, or all if not found
	return $retval;
}

/**
 * Output the license settings section
 *
 * @since 2.0.0
 */
function license_section() {

	// License settings
	$license  = get_license_key();
	$status   = get_license_status();

	// Force status if inactive
	if ( ! empty( $license ) && empty( $status ) ) {
		$status = 'inactive';

	// Force status if empty
	} elseif ( empty( $license ) || empty( $status ) ) {
		$status = 'empty';
	}

	// Get license feedback
	$feedback = get_license_feedback( $status );

	// Toggle
	$refresh_style       = 'display: none;';
	$deactivate_disabled = true;
	$verify_disabled     = empty( $license );

	// Button
	if ( in_array( $status, array( 'valid', 'empty' ), true ) && ! empty( $license ) ) {
		$refresh_style       = 'display: inline-block;';
		$deactivate_disabled = false;
	}

	// Force verify to disabled when status is valid
	if ( 'valid' === $status ) {
		$verify_disabled = true;
		$license_data = get_transient( 'sc_license_check' );
		$feedback['message'] = maybe_add_expiration( $license_data, $feedback['message'] );
	}

	?>

	<div class="sc-license-wrapper">
		<div class="sc-license-header">
			<h3><?php esc_html_e( 'License Key', 'sugar-calendar' ); ?></h3>
			<span><?php esc_html_e( 'Enter a valid license key to enable automatic updates', 'sugar-calendar' ); ?></span>
		</div>
		<div class="sc-license-content">
			<?php _e( 'Look for your license key in your <a href="https://sugarcalendar.com/account/">SugarCalendar.com account</a>. Don\'t have one? <a href="https://sugarcalendar.com/pricing/">Purchase one today!</a>', 'sugar-calendar' ); ?></p>
			<div class="sc-license-input-wrapper">
				<span class="sc-license-input">
					<input id="sc_license_key" name="sc_license_key" type="password" class="sc-license-key" value="<?php echo esc_attr( $license ); ?>" maxlength="32" pattern="[a-zA-Z0-9]+" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">

					<span class="sc-license-status <?php echo sanitize_key( $feedback['class'] ); ?>" title="<?php echo esc_attr( $feedback['text'] ); ?>" aria-hidden="true">
						<span class="screen-reader-text"><?php echo esc_html( $feedback['text'] ); ?></span>
					</span>
				</span>

				<button type="button" class="button-primary sc-license-verify" name="sc_license_verify" <?php disabled( $verify_disabled, true ); ?> data-status="<?php echo sanitize_key( $feedback['class'] ); ?>"><?php esc_html_e( 'Verify', 'sugar-calendar' ); ?></button>
				<button type="submit" class="button-secondary sc-license-deactivate" name="sc_license_deactivate" <?php disabled( $deactivate_disabled, true ); ?>><?php esc_attr_e( 'Deactivate', 'sugar-calendar' ); ?></button>
			</div>

			<p class="sc-license-feedback">
				<span><?php echo $feedback['message']; // May contain HTML ?></span>
				<button type="button" class="sc-license-refresh" style="<?php echo $refresh_style; ?>"><?php esc_html_e( 'Refresh Status', 'sugar-calendar' ); ?></button>
			</p>
		</div>

		<?php wp_nonce_field( 'sc_license_nonce', 'sc_license_nonce' ); ?>

	</div>

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