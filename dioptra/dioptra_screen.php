<?php
/**
 * Dioptra Screen - Main admin interface for Dioptra system
 * 
 * @package Ruplin
 * @subpackage Dioptra
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the Dioptra admin screen
 */
function ruplin_render_dioptra_screen() {
    global $wpdb;
    
    // Enqueue WordPress media scripts for image selector
    wp_enqueue_media();
    
    // Get post ID from URL parameter
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    
    // Initialize data arrays
    $pylon_data = array();
    $post_data = array();
    
    if ($post_id) {
        // Get wp_posts data
        $post = get_post($post_id);
        if ($post) {
            $post_data = array(
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_status' => $post->post_status,
                'post_type' => $post->post_type,
                'post_date' => $post->post_date,
                'post_name' => $post->post_name
            );
        }
        
        // Get wp_pylons data
        $pylons_table = $wpdb->prefix . 'pylons';
        $pylon_row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$pylons_table} WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        if ($pylon_row) {
            $pylon_data = $pylon_row;
        }
    }
    
    // Define all fields in order
    $fields = array(
        'pylon_id' => 'pylons',
        'rel_plasma_page_id' => 'pylons',
        'rel_wp_post_id' => 'pylons',
        'jchronology_order_for_blog_posts' => 'pylons',
        'jchronology_batch' => 'pylons',
        'paragon_featured_image_id' => 'pylons',
        'staircase_page_template_desired' => 'pylons',
        'pylon_archetype' => 'pylons',
        'exempt_from_silkweaver_menu_dynamical' => 'pylons',
        'moniker' => 'pylons',
        'short_anchor' => 'pylons',
        'begin-now-vn-system-area' => 'pylons',
        'vec_disable_vn_system_sitewide' => 'pylons',
        'vec_disable_vn_system_on_post' => 'pylons',
        'vec_meta_title' => 'pylons',
        'vec_meta_description' => 'pylons',
        'meta-title-actual-output' => 'pylons',
        'meta-description-actual-output' => 'pylons',
        'post_title' => 'posts',
        'post_content' => 'posts',
        'post_status' => 'posts',
        'post_type' => 'posts',
        'post_date' => 'posts',
        'post_name' => 'posts',
        'hero_mainheading' => 'pylons',
        'hero_subheading' => 'pylons',
        'chenblock_card1_title' => 'pylons',
        'chenblock_card1_desc' => 'pylons',
        'chenblock_card2_title' => 'pylons',
        'chenblock_card2_desc' => 'pylons',
        'chenblock_card3_title' => 'pylons',
        'chenblock_card3_desc' => 'pylons',
        'cta_zarl_heading' => 'pylons',
        'cta_zarl_phone' => 'pylons',
        'cta_zarl_availability' => 'pylons',
        'cta_zarl_wait_time' => 'pylons',
        'cta_zarl_rating' => 'pylons',
        'cta_zarl_review_count' => 'pylons',
        'sidebar_zebby_title' => 'pylons',
        'sidebar_zebby_description' => 'pylons',
        'sidebar_zebby_button_text_line_1' => 'pylons',
        'sidebar_zebby_button_text_line_2' => 'pylons',
        'sidebar_zebby_availability' => 'pylons',
        'sidebar_zebby_wait_time' => 'pylons',
        'trustblock_vezzy_title' => 'pylons',
        'trustblock_vezzy_desc' => 'pylons',
        'baynar1_main' => 'pylons',
        'baynar2_main' => 'pylons',
        'serena_faq_box_q1' => 'pylons',
        'serena_faq_box_a1' => 'pylons',
        'serena_faq_box_q2' => 'pylons',
        'serena_faq_box_a2' => 'pylons',
        'serena_faq_box_q3' => 'pylons',
        'serena_faq_box_a3' => 'pylons',
        'serena_faq_box_q4' => 'pylons',
        'serena_faq_box_a4' => 'pylons',
        'serena_faq_box_q5' => 'pylons',
        'serena_faq_box_a5' => 'pylons',
        'serena_faq_box_q6' => 'pylons',
        'serena_faq_box_a6' => 'pylons',
        'serena_faq_box_q7' => 'pylons',
        'serena_faq_box_a7' => 'pylons',
        'serena_faq_box_q8' => 'pylons',
        'serena_faq_box_a8' => 'pylons',
        'serena_faq_box_q9' => 'pylons',
        'serena_faq_box_a9' => 'pylons',
        'serena_faq_box_q10' => 'pylons',
        'serena_faq_box_a10' => 'pylons',
        'locpage_topical_string' => 'pylons',
        'locpage_neighborhood' => 'pylons',
        'locpage_city' => 'pylons',
        'locpage_state_code' => 'pylons',
        'locpage_state_full' => 'pylons',
        'locpage_gmaps_string' => 'pylons'
        // OSB fields removed from main table - they're handled separately in the OSB tab
    );
    
    // Debug: Check for servicepage entries
    $debug_servicepages = $wpdb->get_results("
        SELECT py.*, p.post_title, p.post_status 
        FROM {$pylons_table} py 
        LEFT JOIN {$wpdb->posts} p ON p.ID = py.rel_wp_post_id 
        WHERE py.pylon_archetype = 'servicepage'
    ", ARRAY_A);
    
    ?>
    <div class="wrap">
        <div style="display: flex; align-items: center; margin-bottom: 0;">
            <h1 style="margin-right: 20px; margin-bottom: 0;">Dioptra</h1>
            <button type="button" 
                    id="dioptra-save-btn"
                    style="background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">
                Save
            </button>
        </div>
        
        <!-- Dioptra Save Debug Info -->
        <?php if ($post_id): ?>
            <?php
            // Debug current pylon data for this specific post
            $current_pylon = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$pylons_table} WHERE rel_wp_post_id = %d", 
                $post_id
            ), ARRAY_A);
            ?>
            <div style="background: #e7f3ff; border: 1px solid #0073aa; padding: 15px; margin: 10px 0; border-radius: 4px;">
                <strong>🔍 Dioptra Debug Info for Post ID <?php echo $post_id; ?>:</strong>
                <div style="margin: 10px 0; font-family: monospace; font-size: 12px;">
                    <strong>Post Details:</strong><br>
                    - Post Title: "<?php echo esc_html(get_the_title($post_id)); ?>"<br>
                    - Post Status: <?php echo get_post_status($post_id); ?><br>
                    - Pylon Record Exists: <?php echo $current_pylon ? 'YES' : 'NO'; ?><br>
                    
                    <?php if ($current_pylon): ?>
                        <br><strong>Current Pylon Data:</strong><br>
                        - Pylon ID: <?php echo $current_pylon['pylon_id'] ?? 'NULL'; ?><br>
                        - Moniker: "<?php echo esc_html($current_pylon['moniker'] ?? ''); ?>"<br>
                        - Paragon Description: "<?php echo esc_html(substr($current_pylon['paragon_description'] ?? '', 0, 50)); ?><?php echo strlen($current_pylon['paragon_description'] ?? '') > 50 ? '...' : ''; ?>"<br>
                        - Last Updated: <?php echo $current_pylon['updated_at'] ?? 'Never'; ?><br>
                        <br><strong>OSB Settings:</strong><br>
                        - OSB Enabled: <?php echo isset($current_pylon['osb_is_enabled']) ? ($current_pylon['osb_is_enabled'] ? 'YES (1)' : 'NO (0)') : 'COLUMN MISSING'; ?><br>
                        - OSB Title: "<?php echo esc_html($current_pylon['osb_box_title'] ?? ''); ?>"<br>
                        - Services Per Row: <?php echo $current_pylon['osb_services_per_row'] ?? 'NULL'; ?><br>
                        - Max Services: <?php echo $current_pylon['osb_max_services_display'] ?? 'NULL'; ?><br>
                    <?php endif; ?>
                </div>
                <div id="save-debug-results" style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 3px; display: none;">
                    <strong>Save Debug Results:</strong>
                    <div id="save-debug-content"></div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!$post_id): ?>
            <div style="margin-top: 20px;">
                <p style="color: #666;">Please provide a post ID in the URL (e.g., ?page=dioptra&post=128)</p>
            </div>
        <?php else: ?>
            <div style="margin-top: 20px;">
                <p style="display: inline-block; margin-right: 15px;">
                    <strong>Post ID:</strong> <?php echo $post_id; ?>
                    <?php if (!empty($post_data['post_title'])): ?>
                        | <strong>Title:</strong> <?php echo esc_html($post_data['post_title']); ?>
                    <?php endif; ?>
                </p>
                
                <?php 
                $has_pylon = !empty($pylon_data);
                $button_style = $has_pylon ? 
                    'background: #ccc; color: #666; cursor: not-allowed;' : 
                    'background: #28a745; color: white; cursor: pointer;';
                ?>
                
                <button type="button" 
                        id="create-pylon-btn"
                        <?php echo $has_pylon ? 'disabled' : ''; ?>
                        style="<?php echo $button_style; ?> padding: 8px 15px; border: none; border-radius: 4px; font-size: 14px; margin-right: 10px;"
                        onclick="createMissingPylon(<?php echo $post_id; ?>)">
                    create missing pylon
                </button>
                
                <?php
                // Get sitespren_base for external URLs (same logic as Hurricane)
                $sitespren_base = 'example.com'; // fallback
                $current_site_url = get_site_url();
                $sitespren_table = $wpdb->prefix . 'zen_sitespren';
                $sitespren_row = $wpdb->get_row("SELECT * FROM {$sitespren_table} WHERE site_url = '{$current_site_url}' LIMIT 1", ARRAY_A);
                if ($sitespren_row && !empty($sitespren_row['sitespren_base'])) {
                    $sitespren_base = $sitespren_row['sitespren_base'];
                }
                
                $drom_url = 'http://localhost:3000/drom?activefilterchamber=daylight&sitesentered=' . urlencode($sitespren_base);
                $sitejar4_url = 'http://localhost:3000/sitejar4?sitesentered=' . urlencode($sitespren_base);
                $dioptra_url = admin_url('admin.php?page=dioptra&post=' . $post_id);
                $livefrontend_url = get_permalink($post_id);
                $pendulum_url = admin_url('post.php?post=' . $post_id . '&action=edit');
                ?>
                
                <!-- Navigation Buttons -->
                <a href="<?php echo esc_url($drom_url); ?>" 
                   target="_blank" 
                   style="background: #3e0d7b; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase; margin-right: 5px;">
                    /drom
                </a>
                
                <a href="<?php echo esc_url($sitejar4_url); ?>" 
                   target="_blank" 
                   style="background: #193968; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase; margin-right: 5px;">
                    /sitejar4
                </a>
                
                <a href="<?php echo esc_url($pendulum_url); ?>" 
                   target="_blank" 
                   style="background: #000000; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase; margin-right: 5px;">
                    pendulum
                </a>
                
                <a href="<?php echo esc_url($dioptra_url); ?>" 
                   target="_blank" 
                   style="background: #4a90e2; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase; margin-right: 5px;">
                    =dioptra
                </a>
                
                <a href="<?php echo esc_url($livefrontend_url); ?>" 
                   target="_blank" 
                   style="background: #383838; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: lowercase;">
                    livefrontend screen
                </a>
            </div>
            
            <!-- Horizontal Tab System -->
            <div id="dioptra-tabs-container" style="margin-top: 20px; margin-bottom: 10px;">
                <div id="dioptra-tabs-nav" style="border-bottom: 3px solid #ddd; margin-bottom: 0;">
                    <button type="button" 
                            class="dioptra-tab-btn active" 
                            data-tab="main-tab-1a"
                            style="background: #0073aa; color: white; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        Main Tab 1a
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="our-services-config"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        Our Services Box Config
                    </button>
                </div>
            </div>
            
            <!-- Tab Content Containers -->
            <div id="main-tab-1a" class="dioptra-tab-content" style="display: block;">
            
            <table style="width: auto; border-collapse: collapse; margin-top: 0;">
                <thead>
                    <tr style="background-color: #f1f1f1;">
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">checkbox</th>
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">other-info</th>
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">field-name</th>
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black; width: 700px; min-width: 700px; max-width: 700px;">datum-house</th>
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">blank1</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fields as $field_name => $table_source): ?>
                        <?php 
                        // Add black top border for specific rows
                        $row_style = '';
                        $border_rows = [
                            'serena_faq_box_q1', 
                            'locpage_topical_string',
                            'post_title',
                            'hero_mainheading',
                            'chenblock_card1_title',
                            'cta_zarl_heading',
                            'sidebar_zebby_title',
                            'trustblock_vezzy_title',
                            'baynar1_main',
                            'begin-now-vn-system-area'
                        ];
                        if (in_array($field_name, $border_rows)) {
                            $row_style = 'style="border-top: 2px solid black;"';
                        }
                        ?>
                        <tr <?php echo $row_style; ?>>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;<?php echo in_array($field_name, $border_rows) ? ' border-top: 2px solid black;' : ''; ?>">
                                <input type="checkbox" name="field_<?php echo esc_attr($field_name); ?>" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;<?php echo in_array($field_name, $border_rows) ? ' border-top: 2px solid black;' : ''; ?>">
                                <?php 
                                // Fields that are omitted from database updates
                                $omitted_fields = [
                                    'begin-now-vn-system-area',
                                    'vec_disable_vn_system_sitewide',
                                    'vec_disable_vn_system_on_post',
                                    'vec_meta_title',
                                    'vec_meta_description',
                                    'meta-title-actual-output',
                                    'meta-description-actual-output'
                                ];
                                
                                if (strpos($field_name, 'post_') === 0) {
                                    echo 'wp_posts';
                                } elseif ($field_name === 'vec_disable_vn_system_sitewide') {
                                    echo 'omitted from db update(_zen_sit..)';
                                } elseif (in_array($field_name, $omitted_fields)) {
                                    echo 'omitted from db update';
                                }
                                ?>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;<?php echo in_array($field_name, $border_rows) ? ' border-top: 2px solid black;' : ''; ?>">
                                <?php echo esc_html($field_name); ?>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;<?php echo in_array($field_name, $border_rows) ? ' border-top: 2px solid black;' : ''; ?>">
                                <?php
                                $value = '';
                                if ($table_source === 'posts' && isset($post_data[$field_name])) {
                                    $value = $post_data[$field_name];
                                } elseif ($table_source === 'pylons' && isset($pylon_data[$field_name])) {
                                    $value = $pylon_data[$field_name];
                                }
                                
                                // CRITICAL: Remove any existing slashes from database values before display
                                // This handles data that was previously saved with unwanted slashes
                                $value = stripslashes_deep($value);
                                $value = wp_unslash($value);
                                $value = stripslashes($value);
                                ?>
                                <?php if ($field_name === 'post_content'): ?>
                                    <textarea name="field_<?php echo esc_attr($field_name); ?>" 
                                             style="width: 100%; height: 150px; border: 1px solid #ccc; padding: 4px; font-family: monospace; font-size: 12px; resize: vertical;"><?php echo esc_textarea($value); ?></textarea>
                                <?php elseif (strpos($field_name, 'serena_faq_box_a') === 0): ?>
                                    <textarea name="field_<?php echo esc_attr($field_name); ?>" 
                                             style="width: 100%; height: 80px; border: 1px solid #ccc; padding: 4px; font-family: monospace; font-size: 12px; resize: vertical;"
                                             placeholder="Enter FAQ answer..."><?php echo esc_textarea($value); ?></textarea>
                                <?php elseif (strpos($field_name, 'chenblock_card') === 0 && strpos($field_name, '_desc') !== false): ?>
                                    <textarea name="field_<?php echo esc_attr($field_name); ?>" 
                                             style="width: 100%; height: 80px; border: 1px solid #ccc; padding: 4px; font-family: monospace; font-size: 12px; resize: vertical;"
                                             placeholder="Enter card description..."><?php echo esc_textarea($value); ?></textarea>
                                <?php elseif ($field_name === 'vec_meta_description'): ?>
                                    <textarea name="field_<?php echo esc_attr($field_name); ?>" 
                                             style="width: 100%; height: 80px; border: 1px solid #ccc; padding: 4px; font-family: monospace; font-size: 12px; resize: vertical;"
                                             placeholder="Enter SEO meta description..."><?php echo esc_textarea($value); ?></textarea>
                                <?php elseif ($field_name === 'vec_disable_vn_system_sitewide'): ?>
                                    <label style="display: flex; align-items: center; cursor: pointer;">
                                        <input type="checkbox" 
                                               name="field_<?php echo esc_attr($field_name); ?>" 
                                               value="1"
                                               <?php checked($value, 1); ?>
                                               style="margin-right: 8px; transform: scale(1.2);" />
                                        <span style="font-weight: 600;">Disable VN System Sitewide</span>
                                    </label>
                                <?php elseif ($field_name === 'vec_disable_vn_system_on_post'): ?>
                                    <label style="display: flex; align-items: center; cursor: pointer;">
                                        <input type="checkbox" 
                                               name="field_<?php echo esc_attr($field_name); ?>" 
                                               value="1"
                                               <?php checked($value, 1); ?>
                                               style="margin-right: 8px; transform: scale(1.2);" />
                                        <span style="font-weight: 600;">Disable VN System on Post</span>
                                    </label>
                                <?php elseif ($field_name === 'meta-title-actual-output' || $field_name === 'meta-description-actual-output'): ?>
                                    <input type="text" 
                                           name="field_<?php echo esc_attr($field_name); ?>" 
                                           value="<?php echo esc_attr($value); ?>" 
                                           style="width: 100%; border: 1px solid #ccc; padding: 4px; background-color: #f0f0f0; cursor: not-allowed; color: #666;" 
                                           readonly
                                           disabled />
                                <?php elseif ($field_name === 'pylon_id' || $field_name === 'rel_plasma_page_id' || $field_name === 'rel_wp_post_id'): ?>
                                    <input type="text" 
                                           name="field_<?php echo esc_attr($field_name); ?>" 
                                           value="<?php echo esc_attr($value); ?>" 
                                           style="width: 100%; border: 1px solid #ccc; padding: 4px; background-color: #e9e9e9; cursor: not-allowed;" 
                                           readonly
                                           disabled />
                                <?php else: ?>
                                    <?php 
                                    $placeholder = '';
                                    if (strpos($field_name, 'serena_faq_box_q') === 0) {
                                        $placeholder = 'Enter FAQ question...';
                                    } elseif ($field_name === 'vec_meta_title') {
                                        $placeholder = 'Enter SEO meta title...';
                                    }
                                    ?>
                                    <input type="text" 
                                           name="field_<?php echo esc_attr($field_name); ?>" 
                                           id="field_<?php echo esc_attr($field_name); ?>"
                                           value="<?php echo esc_attr($value); ?>" 
                                           <?php if ($placeholder): ?>placeholder="<?php echo esc_attr($placeholder); ?>"<?php endif; ?>
                                           style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                                <?php endif; ?>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;<?php echo in_array($field_name, $border_rows) ? ' border-top: 2px solid black;' : ''; ?>">
                                <?php
                                // Special case for paragon_featured_image_id field
                                if ($field_name === 'paragon_featured_image_id') {
                                    ?>
                                    <button type="button" 
                                            class="button select-paragon-image" 
                                            data-field="field_<?php echo esc_attr($field_name); ?>"
                                            style="background: #0073aa; color: white; border: none; padding: 5px 12px; cursor: pointer; border-radius: 3px;">
                                        Select Image
                                    </button>
                                    <?php
                                }
                                // Special case for exempt_from_silkweaver_menu_dynamical field
                                elseif ($field_name === 'exempt_from_silkweaver_menu_dynamical') {
                                    echo 'NULL or 0 (not 1)';
                                }
                                // Otherwise completely blank
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
            </div> <!-- End Main Tab 1a -->
            
            <!-- Our Services Box Config Tab -->
            <div id="our-services-config" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">Our Services Configuration</h3>
                
                <div style="background: white; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="margin-top: 0; color: #333;">Display Settings</h4>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="background: #e8f5e8;">
                            <td style="padding: 10px 15px; width: 200px; font-weight: 600; color: #555;">Enable Our Services Box:</td>
                            <td style="padding: 10px 15px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" 
                                           id="osb_is_enabled" 
                                           name="field_osb_is_enabled" 
                                           value="1"
                                           <?php checked($pylon_data['osb_is_enabled'] ?? 0, 1); ?>
                                           style="margin-right: 8px; transform: scale(1.2);" />
                                    <span style="font-weight: 600;">Show Our Services section on homepage</span>
                                </label>
                                <small style="color: #666; display: block; margin-top: 5px;">When enabled, the Our Services box will appear before the footer on the homepage</small>
                                <small style="color: #888; font-style: italic; margin-left: 10px;">DB column: osb_is_enabled</small>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 15px; width: 200px; font-weight: 600; color: #555;">Section Title:</td>
                            <td style="padding: 10px 15px;">
                                <input type="text" 
                                       id="osb_box_title" 
                                       name="field_osb_box_title" 
                                       value="<?php echo esc_attr($pylon_data['osb_box_title'] ?? 'Our Services'); ?>"
                                       style="width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" 
                                       placeholder="Our Services" />
                                <small style="color: #666; margin-left: 10px;">Default: "Our Services"</small>
                                <small style="color: #888; font-style: italic; margin-left: 10px;">DB column: osb_box_title</small>
                            </td>
                        </tr>
                        <tr style="background: #f8f9fa;">
                            <td style="padding: 10px 15px; font-weight: 600; color: #555;">Cards Per Row:</td>
                            <td style="padding: 10px 15px;">
                                <select id="osb_services_per_row" name="field_osb_services_per_row" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="3" <?php selected($pylon_data['osb_services_per_row'] ?? 4, 3); ?>>3 per row</option>
                                    <option value="4" <?php selected($pylon_data['osb_services_per_row'] ?? 4, 4); ?>>4 per row (default)</option>
                                    <option value="5" <?php selected($pylon_data['osb_services_per_row'] ?? 4, 5); ?>>5 per row</option>
                                </select>
                                <small style="color: #666; margin-left: 10px;">Desktop layout (auto-responsive on mobile)</small>
                                <small style="color: #888; font-style: italic; margin-left: 10px;">DB column: osb_services_per_row</small>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 15px; font-weight: 600; color: #555;">Max Services to Show:</td>
                            <td style="padding: 10px 15px;">
                                <input type="number" 
                                       id="osb_max_services_display" 
                                       name="field_osb_max_services_display" 
                                       value="<?php echo esc_attr($pylon_data['osb_max_services_display'] ?? 0); ?>" 
                                       min="0" 
                                       style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
                                <small style="color: #666; margin-left: 10px;">0 = Show all services</small>
                                <small style="color: #888; font-style: italic; margin-left: 10px;">DB column: osb_max_services_display</small>
                            </td>
                        </tr>
                    </table>
                    
                    <h4 style="margin: 30px 0 15px 0; color: #333;">Service Pages Management</h4>
                    
                    <div style="margin-bottom: 20px;">
                        <button type="button" 
                                id="refresh-services-list"
                                style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                            Refresh Services List
                        </button>
                        <small style="color: #666; margin-left: 10px;">Load latest service pages</small>
                    </div>
                    
                    <div id="services-management-area">
                        <p style="color: #666; font-style: italic;">Click "Refresh Services List" to load service pages for configuration.</p>
                    </div>
                    
                </div>
            </div>
            
    </div>
    
    <script>
    function createMissingPylon(postId) {
        if (!postId) return;
        
        // Disable button during request
        const btn = document.getElementById('create-pylon-btn');
        btn.disabled = true;
        btn.style.background = '#ccc';
        btn.innerHTML = 'Creating...';
        
        // AJAX request
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'create_missing_pylon',
                post_id: postId,
                nonce: '<?php echo wp_create_nonce('create_pylon_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pylon created successfully!');
                location.reload(); // Reload to show updated data
            } else {
                showDioptraErrorModal(data.data || { message: 'Failed to create pylon' });
                // Re-enable button on error
                btn.disabled = false;
                btn.style.background = '#28a745';
                btn.innerHTML = 'create missing pylon';
            }
        })
        .catch(error => {
            showDioptraErrorModal({ 
                message: 'Network error during pylon creation', 
                details: { error: error.toString() } 
            });
            // Re-enable button on error
            btn.disabled = false;
            btn.style.background = '#28a745';
            btn.innerHTML = 'create missing pylon';
        });
    }
    
    // Tab switching functionality
    function switchDioptraTab(targetTabId) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.dioptra-tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
        });
        
        // Remove active class from all buttons
        const tabButtons = document.querySelectorAll('.dioptra-tab-btn');
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = '#f1f1f1';
            btn.style.color = '#666';
        });
        
        // Show target tab content
        const targetTab = document.getElementById(targetTabId);
        if (targetTab) {
            targetTab.style.display = 'block';
        }
        
        // Activate corresponding button
        const targetButton = document.querySelector(`[data-tab="${targetTabId}"]`);
        if (targetButton) {
            targetButton.classList.add('active');
            targetButton.style.background = '#0073aa';
            targetButton.style.color = 'white';
        }
    }
    
    // Initialize tab system
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.dioptra-tab-btn');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                switchDioptraTab(targetTab);
            });
        });
        
        // Media selector for paragon_featured_image_id
        const imageSelectButtons = document.querySelectorAll('.select-paragon-image');
        imageSelectButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const fieldName = this.getAttribute('data-field');
                const inputField = document.getElementById(fieldName);
                
                console.log('Button clicked, field name:', fieldName);
                console.log('Input field found:', inputField);
                
                // Create media frame
                const mediaFrame = wp.media({
                    title: 'Select Featured Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                });
                
                // When an image is selected
                mediaFrame.on('select', function() {
                    const attachment = mediaFrame.state().get('selection').first().toJSON();
                    console.log('Image selected, attachment ID:', attachment.id);
                    
                    if (inputField) {
                        inputField.value = attachment.id;
                        console.log('Input field value set to:', inputField.value);
                        
                        // Add visual feedback
                        inputField.style.backgroundColor = '#e7f3e7';
                        setTimeout(() => {
                            inputField.style.backgroundColor = '';
                        }, 2000);
                    } else {
                        console.error('Input field not found for:', fieldName);
                    }
                });
                
                // Open the media frame
                mediaFrame.open();
            });
        });
        
        // Refresh services list functionality
        const refreshBtn = document.getElementById('refresh-services-list');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                refreshServicesList();
            });
        }
    });
    
    // Refresh services list function
    function refreshServicesList() {
        const btn = document.getElementById('refresh-services-list');
        const managementArea = document.getElementById('services-management-area');
        
        btn.disabled = true;
        btn.innerHTML = 'Loading...';
        btn.style.background = '#6c757d';
        
        // AJAX call to get services
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'dioptra_get_services_list',
                nonce: '<?php echo wp_create_nonce('dioptra_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                managementArea.innerHTML = data.data.html;
            } else {
                managementArea.innerHTML = '<p style="color: #dc3545;">Error: ' + (data.data.message || 'Failed to load services') + '</p>';
            }
        })
        .catch(error => {
            managementArea.innerHTML = '<p style="color: #dc3545;">Error: ' + error + '</p>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Refresh Services List';
            btn.style.background = '#28a745';
        });
    }
    
    // Save dioptra data functionality
    document.getElementById('dioptra-save-btn').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        
        // Collect all input data
        const formData = new FormData();
        formData.append('action', 'dioptra_save_data');
        formData.append('post_id', <?php echo $post_id; ?>);
        formData.append('nonce', '<?php echo wp_create_nonce('dioptra_save_nonce'); ?>');
        
        // Get all inputs from the table
        const inputs = document.querySelectorAll('input[name^="field_"], textarea[name^="field_"], select[name^="field_"]');
        
        // Debug: Log all fields being collected
        console.log('Dioptra Save - Collecting fields:', inputs.length);
        
        inputs.forEach(input => {
            if (input.type === 'checkbox') {
                // For checkboxes, send 1 if checked, 0 if unchecked
                const checkboxValue = input.checked ? '1' : '0';
                formData.append(input.name, checkboxValue);
                console.log(`Checkbox: ${input.name} = ${checkboxValue} (checked: ${input.checked})`);
            } else {
                formData.append(input.name, input.value);
                console.log(`Field: ${input.name} = ${input.value}`);
            }
        });
        
        // Update button state
        btn.disabled = true;
        btn.style.background = '#ccc';
        btn.innerHTML = 'Saving...';
        
        // AJAX request
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Show debug information
            showSaveDebugInfo(data, formData);
            
            if (data.success) {
                btn.style.background = '#46b450';
                btn.innerHTML = 'Saved!';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.style.background = '#0073aa';
                    btn.innerHTML = originalText;
                }, 2000);
            } else {
                showDioptraErrorModal(data.data || { message: 'Failed to save data' });
                btn.disabled = false;
                btn.style.background = '#0073aa';
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            showDioptraErrorModal({ 
                message: 'Network or JavaScript error', 
                details: { error: error.toString() } 
            });
            btn.disabled = false;
            btn.style.background = '#0073aa';
            btn.innerHTML = originalText;
        });
    });

    // Custom error modal function
    function showDioptraErrorModal(errorData) {
        // Remove existing modal if present
        const existingModal = document.getElementById('dioptra-error-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Create modal HTML
        const modal = document.createElement('div');
        modal.id = 'dioptra-error-modal';
        modal.innerHTML = `
            <div class="dioptra-modal-overlay" onclick="closeDioptraErrorModal()">
                <div class="dioptra-modal-content" onclick="event.stopPropagation()">
                    <div class="dioptra-modal-header">
                        <h3>🚫 Dioptra Save Error</h3>
                        <button class="dioptra-modal-close" onclick="closeDioptraErrorModal()">&times;</button>
                    </div>
                    <div class="dioptra-modal-body">
                        <div class="error-message">
                            <strong>Error:</strong> ${errorData.message || 'Unknown error occurred'}
                        </div>
                        ${errorData.details ? `
                            <div class="error-details">
                                <h4>📊 Diagnostic Information:</h4>
                                <div class="error-detail-grid">
                                    ${errorData.details.post_id ? `<div><strong>Post ID:</strong> ${errorData.details.post_id}</div>` : ''}
                                    ${errorData.details.post_data_count !== undefined ? `<div><strong>Post Fields:</strong> ${errorData.details.post_data_count}</div>` : ''}
                                    ${errorData.details.pylon_data_count !== undefined ? `<div><strong>Pylon Fields:</strong> ${errorData.details.pylon_data_count}</div>` : ''}
                                    ${errorData.details.pylon_record_exists !== undefined ? `<div><strong>Pylon Record Exists:</strong> ${errorData.details.pylon_record_exists ? 'Yes' : 'No'}</div>` : ''}
                                    ${errorData.details.post_update_attempted !== undefined ? `<div><strong>Post Update Attempted:</strong> ${errorData.details.post_update_attempted ? 'Yes' : 'No'}</div>` : ''}
                                    ${errorData.details.pylon_update_attempted !== undefined ? `<div><strong>Pylon Update Attempted:</strong> ${errorData.details.pylon_update_attempted ? 'Yes' : 'No'}</div>` : ''}
                                </div>
                                ${errorData.details.db_error ? `<div class="db-error"><strong>Database Error:</strong> ${errorData.details.db_error}</div>` : ''}
                            </div>
                        ` : ''}
                        ${errorData.debug_info ? `
                            <div class="debug-info">
                                <h4>🔍 Debug Information:</h4>
                                ${errorData.debug_info.post_fields && errorData.debug_info.post_fields.length > 0 ? `<div><strong>Post Fields:</strong> ${errorData.debug_info.post_fields.join(', ')}</div>` : '<div><strong>Post Fields:</strong> None processed</div>'}
                                ${errorData.debug_info.pylon_fields && errorData.debug_info.pylon_fields.length > 0 ? `<div><strong>Pylon Fields:</strong> ${errorData.debug_info.pylon_fields.join(', ')}</div>` : '<div><strong>Pylon Fields:</strong> None processed</div>'}
                                ${errorData.debug_info.raw_post_keys ? `<div><strong>Raw POST Keys:</strong> ${errorData.debug_info.raw_post_keys.join(', ')}</div>` : ''}
                                ${errorData.debug_info.field_prefixed_count !== undefined ? `<div><strong>Fields with "field_" prefix:</strong> ${errorData.debug_info.field_prefixed_count}</div>` : ''}
                            </div>
                        ` : ''}
                        <div class="error-actions">
                            <button class="btn-copy-error" onclick="copyErrorToClipboard()">📋 Copy Error Details</button>
                            <button class="btn-check-logs" onclick="window.open('/wp-admin/admin.php?page=snefuru-logs', '_blank')">📝 Check Debug Logs</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .dioptra-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: fadeIn 0.3s ease;
            }
            .dioptra-modal-content {
                background: white;
                border-radius: 8px;
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 10px 25px rgba(0,0,0,0.3);
                animation: slideIn 0.3s ease;
            }
            .dioptra-modal-header {
                background: #dc3545;
                color: white;
                padding: 15px 20px;
                border-radius: 8px 8px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .dioptra-modal-header h3 {
                margin: 0;
                font-size: 18px;
            }
            .dioptra-modal-close {
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }
            .dioptra-modal-body {
                padding: 20px;
            }
            .error-message {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 12px;
                border-radius: 4px;
                margin-bottom: 15px;
            }
            .error-details {
                background: #e2e3e5;
                border: 1px solid #d6d8db;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 15px;
            }
            .error-details h4 {
                margin: 0 0 10px 0;
                color: #495057;
            }
            .error-detail-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 10px;
            }
            .error-detail-grid > div {
                background: white;
                padding: 8px;
                border-radius: 3px;
                font-size: 13px;
            }
            .db-error {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 8px;
                border-radius: 3px;
                font-family: monospace;
                font-size: 12px;
                margin-top: 10px;
            }
            .debug-info {
                background: #d1ecf1;
                border: 1px solid #bee5eb;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 15px;
            }
            .debug-info h4 {
                margin: 0 0 10px 0;
                color: #0c5460;
            }
            .debug-info div {
                font-size: 13px;
                margin-bottom: 5px;
                font-family: monospace;
            }
            .error-actions {
                display: flex;
                gap: 10px;
                justify-content: flex-end;
            }
            .error-actions button {
                padding: 8px 16px;
                border: 1px solid #007cba;
                background: #007cba;
                color: white;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
            }
            .error-actions button:hover {
                background: #005a87;
                border-color: #005a87;
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideIn {
                from { transform: translateY(-20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        
        // Add modal and styles to document
        document.head.appendChild(style);
        document.body.appendChild(modal);
        
        // Store error data for copying
        window.dioptraErrorData = errorData;
    }

    // Show save debug information
    function showSaveDebugInfo(response, formData) {
        const debugArea = document.getElementById('save-debug-results');
        const debugContent = document.getElementById('save-debug-content');
        
        if (!debugArea || !debugContent) return;
        
        // Count form data fields
        let fieldCount = 0;
        let fieldNames = [];
        
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('field_')) {
                fieldCount++;
                fieldNames.push(key + '=' + (value.length > 30 ? value.substr(0, 30) + '...' : value));
            }
        }
        
        const timestamp = new Date().toLocaleTimeString();
        const isSuccess = response.success;
        
        debugContent.innerHTML = debugContent.innerHTML + `
            <div style="font-family: monospace; font-size: 12px; line-height: 1.4; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px;">
                <div style="color: ${isSuccess ? '#28a745' : '#dc3545'}; font-weight: bold; margin-bottom: 8px;">
                    ${timestamp} - ${isSuccess ? '✅ SAVE SUCCESS' : '❌ SAVE FAILED'}
                </div>
                
                <div style="margin-bottom: 8px;">
                    <strong>Fields Sent:</strong> ${fieldCount} fields<br>
                    <div style="margin-left: 10px; color: #666; max-height: 200px; overflow-y: auto; font-size: 10px;">
                        ${fieldNames.join('<br>')}
                    </div>
                </div>
                
                ${response.data ? `
                    <div style="margin-bottom: 8px;">
                        <strong>Response:</strong> ${response.data.message || 'No message'}
                    </div>
                ` : ''}
                
                ${response.data && response.data.details ? `
                    <div style="background: #f8f9fa; padding: 8px; border-radius: 3px; margin-top: 8px;">
                        <strong>Debug Details:</strong><br>
                        Post ID: ${response.data.details.post_id}<br>
                        Post Fields: ${response.data.details.post_data_count}<br>
                        Pylon Fields: ${response.data.details.pylon_data_count}<br>
                        Pylon Record Exists: ${response.data.details.pylon_record_exists ? 'Yes' : 'No'}<br>
                        ${response.data.details.db_error ? `DB Error: ${response.data.details.db_error}` : ''}
                    </div>
                ` : ''}
            </div>
        `;
        
        debugArea.style.display = 'block';
        
        // Keep debug info visible - don't auto-hide
    }

    function closeDioptraErrorModal() {
        const modal = document.getElementById('dioptra-error-modal');
        if (modal) {
            modal.remove();
        }
    }

    function copyErrorToClipboard() {
        const errorText = JSON.stringify(window.dioptraErrorData, null, 2);
        navigator.clipboard.writeText(errorText).then(() => {
            const btn = document.querySelector('.btn-copy-error');
            const originalText = btn.textContent;
            btn.textContent = '✅ Copied!';
            btn.style.background = '#28a745';
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = '#007cba';
            }, 2000);
        }).catch(() => {
            alert('Could not copy to clipboard. Error data logged to console.');
            console.log('Dioptra Error Data:', window.dioptraErrorData);
        });
    }

    </script>
    <?php
}