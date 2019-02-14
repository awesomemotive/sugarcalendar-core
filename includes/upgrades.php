<?php

function sc_display_upgrade_notices() {

	$version     = get_option( 'sc_version' );
	$upgrade_url = wp_nonce_url( add_query_arg( array( 'sc-action' => 'upgrade-end-times' ), admin_url() ), 'sc-upgrade-nonce' );

	if( empty( $version ) || version_compare( $version, '1.5', '<' ) ) {
		echo '<div class="notice notice-info"><p>' . sprintf( __( 'Sugar Calendar needs to upgrade the events database. Click <a href="%s">here to begin the upgrade</a>.', 'pippin_sc' ), $upgrade_url ) . '</p></div>';
	}

}
add_action( 'admin_notices', 'sc_display_upgrade_notices' );

function sc_process_upgrades() {

	if( empty( $_REQUEST['sc-action'] ) ) {
		return;
	}

	if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'sc-upgrade-nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'pippin_sc' ), __( 'Error', 'pippin_sc' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'edit_posts' ) ) {
		wp_die( __( 'You do not have permission to run this upgrade', 'pippin_sc' ), __( 'Error', 'pippin_sc' ), array( 'response' => 403 ) );
	}

	$events = get_posts( array( 'post_type' => 'sc_event', 'posts_per_page' => -1, 'fields' => 'ids' ) );

	if( $events ) {

		foreach( $events as $event ) {

			$date = get_post_meta( $event, 'sc_event_date_time', true );
			$type = get_post_meta( $event, 'sc_event_recurring', true );

			update_post_meta( $event, 'sc_event_end_date_time', sc_get_event_date( $event, false ) );
			update_post_meta( $event, 'sc_event_end_date', $date );
			update_post_meta( $event, 'sc_event_end_day', date( 'D', $date ) );
			update_post_meta( $event, 'sc_event_end_day_of_week', date( 'N', strtotime( $date ) ) );
			update_post_meta( $event, 'sc_event_end_day_of_month', date( 'd', $date ) );
			update_post_meta( $event, 'sc_event_end_month', date( 'm', $date ) );
			update_post_meta( $event, 'sc_event_end_year', date( 'Y', $date ) );

			// Add recurring event timestamps
			if ( ! empty( $type ) && 'none' != $type ) {
				sc_update_recurring_events( $event );
			}

		}
	}

	update_option( 'sc_version', SEC_PLUGIN_VERSION );

	wp_redirect( admin_url( 'edit.php?post_type=sc_event' ) ); exit;

}
add_action( 'admin_init', 'sc_process_upgrades' );