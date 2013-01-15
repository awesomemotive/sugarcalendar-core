<?php
/*
Plugin Name: Sugar Events Calendar Lite for WordPress
Plugin URI: http://pippinsplugins.com/sugar-calendar-lite
Description: A sweet, simple Event Calendar for WordPress
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Version: 1.0.1
Text Domain: pippin_sc
Domain Path: /languages/
*/


/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/

// plugin folder url
if(!defined('SC_PLUGIN_URL')) {
	define('SC_PLUGIN_URL', plugin_dir_url( __FILE__ ));
}
// plugin folder path
if(!defined('SC_PLUGIN_DIR')) {
	define('SC_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
}
// plugin root file
if(!defined('SC_PLUGIN_FILE')) {
	define('SC_PLUGIN_FILE', __FILE__);
}


/*
|--------------------------------------------------------------------------
| INTERNATIONALIZATION
|--------------------------------------------------------------------------
*/

function sc_textdomain() {
	load_plugin_textdomain( 'pippin_sc', false, dirname( plugin_basename( SC_PLUGIN_FILE ) ) . '/languages/' );
}
add_action('init', 'sc_textdomain', 1);


/*
|--------------------------------------------------------------------------
| File Includes
|--------------------------------------------------------------------------
*/

include_once( SC_PLUGIN_DIR . '/includes/install.php');
include_once( SC_PLUGIN_DIR . '/includes/post-types.php');
include_once( SC_PLUGIN_DIR . '/includes/list-table-columns.php');
include_once( SC_PLUGIN_DIR . '/includes/scripts.php');
include_once( SC_PLUGIN_DIR . '/includes/ajax.php');
include_once( SC_PLUGIN_DIR . '/includes/meta-boxes.php');
include_once( SC_PLUGIN_DIR . '/includes/calendar.php');
include_once( SC_PLUGIN_DIR . '/includes/events-list.php');
include_once( SC_PLUGIN_DIR . '/includes/functions.php');
include_once( SC_PLUGIN_DIR . '/includes/shortcodes.php');
include_once( SC_PLUGIN_DIR . '/includes/event-display.php');
include_once( SC_PLUGIN_DIR . '/includes/query-filters.php');
include_once( SC_PLUGIN_DIR . '/includes/plugin-compatibility.php');
