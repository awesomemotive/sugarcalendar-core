<?php
namespace Sugar_Calendar\Tests\Legacy;

/**
 * Tests for the admin options functions.
 *
 * @group options
 */
class Options extends \WP_UnitTestCase {

	/**
	 * Set up
	 */
	public function setUp() {

	}

	/**
	 * Tear down
	 */
	public function tearDown() {

	}

	/**
	 * Date format
	 *
	 * @group options
	 * @group date-format
	 */
	public function test_date_format() {

		// Set start-of-week to Wednesday
		update_option( 'sc_date_format', 'jS F, Y' );

		// Get the date format
		$format = sc_get_date_format();

		$date  = '1979-06-17 03:33:00';
		$retval = gmdate( $format, strtotime( $date ) );

		$this->assertSame( '17th June, 1979', $retval );
	}

	/**
	 * Time format
	 *
	 * @group options
	 * @group time-format
	 */
	public function test_time_format() {

		// Set start-of-week to Wednesday
		update_option( 'sc_time_format', 'H:i' );

		// Get the date format
		$format = sc_get_time_format();

		$date  = '1979-06-17 03:33:00';
		$retval = gmdate( $format, strtotime( $date ) );

		$this->assertSame( '03:33', $retval );
	}

	/**
	 * Start of week
	 *
	 * @group options
	 * @group start-of-week
	 */
	public function test_start_of_week() {

		// Set start-of-week to Wednesday
		update_option( 'sc_start_of_week', 4 );

		// Get the number
		$start = sc_get_week_start_day();

		$date  = '1979-06-21 03:33:00';
		$retval = (int) gmdate( 'w', strtotime( $date ) );

		$this->assertSame( $start, $retval );
	}
}
