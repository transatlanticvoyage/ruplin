<?php
/**
 * Silkweaver Integration Class
 * Handles menu system logic for all headers
 * Transforms Silkweaver menu output for different header types
 * Test comment to show in VSCode source control
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Silkweaver_Integration {
    
    /**
     * Get menu HTML formatted for specific header type
     */
    public function get_menu_for_header($header_type = 'header2') {
        // Check if Silkweaver is available and enabled
        if (function_exists('silkweaver_render_menu') && get_option('silkweaver_use_system', true)) {
            // Return silkweaver output directly — no transformation.
            // Each header styles the shared silkweaver classes via its own scoped CSS.
            return silkweaver_render_menu();
        }

        // Fallback to WordPress menu with appropriate walker
        return $this->get_wordpress_menu($header_type);
    }
    
    /**
     * Transform Silkweaver menu HTML for specific header type
     */
    private function transform_menu_for_header($silkweaver_html, $header_type) {
        if (empty($silkweaver_html)) {
            return $this->get_fallback_menu($header_type);
        }
        
        // Parse the Silkweaver menu
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $silkweaver_html);
        
        // Get menu classes for header type
        $classes = $this->get_menu_classes($header_type);
        
        $output = '<ul class="' . $classes['menu'] . '">';
        
        $xpath = new DOMXPath($dom);
        $menu_items = $xpath->query('//ul[@class="silkweaver-menu"]/li');
        
        foreach ($menu_items as $item) {
            $link = $item->getElementsByTagName('a')->item(0);
            if ($link) {
                $href = $link->getAttribute('href');
                $text = $link->textContent;
                
                // Check for submenu
                $submenu = $xpath->query('.//ul', $item);
                $has_submenu = $submenu->length > 0;
                
                $item_class = $classes['menu_item'];
                if ($has_submenu) {
                    $item_class .= ' ' . $classes['has_dropdown'];
                }
                
                $output .= '<li class="' . $item_class . '">';
                $output .= '<a class="' . $classes['menu_link'] . '" href="' . esc_url($href) . '">';
                $output .= esc_html($text);
                $output .= '</a>';
                
                if ($has_submenu) {
                    $output .= $this->get_dropdown_icon($header_type);
                    $output .= $this->build_dropdown_menu($submenu, $classes);
                }
                
                $output .= '</li>';
            }
        }
        
        $output .= '</ul>';
        
        return $output;
    }
    
    /**
     * Get component prefix mapping for ZX isolation system
     */
    private function get_component_prefixes() {
        return array(
            'header1' => 'zx_hd1_',
            'header2' => 'zx_hd2_',
            'header3' => 'zx_hd3_',
            'footer1' => 'zx_ft1_',
            'footer2' => 'zx_ft2_',
            'footer3' => 'zx_ft3_',
            'sidebar1' => 'zx_sd1_',
            'sidebar2' => 'zx_sd2_',
            'sidebar3' => 'zx_sd3_',
            'anteheader1' => 'zx_anh1_',
            'anteheader2' => 'zx_anh2_',
            'anteheader3' => 'zx_anh3_'
        );
    }
    
    /**
     * Get CSS classes for different header types using ZX prefixes
     */
    private function get_menu_classes($header_type) {
        $prefixes = $this->get_component_prefixes();
        $prefix = isset($prefixes[$header_type]) ? $prefixes[$header_type] : 'zx_hd2_';
        
        return array(
            'menu' => $prefix . 'menu',
            'menu_item' => $prefix . 'menu_item',
            'menu_link' => $prefix . 'menu_link',
            'has_dropdown' => $prefix . 'has_dropdown',
            'dropdown' => $prefix . 'dropdown',
            'dropdown_icon' => $prefix . 'dropdown_icon'
        );
    }
    
    /**
     * Get dropdown icon HTML for header type
     */
    private function get_dropdown_icon($header_type) {
        $classes = $this->get_menu_classes($header_type);
        
        return '<button class="' . $classes['dropdown_icon'] . '" aria-haspopup="true" aria-expanded="false">' .
               '<svg width="12" height="8" viewBox="0 0 12 8" fill="currentColor">' .
               '<path d="M6 8L0 0h12L6 8z"/>' .
               '</svg>' .
               '</button>';
    }
    
    /**
     * Build dropdown menu HTML
     */
    private function build_dropdown_menu($submenu_nodes, $classes) {
        $output = '<div class="' . $classes['dropdown'] . '">';
        $output .= '<ul>';
        
        foreach ($submenu_nodes as $sub) {
            $sub_items = $sub->getElementsByTagName('li');
            foreach ($sub_items as $sub_item) {
                $sub_link = $sub_item->getElementsByTagName('a')->item(0);
                if ($sub_link) {
                    $output .= '<li>';
                    $output .= '<a href="' . esc_url($sub_link->getAttribute('href')) . '">';
                    $output .= esc_html($sub_link->textContent);
                    $output .= '</a>';
                    $output .= '</li>';
                }
            }
        }
        
        $output .= '</ul>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Get WordPress menu with appropriate walker
     */
    private function get_wordpress_menu($header_type) {
        $walker_class = $this->get_walker_class($header_type);
        $classes = $this->get_menu_classes($header_type);
        
        ob_start();
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_class' => $classes['menu'],
            'container' => false,
            'walker' => $walker_class ? new $walker_class($header_type) : null,
            'fallback_cb' => array($this, 'get_fallback_menu')
        ));
        return ob_get_clean();
    }
    
    /**
     * Get appropriate walker class for header type
     */
    private function get_walker_class($header_type) {
        $walkers = array(
            'header1' => 'Ruplin_Header1_Walker_Nav_Menu',
            'header2' => 'Ruplin_Header2_Walker_Nav_Menu', 
            'header3' => 'Ruplin_Header3_Walker_Nav_Menu'
        );
        
        $walker_class = isset($walkers[$header_type]) ? $walkers[$header_type] : null;
        
        // Check if walker class exists, if not create it dynamically
        if ($walker_class && !class_exists($walker_class)) {
            $this->create_walker_class($walker_class, $header_type);
        }
        
        return $walker_class;
    }
    
    /**
     * Dynamically create walker class for header type
     */
    private function create_walker_class($class_name, $header_type) {
        if (class_exists('Walker_Nav_Menu')) {
            $classes = $this->get_menu_classes($header_type);
            
            eval("
            class {$class_name} extends Walker_Nav_Menu {
                private \$header_type = '{$header_type}';
                private \$classes;
                
                public function __construct(\$header_type = '{$header_type}') {
                    \$this->header_type = \$header_type;
                    \$integration = new Ruplin_Silkweaver_Integration();
                    \$this->classes = \$integration->get_menu_classes(\$header_type);
                }
                
                function start_lvl(&\$output, \$depth = 0, \$args = null) {
                    if (\$depth === 0) {
                        \$output .= '<div class=\"' . \$classes['dropdown'] . '\"><ul>';
                    } else {
                        \$output .= '<ul>';
                    }
                }
                
                function end_lvl(&\$output, \$depth = 0, \$args = null) {
                    \$output .= '</ul>';
                    if (\$depth === 0) {
                        \$output .= '</div>';
                    }
                }
                
                function start_el(&\$output, \$item, \$depth = 0, \$args = null, \$id = 0) {
                    \$has_children = in_array('menu-item-has-children', \$item->classes);
                    
                    if (\$depth === 0) {
                        \$item_class = \$this->classes['menu_item'];
                        if (\$has_children) {
                            \$item_class .= ' ' . \$this->classes['has_dropdown'];
                        }
                        
                        \$output .= '<li class=\"' . \$item_class . '\">';
                        \$output .= '<a class=\"' . \$this->classes['menu_link'] . '\" href=\"' . esc_url(\$item->url) . '\">';
                        \$output .= esc_html(\$item->title);
                        \$output .= '</a>';
                        
                        if (\$has_children) {
                            \$integration = new Ruplin_Silkweaver_Integration();
                            \$output .= \$integration->get_dropdown_icon(\$this->header_type);
                        }
                    } else {
                        \$output .= '<li>';
                        \$output .= '<a href=\"' . esc_url(\$item->url) . '\">' . esc_html(\$item->title) . '</a>';
                    }
                }
                
                function end_el(&\$output, \$item, \$depth = 0, \$args = null) {
                    \$output .= '</li>';
                }
            }
            ");
        }
    }
    
    /**
     * Get fallback menu for header type
     */
    public function get_fallback_menu($header_type = 'header2') {
        $classes = $this->get_menu_classes($header_type);
        
        return '<ul class="' . $classes['menu'] . '">' .
               '<li class="' . $classes['menu_item'] . '">' .
               '<a class="' . $classes['menu_link'] . '" href="' . esc_url(home_url('/')) . '">Home</a>' .
               '</li>' .
               '</ul>';
    }
    
    /**
     * Clear menu cache
     */
    public function clear_menu_cache() {
        if (function_exists('silkweaver_clear_menu_cache')) {
            silkweaver_clear_menu_cache();
        }
    }
    
    /**
     * Check if Silkweaver is available
     */
    public function is_silkweaver_available() {
        return function_exists('silkweaver_render_menu') && get_option('silkweaver_use_system', true);
    }
}