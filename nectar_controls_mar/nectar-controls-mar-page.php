<?php
/**
 * Nectar Controls Mar Management Page
 * 
 * Manages Nectar-related settings for posts from wp_pylons table
 * 
 * @package Ruplin
 * @subpackage NectarControlsMar
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nectar Controls Mar Management Page Class
 */
class Ruplin_Nectar_Controls_Mar_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_action('admin_init', array($this, 'handle_form_submission'));
        
        // Aggressive notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
        
        // AJAX handlers for inline saving
        add_action('wp_ajax_nectar_controls_save_field', array($this, 'ajax_save_field'));
    }
    
    /**
     * Add submenu page to Ruplin Hub
     */
    public function add_admin_menu() {
        add_submenu_page(
            'snefuru',
            'Nectar Controls Mar',
            'Nectar Controls Mar',
            'manage_options',
            'nectar_controls_mar',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'nectar_controls_mar') {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            add_action('admin_notices', '__return_false', -999999);
            add_action('all_admin_notices', '__return_false', -999999);
            add_action('network_admin_notices', '__return_false', -999999);
            add_action('user_admin_notices', '__return_false', -999999);
        }
    }
    
    /**
     * Check current screen and suppress notices
     */
    public function check_and_suppress_notices($screen) {
        if (!$screen) {
            return;
        }
        
        if (strpos($screen->id, 'nectar_controls_mar') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'nectar_controls_mar')) {
            
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices'); 
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            add_action('admin_notices', '__return_false', 999);
            add_action('all_admin_notices', '__return_false', 999); 
            add_action('network_admin_notices', '__return_false', 999);
            add_action('user_admin_notices', '__return_false', 999);
        }
    }
    
    /**
     * Handle form submission
     */
    public function handle_form_submission() {
        if (!isset($_POST['nectar_controls_submit']) || !check_admin_referer('nectar_controls_nonce', 'nectar_controls_nonce')) {
            return;
        }
        
        global $wpdb;
        $pylons_table = $wpdb->prefix . 'pylons';
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            return;
        }
        
        // Get all nectar-related fields from form
        $nectar_fields = array(
            'enable_nectar_blog_feed',
            'nectar_blog_feed_items_qty',
            'nectar_blog_is_excerpt'
        );
        
        // Build update data
        $update_data = array();
        foreach ($nectar_fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                
                // Handle checkbox fields
                if ($field === 'enable_nectar_blog_feed' || $field === 'nectar_blog_is_excerpt') {
                    $update_data[$field] = ($value === '1' || $value === 'true') ? 1 : 0;
                } else {
                    $update_data[$field] = sanitize_text_field($value);
                }
            }
        }
        
        if (!empty($update_data)) {
            // Check if record exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $pylons_table WHERE rel_wp_post_id = %d",
                $post_id
            ));
            
            if ($exists) {
                // Update existing record
                $result = $wpdb->update(
                    $pylons_table,
                    $update_data,
                    array('rel_wp_post_id' => $post_id),
                    null,
                    array('%d')
                );
            } else {
                // Create new record
                $update_data['rel_wp_post_id'] = $post_id;
                $update_data['created_at'] = current_time('mysql');
                $result = $wpdb->insert(
                    $pylons_table,
                    $update_data
                );
            }
            
            if ($result !== false) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible nectar-success-notice"><p>Nectar settings saved successfully!</p></div>';
                });
            }
        }
    }
    
    /**
     * AJAX handler for saving individual fields
     */
    public function ajax_save_field() {
        check_ajax_referer('nectar_controls_ajax', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $pylons_table = $wpdb->prefix . 'pylons';
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $field_name = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
        $field_value = isset($_POST['value']) ? $_POST['value'] : '';
        
        if (!$post_id || !$field_name) {
            wp_send_json_error('Missing required parameters');
        }
        
        // Sanitize value based on field type
        if (in_array($field_name, array('enable_nectar_blog_feed', 'nectar_blog_is_excerpt'))) {
            $field_value = ($field_value === 'true' || $field_value === '1') ? 1 : 0;
        } else {
            $field_value = sanitize_text_field($field_value);
        }
        
        // Check if record exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $pylons_table WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        if ($exists) {
            $result = $wpdb->update(
                $pylons_table,
                array($field_name => $field_value),
                array('rel_wp_post_id' => $post_id)
            );
        } else {
            $result = $wpdb->insert(
                $pylons_table,
                array(
                    'rel_wp_post_id' => $post_id,
                    $field_name => $field_value,
                    'created_at' => current_time('mysql')
                )
            );
        }
        
        if ($result !== false) {
            wp_send_json_success('Field saved successfully');
        } else {
            wp_send_json_error('Failed to save field');
        }
    }
    
    /**
     * Get current Nectar values from database
     */
    private function get_nectar_values($post_id) {
        global $wpdb;
        $pylons_table = $wpdb->prefix . 'pylons';
        
        // Get all columns that start with 'nectar' or are related to nectar functionality
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                enable_nectar_blog_feed,
                nectar_blog_feed_items_qty,
                nectar_blog_is_excerpt
            FROM $pylons_table 
            WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        // Set defaults if no record exists
        if (!$result) {
            $result = array(
                'enable_nectar_blog_feed' => 0,
                'nectar_blog_feed_items_qty' => 6,
                'nectar_blog_is_excerpt' => 1
            );
        }
        
        return $result;
    }
    
    /**
     * Suppress all admin notices - comprehensive version
     */
    private function suppress_all_admin_notices() {
        add_action('admin_print_styles', function() {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            
            global $wp_filter;
            if (isset($wp_filter['user_admin_notices'])) {
                unset($wp_filter['user_admin_notices']);
            }
        }, 0);
        
        add_action('admin_head', function() {
            echo '<style type="text/css">
                .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
                .updated, .error, .update-nag, .admin-notice,
                div.notice, div.updated, div.error, div.update-nag,
                .wrap > .notice, .wrap > .updated, .wrap > .error,
                #adminmenu + .notice, #adminmenu + .updated, #adminmenu + .error,
                .update-php, .php-update-nag,
                .plugin-update-tr, .theme-update-message,
                .update-message, .updating-message,
                #update-nag, #deprecation-warning {
                    display: none !important;
                }
                
                .update-core-php, .notice-alt {
                    display: none !important;
                }
                
                .activated, .deactivated {
                    display: none !important;
                }
                
                .notice-warning, .notice-error {
                    display: none !important;
                }
                
                .wrap .notice:first-child,
                .wrap > div.notice,
                .wrap > div.updated,
                .wrap > div.error {
                    display: none !important;
                }
                
                [class*="notice"], [class*="updated"], [class*="error"],
                [id*="notice"], [id*="message"] {
                    display: none !important;
                }
                
                .wrap h1, .wrap .nectar-controls-content {
                    display: block !important;
                }
                
                /* Allow our success/error messages */
                .nectar-success-notice, .nectar-error-notice {
                    display: block !important;
                }
            </style>';
        }, 1);
        
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        add_action('admin_notices', '__return_false', PHP_INT_MAX);
        add_action('all_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('network_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('user_admin_notices', '__return_false', PHP_INT_MAX);
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        // Get post ID from URL parameter
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        
        if (!$post_id) {
            ?>
            <div class="wrap nectar-controls-content">
                <h1>Nectar Controls Mar</h1>
                <div style="margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #ddd;">
                    <p style="font-size: 16px;">No post ID provided. Please access this page from a post edit screen or provide a post ID in the URL (?post=123).</p>
                </div>
            </div>
            <?php
            return;
        }
        
        // Get post title for display
        $post_title = get_the_title($post_id);
        
        // Get current values
        $nectar_values = $this->get_nectar_values($post_id);
        
        // Define field configurations
        $fields = array(
            'enable_nectar_blog_feed' => array(
                'type' => 'checkbox',
                'label' => 'enable_nectar_blog_feed',
                'description' => 'Enable the Nectar blog feed on this page'
            ),
            'nectar_blog_feed_items_qty' => array(
                'type' => 'number',
                'label' => 'nectar_blog_feed_items_qty',
                'description' => 'Number of blog posts to display (default: 6)'
            ),
            'nectar_blog_is_excerpt' => array(
                'type' => 'checkbox',
                'label' => 'nectar_blog_is_excerpt',
                'description' => 'Show excerpt (checked) or full content (unchecked)'
            )
        );
        
        ?>
        <div class="wrap nectar-controls-content">
            <h1>Nectar Controls Mar</h1>
            
            <div style="margin: 20px 0; padding: 15px; background: #f0f0f0; border-left: 4px solid #0073aa;">
                <strong>Editing Post:</strong> <?php echo esc_html($post_title); ?> (ID: <?php echo esc_html($post_id); ?>)
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('nectar_controls_nonce', 'nectar_controls_nonce'); ?>
                <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                
                <div class="nectar-table-container" style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: auto; margin-top: 20px;">
                    <table class="nectar-controls-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6; width: 300px;">
                                    <strong>Setting</strong>
                                </th>
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">
                                    <strong>Value</strong>
                                </th>
                                <th style="padding: 12px; text-align: left;">
                                    <strong>Description</strong>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fields as $field_name => $field_config): ?>
                                <?php
                                $current_value = isset($nectar_values[$field_name]) ? $nectar_values[$field_name] : '';
                                ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6;">
                                        <label for="<?php echo esc_attr($field_name); ?>" style="font-weight: bold; font-size: 14px; color: #333;">
                                            <?php echo esc_html($field_config['label']); ?>
                                        </label>
                                    </td>
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6;">
                                        <?php if ($field_config['type'] === 'checkbox'): ?>
                                            <input type="checkbox" 
                                                   id="<?php echo esc_attr($field_name); ?>"
                                                   name="<?php echo esc_attr($field_name); ?>" 
                                                   value="1"
                                                   class="nectar-field"
                                                   data-field="<?php echo esc_attr($field_name); ?>"
                                                   <?php checked($current_value, 1); ?>
                                                   style="transform: scale(1.2);">
                                        <?php elseif ($field_config['type'] === 'number'): ?>
                                            <input type="number" 
                                                   id="<?php echo esc_attr($field_name); ?>"
                                                   name="<?php echo esc_attr($field_name); ?>" 
                                                   value="<?php echo esc_attr($current_value); ?>"
                                                   class="nectar-field"
                                                   data-field="<?php echo esc_attr($field_name); ?>"
                                                   min="1"
                                                   max="50"
                                                   style="width: 100px; padding: 5px;">
                                        <?php else: ?>
                                            <input type="text" 
                                                   id="<?php echo esc_attr($field_name); ?>"
                                                   name="<?php echo esc_attr($field_name); ?>" 
                                                   value="<?php echo esc_attr($current_value); ?>"
                                                   class="nectar-field"
                                                   data-field="<?php echo esc_attr($field_name); ?>"
                                                   style="width: 300px; padding: 5px;">
                                        <?php endif; ?>
                                        <span class="save-indicator" style="margin-left: 10px; display: none; color: green;">✓ Saved</span>
                                    </td>
                                    <td style="padding: 12px; color: #666; font-size: 13px;">
                                        <?php echo esc_html($field_config['description']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin: 20px 0;">
                    <button type="submit" name="nectar_controls_submit" class="button button-primary button-large">
                        💾 Save All Changes
                    </button>
                    <a href="<?php echo get_edit_post_link($post_id); ?>" class="button button-secondary" style="margin-left: 10px;">
                        ← Back to Edit Post
                    </a>
                </div>
            </form>
        </div>
        
        <style>
        .nectar-controls-table tr:hover {
            background-color: #f0f8ff !important;
        }
        
        .nectar-field.saving {
            background-color: #ffffcc !important;
        }
        
        .save-indicator.show {
            display: inline !important;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Auto-save on field change
            $('.nectar-field').on('change', function() {
                var $field = $(this);
                var fieldName = $field.data('field');
                var fieldValue;
                
                if ($field.attr('type') === 'checkbox') {
                    fieldValue = $field.is(':checked') ? 1 : 0;
                } else {
                    fieldValue = $field.val();
                }
                
                var $indicator = $field.siblings('.save-indicator');
                $field.addClass('saving');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'nectar_controls_save_field',
                        nonce: '<?php echo wp_create_nonce('nectar_controls_ajax'); ?>',
                        post_id: <?php echo $post_id; ?>,
                        field: fieldName,
                        value: fieldValue
                    },
                    success: function(response) {
                        $field.removeClass('saving');
                        if (response.success) {
                            $indicator.addClass('show');
                            setTimeout(function() {
                                $indicator.removeClass('show');
                            }, 2000);
                        }
                    },
                    error: function() {
                        $field.removeClass('saving');
                        alert('Error saving field');
                    }
                });
            });
        });
        </script>
        <?php
    }
}

// Initialize the class
new Ruplin_Nectar_Controls_Mar_Page();