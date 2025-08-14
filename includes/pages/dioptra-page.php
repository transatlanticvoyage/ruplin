<?php
/**
 * dioptra page - Standalone Stellar Chamber view
 */
function snefuru_dioptra_page() {
    // Get the admin instance for accessing helper methods
    global $snefuru_admin;
    
    // AGGRESSIVE NOTICE SUPPRESSION - only if admin object exists and has the method
    if ($snefuru_admin && method_exists($snefuru_admin, 'suppress_all_admin_notices')) {
        $snefuru_admin->suppress_all_admin_notices();
    }
    
    // Get post ID from URL parameter
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    $post = null;
    
    if ($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            echo '<div class="wrap">';
            echo '<h1>Dioptra - Stellar Chamber</h1>';
            echo '<div class="notice notice-error"><p>Invalid post ID provided.</p></div>';
            echo '<p><a href="' . admin_url('edit.php') . '" class="button">← Back to Posts</a></p>';
            echo '</div>';
            return;
        }
    }
    
    // Enqueue Hurricane assets
    wp_enqueue_style('snefuru-hurricane', SNEFURU_PLUGIN_URL . 'hurricane/assets/hurricane.css', array(), SNEFURU_PLUGIN_VERSION);
    wp_enqueue_script('snefuru-hurricane', SNEFURU_PLUGIN_URL . 'hurricane/assets/hurricane.js', array('jquery'), SNEFURU_PLUGIN_VERSION, true);
    
    ?>
    <div class="wrap" style="max-width: 100%; margin: 20px;">
        <h1 style="margin-bottom: 20px;">
            Dioptra - Stellar Chamber
            <?php if ($post): ?>
                <span style="font-weight: normal; font-size: 0.7em; color: #666;">
                    | Viewing: <?php echo esc_html($post->post_title); ?> (ID: <?php echo $post_id; ?>)
                </span>
            <?php endif; ?>
        </h1>
        
        <?php if (!$post): ?>
            <div class="notice notice-info">
                <p>No post selected. Please provide a post ID in the URL (e.g., ?page=dioptra&post=448)</p>
            </div>
            
            <div style="margin-top: 20px;">
                <h3>Recent Posts</h3>
                <?php
                $recent_posts = get_posts(array(
                    'numberposts' => 10,
                    'post_type' => array('post', 'page'),
                    'post_status' => array('publish', 'draft', 'pending', 'private')
                ));
                
                if ($recent_posts) {
                    echo '<ul>';
                    foreach ($recent_posts as $recent_post) {
                        $url = admin_url('admin.php?page=dioptra&post=' . $recent_post->ID);
                        echo '<li>';
                        echo '<a href="' . esc_url($url) . '">';
                        echo esc_html($recent_post->post_title);
                        echo '</a>';
                        echo ' (' . $recent_post->post_type . ' - ' . $recent_post->post_status . ')';
                        echo '</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 20px;">
                <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>" class="button">
                    ← Edit Post in WordPress Editor
                </a>
                <a href="<?php echo admin_url('admin.php?page=dioptra'); ?>" class="button">
                    View Post Selector
                </a>
            </div>
            
            <?php
            // Initialize Hurricane and render Stellar Chamber
            if (class_exists('Snefuru_Hurricane')) {
                $hurricane = new Snefuru_Hurricane();
                
                // Check if we have a standalone render method
                if (method_exists($hurricane, 'render_stellar_chamber_standalone')) {
                    $hurricane->render_stellar_chamber_standalone($post);
                } else {
                    // Fallback: use the regular method
                    $hurricane->add_stellar_chamber($post);
                }
            } else {
                echo '<div class="notice notice-error"><p>Hurricane class not found. Please ensure the Hurricane feature is properly installed.</p></div>';
            }
            ?>
        <?php endif; ?>
    </div>
    <?php
}