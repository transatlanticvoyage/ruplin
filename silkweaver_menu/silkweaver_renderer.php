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
            return '<nav class="silkweaver-nav" aria-label="Main navigation"><ul class="silkweaver-menu" role="list"><li><a href="/">Home</a></li></ul></nav>';
        }

        $menu_items = $this->parse_config($config);

        // Detect current page URL for aria-current
        $current_path = trailingslashit(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

        // Debug: Add comment to show parsed items count
        $html = '<!-- Silkweaver: Parsed ' . count($menu_items) . ' menu items -->';
        $html .= '<nav class="silkweaver-nav" aria-label="Main navigation">';
        $html .= '<ul class="silkweaver-menu" role="list">';

        foreach ($menu_items as $item) {
            if ($item['type'] === 'static') {
                $item_path = trailingslashit(parse_url($item['url'], PHP_URL_PATH));
                $is_current = ($current_path === $item_path);
                $html .= sprintf(
                    '<li><a href="%s"%s>%s</a></li>',
                    esc_url($item['url']),
                    $is_current ? ' aria-current="page"' : '',
                    esc_html($item['anchor'])
                );
            } elseif ($item['type'] === 'dynamic') {
                $html .= $this->render_dynamic_menu($item);
            } elseif ($item['type'] === 'dynamic_robust_services') {
                $html .= $this->render_robust_services_menu($item);
            } elseif ($item['type'] === 'dynamic_robust_locations') {
                $html .= $this->render_robust_locations_menu($item);
            } elseif ($item['type'] === 'dynamic_elegant_services') {
                $html .= $this->render_elegant_services_menu($item);
            } elseif ($item['type'] === 'dynamic_elegant_locations') {
                $html .= $this->render_elegant_locations_menu($item);
            }
        }

        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Parse configuration text into menu items array
     */
    private function parse_config($config) {
        $lines = explode("\n", $config);
        $menu_items = array();
        
        // Get sitespren data for dynamic placeholders
        global $wpdb;
        $sitespren_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}zen_sitespren LIMIT 1");
        
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
            } elseif (strpos($line, 'pull_all_service_pages_dynamically_with_robust_child_area') === 0) {
                $menu_items[] = array(
                    'type'  => 'dynamic_robust_services',
                    'title' => 'Services',
                );
            } elseif (strpos($line, 'pull_all_location_pages_dynamically_with_robust_child_area') === 0) {
                $menu_items[] = array(
                    'type'  => 'dynamic_robust_locations',
                    'title' => 'Areas We Serve',
                );
            } elseif (strpos($line, 'pull_all_service_pages_dynamically_with_elegant_child_area') === 0) {
                $menu_items[] = array(
                    'type'  => 'dynamic_elegant_services',
                    'title' => 'Services',
                );
            } elseif (strpos($line, 'pull_all_location_pages_dynamically_with_elegant_child_area') === 0) {
                $menu_items[] = array(
                    'type'  => 'dynamic_elegant_locations',
                    'title' => 'Areas We Serve',
                );
            } elseif (strpos($line, 'pull_all_service_pages_dynamically') === 0) {
                // Parse custom_raw_link and custom_raw_link_pinned if present
                $custom_links = array();
                $pinned_links = array();
                
                // Check for pinned link first
                if (preg_match('/custom_raw_link_pinned=([^\s]+)\s+(.+)/', $line, $matches)) {
                    $anchor_text = $matches[2];
                    $original_anchor = $anchor_text;
                    
                    // Replace placeholders with database values if they exist
                    if ($anchor_text === '{home_anchor_for_silkweaver_services}' && 
                        $sitespren_data && !empty($sitespren_data->home_anchor_for_silkweaver_services)) {
                        $anchor_text = $sitespren_data->home_anchor_for_silkweaver_services;
                    } elseif ($anchor_text === '{driggs_city}' && 
                        $sitespren_data && !empty($sitespren_data->driggs_city)) {
                        $anchor_text = $sitespren_data->driggs_city;
                    }
                    
                    // Only add the link if it's not a placeholder (doesn't start with { and end with })
                    // This skips empty/null database values that would show as {placeholder}
                    if (!(substr($anchor_text, 0, 1) === '{' && substr($anchor_text, -1) === '}')) {
                        $pinned_links[] = array(
                            'url' => $matches[1],
                            'anchor' => $anchor_text
                        );
                        error_log("Silkweaver parsed pinned service link: " . $matches[1] . " -> " . $anchor_text);
                    } else {
                        error_log("Silkweaver skipped pinned service link with empty placeholder: " . $original_anchor);
                    }
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
                    $anchor_text = $matches[2];
                    $original_anchor = $anchor_text;
                    
                    // Replace placeholders with database values if they exist
                    if ($anchor_text === '{home_anchor_for_silkweaver_locations}' && 
                        $sitespren_data && !empty($sitespren_data->home_anchor_for_silkweaver_locations)) {
                        $anchor_text = $sitespren_data->home_anchor_for_silkweaver_locations;
                    } elseif ($anchor_text === '{driggs_city}' && 
                        $sitespren_data && !empty($sitespren_data->driggs_city)) {
                        $anchor_text = $sitespren_data->driggs_city;
                    }
                    
                    // Only add the link if it's not a placeholder (doesn't start with { and end with })
                    // This skips empty/null database values that would show as {placeholder}
                    if (!(substr($anchor_text, 0, 1) === '{' && substr($anchor_text, -1) === '}')) {
                        $pinned_links[] = array(
                            'url' => $matches[1],
                            'anchor' => $anchor_text
                        );
                        error_log("Silkweaver parsed pinned location link: " . $matches[1] . " -> " . $anchor_text);
                    } else {
                        error_log("Silkweaver skipped pinned location link with empty placeholder: " . $original_anchor);
                    }
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
                    'title' => 'Areas We Serve',
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
            SELECT p.ID, p.post_title, py.short_anchor, py.pylon_archetype, py.exempt_from_silkweaver_menu_dynamical
            FROM {$wpdb->posts} p 
            INNER JOIN {$wpdb->prefix}pylons py ON p.ID = py.rel_wp_post_id 
            WHERE py.pylon_archetype = %s 
            AND p.post_status = 'publish'
            AND (py.exempt_from_silkweaver_menu_dynamical IS NULL OR py.exempt_from_silkweaver_menu_dynamical != 1)
            ORDER BY CASE WHEN py.short_anchor IS NULL OR py.short_anchor = '' THEN p.post_title ELSE py.short_anchor END ASC
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
            $title = !empty($post->short_anchor) ? $post->short_anchor : $post->post_title;
            
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
            return sprintf('<li><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false">%s</button></li>', esc_html($item['title']));
        }

        $dropdown_id = 'sw-dropdown-' . sanitize_html_class($item['archetype']);
        $html = sprintf(
            '<li class="silkweaver-dropdown"><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false" aria-controls="%s">%s</button>',
            esc_attr($dropdown_id),
            esc_html($item['title'])
        );
        $html .= sprintf('<ul id="%s" class="silkweaver-dropdown-menu">', esc_attr($dropdown_id));
        
        // Generate all links without any whitespace between elements
        $link_html = '';
        foreach ($all_links as $link) {
            // Skip empty or invalid links
            if (empty($link['url']) || empty($link['title'])) {
                continue;
            }
            $link_html .= sprintf(
                '<li><a href="%s">%s</a></li>',
                esc_url($link['url']),
                esc_html($link['title'])
            );
        }
        $html .= $link_html;
        
        $html .= '</ul></li>';
        
        return $html;
    }
    
    
    /**
     * Render robust services dropdown with category tiles and child page listings
     */
    private function render_robust_services_menu($item) {
        global $wpdb;

        // Check if is_active column exists before filtering on it
        $sc_table = $wpdb->prefix . 'service_categories';
        $has_is_active = $wpdb->get_var("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$sc_table' AND COLUMN_NAME = 'is_active'");

        if ($has_is_active) {
            $categories = $wpdb->get_results("SELECT * FROM $sc_table WHERE is_active = 1 ORDER BY category_name ASC");
        } else {
            $categories = $wpdb->get_results("SELECT * FROM $sc_table ORDER BY category_name ASC");
        }

        if (empty($categories)) {
            return sprintf(
                '<li><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false">%s</button></li>',
                esc_html($item['title'])
            );
        }

        $panel_id = 'sw-robust-services-panel';
        $html  = sprintf(
            '<li class="silkweaver-dropdown silkweaver-robust-dropdown silkweaver-robust-services-dropdown"><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false" aria-controls="%s" aria-haspopup="true">%s</button>',
            esc_attr($panel_id),
            esc_html($item['title'])
        );
        $html .= sprintf(
            '<div id="%s" class="silkweaver-robust-child-area" role="region" aria-label="%s submenu">',
            esc_attr($panel_id),
            esc_attr($item['title'])
        );
        $html .= '<div class="silkweaver-robust-tiles-wrapper">';

        foreach ($categories as $category) {
            // Get published child pages for this category via wp_pylons
            $child_pages = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, p.post_title, py.moniker
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->prefix}pylons py ON p.ID = py.rel_wp_post_id
                WHERE py.rel_service_category_id = %d
                AND p.post_status = 'publish'
                ORDER BY CASE WHEN py.moniker IS NULL OR py.moniker = '' THEN p.post_title ELSE py.moniker END ASC
            ", $category->category_id));

            $tile_label_id = 'sw-tile-cat-' . intval($category->category_id);
            $html .= sprintf('<div class="silkweaver-robust-tile" role="group" aria-labelledby="%s">', esc_attr($tile_label_id));

            // Fixed-size image thumbnail area — always rendered regardless of image presence
            $img_url = '';
            if (!empty($category->rel_featured_image_id)) {
                $img_url = wp_get_attachment_image_url($category->rel_featured_image_id, 'medium');
            }
            $html .= '<div class="silkweaver-robust-tile-image" aria-hidden="true">';
            if ($img_url) {
                $html .= sprintf(
                    '<img src="%s" alt="" loading="lazy">',
                    esc_url($img_url)
                );
            }
            $html .= '</div>';

            $html .= '<div class="silkweaver-robust-tile-body">';
            $html .= sprintf('<strong id="%s" class="silkweaver-robust-tile-name">%s</strong>', esc_attr($tile_label_id), esc_html($category->category_name));

            // Child pages list — always visible, no interactive show/hide
            if (!empty($child_pages)) {
                $bullet_on = (get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_bullet_yes_no', 'no') === 'yes');
                $ul_class  = 'silkweaver-robust-child-pages' . ($bullet_on ? ' silkweaver-robust-child-pages--has-bullet' : '');
                $html .= sprintf('<ul class="%s" role="list">', esc_attr($ul_class));
                foreach ($child_pages as $page) {
                    $anchor = (!empty($page->moniker)) ? $page->moniker : $page->post_title;
                    $html  .= '<li>';
                    $html  .= sprintf('<a href="%s">', esc_url(get_permalink($page->ID)));
                    $html  .= '<div class="silkweaver-robust-moniker-row">';
                    $html  .= '<span class="silkweaver-robust-moniker-bullet" aria-hidden="true"></span>';
                    $html  .= esc_html($anchor);
                    $html  .= '</div>';
                    $html  .= '</a>';
                    $html  .= '</li>';
                }
                $html .= '</ul>';
            }

            $html .= '</div>'; // .silkweaver-robust-tile-body
            $html .= '</div>'; // .silkweaver-robust-tile
        }

        $html .= '</div>'; // .silkweaver-robust-tiles-wrapper
        $html .= '</div>'; // .silkweaver-robust-child-area
        $html .= '</li>';

        return $html;
    }

    /**
     * Render robust locations dropdown — single tile containing all published location pages
     */
    private function render_robust_locations_menu($item) {
        global $wpdb;

        // Get all published location pages via wp_pylons
        $location_pages = $wpdb->get_results("
            SELECT p.ID, p.post_title, py.locpage_neighborhood, py.locpage_city
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}pylons py ON p.ID = py.rel_wp_post_id
            WHERE py.pylon_archetype = 'locationpage'
            AND p.post_status = 'publish'
            ORDER BY
                CASE
                    WHEN py.locpage_neighborhood IS NOT NULL AND py.locpage_neighborhood != '' THEN py.locpage_neighborhood
                    WHEN py.locpage_city IS NOT NULL AND py.locpage_city != '' THEN py.locpage_city
                    ELSE p.post_title
                END ASC
        ");

        if (empty($location_pages)) {
            return sprintf(
                '<li><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false">%s</button></li>',
                esc_html($item['title'])
            );
        }

        $panel_id     = 'sw-robust-locations-panel';
        $tile_label_id = 'sw-tile-locations-label';
        $html  = sprintf(
            '<li class="silkweaver-dropdown silkweaver-robust-dropdown silkweaver-robust-locations-dropdown"><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false" aria-controls="%s" aria-haspopup="true">%s</button>',
            esc_attr($panel_id),
            esc_html($item['title'])
        );
        $html .= sprintf(
            '<div id="%s" class="silkweaver-robust-child-area" role="region" aria-label="%s submenu">',
            esc_attr($panel_id),
            esc_attr($item['title'])
        );
        $html .= '<div class="silkweaver-robust-tiles-wrapper">';

        // Single tile containing all location pages
        $html .= sprintf('<div class="silkweaver-robust-tile" role="group" aria-labelledby="%s">', esc_attr($tile_label_id));

        // Image area — uses the featured image ID stored in wp_options if set
        $featured_image_id = Ruplin_Silkweaver_Robust_Locations_Child_Area_Settings_Admin::get_main_featured_image_id();
        if ($featured_image_id > 0) {
            $img_url = wp_get_attachment_image_url($featured_image_id, 'medium');
            if ($img_url) {
                $html .= '<div class="silkweaver-robust-tile-image">';
                $html .= sprintf('<img src="%s" alt="" style="width:100%%;height:100%%;object-fit:cover;">', esc_url($img_url));
                $html .= '</div>';
            } else {
                $html .= '<div class="silkweaver-robust-tile-image" aria-hidden="true"></div>';
            }
        } else {
            $html .= '<div class="silkweaver-robust-tile-image" aria-hidden="true"></div>';
        }

        // Custom HTML snippet 1 — injected between image and tile-body when activated
        if (Ruplin_Silkweaver_Robust_Locations_Child_Area_Settings_Admin::is_snippet_1_active()) {
            $snippet_content = Ruplin_Silkweaver_Robust_Locations_Child_Area_Settings_Admin::get_snippet_1_content();
            $html .= '<div class="silkweaver-robust-locations-custom-html-snippet-1">' . $snippet_content . '</div>';
        }

        $html .= '<div class="silkweaver-robust-tile-body">';
        $html .= sprintf('<strong id="%s" class="silkweaver-robust-tile-name">Areas We Service:</strong>', esc_attr($tile_label_id));

        // Location pages list
        $html .= '<ul class="silkweaver-robust-child-pages" role="list">';
        foreach ($location_pages as $page) {
            // Anchor text: locpage_neighborhood → locpage_city → post_title
            if (!empty($page->locpage_neighborhood)) {
                $anchor = $page->locpage_neighborhood;
            } elseif (!empty($page->locpage_city)) {
                $anchor = $page->locpage_city;
            } else {
                $anchor = $page->post_title;
            }

            $html .= '<li>';
            $html .= sprintf('<a href="%s">', esc_url(get_permalink($page->ID)));
            $html .= '<div class="silkweaver-robust-moniker-row">';
            $html .= '<span class="silkweaver-robust-moniker-bullet" aria-hidden="true"></span>';
            $html .= esc_html($anchor);
            $html .= '</div>';
            $html .= '</a>';
            $html .= '</li>';
        }
        $html .= '</ul>';

        $html .= '</div>'; // .silkweaver-robust-tile-body
        $html .= '</div>'; // .silkweaver-robust-tile

        $html .= '</div>'; // .silkweaver-robust-tiles-wrapper
        $html .= '</div>'; // .silkweaver-robust-child-area
        $html .= '</li>';

        return $html;
    }

    /**
     * Render elegant services dropdown — typography-forward panel, no imagery,
     * auto-fit category columns, includes category description text
     */
    private function render_elegant_services_menu($item) {
        global $wpdb;

        $sc_table = $wpdb->prefix . 'service_categories';
        $has_is_active = $wpdb->get_var("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$sc_table' AND COLUMN_NAME = 'is_active'");

        if ($has_is_active) {
            $categories = $wpdb->get_results("SELECT * FROM $sc_table WHERE is_active = 1 ORDER BY category_name ASC");
        } else {
            $categories = $wpdb->get_results("SELECT * FROM $sc_table ORDER BY category_name ASC");
        }

        if (empty($categories)) {
            return sprintf(
                '<li><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false">%s</button></li>',
                esc_html($item['title'])
            );
        }

        $panel_id = 'sw-elegant-services-panel';
        $html  = sprintf(
            '<li class="silkweaver-dropdown silkweaver-elegant-dropdown silkweaver-elegant-services-dropdown"><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false" aria-controls="%s" aria-haspopup="true">%s</button>',
            esc_attr($panel_id),
            esc_html($item['title'])
        );
        $html .= sprintf(
            '<div id="%s" class="silkweaver-elegant-child-area" role="region" aria-label="%s submenu">',
            esc_attr($panel_id),
            esc_attr($item['title'])
        );
        $html .= '<div class="silkweaver-elegant-inner">';

        foreach ($categories as $category) {
            $child_pages = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, p.post_title, py.moniker
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->prefix}pylons py ON p.ID = py.rel_wp_post_id
                WHERE py.rel_service_category_id = %d
                AND p.post_status = 'publish'
                ORDER BY CASE WHEN py.moniker IS NULL OR py.moniker = '' THEN p.post_title ELSE py.moniker END ASC
            ", $category->category_id));

            $column_label_id = 'sw-elegant-cat-' . intval($category->category_id);
            $html .= sprintf('<div class="silkweaver-elegant-column" role="group" aria-labelledby="%s">', esc_attr($column_label_id));
            $html .= sprintf('<h3 id="%s" class="silkweaver-elegant-category-title">%s</h3>', esc_attr($column_label_id), esc_html($category->category_name));

            if (!empty($category->category_description)) {
                $html .= sprintf('<p class="silkweaver-elegant-category-desc">%s</p>', esc_html($category->category_description));
            }

            if (!empty($child_pages)) {
                $html .= '<ul class="silkweaver-elegant-child-pages" role="list">';
                foreach ($child_pages as $page) {
                    $anchor = !empty($page->moniker) ? $page->moniker : $page->post_title;
                    $html .= sprintf(
                        '<li><a href="%s">%s</a></li>',
                        esc_url(get_permalink($page->ID)),
                        esc_html($anchor)
                    );
                }
                $html .= '</ul>';
            }

            $html .= '</div>'; // .silkweaver-elegant-column
        }

        $html .= '</div>'; // .silkweaver-elegant-inner
        $html .= '</div>'; // .silkweaver-elegant-child-area
        $html .= '</li>';

        return $html;
    }

    /**
     * Render elegant locations dropdown — dense multi-column link list, no imagery
     */
    private function render_elegant_locations_menu($item) {
        global $wpdb;

        $location_pages = $wpdb->get_results("
            SELECT p.ID, p.post_title, py.locpage_neighborhood, py.locpage_city
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}pylons py ON p.ID = py.rel_wp_post_id
            WHERE py.pylon_archetype = 'locationpage'
            AND p.post_status = 'publish'
            ORDER BY
                CASE
                    WHEN py.locpage_neighborhood IS NOT NULL AND py.locpage_neighborhood != '' THEN py.locpage_neighborhood
                    WHEN py.locpage_city IS NOT NULL AND py.locpage_city != '' THEN py.locpage_city
                    ELSE p.post_title
                END ASC
        ");

        if (empty($location_pages)) {
            return sprintf(
                '<li><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false">%s</button></li>',
                esc_html($item['title'])
            );
        }

        $panel_id       = 'sw-elegant-locations-panel';
        $heading_id     = 'sw-elegant-locations-label';
        $html  = sprintf(
            '<li class="silkweaver-dropdown silkweaver-elegant-dropdown silkweaver-elegant-locations-dropdown"><button type="button" class="silkweaver-dropdown-toggle silkweaver-parent-button" aria-expanded="false" aria-controls="%s" aria-haspopup="true">%s</button>',
            esc_attr($panel_id),
            esc_html($item['title'])
        );
        $html .= sprintf(
            '<div id="%s" class="silkweaver-elegant-child-area" role="region" aria-label="%s submenu">',
            esc_attr($panel_id),
            esc_attr($item['title'])
        );
        $html .= '<div class="silkweaver-elegant-inner silkweaver-elegant-inner--locations">';
        $html .= sprintf('<h3 id="%s" class="silkweaver-elegant-section-title">Areas We Service</h3>', esc_attr($heading_id));
        $html .= sprintf('<ul class="silkweaver-elegant-child-pages silkweaver-elegant-locations-list" role="list" aria-labelledby="%s">', esc_attr($heading_id));

        foreach ($location_pages as $page) {
            if (!empty($page->locpage_neighborhood)) {
                $anchor = $page->locpage_neighborhood;
            } elseif (!empty($page->locpage_city)) {
                $anchor = $page->locpage_city;
            } else {
                $anchor = $page->post_title;
            }
            $html .= sprintf(
                '<li><a href="%s">%s</a></li>',
                esc_url(get_permalink($page->ID)),
                esc_html($anchor)
            );
        }

        $html .= '</ul>';
        $html .= '</div>'; // .silkweaver-elegant-inner
        $html .= '</div>'; // .silkweaver-elegant-child-area
        $html .= '</li>';

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