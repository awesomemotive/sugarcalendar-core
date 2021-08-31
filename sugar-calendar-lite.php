<?php
/**
 * Plugin Name:       Sugar Calendar (Lite)
 * Plugin URI:        https://sugarcalendar.com
 * Description:       A calendar with a sweet disposition.
 * Author:            Sandhills Development, LLC
 * Author URI:        https://sandhillsdev.com
 * License:           GNU General Public License v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sugar-calendar
 * Domain Path:       /sugar-calendar/includes/languages/
 * Requires PHP:      5.6.20
 * Requires at least: 5.5
 * Version:           2.2.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * This class_exists() check avoids a fatal error when this plugin is activated
 * in more than one way and should not be removed.
 */
if ( ! class_exists( 'Sugar_Calendar\\Requirements_Check' ) ) {

	// Include the Requirements file
	include_once dirname( __FILE__ ) . '/sugar-calendar/requirements-check.php';

	// Invoke the checker
	if ( class_exists( 'Sugar_Calendar\\Requirements_Check' ) ) {
		new Sugar_Calendar\Requirements_Check( __FILE__ );
	}
}
