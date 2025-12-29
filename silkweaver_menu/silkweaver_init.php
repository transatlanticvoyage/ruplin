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
            width: auto;
            min-width: 220px;
            max-width: 880px;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 8px;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            border: 1px solid rgba(0,0,0,0.1);
            overflow: hidden;
            height: calc(80vh - 100px);
            min-height: calc(80vh - 100px);
            display: grid !important;
            grid-auto-flow: column !important;
            grid-template-rows: repeat(auto-fill, minmax(44px, 1fr)) !important;
            grid-gap: 0 !important;
            font-size: 0 !important;
            line-height: 0 !important;
            letter-spacing: 0 !important;
            word-spacing: 0 !important;
            text-indent: 0 !important;
        }
        
        .silkweaver-dropdown:hover .silkweaver-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .silkweaver-dropdown .silkweaver-dropdown-menu li {
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1 !important;
            list-style: none !important;
            border: none !important;
            background: none !important;
            float: none !important;
            width: 220px !important;
            box-sizing: border-box !important;
            display: block !important;
            font-size: 0 !important;
            height: auto !important;
            align-self: stretch !important;
        }
        
        /* Hide empty list items - more specific */
        .silkweaver-dropdown .silkweaver-dropdown-menu li:empty {
            display: none !important;
        }
        
        .silkweaver-dropdown .silkweaver-dropdown-menu li:has(a:empty) {
            display: none !important;
        }
        
        .silkweaver-dropdown .silkweaver-dropdown-menu li::before,
        .silkweaver-dropdown .silkweaver-dropdown-menu li::after {
            display: none !important;
            content: none !important;
        }
        
        .silkweaver-dropdown .silkweaver-dropdown-menu a {
            display: block !important;
            padding: 12px 20px !important;
            color: #333 !important;
            font-weight: 400 !important;
            font-size: 14px !important;
            border-bottom: 1px solid #f0f0f0 !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            transition: background-color 0.2s ease !important;
            margin: 0 !important;
            text-decoration: none !important;
            width: 220px !important;
            box-sizing: border-box !important;
            line-height: 1.4 !important;
            vertical-align: top !important;
        }
        
        .silkweaver-dropdown .silkweaver-dropdown-menu a::before,
        .silkweaver-dropdown .silkweaver-dropdown-menu a::after {
            display: none !important;
            content: none !important;
        }
        
        .silkweaver-dropdown .silkweaver-dropdown-menu li:last-child a {
            border-bottom: none !important;
        }
        
        .silkweaver-dropdown .silkweaver-dropdown-menu a:hover {
            background: #f8f9fa !important;
            opacity: 1 !important;
        }
        
        
        /* Responsive height adjustments */
        @media (max-height: 600px) {
            .silkweaver-dropdown .silkweaver-dropdown-menu {
                max-height: calc(60vh - 50px) !important;
                column-width: 200px !important;
            }
            .silkweaver-dropdown .silkweaver-dropdown-menu li,
            .silkweaver-dropdown .silkweaver-dropdown-menu a {
                width: 200px !important;
            }
        }
        
        @media (min-height: 900px) {
            .silkweaver-dropdown .silkweaver-dropdown-menu {
                max-height: calc(85vh - 150px) !important;
            }
        }
        
        /* Desktop-only multi-column (disable on mobile) */
        @media (min-width: 769px) {
            .silkweaver-dropdown .silkweaver-dropdown-menu {
                display: grid !important;
                grid-auto-flow: column !important;
                grid-template-rows: repeat(auto-fill, minmax(44px, 1fr)) !important;
                grid-gap: 0 !important;
            }
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
            
            .silkweaver-dropdown .silkweaver-dropdown-menu {
                position: static !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                box-shadow: none !important;
                background: transparent !important;
                margin-left: 20px !important;
                border: none !important;
                padding: 5px 0 !important;
                display: block !important;
                grid-auto-flow: row !important;
                grid-template-rows: none !important;
                height: auto !important;
                min-height: auto !important;
                max-height: none !important;
                max-width: none !important;
            }
            
            .silkweaver-dropdown .silkweaver-dropdown-menu li,
            .silkweaver-dropdown .silkweaver-dropdown-menu a {
                width: 100% !important;
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