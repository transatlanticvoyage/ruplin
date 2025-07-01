/**
 * Hot Plugin Updater JavaScript
 * Handles the upload and update process with real-time progress
 */

jQuery(document).ready(function($) {
    
    const progressSteps = [
        { id: 'create_backup', title: 'Creating backup of current plugin' },
        { id: 'extract_zip', title: 'Extracting uploaded ZIP file' },
        { id: 'validate_structure', title: 'Validating plugin structure' },
        { id: 'update_files', title: 'Updating plugin files' },
        { id: 'clear_caches', title: 'Clearing caches' },
        { id: 'verify_update', title: 'Verifying update and cleanup' }
    ];
    
    // Initialize progress display
    function initializeProgress() {
        const progressContainer = $('#progress-steps');
        progressContainer.empty();
        
        progressSteps.forEach(step => {
            const stepDiv = $(`
                <div class="progress-step pending" id="step-${step.id}">
                    <span class="step-icon">⏳</span>
                    <span class="step-title">${step.title}</span>
                </div>
            `);
            progressContainer.append(stepDiv);
        });
    }
    
    // Update step status
    function updateStepStatus(stepId, status, message = '') {
        const stepElement = $(`#step-${stepId}`);
        const iconElement = stepElement.find('.step-icon');
        
        // Remove all status classes
        stepElement.removeClass('pending running success error');
        
        // Add new status class and icon
        stepElement.addClass(status);
        
        switch (status) {
            case 'running':
                iconElement.html('<div class="spinner"></div>');
                break;
            case 'success':
                iconElement.html('✅');
                break;
            case 'error':
                iconElement.html('❌');
                break;
            default:
                iconElement.html('⏳');
        }
        
        // Add message if provided
        if (message) {
            let messageSpan = stepElement.find('.step-message');
            if (messageSpan.length === 0) {
                messageSpan = $('<span class="step-message"></span>');
                stepElement.append(messageSpan);
            }
            messageSpan.html(` - ${message}`);
        }
    }
    
    // Log message to progress log
    function logMessage(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logClass = type === 'error' ? 'color: red;' : type === 'success' ? 'color: green;' : '';
        const logEntry = `<div style="${logClass}">[${timestamp}] ${message}</div>`;
        
        $('#progress-log').append(logEntry);
        
        // Auto-scroll to bottom
        const logContainer = $('#progress-log')[0];
        logContainer.scrollTop = logContainer.scrollHeight;
    }
    
    // Handle form submission
    $('#hot-update-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = $('#upload-btn');
        const fileInput = $('#plugin-zip')[0];
        
        // Validate file selection
        if (!fileInput.files.length) {
            alert('Please select a ZIP file to upload.');
            return;
        }
        
        // Show progress section
        $('#update-progress').show();
        initializeProgress();
        $('#progress-log').empty();
        
        // Disable form
        uploadBtn.prop('disabled', true).text('Updating...');
        
        // Add AJAX parameters
        formData.append('action', 'snefuru_hot_update');
        formData.append('nonce', snefuru_ajax.nonce);
        
        // Log start
        logMessage('Starting hot plugin update process...', 'info');
        logMessage(`File: ${fileInput.files[0].name} (${(fileInput.files[0].size / 1024 / 1024).toFixed(2)} MB)`, 'info');
        
        // Simulate progress steps (since we can't get real-time progress from PHP)
        let currentStepIndex = 0;
        const progressInterval = setInterval(() => {
            if (currentStepIndex < progressSteps.length) {
                const step = progressSteps[currentStepIndex];
                updateStepStatus(step.id, 'running');
                logMessage(`${step.title}...`, 'info');
                currentStepIndex++;
            }
        }, 1000);
        
        // Make AJAX request
        $.ajax({
            url: snefuru_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 120000, // 2 minutes timeout
            success: function(response) {
                clearInterval(progressInterval);
                
                if (response.success) {
                    // Update all steps based on server response
                    if (response.data.steps) {
                        response.data.steps.forEach(step => {
                            updateStepStatus(step.step, step.status, step.message);
                            logMessage(`${step.message}`, step.status === 'success' ? 'success' : 'error');
                        });
                    }
                    
                    logMessage('✅ Plugin update completed successfully!', 'success');
                    logMessage('🔄 You may need to refresh this page to see changes.', 'info');
                    
                    // Show success message
                    $('<div class="notice notice-success"><p><strong>Success!</strong> Plugin updated successfully. You may need to refresh the page to see changes.</p></div>')
                        .insertAfter('h1')
                        .delay(5000)
                        .fadeOut();
                        
                } else {
                    // Handle error
                    const errorMessage = response.data.message || 'Unknown error occurred';
                    logMessage(`❌ Update failed: ${errorMessage}`, 'error');
                    
                    // Update failed steps
                    if (response.data.steps) {
                        response.data.steps.forEach(step => {
                            updateStepStatus(step.step, step.status, step.message);
                        });
                    }
                    
                    // Show error message
                    $('<div class="notice notice-error"><p><strong>Error!</strong> ' + errorMessage + '</p></div>')
                        .insertAfter('h1')
                        .delay(10000)
                        .fadeOut();
                }
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                
                let errorMessage = 'Network error occurred';
                if (status === 'timeout') {
                    errorMessage = 'Upload timeout - file may be too large or server is slow';
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        errorMessage = errorData.data || errorData.message || errorMessage;
                    } catch (e) {
                        errorMessage = `Server error: ${xhr.status} ${error}`;
                    }
                }
                
                logMessage(`❌ Upload failed: ${errorMessage}`, 'error');
                
                // Mark current step as failed
                if (currentStepIndex > 0) {
                    const currentStep = progressSteps[currentStepIndex - 1];
                    updateStepStatus(currentStep.id, 'error', 'Failed');
                }
                
                // Show error message
                $('<div class="notice notice-error"><p><strong>Upload Failed!</strong> ' + errorMessage + '</p></div>')
                    .insertAfter('h1')
                    .delay(10000)
                    .fadeOut();
            },
            complete: function() {
                // Re-enable form
                uploadBtn.prop('disabled', false).text('🚀 Upload & Update Plugin');
                
                // Log completion
                const timestamp = new Date().toLocaleString();
                logMessage(`Process completed at ${timestamp}`, 'info');
            }
        });
    });
    
    // File input change handler
    $('#plugin-zip').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const fileInfo = `Selected: ${file.name} (${fileSize} MB)`;
            
            // Remove existing file info
            $('.file-info').remove();
            
            // Add file info
            $(this).after(`<p class="description file-info" style="color: #0073aa; font-weight: bold;">${fileInfo}</p>`);
            
            // Warn about large files
            if (file.size > 50 * 1024 * 1024) { // 50MB
                $(this).after('<p class="description file-info" style="color: #d63638;">⚠️ Large file detected. Upload may take longer than usual.</p>');
            }
        }
    });
    
    // Add keyboard shortcut (Ctrl+Shift+U) to focus upload input
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'U') {
            e.preventDefault();
            $('#plugin-zip').focus().click();
        }
    });
    
    // Add drag and drop functionality
    const dropZone = $('#hot-update-form');
    
    dropZone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });
    
    dropZone.on('dragleave dragend', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });
    
    dropZone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.name.endsWith('.zip')) {
                $('#plugin-zip')[0].files = files;
                $('#plugin-zip').trigger('change');
            } else {
                alert('Please drop a ZIP file.');
            }
        }
    });
    
    // Add CSS for drag and drop
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .drag-over {
                background-color: #e3f2fd !important;
                border: 2px dashed #2196f3 !important;
            }
            #hot-update-form {
                transition: all 0.3s ease;
            }
        `)
        .appendTo('head');
});