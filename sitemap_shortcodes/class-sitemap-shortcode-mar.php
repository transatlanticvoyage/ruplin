<?php
/**
 * Sitemap Shortcode Mar Management Page
 * 
 * Provides examples and copy functionality for ruplin_sitemap_method_3 shortcode
 * 
 * @package Ruplin
 * @subpackage SitemapShortcodes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sitemap Shortcode Mar Management Page Class
 */
class Ruplin_Sitemap_Shortcode_Mar_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        
        // Aggressive notice suppression
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));
    }
    
    /**
     * Add submenu page to Ruplin Hub
     */
    public function add_admin_menu() {
        add_submenu_page(
            'snefuru',
            'Sitemap Shortcode Examples',
            'Sitemap_Shortcode_Mar',
            'manage_options',
            'sitemap_shortcode_mar',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'sitemap_shortcode_mar') {
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
        
        if (strpos($screen->id, 'sitemap_shortcode_mar') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'sitemap_shortcode_mar')) {
            
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
     * Suppress all admin notices - comprehensive version
     */
    private function suppress_all_admin_notices() {
        add_action('admin_print_styles', function() {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            
            global $wp_filter;
            if (isset($wp_filter['user_admin_notices'])) {
                unset($wp_filter['user_admin_notices']);
            }
        }, 0);
        
        add_action('admin_head', function() {
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
                
                .update-core-php, .notice-alt {
                    display: none !important;
                }
                
                .activated, .deactivated {
                    display: none !important;
                }
                
                .notice-warning, .notice-error {
                    display: none !important;
                }
                
                .wrap .notice:first-child,
                .wrap > div.notice,
                .wrap > div.updated,
                .wrap > div.error {
                    display: none !important;
                }
                
                [class*="notice"], [class*="updated"], [class*="error"],
                [id*="notice"], [id*="message"] {
                    display: none !important;
                }
                
                .wrap h1, .wrap .sitemap-shortcode-content {
                    display: block !important;
                }
            </style>';
        }, 1);
        
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        add_action('admin_notices', '__return_false', PHP_INT_MAX);
        add_action('all_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('network_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('user_admin_notices', '__return_false', PHP_INT_MAX);
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap sitemap-shortcode-content">
            <h1>🗺️ Sitemap Shortcode Examples</h1>
            <p style="font-size: 16px; color: #666; margin-bottom: 30px;">
                Reference examples for the <code>ruplin_sitemap_method_3</code> shortcode with all customization options.
            </p>
            
            <!-- Example Code Section -->
            <div class="example-section" style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px;">
                    📋 Complete Example with All Options
                </h2>
                <p style="color: #666; margin-bottom: 20px;">
                    This example demonstrates all available customization options for the sitemap shortcode:
                </p>
                
                <div style="position: relative; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; margin: 15px 0;">
                    <button onclick="copyToClipboard('full-example')" 
                            style="position: absolute; top: 10px; right: 10px; background: #0073aa; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                        📄 Copy
                    </button>
                    <pre id="full-example" style="margin: 0; font-family: monospace; font-size: 13px; line-height: 1.4; color: #333; white-space: pre-wrap; padding-right: 80px;">[ruplin_sitemap_method_3 
    service_anchor="post_title" 
    neighborhood_anchor="post_title" 
    city_anchor="city" 
    about_anchor="post_title" 
    contact_anchor="post_title" 
    custom_anchors='{"downtown-jersey-city":"Downtown","about":"Our Story","contact":"Get In Touch","plumbing":"Expert Plumbing","blog/latest-news":"Recent Updates","privacy-policy":"Privacy"}']</pre>
                </div>
            </div>
            
            <!-- Basic Usage Section -->
            <div class="example-section" style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
                    🚀 Basic Usage (Default Settings)
                </h2>
                <p style="color: #666; margin-bottom: 20px;">
                    Simple shortcode with default behavior - no customization needed:
                </p>
                
                <div style="position: relative; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; margin: 15px 0;">
                    <button onclick="copyToClipboard('basic-example')" 
                            style="position: absolute; top: 10px; right: 10px; background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                        📄 Copy
                    </button>
                    <pre id="basic-example" style="margin: 0; font-family: monospace; font-size: 13px; line-height: 1.4; color: #333; white-space: pre-wrap; padding-right: 80px;">[ruplin_sitemap_method_3]</pre>
                </div>
            </div>
            
            <!-- Option Reference Section -->
            <div class="example-section" style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #333; border-bottom: 2px solid #ff6b35; padding-bottom: 10px;">
                    ⚙️ Option Reference Guide
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                    
                    <!-- Service Pages Options -->
                    <div style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; background: #fafafa;">
                        <h3 style="margin-top: 0; color: #0073aa;">🔧 Service Pages</h3>
                        <ul style="margin: 0; padding-left: 20px; color: #666;">
                            <li><code>service_anchor="post_title"</code> - Use post titles instead of moniker</li>
                            <li><code>service_anchor="moniker"</code> - Use moniker field (default)</li>
                        </ul>
                    </div>
                    
                    <!-- Location Pages Options -->
                    <div style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; background: #fafafa;">
                        <h3 style="margin-top: 0; color: #28a745;">📍 Location Pages</h3>
                        <ul style="margin: 0; padding-left: 20px; color: #666;">
                            <li><code>neighborhood_anchor="post_title"</code> - Use post titles</li>
                            <li><code>city_anchor="city"</code> - City name only (no state)</li>
                            <li><code>city_anchor="city_state"</code> - "City, State" format (default)</li>
                        </ul>
                    </div>
                    
                    <!-- Standard Pages Options -->
                    <div style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; background: #fafafa;">
                        <h3 style="margin-top: 0; color: #ff6b35;">📄 Standard Pages</h3>
                        <ul style="margin: 0; padding-left: 20px; color: #666;">
                            <li><code>about_anchor="post_title"</code> - Use post titles</li>
                            <li><code>about_anchor="About Us"</code> - Default text</li>
                            <li><code>contact_anchor="post_title"</code> - Use post titles</li>
                            <li><code>contact_anchor="Contact Us"</code> - Default text</li>
                        </ul>
                    </div>
                    
                    <!-- Custom Anchors -->
                    <div style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; background: #fafafa; grid-column: 1 / -1;">
                        <h3 style="margin-top: 0; color: #dc3545;">🎯 Custom Anchors (JSON)</h3>
                        <p style="margin: 0 0 10px 0; color: #666;">Override specific pages with custom anchor text:</p>
                        <code style="background: #f1f1f1; padding: 8px; border-radius: 4px; display: block; font-size: 12px; margin-top: 10px; overflow-x: auto;">
                            custom_anchors='{"page-slug":"Custom Text","about":"Our Story","contact":"Get In Touch"}'
                        </code>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            // Create temporary textarea
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            
            // Select and copy
            textarea.select();
            document.execCommand('copy');
            
            // Remove temporary element
            document.body.removeChild(textarea);
            
            // Show feedback
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '✅ Copied!';
            button.style.background = '#28a745';
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.style.background = '#0073aa';
            }, 2000);
        }
        
        // Enhanced clipboard support for modern browsers
        if (navigator.clipboard) {
            window.copyToClipboard = function(elementId) {
                const element = document.getElementById(elementId);
                const text = element.textContent;
                
                navigator.clipboard.writeText(text).then(function() {
                    const button = event.target;
                    const originalText = button.innerHTML;
                    button.innerHTML = '✅ Copied!';
                    button.style.background = '#28a745';
                    
                    setTimeout(function() {
                        button.innerHTML = originalText;
                        button.style.background = button.classList.contains('btn-success') ? '#28a745' : '#0073aa';
                    }, 2000);
                }).catch(function() {
                    // Fallback to document.execCommand
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    
                    const button = event.target;
                    const originalText = button.innerHTML;
                    button.innerHTML = '✅ Copied!';
                    button.style.background = '#28a745';
                    
                    setTimeout(function() {
                        button.innerHTML = originalText;
                        button.style.background = '#0073aa';
                    }, 2000);
                });
            };
        }
        </script>
        
        <style>
        .example-section {
            transition: box-shadow 0.3s ease;
        }
        
        .example-section:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15) !important;
        }
        
        .example-section h2 {
            font-size: 18px;
            font-weight: 600;
        }
        
        .example-section h3 {
            font-size: 14px;
            font-weight: 600;
        }
        
        .example-section pre {
            border-left: 4px solid #0073aa;
            background: #f8f9fa !important;
        }
        
        .example-section button {
            transition: all 0.2s ease;
        }
        
        .example-section button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        code {
            background: #f1f1f1;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }
        </style>
        <?php
    }
}

// Initialize the class
new Ruplin_Sitemap_Shortcode_Mar_Page();