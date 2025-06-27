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
        
        // Add Elementor data viewer
        add_action('add_meta_boxes', array($this, 'add_elementor_data_metabox'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_menu_page(
            'Snefuru Cloud',
            'Snefuru',
            'manage_options',
            'snefuru',
            array($this, 'admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'),
            30
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
            'Upload Settings',
            'Upload Settings',
            'manage_options',
            'snefuru-upload-settings',
            array($this, 'upload_settings_page')
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
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('snefuru_settings', 'snefuru_ruplin_api_key_1');
        register_setting('snefuru_settings', 'snefuru_api_url');
        register_setting('snefuru_settings', 'snefuru_sync_interval');
        register_setting('snefuru_settings', 'snefuru_auto_sync');
        register_setting('snefuru_upload_settings', 'snefuru_upload_enabled');
        register_setting('snefuru_upload_settings', 'snefuru_upload_max_size');
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
            <h1>Snefuru Cloud Dashboard</h1>
            
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
        $ruplin_api_key = get_option('snefuru_ruplin_api_key_1', '');
        $api_url = get_option('snefuru_api_url', 'https://your-app.vercel.app/api');
        
        ?>
        <div class="wrap">
            <h1>Snefuru Settings</h1>
            
            <div class="snefuru-card">
                <h3>Ruplin API Configuration</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">Ruplin API Key</th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" id="ruplin-api-key" value="<?php echo esc_attr($ruplin_api_key); ?>" class="regular-text" readonly />
                                <div id="api-key-view-buttons" style="display: flex; gap: 10px;">
                                    <?php if (!empty($ruplin_api_key)): ?>
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
                <h3>Plugin Status</h3>
                <p><strong>Plugin Version:</strong> <?php echo SNEFURU_PLUGIN_VERSION; ?></p>
                <p><strong>API Key Status:</strong> <?php echo !empty($ruplin_api_key) ? '<span class="status-success">Configured</span>' : '<span class="status-error">Not Configured</span>'; ?></p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var originalApiKey = $('#ruplin-api-key').val();
            
            // Copy API key
            $('#copy-api-key').on('click', function() {
                $('#ruplin-api-key').select();
                document.execCommand('copy');
                alert('API key copied to clipboard!');
            });
            
            // Edit API key
            $('#edit-api-key').on('click', function() {
                $('#ruplin-api-key').prop('readonly', false).focus();
                $('#api-key-view-buttons').hide();
                $('#api-key-edit-buttons').css('display', 'flex');
            });
            
            // Cancel edit
            $('#cancel-edit-api-key').on('click', function() {
                $('#ruplin-api-key').val(originalApiKey).prop('readonly', true);
                $('#api-key-view-buttons').css('display', 'flex');
                $('#api-key-edit-buttons').hide();
                $('#api-key-message').hide();
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
                            $('#ruplin-api-key').prop('readonly', true);
                            $('#api-key-view-buttons').css('display', 'flex');
                            $('#api-key-edit-buttons').hide();
                            $message.removeClass('notice-error').addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                            
                            // Update the copy button visibility
                            if (newApiKey && $('#copy-api-key').length === 0) {
                                $('#edit-api-key').before('<button type="button" class="button button-secondary" id="copy-api-key">Copy</button>');
                                $('#copy-api-key').on('click', function() {
                                    $('#ruplin-api-key').select();
                                    document.execCommand('copy');
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
            <h1>Snefuru Logs</h1>
            
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
     * Upload Settings page
     */
    public function upload_settings_page() {
        if (isset($_POST['submit'])) {
            check_admin_referer('snefuru_upload_settings_nonce');
            
            update_option('snefuru_upload_enabled', isset($_POST['snefuru_upload_enabled']) ? 1 : 0);
            update_option('snefuru_upload_max_size', sanitize_text_field($_POST['snefuru_upload_max_size']));
            
            echo '<div class="notice notice-success"><p>Upload settings saved!</p></div>';
        }
        
        $ruplin_api_key = get_option('snefuru_ruplin_api_key_1', '');
        $upload_enabled = get_option('snefuru_upload_enabled', 1);
        $upload_max_size = get_option('snefuru_upload_max_size', '10MB');
        
        ?>
        <div class="wrap">
            <h1>Snefuru Upload Settings</h1>
            
            <div class="snefuru-upload-info">
                <div class="snefuru-card">
                    <h3>Upload API Configuration</h3>
                    <p>Configure the plugin to receive image uploads directly from your Next.js application.</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Ruplin API Key</th>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="text" id="upload-api-key" value="<?php echo esc_attr($ruplin_api_key); ?>" class="regular-text" readonly />
                                    <?php if (!empty($ruplin_api_key)): ?>
                                        <button type="button" class="button button-secondary" id="copy-api-key">Copy</button>
                                    <?php endif; ?>
                                </div>
                                <p class="description">
                                    <?php if (!empty($ruplin_api_key)): ?>
                                        This Ruplin API key is used to authenticate all requests from your Next.js app.
                                        <br><strong>Keep this key secure.</strong>
                                    <?php else: ?>
                                        No Ruplin API key found. Please reinstall the plugin using Option 2.
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
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
                                    <p class="description">Install the plugin with a Ruplin API key to see the upload endpoints.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
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
                </div>
                
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
                
                <?php submit_button('Save Upload Settings'); ?>
            </form>
            
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
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#copy-api-key').on('click', function() {
                $('#upload-api-key').select();
                document.execCommand('copy');
                alert('API key copied to clipboard!');
            });
            
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
        });
        </script>
        <?php
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
} 