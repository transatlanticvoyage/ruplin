<?php
/**
 * Snefuru Plugin Elementor Data Updater
 * Handles updating _elementor_data in WordPress posts
 */

class Snefuru_Elementor_Updater {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes for elementor data updates
     */
    public function register_routes() {
        register_rest_route('snefuru/v1', '/posts/(?P<id>\d+)/elementor', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_elementor_data'),
            'permission_callback' => array($this, 'validate_api_key'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));
    }
    
    /**
     * Validate API key using the ruplin_api_key_1 system
     * This connects to the users.ruplin_api_key_1 field in your Next.js app database
     */
    public function validate_api_key($request) {
        // Get API key from Authorization header
        $auth_header = $request->get_header('authorization');
        if (!$auth_header) {
            return new WP_Error('missing_auth', 'Authorization header required', array('status' => 401));
        }
        
        // Extract Bearer token
        if (!preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return new WP_Error('invalid_auth_format', 'Invalid authorization format', array('status' => 401));
        }
        
        $provided_key = trim($matches[1]);
        if (empty($provided_key)) {
            return new WP_Error('empty_key', 'API key cannot be empty', array('status' => 401));
        }
        
        // Log the validation attempt
        error_log("Snefuru: Validating ruplin API key for elementor update: " . substr($provided_key, 0, 8) . "...");
        
        // Get all configured API keys from WordPress options
        // These should be populated by your sync system
        $valid_keys = array();
        
        // Method 1: Check if key is stored in WordPress options (sync from your app)
        $stored_ruplin_keys = get_option('snefuru_ruplin_api_keys', array());
        if (is_array($stored_ruplin_keys)) {
            $valid_keys = array_merge($valid_keys, $stored_ruplin_keys);
        }
        
        // Method 2: Check against individual option (backwards compatibility)
        $single_ruplin_key = get_option('snefuru_ruplin_api_key_1', '');
        if (!empty($single_ruplin_key)) {
            $valid_keys[] = $single_ruplin_key;
        }
        
        // Method 3: Fallback to main upload API key if configured
        $upload_key = get_option('snefuru_upload_api_key', '');
        if (!empty($upload_key)) {
            $valid_keys[] = $upload_key;
        }
        
        // Validate the provided key against all valid keys
        foreach ($valid_keys as $valid_key) {
            if (hash_equals($valid_key, $provided_key)) {
                error_log("Snefuru: Valid ruplin API key authenticated for elementor update");
                return true;
            }
        }
        
        error_log("Snefuru: Invalid ruplin API key provided for elementor update");
        return new WP_Error('invalid_key', 'Invalid API key', array('status' => 403));
    }
    
    /**
     * Update elementor data for a specific post
     */
    public function update_elementor_data($request) {
        $post_id = (int) $request['id'];
        $elementor_data = $request->get_param('elementor_data');
        
        // Validate post exists and user has permission
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
        }
        
        // Log the update attempt
        error_log("Snefuru: Updating elementor data for post ID: {$post_id}");
        
        try {
            // Validate JSON if it's a string
            if (is_string($elementor_data)) {
                $decoded = json_decode($elementor_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new WP_Error('invalid_json', 'Invalid JSON in elementor_data', array('status' => 400));
                }
                $elementor_data_to_store = $elementor_data; // Store as string
            } else {
                // If it's already an array/object, encode it
                $elementor_data_to_store = json_encode($elementor_data);
            }
            
            // Update the _elementor_data post meta
            $result = update_post_meta($post_id, '_elementor_data', $elementor_data_to_store);
            
            if ($result === false) {
                error_log("Snefuru: Failed to update _elementor_data for post {$post_id}");
                return new WP_Error('update_failed', 'Failed to update elementor data', array('status' => 500));
            }
            
            // Clear any Elementor cache if the plugin is active
            if (class_exists('\Elementor\Plugin')) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
                error_log("Snefuru: Cleared Elementor cache after update");
            }
            
            // Update post modified date to trigger cache invalidation
            wp_update_post(array(
                'ID' => $post_id,
                'post_modified' => current_time('mysql'),
                'post_modified_gmt' => current_time('mysql', 1)
            ));
            
            error_log("Snefuru: Successfully updated elementor data for post {$post_id}");
            
            return array(
                'success' => true,
                'message' => 'Elementor data updated successfully',
                'post_id' => $post_id,
                'updated_at' => current_time('mysql'),
                'data_size' => strlen($elementor_data_to_store)
            );
            
        } catch (Exception $e) {
            error_log("Snefuru: Exception updating elementor data: " . $e->getMessage());
            return new WP_Error('update_exception', $e->getMessage(), array('status' => 500));
        }
    }
}

// Initialize the elementor updater
new Snefuru_Elementor_Updater();