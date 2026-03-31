<?php

/**
 * Loon Static Site Generator Admin Class
 * 
 * Handles admin menu registration and page initialization
 */

if (!defined('ABSPATH')) {
    exit;
}

class Loon_Static_Site_Generator_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 99);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_submenu_page(
            'snefuru', // Parent slug (Ruplin's main menu)
            'Loon Static Site Generator',
            'Loon Generator',
            'manage_options',
            'loon_static_site_generation_mar',
            'loon_static_site_generation_mar_admin_page'
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'snefuru_page_loon_static_site_generation_mar') {
            return;
        }
        
        // Enqueue any specific scripts or styles for this page here
        // wp_enqueue_style('loon-admin-styles', plugin_dir_url(__FILE__) . 'assets/loon-admin.css', array(), '1.0.0');
        // wp_enqueue_script('loon-admin-scripts', plugin_dir_url(__FILE__) . 'assets/loon-admin.js', array('jquery'), '1.0.0', true);
    }
    
    /**
     * Initialize the admin functionality
     */
    public static function init() {
        new self();
    }
}

// Initialize the admin class
add_action('init', array('Loon_Static_Site_Generator_Admin', 'init'));