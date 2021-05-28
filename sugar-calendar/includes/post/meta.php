<?php
/**
 * Event Meta
 *
 * @package Plugins/Site/Events/Meta
 */
namespace Sugar_Calendar\Posts\Meta;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Backwards compatibility for converting post-meta calls to using the new
 * events database table and the datetime columns.
 *
 * @since 2.0.0
 */
final class Back_Compat {

	/**
	 * Array of post-meta keys used to invoke back-compat support
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private $back_compat_keys = array(

		// Start
		'sc_event_date' => array(
			'column' => 'start',
			'read'   => 'U',
			'write'  => 'Y-m-d'
		),
		'sc_event_date_time' => array(
			'column' => 'start',
			'read'   => 'U',
			'write'  => 'Y-m-d H:i:s'
		),
		'sc_event_day' => array(
			'column' => 'start',
			'read'   => 'D',
			'write'  => 'd'
		),
		'sc_event_month' => array(
			'column' => 'start',
			'read'   => 'm',
			'write'  => 'm'
		),
		'sc_event_year' => array(
			'column' => 'start',
			'read'   => 'Y',
			'write'  => 'Y'
		),
		'sc_event_time_hour' => array(
			'column' => 'start',
			'read'   => 'h',
			'write'  => 'H'
		),
		'sc_event_time_minute' => array(
			'column' => 'start',
			'read'   => 'i',
			'write'  => 'i'
		),
		'sc_event_time_am_pm' => array(
			'column' => 'start',
			'read'   => 'a',
			'write'  => false
		),
		'sc_event_day_of_week' => array(
			'column' => 'start',
			'read'   => 'w',
			'write'  => false
		),
		'sc_event_day_of_month' => array(
			'column' => 'start',
			'read'   => 'd',
			'write'  => false
		),
		'sc_event_day_of_year' => array(
			'column' => 'start',
			'read'   => 'z',
			'write'  => false
		),
		'sc_event_timezone' => array(
			'column' => 'start_tz',
			'read'   => null,
			'write'  => null
		),

		// End
		'sc_event_end_date' => array(
			'column' => 'end',
			'read'   => 'U',
			'write'  => 'Y-m-d'
		),
		'sc_event_end_date_time' => array(
			'column' => 'end',
			'read'   => 'U',
			'write'  => 'Y-m-d H:i:s'
		),
		'sc_event_end_day' => array(
			'column' => 'end',
			'read'   => 'D',
			'write'  => 'd'
		),
		'sc_event_end_month' => array(
			'column' => 'end',
			'read'   => 'm',
			'write'  => 'm'
		),
		'sc_event_end_year' => array(
			'column' => 'end',
			'read'   => 'Y',
			'write'  => 'Y'
		),
		'sc_event_end_time_hour' => array(
			'column' => 'end',
			'read'   => 'h',
			'write'  => 'H'
		),
		'sc_event_end_time_minute' => array(
			'column' => 'end',
			'read'   => 'i',
			'write'  => 'i'
		),
		'sc_event_end_time_am_pm' => array(
			'column' => 'end',
			'read'   => 'a',
			'write'  => false
		),
		'sc_event_end_day_of_week' => array(
			'column' => 'end',
			'read'   => 'w',
			'write'  => false
		),
		'sc_event_end_day_of_month' => array(
			'column' => 'end',
			'read'   => 'd',
			'write'  => false
		),
		'sc_event_end_day_of_year' => array(
			'column' => 'start',
			'read'   => 'z',
			'write'  => false
		),
		'sc_event_end_timezone' => array(
			'column' => 'end_tz',
			'read'   => null,
			'write'  => null
		),

		// Recurring
		'sc_event_recurring' => array(
			'column' => 'recurrence',
			'read'   => null,
			'write'  => null
		),
		'sc_recur_until' => array(
			'column' => 'recurrence_end',
			'read'   => 'U',
			'write'  => 'Y-m-d H:i:s'
		),
		'sc_recur_timezone' => array(
			'column' => 'recurrence_end_tz',
			'read'   => null,
			'write'  => null
		),
		'sc_all_recurring' => array(
			'column' => false,
			'read'   => false,
			'write'  => false
		)
	);

	/**
	 * The current post ID, set by ::iterator()
	 *
	 * @since 2.0.0
	 * @var int
	 */
	private $post_id = 0;

