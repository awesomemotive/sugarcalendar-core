<?php
/**
 * Event Meta-boxes
 *
 * @package Plugins/Site/Event/Admin/Metaboxes
 */
namespace Sugar_Calendar\Admin\Editor\Meta;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Common\Editor as Editor;

/**
 * Maybe add custom fields support to supported post types.
 *
 * @since 2.1.0
 *
 * @param array $supports
 *
 * @return array
 */
function custom_fields( $supports = array() ) {

	// Get the custom fields setting
	$supported = Editor\custom_fields();

	// Add custom fields support
	if ( ! empty( $supported ) ) {
		array_push( $supports, 'custom-fields' );
	}

	// Return supported
	return $supports;
}

/**
 * Event Types Meta-box
 * Output radio buttons instead of the default WordPress mechanism
 *
 * @since 2.0.0
 *
 * @param array  $args
 * @param string $taxonomy
 *
 * @return array
 */
function taxonomy_args( $args = array(), $taxonomy = '' ) {
	if ( sugar_calendar_get_calendar_taxonomy_id() === $taxonomy ) {

		$r = apply_filters( 'sugar_calendar_taxonomy_args', array(
			'meta_box_cb' => 'post_categories_meta_box'
		), $args );

		$args = wp_parse_args( $args, $r );
	}

	return $args;
}

/**
 * Use the custom walker for radio buttons
 *
 * @since 2.0.0
 *
 * @param array $args
 *
 * @return array
 */
function checklist_args( $args = array() ) {
	if ( ! empty( $args['taxonomy'] ) && ( sugar_calendar_get_calendar_taxonomy_id() === $args['taxonomy'] ) ) {

		// Filter the walker to make it a radio
		$r = apply_filters( 'sugar_calendar_checklist_args', array(
			'walker' => new \Sugar_Calendar\Admin\Taxonomy\Walker_Category_Radio()
		), $args );

		// Re-parse the arguments
		$args = wp_parse_args( $args, $r );
	}

	return $args;
}

/**
 * Event Meta-box
 *
 * @since 2.0.0
*/
function add() {

	// Get the supported post types
	$supported = get_post_types_by_support( array( 'events' ) );

	// Sections
	add_meta_box(
		'sugar_calendar_editor_event_details',
		esc_html__( 'Event', 'sugar-calendar' ),
		'Sugar_Calendar\\Admin\\Editor\\Meta\\box',
		$supported,
		'normal',
		'high'
	);

	// Events post-type specific meta-box
	$pt = sugar_calendar_get_event_post_type_id();

	// Details
	if ( ! post_type_supports( $pt, 'editor' ) ) {
		add_meta_box(
			'sugar_calendar_details',
			esc_html__( 'Details', 'sugar-calendar' ),
			'Sugar_Calendar\\Admin\\Editor\\Meta\\details',
			$pt,
			'normal',
			'high'
		);
	}
}

/**
 * Filters the user option for Event meta-box ordering, and overrides it when
 * editing with Blocks.
 *
 * This ensures that users who have customized their meta-box layouts will still
 * be able to see meta-boxes no matter the Editing Type (block, classic).
 *
 * @since 2.0.20
 *
 * @param array $original
 * @return mixed
 */
function noop_user_option( $original = array() ) {

	// Bail if using Classic Editor
	if ( 'classic' === Editor\current() ) {
		return $original;
	}

	// Return false
	return false;
}

/**
 * Output the meta box
 *
 * @since 2.0.0
 *
 * @param WP_Post $post
 */
function box( $post = null ) {

	// Initialize the meta box
	$meta_box = new Box();

	// Setup & display the meta box
	$meta_box->setup_sections();
	$meta_box->setup_post( $post );
	$meta_box->display();
}

/**
 * Output the event duration meta-box
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The post
*/
function details( $post = null ) {
	wp_editor( $post->post_content, 'post_content' );
}

/**
 * Offset hour based on meridiem
 *
 * @since 2.0.0
 *
 * @param  int     $hour
 * @param  string  $meridiem
 *
 * @return int
 */
function adjust_hour_for_meridiem( $hour = 0, $meridiem = 'am' ) {

	// Store new hour
	$new_hour = $hour;

	// Bump by 12 hours
	if ( 'pm' === $meridiem && ( $new_hour < 12 ) ) {
		$new_hour += 12;

	// Decrease by 12 hours
	} elseif ( 'am' === $meridiem && ( $new_hour >= 12 ) ) {
		$new_hour -= 12;
	}

	// Filter & return
	return (int) $new_hour;
}

