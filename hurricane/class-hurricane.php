<?php

/**
 * Hurricane Feature for Snefuruplin
 * Adds Hurricane interface element to post/page edit screens
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include Blueshift and Cobalt classes
require_once plugin_dir_path(__FILE__) . 'class-blueshift.php';
require_once plugin_dir_path(__FILE__) . 'class-cobalt.php';
require_once plugin_dir_path(__FILE__) . 'class-titanium.php';

class Snefuru_Hurricane {
    
    private $blueshift;
    private $cobalt;
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_hurricane_metabox'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_hurricane_assets'));
        add_action('edit_form_top', array($this, 'add_stellar_chamber'));
        add_action('wp_ajax_refresh_redshift_data', array($this, 'ajax_refresh_redshift_data'));
        add_action('wp_ajax_refresh_blueshift_data', array($this, 'ajax_refresh_blueshift_data'));
        add_action('wp_ajax_update_format4_filtered', array($this, 'ajax_update_format4_filtered'));
        add_action('wp_ajax_save_blueshift_all_options', array($this, 'ajax_save_blueshift_all_options'));
        add_action('wp_ajax_cobalt_inject_content', array($this, 'ajax_cobalt_inject_content'));
        add_action('wp_ajax_titanium_inject_content', array($this, 'ajax_titanium_inject_content'));
        add_action('wp_ajax_rollback_revision', array($this, 'ajax_rollback_revision'));
        add_action('wp_ajax_save_blueshift_separator_count', array($this, 'ajax_save_blueshift_separator_count'));
        add_action('wp_ajax_save_stellar_default_tab', array($this, 'ajax_save_stellar_default_tab'));
        add_action('wp_ajax_save_thunder_papyrus_data', array($this, 'ajax_save_thunder_papyrus_data'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Initialize Blueshift, Cobalt, and Titanium
        $this->blueshift = new Snefuru_Blueshift();
        $this->cobalt = new Snefuru_Cobalt();
        $this->titanium = new Snefuru_Titanium();
    }
    
    /**
     * AJAX handler for Blueshift data refresh
     */
    public function ajax_refresh_blueshift_data() {
        $this->blueshift->ajax_refresh_blueshift_data();
    }
    
    /**
     * AJAX handler to update filtered format 4 content
     */
    public function ajax_update_format4_filtered() {
        $this->blueshift->ajax_update_format4_filtered();
    }
    
    /**
     * AJAX handler for Cobalt content injection
     */
    public function ajax_cobalt_inject_content() {
        $this->cobalt->ajax_cobalt_inject_content();
    }
    
    /**
     * AJAX handler for Titanium content injection
     */
    public function ajax_titanium_inject_content() {
        $this->titanium->ajax_titanium_inject_content();
    }
    
    /**
     * AJAX handler for rolling back to previous revision
     */
    public function ajax_rollback_revision() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Check if user can edit this post
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error('You do not have permission to edit this post');
        }
        
        // Get the latest revision
        $revisions = wp_get_post_revisions($post_id, array(
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if (empty($revisions)) {
            wp_send_json_error('No revisions found for this post');
        }
        
        // Get the first (latest) revision
        $latest_revision = reset($revisions);
        
        // Restore the revision
        $restore_result = wp_restore_post_revision($latest_revision->ID);
        
        if ($restore_result) {
            // Clear Elementor cache if it exists
            if (class_exists('\Elementor\Plugin')) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }
            
            wp_send_json_success('Successfully rolled back to previous version (Revision ID: ' . $latest_revision->ID . ')');
        } else {
            wp_send_json_error('Failed to restore revision');
        }
    }
    
    /**
     * AJAX handler for saving all Blueshift options
     */
    public function ajax_save_blueshift_all_options() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hurricane_save_blueshift_options')) {
            wp_send_json_error('Security check failed');
        }
        
        // Save separator count
        if (isset($_POST['separator_count'])) {
            $count = intval($_POST['separator_count']);
            if ($count < 1) $count = 1;
            if ($count > 200) $count = 200;
            update_option('ruplin_blueshift_separator_character_count', $count);
        }
        
        // Save show_exclude_from_blueshift option
        $show_exclude_from_blueshift = isset($_POST['show_exclude_from_blueshift']) && $_POST['show_exclude_from_blueshift'] === 'true';
        update_option('ruplin_blueshift_show_exclude_from_blueshift', $show_exclude_from_blueshift);
        
        // Save show_guarded option
        $show_guarded = isset($_POST['show_guarded']) && $_POST['show_guarded'] === 'true';
        update_option('ruplin_blueshift_show_guarded', $show_guarded);
        
        // Save render_p_tags option
        $render_p_tags = isset($_POST['render_p_tags']) && $_POST['render_p_tags'] === 'true';
        update_option('ruplin_blueshift_render_p_tags', $render_p_tags);
        
        // Return success response
        wp_send_json_success('Options saved successfully');
    }
    
    /**
     * AJAX handler for saving Blueshift separator character count
     */
    public function ajax_save_blueshift_separator_count() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ruplin_ajax_nonce')) {
            wp_die('Security check failed');
        }
        
        // Get the count value
        $count = isset($_POST['count']) ? intval($_POST['count']) : 95;
        
        // Validate the count (minimum 10, maximum 500)
        if ($count < 10) {
            $count = 10;
        } elseif ($count > 500) {
            $count = 500;
        }
        
        // Save to database using WordPress options
        update_option('ruplin_blueshift_separator_character_count', $count);
        
        // Return success response
        wp_send_json_success(array(
            'message' => 'Separator count saved successfully',
            'count' => $count
        ));
    }
    
    /**
     * Extract frontend text content from Elementor data
     * Parses _elementor_data JSON to get clean text content with widget separation
     */
    private function extract_elementor_frontend_text($post_id) {
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return 'No Elementor data found for this page.';
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            return 'Could not parse Elementor data.';
        }
        
        $extracted_content = array();
        
        // Process each top-level element (sections/containers)
        foreach ($elements as $element) {
            $content = $this->process_elementor_element($element);
            if (!empty($content)) {
                $extracted_content[] = $content;
            }
        }
        
        // Join content with separator bars
        return implode("\n\n———————————————————————\n\n", $extracted_content);
    }
    
    /**
     * Recursively process Elementor elements to extract text content
     */
    private function process_elementor_element($element) {
        if (!is_array($element) || empty($element['elType'])) {
            return '';
        }
        
        $content_parts = array();
        
        // Extract content based on element type
        $widget_content = $this->extract_widget_text($element);
        if (!empty($widget_content)) {
            $content_parts[] = $widget_content;
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_element) {
                $child_content = $this->process_elementor_element($child_element);
                if (!empty($child_content)) {
                    $content_parts[] = $child_content;
                }
            }
        }
        
        return implode("\n", array_filter($content_parts));
    }
    
    /**
     * Extract text content from specific widget types
     */
    private function extract_widget_text($element) {
        if (empty($element['widgetType']) && empty($element['elType'])) {
            return '';
        }
        
        $widget_type = !empty($element['widgetType']) ? $element['widgetType'] : $element['elType'];
        $settings = !empty($element['settings']) ? $element['settings'] : array();
        
        $text_content = '';
        
        switch ($widget_type) {
            case 'heading':
                if (!empty($settings['title'])) {
                    $text_content = strip_tags($settings['title']);
                }
                break;
                
            case 'text-editor':
                if (!empty($settings['editor'])) {
                    $text_content = wp_strip_all_tags($settings['editor']);
                }
                break;
                
            case 'button':
                if (!empty($settings['text'])) {
                    $text_content = strip_tags($settings['text']);
                }
                break;
                
            case 'image':
                $parts = array();
                if (!empty($settings['caption'])) {
                    $parts[] = 'Caption: ' . strip_tags($settings['caption']);
                }
                if (!empty($settings['alt']) && empty($settings['caption'])) {
                    $parts[] = 'Alt: ' . strip_tags($settings['alt']);
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'testimonial':
                $parts = array();
                if (!empty($settings['testimonial_content'])) {
                    $parts[] = wp_strip_all_tags($settings['testimonial_content']);
                }
                if (!empty($settings['testimonial_name'])) {
                    $parts[] = '— ' . strip_tags($settings['testimonial_name']);
                }
                if (!empty($settings['testimonial_job'])) {
                    $parts[] = strip_tags($settings['testimonial_job']);
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'icon-box':
            case 'image-box':
                $parts = array();
                if (!empty($settings['title_text'])) {
                    $parts[] = strip_tags($settings['title_text']);
                }
                if (!empty($settings['description_text'])) {
                    $parts[] = wp_strip_all_tags($settings['description_text']);
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'accordion':
            case 'toggle':
                $parts = array();
                if (!empty($settings['tabs']) && is_array($settings['tabs'])) {
                    foreach ($settings['tabs'] as $tab) {
                        if (!empty($tab['tab_title'])) {
                            $parts[] = strip_tags($tab['tab_title']);
                        }
                        if (!empty($tab['tab_content'])) {
                            $parts[] = wp_strip_all_tags($tab['tab_content']);
                        }
                    }
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'tabs':
                $parts = array();
                if (!empty($settings['tabs']) && is_array($settings['tabs'])) {
                    foreach ($settings['tabs'] as $tab) {
                        if (!empty($tab['tab_title'])) {
                            $parts[] = 'Tab: ' . strip_tags($tab['tab_title']);
                        }
                        if (!empty($tab['tab_content'])) {
                            $parts[] = wp_strip_all_tags($tab['tab_content']);
                        }
                    }
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'html':
                if (!empty($settings['html'])) {
                    $text_content = wp_strip_all_tags($settings['html']);
                }
                break;
                
            case 'shortcode':
                if (!empty($settings['shortcode'])) {
                    // Process shortcode and extract text
                    $shortcode_output = do_shortcode($settings['shortcode']);
                    $text_content = wp_strip_all_tags($shortcode_output);
                }
                break;
                
            case 'spacer':
            case 'divider':
                // Skip spacers and dividers
                return '';
                
            default:
                // For unknown widgets, try to extract any text-like settings
                $text_fields = array('title', 'text', 'content', 'description', 'caption');
                $parts = array();
                foreach ($text_fields as $field) {
                    if (!empty($settings[$field])) {
                        $parts[] = wp_strip_all_tags($settings[$field]);
                    }
                }
                $text_content = implode("\n", $parts);
                break;
        }
        
        // Clean up whitespace
        if (!empty($text_content)) {
            $text_content = preg_replace('/\s+/', ' ', $text_content);
            $text_content = trim($text_content);
        }
        
        return $text_content;
    }
    
    /**
     * Generate base reference content showing widget mappings
     * This shows the reference IDs (==widget1, ==widget2, etc.) that can be used
     * in Cobalt/Titanium functions to target specific widgets
     */
    private function generate_base_reference_content($post_id) {
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        if (empty($elementor_data)) {
            return "No Elementor data found for this page";
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            // Try stripslashes if initial decode fails
            $elements = json_decode(stripslashes($elementor_data), true);
            if (!$elements || !is_array($elements)) {
                return "Could not parse Elementor data";
            }
        }
        
        // Build the reference mapping - just the widget list
        $output = "";
        $widget_counter = 1;
        
        // Process each top-level element
        foreach ($elements as $element) {
            $this->process_element_for_reference($element, $widget_counter, $output);
        }
        
        return rtrim($output); // Remove trailing newline
    }
    
    /**
     * Process Elementor element recursively to build reference mapping
     */
    private function process_element_for_reference($element, &$widget_counter, &$output) {
        // Check if this is a widget
        if (isset($element['elType']) && $element['elType'] === 'widget') {
            $widget_type = isset($element['widgetType']) ? $element['widgetType'] : 'unknown';
            $widget_ref = "==widget" . $widget_counter;
            
            // Get custom CSS classes
            $custom_classes = '';
            if (!empty($element['settings']['_css_classes'])) {
                $classes = trim($element['settings']['_css_classes']);
                if ($classes) {
                    // Format classes with dots
                    $class_array = explode(' ', $classes);
                    $formatted_classes = array_map(function($class) {
                        return '.' . trim($class);
                    }, $class_array);
                    $custom_classes = ' → ' . implode(' ', $formatted_classes);
                }
            }
            
            // Get current content preview
            $content_preview = $this->get_widget_content_preview($element);
            
            $output .= $widget_ref . " → " . $widget_type . " widget" . $custom_classes . "\n";
            if (!empty($content_preview)) {
                $output .= $content_preview . "\n";
            }
            
            // Handle list-type widgets with items - NO TRUNCATION
            if ($widget_type === 'icon-list' && !empty($element['settings']['icon_list'])) {
                $item_counter = 1;
                foreach ($element['settings']['icon_list'] as $item) {
                    if (!empty($item['text'])) {
                        $output .= "  ==item" . $item_counter . " → " . $item['text'] . "\n";
                        $item_counter++;
                    }
                }
            } elseif (in_array($widget_type, ['tabs', 'accordion', 'toggle']) && !empty($element['settings']['tabs'])) {
                $item_counter = 1;
                foreach ($element['settings']['tabs'] as $tab) {
                    if (!empty($tab['tab_title'])) {
                        $output .= "  ==item" . $item_counter . " → Tab: " . $tab['tab_title'] . "\n";
                        if (!empty($tab['tab_content'])) {
                            $output .= "    Content: " . $tab['tab_content'] . "\n";
                        }
                        $item_counter++;
                    }
                }
            }
            
            $output .= "\n";
            $widget_counter++;
        }
        
        // Process nested elements (sections, columns)
        if (!empty($element['elements'])) {
            foreach ($element['elements'] as $nested) {
                $this->process_element_for_reference($nested, $widget_counter, $output);
            }
        }
    }
    
    /**
     * Get full widget content for reference display - NO TRUNCATION
     */
    private function get_widget_content_preview($element) {
        if (!isset($element['settings'])) {
            return '';
        }
        
        $settings = $element['settings'];
        $widget_type = isset($element['widgetType']) ? $element['widgetType'] : '';
        
        // Extract FULL content based on widget type - NO TRUNCATION
        switch ($widget_type) {
            case 'heading':
                return !empty($settings['title']) ? $settings['title'] : '';
            
            case 'text-editor':
                return !empty($settings['editor']) ? $settings['editor'] : '';
            
            case 'button':
                return !empty($settings['text']) ? $settings['text'] : '';
            
            case 'image':
                if (!empty($settings['caption'])) {
                    return "Caption: " . $settings['caption'];
                }
                return !empty($settings['url']) ? "[Image]" : '';
            
            case 'testimonial':
                return !empty($settings['testimonial_content']) ? $settings['testimonial_content'] : '';
            
            case 'icon-box':
                return !empty($settings['title_text']) ? $settings['title_text'] : '';
            
            case 'html':
                return !empty($settings['html']) ? $settings['html'] : '';
            
            case 'shortcode':
                return !empty($settings['shortcode']) ? "[Shortcode: " . $settings['shortcode'] . "]" : '';
            
            default:
                // Check common fields - return FULL content
                $fields = ['title', 'text', 'content', 'description'];
                foreach ($fields as $field) {
                    if (!empty($settings[$field])) {
                        $text = is_string($settings[$field]) ? $settings[$field] : '';
                        return $text; // Return full text, no truncation
                    }
                }
                return '';
        }
    }
    
    /**
     * Get the total widget count for a post
     */
    private function get_widget_count($post_id) {
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        if (empty($elementor_data)) {
            return "Total widgets found: 0";
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            // Try stripslashes if initial decode fails
            $elements = json_decode(stripslashes($elementor_data), true);
            if (!$elements || !is_array($elements)) {
                return "Total widgets found: 0";
            }
        }
        
        // Count widgets
        $widget_count = 0;
        foreach ($elements as $element) {
            $this->count_widgets_recursive($element, $widget_count);
        }
        
        return "Total widgets found: " . $widget_count;
    }
    
    /**
     * Recursively count widgets in Elementor structure
     */
    private function count_widgets_recursive($element, &$count) {
        // Check if this is a widget
        if (isset($element['elType']) && $element['elType'] === 'widget') {
            $count++;
        }
        
        // Process nested elements
        if (!empty($element['elements'])) {
            foreach ($element['elements'] as $nested) {
                $this->count_widgets_recursive($nested, $count);
            }
        }
    }
    
    /**
     * AJAX handler to refresh redshift data
     */
    public function ajax_refresh_redshift_data() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Extract fresh data
        $frontend_text_content = $this->extract_elementor_frontend_text($post_id);
        
        // Limit length for display if too long
        if (strlen($frontend_text_content) > 10000) {
            $frontend_text_content = substr($frontend_text_content, 0, 10000) . "\n\n... [Content truncated at 10,000 characters]";
        }
        
        wp_send_json_success(array('content' => $frontend_text_content));
    }
    
    /**
     * Add Hurricane Chamber element above the title bar
     */
    public function add_stellar_chamber($post) {
        // Only show on post and page edit screens
        if (!in_array($post->post_type, array('post', 'page'))) {
            return;
        }
        ?>
        <div class="snefuru-stellar-chamber">
            <div class="snefuru-stellar-chamber-header" style="display: flex; align-items: center; justify-content: flex-start;">
                <div style="display: flex; flex-direction: column;">
                    <div class="beamray_banner1">
                        <svg class="stellar-galactic-logo" viewBox="0 0 24 24" fill="currentColor" style="width: 8px; height: 8px;">
                            <circle cx="12" cy="12" r="10" stroke="white" stroke-width="0.5" fill="none"/>
                            <circle cx="12" cy="12" r="6" stroke="white" stroke-width="0.5" fill="none"/>
                            <circle cx="12" cy="12" r="2" fill="white"/>
                            <g stroke="white" stroke-width="0.3" fill="none">
                                <path d="M2,12 Q6,8 12,12 Q18,16 22,12"/>
                                <path d="M12,2 Q8,6 12,12 Q16,18 12,22"/>
                                <path d="M4.9,4.9 Q8.5,8.5 12,12 Q15.5,15.5 19.1,19.1"/>
                                <path d="M19.1,4.9 Q15.5,8.5 12,12 Q8.5,15.5 4.9,19.1"/>
                            </g>
                            <circle cx="6" cy="8" r="0.8" fill="white" opacity="0.8"/>
                            <circle cx="18" cy="6" r="0.6" fill="white" opacity="0.6"/>
                            <circle cx="16" cy="16" r="0.7" fill="white" opacity="0.7"/>
                            <circle cx="8" cy="18" r="0.5" fill="white" opacity="0.5"/>
                        </svg>
                        Hurricane Chamber
                    </div>
                    <span style="color: white; font-size: 16px; font-weight: bold;">top_bar_area</span>
                </div>
                <svg class="snefuru-rocket-icon" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 100 100" style="display: inline-block; margin-left: 10px; transform: rotate(-30deg) scaleX(-1);">
                    <!-- Rocket body -->
                    <path d="M50 10 Q60 20 60 40 L60 55 Q50 65 40 55 L40 40 Q40 20 50 10" fill="white" stroke="#333" stroke-width="2"/>
                    <!-- Rocket tip -->
                    <path d="M50 10 Q55 5 50 0 Q45 5 50 10" fill="#333"/>
                    <!-- Rocket window -->
                    <circle cx="50" cy="30" r="6" fill="#87ceeb" stroke="#333" stroke-width="1"/>
                    <!-- Rocket fins -->
                    <path d="M40 45 L30 55 L35 50 L40 50 Z" fill="#666" stroke="#333" stroke-width="1"/>
                    <path d="M60 45 L70 55 L65 50 L60 50 Z" fill="#666" stroke="#333" stroke-width="1"/>
                    <!-- Fire/flames -->
                    <g class="rocket-flames">
                        <path d="M45 55 Q42 65 43 70 Q45 68 47 70 Q48 65 45 55" fill="#ff9500" opacity="0.9">
                            <animate attributeName="d" 
                                values="M45 55 Q42 65 43 70 Q45 68 47 70 Q48 65 45 55;
                                        M45 55 Q41 66 44 72 Q46 69 48 72 Q49 66 45 55;
                                        M45 55 Q42 65 43 70 Q45 68 47 70 Q48 65 45 55"
                                dur="0.3s" repeatCount="indefinite"/>
                        </path>
                        <path d="M50 55 Q48 68 50 75 Q52 68 50 55" fill="#ff4500" opacity="0.8">
                            <animate attributeName="d" 
                                values="M50 55 Q48 68 50 75 Q52 68 50 55;
                                        M50 55 Q47 70 50 78 Q53 70 50 55;
                                        M50 55 Q48 68 50 75 Q52 68 50 55"
                                dur="0.4s" repeatCount="indefinite"/>
                        </path>
                        <path d="M55 55 Q58 65 57 70 Q55 68 53 70 Q52 65 55 55" fill="#ff9500" opacity="0.9">
                            <animate attributeName="d" 
                                values="M55 55 Q58 65 57 70 Q55 68 53 70 Q52 65 55 55;
                                        M55 55 Q59 66 56 72 Q54 69 52 72 Q51 66 55 55;
                                        M55 55 Q58 65 57 70 Q55 68 53 70 Q52 65 55 55"
                                dur="0.35s" repeatCount="indefinite"/>
                        </path>
                    </g>
                </svg>
                
                <!-- Save Default Tab Container -->
                <div style="display: flex; flex-direction: column; margin-left: 20px;">
                    <!-- Save Current Tab As Default Button -->
                    <button type="button" 
                            id="stellar-save-default-tab"
                            style="background: #2271b1; color: white; border: none; padding: 4px 8px; margin-top: 5px; border-radius: 3px; cursor: pointer; font-size: 12px; font-weight: normal; width: fit-content;"
                            title="Save the currently active tab as the default tab">
                        Save Current Tab As Default
                    </button>
                    <!-- Save Current Expand State Button -->
                    <button type="button" 
                            id="stellar-save-expand-state"
                            style="background: #2271b1; color: white; border: none; padding: 4px 8px; margin-top: 5px; border-radius: 3px; cursor: pointer; font-size: 12px; font-weight: normal; width: fit-content;"
                            title="Save the current expand/collapse state as default">
                        Save Current Expand State
                    </button>
                    <div id="stellar-save-tab-message" style="margin-top: 5px; display: none; color: white; font-size: 11px;"></div>
                </div>
                
                <?php
                // Get the sitespren_base domain from the single-row zen_sitespren table
                global $wpdb;
                $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren LIMIT 1");
                if (empty($sitespren_base)) {
                    $sitespren_base = 'example.com'; // fallback if no data found
                }
                $drom_url = 'http://localhost:3000/drom?activefilterchamber=daylight&sitesentered=' . urlencode($sitespren_base);
                $sitejar4_url = 'http://localhost:3000/sitejar4?sitesentered=' . urlencode($sitespren_base);
                ?>
                
                <!-- Driggsman Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 15px;">
                    <a href="<?php echo esc_url($drom_url); ?>" 
                       target="_blank" 
                       style="background: #3e0d7b; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        open /drom
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo esc_url($drom_url); ?>"
                            style="background: #3e0d7b; color: white; border: none; padding: 8px 4px; margin-left: 2px; border-radius: 0 4px 4px 0; cursor: pointer; width: 10px; font-size: 12px;"
                            title="Copy drom URL">
                        📋
                    </button>
                </div>
                
                <!-- SiteJar4 Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 5px;">
                    <a href="<?php echo esc_url($sitejar4_url); ?>" 
                       target="_blank" 
                       style="background: #193968; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        open /sitejar4
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo esc_url($sitejar4_url); ?>"
                            style="background: #193968; color: white; border: none; padding: 8px 4px; margin-left: 2px; border-radius: 0 4px 4px 0; cursor: pointer; width: 10px; font-size: 12px;"
                            title="Copy sitejar4 URL">
                        📋
                    </button>
                </div>
                
                <!-- Pendulum Screen Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 5px;">
                    <a href="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" 
                       style="background: #000000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        pendulum screen
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo esc_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                            style="background: #000000; color: white; border: none; padding: 8px 4px; margin-left: 2px; border-radius: 0 4px 4px 0; cursor: pointer; width: 10px; font-size: 12px;"
                            title="Copy current page URL">
                        📋
                    </button>
                </div>
                
                <!-- Elementor Editor Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 15px;">
                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=elementor'); ?>" 
                       target="_blank" 
                       style="background: #800000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        elementor screen
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo admin_url('post.php?post=' . $post->ID . '&action=elementor'); ?>"
                            style="background: #800000; color: white; border: none; padding: 8px 4px; margin-left: 2px; border-radius: 0 4px 4px 0; cursor: pointer; width: 10px; font-size: 12px;"
                            title="Copy elementor editor URL">
                        📋
                    </button>
                </div>
                
                <!-- Live Frontend Screen Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 15px;">
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" 
                       target="_blank" 
                       style="background: #383838; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px 0 0 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        livefrontend screen
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo esc_url(get_permalink($post->ID)); ?>"
                            style="background: #383838; color: white; border: none; padding: 8px 4px; margin-left: 0; border-radius: 0 4px 4px 0; cursor: pointer; width: 20px; font-size: 12px;"
                            title="Copy live frontend URL">
                        📋
                    </button>
                </div>
                
                <!-- Minimize/Expand Button -->
                <div style="margin-left: 15px; border: 1px solid rgba(255,255,255,0.3); border-radius: 4px; background: rgba(0,0,0,0.1); display: inline-flex; align-items: center; justify-content: center;">
                    <button type="button" 
                            id="stellar-minimize-btn"
                            style="background: transparent; color: white; border: none; padding: 8px 10px; cursor: pointer; font-size: 16px; line-height: 1; display: flex; align-items: center; justify-content: center;"
                            title="Minimize/Expand Hurricane Chamber">
                        <span id="stellar-minimize-arrow" style="display: inline-block; transition: transform 0.3s ease;">▼</span>
                    </button>
                </div>
            </div>
            
            <!-- Collapsible Content Container -->
            <div id="stellar-collapsible-content" style="transition: all 0.3s ease;">
            <div class="snefuru-stellar-tabs">
                <div class="snefuru-stellar-tab-navigation">
                    <button type="button" class="snefuru-stellar-tab-button active" data-tab="elicitor">
                        Elementor Elicitor
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="elementor">
                        Elementor Deployer 1
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="elementor-deployer-2">
                        Elementor Deployer 2
                    </button>
                    <div class="snefuru-stellar-tab-separator" style="width: 6px; background: #000; height: 40px; margin: 0 8px; border-radius: 2px; pointer-events: none; display: block; z-index: 10; opacity: 1; position: relative;"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="old-elementor-elicitor">
                        Old Elementor Elicitor 1
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="old-elementor-elicitor-2">
                        Old Elementor Elicitor 2
                    </button>
                    <div class="snefuru-stellar-tab-separator" style="width: 6px; background: #000; height: 40px; margin: 0 8px; border-radius: 2px; pointer-events: none; display: block; z-index: 10; opacity: 1; position: relative;"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="gutenberg">
                        Gutenberg Elicitor
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="gut-deployer">
                        Gut. Deployer
                    </button>
                    <div class="snefuru-stellar-tab-separator" style="width: 6px; background: #000; height: 40px; margin: 0 8px; border-radius: 2px; pointer-events: none; display: block; z-index: 10; opacity: 1; position: relative;"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="nimble">
                        Nimble Elicitor
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="nimble-deployer">
                        Nimble Deployer
                    </button>
                    <div class="snefuru-stellar-tab-separator" style="width: 6px; background: #000; height: 40px; margin: 0 8px; border-radius: 2px; pointer-events: none; display: block; z-index: 10; opacity: 1; position: relative;"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="image-freeway">
                        Image Elicitor
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="image-deployer">
                        Image Deployer
                    </button>
                    <div class="snefuru-stellar-tab-separator"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="kpages-schema">
                        Asteroid Support
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="duplicate-kpage">
                        Duplicate Page
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="driggs">
                        Driggs
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="services">
                        Services
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="locations">
                        Locations
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="swipe15">
                        swipe15
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="gbp">
                        GBP
                    </button>
                </div>
                <div class="snefuru-stellar-tab-content">
                    <div class="snefuru-stellar-tab-panel active" data-panel="elicitor">
                        <?php
                        // Generate header303 content
                        $post_id = $post->ID;
                        $post_title = get_the_title($post_id);
                        $post_status = get_post_status($post_id);
                        $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
                        
                        // Format the header303 content with two lines
                        $header303_line1 = sprintf(
                            'wp_posts.post_id = %d / %s / %s / %s',
                            $post_id,
                            $post_title,
                            $post_status,
                            $edit_link
                        );
                        $header303_content = $header303_line1 . "\nBELOW";
                        
                        // Get Elementor data for this post
                        $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                        $formatted_data = '';
                        
                        if (!empty($elementor_data)) {
                            // Decode and pretty print the JSON for better readability
                            $decoded = json_decode($elementor_data, true);
                            if ($decoded !== null) {
                                $formatted_data = json_encode($decoded, JSON_PRETTY_PRINT);
                            } else {
                                $formatted_data = $elementor_data;
                            }
                        }
                        
                        // Static mapping text for header303_db_mapping
                        $db_mapping_text = "wp_posts.ID / wp_posts.post_title / wp_posts.post_status / admin_url('post.php?post=\$ID&action=edit')\n\nDatabase field mappings:\n- Post ID: wp_posts.ID (bigint unsigned, primary key)\n- Post Title: wp_posts.post_title (text field)\n- Post Status: wp_posts.post_status (varchar, values: publish/draft/pending/private/trash/auto-draft/inherit)\n- Edit Link: Dynamically generated using WordPress admin_url() function with wp_posts.ID";
                        ?>
                        
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 (Expanded to take up 2/3 width) -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 2; min-width: 600px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <span style="font-size: 16px; font-weight: bold;">denyeep column div 1</span>
                                    
                                    <!-- Classes to Copy Table -->
                                    <table style="border: 1px solid gray; border-collapse: collapse;">
                                        <tr>
                                            <td style="border: 1px solid gray; padding: 0; background-color: #f0e7bb;">
                                                <div style="padding: 4px; font-weight: bold;">classes to copy</div>
                                            </td>
                                            <td style="border: 1px solid gray; padding: 0;">
                                                <div class="copyable-class" style="padding: 4px; cursor: pointer;" data-class="exclude_from_blueshift">
                                                    <span style="display: inline-block; width: 10px; height: 10px; border: 1px solid red; background-color: white; color: red; font-size: 10px; line-height: 10px; text-align: center; margin-right: 4px; box-sizing: border-box;">✕</span>
                                                    exclude_from_blueshift
                                                </div>
                                            </td>
                                            <td style="border: 1px solid gray; padding: 0;">
                                                <div class="copyable-class" style="padding: 4px; cursor: pointer;" data-class="guarded">
                                                    <svg width="13" height="13" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                                        <polygon points="6.5,1 12,4.5 10,11.5 3,11.5 1,4.5" fill="navy" />
                                                    </svg>
                                                    guarded
                                                </div>
                                            </td>
                                            <td style="border: 1px solid gray; padding: 0;">
                                                <div class="copyable-class" style="padding: 4px; cursor: pointer;" data-class="on_inject_create_p_tags">
                                                    <span style="color: #8a5fd7; margin-right: 4px; font-size: 16px; line-height: 1; vertical-align: middle;">•</span>
                                                    on_inject_create_p_tags
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <button 
                                        type="button"
                                        id="blueshift-save-all-options-btn"
                                        style="padding: 5px 15px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 14px;"
                                    >
                                        Save Options
                                    </button>
                                </div>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Character Count per Separator Control -->
                                <?php 
                                // Get the saved options from database
                                $separator_count = get_option('ruplin_blueshift_separator_character_count', 95);
                                $show_exclude_from_blueshift = get_option('ruplin_blueshift_show_exclude_from_blueshift', false);
                                $show_guarded = get_option('ruplin_blueshift_show_guarded', true);
                                $render_p_tags = get_option('ruplin_blueshift_render_p_tags', true);
                                ?>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                    <span style="font-size: 16px; font-weight: bold;">qty of total characters per separator:</span>
                                    <input 
                                        type="text" 
                                        id="blueshift-separator-char-count"
                                        value="<?php echo esc_attr($separator_count); ?>"
                                        style="width: 80px; padding: 5px 8px; font-size: 14px; border: 1px solid #ddd; border-radius: 3px;"
                                    />
                                    <span id="blueshift-save-status" style="display: none; margin-left: 10px; color: green;"></span>
                                    
                                    <!-- Radio chips for filtering -->
                                    <div style="display: flex; gap: 10px; margin-left: 20px;">
                                        <label class="blueshift-chip" style="display: flex; align-items: center; gap: 5px; cursor: pointer; border: 1px solid #808080; border-radius: 4px; padding: 4px 8px; background-color: #c3c3c3; transition: background-color 0.2s;">
                                            <input 
                                                type="checkbox" 
                                                id="show-exclude-from-blueshift"
                                                class="blueshift-filter-checkbox"
                                                <?php echo $show_exclude_from_blueshift ? 'checked' : ''; ?>
                                                style="cursor: pointer;"
                                            />
                                            <span style="font-size: 14px;">show .exclude_from_blueshift</span>
                                        </label>
                                        <label class="blueshift-chip" style="display: flex; align-items: center; gap: 5px; cursor: pointer; border: 1px solid #808080; border-radius: 4px; padding: 4px 8px; background-color: #d1f7e4; transition: background-color 0.2s;">
                                            <input 
                                                type="checkbox" 
                                                id="show-guarded"
                                                class="blueshift-filter-checkbox"
                                                <?php echo $show_guarded ? 'checked' : ''; ?>
                                                style="cursor: pointer;"
                                            />
                                            <span style="font-size: 14px;">show .guarded</span>
                                        </label>
                                        <label class="blueshift-chip blueshift-chip-p-tags" style="display: flex; align-items: center; gap: 5px; cursor: pointer; border: 1px solid #808080; border-radius: 4px; padding: 4px 8px; background-color: #9bb3e9; transition: background-color 0.2s;">
                                            <input 
                                                type="checkbox" 
                                                id="render-p-tags"
                                                class="blueshift-filter-checkbox"
                                                <?php echo $render_p_tags ? 'checked' : ''; ?>
                                                style="cursor: pointer;"
                                            />
                                            <span style="font-size: 14px;">render &lt;p&gt;&lt;/p&gt; tags</span>
                                        </label>
                                    </div>
                                    
                                    <style>
                                        .blueshift-chip:hover {
                                            background-color: yellow !important;
                                        }
                                        .blueshift-chip:has(input:checked):not(.blueshift-chip-p-tags) {
                                            background-color: #d1f7e4 !important;
                                        }
                                        .blueshift-chip:has(input:not(:checked)):not(.blueshift-chip-p-tags) {
                                            background-color: #c3c3c3 !important;
                                        }
                                        .blueshift-chip-p-tags:has(input:checked) {
                                            background-color: #9bb3e9 !important;
                                        }
                                        .blueshift-chip-p-tags:has(input:not(:checked)) {
                                            background-color: #c3c3c3 !important;
                                        }
                                    </style>
                                </div>
                                
                                <!-- JavaScript for saving all options -->
                                <script type="text/javascript">
                                jQuery(document).ready(function($) {
                                    // Handle copying class names to clipboard
                                    $('.copyable-class').on('click', function() {
                                        var className = $(this).data('class');
                                        
                                        // Create temporary textarea to copy text
                                        var $temp = $('<textarea>');
                                        $('body').append($temp);
                                        $temp.val(className).select();
                                        document.execCommand('copy');
                                        $temp.remove();
                                        
                                        // Visual feedback
                                        var $this = $(this);
                                        var originalText = $this.text();
                                        $this.text('Copied!').css('background-color', '#d4edda');
                                        setTimeout(function() {
                                            $this.text(originalText).css('background-color', '');
                                        }, 1000);
                                    });
                                    
                                    // Handle Save Options button click
                                    $('#blueshift-save-all-options-btn').on('click', function() {
                                        var $btn = $(this);
                                        var $status = $('#blueshift-save-status');
                                        
                                        // Get all option values
                                        var separator_count = $('#blueshift-separator-char-count').val();
                                        var show_exclude_from_blueshift = $('#show-exclude-from-blueshift').is(':checked');
                                        var show_guarded = $('#show-guarded').is(':checked');
                                        var render_p_tags = $('#render-p-tags').is(':checked');
                                        
                                        // Disable button during save
                                        $btn.prop('disabled', true);
                                        var originalText = $btn.text();
                                        $btn.text('Saving...');
                                        
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'save_blueshift_all_options',
                                                separator_count: separator_count,
                                                show_exclude_from_blueshift: show_exclude_from_blueshift,
                                                show_guarded: show_guarded,
                                                render_p_tags: render_p_tags,
                                                nonce: '<?php echo wp_create_nonce('hurricane_save_blueshift_options'); ?>'
                                            },
                                            success: function(response) {
                                                if (response.success) {
                                                    $status.text('Options saved!').css('color', 'green').show();
                                                    setTimeout(function() {
                                                        $status.fadeOut();
                                                    }, 2000);
                                                } else {
                                                    $status.text('Error saving options').css('color', 'red').show();
                                                }
                                            },
                                            error: function() {
                                                $status.text('Error saving options').css('color', 'red').show();
                                            },
                                            complete: function() {
                                                $btn.prop('disabled', false);
                                                $btn.text(originalText);
                                            }
                                        });
                                    });
                                });
                                </script>
                                
                                <!-- Instance: Blueshift Format 4 (Expanded Width) -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation blueshift - format 4</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="blueshift-content-textbox-4-expanded" 
                                                class="snefuru-blueshift-content-textbox" 
                                                readonly
                                                placeholder="Frontend text content will be displayed here"
                                                style="flex: 1; height: 550px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract Blueshift content with multi-line separators for format 4
                                            // Use saved options to determine what to show
                                            $excluded_classes = array();
                                            if (!$show_exclude_from_blueshift) {
                                                $excluded_classes[] = 'exclude_from_blueshift';
                                            }
                                            if (!$show_guarded) {
                                                $excluded_classes[] = 'guarded';
                                            }
                                            $strip_p_tags = !$render_p_tags; // Strip p tags if render_p_tags is false
                                            $blueshift_content_format4 = $this->blueshift->extract_elementor_blueshift_content_format4_filtered($post->ID, $excluded_classes, $strip_p_tags);
                                            
                                            // Limit length for display if too long
                                            if (strlen($blueshift_content_format4) > 50000) {
                                                $blueshift_content_format4 = substr($blueshift_content_format4, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                            }
                                            
                                            echo esc_textarea($blueshift_content_format4);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox-4-expanded" style="height: 550px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Operation Blueshift Label -->
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #333;">operation blueshift: get all text content output on frontend</span>
                                
                                <!-- Refresh Button -->
                                <button type="button" 
                                        id="refresh-blueshift-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="background: #007cba; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; margin-bottom: 15px;">
                                    refresh blueshift data
                                </button>
                                
                                <!-- Label: widgets with class exclude_from_blueshift -->
                                <div style="width: 100%; background: #f0f0f0; padding: 10px; margin-bottom: 15px;">
                                    <div style="text-align: center; font-size: 16px; font-weight: bold; color: black;">
                                        widgets with class exclude_from_blueshift
                                    </div>
                                </div>
                                
                                <!-- Widgets with class of .exclude_from_blueshift -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">widgets with class of .exclude_from_blueshift</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="blueshift-content-textbox-exclude" 
                                                class="snefuru-blueshift-content-textbox" 
                                                readonly
                                                placeholder="Widgets with .exclude_from_blueshift class will be displayed here"
                                                style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract only widgets with exclude_from_blueshift class using format 4 style
                                            $blueshift_content_exclude = $this->blueshift->extract_elementor_blueshift_content_by_class($post->ID, 'exclude_from_blueshift');
                                            
                                            // Limit length for display if too long
                                            if (strlen($blueshift_content_exclude) > 50000) {
                                                $blueshift_content_exclude = substr($blueshift_content_exclude, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                            }
                                            
                                            echo esc_textarea($blueshift_content_exclude);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox-exclude" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Widgets with class of .guarded -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">widgets with class of .guarded</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="blueshift-content-textbox-guarded" 
                                                class="snefuru-blueshift-content-textbox" 
                                                readonly
                                                placeholder="Widgets with .guarded class will be displayed here"
                                                style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract only widgets with guarded class using format 4 style
                                            $blueshift_content_guarded = $this->blueshift->extract_elementor_blueshift_content_by_class($post->ID, 'guarded');
                                            
                                            // Limit length for display if too long
                                            if (strlen($blueshift_content_guarded) > 50000) {
                                                $blueshift_content_guarded = substr($blueshift_content_guarded, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                            }
                                            
                                            echo esc_textarea($blueshift_content_guarded);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox-guarded" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Widgets that contain <p> and/or </p> tags -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">widgets that contain a &lt;p&gt; and/or &lt;/p&gt; tag</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="blueshift-content-textbox-p-tags" 
                                                class="snefuru-blueshift-content-textbox" 
                                                readonly
                                                placeholder="Widgets containing <p> tags will be displayed here"
                                                style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract only widgets that contain <p> tags using format 4 style
                                            $blueshift_content_p_tags = $this->blueshift->extract_elementor_widgets_with_p_tags($post->ID);
                                            
                                            // Limit length for display if too long
                                            if (strlen($blueshift_content_p_tags) > 50000) {
                                                $blueshift_content_p_tags = substr($blueshift_content_p_tags, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                            }
                                            
                                            echo esc_textarea($blueshift_content_p_tags);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox-p-tags" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="elementor">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Zeeprex Submit Section -->
                                <div class="snefuru-zeeprex-section">
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">zeeprex_submit</span>
                                    
                                    <textarea 
                                        id="snefuru-zeeprex-content" 
                                        placeholder="Paste your content here. Make sure your codes are preceded by a '#' symbol (e.g., #y_hero1_heading)"
                                        style="width: 100%; height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 13px; margin-bottom: 10px;"
                                    ></textarea>
                                    
                                    <button 
                                        type="button" 
                                        id="snefuru-inject-content-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="background: #800000; color: #fff; font-weight: bold; text-transform: lowercase; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 15px;"
                                    >
                                        run function_inject_content_2
                                    </button>
                                    
                                    <div id="snefuru-inject-result" style="margin-top: 10px; padding: 10px; border-radius: 4px; display: none;"></div>
                                    
                                    <div style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin-top: 15px; font-size: 12px; line-height: 1.6;">
                                        <strong>DEVELOPER INFO:</strong> this function was originally cloned from page of<br>
                                        https://(insertdomainhere.com)/wp-admin/admin.php?page=zurkoscreen4 on 2025__08_10<br>
                                        it was cloned from the deprecated Zurkovich wp plugin<br>
                                        <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ddd;">
                                        <strong>note to claude code:</strong> please make sure this function works identically when user submits content<br><br>
                                        but the "select a page" dropdown is not needed - that's always the current page / post that the user is viewing the active screen of (the same as the post id in the url like https://8mako.ksit.me/wp-admin/post.php?post=666&action=edit)
                                    </div>
                                </div>
                                
                                <script type="text/javascript">
                                jQuery(document).ready(function($) {
                                    $('#snefuru-inject-content-btn').on('click', function() {
                                        var content = $('#snefuru-zeeprex-content').val();
                                        var postId = $(this).data('post-id');
                                        var $btn = $(this);
                                        var $result = $('#snefuru-inject-result');
                                        
                                        if (!content.trim()) {
                                            $result.removeClass('success error').addClass('error');
                                            $result.html('<strong>Error:</strong> Please enter content to inject.');
                                            $result.show();
                                            return;
                                        }
                                        
                                        // Disable button and show processing
                                        $btn.prop('disabled', true).text('processing...');
                                        $result.hide();
                                        
                                        // Make AJAX call
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'snefuru_inject_content',
                                                post_id: postId,
                                                zeeprex_content: content,
                                                nonce: '<?php echo wp_create_nonce('snefuru_inject_content_nonce'); ?>'
                                            },
                                            success: function(response) {
                                                if (response.success) {
                                                    $result.removeClass('error').css({
                                                        'background': '#d4edda',
                                                        'color': '#155724',
                                                        'border': '1px solid #c3e6cb'
                                                    });
                                                    $result.html('<strong>Success:</strong> ' + response.data.message);
                                                } else {
                                                    $result.removeClass('success').css({
                                                        'background': '#f8d7da',
                                                        'color': '#721c24',
                                                        'border': '1px solid #f5c6cb'
                                                    });
                                                    $result.html('<strong>Error:</strong> ' + response.data);
                                                }
                                                $result.show();
                                            },
                                            error: function() {
                                                $result.removeClass('success').css({
                                                    'background': '#f8d7da',
                                                    'color': '#721c24',
                                                    'border': '1px solid #f5c6cb'
                                                });
                                                $result.html('<strong>Error:</strong> Failed to inject content. Please try again.');
                                                $result.show();
                                            },
                                            complete: function() {
                                                $btn.prop('disabled', false).text('run function_inject_content_2');
                                            }
                                        });
                                    });
                                });
                                </script>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Replex Submit Box -->
                                <div>
                                    <label for="snefuru-replex-content" style="display: block; font-weight: bold; margin-bottom: 5px;">
                                        replex_submit_box (replace codes directly only)
                                    </label>
                                    <textarea 
                                        id="snefuru-replex-content" 
                                        placeholder="Enter content with ##codes to replace directly in text" 
                                        style="width: 100%; height: 150px; font-family: monospace; font-size: 12px; padding: 10px; border: 1px solid #ccc; border-radius: 3px;"
                                    ></textarea>
                                    <button 
                                        type="button" 
                                        id="snefuru-replex-submit-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="margin-top: 10px; background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 3px; cursor: pointer; font-size: 14px;"
                                    >
                                        run function_inject_content_5
                                    </button>
                                    
                                    <!-- Toggle Switch for Auto Post Title Update -->
                                    <div style="margin-top: 15px; display: flex; align-items: center; gap: 10px;">
                                        <label class="snefuru-toggle-switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                            <input type="checkbox" id="snefuru-auto-title-toggle" style="opacity: 0; width: 0; height: 0;">
                                            <span class="snefuru-toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; transition: .3s; border-radius: 24px;"></span>
                                        </label>
                                        <span style="font-size: 12px; color: #666;">replace wp_posts.post_title with the first line of text following the double-pound-sign "##" code in the submission</span>
                                    </div>
                                    
                                    <style>
                                    .snefuru-toggle-slider {
                                        background-color: #ccc !important;
                                    }
                                    .snefuru-toggle-slider:before {
                                        position: absolute;
                                        content: "";
                                        height: 18px;
                                        width: 18px;
                                        left: 3px;
                                        bottom: 3px;
                                        background-color: white;
                                        transition: .3s;
                                        border-radius: 50%;
                                    }
                                    #snefuru-auto-title-toggle:checked + .snefuru-toggle-slider {
                                        background-color: #4CAF50 !important;
                                    }
                                    #snefuru-auto-title-toggle:checked + .snefuru-toggle-slider:before {
                                        transform: translateX(26px);
                                    }
                                    </style>
                                    
                                    <div id="snefuru-replex-result" style="margin-top: 10px; padding: 10px; border-radius: 4px; display: none;"></div>
                                </div>
                                
                                <script type="text/javascript">
                                jQuery(document).ready(function($) {
                                    $('#snefuru-replex-submit-btn').on('click', function() {
                                        var content = $('#snefuru-replex-content').val();
                                        var postId = $(this).data('post-id');
                                        var autoUpdateTitle = $('#snefuru-auto-title-toggle').is(':checked');
                                        var $btn = $(this);
                                        var $result = $('#snefuru-replex-result');
                                        
                                        if (!content.trim()) {
                                            $result.removeClass('success error').addClass('error');
                                            $result.html('<strong>Error:</strong> Please enter content to inject.');
                                            $result.show();
                                            return;
                                        }
                                        
                                        $btn.prop('disabled', true).text('Processing...');
                                        $result.hide();
                                        
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'snefuru_inject_replex_content',
                                                post_id: postId,
                                                replex_content: content,
                                                auto_update_title: autoUpdateTitle ? 1 : 0,
                                                nonce: '<?php echo wp_create_nonce('snefuru_inject_replex_content_nonce'); ?>'
                                            },
                                            success: function(response) {
                                                if (response.success) {
                                                    $result.removeClass('error').addClass('success').css({
                                                        'background': '#d4edda',
                                                        'color': '#155724',
                                                        'border': '1px solid #c3e6cb'
                                                    });
                                                    $result.html('<strong>Success:</strong> ' + response.data.message);
                                                } else {
                                                    $result.removeClass('success').addClass('error').css({
                                                        'background': '#f8d7da',
                                                        'color': '#721c24',
                                                        'border': '1px solid #f5c6cb'
                                                    });
                                                    $result.html('<strong>Error:</strong> ' + (response.data ? response.data : 'Unknown error occurred'));
                                                }
                                                $result.show();
                                            },
                                            error: function() {
                                                $result.removeClass('success').css({
                                                    'background': '#f8d7da',
                                                    'color': '#721c24',
                                                    'border': '1px solid #f5c6cb'
                                                });
                                                $result.html('<strong>Error:</strong> Failed to inject replex content. Please try again.');
                                                $result.show();
                                            },
                                            complete: function() {
                                                $btn.prop('disabled', false).text('run function_inject_content_5');
                                            }
                                        });
                                    });
                                });
                                </script>
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Cobalt Submit Box -->
                                <div>
                                    <label for="snefuru-cobalt-content" style="display: block; font-weight: bold; margin-bottom: 5px;">
                                        cobalt_submit_box (target specific widgets by ID)
                                    </label>
                                    <textarea 
                                        id="snefuru-cobalt-content" 
                                        placeholder="Enter Blueshift Format 3 markup with ==widget1, ==widget2, ==item1, etc. to target specific widgets/items for content updates" 
                                        style="width: 100%; height: 150px; font-family: monospace; font-size: 12px; padding: 10px; border: 1px solid #ccc; border-radius: 3px;"
                                    ></textarea>
                                    <button 
                                        type="button" 
                                        id="snefuru-cobalt-submit-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="margin-top: 10px; background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 3px; cursor: pointer; font-size: 14px;"
                                    >
                                        run cobalt_function_inject_content
                                    </button>
                                    
                                    <!-- Toggle Switch for Auto Post Title Update -->
                                    <div style="margin-top: 15px; display: flex; align-items: center; gap: 10px;">
                                        <label class="snefuru-toggle-switch-cobalt" style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                            <input type="checkbox" id="snefuru-auto-title-toggle-cobalt" style="opacity: 0; width: 0; height: 0;">
                                            <span class="snefuru-toggle-slider-cobalt" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; transition: .3s; border-radius: 24px;"></span>
                                        </label>
                                        <span style="font-size: 12px; color: #666;">replace wp_posts.post_title with the first line of content from the first updated widget in the submission</span>
                                    </div>
                                    
                                    <style>
                                    .snefuru-toggle-slider-cobalt {
                                        background-color: #ccc !important;
                                    }
                                    .snefuru-toggle-slider-cobalt:before {
                                        position: absolute;
                                        content: "";
                                        height: 18px;
                                        width: 18px;
                                        left: 3px;
                                        bottom: 3px;
                                        background-color: white;
                                        transition: .3s;
                                        border-radius: 50%;
                                    }
                                    #snefuru-auto-title-toggle-cobalt:checked + .snefuru-toggle-slider-cobalt {
                                        background-color: #4CAF50 !important;
                                    }
                                    #snefuru-auto-title-toggle-cobalt:checked + .snefuru-toggle-slider-cobalt:before {
                                        transform: translateX(26px);
                                    }
                                    </style>
                                    
                                    <!-- Reset Helper -->
                                    <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; font-size: 12px;">
                                        <div style="color: #495057; margin-bottom: 8px;">
                                            <strong>Emergency Reset:</strong> If page styling breaks, use this command to restore from backup:
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <input type="text" id="reset-elementor-command" value="RESET_ELEMENTOR_DATA" readonly 
                                                   style="flex: 1; padding: 4px 8px; border: 1px solid #ced4da; border-radius: 3px; background-color: #fff; font-family: monospace; font-size: 11px;">
                                            <button type="button" class="snefuru-copy-btn" data-target="reset-elementor-command" 
                                                    style="padding: 4px 8px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 11px;">
                                                Copy
                                            </button>
                                        </div>
                                        <div style="color: #6c757d; font-size: 10px; margin-top: 4px;">
                                            Paste this command in the content box above and click submit to restore the page.
                                        </div>
                                        
                                        <!-- Rollback Button -->
                                        <div style="margin-top: 10px;">
                                            <button type="button" 
                                                    id="rollback-revision-btn" 
                                                    data-post-id="<?php echo esc_attr($post->ID); ?>"
                                                    style="padding: 8px 16px; background: #d63638; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 13px; font-weight: 500;">
                                                Rollback 1 Version In Revision History
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="snefuru-cobalt-result" style="margin-top: 10px; padding: 10px; border-radius: 4px; display: none;"></div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="elementor-deployer-2">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Titanium Submit Box -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-top: 15px;">
                                    <div class="snefuru-titanium-container">
                                        <span class="snefuru-titanium-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">titanium_submit_box (target specific widgets by ID)</span>
                                        <div style="margin-bottom: 10px; color: #666; font-size: 13px;">
                                            Enter Blueshift Format 3 markup with ==widget1, ==widget2, ==item1, etc. to target specific widgets/items for content updates
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <textarea 
                                                id="snefuru-titanium-content" 
                                                class="snefuru-titanium-textbox" 
                                                placeholder="==widget1
