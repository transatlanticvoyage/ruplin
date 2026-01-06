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
                    <?php 
                    // Show Box Ordering tab only for cherry template posts
                    $current_template = isset($pylon_data['staircase_page_template_desired']) ? $pylon_data['staircase_page_template_desired'] : '';
                    if ($current_template === 'cherry'): 
                    ?>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="box-ordering-config"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        Box Ordering
                    </button>
                    <?php endif; ?>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="ocean1"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        Ocean1
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
            
            <!-- Box Ordering Config Tab -->
            <?php if ($current_template === 'cherry'): ?>
            <div id="box-ordering-config" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                
                <?php
                // Get current is_active value for this post
                $current_is_active = 0;
                $current_box_active = $wpdb->get_var($wpdb->prepare(
                    "SELECT is_active FROM {$box_orders_table} WHERE rel_post_id = %d ORDER BY updated_at DESC LIMIT 1",
                    $post_id
                ));
                if ($current_box_active !== null) {
                    $current_is_active = (int) $current_box_active;
                }
                ?>
                
                <!-- Box Ordering Toggle Section -->
                <div style="background: white; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <!-- Toggle Switch -->
                        <div class="box-ordering-toggle-container">
                            <label class="box-ordering-toggle-switch">
                                <input type="checkbox" 
                                       id="box-ordering-active-toggle" 
                                       <?php checked($current_is_active, 1); ?>
                                       data-post-id="<?php echo $post_id; ?>">
                                <span class="box-ordering-slider"></span>
                            </label>
                        </div>
                        
                        <!-- Toggle Text -->
                        <div style="font-size: 22px; font-weight: bold; color: #000;">
                            wp_box_orders.is_active - Mark TRUE - Activate Custom Ordering
                        </div>
                    </div>
                    
                    <!-- Toggle Status Display -->
                    <div id="toggle-status-display" style="margin-top: 15px; padding: 10px; border-radius: 4px; font-weight: 600;">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
                
                <h3 style="margin-top: 0; color: #0073aa;">Box Ordering Configuration</h3>
                
                <?php
                // Check if box ordering config exists for this post
                $box_orders_table = $wpdb->prefix . 'box_orders';
                $box_order_config = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$box_orders_table} WHERE rel_post_id = %d AND is_active = 1",
                    $post_id
                ), ARRAY_A);
                
                // Define all available boxes
                $available_boxes = [
                    'batman_hero_box',
                    'chen_cards_box',
                    'plain_post_content',
                    'osb_box',
                    'serena_faq_box',
                    'nile_map_box',
                    'kristina_cta_box',
                    'victoria_blog_box',
                    'ocean1_box',
                    'ocean2_box',
                    'ocean3_box',
                    'brook_video_box',
                    'olivia_authlinks_box',
                    'ava_whychooseus_box',
                    'kendall_ourprocess_box',
                    'sara_customhtml_box'
                ];
                
                // Get current box order or create default
                $current_order = [];
                if ($box_order_config && !empty($box_order_config['box_order_json'])) {
                    $current_order = json_decode($box_order_config['box_order_json'], true);
                    if (!$current_order) $current_order = [];
                }
                
                // If no custom order exists, use default order
                if (empty($current_order)) {
                    foreach ($available_boxes as $index => $box_name) {
                        $current_order[$box_name] = $index + 1;
                    }
                }
                ?>
                
                <div style="background: white; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    
                    <?php if (!$box_order_config): ?>
                        <!-- Default State: No custom config exists -->
                        <div id="box-ordering-default-state">
                            <div style="background: #e7f3ff; border: 1px solid #0073aa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                                <h4 style="margin: 0 0 10px 0; color: #0073aa;">Using Default Box Order</h4>
                                <p style="margin: 0 0 15px 0; color: #666;">This post is currently using the default hardcoded box order from the cherry template.</p>
                                <button type="button" 
                                        id="create-box-config-btn"
                                        style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                    Create Custom Box Order Config
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Custom Config Exists -->
                        <div id="box-ordering-config-state">
                            <h4 style="margin-top: 0; color: #333;">Custom Box Order Configuration</h4>
                            
                            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                                <!-- JSON Textarea -->
                                <div style="flex: 1;">
                                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">JSON Configuration:</label>
                                    <textarea id="box-order-json" 
                                             style="width: 100%; height: 200px; border: 1px solid #ddd; padding: 10px; font-family: monospace; font-size: 12px; resize: vertical;"><?php echo esc_textarea(json_encode($current_order, JSON_PRETTY_PRINT)); ?></textarea>
                                </div>
                                
                                <!-- Visual Box List -->
                                <div style="flex: 1;">
                                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Visual Box Order:</label>
                                    <div id="visual-box-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f8f9fa;">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center;">
                                <button type="button" 
                                        id="randomize-boxes-btn"
                                        style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                    Randomize All
                                </button>
                                
                                <div style="display: flex; gap: 10px;">
                                    <button type="button" 
                                            id="save-box-order-btn"
                                            style="background: #0073aa; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                        Save Changes
                                    </button>
                                    <button type="button" 
                                            id="cancel-box-order-btn"
                                            style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                                        Cancel
                                    </button>
                                    <button type="button" 
                                            id="delete-box-config-btn"
                                            style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                                        Delete Config
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Ocean1 Tab -->
            <div id="ocean1" class="dioptra-tab-content" style="display: none;">
            
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
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                            <input type="checkbox" name="ocean1_field_1" />
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                            <input type="checkbox" name="ocean1_field_2" />
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                            <input type="checkbox" name="ocean1_field_3" />
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                            <input type="checkbox" name="ocean1_field_4" />
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                        </td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                        </td>
                    </tr>
                </tbody>
            </table>
            
            </div> <!-- End Ocean1 Tab -->
            
    </div>
    
    <style>
    /* Professional Toggle Switch Styling */
    .box-ordering-toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        cursor: pointer;
    }

    .box-ordering-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .box-ordering-slider {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        border-radius: 34px;
        transition: all 0.4s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .box-ordering-slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        border-radius: 50%;
        transition: all 0.4s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .box-ordering-toggle-switch input:checked + .box-ordering-slider {
        background-color: #28a745;
    }

    .box-ordering-toggle-switch input:focus + .box-ordering-slider {
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.6);
    }

    .box-ordering-toggle-switch input:checked + .box-ordering-slider:before {
        transform: translateX(26px);
    }

    .box-ordering-toggle-switch:hover .box-ordering-slider {
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .box-ordering-toggle-container {
        display: flex;
        align-items: center;
    }
    </style>
    
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

    // Box Ordering JavaScript Functions
    
    // Global variable to store original box order
    let originalBoxOrder = {};
    
    // Initialize box ordering functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Store original box order
        const jsonTextarea = document.getElementById('box-order-json');
        if (jsonTextarea) {
            try {
                originalBoxOrder = JSON.parse(jsonTextarea.value);
            } catch (e) {
                originalBoxOrder = {};
            }
            updateVisualBoxList();
        }
        
        // Create box config button
        const createBtn = document.getElementById('create-box-config-btn');
        if (createBtn) {
            createBtn.addEventListener('click', function() {
                createBoxOrderConfig(<?php echo $post_id; ?>);
            });
        }
        
        // Randomize button
        const randomizeBtn = document.getElementById('randomize-boxes-btn');
        if (randomizeBtn) {
            randomizeBtn.addEventListener('click', function() {
                randomizeBoxOrder();
            });
        }
        
        // Save button
        const saveBtn = document.getElementById('save-box-order-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                saveBoxOrderConfig(<?php echo $post_id; ?>);
            });
        }
        
        // Cancel button
        const cancelBtn = document.getElementById('cancel-box-order-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                cancelBoxOrderChanges();
            });
        }
        
        // Delete button
        const deleteBtn = document.getElementById('delete-box-config-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                deleteBoxOrderConfig(<?php echo $post_id; ?>);
            });
        }
        
        // JSON textarea change handler
        if (jsonTextarea) {
            jsonTextarea.addEventListener('input', function() {
                updateVisualBoxList();
            });
        }
        
        // Box Ordering Toggle functionality
        initializeBoxOrderingToggle();
    });
    
    function createBoxOrderConfig(postId) {
        const btn = document.getElementById('create-box-config-btn');
        btn.disabled = true;
        btn.innerHTML = 'Creating...';
        btn.style.background = '#6c757d';
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'create_box_order_config',
                post_id: postId,
                nonce: '<?php echo wp_create_nonce('box_order_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDetailedFeedback(
                    'Success!',
                    'Box order configuration has been created successfully. The page will reload to show the configuration interface.',
                    'success',
                    data.data?.debug_info || null
                );
                setTimeout(() => {
                    location.reload(); // Reload to show the config interface
                }, 2000);
            } else {
                showDetailedFeedback(
                    'Configuration Creation Failed',
                    data.data?.message || data.message || 'Failed to create box order configuration',
                    'error',
                    data.data?.debug_info || data.debug_info || null
                );
            }
        })
        .catch(error => {
            showDetailedFeedback(
                'Network Error',
                'A network error occurred while trying to create the box order configuration: ' + error.message,
                'error',
                {
                    error_type: 'network_error',
                    error_message: error.message,
                    stack_trace: error.stack,
                    timestamp: new Date().toISOString(),
                    user_agent: navigator.userAgent,
                    url: window.location.href
                }
            );
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Create Custom Box Order Config';
            btn.style.background = '#28a745';
        });
    }
    
    function updateVisualBoxList() {
        const jsonTextarea = document.getElementById('box-order-json');
        const visualList = document.getElementById('visual-box-list');
        
        if (!jsonTextarea || !visualList) return;
        
        try {
            const boxOrder = JSON.parse(jsonTextarea.value);
            
            // Convert to array and sort by order value
            const sortedBoxes = Object.entries(boxOrder).sort((a, b) => a[1] - b[1]);
            
            let html = '';
            sortedBoxes.forEach((boxEntry, index) => {
                const [boxName, order] = boxEntry;
                html += `<div style="background: white; border: 1px solid #ddd; margin-bottom: 5px; padding: 8px; border-radius: 3px; font-size: 13px;">
                    <strong>${order}.</strong> ${boxName}
                </div>`;
            });
            
            visualList.innerHTML = html;
        } catch (e) {
            visualList.innerHTML = '<div style="color: #dc3545; font-style: italic;">Invalid JSON format</div>';
        }
    }
    
    function randomizeBoxOrder() {
        const jsonTextarea = document.getElementById('box-order-json');
        if (!jsonTextarea) return;
        
        try {
            const boxOrder = JSON.parse(jsonTextarea.value);
            const boxNames = Object.keys(boxOrder);
            
            // Shuffle the order numbers
            const orderNumbers = Object.values(boxOrder).sort(() => Math.random() - 0.5);
            
            // Create new randomized order
            const newOrder = {};
            boxNames.forEach((boxName, index) => {
                newOrder[boxName] = orderNumbers[index];
            });
            
            jsonTextarea.value = JSON.stringify(newOrder, null, 2);
            updateVisualBoxList();
        } catch (e) {
            alert('Error: Invalid JSON format');
        }
    }
    
    function saveBoxOrderConfig(postId) {
        const jsonTextarea = document.getElementById('box-order-json');
        if (!jsonTextarea) {
            showDetailedFeedback(
                'Interface Error',
                'JSON textarea element not found. This indicates a problem with the page structure.',
                'error',
                {
                    error_type: 'dom_element_missing',
                    element_id: 'box-order-json',
                    timestamp: new Date().toISOString()
                }
            );
            return;
        }
        
        try {
            const boxOrder = JSON.parse(jsonTextarea.value);
        } catch (e) {
            showDetailedFeedback(
                'JSON Validation Error',
                'The JSON configuration contains syntax errors and cannot be saved.',
                'error',
                {
                    error_type: 'json_validation_error',
                    json_error: e.message,
                    json_content: jsonTextarea.value,
                    error_position: e.message.match(/position (\d+)/) ? e.message.match(/position (\d+)/)[1] : null,
                    timestamp: new Date().toISOString()
                }
            );
            return;
        }
        
        const btn = document.getElementById('save-box-order-btn');
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
        btn.style.background = '#6c757d';
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'save_box_order_config',
                post_id: postId,
                box_order_json: jsonTextarea.value,
                nonce: '<?php echo wp_create_nonce('box_order_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDetailedFeedback(
                    'Configuration Saved Successfully!',
                    'Your box order configuration has been saved and is now active.',
                    'success',
                    data.data?.debug_info || null
                );
                btn.innerHTML = 'Saved!';
                btn.style.background = '#28a745';
                
                // Update original order
                originalBoxOrder = JSON.parse(jsonTextarea.value);
                
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Changes';
                    btn.style.background = '#0073aa';
                }, 2000);
            } else {
                showDetailedFeedback(
                    'Save Operation Failed',
                    data.data?.message || data.message || 'Failed to save box order configuration',
                    'error',
                    data.data?.debug_info || data.debug_info || null
                );
                btn.disabled = false;
                btn.innerHTML = 'Save Changes';
                btn.style.background = '#0073aa';
            }
        })
        .catch(error => {
            showDetailedFeedback(
                'Network Error During Save',
                'A network error occurred while trying to save the configuration: ' + error.message,
                'error',
                {
                    error_type: 'network_error',
                    error_message: error.message,
                    stack_trace: error.stack,
                    timestamp: new Date().toISOString(),
                    user_agent: navigator.userAgent,
                    url: window.location.href
                }
            );
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
            btn.style.background = '#0073aa';
        });
    }
    
    function cancelBoxOrderChanges() {
        const jsonTextarea = document.getElementById('box-order-json');
        if (!jsonTextarea) return;
        
        jsonTextarea.value = JSON.stringify(originalBoxOrder, null, 2);
        updateVisualBoxList();
    }
    
    function deleteBoxOrderConfig(postId) {
        if (!confirm('Are you sure you want to delete the custom box order configuration? This will revert to the default box order.')) {
            return;
        }
        
        const btn = document.getElementById('delete-box-config-btn');
        btn.disabled = true;
        btn.innerHTML = 'Deleting...';
        btn.style.background = '#6c757d';
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'delete_box_order_config',
                post_id: postId,
                nonce: '<?php echo wp_create_nonce('box_order_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDetailedFeedback(
                    'Configuration Deleted Successfully!',
                    'The custom box order configuration has been deleted. The page will reload to show the default configuration.',
                    'success',
                    data.data?.debug_info || null
                );
                setTimeout(() => {
                    location.reload(); // Reload to show default state
                }, 2000);
            } else {
                showDetailedFeedback(
                    'Delete Operation Failed',
                    data.data?.message || data.message || 'Failed to delete box order configuration',
                    'error',
                    data.data?.debug_info || data.debug_info || null
                );
                btn.disabled = false;
                btn.innerHTML = 'Delete Config';
                btn.style.background = '#dc3545';
            }
        })
        .catch(error => {
            showDetailedFeedback(
                'Network Error During Delete',
                'A network error occurred while trying to delete the configuration: ' + error.message,
                'error',
                {
                    error_type: 'network_error',
                    error_message: error.message,
                    stack_trace: error.stack,
                    timestamp: new Date().toISOString(),
                    user_agent: navigator.userAgent,
                    url: window.location.href
                }
            );
            btn.disabled = false;
            btn.innerHTML = 'Delete Config';
            btn.style.background = '#dc3545';
        });
    }

    // ============================================================
    // BOX ORDERING TOGGLE FUNCTIONS
    // ============================================================
    
    function initializeBoxOrderingToggle() {
        const toggle = document.getElementById('box-ordering-active-toggle');
        if (!toggle) return; // Toggle not found, probably not on Box Ordering tab
        
        // Set initial status display
        updateToggleStatusDisplay(toggle.checked);
        
        // Add event listener for toggle changes
        toggle.addEventListener('change', function() {
            const postId = this.getAttribute('data-post-id');
            const isActive = this.checked ? 1 : 0;
            
            updateBoxOrderingActive(postId, isActive, this);
        });
    }
    
    function updateToggleStatusDisplay(isActive) {
        const statusDisplay = document.getElementById('toggle-status-display');
        if (!statusDisplay) return;
        
        if (isActive) {
            statusDisplay.style.backgroundColor = '#d4edda';
            statusDisplay.style.color = '#155724';
            statusDisplay.style.border = '1px solid #c3e6cb';
            statusDisplay.innerHTML = '✅ Custom Box Ordering is ACTIVE - Box order configuration will be used if available';
        } else {
            statusDisplay.style.backgroundColor = '#f8d7da';
            statusDisplay.style.color = '#721c24';
            statusDisplay.style.border = '1px solid #f5c6cb';
            statusDisplay.innerHTML = '❌ Custom Box Ordering is INACTIVE - Default hardcoded order will be used';
        }
    }
    
    function updateBoxOrderingActive(postId, isActive, toggleElement) {
        // Disable toggle during request
        toggleElement.disabled = true;
        
        // Show loading state
        const statusDisplay = document.getElementById('toggle-status-display');
        if (statusDisplay) {
            statusDisplay.style.backgroundColor = '#fff3cd';
            statusDisplay.style.color = '#856404';
            statusDisplay.style.border = '1px solid #ffeaa7';
            statusDisplay.innerHTML = '🔄 Updating box ordering status...';
        }
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'update_box_ordering_active',
                post_id: postId,
                is_active: isActive,
                nonce: '<?php echo wp_create_nonce('box_order_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update status display to reflect new state
                updateToggleStatusDisplay(isActive === 1);
                
                // Show success feedback briefly
                showToggleSuccessFeedback(isActive === 1 ? 'Custom ordering activated!' : 'Custom ordering deactivated!');
            } else {
                // Revert toggle state on error
                toggleElement.checked = !toggleElement.checked;
                updateToggleStatusDisplay(toggleElement.checked);
                
                showDetailedFeedback(
                    'Toggle Update Failed',
                    data.data?.message || data.message || 'Failed to update box ordering status',
                    'error',
                    data.data?.debug_info || data.debug_info || null
                );
            }
        })
        .catch(error => {
            // Revert toggle state on error
            toggleElement.checked = !toggleElement.checked;
            updateToggleStatusDisplay(toggleElement.checked);
            
            showDetailedFeedback(
                'Network Error',
                'A network error occurred while updating box ordering status: ' + error.message,
                'error',
                {
                    error_type: 'network_error',
                    error_message: error.message,
                    timestamp: new Date().toISOString()
                }
            );
        })
        .finally(() => {
            // Re-enable toggle
            toggleElement.disabled = false;
        });
    }
    
    function showToggleSuccessFeedback(message) {
        const tempDiv = document.createElement('div');
        tempDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            z-index: 1000000;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideInRight 0.3s ease-out;
        `;
        tempDiv.innerHTML = `✅ ${message}`;
        document.body.appendChild(tempDiv);
        
        // Add slide in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { 
                    opacity: 0;
                    transform: translateX(100px); 
                }
                to { 
                    opacity: 1;
                    transform: translateX(0); 
                }
            }
            @keyframes slideOutRight {
                from { 
                    opacity: 1;
                    transform: translateX(0); 
                }
                to { 
                    opacity: 0;
                    transform: translateX(100px); 
                }
            }
        `;
        document.head.appendChild(style);
        
        setTimeout(() => {
            tempDiv.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                tempDiv.remove();
                style.remove();
            }, 300);
        }, 3000);
    }

    // ============================================================
    // COMPREHENSIVE ERROR FEEDBACK SYSTEM
    // ============================================================
    
    /**
     * Show detailed error or success feedback with professional styling
     */
    function showDetailedFeedback(title, message, type = 'error', debugInfo = null) {
        // Remove existing feedback popup if present
        const existingPopup = document.getElementById('detailed-feedback-popup');
        if (existingPopup) {
            existingPopup.remove();
        }
        
        // Create popup overlay
        const overlay = document.createElement('div');
        overlay.id = 'detailed-feedback-popup';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-out;
        `;
        
        // Create popup container
        const popup = document.createElement('div');
        const isError = type === 'error';
        const borderColor = isError ? '#dc3545' : '#28a745';
        const headerBg = isError ? '#dc3545' : '#28a745';
        const iconSymbol = isError ? '⚠️' : '✅';
        
        popup.style.cssText = `
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 90%;
            width: 800px;
            max-height: 90vh;
            overflow: hidden;
            animation: slideUp 0.3s ease-out;
            border: 2px solid ${borderColor};
        `;
        
        // Format debug info for display
        let debugSection = '';
        if (debugInfo) {
            const formattedDebug = JSON.stringify(debugInfo, null, 2);
            debugSection = `
                <div class="debug-section" style="margin-top: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                        <h4 style="margin: 0; color: #495057; font-size: 14px; font-weight: 600;">🔍 Debug Information</h4>
                        <button id="copy-debug-btn" style="
                            background: #6c757d; 
                            color: white; 
                            border: none; 
                            padding: 6px 12px; 
                            border-radius: 6px; 
                            cursor: pointer; 
                            font-size: 12px;
                            font-weight: 500;
                            transition: background 0.2s;
                        " onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                            📋 Copy Debug Info
                        </button>
                    </div>
                    <div style="
                        background: #f8f9fa; 
                        border: 1px solid #dee2e6; 
                        border-radius: 8px; 
                        padding: 15px; 
                        max-height: 300px; 
                        overflow-y: auto;
                        font-family: 'Courier New', monospace;
                        font-size: 11px;
                        line-height: 1.4;
                        white-space: pre-wrap;
                        word-break: break-all;
                    " id="debug-content">${escapeHtml(formattedDebug)}</div>
                </div>
            `;
        }
        
        popup.innerHTML = `
            <div style="
                background: ${headerBg}; 
                color: white; 
                padding: 20px; 
                display: flex; 
                align-items: center; 
                justify-content: space-between;
            ">
                <div style="display: flex; align-items: center;">
                    <span style="font-size: 24px; margin-right: 12px;">${iconSymbol}</span>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 600;">${escapeHtml(title)}</h3>
                </div>
                <button id="close-feedback-btn" style="
                    background: rgba(255,255,255,0.2); 
                    border: none; 
                    color: white; 
                    width: 32px; 
                    height: 32px; 
                    border-radius: 50%; 
                    cursor: pointer; 
                    font-size: 18px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.2s;
                " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">×</button>
            </div>
            <div style="padding: 25px;">
                <div style="
                    background: ${isError ? '#f8d7da' : '#d4edda'}; 
                    color: ${isError ? '#721c24' : '#155724'}; 
                    padding: 15px; 
                    border-radius: 8px; 
                    margin-bottom: 20px;
                    border-left: 4px solid ${borderColor};
                ">
                    <p style="margin: 0; font-size: 14px; line-height: 1.5;">${escapeHtml(message)}</p>
                </div>
                
                <div style="
                    background: #e3f2fd; 
                    border: 1px solid #1976d2; 
                    border-radius: 8px; 
                    padding: 15px; 
                    margin-bottom: 15px;
                ">
                    <h4 style="margin: 0 0 10px 0; color: #1976d2; font-size: 14px;">📋 Quick Actions</h4>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button id="copy-message-btn" style="
                            background: #1976d2; 
                            color: white; 
                            border: none; 
                            padding: 8px 16px; 
                            border-radius: 6px; 
                            cursor: pointer; 
                            font-size: 13px;
                            font-weight: 500;
                            transition: background 0.2s;
                        " onmouseover="this.style.background='#1565c0'" onmouseout="this.style.background='#1976d2'">
                            Copy Message
                        </button>
                        <button onclick="location.reload()" style="
                            background: #ff9800; 
                            color: white; 
                            border: none; 
                            padding: 8px 16px; 
                            border-radius: 6px; 
                            cursor: pointer; 
                            font-size: 13px;
                            font-weight: 500;
                            transition: background 0.2s;
                        " onmouseover="this.style.background='#f57c00'" onmouseout="this.style.background='#ff9800'">
                            🔄 Refresh Page
                        </button>
                    </div>
                </div>
                
                ${debugSection}
                
                <div style="text-align: right; margin-top: 25px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                    <small style="color: #6c757d; font-size: 12px;">
                        🕒 ${new Date().toLocaleString()} | Environment: WordPress ${wpVersion || 'Unknown'}
                    </small>
                </div>
            </div>
        `;
        
        // Add animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { 
                    opacity: 0;
                    transform: translateY(30px) scale(0.95); 
                }
                to { 
                    opacity: 1;
                    transform: translateY(0) scale(1); 
                }
            }
        `;
        document.head.appendChild(style);
        
        overlay.appendChild(popup);
        document.body.appendChild(overlay);
        
        // Event handlers
        document.getElementById('close-feedback-btn').addEventListener('click', closeFeedbackPopup);
        document.getElementById('copy-message-btn').addEventListener('click', () => copyToClipboard(message, 'Message copied!'));
        
        if (debugInfo) {
            document.getElementById('copy-debug-btn').addEventListener('click', () => {
                copyToClipboard(JSON.stringify(debugInfo, null, 2), 'Debug info copied!');
            });
        }
        
        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeFeedbackPopup();
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFeedbackPopup();
            }
        });
    }
    
    function closeFeedbackPopup() {
        const popup = document.getElementById('detailed-feedback-popup');
        if (popup) {
            popup.style.animation = 'fadeOut 0.2s ease-in';
            setTimeout(() => popup.remove(), 200);
        }
    }
    
    function copyToClipboard(text, successMessage = 'Copied!') {
        navigator.clipboard.writeText(text).then(() => {
            // Show temporary success message
            const tempDiv = document.createElement('div');
            tempDiv.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #28a745;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                z-index: 1000000;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                animation: fadeIn 0.3s ease-out;
            `;
            tempDiv.textContent = successMessage;
            document.body.appendChild(tempDiv);
            
            setTimeout(() => {
                tempDiv.style.animation = 'fadeOut 0.2s ease-in';
                setTimeout(() => tempDiv.remove(), 200);
            }, 1500);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        });
    }
    
    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    </script>
    <?php
}