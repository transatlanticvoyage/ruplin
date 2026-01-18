<?php
/**
 * VectorNode 404 Resolver Class
 * 
 * Handles SEO data for 404 error pages
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class Resolver_404 extends VectorNode_Resolver {
    
    /**
     * Load data - 404 pages don't have database records
     */
    protected function load_data() {
        $this->data = array();
    }
    
    /**
     * Get SEO title for 404 pages
     */
    public function get_title() {
        // Get pattern
        $pattern = $this->settings->get_title_pattern('404');
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern);
    }
    
    /**
     * Get meta description for 404 pages
     */
    public function get_description() {
        // Use pattern
        $pattern = $this->settings->get_description_pattern('404');
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern);
    }
    
    /**
     * Get default canonical URL
     */
    protected function get_default_canonical() {
        // 404 pages shouldn't have canonical
        return '';
    }
    
    /**
     * Override robots for 404 pages
     */
    public function get_robots() {
        // Always noindex 404 pages
        return array(
            'index' => 'noindex',
            'follow' => 'follow'
        );
    }
}