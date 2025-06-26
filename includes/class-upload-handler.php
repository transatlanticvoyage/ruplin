<?php

/**
 * Snefuru Upload Handler
 * Handles image uploads from the Next.js app via custom REST API endpoints
 */
class Snefuru_Upload_Handler {
    
    private $api_namespace = 'snefuru/v1';
    private $upload_endpoint = 'upload-image';
    private $status_endpoint = 'status';
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_endpoints'));
        add_action('init', array($this, 'init_upload_capabilities'));
    }
    
    /**
     * Initialize upload capabilities and security
     */
    public function init_upload_capabilities() {
        // Add upload capability to plugin
        if (!current_user_can('upload_files')) {
            // This will be handled by API key authentication instead
        }
    }
    
    /**
     * Register custom REST API endpoints
     */
    public function register_rest_endpoints() {
        // Main upload endpoint
        register_rest_route($this->api_namespace, '/' . $this->upload_endpoint, array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_image_upload'),
            'permission_callback' => array($this, 'check_upload_permissions'),
            'args' => array(
                'api_key' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'API key for authentication'
                ),
                'batch_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Batch ID from Next.js app'
                ),
                'plan_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Plan ID from Next.js app'
                ),
                'filename' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Custom filename for the image'
                )
            )
        ));
        
        // Status check endpoint
        register_rest_route($this->api_namespace, '/' . $this->status_endpoint, array(
            'methods' => 'GET',
            'callback' => array($this, 'get_plugin_status'),
            'permission_callback' => array($this, 'check_status_permissions'),
            'args' => array(
                'api_key' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'API key for authentication'
                )
            )
        ));

        // New endpoint for syncing posts/pages
        register_rest_route($this->api_namespace, '/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_site_posts'),
            'permission_callback' => array($this, 'check_posts_permissions')
        ));
    }
    
    /**
     * Check permissions for upload endpoint
     */
    public function check_upload_permissions($request) {
        $api_key = $request->get_param('api_key');
        return $this->validate_api_key($api_key);
    }
    
    /**
     * Check permissions for status endpoint
     */
    public function check_status_permissions($request) {
        $api_key = $request->get_param('api_key');
        return $this->validate_api_key($api_key);
    }
    
    /**
     * Validate API key
     */
    private function validate_api_key($api_key) {
        if (empty($api_key)) {
            return new WP_Error(
                'missing_api_key',
                'API key is required',
                array('status' => 401)
            );
        }
        
        // Check the ruplin API key
        $stored_api_key = get_option('snefuru_ruplin_api_key_1', '');
        
        if (!empty($stored_api_key) && hash_equals($stored_api_key, $api_key)) {
            return true;
        }
        
        return new WP_Error(
            'invalid_api_key',
            'Invalid API key provided',
            array('status' => 401)
        );
    }
    
    /**
     * Handle image upload request
     */
    public function handle_image_upload($request) {
        try {
            // Get uploaded files
            $files = $request->get_file_params();
            
            if (empty($files) || !isset($files['file'])) {
                return new WP_Error(
                    'no_file_uploaded',
                    'No file was uploaded',
                    array('status' => 400)
                );
            }
            
            $file = $files['file'];
            
            // Get additional parameters
            $batch_id = $request->get_param('batch_id');
            $plan_id = $request->get_param('plan_id');
            $custom_filename = $request->get_param('filename');
            
            // Validate file
            $validation_result = $this->validate_uploaded_file($file);
            if (is_wp_error($validation_result)) {
                return $validation_result;
            }
            
            // Process the upload
            $upload_result = $this->process_file_upload($file, array(
                'batch_id' => $batch_id,
                'plan_id' => $plan_id,
                'custom_filename' => $custom_filename
            ));
            
            if (is_wp_error($upload_result)) {
                return $upload_result;
            }
            
            // Log successful upload
            $this->log_upload_activity('success', 'Image uploaded successfully', array(
                'attachment_id' => $upload_result['attachment_id'],
                'batch_id' => $batch_id,
                'plan_id' => $plan_id,
                'file_url' => $upload_result['url']
            ));
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => array(
                    'attachment_id' => $upload_result['attachment_id'],
                    'url' => $upload_result['url'],
                    'filename' => $upload_result['filename'],
                    'file_size' => $upload_result['file_size'],
                    'batch_id' => $batch_id,
                    'plan_id' => $plan_id,
                    'upload_date' => current_time('mysql')
                )
            ));
            
        } catch (Exception $e) {
            // Log error
            $this->log_upload_activity('error', 'Upload failed: ' . $e->getMessage(), array(
                'batch_id' => $batch_id ?? null,
                'plan_id' => $plan_id ?? null,
                'error_details' => $e->getTraceAsString()
            ));
            
            return new WP_Error(
                'upload_failed',
                'Upload failed: ' . $e->getMessage(),
                array('status' => 500)
            );
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validate_uploaded_file($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error(
                'upload_error',
                'File upload error: ' . $this->get_upload_error_message($file['error']),
                array('status' => 400)
            );
        }
        
        // Check file size
        $max_size = wp_max_upload_size();
        if ($file['size'] > $max_size) {
            return new WP_Error(
                'file_too_large',
                'File size exceeds maximum allowed size of ' . size_format($max_size),
                array('status' => 400)
            );
        }
        
        // Check file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $file_type = wp_check_filetype($file['name']);
        
        if (!in_array($file['type'], $allowed_types) && !in_array($file_type['type'], $allowed_types)) {
            return new WP_Error(
                'invalid_file_type',
                'Invalid file type. Only images are allowed.',
                array('status' => 400)
            );
        }
        
        return true;
    }
    
    /**
     * Process file upload using WordPress media handling
     */
    private function process_file_upload($file, $metadata = array()) {
        // Include WordPress file handling functions
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        // Set custom filename if provided
        if (!empty($metadata['custom_filename'])) {
            $file['name'] = $this->sanitize_filename($metadata['custom_filename']);
        }
        
        // Handle the upload
        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp'
            )
        );
        
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if (isset($movefile['error'])) {
            return new WP_Error(
                'upload_failed',
                $movefile['error'],
                array('status' => 500)
            );
        }
        
        // Create attachment post
        $attachment = array(
            'guid' => $movefile['url'],
            'post_mime_type' => $movefile['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        // Insert attachment into database
        $attachment_id = wp_insert_attachment($attachment, $movefile['file']);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        // Generate attachment metadata
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $movefile['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Store custom metadata
        if (!empty($metadata['batch_id'])) {
            update_post_meta($attachment_id, 'snefuru_batch_id', sanitize_text_field($metadata['batch_id']));
        }
        
        if (!empty($metadata['plan_id'])) {
            update_post_meta($attachment_id, 'snefuru_plan_id', sanitize_text_field($metadata['plan_id']));
        }
        
        // Store upload source
        update_post_meta($attachment_id, 'snefuru_upload_source', 'plugin_api');
        update_post_meta($attachment_id, 'snefuru_upload_timestamp', current_time('mysql'));
        
        return array(
            'attachment_id' => $attachment_id,
            'url' => $movefile['url'],
            'filename' => basename($movefile['file']),
            'file_size' => filesize($movefile['file']),
            'file_path' => $movefile['file']
        );
    }
    
    /**
     * Get plugin status
     */
    public function get_plugin_status($request) {
        $status = array(
            'plugin_active' => true,
            'plugin_version' => SNEFURU_PLUGIN_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'site_url' => get_site_url(),
            'upload_max_filesize' => size_format(wp_max_upload_size()),
            'api_endpoints' => array(
                'upload' => rest_url($this->api_namespace . '/' . $this->upload_endpoint),
                'status' => rest_url($this->api_namespace . '/' . $this->status_endpoint)
            ),
            'last_check' => current_time('mysql')
        );
        
        return rest_ensure_response(array(
            'success' => true,
            'data' => $status
        ));
    }
    
    /**
     * Sanitize filename
     */
    private function sanitize_filename($filename) {
        $filename = sanitize_file_name($filename);
        
        // Ensure it has an extension
        if (!pathinfo($filename, PATHINFO_EXTENSION)) {
            $filename .= '.jpg';
        }
        
        return $filename;
    }
    
    /**
     * Get upload error message
     */
    private function get_upload_error_message($error_code) {
        $error_messages = array(
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        );
        
        return isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown upload error';
    }
    
    /**
     * Log upload activity
     */
    private function log_upload_activity($type, $message, $data = array()) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        );
        
        // Store in WordPress options (for now, could be moved to custom table later)
        $existing_logs = get_option('snefuru_upload_logs', array());
        
        // Keep only last 1000 entries
        if (count($existing_logs) >= 1000) {
            $existing_logs = array_slice($existing_logs, -999);
        }
        
        $existing_logs[] = $log_entry;
        update_option('snefuru_upload_logs', $existing_logs);
        
        // Also log to WordPress error log for debugging
        if ($type === 'error') {
            error_log('Snefuru Upload Error: ' . $message . ' | Data: ' . json_encode($data));
        }
    }

    /**
     * Check permissions for posts endpoint
     */
    public function check_posts_permissions($request) {
        // Check for API key in header or parameter
        $api_key = $request->get_header('Authorization');
        if ($api_key && strpos($api_key, 'Bearer ') === 0) {
            $api_key = substr($api_key, 7); // Remove 'Bearer ' prefix
        } else {
            $api_key = $request->get_param('api_key');
        }
        
        return $this->validate_api_key($api_key);
    }

    /**
     * Get site posts and pages for syncing
     */
    public function get_site_posts($request) {
        error_log('Snefuru Plugin: get_site_posts method called');
        
        try {
            // Check permissions first
            $permission_check = $this->check_posts_permissions($request);
            if (is_wp_error($permission_check)) {
                error_log('Snefuru Plugin: Permission check failed: ' . $permission_check->get_error_message());
                return $permission_check;
            }
            
            error_log('Snefuru Plugin: Permission check passed');
            
            // Get parameters
            $post_type = $request->get_param('post_type');
            $status = $request->get_param('status');
            $limit = intval($request->get_param('limit')) ?: 1000;
            $offset = intval($request->get_param('offset')) ?: 0;

            // Default to both posts and pages
            $post_types = $post_type ? array($post_type) : array('post', 'page');
            $post_status = $status ? array($status) : array('publish', 'private', 'draft', 'pending');

            error_log('Snefuru Plugin: Query params - post_types: ' . json_encode($post_types) . ', post_status: ' . json_encode($post_status) . ', limit: ' . $limit);

            $all_posts = array();

            foreach ($post_types as $type) {
                // Query posts
                $query_args = array(
                    'post_type' => $type,
                    'post_status' => $post_status,
                    'posts_per_page' => $limit,
                    'offset' => $offset,
                    'orderby' => 'modified',
                    'order' => 'DESC',
                );

                error_log('Snefuru Plugin: Querying for type: ' . $type . ' with args: ' . json_encode($query_args));
                $posts = get_posts($query_args);
                error_log('Snefuru Plugin: Found ' . count($posts) . ' posts of type: ' . $type);

                foreach ($posts as $post) {
                    // Get all post meta
                    $meta = get_post_meta($post->ID);
                    $processed_meta = array();
                    
                    foreach ($meta as $key => $value) {
                        $processed_meta[$key] = is_array($value) && count($value) === 1 ? $value[0] : $value;
                    }

                    // Get categories and tags
                    $categories = wp_get_post_categories($post->ID);
                    $tags = wp_get_post_tags($post->ID, array('fields' => 'ids'));

                    // Get featured image
                    $featured_media = get_post_thumbnail_id($post->ID);

                    // Get Elementor data specifically
                    $elementor_data = null;
                    if (isset($processed_meta['_elementor_data'])) {
                        $elementor_raw = $processed_meta['_elementor_data'];
                        // Try to decode JSON if it's a string
                        if (is_string($elementor_raw)) {
                            $decoded = json_decode($elementor_raw, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $elementor_data = $decoded;
                            } else {
                                // If it's not valid JSON, store as string
                                $elementor_data = $elementor_raw;
                            }
                        } else {
                            $elementor_data = $elementor_raw;
                        }
                    }

                    $post_data = array(
                        'ID' => $post->ID,
                        'post_title' => $post->post_title,
                        'post_content' => $post->post_content,
                        'post_excerpt' => $post->post_excerpt,
                        'post_status' => $post->post_status,
                        'post_type' => $post->post_type,
                        'post_date' => $post->post_date,
                        'post_modified' => $post->post_modified,
                        'post_author' => $post->post_author,
                        'post_name' => $post->post_name, // slug
                        'guid' => $post->guid,
                        'comment_status' => $post->comment_status,
                        'ping_status' => $post->ping_status,
                        'menu_order' => $post->menu_order,
                        'post_parent' => $post->post_parent,
                        'featured_media' => $featured_media,
                        'categories' => $categories,
                        'tags' => $tags,
                        'meta' => $processed_meta,
                        'elementor_data' => $elementor_data,
                    );

                    $all_posts[] = $post_data;
                }
            }

            error_log('Snefuru Plugin: Total posts found: ' . count($all_posts));

            $response_data = array(
                'success' => true,
                'data' => $all_posts,
                'total' => count($all_posts),
                'message' => sprintf('Retrieved %d posts/pages', count($all_posts))
            );
            
            error_log('Snefuru Plugin: Returning response with ' . count($all_posts) . ' posts');
            return rest_ensure_response($response_data);

        } catch (Exception $e) {
            error_log('Snefuru Plugin: Exception in get_site_posts: ' . $e->getMessage());
            error_log('Snefuru Plugin: Exception trace: ' . $e->getTraceAsString());
            return rest_ensure_response(array(
                'success' => false,
                'message' => 'Error retrieving posts: ' . $e->getMessage(),
                'data' => array()
            ));
        } catch (Error $e) {
            error_log('Snefuru Plugin: Fatal error in get_site_posts: ' . $e->getMessage());
            error_log('Snefuru Plugin: Error trace: ' . $e->getTraceAsString());
            return rest_ensure_response(array(
                'success' => false,
                'message' => 'Fatal error retrieving posts: ' . $e->getMessage(),
                'data' => array()
            ));
        }
    }
} 