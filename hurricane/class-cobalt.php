<?php

/**
 * Cobalt Feature for Snefuruplin
 * Updates specific Elementor widgets by their internal reference IDs (==widget1, ==widget2, etc.)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Snefuru_Cobalt {
    
    /**
     * Process cobalt submission and update Elementor widgets
     */
    public function process_cobalt_submission($post_id, $cobalt_content, $auto_update_title = true) {
        // Get current Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return array('success' => false, 'message' => 'No Elementor data found for this page.');
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            return array('success' => false, 'message' => 'Could not parse Elementor data.');
        }
        
        // Parse cobalt submission into widget/item mappings
        $content_mappings = $this->parse_cobalt_submission($cobalt_content);
        
        if (empty($content_mappings)) {
            return array('success' => false, 'message' => 'No valid widget/item references found in submission.');
        }
        
        // Track changes for response
        $updates_made = 0;
        $widget_counter = 1;
        $post_title_updated = false;
        
        // Process each top-level element (sections/containers)
        foreach ($elements as &$element) {
            $this->process_elementor_element_cobalt($element, $content_mappings, $widget_counter, $updates_made);
        }
        
        // Update post title if toggle is enabled and we have updates
        if ($updates_made > 0 && $auto_update_title) {
            $first_content = $this->get_first_content_from_mappings($content_mappings);
            if (!empty($first_content)) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $first_content
                ));
                $post_title_updated = true;
            }
        }
        
        // Save updated Elementor data
        if ($updates_made > 0) {
            update_post_meta($post_id, '_elementor_data', wp_slash(json_encode($elements)));
        }
        
        return array(
            'success' => true, 
            'message' => "Successfully updated {$updates_made} widget(s)." . ($post_title_updated ? " Post title updated." : ""),
            'updates_made' => $updates_made
        );
    }
    
    /**
     * Parse cobalt submission into widget/item content mappings
     */
    private function parse_cobalt_submission($cobalt_content) {
        $mappings = array();
        $lines = explode("\n", $cobalt_content);
        $current_widget = null;
        $current_item = null;
        $content_buffer = array();
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Check for widget marker
            if (preg_match('/^==widget(\d+)(\s+.*)?$/', $line, $matches)) {
                // Save previous content if exists
                if ($current_widget !== null) {
                    if ($current_item !== null) {
                        $mappings[$current_widget]['items'][$current_item] = implode("\n", $content_buffer);
                    } else {
                        $mappings[$current_widget]['content'] = implode("\n", $content_buffer);
                    }
                }
                
                // Start new widget
                $current_widget = 'widget' . $matches[1];
                $current_item = null;
                $content_buffer = array();
            }
            // Check for item marker
            elseif (preg_match('/^==item(\d+)$/', $line, $matches)) {
                // Save previous item content if exists
                if ($current_item !== null && $current_widget !== null) {
                    $mappings[$current_widget]['items'][$current_item] = implode("\n", $content_buffer);
                }
                
                // Start new item
                $current_item = 'item' . $matches[1];
                $content_buffer = array();
            }
            // Regular content line
            else {
                $content_buffer[] = $line;
            }
        }
        
        // Save final content
        if ($current_widget !== null) {
            if ($current_item !== null) {
                $mappings[$current_widget]['items'][$current_item] = implode("\n", $content_buffer);
            } else {
                $mappings[$current_widget]['content'] = implode("\n", $content_buffer);
            }
        }
        
        return $mappings;
    }
    
    /**
     * Recursively process Elementor elements to update content
     */
    private function process_elementor_element_cobalt($element, $content_mappings, &$widget_counter, &$updates_made) {
        if (!is_array($element)) {
            return false;
        }
        
        $updated = false;
        
        // Process if this is a widget
        if (!empty($element['widgetType'])) {
            $widget_key = 'widget' . $widget_counter;
            
            // Check if we have content for this widget
            if (isset($content_mappings[$widget_key])) {
                $mapping = $content_mappings[$widget_key];
                
                // Handle multi-item widgets (with items)
                if (!empty($mapping['items'])) {
                    $updated = $this->update_multi_item_widget($element, $mapping['items']);
                }
                // Handle single content widgets
                elseif (!empty($mapping['content'])) {
                    $updated = $this->update_single_widget($element, $mapping['content']);
                }
                
                if ($updated) {
                    $updates_made++;
                }
            }
            
            $widget_counter++;
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as &$child_element) {
                $child_updated = $this->process_elementor_element_cobalt($child_element, $content_mappings, $widget_counter, $updates_made);
                $updated = $updated || $child_updated;
            }
        }
        
        return $updated;
    }
    
    /**
     * Update a single widget's content
     */
    private function update_single_widget(&$element, $new_content) {
        if (empty($element['widgetType']) || empty($element['settings'])) {
            return false;
        }
        
        $widget_type = $element['widgetType'];
        $updated = false;
        
        switch ($widget_type) {
            case 'heading':
                if (isset($element['settings']['title'])) {
                    $element['settings']['title'] = $new_content;
                    $updated = true;
                }
                break;
                
            case 'text-editor':
                if (isset($element['settings']['editor'])) {
                    $element['settings']['editor'] = $new_content;
                    $updated = true;
                }
                break;
                
            case 'button':
                if (isset($element['settings']['text'])) {
                    $element['settings']['text'] = $new_content;
                    $updated = true;
                }
                break;
                
            case 'image':
                // Update caption or title
                if (isset($element['settings']['caption'])) {
                    $element['settings']['caption'] = $new_content;
                    $updated = true;
                } elseif (isset($element['settings']['title_text'])) {
                    $element['settings']['title_text'] = $new_content;
                    $updated = true;
                }
                break;
                
            case 'icon-box':
                // Update title first, then description if title doesn't exist
                if (isset($element['settings']['title_text'])) {
                    $element['settings']['title_text'] = $new_content;
                    $updated = true;
                } elseif (isset($element['settings']['description_text'])) {
                    $element['settings']['description_text'] = $new_content;
                    $updated = true;
                }
                break;
                
            case 'testimonial':
                if (isset($element['settings']['testimonial_content'])) {
                    $element['settings']['testimonial_content'] = $new_content;
                    $updated = true;
                }
                break;
                
            case 'html':
                if (isset($element['settings']['html'])) {
                    $element['settings']['html'] = $new_content;
                    $updated = true;
                }
                break;
                
            case 'shortcode':
                if (isset($element['settings']['shortcode'])) {
                    $element['settings']['shortcode'] = $new_content;
                    $updated = true;
                }
                break;
                
            default:
                // Try common fields for unknown widgets
                $common_fields = array('title', 'text', 'content', 'editor', 'description');
                foreach ($common_fields as $field) {
                    if (isset($element['settings'][$field])) {
                        $element['settings'][$field] = $new_content;
                        $updated = true;
                        break;
                    }
                }
                break;
        }
        
        return $updated;
    }
    
    /**
     * Update a multi-item widget (like icon lists, tabs, etc.)
     */
    private function update_multi_item_widget(&$element, $item_mappings) {
        if (empty($element['widgetType']) || empty($element['settings'])) {
            return false;
        }
        
        $widget_type = $element['widgetType'];
        $updated = false;
        
        switch ($widget_type) {
            case 'icon-list':
                if (!empty($element['settings']['icon_list']) && is_array($element['settings']['icon_list'])) {
                    foreach ($element['settings']['icon_list'] as $index => &$item) {
                        $item_key = 'item' . ($index + 1);
                        if (isset($item_mappings[$item_key]) && !empty($item['text'])) {
                            $item['text'] = $item_mappings[$item_key];
                            $updated = true;
                        }
                    }
                }
                break;
                
            case 'tabs':
            case 'accordion':
            case 'toggle':
                if (!empty($element['settings']['tabs']) && is_array($element['settings']['tabs'])) {
                    foreach ($element['settings']['tabs'] as $index => &$tab) {
                        $item_key = 'item' . ($index + 1);
                        if (isset($item_mappings[$item_key])) {
                            // Parse content - if it contains multiple lines, split between title and content
                            $lines = explode("\n", $item_mappings[$item_key]);
                            if (count($lines) > 1) {
                                if (isset($tab['tab_title'])) {
                                    $tab['tab_title'] = array_shift($lines);
                                }
                                if (isset($tab['tab_content'])) {
                                    $tab['tab_content'] = implode("\n", $lines);
                                }
                            } else {
                                // Single line - update title or content
                                if (isset($tab['tab_title'])) {
                                    $tab['tab_title'] = $item_mappings[$item_key];
                                } elseif (isset($tab['tab_content'])) {
                                    $tab['tab_content'] = $item_mappings[$item_key];
                                }
                            }
                            $updated = true;
                        }
                    }
                }
                break;
                
            case 'price-list':
                if (!empty($element['settings']['price_list']) && is_array($element['settings']['price_list'])) {
                    foreach ($element['settings']['price_list'] as $index => &$price_item) {
                        $item_key = 'item' . ($index + 1);
                        if (isset($item_mappings[$item_key])) {
                            // Parse content - split between title, description, price
                            $lines = explode("\n", $item_mappings[$item_key]);
                            if (isset($price_item['title']) && !empty($lines[0])) {
                                $price_item['title'] = $lines[0];
                                $updated = true;
                            }
                            if (isset($price_item['description']) && !empty($lines[1])) {
                                $price_item['description'] = $lines[1];
                                $updated = true;
                            }
                            if (isset($price_item['price']) && !empty($lines[2])) {
                                $price_item['price'] = $lines[2];
                                $updated = true;
                            }
                        }
                    }
                }
                break;
                
            case 'social-icons':
                if (!empty($element['settings']['social_icon_list']) && is_array($element['settings']['social_icon_list'])) {
                    foreach ($element['settings']['social_icon_list'] as $index => &$social_item) {
                        $item_key = 'item' . ($index + 1);
                        if (isset($item_mappings[$item_key]) && isset($social_item['social_text'])) {
                            $social_item['social_text'] = $item_mappings[$item_key];
                            $updated = true;
                        }
                    }
                }
                break;
        }
        
        return $updated;
    }
    
    /**
     * Get first content line for post title update
     */
    private function get_first_content_from_mappings($content_mappings) {
        foreach ($content_mappings as $widget_data) {
            if (!empty($widget_data['content'])) {
                $lines = explode("\n", $widget_data['content']);
                return trim($lines[0]);
            }
            if (!empty($widget_data['items'])) {
                foreach ($widget_data['items'] as $item_content) {
                    $lines = explode("\n", $item_content);
                    return trim($lines[0]);
                }
            }
        }
        return '';
    }
    
    /**
     * AJAX handler for cobalt content injection
     */
    public function ajax_cobalt_inject_content() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $auto_update_title = isset($_POST['auto_update_title']) ? (bool)$_POST['auto_update_title'] : true;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        if (empty(trim($content))) {
            wp_send_json_error('Content cannot be empty');
        }
        
        // Process the cobalt submission
        $result = $this->process_cobalt_submission($post_id, $content, $auto_update_title);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
}