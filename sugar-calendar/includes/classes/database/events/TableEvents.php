<?php
/**
 * Events Database: WP_DB_Table_Events class
 *
 * @package Plugins/Events/Database/Object
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Database\Table;

/**
 * Setup the global "events" database table
 *
 * @since 2.0.0
 */
final class Events_Table extends Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'events';

	/**
	 * @var string Database version
	 */
	protected $version = 201902040005;

	/**
	 * @var string Table schema
	 */
	protected $schema = __NAMESPACE__ . '\\Event_Schema';

	/**
	 * Array of upgrade versions and methods.
	 *
	 * @access protected
	 * @since 2.0.0
	 * @var array
	 */
	protected $upgrades = array(
		'201901090003' => 201901090003,
		'201901220001' => 201901220001,
		'201902040004' => 201902040004,
		'201902040005' => 201902040005,
	);

	/**
	 * Setup the database schema
	 *
	 * Note: title & content columns exist here mostly for easier searching, but
	 *
	 *
	 * @since 2.0.0
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			object_id bigint(20) unsigned NOT NULL default '0',
			object_type varchar(20) NOT NULL default '',
			object_subtype varchar(20) NOT NULL default '',
			title text NOT NULL,
			content longtext NOT NULL,
			status varchar(20) NOT NULL default '',
			start datetime NOT NULL default '0000-00-00 00:00:00',
			start_tz varchar(20) NOT NULL default '',
			end datetime NOT NULL default '0000-00-00 00:00:00',
			end_tz varchar(20) NOT NULL default '',
			all_day tinyint(1) NOT NULL default '0',
			recurrence varchar(20) NOT NULL default '',
			recurrence_interval bigint(20) unsigned NOT NULL default '0',
			recurrence_count bigint(20) unsigned NOT NULL default '0',
			recurrence_end datetime NOT NULL default '0000-00-00 00:00:00',
			recurrence_end_tz varchar(20) NOT NULL default '',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY `object` (object_id,object_type,object_subtype),
			KEY `event_status` (status),
			KEY `event_times` (start,end,start_tz,end_tz),
			KEY `event_recur` (recurrence),
			KEY `event_recur_times` (recurrence_end,recurrence_end_tz)";
	}

	/**
	 * Upgrade to version 201901090002
	 * - Add index for the `status` column.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if upgrade was successful, false otherwise.
	 */
	protected function __201901090003() {

		// Look for column
		$result = $this->column_exists( 'status' );

		// Maybe add column
		if ( false === $result ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `status` varchar(20) default '' AFTER `content`;" );
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX status (status(20));" );
		}

		// Return success/fail
		return $this->is_success( true );
	}

	/**
	 * Upgrade to version 201901090002
	 * - Add `object_subtype` column
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if upgrade was successful, false otherwise.
	 */
	protected function __201901220001() {

		// Look for column
		$result = $this->column_exists( 'object_subtype' );

		// Maybe add column
		if ( false === $result ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `object_subtype` varchar(20) default '' AFTER `object_type`;" );
		}

		// Return success/fail
		return $this->is_success( true );
	}

	/**
	 * Upgrade to version 201902040003
	 * - Remove `recurrence_start` column that was added temporarily
	 * - Remove `recurrence_start_tz` column that was added temporarily
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if upgrade was successful, false otherwise.
	 */
	protected function __201902040004() {

		// Look for column
		$result = $this->column_exists( 'recurrence_start' );

		// Maybe add column
		if ( true === $result ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} DROP COLUMN `recurrence_start`;" );
		}

		// Look for column
		$result = $this->column_exists( 'recurrence_start_tz' );

		// Maybe add column
		if ( true === $result ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} DROP COLUMN `recurrence_start_tz`;" );
		}

		// Return success/fail
		return $this->is_success( true );
	}

	/**
	 * Upgrade to version 201901090005
	 * - Add `recurrence_count` column for future use
	 * - Add `recurrence_interval` column for future use
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if upgrade was successful, false otherwise.
	 */
	protected function __201902040005() {

		// Look for column
		$result = $this->column_exists( 'recurrence_interval' );

		// Maybe add column
		if ( false === $result ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `recurrence_interval` bigint(20) unsigned NOT NULL default '0' after `recurrence`;" );
		}

		// Look for column
		$result = $this->column_exists( 'recurrence_count' );

		// Maybe add column
		if ( false === $result ) {
			$this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN `recurrence_count` bigint(20) unsigned NOT NULL default '0' after `recurrence_interval`;" );
		}

		// Return success/fail
		return $this->is_success( true );
	}
}