Your new heading text

==widget2
Your new content text

==widget3
==item1
First list item
==item2
Second list item"
                                                style="width: 100%; height: 400px; padding: 10px; border: 2px solid #e0e5eb; border-radius: 4px; font-family: monospace; font-size: 12px; line-height: 1.4; resize: vertical;"
                                            ></textarea>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <button type="button" 
                                                    id="snefuru-titanium-submit-btn" 
                                                    data-post-id="<?php echo esc_attr($post->ID); ?>"
                                                    class="snefuru-submit-btn-horizontal" 
                                                    style="width: 100%; padding: 12px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;"
                                                    onclick="console.log('Titanium button clicked directly');">
                                                run titanium_function_inject_content
                                            </button>
                                        </div>
                                        
                                        <!-- WP Slash Toggle for Titanium -->
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                            <label class="snefuru-toggle-switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                                <input type="checkbox" id="snefuru-wp-slash-toggle-titanium" checked style="opacity: 0; width: 0; height: 0;">
                                                <span class="snefuru-toggle-slider-wp-slash" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #4CAF50; transition: .4s; border-radius: 24px;"></span>
                                            </label>
                                            <span style="font-size: 12px; color: #666;">run wp_slash process</span>
                                        </div>
                                        
                                        <style>
                                        .snefuru-toggle-slider-wp-slash {
                                            background-color: #ccc !important;
                                        }
                                        .snefuru-toggle-slider-wp-slash:before {
                                            position: absolute;
                                            content: "";
                                            height: 18px;
                                            width: 18px;
                                            left: 3px;
                                            bottom: 3px;
                                            background-color: white;
                                            transition: .3s;
                                            border-radius: 50%;
                                        }
                                        #snefuru-wp-slash-toggle-titanium:checked + .snefuru-toggle-slider-wp-slash {
                                            background-color: #4CAF50 !important;
                                        }
                                        #snefuru-wp-slash-toggle-titanium:checked + .snefuru-toggle-slider-wp-slash:before {
                                            transform: translateX(26px);
                                        }
                                        </style>
                                    </div>
                                    
                                    <!-- Auto Title Update Toggle for Titanium -->
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                        <label class="snefuru-toggle-switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                            <input type="checkbox" id="snefuru-auto-title-toggle-titanium" checked style="opacity: 0; width: 0; height: 0;">
                                            <span class="snefuru-toggle-slider-titanium" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #4CAF50; transition: .4s; border-radius: 24px;"></span>
                                        </label>
                                        <span style="font-size: 12px; color: #666;">replace wp_posts.post_title with the first line of content from the first updated widget in the submission</span>
                                    </div>
                                    
                                    <style>
                                    .snefuru-toggle-slider-titanium {
                                        background-color: #ccc !important;
                                    }
                                    .snefuru-toggle-slider-titanium:before {
                                        position: absolute;
                                        content: "";
                                        height: 18px;
                                        width: 18px;
                                        left: 3px;
                                        bottom: 3px;
                                        background-color: white;
                                        transition: .3s;
                                        border-radius: 50%;
                                    }
                                    #snefuru-auto-title-toggle-titanium:checked + .snefuru-toggle-slider-titanium {
                                        background-color: #4CAF50 !important;
                                    }
                                    #snefuru-auto-title-toggle-titanium:checked + .snefuru-toggle-slider-titanium:before {
                                        transform: translateX(26px);
                                    }
                                    </style>
                                    
                                    <div id="snefuru-titanium-result" style="margin-top: 10px; padding: 10px; border-radius: 4px; display: none;"></div>
                                    
                                    <!-- Inline Script for Titanium -->
                                    <script type="text/javascript">
                                    jQuery(document).ready(function($) {
                                        // Ensure Titanium button handler is bound
                                        $(document).off('click.titanium-submit').on('click.titanium-submit', '#snefuru-titanium-submit-btn', function(e) {
                                            e.preventDefault();
                                            
                                            console.log('Titanium submit button clicked (inline handler)');
                                            
                                            var $button = $(this);
                                            var content = $('#snefuru-titanium-content').val();
                                            var postId = $button.data('post-id');
                                            var autoUpdateTitle = $('#snefuru-auto-title-toggle-titanium').is(':checked');
                                            var runWpSlash = $('#snefuru-wp-slash-toggle-titanium').is(':checked');
                                            var originalText = $button.text();
                                            var $result = $('#snefuru-titanium-result');
                                            
                                            console.log('Content:', content);
                                            console.log('Post ID:', postId);
                                            console.log('Auto update title:', autoUpdateTitle);
                                            console.log('Run wp_slash:', runWpSlash);
                                            
                                            if (!content.trim()) {
                                                $result.removeClass('success error').addClass('error');
                                                $result.html('<strong>Error:</strong> Please enter content to inject.');
                                                $result.show();
                                                return;
                                            }
                                            
                                            // Disable button and show loading
                                            $button.prop('disabled', true).text('Processing...');
                                            $result.hide();
                                            
                                            // Make AJAX request
                                            $.ajax({
                                                url: ajaxurl,
                                                type: 'POST',
                                                data: {
                                                    action: 'titanium_inject_content',
                                                    post_id: postId,
                                                    content: content,
                                                    auto_update_title: autoUpdateTitle,
                                                    run_wp_slash: runWpSlash,
                                                    nonce: '<?php echo wp_create_nonce('hurricane_nonce'); ?>'
                                                },
                                                success: function(response) {
                                                    console.log('Titanium response:', response);
                                                    
                                                    if (response.success) {
                                                        $result.removeClass('error').addClass('success');
                                                        $result.html('<strong>Success:</strong> ' + response.data);
                                                        $result.css({
                                                            'background-color': '#d4edda',
                                                            'border': '1px solid #c3e6cb',
                                                            'color': '#155724'
                                                        });
                                                        $result.show();
                                                        
                                                        // Clear the content after successful submission
                                                        $('#snefuru-titanium-content').val('');
                                                    } else {
                                                        $result.removeClass('success').addClass('error');
                                                        $result.html('<strong>Error:</strong> ' + (response.data || 'Unknown error occurred'));
                                                        $result.css({
                                                            'background-color': '#f8d7da',
                                                            'border': '1px solid #f5c6cb',
                                                            'color': '#721c24'
                                                        });
                                                        $result.show();
                                                    }
                                                },
                                                error: function(xhr, status, error) {
                                                    console.error('AJAX error:', error);
                                                    $result.removeClass('success').addClass('error');
                                                    $result.html('<strong>Error:</strong> AJAX request failed: ' + error);
                                                    $result.css({
                                                        'background-color': '#f8d7da',
                                                        'border': '1px solid #f5c6cb',
                                                        'color': '#721c24'
                                                    });
                                                    $result.show();
                                                },
                                                complete: function() {
                                                    // Re-enable button
                                                    $button.prop('disabled', false).text(originalText);
                                                }
                                            });
                                        });
                                    });
                                    </script>
                                </div>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Suelo Base Reference Content -->
                                <div style="margin-top: 15px;">
                                    <label style="display: block; font-size: 14px; font-weight: bold; margin-bottom: 8px;">suelo_base_reference_content</label>
                                    <textarea 
                                        id="snefuru-suelo-base-reference" 
                                        readonly
                                        style="width: 100%; height: 300px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 12px; background-color: #f9f9f9; resize: vertical;"
                                    ><?php
                                        // Generate base reference content for current post
                                        if ($post && $post->ID) {
                                            echo esc_textarea($this->generate_base_reference_content($post->ID));
                                        } else {
                                            echo "No post ID available";
                                        }
                                    ?></textarea>
                                </div>
                                
                                <!-- Additional Info Box -->
                                <div style="margin-top: 15px;">
                                    <textarea 
                                        id="snefuru-suelo-info" 
                                        readonly
                                        style="width: 100%; height: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 12px; background-color: #f5f5f5; resize: vertical;"
                                    ><?php
                                        // Output the header/footer info
                                        echo "BASE REFERENCE CONTENT FOR COBALT/TITANIUM\n";
                                        echo "==============================================\n\n";
                                        echo "Widget Reference Mapping:\n";
                                        echo "--------------------------\n\n";
                                        echo "USAGE:\n";
                                        echo "Use the widget references above (==widget1, ==widget2, etc.)\n";
                                        echo "in Cobalt/Titanium submissions to target specific widgets.\n\n";
                                        echo "EXAMPLE SUBMISSION:\n";
                                        echo "==widget1\n";
                                        echo "New heading text\n";
                                        echo "==widget3\n";
                                        echo "New button text\n";
                                        echo "==widget5\n";
                                        echo "==item2\n";
                                        echo "Updated list item text\n";
                                    ?></textarea>
                                </div>
                                
                                <!-- Widget Count Box -->
                                <div style="margin-top: 15px;">
                                    <textarea 
                                        id="snefuru-widget-count" 
                                        readonly
                                        style="width: 100%; height: 50px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 12px; background-color: #e8f4f8; resize: vertical;"
                                    ><?php
                                        // Get widget count
                                        if ($post && $post->ID) {
                                            echo esc_textarea($this->get_widget_count($post->ID));
                                        } else {
                                            echo "Total widgets found: 0";
                                        }
                                    ?></textarea>
                                </div>
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="old-elementor-elicitor">
                        <?php
                        // Generate header303 content
                        $post_id = $post->ID;
                        $post_title = get_the_title($post_id);
                        $post_status = get_post_status($post_id);
                        $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
                        
                        // Format the header303 content with two lines
                        $header303_line1 = sprintf(
                            'wp_posts.post_id = %d / %s / %s / %s',
                            $post_id,
                            $post_title,
                            $post_status,
                            $edit_link
                        );
                        $header303_content = $header303_line1 . "\nBELOW";
                        
                        // Get Elementor data for this post
                        $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                        $formatted_data = '';
                        
                        if (!empty($elementor_data)) {
                            // Decode and pretty print the JSON for better readability
                            $decoded = json_decode($elementor_data, true);
                            if ($decoded !== null) {
                                $formatted_data = json_encode($decoded, JSON_PRETTY_PRINT);
                            } else {
                                $formatted_data = $elementor_data;
                            }
                        }
                        
                        // Static mapping text for header303_db_mapping
                        $db_mapping_text = "wp_posts.ID / wp_posts.post_title / wp_posts.post_status / admin_url('post.php?post=\$ID&action=edit')\n\nDatabase field mappings:\n- Post ID: wp_posts.ID (bigint unsigned, primary key)\n- Post Title: wp_posts.post_title (text field)\n- Post Status: wp_posts.post_status (varchar, values: publish/draft/pending/private/trash/auto-draft/inherit)\n- Edit Link: Dynamically generated using WordPress admin_url() function with wp_posts.ID";
                        ?>
                        
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Replete Instance Wrapper -->
                                <div class="snefuru-replete-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 20px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">replete_instance</span>
                                    <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                    
                                    <!-- Copy All Instances Container -->
                                    <div class="snefuru-copy-all-instances-container">
                                        <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">copy all instances</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="old-elicitor-copy-all-instances-textbox" 
                                                class="snefuru-header303-db-mapping-textbox" 
                                                readonly
                                                style="height: 200px; flex: 1;"
                                            ><?php 
                                            // Combine all three instances into one text
                                            $all_instances_text = $db_mapping_text . "\n\n———————————————————————\n\n" . $header303_content . "\n\n———————————————————————\n\n" . $formatted_data;
                                            echo esc_textarea($all_instances_text); 
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="old-elicitor-copy-all-instances-textbox" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instance 1 Wrapper: Header303 DB Mapping -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-header303-db-mapping-container">
                                        <span class="snefuru-header303-db-mapping-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">header303_db_mapping</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="old-elicitor-header303-db-mapping-textbox" 
                                                class="snefuru-header303-db-mapping-textbox" 
                                                readonly
                                                style="flex: 1;"
                                            ><?php echo esc_textarea($db_mapping_text); ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="old-elicitor-header303-db-mapping-textbox" style="height: 150px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instance 2 Wrapper: Header303 -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-header303-container">
                                        <span class="snefuru-header303-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">header303_filled</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="old-elicitor-header303-textbox" 
                                                class="snefuru-header303-textbox" 
                                                readonly
                                                style="flex: 1;"
                                            ><?php echo esc_textarea($header303_content); ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="old-elicitor-header303-textbox" style="height: 100px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instance 3 Wrapper: Elementor Data -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-elementor-data-container">
                                        <span class="snefuru-elementor-data-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">_elementor_data</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="old-elicitor-elementor-data-textbox" 
                                                class="snefuru-elementor-data-textbox" 
                                                readonly
                                                placeholder="No Elementor data found for this page"
                                                style="flex: 1;"
                                            ><?php echo esc_textarea($formatted_data); ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="old-elicitor-elementor-data-textbox" style="height: 100px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Operation Redshift Label -->
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #333;">operation redshift: get all text content output on frontend</span>
                                
                                <!-- Refresh Button -->
                                <button type="button" 
                                        id="old-elicitor-refresh-redshift-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="background: #007cba; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; margin-bottom: 15px;">
                                    refresh redshift data
                                </button>
                                
                                <!-- Instance 1: Frontend Text Content -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation redshift - format 1</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="old-elicitor-frontend-content-textbox" 
                                                class="snefuru-frontend-content-textbox" 
                                                readonly
                                                placeholder="Frontend text content will be displayed here"
                                                style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract frontend text content using new Elementor data parsing approach
                                            $frontend_text_content = $this->extract_elementor_frontend_text($post->ID);
                                            
                                            // Limit length for display if too long
                                            if (strlen($frontend_text_content) > 10000) {
                                                $frontend_text_content = substr($frontend_text_content, 0, 10000) . "\n\n... [Content truncated at 10,000 characters]";
                                            }
                                            
                                            echo esc_textarea($frontend_text_content);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="old-elicitor-frontend-content-textbox" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="old-elementor-elicitor-2">
                        <!-- Label: other formats -->
                        <div style="width: 100%; background: #f0f0f0; padding: 10px; margin-bottom: 15px;">
                            <div style="text-align: center; font-size: 16px; font-weight: bold; color: black;">
                                other formats
                            </div>
                        </div>
                        
                        <!-- Instance 1: Frontend Text Content -->
                        <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                            <div class="snefuru-frontend-content-container">
                                <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation blueshift - format 1</span>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <textarea 
                                        id="blueshift-content-textbox" 
                                        class="snefuru-blueshift-content-textbox" 
                                        readonly
                                        placeholder="Frontend text content will be displayed here"
                                        style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                    ><?php 
                                    // Extract Blueshift content using new approach
                                    $blueshift_content = $this->blueshift->extract_elementor_blueshift_content($post->ID);
                                    
                                    // Limit length for display if too long
                                    if (strlen($blueshift_content) > 50000) {
                                        $blueshift_content = substr($blueshift_content, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                    }
                                    
                                    echo esc_textarea($blueshift_content);
                                    ?></textarea>
                                    <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Instance 2: Blueshift Format 2 -->
                        <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                            <div class="snefuru-frontend-content-container">
                                <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation blueshift - format 2</span>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <textarea 
                                        id="blueshift-content-textbox-2" 
                                        class="snefuru-blueshift-content-textbox" 
                                        readonly
                                        placeholder="Frontend text content will be displayed here"
                                        style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                    ><?php 
                                    // Extract Blueshift content with numbering for format 2
                                    $blueshift_content_format2 = $this->blueshift->extract_elementor_blueshift_content_format2($post->ID);
                                    
                                    // Limit length for display if too long
                                    if (strlen($blueshift_content_format2) > 50000) {
                                        $blueshift_content_format2 = substr($blueshift_content_format2, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                    }
                                    
                                    echo esc_textarea($blueshift_content_format2);
                                    ?></textarea>
                                    <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox-2" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Instance 3: Blueshift Format 3 -->
                        <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                            <div class="snefuru-frontend-content-container">
                                <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation blueshift - format 3</span>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <textarea 
                                        id="blueshift-content-textbox-3" 
                                        class="snefuru-blueshift-content-textbox" 
                                        readonly
                                        placeholder="Frontend text content will be displayed here"
                                        style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                    ><?php 
                                    // Extract Blueshift content with CSS classes and IDs for format 3
                                    $blueshift_content_format3 = $this->blueshift->extract_elementor_blueshift_content_format3($post->ID);
                                    
                                    // Limit length for display if too long
                                    if (strlen($blueshift_content_format3) > 50000) {
                                        $blueshift_content_format3 = substr($blueshift_content_format3, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                    }
                                    
                                    echo esc_textarea($blueshift_content_format3);
                                    ?></textarea>
                                    <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox-3" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="gutenberg">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="gut-deployer">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="nimble">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="nimble-deployer">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="image-freeway">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Operation Rover Instance Wrapper -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-rover-container">
                                        <span class="snefuru-rover-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation rover: get all image grains from page and display in json</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="rover-images-textbox" 
                                                class="snefuru-rover-textbox" 
                                                readonly
                                                placeholder="Image data will be displayed here in JSON format"
                                                style="flex: 1; height: 200px; padding: 10px; border: 2px solid #e0e5eb; border-radius: 4px; font-family: monospace; font-size: 12px; line-height: 1.4; resize: vertical;"
                                            ></textarea>
                                            <button type="button" 
                                                    class="snefuru-copy-btn-right" 
                                                    data-target="rover-images-textbox" 
                                                    style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="image-deployer">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Tebnar2 Raw Button -->
                                <a href="http://localhost:3000/bin34/tebnar2" target="_blank" class="button" style="display: inline-block; padding: 8px 16px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
                                    go to /tebnar2 raw
                                </a>
                                
                                <!-- Hudson ImgPlanBatch ID Section -->
                                <div style="margin-top: 20px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">_zen_orbitposts.hudson_imgplanbatch_id</span>
                                    
                                    <?php
                                    // Get existing hudson_imgplanbatch_id for this post
                                    global $wpdb;
                                    $table_name = $wpdb->prefix . 'zen_orbitposts';
                                    $existing_hudson_id = $wpdb->get_var($wpdb->prepare(
                                        "SELECT hudson_imgplanbatch_id FROM $table_name WHERE rel_wp_post_id = %d",
                                        $post->ID
                                    ));
                                    ?>
                                    <input type="text" 
                                           id="hudson-imgplanbatch-id" 
                                           value="<?php echo esc_attr($existing_hudson_id); ?>"
                                           placeholder="79f1a2d4-ec7e-4f3a-a896-d2e4d0e2d3ba"
                                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 14px; margin-bottom: 10px;"
                                           maxlength="36">
                                    
                                    <button type="button" 
                                            id="save-hudson-imgplanbatch-btn"
                                            class="button button-primary"
                                            style="background: #00a32a; border-color: #00a32a; padding: 8px 16px; font-size: 14px;">
                                        Save
                                    </button>
                                    
                                    <!-- Tebnar2 with Param Button -->
                                    <div style="margin-top: 10px;">
                                        <?php if ($existing_hudson_id): ?>
                                            <a href="http://localhost:3000/bin34/tebnar2?batchid=<?php echo urlencode($existing_hudson_id); ?>" 
                                               target="_blank" 
                                               id="tebnar2-param-btn"
                                               class="button" 
                                               style="display: inline-block; padding: 8px 16px; background: #f0ad4e; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500; border-color: #eea236;">
                                                go to /tebnar2 with param set
                                            </a>
                                        <?php else: ?>
                                            <button type="button" 
                                                    id="tebnar2-param-btn"
                                                    class="button" 
                                                    disabled
                                                    style="padding: 8px 16px; background: #ccc; color: #666; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: not-allowed;"
                                                    title="Save a Hudson ImgPlanBatch ID first to enable this button">
                                                go to /tebnar2 with param set
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <script>
                                jQuery(document).ready(function($) {
                                    $('#save-hudson-imgplanbatch-btn').on('click', function() {
                                        var $btn = $(this);
                                        var hudsonId = $('#hudson-imgplanbatch-id').val().trim();
                                        var postId = <?php echo intval($post->ID); ?>;
                                        
                                        if (!hudsonId) {
                                            alert('Please enter a Hudson ImgPlanBatch ID');
                                            return;
                                        }
                                        
                                        // Basic UUID validation
                                        var uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
                                        if (!uuidRegex.test(hudsonId)) {
                                            alert('Please enter a valid UUID format (e.g., 79f1a2d4-ec7e-4f3a-a896-d2e4d0e2d3ba)');
                                            return;
                                        }
                                        
                                        $btn.prop('disabled', true).text('Saving...');
                                        
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'save_hudson_imgplanbatch_id',
                                                post_id: postId,
                                                hudson_id: hudsonId,
                                                nonce: $('#hurricane-nonce').val()
                                            },
                                            success: function(response) {
                                                if (response.success) {
                                                    alert('Hudson ImgPlanBatch ID saved successfully!');
                                                    
                                                    // Update the tebnar2 param button
                                                    var newUrl = 'http://localhost:3000/bin34/tebnar2?batchid=' + encodeURIComponent(hudsonId);
                                                    var buttonHtml = '<a href="' + newUrl + '" target="_blank" id="tebnar2-param-btn" class="button" style="display: inline-block; padding: 8px 16px; background: #f0ad4e; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500; border-color: #eea236;">go to /tebnar2 with param set</a>';
                                                    $('#tebnar2-param-btn').parent().html(buttonHtml);
                                                } else {
                                                    alert('Error saving ID: ' + (response.data || 'Unknown error'));
                                                }
                                                $btn.prop('disabled', false).text('Save');
                                            },
                                            error: function() {
                                                alert('AJAX error occurred');
                                                $btn.prop('disabled', false).text('Save');
                                            }
                                        });
                                    });
                                });
                                </script>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="driggs">
                        <!-- Link to rup_driggs_mar page -->
                        <div style="margin-bottom: 15px;">
                            <a href="<?php echo admin_url('admin.php?page=rup_driggs_mar'); ?>" 
                               class="button button-secondary" 
                               style="background: #0073aa; color: white; text-decoration: none; padding: 8px 16px; display: inline-block;">
                                Go to the rup_driggs_mar page
                            </a>
                        </div>
                        
                        <h2 style="font-weight: bold; font-size: 16px; margin-bottom: 15px;">driggs field collection 1</h2>
                        
                        <!-- Save Button -->
                        <div style="margin-bottom: 15px;">
                            <button id="save-driggs-btn" class="button button-primary">Save Changes</button>
                        </div>
                        
                        <!-- Driggs Fields Table -->
                        <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                            <div style="overflow-x: auto;">
                                <table id="driggs-stellar-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                    <thead style="background: #f8f9fa;">
                                        <tr>
                                            <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 250px;">Field Name</th>
                                            <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="driggs-stellar-table-body">
                                        <!-- Data will be loaded here via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div id="driggs-stellar-data" 
                             data-nonce="<?php echo wp_create_nonce('rup_driggs_nonce'); ?>"
                             data-initialized="false">
                        </div>
                        
                        <style type="text/css">
                        /* Stellar Toggle Switch Styles */
                        .stellar-driggs-toggle-switch {
                            position: relative;
                            display: inline-block;
                            width: 60px;
                            height: 34px;
                            cursor: pointer;
                        }
                        
                        .stellar-driggs-toggle-switch input[type="checkbox"] {
                            opacity: 0;
                            width: 0;
                            height: 0;
                        }
                        
                        .stellar-driggs-toggle-slider {
                            position: absolute;
                            cursor: pointer;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background-color: #ccc;
                            transition: .4s;
                            border-radius: 34px;
                            border: 1px solid #bbb;
                        }
                        
                        .stellar-driggs-toggle-knob {
                            position: absolute;
                            content: "";
                            height: 26px;
                            width: 26px;
                            left: 4px;
                            bottom: 3px;
                            background-color: white;
                            transition: .4s;
                            border-radius: 50%;
                            border: 1px solid #ddd;
                        }
                        
                        .stellar-driggs-toggle-switch input:checked + .stellar-driggs-toggle-slider {
                            background-color: #2196F3;
                            border-color: #1976D2;
                        }
                        
                        .stellar-driggs-toggle-switch input:checked + .stellar-driggs-toggle-slider .stellar-driggs-toggle-knob {
                            transform: translateX(26px);
                            border-color: #fff;
                        }
                        
                        .stellar-driggs-toggle-switch:hover .stellar-driggs-toggle-slider {
                            background-color: #b3b3b3;
                        }
                        
                        .stellar-driggs-toggle-switch input:checked:hover + .stellar-driggs-toggle-slider {
                            background-color: #1976D2;
                        }
                        
                        /* Editable field styles */
                        .stellareditable-field {
                            cursor: pointer;
                            min-height: 20px;
                            padding: 4px;
                            border-radius: 3px;
                            transition: background-color 0.2s;
                        }
                        
                        .stellareditable-field:hover {
                            background-color: #f0f8ff;
                            border: 1px dashed #2196F3;
                        }
                        
                        #driggs-stellar-table td {
                            vertical-align: middle;
                        }
                        </style>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="services">
                        <h2 class="snefuru-kepler-title">Kepler Services</h2>
                        <?php
                        // Generate the admin URL for rup_services_mar
                        $services_url = admin_url('admin.php?page=rup_services_mar');
                        ?>
                        <div class="snefuru-locations-button-container">
                            <a href="<?php echo esc_url($services_url); ?>" class="snefuru-locations-main-btn">
                                ?page=rup_services_mar
                            </a>
                            <button type="button" class="snefuru-locations-copy-btn" data-copy-url="<?php echo esc_attr($services_url); ?>" title="Copy link to clipboard">
                                📋
                            </button>
                        </div>
                        <!-- Additional services content will go here -->
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="locations">
                        <h2 class="snefuru-kepler-title">Kepler Locations</h2>
                        <?php
                        // Generate the admin URL for rup_locations_mar
                        $locations_url = admin_url('admin.php?page=rup_locations_mar');
                        ?>
                        <div class="snefuru-locations-button-container">
                            <a href="<?php echo esc_url($locations_url); ?>" class="snefuru-locations-main-btn">
                                ?page=rup_locations_mar
                            </a>
                            <button type="button" class="snefuru-locations-copy-btn" data-copy-url="<?php echo esc_attr($locations_url); ?>" title="Copy link to clipboard">
                                📋
                            </button>
                        </div>
                        <!-- Additional locations content will go here -->
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="kpages-schema">
                        <!-- Asteroid Support Content -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Asteroid Support Content -->
                                <div class="snefuru-asteroid-container" style="margin-bottom: 20px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: lowercase;">asteroid support</span>
                                    
                                    <textarea 
                                        id="asteroid-support-textbox" 
                                        readonly
                                        style="width: 100%; height: 120px; padding: 10px; border: 2px solid #e0e5eb; border-radius: 4px; font-family: monospace; font-size: 14px; line-height: 1.6; resize: vertical; background: #f9f9f9;"
                                    >k_
