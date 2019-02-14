<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Admin settings for the calendar
 *
 * @return void
 */
function sc_settings_menu() {
	add_submenu_page( 'edit.php?post_type=sc_event', __( 'Settings', 'pippin_sc' ), __( 'Settings', 'pippin_sc' ), 'manage_options', 'sc-settings', 'sc_settings_page' );
}
add_action('admin_menu', 'sc_settings_menu');

/**
 * Callback for add_submenu_page
 *
 * @since 1.0.0
 * @return void
 */
function sc_settings_page() {
	$license        = get_option( 'sc_license_key' );
	$status         = get_option( 'sc_license_status' );
	$start_of_week  = sc_get_week_start_day();
	$sc_date_format = sc_get_date_format();
	$sc_time_format = sc_get_time_format();
	$beta_opt_in    = get_option( 'sc_beta_opt_in' );

	if ( isset( $_GET['settings-updated'] ) && 'true' == $_GET['settings-updated'] ){?>
		<div class="updated fade"><p class="align-left"><?php _e('Settings Updated', 'pippin_sc'); ?></p></div>
	<?php }

	if ( ( false !== $license ) && ( 'invalid' == $status ) ){?>
		<div class="error fade"><p class="align-left"><?php _e('The supplied license key is invalid', 'pippin_sc'); ?></p></div>
	<?php } ?>

	<div class="wrap">
		<h2><?php _e( 'Sugar Event Calendar Settings', 'pippin_sc' ); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields( 'sc_settings' ); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'Start of Week' ); ?>
						</th>
						<td>
							<select id="sc_start_of_week" name="sc_start_of_week">
								<option value="0" <?php selected( $start_of_week, 0); ?>><?php _e( 'Sunday', 'pippin_sc' ); ?></option>
								<option value="1" <?php selected( $start_of_week, 1); ?>><?php _e( 'Monday', 'pippin_sc' ); ?></option>
								<option value="2" <?php selected( $start_of_week, 2); ?>><?php _e( 'Tuesday', 'pippin_sc' ); ?></option>
								<option value="3" <?php selected( $start_of_week, 3); ?>><?php _e( 'Wednesday', 'pippin_sc' ); ?></option>
								<option value="4" <?php selected( $start_of_week, 4); ?>><?php _e( 'Thursday', 'pippin_sc' ); ?></option>
								<option value="5" <?php selected( $start_of_week, 5); ?>><?php _e( 'Friday', 'pippin_sc' ); ?></option>
								<option value="6" <?php selected( $start_of_week, 6); ?>><?php _e( 'Saturday', 'pippin_sc' ); ?></option>
							</select>
							<label class="description" for="sc_start_of_week"><?php _e( 'Select the first day of the week', 'pippin_sc' ); ?></label>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'Date Format' ); ?>
						</th>
						<td>
							<label title="F j, Y"><input type="radio" name="sc_date_format" value="F j, Y" <?php checked('F j, Y', $sc_date_format); ?>> <span><?php echo date( 'F j, Y' ); ?></span></label><br/>
							<label title="d/m/Y"><input type="radio" name="sc_date_format" value="d/m/Y" <?php checked('d/m/Y', $sc_date_format); ?>> <span><?php echo date( 'd/m/Y' ); ?></span></label><br/>
							<label title="m/d/Y"><input type="radio" name="sc_date_format" value="m/d/Y" <?php checked('m/d/Y', $sc_date_format); ?>> <span><?php echo date( 'm/d/Y' ); ?></span></label><br/>
							<label title="Y-m-d"><input type="radio" name="sc_date_format" value="Y-m-d" <?php checked('Y-m-d', $sc_date_format); ?>> <span><?php echo date( 'Y-m-d' ); ?></span></label><br/>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'Time Format' ); ?>
						</th>
						<td>
							<label title="g:i a"><input type="radio" name="sc_time_format" value="g:i a" <?php checked('g:i a', $sc_time_format); ?>> <span><?php echo date( 'g:i a' ); ?></span></label><br/>
							<label title="g:i A"><input type="radio" name="sc_time_format" value="g:i A" <?php checked('g:i A', $sc_time_format); ?>> <span><?php echo date( 'g:i A' ); ?></span></label><br/>
							<label title="H:i"><input type="radio" name="sc_time_format" value="H:i" <?php checked('H:i', $sc_time_format); ?>> <span><?php echo date( 'H:i' ); ?></span></label><br/>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'License Key' ); ?>
						</th>
						<td>
							<input id="sc_license_key" name="sc_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" /><br/>
							<p class="description" for="sc_license_key"><?php _e( 'Enter your license key (from sugarcalendar.com) if you have one', 'pippin_sc' ); ?></p>
						</td>
					</tr>
					<?php if ( ! empty( $license ) ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'Activate License' ); ?>
							</th>
							<td>
								<?php if ( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e( 'active' ); ?></span>
									<?php wp_nonce_field( 'sc_nonce', 'sc_nonce' ); ?>
									<input type="submit" class="button-secondary" name="sc_license_deactivate" value="<?php _e( 'Deactivate License', 'pippin_sc' ); ?>"/>
								<?php } else { ?>
									<?php wp_nonce_field( 'sc_nonce', 'sc_nonce' ); ?>
									<input type="submit" class="button-secondary" name="sc_license_activate" value="<?php _e( 'Activate License', 'pippin_sc' ); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
					<tr valign="top">
						<th scope="row" valign="top">
							<label for="sc_beta_opt_in"><?php esc_html_e( 'Get Beta Versions' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="sc_beta_opt_in" value="1" <?php checked( true, $beta_opt_in ); ?>> <span><?php _e( 'Receive update notifications about beta versions instead.', 'sugar-calendar' ) ?></span>
							</label><br/>
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
<?php
}


/**
 * Creates our settings in the options table
 *
 * @since 1.0.0
 * @return void
 */
function sc_register_option() {
	register_setting( 'sc_settings', 'sc_license_key', 'edd_sc_sanitize_license' );
	register_setting( 'sc_settings', 'sc_start_of_week' );
	register_setting( 'sc_settings', 'sc_date_format' );
	register_setting( 'sc_settings', 'sc_time_format' );
	register_setting( 'sc_settings', 'sc_beta_opt_in' );
}
add_action( 'admin_init', 'sc_register_option' );

/**
 * Callback for setting up the sc_license_key setting
 *
 * @since 1.0.0
 * @param $new
 * @return string
 */
function edd_sc_sanitize_license( $new ) {
	$old = get_option( 'sc_license_key' );
	if ( $old && $old != $new ) {
		delete_option( 'sc_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

/**
 * License activation check
 *
 * @since 1.0.0
 * @return void
 */
function sc_activate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['sc_license_activate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'sc_nonce', 'sc_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'sc_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license'   => $license,
			'item_name' => urlencode( SEC_PLUGIN_NAME ),
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( 'https://sugarcalendar.com/', array( 'body' => $api_params, 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'sc_license_status', $license_data->license );
		delete_transient( 'sc_license_check' );

		if( 'valid' !== $license_data->license ) {
			wp_die( sprintf( __( 'Your license key could not be activated. Error: %s', 'pippin_sc' ), $license_data->error ), __( 'Error', 'pippin_sc' ), array( 'response' => 401, 'back_link' => true ) );
		}
	}
}
add_action( 'admin_init', 'sc_activate_license' );

/**
 * Deactivate license
 *
 * @since 1.0.0
 * @return void
 */
function sc_deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['sc_license_deactivate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'sc_nonce', 'sc_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'sc_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license'   => $license,
			'item_name' => urlencode( SEC_PLUGIN_NAME ),
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( 'https://sugarcalendar.com/', array( 'body' => $api_params, 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license == 'deactivated' ) {
			delete_option( 'sc_license_status' );
			delete_transient( 'sc_license_check' );
		}
	}
}
add_action( 'admin_init', 'sc_deactivate_license' );

/**
 * Check if license is valid
 *
 * @since 1.0.0
 * @return void|string
 */
function sc_check_license() {
	if( ! empty( $_POST['sc_license_activate'] ) || ! empty( $_POST['sc_license_deactivate'] ) ) {
		return; // Don't fire when saving settings
	}

	$status = get_transient( 'sc_license_check' );
	// Run the license check a maximum of once per day
	if( false === $status ) {

		// retrieve the license from the database
		$license = trim( get_option( 'sc_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'check_license',
			'license' 	=> $license,
			'item_name' => urlencode( SEC_PLUGIN_NAME ),
			'url'       => home_url()
		);
		// Call the custom API.
		$response = wp_remote_post( 'https://sugarcalendar.com/', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'sc_license_status', $license_data->license );

		set_transient( 'sc_license_check', $license_data->license, DAY_IN_SECONDS );
		$status = $license_data->license;
	}
	return $status;
}
add_action( 'admin_init', 'sc_check_license' );
