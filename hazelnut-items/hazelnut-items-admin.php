<?php
/**
 * Hazelnut Items Manager
 * 
 * Admin page for managing hazelnut static page uploads
 * Located in ruplin/hazelnut-items/
 */

if (!defined('ABSPATH')) {
    exit;
}

class Hazelnut_Items_Admin {
    
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
        add_action('wp_ajax_hazelnut_import_zip', array($this, 'ajax_import_zip'));
        add_action('wp_ajax_hazelnut_import_local', array($this, 'ajax_import_local'));
        add_action('wp_ajax_hazelnut_generate_hardcoded', array($this, 'ajax_generate_hardcoded_refs'));
        add_action('wp_ajax_hazelnut_get_html_content', array($this, 'ajax_get_html_content'));
        add_action('wp_ajax_hazelnut_generate_file3', array($this, 'ajax_generate_file3'));
        add_action('wp_ajax_hazelnut_get_file3_content', array($this, 'ajax_get_file3_content'));
        add_action('wp_ajax_hazelnut_get_dependencies', array($this, 'ajax_get_dependencies'));
    }
    
    /**
     * Add admin menu item under Snefuru
     */
    public function add_admin_menu() {
        add_submenu_page(
            'snefuru',  // Parent slug (Ruplin's main menu)
            'Hazelnut Items Mar',  // Page title
            'Hazelnut Items Mar',  // Menu title
            'manage_options',  // Capability
            'hazelnut_items_mar',  // Menu slug
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
            
            /* Hazelnut Items specific styles */
            .hazelnut-items-wrap {
                margin: 20px 0;
                padding: 20px;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 4px;
            }
            
            .hazelnut-items-title {
                font-size: 23px;
                font-weight: 400;
                margin: 0 0 20px 0;
                color: #1e293b;
            }
            
            /* Import interface styles */
            .hazelnut-import-section {
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                padding: 20px;
                margin-bottom: 30px;
            }
            
            .hazelnut-import-row {
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .hazelnut-import-row:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }
            
            .hazelnut-import-title {
                font-size: 16px;
                font-weight: 600;
                color: #374151;
                margin-bottom: 10px;
            }
            
            .hazelnut-import-description {
                color: #6b7280;
                font-size: 14px;
                margin-bottom: 15px;
            }
            
            .hazelnut-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                font-size: 14px;
                margin-right: 10px;
            }
            
            .hazelnut-btn-primary {
                background: #059669;
                color: white;
            }
            
            .hazelnut-btn-primary:hover {
                background: #047857;
            }
            
            .hazelnut-btn-secondary {
                background: #6b7280;
                color: white;
            }
            
            .hazelnut-btn-secondary:hover {
                background: #4b5563;
            }
            
            .hazelnut-local-path {
                background: #fff;
                padding: 10px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                font-family: monospace;
                font-size: 13px;
                color: #374151;
                margin: 10px 0;
            }
            
            /* Table styles */
            .hazelnut-table-container {
                overflow-x: auto;
                margin-top: 20px;
            }
            
            .hazelnut-items-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
                border: 1px solid #e5e7eb;
            }
            
            .hazelnut-items-table th {
                padding: 12px;
                text-align: left;
                font-size: 16px;
                font-weight: normal;
                color: #242424;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                text-transform: lowercase;
            }
            
            .hazelnut-items-table td {
                padding: 12px;
                border: 1px solid #e5e7eb;
                font-size: 14px;
                color: #374151;
            }
            
            .hazelnut-items-table tbody tr:hover {
                background: #f9fafb;
            }
            
            .hazelnut-status-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
            }
            
            .hazelnut-status-completed {
                background: #d1fae5;
                color: #059669;
            }
            
            .hazelnut-status-pending {
                background: #fef3c7;
                color: #d97706;
            }
            
            .hazelnut-status-failed {
                background: #fee2e2;
                color: #dc2626;
            }
            
            .hazelnut-status-processing {
                background: #dbeafe;
                color: #2563eb;
            }
            
            .hazelnut-status-archived {
                background: #e5e7eb;
                color: #6b7280;
            }
            
            .hazelnut-notice {
                margin: 20px 0;
                padding: 15px;
                border-radius: 6px;
                display: none;
            }
            
            .hazelnut-notice.success {
                background: #d1fae5;
                border-left: 4px solid #059669;
                color: #047857;
            }
            
            .hazelnut-notice.error {
                background: #fee2e2;
                border-left: 4px solid #dc2626;
                color: #991b1b;
            }
            
            #hazelnut-file-input {
                display: none;
            }
            
            .hazelnut-copy-btn {
                padding: 4px 8px;
                margin: 2px;
                font-size: 11px;
                background: #f3f4f6;
                border: 1px solid #d1d5db;
                border-radius: 3px;
                cursor: pointer;
                color: #374151;
            }
            
            .hazelnut-copy-btn:hover {
                background: #e5e7eb;
                border-color: #9ca3af;
            }
            
            .hazelnut-copy-btn.copied {
                background: #d1fae5;
                border-color: #059669;
                color: #047857;
            }
            
            /* Progress Bar Styles */
            .hazelnut-progress-wrapper {
                margin-top: 20px;
                padding: 20px;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
            }
            
            .hazelnut-progress-bar {
                width: 100%;
                height: 24px;
                background: #e5e7eb;
                border-radius: 12px;
                overflow: hidden;
                margin-bottom: 15px;
            }
            
            .hazelnut-progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #059669, #10b981);
                border-radius: 12px;
                width: 0%;
                transition: width 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .hazelnut-progress-fill::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, 0.3),
                    transparent
                );
                animation: shimmer 2s infinite;
            }
            
            @keyframes shimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
            
            .hazelnut-progress-status {
                font-size: 16px;
                font-weight: 600;
                color: #374151;
                margin-bottom: 10px;
            }
            
            .hazelnut-progress-details {
                font-size: 14px;
                color: #6b7280;
                line-height: 1.6;
            }
            
            .hazelnut-progress-details .progress-step {
                padding: 4px 0;
                display: flex;
                align-items: center;
            }
            
            .hazelnut-progress-details .progress-step::before {
                content: '▸';
                margin-right: 8px;
                color: #059669;
            }
            
            .hazelnut-progress-details .progress-step.completed::before {
                content: '✓';
                color: #059669;
            }
            
            .hazelnut-progress-details .progress-step.error::before {
                content: '✗';
                color: #dc2626;
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
                
                // Hazelnut functionality
                var selectedZipFile = null;
                
                // Handle ZIP file selection
                $('#hazelnut-select-zip').on('click', function() {
                    $('#hazelnut-file-input').click();
                });
                
                $('#hazelnut-file-input').on('change', function() {
                    var file = this.files[0];
                    if (file) {
                        selectedZipFile = file;
                        $('#hazelnut-selected-file').text(file.name);
                        $('#hazelnut-import-zip').show();
                    }
                });
                
                // Handle ZIP import
                $('#hazelnut-import-zip').on('click', function() {
                    if (!selectedZipFile) {
                        showNotice('Please select a ZIP file first', 'error');
                        return;
                    }
                    
                    var $button = $(this);
                    $button.prop('disabled', true).text('Importing...');
                    
                    var formData = new FormData();
                    formData.append('action', 'hazelnut_import_zip');
                    formData.append('zip_file', selectedZipFile);
                    formData.append('erase_duplicates', $('#hazelnut-erase-duplicates').is(':checked') ? '1' : '0');
                    formData.append('nonce', '<?php echo wp_create_nonce('hazelnut_import'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                showNotice('ZIP file imported successfully', 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                showNotice(response.data.message || 'Import failed', 'error');
                            }
                        },
                        error: function() {
                            showNotice('An error occurred during import', 'error');
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Import Selected ZIP');
                        }
                    });
                });
                
                // Handle local folder import
                $('#hazelnut-import-local').on('click', function() {
                    var folderName = $('#hazelnut-local-folder').val().trim();
                    if (!folderName) {
                        showNotice('Please enter a folder name', 'error');
                        return;
                    }
                    
                    var $button = $(this);
                    $button.prop('disabled', true).text('Processing...');
                    
                    // Show progress container
                    $('#hazelnut-progress-container').fadeIn();
                    $('#hazelnut-progress-details').empty();
                    
                    // Progress tracking
                    var steps = [
                        { percent: 10, status: 'Validating folder name...', detail: 'Checking: ' + folderName },
                        { percent: 25, status: 'Checking for existing imports...', detail: 'Looking for duplicates to remove' },
                        { percent: 40, status: 'Preparing source folder...', detail: 'Reading from local directory' },
                        { percent: 55, status: 'Copying files...', detail: 'Transferring to hazelnut-holdings' },
                        { percent: 70, status: 'Scanning for HTML files...', detail: 'Searching up to 2 levels deep' },
                        { percent: 85, status: 'Updating database...', detail: 'Creating new import record' },
                        { percent: 100, status: 'Import complete!', detail: 'Successfully imported ' + folderName }
                    ];
                    
                    var currentStep = 0;
                    
                    function updateProgress() {
                        if (currentStep < steps.length - 1) {
                            currentStep++;
                            var step = steps[currentStep];
                            
                            $('#hazelnut-progress-fill').css('width', step.percent + '%');
                            $('#hazelnut-progress-status').text(step.status);
                            
                            var $detail = $('<div class="progress-step">' + step.detail + '</div>');
                            $('#hazelnut-progress-details').append($detail);
                            
                            // Mark previous steps as completed
                            if (currentStep > 0) {
                                $('#hazelnut-progress-details .progress-step').eq(currentStep - 1).addClass('completed');
                            }
                            
                            // Simulate progress timing
                            if (currentStep < steps.length - 1) {
                                setTimeout(updateProgress, 500 + Math.random() * 500);
                            }
                        }
                    }
                    
                    // Start progress animation
                    $('#hazelnut-progress-fill').css('width', '10%');
                    $('#hazelnut-progress-status').text(steps[0].status);
                    $('#hazelnut-progress-details').append('<div class="progress-step">' + steps[0].detail + '</div>');
                    
                    setTimeout(updateProgress, 500);
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hazelnut_import_local',
                            folder_name: folderName,
                            erase_duplicates: $('#hazelnut-erase-duplicates-local').is(':checked') ? '1' : '0',
                            nonce: '<?php echo wp_create_nonce('hazelnut_import'); ?>'
                        },
                        success: function(response) {
                            // Complete all remaining steps
                            currentStep = steps.length - 1;
                            $('#hazelnut-progress-fill').css('width', '100%');
                            $('#hazelnut-progress-status').text(steps[currentStep].status);
                            $('#hazelnut-progress-details .progress-step').addClass('completed');
                            
                            if (response.success) {
                                showNotice('Local folder imported successfully', 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 2500);
                            } else {
                                $('#hazelnut-progress-status').text('Import failed');
                                $('#hazelnut-progress-details').append('<div class="progress-step error">' + (response.data.message || 'Import failed') + '</div>');
                                $('#hazelnut-progress-fill').css('background', 'linear-gradient(90deg, #dc2626, #ef4444)');
                                showNotice(response.data.message || 'Import failed', 'error');
                            }
                        },
                        error: function() {
                            $('#hazelnut-progress-status').text('Import error');
                            $('#hazelnut-progress-details').append('<div class="progress-step error">An error occurred during import</div>');
                            $('#hazelnut-progress-fill').css('background', 'linear-gradient(90deg, #dc2626, #ef4444)');
                            showNotice('An error occurred during import', 'error');
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Generate ZIP and Import (Phase 1)');
                            
                            // Hide progress after a delay if successful
                            if ($('#hazelnut-progress-fill').css('width') === '100%') {
                                setTimeout(function() {
                                    if (!$('#hazelnut-progress-details .error').length) {
                                        $('#hazelnut-progress-container').fadeOut();
                                    }
                                }, 5000);
                            }
                        }
                    });
                });
                
                function showNotice(message, type) {
                    var $notice = $('#hazelnut-notice');
                    $notice.removeClass('success error').addClass(type).html(message).fadeIn();
                    setTimeout(function() {
                        $notice.fadeOut();
                    }, 5000);
                }
                
                // Handle copy buttons
                $(document).on('click', '.hazelnut-copy-btn', function() {
                    var copyValue = $(this).data('copy-value');
                    var copyType = $(this).data('copy-type');
                    
                    // Create temporary textarea to copy from
                    var $temp = $('<textarea>');
                    $('body').append($temp);
                    $temp.val(copyValue).select();
                    document.execCommand('copy');
                    $temp.remove();
                    
                    // Show feedback
                    var originalText = $(this).text();
                    $(this).text('Copied!');
                    var $btn = $(this);
                    setTimeout(function() {
                        $btn.text(originalText);
                    }, 1500);
                });
                
                // Handle generate hardcoded references button
                $(document).on('click', '.hazelnut-generate-hardcoded', function() {
                    var $button = $(this);
                    var itemId = $(this).data('item-id');
                    
                    $button.prop('disabled', true).text('Generating...');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hazelnut_generate_hardcoded',
                            item_id: itemId,
                            nonce: '<?php echo wp_create_nonce('hazelnut_generate'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                showNotice('Hardcoded references file generated successfully', 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                showNotice(response.data.message || 'Generation failed', 'error');
                                $button.prop('disabled', false).text('Generate');
                            }
                        },
                        error: function() {
                            showNotice('An error occurred during generation', 'error');
                            $button.prop('disabled', false).text('Generate');
                        }
                    });
                });
                
                // Handle copy all HTML button
                $(document).on('click', '.hazelnut-copy-html-all', function() {
                    var $button = $(this);
                    var itemId = $(this).data('item-id');
                    var originalText = $button.text();
                    
                    $button.prop('disabled', true).text('Fetching...');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hazelnut_get_html_content',
                            item_id: itemId,
                            type: 'all',
                            nonce: '<?php echo wp_create_nonce('hazelnut_get_html'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Copy to clipboard
                                var $temp = $('<textarea>');
                                $('body').append($temp);
                                $temp.val(response.data.content).select();
                                document.execCommand('copy');
                                $temp.remove();
                                
                                $button.text('Copied!');
                                setTimeout(function() {
                                    $button.prop('disabled', false).text(originalText);
                                }, 2000);
                            } else {
                                showNotice(response.data.message || 'Failed to fetch HTML', 'error');
                                $button.prop('disabled', false).text(originalText);
                            }
                        },
                        error: function() {
                            showNotice('An error occurred', 'error');
                            $button.prop('disabled', false).text(originalText);
                        }
                    });
                });
                
                // Handle copy sanitized HTML button
                $(document).on('click', '.hazelnut-copy-html-sanitized', function() {
                    var $button = $(this);
                    var itemId = $(this).data('item-id');
                    var originalText = $button.text();
                    
                    $button.prop('disabled', true).text('Fetching...');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hazelnut_get_html_content',
                            item_id: itemId,
                            type: 'sanitized',
                            nonce: '<?php echo wp_create_nonce('hazelnut_get_html'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Copy to clipboard
                                var $temp = $('<textarea>');
                                $('body').append($temp);
                                $temp.val(response.data.content).select();
                                document.execCommand('copy');
                                $temp.remove();
                                
                                $button.text('Copied!');
                                setTimeout(function() {
                                    $button.prop('disabled', false).text(originalText);
                                }, 2000);
                            } else {
                                showNotice(response.data.message || 'Failed to fetch HTML', 'error');
                                $button.prop('disabled', false).text(originalText);
                            }
                        },
                        error: function() {
                            showNotice('An error occurred', 'error');
                            $button.prop('disabled', false).text(originalText);
                        }
                    });
                });
                
                // Handle Generate File3 button
                $(document).on('click', '.hazelnut-generate-file3', function() {
                    var $button = $(this);
                    var itemId = $(this).data('item-id');
                    var originalText = $button.text();
                    
                    $button.prop('disabled', true).text('Generating...');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hazelnut_generate_file3',
                            item_id: itemId,
                            nonce: '<?php echo wp_create_nonce('hazelnut_file3'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                showNotice('File3 generated successfully with dependencies extracted', 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                showNotice(response.data.message || 'Generation failed', 'error');
                                $button.prop('disabled', false).text(originalText);
                            }
                        },
                        error: function() {
                            showNotice('An error occurred during File3 generation', 'error');
                            $button.prop('disabled', false).text(originalText);
                        }
                    });
                });
                
                // Handle copy sanitized File3 button
                $(document).on('click', '.hazelnut-copy-file3-sanitized', function() {
                    var $button = $(this);
                    var itemId = $(this).data('item-id');
                    var originalText = $button.text();
                    
                    $button.prop('disabled', true).text('Fetching...');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hazelnut_get_file3_content',
                            item_id: itemId,
                            nonce: '<?php echo wp_create_nonce('hazelnut_get_file3'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Copy to clipboard
                                var $temp = $('<textarea>');
                                $('body').append($temp);
                                $temp.val(response.data.content).select();
                                document.execCommand('copy');
                                $temp.remove();
                                
                                $button.text('Copied!');
                                setTimeout(function() {
                                    $button.prop('disabled', false).text(originalText);
                                }, 2000);
                            } else {
                                showNotice(response.data.message || 'Failed to fetch content', 'error');
                                $button.prop('disabled', false).text(originalText);
                            }
                        },
                        error: function() {
                            showNotice('An error occurred', 'error');
                            $button.prop('disabled', false).text(originalText);
                        }
                    });
                });
                
                // Handle copy dependencies button
                $(document).on('click', '.hazelnut-copy-dependencies', function() {
                    var $button = $(this);
                    var itemId = $(this).data('item-id');
                    var originalText = $button.text();
                    
                    $button.prop('disabled', true).text('Copying...');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hazelnut_get_dependencies',
                            item_id: itemId,
                            nonce: '<?php echo wp_create_nonce('hazelnut_deps'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Copy to clipboard
                                var $temp = $('<textarea>');
                                $('body').append($temp);
                                $temp.val(response.data.dependencies).select();
                                document.execCommand('copy');
                                $temp.remove();
                                
                                $button.text('Copied!');
                                setTimeout(function() {
                                    $button.prop('disabled', false).text(originalText);
                                }, 2000);
                            } else {
                                showNotice(response.data.message || 'Failed to fetch dependencies', 'error');
                                $button.prop('disabled', false).text(originalText);
                            }
                        },
                        error: function() {
                            showNotice('An error occurred', 'error');
                            $button.prop('disabled', false).text(originalText);
                        }
                    });
                });
            });
        </script>
        
        <div class="wrap">
            <div class="hazelnut-items-wrap">
                <h1 class="hazelnut-items-title">Hazelnut Items Mar</h1>
                
                <div class="hazelnut-notice" id="hazelnut-notice"></div>
                
                <!-- Import Interface -->
                <div class="hazelnut-import-section">
                    <div class="hazelnut-import-row">
                        <div class="hazelnut-import-title">Import from ZIP File</div>
                        <div class="hazelnut-import-description">Upload a ZIP file containing your hazelnut project structure</div>
                        <input type="file" id="hazelnut-file-input" accept=".zip" />
                        <button class="hazelnut-btn hazelnut-btn-primary" id="hazelnut-select-zip">Select ZIP File</button>
                        <button class="hazelnut-btn hazelnut-btn-secondary" id="hazelnut-import-zip" style="display:none;">Import Selected ZIP</button>
                        <span id="hazelnut-selected-file" style="margin-left: 10px; color: #6b7280;"></span>
                        <div style="margin-top: 10px;">
                            <label style="display: inline-flex; align-items: center; color: #374151; font-size: 14px;">
                                <input type="checkbox" id="hazelnut-erase-duplicates" checked style="margin-right: 8px;" />
                                Erase imports if coming from duplicate folder name
                            </label>
                        </div>
                    </div>
                    
                    <?php if (strpos($_SERVER['HTTP_HOST'], '.local') !== false || $_SERVER['HTTP_HOST'] === 'localhost'): ?>
                    <div class="hazelnut-import-row">
                        <div class="hazelnut-import-title">Import from Local Folder</div>
                        <div class="hazelnut-import-description">Select a folder from your local peanut page outputs</div>
                        <div class="hazelnut-local-path">/Users/kylecampbell/Documents/repos/peanut_page_outputs/</div>
                        <input type="text" id="hazelnut-local-folder" placeholder="Enter folder name (e.g., 110 - chimney-exp_com)" style="width: 300px; padding: 8px; margin-right: 10px; border: 1px solid #d1d5db; border-radius: 4px;" />
                        <button class="hazelnut-btn hazelnut-btn-secondary" id="hazelnut-import-local">Generate ZIP and Import (Phase 1)</button>
                        <div style="margin-top: 10px;">
                            <label style="display: inline-flex; align-items: center; color: #374151; font-size: 14px;">
                                <input type="checkbox" id="hazelnut-erase-duplicates-local" checked style="margin-right: 8px;" />
                                Erase imports if coming from duplicate folder name
                            </label>
                        </div>
                        
                        <!-- Progress Bar Section -->
                        <div id="hazelnut-progress-container" style="display: none;">
                            <div class="hazelnut-progress-wrapper">
                                <div class="hazelnut-progress-bar">
                                    <div class="hazelnut-progress-fill" id="hazelnut-progress-fill"></div>
                                </div>
                                <div class="hazelnut-progress-status" id="hazelnut-progress-status">Initializing...</div>
                                <div class="hazelnut-progress-details" id="hazelnut-progress-details"></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Table Grid -->
                <div class="hazelnut-table-container">
                    <table class="hazelnut-items-table">
                        <thead>
                            <tr>
                                <th>item_id</th>
                                <th>rel_post_id</th>
                                <th>rel_post_status_of_implementation</th>
                                <th>upload_date</th>
                                <th>folder_name</th>
                                <th>original_zip_filename</th>
                                <th>upload_path</th>
                                <th>main_html_file</th>
                                <th>html_file_w_hardcoded_references</th>
                                <th>file3_w_hardcoded_refs_sanitized</th>
                                <th>file3_dependencies_html</th>
                                <th>file3_extracted_dependencies_json</th>
                                <th>file3_extracted_sanitization_metadata</th>
                                <th>total_files_count</th>
                                <th>total_size_bytes</th>
                                <th>asset_types</th>
                                <th>upload_status</th>
                                <th>error_message</th>
                                <th>source_local_path</th>
                                <th>has_been_imported</th>
                                <th>import_date</th>
                                <th>metadata_json</th>
                                <th>notes</th>
                                <th>is_active</th>
                                <th>created_at</th>
                                <th>updated_at</th>
                            </tr>
                        </thead>
                        <tbody id="hazelnut-table-body">
                            <?php
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'hazelnut_items';
                            
                            // Check if table exists
                            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                                $items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY item_id DESC");
                                
                                if ($items) {
                                    foreach ($items as $item) {
                                        echo '<tr>';
                                        echo '<td>' . esc_html($item->item_id) . '</td>';
                                        echo '<td>' . esc_html($item->rel_post_id ?: '') . '</td>';
                                        echo '<td>' . esc_html($item->rel_post_status_of_implementation ?: '') . '</td>';
                                        echo '<td>' . esc_html($item->upload_date) . '</td>';
                                        echo '<td>' . esc_html($item->folder_name) . '</td>';
                                        echo '<td>' . esc_html($item->original_zip_filename ?: '') . '</td>';
                                        echo '<td>' . esc_html($item->upload_path) . '</td>';
                                        echo '<td>';
                                        if ($item->main_html_file) {
                                            // Properly encode URL with spaces
                                            $path_with_file = $item->upload_path . $item->main_html_file;
                                            $encoded_path = str_replace(' ', '%20', $path_with_file);
                                            $full_url = home_url($encoded_path);
                                            
                                            echo '<div>' . esc_html($item->main_html_file) . '</div>';
                                            echo '<button class="hazelnut-copy-btn" data-copy-type="url" data-copy-value="' . esc_attr($full_url) . '">copy full url</button>';
                                            echo '<button class="hazelnut-copy-btn" data-copy-type="filename" data-copy-value="' . esc_attr($item->main_html_file) . '">copy file name</button>';
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        if ($item->html_file_w_hardcoded_references) {
                                            // Show the filename with copy buttons
                                            $path_with_hardcoded = $item->upload_path . $item->html_file_w_hardcoded_references;
                                            $encoded_hardcoded_path = str_replace(' ', '%20', $path_with_hardcoded);
                                            $hardcoded_url = home_url($encoded_hardcoded_path);
                                            
                                            echo '<div>' . esc_html($item->html_file_w_hardcoded_references) . '</div>';
                                            echo '<button class="hazelnut-copy-btn" data-copy-type="url" data-copy-value="' . esc_attr($hardcoded_url) . '">copy full url</button>';
                                            echo '<button class="hazelnut-copy-btn" data-copy-type="filename" data-copy-value="' . esc_attr($item->html_file_w_hardcoded_references) . '">copy file name</button>';
                                            echo '<button class="hazelnut-copy-html-all" data-item-id="' . esc_attr($item->item_id) . '" style="background-color: maroon; color: white; margin-top: 2px;">copy all html from file</button>';
                                            echo '<button class="hazelnut-copy-html-sanitized" data-item-id="' . esc_attr($item->item_id) . '" style="background-color: #ec4899; color: white; margin-top: 2px;">copy sanitized html</button>';
                                        } else if ($item->main_html_file) {
                                            // Show generate button if main_html_file exists but hardcoded version doesn't
                                            echo '<button class="hazelnut-generate-hardcoded" data-item-id="' . esc_attr($item->item_id) . '">Generate</button>';
                                        }
                                        echo '</td>';
                                        
                                        // file3_w_hardcoded_refs_sanitized column
                                        echo '<td>';
                                        if ($item->file3_w_hardcoded_refs_sanitized) {
                                            echo '<div style="font-size: 11px; color: #555;">' . esc_html(substr($item->file3_w_hardcoded_refs_sanitized, 0, 30)) . '...</div>';
                                            echo '<button class="hazelnut-copy-file3-sanitized" data-item-id="' . esc_attr($item->item_id) . '" style="background-color: #ec4899; color: white; font-size: 11px; padding: 2px 4px;">copy sanitized</button>';
                                        } else if ($item->html_file_w_hardcoded_references) {
                                            echo '<button class="hazelnut-generate-file3" data-item-id="' . esc_attr($item->item_id) . '" style="font-size: 11px;">Generate File3</button>';
                                        }
                                        echo '</td>';
                                        
                                        // file3_dependencies_html column
                                        echo '<td>';
                                        if ($item->file3_dependencies_html) {
                                            echo '<div style="font-size: 10px; color: #666; max-width: 150px; overflow: hidden; text-overflow: ellipsis;">' . esc_html(substr($item->file3_dependencies_html, 0, 50)) . '...</div>';
                                            echo '<button class="hazelnut-copy-dependencies" data-item-id="' . esc_attr($item->item_id) . '" style="background-color: #059669; color: white; font-size: 11px; padding: 2px 4px;">copy deps</button>';
                                        }
                                        echo '</td>';
                                        
                                        // file3_extracted_dependencies_json column
                                        echo '<td>';
                                        if ($item->file3_extracted_dependencies_json) {
                                            echo '<div style="font-size: 10px; color: #888; max-width: 100px; overflow: hidden; text-overflow: ellipsis;">' . esc_html(substr($item->file3_extracted_dependencies_json, 0, 40)) . '...</div>';
                                        }
                                        echo '</td>';
                                        
                                        // file3_extracted_sanitization_metadata column
                                        echo '<td>';
                                        if ($item->file3_extracted_sanitization_metadata) {
                                            $metadata = json_decode($item->file3_extracted_sanitization_metadata, true);
                                            if ($metadata && isset($metadata['removed_elements'])) {
                                                echo '<span style="font-size: 10px; color: #999;" title="' . esc_attr($item->file3_extracted_sanitization_metadata) . '">Removed: ' . count($metadata['removed_elements']) . ' elements</span>';
                                            } else {
                                                echo '<span style="font-size: 10px; color: #999;">Metadata stored</span>';
                                            }
                                        }
                                        echo '</td>';
                                        
                                        echo '<td>' . esc_html($item->total_files_count) . '</td>';
                                        echo '<td>' . esc_html($item->total_size_bytes) . '</td>';
                                        echo '<td>' . esc_html($item->asset_types ?: '') . '</td>';
                                        echo '<td><span class="hazelnut-status-badge hazelnut-status-' . esc_attr($item->upload_status) . '">' . esc_html($item->upload_status) . '</span></td>';
                                        echo '<td>' . esc_html($item->error_message ?: '') . '</td>';
                                        echo '<td>' . esc_html($item->source_local_path ?: '') . '</td>';
                                        echo '<td>' . ($item->has_been_imported ? 'Yes' : 'No') . '</td>';
                                        echo '<td>' . esc_html($item->import_date ?: '') . '</td>';
                                        echo '<td>' . esc_html(substr($item->metadata_json ?: '', 0, 50)) . ($item->metadata_json && strlen($item->metadata_json) > 50 ? '...' : '') . '</td>';
                                        echo '<td>' . esc_html($item->notes ?: '') . '</td>';
                                        echo '<td>' . ($item->is_active ? 'Yes' : 'No') . '</td>';
                                        echo '<td>' . esc_html($item->created_at) . '</td>';
                                        echo '<td>' . esc_html($item->updated_at) . '</td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="26" style="text-align: center; color: #6b7280;">No items found</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="26" style="text-align: center; color: #dc2626;">Table does not exist. Please create the wp_hazelnut_items table.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        // Only load on our admin page
        if ($hook !== 'snefuru_page_hazelnut_items_mar') {
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
        if (isset($_GET['page']) && $_GET['page'] === 'hazelnut_items_mar') {
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
     * AJAX handler for ZIP file import
     */
    public function ajax_import_zip() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hazelnut_import')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['zip_file'])) {
            wp_send_json_error(array('message' => 'No file uploaded'));
        }
        
        $uploaded_file = $_FILES['zip_file'];
        
        // Validate file type
        $file_type = wp_check_filetype($uploaded_file['name']);
        if ($file_type['ext'] !== 'zip') {
            wp_send_json_error(array('message' => 'Please upload a valid ZIP file'));
        }
        
        // Create upload directory
        $upload_dir = WP_CONTENT_DIR . '/hazelnut-holdings/';
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }
        
        // Extract folder name from zip filename (remove .zip extension)
        $folder_name = basename($uploaded_file['name'], '.zip');
        $extract_path = $upload_dir . $folder_name . '/';
        
        // Check if we should erase duplicates
        if (isset($_POST['erase_duplicates']) && $_POST['erase_duplicates'] === '1') {
            $this->handle_duplicate_cleanup($folder_name);
        }
        
        // Move uploaded file to temp location
        $temp_file = $upload_dir . 'temp_' . time() . '.zip';
        if (!move_uploaded_file($uploaded_file['tmp_name'], $temp_file)) {
            wp_send_json_error(array('message' => 'Failed to save uploaded file'));
        }
        
        // Extract ZIP
        $zip = new ZipArchive();
        if ($zip->open($temp_file) === TRUE) {
            $zip->extractTo($extract_path);
            $zip->close();
            
            // Delete temp file
            unlink($temp_file);
            
            // Log to database
            $this->log_hazelnut_upload(
                $folder_name,
                $uploaded_file['name'],
                '/wp-content/hazelnut-holdings/' . $folder_name . '/',
                'completed'
            );
            
            wp_send_json_success(array('message' => 'ZIP file imported successfully'));
        } else {
            unlink($temp_file);
            wp_send_json_error(array('message' => 'Failed to extract ZIP file'));
        }
    }
    
    /**
     * AJAX handler for local folder import
     */
    public function ajax_import_local() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hazelnut_import')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        // Check if on local environment
        if (strpos($_SERVER['HTTP_HOST'], '.local') === false && $_SERVER['HTTP_HOST'] !== 'localhost') {
            wp_send_json_error(array('message' => 'This feature is only available on local development environments'));
        }
        
        $folder_name = sanitize_text_field($_POST['folder_name']);
        if (empty($folder_name)) {
            wp_send_json_error(array('message' => 'Folder name is required'));
        }
        
        // Source and destination paths
        $source_path = '/Users/kylecampbell/Documents/repos/peanut_page_outputs/' . $folder_name;
        $dest_path = WP_CONTENT_DIR . '/hazelnut-holdings/' . $folder_name . '/';
        
        // Check if source exists
        if (!is_dir($source_path)) {
            wp_send_json_error(array('message' => 'Source folder not found: ' . $source_path));
        }
        
        // Check if we should erase duplicates
        if (isset($_POST['erase_duplicates']) && $_POST['erase_duplicates'] === '1') {
            $this->handle_duplicate_cleanup($folder_name);
        }
        
        // Create destination directory
        if (!file_exists(WP_CONTENT_DIR . '/hazelnut-holdings/')) {
            wp_mkdir_p(WP_CONTENT_DIR . '/hazelnut-holdings/');
        }
        
        // Copy directory recursively
        $this->copy_directory($source_path, $dest_path);
        
        // Log to database
        $this->log_hazelnut_upload(
            $folder_name,
            null,
            '/wp-content/hazelnut-holdings/' . $folder_name . '/',
            'completed',
            $source_path
        );
        
        wp_send_json_success(array('message' => 'Local folder imported successfully'));
    }
    
    /**
     * Copy directory recursively
     */
    private function copy_directory($source, $dest) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $src_path = $source . '/' . $file;
                $dst_path = $dest . '/' . $file;
                
                if (is_dir($src_path)) {
                    $this->copy_directory($src_path, $dst_path);
                } else {
                    copy($src_path, $dst_path);
                }
            }
        }
        closedir($dir);
    }
    
    /**
     * Delete directory recursively
     */
    private function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->delete_directory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    /**
     * Handle duplicate folder cleanup
     */
    private function handle_duplicate_cleanup($folder_name) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hazelnut_items';
        
        // Check if folder already exists in database
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE folder_name = %s",
            $folder_name
        ));
        
        if ($existing) {
            // Delete the existing folder
            $folder_path = WP_CONTENT_DIR . '/hazelnut-holdings/' . $folder_name;
            if (is_dir($folder_path)) {
                $this->delete_directory($folder_path);
            }
            
            // Delete the database entry
            $wpdb->delete($table_name, array('folder_name' => $folder_name));
        }
    }
    
    /**
     * Find main HTML file (checks root and 1-2 levels deep)
     */
    private function find_main_html_file($base_path) {
        // Check root level first
        $root_html_files = glob($base_path . '*.html');
        if (!empty($root_html_files)) {
            // Return relative path from base
            return basename($root_html_files[0]);
        }
        
        // Check first level subdirectories
        $subdirs = glob($base_path . '*', GLOB_ONLYDIR);
        foreach ($subdirs as $subdir) {
            $subdir_html_files = glob($subdir . '/*.html');
            if (!empty($subdir_html_files)) {
                // Return with subdirectory path
                $subdir_name = basename($subdir);
                $html_file = basename($subdir_html_files[0]);
                return $subdir_name . '/' . $html_file;
            }
            
            // Check second level subdirectories
            $second_level_dirs = glob($subdir . '/*', GLOB_ONLYDIR);
            foreach ($second_level_dirs as $second_dir) {
                $second_html_files = glob($second_dir . '/*.html');
                if (!empty($second_html_files)) {
                    // Return with full relative path
                    $first_dir = basename($subdir);
                    $second_dir_name = basename($second_dir);
                    $html_file = basename($second_html_files[0]);
                    return $first_dir . '/' . $second_dir_name . '/' . $html_file;
                }
            }
        }
        
        return null; // No HTML file found within 2 levels
    }
    
    /**
     * Log hazelnut upload to database
     */
    private function log_hazelnut_upload($folder_name, $zip_filename = null, $upload_path = '', $status = 'completed', $source_path = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hazelnut_items';
        
        // Full path to the uploaded content
        $full_path = WP_CONTENT_DIR . '/hazelnut-holdings/' . $folder_name . '/';
        
        // Count files and calculate size
        $file_count = 0;
        $total_size = 0;
        
        if (is_dir($full_path)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $file_count++;
                    $total_size += $file->getSize();
                }
            }
        }
        
        // Find main HTML file (1-2 levels deep)
        $main_html = $this->find_main_html_file($full_path);
        
        $data = array(
            'upload_date' => current_time('mysql'),
            'folder_name' => $folder_name,
            'original_zip_filename' => $zip_filename,
            'upload_path' => $upload_path,
            'main_html_file' => $main_html,
            'total_files_count' => $file_count,
            'total_size_bytes' => $total_size,
            'upload_status' => $status,
            'source_local_path' => $source_path,
            'is_active' => 1,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $wpdb->insert($table_name, $data);
    }
    
    /**
     * AJAX handler to generate hardcoded references version of HTML file
     */
    public function ajax_generate_hardcoded_refs() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hazelnut_generate')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $item_id = intval($_POST['item_id']);
        if (!$item_id) {
            wp_send_json_error(array('message' => 'Invalid item ID'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hazelnut_items';
        
        // Get the item from database
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE item_id = %d",
            $item_id
        ));
        
        if (!$item || !$item->main_html_file) {
            wp_send_json_error(array('message' => 'Item not found or no HTML file'));
            return;
        }
        
        // Build full path to the original HTML file
        $base_path = WP_CONTENT_DIR . '/hazelnut-holdings/' . $item->folder_name . '/';
        $original_file_path = $base_path . $item->main_html_file;
        
        if (!file_exists($original_file_path)) {
            wp_send_json_error(array('message' => 'Original HTML file not found'));
            return;
        }
        
        // Read the original HTML content
        $html_content = file_get_contents($original_file_path);
        if ($html_content === false) {
            wp_send_json_error(array('message' => 'Could not read HTML file'));
            return;
        }
        
        // Transform relative references to hardcoded absolute references
        $transformed_html = $this->transform_references_to_absolute($html_content, $item->upload_path, $item->main_html_file);
        
        // Generate new filename
        $path_info = pathinfo($item->main_html_file);
        $new_filename = $path_info['dirname'] . '/' . $path_info['filename'] . '_hardcoded_refs.html';
        // Clean up double slashes or leading slash
        $new_filename = ltrim(str_replace('//', '/', $new_filename), '/');
        
        // Write the new file
        $new_file_path = $base_path . $new_filename;
        
        // Create directory if needed
        $new_file_dir = dirname($new_file_path);
        if (!is_dir($new_file_dir)) {
            wp_mkdir_p($new_file_dir);
        }
        
        if (file_put_contents($new_file_path, $transformed_html) === false) {
            wp_send_json_error(array('message' => 'Could not write new HTML file'));
            return;
        }
        
        // Update database with new filename
        $update_result = $wpdb->update(
            $table_name,
            array(
                'html_file_w_hardcoded_references' => $new_filename,
                'updated_at' => current_time('mysql')
            ),
            array('item_id' => $item_id)
        );
        
        if ($update_result === false) {
            wp_send_json_error(array('message' => 'Could not update database'));
            return;
        }
        
        wp_send_json_success(array(
            'message' => 'Hardcoded references file generated successfully',
            'new_filename' => $new_filename
        ));
    }
    
    /**
     * Transform relative references in HTML to absolute paths
     */
    private function transform_references_to_absolute($html, $upload_path, $main_html_file) {
        // Get the directory of the HTML file relative to upload path
        $html_dir = dirname($main_html_file);
        if ($html_dir === '.') {
            $html_dir = '';
        } else {
            $html_dir = $html_dir . '/';
        }
        
        // Base path for all assets (without trailing slash to avoid double slashes)
        $base_url = rtrim($upload_path, '/');
        
        // Pattern replacements for different types of references
        $patterns = array(
            // src attributes (images, scripts)
            '/src=["\'](?!http|https|\/\/|data:|#)([^"\']*)["\']/',
            // href attributes (stylesheets, links)
            '/href=["\'](?!http|https|\/\/|data:|#|mailto:|tel:)([^"\']*)["\']/',
            // url() in inline styles and style tags
            '/url\(["\']?(?!http|https|\/\/|data:)([^"\']*)["\']?\)/',
            // srcset for responsive images (more complex handling)
            '/srcset=["\']([^"\']*)["\']/'
        );
        
        // Process each pattern
        foreach ($patterns as $index => $pattern) {
            if ($index < 3) { // Regular patterns
                $html = preg_replace_callback($pattern, function($matches) use ($base_url, $html_dir) {
                    $original = $matches[0];
                    $path = $matches[1];
                    
                    // Skip empty paths or anchors
                    if (empty($path) || strpos($path, '#') === 0) {
                        return $original;
                    }
                    
                    // Calculate absolute path
                    if (strpos($path, '../') === 0) {
                        // Go up one directory from HTML location
                        $absolute_path = $base_url . '/' . substr($path, 3);
                    } elseif (strpos($path, '/') === 0) {
                        // Already absolute from site root, prepend just the hazelnut path
                        $absolute_path = $path;
                    } else {
                        // Relative to HTML file location
                        $absolute_path = $base_url . '/' . $html_dir . $path;
                    }
                    
                    // Clean up double slashes
                    $absolute_path = preg_replace('#/+#', '/', $absolute_path);
                    
                    // Reconstruct the attribute
                    if (strpos($original, 'src=') === 0) {
                        return 'src="' . $absolute_path . '"';
                    } elseif (strpos($original, 'href=') === 0) {
                        return 'href="' . $absolute_path . '"';
                    } elseif (strpos($original, 'url(') === 0) {
                        return 'url(' . $absolute_path . ')';
                    }
                    
                    return $original;
                }, $html);
            } else { // srcset special handling
                $html = preg_replace_callback($pattern, function($matches) use ($base_url, $html_dir) {
                    $srcset = $matches[1];
                    $sources = explode(',', $srcset);
                    $new_sources = array();
                    
                    foreach ($sources as $source) {
                        $source = trim($source);
                        if (preg_match('/^([^\s]+)(\s+.*)?$/', $source, $src_match)) {
                            $path = $src_match[1];
                            $descriptor = isset($src_match[2]) ? $src_match[2] : '';
                            
                            // Skip if already absolute
                            if (preg_match('#^(https?://|//)#', $path)) {
                                $new_sources[] = $source;
                                continue;
                            }
                            
                            // Calculate absolute path
                            if (strpos($path, '../') === 0) {
                                $absolute_path = $base_url . '/' . substr($path, 3);
                            } elseif (strpos($path, '/') === 0) {
                                $absolute_path = $path;
                            } else {
                                $absolute_path = $base_url . '/' . $html_dir . $path;
                            }
                            
                            $absolute_path = preg_replace('#/+#', '/', $absolute_path);
                            $new_sources[] = $absolute_path . $descriptor;
                        } else {
                            $new_sources[] = $source;
                        }
                    }
                    
                    return 'srcset="' . implode(', ', $new_sources) . '"';
                }, $html);
            }
        }
        
        return $html;
    }
    
    /**
     * AJAX handler to get HTML content (all or sanitized)
     */
    public function ajax_get_html_content() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hazelnut_get_html')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $item_id = intval($_POST['item_id']);
        $type = sanitize_text_field($_POST['type']);
        
        if (!$item_id) {
            wp_send_json_error(array('message' => 'Invalid item ID'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hazelnut_items';
        
        // Get the item from database
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE item_id = %d",
            $item_id
        ));
        
        if (!$item || !$item->html_file_w_hardcoded_references) {
            wp_send_json_error(array('message' => 'Item not found or no hardcoded HTML file'));
            return;
        }
        
        // Build full path to the hardcoded HTML file
        $base_path = WP_CONTENT_DIR . '/hazelnut-holdings/' . $item->folder_name . '/';
        $file_path = $base_path . $item->html_file_w_hardcoded_references;
        
        if (!file_exists($file_path)) {
            wp_send_json_error(array('message' => 'HTML file not found'));
            return;
        }
        
        // Read the HTML content
        $html_content = file_get_contents($file_path);
        if ($html_content === false) {
            wp_send_json_error(array('message' => 'Could not read HTML file'));
            return;
        }
        
        // Return all or sanitized based on type
        if ($type === 'all') {
            wp_send_json_success(array('content' => $html_content));
        } else if ($type === 'sanitized') {
            $sanitized = $this->sanitize_html_for_cashew($html_content);
            wp_send_json_success(array('content' => $sanitized));
        } else {
            wp_send_json_error(array('message' => 'Invalid type'));
        }
    }
    
    /**
     * Sanitize HTML for use in cashew_html_expanse column
     * Removes DOCTYPE, html, head, body tags and extracts main content
     */
    private function sanitize_html_for_cashew($html) {
        // Remove HTML comments
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        
        // Extract body content if it exists
        if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $html, $matches)) {
            $html = $matches[1];
        }
        
        // Remove script tags and their content
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
        
        // Remove style tags and their content  
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $html);
        
        // Remove link tags (usually in head but sometimes in body)
        $html = preg_replace('/<link\b[^>]*>/i', '', $html);
        
        // Remove meta tags
        $html = preg_replace('/<meta\b[^>]*>/i', '', $html);
        
        // Remove title tag
        $html = preg_replace('/<title\b[^>]*>.*?<\/title>/si', '', $html);
        
        // Remove any remaining DOCTYPE declaration
        $html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html);
        
        // Remove html tag if somehow still present
        $html = preg_replace('/<\/?html[^>]*>/i', '', $html);
        
        // Remove head tag and content if somehow still present
        $html = preg_replace('/<head\b[^>]*>.*?<\/head>/si', '', $html);
        
        // Remove body tags but keep content
        $html = preg_replace('/<\/?body[^>]*>/i', '', $html);
        
        // Clean up common wrapper elements that might be around main content
        // Look for main content containers
        if (preg_match('/<(main|article|section|div)\s+(?:id|class)=["\'](?:main|content|main-content|page-content|site-content|primary)["\'][^>]*>(.*?)<\/\1>/si', $html, $matches)) {
            $html = $matches[2];
        }
        
        // Remove common header/footer/nav elements
        $html = preg_replace('/<header\b[^>]*>.*?<\/header>/si', '', $html);
        $html = preg_replace('/<footer\b[^>]*>.*?<\/footer>/si', '', $html);
        $html = preg_replace('/<nav\b[^>]*>.*?<\/nav>/si', '', $html);
        $html = preg_replace('/<aside\b[^>]*>.*?<\/aside>/si', '', $html);
        
        // Clean up extra whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Trim the result
        $html = trim($html);
        
        return $html;
    }
    
    /**
     * AJAX handler to generate File3 (sanitized version with extracted dependencies)
     */
    public function ajax_generate_file3() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hazelnut_file3')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $item_id = intval($_POST['item_id']);
        if (!$item_id) {
            wp_send_json_error(array('message' => 'Invalid item ID'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hazelnut_items';
        
        // Get the item from database
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE item_id = %d",
            $item_id
        ));
        
        if (!$item || !$item->html_file_w_hardcoded_references) {
            wp_send_json_error(array('message' => 'Item not found or no hardcoded HTML file'));
            return;
        }
        
        // Build full path to the hardcoded HTML file
        $base_path = WP_CONTENT_DIR . '/hazelnut-holdings/' . $item->folder_name . '/';
        $hardcoded_file_path = $base_path . $item->html_file_w_hardcoded_references;
        
        if (!file_exists($hardcoded_file_path)) {
            wp_send_json_error(array('message' => 'Hardcoded HTML file not found'));
            return;
        }
        
        // Read the hardcoded HTML content
        $html_content = file_get_contents($hardcoded_file_path);
        if ($html_content === false) {
            wp_send_json_error(array('message' => 'Could not read HTML file'));
            return;
        }
        
        // Extract dependencies and sanitize HTML
        $extraction_result = $this->extract_dependencies_and_sanitize($html_content);
        
        // Generate new filename with _sanitized suffix
        $path_info = pathinfo($item->html_file_w_hardcoded_references);
        // Remove _hardcoded_refs from filename and add _sanitized
        $base_name = str_replace('_hardcoded_refs', '', $path_info['filename']);
        $new_filename = $path_info['dirname'] . '/' . $base_name . '_sanitized.html';
        $new_filename = ltrim(str_replace('//', '/', $new_filename), '/');
        
        // Write the sanitized file
        $new_file_path = $base_path . $new_filename;
        
        // Create directory if needed
        $new_file_dir = dirname($new_file_path);
        if (!is_dir($new_file_dir)) {
            wp_mkdir_p($new_file_dir);
        }
        
        if (file_put_contents($new_file_path, $extraction_result['sanitized_html']) === false) {
            wp_send_json_error(array('message' => 'Could not write sanitized HTML file'));
            return;
        }
        
        // Update database with all file3 data
        $update_result = $wpdb->update(
            $table_name,
            array(
                'file3_w_hardcoded_refs_sanitized' => $new_filename,
                'file3_dependencies_html' => $extraction_result['dependencies_html'],
                'file3_extracted_dependencies_json' => json_encode($extraction_result['dependencies_json']),
                'file3_extracted_sanitization_metadata' => json_encode($extraction_result['metadata']),
                'updated_at' => current_time('mysql')
            ),
            array('item_id' => $item_id)
        );
        
        if ($update_result === false) {
            wp_send_json_error(array('message' => 'Could not update database'));
            return;
        }
        
        wp_send_json_success(array(
            'message' => 'File3 generated successfully',
            'filename' => $new_filename
        ));
    }
    
    /**
     * Extract dependencies and sanitize HTML for File3
     */
    private function extract_dependencies_and_sanitize($html) {
        $dependencies = array(
            'stylesheets' => array(),
            'scripts' => array(),
            'inline_styles' => array(),
            'inline_scripts' => array()
        );
        
        $removed_elements = array();
        
        // Extract stylesheets
        if (preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[0] as $link_tag) {
                if (preg_match('/href=["\']([^"\']+)["\']/', $link_tag, $href_match)) {
                    $dependencies['stylesheets'][] = $href_match[1];
                }
            }
        }
        
        // Extract scripts
        if (preg_match_all('/<script[^>]*src=["\']([^"\']+)["\'][^>]*><\/script>/i', $html, $matches)) {
            foreach ($matches[1] as $script_src) {
                $dependencies['scripts'][] = $script_src;
            }
        }
        
        // Extract inline styles
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/si', $html, $matches)) {
            foreach ($matches[1] as $style_content) {
                $trimmed = trim($style_content);
                if (!empty($trimmed)) {
                    $dependencies['inline_styles'][] = $trimmed;
                }
            }
        }
        
        // Extract inline scripts (non-src scripts)
        if (preg_match_all('/<script(?![^>]*src=)[^>]*>(.*?)<\/script>/si', $html, $matches)) {
            foreach ($matches[1] as $script_content) {
                $trimmed = trim($script_content);
                if (!empty($trimmed)) {
                    $dependencies['inline_scripts'][] = $trimmed;
                }
            }
        }
        
        // Now sanitize the HTML
        $sanitized = $html;
        
        // Remove DOCTYPE
        $sanitized = preg_replace('/<!DOCTYPE[^>]*>/i', '', $sanitized);
        $removed_elements[] = 'DOCTYPE';
        
        // Remove HTML comments
        $sanitized = preg_replace('/<!--.*?-->/s', '', $sanitized);
        
        // Extract body content if exists
        if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $sanitized, $matches)) {
            $sanitized = $matches[1];
            $removed_elements[] = 'body wrapper';
        }
        
        // Remove all script tags
        $sanitized = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $sanitized);
        $removed_elements[] = 'script tags';
        
        // Remove all style tags
        $sanitized = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $sanitized);
        $removed_elements[] = 'style tags';
        
        // Remove link tags
        $sanitized = preg_replace('/<link\b[^>]*>/i', '', $sanitized);
        $removed_elements[] = 'link tags';
        
        // Remove meta tags
        $sanitized = preg_replace('/<meta\b[^>]*>/i', '', $sanitized);
        $removed_elements[] = 'meta tags';
        
        // Remove title tag
        $sanitized = preg_replace('/<title\b[^>]*>.*?<\/title>/si', '', $sanitized);
        $removed_elements[] = 'title tag';
        
        // Remove html tag
        $sanitized = preg_replace('/<\/?html[^>]*>/i', '', $sanitized);
        $removed_elements[] = 'html tag';
        
        // Remove head tag and content
        $sanitized = preg_replace('/<head\b[^>]*>.*?<\/head>/si', '', $sanitized);
        $removed_elements[] = 'head section';
        
        // Remove common layout elements
        $sanitized = preg_replace('/<header\b[^>]*>.*?<\/header>/si', '', $sanitized);
        $sanitized = preg_replace('/<footer\b[^>]*>.*?<\/footer>/si', '', $sanitized);
        $sanitized = preg_replace('/<nav\b[^>]*>.*?<\/nav>/si', '', $sanitized);
        $sanitized = preg_replace('/<aside\b[^>]*>.*?<\/aside>/si', '', $sanitized);
        $removed_elements = array_merge($removed_elements, ['header', 'footer', 'nav', 'aside']);
        
        // Try to extract main content area
        $preserved_classes = array();
        if (preg_match('/<(main|article|section|div)\s+(?:id|class)=["\'](?:main|content|main-content|page-content|site-content|primary)["\'][^>]*>(.*?)<\/\1>/si', $sanitized, $matches)) {
            $sanitized = $matches[2];
            $preserved_classes[] = 'main content area';
        }
        
        // Clean up whitespace
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        $sanitized = preg_replace('/>\s+</', '><', $sanitized);
        $sanitized = trim($sanitized);
        
        // Build dependencies HTML
        $deps_html = '';
        
        if (!empty($dependencies['stylesheets'])) {
            $deps_html .= "<!-- CSS Dependencies -->\n";
            foreach ($dependencies['stylesheets'] as $stylesheet) {
                $deps_html .= '<link rel="stylesheet" href="' . esc_attr($stylesheet) . '">' . "\n";
            }
        }
        
        if (!empty($dependencies['inline_styles'])) {
            $deps_html .= "\n<!-- Inline Styles -->\n";
            foreach ($dependencies['inline_styles'] as $style) {
                $deps_html .= "<style>\n" . $style . "\n</style>\n";
            }
        }
        
        if (!empty($dependencies['scripts'])) {
            $deps_html .= "\n<!-- JS Dependencies -->\n";
            foreach ($dependencies['scripts'] as $script) {
                $deps_html .= '<script src="' . esc_attr($script) . '"></script>' . "\n";
            }
        }
        
        if (!empty($dependencies['inline_scripts'])) {
            $deps_html .= "\n<!-- Inline Scripts -->\n";
            foreach ($dependencies['inline_scripts'] as $script) {
                $deps_html .= "<script>\n" . $script . "\n</script>\n";
            }
        }
        
        // Build metadata
        $metadata = array(
            'removed_elements' => $removed_elements,
            'preserved_classes' => $preserved_classes,
            'sanitized_date' => current_time('mysql'),
            'dependency_counts' => array(
                'stylesheets' => count($dependencies['stylesheets']),
                'scripts' => count($dependencies['scripts']),
                'inline_styles' => count($dependencies['inline_styles']),
                'inline_scripts' => count($dependencies['inline_scripts'])
            )
        );
        
        return array(
            'sanitized_html' => $sanitized,
            'dependencies_html' => $deps_html,
            'dependencies_json' => $dependencies,
            'metadata' => $metadata
        );
    }
    
    /**
     * AJAX handler to get File3 sanitized content
     */
    public function ajax_get_file3_content() {
        if (!wp_verify_nonce($_POST['nonce'], 'hazelnut_get_file3')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $item_id = intval($_POST['item_id']);
        if (!$item_id) {
            wp_send_json_error(array('message' => 'Invalid item ID'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hazelnut_items';
        
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE item_id = %d",
            $item_id
        ));
        
        if (!$item || !$item->file3_w_hardcoded_refs_sanitized) {
            wp_send_json_error(array('message' => 'File3 not found'));
            return;
        }
        
        $base_path = WP_CONTENT_DIR . '/hazelnut-holdings/' . $item->folder_name . '/';
        $file_path = $base_path . $item->file3_w_hardcoded_refs_sanitized;
        
        if (!file_exists($file_path)) {
            wp_send_json_error(array('message' => 'File3 file not found'));
            return;
        }
        
        $content = file_get_contents($file_path);
        if ($content === false) {
            wp_send_json_error(array('message' => 'Could not read File3'));
            return;
        }
        
        wp_send_json_success(array('content' => $content));
    }
    
    /**
     * AJAX handler to get dependencies HTML
     */
    public function ajax_get_dependencies() {
        if (!wp_verify_nonce($_POST['nonce'], 'hazelnut_deps')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $item_id = intval($_POST['item_id']);
        if (!$item_id) {
            wp_send_json_error(array('message' => 'Invalid item ID'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hazelnut_items';
        
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT file3_dependencies_html FROM $table_name WHERE item_id = %d",
            $item_id
        ));
        
        if (!$item || !$item->file3_dependencies_html) {
            wp_send_json_error(array('message' => 'Dependencies not found'));
            return;
        }
        
        wp_send_json_success(array('dependencies' => $item->file3_dependencies_html));
    }
}

// Initialize the Hazelnut Items Admin
new Hazelnut_Items_Admin();