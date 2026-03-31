<?php

class Vulture_DB {
    
    private $wpdb;
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'vulture_txt_flattener_generations';
    }
    
    public function insert_generation($data) {
        $defaults = array(
            'folder_number' => 0,
            'site_domain' => '',
            'iteration_number' => 1,
            'output_path' => '',
            'page_count' => 0,
            'post_count' => 0,
            'total_files' => 0,
            'zip_filename' => null,
            'zip_path' => null,
            'status' => 'pending',
            'triggered_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'completed_at' => null,
            'notes' => null
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
    
    public function get_next_folder_number() {
        $max_from_db = $this->wpdb->get_var(
            "SELECT MAX(folder_number) FROM {$this->table_name}"
        );
        
        $max_from_filesystem = $this->scan_filesystem_for_max_folder_number();
        
        $current_max = max(
            $max_from_db ? intval($max_from_db) : 12,
            $max_from_filesystem ? intval($max_from_filesystem) : 12
        );
        
        if ($current_max < 13) {
            return 13;
        }
        
        return $current_max + 1;
    }
    
    private function scan_filesystem_for_max_folder_number() {
        $output_dir = WP_CONTENT_DIR . '/vulture_txt_flattener_outputs/';
        
        if (!is_dir($output_dir)) {
            return 12;
        }
        
        $max_number = 12;
        $dirs = scandir($output_dir);
        
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            
            if (preg_match('/^(\d+)_/', $dir, $matches)) {
                $number = intval($matches[1]);
                if ($number > $max_number) {
                    $max_number = $number;
                }
            }
        }
        
        return $max_number;
    }
}