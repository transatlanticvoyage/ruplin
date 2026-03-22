<?php
/**
 * Notice Suppressor Class
 * 
 * Aggressively suppresses all admin notices on this page
 * 
 * @package Ruplin
 * @subpackage FundamentalImageSetter
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Fundamental_Image_Setter_Notice_Suppressor {
    
    /**
     * Suppress all notices
     */
    public function suppress_all_notices() {
        // Remove all admin notices
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('user_admin_notices');
        remove_all_actions('network_admin_notices');
        
        // Add CSS to hide any notices that might slip through
        add_action('admin_head', array($this, 'hide_notices_css'));
        
        // Add JavaScript to remove notices from DOM
        add_action('admin_footer', array($this, 'remove_notices_js'));
        
        // Hook into admin_notices with highest priority to clear any that get added
        add_action('admin_notices', array($this, 'clear_all_notices'), -9999);
        add_action('all_admin_notices', array($this, 'clear_all_notices'), -9999);
        
        // Disable query monitor output if active
        add_filter('qm/dispatch/html', '__return_false');
        
        // Disable debug bar if active
        add_filter('debug_bar_enable', '__return_false');
    }
    
    /**
     * CSS to hide notices
     */
    public function hide_notices_css() {
        ?>
        <style type="text/css">
            /* Hide all types of WordPress admin notices */
            .notice,
            .notice-error,
            .notice-warning,
            .notice-success,
            .notice-info,
            .updated,
            .error,
            .update-nag,
            .update-message,
            #message,
            .wrap > .notice,
            .wrap > .error,
            .wrap > .updated,
            .wrap > .update-nag,
            body.ruplin_page_fundamental_image_setter .notice,
            body.ruplin_page_fundamental_image_setter .error,
            body.ruplin_page_fundamental_image_setter .updated,
            body.ruplin_page_fundamental_image_setter .update-nag,
            body.ruplin_page_fundamental_image_setter #message,
            body.toplevel_page_fundamental_image_setter .notice,
            body.toplevel_page_fundamental_image_setter .error,
            body.toplevel_page_fundamental_image_setter .updated,
            body.toplevel_page_fundamental_image_setter .update-nag,
            body.toplevel_page_fundamental_image_setter #message {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                position: absolute !important;
                left: -9999px !important;
            }
            
            /* Also hide any plugin-specific notices */
            [class*="notice"],
            [class*="error"],
            [class*="warning"],
            [class*="updated"],
            [class*="message"],
            [id*="notice"],
            [id*="error"],
            [id*="warning"],
            [id*="message"],
            div[role="alert"] {
                display: none !important;
            }
            
            /* Hide WooCommerce specific notices */
            .woocommerce-message,
            .woocommerce-error,
            .woocommerce-info,
            .wc-connect-notice {
                display: none !important;
            }
            
            /* Hide Yoast SEO notices */
            .yoast-notification,
            .yoast-container,
            .yoast-alert {
                display: none !important;
            }
            
            /* Hide Jetpack notices */
            .jetpack-message,
            .jp-connection-banner {
                display: none !important;
            }
            
            /* Hide Query Monitor */
            #qm {
                display: none !important;
            }
            
            /* Hide Debug Bar */
            #debug-bar-php,
            #querylist {
                display: none !important;
            }
        </style>
        <?php
    }
    
    /**
     * JavaScript to remove notices from DOM
     */
    public function remove_notices_js() {
        ?>
        <script type="text/javascript">
        (function() {
            // Function to remove all notices
            function removeAllNotices() {
                // Select all notice elements
                var selectors = [
                    '.notice',
                    '.notice-error',
                    '.notice-warning', 
                    '.notice-success',
                    '.notice-info',
                    '.updated',
                    '.error',
                    '.update-nag',
                    '.update-message',
                    '#message',
                    '[class*="notice"]',
                    '[class*="error"]',
                    '[class*="warning"]',
                    '[id*="notice"]',
                    '[id*="error"]',
                    'div[role="alert"]',
                    '.woocommerce-message',
                    '.woocommerce-error',
                    '.yoast-notification',
                    '.jetpack-message'
                ];
                
                selectors.forEach(function(selector) {
                    var elements = document.querySelectorAll(selector);
                    elements.forEach(function(el) {
                        el.remove();
                    });
                });
            }
            
            // Remove notices on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', removeAllNotices);
            } else {
                removeAllNotices();
            }
            
            // Remove notices after a delay (for dynamically added notices)
            setTimeout(removeAllNotices, 100);
            setTimeout(removeAllNotices, 500);
            setTimeout(removeAllNotices, 1000);
            
            // Use MutationObserver to catch any dynamically added notices
            if (window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    removeAllNotices();
                });
                
                // Start observing when ready
                if (document.body) {
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                }
            }
            
            // jQuery fallback if available
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ready(function($) {
                    removeAllNotices();
                    
                    // Remove notices after AJAX calls
                    $(document).ajaxComplete(function() {
                        setTimeout(removeAllNotices, 100);
                    });
                });
            }
        })();
        </script>
        <?php
    }
    
    /**
     * Clear all notices (callback for hooks)
     */
    public function clear_all_notices() {
        // This will be called but won't output anything
        // It effectively blocks other notice outputs
        ob_start();
        ob_end_clean();
    }
}