	/**
	 * The current meta key, set by ::iterator()
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $meta_key = '';

	/**
	 * The current event, set by ::iterator()
	 *
	 * @since 2.0.0
	 * @var object
	 */
	private $event = false;

	/** Methods ***************************************************************/

	/**
	 * Add filters
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_hooks' ) );
	}

	/**
	 * Add hooks
	 *
	 * @since 2.0.0
	 */
	public function add_hooks() {
		add_filter( 'add_post_metadata',    array( $this, 'add_post_meta'    ), 10, 4 );
		add_filter( 'delete_post_metadata', array( $this, 'delete_post_meta' ), 10, 4 );
		add_filter( 'get_post_metadata',    array( $this, 'get_post_meta'    ), 10, 4 );
		add_filter( 'update_post_metadata', array( $this, 'update_post_meta' ), 10, 4 );
	}

	/**
	 * Filter add_post_meta() calls and maybe add event data instead.
	 *
	 * This method is unlikely to be used, as add_post_meta() was not directly
	 * called in previous versions. It can maybe be unhooked.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed   $override
	 * @param int     $post_id
	 * @param string  $meta_key
	 * @param mixed   $meta_value
	 *
	 * @return mixed
	 */
	public function add_post_meta( $override = null, $post_id = 0, $meta_key = '', $meta_value = '' ) {
		return $this->update_post_meta( $override, $post_id, $meta_key, $meta_value );
	}

	/**
	 * Filter delete_post_meta() calls and maybe delete event data instead.
	 *
	 * This method is unlikely to be used, as delete_post_meta() was not directly
	 * called in previous versions. It can maybe be unhooked.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed   $override
	 * @param int     $post_id
	 * @param string  $meta_key
	 * @param mixed   $meta_value
	 *
	 * @return mixed
	 */
	public function delete_post_meta( $override = null, $post_id = 0, $meta_key = '', $meta_value = '' ) {
		return $this->update_post_meta( $override, $post_id, $meta_key, '' );
	}

	/**
	 * Filter get_post_meta() calls and maybe get event data instead.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed   $override
	 * @param int     $post_id
	 * @param string  $meta_key
	 * @param bool    $single
	 *
	 * @return mixed
	 */
	public function get_post_meta( $override = null, $post_id = 0, $meta_key = '', $single = false ) {
		$this->iterate( $post_id, $meta_key );

		// Bail if skipping
		if ( $this->skip() ) {
			return $this->reset( $override );
		}

		// Return the read value
		return $this->read();
	}

	/**
	 * Filter update_post_meta() calls and maybe update event data instead.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed  $override
	 * @param int    $post_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 *
	 * @return mixed
	 */
	public function update_post_meta( $override = null, $post_id = 0, $meta_key = '', $meta_value = '' ) {
		$this->iterate( $post_id, $meta_key );

		// Bail if skipping
		if ( $this->skip() ) {
			return $this->reset( $override );
		}

		// Return the write value
		return $this->write( $meta_value );
	}

	/** Helpers ***************************************************************/

