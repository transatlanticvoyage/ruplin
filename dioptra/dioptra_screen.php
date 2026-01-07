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
            
            // TEMPORARY DEBUG: Check if kendall data is in database
            error_log("KENDALL PAGE LOAD DEBUG: kendall_our_process_heading = '" . ($pylon_data['kendall_our_process_heading'] ?? 'NULL') . "'");
            error_log("KENDALL PAGE LOAD DEBUG: kendall_our_process_subheading = '" . ($pylon_data['kendall_our_process_subheading'] ?? 'NULL') . "'");
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
        // Kendall fields removed from main table - they're handled separately in the Kendall tab
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
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="box-ordering-config"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        Box Ordering
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="box-ordering-tab-2"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        Box Ordering Tab 2 (original)
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="batman-hero"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        batman_hero
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="chenblock-card"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        chenblock_card
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="post-content"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        post_content
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="serena-faq"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        serena_faq
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="brook-video-box-box"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        brook_video_box_box
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="olivia-authlinks-box"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        olivia_authlinks_box
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="ava-whychooseus-box"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        ava_whychooseus_box
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="kendall-ourprocess-box"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        kendall_ourprocess_box
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="sara-customhtml-box"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        sara_customhtml_box
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="ocean1"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        ocean1
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="ocean2"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        ocean2
                    </button>
                    <button type="button" 
                            class="dioptra-tab-btn" 
                            data-tab="ocean3"
                            style="background: #f1f1f1; color: #666; border: none; padding: 10px 20px; margin-right: 3px; cursor: pointer; font-weight: 600; border-top-left-radius: 6px; border-top-right-radius: 6px;">
                        ocean3
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
            <div id="box-ordering-config" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">Box Ordering Configuration</h3>
                
                <?php
                // Get box order data from wp_box_orders table
                $box_orders_table = $wpdb->prefix . 'box_orders';
                $box_order_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$box_orders_table} WHERE rel_post_id = %d",
                    $post_id
                ), ARRAY_A);
                
                $is_active = $box_order_data ? (bool)$box_order_data['is_active'] : false;
                $box_order_json = $box_order_data ? $box_order_data['box_order_json'] : '';
                $config_exists = !empty($box_order_data);
                ?>
                
                <div style="background: white; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    
                    <!-- Row Status Check -->
                    <div style="background: #e7f3ff; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #0073aa;">
                        <p style="margin: 0; font-weight: 600; color: #333;">
                            wp_box_orders row exists for this post: <strong style="color: <?php echo $config_exists ? '#28a745' : '#dc3545'; ?>;"><?php echo $config_exists ? 'YES' : 'NO'; ?></strong>
                        </p>
                        <?php if (!$config_exists): ?>
                        <button type="button" 
                                id="create-box-order-row"
                                style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; margin-top: 10px;">
                            create wp_box_orders row for this post
                        </button>
                        <?php else: ?>
                        <button type="button" 
                                id="create-box-order-row"
                                style="background: #ccc; color: #666; border: none; padding: 10px 20px; border-radius: 4px; cursor: not-allowed; font-weight: 600; margin-top: 10px;"
                                disabled>
                            create wp_box_orders row for this post
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($config_exists): ?>
                    <!-- Toggle Switch and Content - Only show if row exists -->
                    <div id="box-order-content-area">
                        <!-- Toggle Switch for Active Status -->
                        <div style="background: #e8f5e8; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                            <h4 style="margin-top: 0; color: #333;">Activation Settings</h4>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <span style="margin-right: 15px; font-weight: 600; color: #555;">wp_box_orders.is_active - Mark TRUE - Activate Custom Ordering</span>
                                <div class="box-order-toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                                    <input type="checkbox" 
                                           id="box_order_is_active" 
                                           name="box_order_is_active" 
                                           value="1"
                                           <?php checked($is_active, true); ?>
                                           style="opacity: 0; width: 0; height: 0;" />
                                    <span class="box-order-toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo $is_active ? '#4CAF50' : '#ccc'; ?>; transition: .4s; border-radius: 34px;">
                                        <span style="position: absolute; content: ''; height: 26px; width: 26px; left: <?php echo $is_active ? '30px' : '4px'; ?>; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                                    </span>
                                </div>
                                <span style="margin-left: 10px; font-weight: 600; color: <?php echo $is_active ? '#4CAF50' : '#999'; ?>;">
                                    <?php echo $is_active ? 'Active' : 'Inactive'; ?>
                                </span>
                            </label>
                            <small style="color: #666; display: block; margin-top: 5px;">Toggle to enable/disable custom box ordering for this page</small>
                        </div>
                        
                        <!-- JSON Editor -->
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: #333;">Box Ordering Configuration</h4>
                            <label for="box_order_json" style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Box Order JSON:</label>
                            <textarea id="box_order_json" 
                                      name="box_order_json" 
                                      style="width: 100%; height: 150px; border: 1px solid #ddd; border-radius: 4px; padding: 10px; font-family: monospace; font-size: 12px; resize: vertical;"
                                      placeholder='{"batman_hero_box": 1, "derek_blog_post_meta_box": 2, "chen_cards_box": 3, "plain_post_content": 4, "osb_box": 5, "serena_faq_box": 6, "nile_map_box": 7, "kristina_cta_box": 8, "victoria_blog_box": 9, "ocean1_box": 10, "ocean2_box": 11, "ocean3_box": 12, "brook_video_box": 13, "olivia_authlinks_box": 14, "ava_whychooseus_box": 15, "kendall_ourprocess_box": 16, "sara_customhtml_box": 17}'><?php echo esc_textarea($box_order_json); ?></textarea>
                            <small style="color: #666;">Edit the JSON configuration for box ordering</small>
                        </div>
                        
                        <!-- Visual Box Order Preview -->
                        <div style="margin-bottom: 20px;">
                            <h5 style="margin-bottom: 10px; color: #333;">Visual Box Order Preview:</h5>
                            <div id="box-order-preview" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 15px; min-height: 100px;">
                                <div id="box-order-preview-content">
                                    <!-- Preview will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <button type="button" 
                                    id="randomize-box-order"
                                    style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                Randomize All
                            </button>
                            <button type="button" 
                                    id="save-box-order"
                                    style="background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                Save Box Order Changes
                            </button>
                            <button type="button" 
                                    id="cancel-box-order-changes"
                                    style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                Cancel
                            </button>
                            <button type="button" 
                                    id="delete-box-config"
                                    style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                Delete Config
                            </button>
                        </div>
                        
                        <!-- Status and Messages -->
                        <div id="box-order-status-area">
                            <div id="box-order-unsaved-warning" style="display: none; background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                                You have unsaved changes to the box order configuration.
                            </div>
                            <div id="box-order-success-message" style="display: none; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                            </div>
                            <div id="box-order-error-message" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Box Ordering Tab 2 (original) -->
            <div id="box-ordering-tab-2" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">Box Ordering Configuration (Original)</h3>
                
                <?php
                // Get box order data from wp_box_orders table for Tab 2
                $box_orders_table_tab2 = $wpdb->prefix . 'box_orders';
                $box_order_data_tab2 = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$box_orders_table_tab2} WHERE rel_post_id = %d",
                    $post_id
                ), ARRAY_A);
                
                $is_active_tab2 = $box_order_data_tab2 ? (bool)$box_order_data_tab2['is_active'] : false;
                $box_order_json_tab2 = $box_order_data_tab2 ? $box_order_data_tab2['box_order_json'] : '';
                $config_exists_tab2 = !empty($box_order_data_tab2);
                ?>
                
                <div style="background: white; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    
                    <?php if (!$config_exists_tab2): ?>
                    <!-- No Config State -->
                    <div id="no-config-state-tab2">
                        <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                            <p style="margin: 0; font-weight: 600;">
                                wp_box_orders row does not exist for this post: <strong style="color: #dc3545;">NO</strong>
                            </p>
                            <button type="button" 
                                    id="create-custom-box-order-config-tab2"
                                    style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; margin-top: 10px;">
                                Create Custom Box Order Config
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Config Exists State -->
                    <div id="config-exists-state-tab2">
                        
                        <!-- Toggle Switch for Active Status -->
                        <div style="background: #e8f5e8; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                            <h4 style="margin-top: 0; color: #333;">Activation Settings</h4>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <span style="margin-right: 15px; font-weight: 600; color: #555;">wp_box_orders.is_active - Mark TRUE - Activate Custom Ordering</span>
                                <div class="box-order-toggle-switch-tab2" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                                    <input type="checkbox" 
                                           id="box_order_is_active_tab2" 
                                           name="box_order_is_active_tab2" 
                                           value="1"
                                           <?php checked($is_active_tab2, true); ?>
                                           style="opacity: 0; width: 0; height: 0;" />
                                    <span class="box-order-toggle-slider-tab2" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo $is_active_tab2 ? '#4CAF50' : '#ccc'; ?>; transition: .4s; border-radius: 34px;">
                                        <span style="position: absolute; content: ''; height: 26px; width: 26px; left: <?php echo $is_active_tab2 ? '30px' : '4px'; ?>; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                                    </span>
                                </div>
                                <span style="margin-left: 10px; font-weight: 600; color: <?php echo $is_active_tab2 ? '#4CAF50' : '#999'; ?>;">
                                    <?php echo $is_active_tab2 ? 'Active' : 'Inactive'; ?>
                                </span>
                            </label>
                            <small style="color: #666; display: block; margin-top: 5px;">Toggle to enable/disable custom box ordering for this page</small>
                        </div>
                        
                        <!-- Box Ordering Configuration Section -->
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: #333;">Box Ordering Configuration</h4>
                            <label for="box_order_json_tab2" style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Box Order JSON:</label>
                            <textarea id="box_order_json_tab2" 
                                      name="box_order_json_tab2" 
                                      style="width: 100%; height: 150px; border: 1px solid #ddd; border-radius: 4px; padding: 10px; font-family: monospace; font-size: 12px; resize: vertical;"
                                      placeholder='{"batman_hero_box": 1, "derek_blog_post_meta_box": 2, "chen_cards_box": 3, "plain_post_content": 4, "osb_box": 5, "serena_faq_box": 6, "nile_map_box": 7, "kristina_cta_box": 8, "victoria_blog_box": 9, "ocean1_box": 10, "ocean2_box": 11, "ocean3_box": 12, "brook_video_box": 13, "olivia_authlinks_box": 14, "ava_whychooseus_box": 15, "kendall_ourprocess_box": 16, "sara_customhtml_box": 17}'><?php echo esc_textarea($box_order_json_tab2); ?></textarea>
                            <small style="color: #666;">Enter JSON configuration with box names as keys and numeric order values</small>
                        </div>
                        
                        <!-- Visual Box Order Preview -->
                        <div style="margin-bottom: 20px;">
                            <h5 style="margin-bottom: 10px; color: #333;">Visual Box List (Ordered):</h5>
                            <div id="box-order-preview-tab2" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 15px; min-height: 100px;">
                                <div id="box-order-preview-content-tab2">
                                    <!-- Preview will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <button type="button" 
                                    id="randomize-all-tab2"
                                    style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                Randomize All
                            </button>
                            <button type="button" 
                                    id="save-box-order-tab2"
                                    style="background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                Save
                            </button>
                            <button type="button" 
                                    id="cancel-box-order-tab2"
                                    style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                Cancel
                            </button>
                            <button type="button" 
                                    id="delete-config-tab2"
                                    style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                Delete
                            </button>
                        </div>
                        
                        <!-- Status and Messages -->
                        <div id="box-order-status-area-tab2">
                            <div id="box-order-unsaved-warning-tab2" style="display: none; background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                                You have unsaved changes to the box order configuration.
                            </div>
                            <div id="box-order-success-message-tab2" style="display: none; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                            </div>
                            <div id="box-order-error-message-tab2" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- batman_hero Tab -->
            <div id="batman-hero" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">batman_hero</h3>
                <p>This tab is currently blank and ready for content configuration.</p>
            </div>
            
            <!-- chenblock_card Tab -->
            <div id="chenblock-card" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">chenblock_card</h3>
                <p>This tab is currently blank and ready for content configuration.</p>
            </div>
            
            <!-- post_content Tab -->
            <div id="post-content" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">post_content</h3>
                <p>This tab is currently blank and ready for content configuration.</p>
            </div>
            
            <!-- serena_faq Tab -->
            <div id="serena-faq" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">serena_faq</h3>
                <p>This tab is currently blank and ready for content configuration.</p>
            </div>
            
            <!-- brook_video_box_box Tab -->
            <div id="brook-video-box-box" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">brook_video_box_box</h3>
                <p>This tab is currently blank and ready for content configuration.</p>
            </div>
            
            <!-- olivia_authlinks_box Tab -->
            <div id="olivia-authlinks-box" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">olivia_authlinks_box</h3>
                <p>This tab is currently blank and ready for content configuration.</p>
            </div>
            
            <!-- ava_whychooseus_box Tab -->
            <div id="ava-whychooseus-box" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">ava_whychooseus_box</h3>
                <p>This tab is currently blank and ready for content configuration.</p>
            </div>
            
            <!-- kendall_ourprocess_box Tab -->
            <div id="kendall-ourprocess-box" class="dioptra-tab-content" style="display: none;">
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
                                <input type="checkbox" name="field_kendall_our_process_heading" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_heading</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_heading" 
                                       id="field_kendall_our_process_heading"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_heading'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_subheading" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_subheading</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_subheading" 
                                       id="field_kendall_our_process_subheading"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_subheading'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_description" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_description</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_description" 
                                       id="field_kendall_our_process_description"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_description'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_1" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_1</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_1" 
                                       id="field_kendall_our_process_step_1"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_1'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_2" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_2</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_2" 
                                       id="field_kendall_our_process_step_2"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_2'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_3" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_3</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_3" 
                                       id="field_kendall_our_process_step_3"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_3'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_4" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_4</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_4" 
                                       id="field_kendall_our_process_step_4"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_4'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_5" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_5</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_5" 
                                       id="field_kendall_our_process_step_5"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_5'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_6" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_6</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_6" 
                                       id="field_kendall_our_process_step_6"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_6'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_7" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_7</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_7" 
                                       id="field_kendall_our_process_step_7"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_7'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_8" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_8</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_8" 
                                       id="field_kendall_our_process_step_8"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_8'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_9" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_9</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_9" 
                                       id="field_kendall_our_process_step_9"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_9'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_kendall_our_process_step_10" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                            <td style="border: 1px solid #ccc; padding: 8px;">kendall_our_process_step_10</td>
                            <td style="border: 1px solid #ccc; padding: 8px; width: 700px; min-width: 700px; max-width: 700px;">
                                <input type="text" 
                                       name="field_kendall_our_process_step_10" 
                                       id="field_kendall_our_process_step_10"
                                       value="<?php echo esc_attr($pylon_data['kendall_our_process_step_10'] ?? ''); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- sara_customhtml_box Tab -->
            <div id="sara-customhtml-box" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                <h3 style="margin-top: 0; color: #0073aa;">sara_customhtml_box</h3>
                <p>This tab is currently blank and ready for content configuration.</p>
            </div>
            
            <!-- ocean1 Tab -->
            <div id="ocean1" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                
                <!-- Article Editor Container -->
                <div class="article-editor-container" style="background: white; border: 1px solid #ddd; border-radius: 4px;">
                    <!-- Editor Toolbar -->
                    <div class="editor-toolbar" style="background: #f5f5f5; border-bottom: 1px solid #ddd; padding: 8px 15px; display: flex; gap: 10px; align-items: center;">
                        <span style="font-weight: 600; color: #333; margin-right: 10px;">Editor Mode:</span>
                        <button type="button" 
                                id="ocean1-visual-view-btn"
                                class="editor-mode-btn active"
                                data-mode="visual"
                                style="background: #0073aa; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-size: 13px; font-weight: 600;">
                            Visual
                        </button>
                        <button type="button" 
                                id="ocean1-code-view-btn"
                                class="editor-mode-btn"
                                data-mode="code"
                                style="background: #f1f1f1; color: #666; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-size: 13px; font-weight: 600;">
                            Code
                        </button>
                        <span style="margin-left: auto; font-size: 12px; color: #666;">
                            Field: content_ocean_1
                        </span>
                    </div>
                    
                    <!-- Editor Content Area -->
                    <div class="editor-content-area" style="position: relative;">
                        <!-- Visual Editor -->
                        <div id="ocean1-visual-editor" class="editor-view" style="display: block;">
                            <textarea name="field_content_ocean_1" 
                                      id="field_content_ocean_1"
                                      style="width: 100%; height: 400px; border: none; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; font-size: 14px; line-height: 1.6; resize: vertical; outline: none;"
                                      placeholder="Enter your article content here. You can use HTML tags for formatting.&#10;&#10;Example:&#10;<h2>Your Heading</h2>&#10;<p>Your paragraph content with <strong>bold text</strong> and <em>italic text</em>.</p>&#10;<ul>&#10;    <li>List item 1</li>&#10;    <li>List item 2</li>&#10;</ul>"><?php echo esc_textarea($pylon_data['content_ocean_1'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Code Editor -->
                        <div id="ocean1-code-editor" class="editor-view" style="display: none;">
                            <textarea id="field_content_ocean_1_code"
                                      style="width: 100%; height: 400px; border: none; padding: 20px; font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; line-height: 1.4; resize: vertical; outline: none; background: #f8f9fa;"
                                      placeholder="HTML code view - edit raw HTML here"><?php echo esc_textarea($pylon_data['content_ocean_1'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Editor Footer -->
                    <div class="editor-footer" style="background: #f9f9f9; border-top: 1px solid #e9ecef; padding: 10px 20px; font-size: 12px; color: #666;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>Tips:</strong> Use Visual mode for easy editing, Code mode for HTML. Content supports paragraphs, headings, lists, links, and basic formatting.
                            </div>
                            <div>
                                Words: <span id="ocean1-word-count">0</span> | Characters: <span id="ocean1-char-count">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ocean2 Tab -->
            <div id="ocean2" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                
                <!-- Article Editor Container -->
                <div class="article-editor-container" style="background: white; border: 1px solid #ddd; border-radius: 4px;">
                    <!-- Editor Toolbar -->
                    <div class="editor-toolbar" style="background: #f5f5f5; border-bottom: 1px solid #ddd; padding: 8px 15px; display: flex; gap: 10px; align-items: center;">
                        <span style="font-weight: 600; color: #333; margin-right: 10px;">Editor Mode:</span>
                        <button type="button" 
                                id="ocean2-visual-view-btn"
                                class="editor-mode-btn active"
                                data-mode="visual"
                                style="background: #0073aa; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-size: 13px; font-weight: 600;">
                            Visual
                        </button>
                        <button type="button" 
                                id="ocean2-code-view-btn"
                                class="editor-mode-btn"
                                data-mode="code"
                                style="background: #f1f1f1; color: #666; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-size: 13px; font-weight: 600;">
                            Code
                        </button>
                        <span style="margin-left: auto; font-size: 12px; color: #666;">
                            Field: content_ocean_2
                        </span>
                    </div>
                    
                    <!-- Editor Content Area -->
                    <div class="editor-content-area" style="position: relative;">
                        <!-- Visual Editor -->
                        <div id="ocean2-visual-editor" class="editor-view" style="display: block;">
                            <textarea name="field_content_ocean_2" 
                                      id="field_content_ocean_2"
                                      style="width: 100%; height: 400px; border: none; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; font-size: 14px; line-height: 1.6; resize: vertical; outline: none;"
                                      placeholder="Enter your article content here. You can use HTML tags for formatting.&#10;&#10;Example:&#10;<h2>Your Heading</h2>&#10;<p>Your paragraph content with <strong>bold text</strong> and <em>italic text</em>.</p>&#10;<ul>&#10;    <li>List item 1</li>&#10;    <li>List item 2</li>&#10;</ul>"><?php echo esc_textarea($pylon_data['content_ocean_2'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Code Editor -->
                        <div id="ocean2-code-editor" class="editor-view" style="display: none;">
                            <textarea id="field_content_ocean_2_code"
                                      style="width: 100%; height: 400px; border: none; padding: 20px; font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; line-height: 1.4; resize: vertical; outline: none; background: #f8f9fa;"
                                      placeholder="HTML code view - edit raw HTML here"><?php echo esc_textarea($pylon_data['content_ocean_2'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Editor Footer -->
                    <div class="editor-footer" style="background: #f9f9f9; border-top: 1px solid #e9ecef; padding: 10px 20px; font-size: 12px; color: #666;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>Tips:</strong> Use Visual mode for easy editing, Code mode for HTML. Content supports paragraphs, headings, lists, links, and basic formatting.
                            </div>
                            <div>
                                Words: <span id="ocean2-word-count">0</span> | Characters: <span id="ocean2-char-count">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ocean3 Tab -->
            <div id="ocean3" class="dioptra-tab-content" style="display: none; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none;">
                
                <!-- Article Editor Container -->
                <div class="article-editor-container" style="background: white; border: 1px solid #ddd; border-radius: 4px;">
                    <!-- Editor Toolbar -->
                    <div class="editor-toolbar" style="background: #f5f5f5; border-bottom: 1px solid #ddd; padding: 8px 15px; display: flex; gap: 10px; align-items: center;">
                        <span style="font-weight: 600; color: #333; margin-right: 10px;">Editor Mode:</span>
                        <button type="button" 
                                id="ocean3-visual-view-btn"
                                class="editor-mode-btn active"
                                data-mode="visual"
                                style="background: #0073aa; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-size: 13px; font-weight: 600;">
                            Visual
                        </button>
                        <button type="button" 
                                id="ocean3-code-view-btn"
                                class="editor-mode-btn"
                                data-mode="code"
                                style="background: #f1f1f1; color: #666; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-size: 13px; font-weight: 600;">
                            Code
                        </button>
                        <span style="margin-left: auto; font-size: 12px; color: #666;">
                            Field: content_ocean_3
                        </span>
                    </div>
                    
                    <!-- Editor Content Area -->
                    <div class="editor-content-area" style="position: relative;">
                        <!-- Visual Editor -->
                        <div id="ocean3-visual-editor" class="editor-view" style="display: block;">
                            <textarea name="field_content_ocean_3" 
                                      id="field_content_ocean_3"
                                      style="width: 100%; height: 400px; border: none; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; font-size: 14px; line-height: 1.6; resize: vertical; outline: none;"
                                      placeholder="Enter your article content here. You can use HTML tags for formatting.&#10;&#10;Example:&#10;<h2>Your Heading</h2>&#10;<p>Your paragraph content with <strong>bold text</strong> and <em>italic text</em>.</p>&#10;<ul>&#10;    <li>List item 1</li>&#10;    <li>List item 2</li>&#10;</ul>"><?php echo esc_textarea($pylon_data['content_ocean_3'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Code Editor -->
                        <div id="ocean3-code-editor" class="editor-view" style="display: none;">
                            <textarea id="field_content_ocean_3_code"
                                      style="width: 100%; height: 400px; border: none; padding: 20px; font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; line-height: 1.4; resize: vertical; outline: none; background: #f8f9fa;"
                                      placeholder="HTML code view - edit raw HTML here"><?php echo esc_textarea($pylon_data['content_ocean_3'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Editor Footer -->
                    <div class="editor-footer" style="background: #f9f9f9; border-top: 1px solid #e9ecef; padding: 10px 20px; font-size: 12px; color: #666;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>Tips:</strong> Use Visual mode for easy editing, Code mode for HTML. Content supports paragraphs, headings, lists, links, and basic formatting.
                            </div>
                            <div>
                                Words: <span id="ocean3-word-count">0</span> | Characters: <span id="ocean3-char-count">0</span>
                            </div>
                        </div>
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
    function switchDioptraTab(targetTabId, updateUrl = true) {
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
        
        // Update URL if requested
        if (updateUrl) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('tab', getTabParamFromId(targetTabId));
            const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
            window.history.replaceState({}, '', newUrl);
        }
    }
    
    // Map tab IDs to URL parameters
    function getTabParamFromId(tabId) {
        const mapping = {
            'main-tab-1a': 'maintab1a',
            'our-services-box-config': 'osb',
            'box-ordering-config': 'box_ordering_1',
            'box-ordering-tab-2': 'box_ordering_2',
            'batman-hero': 'batman_hero',
            'chenblock-card': 'chenblock_card',
            'post-content': 'post_content',
            'serena-faq': 'serena_faq',
            'brook-video-box-box': 'brook_video_box_box',
            'olivia-authlinks-box': 'olivia_authlinks_box',
            'ava-whychooseus-box': 'ava_whychooseus_box',
            'kendall-ourprocess-box': 'kendall',
            'sara-customhtml-box': 'sara',
            'ocean1': 'ocean1',
            'ocean2': 'ocean2',
            'ocean3': 'ocean3'
        };
        return mapping[tabId] || tabId;
    }
    
    // Map URL parameters to tab IDs
    function getTabIdFromParam(param) {
        const mapping = {
            'maintab1a': 'main-tab-1a',
            'osb': 'our-services-box-config',
            'box_ordering_1': 'box-ordering-config',
            'box_ordering_2': 'box-ordering-tab-2',
            'batman_hero': 'batman-hero',
            'chenblock_card': 'chenblock-card',
            'post_content': 'post-content',
            'serena_faq': 'serena-faq',
            'brook_video_box_box': 'brook-video-box-box',
            'olivia_authlinks_box': 'olivia-authlinks-box',
            'ava_whychooseus_box': 'ava-whychooseus-box',
            'kendall': 'kendall-ourprocess-box',
            'sara': 'sara-customhtml-box',
            'ocean1': 'ocean1',
            'ocean2': 'ocean2',
            'ocean3': 'ocean3'
        };
        return mapping[param] || param;
    }
    
    // Initialize tab from URL parameter
    function initializeTabFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        let tabParam = urlParams.get('tab');
        
        // If no tab parameter, default to maintab1a and update URL
        if (!tabParam) {
            tabParam = 'maintab1a';
            urlParams.set('tab', 'maintab1a');
            const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
            window.history.replaceState({}, '', newUrl);
        }
        
        const targetTabId = getTabIdFromParam(tabParam);
        switchDioptraTab(targetTabId, false); // Don't update URL since we're initializing from URL
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
        
        // Initialize tab from URL parameter
        initializeTabFromUrl();
        
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
        
        // Create box order row functionality
        const createBoxOrderRowBtn = document.getElementById('create-box-order-row');
        if (createBoxOrderRowBtn) {
            createBoxOrderRowBtn.addEventListener('click', function() {
                createBoxOrderRow();
            });
        }
        
        // Tab 2 functionality - Create Custom Box Order Config
        const createCustomBoxOrderConfigTab2 = document.getElementById('create-custom-box-order-config-tab2');
        if (createCustomBoxOrderConfigTab2) {
            createCustomBoxOrderConfigTab2.addEventListener('click', function() {
                createCustomBoxOrderConfigTab2Function();
            });
        }
        
        // Tab 2 functionality - Initialize Tab 2 interface
        initializeBoxOrderTab2();
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
    
    // Create box order row function
    function createBoxOrderRow() {
        const btn = document.getElementById('create-box-order-row');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = 'Creating...';
        btn.style.background = '#6c757d';
        
        // AJAX call to create wp_box_orders row
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'create_box_order_row',
                post_id: <?php echo $post_id; ?>,
                nonce: '<?php echo wp_create_nonce('box_order_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show the new row and activate the interface
                location.reload();
            } else {
                showDioptraErrorModal(data.data || { message: 'Failed to create wp_box_orders row' });
                // Re-enable button on error
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.style.background = '#28a745';
            }
        })
        .catch(error => {
            showDioptraErrorModal({ 
                message: 'Network error during row creation', 
                details: { error: error.toString() } 
            });
            // Re-enable button on error
            btn.disabled = false;
            btn.innerHTML = originalText;
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
        
        // Get box order toggle switch
        const boxOrderToggle = document.getElementById('box_order_is_active');
        if (boxOrderToggle) {
            const toggleValue = boxOrderToggle.checked ? '1' : '0';
            formData.append('box_order_is_active', toggleValue);
            console.log(`Box order toggle: box_order_is_active = ${toggleValue} (checked: ${boxOrderToggle.checked})`);
        }
        
        // Get box order JSON
        const boxOrderJson = document.getElementById('box_order_json');
        if (boxOrderJson) {
            formData.append('box_order_json', boxOrderJson.value);
            console.log(`Box order JSON: ${boxOrderJson.value.substring(0, 100)}${boxOrderJson.value.length > 100 ? '...' : ''}`);
        }
        
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

    // Box Order functionality
    document.addEventListener('DOMContentLoaded', function() {
        initializeBoxOrderInterface();
    });
    
    function initializeBoxOrderInterface() {
        // Initialize toggle switch
        const toggleSwitch = document.getElementById('box_order_is_active');
        if (toggleSwitch) {
            toggleSwitch.addEventListener('change', function() {
                updateToggleSwitch(this.checked);
            });
        }
        
        // Initialize JSON textarea change detection
        const jsonTextarea = document.getElementById('box_order_json');
        if (jsonTextarea) {
            let originalValue = jsonTextarea.value;
            jsonTextarea.addEventListener('input', function() {
                if (this.value !== originalValue) {
                    showUnsavedWarning();
                    updateBoxOrderPreview(this.value);
                } else {
                    hideUnsavedWarning();
                }
            });
            
            // Initialize preview
            updateBoxOrderPreview(jsonTextarea.value);
        }
        
        // Button event listeners
        const createConfigBtn = document.getElementById('create-box-order-config');
        if (createConfigBtn) {
            createConfigBtn.addEventListener('click', createBoxOrderConfig);
        }
        
        const randomizeBtn = document.getElementById('randomize-box-order');
        if (randomizeBtn) {
            randomizeBtn.addEventListener('click', randomizeBoxOrder);
        }
        
        const saveBtn = document.getElementById('save-box-order');
        if (saveBtn) {
            saveBtn.addEventListener('click', saveBoxOrderChanges);
        }
        
        const cancelBtn = document.getElementById('cancel-box-order-changes');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', cancelBoxOrderChanges);
        }
        
        const deleteBtn = document.getElementById('delete-box-config');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', deleteBoxConfig);
        }
    }
    
    function updateToggleSwitch(isActive) {
        const slider = document.querySelector('.box-order-toggle-slider');
        const statusText = slider.parentElement.nextElementSibling;
        const innerSlider = slider.querySelector('span');
        
        if (isActive) {
            slider.style.backgroundColor = '#4CAF50';
            innerSlider.style.left = '30px';
            statusText.style.color = '#4CAF50';
            statusText.textContent = 'Active';
        } else {
            slider.style.backgroundColor = '#ccc';
            innerSlider.style.left = '4px';
            statusText.style.color = '#999';
            statusText.textContent = 'Inactive';
        }
    }
    
    function updateBoxOrderPreview(jsonString) {
        const previewContent = document.getElementById('box-order-preview-content');
        if (!previewContent) return;
        
        try {
            if (!jsonString.trim()) {
                previewContent.innerHTML = '<p style="color: #666; font-style: italic;">No box order configuration</p>';
                return;
            }
            
            const boxOrder = JSON.parse(jsonString);
            
            // Check if this is the new object format (box names as keys with numeric values)
            const isNewFormat = typeof boxOrder === 'object' && !Array.isArray(boxOrder) && !boxOrder.boxes;
            
            if (isNewFormat) {
                // Convert to array of objects for sorting
                const boxArray = Object.entries(boxOrder).map(([boxName, orderValue]) => ({
                    name: boxName,
                    order: parseInt(orderValue) || 0
                }));
                
                // Sort by order value
                boxArray.sort((a, b) => a.order - b.order);
                
                let html = '<ol style="margin: 0; padding-left: 20px;">';
                boxArray.forEach((boxItem, index) => {
                    html += `<li style="margin-bottom: 5px; color: #333; font-weight: 600;">Order ${boxItem.order}: ${boxItem.name}</li>`;
                });
                html += '</ol>';
                previewContent.innerHTML = html;
            } else if (boxOrder.boxes && Array.isArray(boxOrder.boxes)) {
                // Legacy format support
                let html = '<ol style="margin: 0; padding-left: 20px;">';
                boxOrder.boxes.forEach((box, index) => {
                    html += `<li style="margin-bottom: 5px; color: #333; font-weight: 600;">${index + 1}. ${box}</li>`;
                });
                html += '</ol>';
                html += '<p style="color: #ff6600; font-size: 12px; margin-top: 10px;"><strong>Note:</strong> This is using the legacy format. Consider converting to the new format for better control.</p>';
                previewContent.innerHTML = html;
            } else {
                previewContent.innerHTML = '<p style="color: #dc3545;">Invalid JSON format - expected {"box_name": order_number, ...} or {"boxes": ["box1", "box2", ...]}</p>';
            }
        } catch (e) {
            previewContent.innerHTML = '<p style="color: #dc3545;">Invalid JSON syntax</p>';
        }
    }
    
    function showUnsavedWarning() {
        const warning = document.getElementById('box-order-unsaved-warning');
        if (warning) {
            warning.style.display = 'block';
        }
    }
    
    function hideUnsavedWarning() {
        const warning = document.getElementById('box-order-unsaved-warning');
        if (warning) {
            warning.style.display = 'none';
        }
    }
    
    function showBoxOrderMessage(type, message) {
        // Hide all messages first
        hideBoxOrderMessages();
        
        const messageElement = document.getElementById(`box-order-${type}-message`);
        if (messageElement) {
            messageElement.textContent = message;
            messageElement.style.display = 'block';
            
            // Auto-hide success messages after 3 seconds
            if (type === 'success') {
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 3000);
            }
        }
    }
    
    function hideBoxOrderMessages() {
        const messages = ['success', 'error'];
        messages.forEach(type => {
            const element = document.getElementById(`box-order-${type}-message`);
            if (element) {
                element.style.display = 'none';
            }
        });
    }
    
    function createBoxOrderConfig() {
        const btn = document.getElementById('create-box-order-config');
        btn.disabled = true;
        btn.innerHTML = 'Creating...';
        
        // Default box order configuration
        const defaultConfig = {
            "batman_hero_box": 1,
            "derek_blog_post_meta_box": 2,
            "chen_cards_box": 3,
            "plain_post_content": 4,
            "osb_box": 5,
            "serena_faq_box": 6,
            "nile_map_box": 7,
            "kristina_cta_box": 8,
            "victoria_blog_box": 9,
            "ocean1_box": 10,
            "ocean2_box": 11,
            "ocean3_box": 12,
            "brook_video_box": 13,
            "olivia_authlinks_box": 14,
            "ava_whychooseus_box": 15,
            "kendall_ourprocess_box": 16,
            "sara_customhtml_box": 17
        };
        
        // Simulate creation (UI only for now)
        setTimeout(() => {
            // Replace no-config state with config-exists state
            const noConfigState = document.getElementById('no-config-state');
            const mainInterface = document.getElementById('box-order-main-interface');
            
            const configExistsHTML = `
                <div id="config-exists-state">
                    <h4 style="color: #333;">Box Ordering Configuration</h4>
                    
                    <!-- JSON Editor -->
                    <div style="margin-bottom: 20px;">
                        <label for="box_order_json" style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Box Order JSON:</label>
                        <textarea id="box_order_json" 
                                  name="box_order_json" 
                                  style="width: 100%; height: 150px; border: 1px solid #ddd; border-radius: 4px; padding: 10px; font-family: monospace; font-size: 12px; resize: vertical;"
                                  placeholder='{"batman_hero_box": 1, "derek_blog_post_meta_box": 2, "chen_cards_box": 3, "plain_post_content": 4, "osb_box": 5, "serena_faq_box": 6, "nile_map_box": 7, "kristina_cta_box": 8, "victoria_blog_box": 9, "ocean1_box": 10, "ocean2_box": 11, "ocean3_box": 12, "brook_video_box": 13, "olivia_authlinks_box": 14, "ava_whychooseus_box": 15, "kendall_ourprocess_box": 16, "sara_customhtml_box": 17}'>${JSON.stringify(defaultConfig, null, 2)}</textarea>
                        <small style="color: #666;">Edit the JSON configuration for box ordering</small>
                    </div>
                    
                    <!-- Visual Box Order Preview -->
                    <div style="margin-bottom: 20px;">
                        <h5 style="margin-bottom: 10px; color: #333;">Visual Box Order Preview:</h5>
                        <div id="box-order-preview" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 15px; min-height: 100px;">
                            <div id="box-order-preview-content"></div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <button type="button" 
                                id="randomize-box-order"
                                style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                            Randomize All
                        </button>
                        <button type="button" 
                                id="save-box-order"
                                style="background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                            Save Box Order Changes
                        </button>
                        <button type="button" 
                                id="cancel-box-order-changes"
                                style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                            Cancel
                        </button>
                        <button type="button" 
                                id="delete-box-config"
                                style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                            Delete Config
                        </button>
                    </div>
                    
                    <!-- Status and Messages -->
                    <div id="box-order-status-area">
                        <div id="box-order-unsaved-warning" style="display: none; background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                            You have unsaved changes to the box order configuration.
                        </div>
                        <div id="box-order-success-message" style="display: none; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                        </div>
                        <div id="box-order-error-message" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                        </div>
                    </div>
                </div>
            `;
            
            mainInterface.innerHTML = configExistsHTML;
            
            // Re-initialize event listeners for new elements
            initializeBoxOrderInterface();
            
            showBoxOrderMessage('success', 'Box order configuration created successfully!');
        }, 500);
    }
    
    function randomizeBoxOrder() {
        const jsonTextarea = document.getElementById('box_order_json');
        if (!jsonTextarea) return;
        
        try {
            let boxOrder;
            if (jsonTextarea.value.trim()) {
                boxOrder = JSON.parse(jsonTextarea.value);
            } else {
                // Default boxes with correct structure if no config exists
                boxOrder = {
                    "batman_hero_box": 1,
                    "derek_blog_post_meta_box": 2,
                    "chen_cards_box": 3,
                    "plain_post_content": 4,
                    "osb_box": 5,
                    "serena_faq_box": 6,
                    "nile_map_box": 7,
                    "kristina_cta_box": 8,
                    "victoria_blog_box": 9,
                    "ocean1_box": 10,
                    "ocean2_box": 11,
                    "ocean3_box": 12,
                    "brook_video_box": 13,
                    "olivia_authlinks_box": 14,
                    "ava_whychooseus_box": 15,
                    "kendall_ourprocess_box": 16,
                    "sara_customhtml_box": 17
                };
            }
            
            // Check if this is the new object format (box names as keys with numeric values)
            const isNewFormat = typeof boxOrder === 'object' && !Array.isArray(boxOrder) && !boxOrder.boxes;
            
            if (isNewFormat) {
                // Get all the numeric values and shuffle them
                const values = Object.values(boxOrder);
                const boxNames = Object.keys(boxOrder);
                
                // Fisher-Yates shuffle the values
                for (let i = values.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [values[i], values[j]] = [values[j], values[i]];
                }
                
                // Reassign shuffled values to box names (keeping box names in same order)
                const newBoxOrder = {};
                boxNames.forEach((boxName, index) => {
                    newBoxOrder[boxName] = values[index];
                });
                
                jsonTextarea.value = JSON.stringify(newBoxOrder, null, 2);
                updateBoxOrderPreview(jsonTextarea.value);
                showUnsavedWarning();
                showBoxOrderMessage('success', 'Box order randomized! Remember to save your changes.');
            } else if (boxOrder.boxes && Array.isArray(boxOrder.boxes)) {
                // Legacy format - convert to new format first, then randomize
                const newBoxOrder = {};
                boxOrder.boxes.forEach((boxName, index) => {
                    newBoxOrder[boxName] = index + 1;
                });
                
                // Then randomize the values
                const values = Object.values(newBoxOrder);
                const boxNames = Object.keys(newBoxOrder);
                
                // Fisher-Yates shuffle the values
                for (let i = values.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [values[i], values[j]] = [values[j], values[i]];
                }
                
                // Reassign shuffled values to box names
                const randomizedBoxOrder = {};
                boxNames.forEach((boxName, index) => {
                    randomizedBoxOrder[boxName] = values[index];
                });
                
                jsonTextarea.value = JSON.stringify(randomizedBoxOrder, null, 2);
                updateBoxOrderPreview(jsonTextarea.value);
                showUnsavedWarning();
                showBoxOrderMessage('success', 'Box order randomized and converted to new format! Remember to save your changes.');
            } else {
                showBoxOrderMessage('error', 'Cannot randomize: Invalid box order format');
            }
        } catch (e) {
            showBoxOrderMessage('error', 'Cannot randomize: Invalid JSON format');
        }
    }
    
    function saveBoxOrderChanges() {
        const btn = document.getElementById('save-box-order');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
        
        // Get form data
        const isActive = document.getElementById('box_order_is_active').checked;
        const boxOrderJson = document.getElementById('box_order_json').value;
        
        // Validate JSON before saving
        try {
            if (boxOrderJson.trim()) {
                JSON.parse(boxOrderJson);
            }
        } catch (e) {
            showBoxOrderMessage('error', 'Cannot save: Invalid JSON format');
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }
        
        // Simulate save operation (UI only for now)
        setTimeout(() => {
            hideUnsavedWarning();
            showBoxOrderMessage('success', 'Box order configuration saved successfully!');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, 800);
    }
    
    function cancelBoxOrderChanges() {
        if (confirm('Are you sure you want to cancel your changes? All unsaved changes will be lost.')) {
            // Reload the page to reset to saved state
            location.reload();
        }
    }
    
    function deleteBoxConfig() {
        if (confirm('Are you sure you want to delete this box order configuration? This action cannot be undone.')) {
            const btn = document.getElementById('delete-box-config');
            btn.disabled = true;
            btn.innerHTML = 'Deleting...';
            
            // Simulate deletion (UI only for now)
            setTimeout(() => {
                // Replace config state with no-config state
                const mainInterface = document.getElementById('box-order-main-interface');
                const noConfigHTML = `
                    <div id="no-config-state">
                        <h4 style="color: #333;">Box Ordering Configuration</h4>
                        <p style="color: #666; margin-bottom: 20px;">No box order configuration exists for this page yet.</p>
                        <button type="button" 
                                id="create-box-order-config"
                                style="background: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                            Create Custom Box Order Config
                        </button>
                    </div>
                `;
                
                mainInterface.innerHTML = noConfigHTML;
                
                // Re-initialize event listeners
                initializeBoxOrderInterface();
                
                // Reset toggle to inactive
                const toggleSwitch = document.getElementById('box_order_is_active');
                if (toggleSwitch) {
                    toggleSwitch.checked = false;
                    updateToggleSwitch(false);
                }
                
                showBoxOrderMessage('success', 'Box order configuration deleted successfully!');
            }, 600);
        }
    }
    
    // ============================================================
    // TAB 2 (ORIGINAL) BOX ORDERING FUNCTIONALITY
    // ============================================================
    
    function initializeBoxOrderTab2() {
        // Initialize toggle switch for Tab 2
        const toggleSwitchTab2 = document.getElementById('box_order_is_active_tab2');
        if (toggleSwitchTab2) {
            toggleSwitchTab2.addEventListener('change', function() {
                updateToggleSwitchTab2(this.checked);
            });
        }
        
        // Initialize JSON textarea change detection for Tab 2
        const jsonTextareaTab2 = document.getElementById('box_order_json_tab2');
        if (jsonTextareaTab2) {
            let originalValueTab2 = jsonTextareaTab2.value;
            jsonTextareaTab2.addEventListener('input', function() {
                if (this.value !== originalValueTab2) {
                    showUnsavedWarningTab2();
                    updateBoxOrderPreviewTab2(this.value);
                } else {
                    hideUnsavedWarningTab2();
                }
            });
            
            // Initialize preview for Tab 2
            updateBoxOrderPreviewTab2(jsonTextareaTab2.value);
        }
        
        // Button event listeners for Tab 2
        const randomizeTab2 = document.getElementById('randomize-all-tab2');
        if (randomizeTab2) {
            randomizeTab2.addEventListener('click', randomizeBoxOrderTab2);
        }
        
        const saveTab2 = document.getElementById('save-box-order-tab2');
        if (saveTab2) {
            saveTab2.addEventListener('click', saveBoxOrderChangesTab2);
        }
        
        const cancelTab2 = document.getElementById('cancel-box-order-tab2');
        if (cancelTab2) {
            cancelTab2.addEventListener('click', cancelBoxOrderChangesTab2);
        }
        
        const deleteTab2 = document.getElementById('delete-config-tab2');
        if (deleteTab2) {
            deleteTab2.addEventListener('click', deleteBoxConfigTab2);
        }
    }
    
    function createCustomBoxOrderConfigTab2Function() {
        const btn = document.getElementById('create-custom-box-order-config-tab2');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = 'Creating...';
        btn.style.background = '#6c757d';
        
        // AJAX call to create wp_box_orders row
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'create_box_order_row',
                post_id: <?php echo $post_id; ?>,
                nonce: '<?php echo wp_create_nonce('box_order_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show the new configuration interface
                location.reload();
            } else {
                showBoxOrderMessageTab2('error', data.data.message || 'Failed to create box order configuration');
                // Re-enable button on error
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.style.background = '#28a745';
            }
        })
        .catch(error => {
            showBoxOrderMessageTab2('error', 'Network error during configuration creation');
            // Re-enable button on error
            btn.disabled = false;
            btn.innerHTML = originalText;
            btn.style.background = '#28a745';
        });
    }
    
    function updateToggleSwitchTab2(isActive) {
        const slider = document.querySelector('.box-order-toggle-slider-tab2');
        const statusText = slider.parentElement.nextElementSibling;
        const innerSlider = slider.querySelector('span');
        
        if (isActive) {
            slider.style.backgroundColor = '#4CAF50';
            innerSlider.style.left = '30px';
            statusText.style.color = '#4CAF50';
            statusText.textContent = 'Active';
        } else {
            slider.style.backgroundColor = '#ccc';
            innerSlider.style.left = '4px';
            statusText.style.color = '#999';
            statusText.textContent = 'Inactive';
        }
    }
    
    function updateBoxOrderPreviewTab2(jsonString) {
        const previewContent = document.getElementById('box-order-preview-content-tab2');
        if (!previewContent) return;
        
        try {
            if (!jsonString.trim()) {
                previewContent.innerHTML = '<p style="color: #666; font-style: italic;">No box order configuration</p>';
                return;
            }
            
            const boxOrder = JSON.parse(jsonString);
            
            // Check if this is the new object format (box names as keys with numeric values)
            const isNewFormat = typeof boxOrder === 'object' && !Array.isArray(boxOrder) && !boxOrder.boxes;
            
            if (isNewFormat) {
                // Convert to array of objects for sorting
                const boxArray = Object.entries(boxOrder).map(([boxName, orderValue]) => ({
                    name: boxName,
                    order: parseInt(orderValue) || 0
                }));
                
                // Sort by order value
                boxArray.sort((a, b) => a.order - b.order);
                
                let html = '<ol style="margin: 0; padding-left: 20px;">';
                boxArray.forEach((boxItem, index) => {
                    html += `<li style="margin-bottom: 5px; color: #333; font-weight: 600;">Order ${boxItem.order}: ${boxItem.name}</li>`;
                });
                html += '</ol>';
                previewContent.innerHTML = html;
            } else {
                previewContent.innerHTML = '<p style="color: #dc3545;">Invalid JSON format - expected {"box_name": order_number, ...}</p>';
            }
        } catch (e) {
            previewContent.innerHTML = '<p style="color: #dc3545;">Invalid JSON syntax</p>';
        }
    }
    
    function showUnsavedWarningTab2() {
        const warning = document.getElementById('box-order-unsaved-warning-tab2');
        if (warning) {
            warning.style.display = 'block';
        }
    }
    
    function hideUnsavedWarningTab2() {
        const warning = document.getElementById('box-order-unsaved-warning-tab2');
        if (warning) {
            warning.style.display = 'none';
        }
    }
    
    function showBoxOrderMessageTab2(type, message) {
        // Hide all messages first
        hideBoxOrderMessagesTab2();
        
        const messageElement = document.getElementById(`box-order-${type}-message-tab2`);
        if (messageElement) {
            messageElement.textContent = message;
            messageElement.style.display = 'block';
            
            // Auto-hide success messages after 3 seconds
            if (type === 'success') {
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 3000);
            }
        }
    }
    
    function hideBoxOrderMessagesTab2() {
        const messages = ['success', 'error'];
        messages.forEach(type => {
            const element = document.getElementById(`box-order-${type}-message-tab2`);
            if (element) {
                element.style.display = 'none';
            }
        });
    }
    
    function randomizeBoxOrderTab2() {
        const jsonTextarea = document.getElementById('box_order_json_tab2');
        if (!jsonTextarea) return;
        
        try {
            let boxOrder;
            if (jsonTextarea.value.trim()) {
                boxOrder = JSON.parse(jsonTextarea.value);
            } else {
                // Default boxes with correct structure if no config exists
                boxOrder = {
                    "batman_hero_box": 1,
                    "derek_blog_post_meta_box": 2,
                    "chen_cards_box": 3,
                    "plain_post_content": 4,
                    "osb_box": 5,
                    "serena_faq_box": 6,
                    "nile_map_box": 7,
                    "kristina_cta_box": 8,
                    "victoria_blog_box": 9,
                    "ocean1_box": 10,
                    "ocean2_box": 11,
                    "ocean3_box": 12,
                    "brook_video_box": 13,
                    "olivia_authlinks_box": 14,
                    "ava_whychooseus_box": 15,
                    "kendall_ourprocess_box": 16,
                    "sara_customhtml_box": 17
                };
            }
            
            // Check if this is the new object format (box names as keys with numeric values)
            const isNewFormat = typeof boxOrder === 'object' && !Array.isArray(boxOrder) && !boxOrder.boxes;
            
            if (isNewFormat) {
                // Get all the numeric values and shuffle them
                const values = Object.values(boxOrder);
                const boxNames = Object.keys(boxOrder);
                
                // Fisher-Yates shuffle the values
                for (let i = values.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [values[i], values[j]] = [values[j], values[i]];
                }
                
                // Reassign shuffled values to box names (keeping box names in same order)
                const newBoxOrder = {};
                boxNames.forEach((boxName, index) => {
                    newBoxOrder[boxName] = values[index];
                });
                
                jsonTextarea.value = JSON.stringify(newBoxOrder, null, 2);
                updateBoxOrderPreviewTab2(jsonTextarea.value);
                showUnsavedWarningTab2();
                showBoxOrderMessageTab2('success', 'Box order randomized! Remember to save your changes.');
            } else {
                showBoxOrderMessageTab2('error', 'Cannot randomize: Invalid box order format');
            }
        } catch (e) {
            showBoxOrderMessageTab2('error', 'Cannot randomize: Invalid JSON format');
        }
    }
    
    function saveBoxOrderChangesTab2() {
        const btn = document.getElementById('save-box-order-tab2');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
        
        // Get form data
        const isActive = document.getElementById('box_order_is_active_tab2').checked;
        const boxOrderJson = document.getElementById('box_order_json_tab2').value;
        
        // Validate JSON before saving
        try {
            if (boxOrderJson.trim()) {
                JSON.parse(boxOrderJson);
            }
        } catch (e) {
            showBoxOrderMessageTab2('error', 'Cannot save: Invalid JSON format');
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }
        
        // AJAX request to save
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'save_box_order_tab2',
                post_id: <?php echo $post_id; ?>,
                is_active: isActive ? '1' : '0',
                box_order_json: boxOrderJson,
                nonce: '<?php echo wp_create_nonce('box_order_save_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideUnsavedWarningTab2();
                showBoxOrderMessageTab2('success', 'Box order configuration saved successfully!');
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                // Update original value to prevent unsaved warning
                const jsonTextarea = document.getElementById('box_order_json_tab2');
                if (jsonTextarea) {
                    // Update the original value tracking
                    jsonTextarea.addEventListener('input', function() {
                        // Reset the event listener with new original value
                        let originalValueTab2 = jsonTextarea.value;
                    });
                }
            } else {
                showBoxOrderMessageTab2('error', data.data.message || 'Failed to save box order configuration');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            showBoxOrderMessageTab2('error', 'Network error during save operation');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
    
    function cancelBoxOrderChangesTab2() {
        if (confirm('Are you sure you want to cancel your changes? All unsaved changes will be lost.')) {
            // Reload the page to reset to saved state
            location.reload();
        }
    }
    
    function deleteBoxConfigTab2() {
        if (confirm('Are you sure you want to delete this box order configuration? This action cannot be undone.')) {
            const btn = document.getElementById('delete-config-tab2');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = 'Deleting...';
            
            // AJAX request to delete
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'delete_box_order_config_tab2',
                    post_id: <?php echo $post_id; ?>,
                    nonce: '<?php echo wp_create_nonce('box_order_delete_nonce'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to show the no-config state
                    location.reload();
                } else {
                    showBoxOrderMessageTab2('error', data.data.message || 'Failed to delete box order configuration');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                showBoxOrderMessageTab2('error', 'Network error during delete operation');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
    }
    
    // Ocean1 Article Editor Functionality
    document.addEventListener('DOMContentLoaded', function() {
        initializeOcean1Editor();
    });
    
    function initializeOcean1Editor() {
        const visualBtn = document.getElementById('ocean1-visual-view-btn');
        const codeBtn = document.getElementById('ocean1-code-view-btn');
        const visualEditor = document.getElementById('ocean1-visual-editor');
        const codeEditor = document.getElementById('ocean1-code-editor');
        const visualTextarea = document.getElementById('field_content_ocean_1');
        const codeTextarea = document.getElementById('field_content_ocean_1_code');
        const wordCountSpan = document.getElementById('ocean1-word-count');
        const charCountSpan = document.getElementById('ocean1-char-count');
        
        if (!visualBtn || !codeBtn || !visualEditor || !codeEditor || !visualTextarea || !codeTextarea) {
            return; // Elements not found, exit gracefully
        }
        
        // Switch to visual view
        function switchToVisual() {
            // Sync content from code to visual
            visualTextarea.value = codeTextarea.value;
            
            // Update UI
            visualEditor.style.display = 'block';
            codeEditor.style.display = 'none';
            
            visualBtn.style.background = '#0073aa';
            visualBtn.style.color = 'white';
            visualBtn.classList.add('active');
            
            codeBtn.style.background = '#f1f1f1';
            codeBtn.style.color = '#666';
            codeBtn.classList.remove('active');
            
            updateWordCount();
        }
        
        // Switch to code view
        function switchToCode() {
            // Sync content from visual to code
            codeTextarea.value = visualTextarea.value;
            
            // Update UI
            visualEditor.style.display = 'none';
            codeEditor.style.display = 'block';
            
            codeBtn.style.background = '#0073aa';
            codeBtn.style.color = 'white';
            codeBtn.classList.add('active');
            
            visualBtn.style.background = '#f1f1f1';
            visualBtn.style.color = '#666';
            visualBtn.classList.remove('active');
            
            updateWordCount();
        }
        
        // Update word and character count
        function updateWordCount() {
            const activeTextarea = visualEditor.style.display === 'none' ? codeTextarea : visualTextarea;
            const content = activeTextarea.value;
            
            // Count characters
            const charCount = content.length;
            
            // Count words (exclude HTML tags for more accurate count)
            const textContent = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            const wordCount = textContent ? textContent.split(' ').length : 0;
            
            if (wordCountSpan) wordCountSpan.textContent = wordCount;
            if (charCountSpan) charCountSpan.textContent = charCount;
        }
        
        // Keep both textareas in sync
        function syncContent(sourceTextarea, targetTextarea) {
            targetTextarea.value = sourceTextarea.value;
            updateWordCount();
        }
        
        // Event listeners
        visualBtn.addEventListener('click', switchToVisual);
        codeBtn.addEventListener('click', switchToCode);
        
        // Sync content between textareas on input
        visualTextarea.addEventListener('input', function() {
            syncContent(visualTextarea, codeTextarea);
        });
        
        codeTextarea.addEventListener('input', function() {
            syncContent(codeTextarea, visualTextarea);
        });
        
        // Initialize word count
        updateWordCount();
    }
    
    // Ocean2 Article Editor Functionality
    document.addEventListener('DOMContentLoaded', function() {
        initializeOcean2Editor();
    });
    
    function initializeOcean2Editor() {
        const visualBtn = document.getElementById('ocean2-visual-view-btn');
        const codeBtn = document.getElementById('ocean2-code-view-btn');
        const visualEditor = document.getElementById('ocean2-visual-editor');
        const codeEditor = document.getElementById('ocean2-code-editor');
        const visualTextarea = document.getElementById('field_content_ocean_2');
        const codeTextarea = document.getElementById('field_content_ocean_2_code');
        const wordCountSpan = document.getElementById('ocean2-word-count');
        const charCountSpan = document.getElementById('ocean2-char-count');
        
        if (!visualBtn || !codeBtn || !visualEditor || !codeEditor || !visualTextarea || !codeTextarea) {
            return; // Elements not found, exit gracefully
        }
        
        // Switch to visual view
        function switchToVisual() {
            visualTextarea.value = codeTextarea.value;
            visualEditor.style.display = 'block';
            codeEditor.style.display = 'none';
            visualBtn.style.background = '#0073aa';
            visualBtn.style.color = 'white';
            visualBtn.classList.add('active');
            codeBtn.style.background = '#f1f1f1';
            codeBtn.style.color = '#666';
            codeBtn.classList.remove('active');
            updateWordCount();
        }
        
        // Switch to code view
        function switchToCode() {
            codeTextarea.value = visualTextarea.value;
            visualEditor.style.display = 'none';
            codeEditor.style.display = 'block';
            codeBtn.style.background = '#0073aa';
            codeBtn.style.color = 'white';
            codeBtn.classList.add('active');
            visualBtn.style.background = '#f1f1f1';
            visualBtn.style.color = '#666';
            visualBtn.classList.remove('active');
            updateWordCount();
        }
        
        // Update word and character count
        function updateWordCount() {
            const activeTextarea = visualEditor.style.display === 'none' ? codeTextarea : visualTextarea;
            const content = activeTextarea.value;
            const charCount = content.length;
            const textContent = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            const wordCount = textContent ? textContent.split(' ').length : 0;
            if (wordCountSpan) wordCountSpan.textContent = wordCount;
            if (charCountSpan) charCountSpan.textContent = charCount;
        }
        
        // Keep both textareas in sync
        function syncContent(sourceTextarea, targetTextarea) {
            targetTextarea.value = sourceTextarea.value;
            updateWordCount();
        }
        
        // Event listeners
        visualBtn.addEventListener('click', switchToVisual);
        codeBtn.addEventListener('click', switchToCode);
        visualTextarea.addEventListener('input', function() {
            syncContent(visualTextarea, codeTextarea);
        });
        codeTextarea.addEventListener('input', function() {
            syncContent(codeTextarea, visualTextarea);
        });
        
        // Initialize word count
        updateWordCount();
    }
    
    // Ocean3 Article Editor Functionality
    document.addEventListener('DOMContentLoaded', function() {
        initializeOcean3Editor();
    });
    
    function initializeOcean3Editor() {
        const visualBtn = document.getElementById('ocean3-visual-view-btn');
        const codeBtn = document.getElementById('ocean3-code-view-btn');
        const visualEditor = document.getElementById('ocean3-visual-editor');
        const codeEditor = document.getElementById('ocean3-code-editor');
        const visualTextarea = document.getElementById('field_content_ocean_3');
        const codeTextarea = document.getElementById('field_content_ocean_3_code');
        const wordCountSpan = document.getElementById('ocean3-word-count');
        const charCountSpan = document.getElementById('ocean3-char-count');
        
        if (!visualBtn || !codeBtn || !visualEditor || !codeEditor || !visualTextarea || !codeTextarea) {
            return; // Elements not found, exit gracefully
        }
        
        // Switch to visual view
        function switchToVisual() {
            visualTextarea.value = codeTextarea.value;
            visualEditor.style.display = 'block';
            codeEditor.style.display = 'none';
            visualBtn.style.background = '#0073aa';
            visualBtn.style.color = 'white';
            visualBtn.classList.add('active');
            codeBtn.style.background = '#f1f1f1';
            codeBtn.style.color = '#666';
            codeBtn.classList.remove('active');
            updateWordCount();
        }
        
        // Switch to code view
        function switchToCode() {
            codeTextarea.value = visualTextarea.value;
            visualEditor.style.display = 'none';
            codeEditor.style.display = 'block';
            codeBtn.style.background = '#0073aa';
            codeBtn.style.color = 'white';
            codeBtn.classList.add('active');
            visualBtn.style.background = '#f1f1f1';
            visualBtn.style.color = '#666';
            visualBtn.classList.remove('active');
            updateWordCount();
        }
        
        // Update word and character count
        function updateWordCount() {
            const activeTextarea = visualEditor.style.display === 'none' ? codeTextarea : visualTextarea;
            const content = activeTextarea.value;
            const charCount = content.length;
            const textContent = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            const wordCount = textContent ? textContent.split(' ').length : 0;
            if (wordCountSpan) wordCountSpan.textContent = wordCount;
            if (charCountSpan) charCountSpan.textContent = charCount;
        }
        
        // Keep both textareas in sync
        function syncContent(sourceTextarea, targetTextarea) {
            targetTextarea.value = sourceTextarea.value;
            updateWordCount();
        }
        
        // Event listeners
        visualBtn.addEventListener('click', switchToVisual);
        codeBtn.addEventListener('click', switchToCode);
        visualTextarea.addEventListener('input', function() {
            syncContent(visualTextarea, codeTextarea);
        });
        codeTextarea.addEventListener('input', function() {
            syncContent(codeTextarea, visualTextarea);
        });
        
        // Initialize word count
        updateWordCount();
    }

    </script>
    <?php
}