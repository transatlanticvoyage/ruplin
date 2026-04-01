<?php
/**
 * Mobile Menu Handler Class
 * Handles mobile menu behavior and JavaScript functionality for all headers
 * Test comment to show in VSCode source control
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Mobile_Menu_Handler {
    
    private static $instance = null;
    private $header_type;
    
    /**
     * Singleton pattern
     */
    public static function getInstance($header_type = 'header2') {
        if (self::$instance === null) {
            self::$instance = new self($header_type);
        }
        return self::$instance;
    }
    
    private function __construct($header_type = 'header2') {
        $this->header_type = $header_type;
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_mobile_menu_scripts'));
        add_action('wp_footer', array($this, 'output_mobile_menu_script'), 99);
    }
    
    /**
     * Enqueue mobile menu scripts
     */
    public function enqueue_mobile_menu_scripts() {
        // Ensure jQuery is loaded
        wp_enqueue_script('jquery');
        
        // Add inline mobile menu script
        wp_add_inline_script('jquery', $this->get_mobile_menu_js(), 'after');
    }
    
    /**
     * Get mobile menu JavaScript based on header type
     */
    private function get_mobile_menu_js() {
        $classes = $this->get_mobile_menu_classes();
        
        return "
        jQuery(document).ready(function($) {
            // Mobile menu toggle
            $('{$classes['toggle']}').on('click', function(e) {
                e.preventDefault();
                $(this).toggleClass('active');
                $('{$classes['wrapper']}').toggleClass('active');
                $('body').toggleClass('mobile-menu-open');
            });
            
            // Close mobile menu when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('{$classes['toggle']}, {$classes['wrapper']}').length) {
                    $('{$classes['toggle']}').removeClass('active');
                    $('{$classes['wrapper']}').removeClass('active');
                    $('body').removeClass('mobile-menu-open');
                }
            });
            
            // Dropdown menu functionality for mobile
            $('{$classes['dropdown_icon']}').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var menuItem = $(this).closest('{$classes['has_dropdown']}');
                var dropdown = menuItem.find('{$classes['dropdown']}').first();
                
                if ($(window).width() <= 1024) {
                    menuItem.toggleClass('active');
                    dropdown.slideToggle(300);
                }
            });
            
            // Handle window resize
            var resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    // Reset mobile menu on resize to desktop
                    if ($(window).width() > 1024) {
                        $('{$classes['toggle']}').removeClass('active');
                        $('{$classes['wrapper']}').removeClass('active');
                        $('body').removeClass('mobile-menu-open');
                        $('{$classes['has_dropdown']}').removeClass('active');
                        $('{$classes['dropdown']}').removeAttr('style');
                    }
                }, 250);
            });
            
            // Escape key to close mobile menu
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('{$classes['toggle']}').removeClass('active');
                    $('{$classes['wrapper']}').removeClass('active');
                    $('body').removeClass('mobile-menu-open');
                }
            });
            
            // Accessibility: Enter key support for mobile toggle
            $('{$classes['toggle']}').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                }
            });
            
            // Update ARIA attributes
            $('{$classes['toggle']}').on('click', function() {
                var expanded = $(this).hasClass('active');
                $(this).attr('aria-expanded', expanded);
            });
        });
        ";
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
     * Get CSS classes for mobile menu based on header type using ZX prefixes
     */
    private function get_mobile_menu_classes() {
        $prefixes = $this->get_component_prefixes();
        $prefix = isset($prefixes[$this->header_type]) ? $prefixes[$this->header_type] : 'zx_hd2_';
        
        return array(
            'toggle' => '.' . $prefix . 'mobile_toggle',
            'wrapper' => '.' . $prefix . 'menu_wrapper',
            'dropdown_icon' => '.' . $prefix . 'dropdown_icon',
            'has_dropdown' => '.' . $prefix . 'has_dropdown',
            'dropdown' => '.' . $prefix . 'dropdown'
        );
    }
    
    /**
     * Output mobile menu script in footer
     */
    public function output_mobile_menu_script() {
        // Additional mobile menu functionality can be added here
        echo $this->get_accessibility_script();
    }
    
    /**
     * Get accessibility script for mobile menu
     */
    private function get_accessibility_script() {
        $classes = $this->get_mobile_menu_classes();
        
        return "
        <script>
        // Enhanced accessibility for mobile menu
        (function() {
            var mobileToggle = document.querySelector('{$classes['toggle']}');
            var mobileWrapper = document.querySelector('{$classes['wrapper']}');
            
            if (mobileToggle && mobileWrapper) {
                // Set initial ARIA attributes
                mobileToggle.setAttribute('aria-haspopup', 'true');
                mobileToggle.setAttribute('aria-expanded', 'false');
                mobileToggle.setAttribute('aria-controls', 'mobile-navigation');
                mobileWrapper.setAttribute('id', 'mobile-navigation');
                
                // Focus management
                var focusableElements = mobileWrapper.querySelectorAll('a, button, [tabindex=\"0\"]');
                var firstFocusable = focusableElements[0];
                var lastFocusable = focusableElements[focusableElements.length - 1];
                
                // Trap focus in mobile menu when open
                if (firstFocusable && lastFocusable) {
                    lastFocusable.addEventListener('keydown', function(e) {
                        if (e.key === 'Tab' && !e.shiftKey && mobileWrapper.classList.contains('active')) {
                            e.preventDefault();
                            firstFocusable.focus();
                        }
                    });
                    
                    firstFocusable.addEventListener('keydown', function(e) {
                        if (e.key === 'Tab' && e.shiftKey && mobileWrapper.classList.contains('active')) {
                            e.preventDefault();
                            lastFocusable.focus();
                        }
                    });
                }
            }
        })();
        </script>
        ";
    }
    
    /**
     * Get mobile menu toggle HTML
     */
    public function get_mobile_toggle_html() {
        $classes = $this->get_mobile_menu_classes();
        $toggle_class = str_replace('.', '', $classes['toggle']);
        
        return '<button class="' . $toggle_class . '" aria-haspopup="true" aria-expanded="false" aria-label="Menu Toggle">' .
               $this->get_hamburger_icon() .
               '</button>';
    }
    
    /**
     * Get hamburger icon HTML based on header type using ZX prefixes
     */
    private function get_hamburger_icon() {
        $prefixes = $this->get_component_prefixes();
        $prefix = isset($prefixes[$this->header_type]) ? $prefixes[$this->header_type] : 'zx_hd2_';
        $bar_class = $prefix . 'toggle_bar';
        
        return '<span class="' . $bar_class . '"></span>' .
               '<span class="' . $bar_class . '"></span>' .
               '<span class="' . $bar_class . '"></span>';
    }
    
    /**
     * Get mobile menu CSS
     */
    public function get_mobile_menu_css() {
        $breakpoint = $this->get_mobile_breakpoint();
        
        return "
        @media (max-width: {$breakpoint}px) {
            .mobile-menu-open {
                overflow: hidden;
            }
            
            .mobile-menu-open .mobile-menu-overlay {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9998;
            }
        }
        ";
    }
    
    /**
     * Get mobile breakpoint based on header type
     */
    private function get_mobile_breakpoint() {
        $breakpoints = array(
            'header1' => 1024,
            'header2' => 1024,
            'header3' => 768
        );
        
        return isset($breakpoints[$this->header_type]) ? $breakpoints[$this->header_type] : 1024;
    }
    
    /**
     * Add mobile menu overlay
     */
    public function add_mobile_overlay() {
        add_action('wp_footer', function() {
            echo '<div class="mobile-menu-overlay" style="display: none;"></div>';
        });
    }
    
    /**
     * Get header-specific mobile menu wrapper classes using ZX prefixes
     */
    public function get_wrapper_classes() {
        $prefixes = $this->get_component_prefixes();
        $prefix = isset($prefixes[$this->header_type]) ? $prefixes[$this->header_type] : 'zx_hd2_';
        return $prefix . 'menu_wrapper';
    }
    
    /**
     * Check if mobile menu is active
     */
    public function is_mobile_active() {
        return wp_is_mobile() || (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Mobile|Android|iPhone|iPad/', $_SERVER['HTTP_USER_AGENT']));
    }
}