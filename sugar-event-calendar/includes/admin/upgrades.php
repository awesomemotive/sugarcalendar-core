<?php
/**
 * Upgrade Screen
 *
 * @since 2.0.0
 */
namespace Sugar_Calendar\Admin\Upgrades;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Output the admin upgrades page
 *
 * @since 2.0.0
*/
function page() {
	global $wpdb;

	// Get the post type
	$post_type = sugar_calendar_get_event_post_type_id();

	// Count
	$total_sql = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != %s";
	$total     = $wpdb->get_var( $wpdb->prepare( $total_sql, $post_type, 'auto-draft' ) );

	// Upgrade action
	$action = isset( $_GET['upgrade'] ) ? sanitize_key( $_GET['upgrade'] ) : '';
	$step   = isset( $_GET['step']    ) ?        absint( $_GET['step']   ) : 1;
	$number = isset( $_GET['number']  ) ?        absint( $_GET['number'] ) : 20;
	$steps  = ! empty( $total ) ? ceil( $total / $number ) : null; ?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Sugar Calendar Upgrades', 'sugar-calendar' ); ?></h1>
		<hr class="wp-header-end">

		<div id="sc-upgrade-status">
			<p><?php _e( 'The upgrade process has begun. Please be patient. You will be redirected when it is finished.', 'sugar-calendar' ); ?></p>

			<?php if ( ! empty( $total ) ) : ?>
				<p><strong><?php printf( __( 'Step %d of %d...', 'sugar-calendar' ), $step, $steps ); ?></strong></p>
			<?php endif; ?>
		</div>

		<script type="text/javascript"><?php
			$step++;

			// Redirect to the main calendar page
			if ( $step > $steps ) {
				$url = sugar_calendar_get_admin_base_url();

				// Mark as complete before we redirect
				upgrade_complete( $action );

			// Redirect to the next step in the upgrader
			} else {
				$url = page_url( array(
					'upgrade' => $action,
					'step'    => $step,
					'total'   => $total,
					'steps'   => $steps
				) );
			} ?>

			setTimeout(function() {
				document.location.href = '<?php echo $url; ?>';
			}, 250);
		</script>
	</div>

	<?php

	// Process the step
	process();
}

/**
 * Get the upgrade page URL, complete with arguments & non
 *
 * @since 2.0.0
 *
 * @param array $args
 *
 * @return string
 */
function page_url( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'page'    => 'sc-upgrades',
		'upgrade' => '',
		'step'    => null,
		'total'   => null,
		'steps'   => null
	) );

	// Nonce & returs
	$url = wp_nonce_url( add_query_arg( $r, admin_url( 'index.php' ) ), 'sc-upgrade-nonce' );

	// Unescape
	return str_replace( '&amp;', '&', $url );
}

/**
 * Display Upgrade Notices
 *
 * @since 2.0.0
 */
function notices() {

	// Don't show notices on the upgrades page
	if ( isset( $_GET['page'] ) && ( 'sc-upgrades' === $_GET['page'] ) ) {
		return;
	}

	// Bail if non upgrading from 1.x to 2.x
	if ( ! get_option( 'sc_version' ) ) {
		upgrade_complete( '20_migration' );
	}

	if ( ! did_upgrade( '20_migration' ) ) {
		printf(
			'<div class="updated"><p>' . __( 'Sugar Calendar needs to upgrade the events database, click <a href="%s">here</a> to start the upgrade.', 'sugar-calendar' ) . '</p></div>',
			page_url( array( 'upgrade' => '20_migration' ) )
		);
	}
}

/**
 * Detects and processes upgrades
 *
 * @since  2.0.0
 *
 * @return bool
 */
function process() {

	// Bail if not in admin
	if ( ! is_admin() ) {
		return;
	}

	// Bail if no upgrade action or nonce
	if ( empty( $_GET['upgrade'] ) || empty( $_REQUEST['_wpnonce'] ) ) {
		return;
	}

	// Bail if doing ajax
	if ( wp_doing_ajax() ) {
		return;
	}

	// Bail if current user cannot manage options
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Bail if nonce check fails
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'sc-upgrade-nonce' ) ) {
		return;
	}

	// Sanitize the step
	$step = sanitize_key( $_GET['upgrade'] );
	$func = "Sugar_Calendar\\Admin\\Upgrades\\do_{$step}";

	if ( function_exists( $func ) ) {
		call_user_func( $func );
	}
}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  2.0.0
 * @param  string $upgrade_action The action to add to the completed upgrades array
 *
 * @return bool                   If the function was successfully added
 */
