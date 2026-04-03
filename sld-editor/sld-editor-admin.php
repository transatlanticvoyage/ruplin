<?php
/**
 * SLD Editor - Site Level Data Editor
 * 
 * Admin page for editing site-level data
 * Located in ruplin/sld-editor/
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLD_Editor_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 15); // Higher priority to ensure parent menu exists
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_notices', array($this, 'suppress_admin_notices'), 1);
        add_action('all_admin_notices', array($this, 'suppress_admin_notices'), 1);
        add_action('network_admin_notices', array($this, 'suppress_admin_notices'), 1);
        add_action('user_admin_notices', array($this, 'suppress_admin_notices'), 1);
        add_action('wp_ajax_sld_editor_save', array($this, 'ajax_save_data'));
    }
    
    /**
     * Add admin menu item under Snefuru
     */
    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_2_mar',  // Parent slug (Ruplin Hub 2)
            'SLD Editor (Site Level Data)',  // Page title
            'SLD Editor (Site Level Data)',  // Menu title
            'manage_options',  // Capability
            'sld_editor',  // Menu slug
            array($this, 'admin_page')  // Callback
        );
    }
    
    /**
     * Admin page output
     */
    public function admin_page() {
        // Aggressive notice suppression
        $this->aggressive_notice_suppression();
        ?>
        
        <style>
            /* Aggressive notice hiding */
            .notice,
            .notice-error,
            .notice-warning,
            .notice-success,
            .notice-info,
            .error,
            .updated,
            .update-nag,
            .update-message,
            #message,
            .wrap > .notice,
            .wrap > .error,
            .wrap > .updated,
            #wpbody-content > .notice,
            #wpbody-content > .error,
            #wpbody-content > .updated,
            .wp-admin .notice,
            .wp-admin .error,
            .wp-admin .updated,
            div.notice,
            div.error,
            div.updated,
            .notice-dismiss,
            .notice-alt,
            .notice-large,
            #setting-error-settings_updated,
            #setting-error-saved,
            .inline-edit-save .notice-error,
            .notice-title,
            .wp-core-ui .notice,
            body.wp-admin .notice,
            body.wp-admin .error,
            body.wp-admin .updated,
            .jetpack-jitm-message,
            .jitm-banner,
            .jetpack-jitm-card,
            .woocommerce-message,
            .woocommerce-error,
            .woocommerce-info,
            .wc-update-nag,
            .wc_plugin_upgrade_notice,
            .elementor-message,
            .e-notice,
            .yoast-notification,
            .yoast-alert,
            .redux-messageredux-notice,
            .redux-message,
            [class*="notice-"],
            [class*="-notice"],
            [id*="notice-"],
            [id*="-notice"],
            .components-notice,
            .components-notice-list,
            .sucuriscan-visible,
            .sucuriscan-notice,
            .autoptimize-notice,
            .autoptimize-notice-wrapper,
            .wp-mail-smtp-notice,
            .monsterinsights-notice,
            .wordfence-notice,
            .smush-notice,
            .defender-notice,
            .hummingbird-notice,
            .snapshot-notice,
            .forminator-notice,
            .hustle-notice,
            .beehive-notice,
            .branda-notice,
            .wpmudev-notice,
            .wphb-notice,
            .sui-notice,
            .rank-math-notice,
            .rank-math-review-notice,
            .seo-admin-notice,
            .wp-rocket-notice,
            .imagify-notice,
            .backwpup-notice,
            .updraftplus-notice,
            .itsec-notice,
            .sucuri-notice,
            .wpforms-notice,
            .wpforms-admin-notice,
            .give-notice,
            .tribe-notice,
            .the-events-calendar-notice,
            .edd-notice,
            .lifterlms-notice,
            .learndash-notice,
            .memberpress-notice,
            .restrict-content-pro-notice,
            .pmpro-notice,
            .gravityforms-notice,
            .gform-notice,
            .ninja-forms-notice,
            .nf-admin-notice,
            .caldera-forms-notice,
            .cf2-notice,
            .contact-form-7-notice,
            .wpcf7-notice,
            .wpml-notice,
            .otgs-notice,
            .polylang-notice,
            .translatepress-notice,
            .weglot-notice,
            .acf-notice,
            .acf-admin-notice,
            .vc_notice,
            .wpbakery-notice,
            .fusion-notice,
            .avada-notice,
            .divi-notice,
            .et-notice,
            .oxygen-notice,
            .ct-notice,
            .beaver-notice,
            .fl-notice,
            .cornerstone-notice,
            .x-notice,
            .pro-notice,
            .themeco-notice,
            .flatsome-notice,
            .ux-notice,
            .salient-notice,
            .nectar-notice,
            .uncode-notice,
            .bridge-notice,
            .qode-notice,
            .betheme-notice,
            .mfn-notice,
            .enfold-notice,
            .avia-notice,
            .jupiterx-notice,
            .artbees-notice,
            .woodmart-notice,
            .xts-notice,
            .porto-notice,
            .p-theme-notice {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                pointer-events: none !important;
            }
            
            /* Ensure the content area is clean */
            #wpbody-content > *:not(.wrap) {
                display: none !important;
            }
            
            /* Reset any notice-related margins */
            .wrap {
                margin-top: 10px !important;
            }
            
            /* Hide admin email verification notices */
            #admin-email-verify-nag,
            .admin-email-verify-nag,
            .admin-email__verification-notice {
                display: none !important;
            }
            
            /* SLD Editor specific styles */
            .sld-editor-wrap {
                margin: 20px 0;
                padding: 20px;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 4px;
            }
            
            .sld-editor-title {
                font-size: 23px;
                font-weight: 400;
                margin: 0 0 20px 0;
                color: #1e293b;
            }
            
            /* Table styles matching Cashew Editor */
            .sld-editor-table {
                width: auto;
                border-collapse: collapse;
                margin-top: 20px;
                background: white;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                display: inline-table;
            }

            .sld-editor-table thead {
                background-color: #f9fafb;
                position: sticky;
                top: 0;
            }

            .sld-editor-table th {
                padding: 12px 16px;
                text-align: left;
                font-size: 11px;
                font-weight: 500;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border: 1px solid #e5e7eb;
                white-space: nowrap;
            }

            .sld-editor-table td {
                padding: 12px 16px;
                border: 1px solid #e5e7eb;
                vertical-align: top;
                white-space: nowrap;
            }
            
            .sld-field-label {
                font-weight: 700;
                color: #000;
                min-width: 200px;
                text-transform: lowercase;
            }
            
            .sld-adjunct-column {
                white-space: nowrap;
                min-width: auto;
            }

            .sld-field-input {
                width: 300px;
                padding: 8px 12px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                font-size: 14px;
            }

            .sld-field-textarea {
                width: 500px;
                min-height: 200px;
                padding: 12px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 13px;
                line-height: 1.4;
                resize: vertical;
            }

            .sld-readonly {
                background-color: #f3f4f6;
                color: #6b7280;
                cursor: not-allowed;
            }

            .sld-save-btn {
                background: #059669;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                margin-top: 20px;
            }

            .sld-save-btn:hover {
                background: #047857;
            }

            .sld-adjunct-column {
                padding: 8px;
                vertical-align: top;
                width: 200px;
            }
            
            .sld-notice {
                margin: 20px 0;
                padding: 15px;
                background: #d1fae5;
                border-left: 4px solid #059669;
                border-radius: 4px;
                display: none;
            }
            
            .sld-notice.success {
                background: #d1fae5;
                border-left-color: #059669;
            }
            
            .sld-notice.error {
                background: #fee2e2;
                border-left-color: #dc2626;
            }
        </style>
        
        <script>
            jQuery(document).ready(function($) {
                // Remove all notices via JavaScript as well
                $('.notice, .notice-error, .notice-warning, .notice-success, .notice-info, .error, .updated, .update-nag').remove();
                
                // Continuously remove notices that might be added dynamically
                setInterval(function() {
                    $('.notice, .notice-error, .notice-warning, .notice-success, .notice-info, .error, .updated, .update-nag').remove();
                    $('[class*="notice-"], [class*="-notice"], [id*="notice-"], [id*="-notice"]').remove();
                }, 100);
                
                // Prevent notices from being added
                $(document).on('DOMNodeInserted', function(e) {
                    var $target = $(e.target);
                    if ($target.hasClass('notice') || 
                        $target.hasClass('error') || 
                        $target.hasClass('updated') || 
                        $target.hasClass('update-nag') ||
                        $target.attr('class') && (
                            $target.attr('class').indexOf('notice') !== -1 ||
                            $target.attr('class').indexOf('error') !== -1 ||
                            $target.attr('class').indexOf('updated') !== -1
                        )) {
                        $target.remove();
                    }
                });
                
                // Handle form submission via AJAX
                $('#sld-editor-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    var $form = $(this);
                    var $button = $form.find('.sld-save-btn');
                    var $notice = $('#sld-notice');
                    
                    // Disable button and show loading state
                    $button.prop('disabled', true).text('Saving...');
                    $notice.hide();
                    
                    // Prepare data
                    var formData = $form.serialize();
                    formData += '&action=sld_editor_save';
                    formData += '&sld_editor_nonce=' + $('#sld_editor_nonce').val();
                    
                    // Send AJAX request
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                $notice.removeClass('error').addClass('success')
                                    .html(response.data.message).fadeIn();
                                
                                // Hide notice after 3 seconds
                                setTimeout(function() {
                                    $notice.fadeOut();
                                }, 3000);
                            } else {
                                $notice.removeClass('success').addClass('error')
                                    .html(response.data.message || 'An error occurred').fadeIn();
                            }
                        },
                        error: function() {
                            $notice.removeClass('success').addClass('error')
                                .html('An error occurred while saving').fadeIn();
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Save Changes');
                        }
                    });
                });
            });
        </script>
        
        <div class="wrap">
            <div class="sld-editor-wrap">
                <h1 class="sld-editor-title">SLD Editor (Site Level Data)</h1>
                
                <div class="sld-notice" id="sld-notice"></div>
                
                <form id="sld-editor-form" method="post" action="">
                    <?php wp_nonce_field('sld_editor_save', 'sld_editor_nonce'); ?>
                    
                    <table class="sld-editor-table" id="sld-editor-table">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Datum House</th>
                                <th>Adjunct 1</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'zen_sitespren';
                            
                            // Get the first row of data
                            $sitespren_data = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1", ARRAY_A);
                            
                            // Define all columns from wp_zen_sitespren
                            $columns = array(
                                'id', 'wppma_id', 'wppma_db_only_created_at', 'wppma_db_only_updated_at',
                                'created_at', 'sitespren_base', 'true_root_domain', 'full_subdomain',
                                'webproperty_type', 'fk_users_id', 'updated_at', 'wpuser1', 'wppass1',
                                'wp_plugin_installed1', 'wp_plugin_connected2', 'fk_domreg_hostaccount',
                                'is_wp_site', 'wp_rest_app_pass', 'driggs_industry', 'driggs_keywords',
                                'driggs_category', 'driggs_city', 'driggs_brand_name', 'driggs_site_type_purpose',
                                'driggs_email_1', 'driggs_phone_1', 'driggs_address_full', 'driggs_street_1',
                                'driggs_street_2', 'driggs_state_code', 'driggs_zip', 'driggs_state_full',
                                'driggs_country', 'driggs_payment_methods', 'driggs_social_media_links',
                                'driggs_hours', 'driggs_owner_name', 'driggs_short_descr', 'driggs_long_descr',
                                'driggs_footer_blurb', 'driggs_year_opened', 'driggs_employees_qty',
                                'driggs_special_note_for_ai_tool', 'driggs_logo_url', 'ns_full', 'ip_address',
                                'is_starred1', 'icon_name', 'icon_color', 'is_bulldozer', 'driggs_phone1_platform_id',
                                'driggs_cgig_id', 'driggs_revenue_goal', 'driggs_address_species_id',
                                'driggs_address_species_note', 'is_competitor', 'is_external', 'is_internal',
                                'is_ppx', 'is_ms', 'is_wayback_rebuild', 'is_naked_wp_build', 'is_rnr', 'is_aff',
                                'is_other1', 'is_other2', 'driggs_citations_done', 'driggs_social_profiles_done',
                                'is_flylocal', 'snailimage', 'snail_image_url', 'snail_image_status',
                                'snail_image_error', 'contact_form_1_endpoint', 'contact_form_1_main_code',
                                'weasel_header_code_1', 'weasel_footer_code_1', 'weasel_header_code_for_analytics',
                                'weasel_footer_code_for_analytics', 'weasel_header_code_for_contact_form',
                                'weasel_footer_code_for_contact_form', 'ratingvalue_for_schema',
                                'reviewcount_for_schema', 'avg_rating_box_hide_sitewide', 'footer_disclaimer'
                            );
                            
                            // Render rows for each column
                            foreach ($columns as $column) {
                                $value = isset($sitespren_data[$column]) ? $sitespren_data[$column] : '';
                                ?>
                                <tr>
                                    <td class="sld-field-label"><?php echo esc_html($column); ?></td>
                                    <td>
                                        <input type="text" 
                                               name="sitespren[<?php echo esc_attr($column); ?>]" 
                                               value="<?php echo esc_attr($value); ?>" 
                                               class="sld-field-input" />
                                    </td>
                                    <td class="sld-adjunct-column"></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    
                    <button type="submit" class="sld-save-btn">Save Changes</button>
                </form>
                
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        // Only load on our admin page
        if ($hook !== 'ruplin-hub-2_page_sld_editor') {
            return;
        }
        
        // Additional script to hide notices via JavaScript
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                $(".notice, .error, .warning, .update-nag, .updated, .settings-error").hide();
                
                // Hide notices that might appear after page load
                setTimeout(function() {
                    $(".notice, .error, .warning, .update-nag, .updated, .settings-error").hide();
                }, 100);
                
                // Watch for dynamically added notices
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === "childList") {
                            $(mutation.target).find(".notice, .error, .warning, .update-nag, .updated, .settings-error").hide();
                        }
                    });
                });
                observer.observe(document.body, { childList: true, subtree: true });
            });
        ');
    }
    
    /**
     * Suppress admin notices
     */
    public function suppress_admin_notices() {
        if (isset($_GET['page']) && $_GET['page'] === 'sld_editor') {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
        }
    }
    
    /**
     * Aggressive notice suppression
     */
    private function aggressive_notice_suppression() {
        // Remove all notice hooks
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        // Additional aggressive suppression
        add_action('admin_notices', '__return_false', PHP_INT_MAX);
        add_action('all_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('network_admin_notices', '__return_false', PHP_INT_MAX);
        add_action('user_admin_notices', '__return_false', PHP_INT_MAX);
        
        // Suppress all PHP notices and warnings for this page
        error_reporting(E_ERROR | E_PARSE);
        
        // Remove any queued notices
        if (isset($GLOBALS['wp_admin_notices'])) {
            $GLOBALS['wp_admin_notices'] = array();
        }
    }
    
    /**
     * AJAX handler for saving SLD data
     */
    public function ajax_save_data() {
        // Verify nonce
        if (!isset($_POST['sld_editor_nonce']) || !wp_verify_nonce($_POST['sld_editor_nonce'], 'sld_editor_save')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        // Get sitespren data
        if (!isset($_POST['sitespren']) || !is_array($_POST['sitespren'])) {
            wp_send_json_error(array('message' => 'No data to save'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        $sitespren_data = $_POST['sitespren'];
        
        // Check if a row exists
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        if ($existing > 0) {
            // Update existing row
            $sitespren_data['wppma_db_only_updated_at'] = current_time('mysql');
            
            // Build update data array (exclude certain fields)
            $update_data = array();
            $skip_fields = array('id', 'wppma_id', 'wppma_db_only_created_at', 'created_at');
            
            foreach ($sitespren_data as $field => $value) {
                if (!in_array($field, $skip_fields)) {
                    $update_data[$field] = $value;
                }
            }
            
            $result = $wpdb->update(
                $table_name,
                $update_data,
                array(), // Update all rows (should only be one)
                null,
                array() // No WHERE clause format
            );
            
            if ($result !== false) {
                wp_send_json_success(array('message' => 'Site Level Data updated successfully'));
            } else {
                wp_send_json_error(array('message' => 'Failed to update data'));
            }
        } else {
            // Insert new row
            $sitespren_data['id'] = wp_generate_uuid4();
            $sitespren_data['wppma_db_only_created_at'] = current_time('mysql');
            $sitespren_data['wppma_db_only_updated_at'] = current_time('mysql');
            $sitespren_data['created_at'] = current_time('mysql');
            
            $result = $wpdb->insert($table_name, $sitespren_data);
            
            if ($result !== false) {
                wp_send_json_success(array('message' => 'Site Level Data created successfully'));
            } else {
                wp_send_json_error(array('message' => 'Failed to create data'));
            }
        }
    }
}

// Initialize the SLD Editor
new SLD_Editor_Admin();