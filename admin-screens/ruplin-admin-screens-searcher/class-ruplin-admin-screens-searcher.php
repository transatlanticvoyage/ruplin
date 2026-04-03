<?php
/**
 * Ruplin Admin Screens Searcher
 * 
 * Admin page for searching and managing Ruplin admin screens
 * URL: /wp-admin/admin.php?page=ruplin_admin_screens_searcher
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Admin_Screens_Searcher {
    
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
        // Add admin menu with priority to ensure parent exists
        add_action('admin_menu', array($this, 'add_admin_menu'), 25);
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Aggressive notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
        
        // AJAX handlers
        add_action('wp_ajax_ruplin_scan_admin_screens', array($this, 'ajax_scan_admin_screens'));
        add_action('wp_ajax_ruplin_get_admin_screens', array($this, 'ajax_get_admin_screens'));
    }
    
    /**
     * Add menu item to WordPress admin
     */
    public function add_admin_menu() {
        // Add as submenu under Ruplin Hub 1
        add_submenu_page(
            'ruplin_hub_2_mar',  // Parent slug (Ruplin Hub 2)
            'Ruplin Admin Screens Searcher',  // Page title
            'Ruplin Admin Screens Searcher',  // Menu title
            'manage_options',  // Capability
            'ruplin_admin_screens_searcher',  // Menu slug
            array($this, 'render_admin_page')  // Callback
        );
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'ruplin_admin_screens_searcher') {
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
        
        if (strpos($screen->id, 'ruplin_admin_screens_searcher') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'ruplin_admin_screens_searcher')) {
            
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
        <div class="wrap ruplin-admin-screens-searcher">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="ruplin-admin-screens-searcher-controls">
                <button id="ruplin-scan-screens" class="button button-primary">Scan for Admin Screens</button>
                <span class="scan-status"></span>
            </div>
            
            <div class="ruplin-admin-screens-searcher-container">
                <div class="search-box-wrapper">
                    <input type="text" id="ruplin-screen-search" placeholder="Search by URL slug..." class="regular-text" />
                    <span class="screen-count"></span>
                </div>
                
                <div class="screens-table-wrapper">
                    <table id="ruplin-screens-table" class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th width="30">#</th>
                                <th>Menu Title</th>
                                <th>Page Title</th>
                                <th>URL Slug</th>
                                <th>Parent Menu</th>
                                <th>File Path</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="no-items">Click "Scan for Admin Screens" to populate the table.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our specific page
        if ($hook !== 'ruplin-hub-2_page_ruplin_admin_screens_searcher') {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'ruplin-admin-screens-searcher',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'ruplin-admin-screens-searcher',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('ruplin-admin-screens-searcher', 'ruplin_admin_screens_searcher_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ruplin_admin_screens_searcher_nonce')
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
                /* Ultra-aggressive notice suppression for Admin Screens Searcher page */
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .notice,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .notice-error,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .notice-warning,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .notice-success,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .notice-info,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .error,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .updated,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .update-nag,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .wp-pointer,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher #message,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .jetpack-jitm-message,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .woocommerce-message,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .woocommerce-error,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher div.fs-notice,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .monsterinsights-notice,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .yoast-notification,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .notice-alt,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .update-php,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .php-update-nag,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher #update-nag,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher #deprecation-warning,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .plugin-update-tr,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .theme-update-message,
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher [class*="notice"],
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher [class*="updated"],
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher [class*="error"],
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher [id*="notice"],
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher [id*="message"] {
                    display: none !important;
                }
                
                /* Keep our own notices visible if needed */
                body.ruplin-hub-2_page_ruplin_admin_screens_searcher .ruplin-admin-screens-searcher-notice {
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
                    $('.notice, .error, .updated, .update-nag').not('.ruplin-admin-screens-searcher-notice').remove();
                    
                    // Monitor for dynamically added notices and remove them
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            $(mutation.addedNodes).each(function() {
                                if ($(this).hasClass('notice') || 
                                    $(this).hasClass('error') || 
                                    $(this).hasClass('updated') ||
                                    $(this).hasClass('update-nag') ||
                                    $(this).attr('class') && $(this).attr('class').indexOf('notice') !== -1) {
                                    if (!$(this).hasClass('ruplin-admin-screens-searcher-notice')) {
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
    
    /**
     * AJAX handler to scan for admin screens
     */
    public function ajax_scan_admin_screens() {
        check_ajax_referer('ruplin_admin_screens_searcher_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $screens = $this->scan_for_admin_screens();
        
        // Store in wp_options as JSON
        update_option('ruplin_admin_screens_cache', json_encode($screens));
        update_option('ruplin_admin_screens_last_scan', current_time('mysql'));
        
        wp_send_json_success(array(
            'screens' => $screens,
            'count' => count($screens),
            'last_scan' => current_time('mysql')
        ));
    }
    
    /**
     * AJAX handler to get cached admin screens
     */
    public function ajax_get_admin_screens() {
        check_ajax_referer('ruplin_admin_screens_searcher_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $cached_screens = get_option('ruplin_admin_screens_cache', '[]');
        $screens = json_decode($cached_screens, true);
        $last_scan = get_option('ruplin_admin_screens_last_scan', 'Never');
        
        wp_send_json_success(array(
            'screens' => $screens ? $screens : array(),
            'count' => count($screens),
            'last_scan' => $last_scan
        ));
    }
    
    /**
     * Scan for all admin screens in Ruplin
     */
    private function scan_for_admin_screens() {
        global $menu, $submenu;
        $screens = array();
        $screen_id = 1;
        
        // First, look for Ruplin Hub menu items
        if (isset($submenu['snefuru'])) {
            foreach ($submenu['snefuru'] as $item) {
                if (isset($item[2])) { // menu slug
                    $screens[] = array(
                        'id' => $screen_id++,
                        'menu_title' => $item[0],
                        'page_title' => $item[3] ?? $item[0],
                        'slug' => $item[2],
                        'parent' => 'Ruplin Hub 1',
                        'url' => admin_url('admin.php?page=' . $item[2]),
                        'file_path' => $this->find_screen_file($item[2])
                    );
                }
            }
        }
        
        // Look for screens under ruplin-settings
        if (isset($submenu['ruplin-settings'])) {
            foreach ($submenu['ruplin-settings'] as $item) {
                if (isset($item[2])) {
                    $screens[] = array(
                        'id' => $screen_id++,
                        'menu_title' => $item[0],
                        'page_title' => $item[3] ?? $item[0],
                        'slug' => $item[2],
                        'parent' => 'Ruplin Settings',
                        'url' => admin_url('admin.php?page=' . $item[2]),
                        'file_path' => $this->find_screen_file($item[2])
                    );
                }
            }
        }
        
        // Scan for files in admin-screens directory
        $admin_screens_dir = SNEFURU_PLUGIN_PATH . 'admin-screens/';
        if (is_dir($admin_screens_dir)) {
            $dirs = scandir($admin_screens_dir);
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($admin_screens_dir . $dir)) {
                    // Check if this screen is already in our list
                    $slug_guess = str_replace('-', '_', $dir);
                    $found = false;
                    foreach ($screens as $screen) {
                        if ($screen['slug'] === $slug_guess) {
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        // This might be an unregistered screen
                        $screens[] = array(
                            'id' => $screen_id++,
                            'menu_title' => '(Unregistered)',
                            'page_title' => ucwords(str_replace(array('-', '_'), ' ', $dir)),
                            'slug' => $slug_guess,
                            'parent' => 'Unknown',
                            'url' => admin_url('admin.php?page=' . $slug_guess),
                            'file_path' => 'admin-screens/' . $dir . '/'
                        );
                    }
                }
            }
        }
        
        // Scan for known patterns in includes directory
        $patterns = array(
            'page' => '*-page.php',
            'mar' => '*-mar.php',
            'admin' => '*-admin.php'
        );
        
        foreach ($patterns as $type => $pattern) {
            $files = glob(SNEFURU_PLUGIN_PATH . 'includes/' . $pattern);
            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $slug_guess = str_replace('-', '_', $filename);
                
                // Check if already found
                $found = false;
                foreach ($screens as $screen) {
                    if (strpos($screen['slug'], $slug_guess) !== false) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $screens[] = array(
                        'id' => $screen_id++,
                        'menu_title' => '(Potential)',
                        'page_title' => ucwords(str_replace(array('-', '_'), ' ', $filename)),
                        'slug' => $slug_guess,
                        'parent' => 'Unknown',
                        'url' => admin_url('admin.php?page=' . $slug_guess),
                        'file_path' => 'includes/' . basename($file)
                    );
                }
            }
        }
        
        return $screens;
    }
    
    /**
     * Try to find the file path for a screen
     */
    private function find_screen_file($slug) {
        // Convert slug to potential file names
        $hyphenated = str_replace('_', '-', $slug);
        $underscored = $slug;
        
        // Check admin-screens directory
        if (is_dir(SNEFURU_PLUGIN_PATH . 'admin-screens/' . $hyphenated)) {
            return 'admin-screens/' . $hyphenated . '/';
        }
        if (is_dir(SNEFURU_PLUGIN_PATH . 'admin-screens/' . $underscored)) {
            return 'admin-screens/' . $underscored . '/';
        }
        
        // Check for specific files
        $possible_files = array(
            'includes/pages/' . $hyphenated . '-page.php',
            'includes/pages/' . $underscored . '-page.php',
            'includes/' . $hyphenated . '.php',
            'includes/' . $underscored . '.php',
            $hyphenated . '/' . $hyphenated . '-page.php',
            $underscored . '/' . $underscored . '-page.php',
        );
        
        foreach ($possible_files as $file) {
            if (file_exists(SNEFURU_PLUGIN_PATH . $file)) {
                return $file;
            }
        }
        
        return 'Unknown';
    }
}

// Initialize the class
Ruplin_Admin_Screens_Searcher::get_instance();