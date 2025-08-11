<?php
/**
 * Plugin Name: Snefuruplin
 * Plugin URI: https://github.com/transatlanticvoyage/snefuru4
 * Description: WordPress plugin for handling image uploads to Snefuru system
 * Version: 4.2.0
 * Author: Snefuru Team
 * License: GPL v2 or later
 * Text Domain: snefuruplin
 * 
 * Test comment: Git subtree setup completed successfully!
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SNEFURU_PLUGIN_VERSION', '4.2.0');
define('SNEFURU_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SNEFURU_PLUGIN_URL', plugin_dir_url(__FILE__));

// Supabase configuration is now managed through WordPress admin settings
// Go to Settings → Ketch Width Manager to configure your Supabase credentials

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
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-ketch-settings.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-ketch-api.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-debug-log-viewer.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-dublish-api.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-ruplin-wppma-database.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-zen-shortcodes.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-elementor-dynamic-tags.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-elementor-media-workaround.php';
        require_once SNEFURU_PLUGIN_PATH . 'hurricane/class-hurricane.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-orbit-mar-admin.php';
    }
    
    private function init_hooks() {
        // Check and update database schema if needed
        Ruplin_WP_Database_Horse_Class::check_database_version();
        
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
        new Snefuru_Dublish_API();
        new Zen_Shortcodes();
        
        // Initialize Elementor integrations
        if (did_action('elementor/loaded')) {
            new Zen_Elementor_Dynamic_Tags();
        }
        
        // Initialize media library workaround (always load for potential Elementor use)
        new Zen_Elementor_Media_Workaround();
        
        // Initialize Hurricane feature
        new Snefuru_Hurricane();
        
        // Add cron jobs for periodic data sync
        add_action('wp', array($this, 'schedule_events'));
        add_action('snefuru_sync_data', array($this, 'sync_data_to_cloud'));
    }
    
    public function activate() {
        // Clean up any mistakenly created zen_driggs table
        $this->cleanup_unwanted_tables();
        
        // Create database tables if needed
        $this->create_tables();
        
        // Load the database class before trying to use it
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-ruplin-wppma-database.php';
        
        // Create zen tables
        Ruplin_WP_Database_Horse_Class::create_tables();
        
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
        
        // Create zen_sitespren table (mirrors Supabase sitespren structure)
        $zen_sitespren_table = $wpdb->prefix . 'zen_sitespren';
        $zen_sitespren_sql = "CREATE TABLE $zen_sitespren_table (
            id varchar(36) NOT NULL,
            wppma_db_only_created_at datetime DEFAULT CURRENT_TIMESTAMP,
            wppma_db_only_updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at datetime DEFAULT NULL,
            sitespren_base text,
            true_root_domain text,
            full_subdomain text,
            webproperty_type text,
            fk_users_id varchar(36),
            updated_at datetime DEFAULT NULL,
            wpuser1 varchar(255),
            wppass1 varchar(255),
            wp_plugin_installed1 tinyint(1) DEFAULT 0,
            wp_plugin_connected2 tinyint(1) DEFAULT 0,
            fk_domreg_hostaccount varchar(36),
            is_wp_site tinyint(1) DEFAULT 0,
            wp_rest_app_pass text,
            driggs_industry text,
            driggs_city text,
            driggs_brand_name text,
            driggs_site_type_purpose text,
            driggs_email_1 text,
            driggs_address_full text,
            driggs_phone_1 text,
            driggs_special_note_for_ai_tool text,
            ns_full text,
            ip_address text,
            is_starred1 text,
            icon_name varchar(50),
            icon_color varchar(7),
            is_bulldozer tinyint(1) DEFAULT 0,
            driggs_phone1_platform_id int(11),
            driggs_cgig_id int(11),
            driggs_revenue_goal int(11),
            driggs_address_species_id int(11),
            is_competitor tinyint(1) DEFAULT 0,
            is_external tinyint(1) DEFAULT 0,
            is_internal tinyint(1) DEFAULT 0,
            is_ppx tinyint(1) DEFAULT 0,
            is_ms tinyint(1) DEFAULT 0,
            is_wayback_rebuild tinyint(1) DEFAULT 0,
            is_naked_wp_build tinyint(1) DEFAULT 0,
            is_rnr tinyint(1) DEFAULT 0,
            is_aff tinyint(1) DEFAULT 0,
            is_other1 tinyint(1) DEFAULT 0,
            is_other2 tinyint(1) DEFAULT 0,
            driggs_citations_done tinyint(1) DEFAULT 0,
            is_flylocal tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY fk_users_id (fk_users_id),
            KEY fk_domreg_hostaccount (fk_domreg_hostaccount),
            KEY sitespren_base (sitespren_base(50)),
            KEY true_root_domain (true_root_domain(50))
        ) $charset_collate;";
        
        dbDelta($zen_sitespren_sql);
        
        // Insert default record if styling table is empty
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
    
    /**
     * Clean up any unwanted tables that should not exist
     */
    private function cleanup_unwanted_tables() {
        global $wpdb;
        
        // Remove zen_driggs table if it exists (this table should never have existed)
        $zen_driggs_table = $wpdb->prefix . 'zen_driggs';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$zen_driggs_table'") == $zen_driggs_table;
        
        if ($table_exists) {
            $wpdb->query("DROP TABLE IF EXISTS $zen_driggs_table");
            error_log('Snefuru: Removed unwanted zen_driggs table during activation');
        }
    }
}

// Initialize the plugin
new SnefuruPlugin(); 