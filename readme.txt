=== Sugar Calendar (Lite) ===
Author:            Pippin Williamson
Contributors:      mordauk
Author URI:        https://sandhillsdev.com
Plugin URI:        https://sugarcalendar.com
Tags:              event calendar, events, simple, events calendar, calendar, Pippin Williamson, Pippin's Plugins
Requires PHP:      5.6.20
Requires at least: 5.0
Tested up to:      5.2
Stable tag:        2.0.5

A sweet, simple event calendar plugin. Create your events and show them on a simple calendar. That's it.

== Description ==

Most event calendar plugins are either way too simple, or extremely overly complex and bloated. Sugar Calendar is designed to be simple, light weight, and provide just the major features you need for event management.

= Features =

* Simple Event Management
* Beautiful Admin Interface
* Ajax Enabled Calendar View
* Events Custom Post Type
* Event Calendar Custom Taxonomy
* Simple Event Configuration
* Easily Set Event Dates
* Easily Set Event Start and End Time
* Events Archive, Listed by Start Date
* Widgets for displaying calendars and event filters
* Shortcode for showing the calendar of events
* Large and small calendar views

= Extended Features =

The full version of this plugin is [available for purchase](https://sugarcalendar.com), and adds a simple recurring events feature.

= Languages =

Sugar Calendar Lite has been translated into:

* English
* German
* French
* Serbian
* Swedish

[Follow this plugin on Github](https://github.com/sugarcalendar/Sugar-Event-Calendar-Lite).

== Installation ==

1. Upload the 'sugar-calendar-lite' folder to the '/wp-content/plugins/'' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the new Events post type and create some events
4. Display the calendar on any page with [sc_events_calendar]
5. View the archive of events at _yoursite.com/events_

== Screenshots ==

1. Event calendar
2. Single event display
3. Event list in admin
4. Event configuration

== Frequently Asked Questions ==

= Can I have recurring events? =

The [non-lite version](https://sugarcalendar.com/) has recurring options for daily, weekly, monthly, and yearly events.

= Can I display Google Maps with my Events? =

Yes, there is a [free add-on plugin](http://wordpress.org/extend/plugins/simple-google-maps-short-code/) for showing maps. This add-on works with both lite and full versions of Sugar Calendar.

= Can I have event registration forms? =

I have built a [free add-on](http://wordpress.org/extend/plugins/sugar-event-calendar-gravity-forms/) that provides integration with the very popular [Gravity Forms plugin](http://www.gravityforms.com/).

== Changelog ==

= 2.0.5 =

June 7, 2019

* Fixed: Events with both dates & times not saving correctly

= 2.0.4 =

June 2, 2019

* Fixed: Admin area List, sorting by Repeat
* Fixed: Calendar shortcode & widget rendering

= 2.0.3 =

May 31, 2019

* Fixed: Possible conflicts with other custom taxonomies
* Fixed: Admin area event color styling
* Added: Start month support in Calendar Widget
* Improved: Handling of events with no end date or time

= 2.0.2 =

May 10, 2019

* Fixed: Admin notices on certain screens
* Fixed: Widget filters not working on archive pages
* Fixed: Database query actions not working as expected
* Fixed: Trashed events being displayed in widgets
* Fixed: Event archive showing details multiple times
* Added: Context to some strings to improved internationalization

= 2.0.1 =

April 10, 2019

* Fixed: Multi-day events not showing on the calendar
* Fixed: Fatal PHP error when events are set to recur daily
* Fixed: Weekly recurring events shown incorrectly in month view

= 2.0.0 =

March 21, 2019

* New: admin area month/week/day/list modes
* New: admin area Event editing experience
* New: admin area Calendar Colors
* New: admin area Settings screen
* New: admin area preferences (saved per-user)
* New: daily recurring events
* Fixed: start of week feature works on all days
* Improved: performance all-around, thanks to custom database storage
* Improved: date/time formatting for calendar-specific markup
* Improved: license key contents now hidden for added security

= 1.6.8 =

* Added: categories taxonomy (from Standard)
* Added: widgets (from Standard)
* Added: end-time (from Standard)
* Added: settings page (from Standard)
* Added: feed support (from Standard)
* Added: license key & beta opt-in (from Standard)
* Minor: tweaks to plugin naming & verbiage
* Updated: sc_before_event_content action  and sc_after_event_content action to run on archive pages as well as singular event pages.

= 1.0.5 =

* Fixed: an incorrect variable name
* Added: Spanish translation files

= 1.0.4 =

* Replaced: the dashboard menu with a dashicon, props Devin Walker

= 1.0.3 =

* Fixed: a bug with Week Start Day being set to Monday

= 1.0.2 =

* Fixed: a bug with the Year/Month drop down
* Fixed: an undefined index error

= 1.0.1 =

* Fixed: a display issue with some months
* Fixed: a JavaScript error
* Fixed: an issue with child elements when loading the calendar via ajax

= 1.0 =

First release of lite version