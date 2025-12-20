<?php
/**
 * Silkweaver Menu System Initialization
 * 
 * @package Ruplin
 * @subpackage SilkweaverMenu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Silkweaver_Menu_System {
    
    public function __construct() {
        // Include required files
        $this->include_files();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        require_once plugin_dir_path(__FILE__) . 'silkweaver_admin_page.php';
        require_once plugin_dir_path(__FILE__) . 'silkweaver_renderer.php';
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin menu is now handled in class-admin.php
        // add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }
    
    /**
     * Enqueue frontend styles for silkweaver menu
     */
    public function enqueue_frontend_styles() {
        if (get_option('silkweaver_use_system', true)) {
            // Try to add to existing stylesheet, fallback to inline style
            $theme_handle = wp_style_is('staircase-style') ? 'staircase-style' : 'style';
            wp_add_inline_style($theme_handle, $this->get_menu_css());
        }
    }
    
    /**
     * Get CSS for silkweaver menu styling
     */
    private function get_menu_css() {
        return '
        /* Silkweaver Menu Styles - Integrated with Staircase Theme */
        .silkweaver-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .silkweaver-menu li {
            position: relative;
            margin-right: 30px;
        }
        
        .silkweaver-menu li:last-child {
            margin-right: 0;
        }
        
        .silkweaver-menu a {
            text-decoration: none;
            color: inherit;
            font-weight: 500;
            font-size: 16px;
            transition: opacity 0.3s ease;
            display: block;
            padding: 8px 0;
        }
        
        .silkweaver-menu a:hover {
            opacity: 0.7;
        }
        
        /* Dropdown Menu Styles */
        .silkweaver-dropdown {
            position: relative;
        }
        
        .silkweaver-dropdown-toggle::after {
            content: "▼";
            font-size: 10px;
            margin-left: 5px;
            opacity: 0.7;
        }
        
        /* Parent button styling for dynamic menu items */
        .silkweaver-parent-button {
            background: none;
            border: none;
            color: inherit;
            font-weight: 500;
            font-size: 16px;
            transition: opacity 0.3s ease;
            padding: 8px 0;
            cursor: pointer;
            font-family: inherit;
        }
        
        .silkweaver-parent-button:hover {
            opacity: 0.7;
        }
        
        .silkweaver-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 220px;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 8px;
            list-style: none;
            margin: 0;
            padding: 15px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .silkweaver-dropdown:hover .silkweaver-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .silkweaver-dropdown-menu li {
            margin: 0;
        }
        
        .silkweaver-dropdown-menu a {
            padding: 10px 20px;
            color: #333;
            font-weight: 400;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }
        
        .silkweaver-dropdown-menu li:last-child a {
            border-bottom: none;
        }
        
        .silkweaver-dropdown-menu a:hover {
            background: #f8f9fa;
            opacity: 1;
        }
        
        /* Mobile responsive - inherit theme breakpoint */
        @media (max-width: 768px) {
            .silkweaver-menu {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .silkweaver-menu li {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100%;
            }
            
            .silkweaver-dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                background: transparent;
                margin-left: 20px;
                border: none;
                padding: 5px 0;
            }
            
            .silkweaver-dropdown-toggle::after {
                display: none;
            }
            
            .silkweaver-parent-button {
                width: 100%;
                text-align: left;
            }
        }
        ';
    }
}

// Initialize the silkweaver menu system
new Silkweaver_Menu_System();