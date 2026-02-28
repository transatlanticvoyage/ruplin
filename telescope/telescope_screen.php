<?php
/**
 * Telescope Content Editor Screen
 * 
 * This file handles the Telescope content editor interface.
 * Implements aggressive WordPress notice/message/warning suppression.
 * 
 * @package Ruplin
 * @subpackage Telescope
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main function to render the Telescope screen
 */
function ruplin_render_telescope_screen() {
    global $wpdb;
    
    // Ensure user has proper capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Get the post ID from URL parameter
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    
    // Handle form submission
    if (isset($_POST['telescope_save']) && $post_id) {
        if (!isset($_POST['telescope_nonce']) || !wp_verify_nonce($_POST['telescope_nonce'], 'telescope_save_' . $post_id)) {
            wp_die('Security check failed');
        }
        
        // Save the data (we'll implement this later)
        telescope_save_post_data($post_id, $_POST);
        echo '<div class="telescope-notice success">Data saved successfully!</div>';
    }
    
    ?>
    <div class="wrap telescope-wrap" style="max-width: 100%; margin: 0;">
        <!-- Remove WordPress admin notices area -->
        <style>
            /* Aggressive notice suppression for Telescope */
            .telescope-wrap ~ .notice,
            .telescope-wrap ~ .notice-warning,
            .telescope-wrap ~ .notice-error,
            .telescope-wrap ~ .notice-success,
            .telescope-wrap ~ .notice-info,
            .telescope-wrap ~ .updated,
            .telescope-wrap ~ .error,
            .telescope-wrap ~ .update-nag,
            #wpbody-content > .notice,
            #wpbody-content > .updated,
            #wpbody-content > .error,
            .wp-header-end ~ .notice,
            .wp-header-end ~ .updated,
            .wp-header-end ~ .error {
                display: none !important;
            }
            
            /* Telescope specific styles */
            .telescope-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                margin: -20px -20px 30px -20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .telescope-header h1 {
                margin: 0;
                color: white;
                font-size: 32px;
                font-weight: 300;
                letter-spacing: -0.5px;
            }
            
            .telescope-header .subtitle {
                margin-top: 10px;
                opacity: 0.9;
                font-size: 16px;
            }
            
            .telescope-content {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                min-height: 400px;
            }
            
            .telescope-placeholder {
                text-align: center;
                padding: 60px 20px;
                color: #666;
            }
            
            .telescope-placeholder-icon {
                font-size: 72px;
                color: #764ba2;
                margin-bottom: 20px;
            }
            
            .telescope-placeholder h2 {
                color: #333;
                font-size: 24px;
                font-weight: 400;
                margin-bottom: 10px;
            }
            
            .telescope-placeholder p {
                color: #666;
                font-size: 16px;
                max-width: 500px;
                margin: 0 auto;
            }
        </style>
        
        <div class="telescope-header">
            <h1>🔭 Telescope Content Editor</h1>
            <div class="subtitle">Advanced content management and optimization interface</div>
        </div>
        
        <div class="telescope-content">
            <?php if ($post_id): ?>
                <?php telescope_render_edit_form($post_id); ?>
            <?php else: ?>
                <?php telescope_render_post_selector(); ?>
            <?php endif; ?>
        </div>
    </div>
    
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
        
        <?php if ($post_id): ?>
        <!-- Jezel Pendulum Button - Links to WP Native Editor -->
        <button 
            id="jezel-pendulum" 
            class="jezel-btn jezel-pendulum-btn"
            onclick="window.open('<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>', '_blank')"
            title="Edit in WordPress Editor"
        >
            <!-- Pendulum Icon SVG -->
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 2px;">
                <!-- Pendulum string/rod -->
                <line x1="12" y1="2" x2="12" y2="14"></line>
                <!-- Pendulum pivot point -->
                <circle cx="12" cy="2" r="1" fill="white"></circle>
                <!-- Pendulum weight/bob -->
                <circle cx="12" cy="17" r="3" fill="white"></circle>
                <!-- Motion arc hints -->
                <path d="M 8 17 Q 12 20 16 17" stroke="white" stroke-width="1" opacity="0.5" stroke-dasharray="2,2"></path>
            </svg>
            <span style="font-size: 12px; display: block;">pend.</span>
        </button>
        
        <!-- Jezel Frontend Button - Links to Live URL -->
        <button 
            id="jezel-frontend" 
            class="jezel-btn jezel-frontend-btn"
            onclick="window.open('<?php echo get_permalink($post_id); ?>', '_blank')"
            title="View on Frontend"
        >
            <span style="font-size: 12px; display: block; line-height: 1.2;">front</span>
            <span style="font-size: 12px; display: block; line-height: 1.2;">end</span>
        </button>
        <?php endif; ?>
    </div>
    
    <style>
        /* Jezel Navigation Styles */
        .jezel-nav-container {
            position: fixed;
            left: 180px; /* Positioned at left edge of main content area (after WP admin sidebar) */
            top: 120px; /* Below admin bar and some spacing */
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        
        .jezel-btn {
            width: 41px;
            height: 41px;
            padding: 2px;
            background-color: #a8c5e6;
            border: 1px solid #4b5563;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: #1f2937;
            font-weight: bold;
            font-size: 14px;
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
        
        /* Jezel Pendulum Button Specific Styles */
        .jezel-pendulum-btn {
            background-color: #1a1a1a !important;
            color: white !important;
            flex-direction: column;
            padding: 4px 2px !important;
        }
        
        .jezel-pendulum-btn:hover {
            background-color: #333333 !important;
        }
        
        .jezel-pendulum-btn span {
            color: white !important;
            line-height: 1;
        }
        
        /* Jezel Frontend Button Specific Styles */
        .jezel-frontend-btn {
            background-color: #4a4a4a !important; /* Dark gray */
            color: white !important;
            flex-direction: column;
            padding: 6px 2px !important;
            justify-content: center;
        }
        
        .jezel-frontend-btn:hover {
            background-color: #5a5a5a !important;
        }
        
        .jezel-frontend-btn span {
            color: white !important;
        }
        
        /* Adjust for collapsed admin menu */
        body.folded .jezel-nav-container {
            left: 56px;
        }
        
        /* Hide on mobile where admin menu is hidden */
        @media screen and (max-width: 782px) {
            .jezel-nav-container {
                display: none;
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
        
        // Aggressive notice removal via JavaScript
        function removeTelescoperNotices() {
            // Remove all WordPress admin notices
            $('.notice, .notice-warning, .notice-error, .notice-success, .notice-info').remove();
            $('.updated, .error, .update-nag, .admin-notice').remove();
            $('#wpbody-content > .notice, #wpbody-content > .updated, #wpbody-content > .error').remove();
            $('.wp-header-end ~ .notice, .wp-header-end ~ .updated, .wp-header-end ~ .error').remove();
            
            // Remove inline messages
            $('.inline-notice, .inline-updated, .inline-error').remove();
            
            // Remove any dynamically added notices
            if (window.wp && window.wp.data && window.wp.data.dispatch) {
                const noticesStore = wp.data.dispatch('core/notices');
                if (noticesStore && noticesStore.removeAllNotices) {
                    noticesStore.removeAllNotices();
                }
            }
        }
        
        // Initial removal
        removeTelescoperNotices();
        
        // Continuous monitoring and removal
        setInterval(removeTelescoperNotices, 500);
        
        // Monitor DOM mutations for new notices
        const observer = new MutationObserver(function(mutations) {
            removeTelescoperNotices();
        });
        
        // Start observing
        if (document.getElementById('wpbody-content')) {
            observer.observe(document.getElementById('wpbody-content'), {
                childList: true,
                subtree: true
            });
        }
    });
    </script>
    <?php
}

/**
 * Render the post selector table
 */
function telescope_render_post_selector() {
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
    
    <div class="telescope-selector">
        <h2>Select a Page or Post to Edit</h2>
        
        <table class="telescope-posts-table">
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
                        <a href="?page=telescope_content_editor&post=<?php echo $post->ID; ?>" 
                           class="button button-primary">
                            Edit in Telescope
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <style>
    .telescope-selector {
        padding: 20px 0;
    }
    
    .telescope-posts-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .telescope-posts-table th {
        background: #f5f5f5;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #ddd;
    }
    
    .telescope-posts-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    
    .telescope-posts-table tr:hover {
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
 * Render the edit form for a specific post
 */
function telescope_render_edit_form($post_id) {
    global $wpdb;
    
    // Get post data
    $post = get_post($post_id);
    if (!$post) {
        echo '<div class="telescope-notice error">Post not found!</div>';
        return;
    }
    
    // Get plasma_pages data if it exists
    $plasma_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}plasma_pages WHERE rel_wp_page_id = %d",
        $post_id
    ), ARRAY_A);
    
    // Define all the database columns for Cherry template
    $fields = [
        'post_title' => ['type' => 'text', 'table' => 'posts'],
        'hero_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'hero_style_setting_background_size' => ['type' => 'text', 'table' => 'pylons'],
        'paragon_featured_image_id' => ['type' => 'text', 'table' => 'pylons'],
        'driggs_phone_1' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'chenblock_card1_title' => ['type' => 'text', 'table' => 'pylons'],
        'chenblock_card1_desc' => ['type' => 'textarea', 'table' => 'pylons'],
        'chenblock_card2_title' => ['type' => 'text', 'table' => 'pylons'],
        'chenblock_card2_desc' => ['type' => 'textarea', 'table' => 'pylons'],
        'chenblock_card3_title' => ['type' => 'text', 'table' => 'pylons'],
        'chenblock_card3_desc' => ['type' => 'textarea', 'table' => 'pylons'],
        'post_content' => ['type' => 'textarea', 'table' => 'posts'],
        'osb_box_title' => ['type' => 'text', 'table' => 'pylons'],
        'osb_services_per_row' => ['type' => 'text', 'table' => 'pylons'],
        'osb_max_services_display' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_q1' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_a1' => ['type' => 'textarea', 'table' => 'pylons'],
        'serena_faq_box_q2' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_a2' => ['type' => 'textarea', 'table' => 'pylons'],
        'serena_faq_box_q3' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_a3' => ['type' => 'textarea', 'table' => 'pylons'],
        'serena_faq_box_q4' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_a4' => ['type' => 'textarea', 'table' => 'pylons'],
        'serena_faq_box_q5' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_a5' => ['type' => 'textarea', 'table' => 'pylons'],
        'serena_faq_box_q6' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_a6' => ['type' => 'textarea', 'table' => 'pylons'],
        'serena_faq_box_q7' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_a7' => ['type' => 'textarea', 'table' => 'pylons'],
        'serena_faq_box_q8' => ['type' => 'text', 'table' => 'pylons'],
        'serena_faq_box_a8' => ['type' => 'textarea', 'table' => 'pylons'],
        'pylon_archetype' => ['type' => 'text', 'table' => 'pylons'],
        'locpage_gmaps_string' => ['type' => 'text', 'table' => 'pylons'],
        'driggs_city' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_state_full' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_state_code' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_country' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_brand_name' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'content_ocean_1' => ['type' => 'textarea', 'table' => 'pylons'],
        'content_ocean_2' => ['type' => 'textarea', 'table' => 'pylons'],
        'content_ocean_3' => ['type' => 'textarea', 'table' => 'pylons'],
        'brook_video_heading' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_description' => ['type' => 'textarea', 'table' => 'pylons'],
        'brook_video_1' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_2' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_3' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_4' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_outro' => ['type' => 'textarea', 'table' => 'pylons'],
        'olivia_authlinks_heading' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_description' => ['type' => 'textarea', 'table' => 'pylons'],
        'olivia_authlinks_1' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_2' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_3' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_4' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_5' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_6' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_7' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_8' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_9' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_10' => ['type' => 'text', 'table' => 'pylons'],
        'olivia_authlinks_outro' => ['type' => 'textarea', 'table' => 'pylons'],
        'ava_why_choose_us_heading' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_description' => ['type' => 'textarea', 'table' => 'pylons'],
        'ava_why_choose_us_reason_1' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_2' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_3' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_4' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_5' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_6' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_heading' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_description' => ['type' => 'textarea', 'table' => 'pylons'],
        'kendall_our_process_step_1' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_step_2' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_step_3' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_step_4' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_outro' => ['type' => 'textarea', 'table' => 'pylons'],
        'sara_customhtml_datum' => ['type' => 'textarea', 'table' => 'pylons'],
        'liz_pricing_heading' => ['type' => 'text', 'table' => 'pylons'],
        'liz_pricing_description' => ['type' => 'text', 'table' => 'pylons'],
        'liz_pricing_body' => ['type' => 'textarea', 'table' => 'pylons'],
        'baynar1_main' => ['type' => 'textarea', 'table' => 'pylons']
    ];
    ?>
    
    <div class="telescope-editor">
        <div class="telescope-editor-header">
            <h2>Editing: <?php echo esc_html($post->post_title); ?></h2>
            <a href="?page=telescope_content_editor" class="button">← Back to Post List</a>
        </div>
        
        <form method="post" action="" class="telescope-form">
            <?php wp_nonce_field('telescope_save_' . $post_id, 'telescope_nonce'); ?>
            <input type="hidden" name="telescope_save" value="1">
            
            <!-- Save button at top -->
            <div class="telescope-actions-top">
                <button type="submit" class="button button-primary button-large">💾 Save Changes</button>
            </div>
            
            <!-- Main editing table -->
            <table class="telescope-edit-table">
                <thead>
                    <tr>
                        <th width="25%">Field Name</th>
                        <th width="50%">Datum House</th>
                        <th width="25%">Misc Stuff</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Get data from multiple tables
                    $pylon_data = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                        $post_id
                    ), ARRAY_A);
                    
                    $sitespren_data = $wpdb->get_row(
                        "SELECT * FROM {$wpdb->prefix}zen_sitespren LIMIT 1",
                        ARRAY_A
                    );
                    
                    foreach ($fields as $field_name => $field_config): 
                        // Get value based on table
                        $value = '';
                        if ($field_config['table'] === 'posts') {
                            if ($field_name === 'post_title') {
                                $value = $post->post_title;
                            } elseif ($field_name === 'post_content') {
                                $value = $post->post_content;
                            }
                        } elseif ($field_config['table'] === 'pylons' && $pylon_data) {
                            $value = isset($pylon_data[$field_name]) ? $pylon_data[$field_name] : '';
                        } elseif ($field_config['table'] === 'zen_sitespren' && $sitespren_data) {
                            $value = isset($sitespren_data[$field_name]) ? $sitespren_data[$field_name] : '';
                        }
                        
                        // Determine appropriate rows for textareas
                        $rows = 4;
                        if (in_array($field_name, ['post_content', 'content_ocean_1', 'content_ocean_2', 'content_ocean_3', 'sara_customhtml_datum', 'baynar1_main'])) {
                            $rows = 8;
                        }
                    ?>
                    <tr>
                        <td class="field-name">
                            <strong style="font-size: 16px; text-transform: lowercase; display: block;">
                                <?php echo esc_html($field_name); ?>
                            </strong>
                        </td>
                        <td class="datum-house">
                            <?php if ($field_config['type'] === 'textarea'): ?>
                                <textarea 
                                    name="field_<?php echo esc_attr($field_name); ?>"
                                    rows="<?php echo $rows; ?>"
                                    class="telescope-field-input"
                                    data-table="<?php echo esc_attr($field_config['table']); ?>"
                                ><?php echo esc_textarea($value); ?></textarea>
                            <?php else: ?>
                                <input 
                                    type="text" 
                                    name="field_<?php echo esc_attr($field_name); ?>"
                                    value="<?php echo esc_attr($value); ?>"
                                    class="telescope-field-input"
                                    data-table="<?php echo esc_attr($field_config['table']); ?>"
                                />
                            <?php endif; ?>
                        </td>
                        <td class="misc-stuff">
                            <!-- Reserved for future use -->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Save button at bottom -->
            <div class="telescope-actions-bottom">
                <button type="submit" class="button button-primary button-large">💾 Save Changes</button>
            </div>
        </form>
    </div>
    
    <style>
    .telescope-editor {
        padding: 20px 0;
    }
    
    .telescope-editor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .telescope-editor-header h2 {
        margin: 0;
    }
    
    .telescope-actions-top,
    .telescope-actions-bottom {
        padding: 15px 0;
    }
    
    .telescope-edit-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .telescope-edit-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }
    
    .telescope-edit-table td {
        padding: 15px;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }
    
    .telescope-edit-table tr:hover {
        background: #f9f9f9;
    }
    
    .field-name {
        font-size: 14px;
    }
    
    .field-name strong {
        font-weight: bold;
        color: #333;
    }
    
    .telescope-field-input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .telescope-field-input:focus {
        border-color: #764ba2;
        outline: none;
        box-shadow: 0 0 0 2px rgba(118, 75, 162, 0.1);
    }
    
    textarea.telescope-field-input {
        font-family: 'Monaco', 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.5;
    }
    
    .telescope-notice {
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .telescope-notice.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .telescope-notice.error {
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
 * Save post data from Telescope form
 */
function telescope_save_post_data($post_id, $form_data) {
    global $wpdb;
    
    // Separate fields by table
    $posts_fields = [];
    $pylons_fields = [];
    $sitespren_fields = [];
    
    foreach ($form_data as $key => $value) {
        if (strpos($key, 'field_') === 0) {
            $field_name = str_replace('field_', '', $key);
            
            // Determine which table this field belongs to
            if ($field_name === 'post_title') {
                $posts_fields['post_title'] = $value;
            } elseif ($field_name === 'post_content') {
                $posts_fields['post_content'] = $value;
            } elseif (strpos($field_name, 'driggs_') === 0) {
                $sitespren_fields[$field_name] = $value;
            } else {
                // Everything else goes to pylons
                $pylons_fields[$field_name] = $value;
            }
        }
    }
    
    // Update wp_posts if needed
    if (!empty($posts_fields)) {
        $wpdb->update(
            $wpdb->posts,
            $posts_fields,
            ['ID' => $post_id]
        );
    }
    
    // Update wp_pylons if needed
    if (!empty($pylons_fields)) {
        // Check if record exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        if ($existing) {
            // Update existing record
            $wpdb->update(
                $wpdb->prefix . 'pylons',
                $pylons_fields,
                ['rel_wp_post_id' => $post_id]
            );
        } else {
            // Insert new record
            $pylons_fields['rel_wp_post_id'] = $post_id;
            $wpdb->insert(
                $wpdb->prefix . 'pylons',
                $pylons_fields
            );
        }
    }
    
    // Update wp_zen_sitespren if needed (updates the single global record)
    if (!empty($sitespren_fields)) {
        // Check if record exists
        $existing = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}zen_sitespren LIMIT 1");
        
        if ($existing) {
            // Update existing record
            $wpdb->update(
                $wpdb->prefix . 'zen_sitespren',
                $sitespren_fields,
                ['id' => $existing]
            );
        } else {
            // Insert new record
            $wpdb->insert(
                $wpdb->prefix . 'zen_sitespren',
                $sitespren_fields
            );
        }
    }
}