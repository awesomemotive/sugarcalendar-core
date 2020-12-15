<?php
namespace Sugar_Calendar;

/**
 * Term Colors Class
 *
 * @since 2.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Use the Term Meta UI class
use JJJ\WP\Term\Meta\UI;

/**
 * Main Term Colors class
 *
 * @since 2.0.0
 */
final class Term_Colors extends UI {

	/**
	 * @var string Plugin version
	 */
	public $version = '4.0.0';

	/**
	 * @var string Database version
	 */
	public $db_version = 202004020001;

	/**
	 * @var string Database version
	 */
	public $db_version_key = 'wpdb_sc_term_color_version';

	/**
	 * @var string Metadata key
	 */
	public $meta_key = 'color';

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 2.0.0
	 */
	public function __construct( $file = '' ) {

		// Filter taxonomies
		add_filter( 'wp_term_color_get_taxonomies', array( $this, 'filter_taxonomies' ) );

		// Call the parent and pass the file
		parent::__construct( $file );

		// Set the URL
		$this->url = SC_PLUGIN_URL . 'includes/admin/assets/';
	}

	/** Taxonomy **************************************************************/

	/**
	 * Setup the labels.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 * @return array
	 */
	public function setup_labels() {

		// Setup the labels
		$this->labels = array(
			'singular'    => esc_html__( 'Color',  'sugar-calendar' ),
			'plural'      => esc_html__( 'Colors', 'sugar-calendar' ),
			'description' => esc_html__( 'Assign calendars a custom color to visually separate them from each-other.', 'sugar-calendar' )
		);
	}

	/**
	 * Only add colors to taxonomies that support them.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 * @return array
	 */
	public function filter_taxonomies( $args = array() ) {
		$args['colors'] = true;

		return $args;
	}

	/** Assets ****************************************************************/

	/**
	 * Enqueue quick-edit JS
	 *
	 * @since 2.0.0
	 */
	public function enqueue_scripts() {

		// Enqueue the color picker
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		// Enqueue fancy coloring; includes quick-edit
		wp_enqueue_script( 'term-color', $this->url . 'js/term-color.js', array( 'wp-color-picker' ), $this->db_version, true );
	}

	/**
	 * Sanitizing the hex color
	 *
	 * @since 2.0.0
	 *
	 * @param   string $data
	 * @return  string
	 */
	public function sanitize_callback( $data = '' ) {
		return sugar_calendar_sanitize_hex_color( $data );
	}

	/**
	 * Add help tabs for `color` column
	 *
	 * @since 2.0.0
	 */
	public function help_tabs() {
		get_current_screen()->add_help_tab( array(
			'id'      => 'wp_term_color_help_tab',
			'title'   => esc_html__( 'Color', 'sugar-calendar' ),
			'content' => '<p>' . esc_html__( 'Calendars can have unique colors to help separate them from each other.', 'sugar-calendar' ) . '</p>',
		) );
	}

	/**
	 * Align custom `color` column
	 *
	 * @since 2.0.0
	 */
	public function admin_head() {
		?>

		<style type="text/css">
			.column-color {
				width: 74px;
			}
			.term-color {
				height: 25px;
				width: 25px;
				display: inline-block;
				border: 2px solid #eee;
				border-radius: 100%;
			}<?php

			// Change some column widths
			if ( ! empty( $this->taxonomies ) ) :
				foreach ( $this->taxonomies as $tax ) :

			?>
			body.taxonomy-<?php echo $tax; ?> .wp-list-table .column-slug {
				width: 17%;
			}
			body.taxonomy-<?php echo $tax; ?> .wp-list-table .column-description {
				width: 23%;
			}<?php

				endforeach;
			endif; ?>
		</style>

		<?php
	}

	/**
	 * Return the formatted output for the column row
	 *
	 * @since 2.0.0
	 *
	 * @param string $meta
	 */
	protected function format_output( $meta = '' ) {
		return '<i class="term-color" data-color="' . esc_attr( $meta ) . '" style="background-color: ' . esc_attr( $meta ) . '"></i>';
	}
}