/**
 * Determine whether the meta-box contents can be saved.
 *
 * This checks a number of specific things, like nonces, autosave, ajax, bulk,
 * and also checks caps based on the object type.
 *
 * @since 2.0
 *
 * @param int    $object_id
 * @param object $object
 *
 * @return bool
 */
function can_save_meta_box( $object_id = 0, $object = null ) {

	// Default return value
	$retval = false;

	// Bail if no nonce or nonce check fails
	if ( empty( $_POST['sc_mb_nonce'] ) || ! wp_verify_nonce( $_POST['sc_mb_nonce'], 'sugar_calendar_nonce' ) ) {
		return $retval;
	}

	// Bail on autosave, ajax, or bulk
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $retval;
	}

	// @todo move into a separate function
	if ( is_a( $object, 'WP_Post' ) ) {

		// Get the post type
		$post_type = get_post_type( $object_id );

		// Only save event metadata to supported post types
		if ( ! post_type_supports( $post_type, 'events' ) ) {
			return $retval;
		}

		// Bail if revision
		if ( wp_is_post_revision( $object_id ) ) {
			return $retval;
		}

		// Get post type object
		$post_type_object = get_post_type_object( $post_type );

		// Bail if user cannot edit this event
		if ( current_user_can( $post_type_object->cap->edit_post, $object_id ) ) {
			$retval = true;
		}
	}

	// Return whether the meta-box can be saved
	return (bool) $retval;
}

/**
 * Meta-box save
 *
 * @since 2.0.0
 *
 * @param int    $object_id ID of the connected object
 * @param object $object    Connected object data
 *
 * @return int|void
 */
function save( $object_id = 0, $object = null ) {

	// Bail if meta-box cannot be saved
	if ( ! can_save_meta_box( $object_id, $object ) ) {
		return $object_id;
	}

	// Prepare event parameters
	$all_day  = prepare_all_day();
	$start    = prepare_start();
	$end      = prepare_end();
	$start_tz = prepare_timezone( 'start' );
	$end_tz   = prepare_timezone( 'end' );

	// Sanitize to prevent data entry errors
	$start    = sanitize_start( $start, $end, $all_day );
	$end      = sanitize_end( $end, $start, $all_day );
	$all_day  = sanitize_all_day( $all_day, $start, $end );
	$start_tz = sanitize_timezone( $start_tz, $end_tz, $all_day );
	$end_tz   = sanitize_timezone( $end_tz, $start_tz, $all_day );

	// Shim these for now (need to make functions for them)
	$title   = $object->post_title;
	$content = $object->post_content;
	$subtype = $object->post_type;
	$status  = $object->post_status;

	// Get an event
	$event = sugar_calendar_get_event_by_object( $object_id );
	$type  = ! empty( $event->object_type )
		? $event->object_type
		: 'post';

	// Assemble the event properties
	$to_save = apply_filters( 'sugar_calendar_event_to_save', array(
		'object_id'      => $object_id,
		'object_type'    => $type,
		'object_subtype' => $subtype,
		'title'          => $title,
		'content'        => $content,
		'status'         => $status,
		'start'          => $start,
		'start_tz'       => $start_tz,
		'end'            => $end,
		'end_tz'         => $end_tz,
		'all_day'        => $all_day
	) );

	// Update or Add New
	$success = ! empty( $event->id )
		? sugar_calendar_update_event( $event->id, $to_save )
		: sugar_calendar_add_event( $to_save );

	// Return the results of the update/add event
	return $success;
}

/**
 * Does the event that is trying to be saved have an end date & time?
 *
 * @since 2.0.5
 *
 * @return bool
 */
function has_start() {
	return ! (
		empty( $_POST['start_date'] )
		&& empty( $_POST['start_time_hour'] )
		&& empty( $_POST['start_time_minute'] )
	);
}

/**
 * Does the event that is trying to be saved have an end date & time?
 *
 * @since 2.0.5
 *
 * @return bool
 */
function has_end() {
	return ! (
		empty( $_POST['end_date'] )
		&& empty( $_POST['end_time_hour'] )
		&& empty( $_POST['end_time_minute'] )
	);
}

