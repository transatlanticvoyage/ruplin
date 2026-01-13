<?php
/**
 * Nuke Mar Handler
 * 
 * Handles the actual deletion of pages and posts
 * 
 * @package Ruplin
 * @subpackage Nuke
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Process the nuke action based on user selections
 * 
 * @param array $post_data The POST data from the form submission
 */
function ruplin_process_nuke_action($post_data) {
    global $wpdb;
    
    // Check user capabilities again
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to perform this action.', 'ruplin'));
    }
    
    $delete_pages = isset($post_data['delete_all_pages']) && $post_data['delete_all_pages'] === '1';
    $delete_posts = isset($post_data['delete_all_posts']) && $post_data['delete_all_posts'] === '1';
    $wipe_sitespren = isset($post_data['wipe_sitespren_values']) && $post_data['wipe_sitespren_values'] === '1';
    
    // Process URL exclusions
    $excluded_urls = array();
    if (isset($post_data['exclude_urls_enabled']) && $post_data['exclude_urls_enabled'] === '1' && !empty($post_data['excluded_urls'])) {
        $urls_input = sanitize_textarea_field($post_data['excluded_urls']);
        $urls_array = explode("\n", $urls_input);
        foreach ($urls_array as $url) {
            $url = trim($url);
            if (!empty($url)) {
                // Remove leading/trailing slashes and store just the slug
                $excluded_urls[] = trim($url, '/');
            }
        }
    }
    
    $pages_deleted = 0;
    $posts_deleted = 0;
    $sitespren_wiped = false;
    $errors = array();
    
    // Start transaction for better performance
    $wpdb->query('START TRANSACTION');
    
    try {
        // Delete all pages if selected
        if ($delete_pages) {
            $pages_deleted = ruplin_delete_all_content('page', $excluded_urls);
            
            // Log the action
            ruplin_log_nuke_action('pages_deleted', $pages_deleted);
        }
        
        // Delete all posts if selected
        if ($delete_posts) {
            $posts_deleted = ruplin_delete_all_content('post', $excluded_urls);
            
            // Log the action
            ruplin_log_nuke_action('posts_deleted', $posts_deleted);
        }
        
        // Wipe sitespren values if selected
        if ($wipe_sitespren) {
            $sitespren_wiped = ruplin_wipe_sitespren_values();
            
            // Log the action
            if ($sitespren_wiped) {
                ruplin_log_nuke_action('sitespren_wiped', 1);
            }
        }
        
        // Clean up any remaining orphaned data in custom tables
        $cleanup_stats = ruplin_final_cleanup_custom_tables();
        
        // Commit the transaction
        $wpdb->query('COMMIT');
        
        // Clear cache after deletion
        ruplin_clear_cache_after_nuke();
        
        // Display success message with cleanup stats
        $message_parts = array();
        if ($pages_deleted > 0) $message_parts[] = sprintf('%d pages deleted', $pages_deleted);
        if ($posts_deleted > 0) $message_parts[] = sprintf('%d posts deleted', $posts_deleted);
        if ($sitespren_wiped) $message_parts[] = 'Site configuration wiped';
        if ($cleanup_stats['pylons'] > 0) $message_parts[] = sprintf('%d pylons records cleaned', $cleanup_stats['pylons']);
        if ($cleanup_stats['orbitposts'] > 0) $message_parts[] = sprintf('%d orbitposts records cleaned', $cleanup_stats['orbitposts']);
        
        $message = 'Nuke operation completed successfully. ';
        if (!empty($message_parts)) {
            $message .= implode(', ', $message_parts) . '.';
        } else {
            $message .= 'No changes were made.';
        }
        
        add_settings_error(
            'ruplin_nuke_messages',
            'ruplin_nuke_success',
            $message,
            'success'
        );
        
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        
        add_settings_error(
            'ruplin_nuke_messages',
            'ruplin_nuke_error',
            sprintf(__('Error during nuke operation: %s', 'ruplin'), $e->getMessage()),
            'error'
        );
    }
    
    // Display admin notices
    settings_errors('ruplin_nuke_messages');
}

