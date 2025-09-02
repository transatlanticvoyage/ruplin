<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Snefuruplin
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load the Database Horse class
require_once plugin_dir_path(__FILE__) . 'includes/class-ruplin-wppma-database.php';

// Preserve database tables and their data when plugin is uninstalled
// Tables remain accessible for future use or backup plugins
// Ruplin_WP_Database_Horse_Class::drop_tables();

// Clean up options
delete_option('snefuru_zen_db_version');
delete_option('snefuru_plugin_db_version');
delete_option('snefuru_upload_enabled');
delete_option('snefuru_upload_max_size');
delete_option('snefuru_ruplin_api_key_1');
delete_option('snefuru_api_url');
delete_option('snefuru_sync_interval');
delete_option('snefuru_auto_sync');

// Clear any scheduled hooks
wp_clear_scheduled_hook('snefuru_sync_data');