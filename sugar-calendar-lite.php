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
 * Requires at least: 5.1
 * Version:           2.1.2
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * This class_exists() check avoids fatal errors when this plugin is activated
 * in more than one way, and should not be removed.
 */
if ( ! class_exists( 'Sugar_Calendar\\Requirements_Check' ) ) {
	require_once dirname( __FILE__ ) . '/sugar-calendar/requirements-check.php';

	// Invoke the checker
	new Sugar_Calendar\Requirements_Check( __FILE__ );
}
