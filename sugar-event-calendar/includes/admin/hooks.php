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

// Admin upgrades
add_action( 'admin_notices', __NAMESPACE__ . '\\Upgrades\\notices' );

// Admin Settings
add_action( 'admin_menu', __NAMESPACE__ . '\\Settings\\menu'               );
add_action( 'admin_init', __NAMESPACE__ . '\\Settings\\register_settings'  );

// Admin meta box
add_action( 'add_meta_boxes',          __NAMESPACE__ . '\\Editor\\Meta\\add'          );
add_action( 'save_post',               __NAMESPACE__ . '\\Editor\\Meta\\save',  10, 2 );
add_filter( 'register_taxonomy_args',  __NAMESPACE__ . '\\Editor\\Meta\\taxonomy_args',  10, 2 );
add_filter( 'wp_terms_checklist_args', __NAMESPACE__ . '\\Editor\\Meta\\checklist_args', 10, 1 );

// Admin Meta-box Save
add_filter( 'sugar_calendar_event_to_save', __NAMESPACE__ . '\\Editor\\Meta\\add_location_to_save' );
add_filter( 'sugar_calendar_event_to_save', __NAMESPACE__ . '\\Editor\\Meta\\add_color_to_save'    );

// Admin New/Edit
add_action( 'edit_form_after_title',  __NAMESPACE__ . '\\Editor\\above' );
add_action( 'edit_form_after_editor', __NAMESPACE__ . '\\Editor\\below' );

// Admin title-box text
add_filter( 'enter_title_here', __NAMESPACE__ . '\\Posts\\title', 10, 2 );

// Admin Messages
add_filter( 'post_updated_messages', __NAMESPACE__ . '\\Posts\\updated_messages' );

// Admin title
add_filter( 'admin_title', 'sugar_calendar_admin_title' );

// Admin Menu
add_action( 'admin_head', __NAMESPACE__ . '\\Menu\\fix_menu_highlight', 9999 );

// Admin body class
add_filter( 'admin_body_class', __NAMESPACE__ . '\\Menu\\body_class' );

// Admin Scripts
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\Assets\\enqueue' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\Assets\\enqueue' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\Assets\\localize' );

// Admin Help
add_action( 'admin_head-edit.php',      __NAMESPACE__ . '\\Help\\add_tabs' );
add_action( 'admin_head-post-new.php',  __NAMESPACE__ . '\\Help\\add_tabs' );
add_action( 'admin_head-term.php',      __NAMESPACE__ . '\\Help\\add_tabs' );
add_action( 'admin_head-edit-tags.php', __NAMESPACE__ . '\\Help\\add_tabs' );

// Admin Event taxonomy tab override
add_action( 'admin_notices', __NAMESPACE__ . '\\Nav\\taxonomy_tabs', 10, 1 );

// Admin remove quick/bulk edit admin screen
add_action( 'admin_print_footer_scripts', __NAMESPACE__ . '\\Posts\\hide_quick_bulk_edit' );

// Admin Screen Options
add_action( 'sugar_calendar_screen_options', __NAMESPACE__ . '\\Screen\\Options\\preferences' );

// Admin Post Type Redirect
add_action( 'load-edit.php', __NAMESPACE__ . '\\Posts\\redirect_old_post_type' );

// Admin Empty Trash
add_action( 'load-toplevel_page_sugar-calendar', __NAMESPACE__ . '\\Menu\\maybe_empty_trash' );
