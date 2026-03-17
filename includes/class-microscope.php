<?php
/**
 * Microscope Data Handler Class
 * 
 * Centralized class for handling Microscope data copying functionality
 * Single source of truth for field configuration and data formatting
 */

class Ruplin_Microscope {
    
    /**
     * Define which fields to copy from wp_posts table
     * Easy to modify in the future by changing this array
     */
    private static $post_fields = array(
        'ID' => 'post_id',
        'post_name' => 'post_name',
        'post_title' => 'post_title',
        'post_date' => 'post_date',
        'post_status' => 'post_status',
        'post_content' => 'post_content',
        'post_type' => 'post_type'
    );
    
    /**
     * Define which fields to exclude from pylons table
     * These won't be included even if they exist in the table
     */
    private static $pylons_exclude_fields = array(
        'id', // Usually don't need the pylons table's own ID
        'created_at', // May not be needed
        'updated_at' // May not be needed
    );
    
    /**
     * Get formatted Microscope data for a given post ID
     * This is the main method that both the toolbar button and Telescope editor button will use
     * 
     * @param int $post_id The WordPress post ID
     * @return array|WP_Error Array with 'success' and 'data' keys, or WP_Error on failure
     */
    public static function get_formatted_data($post_id) {
        global $wpdb;
        
        // Validate post ID
        $post_id = intval($post_id);
        if (!$post_id) {
            return new WP_Error('invalid_post_id', 'Invalid post ID provided');
        }
        
        // Get post data
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('post_not_found', 'Post not found');
        }
        
        // Start building formatted data with new metadata fields
        $formatted_data = '';
        
        // Add source URL
        $source_url = get_permalink($post_id);
        $formatted_data .= "### source_url_copied_from\n";
        $formatted_data .= $source_url . "\n\n";
        
        // Add source domain (extract and clean)
        $parsed_url = parse_url($source_url);
        $domain = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        // Remove www. if present
        $domain = preg_replace('/^www\./', '', $domain);
        $formatted_data .= "### source_domain_copied_from\n";
        $formatted_data .= $domain . "\n\n";
        
        // Add timestamp
        $formatted_data .= "### date_copied_at\n";
        $formatted_data .= current_time('Y-m-d H:i:s') . "\n\n";
        
        // Add post fields
        foreach (self::$post_fields as $wp_field => $display_name) {
            $value = '';
            if ($wp_field === 'ID') {
                $value = $post->ID;
            } else if (property_exists($post, $wp_field)) {
                $value = $post->$wp_field;
            }
            
            $formatted_data .= "### " . $display_name . "\n";
            $formatted_data .= ($value !== null ? $value : '') . "\n\n";
        }
        
        // Get pylons data if exists
        $pylons_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        // Add pylons data if exists
        if ($pylons_data) {
            foreach ($pylons_data as $column => $value) {
                // Skip excluded fields
                if (in_array($column, self::$pylons_exclude_fields)) {
                    continue;
                }
                
                $formatted_data .= "### " . $column . "\n";
                $formatted_data .= ($value !== null ? $value : '') . "\n\n";
            }
        }
        
