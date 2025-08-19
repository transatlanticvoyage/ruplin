<?php

/**
 * Snefuru Version Checker
 * Tracks plugin version changes and deployment sync
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Version_Checker {
    
    const VERSION_OPTION = 'snefuru_deployed_version';
    const LAST_CHECK_OPTION = 'snefuru_last_version_check';
    
    /**
     * Initialize version checking
     */
    public function __construct() {
        add_action('init', array($this, 'check_deployment_sync'));
    }
    
    /**
     * Check if deployment is synced with codebase version
     */
    public function check_deployment_sync() {
        $plugin_version = SNEFURU_PLUGIN_VERSION;
        $deployed_version = get_option(self::VERSION_OPTION, '0.0.0');
        $last_check = get_option(self::LAST_CHECK_OPTION, 0);
        
        // Only check once per hour to avoid excessive logging
        if ((time() - $last_check) < 3600) {
            return;
        }
        
        update_option(self::LAST_CHECK_OPTION, time());
        
        if (version_compare($plugin_version, $deployed_version, '>')) {
            $this->handle_version_update($deployed_version, $plugin_version);
        } elseif (version_compare($plugin_version, $deployed_version, '<')) {
            $this->handle_version_rollback($deployed_version, $plugin_version);
        }
    }
    
    /**
     * Handle version update (deployment is newer)
     * 
     * @param string $old_version Previous version
     * @param string $new_version New version
     */
    private function handle_version_update($old_version, $new_version) {
        update_option(self::VERSION_OPTION, $new_version);
        
        // Log version update
        error_log("Snefuru updated: {$old_version} -> {$new_version}");
        
        // Store update info for admin display
        $update_info = array(
            'from' => $old_version,
            'to' => $new_version,
            'timestamp' => time(),
            'type' => 'update'
        );
        update_option('snefuru_last_version_change', $update_info);
        
        // Trigger update actions
        do_action('snefuru_version_updated', $old_version, $new_version);
        
        // Check if database migration is needed
        $this->check_database_migration($new_version);
    }
    
    /**
     * Handle version rollback (deployment is older)
     * 
     * @param string $deployed_version Version currently deployed
     * @param string $plugin_version Version in plugin files
     */
    private function handle_version_rollback($deployed_version, $plugin_version) {
        // Log potential rollback
        error_log("Snefuru potential rollback detected: deployed {$deployed_version} vs plugin {$plugin_version}");
        
        // Store rollback info for admin display
        $rollback_info = array(
            'deployed' => $deployed_version,
            'plugin' => $plugin_version,
            'timestamp' => time(),
            'type' => 'rollback'
        );
        update_option('snefuru_last_version_change', $rollback_info);
        
        // Add admin notice about version mismatch
        add_action('admin_notices', function() use ($deployed_version, $plugin_version) {
            echo '<div class="notice notice-warning"><p>';
            echo '<strong>Snefuru:</strong> Version mismatch detected. ';
            echo "Deployed: {$deployed_version}, Plugin files: {$plugin_version}. ";
            echo 'This may indicate a deployment issue.';
            echo '</p></div>';
        });
    }
    
    /**
     * Check if database migration might be needed
     * 
     * @param string $new_version New plugin version
     */
    private function check_database_migration($new_version) {
        if (class_exists('Ruplin_WP_Database_Horse_Class')) {
            $db_version = get_option(Ruplin_WP_Database_Horse_Class::DB_VERSION_OPTION, '0.0.0');
            
            if (version_compare($new_version, $db_version, '>')) {
                error_log("Snefuru: Database migration may be needed. Plugin: {$new_version}, DB: {$db_version}");
                
                // Trigger database version check
                Snefuru_Error_Boundary::guard(function() {
                    Ruplin_WP_Database_Horse_Class::check_database_version();
                });
            }
        }
    }
    
    /**
     * Get current version status information
     * 
     * @return array Version status details
     */
    public function get_version_status() {
        $plugin_version = SNEFURU_PLUGIN_VERSION;
        $deployed_version = get_option(self::VERSION_OPTION, '0.0.0');
        $last_change = get_option('snefuru_last_version_change', array());
        
        $status = array(
            'plugin_version' => $plugin_version,
            'deployed_version' => $deployed_version,
            'is_synced' => version_compare($plugin_version, $deployed_version, '='),
            'last_check' => get_option(self::LAST_CHECK_OPTION, 0),
            'last_change' => $last_change
        );
        
        if (class_exists('Ruplin_WP_Database_Horse_Class')) {
            $status['db_version'] = get_option(Ruplin_WP_Database_Horse_Class::DB_VERSION_OPTION, '0.0.0');
        }
        
        return $status;
    }
    
    /**
     * Force version sync (useful for manual deployment fixes)
     */
    public function force_sync() {
        $plugin_version = SNEFURU_PLUGIN_VERSION;
        update_option(self::VERSION_OPTION, $plugin_version);
        update_option(self::LAST_CHECK_OPTION, time());
        
        error_log("Snefuru: Version manually synced to {$plugin_version}");
        
        return true;
    }
    
    /**
     * Get deployment history (if available)
     * 
     * @return array Recent deployment information
     */
    public function get_deployment_history() {
        // This could be expanded to track multiple deployments
        $last_change = get_option('snefuru_last_version_change', array());
        
        if (empty($last_change)) {
            return array();
        }
        
        return array($last_change);
    }
}