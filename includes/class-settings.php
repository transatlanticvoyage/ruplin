<?php

class Snefuru_Settings {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        // Settings are handled in the admin class
        // This class can be extended for more complex settings management
    }
    
    public static function get_default_settings() {
        return array(
            'api_key' => '',
            'api_url' => 'https://your-app.vercel.app/api',
            'sync_interval' => 'hourly',
            'auto_sync' => true,
            'data_types' => array('site_info', 'performance', 'security', 'content')
        );
    }
} 