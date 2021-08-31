<?php
/**
 * Main Plugin Class.
 *
 * @since 1.0.0
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main Plugin Class.
 *
 * @since 2.0.0
 */
final class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var object|Plugin
	 */
	private static $instance = null;

	/**
	 * Loader file.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $file = '';

	/**
	 * Current version.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $version = '2.2.4';

	/**
	 * Prefix.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $prefix = 'sc';

	/**
	 * Main instance.
	 *
	 * Ensures that only one instance exists in memory at any one time.
	 * Also prevents needing to define globals all over the place.
	 *
	 * @since 2.0.0
	 *
	 * @static
	 * @staticvar array $instance
	 * @return object|Plugin
	 */
	public static function instance( $file = '' ) {

		// Return if already instantiated
		if ( self::is_instantiated() ) {
			return self::$instance;
		}

		// Setup the singleton
		self::setup_instance( $file );

		// Bootstrap
		self::$instance->setup_constants();
		self::$instance->setup_files();
		self::$instance->setup_application();

		// Return the instance
		return self::$instance;
	}

	/**
	 * Main installer.
	 *
	 * @since 2.0.0
	 */
	public static function install() {

	}

	/**
	 * Main uninstaller.
	 *
	 * @since 2.0.0
	 */
	public static function uninstall() {

	}

	/**
	 * Main activator, fired as a WordPress activation hook.
	 *
	 * (As a general rule, try to avoid using this if you can.)
	 *
	 * @since 2.0.0
	 */
	public static function activate() {

	}

	/**
	 * Main deactivator, fired as a WordPress deactivation hook.
	 *
	 * (As a general rule, try to avoid using this if you can.)
	 *
	 * @since 2.0.0
	 */
	public static function deactivate() {

	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __NAMESPACE__, '2.0' );
	}

	/**
	 * Disable un-serializing of the class.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __NAMESPACE__, '2.0' );
	}

	/**
	 * Public magic isset method allows checking any key from any scope.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __isset( $key = '' ) {
		return (bool) isset( $this->{$key} );
	}

	/**
	 * Public magic get method allows getting any value from any scope.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		return $this->__isset( $key )
			? $this->{$key}
			: null;
	}

	/**
	 * Return whether the main loading class has been instantiated or not.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if instantiated. False if not.
	 */
	private static function is_instantiated() {

		// Return true if instance is correct class
		if ( ! empty( self::$instance ) && ( self::$instance instanceof Plugin ) ) {
			return true;
		}

		// Return false if not instantiated correctly
		return false;
	}

	/**
	 * Setup the singleton instance
	 *
	 * @since 2.0.0
	 * @param string $file
	 */
	private static function setup_instance( $file = '' ) {
		self::$instance       = new Plugin;
		self::$instance->file = $file;
	}

	/**
	 * Setup plugin constants.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Uppercase
		$prefix = strtoupper( $this->prefix );

		// Plugin Version.
		if ( ! defined( "{$prefix}_PLUGIN_VERSION" ) ) {
			define( "{$prefix}_PLUGIN_VERSION", $this->version );
		}

		// Plugin Root File.
		if ( ! defined( "{$prefix}_PLUGIN_FILE" ) ) {
			define( "{$prefix}_PLUGIN_FILE", $this->file );
		}

		// Prepare file & directory
		$file = constant( "{$prefix}_PLUGIN_FILE" );
		$dir  = basename( __DIR__ );

		// Plugin Base Name.
		if ( ! defined( "{$prefix}_PLUGIN_BASE" ) ) {
			define( "{$prefix}_PLUGIN_BASE", plugin_basename( $file ) . $dir );
		}

		// Plugin Folder Path.
		if ( ! defined( "{$prefix}_PLUGIN_DIR" ) ) {
			define( "{$prefix}_PLUGIN_DIR", trailingslashit( plugin_dir_path( $file ) . $dir ) );
		}

		// Plugin Folder URL.
		if ( ! defined( "{$prefix}_PLUGIN_URL" ) ) {
			define( "{$prefix}_PLUGIN_URL", trailingslashit( plugin_dir_url( $file ) . $dir ) );
		}

		// Make sure CAL_GREGORIAN is defined.
		if ( ! defined( 'CAL_GREGORIAN' ) ) {
			define( 'CAL_GREGORIAN', 1 );
		}
	}

	/**
	 * Setup files.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function setup_files() {

		// Lite
		$this->include_lite();

		// Standard
		$this->include_standard();

		// Admin specific
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->include_admin();

		// Front-end specific
		} else {
			$this->include_frontend();
		}
	}

	/**
	 * Setup the rest of the application
	 *
	 * @since 2.0.0
	 */
	private function setup_application() {

		// Database tables
		new Events_Table();
		new Meta_Table();

		// Backwards Compatibility
		new Posts\Meta\Back_Compat();

		// Taxonomy Features
		new Term_Timezones( $this->file );
		new Term_Colors( $this->file );
	}

	/** Includes **************************************************************/

	/**
	 * Include non-specific files.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	private function include_lite() {

		// Database Engine
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Base.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Table.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Query.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Column.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Row.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Schema.php';

		// Database Queries
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Queries/Meta.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Queries/Compare.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/Queries/Date.php';

		// Events Databases
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/Query.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/Row.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/Schema.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/TableEvents.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/TableEventmeta.php';

		// Utilities
		require_once SC_PLUGIN_DIR . 'includes/classes/utilities/class-term-meta-ui.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/utilities/ical-to-array.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/utilities/ical-rrule-sequencer.php';

		// Terms
		require_once SC_PLUGIN_DIR . 'includes/classes/terms/class-term-colors.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/terms/class-term-timezones.php';

		// Event files
		require_once SC_PLUGIN_DIR . 'includes/events/capabilities.php';
		require_once SC_PLUGIN_DIR . 'includes/events/functions.php';
		require_once SC_PLUGIN_DIR . 'includes/events/meta-data.php';
		require_once SC_PLUGIN_DIR . 'includes/events/relationships.php';

		// Post files
		require_once SC_PLUGIN_DIR . 'includes/post/cron.php';
		require_once SC_PLUGIN_DIR . 'includes/post/feed.php';
		require_once SC_PLUGIN_DIR . 'includes/post/functions.php';
		require_once SC_PLUGIN_DIR . 'includes/post/meta.php';
		require_once SC_PLUGIN_DIR . 'includes/post/query-filters.php';
		require_once SC_PLUGIN_DIR . 'includes/post/taxonomies.php';
		require_once SC_PLUGIN_DIR . 'includes/post/types.php';
		require_once SC_PLUGIN_DIR . 'includes/post/relationship.php';

		// Legacy
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/functions.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/scripts.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/shortcodes.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/widgets.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/hooks.php';

		// Common files
		require_once SC_PLUGIN_DIR . 'includes/common/assets.php';
		require_once SC_PLUGIN_DIR . 'includes/common/color.php';
		require_once SC_PLUGIN_DIR . 'includes/common/editor.php';
		require_once SC_PLUGIN_DIR . 'includes/common/general.php';
		require_once SC_PLUGIN_DIR . 'includes/common/preferences.php';
		require_once SC_PLUGIN_DIR . 'includes/common/settings.php';
		require_once SC_PLUGIN_DIR . 'includes/common/time-zones.php';
		require_once SC_PLUGIN_DIR . 'includes/common/time.php';
		require_once SC_PLUGIN_DIR . 'includes/common/hooks.php';
	}

	/**
	 * Include administration specific files.
	 *
	 * @since 2.0.0
	 */
	private function include_admin() {

		// Include the admin files
		require_once SC_PLUGIN_DIR . 'includes/admin/assets.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/editor.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/general.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/help.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/screen-options.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/menu.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/meta-boxes.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/nav.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/posts.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/upgrades.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/hooks.php';

		// List Tables
		require_once SC_PLUGIN_DIR . 'includes/admin/list-tables/class-wp-list-table-base.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/list-tables/class-wp-list-table-list.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/list-tables/class-wp-list-table-month.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/list-tables/class-wp-list-table-week.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/list-tables/class-wp-list-table-day.php';

		// Meta boxes
		require_once SC_PLUGIN_DIR . 'includes/admin/meta-boxes/class-wp-meta-box.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/meta-boxes/class-wp-meta-box-section.php';
		require_once SC_PLUGIN_DIR . 'includes/admin/meta-boxes/class-wp-walker-category-radio.php';

		// Legacy
		require_once SC_PLUGIN_DIR . 'includes/admin/settings.php';

		// Maybe include front-end on AJAX, add/edit post page, or widgets, to
		// load all shortcodes, widgets, assets, etc...
		if (

			// Admin AJAX
			wp_doing_ajax()

			||

			// Specific admin pages
			(
				! empty( $GLOBALS['pagenow'] )

				&&

				in_array( $GLOBALS['pagenow'], array( 'post.php', 'widgets.php' ), true )
			)
		) {
			$this->include_frontend();
		}
	}

	/**
	 * Include front-end specific files.
	 *
	 * @since 2.0.0
	 */
	private function include_frontend() {

		// Legacy Theme
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/ajax.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/calendar.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/event-display.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/events-list.php';
	}

	/**
	 * Include Standard (non Lite) files, if they exist.
	 *
	 * @since 2.0.3
	 */
	private function include_standard() {

		// Files & directory
		$files = array();
		$dir   = trailingslashit( __DIR__ ) . 'includes/standard';

		// Bail if standard directory does not exist
		if ( ! is_dir( $dir ) ) {
			return;
		}

		// Try to open the directory
		$dh = opendir( $dir );

		// Bail if directory exists but cannot be opened
		if ( empty( $dh ) ) {
			return;
		}

		// Look for files in the directory
		while ( ( $plugin = readdir( $dh ) ) !== false ) {
			$ext = substr( $plugin, -4 );

			if ( $ext === '.php' ) {
				$name = substr( $plugin, 0, strlen( $plugin ) -4 );
				$files[ $name ] = trailingslashit( $dir ) . $plugin;
			}
		}

		// Close the directory
		closedir( $dh );

		// Skip empty index files
		unset( $files['index'] );

		// Bail if no files
		if ( empty( $files ) ) {
			return;
		}

		// Sort files alphabetically
		ksort( $files );

		// Include each file
		foreach ( $files as $file ) {
			require_once $file;
		}
	}
}
