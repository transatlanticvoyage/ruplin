<?php
/**
 * Ferret Snippets AJAX Handler
 * 
 * Handles AJAX requests for saving and loading code snippets
 * 
 * @package Ruplin
 * @subpackage FerretSnippets
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ferret_Snippets_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize AJAX hooks
     */
    private function init_hooks() {
        // AJAX handlers for logged in users
        add_action('wp_ajax_ferret_save_snippet', array($this, 'save_snippet'));
        add_action('wp_ajax_ferret_load_snippets', array($this, 'load_snippets'));
    }
    
    /**
     * Save code snippet via AJAX
     */
    public function save_snippet() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ferret_snippets_nonce')) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Security check failed'
            )));
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Insufficient permissions'
            )));
        }
        
        // Sanitize and validate input
        $post_id = absint($_POST['post_id']);
        $snippet_type = sanitize_text_field($_POST['snippet_type']);
        $snippet_code = wp_unslash($_POST['snippet_code']); // Allow HTML/JS but unslash
        
        if (!$post_id || !in_array($snippet_type, array('header', 'header2', 'footer'))) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Invalid parameters'
            )));
        }
        
        // Verify post exists and user can edit it
        $post = get_post($post_id);
        if (!$post || !current_user_can('edit_post', $post_id)) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Cannot edit this post'
            )));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'zen_orbitposts';
        // Map snippet type to column name
        if ($snippet_type === 'header2') {
            $column = 'ferret_header_code_2';
        } else {
            $column = 'ferret_' . $snippet_type . '_code';
        }
        
        // Check if orbitpost record exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT orbitpost_id FROM $table WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        if ($existing) {
            // Update existing record
            $result = $wpdb->update(
                $table,
                array($column => $snippet_code),
                array('rel_wp_post_id' => $post_id),
                array('%s'),
                array('%d')
            );
        } else {
            // Insert new record
            $data = array(
                'rel_wp_post_id' => $post_id,
                $column => $snippet_code
            );
            $result = $wpdb->insert($table, $data, array('%d', '%s'));
        }
        
        if ($result !== false) {
            wp_die(json_encode(array(
                'success' => true,
                'message' => ucfirst($snippet_type) . ' code saved successfully'
            )));
        } else {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Database error: ' . $wpdb->last_error
            )));
        }
    }
    
    /**
     * Load code snippets via AJAX
     */
    public function load_snippets() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ferret_snippets_nonce')) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Security check failed'
            )));
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Insufficient permissions'
            )));
        }
        
        $post_id = absint($_POST['post_id']);
        if (!$post_id) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Invalid post ID'
            )));
        }
        
        // Verify post exists and user can edit it
        $post = get_post($post_id);
        if (!$post || !current_user_can('edit_post', $post_id)) {
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Cannot edit this post'
            )));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'zen_orbitposts';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT ferret_header_code, ferret_header_code_2, ferret_footer_code 
             FROM $table 
             WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        wp_die(json_encode(array(
            'success' => true,
            'data' => array(
                'header' => $result['ferret_header_code'] ?? '',
                'header2' => $result['ferret_header_code_2'] ?? '',
                'footer' => $result['ferret_footer_code'] ?? ''
            )
        )));
    }
}