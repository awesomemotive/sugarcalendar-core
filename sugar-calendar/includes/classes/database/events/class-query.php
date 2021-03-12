<?php
/**
 * Events: Sugar_Calendar\Event_Query class
 *
 * @package Plugins/Sites/Events/Queries
 */
namespace Sugar_Calendar;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Database\Query;

/**
 * Class used for querying events.
 *
 * @since 2.0.0
 *
 * @see Event_Query::__construct() for accepted arguments.
 */
final class Event_Query extends Query {

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $table_name = 'events';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $table_schema = '\\Sugar_Calendar\\Event_Schema';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * Use underscores between words. I.E. "order_item"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $item_name = 'event';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "order_item"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $item_name_plural = 'events';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * I.E. `\\Sugar_Calendar\\Database\\Row` or `\\Sugar_Calendar\\Database\\Rows\\Customer`
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @since 3.0
	 * @var   mixed
	 */
	protected $item_shape = '\\Sugar_Calendar\\Event';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $cache_group = 'events';

	/** Methods ***************************************************************/

	/**
	 * Sets up the event query, based on the query vars passed.
	 *
	 * @since 3.0
	 * @access protected
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of event query parameters. Default empty.
	 *
	 *     @type int          $id                      An event ID to only return that event. Default empty.
	 *     @type array        $id__in                  Array of event IDs to include. Default empty.
	 *     @type array        $id__not_in              Array of event IDs to exclude. Default empty.
	 *     @type int          $object_id               An object ID to only return that object. Default empty.
	 *     @type array        $object_id__in           Array of object IDs to include. Default empty.
	 *     @type array        $object_id__not_in       Array of object IDs to exclude. Default empty.
	 *     @type int          $object_type             An object type to only return that object. Default empty.
	 *     @type array        $object_type__in         Array of object types to include. Default empty.
	 *     @type array        $object_type__not_in     Array of object types to exclude. Default empty.
	 *     @type int          $object_subtype          An object type to only return that object. Default empty.
	 *     @type array        $object_subtype__in      Array of object types to include. Default empty.
	 *     @type array        $object_subtype__not_in  Array of object types to exclude. Default empty.
	 *     @type int          $status                  A status to only return those events. Default empty.
	 *     @type array        $status__in              Array of statuses to include. Default empty.
	 *     @type array        $status__not_in          Array of statuses to exclude. Default empty.
	 *     @type array        $start_query             Date query clauses to limit events by. See Date_Query.
	 *                                                 Default null.
	 *     @type array        $end_query               Date query clauses to limit events by. See Date_Query.
	 *                                                 Default null.
	 *     @type array        $date_query              Query all datetime columns together. See Date_Query.
	 *     @type array        $date_created_query      Date query clauses to limit events by. See Date_Query.
	 *                                                 Default null.
	 *     @type array        $date_modified_query     Date query clauses to limit by. See Date_Query.
	 *                                                 Default null.
	 *     @type bool         $count                   Whether to return a event count (true) or array of event objects.
	 *                                                 Default false.
	 *     @type string       $fields                  Item fields to return. Accepts any column known names
	 *                                                 or empty (returns an array of complete event objects). Default empty.
	 *     @type int          $number                  Limit number of events to retrieve. Default 100.
	 *     @type int          $offset                  Number of events to offset the query. Used to build LIMIT clause.
	 *                                                 Default 0.
	 *     @type bool         $no_found_rows           Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby                 Accepts 'id', 'title', 'start_date', 'end_date', 'date_created'.
	 *                                                 Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                                 Default 'id'.
	 *     @type string       $order                   How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search                  Search term(s) to retrieve matching events for. Default empty.
	 *     @type bool         $update_item_cache       Whether to prime the cache for found items.
	 *                                                 Default true.
	 *     @type bool         $update_meta_cache       Whether to prime the meta cache for found items.
	 *                                                 Default true.
	 * }
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}

	/**
	 * Queries the database and retrieves items or counts.
	 *
	 * This method overrides the parent class to perform JIT manipulation of the
	 * parameters passed into it, and may be removed at a later date.
	 *
	 * @since 2.0.15
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public function query( $query = array() ) {

		// Do the query
		$retval = parent::query( $query );

		// Maybe prime object ID caches, for non-count queries
		if ( empty( $query['count'] ) ) {
			$this->maybe_prime_object_id_caches( $retval );
		}

		// Return queried items
		return $retval;
	}

	/**
	 * Maybe prime the object ID caches for related Events.
	 *
	 * This method is private because relative-object cache-priming will
	 * eventually be handled upstream in Berlin. It is shimmed here now, so we
	 * get the performance benefits right away.
	 *
	 * @since 2.0.15
	 *
	 * @param array $events
	 */
	private function maybe_prime_object_id_caches( $events = array() ) {

		// Maybe prime the post cache if there are more than 2 post objects
		if ( empty( $events ) ) {
			return;
		}

		// Extract post IDs from queried events
		$post_ids = wp_filter_object_list(
			$events,
			array(
				'object_type' => 'post'
			),
			'and',
			'object_id'
		);

		// Only do this query if there is more than 1 post to prime, otherwise
		// there is no benefit.
		if ( count( $post_ids ) > 1 ) {

			// Query for posts to prime the caches
			new \WP_Query( array(
				'post_type'      => sugar_calendar_get_event_post_type_id(),
				'post_status'    => 'any',
				'post__in'       => $post_ids,
				'posts_per_page' => -1,
				'no_found_rows'  => true,

				// Also prime relative caches
				'update_post_term_cache' => true,
				'update_post_meta_cache' => true,
				'lazy_load_term_meta'    => true
			) );
		}
	}
}
