<?php
/**
 * VectorNode Singular Resolver Class
 * 
 * Handles SEO data for posts, pages, and custom post types
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class Resolver_Singular extends VectorNode_Resolver {
    
    /**
     * Get SEO title for singular pages
     */
    public function get_title() {
        // Check for custom title
        if (!empty($this->data['vectornode_meta_title'])) {
            return $this->data['vectornode_meta_title'];
        }
        
        // Get pattern based on post type
        $post_type = get_post_type($this->object);
        $pattern = $this->settings->get_title_pattern($post_type);
        
        // Replace variables
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern, $this->object);
    }
    
    /**
     * Get meta description for singular pages
     */
    public function get_description() {
        // Check for custom description
        if (!empty($this->data['vectornode_meta_description'])) {
            return $this->data['vectornode_meta_description'];
        }
        
        // Try post excerpt
        if (!empty($this->object->post_excerpt)) {
            return wp_strip_all_tags($this->object->post_excerpt);
        }
        
        // Try to use pattern
        $post_type = get_post_type($this->object);
        $pattern = $this->settings->get_description_pattern($post_type);
        
        // If pattern is %excerpt%, generate from content
        if ($pattern === '%excerpt%') {
            return $this->auto_generate_description($this->object->post_content);
        }
        
        // Replace variables in pattern
        $vars = VectorNode_Template_Vars::get_instance();
        return $vars->replace($pattern, $this->object);
    }
    
    /**
     * Get default canonical URL
     */
    protected function get_default_canonical() {
        return get_permalink($this->object);
    }
    
    /**
     * Override robots for specific conditions
     */
    public function get_robots() {
        $robots = parent::get_robots();
        
        // Check if password protected
        if (!empty($this->object->post_password) && $this->settings->should_noindex('password_protected')) {
            $robots['index'] = 'noindex';
        }
        
        // Check if attachment
        if ($this->object->post_type === 'attachment' && $this->settings->should_noindex('attachment')) {
            $robots['index'] = 'noindex';
        }
        
        // Check if paginated
        if (get_query_var('page') > 1 && $this->settings->should_noindex('paginated')) {
            $robots['index'] = 'noindex';
        }
        
        return $robots;
    }
}