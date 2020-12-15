<?php

/**
 * Event Capabilities
 *
 * @package Plugins/Site/Events/Capabilities
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Maps event capabilities
 *
 * @since 2.0.0
 *
 * @link https://core.trac.wordpress.org/ticket/30991#comment:15 Bugs with mappings
 *
 * @param  array   $caps     Capabilities for meta capability
 * @param  string  $cap      Capability name
 * @param  int     $user_id  User id
 * @param  array   $args     Arguments
 *
 * @return array   Actual capabilities for meta capability
 */
function sugar_calendar_post_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// What capability is being checked?
	switch ( $cap ) {

		// Deleting
		case 'delete_event' :

			// Bail if no post ID
			if ( empty( $args[0] ) ) {
				break;
			}

			$post = get_post( $args[0] );
			if ( ! $post ) {
				$caps[] = 'do_not_allow';
				break;
			}

			if ( 'revision' == $post->post_type ) {
				$post = get_post( $post->post_parent );
				if ( ! $post ) {
					$caps[] = 'do_not_allow';
					break;
				}
			}

			// If the post author is set and the user is the author...
			if ( (int) $user_id === $post->post_author ) {

				// If the post is published or scheduled...
				if ( in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
					$caps = array( 'delete_published_posts' );

				} elseif ( 'trash' === $post->post_status ) {
					$status = get_post_meta( $post->ID, '_wp_trash_meta_status', true );

					if ( in_array( $status, array( 'publish', 'future' ), true ) ) {
						$caps = array( 'delete_published_posts' );
					} else {
						$caps = array( 'delete_posts' );
					}

				// If the post is draft...
				} else {
					$caps = array( 'delete_posts' );
				}

			// The user is trying to edit someone else's post.
			} else {
				$caps = array( 'delete_others_posts' );

				// The post is published or scheduled, extra cap required.
				if ( in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
					$caps = array( 'delete_published_posts' );
				} elseif ( 'private' === $post->post_status ) {
					$caps = array( 'delete_private_posts' );
				}
			}
			break;

		// Editing
		case 'edit_event' :

			// Bail if no post ID
			if ( empty( $args[0] ) ) {
				break;
			}

			$post = get_post( $args[0] );
			if ( empty( $post ) ) {
				$caps = array( 'do_not_allow' );
				break;
			}

			if ( 'revision' === $post->post_type ) {
				$post = get_post( $post->post_parent );
				if ( empty( $post ) ) {
					$caps = array( 'do_not_allow' );
					break;
				}
			}

			// If the post author is set and the user is the author...
			if ( (int) $user_id === (int) $post->post_author ) {

				// If the post is published or scheduled...
				if ( in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
					$caps = array( 'edit_published_posts' );

				} elseif ( 'trash' === $post->post_status ) {
					$status = get_post_meta( $post->ID, '_wp_trash_meta_status', true );

					if ( in_array( $status, array( 'publish', 'future' ), true ) ) {
						$caps = array( 'edit_published_posts' );
					} else {
						$caps = array( 'edit_posts' );
					}

				// If the post is draft...
				} else {
					$caps = array( 'edit_posts' );
				}

			// The user is trying to edit someone else's post.
			} else {
				$caps = array( 'edit_others_posts' );

				// The post is published or scheduled, extra cap required.
				if ( in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
					$caps = array( 'edit_published_posts' );
				} elseif ( 'private' === $post->post_status ) {
					$caps = array( 'edit_private_posts' );
				}
			}
			break;

		// Reading
		case 'read_event' :

			// Bail if no post ID
			if ( empty( $args[0] ) ) {
				break;
			}

			$post = get_post( $args[0] );
			if ( empty( $post ) ) {
				$caps = array( 'do_not_allow' );
				break;
			}

			if ( 'revision' == $post->post_type ) {
				$post = get_post( $post->post_parent );
				if ( empty( $post ) ) {
					$caps = array( 'do_not_allow' );
					break;
				}
			}

			$status_obj = get_post_status_object( $post->post_status );
			if ( empty( $status_obj ) ) {
				$caps = 'do_not_allow';
			} else {
				if ( true === $status_obj->public ) {
					$caps = array( 'read' );
					break;
				}

				if ( (int) $user_id === $post->post_author ) {
					$caps = array( 'read' );
				} elseif ( true === $status_obj->private ) {
					$caps = array( 'read_private_posts' );
				} else {
					$caps = map_meta_cap( 'edit_post', $user_id, $post->ID );
				}
			}

			break;

		// Remap
		case 'delete_events' :
			$caps = array( 'delete_posts' );
			break;

		case 'delete_others_events' :
			$caps = array( 'delete_others_events' );
			break;

		case 'create_events' :
		case 'edit_events' :
		case 'read_calendar' :
			$caps = array( 'edit_posts' );
			break;

		case 'edit_others_events' :
			$caps = array( 'edit_others_posts' );
			break;

		case 'publish_events' :
			$caps = array( 'publish_posts' );
			break;
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_post_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Maps event category capabilities
 *
 * @since 2.0.0
 *
 * @param   array     $caps      Capabilities for meta capability
 * @param   string    $cap       Capability name
 * @param   int       $user_id   User id
 * @param   array     $args      Arguments
 *
 * @return  array     Actual capabilities for meta capability
 */
function sugar_calendar_category_meta_caps( $caps, $cap, $user_id, $args ) {

	// What capability is being checked?
	switch ( $cap ) {
		case 'manage_event_calendars' :
		case 'edit_event_calendars'   :
		case 'delete_event_calendars' :
		case 'assign_event_calendars' :
			$caps = array( 'publish_posts' );
			break;
	}

	// Filter & return
	return apply_filters( 'sugar_calendar_category_meta_caps', $caps, $cap, $user_id, $args );
}
