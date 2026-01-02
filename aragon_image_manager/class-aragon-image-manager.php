<?php
/**
 * Aragon Image Manager
 * 
 * WordPress Media Library image management functionality
 * 
 * @package Ruplin
 * @subpackage AragonImageManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Aragon_Image_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Early notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
        
        // AJAX handlers
        add_action('wp_ajax_aragon_load_media_data', array($this, 'load_media_data'));
        add_action('wp_ajax_aragon_load_posts_pylons_data', array($this, 'load_posts_pylons_data'));
        add_action('wp_ajax_aragon_get_image_url', array($this, 'get_image_url'));
        add_action('wp_ajax_aragon_update_paragon_featured_image', array($this, 'update_paragon_featured_image'));
    }
    
    /**
     * Add submenu page to Ruplin Hub
     */
    public function add_admin_menu() {
        add_submenu_page(
            'snefuru',
            'Aragon Image Manager',
            'Aragon_Image_Mar',
            'manage_options',
            'aragon_image_mar',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'ruplin-hub_page_aragon_image_mar') {
            return;
        }
        
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        
        // Localize script for AJAX
        wp_localize_script('jquery', 'aragon_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aragon_nonce')
        ));
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        // Check if we're on our page
        if (isset($_GET['page']) && $_GET['page'] === 'aragon_image_mar') {
            // Immediate notice suppression
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Override the global filter
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
        
        // Check if we're on the Aragon Image Manager page
        if (strpos($screen->id, 'aragon_image_mar') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'aragon_image_mar')) {
            
            // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices'); 
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Override with empty function
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
        // Remove all admin notices immediately
        add_action('admin_print_styles', function() {
            // Remove all notice actions
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            
            // Remove user admin notices
            global $wp_filter;
            if (isset($wp_filter['user_admin_notices'])) {
                unset($wp_filter['user_admin_notices']);
            }
        }, 0);
        
        // Additional cleanup for persistent notices
        add_action('admin_head', function() {
            // Hide any notices that slip through via CSS
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
                
                /* Hide WordPress core update notices */
                .update-core-php, .notice-alt {
                    display: none !important;
                }
                
                /* Hide plugin activation/deactivation notices */
                .activated, .deactivated {
                    display: none !important;
                }
                
                /* Hide file permission and other system warnings */
                .notice-warning, .notice-error {
                    display: none !important;
                }
                
                /* Hide any remaining notices in common locations */
                .wrap .notice:first-child,
                .wrap > div.notice,
                .wrap > div.updated,
                .wrap > div.error {
                    display: none !important;
                }
                
                /* Nuclear option - hide anything that looks like a notice */
                [class*="notice"], [class*="updated"], [class*="error"],
                [id*="notice"], [id*="message"] {
                    display: none !important;
                }
                
                /* Restore our legitimate content */
                .wrap h1, .wrap .aragon-content {
                    display: block !important;
                }
            </style>';
        }, 1);
        
        // Remove all hooks related to admin notices
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        // Add empty functions to prevent any notices
        add_action('admin_notices', '__return_false', PHP_INT_MAX);
        add_action('all_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('network_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('user_admin_notices', '__return_false', PHP_INT_MAX);
    }
    
    /**
     * Load media data via AJAX
     */
    public function load_media_data() {
        check_ajax_referer('aragon_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        try {
            global $wpdb;
            
            $page = intval($_POST['page'] ?? 1);
            $per_page = intval($_POST['per_page'] ?? 20);
            $search = sanitize_text_field($_POST['search'] ?? '');
            $offset = ($page - 1) * $per_page;
            
            // Build query for attachments (media files)
            $where_clause = "WHERE post_type = 'attachment'";
            if (!empty($search)) {
                $search_term = '%' . $wpdb->esc_like($search) . '%';
                $where_clause .= $wpdb->prepare(" AND (post_title LIKE %s OR post_name LIKE %s)", $search_term, $search_term);
            }
            
            // Get total count
            $total_query = "SELECT COUNT(*) FROM {$wpdb->posts} {$where_clause}";
            $total_items = $wpdb->get_var($total_query);
            
            // Get attachments with pagination
            $query = "SELECT * FROM {$wpdb->posts} 
                      {$where_clause} 
                      ORDER BY post_date DESC 
                      LIMIT {$per_page} OFFSET {$offset}";
            
            $attachments = $wpdb->get_results($query);
            $media_data = array();
            
            foreach ($attachments as $attachment) {
                $attachment_id = $attachment->ID;
                
                // Get metadata
                $metadata = wp_get_attachment_metadata($attachment_id);
                $file_path = get_attached_file($attachment_id);
                $file_size = $file_path ? filesize($file_path) : 0;
                $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                
                // Get thumbnail URL
                $thumb_url = wp_get_attachment_image_src($attachment_id, 'thumbnail');
                $thumb_url = $thumb_url ? $thumb_url[0] : '';
                
                // Get file URL
                $file_url = wp_get_attachment_url($attachment_id);
                
                // Parse file info
                $file_info = pathinfo($file_path);
                $filename = $file_info['basename'] ?? '';
                $extension = $file_info['extension'] ?? '';
                
                // Get dimensions for images
                $width = $metadata['width'] ?? '';
                $height = $metadata['height'] ?? '';
                $dimensions = ($width && $height) ? "{$width} × {$height}" : '';
                
                $media_data[] = array(
                    'id' => $attachment_id,
                    'title' => $attachment->post_title,
                    'filename' => $filename,
                    'file_url' => $file_url,
                    'thumb_url' => $thumb_url,
                    'mime_type' => $attachment->post_mime_type,
                    'file_size' => size_format($file_size),
                    'dimensions' => $dimensions,
                    'alt_text' => $alt_text,
                    'uploaded_date' => $attachment->post_date,
                    'author_id' => $attachment->post_author,
                    'description' => $attachment->post_content,
                    'caption' => $attachment->post_excerpt,
                    'attached_to' => $attachment->post_parent
                );
            }
            
            wp_send_json_success(array(
                'data' => $media_data,
                'total' => $total_items,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total_items / $per_page)
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Error loading media data: ' . $e->getMessage());
        }
    }
    
    /**
     * Load posts and pylons data via AJAX
     */
    public function load_posts_pylons_data() {
        check_ajax_referer('aragon_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        try {
            global $wpdb;
            
            $page = intval($_POST['page'] ?? 1);
            $per_page = intval($_POST['per_page'] ?? 20);
            $search = sanitize_text_field($_POST['search'] ?? '');
            $archetype_filter = sanitize_text_field($_POST['archetype_filter'] ?? 'all');
            $offset = ($page - 1) * $per_page;
            
            // Build query for posts/pages with LEFT JOIN to pylons table
            $pylons_table = $wpdb->prefix . 'pylons';
            $posts_table = $wpdb->posts;
            
            $where_clause = "WHERE p.post_status != 'trash' AND p.post_type IN ('page', 'post')";
            if (!empty($search)) {
                $search_term = '%' . $wpdb->esc_like($search) . '%';
                $where_clause .= $wpdb->prepare(" AND p.post_title LIKE %s", $search_term);
            }
            if ($archetype_filter !== 'all') {
                $where_clause .= $wpdb->prepare(" AND pyl.pylon_archetype = %s", $archetype_filter);
            }
            
            // Check if pylons table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$pylons_table}'");
            
            if ($table_exists) {
                // Get total count with JOIN
                $total_query = "SELECT COUNT(*) 
                               FROM {$posts_table} p
                               LEFT JOIN {$pylons_table} pyl ON p.ID = pyl.rel_wp_post_id
                               {$where_clause}";
                
                // Get data with pagination and JOIN
                $query = "SELECT p.ID as id, 
                                p.post_title, 
                                p.post_status, 
                                p.post_type, 
                                p.post_date,
                                pyl.pylon_id,
                                pyl.paragon_featured_image_id,
                                pyl.pylon_archetype
                         FROM {$posts_table} p
                         LEFT JOIN {$pylons_table} pyl ON p.ID = pyl.rel_wp_post_id
                         {$where_clause}
                         ORDER BY p.post_date DESC 
                         LIMIT {$per_page} OFFSET {$offset}";
            } else {
                // Fallback if pylons table doesn't exist
                $total_query = "SELECT COUNT(*) FROM {$posts_table} p {$where_clause}";
                
                $query = "SELECT p.ID as id, 
                                p.post_title, 
                                p.post_status, 
                                p.post_type, 
                                p.post_date,
                                NULL as pylon_id,
                                NULL as paragon_featured_image_id,
                                NULL as pylon_archetype
                         FROM {$posts_table} p
                         {$where_clause}
                         ORDER BY p.post_date DESC 
                         LIMIT {$per_page} OFFSET {$offset}";
            }
            
            $total_items = $wpdb->get_var($total_query);
            $results = $wpdb->get_results($query);
            
            $posts_data = array();
            foreach ($results as $row) {
                $posts_data[] = array(
                    'id' => $row->id,
                    'post_title' => $row->post_title,
                    'post_status' => $row->post_status,
                    'post_type' => $row->post_type,
                    'post_date' => $row->post_date,
                    'pylon_id' => $row->pylon_id,
                    'paragon_featured_image_id' => $row->paragon_featured_image_id,
                    'pylon_archetype' => $row->pylon_archetype
                );
            }
            
            wp_send_json_success(array(
                'data' => $posts_data,
                'total' => $total_items,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total_items / $per_page)
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Error loading posts/pylons data: ' . $e->getMessage());
        }
    }
    
    /**
     * Get image URL for featured image display
     */
    public function get_image_url() {
        check_ajax_referer('aragon_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $image_id = intval($_POST['image_id'] ?? 0);
        
        if (!$image_id) {
            wp_send_json_error('Invalid image ID');
        }
        
        try {
            $image_url = wp_get_attachment_image_src($image_id, 'thumbnail');
            $image_title = get_the_title($image_id);
            
            if ($image_url && !empty($image_url[0])) {
                wp_send_json_success(array(
                    'url' => $image_url[0],
                    'title' => $image_title,
                    'width' => $image_url[1],
                    'height' => $image_url[2]
                ));
            } else {
                wp_send_json_error('Image not found');
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Error loading image: ' . $e->getMessage());
        }
    }
    
    /**
     * Update paragon_featured_image_id in wp_pylons table
     */
    public function update_paragon_featured_image() {
        check_ajax_referer('aragon_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $media_id = intval($_POST['media_id'] ?? 0);
        
        if (!$post_id || !$media_id) {
            wp_send_json_error('Invalid post ID or media ID');
        }
        
        try {
            global $wpdb;
            
            $pylons_table = $wpdb->prefix . 'pylons';
            
            // Check if pylons table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$pylons_table}'");
            
            if (!$table_exists) {
                wp_send_json_error('Pylons table does not exist');
            }
            
            // Check if record exists for this post
            $existing_record = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$pylons_table} WHERE rel_wp_post_id = %d",
                $post_id
            ));
            
            if ($existing_record) {
                // Update existing record
                $result = $wpdb->update(
                    $pylons_table,
                    array('paragon_featured_image_id' => $media_id),
                    array('rel_wp_post_id' => $post_id),
                    array('%d'),
                    array('%d')
                );
                
                if ($result === false) {
                    wp_send_json_error('Database update failed: ' . $wpdb->last_error);
                }
                
                wp_send_json_success('Featured image updated successfully');
                
            } else {
                // Create new record
                $result = $wpdb->insert(
                    $pylons_table,
                    array(
                        'rel_wp_post_id' => $post_id,
                        'paragon_featured_image_id' => $media_id
                    ),
                    array('%d', '%d')
                );
                
                if ($result === false) {
                    wp_send_json_error('Database insert failed: ' . $wpdb->last_error);
                }
                
                wp_send_json_success('Featured image assigned successfully');
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Error updating featured image: ' . $e->getMessage());
        }
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap aragon-content">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                <h1 style="margin: 0;">Aragon Image Manager</h1>
                <button id="open-navarre-designator" class="button" style="background: #8B5CF6; color: white; border: none; padding: 8px 16px; border-radius: 4px; display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                    <svg width="16" height="16" viewBox="0 0 24 24" style="fill: white; stroke: #F97316; stroke-width: 2;">
                        <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                    </svg>
                    Open Navarre Designator
                </button>
            </div>
            
            <!-- Controls Bar -->
            <div class="aragon-controls" style="background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <label style="font-weight: bold;">Items per page:</label>
                    <select id="aragon-per-page" style="padding: 5px;">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    
                    <div style="position: relative;">
                        <input type="text" id="aragon-search" placeholder="Search media..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 250px;">
                        <button id="aragon-search-btn" class="button" style="margin-left: 5px;">Search</button>
                        <button id="aragon-clear-search" class="button" style="margin-left: 5px;">Clear</button>
                    </div>
                    
                    <div style="margin-left: auto;">
                        <span id="aragon-showing-info" style="color: #666;">Loading...</span>
                    </div>
                </div>
            </div>
            
            <!-- Media Table -->
            <div class="aragon-table-container" style="background: white; border: 1px solid #ddd; border-radius: 5px;">
                <table class="wp-list-table widefat fixed striped media" id="aragon-media-table">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 60px;">
                                <input type="checkbox" id="aragon-select-all">
                            </th>
                            <th scope="col" style="width: 80px;">File</th>
                            <th scope="col">Title</th>
                            <th scope="col">Author</th>
                            <th scope="col">Attached to</th>
                            <th scope="col">Date</th>
                            <th scope="col">File name</th>
                            <th scope="col">File type</th>
                            <th scope="col">File size</th>
                            <th scope="col">Dimensions</th>
                            <th scope="col" style="width: 120px;">Alt Text</th>
                            <th scope="col" style="width: 150px;">Description</th>
                            <th scope="col" style="width: 150px;">Caption</th>
                        </tr>
                    </thead>
                    <tbody id="aragon-media-tbody">
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 20px;">
                                <span id="aragon-loading">Loading media files...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="aragon-pagination" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div class="pagination-info">
                    <span id="aragon-pagination-info"></span>
                </div>
                <div class="pagination-controls">
                    <button id="aragon-first-page" class="button" disabled>« First</button>
                    <button id="aragon-prev-page" class="button" disabled>‹ Previous</button>
                    <span id="aragon-page-info" style="margin: 0 15px;"></span>
                    <button id="aragon-next-page" class="button">Next ›</button>
                    <button id="aragon-last-page" class="button">Last »</button>
                </div>
            </div>
            
            <!-- Navarre Designator Popup -->
            <div id="navarre-designator-popup" class="navarre-popup" style="display: none;">
                <div class="navarre-popup-overlay" id="navarre-popup-overlay"></div>
                <div class="navarre-popup-content">
                    <div class="navarre-popup-header">
                        <div style="display: flex; align-items: center; gap: 20px; flex: 1;">
                            <h2 style="margin: 0; color: #333; font-size: 24px;">Navarre Designator</h2>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: bold; font-size: 14px;">pylon_archetype:</span>
                                <div class="navarre-filter-buttons" style="display: flex; gap: 2px;">
                                    <button class="navarre-filter-btn active" data-archetype="all" style="padding: 4px 8px; border: 1px solid #ddd; cursor: pointer; font-size: 12px; border-radius: 3px; background: #f0f0f0; color: #333;">all</button>
                                    <button class="navarre-filter-btn" data-archetype="homepage" style="padding: 4px 8px; border: 1px solid #ddd; cursor: pointer; font-size: 12px; border-radius: 3px; background: white; color: #333;">homepage</button>
                                    <button class="navarre-filter-btn" data-archetype="servicepage" style="padding: 4px 8px; border: 1px solid #ddd; cursor: pointer; font-size: 12px; border-radius: 3px; background: white; color: #333;">servicepage</button>
                                    <button class="navarre-filter-btn" data-archetype="locationpage" style="padding: 4px 8px; border: 1px solid #ddd; cursor: pointer; font-size: 12px; border-radius: 3px; background: white; color: #333;">locationpage</button>
                                    <button class="navarre-filter-btn" data-archetype="blogpost" style="padding: 4px 8px; border: 1px solid #ddd; cursor: pointer; font-size: 12px; border-radius: 3px; background: white; color: #333;">blogpost</button>
                                    <button class="navarre-filter-btn" data-archetype="aboutpage" style="padding: 4px 8px; border: 1px solid #ddd; cursor: pointer; font-size: 12px; border-radius: 3px; background: white; color: #333;">aboutpage</button>
                                    <button class="navarre-filter-btn" data-archetype="contactpage" style="padding: 4px 8px; border: 1px solid #ddd; cursor: pointer; font-size: 12px; border-radius: 3px; background: white; color: #333;">contactpage</button>
                                </div>
                            </div>
                        </div>
                        <button id="close-navarre-designator" class="navarre-close-btn" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; line-height: 1;">×</button>
                    </div>
                    <div class="navarre-popup-body">
                        <!-- Controls for Navarre table -->
                        <div class="navarre-controls" style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <label style="font-weight: bold;">Items per page:</label>
                                <select id="navarre-per-page" style="padding: 5px;">
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                
                                <div style="position: relative;">
                                    <input type="text" id="navarre-search" placeholder="Search posts..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 250px;">
                                    <button id="navarre-search-btn" class="button" style="margin-left: 5px;">Search</button>
                                    <button id="navarre-clear-search" class="button" style="margin-left: 5px;">Clear</button>
                                </div>
                                
                                <div style="margin-left: auto;">
                                    <span id="navarre-showing-info" style="color: #666;">Loading...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Posts/Pylons Table -->
                        <div class="navarre-table-container" style="background: white; border: 1px solid #ddd; border-radius: 5px;">
                            <table class="wp-list-table widefat fixed striped" id="navarre-posts-table">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 100px;"><strong>gascon_drop_cell</strong></th>
                                        <th scope="col" style="width: 60px;"><strong>post-id</strong></th>
                                        <th scope="col"><strong>post_title</strong></th>
                                        <th scope="col" style="width: 100px;"><strong>post_status</strong></th>
                                        <th scope="col" style="width: 100px;"><strong>post_type</strong></th>
                                        <th scope="col" style="width: 120px;"><strong>post_date</strong></th>
                                        <th scope="col" style="width: 80px;"><strong>pylon_id</strong></th>
                                        <th scope="col" style="width: 140px;"><strong>paragon_featured_image_id</strong></th>
                                        <th scope="col" style="width: 120px;"><strong>pylon_archetype</strong></th>
                                    </tr>
                                </thead>
                                <tbody id="navarre-posts-tbody">
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 20px;">
                                            <span id="navarre-loading">Loading posts data...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="navarre-pagination" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                            <div class="pagination-info">
                                <span id="navarre-pagination-info"></span>
                            </div>
                            <div class="pagination-controls">
                                <button id="navarre-first-page" class="button" disabled>« First</button>
                                <button id="navarre-prev-page" class="button" disabled>‹ Previous</button>
                                <span id="navarre-page-info" style="margin: 0 15px;"></span>
                                <button id="navarre-next-page" class="button">Next ›</button>
                                <button id="navarre-last-page" class="button">Last »</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .aragon-content .wp-list-table {
            border-collapse: collapse;
        }
        
        .aragon-content .wp-list-table th,
        .aragon-content .wp-list-table td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            vertical-align: middle;
        }
        
        .aragon-content .wp-list-table th {
            background: #f9f9f9;
            font-weight: bold;
        }
        
        .aragon-content .media-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .aragon-content .media-placeholder {
            width: 60px;
            height: 60px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #666;
        }
        
        .aragon-content .media-title {
            font-weight: bold;
            color: #0073aa;
            cursor: pointer;
        }
        
        .aragon-content .media-title:hover {
            color: #005a87;
        }
        
        .aragon-content .media-filename {
            font-family: monospace;
            font-size: 12px;
            color: #666;
        }
        
        .aragon-content .pagination-controls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .aragon-content .aragon-alt-text,
        .aragon-content .aragon-description,
        .aragon-content .aragon-caption {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
            display: block;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .aragon-content .aragon-alt-text:empty::after {
            content: "—";
            color: #ccc;
        }
        
        .aragon-content .aragon-description:empty::after {
            content: "—";
            color: #ccc;
        }
        
        .aragon-content .aragon-caption:empty::after {
            content: "—";
            color: #ccc;
        }
        
        .aragon-content .wp-list-table td {
            max-width: 200px;
            word-wrap: break-word;
        }
        
        /* Navarre Designator Popup Styles */
        .navarre-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 100000;
            pointer-events: none;
        }
        
        .navarre-popup-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 35%;
            height: 100%;
            background: transparent;
            pointer-events: none;
        }
        
        .navarre-popup-content {
            position: absolute;
            top: 0;
            right: 0;
            width: 65%;
            height: 100%;
            background: white;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            pointer-events: auto;
        }
        
        .navarre-popup-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9f9f9;
        }
        
        .navarre-popup-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .navarre-close-btn:hover {
            background: #f0f0f0 !important;
            border-radius: 50%;
            width: 30px;
            height: 30px;
        }
        
        #open-navarre-designator:hover {
            background: #7C3AED !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Post status styling in Navarre table */
        .post-status-publish {
            color: #00a32a;
            font-weight: bold;
        }
        
        .post-status-draft {
            color: #b32d2e;
            font-style: italic;
        }
        
        .post-status-private {
            color: #f56e28;
        }
        
        .post-status-future {
            color: #0073aa;
        }
        
        .post-status-pending {
            color: #996633;
        }
        
        /* Navarre filter buttons styling */
        .navarre-filter-btn {
            transition: all 0.2s ease;
        }
        
        .navarre-filter-btn:hover {
            opacity: 0.8;
        }
        
        .navarre-filter-btn.active[data-archetype="all"] {
            background: #f0f0f0 !important;
            color: #333 !important;
            font-weight: bold;
        }
        
        .navarre-filter-btn.active:not([data-archetype="all"]) {
            background: #1e3a8a !important;
            color: white !important;
            font-weight: bold;
        }
        
        .navarre-filter-btn:not(.active) {
            background: white !important;
            color: #333 !important;
        }
        
        /* Gascon drop cell polka dot pattern */
        .gascon-drop-cell {
            background-color: #7DD3C0;
            background-image: 
                radial-gradient(circle at 0px 0px, #5FBAAA 2px, transparent 2px),
                radial-gradient(circle at 5px 5px, #5FBAAA 2px, transparent 2px);
            background-size: 10px 10px;
            background-position: 0 0, 5px 5px;
            transition: all 0.2s ease;
        }
        
        /* Drag and drop styles */
        .aragon-content .media-thumbnail {
            cursor: grab;
            transition: all 0.2s ease;
        }
        
        .aragon-content .media-thumbnail:active {
            cursor: grabbing;
        }
        
        .aragon-content .media-thumbnail.dragging {
            opacity: 0.5;
            transform: scale(0.9);
            cursor: grabbing;
            z-index: 100001;
            position: relative;
        }
        
        /* Ensure drag works across popup */
        body.dragging-active {
            pointer-events: none;
        }
        
        body.dragging-active .media-thumbnail.dragging,
        body.dragging-active .gascon-drop-cell {
            pointer-events: auto;
        }
        
        .gascon-drop-cell.drag-over {
            background-color: #F59E0B !important;
            background-image: 
                radial-gradient(circle at 0px 0px, #D97706 2px, transparent 2px),
                radial-gradient(circle at 5px 5px, #D97706 2px, transparent 2px) !important;
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
        }
        
        .gascon-drop-cell.drop-success {
            background-color: #10B981 !important;
            background-image: 
                radial-gradient(circle at 0px 0px, #059669 2px, transparent 2px),
                radial-gradient(circle at 5px 5px, #059669 2px, transparent 2px) !important;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let perPage = 20;
            let totalPages = 1;
            let totalItems = 0;
            let searchTerm = '';
            
            // Load initial data
            loadMediaData();
            
            // Debug: Check if images are draggable
            $(document).on('mouseenter', '.media-thumbnail', function() {
                console.log('Media thumbnail detected:', $(this).attr('class'), 'Draggable:', $(this).attr('draggable'));
                if (!$(this).attr('draggable')) {
                    $(this).attr('draggable', true);
                    console.log('Set draggable to true');
                }
            });
            
            // Navarre Designator Popup functionality
            let navarreCurrentPage = 1;
            let navarrePerPage = 20;
            let navarreTotalPages = 1;
            let navarreTotalItems = 0;
            let navarreSearchTerm = '';
            let navarreArchetypeFilter = 'all';
            
            $('#open-navarre-designator').click(function() {
                $('#navarre-designator-popup').fadeIn(300);
                $('body').css('overflow', 'hidden'); // Prevent background scrolling
                loadNavarreData(); // Load data when popup opens
            });
            
            $('#close-navarre-designator').click(function() {
                $('#navarre-designator-popup').fadeOut(300);
                $('body').css('overflow', 'auto'); // Restore scrolling
            });
            
            // Close popup with Escape key
            $(document).keyup(function(e) {
                if (e.keyCode === 27 && $('#navarre-designator-popup').is(':visible')) {
                    $('#close-navarre-designator').click();
                }
            });
            
            // Archetype filter buttons
            $('.navarre-filter-btn').click(function() {
                $('.navarre-filter-btn').removeClass('active');
                $(this).addClass('active');
                navarreArchetypeFilter = $(this).data('archetype');
                navarreCurrentPage = 1;
                loadNavarreData();
            });
            
            // Navarre table controls
            $('#navarre-per-page').change(function() {
                navarrePerPage = parseInt($(this).val());
                navarreCurrentPage = 1;
                loadNavarreData();
            });
            
            $('#navarre-search-btn').click(function() {
                navarreSearchTerm = $('#navarre-search').val();
                navarreCurrentPage = 1;
                loadNavarreData();
            });
            
            $('#navarre-search').keypress(function(e) {
                if (e.which === 13) {
                    $('#navarre-search-btn').click();
                }
            });
            
            $('#navarre-clear-search').click(function() {
                $('#navarre-search').val('');
                navarreSearchTerm = '';
                navarreCurrentPage = 1;
                loadNavarreData();
            });
            
            // Navarre pagination controls
            $('#navarre-first-page').click(function() {
                if (navarreCurrentPage > 1) {
                    navarreCurrentPage = 1;
                    loadNavarreData();
                }
            });
            
            $('#navarre-prev-page').click(function() {
                if (navarreCurrentPage > 1) {
                    navarreCurrentPage--;
                    loadNavarreData();
                }
            });
            
            $('#navarre-next-page').click(function() {
                if (navarreCurrentPage < navarreTotalPages) {
                    navarreCurrentPage++;
                    loadNavarreData();
                }
            });
            
            $('#navarre-last-page').click(function() {
                if (navarreCurrentPage < navarreTotalPages) {
                    navarreCurrentPage = navarreTotalPages;
                    loadNavarreData();
                }
            });
            
            function loadNavarreData() {
                $('#navarre-loading').text('Loading posts data...');
                $('#navarre-posts-tbody').html('<tr><td colspan="9" style="text-align: center; padding: 20px;"><span id="navarre-loading">Loading posts data...</span></td></tr>');
                
                $.ajax({
                    url: aragon_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aragon_load_posts_pylons_data',
                        nonce: aragon_ajax.nonce,
                        page: navarreCurrentPage,
                        per_page: navarrePerPage,
                        search: navarreSearchTerm,
                        archetype_filter: navarreArchetypeFilter
                    },
                    success: function(response) {
                        if (response.success) {
                            displayNavarreData(response.data);
                            updateNavarrePagination(response.data);
                        } else {
                            $('#navarre-posts-tbody').html('<tr><td colspan="9" style="text-align: center; padding: 20px; color: red;">Error: ' + response.data + '</td></tr>');
                        }
                    },
                    error: function() {
                        $('#navarre-posts-tbody').html('<tr><td colspan="9" style="text-align: center; padding: 20px; color: red;">Error loading posts data</td></tr>');
                    }
                });
            }
            
            function displayNavarreData(data) {
                const postsData = data.data;
                let html = '';
                
                if (postsData.length === 0) {
                    html = '<tr><td colspan="9" style="text-align: center; padding: 20px;">No posts found</td></tr>';
                } else {
                    postsData.forEach(function(post) {
                        const postDate = new Date(post.post_date).toLocaleDateString();
                        
                        html += '<tr>';
                        html += '<td class="gascon-drop-cell"></td>'; // gascon_drop_cell with polka dot pattern
                        html += '<td>' + post.id + '</td>';
                        html += '<td style="font-weight: bold;"><a href="' + adminUrl + 'post.php?post=' + post.id + '&action=edit" target="_blank">' + (post.post_title || '(No title)') + '</a></td>';
                        html += '<td><span class="post-status-' + post.post_status + '">' + post.post_status + '</span></td>';
                        html += '<td>' + post.post_type + '</td>';
                        html += '<td>' + postDate + '</td>';
                        html += '<td>' + (post.pylon_id || '') + '</td>';
                        
                        // paragon_featured_image_id column with image display
                        html += '<td><div style="display: flex; align-items: center; gap: 8px;">';
                        html += '<span style="min-width: 30px;">' + (post.paragon_featured_image_id || '') + '</span>';
                        if (post.paragon_featured_image_id) {
                            html += '<div class="navarre-image-container" style="width: 50px; height: 50px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; flex-shrink: 0; background: #f9f9f9; display: flex; align-items: center; justify-content: center;">';
                            html += '<img class="navarre-featured-image" data-image-id="' + post.paragon_featured_image_id + '" style="max-width: 100%; max-height: 100%; object-fit: cover;" src="" alt="Loading...">';
                            html += '</div>';
                        } else {
                            html += '<div style="width: 50px; height: 50px; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5; flex-shrink: 0;"></div>';
                        }
                        html += '</div></td>';
                        
                        html += '<td>' + (post.pylon_archetype || '') + '</td>';
                        html += '</tr>';
                    });
                }
                
                $('#navarre-posts-tbody').html(html);
                
                // Load featured images asynchronously
                loadNavarreImages();
                
                // Initialize drop functionality for gascon cells
                initializeDropFunctionality();
                
                // Update showing info
                const start = (navarreCurrentPage - 1) * navarrePerPage + 1;
                const end = Math.min(navarreCurrentPage * navarrePerPage, navarreTotalItems);
                $('#navarre-showing-info').text('Showing ' + start + '-' + end + ' of ' + navarreTotalItems + ' items');
            }
            
            function loadNavarreImages() {
                $('.navarre-featured-image').each(function() {
                    const $img = $(this);
                    const imageId = $img.data('image-id');
                    
                    if (imageId) {
                        // Use WordPress AJAX to get image URL
                        $.ajax({
                            url: aragon_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'aragon_get_image_url',
                                nonce: aragon_ajax.nonce,
                                image_id: imageId
                            },
                            success: function(response) {
                                if (response.success && response.data.url) {
                                    $img.attr('src', response.data.url);
                                    $img.attr('alt', response.data.title || 'Featured Image');
                                } else {
                                    $img.parent().html('<span style="font-size: 10px; color: #999;">No image</span>');
                                }
                            },
                            error: function() {
                                $img.parent().html('<span style="font-size: 10px; color: #999;">Error</span>');
                            }
                        });
                    }
                });
            }
            
            function updateNavarrePagination(data) {
                navarreTotalItems = data.total;
                navarreTotalPages = data.total_pages;
                navarreCurrentPage = data.page;
                
                // Update pagination info
                $('#navarre-page-info').text('Page ' + navarreCurrentPage + ' of ' + navarreTotalPages);
                $('#navarre-pagination-info').text(navarreTotalItems + ' items total');
                
                // Update button states
                $('#navarre-first-page, #navarre-prev-page').prop('disabled', navarreCurrentPage <= 1);
                $('#navarre-next-page, #navarre-last-page').prop('disabled', navarreCurrentPage >= navarreTotalPages);
            }
            
            // Per page selector
            $('#aragon-per-page').change(function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadMediaData();
            });
            
            // Search functionality
            $('#aragon-search-btn').click(function() {
                searchTerm = $('#aragon-search').val();
                currentPage = 1;
                loadMediaData();
            });
            
            $('#aragon-search').keypress(function(e) {
                if (e.which === 13) {
                    $('#aragon-search-btn').click();
                }
            });
            
            $('#aragon-clear-search').click(function() {
                $('#aragon-search').val('');
                searchTerm = '';
                currentPage = 1;
                loadMediaData();
            });
            
            // Pagination controls
            $('#aragon-first-page').click(function() {
                if (currentPage > 1) {
                    currentPage = 1;
                    loadMediaData();
                }
            });
            
            $('#aragon-prev-page').click(function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadMediaData();
                }
            });
            
            $('#aragon-next-page').click(function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadMediaData();
                }
            });
            
            $('#aragon-last-page').click(function() {
                if (currentPage < totalPages) {
                    currentPage = totalPages;
                    loadMediaData();
                }
            });
            
            // Select all checkbox
            $('#aragon-select-all').change(function() {
                const isChecked = $(this).is(':checked');
                $('.aragon-media-checkbox').prop('checked', isChecked);
            });
            
            function loadMediaData() {
                $('#aragon-loading').text('Loading media files...');
                $('#aragon-media-tbody').html('<tr><td colspan="13" style="text-align: center; padding: 20px;"><span id="aragon-loading">Loading media files...</span></td></tr>');
                
                $.ajax({
                    url: aragon_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aragon_load_media_data',
                        nonce: aragon_ajax.nonce,
                        page: currentPage,
                        per_page: perPage,
                        search: searchTerm
                    },
                    success: function(response) {
                        if (response.success) {
                            displayMediaData(response.data);
                            updatePagination(response.data);
                        } else {
                            $('#aragon-media-tbody').html('<tr><td colspan="13" style="text-align: center; padding: 20px; color: red;">Error: ' + response.data + '</td></tr>');
                        }
                    },
                    error: function() {
                        $('#aragon-media-tbody').html('<tr><td colspan="13" style="text-align: center; padding: 20px; color: red;">Error loading media data</td></tr>');
                    }
                });
            }
            
            function displayMediaData(data) {
                const mediaData = data.data;
                let html = '';
                
                if (mediaData.length === 0) {
                    html = '<tr><td colspan="13" style="text-align: center; padding: 20px;">No media files found</td></tr>';
                } else {
                    mediaData.forEach(function(media) {
                        const thumbnail = media.thumb_url ? 
                            '<img src="' + media.thumb_url + '" class="media-thumbnail" alt="' + media.title + '" draggable="true">' :
                            '<div class="media-placeholder">No preview</div>';
                        
                        const attachedTo = media.attached_to ? 
                            '<a href="' + adminUrl + 'post.php?post=' + media.attached_to + '&action=edit">(Edit)</a>' : 
                            'Unattached';
                        
                        const uploadDate = new Date(media.uploaded_date).toLocaleDateString();
                        
                        // Truncate long text for display
                        const truncateText = function(text, maxLength) {
                            if (!text) return '';
                            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
                        };
                        
                        html += '<tr>';
                        html += '<td><input type="checkbox" class="aragon-media-checkbox" value="' + media.id + '"></td>';
                        html += '<td>' + thumbnail + '</td>';
                        html += '<td><span class="media-title" data-id="' + media.id + '">' + media.title + '</span></td>';
                        html += '<td>Author #' + media.author_id + '</td>';
                        html += '<td>' + attachedTo + '</td>';
                        html += '<td>' + uploadDate + '</td>';
                        html += '<td><span class="media-filename">' + media.filename + '</span></td>';
                        html += '<td>' + media.mime_type + '</td>';
                        html += '<td>' + media.file_size + '</td>';
                        html += '<td>' + media.dimensions + '</td>';
                        html += '<td><span class="aragon-alt-text" title="' + (media.alt_text || '') + '">' + truncateText(media.alt_text, 30) + '</span></td>';
                        html += '<td><span class="aragon-description" title="' + (media.description || '') + '">' + truncateText(media.description, 50) + '</span></td>';
                        html += '<td><span class="aragon-caption" title="' + (media.caption || '') + '">' + truncateText(media.caption, 50) + '</span></td>';
                        html += '</tr>';
                    });
                }
                
                $('#aragon-media-tbody').html(html);
                
                // Initialize drag functionality for media thumbnails
                initializeDragFunctionality();
                
                // Update showing info
                const start = (currentPage - 1) * perPage + 1;
                const end = Math.min(currentPage * perPage, totalItems);
                $('#aragon-showing-info').text('Showing ' + start + '-' + end + ' of ' + totalItems + ' items');
            }
            
            function updatePagination(data) {
                totalItems = data.total;
                totalPages = data.total_pages;
                currentPage = data.page;
                
                // Update pagination info
                $('#aragon-page-info').text('Page ' + currentPage + ' of ' + totalPages);
                $('#aragon-pagination-info').text(totalItems + ' items total');
                
                // Update button states
                $('#aragon-first-page, #aragon-prev-page').prop('disabled', currentPage <= 1);
                $('#aragon-next-page, #aragon-last-page').prop('disabled', currentPage >= totalPages);
            }
            
            // Media title click handler (for future use)
            $(document).on('click', '.media-title', function() {
                const mediaId = $(this).data('id');
                console.log('Clicked media ID:', mediaId);
                // Future: Open edit modal or redirect to edit page
            });
            
            // Drag and Drop Functionality
            function initializeDragFunctionality() {
                $('.media-thumbnail').each(function() {
                    const $img = $(this);
                    const $row = $img.closest('tr');
                    const mediaId = $row.find('.aragon-media-checkbox').val();
                    
                    $img.attr('draggable', true);
                    $img.data('media-id', mediaId);
                });
            }
            
            // Use event delegation for drag events
            $(document).on('dragstart', '.media-thumbnail', function(e) {
                const $img = $(this);
                const $row = $img.closest('tr');
                const mediaId = $row.find('.aragon-media-checkbox').val();
                
                $img.addClass('dragging');
                $('body').addClass('dragging-active');
                e.originalEvent.dataTransfer.setData('text/plain', mediaId);
                e.originalEvent.dataTransfer.effectAllowed = 'copy';
                
                console.log('Drag started for media ID:', mediaId);
            });
            
            $(document).on('dragend', '.media-thumbnail', function() {
                $(this).removeClass('dragging');
                $('body').removeClass('dragging-active');
                $('.gascon-drop-cell').removeClass('drag-over');
                console.log('Drag ended');
            });
            
            function initializeDropFunctionality() {
                $('.gascon-drop-cell').each(function() {
                    const $cell = $(this);
                    const $row = $cell.closest('tr');
                    const postId = $row.find('td:nth-child(2)').text(); // post-id column
                    
                    $cell.on('dragover', function(e) {
                        e.preventDefault();
                        e.originalEvent.dataTransfer.dropEffect = 'copy';
                        $cell.addClass('drag-over');
                    });
                    
                    $cell.on('dragleave', function() {
                        $cell.removeClass('drag-over');
                    });
                    
                    $cell.on('drop', function(e) {
                        e.preventDefault();
                        const mediaId = e.originalEvent.dataTransfer.getData('text/plain');
                        $cell.removeClass('drag-over').addClass('drop-success');
                        
                        // Update paragon_featured_image_id in database
                        updateParagonFeaturedImage(postId, mediaId, $cell);
                    });
                });
            }
            
            function updateParagonFeaturedImage(postId, mediaId, $cell) {
                $.ajax({
                    url: aragon_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aragon_update_paragon_featured_image',
                        nonce: aragon_ajax.nonce,
                        post_id: postId,
                        media_id: mediaId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Success feedback
                            setTimeout(function() {
                                $cell.removeClass('drop-success');
                                // Refresh the navarre table to show updated image
                                loadNavarreData();
                            }, 1000);
                        } else {
                            alert('Error updating image: ' + response.data);
                            $cell.removeClass('drop-success');
                        }
                    },
                    error: function() {
                        alert('Error updating image');
                        $cell.removeClass('drop-success');
                    }
                });
            }
            
            const adminUrl = '<?php echo admin_url(); ?>';
        });
        </script>
        
        <?php
    }
}

// Initialize the class
new Aragon_Image_Manager();