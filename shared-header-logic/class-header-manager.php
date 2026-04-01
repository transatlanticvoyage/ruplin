<?php
/**
 * Header Manager Class
 * Central coordinator for all header functionality
 * Handles logo retrieval, template variables, and rendering coordination
 * Test comment to show in VSCode source control
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Header_Manager {
    
    private $header_type;
    private $config;
    
    public function __construct($header_type = 'header2') {
        $this->header_type = $header_type;
        $this->config = $this->get_header_config();
    }
    
    /**
     * Get header-specific configuration
     */
    private function get_header_config() {
        $configs = array(
            'header1' => array(
                'class' => 'header1-wrapper',
                'container_class' => 'header1-container',
                'sticky' => true,
                'template' => 'header1-template.html'
            ),
            'header2' => array(
                'class' => 'hs2-header',
                'container_class' => 'hs2-container', 
                'sticky' => true,
                'template' => 'header-template.html'
            ),
            'header3' => array(
                'class' => 'header3-wrapper',
                'container_class' => 'header3-container',
                'sticky' => false,
                'template' => 'header3-template.html'
            )
        );
        
        return isset($configs[$this->header_type]) ? $configs[$this->header_type] : $configs['header2'];
    }
    
    /**
     * Get all data needed for header rendering
     */
    public function get_header_data() {
        return array(
            'logo_html' => $this->get_logo_html(),
            'menu_html' => $this->get_menu_html(),
            'phone_html' => $this->get_phone_html(),
            'home_url' => esc_url(home_url('/')),
            'header_class' => $this->config['class'],
            'container_class' => $this->config['container_class'],
            'sticky_enabled' => $this->config['sticky'] ? 'true' : 'false'
        );
    }
    
    /**
     * Get logo HTML - centralized logo logic
     */
    private function get_logo_html() {
        // Try staircase theme option first
        $logo_url = get_option('staircase_header_logo', '');
        
        // Fallback to WordPress custom logo
        if (empty($logo_url) && has_custom_logo()) {
            $custom_logo_id = get_theme_mod('custom_logo');
            $logo_data = wp_get_attachment_image_src($custom_logo_id, 'full');
            if ($logo_data) {
                $logo_url = $logo_data[0];
            }
        }
        
        if (!empty($logo_url)) {
            return '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
        } else {
            // Determine title class based on header type
            $title_class = $this->header_type === 'header2' ? 'hs2-site-title' : 'site-title';
            return '<h1 class="' . $title_class . '">' . get_bloginfo('name') . '</h1>';
        }
    }
    
    /**
     * Get menu HTML using shared Silkweaver integration
     */
    private function get_menu_html() {
        if (class_exists('Ruplin_Silkweaver_Integration')) {
            $silkweaver = new Ruplin_Silkweaver_Integration();
            return $silkweaver->get_menu_for_header($this->header_type);
        }
        
        // Fallback to basic WordPress menu
        ob_start();
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_class' => $this->get_menu_class(),
            'container' => false,
            'fallback_cb' => array($this, 'fallback_menu')
        ));
        return ob_get_clean();
    }
    
    /**
     * Get menu class based on header type
     */
    private function get_menu_class() {
        switch ($this->header_type) {
            case 'header2':
                return 'hs2-menu';
            case 'header1':
                return 'header1-menu';
            case 'header3':
                return 'header3-menu';
            default:
                return 'site-menu';
        }
    }
    
    /**
     * Get phone HTML using shared phone utilities
     */
    private function get_phone_html() {
        if (class_exists('Ruplin_Phone_Formatter')) {
            $phone_formatter = new Ruplin_Phone_Formatter();
            return $phone_formatter->get_phone_html_for_header($this->header_type);
        }
        
        // Fallback phone logic
        if (function_exists('staircase_get_header_phone')) {
            $phone_raw = staircase_get_header_phone();
            $phone_formatted = function_exists('staircase_get_formatted_phone') ? 
                              staircase_get_formatted_phone() : $phone_raw;
            
            if (!empty($phone_raw)) {
                $phone_class = $this->header_type === 'header2' ? 'hs2-phone-button' : 'phone-button';
                $icon_class = $this->header_type === 'header2' ? 'hs2-phone-icon' : 'phone-icon';
                
                return '<a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $phone_raw)) . '" class="' . $phone_class . '">' .
                       '<svg class="' . $icon_class . '" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">' .
                       '<path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>' .
                       '</svg>' . esc_html($phone_formatted) . '</a>';
            }
        }
        
        return '';
    }
    
    /**
     * Render header using template system
     */
    public function render_template($header_data) {
        // Get template path
        $template_path = $this->get_template_path();
        
        if (!file_exists($template_path)) {
            return $this->render_fallback_header($header_data);
        }
        
        $template_html = file_get_contents($template_path);
        
        // Replace template variables
        $replacements = array(
            '{{LOGO_PLACEHOLDER}}' => $header_data['logo_html'],
            '{{MENU_PLACEHOLDER}}' => $header_data['menu_html'],
            '{{PHONE_PLACEHOLDER}}' => $header_data['phone_html'],
            '{{HOME_URL}}' => $header_data['home_url'],
            '{{HEADER_CLASS}}' => $header_data['header_class'],
            '{{CONTAINER_CLASS}}' => $header_data['container_class'],
            '{{STICKY_ENABLED}}' => $header_data['sticky_enabled']
        );
        
        foreach ($replacements as $placeholder => $replacement) {
            $template_html = str_replace($placeholder, $replacement, $template_html);
        }
        
        return $template_html;
    }
    
    /**
     * Get template file path
     */
    private function get_template_path() {
        // Try header-specific template first
        $header_template = get_template_directory() . '/headers/' . $this->header_type . '/assets/' . $this->config['template'];
        if (file_exists($header_template)) {
            return $header_template;
        }
        
        // Fallback to shared template
        $shared_template = dirname(__FILE__) . '/templates/base-header.php';
        if (file_exists($shared_template)) {
            return $shared_template;
        }
        
        return false;
    }
    
    /**
     * Fallback header when template not found
     */
    private function render_fallback_header($header_data) {
        return '<header class="' . esc_attr($header_data['header_class']) . '">' .
               '<div class="' . esc_attr($header_data['container_class']) . '">' .
               '<div class="header-logo">' . $header_data['logo_html'] . '</div>' .
               '<nav class="header-nav">' . $header_data['menu_html'] . '</nav>' .
               '<div class="header-cta">' . $header_data['phone_html'] . '</div>' .
               '</div></header>';
    }
    
    /**
     * Fallback menu
     */
    public function fallback_menu() {
        $menu_class = $this->get_menu_class();
        return '<ul class="' . $menu_class . '">' .
               '<li class="menu-item"><a class="menu-link" href="' . esc_url(home_url('/')) . '">Home</a></li>' .
               '</ul>';
    }
    
    /**
     * Add body class for header type
     */
    public function add_body_class() {
        add_filter('body_class', function($classes) {
            $classes[] = $this->header_type . '-active';
            return $classes;
        });
    }
    
    /**
     * Enqueue header-specific assets
     */
    public function enqueue_assets() {
        $assets_url = get_template_directory_uri() . '/headers/' . $this->header_type . '/assets/';
        $version = '2.0.0';
        
        wp_enqueue_style(
            $this->header_type . '-styles',
            $assets_url . 'css/styles.css',
            array(),
            $version
        );
        
        wp_enqueue_script(
            $this->header_type . '-scripts', 
            $assets_url . 'js/scripts.js',
            array('jquery'),
            $version,
            true
        );
    }
}