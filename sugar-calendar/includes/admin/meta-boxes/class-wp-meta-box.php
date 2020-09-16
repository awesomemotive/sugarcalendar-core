<?php
/**
 * Meta-box Class
 *
 * @package Plugins/Site/Event/Admin/Metaboxes
 */
namespace Sugar_Calendar\Admin\Editor\Meta;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main meta box class for interfacing with the events and eventmeta database
 * tables.
 *
 * @since 2.0.0
 */
class Box {

	/**
	 * Sections
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $sections = array();

	/**
	 * ID of the currently selected section
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $current_section = 'duration';

	/**
	 * The event for this meta box
	 *
	 * @since 2.0.0
	 *
	 * @var Event
	 */
	public $event = false;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

	}

	/**
	 * Setup the meta box for the current post
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post
	 */
	public function setup_post( $post = null ) {
		$this->event = $this->get_post_event_data( $post );
	}

	/**
	 * Setup default sections
	 *
	 * @since 2.0.3
	 */
	public function setup_sections() {

		// Duration
		$this->add_section( array(
			'id'       => 'duration',
			'label'    => esc_html__( 'Duration', 'sugar-calendar' ),
			'icon'     => 'clock',
			'order'    => 10,
			'callback' => __NAMESPACE__ . '\\section_duration'
		) );

		// Location
		$this->add_section( array(
			'id'       => 'location',
			'label'    => esc_html__( 'Location', 'sugar-calendar' ),
			'icon'     => 'location',
			'order'    => 50,
			'callback' => __NAMESPACE__ . '\\section_location'
		) );

		// Legacy support
		if ( has_action( 'sc_event_meta_box_before' ) || has_action( 'sc_event_meta_box_after' ) ) {

			// Legacy
			$this->add_section( array(
				'id'       => 'legacy',
				'label'    => esc_html__( 'Other', 'sugar-calendar' ),
				'icon'     => 'admin-settings',
				'order'    => 200,
				'callback' => __NAMESPACE__ . '\\section_legacy'
			) );
		}

		// Allow actions to add sections
		do_action( 'sugar_calendar_admin_meta_box_setup_sections', $this );
	}

	/**
	 * Add a section
	 *
	 * @since 2.0.0
	 *
	 * @param array $section
	 */
	public function add_section( $section = array() ) {

		// Bail if empty or not array
		if ( empty( $section ) || ! is_array( $section ) ) {
			return;
		}

		// Construct the section
		$section = new Section( $section );

		// Bail if section was not created
		if ( empty( $section->id ) ) {
			return;
		}

		// Add the section
		$this->sections[ $section->id ] = $section;

		// Always resort after adding
		$this->sort_sections();
	}

	/**
	 * Sort sections
	 *
	 * @since 2.0.18
	 *
	 * @param string $orderby
	 * @param string $order
	 */
	public function sort_sections( $orderby = 'order', $order = 'ASC' ) {
		$this->sections = wp_list_sort( $this->sections, $orderby, $order );
	}

	/**
	 * Get all sections, and filter them
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	private function get_all_sections() {
		return (array) apply_filters( 'sugar_calendar_admin_meta_box_sections', $this->sections, $this );
	}

	/**
	 * Is a section the current section?
	 *
	 * @since 2.0.0
	 *
	 * @param string $section_id
	 *
	 * @return bool
	 */
	private function is_current_section( $section_id = '' ) {
		return ( $section_id === $this->current_section );
	}

	/**
	 * Output the nonce field for the meta box
	 *
	 * @since 2.0.0
	 */
	private function nonce_field() {
		wp_nonce_field( 'sugar_calendar_nonce', 'sc_mb_nonce', true );
	}

	/**
	 * Display links to all sections
	 *
	 * @since 2.0.0
	 */
	private function display_all_section_links( $tabs = array() ) {
		echo $this->get_all_section_links( $tabs );
	}

	/**
	 * Get event data for a post
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post
	 * @return array
	 */
	private function get_post_event_data( $post = 0 ) {
		return sugar_calendar_get_event_by_object( $post->ID );
	}

	/**
	 * Display all section contents
	 *
	 * @since 2.0.0
	 */
	private function display_all_section_contents( $tabs = array() ) {
		echo $this->get_all_section_contents( $tabs );
	}

	/**
	 * Get the contents of all links as HTML
	 *
	 * @since 2.0.0
	 *
	 * @param array $sections
	 *
	 * @return string
	 */
	private function get_all_section_links( $sections = array() ) {
		ob_start();

		// Loop through sections
		foreach ( $sections as $section ) :

			// Special selected section
			$selected = $this->is_current_section( $section->id )
				? 'aria-selected="true"'
				: 'aria-selected="false"'; ?>

			<button type="button" id="sc-tab-<?php echo esc_attr( $section->id ); ?>" role="tab" aria-labelledby="sc-label-<?php echo esc_attr( $section->id ); ?>" aria-controls="sc-section-<?php echo esc_attr( $section->id ); ?>" <?php echo $selected; ?>>
				<i class="dashicons dashicons-<?php echo esc_attr( $section->icon ); ?>"></i>
				<span class="label" id="sc-label-<?php echo esc_attr( $section->id ); ?>"><?php echo esc_attr( $section->label ); ?></span>
			</button>

		<?php endforeach;

		// Return output buffer
		return ob_get_clean();
	}

	/**
	 * Get the contents of all sections as HTML
	 *
	 * @since 2.0.0
	 *
	 * @param array $sections
	 *
	 * @return string HTML for all section contents
	 */
	private function get_all_section_contents( $sections = array() ) {
		ob_start();

		// Loop through sections
		foreach ( $sections as $section ) :

			// Special selected section
			$selected = ! $this->is_current_section( $section->id )
				? 'style="display: none;"'
				: ''; ?>

			<div id="sc-section-<?php echo esc_attr( $section->id ); ?>" role="tabpanel" tabindex="0" aria-labelledby="sc-tab-<?php echo esc_attr( $section->id ); ?>" class="section-content" <?php echo $selected; ?>><?php

				$this->get_section_contents( $section );

			?></div>

		<?php endforeach;

		// Return output buffer
		return ob_get_clean();
	}

	/**
	 * Get the contents for a specific section
	 *
	 * @since 2.0.18
	 *
	 * @param Section $section
	 */
	private function get_section_contents( $section = '' ) {

		// Setup the hook name
		$hook = 'sugar_calendar_' . $section->id . 'meta_box_contents';

		// Callback
		if ( ! empty( $section->callback ) && is_callable( $section->callback ) ) {
			call_user_func( $section->callback, $this->event );

		// Action
		} elseif ( has_action( $hook ) ) {
			do_action( $hook, $this );
		}

	}

	/**
	 * Output the meta-box contents
	 *
	 * @since 2.0.0
	 */
	public function display() {
		$sections = $this->get_all_sections();
		$event_id = $this->event->id;

		// Start an output buffer
		ob_start(); ?>

		<div class="sugar-calendar-wrap">
			<div class="sc-vertical-sections" role="tablist" aria-orientation="vertical" aria-label="<?php esc_html_e( 'Event Options', 'sugar-calendar' ); ?>">
				<div class="section-nav">
					<?php $this->display_all_section_links( $sections ); ?>
				</div>

				<div class="section-wrap">
					<?php $this->display_all_section_contents( $sections ); ?>
				</div>
			</div>
			<?php $this->nonce_field(); ?>
			<input type="hidden" name="sc-event-id" value="<?php echo esc_attr( $event_id ); ?>" />
		</div>

		<?php

		// Output buffer
		echo ob_get_clean();
	}
}
