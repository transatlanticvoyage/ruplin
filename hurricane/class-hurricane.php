<?php

/**
 * Hurricane Feature for Snefuruplin
 * Adds Hurricane interface element to post/page edit screens
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include Blueshift and Cobalt classes
require_once plugin_dir_path(__FILE__) . 'class-blueshift.php';
require_once plugin_dir_path(__FILE__) . 'class-cobalt.php';

class Snefuru_Hurricane {
    
    private $blueshift;
    private $cobalt;
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_hurricane_metabox'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_hurricane_assets'));
        add_action('edit_form_top', array($this, 'add_stellar_chamber'));
        add_action('wp_ajax_refresh_redshift_data', array($this, 'ajax_refresh_redshift_data'));
        add_action('wp_ajax_refresh_blueshift_data', array($this, 'ajax_refresh_blueshift_data'));
        add_action('wp_ajax_cobalt_inject_content', array($this, 'ajax_cobalt_inject_content'));
        
        // Initialize Blueshift and Cobalt
        $this->blueshift = new Snefuru_Blueshift();
        $this->cobalt = new Snefuru_Cobalt();
    }
    
    /**
     * AJAX handler for Blueshift data refresh
     */
    public function ajax_refresh_blueshift_data() {
        $this->blueshift->ajax_refresh_blueshift_data();
    }
    
    /**
     * AJAX handler for Cobalt content injection
     */
    public function ajax_cobalt_inject_content() {
        $this->cobalt->ajax_cobalt_inject_content();
    }
    
    /**
     * Extract frontend text content from Elementor data
     * Parses _elementor_data JSON to get clean text content with widget separation
     */
    private function extract_elementor_frontend_text($post_id) {
        // Get Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return 'No Elementor data found for this page.';
        }
        
        // Decode JSON data
        $elements = json_decode($elementor_data, true);
        if (!$elements || !is_array($elements)) {
            return 'Could not parse Elementor data.';
        }
        
        $extracted_content = array();
        
        // Process each top-level element (sections/containers)
        foreach ($elements as $element) {
            $content = $this->process_elementor_element($element);
            if (!empty($content)) {
                $extracted_content[] = $content;
            }
        }
        
        // Join content with separator bars
        return implode("\n\n———————————————————————\n\n", $extracted_content);
    }
    
    /**
     * Recursively process Elementor elements to extract text content
     */
    private function process_elementor_element($element) {
        if (!is_array($element) || empty($element['elType'])) {
            return '';
        }
        
        $content_parts = array();
        
        // Extract content based on element type
        $widget_content = $this->extract_widget_text($element);
        if (!empty($widget_content)) {
            $content_parts[] = $widget_content;
        }
        
        // Process child elements recursively
        if (!empty($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child_element) {
                $child_content = $this->process_elementor_element($child_element);
                if (!empty($child_content)) {
                    $content_parts[] = $child_content;
                }
            }
        }
        
        return implode("\n", array_filter($content_parts));
    }
    
    /**
     * Extract text content from specific widget types
     */
    private function extract_widget_text($element) {
        if (empty($element['widgetType']) && empty($element['elType'])) {
            return '';
        }
        
        $widget_type = !empty($element['widgetType']) ? $element['widgetType'] : $element['elType'];
        $settings = !empty($element['settings']) ? $element['settings'] : array();
        
        $text_content = '';
        
        switch ($widget_type) {
            case 'heading':
                if (!empty($settings['title'])) {
                    $text_content = strip_tags($settings['title']);
                }
                break;
                
            case 'text-editor':
                if (!empty($settings['editor'])) {
                    $text_content = wp_strip_all_tags($settings['editor']);
                }
                break;
                
            case 'button':
                if (!empty($settings['text'])) {
                    $text_content = strip_tags($settings['text']);
                }
                break;
                
            case 'image':
                $parts = array();
                if (!empty($settings['caption'])) {
                    $parts[] = 'Caption: ' . strip_tags($settings['caption']);
                }
                if (!empty($settings['alt']) && empty($settings['caption'])) {
                    $parts[] = 'Alt: ' . strip_tags($settings['alt']);
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'testimonial':
                $parts = array();
                if (!empty($settings['testimonial_content'])) {
                    $parts[] = wp_strip_all_tags($settings['testimonial_content']);
                }
                if (!empty($settings['testimonial_name'])) {
                    $parts[] = '— ' . strip_tags($settings['testimonial_name']);
                }
                if (!empty($settings['testimonial_job'])) {
                    $parts[] = strip_tags($settings['testimonial_job']);
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'icon-box':
            case 'image-box':
                $parts = array();
                if (!empty($settings['title_text'])) {
                    $parts[] = strip_tags($settings['title_text']);
                }
                if (!empty($settings['description_text'])) {
                    $parts[] = wp_strip_all_tags($settings['description_text']);
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'accordion':
            case 'toggle':
                $parts = array();
                if (!empty($settings['tabs']) && is_array($settings['tabs'])) {
                    foreach ($settings['tabs'] as $tab) {
                        if (!empty($tab['tab_title'])) {
                            $parts[] = strip_tags($tab['tab_title']);
                        }
                        if (!empty($tab['tab_content'])) {
                            $parts[] = wp_strip_all_tags($tab['tab_content']);
                        }
                    }
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'tabs':
                $parts = array();
                if (!empty($settings['tabs']) && is_array($settings['tabs'])) {
                    foreach ($settings['tabs'] as $tab) {
                        if (!empty($tab['tab_title'])) {
                            $parts[] = 'Tab: ' . strip_tags($tab['tab_title']);
                        }
                        if (!empty($tab['tab_content'])) {
                            $parts[] = wp_strip_all_tags($tab['tab_content']);
                        }
                    }
                }
                $text_content = implode("\n", $parts);
                break;
                
            case 'html':
                if (!empty($settings['html'])) {
                    $text_content = wp_strip_all_tags($settings['html']);
                }
                break;
                
            case 'shortcode':
                if (!empty($settings['shortcode'])) {
                    // Process shortcode and extract text
                    $shortcode_output = do_shortcode($settings['shortcode']);
                    $text_content = wp_strip_all_tags($shortcode_output);
                }
                break;
                
            case 'spacer':
            case 'divider':
                // Skip spacers and dividers
                return '';
                
            default:
                // For unknown widgets, try to extract any text-like settings
                $text_fields = array('title', 'text', 'content', 'description', 'caption');
                $parts = array();
                foreach ($text_fields as $field) {
                    if (!empty($settings[$field])) {
                        $parts[] = wp_strip_all_tags($settings[$field]);
                    }
                }
                $text_content = implode("\n", $parts);
                break;
        }
        
        // Clean up whitespace
        if (!empty($text_content)) {
            $text_content = preg_replace('/\s+/', ' ', $text_content);
            $text_content = trim($text_content);
        }
        
        return $text_content;
    }
    
    /**
     * AJAX handler to refresh redshift data
     */
    public function ajax_refresh_redshift_data() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'hurricane_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Extract fresh data
        $frontend_text_content = $this->extract_elementor_frontend_text($post_id);
        
        // Limit length for display if too long
        if (strlen($frontend_text_content) > 10000) {
            $frontend_text_content = substr($frontend_text_content, 0, 10000) . "\n\n... [Content truncated at 10,000 characters]";
        }
        
        wp_send_json_success(array('content' => $frontend_text_content));
    }
    
    /**
     * Add Stellar Chamber element above the title bar
     */
    public function add_stellar_chamber($post) {
        // Only show on post and page edit screens
        if (!in_array($post->post_type, array('post', 'page'))) {
            return;
        }
        ?>
        <div class="snefuru-stellar-chamber">
            <div class="snefuru-stellar-chamber-header" style="display: flex; align-items: center; justify-content: flex-start;">
                <!-- Hurricane Editor Section -->
                <div style="display: inline-block; width: fit-content; margin-right: 30px; border-bottom: 2px solid white; padding-bottom: 8px;">
                    <div style="display: flex; align-items: center;">
                        <!-- Hurricane Logo SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 100 100" style="margin-right: 10px;">
                            <!-- Hurricane spiral -->
                            <g fill="none" stroke="white" stroke-width="3">
                                <!-- Outer spiral -->
                                <path d="M50,20 Q70,30 65,50 Q60,70 40,65 Q20,60 25,40 Q30,20 50,25" opacity="0.8"/>
                                <!-- Middle spiral -->
                                <path d="M50,30 Q60,35 58,50 Q55,65 42,62 Q30,58 32,45 Q35,32 50,35" opacity="0.6"/>
                                <!-- Inner spiral -->
                                <path d="M50,40 Q55,42 54,50 Q52,58 46,57 Q40,55 41,49 Q42,43 50,44" opacity="0.4"/>
                                <!-- Center eye -->
                                <circle cx="50" cy="50" r="3" fill="white"/>
                            </g>
                            <!-- Wind lines -->
                            <g stroke="white" stroke-width="2" opacity="0.6">
                                <path d="M15,25 Q25,22 20,28" fill="none"/>
                                <path d="M80,75 Q90,72 85,78" fill="none"/>
                                <path d="M20,75 Q30,78 25,72" fill="none"/>
                                <path d="M85,25 Q75,28 80,22" fill="none"/>
                            </g>
                        </svg>
                        <span style="color: white; font-size: 28px; font-weight: bold; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">Hurricane Editor</span>
                    </div>
                </div>
                
                <div style="display: flex; flex-direction: column;">
                    <div class="beamray_banner1">
                        <svg class="stellar-galactic-logo" viewBox="0 0 24 24" fill="currentColor" style="width: 8px; height: 8px;">
                            <circle cx="12" cy="12" r="10" stroke="white" stroke-width="0.5" fill="none"/>
                            <circle cx="12" cy="12" r="6" stroke="white" stroke-width="0.5" fill="none"/>
                            <circle cx="12" cy="12" r="2" fill="white"/>
                            <g stroke="white" stroke-width="0.3" fill="none">
                                <path d="M2,12 Q6,8 12,12 Q18,16 22,12"/>
                                <path d="M12,2 Q8,6 12,12 Q16,18 12,22"/>
                                <path d="M4.9,4.9 Q8.5,8.5 12,12 Q15.5,15.5 19.1,19.1"/>
                                <path d="M19.1,4.9 Q15.5,8.5 12,12 Q8.5,15.5 4.9,19.1"/>
                            </g>
                            <circle cx="6" cy="8" r="0.8" fill="white" opacity="0.8"/>
                            <circle cx="18" cy="6" r="0.6" fill="white" opacity="0.6"/>
                            <circle cx="16" cy="16" r="0.7" fill="white" opacity="0.7"/>
                            <circle cx="8" cy="18" r="0.5" fill="white" opacity="0.5"/>
                        </svg>
                        Stellar Chamber
                    </div>
                    <span style="color: white; font-size: 16px; font-weight: bold;">top_bar_area</span>
                </div>
                <svg class="snefuru-rocket-icon" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 100 100" style="display: inline-block; margin-left: 10px; transform: rotate(-30deg) scaleX(-1);">
                    <!-- Rocket body -->
                    <path d="M50 10 Q60 20 60 40 L60 55 Q50 65 40 55 L40 40 Q40 20 50 10" fill="white" stroke="#333" stroke-width="2"/>
                    <!-- Rocket tip -->
                    <path d="M50 10 Q55 5 50 0 Q45 5 50 10" fill="#333"/>
                    <!-- Rocket window -->
                    <circle cx="50" cy="30" r="6" fill="#87ceeb" stroke="#333" stroke-width="1"/>
                    <!-- Rocket fins -->
                    <path d="M40 45 L30 55 L35 50 L40 50 Z" fill="#666" stroke="#333" stroke-width="1"/>
                    <path d="M60 45 L70 55 L65 50 L60 50 Z" fill="#666" stroke="#333" stroke-width="1"/>
                    <!-- Fire/flames -->
                    <g class="rocket-flames">
                        <path d="M45 55 Q42 65 43 70 Q45 68 47 70 Q48 65 45 55" fill="#ff9500" opacity="0.9">
                            <animate attributeName="d" 
                                values="M45 55 Q42 65 43 70 Q45 68 47 70 Q48 65 45 55;
                                        M45 55 Q41 66 44 72 Q46 69 48 72 Q49 66 45 55;
                                        M45 55 Q42 65 43 70 Q45 68 47 70 Q48 65 45 55"
                                dur="0.3s" repeatCount="indefinite"/>
                        </path>
                        <path d="M50 55 Q48 68 50 75 Q52 68 50 55" fill="#ff4500" opacity="0.8">
                            <animate attributeName="d" 
                                values="M50 55 Q48 68 50 75 Q52 68 50 55;
                                        M50 55 Q47 70 50 78 Q53 70 50 55;
                                        M50 55 Q48 68 50 75 Q52 68 50 55"
                                dur="0.4s" repeatCount="indefinite"/>
                        </path>
                        <path d="M55 55 Q58 65 57 70 Q55 68 53 70 Q52 65 55 55" fill="#ff9500" opacity="0.9">
                            <animate attributeName="d" 
                                values="M55 55 Q58 65 57 70 Q55 68 53 70 Q52 65 55 55;
                                        M55 55 Q59 66 56 72 Q54 69 52 72 Q51 66 55 55;
                                        M55 55 Q58 65 57 70 Q55 68 53 70 Q52 65 55 55"
                                dur="0.35s" repeatCount="indefinite"/>
                        </path>
                    </g>
                </svg>
                
                <!-- Sitespren Base Display -->
                <div style="display: flex; align-items: center; margin-left: 20px;">
                    <span style="color: white; font-size: 16px; font-weight: bold; margin-right: 10px;">sitespren_base</span>
                    <span style="color: white; font-size: 16px; font-weight: bold;">
                        <?php 
                        global $wpdb;
                        $sitespren_base_value = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren WHERE wppma_id = 1");
                        echo esc_html($sitespren_base_value ? $sitespren_base_value : 'No data');
                        ?>
                    </span>
                </div>
                
                <!-- Driggs Revenue Goal Display -->
                <div style="display: flex; align-items: center; margin-left: 20px;">
                    <span style="color: white; font-size: 16px; font-weight: bold; margin-right: 10px;">driggs_revenue_goal</span>
                    <span style="color: white; font-size: 16px; font-weight: bold;">
                        <?php 
                        global $wpdb;
                        $driggs_revenue = $wpdb->get_var("SELECT driggs_revenue_goal FROM {$wpdb->prefix}zen_sitespren LIMIT 1");
                        echo '$' . esc_html($driggs_revenue ? number_format($driggs_revenue, 0) : '0') . '<span style="color: white; font-weight: bold;">/m</span>';
                        ?>
                    </span>
                </div>
                
                <?php
                // Get the sitespren_base domain from the single-row zen_sitespren table
                global $wpdb;
                $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren LIMIT 1");
                if (empty($sitespren_base)) {
                    $sitespren_base = 'example.com'; // fallback if no data found
                }
                $drom_url = 'http://localhost:3000/drom?activefilterchamber=daylight&sitesentered=' . urlencode($sitespren_base);
                $sitejar4_url = 'http://localhost:3000/sitejar4?sitesentered=' . urlencode($sitespren_base);
                ?>
                
                <!-- Pendulum Screen Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 15px;">
                    <a href="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" 
                       style="background: #000000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        pendulum screen
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo esc_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                            style="background: #000000; color: white; border: none; padding: 8px 4px; margin-left: 2px; border-radius: 0 4px 4px 0; cursor: pointer; width: 10px; font-size: 12px;"
                            title="Copy current page URL">
                        📋
                    </button>
                </div>
                
                <!-- Driggsman Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 15px;">
                    <a href="<?php echo esc_url($drom_url); ?>" 
                       target="_blank" 
                       style="background: #800000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        open /drom
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo esc_url($drom_url); ?>"
                            style="background: #800000; color: white; border: none; padding: 8px 4px; margin-left: 2px; border-radius: 0 4px 4px 0; cursor: pointer; width: 10px; font-size: 12px;"
                            title="Copy drom URL">
                        📋
                    </button>
                </div>
                
                <!-- SiteJar4 Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 15px;">
                    <a href="<?php echo esc_url($sitejar4_url); ?>" 
                       target="_blank" 
                       style="background: #800000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        open /sitejar4
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo esc_url($sitejar4_url); ?>"
                            style="background: #800000; color: white; border: none; padding: 8px 4px; margin-left: 2px; border-radius: 0 4px 4px 0; cursor: pointer; width: 10px; font-size: 12px;"
                            title="Copy sitejar4 URL">
                        📋
                    </button>
                </div>
                
                <!-- Elementor Editor Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 15px;">
                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=elementor'); ?>" 
                       target="_blank" 
                       style="background: #800000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        elementor screen
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo admin_url('post.php?post=' . $post->ID . '&action=elementor'); ?>"
                            style="background: #800000; color: white; border: none; padding: 8px 4px; margin-left: 2px; border-radius: 0 4px 4px 0; cursor: pointer; width: 10px; font-size: 12px;"
                            title="Copy elementor editor URL">
                        📋
                    </button>
                </div>
                
                <!-- Live Frontend Screen Button with Copy -->
                <div style="display: flex; align-items: center; margin-left: 15px;">
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" 
                       target="_blank" 
                       style="background: #800000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px 0 0 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                        livefrontend screen
                    </a>
                    <button type="button" 
                            class="snefuru-copy-btn-right snefuru-locations-copy-btn" 
                            data-copy-url="<?php echo esc_url(get_permalink($post->ID)); ?>"
                            style="background: #800000; color: white; border: none; padding: 8px 4px; margin-left: 0; border-radius: 0 4px 4px 0; cursor: pointer; width: 20px; font-size: 12px;"
                            title="Copy live frontend URL">
                        📋
                    </button>
                </div>
                
                <!-- Minimize/Expand Button -->
                <div style="margin-left: 15px; border: 1px solid rgba(255,255,255,0.3); border-radius: 4px; background: rgba(0,0,0,0.1); display: inline-flex; align-items: center; justify-content: center;">
                    <button type="button" 
                            id="stellar-minimize-btn"
                            style="background: transparent; color: white; border: none; padding: 8px 10px; cursor: pointer; font-size: 16px; line-height: 1; display: flex; align-items: center; justify-content: center;"
                            title="Minimize/Expand Stellar Chamber">
                        <span id="stellar-minimize-arrow" style="display: inline-block; transition: transform 0.3s ease;">▼</span>
                    </button>
                </div>
            </div>
            
            <!-- Collapsible Content Container -->
            <div id="stellar-collapsible-content" style="transition: all 0.3s ease;">
            <div class="snefuru-stellar-tabs">
                <div class="snefuru-stellar-tab-navigation">
                    <button type="button" class="snefuru-stellar-tab-button active" data-tab="elicitor">
                        Elementor Elicitor
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="elementor">
                        Elementor Deployer
                    </button>
                    <div class="snefuru-stellar-tab-separator" style="width: 6px; background: #000; height: 40px; margin: 0 8px; border-radius: 2px; pointer-events: none; display: block; z-index: 10; opacity: 1; position: relative;"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="gutenberg">
                        Gutenberg Elicitor
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="gut-deployer">
                        Gut. Deployer
                    </button>
                    <div class="snefuru-stellar-tab-separator" style="width: 6px; background: #000; height: 40px; margin: 0 8px; border-radius: 2px; pointer-events: none; display: block; z-index: 10; opacity: 1; position: relative;"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="nimble">
                        Nimble Elicitor
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="nimble-deployer">
                        Nimble Deployer
                    </button>
                    <div class="snefuru-stellar-tab-separator" style="width: 6px; background: #000; height: 40px; margin: 0 8px; border-radius: 2px; pointer-events: none; display: block; z-index: 10; opacity: 1; position: relative;"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="image-freeway">
                        Image Elicitor
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="image-deployer">
                        Image Deployer
                    </button>
                    <div class="snefuru-stellar-tab-separator"></div>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="other">
                        KenliSidebarLinks
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="kpages-schema">
                        Asteroid Support
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="pantheon-table">
                        Pantheon Table
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="duplicate-kpage">
                        Duplicate Page
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="driggs">
                        Driggs
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="services">
                        Services
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="locations">
                        Locations
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="swipe15">
                        swipe15
                    </button>
                    <button type="button" class="snefuru-stellar-tab-button" data-tab="gbp">
                        GBP
                    </button>
                </div>
                <div class="snefuru-stellar-tab-content">
                    <div class="snefuru-stellar-tab-panel active" data-panel="elicitor">
                        <?php
                        // Generate header303 content
                        $post_id = $post->ID;
                        $post_title = get_the_title($post_id);
                        $post_status = get_post_status($post_id);
                        $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
                        
                        // Format the header303 content with two lines
                        $header303_line1 = sprintf(
                            'wp_posts.post_id = %d / %s / %s / %s',
                            $post_id,
                            $post_title,
                            $post_status,
                            $edit_link
                        );
                        $header303_content = $header303_line1 . "\nBELOW";
                        
                        // Get Elementor data for this post
                        $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                        $formatted_data = '';
                        
                        if (!empty($elementor_data)) {
                            // Decode and pretty print the JSON for better readability
                            $decoded = json_decode($elementor_data, true);
                            if ($decoded !== null) {
                                $formatted_data = json_encode($decoded, JSON_PRETTY_PRINT);
                            } else {
                                $formatted_data = $elementor_data;
                            }
                        }
                        
                        // Static mapping text for header303_db_mapping
                        $db_mapping_text = "wp_posts.ID / wp_posts.post_title / wp_posts.post_status / admin_url('post.php?post=\$ID&action=edit')\n\nDatabase field mappings:\n- Post ID: wp_posts.ID (bigint unsigned, primary key)\n- Post Title: wp_posts.post_title (text field)\n- Post Status: wp_posts.post_status (varchar, values: publish/draft/pending/private/trash/auto-draft/inherit)\n- Edit Link: Dynamically generated using WordPress admin_url() function with wp_posts.ID";
                        ?>
                        
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Replete Instance Wrapper -->
                                <div class="snefuru-replete-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 20px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">replete_instance</span>
                                    <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                    
                                    <!-- Copy All Instances Container -->
                                    <div class="snefuru-copy-all-instances-container">
                                        <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">copy all instances</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="copy-all-instances-textbox" 
                                                class="snefuru-header303-db-mapping-textbox" 
                                                readonly
                                                style="height: 200px; flex: 1;"
                                            ><?php 
                                            // Combine all three instances into one text
                                            $all_instances_text = $db_mapping_text . "\n\n———————————————————————\n\n" . $header303_content . "\n\n———————————————————————\n\n" . $formatted_data;
                                            echo esc_textarea($all_instances_text); 
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="copy-all-instances-textbox" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instance 1 Wrapper: Header303 DB Mapping -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-header303-db-mapping-container">
                                        <span class="snefuru-header303-db-mapping-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">header303_db_mapping</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="header303-db-mapping-textbox" 
                                                class="snefuru-header303-db-mapping-textbox" 
                                                readonly
                                                style="flex: 1;"
                                            ><?php echo esc_textarea($db_mapping_text); ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="header303-db-mapping-textbox" style="height: 150px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instance 2 Wrapper: Header303 -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-header303-container">
                                        <span class="snefuru-header303-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">header303_filled</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="header303-textbox" 
                                                class="snefuru-header303-textbox" 
                                                readonly
                                                style="flex: 1;"
                                            ><?php echo esc_textarea($header303_content); ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="header303-textbox" style="height: 100px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instance 3 Wrapper: Elementor Data -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-elementor-data-container">
                                        <span class="snefuru-elementor-data-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">_elementor_data</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="elementor-data-textbox" 
                                                class="snefuru-elementor-data-textbox" 
                                                readonly
                                                placeholder="No Elementor data found for this page"
                                                style="flex: 1;"
                                            ><?php echo esc_textarea($formatted_data); ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="elementor-data-textbox" style="height: 100px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Operation Redshift Label -->
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #333;">operation redshift: get all text content output on frontend</span>
                                
                                <!-- Refresh Button -->
                                <button type="button" 
                                        id="refresh-redshift-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="background: #007cba; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; margin-bottom: 15px;">
                                    refresh redshift data
                                </button>
                                
                                <!-- Instance 1: Frontend Text Content -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation redshift - format 1</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="frontend-content-textbox" 
                                                class="snefuru-frontend-content-textbox" 
                                                readonly
                                                placeholder="Frontend text content will be displayed here"
                                                style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract frontend text content using new Elementor data parsing approach
                                            $frontend_text_content = $this->extract_elementor_frontend_text($post->ID);
                                            
                                            // Limit length for display if too long
                                            if (strlen($frontend_text_content) > 10000) {
                                                $frontend_text_content = substr($frontend_text_content, 0, 10000) . "\n\n... [Content truncated at 10,000 characters]";
                                            }
                                            
                                            echo esc_textarea($frontend_text_content);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="frontend-content-textbox" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Operation Blueshift Label -->
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #333;">operation blueshift: get all text content output on frontend</span>
                                
                                <!-- Refresh Button -->
                                <button type="button" 
                                        id="refresh-blueshift-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="background: #007cba; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; margin-bottom: 15px;">
                                    refresh blueshift data
                                </button>
                                
                                <!-- Instance 1: Frontend Text Content -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation blueshift - format 1</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="blueshift-content-textbox" 
                                                class="snefuru-blueshift-content-textbox" 
                                                readonly
                                                placeholder="Frontend text content will be displayed here"
                                                style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract Blueshift content using new approach
                                            $blueshift_content = $this->blueshift->extract_elementor_blueshift_content($post->ID);
                                            
                                            // Limit length for display if too long
                                            if (strlen($blueshift_content) > 50000) {
                                                $blueshift_content = substr($blueshift_content, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                            }
                                            
                                            echo esc_textarea($blueshift_content);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instance 2: Blueshift Format 2 -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation blueshift - format 2</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="blueshift-content-textbox-2" 
                                                class="snefuru-blueshift-content-textbox" 
                                                readonly
                                                placeholder="Frontend text content will be displayed here"
                                                style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract Blueshift content with numbering for format 2
                                            $blueshift_content_format2 = $this->blueshift->extract_elementor_blueshift_content_format2($post->ID);
                                            
                                            // Limit length for display if too long
                                            if (strlen($blueshift_content_format2) > 50000) {
                                                $blueshift_content_format2 = substr($blueshift_content_format2, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                            }
                                            
                                            echo esc_textarea($blueshift_content_format2);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox-2" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Instance 3: Blueshift Format 3 -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-frontend-content-container">
                                        <span class="snefuru-frontend-content-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation blueshift - format 3</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="blueshift-content-textbox-3" 
                                                class="snefuru-blueshift-content-textbox" 
                                                readonly
                                                placeholder="Frontend text content will be displayed here"
                                                style="flex: 1; height: 200px; font-family: monospace; font-size: 12px; line-height: 1.4;"
                                            ><?php 
                                            // Extract Blueshift content with CSS classes and IDs for format 3
                                            $blueshift_content_format3 = $this->blueshift->extract_elementor_blueshift_content_format3($post->ID);
                                            
                                            // Limit length for display if too long
                                            if (strlen($blueshift_content_format3) > 50000) {
                                                $blueshift_content_format3 = substr($blueshift_content_format3, 0, 50000) . "\n\n... [Content truncated at 50,000 characters]";
                                            }
                                            
                                            echo esc_textarea($blueshift_content_format3);
                                            ?></textarea>
                                            <button type="button" class="snefuru-copy-btn-right" data-target="blueshift-content-textbox-3" style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="elementor">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Zeeprex Submit Section -->
                                <div class="snefuru-zeeprex-section">
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">zeeprex_submit</span>
                                    
                                    <textarea 
                                        id="snefuru-zeeprex-content" 
                                        placeholder="Paste your content here. Make sure your codes are preceded by a '#' symbol (e.g., #y_hero1_heading)"
                                        style="width: 100%; height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 13px; margin-bottom: 10px;"
                                    ></textarea>
                                    
                                    <button 
                                        type="button" 
                                        id="snefuru-inject-content-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="background: #800000; color: #fff; font-weight: bold; text-transform: lowercase; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 15px;"
                                    >
                                        run function_inject_content_2
                                    </button>
                                    
                                    <div id="snefuru-inject-result" style="margin-top: 10px; padding: 10px; border-radius: 4px; display: none;"></div>
                                    
                                    <div style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin-top: 15px; font-size: 12px; line-height: 1.6;">
                                        <strong>DEVELOPER INFO:</strong> this function was originally cloned from page of<br>
                                        https://(insertdomainhere.com)/wp-admin/admin.php?page=zurkoscreen4 on 2025__08_10<br>
                                        it was cloned from the deprecated Zurkovich wp plugin<br>
                                        <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ddd;">
                                        <strong>note to claude code:</strong> please make sure this function works identically when user submits content<br><br>
                                        but the "select a page" dropdown is not needed - that's always the current page / post that the user is viewing the active screen of (the same as the post id in the url like https://8mako.ksit.me/wp-admin/post.php?post=666&action=edit)
                                    </div>
                                </div>
                                
                                <script type="text/javascript">
                                jQuery(document).ready(function($) {
                                    $('#snefuru-inject-content-btn').on('click', function() {
                                        var content = $('#snefuru-zeeprex-content').val();
                                        var postId = $(this).data('post-id');
                                        var $btn = $(this);
                                        var $result = $('#snefuru-inject-result');
                                        
                                        if (!content.trim()) {
                                            $result.removeClass('success error').addClass('error');
                                            $result.html('<strong>Error:</strong> Please enter content to inject.');
                                            $result.show();
                                            return;
                                        }
                                        
                                        // Disable button and show processing
                                        $btn.prop('disabled', true).text('processing...');
                                        $result.hide();
                                        
                                        // Make AJAX call
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'snefuru_inject_content',
                                                post_id: postId,
                                                zeeprex_content: content,
                                                nonce: '<?php echo wp_create_nonce('snefuru_inject_content_nonce'); ?>'
                                            },
                                            success: function(response) {
                                                if (response.success) {
                                                    $result.removeClass('error').css({
                                                        'background': '#d4edda',
                                                        'color': '#155724',
                                                        'border': '1px solid #c3e6cb'
                                                    });
                                                    $result.html('<strong>Success:</strong> ' + response.data.message);
                                                } else {
                                                    $result.removeClass('success').css({
                                                        'background': '#f8d7da',
                                                        'color': '#721c24',
                                                        'border': '1px solid #f5c6cb'
                                                    });
                                                    $result.html('<strong>Error:</strong> ' + response.data);
                                                }
                                                $result.show();
                                            },
                                            error: function() {
                                                $result.removeClass('success').css({
                                                    'background': '#f8d7da',
                                                    'color': '#721c24',
                                                    'border': '1px solid #f5c6cb'
                                                });
                                                $result.html('<strong>Error:</strong> Failed to inject content. Please try again.');
                                                $result.show();
                                            },
                                            complete: function() {
                                                $btn.prop('disabled', false).text('run function_inject_content_2');
                                            }
                                        });
                                    });
                                });
                                </script>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Replex Submit Box -->
                                <div>
                                    <label for="snefuru-replex-content" style="display: block; font-weight: bold; margin-bottom: 5px;">
                                        replex_submit_box (replace codes directly only)
                                    </label>
                                    <textarea 
                                        id="snefuru-replex-content" 
                                        placeholder="Enter content with ##codes to replace directly in text" 
                                        style="width: 100%; height: 150px; font-family: monospace; font-size: 12px; padding: 10px; border: 1px solid #ccc; border-radius: 3px;"
                                    ></textarea>
                                    <button 
                                        type="button" 
                                        id="snefuru-replex-submit-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="margin-top: 10px; background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 3px; cursor: pointer; font-size: 14px;"
                                    >
                                        run function_inject_content_5
                                    </button>
                                    
                                    <!-- Toggle Switch for Auto Post Title Update -->
                                    <div style="margin-top: 15px; display: flex; align-items: center; gap: 10px;">
                                        <label class="snefuru-toggle-switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                            <input type="checkbox" id="snefuru-auto-title-toggle" style="opacity: 0; width: 0; height: 0;">
                                            <span class="snefuru-toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; transition: .3s; border-radius: 24px;"></span>
                                        </label>
                                        <span style="font-size: 12px; color: #666;">replace wp_posts.post_title with the first line of text following the double-pound-sign "##" code in the submission</span>
                                    </div>
                                    
                                    <style>
                                    .snefuru-toggle-slider {
                                        background-color: #ccc !important;
                                    }
                                    .snefuru-toggle-slider:before {
                                        position: absolute;
                                        content: "";
                                        height: 18px;
                                        width: 18px;
                                        left: 3px;
                                        bottom: 3px;
                                        background-color: white;
                                        transition: .3s;
                                        border-radius: 50%;
                                    }
                                    #snefuru-auto-title-toggle:checked + .snefuru-toggle-slider {
                                        background-color: #4CAF50 !important;
                                    }
                                    #snefuru-auto-title-toggle:checked + .snefuru-toggle-slider:before {
                                        transform: translateX(26px);
                                    }
                                    </style>
                                    
                                    <div id="snefuru-replex-result" style="margin-top: 10px; padding: 10px; border-radius: 4px; display: none;"></div>
                                </div>
                                
                                <script type="text/javascript">
                                jQuery(document).ready(function($) {
                                    $('#snefuru-replex-submit-btn').on('click', function() {
                                        var content = $('#snefuru-replex-content').val();
                                        var postId = $(this).data('post-id');
                                        var autoUpdateTitle = $('#snefuru-auto-title-toggle').is(':checked');
                                        var $btn = $(this);
                                        var $result = $('#snefuru-replex-result');
                                        
                                        if (!content.trim()) {
                                            $result.removeClass('success error').addClass('error');
                                            $result.html('<strong>Error:</strong> Please enter content to inject.');
                                            $result.show();
                                            return;
                                        }
                                        
                                        $btn.prop('disabled', true).text('Processing...');
                                        $result.hide();
                                        
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'snefuru_inject_replex_content',
                                                post_id: postId,
                                                replex_content: content,
                                                auto_update_title: autoUpdateTitle ? 1 : 0,
                                                nonce: '<?php echo wp_create_nonce('snefuru_inject_replex_content_nonce'); ?>'
                                            },
                                            success: function(response) {
                                                if (response.success) {
                                                    $result.removeClass('error').addClass('success').css({
                                                        'background': '#d4edda',
                                                        'color': '#155724',
                                                        'border': '1px solid #c3e6cb'
                                                    });
                                                    $result.html('<strong>Success:</strong> ' + response.data.message);
                                                } else {
                                                    $result.removeClass('success').addClass('error').css({
                                                        'background': '#f8d7da',
                                                        'color': '#721c24',
                                                        'border': '1px solid #f5c6cb'
                                                    });
                                                    $result.html('<strong>Error:</strong> ' + (response.data ? response.data : 'Unknown error occurred'));
                                                }
                                                $result.show();
                                            },
                                            error: function() {
                                                $result.removeClass('success').css({
                                                    'background': '#f8d7da',
                                                    'color': '#721c24',
                                                    'border': '1px solid #f5c6cb'
                                                });
                                                $result.html('<strong>Error:</strong> Failed to inject replex content. Please try again.');
                                                $result.show();
                                            },
                                            complete: function() {
                                                $btn.prop('disabled', false).text('run function_inject_content_5');
                                            }
                                        });
                                    });
                                });
                                </script>
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Cobalt Submit Box -->
                                <div>
                                    <label for="snefuru-cobalt-content" style="display: block; font-weight: bold; margin-bottom: 5px;">
                                        cobalt_submit_box (target specific widgets by ID)
                                    </label>
                                    <textarea 
                                        id="snefuru-cobalt-content" 
                                        placeholder="Enter Blueshift Format 3 markup with ==widget1, ==widget2, ==item1, etc. to target specific widgets/items for content updates" 
                                        style="width: 100%; height: 150px; font-family: monospace; font-size: 12px; padding: 10px; border: 1px solid #ccc; border-radius: 3px;"
                                    ></textarea>
                                    <button 
                                        type="button" 
                                        id="snefuru-cobalt-submit-btn"
                                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                                        style="margin-top: 10px; background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 3px; cursor: pointer; font-size: 14px;"
                                    >
                                        run cobalt_function_inject_content
                                    </button>
                                    
                                    <!-- Toggle Switch for Auto Post Title Update -->
                                    <div style="margin-top: 15px; display: flex; align-items: center; gap: 10px;">
                                        <label class="snefuru-toggle-switch-cobalt" style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                            <input type="checkbox" id="snefuru-auto-title-toggle-cobalt" style="opacity: 0; width: 0; height: 0;">
                                            <span class="snefuru-toggle-slider-cobalt" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; transition: .3s; border-radius: 24px;"></span>
                                        </label>
                                        <span style="font-size: 12px; color: #666;">replace wp_posts.post_title with the first line of content from the first updated widget in the submission</span>
                                    </div>
                                    
                                    <style>
                                    .snefuru-toggle-slider-cobalt {
                                        background-color: #ccc !important;
                                    }
                                    .snefuru-toggle-slider-cobalt:before {
                                        position: absolute;
                                        content: "";
                                        height: 18px;
                                        width: 18px;
                                        left: 3px;
                                        bottom: 3px;
                                        background-color: white;
                                        transition: .3s;
                                        border-radius: 50%;
                                    }
                                    #snefuru-auto-title-toggle-cobalt:checked + .snefuru-toggle-slider-cobalt {
                                        background-color: #4CAF50 !important;
                                    }
                                    #snefuru-auto-title-toggle-cobalt:checked + .snefuru-toggle-slider-cobalt:before {
                                        transform: translateX(26px);
                                    }
                                    </style>
                                    
                                    <div id="snefuru-cobalt-result" style="margin-top: 10px; padding: 10px; border-radius: 4px; display: none;"></div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="gutenberg">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="gut-deployer">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="nimble">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="nimble-deployer">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="image-freeway">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Operation Rover Instance Wrapper -->
                                <div class="snefuru-instance-wrapper" style="border: 1px solid black; padding: 10px; margin-bottom: 15px;">
                                    <div class="snefuru-rover-container">
                                        <span class="snefuru-rover-label" style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">operation rover: get all image grains from page and display in json</span>
                                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                                            <textarea 
                                                id="rover-images-textbox" 
                                                class="snefuru-rover-textbox" 
                                                readonly
                                                placeholder="Image data will be displayed here in JSON format"
                                                style="flex: 1; height: 200px; padding: 10px; border: 2px solid #e0e5eb; border-radius: 4px; font-family: monospace; font-size: 12px; line-height: 1.4; resize: vertical;"
                                            ></textarea>
                                            <button type="button" 
                                                    class="snefuru-copy-btn-right" 
                                                    data-target="rover-images-textbox" 
                                                    style="height: 200px; padding: 8px 12px; background: linear-gradient(135deg, #3582c4 0%, #2271b1 100%); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; writing-mode: vertical-rl; text-orientation: mixed;">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="image-deployer">
                        <!-- Column Container Wrapper -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Tebnar2 Raw Button -->
                                <a href="http://localhost:3000/bin34/tebnar2" target="_blank" class="button" style="display: inline-block; padding: 8px 16px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
                                    go to /tebnar2 raw
                                </a>
                                
                                <!-- Hudson ImgPlanBatch ID Section -->
                                <div style="margin-top: 20px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">_zen_orbitposts.hudson_imgplanbatch_id</span>
                                    
                                    <?php
                                    // Get existing hudson_imgplanbatch_id for this post
                                    global $wpdb;
                                    $table_name = $wpdb->prefix . 'zen_orbitposts';
                                    $existing_hudson_id = $wpdb->get_var($wpdb->prepare(
                                        "SELECT hudson_imgplanbatch_id FROM $table_name WHERE rel_wp_post_id = %d",
                                        $post->ID
                                    ));
                                    ?>
                                    <input type="text" 
                                           id="hudson-imgplanbatch-id" 
                                           value="<?php echo esc_attr($existing_hudson_id); ?>"
                                           placeholder="79f1a2d4-ec7e-4f3a-a896-d2e4d0e2d3ba"
                                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 14px; margin-bottom: 10px;"
                                           maxlength="36">
                                    
                                    <button type="button" 
                                            id="save-hudson-imgplanbatch-btn"
                                            class="button button-primary"
                                            style="background: #00a32a; border-color: #00a32a; padding: 8px 16px; font-size: 14px;">
                                        Save
                                    </button>
                                    
                                    <!-- Tebnar2 with Param Button -->
                                    <div style="margin-top: 10px;">
                                        <?php if ($existing_hudson_id): ?>
                                            <a href="http://localhost:3000/bin34/tebnar2?batchid=<?php echo urlencode($existing_hudson_id); ?>" 
                                               target="_blank" 
                                               id="tebnar2-param-btn"
                                               class="button" 
                                               style="display: inline-block; padding: 8px 16px; background: #f0ad4e; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500; border-color: #eea236;">
                                                go to /tebnar2 with param set
                                            </a>
                                        <?php else: ?>
                                            <button type="button" 
                                                    id="tebnar2-param-btn"
                                                    class="button" 
                                                    disabled
                                                    style="padding: 8px 16px; background: #ccc; color: #666; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: not-allowed;"
                                                    title="Save a Hudson ImgPlanBatch ID first to enable this button">
                                                go to /tebnar2 with param set
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <script>
                                jQuery(document).ready(function($) {
                                    $('#save-hudson-imgplanbatch-btn').on('click', function() {
                                        var $btn = $(this);
                                        var hudsonId = $('#hudson-imgplanbatch-id').val().trim();
                                        var postId = <?php echo intval($post->ID); ?>;
                                        
                                        if (!hudsonId) {
                                            alert('Please enter a Hudson ImgPlanBatch ID');
                                            return;
                                        }
                                        
                                        // Basic UUID validation
                                        var uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
                                        if (!uuidRegex.test(hudsonId)) {
                                            alert('Please enter a valid UUID format (e.g., 79f1a2d4-ec7e-4f3a-a896-d2e4d0e2d3ba)');
                                            return;
                                        }
                                        
                                        $btn.prop('disabled', true).text('Saving...');
                                        
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'save_hudson_imgplanbatch_id',
                                                post_id: postId,
                                                hudson_id: hudsonId,
                                                nonce: $('#hurricane-nonce').val()
                                            },
                                            success: function(response) {
                                                if (response.success) {
                                                    alert('Hudson ImgPlanBatch ID saved successfully!');
                                                    
                                                    // Update the tebnar2 param button
                                                    var newUrl = 'http://localhost:3000/bin34/tebnar2?batchid=' + encodeURIComponent(hudsonId);
                                                    var buttonHtml = '<a href="' + newUrl + '" target="_blank" id="tebnar2-param-btn" class="button" style="display: inline-block; padding: 8px 16px; background: #f0ad4e; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500; border-color: #eea236;">go to /tebnar2 with param set</a>';
                                                    $('#tebnar2-param-btn').parent().html(buttonHtml);
                                                } else {
                                                    alert('Error saving ID: ' + (response.data || 'Unknown error'));
                                                }
                                                $btn.prop('disabled', false).text('Save');
                                            },
                                            error: function() {
                                                alert('AJAX error occurred');
                                                $btn.prop('disabled', false).text('Save');
                                            }
                                        });
                                    });
                                });
                                </script>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="other">
                        <h2 style="font-weight: bold; font-size: 16px; margin-bottom: 15px;">Snefuruplin Admin Links</h2>
                        
                        <div style="background: white; border: 1px solid #ddd; border-radius: 5px; padding: 20px;">
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=snefuru-kenli-sidebar-links'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        KenliSidebarLinks
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=snefuru'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        Dashboard
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=snefuru-settings'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        Settings
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=snefuru-logs'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        Logs
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=snefuru-dublish-logs'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        Dublish Logs
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=snefuru-screen4-manage'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        screen 4 - manage
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=snefuruplin-bespoke-css-1'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        CSS Editor
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_locations_mar'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_locations_mar
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_services_mar'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_services_mar
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_driggs_mar'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_driggs_mar
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_service_tags_mar'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_service_tags_mar
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_location_tags_mar'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_location_tags_mar
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_kpages_mar'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_kpages_mar
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_duplicate_mar'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_duplicate_mar
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_horse_class_page'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_horse_class_page
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=document_outlook_aug9'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        document_outlook_aug9
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=dynamic_images_man'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        dynamic_images_man
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_orbit_mar'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #f0f0f0; border-radius: 4px; text-decoration: none; color: #333; transition: background 0.2s;">
                                        rup_orbit_mar
                                    </a>
                                </li>
                                <li style="margin-bottom: 8px;">
                                    <a href="<?php echo admin_url('admin.php?page=rup_sitespren_export'); ?>" 
                                       style="display: inline-block; padding: 8px 12px; background: #4CAF50; border-radius: 4px; text-decoration: none; color: #fff; transition: background 0.2s;">
                                        📊 Sitespren Export
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="driggs">
                        <!-- Link to rup_driggs_mar page -->
                        <div style="margin-bottom: 15px;">
                            <a href="<?php echo admin_url('admin.php?page=rup_driggs_mar'); ?>" 
                               class="button button-secondary" 
                               style="background: #0073aa; color: white; text-decoration: none; padding: 8px 16px; display: inline-block;">
                                Go to the rup_driggs_mar page
                            </a>
                        </div>
                        
                        <h2 style="font-weight: bold; font-size: 16px; margin-bottom: 15px;">driggs field collection 1</h2>
                        
                        <!-- Save Button -->
                        <div style="margin-bottom: 15px;">
                            <button id="save-driggs-btn" class="button button-primary">Save Changes</button>
                        </div>
                        
                        <!-- Driggs Fields Table -->
                        <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                            <div style="overflow-x: auto;">
                                <table id="driggs-stellar-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                    <thead style="background: #f8f9fa;">
                                        <tr>
                                            <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 250px;">Field Name</th>
                                            <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="driggs-stellar-table-body">
                                        <!-- Data will be loaded here via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div id="driggs-stellar-data" 
                             data-nonce="<?php echo wp_create_nonce('rup_driggs_nonce'); ?>"
                             data-initialized="false">
                        </div>
                        
                        <style type="text/css">
                        /* Stellar Toggle Switch Styles */
                        .stellar-driggs-toggle-switch {
                            position: relative;
                            display: inline-block;
                            width: 60px;
                            height: 34px;
                            cursor: pointer;
                        }
                        
                        .stellar-driggs-toggle-switch input[type="checkbox"] {
                            opacity: 0;
                            width: 0;
                            height: 0;
                        }
                        
                        .stellar-driggs-toggle-slider {
                            position: absolute;
                            cursor: pointer;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background-color: #ccc;
                            transition: .4s;
                            border-radius: 34px;
                            border: 1px solid #bbb;
                        }
                        
                        .stellar-driggs-toggle-knob {
                            position: absolute;
                            content: "";
                            height: 26px;
                            width: 26px;
                            left: 4px;
                            bottom: 3px;
                            background-color: white;
                            transition: .4s;
                            border-radius: 50%;
                            border: 1px solid #ddd;
                        }
                        
                        .stellar-driggs-toggle-switch input:checked + .stellar-driggs-toggle-slider {
                            background-color: #2196F3;
                            border-color: #1976D2;
                        }
                        
                        .stellar-driggs-toggle-switch input:checked + .stellar-driggs-toggle-slider .stellar-driggs-toggle-knob {
                            transform: translateX(26px);
                            border-color: #fff;
                        }
                        
                        .stellar-driggs-toggle-switch:hover .stellar-driggs-toggle-slider {
                            background-color: #b3b3b3;
                        }
                        
                        .stellar-driggs-toggle-switch input:checked:hover + .stellar-driggs-toggle-slider {
                            background-color: #1976D2;
                        }
                        
                        /* Editable field styles */
                        .stellareditable-field {
                            cursor: pointer;
                            min-height: 20px;
                            padding: 4px;
                            border-radius: 3px;
                            transition: background-color 0.2s;
                        }
                        
                        .stellareditable-field:hover {
                            background-color: #f0f8ff;
                            border: 1px dashed #2196F3;
                        }
                        
                        #driggs-stellar-table td {
                            vertical-align: middle;
                        }
                        </style>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="services">
                        <h2 class="snefuru-kepler-title">Kepler Services</h2>
                        <?php
                        // Generate the admin URL for rup_services_mar
                        $services_url = admin_url('admin.php?page=rup_services_mar');
                        ?>
                        <div class="snefuru-locations-button-container">
                            <a href="<?php echo esc_url($services_url); ?>" class="snefuru-locations-main-btn">
                                ?page=rup_services_mar
                            </a>
                            <button type="button" class="snefuru-locations-copy-btn" data-copy-url="<?php echo esc_attr($services_url); ?>" title="Copy link to clipboard">
                                📋
                            </button>
                        </div>
                        <!-- Additional services content will go here -->
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="locations">
                        <h2 class="snefuru-kepler-title">Kepler Locations</h2>
                        <?php
                        // Generate the admin URL for rup_locations_mar
                        $locations_url = admin_url('admin.php?page=rup_locations_mar');
                        ?>
                        <div class="snefuru-locations-button-container">
                            <a href="<?php echo esc_url($locations_url); ?>" class="snefuru-locations-main-btn">
                                ?page=rup_locations_mar
                            </a>
                            <button type="button" class="snefuru-locations-copy-btn" data-copy-url="<?php echo esc_attr($locations_url); ?>" title="Copy link to clipboard">
                                📋
                            </button>
                        </div>
                        <!-- Additional locations content will go here -->
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="kpages-schema">
                        <!-- Asteroid Support Content -->
                        <div class="snefuru-denyeep-columns-wrapper" style="display: flex; gap: 15px; margin-top: 10px;">
                            
                            <!-- Denyeep Column Div 1 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 1</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Asteroid Support Content -->
                                <div class="snefuru-asteroid-container" style="margin-bottom: 20px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: lowercase;">asteroid support</span>
                                    
                                    <textarea 
                                        id="asteroid-support-textbox" 
                                        readonly
                                        style="width: 100%; height: 120px; padding: 10px; border: 2px solid #e0e5eb; border-radius: 4px; font-family: monospace; font-size: 14px; line-height: 1.6; resize: vertical; background: #f9f9f9;"
                                    >k_
