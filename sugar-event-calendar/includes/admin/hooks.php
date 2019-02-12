<?php
/**
 * Sugar Calendar Admin Hooks
 *
 * @package Plugins/Site/Events/Admin/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Admin page
add_action( 'admin_menu', 'sugar_calendar_admin_register_page' );

// Admin assets
add_action( 'admin_init', 'sugar_calendar_admin_register_assets' );

// Admin screen options
add_action( 'admin_init', 'sugar_calendar_save_screen_options' );

// Admin upgrades
add_action( 'admin_notices', 'sugar_calendar_show_upgrade_notices' );

// Admin Settings
add_action( 'admin_menu',    'Sugar_Calendar\\Admin\\Settings\\menu'                );
add_action( 'admin_init',    'Sugar_Calendar\\Admin\\Settings\\activate_license'    );
add_action( 'admin_init',    'Sugar_Calendar\\Admin\\Settings\\deactivate_license'  );
add_action( 'admin_init',    'Sugar_Calendar\\Admin\\Settings\\check_license'       );
add_action( 'admin_init',    'Sugar_Calendar\\Admin\\Settings\\register_options'    );
add_action( 'admin_notices', 'Sugar_Calendar\\Admin\\Settings\\show_license_notice' );

// Admin meta box
add_action( 'add_meta_boxes',          'sugar_calendar_meta_box_add'          );
add_action( 'save_post',               'sugar_calendar_meta_box_save',  10, 2 );
add_filter( 'register_taxonomy_args',  'sugar_calendar_taxonomy_args',  10, 2 );
add_filter( 'wp_terms_checklist_args', 'sugar_calendar_checklist_args', 10, 1 );

// Admin title-box text
add_filter( 'enter_title_here', 'sugar_calendar_enter_title_here', 10, 2 );

// Admin Messages
add_filter( 'post_updated_messages', 'sugar_calendar_post_updated_messages' );

// Admin Menu
add_action( 'admin_head', 'sugar_calendar_admin_fix_menu_highlight', 9999 );

// Admin title
add_filter( 'admin_title', 'sugar_calendar_admin_title' );

// Admin body class
add_filter( 'admin_body_class', 'sugar_calendar_admin_body_class' );

// Admin Scripts
add_action( 'admin_enqueue_scripts', 'sugar_calendar_admin_event_assets' );
add_action( 'admin_enqueue_scripts', 'sugar_calendar_admin_event_assets' );
add_action( 'admin_enqueue_scripts', 'sugar_calendar_admin_localize_scripts' );

// Admin Help
add_action( 'admin_head-edit.php',      'sugar_calendar_admin_add_help_tabs' );
add_action( 'admin_head-post-new.php',  'sugar_calendar_admin_add_help_tabs' );
add_action( 'admin_head-term.php',      'sugar_calendar_admin_add_help_tabs' );
add_action( 'admin_head-edit-tags.php', 'sugar_calendar_admin_add_help_tabs' );

// Admin Event taxonomy tab override
add_action( 'admin_notices', 'sugar_calendar_admin_taxonomy_tabs', 10, 1 );

// Admin remove quick/bulk edit admin screen
add_action( 'admin_print_footer_scripts', 'sugar_calendar_hide_quick_bulk_edit' );

// Admin Screen Options
add_action( 'sugar_calendar_screen_options', 'sugar_calendar_screen_option_settings' );

// Admin Post Type Redirect
add_action( 'load-edit.php', 'sugar_calendar_admin_redirect_old_post_type' );

// Admin Meta-box Save
add_filter( 'sugar_calendar_event_to_save', 'sugar_calendar_add_location_to_save' );
add_filter( 'sugar_calendar_event_to_save', 'sugar_calendar_add_color_to_save'    );
