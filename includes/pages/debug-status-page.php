<?php

/**
 * Debug Status Page for Snefuru Plugin
 * Provides comprehensive status information for troubleshooting
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Debug_Status_Page {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_page'));
    }
    
    /**
     * Add the debug status page to admin menu
     */
    public function add_menu_page() {
        add_submenu_page(
            'tools.php',
            'Snefuru Status',
            'Snefuru Status',
            'manage_options',
            'snefuru-status',
            array($this, 'render_status_page')
        );
    }
    
    /**
     * Render the status page
     */
    public function render_status_page() {
        // Handle actions
        if (isset($_POST['action'])) {
            $this->handle_actions();
        }
        
        ?>
        <div class="wrap">
            <h1>Snefuru Plugin Status</h1>
            
            <?php $this->render_version_info(); ?>
            <?php $this->render_environment_info(); ?>
            <?php $this->render_elementor_status(); ?>
            <?php $this->render_database_status(); ?>
            <?php $this->render_class_status(); ?>
            <?php $this->render_error_log(); ?>
            <?php $this->render_actions(); ?>
        </div>
        <?php
    }
    
    /**
     * Handle form actions
     */
    private function handle_actions() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'snefuru_status_actions')) {
            wp_die('Security check failed');
        }
        
        switch ($_POST['action']) {
            case 'force_version_sync':
                if (class_exists('Snefuru_Version_Checker')) {
                    $checker = new Snefuru_Version_Checker();
                    $checker->force_sync();
                    echo '<div class="notice notice-success"><p>Version sync completed.</p></div>';
                }
                break;
                
            case 'run_db_migration':
                if (class_exists('Ruplin_WP_Database_Horse_Class')) {
                    Ruplin_WP_Database_Horse_Class::check_database_version();
                    echo '<div class="notice notice-success"><p>Database migration check completed.</p></div>';
                }
                break;
                
            case 'clear_activation_errors':
                delete_option('snefuru_activation_error');
                echo '<div class="notice notice-success"><p>Activation errors cleared.</p></div>';
                break;
        }
    }
    
    /**
     * Render version information section
     */
    private function render_version_info() {
        $version_status = array();
        if (class_exists('Snefuru_Version_Checker')) {
            $checker = new Snefuru_Version_Checker();
            $version_status = $checker->get_version_status();
        }
        
        ?>
        <div class="card">
            <h2>Version Information</h2>
            <table class="widefat">
                <tr>
                    <td><strong>Plugin Version</strong></td>
                    <td><?php echo esc_html(SNEFURU_PLUGIN_VERSION); ?></td>
                </tr>
                <?php if (!empty($version_status)): ?>
                <tr>
                    <td><strong>Deployed Version</strong></td>
                    <td><?php echo esc_html($version_status['deployed_version']); ?></td>
                </tr>
                <tr>
                    <td><strong>Version Synced</strong></td>
                    <td><?php echo $version_status['is_synced'] ? '✅ Yes' : '❌ No'; ?></td>
                </tr>
                <?php if (isset($version_status['db_version'])): ?>
                <tr>
                    <td><strong>Database Version</strong></td>
                    <td><?php echo esc_html($version_status['db_version']); ?></td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render environment information
     */
    private function render_environment_info() {
        ?>
        <div class="card">
            <h2>Environment Information</h2>
            <table class="widefat">
                <tr>
                    <td><strong>WordPress Version</strong></td>
                    <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                </tr>
                <tr>
                    <td><strong>PHP Version</strong></td>
                    <td><?php echo esc_html(phpversion()); ?></td>
                </tr>
                <tr>
                    <td><strong>Site URL</strong></td>
                    <td><?php echo esc_html(get_site_url()); ?></td>
                </tr>
                <tr>
                    <td><strong>WordPress Debug</strong></td>
                    <td><?php echo defined('WP_DEBUG') && WP_DEBUG ? '✅ Enabled' : '❌ Disabled'; ?></td>
                </tr>
                <tr>
                    <td><strong>Debug Logging</strong></td>
                    <td><?php echo defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? '✅ Enabled' : '❌ Disabled'; ?></td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render Elementor status
     */
    private function render_elementor_status() {
        $elementor_status = array();
        if (class_exists('Snefuru_Elementor_Manager')) {
            $manager = new Snefuru_Elementor_Manager();
            $elementor_status = $manager->get_elementor_status();
            $features = $manager->get_available_features();
        }
        
        ?>
        <div class="card">
            <h2>Elementor Integration Status</h2>
            <table class="widefat">
                <?php if (!empty($elementor_status)): ?>
                <tr>
                    <td><strong>Elementor Active</strong></td>
                    <td><?php echo $elementor_status['elementor_active'] ? '✅ Yes' : '❌ No'; ?></td>
                </tr>
                <tr>
                    <td><strong>Elementor Loaded</strong></td>
                    <td><?php echo $elementor_status['elementor_loaded'] ? '✅ Yes' : '❌ No'; ?></td>
                </tr>
                <tr>
                    <td><strong>Elementor Initialized</strong></td>
                    <td><?php echo $elementor_status['elementor_init'] ? '✅ Yes' : '❌ No'; ?></td>
                </tr>
                <?php if (isset($elementor_status['elementor_version'])): ?>
                <tr>
                    <td><strong>Elementor Version</strong></td>
                    <td><?php echo esc_html($elementor_status['elementor_version']); ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($features)): ?>
                <tr>
                    <td colspan="2"><strong>Available Features:</strong></td>
                </tr>
                <?php foreach ($features as $feature => $available): ?>
                <tr>
                    <td style="padding-left: 20px;"><?php echo esc_html($feature); ?></td>
                    <td><?php echo $available ? '✅ Available' : '❌ Not Available'; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                
                <?php else: ?>
                <tr>
                    <td colspan="2">Elementor status unavailable</td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render database status
     */
    private function render_database_status() {
        global $wpdb;
        
        // Check table existence
        $tables = array(
            'snefuru_logs',
            'snefuruplin_styling',
            'zen_sitespren',
            'zen_services',
            'zen_locations',
            'zen_orbitposts',
            'zen_cache_reports'
        );
        
        ?>
        <div class="card">
            <h2>Database Status</h2>
            <table class="widefat">
                <?php foreach ($tables as $table): ?>
                <?php
                $full_table_name = $wpdb->prefix . $table;
                $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
                $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name") : 0;
                ?>
                <tr>
                    <td><strong><?php echo esc_html($table); ?></strong></td>
                    <td>
                        <?php echo $exists ? '✅ Exists' : '❌ Missing'; ?>
                        <?php if ($exists): ?>
                            (<?php echo number_format($count); ?> records)
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render class loading status
     */
    private function render_class_status() {
        $classes = array(
            'Snefuru_API_Client',
            'Snefuru_Data_Collector',
            'Snefuru_Admin',
            'Snefuru_Settings',
            'Snefuru_Upload_Handler',
            'Snefuru_Error_Boundary',
            'Snefuru_Elementor_Manager',
            'Snefuru_Version_Checker',
            'Ruplin_WP_Database_Horse_Class',
            'Zen_Elementor_Dynamic_Tags',
            'Zen_Elementor_Media_Workaround'
        );
        
        ?>
        <div class="card">
            <h2>Class Loading Status</h2>
            <table class="widefat">
                <?php foreach ($classes as $class): ?>
                <tr>
                    <td><strong><?php echo esc_html($class); ?></strong></td>
                    <td><?php echo class_exists($class) ? '✅ Loaded' : '❌ Not Loaded'; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render recent error log entries
     */
    private function render_error_log() {
        $activation_error = get_option('snefuru_activation_error');
        
        ?>
        <div class="card">
            <h2>Error Status</h2>
            <?php if ($activation_error): ?>
            <div class="notice notice-error inline">
                <p><strong>Last Activation Error:</strong> <?php echo esc_html($activation_error); ?></p>
            </div>
            <?php else: ?>
            <p>✅ No recent activation errors</p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render action buttons
     */
    private function render_actions() {
        ?>
        <div class="card">
            <h2>Actions</h2>
            <form method="post" style="display: inline-block; margin-right: 10px;">
                <?php wp_nonce_field('snefuru_status_actions'); ?>
                <input type="hidden" name="action" value="force_version_sync">
                <input type="submit" class="button" value="Force Version Sync">
            </form>
            
            <form method="post" style="display: inline-block; margin-right: 10px;">
                <?php wp_nonce_field('snefuru_status_actions'); ?>
                <input type="hidden" name="action" value="run_db_migration">
                <input type="submit" class="button" value="Run DB Migration Check">
            </form>
            
            <form method="post" style="display: inline-block;">
                <?php wp_nonce_field('snefuru_status_actions'); ?>
                <input type="hidden" name="action" value="clear_activation_errors">
                <input type="submit" class="button" value="Clear Activation Errors">
            </form>
        </div>
        <?php
    }
}