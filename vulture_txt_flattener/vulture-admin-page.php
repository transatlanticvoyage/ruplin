<?php

function vulture_txt_flattener_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    global $wpdb;
    
    $domain = parse_url(get_option('siteurl'), PHP_URL_HOST);
    $domain = preg_replace('/^www\./', '', $domain);
    
    $total_pages = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish'");
    $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'");
    $total_all = $total_pages + $total_posts;
    
    $db = new Vulture_DB();
    $generations = $db->get_all_generations();
    
    wp_enqueue_style('vulture-styles', plugin_dir_url(__FILE__) . 'vulture-styles.css', array(), '1.0.0');
    ?>
    
    <div class="wrap vulture-wrap">
        <h1>Vulture TXT Flattener</h1>
        
        <div class="vulture-info-row">
            <span><strong>Site:</strong> <?php echo esc_html($domain); ?></span>
            <span class="sep">|</span>
            <span><strong>Pages:</strong> <?php echo esc_html($total_pages); ?></span>
            <span class="sep">|</span>
            <span><strong>Posts:</strong> <?php echo esc_html($total_posts); ?></span>
            <span class="sep">|</span>
            <span><strong>Total:</strong> <?php echo esc_html($total_all); ?></span>
        </div>
        
        <div class="vulture-options-panel">
            <h2>Options</h2>
            <div class="vulture-options">
                <label>
                    <input type="checkbox" id="vulture-include-pages" checked> Include pages
                </label>
                <label>
                    <input type="checkbox" id="vulture-include-posts"> Include posts
                </label>
                <label>
                    <input type="checkbox" id="vulture-generate-zip" checked> Generate ZIP after flattening
                </label>
            </div>
            
            <button type="button" id="vulture-flatten-btn" class="button button-primary button-hero">
                Flatten Site Now
            </button>
        </div>
        
        <div id="vulture-status-area" class="vulture-status-area" style="display: none;">
            <div class="notice notice-info">
                <p id="vulture-status-message"></p>
            </div>
        </div>
        
        <div class="vulture-generations-panel">
            <h2>Previous Generations</h2>
            <?php if (empty($generations)) : ?>
                <p>No generations yet. Click "Flatten Site Now" to create your first generation.</p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-number">#</th>
                            <th scope="col" class="manage-column column-folder">Folder</th>
                            <th scope="col" class="manage-column column-files">Files</th>
                            <th scope="col" class="manage-column column-status">Status</th>
                            <th scope="col" class="manage-column column-date">Date</th>
                            <th scope="col" class="manage-column column-download">Download</th>
                        </tr>
                    </thead>
                    <tbody id="vulture-generations-tbody">
                        <?php foreach ($generations as $gen) : ?>
                            <tr>
                                <td><?php echo esc_html($gen['folder_number']); ?></td>
                                <td><?php echo esc_html($gen['folder_number'] . '_' . $gen['domain']); ?></td>
                                <td>
                                    <?php echo esc_html($gen['total_files']); ?>
                                    <span class="description">
                                        (<?php echo esc_html($gen['total_pages']); ?> pages, 
                                        <?php echo esc_html($gen['total_posts']); ?> posts)
                                    </span>
                                </td>
                                <td>
                                    <span class="vulture-status-badge vulture-status-<?php echo esc_attr($gen['status']); ?>">
                                        <?php echo esc_html($gen['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($gen['completed_at'] ?: $gen['started_at']); ?></td>
                                <td>
                                    <?php if ($gen['zip_path']) : ?>
                                        <button type="button" 
                                                class="button vulture-download-btn" 
                                                data-generation-id="<?php echo esc_attr($gen['generation_id']); ?>">
                                            Download ZIP
                                        </button>
                                    <?php else : ?>
                                        <span class="description">No ZIP</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var flattenBtn = document.getElementById('vulture-flatten-btn');
            var statusArea = document.getElementById('vulture-status-area');
            var statusMessage = document.getElementById('vulture-status-message');
            
            if (!flattenBtn) return;
            
            flattenBtn.addEventListener('click', function() {
                var includePages = document.getElementById('vulture-include-pages').checked;
                var includePosts = document.getElementById('vulture-include-posts').checked;
                var generateZip = document.getElementById('vulture-generate-zip').checked;
                
                if (!includePages && !includePosts) {
                    alert('Please select at least one option: pages or posts');
                    return;
                }
                
                flattenBtn.disabled = true;
                flattenBtn.textContent = 'Processing...';
                
                statusArea.style.display = 'block';
                statusArea.className = 'vulture-status-area';
                statusMessage.textContent = 'Starting flatten process...';
                
                var formData = new FormData();
                formData.append('action', 'vulture_flatten_site');
                formData.append('nonce', '<?php echo wp_create_nonce('vulture_flatten_nonce'); ?>');
                formData.append('include_pages', includePages);
                formData.append('include_posts', includePosts);
                formData.append('generate_zip', generateZip);
                
                fetch(ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(result) {
                    if (result.success) {
                        statusArea.className = 'vulture-status-area';
                        statusArea.querySelector('.notice').className = 'notice notice-success';
                        statusMessage.textContent = result.data.message;
                        
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        statusArea.className = 'vulture-status-area';
                        statusArea.querySelector('.notice').className = 'notice notice-error';
                        statusMessage.textContent = result.data ? result.data.message : 'An error occurred';
                    }
                })
                .catch(function(error) {
                    statusArea.className = 'vulture-status-area';
                    statusArea.querySelector('.notice').className = 'notice notice-error';
                    statusMessage.textContent = 'Network error: ' + error.message;
                })
                .finally(function() {
                    flattenBtn.disabled = false;
                    flattenBtn.textContent = 'Flatten Site Now';
                });
            });
            
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('vulture-download-btn')) {
                    var generationId = e.target.getAttribute('data-generation-id');
                    
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = ajaxurl;
                    
                    var fields = {
                        'action': 'vulture_download_zip',
                        'nonce': '<?php echo wp_create_nonce('vulture_download_nonce'); ?>',
                        'generation_id': generationId
                    };
                    
                    for (var key in fields) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = fields[key];
                        form.appendChild(input);
                    }
                    
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                }
            });
        });
    })();
    </script>
    <?php
}