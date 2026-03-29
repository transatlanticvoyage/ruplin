<?php
/**
 * Spearhead MU-Plugin Loader
 * 
 * This file should be copied to wp-content/mu-plugins/ directory
 * to ensure Spearhead HTTPS overrides load before WordPress checks
 * 
 * Instructions:
 * 1. Create directory: wp-content/mu-plugins/ (if it doesn't exist)
 * 2. Copy this file to: wp-content/mu-plugins/spearhead-mu-loader.php
 * 3. It will auto-load on every page load
 */

// Only run if we detect a Spearhead/Tregnar request
if (isset($_GET['app_name']) && strpos($_GET['app_name'], 'Tregnar') === 0) {
    // Check for localhost success URL
    if (isset($_GET['success_url'])) {
        $success_url = urldecode($_GET['success_url']);
        
        // If callback is to localhost/development, override HTTPS checks
        if (strpos($success_url, 'http://localhost') !== false || 
            strpos($success_url, 'http://127.0.0.1') !== false ||
            strpos($success_url, 'http://192.168.') !== false ||
            strpos($success_url, 'http://10.') !== false) {
            
            // Debug logging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Spearhead MU: Overriding HTTPS for localhost callback - ' . $success_url);
            }
            
            // Force HTTPS environment variables
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['SERVER_PORT'] = 443;
            $_SERVER['REQUEST_SCHEME'] = 'https';
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            $_SERVER['HTTP_X_FORWARDED_SSL'] = 'on';
            
            // Define constant to bypass SSL checks
            if (!defined('FORCE_SSL_ADMIN')) {
                define('FORCE_SSL_ADMIN', false);
            }
            
            // Hook into WordPress initialization to override checks
            add_action('muplugins_loaded', function() {
                // Override all HTTPS-related checks
                add_filter('wp_is_application_passwords_available', '__return_true', -999);
                add_filter('wp_is_application_passwords_available_for_user', '__return_true', -999);
                add_filter('wp_is_using_https', '__return_true', -999);
                add_filter('wp_is_https_supported', '__return_true', -999);
                add_filter('is_ssl', '__return_true', -999);
                add_filter('wp_is_site_url_using_https', '__return_true', -999);
                add_filter('wp_is_home_url_using_https', '__return_true', -999);
                
                // Also override user capability checks for app passwords
                add_filter('user_has_cap', function($caps) {
                    $caps['manage_application_passwords'] = true;
                    return $caps;
                }, -999);
            }, -999);
            
            // Set a flag so other code knows we've overridden
            define('SPEARHEAD_HTTPS_OVERRIDE', true);
        }
    }
}