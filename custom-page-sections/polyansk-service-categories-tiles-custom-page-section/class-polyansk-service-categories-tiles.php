<?php
/**
 * Polyansk Service Categories Tiles Custom Page Section
 *
 * Renders a full-width tile grid of service categories with breadcrumb
 * links to published service pages that belong to each category.
 *
 * Triggered by wp_pylons.show_polyansk_custom_page_section = true
 */

if (!defined('ABSPATH')) {
    exit;
}

class Polyansk_Service_Categories_Tiles {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue front-end CSS only when needed
     */
    public function enqueue_assets() {
        if (!is_singular()) {
            return;
        }

        global $post;
        if (!$post) {
            return;
        }

        global $wpdb;
        $show = $wpdb->get_var($wpdb->prepare(
            "SELECT show_polyansk_custom_page_section FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d LIMIT 1",
            $post->ID
        ));

        if ($show) {
            wp_enqueue_style(
                'polyansk-service-categories-tiles',
                plugin_dir_url(__FILE__) . 'assets/css/polyansk-tiles.css',
                array(),
                '1.0.0'
            );
        }
    }

    /**
     * Render the tiles section.
     *
     * Call this from your theme template where the section should appear.
     * Returns empty string if the current pylon does not have the flag set.
     */
    public function render($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        if (!$post_id) {
            return '';
        }

        global $wpdb;
        $prefix = $wpdb->prefix;

        // Check if this page should show the polyansk section
        $show = $wpdb->get_var($wpdb->prepare(
            "SELECT show_polyansk_custom_page_section FROM {$prefix}pylons WHERE rel_wp_post_id = %d LIMIT 1",
            $post_id
        ));

        if (!$show) {
            return '';
        }

        // Get all service categories
        $categories = $wpdb->get_results(
            "SELECT category_id, category_name, category_description
             FROM {$prefix}service_categories
             ORDER BY category_id ASC"
        );

        if (empty($categories)) {
            return '';
        }

        // For each category, get published service pages via wp_pylons
        foreach ($categories as $cat) {
            $cat->service_pages = $wpdb->get_results($wpdb->prepare(
                "SELECT pyl.moniker, p.ID as post_id, p.post_name
                 FROM {$prefix}pylons pyl
                 INNER JOIN {$wpdb->posts} p ON pyl.rel_wp_post_id = p.ID
                 WHERE pyl.rel_service_category_id = %d
                   AND p.post_status = 'publish'
                   AND pyl.rel_wp_post_id IS NOT NULL
                   AND pyl.moniker IS NOT NULL
                   AND pyl.moniker != ''
                 ORDER BY pyl.moniker ASC",
                $cat->category_id
            ));
        }

        // Load the template
        ob_start();
        $template_path = plugin_dir_path(__FILE__) . 'templates/tiles-template.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
        return ob_get_clean();
    }
}

Polyansk_Service_Categories_Tiles::get_instance();
