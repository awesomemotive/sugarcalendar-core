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
 * Display the tabs for Events, Calendars, and more.
 *
 * @since 2.0.0
 */
function display() {
	global $taxnow;

	// Get the post type & labels
	$post_type = sugar_calendar_get_event_post_type_id();
	$name      = sugar_calendar_get_post_type_label( $post_type, 'name', esc_html__( 'Events', 'sugar-calendar' ) );

	// Initial tab array
	$tabs = array(
		'sugar-calendar' => array(
			'name' => $name,
			'url'  => sugar_calendar_get_admin_url()
		)
	);

	// Get the taxonomies
	$taxonomies = sugar_calendar_get_object_taxonomies(
		$post_type,
		'objects'
	);

	// Maybe add taxonomies to tabs array
	if ( ! empty( $taxonomies ) ) {
		foreach ( $taxonomies as $tax ) {

			// Skip if private
			if ( empty( $tax->public ) ) {
				continue;
			}

			// Skip if current user cannot manage
			if ( ! current_user_can( $tax->cap->manage_terms ) ) {
				continue;
			}

			// Add taxonomy to tabs
			$tabs[ $tax->name ] = array(
				'name' => $tax->labels->menu_name,
				'url'  => add_query_arg( array(
					'taxonomy'  => $tax->name,
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

	// Output the tabs
	echo get( $tabs, $active_tab );
}

/**
 * Get the product tabs for Events, Calendars, and more.
 *
 * @since 2.0.0
 *
 * @param array $navs Array of navigational items
 * @param string $selected ID of the currently selected nav item
 *
 * @return string HTML for the nav
 */
function get( $navs = array(), $selected = false ) {

	// Fallback to a "General" nav if none passed in (don't rely on this!)
	if ( empty( $navs ) ) {
		$navs = array(
			'general' => array(
				'name' => esc_html__( 'General', 'sugar-calendar' ),
				'url'  => esc_url_raw( $_SERVER['REQUEST_URI'] )
			)
		);
	}

	// Maybe default to first nav item key
	if ( empty( $selected ) ) {
		$selected = array_key_first( $navs );
	}

	/**
	 * Filter the navigation items before they are used to generate HTML.
	 *
	 * @since 2.0.19
	 */
	$navs = (array) apply_filters( 'sugar_calendar_admin_nav_items', $navs, $selected );

	// Start a buffer
	ob_start();

	/**
	 * Fires before the admin navigation.
	 *
	 * @since 2.0.19
	 */
	do_action( 'sugar_calendar_admin_nav_before_wrapper', $navs, $selected ); ?>

	<div class="clear"></div>
	<h2 class="nav-tab-wrapper sc-nav-tab-wrapper sc-tab-clear"><?php

		/**
		 * Fires before the admin navigation inside the wrapper.
		 *
		 * @since 2.0.19
		 */
		do_action( 'sugar_calendar_admin_nav_before_items', $navs, $selected );

		// Loop through tabs, and output links
		foreach ( $navs as $nav_id => $nav ) :

			/**
			 * This PHP is written this way to avoid whitespace between links.
			 *
			 * Do not attempt to clean this up without first testing the output.
			 */
			?><a href="<?php echo esc_url( $nav['url'] ); ?>" class="nav-tab <?php selected_class( $selected, $nav_id ); ?>"><?php

				// May contain HTML
				echo $nav['name'];

			?></a><?php

		endforeach;

		/**
		 * Fires after the admin navigation inside the wrapper.
		 *
		 * @since 2.0.19
		 */
		do_action( 'sugar_calendar_admin_nav_after_items', $navs, $selected );
	?></h2>

	<?php

	/**
	 * Fires after the admin navigation.
	 *
	 * @since 2.0.19
	 */
	do_action( 'sugar_calendar_admin_nav_after_wrapper', $navs, $selected );

	// Get the current buffer
	$retval = ob_get_clean();

	// Filter & return
	return (string) apply_filters( 'sugar_calendar_admin_get_nav', $retval, $navs, $selected );
}

/**
 * Output the appropriate class for the selected nav item.
 *
 * @since 2.0.19
 *
 * @param string $selected
 * @param string $nav
 *
 * @return string
 */
function selected_class( $selected = '', $nav = '' ) {
	echo ( $selected === $nav )
		? 'nav-tab-active'
		: '';
}

/**
 * Maybe add the "Add New" button to the end of the navigation.
 *
 * This function is a necessary abstraction to allow this API to be reused in
 * "Settings" and by external add-ons. See "Event Ticketing" for usage details.
 *
 * @since 2.0.19
 */
function add_new() {

	// Bail if not an admin-area Events page
	if ( ! sugar_calendar_admin_is_events_page() ) {
		return;
	}

	// Get the post type object
	$post_type        = sugar_calendar_get_event_post_type_id();
	$post_type_object = get_post_type_object( $post_type );

	// Bail if user cannot add a new Event
	if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
		return;
	}

	// Singular name for Post type or Taxonomy
	if ( sugar_calendar_admin_is_taxonomy_page() ) {
		$name = get_taxonomy( get_current_screen()->taxonomy )->labels->singular_name;
		$url  = '#tag-name';
	} else {
		$name = sugar_calendar_get_post_type_label( $post_type, 'singular_name' );
		$url  = $url  = add_query_arg( array( 'post_type' => $post_type ), admin_url( 'post-new.php' ) );
	}

	// Default "Add New" text
	$text = sprintf( esc_html__( 'Add %s', 'sugar-calendar' ), $name );

	?><a href="<?php echo esc_url( $url ); ?>" class="page-title-action">
		<?php echo esc_html( $text ); ?>
	</a><?php
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
	$post_type  = sugar_calendar_get_event_post_type_id();
	$taxonomies = sugar_calendar_get_object_taxonomies( $post_type );

	// Bail if current taxonomy is not an event taxonomy
	if ( empty( $taxonomies ) || ! in_array( $taxonomy, $taxonomies, true ) ) {
		return;
	}

	// Output the tabs
	?><div class="wrap sc-tab-wrap"><?php

		display(); ?>

	</div><?php
}
