<?php
/**
 * Ketch Width Management Settings
 * WordPress admin page for configuring Supabase credentials and Ketch system
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ketch_Settings {
    
    private $option_group = 'ketch_settings';
    private $option_name = 'ketch_options';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_options_page(
            'Ketch Width Manager', // Page title
            'Ketch Width Manager', // Menu title
            'manage_options', // Capability
            'ketch-settings', // Menu slug
            array($this, 'admin_page') // Callback
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting($this->option_group, $this->option_name, array($this, 'sanitize_settings'));
        
        // Supabase Configuration Section
        add_settings_section(
            'ketch_supabase_section',
            'Supabase Database Configuration',
            array($this, 'supabase_section_callback'),
            $this->option_group
        );
        
        add_settings_field(
            'supabase_url',
            'Supabase Project URL',
            array($this, 'supabase_url_callback'),
            $this->option_group,
            'ketch_supabase_section'
        );
        
        add_settings_field(
            'supabase_anon_key',
            'Supabase Anon Key',
            array($this, 'supabase_anon_key_callback'),
            $this->option_group,
            'ketch_supabase_section'
        );
        
        // CSS Configuration Section
        add_settings_section(
            'ketch_css_section',
            'CSS File Configuration',
            array($this, 'css_section_callback'),
            $this->option_group
        );
        
        add_settings_field(
            'css_file_path',
            'CSS File Path',
            array($this, 'css_file_path_callback'),
            $this->option_group,
            'ketch_css_section'
        );
    }
    
    /**
     * Sanitize settings input
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['supabase_url'])) {
            $sanitized['supabase_url'] = esc_url_raw($input['supabase_url']);
        }
        
        if (isset($input['supabase_anon_key'])) {
            $sanitized['supabase_anon_key'] = sanitize_text_field($input['supabase_anon_key']);
        }
        
        if (isset($input['css_file_path'])) {
            $sanitized['css_file_path'] = sanitize_text_field($input['css_file_path']);
        } else {
            $sanitized['css_file_path'] = 'wp-content/ketch/ketch-widths-all.css';
        }
        
        return $sanitized;
    }
    
    /**
     * Get option value
     */
    public function get_option($key, $default = '') {
        $options = get_option($this->option_name, array());
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    /**
     * Section callbacks
     */
    public function supabase_section_callback() {
        echo '<p>Enter your Supabase project credentials. You can find these in your Supabase dashboard under Settings → API.</p>';
    }
    
    public function css_section_callback() {
        echo '<p>Configure where the generated CSS file should be stored.</p>';
    }
    
    /**
     * Field callbacks
     */
    public function supabase_url_callback() {
        $value = $this->get_option('supabase_url');
        echo '<input type="url" name="' . $this->option_name . '[supabase_url]" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://your-project.supabase.co" required />';
        echo '<p class="description">Your Supabase project URL (e.g., https://abcdefghijk.supabase.co)</p>';
    }
    
    public function supabase_anon_key_callback() {
        $value = $this->get_option('supabase_anon_key');
        $masked_value = $value ? substr($value, 0, 20) . '...' : '';
        echo '<input type="password" name="' . $this->option_name . '[supabase_anon_key]" value="' . esc_attr($value) . '" class="regular-text" placeholder="eyJhbGciOiJIUzI1NiIs..." required />';
        echo '<p class="description">Your Supabase anon/public key (starts with eyJ...)</p>';
        if ($value) {
            echo '<p class="description"><strong>Current key:</strong> ' . esc_html($masked_value) . '</p>';
        }
    }
    
    public function css_file_path_callback() {
        $value = $this->get_option('css_file_path', 'wp-content/ketch/ketch-widths-all.css');
        echo '<input type="text" name="' . $this->option_name . '[css_file_path]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">Relative path from WordPress root where CSS file will be generated</p>';
    }
    
    /**
     * Admin page HTML
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Ketch Width Manager Settings</h1>
            
            <?php
            // Show success message if settings were saved
            if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
            }
            ?>
            
            <div class="ketch-admin-container" style="display: flex; gap: 20px;">
                <!-- Settings Form -->
                <div style="flex: 2;">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields($this->option_group);
                        do_settings_sections($this->option_group);
                        submit_button('Save Settings');
                        ?>
                    </form>
                    
                    <!-- Test Connection -->
                    <div class="ketch-test-section" style="margin-top: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h3>Test Connection</h3>
                        <p>Click the button below to test your Supabase connection and trigger CSS generation.</p>
                        <button type="button" id="ketch-test-connection" class="button button-secondary">Test Connection & Generate CSS</button>
                        <div id="ketch-test-result" style="margin-top: 10px;"></div>
                    </div>
                </div>
                
                <!-- Info Sidebar -->
                <div style="flex: 1; background: #f9f9f9; padding: 20px; border-radius: 5px;">
                    <h3>How to Get Supabase Credentials</h3>
                    <ol>
                        <li>Go to <a href="https://supabase.com" target="_blank">supabase.com</a></li>
                        <li>Sign in to your account</li>
                        <li>Select your project</li>
                        <li>Go to <strong>Settings → API</strong></li>
                        <li>Copy your <strong>Project URL</strong> and <strong>anon public key</strong></li>
                    </ol>
                    
                    <h3>System Status</h3>
                    <ul>
                        <li><strong>Supabase URL:</strong> 
                            <?php echo $this->get_option('supabase_url') ? '✅ Configured' : '❌ Not set'; ?>
                        </li>
                        <li><strong>Supabase Key:</strong> 
                            <?php echo $this->get_option('supabase_anon_key') ? '✅ Configured' : '❌ Not set'; ?>
                        </li>
                        <li><strong>CSS Directory:</strong> 
                            <?php 
                            $css_dir = ABSPATH . 'wp-content/ketch/';
                            echo is_dir($css_dir) ? '✅ Exists' : '❌ Will be created';
                            ?>
                        </li>
                    </ul>
                    
                    <h3>About Ketch System</h3>
                    <p>The Ketch Width Management System allows you to control column widths across your UI tables dynamically. Settings are stored in Supabase and automatically generate CSS files.</p>
                </div>
            </div>
        </div>
        
        <script>
        document.getElementById('ketch-test-connection').addEventListener('click', function() {
            const button = this;
            const resultDiv = document.getElementById('ketch-test-result');
            
            button.disabled = true;
            button.textContent = 'Testing...';
            resultDiv.innerHTML = '<p style="color: #666;">Testing connection...</p>';
            
            // Make AJAX request to test webhook
            fetch('<?php echo plugins_url('webhooks/update-ketch-css.php', dirname(__FILE__)); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = '<p style="color: green;">✅ Success! CSS file generated with ' + data.settings_count + ' settings.</p>';
                } else {
                    resultDiv.innerHTML = '<p style="color: red;">❌ Error: ' + data.error + '</p>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<p style="color: red;">❌ Connection failed: ' + error.message + '</p>';
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Test Connection & Generate CSS';
            });
        });
        </script>
        
        <style>
        .ketch-admin-container input[type="url"],
        .ketch-admin-container input[type="password"],
        .ketch-admin-container input[type="text"] {
            width: 100%;
            max-width: 500px;
        }
        </style>
        <?php
    }
}

// Initialize the settings class
new Ketch_Settings();