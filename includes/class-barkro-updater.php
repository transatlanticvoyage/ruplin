<?php

class Snefuru_Barkro_Updater {
    
    private $plugin_slug = 'snefuruplin';
    private $plugin_base = 'snefuruplin/snefuru-plugin.php';
    private $update_check_url = '';
    
    public function __construct() {
        // Set update check URL from site settings
        $this->update_check_url = get_option('snefuru_update_check_url', '');
        
        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_action('rest_api_init', array($this, 'register_update_endpoint'));
        
        // Add automatic update support
        add_filter('auto_update_plugin', array($this, 'auto_update_plugin'), 10, 2);
        
        // Track WordPress update process
        add_action('upgrader_process_complete', array($this, 'track_update_complete'), 10, 2);
        add_action('wp_ajax_update-plugin', array($this, 'track_update_start'), 1);
        add_action('wp_ajax_nopriv_update-plugin', array($this, 'track_update_start'), 1);
    }
    
    /**
     * Register REST endpoint for receiving update notifications
     */
    public function register_update_endpoint() {
        register_rest_route('snefuru/v1', '/check-update', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_update_notification'),
            'permission_callback' => array($this, 'verify_api_key')
        ));
    }
    
    /**
     * Verify API key for update notifications
     */
    public function verify_api_key($request) {
        $api_key = $request->get_header('X-API-Key');
        $stored_key = get_option('snefuru_ruplin_api_key_1', '');
        
        return !empty($api_key) && hash_equals($stored_key, $api_key);
    }
    
    /**
     * Handle update notification from Barkro system
     */
    public function handle_update_notification($request) {
        $params = $request->get_json_params();
        
        if (empty($params['action']) || $params['action'] !== 'update_available') {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid action'
            ), 400);
        }
        
        if (empty($params['update_data']) || empty($params['plugin_data'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing update data'
            ), 400);
        }
        
        // Store update data
        $transient_result = set_site_transient('snefuru_update_data', $params['update_data'], 12 * HOUR_IN_SECONDS);
        $this->log_update_event('Update notification received. Transient stored: ' . ($transient_result ? 'success' : 'failed'));
        
        // Get current plugin version
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_base);
        $current_version = $plugin_data['Version'];
        $new_version = $params['update_data']['version'] ?? 'unknown';
        
        $this->log_update_event( "Version comparison - Current: {$current_version}, New: {$new_version}");
        
        // Store update attempt for verification
        update_option('snefuru_last_update_attempt', array(
            'timestamp' => current_time('mysql'),
            'current_version' => $current_version,
            'target_version' => $new_version,
            'download_url' => $params['update_data']['download_url'] ?? '',
            'status' => 'notification_received'
        ));
        
        // Force WordPress to check for updates
        delete_site_transient('update_plugins');
        wp_update_plugins();
        
        $this->log_update_event( 'WordPress update check triggered');
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => "Update notification received - Current: {$current_version}, Target: {$new_version}",
            'current_version' => $current_version,
            'target_version' => $new_version,
            'debug' => array(
                'transient_stored' => $transient_result,
                'update_data_keys' => array_keys($params['update_data']),
                'plugin_file' => $this->plugin_base
            )
        ), 200);
    }
    
    /**
     * Check for plugin updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            $this->log_update_event( 'WordPress update check called but transient->checked is empty');
            return $transient;
        }
        
        // Get stored update data from Barkro notification
        $update_data = get_site_transient('snefuru_update_data');
        
        if (!$update_data) {
            $this->log_update_event( 'WordPress update check called but no update_data transient found');
            return $transient;
        }
        
        $this->log_update_event( 'WordPress update check processing. Update data found: ' . json_encode(array_keys($update_data)));
        
        // Get current plugin version
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_base);
        $current_version = $plugin_data['Version'];
        
        // Compare versions
        $version_compare_result = version_compare($current_version, $update_data['version'], '<');
        $this->log_update_event( "Version comparison result: {$current_version} < {$update_data['version']} = " . ($version_compare_result ? 'true' : 'false'));
        
        if ($version_compare_result) {
            $plugin_update = array(
                'id' => $this->plugin_base,
                'plugin' => $this->plugin_base,
                'slug' => $this->plugin_slug,
                'new_version' => $update_data['version'],
                'url' => 'https://github.com/transatlanticvoyage/snefuru4',
                'package' => $update_data['download_url'],
                'icons' => array(),
                'banners' => array(),
                'banners_rtl' => array(),
                'tested' => get_bloginfo('version'),
                'requires_php' => $update_data['min_php_version'] ?? '7.4',
                'requires' => $update_data['min_wp_version'] ?? '5.0',
                'compatibility' => new stdClass(),
            );
            
            $transient->response[$this->plugin_base] = (object) $plugin_update;
            $this->log_update_event( "Plugin update added to WordPress transient. Download URL: {$update_data['download_url']}");
            
            // Update status
            update_option('snefuru_last_update_attempt', array_merge(
                get_option('snefuru_last_update_attempt', array()),
                array('status' => 'queued_for_wordpress')
            ));
        } else {
            $this->log_update_event( 'No update needed - current version is up to date or newer');
        }
        
        return $transient;
    }
    
    /**
     * Provide plugin information for update details
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
            return $result;
        }
        
        $update_data = get_site_transient('snefuru_update_data');
        
        if (!$update_data) {
            return $result;
        }
        
        $plugin_info = array(
            'name' => 'Snefuruplin',
            'slug' => $this->plugin_slug,
            'version' => $update_data['version'],
            'author' => '<a href="https://snefuru.com">Snefuru Team</a>',
            'author_profile' => 'https://snefuru.com',
            'requires' => $update_data['min_wp_version'] ?? '5.0',
            'tested' => get_bloginfo('version'),
            'requires_php' => $update_data['min_php_version'] ?? '7.4',
            'sections' => array(
                'description' => 'WordPress plugin for handling image uploads to Snefuru system',
                'changelog' => $update_data['changelog'] ?? 'No changelog available',
                'installation' => 'Upload the plugin files to the `/wp-content/plugins/snefuruplin` directory, or install the plugin through the WordPress plugins screen directly.'
            ),
            'download_link' => $update_data['download_url']
        );
        
        return (object) $plugin_info;
    }
    
    /**
     * Enable automatic updates for this plugin
     */
    public function auto_update_plugin($update, $item) {
        // Check if this is our plugin
        if (isset($item->plugin) && $item->plugin === $this->plugin_base) {
            // Check if auto-updates are enabled for this site
            $auto_update_enabled = get_option('snefuru_auto_update_enabled', false);
            return $auto_update_enabled;
        }
        
        return $update;
    }
    
    /**
     * Track when WordPress starts updating our plugin
     */
    public function track_update_start() {
        if (isset($_POST['plugin']) && $_POST['plugin'] === $this->plugin_base) {
            $this->log_update_event( 'WordPress plugin update process started');
            update_option('snefuru_last_update_attempt', array_merge(
                get_option('snefuru_last_update_attempt', array()),
                array('status' => 'wordpress_update_started', 'start_time' => current_time('mysql'))
            ));
        }
    }
    
    /**
     * Track when WordPress completes updating any plugin
     */
    public function track_update_complete($upgrader_object, $options) {
        if (isset($options['type']) && $options['type'] === 'plugin') {
            if (isset($options['plugins']) && in_array($this->plugin_base, $options['plugins'])) {
                $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_base);
                $new_version = $plugin_data['Version'];
                
                $this->log_update_event( "WordPress plugin update completed. New version: {$new_version}");
                
                // Check if update was successful
                $last_attempt = get_option('snefuru_last_update_attempt', array());
                $target_version = $last_attempt['target_version'] ?? '';
                $success = version_compare($new_version, $target_version, '>=');
                
                update_option('snefuru_last_update_attempt', array_merge(
                    $last_attempt,
                    array(
                        'status' => $success ? 'completed_successfully' : 'completed_with_version_mismatch',
                        'completion_time' => current_time('mysql'),
                        'final_version' => $new_version,
                        'version_match' => $success
                    )
                ));
                
                // Clear the update transient since we've processed it
                delete_site_transient('snefuru_update_data');
            }
        }
    }
    
    /**
     * Log update events
     */
    private function log_update_event($message, $type = 'info') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'snefuru_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'timestamp' => current_time('mysql'),
                'action' => 'barkro_update',
                'data' => $message,
                'status' => $type
            )
        );
    }
}