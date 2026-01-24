<?php
/**
 * Plugin Name: Ruplin (Elementor-Safe v4.5)
 * Plugin URI: https://github.com/transatlanticvoyage/ruplin
 * Description: WordPress plugin for handling image uploads to Snefuru system - Now works with or without Elementor
 * Version: 4.5.0
 * Author: Snefuru Team
 * License: GPL v2 or later
 * Text Domain: ruplin
 * 
 * Test comment: Git subtree setup completed successfully!
 * Test comment 2: Testing git sync at 2025-09-19
 * Test comment 7: Final VSCode dual visibility test - 2025-09-19 16:48
 * Git test trigger comment: 2025-10-26
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Self-correction: Ensure plugin is in correct directory
add_action('admin_init', function() {
    $current_dir = basename(dirname(__FILE__));
    $expected_dir = 'ruplin';
    
    // Check if we're in the wrong directory
    if ($current_dir !== $expected_dir && !defined('RUPLIN_RELOCATING')) {
        $plugins_dir = WP_PLUGIN_DIR;
        $current_path = $plugins_dir . '/' . $current_dir;
        $correct_path = $plugins_dir . '/' . $expected_dir;
        
        // Only attempt relocation if target doesn't exist
        if (!file_exists($correct_path) && file_exists($current_path)) {
            // Define flag to prevent infinite loops
            define('RUPLIN_RELOCATING', true);
            
            // Deactivate from wrong location
            deactivate_plugins(plugin_basename(__FILE__));
            
            // Try to rename directory
            if (@rename($current_path, $correct_path)) {
                // Reactivate from correct location
                activate_plugin($expected_dir . '/ruplin.php');
                
                // Redirect to plugins page with success message
                wp_redirect(admin_url('plugins.php?ruplin_relocated=1'));
                exit;
            }
        }
    }
});

// Show admin notice after relocation
add_action('admin_notices', function() {
    if (isset($_GET['ruplin_relocated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Ruplin plugin has been relocated to the correct directory.</p></div>';
    }
});

// Define plugin constants
define('SNEFURU_PLUGIN_VERSION', '4.5.0');
define('SNEFURU_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SNEFURU_PLUGIN_URL', plugin_dir_url(__FILE__));

// Supabase configuration is now managed through WordPress admin settings
// Go to Settings → Ketch Width Manager to configure your Supabase credentials

// Main plugin class
class SnefuruPlugin {
    
    private $elementor_available = false;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Check if Elementor is available and active
     */
    private function is_elementor_available() {
        return class_exists('Elementor\\Plugin') && is_plugin_active('elementor/elementor.php');
    }
    
    public function init() {
        // Check Elementor availability
        $this->elementor_available = $this->is_elementor_available();
        
        // Define constant for global access
        if (!defined('RUPLIN_ELEMENTOR_AVAILABLE')) {
            define('RUPLIN_ELEMENTOR_AVAILABLE', $this->elementor_available);
        }
        
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
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-ketch-settings.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-ketch-api.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-debug-log-viewer.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-dublish-api.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-zen-vacuum-api.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-ruplin-wppma-database.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-zen-shortcodes.php';
        
        // Load Silkweaver Menu System
        require_once SNEFURU_PLUGIN_PATH . 'silkweaver_menu/silkweaver_init.php';
        
        // Load Scorpion Search & Replace System
        require_once SNEFURU_PLUGIN_PATH . 'scorpion_search_replace/class-scorpion-search-replace.php';
        
        // Load Date Worshipper System
        require_once SNEFURU_PLUGIN_PATH . 'date_worshipper/class-date-worshipper.php';
        
        // Load Aragon Image Manager System
        require_once SNEFURU_PLUGIN_PATH . 'aragon_image_manager/class-aragon-image-manager.php';
        
        // Load Contact Form Services Shortcode System
        require_once SNEFURU_PLUGIN_PATH . 'contact_form_services_shortcode/class-contact-form-services-shortcode.php';
        
        // Load Weasel Mar (Contact Form) Management Page
        require_once SNEFURU_PLUGIN_PATH . 'includes/pages/weasel-mar-page.php';
        
        // Load Schema System
        require_once SNEFURU_PLUGIN_PATH . 'schema_system/class-schema-generator.php';
        
        // Load Favicon Manager
        require_once SNEFURU_PLUGIN_PATH . 'favicon_manager/class-favicon-manager.php';
        
        // Load Elementor components only if Elementor is available
        if ($this->elementor_available) {
            require_once SNEFURU_PLUGIN_PATH . 'includes/class-elementor-updater.php';
            require_once SNEFURU_PLUGIN_PATH . 'includes/class-elementor-dynamic-tags.php';
            require_once SNEFURU_PLUGIN_PATH . 'includes/class-elementor-media-workaround.php';
        }
        require_once SNEFURU_PLUGIN_PATH . 'hurricane/class-hurricane.php';
        require_once SNEFURU_PLUGIN_PATH . 'includes/class-orbit-mar-admin.php';
        
        // Load VectorNode SEO system
        require_once SNEFURU_PLUGIN_PATH . 'vectornode_seo_meta/class-vectornode-core.php';
        
        // DEBUG: Disabled - VectorNode debug output
        // if (file_exists(WP_CONTENT_DIR . '/vectornode-debug.php')) {
        //     require_once WP_CONTENT_DIR . '/vectornode-debug.php';
        // }
    }
    
    private function init_hooks() {
        // Check and update database schema if needed
        Ruplin_WP_Database_Horse_Class::check_database_version();
        $this->check_plugin_database_version();
        
        // Initialize components
        new Snefuru_API_Client();
        new Snefuru_Data_Collector();
        new Snefuru_Admin();
        new Snefuru_Settings();
        new Snefuru_Upload_Handler();
        new Snefuru_Media_Tab();
        new Snefuru_CSS_Endpoint();
        new Snefuru_Barkro_Updater();
        new Snefuru_Dublish_API();
        new Snefuru_Zen_Vacuum_API();
        new Zen_Shortcodes();
        
        // Initialize database post sync hooks
        new Ruplin_WP_Database_Horse_Class();
        
        // Initialize Elementor integrations only if available
        if ($this->elementor_available) {
            new Snefuru_Elementor_Updater();
            
            // Initialize Elementor Dynamic Tags if Elementor is loaded
            if (did_action('elementor/loaded')) {
                new Zen_Elementor_Dynamic_Tags();
            }
            
            // Initialize media library workaround for Elementor
            new Zen_Elementor_Media_Workaround();
        }
        
        // Initialize Hurricane feature
        new Snefuru_Hurricane();
        
        // Initialize Ferret Snippets feature
        require_once plugin_dir_path(__FILE__) . 'includes/ferret-snippets/class-ferret-snippets.php';
        Ferret_Snippets::get_instance();
        
        // Initialize Weasel Code Injection
        $this->init_weasel_code_injection();
        
        // Initialize VectorNode SEO system
        \Ruplin\VectorNode\VectorNode_Core::get_instance();
        
        // Register fallback shortcodes for non-Elementor mode
        $this->register_fallback_shortcodes();
        
        // Add cron jobs for periodic data sync
        add_action('wp', array($this, 'schedule_events'));
        add_action('snefuru_sync_data', array($this, 'sync_data_to_cloud'));
    }
    
    /**
     * Register fallback shortcodes for non-Elementor mode
     */
    private function register_fallback_shortcodes() {
        if (!$this->elementor_available) {
            // Register fallback shortcodes that would normally be handled by Elementor
            add_shortcode('ruplin_dynamic', array($this, 'ruplin_dynamic_shortcode'));
            add_shortcode('ruplin_content', array($this, 'ruplin_content_shortcode'));
        }
    }
    
    /**
     * Fallback shortcode for dynamic content
     */
    public function ruplin_dynamic_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => '',
            'key' => '',
            'default' => ''
        ), $atts);
        
        // Get dynamic content based on type
        switch ($atts['type']) {
            case 'meta':
                $value = get_post_meta(get_the_ID(), $atts['key'], true);
                break;
            case 'option':
                $value = get_option($atts['key'], $atts['default']);
                break;
            default:
                $value = $atts['default'];
        }
        
        return !empty($value) ? $value : $atts['default'];
    }
    
    /**
     * Fallback shortcode for content blocks
     */
    public function ruplin_content_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'field' => '',
            'default' => ''
        ), $atts);
        
        if (empty($atts['id'])) {
            return $atts['default'];
        }
        
        // Get content from database
        global $wpdb;
        $table = $wpdb->prefix . 'ruplin_content_blocks';
        $content = $wpdb->get_var($wpdb->prepare(
            "SELECT content FROM $table WHERE block_id = %s AND field_name = %s",
            $atts['id'],
            $atts['field']
        ));
        
        return !empty($content) ? do_shortcode($content) : $atts['default'];
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
        
        // Insert default sitespren data
        $this->maybe_insert_default_sitespren_data();
        
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
            wppma_id INT UNSIGNED AUTO_INCREMENT UNIQUE,
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
            driggs_keywords text,
            driggs_category text,
            driggs_city text,
            driggs_brand_name text,
            driggs_site_type_purpose text,
            driggs_email_1 text,
            driggs_phone_1 text,
            driggs_address_full text,
            driggs_street_1 text,
            driggs_street_2 text,
            driggs_state_code text,
            driggs_zip text,
            driggs_state_full text,
            driggs_country text,
            driggs_payment_methods text,
            driggs_social_media_links text,
            driggs_hours text,
            driggs_owner_name text,
            driggs_short_descr text,
            driggs_long_descr text,
            driggs_footer_blurb text,
            driggs_year_opened int(11),
            driggs_employees_qty int(11),
            driggs_special_note_for_ai_tool text,
            driggs_logo_url text,
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
            driggs_address_species_note text,
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
            driggs_social_profiles_done tinyint(1) DEFAULT 0,
            is_flylocal tinyint(1) DEFAULT 0,
            snailimage varchar(255) DEFAULT NULL,
            snail_image_url text DEFAULT NULL,
            snail_image_status varchar(50) DEFAULT NULL,
            snail_image_error text DEFAULT NULL,
            contact_form_1_endpoint TEXT DEFAULT NULL,
            contact_form_1_main_code TEXT DEFAULT NULL,
            weasel_header_code_1 TEXT DEFAULT NULL,
            weasel_footer_code_1 TEXT DEFAULT NULL,
            weasel_header_code_for_analytics TEXT DEFAULT NULL,
            weasel_footer_code_for_analytics TEXT DEFAULT NULL,
            weasel_header_code_for_contact_form TEXT DEFAULT NULL,
            weasel_footer_code_for_contact_form TEXT DEFAULT NULL,
            ratingvalue_for_schema DECIMAL(3,2) DEFAULT NULL,
            reviewcount_for_schema DECIMAL(10,0) DEFAULT NULL,
            driggs_hours_for_schema TEXT DEFAULT NULL,
            georadius_for_schema INT(10) UNSIGNED DEFAULT NULL,
            home_anchor_for_silkweaver_services TEXT DEFAULT NULL,
            home_anchor_for_silkweaver_locations TEXT DEFAULT NULL,
            PRIMARY KEY (id),
            KEY fk_users_id (fk_users_id),
            KEY fk_domreg_hostaccount (fk_domreg_hostaccount),
            KEY sitespren_base (sitespren_base(50)),
            KEY true_root_domain (true_root_domain(50))
        ) $charset_collate;";
        
        dbDelta($zen_sitespren_sql);
        
        // Create zen_hoof_codes table for dynamic shortcodes (shared with Grove)
        $zen_hoof_codes_table = $wpdb->prefix . 'zen_hoof_codes';
        $zen_hoof_codes_sql = "CREATE TABLE IF NOT EXISTS $zen_hoof_codes_table (
            hoof_id int(11) NOT NULL AUTO_INCREMENT,
            hoof_slug varchar(100) NOT NULL,
            hoof_title varchar(255) DEFAULT NULL,
            hoof_content mediumtext NOT NULL,
            hoof_description text DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            is_system tinyint(1) DEFAULT 0,
            position_order int(11) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (hoof_id),
            UNIQUE KEY unique_slug (hoof_slug),
            INDEX idx_active (is_active),
            INDEX idx_system (is_system),
            INDEX idx_position (position_order)
        ) $charset_collate;";
        
        dbDelta($zen_hoof_codes_sql);
        
        // Create wp_pylons table for page content blocks
        $pylons_table = $wpdb->prefix . 'pylons';
        $pylons_sql = "CREATE TABLE IF NOT EXISTS $pylons_table (
            pylon_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            rel_wp_post_id BIGINT(20) UNSIGNED DEFAULT NULL,
            rel_plasma_page_id BIGINT(20) UNSIGNED DEFAULT NULL,
            pylon_archetype TEXT DEFAULT NULL,
            hero_mainheading TEXT DEFAULT NULL,
            hero_subheading TEXT DEFAULT NULL,
            hero_style_setting_background_size VARCHAR(50) DEFAULT NULL,
            chenblock_card1_title TEXT DEFAULT NULL,
            chenblock_card1_desc TEXT DEFAULT NULL,
            chenblock_card2_title TEXT DEFAULT NULL,
            chenblock_card2_desc TEXT DEFAULT NULL,
            chenblock_card3_title TEXT DEFAULT NULL,
            chenblock_card3_desc TEXT DEFAULT NULL,
            cta_zarl_heading TEXT DEFAULT NULL,
            cta_zarl_phone TEXT DEFAULT NULL,
            cta_zarl_availability TEXT DEFAULT NULL,
            cta_zarl_wait_time TEXT DEFAULT NULL,
            cta_zarl_rating TEXT DEFAULT NULL,
            cta_zarl_review_count TEXT DEFAULT NULL,
            sidebar_zebby_title TEXT DEFAULT NULL,
            sidebar_zebby_description TEXT DEFAULT NULL,
            sidebar_zebby_button_text_line_1 TEXT DEFAULT NULL,
            sidebar_zebby_button_text_line_2 TEXT DEFAULT NULL,
            sidebar_zebby_availability TEXT DEFAULT NULL,
            sidebar_zebby_wait_time TEXT DEFAULT NULL,
            trustblock_vezzy_title TEXT DEFAULT NULL,
            trustblock_vezzy_desc TEXT DEFAULT NULL,
            baynar1_main TEXT DEFAULT NULL,
            baynar2_main TEXT DEFAULT NULL,
            staircase_page_template_desired TEXT DEFAULT NULL,
            moniker TEXT DEFAULT NULL,
            exempt_from_silkweaver_menu_dynamical TINYINT(1) DEFAULT NULL,
            paragon_featured_image_id BIGINT(20) UNSIGNED DEFAULT NULL,
            paragon_description TEXT DEFAULT NULL,
            osb_box_title TEXT DEFAULT NULL,
            osb_services_per_row INT DEFAULT 4,
            osb_max_services_display INT DEFAULT 0,
            osb_is_enabled TINYINT(1) DEFAULT 0,
            locpage_topical_string TEXT DEFAULT NULL,
            locpage_neighborhood TEXT DEFAULT NULL,
            locpage_city TEXT DEFAULT NULL,
            locpage_state_code TEXT DEFAULT NULL,
            locpage_state_full TEXT DEFAULT NULL,
            locpage_gmaps_string TEXT DEFAULT NULL,
            short_anchor TEXT DEFAULT NULL,
            serena_faq_box_q1 TEXT DEFAULT NULL,
            serena_faq_box_a1 TEXT DEFAULT NULL,
            serena_faq_box_q2 TEXT DEFAULT NULL,
            serena_faq_box_a2 TEXT DEFAULT NULL,
            serena_faq_box_q3 TEXT DEFAULT NULL,
            serena_faq_box_a3 TEXT DEFAULT NULL,
            serena_faq_box_q4 TEXT DEFAULT NULL,
            serena_faq_box_a4 TEXT DEFAULT NULL,
            serena_faq_box_q5 TEXT DEFAULT NULL,
            serena_faq_box_a5 TEXT DEFAULT NULL,
            serena_faq_box_q6 TEXT DEFAULT NULL,
            serena_faq_box_a6 TEXT DEFAULT NULL,
            serena_faq_box_q7 TEXT DEFAULT NULL,
            serena_faq_box_a7 TEXT DEFAULT NULL,
            serena_faq_box_q8 TEXT DEFAULT NULL,
            serena_faq_box_a8 TEXT DEFAULT NULL,
            serena_faq_box_q9 TEXT DEFAULT NULL,
            serena_faq_box_a9 TEXT DEFAULT NULL,
            serena_faq_box_q10 TEXT DEFAULT NULL,
            serena_faq_box_a10 TEXT DEFAULT NULL,
            jchronology_order_for_blog_posts INT DEFAULT NULL,
            jchronology_batch INT DEFAULT NULL,
            kw1 TEXT DEFAULT NULL,
            kw2 TEXT DEFAULT NULL,
            kw3 TEXT DEFAULT NULL,
            kw4 TEXT DEFAULT NULL,
            kw5 TEXT DEFAULT NULL,
            kw6 TEXT DEFAULT NULL,
            kw7 TEXT DEFAULT NULL,
            kw8 TEXT DEFAULT NULL,
            content_ocean_1 TEXT DEFAULT NULL,
            content_ocean_2 TEXT DEFAULT NULL,
            content_ocean_3 TEXT DEFAULT NULL,
            kendall_our_process_heading TEXT DEFAULT NULL,
            kendall_our_process_subheading TEXT DEFAULT NULL,
            kendall_our_process_description TEXT DEFAULT NULL,
            kendall_our_process_step_1 TEXT DEFAULT NULL,
            kendall_our_process_step_2 TEXT DEFAULT NULL,
            kendall_our_process_step_3 TEXT DEFAULT NULL,
            kendall_our_process_step_4 TEXT DEFAULT NULL,
            kendall_our_process_step_5 TEXT DEFAULT NULL,
            kendall_our_process_step_6 TEXT DEFAULT NULL,
            kendall_our_process_step_7 TEXT DEFAULT NULL,
            kendall_our_process_step_8 TEXT DEFAULT NULL,
            kendall_our_process_step_9 TEXT DEFAULT NULL,
            kendall_our_process_step_10 TEXT DEFAULT NULL,
            ava_why_choose_us_heading TEXT DEFAULT NULL,
            ava_why_choose_us_subheading TEXT DEFAULT NULL,
            ava_why_choose_us_description TEXT DEFAULT NULL,
            ava_why_choose_us_reason_1 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_2 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_3 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_4 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_5 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_6 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_7 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_8 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_9 TEXT DEFAULT NULL,
            ava_why_choose_us_reason_10 TEXT DEFAULT NULL,
            olivia_authlinks_heading TEXT DEFAULT NULL,
            olivia_authlinks_subheading TEXT DEFAULT NULL,
            olivia_authlinks_description TEXT DEFAULT NULL,
            olivia_authlinks_1 TEXT DEFAULT NULL,
            olivia_authlinks_2 TEXT DEFAULT NULL,
            olivia_authlinks_3 TEXT DEFAULT NULL,
            olivia_authlinks_4 TEXT DEFAULT NULL,
            olivia_authlinks_5 TEXT DEFAULT NULL,
            olivia_authlinks_6 TEXT DEFAULT NULL,
            olivia_authlinks_7 TEXT DEFAULT NULL,
            olivia_authlinks_8 TEXT DEFAULT NULL,
            olivia_authlinks_9 TEXT DEFAULT NULL,
            olivia_authlinks_10 TEXT DEFAULT NULL,
            olivia_authlinks_outro TEXT DEFAULT NULL,
            brook_video_heading TEXT DEFAULT NULL,
            brook_video_subheading TEXT DEFAULT NULL,
            brook_video_description TEXT DEFAULT NULL,
            brook_video_1 TEXT DEFAULT NULL,
            brook_video_2 TEXT DEFAULT NULL,
            brook_video_3 TEXT DEFAULT NULL,
            brook_video_4 TEXT DEFAULT NULL,
            brook_video_outro TEXT DEFAULT NULL,
            sara_customhtml_datum TEXT DEFAULT NULL,
            liz_pricing_heading TEXT DEFAULT NULL,
            liz_pricing_description TEXT DEFAULT NULL,
            liz_pricing_body TEXT DEFAULT NULL,
            vectornode_meta_title TEXT DEFAULT NULL,
            vectornode_meta_description TEXT DEFAULT NULL,
            vectornode_robots TEXT DEFAULT NULL,
            vectornode_canonical_url TEXT DEFAULT NULL,
            vectornode_og_title TEXT DEFAULT NULL,
            vectornode_og_description TEXT DEFAULT NULL,
            vectornode_og_image_id BIGINT(20) DEFAULT NULL,
            vectornode_twitter_title TEXT DEFAULT NULL,
            vectornode_twitter_description TEXT DEFAULT NULL,
            vectornode_focus_keywords TEXT DEFAULT NULL,
            vectornode_schema_type VARCHAR(50) DEFAULT NULL,
            vectornode_breadcrumb_title TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (pylon_id),
            KEY rel_wp_post_id (rel_wp_post_id),
            KEY rel_plasma_page_id (rel_plasma_page_id)
        ) $charset_collate;";
        
        dbDelta($pylons_sql);
        
        // Create wp_box_orders table for box ordering system
        $box_orders_table = $wpdb->prefix . 'box_orders';
        $box_orders_sql = "CREATE TABLE IF NOT EXISTS $box_orders_table (
            item_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            rel_post_id BIGINT(20) UNSIGNED NOT NULL,
            is_active BOOLEAN DEFAULT FALSE,
            box_order_json JSON DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (item_id),
            KEY rel_post_id (rel_post_id),
            KEY is_active (is_active),
            FOREIGN KEY (rel_post_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($box_orders_sql);
        
        // Handle migration of box_order column to box_order_json
        $this->migrate_box_orders_table();
        
        // Handle database migrations for existing wp_pylons tables
        $this->migrate_pylons_table();
        
        // Update plugin database version to trigger future schema updates
        update_option('snefuru_plugin_db_version', SNEFURU_PLUGIN_VERSION);
        
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
        
        // Insert default hoof codes using version-based migration
        $this->migrate_default_hoof_codes();
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
    
    /**
     * Check plugin database version and update schema if needed
     */
    private function check_plugin_database_version() {
        $current_version = get_option('snefuru_plugin_db_version', '0.0.0');
        
        if (version_compare($current_version, SNEFURU_PLUGIN_VERSION, '<')) {
            error_log("Snefuru: Updating plugin database schema from {$current_version} to " . SNEFURU_PLUGIN_VERSION);
            
            // Re-run the activation hook to update database schema
            $this->activate();
            
            error_log('Snefuru: Plugin database schema updated successfully');
        }
    }
    
    /**
     * Insert default sitespren data if table is empty
     * Populates sitespren_base with current site domain
     */
    private function maybe_insert_default_sitespren_data() {
        global $wpdb;
        
        // Check if zen_sitespren table has any records
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $sitespren_table");
        
        if ($existing_count == 0) {
            // Extract domain from current site URL
            $site_url = get_site_url();
            $parsed_url = parse_url($site_url);
            $domain = isset($parsed_url['host']) ? $parsed_url['host'] : 'localhost';
            
            // Remove www. prefix if present
            if (strpos($domain, 'www.') === 0) {
                $domain = substr($domain, 4);
            }
            
            // Insert default record
            $result = $wpdb->insert(
                $sitespren_table,
                array(
                    'id' => wp_generate_uuid4(),
                    'sitespren_base' => $domain,
                    'driggs_brand_name' => get_option('blogname', 'My Site'),
                    'driggs_site_type_purpose' => 'WordPress Site',
                    'driggs_phone_country_code' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%d', '%s', '%s')
            );
            
            if ($result) {
                error_log("Snefuru: Created default sitespren record with domain: {$domain}");
            } else {
                error_log("Snefuru: Failed to create default sitespren record");
            }
        } else {
            error_log("Snefuru: Sitespren table already has {$existing_count} records, skipping default data insertion");
        }
    }
    
    /**
     * Version-based migration system for default hoof codes
     * Tracks which default entries have been installed and adds new ones on updates
     */
    private function migrate_default_hoof_codes() {
        global $wpdb;
        
        $hoof_codes_table = $wpdb->prefix . 'zen_hoof_codes';
        $installed_defaults = get_option('snefuru_hoof_defaults_version', '0.0.0');
        
        // Define default hoof codes with their introduction versions
        $default_codes = array(
            '1.0.0' => array(
                array(
                    'hoof_slug' => 'antelope_phone_piece',
                    'hoof_title' => 'Antelope Phone Piece',
                    'hoof_content' => '<div class="phone-number">[beginning_a_code_moose] Call us: [phone_local]</a></div>',
                    'hoof_description' => 'A complete phone link with local formatting wrapped in a div',
                    'is_active' => 1,
                    'is_system' => 1,
                    'position_order' => 1
                ),
                array(
                    'hoof_slug' => 'lamb_phone_piece',
                    'hoof_title' => 'Lamb Phone Piece',
                    'hoof_content' => '<button class="phone-button">[beginning_a_code_moose]📞 [phone_international]</a></button>',
                    'hoof_description' => 'A phone button with international formatting and emoji',
                    'is_active' => 1,
                    'is_system' => 1,
                    'position_order' => 2
                )
            ),
            '1.1.0' => array(
                array(
                    'hoof_slug' => 'muskox_phone_hub',
                    'hoof_title' => 'Muskox Phone Hub',
                    'hoof_content' => '<div class="phone-number">[beginning_a_code_moose] Call us: [phone_local]</a></div>',
                    'hoof_description' => 'A phone hub with local formatting and call-to-action text',
                    'is_active' => 1,
                    'is_system' => 1,
                    'position_order' => 3
                )
            )
        );
        
        // Install missing default codes from each version
        foreach ($default_codes as $version => $codes) {
            if (version_compare($installed_defaults, $version, '<')) {
                foreach ($codes as $code) {
                    // Check if this specific code already exists
                    $exists = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $hoof_codes_table WHERE hoof_slug = %s",
                        $code['hoof_slug']
                    ));
                    
                    if (!$exists) {
                        $wpdb->insert(
                            $hoof_codes_table,
                            $code,
                            array('%s', '%s', '%s', '%s', '%d', '%d', '%d')
                        );
                    }
                }
            }
        }
        
        // Update the installed defaults version
        update_option('snefuru_hoof_defaults_version', '1.1.0');
    }
    
    /**
     * Migrate existing box_orders table to rename box_order column to box_order_json
     */
    private function migrate_box_orders_table() {
        global $wpdb;
        
        $box_orders_table = $wpdb->prefix . 'box_orders';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$box_orders_table'") === $box_orders_table;
        if (!$table_exists) {
            return; // Table doesn't exist, no migration needed
        }
        
        // Check if old column exists and new column doesn't exist yet
        $old_column_exists = $wpdb->get_results("SHOW COLUMNS FROM $box_orders_table LIKE 'box_order'");
        $new_column_exists = $wpdb->get_results("SHOW COLUMNS FROM $box_orders_table LIKE 'box_order_json'");
        
        if (!empty($old_column_exists) && empty($new_column_exists)) {
            // Rename the column from box_order to box_order_json
            $wpdb->query("ALTER TABLE $box_orders_table CHANGE box_order box_order_json JSON DEFAULT NULL");
            error_log("Snefuru: Migrated box_orders table - renamed box_order column to box_order_json");
        }
    }
    
    /**
     * Migrate existing wp_pylons table to add new columns
     */
    private function migrate_pylons_table() {
        global $wpdb;
        
        $pylons_table = $wpdb->prefix . 'pylons';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$pylons_table'") === $pylons_table;
        if (!$table_exists) {
            return; // Table doesn't exist, no migration needed
        }
        
        // Check current migration version
        $migration_version = get_option('snefuru_pylons_migration_version', '0.0.0');
        
        // Migration for version 1.5.0 - Add new columns
        if (version_compare($migration_version, '1.5.0', '<')) {
            // Check and add moniker column
            $moniker_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'moniker'");
            if (empty($moniker_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN moniker TEXT DEFAULT NULL");
            }
            
            // Check and add exempt_from_silkweaver_menu_dynamical column
            $exempt_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'exempt_from_silkweaver_menu_dynamical'");
            if (empty($exempt_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN exempt_from_silkweaver_menu_dynamical TINYINT(1) DEFAULT NULL");
            }
            
            // Check and add paragon_featured_image_id column
            $image_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'paragon_featured_image_id'");
            if (empty($image_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN paragon_featured_image_id BIGINT(20) UNSIGNED DEFAULT NULL");
            }
            
            // Check and add paragon_description column
            $desc_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'paragon_description'");
            if (empty($desc_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN paragon_description TEXT DEFAULT NULL");
            }
            
            // Check and add our_services_box_title column (for backward compatibility)
            $old_title_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'our_services_box_title'");
            if (empty($old_title_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN our_services_box_title TEXT DEFAULT NULL");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '1.5.0');
        }
        
        // Check for version 1.6.0 migration - rename and add OSB columns
        $current_migration = get_option('snefuru_pylons_migration_version', '1.0.0');
        if (version_compare($current_migration, '1.6.0', '<')) {
            
            // Rename our_services_box_title to osb_box_title
            $old_title_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'our_services_box_title'");
            $new_title_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'osb_box_title'");
            
            if (!empty($old_title_exists) && empty($new_title_exists)) {
                // Rename the column
                $wpdb->query("ALTER TABLE $pylons_table CHANGE our_services_box_title osb_box_title TEXT DEFAULT NULL");
            } elseif (empty($new_title_exists)) {
                // Add the new column if it doesn't exist
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN osb_box_title TEXT DEFAULT NULL");
            }
            
            // Add osb_services_per_row column
            $per_row_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'osb_services_per_row'");
            if (empty($per_row_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN osb_services_per_row INT DEFAULT 4");
            }
            
            // Add osb_max_services_display column
            $max_display_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'osb_max_services_display'");
            if (empty($max_display_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN osb_max_services_display INT DEFAULT 0");
            }
            
            // Add osb_is_enabled column
            $is_enabled_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'osb_is_enabled'");
            if (empty($is_enabled_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN osb_is_enabled TINYINT(1) DEFAULT 0");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '1.6.0');
        }
        
        // Check for version 1.7.0 migration - Add locpage columns
        if (version_compare($current_migration, '1.7.0', '<')) {
            
            // Add locpage_topical_string column
            $topical_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'locpage_topical_string'");
            if (empty($topical_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN locpage_topical_string TEXT DEFAULT NULL");
            }
            
            // Add locpage_neighborhood column
            $neighborhood_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'locpage_neighborhood'");
            if (empty($neighborhood_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN locpage_neighborhood TEXT DEFAULT NULL");
            }
            
            // Add locpage_city column
            $city_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'locpage_city'");
            if (empty($city_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN locpage_city TEXT DEFAULT NULL");
            }
            
            // Add locpage_state_code column
            $state_code_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'locpage_state_code'");
            if (empty($state_code_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN locpage_state_code TEXT DEFAULT NULL");
            }
            
            // Add locpage_state_full column
            $state_full_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'locpage_state_full'");
            if (empty($state_full_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN locpage_state_full TEXT DEFAULT NULL");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '1.7.0');
        }
        
        // Check for version 1.8.0 migration - Add locpage_gmaps_string column
        if (version_compare($current_migration, '1.8.0', '<')) {
            
            // Add locpage_gmaps_string column
            $gmaps_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'locpage_gmaps_string'");
            if (empty($gmaps_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN locpage_gmaps_string TEXT DEFAULT NULL");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '1.8.0');
        }
        
        // Check for version 1.9.0 migration - Add jchronology columns
        if (version_compare($current_migration, '1.9.0', '<')) {
            
            // Add jchronology_order_for_blog_posts column
            $jchronology_order_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'jchronology_order_for_blog_posts'");
            if (empty($jchronology_order_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN jchronology_order_for_blog_posts INT DEFAULT NULL");
            }
            
            // Add jchronology_batch column
            $jchronology_batch_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'jchronology_batch'");
            if (empty($jchronology_batch_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN jchronology_batch INT DEFAULT NULL");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '1.9.0');
        }
        
        // Check for version 2.0.0 migration - Add kw columns
        if (version_compare($current_migration, '2.0.0', '<')) {
            
            // Add kw1 column
            $kw1_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'kw1'");
            if (empty($kw1_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN kw1 TEXT DEFAULT NULL");
            }
            
            // Add kw2 column
            $kw2_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'kw2'");
            if (empty($kw2_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN kw2 TEXT DEFAULT NULL");
            }
            
            // Add kw3 column
            $kw3_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'kw3'");
            if (empty($kw3_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN kw3 TEXT DEFAULT NULL");
            }
            
            // Add kw4 column
            $kw4_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'kw4'");
            if (empty($kw4_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN kw4 TEXT DEFAULT NULL");
            }
            
            // Add kw5 column
            $kw5_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'kw5'");
            if (empty($kw5_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN kw5 TEXT DEFAULT NULL");
            }
            
            // Add kw6 column
            $kw6_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'kw6'");
            if (empty($kw6_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN kw6 TEXT DEFAULT NULL");
            }
            
            // Add kw7 column
            $kw7_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'kw7'");
            if (empty($kw7_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN kw7 TEXT DEFAULT NULL");
            }
            
            // Add kw8 column
            $kw8_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'kw8'");
            if (empty($kw8_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN kw8 TEXT DEFAULT NULL");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '2.0.0');
        }
        
        // Migration for version 2.1.0 - Add liz_pricing columns
        if (version_compare($current_migration, '2.1.0', '<')) {
            // Add liz_pricing_heading column
            $liz_heading_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'liz_pricing_heading'");
            if (empty($liz_heading_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN liz_pricing_heading TEXT DEFAULT NULL");
            }
            
            // Add liz_pricing_description column
            $liz_desc_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'liz_pricing_description'");
            if (empty($liz_desc_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN liz_pricing_description TEXT DEFAULT NULL");
            }
            
            // Add liz_pricing_body column
            $liz_body_exists = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'liz_pricing_body'");
            if (empty($liz_body_exists)) {
                $wpdb->query("ALTER TABLE $pylons_table ADD COLUMN liz_pricing_body TEXT DEFAULT NULL");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '2.1.0');
        }
        
        // Migration for version 2.2.0 - Add weasel columns to zen_sitespren table
        if (version_compare($current_migration, '2.2.0', '<')) {
            $zen_sitespren_table = $wpdb->prefix . 'zen_sitespren';
            
            // Add weasel_header_code_1 column
            $weasel_header_exists = $wpdb->get_results("SHOW COLUMNS FROM $zen_sitespren_table LIKE 'weasel_header_code_1'");
            if (empty($weasel_header_exists)) {
                $wpdb->query("ALTER TABLE $zen_sitespren_table ADD COLUMN weasel_header_code_1 TEXT DEFAULT NULL");
            }
            
            // Add weasel_footer_code_1 column
            $weasel_footer_exists = $wpdb->get_results("SHOW COLUMNS FROM $zen_sitespren_table LIKE 'weasel_footer_code_1'");
            if (empty($weasel_footer_exists)) {
                $wpdb->query("ALTER TABLE $zen_sitespren_table ADD COLUMN weasel_footer_code_1 TEXT DEFAULT NULL");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '2.2.0');
        }
        
        // Migration for version 2.3.0 - Move contact_form_1_main_code from pylons to zen_sitespren
        if (version_compare($current_migration, '2.3.0', '<')) {
            $zen_sitespren_table = $wpdb->prefix . 'zen_sitespren';
            $pylons_table = $wpdb->prefix . 'pylons';
            
            // Add contact_form_1_main_code column to zen_sitespren if it doesn't exist
            $contact_form_exists_in_sitespren = $wpdb->get_results("SHOW COLUMNS FROM $zen_sitespren_table LIKE 'contact_form_1_main_code'");
            if (empty($contact_form_exists_in_sitespren)) {
                $wpdb->query("ALTER TABLE $zen_sitespren_table ADD COLUMN contact_form_1_main_code TEXT DEFAULT NULL AFTER contact_form_1_endpoint");
                
                // Migrate data from pylons to zen_sitespren if the column exists in pylons
                $contact_form_exists_in_pylons = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'contact_form_1_main_code'");
                if (!empty($contact_form_exists_in_pylons)) {
                    // Get any existing data from pylons
                    $pylon_data = $wpdb->get_row("SELECT contact_form_1_main_code FROM $pylons_table WHERE contact_form_1_main_code IS NOT NULL AND contact_form_1_main_code != '' LIMIT 1");
                    
                    if ($pylon_data && $pylon_data->contact_form_1_main_code) {
                        // Update zen_sitespren with the data
                        $wpdb->query($wpdb->prepare(
                            "UPDATE $zen_sitespren_table SET contact_form_1_main_code = %s WHERE wppma_id = 1",
                            $pylon_data->contact_form_1_main_code
                        ));
                    }
                }
            }
            
            // Remove contact_form_1_main_code column from pylons table if it exists
            $contact_form_exists_in_pylons = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'contact_form_1_main_code'");
            if (!empty($contact_form_exists_in_pylons)) {
                $wpdb->query("ALTER TABLE $pylons_table DROP COLUMN contact_form_1_main_code");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '2.3.0');
        }
        
        // Migration for version 2.4.0 - Add contact form weasel columns to zen_sitespren
        if (version_compare($current_migration, '2.4.0', '<')) {
            $zen_sitespren_table = $wpdb->prefix . 'zen_sitespren';
            
            // Add weasel_header_code_for_contact_form column
            $header_contact_exists = $wpdb->get_results("SHOW COLUMNS FROM $zen_sitespren_table LIKE 'weasel_header_code_for_contact_form'");
            if (empty($header_contact_exists)) {
                $wpdb->query("ALTER TABLE $zen_sitespren_table ADD COLUMN weasel_header_code_for_contact_form TEXT DEFAULT NULL AFTER weasel_footer_code_for_analytics");
            }
            
            // Add weasel_footer_code_for_contact_form column
            $footer_contact_exists = $wpdb->get_results("SHOW COLUMNS FROM $zen_sitespren_table LIKE 'weasel_footer_code_for_contact_form'");
            if (empty($footer_contact_exists)) {
                $wpdb->query("ALTER TABLE $zen_sitespren_table ADD COLUMN weasel_footer_code_for_contact_form TEXT DEFAULT NULL AFTER weasel_header_code_for_contact_form");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '2.4.0');
        }
        
        // Migration for version 2.5.0 - Add Silkweaver pinned link anchor columns to zen_sitespren
        if (version_compare($current_migration, '2.5.0', '<')) {
            $zen_sitespren_table = $wpdb->prefix . 'zen_sitespren';
            
            // Add home_anchor_for_silkweaver_services column
            $services_anchor_exists = $wpdb->get_results("SHOW COLUMNS FROM $zen_sitespren_table LIKE 'home_anchor_for_silkweaver_services'");
            if (empty($services_anchor_exists)) {
                $wpdb->query("ALTER TABLE $zen_sitespren_table ADD COLUMN home_anchor_for_silkweaver_services TEXT DEFAULT NULL");
            }
            
            // Add home_anchor_for_silkweaver_locations column
            $locations_anchor_exists = $wpdb->get_results("SHOW COLUMNS FROM $zen_sitespren_table LIKE 'home_anchor_for_silkweaver_locations'");
            if (empty($locations_anchor_exists)) {
                $wpdb->query("ALTER TABLE $zen_sitespren_table ADD COLUMN home_anchor_for_silkweaver_locations TEXT DEFAULT NULL");
            }
            
            // Update migration version
            update_option('snefuru_pylons_migration_version', '2.5.0');
        }
    }
    
    /**
     * Initialize Weasel Code Injection
     * Sets up hooks for injecting header and footer codes
     */
    private function init_weasel_code_injection() {
        // Only run on frontend (not admin)
        if (!is_admin()) {
            // DISABLED OLD VECTORNODE - using new system
            // add_action('wp_head', array($this, 'inject_vectornode_meta_tags'), 1); // High priority to override other meta
            add_action('wp_head', array($this, 'inject_weasel_header_codes'), 999);
            add_action('wp_footer', array($this, 'inject_weasel_footer_codes'), 999);
        }
    }
    
    /**
     * Inject weasel header codes into the <head> section
     * Runs just before </head> tag
     */
    public function inject_weasel_header_codes() {
        global $wpdb;
        
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$sitespren_table'") != $sitespren_table) {
            return;
        }
        
        // Get header codes from database
        $header_codes = $wpdb->get_row("
            SELECT 
                weasel_header_code_1,
                weasel_header_code_for_analytics,
                weasel_header_code_for_contact_form
            FROM $sitespren_table 
            LIMIT 1
        ", ARRAY_A);
        
        if (!$header_codes) {
            return;
        }
        
        // Output header codes if they exist
        echo "\n<!-- Weasel Header Codes Start -->\n";
        
        // Inject weasel_header_code_1
        if (!empty($header_codes['weasel_header_code_1'])) {
            echo "<!-- Weasel Header Code 1 -->\n";
            echo $header_codes['weasel_header_code_1'] . "\n";
        }
        
        // Inject weasel_header_code_for_analytics
        if (!empty($header_codes['weasel_header_code_for_analytics'])) {
            echo "<!-- Weasel Header Code for Analytics -->\n";
            echo $header_codes['weasel_header_code_for_analytics'] . "\n";
        }
        
        // Inject weasel_header_code_for_contact_form
        if (!empty($header_codes['weasel_header_code_for_contact_form'])) {
            echo "<!-- Weasel Header Code for Contact Form -->\n";
            echo $header_codes['weasel_header_code_for_contact_form'] . "\n";
        }
        
        echo "<!-- Weasel Header Codes End -->\n";
    }
    
    /**
     * Inject weasel footer codes into the footer section
     * Runs just before </body> tag
     */
    public function inject_weasel_footer_codes() {
        global $wpdb;
        
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$sitespren_table'") != $sitespren_table) {
            return;
        }
        
        // Get footer codes from database
        $footer_codes = $wpdb->get_row("
            SELECT 
                weasel_footer_code_1,
                weasel_footer_code_for_analytics,
                weasel_footer_code_for_contact_form
            FROM $sitespren_table 
            LIMIT 1
        ", ARRAY_A);
        
        if (!$footer_codes) {
            return;
        }
        
        // Output footer codes if they exist
        echo "\n<!-- Weasel Footer Codes Start -->\n";
        
        // Inject weasel_footer_code_1
        if (!empty($footer_codes['weasel_footer_code_1'])) {
            echo "<!-- Weasel Footer Code 1 -->\n";
            echo $footer_codes['weasel_footer_code_1'] . "\n";
        }
        
        // Inject weasel_footer_code_for_analytics
        if (!empty($footer_codes['weasel_footer_code_for_analytics'])) {
            echo "<!-- Weasel Footer Code for Analytics -->\n";
            echo $footer_codes['weasel_footer_code_for_analytics'] . "\n";
        }
        
        // Inject weasel_footer_code_for_contact_form
        if (!empty($footer_codes['weasel_footer_code_for_contact_form'])) {
            echo "<!-- Weasel Footer Code for Contact Form -->\n";
            echo $footer_codes['weasel_footer_code_for_contact_form'] . "\n";
        }
        
        echo "<!-- Weasel Footer Codes End -->\n";
    }
    
    /**
     * OLD inject VectorNode function - DISABLED 
     * Now using new VectorNode system with class-vectornode-frontend.php
     */
    public function inject_vectornode_meta_tags() {
        // DISABLED - using new VectorNode system instead
        return;
    }
}


/**
 * Global function to output VectorNode meta tags
 * This approach may work better with wp_head hook timing
 */
function vectornode_output_meta_tags() {
    // Check if VectorNode is enabled in settings (simple approach to avoid namespace issues)
    $options = get_option('ruplin_settings');
    $vectornode_enabled = isset($options['enable_vectornode']) && $options['enable_vectornode'] == 1;
    
    if (!$vectornode_enabled) {
        return;
    }
    
    global $vectornode_frontend_instance;
    
    if ($vectornode_frontend_instance && method_exists($vectornode_frontend_instance, 'output_meta_tags')) {
        $vectornode_frontend_instance->output_meta_tags();
    }
}

// Include settings page
require_once plugin_dir_path(__FILE__) . 'ruplin_settings_mar.php';

// Initialize the plugin
new SnefuruPlugin(); 