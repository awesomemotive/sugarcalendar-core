<?php
/**
 * Events Admin Nav
 *
 * @package Plugins/Site/Events/Admin/Nav
 */
namespace Sugar_Calendar\Admin\Nav;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the product tabs for Events, Calendars, and more.
 *
 * @since 2.0.0
 */
function display() {
	global $taxnow;

	// Initial tab array
	$tabs = array(
		'sugar-calendar' => array(
			'name' => __( 'Events', 'sugar-calendar' ),
			'url'  => sugar_calendar_get_admin_url()
		)
	);

	// Get the post type object
	$post_type        = sugar_calendar_get_event_post_type_id();
	$post_type_object = get_post_type_object( $post_type );

	// Get the taxonomies
	$taxonomies = get_object_taxonomies( $post_type, 'objects' );

	// Maybe add taxonomies to tabs array
	if ( ! empty( $taxonomies ) ) {
		foreach ( $taxonomies as $tax => $details ) {
			$tabs[ $tax ] = array(
				'name' => $details->labels->menu_name,
				'url'  => add_query_arg( array(
					'taxonomy'  => $tax,
					'post_type' => $post_type
				), admin_url( 'edit-tags.php' ) )
			);
		}
	}

	// Filter the tabs
	$tabs = apply_filters( 'sugar_calendar_admin_nav', $tabs );

	// Taxonomies
	if ( isset( $taxnow ) && in_array( $taxnow, array_keys( $taxonomies ), true ) ) {
		$active_tab = sanitize_key( $taxnow );

	// Default to Events
	} else {
		$active_tab = 'sugar-calendar';
	}

	// Start a buffer
	ob_start() ?>

	<div class="clear"></div>
	<h2 class="nav-tab-wrapper sc-nav-tab-wrapper sc-tab-clear"><?php

		// Loop through tabs, and output links
		foreach ( $tabs as $tab_id => $tab ) :

			// Setup the class to denote a tab is active
			$active_class = ( $active_tab === $tab_id )
				? 'nav-tab-active'
				: '';

			?><a href="<?php echo esc_url( $tab['url'] ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>"><?php
				echo esc_html( $tab['name'] );
			?></a><?php

		endforeach;

		if ( current_user_can( $post_type_object->cap->create_posts ) ) :

			?><a href="<?php echo esc_url( add_query_arg( array( 'post_type' => $post_type ), admin_url( 'post-new.php' ) ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add New', 'sugar-calendar' ); ?>
			</a><?php

		endif;

	?></h2>
	<br>

	<?php

	// Output the current buffer
	echo ob_get_clean();
}

/**
 * When the Event Calendars list table loads, call the function to view our tabs.
 *
 * This function is necessary because WordPress does not have hooks in places
 * that allow this to be injected more easily.
 *
 * @since 2.0.0
 */
function taxonomy_tabs() {
	global $taxnow;

	// Bail if not viewing a taxonomy
	if ( empty( $taxnow ) ) {
		return;
	}

	// Get taxonomies
	$taxonomy   = sanitize_key( $taxnow );
	$taxonomies = get_object_taxonomies( sugar_calendar_get_event_post_type_id() );

	// Bail if current taxonomy is not an event taxonomy
	if ( empty( $taxonomies ) || ! in_array( $taxonomy, $taxonomies, true ) ) {
		return;
	}

	// Output the tabs
	?><div class="wrap sc-tab-wrap"><?php
		display();
	?></div><?php
}
