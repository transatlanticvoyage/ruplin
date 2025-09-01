<?php
/**
 * Petrifact Generator Class
 * 
 * Generates the MU-plugin file from registry definitions and templates.
 * Handles extraction of minimal code from main plugin classes.
 * 
 * @package Petrifact
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Petrifact_Generator {
    
    /**
     * Generate and install the Petrifact MU-plugin
     * 
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function generate_and_install() {
        try {
            // Generate the MU-plugin content
            $mu_content = self::generate_mu_plugin_content();
            
            if (empty($mu_content)) {
                return new WP_Error('petrifact_generation_failed', 'Failed to generate MU-plugin content');
            }
            
            // Ensure MU-plugins directory exists
            $mu_dir = WP_CONTENT_DIR . '/mu-plugins';
            if (!is_dir($mu_dir)) {
                if (!wp_mkdir_p($mu_dir)) {
                    return new WP_Error('petrifact_dir_creation_failed', 'Failed to create mu-plugins directory');
                }
            }
            
            // Write the MU-plugin file
            $mu_file = $mu_dir . '/petrifact.php';
            $result = file_put_contents($mu_file, $mu_content);
            
            if ($result === false) {
                return new WP_Error('petrifact_write_failed', 'Failed to write MU-plugin file');
            }
            
            // Update version option
            update_option(Petrifact_Registry::VERSION_OPTION, Petrifact_Registry::get_version());
            
            // Log the installation
            self::log_installation();
            
            return true;
            
        } catch (Exception $e) {
            return new WP_Error('petrifact_exception', $e->getMessage());
        }
    }
    
    /**
     * Generate the complete MU-plugin content
     * 
     * @return string Generated PHP code
     */
    private static function generate_mu_plugin_content() {
        $content = self::get_file_header();
        $content .= self::get_security_check();
        $content .= self::get_class_definition();
        $content .= self::get_initialization_code();
        
        return $content;
    }
    
    /**
     * Get the file header for the MU-plugin
     * 
     * @return string PHP file header
     */
    private static function get_file_header() {
        $version = Petrifact_Registry::get_version();
        $generated_date = current_time('Y-m-d H:i:s');
        
        return <<<PHP
<?php
/**
 * Plugin Name: Petrifact
 * Description: Persistent data layer for zen tables and shortcodes. Maintains functionality after main plugin removal.
 * Version: {$version}
 * Generated: {$generated_date}
 * 
 * This is an auto-generated Must-Use plugin that provides runtime support
 * for persistent shortcodes and database queries. Do not edit directly.
 * 
 * @package Petrifact
 */

PHP;
    }
    
    /**
     * Get security check code
     * 
     * @return string Security check PHP code
     */
    private static function get_security_check() {
        return <<<'PHP'
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Only load if not already loaded by main plugin
if (class_exists('Petrifact_Runtime')) {
    return;
}


PHP;
    }
    
    /**
     * Get the main class definition
     * 
     * @return string Class definition PHP code
     */
    private static function get_class_definition() {
        $shortcodes = Petrifact_Registry::get_persistent_shortcodes();
        $tables = Petrifact_Registry::get_persistent_tables();
        $blocked_fields = Petrifact_Registry::get_blocked_fields();
        
        $content = <<<'PHP'
/**
 * Petrifact Runtime Class
 */
class Petrifact_Runtime {
    
    /**
     * Blocked fields that should never be exposed
     */
    private static $blocked_fields = array(

PHP;
        
        // Add blocked fields
        foreach ($blocked_fields as $field) {
            $content .= "        '{$field}',\n";
        }
        
        $content .= <<<'PHP'
    );
    
    /**
     * Initialize the runtime
     */
    public static function init() {
        self::register_shortcodes();
    }
    
    /**
     * Register all persistent shortcodes
     */
    private static function register_shortcodes() {

PHP;
        
        // Add shortcode registrations
        foreach ($shortcodes as $tag => $config) {
            $content .= "        add_shortcode('{$tag}', array(__CLASS__, '{$config['callback']}'));\n";
        }
        
        $content .= "    }\n\n";
        
        // Add shortcode handler methods
        $content .= self::generate_shortcode_handlers();
        
        // Add utility methods
        $content .= self::generate_utility_methods();
        
        $content .= "}\n\n";
        
        return $content;
    }
    
    /**
     * Generate shortcode handler methods
     * 
     * @return string PHP code for shortcode handlers
     */
    private static function generate_shortcode_handlers() {
        $handlers = '';
        
        // Services handlers
        $handlers .= self::generate_services_handlers();
        
        // Locations handlers
        $handlers .= self::generate_locations_handlers();
        
        // Sitespren handlers
        $handlers .= self::generate_sitespren_handlers();
        
        return $handlers;
    }
    
    /**
     * Generate services-related shortcode handlers
     * 
     * @return string PHP code
     */
    private static function generate_services_handlers() {
        return <<<'PHP'
    /**
     * Render services list
     */
    public static function render_services_list($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'limit' => -1,
            'template' => 'list',
            'show_images' => 'false',
            'pinned_first' => 'true',
            'order' => 'ASC',
            'orderby' => 'position_in_custom_order',
            'class' => ''
        ), $atts, 'zen_services');
        
        $table = $wpdb->prefix . 'zen_services';
        
        // Build query
        $query = "SELECT * FROM {$table} WHERE 1=1";
        
        if ($atts['pinned_first'] === 'true') {
            $query .= " ORDER BY is_pinned_service DESC, ";
        } else {
            $query .= " ORDER BY ";
        }
        
        if ($atts['orderby'] === 'position_in_custom_order') {
            $query .= "position_in_custom_order ASC, service_name ASC";
        } else {
            $query .= "service_name " . esc_sql($atts['order']);
        }
        
        if ($atts['limit'] > 0) {
            $query .= " LIMIT " . intval($atts['limit']);
        }
        
        $services = $wpdb->get_results($query);
        
        if (empty($services)) {
            return '<p>No services found.</p>';
        }
        
        $output = '<div class="petrifact-services ' . esc_attr($atts['class']) . '">';
        
        foreach ($services as $service) {
            $output .= '<div class="petrifact-service-item">';
            $output .= '<h3>' . esc_html($service->service_name) . '</h3>';
            
            if (!empty($service->description1_short)) {
                $output .= '<p>' . esc_html($service->description1_short) . '</p>';
            }
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render single service
     */
    public static function render_single_service($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'id' => '',
            'field' => '',
            'template' => 'full'
        ), $atts, 'zen_service');
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $table = $wpdb->prefix . 'zen_services';
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE service_id = %d LIMIT 1",
            intval($atts['id'])
        ));
        
        if (!$service) {
            return '';
        }
        
        // If specific field requested
        if (!empty($atts['field'])) {
            if (in_array($atts['field'], self::$blocked_fields)) {
                return '';
            }
            return esc_html($service->{$atts['field']} ?? '');
        }
        
        // Full template
        $output = '<div class="petrifact-service">';
        $output .= '<h2>' . esc_html($service->service_name) . '</h2>';
        
        if (!empty($service->description1_short)) {
            $output .= '<div class="service-description">' . esc_html($service->description1_short) . '</div>';
        }
        
        if (!empty($service->description1_long)) {
            $output .= '<div class="service-details">' . wp_kses_post($service->description1_long) . '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render service image
     */
    public static function render_service_image($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'id' => '',
            'size' => 'medium',
            'class' => ''
        ), $atts, 'zen_service_image');
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $table = $wpdb->prefix . 'zen_services';
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT rel_image1_id FROM {$table} WHERE service_id = %d LIMIT 1",
            intval($atts['id'])
        ));
        
        if (!$service || empty($service->rel_image1_id)) {
            return '';
        }
        
        $image = wp_get_attachment_image($service->rel_image1_id, $atts['size'], false, array(
            'class' => 'petrifact-service-image ' . esc_attr($atts['class'])
        ));
        
        return $image;
    }
    
    /**
     * Render pinned services
     */
    public static function render_pinned_services($atts) {
        $atts['pinned_only'] = 'true';
        return self::render_services_list($atts);
    }
    

