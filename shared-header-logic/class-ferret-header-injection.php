<?php
/**
 * Ferret Header Injection Class
 * Handles code injection logic for all headers
 * Manages ferret snippets and wp_head/wp_footer integration
 * Test comment to show in VSCode source control
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Ferret_Header_Injection {
    
    private static $instance = null;
    private $injected_styles = array();
    private $injected_scripts = array();
    
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
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // DISABLED 2026-04-10 — these three hooks caused duplicate frontend output.
        // class-ferret-snippets-frontend.php (Ferret_Snippets_Frontend) is now the
        // sole authoritative system for injecting ferret_header_code, ferret_header_code_2,
        // and ferret_footer_code on singular frontend pages.
        // See: ruplin/shared-header-logic/README_FERRET_INJECTION_DEPRECATION.md
        // DO NOT re-enable without first disabling the equivalent hooks in
        // ruplin/includes/ferret-snippets/class-ferret-snippets-frontend.php
        // add_action('wp_head', array($this, 'inject_header_code'), 1);
        // add_action('wp_head', array($this, 'inject_header_code_2'), 2);
        // add_action('wp_footer', array($this, 'inject_footer_code'), 99);

        // enqueue_ferret_assets stays active — it handles ferret_inline_css and
        // ferret_inline_js columns which Ferret_Snippets_Frontend does NOT cover.
        add_action('wp_enqueue_scripts', array($this, 'enqueue_ferret_assets'));
    }
    
    /**
     * Inject ferret header code (first injection point)
     */
    public function inject_header_code() {
        $ferret_code = $this->get_ferret_header_code();
        if (!empty($ferret_code)) {
            echo "\n<!-- Ferret Header Code -->\n";
            echo $ferret_code;
            echo "\n<!-- End Ferret Header Code -->\n";
        }
    }
    
    /**
     * Inject ferret header code 2 (second injection point)
     */
    public function inject_header_code_2() {
        $ferret_code_2 = $this->get_ferret_header_code_2();
        if (!empty($ferret_code_2)) {
            echo "\n<!-- Ferret Header Code 2 -->\n";
            echo $ferret_code_2;
            echo "\n<!-- End Ferret Header Code 2 -->\n";
        }
    }
    
    /**
     * Inject ferret footer code
     */
    public function inject_footer_code() {
        $ferret_footer = $this->get_ferret_footer_code();
        if (!empty($ferret_footer)) {
            echo "\n<!-- Ferret Footer Code -->\n";
            echo $ferret_footer;
            echo "\n<!-- End Ferret Footer Code -->\n";
        }
    }
    
    /**
     * Get ferret header code from database
     */
    private function get_ferret_header_code() {
        global $wpdb;

        // Try to get from orbitposts table first
        $table_name = $wpdb->prefix . 'zen_orbitposts';
        if ($this->table_exists($table_name)) {
            $post_id = $this->get_current_post_id();
            if ($post_id) {
                $result = $wpdb->get_var($wpdb->prepare("SELECT ferret_header_code FROM {$table_name} WHERE rel_wp_post_id = %d", $post_id));
            } else {
                $result = $wpdb->get_var("SELECT ferret_header_code FROM {$table_name} LIMIT 1");
            }
            if (!empty($result)) {
                return $result;
            }
        }
        
        // Fallback to Ferret Snippets if available
        error_log("DEBUG: Checking if Ferret_Snippets class exists");
        if (class_exists('Ferret_Snippets')) {
            error_log("DEBUG: Ferret_Snippets class exists, attempting to get instance (it's a singleton)");
            try {
                // Note: Ferret_Snippets uses singleton pattern with private constructor
                // WRONG: $ferret = new Ferret_Snippets(); // This will cause fatal error
                // RIGHT: Use get_instance() method
                $ferret = Ferret_Snippets::get_instance();
                error_log("DEBUG: Ferret_Snippets instance retrieved successfully");
                if (method_exists($ferret, 'get_header_code')) {
                    error_log("DEBUG: get_header_code method exists, calling it");
                    return $ferret->get_header_code();
                } else {
                    error_log("DEBUG: ERROR - get_header_code method does not exist on Ferret_Snippets");
                }
            } catch (Exception $e) {
                error_log("DEBUG: Exception while getting Ferret_Snippets instance: " . $e->getMessage());
                error_log("DEBUG: Stack trace: " . $e->getTraceAsString());
            } catch (Error $e) {
                error_log("DEBUG: Fatal error while getting Ferret_Snippets instance: " . $e->getMessage());
                error_log("DEBUG: Stack trace: " . $e->getTraceAsString());
            }
        } else {
            error_log("DEBUG: Ferret_Snippets class does not exist");
        }
        
        // Final fallback to option
        return get_option('ferret_header_code', '');
    }
    
    /**
     * Get ferret header code 2 from database
     */
    private function get_ferret_header_code_2() {
        global $wpdb;

        // Try to get from orbitposts table first
        $table_name = $wpdb->prefix . 'zen_orbitposts';
        if ($this->table_exists($table_name)) {
            $post_id = $this->get_current_post_id();
            if ($post_id) {
                $result = $wpdb->get_var($wpdb->prepare("SELECT ferret_header_code_2 FROM {$table_name} WHERE rel_wp_post_id = %d", $post_id));
            } else {
                $result = $wpdb->get_var("SELECT ferret_header_code_2 FROM {$table_name} LIMIT 1");
            }
            if (!empty($result)) {
                return $result;
            }
        }
        
        // Fallback to option
        return get_option('ferret_header_code_2', '');
    }
    
    /**
     * Get ferret footer code from database
     */
    private function get_ferret_footer_code() {
        global $wpdb;

        // Try to get from orbitposts table first
        $table_name = $wpdb->prefix . 'zen_orbitposts';
        if ($this->table_exists($table_name)) {
            $post_id = $this->get_current_post_id();
            if ($post_id) {
                $result = $wpdb->get_var($wpdb->prepare("SELECT ferret_footer_code FROM {$table_name} WHERE rel_wp_post_id = %d", $post_id));
            } else {
                $result = $wpdb->get_var("SELECT ferret_footer_code FROM {$table_name} LIMIT 1");
            }
            if (!empty($result)) {
                return $result;
            }
        }
        
        // Final fallback to option
        return get_option('ferret_footer_code', '');
    }
    
    /**
     * Enqueue ferret-specific assets
     */
    public function enqueue_ferret_assets() {
        // Check if we have CSS to inject
        $inline_css = $this->get_ferret_inline_css();
        if (!empty($inline_css)) {
            wp_add_inline_style('wp-block-library', $inline_css);
        }
        
        // Check if we have JS to inject
        $inline_js = $this->get_ferret_inline_js();
        if (!empty($inline_js)) {
            wp_add_inline_script('jquery', $inline_js);
        }
    }
    
    /**
     * Get ferret inline CSS
     */
    private function get_ferret_inline_css() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zen_orbitposts';
        if ($this->table_exists($table_name)) {
            $post_id = $this->get_current_post_id();
            if ($post_id) {
                $result = $wpdb->get_var($wpdb->prepare("SELECT ferret_inline_css FROM {$table_name} WHERE rel_wp_post_id = %d", $post_id));
            } else {
                $result = $wpdb->get_var("SELECT ferret_inline_css FROM {$table_name} LIMIT 1");
            }
            if (!empty($result)) {
                return $result;
            }
        }
        
        return get_option('ferret_inline_css', '');
    }
    
    /**
     * Get ferret inline JavaScript
     */
    private function get_ferret_inline_js() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zen_orbitposts';
        if ($this->table_exists($table_name)) {
            $post_id = $this->get_current_post_id();
            if ($post_id) {
                $result = $wpdb->get_var($wpdb->prepare("SELECT ferret_inline_js FROM {$table_name} WHERE rel_wp_post_id = %d", $post_id));
            } else {
                $result = $wpdb->get_var("SELECT ferret_inline_js FROM {$table_name} LIMIT 1");
            }
            if (!empty($result)) {
                return $result;
            }
        }
        
        return get_option('ferret_inline_js', '');
    }
    
    /**
     * Save ferret code to database
     */
    public function save_ferret_codes($header_code = '', $header_code_2 = '', $footer_code = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_orbitposts';
        
        if ($this->table_exists($table_name)) {
            // Check if row exists
            $exists = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
            
            if ($exists > 0) {
                // Update existing row
                $wpdb->update(
                    $table_name,
                    array(
                        'ferret_header_code' => $header_code,
                        'ferret_header_code_2' => $header_code_2,
                        'ferret_footer_code' => $footer_code
                    ),
                    array('id' => 1),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
            } else {
                // Insert new row
                $wpdb->insert(
                    $table_name,
                    array(
                        'ferret_header_code' => $header_code,
                        'ferret_header_code_2' => $header_code_2,
                        'ferret_footer_code' => $footer_code
                    ),
                    array('%s', '%s', '%s')
                );
            }
        } else {
            // Fallback to options
            update_option('ferret_header_code', $header_code);
            update_option('ferret_header_code_2', $header_code_2);
            update_option('ferret_footer_code', $footer_code);
        }
    }
    
    /**
     * Get all ferret codes for editing
     */
    public function get_all_ferret_codes() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_orbitposts';
        
        if ($this->table_exists($table_name)) {
            $result = $wpdb->get_row("SELECT ferret_header_code, ferret_header_code_2, ferret_footer_code FROM {$table_name} LIMIT 1", ARRAY_A);
            
            if ($result) {
                return $result;
            }
        }
        
        // Fallback to options
        return array(
            'ferret_header_code' => get_option('ferret_header_code', ''),
            'ferret_header_code_2' => get_option('ferret_header_code_2', ''),
            'ferret_footer_code' => get_option('ferret_footer_code', '')
        );
    }
    
    /**
     * Inject header-specific ferret code
     */
    public function inject_header_specific_code($header_type) {
        $header_specific_code = $this->get_header_specific_code($header_type);
        if (!empty($header_specific_code)) {
            echo "\n<!-- Header {$header_type} Specific Code -->\n";
            echo $header_specific_code;
            echo "\n<!-- End Header {$header_type} Specific Code -->\n";
        }
    }
    
    /**
     * Get header-specific ferret code
     */
    private function get_header_specific_code($header_type) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_orbitposts';
        
        if ($this->table_exists($table_name)) {
            $column_name = 'ferret_' . $header_type . '_code';
            
            // Check if column exists
            $columns = $wpdb->get_col("DESCRIBE {$table_name}");
            if (in_array($column_name, $columns)) {
                $result = $wpdb->get_var("SELECT {$column_name} FROM {$table_name} LIMIT 1");
                if (!empty($result)) {
                    return $result;
                }
            }
        }
        
        return get_option('ferret_' . $header_type . '_code', '');
    }
    
    /**
     * Get the current WordPress post ID for per-page ferret code lookups.
     * Returns 0 if the current context has no singular post (archives, 404, etc.).
     */
    private function get_current_post_id() {
        $id = get_queried_object_id();
        return absint($id);
    }

    /**
     * Check if table exists
     */
    private function table_exists($table_name) {
        global $wpdb;
        
        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
        return $wpdb->get_var($query) === $table_name;
    }
    
    /**
     * Add inline style for specific header
     */
    public function add_header_inline_style($header_type, $css) {
        if (!isset($this->injected_styles[$header_type])) {
            $this->injected_styles[$header_type] = '';
        }
        $this->injected_styles[$header_type] .= $css;
    }
    
    /**
     * Add inline script for specific header
     */
    public function add_header_inline_script($header_type, $js) {
        if (!isset($this->injected_scripts[$header_type])) {
            $this->injected_scripts[$header_type] = '';
        }
        $this->injected_scripts[$header_type] .= $js;
    }
    
    /**
     * Output injected styles for current header
     */
    public function output_header_styles($header_type) {
        if (!empty($this->injected_styles[$header_type])) {
            echo "<style id=\"ferret-{$header_type}-styles\">\n";
            echo $this->injected_styles[$header_type];
            echo "\n</style>\n";
        }
    }
    
    /**
     * Output injected scripts for current header
     */
    public function output_header_scripts($header_type) {
        if (!empty($this->injected_scripts[$header_type])) {
            echo "<script id=\"ferret-{$header_type}-scripts\">\n";
            echo $this->injected_scripts[$header_type];
            echo "\n</script>\n";
        }
    }
    
    /**
     * Clear ferret cache
     */
    public function clear_cache() {
        delete_transient('ferret_header_code');
        delete_transient('ferret_header_code_2');
        delete_transient('ferret_footer_code');
        
        // Clear any cached codes
        $this->injected_styles = array();
        $this->injected_scripts = array();
    }
}