<?php
/**
 * VectorNode Search Resolver Class
 * 
 * Handles SEO data for search results pages
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class Resolver_Search extends VectorNode_Resolver {
    
    /**
     * Load data - search pages don't have database records
     */
    protected function load_data() {
        $this->data = array();
    }
    
    /**
     * Get SEO title for search pages
     */
    public function get_title() {
        // Get pattern
        $pattern = $this->settings->get_title_pattern('search');
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern);
    }
    
    /**
     * Get meta description for search pages
     */
    public function get_description() {
        // Use pattern
        $pattern = $this->settings->get_description_pattern('search');
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern);
    }
    
    /**
     * Get default canonical URL
     */
    protected function get_default_canonical() {
        $search_query = get_search_query();
        if (empty($search_query)) {
            return home_url();
        }
        
        return home_url('?s=' . urlencode($search_query));
    }
    
    /**
     * Override robots for search pages
     */
    public function get_robots() {
        $robots = parent::get_robots();
        
        // Check if search pages should be noindexed
        if ($this->settings->should_noindex('search')) {
            $robots['index'] = 'noindex';
        }
        
        return $robots;
    }
}