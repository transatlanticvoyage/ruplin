<?php
/**
 * Spearhead Connect Helper Screen
 * 
 * Debug page to help diagnose WordPress application password issues
 * 
 * @package Ruplin
 * @subpackage Spearhead_Application_Password_Connector
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Spearhead Helper Page Class
 */
class Spearhead_Helper_Page {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            null, // Hidden page (no parent menu)
            'Spearhead Connect Helper',
            'Spearhead Connect Helper',
            'manage_options',
            'spearhead_connect_helper_screen',
            array($this, 'render_page')
        );
    }
    
    /**
     * Render the helper page
     */
    public function render_page() {
        // Get debugging information
        $siteurl = get_option('siteurl');
        $home = get_option('home');
        $is_ssl = is_ssl();
        $server_https = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set';
        $server_port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 'not set';
        $server_scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'not set';
        $x_forwarded_proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'not set';
        $x_forwarded_ssl = isset($_SERVER['HTTP_X_FORWARDED_SSL']) ? $_SERVER['HTTP_X_FORWARDED_SSL'] : 'not set';
        
        // WordPress functions
        $wp_is_https = function_exists('wp_is_using_https') ? wp_is_using_https() : 'function not found';
        $wp_app_pwd_available = function_exists('wp_is_application_passwords_available') ? wp_is_application_passwords_available() : 'function not found';
        $wp_app_pwd_for_user = false;
        if (function_exists('wp_is_application_passwords_available_for_user')) {
            $current_user = wp_get_current_user();
            $wp_app_pwd_for_user = wp_is_application_passwords_available_for_user($current_user);
        }
        
        // Check constants
        $force_ssl_admin = defined('FORCE_SSL_ADMIN') ? FORCE_SSL_ADMIN : 'not defined';
        $wp_app_passwords_const = defined('WP_APPLICATION_PASSWORDS') ? WP_APPLICATION_PASSWORDS : 'not defined';
        
        // Current user info
        $current_user = wp_get_current_user();
        $user_caps = $current_user->allcaps;
        
        ?>
        <div class="wrap">
            <h1>Spearhead Connect Helper Screen</h1>
            <p>Debug information for WordPress Application Password connectivity</p>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0;">
                <h2>🔍 Critical Settings</h2>
                <table class="widefat" style="margin-top: 10px;">
                    <tbody>
                        <tr>
                            <td style="width: 300px;"><strong>Site URL (wp_options)</strong></td>
                            <td>
                                <input type="text" value="<?php echo esc_attr($siteurl); ?>" readonly style="width: 100%; background: #f0f0f0;">
                                <?php if (strpos($siteurl, 'http://') === 0): ?>
                                    <span style="color: red; font-weight: bold;">⚠️ Using HTTP - should be HTTPS!</span>
                                <?php else: ?>
                                    <span style="color: green;">✓ Using HTTPS</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Home URL (wp_options)</strong></td>
                            <td>
                                <input type="text" value="<?php echo esc_attr($home); ?>" readonly style="width: 100%; background: #f0f0f0;">
                                <?php if (strpos($home, 'http://') === 0): ?>
                                    <span style="color: red; font-weight: bold;">⚠️ Using HTTP - should be HTTPS!</span>
                                <?php else: ?>
                                    <span style="color: green;">✓ Using HTTPS</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0;">
                <h2>🔒 HTTPS Detection</h2>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td style="width: 300px;"><strong>is_ssl()</strong></td>
                            <td><?php echo $is_ssl ? '<span style="color: green;">✓ TRUE</span>' : '<span style="color: red;">✗ FALSE</span>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>wp_is_using_https()</strong></td>
                            <td><?php echo $wp_is_https ? '<span style="color: green;">✓ TRUE</span>' : '<span style="color: red;">✗ FALSE</span>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>$_SERVER['HTTPS']</strong></td>
                            <td><?php echo esc_html($server_https); ?></td>
                        </tr>
                        <tr>
                            <td><strong>$_SERVER['SERVER_PORT']</strong></td>
                            <td><?php echo esc_html($server_port); ?></td>
                        </tr>
                        <tr>
                            <td><strong>$_SERVER['REQUEST_SCHEME']</strong></td>
                            <td><?php echo esc_html($server_scheme); ?></td>
                        </tr>
                        <tr>
                            <td><strong>HTTP_X_FORWARDED_PROTO</strong></td>
                            <td><?php echo esc_html($x_forwarded_proto); ?> <?php if ($x_forwarded_proto === 'https') echo '<span style="color: green;">(Proxy detected)</span>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>HTTP_X_FORWARDED_SSL</strong></td>
                            <td><?php echo esc_html($x_forwarded_ssl); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0;">
                <h2>🔑 Application Passwords</h2>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td style="width: 300px;"><strong>wp_is_application_passwords_available()</strong></td>
                            <td><?php echo $wp_app_pwd_available ? '<span style="color: green;">✓ Available</span>' : '<span style="color: red;">✗ Not Available</span>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Available for current user</strong></td>
                            <td><?php echo $wp_app_pwd_for_user ? '<span style="color: green;">✓ Yes</span>' : '<span style="color: red;">✗ No</span>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>WP_APPLICATION_PASSWORDS constant</strong></td>
                            <td>
                                <?php 
                                if ($wp_app_passwords_const === 'not defined') {
                                    echo '<span style="color: gray;">Not defined (defaults to enabled)</span>';
                                } elseif ($wp_app_passwords_const) {
                                    echo '<span style="color: green;">✓ Enabled</span>';
                                } else {
                                    echo '<span style="color: red;">✗ Disabled in wp-config.php!</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>FORCE_SSL_ADMIN</strong></td>
                            <td><?php echo $force_ssl_admin === true ? 'TRUE' : ($force_ssl_admin === false ? 'FALSE' : 'Not defined'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0;">
                <h2>👤 Current User</h2>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td style="width: 300px;"><strong>Username</strong></td>
                            <td><?php echo esc_html($current_user->user_login); ?></td>
                        </tr>
                        <tr>
                            <td><strong>User ID</strong></td>
                            <td><?php echo esc_html($current_user->ID); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Has manage_application_passwords cap</strong></td>
                            <td><?php echo isset($user_caps['manage_application_passwords']) && $user_caps['manage_application_passwords'] ? '<span style="color: green;">✓ Yes</span>' : '<span style="color: red;">✗ No</span>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Is Administrator</strong></td>
                            <td><?php echo current_user_can('administrator') ? '<span style="color: green;">✓ Yes</span>' : '<span style="color: red;">✗ No</span>'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0;">
                <h2>🛠️ Quick Actions</h2>
                
                <?php if (strpos($siteurl, 'http://') === 0 || strpos($home, 'http://') === 0): ?>
                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 4px;">
                    <strong>⚠️ URLs are using HTTP!</strong><br>
                    To fix this, you can update the database:<br>
                    <code style="background: #f0f0f0; padding: 5px; display: inline-block; margin: 5px 0;">
                    UPDATE wp_options SET option_value = REPLACE(option_value, 'http://<?php echo parse_url($siteurl, PHP_URL_HOST); ?>', 'https://<?php echo parse_url($siteurl, PHP_URL_HOST); ?>') WHERE option_name IN ('siteurl', 'home');
                    </code>
                </div>
                <?php endif; ?>
                
                <div style="margin: 15px 0;">
                    <a href="<?php echo admin_url('authorize-application.php?app_name=Tregnar2_Debug'); ?>" 
                       target="_blank" 
                       class="button button-primary">
                        Test Authorization Page
                    </a>
                    <span style="margin-left: 10px;">Opens the WordPress authorization page to test if it loads</span>
                </div>
                
                <div style="margin: 15px 0;">
                    <a href="<?php echo rest_url('spearhead/v1/debug/https-check'); ?>" 
                       target="_blank" 
                       class="button">
                        Check REST API Debug
                    </a>
                    <span style="margin-left: 10px;">Shows JSON debug output</span>
                </div>
                
                <div style="margin: 15px 0;">
                    <button onclick="navigator.clipboard.writeText('<?php echo esc_js(admin_url()); ?>');" class="button">
                        Copy Admin URL
                    </button>
                    <code style="margin-left: 10px;"><?php echo admin_url(); ?></code>
                </div>
            </div>
            
            <div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0;">
                <h2>📋 Diagnosis</h2>
                <?php
                $issues = array();
                
                if (strpos($siteurl, 'http://') === 0) {
                    $issues[] = '❌ Site URL in database is using HTTP instead of HTTPS';
                }
                if (strpos($home, 'http://') === 0) {
                    $issues[] = '❌ Home URL in database is using HTTP instead of HTTPS';
                }
                if (!$is_ssl) {
                    $issues[] = '❌ WordPress is_ssl() returns false - server may not be passing HTTPS headers correctly';
                }
                if (!$wp_is_https) {
                    $issues[] = '❌ WordPress thinks site is not using HTTPS';
                }
                if (!$wp_app_pwd_available) {
                    $issues[] = '❌ Application passwords are not available (likely due to HTTPS issues)';
                }
                if ($wp_app_passwords_const === false) {
                    $issues[] = '❌ WP_APPLICATION_PASSWORDS is explicitly disabled in wp-config.php';
                }
                
                if (empty($issues)) {
                    echo '<p style="color: green; font-size: 16px;">✅ All checks passed! Application passwords should work.</p>';
                } else {
                    echo '<p style="color: red; font-size: 16px;">Issues found:</p>';
                    echo '<ul>';
                    foreach ($issues as $issue) {
                        echo '<li>' . $issue . '</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
            
            <div style="background: #e3f2fd; border: 1px solid #2196f3; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong>💡 Tip:</strong> If you're behind a proxy (Cloudflare, nginx, etc), WordPress might not detect HTTPS correctly. 
                Add this to wp-config.php:<br>
                <code style="background: white; padding: 10px; display: block; margin: 10px 0; font-family: monospace;">
                if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {<br>
                &nbsp;&nbsp;$_SERVER['HTTPS'] = 'on';<br>
                }
                </code>
            </div>
        </div>
        <?php
    }
}

// Initialize
add_action('init', function() {
    Spearhead_Helper_Page::get_instance();
});