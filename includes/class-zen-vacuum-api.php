<?php
/**
 * Zen Sitespren Vacuum API Handler Class
 * 
 * Handles API requests for zen_sitespren data vacuum operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Zen_Vacuum_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('snefuru/v1', '/zen-sitespren-vacuum', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_zen_sitespren_data'),
            'permission_callback' => array($this, 'permission_check'),
        ));
    }
    
    /**
     * Permission check for API access
     */
    public function permission_check($request) {
        // Check for API key in header or allow from specific user agents
        $user_agent = $request->get_header('User-Agent');
        $api_key = $request->get_header('Authorization');
        
        // Allow requests from Snefuru NextJS app
        if (strpos($user_agent, 'Snefuru-NextJS-App') !== false) {
            return true;
        }
        
        // Check for valid API key
        if ($api_key) {
            $stored_api_key = get_option('snefuru_ruplin_api_key_1', '');
            if (!empty($stored_api_key) && $api_key === 'Bearer ' . $stored_api_key) {
                return true;
            }
        }
        
        return new WP_Error('unauthorized', 'Unauthorized access - missing or invalid API key', array('status' => 401));
    }
    
    /**
     * Get all zen_sitespren data for vacuum operation
     */
    public function get_zen_sitespren_data($request) {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'zen_sitespren';
            
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                return new WP_Error('table_not_found', 'zen_sitespren table not found', array('status' => 404));
            }
            
            // Get all data from zen_sitespren table
            $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
            
            if ($wpdb->last_error) {
                error_log('Snefuru Vacuum API Error: ' . $wpdb->last_error);
                return new WP_Error('database_error', 'Database query failed: ' . $wpdb->last_error, array('status' => 500));
            }
            
            // Log the successful vacuum operation
            error_log('Snefuru: zen_sitespren vacuum API called - ' . count($results) . ' records retrieved');
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'zen_sitespren data retrieved successfully',
                'data' => $results,
                'record_count' => count($results),
                'table_name' => $table_name,
                'site_url' => get_site_url(),
                'timestamp' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            error_log('Snefuru Vacuum API Exception: ' . $e->getMessage());
            return new WP_Error('server_error', 'Server error: ' . $e->getMessage(), array('status' => 500));
        }
    }
}