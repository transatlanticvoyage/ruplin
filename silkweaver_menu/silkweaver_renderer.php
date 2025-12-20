<?php
/**
 * Silkweaver Menu Renderer - Generates HTML menus from configuration
 * 
 * @package Ruplin
 * @subpackage SilkweaverMenu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Silkweaver_Menu_Renderer {
    
    private $cache_key = 'silkweaver_menu_cache';
    private $cache_duration = 24 * HOUR_IN_SECONDS; // 24 hours
    
    /**
     * Main function to render the silkweaver menu
     */
    public function render_menu() {
        // Check if silkweaver system is enabled (either/or option selector)
        if (!get_option('silkweaver_use_system', true)) {
            return ''; // Return empty string - let theme handle native WP menu
        }
        
        // Clear cache for debugging - remove this later
        delete_transient($this->cache_key);
        error_log("Silkweaver cache cleared");
        
        // Try to get cached menu first
        $cached_menu = get_transient($this->cache_key);
        if ($cached_menu !== false) {
            return $cached_menu;
        }
        
        // Generate fresh menu
        $menu_html = $this->generate_menu();
        
        // Cache the result
        set_transient($this->cache_key, $menu_html, $this->cache_duration);
        
        return $menu_html;
    }
    
    /**
     * Generate the menu HTML from configuration
     */
    private function generate_menu() {
        $config = get_option('silkweaver_menu_config', '');
        
        if (empty($config)) {
            return '<ul class="silkweaver-menu"><li><a href="/">Home</a></li></ul>';
        }
        
        $menu_items = $this->parse_config($config);
        
        // Debug: Add comment to show parsed items count
        $html = '<!-- Silkweaver: Parsed ' . count($menu_items) . ' menu items -->';
        $html .= '<ul class="silkweaver-menu">';
        
        foreach ($menu_items as $item) {
            if ($item['type'] === 'static') {
                $html .= sprintf(
                    '<li><a href="%s">%s</a></li>',
                    esc_url($item['url']),
                    esc_html($item['anchor'])
                );
            } elseif ($item['type'] === 'dynamic') {
                $html .= $this->render_dynamic_menu($item);
            }
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Parse configuration text into menu items array
     */
    private function parse_config($config) {
        $lines = explode("\n", $config);
        $menu_items = array();
        
        foreach ($lines as $line_num => $line) {
            $original_line = $line;
            $line = trim($line);
            if (empty($line)) continue;
            
            // Debug: Log each line processing
            error_log("Silkweaver parsing line " . ($line_num + 1) . ": '" . $original_line . "' -> '" . $line . "'");
            
            if (strpos($line, 'target_url=') === 0) {
                // Static menu item: target_url=/path anchor=Link Text
                preg_match('/target_url=([^\s]+)\s+anchor=(.+)/', $line, $matches);
                error_log("Silkweaver static line regex matches: " . json_encode($matches));
                if (count($matches) === 3) {
                    $menu_items[] = array(
                        'type' => 'static',
                        'url' => $matches[1],
                        'anchor' => $matches[2]
                    );
                    error_log("Silkweaver added static item: " . $matches[1] . " -> " . $matches[2]);
                }
            } elseif (strpos($line, 'pull_all_service_pages_dynamically') === 0) {
                // Parse custom_raw_link and custom_raw_link_pinned if present
                $custom_links = array();
                $pinned_links = array();
                
                // Check for pinned link first
                if (preg_match('/custom_raw_link_pinned=([^\s]+)\s+(.+)/', $line, $matches)) {
                    $pinned_links[] = array(
                        'url' => $matches[1],
                        'anchor' => $matches[2]
                    );
                    error_log("Silkweaver parsed pinned service link: " . $matches[1] . " -> " . $matches[2]);
                } elseif (preg_match('/custom_raw_link=([^\s]+)\s+(.+)/', $line, $matches)) {
                    $custom_links[] = array(
                        'url' => $matches[1],
                        'anchor' => $matches[2]
                    );
                    error_log("Silkweaver parsed custom service link: " . $matches[1] . " -> " . $matches[2]);
                }
                
                $menu_items[] = array(
                    'type' => 'dynamic',
                    'archetype' => 'servicepage',
                    'title' => 'Services',
                    'custom_links' => $custom_links,
                    'pinned_links' => $pinned_links
                );
                error_log("Silkweaver added services dynamic item with " . count($custom_links) . " custom links and " . count($pinned_links) . " pinned links");
            } elseif (strpos($line, 'pull_all_location_pages_dynamically') === 0) {
                // Parse custom_raw_link and custom_raw_link_pinned if present
                $custom_links = array();
                $pinned_links = array();
                
                // Check for pinned link first
                if (preg_match('/custom_raw_link_pinned=([^\s]+)\s+(.+)/', $line, $matches)) {
                    $pinned_links[] = array(
                        'url' => $matches[1],
                        'anchor' => $matches[2]
                    );
                    error_log("Silkweaver parsed pinned location link: " . $matches[1] . " -> " . $matches[2]);
                } elseif (preg_match('/custom_raw_link=([^\s]+)\s+(.+)/', $line, $matches)) {
                    $custom_links[] = array(
                        'url' => $matches[1],
                        'anchor' => $matches[2]
                    );
                    error_log("Silkweaver parsed custom location link: " . $matches[1] . " -> " . $matches[2]);
                }
                
                $menu_items[] = array(
                    'type' => 'dynamic',
                    'archetype' => 'locationpage', 
                    'title' => 'Locations',
                    'custom_links' => $custom_links,
                    'pinned_links' => $pinned_links
                );
                error_log("Silkweaver added locations dynamic item with " . count($custom_links) . " custom links and " . count($pinned_links) . " pinned links");
            }
        }
        
        error_log("Silkweaver final menu_items: " . json_encode($menu_items));
        return $menu_items;
    }
    
    /**
     * Render dynamic menu dropdown
     */
    private function render_dynamic_menu($item) {
        global $wpdb;
        
        // Debug: Log the query details
        error_log("Silkweaver dynamic menu query for archetype: " . $item['archetype']);
        error_log("Silkweaver looking for posts with: pylon_archetype = '" . $item['archetype'] . "' AND post_status = 'publish' AND exempt != 1");
        
        // First, let's check what pylons exist with this archetype
        $debug_query = $wpdb->prepare("
            SELECT py.*, p.post_title, p.post_status
            FROM {$wpdb->prefix}pylons py 
            LEFT JOIN {$wpdb->posts} p ON p.ID = py.rel_wp_post_id 
            WHERE py.pylon_archetype = %s
        ", $item['archetype']);
        
        $debug_results = $wpdb->get_results($debug_query, ARRAY_A);
        error_log("Silkweaver debug query results: " . json_encode($debug_results));
        
        // Optimized JOIN query instead of reverse lookup
        $posts = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, py.moniker, py.pylon_archetype, py.exempt_from_silkweaver_menu_dynamical
            FROM {$wpdb->posts} p 
            INNER JOIN {$wpdb->prefix}pylons py ON p.ID = py.rel_wp_post_id 
            WHERE py.pylon_archetype = %s 
            AND p.post_status = 'publish'
            AND (py.exempt_from_silkweaver_menu_dynamical IS NULL OR py.exempt_from_silkweaver_menu_dynamical != 1)
            ORDER BY CASE WHEN py.moniker IS NULL OR py.moniker = '' THEN p.post_title ELSE py.moniker END ASC
        ", $item['archetype']));
        
        error_log("Silkweaver final query results: " . json_encode($posts));
        
        // Create arrays for different link types
        $pinned_links = array();
        $sortable_links = array();
        
        // Add pinned links first (these will appear at the top)
        if (!empty($item['pinned_links'])) {
            foreach ($item['pinned_links'] as $pinned_link) {
                $pinned_links[] = array(
                    'url' => $pinned_link['url'],
                    'title' => $pinned_link['anchor'],
                    'type' => 'pinned'
                );
            }
            error_log("Silkweaver added " . count($item['pinned_links']) . " pinned links to dropdown");
        }
        
        // Add database posts to sortable links
        foreach ($posts as $post) {
            $url = get_permalink($post->ID);
            $title = !empty($post->moniker) ? $post->moniker : $post->post_title;
            
            $sortable_links[] = array(
                'url' => $url,
                'title' => $title,
                'type' => 'post'
            );
        }
        
        // Add custom raw links to sortable links
        if (!empty($item['custom_links'])) {
            foreach ($item['custom_links'] as $custom_link) {
                $sortable_links[] = array(
                    'url' => $custom_link['url'],
                    'title' => $custom_link['anchor'],
                    'type' => 'custom'
                );
            }
            error_log("Silkweaver added " . count($item['custom_links']) . " custom links to dropdown");
        }
        
        // Sort only the sortable links alphabetically by title
        usort($sortable_links, function($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
        
        // Combine pinned links (first) + sorted links
        $all_links = array_merge($pinned_links, $sortable_links);
        
        if (empty($all_links)) {
            return sprintf('<li><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button">%s</button></li>', esc_html($item['title']));
        }
        
        $html = sprintf('<li class="silkweaver-dropdown"><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button">%s</button>', esc_html($item['title']));
        $html .= '<ul class="silkweaver-dropdown-menu">';
        
        foreach ($all_links as $link) {
            $html .= sprintf(
                '<li><a href="%s">%s</a></li>',
                esc_url($link['url']),
                esc_html($link['title'])
            );
        }
        
        $html .= '</ul></li>';
        
        return $html;
    }
    
    
    /**
     * Clear menu cache (call when posts/pylons are updated)
     */
    public function clear_cache() {
        delete_transient($this->cache_key);
    }
}

/**
 * Global function to render silkweaver menu
 */
function silkweaver_render_menu() {
    $renderer = new Silkweaver_Menu_Renderer();
    return $renderer->render_menu();
}

/**
 * Clear cache when posts or pylons are modified
 */
function silkweaver_clear_menu_cache() {
    $renderer = new Silkweaver_Menu_Renderer();
    $renderer->clear_cache();
}

// Hook into post save/delete to clear cache
add_action('save_post', 'silkweaver_clear_menu_cache');
add_action('delete_post', 'silkweaver_clear_menu_cache');
add_action('wp_trash_post', 'silkweaver_clear_menu_cache');
add_action('untrash_post', 'silkweaver_clear_menu_cache');