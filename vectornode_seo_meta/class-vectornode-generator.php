<?php
/**
 * VectorNode Generator Class
 * 
 * Main generator that determines page type and uses appropriate resolver
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class VectorNode_Generator {
    
    private static $instance = null;
    
    /**
     * Current resolver instance
     */
    private $resolver = null;
    
    /**
     * Cached meta data
     */
    private $meta_data = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->determine_resolver();
    }
    
    /**
     * Determine which resolver to use based on current page
     */
    private function determine_resolver() {
        // 404 pages
        if (is_404()) {
            $this->resolver = new Resolver_404();
        }
        // Search results
        elseif (is_search()) {
            $this->resolver = new Resolver_Search();
        }
        // Author archives
        elseif (is_author()) {
            $this->resolver = new Resolver_Author();
        }
        // Category, tag, or custom taxonomy
        elseif (is_category() || is_tag() || is_tax()) {
            $this->resolver = new Resolver_Archive();
        }
        // Single post, page, or custom post type
        elseif (is_singular()) {
            $post = get_queried_object();
            $this->resolver = new Resolver_Singular($post);
        }
        // Home page (posts page)
        elseif (is_home()) {
            // If static page is set as posts page
            $page_for_posts = get_option('page_for_posts');
            if ($page_for_posts) {
                $post = get_post($page_for_posts);
                $this->resolver = new Resolver_Singular($post);
            } else {
                // Default blog archive
                $this->resolver = new Resolver_Archive();
            }
        }
        // Front page
        elseif (is_front_page()) {
            if (is_page()) {
                $post = get_queried_object();
                $this->resolver = new Resolver_Singular($post);
            } else {
                $this->resolver = new Resolver_Archive();
            }
        }
        // Date archives
        elseif (is_date()) {
            $this->resolver = new Resolver_Archive();
        }
        // Default fallback
        else {
            $this->resolver = new Resolver_Archive();
        }
    }
    
    /**
     * Generate all meta data
     */
    public function generate() {
        if ($this->meta_data !== null) {
            return $this->meta_data;
        }
        
        $this->meta_data = array(
            'title' => $this->get_title(),
            'description' => $this->get_description(),
            'robots' => $this->get_robots(),
            'canonical' => $this->get_canonical(),
            'og_title' => $this->get_og_title(),
            'og_description' => $this->get_og_description(),
            'og_image' => $this->get_og_image(),
            'og_type' => $this->get_og_type(),
            'og_url' => $this->get_og_url(),
            'og_site_name' => $this->get_og_site_name(),
            'twitter_title' => $this->get_twitter_title(),
            'twitter_description' => $this->get_twitter_description(),
            'twitter_card' => $this->get_twitter_card(),
            'keywords' => $this->get_keywords()
        );
        
        return $this->meta_data;
    }
    
    /**
     * Get title
     */
    public function get_title() {
        if (!$this->resolver) {
            return get_bloginfo('name');
        }
        return $this->resolver->get_title();
    }
    
    /**
     * Get description
     */
    public function get_description() {
        if (!$this->resolver) {
            return '';
        }
        return $this->resolver->get_description();
    }
    
    /**
     * Get robots directives
     */
    public function get_robots() {
        if (!$this->resolver) {
            return array('index' => 'index', 'follow' => 'follow');
        }
        return $this->resolver->get_robots();
    }
    
    /**
     * Get canonical URL
     */
    public function get_canonical() {
        if (!$this->resolver) {
            return home_url();
        }
        return $this->resolver->get_canonical();
    }
    
    /**
     * Get Open Graph title
     */
    public function get_og_title() {
        if (!$this->resolver) {
            return get_bloginfo('name');
        }
        return $this->resolver->get_og_title();
    }
    
    /**
     * Get Open Graph description
     */
    public function get_og_description() {
        if (!$this->resolver) {
            return '';
        }
        return $this->resolver->get_og_description();
    }
    
    /**
     * Get Open Graph image
     */
    public function get_og_image() {
        if (!$this->resolver) {
            return '';
        }
        return $this->resolver->get_og_image();
    }
    
    /**
     * Get Open Graph type
     */
    public function get_og_type() {
        if (is_singular()) {
            $post_type = get_post_type();
            if ($post_type === 'post') {
                return 'article';
            }
            return 'website';
        }
        return 'website';
    }
    
    /**
     * Get Open Graph URL
     */
    public function get_og_url() {
        return $this->get_canonical();
    }
    
    /**
     * Get Open Graph site name
     */
    public function get_og_site_name() {
        return get_bloginfo('name');
    }
    
    /**
     * Get Twitter title
     */
    public function get_twitter_title() {
        if (!$this->resolver) {
            return get_bloginfo('name');
        }
        return $this->resolver->get_twitter_title();
    }
    
    /**
     * Get Twitter description
     */
    public function get_twitter_description() {
        if (!$this->resolver) {
            return '';
        }
        return $this->resolver->get_twitter_description();
    }
    
    /**
     * Get Twitter card type
     */
    public function get_twitter_card() {
        $settings = VectorNode_Settings::get_instance();
        return $settings->get('twitter_card_type', 'summary_large_image');
    }
    
    /**
     * Get keywords
     */
    public function get_keywords() {
        if (!$this->resolver) {
            return '';
        }
        return $this->resolver->get_keywords();
    }
}