	/**
	 * Read from the events database table, formatted for back-compat.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	private function read() {
		$key = $this->get_current_key();

		// Default return value
		$retval = null;

		// Bail if derived, and no longer read
		if ( false === $key['read'] ) {
			return $retval;
		}

		// Bail if column is undefined
		if ( empty( $key['column'] ) ) {
			return $retval;
		}

		// Get the column
		$column = $key['column'];

		// If null, use the raw value
		if ( is_null( $key['read'] ) ) {
			$retval = $this->event->{$column};

		// Format of column is a valid datetime value
		} elseif ( ! $this->is_datetime_empty( $this->event->{$column} ) ) {
			$date   = strtotime( $this->event->{$column} );
			$retval = gmdate( $key['read'], $date );

		// Otherwise false
		} else {
			$retval = false;
		}

		// Return the formatted value
		return $retval;
	}

	/**
	 * Write to the events database table, formatted for back-compat.
	 *
	 * @since 2.0.0
	 *
	 * @param string $meta_value
	 *
	 * @return mixed
	 */
	private function write( $meta_value = '' ) {

		// Get the key
		$key = $this->get_current_key();

		// Bail if derived, and no longer written
		if ( false === $key['write'] ) {
			return null;
		}

		// Bail if column is undefined
		if ( empty( $key['column'] ) ) {
			return null;
		}

		// Get the key column
		$column = $key['column'];

		// If null, use the raw value
		if ( is_null( $key['write'] ) ) {
			$writeval = $meta_value;

		// Otherwise assume a date format
		} else {

			// Get the event column value
			$date = strtotime( $this->event->{$column} );

			// Updating 1 part of a date
			if ( 1 === strlen( $key['write'] ) ) {

				// Define date parts
				$parts = array( 'Y', 'm', 'd', 'H', 'i', 's' );
				$dates = array();

				// Loop through parts and break date apart
				foreach ( $parts as $part ) {
					$dates[ $part ] = gmdate( $part, $date );
				}

				// Override the date part
				$dates[ $key['write'] ] = $meta_value;

				// Make the new date time
				$writeval = gmmktime(
					$dates['H'],
					$dates['i'],
					$dates['s'],
					$dates['m'],
					$dates['d'],
					$dates['Y']
				);

			// Updating entire value
			} else {
				$writeval = gmdate( $key['write'], $meta_value );
			}
		}

		// Update
		sugar_calendar_update_event( $this->event->id, array(
			$column => $writeval
		) );

		return true;
	}

	/**
	 * Set post ID and meta_key to simplify look-ups.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 */
	private function iterate( $post_id = 0, $meta_key = '' ) {
		$this->post_id  = $post_id;
		$this->meta_key = $meta_key;
	}

	/**
	 * Reset class vars
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $override
	 * @return mixed $override
	 */
	private function reset( $override = null ) {
		$this->post_id  = 0;
		$this->meta_key = '';
		$this->event    = false;

		// Return the override
		return $override;
	}

	/**
	 * Whether a meta filter should be skipped or not. Uses the meta_key to
	 * determine if the post meta key requires backwards compatibility help.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function skip() {

		// Return true during upgrades
		if ( $this->doing_upgrade() ) {
			return true;
		}

		// Return true if not a backwards compatibility
		if ( ! $this->is_back_compat_key() ) {
			return true;
		}

		// Return true if no event exists
		if ( ! $this->find_event() ) {
			return true;
		}

		// Not skipping
		return false;
	}

	/**
	 * Returns whether an upgrade is being performed or not.
	 *
	 * This ensures that meta is not mapped during the upgrade process.
	 *
	 * @since 2.0.8
	 *
	 * @return bool
	 */
	private function doing_upgrade() {
		return ! empty( $GLOBALS['sc_upgrade_meta_skip'] );
	}

	/**
	 * Returns whether a requested meta-key is in the back_compat_keys array.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function is_back_compat_key() {
		return isset( $this->back_compat_keys[ $this->meta_key ] );
	}

	/**
	 * Return the current post meta key.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	private function get_current_key() {
		return $this->back_compat_keys[ $this->meta_key ];
	}

	/**
	 * Check if a datetime value is empty
	 *
	 * @since 2.0.0
	 *
	 * @param string $date
	 * @return bool
	 */
	private function is_datetime_empty( $date = '' ) {

		// Bail if empty date
		if ( empty( $date ) || ( '0000-00-00 00:00:00' === $date ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get an event object.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	private function find_event() {

		// Bail if no post ID
		if ( empty( $this->post_id ) ) {
			return false;
		}

		// Bail early if event already loaded
		if ( ! empty( $this->event ) && ( ( 'post' === $this->event->object_type ) && ( (int) $this->post_id === (int) $this->event->object_id ) ) ) {
			return true;
		}

		// Return an event
		$event = sugar_calendar_get_event_by_object( $this->post_id, 'post' );

		// Bail if no event
		if ( ! $event->exists() ) {
			$this->event = false;
			return false;
		}

		// Set the event
		$this->event = $event;

		// Return the event object
		return true;
	}
}