PHP;
    }
    
    /**
     * Generate locations-related shortcode handlers
     * 
     * @return string PHP code
     */
    private static function generate_locations_handlers() {
        return <<<'PHP'
    /**
     * Render locations list
     */
    public static function render_locations_list($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'limit' => -1,
            'template' => 'list',
            'show_images' => 'false',
            'pinned_first' => 'true',
            'order' => 'ASC',
            'orderby' => 'position_in_custom_order',
            'class' => ''
        ), $atts, 'zen_locations');
        
        $table = $wpdb->prefix . 'zen_locations';
        
        // Build query
        $query = "SELECT * FROM {$table} WHERE 1=1";
        
        if ($atts['pinned_first'] === 'true') {
            $query .= " ORDER BY is_pinned_location DESC, ";
        } else {
            $query .= " ORDER BY ";
        }
        
        if ($atts['orderby'] === 'position_in_custom_order') {
            $query .= "position_in_custom_order ASC, location_name ASC";
        } else {
            $query .= "location_name " . esc_sql($atts['order']);
        }
        
        if ($atts['limit'] > 0) {
            $query .= " LIMIT " . intval($atts['limit']);
        }
        
        $locations = $wpdb->get_results($query);
        
        if (empty($locations)) {
            return '<p>No locations found.</p>';
        }
        
        $output = '<div class="petrifact-locations ' . esc_attr($atts['class']) . '">';
        
        foreach ($locations as $location) {
            $output .= '<div class="petrifact-location-item">';
            $output .= '<h3>' . esc_html($location->location_name) . '</h3>';
            
            // Build address
            $address_parts = array();
            if (!empty($location->street)) $address_parts[] = $location->street;
            if (!empty($location->city)) $address_parts[] = $location->city;
            if (!empty($location->state_code)) $address_parts[] = $location->state_code;
            if (!empty($location->zip_code)) $address_parts[] = $location->zip_code;
            
            if (!empty($address_parts)) {
                $output .= '<p class="location-address">' . esc_html(implode(', ', $address_parts)) . '</p>';
            }
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render single location
     */
    public static function render_single_location($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'id' => '',
            'field' => '',
            'template' => 'full',
            'show_address' => 'true'
        ), $atts, 'zen_location');
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $table = $wpdb->prefix . 'zen_locations';
        $location = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE location_id = %d LIMIT 1",
            intval($atts['id'])
        ));
        
        if (!$location) {
            return '';
        }
        
        // If specific field requested
        if (!empty($atts['field'])) {
            if (in_array($atts['field'], self::$blocked_fields)) {
                return '';
            }
            return esc_html($location->{$atts['field']} ?? '');
        }
        
        // Full template
        $output = '<div class="petrifact-location">';
        $output .= '<h2>' . esc_html($location->location_name) . '</h2>';
        
        if ($atts['show_address'] === 'true') {
            $address_parts = array();
            if (!empty($location->street)) $address_parts[] = $location->street;
            if (!empty($location->city)) $address_parts[] = $location->city;
            if (!empty($location->state_code)) $address_parts[] = $location->state_code;
            if (!empty($location->zip_code)) $address_parts[] = $location->zip_code;
            
            if (!empty($address_parts)) {
                $output .= '<p class="location-address">' . esc_html(implode(', ', $address_parts)) . '</p>';
            }
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render location image
     */
    public static function render_location_image($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'id' => '',
            'size' => 'medium',
            'class' => ''
        ), $atts, 'zen_location_image');
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $table = $wpdb->prefix . 'zen_locations';
        $location = $wpdb->get_row($wpdb->prepare(
            "SELECT rel_image1_id FROM {$table} WHERE location_id = %d LIMIT 1",
            intval($atts['id'])
        ));
        
        if (!$location || empty($location->rel_image1_id)) {
            return '';
        }
        
        $image = wp_get_attachment_image($location->rel_image1_id, $atts['size'], false, array(
            'class' => 'petrifact-location-image ' . esc_attr($atts['class'])
        ));
        
        return $image;
    }
    
    /**
     * Render pinned locations
     */
    public static function render_pinned_locations($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'limit' => -1,
            'template' => 'list',
            'show_images' => 'false'
        ), $atts, 'zen_pinned_locations');
        
        $table = $wpdb->prefix . 'zen_locations';
        $query = "SELECT * FROM {$table} WHERE is_pinned_location = 1 ORDER BY position_in_custom_order ASC";
        
        if ($atts['limit'] > 0) {
            $query .= " LIMIT " . intval($atts['limit']);
        }
        
        $locations = $wpdb->get_results($query);
        
        if (empty($locations)) {
            return '<p>No pinned locations found.</p>';
        }
        
        $output = '<div class="petrifact-pinned-locations">';
        
        foreach ($locations as $location) {
            $output .= '<div class="petrifact-location-item">';
            $output .= '<h3>' . esc_html($location->location_name) . '</h3>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    

PHP;
    }
    
    /**
     * Generate sitespren-related shortcode handlers
     * 
     * @return string PHP code
     */
    private static function generate_sitespren_handlers() {
        return <<<'PHP'
    /**
     * Render sitespren data
     */
    public static function render_sitespren($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'id' => '',
            'field' => '',
            'format' => 'text'
        ), $atts, 'sitespren');
        
        if (empty($atts['id'])) {
            return '';
        }
        
        // Block sensitive fields
        if (in_array($atts['field'], self::$blocked_fields)) {
            return '';
        }
        
        $table = $wpdb->prefix . 'zen_sitespren';
        $sitespren = $wpdb->get_row($wpdb->prepare(
            "SELECT id, true_root_domain, full_subdomain, webproperty_type, is_wp_site, wp_plugin_connected2 
             FROM {$table} WHERE id = %s LIMIT 1",
            $atts['id']
        ));
        
        if (!$sitespren) {
            return '';
        }
        
        // If specific field requested
        if (!empty($atts['field'])) {
            $value = $sitespren->{$atts['field']} ?? '';
            
            if ($atts['format'] === 'status_badge' && $atts['field'] === 'wp_plugin_connected2') {
                $status = $value ? 'Connected' : 'Disconnected';
                $class = $value ? 'status-connected' : 'status-disconnected';
                return '<span class="petrifact-status ' . esc_attr($class) . '">' . esc_html($status) . '</span>';
            }
            
            return esc_html($value);
        }
        
        // Full display
        $output = '<div class="petrifact-sitespren">';
        $output .= '<div class="sitespren-domain">' . esc_html($sitespren->true_root_domain) . '</div>';
        
        if (!empty($sitespren->full_subdomain)) {
            $output .= '<div class="sitespren-subdomain">' . esc_html($sitespren->full_subdomain) . '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render sitespren list
     */
    public static function render_sitespren_list($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'type' => 'all',
            'show' => 'domain,status',
            'limit' => -1,
            'template' => 'list'
        ), $atts, 'zen_sitespren_list');
        
        $table = $wpdb->prefix . 'zen_sitespren';
        $query = "SELECT id, true_root_domain, full_subdomain, webproperty_type, is_wp_site, wp_plugin_connected2 
                  FROM {$table} WHERE 1=1";
        
        if ($atts['type'] === 'wp_sites') {
            $query .= " AND is_wp_site = 1";
        } elseif ($atts['type'] === 'connected') {
            $query .= " AND wp_plugin_connected2 = 1";
        }
        
        $query .= " ORDER BY true_root_domain ASC";
        
        if ($atts['limit'] > 0) {
            $query .= " LIMIT " . intval($atts['limit']);
        }
        
        $sites = $wpdb->get_results($query);
        
        if (empty($sites)) {
            return '<p>No sites found.</p>';
        }
        
        $show_fields = explode(',', $atts['show']);
        
        $output = '<div class="petrifact-sitespren-list">';
        
        foreach ($sites as $site) {
            $output .= '<div class="petrifact-sitespren-item">';
            
            if (in_array('domain', $show_fields)) {
                $output .= '<div class="site-domain">' . esc_html($site->true_root_domain) . '</div>';
            }
            
            if (in_array('subdomain', $show_fields) && !empty($site->full_subdomain)) {
                $output .= '<div class="site-subdomain">' . esc_html($site->full_subdomain) . '</div>';
            }
            
            if (in_array('status', $show_fields)) {
                $status = $site->wp_plugin_connected2 ? 'Connected' : 'Disconnected';
                $class = $site->wp_plugin_connected2 ? 'status-connected' : 'status-disconnected';
                $output .= '<span class="petrifact-status ' . esc_attr($class) . '">' . esc_html($status) . '</span>';
            }
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render sitespren count
     */
    public static function render_sitespren_count($atts) {
        global $wpdb;
        
        $atts = shortcode_atts(array(
            'type' => 'all',
            'label' => ''
        ), $atts, 'zen_sitespren_count');
        
        $table = $wpdb->prefix . 'zen_sitespren';
        $where = '';
        
        if ($atts['type'] === 'connected') {
            $where = " WHERE wp_plugin_connected2 = 1";
        } elseif ($atts['type'] === 'wp_sites') {
            $where = " WHERE is_wp_site = 1";
        }
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}" . $where);
        
        if (!empty($atts['label'])) {
            return esc_html($atts['label']) . ': ' . intval($count);
        }
        
        return intval($count);
    }
    

