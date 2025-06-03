<?php

class Snefuru_Data_Collector {
    
    /**
     * Collect comprehensive site data
     */
    public function collect_site_data() {
        return array(
            'site_info' => $this->get_site_info(),
            'performance' => $this->get_performance_data(),
            'security' => $this->get_security_data(),
            'content' => $this->get_content_stats(),
            'users' => $this->get_user_stats(),
            'plugins' => $this->get_plugin_data(),
            'themes' => $this->get_theme_data(),
            'system' => $this->get_system_info()
        );
    }
    
    /**
     * Collect specific type of data as requested by mothership
     */
    public function collect_specific_data($data_type) {
        switch ($data_type) {
            case 'site_info':
                return $this->get_site_info();
            case 'performance':
                return $this->get_performance_data();
            case 'security':
                return $this->get_security_data();
            case 'content':
                return $this->get_content_stats();
            case 'users':
                return $this->get_user_stats();
            case 'plugins':
                return $this->get_plugin_data();
            case 'themes':
                return $this->get_theme_data();
            case 'system':
                return $this->get_system_info();
            default:
                return array();
        }
    }
    
    /**
     * Get basic site information
     */
    private function get_site_info() {
        return array(
            'url' => get_site_url(),
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'admin_email' => get_option('admin_email'),
            'language' => get_locale(),
            'timezone' => get_option('timezone_string'),
            'wp_version' => get_bloginfo('version'),
            'multisite' => is_multisite(),
            'last_updated' => current_time('mysql')
        );
    }
    
    /**
     * Get performance related data
     */
    private function get_performance_data() {
        global $wpdb;
        
        // Database query performance
        $query_time_start = microtime(true);
        $wpdb->get_results("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish'");
        $query_time = microtime(true) - $query_time_start;
        
        return array(
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'db_query_time' => $query_time,
            'active_plugins_count' => count(get_option('active_plugins', array())),
            'database_size' => $this->get_database_size()
        );
    }
    
    /**
     * Get security related information
     */
    private function get_security_data() {
        $security_data = array(
            'wp_version_current' => $this->is_wp_version_current(),
            'ssl_enabled' => is_ssl(),
            'admin_user_exists' => username_exists('admin') !== false,
            'file_permissions' => $this->check_file_permissions(),
            'failed_login_attempts' => $this->get_failed_login_attempts(),
            'last_login' => get_option('snefuru_last_login_check', ''),
            'security_plugins' => $this->get_security_plugins()
        );
        
        return $security_data;
    }
    
    /**
     * Get content statistics
     */
    private function get_content_stats() {
        global $wpdb;
        
        $post_counts = wp_count_posts();
        $page_counts = wp_count_posts('page');
        $comment_counts = wp_count_comments();
        
        return array(
            'posts' => array(
                'published' => $post_counts->publish,
                'draft' => $post_counts->draft,
                'private' => $post_counts->private
            ),
            'pages' => array(
                'published' => $page_counts->publish,
                'draft' => $page_counts->draft
            ),
            'comments' => array(
                'approved' => $comment_counts->approved,
                'pending' => $comment_counts->moderated,
                'spam' => $comment_counts->spam
            ),
            'media_files' => $this->get_media_count(),
            'categories' => wp_count_terms('category'),
            'tags' => wp_count_terms('post_tag')
        );
    }
    
    /**
     * Get user statistics
     */
    private function get_user_stats() {
        $user_counts = count_users();
        
        return array(
            'total_users' => $user_counts['total_users'],
            'roles' => $user_counts['avail_roles'],
            'recent_registrations' => $this->get_recent_user_registrations()
        );
    }
    
    /**
     * Get plugin information
     */
    private function get_plugin_data() {
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        
        $plugin_data = array();
        foreach ($all_plugins as $plugin_path => $plugin_info) {
            $plugin_data[] = array(
                'name' => $plugin_info['Name'],
                'version' => $plugin_info['Version'],
                'active' => in_array($plugin_path, $active_plugins),
                'needs_update' => $this->plugin_needs_update($plugin_path)
            );
        }
        
        return $plugin_data;
    }
    
    /**
     * Get theme information
     */
    private function get_theme_data() {
        $current_theme = wp_get_theme();
        $all_themes = wp_get_themes();
        
        return array(
            'active_theme' => array(
                'name' => $current_theme->get('Name'),
                'version' => $current_theme->get('Version'),
                'template' => $current_theme->get_template()
            ),
            'total_themes' => count($all_themes),
            'theme_updates' => $this->get_theme_updates()
        );
    }
    
    /**
     * Get system information
     */
    private function get_system_info() {
        global $wp_version;
        
        return array(
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->get_mysql_version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'wordpress_version' => $wp_version,
            'max_upload_size' => wp_max_upload_size(),
            'timezone' => date_default_timezone_get(),
            'disk_free_space' => disk_free_space(ABSPATH),
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status()
        );
    }
    
    // Helper methods
    private function get_database_size() {
        global $wpdb;
        $size = $wpdb->get_var("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema='{$wpdb->dbname}'");
        return $size ? $size . ' MB' : 'Unknown';
    }
    
    private function is_wp_version_current() {
        $current = get_bloginfo('version');
        $latest = get_transient('snefuru_wp_version_check');
        if (!$latest) {
            $response = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/');
            if (!is_wp_error($response)) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $latest = $body['offers'][0]['version'] ?? $current;
                set_transient('snefuru_wp_version_check', $latest, HOUR_IN_SECONDS);
            }
        }
        return version_compare($current, $latest, '>=');
    }
    
    private function check_file_permissions() {
        return array(
            'wp_config' => substr(sprintf('%o', fileperms(ABSPATH . 'wp-config.php')), -4),
            'wp_content' => substr(sprintf('%o', fileperms(WP_CONTENT_DIR)), -4),
            'uploads' => substr(sprintf('%o', fileperms(wp_upload_dir()['basedir'])), -4)
        );
    }
    
    private function get_failed_login_attempts() {
        // This would integrate with security plugins or custom logging
        return get_option('snefuru_failed_logins', 0);
    }
    
    private function get_security_plugins() {
        $security_plugins = array('wordfence', 'sucuri', 'ithemes-security', 'jetpack');
        $active_security = array();
        
        foreach ($security_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $active_security[] = $plugin;
            }
        }
        
        return $active_security;
    }
    
    private function get_media_count() {
        return wp_count_posts('attachment')->inherit;
    }
    
    private function get_recent_user_registrations() {
        $users = get_users(array(
            'date_query' => array(
                array(
                    'after' => '1 week ago'
                )
            ),
            'count_total' => true
        ));
        return count($users);
    }
    
    private function plugin_needs_update($plugin_path) {
        $updates = get_site_transient('update_plugins');
        return isset($updates->response[$plugin_path]);
    }
    
    private function get_theme_updates() {
        $updates = get_site_transient('update_themes');
        return $updates ? count($updates->response) : 0;
    }
    
    private function get_mysql_version() {
        global $wpdb;
        return $wpdb->get_var('SELECT VERSION()');
    }
} 