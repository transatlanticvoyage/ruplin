<?php
/**
 * VectorNode Template Variables Class
 * 
 * Handles replacement of template variables like %title%, %sitename%, etc.
 * 
 * @package Ruplin/VectorNode
 * @since 1.0.0
 */

namespace Ruplin\VectorNode;

if (!defined('ABSPATH')) {
    exit;
}

class VectorNode_Template_Vars {
    
    private static $instance = null;
    
    /**
     * Current context object (post, term, user, etc.)
     */
    private $context = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Replace all variables in a string
     */
    public function replace($string, $context = null) {
        $this->context = $context;
        
        // Get all variables
        $replacements = $this->get_replacements();
        
        // Replace each variable
        foreach ($replacements as $var => $value) {
            $string = str_replace('%' . $var . '%', $value, $string);
        }
        
        // Clean up any remaining variables
        $string = preg_replace('/%[^%]+%/', '', $string);
        
        // Clean up extra spaces
        $string = preg_replace('/\s+/', ' ', $string);
        
        return trim($string);
    }
    
    /**
     * Get all variable replacements
     */
    private function get_replacements() {
        $replacements = array(
            'sitename' => $this->get_sitename(),
            'tagline' => $this->get_tagline(),
            'sep' => $this->get_separator(),
            'currentdate' => $this->get_current_date(),
            'currentyear' => $this->get_current_year(),
            'currentmonth' => $this->get_current_month(),
            'currentday' => $this->get_current_day(),
            'search_query' => $this->get_search_query(),
            'page' => $this->get_page_number()
        );
        
        // Add context-specific replacements
        if ($this->context) {
            $replacements = array_merge($replacements, $this->get_context_replacements());
        }
        
        return $replacements;
    }
    
    /**
     * Get context-specific replacements
     */
    private function get_context_replacements() {
        $replacements = array();
        
        // Post/Page context
        if (is_a($this->context, 'WP_Post')) {
            $replacements['title'] = $this->context->post_title;
            $replacements['excerpt'] = $this->get_post_excerpt();
            $replacements['category'] = $this->get_primary_category();
            $replacements['tag'] = $this->get_primary_tag();
            $replacements['author'] = $this->get_post_author();
            $replacements['modified'] = $this->get_modified_date();
            $replacements['date'] = $this->get_post_date();
        }
        
        // Term context (category, tag, etc.)
        elseif (is_a($this->context, 'WP_Term')) {
            $replacements['term'] = $this->context->name;
            $replacements['term_description'] = $this->context->description;
            $replacements['term_title'] = $this->context->name;
        }
        
        // User context (author pages)
        elseif (is_a($this->context, 'WP_User')) {
            $replacements['name'] = $this->context->display_name;
            $replacements['author'] = $this->context->display_name;
            $replacements['username'] = $this->context->user_login;
        }
        
        return $replacements;
    }
    
    /**
     * Get site name
     */
    private function get_sitename() {
        return get_bloginfo('name');
    }
    
    /**
     * Get site tagline
     */
    private function get_tagline() {
        return get_bloginfo('description');
    }
    
    /**
     * Get separator
     */
    private function get_separator() {
        $settings = VectorNode_Settings::get_instance();
        return $settings->get('separator', '|');
    }
    
    /**
     * Get current date
     */
    private function get_current_date() {
        return date_i18n(get_option('date_format'));
    }
    
    /**
     * Get current year
     */
    private function get_current_year() {
        return date('Y');
    }
    
    /**
     * Get current month
     */
    private function get_current_month() {
        return date_i18n('F');
    }
    
    /**
     * Get current day
     */
    private function get_current_day() {
        return date('j');
    }
    
    /**
     * Get search query
     */
    private function get_search_query() {
        return get_search_query();
    }
    
    /**
     * Get page number for paginated pages
     */
    private function get_page_number() {
        $page = get_query_var('paged');
        if (!$page) {
            $page = get_query_var('page');
        }
        return $page > 1 ? sprintf(__('Page %d', 'vectornode'), $page) : '';
    }
    
    /**
     * Get post excerpt
     */
    private function get_post_excerpt() {
        if (!empty($this->context->post_excerpt)) {
            return wp_strip_all_tags($this->context->post_excerpt);
        }
        
        // Auto-generate from content
        $content = $this->context->post_content;
        $content = strip_shortcodes($content);
        $content = wp_strip_all_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);
        
        if (strlen($content) > 160) {
            $content = substr($content, 0, 160);
            $content = substr($content, 0, strrpos($content, ' '));
            $content .= '...';
        }
        
        return trim($content);
    }
    
    /**
     * Get primary category
     */
    private function get_primary_category() {
        $categories = get_the_category($this->context->ID);
        if (!empty($categories)) {
            return $categories[0]->name;
        }
        return '';
    }
    
    /**
     * Get primary tag
     */
    private function get_primary_tag() {
        $tags = get_the_tags($this->context->ID);
        if (!empty($tags)) {
            $tag = array_values($tags)[0];
            return $tag->name;
        }
        return '';
    }
    
    /**
     * Get post author name
     */
    private function get_post_author() {
        return get_the_author_meta('display_name', $this->context->post_author);
    }
    
    /**
     * Get post modified date
     */
    private function get_modified_date() {
        return get_the_modified_date(get_option('date_format'), $this->context);
    }
    
    /**
     * Get post publish date
     */
    private function get_post_date() {
        return get_the_date(get_option('date_format'), $this->context);
    }
}