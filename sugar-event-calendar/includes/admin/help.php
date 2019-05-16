<?php
/**
 * Event Admin Help
 *
 * @package Plugins/Site/Events/Admin/Help
 */
namespace Sugar_Calendar\Admin\Help;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Admin help tabs
 *
 * @since 2.0.0
 */
function add_tabs() {

	// Bail if not primary post type screen
	if ( ! sugar_calendar_admin_is_events_page() ) {
		return;
	}

	// Calendar
	get_current_screen()->add_help_tab( array(
		'id'		=> 'calendars',
		'title'		=> esc_html__( 'Event Views', 'sugar-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a calendar that lays out your content chronologically.',   'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'View events in month, week, day, and list modes.',                 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Clicking an event shows a snapshot of the event for that period.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events have icons and styling that helps differentiate them.',     'sugar-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Events', 'sugar-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'Most events are single-day, only for a few hours.',  'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Location data can be attached.',                     'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Some events span multiple days, or have intervals.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Organize events using specific calendars.',          'sugar-calendar' ) . '</li></ul>'	) );

	// Month View
	get_current_screen()->add_help_tab( array(
		'id'		=> 'month',
		'title'		=> esc_html__( '&mdash; Month', 'sugar-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a traditional monthly calendar.',        'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Events are listed chronologically in each day.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events may span several days.',                  'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'All-day events appear towards the top.',         'sugar-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Navigation', 'sugar-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'Filter specific events via the filter-bar.',         'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through years with double-arrow buttons.',  'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through months with single-arrow buttons.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Return to today with the double-colon button.',      'sugar-calendar' ) . '</li></ul>'
	) );

	// Week View
	get_current_screen()->add_help_tab( array(
		'id'		=> 'week',
		'title'		=> __( '&mdash; Week', 'sugar-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a traditional weekly calendar view.',    'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Events are listed chronologically in each day.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events spanning more than 1 day are omitted.',   'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'All-day events appear in the top row.',          'sugar-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Navigation', 'sugar-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'Filter specific events via the filter-bar.',         'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through months with double-arrow buttons.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through weeks with single-arrow buttons.',  'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Return to today with the double-colon button.',      'sugar-calendar' ) . '</li></ul>'
	) );

	// Day View
	get_current_screen()->add_help_tab( array(
		'id'		=> 'day',
		'title'		=> __( '&mdash; Day', 'sugar-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a traditional daily calendar view.',     'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Events are listed chronologically for the day.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events spanning more than 1 day are shown.',     'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'All-day events appear in the top row.',          'sugar-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Navigation', 'sugar-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'Filter specific events via the filter-bar.',        'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through weeks with double-arrow buttons.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through days with single-arrow buttons.',  'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Return to today with the double-colon button.',     'sugar-calendar' ) . '</li></ul>'
	) );

	// List View
	get_current_screen()->add_help_tab( array(
		'id'		=> 'list',
		'title'		=> esc_html__( '&mdash; List', 'sugar-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a traditional list view.',                  'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Events are listed chronologically by their start.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events may span several days.',                     'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'All-day events are listed as such.',                'sugar-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Navigation', 'sugar-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'Filter specific events via the filter-bar.',    'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through events with arrow buttons.',   'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Sort events by clicking column headings.',      'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Switch views using the filter-bar icons.',      'sugar-calendar' ) . '</li></ul>'
	) );

	// Help Sidebar
	get_current_screen()->set_help_sidebar(
		'<p><i class="dashicons dashicons-calendar-alt"></i> ' . esc_html__( 'Regular Event', 'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-location"></i> ' . esc_html__( 'Has Location',  'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-clock"></i> '    . esc_html__( 'All Day',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-backup"></i> '   . esc_html__( 'Recurring',     'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-trash"></i> '    . esc_html__( 'Trashed',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-hidden"></i> '   . esc_html__( 'Private',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-lock"></i> '     . esc_html__( 'Protected',     'sugar-calendar' ) . '</p>'
	);
}
