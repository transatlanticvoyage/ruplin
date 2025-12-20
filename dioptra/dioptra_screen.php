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
        'staircase_page_template_desired' => 'pylons',
        'pylon_archetype' => 'pylons',
        'exempt_from_silkweaver_menu_dynamical' => 'pylons',
        'moniker' => 'pylons',
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
        'baynar2_main' => 'pylons'
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
        
        <!-- Debug: Show servicepage entries -->
        <?php if (!empty($debug_servicepages)): ?>
            <div style="background: #f0f8ff; border: 1px solid #0073aa; padding: 10px; margin: 10px 0;">
                <strong>Debug: Found <?php echo count($debug_servicepages); ?> servicepage entries:</strong>
                <?php foreach ($debug_servicepages as $sp): ?>
                    <div style="margin: 5px 0; font-family: monospace; font-size: 12px;">
                        Post ID: <?php echo $sp['rel_wp_post_id']; ?> | 
                        Title: "<?php echo esc_html($sp['post_title']); ?>" | 
                        Status: <?php echo $sp['post_status']; ?> | 
                        Moniker: "<?php echo esc_html($sp['moniker']); ?>" | 
                        Exempt: <?php echo $sp['exempt_from_silkweaver_menu_dynamical'] ?? 'NULL'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 10px 0;">
                <strong>Debug: No servicepage entries found in database!</strong>
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
            
            <table style="width: auto; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background-color: #f1f1f1;">
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">checkbox</th>
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">other-info</th>
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">field-name</th>
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">datum-house</th>
                        <th style="border: 1px solid #ccc; padding: 8px; font-weight: bold; color: black;">blank1</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fields as $field_name => $table_source): ?>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                                <input type="checkbox" name="field_<?php echo esc_attr($field_name); ?>" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;">
                                <?php echo (strpos($field_name, 'post_') === 0) ? 'wp_posts' : ''; ?>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;">
                                <?php echo esc_html($field_name); ?>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;">
                                <?php
                                $value = '';
                                if ($table_source === 'posts' && isset($post_data[$field_name])) {
                                    $value = $post_data[$field_name];
                                } elseif ($table_source === 'pylons' && isset($pylon_data[$field_name])) {
                                    $value = $pylon_data[$field_name];
                                }
                                ?>
                                <input type="text" 
                                       name="data_<?php echo esc_attr($field_name); ?>" 
                                       value="<?php echo esc_attr($value); ?>" 
                                       style="width: 100%; border: 1px solid #ccc; padding: 4px;" />
                            </td>
                            <td style="border: 1px solid #ccc; padding: 8px;">
                                <?php
                                // Special case for exempt_from_silkweaver_menu_dynamical field
                                if ($field_name === 'exempt_from_silkweaver_menu_dynamical') {
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
                alert('Error: ' + (data.data.message || 'Failed to create pylon'));
                // Re-enable button on error
                btn.disabled = false;
                btn.style.background = '#28a745';
                btn.innerHTML = 'create missing pylon';
            }
        })
        .catch(error => {
            alert('Error: ' + error);
            // Re-enable button on error
            btn.disabled = false;
            btn.style.background = '#28a745';
            btn.innerHTML = 'create missing pylon';
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
        
        // Get all text inputs from the table
        const inputs = document.querySelectorAll('input[name^="data_"]');
        inputs.forEach(input => {
            const fieldName = input.name.replace('data_', '');
            formData.append('field_' + fieldName, input.value);
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
            if (data.success) {
                btn.style.background = '#46b450';
                btn.innerHTML = 'Saved!';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.style.background = '#0073aa';
                    btn.innerHTML = originalText;
                }, 2000);
            } else {
                alert('Error: ' + (data.data.message || 'Failed to save data'));
                btn.disabled = false;
                btn.style.background = '#0073aa';
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            alert('Error: ' + error);
            btn.disabled = false;
            btn.style.background = '#0073aa';
            btn.innerHTML = originalText;
        });
    });
    </script>
    <?php
}