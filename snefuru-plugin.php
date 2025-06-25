<?php
/**
 * Plugin Name: Snefuru Image Upload Plugin
 * Plugin URI: https://github.com/transatlanticvoyage/snefuru4
 * Description: WordPress plugin for handling image uploads to Snefuru system
 * Version: 1.0.0
 * Author: Snefuru Team
 * License: GPL v2 or later
 * Text Domain: snefuru-plugin
 * 
 * Test comment: Git subtree setup completed successfully!
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SNEFURU_PLUGIN_VERSION', '1.0.0');
define('SNEFURU_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SNEFURU_PLUGIN_URL', plugin_dir_url(__FILE__));

// Main plugin class
class SnefuruPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load plugin components
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-api-client.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-data-collector.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-admin.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-settings.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-upload-handler.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-media-tab.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-css-endpoint.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-barkro-updater.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-elementor-updater.php';
    }
    
    private function init_hooks() {
        // Initialize components
        new Snefuru_API_Client();
        new Snefuru_Data_Collector();
        new Snefuru_Admin();
        new Snefuru_Settings();
        new Snefuru_Upload_Handler();
        new Snefuru_Media_Tab();
        new Snefuru_CSS_Endpoint();
        new Snefuru_Barkro_Updater();
        new Snefuru_Elementor_Updater();
        
        // Add cron jobs for periodic data sync
        add_action('wp', array($this, 'schedule_events'));
        add_action('snefuru_sync_data', array($this, 'sync_data_to_cloud'));
    }
    
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Schedule recurring events
        if (!wp_next_scheduled('snefuru_sync_data')) {
            wp_schedule_event(time(), 'hourly', 'snefuru_sync_data');
        }
        
        // Set default upload settings
        if (get_option('snefuru_upload_enabled') === false) {
            update_option('snefuru_upload_enabled', 1);
        }
        if (get_option('snefuru_upload_max_size') === false) {
            update_option('snefuru_upload_max_size', '10MB');
        }
    }
    
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('snefuru_sync_data');
    }
    
    public function schedule_events() {
        if (!wp_next_scheduled('snefuru_sync_data')) {
            wp_schedule_event(time(), 'hourly', 'snefuru_sync_data');
        }
    }
    
    public function sync_data_to_cloud() {
        $data_collector = new Snefuru_Data_Collector();
        $api_client = new Snefuru_API_Client();
        
        $site_data = $data_collector->collect_site_data();
        $api_client->send_data_to_cloud($site_data);
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create snefuru_logs table
        $table_name = $wpdb->prefix . 'snefuru_logs';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            action varchar(255) NOT NULL,
            data longtext,
            status varchar(50) DEFAULT 'pending',
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create snefuruplin_styling table
        $styling_table = $wpdb->prefix . 'snefuruplin_styling';
        $styling_sql = "CREATE TABLE $styling_table (
            styling_id mediumint(9) NOT NULL AUTO_INCREMENT,
            styling_content longtext,
            styling_end_url varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (styling_id)
        ) $charset_collate;";
        
        dbDelta($styling_sql);
        
        // Insert default record if table is empty
        $existing_records = $wpdb->get_var("SELECT COUNT(*) FROM $styling_table");
        if ($existing_records == 0) {
            $site_url = get_site_url();
            $default_url = $site_url . '/wp-json/snefuru/v1/css/bespoke';
            $wpdb->insert(
                $styling_table,
                array(
                    'styling_content' => "/* Bespoke CSS Editor - Add your custom styles here */\n\nbody {\n    /* Your custom styles */\n}",
                    'styling_end_url' => $default_url
                )
            );
        }
    }
}

// Initialize the plugin
new SnefuruPlugin(); 