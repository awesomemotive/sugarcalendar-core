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
