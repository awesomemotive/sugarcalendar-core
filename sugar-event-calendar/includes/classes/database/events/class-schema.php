<?php
/**
 * Events Schema Class.
 *
 * @package     Sugar Calendar
 * @subpackage  Database\Schemas
 * @since       2.0
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Database\Schema;

/**
 * Event Schema Class.
 *
 * @since 2.0
 */
final class Event_Schema extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since 2.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		// id
		array(
			'name'       => 'id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'extra'      => 'auto_increment',
			'primary'    => true,
			'sortable'   => true
		),

		// object_id
		array(
			'name'       => 'object_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true
		),

		// object_type
		array(
			'name'       => 'object_type',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true
		),

		// object_subtype
		array(
			'name'       => 'object_subtype',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true
		),

		// title
		array(
			'name'       => 'title',
			'type'       => 'text',
			'default'    => '',
			'sortable'   => true,
			'searchable' => true
		),

		// content
		array(
			'name'       => 'content',
			'type'       => 'longtext',
			'default'    => '',
			'sortable'   => false,
			'searchable' => true
		),

		// status
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true
		),

		// start
		array(
			'name'       => 'start',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true
		),

		// start_tz
		array(
			'name'       => 'start_tz',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => ''
		),

		// end
		array(
			'name'       => 'end',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true
		),

		// end_tz
		array(
			'name'       => 'end_tz',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => ''
		),

		// all_day
		array(
			'name'       => 'all_day',
			'type'       => 'tinyint',
			'length'     => '1',
			'default'    => ''
		),

		// recurrence
		array(
			'name'       => 'recurrence',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => '',
			'sortable'   => true
		),

		// recurrence_interval
		array(
			'name'       => 'recurrence_interval',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true
		),

		// recurrence_count
		array(
			'name'       => 'recurrence_count',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true
		),

		// recurrence_end
		array(
			'name'       => 'recurrence_end',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true
		),

		// recurrence_end_tz
		array(
			'name'       => 'recurrence_end_tz',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => ''
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true
		),

		// date_modified
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true
		),

		// uuid
		array(
			'uuid'       => true,
		)
	);
}
