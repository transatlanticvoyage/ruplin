<?php

if (!defined('ABSPATH')) {
    exit;
}

function ruplin_favicon_mar_page() {
    // Get current favicon
    $current_favicon = get_option('ruplin_favicon_url', '');
    ?>
    <div class="wrap">
        <h1><strong>favicon_mar - Favicon Manager</strong></h1>
        <p>Upload and manage your site's favicon (website icon that appears in browser tabs)</p>
        
        <!-- Current Favicon Display -->
        <div style="margin: 20px 0;">
            <div class="card" style="max-width: 600px; padding: 20px;">
                <h2>Current Favicon</h2>
                <div id="current-favicon-container">
                    <?php if (!empty($current_favicon)): ?>
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <img src="<?php echo esc_url($current_favicon); ?>" 
                                 style="width: 32px; height: 32px; border: 1px solid #ddd; border-radius: 3px;"
                                 alt="Current favicon">
                            <div>
                                <strong>Active:</strong> 
                                <a href="<?php echo esc_url($current_favicon); ?>" target="_blank">
                                    <?php echo esc_html(basename($current_favicon)); ?>
                                </a>
                            </div>
                        </div>
                        <button id="remove-favicon-btn" class="button button-secondary" style="color: #d63384;">
                            🗑️ Remove Current Favicon
                        </button>
                    <?php else: ?>
                        <p style="color: #666; font-style: italic;">No favicon currently set</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Upload New Favicon -->
        <div style="margin: 20px 0;">
            <div class="card" style="max-width: 600px; padding: 20px;">
                <h2>Upload New Favicon</h2>
                <p><strong>Supported formats:</strong> ICO, PNG, JPG, GIF, SVG (max 2MB)</p>
                
                <form id="favicon-upload-form" enctype="multipart/form-data" style="margin-top: 20px;">
                    <div style="margin-bottom: 15px;">
                        <input type="file" id="favicon-file" name="favicon_file" 
                               accept=".ico,.png,.jpg,.jpeg,.gif,.svg" 
                               style="margin-bottom: 10px;">
                    </div>
                    
                    <button type="submit" id="upload-favicon-btn" class="button button-primary">
                        📁 Upload Favicon
                    </button>
                    
                    <div id="upload-progress" style="display: none; margin-top: 10px;">
                        <div style="background: #f1f1f1; border-radius: 10px; height: 6px; overflow: hidden;">
                            <div style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s;" id="progress-bar"></div>
                        </div>
                        <p style="margin: 5px 0; font-size: 12px; color: #666;">Uploading...</p>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Preview Area -->
        <div style="margin: 20px 0;">
            <div class="card" style="max-width: 600px; padding: 20px;">
                <h2>Preview</h2>
                <div id="favicon-preview" style="display: none; margin-top: 15px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img id="preview-image" style="width: 32px; height: 32px; border: 1px solid #ddd; border-radius: 3px;" alt="Favicon preview">
                        <div>
                            <strong>Preview:</strong> <span id="preview-filename"></span>
                        </div>
                    </div>
                </div>
                <p style="color: #666; font-size: 12px; margin-top: 10px;">
                    💡 <strong>Tip:</strong> For best results, use a 32x32 or 16x16 pixel image in ICO or PNG format.
                </p>
            </div>
        </div>
        
        <!-- Feedback Messages -->
        <div id="favicon-feedback" style="display: none; margin: 20px 0; padding: 15px; border-radius: 5px;"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('favicon-upload-form');
        const fileInput = document.getElementById('favicon-file');
        const uploadBtn = document.getElementById('upload-favicon-btn');
        const removeBtn = document.getElementById('remove-favicon-btn');
        const progress = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const feedback = document.getElementById('favicon-feedback');
        const preview = document.getElementById('favicon-preview');
        const previewImage = document.getElementById('preview-image');
        const previewFilename = document.getElementById('preview-filename');
        const currentContainer = document.getElementById('current-favicon-container');
        
        // File selection preview
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/svg+xml'];
                if (!allowedTypes.includes(file.type) && !file.name.toLowerCase().match(/\.(ico|png|jpe?g|gif|svg)$/)) {
                    showFeedback('Invalid file type. Please select ICO, PNG, JPG, GIF, or SVG files only.', 'error');
                    this.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewFilename.textContent = file.name;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Upload form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const file = fileInput.files[0];
            if (!file) {
                showFeedback('Please select a file to upload.', 'error');
                return;
            }
            
            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'upload_favicon');
            formData.append('favicon_file', file);
            formData.append('nonce', '<?php echo wp_create_nonce("favicon_upload_nonce"); ?>');
            
            // Show progress
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            progress.style.display = 'block';
            progressBar.style.width = '50%';
            
            // Send AJAX request
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                progressBar.style.width = '100%';
                
                if (result.success) {
                    showFeedback('✓ Favicon uploaded successfully!', 'success');
                    
                    // Update current favicon display
                    currentContainer.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <img src="${result.data.favicon_url}" 
                                 style="width: 32px; height: 32px; border: 1px solid #ddd; border-radius: 3px;"
                                 alt="Current favicon">
                            <div>
                                <strong>Active:</strong> 
                                <a href="${result.data.favicon_url}" target="_blank">
                                    ${file.name}
                                </a>
                            </div>
                        </div>
                        <button id="remove-favicon-btn" class="button button-secondary" style="color: #d63384;">
                            🗑️ Remove Current Favicon
                        </button>
                    `;
                    
                    // Reset form
                    form.reset();
                    preview.style.display = 'none';
                    
                    // Re-attach remove button event
                    attachRemoveHandler();
                } else {
                    showFeedback('Error: ' + result.data, 'error');
                }
            })
            .catch(error => {
                showFeedback('Upload failed: ' + error.message, 'error');
            })
            .finally(() => {
                uploadBtn.disabled = false;
                uploadBtn.textContent = '📁 Upload Favicon';
                setTimeout(() => {
                    progress.style.display = 'none';
                    progressBar.style.width = '0%';
                }, 1000);
            });
        });
        
        // Remove favicon functionality
        function attachRemoveHandler() {
            const newRemoveBtn = document.getElementById('remove-favicon-btn');
            if (newRemoveBtn) {
                newRemoveBtn.addEventListener('click', removeFavicon);
            }
        }
        
        function removeFavicon() {
            if (!confirm('Are you sure you want to remove the current favicon?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'remove_favicon');
            formData.append('nonce', '<?php echo wp_create_nonce("favicon_remove_nonce"); ?>');
            
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showFeedback('✓ Favicon removed successfully!', 'success');
                    currentContainer.innerHTML = '<p style="color: #666; font-style: italic;">No favicon currently set</p>';
                } else {
                    showFeedback('Error: ' + result.data, 'error');
                }
            })
            .catch(error => {
                showFeedback('Remove failed: ' + error.message, 'error');
            });
        }
        
        // Attach initial remove handler
        if (removeBtn) {
            removeBtn.addEventListener('click', removeFavicon);
        }
        
        // Show feedback message
        function showFeedback(message, type) {
            feedback.textContent = message;
            feedback.className = type === 'success' ? 'notice notice-success' : 'notice notice-error';
            feedback.style.display = 'block';
            
            setTimeout(() => {
                feedback.style.display = 'none';
            }, 5000);
        }
    });
    </script>
    <?php
}