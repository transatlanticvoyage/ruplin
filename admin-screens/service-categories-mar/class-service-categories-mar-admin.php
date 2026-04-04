<?php
/**
 * Service Categories MAR Admin Page
 * 
 * Handles the service categories management interface in WordPress admin
 * URL: /wp-admin/admin.php?page=service_categories_mar
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Service_Categories_Mar_Admin {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Create table on activation
        $this->maybe_create_table();
        
        // Add admin menu with priority to ensure it loads before Service Category Assigner
        add_action('admin_menu', array($this, 'add_admin_menu'), 24);
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_service_categories_mar_get_data', array($this, 'ajax_get_data'));
        add_action('wp_ajax_service_categories_mar_save_field', array($this, 'ajax_save_field'));
        add_action('wp_ajax_service_categories_mar_create_new', array($this, 'ajax_create_new'));
        add_action('wp_ajax_service_categories_mar_delete_row', array($this, 'ajax_delete_row'));
    }
    
    /**
     * Add menu item to WordPress admin
     */
    public function add_admin_menu() {
        // Add as submenu under Ruplin Hub 1
        add_submenu_page(
            'ruplin_hub_2_mar',  // Parent slug (Ruplin Hub 2)
            'Service Categories MAR',  // Page title
            'Service Categories Mar',  // Menu title
            'manage_options',  // Capability
            'service_categories_mar',  // Menu slug
            array($this, 'render_admin_page')  // Callback
        );
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // Aggressive notice/warning suppression
        $this->suppress_admin_notices();
        
        // Get data from database
        $data = $this->get_service_categories_data();
        $columns = $this->get_table_columns();
        
        ?>
        <div class="wrap service-categories-mar-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="service-categories-mar-container">
                <div class="table-description" style="font-size: 16px; color: #242424; margin-bottom: 15px;">
                    wp_service_categories
                </div>
                
                <div class="table-actions">
                    <button id="create-new-row" class="button button-primary">Create New (Inline)</button>
                </div>
                
                <div class="table-wrapper">
                    <table id="service-categories-table" class="service-categories-table">
                        <thead>
                            <tr>
                                <?php foreach ($columns as $column): ?>
                                    <th><?php echo esc_html($column); ?></th>
                                <?php endforeach; ?>
                                <th>actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)): ?>
                                <?php foreach ($data as $row): ?>
                                    <?php 
                                    // Get the ID value - check for category_id first, then id
                                    $row_id = isset($row->category_id) ? $row->category_id : (isset($row->id) ? $row->id : '');
                                    ?>
                                    <tr data-id="<?php echo esc_attr($row_id); ?>">
                                        <?php foreach ($columns as $column): ?>
                                            <?php if ($column === 'rel_featured_image_id'): ?>
                                                <?php
                                                $img_id   = intval($row->$column ?? 0);
                                                $thumb_url = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : '';
                                                ?>
                                                <td class="image-picker-cell" data-field="rel_featured_image_id">
                                                    <div class="image-picker-wrap">
                                                        <div class="image-preview" style="min-height:30px; margin-bottom:4px;">
                                                            <?php if ($thumb_url): ?>
                                                                <img src="<?php echo esc_url($thumb_url); ?>" style="max-width:80px; max-height:60px; display:block;">
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="image-id-display" style="font-size:11px; color:#666; display:block; margin-bottom:5px;">
                                                            <?php echo $img_id ? 'ID: ' . $img_id : '(none)'; ?>
                                                        </span>
                                                        <button type="button" class="button button-small select-image-btn">Select Image</button>
                                                        <button type="button" class="button button-small clear-image-btn" style="margin-left:4px;<?php echo $img_id ? '' : ' display:none;'; ?>">Clear</button>
                                                    </div>
                                                </td>
                                            <?php else: ?>
                                                <td class="editable" data-field="<?php echo esc_attr($column); ?>">
                                                    <?php echo esc_html($row->$column ?? ''); ?>
                                                </td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <td class="actions">
                                            <button class="delete-row button button-small">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo count($columns) + 1; ?>" class="no-data">No categories found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our specific page
        if ($hook !== 'ruplin-hub-2_page_service_categories_mar') {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'service-categories-mar-admin',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue WP media uploader (required for Select Image button)
        wp_enqueue_media();

        // Enqueue JavaScript
        wp_enqueue_script(
            'service-categories-mar-admin',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.1',
            true
        );

        // Localize script for AJAX
        wp_localize_script('service-categories-mar-admin', 'service_categories_mar_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('service_categories_mar_nonce')
        ));
    }
    
    /**
     * Create table if it doesn't exist
     */
    private function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'service_categories';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            category_name varchar(255) DEFAULT NULL,
            longer_name text DEFAULT NULL,
            category_slug varchar(255) DEFAULT NULL,
            parent_id int(11) DEFAULT 0,
            description text,
            meta_title varchar(255) DEFAULT NULL,
            meta_description text,
            display_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_slug (category_slug),
            KEY idx_parent (parent_id),
            KEY idx_order (display_order)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get table columns
     */
    private function get_table_columns() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'service_categories';
        
        $columns = $wpdb->get_col("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = '$table_name' 
            AND TABLE_SCHEMA = DATABASE()
            ORDER BY ORDINAL_POSITION
        ");
        
        return !empty($columns) ? $columns : array('category_id', 'category_name', 'category_description', 'created_at', 'updated_at');
    }
    
    /**
     * Get service categories data
     */
    private function get_service_categories_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'service_categories';
        
        // Debug: Log the table name we're looking for
        error_log("Service Categories MAR - Looking for table: $table_name");
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        error_log("Service Categories MAR - Table exists check result: " . ($table_exists ? 'YES' : 'NO'));
        
        if (!$table_exists) {
            // Debug: List all tables to see what's actually there
            $all_tables = $wpdb->get_col("SHOW TABLES");
            error_log("Service Categories MAR - Available tables: " . implode(', ', $all_tables));
            return array();
        }
        
        // Fix the ORDER BY to use category_id instead of id
        $query = "SELECT * FROM $table_name ORDER BY category_id ASC";
        $results = $wpdb->get_results($query);
        
        error_log("Service Categories MAR - Query: $query");
        error_log("Service Categories MAR - Results count: " . count($results));
        
        if ($wpdb->last_error) {
            error_log("Service Categories MAR - Query error: " . $wpdb->last_error);
        }
        
        return $results;
    }
    
    /**
     * AJAX handler to get data
     */
    public function ajax_get_data() {
        check_ajax_referer('service_categories_mar_nonce', 'nonce');
        
        $data = $this->get_service_categories_data();
        $columns = $this->get_table_columns();
        
        wp_send_json_success(array(
            'data' => $data,
            'columns' => $columns
        ));
    }
    
    /**
     * AJAX handler to save field
     */
    public function ajax_save_field() {
        check_ajax_referer('service_categories_mar_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'service_categories';
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
        $value = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';
        
        if (!$id || !$field) {
            wp_send_json_error('Invalid parameters');
        }
        
        // Don't allow editing the primary key
        if ($field === 'category_id') {
            wp_send_json_error('Cannot edit primary key');
        }
        
        // Special case: rel_featured_image_id — save integer or SQL NULL
        if ($field === 'rel_featured_image_id') {
            if ($value !== '' && $value !== null && intval($value) > 0) {
                $result = $wpdb->update(
                    $table_name,
                    array('rel_featured_image_id' => intval($value)),
                    array('category_id' => $id),
                    array('%d'),
                    array('%d')
                );
            } else {
                // Write SQL NULL via raw query (wpdb->update cannot emit NULL)
                $result = $wpdb->query(
                    $wpdb->prepare("UPDATE $table_name SET rel_featured_image_id = NULL WHERE category_id = %d", $id)
                );
            }

            if ($result === false) {
                wp_send_json_error('Failed to update');
            }
            wp_send_json_success('Updated successfully');
        }

        // Sanitize based on field type
        if (in_array($field, array('id', 'category_id', 'parent_id', 'display_order', 'is_active'))) {
            $value = intval($value);
        } else {
            $value = sanitize_text_field($value);
        }

        $result = $wpdb->update(
            $table_name,
            array($field => $value),
            array('category_id' => $id)
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to update');
        }
        
        wp_send_json_success('Updated successfully');
    }
    
    /**
     * AJAX handler to create new row
     */
    public function ajax_create_new() {
        check_ajax_referer('service_categories_mar_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'service_categories';
        
        // Check if table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if (!$table_exists) {
            // Try to create the table
            $this->maybe_create_table();
            // Check again
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            if (!$table_exists) {
                wp_send_json_error('Table does not exist and could not be created');
            }
        }
        
        // Get actual column names from the existing table
        $columns = $wpdb->get_col("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = '$table_name' 
            AND TABLE_SCHEMA = DATABASE()
        ");
        
        // Build insert data based on actual columns
        $insert_data = array();
        
        // Add default values for columns that exist
        if (in_array('category_name', $columns)) {
            $insert_data['category_name'] = 'New Category';
        }
        if (in_array('name', $columns)) {
            $insert_data['name'] = 'New Category';
        }
        if (in_array('category_slug', $columns)) {
            $insert_data['category_slug'] = 'new-category-' . time();
        }
        if (in_array('slug', $columns)) {
            $insert_data['slug'] = 'new-category-' . time();
        }
        if (in_array('display_order', $columns)) {
            $insert_data['display_order'] = 0;
        }
        if (in_array('sort_order', $columns)) {
            $insert_data['sort_order'] = 0;
        }
        if (in_array('is_active', $columns)) {
            $insert_data['is_active'] = 1;
        }
        if (in_array('status', $columns)) {
            $insert_data['status'] = 1;
        }
        if (in_array('parent_id', $columns)) {
            $insert_data['parent_id'] = 0;
        }
        
        // If we have no recognized columns, try a minimal insert
        if (empty($insert_data)) {
            // Just insert minimal data, let database use defaults
            $insert_data = array();
        }
        
        $result = $wpdb->insert($table_name, $insert_data);
        
        if ($result === false) {
            $error = $wpdb->last_error;
            error_log('Service Categories MAR - Insert failed: ' . $error);
            error_log('Service Categories MAR - Available columns: ' . implode(', ', $columns));
            wp_send_json_error('Failed to create: ' . $error . ' (Columns: ' . implode(', ', $columns) . ')');
        }
        
        $new_id = $wpdb->insert_id;
        
        // Get the newly inserted row - the primary key is category_id
        $new_row = null;
        if ($new_id) {
            $new_row = $wpdb->get_row("SELECT * FROM $table_name WHERE category_id = $new_id");
        }
        
        // If we still don't have the row, get the last inserted row
        if (!$new_row) {
            $new_row = $wpdb->get_row("SELECT * FROM $table_name ORDER BY category_id DESC LIMIT 1");
        }
        
        // If we still don't have data, return success with empty data and reload
        if (!$new_row) {
            wp_send_json_success(array('reload' => true));
        }
        
        wp_send_json_success($new_row);
    }
    
    /**
     * AJAX handler to delete row
     */
    public function ajax_delete_row() {
        check_ajax_referer('service_categories_mar_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'service_categories';
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error('Invalid ID');
        }
        
        $result = $wpdb->delete(
            $table_name,
            array('category_id' => $id)
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to delete');
        }
        
        wp_send_json_success('Deleted successfully');
    }
    
    /**
     * Aggressive admin notice suppression
     */
    private function suppress_admin_notices() {
        // Remove all admin notices on this page
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        // Add custom CSS to hide any notices that might slip through
        add_action('admin_head', function() {
            ?>
            <style>
                /* Aggressive notice suppression for Service Categories MAR page */
                body.ruplin-hub-2_page_service_categories_mar .notice,
                body.ruplin-hub-2_page_service_categories_mar .notice-error,
                body.ruplin-hub-2_page_service_categories_mar .notice-warning,
                body.ruplin-hub-2_page_service_categories_mar .notice-success,
                body.ruplin-hub-2_page_service_categories_mar .notice-info,
                body.ruplin-hub-2_page_service_categories_mar .error,
                body.ruplin-hub-2_page_service_categories_mar .updated,
                body.ruplin-hub-2_page_service_categories_mar .update-nag,
                body.ruplin-hub-2_page_service_categories_mar .wp-pointer,
                body.ruplin-hub-2_page_service_categories_mar #message,
                body.ruplin-hub-2_page_service_categories_mar .jetpack-jitm-message,
                body.ruplin-hub-2_page_service_categories_mar .woocommerce-message,
                body.ruplin-hub-2_page_service_categories_mar .woocommerce-error,
                body.ruplin-hub-2_page_service_categories_mar div.fs-notice,
                body.ruplin-hub-2_page_service_categories_mar .monsterinsights-notice,
                body.ruplin-hub-2_page_service_categories_mar .yoast-notification {
                    display: none !important;
                }
                
                /* Keep our own notices visible if needed */
                body.ruplin-hub-2_page_service_categories_mar .service-categories-mar-notice {
                    display: block !important;
                }
            </style>
            <?php
        }, 999);
        
        // Additional JavaScript-based suppression
        add_action('admin_footer', function() {
            ?>
            <script>
                jQuery(document).ready(function($) {
                    // Remove any notices that were added after page load
                    $('.notice, .error, .updated, .update-nag').not('.service-categories-mar-notice').remove();
                    
                    // Monitor for dynamically added notices
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            $(mutation.addedNodes).each(function() {
                                if ($(this).hasClass('notice') || 
                                    $(this).hasClass('error') || 
                                    $(this).hasClass('updated') ||
                                    $(this).hasClass('update-nag')) {
                                    if (!$(this).hasClass('service-categories-mar-notice')) {
                                        $(this).remove();
                                    }
                                }
                            });
                        });
                    });
                    
                    // Start observing
                    if (document.body) {
                        observer.observe(document.body, {
                            childList: true,
                            subtree: true
                        });
                    }
                });
            </script>
            <?php
        }, 999);
    }
}

// Initialize the class
Ruplin_Service_Categories_Mar_Admin::get_instance();