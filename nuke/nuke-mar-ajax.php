<?php
/**
 * Nuke Mar AJAX Handler
 * 
 * Handles AJAX requests for Nuke Mar functionality from external sources
 * 
 * @package Ruplin
 * @subpackage Nuke
 * @since 4.5.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AJAX actions for Nuke Mar
 * Register directly without waiting for init action to ensure it's available
 */
// Register for logged-in users
add_action('wp_ajax_ruplin_nuke_ajax', 'ruplin_handle_nuke_ajax');

// Register for non-logged-in users (with API key auth)
add_action('wp_ajax_nopriv_ruplin_nuke_ajax', 'ruplin_handle_nuke_ajax');

/**
 * Handle AJAX requests for Nuke Mar operations
 */
function ruplin_handle_nuke_ajax() {
    // Check permissions - but allow API access for development
    $has_permission = current_user_can('manage_options');
    
    // In development mode, also allow API key access even without login
    if (!$has_permission && defined('WP_DEBUG') && WP_DEBUG) {
        // Check for API key or localhost access
        $api_key = $_POST['api_key'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        if (!empty($api_key) || strpos($referer, 'localhost') !== false) {
            $has_permission = true;
        }
    }
    
    if (!$has_permission) {
        wp_send_json_error([
            'message' => 'Insufficient permissions',
            'error' => 'You do not have permission to perform this action'
        ]);
        wp_die();
    }
    
    // Verify API key or nonce
    $api_key = $_POST['api_key'] ?? '';
    $nonce = $_POST['nonce'] ?? '';
    
    // Get API keys from options (if implemented)
    $valid_keys = get_option('ruplin_api_keys', []);
    
    // Allow API key auth for external requests
    $auth_valid = false;
    
    // Check API key first
    if (!empty($api_key)) {
        // For now, accept a simple hardcoded key or empty array (no key validation)
        // In production, implement proper API key management
        if (empty($valid_keys) || in_array($api_key, $valid_keys)) {
            $auth_valid = true;
        }
    }
    
    // Fall back to nonce check if no API key
    if (!$auth_valid && !empty($nonce)) {
        if (wp_verify_nonce($nonce, 'ruplin_nuke_action')) {
            $auth_valid = true;
        }
    }
    
    // If still not authenticated and in development, allow local requests
    if (!$auth_valid && defined('WP_DEBUG') && WP_DEBUG) {
        // Allow requests from localhost for development
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referrer, 'localhost:3000') !== false || strpos($referrer, 'localhost') !== false) {
            $auth_valid = true;
        }
    }
    
    if (!$auth_valid) {
        wp_send_json_error([
            'message' => 'Authentication failed',
            'error' => 'Invalid API key or nonce'
        ]);
        wp_die();
    }
    
    // Include the nuke handler functions
    require_once plugin_dir_path(dirname(__FILE__)) . 'nuke/nuke-mar-handler.php';
    
    // Prepare the data for the handler
    $nuke_data = [
        'delete_all_pages' => $_POST['delete_all_pages'] ?? '0',
        'delete_all_posts' => $_POST['delete_all_posts'] ?? '0',
        'wipe_sitespren_values' => $_POST['wipe_sitespren_values'] ?? '0',
        'exclude_urls_enabled' => $_POST['exclude_urls_enabled'] ?? '0',
        'excluded_urls' => $_POST['excluded_urls'] ?? ''
    ];
    
    // Track results
    $results = [
        'pages_deleted' => 0,
        'posts_deleted' => 0,
        'sitespren_wiped' => false,
        'pylons_cleaned' => 0,
        'orbitposts_cleaned' => 0
    ];
    
    global $wpdb;
    
    try {
        // Start transaction for safety
        $wpdb->query('START TRANSACTION');
        
        // Process URL exclusions
        $excluded_urls = array();
        if ($nuke_data['exclude_urls_enabled'] === '1' && !empty($nuke_data['excluded_urls'])) {
            $urls_input = sanitize_textarea_field($nuke_data['excluded_urls']);
            $urls_array = explode("\n", $urls_input);
            foreach ($urls_array as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $excluded_urls[] = trim($url, '/');
                }
            }
        }
        
        // Delete pages if requested
        if ($nuke_data['delete_all_pages'] === '1') {
            $results['pages_deleted'] = ruplin_delete_all_content('page', $excluded_urls);
            ruplin_log_nuke_action('pages_deleted_ajax', $results['pages_deleted']);
        }
        
        // Delete posts if requested
        if ($nuke_data['delete_all_posts'] === '1') {
            $results['posts_deleted'] = ruplin_delete_all_content('post', $excluded_urls);
            ruplin_log_nuke_action('posts_deleted_ajax', $results['posts_deleted']);
        }
        
        // Wipe sitespren if requested
        if ($nuke_data['wipe_sitespren_values'] === '1') {
            $results['sitespren_wiped'] = ruplin_wipe_sitespren_values();
            if ($results['sitespren_wiped']) {
                ruplin_log_nuke_action('sitespren_wiped_ajax', 1);
            }
        }
        
        // Clean up orphaned data
        $cleanup_stats = ruplin_final_cleanup_custom_tables();
        $results['pylons_cleaned'] = $cleanup_stats['pylons'] ?? 0;
        $results['orbitposts_cleaned'] = $cleanup_stats['orbitposts'] ?? 0;
        
        // Commit transaction
        $wpdb->query('COMMIT');
        
        // Clear caches
        ruplin_clear_cache_after_nuke();
        
        // Build success message
        $message_parts = [];
        if ($results['pages_deleted'] > 0) $message_parts[] = "{$results['pages_deleted']} pages deleted";
        if ($results['posts_deleted'] > 0) $message_parts[] = "{$results['posts_deleted']} posts deleted";
        if ($results['sitespren_wiped']) $message_parts[] = 'Site configuration wiped';
        if ($results['pylons_cleaned'] > 0) $message_parts[] = "{$results['pylons_cleaned']} pylons records cleaned";
        if ($results['orbitposts_cleaned'] > 0) $message_parts[] = "{$results['orbitposts_cleaned']} orbitposts records cleaned";
        
        $results['message'] = !empty($message_parts) 
            ? 'Nuke operation completed: ' . implode(', ', $message_parts) . '.'
            : 'Nuke operation completed with no changes.';
        
        wp_send_json_success($results);
        
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        
        wp_send_json_error([
            'message' => 'Nuke operation failed',
            'error' => $e->getMessage()
        ]);
    }
    
    wp_die();
}

/**
 * Add CORS headers for local development
 * Only active when WP_DEBUG is enabled
 */
function ruplin_add_cors_headers() {
    // Only enable in development
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Check if this is an AJAX request to our handler
        if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'ruplin_nuke_ajax') {
            header("Access-Control-Allow-Origin: http://localhost:3000");
            header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, X-API-Key");
            header("Access-Control-Allow-Credentials: true");
            
            // Handle preflight requests
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                status_header(200);
                exit(0);
            }
        }
    }
}
add_action('init', 'ruplin_add_cors_headers', 1);

/**
 * Alternative: Add CORS headers via send_headers action (more reliable)
 */
function ruplin_send_cors_headers() {
    // Only enable in development
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Check if this is an admin-ajax request
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // Check for our specific action
            if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'ruplin_nuke_ajax') {
                header("Access-Control-Allow-Origin: http://localhost:3000");
                header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
                header("Access-Control-Allow-Headers: Content-Type, X-API-Key");
                header("Access-Control-Allow-Credentials: true");
            }
        }
    }
}
add_action('send_headers', 'ruplin_send_cors_headers', 1);