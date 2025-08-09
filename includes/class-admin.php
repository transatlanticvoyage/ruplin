<?php

class Snefuru_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_snefuru_test_connection', array($this, 'test_api_connection'));
        add_action('wp_ajax_snefuru_sync_now', array($this, 'manual_sync'));
        add_action('wp_ajax_snefuru_clear_upload_logs', array($this, 'clear_upload_logs'));
        add_action('wp_ajax_snefuru_test_upload_endpoint', array($this, 'test_upload_endpoint'));
        add_action('wp_ajax_fetch_site_data', array($this, 'fetch_site_data'));
        add_action('wp_ajax_sync_data_now', array($this, 'sync_data_now'));
        add_action('wp_ajax_clear_logs', array($this, 'clear_logs'));
        add_action('wp_ajax_snefuru_save_api_key', array($this, 'save_api_key'));
        add_action('wp_ajax_snefuru_clear_update_debug', array($this, 'clear_update_debug'));
        add_action('wp_ajax_snefuru_rebuild_zen_tables', array($this, 'rebuild_zen_tables'));
        
        // Locations management AJAX actions
        add_action('wp_ajax_rup_locations_get_data', array($this, 'rup_locations_get_data'));
        add_action('wp_ajax_rup_locations_update_field', array($this, 'rup_locations_update_field'));
        add_action('wp_ajax_rup_locations_create', array($this, 'rup_locations_create'));
        add_action('wp_ajax_rup_locations_get_image_url', array($this, 'rup_locations_get_image_url'));
        
        // Services management AJAX actions
        add_action('wp_ajax_rup_services_get_data', array($this, 'rup_services_get_data'));
        add_action('wp_ajax_rup_services_update_field', array($this, 'rup_services_update_field'));
        add_action('wp_ajax_rup_services_create', array($this, 'rup_services_create'));
        add_action('wp_ajax_rup_services_get_image_url', array($this, 'rup_services_get_image_url'));
        
        // Add Elementor data viewer
        add_action('add_meta_boxes', array($this, 'add_elementor_data_metabox'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_menu_page(
            'Snefuruplin Dashboard',
            'Snefuruplin',
            'manage_options',
            'snefuru',
            array($this, 'admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'),
            30
        );
        
        // Add KenliSidebarLinks as a non-clickable label
        add_submenu_page(
            'snefuru',
            'KenliSidebarLinks',
            'KenliSidebarLinks',
            'manage_options',
            'snefuru-kenli-sidebar-links',
            array($this, 'kenli_sidebar_links_page')
        );
        
        add_submenu_page(
            'snefuru',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'snefuru',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'snefuru',
            'Settings',
            'Settings',
            'manage_options',
            'snefuru-settings',
            array($this, 'settings_page')
        );
        
        
        add_submenu_page(
            'snefuru',
            'Logs',
            'Logs',
            'manage_options',
            'snefuru-logs',
            array($this, 'logs_page')
        );
        
        add_submenu_page(
            'snefuru',
            'Dublish Logs',
            'Dublish Logs',
            'manage_options',
            'snefuru-dublish-logs',
            array($this, 'dublish_logs_page')
        );
        
        add_submenu_page(
            'snefuru',
            'screen 4 - manage',
            'screen 4 - manage',
            'manage_options',
            'snefuru-screen4-manage',
            array($this, 'screen4_manage_page')
        );
        
        add_submenu_page(
            'snefuru',
            'Bespoke CSS Editor',
            'CSS Editor',
            'manage_options',
            'snefuruplin-bespoke-css-1',
            array($this, 'bespoke_css_editor_page')
        );
        
        // New Ruplin pages
        add_submenu_page(
            'snefuru',
            'rup_locations_mar',
            'rup_locations_mar',
            'manage_options',
            'rup_locations_mar',
            array($this, 'rup_locations_mar_page')
        );
        
        add_submenu_page(
            'snefuru',
            'rup_services_mar',
            'rup_services_mar',
            'manage_options',
            'rup_services_mar',
            array($this, 'rup_services_mar_page')
        );
        
        add_submenu_page(
            'snefuru',
            'rup_service_tags_mar',
            'rup_service_tags_mar',
            'manage_options',
            'rup_service_tags_mar',
            array($this, 'rup_service_tags_mar_page')
        );
        
        add_submenu_page(
            'snefuru',
            'rup_location_tags_mar',
            'rup_location_tags_mar',
            'manage_options',
            'rup_location_tags_mar',
            array($this, 'rup_location_tags_mar_page')
        );
        
        add_submenu_page(
            'snefuru',
            'rup_horse_class_page',
            'rup_horse_class_page',
            'manage_options',
            'rup_horse_class_page',
            array($this, 'rup_horse_class_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('snefuru_settings', 'snefuru_ruplin_api_key_1');
        register_setting('snefuru_settings', 'snefuru_api_url');
        register_setting('snefuru_settings', 'snefuru_sync_interval');
        register_setting('snefuru_settings', 'snefuru_auto_sync');
        register_setting('snefuru_settings', 'snefuru_upload_enabled');
        register_setting('snefuru_settings', 'snefuru_upload_max_size');
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'snefuru') === false) {
            return;
        }
        
        wp_enqueue_style('snefuru-admin', SNEFURU_PLUGIN_URL . 'assets/css/admin.css', array(), SNEFURU_PLUGIN_VERSION);
        wp_enqueue_script('snefuru-admin', SNEFURU_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SNEFURU_PLUGIN_VERSION, true);
        
        wp_localize_script('snefuru-admin', 'snefuru_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('snefuru_nonce')
        ));
    }
    
    /**
     * KenliSidebarLinks placeholder page (non-functional)
     */
    public function kenli_sidebar_links_page() {
        echo '<div class="wrap">';
        echo '<h1>KenliSidebarLinks</h1>';
        echo '<p>This is a placeholder menu item.</p>';
        echo '</div>';
    }
    
    /**
     * Main admin dashboard page
     */
    public function admin_page() {
        $api_client = new Snefuru_API_Client();
        $data_collector = new Snefuru_Data_Collector();
        
        // Get recent logs
        global $wpdb;
        $table_name = $wpdb->prefix . 'snefuru_logs';
        $recent_logs = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY timestamp DESC LIMIT 10");
        
        // Get basic site stats
        $site_data = $data_collector->get_site_info();
        $performance_data = $data_collector->collect_specific_data('performance');
        
        ?>
        <div class="wrap">
            <h1>Snefuruplin Dashboard</h1>
            
            <div class="snefuru-dashboard">
                <div class="snefuru-cards">
                    <div class="snefuru-card">
                        <h3>Connection Status</h3>
                        <div id="connection-status">
                            <?php $this->display_connection_status(); ?>
                        </div>
                        <button type="button" class="button button-secondary" id="test-connection">Test Connection</button>
                    </div>
                    
                    <div class="snefuru-card">
                        <h3>Site Information</h3>
                        <p><strong>Site URL:</strong> <?php echo esc_html($site_data['url']); ?></p>
                        <p><strong>WordPress Version:</strong> <?php echo esc_html($site_data['wp_version']); ?></p>
                        <p><strong>Last Sync:</strong> <?php echo esc_html(get_option('snefuru_last_sync', 'Never')); ?></p>
                    </div>
                    
                    <div class="snefuru-card">
                        <h3>Performance Overview</h3>
                        <p><strong>Memory Usage:</strong> <?php echo esc_html(size_format($performance_data['memory_usage'])); ?></p>
                        <p><strong>Active Plugins:</strong> <?php echo esc_html($performance_data['active_plugins_count']); ?></p>
                        <p><strong>Database Size:</strong> <?php echo esc_html($performance_data['database_size']); ?></p>
                    </div>
                    
                    <div class="snefuru-card">
                        <h3>Quick Actions</h3>
                        <button type="button" class="button button-primary" id="sync-now">Sync Now</button>
                        <button type="button" class="button button-secondary" onclick="location.href='<?php echo admin_url('admin.php?page=snefuru-settings'); ?>'">Settings</button>
                        <button type="button" class="button button-secondary" onclick="location.href='<?php echo admin_url('admin.php?page=snefuru-logs'); ?>'">View Logs</button>
                    </div>
                </div>
                
                <div class="snefuru-recent-activity">
                    <h3>Recent Activity</h3>
                    <div class="snefuru-logs-preview">
                        <?php if ($recent_logs): ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_logs as $log): ?>
                                        <tr>
                                            <td><?php echo esc_html(mysql2date('M j, Y g:i A', $log->timestamp)); ?></td>
                                            <td><?php echo esc_html($log->action); ?></td>
                                            <td><span class="status-<?php echo esc_attr($log->status); ?>"><?php echo esc_html($log->status); ?></span></td>
                                            <td><?php echo esc_html($log->data); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No activity logs found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // Handle upload settings form submission
        if (isset($_POST['submit_upload_settings'])) {
            check_admin_referer('snefuru_upload_settings_nonce');
            
            update_option('snefuru_upload_enabled', isset($_POST['snefuru_upload_enabled']) ? 1 : 0);
            update_option('snefuru_upload_max_size', sanitize_text_field($_POST['snefuru_upload_max_size']));
            
            echo '<div class="notice notice-success"><p>Upload settings saved!</p></div>';
        }
        
        $ruplin_api_key = get_option('snefuru_ruplin_api_key_1', '');
        $api_url = get_option('snefuru_api_url', 'https://your-app.vercel.app/api');
        $upload_enabled = get_option('snefuru_upload_enabled', 1);
        $upload_max_size = get_option('snefuru_upload_max_size', '10MB');
        
        ?>
        <div class="wrap">
            <h1>Snefuruplin Settings</h1>
            
            <div class="snefuru-card">
                <h3>Ruplin API Configuration</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">Ruplin API Key</th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" id="ruplin-api-key" value="<?php echo esc_attr($ruplin_api_key); ?>" class="regular-text" readonly data-full-key="<?php echo esc_attr($ruplin_api_key); ?>" />
                                <div id="api-key-view-buttons" style="display: flex; gap: 10px;">
                                    <?php if (!empty($ruplin_api_key)): ?>
                                        <button type="button" class="button button-secondary" id="toggle-api-key" title="Show/Hide API Key">
                                            <span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px; margin: 2px 0;"></span>
                                        </button>
                                        <button type="button" class="button button-secondary" id="copy-api-key">Copy</button>
                                    <?php endif; ?>
                                    <button type="button" class="button button-secondary" id="edit-api-key">Edit</button>
                                </div>
                                <div id="api-key-edit-buttons" style="display: none; gap: 10px;">
                                    <button type="button" class="button button-primary" id="save-api-key">Save</button>
                                    <button type="button" class="button button-secondary" id="cancel-edit-api-key">Cancel</button>
                                </div>
                            </div>
                            <p class="description">
                                <?php if (!empty($ruplin_api_key)): ?>
                                    This is your Ruplin API key used for all plugin operations.
                                <?php else: ?>
                                    No Ruplin API key found. Enter your API key or download the plugin using Option 2 from your Snefuru dashboard.
                                <?php endif; ?>
                            </p>
                            <div id="api-key-message" style="margin-top: 10px; display: none;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">API URL</th>
                        <td>
                            <code><?php echo esc_html($api_url); ?></code>
                            <p class="description">The base URL for your Snefuru Cloud API</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="snefuru-card">
                <h3>Upload API Configuration</h3>
                <p>Configure the plugin to receive image uploads directly from your Next.js application.</p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Upload Endpoints</th>
                        <td>
                            <?php if (!empty($ruplin_api_key)): ?>
                                <p><strong>Upload URL:</strong><br>
                                <code><?php echo rest_url('snefuru/v1/upload-image'); ?></code></p>
                                <p><strong>Status URL:</strong><br>
                                <code><?php echo rest_url('snefuru/v1/status'); ?></code></p>
                                <p><strong>Posts Sync URL:</strong><br>
                                <code><?php echo rest_url('snefuru/v1/posts'); ?></code></p>
                                <p><strong>Elementor Update URL:</strong><br>
                                <code><?php echo rest_url('snefuru/v1/posts/{id}/elementor'); ?></code></p>
                                <button type="button" class="button button-secondary" id="test-upload-endpoint">Test Upload Endpoint</button>
                            <?php else: ?>
                                <p class="description">Enter a Ruplin API key above to see the upload endpoints.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('snefuru_upload_settings_nonce'); ?>
                
                <div class="snefuru-card">
                    <h3>Upload Configuration</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Uploads</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="snefuru_upload_enabled" value="1" <?php checked($upload_enabled, 1); ?> />
                                    Allow image uploads via the plugin API
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Maximum File Size</th>
                            <td>
                                <select name="snefuru_upload_max_size">
                                    <option value="1MB" <?php selected($upload_max_size, '1MB'); ?>>1 MB</option>
                                    <option value="5MB" <?php selected($upload_max_size, '5MB'); ?>>5 MB</option>
                                    <option value="10MB" <?php selected($upload_max_size, '10MB'); ?>>10 MB</option>
                                    <option value="25MB" <?php selected($upload_max_size, '25MB'); ?>>25 MB</option>
                                    <option value="50MB" <?php selected($upload_max_size, '50MB'); ?>>50 MB</option>
                                </select>
                                <p class="description">Maximum allowed file size for uploads (WordPress maximum: <?php echo size_format(wp_max_upload_size()); ?>)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save Upload Settings', 'secondary', 'submit_upload_settings'); ?>
                </div>
            </form>
            
            <div class="snefuru-card">
                <h3>Recent Upload Activity</h3>
                <div id="upload-logs-preview">
                    <?php $this->display_recent_upload_logs(); ?>
                </div>
                <p>
                    <button type="button" class="button button-secondary" onclick="location.href='<?php echo admin_url('admin.php?page=snefuru-logs'); ?>'">View All Upload Logs</button>
                    <button type="button" class="button button-secondary" id="clear-upload-logs">Clear Upload Logs</button>
                </p>
            </div>

            <div class="snefuru-card">
                <h3>Integration Instructions</h3>
                <p>Your plugin is configured with a unified Ruplin API key system:</p>
                <ol>
                    <li><strong>All operations use the same Ruplin API key</strong> - no need for multiple keys</li>
                    <li><strong>The key is pre-configured</strong> when you download using Option 2</li>
                    <li><strong>Your Next.js app automatically uses this key</strong> for all operations (uploads, sync, elementor updates)</li>
                    <li><strong>Test the connection</strong> using the "Test Upload Endpoint" button above</li>
                </ol>
            </div>

            <div class="snefuru-card">
                <h3>Plugin Status</h3>
                <p><strong>Plugin Version:</strong> <?php echo SNEFURU_PLUGIN_VERSION; ?></p>
                <p><strong>API Key Status:</strong> <?php echo !empty($ruplin_api_key) ? '<span class="status-success">Configured</span>' : '<span class="status-error">Not Configured</span>'; ?></p>
            </div>
            
            <div class="snefuru-card">
                <h3>Barkro Update System Debug</h3>
                <?php $this->display_barkro_debug_info(); ?>
                <p>
                    <button type="button" class="button button-secondary" onclick="location.reload();">Refresh Debug Info</button>
                    <button type="button" class="button button-secondary" id="clear-update-logs">Clear Update Logs</button>
                </p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var originalApiKey = $('#ruplin-api-key').data('full-key');
            var isKeyVisible = false;
            
            // Function to mask API key
            function maskApiKey(key) {
                if (!key || key.length < 15) return key;
                return key.substring(0, 10) + '*'.repeat(key.length - 10);
            }
            
            // Initially mask the API key
            if (originalApiKey) {
                $('#ruplin-api-key').val(maskApiKey(originalApiKey));
            }
            
            // Toggle API key visibility
            $('#toggle-api-key').on('click', function() {
                isKeyVisible = !isKeyVisible;
                var $icon = $(this).find('.dashicons');
                
                if (isKeyVisible) {
                    $('#ruplin-api-key').val(originalApiKey);
                    $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                    $(this).attr('title', 'Hide API Key');
                } else {
                    $('#ruplin-api-key').val(maskApiKey(originalApiKey));
                    $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                    $(this).attr('title', 'Show API Key');
                }
            });
            
            // Copy API key (always copy the full key)
            $('#copy-api-key').on('click', function() {
                var tempInput = $('<input>');
                $('body').append(tempInput);
                tempInput.val(originalApiKey).select();
                document.execCommand('copy');
                tempInput.remove();
                alert('API key copied to clipboard!');
            });
            
            // Edit API key
            $('#edit-api-key').on('click', function() {
                // Show full key when editing
                $('#ruplin-api-key').val(originalApiKey).prop('readonly', false).focus();
                $('#api-key-view-buttons').hide();
                $('#api-key-edit-buttons').css('display', 'flex');
                isKeyVisible = true; // Key is visible during edit
            });
            
            // Cancel edit
            $('#cancel-edit-api-key').on('click', function() {
                // Restore masked state
                isKeyVisible = false;
                $('#ruplin-api-key').val(maskApiKey(originalApiKey)).prop('readonly', true);
                $('#api-key-view-buttons').css('display', 'flex');
                $('#api-key-edit-buttons').hide();
                $('#api-key-message').hide();
                // Reset icon to show state
                $('#toggle-api-key').find('.dashicons')
                    .removeClass('dashicons-hidden')
                    .addClass('dashicons-visibility');
                $('#toggle-api-key').attr('title', 'Show API Key');
            });
            
            // Save API key
            $('#save-api-key').on('click', function() {
                var newApiKey = $('#ruplin-api-key').val().trim();
                var $button = $(this);
                var $message = $('#api-key-message');
                
                if (!newApiKey) {
                    $message.removeClass('notice-success').addClass('notice notice-error').html('<p>API key cannot be empty.</p>').show();
                    return;
                }
                
                $button.prop('disabled', true).text('Saving...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'snefuru_save_api_key',
                        api_key: newApiKey,
                        nonce: '<?php echo wp_create_nonce('snefuru_save_api_key'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            originalApiKey = newApiKey;
                            isKeyVisible = false;
                            $('#ruplin-api-key').val(maskApiKey(newApiKey)).prop('readonly', true);
                            $('#ruplin-api-key').data('full-key', newApiKey);
                            $('#api-key-view-buttons').css('display', 'flex');
                            $('#api-key-edit-buttons').hide();
                            $message.removeClass('notice-error').addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                            
                            // Update the toggle/copy button visibility
                            if (newApiKey && $('#toggle-api-key').length === 0) {
                                var buttonsHtml = '<button type="button" class="button button-secondary" id="toggle-api-key" title="Show/Hide API Key">' +
                                    '<span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px; margin: 2px 0;"></span>' +
                                    '</button>' +
                                    '<button type="button" class="button button-secondary" id="copy-api-key">Copy</button>';
                                $('#edit-api-key').before(buttonsHtml);
                                
                                // Re-attach event handlers
                                $('#toggle-api-key').on('click', function() {
                                    isKeyVisible = !isKeyVisible;
                                    var $icon = $(this).find('.dashicons');
                                    
                                    if (isKeyVisible) {
                                        $('#ruplin-api-key').val(originalApiKey);
                                        $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                                        $(this).attr('title', 'Hide API Key');
                                    } else {
                                        $('#ruplin-api-key').val(maskApiKey(originalApiKey));
                                        $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                                        $(this).attr('title', 'Show API Key');
                                    }
                                });
                                
                                $('#copy-api-key').on('click', function() {
                                    var tempInput = $('<input>');
                                    $('body').append(tempInput);
                                    tempInput.val(originalApiKey).select();
                                    document.execCommand('copy');
                                    tempInput.remove();
                                    alert('API key copied to clipboard!');
                                });
                            }
                            
                            // Update API key status
                            var $status = $('p:contains("API Key Status:")');
                            if ($status.length) {
                                $status.html('<strong>API Key Status:</strong> <span class="status-success">Configured</span>');
                            }
                        } else {
                            $message.removeClass('notice-success').addClass('notice notice-error').html('<p>' + response.data.message + '</p>').show();
                        }
                    },
                    error: function() {
                        $message.removeClass('notice-success').addClass('notice notice-error').html('<p>Failed to save API key. Please try again.</p>').show();
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Save');
                    }
                });
            });
            
            // Upload functionality
            $('#test-upload-endpoint').on('click', function() {
                $.post(ajaxurl, {
                    action: 'snefuru_test_upload_endpoint',
                    nonce: '<?php echo wp_create_nonce('snefuru_upload_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('✅ Upload endpoint is working correctly!\n\n' + response.data.message);
                    } else {
                        alert('❌ Upload endpoint test failed:\n\n' + response.data.message);
                    }
                });
            });
            
            $('#clear-upload-logs').on('click', function() {
                if (confirm('Are you sure you want to clear all upload logs?')) {
                    $.post(ajaxurl, {
                        action: 'snefuru_clear_upload_logs',
                        nonce: '<?php echo wp_create_nonce('snefuru_upload_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            $('#upload-logs-preview').html('<p>Upload logs cleared.</p>');
                            alert('Upload logs cleared successfully!');
                        } else {
                            alert('Error clearing logs: ' + response.data.message);
                        }
                    });
                }
            });
            
            $('#clear-update-logs').on('click', function() {
                if (confirm('Are you sure you want to clear Barkro update debug data?')) {
                    $.post(ajaxurl, {
                        action: 'snefuru_clear_update_debug',
                        nonce: '<?php echo wp_create_nonce('snefuru_admin_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Update debug data cleared successfully!');
                            location.reload();
                        } else {
                            alert('Error clearing debug data: ' + response.data.message);
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Logs page
     */
    public function logs_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'snefuru_logs';
        
        // Handle log clearing
        if (isset($_POST['clear_logs'])) {
            check_admin_referer('snefuru_logs_nonce');
            $wpdb->query("TRUNCATE TABLE {$table_name}");
            echo '<div class="notice notice-success"><p>Logs cleared!</p></div>';
        }
        
        // Pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        $logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} ORDER BY timestamp DESC LIMIT %d OFFSET %d", $per_page, $offset));
        
        $total_pages = ceil($total_logs / $per_page);
        
        ?>
        <div class="wrap">
            <h1>Snefuruplin Logs</h1>
            
            <div class="tablenav top">
                <form method="post" style="float: right;">
                    <?php wp_nonce_field('snefuru_logs_nonce'); ?>
                    <input type="submit" name="clear_logs" class="button button-secondary" value="Clear All Logs" onclick="return confirm('Are you sure you want to clear all logs?');" />
                </form>
                <div class="clear"></div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 150px;">Timestamp</th>
                        <th style="width: 120px;">Action</th>
                        <th style="width: 80px;">Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html(mysql2date('M j, Y g:i:s A', $log->timestamp)); ?></td>
                                <td><?php echo esc_html($log->action); ?></td>
                                <td><span class="status-<?php echo esc_attr($log->status); ?>"><?php echo esc_html($log->status); ?></span></td>
                                <td><?php echo esc_html($log->data); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        $page_links = paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $current_page
                        ));
                        
                        if ($page_links) {
                            echo '<span class="displaying-num">' . sprintf(_n('%d item', '%d items', $total_logs), $total_logs) . '</span>';
                            echo $page_links;
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Display connection status
     */
    private function display_connection_status() {
        $ruplin_api_key = get_option('snefuru_ruplin_api_key_1', '');
        
        if (empty($ruplin_api_key)) {
            echo '<span class="status-error">Not configured</span>';
        } else {
            $last_successful_sync = get_option('snefuru_last_successful_sync', '');
            if ($last_successful_sync) {
                echo '<span class="status-success">Connected</span>';
                echo '<br><small>Last sync: ' . esc_html(mysql2date('M j, Y g:i A', $last_successful_sync)) . '</small>';
            } else {
                echo '<span class="status-warning">Configured but not tested</span>';
            }
        }
    }
    
    /**
     * AJAX: Test API connection
     */
    public function test_api_connection() {
        check_ajax_referer('snefuru_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $api_client = new Snefuru_API_Client();
        $test_data = array('test' => true, 'timestamp' => current_time('mysql'));
        
        $result = $api_client->send_data_to_cloud($test_data);
        
        if ($result) {
            wp_send_json_success('Connection successful!');
        } else {
            wp_send_json_error('Connection failed. Please check your API key and URL.');
        }
    }
    
    /**
     * AJAX: Manual sync
     */
    public function manual_sync() {
        check_ajax_referer('snefuru_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $data_collector = new Snefuru_Data_Collector();
        $api_client = new Snefuru_API_Client();
        
        $site_data = $data_collector->collect_site_data();
        $result = $api_client->send_data_to_cloud($site_data);
        
        if ($result) {
            update_option('snefuru_last_sync', current_time('mysql'));
            update_option('snefuru_last_successful_sync', current_time('mysql'));
            wp_send_json_success('Data synchronized successfully!');
        } else {
            wp_send_json_error('Synchronization failed. Please check your connection settings.');
        }
    }
    
    /**
     * AJAX: Save API key
     */
    public function save_api_key() {
        check_ajax_referer('snefuru_save_api_key', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API key cannot be empty.'));
        }
        
        // Validate API key format (basic validation)
        if (strlen($api_key) < 10) {
            wp_send_json_error(array('message' => 'Invalid API key format.'));
        }
        
        // Save the API key
        update_option('snefuru_ruplin_api_key_1', $api_key);
        
        // Test the API key by making a test connection
        $api_client = new Snefuru_API_Client();
        $test_data = array('test' => true, 'timestamp' => current_time('mysql'));
        $result = $api_client->send_data_to_cloud($test_data);
        
        if ($result) {
            wp_send_json_success(array('message' => 'API key saved and verified successfully!'));
        } else {
            // Still save the key but warn about connection
            wp_send_json_success(array('message' => 'API key saved. Warning: Could not verify connection with this key.'));
        }
    }
    
    /**
     * AJAX: Rebuild Zen Tables
     */
    public function rebuild_zen_tables() {
        check_ajax_referer('snefuru_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        try {
            // Include the database class if not already loaded
            if (!class_exists('Ruplin_WP_Database_Horse_Class')) {
                require_once SNEFURU_PLUGIN_PATH . 'includes/class-ruplin-wppma-database.php';
            }
            
            error_log('Snefuru: AJAX rebuild request started');
            
            // Create/rebuild the tables
            $result = Ruplin_WP_Database_Horse_Class::create_tables();
            
            error_log('Snefuru: create_tables() returned: ' . ($result ? 'true' : 'false'));
            
            // Check if tables exist to verify success
            if (Ruplin_WP_Database_Horse_Class::tables_exist()) {
                global $wpdb;
                $services_table = $wpdb->prefix . 'zen_services';
                $locations_table = $wpdb->prefix . 'zen_locations';
                
                // Get table row counts for confirmation
                $services_count = $wpdb->get_var("SELECT COUNT(*) FROM $services_table");
                $locations_count = $wpdb->get_var("SELECT COUNT(*) FROM $locations_table");
                
                wp_send_json_success(array(
                    'message' => "Zen tables rebuilt successfully! Tables {$services_table} ({$services_count} rows) and {$locations_table} ({$locations_count} rows) are ready."
                ));
            } else {
                wp_send_json_error(array(
                    'message' => 'Tables were processed but verification failed. Check error_log for details.'
                ));
            }
        } catch (Exception $e) {
            error_log('Snefuru: AJAX rebuild exception: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Error rebuilding tables: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Display recent upload logs
     */
    private function display_recent_upload_logs() {
        $upload_logs = get_option('snefuru_upload_logs', array());
        $recent_logs = array_slice(array_reverse($upload_logs), 0, 10);
        
        if (empty($recent_logs)) {
            echo '<p>No upload activity yet.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Time</th><th>Type</th><th>Message</th><th>Details</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($recent_logs as $log) {
            $status_class = $log['type'] === 'error' ? 'error' : 'success';
            echo '<tr>';
            echo '<td>' . esc_html(mysql2date('M j, Y g:i A', $log['timestamp'])) . '</td>';
            echo '<td><span class="status-' . esc_attr($status_class) . '">' . esc_html($log['type']) . '</span></td>';
            echo '<td>' . esc_html($log['message']) . '</td>';
            echo '<td>';
            if (!empty($log['data'])) {
                if (isset($log['data']['attachment_id'])) {
                    echo 'ID: ' . esc_html($log['data']['attachment_id']);
                }
                if (isset($log['data']['batch_id'])) {
                    echo ' | Batch: ' . esc_html(substr($log['data']['batch_id'], 0, 8)) . '...';
                }
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    
    /**
     * Clear upload logs
     */
    public function clear_upload_logs() {
        check_ajax_referer('snefuru_upload_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        delete_option('snefuru_upload_logs');
        
        wp_send_json_success(array(
            'message' => 'Upload logs cleared successfully'
        ));
    }
    
    /**
     * Test upload endpoint
     */
    public function test_upload_endpoint() {
        check_ajax_referer('snefuru_upload_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $api_key = get_option('snefuru_ruplin_api_key_1', '');
        
        if (empty($api_key)) {
            wp_send_json_error(array(
                'message' => 'No Ruplin API key configured. Please reinstall the plugin using Option 2.'
            ));
        }
        
        // Test the status endpoint
        $status_url = rest_url('snefuru/v1/status') . '?api_key=' . urlencode($api_key);
        $response = wp_remote_get($status_url);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => 'Failed to connect to status endpoint: ' . $response->get_error_message()
            ));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (wp_remote_retrieve_response_code($response) === 200 && isset($data['success']) && $data['success']) {
            wp_send_json_success(array(
                'message' => 'Plugin upload endpoints are working correctly!\n\nStatus: ' . print_r($data['data'], true)
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Status endpoint test failed. Response: ' . $body
            ));
        }
    }
    
    /**
     * Add metabox to show Elementor data
     */
    public function add_elementor_data_metabox() {
        // Add to posts and pages
        add_meta_box(
            'snefuru-elementor-data',
            'Elementor Raw Data (Snefuru Plugin)',
            array($this, 'elementor_data_metabox_callback'),
            'post',
            'normal',
            'low'
        );
        
        add_meta_box(
            'snefuru-elementor-data',
            'Elementor Raw Data (Snefuru Plugin)',
            array($this, 'elementor_data_metabox_callback'),
            'page',
            'normal',
            'low'
        );
        
        // Add to any other post types that might use Elementor
        $post_types = get_post_types(array('public' => true), 'names');
        foreach ($post_types as $post_type) {
            if (!in_array($post_type, array('post', 'page', 'attachment'))) {
                add_meta_box(
                    'snefuru-elementor-data',
                    'Elementor Raw Data (Snefuru Plugin)',
                    array($this, 'elementor_data_metabox_callback'),
                    $post_type,
                    'normal',
                    'low'
                );
            }
        }
    }
    
    /**
     * Metabox callback to display Elementor data
     */
    public function elementor_data_metabox_callback($post) {
        // Get the Elementor data
        $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            echo '<p style="color: #666; font-style: italic;">No Elementor data found for this post/page.</p>';
            echo '<p style="color: #666; font-size: 12px;">This metabox will only show data if this post/page was created or edited with Elementor.</p>';
            return;
        }
        
        // Format the JSON for better readability
        $formatted_data = '';
        if (is_string($elementor_data)) {
            // If it's already a JSON string, try to format it
            $decoded = json_decode($elementor_data, true);
            if ($decoded !== null) {
                $formatted_data = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            } else {
                $formatted_data = $elementor_data;
            }
        } else {
            // If it's an array/object, encode it
            $formatted_data = json_encode($elementor_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        
        ?>
        <div style="margin-bottom: 10px;">
            <p><strong>Raw Elementor Data (_elementor_data field):</strong></p>
            <p style="font-size: 12px; color: #666;">
                This is the raw data that Elementor stores for this page. You can copy this data for backup, 
                debugging, or transferring page designs. 
                <strong>Warning:</strong> Do not modify this data unless you know what you're doing.
            </p>
        </div>
        
        <div style="position: relative;">
            <textarea 
                id="snefuru-elementor-data" 
                readonly 
                style="width: 100%; height: 300px; font-family: monospace; font-size: 11px; background: #f9f9f9; border: 1px solid #ddd; padding: 10px; resize: vertical;"
                onclick="this.select();"
            ><?php echo esc_textarea($formatted_data); ?></textarea>
            
            <button 
                type="button" 
                onclick="copyElementorData()" 
                style="position: absolute; top: 5px; right: 5px; background: #0073aa; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 11px;"
                title="Copy to clipboard"
            >
                Copy
            </button>
        </div>
        
        <div style="margin-top: 10px; font-size: 12px; color: #666;">
            <strong>Data size:</strong> <?php echo size_format(strlen($formatted_data)); ?> | 
            <strong>Last modified:</strong> <?php echo get_the_modified_date('Y-m-d H:i:s', $post->ID); ?>
        </div>
        
        <script>
        function copyElementorData() {
            var textArea = document.getElementById('snefuru-elementor-data');
            textArea.select();
            textArea.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                // Show success message
                var button = event.target;
                var originalText = button.textContent;
                button.textContent = 'Copied!';
                button.style.background = '#28a745';
                setTimeout(function() {
                    button.textContent = originalText;
                    button.style.background = '#0073aa';
                }, 2000);
            } catch (err) {
                alert('Failed to copy. Please manually select and copy the text.');
            }
        }
        </script>
        
        <?php
    }
    
    /**
     * Screen 4 - Manage page
     */
    public function screen4_manage_page() {
        // Handle form submission
        if (isset($_POST['submit'])) {
            check_admin_referer('snefuru_screen4_manage_nonce');
            
            update_option('snefuru_maleench1', sanitize_textarea_field($_POST['snefuru_maleench1']));
            
            echo '<div class="notice notice-success"><p>maleench1 data saved!</p></div>';
        }
        
        $maleench1_value = get_option('snefuru_maleench1', '');
        
        ?>
        <div class="wrap">
            <h1>screen 4 - manage</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('snefuru_screen4_manage_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="snefuru_maleench1">maleench1</label>
                        </th>
                        <td>
                            <textarea 
                                name="snefuru_maleench1" 
                                id="snefuru_maleench1" 
                                rows="10" 
                                cols="80" 
                                class="large-text"
                            ><?php echo esc_textarea($maleench1_value); ?></textarea>
                            <p class="description">Enter your maleench1 data here.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Bespoke CSS Editor page
     */
    public function bespoke_css_editor_page() {
        global $wpdb;
        $styling_table = $wpdb->prefix . 'snefuruplin_styling';
        
        // Handle form submission
        if (isset($_POST['save_css'])) {
            check_admin_referer('snefuru_css_editor_nonce');
            
            $styling_content = wp_unslash($_POST['styling_content']);
            $styling_end_url = esc_url_raw($_POST['styling_end_url']);
            
            // Update or insert CSS content
            $existing_record = $wpdb->get_row("SELECT * FROM $styling_table ORDER BY styling_id LIMIT 1");
            
            if ($existing_record) {
                $wpdb->update(
                    $styling_table,
                    array(
                        'styling_content' => $styling_content,
                        'styling_end_url' => $styling_end_url
                    ),
                    array('styling_id' => $existing_record->styling_id)
                );
            } else {
                $wpdb->insert(
                    $styling_table,
                    array(
                        'styling_content' => $styling_content,
                        'styling_end_url' => $styling_end_url
                    )
                );
            }
            
            echo '<div class="notice notice-success"><p>CSS saved successfully!</p></div>';
        }
        
        // Get current CSS content
        $current_record = $wpdb->get_row("SELECT * FROM $styling_table ORDER BY styling_id LIMIT 1");
        $css_content = $current_record ? $current_record->styling_content : "/* Bespoke CSS Editor - Add your custom styles here */\n\nbody {\n    /* Your custom styles */\n}";
        $end_url = $current_record ? $current_record->styling_end_url : get_site_url() . '/wp-json/snefuru/v1/css/bespoke';
        
        ?>
        <div class="wrap" style="margin: 20px 0 0 0;">
            <h1>Bespoke CSS Editor</h1>
            
            <form method="post" action="" style="height: calc(100vh - 120px);">
                <?php wp_nonce_field('snefuru_css_editor_nonce'); ?>
                
                <!-- Top controls bar -->
                <div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin-bottom: 10px; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 10px;">
                        <button type="submit" name="save_css" class="button button-primary button-large">
                            💾 Save Changes
                        </button>
                        
                        <div style="flex: 1;">
                            <label for="styling_end_url" style="font-weight: 600; margin-right: 10px;">Public CSS URL:</label>
                            <input 
                                type="url" 
                                name="styling_end_url" 
                                id="styling_end_url" 
                                value="<?php echo esc_attr($end_url); ?>" 
                                style="width: 400px; padding: 4px 8px;"
                                readonly
                            />
                            <button type="button" onclick="copyToClipboard('styling_end_url')" class="button button-secondary" style="margin-left: 10px;">
                                📋 Copy URL
                            </button>
                        </div>
                    </div>
                    
                    <div style="font-size: 12px; color: #666;">
                        <strong>Usage:</strong> Include this CSS in external sites with: 
                        <code>&lt;link rel="stylesheet" href="<?php echo esc_html($end_url); ?>"&gt;</code>
                    </div>
                </div>
                
                <!-- CSS Editor -->
                <div style="height: calc(100% - 100px); border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                    <textarea 
                        name="styling_content" 
                        id="css-editor"
                        style="width: 100%; height: 100%; border: none; font-family: 'Monaco', 'Consolas', 'Courier New', monospace; font-size: 13px; line-height: 1.4; padding: 20px; resize: none; outline: none; background: #f8f9fa;"
                        placeholder="/* Add your custom CSS here... */"
                    ><?php echo esc_textarea($css_content); ?></textarea>
                </div>
            </form>
        </div>
        
        <style>
        .wrap {
            margin-right: 20px !important;
        }
        
        #css-editor {
            tab-size: 4;
            -moz-tab-size: 4;
        }
        
        #css-editor:focus {
            background: #fff !important;
            box-shadow: inset 0 0 0 2px #0073aa;
        }
        
        /* Syntax highlighting for basic CSS */
        #css-editor {
            color: #333;
        }
        </style>
        
        <script>
        function copyToClipboard(elementId) {
            var element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                // Show success feedback
                var button = event.target;
                var originalText = button.textContent;
                button.textContent = '✅ Copied!';
                button.style.background = '#28a745';
                button.style.color = '#fff';
                setTimeout(function() {
                    button.textContent = originalText;
                    button.style.background = '';
                    button.style.color = '';
                }, 2000);
            } catch (err) {
                alert('Failed to copy. Please manually select and copy the URL.');
            }
        }
        
        // Auto-save functionality (optional)
        var autoSaveTimeout;
        document.getElementById('css-editor').addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(function() {
                // Could implement auto-save here
                console.log('CSS content changed - auto-save could trigger here');
            }, 5000);
        });
        
        // Enhance textarea with basic features
        document.getElementById('css-editor').addEventListener('keydown', function(e) {
            // Tab key support
            if (e.key === 'Tab') {
                e.preventDefault();
                var start = this.selectionStart;
                var end = this.selectionEnd;
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
        </script>
        
        <?php
    }
    
    /**
     * Display Barkro update system debug information
     */
    private function display_barkro_debug_info() {
        global $wpdb;
        
        // Get last update attempt info
        $last_attempt = get_option('snefuru_last_update_attempt', array());
        
        // Get recent Barkro logs
        $table_name = $wpdb->prefix . 'snefuru_logs';
        $barkro_logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE action = 'barkro_update' ORDER BY timestamp DESC LIMIT 50"
        ));
        
        // Get current update transient
        $update_transient = get_site_transient('snefuru_update_data');
        
        echo '<div class="barkro-debug-info">';
        
        // Last Update Attempt
        echo '<h4>Last Update Attempt</h4>';
        if (!empty($last_attempt)) {
            echo '<table class="widefat" style="max-width: 100%;">';
            foreach ($last_attempt as $key => $value) {
                echo '<tr>';
                echo '<td style="width: 200px;"><strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</strong></td>';
                echo '<td>' . esc_html($value) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No update attempts recorded.</p>';
        }
        
        // Current Update Transient
        echo '<h4>Current Update Transient</h4>';
        if ($update_transient) {
            echo '<pre style="background: #f9f9f9; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;">';
            echo esc_html(json_encode($update_transient, JSON_PRETTY_PRINT));
            echo '</pre>';
        } else {
            echo '<p>No update transient data found.</p>';
        }
        
        // Recent Logs
        echo '<h4>Recent Barkro Update Logs</h4>';
        if ($barkro_logs) {
            echo '<table class="wp-list-table widefat fixed striped" style="max-width: 100%;">';
            echo '<thead><tr><th style="width: 150px;">Timestamp</th><th>Event</th><th>Status</th></tr></thead>';
            echo '<tbody>';
            foreach ($barkro_logs as $log) {
                echo '<tr>';
                echo '<td>' . esc_html(mysql2date('M j, Y g:i:s A', $log->timestamp)) . '</td>';
                echo '<td>' . esc_html($log->data) . '</td>';
                echo '<td><span class="status-' . esc_attr($log->status) . '">' . esc_html($log->status) . '</span></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No Barkro update logs found.</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * Clear Barkro update debug data
     */
    public function clear_update_debug() {
        check_ajax_referer('snefuru_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        
        // Clear update attempt data
        delete_option('snefuru_last_update_attempt');
        
        // Clear update transient
        delete_site_transient('snefuru_update_data');
        
        // Clear Barkro logs
        $table_name = $wpdb->prefix . 'snefuru_logs';
        $wpdb->delete($table_name, array('action' => 'barkro_update'));
        
        wp_send_json_success(array(
            'message' => 'Barkro update debug data cleared successfully'
        ));
    }
    
    /**
     * Dublish logs page
     */
    public function dublish_logs_page() {
        // Get Dublish API instance to fetch logs
        $dublish_api = new Snefuru_Dublish_API();
        $logs = $dublish_api->get_dublish_logs(100);
        
        ?>
        <div class="wrap">
            <h1>Dublish Activity Logs</h1>
            <p>This page shows all Elementor pages created via the Dublish system from Snefuru.</p>
            
            <?php if (empty($logs)): ?>
                <div class="notice notice-info">
                    <p>No Dublish activity found yet. Elementor pages created via Dublish will appear here.</p>
                </div>
            <?php else: ?>
                <div class="snefuru-card">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Post ID</th>
                                <th>Post Title</th>
                                <th>Gcon Piece ID</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): 
                                $data = json_decode($log->data, true);
                                $post_id = isset($data['post_id']) ? $data['post_id'] : 'N/A';
                                $post_title = isset($data['post_title']) ? $data['post_title'] : 'N/A';
                                $gcon_piece_id = isset($data['gcon_piece_id']) ? $data['gcon_piece_id'] : 'N/A';
                                ?>
                                <tr>
                                    <td><?php echo esc_html($log->timestamp); ?></td>
                                    <td>
                                        <?php if ($post_id !== 'N/A'): ?>
                                            <strong><?php echo esc_html($post_id); ?></strong>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($post_title); ?></td>
                                    <td>
                                        <code style="font-size: 11px;">
                                            <?php echo esc_html(substr($gcon_piece_id, 0, 8) . '...'); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <span class="status-<?php echo esc_attr($log->status); ?>">
                                            <?php echo esc_html($log->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($post_id !== 'N/A' && get_post($post_id)): ?>
                                            <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=elementor'); ?>" class="button button-small" target="_blank">
                                                Edit in Elementor
                                            </a>
                                            <a href="<?php echo get_permalink($post_id); ?>" class="button button-small" target="_blank">
                                                View Page
                                            </a>
                                        <?php else: ?>
                                            <em>Post not found</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <style>
                .snefuru-card {
                    background: white;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    margin: 20px 0;
                }
                .status-completed {
                    color: #46b450;
                    font-weight: bold;
                }
                .status-failed {
                    color: #dc3232;
                    font-weight: bold;
                }
                .status-pending {
                    color: #ffb900;
                    font-weight: bold;
                }
            </style>
        </div>
        <?php
    }
    
    /**
     * rup_locations_mar page - Locations Management
     */
    public function rup_locations_mar_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_locations';
        
        // Handle AJAX requests
        if (isset($_POST['action'])) {
            $this->handle_locations_ajax();
            return;
        }
        
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        
        ?>
        <div class="wrap" style="margin: 0; padding: 20px;">
            <h1 style="margin-bottom: 20px;">📍 Zen Locations Manager</h1>
            
            <!-- Control Bar -->
            <div style="background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button id="create-inline-btn" class="button button-primary">Create New (Inline)</button>
                        <button id="create-popup-btn" class="button button-secondary">Create New (Popup)</button>
                    </div>
                </div>
                
                <!-- Nubra Tableface Kite -->
                <div id="nubra-tableface-kite" style="margin-bottom: 15px;"></div>
                
                <!-- Pagination and Search Controls -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <!-- Items per page -->
                        <div style="display: flex; gap: 0; border: 1px solid #ccc;">
                            <button class="per-page-btn" data-value="10" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px;">10</button>
                            <button class="per-page-btn" data-value="20" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">20</button>
                            <button class="per-page-btn" data-value="50" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">50</button>
                            <button class="per-page-btn active" data-value="100" style="border: none; padding: 10px 15px; background: #0073aa; color: white; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">100</button>
                            <button class="per-page-btn" data-value="200" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">200</button>
                            <button class="per-page-btn" data-value="500" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">500</button>
                            <button class="per-page-btn" data-value="all" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc; border-top-right-radius: 4px; border-bottom-right-radius: 4px;">All</button>
                        </div>
                        
                        <!-- Page navigation -->
                        <div id="page-nav" style="display: flex; gap: 0; border: 1px solid #ccc;">
                            <!-- Dynamic page buttons will go here -->
                        </div>
                    </div>
                    
                    <!-- Search -->
                    <div style="position: relative;">
                        <input type="text" id="search-box" placeholder="Search locations..." style="padding: 8px 40px 8px 12px; border: 1px solid #ccc; border-radius: 4px; width: 250px; font-size: 14px;">
                        <button id="clear-search" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #ffeb3b; border: none; padding: 4px 8px; font-size: 12px; font-weight: bold; border-radius: 3px; cursor: pointer;">CL</button>
                    </div>
                </div>
            </div>
            
            <!-- Main Table -->
            <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                <div style="overflow-x: auto;">
                    <table id="locations-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f0f0f0; width: 50px; cursor: pointer;" onclick="toggleSelectAll()">
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                        <input type="checkbox" id="select-all" style="width: 20px; height: 20px;">
                                    </div>
                                </th>
                                <th data-sort="location_id" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">location_id</th>
                                <th data-sort="location_name" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">location_name</th>
                                <th data-sort="location_placard" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">location_placard</th>
                                <th data-sort="location_moniker" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">location_moniker</th>
                                <th data-sort="location_sobriquet" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">location_sobriquet</th>
                                <th data-sort="street" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">street</th>
                                <th data-sort="city" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">city</th>
                                <th data-sort="state_code" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">state_code</th>
                                <th data-sort="zip_code" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">zip_code</th>
                                <th data-sort="country" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">country</th>
                                <th data-sort="rel_image1_id" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">rel_image1_id</th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">pick image1</th>
                                <th data-sort="is_pinned_location" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">is_pinned_location</th>
                                <th data-sort="position_in_custom_order" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">position_in_custom_order</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Bottom Pagination -->
            <div style="background: white; border: 1px solid #ddd; padding: 15px; margin-top: 20px; border-radius: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <div id="record-info" style="font-size: 14px; color: #666;"></div>
                        
                        <!-- Bottom Items per page -->
                        <div style="display: flex; gap: 0; border: 1px solid #ccc;">
                            <button class="per-page-btn-bottom" data-value="10" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px;">10</button>
                            <button class="per-page-btn-bottom" data-value="20" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">20</button>
                            <button class="per-page-btn-bottom" data-value="50" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">50</button>
                            <button class="per-page-btn-bottom active" data-value="100" style="border: none; padding: 10px 15px; background: #0073aa; color: white; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">100</button>
                            <button class="per-page-btn-bottom" data-value="200" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">200</button>
                            <button class="per-page-btn-bottom" data-value="500" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">500</button>
                            <button class="per-page-btn-bottom" data-value="all" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc; border-top-right-radius: 4px; border-bottom-right-radius: 4px;">All</button>
                        </div>
                        
                        <!-- Bottom Page navigation -->
                        <div id="page-nav-bottom" style="display: flex; gap: 0; border: 1px solid #ccc;">
                            <!-- Dynamic page buttons will go here -->
                        </div>
                    </div>
                    
                    <!-- Bottom Search -->
                    <div style="position: relative;">
                        <input type="text" id="search-box-bottom" placeholder="Search locations..." style="padding: 8px 40px 8px 12px; border: 1px solid #ccc; border-radius: 4px; width: 250px; font-size: 14px;">
                        <button id="clear-search-bottom" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #ffeb3b; border: none; padding: 4px 8px; font-size: 12px; font-weight: bold; border-radius: 3px; cursor: pointer;">CL</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Create Popup Modal -->
        <div id="create-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 600px; max-height: 80vh; overflow-y: auto;">
                <h2 style="margin-top: 0;">Create New Location</h2>
                <form id="create-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Location Name:</label>
                            <input type="text" name="location_name" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Location Placard:</label>
                            <input type="text" name="location_placard" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Location Moniker:</label>
                            <input type="text" name="location_moniker" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Location Sobriquet:</label>
                            <input type="text" name="location_sobriquet" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Street:</label>
                            <input type="text" name="street" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">City:</label>
                            <input type="text" name="city" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">State Code:</label>
                            <input type="text" name="state_code" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Zip Code:</label>
                            <input type="text" name="zip_code" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Country:</label>
                            <input type="text" name="country" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Related Image ID:</label>
                            <input type="number" name="rel_image1_id" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Position Order:</label>
                            <input type="number" name="position_in_custom_order" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: bold;">
                                <input type="checkbox" name="is_pinned_location" style="width: 20px; height: 20px;">
                                Is Pinned Location
                            </label>
                        </div>
                    </div>
                    <div style="text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
                        <button type="button" id="cancel-create" style="margin-right: 10px; padding: 8px 20px; border: 1px solid #ccc; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button type="submit" style="padding: 8px 20px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer;">Create Location</button>
                    </div>
                </form>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let itemsPerPage = 100;
            let searchTerm = '';
            let sortField = 'location_id';
            let sortOrder = 'asc';
            let selectedRows = new Set();
            let allData = [];
            let filteredData = [];
            let editingCell = null;
            
            // Load Nubra Tableface Kite
            loadNubraTablefaceKite();
            
            // Initial load
            loadData();
            
            // Search functionality
            $('#search-box, #search-box-bottom').on('input', function() {
                searchTerm = $(this).val();
                $('#search-box, #search-box-bottom').val(searchTerm); // Sync both search boxes
                currentPage = 1;
                filterAndDisplay();
            });
            
            $('#clear-search, #clear-search-bottom').click(function() {
                searchTerm = '';
                $('#search-box, #search-box-bottom').val('');
                currentPage = 1;
                filterAndDisplay();
            });
            
            // Per page buttons
            $('.per-page-btn, .per-page-btn-bottom').click(function() {
                itemsPerPage = $(this).data('value') === 'all' ? 999999 : $(this).data('value');
                $('.per-page-btn, .per-page-btn-bottom').removeClass('active').css({'background': '#f9f9f9', 'color': 'black'});
                $(`.per-page-btn[data-value="${$(this).data('value')}"], .per-page-btn-bottom[data-value="${$(this).data('value')}"]`).addClass('active').css({'background': '#0073aa', 'color': 'white'});
                currentPage = 1;
                filterAndDisplay();
            });
            
            // Create buttons
            $('#create-inline-btn').click(function() {
                createInlineRow();
            });
            
            $('#create-popup-btn').click(function() {
                $('#create-modal').show();
            });
            
            $('#cancel-create').click(function() {
                $('#create-modal').hide();
                $('#create-form')[0].reset();
            });
            
            $('#create-form').submit(function(e) {
                e.preventDefault();
                createLocationPopup();
            });
            
            // Load data function
            function loadData() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_locations_get_data',
                        nonce: '<?php echo wp_create_nonce('rup_locations_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            allData = response.data;
                            filterAndDisplay();
                        }
                    },
                    error: function() {
                        alert('Error loading data');
                    }
                });
            }
            
            function loadNubraTablefaceKite() {
                $('#nubra-tableface-kite').html('<div style="color: #666; font-size: 12px; font-style: italic; padding: 5px 0; border-left: 3px solid #0073aa; padding-left: 10px;">nubra-tableface-kite: zen_locations comprehensive management grid</div>');
            }
            
            function filterAndDisplay() {
                // Filter data
                if (searchTerm === '') {
                    filteredData = allData;
                } else {
                    filteredData = allData.filter(item => {
                        return Object.values(item).some(value => 
                            value && value.toString().toLowerCase().includes(searchTerm.toLowerCase())
                        );
                    });
                }
                
                // Sort data
                filteredData.sort((a, b) => {
                    let aVal = a[sortField] || '';
                    let bVal = b[sortField] || '';
                    if (sortOrder === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });
                
                displayTable();
                updatePagination();
                updateRecordInfo();
            }
            
            function displayTable() {
                let startIndex = (currentPage - 1) * itemsPerPage;
                let endIndex = startIndex + itemsPerPage;
                let pageData = filteredData.slice(startIndex, endIndex);
                
                let tbody = $('#table-body');
                tbody.empty();
                
                pageData.forEach(function(row) {
                    let tr = $('<tr style="cursor: pointer;"></tr>');
                    tr.hover(function() {
                        $(this).css('background-color', '#f9f9f9');
                    }, function() {
                        $(this).css('background-color', '');
                    });
                    
                    // Checkbox column
                    let checkboxTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center; cursor: pointer;"></td>');
                    let checkbox = $('<input type="checkbox" style="width: 20px; height: 20px;">');
                    checkbox.prop('checked', selectedRows.has(row.location_id));
                    checkbox.change(function() {
                        if (this.checked) {
                            selectedRows.add(row.location_id);
                        } else {
                            selectedRows.delete(row.location_id);
                        }
                    });
                    checkboxTd.click(function(e) {
                        if (e.target.type !== 'checkbox') {
                            checkbox.prop('checked', !checkbox.prop('checked')).change();
                        }
                    });
                    checkboxTd.append(checkbox);
                    tr.append(checkboxTd);
                    
                    // Data columns
                    ['location_id', 'location_name', 'location_placard', 'location_moniker', 
                     'location_sobriquet', 'street', 'city', 'state_code', 'zip_code', 
                     'country', 'rel_image1_id', 'pick_image1', 'is_pinned_location', 'position_in_custom_order'].forEach(function(field) {
                        let td = $('<td style="padding: 8px; border: 1px solid #ddd;"></td>');
                        
                        if (field === 'is_pinned_location') {
                            // Boolean toggle switch
                            let toggleSwitch = $('<label style="position: relative; display: inline-block; width: 50px; height: 24px;"><input type="checkbox" style="opacity: 0; width: 0; height: 0;"><span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px;"></span></label>');
                            let checkbox = toggleSwitch.find('input');
                            let slider = toggleSwitch.find('span');
                            
                            checkbox.prop('checked', row[field] == 1);
                            if (row[field] == 1) {
                                slider.css('background-color', '#2196F3');
                                slider.append('<span style="position: absolute; content: ""; height: 18px; width: 18px; left: 26px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>');
                            } else {
                                slider.append('<span style="position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>');
                            }
                            
                            checkbox.change(function() {
                                updateField(row.location_id, field, this.checked ? 1 : 0);
                                row[field] = this.checked ? 1 : 0;
                            });
                            
                            td.append(toggleSwitch);
                        } else if (field === 'pick_image1') {
                            // Image picker widget
                            let imageId = row['rel_image1_id'];
                            let imageContainer = $('<div style="display: flex; align-items: center; gap: 5px;"></div>');
                            
                            if (imageId && imageId > 0) {
                                // Show existing image with "assign new image" button
                                let assignBtn = $('<button style="padding: 3px 8px; font-size: 11px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer; white-space: nowrap;">assign new image</button>');
                                assignBtn.click(function() {
                                    openImagePicker(row.location_id, imageContainer);
                                });
                                imageContainer.append(assignBtn);
                                
                                // Add image preview (will be loaded via AJAX)
                                let imagePreview = $('<img style="max-height: 30px; max-width: 200px; border: 1px solid #ddd; margin-left: 5px;">');
                                imagePreview.attr('data-image-id', imageId);
                                imageContainer.append(imagePreview);
                                
                                // Load image source
                                loadImagePreview(imageId, imagePreview);
                            } else {
                                // Show "choose image" button
                                let chooseBtn = $('<button style="padding: 3px 8px; font-size: 11px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer;">choose image</button>');
                                chooseBtn.click(function() {
                                    openImagePicker(row.location_id, imageContainer);
                                });
                                imageContainer.append(chooseBtn);
                            }
                            
                            td.append(imageContainer);
                        } else {
                            // Text field
                            let value = row[field] || '';
                            td.text(value);
                            
                            if (field !== 'location_id') { // Don't allow editing ID
                                td.attr('data-field', field);
                                td.attr('data-id', row.location_id);
                                td.css('cursor', 'text');
                                td.click(function() {
                                    startInlineEdit($(this), value, row.location_id, field);
                                });
                            }
                        }
                        
                        tr.append(td);
                    });
                    
                    tbody.append(tr);
                });
            }
            
            function startInlineEdit(cell, currentValue, id, field) {
                if (editingCell) return; // Only one cell at a time
                
                editingCell = { cell: cell, id: id, field: field, originalValue: currentValue };
                
                let input = $('<input type="text" style="width: 100%; padding: 4px; border: 1px solid #0073aa; background: #fff;">');
                input.val(currentValue);
                cell.empty().append(input);
                input.focus().select();
                
                input.keydown(function(e) {
                    if (e.key === 'Enter') {
                        saveInlineEdit();
                    } else if (e.key === 'Escape') {
                        cancelInlineEdit();
                    }
                });
                
                input.blur(function() {
                    saveInlineEdit();
                });
            }
            
            function saveInlineEdit() {
                if (!editingCell) return;
                
                let newValue = editingCell.cell.find('input').val();
                updateField(editingCell.id, editingCell.field, newValue);
                
                // Update local data
                let dataItem = allData.find(item => item.location_id == editingCell.id);
                if (dataItem) {
                    dataItem[editingCell.field] = newValue;
                }
                
                editingCell.cell.empty().text(newValue);
                editingCell = null;
            }
            
            function cancelInlineEdit() {
                if (!editingCell) return;
                
                editingCell.cell.empty().text(editingCell.originalValue);
                editingCell = null;
            }
            
            function updateField(id, field, value) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_locations_update_field',
                        nonce: '<?php echo wp_create_nonce('rup_locations_nonce'); ?>',
                        id: id,
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        if (!response.success) {
                            alert('Error updating field: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error updating field');
                    }
                });
            }
            
            function createInlineRow() {
                let tbody = $('#table-body');
                let newRow = $('<tr style="background: #ffffcc; border: 2px solid #0073aa;"></tr>');
                
                // Create empty row with inputs
                // Checkbox
                let checkboxTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"></td>');
                checkboxTd.append('<input type="checkbox" style="width: 20px; height: 20px;" disabled>');
                newRow.append(checkboxTd);
                
                // Location ID (auto-generated)
                newRow.append('<td style="padding: 8px; border: 1px solid #ddd; color: #999;">(auto)</td>');
                
                // Other fields
                ['location_name', 'location_placard', 'location_moniker', 'location_sobriquet', 
                 'street', 'city', 'state_code', 'zip_code', 'country', 'rel_image1_id'].forEach(function(field) {
                    let td = $('<td style="padding: 4px; border: 1px solid #ddd;"></td>');
                    let input;
                    if (field === 'rel_image1_id') {
                        input = $('<input type="number" style="width: 100%; padding: 4px; border: 1px solid #ccc;" placeholder="Image ID">');
                    } else {
                        input = $('<input type="text" style="width: 100%; padding: 4px; border: 1px solid #ccc;">');
                    }
                    input.attr('name', field);
                    td.append(input);
                    newRow.append(td);
                });
                
                // Pick image1 column (placeholder for inline creation)
                let pickImageTd = $('<td style="padding: 4px; border: 1px solid #ddd; text-align: center; color: #999;"></td>');
                pickImageTd.text('(set after save)');
                newRow.append(pickImageTd);
                
                // Boolean field
                let boolTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"></td>');
                boolTd.append('<input type="checkbox" name="is_pinned_location" style="width: 20px; height: 20px;">');
                newRow.append(boolTd);
                
                // Position field
                let positionTd = $('<td style="padding: 4px; border: 1px solid #ddd;"></td>');
                let positionInput = $('<input type="number" name="position_in_custom_order" style="width: 100%; padding: 4px; border: 1px solid #ccc;" placeholder="Order">');
                positionTd.append(positionInput);
                newRow.append(positionTd);
                
                // Add save/cancel buttons
                let actionTd = $('<td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: center;"></td>');
                actionTd.append('<button onclick="saveInlineRow(this)" style="margin-right: 5px; padding: 4px 12px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer;">Save</button>');
                actionTd.append('<button onclick="cancelInlineRow(this)" style="padding: 4px 12px; background: #666; color: white; border: none; border-radius: 3px; cursor: pointer;">Cancel</button>');
                newRow.append(actionTd);
                
                tbody.prepend(newRow);
            }
            
            window.saveInlineRow = function(btn) {
                let row = $(btn).closest('tr');
                let formData = {};
                
                row.find('input[name]').each(function() {
                    let field = $(this).attr('name');
                    let value = $(this).val();
                    if ($(this).attr('type') === 'checkbox') {
                        value = $(this).is(':checked') ? 1 : 0;
                    }
                    formData[field] = value;
                });
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_locations_create',
                        nonce: '<?php echo wp_create_nonce('rup_locations_nonce'); ?>',
                        data: formData
                    },
                    success: function(response) {
                        if (response.success) {
                            loadData(); // Reload all data
                        } else {
                            alert('Error creating location: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error creating location');
                    }
                });
            };
            
            window.cancelInlineRow = function(btn) {
                $(btn).closest('tr').remove();
            };
            
            function createLocationPopup() {
                let formData = {};
                $('#create-form input').each(function() {
                    let field = $(this).attr('name');
                    let value = $(this).val();
                    if ($(this).attr('type') === 'checkbox') {
                        value = $(this).is(':checked') ? 1 : 0;
                    }
                    formData[field] = value;
                });
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_locations_create',
                        nonce: '<?php echo wp_create_nonce('rup_locations_nonce'); ?>',
                        data: formData
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#create-modal').hide();
                            $('#create-form')[0].reset();
                            loadData();
                        } else {
                            alert('Error creating location: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error creating location');
                    }
                });
            }
            
            function updatePagination() {
                let totalPages = Math.ceil(filteredData.length / itemsPerPage);
                let pageNav = $('#page-nav, #page-nav-bottom');
                pageNav.empty();
                
                for (let i = 1; i <= totalPages && i <= 20; i++) {
                    let btn = $('<button>' + i + '</button>');
                    btn.css({
                        'border': 'none',
                        'padding': '10px 15px',
                        'background': i === currentPage ? '#0073aa' : '#f9f9f9',
                        'color': i === currentPage ? 'white' : 'black',
                        'cursor': 'pointer',
                        'font-size': '14px'
                    });
                    
                    if (i > 1) {
                        btn.css('border-left', '1px solid #ccc');
                    }
                    
                    btn.click(function() {
                        currentPage = i;
                        filterAndDisplay();
                    });
                    
                    pageNav.append(btn);
                }
            }
            
            function updateRecordInfo() {
                let start = (currentPage - 1) * itemsPerPage + 1;
                let end = Math.min(currentPage * itemsPerPage, filteredData.length);
                let info = start + '-' + end + ' of ' + filteredData.length + ' locations';
                if (selectedRows.size > 0) {
                    info += ' (' + selectedRows.size + ' selected)';
                }
                $('#record-info').text(info);
            }
            
            // Select all functionality
            window.toggleSelectAll = function() {
                let selectAll = $('#select-all');
                let isChecked = !selectAll.prop('checked');
                selectAll.prop('checked', isChecked);
                
                if (isChecked) {
                    filteredData.forEach(function(item) {
                        selectedRows.add(item.location_id);
                    });
                } else {
                    selectedRows.clear();
                }
                
                filterAndDisplay();
            };
            
            // Sort functionality
            $('th[data-sort]').click(function() {
                let field = $(this).data('sort');
                if (sortField === field) {
                    sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    sortField = field;
                    sortOrder = 'asc';
                }
                filterAndDisplay();
            });
            
            // Image picker functions
            function openImagePicker(locationId, container) {
                // Check if WordPress media uploader is available
                if (typeof wp !== 'undefined' && wp.media) {
                    let mediaUploader = wp.media({
                        title: 'Choose Image for Location',
                        button: {
                            text: 'Use This Image'
                        },
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });
                    
                    mediaUploader.on('select', function() {
                        let attachment = mediaUploader.state().get('selection').first().toJSON();
                        let imageId = attachment.id;
                        
                        // Update the rel_image1_id field
                        updateField(locationId, 'rel_image1_id', imageId);
                        
                        // Update local data
                        let dataItem = allData.find(item => item.location_id == locationId);
                        if (dataItem) {
                            dataItem.rel_image1_id = imageId;
                        }
                        
                        // Update the container display
                        container.empty();
                        
                        let assignBtn = $('<button style="padding: 3px 8px; font-size: 11px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer; white-space: nowrap;">assign new image</button>');
                        assignBtn.click(function() {
                            openImagePicker(locationId, container);
                        });
                        container.append(assignBtn);
                        
                        let imagePreview = $('<img style="max-height: 30px; max-width: 200px; border: 1px solid #ddd; margin-left: 5px;">');
                        imagePreview.attr('src', attachment.sizes?.thumbnail?.url || attachment.url);
                        container.append(imagePreview);
                    });
                    
                    mediaUploader.open();
                } else {
                    alert('WordPress media uploader not available. Please refresh the page.');
                }
            }
            
            function loadImagePreview(imageId, imageElement) {
                // Load image URL via AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_locations_get_image_url',
                        nonce: '<?php echo wp_create_nonce('rup_locations_nonce'); ?>',
                        image_id: imageId
                    },
                    success: function(response) {
                        if (response.success && response.data.url) {
                            imageElement.attr('src', response.data.url);
                        } else {
                            imageElement.attr('src', '');
                            imageElement.attr('alt', 'Image not found');
                        }
                    },
                    error: function() {
                        imageElement.attr('src', '');
                        imageElement.attr('alt', 'Error loading image');
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * rup_services_mar page - Services Management
     */
    public function rup_services_mar_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_services';
        
        // Handle AJAX requests
        if (isset($_POST['action'])) {
            $this->handle_services_ajax();
            return;
        }
        
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        
        ?>
        <div class="wrap" style="margin: 0; padding: 20px;">
            <h1 style="margin-bottom: 20px;">🔧 Zen Services Manager</h1>
            
            <!-- Control Bar -->
            <div style="background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button id="create-inline-btn" class="button button-primary">Create New (Inline)</button>
                        <button id="create-popup-btn" class="button button-secondary">Create New (Popup)</button>
                    </div>
                </div>
                
                <!-- Nubra Tableface Kite -->
                <div id="nubra-tableface-kite" style="margin-bottom: 15px;"></div>
                
                <!-- Pagination and Search Controls -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <!-- Items per page -->
                        <div style="display: flex; gap: 0; border: 1px solid #ccc;">
                            <button class="per-page-btn" data-value="10" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px;">10</button>
                            <button class="per-page-btn" data-value="20" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">20</button>
                            <button class="per-page-btn" data-value="50" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">50</button>
                            <button class="per-page-btn active" data-value="100" style="border: none; padding: 10px 15px; background: #0073aa; color: white; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">100</button>
                            <button class="per-page-btn" data-value="200" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">200</button>
                            <button class="per-page-btn" data-value="500" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">500</button>
                            <button class="per-page-btn" data-value="all" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc; border-top-right-radius: 4px; border-bottom-right-radius: 4px;">All</button>
                        </div>
                        
                        <!-- Page navigation -->
                        <div id="page-nav" style="display: flex; gap: 0; border: 1px solid #ccc;">
                            <!-- Dynamic page buttons will go here -->
                        </div>
                    </div>
                    
                    <!-- Search -->
                    <div style="position: relative;">
                        <input type="text" id="search-box" placeholder="Search services..." style="padding: 8px 40px 8px 12px; border: 1px solid #ccc; border-radius: 4px; width: 250px; font-size: 14px;">
                        <button id="clear-search" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #ffeb3b; border: none; padding: 4px 8px; font-size: 12px; font-weight: bold; border-radius: 3px; cursor: pointer;">CL</button>
                    </div>
                </div>
            </div>
            
            <!-- Main Table -->
            <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                <div style="overflow-x: auto;">
                    <table id="services-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f0f0f0; width: 50px; cursor: pointer;" onclick="toggleSelectAll()">
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                        <input type="checkbox" id="select-all" style="width: 20px; height: 20px;">
                                    </div>
                                </th>
                                <th data-sort="service_id" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">service_id</th>
                                <th data-sort="service_name" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">service_name</th>
                                <th data-sort="service_placard" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">service_placard</th>
                                <th data-sort="service_moniker" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">service_moniker</th>
                                <th data-sort="service_sobriquet" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">service_sobriquet</th>
                                <th data-sort="description1_short" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">description1_short</th>
                                <th data-sort="description1_long" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">description1_long</th>
                                <th data-sort="rel_image1_id" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">rel_image1_id</th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">pick image1</th>
                                <th data-sort="is_pinned_service" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">is_pinned_service</th>
                                <th data-sort="position_in_custom_order" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; cursor: pointer; background: #f8f9fa;">position_in_custom_order</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Bottom Pagination -->
            <div style="background: white; border: 1px solid #ddd; padding: 15px; margin-top: 20px; border-radius: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <div id="record-info" style="font-size: 14px; color: #666;"></div>
                        
                        <!-- Bottom Items per page -->
                        <div style="display: flex; gap: 0; border: 1px solid #ccc;">
                            <button class="per-page-btn-bottom" data-value="10" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px;">10</button>
                            <button class="per-page-btn-bottom" data-value="20" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">20</button>
                            <button class="per-page-btn-bottom" data-value="50" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">50</button>
                            <button class="per-page-btn-bottom active" data-value="100" style="border: none; padding: 10px 15px; background: #0073aa; color: white; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">100</button>
                            <button class="per-page-btn-bottom" data-value="200" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">200</button>
                            <button class="per-page-btn-bottom" data-value="500" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc;">500</button>
                            <button class="per-page-btn-bottom" data-value="all" style="border: none; padding: 10px 15px; background: #f9f9f9; cursor: pointer; font-size: 14px; border-left: 1px solid #ccc; border-top-right-radius: 4px; border-bottom-right-radius: 4px;">All</button>
                        </div>
                        
                        <!-- Bottom Page navigation -->
                        <div id="page-nav-bottom" style="display: flex; gap: 0; border: 1px solid #ccc;">
                            <!-- Dynamic page buttons will go here -->
                        </div>
                    </div>
                    
                    <!-- Bottom Search -->
                    <div style="position: relative;">
                        <input type="text" id="search-box-bottom" placeholder="Search services..." style="padding: 8px 40px 8px 12px; border: 1px solid #ccc; border-radius: 4px; width: 250px; font-size: 14px;">
                        <button id="clear-search-bottom" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #ffeb3b; border: none; padding: 4px 8px; font-size: 12px; font-weight: bold; border-radius: 3px; cursor: pointer;">CL</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Create Popup Modal -->
        <div id="create-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 700px; max-height: 80vh; overflow-y: auto;">
                <h2 style="margin-top: 0;">Create New Service</h2>
                <form id="create-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Service Name:</label>
                            <input type="text" name="service_name" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Service Placard:</label>
                            <input type="text" name="service_placard" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Service Moniker:</label>
                            <input type="text" name="service_moniker" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Service Sobriquet:</label>
                            <input type="text" name="service_sobriquet" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Short Description:</label>
                            <textarea name="description1_short" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Long Description:</label>
                            <textarea name="description1_long" rows="5" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Related Image ID:</label>
                            <input type="number" name="rel_image1_id" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Position Order:</label>
                            <input type="number" name="position_in_custom_order" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: bold;">
                                <input type="checkbox" name="is_pinned_service" style="width: 20px; height: 20px;">
                                Is Pinned Service
                            </label>
                        </div>
                    </div>
                    <div style="text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
                        <button type="button" id="cancel-create" style="margin-right: 10px; padding: 8px 20px; border: 1px solid #ccc; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button type="submit" style="padding: 8px 20px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer;">Create Service</button>
                    </div>
                </form>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let itemsPerPage = 100;
            let searchTerm = '';
            let sortField = 'service_id';
            let sortOrder = 'asc';
            let selectedRows = new Set();
            let allData = [];
            let filteredData = [];
            let editingCell = null;
            
            // Load Nubra Tableface Kite
            loadNubraTablefaceKite();
            
            // Initial load
            loadData();
            
            // Search functionality
            $('#search-box, #search-box-bottom').on('input', function() {
                searchTerm = $(this).val();
                $('#search-box, #search-box-bottom').val(searchTerm); // Sync both search boxes
                currentPage = 1;
                filterAndDisplay();
            });
            
            $('#clear-search, #clear-search-bottom').click(function() {
                searchTerm = '';
                $('#search-box, #search-box-bottom').val('');
                currentPage = 1;
                filterAndDisplay();
            });
            
            // Per page buttons
            $('.per-page-btn, .per-page-btn-bottom').click(function() {
                itemsPerPage = $(this).data('value') === 'all' ? 999999 : $(this).data('value');
                $('.per-page-btn, .per-page-btn-bottom').removeClass('active').css({'background': '#f9f9f9', 'color': 'black'});
                $(`.per-page-btn[data-value="${$(this).data('value')}"], .per-page-btn-bottom[data-value="${$(this).data('value')}"]`).addClass('active').css({'background': '#0073aa', 'color': 'white'});
                currentPage = 1;
                filterAndDisplay();
            });
            
            // Create buttons
            $('#create-inline-btn').click(function() {
                createInlineRow();
            });
            
            $('#create-popup-btn').click(function() {
                $('#create-modal').show();
            });
            
            $('#cancel-create').click(function() {
                $('#create-modal').hide();
                $('#create-form')[0].reset();
            });
            
            $('#create-form').submit(function(e) {
                e.preventDefault();
                createServicePopup();
            });
            
            // Load data function
            function loadData() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_services_get_data',
                        nonce: '<?php echo wp_create_nonce('rup_services_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            allData = response.data;
                            filterAndDisplay();
                        }
                    },
                    error: function() {
                        alert('Error loading data');
                    }
                });
            }
            
            function loadNubraTablefaceKite() {
                $('#nubra-tableface-kite').html('<div style="color: #666; font-size: 12px; font-style: italic; padding: 5px 0; border-left: 3px solid #0073aa; padding-left: 10px;">nubra-tableface-kite: zen_services comprehensive management grid</div>');
            }
            
            function filterAndDisplay() {
                // Filter data
                if (searchTerm === '') {
                    filteredData = allData;
                } else {
                    filteredData = allData.filter(item => {
                        return Object.values(item).some(value => 
                            value && value.toString().toLowerCase().includes(searchTerm.toLowerCase())
                        );
                    });
                }
                
                // Sort data
                filteredData.sort((a, b) => {
                    let aVal = a[sortField] || '';
                    let bVal = b[sortField] || '';
                    if (sortOrder === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });
                
                displayTable();
                updatePagination();
                updateRecordInfo();
            }
            
            function displayTable() {
                let startIndex = (currentPage - 1) * itemsPerPage;
                let endIndex = startIndex + itemsPerPage;
                let pageData = filteredData.slice(startIndex, endIndex);
                
                let tbody = $('#table-body');
                tbody.empty();
                
                pageData.forEach(function(row) {
                    let tr = $('<tr style="cursor: pointer;"></tr>');
                    tr.hover(function() {
                        $(this).css('background-color', '#f9f9f9');
                    }, function() {
                        $(this).css('background-color', '');
                    });
                    
                    // Checkbox column
                    let checkboxTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center; cursor: pointer;"></td>');
                    let checkbox = $('<input type="checkbox" style="width: 20px; height: 20px;">');
                    checkbox.prop('checked', selectedRows.has(row.service_id));
                    checkbox.change(function() {
                        if (this.checked) {
                            selectedRows.add(row.service_id);
                        } else {
                            selectedRows.delete(row.service_id);
                        }
                    });
                    checkboxTd.click(function(e) {
                        if (e.target.type !== 'checkbox') {
                            checkbox.prop('checked', !checkbox.prop('checked')).change();
                        }
                    });
                    checkboxTd.append(checkbox);
                    tr.append(checkboxTd);
                    
                    // Data columns
                    ['service_id', 'service_name', 'service_placard', 'service_moniker', 
                     'service_sobriquet', 'description1_short', 'description1_long', 'rel_image1_id', 
                     'pick_image1', 'is_pinned_service', 'position_in_custom_order'].forEach(function(field) {
                        let td = $('<td style="padding: 8px; border: 1px solid #ddd;"></td>');
                        
                        if (field === 'is_pinned_service') {
                            // Boolean toggle switch
                            let toggleSwitch = $('<label style="position: relative; display: inline-block; width: 50px; height: 24px;"><input type="checkbox" style="opacity: 0; width: 0; height: 0;"><span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px;"></span></label>');
                            let checkbox = toggleSwitch.find('input');
                            let slider = toggleSwitch.find('span');
                            
                            checkbox.prop('checked', row[field] == 1);
                            if (row[field] == 1) {
                                slider.css('background-color', '#2196F3');
                                slider.append('<span style="position: absolute; content: ""; height: 18px; width: 18px; left: 26px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>');
                            } else {
                                slider.append('<span style="position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>');
                            }
                            
                            checkbox.change(function() {
                                updateField(row.service_id, 'is_pinned_service', this.checked ? 1 : 0);
                                row.is_pinned_service = this.checked ? 1 : 0;
                            });
                            
                            td.append(toggleSwitch);
                        } else if (field === 'pick_image1') {
                            // Image picker widget
                            let imageId = row['rel_image1_id'];
                            let imageContainer = $('<div style="display: flex; align-items: center; gap: 5px;"></div>');
                            
                            if (imageId && imageId > 0) {
                                // Show existing image with "assign new image" button
                                let assignBtn = $('<button style="padding: 3px 8px; font-size: 11px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer; white-space: nowrap;">assign new image</button>');
                                assignBtn.click(function() {
                                    openImagePicker(row.service_id, imageContainer);
                                });
                                imageContainer.append(assignBtn);
                                
                                // Add image preview (will be loaded via AJAX)
                                let imagePreview = $('<img style="max-height: 30px; max-width: 200px; border: 1px solid #ddd; margin-left: 5px;">');
                                imagePreview.attr('data-image-id', imageId);
                                imageContainer.append(imagePreview);
                                
                                // Load image source
                                loadImagePreview(imageId, imagePreview);
                            } else {
                                // Show "choose image" button
                                let chooseBtn = $('<button style="padding: 3px 8px; font-size: 11px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer;">choose image</button>');
                                chooseBtn.click(function() {
                                    openImagePicker(row.service_id, imageContainer);
                                });
                                imageContainer.append(chooseBtn);
                            }
                            
                            td.append(imageContainer);
                        } else {
                            // Text field
                            let value = row[field] || '';
                            
                            // Truncate long text for display
                            if ((field === 'description1_short' || field === 'description1_long') && value.length > 50) {
                                td.text(value.substring(0, 50) + '...');
                                td.attr('title', value);
                            } else {
                                td.text(value);
                            }
                            
                            if (field !== 'service_id') { // Don't allow editing ID
                                td.attr('data-field', field);
                                td.attr('data-id', row.service_id);
                                td.css('cursor', 'text');
                                td.click(function() {
                                    startInlineEdit($(this), value, row.service_id, field);
                                });
                            }
                        }
                        
                        tr.append(td);
                    });
                    
                    tbody.append(tr);
                });
            }
            
            function startInlineEdit(cell, currentValue, id, field) {
                if (editingCell) return; // Only one cell at a time
                
                editingCell = { cell: cell, id: id, field: field, originalValue: currentValue };
                
                let input;
                if (field === 'description1_short' || field === 'description1_long') {
                    input = $('<textarea style="width: 100%; padding: 4px; border: 1px solid #0073aa; background: #fff; min-height: 60px; resize: vertical;"></textarea>');
                } else {
                    input = $('<input type="text" style="width: 100%; padding: 4px; border: 1px solid #0073aa; background: #fff;">');
                }
                
                input.val(currentValue);
                cell.empty().append(input);
                input.focus().select();
                
                input.keydown(function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        saveInlineEdit();
                    } else if (e.key === 'Escape') {
                        cancelInlineEdit();
                    }
                });
                
                input.blur(function() {
                    saveInlineEdit();
                });
            }
            
            function saveInlineEdit() {
                if (!editingCell) return;
                
                let newValue = editingCell.cell.find('input, textarea').val();
                updateField(editingCell.id, editingCell.field, newValue);
                
                // Update local data
                let dataItem = allData.find(item => item.service_id == editingCell.id);
                if (dataItem) {
                    dataItem[editingCell.field] = newValue;
                }
                
                // Display truncated text if needed
                let displayValue = newValue;
                if ((editingCell.field === 'description1_short' || editingCell.field === 'description1_long') && newValue.length > 50) {
                    displayValue = newValue.substring(0, 50) + '...';
                    editingCell.cell.attr('title', newValue);
                }
                
                editingCell.cell.empty().text(displayValue);
                editingCell = null;
            }
            
            function cancelInlineEdit() {
                if (!editingCell) return;
                
                let displayValue = editingCell.originalValue;
                if ((editingCell.field === 'description1_short' || editingCell.field === 'description1_long') && editingCell.originalValue.length > 50) {
                    displayValue = editingCell.originalValue.substring(0, 50) + '...';
                    editingCell.cell.attr('title', editingCell.originalValue);
                }
                
                editingCell.cell.empty().text(displayValue);
                editingCell = null;
            }
            
            function updateField(id, field, value) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_services_update_field',
                        nonce: '<?php echo wp_create_nonce('rup_services_nonce'); ?>',
                        id: id,
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        if (!response.success) {
                            alert('Error updating field: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error updating field');
                    }
                });
            }
            
            function createInlineRow() {
                let tbody = $('#table-body');
                let newRow = $('<tr style="background: #ffffcc; border: 2px solid #0073aa;"></tr>');
                
                // Create empty row with inputs
                // Checkbox
                let checkboxTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"></td>');
                checkboxTd.append('<input type="checkbox" style="width: 20px; height: 20px;" disabled>');
                newRow.append(checkboxTd);
                
                // Service ID (auto-generated)
                newRow.append('<td style="padding: 8px; border: 1px solid #ddd; color: #999;">(auto)</td>');
                
                // Text fields
                ['service_name', 'service_placard', 'service_moniker', 'service_sobriquet'].forEach(function(field) {
                    let td = $('<td style="padding: 4px; border: 1px solid #ddd;"></td>');
                    let input = $('<input type="text" style="width: 100%; padding: 4px; border: 1px solid #ccc;">');
                    input.attr('name', field);
                    td.append(input);
                    newRow.append(td);
                });
                
                // Description fields (textareas)
                ['description1_short', 'description1_long'].forEach(function(field) {
                    let td = $('<td style="padding: 4px; border: 1px solid #ddd;"></td>');
                    let textarea = $('<textarea style="width: 100%; padding: 4px; border: 1px solid #ccc; height: 40px; resize: vertical;"></textarea>');
                    textarea.attr('name', field);
                    td.append(textarea);
                    newRow.append(td);
                });
                
                // Related image ID
                let imageIdTd = $('<td style="padding: 4px; border: 1px solid #ddd;"></td>');
                let imageIdInput = $('<input type="number" name="rel_image1_id" style="width: 100%; padding: 4px; border: 1px solid #ccc;" placeholder="Image ID">');
                imageIdTd.append(imageIdInput);
                newRow.append(imageIdTd);
                
                // Pick image1 column (placeholder for inline creation)
                let pickImageTd = $('<td style="padding: 4px; border: 1px solid #ddd; text-align: center; color: #999;"></td>');
                pickImageTd.text('(set after save)');
                newRow.append(pickImageTd);
                
                // Boolean field
                let boolTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"></td>');
                boolTd.append('<input type="checkbox" name="is_pinned_service" style="width: 20px; height: 20px;">');
                newRow.append(boolTd);
                
                // Position field
                let positionTd = $('<td style="padding: 4px; border: 1px solid #ddd;"></td>');
                let positionInput = $('<input type="number" name="position_in_custom_order" style="width: 100%; padding: 4px; border: 1px solid #ccc;" placeholder="Order">');
                positionTd.append(positionInput);
                newRow.append(positionTd);
                
                // Add save/cancel buttons
                let actionTd = $('<td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: center;"></td>');
                actionTd.append('<button onclick="saveInlineRow(this)" style="margin-right: 5px; padding: 4px 12px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer;">Save</button>');
                actionTd.append('<button onclick="cancelInlineRow(this)" style="padding: 4px 12px; background: #666; color: white; border: none; border-radius: 3px; cursor: pointer;">Cancel</button>');
                newRow.append(actionTd);
                
                tbody.prepend(newRow);
            }
            
            window.saveInlineRow = function(btn) {
                let row = $(btn).closest('tr');
                let formData = {};
                
                row.find('input[name], textarea[name]').each(function() {
                    let field = $(this).attr('name');
                    let value = $(this).val();
                    if ($(this).attr('type') === 'checkbox') {
                        value = $(this).is(':checked') ? 1 : 0;
                    }
                    formData[field] = value;
                });
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_services_create',
                        nonce: '<?php echo wp_create_nonce('rup_services_nonce'); ?>',
                        data: formData
                    },
                    success: function(response) {
                        if (response.success) {
                            loadData(); // Reload all data
                        } else {
                            alert('Error creating service: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error creating service');
                    }
                });
            };
            
            window.cancelInlineRow = function(btn) {
                $(btn).closest('tr').remove();
            };
            
            function createServicePopup() {
                let formData = {};
                $('#create-form input, #create-form textarea').each(function() {
                    let field = $(this).attr('name');
                    let value = $(this).val();
                    if ($(this).attr('type') === 'checkbox') {
                        value = $(this).is(':checked') ? 1 : 0;
                    }
                    formData[field] = value;
                });
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_services_create',
                        nonce: '<?php echo wp_create_nonce('rup_services_nonce'); ?>',
                        data: formData
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#create-modal').hide();
                            $('#create-form')[0].reset();
                            loadData();
                        } else {
                            alert('Error creating service: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error creating service');
                    }
                });
            }
            
            function updatePagination() {
                let totalPages = Math.ceil(filteredData.length / itemsPerPage);
                let pageNav = $('#page-nav, #page-nav-bottom');
                pageNav.empty();
                
                for (let i = 1; i <= totalPages && i <= 20; i++) {
                    let btn = $('<button>' + i + '</button>');
                    btn.css({
                        'border': 'none',
                        'padding': '10px 15px',
                        'background': i === currentPage ? '#0073aa' : '#f9f9f9',
                        'color': i === currentPage ? 'white' : 'black',
                        'cursor': 'pointer',
                        'font-size': '14px'
                    });
                    
                    if (i > 1) {
                        btn.css('border-left', '1px solid #ccc');
                    }
                    
                    btn.click(function() {
                        currentPage = i;
                        filterAndDisplay();
                    });
                    
                    pageNav.append(btn);
                }
            }
            
            function updateRecordInfo() {
                let start = (currentPage - 1) * itemsPerPage + 1;
                let end = Math.min(currentPage * itemsPerPage, filteredData.length);
                let info = start + '-' + end + ' of ' + filteredData.length + ' services';
                if (selectedRows.size > 0) {
                    info += ' (' + selectedRows.size + ' selected)';
                }
                $('#record-info').text(info);
            }
            
            // Select all functionality
            window.toggleSelectAll = function() {
                let selectAll = $('#select-all');
                let isChecked = !selectAll.prop('checked');
                selectAll.prop('checked', isChecked);
                
                if (isChecked) {
                    filteredData.forEach(function(item) {
                        selectedRows.add(item.service_id);
                    });
                } else {
                    selectedRows.clear();
                }
                
                filterAndDisplay();
            };
            
            // Sort functionality
            $('th[data-sort]').click(function() {
                let field = $(this).data('sort');
                if (sortField === field) {
                    sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    sortField = field;
                    sortOrder = 'asc';
                }
                filterAndDisplay();
            });
            
            // Image picker functions
            function openImagePicker(serviceId, container) {
                // Check if WordPress media uploader is available
                if (typeof wp !== 'undefined' && wp.media) {
                    let mediaUploader = wp.media({
                        title: 'Choose Image for Service',
                        button: {
                            text: 'Use This Image'
                        },
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });
                    
                    mediaUploader.on('select', function() {
                        let attachment = mediaUploader.state().get('selection').first().toJSON();
                        let imageId = attachment.id;
                        
                        // Update the rel_image1_id field
                        updateField(serviceId, 'rel_image1_id', imageId);
                        
                        // Update local data
                        let dataItem = allData.find(item => item.service_id == serviceId);
                        if (dataItem) {
                            dataItem.rel_image1_id = imageId;
                        }
                        
                        // Update the container display
                        container.empty();
                        
                        let assignBtn = $('<button style="padding: 3px 8px; font-size: 11px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer; white-space: nowrap;">assign new image</button>');
                        assignBtn.click(function() {
                            openImagePicker(serviceId, container);
                        });
                        container.append(assignBtn);
                        
                        let imagePreview = $('<img style="max-height: 30px; max-width: 200px; border: 1px solid #ddd; margin-left: 5px;">');
                        imagePreview.attr('src', attachment.sizes?.thumbnail?.url || attachment.url);
                        container.append(imagePreview);
                    });
                    
                    mediaUploader.open();
                } else {
                    alert('WordPress media uploader not available. Please refresh the page.');
                }
            }
            
            function loadImagePreview(imageId, imageElement) {
                // Load image URL via AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_services_get_image_url',
                        nonce: '<?php echo wp_create_nonce('rup_services_nonce'); ?>',
                        image_id: imageId
                    },
                    success: function(response) {
                        if (response.success && response.data.url) {
                            imageElement.attr('src', response.data.url);
                        } else {
                            imageElement.attr('src', '');
                            imageElement.attr('alt', 'Image not found');
                        }
                    },
                    error: function() {
                        imageElement.attr('src', '');
                        imageElement.attr('alt', 'Error loading image');
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * rup_service_tags_mar page
     */
    public function rup_service_tags_mar_page() {
        echo '<div class="wrap">';
        echo '<h1><strong>rup_service_tags_mar</strong></h1>';
        echo '</div>';
    }
    
    /**
     * rup_location_tags_mar page
     */
    public function rup_location_tags_mar_page() {
        echo '<div class="wrap">';
        echo '<h1><strong>rup_location_tags_mar</strong></h1>';
        echo '</div>';
    }
    
    /**
     * rup_horse_class_page - Database Horse Class Management
     */
    public function rup_horse_class_page() {
        echo '<div class="wrap">';
        echo '<h1>Database Horse Class Management</h1>';
        echo '<div class="card" style="max-width: 600px;">';
        echo '<h2>Zen Tables Management</h2>';
        echo '<p>This tool rebuilds the <code>_zen_services</code> and <code>_zen_locations</code> tables in your WordPress database.</p>';
        echo '<p><strong>What it does:</strong></p>';
        echo '<ul>';
        echo '<li>Recreates both zen tables with proper schema</li>';
        echo '<li>Preserves existing data if tables already exist</li>';
        echo '<li>Updates table structure if schema has changed</li>';
        echo '</ul>';
        echo '<div style="margin: 20px 0;">';
        echo '<button id="rebuild-zen-tables" class="button button-primary">Rebuild Zen Tables</button>';
        echo '</div>';
        echo '<div id="rebuild-status" style="margin-top: 15px;"></div>';
        echo '</div>';
        echo '</div>';
        
        // Add JavaScript for button functionality
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#rebuild-zen-tables').on('click', function(e) {
                e.preventDefault();
                
                if (!confirm('This will rebuild the _zen_services and _zen_locations tables. Continue?')) {
                    return;
                }
                
                var $button = $(this);
                var originalText = $button.text();
                
                $button.text('Rebuilding...').prop('disabled', true);
                $('#rebuild-status').html('<p style="color: #0073aa;">Processing...</p>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'snefuru_rebuild_zen_tables',
                        nonce: '<?php echo wp_create_nonce('snefuru_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#rebuild-status').html('<p style="color: #46b450;">✅ ' + response.data.message + '</p>');
                        } else {
                            $('#rebuild-status').html('<p style="color: #dc3232;">❌ Error: ' + (response.data.message || 'Failed to rebuild tables') + '</p>');
                        }
                    },
                    error: function() {
                        $('#rebuild-status').html('<p style="color: #dc3232;">❌ Failed to rebuild tables. Please check server logs.</p>');
                    },
                    complete: function() {
                        $button.text(originalText).prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX: Get locations data
     */
    public function rup_locations_get_data() {
        check_ajax_referer('rup_locations_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_locations';
        
        try {
            $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY location_id ASC", ARRAY_A);
            
            if ($wpdb->last_error) {
                wp_send_json_error('Database error: ' . $wpdb->last_error);
                return;
            }
            
            // Convert boolean values to proper format
            foreach ($results as &$row) {
                $row['is_pinned_location'] = (int) $row['is_pinned_location'];
            }
            
            wp_send_json_success($results);
        } catch (Exception $e) {
            wp_send_json_error('Error loading locations: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Update location field
     */
    public function rup_locations_update_field() {
        check_ajax_referer('rup_locations_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $field = sanitize_text_field($_POST['field']);
        $value = $_POST['value'];
        
        // Validate field name
        $allowed_fields = [
            'location_name', 'location_placard', 'location_moniker', 'location_sobriquet',
            'street', 'city', 'state_code', 'zip_code', 'country', 'rel_image1_id',
            'is_pinned_location', 'position_in_custom_order'
        ];
        
        if (!in_array($field, $allowed_fields)) {
            wp_send_json_error('Invalid field name');
            return;
        }
        
        // Sanitize value based on field type
        if ($field === 'rel_image1_id' || $field === 'position_in_custom_order') {
            $value = $value === '' ? NULL : intval($value);
        } elseif ($field === 'is_pinned_location') {
            $value = $value ? 1 : 0;
        } else {
            $value = $value === '' ? NULL : sanitize_text_field($value);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_locations';
        
        try {
            $result = $wpdb->update(
                $table_name,
                array($field => $value),
                array('location_id' => $id),
                null,
                array('%d')
            );
            
            if ($result === false) {
                wp_send_json_error('Database update failed: ' . $wpdb->last_error);
                return;
            }
            
            wp_send_json_success('Field updated successfully');
        } catch (Exception $e) {
            wp_send_json_error('Error updating field: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Create new location
     */
    public function rup_locations_create() {
        check_ajax_referer('rup_locations_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $data = $_POST['data'];
        
        // Sanitize and validate data
        $insert_data = array();
        
        $text_fields = ['location_name', 'location_placard', 'location_moniker', 'location_sobriquet', 'street', 'city', 'state_code', 'zip_code', 'country'];
        $int_fields = ['rel_image1_id', 'position_in_custom_order'];
        $bool_fields = ['is_pinned_location'];
        
        foreach ($text_fields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $insert_data[$field] = sanitize_text_field($data[$field]);
            }
        }
        
        foreach ($int_fields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $insert_data[$field] = intval($data[$field]);
            }
        }
        
        foreach ($bool_fields as $field) {
            if (isset($data[$field])) {
                $insert_data[$field] = $data[$field] ? 1 : 0;
            }
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_locations';
        
        try {
            $result = $wpdb->insert($table_name, $insert_data);
            
            if ($result === false) {
                wp_send_json_error('Database insert failed: ' . $wpdb->last_error);
                return;
            }
            
            $new_id = $wpdb->insert_id;
            wp_send_json_success(array(
                'message' => 'Location created successfully',
                'id' => $new_id
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error creating location: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Get image URL by ID
     */
    public function rup_locations_get_image_url() {
        check_ajax_referer('rup_locations_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $image_id = intval($_POST['image_id']);
        
        if (!$image_id) {
            wp_send_json_error('Invalid image ID');
            return;
        }
        
        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
        
        if (!$image_url) {
            // Try full size if thumbnail doesn't exist
            $image_url = wp_get_attachment_image_url($image_id, 'full');
        }
        
        if ($image_url) {
            wp_send_json_success(array(
                'url' => $image_url,
                'id' => $image_id
            ));
        } else {
            wp_send_json_error('Image not found');
        }
    }
    
    /**
     * AJAX: Get services data
     */
    public function rup_services_get_data() {
        check_ajax_referer('rup_services_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_services';
        
        try {
            $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY service_id ASC", ARRAY_A);
            
            if ($wpdb->last_error) {
                wp_send_json_error('Database error: ' . $wpdb->last_error);
                return;
            }
            
            // Convert boolean values to proper format
            foreach ($results as &$row) {
                $row['is_pinned_service'] = (int) $row['is_pinned_service'];
            }
            
            wp_send_json_success($results);
        } catch (Exception $e) {
            wp_send_json_error('Error loading services: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Update service field
     */
    public function rup_services_update_field() {
        check_ajax_referer('rup_services_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $field = sanitize_text_field($_POST['field']);
        $value = $_POST['value'];
        
        // Validate field name
        $allowed_fields = [
            'service_name', 'service_placard', 'service_moniker', 'service_sobriquet',
            'description1_short', 'description1_long', 'rel_image1_id',
            'is_pinned_service', 'position_in_custom_order'
        ];
        
        if (!in_array($field, $allowed_fields)) {
            wp_send_json_error('Invalid field name');
            return;
        }
        
        // Sanitize value based on field type
        if ($field === 'rel_image1_id' || $field === 'position_in_custom_order') {
            $value = $value === '' ? NULL : intval($value);
        } elseif ($field === 'is_pinned_service') {
            $value = $value ? 1 : 0;
        } else {
            $value = $value === '' ? NULL : sanitize_text_field($value);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_services';
        
        try {
            $result = $wpdb->update(
                $table_name,
                array($field => $value),
                array('service_id' => $id),
                null,
                array('%d')
            );
            
            if ($result === false) {
                wp_send_json_error('Database update failed: ' . $wpdb->last_error);
                return;
            }
            
            wp_send_json_success('Field updated successfully');
        } catch (Exception $e) {
            wp_send_json_error('Error updating field: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Create new service
     */
    public function rup_services_create() {
        check_ajax_referer('rup_services_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $data = $_POST['data'];
        
        // Sanitize and validate data
        $insert_data = array();
        
        $text_fields = ['service_name', 'service_placard', 'service_moniker', 'service_sobriquet', 'description1_short', 'description1_long'];
        $int_fields = ['rel_image1_id', 'position_in_custom_order'];
        $bool_fields = ['is_pinned_service'];
        
        foreach ($text_fields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $insert_data[$field] = sanitize_text_field($data[$field]);
            }
        }
        
        foreach ($int_fields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $insert_data[$field] = intval($data[$field]);
            }
        }
        
        foreach ($bool_fields as $field) {
            if (isset($data[$field])) {
                $insert_data[$field] = $data[$field] ? 1 : 0;
            }
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_services';
        
        try {
            $result = $wpdb->insert($table_name, $insert_data);
            
            if ($result === false) {
                wp_send_json_error('Database insert failed: ' . $wpdb->last_error);
                return;
            }
            
            $new_id = $wpdb->insert_id;
            wp_send_json_success(array(
                'message' => 'Service created successfully',
                'id' => $new_id
            ));
        } catch (Exception $e) {
            wp_send_json_error('Error creating service: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Get image URL by ID for services
     */
    public function rup_services_get_image_url() {
        check_ajax_referer('rup_services_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $image_id = intval($_POST['image_id']);
        
        if (!$image_id) {
            wp_send_json_error('Invalid image ID');
            return;
        }
        
        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
        
        if (!$image_url) {
            // Try full size if thumbnail doesn't exist
            $image_url = wp_get_attachment_image_url($image_id, 'full');
        }
        
        if ($image_url) {
            wp_send_json_success(array(
                'url' => $image_url,
                'id' => $image_id
            ));
        } else {
            wp_send_json_error('Image not found');
        }
    }
} 