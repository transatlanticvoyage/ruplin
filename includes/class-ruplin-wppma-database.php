<?php

/**
 * Ruplin WP Database Horse Class
 * Handles creation and management of zen_services and zen_locations tables
 */
class Ruplin_WP_Database_Horse_Class {
    
    /**
     * Database version for tracking schema changes
     */
    const DB_VERSION = '1.8.0';
    
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
                hudson_imgplanbatch_id VARCHAR(36),
                is_pinned BOOLEAN DEFAULT FALSE,
                is_flagged BOOLEAN DEFAULT FALSE,
                is_starred BOOLEAN DEFAULT FALSE,
                is_squared BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (orbitpost_id),
                KEY rel_wp_post_id (rel_wp_post_id),
                KEY hudson_imgplanbatch_id (hudson_imgplanbatch_id)
            ) $charset_collate;";
            
            // Create zen_cache_reports table
            $cache_reports_table = $wpdb->prefix . 'zen_cache_reports';
            $cache_reports_sql = "CREATE TABLE $cache_reports_table (
                report_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) UNSIGNED,
                operation_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                cache_type VARCHAR(50),
                operation_status VARCHAR(20),
                items_cleared INT(11) DEFAULT 0,
                memory_before BIGINT(20) DEFAULT 0,
                memory_after BIGINT(20) DEFAULT 0,
                execution_time_ms INT(11) DEFAULT 0,
                error_message TEXT,
                additional_data JSON,
                PRIMARY KEY (report_id),
                KEY user_id (user_id),
                KEY cache_type (cache_type),
                KEY operation_timestamp (operation_timestamp)
            ) $charset_collate;";
            
            // Use dbDelta to create/update tables
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            // Log the SQL for debugging
            error_log('Snefuru: Creating services table with SQL: ' . $services_sql);
            error_log('Snefuru: Creating locations table with SQL: ' . $locations_sql);
            error_log('Snefuru: Creating orbitposts table with SQL: ' . $orbitposts_sql);
            error_log('Snefuru: Creating cache_reports table with SQL: ' . $cache_reports_sql);
            
            $services_result = dbDelta($services_sql);
            $locations_result = dbDelta($locations_sql);
            $orbitposts_result = dbDelta($orbitposts_sql);
            $cache_reports_result = dbDelta($cache_reports_sql);
            
            // Log dbDelta results
            error_log('Snefuru: Services table dbDelta result: ' . print_r($services_result, true));
            error_log('Snefuru: Locations table dbDelta result: ' . print_r($locations_result, true));
            error_log('Snefuru: Orbitposts table dbDelta result: ' . print_r($orbitposts_result, true));
            error_log('Snefuru: Cache reports table dbDelta result: ' . print_r($cache_reports_result, true));
            
            // Update database version
            update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
            
            // Verify tables were created
            $services_exists = $wpdb->get_var("SHOW TABLES LIKE '$services_table'") == $services_table;
            $locations_exists = $wpdb->get_var("SHOW TABLES LIKE '$locations_table'") == $locations_table;
            $orbitposts_exists = $wpdb->get_var("SHOW TABLES LIKE '$orbitposts_table'") == $orbitposts_table;
            $cache_reports_exists = $wpdb->get_var("SHOW TABLES LIKE '$cache_reports_table'") == $cache_reports_table;
            
            // Log verification results
            error_log('Snefuru: Services table exists: ' . ($services_exists ? 'YES' : 'NO'));
            error_log('Snefuru: Locations table exists: ' . ($locations_exists ? 'YES' : 'NO'));
            error_log('Snefuru: Orbitposts table exists: ' . ($orbitposts_exists ? 'YES' : 'NO'));
            error_log('Snefuru: Cache reports table exists: ' . ($cache_reports_exists ? 'YES' : 'NO'));
            
            if ($services_exists && $locations_exists && $orbitposts_exists && $cache_reports_exists) {
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
    
    /**
     * Insert a cache report
     */
    public static function insert_cache_report($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_cache_reports';
        
        return $wpdb->insert($table, $data);
    }
    
    /**
     * Get cache reports with optional filtering
     */
    public static function get_cache_reports($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_cache_reports';
        
        $defaults = array(
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'operation_timestamp',
            'order' => 'DESC',
            'user_id' => null,
            'cache_type' => null,
            'date_from' => null,
            'date_to' => null
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($args['user_id'])) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }
        
        if (!empty($args['cache_type'])) {
            $where_conditions[] = 'cache_type = %s';
            $where_values[] = $args['cache_type'];
        }
        
        if (!empty($args['date_from'])) {
            $where_conditions[] = 'operation_timestamp >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where_conditions[] = 'operation_timestamp <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $query = "SELECT * FROM $table $where_clause ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($query, $where_values));
        } else {
            return $wpdb->get_results($query);
        }
    }
    
    /**
     * Get cache report statistics
     */
    public static function get_cache_report_stats($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_cache_reports';
        
        $defaults = array(
            'user_id' => null,
            'date_from' => null,
            'date_to' => null
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($args['user_id'])) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }
        
        if (!empty($args['date_from'])) {
            $where_conditions[] = 'operation_timestamp >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where_conditions[] = 'operation_timestamp <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $query = "SELECT 
            cache_type,
            COUNT(*) as total_operations,
            SUM(CASE WHEN operation_status = 'success' THEN 1 ELSE 0 END) as successful_operations,
            SUM(CASE WHEN operation_status = 'error' THEN 1 ELSE 0 END) as failed_operations,
            SUM(items_cleared) as total_items_cleared,
            AVG(execution_time_ms) as avg_execution_time,
            MAX(operation_timestamp) as last_operation
            FROM $table 
            $where_clause 
            GROUP BY cache_type 
            ORDER BY total_operations DESC";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($query, $where_values));
        } else {
            return $wpdb->get_results($query);
        }
    }
}