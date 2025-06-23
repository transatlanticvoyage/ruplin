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
        $stored_key = get_option('snefuru_upload_api_key', '');
        
        return !empty($api_key) && $api_key === $stored_key;
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
        set_site_transient('snefuru_update_data', $params['update_data'], 12 * HOUR_IN_SECONDS);
        
        // Get current plugin version
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_base);
        $current_version = $plugin_data['Version'];
        
        // Force WordPress to check for updates
        delete_site_transient('update_plugins');
        wp_update_plugins();
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Update notification received',
            'current_version' => $current_version
        ), 200);
    }
    
    /**
     * Check for plugin updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Get stored update data from Barkro notification
        $update_data = get_site_transient('snefuru_update_data');
        
        if (!$update_data) {
            // No update data available
            return $transient;
        }
        
        // Get current plugin version
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_base);
        $current_version = $plugin_data['Version'];
        
        // Compare versions
        if (version_compare($current_version, $update_data['version'], '<')) {
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
            'name' => 'Snefuru Image Upload Plugin',
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