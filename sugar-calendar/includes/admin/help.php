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
 * Admin help, calendar tabs
 *
 * @since 2.0.14
 */
function add_calendar_tabs() {

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
		'<p><i class="dashicons dashicons-location"></i> '     . esc_html__( 'Has Location',  'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-clock"></i> '        . esc_html__( 'All Day',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-backup"></i> '       . esc_html__( 'Recurring',     'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-trash"></i> '        . esc_html__( 'Trashed',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-hidden"></i> '       . esc_html__( 'Private',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-lock"></i> '         . esc_html__( 'Protected',     'sugar-calendar' ) . '</p>'
	);
}

/**
 * Admin help, settings tabs
 *
 * @since 2.0.14
 */
function add_settings_tabs() {

	// Day/Week table
	ob_start(); ?>

	<table class="sc-date-time-format-table sc-custom-date-table">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Day', 'sugar-calendar' ); ?></th>
			</tr>
		</thead>

		<tbody class="tbody">
			<tr>
				<th><code class="code">d</code></th>
				<td><?php esc_html_e( 'Day of the month, 2 digits with leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>01</code> to <code>31</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">D</code></th>
				<td><?php esc_html_e( 'A textual representation of a day, three letters', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>Mon</code> through <code>Sun</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">j</code></th>
				<td><?php esc_html_e( 'Day of the month without leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>1</code> to <code>31</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">l</code></th>
				<td><?php esc_html_e( 'A full textual representation of the day of the week', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>Sunday</code> through <code>Saturday</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">N</code></th>
				<td><?php esc_html_e( 'ISO-8601 numeric representation of the day of the week', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>1</code> (Mon) through <code>7</code> (Sun)', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">S</code></th>
				<td><?php esc_html_e( 'English ordinal suffix for the day of the month, 2 characters', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>st</code>, <code>nd</code>, <code>rd</code> or <code>th</code> <br>(Works well with <code>j</code>)', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">w</code></th>
				<td><?php esc_html_e( 'Numeric representation of the day of the week', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>0</code> (Sun) through <code>6</code> (Sat)', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">z</code></th>
				<td><?php esc_html_e( 'The day of the year (starting from 0)', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>0</code> through <code>365</code>', 'sugar-calendar' ); ?></td>
			</tr>
		</tbody>

		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Week', 'sugar-calendar' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<th><code class="code">W</code></th>
				<td><?php esc_html_e( 'ISO-8601 week number of year, weeks starting on Monday', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>42</code> (the 42nd week in the year)', 'sugar-calendar' ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php

	// Day/Week table
	$day_week_table = ob_get_clean();

	// Month table
	ob_start(); ?>

	<table class="sc-date-time-format-table sc-custom-date-table">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Month', 'sugar-calendar' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<th><code class="code">F</code></th>
				<td><?php esc_html_e( 'A full textual representation of a month, such as January or March', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>January</code> through <code>December</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">m</code></th>
				<td><?php esc_html_e( 'Numeric representation of a month, with leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>01</code> through <code>12</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">M</code></th>
				<td><?php esc_html_e( 'A short textual representation of a month, three letters', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>Jan</code> through <code>Dec</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">n</code></th>
				<td><?php esc_html_e( 'Numeric representation of a month, without leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>1</code> through <code>12</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">t</code></th>
				<td><?php esc_html_e( 'Number of days in the given month', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>28</code> through <code>31</code>', 'sugar-calendar' ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php

	// Month table
	$month_table = ob_get_clean();

	// Year table
	ob_start(); ?>

	<table class="sc-date-time-format-table sc-custom-date-table">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Year', 'sugar-calendar' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<th><code class="code">L</code></th>
				<td><?php esc_html_e( 'Whether it is a leap year', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>1</code> if it is a leap year, <code>0</code> otherwise.', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">o</code></th>
				<td><?php echo __( 'ISO-8601 week-numbering year.<br>Same as <code>Y</code> except if the week is for an adjacent year, year is used.', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>1999</code> or <code>2003</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">Y</code></th>
				<td><?php esc_html_e( 'A full numeric representation of a year, 4 digits', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>1999</code> or <code>2003</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">y</code></th>
				<td><?php esc_html_e( 'A two digit representation of a year', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>99</code> or <code>03</code>', 'sugar-calendar' ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php

	// Year table
	$year_table = ob_get_clean();

	// Time table
	ob_start(); ?>

	<table class="sc-date-time-format-table sc-custom-time-table">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Time', 'sugar-calendar' ); ?></th>
			</tr>
		</thead>

		<tbody class="tbody">
			<tr>
				<th><code class="code">a</code></th>
				<td><?php esc_html_e( 'Lowercase Ante meridiem and Post meridiem', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>am</code> or <code>pm</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">A</code></th>
				<td><?php esc_html_e( 'Uppercase Ante meridiem and Post meridiem', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>AM</code> or <code>PM</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">B</code></th>
				<td><?php esc_html_e( 'Swatch Internet time', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>000</code> through <code>999</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">g</code></th>
				<td><?php esc_html_e( '12-hour format of an hour without leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>1</code> through <code>12</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">G</code></th>
				<td><?php esc_html_e( '24-hour format of an hour without leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>0</code> through <code>23</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">h</code></th>
				<td><?php esc_html_e( '12-hour format of an hour with leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>01</code> through <code>12</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">H</code></th>
				<td><?php esc_html_e( '24-hour format of an hour with leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>00</code> through <code>23</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">i</code></th>
				<td><?php esc_html_e( 'Minutes with leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>00</code> to <code>59</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">s</code></th>
				<td><?php esc_html_e( 'Seconds with leading zeros', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>00</code> through <code>59</code>', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">u</code></th>
				<td><?php esc_html_e( 'Microseconds', 'sugar-calendar' ); ?></td>
				<td><code>654321</code></td>
			</tr>

			<tr>
				<th><code class="code">v</code></th>
				<td><?php esc_html_e( 'Milliseconds', 'sugar-calendar' ); ?></td>
				<td><code>654</code></td>
			</tr>
		</tbody>
	</table>

	<?php

	// Time table
	$time_table = ob_get_clean();

	// Time zone table
	ob_start(); ?>

	<table class="sc-date-time-format-table sc-custom-time-table">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Time Zone', 'sugar-calendar' ); ?></th>
			</tr>
		</thead>

		<tbody class="tbody">
			<tr>
				<th><code class="code">e</code></th>
				<td><?php esc_html_e( 'Time zone identifier', 'sugar-calendar' ); ?></td>
				<td><code>UTC</code>, <code>GMT</code>, <code>Atlantic/Azores</code></td>
			</tr>

			<tr>
				<th><code class="code">I</code></td>
				<td><?php esc_html_e( 'Whether or not the date is in daylight saving time', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>1</code> if Daylight Saving Time, <code>0</code> otherwise.', 'sugar-calendar' ); ?></td>
			</tr>

			<tr>
				<th><code class="code">O</code></th>
				<td><?php esc_html_e( 'Difference to Greenwich time (GMT) without colon between hours and minutes', 'sugar-calendar' ); ?></td>
				<td><code>+0200</code></td>
			</tr>

			<tr>
				<th><code class="code">P</code></th>
				<td><?php esc_html_e( 'Difference to Greenwich time (GMT) with colon between hours and minutes', 'sugar-calendar' ); ?></td>
				<td><code>+02:00</code></td>
			</tr>

			<tr>
				<th><code class="code">T</code></th>
				<td><?php esc_html_e( 'Time zone abbreviation', 'sugar-calendar' ); ?></td>
				<td><code>EST</code>, <code>MDT</code> ...</td>
			</tr>

			<tr>
				<th><code class="code">Z</code></th>
				<td><?php echo __( 'Time zone offset in seconds.<br>West of UTC is negative.<br>East of UTC is positive.', 'sugar-calendar' ); ?></td>
				<td><?php echo __( '<code>-43200</code> through <code>50400</code>', 'sugar-calendar' ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php

	// Time zone table
	$timezone_table = ob_get_clean();

	// Full table
	ob_start(); ?>

	<table class="sc-date-time-format-table sc-custom-time-table">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Full Date/Time', 'sugar-calendar' ); ?></th>
			</tr>
		</thead>

		<tbody class="tbody">
			<tr>
				<th><code class="code">c</code></th>
				<td><?php echo __( '<a href="https://www.iso.org/iso-8601-date-and-time-format.html">ISO-8601</a> formatted date', 'sugar-calendar' ); ?></td>
				<td><pre><code><?php esc_html_e( '2004-02-12T15:19:21+00:00', 'sugar-calendar' ); ?></code></pre></td>
			</tr>

			<tr>
				<th><code class="code">r</code></th>
				<td><?php echo __( '<a href="http://www.faqs.org/rfcs/rfc2822">RFC 2822</a> formatted date', 'sugar-calendar' ); ?></td>
				<td><pre><code><?php esc_html_e( 'Thu, 21 Dec 2000 16:01:07 +0200', 'sugar-calendar' ); ?></code></pre></th>
			</tr>

			<tr>
				<th><code class="code">U</code></th>
				<td><?php echo __( 'Seconds since the Unix Epoch<br>(January 1 1970 00:00:00 GMT)', 'sugar-calendar' ); ?></td>
				<td><code><?php echo time(); ?></code></td>
			</tr>
		</tbody>
	</table>

	<?php

	// Full table
	$full_table = ob_get_clean();

	// Date Formatting
	get_current_screen()->add_help_tab( array(
		'id'		=> 'date-formatting',
		'title'		=> esc_html__( 'Date Formatting', 'sugar-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'Date output can be customized to suit your needs.',        'sugar-calendar' ) . '</p>' .
			'<p>'  . esc_html__( 'Clicking on rows will append the code to the custom box.', 'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Popular formats are pre-populated.',                       'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Format values are single characters.',                     'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Just about any result can be achieved.',                   'sugar-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Ranges', 'sugar-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'Days can be represented as numbers or words.',             'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Week number is useful in month view calendars.',           'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Months can be represented as numbers or words.',           'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Years can be 2 or 4 digits, and more.',                    'sugar-calendar' ) . '</li></ul>'
	) );

	// Day & Week
	get_current_screen()->add_help_tab( array(
		'id'		=> 'day-week-format',
		'title'		=> __( '&mdash; Day & Week', 'sugar-calendar' ),
		'content'	=> $day_week_table
	) );

	// Month
	get_current_screen()->add_help_tab( array(
		'id'		=> 'month-format',
		'title'		=> __( '&mdash; Month', 'sugar-calendar' ),
		'content'	=> $month_table
	) );

	// Year
	get_current_screen()->add_help_tab( array(
		'id'		=> 'year-format',
		'title'		=> __( '&mdash; Year', 'sugar-calendar' ),
		'content'	=> $year_table
	) );

	// Time Formatting
	get_current_screen()->add_help_tab( array(
		'id'		=> 'time-formatting',
		'title'		=> esc_html__( 'Time Formatting', 'sugar-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'Time output can be customized to suit your needs.',        'sugar-calendar' ) . '</p>' .
			'<p>'  . esc_html__( 'Clicking on rows will append the code to the custom box.', 'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Popular formats are pre-populated.',                       'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Format values are single characters.',                     'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Just about any result can be achieved.',                   'sugar-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Ranges', 'sugar-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'Times can be represented with or without leading zeros.',  'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( '12 or 24 hour times, with or without meridiems.',          'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Minutes and seconds always have leading zeros.',           'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Micro and Milli seconds provide extra precision.',         'sugar-calendar' ) . '</li></ul>'
	) );


	// Time
	get_current_screen()->add_help_tab( array(
		'id'		=> 'time-format',
		'title'		=> __( '&mdash; Time', 'sugar-calendar' ),
		'content'	=> $time_table
	) );

	// Time zone
	get_current_screen()->add_help_tab( array(
		'id'		=> 'time-zone-format',
		'title'		=> __( '&mdash; Time zone', 'sugar-calendar' ),
		'content'	=> $timezone_table
	) );

	// Full
	get_current_screen()->add_help_tab( array(
		'id'		=> 'full-format',
		'title'		=> __( '&mdash; Full', 'sugar-calendar' ),
		'content'	=> $full_table
	) );

	// Time Zones
	get_current_screen()->add_help_tab( array(
		'id'		=> 'time-zones',
		'title'		=> esc_html__( 'Time Zones', 'sugar-calendar' ),
		'content'	=>
			'<p><strong>'  . esc_html__( 'Ranges', 'sugar-calendar' ) . '</strong></p>' .
			'<p>'  . esc_html__( 'Adds time zone support to Events and Calendars.',          'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( '"Off" is the default value. Leave off if you are unsure.', 'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( '"Single" means Event Start & End are the same.',           'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( '"Multi" means Event Start & End can be different.',        'sugar-calendar' ) . '</li></ul>' .
			'<p><strong>'  . esc_html__( 'Default Time Zone', 'sugar-calendar' ) . '</strong></p>' .
			'<p>'  . esc_html__( 'What to use when nothing else is specified.',              'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Categorized by region. Ordered alphabetically',            'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( '"UTC" should only be used if location is not available.',  'sugar-calendar' ) . '</li>' .
			'<li>' . esc_html__( '"Floating" means "no time zone" or relative to the user.', 'sugar-calendar' ) . '</li></ul>' .
			'<p><strong>'  . esc_html__( 'Visitor Conversion', 'sugar-calendar' ) . '</strong></p>' .
			'<p>'  . esc_html__( 'Enable this to make times relative to the site visitor.',  'sugar-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Relies on browser support. May not always be accurate.',   'sugar-calendar' ) . '</li></ul>'
	) );

	// Help Sidebar
	get_current_screen()->set_help_sidebar(
		'<p><i class="dashicons dashicons-calendar-alt"></i> ' . esc_html__( 'Regular Event', 'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-location"></i> '     . esc_html__( 'Has Location',  'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-clock"></i> '        . esc_html__( 'All Day',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-backup"></i> '       . esc_html__( 'Recurring',     'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-trash"></i> '        . esc_html__( 'Trashed',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-hidden"></i> '       . esc_html__( 'Private',       'sugar-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-lock"></i> '         . esc_html__( 'Protected',     'sugar-calendar' ) . '</p>'
	);
}
