<?php
/**
 * VectorNode Frontend Class
 * 
 * Outputs SEO meta tags to the frontend
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class VectorNode_Frontend {
    
    private static $instance = null;
    
    /**
     * Generator instance
     */
    private $generator = null;
    
    /**
     * Meta data
     */
    private $meta = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Remove WordPress default title if we're handling it
        remove_action('wp_head', '_wp_render_title_tag', 1);
        
        // Add our meta output with high priority
        add_action('wp_head', array($this, 'output_meta_tags'), 1);
        
        // Add title filter for themes without title-tag support
        add_filter('wp_title', array($this, 'filter_wp_title'), 10, 3);
        add_filter('pre_get_document_title', array($this, 'filter_document_title'), 10);
    }
    
    /**
     * Output all meta tags
     */
    public function output_meta_tags() {
        // Check if VectorNode is enabled for this page
        if (is_singular()) {
            $post_id = get_the_ID();
            if (!VectorNode_Core::is_enabled($post_id)) {
                // Re-add WordPress title if we're not handling it
                _wp_render_title_tag();
                return;
            }
        }
        
        // Generate meta data
        $this->generator = VectorNode_Generator::get_instance();
        $this->meta = $this->generator->generate();
        
        // Output meta tags
        echo "\n<!-- VectorNode SEO Meta Start -->\n";
        
        // Title
        $this->output_title();
        
        // Description
        $this->output_description();
        
        // Robots
        $this->output_robots();
        
        // Canonical
        $this->output_canonical();
        
        // Open Graph
        $this->output_open_graph();
        
        // Twitter Card
        $this->output_twitter_card();
        
        // Keywords (optional)
        $this->output_keywords();
        
        echo "<!-- VectorNode SEO Meta End -->\n\n";
    }
    
    /**
     * Output title tag
     */
    private function output_title() {
        if (!empty($this->meta['title'])) {
            echo '<title>' . esc_html($this->meta['title']) . '</title>' . "\n";
        }
    }
    
    /**
     * Output meta description
     */
    private function output_description() {
        if (!empty($this->meta['description'])) {
            echo '<meta name="description" content="' . esc_attr($this->meta['description']) . '" />' . "\n";
        }
    }
    
    /**
     * Output robots meta
     */
    private function output_robots() {
        if (!empty($this->meta['robots'])) {
            $robots_parts = array();
            
            foreach ($this->meta['robots'] as $key => $value) {
                if ($key === 'max-snippet' || $key === 'max-video-preview' || $key === 'max-image-preview') {
                    $robots_parts[] = $key . ':' . $value;
                } else {
                    $robots_parts[] = $value;
                }
            }
            
            if (!empty($robots_parts)) {
                echo '<meta name="robots" content="' . esc_attr(implode(', ', $robots_parts)) . '" />' . "\n";
            }
        }
    }
    
    /**
     * Output canonical link
     */
    private function output_canonical() {
        if (!empty($this->meta['canonical'])) {
            echo '<link rel="canonical" href="' . esc_url($this->meta['canonical']) . '" />' . "\n";
        }
    }
    
    /**
     * Output Open Graph meta tags
     */
    private function output_open_graph() {
        echo "\n<!-- Open Graph -->\n";
        
        // Locale
        $locale = get_locale();
        echo '<meta property="og:locale" content="' . esc_attr($locale) . '" />' . "\n";
        
        // Type
        if (!empty($this->meta['og_type'])) {
            echo '<meta property="og:type" content="' . esc_attr($this->meta['og_type']) . '" />' . "\n";
        }
        
        // Title
        if (!empty($this->meta['og_title'])) {
            echo '<meta property="og:title" content="' . esc_attr($this->meta['og_title']) . '" />' . "\n";
        }
        
        // Description
        if (!empty($this->meta['og_description'])) {
            echo '<meta property="og:description" content="' . esc_attr($this->meta['og_description']) . '" />' . "\n";
        }
        
        // URL
        if (!empty($this->meta['og_url'])) {
            echo '<meta property="og:url" content="' . esc_url($this->meta['og_url']) . '" />' . "\n";
        }
        
        // Site Name
        if (!empty($this->meta['og_site_name'])) {
            echo '<meta property="og:site_name" content="' . esc_attr($this->meta['og_site_name']) . '" />' . "\n";
        }
        
        // Image
        if (!empty($this->meta['og_image'])) {
            echo '<meta property="og:image" content="' . esc_url($this->meta['og_image']) . '" />' . "\n";
            
            // Try to get image dimensions
            $image_id = attachment_url_to_postid($this->meta['og_image']);
            if ($image_id) {
                $image_meta = wp_get_attachment_metadata($image_id);
                if ($image_meta) {
                    if (!empty($image_meta['width'])) {
                        echo '<meta property="og:image:width" content="' . esc_attr($image_meta['width']) . '" />' . "\n";
                    }
                    if (!empty($image_meta['height'])) {
                        echo '<meta property="og:image:height" content="' . esc_attr($image_meta['height']) . '" />' . "\n";
                    }
                }
            }
        }
        
        // Article specific tags for posts
        if ($this->meta['og_type'] === 'article' && is_singular('post')) {
            $post = get_post();
            
            // Published time
            echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $post)) . '" />' . "\n";
            
            // Modified time
            echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $post)) . '" />' . "\n";
            
            // Author
            $author_name = get_the_author_meta('display_name', $post->post_author);
            if ($author_name) {
                echo '<meta property="article:author" content="' . esc_attr($author_name) . '" />' . "\n";
            }
        }
    }
    
    /**
     * Output Twitter Card meta tags
     */
    private function output_twitter_card() {
        echo "\n<!-- Twitter Card -->\n";
        
        // Card type
        if (!empty($this->meta['twitter_card'])) {
            echo '<meta name="twitter:card" content="' . esc_attr($this->meta['twitter_card']) . '" />' . "\n";
        }
        
        // Title
        if (!empty($this->meta['twitter_title'])) {
            echo '<meta name="twitter:title" content="' . esc_attr($this->meta['twitter_title']) . '" />' . "\n";
        }
        
        // Description
        if (!empty($this->meta['twitter_description'])) {
            echo '<meta name="twitter:description" content="' . esc_attr($this->meta['twitter_description']) . '" />' . "\n";
        }
        
        // Image
        if (!empty($this->meta['og_image'])) {
            echo '<meta name="twitter:image" content="' . esc_url($this->meta['og_image']) . '" />' . "\n";
        }
        
        // Add reading time for posts
        if (is_singular('post')) {
            $content = get_post_field('post_content', get_the_ID());
            $word_count = str_word_count(strip_tags($content));
            $reading_time = ceil($word_count / 200); // Assuming 200 words per minute
            
            echo '<meta name="twitter:label1" content="Time to read" />' . "\n";
            echo '<meta name="twitter:data1" content="' . esc_attr($reading_time . ' minute' . ($reading_time > 1 ? 's' : '')) . '" />' . "\n";
        }
    }
    
    /**
     * Output keywords meta tag (optional)
     */
    private function output_keywords() {
        if (!empty($this->meta['keywords'])) {
            echo '<meta name="keywords" content="' . esc_attr($this->meta['keywords']) . '" />' . "\n";
        }
    }
    
    /**
     * Filter wp_title for themes without title-tag support
     */
    public function filter_wp_title($title, $sep, $seplocation) {
        if (!$this->generator) {
            $this->generator = VectorNode_Generator::get_instance();
        }
        
        $seo_title = $this->generator->get_title();
        if (!empty($seo_title)) {
            return $seo_title;
        }
        
        return $title;
    }
    
    /**
     * Filter document title for themes with title-tag support
     */
    public function filter_document_title($title) {
        // Check if VectorNode is enabled for this page
        if (is_singular()) {
            $post_id = get_the_ID();
            if (!VectorNode_Core::is_enabled($post_id)) {
                return $title;
            }
        }
        
        if (!$this->generator) {
            $this->generator = VectorNode_Generator::get_instance();
        }
        
        $seo_title = $this->generator->get_title();
        if (!empty($seo_title)) {
            return $seo_title;
        }
        
        return $title;
    }
}