g_
w_
y_</textarea>
                                    
                                    <span style="display: block; font-size: 16px; font-weight: bold; margin-top: 15px; color: #333;">NoteToSelfKyle:database integration needed</span>
                                </div>
                            </div>
                            
                            <!-- Denyeep Column Div 2 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 2</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                            
                            <!-- Denyeep Column Div 3 -->
                            <div class="snefuru-denyeep-column" style="border: 1px solid black; padding: 10px; flex: 1; min-width: 420px;">
                                <span style="display: block; font-size: 16px; font-weight: bold; margin-bottom: 10px;">denyeep column div 3</span>
                                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ccc;">
                                
                                <!-- Content area left blank for now -->
                            </div>
                            
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="pantheon-table">
                        <div style="padding: 15px;">
                            <h1 style="margin-bottom: 20px; font-size: 18px;">📋 Pantheon Table Management</h1>
                        
                            <!-- Control Bar (mimicking rup_locations_mar style) -->
                            <div style="background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <button type="button" id="stellar-pantheon-process" class="button button-primary" style="font-size: 12px; padding: 8px 12px;">
                                            🚀 Process Selected
                                        </button>
                                        <span id="stellar-pantheon-count" style="color: #666; font-size: 12px;">0 selected</span>
                                    </div>
                                    <div>
                                        <a href="<?php echo admin_url('admin.php?page=rup_pantheon_mar'); ?>" class="button" style="font-size: 12px; padding: 6px 12px; background: #f0ad4e; color: #fff; text-decoration: none; border-color: #eea236;" target="_blank">
                                            📋 Full Admin Page
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Search Controls -->
                                <div style="display: flex; justify-content: flex-end; align-items: center;">
                                    <!-- Search -->
                                    <div style="position: relative;">
                                        <input type="text" id="stellar-pantheon-search" placeholder="Search descriptions..." style="padding: 8px 40px 8px 12px; border: 1px solid #ccc; border-radius: 4px; width: 200px; font-size: 12px;">
                                        <button id="stellar-pantheon-clear" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #ffeb3b; border: none; padding: 4px 6px; font-size: 10px; font-weight: bold; border-radius: 3px; cursor: pointer;">CL</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Main Table (matching rup_locations_mar style) -->
                            <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                                <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
                                    <table id="stellar-pantheon-table" style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                        <thead style="background: #f8f9fa;">
                                            <tr>
                                                <th style="padding: 10px 6px; border: 1px solid #ddd; font-weight: bold; text-align: center; background: #f0f0f0; width: 40px;">
                                                    <input type="checkbox" id="stellar-pantheon-select-all" style="width: 16px; height: 16px;">
                                                </th>
                                                <th style="padding: 10px 6px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 180px; font-size: 11px;">origin_page</th>
                                                <th style="padding: 10px 6px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; font-size: 11px;">description</th>
                                                <th style="padding: 10px 6px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 80px; font-size: 11px;">name</th>
                                                <th style="padding: 10px 6px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 100px; font-size: 11px;">id_code_of_turtle</th>
                                                <th style="padding: 10px 6px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 100px; font-size: 11px;">related_entitites</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr data-type="chen" data-description="open chen page">
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd;">
                                                    <input type="checkbox" class="stellar-pantheon-checkbox" value="chen-1" style="width: 14px; height: 14px;">
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 10px;">
                                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" style="color: #0073aa; text-decoration: none; font-family: monospace;">
                                                        <?php echo home_url(); ?>/wp-admin/post.php?post=<?php echo $post->ID; ?>&action=edit
                                                    </a>
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 11px;">
                                                    <strong style="color: #0073aa;">open chen page</strong>
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                            
                                            <tr data-type="chen" data-description="open chen page">
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd;">
                                                    <input type="checkbox" class="stellar-pantheon-checkbox" value="chen-2" style="width: 14px; height: 14px;">
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 10px;">
                                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" style="color: #0073aa; text-decoration: none; font-family: monospace;">
                                                        <?php echo home_url(); ?>/wp-admin/post.php?post=<?php echo $post->ID; ?>&action=edit
                                                    </a>
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 11px;">
                                                    <strong style="color: #0073aa;">open chen page</strong>
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                            
                                            <tr data-type="chen" data-description="open chen page">
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd;">
                                                    <input type="checkbox" class="stellar-pantheon-checkbox" value="chen-3" style="width: 14px; height: 14px;">
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 10px;">
                                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" style="color: #0073aa; text-decoration: none; font-family: monospace;">
                                                        <?php echo home_url(); ?>/wp-admin/post.php?post=<?php echo $post->ID; ?>&action=edit
                                                    </a>
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 11px;">
                                                    <strong style="color: #0073aa;">open chen page</strong>
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                            
                                            <tr data-type="sitejar4" data-description="open /sitejar4?PARAMETER">
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd;">
                                                    <input type="checkbox" class="stellar-pantheon-checkbox" value="sitejar4-1" style="width: 14px; height: 14px;">
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 10px;">
                                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" style="color: #0073aa; text-decoration: none; font-family: monospace;">
                                                        <?php echo home_url(); ?>/wp-admin/post.php?post=<?php echo $post->ID; ?>&action=edit
                                                    </a>
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 11px;">
                                                    <strong style="color: #0073aa;">open /sitejar4?PARAMETER</strong>
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                            
                                            <tr data-type="drom" data-description="open /drom?PARAMETER">
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd;">
                                                    <input type="checkbox" class="stellar-pantheon-checkbox" value="drom-1" style="width: 14px; height: 14px;">
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 10px;">
                                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" style="color: #0073aa; text-decoration: none; font-family: monospace;">
                                                        <?php echo home_url(); ?>/wp-admin/post.php?post=<?php echo $post->ID; ?>&action=edit
                                                    </a>
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 11px;">
                                                    <strong style="color: #0073aa;">open /drom?PARAMETER</strong>
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                            
                                            <tr data-type="gcjar1" data-description="open /gcjar1?PARAMETER">
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd;">
                                                    <input type="checkbox" class="stellar-pantheon-checkbox" value="gcjar1-1" style="width: 14px; height: 14px;">
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 10px;">
                                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" style="color: #0073aa; text-decoration: none; font-family: monospace;">
                                                        <?php echo home_url(); ?>/wp-admin/post.php?post=<?php echo $post->ID; ?>&action=edit
                                                    </a>
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 11px;">
                                                    <strong style="color: #0073aa;">open /gcjar1?PARAMETER</strong>
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                            
                                            <tr data-type="nwjar1" data-description="open /nwjar1?PARAMETER">
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd;">
                                                    <input type="checkbox" class="stellar-pantheon-checkbox" value="nwjar1-1" style="width: 14px; height: 14px;">
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 10px;">
                                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" style="color: #0073aa; text-decoration: none; font-family: monospace;">
                                                        <?php echo home_url(); ?>/wp-admin/post.php?post=<?php echo $post->ID; ?>&action=edit
                                                    </a>
                                                </td>
                                                <td style="padding: 8px 6px; border: 1px solid #ddd; font-size: 11px;">
                                                    <strong style="color: #0073aa;">open /nwjar1?PARAMETER</strong>
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                                <td style="text-align: center; padding: 8px 6px; border: 1px solid #ddd; color: #999; font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Status Messages -->
                            <div id="stellar-pantheon-messages" style="margin-top: 15px;"></div>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="duplicate-kpage">
                        <div style="text-align: center; padding: 30px 20px;">
                            <h2 style="color: #0073aa; margin-bottom: 25px; font-size: 20px;">Duplicate Current Page/Post</h2>
                            
                            <!-- Duplicate Button Container -->
                            <div class="snefuru-duplicate-button-container" style="display: inline-flex; align-items: center; gap: 0; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0, 115, 170, 0.15); border-radius: 8px;">
                                <button 
                                    type="button" 
                                    id="snefuru-duplicate-page-btn"
                                    data-post-id="<?php echo esc_attr($post->ID); ?>"
                                    style="
                                        background: linear-gradient(135deg, #0073aa 0%, #005177 100%);
                                        color: white;
                                        border: none;
                                        padding: 15px 25px;
                                        font-size: 16px;
                                        font-weight: 600;
                                        border-radius: 8px 0 0 8px;
                                        cursor: pointer;
                                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                        position: relative;
                                        min-height: 54px;
                                        display: flex;
                                        align-items: center;
                                        gap: 8px;
                                        letter-spacing: 0.3px;
                                        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                                    "
                                    onmouseover="this.style.background='linear-gradient(135deg, #005177 0%, #003a52 100%)'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0, 115, 170, 0.25)'"
                                    onmouseout="this.style.background='linear-gradient(135deg, #0073aa 0%, #005177 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 115, 170, 0.15)'"
                                >
                                    <span style="font-size: 18px;">📄</span>
                                    <span>Duplicate This WP Page/Post Now</span>
                                </button>
                                
                                <button 
                                    type="button" 
                                    class="snefuru-copy-duplicate-text-btn" 
                                    title="Copy button text to clipboard"
                                    style="
                                        background: linear-gradient(135deg, #00a32a 0%, #007a1f 100%);
                                        color: white;
                                        border: none;
                                        width: 54px;
                                        height: 54px;
                                        border-radius: 0 8px 8px 0;
                                        cursor: pointer;
                                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-size: 16px;
                                    "
                                    onmouseover="this.style.background='linear-gradient(135deg, #007a1f 0%, #005a17 100%)'; this.style.transform='scale(1.05)'"
                                    onmouseout="this.style.background='linear-gradient(135deg, #00a32a 0%, #007a1f 100%)'; this.style.transform='scale(1)'"
                                >
                                    📋
                                </button>
                            </div>
                            
                            <!-- Go to rup_duplicate_mar Button -->
                            <div style="margin-top: 20px; text-align: center;">
                                <a href="<?php echo admin_url('admin.php?page=rup_duplicate_mar'); ?>" 
                                   target="_blank"
                                   class="snefuru-go-to-duplicate-mar-btn" 
                                   title="Go to bulk duplication page"
                                   style="
                                       display: inline-flex;
                                       align-items: center;
                                       gap: 8px;
                                       background: linear-gradient(135deg, #f5f5dc 0%, #e6e6cd 100%);
                                       color: #4a4a4a;
                                       border: 2px solid #d3d3d3;
                                       border-radius: 8px;
                                       padding: 12px 20px;
                                       font-weight: 600;
                                       text-decoration: none;
                                       font-size: 14px;
                                       cursor: pointer;
                                       transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                       box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                       text-transform: none;
                                       font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                                   "
                                   onmouseover="this.style.background='linear-gradient(135deg, #f0f0d8 0%, #e0e0c8 100%)'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0, 0, 0, 0.15)'"
                                   onmouseout="this.style.background='linear-gradient(135deg, #f5f5dc 0%, #e6e6cd 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.1)'"
                                >
                                    <span style="font-size: 16px;">🔗</span>
                                    <span>go to rup_duplicate_mar -></span>
                                </a>
                                
                                <button 
                                    type="button" 
                                    class="snefuru-copy-duplicate-mar-btn" 
                                    title="Copy link text to clipboard"
                                    style="
                                        background: linear-gradient(135deg, #00a32a 0%, #007a1f 100%);
                                        color: white;
                                        border: none;
                                        border-radius: 6px;
                                        width: 32px;
                                        height: 32px;
                                        margin-left: 8px;
                                        cursor: pointer;
                                        display: inline-flex;
                                        align-items: center;
                                        justify-content: center;
                                        transition: all 0.3s ease;
                                        box-shadow: 0 2px 6px rgba(0, 163, 42, 0.2);
                                    "
                                    onmouseover="this.style.background='linear-gradient(135deg, #00ba37 0%, #008a25 100%)'; this.style.transform='scale(1.05)'"
                                    onmouseout="this.style.background='linear-gradient(135deg, #00a32a 0%, #007a1f 100%)'; this.style.transform='scale(1)'"
                                >
                                    📋
                                </button>
                            </div>
                            
                            <!-- Status Display -->
                            <div id="snefuru-duplicate-status" style="display: none; margin-top: 15px; padding: 15px; border-radius: 8px; max-width: 600px; margin-left: auto; margin-right: auto;"></div>
                            
                            <!-- Result Link -->
                            <div id="snefuru-duplicate-result" style="display: none; margin-top: 20px; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; max-width: 600px; margin-left: auto; margin-right: auto;">
                                <h3 style="color: #155724; margin-top: 0; margin-bottom: 15px;">✅ Page Duplicated Successfully!</h3>
                                <div id="snefuru-duplicate-link-container"></div>
                            </div>
                        </div>
                        
                        <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            // Copy button text functionality
                            $('.snefuru-copy-duplicate-text-btn').on('click', function() {
                                var textToCopy = 'Duplicate This WP Page/Post Now';
                                var $btn = $(this);
                                
                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(textToCopy).then(function() {
                                        $btn.html('✅');
                                        setTimeout(function() {
                                            $btn.html('📋');
                                        }, 2000);
                                        console.log('Button text copied to clipboard');
                                    }).catch(function(err) {
                                        console.error('Clipboard write failed:', err);
                                    });
                                } else {
                                    // Fallback for older browsers
                                    var textarea = document.createElement('textarea');
                                    textarea.value = textToCopy;
                                    textarea.style.position = 'fixed';
                                    textarea.style.opacity = '0';
                                    document.body.appendChild(textarea);
                                    
                                    try {
                                        textarea.select();
                                        textarea.setSelectionRange(0, 99999);
                                        var successful = document.execCommand('copy');
                                        
                                        if (successful) {
                                            $btn.html('✅');
                                            setTimeout(function() {
                                                $btn.html('📋');
                                            }, 2000);
                                            console.log('Button text copied using fallback method');
                                        }
                                    } catch (err) {
                                        console.error('Fallback copy error:', err);
                                    } finally {
                                        document.body.removeChild(textarea);
                                    }
                                }
                            });
                            
                            // Duplicate page functionality
                            $('#snefuru-duplicate-page-btn').on('click', function() {
                                var $btn = $(this);
                                var $status = $('#snefuru-duplicate-status');
                                var $result = $('#snefuru-duplicate-result');
                                var postId = $btn.data('post-id');
                                
                                // Disable button and show processing
                                $btn.prop('disabled', true);
                                $btn.find('span:last-child').text('Duplicating...');
                                $btn.css('cursor', 'not-allowed');
                                
                                // Show status message
                                $status.removeClass('error').css({
                                    'background': '#fff3cd',
                                    'color': '#856404',
                                    'border': '1px solid #ffeaa7'
                                }).html('<strong>⏳ Processing:</strong> Saving current page and creating duplicate...').show();
                                
                                $result.hide();
                                
                                // Make AJAX call
                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'snefuru_duplicate_page',
                                        post_id: postId,
                                        nonce: '<?php echo wp_create_nonce('snefuru_duplicate_page_nonce'); ?>'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            $status.hide();
                                            $result.show();
                                            
                                            var editUrl = response.data.edit_url;
                                            var viewUrl = response.data.view_url;
                                            var title = response.data.duplicate_title;
                                            var postType = '<?php echo get_post_type(); ?>' || 'page';
                                            
                                            var linkHtml = '<div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">' +
                                                '<a href="' + editUrl + '" class="button button-primary" target="_blank" style="text-decoration: none;">' +
                                                '✏️ Edit New ' + (postType === 'post' ? 'Post' : 'Page') + '</a>' +
                                                '<a href="' + viewUrl + '" class="button button-secondary" target="_blank" style="text-decoration: none;">' +
                                                '👁️ View New ' + (postType === 'post' ? 'Post' : 'Page') + '</a>' +
                                                '</div>' +
                                                '<p style="margin-top: 15px; color: #155724; font-weight: 500;">New ' + (postType === 'post' ? 'post' : 'page') + ' title: <strong>"' + title + '"</strong></p>';
                                            
                                            $('#snefuru-duplicate-link-container').html(linkHtml);
                                        } else {
                                            $result.hide();
                                            $status.removeClass('success').css({
                                                'background': '#f8d7da',
                                                'color': '#721c24',
                                                'border': '1px solid #f5c6cb'
                                            }).html('<strong>❌ Error:</strong> ' + response.data).show();
                                        }
                                    },
                                    error: function() {
                                        $result.hide();
                                        $status.removeClass('success').css({
                                            'background': '#f8d7da',
                                            'color': '#721c24',
                                            'border': '1px solid #f5c6cb'
                                        }).html('<strong>❌ Error:</strong> Failed to duplicate page. Please try again.').show();
                                    },
                                    complete: function() {
                                        // Re-enable button
                                        $btn.prop('disabled', false);
                                        $btn.find('span:last-child').text('Duplicate This WP Page/Post Now');
                                        $btn.css('cursor', 'pointer');
                                    }
                                });
                            });
                            
                            // Copy rup_duplicate_mar button text functionality
                            $('.snefuru-copy-duplicate-mar-btn').on('click', function() {
                                var textToCopy = 'go to rup_duplicate_mar ->';
                                var $btn = $(this);
                                
                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(textToCopy).then(function() {
                                        $btn.html('✅');
                                        setTimeout(function() {
                                            $btn.html('📋');
                                        }, 2000);
                                        console.log('rup_duplicate_mar button text copied to clipboard');
                                    }).catch(function(err) {
                                        console.error('Clipboard write failed:', err);
                                    });
                                } else {
                                    // Fallback for older browsers
                                    var textarea = document.createElement('textarea');
                                    textarea.value = textToCopy;
                                    textarea.style.position = 'fixed';
                                    textarea.style.opacity = '0';
                                    document.body.appendChild(textarea);
                                    
                                    try {
                                        textarea.select();
                                        textarea.setSelectionRange(0, 99999);
                                        if (document.execCommand('copy')) {
                                            $btn.html('✅');
                                            setTimeout(function() {
                                                $btn.html('📋');
                                            }, 2000);
                                            console.log('rup_duplicate_mar button text copied using fallback method');
                                        }
                                    } catch (err) {
                                        console.error('Fallback copy error:', err);
                                    } finally {
                                        document.body.removeChild(textarea);
                                    }
                                }
                            });
                        });
                        </script>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="swipe15">
                        <!-- Swipe15 Content Container -->
                        <div class="snefuru-header303-db-mapping-container">
                            <div class="snefuru-header303-db-mapping-header">
                                <span class="snefuru-header303-db-mapping-label">swipe15 content</span>
                                <button type="button" class="snefuru-copy-btn" data-target="swipe15-textbox">
                                    Copy
                                </button>
                            </div>
                            <textarea 
                                id="swipe15-textbox" 
                                class="snefuru-header303-db-mapping-textbox" 
                                readonly
                            >———————————————————————
