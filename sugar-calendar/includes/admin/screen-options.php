<?php
/**
 * Event Admin Screen Options
 *
 * @package Plugins/Site/Events/Admin/Screen
 */
namespace Sugar_Calendar\Admin\Screen\Options;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Admin screen options
 *
 * @since 2.0.0
 */
function add() {

	// Bail if not primary post type screen
	if ( ! sugar_calendar_admin_is_events_page() ) {
		return;
	}

	// Custom option output
	add_filter( 'screen_settings', 'Sugar_Calendar\\Admin\\Screen\\Options\\display', 10, 2 );
}

/**
 * Return the contents of event specific screen options
 *
 * This is largely for user-specific preferences, but could theoretically be
 * useful for other event view-specific screen options.
 *
 * @since 2.0.0
 *
 * @param string $settings
 * @param object $screen
 *
 * @return string
 */
function display( $settings = '', $screen = false ) {

	// Start the output buffer
	ob_start();

	// Custom screen options
	do_action( 'sugar_calendar_screen_options' );

	// Return the output buffer contents
	return ob_get_clean();
}

/**
 * Return the default preferences
 *
 * @since 2.1.1
 *
 * @return array
 */
function get_defaults() {
	return array(
		'sc_events_max_num' => '100',
		'sc_start_of_week'  => '1',
		'sc_date_format'    => 'F j, Y',
		'sc_time_format'    => 'g:i a',
		'sc_timezone'       => ''
	);
}

/**
 * Output the various screen option settings for Calendar page views and modes
 *
 * @since 2.0.0
 */
