<?php
namespace Sugar_Calendar\Tests\Admin;

/**
 * Tests for the admin tools functions.
 *
 * @group meta-box
 */
class MetaBox extends \WP_UnitTestCase {

	/**
	 * Set up
	 */
	public function setUp() {
		require_once SC_PLUGIN_DIR . 'includes/admin/meta-boxes.php';
	}

	/**
	 * Tear down
	 */
	public function tearDown() {
		unset(
			$_POST['all_day'],
			$_POST['start_date'],
			$_POST['start_time_hour'],
			$_POST['start_time_minute'],
			$_POST['start_time_second'],
			$_POST['start_time_am_pm'],
			$_POST['end_date'],
			$_POST['end_time_hour'],
			$_POST['end_time_second'],
			$_POST['end_time_minute'],
			$_POST['end_time_am_pm'],
			$_POST['location'],
			$_POST['color']
		);
	}

	/**
	 * @group meta-box
	 * @group has_end
	 */
	public function test_has_end_ok() {
		$_POST['end_date']        = '1980-05-15';
		$_POST['end_time_hour']   = '12';
		$_POST['end_time_minute'] = '00';

		$retval = \Sugar_Calendar\Admin\Editor\Meta\has_end();

		$this->assertSame( true, $retval );
	}

	/**
	 * @group meta-box
	 * @group has_end
	 */
	public function test_has_no_end_default() {
		$retval = \Sugar_Calendar\Admin\Editor\Meta\has_end();

		$this->assertSame( false, $retval );
	}

	/**
	 * @group meta-box
	 * @group has_end
	 */
	public function test_has_no_end_empty() {
		$_POST['end_date']        = '';
		$_POST['end_time_hour']   = '';
		$_POST['end_time_minute'] = '';

		$retval = \Sugar_Calendar\Admin\Editor\Meta\has_end();

		$this->assertSame( false, $retval );
	}

	/**
	 * @group meta-box
	 * @group prepare_all_day
	 */
	public function test_prepare_all_day_ok() {
		$_POST['all_day'] = true;

		$retval = \Sugar_Calendar\Admin\Editor\Meta\prepare_all_day();

		$this->assertSame( true, $retval );
	}

	/**
	 * @group meta-box
	 * @group prepare_all_day
	 */
	public function test_prepare_all_day_default() {
		$retval = \Sugar_Calendar\Admin\Editor\Meta\prepare_all_day();

		$this->assertSame( false, $retval );
	}

	/**
	 * @group meta-box
	 * @group prepare_all_day
	 */
	public function test_prepare_all_day_empty() {
		$_POST['all_day'] = '';

		$retval = \Sugar_Calendar\Admin\Editor\Meta\prepare_all_day();

		$this->assertSame( false, $retval );
	}

	/**
	 * @group meta-box
	 * @group prepare_start
	 */
	public function test_prepare_start_noon() {
		$_POST['start_date']        = '1980-05-15';
		$_POST['start_time_hour']   = '12';
		$_POST['start_time_minute'] = '00';
		$_POST['start_time_am_pm']  = 'pm';

		$retval = \Sugar_Calendar\Admin\Editor\Meta\prepare_start();

		$this->assertSame( '1980-05-15 12:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group prepare_start
	 */
	public function test_prepare_start_midnight() {
		$_POST['start_date']        = '1980-05-15';
		$_POST['start_time_hour']   = '12';
		$_POST['start_time_minute'] = '00';
		$_POST['start_time_am_pm']  = 'am';

		$retval = \Sugar_Calendar\Admin\Editor\Meta\prepare_start();

		$this->assertSame( '1980-05-15 00:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group prepare_start
	 */
	public function test_prepare_start_empty_date() {
		$_POST['start_date']        = '';
		$_POST['start_time_hour']   = '12';
		$_POST['start_time_minute'] = '00';
		$_POST['start_time_am_pm']  = 'pm';

		$retval = \Sugar_Calendar\Admin\Editor\Meta\prepare_start();
		$compare = gmdate( 'Y-m-d 12:00:00', current_time( 'timestamp' ) );

		$this->assertSame( $compare, $retval );
	}

	/**
	 * @group meta-box
	 * @group prepare_start
	 */
	public function test_prepare_start_empty_time() {
		$_POST['start_date']        = '1980-05-15';
		$_POST['start_time_hour']   = '';
		$_POST['start_time_minute'] = '';
		$_POST['start_time_am_pm']  = '';

		$retval = \Sugar_Calendar\Admin\Editor\Meta\prepare_start();

		$this->assertSame( '1980-05-15 00:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group sanitize_end
	 */
	public function test_sanitize_end_ok() {

		$retval = \Sugar_Calendar\Admin\Editor\Meta\sanitize_end(
			'1980-05-15 12:00:00',
			'1980-05-15 13:00:00',
			false
		);

		$this->assertSame( '1980-05-15 13:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group sanitize_end
	 */
	public function test_sanitize_end_start_time_after_end_time() {

		$retval = \Sugar_Calendar\Admin\Editor\Meta\sanitize_end(
			'1980-05-15 11:00:00',
			'1980-05-15 12:00:00',
			false
		);

		$this->assertSame( '1980-05-15 12:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group sanitize_end
	 */
	public function test_sanitize_end_start_date_before_end_date() {

		$retval = \Sugar_Calendar\Admin\Editor\Meta\sanitize_end(
			'1979-06-17 12:00:00',
			'1980-05-15 12:00:00',
			false
		);

		$this->assertSame( '1980-05-15 12:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group sanitize_end
	 */
	public function test_sanitize_end_empty_end_date_time() {

		$retval = \Sugar_Calendar\Admin\Editor\Meta\sanitize_end(
			'0000-00-00 00:00:00',
			'1980-05-15 12:00:00',
			false
		);

		$this->assertSame( '1980-05-15 12:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group sanitize_end
	 */
	public function test_sanitize_end_empty_end_date() {

		$retval = \Sugar_Calendar\Admin\Editor\Meta\sanitize_end(
			'0000-00-00 13:00:00',
			'1980-05-15 12:00:00',
			false
		);

		$this->assertSame( '1980-05-15 12:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group sanitize_end
	 */
	public function test_sanitize_end_empty_end_time() {

		$retval = \Sugar_Calendar\Admin\Editor\Meta\sanitize_end(
			'1980-05-15 00:00:00',
			'1980-05-15 12:00:00',
			false
		);

		$this->assertSame( '1980-05-15 12:00:00', $retval );
	}

	/**
	 * @group meta-box
	 * @group sanitize_end
	 */
	public function test_sanitize_end_all_day() {

		$retval = \Sugar_Calendar\Admin\Editor\Meta\sanitize_end(
			'1980-05-15 00:00:00',
			'1980-05-15 12:00:00',
			true
		);

		$this->assertSame( '1980-05-15 23:59:59', $retval );
	}
}
