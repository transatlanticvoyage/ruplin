<?php
/**
 * Ketch Width Management REST API Endpoints
 * Handles Supabase webhook triggers and CSS generation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ketch_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('snefuru/v1', '/ketch/trigger-css-update', array(
            'methods' => 'POST',
            'callback' => array($this, 'trigger_css_update'),
            'permission_callback' => '__return_true', // Public access for Supabase webhook
        ));
        
        register_rest_route('snefuru/v1', '/ketch/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'ketch_status'),
            'permission_callback' => '__return_true', // Public access
        ));
    }
    
    /**
     * Handle CSS update trigger from Supabase
     */
    public function trigger_css_update($request) {
        try {
            // Log the webhook trigger
            error_log('Ketch API: CSS update triggered from Supabase');
            
            // Query Supabase for current settings
            $settings = $this->query_ketch_width_settings();
            
            if ($settings === false) {
                return new WP_REST_Response([
                    'success' => false,
                    'error' => 'Failed to fetch settings from Supabase database',
                    'message' => 'Check Supabase credentials in Settings → Ketch Width Manager'
                ], 500);
            }
            
            // Generate CSS content
            $css_content = $this->generate_ketch_css($settings);
            
            // Write CSS file
            $write_success = $this->write_ketch_css_file($css_content);
            
            if (!$write_success) {
                return new WP_REST_Response([
                    'success' => false,
                    'error' => 'Failed to write CSS file',
                    'message' => 'Check file permissions on wp-content directory'
                ], 500);
            }
            
            // Success response
            return new WP_REST_Response([
                'success' => true,
                'message' => 'CSS file updated successfully',
                'data' => [
                    'settings_count' => count($settings),
                    'css_length' => strlen($css_content),
                    'css_file_path' => 'wp-content/ketch/ketch-widths-all.css',
                    'timestamp' => current_time('mysql'),
                    'trigger_source' => 'supabase_webhook'
                ]
            ], 200);
            
        } catch (Exception $e) {
            error_log('Ketch API: Exception - ' . $e->getMessage());
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get Ketch system status
     */
    public function ketch_status($request) {
        $ketch_options = get_option('ketch_options', array());
        $css_file_path = ABSPATH . 'wp-content/ketch/ketch-widths-all.css';
        $css_dir_path = ABSPATH . 'wp-content/ketch/';
        
        $status = [
            'system' => 'Ketch Width Management System',
            'version' => '1.0.0',
            'timestamp' => current_time('mysql'),
            'configuration' => [
                'supabase_url_configured' => !empty($ketch_options['supabase_url']),
                'supabase_key_configured' => !empty($ketch_options['supabase_anon_key']),
                'css_file_path' => $ketch_options['css_file_path'] ?? 'wp-content/ketch/ketch-widths-all.css'
            ],
            'file_system' => [
                'css_directory_exists' => is_dir($css_dir_path),
                'css_directory_writable' => is_writable(dirname($css_dir_path)),
                'css_file_exists' => file_exists($css_file_path),
                'css_file_size' => file_exists($css_file_path) ? filesize($css_file_path) : 0,
                'css_file_modified' => file_exists($css_file_path) ? date('Y-m-d H:i:s', filemtime($css_file_path)) : null
            ]
        ];
        
        return new WP_REST_Response($status, 200);
    }
    
    /**
     * Query Supabase for ketch width settings
     */
    private function query_ketch_width_settings() {
        // Get Supabase connection details from WordPress options
        $ketch_options = get_option('ketch_options', array());
        $supabase_url = isset($ketch_options['supabase_url']) ? $ketch_options['supabase_url'] : '';
        $supabase_key = isset($ketch_options['supabase_anon_key']) ? $ketch_options['supabase_anon_key'] : '';
        
        if (empty($supabase_url) || empty($supabase_key)) {
            error_log('Ketch API: Missing Supabase credentials. Please configure in Settings → Ketch Width Manager');
            return false;
        }
        
        $endpoint = $supabase_url . '/rest/v1/ketch_width_settings?select=*&order=rel_ui_table_grid.asc,rel_db_field.asc';
        
        $headers = [
            'apikey: ' . $supabase_key,
            'Authorization: Bearer ' . $supabase_key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Ketch-API/1.0 WordPress/' . get_bloginfo('version'));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            error_log('Ketch API: cURL error - ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($http_code !== 200) {
            error_log('Ketch API: HTTP error ' . $http_code . ' - ' . $response);
            return false;
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Ketch API: JSON decode error - ' . json_last_error_msg());
            return false;
        }
        
        return $data;
    }
    
    /**
     * Generate CSS from ketch width settings
     */
    private function generate_ketch_css($settings) {
        if (empty($settings)) {
            return "/* No ketch width settings found */\n";
        }
        
        $css = "/* Ketch Width Management System - Auto-generated CSS */\n";
        $css .= "/* Generated on: " . current_time('Y-m-d H:i:s') . " */\n";
        $css .= "/* WordPress Site: " . get_bloginfo('name') . " */\n";
        $css .= "/* Total Settings: " . count($settings) . " */\n\n";
        
        // Group settings by rel_ui_table_grid
        $grouped_settings = [];
        foreach ($settings as $setting) {
            $grid = $setting['rel_ui_table_grid'];
            if (!isset($grouped_settings[$grid])) {
                $grouped_settings[$grid] = [];
            }
            $grouped_settings[$grid][] = $setting;
        }
        
        // Generate CSS sections for each UI table grid
        foreach ($grouped_settings as $grid_name => $grid_settings) {
            $css .= "/* ========== {$grid_name} UI Table Grid ========== */\n";
            
            foreach ($grid_settings as $setting) {
                $width_px = $setting['width_pon'];
                
                if ($setting['is_kustom_col'] && !empty($setting['kustom_col_id'])) {
                    // Custom column
                    $selector = ".ketch-{$grid_name} .ketch-col-{$setting['kustom_col_id']}";
                    $css .= "{$selector} {\n";
                    $css .= "  width: {$width_px}px;\n";
                    $css .= "  min-width: {$width_px}px;\n";
                    $css .= "  max-width: {$width_px}px;\n";
                    $css .= "}\n\n";
                } else {
                    // Regular database field column
                    $field = $setting['rel_db_field'];
                    $selector = ".ketch-{$grid_name} .ketch-col-{$field}";
                    $css .= "{$selector} {\n";
                    $css .= "  width: {$width_px}px;\n";
                    $css .= "  min-width: {$width_px}px;\n";
                    $css .= "  max-width: {$width_px}px;\n";
                    $css .= "}\n\n";
                }
            }
            
            $css .= "\n";
        }
        
        return $css;
    }
    
    /**
     * Write CSS to file
     */
    private function write_ketch_css_file($css_content) {
        // Get CSS file path from options
        $ketch_options = get_option('ketch_options', array());
        $css_relative_path = isset($ketch_options['css_file_path']) ? $ketch_options['css_file_path'] : 'wp-content/ketch/ketch-widths-all.css';
        
        // Ensure ketch directory exists
        $ketch_dir = ABSPATH . 'wp-content/ketch';
        if (!file_exists($ketch_dir)) {
            if (!wp_mkdir_p($ketch_dir)) {
                error_log('Ketch API: Failed to create ketch directory');
                return false;
            }
        }
        
        $css_file = $ketch_dir . '/ketch-widths-all.css';
        
        $result = file_put_contents($css_file, $css_content);
        
        if ($result === false) {
            error_log('Ketch API: Failed to write CSS file');
            return false;
        }
        
        error_log('Ketch API: Successfully wrote ' . strlen($css_content) . ' bytes to CSS file');
        return true;
    }
}

// Initialize the API class
new Ketch_API();