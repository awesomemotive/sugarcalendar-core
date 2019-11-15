<?php
namespace Sugar_Calendar\Tests\Post;

/**
 * Tests for the admin tools functions.
 *
 * @group tools
 */
class Taxonomy extends \WP_UnitTestCase {

	public $pt  = '';
	public $tax = '';

	public $post_id  = 0;
	public $event_id = 0;
	public $term_id  = 0;

	/**
	 * Set up
	 */
	public function setUp() {

		// Get regisitered post type & taxonomy
		$this->pt  = sugar_calendar_get_event_post_type_id();
		$this->tax = sugar_calendar_get_calendar_taxonomy_id();

		// Post
		$this->post_id = (int) wp_insert_post( array(
			'post_author' => 1,
			'post_title'  => 'A birthday',
			'post_type'   => $this->pt,
			'post_status' => 'publish'
		) );

		// Event
		$this->event_id = (int) sugar_calendar_add_event( array(
			'object_id'      => $this->post_id,
			'object_type'    => 'post',
			'object_subtype' => $this->pt,
			'title'          => 'A birthday',
			'status'         => 'publish',
			'start'          => '1979-06-17 03:33:00',
			'end'            => '1979-06-17 03:33:00',
			'all_day'        => true,
			'recurrence'     => 'yearly'
		) );

		// Create the term
		$term = wp_insert_term( 'birthday', $this->tax );

		// Set TermID
		$this->term_id = (int) $term['term_id'];

		// Add post ID to term
		wp_set_object_terms( $this->post_id, $this->term_id, $this->tax );
	}

	/**
	 * Tear down
	 */
	public function tearDown() {
		sugar_calendar_delete_event( $this->event_id );
		wp_delete_object_term_relationships( $this->post_id, $this->tax );
		wp_delete_post( $this->post_id );
		wp_delete_term( 'birthday', $this->tax );
	}

	/**
	 * @group taxonomy
	 */
	public function test_get_past_events_in_calendar() {

		// Get the term
		$term = get_term( $this->term_id, $this->tax );

		// Get all events before today by the term
		$events = sugar_calendar_get_events( array(
			'object_id'      => $this->post_id,
			'object_type'    => 'post',
			'object_subtype' => $this->pt,
			'status'         => 'publish',
			'orderby'        => 'start',
			'order'          => 'ASC',
			'start_query'    => array(
				'before'    => date( 'Y-m-d' ),
				'inclusive' => true,
			),
			$this->tax       => $term->slug
		) );

		// Get the first event ID
		$event_id = (int) $events[0]->id;

		$this->assertSame( $this->event_id, $event_id );
	}
}