/**
 * Prepare the all-day value to be saved to the database.
 *
 * @since 2.0.5
 *
 * @return bool
 */
function prepare_all_day() {
	return ! empty( $_POST['all_day'] )
		? (bool) $_POST['all_day']
		: false;
}

/**
 * Prepare the start value to be saved to the database.
 *
 * @since 2.0.5
 *
 * @return string The MySQL formatted datetime to start
 */
function prepare_start() {
	return prepare_date_time( 'start' );
}

/**
 * Prepare the start value to be saved to the database.
 *
 * @since 2.0.5
 *
 * @return string The MySQL formatted datetime to start
 */
function prepare_end() {
	return prepare_date_time( 'end' );
}

/**
 * Helper function to prepare any combined date/hour/minute/meridiem fields.
 *
 * Used by start & end, but could reliably be used elsewhere.
 *
 * This helper exists to eliminate duplicated code, and to provide a single
 * function to funnel different field formats through, I.E. 12/24 hour clocks.
 *
 * @since 2.0.5
 *
 * @param type $prefix
 * @return type
 */
function prepare_date_time( $prefix = 'start' ) {

	// Sanity check the prefix
	if ( empty( $prefix ) || ! is_string( $prefix ) ) {
		$prefix = 'start';
	}

	// Sanitize the prefix, and append an underscore
	$prefix = sanitize_key( $prefix ) . '_';

	// Get the current time
	$now = sugar_calendar_get_request_time();

	// Get the current Year, Month, and Day, without any time
	$nt  = gmdate( 'Y-m-d H:i:s', gmmktime(
		0,
		0,
		0,
		gmdate( 'n', $now ),
		gmdate( 'j', $now ),
		gmdate( 'Y', $now )
	) );

	// Calendar date is set
	$date = ! empty( $_POST[ $prefix . 'date' ] )
		? strtotime( sanitize_text_field( $_POST[ $prefix . 'date' ] ) )
		: strtotime( $nt );

	// Hour
	$hour = ! empty( $_POST[ $prefix . 'time_hour' ] )
		? sanitize_text_field( $_POST[ $prefix . 'time_hour'] )
		: 0;

	// Minutes
	$minutes = ! empty( $_POST[ $prefix . 'time_minute' ] )
		? sanitize_text_field( $_POST[ $prefix . 'time_minute' ] )
		: 0;

	// Seconds
	$seconds = ! empty( $_POST[ $prefix . 'time_second' ] )
		? sanitize_text_field( $_POST[ $prefix . 'time_second' ] )
		: 0;

	// Maybe adjust for meridiem
	if ( '12' === sugar_calendar_get_clock_type() ) {

		// Day/night
		$am_pm = ! empty( $_POST[ $prefix . 'time_am_pm' ] )
			? sanitize_text_field( $_POST[ $prefix . 'time_am_pm' ] )
			: 'am';

		// Maybe tweak hours
		$hour = adjust_hour_for_meridiem( $hour, $am_pm );
	}

	// Make timestamp from pieces
	$timestamp = gmmktime(
		intval( $hour ),
		intval( $minutes ),
		intval( $seconds ),
		gmdate( 'n', $date ),
		gmdate( 'j', $date ),
		gmdate( 'Y', $date )
	);

	// Format for MySQL
	$retval = gmdate( 'Y-m-d H:i:s', $timestamp );

	// Return
	return $retval;
}

/**
 * Prepare a time zone value to be saved to the database.
 *
 * @since 2.1.0
 *
 * @return string The PHP/Olson time zone to save
 */
function prepare_timezone( $prefix = 'start' ) {

	// Sanity check the prefix
	if ( empty( $prefix ) || ! is_string( $prefix ) ) {
		$prefix = 'start';
	}

	// Sanitize the prefix, and append an underscore
	$prefix = sanitize_key( $prefix ) . '_';
	$field  = "{$prefix}tz";

	// Sanitize time zone
	$zone = ! empty( $_POST[ $field ] )
		? sanitize_text_field( $_POST[ $field ] )
		: '';

	// Return the prepared time zone
	return $zone;
}

/**
 * Sanitizes the start MySQL datetime, so that:
 *
 * - If all-day, time is set to midnight
 *
 * @since 2.0.5
 *
 * @param string $start   The start time, in MySQL format
 * @param string $end     The end time, in MySQL format
 * @param bool   $all_day True|False, whether the event is all-day
 *
 * @return string
 */
