<?php

/**
 * Snefuru Elementor Manager
 * Handles safe loading and initialization of Elementor-dependent features
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Elementor_Manager {
    
    /**
     * Initialize the Elementor manager
     */
    public function __construct() {
        // Wait for all plugins to load before checking Elementor status
        add_action('plugins_loaded', array($this, 'check_elementor'), 20);
    }
    
    /**
     * Check if Elementor is available and set up initialization
     */
    public function check_elementor() {
        if (!$this->is_elementor_available()) {
            error_log('Snefuru: Elementor not available, skipping Elementor features');
            return;
        }
        
        error_log('Snefuru: Elementor detected, setting up Elementor features');
        
        // Wait for Elementor to fully initialize before loading our features
        add_action('elementor/init', array($this, 'init_elementor_features'));
    }
    
    /**
     * Check if Elementor is properly available
     * 
     * @return bool True if Elementor is ready
     */
    private function is_elementor_available() {
        return class_exists('Elementor\Plugin') && did_action('elementor/loaded');
    }
    
    /**
     * Initialize all Elementor-dependent features safely
     */
    public function init_elementor_features() {
        error_log('Snefuru: Initializing Elementor features');
        
        // Initialize Dynamic Tags with error boundary
        Snefuru_Error_Boundary::guard(function() {
            if (class_exists('Zen_Elementor_Dynamic_Tags')) {
                new Zen_Elementor_Dynamic_Tags();
                error_log('Snefuru: Zen_Elementor_Dynamic_Tags initialized');
            } else {
                error_log('Snefuru: Zen_Elementor_Dynamic_Tags class not found');
            }
        }, function($error) {
            error_log('Snefuru: Failed to initialize Elementor Dynamic Tags: ' . $error->getMessage());
        });
        
        // Initialize Media Workaround with error boundary
        Snefuru_Error_Boundary::guard(function() {
            if (class_exists('Zen_Elementor_Media_Workaround')) {
                new Zen_Elementor_Media_Workaround();
                error_log('Snefuru: Zen_Elementor_Media_Workaround initialized');
            } else {
                error_log('Snefuru: Zen_Elementor_Media_Workaround class not found');
            }
        }, function($error) {
            error_log('Snefuru: Failed to initialize Elementor Media Workaround: ' . $error->getMessage());
        });
        
        // Hook for additional Elementor features that might be added later
        do_action('snefuru_elementor_features_loaded');
    }
    
    /**
     * Get Elementor status information for debugging
     * 
     * @return array Status information
     */
    public function get_elementor_status() {
        $status = array(
            'elementor_active' => class_exists('Elementor\Plugin'),
            'elementor_loaded' => did_action('elementor/loaded'),
            'elementor_init' => did_action('elementor/init'),
            'available' => $this->is_elementor_available()
        );
        
        if ($status['elementor_active']) {
            $status['elementor_version'] = defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'unknown';
        }
        
        return $status;
    }
    
    /**
     * Check if specific Elementor feature classes are available
     * 
     * @return array List of available feature classes
     */
    public function get_available_features() {
        $features = array(
            'Zen_Elementor_Dynamic_Tags' => class_exists('Zen_Elementor_Dynamic_Tags'),
            'Zen_Elementor_Media_Workaround' => class_exists('Zen_Elementor_Media_Workaround')
        );
        
        return $features;
    }
}