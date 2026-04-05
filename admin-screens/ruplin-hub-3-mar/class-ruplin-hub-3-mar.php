<?php
/**
 * Ruplin Hub 3 MAR Admin Page
 *
 * Top-level admin page for Ruplin Hub 3
 * URL: /wp-admin/admin.php?page=ruplin_hub_3_mar
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Hub_3_Mar {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Priority 14 — same as Hub 2, ensures parent exists before child pages register
        add_action('admin_menu', array($this, 'add_admin_menu'), 14);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Ruplin Hub 3',       // Page title
            'Ruplin Hub 3',       // Menu title
            'manage_options',     // Capability
            'ruplin_hub_3_mar',   // Menu slug
            array($this, 'render_admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'),
            3.66  // Position — right after Ruplin Hub 2 (3.65)
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        ?>
        <div class="wrap">
            <h1>Ruplin Hub 3</h1>
            <p style="color:#646970;">Select a tool from the menu.</p>
        </div>
        <?php
    }
}

Ruplin_Hub_3_Mar::get_instance();
