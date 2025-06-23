<?php

class Snefuru_CSS_Endpoint {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('snefuru/v1', '/css/bespoke', array(
            'methods' => 'GET',
            'callback' => array($this, 'serve_css'),
            'permission_callback' => '__return_true', // Public access
        ));
        
        register_rest_route('snefuru/v1', '/css/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'css_status'),
            'permission_callback' => '__return_true', // Public access
        ));
    }
    
    /**
     * Serve the CSS content
     */
    public function serve_css($request) {
        global $wpdb;
        
        $styling_table = $wpdb->prefix . 'snefuruplin_styling';
        $record = $wpdb->get_row("SELECT * FROM $styling_table ORDER BY styling_id LIMIT 1");
        
        if (!$record || empty($record->styling_content)) {
            // Return default CSS if nothing is saved
            $css_content = "/* No custom CSS configured */";
        } else {
            $css_content = $record->styling_content;
        }
        
        // Set proper headers and output raw CSS (not JSON)
        header('Content-Type: text/css; charset=UTF-8');
        header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
        header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Stop WordPress from adding any extra output
        status_header(200);
        
        // Output raw CSS and exit
        echo $css_content;
        exit;
    }
    
    /**
     * Provide status information about the CSS endpoint
     */
    public function css_status($request) {
        global $wpdb;
        
        $styling_table = $wpdb->prefix . 'snefuruplin_styling';
        $record = $wpdb->get_row("SELECT * FROM $styling_table ORDER BY styling_id LIMIT 1");
        
        $status = array(
            'endpoint_active' => true,
            'has_custom_css' => !empty($record->styling_content),
            'css_length' => $record ? strlen($record->styling_content) : 0,
            'last_updated' => $record ? $record->updated_at : null,
            'public_url' => get_rest_url(null, 'snefuru/v1/css/bespoke'),
            'usage_example' => '<link rel="stylesheet" href="' . get_rest_url(null, 'snefuru/v1/css/bespoke') . '">',
            'timestamp' => current_time('Y-m-d H:i:s')
        );
        
        return new WP_REST_Response($status, 200);
    }
}