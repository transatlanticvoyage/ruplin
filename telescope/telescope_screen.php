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
    
    // Enqueue media scripts for image selection
    wp_enqueue_media();
    
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
        
        // DEBUG: Check both baynar fields before save
        $baynar1_value_sent = isset($_POST['field_baynar1_main']) ? $_POST['field_baynar1_main'] : '(NOT SENT)';
        $baynar2_value_sent = isset($_POST['field_baynar2_main']) ? $_POST['field_baynar2_main'] : '(NOT SENT)';
        error_log("TELESCOPE FORM SUBMISSION - baynar1_main value: '" . substr($baynar1_value_sent, 0, 100) . "...'");
        error_log("TELESCOPE FORM SUBMISSION - baynar2_main value: '" . substr($baynar2_value_sent, 0, 100) . "...'");
        
        // Save the data
        $save_result = telescope_save_post_data($post_id, $_POST);
        
        // Check if important fields were actually saved
        global $wpdb;
        $saved_baynar1 = $wpdb->get_var($wpdb->prepare(
            "SELECT baynar1_main FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        $saved_baynar2 = $wpdb->get_var($wpdb->prepare(
            "SELECT baynar2_main FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        $saved_ocean1 = $wpdb->get_var($wpdb->prepare(
            "SELECT content_ocean_1 FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        $saved_ocean2 = $wpdb->get_var($wpdb->prepare(
            "SELECT content_ocean_2 FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        $saved_ocean3 = $wpdb->get_var($wpdb->prepare(
            "SELECT content_ocean_3 FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        // Show detailed save status for both fields
        $errors = [];
        $successes = [];
        
        if ($baynar1_value_sent !== '(NOT SENT)') {
            if ($saved_baynar1 === $baynar1_value_sent) {
                $successes[] = "✅ Baynar1_main saved correctly";
            } else {
                $errors[] = "❌ Baynar1_main FAILED - Sent: '" . esc_html(substr($baynar1_value_sent, 0, 30)) . "...' | Saved: '" . esc_html(substr($saved_baynar1 ?: '(NULL)', 0, 30)) . "...'";
            }
        }
        
        if ($baynar2_value_sent !== '(NOT SENT)') {
            if ($saved_baynar2 === $baynar2_value_sent) {
                $successes[] = "✅ Baynar2_main saved correctly";
            } else {
                $errors[] = "❌ Baynar2_main FAILED - Sent: '" . esc_html(substr($baynar2_value_sent, 0, 30)) . "...' | Saved: '" . esc_html(substr($saved_baynar2 ?: '(NULL)', 0, 30)) . "...'";
            }
        }
        
        // Check ocean fields
        $ocean1_value_sent = isset($_POST['field_content_ocean_1']) ? $_POST['field_content_ocean_1'] : '(NOT SENT)';
        $ocean2_value_sent = isset($_POST['field_content_ocean_2']) ? $_POST['field_content_ocean_2'] : '(NOT SENT)';
        $ocean3_value_sent = isset($_POST['field_content_ocean_3']) ? $_POST['field_content_ocean_3'] : '(NOT SENT)';
        
        if ($ocean1_value_sent !== '(NOT SENT)') {
            if ($saved_ocean1 === $ocean1_value_sent) {
                $successes[] = "✅ Content_ocean_1 saved correctly";
            } else {
                $errors[] = "❌ Content_ocean_1 FAILED - Sent: '" . esc_html(substr($ocean1_value_sent, 0, 30)) . "...' | Saved: '" . esc_html(substr($saved_ocean1 ?: '(NULL)', 0, 30)) . "...'";
            }
        }
        
        if ($ocean2_value_sent !== '(NOT SENT)') {
            if ($saved_ocean2 === $ocean2_value_sent) {
                $successes[] = "✅ Content_ocean_2 saved correctly";
            } else {
                $errors[] = "❌ Content_ocean_2 FAILED - Sent: '" . esc_html(substr($ocean2_value_sent, 0, 30)) . "...' | Saved: '" . esc_html(substr($saved_ocean2 ?: '(NULL)', 0, 30)) . "...'";
            }
        }
        
        if ($ocean3_value_sent !== '(NOT SENT)') {
            if ($saved_ocean3 === $ocean3_value_sent) {
                $successes[] = "✅ Content_ocean_3 saved correctly";
            } else {
                $errors[] = "❌ Content_ocean_3 FAILED - Sent: '" . esc_html(substr($ocean3_value_sent, 0, 30)) . "...' | Saved: '" . esc_html(substr($saved_ocean3 ?: '(NULL)', 0, 30)) . "...'";
            }
        }
        
        // Display status
        if (!empty($errors)) {
            echo '<div class="telescope-notice error">⚠️ Save Issues:<br>' . implode('<br>', $errors) . '</div>';
        }
        if (!empty($successes)) {
            echo '<div class="telescope-notice success">' . implode('<br>', $successes) . '</div>';
        }
        if (empty($errors) && empty($successes)) {
            echo '<div class="telescope-notice success">Data saved successfully!</div>';
        }
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
        // ========== NON-TEMPLATE FIELDS (General/Utility) ==========
        'post_title' => ['type' => 'text', 'table' => 'posts'],
        'hero_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'hero_style_setting_background_size' => ['type' => 'text', 'table' => 'pylons'],
        'paragon_featured_image_id' => ['type' => 'text', 'table' => 'pylons'],
        'pylon_archetype' => ['type' => 'text', 'table' => 'pylons'],
        'locpage_gmaps_string' => ['type' => 'text', 'table' => 'pylons'],
        'driggs_phone_1' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_city' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_state_full' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_state_code' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_country' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'driggs_brand_name' => ['type' => 'text', 'table' => 'zen_sitespren'],
        'baynar1_main' => ['type' => 'textarea', 'table' => 'pylons'],
        'baynar2_main' => ['type' => 'textarea', 'table' => 'pylons'],
        
        // ========== SEPARATOR ==========
        '__separator__' => ['type' => 'separator', 'table' => 'none'],
        
        // ========== CHERRY TEMPLATE FIELDS (in order of rendering) ==========
        
        // 1. Chen Cards (renders first after hero)
        'chenblock_card1_title' => ['type' => 'text', 'table' => 'pylons'],
        'chenblock_card1_desc' => ['type' => 'textarea', 'table' => 'pylons'],
        'chenblock_card2_title' => ['type' => 'text', 'table' => 'pylons'],
        'chenblock_card2_desc' => ['type' => 'textarea', 'table' => 'pylons'],
        'chenblock_card3_title' => ['type' => 'text', 'table' => 'pylons'],
        'chenblock_card3_desc' => ['type' => 'textarea', 'table' => 'pylons'],
        
        // 2. Content Bay 1
        'content_bay_1' => ['type' => 'textarea', 'table' => 'pylons'],
        'content_bay_1_image_id' => ['type' => 'media_select', 'table' => 'pylons'],
        
        // 3. Content Bay 2
        'content_bay_2' => ['type' => 'textarea', 'table' => 'pylons'],
        'content_bay_2_image_id' => ['type' => 'media_select', 'table' => 'pylons'],
        
        // 4. Content Lake
        'content_lake' => ['type' => 'textarea', 'table' => 'pylons'],
        
        // 5. Content Sea
        'content_sea' => ['type' => 'textarea', 'table' => 'pylons'],
        
        // 6. Main Post Content
        'post_content' => ['type' => 'textarea', 'table' => 'posts'],
        
        // 7. OSB Box
        'osb_box_title' => ['type' => 'text', 'table' => 'pylons'],
        'osb_services_per_row' => ['type' => 'text', 'table' => 'pylons'],
        'osb_max_services_display' => ['type' => 'text', 'table' => 'pylons'],
        
        // 8. Serena FAQ Box
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
        
        // 9. Nile Map Box (no fields - just renders map)
        
        // 10. Kristina CTA Box (no fields - uses global data)
        
        // 11. Victoria Blog Box (no fields - pulls recent posts)
        
        // 12. Ocean Content Boxes
        'content_ocean_1' => ['type' => 'textarea', 'table' => 'pylons'],
        'content_ocean_2' => ['type' => 'textarea', 'table' => 'pylons'],
        'content_ocean_3' => ['type' => 'textarea', 'table' => 'pylons'],
        
        // 13. Brook Video Box
        'brook_video_heading' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_description' => ['type' => 'textarea', 'table' => 'pylons'],
        'brook_video_1' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_2' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_3' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_4' => ['type' => 'text', 'table' => 'pylons'],
        'brook_video_outro' => ['type' => 'textarea', 'table' => 'pylons'],
        
        // 14. Olivia Auth Links Box
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
        
        // 15. Ava Why Choose Us Box
        'ava_why_choose_us_heading' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_description' => ['type' => 'textarea', 'table' => 'pylons'],
        'ava_why_choose_us_reason_1' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_2' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_3' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_4' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_5' => ['type' => 'text', 'table' => 'pylons'],
        'ava_why_choose_us_reason_6' => ['type' => 'text', 'table' => 'pylons'],
        
        // 16. Kendall Our Process Box
        'kendall_our_process_heading' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_subheading' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_description' => ['type' => 'textarea', 'table' => 'pylons'],
        'kendall_our_process_step_1' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_step_2' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_step_3' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_step_4' => ['type' => 'text', 'table' => 'pylons'],
        'kendall_our_process_outro' => ['type' => 'textarea', 'table' => 'pylons'],
        
        // 17. Sara Custom HTML Box
        'sara_customhtml_datum' => ['type' => 'textarea', 'table' => 'pylons'],
        
        // 18. Liz Pricing Box
        'liz_pricing_heading' => ['type' => 'text', 'table' => 'pylons'],
        'liz_pricing_description' => ['type' => 'text', 'table' => 'pylons'],
        'liz_pricing_body' => ['type' => 'textarea', 'table' => 'pylons']
    ];
    ?>
    
    <div class="telescope-editor">
        <!-- Debug Feedback Box -->
        <div id="telescope-debug-box" style="background: #f0f8ff; border: 2px solid #4a90e2; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <h3 style="margin-top: 0; color: #2c5aa0;">🔍 Debug Feedback for baynar1_main & baynar2_main</h3>
            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <button type="button" id="test-baynar-btn" class="button button-primary" onclick="testBaynarField()">
                    🧪 Run Diagnostics
                </button>
                <button type="button" id="copy-debug-btn" class="button" onclick="copyDebugLog()">
                    📋 Copy Debug Log
                </button>
                <button type="button" id="clear-debug-btn" class="button" onclick="clearDebugLog()">
                    🗑️ Clear Log
                </button>
            </div>
            <textarea id="debug-output" readonly style="width: 100%; height: 200px; font-family: monospace; font-size: 12px; background: white; border: 1px solid #ddd; padding: 10px;">Click 'Run Diagnostics' to test baynar1_main and baynar2_main fields...</textarea>
        </div>
        
        <div class="telescope-editor-header">
            <h2>Editing: <?php echo esc_html($post->post_title); ?></h2>
            <a href="?page=telescope_content_editor" class="button">← Back to Post List</a>
        </div>
        
        <form method="post" action="" class="telescope-form" id="telescope-form">
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
                        // Handle separator row
                        if ($field_config['type'] === 'separator') {
                    ?>
                    <tr>
                        <td colspan="3" style="background: #000; height: 3px; padding: 0;"></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="background: #f0f0f0; padding: 10px; text-align: center; font-weight: bold; font-size: 14px; text-transform: uppercase;">
                            Cherry Template Fields (In Order of Rendering)
                        </td>
                    </tr>
                    <?php
                            continue;
                        }
                        
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
                        if (in_array($field_name, ['post_content', 'content_ocean_1', 'content_ocean_2', 'content_ocean_3', 'sara_customhtml_datum', 'baynar1_main', 'baynar2_main', 'content_bay_1', 'content_bay_2', 'content_lake', 'content_sea'])) {
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
                            <?php elseif ($field_config['type'] === 'media_select'): ?>
                                <div class="telescope-media-selector">
                                    <input 
                                        type="text" 
                                        id="field_<?php echo esc_attr($field_name); ?>"
                                        name="field_<?php echo esc_attr($field_name); ?>"
                                        value="<?php echo esc_attr($value); ?>"
                                        class="telescope-field-input telescope-media-id"
                                        data-table="<?php echo esc_attr($field_config['table']); ?>"
                                        placeholder="Image ID"
                                        style="width: 100px;"
                                    />
                                    <button type="button" 
                                            class="button telescope-media-button" 
                                            data-target="field_<?php echo esc_attr($field_name); ?>"
                                            data-preview="preview_<?php echo esc_attr($field_name); ?>">
                                        Select Image
                                    </button>
                                    <button type="button" 
                                            class="button telescope-media-remove" 
                                            data-target="field_<?php echo esc_attr($field_name); ?>"
                                            data-preview="preview_<?php echo esc_attr($field_name); ?>">
                                        Remove
                                    </button>
                                    <div id="preview_<?php echo esc_attr($field_name); ?>" class="telescope-media-preview" style="margin-top: 10px;">
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
    
    <script>
    function addDebugLine(message) {
        const output = document.getElementById('debug-output');
        const timestamp = new Date().toLocaleTimeString();
        output.value += `[${timestamp}] ${message}\n`;
        output.scrollTop = output.scrollHeight;
    }
    
    function testBaynarField() {
        clearDebugLog();
        addDebugLine('=== STARTING BAYNAR FIELDS DIAGNOSTICS ===');
        
        // 1. Check if baynar1_main field exists in DOM
        addDebugLine('\n--- BAYNAR1_MAIN CHECK ---');
        const baynar1Field = document.querySelector('textarea[name="field_baynar1_main"]');
        if (baynar1Field) {
            addDebugLine('✓ baynar1_main found in DOM');
            addDebugLine('  Current value length: ' + baynar1Field.value.length + ' characters');
            addDebugLine('  First 50 chars: "' + baynar1Field.value.substring(0, 50) + '..."');
        } else {
            addDebugLine('✗ ERROR: baynar1_main field not found in DOM!');
        }
        
        // 2. Check if baynar2_main field exists in DOM
        addDebugLine('\n--- BAYNAR2_MAIN CHECK ---');
        const baynar2Field = document.querySelector('textarea[name="field_baynar2_main"]');
        if (baynar2Field) {
            addDebugLine('✓ baynar2_main found in DOM');
            addDebugLine('  Current value length: ' + baynar2Field.value.length + ' characters');
            addDebugLine('  First 50 chars: "' + baynar2Field.value.substring(0, 50) + '..."');
        } else {
            addDebugLine('✗ ERROR: baynar2_main field not found in DOM!');
        }
        
        // 3. Check form data collection
        addDebugLine('\n=== FORM DATA COLLECTION TEST ===');
        const form = document.getElementById('telescope-form');
        const formData = new FormData(form);
        let baynar1Found = false;
        let baynar2Found = false;
        
        for (let [key, value] of formData.entries()) {
            if (key === 'field_baynar1_main') {
                addDebugLine('✓ baynar1_main in FormData: "' + value.substring(0, 50) + '..."');
                baynar1Found = true;
            }
            if (key === 'field_baynar2_main') {
                addDebugLine('✓ baynar2_main in FormData: "' + value.substring(0, 50) + '..."');
                baynar2Found = true;
            }
        }
        
        if (!baynar1Found) {
            addDebugLine('✗ ERROR: baynar1_main not in form data!');
        }
        if (!baynar2Found) {
            addDebugLine('✗ ERROR: baynar2_main not in form data!');
        }
        
        // 4. Database and column check
        addDebugLine('\n=== DATABASE CHECK ===');
        addDebugLine('Post ID: <?php echo $post_id; ?>');
        
        <?php
        global $wpdb;
        $current_val1 = $wpdb->get_var($wpdb->prepare(
            "SELECT baynar1_main FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        $current_val2 = $wpdb->get_var($wpdb->prepare(
            "SELECT baynar2_main FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        $column1_exists = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}pylons LIKE 'baynar1_main'");
        $column2_exists = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}pylons LIKE 'baynar2_main'");
        ?>
        
        addDebugLine('baynar1_main DB value: "<?php echo esc_js(substr($current_val1 ?: '(NULL/EMPTY)', 0, 50)); ?>..."');
        addDebugLine('baynar2_main DB value: "<?php echo esc_js(substr($current_val2 ?: '(NULL/EMPTY)', 0, 50)); ?>..."');
        addDebugLine('Column baynar1_main exists: <?php echo $column1_exists ? "YES" : "NO"; ?>');
        addDebugLine('Column baynar2_main exists: <?php echo $column2_exists ? "YES" : "NO"; ?>');
        
        // 4. Check all field names being sent
        addDebugLine('\n=== ALL FIELD NAMES IN FORM ===');
        const inputs = form.querySelectorAll('input[name^="field_"], textarea[name^="field_"], select[name^="field_"]');
        inputs.forEach(input => {
            if (input.name.includes('baynar') || input.name.includes('main')) {
                addDebugLine('  ' + input.name + ' (type: ' + input.type + ')');
            }
        });
        
        addDebugLine('\n=== DIAGNOSTICS COMPLETE ===');
        addDebugLine('Check PHP error logs after saving for server-side debug info');
    }
    
    function copyDebugLog() {
        const output = document.getElementById('debug-output');
        output.select();
        document.execCommand('copy');
        alert('Debug log copied to clipboard!');
    }
    
    function clearDebugLog() {
        document.getElementById('debug-output').value = 'Debug log cleared. Click "Run Diagnostics" to start...\n';
    }
    
    // Auto-run diagnostics on page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(testBaynarField, 500);
    });
    
    // Media Library JavaScript
    jQuery(document).ready(function($) {
        // Enqueue media if not already loaded
        if (typeof wp !== 'undefined' && typeof wp.media !== 'undefined') {
            
            // Handle Select Image button clicks
            $('.telescope-media-select').on('click', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const targetField = $('#' + button.data('target'));
                const previewDiv = $('#' + button.data('preview'));
                
                // Create media frame
                const mediaFrame = wp.media({
                    title: 'Select Image',
                    button: {
                        text: 'Use This Image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                // When image is selected
                mediaFrame.on('select', function() {
                    const attachment = mediaFrame.state().get('selection').first().toJSON();
                    
                    // Update the field with image ID
                    targetField.val(attachment.id);
                    
                    // Update preview
                    let thumbnailUrl = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    previewDiv.html('<img src="' + thumbnailUrl + '" style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 3px;">');
                });
                
                // Open the media frame
                mediaFrame.open();
            });
            
            // Handle Remove button clicks
            $('.telescope-media-remove').on('click', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const targetField = $('#' + button.data('target'));
                const previewDiv = $('#' + button.data('preview'));
                
                // Clear the field value
                targetField.val('');
                
                // Clear the preview
                previewDiv.html('');
            });
        }
    });
    </script>
    <?php
}

