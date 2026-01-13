<?php
/**
 * Weasel Mar (Contact Form) Management Page
 * 
 * Manages weasel codes and contact form settings from wp_zen_sitespren table
 * 
 * @package Ruplin
 * @subpackage WeaselMar
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Weasel Mar Management Page Class
 */
class Ruplin_Weasel_Mar_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_action('admin_init', array($this, 'handle_form_submission'));
        
        // Aggressive notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
    }
    
    /**
     * Add submenu page to Ruplin Hub
     */
    public function add_admin_menu() {
        add_submenu_page(
            'snefuru',
            'Weasel Mar (Contact Form)',
            'Weasel_Mar (contact form)',
            'manage_options',
            'weasel_mar',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'weasel_mar') {
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
        
        if (strpos($screen->id, 'weasel_mar') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'weasel_mar')) {
            
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
        if (isset($_POST['weasel_mar_submit']) && check_admin_referer('weasel_mar_nonce', 'weasel_mar_nonce')) {
            global $wpdb;
            
            $sitespren_table = $wpdb->prefix . 'zen_sitespren';
            
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$sitespren_table'") != $sitespren_table) {
                return;
            }
            
            // Define the fields we're updating
            $fields = array(
                'contact_form_1_endpoint',
                'contact_form_1_main_code',
                'weasel_header_code_for_contact_form',
                'weasel_footer_code_for_contact_form',
                'weasel_header_code_for_analytics',
                'weasel_footer_code_for_analytics',
                'weasel_header_code_1',
                'weasel_footer_code_1'
            );
            
            // Build update query
            $update_data = array();
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $update_data[$field] = wp_unslash($_POST[$field]);
                }
            }
            
            if (!empty($update_data)) {
                // Update the single row in zen_sitespren table
                $result = $wpdb->update(
                    $sitespren_table,
                    $update_data,
                    array('wppma_id' => 1), // Assuming the main row has wppma_id = 1
                    array_fill(0, count($update_data), '%s'), // All fields are text
                    array('%d') // wppma_id is integer
                );
                
                if ($result !== false) {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible weasel-success-notice"><p>Settings saved successfully!</p></div>';
                    });
                } else {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-error is-dismissible weasel-error-notice"><p>Failed to save settings.</p></div>';
                    });
                }
            }
        }
    }
    
    /**
     * Get current values from database
     */
    private function get_current_values() {
        global $wpdb;
        
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$sitespren_table'") != $sitespren_table) {
            return array();
        }
        
        $result = $wpdb->get_row("SELECT * FROM $sitespren_table LIMIT 1", ARRAY_A);
        
        return $result ? $result : array();
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
                
                .wrap h1, .wrap .weasel-mar-content {
                    display: block !important;
                }
                
                /* Allow our success/error messages */
                .weasel-success-notice, .weasel-error-notice {
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
        
        // Get current values
        $current_values = $this->get_current_values();
        
        // Define fields in the order requested
        $fields = array(
            'contact_form_1_endpoint',
            'contact_form_1_main_code',
            'weasel_header_code_for_contact_form',
            'weasel_footer_code_for_contact_form',
            'weasel_header_code_for_analytics',
            'weasel_footer_code_for_analytics',
            'weasel_header_code_1',
            'weasel_footer_code_1'
        );
        
        ?>
        <div class="wrap weasel-mar-content">
            <h1>Weasel Mar (Contact Form) Management</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('weasel_mar_nonce', 'weasel_mar_nonce'); ?>
                
                <div style="margin: 20px 0;">
                    <button type="submit" name="weasel_mar_submit" class="button button-primary button-large">
                        💾 Save All Changes
                    </button>
                </div>
                
                <div class="weasel-mar-table-container" style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: auto; margin-top: 20px; display: inline-block;">
                    <table class="weasel-mar-table" style="width: auto; border-collapse: collapse; table-layout: auto;">
                        <thead>
                            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6; width: auto; white-space: nowrap;">
                                    <input type="checkbox" id="select-all-checkbox" style="transform: scale(1.2);">
                                </th>
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6; font-weight: bold; color: #495057; width: auto; white-space: nowrap;">
                                    field-name
                                </th>
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6; font-weight: bold; color: #495057; width: auto; min-width: 300px;">
                                    datum-house
                                </th>
                                <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6; font-weight: bold; color: #495057; width: auto; white-space: nowrap;">
                                    colg
                                </th>
                                <th style="padding: 12px; text-align: left; font-weight: bold; color: #495057; width: auto; white-space: nowrap;">
                                    colh
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fields as $index => $field_name): ?>
                                <tr style="border-bottom: 1px solid #dee2e6; <?php echo $index % 2 === 0 ? 'background: #f8f9fa;' : 'background: white;'; ?>">
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6; text-align: center; width: auto; white-space: nowrap;">
                                        <input type="checkbox" class="row-checkbox" style="transform: scale(1.1);">
                                    </td>
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6; font-family: monospace; font-size: 13px; color: #333; width: auto; white-space: nowrap;">
                                        <strong><?php echo esc_html($field_name); ?></strong>
                                    </td>
                                    <td style="padding: 8px; border-right: 1px solid #dee2e6; width: auto;">
                                        <textarea 
                                            name="<?php echo esc_attr($field_name); ?>" 
                                            rows="3" 
                                            style="width: 300px; min-height: 60px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 12px; resize: vertical;"
                                            placeholder="Enter <?php echo esc_attr($field_name); ?> value..."
                                        ><?php echo esc_textarea(isset($current_values[$field_name]) ? $current_values[$field_name] : ''); ?></textarea>
                                    </td>
                                    <td style="padding: 12px; border-right: 1px solid #dee2e6; color: #666; font-size: 13px; width: auto; white-space: nowrap;">
                                        <!-- colg placeholder -->
                                        -
                                    </td>
                                    <td style="padding: 12px; color: #666; font-size: 13px; width: auto; white-space: nowrap;">
                                        <!-- colh placeholder -->
                                        -
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin: 20px 0; padding-top: 20px; border-top: 1px solid #ddd;">
                    <button type="submit" name="weasel_mar_submit" class="button button-primary button-large">
                        💾 Save All Changes
                    </button>
                    <span style="margin-left: 15px; color: #666; font-size: 13px;">
                        Changes will be saved to the wp_zen_sitespren database table.
                    </span>
                </div>
            </form>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all checkbox functionality
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            
            selectAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
            
            // Update select all when individual checkboxes change
            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
                    const noneChecked = Array.from(rowCheckboxes).every(cb => !cb.checked);
                    
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
                });
            });
        });
        </script>
        
        <style>
        .weasel-mar-table-container {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .weasel-mar-table th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .weasel-mar-table tr:hover {
            background-color: #f0f8ff !important;
        }
        
        .weasel-mar-table textarea:focus {
            border-color: #0073aa;
            box-shadow: 0 0 0 1px #0073aa;
            outline: none;
        }
        
        .button-large {
            font-size: 14px !important;
            padding: 8px 16px !important;
        }
        </style>
        <?php
    }
}

// Initialize the class
new Ruplin_Weasel_Mar_Page();