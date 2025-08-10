<?php
/**
 * Snefuruplin Elementor Content Injector
 * 
 * Cloned and adapted from zurkovich plugin's function_inject_content_2
 * Original date: 2025-08-10
 * 
 * This class handles injecting content into Elementor widgets based on code mappings
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Elementor_Content_Injector {
    
    /**
     * Inject content into Elementor widgets based on user input codes.
     *
     * @param int $page_id The ID of the page to update.
     * @param string $zeeprex_content The user-submitted content.
     * @return array Result array with success status and message
     */
    public static function inject_content($page_id, $zeeprex_content) {
        error_log('Snefuru: Starting content injection for page_id: ' . $page_id);
        
        if (empty($zeeprex_content)) {
            error_log('Snefuru: Empty zeeprex_content');
            return array(
                'success' => false,
                'message' => 'No content provided'
            );
        }

        // Decode HTML entities in the content
        $zeeprex_content = html_entity_decode($zeeprex_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        error_log('Snefuru: Decoded content: ' . substr($zeeprex_content, 0, 200));
        
        // Parse mappings
        $map = array();
        $lines = preg_split('/\r\n|\r|\n/', $zeeprex_content);
        $key = '';
        
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            error_log('Snefuru: Processing line ' . ($line_num + 1) . ': ' . $line);
            
            if (preg_match('/^#y_([^\s]+)/', $line, $m)) {
                $key = 'y_' . $m[1];
                $map[$key] = '';
                error_log('Snefuru: Found y_ code: ' . $key);
            } elseif (preg_match('/^#Y_([^\s]+)/', $line, $m)) {
                $key = 'Y_' . $m[1];
                $map[$key] = '';
                error_log('Snefuru: Found Y_ code: ' . $key);
            } elseif (preg_match('/^#/', $line)) {
                $key = '';
                error_log('Snefuru: Ignoring non-y code: ' . $line);
            } elseif ($key !== '') {
                $map[$key] .= ($map[$key] === '' ? '' : "\n") . $line;
                error_log('Snefuru: Added content for ' . $key . ': ' . substr($line, 0, 50) . '...');
            }
        }
        
        if (empty($map)) {
            error_log('Snefuru: No codes found in content');
            return array(
                'success' => false,
                'message' => 'No valid codes found in content. Make sure codes start with #y_ or #Y_'
            );
        }

        error_log('Snefuru: Found ' . count($map) . ' codes to process');
        error_log('Snefuru: Codes: ' . implode(', ', array_keys($map)));

        // Save mapping meta
        update_post_meta($page_id, 'snefuru_zeeprex_map', $map);
        update_post_meta($page_id, 'snefuru_zeeprex_submit', $zeeprex_content);
        error_log('Snefuru: Saved mapping to post meta');

        // Get Elementor data
        $data = get_post_meta($page_id, '_elementor_data', true);
        if (!$data) {
            error_log('Snefuru: No Elementor data found for page');
            return array(
                'success' => false,
                'message' => 'No Elementor data found for this page. Make sure this page uses Elementor.'
            );
        }

        error_log('Snefuru: Raw Elementor data: ' . substr($data, 0, 200) . '...');

        // Decode the data
        $elements = json_decode($data, true);
        if (!is_array($elements)) {
            error_log('Snefuru: Failed to decode Elementor data');
            return array(
                'success' => false,
                'message' => 'Failed to decode Elementor data'
            );
        }

        // Process the elements
        $processed_count = 0;
        $elements = self::process_elements($elements, $map, $processed_count);

        // Save the updated data
        $updated_data = wp_json_encode($elements);
        if ($updated_data === false) {
            error_log('Snefuru: Failed to encode updated data');
            return array(
                'success' => false,
                'message' => 'Failed to encode updated data'
            );
        }

        error_log('Snefuru: Updated Elementor data: ' . substr($updated_data, 0, 200) . '...');

        // Use Elementor's internal save mechanism if available
        if (class_exists('\Elementor\Plugin')) {
            $document = \Elementor\Plugin::$instance->documents->get($page_id);
            if ($document) {
                // Update the document data
                $document->save([
                    'elements' => $elements,
                    'settings' => $document->get_settings()
                ]);
                error_log('Snefuru: Saved using Elementor document save');
                
                // Clear Elementor cache
                if (method_exists('\Elementor\Plugin::$instance->files_manager', 'clear_cache')) {
                    \Elementor\Plugin::$instance->files_manager->clear_cache();
                }
                
                return array(
                    'success' => true,
                    'message' => sprintf('Content injected successfully! %d replacements made.', $processed_count)
                );
            }
        }

        // Fallback: Save directly to post meta
        update_post_meta($page_id, '_elementor_data', $updated_data);
        error_log('Snefuru: Saved directly to post meta');
        
        return array(
            'success' => true,
            'message' => sprintf('Content injected successfully! %d replacements made.', $processed_count)
        );
    }

    /**
     * Process Elementor elements recursively
     */
    private static function process_elements($elements, $map, &$processed_count) {
        foreach ($elements as &$el) {
            if (isset($el['settings']) && is_array($el['settings'])) {
                // Store original settings
                $original_settings = $el['settings'];
                
                // Update content fields based on widget type
                if (isset($el['widgetType'])) {
                    error_log('Snefuru: Processing widget type: ' . $el['widgetType']);
                    
                    // Log all settings for debugging
                    error_log('Snefuru: Widget settings: ' . json_encode($original_settings));
                    
                    switch ($el['widgetType']) {
                        case 'heading':
                            if (isset($original_settings['title'])) {
                                foreach ($map as $key => $val) {
                                    if ($original_settings['title'] === $key) {
                                        error_log('Snefuru: Found match in heading title: ' . $key);
                                        $el['settings']['title'] = $val;
                                        $processed_count++;
                                        
                                        // Preserve typography and other settings
                                        foreach ($original_settings as $setting_key => $setting_val) {
                                            if ($setting_key !== 'title' && !isset($el['settings'][$setting_key])) {
                                                $el['settings'][$setting_key] = $setting_val;
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                            
                        case 'text-editor':
                            if (isset($original_settings['editor'])) {
                                foreach ($map as $key => $val) {
                                    // Check for the code both with and without HTML tags
                                    $code_with_tags = '<p>' . $key . '</p>';
                                    if ($original_settings['editor'] === $key || $original_settings['editor'] === $code_with_tags) {
                                        error_log('Snefuru: Found match in text-editor editor: ' . $key);
                                        // If the original had HTML tags, wrap the new content in them
                                        if (strpos($original_settings['editor'], '<p>') !== false) {
                                            $el['settings']['editor'] = '<p>' . $val . '</p>';
                                        } else {
                                            $el['settings']['editor'] = $val;
                                        }
                                        $processed_count++;
                                        
                                        // Preserve typography and other settings
                                        foreach ($original_settings as $setting_key => $setting_val) {
                                            if ($setting_key !== 'editor' && !isset($el['settings'][$setting_key])) {
                                                $el['settings'][$setting_key] = $setting_val;
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                            
                        case 'image-box':
                            if (isset($original_settings['title_text'])) {
                                foreach ($map as $key => $val) {
                                    if ($original_settings['title_text'] === $key) {
                                        error_log('Snefuru: Found match in image-box title_text: ' . $key);
                                        $el['settings']['title_text'] = $val;
                                        $processed_count++;
                                        
                                        // Preserve title typography
                                        if (isset($original_settings['title_typography_typography'])) {
                                            $el['settings']['title_typography_typography'] = $original_settings['title_typography_typography'];
                                        }
                                    }
                                }
                            }
                            if (isset($original_settings['description_text'])) {
                                foreach ($map as $key => $val) {
                                    if ($original_settings['description_text'] === $key) {
                                        error_log('Snefuru: Found match in image-box description_text: ' . $key);
                                        $el['settings']['description_text'] = $val;
                                        $processed_count++;
                                        
                                        // Preserve description typography
                                        if (isset($original_settings['description_typography_typography'])) {
                                            $el['settings']['description_typography_typography'] = $original_settings['description_typography_typography'];
                                        }
                                    }
                                }
                            }
                            break;
                            
                        case 'button':
                            if (isset($original_settings['text'])) {
                                foreach ($map as $key => $val) {
                                    if ($original_settings['text'] === $key) {
                                        error_log('Snefuru: Found match in button text: ' . $key);
                                        $el['settings']['text'] = $val;
                                        $processed_count++;
                                    }
                                }
                            }
                            break;
                            
                        case 'icon-box':
                            if (isset($original_settings['title_text'])) {
                                foreach ($map as $key => $val) {
                                    if ($original_settings['title_text'] === $key) {
                                        error_log('Snefuru: Found match in icon-box title_text: ' . $key);
                                        $el['settings']['title_text'] = $val;
                                        $processed_count++;
                                    }
                                }
                            }
                            if (isset($original_settings['description_text'])) {
                                foreach ($map as $key => $val) {
                                    if ($original_settings['description_text'] === $key) {
                                        error_log('Snefuru: Found match in icon-box description_text: ' . $key);
                                        $el['settings']['description_text'] = $val;
                                        $processed_count++;
                                    }
                                }
                            }
                            break;
                    }
                }
            }
            
            // Process child elements
            if (isset($el['elements']) && is_array($el['elements'])) {
                $el['elements'] = self::process_elements($el['elements'], $map, $processed_count);
            }
        }
        return $elements;
    }
}