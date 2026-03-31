<?php
/**
 * Work Projects Mar Management Page
 * 
 * Professional work projects management interface
 * 
 * @package Ruplin
 * @subpackage ProfessionalWorkProjects
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Work Projects Mar Management Page Class
 */
class Ruplin_Work_Projects_Mar_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        
        // Aggressive notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
        
        // AJAX handlers for inline editing
        add_action('wp_ajax_work_projects_mar_update', array($this, 'ajax_update_field'));
        add_action('wp_ajax_work_projects_mar_create', array($this, 'ajax_create_record'));
        add_action('wp_ajax_work_projects_mar_delete', array($this, 'ajax_delete_record'));
    }
    
    /**
     * Add submenu page to Ruplin Hub
     */
    public function add_admin_menu() {
        add_submenu_page(
            'snefuru',
            'Work Projects Mar',
            'Work Projects Mar',
            'manage_options',
            'work_projects_mar',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'work_projects_mar') {
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
        
        if (strpos($screen->id, 'work_projects_mar') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'work_projects_mar')) {
            
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
                
                .wrap h1, .wrap .work-projects-mar-content {
                    display: block !important;
                }
                
                /* Allow our success/error messages if needed later */
                .work-projects-success-notice, .work-projects-error-notice {
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
     * Get database columns
     */
    private function get_table_columns() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'work_projects';
        
        // Get column information
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
        
        if (!$columns) {
            // If table doesn't exist, create it with a basic structure
            $this->create_table_if_not_exists();
            $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
        }
        
        return $columns;
    }
    
    /**
     * Create table if it doesn't exist
     */
    private function create_table_if_not_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'work_projects';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            project_name varchar(255) DEFAULT NULL,
            client_name varchar(255) DEFAULT NULL,
            project_date date DEFAULT NULL,
            status varchar(50) DEFAULT NULL,
            description text DEFAULT NULL,
            technologies text DEFAULT NULL,
            project_url varchar(255) DEFAULT NULL,
            featured tinyint(1) DEFAULT 0,
            display_order int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get all records
     */
    private function get_all_records() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'work_projects';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY display_order ASC, id DESC", ARRAY_A);
    }
    
    /**
     * AJAX: Update field
     */
    public function ajax_update_field() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'work_projects';
        
        $record_id = intval($_POST['id']);
        $field = sanitize_text_field($_POST['field']);
        $value = $_POST['value']; // Don't sanitize here, handle per field type
        
        // Update the field
        $result = $wpdb->update(
            $table_name,
            array($field => $value),
            array('id' => $record_id)
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Update failed'));
        }
    }
    
    /**
     * AJAX: Create new record
     */
    public function ajax_create_record() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'work_projects';
        
        // Insert new empty record
        $result = $wpdb->insert(
            $table_name,
            array(
                'project_name' => 'New Project',
                'status' => 'draft'
            )
        );
        
        if ($result !== false) {
            $new_id = $wpdb->insert_id;
            wp_send_json_success(array('id' => $new_id, 'message' => 'Created successfully'));
        } else {
            wp_send_json_error(array('message' => 'Creation failed'));
        }
    }
    
    /**
     * AJAX: Delete record
     */
    public function ajax_delete_record() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'work_projects';
        
        $record_id = intval($_POST['id']);
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $record_id)
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Deletion failed'));
        }
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        // Get columns and data
        $columns = $this->get_table_columns();
        $records = $this->get_all_records();
        
        ?>
        <div class="wrap work-projects-mar-content">
            <h1>Work Projects Mar</h1>
            
            <div style="margin: 20px 0;">
                <button id="work-projects-create-new" class="button button-primary">
                    Create New (Inline)
                </button>
            </div>
            
            <div class="work-projects-table-container" style="background: white; padding: 0; border: 1px solid #ccc;">
                <table id="work-projects-table" class="work-projects-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $column): ?>
                                <th style="border: 1px solid gray; padding: 10px; text-align: left; font-size: 16px; color: #242424; font-weight: normal;">
                                    <?php echo strtolower($column->Field); ?>
                                </th>
                            <?php endforeach; ?>
                            <th style="border: 1px solid gray; padding: 10px; text-align: center; font-size: 16px; color: #242424; font-weight: normal;">
                                actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): ?>
                                <tr data-id="<?php echo esc_attr($record['id']); ?>">
                                    <?php foreach ($columns as $column): ?>
                                        <?php 
                                        $field = $column->Field;
                                        $value = isset($record[$field]) ? $record[$field] : '';
                                        $type = $column->Type;
                                        
                                        // Determine if field should be editable
                                        $editable = !in_array($field, ['id', 'created_at', 'updated_at']);
                                        ?>
                                        <td style="border: 1px solid gray; padding: 8px;">
                                            <?php if ($editable): ?>
                                                <div class="editable-field" 
                                                     contenteditable="true"
                                                     data-field="<?php echo esc_attr($field); ?>"
                                                     data-original="<?php echo esc_attr($value); ?>"
                                                     style="min-height: 20px; outline: none; padding: 2px;">
                                                    <?php echo esc_html($value); ?>
                                                </div>
                                            <?php else: ?>
                                                <div style="color: #666; padding: 2px;">
                                                    <?php echo esc_html($value); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td style="border: 1px solid gray; padding: 8px; text-align: center;">
                                        <button class="button button-small delete-record" data-id="<?php echo esc_attr($record['id']); ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo count($columns) + 1; ?>" style="border: 1px solid gray; padding: 20px; text-align: center; color: #666;">
                                    No records found. Click "Create New (Inline)" to add your first project.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <style>
        .work-projects-table .editable-field:hover {
            background-color: #f0f8ff;
            cursor: text;
        }
        
        .work-projects-table .editable-field:focus {
            background-color: #fff;
            box-shadow: inset 0 0 3px #0073aa;
        }
        
        .work-projects-table .editable-field.saving {
            background-color: #ffffcc;
        }
        
        .work-projects-table .editable-field.saved {
            background-color: #d4edda;
            transition: background-color 0.5s ease;
        }
        
        .work-projects-table .editable-field.error {
            background-color: #f8d7da;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var nonce = '<?php echo wp_create_nonce('work_projects_mar_nonce'); ?>';
            
            // Inline editing
            $('.editable-field').on('blur', function() {
                var $this = $(this);
                var newValue = $this.text().trim();
                var originalValue = $this.data('original');
                var field = $this.data('field');
                var recordId = $this.closest('tr').data('id');
                
                if (newValue !== originalValue.toString()) {
                    $this.addClass('saving');
                    
                    $.post(ajaxurl, {
                        action: 'work_projects_mar_update',
                        nonce: nonce,
                        id: recordId,
                        field: field,
                        value: newValue
                    }, function(response) {
                        $this.removeClass('saving');
                        
                        if (response.success) {
                            $this.addClass('saved');
                            $this.data('original', newValue);
                            setTimeout(function() {
                                $this.removeClass('saved');
                            }, 1500);
                        } else {
                            $this.addClass('error');
                            setTimeout(function() {
                                $this.removeClass('error');
                            }, 1500);
                        }
                    });
                }
            });
            
            // Prevent enter key from creating line breaks
            $('.editable-field').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).blur();
                }
            });
            
            // Create new record
            $('#work-projects-create-new').on('click', function() {
                var $button = $(this);
                $button.prop('disabled', true).text('Creating...');
                
                $.post(ajaxurl, {
                    action: 'work_projects_mar_create',
                    nonce: nonce
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to create new record');
                        $button.prop('disabled', false).text('Create New (Inline)');
                    }
                });
            });
            
            // Delete record
            $('.delete-record').on('click', function() {
                if (!confirm('Are you sure you want to delete this record?')) {
                    return;
                }
                
                var $button = $(this);
                var recordId = $button.data('id');
                
                $button.prop('disabled', true).text('Deleting...');
                
                $.post(ajaxurl, {
                    action: 'work_projects_mar_delete',
                    nonce: nonce,
                    id: recordId
                }, function(response) {
                    if (response.success) {
                        $button.closest('tr').fadeOut(400, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Failed to delete record');
                        $button.prop('disabled', false).text('Delete');
                    }
                });
            });
        });
        </script>
        <?php
    }
}

// Initialize the class
new Ruplin_Work_Projects_Mar_Page();