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
        
        // Add AJAX handler for cherry template generation
        add_action('wp_ajax_generate_cherry_template_html', array($this, 'ajax_generate_cherry_template'));
        
        // Add AJAX handler for creating orbitposts row
        add_action('wp_ajax_create_orbitposts_row', array($this, 'ajax_create_orbitposts_row'));
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
        $orbitposts_data = $this->get_orbitposts_data($post_id);
        
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
                    width: auto;
                    border-collapse: collapse;
                    margin-top: 20px;
                    background: white;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                    display: inline-table;
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
                    white-space: nowrap;
                }
                
                /* Allow wrapping for textarea cells */
                .cashew-editor-table td:has(.cashew-field-textarea) {
                    white-space: normal;
                }

                .cashew-field-label {
                    font-weight: 600;
                    color: #374151;
                    min-width: 200px;
                }
                
                .cashew-adjunct-column {
                    white-space: nowrap;
                    min-width: auto;
                }

                .cashew-field-input {
                    width: 300px;
                    padding: 8px 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 4px;
                    font-size: 14px;
                }

                .cashew-field-textarea {
                    width: 500px;
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
                    background: #16a34a;
                    color: white;
                    border: none;
                    padding: 12px 28px;
                    border-radius: 6px;
                    font-weight: 600;
                    font-size: 15px;
                    cursor: pointer;
                }

                .cashew-save-btn:hover {
                    background: #15803d;
                }

                .cashew-save-btn-top {
                    margin-bottom: 16px;
                }

                .cashew-save-btn-bottom {
                    margin-top: 20px;
                }

                /* Polyansk toggle switch */
                .cashew-toggle {
                    width: 48px;
                    height: 26px;
                    background: #d1d5db;
                    border-radius: 13px;
                    cursor: pointer;
                    position: relative;
                    transition: background 0.2s;
                    display: inline-block;
                }

                .cashew-toggle-on {
                    background: #16a34a;
                }

                .cashew-toggle-dial {
                    width: 22px;
                    height: 22px;
                    background: #ffffff;
                    border-radius: 50%;
                    position: absolute;
                    top: 2px;
                    left: 2px;
                    transition: left 0.2s;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
                }

                .cashew-toggle-on .cashew-toggle-dial {
                    left: 24px;
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
                
                <button type="submit" class="cashew-save-btn cashew-save-btn-top">Save Changes</button>

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
                                <button type="button" class="pill-btn" data-target="staircase_page_template_desired" data-value="cherry">cherry</button>
                                <button type="button" class="pill-btn" data-target="staircase_page_template_desired" data-value="vibrantcashew">vibrantcashew</button>
                                <button type="button" class="pill-btn" data-target="staircase_page_template_desired" data-value="bilberry">bilberry</button>
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
                            <td class="cashew-field-label">wp_pylons.moniker</td>
                            <td>
                                <input type="text"
                                       name="moniker"
                                       class="cashew-field-input"
                                       value="<?php echo esc_attr($pylon_data['moniker'] ?? ''); ?>">
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
                            <td class="cashew-field-label">show_polyansk_custom_page_section</td>
                            <td>
                                <?php $polyansk_val = !empty($pylon_data['show_polyansk_custom_page_section']); ?>
                                <input type="hidden" name="show_polyansk_custom_page_section" value="<?php echo $polyansk_val ? '1' : '0'; ?>">
                                <div class="cashew-toggle <?php echo $polyansk_val ? 'cashew-toggle-on' : ''; ?>" data-field="show_polyansk_custom_page_section">
                                    <div class="cashew-toggle-dial"></div>
                                </div>
                            </td>
                            <td class="cashew-adjunct-column"></td>
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
                        
                        <!-- Ferret Code Fields from wp_zen_orbitposts -->
                        <tr>
                            <td class="cashew-field-label"><b>wp_zen_orbitposts.ferret_header_code</b></td>
                            <td>
                                <?php if ($orbitposts_data): ?>
                                    <input type="text" 
                                           name="ferret_header_code"
                                           class="cashew-field-input" 
                                           value="<?php echo esc_attr($orbitposts_data['ferret_header_code'] ?? ''); ?>" 
                                           placeholder="Enter header code...">
                                <?php else: ?>
                                    <span style="color: #666;">no orbitposts row found </span>
                                    <button type="button" class="button button-small create-orbitposts-btn" data-post-id="<?php echo $post_id; ?>">(create one now)</button>
                                <?php endif; ?>
                            </td>
                            <td class="cashew-adjunct-column"></td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label"><b>wp_zen_orbitposts.ferret_header_code_2</b></td>
                            <td>
                                <?php if ($orbitposts_data): ?>
                                    <input type="text" 
                                           name="ferret_header_code_2"
                                           class="cashew-field-input" 
                                           value="<?php echo esc_attr($orbitposts_data['ferret_header_code_2'] ?? ''); ?>" 
                                           placeholder="Enter header code 2...">
                                <?php else: ?>
                                    <span style="color: #666;">no orbitposts row found </span>
                                    <button type="button" class="button button-small create-orbitposts-btn" data-post-id="<?php echo $post_id; ?>">(create one now)</button>
                                <?php endif; ?>
                            </td>
                            <td class="cashew-adjunct-column"></td>
                        </tr>
                        <tr>
                            <td class="cashew-field-label"><b>wp_zen_orbitposts.ferret_footer_code</b></td>
                            <td>
                                <?php if ($orbitposts_data): ?>
                                    <input type="text" 
                                           name="ferret_footer_code"
                                           class="cashew-field-input" 
                                           value="<?php echo esc_attr($orbitposts_data['ferret_footer_code'] ?? ''); ?>" 
                                           placeholder="Enter footer code...">
                                <?php else: ?>
                                    <span style="color: #666;">no orbitposts row found </span>
                                    <button type="button" class="button button-small create-orbitposts-btn" data-post-id="<?php echo $post_id; ?>">(create one now)</button>
                                <?php endif; ?>
                            </td>
                            <td class="cashew-adjunct-column"></td>
                        </tr>
                    </tbody>
                </table>
                
                <button type="submit" class="cashew-save-btn cashew-save-btn-bottom">Save Changes</button>
            </form>
            
            <!-- Cherry Template Generation Section -->
            <div class="cherry-template-generator" style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                <div style="margin-bottom: 20px;">
                    <button type="button" id="generate-cherry-template" class="cashew-save-btn" style="background: #8b5cf6;">
                        Generate Cherry Page Template Raw Source Code with No Header and No Footer
                    </button>
                    <button type="button" id="copy-cherry-template" class="cashew-save-btn" style="background: #10b981; display: none; margin-left: 10px;">
                        Copy to Clipboard
                    </button>
                </div>
                
                <div id="cherry-template-output" style="display: none;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151;">
                        Generated Cherry Template HTML:
                    </label>
                    <textarea id="cherry-template-html" 
                              class="cashew-field-textarea" 
                              style="width: 100%; height: 500px; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.4;"
                              wrap="off"
                              readonly></textarea>
                    <div id="cherry-template-status" style="margin-top: 10px; padding: 10px; display: none;"></div>
                </div>
            </div>

            <script>
                // Polyansk toggle switch
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.cashew-toggle').forEach(function(toggle) {
                        toggle.addEventListener('click', function() {
                            var field = this.getAttribute('data-field');
                            var input = document.querySelector('input[name="' + field + '"]');
                            var isOn = this.classList.toggle('cashew-toggle-on');
                            input.value = isOn ? '1' : '0';
                        });
                    });
                });

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

                // Cherry Template Generator Functionality
                document.addEventListener('DOMContentLoaded', function() {
                    // Generate Cherry Template Button
                    const generateBtn = document.getElementById('generate-cherry-template');
                    const copyBtn = document.getElementById('copy-cherry-template');
                    const outputDiv = document.getElementById('cherry-template-output');
                    const htmlTextarea = document.getElementById('cherry-template-html');
                    const statusDiv = document.getElementById('cherry-template-status');
                    
                    if (generateBtn) {
                        generateBtn.addEventListener('click', function() {
                            const postId = <?php echo json_encode($post_id); ?>;
                            
                            if (!postId) {
                                alert('No post ID found');
                                return;
                            }
                            
                            // Show loading state
                            generateBtn.disabled = true;
                            generateBtn.textContent = 'Generating...';
                            statusDiv.style.display = 'block';
                            statusDiv.style.background = '#fef3c7';
                            statusDiv.style.color = '#92400e';
                            statusDiv.innerHTML = 'Generating cherry template HTML...';
                            
                            // Make AJAX request
                            jQuery.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'generate_cherry_template_html',
                                    post_id: postId,
                                    nonce: '<?php echo wp_create_nonce('generate_cherry_template'); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Show the generated HTML
                                        outputDiv.style.display = 'block';
                                        copyBtn.style.display = 'inline-block';
                                        htmlTextarea.value = response.data.html;
                                        
                                        // Update status
                                        statusDiv.style.background = '#d1fae5';
                                        statusDiv.style.color = '#065f46';
                                        statusDiv.innerHTML = 'Cherry template HTML generated successfully!';
                                        
                                        // Auto-hide status after 3 seconds
                                        setTimeout(function() {
                                            statusDiv.style.display = 'none';
                                        }, 3000);
                                    } else {
                                        // Show error
                                        statusDiv.style.background = '#fee2e2';
                                        statusDiv.style.color = '#991b1b';
                                        statusDiv.innerHTML = 'Error: ' + (response.data.message || 'Failed to generate template');
                                    }
                                },
                                error: function() {
                                    statusDiv.style.background = '#fee2e2';
                                    statusDiv.style.color = '#991b1b';
                                    statusDiv.innerHTML = 'Network error occurred while generating template';
                                },
                                complete: function() {
                                    generateBtn.disabled = false;
                                    generateBtn.innerHTML = 'Generate Cherry Page Template Raw Source Code with No Header and No Footer';
                                }
                            });
                        });
                    }
                    
                    // Copy to Clipboard Button
                    if (copyBtn) {
                        copyBtn.addEventListener('click', function() {
                            htmlTextarea.select();
                            htmlTextarea.setSelectionRange(0, 99999); // For mobile devices
                            
                            try {
                                document.execCommand('copy');
                                
                                // Visual feedback
                                copyBtn.textContent = 'Copied!';
                                copyBtn.style.background = '#059669';
                                
                                // Reset after 2 seconds
                                setTimeout(function() {
                                    copyBtn.textContent = 'Copy to Clipboard';
                                    copyBtn.style.background = '#10b981';
                                }, 2000);
                            } catch (err) {
                                alert('Failed to copy to clipboard');
                            }
                        });
                    }
                    
                    // Original Pill Button Functionality
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
            
            <script>
            jQuery(document).ready(function($) {
                // Handle create orbitposts row button click
                $('.create-orbitposts-btn').on('click', function() {
                    var button = $(this);
                    var postId = button.data('post-id');
                    
                    button.prop('disabled', true).text('Creating...');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'create_orbitposts_row',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('create_orbitposts'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload the page to show the new input fields
                                location.reload();
                            } else {
                                alert('Error creating orbitposts row: ' + response.data.message);
                                button.prop('disabled', false).text('(create one now)');
                            }
                        },
                        error: function() {
                            alert('An error occurred while creating the orbitposts row');
                            button.prop('disabled', false).text('(create one now)');
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
            "SELECT pylon_archetype, cashew_html_expanse, staircase_page_template_desired, expanse_width, header_desired, footer_desired, sidebar_desired, anteheader_desired, show_polyansk_custom_page_section FROM {$pylons_table} WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        return $pylon_data ?: array();
    }
    
    private function get_orbitposts_data($post_id) {
        if (!$post_id) return null;
        
        global $wpdb;
        $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$orbitposts_table'") == $orbitposts_table;
        if (!$table_exists) {
            return null;
        }
        
        $orbitposts_data = $wpdb->get_row($wpdb->prepare(
            "SELECT ferret_header_code, ferret_header_code_2, ferret_footer_code FROM {$orbitposts_table} WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        return $orbitposts_data;
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
            'moniker' => sanitize_text_field($_POST['moniker'] ?? ''),
            'cashew_html_expanse' => wp_unslash($_POST['cashew_html_expanse'] ?? ''),
            'staircase_page_template_desired' => sanitize_text_field($_POST['staircase_page_template_desired'] ?? ''),
            'expanse_width' => sanitize_text_field($_POST['expanse_width'] ?? 'full'),
            'header_desired' => sanitize_text_field($_POST['header_desired'] ?? ''),
            'footer_desired' => sanitize_text_field($_POST['footer_desired'] ?? ''),
            'sidebar_desired' => sanitize_text_field($_POST['sidebar_desired'] ?? ''),
            'anteheader_desired' => sanitize_text_field($_POST['anteheader_desired'] ?? ''),
            'show_polyansk_custom_page_section' => intval($_POST['show_polyansk_custom_page_section'] ?? 0)
        );

        if ($exists) {
            // Update existing record
            $wpdb->update(
                $pylons_table,
                $pylon_data,
                array('rel_wp_post_id' => $post_id),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'), // 9 text fields + 1 integer (show_polyansk)
                array('%d')
            );
        } else {
            // Insert new record
            $pylon_data['rel_wp_post_id'] = $post_id;
            $wpdb->insert(
                $pylons_table,
                $pylon_data,
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d') // 9 text fields + 1 integer (show_polyansk) + 1 integer (rel_wp_post_id)
            );
        }
        
        // Update/Insert wp_zen_orbitposts if ferret fields are submitted
        $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$orbitposts_table'") == $orbitposts_table;
        
        if ($table_exists && (isset($_POST['ferret_header_code']) || isset($_POST['ferret_header_code_2']) || isset($_POST['ferret_footer_code']))) {
            // Check if orbitposts record exists
            $orbit_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT rel_wp_post_id FROM {$orbitposts_table} WHERE rel_wp_post_id = %d",
                $post_id
            ));
            
            $orbitposts_data = array(
                'ferret_header_code' => wp_unslash($_POST['ferret_header_code'] ?? ''),
                'ferret_header_code_2' => wp_unslash($_POST['ferret_header_code_2'] ?? ''),
                'ferret_footer_code' => wp_unslash($_POST['ferret_footer_code'] ?? '')
            );
            
            if ($orbit_exists) {
                // Update existing record
                $wpdb->update(
                    $orbitposts_table,
                    $orbitposts_data,
                    array('rel_wp_post_id' => $post_id),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
            } else {
                // Insert new record
                $orbitposts_data['rel_wp_post_id'] = $post_id;
                $wpdb->insert(
                    $orbitposts_table,
                    $orbitposts_data,
                    array('%s', '%s', '%s', '%d')
                );
            }
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
    
    /**
     * AJAX handler to generate cherry template HTML
     */
    public function ajax_create_orbitposts_row() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'create_orbitposts')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
            return;
        }
        
        global $wpdb;
        $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$orbitposts_table'") == $orbitposts_table;
        if (!$table_exists) {
            // Create the table if it doesn't exist
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS $orbitposts_table (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                rel_wp_post_id BIGINT(20) UNSIGNED NOT NULL,
                ferret_header_code LONGTEXT DEFAULT NULL,
                ferret_header_code_2 LONGTEXT DEFAULT NULL,
                ferret_footer_code LONGTEXT DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY rel_wp_post_id (rel_wp_post_id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        
        // Check if row already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT rel_wp_post_id FROM {$orbitposts_table} WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        if ($exists) {
            wp_send_json_error(array('message' => 'Orbitposts row already exists'));
            return;
        }
        
        // Insert new row
        $result = $wpdb->insert(
            $orbitposts_table,
            array(
                'rel_wp_post_id' => $post_id,
                'ferret_header_code' => '',
                'ferret_header_code_2' => '',
                'ferret_footer_code' => ''
            ),
            array('%d', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to create orbitposts row'));
            return;
        }
        
        wp_send_json_success(array('message' => 'Orbitposts row created successfully'));
    }
    
    public function ajax_generate_cherry_template() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'generate_cherry_template')) {
            wp_send_json_error(array('message' => 'Security verification failed'));
        }
        
        // Get post ID
        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
        }
        
        // Check if staircase theme functions exist
        if (!function_exists('staircase_cherry_hero') || !function_exists('staircase_render_avg_rating_box')) {
            wp_send_json_error(array('message' => 'Staircase theme functions not found. Please ensure Staircase theme is active.'));
        }
        
        // Set up the post data
        global $post;
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array('message' => 'Post not found'));
        }
        setup_postdata($post);
        
        // Start output buffering to capture the HTML
        ob_start();
        
        try {
            // Render cherry template components in order
            
            // 1. Batman Hero Section
            if (function_exists('staircase_cherry_hero')) {
                staircase_cherry_hero();
            }
            
            // 2. Average Rating Box (always second in cherry template)
            if (function_exists('staircase_render_avg_rating_box')) {
                staircase_render_avg_rating_box();
            }
            
            // 3. Chen Cards
            if (function_exists('staircase_render_chen_cards')) {
                staircase_render_chen_cards();
            }
            
            // 4. Get box order and render other boxes
            global $wpdb;
            $pylons_table = $wpdb->prefix . 'pylons';
            $pylon_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$pylons_table} WHERE rel_wp_post_id = %d",
                $post_id
            ), ARRAY_A);
            
            // Check for custom box order
            $box_order_json = $pylon_data['box_order_json'] ?? '';
            $custom_box_order = array();
            
            if (!empty($box_order_json)) {
                $decoded_order = json_decode($box_order_json, true);
                if (is_array($decoded_order)) {
                    $custom_box_order = $decoded_order;
                }
            }
            
            // Default cherry template box order
            $default_order = array(
                'content_fields',
                'osb_box', 
                'reviewsbox',
                'faq',
                'video_box',
                'elf_form_box',
                'city_links',
                'content_2',
                'content_3',
                'content_4',
                'content_5',
                'testimonial_box',
                'cta_banner_box',
                'map_driving_directions',
                'service_area_box'
            );
            
            $boxes_to_render = !empty($custom_box_order) ? $custom_box_order : $default_order;
            
            // Render each box
            foreach ($boxes_to_render as $box_name) {
                // Skip hero and rating box as they're already rendered
                if (in_array($box_name, array('batman_hero_box', 'avg_rating_box', 'chen_cards'))) {
                    continue;
                }
                
                // Map box names to their render functions
                switch ($box_name) {
                    case 'content_fields':
                        if (function_exists('staircase_render_content_fields')) {
                            staircase_render_content_fields();
                        }
                        break;
                    case 'osb_box':
                        if (function_exists('staircase_render_osb_box')) {
                            staircase_render_osb_box();
                        }
                        break;
                    case 'reviewsbox':
                        if (function_exists('staircase_render_reviewsbox')) {
                            staircase_render_reviewsbox();
                        }
                        break;
                    case 'faq':
                        if (function_exists('staircase_render_faq')) {
                            staircase_render_faq();
                        }
                        break;
                    case 'video_box':
                        if (function_exists('staircase_render_video_box')) {
                            staircase_render_video_box();
                        }
                        break;
                    case 'elf_form_box':
                        if (function_exists('staircase_render_elf_form_box')) {
                            staircase_render_elf_form_box();
                        }
                        break;
                    case 'city_links':
                        if (function_exists('staircase_render_city_links')) {
                            staircase_render_city_links();
                        }
                        break;
                    case 'content_2':
                        if (function_exists('staircase_render_content_2')) {
                            staircase_render_content_2();
                        }
                        break;
                    case 'content_3':
                        if (function_exists('staircase_render_content_3')) {
                            staircase_render_content_3();
                        }
                        break;
                    case 'content_4':
                        if (function_exists('staircase_render_content_4')) {
                            staircase_render_content_4();
                        }
                        break;
                    case 'content_5':
                        if (function_exists('staircase_render_content_5')) {
                            staircase_render_content_5();
                        }
                        break;
                    case 'testimonial_box':
                        if (function_exists('staircase_render_testimonial_box')) {
                            staircase_render_testimonial_box();
                        }
                        break;
                    case 'cta_banner_box':
                        if (function_exists('staircase_render_cta_banner_box')) {
                            staircase_render_cta_banner_box();
                        }
                        break;
                    case 'map_driving_directions':
                        if (function_exists('staircase_render_map_driving_directions')) {
                            staircase_render_map_driving_directions();
                        }
                        break;
                    case 'service_area_box':
                        if (function_exists('staircase_render_service_area_box')) {
                            staircase_render_service_area_box();
                        }
                        break;
                }
            }
            
            // Get the generated HTML
            $html = ob_get_clean();
            
            // Reset post data
            wp_reset_postdata();
            
            // Return success with the HTML
            wp_send_json_success(array(
                'html' => $html,
                'message' => 'Cherry template HTML generated successfully'
            ));
            
        } catch (Exception $e) {
            ob_end_clean();
            wp_reset_postdata();
            wp_send_json_error(array('message' => 'Error generating template: ' . $e->getMessage()));
        }
    }
}

new CashewEditorAdmin();