function sanitize_start( $start = '', $end = '', $all_day = false ) {

	// Bail early if start or end are empty or malformed
	if ( empty( $start ) || empty( $end ) || ! is_string( $start ) || ! is_string( $end ) ) {
		return $start;
	}

	// Check if the user attempted to set an end date and/or time
	$start_int = strtotime( $start );

	// All day events end at the final second
	if ( true === $all_day ) {
		$start_int = gmmktime(
			0,
			0,
			0,
			gmdate( 'n', $start_int ),
			gmdate( 'j', $start_int ),
			gmdate( 'Y', $start_int )
		);
	}

	// Format
	$retval = gmdate( 'Y-m-d H:i:s', $start_int );

	// Return the new start
	return $retval;
}

/**
 * Sanitizes the all-day value, so that:
 *
 * - If times align, all-day is made true
 *
 * @since 2.0.5
 *
 * @param bool   $all_day True|False, whether the event is all-day
 * @param string $start   The start time, in MySQL format
 * @param string $end     The end time, in MySQL format
 *
 * @return string
 */
function sanitize_all_day( $all_day = false, $start = '', $end = '' ) {

	// Bail early if start or end are empty or malformed
	if ( empty( $start ) || empty( $end ) || ! is_string( $start ) || ! is_string( $end ) ) {
		return $start;
	}

	// Check if the user attempted to set an end date and/or time
	$start_int = strtotime( $start );
	$end_int   = strtotime( $end );

	// Starts at midnight and ends 1 second before
	if (
		( '00:00:00' === gmdate( 'H:i:s', $start_int ) )
		&&
		( '23:59:59' === gmdate( 'H:i:s', $end_int ) )
	) {
		$all_day = true;
	}

	// Return the new start
	return (bool) $all_day;
}

/**
 * Sanitizes the end MySQL datetime, so that:
 *
 * - It does not end before it starts
 * - It is at least as long as the minimum event duration (if exists)
 * - If the date is empty, the time can still be used
 * - If both the date and the time are empty, it will equal the start
 *
 * @since 2.0.5
 *
 * @param string $end     The end time, in MySQL format
 * @param string $start   The start time, in MySQL format
 * @param bool   $all_day True|False, whether the event is all-day
 *
 * @return string
 */
function sanitize_end( $end = '', $start = '', $all_day = false ) {

	// Bail early if start or end are empty or malformed
	if ( empty( $start ) || empty( $end ) || ! is_string( $start ) || ! is_string( $end ) ) {
		return $end;
	}

	// Convert to integers for faster comparisons
	$start_int = strtotime( $start );
	$end_int   = strtotime( $end   );

	// Check if the user attempted to set an end date and/or time
	$has_end = has_end();

	// The user attempted an end date and this isn't an all-day event
	if ( ( true === $has_end ) && ( false === $all_day ) ) {

		// See if there a minimum duration to enforce...
		$minimum = sugar_calendar_get_minimum_event_duration();

		// Calculate a minimum end, maybe using the minimum duration
		$end_compare = ! empty( $minimum )
			? strtotime( '+' . $minimum, $start_int )
			: $end_int;

		// Bail if event duration exceeds the minimum (great!)
		if ( $end_compare > $start_int ) {
			return $end;

		// If there is a minimum, the new end is the start + the minimum
		} elseif ( ! empty( $minimum ) ) {
			$end_int = $end_compare;

		// If there isn't a minimum, then the end needs to be rejected
		} else {
			$has_end = false;
		}
	}

	// The above logic deterimned that the end needs to equal the start.
	// This is how events are allowed to have a start without a known end.
	if ( false === $has_end ) {
		$end_int = $start_int;
	}

	// All day events end at the final second
	if ( true === $all_day ) {
		$end_int = gmmktime(
			23,
			59,
			59,
			gmdate( 'n', $end_int ),
			gmdate( 'j', $end_int ),
			gmdate( 'Y', $end_int )
		);
	}

	// Format
	$retval = gmdate( 'Y-m-d H:i:s', $end_int );

	// Return the new end
	return $retval;
}

/**
 * Sanitize a timezone value, so that:
 *
 * - it can be empty                     (Floating)
 * - it can be valid PHP/Olson time zone (America/Chicago)
 * - it can be UTC offset                (UTC-13)
 *
 * @since 2.1.0
 *
 * @param string $timezone1
 * @param string $timezone2
 * @param string $all_day
 *
 * @return string
 */
