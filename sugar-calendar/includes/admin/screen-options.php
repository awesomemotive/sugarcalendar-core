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
 * Output the various screen option settings for Calendar page views and modes
 *
 * @since 2.0.0
 */
function preferences() {

	// Get the preferences
	$preferences = array(
		'sc_events_max_num' => '100',
		'date_format'       => 'F j, Y',
		'time_format'       => 'g:i a',
		'start_of_week'     => '1',
	);

	// Assign preferences to variable variables to use below
	foreach ( $preferences as $key => $default ) {
		${$key} = sugar_calendar_get_user_preference( $key, $default );
	} ?>

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
					<label for="start_of_week"><?php esc_html_e( 'Start of Week', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<select id="start_of_week" name="start_of_week">
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
					<label for="date_format"><?php esc_html_e( 'Date Format', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<label title="F j, Y"><input type="radio" name="date_format" value="F j, Y" <?php checked( 'F j, Y', $date_format ); ?>> <span><?php echo date( 'F j, Y' ); ?></span></label><br/>
					<label title="d/m/Y"><input type="radio" name="date_format" value="d/m/Y" <?php checked( 'd/m/Y', $date_format ); ?>> <span><?php echo date( 'd/m/Y' ); ?></span></label><br/>
					<label title="m/d/Y"><input type="radio" name="date_format" value="m/d/Y" <?php checked( 'm/d/Y', $date_format ); ?>> <span><?php echo date( 'm/d/Y' ); ?></span></label><br/>
					<label title="Y-m-d"><input type="radio" name="date_format" value="Y-m-d" <?php checked( 'Y-m-d', $date_format ); ?>> <span><?php echo date( 'Y-m-d' ); ?></span></label><br/>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<label for="time_format"><?php esc_html_e( 'Time Format', 'sugar-calendar' ); ?></label>
				</th>
				<td>
					<label title="g:i a"><input type="radio" name="time_format" value="g:i a" <?php checked( 'g:i a', $time_format ); ?>> <span><?php echo date( 'g:i a' ); ?></span></label><br/>
					<label title="g:i A"><input type="radio" name="time_format" value="g:i A" <?php checked( 'g:i A', $time_format ); ?>> <span><?php echo date( 'g:i A' ); ?></span></label><br/>
					<label title="H:i"><input type="radio" name="time_format" value="H:i" <?php checked( 'H:i', $time_format ); ?>> <span><?php echo date( 'H:i' ); ?></span></label><br/>
				</td>
			</tr>
		</tbody>
	</table>

	<?php

	// Submit button
	submit_button(
		esc_html__( 'Apply', 'sugar-calendar' ),
		'primary',
		'screen-options-apply',
		true
	);
}

/**
 * Save screen options
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

	// Get the preferences
	$preferences = array(
		'sc_events_max_num' => '100',
		'date_format'       => 'F j, Y',
		'time_format'       => 'g:i a',
		'start_of_week'     => '1',
	);

	// Assign preferences to variable variables to use below
	foreach ( $preferences as $key => $default ) {

		$pref = isset( $_POST[ $key ] )
			? sanitize_text_field( $_POST[ $key ] )
			: $default;

		// Skip if not set or default value
		if ( $default === $pref ) {
			sugar_calendar_delete_user_preference( $key );
			continue;
		}

		// Save preference
		sugar_calendar_set_user_preference( $key, $pref );
	}

	// Redirect
	wp_safe_redirect( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	exit();
}