function upgrade_complete( $upgrade_action = '' ) {

	// Bail if no new upgrade action to set
	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'sc_completed_upgrades', $completed_upgrades );
}

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  2.0.0
 * @param  string $upgrade_action The upgrade action to check completion for
 *
 * @return bool                   If the action has been added to the completed actions array
 */
function did_upgrade( $upgrade_action = '' ) {

	// Bail if no new upgrade action to set
	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades = completed_upgrades();

	return in_array( $upgrade_action, $completed_upgrades, true );
}

/**
 * Gets the array of completed upgrade actions
 *
 * @since  2.0.0
 *
 * @return array The array of completed upgrades
 */
function completed_upgrades() {

	// Get option
	$completed_upgrades = get_option( 'sc_completed_upgrades', array() );

	// Make sure empty value is an array
	if ( empty( $completed_upgrades ) ) {
		$completed_upgrades = array();
	}

	// Cast and return
	return (array) $completed_upgrades;
}

/**
 * Upgrades for events data for version 2.0
 *
 * @since 2.0.0
 */
function do_20_migration() {
	global $wpdb, $sc_doing_upgrade;

	// Set the upgrade global
	$sc_doing_upgrade = true;

	@ignore_user_abort( true );

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 20;
	$offset = ( $step === 1 )
		? 0
		: ( $step - 1 ) * $number;

	// Get the post type
	$post_type = sugar_calendar_get_event_post_type_id();

	// Events
	$posts_sql = "SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_status != %s ORDER BY %s ASC LIMIT %d, %d";
	$posts     = $wpdb->get_results( $wpdb->prepare( $posts_sql, $post_type, 'auto-draft', 'ID', $offset, $number ) );

	// Loop through posts to migrate
	if ( ! empty( $posts ) && ! is_wp_error( $posts ) ) {

		// Migrate events
		foreach ( $posts as $post ) {

			// Query for duplicate
			$duplicate = sugar_calendar_get_event_by_object( $post->ID, 'post' );

			// Skip if already exists
			if ( $duplicate->exists() ) {
				continue;
			}

			// Get start & end
			$start = (int) get_post_meta( $post->ID, 'sc_event_date_time',     true );
			$end   = (int) get_post_meta( $post->ID, 'sc_event_end_date_time', true );

			// Format the start & end
			$start_date_time = date( 'Y-m-d H:i:s', $start );
			$end_date_time   = date( 'Y-m-d H:i:s', $end   );

			// Format the start & end
			$start_hours = date( 'H:i:s', $start );
			$end_hours   = date( 'H:i:s', $end   );

			// Mark as "All Day" if no end date, or start & end are identical
			if ( empty( $end ) || ( $start === $end ) ) {
				$all_day = true;

			// Mark as "All Day" if both start & end are midnight
			} elseif ( ( $start_hours === $end_hours ) && ( '00:00:00' === $start_hours ) ) {
				$all_day = true;

			// Default to not "All Day"
			} else {
				$all_day = false;
			}

			// Format the start & end
			$start_day = date( 'Y-m-d', $start );
			$end_day   = date( 'Y-m-d', $end   );

			// Force empty end time to match start time
			if ( ( $start_day === $end_day ) && ( '00:00:00' === $end_hours ) ) {
				$end = $start;
				$end_date_time = $start_date_time;
			}

			// Recurring
			$recur_type = get_post_meta( $post->ID, 'sc_event_recurring', true );

			// Do not save "none" string value
			$recur_type = ! empty( $recur_type ) && ( 'none' !== $recur_type )
				? sanitize_key( $recur_type )
				: '';

			// Format recurrence end
			$recur_end = (int) get_post_meta( $post->ID, 'sc_recur_until', true );
			$recur_end = ! empty( $recur_end )
				? date( 'Y-m-d H:i:s', $recur_end )
				: '';

			// Add the event
			sugar_calendar_add_event( array(
				'object_id'      => $post->ID,
				'object_type'    => 'post',
				'object_subtype' => $post->post_type,
				'title'          => $post->post_title,
				'content'        => $post->post_content,
				'status'         => $post->post_status,
				'start'          => $start_date_time,
				'end'            => $end_date_time,
				'all_day'        => $all_day,
				'recurrence'     => $recur_type,
				'recurrence_end' => $recur_end,
				'date_created'   => $post->post_date_gmt,
				'date_modified'  => $post->post_modified_gmt,

				/**
				 * Add event meta keys and values below.
				 *
				 * - Largely from add-ons
				 * - Empty values are not saved
				 * - Duplicated keys will overwrite
				 * - Repeat get_post_meta() calls are cached
				 */
				// From Google Maps Add-on
				'location'       => get_post_meta( $post->ID, 'sc_map_address', true )
			) );
		}
	}

	// Unet the upgrade global
	unset( $sc_doing_upgrade );
}