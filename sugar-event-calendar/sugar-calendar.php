<?php
/**
 * Main Sugar_Calendar Plugin Class.
 *
 * @since 2.0.0
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main Sugar_Calendar Plugin Class.
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
	 * Main instance.
	 *
	 * Insures that only one instance exists in memory at any one time.
	 * Also prevents needing to define globals all over the place.
	 *
	 * @since 2.0.0 Accepts $file parameter to work with Sugar_Calendar_Requirements_Check
	 *
	 * @static
	 * @staticvar array $instance
	 * @see Sugar_Calendar_Requirements_Check()
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
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sugar-calendar' ), '2.0' );
	}

	/**
	 * Disable un-serializing of the class.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sugar-calendar' ), '2.0' );
	}

	/**
	 * Backwards compatibility for some database properties
	 *
	 * This is probably still not working right, so don't count on it yet.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		return isset( $this->{$key} )
			? $this->{$key}
			: null;
	}

	/**
	 * Return whether the main loading class has been instantiated or not.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean True if instantiated. False if not.
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

		// Plugin Version.
		if ( ! defined( 'SC_PLUGIN_VERSION' ) ) {
			define( 'SC_PLUGIN_VERSION', '2.0.5' );
		}

		// Plugin Root File.
		if ( ! defined( 'SC_PLUGIN_FILE' ) ) {
			define( 'SC_PLUGIN_FILE', $this->file );
		}

		// Plugin Base Name.
		if ( ! defined( 'SC_PLUGIN_BASE' ) ) {
			define( 'SC_PLUGIN_BASE', plugin_basename( SC_PLUGIN_FILE ) );
		}

		// Plugin Folder Path.
		if ( ! defined( 'SC_PLUGIN_DIR' ) ) {
			define( 'SC_PLUGIN_DIR', plugin_dir_path( SC_PLUGIN_FILE ) . 'sugar-event-calendar/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'SC_PLUGIN_URL' ) ) {
			define( 'SC_PLUGIN_URL', plugin_dir_url( SC_PLUGIN_FILE )  . 'sugar-event-calendar/' );
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

		// Admin specific
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->include_admin();

		// Front-end specific
		} else {
			$this->include_frontend();
		}

		// Lite
		$this->include_lite();

		// Standard
		$this->include_standard();
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
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/class-base.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/class-table.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/class-query.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/class-column.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/class-row.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/engine/class-schema.php';

		// Database Queries
		require_once SC_PLUGIN_DIR . 'includes/classes/database/queries/class-compare.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/queries/class-date.php';

		// Events Databases
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/class-query.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/class-row.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/class-schema.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/class-table-events.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/database/events/class-table-eventmeta.php';

		// Events Helpers
		require_once SC_PLUGIN_DIR . 'includes/classes/objects/class-wp-event-schema.php';

		// Term Colors
		require_once SC_PLUGIN_DIR . 'includes/classes/term-colors/class-term-meta-ui.php';
		require_once SC_PLUGIN_DIR . 'includes/classes/term-colors/class-term-colors.php';

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
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/shortcodes.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/widgets.php';
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/hooks.php';

		// Common files
		require_once SC_PLUGIN_DIR . 'includes/common/time.php';
		require_once SC_PLUGIN_DIR . 'includes/common/color.php';
		require_once SC_PLUGIN_DIR . 'includes/common/preferences.php';
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
		require_once SC_PLUGIN_DIR . 'includes/admin/meta-boxes/class-wp-walker-category-radio.php';

		// Legacy
		require_once SC_PLUGIN_DIR . 'includes/admin/settings.php';

		// Maybe include front-end on AJAX or add/edit post page to load
		// shortcodes, widgets, and more...
		if ( wp_doing_ajax() || ( ! empty( $GLOBALS['pagenow'] ) && ( 'post.php' === $GLOBALS['pagenow'] ) ) ) {
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
		require_once SC_PLUGIN_DIR . 'includes/themes/legacy/scripts.php';
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
			if ( substr( $plugin, -4 ) == '.php' ) {
				$files[] = trailingslashit( $dir ) . $plugin;
			}
		}

		// Close the directory
		closedir( $dh );

		// Bail if no files
		if ( empty( $files ) ) {
			return;
		}

		// Sort files alphabetically
		sort( $files );

		// Include each file
		foreach ( $files as $file ) {
			include_once $file;
		}
	}
}

/**
 * Returns the plugin instance.
 *
 * The main function responsible for returning the one true plugin instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sc = sugar_calendar(); ?>
 *
 * @since 2.0.0
 * @return object|Plugin
 */
function sugar_calendar() {
	return Plugin::instance();
}
