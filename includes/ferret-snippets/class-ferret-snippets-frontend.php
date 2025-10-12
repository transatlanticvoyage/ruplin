<?php
/**
 * Ferret Snippets Frontend Handler
 * 
 * Handles injection of code snippets into frontend pages
 * 
 * @package Ruplin
 * @subpackage FerretSnippets
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ferret_Snippets_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize frontend hooks
     */
    private function init_hooks() {
        // Header injection - high priority to ensure it runs
        add_action('wp_head', array($this, 'inject_header_code'), 999);
        
        // Footer injection - high priority to ensure it runs
        add_action('wp_footer', array($this, 'inject_footer_code'), 999);
    }
    
    /**
     * Inject header code for current post/page
     */
    public function inject_header_code() {
        $code = $this->get_code_for_current_post('header');
        if (!empty($code)) {
            echo "\n<!-- Ferret Snippets: Header Code -->\n";
            echo $code;
            echo "\n<!-- End Ferret Snippets: Header Code -->\n";
        }
    }
    
    /**
     * Inject footer code for current post/page
     */
    public function inject_footer_code() {
        $code = $this->get_code_for_current_post('footer');
        if (!empty($code)) {
            echo "\n<!-- Ferret Snippets: Footer Code -->\n";
            echo $code;
            echo "\n<!-- End Ferret Snippets: Footer Code -->\n";
        }
    }
    
    /**
     * Get code snippet for current post
     */
    private function get_code_for_current_post($type) {
        // Only run on singular posts/pages
        if (!is_singular()) {
            return '';
        }
        
        global $post;
        if (!$post || !$post->ID) {
            return '';
        }
        
        // Validate type
        if (!in_array($type, array('header', 'footer'))) {
            return '';
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'zen_orbitposts';
        $column = 'ferret_' . $type . '_code';
        
        $code = $wpdb->get_var($wpdb->prepare(
            "SELECT $column FROM $table WHERE rel_wp_post_id = %d",
            $post->ID
        ));
        
        // Return the raw code - WordPress will handle any necessary escaping
        // since this is meant for JavaScript, CSS, etc.
        return $code;
    }
    
    /**
     * Check if current user can see debug info
     */
    private function can_debug() {
        return current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG;
    }
}