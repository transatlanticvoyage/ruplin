<?php

class Vulture_Controller {
    
    private $db;
    
    public function __construct() {
        $this->db = new Vulture_DB();
    }
    
    public function run_flatten($options = array()) {
        $defaults = array(
            'include_pages' => true,
            'include_posts' => false,
            'generate_zip' => false
        );
        $options = wp_parse_args($options, $defaults);
        
        $folder_number = $this->db->get_next_folder_number();
        $domain = $this->get_domain();
        
        $base_output_dir = WP_CONTENT_DIR . '/vulture_txt_flattener_outputs/';
        $folder_name = $folder_number . '_' . $domain;
        $full_output_dir = $base_output_dir . $folder_name . '/iteration_1_original_dont_edit/';
        
        if (!file_exists($full_output_dir)) {
            wp_mkdir_p($full_output_dir);
        }
        
        $generation_id = $this->db->insert_generation(array(
            'folder_number' => $folder_number,
            'domain' => $domain,
            'folder_path' => $folder_name . '/iteration_1_original_dont_edit/',
            'include_pages' => $options['include_pages'] ? 1 : 0,
            'include_posts' => $options['include_posts'] ? 1 : 0,
            'status' => 'processing'
        ));
        
        if (!$generation_id) {
            return array('success' => false, 'error' => 'Failed to create generation record');
        }
        
        $total_files = 0;
        $total_pages = 0;
        $total_posts = 0;
        
        if ($options['include_pages']) {
            $pages = $this->get_all_pages_sorted();
            foreach ($pages as $page) {
                $this->write_txt_file($page, $full_output_dir);
                $total_pages++;
                $total_files++;
            }
        }
        
        if ($options['include_posts']) {
            $posts = $this->get_all_posts();
            foreach ($posts as $post) {
                $this->write_txt_file($post, $full_output_dir);
                $total_posts++;
                $total_files++;
            }
        }
        
        $zip_path = null;
        if ($options['generate_zip']) {
            $zip_filename = $folder_name . '.zip';
            $zip_full_path = $base_output_dir . $zip_filename;
            if ($this->create_zip($full_output_dir, $zip_full_path)) {
                $zip_path = $zip_filename;
            }
        }
        
        $this->db->update_generation($generation_id, array(
            'total_pages' => $total_pages,
            'total_posts' => $total_posts,
            'total_files' => $total_files,
            'zip_path' => $zip_path,
            'status' => 'complete',
            'completed_at' => current_time('mysql')
        ));
        
        return $this->db->get_generation($generation_id);
    }
    
    private function get_domain() {
        $siteurl = get_option('siteurl');
        $domain = parse_url($siteurl, PHP_URL_HOST);
        $domain = preg_replace('/^www\./', '', $domain);
        return $domain;
    }
    
    private function get_all_pages_sorted() {
        global $wpdb;
        
        $query = "
            SELECT p1.*, 
                   COALESCE((
                       SELECT COUNT(*) 
                       FROM {$wpdb->posts} p2 
                       WHERE p2.ID = p1.post_parent
                   ), 0) as parent_depth
            FROM {$wpdb->posts} p1
            WHERE p1.post_type = 'page' 
            AND p1.post_status = 'publish'
            ORDER BY parent_depth ASC, p1.post_parent ASC, p1.menu_order ASC, p1.post_title ASC
        ";
        
        return $wpdb->get_results($query);
    }
    
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
    
    private function write_txt_file($post, $output_dir) {
        $content = $this->build_txt_content($post);
        
        $wp_full_path = '';
        if ($post->post_type === 'page') {
            if (get_option('page_on_front') == $post->ID) {
                $filename = 'front_page.txt';
            } else {
                $wp_full_path = get_page_uri($post->ID);
                $filename = str_replace('/', '__', $wp_full_path) . '.txt';
            }
        } else {
            $filename = $post->post_name . '.txt';
            $wp_full_path = $post->post_name;
        }
        
        $file_path = $output_dir . $filename;
        file_put_contents($file_path, $content);
    }
    