/**
 * Delete all content of a specific post type
 * 
 * @param string $post_type The post type to delete
 * @param array $excluded_urls Array of URL slugs to exclude from deletion
 * @return int Number of items deleted
 */
function ruplin_delete_all_content($post_type, $excluded_urls = array()) {
    global $wpdb;
    
    $deleted_count = 0;
    
    // Get all posts/pages of the specified type
    $args = array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
        'fields'         => 'ids',
        'no_found_rows'  => true,
    );
    
    // Build exclusion list
    $excluded_post_ids = array();
    
    // No automatic system page exclusions - let nuke delete everything unless manually excluded
    
    // Get post IDs to exclude based on URL slugs
    if (!empty($excluded_urls)) {
        foreach ($excluded_urls as $slug) {
            // Query for posts with matching post_name
            $post_id = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                 WHERE post_name = %s 
                 AND post_type = %s 
                 LIMIT 1",
                $slug,
                $post_type
            ));
            
            if ($post_id) {
                $excluded_post_ids[] = $post_id;
            }
        }
    }
    
    // Automatically exclude posts with 'nukeignore' or 'turtle' in their post_name
    $auto_excluded_posts = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
         WHERE (post_name LIKE %s OR post_name LIKE %s)
         AND post_type = %s",
        '%nukeignore%',
        '%turtle%',
        $post_type
    ));
    
    if (!empty($auto_excluded_posts)) {
        $excluded_post_ids = array_merge($excluded_post_ids, $auto_excluded_posts);
    }
    
    // Apply exclusions to query
    if (!empty($excluded_post_ids)) {
        $args['post__not_in'] = array_unique($excluded_post_ids);
    }
    
    $query = new WP_Query($args);
    $post_ids = $query->posts;
    
    // Delete each post/page
    foreach ($post_ids as $post_id) {
        // Force delete to bypass trash
        $result = wp_delete_post($post_id, true);
        
        if ($result !== false) {
            $deleted_count++;
            
            // Also clean up any associated metadata
            ruplin_clean_post_metadata($post_id);
        }
    }
    
    // Clean up orphaned data
    ruplin_clean_orphaned_data($post_type);
    
    return $deleted_count;
}

/**
 * Clean up post metadata after deletion
 * 
 * @param int $post_id The post ID
 */
function ruplin_clean_post_metadata($post_id) {
    global $wpdb;
    
    // Clean up postmeta
    $wpdb->delete(
        $wpdb->postmeta,
        array('post_id' => $post_id),
        array('%d')
    );
    
    // Clean up term relationships
    $wpdb->delete(
        $wpdb->term_relationships,
        array('object_id' => $post_id),
        array('%d')
    );
    
    // Clean up comments for this post
    $wpdb->delete(
        $wpdb->comments,
        array('comment_post_ID' => $post_id),
        array('%d')
    );
    
    // Clean up comment meta for deleted comments
    $wpdb->query(
        "DELETE FROM {$wpdb->commentmeta} 
         WHERE comment_id NOT IN (SELECT comment_id FROM {$wpdb->comments})"
    );
    
    // Clean up from custom tables if they exist
    $pylons_table = $wpdb->prefix . 'pylons';
    if ($wpdb->get_var("SHOW TABLES LIKE '$pylons_table'") == $pylons_table) {
        $wpdb->delete(
            $pylons_table,
            array('rel_wp_post_id' => $post_id),
            array('%d')
        );
    }
    
    $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
    if ($wpdb->get_var("SHOW TABLES LIKE '$orbitposts_table'") == $orbitposts_table) {
        $wpdb->delete(
            $orbitposts_table,
            array('rel_wp_post_id' => $post_id),
            array('%d')
        );
    }
}

/**
 * Clean up orphaned data after mass deletion
 * 
 * @param string $post_type The post type that was deleted
 */
