<?php

class Vulture_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_vulture_flatten_site', array($this, 'handle_flatten_site'));
        add_action('wp_ajax_vulture_download_zip', array($this, 'handle_download_zip'));
    }
    
    public function handle_flatten_site() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('vulture_flatten_nonce', 'nonce');
        
        $include_pages = isset($_POST['include_pages']) && $_POST['include_pages'] === 'true';
        $include_posts = isset($_POST['include_posts']) && $_POST['include_posts'] === 'true';
        $generate_zip = isset($_POST['generate_zip']) && $_POST['generate_zip'] === 'true';
        
        $controller = new Vulture_Controller();
        $result = $controller->run_flatten(array(
            'include_pages' => $include_pages,
            'include_posts' => $include_posts,
            'generate_zip' => $generate_zip
        ));
        
        if ($result && $result['status'] === 'complete') {
            wp_send_json_success(array(
                'generation_id' => $result['generation_id'],
                'folder_number' => $result['folder_number'],
                'total_files' => $result['total_files'],
                'page_count' => $result['page_count'],
                'post_count' => $result['post_count'],
                'zip_filename' => $result['zip_filename'],
                'message' => sprintf(
                    'Successfully flattened %d pages and %d posts into %d files', 
                    $result['page_count'], 
                    $result['post_count'], 
                    $result['total_files']
                )
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Failed to flatten site'
            ));
        }
    }
    
    public function handle_download_zip() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('vulture_download_nonce', 'nonce');
        
        $generation_id = intval($_POST['generation_id']);
        if (!$generation_id) {
            wp_die('Invalid generation ID');
        }
        
        $db = new Vulture_DB();
        $generation = $db->get_generation($generation_id);
        
        if (!$generation || !$generation['zip_path']) {
            wp_die('ZIP file not found');
        }
        
        $zip_full_path = WP_CONTENT_DIR . '/vulture_txt_flattener_outputs/' . $generation['zip_path'];
        
        if (!file_exists($zip_full_path)) {
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