g_
w_
y_</textarea>
                                    
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-top: 15px; color: #333;">NoteToSelfKyle:database integration needed</span>
                                </div>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Hurricane Chamber Default State Setting -->
                                <div class="snefuru-stellar-chamber-settings" style="margin-bottom: 20px;">
                                    <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold;">Hurricane Chamber Default State</h4>
                                    <p style="margin: 0 0 10px 0; font-size: 12px; color: #666;">Set whether the Hurricane Chamber should be expanded or collapsed by default when editing posts/pages.</p>
                                    
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <label class="stellar-chamber-toggle-switch-editor">
                                            <input type="radio" 
                                                   name="ruplin_stellar_chamber_default_state_editor" 
                                                   value="expanded" 
                                                   <?php checked(get_option('ruplin_stellar_chamber_default_state', 'collapsed'), 'expanded'); ?> />
                                            <span class="stellar-chamber-toggle-slider-editor"></span>
                                            <span class="stellar-chamber-toggle-label-editor">
                                                <span class="expanded-label-editor">Expanded</span>
                                                <span class="collapsed-label-editor">Collapsed</span>
                                            </span>
                                        </label>
                                        <input type="radio" 
                                               name="ruplin_stellar_chamber_default_state_editor" 
                                               value="collapsed" 
                                               <?php checked(get_option('ruplin_stellar_chamber_default_state', 'collapsed'), 'collapsed'); ?> 
                                               style="display: none;" />
                                    </div>
                                    
                                    <button type="button" 
                                            class="stellar-chamber-save-setting-btn"
                                            style="margin-top: 10px; background: #0073aa; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-size: 12px;">
                                        Save Setting
                                    </button>
                                    
                                    <div class="stellar-chamber-message-editor" style="margin-top: 10px; display: none; padding: 8px; border-radius: 3px; font-size: 12px;"></div>
                                </div>
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="duplicate-kpage">
                        <div style="text-align: center; padding: 30px 20px;">
                            <h2 style="color: #0073aa; margin-bottom: 25px; font-size: 20px;">Duplicate Current Page/Post</h2>
                            
                            <!-- Duplicate Button Container -->
                            <div class="snefuru-duplicate-button-container" style="display: inline-flex; align-items: center; gap: 0; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0, 115, 170, 0.15); border-radius: 8px;">
                                <button 
                                    type="button" 
                                    id="snefuru-duplicate-page-btn"
                                    data-post-id="<?php echo esc_attr($post->ID); ?>"
                                    style="
                                        background: linear-gradient(135deg, #0073aa 0%, #005177 100%);
                                        color: white;
                                        border: none;
                                        padding: 15px 25px;
                                        font-size: 16px;
                                        font-weight: 600;
                                        border-radius: 8px 0 0 8px;
                                        cursor: pointer;
                                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                        position: relative;
                                        min-height: 54px;
                                        display: flex;
                                        align-items: center;
                                        gap: 8px;
                                        letter-spacing: 0.3px;
                                        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                                    "
                                    onmouseover="this.style.background='linear-gradient(135deg, #005177 0%, #003a52 100%)'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0, 115, 170, 0.25)'"
                                    onmouseout="this.style.background='linear-gradient(135deg, #0073aa 0%, #005177 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 115, 170, 0.15)'"
                                >
                                    <span style="font-size: 18px;">📄</span>
                                    <span>Duplicate This WP Page/Post Now</span>
                                </button>
                                
                                <button 
                                    type="button" 
                                    class="snefuru-copy-duplicate-text-btn" 
                                    title="Copy button text to clipboard"
                                    style="
                                        background: linear-gradient(135deg, #00a32a 0%, #007a1f 100%);
                                        color: white;
                                        border: none;
                                        width: 54px;
                                        height: 54px;
                                        border-radius: 0 8px 8px 0;
                                        cursor: pointer;
                                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-size: 16px;
                                    "
                                    onmouseover="this.style.background='linear-gradient(135deg, #007a1f 0%, #005a17 100%)'; this.style.transform='scale(1.05)'"
                                    onmouseout="this.style.background='linear-gradient(135deg, #00a32a 0%, #007a1f 100%)'; this.style.transform='scale(1)'"
                                >
                                    📋
                                </button>
                            </div>
                            
                            <!-- Go to rup_duplicate_mar Button -->
                            <div style="margin-top: 20px; text-align: center;">
                                <a href="<?php echo admin_url('admin.php?page=rup_duplicate_mar'); ?>" 
                                   target="_blank"
                                   class="snefuru-go-to-duplicate-mar-btn" 
                                   title="Go to bulk duplication page"
                                   style="
                                       display: inline-flex;
                                       align-items: center;
                                       gap: 8px;
                                       background: linear-gradient(135deg, #f5f5dc 0%, #e6e6cd 100%);
                                       color: #4a4a4a;
                                       border: 2px solid #d3d3d3;
                                       border-radius: 8px;
                                       padding: 12px 20px;
                                       font-weight: 600;
                                       text-decoration: none;
                                       font-size: 14px;
                                       cursor: pointer;
                                       transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                       box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                       text-transform: none;
                                       font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                                   "
                                   onmouseover="this.style.background='linear-gradient(135deg, #f0f0d8 0%, #e0e0c8 100%)'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0, 0, 0, 0.15)'"
                                   onmouseout="this.style.background='linear-gradient(135deg, #f5f5dc 0%, #e6e6cd 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.1)'"
                                >
                                    <span style="font-size: 16px;">🔗</span>
                                    <span>go to rup_duplicate_mar -></span>
                                </a>
                                
                                <button 
                                    type="button" 
                                    class="snefuru-copy-duplicate-mar-btn" 
                                    title="Copy link text to clipboard"
                                    style="
                                        background: linear-gradient(135deg, #00a32a 0%, #007a1f 100%);
                                        color: white;
                                        border: none;
                                        border-radius: 6px;
                                        width: 32px;
                                        height: 32px;
                                        margin-left: 8px;
                                        cursor: pointer;
                                        display: inline-flex;
                                        align-items: center;
                                        justify-content: center;
                                        transition: all 0.3s ease;
                                        box-shadow: 0 2px 6px rgba(0, 163, 42, 0.2);
                                    "
                                    onmouseover="this.style.background='linear-gradient(135deg, #00ba37 0%, #008a25 100%)'; this.style.transform='scale(1.05)'"
                                    onmouseout="this.style.background='linear-gradient(135deg, #00a32a 0%, #007a1f 100%)'; this.style.transform='scale(1)'"
                                >
                                    📋
                                </button>
                            </div>
                            
                            <!-- Status Display -->
                            <div id="snefuru-duplicate-status" style="display: none; margin-top: 15px; padding: 15px; border-radius: 8px; max-width: 600px; margin-left: auto; margin-right: auto;"></div>
                            
                            <!-- Result Link -->
                            <div id="snefuru-duplicate-result" style="display: none; margin-top: 20px; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; max-width: 600px; margin-left: auto; margin-right: auto;">
                                <h3 style="color: #155724; margin-top: 0; margin-bottom: 15px;">✅ Page Duplicated Successfully!</h3>
                                <div id="snefuru-duplicate-link-container"></div>
                            </div>
                        </div>
                        
                        <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            // Copy button text functionality
                            $('.snefuru-copy-duplicate-text-btn').on('click', function() {
                                var textToCopy = 'Duplicate This WP Page/Post Now';
                                var $btn = $(this);
                                
                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(textToCopy).then(function() {
                                        $btn.html('✅');
                                        setTimeout(function() {
                                            $btn.html('📋');
                                        }, 2000);
                                        console.log('Button text copied to clipboard');
                                    }).catch(function(err) {
                                        console.error('Clipboard write failed:', err);
                                    });
                                } else {
                                    // Fallback for older browsers
                                    var textarea = document.createElement('textarea');
                                    textarea.value = textToCopy;
                                    textarea.style.position = 'fixed';
                                    textarea.style.opacity = '0';
                                    document.body.appendChild(textarea);
                                    
                                    try {
                                        textarea.select();
                                        textarea.setSelectionRange(0, 99999);
                                        var successful = document.execCommand('copy');
                                        
                                        if (successful) {
                                            $btn.html('✅');
                                            setTimeout(function() {
                                                $btn.html('📋');
                                            }, 2000);
                                            console.log('Button text copied using fallback method');
                                        }
                                    } catch (err) {
                                        console.error('Fallback copy error:', err);
                                    } finally {
                                        document.body.removeChild(textarea);
                                    }
                                }
                            });
                            
                            // Duplicate page functionality
                            $('#snefuru-duplicate-page-btn').on('click', function() {
                                var $btn = $(this);
                                var $status = $('#snefuru-duplicate-status');
                                var $result = $('#snefuru-duplicate-result');
                                var postId = $btn.data('post-id');
                                
                                // Disable button and show processing
                                $btn.prop('disabled', true);
                                $btn.find('span:last-child').text('Duplicating...');
                                $btn.css('cursor', 'not-allowed');
                                
                                // Show status message
                                $status.removeClass('error').css({
                                    'background': '#fff3cd',
                                    'color': '#856404',
                                    'border': '1px solid #ffeaa7'
                                }).html('<strong>⏳ Processing:</strong> Saving current page and creating duplicate...').show();
                                
                                $result.hide();
                                
                                // Make AJAX call
                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'snefuru_duplicate_page',
                                        post_id: postId,
                                        nonce: '<?php echo wp_create_nonce('snefuru_duplicate_page_nonce'); ?>'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            $status.hide();
                                            $result.show();
                                            
                                            var editUrl = response.data.edit_url;
                                            var viewUrl = response.data.view_url;
                                            var title = response.data.duplicate_title;
                                            var postType = '<?php echo get_post_type(); ?>' || 'page';
                                            
                                            var linkHtml = '<div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">' +
                                                '<a href="' + editUrl + '" class="button button-primary" target="_blank" style="text-decoration: none;">' +
                                                '✏️ Edit New ' + (postType === 'post' ? 'Post' : 'Page') + '</a>' +
                                                '<a href="' + viewUrl + '" class="button button-secondary" target="_blank" style="text-decoration: none;">' +
                                                '👁️ View New ' + (postType === 'post' ? 'Post' : 'Page') + '</a>' +
                                                '</div>' +
                                                '<p style="margin-top: 15px; color: #155724; font-weight: 500;">New ' + (postType === 'post' ? 'post' : 'page') + ' title: <strong>"' + title + '"</strong></p>';
                                            
                                            $('#snefuru-duplicate-link-container').html(linkHtml);
                                        } else {
                                            $result.hide();
                                            $status.removeClass('success').css({
                                                'background': '#f8d7da',
                                                'color': '#721c24',
                                                'border': '1px solid #f5c6cb'
                                            }).html('<strong>❌ Error:</strong> ' + response.data).show();
                                        }
                                    },
                                    error: function() {
                                        $result.hide();
                                        $status.removeClass('success').css({
                                            'background': '#f8d7da',
                                            'color': '#721c24',
                                            'border': '1px solid #f5c6cb'
                                        }).html('<strong>❌ Error:</strong> Failed to duplicate page. Please try again.').show();
                                    },
                                    complete: function() {
                                        // Re-enable button
                                        $btn.prop('disabled', false);
                                        $btn.find('span:last-child').text('Duplicate This WP Page/Post Now');
                                        $btn.css('cursor', 'pointer');
                                    }
                                });
                            });
                            
                            // Copy rup_duplicate_mar button text functionality
                            $('.snefuru-copy-duplicate-mar-btn').on('click', function() {
                                var textToCopy = 'go to rup_duplicate_mar ->';
                                var $btn = $(this);
                                
                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(textToCopy).then(function() {
                                        $btn.html('✅');
                                        setTimeout(function() {
                                            $btn.html('📋');
                                        }, 2000);
                                        console.log('rup_duplicate_mar button text copied to clipboard');
                                    }).catch(function(err) {
                                        console.error('Clipboard write failed:', err);
                                    });
                                } else {
                                    // Fallback for older browsers
                                    var textarea = document.createElement('textarea');
                                    textarea.value = textToCopy;
                                    textarea.style.position = 'fixed';
                                    textarea.style.opacity = '0';
                                    document.body.appendChild(textarea);
                                    
                                    try {
                                        textarea.select();
                                        textarea.setSelectionRange(0, 99999);
                                        if (document.execCommand('copy')) {
                                            $btn.html('✅');
                                            setTimeout(function() {
                                                $btn.html('📋');
                                            }, 2000);
                                            console.log('rup_duplicate_mar button text copied using fallback method');
                                        }
                                    } catch (err) {
                                        console.error('Fallback copy error:', err);
                                    } finally {
                                        document.body.removeChild(textarea);
                                    }
                                }
                            });
                        });
                        </script>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="swipe15">
                        <!-- Swipe15 Content Container -->
                        <div class="snefuru-header303-db-mapping-container">
                            <div class="snefuru-header303-db-mapping-header">
                                <span class="snefuru-header303-db-mapping-label">swipe15 content</span>
                                <button type="button" class="snefuru-copy-btn" data-target="swipe15-textbox">
                                    Copy
                                </button>
                            </div>
                            <textarea 
                                id="swipe15-textbox" 
                                class="snefuru-header303-db-mapping-textbox" 
                                readonly
                            >———————————————————————
