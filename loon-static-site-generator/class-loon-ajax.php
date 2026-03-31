<?php

/**
 * Loon Static Site Generator AJAX Handler
 * 
 * Handles AJAX requests for static site generation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Loon_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_loon_generate_static_site', array($this, 'handle_generate_static_site'));
        add_action('wp_ajax_loon_download_zip', array($this, 'handle_download_zip'));
    }
    
    /**
     * Handle static site generation request
     */
    public function handle_generate_static_site() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('loon_generate_nonce', 'nonce');
        
        // Get options from POST
        $include_pages = isset($_POST['include_pages']) && $_POST['include_pages'] === 'true';
        $include_posts = isset($_POST['include_posts']) && $_POST['include_posts'] === 'true';
        $generate_zip = isset($_POST['generate_zip']) && $_POST['generate_zip'] === 'true';
        $output_location = isset($_POST['output_location']) ? sanitize_text_field($_POST['output_location']) : 'wp_content';
        
        // Validate output location
        if (!in_array($output_location, array('local', 'wp_content'))) {
            $output_location = 'wp_content';
        }
        
        // Initialize controller
        $controller = new Loon_Controller();
        
        // Run generation
        $result = $controller->run_generation(array(
            'include_pages' => $include_pages,
            'include_posts' => $include_posts,
            'generate_zip' => $generate_zip,
            'output_location' => $output_location
        ));
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => sprintf(
                    'Successfully generated static site: %d pages, %d posts, %d total files (%.2f MB)',
                    $result['page_count'],
                    $result['post_count'],
                    $result['total_files'],
                    $result['total_size_mb']
                ),
                'generation_id' => $result['generation_id'],
                'folder_name' => $result['folder_name'],
                'output_path' => $result['output_path']
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Failed to generate static site: ' . $result['error']
            ));
        }
    }
    
    /**
     * Handle ZIP download request
     */
    public function handle_download_zip() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('loon_download_nonce', 'nonce');
        
        $generation_id = intval($_POST['generation_id']);
        if (!$generation_id) {
            wp_die('Invalid generation ID');
        }
        
        $db = new Loon_DB();
        $generation = $db->get_generation($generation_id);
        
        if (!$generation || !$generation['zip_path']) {
            wp_die('ZIP file not found');
        }
        
        // Check both possible locations
        $possible_paths = array(
            WP_CONTENT_DIR . '/loon-static-site-outputs/' . $generation['zip_path'],
            '/Users/kylecampbell/Documents/repos/loon-static-site-outputs/' . $generation['zip_path']
        );
        
        $zip_full_path = null;
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                $zip_full_path = $path;
                break;
            }
        }
        
        if (!$zip_full_path) {
            wp_die('ZIP file does not exist');
        }
        
        $filename = basename($generation['zip_path']);
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($zip_full_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($zip_full_path);
        exit;
    }
}

// Initialize AJAX handler
new Loon_Ajax();