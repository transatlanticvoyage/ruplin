<?php
/**
 * WordPress Debug Log Viewer for Snefuru Plugin
 * Provides admin interface to view WordPress debug logs
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Debug_Log_Viewer {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_clear_debug_log', array($this, 'clear_debug_log'));
        add_action('wp_ajax_refresh_debug_log', array($this, 'refresh_debug_log'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_management_page(
            'WP Debug Log (Snefuru)', // Page title
            'WP Debug Log (Snefuru)', // Menu title
            'manage_options', // Capability
            'snefuru-debug-log', // Menu slug
            array($this, 'admin_page') // Callback
        );
    }
    
    /**
     * Get debug log file paths to check
     */
    private function get_debug_log_paths() {
        $paths = array();
        
        // Standard WordPress debug log locations
        $paths[] = array(
            'name' => 'WordPress Debug Log (wp-content)',
            'path' => WP_CONTENT_DIR . '/debug.log',
            'type' => 'wordpress'
        );
        
        $paths[] = array(
            'name' => 'Root Error Log',
            'path' => ABSPATH . 'error_log',
            'type' => 'server'
        );
        
        $paths[] = array(
            'name' => 'Root Error Log (.log)',
            'path' => ABSPATH . 'error.log',
            'type' => 'server'
        );
        
        $paths[] = array(
            'name' => 'PHP Error Log (wp-content)',
            'path' => WP_CONTENT_DIR . '/error_log',
            'type' => 'server'
        );
        
        // Check if WP_DEBUG_LOG is defined with custom path
        if (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG)) {
            $paths[] = array(
                'name' => 'Custom Debug Log',
                'path' => WP_DEBUG_LOG,
                'type' => 'custom'
            );
        }
        
        return $paths;
    }
    
    /**
     * Read log file content
     */
    private function read_log_file($file_path, $lines = 100) {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return false;
        }
        
        $file_size = filesize($file_path);
        if ($file_size === 0) {
            return "Log file is empty.";
        }
        
        // For large files, read only the last N lines
        if ($file_size > 1024 * 1024) { // 1MB
            return $this->tail_file($file_path, $lines);
        } else {
            return file_get_contents($file_path);
        }
    }
    
    /**
     * Get last N lines from file (like tail command)
     */
    private function tail_file($file_path, $lines = 100) {
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return false;
        }
        
        $line_buffer = array();
        
        while (($line = fgets($handle)) !== false) {
            $line_buffer[] = $line;
            if (count($line_buffer) > $lines) {
                array_shift($line_buffer);
            }
        }
        
        fclose($handle);
        return implode('', $line_buffer);
    }
    
    /**
     * AJAX handler to clear debug log
     */
    public function clear_debug_log() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('snefuru_debug_nonce');
        
        $log_path = sanitize_text_field($_POST['log_path']);
        
        if (file_exists($log_path) && is_writable($log_path)) {
            if (file_put_contents($log_path, '') !== false) {
                wp_send_json_success('Log file cleared successfully');
            } else {
                wp_send_json_error('Failed to clear log file');
            }
        } else {
            wp_send_json_error('Log file not found or not writable');
        }
    }
    
    /**
     * AJAX handler to refresh debug log
     */
    public function refresh_debug_log() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('snefuru_debug_nonce');
        
        $log_path = sanitize_text_field($_POST['log_path']);
        $content = $this->read_log_file($log_path, 200);
        
        if ($content !== false) {
            wp_send_json_success($content);
        } else {
            wp_send_json_error('Failed to read log file');
        }
    }
    
    /**
     * Admin page HTML
     */
    public function admin_page() {
        $debug_paths = $this->get_debug_log_paths();
        $wp_debug_status = defined('WP_DEBUG') && WP_DEBUG;
        $wp_debug_log_status = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
        ?>
        <div class="wrap">
            <h1>WP Debug Log (Snefuru)</h1>
            
            <!-- Debug Status Section -->
            <div class="notice notice-info">
                <h3>WordPress Debug Configuration</h3>
                <ul>
                    <li><strong>WP_DEBUG:</strong> <?php echo $wp_debug_status ? '✅ Enabled' : '❌ Disabled'; ?></li>
                    <li><strong>WP_DEBUG_LOG:</strong> <?php echo $wp_debug_log_status ? '✅ Enabled' : '❌ Disabled'; ?></li>
                    <li><strong>WP_DEBUG_DISPLAY:</strong> <?php echo (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) ? '✅ Enabled' : '❌ Disabled'; ?></li>
                </ul>
                <?php if (!$wp_debug_status || !$wp_debug_log_status): ?>
                <p><strong>Note:</strong> To enable debug logging, add these lines to your wp-config.php file:</p>
                <pre style="background: #f0f0f0; padding: 10px; border-radius: 4px;">define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);</pre>
                <?php endif; ?>
            </div>
            
            <!-- Log Files Section -->
            <div class="debug-log-container">
                <?php foreach ($debug_paths as $index => $log_info): ?>
                    <?php
                    $file_exists = file_exists($log_info['path']);
                    $file_readable = $file_exists && is_readable($log_info['path']);
                    $file_size = $file_exists ? filesize($log_info['path']) : 0;
                    $content = $file_readable ? $this->read_log_file($log_info['path'], 200) : false;
                    ?>
                    
                    <div class="log-section" style="margin-bottom: 30px; border: 1px solid #ddd; border-radius: 5px; padding: 20px;">
                        <h2><?php echo esc_html($log_info['name']); ?></h2>
                        
                        <div class="log-info" style="margin-bottom: 15px;">
                            <p><strong>Path:</strong> <code><?php echo esc_html($log_info['path']); ?></code></p>
                            <p>
                                <strong>Status:</strong> 
                                <?php if ($file_exists): ?>
                                    ✅ Exists (<?php echo number_format($file_size); ?> bytes)
                                    <?php if ($file_readable): ?>
                                        | ✅ Readable
                                    <?php else: ?>
                                        | ❌ Not readable
                                    <?php endif; ?>
                                <?php else: ?>
                                    ❌ File not found
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <?php if ($file_readable && $content !== false): ?>
                            <div class="log-actions" style="margin-bottom: 10px;">
                                <button type="button" class="button" onclick="refreshLog('<?php echo esc_js($log_info['path']); ?>', <?php echo $index; ?>)">
                                    🔄 Refresh
                                </button>
                                <button type="button" class="button button-secondary" onclick="clearLog('<?php echo esc_js($log_info['path']); ?>', <?php echo $index; ?>)">
                                    🗑️ Clear Log
                                </button>
                                <button type="button" class="button button-secondary" onclick="toggleLog(<?php echo $index; ?>)">
                                    👁️ Toggle View
                                </button>
                            </div>
                            
                            <div id="log-content-<?php echo $index; ?>" class="log-content" style="display: block;">
                                <textarea readonly style="width: 100%; height: 300px; font-family: monospace; font-size: 12px; background: #f9f9f9; border: 1px solid #ddd; padding: 10px;"><?php echo esc_textarea($content); ?></textarea>
                            </div>
                        <?php elseif ($file_exists && !$file_readable): ?>
                            <p style="color: #d63638;">❌ Cannot read log file. Check file permissions.</p>
                        <?php elseif ($file_exists && $file_size === 0): ?>
                            <p style="color: #00a32a;">✅ Log file exists but is empty.</p>
                        <?php else: ?>
                            <p style="color: #dba617;">ℹ️ Log file does not exist yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section" style="margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 5px;">
                <h3>🔍 Quick Filters</h3>
                <p>Look for these common error patterns in the logs above:</p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="button" onclick="highlightText('Fatal error')">Fatal Errors</button>
                    <button type="button" class="button" onclick="highlightText('PHP Warning')">PHP Warnings</button>
                    <button type="button" class="button" onclick="highlightText('PHP Notice')">PHP Notices</button>
                    <button type="button" class="button" onclick="highlightText('ketch')">Ketch Related</button>
                    <button type="button" class="button" onclick="highlightText('snefuru')">Snefuru Related</button>
                    <button type="button" class="button" onclick="highlightText('REST')">REST API</button>
                    <button type="button" class="button" onclick="clearHighlight()">Clear Highlights</button>
                </div>
            </div>
        </div>
        
        <script>
        function refreshLog(logPath, index) {
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Refreshing...';
            button.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'refresh_debug_log',
                    log_path: logPath,
                    _ajax_nonce: '<?php echo wp_create_nonce('snefuru_debug_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('#log-content-' + index + ' textarea').value = data.data;
                } else {
                    alert('Error: ' + data.data);
                }
            })
            .catch(error => {
                alert('Network error: ' + error.message);
            })
            .finally(() => {
                button.textContent = originalText;
                button.disabled = false;
            });
        }
        
        function clearLog(logPath, index) {
            if (!confirm('Are you sure you want to clear this log file? This action cannot be undone.')) {
                return;
            }
            
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Clearing...';
            button.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'clear_debug_log',
                    log_path: logPath,
                    _ajax_nonce: '<?php echo wp_create_nonce('snefuru_debug_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('#log-content-' + index + ' textarea').value = '';
                    alert('Log file cleared successfully');
                } else {
                    alert('Error: ' + data.data);
                }
            })
            .catch(error => {
                alert('Network error: ' + error.message);
            })
            .finally(() => {
                button.textContent = originalText;
                button.disabled = false;
            });
        }
        
        function toggleLog(index) {
            const content = document.getElementById('log-content-' + index);
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        }
        
        function highlightText(searchText) {
            clearHighlight();
            const textareas = document.querySelectorAll('.log-content textarea');
            textareas.forEach(textarea => {
                const content = textarea.value;
                const highlighted = content.replace(
                    new RegExp(searchText, 'gi'),
                    '***$&***'
                );
                textarea.value = highlighted;
            });
        }
        
        function clearHighlight() {
            const textareas = document.querySelectorAll('.log-content textarea');
            textareas.forEach(textarea => {
                textarea.value = textarea.value.replace(/\*\*\*(.*?)\*\*\*/g, '$1');
            });
        }
        </script>
        
        <style>
        .debug-log-container .log-section {
            background: white;
        }
        .log-content textarea {
            font-family: 'Courier New', monospace;
            line-height: 1.4;
        }
        .filter-section button {
            margin-bottom: 5px;
        }
        </style>
        <?php
    }
}

// Initialize the debug log viewer
new Snefuru_Debug_Log_Viewer();