/**
 * Save post data from Telescope form
 */
function telescope_save_post_data($post_id, $form_data) {
    global $wpdb;
    
    // DEBUG: Log all incoming form data
    error_log("===== TELESCOPE SAVE DEBUG START =====");
    error_log("Post ID: " . $post_id);
    error_log("Form data keys: " . implode(', ', array_keys($form_data)));
    
    // DEBUG: Check for all important fields
    $fields_to_check = ['baynar1_main', 'baynar2_main', 'content_ocean_1', 'content_ocean_2', 'content_ocean_3', 'content_bay_1', 'content_bay_2', 'content_lake', 'content_sea'];
    foreach ($fields_to_check as $field_check) {
        $field_found = false;
        foreach ($form_data as $key => $value) {
            if (strpos($key, $field_check) !== false) {
                error_log("Found $field_check: $key = '" . substr($value, 0, 100) . "...'");
                $field_found = true;
                break;
            }
        }
        if (!$field_found) {
            error_log("⚠️ WARNING: $field_check not found in form data!");
        }
    }
    
    // Separate fields by table
    $posts_fields = [];
    $pylons_fields = [];
    $sitespren_fields = [];
    
    foreach ($form_data as $key => $value) {
        if (strpos($key, 'field_') === 0) {
            $field_name = str_replace('field_', '', $key);
            
            // Remove WordPress/PHP magic quotes slashes
            $value = wp_unslash($value);
            
            // DEBUG: Log field processing for important fields
            if (in_array($field_name, ['baynar1_main', 'baynar2_main', 'content_ocean_1', 'content_ocean_2', 'content_ocean_3', 'content_bay_1', 'content_bay_2', 'content_lake', 'content_sea'])) {
                error_log("🎯 " . strtoupper($field_name) . " FOUND!");
                error_log("  Raw key: $key");
                error_log("  Field name: $field_name");
                error_log("  Value length: " . strlen($value));
                error_log("  First 100 chars: '" . substr($value, 0, 100) . "...'");
            }
            
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
                
                // DEBUG: Confirm important fields are going to pylons
                if (in_array($field_name, ['baynar1_main', 'baynar2_main', 'content_ocean_1', 'content_ocean_2', 'content_ocean_3', 'content_bay_1', 'content_bay_2', 'content_lake', 'content_sea'])) {
                    error_log("✓ $field_name added to pylons_fields array");
                }
            }
        }
    }
    
    // DEBUG: Log what's being saved to pylons
    error_log("PYLONS FIELDS TO SAVE: " . print_r(array_keys($pylons_fields), true));
    
    // Check all important fields before save
    $important_fields = ['baynar1_main', 'baynar2_main', 'content_ocean_1', 'content_ocean_2', 'content_ocean_3', 'content_bay_1', 'content_bay_2', 'content_lake', 'content_sea'];
    foreach ($important_fields as $field) {
        if (isset($pylons_fields[$field])) {
            error_log("✓ $field IS in pylons_fields array before save");
        } else {
            error_log("✗ $field IS NOT in pylons_fields array before save!");
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
        // DEBUG: Log before checking/updating pylons
        error_log("About to update pylons table with " . count($pylons_fields) . " fields");
        
        // Check if record exists - FIXED: column is pylon_id not id
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT pylon_id FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
            $post_id
        ));
        
        error_log("Pylons record exists? " . ($existing ? "YES (ID: $existing)" : "NO"));
        
        if ($existing) {
            // DEBUG: Build format array for the update
            $format_array = array();
            foreach ($pylons_fields as $field_name => $value) {
                if (in_array($field_name, ['pylon_id', 'rel_wp_post_id', 'plasma_page_id', 
                                           'osb_services_per_row', 'osb_max_services_display', 
                                           'paragon_featured_image_id', 'jchronology_order_for_blog_posts', 
                                           'jchronology_batch', 'exempt_from_silkweaver_menu_dynamical', 
                                           'osb_is_enabled'])) {
                    $format_array[] = '%d';  // Integer fields
                } else {
                    $format_array[] = '%s';  // String/text fields (including baynar1_main)
                }
            }
            
            error_log("Format array for update: " . json_encode($format_array));
            error_log("Number of fields: " . count($pylons_fields) . ", Number of formats: " . count($format_array));
            
            // Update existing record WITH format specifiers
            $result = $wpdb->update(
                $wpdb->prefix . 'pylons',
                $pylons_fields,
                ['rel_wp_post_id' => $post_id],
                $format_array,  // Specify formats for each field
                ['%d']  // Format for WHERE clause
            );
            
            error_log("Update result: " . ($result !== false ? "SUCCESS (rows affected: $result)" : "FAILED"));
            if ($result === false) {
                error_log("Update ERROR: " . $wpdb->last_error);
                error_log("Last query: " . $wpdb->last_query);
            } elseif ($result === 0) {
                error_log("WARNING: Update returned 0 - no rows were changed (data might be identical)");
            }
            
            // DEBUG: Verify baynar1_main was actually saved
            if (isset($pylons_fields['baynar1_main'])) {
                $saved_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT baynar1_main FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                    $post_id
                ));
                error_log("BAYNAR1_MAIN VERIFICATION:");
                error_log("  Tried to save: '" . substr($pylons_fields['baynar1_main'], 0, 50) . "...'");
                error_log("  Actually saved: '" . substr($saved_value ?: '(NULL)', 0, 50) . "...'");
                error_log("  Save successful? " . ($saved_value === $pylons_fields['baynar1_main'] ? "YES" : "NO"));
                
                // If save failed, try direct SQL update
                if ($saved_value !== $pylons_fields['baynar1_main']) {
                    error_log("⚠️ BAYNAR1_MAIN SAVE FAILED! Attempting direct SQL...");
                    $direct_sql = $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}pylons SET baynar1_main = %s WHERE rel_wp_post_id = %d",
                        $pylons_fields['baynar1_main'],
                        $post_id
                    );
                    error_log("Direct SQL: " . $direct_sql);
                    $direct_result = $wpdb->query($direct_sql);
                    error_log("Direct SQL result: " . var_export($direct_result, true));
                    
                    // Check again
                    $final_value = $wpdb->get_var($wpdb->prepare(
                        "SELECT baynar1_main FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                        $post_id
                    ));
                    error_log("Final value after direct SQL: '" . substr($final_value ?: '(NULL)', 0, 50) . "...'");
                }
            }
            
            // DEBUG: Verify baynar2_main was actually saved
            if (isset($pylons_fields['baynar2_main'])) {
                $saved_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT baynar2_main FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                    $post_id
                ));
                error_log("BAYNAR2_MAIN VERIFICATION:");
                error_log("  Tried to save: '" . substr($pylons_fields['baynar2_main'], 0, 50) . "...'");
                error_log("  Actually saved: '" . substr($saved_value ?: '(NULL)', 0, 50) . "...'");
                error_log("  Save successful? " . ($saved_value === $pylons_fields['baynar2_main'] ? "YES" : "NO"));
                
                // If save failed, try direct SQL update
                if ($saved_value !== $pylons_fields['baynar2_main']) {
                    error_log("⚠️ BAYNAR2_MAIN SAVE FAILED! Attempting direct SQL...");
                    $direct_sql = $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}pylons SET baynar2_main = %s WHERE rel_wp_post_id = %d",
                        $pylons_fields['baynar2_main'],
                        $post_id
                    );
                    error_log("Direct SQL: " . $direct_sql);
                    $direct_result = $wpdb->query($direct_sql);
                    error_log("Direct SQL result: " . var_export($direct_result, true));
                    
                    // Check again
                    $final_value = $wpdb->get_var($wpdb->prepare(
                        "SELECT baynar2_main FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                        $post_id
                    ));
                    error_log("Final value after direct SQL: '" . substr($final_value ?: '(NULL)', 0, 50) . "...'");
                }
            }
            
            // Add save verification and fallback for ocean and bay/lake/sea fields
            $content_fields = ['content_ocean_1', 'content_ocean_2', 'content_ocean_3', 'content_bay_1', 'content_bay_2', 'content_lake', 'content_sea'];
            foreach ($content_fields as $content_field) {
                if (isset($pylons_fields[$content_field])) {
                    $saved_value = $wpdb->get_var($wpdb->prepare(
                        "SELECT $content_field FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                        $post_id
                    ));
                    $field_upper = strtoupper($content_field);
                    error_log("$field_upper VERIFICATION:");
                    error_log("  Tried to save: '" . substr($pylons_fields[$content_field], 0, 50) . "...'");
                    error_log("  Actually saved: '" . substr($saved_value ?: '(NULL)', 0, 50) . "...'");
                    error_log("  Save successful? " . ($saved_value === $pylons_fields[$content_field] ? "YES" : "NO"));
                    
                    // If save failed, try direct SQL update
                    if ($saved_value !== $pylons_fields[$content_field]) {
                        error_log("⚠️ $field_upper SAVE FAILED! Attempting direct SQL...");
                        $direct_sql = $wpdb->prepare(
                            "UPDATE {$wpdb->prefix}pylons SET $content_field = %s WHERE rel_wp_post_id = %d",
                            $pylons_fields[$content_field],
                            $post_id
                        );
                        error_log("Direct SQL: " . $direct_sql);
                        $direct_result = $wpdb->query($direct_sql);
                        error_log("Direct SQL result: " . var_export($direct_result, true));
                        
                        // Check again
                        $final_value = $wpdb->get_var($wpdb->prepare(
                            "SELECT $content_field FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                            $post_id
                        ));
                        error_log("Final value after direct SQL: '" . substr($final_value ?: '(NULL)', 0, 50) . "...'");
                    }
                }
            }
            
            // COMPREHENSIVE FIX: Add verification for ALL other pylons fields
            foreach ($pylons_fields as $field_name => $field_value) {
                // Skip fields we've already verified
                if (in_array($field_name, ['baynar1_main', 'baynar2_main', 'content_ocean_1', 'content_ocean_2', 'content_ocean_3', 'content_bay_1', 'content_bay_2', 'content_lake', 'content_sea'])) {
                    continue;
                }
                
                // Verify the field was saved
                $saved_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT $field_name FROM {$wpdb->prefix}pylons WHERE rel_wp_post_id = %d",
                    $post_id
                ));
                
                // If not saved correctly, force update with direct SQL
                if ($saved_value !== $field_value && !empty($field_value)) {
                    error_log("⚠️ Field '$field_name' not saved correctly. Attempting direct SQL...");
                    $direct_sql = $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}pylons SET $field_name = %s WHERE rel_wp_post_id = %d",
                        $field_value,
                        $post_id
                    );
                    $wpdb->query($direct_sql);
                    error_log("Direct SQL executed for field: $field_name");
                }
            }
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
    
    error_log("===== TELESCOPE SAVE DEBUG END =====");
}