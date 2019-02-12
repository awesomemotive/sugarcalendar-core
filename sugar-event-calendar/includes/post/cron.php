<?php

/**
 * Event Post Cron
 *
 * @package Plugins/Site/Events/Cron
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Schedule the cron job used to update events that have passed
 *
 * @since 2.0.0
 */
function sugar_calendar_cron_hook() {

	// Bail if already scheduled
	if ( wp_next_scheduled( 'sugar_calendar_cron_hook' ) ) {
		return;
	}
}

/**
 * Unschedule the cron job used to update events that have passed
 *
 * @since 2.0.0
 */
function sugar_calendar_cron_unhook() {
   $timestamp = wp_next_scheduled( 'sugar_calendar_cron_hook' );
   wp_unschedule_event( $timestamp, 'sugar_calendar_cron_hook' );
}
