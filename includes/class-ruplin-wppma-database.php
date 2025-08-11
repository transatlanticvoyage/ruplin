<?php

/**
 * Ruplin WP Database Horse Class
 * Handles creation and management of zen_services and zen_locations tables
 */
class Ruplin_WP_Database_Horse_Class {
    
    /**
     * Database version for tracking schema changes
     */
    const DB_VERSION = '1.4.0';
    
    /**
     * Option name for storing database version
     */
    const DB_VERSION_OPTION = 'snefuru_zen_db_version';
    
    /**
     * Create zen tables
     */
    public static function create_tables() {
        global $wpdb;
        
        try {
            $charset_collate = $wpdb->get_charset_collate();
            
            // Create zen_services table
            $services_table = $wpdb->prefix . 'zen_services';
            $services_sql = "CREATE TABLE $services_table (
                service_id INT(11) NOT NULL AUTO_INCREMENT,
                service_name TEXT,
                service_placard TEXT,
                service_moniker TEXT,
                service_sobriquet TEXT,
                description1_short TEXT,
                description1_long TEXT,
                rel_image1_id INT(11),
                is_pinned_service BOOLEAN DEFAULT FALSE,
                position_in_custom_order INT(11),
                PRIMARY KEY (service_id)
            ) $charset_collate;";
            
            // Create zen_locations table
            $locations_table = $wpdb->prefix . 'zen_locations';
            $locations_sql = "CREATE TABLE $locations_table (
                location_id INT(11) NOT NULL AUTO_INCREMENT,
                location_name TEXT,
                location_placard TEXT,
                location_moniker TEXT,
                location_sobriquet TEXT,
                street TEXT,
                city TEXT,
                state_code TEXT,
                zip_code TEXT,
                country TEXT,
                rel_image1_id INT(11),
                is_pinned_location BOOLEAN DEFAULT FALSE,
                position_in_custom_order INT(11),
                PRIMARY KEY (location_id)
            ) $charset_collate;";
            
            // Create zen_orbitposts table
            $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
            $orbitposts_sql = "CREATE TABLE $orbitposts_table (
                orbitpost_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                rel_wp_post_id BIGINT(20) UNSIGNED,
                redshift_datum TEXT,
                rover_datum JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (orbitpost_id),
                KEY rel_wp_post_id (rel_wp_post_id)
            ) $charset_collate;";
            
            // Use dbDelta to create/update tables
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            // Log the SQL for debugging
            error_log('Snefuru: Creating services table with SQL: ' . $services_sql);
            error_log('Snefuru: Creating locations table with SQL: ' . $locations_sql);
            error_log('Snefuru: Creating orbitposts table with SQL: ' . $orbitposts_sql);
            
            $services_result = dbDelta($services_sql);
            $locations_result = dbDelta($locations_sql);
            $orbitposts_result = dbDelta($orbitposts_sql);
            
            // Log dbDelta results
            error_log('Snefuru: Services table dbDelta result: ' . print_r($services_result, true));
            error_log('Snefuru: Locations table dbDelta result: ' . print_r($locations_result, true));
            error_log('Snefuru: Orbitposts table dbDelta result: ' . print_r($orbitposts_result, true));
            
            // Update database version
            update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
            
            // Verify tables were created
            $services_exists = $wpdb->get_var("SHOW TABLES LIKE '$services_table'") == $services_table;
            $locations_exists = $wpdb->get_var("SHOW TABLES LIKE '$locations_table'") == $locations_table;
            $orbitposts_exists = $wpdb->get_var("SHOW TABLES LIKE '$orbitposts_table'") == $orbitposts_table;
            
            // Log verification results
            error_log('Snefuru: Services table exists: ' . ($services_exists ? 'YES' : 'NO'));
            error_log('Snefuru: Locations table exists: ' . ($locations_exists ? 'YES' : 'NO'));
            error_log('Snefuru: Orbitposts table exists: ' . ($orbitposts_exists ? 'YES' : 'NO'));
            
            if ($services_exists && $locations_exists && $orbitposts_exists) {
                error_log('Snefuru: Zen tables created/updated successfully');
                return true;
            } else {
                error_log('Snefuru: Table verification failed');
                return false;
            }
            
        } catch (Exception $e) {
            error_log('Snefuru: Error creating zen tables: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if tables exist
     */
    public static function tables_exist() {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'zen_services';
        $locations_table = $wpdb->prefix . 'zen_locations';
        
        $services_exists = $wpdb->get_var("SHOW TABLES LIKE '$services_table'") == $services_table;
        $locations_exists = $wpdb->get_var("SHOW TABLES LIKE '$locations_table'") == $locations_table;
        
        return $services_exists && $locations_exists;
    }
    
    /**
     * Drop zen tables (for uninstall)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'zen_services';
        $locations_table = $wpdb->prefix . 'zen_locations';
        
        $wpdb->query("DROP TABLE IF EXISTS $services_table");
        $wpdb->query("DROP TABLE IF EXISTS $locations_table");
        
        // Delete database version option
        delete_option(self::DB_VERSION_OPTION);
        
        error_log('Snefuru Zen tables dropped');
    }
    
    /**
     * Check and update database schema if needed
     */
    public static function check_database_version() {
        $current_version = get_option(self::DB_VERSION_OPTION, '0.0.0');
        
        if (version_compare($current_version, self::DB_VERSION, '<')) {
            self::create_tables();
        }
    }
    
    /**
     * Get all services
     */
    public static function get_services($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_services';
        
        $defaults = array(
            'orderby' => 'position_in_custom_order',
            'order' => 'ASC',
            'pinned_first' => true
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $orderby = $args['pinned_first'] ? 'is_pinned_service DESC, position_in_custom_order' : $args['orderby'];
        $order = $args['order'];
        
        $query = "SELECT * FROM $table ORDER BY $orderby $order";
        return $wpdb->get_results($query);
    }
    
    /**
     * Get all locations
     */
    public static function get_locations($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_locations';
        
        $defaults = array(
            'orderby' => 'position_in_custom_order',
            'order' => 'ASC',
            'pinned_first' => true
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $orderby = $args['pinned_first'] ? 'is_pinned_location DESC, position_in_custom_order' : $args['orderby'];
        $order = $args['order'];
        
        $query = "SELECT * FROM $table ORDER BY $orderby $order";
        return $wpdb->get_results($query);
    }
    
    /**
     * Insert a service
     */
    public static function insert_service($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_services';
        
        return $wpdb->insert($table, $data);
    }
    
    /**
     * Insert a location
     */
    public static function insert_location($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_locations';
        
        return $wpdb->insert($table, $data);
    }
    
    /**
     * Update a service
     */
    public static function update_service($service_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_services';
        
        return $wpdb->update($table, $data, array('service_id' => $service_id));
    }
    
    /**
     * Update a location
     */
    public static function update_location($location_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_locations';
        
        return $wpdb->update($table, $data, array('location_id' => $location_id));
    }
    
    /**
     * Delete a service
     */
    public static function delete_service($service_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_services';
        
        return $wpdb->delete($table, array('service_id' => $service_id));
    }
    
    /**
     * Delete a location
     */
    public static function delete_location($location_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_locations';
        
        return $wpdb->delete($table, array('location_id' => $location_id));
    }
    
    /**
     * Get service by ID
     */
    public static function get_service($service_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_services';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE service_id = %d", $service_id));
    }
    
    /**
     * Get location by ID
     */
    public static function get_location($location_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_locations';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE location_id = %d", $location_id));
    }
}