<?php
/**
 * Nuke Mar Admin Page
 * 
 * Provides functionality to delete all pages and/or posts from the site
 * 
 * @package Ruplin
 * @subpackage Nuke
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the Nuke Mar admin page
 */
function ruplin_render_nuke_mar_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'ruplin'));
    }
    
    // Handle form submission
    if (isset($_POST['ruplin_nuke_action']) && $_POST['ruplin_nuke_action'] === 'f494_nuke_wipe') {
        // Verify nonce
        if (!isset($_POST['ruplin_nuke_nonce']) || !wp_verify_nonce($_POST['ruplin_nuke_nonce'], 'ruplin_nuke_action')) {
            wp_die(__('Security check failed', 'ruplin'));
        }
        
        // Process the nuke action
        require_once plugin_dir_path(__FILE__) . 'nuke-mar-handler.php';
        ruplin_process_nuke_action($_POST);
    }
    ?>
    
    <div class="wrap">
        <h1><?php echo esc_html__('Nuke_Mar - Content Deletion Tool', 'ruplin'); ?></h1>
        
        <div class="notice notice-warning">
            <p><strong><?php echo esc_html__('WARNING:', 'ruplin'); ?></strong> <?php echo esc_html__('This tool will permanently delete content from your site. This action cannot be undone.', 'ruplin'); ?></p>
        </div>
        
        <div class="ruplin-nuke-container">
            <form method="post" action="" id="ruplin-nuke-form">
                <?php wp_nonce_field('ruplin_nuke_action', 'ruplin_nuke_nonce'); ?>
                <input type="hidden" name="ruplin_nuke_action" value="f494_nuke_wipe">
                
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><?php echo esc_html__('Select', 'ruplin'); ?></th>
                            <th><?php echo esc_html__('Action', 'ruplin'); ?></th>
                            <th><?php echo esc_html__('Description', 'ruplin'); ?></th>
                            <th style="width: 150px;"><?php echo esc_html__('Current Count', 'ruplin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       id="delete_all_pages" 
                                       name="delete_all_pages" 
                                       value="1" 
                                       checked="checked">
                            </td>
                            <td>
                                <label for="delete_all_pages">
                                    <strong><?php echo esc_html__('Delete all pages', 'ruplin'); ?></strong>
                                </label>
                            </td>
                            <td>
                                <?php echo esc_html__('Permanently delete all pages from the site (excluding system pages)', 'ruplin'); ?>
                            </td>
                            <td>
                                <?php 
                                $pages_count = wp_count_posts('page');
                                $total_pages = isset($pages_count->publish) ? $pages_count->publish : 0;
                                $total_pages += isset($pages_count->draft) ? $pages_count->draft : 0;
                                $total_pages += isset($pages_count->private) ? $pages_count->private : 0;
                                echo '<span class="dashicons dashicons-admin-page"></span> ' . esc_html($total_pages);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       id="delete_all_posts" 
                                       name="delete_all_posts" 
                                       value="1" 
                                       checked="checked">
                            </td>
                            <td>
                                <label for="delete_all_posts">
                                    <strong><?php echo esc_html__('Delete all posts', 'ruplin'); ?></strong>
                                </label>
                            </td>
                            <td>
                                <?php echo esc_html__('Permanently delete all blog posts from the site', 'ruplin'); ?>
                            </td>
                            <td>
                                <?php 
                                $posts_count = wp_count_posts('post');
                                $total_posts = isset($posts_count->publish) ? $posts_count->publish : 0;
                                $total_posts += isset($posts_count->draft) ? $posts_count->draft : 0;
                                $total_posts += isset($posts_count->private) ? $posts_count->private : 0;
                                echo '<span class="dashicons dashicons-admin-post"></span> ' . esc_html($total_posts);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       id="wipe_sitespren_values" 
                                       name="wipe_sitespren_values" 
                                       value="1" 
                                       checked="checked">
                            </td>
                            <td>
                                <label for="wipe_sitespren_values">
                                    <strong><?php echo esc_html__('Wipe all values in wp_zen_sitespren (the 1 row)', 'ruplin'); ?></strong>
                                </label>
                            </td>
                            <td>
                                <?php echo esc_html__('Clear all data fields in the site configuration table while preserving the row itself', 'ruplin'); ?>
                            </td>
                            <td>
                                <?php 
                                global $wpdb;
                                $sitespren_table = $wpdb->prefix . 'zen_sitespren';
                                $sitespren_exists = $wpdb->get_var("SHOW TABLES LIKE '$sitespren_table'") == $sitespren_table;
                                if ($sitespren_exists) {
                                    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $sitespren_table");
                                    echo '<span class="dashicons dashicons-database"></span> ' . esc_html($row_count) . ' row';
                                } else {
                                    echo '<span class="dashicons dashicons-database"></span> Table not found';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       id="exclude_urls_enabled" 
                                       name="exclude_urls_enabled" 
                                       value="1" 
                                       checked="checked">
                            </td>
                            <td colspan="2">
                                <label for="exclude_urls_enabled">
                                    <strong><?php echo esc_html__('Ignore pages/posts with the following URLs (do not delete)', 'ruplin'); ?></strong>
                                </label>
                                <div style="margin-top: 10px;">
                                    <textarea 
                                        id="excluded_urls" 
                                        name="excluded_urls" 
                                        rows="6" 
                                        cols="50" 
                                        style="width: 100%; max-width: 500px; font-family: monospace;"
                                        placeholder="Enter one URL per line">/privacy-policy/
/terms-of-service/
/sitemap/
/blog/
/turtle-example-with-all-cherry-boxes/
/turtle-example-minimal-cherry-boxes/</textarea>
                                    <p class="description" style="margin-top: 5px;">
                                        <?php echo esc_html__('Enter one URL slug per line. These pages/posts will be protected from deletion.', 'ruplin'); ?>
                                    </p>
                                    <p class="description" style="margin-top: 8px; padding: 8px; background-color: #e8f4fd; border-left: 4px solid #0073aa; color: #0073aa;">
                                        <strong><?php echo esc_html__('Auto-Protection:', 'ruplin'); ?></strong> 
                                        <?php echo esc_html__('Pages/posts with "nukeignore" or "turtle" in their URL will be automatically protected from deletion.', 'ruplin'); ?>
                                    </p>
                                </div>
                            </td>
                            <td style="vertical-align: top;">
                                <?php 
                                echo '<span class="dashicons dashicons-shield"></span> Protected URLs';
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="ruplin-nuke-actions" style="margin-top: 20px;">
                    <button type="submit" 
                            class="button button-primary button-large" 
                            id="ruplin-nuke-submit"
                            style="background-color: #dc3545; border-color: #dc3545;">
                        <span class="dashicons dashicons-warning" style="vertical-align: middle; margin-right: 5px;"></span>
                        <?php echo esc_html__('Run F494 - Nuke Wipe', 'ruplin'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <style type="text/css">
        .ruplin-nuke-container {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .ruplin-nuke-container table {
            margin: 0;
        }
        
        .ruplin-nuke-container table td,
        .ruplin-nuke-container table th {
            padding: 12px;
        }
        
        .ruplin-nuke-container input[type="checkbox"] {
            margin: 0;
            width: 18px;
            height: 18px;
        }
        
        #ruplin-nuke-submit:hover {
            background-color: #c82333 !important;
            border-color: #bd2130 !important;
        }
        
        .ruplin-nuke-actions {
            padding: 15px;
            background-color: #f1f1f1;
            border-top: 1px solid #ddd;
        }
        
        .dashicons {
            line-height: 1.4;
        }
    </style>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#ruplin-nuke-form').on('submit', function(e) {
            var deletePages = $('#delete_all_pages').is(':checked');
            var deletePosts = $('#delete_all_posts').is(':checked');
            var wipeSitespren = $('#wipe_sitespren_values').is(':checked');
            
            if (!deletePages && !deletePosts && !wipeSitespren) {
                alert('Please select at least one option to proceed.');
                e.preventDefault();
                return false;
            }
            
            var confirmMessage = 'WARNING: This action will permanently:\n\n';
            
            if (deletePages) {
                confirmMessage += '- DELETE ALL PAGES\n';
            }
            if (deletePosts) {
                confirmMessage += '- DELETE ALL POSTS\n';
            }
            if (wipeSitespren) {
                confirmMessage += '- WIPE ALL SITE CONFIGURATION DATA\n';
            }
            
            confirmMessage += '\nThis action CANNOT be undone. Are you absolutely sure?';
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
            
            // Double confirmation for safety
            var secondConfirm = prompt('Type "DELETE" to confirm this action:');
            
            if (secondConfirm !== 'DELETE') {
                alert('Action cancelled. The confirmation text did not match.');
                e.preventDefault();
                return false;
            }
            
            // Show processing message
            $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Processing...');
        });
        
        // Handle exclude URLs checkbox
        $('#exclude_urls_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('#excluded_urls').prop('disabled', false).css('opacity', '1');
            } else {
                $('#excluded_urls').prop('disabled', true).css('opacity', '0.5');
            }
        });
    });
    </script>
    
    <?php
}