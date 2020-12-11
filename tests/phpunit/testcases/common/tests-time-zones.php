<?php
namespace Sugar_Calendar\Tests\Common;

/**
 * Tests for the common timezone functions.
 *
 * @group timezones
 */
class TimeZones extends \WP_UnitTestCase {

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
	 * @group settings
	 */
	public function test_default_is_null() {
		$tz = sugar_calendar_get_timezone();

		$this->assertSame( null, $tz );
	}

	/**
	 * At 2020-11-23 00:00:00, Chicago is 6 hours behind UTC.
	 */
	public function test_get_timezone_diff_chicago_to_utc() {
		$assert = sugar_calendar_get_timezone_diff( 'America/Chicago', 'UTC', '2020-11-23 00:00:00' );
		$same   = -6 * HOUR_IN_SECONDS;
		$this->assertSame( $same, $assert );
	}

	/**
	 * At 2020-11-23 00:00:00, Chicago is 1 hour behind New York.
	 */
	public function test_get_timezone_diff_chicago_to_new_york() {
		$assert = sugar_calendar_get_timezone_diff( 'America/Chicago', 'America/New_York', '2020-11-23 00:00:00' );
		$same   = -HOUR_IN_SECONDS;
		$this->assertSame( $same, $assert );
	}

	/**
	 * At 2020-11-23 00:00:00, New York is 1 hour ahead of Chicago.
	 */
	public function test_get_timezone_diff_new_york_to_chicago() {
		$assert = sugar_calendar_get_timezone_diff( 'America/New_York', 'America/Chicago', '2020-11-23 00:00:00' );
		$same   = HOUR_IN_SECONDS;
		$this->assertSame( $same, $assert );
	}

	/**
	 * At 2020-11-23 00:00:00, Honalulu is 11 hours behind Midway.
	 */
	public function test_get_timezone_diff_midway_to_honalulu() {
		$assert = sugar_calendar_get_timezone_diff( 'Pacific/Midway', 'Pacific/Honalulu', '2020-11-23 00:00:00' );
		$same   = -11 * HOUR_IN_SECONDS;

		$this->assertSame( $same, $assert );
	}

	/**
	 * At 2020-11-23 00:00:00, Cairo is 1 hour behind Moscow.
	 */
	public function test_get_timezone_diff_cairo_to_moscow() {
		$assert = sugar_calendar_get_timezone_diff( 'Africa/Cairo', 'Europe/Moscow', '2020-11-23 00:00:00' );
		$same   = -HOUR_IN_SECONDS;
		$this->assertSame( $same, $assert );
	}

	/**
	 * At 2020-11-23 00:00:00, Chicago to New York to UTC is 6 hours.
	 */
	public function test_get_timezone_diff_multi() {

		$args = array(
			'datetime'  => sugar_calendar_get_request_time( 'mysql' ),
			'timezones' => array(
				'America/Chicago',
				'America/New_York',
				'UTC',
			)
		);

		$offset = sugar_calendar_get_timezone_diff_multi( $args );
		$same   = -6 * HOUR_IN_SECONDS;

		$this->assertSame( $same, $offset );
	}

	/**
	 * At 2020-11-23 00:00:00, Los Angeles is 3 hours behind New York.
	 */
	public function test_get_timezone_diff_multi_hours() {

		$args = array(
			'datetime'  => sugar_calendar_get_request_time( 'mysql' ),
			'format'    => 'hours',
			'direction' => 'left',
			'timezones' => array(
				'America/Los_Angeles',
				'America/New_York',
			)
		);

		$offset = sugar_calendar_get_timezone_diff_multi( $args );
		$same   = -3;

		$this->assertSame( $same, $offset );
	}

	/**
	 * @group get_datetime_object
	 */
	public function test_get_datetime_object_chicago_greater_than_new_york() {

		// 09:20 New York
		$dto1 = sugar_calendar_get_datetime_object( '2020-12-11 09:20:00', 'America/New_York' );

		// 09:20 Chicago viewed from New York (10:20)
		$dto2 = sugar_calendar_get_datetime_object( '2020-12-11 09:20:00', 'America/Chicago', 'America/New_York' );

		// Test that $dto2 is greater than $dto1
		$this->assertGreaterThan( $dto1, $dto2 );
	}

	/**
	 * @group get_datetime_object
	 */
	public function test_get_datetime_object_chicago_greater_than_los_angeles() {

		// 09:20 Chicago
		$dto1 = sugar_calendar_get_datetime_object( '2020-12-11 09:20:00', 'America/Chicago' );

		// 09:20 Los Angeles viewed from Chicago (11:20)
		$dto2 = sugar_calendar_get_datetime_object( '2020-12-11 09:20:00', 'America/Los_Angeles', 'America/Chicago' );

		// Test that $dto2 is greater than $dto1
		$this->assertGreaterThan( $dto1, $dto2 );
	}

	/**
	 * @group get_datetime_object
	 */
	public function test_get_datetime_object_los_angeles_less_than_new_york() {

		// 09:20 Los Angeles
		$dto1 = sugar_calendar_get_datetime_object( '2020-12-11 09:20:00', 'America/Los_Angeles' );

		// 09:20 New York
		$dto2 = sugar_calendar_get_datetime_object( '2020-12-11 09:20:00', 'America/New_York' );

		// Test that $dto2 is greater than $dto1
		$this->assertLessThan( $dto1, $dto2 );
	}

	/**
	 * @group get_datetime_object
	 */
	public function test_get_datetime_object_los_angeles_equal_to_new_york() {

		// 09:20 Los Angeles timestamp
		$dto1 = sugar_calendar_get_datetime_object( '2020-12-11 09:20:00', 'America/Los_Angeles' )->getTimestamp();

		// 11:20 New York timestamp
		$dto2 = sugar_calendar_get_datetime_object( '2020-12-11 12:20:00', 'America/New_York' )->getTimestamp();

		// Test that $dto1 equals $dto2
		$this->assertEquals( $dto1, $dto2 );
	}
}
