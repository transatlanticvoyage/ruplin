<?php
/**
 * Shared Header Loader Class
 * Initializes and coordinates all shared header logic classes
 * Test comment to show in VSCode source control
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Shared_Header_Loader {
    
    private static $instance = null;
    private $loaded_classes = array();
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_shared_classes();
        $this->init_hooks();
    }
    
    /**
     * Load all shared header classes
     */
    private function load_shared_classes() {
        $classes_to_load = array(
            'class-header-manager.php',
            'class-silkweaver-integration.php',
            'class-ferret-header-injection.php',
            'class-phone-formatter.php',
            'class-mobile-menu-handler.php'
        );
        
        $base_path = dirname(__FILE__) . '/';
        
        foreach ($classes_to_load as $class_file) {
            $file_path = $base_path . $class_file;
            if (file_exists($file_path)) {
                require_once $file_path;
                $this->loaded_classes[] = $class_file;
            }
        }
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Initialize Ferret injection system early
        if (class_exists('Ruplin_Ferret_Header_Injection')) {
            Ruplin_Ferret_Header_Injection::getInstance();
        }
        
        // Add admin hooks for managing shared header settings
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
        }
        
        // Add AJAX endpoints for header management
        add_action('wp_ajax_ruplin_test_header', array($this, 'ajax_test_header'));
        add_action('wp_ajax_ruplin_clear_header_cache', array($this, 'ajax_clear_header_cache'));
    }
    
    /**
     * Get header manager instance
     */
    public function get_header_manager($header_type = 'header2') {
        if (class_exists('Ruplin_Header_Manager')) {
            return new Ruplin_Header_Manager($header_type);
        }
        return null;
    }
    
    /**
     * Get silkweaver integration instance
     */
    public function get_silkweaver_integration() {
        if (class_exists('Ruplin_Silkweaver_Integration')) {
            return new Ruplin_Silkweaver_Integration();
        }
        return null;
    }
    
    /**
     * Get phone formatter instance
     */
    public function get_phone_formatter() {
        if (class_exists('Ruplin_Phone_Formatter')) {
            return new Ruplin_Phone_Formatter();
        }
        return null;
    }
    
    /**
     * Get mobile menu handler instance
     */
    public function get_mobile_menu_handler($header_type = 'header2') {
        if (class_exists('Ruplin_Mobile_Menu_Handler')) {
            return Ruplin_Mobile_Menu_Handler::getInstance($header_type);
        }
        return null;
    }
    
    /**
     * Render header using shared system
     */
    public function render_header($header_type = 'header2') {
        $header_manager = $this->get_header_manager($header_type);
        
        if ($header_manager) {
            // Add body class
            $header_manager->add_body_class();
            
            // Enqueue assets
            $header_manager->enqueue_assets();
            
            // Get header data and render
            $header_data = $header_manager->get_header_data();
            return $header_manager->render_template($header_data);
        }
        
        return '<p>Shared header system not available.</p>';
    }
    
    /**
     * Add admin menu for shared header management
     */
    public function add_admin_menu() {
        add_submenu_page(
            'ruplin-settings',
            'Shared Headers',
            'Shared Headers',
            'manage_options',
            'ruplin-shared-headers',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Render admin page for shared header management
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Shared Header System</h1>
            
            <div class="card">
                <h2>System Status</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Component</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $this->render_status_row('Header Manager', 'Ruplin_Header_Manager'); ?>
                        <?php $this->render_status_row('Silkweaver Integration', 'Ruplin_Silkweaver_Integration'); ?>
                        <?php $this->render_status_row('Ferret Injection', 'Ruplin_Ferret_Header_Injection'); ?>
                        <?php $this->render_status_row('Phone Formatter', 'Ruplin_Phone_Formatter'); ?>
                        <?php $this->render_status_row('Mobile Menu Handler', 'Ruplin_Mobile_Menu_Handler'); ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Test Headers</h2>
                <p>Test different header types with the shared system:</p>
                
                <button class="button" onclick="testHeader('header1')">Test Header 1</button>
                <button class="button" onclick="testHeader('header2')">Test Header 2</button>
                <button class="button" onclick="testHeader('header3')">Test Header 3</button>
                
                <div id="test-results" style="margin-top: 20px;"></div>
            </div>
            
            <div class="card">
                <h2>Cache Management</h2>
                <button class="button" onclick="clearHeaderCache()">Clear Header Cache</button>
                <button class="button" onclick="clearMenuCache()">Clear Menu Cache</button>
                <button class="button" onclick="clearFerretCache()">Clear Ferret Cache</button>
            </div>
        </div>
        
        <script>
        function testHeader(headerType) {
            var data = {
                'action': 'ruplin_test_header',
                'header_type': headerType,
                'nonce': '<?php echo wp_create_nonce('ruplin_test_header'); ?>'
            };
            
            jQuery.post(ajaxurl, data, function(response) {
                document.getElementById('test-results').innerHTML = response;
            });
        }
        
        function clearHeaderCache() {
            var data = {
                'action': 'ruplin_clear_header_cache',
                'cache_type': 'header',
                'nonce': '<?php echo wp_create_nonce('ruplin_clear_cache'); ?>'
            };
            
            jQuery.post(ajaxurl, data, function(response) {
                alert('Header cache cleared!');
            });
        }
        
        function clearMenuCache() {
            var data = {
                'action': 'ruplin_clear_header_cache',
                'cache_type': 'menu',
                'nonce': '<?php echo wp_create_nonce('ruplin_clear_cache'); ?>'
            };
            
            jQuery.post(ajaxurl, data, function(response) {
                alert('Menu cache cleared!');
            });
        }
        
        function clearFerretCache() {
            var data = {
                'action': 'ruplin_clear_header_cache',
                'cache_type': 'ferret',
                'nonce': '<?php echo wp_create_nonce('ruplin_clear_cache'); ?>'
            };
            
            jQuery.post(ajaxurl, data, function(response) {
                alert('Ferret cache cleared!');
            });
        }
        </script>
        <?php
    }
    
    /**
     * Render status row for admin page
     */
    private function render_status_row($name, $class_name) {
        $status = class_exists($class_name) ? 'Loaded' : 'Not Available';
        $status_class = class_exists($class_name) ? 'success' : 'error';
        
        echo '<tr>';
        echo '<td>' . esc_html($name) . '</td>';
        echo '<td><span class="' . $status_class . '">' . esc_html($status) . '</span></td>';
        echo '<td>';
        if (class_exists($class_name)) {
            echo '<button class="button button-small">Configure</button>';
        } else {
            echo '<em>N/A</em>';
        }
        echo '</td>';
        echo '</tr>';
    }
    
    /**
     * AJAX handler for testing headers
     */
    public function ajax_test_header() {
        check_ajax_referer('ruplin_test_header', 'nonce');
        
        $header_type = sanitize_text_field($_POST['header_type']);
        $header_manager = $this->get_header_manager($header_type);
        
        if ($header_manager) {
            $header_data = $header_manager->get_header_data();
            echo '<h4>Test Results for ' . esc_html($header_type) . ':</h4>';
            echo '<pre>' . print_r($header_data, true) . '</pre>';
        } else {
            echo '<p class="error">Failed to load header manager for ' . esc_html($header_type) . '</p>';
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler for clearing cache
     */
    public function ajax_clear_header_cache() {
        check_ajax_referer('ruplin_clear_cache', 'nonce');
        
        $cache_type = sanitize_text_field($_POST['cache_type']);
        
        switch ($cache_type) {
            case 'menu':
                $silkweaver = $this->get_silkweaver_integration();
                if ($silkweaver) {
                    $silkweaver->clear_menu_cache();
                }
                break;
                
            case 'ferret':
                if (class_exists('Ruplin_Ferret_Header_Injection')) {
                    $ferret = Ruplin_Ferret_Header_Injection::getInstance();
                    $ferret->clear_cache();
                }
                break;
                
            case 'header':
            default:
                // Clear general header cache
                delete_transient('shared_header_cache');
                break;
        }
        
        wp_die();
    }
    
    /**
     * Get loaded classes for debugging
     */
    public function get_loaded_classes() {
        return $this->loaded_classes;
    }
    
    /**
     * Check if all required classes are loaded
     */
    public function is_fully_loaded() {
        $required_classes = array(
            'Ruplin_Header_Manager',
            'Ruplin_Silkweaver_Integration',
            'Ruplin_Ferret_Header_Injection',
            'Ruplin_Phone_Formatter',
            'Ruplin_Mobile_Menu_Handler'
        );
        
        foreach ($required_classes as $class) {
            if (!class_exists($class)) {
                return false;
            }
        }
        
        return true;
    }
}