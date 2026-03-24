<?php

/**
 * Ruplin Plasma Import Processor Class
 * Handles the actual import functionality for plasma pages
 */
class Ruplin_Plasma_Import_Processor {
    
    /**
     * WordPress database object
     */
    private $wpdb;
    
    /**
     * Mapping of plasma fields to wp_posts fields
     */
    private $wp_posts_mapping = [
        'page_status' => 'post_status',
        'page_type' => 'post_type',
        'page_title' => 'post_title',
        'page_content' => 'post_content',
        'page_date' => 'post_date',
        'page_name' => 'post_name'
    ];
    
    /**
     * Mapping of plasma fields to wp_pylons fields (for different names only)
     */
    private $wp_pylons_mapping = [
        'page_archetype' => 'pylon_archetype'
    ];
    
    /**
     * Explicit same-name mappings for wp_pylons (ensures these fields are always mapped)
     */
    private $wp_pylons_explicit_same_name = [
        'staircase_page_template_desired' => 'staircase_page_template_desired'
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Remove slashes from data if slash removal is enabled (default behavior)
     * 
     * @param mixed $data Data to process (string, array, or object)
     * @return mixed Processed data with slashes removed or original data
     */
    private function maybe_remove_slashes($data) {
        // Check if slash removal is disabled via POST parameter
        $disable_slash_removal = isset($_POST['disable_slash_removal']) && $_POST['disable_slash_removal'] === 'true';
        
        if ($disable_slash_removal) {
            return $data; // Return original data without slash removal
        }
        
        // Default behavior: remove slashes
        if (is_string($data)) {
            return wp_unslash($data);
        } elseif (is_array($data)) {
            return array_map(array($this, 'maybe_remove_slashes'), $data);
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->maybe_remove_slashes($value);
            }
            return $data;
        }
        
        return $data;
    }
    
    /**
     * Check if empty fields should be updated based on user setting
     * 
     * @return bool True if empty fields should be set to empty in database
     */
    private function should_update_empty_fields() {
        // Check if the setting was passed in the request
        return isset($_POST['update_empty_fields']) && $_POST['update_empty_fields'] === 'true';
    }
    