SPECIAL NOTE TO THE AI MODEL I'M CHATTING WITH:

In the following text content I paste below, you will be seeing the following:

(INSERT DESCRIPTION HERE)

———————————————————————</textarea>
                        </div>
                        
                        <!-- Separator Line -->
                        <hr style="margin: 30px 0; border: 0; border-top: 2px solid #333;">
                        
                        <!-- Quick Stats Section -->
                        <div class="snefuru-quick-stats-container" style="margin-top: 20px;">
                            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333;">Quick Stats For This Kpage</h3>
                            
                            <!-- Content area left blank for now -->
                            <div class="snefuru-quick-stats-content" style="min-height: 100px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                <!-- Stats content will be added here in the future -->
                            </div>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="gbp">
                        <!-- GBP content will go here -->
                    </div>
                </div>
            </div>
        </div>
        </div> <!-- End of stellar-collapsible-content -->
        
        <!-- Hidden nonce field for AJAX security -->
        <input type="hidden" id="hurricane-nonce" value="<?php echo wp_create_nonce('hurricane_nonce'); ?>" />
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Minimize/Expand Stellar Chamber functionality
            // Get default state from PHP setting
            var defaultState = '<?php echo esc_js(get_option('ruplin_stellar_chamber_default_state', 'collapsed')); ?>';
            var isStellarMinimized = (defaultState === 'collapsed');
            
            // Apply default state on page load
            if (isStellarMinimized) {
                var $content = $('#stellar-collapsible-content');
                var $arrow = $('#stellar-minimize-arrow');
                $content.hide(); // Start hidden
                $arrow.css('transform', 'rotate(-90deg)');
            }
            
            $('#stellar-minimize-btn').on('click', function() {
                var $content = $('#stellar-collapsible-content');
                var $arrow = $('#stellar-minimize-arrow');
                
                if (isStellarMinimized) {
                    // Expand
                    $content.slideDown(300);
                    $arrow.css('transform', 'rotate(0deg)');
                    isStellarMinimized = false;
                } else {
                    // Minimize
                    $content.slideUp(300);
                    $arrow.css('transform', 'rotate(-90deg)');
                    isStellarMinimized = true;
                }
                
                // Trigger custom event for state change
                $(document).trigger('stellarStateChanged');
            });
            
            // Load default tab on page load
            <?php 
            $default_tab = get_option('stellar_chamber_default_tab', 'elicitor');
            ?>
            var defaultTab = '<?php echo esc_js($default_tab); ?>';
            
            // Auto-activate the saved default tab if it's not the first tab
            if (defaultTab && defaultTab !== 'elicitor') {
                // Find the button with the matching data-tab attribute
                var $defaultTabBtn = $('.snefuru-stellar-tab-button[data-tab="' + defaultTab + '"]');
                if ($defaultTabBtn.length > 0) {
                    // Remove active class from all buttons and panels
                    $('.snefuru-stellar-tab-button').removeClass('active');
                    $('.snefuru-stellar-tab-panel').removeClass('active');
                    
                    // Add active class to the default tab button and its panel
                    $defaultTabBtn.addClass('active');
                    $('.snefuru-stellar-tab-panel[data-panel="' + defaultTab + '"]').addClass('active');
                }
            }
            
            // Handle "Save Current Tab As Default" button click
            $('#stellar-save-default-tab').on('click', function() {
                var $button = $(this);
                var $message = $('#stellar-save-tab-message');
                
                // Find the currently active tab
                var $activeTab = $('.snefuru-stellar-tab-button.active');
                if ($activeTab.length === 0) {
                    $message.text('No active tab found').css('color', '#ff6b6b').fadeIn();
                    setTimeout(function() {
                        $message.fadeOut();
                    }, 3000);
                    return;
                }
                
                var currentTab = $activeTab.data('tab');
                var tabLabel = $activeTab.text().trim();
                
                // Disable button and show loading state
                $button.prop('disabled', true).text('Saving...');
                $message.hide();
                
                // Send AJAX request to save the default tab
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_stellar_default_tab',
                        nonce: $('#hurricane-nonce').val(),
                        tab_value: currentTab
                    },
                    success: function(response) {
                        if (response.success) {
                            $message.text('Default tab set to: ' + tabLabel).css('color', '#4ade80').fadeIn();
                        } else {
                            $message.text(response.data || 'Error saving default tab').css('color', '#ff6b6b').fadeIn();
                        }
                    },
                    error: function() {
                        $message.text('Error saving default tab').css('color', '#ff6b6b').fadeIn();
                    },
                    complete: function() {
                        // Restore button state
                        $button.prop('disabled', false).text('Save Current Tab As Default');
                        
                        // Hide message after 3 seconds
                        setTimeout(function() {
                            $message.fadeOut();
                        }, 3000);
                    }
                });
            });
            
            // Handle "Save Current Expand State" button click
            $('#stellar-save-expand-state').on('click', function() {
                var $button = $(this);
                var $message = $('#stellar-save-tab-message');
                
                // Get the current expand/collapse state
                var currentState = isStellarMinimized ? 'collapsed' : 'expanded';
                
                // Disable button and show loading state
                $button.prop('disabled', true).text('Saving...');
                $message.hide();
                
                // Send AJAX request to save the expand state
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_stellar_chamber_setting',
                        nonce: '<?php echo wp_create_nonce('hurricane_nonce'); ?>',
                        default_state: currentState
                    },
                    success: function(response) {
                        if (response.success) {
                            $message.text('Default expand state set to: ' + currentState).css('color', '#4ade80').fadeIn();
                            // Update the saved state variable
                            savedExpandState = currentState;
                            // Update button state
                            updateExpandStateButton();
                        } else {
                            $message.text(response.data || 'Error saving expand state').css('color', '#ff6b6b').fadeIn();
                        }
                    },
                    error: function() {
                        $message.text('Error saving expand state').css('color', '#ff6b6b').fadeIn();
                    },
                    complete: function() {
                        // Restore button text
                        $button.text('Save Current Expand State');
                        
                        // Hide message after 3 seconds
                        setTimeout(function() {
                            $message.fadeOut();
                        }, 3000);
                    }
                });
            });
            
            // Track the saved expand state
            var savedExpandState = '<?php echo esc_js(get_option('ruplin_stellar_chamber_default_state', 'collapsed')); ?>';
            
            // Function to update the expand state button's enabled/disabled state
            function updateExpandStateButton() {
                var currentState = isStellarMinimized ? 'collapsed' : 'expanded';
                var $button = $('#stellar-save-expand-state');
                
                if (currentState === savedExpandState) {
                    // Current state matches saved state - disable button
                    $button.prop('disabled', true)
                           .css({
                               'background': '#94a3b8',
                               'cursor': 'not-allowed',
                               'opacity': '0.6'
                           })
                           .attr('title', 'Current state already saved as default');
                } else {
                    // States differ - enable button
                    $button.prop('disabled', false)
                           .css({
                               'background': '#2271b1',
                               'cursor': 'pointer',
                               'opacity': '1'
                           })
                           .attr('title', 'Save the current expand/collapse state as default');
                }
            }
            
            // Initial button state
            updateExpandStateButton();
            
            // Add handler to existing minimize button to update state
            $(document).on('stellarStateChanged', function() {
                updateExpandStateButton();
            });
            
            // Stellar Chamber Default State Toggle (Editor Version) 
            $('.stellar-chamber-toggle-switch-editor').on('click', function(e) {
                var $switch = $(this);
                var $radioExpanded = $switch.find('input[value="expanded"]');
                var $radioCollapsed = $switch.next('input[value="collapsed"]');
                
                // Toggle the radio buttons - expanded=checked means "expanded" state (blue/right)
                // and collapsed=checked means "collapsed" state (gray/left)
                if ($radioExpanded.is(':checked')) {
                    $radioExpanded.prop('checked', false);
                    $radioCollapsed.prop('checked', true);
                } else {
                    $radioCollapsed.prop('checked', false);
                    $radioExpanded.prop('checked', true);
                }
                
                // Prevent default radio button behavior
                e.preventDefault();
            });
            
            // Save Stellar Chamber Setting Button
            $('.stellar-chamber-save-setting-btn').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var $message = $('.stellar-chamber-message-editor');
                var selectedValue = $('input[name="ruplin_stellar_chamber_default_state_editor"]:checked').val();
                
                // Show loading state
                $button.prop('disabled', true).text('Saving...');
                $message.hide();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_stellar_chamber_setting',
                        nonce: $('#hurricane-nonce').val(),
                        setting_value: selectedValue
                    },
                    success: function(response) {
                        try {
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (data.success) {
                                $message.removeClass('error').addClass('success')
                                       .text('Setting saved successfully!').fadeIn();
                            } else {
                                $message.removeClass('success').addClass('error')
                                       .text(data.message || 'Error saving setting').fadeIn();
                            }
                        } catch (e) {
                            $message.removeClass('success').addClass('error')
                                   .text('Error processing response').fadeIn();
                        }
                    },
                    error: function() {
                        $message.removeClass('success').addClass('error')
                               .text('Error saving setting').fadeIn();
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Save Setting');
                        
                        // Hide message after 3 seconds
                        setTimeout(function() {
                            $message.fadeOut();
                        }, 3000);
                    }
                });
            });
            
            // Stellar Chamber Pantheon Table functionality
            var stellarSelectedCount = 0;
            
            function updateStellarSelectionCount() {
                stellarSelectedCount = $('.stellar-pantheon-checkbox:checked').length;
                $('#stellar-pantheon-count').text(stellarSelectedCount + ' selected');
                $('#stellar-pantheon-process').prop('disabled', stellarSelectedCount === 0);
            }
            
            // Handle individual checkbox changes
            $(document).on('change', '.stellar-pantheon-checkbox', function() {
                var $row = $(this).closest('tr');
                if ($(this).is(':checked')) {
                    $row.css('background-color', '#e8f4f8');
                } else {
                    $row.css('background-color', '');
                }
                updateStellarSelectionCount();
            });
            
            // Handle select all checkbox
            $('#stellar-pantheon-select-all').change(function() {
                var checked = $(this).is(':checked');
                $('#stellar-pantheon-table tbody tr:visible .stellar-pantheon-checkbox').each(function() {
                    $(this).prop('checked', checked);
                    var $row = $(this).closest('tr');
                    if (checked) {
                        $row.css('background-color', '#e8f4f8');
                    } else {
                        $row.css('background-color', '');
                    }
                });
                updateStellarSelectionCount();
            });
            
            // Search functionality
            $('#stellar-pantheon-search').on('input', function() {
                filterStellarTable();
            });
            
            // Clear search button
            $('#stellar-pantheon-clear').click(function() {
                $('#stellar-pantheon-search').val('');
                filterStellarTable();
            });
            
            // Filter table function
            function filterStellarTable() {
                var searchText = $('#stellar-pantheon-search').val().toLowerCase();
                
                $('#stellar-pantheon-table tbody tr').each(function() {
                    var $row = $(this);
                    var description = $row.attr('data-description').toLowerCase();
                    
                    var matchesSearch = searchText === '' || description.includes(searchText);
                    
                    if (matchesSearch) {
                        $row.show();
                    } else {
                        $row.hide();
                        // Uncheck hidden rows
                        $row.find('.stellar-pantheon-checkbox').prop('checked', false);
                        $row.css('background-color', '');
                    }
                });
                
                updateStellarSelectionCount();
            }
            
            // Handle process selected button
            $('#stellar-pantheon-process').click(function() {
                var selectedItems = [];
                $('.stellar-pantheon-checkbox:checked').each(function() {
                    selectedItems.push({
                        value: $(this).val(),
                        description: $(this).closest('tr').find('td:nth-child(2) strong').text(),
                        type: $(this).closest('tr').attr('data-type')
                    });
                });
                
                if (selectedItems.length === 0) {
                    alert('Please select at least one item to process.');
                    return;
                }
                
                // Show processing message
                var $statusDiv = $('#stellar-pantheon-messages');
                $statusDiv.html('<div style="background: #d4edda; color: #155724; padding: 8px; border: 1px solid #c3e6cb; border-radius: 4px; font-size: 12px;">🔄 Processing ' + selectedItems.length + ' selected items...</div>');
                
                // Simulate processing (replace with actual AJAX call)
                setTimeout(function() {
                    var successHtml = '<div style="background: #d1eddb; color: #155724; padding: 8px; border: 1px solid #c3e6cb; border-radius: 4px; font-size: 12px;">✅ Successfully processed ' + selectedItems.length + ' items:<br>';
                    selectedItems.forEach(function(item) {
                        successHtml += '• ' + item.description + ' (' + item.value + ')<br>';
                    });
                    successHtml += '</div>';
                    $statusDiv.html(successHtml);
                }, 2000);
            });
            
            // Initialize
            updateStellarSelectionCount();
        });
        </script>
        <?php
    }
    
    /**
     * Render Stellar Chamber as standalone component for dioptra page
     */
    public function render_stellar_chamber_standalone($post) {
        // This method simply calls the original add_stellar_chamber
        // The original method has a type check, but we'll bypass it by temporarily
        // modifying the post type if needed
        
        // Store original post type
        $original_post_type = $post->post_type;
        
        // Temporarily set post type to 'post' or 'page' to pass the check
        if (!in_array($post->post_type, array('post', 'page'))) {
            $post->post_type = 'post';
        }
        
        // Call the original method
        $this->add_stellar_chamber($post);
        
        // Restore original post type
        $post->post_type = $original_post_type;
    }
    
    /**
     * Add Hurricane metabox to post/page edit screens
     */
    public function add_hurricane_metabox() {
        // Get current screen
        $screen = get_current_screen();
        
        // Only add to post and page edit screens
        if ($screen && in_array($screen->base, array('post')) && in_array($screen->post_type, array('post', 'page'))) {
            add_meta_box(
                'snefuru-hurricane',
                'Tsunami',
                array($this, 'render_hurricane_metabox'),
                array('post', 'page'),
                'side', // This places it in the sidebar (right side)
                'high'  // This places it at the top of the sidebar
            );
        }
    }
    
    /**
     * Render the Hurricane metabox content
     */
    public function render_hurricane_metabox($post) {
        ?>
        <div class="snefuru-hurricane-container">
            <div class="snefuru-hurricane-content">
                Tsunami
            </div>
            <div class="snefuru-hurricane-controls">
                <button type="button" class="button button-primary snefuru-lightning-popup-btn" onclick="window.snefuruOpenLightningPopup()">
                    ⚡ Lightning Popup
                </button>
                <button type="button" class="button button-primary snefuru-thunder-popup-btn" onclick="window.snefuruOpenThunderPopup()" style="margin-top: 8px;">
                    ⛈️ Thunder Button (papyrus)
                </button>
            </div>
        </div>
        
        <!-- Lightning Popup Modal -->
        <div id="snefuru-lightning-popup" class="snefuru-popup-overlay" style="display: none;">
            <div class="snefuru-popup-container">
                <div class="snefuru-popup-header">
                    <h2 class="snefuru-popup-title">Lightning Popup</h2>
                    <button type="button" class="snefuru-popup-close" onclick="window.snefuruCloseLightningPopup()">&times;</button>
                </div>
                <div class="snefuru-popup-content">
                    <?php
                    // Hook for other plugins/features to add content
                    do_action('ruplin_lightning_popup_content');
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Thunder Popup Modal (Papyrus) -->
        <div id="snefuru-thunder-popup" class="snefuru-popup-overlay" style="display: none;">
            <div class="snefuru-popup-container">
                <div class="snefuru-popup-header">
                    <h2 class="snefuru-popup-title">Thunder (Papyrus)</h2>
                    <button type="button" class="snefuru-popup-close">&times;</button>
                </div>
                <div class="snefuru-popup-content">
                    <!-- Tab Navigation -->
                    <div class="snefuru-thunder-tabs">
                        <button type="button" class="thunder-tab-btn active" data-tab="papyrus-page-level">wp_zen_orbitposts.<br>papyrus_page_level_insert</button>
                        <button type="button" class="thunder-tab-btn" data-tab="grove-vault-papyrus">grove_vault_papyrus_1</button>
                        <button type="button" class="thunder-tab-btn" data-tab="grove-vault-raw">grove_vault_papyrus_1 (raw)</button>
                        <button type="button" class="thunder-tab-btn" data-tab="grove-vault-rendered">
                            <span style="color: red; cursor: help; margin-right: 5px;" 
                                  title="WARNING: the logic involved here in the RUPLIN plugin may be DUPLICATED from the GROVE plugin's grove_papyrus_hub page (prone to errors when updating - must remember to update both)"
                                  onclick="event.stopPropagation(); alert('WARNING: the logic involved here in the RUPLIN plugin may be DUPLICATED from the GROVE plugin\'s grove_papyrus_hub page (prone to errors when updating - must remember to update both)'); return false;">
                                ⚠️
                            </span>grove_vault_papyrus_1 (rendered)
                        </button>
                        <button type="button" class="thunder-tab-btn" data-tab="thunder-tab2">tab 2</button>
                        <button type="button" class="thunder-tab-btn" data-tab="thunder-tab3">tab 3</button>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="thunder-tab-content">
                        <!-- Papyrus Page Level Insert Tab -->
                        <div id="papyrus-page-level-content" class="thunder-tab-pane active">
                            <div style="margin-bottom: 10px;">
                                <label style="font-weight: bold; display: block; margin-bottom: 5px;">
                                    Papyrus Page Level Insert
                                    <span style="font-weight: normal; color: #666; font-size: 12px; margin-left: 10px;">
                                        (zen_orbitposts.papyrus_page_level_insert)
                                    </span>
                                </label>
                                <textarea id="papyrus-page-level-textarea" 
                                          style="width: 100%; height: 400px; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                          placeholder="Enter papyrus page level insert content..."><?php
                                    // Get current post ID and load existing content
                                    global $post, $wpdb;
                                    if ($post && $post->ID) {
                                        $table_name = $wpdb->prefix . 'zen_orbitposts';
                                        $orbitpost = $wpdb->get_row($wpdb->prepare(
                                            "SELECT papyrus_page_level_insert FROM $table_name WHERE rel_wp_post_id = %d",
                                            $post->ID
                                        ));
                                        if ($orbitpost && $orbitpost->papyrus_page_level_insert) {
                                            echo esc_textarea($orbitpost->papyrus_page_level_insert);
                                        }
                                    }
                                ?></textarea>
                            </div>
                            <div style="text-align: right;">
                                <button type="button" class="button button-primary save-thunder-papyrus-btn">
                                    Save Papyrus Data
                                </button>
                            </div>
                        </div>
                        
                        <!-- Grove Vault Papyrus 1 Tab -->
                        <div id="grove-vault-papyrus-content" class="thunder-tab-pane" style="display: none;">
                            <div style="margin-bottom: 10px;">
                                <label style="font-weight: bold; display: block; margin-bottom: 5px;">
                                    Grove Vault: Papyrus 1
                                    <span style="font-weight: normal; color: #666; font-size: 12px; margin-left: 10px;">
                                        (Read-only from Grove vault system)
                                    </span>
                                </label>
                                <textarea style="width: 100%; height: 400px; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5;"
                                          readonly><?php
                                    // Retrieve content from Grove vault
                                    if (function_exists('grove_vault')) {
                                        $vault_content = grove_vault('papyrus/papyrus-1');
                                        if ($vault_content) {
                                            echo esc_textarea($vault_content);
                                        } else {
                                            echo '// Grove vault content not available. Ensure Grove plugin is active.';
                                        }
                                    } else if (class_exists('Grove_Vault_Keeper')) {
                                        // Fallback to direct class access
                                        $vault_content = Grove_Vault_Keeper::retrieve('papyrus/papyrus-1');
                                        if ($vault_content) {
                                            echo esc_textarea($vault_content);
                                        } else {
                                            echo '// Unable to retrieve vault content.';
                                        }
                                    } else {
                                        echo '// Grove plugin not active. Cannot retrieve vault content.';
                                    }
                                ?></textarea>
                            </div>
                            <div style="text-align: right; color: #666; font-size: 12px;">
                                <em>This content is stored in Grove vault: /grove/vaults/papyrus/papyrus-1.vault.php</em>
                            </div>
                        </div>
                        
                        <!-- Grove Vault Papyrus 1 Raw Tab -->
                        <div id="grove-vault-raw-content" class="thunder-tab-pane" style="display: none;">
                            <div style="margin-bottom: 10px;">
                                <label style="font-weight: bold; display: block; margin-bottom: 5px;">
                                    Grove Vault: Papyrus 1 (Raw)
                                    <span style="font-weight: normal; color: #666; font-size: 12px; margin-left: 10px;">
                                        (Raw template from Grove vault)
                                    </span>
                                </label>
                                <textarea style="width: 100%; height: 400px; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5;"
                                          readonly><?php
                                    // Retrieve RAW content from Grove vault
                                    if (function_exists('grove_vault')) {
                                        $vault_raw_content = grove_vault('papyrus/papyrus-1');
                                        if ($vault_raw_content) {
                                            echo esc_textarea($vault_raw_content);
                                        } else {
                                            echo '// Grove vault content not available. Ensure Grove plugin is active.';
                                        }
                                    } else if (class_exists('Grove_Vault_Keeper')) {
                                        $vault_raw_content = Grove_Vault_Keeper::retrieve('papyrus/papyrus-1');
                                        if ($vault_raw_content) {
                                            echo esc_textarea($vault_raw_content);
                                        } else {
                                            echo '// Unable to retrieve vault content.';
                                        }
                                    } else {
                                        echo '// Grove plugin not active. Cannot retrieve vault content.';
                                    }
                                ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Grove Vault Papyrus 1 Rendered Tab -->
                        <div id="grove-vault-rendered-content" class="thunder-tab-pane" style="display: none;">
                            <div style="margin-bottom: 10px;">
                                <label style="font-weight: bold; display: block; margin-bottom: 5px;">
                                    Grove Vault: Papyrus 1 (Rendered)
                                    <span style="font-weight: normal; color: #666; font-size: 12px; margin-left: 10px;">
                                        (With database values inserted for this post)
                                    </span>
                                </label>
                                <textarea style="width: 100%; height: 400px; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5;"
                                          readonly><?php
                                    // Get the raw vault content first
                                    $rendered_content = '';
                                    if (function_exists('grove_vault')) {
                                        $rendered_content = grove_vault('papyrus/papyrus-1');
                                    } else if (class_exists('Grove_Vault_Keeper')) {
                                        $rendered_content = Grove_Vault_Keeper::retrieve('papyrus/papyrus-1');
                                    }
                                    
                                    if ($rendered_content) {
                                        global $post, $wpdb;
                                        
                                        // Replace SITE-LEVEL driggs data
                                        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
                                        $sitespren_data = $wpdb->get_row("SELECT * FROM $sitespren_table LIMIT 1", ARRAY_A);
                                        
                                        $flag1_columns = array(
                                            'sitespren_base',
                                            'driggs_brand_name',
                                            'driggs_phone_1',
                                            'driggs_city',
                                            'driggs_state_code',
                                            'driggs_industry',
                                            'driggs_site_type_purpose',
                                            'driggs_email_1'
                                        );
                                        
                                        $site_level_text = '';
                                        foreach ($flag1_columns as $column) {
                                            $value = isset($sitespren_data[$column]) ? $sitespren_data[$column] : '';
                                            $site_level_text .= $column . "\t" . $value . "\n";
                                        }
                                        $site_level_text = trim($site_level_text);
                                        
                                        // Replace site-level placeholder
                                        $search_site = '(INSERT HERE - MAIN SITE LEVEL DRIGGS DATA - flag1 ai chat for content generation)';
                                        $rendered_content = str_replace($search_site, $site_level_text, $rendered_content);
                                        
                                        // Replace PAGE-LEVEL papyrus_page_level_insert data
                                        $page_level_text = '';
                                        if ($post && $post->ID) {
                                            $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
                                            $orbitpost = $wpdb->get_row($wpdb->prepare(
                                                "SELECT papyrus_page_level_insert FROM $orbitposts_table WHERE rel_wp_post_id = %d",
                                                $post->ID
                                            ));
                                            if ($orbitpost && $orbitpost->papyrus_page_level_insert) {
                                                $page_level_text = $orbitpost->papyrus_page_level_insert;
                                            } else {
                                                $page_level_text = '// No papyrus_page_level_insert data for this post';
                                            }
                                        } else {
                                            $page_level_text = '// Post ID not available';
                                        }
                                        
                                        // Replace page-level placeholder
                                        $search_page = '[INSERT DB COLUMN VALUE FROM orbitposts.papyrus_page_level_insert]';
                                        $rendered_content = str_replace($search_page, $page_level_text, $rendered_content);
                                        
                                        echo esc_textarea($rendered_content);
                                    } else {
                                        echo '// Grove vault content not available.';
                                    }
                                ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Tab 2 -->
                        <div id="thunder-tab2-content" class="thunder-tab-pane" style="display: none;">
                            <div style="margin-bottom: 10px;">
                                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Tab 2 Content</label>
                                <textarea style="width: 100%; height: 400px; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                          placeholder="Tab 2 content (not connected to database)..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Tab 3 -->
                        <div id="thunder-tab3-content" class="thunder-tab-pane" style="display: none;">
                            <div style="margin-bottom: 10px;">
                                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Tab 3 Content</label>
                                <textarea style="width: 100%; height: 400px; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                          placeholder="Tab 3 content (not connected to database)..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Inline Thunder Functions for reliability -->
        <script type="text/javascript">
        // Define Thunder functions inline to ensure they're available
        if (typeof window.snefuruOpenThunderPopup === 'undefined') {
            window.snefuruOpenThunderPopup = function() {
                console.log('Opening thunder popup (inline)...');
                var popup = jQuery('#snefuru-thunder-popup');
                if (popup.length) {
                    popup.show().fadeIn(300);
                    jQuery('body').addClass('snefuru-popup-open');
                    console.log('Thunder popup opened successfully');
                    
                    // Initialize tab handlers when popup opens
                    initThunderTabHandlers();
                } else {
                    console.error('Thunder popup element not found!');
                }
            };
        }
        
        if (typeof window.snefuruCloseThunderPopup === 'undefined') {
            window.snefuruCloseThunderPopup = function() {
                console.log('Closing thunder popup (inline)...');
                jQuery('#snefuru-thunder-popup').fadeOut(300);
                jQuery('body').removeClass('snefuru-popup-open');
            };
        }
        
        // Initialize Thunder tab handlers
        function initThunderTabHandlers() {
            // Remove any existing handlers first
            jQuery('.thunder-tab-btn').off('click.thunder-tabs');
            
            // Add tab switching functionality
            jQuery('.thunder-tab-btn').on('click.thunder-tabs', function(e) {
                e.preventDefault();
                var tabId = jQuery(this).data('tab');
                console.log('Switching to tab:', tabId);
                
                // Update active states
                jQuery('.thunder-tab-btn').removeClass('active');
                jQuery(this).addClass('active');
                
                // Switch content
                jQuery('.thunder-tab-pane').hide();
                jQuery('#' + tabId + '-content').show();
                
                // If switching to rendered tab, update the content dynamically
                if (tabId === 'grove-vault-rendered') {
                    updateRenderedContent();
                }
            });
            
            // Remove existing save handlers and add new one
            jQuery('.save-thunder-papyrus-btn').off('click.save-thunder');
            jQuery('.save-thunder-papyrus-btn').on('click.save-thunder', function(e) {
                e.preventDefault();
                console.log('Save button clicked');
                saveThunderPapyrusData();
            });
            
            // Close button handler
            jQuery('#snefuru-thunder-popup .snefuru-popup-close').off('click.thunder-close');
            jQuery('#snefuru-thunder-popup .snefuru-popup-close').on('click.thunder-close', function(e) {
                e.preventDefault();
                window.snefuruCloseThunderPopup();
            });
        }
        
        // Define save function if not exists
        if (typeof window.saveThunderPapyrusData === 'undefined') {
            window.saveThunderPapyrusData = function() {
                var content = jQuery('#papyrus-page-level-textarea').val();
                var postId = <?php echo get_the_ID() ? get_the_ID() : '0'; ?>;
                
                console.log('Saving papyrus data for post:', postId);
                
                // Show saving indicator
                jQuery('.save-thunder-papyrus-btn').text('Saving...').prop('disabled', true);
                
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_thunder_papyrus_data',
                        nonce: '<?php echo wp_create_nonce('hurricane_nonce'); ?>',
                        post_id: postId,
                        papyrus_content: content
                    },
                    success: function(response) {
                        jQuery('.save-thunder-papyrus-btn').text('Save Papyrus Data').prop('disabled', false);
                        if (response.success) {
                            console.log('Papyrus data saved successfully');
                            // Update the rendered content if that tab exists
                            updateRenderedContent();
                            // Show success message
                            alert('Papyrus data saved successfully!');
                        } else {
                            console.error('Failed to save papyrus data:', response.data);
                            alert('Failed to save papyrus data: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        jQuery('.save-thunder-papyrus-btn').text('Save Papyrus Data').prop('disabled', false);
                        console.error('AJAX error:', error);
                        alert('Error saving papyrus data: ' + error);
                    }
                });
            };
        }
        
        // Function to update rendered content dynamically
        function updateRenderedContent() {
            var textarea = jQuery('#grove-vault-rendered-content textarea');
            if (textarea.length === 0) return;
            
            // Get the current papyrus_page_level_insert value from the editable field
            var currentPapyrusContent = jQuery('#papyrus-page-level-textarea').val() || '// No papyrus_page_level_insert data for this post';
            
            // Get the raw vault content (stored in a data attribute or fetch it)
            var rawContent = jQuery('#grove-vault-raw-content textarea').val();
            
            if (!rawContent) {
                console.log('No raw content available');
                return;
            }
            
            // Replace the placeholder with the current value
            var renderedContent = rawContent;
            
            // Replace site-level placeholder with the actual driggs data
            var siteLevelData = <?php 
                global $wpdb;
                $sitespren_table = $wpdb->prefix . 'zen_sitespren';
                $sitespren_data = $wpdb->get_row("SELECT * FROM $sitespren_table LIMIT 1", ARRAY_A);
                
                $flag1_columns = array(
                    'sitespren_base',
                    'driggs_brand_name',
                    'driggs_phone_1',
                    'driggs_city',
                    'driggs_state_code',
                    'driggs_industry',
                    'driggs_site_type_purpose',
                    'driggs_email_1'
                );
                
                $site_level_text = '';
                foreach ($flag1_columns as $column) {
                    $value = isset($sitespren_data[$column]) ? $sitespren_data[$column] : '';
                    $site_level_text .= $column . "\t" . $value . "\n";
                }
                $site_level_text = trim($site_level_text);
                echo json_encode($site_level_text);
            ?>;
            
            var searchSite = '(INSERT HERE - MAIN SITE LEVEL DRIGGS DATA - flag1 ai chat for content generation)';
            renderedContent = renderedContent.replace(searchSite, siteLevelData);
            
            // Replace page-level placeholder
            var searchPage = '[INSERT DB COLUMN VALUE FROM orbitposts.papyrus_page_level_insert]';
            renderedContent = renderedContent.replace(searchPage, currentPapyrusContent);
            
            // Update the textarea
            textarea.val(renderedContent);
            console.log('Rendered content updated');
        }
        
        // Initialize handlers on document ready
        jQuery(document).ready(function() {
            // Also initialize when Thunder popup is opened via any method
            jQuery(document).on('click', '.snefuru-thunder-popup-btn', function() {
                setTimeout(initThunderTabHandlers, 100);
            });
        });
        </script>
        
        <!-- Add Thunder-specific styles -->
        <style>
        .snefuru-thunder-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        
        .thunder-tab-btn {
            padding: 8px 16px;
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px 4px 0 0;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .thunder-tab-btn:hover {
            background: #e0e0e0;
        }
        
        .thunder-tab-btn.active {
            background: #0073aa;
            color: white;
            border-color: #0073aa;
        }
        
        .thunder-tab-pane {
            padding: 10px;
        }
        </style>
        <?php
    }
    
    /**
     * Enqueue Hurricane CSS and JS assets
     */
    public function enqueue_hurricane_assets($hook) {
        // Only load on post/page edit screens
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        // Get current screen to check post type
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, array('post', 'page'))) {
            return;
        }
        
        wp_enqueue_style(
            'snefuru-hurricane-css',
            SNEFURU_PLUGIN_URL . 'hurricane/assets/hurricane.css',
            array(),
            SNEFURU_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'snefuru-hurricane-js',
            SNEFURU_PLUGIN_URL . 'hurricane/assets/hurricane.js',
            array('jquery'),
            SNEFURU_PLUGIN_VERSION,
            true
        );
        
        // Add inline script to test if assets are loading
        wp_add_inline_script('snefuru-hurricane-js', '
            console.log("Hurricane JS loaded successfully");
            console.log("jQuery version:", jQuery.fn.jquery);
        ');
        
        // Localize script with AJAX URL and nonce
        wp_localize_script('snefuru-hurricane-js', 'snefuruHurricane', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hurricane_nonce'),
            'post_id' => get_the_ID()
        ));
    }
    
    /**
     * Add admin menu for developer info
     */
    public function add_admin_menu() {
        add_menu_page(
            'Cobalt Developer Info',
            'Cobalt Dev Info',
            'manage_options',
            'cobalt_developer_info',
            array($this, 'cobalt_developer_info_page'),
            'dashicons-info',
            999
        );
    }
    
    /**
     * Display the cobalt developer info page
     */
    public function cobalt_developer_info_page() {
        ?>
        <div class="wrap">
            <h1>Cobalt Function - Developer Information</h1>
            <p><strong>Updated: 2025_10_10</strong></p>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-top: 20px;">
                <h2>Cobalt Function Debug Report - Complete Solution</h2>
                
                <h3>Project: WordPress Elementor Widget Content Update System</h3>
                <p><strong>Date:</strong> October 10, 2025<br>
                <strong>Status:</strong> ✅ RESOLVED</p>
                
                <hr>
                
                <h3>Overview</h3>
                <p>The Cobalt function was designed to update specific Elementor widgets by their internal reference IDs (==widget1, ==widget2, etc.) but was failing to persist changes to the frontend despite reporting success.</p>
                
                <h3>Problems Encountered & Solutions</h3>
                
                <h4>1. ❌ AJAX Handler Not Being Called</h4>
                <p><strong>Problem:</strong> Initial attempts showed no AJAX response or debug logging<br>
                <strong>Root Cause:</strong> JavaScript was using <code>ajaxurl</code> but WordPress was localizing it as <code>snefuruHurricane.ajaxurl</code><br>
                <strong>Solution:</strong></p>
                <ul>
                    <li>Updated JavaScript to use <code>snefuruHurricane.ajaxurl</code></li>
                    <li>Fixed nonce mismatch between <code>hurricane_nonce</code> (expected) and <code>snefuru_hurricane_nonce</code> (provided)</li>
                </ul>
                
                <h4>2. ❌ JSON Corruption Breaking Page Styling</h4>
                <p><strong>Problem:</strong> After successful widget updates, page styling would break due to corrupted JSON<br>
                <strong>Root Cause:</strong> WordPress <code>wp_slash()</code> function was corrupting the JSON structure<br>
                <strong>Solutions Applied:</strong></p>
                <ul>
                    <li>Added JSON validation before and after encoding</li>
                    <li>Used proper JSON encoding flags: <code>JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES</code></li>
                    <li>Implemented content sanitization with <code>sanitize_content_for_json()</code> function</li>
                    <li>Added fallback to save without <code>wp_slash()</code> when corruption detected</li>
                    <li>Created emergency reset system with <code>RESET_ELEMENTOR_DATA</code> command</li>
                </ul>
                
                <h4>3. ❌ Database Save Failures</h4>
                <p><strong>Problem:</strong> <code>update_post_meta()</code> was returning FALSE, causing save failures<br>
                <strong>Root Cause:</strong> Multiple meta entries or WordPress hooks interfering with saves<br>
                <strong>Solution:</strong> Implemented multi-tier save approach:</p>
                <ol>
                    <li>Try <code>update_post_meta()</code></li>
                    <li>Fall back to direct <code>$wpdb->update()</code></li>
                    <li>Last resort: delete specific entry and add new one</li>
                </ol>
                
                <h4>4. ❌ <strong>CRITICAL: Changes Not Persisting (The Real Bug)</strong></h4>
                <p><strong>Problem:</strong> Widgets reported successful updates, JSON showed identical length, no actual changes saved<br>
                <strong>Root Cause:</strong> <strong>PHP pass-by-reference issue</strong> - The main processing function was not passing elements by reference<br>
                <strong>The Fix:</strong> Changed function signature from:</p>
                <pre><code>process_elementor_element_cobalt($element, ...)  // ❌ Works on copy</code></pre>
                <p>to:</p>
                <pre><code>process_elementor_element_cobalt(&$element, ...) // ✅ Works on actual element</code></pre>
                
                <h3>Final Working Evidence</h3>
                <p><strong>Before Fix:</strong> JSON length remained 14384 (no changes)<br>
                <strong>After Fix:</strong></p>
                <ul>
                    <li>JSON length changed from 14384 → 14302 (actual changes)</li>
                    <li><code>update_post_meta result: TRUE</code></li>
                    <li><code>Contains "drain": YES</code></li>
                    <li><code>Contains "Amazing Plumbing People": YES</code></li>
                    <li>Frontend content successfully updated</li>
                </ul>
                
                <h3>Key Files Modified</h3>
                <ol>
                    <li><code>/hurricane/class-cobalt.php</code> - Main logic and pass-by-reference fix</li>
                    <li><code>/hurricane/assets/hurricane.js</code> - AJAX URL and nonce fixes</li>
                    <li><code>/hurricane/class-hurricane.php</code> - UI improvements and reset functionality</li>
                </ol>
                
                <h3>Implementation Features</h3>
                <ul>
                    <li><strong>Backup System:</strong> Automatic backup creation before changes</li>
                    <li><strong>Emergency Reset:</strong> <code>RESET_ELEMENTOR_DATA</code> command with UI helper</li>
                    <li><strong>Comprehensive Debugging:</strong> Detailed logging throughout the process</li>
                    <li><strong>Multi-tier Save Strategy:</strong> Handles various database save scenarios</li>
                    <li><strong>JSON Validation:</strong> Prevents corruption before saving</li>
                    <li><strong>Content Sanitization:</strong> Removes problematic characters</li>
                </ul>
                
                <h3>Lessons Learned</h3>
                <ol>
                    <li><strong>PHP References Critical:</strong> Always use <code>&$variable</code> for functions that modify array structures</li>
                    <li><strong>WordPress Localization:</strong> Match JavaScript variable names exactly with <code>wp_localize_script()</code></li>
                    <li><strong>JSON Encoding:</strong> Use proper flags and validate before/after encoding</li>
                    <li><strong>Debugging Strategy:</strong> Log at every step to isolate the exact failure point</li>
                    <li><strong>WordPress wp_slash():</strong> Can corrupt JSON - always test decode after applying</li>
                </ol>
                
                <h3>Success Metrics</h3>
                <ul>
                    <li>✅ AJAX handler properly called</li>
                    <li>✅ Widget content successfully parsed and mapped</li>
                    <li>✅ Individual widget updates working correctly</li>
                    <li>✅ Changes persisting in memory (pass-by-reference fix)</li>
                    <li>✅ JSON encoding without corruption</li>
                    <li>✅ Database saves successful</li>
                    <li>✅ Frontend content updates visible</li>
                    <li>✅ Page styling remains intact</li>
                    <li>✅ Emergency recovery system functional</li>
                </ul>
                
                <p><strong>Final Result:</strong> Cobalt function now successfully updates Elementor widget content and persists changes to the frontend.</p>
            </div>
        </div>
        
        <style>
        .wrap h2, .wrap h3, .wrap h4 {
            color: #23282d;
            margin-top: 20px;
        }
        .wrap pre {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        .wrap code {
            background: #f1f1f1;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
        .wrap ul, .wrap ol {
            margin-left: 20px;
        }
        .wrap li {
            margin-bottom: 5px;
        }
        </style>
        <?php
    }
    
    /**
     * AJAX handler for saving the default stellar chamber tab
     * 
     * NOTE FOR FUTURE TAB RENAMING:
     * If you rename tab labels in the UI, you must also update the corresponding
     * saved values in the database. The stellar_chamber_default_tab option stores
     * the data-tab attribute values (e.g., 'elicitor', 'elementor', 'elementor-deployer-2').
     * 
     * Tab mapping reference:
     * - 'elicitor' => "Elementor Elicitor"
     * - 'elementor' => "Elementor Deployer 1"
     * - 'elementor-deployer-2' => "Elementor Deployer 2"
     * - 'old-elementor-elicitor' => "Old Elementor Elicitor 1"
     * - 'old-elementor-elicitor-2' => "Old Elementor Elicitor 2"
     * - 'gutenberg' => "Gutenberg Elicitor"
     * - 'gut-deployer' => "Gut. Deployer"
     * - 'nimble' => "Nimble Elicitor"
     * - 'nimble-deployer' => "Nimble Deployer"
     * 
     * When renaming tabs, ensure the data-tab attribute remains consistent
     * or update existing saved values in the database accordingly.
     */
    public function ajax_save_stellar_default_tab() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Get the tab value from the request
        $tab_value = isset($_POST['tab_value']) ? sanitize_text_field($_POST['tab_value']) : '';
        
        // Validate tab value - must match one of our known tab identifiers
        $valid_tabs = array(
            'elicitor',
            'elementor',
            'elementor-deployer-2',
            'old-elementor-elicitor',
            'old-elementor-elicitor-2',
            'gutenberg',
            'gut-deployer',
            'nimble',
            'nimble-deployer'
        );
        
        if (!in_array($tab_value, $valid_tabs)) {
            wp_send_json_error('Invalid tab value');
        }
        
        // Save the option to the database
        update_option('stellar_chamber_default_tab', $tab_value);
        
        // Return success response with the saved tab
        wp_send_json_success(array(
            'message' => 'Default tab saved successfully',
            'saved_tab' => $tab_value
        ));
    }
    
    /**
     * AJAX handler for saving Thunder papyrus data
     */
    public function ajax_save_thunder_papyrus_data() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Get the post ID and papyrus content from the request
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $papyrus_content = isset($_POST['papyrus_content']) ? wp_kses_post($_POST['papyrus_content']) : '';
        
        // Validate post ID
        if (!$post_id || !get_post($post_id)) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Check user capabilities
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error('You do not have permission to edit this post');
        }
        
        global $wpdb;
        $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
        
        // Check if orbitpost record exists for this post
        $orbitpost_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT orbitpost_id FROM $orbitposts_table WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        if ($orbitpost_exists) {
            // Update existing record
            $result = $wpdb->update(
                $orbitposts_table,
                array(
                    'papyrus_page_level_insert' => $papyrus_content,
                    'updated_at' => current_time('mysql')
                ),
                array('rel_wp_post_id' => $post_id),
                array('%s', '%s'),
                array('%d')
            );
        } else {
            // Create new orbitpost record
            $result = $wpdb->insert(
                $orbitposts_table,
                array(
                    'rel_wp_post_id' => $post_id,
                    'papyrus_page_level_insert' => $papyrus_content,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%s')
            );
        }
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Papyrus data saved successfully',
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Failed to save papyrus data: ' . $wpdb->last_error);
        }
    }
}