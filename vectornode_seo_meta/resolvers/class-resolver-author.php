<?php
/**
 * VectorNode Author Resolver Class
 * 
 * Handles SEO data for author archive pages
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class Resolver_Author extends VectorNode_Resolver {
    
    /**
     * Constructor - accepts user object
     */
    public function __construct($user = null) {
        if (!$user) {
            $user = get_queried_object();
        }
        parent::__construct($user);
    }
    
    /**
     * Load data - authors don't have database records
     */
    protected function load_data() {
        $this->data = array();
    }
    
    /**
     * Get SEO title for author pages
     */
    public function get_title() {
        if (!$this->object || !isset($this->object->ID)) {
            return get_bloginfo('name');
        }
        
        // Get pattern
        $pattern = $this->settings->get_title_pattern('author');
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern, $this->object);
    }
    
    /**
     * Get meta description for author pages
     */
    public function get_description() {
        if (!$this->object || !isset($this->object->ID)) {
            return '';
        }
        
        // Check for author bio
        $bio = get_the_author_meta('description', $this->object->ID);
        if (!empty($bio)) {
            return wp_strip_all_tags($bio);
        }
        
        // Use pattern
        $pattern = $this->settings->get_description_pattern('author');
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern, $this->object);
    }
    
    /**
     * Get default canonical URL
     */
    protected function get_default_canonical() {
        if (!$this->object || !isset($this->object->ID)) {
            return home_url();
        }
        
        return get_author_posts_url($this->object->ID);
    }
    
    /**
     * Override robots for author pages
     */
    public function get_robots() {
        $robots = parent::get_robots();
        
        // Check if author archives should be noindexed
        if ($this->settings->should_noindex('author')) {
            $robots['index'] = 'noindex';
        }
        
        // Check if paginated
        if (is_paged() && $this->settings->should_noindex('paginated')) {
            $robots['index'] = 'noindex';
        }
        
        return $robots;
    }
}