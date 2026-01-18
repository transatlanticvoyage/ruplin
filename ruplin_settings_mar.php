<?php
/**
 * Ruplin Settings Page
 * 
 * General settings page for the Ruplin plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Settings_Page {
    
    private $options;
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'), 20);
        add_action('admin_init', array($this, 'page_init'));
    }
    
    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Ruplin Hub" menu
        add_submenu_page(
            'snefuru',              // Parent slug (correct parent menu)
            'Ruplin Settings',      // Page title
            'Ruplin Settings',      // Menu title
            'manage_options',       // Capability
            'ruplin_settings_mar',  // Menu slug
            array($this, 'create_admin_page') // Function
        );
    }
    
    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option('ruplin_settings');
        ?>
        <div class="wrap">
            <h1>Ruplin Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields('ruplin_settings_group');
                do_settings_sections('ruplin_settings_mar');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Register and add settings
     */
    public function page_init() {        
        register_setting(
            'ruplin_settings_group', // Option group
            'ruplin_settings', // Option name
            array($this, 'sanitize') // Sanitize
        );
        
        add_settings_section(
            'vectornode_section', // ID
            'VectorNode System', // Title
            array($this, 'print_section_info'), // Callback
            'ruplin_settings_mar' // Page
        );
        
        add_settings_field(
            'enable_vectornode', // ID
            'Enable VectorNode System', // Title
            array($this, 'enable_vectornode_callback'), // Callback
            'ruplin_settings_mar', // Page
            'vectornode_section' // Section
        );
    }
    
    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        
        if (isset($input['enable_vectornode'])) {
            $new_input['enable_vectornode'] = absint($input['enable_vectornode']);
        }
        
        return $new_input;
    }
    
    /**
     * Print the Section text
     */
    public function print_section_info() {
        print 'Configure VectorNode SEO system settings:';
    }
    
    /**
     * Get the settings option array and print one of its values
     */
    public function enable_vectornode_callback() {
        $value = isset($this->options['enable_vectornode']) ? $this->options['enable_vectornode'] : 0;
        printf(
            '<input type="checkbox" id="enable_vectornode" name="ruplin_settings[enable_vectornode]" value="1" %s />',
            checked(1, $value, false)
        );
        echo '<p class="description">Check this box to enable the VectorNode SEO meta tag system.</p>';
    }
    
    /**
     * Helper function to check if VectorNode is enabled
     */
    public static function is_vectornode_enabled() {
        $options = get_option('ruplin_settings');
        return isset($options['enable_vectornode']) && $options['enable_vectornode'] == 1;
    }
}

// Initialize the settings page
if (is_admin()) {
    $ruplin_settings_page = new Ruplin_Settings_Page();
}