function sanitize_timezone( $timezone1 = '', $timezone2 = '', $all_day = false ) {

	// Default return value
	$retval = $timezone1;

	// All-day events have no time zones
	if ( ! empty( $all_day ) ) {
		$retval = '';

	// Not all-day, so check time zones
	} else {

		// Maybe fallback to whatever time zone is not empty
		$retval = ! empty( $timezone1 )
			? $timezone1
			: $timezone2;
	}

	// Sanitize & return
	return sugar_calendar_sanitize_timezone( $retval );
}

/**
 * Maybe save event location in eventmeta
 *
 * @since 2.0.0
 *
 * @param array $event
 *
 * @return array
 */
function add_location_to_save( $event = array() ) {

	// Get event location
	$event['location'] = ! empty( $_POST['location'] )
		? wp_kses( $_POST['location'], array() )
		: '';

	// Return the event
	return $event;
}

/**
 * Maybe save event color in eventmeta
 *
 * @since 2.0.0
 *
 * @param array $event
 *
 * @return array
 */
function add_color_to_save( $event = array() ) {

	// Bail if missing ID or type
	if ( empty( $event['object_id'] ) || empty( $event['object_type'] ) ) {
		return $event;
	}

	// Set the event color
	$event['color'] = sugar_calendar_get_event_color( $event['object_id'], $event['object_type'] );

	// Return the event
	return $event;
}

/**
 * Display calendar taxonomy meta box
 *
 * This is a copy of post_categories_meta_box() which allows us to remove the
 * "Most Used" tab functionality for the "Calendars" taxonomy.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post
 * @param array   $box {
 *     Categories meta box arguments.
 *
 *     @type string   $id       Meta box 'id' attribute.
 *     @type string   $title    Meta box title.
 *     @type callable $callback Meta box display callback.
 *     @type array    $args {
 *         Extra meta box arguments.
 *
 *         @type string $taxonomy Taxonomy. Default 'category'.
 *     }
 * }
 */
function calendars( $post = null, $box = array() ) {

	// Fallback
	$args = ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) )
		? array()
		: $box['args'];

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'taxonomy' => 'category'
	) );

	// Taxonomy vars
	$taxonomy = get_taxonomy( $r['taxonomy'] );
	$tax_name = esc_attr( $taxonomy->name );
	$default  = sugar_calendar_get_default_calendar();

	// Dropdown arguments
	$parent_dropdown_args = apply_filters( 'post_edit_category_parent_dropdown_args', array(
		'taxonomy'         => $taxonomy->name,
		'hide_empty'       => 0,
		'name'             => 'new' . $taxonomy->name . '_parent',
		'orderby'          => 'name',
		'hierarchical'     => $taxonomy->hierarchical,
		'show_option_none' => '&mdash; ' . $taxonomy->labels->parent_item . ' &mdash;',
	) );

	// Check term cache first
	$selected = get_object_term_cache( $post->ID, $taxonomy->name );

	// Pluck IDs from cache
	if ( false !== $selected ) {
		$selected = wp_list_pluck( $selected, 'term_id' );

	// No cache, so query for selected
	} else {

		// Args
		$tax_args = array_merge( $r, array(
			'fields' => 'ids'
		) ) ;

		// Query
		$selected = wp_get_object_terms( $post->ID, $taxonomy->name, $tax_args );
	}

	// Use default
	if ( empty( $selected ) && ! empty( $default ) ) {
		$selected = array( $default );
	}

	// Checklist arguments
	$checklist_args = array(
		'taxonomy'      => $taxonomy->name,
		'selected_cats' => $selected,
		'checked_ontop' => false
	); ?>

	<div id="taxonomy-<?php echo $tax_name; ?>" class="categorydiv">

		<div id="<?php echo $tax_name; ?>-all">
			<?php

			$name = ( 'category' === $taxonomy->name )
				? 'post_category'
				: 'tax_input[' . $taxonomy->name . ']';

			// Allows for an empty term set to be sent. 0 is an invalid Term ID
			// and will be ignored by empty() checks.
			echo "<input type='hidden' name='{$name}[]' value='0' />"; ?>

			<ul id="<?php echo $tax_name; ?>checklist" data-wp-lists="list:<?php echo $tax_name; ?>" class="categorychecklist form-no-clear">
				<?php wp_terms_checklist( $post->ID, $checklist_args ); ?>
			</ul>

			<a id="<?php echo $tax_name; ?>-clear" href="#<?php echo $tax_name; ?>-clear" class="hide-if-no-js button taxonomy-clear">
				<?php esc_html_e( 'Clear', 'sugar-calendar' ); ?>
			</a>
		</div>

		<?php if ( current_user_can( $taxonomy->cap->edit_terms ) ) : ?>

			<div id="<?php echo $tax_name; ?>-adder" class="wp-hidden-children">
				<a id="<?php echo $tax_name; ?>-add-toggle" href="#<?php echo $tax_name; ?>-add" class="hide-if-no-js taxonomy-add-new">
					<?php echo $taxonomy->labels->add_new_item; ?>
				</a>

				<p id="<?php echo $tax_name; ?>-add" class="category-add wp-hidden-child">
					<label class="screen-reader-text" for="new<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>
					<input type="text" name="new<?php echo $tax_name; ?>" id="new<?php echo $tax_name; ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $taxonomy->labels->new_item_name ); ?>" aria-required="true"/>
					<label class="screen-reader-text" for="new<?php echo $tax_name; ?>_parent">
						<?php echo $taxonomy->labels->parent_item_colon; ?>
					</label>

					<?php wp_dropdown_categories( $parent_dropdown_args ); ?>

					<input type="button" id="<?php echo $tax_name; ?>-add-submit" data-wp-lists="add:<?php echo $tax_name; ?>checklist:<?php echo $tax_name; ?>-add" class="button category-add-submit" value="<?php echo esc_attr( $taxonomy->labels->add_new_item ); ?>" />

					<?php wp_nonce_field( 'add-' . $taxonomy->name, '_ajax_nonce-add-' . $taxonomy->name, false ); ?>

					<span id="<?php echo $tax_name; ?>-ajax-response"></span>
				</p>
			</div>

		<?php endif; ?>

	</div>

	<?php
}

