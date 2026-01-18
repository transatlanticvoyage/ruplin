<?php
/**
 * VectorNode Settings Class
 * 
 * Manages default patterns and settings for VectorNode SEO
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class VectorNode_Settings {
    
    private static $instance = null;
    
    private $settings = null;
    
    /**
     * Default settings
     */
    private $defaults = array(
        'title_patterns' => array(
            'post' => '%title% | %sitename%',
            'page' => '%title% | %sitename%',
            'category' => '%term% Archives | %sitename%',
            'post_tag' => 'Posts tagged "%term%" | %sitename%',
            'author' => '%name%, Author at %sitename%',
            'search' => 'Search Results for "%search_query%" | %sitename%',
            '404' => 'Page Not Found | %sitename%',
            'archive' => '%archive_title% | %sitename%',
            'date' => '%date% Archives | %sitename%'
        ),
        'description_patterns' => array(
            'post' => '%excerpt%',
            'page' => '%excerpt%',
            'category' => 'Browse our %term% articles and resources',
            'post_tag' => 'Posts tagged with %term%',
            'author' => 'Articles written by %name%',
            'search' => 'Search results for: %search_query%',
            '404' => 'The page you are looking for could not be found',
            'archive' => 'Archive of %archive_title%',
            'date' => 'Posts from %date%'
        ),
        'separator' => '|',
        'og_default_image' => '',
        'twitter_card_type' => 'summary_large_image',
        'noindex_settings' => array(
            'search' => true,
            'author' => false,
            'date' => true,
            'attachment' => true,
            'paginated' => false,
            'password_protected' => true
        ),
        'robots_defaults' => array(
            'max-snippet' => '-1',
            'max-video-preview' => '-1',
            'max-image-preview' => 'large'
        )
    );
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_settings();
    }
    
    /**
     * Load settings from database
     */
    private function load_settings() {
        $saved = get_option('vectornode_seo_settings', array());
        $this->settings = wp_parse_args($saved, $this->defaults);
    }
    
    /**
     * Get a specific setting
     */
    public function get($key, $subkey = null) {
        if ($subkey) {
            return isset($this->settings[$key][$subkey]) ? $this->settings[$key][$subkey] : null;
        }
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }
    
    /**
     * Get title pattern for a specific type
     */
    public function get_title_pattern($type) {
        $patterns = $this->get('title_patterns');
        return isset($patterns[$type]) ? $patterns[$type] : '%title% | %sitename%';
    }
    
    /**
     * Get description pattern for a specific type
     */
    public function get_description_pattern($type) {
        $patterns = $this->get('description_patterns');
        return isset($patterns[$type]) ? $patterns[$type] : '%excerpt%';
    }
    
    /**
     * Save settings
     */
    public function save($settings) {
        $this->settings = wp_parse_args($settings, $this->defaults);
        update_option('vectornode_seo_settings', $this->settings);
    }
    
    /**
     * Get default robots directives
     */
    public function get_robots_defaults() {
        $robots = array('index' => 'index', 'follow' => 'follow');
        
        // Add advanced directives
        $advanced = $this->get('robots_defaults');
        foreach ($advanced as $key => $value) {
            $robots[$key] = $value;
        }
        
        return $robots;
    }
    
    /**
     * Check if a specific page type should be noindexed
     */
    public function should_noindex($type) {
        $noindex = $this->get('noindex_settings');
        return isset($noindex[$type]) ? (bool) $noindex[$type] : false;
    }
}