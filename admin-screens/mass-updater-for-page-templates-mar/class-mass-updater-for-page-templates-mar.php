<?php
/**
 * Mass Updater For Page Templates MAR Admin Page
 *
 * Handles mass page template update interface in WordPress admin
 * URL: /wp-admin/admin.php?page=mass_updater_for_page_templates_mar
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Mass_Updater_For_Page_Templates_Mar {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 25);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_init', array($this, 'early_notice_suppression'));
        add_action('current_screen', array($this, 'check_and_suppress_notices'));

        // AJAX handlers
        add_action('wp_ajax_mass_updater_page_templates_get_data', array($this, 'ajax_get_data'));
        add_action('wp_ajax_mass_updater_page_templates_save_changes', array($this, 'ajax_save_changes'));
        add_action('wp_ajax_mass_updater_page_templates_bulk_update', array($this, 'ajax_bulk_update'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_2_mar',
            'Mass Update For Page Templates',
            'Mass Update For Page Templates',
            'manage_options',
            'mass_updater_for_page_templates_mar',
            array($this, 'render_admin_page')
        );
    }

    public function early_notice_suppression() {
        if (isset($_GET['page']) && $_GET['page'] === 'mass_updater_for_page_templates_mar') {
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

    public function check_and_suppress_notices($screen) {
        if (!$screen) {
            return;
        }

        if (strpos($screen->id, 'mass_updater_for_page_templates_mar') !== false ||
            (isset($_GET['page']) && $_GET['page'] === 'mass_updater_for_page_templates_mar')) {

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

    public function render_admin_page() {
        $this->suppress_all_admin_notices();

        ?>
        <div class="wrap mupt-mar">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="mupt-mar-container">
                <div class="mupt-table-description">
                    all wp_posts joined to wp_pylons (all pylon archetypes) &nbsp;&nbsp;|||| mass update page template &amp; layout settings
                </div>

                <div class="mupt-table-actions">
                    <button id="mupt-save-changes" class="button button-primary" disabled>Save Changes</button>
                    <span id="mupt-save-status" style="margin-left: 10px;"></span>
                </div>

                <div class="mupt-bulk-actions" style="margin-bottom: 20px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" id="mupt-fill-blogpost-bilberry" class="button" style="padding: 8px 16px;">fill blogpost archetype with "bilberry" page template</button>
                    <button type="button" id="mupt-misc-archetypes-bilberry" class="button" style="padding: 8px 16px;">aboutpage, contactpage, termspage, privacypage - make "bilberry"</button>
                    <button type="button" id="mupt-all-header1" class="button" style="padding: 8px 16px;">update all pages to header1</button>
                    <button type="button" id="mupt-all-header2" class="button" style="padding: 8px 16px;">update all pages to header2</button>
                    <button type="button" id="mupt-servicepage-locationpage-cherry" class="button" style="padding: 8px 16px;">update all servicepage and locationpage to "cherry" page template</button>
                </div>

                <div class="mupt-table-wrapper">
                    <table id="mupt-table" class="mupt-table">
                        <thead>
                            <tr>
                                <th>tools</th>
                                <th>post_id</th>
                                <th>post_title</th>
                                <th>pylon_archetype</th>
                                <th>staircase_page_template_desired</th>
                                <th>header_desired</th>
                                <th>footer_desired</th>
                                <th>sidebar_desired</th>
                                <th>anteheader_desired</th>
                                <th>post_name</th>
                                <th>post_status</th>
                                <th>pylon_id</th>
                                <th>rel_wp_post_id</th>
                                <th>moniker</th>
                                <th>service_category</th>
                                <th>rel_service_category_id</th>
                                <th>category_id</th>
                                <th>category_name</th>
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

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'mass_updater_for_page_templates_mar') === false) {
            return;
        }

        wp_enqueue_style(
            'mass-updater-page-templates-mar',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'mass-updater-page-templates-mar',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('mass-updater-page-templates-mar', 'mupt_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mass_updater_page_templates_nonce')
        ));
    }

    private function suppress_all_admin_notices() {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');

        add_action('admin_head', function() {
            ?>
            <style>
                body[class*="mass_updater_for_page_templates_mar"] .notice,
                body[class*="mass_updater_for_page_templates_mar"] .notice-error,
                body[class*="mass_updater_for_page_templates_mar"] .notice-warning,
                body[class*="mass_updater_for_page_templates_mar"] .notice-success,
                body[class*="mass_updater_for_page_templates_mar"] .notice-info,
                body[class*="mass_updater_for_page_templates_mar"] .error,
                body[class*="mass_updater_for_page_templates_mar"] .updated,
                body[class*="mass_updater_for_page_templates_mar"] .update-nag,
                body[class*="mass_updater_for_page_templates_mar"] .wp-pointer,
                body[class*="mass_updater_for_page_templates_mar"] #message,
                body[class*="mass_updater_for_page_templates_mar"] .jetpack-jitm-message,
                body[class*="mass_updater_for_page_templates_mar"] .woocommerce-message,
                body[class*="mass_updater_for_page_templates_mar"] .woocommerce-error,
                body[class*="mass_updater_for_page_templates_mar"] div.fs-notice,
                body[class*="mass_updater_for_page_templates_mar"] .monsterinsights-notice,
                body[class*="mass_updater_for_page_templates_mar"] .yoast-notification,
                body[class*="mass_updater_for_page_templates_mar"] .notice-alt,
                body[class*="mass_updater_for_page_templates_mar"] .update-php,
                body[class*="mass_updater_for_page_templates_mar"] .php-update-nag,
                body[class*="mass_updater_for_page_templates_mar"] .plugin-update-tr,
                body[class*="mass_updater_for_page_templates_mar"] .theme-update-message,
                body[class*="mass_updater_for_page_templates_mar"] .update-message,
                body[class*="mass_updater_for_page_templates_mar"] .updating-message,
                body[class*="mass_updater_for_page_templates_mar"] #update-nag,
                body[class*="mass_updater_for_page_templates_mar"] #deprecation-warning,
                body[class*="mass_updater_for_page_templates_mar"] .activated,
                body[class*="mass_updater_for_page_templates_mar"] .deactivated,
                body[class*="mass_updater_for_page_templates_mar"] [class*="notice"],
                body[class*="mass_updater_for_page_templates_mar"] [class*="updated"],
                body[class*="mass_updater_for_page_templates_mar"] [class*="error"],
                body[class*="mass_updater_for_page_templates_mar"] [id*="notice"],
                body[class*="mass_updater_for_page_templates_mar"] [id*="message"] {
                    display: none !important;
                }

                body[class*="mass_updater_for_page_templates_mar"] .mupt-mar-notice {
                    display: block !important;
                }

                body[class*="mass_updater_for_page_templates_mar"] .wrap h1,
                body[class*="mass_updater_for_page_templates_mar"] .mupt-mar-container {
                    display: block !important;
                }
            </style>
            <?php
        }, 999);

        add_action('admin_footer', function() {
            ?>
            <script>
                jQuery(document).ready(function($) {
                    $('.notice, .error, .updated, .update-nag').not('.mupt-mar-notice').remove();

                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            $(mutation.addedNodes).each(function() {
                                if ($(this).hasClass('notice') ||
                                    $(this).hasClass('error') ||
                                    $(this).hasClass('updated') ||
                                    $(this).hasClass('update-nag')) {
                                    if (!$(this).hasClass('mupt-mar-notice')) {
                                        $(this).remove();
                                    }
                                }
                            });
                        });
                    });

                    if (document.body) {
                        observer.observe(document.body, { childList: true, subtree: true });
                    }

                    var wpbody = document.getElementById('wpbody-content');
                    if (wpbody) {
                        observer.observe(wpbody, { childList: true, subtree: true });
                    }
                });
            </script>
            <?php
        }, 999);

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
                pyl.staircase_page_template_desired,
                pyl.header_desired,
                pyl.footer_desired,
                pyl.sidebar_desired,
                pyl.anteheader_desired,
                pyl.moniker,
                pyl.service_category,
                pyl.rel_service_category_id,
                sc.category_id,
                sc.category_name
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}pylons pyl ON p.ID = pyl.rel_wp_post_id
            LEFT JOIN {$wpdb->prefix}service_categories sc ON pyl.rel_service_category_id = sc.category_id
            WHERE p.post_status IN ('publish', 'draft', 'private', 'pending')
            ORDER BY p.post_title ASC
        ";

        return $wpdb->get_results($query, ARRAY_A);
    }

    private function render_table_rows() {
        $data = $this->get_service_page_data();
        $output = '';

        if (empty($data)) {
            $output = '<tr><td colspan="18" class="no-data">No posts with pylon data found</td></tr>';
        } else {
            foreach ($data as $row) {
                $output .= '<tr data-pylon-id="' . esc_attr($row['pylon_id']) . '">';
                $pid = esc_attr($row['post_id']);
                $output .= '<td class="mupt-tools-cell">';
                $output .= '<a href="' . admin_url('post.php?post=' . $pid . '&action=edit') . '" class="mupt-tool-btn" target="_blank">pendulum</a>';
                $output .= '<a href="' . admin_url('admin.php?page=cashew_editor&post_id=' . $pid) . '" class="mupt-tool-btn" target="_blank">cashew</a>';
                $output .= '<a href="' . admin_url('admin.php?page=telescope_content_editor&post=' . $pid) . '" class="mupt-tool-btn" target="_blank">telescope</a>';
                $output .= '<a href="' . esc_url(get_permalink($row['post_id'])) . '" class="mupt-tool-btn" target="_blank">front end</a>';
                $output .= '</td>';
                $output .= '<td>' . esc_html($row['post_id']) . '</td>';
                $output .= '<td>' . esc_html($row['post_title']) . '</td>';
                $output .= '<td>' . esc_html($row['pylon_archetype']) . '</td>';
                $output .= '<td><input type="text" class="mupt-editable-field" data-field="staircase_page_template_desired" data-original="' . esc_attr($row['staircase_page_template_desired'] ?? '') . '" value="' . esc_attr($row['staircase_page_template_desired'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="mupt-editable-field" data-field="header_desired" data-original="' . esc_attr($row['header_desired'] ?? '') . '" value="' . esc_attr($row['header_desired'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="mupt-editable-field" data-field="footer_desired" data-original="' . esc_attr($row['footer_desired'] ?? '') . '" value="' . esc_attr($row['footer_desired'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="mupt-editable-field" data-field="sidebar_desired" data-original="' . esc_attr($row['sidebar_desired'] ?? '') . '" value="' . esc_attr($row['sidebar_desired'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="mupt-editable-field" data-field="anteheader_desired" data-original="' . esc_attr($row['anteheader_desired'] ?? '') . '" value="' . esc_attr($row['anteheader_desired'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td>' . esc_html($row['post_name']) . '</td>';
                $output .= '<td>' . esc_html($row['post_status']) . '</td>';
                $output .= '<td>' . esc_html($row['pylon_id']) . '</td>';
                $output .= '<td>' . esc_html($row['rel_wp_post_id']) . '</td>';
                $output .= '<td><input type="text" class="mupt-editable-field" data-field="moniker" data-original="' . esc_attr($row['moniker'] ?? '') . '" value="' . esc_attr($row['moniker'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="mupt-editable-field" data-field="service_category" data-original="' . esc_attr($row['service_category'] ?? '') . '" value="' . esc_attr($row['service_category'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td><input type="text" class="mupt-editable-field" data-field="rel_service_category_id" data-original="' . esc_attr($row['rel_service_category_id'] ?? '') . '" value="' . esc_attr($row['rel_service_category_id'] ?? '') . '" style="width: 100%;"></td>';
                $output .= '<td>' . esc_html($row['category_id'] ?? '') . '</td>';
                $output .= '<td>' . esc_html($row['category_name'] ?? '') . '</td>';
                $output .= '</tr>';
            }
        }

        return $output;
    }

    public function ajax_get_data() {
        check_ajax_referer('mass_updater_page_templates_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $data = $this->get_service_page_data();

        wp_send_json_success(array(
            'data' => $data,
            'count' => count($data)
        ));
    }

    public function ajax_save_changes() {
        check_ajax_referer('mass_updater_page_templates_nonce', 'nonce');

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

            $allowed_text_fields = array(
                'moniker', 'service_category', 'rel_service_category_id',
                'staircase_page_template_desired', 'header_desired',
                'footer_desired', 'sidebar_desired', 'anteheader_desired',
            );

            if (!in_array($field, $allowed_text_fields)) {
                $error_count++;
                $errors[] = "Invalid field: $field";
                continue;
            }

            if ($field === 'rel_service_category_id') {
                $value = !empty($value) ? intval($value) : null;
            } else {
                $value = sanitize_text_field($value);
            }

            $result = $wpdb->update(
                $pylons_table,
                array($field => $value),
                array('pylon_id' => $pylon_id),
                ($field === 'rel_service_category_id' && $value !== null) ? array('%d') : array('%s'),
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

    public function ajax_bulk_update() {
        check_ajax_referer('mass_updater_page_templates_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;
        $pylons_table = $wpdb->prefix . 'pylons';

        $bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';

        switch ($bulk_action) {
            case 'fill_blogpost_bilberry':
                $affected = $wpdb->query($wpdb->prepare(
                    "UPDATE {$pylons_table} SET staircase_page_template_desired = %s WHERE pylon_archetype = %s",
                    'bilberry',
                    'blogpost'
                ));
                wp_send_json_success(array(
                    'message' => "Updated $affected blogpost rows to bilberry page template"
                ));
                break;

            case 'misc_archetypes_bilberry':
                $affected = $wpdb->query($wpdb->prepare(
                    "UPDATE {$pylons_table} SET staircase_page_template_desired = %s WHERE pylon_archetype IN (%s, %s, %s, %s)",
                    'bilberry',
                    'aboutpage',
                    'contactpage',
                    'termspage',
                    'privacypage'
                ));
                wp_send_json_success(array(
                    'message' => "Updated $affected aboutpage/contactpage/termspage/privacypage rows to bilberry page template"
                ));
                break;

            case 'all_header1':
                $affected = $wpdb->query($wpdb->prepare(
                    "UPDATE {$pylons_table} SET header_desired = %s",
                    'header1'
                ));
                wp_send_json_success(array(
                    'message' => "Updated $affected rows to header1"
                ));
                break;

            case 'all_header2':
                $affected = $wpdb->query($wpdb->prepare(
                    "UPDATE {$pylons_table} SET header_desired = %s",
                    'header2'
                ));
                wp_send_json_success(array(
                    'message' => "Updated $affected rows to header2"
                ));
                break;

            case 'servicepage_locationpage_cherry':
                $affected = $wpdb->query($wpdb->prepare(
                    "UPDATE {$pylons_table} SET staircase_page_template_desired = %s WHERE pylon_archetype IN (%s, %s)",
                    'cherry',
                    'servicepage',
                    'locationpage'
                ));
                wp_send_json_success(array(
                    'message' => "Updated $affected servicepage/locationpage rows to cherry page template"
                ));
                break;

            default:
                wp_send_json_error('Unknown bulk action');
                break;
        }
    }
}

Ruplin_Mass_Updater_For_Page_Templates_Mar::get_instance();
