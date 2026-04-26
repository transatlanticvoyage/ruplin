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

        // AJAX handlers for image attachments
        add_action('wp_ajax_work_projects_mar_attach_images', array($this, 'ajax_attach_images'));
        add_action('wp_ajax_work_projects_mar_detach_image', array($this, 'ajax_detach_image'));
        add_action('wp_ajax_work_projects_mar_clear_images', array($this, 'ajax_clear_images'));
        add_action('wp_ajax_work_projects_mar_list_images', array($this, 'ajax_list_images'));

        // AJAX handlers for per-relation (per-image-in-project) client metadata
        add_action('wp_ajax_work_projects_mar_get_all_relations', array($this, 'ajax_get_all_relations'));
        add_action('wp_ajax_work_projects_mar_update_relation_meta', array($this, 'ajax_update_relation_meta'));

        // Load WP media library assets on this admin page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_media_library'));
    }

    /**
     * Enqueue WP media library on the work_projects_mar screen only
     */
    public function enqueue_media_library($hook_suffix) {
        if (isset($_GET['page']) && $_GET['page'] === 'work_projects_mar') {
            wp_enqueue_media();
        }
    }
    
    /**
     * Add submenu page to Ruplin Hub
     */
    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_2_mar',  // Parent slug (Ruplin Hub 2)
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
        } else {
            // Existing table — ensure newer schema additions are present
            if ($this->ensure_columns_up_to_date()) {
                $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
            }
        }

        return $columns;
    }

    /**
     * Idempotent column migrations for existing installs.
     * Adds new columns one-by-one if they don't already exist.
     * Returns true if any column was added (so callers can re-fetch).
     */
    private function ensure_columns_up_to_date() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'work_projects';
        $rel_table  = $wpdb->prefix . 'work_projects_images_relations';
        $changed = false;

        // wp_work_projects.client_location
        $col_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'client_location'");
        if (empty($col_exists)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN client_location TEXT DEFAULT NULL AFTER client_name");
            $changed = true;
        }

        // wp_work_projects_images_relations.client_name + client_location
        // Per-image-in-project metadata (one row per image-in-project pairing).
        $rel_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$rel_table'");
        if ($rel_table_exists) {
            $rel_col_name = $wpdb->get_results("SHOW COLUMNS FROM $rel_table LIKE 'client_name'");
            if (empty($rel_col_name)) {
                $wpdb->query("ALTER TABLE $rel_table ADD COLUMN client_name TEXT DEFAULT NULL AFTER image_id");
                $changed = true;
            }
            $rel_col_loc = $wpdb->get_results("SHOW COLUMNS FROM $rel_table LIKE 'client_location'");
            if (empty($rel_col_loc)) {
                $wpdb->query("ALTER TABLE $rel_table ADD COLUMN client_location TEXT DEFAULT NULL AFTER client_name");
                $changed = true;
            }
        }

        return $changed;
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
            client_location text DEFAULT NULL,
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
     * Get attached images for a project
     * Returns array of rows with relation_id, image_id, thumb_url, full_url, title
     */
    private function get_project_images($project_id) {
        global $wpdb;
        $rel_table = $wpdb->prefix . 'work_projects_images_relations';

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT r.relation_id, r.image_id, p.post_title
             FROM $rel_table r
             LEFT JOIN {$wpdb->posts} p ON p.ID = r.image_id
             WHERE r.project_id = %d
             ORDER BY r.relation_id ASC",
            $project_id
        ), ARRAY_A);

        $result = array();
        foreach ($rows as $row) {
            $thumb = wp_get_attachment_image_url(intval($row['image_id']), 'thumbnail');
            $full  = wp_get_attachment_image_url(intval($row['image_id']), 'full');
            $result[] = array(
                'relation_id' => intval($row['relation_id']),
                'image_id'    => intval($row['image_id']),
                'title'       => $row['post_title'],
                'thumb_url'   => $thumb ? $thumb : '',
                'full_url'    => $full ? $full : '',
            );
        }
        return $result;
    }

    /**
     * Render the image-strip HTML for a single project row
     */
    private function render_images_cell($project_id) {
        $images = $this->get_project_images($project_id);
        $html  = '<div class="wpm-images-cell" data-project-id="' . intval($project_id) . '">';
        $html .= '<div class="wpm-images-strip">';
        if (empty($images)) {
            $html .= '<span class="wpm-images-empty">No images</span>';
        } else {
            foreach ($images as $img) {
                $html .= '<span class="wpm-image-thumb" data-relation-id="' . intval($img['relation_id']) . '">';
                if ($img['thumb_url']) {
                    $html .= '<img src="' . esc_url($img['thumb_url']) . '" alt="' . esc_attr($img['title']) . '" />';
                } else {
                    $html .= '<span class="wpm-missing-image">#' . intval($img['image_id']) . '</span>';
                }
                $html .= '<button type="button" class="wpm-thumb-remove" title="Remove">&times;</button>';
                $html .= '</span>';
            }
        }
        $html .= '</div>';
        $html .= '<div class="wpm-images-actions">';
        $html .= '<button type="button" class="button button-small wpm-add-images">Add Images</button>';
        if (!empty($images)) {
            $html .= ' <button type="button" class="button button-small wpm-clear-images">Clear All</button>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * AJAX: Attach one or more images to a project
     * Expects: project_id (int), image_ids (array of ints)
     */
    public function ajax_attach_images() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');

        global $wpdb;
        $rel_table = $wpdb->prefix . 'work_projects_images_relations';

        $project_id = intval($_POST['project_id'] ?? 0);
        $image_ids  = isset($_POST['image_ids']) && is_array($_POST['image_ids']) ? array_map('intval', $_POST['image_ids']) : array();

        if ($project_id <= 0 || empty($image_ids)) {
            wp_send_json_error(array('message' => 'Missing project_id or image_ids'));
        }

        $inserted = 0;
        foreach ($image_ids as $image_id) {
            if ($image_id <= 0) continue;
            $result = $wpdb->insert(
                $rel_table,
                array(
                    'project_id' => $project_id,
                    'image_id'   => $image_id,
                )
            );
            if ($result) $inserted++;
        }

        wp_send_json_success(array(
            'inserted' => $inserted,
            'html'     => $this->render_images_cell($project_id),
        ));
    }

    /**
     * AJAX: Detach a single relation row
     * Expects: relation_id (int), project_id (int)
     */
    public function ajax_detach_image() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');

        global $wpdb;
        $rel_table = $wpdb->prefix . 'work_projects_images_relations';

        $relation_id = intval($_POST['relation_id'] ?? 0);
        $project_id  = intval($_POST['project_id'] ?? 0);

        if ($relation_id <= 0 || $project_id <= 0) {
            wp_send_json_error(array('message' => 'Missing relation_id or project_id'));
        }

        $wpdb->delete($rel_table, array('relation_id' => $relation_id));

        wp_send_json_success(array(
            'html' => $this->render_images_cell($project_id),
        ));
    }

    /**
     * AJAX: Clear all image relations for a project
     * Expects: project_id (int)
     */
    public function ajax_clear_images() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');

        global $wpdb;
        $rel_table = $wpdb->prefix . 'work_projects_images_relations';

        $project_id = intval($_POST['project_id'] ?? 0);
        if ($project_id <= 0) {
            wp_send_json_error(array('message' => 'Missing project_id'));
        }

        $wpdb->delete($rel_table, array('project_id' => $project_id));

        wp_send_json_success(array(
            'html' => $this->render_images_cell($project_id),
        ));
    }

    /**
     * AJAX: List images for a project (returns rendered HTML for the cell)
     * Expects: project_id (int)
     */
    public function ajax_list_images() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');

        $project_id = intval($_POST['project_id'] ?? 0);
        if ($project_id <= 0) {
            wp_send_json_error(array('message' => 'Missing project_id'));
        }

        wp_send_json_success(array(
            'html' => $this->render_images_cell($project_id),
        ));
    }

    /**
     * AJAX: Return a flat ordered list of every image-in-project relation,
     * with project context and image URLs, for the popup's prev/next cycling.
     * Sorted by project_id, then relation_id (insertion order within project).
     */
    public function ajax_get_all_relations() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');

        global $wpdb;
        $rel_table  = $wpdb->prefix . 'work_projects_images_relations';
        $proj_table = $wpdb->prefix . 'work_projects';

        $rows = $wpdb->get_results(
            "SELECT r.relation_id, r.project_id, r.image_id, r.client_name, r.client_location,
                    p.project_name
             FROM $rel_table r
             LEFT JOIN $proj_table p ON p.id = r.project_id
             ORDER BY r.project_id ASC, r.relation_id ASC",
            ARRAY_A
        );

        $out = array();
        foreach ($rows as $row) {
            $image_id = intval($row['image_id']);
            $thumb_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'medium')   : '';
            $full_url  = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'full')     : '';
            $title     = $image_id > 0 ? get_the_title($image_id) : '';
            $out[] = array(
                'relation_id'     => intval($row['relation_id']),
                'project_id'      => intval($row['project_id']),
                'project_name'    => $row['project_name'] ?: '',
                'image_id'        => $image_id,
                'image_thumb_url' => $thumb_url,
                'image_full_url'  => $full_url,
                'image_title'     => $title,
                'client_name'     => $row['client_name'] ?: '',
                'client_location' => $row['client_location'] ?: '',
            );
        }

        wp_send_json_success(array('relations' => $out));
    }

    /**
     * AJAX: Update one client metadata field (client_name | client_location)
     * for a specific relation row.
     */
    public function ajax_update_relation_meta() {
        check_ajax_referer('work_projects_mar_nonce', 'nonce');

        global $wpdb;
        $rel_table = $wpdb->prefix . 'work_projects_images_relations';

        $relation_id = intval($_POST['relation_id'] ?? 0);
        $field       = isset($_POST['field']) ? sanitize_key($_POST['field']) : '';
        $value       = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';
        $value       = sanitize_text_field($value);

        $allowed = array('client_name', 'client_location');
        if ($relation_id <= 0 || !in_array($field, $allowed, true)) {
            wp_send_json_error(array('message' => 'Bad relation_id or field'));
        }

        $result = $wpdb->update(
            $rel_table,
            array($field => $value),
            array('relation_id' => $relation_id)
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Update failed'));
        }

        wp_send_json_success(array('field' => $field, 'value' => $value));
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
            
            <?php 
            global $wpdb;
            $table_name = $wpdb->prefix . 'work_projects';
            ?>
            <div style="background: #f0f0f1; padding: 10px 15px; margin-bottom: 10px; border-left: 4px solid #2271b1;">
                <strong>Database Table:</strong> <code style="background: #fff; padding: 2px 5px; font-family: monospace;"><?php echo esc_html($table_name); ?></code>
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
                            <th style="border: 1px solid gray; padding: 10px; text-align: left; font-size: 16px; color: #242424; font-weight: normal;">
                                images
                            </th>
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
                                    <td style="border: 1px solid gray; padding: 8px;">
                                        <?php echo $this->render_images_cell(intval($record['id'])); ?>
                                    </td>
                                    <td style="border: 1px solid gray; padding: 8px; text-align: center;">
                                        <button class="button button-small delete-record" data-id="<?php echo esc_attr($record['id']); ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo count($columns) + 2; ?>" style="border: 1px solid gray; padding: 20px; text-align: center; color: #666;">
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

        /* Images cell */
        .wpm-images-cell { min-width: 260px; }
        .wpm-images-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 6px;
        }
        .wpm-image-thumb {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 48px;
            background: #f6f6f6;
            border: 1px solid #ddd;
            overflow: hidden;
        }
        .wpm-image-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .wpm-image-thumb .wpm-missing-image {
            font-size: 10px;
            color: #c00;
            padding: 4px;
            display: block;
        }
        .wpm-thumb-remove {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 18px;
            height: 18px;
            line-height: 16px;
            text-align: center;
            border-radius: 50%;
            border: 1px solid #c00;
            background: #fff;
            color: #c00;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
        }
        .wpm-thumb-remove:hover { background: #c00; color: #fff; }
        .wpm-images-empty {
            color: #999;
            font-style: italic;
            font-size: 12px;
        }
        .wpm-images-actions .button { margin-right: 4px; }

        /* ─────────────────────────────────────────────────────────────
           Image meta popup (per-image client metadata editor)
           ───────────────────────────────────────────────────────────── */
        .wpm-image-thumb img { cursor: pointer; }
        .wpm-meta-popup-backdrop {
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 100000;
            display: none;
            align-items: center; justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }
        .wpm-meta-popup-backdrop.is-open { display: flex; }
        .wpm-meta-popup {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            max-height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .wpm-meta-popup_header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px;
            background: #1d2327;
            color: #fff;
        }
        .wpm-meta-popup_header_title {
            font-family: ui-monospace, "SF Mono", Menlo, monospace;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .wpm-meta-popup_close {
            background: none; border: 0; color: #fff;
            cursor: pointer; padding: 4px 8px;
            font-size: 22px; line-height: 1;
        }
        .wpm-meta-popup_close:hover { color: #ffb900; }

        .wpm-meta-popup_body {
            padding: 16px;
            overflow-y: auto;
        }
        .wpm-meta-popup_image_wrap {
            display: flex; align-items: center; justify-content: center;
            background: #f0f0f1;
            border-radius: 4px;
            min-height: 220px;
            margin-bottom: 14px;
            overflow: hidden;
        }
        .wpm-meta-popup_image {
            display: block;
            max-width: 500px;
            max-height: 500px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .wpm-meta-popup_context {
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 14px;
            font-size: 12px;
            line-height: 1.5;
        }
        .wpm-meta-popup_context_label {
            font-family: ui-monospace, "SF Mono", Menlo, monospace;
            color: #50575e;
        }
        .wpm-meta-popup_context_value {
            font-weight: 600;
            color: #1d2327;
        }
        .wpm-meta-popup_field {
            margin-bottom: 12px;
        }
        .wpm-meta-popup_field label {
            display: block;
            font-family: ui-monospace, "SF Mono", Menlo, monospace;
            font-size: 12px;
            color: #50575e;
            margin-bottom: 4px;
            text-transform: lowercase;
        }
        .wpm-meta-popup_input {
            width: 100%;
            box-sizing: border-box;
            padding: 8px 10px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.15s, background 0.5s ease;
        }
        .wpm-meta-popup_input:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }
        .wpm-meta-popup_input.saving { background-color: #fffbcc; }
        .wpm-meta-popup_input.saved  { background-color: #d4edda; }
        .wpm-meta-popup_input.error  { background-color: #f8d7da; }

        .wpm-meta-popup_nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 16px;
            background: #f6f7f7;
            border-top: 1px solid #dcdcde;
        }
        .wpm-meta-popup_nav_btn {
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            padding: 6px 14px;
            cursor: pointer;
            font-size: 13px;
            color: #1d2327;
        }
        .wpm-meta-popup_nav_btn:hover {
            background: #2271b1;
            border-color: #2271b1;
            color: #fff;
        }
        .wpm-meta-popup_nav_btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .wpm-meta-popup_nav_position {
            font-size: 12px;
            color: #50575e;
            font-variant-numeric: tabular-nums;
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
            
            // -----------------------------------------------------------
            // Image management — WP media picker
            // -----------------------------------------------------------
            var wpmMediaFrame = null;

            // "Add Images" — open WP media picker in multi-select mode
            $(document).on('click', '.wpm-add-images', function(e) {
                e.preventDefault();
                var $cell = $(this).closest('.wpm-images-cell');
                var projectId = $cell.data('project-id');

                // Fresh frame each time so selection state doesn't leak between rows
                wpmMediaFrame = wp.media({
                    title: 'Select Images for Project #' + projectId,
                    button: { text: 'Attach Selected' },
                    library: { type: 'image' },
                    multiple: 'add'
                });

                wpmMediaFrame.on('select', function() {
                    var attachments = wpmMediaFrame.state().get('selection').toJSON();
                    var imageIds = attachments.map(function(a) { return a.id; });
                    if (!imageIds.length) return;

                    $.post(ajaxurl, {
                        action: 'work_projects_mar_attach_images',
                        nonce: nonce,
                        project_id: projectId,
                        image_ids: imageIds
                    }, function(response) {
                        if (response.success) {
                            $cell.replaceWith(response.data.html);
                        } else {
                            alert('Failed to attach images: ' + (response.data && response.data.message || 'unknown error'));
                        }
                    });
                });

                wpmMediaFrame.open();
            });

            // Remove a single image (X button on thumb)
            $(document).on('click', '.wpm-thumb-remove', function(e) {
                e.preventDefault();
                var $thumb = $(this).closest('.wpm-image-thumb');
                var $cell = $(this).closest('.wpm-images-cell');
                var projectId = $cell.data('project-id');
                var relationId = $thumb.data('relation-id');
                if (!relationId) return;

                $.post(ajaxurl, {
                    action: 'work_projects_mar_detach_image',
                    nonce: nonce,
                    project_id: projectId,
                    relation_id: relationId
                }, function(response) {
                    if (response.success) {
                        $cell.replaceWith(response.data.html);
                    } else {
                        alert('Failed to detach image');
                    }
                });
            });

            // Clear all images for a project
            $(document).on('click', '.wpm-clear-images', function(e) {
                e.preventDefault();
                if (!confirm('Remove all images from this project? Images remain in the media library — only the project relation is cleared.')) return;

                var $cell = $(this).closest('.wpm-images-cell');
                var projectId = $cell.data('project-id');

                $.post(ajaxurl, {
                    action: 'work_projects_mar_clear_images',
                    nonce: nonce,
                    project_id: projectId
                }, function(response) {
                    if (response.success) {
                        $cell.replaceWith(response.data.html);
                    } else {
                        alert('Failed to clear images');
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

        <!-- ─── Image Meta Popup (per-image client metadata editor) ─── -->
        <div class="wpm-meta-popup-backdrop" id="wpm-meta-popup-backdrop" aria-hidden="true">
            <div class="wpm-meta-popup" role="dialog" aria-modal="true" aria-labelledby="wpm-meta-popup-header-title">
                <div class="wpm-meta-popup_header">
                    <span class="wpm-meta-popup_header_title" id="wpm-meta-popup-header-title">wp_work_projects_images_relations</span>
                    <button type="button" class="wpm-meta-popup_close" id="wpm-meta-popup-close" aria-label="Close">&times;</button>
                </div>
                <div class="wpm-meta-popup_body">
                    <div class="wpm-meta-popup_image_wrap">
                        <img class="wpm-meta-popup_image" id="wpm-meta-popup-image" src="" alt="" />
                    </div>
                    <div class="wpm-meta-popup_context">
                        <span class="wpm-meta-popup_context_label">wp_work_projects.project_name:</span>
                        <span class="wpm-meta-popup_context_value" id="wpm-meta-popup-project-name"></span>
                    </div>
                    <div class="wpm-meta-popup_field">
                        <label for="wpm-meta-popup-client-name">client_name</label>
                        <input type="text" class="wpm-meta-popup_input" id="wpm-meta-popup-client-name" data-field="client_name" autocomplete="off" />
                    </div>
                    <div class="wpm-meta-popup_field">
                        <label for="wpm-meta-popup-client-location">client_location</label>
                        <input type="text" class="wpm-meta-popup_input" id="wpm-meta-popup-client-location" data-field="client_location" autocomplete="off" />
                    </div>
                </div>
                <div class="wpm-meta-popup_nav">
                    <button type="button" class="wpm-meta-popup_nav_btn" id="wpm-meta-popup-prev">&larr; Prev</button>
                    <span class="wpm-meta-popup_nav_position" id="wpm-meta-popup-position"></span>
                    <button type="button" class="wpm-meta-popup_nav_btn" id="wpm-meta-popup-next">Next &rarr;</button>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var nonce   = '<?php echo wp_create_nonce('work_projects_mar_nonce'); ?>';

            var allRelations = [];   // flat list across all projects
            var currentIndex = -1;   // index into allRelations

            var $backdrop  = $('#wpm-meta-popup-backdrop');
            var $closeBtn  = $('#wpm-meta-popup-close');
            var $img       = $('#wpm-meta-popup-image');
            var $projName  = $('#wpm-meta-popup-project-name');
            var $clientName = $('#wpm-meta-popup-client-name');
            var $clientLoc  = $('#wpm-meta-popup-client-location');
            var $prevBtn   = $('#wpm-meta-popup-prev');
            var $nextBtn   = $('#wpm-meta-popup-next');
            var $position  = $('#wpm-meta-popup-position');

            function renderCurrent() {
                if (currentIndex < 0 || currentIndex >= allRelations.length) return;
                var rel = allRelations[currentIndex];
                $img.attr('src', rel.image_full_url || rel.image_thumb_url || '')
                    .attr('alt', rel.image_title || '');
                $projName.text(rel.project_name || ('Project #' + rel.project_id));
                $clientName.val(rel.client_name || '').removeClass('saving saved error');
                $clientLoc.val(rel.client_location || '').removeClass('saving saved error');
                $position.text((currentIndex + 1) + ' / ' + allRelations.length);
                var multiple = allRelations.length > 1;
                $prevBtn.prop('disabled', !multiple);
                $nextBtn.prop('disabled', !multiple);
            }

            function openPopupForRelation(relationId) {
                $.post(ajaxurl, {
                    action: 'work_projects_mar_get_all_relations',
                    nonce: nonce
                }, function(response) {
                    if (!response || !response.success) {
                        alert('Failed to load image list');
                        return;
                    }
                    allRelations = response.data.relations || [];
                    currentIndex = -1;
                    for (var i = 0; i < allRelations.length; i++) {
                        if (allRelations[i].relation_id === relationId) {
                            currentIndex = i;
                            break;
                        }
                    }
                    if (currentIndex < 0 && allRelations.length > 0) currentIndex = 0;
                    if (currentIndex < 0) {
                        alert('No images found');
                        return;
                    }
                    renderCurrent();
                    $backdrop.addClass('is-open').attr('aria-hidden', 'false');
                });
            }

            function closePopup() {
                $backdrop.removeClass('is-open').attr('aria-hidden', 'true');
                allRelations = [];
                currentIndex = -1;
            }

            // Click on any thumbnail image opens the meta popup for that relation.
            // The X delete button has its own handler with stopPropagation precedence.
            $(document).on('click', '.wpm-image-thumb img', function(e) {
                var $thumb = $(this).closest('.wpm-image-thumb');
                var relationId = parseInt($thumb.data('relation-id'), 10);
                if (!relationId) return;
                e.preventDefault();
                e.stopPropagation();
                openPopupForRelation(relationId);
            });

            // Prevent the X delete button from also triggering the popup
            $(document).on('click', '.wpm-thumb-remove', function(e) {
                e.stopPropagation();
            });

            $closeBtn.on('click', closePopup);

            // Close on backdrop click (but not on popup body click)
            $backdrop.on('click', function(e) {
                if (e.target === this) closePopup();
            });

            // Esc closes
            $(document).on('keydown', function(e) {
                if (!$backdrop.hasClass('is-open')) return;
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closePopup();
                } else if (e.key === 'ArrowRight') {
                    $nextBtn.trigger('click');
                } else if (e.key === 'ArrowLeft') {
                    $prevBtn.trigger('click');
                }
            });

            // Prev / Next cycle through ALL relations across projects
            $prevBtn.on('click', function() {
                if (allRelations.length < 2) return;
                currentIndex = (currentIndex - 1 + allRelations.length) % allRelations.length;
                renderCurrent();
            });
            $nextBtn.on('click', function() {
                if (allRelations.length < 2) return;
                currentIndex = (currentIndex + 1) % allRelations.length;
                renderCurrent();
            });

            // Inline save on blur for both meta fields
            function saveField($input) {
                if (currentIndex < 0) return;
                var rel = allRelations[currentIndex];
                var field = $input.data('field');
                var newVal = $input.val();
                var oldVal = rel[field] || '';
                if (newVal === oldVal) return;

                $input.removeClass('saved error').addClass('saving');

                $.post(ajaxurl, {
                    action: 'work_projects_mar_update_relation_meta',
                    nonce: nonce,
                    relation_id: rel.relation_id,
                    field: field,
                    value: newVal
                }, function(response) {
                    $input.removeClass('saving');
                    if (response && response.success) {
                        rel[field] = newVal;
                        $input.addClass('saved');
                        setTimeout(function() { $input.removeClass('saved'); }, 1200);
                    } else {
                        $input.addClass('error');
                        setTimeout(function() { $input.removeClass('error'); }, 2000);
                    }
                });
            }

            $clientName.on('blur', function() { saveField($(this)); });
            $clientLoc.on('blur',  function() { saveField($(this)); });
        });
        </script>
        <?php
    }
}

// Initialize the class
new Ruplin_Work_Projects_Mar_Page();