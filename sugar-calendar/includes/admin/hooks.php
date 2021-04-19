<?php
/**
 * Sugar Calendar Admin Hooks
 *
 * @package Plugins/Site/Events/Admin/Hooks
 */
namespace Sugar_Calendar\Admin;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Admin page
add_action( 'admin_menu', __NAMESPACE__ . '\\Menu\\register_page' );

// Admin assets
add_action( 'admin_init', __NAMESPACE__ . '\\Assets\\register' );

// Admin screen options
add_action( 'admin_init', __NAMESPACE__ . '\\Screen\\Options\\save' );
add_action( 'admin_init', __NAMESPACE__ . '\\Screen\\Options\\reset' );

// Admin upgrades
add_action( 'admin_notices', __NAMESPACE__ . '\\Upgrades\\notices' );

// Admin Settings
add_action( 'admin_menu', __NAMESPACE__ . '\\Settings\\menu' );

// Admin meta box
add_action( 'add_meta_boxes',          __NAMESPACE__ . '\\Editor\\Meta\\add'          );
add_action( 'save_post',               __NAMESPACE__ . '\\Editor\\Meta\\save',  10, 2 );
add_filter( 'register_taxonomy_args',  __NAMESPACE__ . '\\Editor\\Meta\\taxonomy_args',  10, 2 );
add_filter( 'wp_terms_checklist_args', __NAMESPACE__ . '\\Editor\\Meta\\checklist_args', 10, 1 );
add_filter( 'sc_event_supports',       __NAMESPACE__ . '\\Editor\\Meta\\custom_fields' );

// Admin meta box Save
add_filter( 'sugar_calendar_event_to_save', __NAMESPACE__ . '\\Editor\\Meta\\add_location_to_save' );
add_filter( 'sugar_calendar_event_to_save', __NAMESPACE__ . '\\Editor\\Meta\\add_color_to_save'    );

// Admin meta box filter
add_filter( 'get_user_option_meta-box-order_sc_event', __NAMESPACE__ . '\\Editor\\Meta\\noop_user_option' );

// Admin New/Edit
add_action( 'edit_form_after_title',  __NAMESPACE__ . '\\Editor\\above' );
add_action( 'edit_form_after_editor', __NAMESPACE__ . '\\Editor\\below' );

// Admin title-box text
add_filter( 'enter_title_here', __NAMESPACE__ . '\\Posts\\title', 10, 2 );

// Admin Messages
add_filter( 'post_updated_messages', __NAMESPACE__ . '\\Posts\\updated_messages' );

// Admin title
add_filter( 'admin_title', 'sugar_calendar_admin_title', 10, 2 );

// Admin Menu
add_action( 'admin_head', __NAMESPACE__ . '\\Menu\\fix_menu_highlight', 9999 );

// Admin body class
add_filter( 'admin_body_class', __NAMESPACE__ . '\\Menu\\body_class' );

// Admin Scripts
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\Assets\\enqueue' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\Assets\\localize' );

// Admin Help (Calendar)
add_action( 'admin_head-edit.php',      __NAMESPACE__ . '\\Help\\add_calendar_tabs' );
add_action( 'admin_head-post-new.php',  __NAMESPACE__ . '\\Help\\add_calendar_tabs' );
add_action( 'admin_head-term.php',      __NAMESPACE__ . '\\Help\\add_calendar_tabs' );
add_action( 'admin_head-edit-tags.php', __NAMESPACE__ . '\\Help\\add_calendar_tabs' );

// Admin Help (Settings)
add_action( 'admin_head-calendar_page_sc-settings', __NAMESPACE__ . '\\Help\\add_settings_tabs' );

// Admin Event taxonomy tab override
add_action( 'admin_notices', __NAMESPACE__ . '\\Nav\\taxonomy_tabs', 10, 1 );
add_action( 'sugar_calendar_admin_nav_after_items', __NAMESPACE__ . '\\Nav\\add_new' );

// Admin remove quick/bulk edit admin screen
add_action( 'admin_print_footer_scripts', __NAMESPACE__ . '\\Posts\\hide_quick_bulk_edit' );

// Admin Screen Options
add_action( 'sugar_calendar_screen_options', __NAMESPACE__ . '\\Screen\\Options\\preferences' );

// Admin Post Type Redirect
add_action( 'load-edit.php', __NAMESPACE__ . '\\Posts\\redirect_old_post_type' );

// Admin AJAX to format custom Date & Time values
add_action( 'wp_ajax_sc_date_format', __NAMESPACE__ . '\\Settings\\ajax_date_format' );
add_action( 'wp_ajax_sc_time_format', __NAMESPACE__ . '\\Settings\\ajax_time_format' );

// Get the page ID
$sc_admin_page = sugar_calendar_get_admin_page_id();

// Page ID specific actions
add_action( "load-{$sc_admin_page}", __NAMESPACE__ . '\\Menu\\maybe_empty_trash' );
add_action( "load-{$sc_admin_page}", __NAMESPACE__ . '\\Menu\\preload_list_table' );

// Global cleanup
unset( $sc_admin_page );
