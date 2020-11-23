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
	public function test_default_is_false() {
		$tz = sugar_calendar_get_timezone();

		$this->assertSame( false, $tz );
	}
}
