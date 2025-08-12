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
        
        // Sitespren export AJAX action
        add_action('wp_ajax_export_sitespren', array($this, 'handle_sitespren_export'));
        
        // Hudson ImgPlanBatch ID save AJAX action
        add_action('wp_ajax_save_hudson_imgplanbatch_id', array($this, 'save_hudson_imgplanbatch_id'));
        
        // Content injection AJAX action
        add_action('wp_ajax_snefuru_inject_content', array($this, 'snefuru_inject_content'));
        
        // Page duplication AJAX action - TEMPORARILY DISABLED FOR DEBUGGING
        // add_action('wp_ajax_snefuru_duplicate_page', array($this, 'snefuru_duplicate_page'));
        
        // Bulk duplication AJAX action
        add_action('wp_ajax_rup_duplicate_single_post', array($this, 'rup_duplicate_single_post'));
        
        // AJAX handler for beamraymar post content update
        add_action('wp_ajax_beamraymar_update_post_content', array($this, 'beamraymar_update_post_content'));
        
        // AJAX handler for beamraymar elementor data update
        add_action('wp_ajax_beamraymar_update_elementor_data', array($this, 'beamraymar_update_elementor_data'));
        
        // AJAX handler for cssmar CSS file save
        add_action('wp_ajax_cssmar_save_css', array($this, 'cssmar_save_css'));
        
        // Add Elementor data viewer
        add_action('add_meta_boxes', array($this, 'add_elementor_data_metabox'));
        
        // One-time cleanup on admin_init
        add_action('admin_init', array($this, 'one_time_cleanup_zen_driggs'), 1);
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
        
        // Add Cockpit as the first submenu item
        add_submenu_page(
            'snefuru',
            'Cockpit - rup_kenli_mar',
            'Cockpit - rup_kenli_mar',
            'manage_options',
            'rup_kenli_mar',
            array($this, 'rup_kenli_mar_page')
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
            'rup_kpages_mar',
            'rup_kpages_mar',
            'manage_options',
            'rup_kpages_mar',
            array($this, 'rup_kpages_mar_page')
        );
        
        add_submenu_page(
            'snefuru',
            'rup_duplicate_mar',
            'rup_duplicate_mar',
            'manage_options',
            'rup_duplicate_mar',
            array($this, 'rup_duplicate_mar_page')
        );
        
        add_submenu_page(
            'snefuru',
            'rup_pantheon_mar',
            'rup_pantheon_mar',
            'manage_options',
            'rup_pantheon_mar',
            array($this, 'rup_pantheon_mar_page')
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
        
        add_submenu_page(
            'snefuru',
            'Sitespren Export',
            'Sitespren Export',
            'manage_options',
            'rup_sitespren_export',
            array($this, 'rup_sitespren_export_page')
        );
        
        add_submenu_page(
            'snefuru',
            'beamraymar',
            'beamraymar',
            'manage_options',
            'beamraymar',
            array($this, 'beamraymar_page')
        );
        
        add_submenu_page(
            'snefuru',
            'cssmar',
            'cssmar',
            'manage_options',
            'cssmar',
            array($this, 'cssmar_page')
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
        wp_enqueue_style('sddx-240-ruplin-screens-css', SNEFURU_PLUGIN_URL . 'assets/css/sddx_240_ruplin_screens_only_css_by_kyle_1.css', array(), SNEFURU_PLUGIN_VERSION);
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
                            <a href="<?php echo admin_url('admin.php?page=rup_sitespren_export'); ?>" 
                               class="button button-secondary" 
                               style="background: #0073aa; color: white; border-color: #0073aa; margin-right: 10px;">
                                export at rup_sitespren_mar
                            </a>
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
                                    <th style="padding: 0; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 20px;">stuff3</th>
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
        
        <style type="text/css">
        /* Toggle Switch Styles */
        .driggs-toggle-switch {
            cursor: pointer !important;
        }
        
        .driggs-toggle-switch input[type="checkbox"]:focus + .driggs-toggle-slider {
            box-shadow: 0 0 1px #2196F3;
        }
        
        .driggs-toggle-slider {
            pointer-events: none;
        }
        
        .driggs-toggle-knob {
            pointer-events: none;
        }
        
        /* Ensure table cells are properly styled */
        #driggs-table td {
            vertical-align: middle;
        }
        
        #driggs-table td[data-field] {
            min-height: 30px;
        }
        
        #driggs-table textarea,
        #driggs-table input[type="text"],
        #driggs-table input[type="email"],
        #driggs-table input[type="number"] {
            font-family: inherit;
            font-size: 14px;
        }
        
        /* Roaring Div Styles */
        .roaring_div {
            width: 20px;
            height: 100%;
            border: 1px solid gray;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            background-color: #f0f0f0;
            transition: background-color 0.2s;
        }
        
        .roaring_div:hover {
            background-color: #e0e0e0;
            border-color: #666;
        }
        
        /* Ensure stuff3 column cells have no padding */
        #driggs-table td.stuff3-cell {
            padding: 0 !important;
            width: 20px !important;
        }
        
        /* Read-only field styling */
        .driggs-readonly-field {
            background-color: #f9f9f9 !important;
            color: #666 !important;
            font-style: italic !important;
            cursor: default !important;
            user-select: text !important;
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            pointer-events: auto !important;
        }
        
        .driggs-readonly-field:hover {
            background-color: #f9f9f9 !important;
        }
        </style>
        
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
                    {key: 'wppma_id', label: 'wppma_id', type: 'number'},
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
                    {key: 'driggs_logo_url', label: 'driggs_logo_url', type: 'logo_url'},
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
                    } else if (field.key === 'wppma_id' || field.key === 'id' || field.type === 'datetime') {
                        // Read-only fields (ID fields and datetime fields)
                        valueTd.text(value);
                        valueTd.css('color', '#666');
                        valueTd.css('font-style', 'italic');
                        valueTd.css('cursor', 'default');
                        valueTd.css('user-select', 'text');
                        valueTd.css('-webkit-user-select', 'text');
                        valueTd.css('-moz-user-select', 'text');
                        valueTd.addClass('driggs-readonly-field');
                        valueTd.attr('title', 'This field is read-only (can select text but not edit)');
                    } else if (field.type === 'logo_url') {
                        // Special handling for logo URL with image preview
                        let logoContainer = $('<div style="display: flex; flex-direction: column; width: 100%; height: 100%;"></div>');
                        
                        // Editable text input for URL
                        let urlInput = $('<input type="text" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px; margin-bottom: 5px; font-size: 12px;">');
                        urlInput.val(value || '');
                        urlInput.attr('data-field', field.key);
                        
                        // Image preview container
                        let imagePreview = $('<div style="width: 100%; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid #eee; border-radius: 3px; background: #f9f9f9;"></div>');
                        
                        // Function to update image preview
                        function updateImagePreview(url) {
                            if (url && url.trim() !== '') {
                                let img = $('<img style="max-width: 100%; max-height: 38px; object-fit: contain;">');
                                img.attr('src', url);
                                img.on('load', function() {
                                    imagePreview.html(img);
                                });
                                img.on('error', function() {
                                    imagePreview.html('<span style="color: #999; font-size: 11px;">Invalid image URL</span>');
                                });
                            } else {
                                imagePreview.html('<span style="color: #ccc; font-size: 11px;">No logo URL</span>');
                            }
                        }
                        
                        // Update preview on input change
                        urlInput.on('input', function() {
                            let newUrl = $(this).val();
                            updateImagePreview(newUrl);
                        });
                        
                        // Save on blur
                        urlInput.on('blur', function() {
                            let newValue = $(this).val();
                            if (newValue !== value) {
                                updateField(field.key, newValue);
                                currentData[field.key] = newValue;
                                hasChanges = true;
                            }
                        });
                        
                        // Initialize with current value
                        updateImagePreview(value);
                        
                        logoContainer.append(urlInput).append(imagePreview);
                        valueTd.append(logoContainer);
                        valueTd.css('padding', '6px');
                    } else {
                        // Editable text fields
                        valueTd.text(value);
                        valueTd.attr('data-field', field.key);
                        valueTd.attr('data-type', field.type);
                        valueTd.css('cursor', 'text');
                        valueTd.click(function() {
                            startInlineEdit($(this), value, field.key, field.type);
                        });
                    }
                    
                    tr.append(valueTd);
                    
                    // Add stuff3 column with roaring_div as shortcode copy button
                    let stuff3Td = $('<td class="stuff3-cell" style="padding: 0; border: 1px solid #ddd; width: 20px;"></td>');
                    let roaring_div = $('<div class="roaring_div">R</div>');
                    
                    // Add click handler to copy shortcode
                    roaring_div.css('cursor', 'pointer');
                    roaring_div.attr('title', 'Copy shortcode for ' + field.label);
                    roaring_div.on('click', function() {
                        console.log('R button clicked for field:', field.key);
                        console.log('Current data:', currentData);
                        
                        // Get the wppma_id from the current data
                        let wppmaId = currentData.wppma_id || '1'; // Default to 1 if not found
                        let shortcode = '[sitespren wppma_id="' + wppmaId + '" field="' + field.key + '"]';
                        
                        console.log('Generated shortcode:', shortcode);
                        
                        // Copy to clipboard
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(shortcode).then(function() {
                                console.log('Clipboard copy successful');
                                // Visual feedback - change background color temporarily
                                let originalBg = roaring_div.css('background-color');
                                roaring_div.css('background-color', '#4CAF50');
                                roaring_div.text('✓');
                                
                                setTimeout(function() {
                                    roaring_div.css('background-color', originalBg);
                                    roaring_div.text('R');
                                }, 1000);
                            }).catch(function(error) {
                                console.error('Clipboard copy failed:', error);
                                // Fallback method
                                copyShortcodeToClipboardFallback(shortcode, roaring_div);
                            });
                        } else {
                            console.log('Using fallback clipboard method');
                            // Fallback for older browsers
                            copyShortcodeToClipboardFallback(shortcode, roaring_div);
                        }
                    });
                    
                    stuff3Td.append(roaring_div);
                    tr.append(stuff3Td);
                    
                    tbody.append(tr);
                });
            }
            
            function createToggleSwitch(fieldKey, isChecked) {
                let toggleContainer = $('<div style="display: flex; align-items: center;"></div>');
                
                // Create the toggle switch with a unique ID
                let switchId = 'toggle-' + fieldKey;
                let toggleSwitch = $('<label class="driggs-toggle-switch" for="' + switchId + '" style="position: relative; display: inline-block; width: 50px; height: 24px; cursor: pointer;"></label>');
                let checkbox = $('<input type="checkbox" id="' + switchId + '" style="position: absolute; opacity: 0; width: 0; height: 0;">');
                let slider = $('<span class="driggs-toggle-slider" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: ' + (isChecked ? '#2196F3' : '#ccc') + '; transition: .4s; border-radius: 24px;"></span>');
                let knob = $('<span class="driggs-toggle-knob" style="position: absolute; content: \'\'; height: 18px; width: 18px; left: ' + (isChecked ? '26px' : '3px') + '; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>');
                
                checkbox.prop('checked', isChecked);
                slider.append(knob);
                toggleSwitch.append(checkbox).append(slider);
                
                // Handle checkbox change
                checkbox.on('change', function() {
                    let checked = this.checked;
                    
                    // Update visual state
                    slider.css('background-color', checked ? '#2196F3' : '#ccc');
                    knob.css('left', checked ? '26px' : '3px');
                    
                    // Update field in database
                    updateField(fieldKey, checked ? 1 : 0);
                    currentData[fieldKey] = checked ? 1 : 0;
                    hasChanges = true;
                });
                
                // Also handle label click to ensure responsiveness
                toggleSwitch.on('click', function(e) {
                    if (e.target.tagName !== 'INPUT') {
                        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                    }
                });
                
                toggleContainer.append(toggleSwitch);
                return toggleContainer;
            }
            
            // Fallback function for copying shortcode to clipboard
            function copyShortcodeToClipboardFallback(shortcode, element) {
                // Create temporary textarea
                let tempTextarea = $('<textarea>');
                tempTextarea.val(shortcode);
                tempTextarea.css({
                    position: 'fixed',
                    left: '-9999px',
                    top: '-9999px'
                });
                $('body').append(tempTextarea);
                
                // Select and copy
                tempTextarea[0].select();
                tempTextarea[0].setSelectionRange(0, 99999); // For mobile devices
                
                try {
                    let success = document.execCommand('copy');
                    console.log('Document.execCommand copy result:', success);
                    
                    if (success) {
                        // Visual feedback
                        let originalBg = element.css('background-color');
                        element.css('background-color', '#4CAF50');
                        element.text('✓');
                        
                        setTimeout(function() {
                            element.css('background-color', originalBg);
                            element.text('R');
                        }, 1000);
                        console.log('Shortcode copied successfully (fallback method):', shortcode);
                    } else {
                        throw new Error('Copy command failed');
                    }
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                    alert('Shortcode: ' + shortcode + '\n\nCopy failed. The shortcode is shown above - please copy it manually.');
                }
                
                // Remove temporary textarea
                tempTextarea.remove();
            }
            
            function startInlineEdit(cell, currentValue, fieldKey, fieldType) {
                if (cell.find('input, textarea').length > 0) return; // Already editing
                
                // Prevent editing of read-only fields
                if (fieldKey === 'wppma_id' || fieldKey === 'id' || fieldType === 'datetime') {
                    return;
                }
                
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
                console.log('Updating field:', fieldKey, 'with value:', value);
                
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
                        if (response.success) {
                            console.log('Field updated successfully:', fieldKey);
                        } else {
                            console.error('Error updating field:', response.data);
                            alert('Error updating field: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        alert('Error updating field: ' + error);
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
     * rup_kpages_mar page
     */
    public function rup_kpages_mar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        echo '<div class="wrap">';
        echo '<h1><strong>rup_kpages_mar</strong></h1>';
        echo '</div>';
    }
    
    /**
     * rup_duplicate_mar page - Bulk Page/Post Duplication
     */
    public function rup_duplicate_mar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        // Get all posts and pages
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => array('publish', 'draft', 'private', 'pending'),
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $posts = get_posts($args);
        
        ?>
        <div class="wrap">
            <h1><strong>rup_duplicate_mar</strong></h1>
            <p>Select pages/posts to duplicate. New duplicates will be created as drafts.</p>
            
            <!-- Search and Filter Controls -->
            <div style="background: #f9f9f9; padding: 15px; margin: 20px 0; border: 1px solid #ddd; border-radius: 6px;">
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <label><strong>Search:</strong></label>
                    <input type="text" id="rup-search-posts" placeholder="Search titles..." style="padding: 6px; border: 1px solid #ddd; border-radius: 3px; width: 250px;">
                    
                    <label><strong>Filter Type:</strong></label>
                    <select id="rup-filter-type" style="padding: 6px; border: 1px solid #ddd; border-radius: 3px;">
                        <option value="">All Types</option>
                        <option value="page">Pages Only</option>
                        <option value="post">Posts Only</option>
                    </select>
                    
                    <label><strong>Filter Status:</strong></label>
                    <select id="rup-filter-status" style="padding: 6px; border: 1px solid #ddd; border-radius: 3px;">
                        <option value="">All Statuses</option>
                        <option value="publish">Published</option>
                        <option value="draft">Draft</option>
                        <option value="private">Private</option>
                        <option value="pending">Pending</option>
                    </select>
                    
                    <button type="button" id="rup-clear-filters" class="button">Clear Filters</button>
                </div>
            </div>
            
            <!-- Bulk Actions -->
            <div style="margin: 20px 0; padding: 15px; background: #fff; border: 2px solid #0073aa; border-radius: 6px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button type="button" id="rup-duplicate-selected" class="button button-primary" style="background: linear-gradient(135deg, #0073aa 0%, #005177 100%); padding: 10px 20px; font-weight: 600;">
                        🔄 Duplicate Selected Items
                    </button>
                    <span id="rup-selection-count" style="color: #666; font-weight: 500;">0 items selected</span>
                    <button type="button" id="rup-select-all" class="button" style="margin-left: auto;">Select All Visible</button>
                    <button type="button" id="rup-deselect-all" class="button">Deselect All</button>
                </div>
            </div>
            
            <!-- Results Table -->
            <div style="background: white; border: 1px solid #ddd; border-radius: 6px; overflow: hidden;">
                <table class="wp-list-table widefat fixed striped" id="rup-posts-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center; padding: 12px 8px;">
                                <input type="checkbox" id="rup-select-all-checkbox" style="transform: scale(1.2);">
                            </th>
                            <th style="padding: 12px; font-weight: 600;">Title</th>
                            <th style="width: 100px; text-align: center; padding: 12px;">Type</th>
                            <th style="width: 100px; text-align: center; padding: 12px;">Status</th>
                            <th style="width: 150px; text-align: center; padding: 12px;">Last Modified</th>
                            <th style="width: 80px; text-align: center; padding: 12px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr data-post-type="<?php echo esc_attr($post->post_type); ?>" 
                            data-post-status="<?php echo esc_attr($post->post_status); ?>"
                            data-post-title="<?php echo esc_attr(strtolower($post->post_title)); ?>">
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" class="rup-post-checkbox" 
                                       value="<?php echo $post->ID; ?>" 
                                       style="transform: scale(1.2);">
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #0073aa;">
                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" 
                                       target="_blank" style="text-decoration: none; color: inherit;">
                                        <?php echo esc_html($post->post_title ?: '(No Title)'); ?>
                                    </a>
                                </strong>
                                <div style="color: #666; font-size: 12px; margin-top: 4px;">
                                    <?php 
                                    $excerpt = $post->post_excerpt ?: $post->post_content;
                                    echo esc_html(wp_trim_words(strip_tags($excerpt), 15)); 
                                    ?>
                                </div>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span class="post-type-badge" style="
                                    background: <?php echo $post->post_type === 'page' ? '#2271b1' : '#00a32a'; ?>;
                                    color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; 
                                    font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
                                ">
                                    <?php echo esc_html($post->post_type); ?>
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span class="post-status-badge" style="
                                    background: <?php 
                                        switch($post->post_status) {
                                            case 'publish': echo '#00a32a';
                                            case 'draft': echo '#dba617';
                                            case 'private': echo '#8b1538';
                                            case 'pending': echo '#d63638';
                                            default: echo '#666';
                                        }
                                    ?>;
                                    color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px;
                                    font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
                                ">
                                    <?php echo esc_html($post->post_status); ?>
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-size: 13px;">
                                <?php echo esc_html(date('M j, Y', strtotime($post->post_modified))); ?>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-family: monospace;">
                                <?php echo $post->ID; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Status Messages -->
            <div id="rup-status-messages" style="margin-top: 20px;"></div>
        </div>
        
        <style>
        #rup-posts-table tbody tr:hover {
            background-color: #f0f8ff !important;
        }
        
        .rup-post-checkbox:checked {
            accent-color: #0073aa;
        }
        
        #rup-posts-table tbody tr.selected {
            background-color: #e8f4f8 !important;
        }
        
        .rup-status-success {
            background: #d1eddb; 
            color: #155724; 
            padding: 12px; 
            border: 1px solid #c3e6cb; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        
        .rup-status-error {
            background: #f8d7da; 
            color: #721c24; 
            padding: 12px; 
            border: 1px solid #f5c6cb; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        
        .rup-status-processing {
            background: #d4edda; 
            color: #155724; 
            padding: 12px; 
            border: 1px solid #c3e6cb; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            var selectedCount = 0;
            
            // Update selection count
            function updateSelectionCount() {
                selectedCount = $('.rup-post-checkbox:checked').length;
                $('#rup-selection-count').text(selectedCount + ' items selected');
                $('#rup-duplicate-selected').prop('disabled', selectedCount === 0);
            }
            
            // Handle individual checkbox changes
            $(document).on('change', '.rup-post-checkbox', function() {
                var $row = $(this).closest('tr');
                if ($(this).is(':checked')) {
                    $row.addClass('selected');
                } else {
                    $row.removeClass('selected');
                    $('#rup-select-all-checkbox').prop('checked', false);
                }
                updateSelectionCount();
            });
            
            // Handle select all checkbox
            $('#rup-select-all-checkbox').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('#rup-posts-table tbody tr:visible .rup-post-checkbox').prop('checked', isChecked);
                $('#rup-posts-table tbody tr:visible').toggleClass('selected', isChecked);
                updateSelectionCount();
            });
            
            // Select all visible button
            $('#rup-select-all').on('click', function() {
                $('#rup-posts-table tbody tr:visible .rup-post-checkbox').prop('checked', true);
                $('#rup-posts-table tbody tr:visible').addClass('selected');
                updateSelectionCount();
            });
            
            // Deselect all button  
            $('#rup-deselect-all').on('click', function() {
                $('.rup-post-checkbox').prop('checked', false);
                $('#rup-posts-table tbody tr').removeClass('selected');
                $('#rup-select-all-checkbox').prop('checked', false);
                updateSelectionCount();
            });
            
            // Search functionality
            $('#rup-search-posts').on('input', function() {
                filterTable();
            });
            
            // Filter functionality
            $('#rup-filter-type, #rup-filter-status').on('change', function() {
                filterTable();
            });
            
            // Clear filters
            $('#rup-clear-filters').on('click', function() {
                $('#rup-search-posts').val('');
                $('#rup-filter-type').val('');
                $('#rup-filter-status').val('');
                filterTable();
            });
            
            // Filter table function
            function filterTable() {
                var searchText = $('#rup-search-posts').val().toLowerCase();
                var filterType = $('#rup-filter-type').val();
                var filterStatus = $('#rup-filter-status').val();
                
                $('#rup-posts-table tbody tr').each(function() {
                    var $row = $(this);
                    var title = $row.data('post-title');
                    var type = $row.data('post-type');
                    var status = $row.data('post-status');
                    
                    var showRow = true;
                    
                    // Search filter
                    if (searchText && title.indexOf(searchText) === -1) {
                        showRow = false;
                    }
                    
                    // Type filter
                    if (filterType && type !== filterType) {
                        showRow = false;
                    }
                    
                    // Status filter
                    if (filterStatus && status !== filterStatus) {
                        showRow = false;
                    }
                    
                    $row.toggle(showRow);
                });
                
                // Update select all checkbox state
                updateSelectionCount();
            }
            
            // Duplicate selected posts
            $('#rup-duplicate-selected').on('click', function() {
                var selectedPosts = [];
                $('.rup-post-checkbox:checked').each(function() {
                    selectedPosts.push($(this).val());
                });
                
                if (selectedPosts.length === 0) {
                    alert('Please select at least one item to duplicate.');
                    return;
                }
                
                if (!confirm('Are you sure you want to duplicate ' + selectedPosts.length + ' selected items?')) {
                    return;
                }
                
                var $button = $(this);
                var originalText = $button.text();
                $button.prop('disabled', true).html('🔄 Processing...');
                
                $('#rup-status-messages').html(
                    '<div class="rup-status-processing">Processing duplication of ' + selectedPosts.length + ' items...</div>'
                );
                
                // Process duplications via AJAX
                duplicatePosts(selectedPosts, 0, [], []);
                
                function duplicatePosts(postIds, index, successes, errors) {
                    if (index >= postIds.length) {
                        // All done
                        $button.prop('disabled', false).text(originalText);
                        
                        var html = '';
                        if (successes.length > 0) {
                            html += '<div class="rup-status-success">Successfully duplicated ' + successes.length + ' items:<ul>';
                            successes.forEach(function(success) {
                                html += '<li><strong>' + success.title + '</strong> → <a href="' + success.edit_url + '" target="_blank">Edit Copy</a></li>';
                            });
                            html += '</ul></div>';
                        }
                        
                        if (errors.length > 0) {
                            html += '<div class="rup-status-error">Errors occurred for ' + errors.length + ' items:<ul>';
                            errors.forEach(function(error) {
                                html += '<li>' + error + '</li>';
                            });
                            html += '</ul></div>';
                        }
                        
                        $('#rup-status-messages').html(html);
                        
                        // Deselect all checkboxes
                        $('#rup-deselect-all').click();
                        
                        return;
                    }
                    
                    var postId = postIds[index];
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'rup_duplicate_single_post',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('rup_duplicate_single_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                successes.push(response.data);
                            } else {
                                errors.push('Post ID ' + postId + ': ' + response.data);
                            }
                        },
                        error: function() {
                            errors.push('Post ID ' + postId + ': AJAX request failed');
                        },
                        complete: function() {
                            // Process next item
                            duplicatePosts(postIds, index + 1, successes, errors);
                        }
                    });
                }
            });
            
            // Initialize
            updateSelectionCount();
        });
        </script>
        <?php
    }
    
    /**
     * rup_pantheon_mar page - Pantheon Table Management
     */
    public function rup_pantheon_mar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap">
            <h1><strong>rup_pantheon_mar</strong></h1>
            <p>Pantheon Table - Manage turtle entities and related parameters</p>
            
            <!-- Search and Filter Controls -->
            <div style="background: #f9f9f9; padding: 15px; margin: 20px 0; border: 1px solid #ddd; border-radius: 6px;">
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <label><strong>Search:</strong></label>
                    <input type="text" id="pantheon-search" placeholder="Search descriptions..." style="padding: 6px; border: 1px solid #ddd; border-radius: 3px; width: 250px;">
                    
                    <label><strong>Filter Type:</strong></label>
                    <select id="pantheon-filter-type" style="padding: 6px; border: 1px solid #ddd; border-radius: 3px;">
                        <option value="">All Types</option>
                        <option value="chen">Chen Pages</option>
                        <option value="sitejar4">SiteJar4</option>
                        <option value="driggsman">Driggsman</option>
                        <option value="gcjar1">GCJar1</option>
                        <option value="nwjar1">NWJar1</option>
                    </select>
                    
                    <button type="button" id="pantheon-clear-filters" class="button">Clear Filters</button>
                </div>
            </div>
            
            <!-- Bulk Actions -->
            <div style="margin: 20px 0; padding: 15px; background: #fff; border: 2px solid #0073aa; border-radius: 6px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button type="button" id="pantheon-process-selected" class="button button-primary" style="background: linear-gradient(135deg, #0073aa 0%, #005177 100%); padding: 10px 20px; font-weight: 600;">
                        🚀 Process Selected Items
                    </button>
                    <span id="pantheon-selection-count" style="color: #666; font-weight: 500;">0 items selected</span>
                    <button type="button" id="pantheon-select-all" class="button" style="margin-left: auto;">Select All Visible</button>
                    <button type="button" id="pantheon-deselect-all" class="button">Deselect All</button>
                </div>
            </div>
            
            <!-- Pantheon Table -->
            <div style="background: white; border: 1px solid #ddd; border-radius: 6px; overflow: hidden;">
                <table class="wp-list-table widefat fixed striped" id="pantheon-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center; padding: 12px 8px;">
                                <input type="checkbox" id="pantheon-select-all-checkbox" style="transform: scale(1.2);">
                            </th>
                            <th style="padding: 12px; font-weight: 600;">Description</th>
                            <th style="width: 150px; text-align: center; padding: 12px;">Name</th>
                            <th style="width: 150px; text-align: center; padding: 12px;">ID Code of Turtle</th>
                            <th style="width: 200px; text-align: center; padding: 12px;">Related Entities</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-type="chen" data-description="open chen page">
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" class="pantheon-checkbox" value="chen-1" style="transform: scale(1.2);">
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #0073aa;">open chen page</strong>
                                <div style="color: #666; font-size: 12px; margin-top: 4px;">
                                    Navigate to chen administration interface
                                </div>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span style="background: #2271b1; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    CHEN-1
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-family: monospace;">
                                turtle_chen_01
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666;">
                                admin_interface, navigation
                            </td>
                        </tr>
                        
                        <tr data-type="chen" data-description="open chen page">
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" class="pantheon-checkbox" value="chen-2" style="transform: scale(1.2);">
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #0073aa;">open chen page</strong>
                                <div style="color: #666; font-size: 12px; margin-top: 4px;">
                                    Navigate to chen administration interface
                                </div>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span style="background: #2271b1; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    CHEN-2
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-family: monospace;">
                                turtle_chen_02
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666;">
                                admin_interface, navigation
                            </td>
                        </tr>
                        
                        <tr data-type="chen" data-description="open chen page">
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" class="pantheon-checkbox" value="chen-3" style="transform: scale(1.2);">
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #0073aa;">open chen page</strong>
                                <div style="color: #666; font-size: 12px; margin-top: 4px;">
                                    Navigate to chen administration interface
                                </div>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span style="background: #2271b1; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    CHEN-3
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-family: monospace;">
                                turtle_chen_03
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666;">
                                admin_interface, navigation
                            </td>
                        </tr>
                        
                        <tr data-type="sitejar4" data-description="open /sitejar4?PARAMETER">
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" class="pantheon-checkbox" value="sitejar4-1" style="transform: scale(1.2);">
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #0073aa;">open /sitejar4?PARAMETER</strong>
                                <div style="color: #666; font-size: 12px; margin-top: 4px;">
                                    Access SiteJar4 with dynamic parameter
                                </div>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span style="background: #00a32a; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    SITEJAR4
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-family: monospace;">
                                turtle_sitejar4_01
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666;">
                                parameter_handler, site_access
                            </td>
                        </tr>
                        
                        <tr data-type="driggsman" data-description="open /driggsman?PARAMETER">
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" class="pantheon-checkbox" value="driggsman-1" style="transform: scale(1.2);">
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #0073aa;">open /driggsman?PARAMETER</strong>
                                <div style="color: #666; font-size: 12px; margin-top: 4px;">
                                    Access Driggsman interface with parameter
                                </div>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span style="background: #d63638; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    DRIGGSMAN
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-family: monospace;">
                                turtle_driggsman_01
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666;">
                                parameter_handler, driggs_access
                            </td>
                        </tr>
                        
                        <tr data-type="gcjar1" data-description="open /gcjar1?PARAMETER">
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" class="pantheon-checkbox" value="gcjar1-1" style="transform: scale(1.2);">
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #0073aa;">open /gcjar1?PARAMETER</strong>
                                <div style="color: #666; font-size: 12px; margin-top: 4px;">
                                    Access GCJar1 interface with parameter
                                </div>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span style="background: #dba617; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    GCJAR1
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-family: monospace;">
                                turtle_gcjar1_01
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666;">
                                parameter_handler, gc_access
                            </td>
                        </tr>
                        
                        <tr data-type="nwjar1" data-description="open /nwjar1?PARAMETER">
                            <td style="text-align: center; padding: 12px 8px;">
                                <input type="checkbox" class="pantheon-checkbox" value="nwjar1-1" style="transform: scale(1.2);">
                            </td>
                            <td style="padding: 12px;">
                                <strong style="color: #0073aa;">open /nwjar1?PARAMETER</strong>
                                <div style="color: #666; font-size: 12px; margin-top: 4px;">
                                    Access NWJar1 interface with parameter
                                </div>
                            </td>
                            <td style="text-align: center; padding: 12px;">
                                <span style="background: #8b1538; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    NWJAR1
                                </span>
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666; font-family: monospace;">
                                turtle_nwjar1_01
                            </td>
                            <td style="text-align: center; padding: 12px; color: #666;">
                                parameter_handler, nw_access
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Status Messages -->
            <div id="pantheon-status-messages" style="margin-top: 20px;"></div>
        </div>
        
        <style>
        #pantheon-table tbody tr:hover {
            background-color: #f0f8ff !important;
        }
        
        .pantheon-checkbox:checked {
            accent-color: #0073aa;
        }
        
        #pantheon-table tbody tr.selected {
            background-color: #e8f4f8 !important;
        }
        
        .pantheon-status-success {
            background: #d1eddb; 
            color: #155724; 
            padding: 12px; 
            border: 1px solid #c3e6cb; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        
        .pantheon-status-error {
            background: #f8d7da; 
            color: #721c24; 
            padding: 12px; 
            border: 1px solid #f5c6cb; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        
        .pantheon-status-processing {
            background: #d4edda; 
            color: #155724; 
            padding: 12px; 
            border: 1px solid #c3e6cb; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            var selectedCount = 0;
            
            // Update selection count
            function updateSelectionCount() {
                selectedCount = $('.pantheon-checkbox:checked').length;
                $('#pantheon-selection-count').text(selectedCount + ' items selected');
                $('#pantheon-process-selected').prop('disabled', selectedCount === 0);
            }
            
            // Handle individual checkbox changes
            $(document).on('change', '.pantheon-checkbox', function() {
                var $row = $(this).closest('tr');
                if ($(this).is(':checked')) {
                    $row.addClass('selected');
                } else {
                    $row.removeClass('selected');
                }
                updateSelectionCount();
            });
            
            // Handle select all checkbox
            $('#pantheon-select-all-checkbox').change(function() {
                var checked = $(this).is(':checked');
                $('#pantheon-table tbody tr:visible .pantheon-checkbox').each(function() {
                    $(this).prop('checked', checked);
                    var $row = $(this).closest('tr');
                    if (checked) {
                        $row.addClass('selected');
                    } else {
                        $row.removeClass('selected');
                    }
                });
                updateSelectionCount();
            });
            
            // Handle select all visible button
            $('#pantheon-select-all').click(function() {
                $('#pantheon-table tbody tr:visible .pantheon-checkbox').each(function() {
                    $(this).prop('checked', true);
                    $(this).closest('tr').addClass('selected');
                });
                updateSelectionCount();
            });
            
            // Handle deselect all button
            $('#pantheon-deselect-all').click(function() {
                $('.pantheon-checkbox').prop('checked', false);
                $('#pantheon-table tbody tr').removeClass('selected');
                $('#pantheon-select-all-checkbox').prop('checked', false);
                updateSelectionCount();
            });
            
            // Search functionality
            $('#pantheon-search').on('input', function() {
                filterTable();
            });
            
            // Filter functionality
            $('#pantheon-filter-type').change(function() {
                filterTable();
            });
            
            // Clear filters
            $('#pantheon-clear-filters').click(function() {
                $('#pantheon-search').val('');
                $('#pantheon-filter-type').val('');
                filterTable();
            });
            
            // Filter table function
            function filterTable() {
                var searchText = $('#pantheon-search').val().toLowerCase();
                var typeFilter = $('#pantheon-filter-type').val();
                
                $('#pantheon-table tbody tr').each(function() {
                    var $row = $(this);
                    var description = $row.attr('data-description').toLowerCase();
                    var type = $row.attr('data-type');
                    
                    var matchesSearch = searchText === '' || description.includes(searchText);
                    var matchesType = typeFilter === '' || type === typeFilter;
                    
                    if (matchesSearch && matchesType) {
                        $row.show();
                    } else {
                        $row.hide();
                        // Uncheck hidden rows
                        $row.find('.pantheon-checkbox').prop('checked', false);
                        $row.removeClass('selected');
                    }
                });
                
                updateSelectionCount();
            }
            
            // Handle process selected button
            $('#pantheon-process-selected').click(function() {
                var selectedItems = [];
                $('.pantheon-checkbox:checked').each(function() {
                    selectedItems.push({
                        value: $(this).val(),
                        description: $(this).closest('tr').find('td:nth-child(2) strong').text(),
                        type: $(this).closest('tr').attr('data-type')
                    });
                });
                
                if (selectedItems.length === 0) {
                    alert('Please select at least one item to process.');
                    return;
                }
                
                // Show processing message
                var $statusDiv = $('#pantheon-status-messages');
                $statusDiv.html('<div class="pantheon-status-processing">🔄 Processing ' + selectedItems.length + ' selected items...</div>');
                
                // Simulate processing (replace with actual AJAX call)
                setTimeout(function() {
                    var successHtml = '<div class="pantheon-status-success">✅ Successfully processed ' + selectedItems.length + ' items:<br>';
                    selectedItems.forEach(function(item) {
                        successHtml += '• ' + item.description + ' (' + item.value + ')<br>';
                    });
                    successHtml += '</div>';
                    $statusDiv.html(successHtml);
                }, 2000);
            });
            
            // Initialize
            updateSelectionCount();
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for single post duplication
     */
    public function rup_duplicate_single_post() {
        check_ajax_referer('rup_duplicate_single_nonce', 'nonce');
        
        if (!current_user_can('edit_pages') && !current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
            return;
        }
        
        // Get the original post
        $original_post = get_post($post_id);
        if (!$original_post) {
            wp_send_json_error('Original page/post not found');
            return;
        }
        
        // Create the duplicate post data
        $duplicate_post_data = array(
            'post_title'     => 'Copy of ' . $original_post->post_title,
            'post_content'   => $original_post->post_content,
            'post_status'    => 'draft', // Always create as draft for safety
            'post_type'      => $original_post->post_type,
            'post_author'    => get_current_user_id(),
            'post_excerpt'   => $original_post->post_excerpt,
            'post_parent'    => $original_post->post_parent,
            'menu_order'     => $original_post->menu_order,
            'comment_status' => $original_post->comment_status,
            'ping_status'    => $original_post->ping_status
        );
        
        // Insert the duplicate post
        $duplicate_post_id = wp_insert_post($duplicate_post_data, true);
        
        if (is_wp_error($duplicate_post_id)) {
            wp_send_json_error('Failed to create duplicate: ' . $duplicate_post_id->get_error_message());
            return;
        }
        
        // Copy all post meta, including Elementor data
        $post_meta = get_post_meta($post_id);
        foreach ($post_meta as $meta_key => $meta_values) {
            // Skip certain meta keys that should be unique
            if (in_array($meta_key, array('_edit_lock', '_edit_last'))) {
                continue;
            }
            
            foreach ($meta_values as $meta_value) {
                add_post_meta($duplicate_post_id, $meta_key, maybe_unserialize($meta_value));
            }
        }
        
        // Copy taxonomies (categories, tags, etc.)
        $taxonomies = get_object_taxonomies($original_post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
            if (!is_wp_error($terms) && !empty($terms)) {
                wp_set_post_terms($duplicate_post_id, $terms, $taxonomy);
            }
        }
        
        // Generate URLs for the response
        $edit_url = admin_url('post.php?action=edit&post=' . $duplicate_post_id);
        
        // Return success response
        wp_send_json_success(array(
            'title' => 'Copy of ' . $original_post->post_title,
            'edit_url' => $edit_url,
            'original_title' => $original_post->post_title
        ));
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
        
        // Validate field name - all editable fields from zen_sitespren table
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
            // Build the update query dynamically based on field type
            $format = '%s'; // Default format for strings
            
            if (in_array($field, $number_fields) || in_array($field, $boolean_fields)) {
                $format = '%d'; // Use integer format for numbers and booleans
            }
            
            // Use update method which is safer for dynamic field names
            $update_data = array(
                $field => $value,
                'wppma_db_only_updated_at' => current_time('mysql')
            );
            
            $update_formats = array(
                $format,
                '%s'
            );
            
            // Update the single record - we update all rows since there should only be one
            // First check if there's a record
            $has_record = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            
            if ($has_record == 0) {
                // No record exists, create one first
                $this->ensure_sitespren_record();
            }
            
            // Now update - using 1=1 condition to update all rows
            $sql = $wpdb->prepare(
                "UPDATE $table_name SET `$field` = $format, `wppma_db_only_updated_at` = %s WHERE 1=1",
                $value,
                current_time('mysql')
            );
            
            $result = $wpdb->query($sql);
            
            // If no rows were updated but also no error, it might mean the value was already the same
            if ($result === 0 && !$wpdb->last_error) {
                // Still consider it a success if no error occurred
                wp_send_json_success('Field value unchanged or already up to date');
                return;
            }
            
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
     * Handle sitespren export AJAX request
     */
    public function handle_sitespren_export() {
        check_ajax_referer('sitespren_export_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'zen_sitespren';
            $wppma_id = isset($_POST['wppma_id']) ? intval($_POST['wppma_id']) : 1;
            $formats = isset($_POST['formats']) ? $_POST['formats'] : array();
            $transpose = isset($_POST['transpose']) ? sanitize_text_field($_POST['transpose']) : 'no';
            
            if (empty($formats)) {
                wp_send_json_error('No formats selected');
            }
            
            // Get the sitespren row data
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE wppma_id = %d", $wppma_id), ARRAY_A);
            
            if (!$row) {
                wp_send_json_error('No sitespren record found with wppma_id = ' . $wppma_id);
            }
            
            $generated_files = array();
            $upload_dir = wp_upload_dir();
            $export_dir = $upload_dir['basedir'] . '/sitespren_exports';
            $export_url = $upload_dir['baseurl'] . '/sitespren_exports';
            
            // Create export directory if it doesn't exist
            if (!is_dir($export_dir)) {
                wp_mkdir_p($export_dir);
            }
            
            foreach ($formats as $format) {
                $filename = 'sitespren_' . $wppma_id . '_' . date('Y-m-d_H-i-s') . '.' . $format;
                $filepath = $export_dir . '/' . $filename;
                $fileurl = $export_url . '/' . $filename;
                
                switch ($format) {
                    case 'csv':
                        $this->export_to_csv($row, $filepath, $transpose);
                        break;
                    case 'xlsx':
                    case 'xls':
                        $this->export_to_excel($row, $filepath, $format, $transpose);
                        break;
                    case 'sql':
                        $this->export_to_sql($row, $filepath, $table_name);
                        break;
                    case 'md':
                        $this->export_to_markdown($row, $filepath);
                        break;
                    default:
                        continue 2;
                }
                
                if (file_exists($filepath)) {
                    $generated_files[] = array(
                        'filename' => $filename,
                        'url' => $fileurl,
                        'format' => $format
                    );
                }
            }
            
            if (empty($generated_files)) {
                wp_send_json_error('Failed to generate export files');
            }
            
            wp_send_json_success(array(
                'message' => 'Export completed successfully',
                'files' => $generated_files
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Export failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Save Hudson ImgPlanBatch ID to zen_orbitposts table
     */
    public function save_hudson_imgplanbatch_id() {
        check_ajax_referer('hurricane_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $hudson_id = isset($_POST['hudson_id']) ? sanitize_text_field($_POST['hudson_id']) : '';
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        if (empty($hudson_id)) {
            wp_send_json_error('Hudson ID is required');
        }
        
        // Validate UUID format
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $hudson_id)) {
            wp_send_json_error('Invalid UUID format');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_orbitposts';
        
        try {
            // Check if a record exists for this post_id
            $existing_record = $wpdb->get_row($wpdb->prepare(
                "SELECT orbitpost_id FROM $table_name WHERE rel_wp_post_id = %d",
                $post_id
            ));
            
            if ($existing_record) {
                // Update existing record
                $result = $wpdb->update(
                    $table_name,
                    array(
                        'hudson_imgplanbatch_id' => $hudson_id,
                        'updated_at' => current_time('mysql')
                    ),
                    array('rel_wp_post_id' => $post_id),
                    array('%s', '%s'),
                    array('%d')
                );
            } else {
                // Insert new record
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'rel_wp_post_id' => $post_id,
                        'hudson_imgplanbatch_id' => $hudson_id,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ),
                    array('%d', '%s', '%s', '%s')
                );
            }
            
            if ($result === false) {
                wp_send_json_error('Database error: ' . $wpdb->last_error);
            }
            
            wp_send_json_success('Hudson ImgPlanBatch ID saved successfully');
            
        } catch (Exception $e) {
            wp_send_json_error('Error saving Hudson ID: ' . $e->getMessage());
        }
    }
    
    /**
     * Export data to CSV format
     */
    private function export_to_csv($data, $filepath, $transpose = 'no') {
        $fp = fopen($filepath, 'w');
        
        if ($transpose === 'yes') {
            // Transpose: First column = field names, second column = values
            foreach ($data as $key => $value) {
                fputcsv($fp, array($key, $value));
            }
        } else {
            // Normal: Headers row, then data row
            fputcsv($fp, array_keys($data)); // Headers
            fputcsv($fp, array_values($data)); // Data
        }
        
        fclose($fp);
    }
    
    /**
     * Export data to Excel format
     */
    private function export_to_excel($data, $filepath, $format, $transpose = 'no') {
        if ($format === 'xlsx') {
            // Create a proper XLSX file using XML structure
            $this->create_xlsx_file($data, $filepath, $transpose);
        } else {
            // For XLS, create a simple HTML table that Excel can import
            $this->create_xls_html($data, $filepath, $transpose);
        }
    }
    
    /**
     * Create a proper XLSX file with minimal XML structure
     */
    private function create_xlsx_file($data, $filepath, $transpose = 'no') {
        // Create temporary directory for XLSX components
        $temp_dir = sys_get_temp_dir() . '/xlsx_' . uniqid();
        mkdir($temp_dir);
        mkdir($temp_dir . '/_rels');
        mkdir($temp_dir . '/docProps');
        mkdir($temp_dir . '/xl');
        mkdir($temp_dir . '/xl/_rels');
        mkdir($temp_dir . '/xl/worksheets');
        
        // Create [Content_Types].xml
        file_put_contents($temp_dir . '/[Content_Types].xml', 
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' .
            '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>' .
            '</Types>');
        
        // Create _rels/.rels
        file_put_contents($temp_dir . '/_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>' .
            '</Relationships>');
        
        // Create docProps/app.xml
        file_put_contents($temp_dir . '/docProps/app.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">' .
            '<Application>Snefuruplin</Application>' .
            '</Properties>');
        
        // Create docProps/core.xml
        file_put_contents($temp_dir . '/docProps/core.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties">' .
            '<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">Snefuruplin</dc:creator>' .
            '<dcterms:created xmlns:dcterms="http://purl.org/dc/terms/" xsi:type="dcterms:W3CDTF" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . date('c') . '</dcterms:created>' .
            '</cp:coreProperties>');
        
        // Create xl/_rels/workbook.xml.rels
        file_put_contents($temp_dir . '/xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '</Relationships>');
        
        // Create xl/workbook.xml
        file_put_contents($temp_dir . '/xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheets><sheet name="Sitespren Data" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/></sheets>' .
            '</workbook>');
        
        // Create worksheet data
        $worksheet_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheetData>';
        
        if ($transpose === 'yes') {
            // Transpose mode: Each row contains field name and value
            $row = 1;
            foreach ($data as $key => $value) {
                $worksheet_xml .= '<row r="' . $row . '">';
                
                // Field name in column A
                $cell_ref_a = 'A' . $row;
                $worksheet_xml .= '<c r="' . $cell_ref_a . '" t="inlineStr"><is><t>' . htmlspecialchars($key) . '</t></is></c>';
                
                // Field value in column B
                $cell_ref_b = 'B' . $row;
                $worksheet_xml .= '<c r="' . $cell_ref_b . '" t="inlineStr"><is><t>' . htmlspecialchars($value) . '</t></is></c>';
                
                $worksheet_xml .= '</row>';
                $row++;
            }
        } else {
            // Normal mode: Header row then data row
            
            // Add header row
            $worksheet_xml .= '<row r="1">';
            $col = 1;
            foreach (array_keys($data) as $header) {
                $cell_ref = $this->excel_column_name($col) . '1';
                $worksheet_xml .= '<c r="' . $cell_ref . '" t="inlineStr"><is><t>' . htmlspecialchars($header) . '</t></is></c>';
                $col++;
            }
            $worksheet_xml .= '</row>';
            
            // Add data row
            $worksheet_xml .= '<row r="2">';
            $col = 1;
            foreach (array_values($data) as $value) {
                $cell_ref = $this->excel_column_name($col) . '2';
                $worksheet_xml .= '<c r="' . $cell_ref . '" t="inlineStr"><is><t>' . htmlspecialchars($value) . '</t></is></c>';
                $col++;
            }
            $worksheet_xml .= '</row>';
        }
        
        $worksheet_xml .= '</sheetData></worksheet>';
        
        file_put_contents($temp_dir . '/xl/worksheets/sheet1.xml', $worksheet_xml);
        
        // Create ZIP file
        $this->create_zip_archive($temp_dir, $filepath);
        
        // Clean up temp directory
        $this->recursive_rmdir($temp_dir);
    }
    
    /**
     * Create XLS file as HTML table (Excel can import this)
     */
    private function create_xls_html($data, $filepath, $transpose = 'no') {
        $html = '<html><head><meta charset="UTF-8"></head><body>' .
                '<table border="1">';
        
        if ($transpose === 'yes') {
            // Transpose mode: Each row contains field name and value
            foreach ($data as $key => $value) {
                $html .= '<tr><td><strong>' . htmlspecialchars($key) . '</strong></td><td>' . htmlspecialchars($value) . '</td></tr>';
            }
        } else {
            // Normal mode: Header row then data row
            $html .= '<tr>';
            
            // Headers
            foreach (array_keys($data) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr><tr>';
            
            // Data
            foreach (array_values($data) as $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table></body></html>';
        
        file_put_contents($filepath, $html);
    }
    
    /**
     * Convert column number to Excel column name (A, B, C, ..., AA, AB, etc.)
     */
    private function excel_column_name($col) {
        $name = '';
        while ($col > 0) {
            $col--;
            $name = chr($col % 26 + 65) . $name;
            $col = intval($col / 26);
        }
        return $name;
    }
    
    /**
     * Create ZIP archive from directory
     */
    private function create_zip_archive($source_dir, $output_file) {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($output_file, ZipArchive::CREATE) === TRUE) {
                $this->add_dir_to_zip($zip, $source_dir, '');
                $zip->close();
            }
        } else {
            // Fallback: use system zip command if available
            $output_dir = dirname($output_file);
            $output_name = basename($output_file);
            exec("cd '$source_dir' && zip -r '$output_file' .", $output, $return_code);
        }
    }
    
    /**
     * Add directory contents to ZIP archive recursively
     */
    private function add_dir_to_zip($zip, $dir, $prefix) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $filepath = $dir . '/' . $file;
            $zippath = $prefix . $file;
            if (is_dir($filepath)) {
                $zip->addEmptyDir($zippath);
                $this->add_dir_to_zip($zip, $filepath, $zippath . '/');
            } else {
                $zip->addFile($filepath, $zippath);
            }
        }
    }
    
    /**
     * Recursively remove directory
     */
    private function recursive_rmdir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filepath = $dir . '/' . $file;
                    if (is_dir($filepath)) {
                        $this->recursive_rmdir($filepath);
                    } else {
                        unlink($filepath);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    /**
     * Export data to SQL format
     */
    private function export_to_sql($data, $filepath, $table_name) {
        $columns = implode(', ', array_map(function($col) { return "`$col`"; }, array_keys($data)));
        $values = implode(', ', array_map(function($val) { 
            return "'" . addslashes($val) . "'"; 
        }, array_values($data)));
        
        $sql = "INSERT INTO `$table_name` ($columns) VALUES ($values);";
        file_put_contents($filepath, $sql);
    }
    
    /**
     * Export data to Markdown format
     */
    private function export_to_markdown($data, $filepath) {
        $content = "# Sitespren Export\n\n";
        $content .= "| Field | Value |\n";
        $content .= "|-------|-------|\n";
        
        foreach ($data as $key => $value) {
            $content .= "| `$key` | " . str_replace('|', '\\|', $value) . " |\n";
        }
        
        file_put_contents($filepath, $content);
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
     * rup_sitespren_export page - Export Sitespren Data
     */
    public function rup_sitespren_export_page() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        // Handle AJAX requests
        if (isset($_POST['action']) && $_POST['action'] === 'export_sitespren') {
            $this->handle_sitespren_export();
            return;
        }
        
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <div style="padding: 20px;">
                <h1 style="margin-bottom: 20px;">📊 Sitespren Export</h1>
                
                <!-- Export Button -->
                <div style="margin-bottom: 20px;">
                    <button id="export-sitespren-btn" class="button button-primary" style="background: #4CAF50; border-color: #4CAF50; font-size: 16px; padding: 12px 20px;">
                        📤 Export 1 Sitespren Row
                    </button>
                </div>
                
                <!-- Export Format Selection Table -->
                <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                    <table id="export-formats-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f0f0f0; width: 50px;">
                                    <input type="checkbox" id="select-all-formats" style="width: 20px; height: 20px;">
                                </th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">File Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f9f9f9'" onmouseout="this.style.backgroundColor=''">
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <input type="checkbox" class="format-checkbox" value="xlsx" style="width: 20px; height: 20px;">
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d;">xlsx</td>
                            </tr>
                            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f9f9f9'" onmouseout="this.style.backgroundColor=''">
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <input type="checkbox" class="format-checkbox" value="xls" style="width: 20px; height: 20px;">
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d;">xls</td>
                            </tr>
                            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f9f9f9'" onmouseout="this.style.backgroundColor=''">
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <input type="checkbox" class="format-checkbox" value="csv" style="width: 20px; height: 20px;">
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d;">csv</td>
                            </tr>
                            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f9f9f9'" onmouseout="this.style.backgroundColor=''">
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <input type="checkbox" class="format-checkbox" value="sql" style="width: 20px; height: 20px;">
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d;">sql</td>
                            </tr>
                            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f9f9f9'" onmouseout="this.style.backgroundColor=''">
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <input type="checkbox" class="format-checkbox" value="md" style="width: 20px; height: 20px;">
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d;">md</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Transpose Option Table -->
                <div style="margin-top: 20px; background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                    <table id="transpose-options-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f0f0f0; width: 50px;">
                                    <!-- No select all checkbox for transpose -->
                                </th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;"><strong>transpose</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f9f9f9'" onmouseout="this.style.backgroundColor=''">
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <input type="radio" class="transpose-radio" name="transpose-option" value="yes" style="width: 20px; height: 20px;">
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d;">yes transpose</td>
                            </tr>
                            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f9f9f9'" onmouseout="this.style.backgroundColor=''">
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <input type="radio" class="transpose-radio" name="transpose-option" value="no" checked style="width: 20px; height: 20px;">
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d;">do not transpose</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
                    <p><strong>Instructions:</strong></p>
                    <ul>
                        <li>Select one or more file formats using the checkboxes</li>
                        <li>Click "Export 1 Sitespren Row" to export data for wppma_id = 1</li>
                        <li>Files will be generated and downloaded automatically</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Select all functionality
            $('#select-all-formats').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.format-checkbox').prop('checked', isChecked);
            });
            
            // Individual checkbox handling
            $('.format-checkbox').on('change', function() {
                var totalCheckboxes = $('.format-checkbox').length;
                var checkedCheckboxes = $('.format-checkbox:checked').length;
                
                if (checkedCheckboxes === 0) {
                    $('#select-all-formats').prop('checked', false).prop('indeterminate', false);
                } else if (checkedCheckboxes === totalCheckboxes) {
                    $('#select-all-formats').prop('checked', true).prop('indeterminate', false);
                } else {
                    $('#select-all-formats').prop('checked', false).prop('indeterminate', true);
                }
            });
            
            // Export button click
            $('#export-sitespren-btn').on('click', function() {
                var selectedFormats = [];
                $('.format-checkbox:checked').each(function() {
                    selectedFormats.push($(this).val());
                });
                
                if (selectedFormats.length === 0) {
                    alert('Please select at least one file format.');
                    return;
                }
                
                // Get transpose option
                var transposeOption = $('.transpose-radio:checked').val();
                
                if (!confirm('Export sitespren row where wppma_id = 1 in formats: ' + selectedFormats.join(', ') + ' (transpose: ' + transposeOption + ')?')) {
                    return;
                }
                
                var $button = $(this);
                var originalText = $button.text();
                $button.prop('disabled', true).text('🔄 Exporting...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'export_sitespren',
                        formats: selectedFormats,
                        transpose: transposeOption,
                        wppma_id: 1,
                        nonce: '<?php echo wp_create_nonce('sitespren_export_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Function to download a file using fetch for better reliability
                            function downloadFileWithFetch(fileInfo, index) {
                                setTimeout(function() {
                                    // Try fetch first for better cross-browser support
                                    fetch(fileInfo.url)
                                        .then(function(response) {
                                            return response.blob();
                                        })
                                        .then(function(blob) {
                                            // Create blob URL and download
                                            var blobUrl = window.URL.createObjectURL(blob);
                                            var link = document.createElement('a');
                                            link.href = blobUrl;
                                            link.download = fileInfo.filename;
                                            link.style.display = 'none';
                                            document.body.appendChild(link);
                                            link.click();
                                            document.body.removeChild(link);
                                            
                                            // Clean up blob URL
                                            setTimeout(function() {
                                                window.URL.revokeObjectURL(blobUrl);
                                            }, 100);
                                        })
                                        .catch(function(error) {
                                            console.error('Fetch download failed:', error);
                                            // Fallback to direct link method
                                            var link = document.createElement('a');
                                            link.href = fileInfo.url;
                                            link.download = fileInfo.filename;
                                            link.target = '_blank';
                                            link.style.display = 'none';
                                            document.body.appendChild(link);
                                            link.click();
                                            document.body.removeChild(link);
                                        });
                                }, index * 700); // Stagger downloads by 700ms to avoid browser blocking
                            }
                            
                            // Download each file
                            response.data.files.forEach(function(file, index) {
                                downloadFileWithFetch(file, index);
                            });
                            
                            // Show success message
                            var fileList = response.data.files.map(function(f) { 
                                return '• ' + f.filename; 
                            }).join('\\n');
                            
                            // Delay alert to ensure downloads start
                            setTimeout(function() {
                                alert('✅ Export completed!\\n\\nFiles are downloading:\\n' + fileList + '\\n\\nCheck your Downloads folder.');
                            }, 100);
                        } else {
                            alert('Export failed: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error occurred during export');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
        </script>
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
            'snefuru_page_rup_kpages_mar',
            'snefuru_page_rup_duplicate_mar',
            'snefuru_page_rup_horse_class_page',
            'snefuru_page_document_outlook_aug9',
            'snefuru_page_dynamic_images_man',
            'snefuru_page_rup_sitespren_export',
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
        
        // Check if we're on WP Pusher plugins page
        $is_wppusher_page = (isset($_GET['page']) && $_GET['page'] === 'wppusher-plugins') ||
                           ($screen->id === 'toplevel_page_wppusher-plugins') ||
                           ($screen->id === 'admin_page_wppusher-plugins');
        
        if ($is_snefuru_page || $is_editor_page) {
            // Suppress notices immediately
            $this->suppress_all_admin_notices();
        } elseif ($is_wppusher_page) {
            // For WP Pusher page, suppress all notices except WP Pusher's own
            $this->suppress_all_admin_notices_except_wppusher();
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
                'rup_driggs_mar', 'rup_kpages_mar', 'rup_duplicate_mar'
             ]));
        
        // Check if we're on WP Pusher page
        $is_wppusher_page = isset($_GET['page']) && $_GET['page'] === 'wppusher-plugins';
        
        // Check if we're on WordPress post/page editor
        $is_editor_page = $this->is_post_editor_page();
        
        if ($is_snefuru_page || $is_editor_page) {
            
            // Pre-emptively block all admin notice hooks
            add_action('admin_notices', function() { 
                ob_start(); // Start output buffering to catch any notices
            }, 0);
        } elseif ($is_wppusher_page) {
            // For WP Pusher page, suppress selectively
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
                'rup_driggs_mar', 'rup_kpages_mar', 'rup_duplicate_mar'
             ]));
        
        // Check if we're on WP Pusher page
        $is_wppusher_page = isset($_GET['page']) && $_GET['page'] === 'wppusher-plugins';
        
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
        } elseif ($is_wppusher_page) {
            // For WP Pusher, use selective suppression
            $this->suppress_all_admin_notices_except_wppusher();
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
     * Suppress all admin notices except those from WP Pusher plugin
     * Used specifically for the WP Pusher plugins page to keep it clean while allowing its own feedback
     */
    private function suppress_all_admin_notices_except_wppusher() {
        // Remove all admin notices immediately, but we'll re-add WP Pusher ones
        add_action('admin_print_styles', function() {
            global $wp_filter;
            
            // Store WP Pusher callbacks before clearing
            $wppusher_callbacks = array();
            $notice_hooks = array('admin_notices', 'all_admin_notices');
            
            foreach ($notice_hooks as $hook) {
                if (isset($wp_filter[$hook]) && isset($wp_filter[$hook]->callbacks)) {
                    foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
                        foreach ($callbacks as $callback_id => $callback) {
                            // Check if this is a WP Pusher callback
                            if (is_array($callback['function'])) {
                                $class_name = is_object($callback['function'][0]) ? 
                                    get_class($callback['function'][0]) : 
                                    $callback['function'][0];
                                    
                                if (stripos($class_name, 'pusher') !== false || 
                                    stripos($class_name, 'wppusher') !== false) {
                                    $wppusher_callbacks[$hook][$priority][$callback_id] = $callback;
                                }
                            } elseif (is_string($callback['function'])) {
                                if (stripos($callback['function'], 'pusher') !== false || 
                                    stripos($callback['function'], 'wppusher') !== false) {
                                    $wppusher_callbacks[$hook][$priority][$callback_id] = $callback;
                                }
                            }
                        }
                    }
                }
            }
            
            // Clear all notice hooks
            foreach ($notice_hooks as $hook) {
                remove_all_actions($hook);
            }
            
            // Re-add only WP Pusher callbacks
            foreach ($wppusher_callbacks as $hook => $priorities) {
                foreach ($priorities as $priority => $callbacks) {
                    foreach ($callbacks as $callback) {
                        add_action($hook, $callback['function'], $priority, $callback['accepted_args']);
                    }
                }
            }
        }, 0);
        
        // Hide non-WP Pusher notices via CSS, but allow action feedback
        add_action('admin_head', function() {
            echo '<style type="text/css">
                /* Hide all notices by default, but allow specific ones through */
                .notice:not(.wppusher-notice):not(.action-feedback),
                .notice-warning:not(.wppusher-notice):not(.action-feedback),
                .notice-error:not(.wppusher-notice):not(.action-feedback),
                .notice-success:not(.wppusher-notice):not(.action-feedback),
                .notice-info:not(.wppusher-notice):not(.action-feedback),
                .updated:not(.wppusher-notice):not(.action-feedback),
                .error:not(.wppusher-notice):not(.action-feedback),
                .update-nag:not(.wppusher-notice):not(.action-feedback) {
                    display: none !important;
                }
                
                /* Allow WP Pusher specific notices */
                .notice.wppusher-notice,
                .notice[data-plugin="wppusher"],
                .notice[id*="wppusher"],
                .wppusher-message,
                .wppusher-error,
                .wppusher-success,
                .wppusher-warning,
                .wppusher-info {
                    display: block !important;
                }
                
                /* Allow action feedback notices (temporarily) */
                .notice.action-feedback,
                .notice-success.action-feedback,
                .notice-error.action-feedback {
                    display: block !important;
                }
            </style>';
        });
        
        // JavaScript to dynamically filter notices
        add_action('admin_footer', function() {
            echo '<script type="text/javascript">
                jQuery(document).ready(function($) {
                    // Function to check if notice is from WP Pusher or related to page actions
                    function isWPPusherNotice(element) {
                        var $el = $(element);
                        var text = $el.text().toLowerCase();
                        var html = $el.html().toLowerCase();
                        
                        // Check for WP Pusher keywords and related terms
                        var pusherKeywords = ["pusher", "wppusher", "push-to-deploy", "github", "bitbucket", "gitlab"];
                        for (var i = 0; i < pusherKeywords.length; i++) {
                            if (text.indexOf(pusherKeywords[i]) !== -1 || html.indexOf(pusherKeywords[i]) !== -1) {
                                return true;
                            }
                        }
                        
                        // Check for WP Pusher classes or IDs
                        if ($el.hasClass("wppusher-notice") || 
                            $el.attr("id") && $el.attr("id").indexOf("wppusher") !== -1 ||
                            $el.attr("data-plugin") === "wppusher") {
                            return true;
                        }
                        
                        // Allow action feedback messages (plugin update results)
                        var actionKeywords = [
                            "plugin successfully updated", "successfully updated", "update successful",
                            "failed to update", "update failed", "error updating",
                            "plugin updated", "plugin installation", "deployment",
                            "successfully deployed", "deployment failed", "deployment successful"
                        ];
                        
                        for (var i = 0; i < actionKeywords.length; i++) {
                            if (text.indexOf(actionKeywords[i]) !== -1) {
                                return true;
                            }
                        }
                        
                        // Allow notices that appear immediately after form submissions or button clicks
                        // These are typically action feedback messages
                        if ($el.hasClass("notice-success") || $el.hasClass("notice-error")) {
                            var allowedSuccessTerms = ["updated", "success", "completed", "deployed"];
                            var allowedErrorTerms = ["failed", "error", "could not", "unable to"];
                            
                            for (var i = 0; i < allowedSuccessTerms.length; i++) {
                                if (text.indexOf(allowedSuccessTerms[i]) !== -1) {
                                    return true;
                                }
                            }
                            
                            for (var i = 0; i < allowedErrorTerms.length; i++) {
                                if (text.indexOf(allowedErrorTerms[i]) !== -1) {
                                    return true;
                                }
                            }
                        }
                        
                        return false;
                    }
                    
                    // Process notices immediately - either remove them or mark them as allowed
                    $(".notice, .updated, .error, .update-nag").each(function() {
                        if (isWPPusherNotice(this)) {
                            // Mark as allowed notice
                            $(this).addClass("action-feedback");
                        } else {
                            $(this).remove();
                        }
                    });
                    
                    // Monitor for dynamically added notices
                    if (typeof MutationObserver !== "undefined") {
                        var observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                $(mutation.addedNodes).each(function() {
                                    if ($(this).is(".notice, .updated, .error, .update-nag")) {
                                        if (isWPPusherNotice(this)) {
                                            $(this).addClass("action-feedback");
                                        } else {
                                            $(this).remove();
                                        }
                                    }
                                    
                                    $(this).find(".notice, .updated, .error, .update-nag").each(function() {
                                        if (isWPPusherNotice(this)) {
                                            $(this).addClass("action-feedback");
                                        } else {
                                            $(this).remove();
                                        }
                                    });
                                });
                            });
                        });
                        
                        observer.observe(document.body, {
                            childList: true,
                            subtree: true
                        });
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
    
    /**
     * One-time cleanup of zen_driggs table - runs once then sets flag to never run again
     */
    public function one_time_cleanup_zen_driggs() {
        // Check if cleanup has already been done
        if (get_option('snefuru_zen_driggs_cleaned_up', false)) {
            return;
        }
        
        global $wpdb;
        $zen_driggs_table = $wpdb->prefix . 'zen_driggs';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$zen_driggs_table'") == $zen_driggs_table;
        
        if ($table_exists) {
            $wpdb->query("DROP TABLE IF EXISTS $zen_driggs_table");
            error_log('Snefuru: One-time cleanup - Removed unwanted zen_driggs table');
        }
        
        // Set flag so this only runs once
        update_option('snefuru_zen_driggs_cleaned_up', true);
    }
    
    /**
     * AJAX: Handle content injection
     */
    public function snefuru_inject_content() {
        check_ajax_referer('snefuru_inject_content_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        $zeeprex_content = isset($_POST['zeeprex_content']) ? wp_kses_post($_POST['zeeprex_content']) : '';
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
            return;
        }
        
        if (empty($zeeprex_content)) {
            wp_send_json_error('No content provided');
            return;
        }
        
        // Include the content injector class
        require_once SNEFURU_PLUGIN_DIR . 'includes/class-elementor-content-injector.php';
        
        // Inject the content
        $result = Snefuru_Elementor_Content_Injector::inject_content($post_id, $zeeprex_content);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => $result['message']
            ));
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX: Handle page duplication
     */
    public function snefuru_duplicate_page() {
        // Temporary simple implementation to test if this method is causing the error
        wp_send_json_error('Page duplication temporarily disabled for debugging');
        return;
        
        /*
        check_ajax_referer('snefuru_duplicate_page_nonce', 'nonce');
        
        if (!current_user_can('edit_pages') && !current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        // Get the post ID from AJAX request
        $current_post_id = 0;
        if (isset($_POST['post_id'])) {
            $current_post_id = intval($_POST['post_id']);
        } else {
            // Fallback: try other methods
            $current_post_id = get_the_ID();
            if (!$current_post_id) {
                global $post;
                if ($post && $post->ID) {
                    $current_post_id = $post->ID;
                }
            }
        }
        
        if (!$current_post_id) {
            wp_send_json_error('Unable to determine current page/post ID');
            return;
        }
        
        // Get the original post
        $original_post = get_post($current_post_id);
        if (!$original_post) {
            wp_send_json_error('Original page/post not found');
            return;
        }
        
        // Create the duplicate post data
        $duplicate_post_data = array(
            'post_title'     => 'Copy of ' . $original_post->post_title,
            'post_content'   => $original_post->post_content,
            'post_status'    => 'draft', // Always create as draft for safety
            'post_type'      => $original_post->post_type,
            'post_author'    => get_current_user_id(),
            'post_excerpt'   => $original_post->post_excerpt,
            'post_parent'    => $original_post->post_parent,
            'menu_order'     => $original_post->menu_order,
            'comment_status' => $original_post->comment_status,
            'ping_status'    => $original_post->ping_status
        );
        
        // Insert the duplicate post
        $duplicate_post_id = wp_insert_post($duplicate_post_data, true);
        
        if (is_wp_error($duplicate_post_id)) {
            wp_send_json_error('Failed to create duplicate: ' . $duplicate_post_id->get_error_message());
            return;
        }
        
        // Copy all post meta, including Elementor data
        $post_meta = get_post_meta($current_post_id);
        foreach ($post_meta as $meta_key => $meta_values) {
            // Skip certain meta keys that should be unique
            if (in_array($meta_key, array('_edit_lock', '_edit_last'))) {
                continue;
            }
            
            foreach ($meta_values as $meta_value) {
                add_post_meta($duplicate_post_id, $meta_key, maybe_unserialize($meta_value));
            }
        }
        
        // Copy taxonomies (categories, tags, etc.)
        $taxonomies = get_object_taxonomies($original_post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($current_post_id, $taxonomy, array('fields' => 'ids'));
            if (!is_wp_error($terms) && !empty($terms)) {
                wp_set_post_terms($duplicate_post_id, $terms, $taxonomy);
            }
        }
        
        // Clear any Elementor cache for the new post
        if (class_exists('\\Elementor\\Plugin')) {
            if (method_exists('\\Elementor\\Plugin::$instance->files_manager', 'clear_cache')) {
                \\Elementor\\Plugin::$instance->files_manager->clear_cache();
            }
        }
        
        // Generate URLs for the response
        $edit_url = admin_url('post.php?action=edit&post=' . $duplicate_post_id);
        $view_url = get_permalink($duplicate_post_id);
        
        // Return success response
        wp_send_json_success(array(
            'message' => sprintf('Successfully created duplicate of "%s"', $original_post->post_title),
            'duplicate_id' => $duplicate_post_id,
            'duplicate_title' => 'Copy of ' . $original_post->post_title,
            'edit_url' => $edit_url,
            'view_url' => $view_url,
            'original_title' => $original_post->post_title
        ));
        */
    }
    
    /**
     * Kenli Mar page - shows all admin pages in the ruplin plugin
     */
    public function rup_kenli_mar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        // Get all admin pages from this plugin
        $admin_pages = $this->get_all_admin_pages();
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <div style="padding: 20px;">
                <h1 style="margin-bottom: 20px;">🔗 Kenli Mar - Ruplin Plugin Pages Manager</h1>
                
                <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                    <table id="kenli-mar-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f0f0f0; width: 80px;">ID</th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f8f9fa; width: 200px;">Page Name</th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f8f9fa; width: 250px;">Page Slug</th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f8f9fa;">Full URL</th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f8f9fa; width: 150px;">Type</th>
                                <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f8f9fa; width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $row_id = 1;
                            foreach ($admin_pages as $page) : 
                                $alternating_bg = ($row_id % 2 == 0) ? '#f9f9f9' : 'white';
                            ?>
                            <tr style="background: <?php echo $alternating_bg; ?>;" onmouseover="this.style.backgroundColor='#e8f4fd'" onmouseout="this.style.backgroundColor='<?php echo $alternating_bg; ?>'">
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center; font-weight: bold; color: #666;">
                                    <?php echo $row_id; ?>
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">
                                    <?php echo esc_html($page['title']); ?>
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; font-family: monospace; background: #f8f8f8;">
                                    <?php echo esc_html($page['slug']); ?>
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd;">
                                    <a href="<?php echo esc_url($page['url']); ?>" style="color: #0073aa; text-decoration: none; font-family: monospace; font-size: 12px;" target="_blank">
                                        <?php echo esc_html($page['url']); ?>
                                    </a>
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <span style="background: <?php echo $page['type'] === 'menu' ? '#4CAF50' : '#2196F3'; ?>; color: white; padding: 4px 8px; border-radius: 3px; font-size: 11px; text-transform: uppercase;">
                                        <?php echo esc_html($page['type']); ?>
                                    </span>
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                    <a href="<?php echo esc_url($page['url']); ?>" class="button button-small" style="background: #0073aa; color: white; text-decoration: none; padding: 4px 8px; border-radius: 3px; font-size: 11px;" target="_blank">
                                        Open →
                                    </a>
                                </td>
                            </tr>
                            <?php 
                            $row_id++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border: 1px solid #b3d9ff; border-radius: 5px;">
                    <h3 style="margin: 0 0 10px 0; color: #0066cc;">📋 Page Summary</h3>
                    <p style="margin: 0; color: #333;">
                        Total Pages: <strong><?php echo count($admin_pages); ?></strong> | 
                        Menu Pages: <strong><?php echo count(array_filter($admin_pages, function($p) { return $p['type'] === 'menu'; })); ?></strong> | 
                        Submenu Pages: <strong><?php echo count(array_filter($admin_pages, function($p) { return $p['type'] === 'submenu'; })); ?></strong>
                    </p>
                </div>
            </div>
        </div>
        
        <style>
        .kenli-mar-table tbody tr:hover {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .kenli-mar-table a:hover {
            text-decoration: underline !important;
        }
        </style>
        <?php
    }
    
    /**
     * Get all admin pages from this plugin
     */
    private function get_all_admin_pages() {
        $pages = array();
        
        // Define all the submenu pages from this plugin
        $submenu_pages = array(
            array('title' => 'Cockpit - rup_kenli_mar', 'slug' => 'rup_kenli_mar'),
            array('title' => 'KenliSidebarLinks', 'slug' => 'snefuru-kenli-sidebar-links'),
            array('title' => 'Dashboard', 'slug' => 'snefuru'),
            array('title' => 'Settings', 'slug' => 'snefuru-settings'),
            array('title' => 'Logs', 'slug' => 'snefuru-logs'),
            array('title' => 'Dublish Logs', 'slug' => 'snefuru-dublish-logs'),
            array('title' => 'screen 4 - manage', 'slug' => 'snefuru-screen4-manage'),
            array('title' => 'Bespoke CSS Editor', 'slug' => 'snefuruplin-bespoke-css-1'),
            array('title' => 'rup_locations_mar', 'slug' => 'rup_locations_mar'),
            array('title' => 'rup_services_mar', 'slug' => 'rup_services_mar'),
            array('title' => 'rup_driggs_mar', 'slug' => 'rup_driggs_mar'),
            array('title' => 'rup_service_tags_mar', 'slug' => 'rup_service_tags_mar'),
            array('title' => 'rup_location_tags_mar', 'slug' => 'rup_location_tags_mar'),
            array('title' => 'rup_kpages_mar', 'slug' => 'rup_kpages_mar'),
            array('title' => 'rup_duplicate_mar', 'slug' => 'rup_duplicate_mar'),
            array('title' => 'rup_pantheon_mar', 'slug' => 'rup_pantheon_mar'),
            array('title' => 'rup_horse_class_page', 'slug' => 'rup_horse_class_page'),
            array('title' => 'document_outlook_aug9', 'slug' => 'document_outlook_aug9'),
            array('title' => 'dynamic_images_man', 'slug' => 'dynamic_images_man'),
            array('title' => 'Sitespren Export', 'slug' => 'rup_sitespren_export'),
            array('title' => 'beamraymar', 'slug' => 'beamraymar'),
        );
        
        // Add the separate menu pages
        $menu_pages = array(
            array('title' => 'Orbit Mar', 'slug' => 'rup_orbit_mar'),
        );
        
        // Process submenu pages
        foreach ($submenu_pages as $page) {
            $pages[] = array(
                'title' => $page['title'],
                'slug' => $page['slug'],
                'url' => admin_url('admin.php?page=' . $page['slug']),
                'type' => 'submenu'
            );
        }
        
        // Process menu pages
        foreach ($menu_pages as $page) {
            $pages[] = array(
                'title' => $page['title'],
                'slug' => $page['slug'],
                'url' => admin_url('admin.php?page=' . $page['slug']),
                'type' => 'menu'
            );
        }
        
        return $pages;
    }
    
    /**
     * Beamraymar admin page - WordPress Posts & Pages Manager
     */
    public function beamraymar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        // Handle AJAX requests
        if (isset($_POST['action']) && in_array($_POST['action'], ['create_new_post', 'update_post_field', 'create', 'edit'])) {
            $this->handle_beamraymar_ajax();
            return;
        }
        
        // Get posts and pages data
        $posts_pages = $this->get_posts_and_pages_data();
        
        ?>
        <div class="wrap beamraymar-wrapper">
            <style>
                /* Beamraymar Custom Styles - Mimicking FileJar Design */
                .beamraymar-wrapper {
                    background: white;
                    padding: 0;
                    margin: 0 0 0 -20px;
                }
                
                .beamraymar-top-controls {
                    background-color: white;
                    border-bottom: 1px solid #ddd;
                    padding: 12px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 16px;
                }
                
                .beamraymar-controls-left {
                    display: flex;
                    align-items: center;
                    gap: 24px;
                }
                
                .beamray_banner1 {
                    background: black;
                    color: white;
                    font-size: 18px;
                    font-weight: bold;
                    padding: 8px 12px;
                    border: 1px solid gray;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    white-space: nowrap;
                }
                
                .beamray-logo {
                    width: 20px;
                    height: 20px;
                    display: inline-block;
                }
                
                .beamraymar-controls-right {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                
                .beamraymar-info-text {
                    font-size: 14px;
                    color: #666;
                }
                
                .beamraymar-pagination-controls {
                    display: flex;
                    align-items: center;
                }
                
                .beamraymar-pagination-bar {
                    display: flex;
                }
                
                .beamraymar-pagination-btn {
                    padding: 10px 12px;
                    font-size: 14px;
                    border: 1px solid #ddd;
                    background: white;
                    cursor: pointer;
                    margin-right: -1px;
                    text-decoration: none;
                    color: #333;
                    transition: background-color 0.2s;
                }
                
                .beamraymar-pagination-btn:hover {
                    background-color: #f5f5f5;
                }
                
                .beamraymar-pagination-btn.active {
                    background-color: #0073aa;
                    color: white;
                }
                
                .beamraymar-pagination-btn:first-child {
                    border-top-left-radius: 4px;
                    border-bottom-left-radius: 4px;
                }
                
                .beamraymar-pagination-btn:last-child {
                    border-top-right-radius: 4px;
                    border-bottom-right-radius: 4px;
                }
                
                .beamraymar-pagination-divider {
                    width: 1px;
                    height: 20px;
                    background: #ddd;
                    margin: 0 12px;
                }
                
                .beamraymar-search-container {
                    position: relative;
                }
                
                .beamraymar-search-input {
                    width: 320px;
                    padding: 8px 40px 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                }
                
                .beamraymar-clear-btn {
                    position: absolute;
                    right: 6px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: #ffd700;
                    color: black;
                    border: none;
                    padding: 4px 8px;
                    border-radius: 2px;
                    font-size: 12px;
                    font-weight: bold;
                    cursor: pointer;
                }
                
                .beamraymar-clear-btn:hover {
                    background: #ffed4e;
                }
                
                .beamraymar-create-buttons {
                    display: flex;
                    gap: 8px;
                }
                
                .beamraymar-create-btn {
                    padding: 8px 16px;
                    font-size: 14px;
                    font-weight: 600;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    text-decoration: none;
                    transition: all 0.2s;
                }
                
                .beamraymar-create-inline-post {
                    background: #16a085;
                    color: white;
                }
                
                .beamraymar-create-inline-post:hover {
                    background: #138d75;
                }
                
                .beamraymar-create-inline-page {
                    background: #2980b9;
                    color: white;
                }
                
                .beamraymar-create-inline-page:hover {
                    background: #2471a3;
                }
                
                .beamraymar-create-popup {
                    background: #8e44ad;
                    color: white;
                }
                
                .beamraymar-create-popup:hover {
                    background: #7d3c98;
                }
                
                .beamraymar-nubra-kite {
                    background: #f39c12;
                    color: black;
                    padding: 6px 12px;
                    border: 1px solid black;
                    border-radius: 3px;
                    font-size: 12px;
                    font-weight: bold;
                }
                
                .beamraymar-table-container {
                    background: white;
                    overflow: hidden;
                }
                
                .beamraymar-table-scroll {
                    overflow-x: auto;
                }
                
                .beamraymar-table {
                    width: 100%;
                    border-collapse: collapse;
                    border: 1px solid #ddd;
                    min-width: 1600px;
                }
                
                .beamraymar-table thead {
                    background: #f8f9fa;
                }
                
                .beamraymar-table th,
                .beamraymar-table td {
                    border: 1px solid #ddd;
                    padding: 8px 12px;
                    text-align: left;
                    font-size: 14px;
                }
                
                .beamraymar-table th {
                    font-weight: bold;
                    font-size: 12px;
                    text-transform: lowercase;
                    cursor: pointer;
                    position: relative;
                }
                
                .beamraymar-table th:hover {
                    background: #e9ecef;
                }
                
                .beamraymar-table tbody tr:hover {
                    background: #f8f9fa;
                }
                
                .beamraymar-checkbox-cell {
                    width: 40px;
                    text-align: center;
                    cursor: pointer;
                }
                
                .beamraymar-checkbox {
                    width: 20px;
                    height: 20px;
                    cursor: pointer;
                }
                
                .beamraymar-editable-cell {
                    cursor: pointer;
                    min-height: 20px;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                }
                
                .beamraymar-editable-cell:hover {
                    background: #f0f8ff;
                    outline: 1px solid #cce7ff;
                }
                
                .beamraymar-editing-input {
                    width: 100%;
                    padding: 4px 6px;
                    border: 2px solid #0073aa;
                    border-radius: 3px;
                    font-size: 14px;
                    background: white;
                }
                
                .beamraymar-editing-textarea {
                    width: 100%;
                    padding: 4px 6px;
                    border: 2px solid #0073aa;
                    border-radius: 3px;
                    font-size: 14px;
                    background: white;
                    resize: none;
                    min-height: 60px;
                }
                
                .beamraymar-toggle-switch {
                    width: 48px;
                    height: 24px;
                    border-radius: 12px;
                    cursor: pointer;
                    transition: all 0.3s;
                    position: relative;
                    display: inline-block;
                }
                
                .beamraymar-toggle-switch.on {
                    background: #16a085;
                }
                
                .beamraymar-toggle-switch.off {
                    background: #bdc3c7;
                }
                
                .beamraymar-toggle-handle {
                    width: 20px;
                    height: 20px;
                    background: white;
                    border-radius: 50%;
                    position: absolute;
                    top: 2px;
                    transition: transform 0.3s;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
                }
                
                .beamraymar-toggle-switch.on .beamraymar-toggle-handle {
                    transform: translateX(24px);
                }
                
                .beamraymar-toggle-switch.off .beamraymar-toggle-handle {
                    transform: translateX(2px);
                }
                
                .beamraymar-sort-indicator {
                    margin-left: 8px;
                    color: #666;
                }
                
                .beamraymar-loading {
                    text-align: center;
                    padding: 40px;
                    font-size: 16px;
                    color: #666;
                }
                
                /* Bottom controls */
                .beamraymar-bottom-controls {
                    background-color: white;
                    border-top: 1px solid #ddd;
                    padding: 12px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                /* Modal styles */
                .beamraymar-modal {
                    display: none;
                    position: fixed;
                    z-index: 999999;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                }
                
                .beamraymar-modal.active {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .beamraymar-modal-content {
                    background: white;
                    padding: 30px;
                    border-radius: 6px;
                    max-width: 800px;
                    width: 90%;
                    max-height: 80vh;
                    overflow-y: auto;
                }
                
                .beamraymar-modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 15px;
                }
                
                .beamraymar-modal-title {
                    font-size: 20px;
                    font-weight: bold;
                    margin: 0;
                }
                
                .beamraymar-modal-close {
                    font-size: 24px;
                    background: none;
                    border: none;
                    cursor: pointer;
                    color: #666;
                }
                
                .beamraymar-modal-close:hover {
                    color: #333;
                }
                
                .beamraymar-form-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                
                .beamraymar-form-field {
                    display: flex;
                    flex-direction: column;
                }
                
                .beamraymar-form-field.full-width {
                    grid-column: 1 / -1;
                }
                
                .beamraymar-form-label {
                    font-weight: 600;
                    margin-bottom: 6px;
                    color: #333;
                }
                
                .beamraymar-form-input,
                .beamraymar-form-textarea,
                .beamraymar-form-select {
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                }
                
                .beamraymar-form-textarea {
                    min-height: 100px;
                    resize: vertical;
                }
                
                .beamraymar-modal-actions {
                    display: flex;
                    justify-content: flex-end;
                    gap: 12px;
                    margin-top: 24px;
                }
                
                .beamraymar-modal-btn {
                    padding: 10px 20px;
                    border: none;
                    border-radius: 4px;
                    font-size: 14px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                }
                
                .beamraymar-modal-btn.primary {
                    background: #0073aa;
                    color: white;
                }
                
                .beamraymar-modal-btn.primary:hover {
                    background: #005a87;
                }
                
                .beamraymar-modal-btn.secondary {
                    background: #f1f1f1;
                    color: #333;
                    border: 1px solid #ddd;
                }
                
                .beamraymar-modal-btn.secondary:hover {
                    background: #e0e0e0;
                }
                
                .beamraymar-table td .tcell_inner_wrapper_div {
                    height: 38px;
                }
                
                /* Column pagination styles */
                .beamraymar-column-pagination-controls {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 8px;
                    margin-left: 15px;
                }
                
                .beamraymar-column-pagination-bar {
                    display: flex;
                }
                
                .beamraymar-column-pagination-btn {
                    padding: 10px 12px;
                    font-size: 14px;
                    border: 1px solid #ddd;
                    background: white;
                    cursor: pointer;
                    margin-right: -1px;
                    text-decoration: none;
                    color: #333;
                    transition: background-color 0.2s;
                }
                
                .beamraymar-column-pagination-btn:hover {
                    background-color: #f5f5f5;
                }
                
                .beamraymar-column-pagination-btn.active {
                    background-color: #ffd700;
                    color: black;
                }
                
                .beamraymar-column-pagination-btn:first-child {
                    border-top-left-radius: 4px;
                    border-bottom-left-radius: 4px;
                }
                
                .beamraymar-column-pagination-btn:last-child {
                    border-top-right-radius: 4px;
                    border-bottom-right-radius: 4px;
                }
                
                /* Filter button bars */
                .beamraymar-filter-controls {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 8px;
                    margin-left: 15px;
                }
                
                .beamraymar-filter-bar {
                    display: flex;
                }
                
                .beamraymar-filter-btn {
                    padding: 10px 12px;
                    font-size: 14px;
                    border: 1px solid #ddd;
                    background: white;
                    cursor: pointer;
                    margin-right: -1px;
                    text-decoration: none;
                    color: #333;
                    transition: background-color 0.2s;
                }
                
                .beamraymar-filter-btn:hover {
                    background-color: #f5f5f5;
                }
                
                .beamraymar-filter-btn.active {
                    background-color: #0073aa;
                    color: white;
                }
                
                .beamraymar-filter-btn:first-child {
                    border-top-left-radius: 4px;
                    border-bottom-left-radius: 4px;
                }
                
                .beamraymar-filter-btn:last-child {
                    border-top-right-radius: 4px;
                    border-bottom-right-radius: 4px;
                }
                
                /* ED button for post_content */
                .beamraymar-content-edit-btn {
                    width: 20px;
                    height: 20px;
                    background: #0073aa;
                    color: white;
                    border: none;
                    cursor: pointer;
                    font-size: 10px;
                    font-weight: bold;
                    float: right;
                    margin-left: 8px;
                }
                
                .beamraymar-content-edit-btn:hover {
                    background: #005a87;
                }
                
                /* Post content editor popup */
                .beamraymar-content-editor-modal {
                    display: none;
                    position: fixed;
                    z-index: 999999;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                }
                
                .beamraymar-content-editor-modal.active {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .beamraymar-content-editor-content {
                    background: white;
                    padding: 30px;
                    border-radius: 6px;
                    width: 90%;
                    height: 85%;
                    display: flex;
                    flex-direction: column;
                }
                
                .beamraymar-content-editor-header {
                    font-size: 16px;
                    font-weight: bold;
                    margin-bottom: 15px;
                }
                
                .beamraymar-content-editor-textarea {
                    width: 100%;
                    flex: 1;
                    padding: 10px;
                    border: 1px solid #ddd;
                    font-family: monospace;
                    font-size: 14px;
                    resize: none;
                }
                
                .beamraymar-content-editor-actions {
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                    margin-top: 15px;
                }
                
                .beamraymar-content-editor-btn {
                    padding: 10px 20px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-weight: 600;
                }
                
                .beamraymar-content-editor-btn.save {
                    background: #0073aa;
                    color: white;
                }
                
                .beamraymar-content-editor-btn.cancel {
                    background: #f1f1f1;
                    color: #333;
                }
                
                /* Tool button styling */
                .beamraymar-tool-btn {
                    width: 20px;
                    height: 20px;
                    background: #0073aa;
                    color: white;
                    border: none;
                    cursor: pointer;
                    font-size: 10px;
                    font-weight: bold;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    text-decoration: none;
                    border-radius: 2px;
                }
                
                .beamraymar-tool-btn:hover {
                    background: #005a87;
                    color: white;
                    text-decoration: none;
                }
                
                /* Pendulum button styling */
                .beamraymar-pendulum-btn {
                    width: 20px;
                    height: 20px;
                    background: #0073aa;
                    color: white;
                    border: none;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 10px;
                    cursor: pointer;
                    margin-right: 2px;
                    border-radius: 2px;
                }
                
                .beamraymar-pendulum-btn:hover {
                    background: #005a87;
                    color: white;
                    text-decoration: none;
                }
                
                /* Elementor button styling */
                .beamraymar-elementor-btn {
                    width: 20px;
                    height: 20px;
                    background: #0073aa;
                    color: white;
                    border: none;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 10px;
                    cursor: pointer;
                    margin-left: 2px;
                    border-radius: 2px;
                }
                
                .beamraymar-elementor-btn:hover {
                    background: #005a87;
                    color: white;
                    text-decoration: none;
                }
                
                .beamraymar-elementor-btn.disabled {
                    background: #ccc;
                    color: #999;
                    cursor: not-allowed;
                    pointer-events: none;
                }
            </style>

            <!-- Top Controls -->
            <div class="beamraymar-top-controls">
                <div class="beamraymar-controls-left">
                    <div class="beamray_banner1">
                        <svg class="beamray-logo" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L4 7v10l8 5 8-5V7l-8-5zm0 2.18L18.18 8 12 11.82 5.82 8 12 4.18zM6 9.42l5 2.5v8.16l-5-2.5V9.42zm12 0v8.16l-5 2.5v-8.16l5-2.5z"/>
                            <circle cx="12" cy="8" r="1.5" fill="white"/>
                            <line x1="12" y1="8" x2="12" y2="16" stroke="white" stroke-width="1" opacity="0.6"/>
                            <line x1="8" y1="10" x2="16" y2="14" stroke="white" stroke-width="0.5" opacity="0.4"/>
                            <line x1="16" y1="10" x2="8" y2="14" stroke="white" stroke-width="0.5" opacity="0.4"/>
                        </svg>
                        BeamRay Table
                    </div>
                    <div class="beamraymar-info-text">
                        <span id="beamraymar-results-info">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                    </div>
                    
                    <div class="beamraymar-pagination-controls">
                        <div class="beamraymar-pagination-bar" id="beamraymar-per-page-bar">
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="10">10</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="20">20</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="50">50</a>
                            <a href="#" class="beamraymar-pagination-btn active" data-per-page="100">100</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="200">200</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="500">500</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="all">All</a>
                        </div>
                        
                        <div class="beamraymar-pagination-divider"></div>
                        
                        <div class="beamraymar-pagination-bar" id="beamraymar-page-bar">
                            <a href="#" class="beamraymar-pagination-btn" data-page="first">First</a>
                            <a href="#" class="beamraymar-pagination-btn" data-page="prev">Prev</a>
                            <a href="#" class="beamraymar-pagination-btn active" data-page="1">1</a>
                            <a href="#" class="beamraymar-pagination-btn" data-page="next">Next</a>
                            <a href="#" class="beamraymar-pagination-btn" data-page="last">Last</a>
                        </div>
                    </div>
                    
                    <div class="beamraymar-search-container">
                        <input type="text" id="beamraymar-search" class="beamraymar-search-input" placeholder="Search all fields...">
                        <button class="beamraymar-clear-btn" id="beamraymar-clear">CL</button>
                    </div>
                </div>
                
                <div class="beamraymar-controls-right">
                    <div style="display: flex; flex-direction: column; align-items: flex-end;">
                        <?php
                        global $wpdb;
                        $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren WHERE wppma_id = 1");
                        ?>
                        <div style="font-size: 16px; font-weight: bold; text-transform: lowercase; margin-bottom: 10px;">
                            <span style="font-weight: bold;"><?php echo esc_html($wpdb->prefix); ?></span>zen_sitespren.sitespren_base: <?php echo esc_html($sitespren_base ?: ''); ?>
                        </div>
                        <div class="beamraymar-create-buttons">
                            <button class="beamraymar-create-btn beamraymar-create-inline-post" id="create-post-inline">Create New (Inline)</button>
                            <button class="beamraymar-create-btn beamraymar-create-inline-page" id="create-page-inline">Create New (Inline) WP Page</button>
                            <button class="beamraymar-create-btn beamraymar-create-popup" id="create-popup">Create New (Popup)</button>
                        </div>
                    </div>
                    <div class="beamraymar-nubra-kite">nubra-tableface-kite</div>
                    
                    <!-- Column Pagination Controls -->
                    <div class="beamraymar-column-pagination-controls">
                        <div class="beamraymar-column-pagination-bar" id="beamraymar-column-group-bar">
                            <a href="#" class="beamraymar-column-pagination-btn active" data-column-group="1">Columns 1-7</a>
                            <a href="#" class="beamraymar-column-pagination-btn" data-column-group="2">Columns 8-14</a>
                            <a href="#" class="beamraymar-column-pagination-btn" data-column-group="3">Column 15</a>
                        </div>
                        
                        <div class="beamraymar-column-pagination-bar" id="beamraymar-column-nav-bar">
                            <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="first">First</a>
                            <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="prev">Prev</a>
                            <a href="#" class="beamraymar-column-pagination-btn active" data-column-nav="1">1</a>
                            <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="next">Next</a>
                            <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="last">Last</a>
                        </div>
                    </div>
                    
                    <!-- Filter Controls -->
                    <div class="beamraymar-filter-controls">
                        <div class="beamraymar-filter-bar" id="beamraymar-post-type-filter">
                            <a href="#" class="beamraymar-filter-btn" data-filter-type="post_type" data-filter-value="page">Page</a>
                            <a href="#" class="beamraymar-filter-btn" data-filter-type="post_type" data-filter-value="post">Post</a>
                        </div>
                        
                        <div class="beamraymar-filter-bar" id="beamraymar-post-status-filter">
                            <a href="#" class="beamraymar-filter-btn" data-filter-type="post_status" data-filter-value="publish">Published</a>
                            <a href="#" class="beamraymar-filter-btn" data-filter-type="post_status" data-filter-value="draft">Draft</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="beamraymar-table-container">
                <div class="beamraymar-table-scroll">
                    <table class="beamraymar-table" id="beamraymar-table">
                        <thead>
                            <tr>
                                <th class="fact_checkbox"><div class="tcell_inner_wrapper_div"></div></th>
                                <th class="fact_tool_buttons"><div class="tcell_inner_wrapper_div"><strong>misc-uicol-type</strong></div></th>
                                <th class="fact_wp_posts_id"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_post_title"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_post_content"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_postmeta_meta_key_elementor_data"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>postmeta</strong></div></th>
                                <th class="fact_wp_posts_post_type"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_post_status"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_post_name"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_post_date"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_post_modified"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_post_author"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_post_parent"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_menu_order"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_comment_status"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_wp_posts_ping_status"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                                <th class="fact_zen_orbitposts_rel_wp_post_id"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                                <th class="fact_zen_orbitposts_orbitpost_id"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                                <th class="fact_zen_orbitposts_redshift_datum"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                                <th class="fact_zen_orbitposts_rover_datum"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                                <th class="fact_zen_orbitposts_hudson_imgplanbatch_id"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                                <th class="fact_zen_orbitposts_created_at"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                                <th class="fact_zen_orbitposts_updated_at"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            </tr>
                            <tr class="beamraymar-main-header-row">
                                <th class="beamraymar-checkbox-cell fact_checkbox">
                                    <div class="tcell_inner_wrapper_div"><input type="checkbox" class="beamraymar-checkbox" id="select-all"></div>
                                </th>
                                <th class="fact_tool_buttons"><div class="tcell_inner_wrapper_div">tool_buttons</div></th>
                                <th data-field="ID" data-type="integer" class="fact_wp_posts_id"><div class="tcell_inner_wrapper_div">id</div></th>
                                <th data-field="post_title" data-type="text" class="fact_wp_posts_post_title"><div class="tcell_inner_wrapper_div">post_title</div></th>
                                <th data-field="post_content" data-type="longtext" class="fact_wp_posts_post_content"><div class="tcell_inner_wrapper_div">post_content</div></th>
                                <th data-field="_elementor_data" data-type="text" class="fact_wp_postmeta_meta_key_elementor_data"><div class="tcell_inner_wrapper_div"><strong>meta_key:_elementor_data</strong></div></th>
                                <th data-field="post_type" data-type="text" class="fact_wp_posts_post_type"><div class="tcell_inner_wrapper_div">post_type</div></th>
                                <th data-field="post_status" data-type="text" class="fact_wp_posts_post_status"><div class="tcell_inner_wrapper_div">post_status</div></th>
                                <th data-field="post_name" data-type="text" class="fact_wp_posts_post_name"><div class="tcell_inner_wrapper_div">post_name</div></th>
                                <th data-field="post_date" data-type="datetime" class="fact_wp_posts_post_date"><div class="tcell_inner_wrapper_div">post_date</div></th>
                                <th data-field="post_modified" data-type="datetime" class="fact_wp_posts_post_modified"><div class="tcell_inner_wrapper_div">post_modified</div></th>
                                <th data-field="post_author" data-type="integer" class="fact_wp_posts_post_author"><div class="tcell_inner_wrapper_div">post_author</div></th>
                                <th data-field="post_parent" data-type="integer" class="fact_wp_posts_post_parent"><div class="tcell_inner_wrapper_div">post_parent</div></th>
                                <th data-field="menu_order" data-type="integer" class="fact_wp_posts_menu_order"><div class="tcell_inner_wrapper_div">menu_order</div></th>
                                <th data-field="comment_status" data-type="text" class="fact_wp_posts_comment_status"><div class="tcell_inner_wrapper_div">comment_status</div></th>
                                <th data-field="ping_status" data-type="text" class="fact_wp_posts_ping_status"><div class="tcell_inner_wrapper_div">ping_status</div></th>
                                <th data-field="rel_wp_post_id" data-type="integer" class="fact_zen_orbitposts_rel_wp_post_id"><div class="tcell_inner_wrapper_div">rel_wp_post_id</div></th>
                                <th data-field="orbitpost_id" data-type="integer" class="fact_zen_orbitposts_orbitpost_id"><div class="tcell_inner_wrapper_div">orbitpost_id</div></th>
                                <th data-field="redshift_datum" data-type="text" class="fact_zen_orbitposts_redshift_datum"><div class="tcell_inner_wrapper_div">redshift_datum</div></th>
                                <th data-field="rover_datum" data-type="text" class="fact_zen_orbitposts_rover_datum"><div class="tcell_inner_wrapper_div">rover_datum</div></th>
                                <th data-field="hudson_imgplanbatch_id" data-type="integer" class="fact_zen_orbitposts_hudson_imgplanbatch_id"><div class="tcell_inner_wrapper_div">hudson_imgplanbatch_id</div></th>
                                <th data-field="created_at" data-type="datetime" class="fact_zen_orbitposts_created_at"><div class="tcell_inner_wrapper_div">created_at</div></th>
                                <th data-field="updated_at" data-type="datetime" class="fact_zen_orbitposts_updated_at"><div class="tcell_inner_wrapper_div">updated_at</div></th>
                            </tr>
                        </thead>
                        <tbody id="beamraymar-tbody">
                            <?php $this->render_beamraymar_table_rows($posts_pages); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Controls -->
            <div class="beamraymar-bottom-controls">
                <div class="beamraymar-controls-left">
                    <div class="beamraymar-info-text">
                        <span id="beamraymar-results-info-bottom">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                    </div>
                    
                    <div class="beamraymar-pagination-controls">
                        <div class="beamraymar-pagination-bar" id="beamraymar-per-page-bar-bottom">
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="10">10</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="20">20</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="50">50</a>
                            <a href="#" class="beamraymar-pagination-btn active" data-per-page="100">100</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="200">200</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="500">500</a>
                            <a href="#" class="beamraymar-pagination-btn" data-per-page="all">All</a>
                        </div>
                        
                        <div class="beamraymar-pagination-divider"></div>
                        
                        <div class="beamraymar-pagination-bar" id="beamraymar-page-bar-bottom">
                            <a href="#" class="beamraymar-pagination-btn" data-page="first">First</a>
                            <a href="#" class="beamraymar-pagination-btn" data-page="prev">Prev</a>
                            <a href="#" class="beamraymar-pagination-btn active" data-page="1">1</a>
                            <a href="#" class="beamraymar-pagination-btn" data-page="next">Next</a>
                            <a href="#" class="beamraymar-pagination-btn" data-page="last">Last</a>
                        </div>
                    </div>
                    
                    <div class="beamraymar-search-container">
                        <input type="text" id="beamraymar-search-bottom" class="beamraymar-search-input" placeholder="Search all fields...">
                        <button class="beamraymar-clear-btn" id="beamraymar-clear-bottom">CL</button>
                    </div>
                </div>
                <div></div>
            </div>

            <!-- Create/Edit Modal -->
            <div id="beamraymar-modal" class="beamraymar-modal">
                <div class="beamraymar-modal-content">
                    <div class="beamraymar-modal-header">
                        <h2 class="beamraymar-modal-title" id="beamraymar-modal-title">Create New Post/Page</h2>
                        <button class="beamraymar-modal-close" id="beamraymar-modal-close">&times;</button>
                    </div>
                    
                    <form id="beamraymar-modal-form">
                        <input type="hidden" id="modal-post-id" name="post_id">
                        <input type="hidden" id="modal-action" name="action" value="create">
                        
                        <div class="beamraymar-form-grid">
                            <div class="beamraymar-form-field">
                                <label class="beamraymar-form-label">Post Type</label>
                                <select id="modal-post-type" name="post_type" class="beamraymar-form-select">
                                    <option value="post">Post</option>
                                    <option value="page">Page</option>
                                </select>
                            </div>
                            
                            <div class="beamraymar-form-field">
                                <label class="beamraymar-form-label">Status</label>
                                <select id="modal-post-status" name="post_status" class="beamraymar-form-select">
                                    <option value="draft">Draft</option>
                                    <option value="publish">Published</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>
                            
                            <div class="beamraymar-form-field full-width">
                                <label class="beamraymar-form-label">Title</label>
                                <input type="text" id="modal-post-title" name="post_title" class="beamraymar-form-input">
                            </div>
                            
                            <div class="beamraymar-form-field full-width">
                                <label class="beamraymar-form-label">Content</label>
                                <textarea id="modal-post-content" name="post_content" class="beamraymar-form-textarea"></textarea>
                            </div>
                            
                            <div class="beamraymar-form-field">
                                <label class="beamraymar-form-label">Slug</label>
                                <input type="text" id="modal-post-name" name="post_name" class="beamraymar-form-input">
                            </div>
                            
                            <div class="beamraymar-form-field">
                                <label class="beamraymar-form-label">Parent ID</label>
                                <input type="number" id="modal-post-parent" name="post_parent" class="beamraymar-form-input" value="0">
                            </div>
                        </div>
                        
                        <div class="beamraymar-modal-actions">
                            <button type="button" class="beamraymar-modal-btn secondary" id="beamraymar-modal-cancel">Cancel</button>
                            <button type="submit" class="beamraymar-modal-btn primary" id="beamraymar-modal-save">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Post Content Editor Modal -->
            <div id="beamraymar-content-editor-modal" class="beamraymar-content-editor-modal">
                <div class="beamraymar-content-editor-content">
                    <div class="beamraymar-content-editor-header">post_content</div>
                    <textarea id="beamraymar-content-editor-textarea" class="beamraymar-content-editor-textarea"></textarea>
                    <div class="beamraymar-content-editor-actions">
                        <button type="button" class="beamraymar-content-editor-btn cancel" id="beamraymar-content-editor-cancel">Cancel</button>
                        <button type="button" class="beamraymar-content-editor-btn save" id="beamraymar-content-editor-save">Save</button>
                    </div>
                </div>
            </div>

            <!-- Elementor Data Editor Modal -->
            <div id="beamraymar-elementor-editor-modal" class="beamraymar-content-editor-modal">
                <div class="beamraymar-content-editor-content">
                    <div class="beamraymar-content-editor-header">_elementor_data</div>
                    <textarea id="beamraymar-elementor-editor-textarea" class="beamraymar-content-editor-textarea"></textarea>
                    <div class="beamraymar-content-editor-actions">
                        <button type="button" class="beamraymar-content-editor-btn cancel" id="beamraymar-elementor-editor-cancel">Cancel</button>
                        <button type="button" class="beamraymar-content-editor-btn save" id="beamraymar-elementor-editor-save">Save</button>
                    </div>
                </div>
            </div>

            <script>
                // Beamraymar JavaScript functionality
                (function($) {
                    'use strict';
                    
                    let currentPage = 1;
                    let itemsPerPage = 100;
                    let currentSearch = '';
                    let allData = <?php echo json_encode($posts_pages); ?>;
                    let filteredData = [...allData];
                    let selectedRows = new Set();
                    let editingCell = null;
                    let currentContentEditPostId = null;
                    let currentElementorEditPostId = null;
                    
                    // Initialize page
                    $(document).ready(function() {
                        initializeEventHandlers();
                        updateTable();
                        updateColumnVisibility(); // Initialize column pagination
                    });
                    
                    function initializeEventHandlers() {
                        // Pagination controls
                        $('.beamraymar-pagination-btn').on('click', function(e) {
                            e.preventDefault();
                            const $this = $(this);
                            
                            if ($this.data('per-page')) {
                                // Per page selection
                                const perPage = $this.data('per-page');
                                itemsPerPage = perPage === 'all' ? filteredData.length : parseInt(perPage);
                                currentPage = 1;
                                
                                $this.siblings('.beamraymar-pagination-btn').removeClass('active');
                                $this.addClass('active');
                                
                                // Update both top and bottom bars
                                $('[data-per-page="' + perPage + '"]').addClass('active').siblings().removeClass('active');
                            } else if ($this.data('page')) {
                                // Page navigation
                                const pageAction = $this.data('page');
                                const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                                
                                switch(pageAction) {
                                    case 'first':
                                        currentPage = 1;
                                        break;
                                    case 'prev':
                                        if (currentPage > 1) currentPage--;
                                        break;
                                    case 'next':
                                        if (currentPage < totalPages) currentPage++;
                                        break;
                                    case 'last':
                                        currentPage = totalPages;
                                        break;
                                    default:
                                        if (!isNaN(pageAction)) {
                                            currentPage = parseInt(pageAction);
                                        }
                                }
                            }
                            
                            updateTable();
                        });
                        
                        // Column pagination controls
                        $('.beamraymar-column-pagination-btn').on('click', function(e) {
                            e.preventDefault();
                            const $this = $(this);
                            
                            if ($this.data('column-group')) {
                                // Column group selection
                                const columnGroup = parseInt($this.data('column-group'));
                                setActiveColumnGroup(columnGroup);
                                
                                $this.siblings('.beamraymar-column-pagination-btn').removeClass('active');
                                $this.addClass('active');
                                
                            } else if ($this.data('column-nav')) {
                                // Column navigation
                                const navAction = $this.data('column-nav');
                                handleColumnNavigation(navAction);
                                
                                // Update active state for numbered buttons only
                                if (!isNaN(navAction)) {
                                    $this.siblings('[data-column-nav]').removeClass('active');
                                    $this.addClass('active');
                                }
                            }
                        });
                        
                        // Filter button controls
                        $('.beamraymar-filter-btn').on('click', function(e) {
                            e.preventDefault();
                            const $this = $(this);
                            
                            // Toggle active state
                            $this.toggleClass('active');
                            
                            // Reset to page 1 and apply filters
                            currentPage = 1;
                            filterData();
                            updateTable();
                        });
                        
                        // Search functionality
                        $('#beamraymar-search, #beamraymar-search-bottom').on('input', function() {
                            currentSearch = $(this).val().toLowerCase();
                            $('#beamraymar-search, #beamraymar-search-bottom').val(currentSearch);
                            currentPage = 1;
                            filterData();
                            updateTable();
                        });
                        
                        // Clear search
                        $('#beamraymar-clear, #beamraymar-clear-bottom').on('click', function() {
                            $('#beamraymar-search, #beamraymar-search-bottom').val('');
                            currentSearch = '';
                            filterData();
                            updateTable();
                        });
                        
                        // Create buttons
                        $('#create-post-inline').on('click', function() {
                            createNewInline('post');
                        });
                        
                        $('#create-page-inline').on('click', function() {
                            createNewInline('page');
                        });
                        
                        $('#create-popup').on('click', function() {
                            openModal('create');
                        });
                        
                        // Select all checkbox
                        $('#select-all').on('change', function() {
                            const isChecked = $(this).is(':checked');
                            $('.row-checkbox').prop('checked', isChecked);
                            
                            if (isChecked) {
                                $('.row-checkbox').each(function() {
                                    selectedRows.add(parseInt($(this).val()));
                                });
                            } else {
                                selectedRows.clear();
                            }
                        });
                        
                        // Modal handlers
                        $('#beamraymar-modal-close, #beamraymar-modal-cancel').on('click', function() {
                            closeModal();
                        });
                        
                        $('#beamraymar-modal').on('click', function(e) {
                            if (e.target === this) {
                                closeModal();
                            }
                        });
                        
                        $('#beamraymar-modal-form').on('submit', function(e) {
                            e.preventDefault();
                            saveModalData();
                        });
                        
                        // Post content editor handlers
                        $(document).on('click', '.beamraymar-content-edit-btn', function(e) {
                            e.stopPropagation();
                            const postId = $(this).data('post-id');
                            const editorType = $(this).data('editor-type');
                            
                            if (editorType === 'elementor') {
                                openElementorEditor(postId);
                            } else {
                                openContentEditor(postId);
                            }
                        });
                        
                        $('#beamraymar-content-editor-cancel').on('click', function() {
                            closeContentEditor();
                        });
                        
                        $('#beamraymar-content-editor-save').on('click', function() {
                            saveContentEditor();
                        });
                        
                        // Elementor data editor handlers
                        $('#beamraymar-elementor-editor-cancel').on('click', function() {
                            closeElementorEditor();
                        });
                        
                        $('#beamraymar-elementor-editor-save').on('click', function() {
                            saveElementorEditor();
                        });
                        
                        // Table column sorting
                        $('.beamraymar-table th[data-field]').on('click', function() {
                            const field = $(this).data('field');
                            sortData(field);
                            updateTable();
                        });
                    }
                    
                    function filterData() {
                        filteredData = allData.filter(item => {
                            // Apply search filter
                            let matchesSearch = true;
                            if (currentSearch) {
                                matchesSearch = Object.values(item).some(value => {
                                    if (value === null || value === undefined) return false;
                                    return value.toString().toLowerCase().includes(currentSearch);
                                });
                            }
                            
                            // Apply post type filter
                            let matchesPostType = true;
                            const activePostTypes = $('.beamraymar-filter-btn[data-filter-type="post_type"].active');
                            if (activePostTypes.length > 0) {
                                const activeValues = activePostTypes.map(function() {
                                    return $(this).data('filter-value');
                                }).get();
                                matchesPostType = activeValues.includes(item.post_type);
                            }
                            
                            // Apply post status filter
                            let matchesPostStatus = true;
                            const activePostStatuses = $('.beamraymar-filter-btn[data-filter-type="post_status"].active');
                            if (activePostStatuses.length > 0) {
                                const activeValues = activePostStatuses.map(function() {
                                    return $(this).data('filter-value');
                                }).get();
                                matchesPostStatus = activeValues.includes(item.post_status);
                            }
                            
                            return matchesSearch && matchesPostType && matchesPostStatus;
                        });
                    }
                    
                    // Column pagination variables
                    let currentColumnGroup = 1;
                    const columnsPerGroup = 7;
                    
                    function setActiveColumnGroup(groupNumber) {
                        currentColumnGroup = groupNumber;
                        updateColumnVisibility();
                    }
                    
                    function handleColumnNavigation(action) {
                        const totalGroups = Math.ceil(15 / columnsPerGroup); // 15 total columns
                        
                        switch(action) {
                            case 'first':
                                currentColumnGroup = 1;
                                break;
                            case 'prev':
                                if (currentColumnGroup > 1) currentColumnGroup--;
                                break;
                            case 'next':
                                if (currentColumnGroup < totalGroups) currentColumnGroup++;
                                break;
                            case 'last':
                                currentColumnGroup = totalGroups;
                                break;
                            default:
                                if (!isNaN(action)) {
                                    currentColumnGroup = parseInt(action);
                                }
                        }
                        
                        updateColumnVisibility();
                        updateColumnGroupButtons();
                    }
                    
                    function updateColumnVisibility() {
                        const startColumn = (currentColumnGroup - 1) * columnsPerGroup + 1; // +1 for checkbox column
                        const endColumn = startColumn + columnsPerGroup - 1;
                        
                        // Hide all columns except checkbox (index 0)
                        $('.beamraymar-table th, .beamraymar-table td').each(function(index) {
                            const columnIndex = $(this).index();
                            if (columnIndex === 0) {
                                // Always show checkbox column
                                $(this).show();
                            } else if (columnIndex >= startColumn && columnIndex <= endColumn) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    }
                    
                    function updateColumnGroupButtons() {
                        // Update column group bar
                        $('#beamraymar-column-group-bar .beamraymar-column-pagination-btn').removeClass('active');
                        $(`[data-column-group="${currentColumnGroup}"]`).addClass('active');
                        
                        // Update column nav bar active state
                        $('#beamraymar-column-nav-bar .beamraymar-column-pagination-btn').removeClass('active');
                        $(`[data-column-nav="${currentColumnGroup}"]`).addClass('active');
                    }
                    
                    function updateTable() {
                        const startIndex = (currentPage - 1) * itemsPerPage;
                        const endIndex = itemsPerPage === filteredData.length ? filteredData.length : startIndex + itemsPerPage;
                        const pageData = filteredData.slice(startIndex, endIndex);
                        
                        // Update table rows
                        renderTableRows(pageData);
                        
                        // Update info text
                        const infoText = `${startIndex + 1}-${Math.min(endIndex, filteredData.length)} of ${filteredData.length} posts/pages`;
                        $('#beamraymar-results-info, #beamraymar-results-info-bottom').text(infoText);
                        
                        // Update pagination buttons
                        updatePaginationButtons();
                        
                        // Reattach event handlers for new rows
                        attachRowEventHandlers();
                        
                        // Apply column pagination
                        updateColumnVisibility();
                    }
                    
                    function getFrontendUrl(item) {
                        const baseUrl = '<?php echo home_url('/'); ?>';
                        if (item.post_status === 'draft') {
                            return baseUrl + '?p=' + item.ID + '&preview=true';
                        } else {
                            if (item.post_name) {
                                return baseUrl + item.post_name + '/';
                            } else {
                                return baseUrl + '?p=' + item.ID;
                            }
                        }
                    }
                    
                    function getAdminEditUrl(item) {
                        const adminUrl = '<?php echo admin_url('post.php'); ?>';
                        return adminUrl + '?post=' + item.ID + '&action=edit';
                    }
                    
                    function getElementorEditUrl(item) {
                        const adminUrl = '<?php echo admin_url('post.php'); ?>';
                        return adminUrl + '?post=' + item.ID + '&action=elementor';
                    }
                    
                    function isElementorPost(item) {
                        return item._elementor_data && item._elementor_data !== '' && item._elementor_data !== '[]';
                    }
                    
                    function renderTableRows(data) {
                        let html = '';
                        
                        data.forEach(item => {
                            html += `<tr data-id="${item.ID}">
                                <td class="beamraymar-checkbox-cell fact_checkbox">
                                    <div class="tcell_inner_wrapper_div"><input type="checkbox" class="beamraymar-checkbox row-checkbox" value="${item.ID}"></div>
                                </td>
                                <td class="readonly-cell fact_tool_buttons"><div class="tcell_inner_wrapper_div"><a href="${getAdminEditUrl(item)}" target="_blank" class="beamraymar-pendulum-btn">&#9675;&#124;</a><a href="${getFrontendUrl(item)}" target="_blank" class="beamraymar-tool-btn">F</a>${isElementorPost(item) ? `<a href="${getElementorEditUrl(item)}" target="_blank" class="beamraymar-elementor-btn">E</a>` : '<span class="beamraymar-elementor-btn disabled">E</span>'}</div></td>
                                <td class="readonly-cell fact_wp_posts_id"><div class="tcell_inner_wrapper_div">${item.ID}</div></td>
                                <td class="beamraymar-editable-cell fact_wp_posts_post_title" data-field="post_title" data-type="text"><div class="tcell_inner_wrapper_div">${item.post_title || ''}</div></td>
                                <td class="readonly-cell fact_wp_posts_post_content"><div class="tcell_inner_wrapper_div">${truncatePostContent(item.post_content || '')}<button class="beamraymar-content-edit-btn" data-post-id="${item.ID}">ED</button></div></td>
                                <td class="readonly-cell fact_wp_postmeta_meta_key_elementor_data"><div class="tcell_inner_wrapper_div">${formatElementorData(item._elementor_data)}<button class="beamraymar-content-edit-btn" data-post-id="${item.ID}" data-editor-type="elementor">ED</button></div></td>
                                <td class="readonly-cell fact_wp_posts_post_type"><div class="tcell_inner_wrapper_div">${item.post_type}</div></td>
                                <td class="beamraymar-editable-cell fact_wp_posts_post_status" data-field="post_status" data-type="select"><div class="tcell_inner_wrapper_div">${item.post_status}</div></td>
                                <td class="beamraymar-editable-cell fact_wp_posts_post_name" data-field="post_name" data-type="text"><div class="tcell_inner_wrapper_div">${item.post_name || ''}</div></td>
                                <td class="readonly-cell fact_wp_posts_post_date"><div class="tcell_inner_wrapper_div">${formatDate(item.post_date)}</div></td>
                                <td class="readonly-cell fact_wp_posts_post_modified"><div class="tcell_inner_wrapper_div">${formatDate(item.post_modified)}</div></td>
                                <td class="beamraymar-editable-cell fact_wp_posts_post_author" data-field="post_author" data-type="integer"><div class="tcell_inner_wrapper_div">${item.post_author}</div></td>
                                <td class="beamraymar-editable-cell fact_wp_posts_post_parent" data-field="post_parent" data-type="integer"><div class="tcell_inner_wrapper_div">${item.post_parent || '0'}</div></td>
                                <td class="beamraymar-editable-cell fact_wp_posts_menu_order" data-field="menu_order" data-type="integer"><div class="tcell_inner_wrapper_div">${item.menu_order || '0'}</div></td>
                                <td class="beamraymar-editable-cell fact_wp_posts_comment_status" data-field="comment_status" data-type="select"><div class="tcell_inner_wrapper_div">${item.comment_status}</div></td>
                                <td class="beamraymar-editable-cell fact_wp_posts_ping_status" data-field="ping_status" data-type="select"><div class="tcell_inner_wrapper_div">${item.ping_status}</div></td>
                                <td class="readonly-cell fact_zen_orbitposts_rel_wp_post_id"><div class="tcell_inner_wrapper_div">${item.rel_wp_post_id || ''}</div></td>
                                <td class="readonly-cell fact_zen_orbitposts_orbitpost_id"><div class="tcell_inner_wrapper_div">${item.orbitpost_id || ''}</div></td>
                                <td class="readonly-cell fact_zen_orbitposts_redshift_datum"><div class="tcell_inner_wrapper_div">${item.redshift_datum || ''}</div></td>
                                <td class="readonly-cell fact_zen_orbitposts_rover_datum"><div class="tcell_inner_wrapper_div">${item.rover_datum || ''}</div></td>
                                <td class="readonly-cell fact_zen_orbitposts_hudson_imgplanbatch_id"><div class="tcell_inner_wrapper_div">${item.hudson_imgplanbatch_id || ''}</div></td>
                                <td class="readonly-cell fact_zen_orbitposts_created_at"><div class="tcell_inner_wrapper_div">${formatDate(item.created_at)}</div></td>
                                <td class="readonly-cell fact_zen_orbitposts_updated_at"><div class="tcell_inner_wrapper_div">${formatDate(item.updated_at)}</div></td>
                            </tr>`;
                        });
                        
                        $('#beamraymar-tbody').html(html);
                    }
                    
                    function attachRowEventHandlers() {
                        // Row checkbox handling
                        $('.row-checkbox').on('change', function() {
                            const id = parseInt($(this).val());
                            if ($(this).is(':checked')) {
                                selectedRows.add(id);
                            } else {
                                selectedRows.delete(id);
                            }
                        });
                        
                        // Inline editing
                        $('.beamraymar-editable-cell').on('click', function() {
                            if (editingCell) return; // Don't start new edit if already editing
                            
                            const $cell = $(this);
                            const field = $cell.data('field');
                            const type = $cell.data('type');
                            const currentValue = $cell.text().trim();
                            const postId = $cell.closest('tr').data('id');
                            
                            startInlineEdit($cell, field, type, currentValue, postId);
                        });
                    }
                    
                    function startInlineEdit($cell, field, type, currentValue, postId) {
                        editingCell = { $cell, field, type, postId, originalValue: currentValue };
                        
                        let input;
                        if (type === 'longtext') {
                            input = $(`<textarea class="beamraymar-editing-textarea">${currentValue}</textarea>`);
                        } else if (type === 'select') {
                            let options = '';
                            if (field === 'post_status') {
                                options = '<option value="draft">draft</option><option value="publish">publish</option><option value="private">private</option>';
                            } else if (field === 'comment_status' || field === 'ping_status') {
                                options = '<option value="open">open</option><option value="closed">closed</option>';
                            }
                            input = $(`<select class="beamraymar-editing-input">${options}</select>`);
                            input.val(currentValue);
                        } else {
                            input = $(`<input type="text" class="beamraymar-editing-input" value="${currentValue}">`);
                        }
                        
                        $cell.html(input);
                        input.focus();
                        
                        // Handle save on blur or Enter key
                        input.on('blur', function() {
                            saveInlineEdit();
                        });
                        
                        input.on('keydown', function(e) {
                            if (e.key === 'Enter' && !e.shiftKey) {
                                e.preventDefault();
                                saveInlineEdit();
                            } else if (e.key === 'Escape') {
                                cancelInlineEdit();
                            }
                        });
                    }
                    
                    function saveInlineEdit() {
                        if (!editingCell) return;
                        
                        const $input = editingCell.$cell.find('input, textarea, select');
                        const newValue = $input.val();
                        
                        // Update UI immediately
                        editingCell.$cell.text(newValue);
                        
                        // Update data
                        const itemIndex = allData.findIndex(item => item.ID == editingCell.postId);
                        if (itemIndex !== -1) {
                            allData[itemIndex][editingCell.field] = newValue;
                        }
                        
                        // Save to server
                        saveFieldToServer(editingCell.postId, editingCell.field, newValue);
                        
                        editingCell = null;
                    }
                    
                    function cancelInlineEdit() {
                        if (!editingCell) return;
                        
                        editingCell.$cell.text(editingCell.originalValue);
                        editingCell = null;
                    }
                    
                    function saveFieldToServer(postId, field, value) {
                        $.post('<?php echo admin_url('admin.php?page=beamraymar'); ?>', {
                            action: 'update_post_field',
                            post_id: postId,
                            field: field,
                            value: value,
                            nonce: '<?php echo wp_create_nonce('beamraymar_nonce'); ?>'
                        }).fail(function(xhr, status, error) {
                            console.log('Save failed:', xhr.responseText);
                            alert('Failed to save changes. Please try again.');
                        });
                    }
                    
                    function createNewInline(postType) {
                        $.post('<?php echo admin_url('admin.php?page=beamraymar'); ?>', {
                            action: 'create_new_post',
                            post_type: postType,
                            nonce: '<?php echo wp_create_nonce('beamraymar_nonce'); ?>'
                        }).done(function(response) {
                            console.log('Server response:', response);
                            try {
                                const data = typeof response === 'string' ? JSON.parse(response) : response;
                                if (data.success) {
                                    // Add new post to beginning of data array
                                    allData.unshift(data.post);
                                    filterData();
                                    currentPage = 1;
                                    updateTable();
                                } else {
                                    alert('Failed to create new ' + postType + ': ' + (data.message || 'Unknown error'));
                                }
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                console.log('Raw response:', response);
                                alert('Failed to create new ' + postType + '. Please try again.');
                            }
                        }).fail(function(xhr, status, error) {
                            console.log('Request failed:', xhr.responseText);
                            alert('Failed to create new ' + postType + '. Please try again.');
                        });
                    }
                    
                    function openModal(mode, postData = null) {
                        if (mode === 'create') {
                            $('#beamraymar-modal-title').text('Create New Post/Page');
                            $('#modal-action').val('create');
                            $('#beamraymar-modal-form')[0].reset();
                        } else {
                            $('#beamraymar-modal-title').text('Edit Post/Page');
                            $('#modal-action').val('edit');
                            // Populate form with post data
                            Object.keys(postData).forEach(key => {
                                $(`#modal-${key}`).val(postData[key]);
                            });
                        }
                        
                        $('#beamraymar-modal').addClass('active');
                    }
                    
                    function closeModal() {
                        $('#beamraymar-modal').removeClass('active');
                    }
                    
                    function saveModalData() {
                        const formData = new FormData($('#beamraymar-modal-form')[0]);
                        formData.append('nonce', '<?php echo wp_create_nonce('beamraymar_nonce'); ?>');
                        
                        $.post('<?php echo admin_url('admin.php?page=beamraymar'); ?>', formData, {
                            processData: false,
                            contentType: false
                        }).done(function(response) {
                            console.log('Modal save response:', response);
                            try {
                                const data = typeof response === 'string' ? JSON.parse(response) : response;
                                if (data.success) {
                                    closeModal();
                                    // Refresh data
                                    location.reload();
                                } else {
                                    alert('Failed to save: ' + (data.message || 'Unknown error'));
                                }
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                alert('Failed to save. Please try again.');
                            }
                        }).fail(function(xhr, status, error) {
                            console.log('Modal save failed:', xhr.responseText);
                            alert('Failed to save. Please try again.');
                        });
                    }
                    
                    function updatePaginationButtons() {
                        const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                        
                        // Update page number buttons (simplified for demo)
                        $('.beamraymar-pagination-btn[data-page]').each(function() {
                            const $btn = $(this);
                            const page = $btn.data('page');
                            
                            if (page === 'first' || page === 'prev') {
                                $btn.toggleClass('disabled', currentPage <= 1);
                            } else if (page === 'next' || page === 'last') {
                                $btn.toggleClass('disabled', currentPage >= totalPages);
                            } else if (!isNaN(page)) {
                                $btn.removeClass('active');
                                if (parseInt(page) === currentPage) {
                                    $btn.addClass('active');
                                }
                            }
                        });
                    }
                    
                    function sortData(field) {
                        // Simple sorting implementation
                        filteredData.sort((a, b) => {
                            const aVal = a[field] || '';
                            const bVal = b[field] || '';
                            return aVal.toString().localeCompare(bVal.toString());
                        });
                    }
                    
                    // Utility functions
                    function truncateText(text, length) {
                        if (!text) return '';
                        return text.length > length ? text.substring(0, length) + '...' : text;
                    }
                    
                    function truncatePostContent(text) {
                        if (!text) return '';
                        
                        // Strip HTML tags to get plain text
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = text;
                        const plainText = tempDiv.textContent || tempDiv.innerText || '';
                        
                        // Get first line only
                        let firstLine = plainText.split(/\r?\n/)[0] || '';
                        
                        // Truncate to 50 characters with conditional ellipsis
                        if (firstLine.length > 50) {
                            return firstLine.substring(0, 50) + '...';
                        }
                        return firstLine;
                    }
                    
                    function formatDate(dateString) {
                        if (!dateString) return '';
                        return new Date(dateString).toLocaleString();
                    }
                    
                    function formatElementorData(elementorData) {
                        if (elementorData === null || elementorData === undefined) {
                            return 'none';
                        }
                        if (elementorData === '' || elementorData === '[]') {
                            return '0 lines';
                        }
                        const lineCount = (elementorData.match(/\n/g) || []).length + 1;
                        return lineCount + ' lines';
                    }
                    
                    // Elementor data editor functions
                    function openElementorEditor(postId) {
                        currentElementorEditPostId = postId;
                        const post = allData.find(item => item.ID == postId);
                        
                        if (post) {
                            $('#beamraymar-elementor-editor-textarea').val(post._elementor_data || '');
                            $('#beamraymar-elementor-editor-modal').addClass('active');
                        }
                    }
                    
                    function closeElementorEditor() {
                        $('#beamraymar-elementor-editor-modal').removeClass('active');
                        $('#beamraymar-elementor-editor-textarea').val('');
                        currentElementorEditPostId = null;
                    }
                    
                    function saveElementorEditor() {
                        if (!currentElementorEditPostId) return;
                        
                        const newElementorData = $('#beamraymar-elementor-editor-textarea').val();
                        
                        // Send AJAX request to save
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'beamraymar_update_elementor_data',
                                post_id: currentElementorEditPostId,
                                elementor_data: newElementorData,
                                nonce: '<?php echo wp_create_nonce('beamraymar_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Update local data
                                    const post = allData.find(item => item.ID == currentElementorEditPostId);
                                    if (post) {
                                        post._elementor_data = newElementorData;
                                    }
                                    
                                    // Refresh table
                                    updateTable();
                                    
                                    // Close editor
                                    closeElementorEditor();
                                } else {
                                    alert('Failed to save: ' + (response.data || 'Unknown error'));
                                }
                            },
                            error: function() {
                                alert('Failed to save elementor data');
                            }
                        });
                    }
                    
                    // Post content editor functions
                    function openContentEditor(postId) {
                        currentContentEditPostId = postId;
                        const post = allData.find(item => item.ID == postId);
                        
                        if (post) {
                            $('#beamraymar-content-editor-textarea').val(post.post_content || '');
                            $('#beamraymar-content-editor-modal').addClass('active');
                        }
                    }
                    
                    function closeContentEditor() {
                        $('#beamraymar-content-editor-modal').removeClass('active');
                        $('#beamraymar-content-editor-textarea').val('');
                        currentContentEditPostId = null;
                    }
                    
                    function saveContentEditor() {
                        if (!currentContentEditPostId) return;
                        
                        const newContent = $('#beamraymar-content-editor-textarea').val();
                        
                        // Send AJAX request to save
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'beamraymar_update_post_content',
                                post_id: currentContentEditPostId,
                                post_content: newContent,
                                nonce: '<?php echo wp_create_nonce('beamraymar_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Update local data
                                    const post = allData.find(item => item.ID == currentContentEditPostId);
                                    if (post) {
                                        post.post_content = newContent;
                                    }
                                    
                                    // Refresh table
                                    updateTable();
                                    
                                    // Close editor
                                    closeContentEditor();
                                } else {
                                    alert('Failed to save: ' + (response.data || 'Unknown error'));
                                }
                            },
                            error: function() {
                                alert('Failed to save content');
                            }
                        });
                    }
                    
                })(jQuery);
            </script>
        </div>
        <?php
    }
    
    /**
     * Get posts and pages data for beamraymar table
     */
    private function get_posts_and_pages_data() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT p.ID, p.post_title, p.post_content, p.post_type, p.post_status, p.post_name, 
                    p.post_date, p.post_modified, p.post_author, p.post_parent, p.menu_order, 
                    p.comment_status, p.ping_status,
                    pm.meta_value as _elementor_data,
                    zop.rel_wp_post_id, zop.orbitpost_id, zop.redshift_datum, zop.rover_datum, 
                    zop.hudson_imgplanbatch_id, zop.created_at, zop.updated_at
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_elementor_data'
             LEFT JOIN {$wpdb->prefix}zen_orbitposts zop ON p.ID = zop.rel_wp_post_id
             WHERE p.post_type IN ('post', 'page') 
             AND p.post_status NOT IN ('trash', 'auto-draft') 
             ORDER BY p.post_modified DESC",
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Render table rows for beamraymar table
     */
    private function render_beamraymar_table_rows($posts_pages) {
        if (empty($posts_pages)) {
            echo '<tr><td colspan="23" style="text-align: center; padding: 40px;"><div class="tcell_inner_wrapper_div">No posts or pages found.</div></td></tr>';
            return;
        }
        
        $count = 0;
        foreach ($posts_pages as $item) {
            if ($count >= 100) break; // Default pagination limit
            
            // Strip all HTML tags to get plain text
            $plain_text = strip_tags($item['post_content']);
            
            // Get first line only
            $first_line = explode("\n", $plain_text)[0];
            $first_line = explode("\r", $first_line)[0]; // Handle Windows line breaks
            
            // Truncate to 50 characters with conditional ellipsis
            if (strlen($first_line) > 50) {
                $content_preview = substr($first_line, 0, 50) . '...';
            } else {
                $content_preview = $first_line;
            }
            
            echo '<tr data-id="' . esc_attr($item['ID']) . '">';
            echo '<td class="beamraymar-checkbox-cell fact_checkbox">';
            echo '<div class="tcell_inner_wrapper_div"><input type="checkbox" class="beamraymar-checkbox row-checkbox" value="' . esc_attr($item['ID']) . '"></div>';
            echo '</td>';
            
            // Frontend URL logic
            $frontend_url = '';
            if ($item['post_status'] === 'draft') {
                $frontend_url = home_url('/?p=' . $item['ID'] . '&preview=true');
            } else {
                if ($item['post_name']) {
                    $frontend_url = home_url('/' . $item['post_name'] . '/');
                } else {
                    $frontend_url = home_url('/?p=' . $item['ID']);
                }
            }
            $admin_edit_url = admin_url('post.php?post=' . $item['ID'] . '&action=edit');
            $elementor_edit_url = admin_url('post.php?post=' . $item['ID'] . '&action=elementor');
            $is_elementor = !empty($item['_elementor_data']) && $item['_elementor_data'] !== '[]';
            
            echo '<td class="readonly-cell fact_tool_buttons"><div class="tcell_inner_wrapper_div">';
            echo '<a href="' . esc_url($admin_edit_url) . '" target="_blank" class="beamraymar-pendulum-btn">&#9675;&#124;</a>';
            echo '<a href="' . esc_url($frontend_url) . '" target="_blank" class="beamraymar-tool-btn">F</a>';
            if ($is_elementor) {
                echo '<a href="' . esc_url($elementor_edit_url) . '" target="_blank" class="beamraymar-elementor-btn">E</a>';
            } else {
                echo '<span class="beamraymar-elementor-btn disabled">E</span>';
            }
            echo '</div></td>';
            echo '<td class="readonly-cell fact_wp_posts_id"><div class="tcell_inner_wrapper_div">' . esc_html($item['ID']) . '</div></td>';
            echo '<td class="beamraymar-editable-cell fact_wp_posts_post_title" data-field="post_title" data-type="text"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_title']) . '</div></td>';
            echo '<td class="readonly-cell fact_wp_posts_post_content"><div class="tcell_inner_wrapper_div">' . esc_html($content_preview) . '<button class="beamraymar-content-edit-btn" data-post-id="' . esc_attr($item['ID']) . '">ED</button></div></td>';
            
            // Calculate elementor data line count
            $elementor_display = 'none';
            if (isset($item['_elementor_data']) && $item['_elementor_data'] !== null) {
                if (empty($item['_elementor_data']) || $item['_elementor_data'] === '[]') {
                    $elementor_display = '0 lines';
                } else {
                    $line_count = substr_count($item['_elementor_data'], "\n") + 1;
                    $elementor_display = $line_count . ' lines';
                }
            }
            echo '<td class="readonly-cell fact_wp_postmeta_meta_key_elementor_data"><div class="tcell_inner_wrapper_div">' . esc_html($elementor_display) . '<button class="beamraymar-content-edit-btn" data-post-id="' . esc_attr($item['ID']) . '" data-editor-type="elementor">ED</button></div></td>';
            
            echo '<td class="readonly-cell fact_wp_posts_post_type"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_type']) . '</div></td>';
            echo '<td class="beamraymar-editable-cell fact_wp_posts_post_status" data-field="post_status" data-type="select"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_status']) . '</div></td>';
            echo '<td class="beamraymar-editable-cell fact_wp_posts_post_name" data-field="post_name" data-type="text"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_name']) . '</div></td>';
            echo '<td class="readonly-cell fact_wp_posts_post_date"><div class="tcell_inner_wrapper_div">' . esc_html(date('Y-m-d H:i:s', strtotime($item['post_date']))) . '</div></td>';
            echo '<td class="readonly-cell fact_wp_posts_post_modified"><div class="tcell_inner_wrapper_div">' . esc_html(date('Y-m-d H:i:s', strtotime($item['post_modified']))) . '</div></td>';
            echo '<td class="beamraymar-editable-cell fact_wp_posts_post_author" data-field="post_author" data-type="integer"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_author']) . '</div></td>';
            echo '<td class="beamraymar-editable-cell fact_wp_posts_post_parent" data-field="post_parent" data-type="integer"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_parent'] ?: '0') . '</div></td>';
            echo '<td class="beamraymar-editable-cell fact_wp_posts_menu_order" data-field="menu_order" data-type="integer"><div class="tcell_inner_wrapper_div">' . esc_html($item['menu_order'] ?: '0') . '</div></td>';
            echo '<td class="beamraymar-editable-cell fact_wp_posts_comment_status" data-field="comment_status" data-type="select"><div class="tcell_inner_wrapper_div">' . esc_html($item['comment_status']) . '</div></td>';
            echo '<td class="beamraymar-editable-cell fact_wp_posts_ping_status" data-field="ping_status" data-type="select"><div class="tcell_inner_wrapper_div">' . esc_html($item['ping_status']) . '</div></td>';
            echo '<td class="readonly-cell fact_zen_orbitposts_rel_wp_post_id"><div class="tcell_inner_wrapper_div">' . esc_html($item['rel_wp_post_id'] ?: '') . '</div></td>';
            echo '<td class="readonly-cell fact_zen_orbitposts_orbitpost_id"><div class="tcell_inner_wrapper_div">' . esc_html($item['orbitpost_id'] ?: '') . '</div></td>';
            echo '<td class="readonly-cell fact_zen_orbitposts_redshift_datum"><div class="tcell_inner_wrapper_div">' . esc_html($item['redshift_datum'] ?: '') . '</div></td>';
            echo '<td class="readonly-cell fact_zen_orbitposts_rover_datum"><div class="tcell_inner_wrapper_div">' . esc_html($item['rover_datum'] ?: '') . '</div></td>';
            echo '<td class="readonly-cell fact_zen_orbitposts_hudson_imgplanbatch_id"><div class="tcell_inner_wrapper_div">' . esc_html($item['hudson_imgplanbatch_id'] ?: '') . '</div></td>';
            echo '<td class="readonly-cell fact_zen_orbitposts_created_at"><div class="tcell_inner_wrapper_div">' . esc_html($item['created_at'] ? date('Y-m-d H:i:s', strtotime($item['created_at'])) : '') . '</div></td>';
            echo '<td class="readonly-cell fact_zen_orbitposts_updated_at"><div class="tcell_inner_wrapper_div">' . esc_html($item['updated_at'] ? date('Y-m-d H:i:s', strtotime($item['updated_at'])) : '') . '</div></td>';
            echo '</tr>';
            
            $count++;
        }
    }
    
    /**
     * AJAX handler for updating elementor data from beamraymar editor
     */
    public function beamraymar_update_elementor_data() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'beamraymar_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        // Get and validate post ID
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
            return;
        }
        
        // Get new elementor data
        $new_elementor_data = isset($_POST['elementor_data']) ? $_POST['elementor_data'] : '';
        
        // Update the post meta
        if ($new_elementor_data === '' || $new_elementor_data === null) {
            delete_post_meta($post_id, '_elementor_data');
            wp_send_json_success('Elementor data cleared');
        } else {
            update_post_meta($post_id, '_elementor_data', $new_elementor_data);
            wp_send_json_success('Elementor data updated successfully');
        }
    }
    
    /**
     * AJAX handler for updating post content from beamraymar editor
     */
    public function beamraymar_update_post_content() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'beamraymar_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        // Get and validate post ID
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
            return;
        }
        
        // Get new content
        $new_content = isset($_POST['post_content']) ? wp_kses_post($_POST['post_content']) : '';
        
        // Update the post
        $updated = wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_content
        ));
        
        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } else {
            wp_send_json_success('Content updated successfully');
        }
    }
    
    /**
     * Handle AJAX requests for beamraymar page
     */
    private function handle_beamraymar_ajax() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'beamraymar_nonce')) {
            echo json_encode(array('success' => false, 'message' => 'Security check failed'));
            wp_die();
        }
        
        $action = sanitize_text_field($_POST['action']);
        
        switch ($action) {
            case 'create_new_post':
                $this->ajax_create_new_post();
                break;
                
            case 'update_post_field':
                $this->ajax_update_post_field();
                break;
                
            case 'create':
            case 'edit':
                $this->ajax_create_or_edit_post();
                break;
                
            default:
                wp_die('Invalid action');
        }
    }
    
    /**
     * AJAX handler for creating new post inline
     */
    private function ajax_create_new_post() {
        if (!isset($_POST['post_type'])) {
            echo json_encode(array('success' => false, 'message' => 'Post type not provided'));
            wp_die();
        }
        
        $post_type = sanitize_text_field($_POST['post_type']);
        
        if (!in_array($post_type, array('post', 'page'))) {
            echo json_encode(array('success' => false, 'message' => 'Invalid post type: ' . $post_type));
            wp_die();
        }
        
        $post_data = array(
            'post_type' => $post_type,
            'post_title' => '',
            'post_content' => '',
            'post_status' => 'draft',
            'post_author' => get_current_user_id()
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            echo json_encode(array('success' => false, 'message' => $post_id->get_error_message()));
            wp_die();
        }
        
        // Get the created post data
        $post = get_post($post_id, ARRAY_A);
        $response_data = array(
            'ID' => $post['ID'],
            'post_title' => $post['post_title'],
            'post_content' => $post['post_content'],
            'post_type' => $post['post_type'],
            'post_status' => $post['post_status'],
            'post_name' => $post['post_name'],
            'post_date' => $post['post_date'],
            'post_modified' => $post['post_modified'],
            'post_author' => $post['post_author'],
            'post_parent' => $post['post_parent'],
            'menu_order' => $post['menu_order'],
            'comment_status' => $post['comment_status'],
            'ping_status' => $post['ping_status']
        );
        
        echo json_encode(array('success' => true, 'post' => $response_data));
        wp_die();
    }
    
    /**
     * AJAX handler for updating post field inline
     */
    private function ajax_update_post_field() {
        $post_id = intval($_POST['post_id']);
        $field = sanitize_text_field($_POST['field']);
        $value = $_POST['value']; // Don't sanitize yet, depends on field type
        
        // Validate post exists
        if (!get_post($post_id)) {
            echo json_encode(array('success' => false, 'message' => 'Post not found'));
            wp_die();
        }
        
        // Sanitize value based on field type
        switch ($field) {
            case 'post_title':
            case 'post_name':
                $value = sanitize_text_field($value);
                break;
            case 'post_content':
                $value = wp_kses_post($value);
                break;
            case 'post_status':
                $value = in_array($value, array('draft', 'publish', 'private')) ? $value : 'draft';
                break;
            case 'comment_status':
            case 'ping_status':
                $value = in_array($value, array('open', 'closed')) ? $value : 'closed';
                break;
            case 'post_author':
            case 'post_parent':
            case 'menu_order':
                $value = intval($value);
                break;
            default:
                echo json_encode(array('success' => false, 'message' => 'Invalid field'));
                wp_die();
        }
        
        $update_data = array(
            'ID' => $post_id,
            $field => $value
        );
        
        $result = wp_update_post($update_data);
        
        if (is_wp_error($result)) {
            echo json_encode(array('success' => false, 'message' => $result->get_error_message()));
            wp_die();
        }
        
        echo json_encode(array('success' => true));
        wp_die();
    }
    
    /**
     * AJAX handler for create/edit post via modal
     */
    private function ajax_create_or_edit_post() {
        $action = sanitize_text_field($_POST['action']);
        $post_data = array(
            'post_type' => sanitize_text_field($_POST['post_type']),
            'post_title' => sanitize_text_field($_POST['post_title']),
            'post_content' => wp_kses_post($_POST['post_content']),
            'post_status' => sanitize_text_field($_POST['post_status']),
            'post_name' => sanitize_title($_POST['post_name']),
            'post_parent' => intval($_POST['post_parent'])
        );
        
        if ($action === 'edit') {
            $post_data['ID'] = intval($_POST['post_id']);
            $result = wp_update_post($post_data);
        } else {
            $post_data['post_author'] = get_current_user_id();
            $result = wp_insert_post($post_data);
        }
        
        if (is_wp_error($result)) {
            echo json_encode(array('success' => false, 'message' => $result->get_error_message()));
            wp_die();
        }
        
        echo json_encode(array('success' => true, 'post_id' => $result));
        wp_die();
    }
    
    /**
     * CSS Editor page (cssmar)
     */
    public function cssmar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        global $wpdb;
        
        // Get sitespren_base from database
        $sitespren_base = '';
        $zen_sitespren_table = $wpdb->prefix . 'zen_sitespren';
        if ($wpdb->get_var("SHOW TABLES LIKE '$zen_sitespren_table'") == $zen_sitespren_table) {
            $result = $wpdb->get_row("SELECT sitespren_base FROM $zen_sitespren_table WHERE wppma_id = 1");
            if ($result) {
                $sitespren_base = $result->sitespren_base;
            }
        }
        
        // Get CSS file content
        $css_file_path = SNEFURU_PLUGIN_PATH . 'assets/css/sddx_240_ruplin_screens_only_css_by_kyle_1.css';
        $css_content = '';
        if (file_exists($css_file_path)) {
            $css_content = file_get_contents($css_file_path);
        }
        
        // Construct full URL
        $plugin_url = SNEFURU_PLUGIN_URL . 'assets/css/sddx_240_ruplin_screens_only_css_by_kyle_1.css';
        $full_url = 'https://' . $sitespren_base . '/' . str_replace(home_url('/'), '', $plugin_url);
        
        ?>
        <div class="wrap cssmar-wrapper">
            <style>
                .cssmar-wrapper {
                    background: white;
                    padding: 20px;
                    margin: 0 0 0 -20px;
                }
                
                .cssmar-tabs {
                    display: flex;
                    border-bottom: 2px solid #ddd;
                    margin-bottom: 20px;
                }
                
                .cssmar-tab {
                    padding: 12px 20px;
                    background: #f1f1f1;
                    border: 1px solid #ddd;
                    border-bottom: none;
                    cursor: pointer;
                    font-weight: 600;
                    margin-right: 2px;
                }
                
                .cssmar-tab.active {
                    background: white;
                    border-bottom: 2px solid white;
                    margin-bottom: -2px;
                }
                
                .cssmar-url-container {
                    display: flex;
                    align-items: center;
                    margin-bottom: 15px;
                    gap: 10px;
                }
                
                .cssmar-url-input {
                    flex: 1;
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                    background: #f9f9f9;
                }
                
                .cssmar-copy-url-btn {
                    padding: 8px 16px;
                    background: #0073aa;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-weight: 600;
                }
                
                .cssmar-copy-url-btn:hover {
                    background: #005a87;
                }
                
                .cssmar-editor {
                    width: 100%;
                    height: 600px;
                    font-family: 'Courier New', monospace;
                    font-size: 14px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    padding: 15px;
                    background: #f9f9f9;
                    resize: vertical;
                }
                
                .cssmar-save-btn {
                    padding: 12px 24px;
                    background: #16a085;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-weight: 600;
                    font-size: 16px;
                }
                
                .cssmar-save-btn:hover {
                    background: #138d75;
                }
                
                .cssmar-save-container {
                    margin: 15px 0;
                }
            </style>
            
            <h1>CSS Editor (cssmar)</h1>
            
            <!-- Tabs -->
            <div class="cssmar-tabs">
                <div class="cssmar-tab active" data-tab="sddx-240">SDDX - 240</div>
                <div class="cssmar-tab" data-tab="tab2">tab2</div>
                <div class="cssmar-tab" data-tab="tab3">tab3</div>
                <div class="cssmar-tab" data-tab="tab4">tab4</div>
                <div class="cssmar-tab" data-tab="tab5">tab5</div>
            </div>
            
            <!-- URL Display -->
            <div class="cssmar-url-container">
                <input type="text" class="cssmar-url-input" value="<?php echo esc_attr($full_url); ?>" readonly>
                <button type="button" class="cssmar-copy-url-btn" onclick="copyUrlToClipboard()">Copy</button>
            </div>
            
            <!-- Save Button (Top) -->
            <div class="cssmar-save-container">
                <button type="button" class="cssmar-save-btn" onclick="saveCssFile()">Save</button>
            </div>
            
            <!-- CSS Editor -->
            <textarea class="cssmar-editor" id="cssmar-editor"><?php echo esc_textarea($css_content); ?></textarea>
            
            <!-- Save Button (Bottom) -->
            <div class="cssmar-save-container">
                <button type="button" class="cssmar-save-btn" onclick="saveCssFile()">Save</button>
            </div>
            
            <script>
                function copyUrlToClipboard() {
                    const urlInput = document.querySelector('.cssmar-url-input');
                    urlInput.select();
                    document.execCommand('copy');
                    
                    const button = document.querySelector('.cssmar-copy-url-btn');
                    const originalText = button.textContent;
                    button.textContent = 'Copied!';
                    button.style.background = '#16a085';
                    
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.style.background = '#0073aa';
                    }, 2000);
                }
                
                function saveCssFile() {
                    const content = document.getElementById('cssmar-editor').value;
                    const saveButtons = document.querySelectorAll('.cssmar-save-btn');
                    
                    // Update button states
                    saveButtons.forEach(btn => {
                        btn.textContent = 'Saving...';
                        btn.style.background = '#f39c12';
                        btn.disabled = true;
                    });
                    
                    const formData = new FormData();
                    formData.append('action', 'cssmar_save_css');
                    formData.append('css_content', content);
                    formData.append('nonce', '<?php echo wp_create_nonce('cssmar_nonce'); ?>');
                    
                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            saveButtons.forEach(btn => {
                                btn.textContent = 'Saved!';
                                btn.style.background = '#16a085';
                            });
                        } else {
                            saveButtons.forEach(btn => {
                                btn.textContent = 'Error';
                                btn.style.background = '#e74c3c';
                            });
                            alert('Error saving file: ' + (data.message || 'Unknown error'));
                        }
                        
                        setTimeout(() => {
                            saveButtons.forEach(btn => {
                                btn.textContent = 'Save';
                                btn.style.background = '#16a085';
                                btn.disabled = false;
                            });
                        }, 2000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        saveButtons.forEach(btn => {
                            btn.textContent = 'Error';
                            btn.style.background = '#e74c3c';
                            btn.disabled = false;
                        });
                        setTimeout(() => {
                            saveButtons.forEach(btn => {
                                btn.textContent = 'Save';
                                btn.style.background = '#16a085';
                            });
                        }, 2000);
                    });
                }
                
                // Tab functionality (currently only SDDX-240 is functional)
                document.querySelectorAll('.cssmar-tab').forEach(tab => {
                    tab.addEventListener('click', function() {
                        if (this.dataset.tab !== 'sddx-240') {
                            alert('This tab is not yet implemented.');
                            return;
                        }
                        
                        document.querySelectorAll('.cssmar-tab').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            </script>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for saving CSS file
     */
    public function cssmar_save_css() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cssmar_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $css_content = stripslashes($_POST['css_content']);
        $css_file_path = SNEFURU_PLUGIN_PATH . 'assets/css/sddx_240_ruplin_screens_only_css_by_kyle_1.css';
        
        // Save the file
        $result = file_put_contents($css_file_path, $css_content);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'CSS file saved successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save CSS file'));
        }
    }
} 