<?php
/**
 * Event Meta: WP_DB_Table_Eventmeta class
 *
 * @package Plugins/Events/Database/Meta
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Database\Table;

/**
 * Setup the global "eventmeta" database table
 *
 * @since 2.0.0
 */
final class Meta_Table extends Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'eventmeta';

	/**
	 * @var string Database version
	 */
	protected $version = 201810110001;

	/**
	 * Setup the database schema
	 *
	 * @since 2.0.0
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			sc_event_id bigint(20) unsigned NOT NULL default 0,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			KEY sc_event_id (sc_event_id),
			KEY meta_key (meta_key({$max_index_length}))";
	}
}