function ruplin_clean_orphaned_data($post_type) {
    global $wpdb;
    
    // Clean up orphaned records from wp_pylons table
    $pylons_table = $wpdb->prefix . 'pylons';
    if ($wpdb->get_var("SHOW TABLES LIKE '$pylons_table'") == $pylons_table) {
        $wpdb->query(
            "DELETE py FROM {$pylons_table} py
             LEFT JOIN {$wpdb->posts} p ON py.rel_wp_post_id = p.ID
             WHERE p.ID IS NULL"
        );
    }
    
    // Clean up orphaned records from wp_zen_orbitposts table
    $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
    if ($wpdb->get_var("SHOW TABLES LIKE '$orbitposts_table'") == $orbitposts_table) {
        $wpdb->query(
            "DELETE zo FROM {$orbitposts_table} zo
             LEFT JOIN {$wpdb->posts} p ON zo.rel_wp_post_id = p.ID
             WHERE p.ID IS NULL"
        );
    }
    
    // Clean up orphaned term relationships
    $wpdb->query(
        "DELETE tr FROM {$wpdb->term_relationships} tr
         LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
         WHERE p.ID IS NULL"
    );
    
    // Clean up orphaned postmeta
    $wpdb->query(
        "DELETE pm FROM {$wpdb->postmeta} pm
         LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
         WHERE p.ID IS NULL"
    );
    
    // Update term counts
    $terms = get_terms(array(
        'taxonomy' => ($post_type === 'post') ? array('category', 'post_tag') : array(),
        'hide_empty' => false,
    ));
    
    foreach ($terms as $term) {
        wp_update_term_count($term->term_id, $term->taxonomy);
    }
}

/**
 * Log nuke actions to the database
 * 
 * @param string $action The action performed
 * @param int $count Number of items affected
 */
function ruplin_log_nuke_action($action, $count) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'snefuru_logs';
    
    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        $wpdb->insert(
            $table_name,
            array(
                'timestamp' => current_time('mysql'),
                'action' => 'nuke_mar_' . $action,
                'data' => json_encode(array(
                    'count' => $count,
                    'user_id' => get_current_user_id(),
                    'user_login' => wp_get_current_user()->user_login,
                )),
                'status' => 'completed',
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
}

/**
 * Clear various caches after nuke operation
 */
function ruplin_clear_cache_after_nuke() {
    // Clear WordPress object cache
    wp_cache_flush();
    
    // Clear rewrite rules
    flush_rewrite_rules();
    
    // Clear any transients
    $wpdb = $GLOBALS['wpdb'];
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'");
    
    // Clear WP Super Cache if active
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }
    
    // Clear W3 Total Cache if active
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }
    
    // Clear WP Rocket cache if active
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
    }
    
    // Clear Autoptimize cache if active
    if (class_exists('autoptimizeCache')) {
        autoptimizeCache::clearall();
    }
    
    // Trigger action for other plugins to clear their caches
    do_action('ruplin_after_nuke_clear_cache');
}

/**
 * Final cleanup of custom tables to ensure no orphaned records remain
 * 
 * @return array Statistics about cleaned records
 */
