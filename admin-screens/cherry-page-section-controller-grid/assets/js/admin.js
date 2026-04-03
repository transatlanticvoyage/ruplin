/**
 * Cherry Page Section Controller Grid - Admin JavaScript
 *
 * Handles client-side functionality for Cherry Page Section Controller Grid
 */

(function($) {
    'use strict';

    // Cherry Controller Grid Handler
    var CherryControllerGrid = {

        changedFields: {},

        /**
         * Initialize
         */
        init: function() {
            // Initialize when DOM is ready
            $(document).ready(function() {
                CherryControllerGrid.bindEvents();
                CherryControllerGrid.setupInterface();
            });
        },

        /**
         * Setup initial interface
         */
        setupInterface: function() {
            this.addTableInteractivity();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Track changes in editable fields
            $(document).on('input', '.cherry-controller-grid .editable-field', this.handleFieldChange.bind(this));

            // Save button click
            $(document).on('click', '.cherry-controller-grid #save-changes', this.saveChanges.bind(this));

            // Add refresh button functionality if needed
            $(document).on('click', '.cherry-controller-grid .refresh-data', this.refreshTableData.bind(this));
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
            var $saveButton = $('.cherry-controller-grid #save-changes');

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
            var $status = $('.cherry-controller-grid #save-status');

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

            $.ajax({
                url: cherry_controller_grid_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cherry_controller_grid_save_changes',
                    nonce: cherry_controller_grid_ajax.nonce,
                    changes: changes
                },
                dataType: 'json'
            })
                .done(function(response) {
                    if (response.success) {
                        $status.text(response.data.message).css('color', 'green');

                        // Update original values for saved fields
                        for (var key in CherryControllerGrid.changedFields) {
                            var change = CherryControllerGrid.changedFields[key];
                            var $input = $('.cherry-controller-table tr[data-pylon-id="' + change.pylon_id + '"] .editable-field[data-field="' + change.field + '"]');
                            $input.data('original', change.value);
                        }

                        // Clear changed fields
                        CherryControllerGrid.changedFields = {};

                        // Reset button
                        $button.text('Save Changes');
                        CherryControllerGrid.updateSaveButton();

                        // Clear status after 3 seconds
                        setTimeout(function() {
                            $status.text('');
                        }, 3000);
                    } else {
                        var errMsg = (response.data && response.data.message) ? response.data.message : (typeof response.data === 'string' ? response.data : 'Error saving changes');
                        $status.text(errMsg).css('color', 'red');
                        $button.prop('disabled', false).text('Save Changes');
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    $status.text('Failed to save: ' + textStatus).css('color', 'red');
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
                        CherryControllerGrid.updateTable(response.data.data);
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
            var $tbody = $('.cherry-controller-table tbody');
            $tbody.empty();

            if (!data || data.length === 0) {
                $tbody.append('<tr><td colspan="13" class="no-data">No posts found with pylon_archetype = "servicepage"</td></tr>');
                return;
            }

            $.each(data, function(index, row) {
                var $tr = $('<tr>');
                $tr.append('<td>' + CherryControllerGrid.escapeHtml(row.post_id) + '</td>');
                $tr.append('<td>' + CherryControllerGrid.escapeHtml(row.post_title) + '</td>');
                $tr.append('<td>' + CherryControllerGrid.escapeHtml(row.post_name) + '</td>');
                $tr.append('<td>' + CherryControllerGrid.escapeHtml(row.post_status) + '</td>');
                $tr.append('<td>' + CherryControllerGrid.escapeHtml(row.pylon_id) + '</td>');
                $tr.append('<td>' + CherryControllerGrid.escapeHtml(row.rel_wp_post_id) + '</td>');
                $tr.append('<td>' + CherryControllerGrid.escapeHtml(row.pylon_archetype) + '</td>');
                $tbody.append($tr);
            });
        },

        /**
         * Handle AJAX requests
         */
        handleAjaxRequest: function(action, data) {
            return $.ajax({
                url: cherry_controller_grid_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cherry_controller_grid_' + action,
                    nonce: cherry_controller_grid_ajax.nonce,
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
    window.CherryControllerGrid = CherryControllerGrid;
    CherryControllerGrid.init();

})(jQuery);
