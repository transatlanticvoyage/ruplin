<?php
/**
 * Set Default Sitewide Header And Footer Admin Page
 *
 * Child page of Ruplin Hub 3.
 * URL: /wp-admin/admin.php?page=set_default_sitewide_header_and_footer
 *
 * Page is intentionally blank for now — feature content to be added later.
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Set_Default_Sitewide_Header_And_Footer {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Priority 35 keeps this as the LAST child under Ruplin Hub 3
        // (parent registers at 14, warbler at 15, silkweaver at 25,
        //  blog-post-fixer at 30).
        add_action('admin_menu', array($this, 'add_admin_menu'), 35);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_3_mar',                            // Parent slug — appears under Ruplin Hub 3
            'Set Default Sitewide Header And Footer',      // Page title
            'Set Default Sitewide Header And Footer',      // Menu title
            'manage_options',
            'set_default_sitewide_header_and_footer',      // Menu slug
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page() {
        $this->suppress_admin_notices();
        ?>
        <div class="wrap set-default-sitewide-header-and-footer-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <!-- Intentionally blank — page content to be added later. -->
        </div>
        <?php
    }

    /**
     * Aggressive WP admin notice / message / warning suppression,
     * matching the pattern used on our other Ruplin admin pages.
     */
    private function suppress_admin_notices() {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');

        add_action('admin_head', function() {
            ?>
            <style>
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer .notice,
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer .notice-error,
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer .notice-warning,
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer .notice-success,
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer .notice-info,
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer .error,
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer .updated,
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer .update-nag,
                body.ruplin-hub-3_page_set_default_sitewide_header_and_footer #message {
                    display: none !important;
                }
            </style>
            <?php
        }, 999);

        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.notice, .error, .updated, .update-nag').remove();
            });
            </script>
            <?php
        }, 999);
    }
}

Ruplin_Set_Default_Sitewide_Header_And_Footer::get_instance();
