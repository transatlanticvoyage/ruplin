<?php
/**
 * VectorNode SEO Core Class
 * 
 * Main initialization and coordination for the VectorNode SEO system
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class VectorNode_Core {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance of the class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        // Load base resolver
        require_once plugin_dir_path(__FILE__) . 'class-vectornode-resolver.php';
        
        // Load specific resolvers
        require_once plugin_dir_path(__FILE__) . 'resolvers/class-resolver-singular.php';
        require_once plugin_dir_path(__FILE__) . 'resolvers/class-resolver-archive.php';
        require_once plugin_dir_path(__FILE__) . 'resolvers/class-resolver-author.php';
        require_once plugin_dir_path(__FILE__) . 'resolvers/class-resolver-search.php';
        require_once plugin_dir_path(__FILE__) . 'resolvers/class-resolver-404.php';
        
        // Load other components
        require_once plugin_dir_path(__FILE__) . 'class-vectornode-template-vars.php';
        require_once plugin_dir_path(__FILE__) . 'class-vectornode-generator.php';
        require_once plugin_dir_path(__FILE__) . 'class-vectornode-frontend.php';
        require_once plugin_dir_path(__FILE__) . 'class-vectornode-settings.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Check if VectorNode is enabled in settings
        if (!self::is_vectornode_enabled()) {
            return; // Exit early if VectorNode is disabled
        }
        
        // Initialize settings
        VectorNode_Settings::get_instance();
        
        // Initialize frontend output on wp hook to ensure proper timing
        if (!is_admin()) {
            add_action('wp', function() {
                VectorNode_Frontend::get_instance();
            }, 1);
        }
    }
    
    /**
     * Initialize frontend components
     */
    public function init_frontend() {
        VectorNode_Frontend::get_instance();
    }
    
    /**
     * Check if VectorNode is enabled for the current post
     * Always returns true now since we removed the enable/disable functionality
     */
    public static function is_enabled($post_id = null) {
        // VectorNode is always enabled now - no more enable/disable switches
        return true;
    }
    
    /**
     * Get VectorNode data for a post
     */
    public static function get_post_data($post_id) {
        global $wpdb;
        
        if (!$post_id) {
            return null;
        }
        
        $pylons_table = $wpdb->prefix . 'pylons';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT 
                vectornode_meta_title,
                vectornode_meta_description,
                vectornode_robots,
                vectornode_canonical_url,
                vectornode_og_title,
                vectornode_og_description,
                vectornode_og_image_id,
                vectornode_twitter_title,
                vectornode_twitter_description,
                vectornode_focus_keywords,
                vectornode_schema_type,
                vectornode_breadcrumb_title
            FROM $pylons_table 
            WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
    }
    
    /**
     * Check if VectorNode system is enabled in settings
     */
    public static function is_vectornode_enabled() {
        $options = get_option('ruplin_settings');
        return isset($options['enable_vectornode']) && $options['enable_vectornode'] == 1;
    }
}