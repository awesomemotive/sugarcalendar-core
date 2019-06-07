<?php
/**
 * Meta Box Class
 *
 * @package Plugins/Site/MetaBox
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
			'callback' => array( $this, 'section_duration' )
		) );

		// Location
		$this->add_section( array(
			'id'       => 'location',
			'label'    => esc_html__( 'Location', 'sugar-calendar' ),
			'icon'     => 'location',
			'order'    => 80,
			'callback' => array( $this, 'section_location' )
		) );

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
		$this->sections[] = (object) wp_parse_args( $section, array(
			'id'       => '',
			'label'    => '',
			'icon'     => 'admin-settings',
			'order'    => 50,
			'callback' => ''
		) );

		// Resort by order
		$this->sections = wp_list_sort( $this->sections, 'order', 'ASC' );
	}

	/**
	 * Get all sections, and filter them
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	private function get_all_sections() {
		return (array) apply_filters( 'sugar_calendar', $this->sections, $this );
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
				: ''; ?>

			<li class="section-title" <?php echo $selected; ?>>
				<a href="#<?php echo esc_attr( $section->id ); ?>">
					<i class="dashicons dashicons-<?php echo esc_attr( $section->icon ); ?>"></i>
					<span class="label"><?php echo esc_attr( $section->label ); ?></span>
				</a>
			</li>

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

			<div id="<?php echo esc_attr( $section->id ); ?>" class="section-content" <?php echo $selected; ?>><?php

				// Callback or action
				if ( ! empty( $section->callback ) && is_callable( $section->callback ) ) :
					call_user_func( $section->callback, $this->event );
				else :
					do_action( 'sugar_calendar_' . $section->id . 'meta_box_contents', $this );
				endif;

			?></div>

		<?php endforeach;

		// Return output buffer
		return ob_get_clean();
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
			<div class="sc-vertical-sections">
				<ul class="section-nav">
					<?php $this->display_all_section_links( $sections ); ?>
				</ul>

				<div class="section-wrap">
					<?php $this->display_all_section_contents( $sections ); ?>
				</div>
				<br class="clear">
			</div>
			<?php $this->nonce_field(); ?>
			<input type="hidden" name="sc-event-id" value="<?php echo esc_attr( $event_id ); ?>" />
		</div>

		<?php

		// Output buffer
		echo ob_get_clean();
	}

	/**
	 * Output the event duration meta-box
	 *
	 * @since  0.2.3
	 */
	public function section_duration( $event = null ) {

		// Defaults
		$date = $hour = $minute = $end_date = $end_hour = $end_minute = '';

		// Default AM/PM
		$am_pm = $end_am_pm = '';

		/** All Day ***********************************************************/

		$all_day = ! empty( $event->all_day )
			? (bool) $event->all_day
			: false;

		$hidden = ( true === $all_day )
			? ' style="display: none;"'
			: '';

		/** Ends **************************************************************/

		// Get date_time
		$end_date_time = ! empty( $event->end ) && ( $event->start !== $event->end )
			? strtotime( $event->end )
			: null;

		// Only if end isn't empty
		if ( ! empty( $end_date_time ) ) {

			// Date
			$end_date = date( 'Y-m-d', $end_date_time );

			// Only if not all-day
			if ( empty( $all_day ) ) {

				// Hour
				$end_hour = date( 'h', $end_date_time );
				if ( empty( $end_hour ) ) {
					$end_hour = '';
				}

				// Minute
				$end_minute = date( 'i', $end_date_time );
				if ( empty( $end_hour ) || empty( $end_minute )) {
					$end_minute = '';
				}

				// Day/night
				$end_am_pm = date( 'a', $end_date_time );
				if ( empty( $end_hour ) && empty( $end_minute ) ) {
					$end_am_pm = '';
				}
			}
		}

		/** Starts ************************************************************/

		// Get date_time
		if ( ! empty( $_GET['start_day'] ) ) {
			$date_time = (int) $_GET['start_day'];
		} else {
			$date_time = ! empty( $event->start )
				? strtotime( $event->start )
				: null;
		}

		// Date
		if ( ! empty( $date_time ) ) {
			$date = date( 'Y-m-d', $date_time );

			// Only if not all-day
			if ( empty( $all_day ) ) {

				// Hour
				$hour = date( 'h', $date_time );
				if ( empty( $hour ) ) {
					$hour = '';
				}

				// Minute
				$minute = date( 'i', $date_time );
				if ( empty( $hour ) || empty( $minute ) ) {
					$minute = '';
				}

				// Day/night
				$am_pm = date( 'a', $date_time );
				if ( empty( $hour ) && empty( $minute ) ) {
					$am_pm = '';
				}

			// All day
			} elseif ( $date === $end_date ) {
				$end_date = '';
			}
		}

		/** Let's Go! *********************************************************/

		// Start an output buffer
		ob_start(); ?>

		<table class="form-table rowfat">
			<tbody>
				<tr>
					<th>
						<label for="all_day" class="screen-reader-text"><?php esc_html_e( 'All Day', 'sugar-calendar' ); ?></label>
					</th>

					<td>
						<label>
							<input type="checkbox" name="all_day" id="all_day" value="1" <?php checked( $all_day ); ?> />
							<?php esc_html_e( 'All-day event', 'sugar-calendar' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th>
						<label for="start_date"><?php esc_html_e( 'Start', 'sugar-calendar'); ?></label>
					</th>

					<td>
						<input type="text" class="sugar_calendar_datepicker" name="start_date" id="start_date" value="<?php echo esc_attr( $date ); ?>" placeholder="yyyy-mm-dd" />
						<div class="event-time" <?php echo $hidden; ?>>
							<span class="sc-time-separator"><?php esc_html_e( ' at ', 'sugar-calendar' ); ?></span>
							<?php sugar_calendar_time_dropdown( array(
								'first'    => '&nbsp;',
								'id'       => 'start_time_hour',
								'name'     => 'start_time_hour',
								'items'    => sugar_calendar_get_hours(),
								'selected' => $hour
							) ); ?>
							<span class="sc-time-separator">:</span>
							<?php sugar_calendar_time_dropdown( array(
								'first'    => '&nbsp;',
								'id'       => 'start_time_minute',
								'name'     => 'start_time_minute',
								'items'    => sugar_calendar_get_minutes(),
								'selected' => $minute
							) ); ?>
							<select id="start_time_am_pm" name="start_time_am_pm" class="sc-select-chosen sc-time">
								<option value="">&nbsp;</option>
								<option value="am" <?php selected( $am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'sugar-calendar' ); ?></option>
								<option value="pm" <?php selected( $am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'sugar-calendar' ); ?></option>
							</select>
						</div>
					</td>

				</tr>

				<tr>
					<th>
						<label for="end_date"><?php esc_html_e( 'End', 'sugar-calendar'); ?></label>
					</th>

					<td>
						<input type="text" class="sugar_calendar_datepicker" name="end_date" id="end_date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="yyyy-mm-dd" />
						<div class="event-time" <?php echo $hidden; ?>>
							<span class="sc-time-separator"><?php esc_html_e( ' at ', 'sugar-calendar' ); ?></span>
							<?php sugar_calendar_time_dropdown( array(
								'first'    => '&nbsp;',
								'id'       => 'end_time_hour',
								'name'     => 'end_time_hour',
								'items'    => sugar_calendar_get_hours(),
								'selected' => $end_hour
							) ); ?>
							<span class="sc-time-separator">:</span>
							<?php sugar_calendar_time_dropdown( array(
								'first'    => '&nbsp;',
								'id'       => 'end_time_minute',
								'name'     => 'end_time_minute',
								'items'    => sugar_calendar_get_minutes(),
								'selected' => $end_minute
							) ); ?>
							<select id="end_time_am_pm" name="end_time_am_pm" class="sc-select-chosen sc-time">
								<option value="">&nbsp;</option>
								<option value="am" <?php selected( $end_am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'sugar-calendar' ); ?></option>
								<option value="pm" <?php selected( $end_am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'sugar-calendar' ); ?></option>
							</select>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<?php

		echo ob_get_clean();
	}

	/**
	 * Output the event location meta-box
	 *
	 * @since  0.2.3
	 *
	 * @param Event $event The event
	*/
	public function section_location( $event = null ) {

		// Location
		$location = $event->location;

		// Start an output buffer
		ob_start(); ?>

		<table class="form-table rowfat">
			<tbody>

				<?php if ( apply_filters( 'sugar_calendar_location', true ) ) : ?>

					<tr>
						<th>
							<label for="location"><?php esc_html_e( 'Location', 'sugar-calendar' ); ?></label>
						</th>

						<td>
							<label>
								<textarea name="location" id="location" placeholder="<?php esc_html_e( '(Optional)', 'sugar-calendar' ); ?>"><?php echo esc_textarea( $location ); ?></textarea>
							</label>
						</td>
					</tr>

				<?php endif; ?>
			</tbody>
		</table>

		<?php

		// End & flush the output buffer
		echo ob_get_clean();
	}
}
