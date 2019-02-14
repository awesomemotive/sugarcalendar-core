<?php
/*
Plugin Name: Sugar Calendar (Lite)
Plugin URI:  https://sugarcalendar.com
Description: A sweet, simple Event Calendar for WordPress
Author:      Sandhills Development, LLC
Author URI:  https://sandhillsdev.com
Version:     1.6.8
Text Domain: pippin_sc
Domain Path: /languages/
*/


/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/

// plugin folder url
if ( !defined( 'SC_PLUGIN_URL' ) ) {
	define( 'SC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
// plugin folder path
if ( !defined( 'SC_PLUGIN_DIR' ) ) {
	define( 'SC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
// plugin root file
if ( !defined( 'SC_PLUGIN_FILE' ) ) {
	define( 'SC_PLUGIN_FILE', __FILE__ );
}
if ( !defined( 'SEC_PLUGIN_VERSION' ) ) {
	define( 'SEC_PLUGIN_VERSION', '1.6.8' );
}
if ( !defined( 'SEC_PLUGIN_NAME' ) ) {
	define( 'SEC_PLUGIN_NAME', 'Sugar Calendar' );
}


/*
|--------------------------------------------------------------------------
| INTERNATIONALIZATION
|--------------------------------------------------------------------------
*/

function sc_textdomain() {
	load_plugin_textdomain( 'pippin_sc', false, dirname( plugin_basename( SC_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'init', 'sc_textdomain', 1 );


/*
|--------------------------------------------------------------------------
| File Includes
|--------------------------------------------------------------------------
*/

include_once SC_PLUGIN_DIR . '/includes/install.php';
include_once SC_PLUGIN_DIR . '/includes/post-types.php';
include_once SC_PLUGIN_DIR . '/includes/taxonomies.php';
include_once SC_PLUGIN_DIR . '/includes/list-table-columns.php';
include_once SC_PLUGIN_DIR . '/includes/scripts.php';
include_once SC_PLUGIN_DIR . '/includes/ajax.php';
include_once SC_PLUGIN_DIR . '/includes/widgets.php';
include_once SC_PLUGIN_DIR . '/includes/meta-boxes.php';
include_once SC_PLUGIN_DIR . '/includes/calendar.php';
include_once SC_PLUGIN_DIR . '/includes/events-list.php';
include_once SC_PLUGIN_DIR . '/includes/functions.php';
include_once SC_PLUGIN_DIR . '/includes/shortcodes.php';
include_once SC_PLUGIN_DIR . '/includes/event-display.php';
include_once SC_PLUGIN_DIR . '/includes/query-filters.php';
include_once SC_PLUGIN_DIR . '/includes/plugin-compatibility.php';
include_once SC_PLUGIN_DIR . '/includes/feed.php';

if ( is_admin() ) {
	if ( ! class_exists( 'SG_Plugin_Updater' ) ) {
		// load our custom updater
		include dirname( __FILE__ ) . '/SG_Plugin_Updater.php';
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'sc_license_key' ) );

	// setup the updater
	$edd_updater = new SG_Plugin_Updater( 'https://sugarcalendar.com/', __FILE__, array(
			'version'   => SEC_PLUGIN_VERSION,  // current version number
			'license'   => $license_key,        // license key (used get_option above to retrieve from DB)
			'item_name' => SEC_PLUGIN_NAME,     // name of this plugin
			'item_id'   => 16,                  // ID of this plugin
			'author'    => 'Pippin Williamson', // author of this plugin
			'beta'      => (bool) get_option( 'sc_beta_opt_in' )
		)
	);
	include_once SC_PLUGIN_DIR . '/includes/settings.php';
	include_once SC_PLUGIN_DIR . '/includes/upgrades.php';
}