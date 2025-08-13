<?php
/**
 * rupcacheman page - Cache Management
 */
function snefuru_rupcacheman_page() {
    // Get the admin instance for accessing helper methods
    global $snefuru_admin;
    
    // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
    $snefuru_admin->suppress_all_admin_notices();
    
    ?>
    <div class="wrap" style="margin: 0; padding: 0;">
        <!-- Allow space for WordPress notices -->
        <div style="height: 20px;"></div>
        
        <div style="padding: 20px;">
            <h1 style="margin-bottom: 20px;">🧹 Cache Management - rupcacheman</h1>
            
            <div style="background: white; border: 1px solid #ddd; padding: 30px; border-radius: 5px; max-width: 1200px;">
                <p style="margin-bottom: 30px; color: #666;">
                    Use these buttons to clear different types of caches that might be preventing updates from appearing.
                    Each button targets specific cache mechanisms.
                </p>
                
                <!-- WordPress Core Caches -->
                <div style="margin-bottom: 40px;">
                    <h3 style="color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-bottom: 20px;">WordPress Core Caches</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_object_cache" 
                                style="padding: 15px; background: #0073aa; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            🗃️ Clear Object Cache
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">wp_cache_flush()</div>
                        </button>
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_transients"
                                style="padding: 15px; background: #0073aa; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            ⏰ Clear Transients
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">Delete all _transient_* data</div>
                        </button>
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_rewrite_rules"
                                style="padding: 15px; background: #0073aa; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            🔄 Flush Rewrite Rules
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">flush_rewrite_rules()</div>
                        </button>
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_opcache"
                                style="padding: 15px; background: #0073aa; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            ⚡ Clear OpCache
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">opcache_reset() if available</div>
                        </button>
                    </div>
                </div>
                
                <!-- Plugin & Theme Caches -->
                <div style="margin-bottom: 40px;">
                    <h3 style="color: #16a085; border-bottom: 2px solid #16a085; padding-bottom: 10px; margin-bottom: 20px;">Plugin & Theme Caches</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_plugin_cache"
                                style="padding: 15px; background: #16a085; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            🔌 Clear Plugin Caches
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">wp-content/cache/ directories</div>
                        </button>
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_elementor_cache"
                                style="padding: 15px; background: #16a085; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            🎨 Clear Elementor Cache
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">Elementor files_manager cache</div>
                        </button>
                    </div>
                </div>
                
                <!-- Database & File Caches -->
                <div style="margin-bottom: 40px;">
                    <h3 style="color: #e67e22; border-bottom: 2px solid #e67e22; padding-bottom: 10px; margin-bottom: 20px;">Database & File Caches</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_database_cache"
                                style="padding: 15px; background: #e67e22; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            🗄️ Clear Database Cache
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">Meta cache & database queries</div>
                        </button>
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_file_cache"
                                style="padding: 15px; background: #e67e22; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            📁 Clear File Cache
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">Compiled assets & temp files</div>
                        </button>
                    </div>
                </div>
                
                <!-- Server & Advanced Caches -->
                <div style="margin-bottom: 40px;">
                    <h3 style="color: #8e44ad; border-bottom: 2px solid #8e44ad; padding-bottom: 10px; margin-bottom: 20px;">Server & Advanced Caches</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        
                        <button type="button" class="cache-btn" data-action="rup_clear_server_cache"
                                style="padding: 15px; background: #8e44ad; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            🖥️ Clear Server Cache
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">Memcached, Redis, APCu</div>
                        </button>
                        
                        <button type="button" class="cache-btn" data-action="rup_force_asset_reload"
                                style="padding: 15px; background: #8e44ad; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                            🔄 Force Asset Reload
                            <div style="font-size: 12px; margin-top: 5px; opacity: 0.9;">Version CSS/JS for browser reload</div>
                        </button>
                    </div>
                </div>
                
                <!-- Nuclear Option -->
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #e74c3c; border-bottom: 2px solid #e74c3c; padding-bottom: 10px; margin-bottom: 20px;">Nuclear Option</h3>
                    <div style="display: flex; justify-content: center;">
                        <button type="button" class="cache-btn" data-action="rup_nuclear_cache_flush"
                                style="padding: 20px 30px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 16px;">
                            💥 NUCLEAR CACHE FLUSH
                            <div style="font-size: 14px; margin-top: 5px; opacity: 0.9;">Clear ALL cache types at once</div>
                        </button>
                    </div>
                </div>
                
                <!-- Cache Reports Section -->
                <div style="margin-top: 40px;">
                    <h3 style="color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px;">📊 Recent Cache Operations</h3>
                    <div id="cache-reports" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                        <?php $snefuru_admin->display_cache_reports(); ?>
                    </div>
                    <div style="text-align: center;">
                        <button type="button" id="refresh-reports" style="padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            🔄 Refresh Reports
                        </button>
                    </div>
                </div>
                
                <!-- Results Area -->
                <div id="cache-results" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px; display: none;">
                    <h4 style="margin-top: 0; color: #333;">Results:</h4>
                    <div id="cache-results-content"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Define ajaxurl for this page since WordPress doesn't auto-define it everywhere
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        
        $('.cache-btn').on('click', function() {
            var button = $(this);
            var action = button.data('action');
            var originalText = button.html();
            
            // Update button state
            button.prop('disabled', true);
            button.html('🔄 Processing...');
            
            // Show results area
            $('#cache-results').show();
            $('#cache-results-content').append('<div>🔄 Running: ' + action + '...</div>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: action,
                    nonce: '<?php echo wp_create_nonce('rupcacheman_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var details = '';
                        if (response.data.items_cleared !== undefined) {
                            details += ' (' + response.data.items_cleared + ' items cleared';
                            if (response.data.execution_time) {
                                details += ', ' + response.data.execution_time;
                            }
                            details += ')';
                        }
                        $('#cache-results-content').append('<div style="color: #16a085;">✅ ' + action + ': ' + response.data.message + details + '</div>');
                    } else {
                        $('#cache-results-content').append('<div style="color: #e74c3c;">❌ ' + action + ': ' + response.data + '</div>');
                    }
                    
                    // Refresh the reports after a successful operation
                    if (response.success) {
                        setTimeout(function() {
                            refreshReports();
                        }, 1000);
                    }
                },
                error: function() {
                    $('#cache-results-content').append('<div style="color: #e74c3c;">❌ ' + action + ': AJAX error occurred</div>');
                },
                complete: function() {
                    // Reset button
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        });
        
        // Refresh reports button handler
        $('#refresh-reports').on('click', function() {
            refreshReports();
        });
        
        // Function to refresh cache reports
        function refreshReports() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rup_get_cache_reports',
                    nonce: '<?php echo wp_create_nonce('rupcacheman_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#cache-reports').html(response.data);
                    }
                }
            });
        }
    });
    </script>
    <?php
}