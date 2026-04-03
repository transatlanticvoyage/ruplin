<?php
/**
 * Cherry Page Section Controller Grid Admin Page
 *
 * Handles cherry page section controller interface in WordPress admin
 * URL: /wp-admin/admin.php?page=cherry_page_section_controller_grid
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Cherry_Page_Section_Controller_Grid {

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
        add_action('wp_ajax_cherry_controller_grid_get_data', array($this, 'ajax_get_data'));
        add_action('wp_ajax_cherry_controller_grid_save_changes', array($this, 'ajax_save_changes'));
    }

    /**
     * Add menu item to WordPress admin
     */
    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_2_mar',  // Parent slug (Ruplin Hub 2)
            'Cherry Page Section Controller Grid',  // Page title
            'Cherry Page Section Controller Grid',  // Menu title
            'manage_options',  // Capability
            'cherry_page_section_controller_grid',  // Menu slug
            array($this, 'render_admin_page')  // Callback
        );
    }

    /**
     * Early notice suppression
     */
    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'cherry_page_section_controller_grid') {
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

        if (strpos($screen->id, 'cherry_page_section_controller_grid') !== false ||
            (isset($_GET['page']) && $_GET['page'] === 'cherry_page_section_controller_grid')) {

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
        <div class="wrap cherry-controller-grid">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="cherry-controller-grid-container">
                <div class="table-description">
                    wp_posts items where wp_pylons.pylon_archetype = "servicepage"
                </div>

                <div class="table-actions">
                    <button id="save-changes" class="button button-primary" disabled>Save Changes</button>
                    <span id="save-status" style="margin-left: 10px;"></span>
                </div>

                <!-- Rocket Chamber Div - Contains the pagination controls and search -->
                <div class="rocket_chamber_div" style="border: 1px solid black; padding: 0; margin: 20px 0; position: relative;">
                    <div style="position: absolute; top: 4px; left: 4px; font-size: 16px; font-weight: bold; display: flex; align-items: center; gap: 6px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="black" style="transform: rotate(15deg);">
                            <ellipse cx="12" cy="8" rx="3" ry="6" fill="black"/>
                            <path d="M12 2 L15 8 L9 8 Z" fill="black"/>
                            <path d="M9 12 L7 14 L9 16 Z" fill="black"/>
                            <path d="M15 12 L17 14 L15 16 Z" fill="black"/>
                            <path d="M10 14 L9 18 L10.5 16 L12 20 L13.5 16 L15 18 L14 14 Z" fill="black"/>
                            <circle cx="12" cy="6" r="1" fill="white"/>
                        </svg>
                        rocket_chamber
                    </div>
                    <div style="margin-top: 24px; padding-top: 4px; padding-bottom: 0; padding-left: 8px; padding-right: 8px;">
                        <div style="display: flex; align-items: end; justify-content: space-between;">
                            <div style="display: flex; align-items: end; gap: 32px;">
                                <table style="border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td style="border: 1px solid black; padding: 4px; text-align: center;">
                                                <div style="font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                                    <span style="font-weight: bold;">row pagination</span>
                                                    <span style="font-size: 14px; font-weight: normal;">
                                                        Showing <span style="font-weight: bold;" id="ccg-services-showing">0</span> of <span style="font-weight: bold;" id="ccg-services-total">0</span> services
                                                    </span>
                                                </div>
                                            </td>
                                            <td style="border: 1px solid black; padding: 4px; text-align: center;">
                                                <div style="font-size: 16px; font-weight: bold;">
                                                    search box 2
                                                </div>
                                            </td>
                                            <td style="border: 1px solid black; padding: 4px; text-align: center;">
                                                <div style="font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                                    <span style="font-weight: bold;">column pagination</span>
                                                    <span style="font-size: 14px; font-weight: normal;">
                                                        Showing <span style="font-weight: bold;" id="ccg-columns-showing">0</span> columns of <span style="font-weight: bold;" id="ccg-columns-total">0</span> total columns
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid black; padding: 4px;">
                                                <div style="display: flex; align-items: end; gap: 16px;">
                                                    <!-- Row Pagination Bar 1: Items per page selector -->
                                                    <div style="display: flex; align-items: center;">
                                                        <span style="font-size: 12px; color: #4B5563; margin-right: 8px;">Rows/page:</span>
                                                        <div style="display: inline-flex; border-radius: 6px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                                            <button type="button" data-rows="3" class="ccg-rows-per-page-btn" style="padding: 10px 8px; font-size: 14px; border: 1px solid #D1D5DB; border-radius: 6px 0 0 6px; margin-right: -1px; cursor: pointer; background: white;">3</button>
                                                            <button type="button" data-rows="4" class="ccg-rows-per-page-btn" style="padding: 10px 8px; font-size: 14px; border: 1px solid #D1D5DB; margin-right: -1px; cursor: pointer; background: white;">4</button>
                                                            <button type="button" data-rows="5" class="ccg-rows-per-page-btn" style="padding: 10px 8px; font-size: 14px; border: 1px solid #D1D5DB; margin-right: -1px; cursor: pointer; background: white;">5</button>
                                                            <button type="button" data-rows="10" class="ccg-rows-per-page-btn active" style="padding: 10px 8px; font-size: 14px; border: 1px solid #3B82F6; background: #3B82F6; color: white; margin-right: -1px; cursor: pointer;">10</button>
                                                            <button type="button" data-rows="25" class="ccg-rows-per-page-btn" style="padding: 10px 8px; font-size: 14px; border: 1px solid #D1D5DB; margin-right: -1px; cursor: pointer; background: white;">25</button>
                                                            <button type="button" data-rows="50" class="ccg-rows-per-page-btn" style="padding: 10px 8px; font-size: 14px; border: 1px solid #D1D5DB; margin-right: -1px; cursor: pointer; background: white;">50</button>
                                                            <button type="button" data-rows="100" class="ccg-rows-per-page-btn" style="padding: 10px 8px; font-size: 14px; border: 1px solid #D1D5DB; margin-right: -1px; cursor: pointer; background: white;">100</button>
                                                            <button type="button" data-rows="200" class="ccg-rows-per-page-btn" style="padding: 10px 8px; font-size: 14px; border: 1px solid #D1D5DB; margin-right: -1px; cursor: pointer; background: white;">200</button>
                                                            <button type="button" data-rows="all" class="ccg-rows-per-page-btn" style="padding: 10px 8px; font-size: 14px; border: 1px solid #D1D5DB; border-radius: 0 6px 6px 0; cursor: pointer; background: white;">All</button>
                                                        </div>
                                                    </div>
                                                    <!-- Row Pagination Bar 2: Page navigation -->
                                                    <div style="display: flex; align-items: center;">
                                                        <span style="font-size: 12px; color: #4B5563; margin-right: 8px;">Row page:</span>
                                                        <nav style="display: inline-flex; border-radius: 6px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                                            <button type="button" id="ccg-first-row-page" style="position: relative; display: inline-flex; align-items: center; border-radius: 6px 0 0 0; padding: 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; color: #9CA3AF; border: 1px solid #D1D5DB; cursor: pointer; background: white;">≪</button>
                                                            <button type="button" id="ccg-prev-row-page" style="position: relative; display: inline-flex; align-items: center; padding: 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; color: #9CA3AF; border: 1px solid #D1D5DB; margin-left: -1px; cursor: pointer; background: white;">
                                                                <svg style="width: 16px; height: 16px; color: #6B7280;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                    <path d="M1 4v6h6" />
                                                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                                                                </svg>
                                                            </button>
                                                            <span style="position: relative; display: inline-flex; align-items: center; padding: 8px 12px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #D1D5DB; margin-left: -1px; background: white; font-weight: bold;"><span id="ccg-current-row-page">1</span> of <span id="ccg-total-row-pages">1</span></span>
                                                            <button type="button" id="ccg-next-row-page" style="position: relative; display: inline-flex; align-items: center; padding: 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; color: #9CA3AF; border: 1px solid #D1D5DB; margin-left: -1px; cursor: pointer; background: white;">
                                                                <svg style="width: 16px; height: 16px; color: #6B7280;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                    <path d="M23 4v6h-6" />
                                                                    <path d="M20.49 15a9 9 0 1 1-2.13-9.36L23 10" />
                                                                </svg>
                                                            </button>
                                                            <button type="button" id="ccg-last-row-page" style="position: relative; display: inline-flex; align-items: center; border-radius: 0 6px 6px 0; padding: 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; color: #9CA3AF; border: 1px solid #D1D5DB; margin-left: -1px; cursor: pointer; background: white;">≫</button>
                                                        </nav>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="border: 1px solid black; padding: 4px;">
                                                <div style="display: flex; align-items: end;">
                                                    <input type="text" id="ccg-search" placeholder="Search..." style="width: 200px; margin-bottom: 3px; padding: 8px 12px; border: 1px solid #D1D5DB; border-radius: 4px; font-size: 14px; background: white; outline: none; transition: all 0.15s ease;" onFocus="this.style.outline='none'; this.style.borderColor='#3B82F6'; this.style.boxShadow='0 0 0 2px rgba(59, 130, 246, 0.1)'" onBlur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none'">
                                                </div>
                                            </td>
                                            <td style="border: 1px solid black; padding: 4px;">
                                                <div style="display: flex; align-items: end; gap: 16px;">
                                                    <!-- Column Pagination Bar 1: Columns per page quantity selector -->
                                                    <div style="display: flex; align-items: center;">
                                                        <span style="font-size: 12px; color: #4B5563; margin-right: 8px;">Cols/page:</span>
                                                        <button type="button" data-cols="4" class="ccg-cols-per-page-btn active" style="padding: 10px 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; border-radius: 4px 0 0 4px; margin-right: -1px; cursor: pointer; background: #f8f782; color: black;">4</button>
                                                        <button type="button" data-cols="5" class="ccg-cols-per-page-btn" style="padding: 10px 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; margin-right: -1px; cursor: pointer; background: white;">5</button>
                                                        <button type="button" data-cols="6" class="ccg-cols-per-page-btn" style="padding: 10px 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; margin-right: -1px; cursor: pointer; background: white;">6</button>
                                                        <button type="button" data-cols="8" class="ccg-cols-per-page-btn" style="padding: 10px 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; margin-right: -1px; cursor: pointer; background: white;">8</button>
                                                        <button type="button" data-cols="11" class="ccg-cols-per-page-btn" style="padding: 10px 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; margin-right: -1px; cursor: pointer; background: white;">11</button>
                                                        <button type="button" data-cols="15" class="ccg-cols-per-page-btn" style="padding: 10px 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; margin-right: -1px; cursor: pointer; background: white;">15</button>
                                                        <button type="button" data-cols="all" class="ccg-cols-per-page-btn" style="padding: 10px 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; border-radius: 0 4px 4px 0; cursor: pointer; background: white;">All</button>
                                                    </div>
                                                    <!-- Column Pagination Bar 2: Current column page selector -->
                                                    <div style="display: flex; align-items: center;">
                                                        <span style="font-size: 12px; color: #4B5563; margin-right: 8px;">Col page:</span>
                                                        <button type="button" id="ccg-first-col-page" style="padding: 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; border-radius: 4px 0 0 4px; margin-right: -1px; cursor: pointer; background: white;">≪</button>
                                                        <button type="button" id="ccg-prev-col-page" style="padding: 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; margin-right: -1px; cursor: pointer; background: white;">
                                                            <svg style="width: 16px; height: 16px; color: #6B7280;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                <path d="M1 4v6h6" />
                                                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                                                            </svg>
                                                        </button>
                                                        <span style="padding: 8px 12px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; margin-right: -1px; background: white; font-weight: bold; display: inline-flex; align-items: center;"><span id="ccg-current-col-page">1</span> of <span id="ccg-total-col-pages">1</span></span>
                                                        <button type="button" id="ccg-next-col-page" style="padding: 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; margin-right: -1px; cursor: pointer; background: white;">
                                                            <svg style="width: 16px; height: 16px; color: #6B7280;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                <path d="M23 4v6h-6" />
                                                                <path d="M20.49 15a9 9 0 1 1-2.13-9.36L23 10" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" id="ccg-last-col-page" style="padding: 8px; font-size: 14px; padding-top: 10px; padding-bottom: 10px; border: 1px solid #000; border-radius: 0 4px 4px 0; cursor: pointer; background: white;">≫</button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table id="cherry-controller-table" class="cherry-controller-table">
                        <thead>
                            <tr>
                                <th>tools</th>
                                <th>post_id</th>
                                <th>post_title</th>
                                <th>post_name</th>
                                <th>post_status</th>
                                <th>pylon_id</th>
                                <th>rel_wp_post_id</th>
                                <th>pylon_archetype</th>
                                <th>moniker</th>
                                <th>service_category</th>
                                <th>rel_service_category_id</th>
                                <th>category_id</th>
                                <th class="ccg-separator-right">category_name</th>
                                <th>batman_hero_box_hide</th>
                                <th>avg_rating_box_hide</th>
                                <th>derek_blog_post_meta_box_hide</th>
                                <th>chen_cards_box_hide</th>
                                <th>polyansk_tiles_box_hide</th>
                                <th>kristina_cta_box_instance_1_hide</th>
                                <th>content_bay_1_box_hide</th>
                                <th>content_bay_2_box_hide</th>
                                <th>content_lake_box_hide</th>
                                <th>content_sea_box_hide</th>
                                <th>osb_box_hide</th>
                                <th>reviews_box_hide</th>
                                <th>serena_faq_box_hide</th>
                                <th>nile_map_box_hide</th>
                                <th>kristina_cta_box_instance_2_hide</th>
                                <th>victoria_blog_box_hide</th>
                                <th>ocean_1_box_hide</th>
                                <th>ocean_2_box_hide</th>
                                <th>ocean_3_box_hide</th>
                                <th>brook_video_box_hide</th>
                                <th>olivia_auth_links_box_hide</th>
                                <th>ava_why_choose_us_box_hide</th>
                                <th>kendall_our_process_box_hide</th>
                                <th>sara_custom_html_box_hide</th>
                                <th>liz_pricing_box_hide</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $this->render_table_rows(); ?>
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
        if ($hook !== 'ruplin-hub-2_page_cherry_page_section_controller_grid') {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'cherry-controller-grid',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'cherry-controller-grid',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script for AJAX
        wp_localize_script('cherry-controller-grid', 'cherry_controller_grid_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cherry_controller_grid_nonce')
        ));
    }

    /**
     * Aggressive admin notice suppression
     */
    private function suppress_all_admin_notices() {
        // Remove all admin notices on this page
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');

        // Add custom CSS to hide any notices that might slip through
        add_action('admin_head', function() {
            ?>
            <style>
                /* Ultra-aggressive notice suppression for Cherry Page Section Controller Grid */
                body[class*="cherry_page_section_controller_grid"] .notice,
                body[class*="cherry_page_section_controller_grid"] .notice-error,
                body[class*="cherry_page_section_controller_grid"] .notice-warning,
                body[class*="cherry_page_section_controller_grid"] .notice-success,
                body[class*="cherry_page_section_controller_grid"] .notice-info,
                body[class*="cherry_page_section_controller_grid"] .error,
                body[class*="cherry_page_section_controller_grid"] .updated,
                body[class*="cherry_page_section_controller_grid"] .update-nag,
                body[class*="cherry_page_section_controller_grid"] .wp-pointer,
                body[class*="cherry_page_section_controller_grid"] #message,
                body[class*="cherry_page_section_controller_grid"] .jetpack-jitm-message,
                body[class*="cherry_page_section_controller_grid"] .woocommerce-message,
                body[class*="cherry_page_section_controller_grid"] .woocommerce-error,
                body[class*="cherry_page_section_controller_grid"] div.fs-notice,
                body[class*="cherry_page_section_controller_grid"] .monsterinsights-notice,
                body[class*="cherry_page_section_controller_grid"] .yoast-notification,
                body[class*="cherry_page_section_controller_grid"] .notice-alt,
                body[class*="cherry_page_section_controller_grid"] .update-php,
                body[class*="cherry_page_section_controller_grid"] .php-update-nag,
                body[class*="cherry_page_section_controller_grid"] .plugin-update-tr,
                body[class*="cherry_page_section_controller_grid"] .theme-update-message,
                body[class*="cherry_page_section_controller_grid"] .update-message,
                body[class*="cherry_page_section_controller_grid"] .updating-message,
                body[class*="cherry_page_section_controller_grid"] #update-nag,
                body[class*="cherry_page_section_controller_grid"] #deprecation-warning,
                body[class*="cherry_page_section_controller_grid"] .activated,
                body[class*="cherry_page_section_controller_grid"] .deactivated,
                body[class*="cherry_page_section_controller_grid"] [class*="notice"],
                body[class*="cherry_page_section_controller_grid"] [class*="updated"],
                body[class*="cherry_page_section_controller_grid"] [class*="error"],
                body[class*="cherry_page_section_controller_grid"] [id*="notice"],
                body[class*="cherry_page_section_controller_grid"] [id*="message"] {
                    display: none !important;
                }

                /* Keep our own notices visible if needed */
                body[class*="cherry_page_section_controller_grid"] .cherry-controller-grid-notice {
                    display: block !important;
                }

                /* Ensure our content is visible */
                body[class*="cherry_page_section_controller_grid"] .wrap h1,
                body[class*="cherry_page_section_controller_grid"] .cherry-controller-grid-content,
                body[class*="cherry_page_section_controller_grid"] .cherry-controller-grid-container {
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
                    $('.notice, .error, .updated, .update-nag').not('.cherry-controller-grid-notice').remove();

                    // Monitor for dynamically added notices
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            $(mutation.addedNodes).each(function() {
                                if ($(this).hasClass('notice') ||
                                    $(this).hasClass('error') ||
                                    $(this).hasClass('updated') ||
                                    $(this).hasClass('update-nag')) {
                                    if (!$(this).hasClass('cherry-controller-grid-notice')) {
                                        $(this).remove();
                                    }
                                }
                            });
                        });
                    });

                    // Start observing
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
     * Get service page data from database
     */
    private function get_service_page_data() {
        global $wpdb;

        $pylons_table = $wpdb->prefix . 'pylons';

        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$pylons_table'");
        if (!$table_exists) {
            return array();
        }

        $query = "
            SELECT
                p.ID as post_id,
                p.post_title,
                p.post_name,
                p.post_status,
                pyl.pylon_id as pylon_id,
                pyl.rel_wp_post_id,
                pyl.pylon_archetype,
                pyl.moniker,
                pyl.service_category,
                pyl.rel_service_category_id,
                sc.category_id,
                sc.category_name,
                pyl.batman_hero_box_hide,
                pyl.avg_rating_box_hide,
                pyl.derek_blog_post_meta_box_hide,
                pyl.chen_cards_box_hide,
                pyl.polyansk_tiles_box_hide,
                pyl.kristina_cta_box_instance_1_hide,
                pyl.content_bay_1_box_hide,
                pyl.content_bay_2_box_hide,
                pyl.content_lake_box_hide,
                pyl.content_sea_box_hide,
                pyl.osb_box_hide,
                pyl.reviews_box_hide,
                pyl.serena_faq_box_hide,
                pyl.nile_map_box_hide,
                pyl.kristina_cta_box_instance_2_hide,
                pyl.victoria_blog_box_hide,
                pyl.ocean_1_box_hide,
                pyl.ocean_2_box_hide,
                pyl.ocean_3_box_hide,
                pyl.brook_video_box_hide,
                pyl.olivia_auth_links_box_hide,
                pyl.ava_why_choose_us_box_hide,
                pyl.kendall_our_process_box_hide,
                pyl.sara_custom_html_box_hide,
                pyl.liz_pricing_box_hide
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}pylons pyl ON p.ID = pyl.rel_wp_post_id
            LEFT JOIN {$wpdb->prefix}service_categories sc ON pyl.rel_service_category_id = sc.category_id
            WHERE pyl.pylon_archetype = 'servicepage'
            ORDER BY p.post_title ASC
        ";

        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    /**
     * Render table rows
     */
    private function render_table_rows() {
        $data = $this->get_service_page_data();
        $output = '';

        $box_hide_fields = array(
            'batman_hero_box_hide',
            'avg_rating_box_hide',
            'derek_blog_post_meta_box_hide',
            'chen_cards_box_hide',
            'polyansk_tiles_box_hide',
            'kristina_cta_box_instance_1_hide',
            'content_bay_1_box_hide',
            'content_bay_2_box_hide',
            'content_lake_box_hide',
            'content_sea_box_hide',
            'osb_box_hide',
            'reviews_box_hide',
            'serena_faq_box_hide',
            'nile_map_box_hide',
            'kristina_cta_box_instance_2_hide',
            'victoria_blog_box_hide',
            'ocean_1_box_hide',
            'ocean_2_box_hide',
            'ocean_3_box_hide',
            'brook_video_box_hide',
            'olivia_auth_links_box_hide',
            'ava_why_choose_us_box_hide',
            'kendall_our_process_box_hide',
            'sara_custom_html_box_hide',
            'liz_pricing_box_hide',
        );

        if (empty($data)) {
            $output = '<tr><td colspan="38" class="no-data">No posts found with pylon_archetype = "servicepage"</td></tr>';
        } else {
            foreach ($data as $row) {
                $output .= '<tr data-pylon-id="' . esc_attr($row['pylon_id']) . '">';
                $pid = esc_attr($row['post_id']);
                $output .= '<td class="ccg-tools-cell">';
                $output .= '<a href="' . admin_url('post.php?post=' . $pid . '&action=edit') . '" class="ccg-tool-btn" target="_blank">pendulum</a>';
                $output .= '<a href="' . admin_url('admin.php?page=cashew_editor&post_id=' . $pid) . '" class="ccg-tool-btn" target="_blank">cashew</a>';
                $output .= '<a href="' . admin_url('admin.php?page=telescope_content_editor&post=' . $pid) . '" class="ccg-tool-btn" target="_blank">telescope</a>';
                $output .= '<a href="' . esc_url(get_permalink($row['post_id'])) . '" class="ccg-tool-btn" target="_blank">front end</a>';
                $output .= '</td>';
                $output .= '<td>' . esc_html($row['post_id']) . '</td>';
                $output .= '<td>' . esc_html($row['post_title']) . '</td>';
                $output .= '<td>' . esc_html($row['post_name']) . '</td>';
                $output .= '<td>' . esc_html($row['post_status']) . '</td>';
                $output .= '<td>' . esc_html($row['pylon_id']) . '</td>';
                $output .= '<td>' . esc_html($row['rel_wp_post_id']) . '</td>';
                $output .= '<td>' . esc_html($row['pylon_archetype']) . '</td>';
                $output .= '<td><input type="text" class="editable-field" data-field="moniker" data-original="' . esc_attr($row['moniker'] ?? '') . '" value="' . esc_attr($row['moniker'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="editable-field" data-field="service_category" data-original="' . esc_attr($row['service_category'] ?? '') . '" value="' . esc_attr($row['service_category'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="editable-field" data-field="rel_service_category_id" data-original="' . esc_attr($row['rel_service_category_id'] ?? '') . '" value="' . esc_attr($row['rel_service_category_id'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td>' . esc_html($row['category_id'] ?? '') . '</td>';
                $output .= '<td class="ccg-separator-right">' . esc_html($row['category_name'] ?? '') . '</td>';

                // Toggle switches for box_hide boolean fields
                foreach ($box_hide_fields as $field) {
                    $val = intval($row[$field] ?? 0);
                    $checked = $val ? ' checked' : '';
                    $output .= '<td class="ccg-toggle-cell">';
                    $output .= '<label class="ccg-toggle">';
                    $output .= '<input type="checkbox" class="ccg-toggle-input editable-field" data-field="' . esc_attr($field) . '" data-original="' . $val . '"' . $checked . '>';
                    $output .= '<span class="ccg-toggle-slider"></span>';
                    $output .= '</label>';
                    $output .= '</td>';
                }

                $output .= '</tr>';
            }
        }

        return $output;
    }

    /**
     * AJAX handler to get data
     */
    public function ajax_get_data() {
        check_ajax_referer('cherry_controller_grid_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $data = $this->get_service_page_data();

        wp_send_json_success(array(
            'data' => $data,
            'count' => count($data)
        ));
    }

    /**
     * AJAX handler to save changes
     */
    public function ajax_save_changes() {
        check_ajax_referer('cherry_controller_grid_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;
        $pylons_table = $wpdb->prefix . 'pylons';

        $changes = isset($_POST['changes']) ? $_POST['changes'] : array();

        if (empty($changes)) {
            wp_send_json_error('No changes to save');
            return;
        }

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        foreach ($changes as $change) {
            $pylon_id = intval($change['pylon_id']);
            $field = sanitize_text_field($change['field']);
            $value = $change['value'];

            // Allowed boolean (box_hide) fields
            $box_hide_fields = array(
                'batman_hero_box_hide', 'avg_rating_box_hide', 'derek_blog_post_meta_box_hide',
                'chen_cards_box_hide', 'polyansk_tiles_box_hide', 'kristina_cta_box_instance_1_hide',
                'content_bay_1_box_hide', 'content_bay_2_box_hide', 'content_lake_box_hide',
                'content_sea_box_hide', 'osb_box_hide', 'reviews_box_hide',
                'serena_faq_box_hide', 'nile_map_box_hide', 'kristina_cta_box_instance_2_hide',
                'victoria_blog_box_hide', 'ocean_1_box_hide', 'ocean_2_box_hide',
                'ocean_3_box_hide', 'brook_video_box_hide', 'olivia_auth_links_box_hide',
                'ava_why_choose_us_box_hide', 'kendall_our_process_box_hide',
                'sara_custom_html_box_hide', 'liz_pricing_box_hide',
            );

            $text_fields = array('moniker', 'service_category', 'rel_service_category_id');

            // Validate field name
            if (!in_array($field, $text_fields) && !in_array($field, $box_hide_fields)) {
                $error_count++;
                $errors[] = "Invalid field: $field";
                continue;
            }

            // Type handling
            if (in_array($field, $box_hide_fields)) {
                $value = intval($value) ? 1 : 0;
            } elseif ($field === 'rel_service_category_id') {
                $value = !empty($value) ? intval($value) : null;
            } else {
                $value = sanitize_text_field($value);
            }

            // Determine format specifier
            if (in_array($field, $box_hide_fields) || ($field === 'rel_service_category_id' && $value !== null)) {
                $format = array('%d');
            } else {
                $format = array('%s');
            }

            $result = $wpdb->update(
                $pylons_table,
                array($field => $value),
                array('pylon_id' => $pylon_id),
                $format,
                array('%d')
            );

            if ($result !== false) {
                $success_count++;
            } else {
                $error_count++;
                $db_error = $wpdb->last_error;
                $errors[] = "Failed to update pylon_id: $pylon_id, field: $field, db_error: $db_error";
            }
        }

        if ($error_count > 0) {
            wp_send_json_error(array(
                'message' => "Saved $success_count changes with $error_count errors",
                'errors' => $errors
            ));
        } else {
            wp_send_json_success(array(
                'message' => "Successfully saved $success_count changes"
            ));
        }
    }
}

// Initialize the class
Ruplin_Cherry_Page_Section_Controller_Grid::get_instance();
