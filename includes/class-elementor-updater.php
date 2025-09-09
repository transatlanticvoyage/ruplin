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
        // Enhanced endpoint (default - uses new method)
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
        
        // Native Elementor API endpoint (most robust)
        register_rest_route('snefuru/v1', '/posts/(?P<id>\d+)/elementor-native', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_elementor_data_native'),
            'permission_callback' => array($this, 'validate_api_key'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));
        
        // Legacy endpoint (fallback - uses old method)
        register_rest_route('snefuru/v1', '/posts/(?P<id>\d+)/elementor-legacy', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_elementor_data_legacy'),
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
        
        // Get the ruplin API key
        $stored_key = get_option('snefuru_ruplin_api_key_1', '');
        
        if (!empty($stored_key) && hash_equals($stored_key, $provided_key)) {
            error_log("Snefuru: Valid ruplin API key authenticated for elementor update");
            return true;
        }
        
        error_log("Snefuru: Invalid ruplin API key provided for elementor update");
        return new WP_Error('invalid_key', 'Invalid API key', array('status' => 403));
    }
    
    /**
     * Update elementor data for a specific post with proper meta key handling
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
        error_log("Snefuru: Starting enhanced elementor data update for post ID: {$post_id}");
        
        try {
            // Validate and prepare JSON data
            if (is_string($elementor_data)) {
                $decoded = json_decode($elementor_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new WP_Error('invalid_json', 'Invalid JSON in elementor_data: ' . json_last_error_msg(), array('status' => 400));
                }
                $elementor_data_to_store = $elementor_data; // Store as string
                $data_array = $decoded;
            } else {
                // If it's already an array/object, encode it
                $data_array = $elementor_data;
                $elementor_data_to_store = json_encode($elementor_data);
            }
            
            // Validate Elementor data structure
            if (!$this->validate_elementor_structure($data_array)) {
                return new WP_Error('invalid_structure', 'Invalid Elementor data structure', array('status' => 400));
            }
            
            // Update all required Elementor meta keys
            $meta_updates = $this->update_elementor_meta_keys($post_id, $elementor_data_to_store, $data_array);
            
            if (!$meta_updates['success']) {
                return new WP_Error('meta_update_failed', $meta_updates['error'], array('status' => 500));
            }
            
            // Perform comprehensive cache clearing and regeneration
            $cache_result = $this->clear_and_regenerate_caches($post_id);
            
            // Update post modified date to trigger cache invalidation
            wp_update_post(array(
                'ID' => $post_id,
                'post_modified' => current_time('mysql'),
                'post_modified_gmt' => current_time('mysql', 1)
            ));
            
            error_log("Snefuru: Successfully completed enhanced elementor update for post {$post_id}");
            
            return array(
                'success' => true,
                'message' => 'Elementor data updated with enhanced method',
                'post_id' => $post_id,
                'updated_at' => current_time('mysql'),
                'data_size' => strlen($elementor_data_to_store),
                'meta_updates' => $meta_updates['details'],
                'cache_cleared' => $cache_result
            );
            
        } catch (Exception $e) {
            error_log("Snefuru: Exception in enhanced elementor update: " . $e->getMessage());
            return new WP_Error('update_exception', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Update all required Elementor meta keys
     */
    private function update_elementor_meta_keys($post_id, $elementor_data, $data_array) {
        try {
            $updates = array();
            
            // Primary elementor data
            $result = update_post_meta($post_id, '_elementor_data', $elementor_data);
            $updates['_elementor_data'] = $result !== false;
            
            // Elementor version (required for compatibility)
            if (defined('ELEMENTOR_VERSION')) {
                update_post_meta($post_id, '_elementor_version', ELEMENTOR_VERSION);
                $updates['_elementor_version'] = ELEMENTOR_VERSION;
            }
            
            // Edit mode (required for Elementor to recognize the page)
            update_post_meta($post_id, '_elementor_edit_mode', 'builder');
            $updates['_elementor_edit_mode'] = 'builder';
            
            // Template type (determine from post type)
            $post = get_post($post_id);
            $template_type = 'wp-' . $post->post_type;
            update_post_meta($post_id, '_elementor_template_type', $template_type);
            $updates['_elementor_template_type'] = $template_type;
            
            // Calculate and update controls usage
            $controls_usage = $this->calculate_controls_usage($data_array);
            update_post_meta($post_id, '_elementor_controls_usage', $controls_usage);
            $updates['_elementor_controls_usage'] = count($controls_usage);
            
            error_log("Snefuru: Updated all Elementor meta keys for post {$post_id}");
            
            return array(
                'success' => true,
                'details' => $updates
            );
            
        } catch (Exception $e) {
            error_log("Snefuru: Failed to update meta keys: " . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Validate Elementor data structure
     */
    private function validate_elementor_structure($data) {
        // Basic validation - should be an array of elements
        if (!is_array($data)) {
            return false;
        }
        
        // Each element should have required properties
        foreach ($data as $element) {
            if (!is_array($element)) {
                return false;
            }
            
            // Check for required Elementor element properties
            if (!isset($element['id']) || !isset($element['elType'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Calculate controls usage from Elementor data
     */
    private function calculate_controls_usage($data) {
        $controls = array();
        
        $this->extract_controls_recursive($data, $controls);
        
        return $controls;
    }
    
    /**
     * Recursively extract controls from Elementor data
     */
    private function extract_controls_recursive($data, &$controls) {
        if (!is_array($data)) {
            return;
        }
        
        foreach ($data as $item) {
            if (is_array($item)) {
                // Track widget types
                if (isset($item['widgetType'])) {
                    $widget_type = $item['widgetType'];
                    if (!isset($controls[$widget_type])) {
                        $controls[$widget_type] = array(
                            'count' => 0,
                            'control_percentage' => 0,
                            'controls' => array()
                        );
                    }
                    $controls[$widget_type]['count']++;
                }
                
                // Track element types
                if (isset($item['elType'])) {
                    $el_type = $item['elType'];
                    if (!isset($controls[$el_type])) {
                        $controls[$el_type] = array(
                            'count' => 0,
                            'control_percentage' => 0,
                            'controls' => array()
                        );
                    }
                    $controls[$el_type]['count']++;
                }
                
                // Recurse into nested elements
                if (isset($item['elements'])) {
                    $this->extract_controls_recursive($item['elements'], $controls);
                }
            }
        }
    }
    
    /**
     * Clear and regenerate all Elementor caches
     */
    private function clear_and_regenerate_caches($post_id) {
        $cleared = array();
        
        if (!class_exists('\Elementor\Plugin')) {
            error_log("Snefuru: Elementor plugin not available for cache clearing");
            return $cleared;
        }
        
        try {
            // Clear general files cache
            \Elementor\Plugin::$instance->files_manager->clear_cache();
            $cleared['files_cache'] = true;
            
            // Clear post-specific CSS cache
            if (method_exists(\Elementor\Plugin::$instance, 'posts_css_manager')) {
                \Elementor\Plugin::$instance->posts_css_manager->clear_cache();
                $cleared['posts_css_cache'] = true;
            }
            
            // Regenerate CSS for this specific post
            if (class_exists('\Elementor\Core\Files\CSS\Post')) {
                $css_file = \Elementor\Core\Files\CSS\Post::create($post_id);
                if ($css_file) {
                    $css_file->delete();
                    $css_file->enqueue();
                    $cleared['post_css_regenerated'] = true;
                }
            }
            
            // Clear frontend cache if available
            if (method_exists(\Elementor\Plugin::$instance, 'frontend')) {
                // Force regeneration on next page load
                delete_post_meta($post_id, '_elementor_css');
                $cleared['frontend_cache'] = true;
            }
            
            error_log("Snefuru: Comprehensive cache clearing completed for post {$post_id}");
            
        } catch (Exception $e) {
            error_log("Snefuru: Cache clearing error: " . $e->getMessage());
            $cleared['error'] = $e->getMessage();
        }
        
        return $cleared;
    }
    
    /**
     * Update elementor data using Elementor's native document API (most robust method)
     */
    public function update_elementor_data_native($request) {
        $post_id = (int) $request['id'];
        $elementor_data = $request->get_param('elementor_data');
        
        // Validate post exists and is an Elementor page
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
        }
        
        // Check if Elementor is available
        if (!class_exists('\Elementor\Plugin')) {
            return new WP_Error('elementor_not_available', 'Elementor plugin not available', array('status' => 500));
        }
        
        error_log("Snefuru: Starting native elementor update for post ID: {$post_id}");
        
        try {
            // Parse and validate JSON data
            if (is_string($elementor_data)) {
                $data_array = json_decode($elementor_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new WP_Error('invalid_json', 'Invalid JSON: ' . json_last_error_msg(), array('status' => 400));
                }
            } else {
                $data_array = $elementor_data;
            }
            
            // Get Elementor document
            $document = \Elementor\Plugin::$instance->documents->get($post_id);
            
            if (!$document) {
                // Try to create document if it doesn't exist
                $document = \Elementor\Plugin::$instance->documents->create(
                    'wp-' . $post->post_type,
                    array(
                        'post_id' => $post_id,
                        'post_title' => $post->post_title,
                    )
                );
                
                if (!$document) {
                    return new WP_Error('document_creation_failed', 'Failed to create Elementor document', array('status' => 500));
                }
            }
            
            // Get current settings to preserve them
            $current_settings = $document->get_settings();
            
            // Log what we're about to update
            $elements_count = is_array($data_array) ? count($data_array) : 0;
            error_log("Snefuru: Updating {$elements_count} elements via native API for post {$post_id}");
            
            // Look for image URLs in the data being saved
            $data_string = json_encode($data_array);
            $image_url_matches = array();
            if (preg_match_all('/https?:\/\/[^\s"\']+\.(jpg|jpeg|png|gif|webp)/i', $data_string, $matches)) {
                $image_url_matches = array_unique($matches[0]);
                error_log("Snefuru: Found " . count($image_url_matches) . " image URLs in update data");
                foreach (array_slice($image_url_matches, 0, 5) as $i => $url) {
                    error_log("Snefuru: Image URL " . ($i + 1) . ": " . $url);
                }
            }
            
            // Update document with new elements and preserve settings
            $document->save(array(
                'elements' => $data_array,
                'settings' => $current_settings
            ));
            
            error_log("Snefuru: Successfully updated via native Elementor API for post {$post_id}");
            
            // Force comprehensive cache clearing and regeneration
            if (class_exists('\Elementor\Core\Files\CSS\Post')) {
                $css_file = \Elementor\Core\Files\CSS\Post::create($post_id);
                if ($css_file) {
                    $css_file->delete();
                    $css_file->update();
                    error_log("Snefuru: Forced CSS regeneration for post {$post_id}");
                }
            }
            
            // Clear WordPress object cache for this post
            wp_cache_delete($post_id, 'posts');
            wp_cache_delete($post_id, 'post_meta');
            
            // Clear any page caching if available
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
                error_log("Snefuru: Flushed WordPress cache");
            }
            
            // Clear WP Rocket cache if available
            if (function_exists('rocket_clean_post')) {
                rocket_clean_post($post_id);
                error_log("Snefuru: Cleared WP Rocket cache for post {$post_id}");
            }
            
            // Clear W3 Total Cache if available  
            if (function_exists('w3tc_pgcache_flush_post')) {
                w3tc_pgcache_flush_post($post_id);
                error_log("Snefuru: Cleared W3TC cache for post {$post_id}");
            }
            
            return array(
                'success' => true,
                'message' => 'Elementor data updated via native API',
                'method' => 'native_elementor_api',
                'post_id' => $post_id,
                'updated_at' => current_time('mysql'),
                'elements_count' => is_array($data_array) ? count($data_array) : 0
            );
            
        } catch (Exception $e) {
            error_log("Snefuru: Native Elementor API error: " . $e->getMessage());
            return new WP_Error('native_update_failed', 'Native update failed: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Legacy elementor data update method (for fallback compatibility)
     */
    public function update_elementor_data_legacy($request) {
        $post_id = (int) $request['id'];
        $elementor_data = $request->get_param('elementor_data');
        
        // Validate post exists
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
        }
        
        error_log("Snefuru: Using legacy elementor update method for post ID: {$post_id}");
        
        try {
            // Basic JSON validation
            if (is_string($elementor_data)) {
                $decoded = json_decode($elementor_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new WP_Error('invalid_json', 'Invalid JSON in elementor_data', array('status' => 400));
                }
                $elementor_data_to_store = $elementor_data;
            } else {
                $elementor_data_to_store = json_encode($elementor_data);
            }
            
            // Simple meta update (old method)
            $result = update_post_meta($post_id, '_elementor_data', $elementor_data_to_store);
            
            if ($result === false) {
                return new WP_Error('update_failed', 'Failed to update elementor data', array('status' => 500));
            }
            
            // Basic cache clearing
            if (class_exists('\Elementor\Plugin')) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }
            
            error_log("Snefuru: Legacy update completed for post {$post_id}");
            
            return array(
                'success' => true,
                'message' => 'Elementor data updated (legacy method)',
                'method' => 'legacy',
                'post_id' => $post_id,
                'updated_at' => current_time('mysql')
            );
            
        } catch (Exception $e) {
            error_log("Snefuru: Legacy update error: " . $e->getMessage());
            return new WP_Error('legacy_update_failed', $e->getMessage(), array('status' => 500));
        }
    }
}

// Initialize the elementor updater
new Snefuru_Elementor_Updater();