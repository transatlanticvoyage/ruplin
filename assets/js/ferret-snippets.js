/**
 * Ferret Snippets JavaScript
 * 
 * Handles UI interactions for the code snippet tabs and saving
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initFerretSnippets();
    });
    
    /**
     * Initialize Ferret Snippets functionality
     */
    function initFerretSnippets() {
        // Tab switching
        $(document).on('click', '.ferret-tab', function(e) {
            e.preventDefault();
            switchTab($(this));
        });
        
        // Save button clicks
        $(document).on('click', '.ferret-save-btn', function(e) {
            e.preventDefault();
            saveSnippet($(this));
        });
        
        // Load existing snippets when popup opens
        $(document).on('click', '.snefuru-lightning-popup-btn', function() {
            // Small delay to ensure popup is fully opened
            setTimeout(loadSnippets, 300);
        });
        
        // Auto-resize textareas
        $(document).on('input', '.ferret-code-editor', function() {
            autoResizeTextarea(this);
        });
        
        console.log('Ferret Snippets initialized');
    }
    
    /**
     * Switch between tabs
     */
    function switchTab($clickedTab) {
        var tabType = $clickedTab.data('tab');
        
        // Update tab buttons
        $('.ferret-tab').removeClass('active');
        $clickedTab.addClass('active');
        
        // Update tab panels
        $('.ferret-tab-panel').removeClass('active');
        $('#ferret-tab-' + tabType).addClass('active');
        
        // Auto-resize textarea in newly visible tab
        setTimeout(function() {
            var $textarea = $('#ferret-tab-' + tabType + ' .ferret-code-editor');
            if ($textarea.length) {
                autoResizeTextarea($textarea[0]);
            }
        }, 100);
    }
    
    /**
     * Save code snippet
     */
    function saveSnippet($button) {
        var snippetType = $button.data('type');
        var $textarea = $('#ferret-' + snippetType + '-code');
        var code = $textarea.val();
        
        // Disable button during save
        $button.prop('disabled', true).text('Saving...');
        
        // Clear previous messages
        $('.ferret-snippet-message').hide();
        
        $.ajax({
            url: ferretSnippets.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ferret_save_snippet',
                nonce: ferretSnippets.nonce,
                post_id: ferretSnippets.postId,
                snippet_type: snippetType,
                snippet_code: code
            },
            success: function(response) {
                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message || ferretSnippets.messages.saveError, 'error');
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    showMessage(ferretSnippets.messages.saveError, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                showMessage(ferretSnippets.messages.saveError, 'error');
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).text('Save ' + capitalizeFirst(snippetType) + ' Code');
            }
        });
    }
    
    /**
     * Load existing snippets
     */
    function loadSnippets() {
        // Only load if we have a post ID and the container exists
        if (!ferretSnippets.postId || !$('#ferret-snippets-container').length) {
            return;
        }
        
        $.ajax({
            url: ferretSnippets.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ferret_load_snippets',
                nonce: ferretSnippets.nonce,
                post_id: ferretSnippets.postId
            },
            success: function(response) {
                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success && data.data) {
                        $('#ferret-header-code').val(data.data.header || '');
                        $('#ferret-footer-code').val(data.data.footer || '');
                        
                        // Auto-resize textareas
                        $('.ferret-code-editor').each(function() {
                            autoResizeTextarea(this);
                        });
                    } else {
                        console.warn('Load snippets warning:', data.message);
                    }
                } catch (e) {
                    console.error('Parse error loading snippets:', e);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading snippets:', error);
                showMessage(ferretSnippets.messages.loadError, 'error');
            }
        });
    }
    
    /**
     * Show message to user
     */
    function showMessage(message, type) {
        var $messageDiv = $('.ferret-snippet-message');
        $messageDiv
            .removeClass('success error')
            .addClass(type)
            .text(message)
            .fadeIn()
            .delay(4000)
            .fadeOut();
    }
    
    /**
     * Auto-resize textarea based on content
     */
    function autoResizeTextarea(textarea) {
        if (!textarea) return;
        
        // Reset height to auto to get the correct scrollHeight
        textarea.style.height = 'auto';
        
        // Set height based on scroll height, with min and max limits
        var newHeight = Math.max(150, Math.min(textarea.scrollHeight + 2, 600));
        textarea.style.height = newHeight + 'px';
    }
    
    /**
     * Capitalize first letter of string
     */
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

})(jQuery);