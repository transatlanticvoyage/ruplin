<?php

class Snefuru_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        
        // Early admin notice suppression for all snefuruplin pages
        add_action('current_screen', array($this, 'maybe_suppress_admin_notices'), 1);
        add_action('admin_init', array($this, 'early_notice_suppression'), 1);
        
        // Ultra-early suppression that runs on plugins_loaded
        add_action('plugins_loaded', array($this, 'ultra_early_notice_suppression'), 999);
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
        
        // Driggs management AJAX actions
        add_action('wp_ajax_rup_driggs_get_data', array($this, 'rup_driggs_get_data'));
        add_action('wp_ajax_rup_driggs_update_field', array($this, 'rup_driggs_update_field'));
        
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
            'rup_driggs_mar',
            'rup_driggs_mar',
            'manage_options',
            'rup_driggs_mar',
            array($this, 'rup_driggs_mar_page')
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
        
        add_submenu_page(
            'snefuru',
            'document_outlook_aug9',
            'document_outlook_aug9',
            'manage_options',
            'document_outlook_aug9',
            array($this, 'document_outlook_aug9_page')
        );
        
        add_submenu_page(
            'snefuru',
            'dynamic_images_man',
            'dynamic_images_man',
            'manage_options',
            'dynamic_images_man',
            array($this, 'dynamic_images_man_page')
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
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        echo '<div class="wrap">';
        echo '<h1>KenliSidebarLinks</h1>';
        echo '<p>This is a placeholder menu item.</p>';
        echo '</div>';
    }
    
    /**
     * Main admin dashboard page
     */
    public function admin_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
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
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
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
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
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
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
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
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
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
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
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
        
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        // Handle AJAX requests
        if (isset($_POST['action'])) {
            $this->handle_locations_ajax();
            return;
        }
        
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <div style="padding: 20px;">
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
            </div> <!-- Close padding wrapper -->
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
                        let td = $('<td style="padding: 8px; border: 1px solid #ddd; position: relative;"></td>');
                        
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
                        
                        // Add shortcode copy button to all cells
                        let copyButton = $('<div class="shortcode-copy-btn" style="position: absolute; top: 0; right: 0; width: 10px; height: 100%; background: white; border: 1px solid #999; z-index: 10; cursor: pointer; font-size: 11px; color: #666; display: flex; align-items: center; justify-content: center;">SH</div>');
                        
                        copyButton.hover(function() {
                            $(this).css('background-color', '#ffeb3b');
                        }, function() {
                            $(this).css('background-color', 'white');
                        });
                        
                        copyButton.click(function(e) {
                            e.stopPropagation();
                            let shortcode = generateLocationShortcode(field, row);
                            copyToClipboard(shortcode);
                            showCopySuccess($(this));
                        });
                        
                        td.append(copyButton);
                        
                        tr.append(td);
                    });
                    
                    tbody.append(tr);
                });
            }
            
            function startInlineEdit(cell, currentValue, id, field) {
                if (editingCell) return; // Only one cell at a time
                
                editingCell = { cell: cell, id: id, field: field, originalValue: currentValue };
                
                // Preserve the copy button
                let copyButton = cell.find('.shortcode-copy-btn').detach();
                
                let input = $('<input type="text" style="width: calc(100% - 12px); padding: 4px; border: 1px solid #0073aa; background: #fff;">');
                input.val(currentValue);
                cell.empty().append(input);
                cell.append(copyButton); // Re-add the copy button
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
                
                // Preserve the copy button
                let copyButton = editingCell.cell.find('.shortcode-copy-btn').detach();
                editingCell.cell.empty().text(newValue);
                editingCell.cell.append(copyButton); // Re-add the copy button
                editingCell = null;
            }
            
            function cancelInlineEdit() {
                if (!editingCell) return;
                
                // Preserve the copy button
                let copyButton = editingCell.cell.find('.shortcode-copy-btn').detach();
                editingCell.cell.empty().text(editingCell.originalValue);
                editingCell.cell.append(copyButton); // Re-add the copy button
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
            
            // Shortcode generation functions
            function generateLocationShortcode(field, row) {
                let locationId = row.location_id;
                
                if (field === 'location_id') {
                    return '[zen_location id="' + locationId + '"]';
                } else if (field === 'pick_image1' || field === 'rel_image1_id') {
                    return '[zen_location_image id="' + locationId + '" size="medium"]';
                } else {
                    return '[zen_location id="' + locationId + '" field="' + field + '"]';
                }
            }
            
            function copyToClipboard(text) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).catch(function(err) {
                        fallbackCopyTextToClipboard(text);
                    });
                } else {
                    fallbackCopyTextToClipboard(text);
                }
            }
            
            function fallbackCopyTextToClipboard(text) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.position = "fixed";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                }
                
                document.body.removeChild(textArea);
            }
            
            function showCopySuccess(button) {
                let originalText = button.text();
                let originalBg = button.css('background-color');
                
                button.text('✓');
                button.css('background-color', '#4caf50');
                button.css('color', 'white');
                
                setTimeout(function() {
                    button.text(originalText);
                    button.css('background-color', originalBg);
                    button.css('color', '#666');
                }, 1000);
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
        
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        // Handle AJAX requests
        if (isset($_POST['action'])) {
            $this->handle_services_ajax();
            return;
        }
        
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        
        // Suppress all admin notices except those directly related to this page
        add_action('admin_print_scripts', function() {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        });
        
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <div style="padding: 20px;">
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
            </div> <!-- Close padding wrapper -->
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
                        let td = $('<td style="padding: 8px; border: 1px solid #ddd; position: relative;"></td>');
                        
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
                        
                        // Add shortcode copy button to all cells
                        let copyButton = $('<div class="shortcode-copy-btn" style="position: absolute; top: 0; right: 0; width: 10px; height: 100%; background: white; border: 1px solid #999; z-index: 10; cursor: pointer; font-size: 11px; color: #666; display: flex; align-items: center; justify-content: center;">SH</div>');
                        
                        copyButton.hover(function() {
                            $(this).css('background-color', '#ffeb3b');
                        }, function() {
                            $(this).css('background-color', 'white');
                        });
                        
                        copyButton.click(function(e) {
                            e.stopPropagation();
                            let shortcode = generateServiceShortcode(field, row);
                            copyToClipboard(shortcode);
                            showCopySuccess($(this));
                        });
                        
                        td.append(copyButton);
                        
                        tr.append(td);
                    });
                    
                    tbody.append(tr);
                });
            }
            
            function startInlineEdit(cell, currentValue, id, field) {
                if (editingCell) return; // Only one cell at a time
                
                editingCell = { cell: cell, id: id, field: field, originalValue: currentValue };
                
                // Preserve the copy button
                let copyButton = cell.find('.shortcode-copy-btn').detach();
                
                let input;
                if (field === 'description1_short' || field === 'description1_long') {
                    input = $('<textarea style="width: calc(100% - 12px); padding: 4px; border: 1px solid #0073aa; background: #fff; min-height: 60px; resize: vertical;"></textarea>');
                } else {
                    input = $('<input type="text" style="width: calc(100% - 12px); padding: 4px; border: 1px solid #0073aa; background: #fff;">');
                }
                
                input.val(currentValue);
                cell.empty().append(input);
                cell.append(copyButton); // Re-add the copy button
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
                
                // Preserve the copy button
                let copyButton = editingCell.cell.find('.shortcode-copy-btn').detach();
                editingCell.cell.empty().text(displayValue);
                editingCell.cell.append(copyButton); // Re-add the copy button
                editingCell = null;
            }
            
            function cancelInlineEdit() {
                if (!editingCell) return;
                
                let displayValue = editingCell.originalValue;
                if ((editingCell.field === 'description1_short' || editingCell.field === 'description1_long') && editingCell.originalValue.length > 50) {
                    displayValue = editingCell.originalValue.substring(0, 50) + '...';
                    editingCell.cell.attr('title', editingCell.originalValue);
                }
                
                // Preserve the copy button
                let copyButton = editingCell.cell.find('.shortcode-copy-btn').detach();
                editingCell.cell.empty().text(displayValue);
                editingCell.cell.append(copyButton); // Re-add the copy button
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
            
            // Shortcode generation functions
            function generateServiceShortcode(field, row) {
                let serviceId = row.service_id;
                
                if (field === 'service_id') {
                    return '[zen_service id="' + serviceId + '"]';
                } else if (field === 'pick_image1' || field === 'rel_image1_id') {
                    return '[zen_service_image id="' + serviceId + '" size="medium"]';
                } else {
                    return '[zen_service id="' + serviceId + '" field="' + field + '"]';
                }
            }
            
            function copyToClipboard(text) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).catch(function(err) {
                        fallbackCopyTextToClipboard(text);
                    });
                } else {
                    fallbackCopyTextToClipboard(text);
                }
            }
            
            function fallbackCopyTextToClipboard(text) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.position = "fixed";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                }
                
                document.body.removeChild(textArea);
            }
            
            function showCopySuccess(button) {
                let originalText = button.text();
                let originalBg = button.css('background-color');
                
                button.text('✓');
                button.css('background-color', '#4caf50');
                button.css('color', 'white');
                
                setTimeout(function() {
                    button.text(originalText);
                    button.css('background-color', originalBg);
                    button.css('color', '#666');
                }, 1000);
            }
        });
        </script>
        <?php
    }
    
    /**
     * rup_driggs_mar page - Driggs Management (Vertical Field Editor)
     */
    public function rup_driggs_mar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        $current_site_url = get_site_url();
        
        // Ensure zen_sitespren table has a record for current site
        $this->ensure_sitespren_record();
        
        // Handle AJAX requests
        if (isset($_POST['action'])) {
            $this->handle_driggs_ajax();
            return;
        }
        
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <div style="padding: 20px;">
                <h1 style="margin-bottom: 20px;">🎯 Driggs Site Manager</h1>
                
                <!-- Control Bar -->
                <div style="background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Current Site:</strong> <?php echo esc_html($current_site_url); ?>
                        </div>
                        <div>
                            <button id="save-all-btn" class="button button-primary">Save All Changes</button>
                            <button id="reset-btn" class="button button-secondary">Reset to Defaults</button>
                        </div>
                    </div>
                </div>
                
                <!-- Vertical Field Table -->
                <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                    <div style="overflow-x: auto;">
                        <table id="driggs-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f0f0f0; width: 50px;">
                                        <input type="checkbox" id="select-all" style="width: 20px; height: 20px;">
                                    </th>
                                    <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 250px;">Field Name</th>
                                    <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">Value</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <!-- Data will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
                    <p><strong>Instructions:</strong></p>
                    <ul>
                        <li>Click on any value to edit it inline</li>
                        <li>Toggle switches control boolean fields</li>
                        <li>Changes are saved automatically when you click out of a field</li>
                        <li>Use "Save All Changes" to force save all modifications</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            let currentData = {};
            let hasChanges = false;
            
            // Load initial data
            loadDriggsData();
            
            function loadDriggsData() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_driggs_get_data',
                        nonce: '<?php echo wp_create_nonce('rup_driggs_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            currentData = response.data;
                            displayData();
                        } else {
                            alert('Error loading driggs data: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error loading driggs data');
                    }
                });
            }
            
            function displayData() {
                let tbody = $('#table-body');
                tbody.empty();
                
                // Field definitions in exact order requested - display actual DB field names in bold
                const fields = [
                    {key: 'wppma_db_only_created_at', label: 'wppma_db_only_created_at', type: 'datetime'},
                    {key: 'wppma_db_only_updated_at', label: 'wppma_db_only_updated_at', type: 'datetime'},
                    {key: 'created_at', label: 'created_at', type: 'datetime'},
                    {key: 'sitespren_base', label: 'sitespren_base', type: 'text'},
                    {key: 'true_root_domain', label: 'true_root_domain', type: 'text'},
                    {key: 'full_subdomain', label: 'full_subdomain', type: 'text'},
                    {key: 'webproperty_type', label: 'webproperty_type', type: 'text'},
                    {key: 'fk_users_id', label: 'fk_users_id', type: 'text'},
                    {key: 'updated_at', label: 'updated_at', type: 'datetime'},
                    {key: 'wpuser1', label: 'wpuser1', type: 'text'},
                    {key: 'wppass1', label: 'wppass1', type: 'password'},
                    {key: 'wp_plugin_installed1', label: 'wp_plugin_installed1', type: 'boolean'},
                    {key: 'wp_plugin_connected2', label: 'wp_plugin_connected2', type: 'boolean'},
                    {key: 'fk_domreg_hostaccount', label: 'fk_domreg_hostaccount', type: 'text'},
                    {key: 'is_wp_site', label: 'is_wp_site', type: 'boolean'},
                    {key: 'id', label: 'id', type: 'text'},
                    {key: 'wp_rest_app_pass', label: 'wp_rest_app_pass', type: 'textarea'},
                    {key: 'driggs_industry', label: 'driggs_industry', type: 'text'},
                    {key: 'driggs_city', label: 'driggs_city', type: 'text'},
                    {key: 'driggs_brand_name', label: 'driggs_brand_name', type: 'text'},
                    {key: 'driggs_site_type_purpose', label: 'driggs_site_type_purpose', type: 'text'},
                    {key: 'driggs_email_1', label: 'driggs_email_1', type: 'email'},
                    {key: 'driggs_address_full', label: 'driggs_address_full', type: 'textarea'},
                    {key: 'driggs_phone_1', label: 'driggs_phone_1', type: 'text'},
                    {key: 'driggs_special_note_for_ai_tool', label: 'driggs_special_note_for_ai_tool', type: 'textarea'},
                    {key: 'ns_full', label: 'ns_full', type: 'text'},
                    {key: 'ip_address', label: 'ip_address', type: 'text'},
                    {key: 'is_starred1', label: 'is_starred1', type: 'text'},
                    {key: 'icon_name', label: 'icon_name', type: 'text'},
                    {key: 'icon_color', label: 'icon_color', type: 'text'},
                    {key: 'is_bulldozer', label: 'is_bulldozer', type: 'boolean'},
                    {key: 'driggs_phone1_platform_id', label: 'driggs_phone1_platform_id', type: 'number'},
                    {key: 'driggs_cgig_id', label: 'driggs_cgig_id', type: 'number'},
                    {key: 'driggs_revenue_goal', label: 'driggs_revenue_goal', type: 'number'},
                    {key: 'driggs_address_species_id', label: 'driggs_address_species_id', type: 'number'},
                    {key: 'is_competitor', label: 'is_competitor', type: 'boolean'},
                    {key: 'is_external', label: 'is_external', type: 'boolean'},
                    {key: 'is_internal', label: 'is_internal', type: 'boolean'},
                    {key: 'is_ppx', label: 'is_ppx', type: 'boolean'},
                    {key: 'is_ms', label: 'is_ms', type: 'boolean'},
                    {key: 'is_wayback_rebuild', label: 'is_wayback_rebuild', type: 'boolean'},
                    {key: 'is_naked_wp_build', label: 'is_naked_wp_build', type: 'boolean'},
                    {key: 'is_rnr', label: 'is_rnr', type: 'boolean'},
                    {key: 'is_aff', label: 'is_aff', type: 'boolean'},
                    {key: 'is_other1', label: 'is_other1', type: 'boolean'},
                    {key: 'is_other2', label: 'is_other2', type: 'boolean'},
                    {key: 'driggs_citations_done', label: 'driggs_citations_done', type: 'boolean'},
                    {key: 'is_flylocal', label: 'is_flylocal', type: 'boolean'}
                ];
                
                fields.forEach(function(field) {
                    let tr = $('<tr style="cursor: pointer;"></tr>');
                    tr.hover(function() {
                        $(this).css('background-color', '#f9f9f9');
                    }, function() {
                        $(this).css('background-color', '');
                    });
                    
                    // Checkbox column
                    let checkboxTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"></td>');
                    let checkbox = $('<input type="checkbox" style="width: 20px; height: 20px;" data-field="' + field.key + '">');
                    checkboxTd.append(checkbox);
                    tr.append(checkboxTd);
                    
                    // Field name column - bold DB field names
                    let fieldNameTd = $('<td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d; font-family: monospace;"></td>');
                    fieldNameTd.text(field.label);
                    tr.append(fieldNameTd);
                    
                    // Value column
                    let valueTd = $('<td style="padding: 8px; border: 1px solid #ddd;"></td>');
                    let value = currentData[field.key] || '';
                    
                    if (field.type === 'boolean') {
                        // Toggle switch for boolean fields
                        let toggleSwitch = createToggleSwitch(field.key, value == 1);
                        valueTd.append(toggleSwitch);
                    } else {
                        // Text field for other types
                        valueTd.text(value);
                        valueTd.attr('data-field', field.key);
                        valueTd.attr('data-type', field.type);
                        valueTd.css('cursor', 'text');
                        valueTd.click(function() {
                            startInlineEdit($(this), value, field.key, field.type);
                        });
                    }
                    
                    tr.append(valueTd);
                    tbody.append(tr);
                });
            }
            
            function createToggleSwitch(fieldKey, isChecked) {
                let toggleContainer = $('<div style="display: flex; align-items: center;"></div>');
                let toggleSwitch = $('<label style="position: relative; display: inline-block; width: 50px; height: 24px;"><input type="checkbox" style="opacity: 0; width: 0; height: 0;"><span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px;"></span></label>');
                let checkbox = toggleSwitch.find('input');
                let slider = toggleSwitch.find('span');
                
                checkbox.prop('checked', isChecked);
                if (isChecked) {
                    slider.css('background-color', '#2196F3');
                    slider.append('<span style="position: absolute; content: ""; height: 18px; width: 18px; left: 26px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>');
                } else {
                    slider.append('<span style="position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>');
                }
                
                checkbox.change(function() {
                    updateField(fieldKey, this.checked ? 1 : 0);
                    currentData[fieldKey] = this.checked ? 1 : 0;
                    hasChanges = true;
                });
                
                toggleContainer.append(toggleSwitch);
                return toggleContainer;
            }
            
            function startInlineEdit(cell, currentValue, fieldKey, fieldType) {
                if (cell.find('input, textarea').length > 0) return; // Already editing
                
                let input;
                if (fieldType === 'textarea') {
                    input = $('<textarea style="width: 100%; padding: 4px; border: 1px solid #0073aa; background: #fff; min-height: 60px; resize: vertical;"></textarea>');
                } else {
                    input = $('<input type="' + (fieldType === 'email' ? 'email' : fieldType === 'number' ? 'number' : 'text') + '" style="width: 100%; padding: 4px; border: 1px solid #0073aa; background: #fff;">');
                }
                
                input.val(currentValue);
                cell.empty().append(input);
                input.focus().select();
                
                input.keydown(function(e) {
                    if (e.key === 'Enter' && fieldType !== 'textarea') {
                        e.preventDefault();
                        saveInlineEdit(cell, input, fieldKey, fieldType);
                    } else if (e.key === 'Escape') {
                        cancelInlineEdit(cell, currentValue);
                    }
                });
                
                input.blur(function() {
                    saveInlineEdit(cell, input, fieldKey, fieldType);
                });
            }
            
            function saveInlineEdit(cell, input, fieldKey, fieldType) {
                let newValue = input.val();
                updateField(fieldKey, newValue);
                currentData[fieldKey] = newValue;
                
                // Display the new value
                let displayValue = newValue;
                if (fieldType === 'textarea' && newValue.length > 100) {
                    displayValue = newValue.substring(0, 100) + '...';
                    cell.attr('title', newValue);
                }
                
                cell.empty().text(displayValue);
                cell.css('cursor', 'text');
                cell.click(function() {
                    startInlineEdit($(this), newValue, fieldKey, fieldType);
                });
                
                hasChanges = true;
            }
            
            function cancelInlineEdit(cell, originalValue) {
                cell.empty().text(originalValue);
                cell.css('cursor', 'text');
            }
            
            function updateField(fieldKey, value) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rup_driggs_update_field',
                        nonce: '<?php echo wp_create_nonce('rup_driggs_nonce'); ?>',
                        field: fieldKey,
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
            
            // Save All button
            $('#save-all-btn').click(function() {
                if (hasChanges) {
                    alert('All changes have been saved automatically.');
                    hasChanges = false;
                } else {
                    alert('No changes to save.');
                }
            });
            
            // Reset button
            $('#reset-btn').click(function() {
                if (confirm('Reset all fields to default values? This cannot be undone.')) {
                    // Implement reset functionality
                    location.reload();
                }
            });
            
            // Select all checkbox
            $('#select-all').change(function() {
                $('input[data-field]').prop('checked', this.checked);
            });
        });
        </script>
        
        <?php
    }
    
    /**
     * rup_service_tags_mar page
     */
    public function rup_service_tags_mar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        echo '<div class="wrap">';
        echo '<h1><strong>rup_service_tags_mar</strong></h1>';
        echo '</div>';
    }
    
    /**
     * rup_location_tags_mar page
     */
    public function rup_location_tags_mar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        echo '<div class="wrap">';
        echo '<h1><strong>rup_location_tags_mar</strong></h1>';
        echo '</div>';
    }
    
    /**
     * rup_horse_class_page - Database Horse Class Management
     */
    public function rup_horse_class_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
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
    
    /**
     * AJAX: Get driggs data for current site
     */
    public function rup_driggs_get_data() {
        check_ajax_referer('rup_driggs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        $current_site_url = get_site_url();
        
        try {
            // Get the single record for this site - there should only be one row
            $result = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1", ARRAY_A);
            
            if ($wpdb->last_error) {
                wp_send_json_error('Database error: ' . $wpdb->last_error);
                return;
            }
            
            // If no record exists, create one with defaults
            if (!$result) {
                $this->ensure_sitespren_record();
                $result = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1", ARRAY_A);
            }
            
            // Convert boolean values to proper format
            $boolean_fields = [
                'wp_plugin_installed1', 'wp_plugin_connected2', 'is_wp_site', 'is_bulldozer', 
                'driggs_citations_done', 'is_competitor', 'is_external', 'is_internal', 
                'is_ppx', 'is_ms', 'is_wayback_rebuild', 'is_naked_wp_build', 
                'is_rnr', 'is_aff', 'is_other1', 'is_other2', 'is_flylocal'
            ];
            
            foreach ($boolean_fields as $field) {
                if (isset($result[$field])) {
                    $result[$field] = (int) $result[$field];
                }
            }
            
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error('Error loading driggs data: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Update driggs field
     */
    public function rup_driggs_update_field() {
        check_ajax_referer('rup_driggs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $field = sanitize_text_field($_POST['field']);
        $value = $_POST['value'];
        
        // Validate field name - all editable fields from zen_driggs table
        $allowed_fields = [
            'created_at', 'sitespren_base', 'true_root_domain', 'full_subdomain', 'webproperty_type',
            'fk_users_id', 'updated_at', 'wpuser1', 'wppass1', 'wp_plugin_installed1', 'wp_plugin_connected2',
            'fk_domreg_hostaccount', 'is_wp_site', 'wp_rest_app_pass', 'driggs_industry', 'driggs_city',
            'driggs_brand_name', 'driggs_site_type_purpose', 'driggs_email_1', 'driggs_address_full',
            'driggs_phone_1', 'driggs_special_note_for_ai_tool', 'ns_full', 'ip_address', 'is_starred1',
            'icon_name', 'icon_color', 'is_bulldozer', 'driggs_phone1_platform_id', 'driggs_cgig_id',
            'driggs_revenue_goal', 'driggs_address_species_id', 'is_competitor', 'is_external', 'is_internal',
            'is_ppx', 'is_ms', 'is_wayback_rebuild', 'is_naked_wp_build', 'is_rnr', 'is_aff',
            'is_other1', 'is_other2', 'driggs_citations_done', 'is_flylocal'
        ];
        
        if (!in_array($field, $allowed_fields)) {
            wp_send_json_error('Invalid field name');
            return;
        }
        
        // Sanitize value based on field type
        $number_fields = ['driggs_phone1_platform_id', 'driggs_cgig_id', 'driggs_revenue_goal', 'driggs_address_species_id'];
        $boolean_fields = ['wp_plugin_installed1', 'wp_plugin_connected2', 'is_wp_site', 'is_bulldozer', 'driggs_citations_done', 'is_competitor', 'is_external', 'is_internal', 'is_ppx', 'is_ms', 'is_wayback_rebuild', 'is_naked_wp_build', 'is_rnr', 'is_aff', 'is_other1', 'is_other2', 'is_flylocal'];
        $email_fields = ['driggs_email_1'];
        $datetime_fields = ['created_at', 'updated_at'];
        
        if (in_array($field, $number_fields)) {
            $value = $value === '' ? NULL : intval($value);
        } elseif (in_array($field, $boolean_fields)) {
            $value = $value ? 1 : 0;
        } elseif (in_array($field, $email_fields)) {
            $value = $value === '' ? NULL : sanitize_email($value);
        } elseif (in_array($field, $datetime_fields)) {
            // Allow datetime values in MySQL format (YYYY-MM-DD HH:MM:SS)
            $value = $value === '' ? NULL : sanitize_text_field($value);
        } else {
            $value = $value === '' ? NULL : sanitize_textarea_field($value);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        
        try {
            // Update the field in the single record (there should only be one)
            $result = $wpdb->query($wpdb->prepare("UPDATE $table_name SET $field = %s, wppma_db_only_updated_at = %s", $value, current_time('mysql')));
            
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
     * document_outlook_aug9 page - Documentation
     */
    public function document_outlook_aug9_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <div style="padding: 20px;">
                <h1 style="margin-bottom: 20px;">Methods For Inserting Shortcodes Into Elementor Frontend From The Garage System</h1>
                
                <div style="background: white; border: 1px solid #ddd; padding: 30px; border-radius: 5px; max-width: 1200px;">
                    <p style="margin-bottom: 20px; color: #666; font-style: italic;">claude code output on ggg2025__08_09 -</p>
                    <hr style="margin-bottom: 30px;">
                    
                    <p style="margin-bottom: 20px; line-height: 1.6;">
                        Looking at your situation, here are the main approaches to integrate your zen_services database images with Elementor's image selection:
                    </p>
                    
                    <h2 style="margin-top: 30px; margin-bottom: 15px; color: #0073aa;">Option 1: Elementor Dynamic Tags (Recommended)</h2>
                    
                    <p style="margin-bottom: 15px; line-height: 1.6;">Create custom Elementor Dynamic Tags that pull directly from your zen_services table:</p>
                    <ul style="margin-left: 20px; margin-bottom: 20px; line-height: 1.6;">
                        <li><strong>How it works:</strong> In Elementor's image widget, you'd see a "Dynamic" button next to "Choose Image"</li>
                        <li><strong>What you'd see:</strong> Custom tags like "Zen Service Image", "Zen Location Image"</li>
                        <li><strong>User experience:</strong> Select the tag → Choose service ID → Image automatically pulls from rel_image1_id</li>
                        <li><strong>Pros:</strong> Native Elementor integration, real-time updates, works with all Elementor image widgets</li>
                        <li><strong>Cons:</strong> Requires Elementor-specific PHP development</li>
                    </ul>
                    
                    <h2 style="margin-top: 30px; margin-bottom: 15px; color: #0073aa;">Option 2: WordPress Media Library Integration</h2>
                    
                    <p style="margin-bottom: 15px; line-height: 1.6;">Automatically sync zen_services images into WordPress media library with special metadata:</p>
                    <ul style="margin-left: 20px; margin-bottom: 20px; line-height: 1.6;">
                        <li><strong>How it works:</strong> When images are assigned in your zen tables, they get tagged in media library</li>
                        <li><strong>What you'd see:</strong> In "Choose Image", you'd see categories like "Zen Services Images" or searchable tags</li>
                        <li><strong>User experience:</strong> Normal WordPress media selection but with zen-specific organization</li>
                        <li><strong>Pros:</strong> Works with existing workflow, no Elementor-specific code needed</li>
                        <li><strong>Cons:</strong> Requires media library synchronization system</li>
                    </ul>
                    
                    <h2 style="margin-top: 30px; margin-bottom: 15px; color: #0073aa;">Option 3: Custom Image Selection Modal</h2>
                    
                    <p style="margin-bottom: 15px; line-height: 1.6;">Replace Elementor's image chooser with your own that shows zen data:</p>
                    <ul style="margin-left: 20px; margin-bottom: 20px; line-height: 1.6;">
                        <li><strong>How it works:</strong> Override the image selection process to show zen services with their images</li>
                        <li><strong>What you'd see:</strong> Grid of services with names + images instead of just raw media files</li>
                        <li><strong>User experience:</strong> More contextual selection (pick by service name rather than filename)</li>
                        <li><strong>Pros:</strong> Most user-friendly for content creators</li>
                        <li><strong>Cons:</strong> Most complex to implement, might break with Elementor updates</li>
                    </ul>
                    
                    <h2 style="margin-top: 30px; margin-bottom: 15px; color: #0073aa;">Option 4: Shortcode-to-Image Bridge</h2>
                    
                    <p style="margin-bottom: 15px; line-height: 1.6;">Create a system where you paste a zen shortcode and it converts to the image:</p>
                    <ul style="margin-left: 20px; margin-bottom: 20px; line-height: 1.6;">
                        <li><strong>How it works:</strong> Special input field that accepts <code>[zen_service_image id="5"]</code> and converts it</li>
                        <li><strong>What you'd see:</strong> Paste shortcode → Image appears automatically</li>
                        <li><strong>User experience:</strong> Copy from your admin tables → Paste in Elementor</li>
                        <li><strong>Pros:</strong> Leverages your existing shortcode system</li>
                        <li><strong>Cons:</strong> Less visual, requires users to know IDs</li>
                    </ul>
                    
                    <div style="background: #f0f8ff; border: 1px solid #0073aa; padding: 20px; border-radius: 5px; margin-top: 30px;">
                        <p style="margin: 0; line-height: 1.6;">
                            <strong>My recommendation:</strong> Start with <strong>Option 1 (Dynamic Tags)</strong> because it integrates most naturally with Elementor's existing workflow and gives you the most flexibility. Users would still see the familiar Elementor interface but with your zen data as a source option.
                        </p>
                    </div>
                    
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #0073aa;">
                        <p style="margin: 0; line-height: 1.6;">
                            <strong>What are your thoughts on these approaches? Which direction feels most aligned with how you want content creators to work with your zen data?</strong>
                        </p>
                    </div>
                    
                    <hr style="margin: 30px 0;">
                    <p style="margin: 0; font-style: italic; color: #666;">this is a documentation page</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * dynamic_images_man page - Dynamic Images Management Documentation
     */
    public function dynamic_images_man_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        // Check if Elementor is active
        $elementor_active = is_plugin_active('elementor/elementor.php') || did_action('elementor/loaded');
        
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <div style="padding: 20px;">
                <h1 style="margin-bottom: 20px;">🎯 Dynamic Images Management</h1>
                
                <div style="background: white; border: 1px solid #ddd; padding: 30px; border-radius: 5px; max-width: 1200px;">
                    
                    <?php if ($elementor_active): ?>
                        <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 30px;">
                            <h3 style="margin-top: 0; color: #155724;">✅ Elementor Dynamic Tags System Active</h3>
                            <p style="margin-bottom: 0; color: #155724;">Your Elementor Dynamic Tags for Zen Services and Locations are ready to use!</p>
                        </div>
                    <?php else: ?>
                        <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 30px;">
                            <h3 style="margin-top: 0; color: #721c24;">⚠️ Elementor Not Detected</h3>
                            <p style="margin-bottom: 0; color: #721c24;">Please install and activate Elementor plugin to use Dynamic Tags functionality.</p>
                        </div>
                    <?php endif; ?>
                    
                    <h2 style="margin-top: 0; color: #0073aa;">How to Use Zen Dynamic Tags in Elementor</h2>
                    
                    <p style="line-height: 1.6; margin-bottom: 20px;">
                        The Dynamic Tags system allows you to pull images and text directly from your zen_services and zen_locations database tables into any Elementor widget that supports dynamic content.
                    </p>
                    
                    <h3 style="color: #0073aa; margin-top: 30px;">Step 1: Add an Image Widget</h3>
                    <ol style="line-height: 1.6; margin-left: 20px;">
                        <li>In Elementor editor, drag an <strong>Image widget</strong> onto your page</li>
                        <li>In the widget settings, look for the <strong>"Choose Image"</strong> button</li>
                        <li>Click the small <strong>📋 "Dynamic"</strong> button next to "Choose Image"</li>
                    </ol>
                    
                    <h3 style="color: #0073aa; margin-top: 30px;">Step 2: Select Zen Dynamic Tag</h3>
                    <p style="line-height: 1.6;">In the Dynamic Tags panel, you'll see a <strong>"Zen Garage"</strong> group with these options:</p>
                    <ul style="line-height: 1.6; margin-left: 20px;">
                        <li><strong>Zen Service Image</strong> - Pull images from zen_services table</li>
                        <li><strong>Zen Location Image</strong> - Pull images from zen_locations table</li>
                    </ul>
                    
                    <h3 style="color: #0073aa; margin-top: 30px;">Step 3: Configure the Tag</h3>
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                        <h4 style="margin-top: 0;">For Service Images:</h4>
                        <ul style="line-height: 1.6; margin-left: 20px;">
                            <li><strong>Service ID:</strong> Enter the ID number from your zen_services table</li>
                            <li><strong>Image Size:</strong> Choose thumbnail, medium, large, or full size</li>
                        </ul>
                        
                        <h4>For Location Images:</h4>
                        <ul style="line-height: 1.6; margin-left: 20px;">
                            <li><strong>Location ID:</strong> Enter the ID number from your zen_locations table</li>
                            <li><strong>Image Size:</strong> Choose thumbnail, medium, large, or full size</li>
                        </ul>
                    </div>
                    
                    <h3 style="color: #0073aa; margin-top: 30px;">Text Dynamic Tags</h3>
                    <p style="line-height: 1.6;">For text widgets (headings, text blocks, etc.), you can also use:</p>
                    <ul style="line-height: 1.6; margin-left: 20px;">
                        <li><strong>Zen Service Name</strong> - Pull service names, descriptions, placards, etc.</li>
                        <li><strong>Zen Location Name</strong> - Pull location names, addresses, city, state, etc.</li>
                    </ul>
                    
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 30px 0;">
                        <h4 style="margin-top: 0; color: #856404;">💡 Pro Tip: Finding IDs</h4>
                        <p style="margin-bottom: 0; color: #856404; line-height: 1.6;">
                            To find Service IDs and Location IDs, visit your <strong>rup_services_mar</strong> and <strong>rup_locations_mar</strong> admin pages. 
                            The ID numbers are shown in the first column of each table. You can also use the <strong>SH</strong> copy buttons to quickly copy shortcodes with the correct IDs.
                        </p>
                    </div>
                    
                    <h3 style="color: #0073aa; margin-top: 30px;">Available Service Fields</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                        <div>
                            <ul style="line-height: 1.6;">
                                <li>Service Name</li>
                                <li>Service Placard</li>
                                <li>Service Moniker</li>
                                <li>Service Sobriquet</li>
                            </ul>
                        </div>
                        <div>
                            <ul style="line-height: 1.6;">
                                <li>Short Description</li>
                                <li>Long Description</li>
                                <li>Service Image (via rel_image1_id)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <h3 style="color: #0073aa; margin-top: 30px;">Available Location Fields</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                        <div>
                            <ul style="line-height: 1.6;">
                                <li>Location Name</li>
                                <li>Location Placard</li>
                                <li>Location Moniker</li>
                                <li>Location Sobriquet</li>
                                <li>Street Address</li>
                            </ul>
                        </div>
                        <div>
                            <ul style="line-height: 1.6;">
                                <li>City</li>
                                <li>State Code</li>
                                <li>Zip Code</li>
                                <li>Country</li>
                                <li>Location Image (via rel_image1_id)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div style="background: #e7f3ff; border: 1px solid #0073aa; padding: 20px; border-radius: 5px; margin-top: 30px;">
                        <h4 style="margin-top: 0; color: #0073aa;">🚀 Benefits of Dynamic Tags</h4>
                        <ul style="line-height: 1.6; margin-bottom: 0; color: #0073aa;">
                            <li><strong>Real-time updates:</strong> Change data in your admin tables, see updates immediately on frontend</li>
                            <li><strong>No manual image uploads:</strong> Images managed centrally through your zen system</li>
                            <li><strong>Consistent data:</strong> Single source of truth for all service and location information</li>
                            <li><strong>Easy maintenance:</strong> Update once, changes appear everywhere the tag is used</li>
                        </ul>
                    </div>
                    
                    <div style="background: #d1ecf1; border: 1px solid #0c5460; padding: 20px; border-radius: 5px; margin-top: 30px;">
                        <h4 style="margin-top: 0; color: #0c5460;">🔗 NEW: Gopher Dynamic Tab</h4>
                        <p style="color: #0c5460; margin-bottom: 10px;">Can't see Dynamic Tags in Elementor? Use our new <strong>Gopher Dynamic Tab</strong>!</p>
                        <div style="background: rgba(255,255,255,0.7); padding: 15px; border-radius: 4px; margin: 15px 0;">
                            <h5 style="margin: 0 0 10px 0; color: #0c5460;">📋 How to Use:</h5>
                            <ol style="line-height: 1.6; color: #0c5460; margin: 0;">
                                <li>In Elementor, add an <strong>Image Box</strong> element</li>
                                <li>Click <strong>"Choose Image"</strong></li>
                                <li>In the sidebar, look for <strong>"Gopher Dynamic Tab"</strong></li>
                                <li>Click it to open the zen data selector</li>
                                <li>Switch between <strong>🚀 Services</strong> and <strong>📍 Locations</strong></li>
                                <li>Click on any item to select it</li>
                                <li>Click <strong>"Use Selected Image"</strong></li>
                            </ol>
                        </div>
                        <p style="color: #0c5460; margin: 0; font-weight: 500;">✅ Works without Elementor Pro - Full Dynamic Tags functionality!</p>
                    </div>
                    
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #0073aa;">
                        <h4 style="color: #0073aa;">Quick Links to Data Management:</h4>
                        <ul style="line-height: 1.6;">
                            <li><a href="<?php echo admin_url('admin.php?page=rup_services_mar'); ?>" class="button button-secondary">Manage Services & Images →</a></li>
                            <li style="margin-top: 10px;"><a href="<?php echo admin_url('admin.php?page=rup_locations_mar'); ?>" class="button button-secondary">Manage Locations & Images →</a></li>
                        </ul>
                    </div>
                    
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Check if we're on a snefuruplin admin page and suppress notices early
     * This runs on 'current_screen' hook which is early enough to catch most notices
     */
    public function maybe_suppress_admin_notices() {
        $screen = get_current_screen();
        
        if (!$screen) {
            return;
        }
        
        // List of our plugin pages that need notice suppression
        $snefuruplin_pages = array(
            'toplevel_page_snefuru',
            'snefuru_page_snefuru_settings', 
            'snefuru_page_snefuru_logs',
            'snefuru_page_screen4_manage',
            'snefuru_page_bespoke_css_editor',
            'snefuru_page_dublish_logs',
            'snefuru_page_rup_locations_mar',
            'snefuru_page_rup_services_mar',
            'snefuru_page_rup_driggs_mar',
            'snefuru_page_rup_service_tags_mar',
            'snefuru_page_rup_location_tags_mar',
            'snefuru_page_rup_horse_class_page',
            'snefuru_page_document_outlook_aug9',
            'snefuru_page_dynamic_images_man',
            'snefuru_page_kenli_sidebar_links'
        );
        
        // Check if current screen is one of our plugin pages OR a post/page editor
        $is_snefuru_page = in_array($screen->id, $snefuruplin_pages) || 
            (isset($_GET['page']) && strpos($_GET['page'], 'rup_') === 0) ||
            (isset($_GET['page']) && $_GET['page'] === 'snefuru');
        
        // Check if we're on a WordPress post/page editor screen
        $is_editor_page = ($screen->base === 'post' && $screen->action === 'add') ||
                         ($screen->base === 'post' && isset($_GET['action']) && $_GET['action'] === 'edit') ||
                         ($screen->id === 'post') ||
                         ($screen->id === 'page') ||
                         in_array($screen->post_type, ['post', 'page']) ||
                         $this->is_post_editor_page();
        
        if ($is_snefuru_page || $is_editor_page) {
            // Suppress notices immediately
            $this->suppress_all_admin_notices();
        }
    }
    
    /**
     * Check if current page is a WordPress post/page editor
     */
    private function is_post_editor_page() {
        global $pagenow;
        
        // Check for post editor pages
        if (in_array($pagenow, ['post.php', 'post-new.php'])) {
            return true;
        }
        
        // Additional check via URL parameters
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['post'])) {
            return true;
        }
        
        // Check for new post creation
        if ($pagenow === 'post-new.php') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Ultra-early notice suppression that runs on plugins_loaded
     * This catches notices from other plugins that load early
     */
    public function ultra_early_notice_suppression() {
        // Only run in admin area
        if (!is_admin()) {
            return;
        }
        
        // Check if we're going to a snefuruplin page OR a post/page editor
        $is_snefuru_page = isset($_GET['page']) && 
            (strpos($_GET['page'], 'rup_') === 0 || 
             $_GET['page'] === 'snefuru' ||
             strpos($_GET['page'], 'snefuru_') === 0 ||
             in_array($_GET['page'], [
                'snefuru_settings', 'snefuru_logs', 'screen4_manage', 
                'bespoke_css_editor', 'dublish_logs', 'document_outlook_aug9',
                'dynamic_images_man', 'kenli_sidebar_links', 'rup_horse_class_page',
                'rup_driggs_mar'
             ]));
        
        // Check if we're on WordPress post/page editor
        $is_editor_page = $this->is_post_editor_page();
        
        if ($is_snefuru_page || $is_editor_page) {
            
            // Pre-emptively block all admin notice hooks
            add_action('admin_notices', function() { 
                ob_start(); // Start output buffering to catch any notices
            }, 0);
            
            add_action('admin_print_footer_scripts', function() {
                if (ob_get_level()) {
                    ob_end_clean(); // Discard any buffered notices
                }
            }, 999);
        }
    }
    
    /**
     * Very early notice suppression that runs on admin_init
     * This catches notices that are added before current_screen hook
     */
    public function early_notice_suppression() {
        // Check if we're on a snefuruplin page via $_GET parameter
        $is_snefuru_page = isset($_GET['page']) && 
            (strpos($_GET['page'], 'rup_') === 0 || 
             $_GET['page'] === 'snefuru' ||
             strpos($_GET['page'], 'snefuru_') === 0 ||
             in_array($_GET['page'], [
                'snefuru_settings', 'snefuru_logs', 'screen4_manage', 
                'bespoke_css_editor', 'dublish_logs', 'document_outlook_aug9',
                'dynamic_images_man', 'kenli_sidebar_links', 'rup_horse_class_page',
                'rup_driggs_mar'
             ]));
        
        // Check if we're on a WordPress post/page editor
        $is_editor_page = $this->is_post_editor_page();
        
        if ($is_snefuru_page || $is_editor_page) {
            
            // Immediate notice suppression
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            
            // Add our suppression hooks very early
            add_action('admin_notices', '__return_false', 1);
            add_action('all_admin_notices', '__return_false', 1);
            add_action('network_admin_notices', '__return_false', 1);
            
            // Also suppress via CSS as backup
            add_action('admin_head', function() {
                echo '<style type="text/css">
                    .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
                    .updated, .error, .update-nag, .admin-notice,
                    div.notice, div.updated, div.error, div.update-nag,
                    .wrap > .notice, .wrap > .updated, .wrap > .error,
                    #adminmenu + .notice, #adminmenu + .updated, #adminmenu + .error,
                    .update-php, .php-update-nag,
                    .plugin-update-tr, .theme-update-message,
                    .update-message, .updating-message,
                    #update-nag, #deprecation-warning,
                    .update-core-php, .notice-alt,
                    .activated, .deactivated {
                        display: none !important;
                    }
                </style>';
            }, 1);
        }
    }
    
    /**
     * Completely suppress all admin notices on specific pages
     * Used to create clean admin interfaces without WordPress plugin warnings/notices
     */
    private function suppress_all_admin_notices() {
        // Remove all admin notices immediately
        add_action('admin_print_styles', function() {
            // Remove all notice actions
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            
            // Remove user admin notices
            global $wp_filter;
            if (isset($wp_filter['user_admin_notices'])) {
                unset($wp_filter['user_admin_notices']);
            }
        }, 0);
        
        // Additional cleanup for persistent notices
        add_action('admin_head', function() {
            // Hide any notices that slip through via CSS
            echo '<style type="text/css">
                .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
                .updated, .error, .update-nag, .admin-notice,
                div.notice, div.updated, div.error, div.update-nag,
                .wrap > .notice, .wrap > .updated, .wrap > .error,
                #adminmenu + .notice, #adminmenu + .updated, #adminmenu + .error,
                .update-php, .php-update-nag,
                .plugin-update-tr, .theme-update-message,
                .update-message, .updating-message,
                #update-nag, #deprecation-warning {
                    display: none !important;
                }
                
                /* Hide WordPress core update notices */
                .update-core-php, .notice-alt {
                    display: none !important;
                }
                
                /* Hide plugin activation/deactivation notices */
                .activated, .deactivated {
                    display: none !important;
                }
                
                /* Post/Page editor specific notice suppression */
                .edit-post-header .components-notice-list,
                .edit-post-layout .components-notice,
                .edit-post-sidebar .components-notice,
                .components-snackbar-list,
                .components-notice-list .components-notice,
                .interface-interface-skeleton__notices,
                .edit-post-notices,
                .block-editor-warning,
                .components-notice.is-warning,
                .components-notice.is-error,
                .components-notice.is-success,
                .components-notice.is-info {
                    display: none !important;
                }
            </style>';
        });
        
        // Remove notices from third-party plugins by clearing the notices array
        add_action('admin_print_scripts', function() {
            global $wp_filter;
            
            // Clear all notice-related hooks
            $notice_hooks = [
                'admin_notices',
                'all_admin_notices', 
                'network_admin_notices',
                'user_admin_notices'
            ];
            
            foreach ($notice_hooks as $hook) {
                if (isset($wp_filter[$hook])) {
                    $wp_filter[$hook] = new WP_Hook();
                }
            }
        }, 0);
        
        // Capture and discard any output from notices
        add_action('admin_print_styles', function() {
            ob_start(function($buffer) {
                // Strip out common notice patterns
                $patterns = [
                    '/<div[^>]*class="[^"]*notice[^"]*"[^>]*>.*?<\/div>/is',
                    '/<div[^>]*class="[^"]*updated[^"]*"[^>]*>.*?<\/div>/is',
                    '/<div[^>]*class="[^"]*error[^"]*"[^>]*>.*?<\/div>/is',
                    '/<div[^>]*id="[^"]*update-nag[^"]*"[^>]*>.*?<\/div>/is'
                ];
                
                foreach ($patterns as $pattern) {
                    $buffer = preg_replace($pattern, '', $buffer);
                }
                
                return $buffer;
            });
        }, 0);
        
        // Final cleanup - remove any remaining notices via JavaScript
        add_action('admin_footer', function() {
            echo '<script type="text/javascript">
                jQuery(document).ready(function($) {
                    // Remove all notice elements
                    $(".notice, .notice-warning, .notice-error, .notice-success, .notice-info").remove();
                    $(".updated, .error, .update-nag, .admin-notice").remove();
                    $("#update-nag, #deprecation-warning").remove();
                    $(".update-php, .php-update-nag").remove();
                    $(".plugin-update-tr, .theme-update-message").remove();
                    $(".update-message, .updating-message").remove();
                    
                    // Set up observer to remove notices that get added dynamically
                    if (window.MutationObserver) {
                        var observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                // Remove classic WordPress notices
                                $(mutation.addedNodes).find(".notice, .updated, .error, .update-nag").remove();
                                if ($(mutation.target).is(".notice, .updated, .error, .update-nag")) {
                                    $(mutation.target).remove();
                                }
                                
                                // Remove Gutenberg/Block editor notices
                                $(mutation.addedNodes).find(".components-notice, .components-snackbar, .block-editor-warning").remove();
                                if ($(mutation.target).is(".components-notice, .components-snackbar, .block-editor-warning")) {
                                    $(mutation.target).remove();
                                }
                                
                                // Remove notice lists and containers
                                $(mutation.addedNodes).find(".components-notice-list, .interface-interface-skeleton__notices").remove();
                                if ($(mutation.target).is(".components-notice-list, .interface-interface-skeleton__notices")) {
                                    $(mutation.target).remove();
                                }
                            });
                        });
                        
                        observer.observe(document.body, {
                            childList: true,
                            subtree: true
                        });
                    }
                    
                    // Gutenberg/Block Editor specific notice removal
                    if (typeof wp !== "undefined" && wp.data) {
                        // Remove notices from WordPress data stores
                        setTimeout(function() {
                            if (wp.data.dispatch && wp.data.dispatch("core/notices")) {
                                wp.data.dispatch("core/notices").removeAllNotices();
                            }
                        }, 1000);
                        
                        // Continuously remove notices
                        setInterval(function() {
                            if (wp.data.dispatch && wp.data.dispatch("core/notices")) {
                                wp.data.dispatch("core/notices").removeAllNotices();
                            }
                        }, 5000);
                    }
                });
            </script>';
        });
    }
    
    /**
     * Ensure zen_sitespren table has a record for the current site
     */
    private function ensure_sitespren_record() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        
        // Check if any record exists (there should only be one)
        $existing_record = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        if ($existing_record == 0) {
            // Create a single default record
            $current_site_url = get_site_url();
            $uuid = wp_generate_uuid4();
            
            $wpdb->insert(
                $table_name,
                array(
                    'id' => $uuid,
                    'sitespren_base' => parse_url($current_site_url, PHP_URL_HOST),
                    'true_root_domain' => parse_url($current_site_url, PHP_URL_HOST),
                    'full_subdomain' => parse_url($current_site_url, PHP_URL_HOST),
                    'driggs_brand_name' => get_bloginfo('name'),
                    'driggs_email_1' => get_option('admin_email'),
                    'is_wp_site' => 1
                )
            );
        }
    }
} 