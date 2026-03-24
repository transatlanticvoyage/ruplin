<?php
/**
 * Jezel Navigation Component
 * Provides vertical page navigation with snap scrolling functionality
 * 
 * @package Ruplin
 * @subpackage JezelNavigation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Jezel_Navigation {
    
    /**
     * Render the Jezel navigation buttons
     * 
     * @param array $args Optional arguments for customization
     */
    public static function render($args = array()) {
        $defaults = array(
            'show_save' => false,
            'save_form_id' => '',
            'show_pendulum' => false,
            'post_id' => null,
            'show_frontend' => false,
            'custom_buttons' => array(),
            'position_left' => '180px',
            'position_top' => '120px'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        ?>
        <!-- Jezel Navigation Buttons -->
        <div id="jezel-navigation" class="jezel-nav-container" style="left: <?php echo esc_attr($args['position_left']); ?>; top: <?php echo esc_attr($args['position_top']); ?>;">
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
            
            <?php if ($args['show_pendulum'] && $args['post_id']): ?>
            <!-- Jezel Pendulum Button - Links to WP Native Editor -->
            <button 
                id="jezel-pendulum" 
                class="jezel-btn jezel-pendulum-btn"
                onclick="window.open('<?php echo admin_url('post.php?post=' . $args['post_id'] . '&action=edit'); ?>', '_blank')"
                title="Edit in WordPress Editor"
            >
                <!-- Pendulum Icon SVG -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 2px;">
                    <line x1="12" y1="2" x2="12" y2="12"></line>
                    <circle cx="12" cy="16" r="4"></circle>
                    <line x1="12" y1="2" x2="16" y2="6"></line>
                    <line x1="12" y1="2" x2="8" y2="6"></line>
                </svg>
                <span style="font-size: 10px; color: white; margin-top: 2px;">edit</span>
            </button>
            <?php endif; ?>
            
            <?php if ($args['show_frontend'] && $args['post_id']): ?>
            <!-- Jezel Frontend Button - Links to Live URL -->
            <button 
                id="jezel-frontend" 
                class="jezel-btn jezel-frontend-btn"
                onclick="window.open('<?php echo get_permalink($args['post_id']); ?>', '_blank')"
                title="View on Frontend"
            >
                <span style="font-size: 12px; display: block; line-height: 1.2;">front</span>
                <span style="font-size: 12px; display: block; line-height: 1.2;">end</span>
            </button>
            <?php endif; ?>
            
            <?php if ($args['show_save'] && $args['save_form_id']): ?>
            <!-- Jezel Save Button -->
            <button 
                id="jezel-save" 
                class="jezel-btn"
                onclick="document.getElementById('<?php echo esc_attr($args['save_form_id']); ?>').submit();"
                title="Save Content"
                style="background-color: #23bb73 !important; color: white !important; border-color: #23bb73 !important;"
            >
                <span style="font-size: 12px; display: block; line-height: 1.2;">save</span>
            </button>
            <?php endif; ?>
            
            <?php 
            // Render custom buttons if provided
            if (!empty($args['custom_buttons'])) {
                foreach ($args['custom_buttons'] as $button) {
                    echo $button;
                }
            }
            ?>
        </div>
        <?php
        
        // Include CSS and JS only once
        static $assets_included = false;
        if (!$assets_included) {
            self::render_assets();
            $assets_included = true;
        }
    }
    
    /**
     * Render CSS and JavaScript for Jezel navigation
     */
    private static function render_assets() {
        ?>
        <style>
            /* Jezel Navigation Styles */
            .jezel-nav-container {
                position: fixed;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .jezel-btn {
                width: 41px;
                height: 41px;
                padding: 2px;
                background-color: #a8c5e6;
                border: 1px solid #4b5563;
                border-radius: 4px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                font-weight: 600;
                transition: all 0.2s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
                background-color: #4a4a4a !important;
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
        
        // Initialize Jezel buttons when document is ready
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                // Initial button state
                updateJezelButtons();
                
                // Update on scroll
                $(window).on('scroll', function() {
                    updateJezelButtons();
                });
            });
        } else {
            // Fallback for non-jQuery environments
            document.addEventListener('DOMContentLoaded', function() {
                updateJezelButtons();
                window.addEventListener('scroll', updateJezelButtons);
            });
        }
        </script>
        <?php
    }
}