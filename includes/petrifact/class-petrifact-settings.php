<?php
/**
 * Petrifact Settings Class
 * 
 * Handles admin interface for Petrifact persistence options
 * 
 * @package Petrifact
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Petrifact_Settings {
    
    /**
     * Settings page slug
     */
    const PAGE_SLUG = 'petrifact-settings';
    
    /**
     * Initialize the settings page
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_post_rebuild_petrifact', array($this, 'rebuild_petrifact'));
        add_action('admin_post_remove_petrifact', array($this, 'remove_petrifact'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Petrifact Settings',
            'Petrifact Settings',
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('petrifact_settings', 'snefuru_petrifact_enabled');
        register_setting('petrifact_settings', 'snefuru_preserve_data_on_uninstall');
        register_setting('petrifact_settings', 'snefuru_petrifact_auto_update');
        
        add_settings_section(
            'petrifact_general',
            'General Settings',
            array($this, 'render_general_section'),
            'petrifact_settings'
        );
        
        add_settings_field(
            'petrifact_enabled',
            'Enable Petrifact',
            array($this, 'render_enabled_field'),
            'petrifact_settings',
            'petrifact_general'
        );
        
        add_settings_field(
            'preserve_data',
            'Preserve Data on Uninstall',
            array($this, 'render_preserve_data_field'),
            'petrifact_settings',
            'petrifact_general'
        );
        
        add_settings_field(
            'auto_update',
            'Auto-update Petrifact',
            array($this, 'render_auto_update_field'),
            'petrifact_settings',
            'petrifact_general'
        );
        
        add_settings_section(
            'petrifact_status',
            'Status Information',
            array($this, 'render_status_section'),
            'petrifact_settings'
        );
    }
    
    /**
     * Render the settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="petrifact-admin-wrapper">
                <div class="petrifact-main-content">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('petrifact_settings');
                        do_settings_sections('petrifact_settings');
                        submit_button();
                        ?>
                    </form>
                    
                    <div class="petrifact-actions">
                        <h3>Actions</h3>
                        
                        <p>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=rebuild_petrifact'), 'rebuild_petrifact'); ?>" 
                               class="button button-secondary">
                                🔄 Rebuild Petrifact MU-Plugin
                            </a>
                            <span class="description">Regenerate the MU-plugin with latest shortcodes</span>
                        </p>
                        
                        <p>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=remove_petrifact'), 'remove_petrifact'); ?>" 
                               class="button button-secondary petrifact-danger" 
                               onclick="return confirm('Are you sure? This will remove the Petrifact MU-plugin.')">
                                🗑️ Remove Petrifact MU-Plugin
                            </a>
                            <span class="description">Remove the MU-plugin (data will remain)</span>
                        </p>
                    </div>
                </div>
                
                <div class="petrifact-sidebar">
                    <?php $this->render_info_box(); ?>
                    <?php $this->render_shortcode_list(); ?>
                </div>
            </div>
        </div>
        
        <style>
            .petrifact-admin-wrapper {
                display: flex;
                gap: 30px;
                margin-top: 20px;
            }
            .petrifact-main-content {
                flex: 2;
            }
            .petrifact-sidebar {
                flex: 1;
            }
            .petrifact-info-box {
                background: #f1f1f1;
                border: 1px solid #ccd0d4;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .petrifact-status {
                margin: 10px 0;
                padding: 8px 12px;
                border-radius: 3px;
                font-weight: bold;
            }
            .petrifact-status.installed {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .petrifact-status.not-installed {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .petrifact-actions {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #ccd0d4;
            }
            .petrifact-actions p {
                margin: 15px 0;
            }
            .petrifact-actions .description {
                display: block;
                margin-top: 5px;
                color: #666;
                font-style: italic;
            }
            .petrifact-danger {
                color: #dc3545 !important;
            }
            .petrifact-shortcodes {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }
            .petrifact-shortcodes h4 {
                margin: 0;
                padding: 12px 15px;
                background: #f8f9fa;
                border-bottom: 1px solid #ccd0d4;
            }
            .petrifact-shortcodes ul {
                margin: 0;
                padding: 0;
            }
            .petrifact-shortcodes li {
                list-style: none;
                padding: 8px 15px;
                border-bottom: 1px solid #f0f0f1;
                font-family: monospace;
                font-size: 13px;
            }
            .petrifact-shortcodes li:last-child {
                border-bottom: none;
            }
        </style>
        <?php
    }
    
    /**
     * Render general section description
     */
    public function render_general_section() {
        echo '<p>Configure how Petrifact manages persistent data and shortcodes.</p>';
    }
    
    /**
     * Render status section description
     */
    public function render_status_section() {
        $this->render_status_info();
    }
    
    /**
     * Render enabled field
     */
    public function render_enabled_field() {
        $enabled = get_option('snefuru_petrifact_enabled', 1);
        ?>
        <label>
            <input type="checkbox" name="snefuru_petrifact_enabled" value="1" <?php checked($enabled, 1); ?> />
            Install and maintain Petrifact MU-plugin
        </label>
        <p class="description">
            When enabled, Petrifact will install a Must-Use plugin that keeps shortcodes functional even after the main plugin is removed.
        </p>
        <?php
    }
    
    /**
     * Render preserve data field
     */
    public function render_preserve_data_field() {
        $preserve = get_option('snefuru_preserve_data_on_uninstall', 1);
        ?>
        <label>
            <input type="checkbox" name="snefuru_preserve_data_on_uninstall" value="1" <?php checked($preserve, 1); ?> />
            Keep database tables when uninstalling plugin
        </label>
        <p class="description">
            When enabled, zen_services, zen_locations, and zen_sitespren tables will remain after plugin deletion. 
            <strong>Disable this if you want complete data removal.</strong>
        </p>
        <?php
    }
    
    /**
     * Render auto-update field
     */
    public function render_auto_update_field() {
        $auto_update = get_option('snefuru_petrifact_auto_update', 1);
        ?>
        <label>
            <input type="checkbox" name="snefuru_petrifact_auto_update" value="1" <?php checked($auto_update, 1); ?> />
            Automatically update Petrifact when plugin updates
        </label>
        <p class="description">
            When enabled, the MU-plugin will automatically regenerate when new shortcodes are added.
        </p>
        <?php
    }
    
    /**
     * Render status information
     */
    private function render_status_info() {
        $is_installed = Petrifact_Generator::is_installed();
        $validation = Petrifact_Generator::validate_installation();
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row">MU-Plugin Status</th>';
        echo '<td>';
        
        if ($is_installed) {
            echo '<div class="petrifact-status installed">✅ Installed</div>';
            
            if ($validation['version_match']) {
                echo '<p>Version: ' . esc_html(Petrifact_Registry::get_version()) . ' (up to date)</p>';
            } else {
                echo '<p>Version: Outdated (needs update)</p>';
            }
        } else {
            echo '<div class="petrifact-status not-installed">❌ Not Installed</div>';
        }
        
        echo '</td>';
        echo '</tr>';
        
        // Tables status
        echo '<tr>';
        echo '<th scope="row">Database Tables</th>';
        echo '<td>';
        
        foreach ($validation['tables_exist'] as $table => $exists) {
            $status = $exists ? '✅' : '❌';
            $table_display = str_replace('zen_', '', $table);
            echo '<p>' . $status . ' ' . esc_html($table_display) . '</p>';
        }
        
        echo '</td>';
        echo '</tr>';
        
        // Shortcodes status (if MU plugin is loaded)
        if (!empty($validation['shortcodes_registered'])) {
            echo '<tr>';
            echo '<th scope="row">Registered Shortcodes</th>';
            echo '<td>';
            
            foreach ($validation['shortcodes_registered'] as $shortcode => $registered) {
                $status = $registered ? '✅' : '❌';
                echo '<p>' . $status . ' [' . esc_html($shortcode) . ']</p>';
            }
            
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
    
    /**
     * Render info box in sidebar
     */
    private function render_info_box() {
        ?>
        <div class="petrifact-info-box">
            <h4>What is Petrifact?</h4>
            <p>
                Petrifact is a persistence layer that keeps your zen shortcodes and data functional 
                even after the main Snefuruplin plugin is removed or deactivated.
            </p>
            <p>
                It works by creating a lightweight Must-Use plugin that contains only the essential 
                shortcode handlers and database queries needed to display your content.
            </p>
            <p>
                <strong>Use cases:</strong>
            </p>
            <ul>
                <li>Plugin updates and maintenance</li>
                <li>Site migrations</li>
                <li>Content protection</li>
                <li>Development and staging</li>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Render shortcode list in sidebar
     */
    private function render_shortcode_list() {
        $shortcodes = Petrifact_Registry::get_persistent_shortcodes();
        ?>
        <div class="petrifact-shortcodes">
            <h4>Persistent Shortcodes</h4>
            <ul>
                <?php foreach (array_keys($shortcodes) as $shortcode): ?>
                    <li>[<?php echo esc_html($shortcode); ?>]</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Handle rebuild action
     */
    public function rebuild_petrifact() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'], 'rebuild_petrifact')) {
            wp_die('Unauthorized');
        }
        
        $result = Petrifact_Generator::generate_and_install();
        
        if (is_wp_error($result)) {
            set_transient('petrifact_admin_notice', array(
                'type' => 'error',
                'message' => 'Failed to rebuild Petrifact: ' . $result->get_error_message()
            ), 30);
        } else {
            set_transient('petrifact_admin_notice', array(
                'type' => 'success',
                'message' => 'Petrifact MU-plugin rebuilt successfully!'
            ), 30);
        }
        
        wp_redirect(admin_url('options-general.php?page=' . self::PAGE_SLUG));
        exit;
    }
    
    /**
     * Handle remove action
     */
    public function remove_petrifact() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'], 'remove_petrifact')) {
            wp_die('Unauthorized');
        }
        
        $result = Petrifact_Generator::remove_mu_plugin();
        
        if ($result) {
            set_transient('petrifact_admin_notice', array(
                'type' => 'success',
                'message' => 'Petrifact MU-plugin removed successfully!'
            ), 30);
        } else {
            set_transient('petrifact_admin_notice', array(
                'type' => 'error',
                'message' => 'Failed to remove Petrifact MU-plugin'
            ), 30);
        }
        
        wp_redirect(admin_url('options-general.php?page=' . self::PAGE_SLUG));
        exit;
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        $notice = get_transient('petrifact_admin_notice');
        if (!$notice) {
            return;
        }
        
        delete_transient('petrifact_admin_notice');
        
        $class = $notice['type'] === 'success' ? 'notice-success' : 'notice-error';
        ?>
        <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
            <p><?php echo esc_html($notice['message']); ?></p>
        </div>
        <?php
    }
}