/**
 * Orbit Mar Admin JavaScript
 * Handles inline editing, pagination, and CRUD operations
 */

(function($) {
    'use strict';
    
    // State management
    var currentPage = 1;
    var perPage = 100;
    var totalPages = 1;
    var searchQuery = '';
    var selectedRows = [];
    
    // Initialize on document ready
    $(document).ready(function() {
        initializeOrbitMar();
    });
    
    function initializeOrbitMar() {
        loadTableData();
        bindEvents();
        syncPaginationControls();
    }
    
    // Load table data via AJAX
    function loadTableData() {
        var $table = $('#orbit-mar-table');
        $table.addClass('loading');
        
        $.ajax({
            url: orbitMarAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'orbit_mar_get_data',
                nonce: orbitMarAjax.nonce,
                page: currentPage,
                per_page: perPage,
                search: searchQuery
            },
            success: function(response) {
                if (response.success) {
                    renderTableRows(response.data.data);
                    updatePaginationButtons(response.data);
                    selectedRows = [];
                    $('#orbit-select-all').prop('checked', false);
                } else {
                    console.error('Failed to load data');
                }
            },
            error: function() {
                console.error('AJAX error');
            },
            complete: function() {
                $table.removeClass('loading');
            }
        });
    }
    
    // Render table rows
    function renderTableRows(data) {
        var $tbody = $('#orbit-mar-table tbody');
        $tbody.empty();
        
        if (data.length === 0) {
            $tbody.append(
                '<tr><td colspan="6" class="orbit-empty-state">' +
                '<h3>No records found</h3>' +
                '<p>Create a new record to get started.</p>' +
                '</td></tr>'
            );
            return;
        }
        
        $.each(data, function(index, row) {
            var $tr = $('<tr data-id="' + row.orbitpost_id + '">');
            
            // Checkbox cell
            $tr.append(
                '<td class="checkbox-column">' +
                '<div class="checkbox-cell">' +
                '<input type="checkbox" class="row-checkbox" value="' + row.orbitpost_id + '" />' +
                '</div>' +
                '</td>'
            );
            
            // ID cell (not editable)
            $tr.append('<td>' + row.orbitpost_id + '</td>');
            
            // rel_wp_post_id cell (editable)
            $tr.append(
                '<td class="editable" data-field="rel_wp_post_id">' +
                (row.rel_wp_post_id || '') +
                '</td>'
            );
            
            // Redshift datum cell (editable)
            $tr.append(
                '<td class="editable" data-field="redshift_datum">' +
                escapeHtml(row.redshift_datum || '') +
                '</td>'
            );
            
            // Timestamp cells (not editable)
            $tr.append('<td>' + formatDate(row.created_at) + '</td>');
            $tr.append('<td>' + formatDate(row.updated_at) + '</td>');
            
            $tbody.append($tr);
        });
    }
    
    // Bind all event handlers
    function bindEvents() {
        // Inline editing
        $(document).on('click', '.orbit-mar-table td.editable', function() {
            if ($(this).hasClass('editing')) {
                return;
            }
            startInlineEdit($(this));
        });
        
        // Save inline edit on Enter/blur
        $(document).on('keydown', '.orbit-mar-table td.editing textarea', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                saveInlineEdit($(this).parent());
            } else if (e.key === 'Escape') {
                cancelInlineEdit($(this).parent());
            }
        });
        
        $(document).on('blur', '.orbit-mar-table td.editing textarea', function() {
            saveInlineEdit($(this).parent());
        });
        
        // Checkbox handling
        $(document).on('change', '.row-checkbox', function() {
            var id = parseInt($(this).val());
            if ($(this).is(':checked')) {
                selectedRows.push(id);
                $(this).closest('tr').addClass('selected');
            } else {
                selectedRows = selectedRows.filter(function(rowId) {
                    return rowId !== id;
                });
                $(this).closest('tr').removeClass('selected');
            }
            updateSelectAllCheckbox();
        });
        
        // Select all checkbox
        $('#orbit-select-all').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.row-checkbox').prop('checked', isChecked);
            
            if (isChecked) {
                selectedRows = [];
                $('.row-checkbox').each(function() {
                    selectedRows.push(parseInt($(this).val()));
                    $(this).closest('tr').addClass('selected');
                });
            } else {
                selectedRows = [];
                $('.orbit-mar-table tbody tr').removeClass('selected');
            }
        });
        
        // Make entire checkbox cell clickable
        $(document).on('click', '.checkbox-cell', function(e) {
            if (e.target.type !== 'checkbox') {
                var $checkbox = $(this).find('input[type="checkbox"]');
                $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
            }
        });
        
        // Pagination controls
        $(document).on('click', '.orbit-mar-button-group button[data-perpage]', function() {
            var newPerPage = $(this).data('perpage');
            perPage = newPerPage === 'all' ? 'all' : parseInt(newPerPage);
            currentPage = 1;
            
            $('.orbit-mar-button-group button[data-perpage]').removeClass('active');
            $('button[data-perpage="' + newPerPage + '"]').addClass('active');
            
            loadTableData();
        });
        
        $(document).on('click', '.orbit-mar-button-group.page-numbers button', function() {
            currentPage = parseInt($(this).data('page'));
            loadTableData();
        });
        
        // Search functionality
        $('#orbit-search, #orbit-search-bottom').on('keyup', debounce(function() {
            searchQuery = $(this).val();
            currentPage = 1;
            syncSearchBoxes();
            loadTableData();
        }, 300));
        
        // Clear search
        $('#orbit-clear-search, #orbit-clear-search-bottom').on('click', function() {
            searchQuery = '';
            $('#orbit-search, #orbit-search-bottom').val('');
            currentPage = 1;
            loadTableData();
        });
        
        // Create new (inline)
        $('#orbit-create-inline').on('click', function() {
            createNewRowInline();
        });
        
        // Create new (popup)
        $('#orbit-create-popup').on('click', function() {
            openPopupModal();
        });
        
        // Delete selected
        $('#orbit-delete-selected').on('click', function() {
            deleteSelectedRows();
        });
        
        // Operation201 button
        $('#operation201-btn').on('click', function() {
            executeOperation201();
        });
        
        // Copy operation201 button text
        $('#copy-operation201-btn').on('click', function() {
            copyOperation201Text();
        });
        
        // Modal controls
        $('.orbit-modal-close, #orbit-popup-cancel').on('click', function() {
            closePopupModal();
        });
        
        $('#orbit-popup-save').on('click', function() {
            savePopupData();
        });
        
        // Close modal on outside click
        $('.orbit-modal').on('click', function(e) {
            if (e.target === this) {
                closePopupModal();
            }
        });
    }
    
    // Start inline editing
    function startInlineEdit($cell) {
        var currentValue = $cell.text();
        var field = $cell.data('field');
        
        $cell.addClass('editing');
        
        var $input = $('<textarea>').val(currentValue);
        $cell.html($input);
        $input.focus().select();
    }
    
    // Save inline edit
    function saveInlineEdit($cell) {
        var $input = $cell.find('textarea');
        if (!$input.length) return;
        
        var newValue = $input.val();
        var oldValue = $input.data('original') || '';
        var field = $cell.data('field');
        var rowId = $cell.closest('tr').data('id');
        
        // Remove editing state
        $cell.removeClass('editing');
        $cell.text(newValue);
        
        // Save to database
        $.ajax({
            url: orbitMarAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'orbit_mar_save_row',
                nonce: orbitMarAjax.nonce,
                id: rowId,
                field: field,
                value: newValue
            },
            success: function(response) {
                if (response.success) {
                    // Update timestamp
                    var $row = $cell.closest('tr');
                    $row.find('td:last').text(formatDate(response.data.updated_at));
                } else {
                    alert('Failed to save: ' + response.data);
                    $cell.text(oldValue);
                }
            },
            error: function() {
                alert('Error saving data');
                $cell.text(oldValue);
            }
        });
    }
    
    // Cancel inline edit
    function cancelInlineEdit($cell) {
        var $input = $cell.find('textarea');
        var originalValue = $input.data('original') || '';
        
        $cell.removeClass('editing');
        $cell.text(originalValue);
    }
    
    // Create new row inline
    function createNewRowInline() {
        $.ajax({
            url: orbitMarAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'orbit_mar_create_row',
                nonce: orbitMarAjax.nonce,
                redshift_datum: ''
            },
            success: function(response) {
                if (response.success) {
                    // Add new row to top of table
                    var row = response.data;
                    var $tbody = $('#orbit-mar-table tbody');
                    
                    // Remove empty state if exists
                    $tbody.find('.orbit-empty-state').closest('tr').remove();
                    
                    var $tr = $('<tr data-id="' + row.orbitpost_id + '" class="new-row">');
                    
                    $tr.append(
                        '<td class="checkbox-column">' +
                        '<div class="checkbox-cell">' +
                        '<input type="checkbox" class="row-checkbox" value="' + row.orbitpost_id + '" />' +
                        '</div>' +
                        '</td>'
                    );
                    
                    $tr.append('<td>' + row.orbitpost_id + '</td>');
                    $tr.append(
                        '<td class="editable" data-field="rel_wp_post_id">' +
                        (row.rel_wp_post_id || '') +
                        '</td>'
                    );
                    $tr.append(
                        '<td class="editable" data-field="redshift_datum">' +
                        escapeHtml(row.redshift_datum || '') +
                        '</td>'
                    );
                    $tr.append('<td>' + formatDate(row.created_at) + '</td>');
                    $tr.append('<td>' + formatDate(row.updated_at) + '</td>');
                    
                    $tbody.prepend($tr);
                    
                    // Start editing the redshift_datum field immediately
                    setTimeout(function() {
                        $tr.find('td.editable').click();
                    }, 100);
                    
                    // Remove highlight after animation
                    setTimeout(function() {
                        $tr.removeClass('new-row');
                    }, 2000);
                }
            }
        });
    }
    
    // Open popup modal
    function openPopupModal(rowData) {
        var $modal = $('#orbit-popup-modal');
        
        if (rowData) {
            // Edit existing
            $('#popup-orbitpost-id').val(rowData.orbitpost_id);
            $('#popup-redshift-datum').val(rowData.redshift_datum);
            $('#popup-created-at').text(formatDate(rowData.created_at));
            $('#popup-updated-at').text(formatDate(rowData.updated_at));
            $('.orbit-modal-header h2').text('Edit Orbit Post #' + rowData.orbitpost_id);
        } else {
            // Create new
            $('#popup-orbitpost-id').val('');
            $('#popup-redshift-datum').val('');
            $('#popup-created-at').text('Will be set on save');
            $('#popup-updated-at').text('Will be set on save');
            $('.orbit-modal-header h2').text('Create New Orbit Post');
        }
        
        $modal.fadeIn(200);
    }
    
    // Close popup modal
    function closePopupModal() {
        $('#orbit-popup-modal').fadeOut(200);
    }
    
    // Save popup data
    function savePopupData() {
        var id = $('#popup-orbitpost-id').val();
        var redshift_datum = $('#popup-redshift-datum').val();
        
        if (id) {
            // Update existing
            $.ajax({
                url: orbitMarAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'orbit_mar_save_row',
                    nonce: orbitMarAjax.nonce,
                    id: id,
                    field: 'redshift_datum',
                    value: redshift_datum
                },
                success: function(response) {
                    if (response.success) {
                        closePopupModal();
                        loadTableData();
                    } else {
                        alert('Failed to save: ' + response.data);
                    }
                }
            });
        } else {
            // Create new
            $.ajax({
                url: orbitMarAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'orbit_mar_create_row',
                    nonce: orbitMarAjax.nonce,
                    redshift_datum: redshift_datum
                },
                success: function(response) {
                    if (response.success) {
                        closePopupModal();
                        loadTableData();
                    } else {
                        alert('Failed to create: ' + response.data);
                    }
                }
            });
        }
    }
    
    // Delete selected rows
    function deleteSelectedRows() {
        if (selectedRows.length === 0) {
            alert('Please select rows to delete');
            return;
        }
        
        if (!confirm('Are you sure you want to delete ' + selectedRows.length + ' row(s)?')) {
            return;
        }
        
        $.ajax({
            url: orbitMarAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'orbit_mar_delete_rows',
                nonce: orbitMarAjax.nonce,
                ids: selectedRows
            },
            success: function(response) {
                if (response.success) {
                    loadTableData();
                } else {
                    alert('Failed to delete: ' + response.data);
                }
            }
        });
    }
    
    // Update pagination buttons
    function updatePaginationButtons(data) {
        totalPages = data.total_pages;
        currentPage = data.current_page;
        
        // Update page number buttons
        var $pageButtons = $('.page-numbers, .page-numbers-bottom');
        $pageButtons.empty();
        
        // Calculate page range to show
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, currentPage + 2);
        
        // First page
        if (startPage > 1) {
            $pageButtons.append('<button data-page="1">1</button>');
            if (startPage > 2) {
                $pageButtons.append('<button disabled>...</button>');
            }
        }
        
        // Page range
        for (var i = startPage; i <= endPage; i++) {
            var $btn = $('<button data-page="' + i + '">' + i + '</button>');
            if (i === currentPage) {
                $btn.addClass('active');
            }
            $pageButtons.append($btn);
        }
        
        // Last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                $pageButtons.append('<button disabled>...</button>');
            }
            $pageButtons.append('<button data-page="' + totalPages + '">' + totalPages + '</button>');
        }
    }
    
    // Sync pagination controls
    function syncPaginationControls() {
        // Sync per-page buttons
        $('.orbit-mar-button-group button[data-perpage]').on('click', function() {
            var value = $(this).data('perpage');
            $('.orbit-mar-button-group button[data-perpage="' + value + '"]').addClass('active');
        });
    }
    
    // Sync search boxes
    function syncSearchBoxes() {
        $('#orbit-search, #orbit-search-bottom').val(searchQuery);
    }
    
    // Update select all checkbox state
    function updateSelectAllCheckbox() {
        var totalCheckboxes = $('.row-checkbox').length;
        var checkedCheckboxes = $('.row-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#orbit-select-all').prop('checked', false).prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#orbit-select-all').prop('checked', true).prop('indeterminate', false);
        } else {
            $('#orbit-select-all').prop('checked', false).prop('indeterminate', true);
        }
    }
    
    // Utility: Escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Utility: Format date
    function formatDate(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        return date.toLocaleString();
    }
    
    // Utility: Debounce
    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }
    
    // Execute Operation201
    function executeOperation201() {
        if (!confirm('Are you sure you want to run Operation201?\n\nThis will create orbitpost records for all published WordPress posts and pages that don\'t already have one.')) {
            return;
        }
        
        var $button = $('#operation201-btn');
        var originalText = $button.text();
        $button.prop('disabled', true).text('Running Operation201...');
        
        $.ajax({
            url: orbitMarAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'orbit_mar_operation201',
                nonce: orbitMarAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Operation201 Results:\n\n' + response.data.message);
                    // Refresh the table to show new records
                    loadTableData();
                } else {
                    alert('Operation201 failed: ' + response.data);
                }
            },
            error: function() {
                alert('Error executing Operation201');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    }
    
    // Copy Operation201 text to clipboard
    function copyOperation201Text() {
        var text = 'operation201 - populate _orbitposts db table for any missing wp_posts';
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                // Visual feedback
                var $button = $('#copy-operation201-btn');
                var originalText = $button.text();
                $button.text('✓').css('background', 'green');
                
                setTimeout(function() {
                    $button.text(originalText).css('background', '#666');
                }, 1000);
            }).catch(function() {
                fallbackCopyText(text);
            });
        } else {
            fallbackCopyText(text);
        }
    }
    
    // Fallback copy method for older browsers
    function fallbackCopyText(text) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            // Visual feedback
            var $button = $('#copy-operation201-btn');
            var originalText = $button.text();
            $button.text('✓').css('background', 'green');
            
            setTimeout(function() {
                $button.text(originalText).css('background', '#666');
            }, 1000);
        } catch (err) {
            alert('Copy failed. Please manually copy: ' + text);
        }
        
        document.body.removeChild(textArea);
    }
    
})(jQuery);