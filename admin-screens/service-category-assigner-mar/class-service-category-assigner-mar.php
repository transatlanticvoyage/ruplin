<?php
/**
 * Service Category Assigner MAR Admin Page
 * 
 * Handles service category assignment interface in WordPress admin
 * URL: /wp-admin/admin.php?page=service_category_assigner_mar
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Service_Category_Assigner_Mar {
    
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
        // Add admin menu with priority to ensure parent exists
        add_action('admin_menu', array($this, 'add_admin_menu'), 25);
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Aggressive notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
        
        // AJAX handlers
        add_action('wp_ajax_service_category_assigner_get_data', array($this, 'ajax_get_data'));
        add_action('wp_ajax_service_category_assigner_save_changes', array($this, 'ajax_save_changes'));
    }
    
    /**
     * Add menu item to WordPress admin
     */
    public function add_admin_menu() {
        // Add as submenu under Ruplin Hub 1
        add_submenu_page(
            'snefuru',  // Parent slug (Ruplin Hub 1)
            'Service Category Assigner',  // Page title
            'Service Category Assigner',  // Menu title
            'manage_options',  // Capability
            'service_category_assigner_mar',  // Menu slug
            array($this, 'render_admin_page')  // Callback
        );
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'service_category_assigner_mar') {
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
        
        if (strpos($screen->id, 'service_category_assigner_mar') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'service_category_assigner_mar')) {
            
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
     * Render the admin page
     */
    public function render_admin_page() {
        // Aggressive notice/warning suppression
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap service-category-assigner-mar">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="service-category-assigner-mar-container">
                <div class="table-description">
                    wp_posts items where wp_pylons.pylon_archetype = "servicepage"
                </div>
                
                <div class="table-actions">
                    <button id="save-changes" class="button button-primary" disabled>Save Changes</button>
                    <span id="save-status" style="margin-left: 10px;"></span>
                </div>
                
                <div class="table-wrapper">
                    <table id="service-category-table" class="service-category-table">
                        <thead>
                            <tr>
                                <th>tools</th>
                                <th>post_id</th>
                                <th>post_title</th>
                                <th>post_name</th>
                                <th>post_status</th>
                                <th>pylon_id</th>
                                <th>rel_wp_post_id</th>
                                <th>pylon_archetype</th>
                                <th>moniker</th>
                                <th>service_category</th>
                                <th>rel_service_category_id</th>
                                <th>category_id</th>
                                <th>category_name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $this->render_table_rows(); ?>
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
        if ($hook !== 'ruplin-hub-1_page_service_category_assigner_mar') {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'service-category-assigner-mar',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'service-category-assigner-mar',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('service-category-assigner-mar', 'service_category_assigner_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('service_category_assigner_nonce')
        ));
    }
    
    /**
     * Aggressive admin notice suppression
     */
    private function suppress_all_admin_notices() {
        // Remove all admin notices on this page
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        // Add custom CSS to hide any notices that might slip through
        add_action('admin_head', function() {
            ?>
            <style>
                /* Ultra-aggressive notice suppression for Service Category Assigner MAR page */
                body.ruplin-hub-1_page_service_category_assigner_mar .notice,
                body.ruplin-hub-1_page_service_category_assigner_mar .notice-error,
                body.ruplin-hub-1_page_service_category_assigner_mar .notice-warning,
                body.ruplin-hub-1_page_service_category_assigner_mar .notice-success,
                body.ruplin-hub-1_page_service_category_assigner_mar .notice-info,
                body.ruplin-hub-1_page_service_category_assigner_mar .error,
                body.ruplin-hub-1_page_service_category_assigner_mar .updated,
                body.ruplin-hub-1_page_service_category_assigner_mar .update-nag,
                body.ruplin-hub-1_page_service_category_assigner_mar .wp-pointer,
                body.ruplin-hub-1_page_service_category_assigner_mar #message,
                body.ruplin-hub-1_page_service_category_assigner_mar .jetpack-jitm-message,
                body.ruplin-hub-1_page_service_category_assigner_mar .woocommerce-message,
                body.ruplin-hub-1_page_service_category_assigner_mar .woocommerce-error,
                body.ruplin-hub-1_page_service_category_assigner_mar div.fs-notice,
                body.ruplin-hub-1_page_service_category_assigner_mar .monsterinsights-notice,
                body.ruplin-hub-1_page_service_category_assigner_mar .yoast-notification,
                body.ruplin-hub-1_page_service_category_assigner_mar .notice-alt,
                body.ruplin-hub-1_page_service_category_assigner_mar .update-php,
                body.ruplin-hub-1_page_service_category_assigner_mar .php-update-nag,
                body.ruplin-hub-1_page_service_category_assigner_mar .plugin-update-tr,
                body.ruplin-hub-1_page_service_category_assigner_mar .theme-update-message,
                body.ruplin-hub-1_page_service_category_assigner_mar .update-message,
                body.ruplin-hub-1_page_service_category_assigner_mar .updating-message,
                body.ruplin-hub-1_page_service_category_assigner_mar #update-nag,
                body.ruplin-hub-1_page_service_category_assigner_mar #deprecation-warning,
                body.ruplin-hub-1_page_service_category_assigner_mar .activated,
                body.ruplin-hub-1_page_service_category_assigner_mar .deactivated,
                body.ruplin-hub-1_page_service_category_assigner_mar [class*="notice"],
                body.ruplin-hub-1_page_service_category_assigner_mar [class*="updated"],
                body.ruplin-hub-1_page_service_category_assigner_mar [class*="error"],
                body.ruplin-hub-1_page_service_category_assigner_mar [id*="notice"],
                body.ruplin-hub-1_page_service_category_assigner_mar [id*="message"] {
                    display: none !important;
                }
                
                /* Keep our own notices visible if needed */
                body.ruplin-hub-1_page_service_category_assigner_mar .service-category-assigner-notice {
                    display: block !important;
                }
                
                /* Ensure our content is visible */
                body.ruplin-hub-1_page_service_category_assigner_mar .wrap h1,
                body.ruplin-hub-1_page_service_category_assigner_mar .service-category-assigner-mar-content,
                body.ruplin-hub-1_page_service_category_assigner_mar .service-category-assigner-mar-container {
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
                    $('.notice, .error, .updated, .update-nag').not('.service-category-assigner-notice').remove();
                    
                    // Monitor for dynamically added notices
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            $(mutation.addedNodes).each(function() {
                                if ($(this).hasClass('notice') || 
                                    $(this).hasClass('error') || 
                                    $(this).hasClass('updated') ||
                                    $(this).hasClass('update-nag')) {
                                    if (!$(this).hasClass('service-category-assigner-notice')) {
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
                    
                    // Also observe the wpbody-content area specifically
                    var wpbody = document.getElementById('wpbody-content');
                    if (wpbody) {
                        observer.observe(wpbody, {
                            childList: true,
                            subtree: true
                        });
                    }
                });
            </script>
            <?php
        }, 999);
        
        // PHP-based notice blocking
        add_action('admin_print_styles', function() {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            
            global $wp_filter;
            if (isset($wp_filter['user_admin_notices'])) {
                unset($wp_filter['user_admin_notices']);
            }
        }, 0);
    }
    
    /**
     * Get service page data from database
     */
    private function get_service_page_data() {
        global $wpdb;
        
        // First, let's check if the pylons table exists and what data it contains
        $pylons_table = $wpdb->prefix . 'pylons';
        
        // Debug: Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$pylons_table'");
        if (!$table_exists) {
            error_log("Debug: Table $pylons_table does not exist!");
            return array();
        }
        
        // Debug: Check what pylon_archetype values exist
        $archetypes = $wpdb->get_col("SELECT DISTINCT pylon_archetype FROM $pylons_table");
        error_log("Debug: Available pylon_archetypes: " . print_r($archetypes, true));
        
        // Debug: Count records with servicepage
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $pylons_table WHERE pylon_archetype = 'servicepage'");
        error_log("Debug: Count of servicepage records: " . $count);
        
        // If no servicepage, let's check for similar values
        if ($count == 0) {
            $like_count = $wpdb->get_var("SELECT COUNT(*) FROM $pylons_table WHERE pylon_archetype LIKE '%service%'");
            error_log("Debug: Count of records with 'service' in archetype: " . $like_count);
            
            // Get sample of actual values
            $samples = $wpdb->get_results("SELECT pylon_id, rel_wp_post_id, pylon_archetype FROM $pylons_table WHERE pylon_archetype LIKE '%service%' LIMIT 5", ARRAY_A);
            error_log("Debug: Sample service-related records: " . print_r($samples, true));
        }
        
        $query = "
            SELECT
                p.ID as post_id,
                p.post_title,
                p.post_name,
                p.post_status,
                pyl.pylon_id as pylon_id,
                pyl.rel_wp_post_id,
                pyl.pylon_archetype,
                pyl.moniker,
                pyl.service_category,
                pyl.rel_service_category_id,
                sc.category_id,
                sc.category_name
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}pylons pyl ON p.ID = pyl.rel_wp_post_id
            LEFT JOIN {$wpdb->prefix}service_categories sc ON pyl.rel_service_category_id = sc.category_id
            WHERE pyl.pylon_archetype = 'servicepage'
            ORDER BY p.post_title ASC
        ";
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // Debug: Log query and results
        error_log("Debug: Query executed: " . $query);
        error_log("Debug: Number of results: " . count($results));
        
        return $results;
    }
    
    /**
     * Render table rows
     */
    private function render_table_rows() {
        $data = $this->get_service_page_data();
        $output = '';
        
        if (empty($data)) {
            $output = '<tr><td colspan="13" class="no-data">No posts found with pylon_archetype = "servicepage"</td></tr>';
        } else {
            foreach ($data as $row) {
                $output .= '<tr data-pylon-id="' . esc_attr($row['pylon_id']) . '">';
                $pid = esc_attr($row['post_id']);
                $output .= '<td class="sca-tools-cell">';
                $output .= '<a href="' . admin_url('post.php?post=' . $pid . '&action=edit') . '" class="sca-tool-btn" target="_blank">pendulum</a>';
                $output .= '<a href="' . admin_url('admin.php?page=cashew_editor&post_id=' . $pid) . '" class="sca-tool-btn" target="_blank">cashew</a>';
                $output .= '<a href="' . admin_url('admin.php?page=telescope_content_editor&post=' . $pid) . '" class="sca-tool-btn" target="_blank">telescope</a>';
                $output .= '<a href="' . esc_url(get_permalink($row['post_id'])) . '" class="sca-tool-btn" target="_blank">front end</a>';
                $output .= '</td>';
                $output .= '<td>' . esc_html($row['post_id']) . '</td>';
                $output .= '<td>' . esc_html($row['post_title']) . '</td>';
                $output .= '<td>' . esc_html($row['post_name']) . '</td>';
                $output .= '<td>' . esc_html($row['post_status']) . '</td>';
                $output .= '<td>' . esc_html($row['pylon_id']) . '</td>';
                $output .= '<td>' . esc_html($row['rel_wp_post_id']) . '</td>';
                $output .= '<td>' . esc_html($row['pylon_archetype']) . '</td>';
                $output .= '<td><input type="text" class="editable-field" data-field="moniker" data-original="' . esc_attr($row['moniker'] ?? '') . '" value="' . esc_attr($row['moniker'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="editable-field" data-field="service_category" data-original="' . esc_attr($row['service_category'] ?? '') . '" value="' . esc_attr($row['service_category'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="editable-field" data-field="rel_service_category_id" data-original="' . esc_attr($row['rel_service_category_id'] ?? '') . '" value="' . esc_attr($row['rel_service_category_id'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td>' . esc_html($row['category_id'] ?? '') . '</td>';
                $output .= '<td>' . esc_html($row['category_name'] ?? '') . '</td>';
                $output .= '</tr>';
            }
        }
        
        return $output;
    }
    
    /**
     * AJAX handler to get data
     */
    public function ajax_get_data() {
        check_ajax_referer('service_category_assigner_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $data = $this->get_service_page_data();
        
        wp_send_json_success(array(
            'data' => $data,
            'count' => count($data)
        ));
    }
    
    /**
     * AJAX handler to save changes
     */
    public function ajax_save_changes() {
        check_ajax_referer('service_category_assigner_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $pylons_table = $wpdb->prefix . 'pylons';
        
        $changes = isset($_POST['changes']) ? $_POST['changes'] : array();

        error_log('[SCA Debug] POST keys: ' . implode(', ', array_keys($_POST)));
        error_log('[SCA Debug] changes count: ' . count($changes));
        error_log('[SCA Debug] changes raw: ' . print_r($changes, true));

        if (empty($changes)) {
            wp_send_json_error('No changes to save — $_POST keys: ' . implode(', ', array_keys($_POST)));
            return;
        }
        
        $success_count = 0;
        $error_count = 0;
        $errors = array();
        
        foreach ($changes as $change) {
            $pylon_id = intval($change['pylon_id']);
            $field = sanitize_text_field($change['field']);
            $value = $change['value'];
            
            // Validate field name
            if (!in_array($field, array('moniker', 'service_category', 'rel_service_category_id'))) {
                $error_count++;
                $errors[] = "Invalid field: $field";
                continue;
            }
            
            // Special handling for rel_service_category_id - should be numeric or null
            if ($field === 'rel_service_category_id') {
                $value = !empty($value) ? intval($value) : null;
            } else {
                $value = sanitize_text_field($value);
            }
            
            $result = $wpdb->update(
                $pylons_table,
                array($field => $value),
                array('pylon_id' => $pylon_id),
                ($field === 'rel_service_category_id' && $value !== null) ? array('%d') : array('%s'),
                array('%d')
            );
            
            if ($result !== false) {
                $success_count++;
            } else {
                $error_count++;
                $db_error = $wpdb->last_error;
                $errors[] = "Failed to update pylon_id: $pylon_id, field: $field, db_error: $db_error";
                error_log("[SCA Debug] DB update failed — pylon_id: $pylon_id, field: $field, value: " . print_r($value, true) . ", error: $db_error");
            }
        }
        
        if ($error_count > 0) {
            wp_send_json_error(array(
                'message' => "Saved $success_count changes with $error_count errors",
                'errors' => $errors
            ));
        } else {
            wp_send_json_success(array(
                'message' => "Successfully saved $success_count changes"
            ));
        }
    }
}

// Initialize the class
Ruplin_Service_Category_Assigner_Mar::get_instance();