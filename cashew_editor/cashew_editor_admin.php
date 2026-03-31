<?php
if (!defined('ABSPATH')) {
    exit;
}

class CashewEditorAdmin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_head', array($this, 'suppress_notices'));
        add_action('admin_notices', array($this, 'suppress_admin_notices'), 1);
        add_action('all_admin_notices', array($this, 'suppress_admin_notices'), 1);
        add_filter('admin_title', array($this, 'fix_admin_title'), 10, 2);
    }

    public function add_admin_menu() {
        add_submenu_page(
            null, // No parent menu - direct access only via URL
            'Cashew Editor',
            'Cashew Editor',
            'manage_options',
            'cashew_editor',
            array($this, 'admin_page')
        );
    }

    public function admin_page() {
        // Suppress notices for this page
        $this->suppress_notices();
        
        // Get post_id from URL parameter
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        
        // Handle form submission
        if ($_POST && wp_verify_nonce($_POST['cashew_editor_nonce'], 'cashew_editor_save')) {
            $this->handle_form_submission($post_id);
        }
        
        // Get current data
        $post_data = $this->get_post_data($post_id);
        $pylon_data = $this->get_pylon_data($post_id);
        
        ?>
        <div class="wrap cashew-editor-wrap">
            <!-- Jezel Buttons - Copied from telescope page -->
            <div id="jezel-frontend-widget" class="jezel-frontend-container">
                <!-- Collapse Toggle -->
                <button id="jezel-toggle" class="jezel-btn jezel-toggle-btn" onclick="toggleJezelWidget()" title="Toggle Jezel Widget">
                    <span class="jezel-toggle-icon">▶</span>
                </button>
                
                <div id="jezel-buttons" class="jezel-buttons-wrapper">
                    <!-- Scroll Navigation Buttons -->
                    <button class="jezel-btn jezel-scroll-btn" onclick="jezelScrollToTop()" title="Scroll to top">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <path d="M6 3L2 7H10L6 3Z" fill="currentColor"/>
                        </svg>
                    </button>
                    
                    <button class="jezel-btn jezel-scroll-btn" onclick="jezelScrollTo(0.25)" title="Scroll to 25%">
                        <span>25</span>
                    </button>
                    
                    <button class="jezel-btn jezel-scroll-btn" onclick="jezelScrollTo(0.50)" title="Scroll to middle">
                        <span>M</span>
                    </button>
                    
                    <button class="jezel-btn jezel-scroll-btn" onclick="jezelScrollTo(0.75)" title="Scroll to 75%">
                        <span>75</span>
                    </button>
                    
                    <button class="jezel-btn jezel-scroll-btn" onclick="jezelScrollToBottom()" title="Scroll to bottom">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <path d="M6 9L10 5H2L6 9Z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </div>

            <style>
                /* Cashew Editor Specific Styles */
                .cashew-editor-wrap {
                    margin: 0;
                    padding: 20px;
                    position: relative;
                }
                .cashew-editor-wrap .notice,
                .cashew-editor-wrap .error,
                .cashew-editor-wrap .warning,
                .cashew-editor-wrap .update-nag {
                    display: none !important;
                }

                /* Jezel Buttons - Copied from telescope page */
                .jezel-frontend-container {
                    position: fixed;
                    left: 20px;
                    top: 50%;
                    transform: translateY(-50%);
                    z-index: 999999;
                }

                .jezel-buttons-wrapper {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                    transition: all 0.3s ease;
                }

                .jezel-btn {
                    width: 38px;
                    height: 38px;
                    border: none;
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    border-radius: 6px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 11px;
                    font-weight: bold;
                    transition: all 0.2s ease;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                }

                .jezel-btn:hover {
                    background: rgba(0, 0, 0, 0.9);
                    transform: scale(1.05);
                }

                .jezel-toggle-btn {
                    margin-bottom: 8px;
                    background: rgba(0, 123, 255, 0.8);
                }

                .jezel-toggle-btn:hover {
                    background: rgba(0, 123, 255, 1);
                }

                .jezel-toggle-icon {
                    transition: transform 0.3s ease;
                }

                /* UI Table Grid Styles - Based on microscope editor */
                .cashew-editor-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    background: white;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                }

                .cashew-editor-table thead {
                    background-color: #f9fafb;
                    position: sticky;
                    top: 0;
                }

                .cashew-editor-table th {
                    padding: 12px 16px;
                    text-align: left;
                    font-size: 11px;
                    font-weight: 500;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    border: 1px solid #e5e7eb;
                    white-space: nowrap;
                }

                .cashew-editor-table td {
                    padding: 12px 16px;
                    border: 1px solid #e5e7eb;
                    vertical-align: top;
                }

                .cashew-field-label {
                    font-weight: 600;
                    color: #374151;
                    min-width: 200px;
                }

                .cashew-field-input {
                    width: 100%;
                    padding: 8px 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 4px;
                    font-size: 14px;
                }

                .cashew-field-textarea {
                    width: 100%;
                    min-height: 200px;
                    padding: 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 4px;
                    font-family: 'Courier New', monospace;
                    font-size: 13px;
                    line-height: 1.4;
                    resize: vertical;
                }

                .cashew-readonly {
                    background-color: #f3f4f6;
                    color: #6b7280;
                    cursor: not-allowed;
                }

                .cashew-save-btn {
                    background: #059669;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    font-weight: 600;
                    cursor: pointer;
                    margin-top: 20px;
                }

                .cashew-save-btn:hover {
                    background: #047857;
                }

                .cashew-adjunct-column {
                    padding: 8px;
                    vertical-align: top;
                    width: 200px;
                }

                .pill-buttons {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 4px;
                }

                .pill-btn {
                    background: #f3f4f6;
                    border: 1px solid #d1d5db;
                    border-radius: 12px;
                    padding: 4px 8px;
                    font-size: 11px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    color: #374151;
                    font-weight: 500;
                }

                .pill-btn:hover {
                    background: #e5e7eb;
                    border-color: #9ca3af;
                    transform: translateY(-1px);
                }

                .pill-btn:active {
                    background: #d1d5db;
                    transform: translateY(0);
                }
            </style>

            <h1>Cashew Editor</h1>
            
            <!-- Editor Navigation Button Bar -->
            <div class="editor-navigation-bar" style="background: #f0f0f1; padding: 10px 20px; border: 1px solid #c3c4c7; border-radius: 4px; margin: 20px 0;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <?php 
                    $nav_post_id = $post_id ?: get_option('page_on_front');
                    $nav_post_url = $nav_post_id ? get_permalink($nav_post_id) : home_url('/');
                    ?>
                    
                    <!-- Pendulum (WP Native Editor) -->
                    <a href="<?php echo admin_url('post.php?post=' . $nav_post_id . '&action=edit'); ?>" 
                       target="_blank" 
                       class="button button-secondary"
                       style="display: inline-flex; align-items: center; gap: 5px;">
                        pendulum (wp native editor)
                    </a>
                    
                    <!-- Telescope -->
                    <a href="<?php echo admin_url('admin.php?page=telescope_content_editor&post=' . $nav_post_id); ?>" 
                       target="_blank" 
                       class="button button-secondary"
                       style="display: inline-flex; align-items: center; gap: 5px;">
                        telescope
                    </a>
                    
                    <!-- Cashew -->
                    <a href="<?php echo admin_url('admin.php?page=cashew_editor&post_id=' . $nav_post_id); ?>" 
                       target="_blank" 
                       class="button button-primary"
                       style="display: inline-flex; align-items: center; gap: 5px;">
                        cashew
                    </a>
                    
                    <!-- Front End -->
                    <a href="<?php echo esc_url($nav_post_url); ?>" 
                       target="_blank" 
                       class="button button-secondary"
                       style="display: inline-flex; align-items: center; gap: 5px;">
                        front end
                    </a>
                </div>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('cashew_editor_save', 'cashew_editor_nonce'); ?>
                
                <!-- UI Table Grid -->
                <table class="cashew-editor-table">
                    <thead>
                        <tr>
                            <th>Field Name</th>
                            <th>Datum House</th>
                            <th>adjunct 1</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="cashew-field-label">wp_posts.post_id</td>
                            <td>
                                <input type="text" 
                                       class="cashew-field-input cashew-readonly" 
                                       value="<?php echo esc_attr($post_data['ID'] ?? ''); ?>" 
                                       readonly>
                            </td>
                            <td class="cashew-adjunct-column">
                                <!-- Read-only field, no buttons needed -->
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">staircase_page_template_desired</td>
                            <td>
                                <input type="text" 
                                       name="staircase_page_template_desired"
                                       class="cashew-field-input" 
                                       value="<?php echo esc_attr($pylon_data['staircase_page_template_desired'] ?? ''); ?>">
                            </td>
                            <td class="cashew-adjunct-column">
                                <!-- Template buttons would go here if needed -->
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">expanse_width</td>
                            <td>
                                <input type="text" 
                                       name="expanse_width"
                                       class="cashew-field-input" 
                                       value="<?php echo esc_attr($pylon_data['expanse_width'] ?? 'full'); ?>"
                                       placeholder="full or partial (default: full)">
                            </td>
                            <td class="cashew-adjunct-column">
                                <div class="pill-buttons">
                                    <button type="button" class="pill-btn" data-target="expanse_width" data-value="full">full</button>
                                    <button type="button" class="pill-btn" data-target="expanse_width" data-value="partial">partial</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">header_desired</td>
                            <td>
                                <input type="text" 
                                       name="header_desired"
                                       class="cashew-field-input" 
                                       value="<?php echo esc_attr($pylon_data['header_desired'] ?? ''); ?>"
                                       placeholder="e.g. header1, header2, header3">
                            </td>
                            <td class="cashew-adjunct-column">
                                <div class="pill-buttons">
                                    <button type="button" class="pill-btn" data-target="header_desired" data-value="header1">header1</button>
                                    <button type="button" class="pill-btn" data-target="header_desired" data-value="header2">header2</button>
                                    <button type="button" class="pill-btn" data-target="header_desired" data-value="header3">header3</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">footer_desired</td>
                            <td>
                                <input type="text" 
                                       name="footer_desired"
                                       class="cashew-field-input" 
                                       value="<?php echo esc_attr($pylon_data['footer_desired'] ?? ''); ?>"
                                       placeholder="e.g. footer1, footer2, footer3">
                            </td>
                            <td class="cashew-adjunct-column">
                                <div class="pill-buttons">
                                    <button type="button" class="pill-btn" data-target="footer_desired" data-value="footer1">footer1</button>
                                    <button type="button" class="pill-btn" data-target="footer_desired" data-value="footer2">footer2</button>
                                    <button type="button" class="pill-btn" data-target="footer_desired" data-value="footer3">footer3</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">sidebar_desired</td>
                            <td>
                                <input type="text" 
                                       name="sidebar_desired"
                                       class="cashew-field-input" 
                                       value="<?php echo esc_attr($pylon_data['sidebar_desired'] ?? ''); ?>"
                                       placeholder="e.g. sidebar1, sidebar2">
                            </td>
                            <td class="cashew-adjunct-column">
                                <div class="pill-buttons">
                                    <button type="button" class="pill-btn" data-target="sidebar_desired" data-value="sidebar1">sidebar1</button>
                                    <button type="button" class="pill-btn" data-target="sidebar_desired" data-value="sidebar2">sidebar2</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">anteheader_desired</td>
                            <td>
                                <input type="text" 
                                       name="anteheader_desired"
                                       class="cashew-field-input" 
                                       value="<?php echo esc_attr($pylon_data['anteheader_desired'] ?? ''); ?>"
                                       placeholder="e.g. anteheader1, anteheader2 (optional)">
                            </td>
                            <td class="cashew-adjunct-column">
                                <div class="pill-buttons">
                                    <button type="button" class="pill-btn" data-target="anteheader_desired" data-value="anteheader1">anteheader1</button>
                                    <button type="button" class="pill-btn" data-target="anteheader_desired" data-value="anteheader2">anteheader2</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">wp_posts.post_title</td>
                            <td>
                                <input type="text" 
                                       name="post_title"
                                       class="cashew-field-input" 
                                       value="<?php echo esc_attr($post_data['post_title'] ?? ''); ?>">
                            </td>
                            <td class="cashew-adjunct-column">
                                <!-- Text field, no specific buttons needed -->
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">wp_pylons.pylon_archetype</td>
                            <td>
                                <input type="text" 
                                       name="pylon_archetype"
                                       class="cashew-field-input" 
                                       value="<?php echo esc_attr($pylon_data['pylon_archetype'] ?? ''); ?>">
                            </td>
                            <td class="cashew-adjunct-column">
                                <!-- Archetype field, no specific buttons needed -->
                            </td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label">wp_pylons.cashew_html_expanse</td>
                            <td>
                                <textarea name="cashew_html_expanse" 
                                          class="cashew-field-textarea"
                                          placeholder="Enter your custom HTML content here..."><?php echo esc_textarea($pylon_data['cashew_html_expanse'] ?? ''); ?></textarea>
                            </td>
                            <td class="cashew-adjunct-column">
                                <!-- HTML content field, no specific buttons needed -->
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <button type="submit" class="cashew-save-btn">Save Changes</button>
            </form>

            <script>
                // Jezel Button Functions
                function toggleJezelWidget() {
                    const buttons = document.getElementById('jezel-buttons');
                    const icon = document.querySelector('.jezel-toggle-icon');
                    
                    if (buttons.style.display === 'none') {
                        buttons.style.display = 'flex';
                        icon.style.transform = 'rotate(90deg)';
                    } else {
                        buttons.style.display = 'none';
                        icon.style.transform = 'rotate(0deg)';
                    }
                }

                function jezelScrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }

                function jezelScrollToBottom() {
                    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                }

                function jezelScrollTo(percentage) {
                    const scrollHeight = document.body.scrollHeight - window.innerHeight;
                    const targetPosition = scrollHeight * percentage;
                    window.scrollTo({ top: targetPosition, behavior: 'smooth' });
                }

                // Pill Button Functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const pillButtons = document.querySelectorAll('.pill-btn');
                    
                    pillButtons.forEach(function(button) {
                        button.addEventListener('click', function() {
                            const targetFieldName = this.getAttribute('data-target');
                            const value = this.getAttribute('data-value');
                            
                            // Find the input field by name
                            const targetInput = document.querySelector('input[name="' + targetFieldName + '"]');
                            if (targetInput) {
                                targetInput.value = value;
                                
                                // Visual feedback
                                this.style.background = '#10b981';
                                this.style.color = '#ffffff';
                                this.style.borderColor = '#10b981';
                                
                                // Reset visual feedback after a short delay
                                setTimeout(() => {
                                    this.style.background = '';
                                    this.style.color = '';
                                    this.style.borderColor = '';
                                }, 200);
                            }
                        });
                    });
                });
            </script>
        </div>
        <?php
    }

    private function get_post_data($post_id) {
        if (!$post_id) return array();
        
        $post = get_post($post_id);
        return $post ? $post->to_array() : array();
    }

    private function get_pylon_data($post_id) {
        if (!$post_id) return array();
        
        global $wpdb;
        $pylons_table = $wpdb->prefix . 'pylons';
        
        $pylon_data = $wpdb->get_row($wpdb->prepare(
            "SELECT pylon_archetype, cashew_html_expanse, staircase_page_template_desired, expanse_width, header_desired, footer_desired, sidebar_desired, anteheader_desired FROM {$pylons_table} WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        return $pylon_data ?: array();
    }

    private function handle_form_submission($post_id) {
        if (!$post_id) return;
        
        global $wpdb;
        
        // Update wp_posts
        if (isset($_POST['post_title'])) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($_POST['post_title'])
            ));
        }
        
        // Update/Insert wp_pylons
        $pylons_table = $wpdb->prefix . 'pylons';
        
        // Check if pylon record exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT pylon_id FROM {$pylons_table} WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        $pylon_data = array(
            'pylon_archetype' => sanitize_text_field($_POST['pylon_archetype'] ?? ''),
            'cashew_html_expanse' => wp_unslash($_POST['cashew_html_expanse'] ?? ''),
            'staircase_page_template_desired' => sanitize_text_field($_POST['staircase_page_template_desired'] ?? ''),
            'expanse_width' => sanitize_text_field($_POST['expanse_width'] ?? 'full'),
            'header_desired' => sanitize_text_field($_POST['header_desired'] ?? ''),
            'footer_desired' => sanitize_text_field($_POST['footer_desired'] ?? ''),
            'sidebar_desired' => sanitize_text_field($_POST['sidebar_desired'] ?? ''),
            'anteheader_desired' => sanitize_text_field($_POST['anteheader_desired'] ?? '')
        );
        
        if ($exists) {
            // Update existing record
            $wpdb->update(
                $pylons_table,
                $pylon_data,
                array('rel_wp_post_id' => $post_id),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'), // 8 fields: pylon_archetype, cashew_html_expanse, staircase_page_template_desired, expanse_width, header_desired, footer_desired, sidebar_desired, anteheader_desired
                array('%d')
            );
        } else {
            // Insert new record
            $pylon_data['rel_wp_post_id'] = $post_id;
            $wpdb->insert(
                $pylons_table,
                $pylon_data,
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d') // 8 text fields + 1 integer (rel_wp_post_id)
            );
        }
        
        // Show success message
        echo '<div class="notice notice-success"><p>Changes saved successfully!</p></div>';
    }

    public function suppress_notices() {
        if (isset($_GET['page']) && $_GET['page'] === 'cashew_editor') {
            // Remove all admin notices for this page
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Add CSS to hide any remaining notices
            echo '<style>
                .notice, .error, .warning, .update-nag, .updated, .settings-error {
                    display: none !important;
                }
                #wpbody-content .wrap h1:after {
                    content: "" !important;
                }
            </style>';
        }
    }

    public function suppress_admin_notices() {
        if (isset($_GET['page']) && $_GET['page'] === 'cashew_editor') {
            // Prevent output buffering of notices
            ob_clean();
            return false;
        }
    }
    
    public function fix_admin_title($admin_title, $title) {
        if (isset($_GET['page']) && $_GET['page'] === 'cashew_editor') {
            return 'Cashew Editor - WordPress';
        }
        return $admin_title;
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'admin_page_cashew_editor') {
            return;
        }
        
        // Additional script to hide notices via JavaScript
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                $(".notice, .error, .warning, .update-nag, .updated, .settings-error").hide();
                
                // Hide notices that might appear after page load
                setTimeout(function() {
                    $(".notice, .error, .warning, .update-nag, .updated, .settings-error").hide();
                }, 100);
                
                // Watch for dynamically added notices
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === "childList") {
                            $(mutation.target).find(".notice, .error, .warning, .update-nag, .updated, .settings-error").hide();
                        }
                    });
                });
                observer.observe(document.body, { childList: true, subtree: true });
            });
        ');
    }
}

new CashewEditorAdmin();