    private function build_txt_content($post) {
        global $wpdb;
        
        $domain = $this->get_domain();
        $is_front_page = (get_option('page_on_front') == $post->ID) ? 'TRUE' : 'FALSE';
        
        $wp_full_path = '';
        if ($post->post_type === 'page') {
            $wp_full_path = get_page_uri($post->ID);
        } else {
            $wp_full_path = $post->post_name;
        }
        
        $parent_post_name = '';
        $wp_parent_slug = '';
        if ($post->post_parent > 0) {
            $parent = get_post($post->post_parent);
            if ($parent) {
                $parent_post_name = $parent->post_name;
                $wp_parent_slug = get_page_uri($parent->ID);
            }
        }
        
        $permalink = get_permalink($post->ID);
        
        $word_count = str_word_count(strip_tags($post->post_content));
        
        preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $post->post_content, $h1_matches);
        $content_h1 = isset($h1_matches[1]) ? strip_tags($h1_matches[1]) : '';
        
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $post->post_content, $h2_matches);
        $content_h2s = !empty($h2_matches[1]) ? implode(', ', array_map('strip_tags', $h2_matches[1])) : '';
        
        preg_match_all('/<img[^>]+>/i', $post->post_content, $img_matches);
        $content_images_count = count($img_matches[0]);
        
        $meta_title = $post->post_title;
        $meta_description = '';
        $meta_keywords = '';
        
        $yoast_title = get_post_meta($post->ID, '_yoast_wpseo_title', true);
        if ($yoast_title) {
            $meta_title = $yoast_title;
        }
        
        $rank_math_title = get_post_meta($post->ID, 'rank_math_title', true);
        if ($rank_math_title) {
            $meta_title = $rank_math_title;
        }
        
        $yoast_desc = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
        if ($yoast_desc) {
            $meta_description = $yoast_desc;
        }
        
        $rank_math_desc = get_post_meta($post->ID, 'rank_math_description', true);
        if ($rank_math_desc) {
            $meta_description = $rank_math_desc;
        }
        
        $pylons_data = $wpdb->get_row($wpdb->prepare(
            "SELECT staircase_page_template_desired, vectornode_meta_title, vectornode_meta_description 
             FROM {$wpdb->prefix}pylons 
             WHERE rel_wp_post_id = %d 
             LIMIT 1",
            $post->ID
        ));
        
        $staircase_template = '';
        $vectornode_meta_title = '';
        $vectornode_meta_description = '';
        
        if ($pylons_data) {
            $staircase_template = $pylons_data->staircase_page_template_desired ?: '';
            $vectornode_meta_title = $pylons_data->vectornode_meta_title ?: '';
            $vectornode_meta_description = $pylons_data->vectornode_meta_description ?: '';
        }
        
        $content = "======================================================================\n";
        $content .= "VULTURE TXT FLATTENER\n";
        $content .= "Domain:   {$domain}\n";
        $content .= "Generated: " . current_time('Y-m-d H:i:s') . "\n";
        $content .= "======================================================================\n\n";
        
        $content .= "### general meta info for this page\n";
        $content .= "wp_full_path: {$wp_full_path}\n";
        $content .= "is_front_page: {$is_front_page}\n";
        $content .= "is_pdf: FALSE\n";
        $content .= "notes: \n\n";
        
        $content .= "### wp_pylons.staircase_page_template_desired\n";
        $content .= "{$staircase_template}\n\n";
        
        $content .= "### post_id\n";
        $content .= "{$post->ID}\n\n";
        
        $content .= "### post_title\n";
        $content .= "{$post->post_title}\n\n";
        
        $content .= "### post_status\n";
        $content .= "{$post->post_status}\n\n";
        
        $content .= "### post_type\n";
        $content .= "{$post->post_type}\n\n";
        
        $content .= "### post_parent\n";
        $content .= "{$parent_post_name}\n\n";
        
        $content .= "### post_name\n";
        $content .= "{$post->post_name}\n\n";
        
        $content .= "### post_date\n";
        $content .= "{$post->post_date}\n\n";
        
        $content .= "### post_date_source\n";
        $content .= "wp_db\n\n";
        
        $content .= "### post_title\n";
        $content .= "{$post->post_title}\n\n";
        
        $content .= "### post_content\n";
        $content .= "{$post->post_content}\n\n";
        
        $content .= "### post_excerpt\n";
        $content .= "{$post->post_excerpt}\n\n";
        
        $content .= "### post_modified\n";
        $content .= "{$post->post_modified}\n\n";
        
        $content .= "### post_modified_gmt\n";
        $content .= "{$post->post_modified_gmt}\n\n";
        
        $content .= "### post_author\n";
        $content .= "{$post->post_author}\n\n";
        
        $content .= "### post_password\n";
        $content .= "{$post->post_password}\n\n";
        
        $content .= "### post_mime_type\n";
        $content .= "text/html\n\n";
        
        $content .= "### comment_status\n";
        $content .= "{$post->comment_status}\n\n";
        
        $content .= "### ping_status\n";
        $content .= "{$post->ping_status}\n\n";
        
        $content .= "### menu_order\n";
        $content .= "{$post->menu_order}\n\n";
        
        $content .= "### guid\n";
        $content .= "{$post->guid}\n\n";
        
        $content .= "### meta_title\n";
        $content .= "{$meta_title}\n\n";
        
        $content .= "### meta_description\n";
        $content .= "{$meta_description}\n\n";
        
        $content .= "### meta_keywords\n";
        $content .= "{$meta_keywords}\n\n";
        
        $content .= "### meta_lang\n";
        $content .= "en\n\n";
        
        $content .= "### wp_parent_slug\n";
        $content .= "{$wp_parent_slug}\n\n";
        
        $content .= "### wp_parent_post_name\n";
        $content .= "{$parent_post_name}\n\n";
        
        $content .= "### wp_full_path\n";
        $content .= "{$wp_full_path}\n\n";
        
        $content .= "### wp_slug\n";
        $content .= "{$post->post_name}\n\n";
        
        $content .= "### wp_permalink\n";
        $content .= "{$permalink}\n\n";
        
        $content .= "### content_word_count\n";
        $content .= "{$word_count}\n\n";
        
        $content .= "### content_h1\n";
        $content .= "{$content_h1}\n\n";
        
        $content .= "### content_h2s\n";
        $content .= "{$content_h2s}\n\n";
        
        $content .= "### content_images_count\n";
        $content .= "{$content_images_count}\n\n";
        
        $content .= "### redirect_from_old_url\n";
        $content .= "\n\n";
        
        $content .= "### vectornode_meta_title\n";
        $content .= "{$vectornode_meta_title}\n\n";
        
        $content .= "### vectornode_meta_description\n";
        $content .= "{$vectornode_meta_description}\n\n";
        
        $content .= "### staircase_page_template_desired\n";
        $content .= "{$staircase_template}\n";
        
        return $content;
    }
    
    private function create_zip($source_dir, $zip_path) {
        if (!class_exists('ZipArchive')) {
            return false;
        }
        
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
            return false;
        }
        
        $source_dir = rtrim($source_dir, '/') . '/';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $file_path = $file->getRealPath();
            $relative_path = str_replace($source_dir, '', $file_path);
            
            if ($file->isDir()) {
                $zip->addEmptyDir($relative_path);
            } else {
                $zip->addFile($file_path, $relative_path);
            }
        }
        
        $zip->close();
        return true;
    }
}