<?php
/**
 * Contact Form Services Shortcode
 * 
 * Provides shortcode functionality for listing services in contact forms
 * 
 * @package Ruplin
 * @subpackage ContactFormServicesShortcode
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Contact_Form_Services_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_shortcode('ruplin_shortcode_to_list_services_for_contact_form', array($this, 'render_services_shortcode'));
        add_shortcode('ruplin_shortcode_for_contact_form_1_endpoint', array($this, 'render_contact_form_endpoint_shortcode'));
        
        // Early notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
    }
    
    /**
     * Add submenu page to Ruplin Hub
     */
    public function add_admin_menu() {
        add_submenu_page(
            'snefuru',
            'Contact Form Services Shortcode',
            'Contact_Form_Services_Shortcode',
            'manage_options',
            'contact_form_services_shortcode',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        // Check if we're on our page
        if (isset($_GET['page']) && $_GET['page'] === 'contact_form_services_shortcode') {
            // Immediate notice suppression
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Override the global filter
            add_action('admin_notices', '__return_false', -999999);
            add_action('all_admin_notices', '__return_false', -999999);
            add_action('network_admin_notices', '__return_false', -999999);
            add_action('user_admin_notices', '__return_false', -999999);
        }
    }
    
    /**
     * Check current screen and suppress notices
     */
    public function check_and_suppress_notices($screen) {
        if (!$screen) {
            return;
        }
        
        // Check if we're on the Contact Form Services Shortcode page
        if (strpos($screen->id, 'contact_form_services_shortcode') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'contact_form_services_shortcode')) {
            
            // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices'); 
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Override with empty function
            add_action('admin_notices', '__return_false', 999);
            add_action('all_admin_notices', '__return_false', 999); 
            add_action('network_admin_notices', '__return_false', 999);
            add_action('user_admin_notices', '__return_false', 999);
        }
    }
    
    /**
     * Suppress all admin notices - comprehensive version
     */
    private function suppress_all_admin_notices() {
        // Remove all admin notices immediately
        add_action('admin_print_styles', function() {
            // Remove all notice actions
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            
            // Remove user admin notices
            global $wp_filter;
            if (isset($wp_filter['user_admin_notices'])) {
                unset($wp_filter['user_admin_notices']);
            }
        }, 0);
        
        // Additional cleanup for persistent notices
        add_action('admin_head', function() {
            // Hide any notices that slip through via CSS
            echo '<style type="text/css">
                .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
                .updated, .error, .update-nag, .admin-notice,
                div.notice, div.updated, div.error, div.update-nag,
                .wrap > .notice, .wrap > .updated, .wrap > .error,
                #adminmenu + .notice, #adminmenu + .updated, #adminmenu + .error,
                .update-php, .php-update-nag,
                .plugin-update-tr, .theme-update-message,
                .update-message, .updating-message,
                #update-nag, #deprecation-warning {
                    display: none !important;
                }
                
                /* Hide WordPress core update notices */
                .update-core-php, .notice-alt {
                    display: none !important;
                }
                
                /* Hide plugin activation/deactivation notices */
                .activated, .deactivated {
                    display: none !important;
                }
                
                /* Hide file permission and other system warnings */
                .notice-warning, .notice-error {
                    display: none !important;
                }
                
                /* Hide any remaining notices in common locations */
                .wrap .notice:first-child,
                .wrap > div.notice,
                .wrap > div.updated,
                .wrap > div.error {
                    display: none !important;
                }
                
                /* Nuclear option - hide anything that looks like a notice */
                [class*="notice"], [class*="updated"], [class*="error"],
                [id*="notice"], [id*="message"] {
                    display: none !important;
                }
                
                /* Restore our legitimate content */
                .wrap h1, .wrap .contact-form-services-content {
                    display: block !important;
                }
            </style>';
        }, 1);
        
        // Remove all hooks related to admin notices
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        // Add empty functions to prevent any notices
        add_action('admin_notices', '__return_false', PHP_INT_MAX);
        add_action('all_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('network_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('user_admin_notices', '__return_false', PHP_INT_MAX);
    }
    
    /**
     * Render the services shortcode
     */
    public function render_services_shortcode($atts) {
        global $wpdb;
        
        // Get the pylons table name
        $pylons_table = $wpdb->prefix . 'pylons';
        $posts_table = $wpdb->posts;
        
        // Check if pylons table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$pylons_table}'");
        
        if (!$table_exists) {
            return '<!-- Pylons table not found -->';
        }
        
        // Query to get all services (servicepage archetype) with their monikers
        $query = "SELECT DISTINCT pyl.moniker 
                  FROM {$pylons_table} pyl
                  INNER JOIN {$posts_table} p ON pyl.rel_wp_post_id = p.ID
                  WHERE p.post_status = 'publish' 
                  AND pyl.pylon_archetype = 'servicepage'
                  AND pyl.moniker IS NOT NULL 
                  AND pyl.moniker != ''
                  ORDER BY pyl.moniker ASC";
        
        $services = $wpdb->get_results($query);
        
        // Build the options HTML
        $output = '';
        
        if (!empty($services)) {
            foreach ($services as $service) {
                $moniker = esc_html($service->moniker);
                $output .= '<option>' . $moniker . '</option>' . "\n";
            }
        }
        
        // Add "Other" option at the end
        $output .= '<option>Other</option>' . "\n";
        
        return $output;
    }
    
    /**
     * Render the contact form endpoint shortcode
     * Returns the value of contact_form_1_endpoint from wp_zen_sitespren table
     */
    public function render_contact_form_endpoint_shortcode($atts) {
        global $wpdb;
        
        // Get the zen_sitespren table name
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        
        // Check if zen_sitespren table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$sitespren_table}'");
        
        if (!$table_exists) {
            return '<!-- zen_sitespren table not found -->';
        }
        
        // Get the contact_form_1_endpoint value from the single row in zen_sitespren
        $endpoint = $wpdb->get_var("SELECT contact_form_1_endpoint FROM {$sitespren_table} LIMIT 1");
        
        // Return the endpoint value or empty string if not found
        return $endpoint ? esc_html($endpoint) : '';
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap contact-form-services-content">
            <h1>Contact Form Shortcodes</h1>
            
            <div style="background: white; border: 1px solid #ddd; padding: 20px; margin-top: 20px; border-radius: 5px;">
                <h2 style="margin-top: 0;">Available Shortcodes</h2>
                
                <h3>1. Services List Shortcode</h3>
                <p style="margin-bottom: 15px;">
                    Use this shortcode in your contact forms to automatically generate a dropdown list of all services from your site:
                </p>
                
                <div style="background: #f0f0f0; border: 1px solid #ccc; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <input type="text" 
                           value="[ruplin_shortcode_to_list_services_for_contact_form]" 
                           readonly 
                           style="width: 100%; padding: 10px; font-family: monospace; font-size: 14px; background: white; border: 1px solid #ddd; border-radius: 3px;"
                           onclick="this.select();">
                </div>
                
                <div style="background: #f9f9f9; border-left: 4px solid #0073aa; padding: 15px; margin-top: 20px;">
                    <h3 style="margin-top: 0;">How it works:</h3>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li>Automatically finds all published pages/posts with "servicepage" archetype</li>
                        <li>Retrieves the moniker value from the pylons database</li>
                        <li>Generates HTML option tags for use in select dropdowns</li>
                        <li>Includes an "Other" option at the end</li>
                    </ul>
                </div>
                
                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin-top: 20px;">
                    <h3 style="margin-top: 0; color: #856404;">Example Output:</h3>
                    <pre style="background: white; padding: 10px; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto;">
&lt;option&gt;Roof Repair&lt;/option&gt;
&lt;option&gt;Roof Replacement&lt;/option&gt;
&lt;option&gt;Inspection&lt;/option&gt;
&lt;option&gt;Gutter Install/Repair&lt;/option&gt;
&lt;option&gt;Siding&lt;/option&gt;
&lt;option&gt;Other&lt;/option&gt;</pre>
                </div>
                
                <hr style="margin: 30px 0; border-color: #ddd;">
                
                <h3>2. Contact Form Endpoint Shortcode</h3>
                <p style="margin-bottom: 15px;">
                    Use this shortcode to output the contact form endpoint URL from the database:
                </p>
                
                <div style="background: #f0f0f0; border: 1px solid #ccc; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <input type="text" 
                           value="[ruplin_shortcode_for_contact_form_1_endpoint]" 
                           readonly 
                           style="width: 100%; padding: 10px; font-family: monospace; font-size: 14px; background: white; border: 1px solid #ddd; border-radius: 3px;"
                           onclick="this.select();">
                </div>
                
                <div style="background: #f9f9f9; border-left: 4px solid #0073aa; padding: 15px;">
                    <h3 style="margin-top: 0;">How it works:</h3>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li>Retrieves the contact_form_1_endpoint value from wp_zen_sitespren table</li>
                        <li>Returns the endpoint URL stored in the database</li>
                        <li>Can be used in form action attributes or JavaScript</li>
                        <li>Returns empty string if no endpoint is configured</li>
                    </ul>
                </div>
                
                <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 4px; margin-top: 20px;">
                    <h3 style="margin-top: 0; color: #0c5460;">Current Endpoint:</h3>
                    <?php
                    global $wpdb;
                    $sitespren_table = $wpdb->prefix . 'zen_sitespren';
                    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$sitespren_table}'");
                    
                    if ($table_exists) {
                        $endpoint = $wpdb->get_var("SELECT contact_form_1_endpoint FROM {$sitespren_table} LIMIT 1");
                        if ($endpoint) {
                            echo '<code style="background: white; padding: 8px; display: block; border: 1px solid #ddd; border-radius: 3px; word-break: break-all;">' . esc_html($endpoint) . '</code>';
                        } else {
                            echo '<p style="color: #666; font-style: italic; margin: 0;">No endpoint configured in database.</p>';
                        }
                    } else {
                        echo '<p style="color: #d9534f; font-style: italic; margin: 0;">zen_sitespren table not found.</p>';
                    }
                    ?>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <h3>Current Services in Database:</h3>
                    <div style="background: white; border: 1px solid #ddd; padding: 15px; border-radius: 4px; max-height: 300px; overflow-y: auto;">
                        <?php
                        // Show current services for preview
                        global $wpdb;
                        $pylons_table = $wpdb->prefix . 'pylons';
                        $posts_table = $wpdb->posts;
                        
                        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$pylons_table}'");
                        
                        if ($table_exists) {
                            $query = "SELECT DISTINCT pyl.moniker 
                                      FROM {$pylons_table} pyl
                                      INNER JOIN {$posts_table} p ON pyl.rel_wp_post_id = p.ID
                                      WHERE p.post_status = 'publish' 
                                      AND pyl.pylon_archetype = 'servicepage'
                                      AND pyl.moniker IS NOT NULL 
                                      AND pyl.moniker != ''
                                      ORDER BY pyl.moniker ASC";
                            
                            $services = $wpdb->get_results($query);
                            
                            if (!empty($services)) {
                                echo '<ul style="list-style: none; margin: 0; padding: 0;">';
                                foreach ($services as $service) {
                                    echo '<li style="padding: 5px 0; border-bottom: 1px solid #f0f0f0;">• ' . esc_html($service->moniker) . '</li>';
                                }
                                echo '<li style="padding: 5px 0; color: #666;">• Other</li>';
                                echo '</ul>';
                            } else {
                                echo '<p style="color: #666; font-style: italic;">No services found with "servicepage" archetype.</p>';
                            }
                        } else {
                            echo '<p style="color: #d9534f; font-style: italic;">Pylons table not found in database.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the class
new Contact_Form_Services_Shortcode();