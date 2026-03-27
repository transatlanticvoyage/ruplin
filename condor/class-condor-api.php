<?php
/**
 * Condor API Endpoint
 * Provides REST API endpoints for Plasma Wizard Step 130 integration
 * Handles direct WordPress content injection with Application Password authentication
 */

class Ruplin_Condor_API {
    
    /**
     * Constructor - Register REST API routes
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Main namespace for Condor endpoints
        $namespace = 'ruplin-condor/v1';
        
        // Create pylon record
        register_rest_route($namespace, '/create-pylon', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_pylon'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'rel_wp_post_id' => array(
                    'required' => true,
                    'type' => 'integer'
                ),
                'plasma_page_id' => array(
                    'required' => false,
                    'type' => 'integer'
                )
            )
        ));
        
        // Batch import endpoint
        register_rest_route($namespace, '/batch-import', array(
            'methods' => 'POST',
            'callback' => array($this, 'batch_import'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Test connection endpoint
        register_rest_route($namespace, '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Get pylons count
        register_rest_route($namespace, '/pylons-count', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_pylons_count'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Nuke content endpoint - deletes all pages, posts and pylons
        register_rest_route($namespace, '/nuke-content', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'nuke_content'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Nuke driggs endpoint - clears wp_zen_sitespren data
        register_rest_route($namespace, '/nuke-driggs', array(
            'methods' => 'POST',
            'callback' => array($this, 'nuke_driggs'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Inject sitespren data endpoint
        register_rest_route($namespace, '/inject-sitespren', array(
            'methods' => 'POST',
            'callback' => array($this, 'inject_sitespren'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }
    
    /**
     * Permission callback - Check if user has permission to use the API
     * Supports Application Password authentication
     */
    public function check_permission($request) {
        // Check if user is authenticated
        $current_user = wp_get_current_user();
        
        if ($current_user && $current_user->ID > 0) {
            // Check for manage_options capability (admin)
            return current_user_can('manage_options');
        }
        
        // If not authenticated, check for Application Password in headers
        $auth_header = $request->get_header('Authorization');
        
        if ($auth_header && strpos($auth_header, 'Basic ') === 0) {
            // Basic auth is handled by WordPress automatically for REST API
            // If we get here and there's an auth header, WordPress will handle it
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a pylon record
     */
    public function create_pylon($request) {
        global $wpdb;
        
        $params = $request->get_json_params();
        $pylons_table = $wpdb->prefix . 'pylons';
        
        // Build pylon data
        $pylon_data = array(
            'rel_wp_post_id' => intval($params['rel_wp_post_id'])
        );
        
        // Add optional fields
        if (isset($params['plasma_page_id'])) {
            $pylon_data['plasma_page_id'] = intval($params['plasma_page_id']);
        }
        
        if (isset($params['pylon_archetype'])) {
            $pylon_data['pylon_archetype'] = sanitize_text_field($params['pylon_archetype']);
            
            // Apply OSB rule
            if ($params['pylon_archetype'] === 'homepage') {
                $pylon_data['osb_is_enabled'] = 1;
            }
        }
        
        // Get the list of allowed columns from the database
        $allowed_fields = $this->get_pylons_columns();
        
        // Auto-map ALL fields that exist in both the request and the database schema
        // This ensures all matching plasma_pages fields are transferred to wp_pylons
        foreach ($params as $key => $value) {
            // Skip if already set or not in allowed fields
            if (isset($pylon_data[$key]) || !in_array($key, $allowed_fields)) {
                continue;
            }
            
            // Sanitize based on field type/name patterns
            if (strpos($key, '_id') !== false || $key === 'jchronology_order_for_blog_posts' || $key === 'jchronology_batch') {
                // Numeric fields
                $pylon_data[$key] = is_numeric($value) ? intval($value) : null;
            } elseif (strpos($key, '_hide') !== false || strpos($key, 'is_enabled') !== false) {
                // Boolean fields
                $pylon_data[$key] = $value ? 1 : 0;
            } elseif (strpos($key, '_date') !== false) {
                // Date fields
                $pylon_data[$key] = $value ? sanitize_text_field($value) : null;
            } elseif (strpos($key, '_stars') !== false) {
                // Rating fields (smallint)
                $pylon_data[$key] = is_numeric($value) ? intval($value) : null;
            } else {
                // Text fields (most common)
                // Don't use sanitize_text_field as it strips HTML - just use the value as-is
                // WordPress will handle escaping when inserting
                $pylon_data[$key] = $value;
            }
        }
        
        // Insert the record
        $result = $wpdb->insert($pylons_table, $pylon_data);
        
        if ($result === false) {
            // Log the error for debugging
            error_log('Condor API - Failed to insert pylon: ' . $wpdb->last_error);
            error_log('Condor API - Attempted data: ' . print_r($pylon_data, true));
            
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to create pylon record',
                'error' => $wpdb->last_error,
                'attempted_data' => $pylon_data
            ), 500);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'id' => $wpdb->insert_id,
            'message' => 'Pylon record created successfully',
            'fields_inserted' => count($pylon_data)
        ), 200);
    }
    
    /**
     * Batch import pages with pylons
     * This endpoint handles both post creation and pylon creation
     */
    public function batch_import($request) {
        $params = $request->get_json_params();
        
        if (!isset($params['pages']) || !is_array($params['pages'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'No pages provided'
            ), 400);
        }
        
        // Load the processor class
        require_once plugin_dir_path(dirname(__FILE__)) . 'ruplin-plasma-import-mar/class-ruplin-plasma-import-processor.php';
        
        $processor = new Ruplin_Plasma_Import_Processor();
        
        // Process the import
        $results = $processor->import_pages($params['pages']);
        
        return new WP_REST_Response(array(
            'success' => true,
            'results' => $results
        ), 200);
    }
    
    /**
     * Test connection endpoint
     */
    public function test_connection($request) {
        $current_user = wp_get_current_user();
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Connection successful',
            'user' => array(
                'id' => $current_user->ID,
                'login' => $current_user->user_login,
                'email' => $current_user->user_email,
                'display_name' => $current_user->display_name,
                'capabilities' => $current_user->allcaps
            ),
            'site' => array(
                'name' => get_bloginfo('name'),
                'url' => get_site_url(),
                'wp_version' => get_bloginfo('version')
            )
        ), 200);
    }
    
    /**
     * Get pylons count
     */
    public function get_pylons_count($request) {
        global $wpdb;
        
        $pylons_table = $wpdb->prefix . 'pylons';
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$pylons_table}");
        
        return new WP_REST_Response(array(
            'success' => true,
            'count' => intval($count)
        ), 200);
    }
    
    /**
     * Get columns from pylons table
     */
    private function get_pylons_columns() {
        global $wpdb;
        $pylons_table = $wpdb->prefix . 'pylons';
        $columns = $wpdb->get_col("SHOW COLUMNS FROM `{$pylons_table}`");
        return $columns ? $columns : array();
    }
    
    /**
     * Nuke all content - Delete all pages, posts, and pylons
     */
    public function nuke_content($request) {
        global $wpdb;
        
        // Count items before deletion
        $pages_count = wp_count_posts('page')->publish + wp_count_posts('page')->draft;
        $posts_count = wp_count_posts('post')->publish + wp_count_posts('post')->draft;
        
        // Delete all pages
        $pages = get_posts(array(
            'post_type' => 'page',
            'numberposts' => -1,
            'post_status' => 'any'
        ));
        
        foreach ($pages as $page) {
            wp_delete_post($page->ID, true); // Force delete (bypass trash)
        }
        
        // Delete all posts
        $posts = get_posts(array(
            'post_type' => 'post',
            'numberposts' => -1,
            'post_status' => 'any'
        ));
        
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true); // Force delete (bypass trash)
        }
        
        // Delete all pylons
        $pylons_table = $wpdb->prefix . 'pylons';
        $pylons_deleted = $wpdb->query("DELETE FROM {$pylons_table}");
        
        return new WP_REST_Response(array(
            'success' => true,
            'pages_deleted' => count($pages),
            'posts_deleted' => count($posts),
            'pylons_deleted' => $pylons_deleted,
            'message' => 'All content has been deleted'
        ), 200);
    }
    
    /**
     * Nuke driggs data - Clear all values in wp_zen_sitespren except primary key
     */
    public function nuke_driggs($request) {
        global $wpdb;
        
        $zen_table = $wpdb->prefix . 'zen_sitespren';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$zen_table}'");
        if (!$table_exists) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'wp_zen_sitespren table does not exist'
            ), 404);
        }
        
        // Get all columns except primary key
        $columns = $wpdb->get_col("SHOW COLUMNS FROM `{$zen_table}`");
        $primary_key = 'id'; // Assuming 'id' is the primary key
        
        // Build update query to set all columns to NULL except primary key
        $set_clauses = array();
        foreach ($columns as $column) {
            if ($column !== $primary_key) {
                $set_clauses[] = "`{$column}` = NULL";
            }
        }
        
        if (empty($set_clauses)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'No columns to update'
            ), 400);
        }
        
        // Update the single row (wp_zen_sitespren always has exactly 1 row)
        $sql = "UPDATE {$zen_table} SET " . implode(', ', $set_clauses) . " WHERE {$primary_key} = 1";
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to clear driggs data',
                'error' => $wpdb->last_error
            ), 500);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'All driggs data has been cleared',
            'fields_cleared' => count($set_clauses)
        ), 200);
    }
    
    /**
     * Inject sitespren data from React app into wp_zen_sitespren
     */
    public function inject_sitespren($request) {
        global $wpdb;
        
        $params = $request->get_json_params();
        
        if (!isset($params['sitespren_data']) || !is_array($params['sitespren_data'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'No sitespren data provided'
            ), 400);
        }
        
        $sitespren_data = $params['sitespren_data'];
        $zen_table = $wpdb->prefix . 'zen_sitespren';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$zen_table}'");
        if (!$table_exists) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'wp_zen_sitespren table does not exist'
            ), 404);
        }
        
        // Get columns from wp_zen_sitespren
        $zen_columns = $wpdb->get_col("SHOW COLUMNS FROM `{$zen_table}`");
        $primary_key = 'id';
        
        // First, clear existing data (except primary key)
        $clear_clauses = array();
        foreach ($zen_columns as $column) {
            if ($column !== $primary_key) {
                $clear_clauses[] = "`{$column}` = NULL";
            }
        }
        
        if (!empty($clear_clauses)) {
            $clear_sql = "UPDATE {$zen_table} SET " . implode(', ', $clear_clauses) . " WHERE {$primary_key} = 1";
            $wpdb->query($clear_sql);
        }
        
        // Now inject new data - auto-map matching columns
        $update_data = array();
        $fields_updated = 0;
        
        foreach ($sitespren_data as $key => $value) {
            // Skip if column doesn't exist in wp_zen_sitespren or is primary key
            if (!in_array($key, $zen_columns) || $key === $primary_key) {
                continue;
            }
            
            // Add to update data
            $update_data[$key] = $value;
            $fields_updated++;
        }
        
        if (empty($update_data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'No matching fields found to update'
            ), 400);
        }
        
        // Update the row
        $result = $wpdb->update(
            $zen_table,
            $update_data,
            array($primary_key => 1) // WHERE id = 1
        );
        
        if ($result === false) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to inject sitespren data',
                'error' => $wpdb->last_error
            ), 500);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Sitespren data injected successfully',
            'fields_updated' => $fields_updated
        ), 200);
    }
}

// Initialize the API
new Ruplin_Condor_API();