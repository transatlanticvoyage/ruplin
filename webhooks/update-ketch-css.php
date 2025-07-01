<?php
/**
 * Ketch Width CSS Update Webhook
 * Handles incoming requests from Supabase trigger to regenerate CSS file
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    $wp_config_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-config.php';
    if (file_exists($wp_config_path)) {
        require_once($wp_config_path);
    } else {
        http_response_code(403);
        exit('Access denied');
    }
}

// Security check - verify request origin or add authentication if needed
// For now, accepting all requests. In production, add proper authentication.

// Log webhook receipt
error_log('Ketch CSS Webhook: Received request from Supabase trigger');

/**
 * Query Supabase for ketch width settings
 */
function query_ketch_width_settings() {
    // Get Supabase connection details from WordPress options
    $ketch_options = get_option('ketch_options', array());
    $supabase_url = isset($ketch_options['supabase_url']) ? $ketch_options['supabase_url'] : '';
    $supabase_key = isset($ketch_options['supabase_anon_key']) ? $ketch_options['supabase_anon_key'] : '';
    
    if (empty($supabase_url) || empty($supabase_key)) {
        error_log('Ketch CSS Webhook: Missing Supabase credentials. Please configure in Settings → Ketch Width Manager');
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
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        error_log('Ketch CSS Webhook: cURL error - ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log('Ketch CSS Webhook: HTTP error ' . $http_code . ' - ' . $response);
        return false;
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Ketch CSS Webhook: JSON decode error - ' . json_last_error_msg());
        return false;
    }
    
    return $data;
}

/**
 * Generate CSS from ketch width settings
 */
function generate_ketch_css($settings) {
    if (empty($settings)) {
        return "/* No ketch width settings found */\n";
    }
    
    $css = "/* Ketch Width Management System - Auto-generated CSS */\n";
    $css .= "/* Generated on: " . date('Y-m-d H:i:s') . " UTC */\n\n";
    
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
function write_ketch_css_file($css_content) {
    // Ensure ketch directory exists
    $ketch_dir = ABSPATH . 'wp-content/ketch';
    if (!file_exists($ketch_dir)) {
        if (!wp_mkdir_p($ketch_dir)) {
            error_log('Ketch CSS Webhook: Failed to create ketch directory');
            return false;
        }
    }
    
    $css_file = $ketch_dir . '/ketch-widths-all.css';
    
    $result = file_put_contents($css_file, $css_content);
    
    if ($result === false) {
        error_log('Ketch CSS Webhook: Failed to write CSS file');
        return false;
    }
    
    error_log('Ketch CSS Webhook: Successfully wrote ' . strlen($css_content) . ' bytes to CSS file');
    return true;
}

// Main webhook processing
try {
    // Query Supabase for current settings
    $settings = query_ketch_width_settings();
    
    if ($settings === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to fetch settings from database']);
        exit;
    }
    
    // Generate CSS content
    $css_content = generate_ketch_css($settings);
    
    // Write CSS file
    $write_success = write_ketch_css_file($css_content);
    
    if (!$write_success) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to write CSS file']);
        exit;
    }
    
    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'CSS file updated successfully',
        'settings_count' => count($settings),
        'css_length' => strlen($css_content),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log('Ketch CSS Webhook: Exception - ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}