<?php
/**
 * Ferret Snippets Main Class
 * 
 * Manages page-specific code snippets for headers and footers
 * 
 * @package Ruplin
 * @subpackage FerretSnippets
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ferret_Snippets {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * AJAX handler instance
     */
    private $ajax_handler;
    
    /**
     * Frontend handler instance  
     */
    private $frontend_handler;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'class-ferret-snippets-ajax.php';
        require_once plugin_dir_path(__FILE__) . 'class-ferret-snippets-frontend.php';
        
        $this->ajax_handler = new Ferret_Snippets_Ajax();
        $this->frontend_handler = new Ferret_Snippets_Frontend();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add content to Lightning popup
        add_action('ruplin_lightning_popup_content', array($this, 'render_popup_content'));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets($hook) {
        // Only load on post/page edit screens
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        global $post;
        if (!$post) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'ferret-snippets',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/ferret-snippets.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue JS
        wp_enqueue_script(
            'ferret-snippets',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/ferret-snippets.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script with necessary data
        wp_localize_script('ferret-snippets', 'ferretSnippets', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ferret_snippets_nonce'),
            'postId' => $post->ID,
            'messages' => array(
                'saveSuccess' => __('Code snippet saved successfully', 'ruplin'),
                'saveError' => __('Error saving code snippet', 'ruplin'),
                'loadError' => __('Error loading code snippets', 'ruplin')
            )
        ));
    }
    
    /**
     * Render content for Lightning popup
     */
    public function render_popup_content() {
        global $post;
        if (!$post) {
            return;
        }
        
        // Get existing snippets for this post
        $snippets = $this->get_snippets($post->ID);
        ?>
        <div id="ferret-snippets-container">
            <div class="ferret-tabs">
                <button class="ferret-tab active" data-tab="header">
                    header (before closing &lt;/head&gt; tag)
                </button>
                <button class="ferret-tab" data-tab="header2">
                    ferret_header_code_2
                </button>
                <button class="ferret-tab" data-tab="footer">
                    footer (before closing &lt;/body&gt; tag)
                </button>
            </div>
            
            <div class="ferret-tab-content">
                <div id="ferret-tab-header" class="ferret-tab-panel active">
                    <div class="ferret-snippet-controls">
                        <button type="button" class="button button-primary ferret-save-btn" data-type="header">
                            Save Header Code
                        </button>
                        <span class="ferret-db-info">
                            <?php global $wpdb; echo esc_html('(' . str_replace('_', ')_', $wpdb->prefix) . 'zen_orbitposts.ferret_header_code'); ?>
                        </span>
                    </div>
                    <textarea 
                        id="ferret-header-code" 
                        class="ferret-code-editor" 
                        placeholder="Enter code to be inserted before closing </head> tag..."
                        rows="20"><?php echo esc_textarea($snippets['header'] ?? ''); ?></textarea>
                </div>
                
                <div id="ferret-tab-header2" class="ferret-tab-panel">
                    <div class="ferret-snippet-controls">
                        <button type="button" class="button button-primary ferret-save-btn" data-type="header2">
                            Save Header Code 2
                        </button>
                        <span class="ferret-db-info">
                            <?php global $wpdb; echo esc_html('(' . str_replace('_', ')_', $wpdb->prefix) . 'zen_orbitposts.ferret_header_code_2'); ?>
                        </span>
                    </div>
                    <textarea 
                        id="ferret-header2-code" 
                        class="ferret-code-editor" 
                        placeholder="Enter additional header code..."
                        rows="20"><?php echo esc_textarea($snippets['header2'] ?? ''); ?></textarea>
                </div>
                
                <div id="ferret-tab-footer" class="ferret-tab-panel">
                    <div class="ferret-snippet-controls">
                        <button type="button" class="button button-primary ferret-save-btn" data-type="footer">
                            Save Footer Code
                        </button>
                        <span class="ferret-db-info">
                            <?php global $wpdb; echo esc_html('(' . str_replace('_', ')_', $wpdb->prefix) . 'zen_orbitposts.ferret_footer_code'); ?>
                        </span>
                    </div>
                    <textarea 
                        id="ferret-footer-code" 
                        class="ferret-code-editor" 
                        placeholder="Enter code to be inserted before closing </body> tag..."
                        rows="20"><?php echo esc_textarea($snippets['footer'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="ferret-snippet-message" style="display:none;"></div>
        </div>
        <?php
    }
    
    /**
     * Get snippets for a post
     */
    public function get_snippets($post_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'zen_orbitposts';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT ferret_header_code, ferret_header_code_2, ferret_footer_code 
             FROM $table 
             WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        return array(
            'header' => $result['ferret_header_code'] ?? '',
            'header2' => $result['ferret_header_code_2'] ?? '',
            'footer' => $result['ferret_footer_code'] ?? ''
        );
    }
}