<?php
namespace Sugar_Calendar\Tests\Legacy;

/**
 * Tests for the admin tools functions.
 *
 * @group settings
 */
class Settings extends \WP_UnitTestCase {

	public $max = 15;

	/**
	 * Set up
	 */
	public function setUp() {

		// Get regisitered post type & taxonomy
		$pt       = sugar_calendar_get_event_post_type_id();
		$post_ids = array();

		// Create 30 posts
		for ( $x = 1; $x <= $this->max; $x++ ) {

			// Post
			$post_ids[] = $post_id = (int) wp_insert_post( array(
				'post_author' => 1,
				'post_title'  => 'A birthday',
				'post_type'   => $pt,
				'post_status' => 'publish'
			) );

			// Event
			sugar_calendar_add_event( array(
				'object_id'      => $post_id,
				'object_type'    => 'post',
				'object_subtype' => $pt,
				'title'          => 'A birthday',
				'status'         => 'publish',
				'start'          => '1979-06-' . $x . ' 03:33:00',
				'end'            => '1979-06-' . $x . ' 03:33:00',
				'all_day'        => true,
				'recurrence'     => 'yearly'
			) );
		}
	}

	/**
	 * Tear down
	 */
	public function tearDown() {

		// Get all events
		$events = sugar_calendar_get_events( array(
			'number' => 0
		) );

		// Loop through events and delete them all
		foreach ( $events as $event ) {
			sugar_calendar_delete_event( $event->id );
			wp_delete_post( $event->object_id );
		}

		// Delete the number-of-events option
		delete_option( 'sc_number_of_events' );
	}

	/**
	 * Five events
	 *
	 * @group settings
	 */
	public function test_number_of_events_5() {

		// Set number to 5
		update_option( 'sc_number_of_events', 5 );

		// Get the number
		$number = sc_get_number_of_events();

		// Get all events before today by the term
		$events = sugar_calendar_get_events( array(
			'no_found_rows'  => true,
			'number'         => $number,
			'object_type'    => 'post',
			'object_subtype' => sugar_calendar_get_event_post_type_id(),
			'status'         => 'publish',
			'orderby'        => 'start',
			'order'          => 'ASC',
			'start_query'    => array(
				'before'    => '1979-07-01',
				'inclusive' => true,
			)
		) );

		$this->assertSame( count( $events ), $number );
	}

	/**
	 * Ten events
	 *
	 * @group settings
	 */
	public function test_number_of_events_10() {

		// Set number to 5
		update_option( 'sc_number_of_events', 10 );

		// Get the number
		$number = sc_get_number_of_events();

		// Get all events before today by the term
		$events = sugar_calendar_get_events( array(
			'no_found_rows'  => true,
			'number'         => $number,
			'object_type'    => 'post',
			'object_subtype' => sugar_calendar_get_event_post_type_id(),
			'status'         => 'publish',
			'orderby'        => 'start',
			'order'          => 'ASC',
			'start_query'    => array(
				'before'    => '1979-07-01',
				'inclusive' => true,
			)
		) );

		$this->assertSame( count( $events ), $number );
	}

	/**
	 * All events
	 *
	 * @group settings
	 */
	public function test_number_of_events_0() {

		// Set number to 5
		update_option( 'sc_number_of_events', 0 );

		// Get the number
		$number = sc_get_number_of_events();

		// Get all events before today by the term
		$events = sugar_calendar_get_events( array(
			'no_found_rows'  => true,
			'number'         => $number,
			'object_type'    => 'post',
			'object_subtype' => sugar_calendar_get_event_post_type_id(),
			'status'         => 'publish',
			'orderby'        => 'start',
			'order'          => 'ASC',
			'start_query'    => array(
				'before'    => '1979-07-01',
				'inclusive' => true,
			)
		) );

		$this->assertSame( count( $events ), $this->max );
	}
}
