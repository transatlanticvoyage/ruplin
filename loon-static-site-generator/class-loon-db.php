<?php

/**
 * Loon Static Site Generator Database Class
 * 
 * Handles all database operations for Loon generations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Loon_DB {
    
    private $wpdb;
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'loon_static_site_generations';
    }
    
    public function insert_generation($data = array()) {
        $defaults = array(
            'folder_number' => 0,
            'site_domain' => '',
            'output_path' => '',
            'page_count' => 0,
            'post_count' => 0,
            'total_files' => 0,
            'total_size_mb' => 0.00,
            'zip_filename' => null,
            'zip_path' => null,
            'status' => 'pending',
            'options_json' => null,
            'error_message' => null,
            'created_at' => current_time('mysql'),
            'completed_at' => null
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $this->wpdb->insert(
            $this->table_name,
            $data
        );
        
        if ($result === false) {
            return false;
        }
        
        return $this->wpdb->insert_id;
    }
    
    public function update_generation($generation_id, $data) {
        return $this->wpdb->update(
            $this->table_name,
            $data,
            array('generation_id' => $generation_id),
            null,
            array('%d')
        );
    }
    
    public function get_all_generations() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY generation_id DESC";
        return $this->wpdb->get_results($query, ARRAY_A);
    }
    
    public function get_generation($generation_id) {
        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE generation_id = %d",
            $generation_id
        );
        return $this->wpdb->get_row($query, ARRAY_A);
    }
    
    /**
     * Get next folder number for generation
     * Mimics Vulture's logic: checks both DB and filesystem
     */
    public function get_next_folder_number($base_dir = null) {
        // Get max from database
        $max_from_db = $this->wpdb->get_var(
            "SELECT MAX(folder_number) FROM {$this->table_name}"
        );
        
        // Get max from filesystem
        $max_from_filesystem = $this->scan_filesystem_for_max_folder_number($base_dir);
        
        // Determine current max (minimum baseline of 12)
        $current_max = max(
            $max_from_db ? intval($max_from_db) : 12,
            $max_from_filesystem ? intval($max_from_filesystem) : 12
        );
        
        // Start at 13 minimum
        if ($current_max < 13) {
            return 13;
        }
        
        return $current_max + 1;
    }
    
    /**
     * Scan filesystem for existing folder numbers
     */
    private function scan_filesystem_for_max_folder_number($base_dir = null) {
        // Try both possible output directories
        $dirs_to_check = array();
        
        // Local directory option
        $local_dir = '/Users/kylecampbell/Documents/repos/loon-static-site-outputs/';
        if (is_dir($local_dir)) {
            $dirs_to_check[] = $local_dir;
        }
        
        // WP content directory option
        $wp_dir = WP_CONTENT_DIR . '/loon-static-site-outputs/';
        if (is_dir($wp_dir)) {
            $dirs_to_check[] = $wp_dir;
        }
        
        // If specific base_dir provided, use only that
        if ($base_dir && is_dir($base_dir)) {
            $dirs_to_check = array($base_dir);
        }
        
        $max_number = 12;
        
        foreach ($dirs_to_check as $output_dir) {
            if (!is_dir($output_dir)) {
                continue;
            }
            
            $dirs = scandir($output_dir);
            
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }
                
                // Match pattern: {number}_{domain}
                if (preg_match('/^(\d+)_/', $dir, $matches)) {
                    $folder_number = intval($matches[1]);
                    if ($folder_number > $max_number) {
                        $max_number = $folder_number;
                    }
                }
            }
        }
        
        return $max_number;
    }
    
    /**
     * Check if we're on localhost
     */
    public function is_localhost() {
        $whitelist = array('127.0.0.1', '::1');
        
        if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
            return true;
        }
        
        // Check for local development domains
        $local_domains = array('localhost', '.local', '.test', '.dev');
        $current_domain = parse_url(get_site_url(), PHP_URL_HOST);
        
        foreach ($local_domains as $local_domain) {
            if (stripos($current_domain, $local_domain) !== false) {
                return true;
            }
        }
        
        return false;
    }
}