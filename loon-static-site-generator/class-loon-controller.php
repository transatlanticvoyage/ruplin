<?php

/**
 * Loon Static Site Generator Controller Class
 * 
 * Handles the static site generation process
 */

if (!defined('ABSPATH')) {
    exit;
}

class Loon_Controller {
    
    private $db;
    
    public function __construct() {
        $this->db = new Loon_DB();
    }
    
    /**
     * Run the static site generation
     */
    public function run_generation($options = array()) {
        $defaults = array(
            'include_pages' => true,
            'include_posts' => true,
            'generate_zip' => true,
            'output_location' => 'wp_content' // 'local' or 'wp_content'
        );
        $options = wp_parse_args($options, $defaults);
        
        // Determine base output directory
        $base_output_dir = $this->get_base_output_dir($options['output_location']);
        
        // Get folder number and domain
        $folder_number = $this->db->get_next_folder_number($base_output_dir);
        $domain = $this->get_domain();
        
        // Create folder structure
        $folder_name = $folder_number . '_' . $domain;
        $full_output_dir = $base_output_dir . $folder_name . '/static_v1/';
        
        // Create directories
        if (!file_exists($full_output_dir)) {
            wp_mkdir_p($full_output_dir);
        }
        
        // Store options as JSON
        $options_json = json_encode($options);
        
        // Create database record
        $generation_id = $this->db->insert_generation(array(
            'folder_number' => $folder_number,
            'site_domain' => $domain,
            'output_path' => $folder_name . '/static_v1/',
            'status' => 'processing',
            'options_json' => $options_json
        ));
        
        if (!$generation_id) {
            return array(
                'success' => false,
                'error' => 'Failed to create generation record'
            );
        }
        
        // Initialize counters
        $total_files = 0;
        $total_pages = 0;
        $total_posts = 0;
        $total_size = 0;
        
        try {
            // Generate pages
            if ($options['include_pages']) {
                $pages = $this->get_all_pages();
                foreach ($pages as $page) {
                    $this->generate_static_html($page, $full_output_dir);
                    $total_pages++;
                    $total_files++;
                }
            }
            
            // Generate posts
            if ($options['include_posts']) {
                $posts = $this->get_all_posts();
                foreach ($posts as $post) {
                    $this->generate_static_html($post, $full_output_dir);
                    $total_posts++;
                    $total_files++;
                }
            }
            
            // Calculate total size
            $total_size = $this->calculate_directory_size($full_output_dir);
            $total_size_mb = round($total_size / (1024 * 1024), 2);
            
            // Generate ZIP if requested
            $zip_path = null;
            $zip_filename = null;
            if ($options['generate_zip']) {
                $zip_filename = $folder_name . '.zip';
                $zip_full_path = $base_output_dir . $zip_filename;
                
                if ($this->create_zip($full_output_dir, $zip_full_path)) {
                    $zip_path = $zip_filename;
                }
            }
            
            // Update database record
            $this->db->update_generation($generation_id, array(
                'page_count' => $total_pages,
                'post_count' => $total_posts,
                'total_files' => $total_files,
                'total_size_mb' => $total_size_mb,
                'zip_filename' => $zip_filename,
                'zip_path' => $zip_path,
                'status' => 'complete',
                'completed_at' => current_time('mysql')
            ));
            
            return array(
                'success' => true,
                'generation_id' => $generation_id,
                'folder_number' => $folder_number,
                'folder_name' => $folder_name,
                'total_files' => $total_files,
                'page_count' => $total_pages,
                'post_count' => $total_posts,
                'total_size_mb' => $total_size_mb,
                'zip_filename' => $zip_filename,
                'output_path' => $full_output_dir
            );
            
        } catch (Exception $e) {
            // Update database with error
            $this->db->update_generation($generation_id, array(
                'status' => 'error',
                'error_message' => $e->getMessage()
            ));
            
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Get base output directory based on option
     */
    private function get_base_output_dir($location) {
        if ($location === 'local') {
            $dir = '/Users/kylecampbell/Documents/repos/loon-static-site-outputs/';
        } else {
            $dir = WP_CONTENT_DIR . '/loon-static-site-outputs/';
        }
        
        // Create if doesn't exist
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        
        return $dir;
    }
    
    /**
     * Get domain without www
     */
    private function get_domain() {
        $siteurl = get_option('siteurl');
        $domain = parse_url($siteurl, PHP_URL_HOST);
        $domain = preg_replace('/^www\./', '', $domain);
        return $domain;
    }
    
    /**
     * Get all published pages
     */
    private function get_all_pages() {
        global $wpdb;
        
        $query = "
            SELECT * FROM {$wpdb->posts}
            WHERE post_type = 'page' 
            AND post_status = 'publish'
            ORDER BY menu_order ASC, post_title ASC
        ";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get all published posts
     */
    private function get_all_posts() {
        global $wpdb;
        
        $query = "
            SELECT * FROM {$wpdb->posts}
            WHERE post_type = 'post' 
            AND post_status = 'publish'
            ORDER BY post_date DESC
        ";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Generate static HTML for a post/page
     */
    private function generate_static_html($post, $output_dir) {
        // TODO: Implement full HTML generation
        // For now, create a basic HTML file
        
        $content = $this->build_html_content($post);
        
        // Determine filename
        if ($post->post_type === 'page') {
            if (get_option('page_on_front') == $post->ID) {
                $filename = 'index.html';
            } else {
                $slug = $post->post_name;
                $filename = $slug . '.html';
            }
        } else {
            $filename = $post->post_name . '.html';
        }
        
        $file_path = $output_dir . $filename;
        file_put_contents($file_path, $content);
    }
    
    /**
     * Build HTML content for a post/page
     */
    private function build_html_content($post) {
        $title = get_the_title($post->ID);
        $content = apply_filters('the_content', $post->post_content);
        $permalink = get_permalink($post->ID);
        
        // Basic HTML template
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html($title) . '</title>
    <link rel="canonical" href="' . esc_url($permalink) . '">
</head>
<body>
    <h1>' . esc_html($title) . '</h1>
    <div class="content">
        ' . $content . '
    </div>
    <!-- Generated by Loon Static Site Generator -->
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Calculate directory size in bytes
     */
    private function calculate_directory_size($dir) {
        $size = 0;
        
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->calculate_directory_size($each);
        }
        
        return $size;
    }
    
    /**
     * Create ZIP archive of directory
     */
    private function create_zip($source, $destination) {
        if (!extension_loaded('zip')) {
            return false;
        }
        
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE) !== TRUE) {
            return false;
        }
        
        $source = realpath($source);
        
        if (is_dir($source)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                $file = realpath($file);
                
                if (is_dir($file)) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file)) {
                    $zip->addFile($file, str_replace($source . '/', '', $file));
                }
            }
        } else if (is_file($source)) {
            $zip->addFile($source, basename($source));
        }
        
        return $zip->close();
    }
}