/**
 * Service Categories MAR - Admin JavaScript
 * 
 * Handles inline editing functionality for Service Categories
 */

(function($) {
    'use strict';
    
    var ServiceCategoriesMAR = {
        
        /**
         * Initialize
         */
        init: function() {
            $(document).ready(function() {
                ServiceCategoriesMAR.bindEvents();
            });
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Inline editing
            $(document).on('click', '.service-categories-table td.editable', this.startEdit);
            $(document).on('blur', '.service-categories-table td.editing input, .service-categories-table td.editing textarea', this.saveEdit);
            $(document).on('keypress', '.service-categories-table td.editing input', this.handleEnterKey);
            
            // Create new row
            $(document).on('click', '#create-new-row', this.createNewRow);
            
            // Delete row
            $(document).on('click', '.delete-row', this.deleteRow);
        },
        
        /**
         * Start inline editing
         */
        startEdit: function(e) {
            var $cell = $(this);
            
            // Don't edit if already editing
            if ($cell.hasClass('editing')) {
                return;
            }
            
            // Get current value
            var currentValue = $cell.text();
            var fieldName = $cell.data('field');
            
            // Mark as editing
            $cell.addClass('editing');
            
            // Determine input type based on field
            var inputHtml;
            if (fieldName === 'description' || fieldName === 'meta_description') {
                inputHtml = '<textarea>' + ServiceCategoriesMAR.escapeHtml(currentValue) + '</textarea>';
            } else {
                inputHtml = '<input type="text" value="' + ServiceCategoriesMAR.escapeHtml(currentValue) + '">';
            }
            
            // Replace content with input
            $cell.html(inputHtml);
            
            // Focus and select text
            var $input = $cell.find('input, textarea');
            $input.focus().select();
        },
        
        /**
         * Save inline edit
         */
        saveEdit: function(e) {
            var $input = $(this);
            var $cell = $input.closest('td');
            var $row = $cell.closest('tr');
            
            var newValue = $input.val();
            var oldValue = $input.is('textarea') ? $input.text() : $input.attr('value');
            var field = $cell.data('field');
            var id = $row.data('id');
            
            // Remove editing class
            $cell.removeClass('editing');
            
            // If value hasn't changed, just restore display
            if (newValue === oldValue) {
                $cell.text(newValue);
                return;
            }
            
            // Mark as saving
            $cell.addClass('saving');
            
            // Save via AJAX
            $.ajax({
                url: service_categories_mar_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'service_categories_mar_save_field',
                    nonce: service_categories_mar_ajax.nonce,
                    id: id,
                    field: field,
                    value: newValue
                },
                success: function(response) {
                    $cell.removeClass('saving');
                    
                    if (response.success) {
                        $cell.text(newValue);
                        $cell.addClass('success');
                        setTimeout(function() {
                            $cell.removeClass('success');
                        }, 1000);
                    } else {
                        $cell.text(oldValue);
                        $cell.addClass('error');
                        setTimeout(function() {
                            $cell.removeClass('error');
                        }, 1000);
                        console.error('Failed to save:', response.data);
                    }
                },
                error: function() {
                    $cell.removeClass('saving');
                    $cell.text(oldValue);
                    $cell.addClass('error');
                    setTimeout(function() {
                        $cell.removeClass('error');
                    }, 1000);
                }
            });
        },
        
        /**
         * Handle Enter key in input fields
         */
        handleEnterKey: function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $(this).blur();
            }
        },
        
        /**
         * Create new row
         */
        createNewRow: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $button.prop('disabled', true).text('Creating...');
            
            $.ajax({
                url: service_categories_mar_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'service_categories_mar_create_new',
                    nonce: service_categories_mar_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var newRow = response.data;
                        
                        // Check if we should reload
                        if (newRow && newRow.reload === true) {
                            location.reload();
                            return;
                        }
                        
                        // Check if newRow exists
                        if (!newRow) {
                            alert('Row created but no data returned');
                            location.reload();
                            return;
                        }
                        
                        var $tbody = $('.service-categories-table tbody');
                        
                        // Remove "no data" row if exists
                        $tbody.find('.no-data').closest('tr').remove();
                        
                        // Build new row HTML
                        var columns = $('.service-categories-table thead th').not(':last').map(function() {
                            return $(this).text();
                        }).get();
                        
                        // Get the ID field - the primary key is category_id
                        var rowId = newRow.category_id || newRow.id || newRow.ID || '';
                        
                        var rowHtml = '<tr data-id="' + rowId + '" class="new-row">';
                        
                        $.each(columns, function(index, column) {
                            var value = newRow[column] || '';
                            rowHtml += '<td class="editable" data-field="' + column + '">' + ServiceCategoriesMAR.escapeHtml(String(value)) + '</td>';
                        });
                        
                        rowHtml += '<td class="actions"><button class="delete-row button button-small">Delete</button></td>';
                        rowHtml += '</tr>';
                        
                        // Prepend new row
                        $tbody.prepend(rowHtml);
                        
                        // Remove highlight after animation
                        setTimeout(function() {
                            $tbody.find('tr.new-row').removeClass('new-row');
                        }, 2000);
                    } else {
                        alert('Failed to create new category: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Failed to create new category');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Create New (Inline)');
                }
            });
        },
        
        /**
         * Delete row
         */
        deleteRow: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this category?')) {
                return;
            }
            
            var $button = $(this);
            var $row = $button.closest('tr');
            var id = $row.data('id');
            
            $button.prop('disabled', true).text('Deleting...');
            
            $.ajax({
                url: service_categories_mar_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'service_categories_mar_delete_row',
                    nonce: service_categories_mar_ajax.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(400, function() {
                            $row.remove();
                            
                            // Check if table is empty
                            if ($('.service-categories-table tbody tr').length === 0) {
                                var colspan = $('.service-categories-table thead th').length;
                                $('.service-categories-table tbody').append(
                                    '<tr><td colspan="' + colspan + '" class="no-data">No categories found</td></tr>'
                                );
                            }
                        });
                    } else {
                        alert('Failed to delete: ' + (response.data || 'Unknown error'));
                        $button.prop('disabled', false).text('Delete');
                    }
                },
                error: function() {
                    alert('Failed to delete category');
                    $button.prop('disabled', false).text('Delete');
                }
            });
        },
        
        /**
         * Escape HTML
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
    
    // Initialize
    ServiceCategoriesMAR.init();
    
})(jQuery);