<?php
/**
 * Silkweaver Admin Page - Standalone menu system configuration
 * 
 * @package Ruplin
 * @subpackage SilkweaverMenu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the Silkweaver admin page
 */
function silkweaver_render_admin_page() {
    // IMMEDIATE notice suppression - before any output
    remove_all_actions('admin_notices');
    remove_all_actions('all_admin_notices');
    remove_all_actions('network_admin_notices');
    remove_all_actions('user_admin_notices');
    
    // Early CSS injection to hide notices immediately
    echo '<style>
        .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
        .updated, .error, .update-nag, .admin-notice,
        div.notice, div.updated, div.error, div.update-nag,
        .wrap > .notice, .wrap > .updated, .wrap > .error,
        #setting-error-settings_updated, .settings-error,
        .notice-alt, .notice-large, #message,
        .wp-core-ui .notice, .wp-core-ui .updated, .wp-core-ui .error {
            display: none !important;
            visibility: hidden !important;
        }
    </style>';
    
    // Get current settings
    $use_silkweaver = get_option('silkweaver_use_system', true);
    $menu_config = get_option('silkweaver_menu_config', '');
    
    // Handle form submissions
    if (isset($_POST['silkweaver_save_settings'])) {
        if (wp_verify_nonce($_POST['silkweaver_nonce'], 'silkweaver_save')) {
            $use_silkweaver = isset($_POST['use_silkweaver_system']) ? true : false;
            $menu_config = sanitize_textarea_field($_POST['menu_config']);
            
            update_option('silkweaver_use_system', $use_silkweaver);
            update_option('silkweaver_menu_config', $menu_config);
            
            echo '<div style="background: #46b450; color: white; padding: 10px; border-radius: 4px; margin: 20px 0;">Settings saved successfully!</div>';
        }
    }
    ?>
    
    <div class="wrap">
        <h1>Silkweaver Menu System</h1>
        <p>Configure your standalone menu system that replaces WordPress native menus.</p>
        
        <form method="post">
            <?php wp_nonce_field('silkweaver_save', 'silkweaver_nonce'); ?>
            
            <!-- Menu System Selection -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0;">
                <h3>FOR MAIN HEADER NAV MENU:</h3>
                <div style="margin: 15px 0;">
                    <label style="display: block; margin-bottom: 10px;">
                        <input type="radio" name="menu_system" value="silkweaver" <?php checked($use_silkweaver, true); ?> onchange="updateMenuSystem(true)">
                        <strong>Use silkweaver menu system</strong>
                    </label>
                    <label style="display: block;">
                        <input type="radio" name="menu_system" value="wordpress" <?php checked($use_silkweaver, false); ?> onchange="updateMenuSystem(false)">
                        <strong>Use normal native WP menu system</strong>
                    </label>
                    <input type="hidden" name="use_silkweaver_system" id="use_silkweaver_hidden" value="<?php echo $use_silkweaver ? '1' : '0'; ?>">
                </div>
            </div>
            
            <!-- Menu Configuration -->
            <div id="silkweaver_config" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0; <?php echo $use_silkweaver ? '' : 'opacity: 0.5;'; ?>">
                <h3>Menu Configuration</h3>
                <p>Enter your menu configuration code below:</p>
                
                <div style="font-size: 15px; font-weight: bold; margin: 15px 0 10px 0;">
                    WordPress option silkweaver_menu_config
                </div>
                
                <textarea name="menu_config" id="menu_config" rows="15" style="width: 100%; font-family: 'Courier New', monospace; padding: 10px; border: 1px solid #ccd0d4;" <?php echo $use_silkweaver ? '' : 'readonly'; ?>><?php echo esc_textarea($menu_config); ?></textarea>
                
                <div style="margin-top: 15px; padding: 15px; background: #f0f0f1; border-radius: 4px;">
                    <h4>Example Configuration:</h4>
                    <pre style="background: #23282d; color: #f1f1f1; padding: 10px; border-radius: 4px; overflow-x: auto;">target_url=/ anchor=Home
target_url=/derby anchor=Derby, UK
target_url=/termites anchor=Termite Extermination
pull_all_service_pages_dynamically
pull_all_service_pages_dynamically custom_raw_link=/saginaw Saginaw
pull_all_service_pages_dynamically custom_raw_link_pinned=/emergency Emergency Service
pull_all_location_pages_dynamically
pull_all_location_pages_dynamically custom_raw_link=/ Headquarters
pull_all_location_pages_dynamically custom_raw_link_pinned=/service-areas Service Areas</pre>
                    
                    <p><strong>Syntax Guide:</strong></p>
                    <ul>
                        <li><code>target_url=/path anchor=Link Text</code> - Static menu link</li>
                        <li><code>pull_all_service_pages_dynamically</code> - Dynamic "Services" dropdown with all service pages (alphabetically ordered)</li>
                        <li><code>pull_all_location_pages_dynamically</code> - Dynamic "Areas We Serve" dropdown with all location pages (alphabetically ordered)</li>
                        <li><code>pull_all_service_pages_dynamically custom_raw_link=/path Link Text</code> - Services dropdown + custom link (inserted alphabetically)</li>
                        <li><code>pull_all_location_pages_dynamically custom_raw_link=/path Link Text</code> - Areas We Serve dropdown + custom link (inserted alphabetically)</li>
                        <li><code>pull_all_service_pages_dynamically custom_raw_link_pinned=/path Link Text</code> - Services dropdown + pinned link (appears first, not sorted)</li>
                        <li><code>pull_all_location_pages_dynamically custom_raw_link_pinned=/path Link Text</code> - Areas We Serve dropdown + pinned link (appears first, not sorted)</li>
                    </ul>
                    
                    <p><strong>Custom Link Types:</strong></p>
                    <ul>
                        <li><strong>Regular custom links</strong> - Sorted alphabetically with database pages</li>
                        <li><strong>Pinned custom links</strong> - Always appear first in dropdown, not part of alphabetical sort</li>
                        <li>Example: <code>custom_raw_link=/ Headquarters</code> adds a "Headquarters" link sorted alphabetically</li>
                        <li>Example: <code>custom_raw_link_pinned=/emergency Emergency Service</code> adds "Emergency Service" link at the top</li>
                    </ul>
                </div>
            </div>
            
            <!-- Database Optimization -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin: 20px 0;">
                <h3>Database Optimization</h3>
                <p>Optimize database indexes for faster menu loading:</p>
                <button type="button" id="optimize_indexes" class="button button-secondary">Optimize Database Indexes</button>
                <div id="optimize_status" style="margin-top: 10px;"></div>
            </div>
            
            <p class="submit">
                <input type="submit" name="silkweaver_save_settings" class="button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    
    <script>
    function updateMenuSystem(useSilkweaver) {
        document.getElementById('use_silkweaver_hidden').value = useSilkweaver ? '1' : '0';
        const configDiv = document.getElementById('silkweaver_config');
        const textarea = document.getElementById('menu_config');
        
        if (useSilkweaver) {
            configDiv.style.opacity = '1';
            textarea.removeAttribute('readonly');
        } else {
            configDiv.style.opacity = '0.5';
            textarea.setAttribute('readonly', 'readonly');
        }
    }
    
    // Database optimization
    document.getElementById('optimize_indexes').addEventListener('click', function() {
        const button = this;
        const status = document.getElementById('optimize_status');
        
        button.disabled = true;
        button.textContent = 'Optimizing...';
        status.innerHTML = '<p style="color: #666;">Running database optimization...</p>';
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'silkweaver_optimize_indexes',
                nonce: '<?php echo wp_create_nonce('snefuru_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                status.innerHTML = '<p style="color: #46b450;">✅ ' + data.data.message + '</p>';
            } else {
                status.innerHTML = '<p style="color: #dc3232;">❌ ' + (data.data.message || 'Optimization failed') + '</p>';
            }
        })
        .catch(error => {
            status.innerHTML = '<p style="color: #dc3232;">❌ Error: ' + error + '</p>';
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = 'Optimize Database Indexes';
        });
    });
    </script>
    
    <?php
}