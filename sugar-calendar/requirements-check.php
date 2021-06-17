<?php
/**
 * Requirements Checker Class.
 *
 * @since 2.0.0
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The main plugin requirements checker
 *
 * @since 2.0.0
 */
final class Requirements_Check {

	/**
	 * Plugin file
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $file = '';

	/**
	 * Plugin basename
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $base = '';

	/**
	 * Requirements array
	 *
	 * @todo Extend WP_Dependencies
	 * @var array
	 * @since 2.0.0
	 */
	private $requirements = array(

		// PHP
		'php' => array(
			'minimum' => '5.6.20',
			'name'    => 'PHP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		),

		// WordPress
		'wp' => array(
			'minimum' => '5.5.0',
			'name'    => 'WordPress',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		)
	);

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 * @param string $main_file Main plugin file
	 */
	public function __construct( $main_file = '' ) {
		if ( ! empty( $main_file ) ) {
			$this->init( $main_file );
		}
	}

	/**
	 * Initialize plugin requirements
	 *
	 * Abstracted out of the Constructor above to allow testing this class
	 *
	 * @since 2.1.2
	 * @param string $main_file Main plugin file
	 */
	public function init( $main_file = '' ) {

		// Bail if no main file passed
		if ( empty( $main_file ) ) {
			return;
		}

		// Setup file & base
		$this->file = $main_file;
		$this->base = plugin_basename( $this->file );

		// Always load translations
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Load or quit
		$this->met()
			? $this->load()
			: $this->quit();
	}

	/**
	 * Quit without loading
	 *
	 * @since 2.0.0
	 */
	private function quit() {
		add_action( 'admin_head',                        array( $this, 'admin_head'        ) );
		add_filter( "plugin_action_links_{$this->base}", array( $this, 'plugin_row_links'  ) );
		add_action( "after_plugin_row_{$this->base}",    array( $this, 'plugin_row_notice' ) );
	}

	/** Specific Methods ******************************************************/

	/**
	 * Load normally
	 *
	 * @since 2.0.0
	 */
	private function load() {

		// Class string
		$class = 'Sugar_Calendar\\Plugin';

		// Maybe include the bundled bootstrapper
		if ( ! class_exists( $class ) ) {
			require_once dirname( $this->file ) . '/sugar-calendar/sugar-calendar.php';
		}

		// Maybe hook-in the bootstrapper
		if ( class_exists( $class ) ) {

			// Bootstrap to plugins_loaded before priority 10 to make sure
			// add-ons are loaded after us.
			add_action( 'plugins_loaded', array( $this, 'bootstrap' ), 8 );

			// Register the activation hook
			register_activation_hook( $this->file, array( $this, 'install' ) );

			// Register the deactivation hook
			register_deactivation_hook( $this->file, array( $this, 'uninstall' ) );
		}
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @since 2.0.0
	 */
	public function install() {

		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		// Add the cron jobs
		sugar_calendar_cron_hook( $this->is_network_wide() );
	}

	/**
	 * Uninstall, usually on a deactivation hook.
	 *
	 * @since 2.0.0
	 */
	public function uninstall() {

		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		// Remove the cron hooks
		sugar_calendar_cron_unhook( $this->is_network_wide() );
	}

	/**
	 * Bootstrap everything.
	 *
	 * @since 2.0.0
	 */
	public function bootstrap() {
		Plugin::instance( $this->file );
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function unmet_requirements_url() {
		return 'https://sugarcalendar.com';
	}

	/**
	 * Plugin specific text to quickly explain what's wrong.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function unmet_requirements_text() {
		esc_html_e( 'This plugin is not fully active.', 'sugar-calendar' );
	}

	/**
	 * Plugin specific text to describe a single unmet requirement.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function unmet_requirements_description_text() {
		return esc_html__( 'Requires %s (%s), but (%s) is installed.', 'sugar-calendar' );
	}

	/**
	 * Plugin specific text to describe a single missing requirement.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function unmet_requirements_missing_text() {
		return esc_html__( 'Requires %s (%s), but it appears to be missing.', 'sugar-calendar' );
	}

	/**
	 * Plugin specific text used to link to an external requirements page.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function unmet_requirements_link() {
		return esc_html__( 'Requirements', 'sugar-calendar' );
	}

	/**
	 * Plugin specific aria label text to describe the requirements link.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function unmet_requirements_label() {
		return esc_html__( 'Sugar Calendar Requirements', 'sugar-calendar' );
	}

	/**
	 * Plugin specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function unmet_requirements_name() {
		return 'sc-requirements';
	}

	/**
	 * Is plugin activation network wide?
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	private function is_network_wide() {
		return ! empty( $_GET['networkwide'] )
			? (bool) $_GET['networkwide']
			: false;
	}

	/** Helpers ***************************************************************/

	/**
	 * Helper method to re-determine if WordPress 5.5 is showing the auto-update
	 * column in the Plugins list table.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	private function showing_auto_updates() {

		// Bail if auto updates do not exist
		if ( ! function_exists( 'wp_is_auto_update_enabled_for_type' ) ) {
			return false;
		}

		// Bail if not enabled
		if ( ! wp_is_auto_update_enabled_for_type( 'plugin' ) ) {
			return false;
		}

		// Bail if current user cannot update plugins
		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		// Bail if in network admin
		if ( is_multisite() && ! is_blog_admin() ) {
			return false;
		}

		// Allowed auto-update statuses
		$allowed_statuses = array(
			'all',
			'active',
			'inactive',
			'recently_activated',
			'upgrade',
			'search',
			'paused',
			'auto-update-enabled',
			'auto-update-disabled'
		);

		// Status
		$status = ! empty( $_REQUEST['plugin_status'] )
			? sanitize_key( $_REQUEST['plugin_status'] )
			: 'all';

		// Is status allowed?
		$allowed = in_array( $status, $allowed_statuses, true );

		// Return if allowed
		return $allowed;
	}

	/**
	 * How many columns should the description column span?
	 *
	 * For plugin_row_notice()
	 *
	 * @since 2.0.0
	 */
	private function description_colspan() {
		return $this->showing_auto_updates()
			? 2
			: 1;
	}

	/** Agnostic Methods ******************************************************/

	/**
	 * Plugin agnostic method to output the additional plugin row
	 *
	 * @since 2.0.0
	 */
	public function plugin_row_notice() {
		?><tr class="active <?php echo esc_attr( $this->unmet_requirements_name() ); ?>-row">
		<th class="check-column">
			<span class="dashicons dashicons-warning"></span>
		</th>
		<td class="column-primary">
			<?php $this->unmet_requirements_text(); ?>
		</td>
		<td class="column-description" colspan="<?php echo absint( $this->description_colspan() ); ?>">
			<?php $this->unmet_requirements_description(); ?>
		</td>
		</tr><?php
	}

	/**
	 * Plugin agnostic method used to output all unmet requirement information
	 *
	 * @since 2.0.0
	 */
	private function unmet_requirements_description() {
		foreach ( $this->requirements as $properties ) {
			if ( empty( $properties['met'] ) ) {
				$this->unmet_requirement_description( $properties );
			}
		}
	}

	/**
	 * Plugin agnostic method to output specific unmet requirement information
	 *
	 * @since 2.0.0
	 * @param array $requirement
	 */
	private function unmet_requirement_description( $requirement = array() ) {

		// Requirement exists, but is out of date
		if ( ! empty( $requirement['exists'] ) ) {
			$text = sprintf(
				$this->unmet_requirements_description_text(),
				'<strong>' . esc_html( $requirement['name']    ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['current'] ) . '</strong>'
			);

			// Requirement could not be found
		} else {
			$text = sprintf(
				$this->unmet_requirements_missing_text(),
				'<strong>' . esc_html( $requirement['name']    ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>'
			);
		}

		// Output the description (unescaped, contains HTML)
		echo wpautop( $text );
	}

	/**
	 * Plugin agnostic method to output unmet requirements styling
	 *
	 * @since 2.0.0
	 */
	public function admin_head() {

		// Get the requirements row name
		$name = $this->unmet_requirements_name(); ?>

		<style id="<?php echo esc_attr( $name ); ?>">
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] td,
			.plugins .<?php echo esc_html( $name ); ?>-row th,
			.plugins .<?php echo esc_html( $name ); ?>-row td {
				background: #fff5f5;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th {
				box-shadow: none;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row th span {
				margin-left: 6px;
				color: #dc3232;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins .<?php echo esc_html( $name ); ?>-row th.check-column {
				border-left: 4px solid #dc3232 !important;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p {
				margin: 0;
				padding: 0;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p:not(:last-of-type) {
				margin-bottom: 8px;
			}
		</style>
		<?php
	}

	/**
	 * Plugin agnostic method to add the "Requirements" link to row actions
	 *
	 * @since 2.0.0
	 * @param array $links
	 * @return array
	 */
	public function plugin_row_links( $links = array() ) {

		// Add the Requirements link
		$links['requirements'] =
			'<a href="' . esc_url( $this->unmet_requirements_url() ) . '" aria-label="' . esc_attr( $this->unmet_requirements_label() ) . '">'
			. esc_html( $this->unmet_requirements_link() )
			. '</a>';

		// Return links with Requirements link
		return $links;
	}

	/** Checkers **************************************************************/

	/**
	 * Plugin specific requirements checker
	 *
	 * @since 2.0.0
	 */
	private function check() {

		// Loop through requirements
		foreach ( $this->requirements as $dependency => $properties ) {

			// Which dependency are we checking?
			switch ( $dependency ) {

				// PHP
				case 'php' :
					$version = phpversion();
					break;

				// WP
				case 'wp' :
					$version = get_bloginfo( 'version' );
					break;

				// Unknown
				default :
					$version = false;
					break;
			}

			// Merge to original array
			if ( ! empty( $version ) ) {
				$this->requirements[ $dependency ] = array_merge( $this->requirements[ $dependency ], array(
					'current' => $version,
					'checked' => true,
					'met'     => version_compare( $version, $properties['minimum'], '>=' )
				) );
			}
		}
	}

	/**
	 * Have all requirements been met?
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function met() {

		// Run the check
		$this->check();

		// Default to true (any false below wins)
		$retval  = true;
		$to_meet = wp_list_pluck( $this->requirements, 'met' );

		// Look for unmet dependencies, and exit if so
		foreach ( $to_meet as $met ) {
			if ( empty( $met ) ) {
				$retval = false;
				continue;
			}
		}

		// Return
		return $retval;
	}

	/** Translations **********************************************************/

	/**
	 * Plugin specific text-domain loader.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'sugar-calendar' );
	}
}
