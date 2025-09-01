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

// Load required classes
require_once plugin_dir_path(__FILE__) . 'includes/class-ruplin-wppma-database.php';
require_once plugin_dir_path(__FILE__) . 'includes/petrifact/class-petrifact-registry.php';
require_once plugin_dir_path(__FILE__) . 'includes/petrifact/class-petrifact-generator.php';

// Handle Petrifact persistence settings
$preserve_data = get_option('snefuru_preserve_data_on_uninstall', 1);
$petrifact_enabled = get_option('snefuru_petrifact_enabled', 1);

if (!$preserve_data) {
    // Complete removal - drop tables and remove MU-plugin
    Ruplin_WP_Database_Horse_Class::drop_tables();
    
    if (class_exists('Petrifact_Generator')) {
        Petrifact_Generator::remove_mu_plugin();
    }
    
    // Remove Petrifact options
    delete_option('petrifact_runtime_version');
    delete_option('petrifact_last_install');
} else {
    // Preserve data - keep tables and MU-plugin if enabled
    // Tables will remain: zen_services, zen_locations, zen_sitespren, zen_orbitposts
    // MU-plugin will remain functional if petrifact_enabled is true
}

// Clean up main plugin options (always remove these)
delete_option('snefuru_zen_db_version');
delete_option('snefuru_plugin_db_version');
delete_option('snefuru_upload_enabled');
delete_option('snefuru_upload_max_size');
delete_option('snefuru_ruplin_api_key_1');
delete_option('snefuru_api_url');
delete_option('snefuru_sync_interval');
delete_option('snefuru_auto_sync');

// Clean up Petrifact options if not preserving data
if (!$preserve_data) {
    delete_option('snefuru_petrifact_enabled');
    delete_option('snefuru_preserve_data_on_uninstall');
    delete_option('snefuru_petrifact_auto_update');
}

// Clear any scheduled hooks
wp_clear_scheduled_hook('snefuru_sync_data');