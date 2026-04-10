<?php
/**
 * Fundamental Image Setter Page Renderer
 * 
 * @package Ruplin
 * @subpackage FundamentalImageSetter
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Fundamental_Image_Setter_Page_Renderer {
    
    /**
     * Render the admin page
     */
    public function render() {
        global $wpdb;
        
        // Enqueue media scripts for image selection
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        
        // Get the post ID from URL parameter
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        
        // Handle form submission
        if (isset($_POST['fundamental_save']) && $post_id) {
            if (!isset($_POST['fundamental_nonce']) || !wp_verify_nonce($_POST['fundamental_nonce'], 'fundamental_save_' . $post_id)) {
                wp_die('Security check failed');
            }
            
            // Save the data
            $this->save_post_data($post_id, $_POST);
            echo '<div class="fundamental-notice success">Data saved successfully!</div>';
        }
        
        ?>
        <div class="wrap fundamental-wrap" style="max-width: 100%; margin: 0;">
            <!-- Remove WordPress admin notices area -->
            <style>
                /* Aggressive notice suppression for Fundamental */
                .fundamental-wrap ~ .notice,
                .fundamental-wrap ~ .notice-warning,
                .fundamental-wrap ~ .notice-error,
                .fundamental-wrap ~ .notice-success,
                .fundamental-wrap ~ .notice-info,
                .fundamental-wrap ~ .updated,
                .fundamental-wrap ~ .error,
                .fundamental-wrap ~ .update-nag,
                #wpbody-content > .notice,
                #wpbody-content > .updated,
                #wpbody-content > .error,
                .wp-header-end ~ .notice,
                .wp-header-end ~ .updated,
                .wp-header-end ~ .error {
                    display: none !important;
                }
                
                /* Fundamental specific styles - matching telescope */
                .fundamental-header {
                    background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
                    color: white;
                    padding: 30px;
                    margin: -20px -20px 30px -20px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                
                .fundamental-header h1 {
                    margin: 0;
                    color: white;
                    font-size: 32px;
                    font-weight: 300;
                    letter-spacing: -0.5px;
                }
                
                .fundamental-header .subtitle {
                    margin-top: 10px;
                    opacity: 0.9;
                    font-size: 16px;
                }
                
                .fundamental-content {
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                    min-height: 400px;
                }
                
                .fundamental-placeholder {
                    text-align: center;
                    padding: 60px 20px;
                    color: #666;
                }
                
                .fundamental-placeholder-icon {
                    font-size: 72px;
                    color: #8B4513;
                    margin-bottom: 20px;
                }
                
                .fundamental-placeholder h2 {
                    color: #333;
                    font-size: 24px;
                    font-weight: 400;
                    margin-bottom: 10px;
                }
                
                .fundamental-placeholder p {
                    color: #666;
                    font-size: 16px;
                    max-width: 500px;
                    margin: 0 auto;
                }
            </style>
            
            <?php $this->render_filezilla_section(); ?>
            
            <div class="fundamental-header">
                <h1>🎯 Fundamental Image Setter</h1>
                <div class="subtitle">Quick updates for essential page settings and images</div>
            </div>
            
            <div class="fundamental-content">
                <?php if ($post_id): ?>
                    <?php $this->render_edit_form($post_id); ?>
                <?php else: ?>
                    <?php $this->render_post_selector(); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php $this->render_jezel_navigation(); ?>
        <?php
    }
    
    /**
     * Render the post selector table
     */
    private function render_post_selector() {
        global $wpdb;
        
        // Get all posts and pages
        $posts = $wpdb->get_results("
            SELECT ID, post_title, post_type, post_status, post_date
            FROM {$wpdb->posts}
            WHERE post_type IN ('post', 'page')
            AND post_status IN ('publish', 'draft', 'pending', 'private')
            ORDER BY post_date DESC
            LIMIT 100
        ");
        ?>
        
        <div class="fundamental-selector">
            <h2>Select a Page or Post to Edit</h2>
            
            <table class="fundamental-posts-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo esc_html($post->ID); ?></td>
                        <td><?php echo esc_html($post->post_title ?: '(no title)'); ?></td>
                        <td><?php echo esc_html($post->post_type); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($post->post_status); ?>">
                                <?php echo esc_html($post->post_status); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($post->post_date))); ?></td>
                        <td>
                            <a href="?page=fundamental_image_setter&post=<?php echo $post->ID; ?>" 
                               class="button button-primary">
                                Edit in Fundamental
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .fundamental-selector {
            padding: 20px 0;
        }
        
        .fundamental-posts-table {
            width: 100%;
            margin-left: 12px;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .fundamental-posts-table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        
        .fundamental-posts-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .fundamental-posts-table tr:hover {
            background: #f9f9f9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-publish { background: #d4edda; color: #155724; }
        .status-draft { background: #fff3cd; color: #856404; }
        .status-pending { background: #cce5ff; color: #004085; }
        .status-private { background: #f8d7da; color: #721c24; }
        </style>
        <?php
    }
    
    /**
     * Render the edit form
     */
    private function render_edit_form($post_id) {
        global $wpdb;
        
        // Get post data
        $post = get_post($post_id);
        if (!$post) {
            echo '<div class="fundamental-notice error">Post not found!</div>';
            return;
        }
        
        // Define the fields in the specified order
        $fields = [
            'wp_site_title' => ['type' => 'site_title', 'table' => 'options'],
            'avg_rating_box_hide_sitewide' => ['type' => 'checkbox', 'table' => 'zen_sitespren'],
            'pylon_archetype' => ['type' => 'text', 'table' => 'pylons'],
            'driggs_brand_name' => ['type' => 'text', 'table' => 'zen_sitespren'],
            'post_title' => ['type' => 'text', 'table' => 'posts'],
            'paragon_featured_image_id' => ['type' => 'media_select', 'table' => 'pylons'],
            'content_bay_1' => ['type' => 'textarea', 'table' => 'pylons'],
            'content_bay_1_image_id' => ['type' => 'media_select', 'table' => 'pylons'],
            'content_bay_2' => ['type' => 'textarea', 'table' => 'pylons'],
            'content_bay_2_image_id' => ['type' => 'media_select', 'table' => 'pylons']
        ];
        
        // Get data from multiple tables
        $pylon_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ), ARRAY_A);
        
        $sitespren_data = $wpdb->get_row(
            "SELECT * FROM {$wpdb->prefix}zen_sitespren LIMIT 1",
            ARRAY_A
        );
        
        ?>
        
        <div class="fundamental-editor">
            <div class="fundamental-editor-header">
                <h2>Editing: <?php echo esc_html($post->post_title); ?></h2>
                <a href="?page=fundamental_image_setter" class="button">← Back to Post List</a>
            </div>
            
            <form method="post" action="" class="fundamental-form" id="fundamental-form">
                <?php wp_nonce_field('fundamental_save_' . $post_id, 'fundamental_nonce'); ?>
                <input type="hidden" name="fundamental_save" value="1">
                
                <!-- Save button at top -->
                <div class="fundamental-actions-top">
                    <button type="submit" class="button button-primary button-large">💾 Save Changes</button>
                </div>
                
                <!-- Main editing table -->
                <table class="fundamental-edit-table">
                    <thead>
                        <tr>
                            <th width="25%">Field Name</th>
                            <th width="50%">Datum House</th>
                            <th width="25%">Misc Stuff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $this->render_table_rows($fields, $post, $pylon_data, $sitespren_data, $post_id); ?>
                    </tbody>
                </table>
                
                <!-- Save button at bottom -->
                <div class="fundamental-actions-bottom">
                    <button type="submit" class="button button-primary button-large">💾 Save Changes</button>
                </div>
            </form>
        </div>
        
        <?php $this->render_edit_styles(); ?>
        <?php $this->render_media_scripts(); ?>
        <?php
    }
    
    /**
     * Render table rows
     */
    private function render_table_rows($fields, $post, $pylon_data, $sitespren_data, $post_id) {
        global $wpdb;
        
        foreach ($fields as $field_name => $field_config):
            // Get the value based on table
            $value = '';
            if ($field_name === 'wp_site_title') {
                $value = get_option('blogname');
            } elseif ($field_config['table'] === 'posts') {
                $value = $post->$field_name;
            } elseif ($field_config['table'] === 'pylons' && $pylon_data) {
                $value = isset($pylon_data[$field_name]) ? $pylon_data[$field_name] : '';
            } elseif ($field_config['table'] === 'zen_sitespren' && $sitespren_data) {
                $value = isset($sitespren_data[$field_name]) ? $sitespren_data[$field_name] : '';
            }
            
            // Render special rows
            if ($field_name === 'wp_site_title'):
                $this->render_site_title_row($value);
            elseif ($field_name === 'avg_rating_box_hide_sitewide'):
                $this->render_rating_hide_row($value);
            else:
                $this->render_field_row($field_name, $field_config, $value);
            endif;
        endforeach;
    }
    
    /**
     * Render site title row
     */
    private function render_site_title_row($value) {
        ?>
        <tr style="background: #f0f8ff; border: 2px solid #0073aa;">
            <td style="padding: 15px; vertical-align: middle;">
                <span class="field-name" style="font-weight: bold; color: #0073aa;">
                    WP Site Title
                </span>
                <div class="field-description" style="font-size: 11px; color: #666; margin-top: 5px;">
                    WordPress native site title (Settings → General)
                </div>
            </td>
            <td style="padding: 15px;">
                <input 
                    type="text" 
                    id="wp_site_title" 
                    name="wp_site_title" 
                    value="<?php echo esc_attr($value); ?>" 
                    style="width: 100%; padding: 8px; border: 1px solid #0073aa; border-radius: 4px; font-size: 14px;">
            </td>
            <td style="padding: 15px;">
                <div style="color: #666; font-size: 12px;">
                    <strong>Database:</strong> wp_options table<br>
                    <strong>Option name:</strong> blogname<br>
                    <strong>Current value:</strong> <?php echo esc_html($value); ?>
                </div>
            </td>
        </tr>
        
        <!-- Separator after site title -->
        <tr>
            <td colspan="3" style="background: #0073aa; height: 2px; padding: 0;"></td>
        </tr>
        <?php
    }
    
    /**
     * Render rating hide row
     */
    private function render_rating_hide_row($value) {
        ?>
        <tr style="background: #fff4e6; border: 2px solid #ff9800;">
            <td style="padding: 15px; vertical-align: middle;">
                <span class="field-name" style="font-weight: bold; color: #ff6f00;">
                    avg_rating_box_hide_sitewide
                </span>
                <div class="field-description" style="font-size: 11px; color: #666; margin-top: 5px;">
                    Hide average rating box on ALL pages sitewide
                </div>
            </td>
            <td style="padding: 15px;">
                <label class="toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                    <input 
                        type="checkbox" 
                        id="avg_rating_box_hide_sitewide" 
                        name="avg_rating_box_hide_sitewide" 
                        value="1"
                        <?php echo $value == 1 ? 'checked' : ''; ?>
                        style="opacity: 0; width: 0; height: 0;">
                    <span class="toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;">
                        <span class="toggle-label" style="position: absolute; top: 50%; transform: translateY(-50%); left: 8px; right: 8px; text-align: center; color: white; font-weight: bold; font-size: 12px; pointer-events: none;">
                            <?php echo $value == 1 ? 'HIDDEN' : 'VISIBLE'; ?>
                        </span>
                    </span>
                </label>
                <span style="margin-left: 15px; color: #666; font-size: 13px;">
                    Currently: <strong><?php echo $value == 1 ? 'Hidden sitewide' : 'Visible sitewide'; ?></strong>
                </span>
            </td>
            <td style="padding: 15px;">
                <div style="color: #666; font-size: 12px;">
                    <strong>Database:</strong> wp_zen_sitespren table<br>
                    <strong>Column:</strong> avg_rating_box_hide_sitewide<br>
                    <strong>Type:</strong> Boolean (0 = show, 1 = hide)<br>
                    <strong>Note:</strong> Overrides individual page settings
                </div>
            </td>
        </tr>
        
        <!-- Separator after sitewide setting -->
        <tr>
            <td colspan="3" style="background: #ff9800; height: 2px; padding: 0;"></td>
        </tr>
        <?php
    }
    
    /**
     * Render regular field row
     */
    private function render_field_row($field_name, $field_config, $value) {
        ?>
        <tr>
            <td class="field-name-cell">
                <span class="field-name">
                    <strong><?php echo str_replace('_', ' ', ucfirst($field_name)); ?></strong>
                </span>
            </td>
            <td class="datum-house">
                <?php if ($field_config['type'] === 'textarea'): ?>
                    <textarea 
                        name="field_<?php echo esc_attr($field_name); ?>"
                        rows="5"
                        class="fundamental-field-input"
                        data-table="<?php echo esc_attr($field_config['table']); ?>"
                    ><?php echo esc_textarea($value); ?></textarea>
                <?php elseif ($field_config['type'] === 'media_select'): ?>
                    <div class="fundamental-media-select-container">
                        <input 
                            type="number" 
                            id="field_<?php echo esc_attr($field_name); ?>"
                            name="field_<?php echo esc_attr($field_name); ?>"
                            value="<?php echo esc_attr($value); ?>"
                            class="fundamental-media-id-input"
                            data-table="<?php echo esc_attr($field_config['table']); ?>"
                            style="width: 100px;"
                        />
                        <button type="button" 
                                class="button fundamental-media-select" 
                                data-target="field_<?php echo esc_attr($field_name); ?>"
                                data-preview="preview_<?php echo esc_attr($field_name); ?>">
                            Select Image
                        </button>
                        <button type="button" 
                                class="button fundamental-media-remove" 
                                data-target="field_<?php echo esc_attr($field_name); ?>"
                                data-preview="preview_<?php echo esc_attr($field_name); ?>">
                            Remove
                        </button>
                        <div id="preview_<?php echo esc_attr($field_name); ?>" class="fundamental-media-preview" style="margin-top: 10px;">
                            <?php if (!empty($value) && is_numeric($value)): 
                                $image_url = wp_get_attachment_image_url($value, 'thumbnail');
                                if ($image_url): ?>
                                    <img src="<?php echo esc_url($image_url); ?>" style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 3px;">
                                <?php endif; 
                            endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <input 
                        type="text" 
                        name="field_<?php echo esc_attr($field_name); ?>"
                        value="<?php echo esc_attr($value); ?>"
                        class="fundamental-field-input"
                        data-table="<?php echo esc_attr($field_config['table']); ?>"
                    />
                <?php endif; ?>
            </td>
            <td class="misc-stuff">
                <!-- Reserved for future use -->
            </td>
        </tr>
        <?php
    }
    
    /**
     * Render edit styles
     */
    private function render_edit_styles() {
        ?>
        <style>
        /* Toggle Switch Styles for Sitewide Rating Hide */
        .toggle-switch input:checked + .toggle-slider {
            background-color: #ff6f00;
        }
        
        .toggle-switch input:not(:checked) + .toggle-slider {
            background-color: #4CAF50;
        }
        
        .toggle-switch .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .toggle-switch:hover .toggle-slider {
            box-shadow: 0 0 4px #2196F3;
        }
        
        .fundamental-editor {
            padding: 20px 0;
        }
        
        .fundamental-editor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .fundamental-editor-header h2 {
            margin: 0;
        }
        
        .fundamental-actions-top,
        .fundamental-actions-bottom {
            padding: 15px 0;
        }
        
        .fundamental-edit-table {
            width: 100%;
            margin-left: 12px;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .fundamental-edit-table th {
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .fundamental-edit-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .fundamental-edit-table tr:hover {
            background: #f9f9f9;
        }
        
        .field-name {
            font-size: 14px;
        }
        
        .field-name strong {
            font-weight: bold;
            color: #333;
        }
        
        .fundamental-field-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .fundamental-field-input:focus {
            border-color: #D2691E;
            outline: none;
            box-shadow: 0 0 0 2px rgba(210, 105, 30, 0.1);
        }
        
        textarea.fundamental-field-input {
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .fundamental-notice {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .fundamental-notice.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .fundamental-notice.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .misc-stuff {
            color: #999;
            font-style: italic;
        }
        </style>
        <?php
    }
    
    /**
     * Render media scripts
     */
    private function render_media_scripts() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Handle image selection
            $('.fundamental-media-select').on('click', function(e) {
                e.preventDefault();
                var targetInput = $(this).data('target');
                var previewDiv = $(this).data('preview');
                
                var mediaFrame = wp.media({
                    title: 'Select Image',
                    button: {
                        text: 'Use This Image'
                    },
                    multiple: false
                });
                
                mediaFrame.on('select', function() {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                    $('#' + targetInput).val(attachment.id);
                    
                    // Update preview
                    var previewHtml = '<img src="' + attachment.url + '" style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 3px;">';
                    $('#' + previewDiv).html(previewHtml);
                });
                
                mediaFrame.open();
            });
            
            // Handle image removal
            $('.fundamental-media-remove').on('click', function(e) {
                e.preventDefault();
                var targetInput = $(this).data('target');
                var previewDiv = $(this).data('preview');
                
                $('#' + targetInput).val('');
                $('#' + previewDiv).html('');
            });
            
            // Toggle switch label update
            $('#avg_rating_box_hide_sitewide').on('change', function() {
                var label = $(this).is(':checked') ? 'HIDDEN' : 'VISIBLE';
                $(this).next('.toggle-slider').find('.toggle-label').text(label);
                
                var statusText = $(this).is(':checked') ? 'Hidden sitewide' : 'Visible sitewide';
                $(this).closest('td').find('span:last strong').text(statusText);
            });
            
            // Aggressive notice removal via JavaScript
            function removeFundamentalNotices() {
                // Remove all WordPress admin notices
                $('.notice, .notice-warning, .notice-error, .notice-success, .notice-info').not('.fundamental-notice').remove();
                $('.updated, .error, .update-nag, .admin-notice').not('.fundamental-notice').remove();
                $('#wpbody-content > .notice, #wpbody-content > .updated, #wpbody-content > .error').not('.fundamental-notice').remove();
                $('.wp-header-end ~ .notice, .wp-header-end ~ .updated, .wp-header-end ~ .error').not('.fundamental-notice').remove();
            }
            
            // Initial removal
            removeFundamentalNotices();
            
            // Continuous monitoring and removal
            setInterval(removeFundamentalNotices, 500);
        });
        </script>
        <?php
    }
    
    /**
     * Render Jezel navigation
     */
    private function render_jezel_navigation() {
        ?>
        <!-- Jezel Navigation Buttons -->
        <div id="jezel-navigation" class="jezel-nav-container">
            <!-- Jezel Up Arrow Button -->
            <button 
                id="jezel-up" 
                class="jezel-btn jezel-scroll-btn"
                onclick="jezelScrollToTop()"
                title="Scroll to top"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
            </button>
            
            <!-- Jezel 25% Button -->
            <button 
                id="jezel-25" 
                class="jezel-btn jezel-scroll-btn"
                onclick="jezelScrollToQuarter()"
                title="Scroll to 25%"
            >
                <span>25</span>
            </button>
            
            <!-- Jezel Middle Button -->
            <button 
                id="jezel-m" 
                class="jezel-btn jezel-scroll-btn"
                onclick="jezelScrollToMiddle()"
                title="Scroll to middle (50%)"
            >
                <span>M</span>
            </button>
            
            <!-- Jezel 75% Button -->
            <button 
                id="jezel-75" 
                class="jezel-btn jezel-scroll-btn"
                onclick="jezelScrollToThreeQuarters()"
                title="Scroll to 75%"
            >
                <span>75</span>
            </button>
            
            <!-- Jezel Down Arrow Button -->
            <button 
                id="jezel-down" 
                class="jezel-btn jezel-scroll-btn"
                onclick="jezelScrollToBottom()"
                title="Scroll to bottom"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
        </div>
        
        <style>
        /* Jezel Navigation Styles */
        .jezel-nav-container {
            position: fixed;
            left: 170px;
            top: 120px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .jezel-btn {
            width: 41px;
            height: 41px;
            padding: 2px;
            background-color: #a8c5e6;
            border: 1px solid #4b5563;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .jezel-btn:hover {
            background-color: #6b7280;
            transform: translateX(2px);
        }
        
        .jezel-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        .jezel-btn:disabled:hover {
            background-color: #a8c5e6;
            transform: none;
        }
        
        .jezel-btn svg {
            color: #1f2937;
        }
        
        .jezel-btn span {
            color: #1f2937;
        }
        
        /* Adjust for collapsed admin menu */
        body.folded .jezel-nav-container {
            left: 46px;
        }

        /* Hide on mobile where admin menu is hidden */
        @media screen and (max-width: 782px) {
            .jezel-nav-container {
                left: 10px;
            }
        }
        </style>
        
        <script type="text/javascript">
        // Jezel Navigation Functions
        function jezelScrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        function jezelScrollToBottom() {
            window.scrollTo({
                top: document.documentElement.scrollHeight,
                behavior: 'smooth'
            });
        }
        
        function jezelScrollToMiddle() {
            const middlePosition = document.documentElement.scrollHeight / 2;
            window.scrollTo({
                top: middlePosition,
                behavior: 'smooth'
            });
        }
        
        function jezelScrollToQuarter() {
            const quarterPosition = document.documentElement.scrollHeight * 0.25;
            window.scrollTo({
                top: quarterPosition,
                behavior: 'smooth'
            });
        }
        
        function jezelScrollToThreeQuarters() {
            const threeQuartersPosition = document.documentElement.scrollHeight * 0.75;
            window.scrollTo({
                top: threeQuartersPosition,
                behavior: 'smooth'
            });
        }
        
        // Update button states based on scroll position
        function updateJezelButtons() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight;
            const clientHeight = document.documentElement.clientHeight;
            
            const isAtTop = scrollTop < 10;
            const isAtBottom = scrollTop + clientHeight >= scrollHeight - 10;
            
            const upBtn = document.getElementById('jezel-up');
            const downBtn = document.getElementById('jezel-down');
            
            if (upBtn) {
                upBtn.disabled = isAtTop;
            }
            if (downBtn) {
                downBtn.disabled = isAtBottom;
            }
        }
        
        jQuery(document).ready(function($) {
            // Initial button state
            updateJezelButtons();
            
            // Update on scroll
            $(window).on('scroll', function() {
                updateJezelButtons();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render FileZilla path section
     */
    private function render_filezilla_section() {
        global $wpdb;
        
        // Get the sitespren_base from database
        $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren LIMIT 1");
        
        // Fall back to current domain if sitespren_base is not set
        if (empty($sitespren_base)) {
            $site_url = get_site_url();
            $parsed_url = parse_url($site_url);
            $domain = $parsed_url['host'] ?? '';
            
            // Remove www. if present but keep other subdomains
            if (strpos($domain, 'www.') === 0) {
                $domain = substr($domain, 4);
            }
            $sitespren_base = $domain;
        }
        
        // Build the FileZilla path
        $filezilla_path = '/' . $sitespren_base . '/wp-content/ai1wm-backups';
        ?>
        
        <!-- FileZilla Path Section - Top of page -->
        <div style="display: flex; align-items: center; gap: 15px; margin: 0 -20px 20px -20px; padding: 20px 30px; background: #2c3e50; border-bottom: 3px solid #1e3a5f;">
            <button type="button" 
                    id="copy-filezilla-btn"
                    class="button" 
                    style="background: #1e3a5f; color: white; text-decoration: none; padding: 12px 20px; font-size: 16px; font-weight: bold; border: none; border-radius: 4px; display: inline-block; transition: background-color 0.3s ease; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"
                    onclick="copyFileZillaPath()">
                <span style="color: #FFA500;">copy filezilla path</span>
            </button>
            
            <input type="text" 
                   id="filezilla-path-input"
                   value="<?php echo esc_attr($filezilla_path); ?>" 
                   readonly
                   size="<?php echo strlen($filezilla_path) + 2; ?>"
                   style="padding: 10px; font-size: 14px; font-family: monospace; border: 1px solid #34495e; border-radius: 4px; background: white; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
            
            <button type="button" 
                    id="copy-domain-btn"
                    class="button" 
                    style="background: #27ae60; color: white; text-decoration: none; padding: 12px 20px; font-size: 16px; font-weight: bold; border: none; border-radius: 4px; display: inline-block; transition: background-color 0.3s ease; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"
                    onclick="copyDomainOnly()">
                <span style="color: #f1c40f;">copy domain only</span>
            </button>
            
            <input type="hidden" id="domain-only-input" value="<?php echo esc_attr($sitespren_base); ?>">
            
            <span id="copy-feedback" style="display: none; color: #2ecc71; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">✓ Copied!</span>
        </div>
        
        <style>
            #copy-filezilla-btn:hover {
                background: #87CEEB !important;
                transform: translateY(-1px);
                box-shadow: 0 3px 6px rgba(0,0,0,0.3) !important;
            }
            #copy-domain-btn:hover {
                background: #229954 !important;
                transform: translateY(-1px);
                box-shadow: 0 3px 6px rgba(0,0,0,0.3) !important;
            }
        </style>
        
        <script>
        function copyFileZillaPath() {
            var pathInput = document.getElementById('filezilla-path-input');
            var feedback = document.getElementById('copy-feedback');
            
            // Select the text
            pathInput.select();
            pathInput.setSelectionRange(0, 99999); // For mobile devices
            
            // Copy the text
            document.execCommand('copy');
            
            // Show feedback
            feedback.style.display = 'inline';
            feedback.innerHTML = '✓ FileZilla Path Copied!';
            setTimeout(function() {
                feedback.style.display = 'none';
            }, 2000);
            
            // Remove selection
            window.getSelection().removeAllRanges();
        }
        
        function copyDomainOnly() {
            var domainInput = document.getElementById('domain-only-input');
            var feedback = document.getElementById('copy-feedback');
            
            // Create temporary input to copy from
            var tempInput = document.createElement('input');
            tempInput.value = domainInput.value;
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.setSelectionRange(0, 99999);
            
            // Copy the text
            document.execCommand('copy');
            
            // Remove temporary input
            document.body.removeChild(tempInput);
            
            // Show feedback
            feedback.style.display = 'inline';
            feedback.innerHTML = '✓ Domain Copied!';
            setTimeout(function() {
                feedback.style.display = 'none';
            }, 2000);
        }
        </script>
        <?php
    }
    
    /**
     * Save post data
     */
    private function save_post_data($post_id, $form_data) {
        global $wpdb;
        
        // Handle WordPress Site Title update
        if (isset($form_data['wp_site_title'])) {
            $new_site_title = sanitize_text_field($form_data['wp_site_title']);
            update_option('blogname', $new_site_title);
        }
        
        // Handle Sitewide Rating Box Hide setting
        $sitewide_hide_value = isset($form_data['avg_rating_box_hide_sitewide']) ? 1 : 0;
        
        // Check if zen_sitespren table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}zen_sitespren'") != null;
        
        if ($table_exists) {
            // Update the sitewide setting
            $wpdb->update(
                $wpdb->prefix . 'zen_sitespren',
                array('avg_rating_box_hide_sitewide' => $sitewide_hide_value),
                array('id' => 1)
            );
            
            // Update driggs_brand_name if provided
            if (isset($form_data['field_driggs_brand_name'])) {
                $wpdb->update(
                    $wpdb->prefix . 'zen_sitespren',
                    array('driggs_brand_name' => sanitize_text_field($form_data['field_driggs_brand_name'])),
                    array('id' => 1)
                );
            }
        }
        
        // Prepare pylon updates
        $pylon_updates = [];
        $pylon_fields = ['pylon_archetype', 'paragon_featured_image_id', 'content_bay_1', 
                        'content_bay_1_image_id', 'content_bay_2', 'content_bay_2_image_id'];
        
        foreach ($pylon_fields as $field) {
            if (isset($form_data['field_' . $field])) {
                $pylon_updates[$field] = sanitize_text_field($form_data['field_' . $field]);
            }
        }
        
        // Update pylons table if there are updates
        if (!empty($pylon_updates)) {
            // Check if pylon record exists
            $pylon_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT pylon_id FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                $post_id
            ));
            
            if ($pylon_exists) {
                $wpdb->update(
                    $wpdb->prefix . 'pylons',
                    $pylon_updates,
                    array('rel_wp_post_id' => $post_id)
                );
            } else {
                // Create pylon record
                $pylon_updates['rel_wp_post_id'] = $post_id;
                $wpdb->insert(
                    $wpdb->prefix . 'pylons',
                    $pylon_updates
                );
            }
        }
        
        // Update post title if provided
        if (isset($form_data['field_post_title'])) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($form_data['field_post_title'])
            ));
        }
        
        return true;
    }
}