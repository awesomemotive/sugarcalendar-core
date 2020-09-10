<?php
/**
 * Event Relationships
 *
 * @package Plugins/Site/Events/Relationships
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return an array of the relationships that calendars are available for.
 *
 * You can filter this to enable a post calendar for just about any kind of
 * object type with an interface (post, user, term, comment, custom, etc...)
 *
 * By default, posts are the only relationships that are supported.
 *
 * @since 2.0.0
 *
 * @return array
 */
function sugar_calendar_get_event_relationships() {
	return apply_filters( 'sugar_calendar_get_event_relationships', array(

		// Default event relationships
		array(
			'name'    => 'Events',
			'type'    => 'post',
			'subtype' => sugar_calendar_get_event_post_type_id()
		)
	) );
}
