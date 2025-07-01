<?php
/**
 * Hot Plugin Updater for Snefuru Plugin
 * Allows uploading and overwriting plugin files without deactivation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Hot_Plugin_Updater {
    
    private $plugin_dir;
    
    public function __construct() {
        $this->plugin_dir = plugin_dir_path(dirname(__FILE__));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_snefuru_hot_update', array($this, 'handle_hot_update'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Add admin menu with main Snefuru menu and submenu
     */
    public function add_admin_menu() {
        // Main Snefuru menu (if it doesn't exist)
        add_menu_page(
            'Snefuru', // Page title
            'Snefuru', // Menu title
            'manage_options', // Capability
            'snefuru-main', // Menu slug
            array($this, 'main_admin_page'), // Callback
            'dashicons-cloud', // Icon
            30 // Position
        );
        
        // Hot Plugin Updater submenu
        add_submenu_page(
            'snefuru-main', // Parent slug
            'Hot Plugin Updater', // Page title
            'Hot Plugin Updater', // Menu title
            'manage_options', // Capability
            'snefuru-hot-updater', // Menu slug
            array($this, 'hot_updater_page') // Callback
        );
        
        // Debug Log Viewer submenu (move from Tools to Snefuru)
        add_submenu_page(
            'snefuru-main', // Parent slug
            'Debug Log Viewer', // Page title
            'Debug Log Viewer', // Menu title
            'manage_options', // Capability
            'snefuru-debug-log', // Menu slug
            array($this, 'debug_log_redirect') // Callback
        );
        
        // Ketch Width Manager submenu (move from Settings to Snefuru)
        add_submenu_page(
            'snefuru-main', // Parent slug
            'Ketch Width Manager', // Page title
            'Ketch Width Manager', // Menu title
            'manage_options', // Capability
            'snefuru-ketch-settings', // Menu slug
            array($this, 'ketch_settings_redirect') // Callback
        );
    }
    
    /**
     * Main Snefuru admin page
     */
    public function main_admin_page() {
        ?>
        <div class="wrap">
            <h1>Snefuru Control Panel</h1>
            
            <div class="snefuru-dashboard" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                
                <!-- Hot Plugin Updater Card -->
                <div class="dashboard-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2 style="margin-top: 0; color: #e74c3c;">🔥 Hot Plugin Updater</h2>
                    <p>Upload and update plugin files without deactivation. Perfect for development and testing.</p>
                    <a href="<?php echo admin_url('admin.php?page=snefuru-hot-updater'); ?>" class="button button-primary">
                        Access Hot Updater
                    </a>
                </div>
                
                <!-- Ketch Width Manager Card -->
                <div class="dashboard-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2 style="margin-top: 0; color: #3498db;">📏 Ketch Width Manager</h2>
                    <p>Configure Supabase credentials and manage UI table column widths dynamically.</p>
                    <a href="<?php echo admin_url('admin.php?page=snefuru-ketch-settings'); ?>" class="button button-primary">
                        Access Ketch Manager
                    </a>
                </div>
                
                <!-- Debug Log Viewer Card -->
                <div class="dashboard-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2 style="margin-top: 0; color: #f39c12;">🔍 Debug Log Viewer</h2>
                    <p>View WordPress debug logs, PHP errors, and troubleshoot plugin issues.</p>
                    <a href="<?php echo admin_url('admin.php?page=snefuru-debug-log'); ?>" class="button button-primary">
                        Access Debug Logs
                    </a>
                </div>
                
                <!-- System Status Card -->
                <div class="dashboard-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2 style="margin-top: 0; color: #27ae60;">⚡ System Status</h2>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li><strong>Plugin Version:</strong> <?php echo defined('SNEFURU_PLUGIN_VERSION') ? SNEFURU_PLUGIN_VERSION : 'Unknown'; ?></li>
                        <li><strong>WordPress:</strong> <?php echo get_bloginfo('version'); ?></li>
                        <li><strong>PHP:</strong> <?php echo PHP_VERSION; ?></li>
                        <li><strong>Debug Mode:</strong> <?php echo defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled'; ?></li>
                    </ul>
                </div>
                
            </div>
        </div>
        <?php
    }
    
    /**
     * Redirect functions for moved menu items
     */
    public function debug_log_redirect() {
        // Include the debug log viewer class and call its admin page
        if (class_exists('Snefuru_Debug_Log_Viewer')) {
            $debug_viewer = new Snefuru_Debug_Log_Viewer();
            $debug_viewer->admin_page();
        }
    }
    
    public function ketch_settings_redirect() {
        // Include the ketch settings class and call its admin page
        if (class_exists('Ketch_Settings')) {
            $ketch_settings = new Ketch_Settings();
            $ketch_settings->admin_page();
        }
    }
    
    /**
     * Hot Plugin Updater admin page
     */
    public function hot_updater_page() {
        ?>
        <div class="wrap">
            <h1>🔥 Hot Plugin Updater</h1>
            
            <div class="notice notice-warning">
                <p><strong>⚠️ Warning:</strong> This tool will overwrite existing plugin files without deactivation. Always backup your site before using this feature.</p>
            </div>
            
            <!-- Upload Form -->
            <div class="card" style="max-width: 600px;">
                <h2>Upload New Plugin Version</h2>
                
                <form id="hot-update-form" enctype="multipart/form-data">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="plugin-zip">Plugin ZIP File</label>
                            </th>
                            <td>
                                <input type="file" id="plugin-zip" name="plugin_zip" accept=".zip" required />
                                <p class="description">Select a ZIP file containing the updated Snefuruplin plugin.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="backup-current">Backup Current Version</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="backup-current" name="backup_current" checked />
                                    Create backup of current plugin before updating
                                </label>
                                <p class="description">Recommended: Creates a timestamped backup in wp-content/uploads/snefuru-backups/</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="clear-caches">Clear Caches</label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="clear-caches" name="clear_caches" checked />
                                    Clear PHP opcache and object cache after update
                                </label>
                                <p class="description">Recommended: Ensures new code is loaded immediately</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large" id="upload-btn">
                            🚀 Upload & Update Plugin
                        </button>
                    </p>
                </form>
            </div>
            
            <!-- Progress & Results -->
            <div id="update-progress" style="display: none;">
                <div class="card">
                    <h3>Update Progress</h3>
                    <div class="progress-container">
                        <div id="progress-steps"></div>
                        <div id="progress-log" style="background: #f9f9f9; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; margin-top: 15px;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Current Plugin Info -->
            <div class="card" style="margin-top: 30px;">
                <h2>Current Plugin Information</h2>
                <table class="widefat">
                    <tr>
                        <td><strong>Plugin Directory:</strong></td>
                        <td><code><?php echo esc_html($this->plugin_dir); ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>Plugin Version:</strong></td>
                        <td><?php echo defined('SNEFURU_PLUGIN_VERSION') ? SNEFURU_PLUGIN_VERSION : 'Unknown'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Modified:</strong></td>
                        <td>
                            <?php 
                            $main_file = $this->plugin_dir . 'snefuru-plugin.php';
                            if (file_exists($main_file)) {
                                echo date('Y-m-d H:i:s', filemtime($main_file));
                            } else {
                                echo 'Unknown';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Directory Writable:</strong></td>
                        <td>
                            <?php echo is_writable($this->plugin_dir) ? '✅ Yes' : '❌ No'; ?>
                            <?php if (!is_writable($this->plugin_dir)): ?>
                                <span style="color: red;">(Update will fail - check file permissions)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <style>
        .progress-step {
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .progress-step.pending { background: #f0f0f0; color: #666; }
        .progress-step.running { background: #e3f2fd; color: #1976d2; }
        .progress-step.success { background: #e8f5e8; color: #2e7d32; }
        .progress-step.error { background: #ffebee; color: #c62828; }
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>
        <?php
    }
    
    /**
     * Enqueue scripts for the hot updater
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'snefuru_page_snefuru-hot-updater') {
            return;
        }
        
        wp_enqueue_script(
            'snefuru-hot-updater',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/hot-updater.js',
            array('jquery'),
            SNEFURU_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('snefuru-hot-updater', 'snefuru_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('snefuru_hot_update_nonce'),
        ));
    }
    
    /**
     * Handle AJAX hot update request
     */
    public function handle_hot_update() {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'snefuru_hot_update_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check if file was uploaded
        if (empty($_FILES['plugin_zip'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['plugin_zip'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('File upload error: ' . $file['error']);
        }
        
        if ($file['type'] !== 'application/zip' && !str_ends_with($file['name'], '.zip')) {
            wp_send_json_error('File must be a ZIP archive');
        }
        
        $steps = array();
        
        try {
            // Step 1: Create backup if requested
            if (!empty($_POST['backup_current'])) {
                $steps[] = $this->create_backup();
            }
            
            // Step 2: Extract uploaded ZIP
            $steps[] = $this->extract_zip($file['tmp_name']);
            
            // Step 3: Validate extracted content
            $steps[] = $this->validate_plugin_structure();
            
            // Step 4: Update plugin files
            $steps[] = $this->update_plugin_files();
            
            // Step 5: Clear caches if requested
            if (!empty($_POST['clear_caches'])) {
                $steps[] = $this->clear_caches();
            }
            
            // Step 6: Verify update
            $steps[] = $this->verify_update();
            
            wp_send_json_success(array(
                'message' => 'Plugin updated successfully!',
                'steps' => $steps,
                'timestamp' => current_time('mysql')
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'steps' => $steps
            ));
        }
    }
    
    /**
     * Create backup of current plugin
     */
    private function create_backup() {
        $backup_dir = WP_CONTENT_DIR . '/uploads/snefuru-backups';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backup_file = $backup_dir . '/snefuruplin-backup-' . $timestamp . '.zip';
        
        // Create ZIP of current plugin
        $zip = new ZipArchive();
        if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('Could not create backup ZIP file');
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($this->plugin_dir, '', $file->getRealPath());
                $zip->addFile($file->getRealPath(), $relativePath);
            }
        }
        
        $zip->close();
        
        return array(
            'step' => 'create_backup',
            'status' => 'success',
            'message' => 'Backup created: ' . basename($backup_file)
        );
    }
    
    /**
     * Extract uploaded ZIP file
     */
    private function extract_zip($zip_path) {
        $temp_dir = WP_CONTENT_DIR . '/uploads/snefuru-temp-' . uniqid();
        wp_mkdir_p($temp_dir);
        
        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== TRUE) {
            throw new Exception('Could not open uploaded ZIP file');
        }
        
        $zip->extractTo($temp_dir);
        $zip->close();
        
        // Store temp directory for later use
        update_option('snefuru_temp_extract_dir', $temp_dir);
        
        return array(
            'step' => 'extract_zip',
            'status' => 'success',
            'message' => 'ZIP file extracted to temporary directory'
        );
    }
    
    /**
     * Validate plugin structure
     */
    private function validate_plugin_structure() {
        $temp_dir = get_option('snefuru_temp_extract_dir');
        
        // Look for snefuru-plugin.php in the extracted files
        $main_file_found = false;
        $plugin_root = '';
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getFilename() === 'snefuru-plugin.php') {
                $main_file_found = true;
                $plugin_root = dirname($file->getRealPath());
                break;
            }
        }
        
        if (!$main_file_found) {
            throw new Exception('Invalid plugin: snefuru-plugin.php not found in uploaded ZIP');
        }
        
        // Store plugin root for update
        update_option('snefuru_plugin_root', $plugin_root);
        
        return array(
            'step' => 'validate_structure',
            'status' => 'success',
            'message' => 'Plugin structure validated'
        );
    }
    
    /**
     * Update plugin files
     */
    private function update_plugin_files() {
        $plugin_root = get_option('snefuru_plugin_root');
        
        // Copy files from temp directory to plugin directory
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        $files_updated = 0;
        
        foreach ($iterator as $file) {
            $relativePath = str_replace($plugin_root, '', $file->getRealPath());
            $targetPath = $this->plugin_dir . ltrim($relativePath, '/');
            
            if ($file->isDir()) {
                if (!file_exists($targetPath)) {
                    wp_mkdir_p($targetPath);
                }
            } else {
                if (!copy($file->getRealPath(), $targetPath)) {
                    throw new Exception('Failed to copy file: ' . $relativePath);
                }
                $files_updated++;
            }
        }
        
        return array(
            'step' => 'update_files',
            'status' => 'success',
            'message' => "Updated {$files_updated} files"
        );
    }
    
    /**
     * Clear caches
     */
    private function clear_caches() {
        $caches_cleared = array();
        
        // Clear PHP opcache
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $caches_cleared[] = 'PHP OPCache';
        }
        
        // Clear WordPress object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
            $caches_cleared[] = 'WordPress Object Cache';
        }
        
        return array(
            'step' => 'clear_caches',
            'status' => 'success',
            'message' => 'Cleared: ' . implode(', ', $caches_cleared)
        );
    }
    
    /**
     * Verify update was successful
     */
    private function verify_update() {
        // Clean up temp files
        $temp_dir = get_option('snefuru_temp_extract_dir');
        if ($temp_dir && file_exists($temp_dir)) {
            $this->recursive_delete($temp_dir);
        }
        
        // Clean up options
        delete_option('snefuru_temp_extract_dir');
        delete_option('snefuru_plugin_root');
        
        // Check if main plugin file exists and is readable
        $main_file = $this->plugin_dir . 'snefuru-plugin.php';
        if (!file_exists($main_file)) {
            throw new Exception('Update verification failed: Main plugin file missing');
        }
        
        return array(
            'step' => 'verify_update',
            'status' => 'success',
            'message' => 'Update verified and cleanup completed'
        );
    }
    
    /**
     * Recursively delete directory
     */
    private function recursive_delete($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($dir);
    }
}

// Initialize the hot plugin updater
new Snefuru_Hot_Plugin_Updater();