function preferences() {
	global $wp_locale;

	// Default values
	$sc_events_max_num = null;
	$sc_start_of_week = null;
	$sc_date_format = null;
	$sc_time_format = null;
	$sc_timezone = null;

	// Get the default preferences
	$preferences = get_defaults();

	// Assign preferences to variable variables to use below
	foreach ( $preferences as $key => $default ) {
		${$key} = sugar_calendar_get_user_preference( $key, $default );
	}

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
	$custom_date_checked = ! in_array( $sc_date_format, $date_formats, true );

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

	// Is custom time checked?
	$custom_time_checked = ! in_array( $sc_time_format, $time_formats, true );

	// Get the time zone and type
	$timezone = sugar_calendar_get_timezone();
	$tztype   = sugar_calendar_get_timezone_type(); ?>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_events_max_num"><?php esc_html_e( 'Maximum Events', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<input type="number" step="1" min="1" max="999" class="code" name="sc_events_max_num" id="sc_events_max_num" maxlength="3" value="<?php echo absint( $sc_events_max_num ); ?>">
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="sc_start_of_week"><?php esc_html_e( 'Start of Week', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<select id="sc_start_of_week" name="sc_start_of_week">
						<option value="0" <?php selected( $sc_start_of_week, 0 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 0 ) ); ?></option>
						<option value="1" <?php selected( $sc_start_of_week, 1 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 1 ) ); ?></option>
						<option value="2" <?php selected( $sc_start_of_week, 2 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 2 ) ); ?></option>
						<option value="3" <?php selected( $sc_start_of_week, 3 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 3 ) ); ?></option>
						<option value="4" <?php selected( $sc_start_of_week, 4 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 4 ) ); ?></option>
						<option value="5" <?php selected( $sc_start_of_week, 5 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 5 ) ); ?></option>
						<option value="6" <?php selected( $sc_start_of_week, 6 ); ?>><?php echo esc_html( $wp_locale->get_weekday( 6 ) ); ?></option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select the first day of the week', 'sugar-calendar' ); ?>
					</p>
				</td>
			</tr>

			<tr valign="top" class="sc-date-time-prefs">
				<th scope="row" valign="top">
					<label for="sc_date_format"><?php esc_html_e( 'Date Format', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<?php foreach ( $date_formats as $key => $format ) :

						// Radio ID
						$id = ( 0 === $key )
							? ' id="sc_date_format"'
							: '';

						// Checked?
						$checked = checked( $format, $sc_date_format, false );

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
						<input type="radio" name="sc_date_format" id="sc_date_format_custom_radio" value="<?php echo esc_attr( $sc_date_format ); ?>" <?php checked( $custom_date_checked ); ?> />
						<span class="date-time-text date-time-custom-text"><?php esc_html_e( 'Custom:', 'sugar-calendar' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'enter a custom date format in the following field', 'sugar-calendar' ); ?></span>
						</span>
					</label>

					<label for="sc_date_format_custom" class="screen-reader-text"><?php esc_html_e( 'Custom date format:', 'sugar-calendar' ); ?></label>
					<input type="text" name="sc_date_format_custom" id="sc_date_format_custom" value="<?php echo esc_attr( $sc_date_format ); ?>" class="small-text" />
				</td>
			</tr>

			<tr valign="top" class="sc-date-time-prefs">
				<th scope="row" valign="top">
					<label for="sc_time_format"><?php esc_html_e( 'Time Format', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<?php foreach ( $time_formats as $key => $format ) :

						// Radio ID
						$id = ( 0 === $key )
							? ' id="sc_time_format"'
							: '';

						// Checked?
						$checked = checked( $format, $sc_time_format, false );

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
						<input type="radio" name="sc_time_format" id="sc_time_format_custom_radio" value="<?php echo esc_attr( $sc_time_format ); ?>" <?php checked( $custom_time_checked ); ?> />
						<span class="date-time-text date-time-custom-text"><?php esc_html_e( 'Custom:', 'sugar-calendar' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'enter a custom time format in the following field', 'sugar-calendar' ); ?></span>
						</span>
					</label>

					<label for="sc_time_format_custom" class="screen-reader-text"><?php esc_html_e( 'Custom time format:', 'sugar-calendar' ); ?></label>
					<input type="text" name="sc_time_format_custom" id="sc_time_format_custom" value="<?php echo esc_attr( $sc_time_format ); ?>" class="small-text" />
				</td>
			</tr>

			<?php if ( ! empty( $sc_timezone ) || ( 'off' !== $tztype ) ) : ?>

				<tr valign="top">
					<th scope="row" valign="top">
						<label for="sc_timezone"><?php esc_html_e( 'Time Zone', 'sugar-calendar' ); ?></label>
					</th>
					<td>
						<?php sugar_calendar_timezone_dropdown( array(
							'id'      => 'sc_timezone',
							'name'    => 'sc_timezone',
							'class'   => '',
							'none'    => esc_html__( 'Floating', 'sugar-calendar' ),
							'current' => $sc_timezone
						) ); ?>
					</td>
				</tr>

			<?php endif; ?>

		</tbody>
	</table>

	<p class="submit"><?php

		// Submit button
		submit_button(
			esc_html__( 'Update', 'sugar-calendar' ),
			'primary',
			'screen-options-apply',
			false
		);

		// Nonce URL
		$url       = add_query_arg( array( 'action' => 'sc-reset-user-prefs' ), wp_get_referer() );
		$reset_url = wp_nonce_url( $url, 'sc-reset-user-prefs', 'sc-screen-options-nonce' ); ?>

		<a class="button secondary" href="<?php echo esc_url( $reset_url ); ?>"><?php

			esc_html_e( 'Reset to Defaults', 'sugar-calendar' );

		?></a>
	</p>

	<?php
}

/**
 * Save the current user's preferences
 *
 * @since 2.0.0
 */
function save() {

	// Bail if not saving screen options
	if ( ! isset( $_POST['screen-options-apply'] ) ) {
		return;
	}

	// Nonce check
	check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );

	// Get the default preferences
	$preferences = get_defaults();

	// Assign preferences to variable variables to use below
	foreach ( $preferences as $key => $default ) {

		// Get the POSTed value of this key
		$pref = isset( $_POST[ $key ] )
			? sanitize_text_field( $_POST[ $key ] )
			: $default;

		// Save preference
		sugar_calendar_set_user_preference( $key, $pref );
	}

	// Redirect
	wp_safe_redirect( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	exit();
}

/**
 * Reset the current user's preferences
 *
 * @since 2.1.2
 */
function reset() {

	// Bail if not saving screen options
	if ( ! isset( $_GET['action'] ) || ( 'sc-reset-user-prefs' !== $_GET['action'] ) ) {
		return;
	}

	// Nonce check
	check_admin_referer( 'sc-reset-user-prefs', 'sc-screen-options-nonce' );

	// Get the default preferences
	$preferences = array_keys( get_defaults() );

	// Assign preferences to variable variables to use below
	foreach ( $preferences as $key ) {
		sugar_calendar_delete_user_preference( $key );
	}

	// Remove action argument
	$redirect_url = remove_query_arg( 'action', wp_unslash( $_SERVER['REQUEST_URI'] ) );

	// Redirect
	wp_safe_redirect( $redirect_url );
	exit();
}