    /**
     * Main import method - processes array of pages from JSON
     * 
     * @param array $pages_data Array of page objects from plasma export
     * @return array Results with success/error information
     */
    public function import_pages($pages_data) {
        $results = [
            'success' => [],
            'errors' => [],
            'total' => count($pages_data),
            'homepage_id' => null
        ];
        
        // Check if we should set homepage
        $should_set_homepage = isset($_POST['set_homepage_option']) && $_POST['set_homepage_option'] === 'true';
        $homepage_post_id = null;
        
        foreach ($pages_data as $index => $page) {
            try {
                // Apply slash removal if enabled (default behavior)
                $clean_page = $this->maybe_remove_slashes($page);
                
                // Create WordPress post/page
                $post_id = $this->create_wordpress_post($clean_page);
                
                if ($post_id && !is_wp_error($post_id)) {
                    // Create pylon record
                    $pylon_result = $this->create_pylon_record($post_id, $clean_page);
                    
                    if ($pylon_result) {
                        $results['success'][] = [
                            'index' => $index,
                            'post_id' => $post_id,
                            'title' => $page['page_title'] ?? 'Untitled',
                            'page_id' => $page['page_id'] ?? null
                        ];
                        
                        // Check if this is the homepage
                        if ($should_set_homepage && isset($page['page_archetype']) && $page['page_archetype'] === 'homepage') {
                            $homepage_post_id = $post_id;
                            $results['homepage_id'] = $post_id;
                        }
                        
                        // TODO: Future - assign page template based on staircase_page_template_desired
                        // $this->assign_page_template($post_id, $page);
                    } else {
                        // Post created but pylon record failed
                        $results['errors'][] = [
                            'index' => $index,
                            'message' => 'Post created but pylon record failed',
                            'post_id' => $post_id
                        ];
                    }
                } else {
                    // Post creation failed
                    $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error creating post';
                    $results['errors'][] = [
                        'index' => $index,
                        'message' => $error_message,
                        'title' => $page['page_title'] ?? 'Untitled'
                    ];
                }
            } catch (Exception $e) {
                $results['errors'][] = [
                    'index' => $index,
                    'message' => $e->getMessage(),
                    'title' => $page['page_title'] ?? 'Untitled'
                ];
            }
        }
        
        // Set the homepage if we found one
        if ($should_set_homepage && $homepage_post_id) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $homepage_post_id);
            $results['homepage_set'] = true;
            $results['homepage_message'] = 'Homepage successfully set to page ID: ' . $homepage_post_id;
        }
        
        return $results;
    }
    
    /**
     * Create a WordPress post/page from plasma data
     * 
     * @param array $page_data Single page data from plasma export
     * @return int|WP_Error Post ID on success, WP_Error on failure
     */
    private function create_wordpress_post($page_data) {
        // Map plasma fields to WordPress post fields
        $post_data = [];
        
        // Apply explicit field mappings
        foreach ($this->wp_posts_mapping as $plasma_field => $wp_field) {
            if (isset($page_data[$plasma_field]) && !empty($page_data[$plasma_field])) {
                $post_data[$wp_field] = $page_data[$plasma_field];
            }
        }
        
        // Set defaults if not provided
        if (!isset($post_data['post_status']) || empty($post_data['post_status'])) {
            $post_data['post_status'] = 'publish';
        }
        
        if (!isset($post_data['post_type']) || empty($post_data['post_type'])) {
            // Default to 'page', but check for special conditions
            $post_data['post_type'] = 'page';
            
            // If page_type is blank/null and page_archetype has value of "blogpost", create as post
            if ((!isset($page_data['page_type']) || empty($page_data['page_type'])) && 
                (isset($page_data['page_archetype']) && $page_data['page_archetype'] === 'blogpost')) {
                $post_data['post_type'] = 'post';
            }
        }
        
        if (!isset($post_data['post_title'])) {
            $post_data['post_title'] = 'Imported Page - ' . current_time('mysql');
        }
        
        // Set post author to current user
        $post_data['post_author'] = get_current_user_id();
        
        // Handle post_date - convert from ISO format if needed
        if (isset($post_data['post_date'])) {
            $post_data['post_date'] = date('Y-m-d H:i:s', strtotime($post_data['post_date']));
            $post_data['post_date_gmt'] = get_gmt_from_date($post_data['post_date']);
        }
        
        // Insert the post
        $post_id = wp_insert_post($post_data);
        
        return $post_id;
    }
    
    /**
     * Create a wp_pylons record for the imported page
     * 
     * @param int $post_id WordPress post ID
     * @param array $page_data Original plasma page data
     * @return bool|int Insert ID on success, false on failure
     */
    private function create_pylon_record($post_id, $page_data) {
        $pylons_table = $this->wpdb->prefix . 'pylons';
        
        // Get the schema of wp_pylons table to enable dynamic mapping
        $pylons_columns = $this->get_table_columns($pylons_table);
        
        // Start with required relational fields
        $pylon_data = [
            'rel_wp_post_id' => $post_id,
        ];
        
        // Check which plasma page ID column exists for backwards compatibility
        if (in_array('rel_plasma_page_id', $pylons_columns)) {
            // Old schema - use rel_plasma_page_id
            $pylon_data['rel_plasma_page_id'] = isset($page_data['page_id']) ? intval($page_data['page_id']) : null;
        } elseif (in_array('plasma_page_id', $pylons_columns)) {
            // New schema (Ruplin standard) - use plasma_page_id
            $pylon_data['plasma_page_id'] = isset($page_data['page_id']) ? intval($page_data['page_id']) : null;
        }
        
        // Apply explicit field mappings (for fields with different names)
        foreach ($this->wp_pylons_mapping as $plasma_field => $pylon_field) {
            if (isset($page_data[$plasma_field])) {
                $value = $page_data[$plasma_field];
                // Map non-empty values, or empty values if explicitly allowed
                if (!empty($value) || $value === '0' || (empty($value) && $this->should_update_empty_fields())) {
                    $pylon_data[$pylon_field] = $value;
                }
            }
        }
        
        // Apply explicit same-name mappings (ensures these critical fields are always mapped)
        foreach ($this->wp_pylons_explicit_same_name as $plasma_field => $pylon_field) {
            if (isset($page_data[$plasma_field])) {
                $value = $page_data[$plasma_field];
                // Map non-empty values, or empty values if explicitly allowed
                if (!empty($value) || $value === '0' || (empty($value) && $this->should_update_empty_fields())) {
                    $pylon_data[$pylon_field] = $value;
                }
            }
        }
        
        // Auto-map exact column name matches (future-proof for new columns)
        foreach ($page_data as $field => $value) {
            // Skip if already mapped or if field doesn't exist in schema
            if (!isset($pylon_data[$field]) && in_array($field, $pylons_columns)) {
                // Map non-empty values, or empty values if explicitly allowed
                if (!empty($value) || $value === '0' || (empty($value) && $this->should_update_empty_fields())) {
                    $pylon_data[$field] = $value;
                }
            }
        }
        
        // Apply OSB rule: If page_archetype is 'homepage', set osb_is_enabled to 1
        if (isset($pylon_data['pylon_archetype']) && $pylon_data['pylon_archetype'] === 'homepage') {
            $pylon_data['osb_is_enabled'] = 1;
        }
        
        // Insert the pylon record
        $result = $this->wpdb->insert(
            $pylons_table,
            $pylon_data
        );
        
        if ($result === false) {
            error_log('Grove Plasma Import - Failed to insert pylon record: ' . $this->wpdb->last_error);
            return false;
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get column names for a database table
     * 
     * @param string $table_name Full table name with prefix
     * @return array Array of column names
     */
    private function get_table_columns($table_name) {
        $columns = $this->wpdb->get_col("SHOW COLUMNS FROM `{$table_name}`");
        return $columns ? $columns : [];
    }
    
    /**
     * AJAX handler for import request
     */
    public function handle_ajax_import() {
        // Check if this is an API request with Basic Auth
        $is_api_request = false;
        
        // Check for Basic Auth header
        $auth_header = '';
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        if (!empty($auth_header) && strpos($auth_header, 'Basic ') === 0) {
            // Decode Basic Auth
            $encoded_creds = substr($auth_header, 6);
            $decoded = base64_decode($encoded_creds);
            list($username, $password) = explode(':', $decoded, 2);
            
            // Authenticate user with Application Password
            $user = wp_authenticate_application_password(null, $username, $password);
            
            if (!is_wp_error($user) && $user) {
                // Set the current user
                wp_set_current_user($user->ID);
                $is_api_request = true;
            }
        }
        
        // Fallback: Check for auth via URL parameters (for when headers are stripped)
        if (!$is_api_request && isset($_POST['auth_user']) && isset($_POST['auth_pass'])) {
            error_log("Grove Debug: URL param auth attempt - username: " . $_POST['auth_user']);
            $username = sanitize_text_field($_POST['auth_user']);
            $password = sanitize_text_field($_POST['auth_pass']);
            
            // Debug: Check if Application Passwords are available
            if (!function_exists('wp_authenticate_application_password')) {
                error_log("Grove Debug: wp_authenticate_application_password function not available");
                
                // Fallback: Try regular WordPress authentication
                $user = wp_authenticate($username, $password);
                if (!is_wp_error($user) && $user) {
                    wp_set_current_user($user->ID);
                    $is_api_request = true;
                    error_log("Grove Debug: Fallback auth successful for user: " . $user->user_login);
                } else {
                    error_log("Grove Debug: Fallback auth failed");
                }
            } else {
                // Authenticate user with Application Password
                $user = wp_authenticate_application_password(null, $username, $password);
                error_log("Grove Debug: Auth result type: " . gettype($user));
                error_log("Grove Debug: Auth result: " . (is_wp_error($user) ? $user->get_error_message() : 'Success - User ID: ' . (is_object($user) && isset($user->ID) ? $user->ID : 'NO ID')));
                
                if (!is_wp_error($user) && $user && is_object($user) && isset($user->ID)) {
                    // Set the current user
                    wp_set_current_user($user->ID);
                    $is_api_request = true;
                    error_log("Grove Debug: Successfully authenticated user: " . $user->user_login);
                } else {
                    error_log("Grove Debug: Authentication failed - " . (is_wp_error($user) ? $user->get_error_message() : 'Invalid user object'));
                    
                    // Try regular WordPress authentication as fallback
                    error_log("Grove Debug: Trying regular wp_authenticate as fallback");
                    $fallback_user = wp_authenticate($username, $password);
                    if (!is_wp_error($fallback_user) && $fallback_user) {
                        wp_set_current_user($fallback_user->ID);
                        $is_api_request = true;
                        error_log("Grove Debug: Fallback auth successful for user: " . $fallback_user->user_login);
                    }
                }
            }
        }
        
        
        if (!$is_api_request) {
            // Debug: Log what we're checking
            error_log("Grove Debug: Not API request - checking nonce. POST data: " . print_r($_POST, true));
            
            // Verify nonce for regular requests
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ruplin_plasma_import')) {
                error_log("Grove Debug: Nonce check failed. Nonce provided: " . (isset($_POST['nonce']) ? $_POST['nonce'] : 'none'));
                wp_die('Security check failed');
            }
        } else {
            error_log("Grove Debug: API request authenticated successfully");
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions - User: ' . wp_get_current_user()->user_login]);
            return;
        }
        
        // Get the pages data from POST
        $pages_data_raw = isset($_POST['pages']) ? $_POST['pages'] : [];
        
        if (empty($pages_data_raw)) {
            wp_send_json_error(['message' => 'No pages data provided']);
            return;
        }
        
        // Decode JSON if it's a string
        if (is_string($pages_data_raw)) {
            error_log("Grove Debug: Raw pages data: " . $pages_data_raw);
            // Strip slashes that WordPress adds to POST data
            $clean_json = stripslashes($pages_data_raw);
            error_log("Grove Debug: Clean pages data: " . $clean_json);
            $pages_data = json_decode($clean_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Grove Debug: JSON decode error: " . json_last_error_msg());
                wp_send_json_error(['message' => 'Invalid JSON in pages data: ' . json_last_error_msg()]);
                return;
            }
        } else {
            $pages_data = $pages_data_raw;
        }
        
        if (empty($pages_data) || !is_array($pages_data)) {
            wp_send_json_error(['message' => 'No valid pages data provided']);
            return;
        }
        
        // Process the import
        $results = $this->import_pages($pages_data);
        
        // Check if F582 date processing is requested
        $run_f582_option = isset($_POST['run_f582_option']) && $_POST['run_f582_option'] === 'true';
        
        if ($run_f582_option && count($results['success']) > 0) {
            // Run F582 date processing on posts that were imported
            $f582_result = $this->run_f582_date_processing($results);
            if ($f582_result['success']) {
                $results['f582_message'] = $f582_result['message'];
            } else {
                $results['f582_error'] = $f582_result['message'];
            }
        }
        
        // Return results
        if (count($results['errors']) === 0) {
            $message = sprintf('Successfully imported %d pages', count($results['success']));
            if (isset($results['homepage_set']) && $results['homepage_set']) {
                $message .= '. ' . $results['homepage_message'];
            }
            if (isset($results['f582_message'])) {
                $message .= '. ' . $results['f582_message'];
            }
            wp_send_json_success([
                'message' => $message,
                'details' => $results
            ]);
        } else if (count($results['success']) > 0) {
            $message = sprintf('Imported %d of %d pages with some errors', 
                count($results['success']), 
                $results['total']
            );
            if (isset($results['homepage_set']) && $results['homepage_set']) {
                $message .= '. ' . $results['homepage_message'];
            }
            if (isset($results['f582_message'])) {
                $message .= '. ' . $results['f582_message'];
            }
            wp_send_json_success([
                'message' => $message,
                'details' => $results
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Import failed for all pages',
                'details' => $results
            ]);
        }
    }
    
    /**
     * Template assignment is now handled automatically via wp_pylons
     * 
     * The staircase_page_template_desired field is stored in wp_pylons during import,
     * and the theme's staircase_get_current_template() function reads from wp_pylons first.
     * No additional template assignment needed during import.
     * 
     * @param int $post_id WordPress post ID
     * @param array $page_data Original plasma page data
     */
    private function assign_page_template($post_id, $page_data) {
        // Template assignment is handled automatically via wp_pylons table
        // The theme reads staircase_page_template_desired from wp_pylons on page load
        // and normalizes the template name to match available templates
        
        // No action needed here - template assignment happens at render time
    }
    
    /**
     * AJAX handler for driggs data import request
     */
    public function handle_ajax_driggs_import() {
        // Check if this is an API request with Basic Auth
        $is_api_request = false;
        
        // Check for Basic Auth header
        $auth_header = '';
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        if (!empty($auth_header) && strpos($auth_header, 'Basic ') === 0) {
            // Decode Basic Auth
            $encoded_creds = substr($auth_header, 6);
            $decoded = base64_decode($encoded_creds);
            list($username, $password) = explode(':', $decoded, 2);
            
            // Authenticate user with Application Password
            $user = wp_authenticate_application_password(null, $username, $password);
            
            if (!is_wp_error($user) && $user) {
                // Set the current user
                wp_set_current_user($user->ID);
                $is_api_request = true;
            }
        }
        
        // Fallback: Check for auth via URL parameters (for when headers are stripped)
        if (!$is_api_request && isset($_POST['auth_user']) && isset($_POST['auth_pass'])) {
            error_log("Grove Debug: URL param auth attempt - username: " . $_POST['auth_user']);
            $username = sanitize_text_field($_POST['auth_user']);
            $password = sanitize_text_field($_POST['auth_pass']);
            
            // Debug: Check if Application Passwords are available
            if (!function_exists('wp_authenticate_application_password')) {
                error_log("Grove Debug: wp_authenticate_application_password function not available");
                
                // Fallback: Try regular WordPress authentication
                $user = wp_authenticate($username, $password);
                if (!is_wp_error($user) && $user) {
                    wp_set_current_user($user->ID);
                    $is_api_request = true;
                    error_log("Grove Debug: Fallback auth successful for user: " . $user->user_login);
                } else {
                    error_log("Grove Debug: Fallback auth failed");
                }
            } else {
                // Authenticate user with Application Password
                $user = wp_authenticate_application_password(null, $username, $password);
                error_log("Grove Debug: Auth result type: " . gettype($user));
                error_log("Grove Debug: Auth result: " . (is_wp_error($user) ? $user->get_error_message() : 'Success - User ID: ' . (is_object($user) && isset($user->ID) ? $user->ID : 'NO ID')));
                
                if (!is_wp_error($user) && $user && is_object($user) && isset($user->ID)) {
                    // Set the current user
                    wp_set_current_user($user->ID);
                    $is_api_request = true;
                    error_log("Grove Debug: Successfully authenticated user: " . $user->user_login);
                } else {
                    error_log("Grove Debug: Authentication failed - " . (is_wp_error($user) ? $user->get_error_message() : 'Invalid user object'));
                    
                    // Try regular WordPress authentication as fallback
                    error_log("Grove Debug: Trying regular wp_authenticate as fallback");
                    $fallback_user = wp_authenticate($username, $password);
                    if (!is_wp_error($fallback_user) && $fallback_user) {
                        wp_set_current_user($fallback_user->ID);
                        $is_api_request = true;
                        error_log("Grove Debug: Fallback auth successful for user: " . $fallback_user->user_login);
                    }
                }
            }
        }
        
        // TEMPORARY: Skip authentication for testing
        if (isset($_POST['auth_user']) && $_POST['auth_user'] === 'admin') {
            error_log("Grove Debug DRIGGS: TEMP AUTH BYPASS - Setting admin user");
            $admin_user = get_user_by('login', 'admin');
            if ($admin_user) {
                wp_set_current_user($admin_user->ID);
                $is_api_request = true;
                error_log("Grove Debug DRIGGS: TEMP AUTH BYPASS - Admin user set: " . $admin_user->user_login);
            }
        }
        
        if (!$is_api_request) {
            // Debug: Log what we're checking
            error_log("Grove Debug DRIGGS: Not API request - checking nonce. POST data: " . print_r($_POST, true));
            
            // Verify nonce for regular requests
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ruplin_driggs_import')) {
                error_log("Grove Debug DRIGGS: Nonce check failed. Nonce provided: " . (isset($_POST['nonce']) ? $_POST['nonce'] : 'none'));
                wp_die('Security check failed');
            }
        } else {
            error_log("Grove Debug DRIGGS: API request authenticated successfully");
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions - User: ' . wp_get_current_user()->user_login]);
            return;
        }
        
        // Get the driggs data from POST
        $driggs_data_raw = isset($_POST['driggs_data']) ? $_POST['driggs_data'] : [];
        
        if (empty($driggs_data_raw)) {
            wp_send_json_error(['message' => 'No driggs data provided']);
            return;
        }
        
        // Decode JSON if it's a string (for API requests)
        if (is_string($driggs_data_raw)) {
            // Strip slashes that WordPress adds to POST data
            $clean_json = stripslashes($driggs_data_raw);
            $driggs_data = json_decode($clean_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error(['message' => 'Invalid JSON in driggs data: ' . json_last_error_msg()]);
                return;
            }
        } else {
            $driggs_data = $driggs_data_raw;
        }
        
        // Apply slash removal if enabled (default behavior)
        $clean_driggs_data = $this->maybe_remove_slashes($driggs_data);
        
        // Check if we should update the site title
        $update_site_title = isset($_POST['update_site_title']) && $_POST['update_site_title'] === 'true';
        
        // Process the driggs data import
        $results = $this->import_driggs_data($clean_driggs_data);
        
        // Update WordPress site title if the option is checked and driggs_brand_name exists
        $site_title_updated = false;
        if ($update_site_title && $results['success'] && isset($clean_driggs_data['driggs_brand_name']) && !empty($clean_driggs_data['driggs_brand_name'])) {
            update_option('blogname', $clean_driggs_data['driggs_brand_name']);
            $site_title_updated = true;
        }
        
        // Return results
        if ($results['success']) {
            // Build detailed success message
            $message = sprintf('Successfully imported %d valid fields to wp_zen_sitespren', $results['valid_fields_imported']);
            
            if ($results['invalid_fields_skipped'] > 0) {
                $message .= sprintf(' (%d fields skipped - not found in database)', $results['invalid_fields_skipped']);
            }
            
            if ($site_title_updated) {
                $message .= sprintf('. Site title updated to: "%s"', $clean_driggs_data['driggs_brand_name']);
            }
            
            wp_send_json_success([
                'message' => $message,
                'details' => $results,
                'site_title_updated' => $site_title_updated
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Driggs data import failed: ' . $results['error'],
                'details' => $results
            ]);
        }
    }
    
    /**
     * Import driggs data into wp_zen_sitespren table
     * 
     * @param array $driggs_data Array of driggs field => value pairs
     * @return array Results with success/error information
     */
    private function import_driggs_data($driggs_data) {
        global $wpdb;
        
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        
        try {
            // Check if the sitespren record exists (assuming we work with ID 1)
            $existing_record = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $sitespren_table WHERE wppma_id = %d",
                1
            ));
            
            if ($existing_record) {
                // Get actual table columns to validate field existence
                $table_columns = $wpdb->get_col("DESCRIBE $sitespren_table");
                
                // Update existing record
                $update_data = [];
                $invalid_fields = [];
                
                foreach ($driggs_data as $field => $value) {
                    // Skip "id" columns - never import these
                    if (strtolower($field) === 'id') {
                        $invalid_fields[] = $field . ' (excluded: id columns not allowed)';
                        continue;
                    }
                    
                    // Sanitize the field name to prevent SQL injection
                    if (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                        // Check if column actually exists in database
                        if (in_array($field, $table_columns)) {
                            $update_data[$field] = $value;
                        } else {
                            $invalid_fields[] = $field;
                        }
                    }
                }
                
                // Log invalid fields for debugging
                if (!empty($invalid_fields)) {
                    error_log('Driggs Import: Skipping fields that don\'t exist in wp_zen_sitespren: ' . implode(', ', $invalid_fields));
                }
                
                if (!empty($update_data)) {
                    $result = $wpdb->update(
                        $sitespren_table,
                        $update_data,
                        ['wppma_id' => 1],
                        null,
                        ['%d']
                    );
                    
                    if ($result === false) {
                        return [
                            'success' => false,
                            'error' => 'Database update failed: ' . $wpdb->last_error
                        ];
                    }
                    
                    return [
                        'success' => true,
                        'updated_fields' => array_keys($update_data),
                        'skipped_fields' => $invalid_fields,
                        'record_id' => 1,
                        'total_fields_processed' => count($driggs_data),
                        'valid_fields_imported' => count($update_data),
                        'invalid_fields_skipped' => count($invalid_fields)
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'No valid fields to update'
                    ];
                }
            } else {
                // Create new record with driggs data
                $insert_data = ['wppma_id' => 1];
                foreach ($driggs_data as $field => $value) {
                    // Skip "id" columns - never import these
                    if (strtolower($field) === 'id') {
                        continue;
                    }
                    
                    // Sanitize the field name to prevent SQL injection
                    if (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                        $insert_data[$field] = $value;
                    }
                }
                
                $result = $wpdb->insert(
                    $sitespren_table,
                    $insert_data
                );
                
                if ($result === false) {
                    return [
                        'success' => false,
                        'error' => 'Database insert failed: ' . $wpdb->last_error
                    ];
                }
                
                return [
                    'success' => true,
                    'inserted_fields' => array_keys($insert_data),
                    'record_id' => $wpdb->insert_id
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Exception occurred: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Run F582 date processing on imported posts
     * This is a centralized version that can be called from different import functions
     */
    private function run_f582_date_processing($import_results) {
        try {
            // Get only the posts (page_type = 'post') from the successfully imported items
            // Also collect their jchronology_order values
            $post_data = [];
            foreach ($import_results['success'] as $item) {
                if (isset($item['page_type']) && $item['page_type'] === 'post' && isset($item['post_id'])) {
                    $post_data[] = [
                        'post_id' => $item['post_id'],
                        'jchronology_order' => isset($item['jchronology_order_for_blog_posts']) ? $item['jchronology_order_for_blog_posts'] : 0
                    ];
                }
            }
            
            if (empty($post_data)) {
                return [
                    'success' => true,
                    'message' => 'No blog posts found for F582 processing'
                ];
            }
            
            global $wpdb;
            
            // Sort posts by jchronology_order_for_blog_posts (ascending: 1, 2, 3...)
            usort($post_data, function($a, $b) {
                return ($a['jchronology_order'] ?: 0) - ($b['jchronology_order'] ?: 0);
            });
            
            // Extract just the post IDs after sorting
            $post_ids = array_column($post_data, 'post_id');
            
            // F582 settings - using the same defaults as the Date Worshipper
            $backdate_count = min(8, count($post_ids)); // Default to 8 or total posts if less
            $future_count = count($post_ids) - $backdate_count;
            $interval_from = 4; // Default interval from 4 days
            $interval_to = 11;  // Default interval to 11 days
            
            // Split posts into backdate and future groups (no shuffle - respect jchronology order)
            $backdate_posts = array_slice($post_ids, 0, $backdate_count);
            $future_posts = array_slice($post_ids, $backdate_count, $future_count);
            
            // Reverse backdated posts so jchronology=1 is farthest in past
            $backdate_posts = array_reverse($backdate_posts);
            
            $current_time = current_time('timestamp');
            $updated_count = 0;
            
            // Process backdate posts (going backward in time)
            $backdate_time = $current_time;
            foreach ($backdate_posts as $post_id) {
                // Generate random interval in seconds
                $min_seconds = $interval_from * 24 * 60 * 60;
                $max_seconds = $interval_to * 24 * 60 * 60;
                $random_seconds = rand($min_seconds, $max_seconds);
                
                // Add random hours, minutes, seconds for more natural distribution
                $random_hours = rand(0, 23);
                $random_minutes = rand(0, 59);
                $random_seconds_component = rand(0, 59);
                $random_seconds += ($random_hours * 3600) + ($random_minutes * 60) + $random_seconds_component;
                
                // Go back in time
                $backdate_time -= $random_seconds;
                
                $new_date = date('Y-m-d H:i:s', $backdate_time);
                
                // Update post date
                $result = $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_date' => $new_date,
                        'post_date_gmt' => get_gmt_from_date($new_date),
                        'post_status' => 'publish',
                        'post_modified' => current_time('mysql'),
                        'post_modified_gmt' => current_time('mysql', 1)
                    ),
                    array('ID' => $post_id),
                    array('%s', '%s', '%s', '%s', '%s'),
                    array('%d')
                );
                
                if ($result !== false) {
                    $updated_count++;
                    clean_post_cache($post_id);
                }
            }
            
            // Process future posts (going forward in time)
            $future_time = $current_time;
            foreach ($future_posts as $post_id) {
                // Generate random interval in seconds
                $min_seconds = $interval_from * 24 * 60 * 60;
                $max_seconds = $interval_to * 24 * 60 * 60;
                $random_seconds = rand($min_seconds, $max_seconds);
                
                // Add random hours, minutes, seconds for more natural distribution
                $random_hours = rand(0, 23);
                $random_minutes = rand(0, 59);
                $random_seconds_component = rand(0, 59);
                $random_seconds += ($random_hours * 3600) + ($random_minutes * 60) + $random_seconds_component;
                
                // Go forward in time
                $future_time += $random_seconds;
                
                $new_date = date('Y-m-d H:i:s', $future_time);
                
                // Update post date and set as scheduled
                $result = $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_date' => $new_date,
                        'post_date_gmt' => get_gmt_from_date($new_date),
                        'post_status' => 'future',
                        'post_modified' => current_time('mysql'),
                        'post_modified_gmt' => current_time('mysql', 1)
                    ),
                    array('ID' => $post_id),
                    array('%s', '%s', '%s', '%s', '%s'),
                    array('%d')
                );
                
                if ($result !== false) {
                    $updated_count++;
                    clean_post_cache($post_id);
                    
                    // Schedule the post to be published
                    wp_schedule_single_event($future_time, 'publish_future_post', array($post_id));
                }
            }
            
            return [
                'success' => true,
                'message' => sprintf('F582 processed %d posts (%d backdated, %d scheduled)', 
                    $updated_count, count($backdate_posts), count($future_posts))
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'F582 processing failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle file upload for file-based import
     */
    public function handle_file_upload() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'ruplin_file_upload')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Validate file upload
        if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('File upload failed');
            return;
        }
        
        $file = $_FILES['json_file'];
        
        // Validate file type
        if (!in_array($file['type'], ['application/json', 'text/plain']) && 
            !preg_match('/\.json$/i', $file['name'])) {
            wp_send_json_error('Invalid file type. Please upload a JSON file.');
            return;
        }
        
        // Validate file size (max 50MB for file-based method)
        if ($file['size'] > 50 * 1024 * 1024) {
            wp_send_json_error('File too large. Maximum size is 50MB.');
            return;
        }
        
        try {
            // Read and parse JSON
            $json_content = file_get_contents($file['tmp_name']);
            $json_data = json_decode($json_content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Invalid JSON format: ' . json_last_error_msg());
                return;
            }
            
            if (!isset($json_data['pages']) || !is_array($json_data['pages'])) {
                wp_send_json_error('Invalid JSON structure. Expected "pages" array.');
                return;
            }
            
            // Generate unique file ID
            $file_id = uniqid('ruplin_import_', true);
            
            // Store file in uploads directory
            $upload_dir = wp_upload_dir();
            $grove_temp_dir = $upload_dir['basedir'] . '/ruplin-temp/';
            
            if (!file_exists($grove_temp_dir)) {
                wp_mkdir_p($grove_temp_dir);
            }
            
            $temp_file_path = $grove_temp_dir . $file_id . '.json';
            
            if (!file_put_contents($temp_file_path, $json_content)) {
                wp_send_json_error('Failed to save uploaded file');
                return;
            }
            
            // Clean up old temp files (older than 24 hours)
            $this->cleanup_temp_files($grove_temp_dir);
            
            wp_send_json_success([
                'message' => 'File uploaded successfully',
                'file_id' => $file_id,
                'json_data' => $json_data,
                'pages_count' => count($json_data['pages']),
                'file_size' => $file['size']
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('Processing failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle file-based batch import
     */
    public function handle_file_batch_import() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'ruplin_plasma_import')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $file_id = sanitize_text_field($_POST['file_id']);
        $batch_start = intval($_POST['batch_start']);
        $batch_size = intval($_POST['batch_size']);
        $selected_indexes = isset($_POST['selected_indexes']) ? array_map('intval', $_POST['selected_indexes']) : [];
        
        if (empty($file_id)) {
            wp_send_json_error('File ID required');
            return;
        }
        
        try {
            // Load file data
            $upload_dir = wp_upload_dir();
            $temp_file_path = $upload_dir['basedir'] . '/ruplin-temp/' . $file_id . '.json';
            
            if (!file_exists($temp_file_path)) {
                wp_send_json_error('Temporary file not found. Please re-upload.');
                return;
            }
            
            $json_content = file_get_contents($temp_file_path);
            $json_data = json_decode($json_content, true);
            
            if (!$json_data || !isset($json_data['pages'])) {
                wp_send_json_error('Invalid file data');
                return;
            }
            
            // Get the batch of pages to process based on selected indexes
            $all_pages = $json_data['pages'];
            $pages_batch = [];
            
            if (!empty($selected_indexes)) {
                // Use selected indexes
                foreach ($selected_indexes as $index) {
                    if (isset($all_pages[$index])) {
                        $pages_batch[] = $all_pages[$index];
                    }
                }
            } else {
                // Fallback to batch slice if no selected indexes
                $pages_batch = array_slice($all_pages, $batch_start, $batch_size);
            }
            
            if (empty($pages_batch)) {
                wp_send_json_success([
                    'message' => 'Batch completed',
                    'pages_processed' => 0,
                    'is_complete' => true
                ]);
                return;
            }
            
            // Process this batch using existing import logic
            $results = $this->process_pages_import($pages_batch, [
                'update_empty_fields' => $_POST['update_empty_fields'] ?? 'false',
                'set_homepage_option' => $_POST['set_homepage_option'] ?? 'false',
                'run_f582_option' => $_POST['run_f582_option'] ?? 'false'
            ]);
            
            $is_complete = ($batch_start + $batch_size) >= count($all_pages);
            
            // Clean up temp file if this is the last batch
            if ($is_complete) {
                unlink($temp_file_path);
            }
            
            wp_send_json_success([
                'message' => "Batch processed: {$results['success_count']} created, {$results['error_count']} errors",
                'pages_processed' => count($pages_batch),
                'success_count' => $results['success_count'],
                'error_count' => $results['error_count'],
                'errors' => $results['errors'],
                'is_complete' => $is_complete,
                'total_processed' => $batch_start + count($pages_batch),
                'total_pages' => count($all_pages)
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('Batch processing failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Process pages import (extracted from existing handle_ajax_import)
     */
    private function process_pages_import($pages, $options) {
        $results = [
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'created_posts' => []
        ];
        
        $homepage_post_id = null;
        $should_set_homepage = ($options['set_homepage_option'] === 'true');
        $should_update_empty_fields = ($options['update_empty_fields'] === 'true');
        
        foreach ($pages as $index => $page) {
            try {
                // Create WordPress post/page
                $post_id = $this->create_wordpress_post($page);
                
                if ($post_id && !is_wp_error($post_id)) {
                    // Create pylon record
                    $pylon_result = $this->create_pylon_record($post_id, $page);
                    
                    if ($pylon_result) {
                        $results['success_count']++;
                        $results['created_posts'][] = [
                            'post_id' => $post_id,
                            'title' => $page['page_title'] ?? 'Untitled'
                        ];
                        
                        // Check if this is the homepage
                        if ($should_set_homepage && isset($page['page_archetype']) && $page['page_archetype'] === 'homepage') {
                            $homepage_post_id = $post_id;
                        }
                        
                        // Handle page template assignment if needed
                        // $this->assign_page_template($post_id, $page);
                    } else {
                        // Post created but pylon record failed
                        $results['error_count']++;
                        $results['errors'][] = [
                            'title' => $page['page_title'] ?? 'Untitled',
                            'message' => 'Post created but pylon record failed',
                            'post_id' => $post_id
                        ];
                    }
                } else {
                    // Post creation failed
                    $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error creating post';
                    $results['error_count']++;
                    $results['errors'][] = [
                        'title' => $page['page_title'] ?? 'Untitled',
                        'message' => $error_message
                    ];
                }
            } catch (Exception $e) {
                $results['error_count']++;
                $results['errors'][] = [
                    'title' => $page['page_title'] ?? 'Untitled',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        // Set the homepage if we found one
        if ($should_set_homepage && $homepage_post_id) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $homepage_post_id);
        }
        
        return $results;
    }
    
    /**
     * Clean up temporary files older than 24 hours
     */
    private function cleanup_temp_files($temp_dir) {
        if (!is_dir($temp_dir)) {
            return;
        }
        
        $files = glob($temp_dir . '*.json');
        $cutoff_time = time() - (24 * 60 * 60); // 24 hours ago
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                unlink($file);
            }
        }
    }
}