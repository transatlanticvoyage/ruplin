/**
 * Service Category Assigner MAR - Admin JavaScript
 * 
 * Handles client-side functionality for Service Category Assigner
 */

(function($) {
    'use strict';
    
    // Service Category Assigner MAR Handler
    var ServiceCategoryAssigner = {
        
        changedFields: {},
        
        /**
         * Initialize
         */
        init: function() {
            // Initialize when DOM is ready
            $(document).ready(function() {
                ServiceCategoryAssigner.bindEvents();
                ServiceCategoryAssigner.setupInterface();
            });
        },
        
        /**
         * Setup initial interface
         */
        setupInterface: function() {
            // Add any dynamic interface setup here
            this.addTableInteractivity();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Track changes in editable fields
            $(document).on('input', '.editable-field', this.handleFieldChange.bind(this));
            
            // Save button click
            $(document).on('click', '#save-changes', this.saveChanges.bind(this));
            
            // Add refresh button functionality if needed
            $(document).on('click', '.refresh-data', this.refreshTableData.bind(this));
        },
        
        /**
         * Add table interactivity
         */
        addTableInteractivity: function() {
            // Add sorting capabilities if needed in future
            // Add filtering capabilities if needed in future
        },
        
        /**
         * Handle field change
         */
        handleFieldChange: function(e) {
            var $input = $(e.target);
            var $row = $input.closest('tr');
            var pylonId = $row.data('pylon-id');
            var field = $input.data('field');
            var originalValue = $input.data('original');
            var currentValue = $input.val();
            
            var key = pylonId + '_' + field;
            
            if (currentValue !== originalValue) {
                // Track the change
                this.changedFields[key] = {
                    pylon_id: pylonId,
                    field: field,
                    value: currentValue,
                    original: originalValue
                };
            } else {
                // Remove from changed if value is back to original
                delete this.changedFields[key];
            }
            
            // Update save button state
            this.updateSaveButton();
        },
        
        /**
         * Update save button state
         */
        updateSaveButton: function() {
            var hasChanges = Object.keys(this.changedFields).length > 0;
            var $saveButton = $('#save-changes');
            
            if (hasChanges) {
                $saveButton.prop('disabled', false);
                $saveButton.removeClass('button-disabled');
            } else {
                $saveButton.prop('disabled', true);
                $saveButton.addClass('button-disabled');
            }
        },
        
        /**
         * Save changes
         */
        saveChanges: function(e) {
            e.preventDefault();
            
            var $button = $(e.target);
            var $status = $('#save-status');
            
            if (Object.keys(this.changedFields).length === 0) {
                return;
            }
            
            // Prepare changes array
            var changes = [];
            for (var key in this.changedFields) {
                changes.push(this.changedFields[key]);
            }
            
            // Disable button and show saving status
            $button.prop('disabled', true).text('Saving...');
            $status.text('Saving changes...').css('color', '#0073aa');
            
            // Send AJAX request — send changes at top level so PHP can read $_POST['changes']
            console.log('[SCA Debug] Sending changes:', changes);

            $.ajax({
                url: service_category_assigner_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'service_category_assigner_save_changes',
                    nonce: service_category_assigner_ajax.nonce,
                    changes: changes
                },
                dataType: 'json'
            })
                .done(function(response) {
                    console.log('[SCA Debug] Response:', response);
                    if (response.success) {
                        $status.text(response.data.message).css('color', 'green');

                        // Update original values for saved fields
                        for (var key in ServiceCategoryAssigner.changedFields) {
                            var change = ServiceCategoryAssigner.changedFields[key];
                            var $input = $('.service-category-table tr[data-pylon-id="' + change.pylon_id + '"] .editable-field[data-field="' + change.field + '"]');
                            $input.data('original', change.value);
                        }

                        // Clear changed fields
                        ServiceCategoryAssigner.changedFields = {};

                        // Reset button
                        $button.text('Save Changes');
                        ServiceCategoryAssigner.updateSaveButton();

                        // Clear status after 3 seconds
                        setTimeout(function() {
                            $status.text('');
                        }, 3000);
                    } else {
                        var errMsg = (response.data && response.data.message) ? response.data.message : (typeof response.data === 'string' ? response.data : 'Error saving changes');
                        $status.text(errMsg).css('color', 'red');
                        console.error('[SCA Debug] Save failed. response.data:', response.data);
                        $button.prop('disabled', false).text('Save Changes');
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('[SCA Debug] AJAX failed:', textStatus, errorThrown);
                    console.error('[SCA Debug] Response text:', jqXHR.responseText);
                    $status.text('Failed to save: ' + textStatus + ' — check console for details').css('color', 'red');
                    $button.prop('disabled', false).text('Save Changes');
                });
        },
        
        /**
         * Refresh table data via AJAX
         */
        refreshTableData: function(e) {
            if (e) e.preventDefault();
            
            var $button = $(e.currentTarget);
            $button.prop('disabled', true).text('Refreshing...');
            
            this.handleAjaxRequest('get_data', {})
                .done(function(response) {
                    if (response.success) {
                        ServiceCategoryAssigner.updateTable(response.data.data);
                    }
                })
                .fail(function() {
                    console.error('Failed to refresh data');
                })
                .always(function() {
                    $button.prop('disabled', false).text('Refresh');
                });
        },
        
        /**
         * Update table with new data
         */
        updateTable: function(data) {
            var $tbody = $('.service-category-table tbody');
            $tbody.empty();
            
            if (!data || data.length === 0) {
                $tbody.append('<tr><td colspan="7" class="no-data">No posts found with pylon_archetype = "servicepage"</td></tr>');
                return;
            }
            
            $.each(data, function(index, row) {
                var $tr = $('<tr>');
                $tr.append('<td>' + ServiceCategoryAssigner.escapeHtml(row.post_id) + '</td>');
                $tr.append('<td>' + ServiceCategoryAssigner.escapeHtml(row.post_title) + '</td>');
                $tr.append('<td>' + ServiceCategoryAssigner.escapeHtml(row.post_name) + '</td>');
                $tr.append('<td>' + ServiceCategoryAssigner.escapeHtml(row.post_status) + '</td>');
                $tr.append('<td>' + ServiceCategoryAssigner.escapeHtml(row.pylon_id) + '</td>');
                $tr.append('<td>' + ServiceCategoryAssigner.escapeHtml(row.rel_wp_post_id) + '</td>');
                $tr.append('<td>' + ServiceCategoryAssigner.escapeHtml(row.pylon_archetype) + '</td>');
                $tbody.append($tr);
            });
        },
        
        /**
         * Handle AJAX requests
         */
        handleAjaxRequest: function(action, data) {
            return $.ajax({
                url: service_category_assigner_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'service_category_assigner_' + action,
                    nonce: service_category_assigner_ajax.nonce,
                    data: data
                },
                dataType: 'json'
            });
        },
        
        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            if (text === null || text === undefined) {
                return '';
            }
            
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    // Initialize the module
    window.ServiceCategoryAssigner = ServiceCategoryAssigner;
    ServiceCategoryAssigner.init();
    
})(jQuery);