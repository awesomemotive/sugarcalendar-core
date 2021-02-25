<?php
/**
 * Sugar Calendar Admin Posts
 *
 * @package Plugins/Site/Events/Admin/Posts
 */
namespace Sugar_Calendar\Admin\Posts;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Set the placeholder text for the title field for this post type.
 *
 * @since 2.0.0
 *
 * @param   string   $title The placeholder text
 * @param   WP_Post  $post  The current post
 *
 * @return  string          The updated placeholder text
 */
function title( $title, \WP_Post $post ) {

	// Override if primary post type
	if ( sugar_calendar_get_event_post_type_id() === $post->post_type ) {
		$title = esc_html__( 'Name this event', 'sugar-calendar' );
	}

	// Return possibly modified title
	return $title;
}

/**
 * Copy an Event, from within any admin area List Table row action.
 *
 * @since 2.1.7
 *
 * @param int $post_id
 */
function copy( $post_id = 0 ) {

	// Error if no post ID
	if ( empty( $post_id ) ) {
		wp_die( esc_html__( 'Invalid item ID.', 'sugar-calendar' ) );
	}

	// Action ID
	$action = 'sc_copy';

	// Check the nonce
	check_admin_referer( "{$action}-post_{$post_id}" );

	// Get the Post
	$post = get_post( $post_id );

	// Error if Post does not exist
	if ( empty( $post ) ) {
		wp_die( esc_html__( 'The item you are trying to copy no longer exists.', 'sugar-calendar' ) );
	}

	// Check Post-Type
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	// Error if Post-Type is invalid
	if ( empty( $post_type_object ) || ! post_type_supports( $post_type, 'events' ) ) {
		wp_die( esc_html__( 'Sorry, this item cannot be copied.', 'sugar-calendar' ) );
	}

	// Error if current user cannot edit the original post
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to copy this item.', 'sugar-calendar' ) );
	}

	// Get the Event for the Post
	$event = sugar_calendar_get_event_by_object( $post_id, 'post' );

	// Error if missing Event data
	if ( empty( $event ) ) {
		wp_die( esc_html__( 'This post does not have event data.', 'sugar-calendar' ) );
	}

	// New status is always draft
	$new_status = 'draft';

	// New title always has suffix
	$new_title  = $post->post_title . ' - ' . esc_html_x( 'Copy', 'noun', 'sugar-calendar' );

	// Copy the Post
	$new_post_id = sugar_calendar_copy_post( $post_id, array(
		'post_title'  => $new_title,
		'post_status' => $new_status
	) );

	// Error if Post was not copied
	if ( empty( $new_post_id ) ) {
		wp_die( esc_html__( 'Error copying the item.', 'sugar-calendar' ) );
	}

	// Copy the Event
	$new_event_id = sugar_calendar_copy_event( $event->id, array(
		'title'     => $new_title,
		'status'    => $new_status,
		'object_id' => $new_post_id
	) );

	// Error if Event was not copied
	if ( empty( $new_event_id ) ) {
		wp_die( esc_html__( 'Error copying the item.', 'sugar-calendar' ) );
	}

	// Sendback an indication that copy succeeded
	$sendback = add_query_arg(
		array(
			'copied' => 1,
			'ids'    => $post_id,
		),
		wp_get_referer()
	);

	// Redirect
	wp_safe_redirect( $sendback );
	exit;
}

/**
 * Filter the messages array and add custom messages for the built-in Post Type.
 *
 * @since 2.0.0
 *
 * @global WP_Post $post
 * @param array $messages
 *
 * @return array
 */
function updated_messages( $messages = array() ) {
	global $post;

	// Permalink
	$permalink = get_permalink( $post->ID );
	if ( empty( $permalink ) ) {
		$permalink = '';
	}

	// Preview URL
	$preview_url = get_preview_post_link( $post );

	// Preview post link
	$preview_post_link_html = sprintf( ' <a target="_blank" href="%1$s">%2$s</a>',
		esc_url( $preview_url ),
		esc_html__( 'Preview event', 'sugar-calendar' )
	);

	// Scheduled post preview link
	$scheduled_post_link_html = sprintf( ' <a target="_blank" href="%1$s">%2$s</a>',
		esc_url( $permalink ),
		esc_html__( 'Preview event', 'sugar-calendar' )
	);

	// View post link
	$view_post_link_html = sprintf( ' <a href="%1$s">%2$s</a>',
		esc_url( $permalink ),
		esc_html__( 'View event', 'sugar-calendar' )
	);

	// Scheduled (uses WordPress site locale & timezone)
	$format         = esc_html_x( 'M j, Y @ H:i', 'Date formatting', 'sugar-calendar' );
	$timestamp      = strtotime( $post->post_date );
	$scheduled_date = date_i18n( $format, $timestamp );

	// Add post type to messages array
	$messages[ sugar_calendar_get_event_post_type_id() ] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => esc_html__( 'Event updated.',   'sugar-calendar' ) . $view_post_link_html,
		4  => esc_html__( 'Event updated.',   'sugar-calendar' ),
		5  => isset( $_GET['revision'] )
			? sprintf( esc_html__( 'Event restored to revision from %s.', 'sugar-calendar' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
			: false,
		6  => esc_html__( 'Event created.',   'sugar-calendar' ) . $view_post_link_html,
		7  => esc_html__( 'Event saved.',     'sugar-calendar' ),
		8  => esc_html__( 'Event submitted.', 'sugar-calendar' ) . $preview_post_link_html,
		9  => sprintf( esc_html__( 'Event scheduled for: %s.', 'sugar-calendar' ), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
		10 => esc_html__( 'Event draft updated.', 'sugar-calendar' ) . $preview_post_link_html,
	);

	// Return
	return $messages;
}

/**
 * Hides the inline-edit-group from the admin form
 *
 * @since 2.0.0
 */
function hide_quick_bulk_edit() {

	// Bail if not an event post type
	if ( ! post_type_supports( get_current_screen()->post_type, 'events' ) ) {
		return;
	}

	?>
	<script>
		jQuery( document ).ready( function( $ ) {
			$("#the-list").on("click", "a.editinline", function () {
				jQuery(".inline-edit-group").hide();
				jQuery(".inline-edit-date").hide();
			} );
		});
	</script>
	<?php
}

/**
 * Detect & redirect away from any old admin-area post-type pages.
 *
 * This is necessary to prevent savvy users from accidentally finding hidden
 * Event Post Type pages that are still hanging around behind the scenes.
 *
 * Currently hooked to `load-edit.php` but could be changed later to handle more
 * specific cases.
 *
 * @since 2.0.0
 */
function redirect_old_post_type() {
	global $typenow;

	// Redirect if global post-type matches our Event post type
	if ( sugar_calendar_get_event_post_type_id() === $typenow ) {

		// Get base
		$base = sugar_calendar_get_admin_base_url();

		// Setup default redirection
		$redirect = $base;

		// Default arguments
		$args = array();

		// Get allowed keys
		$taxos = sugar_calendar_get_object_taxonomies();

		// Loop through taxonomies looking for terms
		if ( ! empty( $taxos ) ) {

			// Loop
			foreach ( $taxos as $tax ) {

				// Look for terms in URL
				if ( isset( $_GET[ $tax ] ) ) {

					// Add to allowed args
					$args[ $tax ] = sanitize_key( $_GET[ $tax ] );
				}
			}

			// Maybe add arguments to base
			if ( ! empty( $args ) ) {
				$redirect = add_query_arg( $args, $base );
			}
		}

		wp_safe_redirect( $redirect );
		exit();
	}
}