SPECIAL NOTE TO THE AI MODEL I'M CHATTING WITH:

In the following text content I paste below, you will be seeing the following:

(INSERT DESCRIPTION HERE)

———————————————————————</textarea>
                        </div>
                        
                        <!-- Separator Line -->
                        <hr style="margin: 30px 0; border: 0; border-top: 2px solid #333;">
                        
                        <!-- Quick Stats Section -->
                        <div class="snefuru-quick-stats-container" style="margin-top: 20px;">
                            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333;">Quick Stats For This Kpage</h3>
                            
                            <!-- Content area left blank for now -->
                            <div class="snefuru-quick-stats-content" style="min-height: 100px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                <!-- Stats content will be added here in the future -->
                            </div>
                        </div>
                    </div>
                    <div class="snefuru-stellar-tab-panel" data-panel="gbp">
                        <!-- GBP content will go here -->
                    </div>
                </div>
            </div>
        </div>
        </div> <!-- End of stellar-collapsible-content -->
        
        <!-- Hidden nonce field for AJAX security -->
        <input type="hidden" id="hurricane-nonce" value="<?php echo wp_create_nonce('hurricane_nonce'); ?>" />
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Minimize/Expand Stellar Chamber functionality
            var isStellarMinimized = false;
            
            $('#stellar-minimize-btn').on('click', function() {
                var $content = $('#stellar-collapsible-content');
                var $arrow = $('#stellar-minimize-arrow');
                
                if (isStellarMinimized) {
                    // Expand
                    $content.slideDown(300);
                    $arrow.css('transform', 'rotate(0deg)');
                    isStellarMinimized = false;
                } else {
                    // Minimize
                    $content.slideUp(300);
                    $arrow.css('transform', 'rotate(-90deg)');
                    isStellarMinimized = true;
                }
            });
            
            // Stellar Chamber Pantheon Table functionality
            var stellarSelectedCount = 0;
            
            function updateStellarSelectionCount() {
                stellarSelectedCount = $('.stellar-pantheon-checkbox:checked').length;
                $('#stellar-pantheon-count').text(stellarSelectedCount + ' selected');
                $('#stellar-pantheon-process').prop('disabled', stellarSelectedCount === 0);
            }
            
            // Handle individual checkbox changes
            $(document).on('change', '.stellar-pantheon-checkbox', function() {
                var $row = $(this).closest('tr');
                if ($(this).is(':checked')) {
                    $row.css('background-color', '#e8f4f8');
                } else {
                    $row.css('background-color', '');
                }
                updateStellarSelectionCount();
            });
            
            // Handle select all checkbox
            $('#stellar-pantheon-select-all').change(function() {
                var checked = $(this).is(':checked');
                $('#stellar-pantheon-table tbody tr:visible .stellar-pantheon-checkbox').each(function() {
                    $(this).prop('checked', checked);
                    var $row = $(this).closest('tr');
                    if (checked) {
                        $row.css('background-color', '#e8f4f8');
                    } else {
                        $row.css('background-color', '');
                    }
                });
                updateStellarSelectionCount();
            });
            
            // Search functionality
            $('#stellar-pantheon-search').on('input', function() {
                filterStellarTable();
            });
            
            // Clear search button
            $('#stellar-pantheon-clear').click(function() {
                $('#stellar-pantheon-search').val('');
                filterStellarTable();
            });
            
            // Filter table function
            function filterStellarTable() {
                var searchText = $('#stellar-pantheon-search').val().toLowerCase();
                
                $('#stellar-pantheon-table tbody tr').each(function() {
                    var $row = $(this);
                    var description = $row.attr('data-description').toLowerCase();
                    
                    var matchesSearch = searchText === '' || description.includes(searchText);
                    
                    if (matchesSearch) {
                        $row.show();
                    } else {
                        $row.hide();
                        // Uncheck hidden rows
                        $row.find('.stellar-pantheon-checkbox').prop('checked', false);
                        $row.css('background-color', '');
                    }
                });
                
                updateStellarSelectionCount();
            }
            
            // Handle process selected button
            $('#stellar-pantheon-process').click(function() {
                var selectedItems = [];
                $('.stellar-pantheon-checkbox:checked').each(function() {
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
                var $statusDiv = $('#stellar-pantheon-messages');
                $statusDiv.html('<div style="background: #d4edda; color: #155724; padding: 8px; border: 1px solid #c3e6cb; border-radius: 4px; font-size: 12px;">🔄 Processing ' + selectedItems.length + ' selected items...</div>');
                
                // Simulate processing (replace with actual AJAX call)
                setTimeout(function() {
                    var successHtml = '<div style="background: #d1eddb; color: #155724; padding: 8px; border: 1px solid #c3e6cb; border-radius: 4px; font-size: 12px;">✅ Successfully processed ' + selectedItems.length + ' items:<br>';
                    selectedItems.forEach(function(item) {
                        successHtml += '• ' + item.description + ' (' + item.value + ')<br>';
                    });
                    successHtml += '</div>';
                    $statusDiv.html(successHtml);
                }, 2000);
            });
            
            // Initialize
            updateStellarSelectionCount();
        });
        </script>
        <?php
    }
    
    /**
     * Render Stellar Chamber as standalone component for dioptra page
     */
    public function render_stellar_chamber_standalone($post) {
        // This method simply calls the original add_stellar_chamber
        // The original method has a type check, but we'll bypass it by temporarily
        // modifying the post type if needed
        
        // Store original post type
        $original_post_type = $post->post_type;
        
        // Temporarily set post type to 'post' or 'page' to pass the check
        if (!in_array($post->post_type, array('post', 'page'))) {
            $post->post_type = 'post';
        }
        
        // Call the original method
        $this->add_stellar_chamber($post);
        
        // Restore original post type
        $post->post_type = $original_post_type;
    }
    
    /**
     * Add Hurricane metabox to post/page edit screens
     */
    public function add_hurricane_metabox() {
        // Get current screen
        $screen = get_current_screen();
        
        // Only add to post and page edit screens
        if ($screen && in_array($screen->base, array('post')) && in_array($screen->post_type, array('post', 'page'))) {
            add_meta_box(
                'snefuru-hurricane',
                'Hurricane',
                array($this, 'render_hurricane_metabox'),
                array('post', 'page'),
                'side', // This places it in the sidebar (right side)
                'high'  // This places it at the top of the sidebar
            );
        }
    }
    
    /**
     * Render the Hurricane metabox content
     */
    public function render_hurricane_metabox($post) {
        ?>
        <div class="snefuru-hurricane-container">
            <div class="snefuru-hurricane-content">
                Hurricane
            </div>
            <div class="snefuru-hurricane-controls">
                <button type="button" class="button button-primary snefuru-lightning-popup-btn" onclick="window.snefuruOpenLightningPopup()">
                    ⚡ Lightning Popup
                </button>
            </div>
        </div>
        
        <!-- Lightning Popup Modal -->
        <div id="snefuru-lightning-popup" class="snefuru-popup-overlay" style="display: none;">
            <div class="snefuru-popup-container">
                <div class="snefuru-popup-header">
                    <h2 class="snefuru-popup-title">Lightning Popup</h2>
                    <button type="button" class="snefuru-popup-close" onclick="window.snefuruCloseLightningPopup()">&times;</button>
                </div>
                <div class="snefuru-popup-content">
                    <!-- Future content will go here -->
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue Hurricane CSS and JS assets
     */
    public function enqueue_hurricane_assets($hook) {
        // Only load on post/page edit screens
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        // Get current screen to check post type
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, array('post', 'page'))) {
            return;
        }
        
        wp_enqueue_style(
            'snefuru-hurricane-css',
            SNEFURU_PLUGIN_URL . 'hurricane/assets/hurricane.css',
            array(),
            SNEFURU_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'snefuru-hurricane-js',
            SNEFURU_PLUGIN_URL . 'hurricane/assets/hurricane.js',
            array('jquery'),
            SNEFURU_PLUGIN_VERSION,
            true
        );
        
        // Add inline script to test if assets are loading
        wp_add_inline_script('snefuru-hurricane-js', '
            console.log("Hurricane JS loaded successfully");
            console.log("jQuery version:", jQuery.fn.jquery);
        ');
        
        // Localize script with AJAX URL and nonce
        wp_localize_script('snefuru-hurricane-js', 'snefuruHurricane', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('snefuru_hurricane_nonce'),
            'post_id' => get_the_ID()
        ));
    }
}