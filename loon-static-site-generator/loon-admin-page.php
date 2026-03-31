<?php

/**
 * Loon Static Site Generator Admin Page
 * 
 * Admin page for managing static site generation
 */

if (!defined('ABSPATH')) {
    exit;
}

function loon_static_site_generation_mar_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Aggressive notice suppression
    remove_all_actions('admin_notices');
    remove_all_actions('all_admin_notices');
    remove_all_actions('network_admin_notices');
    remove_all_actions('user_admin_notices');
    
    // Additional aggressive notice suppression
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
    
    ?>
    
    <style>
        /* Hide all WordPress notices on this page */
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
        });
    </script>
    
    <style>
        /* Table styles */
        .loon-table-container {
            margin-top: 20px;
        }
        
        .loon-db-table-name {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            font-family: monospace;
        }
        
        .loon-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .loon-table th,
        .loon-table td {
            border: 1px solid gray;
            padding: 8px;
            text-align: left;
        }
        
        .loon-table th {
            background: #f5f5f5;
            font-size: 16px;
            color: #242424;
            font-weight: normal;
        }
        
        .loon-table td {
            font-size: 14px;
        }
        
        .loon-generate-box {
            background: white;
            border: 1px solid #ccc;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .loon-generate-button {
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .loon-generate-button:hover {
            background: #005a87;
        }
        
        .loon-options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .loon-option-card {
            border: 2px solid #ddd;
            padding: 15px;
            background: white;
            border-radius: 4px;
            position: relative;
        }
        
        .loon-option-card.selected {
            border-color: #0073aa;
            background: #f0f8ff;
        }
        
        .loon-option-card.disabled {
            opacity: 0.5;
            pointer-events: none;
            background: #f5f5f5;
        }
        
        .loon-option-card input[type="radio"] {
            margin-right: 10px;
        }
        
        .loon-option-card h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        
        .loon-option-card p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
        
        .loon-checkboxes {
            margin: 20px 0;
            background: white;
            padding: 15px;
            border: 1px solid #ddd;
        }
        
        .loon-checkboxes label {
            display: block;
            margin-bottom: 10px;
        }
        
        .loon-checkboxes input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .loon-status-message {
            margin-top: 20px;
            padding: 15px;
            background: #f0f8ff;
            border-left: 4px solid #0073aa;
            display: none;
        }
        
        .loon-status-message.success {
            background: #f0fff4;
            border-color: #46b450;
        }
        
        .loon-status-message.error {
            background: #ffeef1;
            border-color: #dc3232;
        }
    </style>
    
    <div class="wrap">
        <h1>Loon Static Site Generator</h1>
        
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'loon_static_site_generations';
        
        // Check if we're on localhost
        require_once plugin_dir_path(__FILE__) . 'class-loon-db.php';
        $loon_db = new Loon_DB();
        $is_localhost = $loon_db->is_localhost();
        
        // Get all records from the table
        $generations = $wpdb->get_results("SELECT * FROM $table_name ORDER BY generation_id DESC", ARRAY_A);
        ?>
        
        <div class="loon-generate-box">
            <h2>Generate New Static Site</h2>
            
            <div class="loon-options-grid">
                <div class="loon-option-card <?php echo $is_localhost ? '' : 'disabled'; ?>" id="local-option">
                    <label>
                        <input type="radio" name="output_location" value="local" <?php echo $is_localhost ? 'checked' : 'disabled'; ?>>
                        <strong>Generate on Local Machine</strong>
                    </label>
                    <p>/Users/kylecampbell/Documents/repos/loon-static-site-outputs/</p>
                    <?php if (!$is_localhost) : ?>
                        <p style="color: red; margin-top: 10px;">Only available on localhost</p>
                    <?php endif; ?>
                </div>
                
                <div class="loon-option-card <?php echo !$is_localhost ? 'selected' : ''; ?>" id="wp-content-option">
                    <label>
                        <input type="radio" name="output_location" value="wp_content" <?php echo !$is_localhost ? 'checked' : ''; ?>>
                        <strong>Generate in WP Content</strong>
                    </label>
                    <p>/wp-content/loon-static-site-outputs/</p>
                </div>
            </div>
            
            <div class="loon-checkboxes">
                <h3>Include Content Types:</h3>
                <label>
                    <input type="checkbox" id="include-pages" checked> Include Pages
                </label>
                <label>
                    <input type="checkbox" id="include-posts" checked> Include Posts
                </label>
                <label>
                    <input type="checkbox" id="generate-zip" checked> Generate ZIP File
                </label>
            </div>
            
            <button type="button" class="loon-generate-button" id="loon-generate-btn">
                Generate Static Site Now
            </button>
            
            <div class="loon-status-message" id="loon-status-message"></div>
        </div>
        
        <div class="loon-table-container">
            <div class="loon-db-table-name">Database Table: <?php echo esc_html($table_name); ?></div>
            
            <table class="loon-table">
                <thead>
                    <tr>
                        <th>generation_id</th>
                        <th>folder_number</th>
                        <th>site_domain</th>
                        <th>output_path</th>
                        <th>page_count</th>
                        <th>post_count</th>
                        <th>total_files</th>
                        <th>total_size_mb</th>
                        <th>zip_filename</th>
                        <th>zip_path</th>
                        <th>status</th>
                        <th>options_json</th>
                        <th>error_message</th>
                        <th>created_at</th>
                        <th>completed_at</th>
                        <th>download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($generations)) : ?>
                        <?php foreach ($generations as $gen) : ?>
                            <tr>
                                <td><?php echo esc_html($gen['generation_id']); ?></td>
                                <td><?php echo esc_html($gen['folder_number']); ?></td>
                                <td><?php echo esc_html($gen['site_domain']); ?></td>
                                <td><?php echo esc_html($gen['output_path']); ?></td>
                                <td><?php echo esc_html($gen['page_count']); ?></td>
                                <td><?php echo esc_html($gen['post_count']); ?></td>
                                <td><?php echo esc_html($gen['total_files']); ?></td>
                                <td><?php echo esc_html($gen['total_size_mb']); ?></td>
                                <td><?php echo esc_html($gen['zip_filename']); ?></td>
                                <td><?php echo esc_html($gen['zip_path']); ?></td>
                                <td><?php echo esc_html($gen['status']); ?></td>
                                <td><?php echo esc_html($gen['options_json']); ?></td>
                                <td><?php echo esc_html($gen['error_message']); ?></td>
                                <td><?php echo esc_html($gen['created_at']); ?></td>
                                <td><?php echo esc_html($gen['completed_at']); ?></td>
                                <td>
                                    <?php if ($gen['zip_path']) : ?>
                                        <button type="button" 
                                                class="button loon-download-btn" 
                                                data-generation-id="<?php echo esc_attr($gen['generation_id']); ?>">
                                            Download
                                        </button>
                                    <?php else : ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="16" style="text-align: center; color: #999;">No generations found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle option card selection
        $('input[name="output_location"]').on('change', function() {
            $('.loon-option-card').removeClass('selected');
            $(this).closest('.loon-option-card').addClass('selected');
        });
        
        // Handle generate button click
        $('#loon-generate-btn').on('click', function() {
            var $btn = $(this);
            var $statusMsg = $('#loon-status-message');
            
            // Get selected options
            var outputLocation = $('input[name="output_location"]:checked').val();
            var includePages = $('#include-pages').is(':checked');
            var includePosts = $('#include-posts').is(':checked');
            var generateZip = $('#generate-zip').is(':checked');
            
            // Validate
            if (!includePages && !includePosts) {
                alert('Please select at least one content type to include');
                return;
            }
            
            // Show processing state
            $btn.prop('disabled', true).text('Generating...');
            $statusMsg.removeClass('success error').addClass('processing').text('Processing...').show();
            
            // Prepare data
            var data = {
                action: 'loon_generate_static_site',
                nonce: '<?php echo wp_create_nonce('loon_generate_nonce'); ?>',
                output_location: outputLocation,
                include_pages: includePages,
                include_posts: includePosts,
                generate_zip: generateZip
            };
            
            // Send AJAX request
            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    $statusMsg.removeClass('processing error').addClass('success');
                    $statusMsg.html(response.data.message);
                    
                    // Reload page after 2 seconds to show new generation
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $statusMsg.removeClass('processing success').addClass('error');
                    $statusMsg.html(response.data.message || 'An error occurred');
                }
            })
            .fail(function() {
                $statusMsg.removeClass('processing success').addClass('error');
                $statusMsg.html('Network error occurred');
            })
            .always(function() {
                $btn.prop('disabled', false).text('Generate Static Site Now');
            });
        });
        
        // Handle download button clicks
        $(document).on('click', '.loon-download-btn', function() {
            var generationId = $(this).data('generation-id');
            
            // Create form and submit for download
            var form = $('<form>', {
                method: 'POST',
                action: ajaxurl
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: 'loon_download_zip'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'nonce',
                value: '<?php echo wp_create_nonce('loon_download_nonce'); ?>'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'generation_id',
                value: generationId
            }));
            
            form.appendTo('body').submit().remove();
        });
    });
    </script>
    
    <?php
}