        return array(
            'success' => true,
            'data' => $formatted_data
        );
    }
    
    /**
     * Output the JavaScript function for copying data
     * This can be used by any page that needs the copy functionality
     * 
     * @param string $button_selector jQuery selector for the button element
     * @param bool $include_jquery Whether to include jQuery (set false if jQuery is already loaded)
     */
    public static function output_copy_script($button_selector = '.microscope-copy-btn', $include_jquery = false) {
        ?>
        <script type="text/javascript">
        <?php if ($include_jquery): ?>
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is required for Microscope copy functionality');
        }
        <?php endif; ?>
        
        function copyMicroscopeData(postId, buttonElement) {
            // Allow passing either element or use default selector
            const button = buttonElement || document.querySelector('<?php echo esc_js($button_selector); ?>');
            if (!button) {
                console.error('Microscope button not found');
                return;
            }
            
            const originalText = button.textContent || button.innerHTML;
            const originalBg = button.style.background || '';
            
            // Show loading state
            if (button.tagName === 'A' || button.tagName === 'SPAN') {
                button.textContent = 'Copying...';
            } else {
                button.innerHTML = 'Copying...';
            }
            
            // Make AJAX call to get formatted data
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'microscope_get_page_data',
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        // Copy to clipboard using modern API if available
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(response.data.data).then(function() {
                                showSuccess();
                            }).catch(function(err) {
                                fallbackCopy(response.data.data);
                            });
                        } else {
                            fallbackCopy(response.data.data);
                        }
                        
                        function fallbackCopy(text) {
                            const textarea = document.createElement('textarea');
                            textarea.value = text;
                            textarea.style.position = 'fixed';
                            textarea.style.left = '-999999px';
                            textarea.style.top = '-999999px';
                            document.body.appendChild(textarea);
                            textarea.focus();
                            textarea.select();
                            
                            try {
                                document.execCommand('copy');
                                showSuccess();
                            } catch (err) {
                                showError();
                            }
                            
                            document.body.removeChild(textarea);
                        }
                        
                        function showSuccess() {
                            if (button.tagName === 'A' || button.tagName === 'SPAN') {
                                button.textContent = '✓ Copied!';
                            } else {
                                button.innerHTML = '✓ Copied!';
                            }
                            button.style.background = 'green';
                            button.style.color = 'white';
                            
                            // Reset after 2 seconds
                            setTimeout(function() {
                                if (button.tagName === 'A' || button.tagName === 'SPAN') {
                                    button.textContent = originalText;
                                } else {
                                    button.innerHTML = originalText;
                                }
                                button.style.background = originalBg;
                                button.style.color = '';
                            }, 2000);
                        }
                        
                        function showError() {
                            if (button.tagName === 'A' || button.tagName === 'SPAN') {
                                button.textContent = 'Copy Failed!';
                            } else {
                                button.innerHTML = 'Copy Failed!';
                            }
                            setTimeout(function() {
                                if (button.tagName === 'A' || button.tagName === 'SPAN') {
                                    button.textContent = originalText;
                                } else {
                                    button.innerHTML = originalText;
                                }
                            }, 2000);
                        }
                    } else {
                        button.textContent = 'Error: ' + (response.data || 'Unknown error');
                        setTimeout(function() {
                            if (button.tagName === 'A' || button.tagName === 'SPAN') {
                                button.textContent = originalText;
                            } else {
                                button.innerHTML = originalText;
                            }
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    button.textContent = 'Error!';
                    console.error('Microscope AJAX error:', error);
                    setTimeout(function() {
                        if (button.tagName === 'A' || button.tagName === 'SPAN') {
                            button.textContent = originalText;
                        } else {
                            button.innerHTML = originalText;
                        }
                    }, 2000);
                }
            });
        }
        
        // Attach click handlers to any microscope buttons on the page
        jQuery(document).ready(function($) {
            $(document).on('click', '<?php echo esc_js($button_selector); ?>', function(e) {
                e.preventDefault();
                const postId = $(this).data('post-id') || $(this).attr('data-post-id');
                if (postId) {
                    copyMicroscopeData(postId, this);
                } else {
                    console.error('No post ID found on button');
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Output CSS styles for Microscope buttons
     * Can be customized per implementation
     * 
     * @param array $custom_styles Array of CSS properties to override defaults
     */
    public static function output_button_styles($custom_styles = array()) {
        $default_styles = array(
            'background' => 'maroon',
            'color' => 'white',
            'padding' => '8px 15px',
            'border' => 'none',
            'border-radius' => '4px',
            'cursor' => 'pointer',
            'font-weight' => '600',
            'transition' => 'all 0.3s ease'
        );
        
        $styles = array_merge($default_styles, $custom_styles);
        ?>
        <style type="text/css">
            .microscope-copy-btn {
                <?php foreach ($styles as $property => $value): ?>
                <?php echo esc_attr($property); ?>: <?php echo esc_attr($value); ?>;
                <?php endforeach; ?>
            }
            
            .microscope-copy-btn:hover {
                background: #8B0000 !important;
                transform: scale(1.05);
            }
            
            .microscope-copy-btn:before {
                content: "🔬 ";
                font-size: 16px;
                vertical-align: middle;
            }
        </style>
        <?php
    }
}