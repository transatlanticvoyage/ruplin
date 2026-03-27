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
        
        // Create blog and sitemap pages endpoint
        register_rest_route($namespace, '/create-blog-sitemap-pages', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_blog_sitemap_pages'),
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
        
        $params = $request->get_json_params();
        $exclude_blog_sitemap = isset($params['exclude_blog_sitemap']) ? $params['exclude_blog_sitemap'] : true;
        
        // Count items before deletion
        $pages_count = wp_count_posts('page')->publish + wp_count_posts('page')->draft;
        $posts_count = wp_count_posts('post')->publish + wp_count_posts('post')->draft;
        
        // Get excluded page IDs if option is enabled
        $excluded_ids = array();
        if ($exclude_blog_sitemap) {
            // Find blog page
            $blog_page = get_page_by_path('blog');
            if ($blog_page) {
                $excluded_ids[] = $blog_page->ID;
            }
            
            // Find sitemap page
            $sitemap_page = get_page_by_path('sitemap');
            if ($sitemap_page) {
                $excluded_ids[] = $sitemap_page->ID;
            }
        }
        
        // Delete all pages (except excluded)
        $pages = get_posts(array(
            'post_type' => 'page',
            'numberposts' => -1,
            'post_status' => 'any',
            'exclude' => $excluded_ids
        ));
        
        $pages_deleted = 0;
        foreach ($pages as $page) {
            wp_delete_post($page->ID, true); // Force delete (bypass trash)
            $pages_deleted++;
        }
        
        // Delete all posts
        $posts = get_posts(array(
            'post_type' => 'post',
            'numberposts' => -1,
            'post_status' => 'any'
        ));
        
        $posts_deleted = 0;
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true); // Force delete (bypass trash)
            $posts_deleted++;
        }
        
        // Delete all pylons (except those related to excluded pages)
        $pylons_table = $wpdb->prefix . 'pylons';
        if (!empty($excluded_ids)) {
            $excluded_ids_string = implode(',', array_map('intval', $excluded_ids));
            $pylons_deleted = $wpdb->query("DELETE FROM {$pylons_table} WHERE rel_wp_post_id NOT IN ({$excluded_ids_string})");
        } else {
            $pylons_deleted = $wpdb->query("DELETE FROM {$pylons_table}");
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'pages_deleted' => $pages_deleted,
            'posts_deleted' => $posts_deleted,
            'pylons_deleted' => $pylons_deleted,
            'excluded_pages' => count($excluded_ids),
            'message' => $exclude_blog_sitemap ? 'Content deleted (blog and sitemap pages preserved)' : 'All content has been deleted'
        ), 200);
    }
    
    /**
     * Nuke driggs data - Clear all values in wp_zen_sitespren except id and wppma_id
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
        
        // Get all columns except id and wppma_id (both must be preserved)
        $columns = $wpdb->get_col("SHOW COLUMNS FROM `{$zen_table}`");
        $preserved_columns = array('id', 'wppma_id'); // Do not touch these columns
        
        // Build update query to set all columns to NULL except preserved columns
        $set_clauses = array();
        foreach ($columns as $column) {
            if (!in_array($column, $preserved_columns)) {
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
        // Using wppma_id = 1 since that's the standard identifier
        $sql = "UPDATE {$zen_table} SET " . implode(', ', $set_clauses) . " WHERE wppma_id = 1";
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
            'message' => 'All driggs data has been cleared (preserved id and wppma_id)',
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
        $preserved_columns = array('id', 'wppma_id'); // Do not touch these columns
        
        // First, clear existing data (except id and wppma_id)
        $clear_clauses = array();
        foreach ($zen_columns as $column) {
            if (!in_array($column, $preserved_columns)) {
                $clear_clauses[] = "`{$column}` = NULL";
            }
        }
        
        if (!empty($clear_clauses)) {
            // Using wppma_id = 1 for the WHERE clause
            $clear_sql = "UPDATE {$zen_table} SET " . implode(', ', $clear_clauses) . " WHERE wppma_id = 1";
            $wpdb->query($clear_sql);
        }
        
        // Now inject new data - auto-map matching columns
        $update_data = array();
        $fields_updated = 0;
        
        foreach ($sitespren_data as $key => $value) {
            // Skip if column doesn't exist in wp_zen_sitespren or is a preserved column
            if (!in_array($key, $zen_columns) || in_array($key, $preserved_columns)) {
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
        
        // Update the row using wppma_id as the identifier
        $result = $wpdb->update(
            $zen_table,
            $update_data,
            array('wppma_id' => 1) // WHERE wppma_id = 1
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
            'message' => 'Sitespren data injected successfully (preserved id and wppma_id)',
            'fields_updated' => $fields_updated
        ), 200);
    }
    
    /**
     * Create Blog and Sitemap pages with pylons
     */
    public function create_blog_sitemap_pages($request) {
        global $wpdb;
        
        $results = array();
        $pylons_table = $wpdb->prefix . 'pylons';
        
        // Create Blog page
        $blog_page_id = null;
        $existing_blog = get_page_by_path('blog');
        
        if (!$existing_blog) {
            $blog_page_id = wp_insert_post(array(
                'post_title' => 'Blog',
                'post_name' => 'blog',
                'post_content' => '', // Empty content for blog page
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => get_current_user_id()
            ));
            
            if (!is_wp_error($blog_page_id)) {
                // Set as posts page in WordPress settings
                update_option('page_for_posts', $blog_page_id);
                
                // Create pylon record for blog page
                $wpdb->insert($pylons_table, array(
                    'rel_wp_post_id' => $blog_page_id,
                    'pylon_archetype' => 'blogpage',
                    'staircase_page_template_desired' => 'bilberry'
                ));
                
                $results['blog'] = array(
                    'success' => true,
                    'page_id' => $blog_page_id,
                    'message' => 'Blog page created successfully'
                );
            } else {
                $results['blog'] = array(
                    'success' => false,
                    'message' => 'Failed to create blog page: ' . $blog_page_id->get_error_message()
                );
            }
        } else {
            $results['blog'] = array(
                'success' => false,
                'message' => 'Blog page already exists',
                'page_id' => $existing_blog->ID
            );
        }
        
        // Create Sitemap page
        $sitemap_page_id = null;
        $existing_sitemap = get_page_by_path('sitemap');
        
        if (!$existing_sitemap) {
            // Sitemap content with shortcode
            $sitemap_content = '[ruplin_sitemap_method_3
      zservice_anchor="post_title"
      zneighborhood_anchor="post_title"
      zcity_anchor="city"
      zabout_anchor="post_title"
      zcontact_anchor="post_title"
      zcustom_anchors=\'{"zdowntown-jersey-city":"Downtown","zabout":"Our Story","zcontact":"Get In Touch","zplumbing":"Expert Plumbing","zblog/latest-news":"Recent Updates","zprivacy-policy":"Privacy"}\']';
            
            $sitemap_page_id = wp_insert_post(array(
                'post_title' => 'Sitemap',
                'post_name' => 'sitemap',
                'post_content' => $sitemap_content,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => get_current_user_id()
            ));
            
            if (!is_wp_error($sitemap_page_id)) {
                // Create pylon record for sitemap page
                $wpdb->insert($pylons_table, array(
                    'rel_wp_post_id' => $sitemap_page_id,
                    'pylon_archetype' => 'sitemappage',
                    'staircase_page_template_desired' => 'bilberry'
                ));
                
                $results['sitemap'] = array(
                    'success' => true,
                    'page_id' => $sitemap_page_id,
                    'message' => 'Sitemap page created successfully'
                );
            } else {
                $results['sitemap'] = array(
                    'success' => false,
                    'message' => 'Failed to create sitemap page: ' . $sitemap_page_id->get_error_message()
                );
            }
        } else {
            $results['sitemap'] = array(
                'success' => false,
                'message' => 'Sitemap page already exists',
                'page_id' => $existing_sitemap->ID
            );
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'results' => $results
        ), 200);
    }
}

// Initialize the API
new Ruplin_Condor_API();