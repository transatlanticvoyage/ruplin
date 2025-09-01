<?php
/**
 * Petrifact Registry Class
 * 
 * Central registry for all persistent shortcodes and data structures
 * that should remain functional after main plugin deletion.
 * 
 * @package Petrifact
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Petrifact_Registry {
    
    /**
     * Version of the Petrifact runtime
     * Bump this when adding new shortcodes or modifying existing ones
     */
    const PETRIFACT_VERSION = '1.0.0';
    
    /**
     * Option name for tracking installed Petrifact version
     */
    const VERSION_OPTION = 'petrifact_runtime_version';
    
    /**
     * Get all shortcodes that should persist after plugin deletion
     * 
     * @return array Array of shortcode definitions
     */
    public static function get_persistent_shortcodes() {
        return array(
            // Services shortcodes
            'zen_services' => array(
                'description' => 'Display list of services',
                'callback' => 'render_services_list',
                'requires_tables' => array('zen_services'),
                'required_fields' => array('service_id', 'service_name', 'description1_short'),
                'optional_fields' => array('service_placard', 'service_moniker', 'rel_image1_id', 'is_pinned_service'),
                'supports_attributes' => array('limit', 'template', 'show_images', 'pinned_first', 'order', 'orderby', 'class')
            ),
            
            'zen_service' => array(
                'description' => 'Display single service',
                'callback' => 'render_single_service',
                'requires_tables' => array('zen_services'),
                'required_fields' => array('service_id', 'service_name'),
                'optional_fields' => array('description1_short', 'description1_long', 'service_placard'),
                'supports_attributes' => array('id', 'field', 'template', 'show_image')
            ),
            
            'zen_service_image' => array(
                'description' => 'Display service image',
                'callback' => 'render_service_image',
                'requires_tables' => array('zen_services'),
                'required_fields' => array('service_id', 'rel_image1_id'),
                'supports_attributes' => array('id', 'size', 'class')
            ),
            
            // Locations shortcodes
            'zen_locations' => array(
                'description' => 'Display list of locations',
                'callback' => 'render_locations_list',
                'requires_tables' => array('zen_locations'),
                'required_fields' => array('location_id', 'location_name'),
                'optional_fields' => array('city', 'state_code', 'street', 'zip_code'),
                'supports_attributes' => array('limit', 'template', 'show_images', 'pinned_first', 'order', 'orderby', 'class')
            ),
            
            'zen_location' => array(
                'description' => 'Display single location',
                'callback' => 'render_single_location',
                'requires_tables' => array('zen_locations'),
                'required_fields' => array('location_id', 'location_name'),
                'optional_fields' => array('street', 'city', 'state_code', 'zip_code', 'location_placard'),
                'supports_attributes' => array('id', 'field', 'template', 'show_address')
            ),
            
            'zen_location_image' => array(
                'description' => 'Display location image',
                'callback' => 'render_location_image',
                'requires_tables' => array('zen_locations'),
                'required_fields' => array('location_id', 'rel_image1_id'),
                'supports_attributes' => array('id', 'size', 'class')
            ),
            
            // Sitespren shortcodes
            'sitespren' => array(
                'description' => 'Display sitespren data',
                'callback' => 'render_sitespren',
                'requires_tables' => array('zen_sitespren'),
                'required_fields' => array('id', 'true_root_domain'),
                'optional_fields' => array('full_subdomain', 'webproperty_type', 'is_wp_site'),
                'supports_attributes' => array('id', 'field', 'format'),
                'security' => 'filtered' // Special flag for sensitive data filtering
            ),
            
            'zen_sitespren_list' => array(
                'description' => 'Display list of sitespren entries',
                'callback' => 'render_sitespren_list',
                'requires_tables' => array('zen_sitespren'),
                'required_fields' => array('id', 'true_root_domain'),
                'optional_fields' => array('full_subdomain', 'is_wp_site', 'wp_plugin_connected2'),
                'supports_attributes' => array('type', 'show', 'limit', 'template'),
                'security' => 'filtered'
            ),
            
            'zen_sitespren_count' => array(
                'description' => 'Display count of sitespren entries',
                'callback' => 'render_sitespren_count',
                'requires_tables' => array('zen_sitespren'),
                'required_fields' => array('id'),
                'supports_attributes' => array('type', 'label'),
                'security' => 'public'
            ),
            
            // Utility shortcodes
            'zen_pinned_services' => array(
                'description' => 'Display only pinned services',
                'callback' => 'render_pinned_services',
                'requires_tables' => array('zen_services'),
                'required_fields' => array('service_id', 'service_name', 'is_pinned_service'),
                'supports_attributes' => array('limit', 'template', 'show_images')
            ),
            
            'zen_pinned_locations' => array(
                'description' => 'Display only pinned locations',
                'callback' => 'render_pinned_locations',
                'requires_tables' => array('zen_locations'),
                'required_fields' => array('location_id', 'location_name', 'is_pinned_location'),
                'supports_attributes' => array('limit', 'template', 'show_images')
            )
        );
    }
    
    /**
     * Get database tables that need to persist
     * 
     * @return array Array of table definitions
     */
    public static function get_persistent_tables() {
        global $wpdb;
        
        return array(
            'zen_services' => array(
                'name' => $wpdb->prefix . 'zen_services',
                'description' => 'Services offered by the business',
                'critical_fields' => array('service_id', 'service_name', 'description1_short')
            ),
            'zen_locations' => array(
                'name' => $wpdb->prefix . 'zen_locations',
                'description' => 'Business locations',
                'critical_fields' => array('location_id', 'location_name', 'city', 'state_code')
            ),
            'zen_sitespren' => array(
                'name' => $wpdb->prefix . 'zen_sitespren',
                'description' => 'Site configuration and metadata',
                'critical_fields' => array('id', 'true_root_domain', 'full_subdomain'),
                'sensitive_fields' => array('wpuser1', 'wppass1') // Never expose these
            ),
            'zen_orbitposts' => array(
                'name' => $wpdb->prefix . 'zen_orbitposts',
                'description' => 'Post metadata and relationships',
                'critical_fields' => array('orbitpost_id', 'rel_wp_post_id')
            )
        );
    }
    
    /**
     * Get fields that should never be exposed via shortcodes
     * 
     * @return array Array of blocked field names
     */
    public static function get_blocked_fields() {
        return array(
            'wpuser1',
            'wppass1',
            'wp_password',
            'api_key',
            'api_secret',
            'auth_token',
            'private_key'
        );
    }
    
    /**
     * Get fields that require admin capabilities to access
     * 
     * @return array Array of admin-only field names
     */
    public static function get_admin_only_fields() {
        return array(
            'fk_users_id',
            'fk_domreg_hostaccount',
            'wp_plugin_installed1',
            'hudson_imgplanbatch_id'
        );
    }
    
    /**
     * Check if a shortcode should be included in Petrifact
     * 
     * @param string $shortcode_name Name of the shortcode
     * @return bool True if shortcode should persist
     */
    public static function is_persistent_shortcode($shortcode_name) {
        $shortcodes = self::get_persistent_shortcodes();
        return isset($shortcodes[$shortcode_name]);
    }
    
    /**
     * Get the current Petrifact version
     * 
     * @return string Version string
     */
    public static function get_version() {
        return self::PETRIFACT_VERSION;
    }
    
    /**
     * Check if Petrifact needs updating
     * 
     * @return bool True if update needed
     */
    public static function needs_update() {
        $installed_version = get_option(self::VERSION_OPTION, '0.0.0');
        return version_compare($installed_version, self::PETRIFACT_VERSION, '<');
    }
    
    /**
     * Get minimal database queries for MU-plugin
     * These are simplified versions without complex joins or heavy processing
     * 
     * @return array Array of query templates
     */
    public static function get_query_templates() {
        return array(
            'get_service_by_id' => "SELECT * FROM {table} WHERE service_id = %d LIMIT 1",
            'get_services_list' => "SELECT * FROM {table} ORDER BY position_in_custom_order ASC, service_name ASC",
            'get_pinned_services' => "SELECT * FROM {table} WHERE is_pinned_service = 1 ORDER BY position_in_custom_order ASC",
            'get_location_by_id' => "SELECT * FROM {table} WHERE location_id = %d LIMIT 1",
            'get_locations_list' => "SELECT * FROM {table} ORDER BY position_in_custom_order ASC, location_name ASC",
            'get_pinned_locations' => "SELECT * FROM {table} WHERE is_pinned_location = 1 ORDER BY position_in_custom_order ASC",
            'get_sitespren_by_id' => "SELECT * FROM {table} WHERE id = %s LIMIT 1",
            'get_sitespren_by_domain' => "SELECT * FROM {table} WHERE true_root_domain = %s LIMIT 1",
            'count_sitespren' => "SELECT COUNT(*) as total FROM {table} WHERE {condition}"
        );
    }
}