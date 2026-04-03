<?php
/**
 * Silkweaver Robust Locations Child Area Settings Admin Page
 *
 * URL: /wp-admin/admin.php?page=silkweaver_robust_locations_child_area_settings
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Silkweaver_Robust_Locations_Child_Area_Settings_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 23);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_2_mar',
            'Silkweaver Robust Locations Child Area Settings',
            'silkweaver robust locations child area settings',
            'manage_options',
            'silkweaver_robust_locations_child_area_settings',
            array($this, 'render_admin_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings') {
            return;
        }
        // Assets can be enqueued here in future
    }

    public function render_admin_page() {
        $this->suppress_admin_notices();
        ?>
        <div class="wrap srlca-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        </div>
        <?php
    }

    private function suppress_admin_notices() {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');

        add_action('admin_head', function() {
            ?>
            <style>
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice-error,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice-warning,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice-success,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice-info,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .error,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .updated,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .update-nag,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings #message {
                    display: none !important;
                }
            </style>
            <?php
        }, 999);

        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.notice, .error, .updated, .update-nag').not('.silkweaver-robust-locations-settings-notice').remove();
            });
            </script>
            <?php
        }, 999);
    }
}

Ruplin_Silkweaver_Robust_Locations_Child_Area_Settings_Admin::get_instance();
