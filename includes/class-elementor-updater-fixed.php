<?php
/**
 * FIXED Elementor Updater - Properly triggers WordPress revisions
 */

// Key changes needed in update_elementor_data function:

// CURRENT (BROKEN):
// Only updates meta directly, bypassing WordPress
update_post_meta($post_id, '_elementor_data', $elementor_data);
wp_update_post(array(
    'ID' => $post_id,
    'post_modified' => current_time('mysql')
));

// FIXED VERSION:
// Properly triggers WordPress save process and revisions
function update_elementor_data_fixed($post_id, $elementor_data) {
    
    // 1. Store current post for revision
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('post_not_found', 'Post not found');
    }
    
    // 2. Trigger pre-save hooks (critical for Elementor)
    do_action('elementor/editor/before_save', $post_id, $elementor_data);
    
    // 3. Update the post content to trigger revision
    // Even if we don't change content, updating it triggers revision system
    $update_args = array(
        'ID' => $post_id,
        'post_content' => $post->post_content, // Keep same content
        'post_modified' => current_time('mysql'),
        'post_modified_gmt' => current_time('mysql', 1)
    );
    
    // Remove this filter temporarily to allow revision creation
    remove_filter('wp_save_post_revision_post_has_changed', '__return_false');
    
    // This will create a revision
    $result = wp_update_post($update_args, true);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    // 4. NOW update the Elementor meta
    update_post_meta($post_id, '_elementor_data', $elementor_data);
    update_post_meta($post_id, '_elementor_version', ELEMENTOR_VERSION);
    update_post_meta($post_id, '_elementor_edit_mode', 'builder');
    
    // 5. Trigger Elementor's save hooks (regenerates CSS properly)
    do_action('elementor/editor/after_save', $post_id, $elementor_data);
    
    // 6. Clear and regenerate CSS (Elementor way)
    if (class_exists('\Elementor\Plugin')) {
        // Get document
        $document = \Elementor\Plugin::$instance->documents->get($post_id);
        if ($document) {
            // Save settings to regenerate everything
            $document->save([
                'settings' => $document->get_settings(),
                'elements' => json_decode($elementor_data, true)
            ]);
        }
    }
    
    // 7. Clear all caches
    $this->clear_and_regenerate_caches($post_id);
    
    return array(
        'success' => true,
        'revision_created' => true,
        'post_id' => $post_id
    );
}

/**
 * Alternative: Force revision creation
 */
function force_revision_creation($post_id) {
    // Get the post
    $post = get_post($post_id);
    
    // Create revision manually
    $revision_id = _wp_put_post_revision($post);
    
    return $revision_id;
}