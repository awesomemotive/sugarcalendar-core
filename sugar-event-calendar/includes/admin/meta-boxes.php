<?php
/**
 * Event Metaboxes
 *
 * @package Plugins/Site/Event/Admin/Metaboxes
 */
namespace Sugar_Calendar\Admin\Editor\Meta;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Event Types Metabox
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
 * Event Metabox
 *
 * @since 2.0.0
*/
function add() {

	// Sections
	add_meta_box(
		'sugar_calendar_event_details',
		esc_html__( 'Event', 'sugar-calendar' ),
		'Sugar_Calendar\\Admin\\Editor\\Meta\\box',
		sugar_calendar_allowed_post_types(),
		'above_event_editor',
		'high'
	);

	// Details
	add_meta_box(
		'sugar_calendar_details',
		esc_html__( 'Details', 'sugar-calendar' ),
		'Sugar_Calendar\\Admin\\Editor\\Meta\\details',
		sugar_calendar_get_event_post_type_id(),
		'above_event_editor',
		'default'
	);
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
 * Output the event duration metabox
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
 * @return boolean
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
 * Metabox save
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
	$all_day = prepare_all_day();
	$start   = prepare_start();
	$end     = prepare_end();

	// Sanitize start & end to prevent data entry errors
	$start   = sanitize_start( $start, $end, $all_day );
	$end     = sanitize_end( $end, $start, $all_day );
	$all_day = sanitize_all_day( $all_day, $start, $end );

	// Time zones (empty for UTC by default)
	$start_tz = $end_tz = '';

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
	$nt  = date( 'Y-m-d H:i:s', mktime(
		0,
		0,
		0,
		date( 'n', $now ),
		date( 'j', $now ),
		date( 'Y', $now )
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

	// Day/night
	$am_pm = ! empty( $_POST[ $prefix . 'time_am_pm' ] )
		? sanitize_text_field( $_POST[ $prefix . 'time_am_pm' ] )
		: 'am';

	// Maybe tweak hours
	$hour = adjust_hour_for_meridiem( $hour, $am_pm );

	// Make timestamp from pieces
	$timestamp = mktime(
		intval( $hour ),
		intval( $minutes ),
		intval( $seconds ),
		date( 'n', $date ),
		date( 'j', $date ),
		date( 'Y', $date )
	);

	// Format for MySQL
	$retval = date( 'Y-m-d H:i:s', $timestamp );

	// Return
	return $retval;
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
		$start_int = mktime(
			0,
			0,
			0,
			date( 'n', $start_int ),
			date( 'j', $start_int ),
			date( 'Y', $start_int )
		);
	}

	// Format
	$retval = date( 'Y-m-d H:i:s', $start_int );

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
		( '00:00:00' === date( 'H:i:s', $start_int ) )
		&&
		( '23:59:59' === date( 'H:i:s', $end_int ) )
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

	// See if there a minimum duration to enforce...
	$minimum = sugar_calendar_get_minimum_event_duration();

	// Convert to integers for faster comparisons
	$start_int = strtotime( $start );
	$end_int   = strtotime( $end   );

	// Calculate the end, based on a minimum duration (if set)
	$end_compare = ! empty( $minimum )
		? strtotime( '+' . $minimum, $end_int )
		: $end_int;

	// Check if the user attempted to set an end date and/or time
	$has_end = has_end();

	// Bail if event duration exceeds the minimum (great!)
	if ( ( true === $has_end ) && ( $end_compare > $start_int ) ) {
		return $end;
	}

	// ...or the user attempted an end date and this isn't an all-day event
	if ( ( true === $has_end ) && ( false === $all_day ) ) {

		// If there is a minimum, the new end is the start + the minimum
		if ( ! empty( $minimum ) ) {
			$end_int = strtotime( '+' . $minimum, $start_int );

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
		$end_int = mktime(
			23,
			59,
			59,
			date( 'n', $end_int ),
			date( 'j', $end_int ),
			date( 'Y', $end_int )
		);
	}

	// Format
	$retval = date( 'Y-m-d H:i:s', $end_int );

	// Return the new end
	return $retval;
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
function calendars( $post, $box ) {
	$defaults = array( 'taxonomy' => 'category' );

	$args = ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) )
		? array()
		: $box['args'];

	$r        = wp_parse_args( $args, $defaults );
	$tax_name = esc_attr( $r['taxonomy'] );
	$taxonomy = get_taxonomy( $r['taxonomy'] ); ?>

	<div id="taxonomy-<?php echo $tax_name; ?>" class="categorydiv">

		<div id="<?php echo $tax_name; ?>-all">
			<?php

			$name = ( $tax_name === 'category' )
				? 'post_category'
				: 'tax_input[' . $tax_name . ']';

			// Allows for an empty term set to be sent. 0 is an invalid Term ID
			// and will be ignored by empty() checks.
			echo "<input type='hidden' name='{$name}[]' value='0' />"; ?>

			<ul id="<?php echo $tax_name; ?>checklist" data-wp-lists="list:<?php echo $tax_name; ?>" class="categorychecklist form-no-clear">
				<?php wp_terms_checklist( $post->ID, array( 'taxonomy' => $tax_name ) ); ?>
			</ul>
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

					<?php
					/**
					 * Filters the arguments for the taxonomy parent dropdown on the Post Edit page.
					 *
					 * @since 4.4.0
					 *
					 * @param array $parent_dropdown_args {
					 *     Optional. Array of arguments to generate parent dropdown.
					 *
					 *     @type string   $taxonomy         Name of the taxonomy to retrieve.
					 *     @type bool     $hide_if_empty    True to skip generating markup if no
					 *                                      categories are found. Default 0.
					 *     @type string   $name             Value for the 'name' attribute
					 *                                      of the select element.
					 *                                      Default "new{$tax_name}_parent".
					 *     @type string   $orderby          Which column to use for ordering
					 *                                      terms. Default 'name'.
					 *     @type bool|int $hierarchical     Whether to traverse the taxonomy
					 *                                      hierarchy. Default 1.
					 *     @type string   $show_option_none Text to display for the "none" option.
					 *                                      Default "&mdash; {$parent} &mdash;",
					 *                                      where `$parent` is 'parent_item'
					 *                                      taxonomy label.
					 * }
					 */
					$parent_dropdown_args = apply_filters( 'post_edit_category_parent_dropdown_args', array(
						'taxonomy'         => $tax_name,
						'hide_empty'       => 0,
						'name'             => 'new' . $tax_name . '_parent',
						'orderby'          => 'name',
						'hierarchical'     => 1,
						'show_option_none' => '&mdash; ' . $taxonomy->labels->parent_item . ' &mdash;',
					) );

					wp_dropdown_categories( $parent_dropdown_args ); ?>

					<input type="button" id="<?php echo $tax_name; ?>-add-submit" data-wp-lists="add:<?php echo $tax_name; ?>checklist:<?php echo $tax_name; ?>-add" class="button category-add-submit" value="<?php echo esc_attr( $taxonomy->labels->add_new_item ); ?>" />

					<?php wp_nonce_field( 'add-' . $tax_name, '_ajax_nonce-add-' . $tax_name, false ); ?>

					<span id="<?php echo $tax_name; ?>-ajax-response"></span>
				</p>
			</div>

		<?php endif; ?>

	</div>

	<?php
}
