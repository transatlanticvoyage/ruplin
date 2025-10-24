<?php

/**
 * Titanium Feature for Snefuruplin
 * Updates specific Elementor widgets by their internal reference IDs (==widget1, ==widget2, etc.)
 * This is an exact clone of Cobalt functionality for baseline establishment
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Snefuru_Titanium {
    
    /**
     * Process titanium submission and update Elementor widgets
     */
    public function process_titanium_submission($post_id, $titanium_content, $auto_update_title = true) {
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] process_titanium_submission called with post_id: ' . $post_id . PHP_EOL, FILE_APPEND);
        
        // Special reset command to restore backup data
        if (trim($titanium_content) === 'RESET_ELEMENTOR_DATA') {
            return $this->restore_elementor_backup($post_id);
        }
        
        // Get current Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Elementor data length: ' . strlen($elementor_data) . PHP_EOL, FILE_APPEND);
        
        if (empty($elementor_data)) {
            return array('success' => false, 'message' => 'No Elementor data found for this page.');
        }
        
        // Create backup before making changes
        $this->create_elementor_backup($post_id, $elementor_data);
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] JSON decode failed, error: ' . json_last_error_msg() . PHP_EOL, FILE_APPEND);
            return array('success' => false, 'message' => 'Could not parse Elementor data. JSON error: ' . json_last_error_msg());
        }
        
        // Parse titanium submission into widget/item mappings
        $content_mappings = $this->parse_titanium_submission($titanium_content);
        
        if (empty($content_mappings)) {
            return array('success' => false, 'message' => 'No valid widget/item references found in submission.');
        }
        
        // Debug: Log what we parsed
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Parsed mappings: ' . print_r($content_mappings, true) . PHP_EOL, FILE_APPEND);
        
        // Track changes for response
        $updates_made = 0;
        $widget_counter = 1;
        $post_title_updated = false;
        
        // Process each top-level element (sections/containers)
        foreach ($elements as &$element) {
            $this->process_elementor_element_titanium($element, $content_mappings, $widget_counter, $updates_made);
        }
        
        // Debug: Log final results
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Total updates made: ' . $updates_made . PHP_EOL, FILE_APPEND);
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Final widget counter: ' . $widget_counter . PHP_EOL, FILE_APPEND);
        
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
            // First test: verify elements array can be encoded before processing
            $test_encode = json_encode($elements);
            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Elements array corrupted during processing: ' . json_last_error_msg() . PHP_EOL, FILE_APPEND);
                return array('success' => false, 'message' => 'Elements array was corrupted during processing: ' . json_last_error_msg());
            }
            
            // Encode JSON with proper flags to prevent corruption
            $json_data = json_encode($elements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // Check if JSON encoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] JSON encode error: ' . json_last_error_msg() . PHP_EOL, FILE_APPEND);
                return array('success' => false, 'message' => 'Failed to encode updated data: ' . json_last_error_msg());
            }
            
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] JSON encoded successfully, length: ' . strlen($json_data) . PHP_EOL, FILE_APPEND);
            
            // Test decode without wp_slash first
            $test_decode_clean = json_decode($json_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Clean JSON decode failed: ' . json_last_error_msg() . PHP_EOL, FILE_APPEND);
                return array('success' => false, 'message' => 'Clean JSON decode failed: ' . json_last_error_msg());
            }
            
            // Apply wp_slash
            $updated_data = wp_slash($json_data);
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Applied wp_slash, length: ' . strlen($updated_data) . PHP_EOL, FILE_APPEND);
            
            // Test that our data can be decoded after wp_slash
            $test_decode = json_decode($updated_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Post wp_slash decode failed: ' . json_last_error_msg() . PHP_EOL, FILE_APPEND);
                
                // Try without wp_slash
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Trying save without wp_slash...' . PHP_EOL, FILE_APPEND);
                $updated_data = $json_data; // Use original without wp_slash
            }
            
            // Try different approaches to save the data
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Trying update_post_meta...' . PHP_EOL, FILE_APPEND);
            $save_result = update_post_meta($post_id, '_elementor_data', $updated_data);
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] update_post_meta result: ' . ($save_result ? 'TRUE' : 'FALSE') . PHP_EOL, FILE_APPEND);
            
            if (!$save_result) {
                // Try direct database query instead of delete/add approach
                global $wpdb;
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] update_post_meta failed, trying direct database update...' . PHP_EOL, FILE_APPEND);
                $db_result = $wpdb->update(
                    $wpdb->postmeta,
                    array('meta_value' => $updated_data),
                    array('post_id' => $post_id, 'meta_key' => '_elementor_data')
                );
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Database update result: ' . $db_result . ' (0=no change, >0=success, false=error)' . PHP_EOL, FILE_APPEND);
                
                if ($db_result === false) {
                    // Last resort: delete old and add new, but do it carefully
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Direct update failed, trying delete-specific and add...' . PHP_EOL, FILE_APPEND);
                    
                    // Get the current meta_id so we can delete the specific entry
                    $current_meta = $wpdb->get_row($wpdb->prepare(
                        "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_elementor_data' LIMIT 1",
                        $post_id
                    ));
                    
                    if ($current_meta) {
                        // Delete the specific meta entry by ID
                        $delete_result = $wpdb->delete($wpdb->postmeta, array('meta_id' => $current_meta->meta_id));
                        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Deleted specific meta entry, result: ' . $delete_result . PHP_EOL, FILE_APPEND);
                        
                        // Add the new data
                        $add_result = add_post_meta($post_id, '_elementor_data', $updated_data, true);
                        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] add_post_meta result: ' . ($add_result ? 'TRUE' : 'FALSE') . PHP_EOL, FILE_APPEND);
                    }
                }
            }
            
            // Clear Elementor cache
            if (class_exists('\Elementor\Plugin')) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
                error_log('Titanium Debug - Cleared Elementor global cache');
                
                // Clear post-specific cache
                if (method_exists('\Elementor\Plugin::$instance->posts_css_manager', 'clear_cache')) {
                    \Elementor\Plugin::$instance->posts_css_manager->clear_cache();
                    error_log('Titanium Debug - Cleared Elementor CSS cache');
                }
            }
            
            // Clear any WordPress object cache
            wp_cache_delete($post_id, 'posts');
            wp_cache_delete($post_id, 'post_meta');
            error_log('Titanium Debug - Cleared WordPress object cache for post');
            
            // Check for multiple meta entries
            global $wpdb;
            $meta_entries = $wpdb->get_results($wpdb->prepare(
                "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_elementor_data'",
                $post_id
            ));
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Found ' . count($meta_entries) . ' _elementor_data entries' . PHP_EOL, FILE_APPEND);
            
            foreach ($meta_entries as $i => $entry) {
                $entry_length = strlen($entry->meta_value);
                $contains_drain = stripos($entry->meta_value, 'drain') !== false;
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Entry ' . ($i+1) . ' (ID: ' . $entry->meta_id . '): Length=' . $entry_length . ', Contains drain=' . ($contains_drain ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);
            }
            
            // Verify the save by re-reading the data using get_post_meta
            $saved_data = get_post_meta($post_id, '_elementor_data', true);
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] get_post_meta returned data length: ' . strlen($saved_data) . PHP_EOL, FILE_APPEND);
            
            // Check if our test content is actually in the saved data
            $contains_drain = stripos($saved_data, 'drain') !== false;
            $contains_amazing = stripos($saved_data, 'Amazing Plumbing People') !== false;
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] get_post_meta result contains "drain": ' . ($contains_drain ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] get_post_meta result contains "Amazing Plumbing People": ' . ($contains_amazing ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);
            
            // Test JSON validity
            $test_decode = json_decode($saved_data, true);
            $json_valid = (json_last_error() === JSON_ERROR_NONE);
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] JSON is valid: ' . ($json_valid ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);
            if (!$json_valid) {
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] JSON error: ' . json_last_error_msg() . PHP_EOL, FILE_APPEND);
            }
            
            // Check if data contains expected patterns (case-insensitive search)
            $contains_drain_ci = stripos($saved_data, 'Great drain cleaning') !== false;
            $contains_amazing_ci = stripos($saved_data, 'Amazing Plumbing') !== false;
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Contains "Great drain cleaning": ' . ($contains_drain_ci ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Contains "Amazing Plumbing": ' . ($contains_amazing_ci ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);
            
            // Save a sample of the data to see what's actually in there
            $sample = substr($saved_data, 0, 500);
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Data sample (first 500 chars): ' . $sample . PHP_EOL, FILE_APPEND);
            
            // Check if our changes are actually in the saved data
            $found_widget1_content = false;
            $found_widget2_content = false;
            $this->verify_saved_content($saved_elements, $found_widget1_content, $found_widget2_content);
            error_log('Titanium Debug - Verification: Widget1 content found: ' . ($found_widget1_content ? 'YES' : 'NO'));
            error_log('Titanium Debug - Verification: Widget2 content found: ' . ($found_widget2_content ? 'YES' : 'NO'));
            
            // Clear Elementor cache manually
            if (class_exists('Elementor\Plugin')) {
                $elementor_instance = \Elementor\Plugin::$instance;
                if (isset($elementor_instance->files_manager) && method_exists($elementor_instance->files_manager, 'clear_cache')) {
                    $elementor_instance->files_manager->clear_cache();
                }
            }
        }
        
        return array(
            'success' => true, 
            'message' => "Successfully updated {$updates_made} widget(s)." . ($post_title_updated ? " Post title updated." : ""),
            'updates_made' => $updates_made
        );
    }
    
    /**
     * Parse titanium submission into widget/item content mappings
     */
    private function parse_titanium_submission($titanium_content) {
        $mappings = array();
        $lines = explode("\n", $titanium_content);
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
                // Sanitize content to prevent JSON corruption
                $sanitized_line = $this->sanitize_content_for_json($line);
                $content_buffer[] = $sanitized_line;
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
    private function process_elementor_element_titanium(&$element, $content_mappings, &$widget_counter, &$updates_made) {
        if (!is_array($element)) {
            return false;
        }
        
        $updated = false;
        
        // Process if this is a widget
        if (!empty($element['widgetType'])) {
            // Check if this widget has extractable content (match Blueshift logic)
            $widget_has_content = $this->widget_has_extractable_content($element);
            
            if ($widget_has_content) {
                $widget_key = 'widget' . $widget_counter;
                
                // Debug: Log widget processing
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Processing widget ' . $widget_counter . ' (type: ' . $element['widgetType'] . ')' . PHP_EOL, FILE_APPEND);
                
                // Check if we have content for this widget
                if (isset($content_mappings[$widget_key])) {
                    $mapping = $content_mappings[$widget_key];
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Found mapping for ' . $widget_key . ': ' . print_r($mapping, true) . PHP_EOL, FILE_APPEND);
                    
                    // Handle multi-item widgets (with items)
                    if (!empty($mapping['items'])) {
                        $updated = $this->update_multi_item_widget($element, $mapping['items']);
                        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Multi-item widget update result: ' . ($updated ? 'success' : 'failed') . PHP_EOL, FILE_APPEND);
                    }
                    // Handle single content widgets
                    elseif (!empty($mapping['content'])) {
                        $updated = $this->update_single_widget($element, $mapping['content']);
                        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Single widget update result: ' . ($updated ? 'success' : 'failed') . PHP_EOL, FILE_APPEND);
                    }
                    
                    if ($updated) {
                        $updates_made++;
                    }
                } else {
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] No mapping found for ' . $widget_key . PHP_EOL, FILE_APPEND);
                }
                
                $widget_counter++;
            } else {
                file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Skipping widget ' . $element['widgetType'] . ' (no extractable content)' . PHP_EOL, FILE_APPEND);
            }
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as &$child_element) {
                $child_updated = $this->process_elementor_element_titanium($child_element, $content_mappings, $widget_counter, $updates_made);
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
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] update_single_widget: Missing widgetType or settings' . PHP_EOL, FILE_APPEND);
            return false;
        }
        
        $widget_type = $element['widgetType'];
        $updated = false;
        
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] update_single_widget: Processing ' . $widget_type . ' with content: ' . $new_content . PHP_EOL, FILE_APPEND);
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Available settings: ' . print_r(array_keys($element['settings']), true) . PHP_EOL, FILE_APPEND);
        
        switch ($widget_type) {
            case 'heading':
                if (isset($element['settings']['title'])) {
                    $old_title = $element['settings']['title'];
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Updating heading title from "' . $old_title . '" to "' . $new_content . '"' . PHP_EOL, FILE_APPEND);
                    $element['settings']['title'] = $new_content;
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] After update, element title is now: "' . $element['settings']['title'] . '"' . PHP_EOL, FILE_APPEND);
                    $updated = true;
                } else {
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Heading widget has no title setting' . PHP_EOL, FILE_APPEND);
                }
                break;
                
            case 'text-editor':
                if (isset($element['settings']['editor'])) {
                    $old_content = $element['settings']['editor'];
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Updating text-editor from "' . substr($old_content, 0, 50) . '..." to "' . $new_content . '"' . PHP_EOL, FILE_APPEND);
                    $element['settings']['editor'] = $new_content;
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] After update, editor content is now: "' . $element['settings']['editor'] . '"' . PHP_EOL, FILE_APPEND);
                    $updated = true;
                } else {
                    file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Text-editor widget has no editor setting' . PHP_EOL, FILE_APPEND);
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
        
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] update_single_widget: Returning ' . ($updated ? 'true' : 'false') . PHP_EOL, FILE_APPEND);
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
     * Check if widget has extractable content (matches Blueshift logic)
     */
    private function widget_has_extractable_content($element) {
        if (empty($element['widgetType']) || empty($element['settings'])) {
            return false;
        }
        
        $widget_type = $element['widgetType'];
        $settings = $element['settings'];
        
        switch ($widget_type) {
            case 'heading':
                return !empty($settings['title']);
                
            case 'text-editor':
                return !empty($settings['editor']);
                
            case 'button':
                return !empty($settings['text']);
                
            case 'image':
                return !empty($settings['caption']) || !empty($settings['title_text']);
                
            case 'icon-list':
                return !empty($settings['icon_list']) && is_array($settings['icon_list']);
                
            case 'testimonial':
                return !empty($settings['testimonial_content']) || !empty($settings['testimonial_name']) || !empty($settings['testimonial_job']);
                
            case 'icon-box':
                return !empty($settings['title_text']) || !empty($settings['description_text']);
                
            case 'video':
                return !empty($settings['image_overlay']['alt']);
                
            case 'tabs':
            case 'accordion':
            case 'toggle':
                return !empty($settings['tabs']) && is_array($settings['tabs']);
                
            case 'call-to-action':
                return !empty($settings['title']) || !empty($settings['description']) || !empty($settings['button']);
                
            case 'html':
                return !empty($settings['html']);
                
            case 'shortcode':
                return !empty($settings['shortcode']);
                
            case 'wordpress':
                return !empty($settings['wp']['text']) || !empty($settings['title']);
                
            case 'gallery':
            case 'image-carousel':
                return !empty($settings['gallery']) && is_array($settings['gallery']);
                
            case 'price-list':
                return !empty($settings['price_list']) && is_array($settings['price_list']);
                
            case 'social-icons':
                return !empty($settings['social_icon_list']) && is_array($settings['social_icon_list']);
                
            default:
                // For unknown widgets, check common fields
                $text_fields = array('title', 'text', 'content', 'description', 'caption', 'html', 'editor');
                foreach ($text_fields as $field) {
                    if (!empty($settings[$field])) {
                        return true;
                    }
                }
                return false;
        }
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
     * Verify that our changes were actually saved
     */
    private function verify_saved_content($elements, &$found_widget1, &$found_widget2) {
        $widget_counter = 1;
        
        foreach ($elements as $element) {
            $this->check_saved_element($element, $widget_counter, $found_widget1, $found_widget2);
        }
    }
    
    /**
     * Recursively check saved elements for our content
     */
    private function check_saved_element($element, &$widget_counter, &$found_widget1, &$found_widget2) {
        if (!is_array($element)) {
            return;
        }
        
        // Check if this is a widget with extractable content
        if (!empty($element['widgetType'])) {
            $widget_has_content = $this->widget_has_extractable_content($element);
            
            if ($widget_has_content) {
                if ($widget_counter == 1 && !empty($element['settings']['title']) && 
                    $element['settings']['title'] === 'Amazing Plumbing People') {
                    $found_widget1 = true;
                    error_log('Titanium Debug - Found widget1 content: ' . $element['settings']['title']);
                }
                
                if ($widget_counter == 2 && !empty($element['settings']['editor']) && 
                    $element['settings']['editor'] === 'Great drain cleaning services') {
                    $found_widget2 = true;
                    error_log('Titanium Debug - Found widget2 content: ' . $element['settings']['editor']);
                }
                
                $widget_counter++;
            }
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_element) {
                $this->check_saved_element($child_element, $widget_counter, $found_widget1, $found_widget2);
            }
        }
    }
    
    /**
     * AJAX handler for titanium content injection
     */
    public function ajax_titanium_inject_content() {
        // Write to a custom file for debugging
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] TITANIUM AJAX HANDLER CALLED - START' . PHP_EOL, FILE_APPEND);
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] POST data: ' . print_r($_POST, true) . PHP_EOL, FILE_APPEND);
        
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Security check failed' . PHP_EOL, FILE_APPEND);
            wp_die('Security check failed');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $auto_update_title = isset($_POST['auto_update_title']) ? (bool)$_POST['auto_update_title'] : true;
        
        // Process the actual titanium submission instead of just returning test message
        $result = $this->process_titanium_submission($post_id, $content, $auto_update_title);
        
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Process result: ' . print_r($result, true) . PHP_EOL, FILE_APPEND);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Create backup of Elementor data before making changes
     */
    private function create_elementor_backup($post_id, $elementor_data) {
        $backup_key = '_elementor_data_backup_' . date('Y_m_d_H_i_s');
        add_post_meta($post_id, $backup_key, $elementor_data);
        file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Created backup: ' . $backup_key . PHP_EOL, FILE_APPEND);
    }
    
    /**
     * Restore Elementor data from latest backup
     */
    private function restore_elementor_backup($post_id) {
        global $wpdb;
        
        // Find the most recent backup
        $backup_meta = $wpdb->get_row($wpdb->prepare(
            "SELECT meta_key, meta_value FROM {$wpdb->postmeta} 
             WHERE post_id = %d AND meta_key LIKE '_elementor_data_backup_%' 
             ORDER BY meta_key DESC LIMIT 1",
            $post_id
        ));
        
        if (!$backup_meta) {
            return array('success' => false, 'message' => 'No backup found to restore from.');
        }
        
        // Restore the backup data
        $restore_result = update_post_meta($post_id, '_elementor_data', $backup_meta->meta_value);
        
        if ($restore_result !== false) {
            file_put_contents(WP_CONTENT_DIR . '/titanium-debug.log', '[' . date('Y-m-d H:i:s') . '] Restored from backup: ' . $backup_meta->meta_key . PHP_EOL, FILE_APPEND);
            
            // Clear Elementor cache after restore
            if (class_exists('\Elementor\Plugin')) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }
            
            return array('success' => true, 'message' => 'Elementor data restored from backup: ' . $backup_meta->meta_key);
        } else {
            return array('success' => false, 'message' => 'Failed to restore backup data.');
        }
    }
    
    /**
     * Sanitize content to prevent JSON encoding issues
     */
    private function sanitize_content_for_json($content) {
        // Remove or replace potentially problematic characters
        $content = wp_strip_all_tags($content); // Remove any HTML tags
        $content = wp_unslash($content); // Remove WordPress slashes
        
        // Replace problematic characters that might break JSON
        $content = str_replace(array("\r\n", "\r"), "\n", $content); // Normalize line endings
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content); // Remove control characters except \t, \n
        
        // Ensure UTF-8 encoding
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }
        
        return $content;
    }
}