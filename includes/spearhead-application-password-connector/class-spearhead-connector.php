<?php
/**
 * Spearhead Application Password Connector
 * 
 * Handles automated WordPress application password authentication flow
 * for integration with external applications like Tregnar/Snefuru.
 * 
 * @package Ruplin
 * @subpackage Spearhead_Application_Password_Connector
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Spearhead Connector Class
 */
class Spearhead_Application_Password_Connector {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register REST API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Add JavaScript to the authorization page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_authorization_scripts'));
        
        // Add custom query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Handle the authorization page modifications - hook very early
        add_action('init', array($this, 'modify_authorization_page'), 1);
        
        // Also hook into admin_init for additional processing
        add_action('admin_init', array($this, 'modify_authorization_page'));
        
        // Override HTTPS checks for Spearhead requests
        add_action('init', array($this, 'override_https_checks_for_spearhead'), 1);
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('spearhead/v1', '/app-password/create', array(
            'methods'  => 'POST',
            'callback' => array($this, 'create_application_password'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'app_name' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'success_url' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'esc_url_raw',
                ),
            ),
        ));
        
        register_rest_route('spearhead/v1', '/app-password/verify', array(
            'methods'  => 'POST',
            'callback' => array($this, 'verify_application_password'),
            'permission_callback' => '__return_true', // Public endpoint
            'args' => array(
                'username' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'password' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        register_rest_route('spearhead/v1', '/app-password/callback', array(
            'methods'  => 'GET',
            'callback' => array($this, 'handle_callback'),
            'permission_callback' => '__return_true', // Public endpoint
        ));
        
        register_rest_route('spearhead/v1', '/debug/https-check', array(
            'methods'  => 'GET',
            'callback' => array($this, 'debug_https_check'),
            'permission_callback' => '__return_true', // Public endpoint
        ));
    }
    
    /**
     * Check permission for creating application password
     */
    public function check_permission($request) {
        return current_user_can('manage_options');
    }
    
    /**
     * Create application password via REST API
     */
    public function create_application_password($request) {
        $app_name = $request->get_param('app_name');
        $success_url = $request->get_param('success_url');
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
        }
        
        // Generate application password
        $app_password = wp_generate_password(24, false);
        $hashed = wp_hash_password($app_password);
        
        // Get existing application passwords
        $app_passwords = get_user_meta($user_id, '_application_passwords', true);
        if (!$app_passwords) {
            $app_passwords = array();
        }
        
        // Add new password
        $app_passwords[] = array(
            'uuid'      => wp_generate_uuid4(),
            'app_id'    => '',
            'name'      => $app_name,
            'password'  => $hashed,
            'created'   => time(),
            'last_used' => null,
            'last_ip'   => null,
        );
        
        // Save back to user meta
        update_user_meta($user_id, '_application_passwords', $app_passwords);
        
        // Get username
        $user = get_userdata($user_id);
        
        // Format password with spaces for display
        $formatted_password = trim(chunk_split($app_password, 4, ' '));
        
        return array(
            'success' => true,
            'username' => $user->user_login,
            'password' => $formatted_password,
            'raw_password' => $app_password,
            'success_url' => $success_url,
        );
    }
    
    /**
     * Verify application password
     */
    public function verify_application_password($request) {
        $username = $request->get_param('username');
        $password = $request->get_param('password');
        
        // Remove spaces from password if present
        $password = str_replace(' ', '', $password);
        
        // Attempt authentication
        $user = wp_authenticate_application_password(null, $username, $password);
        
        if (is_wp_error($user)) {
            return array(
                'success' => false,
                'message' => 'Invalid credentials',
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Password verified successfully',
            'user_id' => $user->ID,
            'username' => $user->user_login,
        );
    }
    
    /**
     * Handle callback from authorization page
     */
    public function handle_callback($request) {
        $params = $request->get_params();
        
        // This endpoint can be used to handle the redirect back from WordPress
        // after application password is created
        wp_safe_redirect(home_url());
        exit;
    }
    
    /**
     * Debug HTTPS and application password availability
     */
    public function debug_https_check($request) {
        return array(
            'is_ssl' => is_ssl(),
            'wp_is_using_https' => function_exists('wp_is_using_https') ? wp_is_using_https() : 'function not found',
            'wp_is_application_passwords_available' => function_exists('wp_is_application_passwords_available') ? wp_is_application_passwords_available() : 'function not found',
            'server_https' => isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set',
            'server_port' => isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 'not set',
            'server_scheme' => isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'not set',
            'constants' => array(
                'FORCE_SSL_ADMIN' => defined('FORCE_SSL_ADMIN') ? FORCE_SSL_ADMIN : 'not defined',
                'SPEARHEAD_HTTPS_OVERRIDE' => defined('SPEARHEAD_HTTPS_OVERRIDE') ? SPEARHEAD_HTTPS_OVERRIDE : 'not defined',
            ),
            'site_url' => site_url(),
            'home_url' => home_url(),
            'admin_url' => admin_url(),
        );
    }
    
    /**
     * Enqueue scripts on authorization page
     */
    public function enqueue_authorization_scripts($hook) {
        // Only load on the authorize-application page
        if ($hook !== 'authorize-application.php') {
            return;
        }
        
        // Check if this is a Spearhead request
        if (!isset($_GET['app_name']) || strpos($_GET['app_name'], 'Tregnar') !== 0) {
            return;
        }
        
        wp_enqueue_script(
            'spearhead-auth-connector',
            plugin_dir_url(__FILE__) . 'spearhead-auth.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Pass data to JavaScript
        wp_localize_script('spearhead-auth-connector', 'spearhead_auth', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('spearhead/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'success_url' => isset($_GET['success_url']) ? esc_url($_GET['success_url']) : '',
            'app_name' => isset($_GET['app_name']) ? sanitize_text_field($_GET['app_name']) : '',
        ));
    }
    
    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'spearhead_success_url';
        $vars[] = 'spearhead_app_name';
        return $vars;
    }
    
    /**
     * Override HTTPS checks specifically for Spearhead requests
     */
    public function override_https_checks_for_spearhead() {
        // Only apply on admin pages
        if (!is_admin()) {
            return;
        }
        
        // Check if this is an authorization page request with Tregnar
        if (isset($_GET['app_name']) && strpos($_GET['app_name'], 'Tregnar') === 0) {
            // Check if we have a localhost success URL
            if (isset($_GET['success_url'])) {
                $success_url = urldecode($_GET['success_url']);
                
                // If it's a development URL, override HTTPS checks
                if (strpos($success_url, 'http://localhost') !== false || 
                    strpos($success_url, 'http://127.0.0.1') !== false ||
                    strpos($success_url, 'http://192.168.') !== false) {
                    
                    // Force WordPress to think it's using HTTPS
                    if (!defined('FORCE_SSL_ADMIN')) {
                        define('FORCE_SSL_ADMIN', false);
                    }
                    
                    // Override the application password availability check
                    add_filter('wp_is_application_passwords_available', '__return_true', 1);
                    add_filter('wp_is_application_passwords_available_for_user', '__return_true', 1);
                    
                    // Override HTTPS detection
                    add_filter('wp_is_using_https', '__return_true', 1);
                    
                    // Modify server variables to fake HTTPS
                    if (!isset($_SERVER['HTTPS'])) {
                        $_SERVER['HTTPS'] = 'on';
                    }
                    $_SERVER['SERVER_PORT'] = 443;
                    
                    // Set a flag so we know we've overridden
                    define('SPEARHEAD_OVERRIDE_HTTPS', true);
                }
            }
        }
    }
    
    /**
     * Modify authorization page behavior
     */
    public function modify_authorization_page() {
        // Check if we're on the authorization page
        if (!isset($_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], 'authorize-application.php') === false) {
            return;
        }
        
        // Check for Spearhead/Tregnar requests
        if (!isset($_GET['app_name']) || strpos($_GET['app_name'], 'Tregnar') !== 0) {
            return;
        }
        
        // For Spearhead requests with localhost callback, bypass HTTPS requirement
        if (isset($_GET['success_url'])) {
            $success_url = $_GET['success_url'];
            
            // Check if this is a localhost/development URL
            if (strpos($success_url, 'localhost') !== false || 
                strpos($success_url, '127.0.0.1') !== false ||
                strpos($success_url, '192.168.') !== false ||
                strpos($success_url, '10.0.') !== false) {
                
                // Hook into WordPress's HTTPS check to bypass for localhost
                add_filter('wp_is_application_passwords_available', '__return_true', 999);
                add_filter('wp_is_application_passwords_available_for_user', '__return_true', 999);
                
                // Override the HTTPS requirement check
                add_filter('wp_is_using_https', '__return_true', 999);
                add_filter('wp_is_https_supported', '__return_true', 999);
            }
            
            // Store the success URL in a transient for later use
            $user_id = get_current_user_id();
            if ($user_id) {
                set_transient('spearhead_success_url_' . $user_id, esc_url_raw($success_url), 300); // 5 minutes
            }
        }
    }
}

