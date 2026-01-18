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
        // Initialize frontend output only on non-admin pages
        if (!is_admin()) {
            add_action('init', array($this, 'init_frontend'), 10);
        }
        
        // Initialize settings
        VectorNode_Settings::get_instance();
    }
    
    /**
     * Initialize frontend components
     */
    public function init_frontend() {
        VectorNode_Frontend::get_instance();
    }
    
    /**
     * Check if VectorNode is enabled for the current post
     */
    public static function is_enabled($post_id = null) {
        global $wpdb;
        
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$post_id) {
            return false;
        }
        
        $pylons_table = $wpdb->prefix . 'pylons';
        $enabled = $wpdb->get_var($wpdb->prepare(
            "SELECT vectornode_enabled FROM $pylons_table WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        // Default to true if no record exists
        return $enabled === null ? true : (bool) $enabled;
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
                vectornode_breadcrumb_title,
                vectornode_enabled
            FROM $pylons_table 
            WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
    }
}