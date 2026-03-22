<?php
/**
 * Fundamental Image Setter - Main Controller
 * 
 * @package Ruplin
 * @subpackage FundamentalImageSetter
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Fundamental_Image_Setter_Controller {
    
    private $notice_suppressor;
    private $page_renderer;
    
    public function __construct() {
        $this->load_dependencies();
        $this->init_components();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'class-notice-suppressor.php';
        require_once plugin_dir_path(__FILE__) . 'class-page-renderer.php';
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        $this->notice_suppressor = new Ruplin_Fundamental_Image_Setter_Notice_Suppressor();
        $this->page_renderer = new Ruplin_Fundamental_Image_Setter_Page_Renderer();
    }
    
    /**
     * Main entry point for rendering the admin page
     */
    public function render() {
        // Activate notice suppression
        $this->notice_suppressor->suppress_all_notices();
        
        // Render the page
        $this->page_renderer->render();
    }
    
    /**
     * Check if we're on our specific admin page
     */
    public function is_current_page() {
        $screen = get_current_screen();
        return ($screen && $screen->base === 'ruplin_page_fundamental_image_setter');
    }
    
    /**
     * Initialize hooks for the page
     */
    public function init_hooks() {
        // Hook notice suppression early
        add_action('current_screen', array($this, 'maybe_suppress_notices'));
    }
    
    /**
     * Conditionally suppress notices if on our page
     */
    public function maybe_suppress_notices() {
        if ($this->is_current_page()) {
            $this->notice_suppressor->suppress_all_notices();
        }
    }
}