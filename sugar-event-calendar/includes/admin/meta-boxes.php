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
	$meta_box->setup( $post );
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
 * @param obejct $object    Connected object data
 *
 * @return int|void
 */
function save( $object_id = 0, $object = null ) {

	// Bail if meta-box cannot be saved
	if ( ! can_save_meta_box( $object_id, $object ) ) {
		return $object_id;
	}

	/** Starts ****************************************************************/

	// Get calendar date
	$date = ! empty( $_POST['start_date'] )
		? strtotime( sanitize_text_field( $_POST['start_date'] ) )
		: current_time( 'timestamp' );

	// Hour
	$hour = ! empty( $_POST['start_time_hour'] )
		? sanitize_text_field( $_POST['start_time_hour'] )
		: 0;

	// Minutes
	$minutes = ! empty( $_POST['start_time_minute'] )
		? sanitize_text_field( $_POST['start_time_minute'] )
		: 0;

	// Day/night
	$am_pm = ! empty( $_POST['start_time_am_pm'] )
		? sanitize_text_field( $_POST['start_time_am_pm'] )
		: 'am';

	/** Ends ******************************************************************/

	// Calendar date is set
	$end_date = ! empty( $_POST['end_date'] )
		? strtotime( sanitize_text_field( $_POST['end_date'] ) )
		: null;

	// Hour
	$end_hour = ! empty( $_POST['end_time_hour'] )
		? sanitize_text_field( $_POST['end_time_hour'] )
		: 0;

	// Minutes
	$end_minutes = ! empty( $_POST['end_time_minute'] )
		? sanitize_text_field( $_POST['end_time_minute'] )
		: 0;

	// Day/night
	$end_am_pm = ! empty( $_POST['end_time_am_pm']  )
		? sanitize_text_field( $_POST['end_time_am_pm'] )
		: 'am';

	/** All Day ***************************************************************/

	// Get all-day status
	$all_day = ! empty( $_POST['all_day'] )
		? (bool) $_POST['all_day']
		: false;

	// Set all day if no end date
	if ( ( false === $all_day ) && ( empty( $minutes ) && empty( $hour ) && empty( $end_minutes ) && empty( $end_hour ) ) ) {
		if ( empty( $end_date ) || ( $date === $end_date ) ) {

			// Make all-day event
			$all_day = true;

			// Make single-day event
			if ( empty( $end_date ) ) {
				$end_date = $date;
			}
		}
	}

	/** Combine ***************************************************************/

	// Maybe tweak hours
	$hour     = adjust_hour_for_meridiem( $hour,     $am_pm     );
	$end_hour = adjust_hour_for_meridiem( $end_hour, $end_am_pm );

	// Make timestamps from pieces
	$date = mktime( intval( $hour ), intval( $minutes ), 0, date( 'm', $date ), date( 'd', $date ), date( 'Y', $date ) );

	// End dates for all-day events must be end of day
	if ( true === $all_day ) {
		$end_date = mktime( 23, 59, 59, date( 'm', $end_date ), date( 'd', $end_date ), date( 'Y', $end_date ) );

	// Use the passed end date and time
	} else {
		$end_date = mktime( intval( $end_hour ), intval( $end_minutes ), 0, date( 'm', $end_date ), date( 'd', $end_date ), date( 'Y', $end_date ) );
	}

	// End dates can't be before start dates
	if ( $end_date <= $date ) {
		$minimum  = sugar_calendar_get_minimum_event_duration();
		$end_date = ! empty( $minimum )
			? strtotime( '+' . $minimum, $date )
			: $date;
	}

	/** Repeat ****************************************************************/

	// Repeat
	$repeat = ! empty( $_POST['recurrence'] )
		? sanitize_key( $_POST['recurrence'] )
		: '';

	// Expire
	$expire = ! empty( $_POST['recurrence_end'] )
		? sanitize_text_field( $_POST['recurrence_end'] )
		: '';

	// Recurrence expriation
	if ( ! empty( $repeat ) && ! empty( $expire ) ) {
		$recur_end         = strtotime( $expire );
		$recur_end_hour    = 0;
		$recur_end_minutes = 0;
		$recur_end_am_pm   = 'am';
		$recur_end_hour    = adjust_hour_for_meridiem( $recur_end_hour, $recur_end_am_pm );
		$recur_end_date    = mktime( intval( $recur_end_hour ), intval( $recur_end_minutes ), 0, date( 'm', $recur_end ), date( 'd', $recur_end ), date( 'Y', $recur_end ) );
	} else {
		$recur_end_date = '';
	}

	/** Save ******************************************************************/

	// Save the start date & time
	if ( ! empty( $date ) ) {
		$date = gmdate( 'Y-m-d H:i:s', $date );
	}

	// Save the end date & time
	if ( ! empty( $end_date ) ) {
		$end_date = gmdate( 'Y-m-d H:i:s', $end_date );
	}

	// Save only if repeating with an end
	if ( ! empty(  $repeat ) && ! empty( $recur_end_date ) ) {
		$recur_end_date = gmdate( 'Y-m-d H:i:s', $recur_end_date );
	}

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
		'object_id'           => $object_id,
		'object_type'         => $type,
		'object_subtype'      => $subtype,
		'title'               => $title,
		'content'             => $content,
		'status'              => $status,
		'start'               => $date,
		'start_tz'            => '',
		'end'                 => $end_date,
		'end_tz'              => '',
		'all_day'             => $all_day,
		'recurrence'          => $repeat,
		'recurrence_interval' => 0,
		'recurrence_count'    => 0,
		'recurrence_end'      => $recur_end_date,
		'recurrence_end_tz'   => '',
	) );

	// Update
	if ( ! empty( $event->id ) ) {
		$success = sugar_calendar_update_event( $event->id, $to_save );

	// Add
	} else {
		$success = sugar_calendar_add_event( $to_save );
	}
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