// Initialize the connector very early
add_action('plugins_loaded', function() {
    // Check for Spearhead requests IMMEDIATELY
    if (isset($_GET['app_name']) && strpos($_GET['app_name'], 'Tregnar') === 0) {
        // If we have a localhost success URL, override HTTPS immediately
        if (isset($_GET['success_url'])) {
            $success_url = urldecode($_GET['success_url']);
            if (strpos($success_url, 'http://localhost') !== false || 
                strpos($success_url, 'http://127.0.0.1') !== false ||
                strpos($success_url, 'http://192.168.') !== false) {
                
                // Force HTTPS environment BEFORE WordPress checks
                $_SERVER['HTTPS'] = 'on';
                $_SERVER['SERVER_PORT'] = 443;
                $_SERVER['REQUEST_SCHEME'] = 'https';
                
                // Override all possible HTTPS checks
                add_filter('wp_is_application_passwords_available', '__return_true', 1);
                add_filter('wp_is_application_passwords_available_for_user', '__return_true', 1);
                add_filter('wp_is_using_https', '__return_true', 1);
                add_filter('wp_is_https_supported', '__return_true', 1);
                add_filter('wp_is_site_url_using_https', '__return_true', 1);
                add_filter('wp_is_home_url_using_https', '__return_true', 1);
                
                // Also override the is_ssl() function result
                add_filter('is_ssl', '__return_true', 1);
            }
        }
    }
    
    Spearhead_Application_Password_Connector::get_instance();
}, 1); // Priority 1 to run very early