PHP;
    }
    
    /**
     * Generate utility methods
     * 
     * @return string PHP code
     */
    private static function generate_utility_methods() {
        return <<<'PHP'
    /**
     * Check if a field is safe to display
     */
    private static function is_safe_field($field) {
        return !in_array($field, self::$blocked_fields);
    }
    
    /**
     * Sanitize and validate field name
     */
    private static function sanitize_field_name($field) {
        return preg_replace('/[^a-z0-9_]/i', '', $field);
    }
    
    /**
     * Get table exists check
     */
    private static function table_exists($table_name) {
        global $wpdb;
        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
        return $wpdb->get_var($query) === $table_name;
    }

PHP;
    }
    
    /**
     * Get initialization code
     * 
     * @return string Initialization PHP code
     */
    private static function get_initialization_code() {
        return <<<'PHP'
// Initialize Petrifact Runtime
add_action('init', array('Petrifact_Runtime', 'init'), 5);

// Add basic styles if not provided by theme
add_action('wp_head', function() {
    ?>
    <style>
        .petrifact-services, .petrifact-locations, .petrifact-sitespren-list {
            margin: 20px 0;
        }
        .petrifact-service-item, .petrifact-location-item, .petrifact-sitespren-item {
            margin-bottom: 15px;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .petrifact-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .petrifact-status.status-connected {
            background: #d4edda;
            color: #155724;
        }
        .petrifact-status.status-disconnected {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
    <?php
});

PHP;
    }
    
    /**
     * Log the Petrifact installation
     * 
     * @return void
     */
    private static function log_installation() {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'version' => Petrifact_Registry::get_version(),
            'action' => 'petrifact_installed',
            'mu_file' => WP_CONTENT_DIR . '/mu-plugins/petrifact.php'
        );
        
        update_option('petrifact_last_install', $log_entry);
    }
    
    /**
     * Remove the Petrifact MU-plugin
     * 
     * @return bool True on success
     */
    public static function remove_mu_plugin() {
        $mu_file = WP_CONTENT_DIR . '/mu-plugins/petrifact.php';
        
        if (file_exists($mu_file)) {
            return unlink($mu_file);
        }
        
        return true;
    }
    
    /**
     * Check if Petrifact MU-plugin is installed
     * 
     * @return bool True if installed
     */
    public static function is_installed() {
        $mu_file = WP_CONTENT_DIR . '/mu-plugins/petrifact.php';
        return file_exists($mu_file);
    }
    
    /**
     * Validate the Petrifact installation
     * 
     * @return array Array of validation results
     */
    public static function validate_installation() {
        $results = array(
            'mu_file_exists' => false,
            'version_match' => false,
            'tables_exist' => array(),
            'shortcodes_registered' => array()
        );
        
        // Check MU file
        $mu_file = WP_CONTENT_DIR . '/mu-plugins/petrifact.php';
        $results['mu_file_exists'] = file_exists($mu_file);
        
        // Check version
        $installed_version = get_option(Petrifact_Registry::VERSION_OPTION, '0.0.0');
        $results['version_match'] = version_compare($installed_version, Petrifact_Registry::get_version(), '=');
        
        // Check tables
        global $wpdb;
        $tables = Petrifact_Registry::get_persistent_tables();
        foreach ($tables as $key => $table_info) {
            $table_name = $table_info['name'];
            $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
            $results['tables_exist'][$key] = $exists;
        }
        
        // Check shortcodes (if MU plugin is loaded)
        if (class_exists('Petrifact_Runtime')) {
            $shortcodes = Petrifact_Registry::get_persistent_shortcodes();
            foreach ($shortcodes as $tag => $config) {
                $results['shortcodes_registered'][$tag] = shortcode_exists($tag);
            }
        }
        
        return $results;
    }
}