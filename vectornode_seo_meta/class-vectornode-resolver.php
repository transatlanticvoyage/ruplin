<?php
/**
 * VectorNode Base Resolver Class
 * 
 * Base class for all page-type specific resolvers
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

abstract class VectorNode_Resolver {
    
    /**
     * Current object being resolved (post, term, user, etc.)
     */
    protected $object = null;
    
    /**
     * Cached data from database
     */
    protected $data = null;
    
    /**
     * Settings instance
     */
    protected $settings = null;
    
    /**
     * Constructor
     */
    public function __construct($object = null) {
        $this->object = $object;
        $this->settings = VectorNode_Settings::get_instance();
        $this->load_data();
    }
    
    /**
     * Load data from database - to be implemented by child classes
     */
    protected function load_data() {
        // Default implementation for posts
        if (is_object($this->object) && isset($this->object->ID)) {
            $this->data = VectorNode_Core::get_post_data($this->object->ID);
        }
    }
    
    /**
     * Get SEO title
     */
    abstract public function get_title();
    
    /**
     * Get meta description
     */
    abstract public function get_description();
    
    /**
     * Get robots directives
     */
    public function get_robots() {
        // Check for custom robots
        if (!empty($this->data['vectornode_robots'])) {
            $robots = json_decode($this->data['vectornode_robots'], true);
            if (is_array($robots)) {
                return $this->merge_robots($robots);
            }
        }
        
        // Return defaults
        return $this->settings->get_robots_defaults();
    }
    
    /**
     * Get canonical URL
     */
    public function get_canonical() {
        // Check for custom canonical
        if (!empty($this->data['vectornode_canonical_url'])) {
            return $this->data['vectornode_canonical_url'];
        }
        
        // Return default canonical
        return $this->get_default_canonical();
    }
    
    /**
     * Get Open Graph title
     */
    public function get_og_title() {
        // Check for custom OG title
        if (!empty($this->data['vectornode_og_title'])) {
            return $this->data['vectornode_og_title'];
        }
        
        // Fall back to regular title
        return $this->get_title();
    }
    
    /**
     * Get Open Graph description
     */
    public function get_og_description() {
        // Check for custom OG description
        if (!empty($this->data['vectornode_og_description'])) {
            return $this->data['vectornode_og_description'];
        }
        
        // Fall back to regular description
        return $this->get_description();
    }
    
    /**
     * Get Open Graph image URL
     */
    public function get_og_image() {
        // Check for custom OG image
        if (!empty($this->data['vectornode_og_image_id'])) {
            $image_url = wp_get_attachment_image_url($this->data['vectornode_og_image_id'], 'large');
            if ($image_url) {
                return $image_url;
            }
        }
        
        // Try to get featured image for posts
        if (is_object($this->object) && isset($this->object->ID)) {
            $featured_image_id = get_post_thumbnail_id($this->object->ID);
            if ($featured_image_id) {
                $image_url = wp_get_attachment_image_url($featured_image_id, 'large');
                if ($image_url) {
                    return $image_url;
                }
            }
        }
        
        // Return default OG image from settings
        return $this->settings->get('og_default_image');
    }
    
    /**
     * Get Twitter title
     */
    public function get_twitter_title() {
        // Check for custom Twitter title
        if (!empty($this->data['vectornode_twitter_title'])) {
            return $this->data['vectornode_twitter_title'];
        }
        
        // Fall back to OG title
        return $this->get_og_title();
    }
    
    /**
     * Get Twitter description
     */
    public function get_twitter_description() {
        // Check for custom Twitter description
        if (!empty($this->data['vectornode_twitter_description'])) {
            return $this->data['vectornode_twitter_description'];
        }
        
        // Fall back to OG description
        return $this->get_og_description();
    }
    
    /**
     * Get focus keywords
     */
    public function get_keywords() {
        return !empty($this->data['vectornode_focus_keywords']) ? $this->data['vectornode_focus_keywords'] : '';
    }
    
    /**
     * Get schema type
     */
    public function get_schema_type() {
        return !empty($this->data['vectornode_schema_type']) ? $this->data['vectornode_schema_type'] : 'article';
    }
    
    /**
     * Get breadcrumb title
     */
    public function get_breadcrumb_title() {
        if (!empty($this->data['vectornode_breadcrumb_title'])) {
            return $this->data['vectornode_breadcrumb_title'];
        }
        
        // Fall back to post title
        if (is_object($this->object) && isset($this->object->post_title)) {
            return $this->object->post_title;
        }
        
        return '';
    }
    
    /**
     * Get default canonical URL - to be implemented by child classes
     */
    abstract protected function get_default_canonical();
    
    /**
     * Merge robots directives with defaults
     */
    protected function merge_robots($custom_robots) {
        $defaults = $this->settings->get_robots_defaults();
        return array_merge($defaults, $custom_robots);
    }
    
    /**
     * Auto-generate description from content
     */
    protected function auto_generate_description($content, $length = 160) {
        if (empty($content)) {
            return '';
        }
        
        // Strip shortcodes
        $content = strip_shortcodes($content);
        
        // Strip HTML tags
        $content = wp_strip_all_tags($content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Trim to length
        if (strlen($content) > $length) {
            $content = substr($content, 0, $length);
            // Cut at last word boundary
            $content = substr($content, 0, strrpos($content, ' '));
            $content .= '...';
        }
        
        return trim($content);
    }
}