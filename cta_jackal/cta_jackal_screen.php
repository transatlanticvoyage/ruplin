<?php
/**
 * CTA Horizontal Jackal System
 * 
 * @package Ruplin
 * @subpackage CTA_Jackal
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Aggressive warning/notice suppression - same as other Ruplin pages
add_action('admin_init', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'cta_jackal') {
        // Remove all admin notices
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('user_admin_notices');
        remove_all_actions('network_admin_notices');
        
        // Add our custom action to suppress notices
        add_action('admin_notices', function() {
            // Remove any queued notices
            remove_all_actions('admin_notices');
        }, -9999);
        
        add_action('all_admin_notices', function() {
            // Remove any queued notices
            remove_all_actions('all_admin_notices');
        }, -9999);
        
        // Also suppress any admin notice hooks that might be added later
        add_action('admin_head', function() {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            ?>
            <style>
                /* Hide any notices that slip through */
                .notice,
                .notice-error,
                .notice-warning,
                .notice-success,
                .notice-info,
                .updated,
                .error,
                .update-nag,
                #message,
                .wrap > .notice,
                .wrap > .error,
                .wrap > .updated {
                    display: none !important;
                }
                
                /* Jackal icon styles */
                .cta-jackal-header {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    margin: 20px 0 30px 0;
                }
                
                .cta-jackal-icon {
                    width: 48px;
                    height: 48px;
                    flex-shrink: 0;
                }
                
                .cta-jackal-title {
                    font-size: 24px;
                    font-weight: 600;
                    color: #1e1e1e;
                    margin: 0;
                    padding: 0;
                }
                
                /* Page container */
                .cta-jackal-container {
                    max-width: 1200px;
                    margin: 20px 20px 20px 0;
                    background: #fff;
                    padding: 30px;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
            </style>
            <?php
        });
    }
}, -9999);

// Main CTA Jackal page function
function cta_jackal_page() {
    ?>
    <div class="wrap">
        <div class="cta-jackal-container">
            <div class="cta-jackal-header">
                <!-- Jackal SVG Icon -->
                <svg class="cta-jackal-icon" viewBox="0 0 24 24" fill="#8B4513" xmlns="http://www.w3.org/2000/svg">
                    <!-- Jackal head/silhouette -->
                    <path d="M4 8C4 6 5 4 7 3C8 2.5 9 2.5 9.5 3C10 3.5 10 4 10 4.5C10 4 10 3.5 10.5 3C11 2.5 12 2.5 13 3C15 4 16 6 16 8C16 8 16 9 15.5 10C15 11 14 12 12 12C10 12 9 11 8.5 10C8 9 8 8 8 8C8 8 8 9 7.5 10C7 11 6 12 4 12C2 12 1 11 0.5 10C0 9 0 8 0 8L4 8Z" transform="translate(4, 4)"/>
                    <!-- Ears -->
                    <path d="M6 3L5 1L4 3" stroke="#8B4513" stroke-width="0.5" fill="#8B4513"/>
                    <path d="M18 3L19 1L20 3" stroke="#8B4513" stroke-width="0.5" fill="#8B4513"/>
                    <!-- Body suggestion -->
                    <path d="M8 12C8 12 7 14 7 16C7 18 7 20 8 21C9 22 11 22 12 22C13 22 15 22 16 21C17 20 17 18 17 16C17 14 16 12 16 12" fill="#8B4513"/>
                    <!-- Tail -->
                    <path d="M16 18C16 18 18 19 19 20C20 21 20 22 19 22C18 22 17 21 16 20" fill="#8B4513"/>
                </svg>
                <h1 class="cta-jackal-title">CTA Horizontal Jackal System</h1>
            </div>
        </div>
    </div>
    <?php
}