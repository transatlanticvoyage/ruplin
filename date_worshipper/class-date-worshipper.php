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
        
        // Add AJAX handlers for F582 processing
        add_action('wp_ajax_f582_process_posts', array($this, 'f582_process_posts'));
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
        
        // Localize script for AJAX
        wp_localize_script('jquery', 'date_worshipper_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('date_worshipper_nonce')
        ));
        
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
                
                // Diosa Date Stretcher slider functionality
                $("#date-stretcher-slider").on("input", function() {
                    var sliderValue = parseInt($(this).val());
                    var totalPosts = parseInt($(this).attr("max"));
                    var futureDateCount = totalPosts - sliderValue;
                    
                    $("#backdate-count").text(sliderValue);
                    $("#futuredate-count").text(futureDateCount);
                });
                
                // F582 button functionality
                $("#run-f582").on("click", function() {
                    // Create custom styled popup
                    var popup = $("<div>", {
                        id: "f582-popup",
                        css: {
                            position: "fixed",
                            top: "0",
                            left: "0",
                            width: "100%",
                            height: "100%",
                            backgroundColor: "rgba(0, 0, 0, 0.7)",
                            zIndex: "10000",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center"
                        }
                    });
                    
                    var popupContent = $("<div>", {
                        css: {
                            backgroundColor: "#fff",
                            padding: "30px",
                            borderRadius: "8px",
                            border: "3px solid #ff9800",
                            textAlign: "center",
                            maxWidth: "400px",
                            boxShadow: "0 10px 30px rgba(0,0,0,0.3)"
                        },
                        html: "<h3 style=\\"color: #ff9800; margin-bottom: 20px;\\">⚡ F582 Date Processing</h3>" +
                              "<p style=\\"margin-bottom: 25px; font-size: 16px;\\">Are you sure you want to do this?</p>" +
                              "<div style=\\"display: flex; gap: 15px; justify-content: center;\\"></div>"
                    });
                    
                    var confirmBtn = $("<button>", {
                        text: "Yes, Proceed",
                        css: {
                            padding: "10px 20px",
                            backgroundColor: "#ff9800",
                            color: "white",
                            border: "none",
                            borderRadius: "4px",
                            cursor: "pointer",
                            fontWeight: "bold"
                        },
                        click: function() {
                            popup.remove();
                            executeF582();
                        }
                    });
                    
                    var cancelBtn = $("<button>", {
                        text: "Cancel",
                        css: {
                            padding: "10px 20px",
                            backgroundColor: "#666",
                            color: "white",
                            border: "none",
                            borderRadius: "4px",
                            cursor: "pointer",
                            fontWeight: "bold"
                        },
                        click: function() {
                            popup.remove();
                        }
                    });
                    
                    popupContent.find("div").append(confirmBtn, cancelBtn);
                    popup.append(popupContent);
                    $("body").append(popup);
                });
                
                // F582 execution function
                function executeF582() {
                    console.log("Starting F582 execution...");
                    
                    var backdateCount = parseInt($("#backdate-count").text());
                    var futureDateCount = parseInt($("#futuredate-count").text());
                    var intervalFrom = parseInt($("#interval-from").val());
                    var intervalTo = parseInt($("#interval-to").val());
                    
                    console.log("Settings:", {
                        backdateCount: backdateCount,
                        futureDateCount: futureDateCount,
                        intervalFrom: intervalFrom,
                        intervalTo: intervalTo
                    });
                    
                    // Send AJAX request to process posts
                    $.ajax({
                        url: date_worshipper_ajax.ajax_url,
                        type: "POST",
                        data: {
                            action: "f582_process_posts",
                            nonce: date_worshipper_ajax.nonce,
                            backdate_count: backdateCount,
                            future_count: futureDateCount,
                            interval_from: intervalFrom,
                            interval_to: intervalTo
                        },
                        success: function(response) {
                            if (response.success) {
                                alert("F582 processing completed successfully!");
                                location.reload(); // Refresh to show updated dates
                            } else {
                                alert("Error: " + (response.data || "Unknown error occurred"));
                            }
                        },
                        error: function() {
                            alert("AJAX error occurred during F582 processing");
                        }
                    });
                }
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
        
        // Check if wp_pylons table exists and has the jchronology columns
        $pylons_table = $wpdb->prefix . 'pylons';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$pylons_table'") === $pylons_table;
        
        $jchronology_columns_exist = false;
        if ($table_exists) {
            $columns = $wpdb->get_results("SHOW COLUMNS FROM $pylons_table LIKE 'jchronology_%'");
            $jchronology_columns_exist = count($columns) >= 2;
        }
        
        // Get all posts with optional jchronology data if available
        if ($table_exists && $jchronology_columns_exist) {
            $posts_query = "
                SELECT 
                    p.ID,
                    p.post_title,
                    p.post_status,
                    p.post_type,
                    p.post_date,
                    p.post_modified,
                    p.post_author,
                    py.jchronology_order_for_blog_posts,
                    py.jchronology_batch
                FROM {$wpdb->posts} p
                LEFT JOIN {$pylons_table} py ON p.ID = py.rel_wp_post_id
                WHERE p.post_type = 'post' AND p.post_status != 'trash'
                ORDER BY p.post_date DESC
            ";
        } else {
            // Fallback query without jchronology columns
            $posts_query = "
                SELECT 
                    ID,
                    post_title,
                    post_status,
                    post_type,
                    post_date,
                    post_modified,
                    post_author,
                    NULL as jchronology_order_for_blog_posts,
                    NULL as jchronology_batch
                FROM {$wpdb->posts}
                WHERE post_type = 'post' AND post_status != 'trash'
                ORDER BY post_date DESC
            ";
        }
        
        $posts = $wpdb->get_results($posts_query);
        
        // Get authors for display
        $authors = get_users(array('fields' => array('ID', 'display_name')));
        $author_map = array();
        foreach ($authors as $author) {
            $author_map[$author->ID] = $author->display_name;
        }
        
        ?>
        <style>
        .jchronology-tooltip {
            position: relative;
            cursor: help;
        }
        .jchronology-tooltip:hover::after {
            content: attr(title);
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
        }
        </style>
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
                
                <div style="margin-bottom: 25px; padding: 15px; background: #fff8e1; border: 2px solid #ff9800; border-radius: 8px;">
                    <div style="margin-bottom: 15px;">
                        <span style="font-weight: bold; color: #ff9800; font-size: 16px;">⚡ Diosa Date Stretcher</span>
                    </div>
                    
                    <hr style="margin: 15px 0; border: 0; height: 1px; background: #ff9800;">
                    
                    <div style="max-width: 900px;">
                        <div style="margin-bottom: 10px;">
                            <span style="font-weight: bold; color: #333;">Part 1 - Select Past/Future Spread</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <input type="range" id="date-stretcher-slider" min="1" max="<?php echo count($posts); ?>" value="<?php echo min(8, count($posts)); ?>" style="flex: 1; height: 8px; background: #ddd; outline: none; border-radius: 5px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: bold; color: #ff9800;">Total: <?php echo count($posts); ?></span>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                            <div>
                                <span style="font-weight: bold; color: #333;">Posts To Back-Date: <span id="backdate-count"><?php echo min(8, count($posts)); ?></span>/<?php echo count($posts); ?></span>
                            </div>
                            <div>
                                <span style="font-weight: bold; color: #333;">Posts To Future-Date: <span id="futuredate-count"><?php echo count($posts) - min(8, count($posts)); ?></span>/<?php echo count($posts); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <hr style="margin: 20px 0; border: 0; height: 1px; background: #ff9800;">
                    
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: bold; color: #333;">Part 2 - Interval Setting</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" id="interval-from" value="4" min="1" style="width: 60px; padding: 5px; border: 1px solid #ff9800; border-radius: 3px;">
                        <span style="font-weight: bold; color: #333;">to</span>
                        <input type="number" id="interval-to" value="11" min="1" style="width: 60px; padding: 5px; border: 1px solid #ff9800; border-radius: 3px;">
                        <span style="font-weight: bold; color: #333;">days</span>
                    </div>
                    
                    <hr style="margin: 20px 0; border: 0; height: 1px; background: #ff9800;">
                    
                    <div>
                        <button type="button" id="run-f582" style="padding: 10px 20px; background: #ff9800; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">run f582 - set blog post dates</button>
                    </div>
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
                                <th class="jchronology-tooltip" style="padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase;" title="db column: wp_pylons.jchronology_order_for_blog_posts">jchron...</th>
                                <th class="jchronology-tooltip" style="padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase;" title="db column: wp_pylons.jchronology_batch">jc-batch</th>
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
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo !empty($post->jchronology_order_for_blog_posts) ? esc_html($post->jchronology_order_for_blog_posts) : ''; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo !empty($post->jchronology_batch) ? esc_html($post->jchronology_batch) : ''; ?></td>
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
    
    /**
     * AJAX handler for F582 post processing
     */
    public function f582_process_posts() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'date_worshipper_nonce')) {
            wp_die('Security check failed');
        }
        
        // Get parameters
        $backdate_count = intval($_POST['backdate_count']);
        $future_count = intval($_POST['future_count']);
        $interval_from = intval($_POST['interval_from']);
        $interval_to = intval($_POST['interval_to']);
        
        global $wpdb;
        
        try {
            // Get all eligible posts ordered by jchronology_order_for_blog_posts
            $pylons_table = $wpdb->prefix . 'pylons';
            $posts_query = "
                SELECT p.ID, p.post_title, 
                       COALESCE(py.jchronology_order_for_blog_posts, 999999) as jchronology_order
                FROM {$wpdb->posts} p
                LEFT JOIN {$pylons_table} py ON p.ID = py.rel_wp_post_id
                WHERE p.post_type = 'post' 
                AND p.post_status IN ('publish', 'draft', 'private')
                AND p.post_status != 'trash'
                ORDER BY jchronology_order ASC, p.ID ASC
            ";
            
            $posts = $wpdb->get_results($posts_query);
            
            if (empty($posts)) {
                wp_send_json_error('No eligible posts found');
                return;
            }
            
            $total_posts = count($posts);
            
            // Ensure we don't exceed available posts
            $backdate_count = min($backdate_count, $total_posts);
            $future_count = min($future_count, $total_posts - $backdate_count);
            
            // Split posts into backdate and future groups
            $backdate_posts = array_slice($posts, 0, $backdate_count);
            $future_posts = array_slice($posts, $backdate_count, $future_count);
            
            // Reverse backdated posts so jchronology=1 is farthest in past
            $backdate_posts = array_reverse($backdate_posts);
            
            $current_time = current_time('timestamp');
            $updated_count = 0;
            
            // Process backdate posts (going backward in time)
            $backdate_time = $current_time;
            foreach ($backdate_posts as $post) {
                // Generate random interval in seconds
                $min_seconds = $interval_from * 24 * 60 * 60;
                $max_seconds = $interval_to * 24 * 60 * 60;
                $random_seconds = rand($min_seconds, $max_seconds);
                
                // Add random hours, minutes, seconds for more natural distribution
                $random_hours = rand(0, 23);
                $random_minutes = rand(0, 59);
                $random_seconds_component = rand(0, 59);
                $random_seconds += ($random_hours * 3600) + ($random_minutes * 60) + $random_seconds_component;
                
                // Go back in time
                $backdate_time -= $random_seconds;
                
                $new_date = date('Y-m-d H:i:s', $backdate_time);
                
                // Update post date
                $result = $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_date' => $new_date,
                        'post_date_gmt' => get_gmt_from_date($new_date),
                        'post_status' => 'publish',
                        'post_modified' => current_time('mysql'),
                        'post_modified_gmt' => current_time('mysql', 1)
                    ),
                    array('ID' => $post->ID),
                    array('%s', '%s', '%s', '%s', '%s'),
                    array('%d')
                );
                
                if ($result !== false) {
                    $updated_count++;
                    // Clear post cache
                    clean_post_cache($post->ID);
                }
            }
            
            // Process future posts (going forward in time)
            $future_time = $current_time;
            foreach ($future_posts as $post) {
                // Generate random interval in seconds
                $min_seconds = $interval_from * 24 * 60 * 60;
                $max_seconds = $interval_to * 24 * 60 * 60;
                $random_seconds = rand($min_seconds, $max_seconds);
                
                // Add random hours, minutes, seconds for more natural distribution
                $random_hours = rand(0, 23);
                $random_minutes = rand(0, 59);
                $random_seconds_component = rand(0, 59);
                $random_seconds += ($random_hours * 3600) + ($random_minutes * 60) + $random_seconds_component;
                
                // Go forward in time
                $future_time += $random_seconds;
                
                $new_date = date('Y-m-d H:i:s', $future_time);
                
                // Update post date and set as scheduled
                $result = $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_date' => $new_date,
                        'post_date_gmt' => get_gmt_from_date($new_date),
                        'post_status' => 'future',
                        'post_modified' => current_time('mysql'),
                        'post_modified_gmt' => current_time('mysql', 1)
                    ),
                    array('ID' => $post->ID),
                    array('%s', '%s', '%s', '%s', '%s'),
                    array('%d')
                );
                
                if ($result !== false) {
                    $updated_count++;
                    // Clear post cache
                    clean_post_cache($post->ID);
                    
                    // Schedule the post to be published
                    wp_schedule_single_event($future_time, 'publish_future_post', array($post->ID));
                }
            }
            
            wp_send_json_success(array(
                'message' => "F582 processing completed. Updated {$updated_count} posts.",
                'backdate_count' => count($backdate_posts),
                'future_count' => count($future_posts),
                'total_updated' => $updated_count
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Error during F582 processing: ' . $e->getMessage());
        }
    }
}

// Initialize the class
new Date_Worshipper();
?>