function ruplin_final_cleanup_custom_tables() {
    global $wpdb;
    
    $stats = array(
        'pylons' => 0,
        'orbitposts' => 0
    );
    
    // Count and clean pylons table
    $pylons_table = $wpdb->prefix . 'pylons';
    if ($wpdb->get_var("SHOW TABLES LIKE '$pylons_table'") == $pylons_table) {
        // Count orphaned records before deletion
        $orphaned_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$pylons_table} py
             LEFT JOIN {$wpdb->posts} p ON py.rel_wp_post_id = p.ID
             WHERE p.ID IS NULL AND py.rel_wp_post_id IS NOT NULL"
        );
        
        if ($orphaned_count > 0) {
            // Delete orphaned records
            $wpdb->query(
                "DELETE py FROM {$pylons_table} py
                 LEFT JOIN {$wpdb->posts} p ON py.rel_wp_post_id = p.ID
                 WHERE p.ID IS NULL AND py.rel_wp_post_id IS NOT NULL"
            );
            
            $stats['pylons'] = $orphaned_count;
        }
    }
    
    // Count and clean zen_orbitposts table
    $orbitposts_table = $wpdb->prefix . 'zen_orbitposts';
    if ($wpdb->get_var("SHOW TABLES LIKE '$orbitposts_table'") == $orbitposts_table) {
        // Count orphaned records before deletion
        $orphaned_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$orbitposts_table} zo
             LEFT JOIN {$wpdb->posts} p ON zo.rel_wp_post_id = p.ID
             WHERE p.ID IS NULL AND zo.rel_wp_post_id IS NOT NULL"
        );
        
        if ($orphaned_count > 0) {
            // Delete orphaned records
            $wpdb->query(
                "DELETE zo FROM {$orbitposts_table} zo
                 LEFT JOIN {$wpdb->posts} p ON zo.rel_wp_post_id = p.ID
                 WHERE p.ID IS NULL AND zo.rel_wp_post_id IS NOT NULL"
            );
            
            $stats['orbitposts'] = $orphaned_count;
        }
    }
    
    // Log the cleanup statistics if any records were cleaned
    if ($stats['pylons'] > 0 || $stats['orbitposts'] > 0) {
        ruplin_log_nuke_action('custom_tables_cleanup', array_sum($stats));
    }
    
    return $stats;
}

/**
 * Wipe all values in wp_zen_sitespren table except the primary key
 * 
 * @return bool Success status
 */
function ruplin_wipe_sitespren_values() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'zen_sitespren';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        error_log('Ruplin: zen_sitespren table does not exist');
        return false;
    }
    
    // Get all columns from the table
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    
    if (empty($columns)) {
        error_log('Ruplin: Could not get columns from zen_sitespren table');
        return false;
    }
    
    // Build UPDATE statement to set all columns to NULL except primary key and auto-increment fields
    $update_fields = array();
    $protected_columns = array('id', 'wppma_id'); // Primary key and auto-increment columns to preserve - NEVER modify these
    
    foreach ($columns as $column) {
        // Skip protected columns - CRITICAL: Never modify id or auto-increment columns
        if (in_array($column->Field, $protected_columns)) {
            error_log("Ruplin: Protecting column '{$column->Field}' from modification during sitespren wipe");
            continue;
        }
        
        // Skip timestamp columns that auto-update
        if (strpos($column->Extra, 'on update') !== false) {
            continue;
        }
        
        // Set column to NULL or empty string based on whether it allows NULL
        if ($column->Null === 'YES') {
            $update_fields[] = "`{$column->Field}` = NULL";
        } else {
            // For non-nullable fields, set appropriate default based on type
            if (strpos($column->Type, 'int') !== false || 
                strpos($column->Type, 'decimal') !== false || 
                strpos($column->Type, 'float') !== false || 
                strpos($column->Type, 'double') !== false) {
                $update_fields[] = "`{$column->Field}` = 0";
            } elseif (strpos($column->Type, 'tinyint(1)') !== false) {
                $update_fields[] = "`{$column->Field}` = 0";
            } elseif (strpos($column->Type, 'datetime') !== false || 
                     strpos($column->Type, 'timestamp') !== false) {
                // Skip datetime fields that might have defaults
                if (strpos($column->Default, 'CURRENT_TIMESTAMP') === false) {
                    $update_fields[] = "`{$column->Field}` = NULL";
                }
            } else {
                // Text, varchar, and other string types
                $update_fields[] = "`{$column->Field}` = ''";
            }
        }
    }
    
    if (empty($update_fields)) {
        error_log('Ruplin: No fields to update in zen_sitespren table');
        return false;
    }
    
    // Execute the update
    $sql = "UPDATE $table_name SET " . implode(', ', $update_fields);
    
    $result = $wpdb->query($sql);
    
    if ($result === false) {
        error_log('Ruplin: Failed to wipe zen_sitespren values: ' . $wpdb->last_error);
        return false;
    }
    
    return true;
}