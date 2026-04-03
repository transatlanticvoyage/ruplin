<?php
/**
 * Ruplin Hub 2 MAR Admin Page
 * 
 * Top-level admin page for Ruplin Hub 2
 * URL: /wp-admin/admin.php?page=ruplin_hub_2_mar
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Hub_2_Mar {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add admin menu - must run before child subpages (sld_editor at 15, etc.)
        add_action('admin_menu', array($this, 'add_admin_menu'), 14);
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Aggressive notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
        
        // AJAX handlers can be added here
        // add_action('wp_ajax_ruplin_hub_2_action', array($this, 'handle_ajax'));
    }
    
    /**
     * Add top-level menu item to WordPress admin
     */
    public function add_admin_menu() {
        // Add as top-level menu item, positioned after Ruplin Hub 1
        add_menu_page(
            'Ruplin Hub 2',  // Page title
            'Ruplin Hub 2',  // Menu title
            'manage_options',  // Capability
            'ruplin_hub_2_mar',  // Menu slug
            array($this, 'render_admin_page'),  // Callback
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'),  // Icon (same as Ruplin Hub 1)
            3.65  // Position (right after Ruplin Hub 1 which is at 3.6)
        );
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'ruplin_hub_2_mar') {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
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
        
        if (strpos($screen->id, 'ruplin_hub_2_mar') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'ruplin_hub_2_mar')) {
            
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            add_action('admin_notices', '__return_false', 999);
            add_action('all_admin_notices', '__return_false', 999);
            add_action('network_admin_notices', '__return_false', 999);
            add_action('user_admin_notices', '__return_false', 999);
        }
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // Aggressive notice/warning suppression
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap ruplin-hub-2-mar">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="ruplin-hub-2-mar-container">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 8px;"><a href="<?php echo admin_url('admin.php?page=sld_editor'); ?>">SLD Editor (Site Level Data)</a></li>
                    <li style="margin-bottom: 8px;"><a href="<?php echo admin_url('admin.php?page=hazelnut_items_mar'); ?>">Hazelnut Items Mar</a></li>
                    <li style="margin-bottom: 8px;"><a href="<?php echo admin_url('admin.php?page=work_projects_mar'); ?>">Work Projects Mar</a></li>
                    <li style="margin-bottom: 8px;"><a href="<?php echo admin_url('admin.php?page=nectar_controls_mar'); ?>">Nectar Controls Mar</a></li>
                    <li style="margin-bottom: 8px;"><a href="<?php echo admin_url('admin.php?page=sitemap_shortcode_mar'); ?>">Sitemap Shortcode Mar</a></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our specific page
        if ($hook !== 'toplevel_page_ruplin_hub_2_mar') {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'ruplin-hub-2-mar',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'ruplin-hub-2-mar',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('ruplin-hub-2-mar', 'ruplin_hub_2_mar_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ruplin_hub_2_mar_nonce')
        ));
    }
    
    /**
     * Aggressive admin notice suppression
     */
    private function suppress_all_admin_notices() {
        // Remove all admin notices
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        // Add comprehensive CSS to hide notices
        add_action('admin_head', function() {
            ?>
            <style>
                /* Ultra-aggressive notice suppression for Ruplin Hub 2 page */
                body.toplevel_page_ruplin_hub_2_mar .notice,
                body.toplevel_page_ruplin_hub_2_mar .notice-error,
                body.toplevel_page_ruplin_hub_2_mar .notice-warning,
                body.toplevel_page_ruplin_hub_2_mar .notice-success,
                body.toplevel_page_ruplin_hub_2_mar .notice-info,
                body.toplevel_page_ruplin_hub_2_mar .error,
                body.toplevel_page_ruplin_hub_2_mar .updated,
                body.toplevel_page_ruplin_hub_2_mar .update-nag,
                body.toplevel_page_ruplin_hub_2_mar .wp-pointer,
                body.toplevel_page_ruplin_hub_2_mar #message,
                body.toplevel_page_ruplin_hub_2_mar .jetpack-jitm-message,
                body.toplevel_page_ruplin_hub_2_mar .woocommerce-message,
                body.toplevel_page_ruplin_hub_2_mar .woocommerce-error,
                body.toplevel_page_ruplin_hub_2_mar div.fs-notice,
                body.toplevel_page_ruplin_hub_2_mar .monsterinsights-notice,
                body.toplevel_page_ruplin_hub_2_mar .yoast-notification,
                body.toplevel_page_ruplin_hub_2_mar .notice-alt,
                body.toplevel_page_ruplin_hub_2_mar .update-php,
                body.toplevel_page_ruplin_hub_2_mar .php-update-nag,
                body.toplevel_page_ruplin_hub_2_mar #update-nag,
                body.toplevel_page_ruplin_hub_2_mar #deprecation-warning,
                body.toplevel_page_ruplin_hub_2_mar .plugin-update-tr,
                body.toplevel_page_ruplin_hub_2_mar .theme-update-message,
                body.toplevel_page_ruplin_hub_2_mar [class*="notice"],
                body.toplevel_page_ruplin_hub_2_mar [class*="updated"],
                body.toplevel_page_ruplin_hub_2_mar [class*="error"],
                body.toplevel_page_ruplin_hub_2_mar [id*="notice"],
                body.toplevel_page_ruplin_hub_2_mar [id*="message"] {
                    display: none !important;
                }
                
                /* Keep our own notices visible if needed */
                body.toplevel_page_ruplin_hub_2_mar .ruplin-hub-2-mar-notice {
                    display: block !important;
                }
            </style>
            <?php
        }, 999);
        
        // Additional JavaScript-based suppression
        add_action('admin_footer', function() {
            ?>
            <script>
                jQuery(document).ready(function($) {
                    // Remove any notices that were added after page load
                    $('.notice, .error, .updated, .update-nag').not('.ruplin-hub-2-mar-notice').remove();
                    
                    // Monitor for dynamically added notices and remove them
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            $(mutation.addedNodes).each(function() {
                                if ($(this).hasClass('notice') || 
                                    $(this).hasClass('error') || 
                                    $(this).hasClass('updated') ||
                                    $(this).hasClass('update-nag') ||
                                    $(this).attr('class') && $(this).attr('class').indexOf('notice') !== -1) {
                                    if (!$(this).hasClass('ruplin-hub-2-mar-notice')) {
                                        $(this).remove();
                                    }
                                }
                            });
                        });
                    });
                    
                    // Start observing the document body for changes
                    if (document.body) {
                        observer.observe(document.body, {
                            childList: true,
                            subtree: true
                        });
                    }
                    
                    // Also observe the wpbody-content area specifically
                    var wpbody = document.getElementById('wpbody-content');
                    if (wpbody) {
                        observer.observe(wpbody, {
                            childList: true,
                            subtree: true
                        });
                    }
                });
            </script>
            <?php
        }, 999);
        
        // PHP-based notice blocking
        add_action('admin_print_styles', function() {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            
            global $wp_filter;
            if (isset($wp_filter['user_admin_notices'])) {
                unset($wp_filter['user_admin_notices']);
            }
        }, 0);
    }
}

// Initialize the class
Ruplin_Hub_2_Mar::get_instance();