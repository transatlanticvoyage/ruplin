<?php
/**
 * VectorNode Archive Resolver Class
 * 
 * Handles SEO data for category, tag, and taxonomy archives
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class Resolver_Archive extends VectorNode_Resolver {
    
    /**
     * Constructor - accepts term object
     */
    public function __construct($term = null) {
        if (!$term) {
            $term = get_queried_object();
        }
        parent::__construct($term);
    }
    
    /**
     * Load data - archives don't have database records yet
     */
    protected function load_data() {
        // TODO: Could extend to support term meta in future
        $this->data = array();
    }
    
    /**
     * Get SEO title for archive pages
     */
    public function get_title() {
        if (!$this->object || !isset($this->object->taxonomy)) {
            return get_bloginfo('name');
        }
        
        // Get pattern based on taxonomy
        $pattern = $this->settings->get_title_pattern($this->object->taxonomy);
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern, $this->object);
    }
    
    /**
     * Get meta description for archive pages
     */
    public function get_description() {
        if (!$this->object || !isset($this->object->taxonomy)) {
            return '';
        }
        
        // Check if term has description
        if (!empty($this->object->description)) {
            return wp_strip_all_tags($this->object->description);
        }
        
        // Use pattern
        $pattern = $this->settings->get_description_pattern($this->object->taxonomy);
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern, $this->object);
    }
    
    /**
     * Get default canonical URL
     */
    protected function get_default_canonical() {
        if (!$this->object || !isset($this->object->term_id)) {
            return home_url();
        }
        
        return get_term_link($this->object);
    }
    
    /**
     * Override robots for archive pages
     */
    public function get_robots() {
        $robots = parent::get_robots();
        
        // Check if paginated archives should be noindexed
        if (is_paged() && $this->settings->should_noindex('paginated')) {
            $robots['index'] = 'noindex';
        }
        
        return $robots;
    }
}