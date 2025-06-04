<?php

class Snefuru_API_Client {
    
    private $api_base_url;
    private $api_key;
    
    public function __construct() {
        $this->api_base_url = get_option('snefuru_api_url', 'https://your-app.vercel.app/api');
        $this->api_key = get_option('snefuru_api_key', '');
    }
    
    /**
     * Send data to the cloud mothership
     */
    public function send_data_to_cloud($data) {
        if (empty($this->api_key)) {
            error_log('Snefuru: API key not configured');
            return false;
        }
        
        $endpoint = $this->api_base_url . '/plugin-data';
        
        $body = array(
            'site_url' => get_site_url(),
            'site_name' => get_bloginfo('name'),
            'plugin_version' => SNEFURU_PLUGIN_VERSION,
            'timestamp' => current_time('mysql'),
            'data' => $data
        );
        
        $response = wp_remote_post($endpoint, array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
                'User-Agent' => 'Snefuru-Plugin/' . SNEFURU_PLUGIN_VERSION
            ),
            'body' => json_encode($body)
        ));
        
        if (is_wp_error($response)) {
            error_log('Snefuru API Error: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code === 200) {
            $this->log_activity('data_sent', 'Data successfully sent to cloud');
            return json_decode($response_body, true);
        } else {
            error_log('Snefuru API Error: HTTP ' . $response_code . ' - ' . $response_body);
            return false;
        }
    }
    
    /**
     * Get instructions from the cloud mothership
     */
    public function get_instructions_from_cloud() {
        if (empty($this->api_key)) {
            return false;
        }
        
        $endpoint = $this->api_base_url . '/plugin-instructions';
        $site_id = $this->get_site_identifier();
        
        $response = wp_remote_get($endpoint . '?site_id=' . urlencode($site_id), array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'User-Agent' => 'Snefuru-Plugin/' . SNEFURU_PLUGIN_VERSION
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('Snefuru API Error: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code === 200) {
            $instructions = json_decode(wp_remote_retrieve_body($response), true);
            $this->process_instructions($instructions);
            return $instructions;
        }
        
        return false;
    }
    
    /**
     * Process instructions received from the mothership
     */
    private function process_instructions($instructions) {
        if (!is_array($instructions)) {
            return;
        }
        
        foreach ($instructions as $instruction) {
            switch ($instruction['type']) {
                case 'update_setting':
                    update_option($instruction['key'], $instruction['value']);
                    $this->log_activity('setting_updated', "Updated {$instruction['key']}");
                    break;
                    
                case 'collect_data':
                    $data_collector = new Snefuru_Data_Collector();
                    $data = $data_collector->collect_specific_data($instruction['data_type']);
                    $this->send_data_to_cloud($data);
                    break;
                    
                case 'run_maintenance':
                    $this->run_maintenance_task($instruction['task']);
                    break;
                    
                default:
                    error_log('Snefuru: Unknown instruction type: ' . $instruction['type']);
            }
        }
    }
    
    /**
     * Run maintenance tasks as instructed by mothership
     */
    private function run_maintenance_task($task) {
        switch ($task) {
            case 'clear_cache':
                // Clear various caches
                if (function_exists('wp_cache_flush')) {
                    wp_cache_flush();
                }
                $this->log_activity('maintenance', 'Cache cleared');
                break;
                
            case 'optimize_db':
                // Basic database optimization
                global $wpdb;
                $wpdb->query("OPTIMIZE TABLE {$wpdb->posts}");
                $this->log_activity('maintenance', 'Database optimized');
                break;
        }
    }
    
    /**
     * Get unique site identifier
     */
    private function get_site_identifier() {
        $site_id = get_option('snefuru_site_id');
        if (!$site_id) {
            $site_id = md5(get_site_url() . get_option('siteurl'));
            update_option('snefuru_site_id', $site_id);
        }
        return $site_id;
    }
    
    /**
     * Get generated images from the Snefuru system
     */
    public function get_generated_images($limit = 50) {
        if (empty($this->api_key)) {
            return array();
        }
        
        $endpoint = $this->api_base_url . '/generated-images';
        $site_id = $this->get_site_identifier();
        
        $response = wp_remote_get($endpoint . '?site_id=' . urlencode($site_id) . '&limit=' . intval($limit), array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'User-Agent' => 'Snefuru-Plugin/' . SNEFURU_PLUGIN_VERSION
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('Snefuru API Error getting images: ' . $response->get_error_message());
            return array();
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code === 200) {
            $images = json_decode(wp_remote_retrieve_body($response), true);
            return is_array($images) ? $images : array();
        }
        
        error_log('Snefuru API Error: HTTP ' . $response_code . ' when fetching images');
        return array();
    }
    
    /**
     * Log plugin activities
     */
    private function log_activity($action, $message) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'snefuru_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'action' => $action,
                'data' => $message,
                'status' => 'completed',
                'timestamp' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
} 