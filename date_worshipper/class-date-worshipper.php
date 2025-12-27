<?php
/**
 * Date Worshipper - Posts Management System
 * 
 * @package Ruplin
 */

class Date_Worshipper {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add aggressive notice suppression for this page
        add_action('current_screen', array($this, 'suppress_admin_notices_on_date_worshipper_page'), 1);
        add_action('admin_init', array($this, 'early_notice_suppression_date_worshipper'), 1);
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            null, // Hidden from menu initially
            'Date Worshipper',
            'Date Worshipper',
            'manage_options',
            'date_worshipper_mar',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'date_worshipper_mar') === false && 
            (!isset($_GET['page']) || $_GET['page'] !== 'date_worshipper_mar')) {
            return;
        }
        
        wp_enqueue_script('jquery');
        
        // Add inline script for checkbox and random selector functionality
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Select all functionality
                $("#select-all-posts").on("change", function() {
                    $(".post-checkbox").prop("checked", $(this).prop("checked"));
                });
                
                // Individual checkbox change
                $(".post-checkbox").on("change", function() {
                    var totalCheckboxes = $(".post-checkbox").length;
                    var checkedCheckboxes = $(".post-checkbox:checked").length;
                    
                    if (checkedCheckboxes === totalCheckboxes) {
                        $("#select-all-posts").prop("checked", true).prop("indeterminate", false);
                    } else if (checkedCheckboxes === 0) {
                        $("#select-all-posts").prop("checked", false).prop("indeterminate", false);
                    } else {
                        $("#select-all-posts").prop("checked", false).prop("indeterminate", true);
                    }
                });
                
                // Random selector up/down button functionality
                $("#count-down").on("click", function() {
                    var currentVal = parseInt($("#random-count").val()) || 1;
                    if (currentVal > 1) {
                        $("#random-count").val(currentVal - 1);
                    }
                });
                
                $("#count-up").on("click", function() {
                    var currentVal = parseInt($("#random-count").val()) || 0;
                    var maxPosts = $(".post-checkbox").length;
                    if (currentVal < maxPosts) {
                        $("#random-count").val(currentVal + 1);
                    }
                });
                
                // Random selection functionality
                $("#select-randomly").on("click", function() {
                    var count = parseInt($("#random-count").val()) || 0;
                    var checkboxes = $(".post-checkbox");
                    var totalPosts = checkboxes.length;
                    
                    console.log("Random selector clicked. Count:", count, "Total posts:", totalPosts);
                    
                    if (count <= 0 || count > totalPosts) {
                        alert("Please enter a valid number between 1 and " + totalPosts);
                        return;
                    }
                    
                    // Uncheck all first
                    checkboxes.prop("checked", false);
                    $("#select-all-posts").prop("checked", false).prop("indeterminate", false);
                    
                    // Get random indices using Fisher-Yates shuffle
                    var indices = [];
                    for (var i = 0; i < totalPosts; i++) {
                        indices.push(i);
                    }
                    
                    // Shuffle array
                    for (var i = indices.length - 1; i > 0; i--) {
                        var j = Math.floor(Math.random() * (i + 1));
                        var temp = indices[i];
                        indices[i] = indices[j];
                        indices[j] = temp;
                    }
                    
                    // Check the randomly selected checkboxes
                    for (var i = 0; i < count; i++) {
                        $(checkboxes[indices[i]]).prop("checked", true);
                    }
                    
                    console.log("Selected", count, "random posts");
                    
                    // Update select-all checkbox state
                    if (count === totalPosts) {
                        $("#select-all-posts").prop("checked", true);
                    } else {
                        $("#select-all-posts").prop("indeterminate", true);
                    }
                });
                
                // Ensure input only accepts valid numbers
                $("#random-count").on("input", function() {
                    var val = parseInt($(this).val());
                    var maxPosts = $(".post-checkbox").length;
                    if (isNaN(val) || val < 1) {
                        $(this).val(1);
                    } else if (val > maxPosts) {
                        $(this).val(maxPosts);
                    }
                });
            });
        ');
    }
    
    /**
     * Suppress admin notices on Date Worshipper page
     */
    public function suppress_admin_notices_on_date_worshipper_page() {
        $screen = get_current_screen();
        
        if (!$screen) {
            return;
        }
        
        // Check if we're on the Date Worshipper page
        if (strpos($screen->id, 'date_worshipper_mar') !== false || 
            (isset($_GET['page']) && $_GET['page'] === 'date_worshipper_mar')) {
            
            // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices'); 
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Also suppress notices that might get added later
            add_action('admin_notices', '__return_false', 999);
            add_action('all_admin_notices', '__return_false', 999);
            add_action('network_admin_notices', '__return_false', 999);
            add_action('user_admin_notices', '__return_false', 999);
        }
    }
    
    /**
     * Early notice suppression for Date Worshipper page
     */
    public function early_notice_suppression_date_worshipper() {
        // Check if we're on Date Worshipper page
        if (isset($_GET['page']) && $_GET['page'] === 'date_worshipper_mar') {
            
            // Immediate notice suppression
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Add our suppression hooks very early
            add_action('admin_notices', '__return_false', 1);
            add_action('all_admin_notices', '__return_false', 1);
            add_action('network_admin_notices', '__return_false', 1);
            add_action('user_admin_notices', '__return_false', 1);
        }
    }
    
    /**
     * Render the admin page
     */
    public function admin_page() {
        global $wpdb;
        
        // Get all posts (not pages)
        $posts_query = "
            SELECT 
                ID,
                post_title,
                post_status,
                post_type,
                post_date,
                post_modified,
                post_author
            FROM {$wpdb->posts}
            WHERE post_type = 'post' AND post_status != 'trash'
            ORDER BY post_date DESC
        ";
        
        $posts = $wpdb->get_results($posts_query);
        
        // Get authors for display
        $authors = get_users(array('fields' => array('ID', 'display_name')));
        $author_map = array();
        foreach ($authors as $author) {
            $author_map[$author->ID] = $author->display_name;
        }
        
        ?>
        <div class="wrap">
            <h1>Date Worshipper - Posts Management</h1>
            <p>All posts in the system (published, draft, and all other statuses)</p>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin: 20px 0;">
                <div style="margin-bottom: 25px; display: flex; align-items: center; gap: 12px; padding: 15px; background: #f0f8ff; border: 2px solid #0073aa; border-radius: 8px;">
                    <span style="font-weight: bold; color: #0073aa; font-size: 16px;">🎲 Random Selector</span>
                    <input type="number" id="random-count" value="9" min="1" style="width: 70px; padding: 8px; border: 2px solid #0073aa; border-radius: 4px; font-size: 14px;">
                    <button type="button" id="count-down" style="padding: 8px 14px; cursor: pointer; border: 2px solid #0073aa; background: #fff; border-radius: 4px; font-weight: bold;">▼</button>
                    <button type="button" id="count-up" style="padding: 8px 14px; cursor: pointer; border: 2px solid #0073aa; background: #fff; border-radius: 4px; font-weight: bold;">▲</button>
                    <button type="button" id="select-randomly" style="padding: 8px 20px; background: #0073aa; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; text-transform: uppercase;">Select Randomly</button>
                </div>
                <h2>Posts Overview</h2>
                <p>Total posts found: <?php echo count($posts); ?></p>
                
                <?php if (count($posts) > 0): ?>
                <div style="overflow-x: auto;">
                    <table id="date-worshipper-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                        <thead>
                            <tr style="background: #f1f1f1;">
                                <th style="padding: 10px; text-align: center; border: 1px solid #ddd; width: 50px;">
                                    <input type="checkbox" id="select-all-posts" style="margin: 0;">
                                </th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">ID</th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase;">post_status</th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase;">post_date</th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase;">post_title</th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Status</th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Author</th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase;">post_date</th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase;">post_modified</th>
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px; text-align: center; border: 1px solid #ddd;">
                                    <input type="checkbox" name="selected_posts[]" value="<?php echo esc_attr($post->ID); ?>" class="post-checkbox" style="margin: 0;">
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($post->ID); ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($post->post_status); ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html(date('Y-m-d H:i:s', strtotime($post->post_date))); ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php 
                                    $title = !empty($post->post_title) ? $post->post_title : '(no title)';
                                    echo esc_html($title); 
                                    ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php 
                                    $status_display = ucfirst($post->post_status);
                                    $status_color = '';
                                    switch($post->post_status) {
                                        case 'publish':
                                            $status_color = 'green';
                                            break;
                                        case 'draft':
                                            $status_color = 'orange';
                                            break;
                                        case 'trash':
                                            $status_color = 'red';
                                            break;
                                        case 'private':
                                            $status_color = 'purple';
                                            break;
                                        default:
                                            $status_color = 'gray';
                                    }
                                    ?>
                                    <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                        <?php echo esc_html($status_display); ?>
                                    </span>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php 
                                    $author_name = isset($author_map[$post->post_author]) ? $author_map[$post->post_author] : 'Unknown';
                                    echo esc_html($author_name); 
                                    ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html(date('Y-m-d H:i:s', strtotime($post->post_date))); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo esc_html(date('Y-m-d H:i:s', strtotime($post->post_modified))); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" 
                                       target="_blank" 
                                       style="color: #0073aa; text-decoration: none;">
                                        Edit
                                    </a>
                                    <?php if ($post->post_status === 'publish'): ?>
                                    | <a href="<?php echo get_permalink($post->ID); ?>" 
                                         target="_blank" 
                                         style="color: #0073aa; text-decoration: none;">
                                        View
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <style>
                    #date-worshipper-table tbody tr:hover {
                        background-color: #f5f5f5;
                    }
                    
                    #date-worshipper-table th {
                        position: sticky;
                        top: 0;
                        background: #f1f1f1;
                        z-index: 10;
                    }
                </style>
                
                <?php else: ?>
                <p style="padding: 20px; background: #f9f9f9; border-left: 4px solid #ffb900;">
                    No posts found in the database.
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

// Initialize the class
new Date_Worshipper();
?>