/**
 * Output the event duration meta-box section
 *
 * @since  2.0.0
 *
 * @param Event $event The event object
 */
function section_duration( $event = null ) {

	// Get clock type, hours, and minutes
	$tztype    = sugar_calendar_get_timezone_type();
	$timezone  = sugar_calendar_get_timezone();
	$clock     = sugar_calendar_get_clock_type();
	$hours     = sugar_calendar_get_hours();
	$minutes   = sugar_calendar_get_minutes();

	// Get the hour format based on the clock type
	$hour_format = ( '12' === $clock )
		? 'h'
		: 'H';

	// Setup empty Event if malformed
	if ( ! is_object( $event ) ) {
		$event = new Sugar_Calendar\Event();
	}

	// Default dates & times
	$date = $hour = $minute = $end_date = $end_hour = $end_minute = '';

	// Default AM/PM
	$am_pm = $end_am_pm = '';

	// Default time zones
	$start_tz = $end_tz = '';

	// Default time zone UI
	$show_multi_tz = $show_single_tz = false;

	/** All Day ***************************************************************/

	$all_day = ! empty( $event->all_day )
		? (bool) $event->all_day
		: false;

	$hidden = ( true === $all_day )
		? ' style="display: none;"'
		: '';

	/** Ends ******************************************************************/

	// Get date_time
	$end_date_time = ! $event->is_empty_date( $event->end ) && ( $event->start !== $event->end )
		? strtotime( $event->end )
		: null;

	// Only if end isn't empty
	if ( ! empty( $end_date_time ) ) {

		// Date
		$end_date = gmdate( 'Y-m-d', $end_date_time );

		// Only if not all-day
		if ( empty( $all_day ) ) {

			// Hour
			$end_hour = gmdate( $hour_format, $end_date_time );
			if ( empty( $end_hour ) ) {
				$end_hour = '';
			}

			// Minute
			$end_minute = gmdate( 'i', $end_date_time );
			if ( empty( $end_hour ) || empty( $end_minute )) {
				$end_minute = '';
			}

			// Day/night
			$end_am_pm = gmdate( 'a', $end_date_time );
			if ( empty( $end_hour ) && empty( $end_minute ) ) {
				$end_am_pm = '';
			}
		}
	}

	/** Starts ****************************************************************/

	// Get date_time
	if ( ! empty( $_GET['start_day'] ) ) {
		$date_time = (int) $_GET['start_day'];
	} else {
		$date_time = ! $event->is_empty_date( $event->start )
			? strtotime( $event->start )
			: null;
	}

	// Date
	if ( ! empty( $date_time ) ) {
		$date = gmdate( 'Y-m-d', $date_time );

		// Only if not all-day
		if ( empty( $all_day ) ) {

			// Hour
			$hour = gmdate( $hour_format, $date_time );
			if ( empty( $hour ) ) {
				$hour = '';
			}

			// Minute
			$minute = gmdate( 'i', $date_time );
			if ( empty( $hour ) || empty( $minute ) ) {
				$minute = '';
			}

			// Day/night
			$am_pm = gmdate( 'a', $date_time );
			if ( empty( $hour ) && empty( $minute ) ) {
				$am_pm = '';
			}

		// All day
		} elseif ( $date === $end_date ) {
			$end_date = '';
		}
	}

	/** Time Zones ************************************************************/

	// Default time zone on "Add New"
	if ( empty( $event->end_tz ) && ( 'off' !== $tztype ) && ! $event->exists() ) {
		$end_tz = $timezone;

	// Event end time zone
	} elseif ( ! empty( $end_date_time ) || ( $date_time !== $end_date_time ) ) {
		$end_tz = $event->end_tz;
	}

	// Default time zone on "Add New"
	if ( empty( $event->start_tz ) && ( 'off' !== $tztype ) && ! $event->exists() ) {
		$start_tz = $timezone;

	// Event start time zone
	} elseif ( ! empty( $date_time ) ) {
		$start_tz = $event->start_tz;
	}

	// All day Events have no time zone data
	if ( ! empty( $all_day ) ) {
		$start_tz = '';
		$end_tz   = '';
	}

	// Show multi time zone UI
	if ( ( 'multi' === $tztype )
		|| (
			! empty( $end_tz )
			&& ( $date_time !== $end_date_time )
			&& ( $start_tz  !== $end_tz        )
		)
	) {
		$show_multi_tz = true;

	// Show single time zone UI
	} elseif ( ( 'single' === $tztype ) || ! empty( $start_tz ) ) {
		$show_single_tz = true;
	}

	/** Let's Go! *************************************************************/

	// Start an output buffer
	ob_start(); ?>

	<table class="form-table rowfat">
		<tbody>
			<tr>
				<th>
					<label for="all_day" class="screen-reader-text"><?php esc_html_e( 'All Day', 'sugar-calendar' ); ?></label>
				</th>

				<td>
					<label>
						<input type="checkbox" name="all_day" id="all_day" value="1" <?php checked( $all_day ); ?> />
						<?php esc_html_e( 'All-day', 'sugar-calendar' ); ?>
					</label>
				</td>
			</tr>

			<tr>
				<th>
					<label for="start_date"><?php esc_html_e( 'Start', 'sugar-calendar'); ?></label>
				</th>

				<td>
					<div class="event-date">
						<input type="text" class="sugar_calendar_datepicker" name="start_date" id="start_date" value="<?php echo esc_attr( $date ); ?>" placeholder="yyyy-mm-dd" />
					</div>

					<div class="event-time" <?php echo $hidden; ?>>
						<span class="sc-time-separator"><?php esc_html_e( 'at', 'sugar-calendar' ); ?></span>
						<?php

						// Start Hour
						sugar_calendar_time_dropdown( array(
							'first'    => '&nbsp;',
							'id'       => 'start_time_hour',
							'name'     => 'start_time_hour',
							'items'    => $hours,
							'selected' => $hour
						) );

						?><span class="sc-time-separator"> : </span><?php

						// Start Minute
						sugar_calendar_time_dropdown( array(
							'first'    => '&nbsp;',
							'id'       => 'start_time_minute',
							'name'     => 'start_time_minute',
							'items'    => $minutes,
							'selected' => $minute
						) );

						// Start AM/PM
						if ( '12' === $clock ) :
							?><select id="start_time_am_pm" name="start_time_am_pm" class="sc-select-chosen sc-time">
								<option value="">&nbsp;</option>
								<option value="am" <?php selected( $am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'sugar-calendar' ); ?></option>
								<option value="pm" <?php selected( $am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'sugar-calendar' ); ?></option>
							</select><?php
						endif;

					?></div><?php

					// Start Time Zone
					if ( true === $show_multi_tz ) :

						?><div class="event-time-zone"><?php

							sugar_calendar_timezone_dropdown( array(
								'id'      => 'start_tz',
								'name'    => 'start_tz',
								'current' => $start_tz
							) );

						?></div><?php

					endif;
				?></td>

			</tr>

			<tr>
				<th>
					<label for="end_date"><?php esc_html_e( 'End', 'sugar-calendar'); ?></label>
				</th>

				<td>
					<div class="event-date">
						<input type="text" class="sugar_calendar_datepicker" name="end_date" id="end_date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="yyyy-mm-dd" />
					</div>

					<div class="event-time" <?php echo $hidden; ?>>
						<span class="sc-time-separator"><?php esc_html_e( 'at', 'sugar-calendar' ); ?></span>
						<?php

						// End Hour
						sugar_calendar_time_dropdown( array(
							'first'    => '&nbsp;',
							'id'       => 'end_time_hour',
							'name'     => 'end_time_hour',
							'items'    => $hours,
							'selected' => $end_hour
						) );

						?><span class="sc-time-separator"> : </span><?php

						// End Minute
						sugar_calendar_time_dropdown( array(
							'first'    => '&nbsp;',
							'id'       => 'end_time_minute',
							'name'     => 'end_time_minute',
							'items'    => $minutes,
							'selected' => $end_minute
						) );

						// End AM/PM
						if ( '12' === $clock ) :
							?><select id="end_time_am_pm" name="end_time_am_pm" class="sc-select-chosen sc-time">
								<option value="">&nbsp;</option>
								<option value="am" <?php selected( $end_am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'sugar-calendar' ); ?></option>
								<option value="pm" <?php selected( $end_am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'sugar-calendar' ); ?></option>
							</select><?php
						endif;
					?></div><?php

					// End Time Zone
					if ( true === $show_multi_tz ) :

						?><div class="event-time-zone"><?php

							sugar_calendar_timezone_dropdown( array(
								'id'      => 'end_tz',
								'name'    => 'end_tz',
								'current' => $end_tz
							) );

						?></div><?php

					endif;

				?></td>
			</tr>

			<?php

			// Start & end time zones
			if ( true === $show_single_tz ) : ?>

				<tr class="time-zone-row" <?php echo $hidden; ?>>
					<th>
						<label for="start_tz"><?php esc_html_e( 'Time Zone', 'sugar-calendar'); ?></label>
					</th>

					<td>
						<div class="event-time-zone"><?php

							sugar_calendar_timezone_dropdown( array(
								'id'      => 'start_tz',
								'name'    => 'start_tz',
								'current' => $start_tz
							) );

						?></div>
					</td>
				</tr>

			<?php endif; ?>

		</tbody>
	</table>

	<?php

	echo ob_get_clean();
}

