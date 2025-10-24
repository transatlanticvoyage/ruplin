<?php

/**
 * Blueshift Feature for Snefuruplin
 * Extracts Elementor widget content with HTML preserved, separated by ##pc markers
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Snefuru_Blueshift {
    
    /**
     * Extract frontend content from Elementor data with HTML preserved
     * Each widget's content is separated by ##widget marker
     */
    public function extract_elementor_blueshift_content($post_id) {
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return '##widget' . "\n" . 'No Elementor data found for this page.';
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            return '##widget' . "\n" . 'Could not parse Elementor data.';
        }
        
        $extracted_content = array();
        
        // Process each top-level element (sections/containers)
        foreach ($elements as $element) {
            $this->process_elementor_element_blueshift($element, $extracted_content);
        }
        
        // If no content found, return empty indicator
        if (empty($extracted_content)) {
            return '##widget' . "\n" . 'No widget content found.';
        }
        
        // Join all content pieces with ##widget separator
        $result = '';
        foreach ($extracted_content as $content) {
            if (!empty(trim($content))) {
                $result .= '##widget' . "\n" . $content . "\n";
            }
        }
        
        return rtrim($result);
    }
    
    /**
     * Recursively process Elementor elements to extract HTML content
     */
    private function process_elementor_element_blueshift($element, &$extracted_content) {
        if (!is_array($element)) {
            return;
        }
        
        // Extract content if this is a widget
        if (!empty($element['widgetType'])) {
            $widget_content = $this->extract_widget_html_content($element);
            if (!empty($widget_content)) {
                $extracted_content[] = $widget_content;
            }
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_element) {
                $this->process_elementor_element_blueshift($child_element, $extracted_content);
            }
        }
    }
    
    /**
     * Extract HTML content from specific widget types
     */
    private function extract_widget_html_content($element) {
        if (empty($element['widgetType'])) {
            return '';
        }
        
        $widget_type = $element['widgetType'];
        $settings = !empty($element['settings']) ? $element['settings'] : array();
        
        $html_content = '';
        
        switch ($widget_type) {
            case 'heading':
                if (!empty($settings['title'])) {
                    $html_content = $settings['title'];
                    // Check for heading tag
                    $tag = !empty($settings['header_size']) ? $settings['header_size'] : 'h2';
                    if (!empty($settings['size'])) {
                        $tag = $settings['size'];
                    }
                    // Preserve the raw content as stored
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'text-editor':
                if (!empty($settings['editor'])) {
                    // Text editor content often contains HTML
                    $html_content = $settings['editor'];
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'button':
                if (!empty($settings['text'])) {
                    $html_content = $settings['text'];
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'image':
                $html_content_parts = array();
                if (!empty($settings['caption'])) {
                    $html_content_parts[] = $settings['caption'];
                }
                if (!empty($settings['title_text'])) {
                    $html_content_parts[] = $settings['title_text'];
                }
                if (!empty($html_content_parts)) {
                    $html_content = implode("\n", $html_content_parts);
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'icon-list':
                if (!empty($settings['icon_list']) && is_array($settings['icon_list'])) {
                    $list_items = array();
                    foreach ($settings['icon_list'] as $item) {
                        if (!empty($item['text'])) {
                            $list_items[] = '##item' . "\n" . $this->preserve_html_format($item['text']);
                        }
                    }
                    if (!empty($list_items)) {
                        // Join items without additional separators since each already has ##item
                        $html_content = implode("\n", $list_items);
                    }
                }
                break;
                
            case 'testimonial':
                $content_parts = array();
                if (!empty($settings['testimonial_content'])) {
                    $content_parts[] = $settings['testimonial_content'];
                }
                if (!empty($settings['testimonial_name'])) {
                    $content_parts[] = $settings['testimonial_name'];
                }
                if (!empty($settings['testimonial_job'])) {
                    $content_parts[] = $settings['testimonial_job'];
                }
                if (!empty($content_parts)) {
                    $html_content = implode("\n", $content_parts);
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'icon-box':
                $content_parts = array();
                if (!empty($settings['title_text'])) {
                    $content_parts[] = $settings['title_text'];
                }
                if (!empty($settings['description_text'])) {
                    $content_parts[] = $settings['description_text'];
                }
                if (!empty($content_parts)) {
                    $html_content = implode("\n", $content_parts);
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'video':
                if (!empty($settings['image_overlay']['alt'])) {
                    $html_content = $settings['image_overlay']['alt'];
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'tabs':
            case 'accordion':
            case 'toggle':
                if (!empty($settings['tabs']) && is_array($settings['tabs'])) {
                    $tab_contents = array();
                    foreach ($settings['tabs'] as $tab) {
                        $tab_item = array();
                        if (!empty($tab['tab_title'])) {
                            $tab_item[] = $this->preserve_html_format($tab['tab_title']);
                        }
                        if (!empty($tab['tab_content'])) {
                            $tab_item[] = $this->preserve_html_format($tab['tab_content']);
                        }
                        if (!empty($tab_item)) {
                            $tab_contents[] = '##item' . "\n" . implode("\n", $tab_item);
                        }
                    }
                    if (!empty($tab_contents)) {
                        // Join items without additional separators since each already has ##item
                        $html_content = implode("\n", $tab_contents);
                    }
                }
                break;
                
            case 'call-to-action':
                $content_parts = array();
                if (!empty($settings['title'])) {
                    $content_parts[] = $settings['title'];
                }
                if (!empty($settings['description'])) {
                    $content_parts[] = $settings['description'];
                }
                if (!empty($settings['button'])) {
                    $content_parts[] = $settings['button'];
                }
                if (!empty($content_parts)) {
                    $html_content = implode("\n", $content_parts);
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'html':
                if (!empty($settings['html'])) {
                    // Raw HTML widget - preserve exactly as is
                    $html_content = $settings['html'];
                }
                break;
                
            case 'shortcode':
                if (!empty($settings['shortcode'])) {
                    $html_content = $settings['shortcode'];
                }
                break;
                
            case 'wordpress':
                // WordPress widgets (like WordPress.widget-text)
                if (!empty($settings['wp']['text'])) {
                    $html_content = $settings['wp']['text'];
                    $html_content = $this->preserve_html_format($html_content);
                } elseif (!empty($settings['title'])) {
                    $html_content = $settings['title'];
                    $html_content = $this->preserve_html_format($html_content);
                }
                break;
                
            case 'gallery':
            case 'image-carousel':
                // For galleries with multiple images
                if (!empty($settings['gallery']) && is_array($settings['gallery'])) {
                    $gallery_items = array();
                    foreach ($settings['gallery'] as $image) {
                        if (!empty($image['alt']) || !empty($image['title'])) {
                            $img_text = array();
                            if (!empty($image['alt'])) {
                                $img_text[] = $this->preserve_html_format($image['alt']);
                            }
                            if (!empty($image['title'])) {
                                $img_text[] = $this->preserve_html_format($image['title']);
                            }
                            if (!empty($img_text)) {
                                $gallery_items[] = '##item' . "\n" . implode("\n", $img_text);
                            }
                        }
                    }
                    if (!empty($gallery_items)) {
                        $html_content = implode("\n", $gallery_items);
                    }
                }
                break;
                
            case 'price-list':
                // For price list widgets with multiple items
                if (!empty($settings['price_list']) && is_array($settings['price_list'])) {
                    $price_items = array();
                    foreach ($settings['price_list'] as $item) {
                        $item_parts = array();
                        if (!empty($item['title'])) {
                            $item_parts[] = $this->preserve_html_format($item['title']);
                        }
                        if (!empty($item['description'])) {
                            $item_parts[] = $this->preserve_html_format($item['description']);
                        }
                        if (!empty($item['price'])) {
                            $item_parts[] = $this->preserve_html_format($item['price']);
                        }
                        if (!empty($item_parts)) {
                            $price_items[] = '##item' . "\n" . implode("\n", $item_parts);
                        }
                    }
                    if (!empty($price_items)) {
                        $html_content = implode("\n", $price_items);
                    }
                }
                break;
                
            case 'social-icons':
                // For social icons with custom labels
                if (!empty($settings['social_icon_list']) && is_array($settings['social_icon_list'])) {
                    $social_items = array();
                    foreach ($settings['social_icon_list'] as $item) {
                        if (!empty($item['social_text'])) {
                            $social_items[] = '##item' . "\n" . $this->preserve_html_format($item['social_text']);
                        }
                    }
                    if (!empty($social_items)) {
                        $html_content = implode("\n", $social_items);
                    }
                }
                break;
                
            default:
                // For unknown widgets, try to extract any text-like settings with HTML
                $text_fields = array('title', 'text', 'content', 'description', 'caption', 'html', 'editor');
                foreach ($text_fields as $field) {
                    if (!empty($settings[$field])) {
                        $html_content = $settings[$field];
                        $html_content = $this->preserve_html_format($html_content);
                        break; // Take the first found field
                    }
                }
                break;
        }
        
        return $html_content;
    }
    
    /**
     * Preserve HTML formatting and line breaks
     * This keeps the content exactly as stored in Elementor
     */
    private function preserve_html_format($content) {
        // Return content as-is to preserve HTML tags and formatting
        // Don't strip tags or modify the content
        return trim($content);
    }
    
    /**
     * Extract frontend content with numbered separators for format 2
     */
    public function extract_elementor_blueshift_content_format2($post_id) {
        // Get the base content first
        $base_content = $this->extract_elementor_blueshift_content($post_id);
        
        // Add numbering to the content
        return $this->add_numbering_to_content($base_content);
    }
    
    /**
     * Extract frontend content with numbered separators and custom CSS classes/IDs for format 3
     */
    public function extract_elementor_blueshift_content_format3($post_id) {
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return '==widget1' . "\n" . 'No Elementor data found for this page.';
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            return '==widget1' . "\n" . 'Could not parse Elementor data.';
        }
        
        $extracted_content = array();
        $widget_counter = 1;
        
        // Process each top-level element (sections/containers)
        foreach ($elements as $element) {
            $this->process_elementor_element_blueshift_format3($element, $extracted_content, $widget_counter);
        }
        
        // If no content found, return empty indicator
        if (empty($extracted_content)) {
            return '==widget1' . "\n" . 'No widget content found.';
        }
        
        // Join all content pieces
        return implode("\n", $extracted_content);
    }
    
    /**
     * Recursively process Elementor elements to extract HTML content with CSS classes and IDs
     */
    private function process_elementor_element_blueshift_format3($element, &$extracted_content, &$widget_counter) {
        if (!is_array($element)) {
            return;
        }
        
        // Extract content if this is a widget
        if (!empty($element['widgetType'])) {
            // Get widget content
            $widget_content = $this->extract_widget_html_content($element);
            
            if (!empty($widget_content)) {
                // Extract custom CSS classes and ID
                $css_info = $this->extract_css_classes_and_id($element);
                
                // Create widget marker with CSS info
                $widget_marker = '==widget' . $widget_counter;
                if (!empty($css_info)) {
                    $widget_marker .= ' ' . $css_info;
                }
                
                // Handle multi-item widgets
                if (strpos($widget_content, '##item') !== false) {
                    // This is a multi-item widget
                    $lines = explode("\n", $widget_content);
                    $processed_lines = array();
                    $item_counter = 1;
                    
                    $processed_lines[] = $widget_marker; // Add widget marker first
                    
                    foreach ($lines as $line) {
                        if (strpos($line, '##item') === 0) {
                            // Replace ##item with ==item numbered version
                            $processed_lines[] = '==item' . $item_counter;
                            $item_counter++;
                        } else {
                            // Keep content line as-is
                            $processed_lines[] = $line;
                        }
                    }
                    
                    $extracted_content = array_merge($extracted_content, $processed_lines);
                } else {
                    // Single-item widget
                    $extracted_content[] = $widget_marker;
                    $extracted_content[] = $widget_content;
                }
                
                $widget_counter++;
            }
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_element) {
                $this->process_elementor_element_blueshift_format3($child_element, $extracted_content, $widget_counter);
            }
        }
    }
    
    /**
     * Extract custom CSS classes and ID from Elementor element settings
     */
    private function extract_css_classes_and_id($element) {
        $css_parts = array();
        
        if (!empty($element['settings'])) {
            $settings = $element['settings'];
            
            // Extract custom ID
            if (!empty($settings['_element_id'])) {
                $css_parts[] = '#' . $settings['_element_id'];
            }
            
            // Extract custom CSS classes
            if (!empty($settings['_css_classes'])) {
                $classes = trim($settings['_css_classes']);
                if (!empty($classes)) {
                    // Split by spaces and add dot prefix to each class
                    $class_array = array_filter(explode(' ', $classes));
                    foreach ($class_array as $class) {
                        $css_parts[] = '.' . trim($class);
                    }
                }
            }
        }
        
        return implode(' ', $css_parts);
    }
    
    /**
     * Add incremental numbering to ##widget and ##item markers
     */
    private function add_numbering_to_content($content) {
        $widget_counter = 1;
        $item_counter = 1;
        
        // Split content into lines
        $lines = explode("\n", $content);
        $numbered_lines = array();
        
        foreach ($lines as $line) {
            if (strpos($line, '##widget') === 0) {
                // Replace ##widget with ##widget{number}
                $numbered_lines[] = '##widget' . $widget_counter;
                $widget_counter++;
                // Reset item counter for each new widget
                $item_counter = 1;
            } elseif (strpos($line, '##item') === 0) {
                // Replace ##item with ##item{number}
                $numbered_lines[] = '##item' . $item_counter;
                $item_counter++;
            } else {
                // Keep the line as-is
                $numbered_lines[] = $line;
            }
        }
        
        return implode("\n", $numbered_lines);
    }
    
    /**
     * Extract frontend content with multi-line separators for format 4
     * Uses configurable separator character count from database
     */
    public function extract_elementor_blueshift_content_format4($post_id) {
        // Get the saved separator count from database, default to 95
        $separator_count = get_option('ruplin_blueshift_separator_character_count', 95);
        
        // Create separator lines
        $equal_separator = str_repeat('=', $separator_count);
        $dash_separator = str_repeat('—', $separator_count);
        
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return $equal_separator . "\n" . 
                   'widget1' . "\n" .
                   $dash_separator . "\n" .
                   'No Elementor data found for this page.' . "\n" .
                   $equal_separator;
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            return $equal_separator . "\n" . 
                   'widget1' . "\n" .
                   $dash_separator . "\n" .
                   'Could not parse Elementor data.' . "\n" .
                   $equal_separator;
        }
        
        $extracted_content = array();
        $widget_counter = 1;
        
        // Process each top-level element (sections/containers)
        foreach ($elements as $element) {
            $this->process_elementor_element_blueshift_format4($element, $extracted_content, $widget_counter, $equal_separator, $dash_separator);
        }
        
        // If no content found, return empty indicator
        if (empty($extracted_content)) {
            return $equal_separator . "\n" . 
                   'widget1' . "\n" .
                   $dash_separator . "\n" .
                   'No widget content found.' . "\n" .
                   $equal_separator;
        }
        
        // Build output with single separator between widgets
        $output = '';
        foreach ($extracted_content as $index => $content) {
            if ($index === 0) {
                // First widget starts with separator
                $output .= $content;
            } else {
                // Subsequent widgets just continue (they already have their top separator)
                $output .= "\n" . $content;
            }
        }
        
        // Add closing separator at the end
        $output .= "\n" . $equal_separator;
        
        return $output;
    }
    
    /**
     * Recursively process Elementor elements for format 4 with multi-line separators
     */
    private function process_elementor_element_blueshift_format4($element, &$extracted_content, &$widget_counter, $equal_separator, $dash_separator) {
        if (!is_array($element)) {
            return;
        }
        
        // Extract content if this is a widget
        if (!empty($element['widgetType'])) {
            // Get widget content
            $widget_content = $this->extract_widget_html_content($element);
            
            if (!empty($widget_content)) {
                // Extract custom CSS classes and ID
                $css_info = $this->extract_css_classes_and_id($element);
                
                // Create widget identifier with CSS info if present
                $widget_identifier = 'widget' . $widget_counter;
                if (!empty($css_info)) {
                    $widget_identifier .= ' ' . $css_info;
                }
                
                // Check if content has ##item markers
                if (strpos($widget_content, '##item') !== false) {
                    // This is a list widget - handle items specially
                    $items = explode('##item', $widget_content);
                    
                    // First part is the widget header
                    $formatted_output = $equal_separator . "\n" .
                                      $widget_identifier;
                    
                    // Process each item (skip first empty element from explode)
                    $item_counter = 1;
                    for ($i = 1; $i < count($items); $i++) {
                        $item_content = trim($items[$i]);
                        if (!empty($item_content)) {
                            $formatted_output .= "\n" . $equal_separator . "\n" .
                                               "widget" . $widget_counter . "-item" . $item_counter . "\n" .
                                               $dash_separator . "\n" .
                                               $item_content;
                            $item_counter++;
                        }
                    }
                } else {
                    // Regular widget - use standard format
                    $formatted_output = $equal_separator . "\n" .
                                      $widget_identifier . "\n" .
                                      $dash_separator . "\n" .
                                      $widget_content;
                }
                
                $extracted_content[] = $formatted_output;
                $widget_counter++;
            }
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_element) {
                $this->process_elementor_element_blueshift_format4($child_element, $extracted_content, $widget_counter, $equal_separator, $dash_separator);
            }
        }
    }
    
    /**
     * Extract frontend content with multi-line separators for format 4 with filtering
     * @param int $post_id The post ID
     * @param array $excluded_classes Classes to exclude from output
     */
    public function extract_elementor_blueshift_content_format4_filtered($post_id, $excluded_classes = array()) {
        // Get the saved separator count from database, default to 95
        $separator_count = get_option('ruplin_blueshift_separator_character_count', 95);
        
        // Create separator lines
        $equal_separator = str_repeat('=', $separator_count);
        $dash_separator = str_repeat('—', $separator_count);
        
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return $equal_separator . "\n" . 
                   'widget1' . "\n" .
                   $dash_separator . "\n" .
                   'No Elementor data found for this page.' . "\n" .
                   $equal_separator;
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            return $equal_separator . "\n" . 
                   'widget1' . "\n" .
                   $dash_separator . "\n" .
                   'Could not parse Elementor data.' . "\n" .
                   $equal_separator;
        }
        
        $extracted_content = array();
        $widget_counter = 1;
        
        // Process each top-level element (sections/containers)
        foreach ($elements as $element) {
            $this->process_elementor_element_blueshift_format4_filtered($element, $extracted_content, $widget_counter, $equal_separator, $dash_separator, $excluded_classes);
        }
        
        // If no content found, return empty indicator
        if (empty($extracted_content)) {
            return $equal_separator . "\n" . 
                   'widget1' . "\n" .
                   $dash_separator . "\n" .
                   'No widget content found.' . "\n" .
                   $equal_separator;
        }
        
        // Build output with single separator between widgets
        $output = '';
        foreach ($extracted_content as $index => $content) {
            if ($index === 0) {
                // First widget starts with separator
                $output .= $content;
            } else {
                // Subsequent widgets just continue (they already have their top separator)
                $output .= "\n" . $content;
            }
        }
        
        // Add closing separator at the end
        $output .= "\n" . $equal_separator;
        
        return $output;
    }
    
    /**
     * Recursively process Elementor elements for format 4 with filtering
     */
    private function process_elementor_element_blueshift_format4_filtered($element, &$extracted_content, &$widget_counter, $equal_separator, $dash_separator, $excluded_classes) {
        if (!is_array($element)) {
            return;
        }
        
        // Extract content if this is a widget
        if (!empty($element['widgetType'])) {
            // Get widget content first
            $widget_content = $this->extract_widget_html_content($element);
            
            // Only process if widget has content
            if (!empty($widget_content)) {
                // Extract CSS info
                $css_info = $this->extract_css_classes_and_id($element);
                
                // Check each excluded class
                $should_exclude = false;
                foreach ($excluded_classes as $class) {
                    if (strpos($css_info, '.' . $class) !== false) {
                        $should_exclude = true;
                        break;
                    }
                }
                
                // If widget should NOT be excluded, add it to output
                if (!$should_exclude) {
                    // Create widget identifier with CSS info if present
                    $widget_identifier = 'widget' . $widget_counter;
                    if (!empty($css_info)) {
                        $widget_identifier .= ' ' . $css_info;
                    }
                    
                    // Check if content has ##item markers
                    if (strpos($widget_content, '##item') !== false) {
                        // This is a list widget - handle items specially
                        $items = explode('##item', $widget_content);
                        
                        // First part is the widget header
                        $formatted_output = $equal_separator . "\n" .
                                          $widget_identifier;
                        
                        // Process each item (skip first empty element from explode)
                        $item_counter = 1;
                        for ($i = 1; $i < count($items); $i++) {
                            $item_content = trim($items[$i]);
                            if (!empty($item_content)) {
                                $formatted_output .= "\n" . $equal_separator . "\n" .
                                                   "widget" . $widget_counter . "-item" . $item_counter . "\n" .
                                                   $dash_separator . "\n" .
                                                   $item_content;
                                $item_counter++;
                            }
                        }
                    } else {
                        // Regular widget - use standard format
                        $formatted_output = $equal_separator . "\n" .
                                          $widget_identifier . "\n" .
                                          $dash_separator . "\n" .
                                          $widget_content;
                    }
                    
                    $extracted_content[] = $formatted_output;
                }
                
                // ALWAYS increment counter regardless of whether widget was excluded
                // This maintains the original widget numbering scheme
                $widget_counter++;
            }
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_element) {
                $this->process_elementor_element_blueshift_format4_filtered($child_element, $extracted_content, $widget_counter, $equal_separator, $dash_separator, $excluded_classes);
            }
        }
    }
    
    /**
     * Extract frontend content for widgets with specific class using format 4 style
     * @param string $post_id The post ID
     * @param string $class_filter The class to filter by (e.g., 'exclude_from_blueshift')
     */
    public function extract_elementor_blueshift_content_by_class($post_id, $class_filter = 'exclude_from_blueshift') {
        // Get the saved separator count from database, default to 95
        $separator_count = get_option('ruplin_blueshift_separator_character_count', 95);
        
        // Create separator lines
        $equal_separator = str_repeat('=', $separator_count);
        $dash_separator = str_repeat('—', $separator_count);
        
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return $equal_separator . "\n" . 
                   'No Elementor data found for this page.' . "\n" .
                   $equal_separator;
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            return $equal_separator . "\n" . 
                   'Could not parse Elementor data.' . "\n" .
                   $equal_separator;
        }
        
        $extracted_content = array();
        $widget_counter = 1; // This counter increments for ALL widgets to maintain proper numbering
        $filtered_widgets = array(); // Store only the filtered widgets with their correct numbers
        
        // Process each top-level element (sections/containers)
        foreach ($elements as $element) {
            $this->process_elementor_element_by_class($element, $extracted_content, $widget_counter, $class_filter, $equal_separator, $dash_separator, $filtered_widgets);
        }
        
        // If no content found, return empty indicator
        if (empty($filtered_widgets)) {
            return $equal_separator . "\n" . 
                   'No widgets with class .' . $class_filter . ' found.' . "\n" .
                   $equal_separator;
        }
        
        // Build output from filtered widgets only
        $output = '';
        foreach ($filtered_widgets as $index => $content) {
            if ($index === 0) {
                $output .= $content;
            } else {
                $output .= "\n" . $content;
            }
        }
        
        // Add closing separator at the end
        $output .= "\n" . $equal_separator;
        
        return $output;
    }
    
    /**
     * Process Elementor elements filtering by specific class
     */
    private function process_elementor_element_by_class($element, &$extracted_content, &$widget_counter, $class_filter, $equal_separator, $dash_separator, &$filtered_widgets) {
        if (!is_array($element)) {
            return;
        }
        
        // Extract content if this is a widget
        if (!empty($element['widgetType'])) {
            // Get widget content first
            $widget_content = $this->extract_widget_html_content($element);
            
            // Check if widget has content
            if (!empty($widget_content)) {
                // Extract CSS info
                $css_info = $this->extract_css_classes_and_id($element);
                
                // Check if the class filter appears in the CSS info
                $has_filtered_class = (strpos($css_info, '.' . $class_filter) !== false);
                
                if ($has_filtered_class) {
                    // Create widget identifier with CSS info using current counter
                    $widget_identifier = 'widget' . $widget_counter;
                    if (!empty($css_info)) {
                        $widget_identifier .= ' ' . $css_info;
                    }
                    
                    // Check if content has ##item markers
                    if (strpos($widget_content, '##item') !== false) {
                        // This is a list widget - handle items specially
                        $items = explode('##item', $widget_content);
                        
                        // First part is the widget header
                        $formatted_output = $equal_separator . "\n" .
                                          $widget_identifier;
                        
                        // Process each item (skip first empty element from explode)
                        $item_counter = 1;
                        for ($i = 1; $i < count($items); $i++) {
                            $item_content = trim($items[$i]);
                            if (!empty($item_content)) {
                                $formatted_output .= "\n" . $equal_separator . "\n" .
                                                   "widget" . $widget_counter . "-item" . $item_counter . "\n" .
                                                   $dash_separator . "\n" .
                                                   $item_content;
                                $item_counter++;
                            }
                        }
                    } else {
                        // Regular widget - use standard format
                        $formatted_output = $equal_separator . "\n" .
                                          $widget_identifier . "\n" .
                                          $dash_separator . "\n" .
                                          $widget_content;
                    }
                    
                    // Add to filtered widgets array
                    $filtered_widgets[] = $formatted_output;
                }
                
                // Always increment counter regardless of whether widget was filtered
                // This maintains consistent numbering with Format 4
                $widget_counter++;
            }
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_element) {
                $this->process_elementor_element_by_class($child_element, $extracted_content, $widget_counter, $class_filter, $equal_separator, $dash_separator, $filtered_widgets);
            }
        }
    }
    
    /**
     * AJAX handler to refresh blueshift data
     */
    public function ajax_refresh_blueshift_data() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Extract fresh data for all formats
        $blueshift_content_format1 = $this->extract_elementor_blueshift_content($post_id);
        $blueshift_content_format2 = $this->extract_elementor_blueshift_content_format2($post_id);
        $blueshift_content_format3 = $this->extract_elementor_blueshift_content_format3($post_id);
        
        // Limit length for display if too long
        if (strlen($blueshift_content_format1) > 50000) {
            $blueshift_content_format1 = substr($blueshift_content_format1, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
        }
        
        if (strlen($blueshift_content_format2) > 50000) {
            $blueshift_content_format2 = substr($blueshift_content_format2, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
        }
        
        if (strlen($blueshift_content_format3) > 50000) {
            $blueshift_content_format3 = substr($blueshift_content_format3, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
        }
        
        wp_send_json_success(array(
            'content_format1' => $blueshift_content_format1,
            'content_format2' => $blueshift_content_format2,
            'content_format3' => $blueshift_content_format3
        ));
    }
    
    /**
     * AJAX handler to update filtered format 4 content
     */
    public function ajax_update_format4_filtered() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Get excluded classes from POST
        $excluded_classes = array();
        
        // Check which classes should be excluded (unchecked means exclude)
        if (!isset($_POST['show_exclude_from_blueshift']) || $_POST['show_exclude_from_blueshift'] !== 'true') {
            $excluded_classes[] = 'exclude_from_blueshift';
        }
        
        if (!isset($_POST['show_guarded']) || $_POST['show_guarded'] !== 'true') {
            $excluded_classes[] = 'guarded';
        }
        
        // Extract format 4 content with filtering
        $blueshift_content_format4 = $this->extract_elementor_blueshift_content_format4_filtered($post_id, $excluded_classes);
        
        // Limit length for display if too long
        if (strlen($blueshift_content_format4) > 50000) {
            $blueshift_content_format4 = substr($blueshift_content_format4, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
        }
        
        wp_send_json_success(array(
            'content_format4' => $blueshift_content_format4
        ));
    }
}