/**
 * Output the event location meta-box section
 *
 * @since  2.0.0
 *
 * @param Event $event The event object
*/
function section_location( $event = null ) {

	// Setup empty Event if malformed
	if ( ! is_object( $event ) ) {
		$event = new Sugar_Calendar\Event();
	}

	// Location
	$location = $event->location;

	// Start an output buffer
	ob_start(); ?>

	<table class="form-table rowfat">
		<tbody>

			<?php if ( apply_filters( 'sugar_calendar_location', true ) ) : ?>

				<tr>
					<th>
						<label for="location"><?php esc_html_e( 'Location', 'sugar-calendar' ); ?></label>
					</th>

					<td>
						<label>
							<textarea name="location" id="location" placeholder="<?php esc_html_e( '(Optional)', 'sugar-calendar' ); ?>"><?php echo esc_textarea( $location ); ?></textarea>
						</label>
					</td>
				</tr>

			<?php endif; ?>
		</tbody>
	</table>

	<?php

	// End & flush the output buffer
	echo ob_get_clean();
}

/**
 * Output the event legacy meta-box section
 *
 * @since  2.0.17
 */
function section_legacy() {

	// Start an output buffer
	ob_start(); ?>

	<table class="form-table rowfat">
		<tbody>

			<?php do_action( 'sc_event_meta_box_before' ); ?>

			<?php do_action( 'sc_event_meta_box_after' ); ?>

		</tbody>
	</table>

	<?php

	// End & flush the output buffer
	echo ob_get_clean();
}
