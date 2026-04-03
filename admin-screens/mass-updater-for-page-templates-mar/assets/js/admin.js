/**
 * Mass Updater For Page Templates MAR - Admin JavaScript
 *
 * Handles client-side functionality for Mass Updater For Page Templates
 */

(function($) {
    'use strict';

    var MassUpdaterPageTemplates = {

        changedFields: {},

        init: function() {
            $(document).ready(function() {
                MassUpdaterPageTemplates.bindEvents();
                MassUpdaterPageTemplates.setupInterface();
            });
        },

        setupInterface: function() {
            this.addTableInteractivity();
        },

        bindEvents: function() {
            $(document).on('input', '.mupt-editable-field', this.handleFieldChange.bind(this));
            $(document).on('click', '#mupt-save-changes', this.saveChanges.bind(this));
            $(document).on('click', '.mupt-refresh-data', this.refreshTableData.bind(this));

            // Bulk action buttons
            $(document).on('click', '#mupt-fill-blogpost-bilberry', function() { MassUpdaterPageTemplates.executeBulkAction('fill_blogpost_bilberry', this); });
            $(document).on('click', '#mupt-misc-archetypes-bilberry', function() { MassUpdaterPageTemplates.executeBulkAction('misc_archetypes_bilberry', this); });
            $(document).on('click', '#mupt-all-header1', function() { MassUpdaterPageTemplates.executeBulkAction('all_header1', this); });
            $(document).on('click', '#mupt-all-header2', function() { MassUpdaterPageTemplates.executeBulkAction('all_header2', this); });
            $(document).on('click', '#mupt-servicepage-locationpage-cherry', function() { MassUpdaterPageTemplates.executeBulkAction('servicepage_locationpage_cherry', this); });
        },

        addTableInteractivity: function() {
        },

        executeBulkAction: function(actionName, btnEl) {
            var $button = $(btnEl);
            var originalText = $button.text();
            var $status = $('#mupt-save-status');

            $button.prop('disabled', true).text('Processing...');
            $status.text('Running bulk update...').css('color', '#0073aa');

            $.ajax({
                url: mupt_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mass_updater_page_templates_bulk_update',
                    nonce: mupt_ajax.nonce,
                    bulk_action: actionName
                },
                dataType: 'json'
            })
                .done(function(response) {
                    if (response.success) {
                        $status.text(response.data.message).css('color', 'green');
                        // Reload page to reflect changes in the table
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        var errMsg = (response.data && response.data.message) ? response.data.message : 'Bulk update failed';
                        $status.text(errMsg).css('color', 'red');
                        $button.prop('disabled', false).text(originalText);
                    }
                })
                .fail(function(jqXHR, textStatus) {
                    $status.text('Failed: ' + textStatus).css('color', 'red');
                    $button.prop('disabled', false).text(originalText);
                });
        },

        handleFieldChange: function(e) {
            var $input = $(e.target);
            var $row = $input.closest('tr');
            var pylonId = $row.data('pylon-id');
            var field = $input.data('field');
            var originalValue = $input.data('original');
            var currentValue = $input.val();

            var key = pylonId + '_' + field;

            if (currentValue !== originalValue) {
                this.changedFields[key] = {
                    pylon_id: pylonId,
                    field: field,
                    value: currentValue,
                    original: originalValue
                };
            } else {
                delete this.changedFields[key];
            }

            this.updateSaveButton();
        },

        updateSaveButton: function() {
            var hasChanges = Object.keys(this.changedFields).length > 0;
            var $saveButton = $('#mupt-save-changes');

            if (hasChanges) {
                $saveButton.prop('disabled', false);
                $saveButton.removeClass('button-disabled');
            } else {
                $saveButton.prop('disabled', true);
                $saveButton.addClass('button-disabled');
            }
        },

        saveChanges: function(e) {
            e.preventDefault();

            var $button = $(e.target);
            var $status = $('#mupt-save-status');

            if (Object.keys(this.changedFields).length === 0) {
                return;
            }

            var changes = [];
            for (var key in this.changedFields) {
                changes.push(this.changedFields[key]);
            }

            $button.prop('disabled', true).text('Saving...');
            $status.text('Saving changes...').css('color', '#0073aa');

            $.ajax({
                url: mupt_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mass_updater_page_templates_save_changes',
                    nonce: mupt_ajax.nonce,
                    changes: changes
                },
                dataType: 'json'
            })
                .done(function(response) {
                    if (response.success) {
                        $status.text(response.data.message).css('color', 'green');

                        for (var key in MassUpdaterPageTemplates.changedFields) {
                            var change = MassUpdaterPageTemplates.changedFields[key];
                            var $input = $('.mupt-table tr[data-pylon-id="' + change.pylon_id + '"] .mupt-editable-field[data-field="' + change.field + '"]');
                            $input.data('original', change.value);
                        }

                        MassUpdaterPageTemplates.changedFields = {};
                        $button.text('Save Changes');
                        MassUpdaterPageTemplates.updateSaveButton();

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

        refreshTableData: function(e) {
            if (e) e.preventDefault();

            var $button = $(e.currentTarget);
            $button.prop('disabled', true).text('Refreshing...');

            this.handleAjaxRequest('get_data', {})
                .done(function(response) {
                    if (response.success) {
                        MassUpdaterPageTemplates.updateTable(response.data.data);
                    }
                })
                .fail(function() {
                    console.error('Failed to refresh data');
                })
                .always(function() {
                    $button.prop('disabled', false).text('Refresh');
                });
        },

        updateTable: function(data) {
            var $tbody = $('.mupt-table tbody');
            $tbody.empty();

            if (!data || data.length === 0) {
                $tbody.append('<tr><td colspan="13" class="no-data">No posts found with pylon_archetype = "servicepage"</td></tr>');
                return;
            }

            $.each(data, function(index, row) {
                var $tr = $('<tr>');
                $tr.append('<td>' + MassUpdaterPageTemplates.escapeHtml(row.post_id) + '</td>');
                $tr.append('<td>' + MassUpdaterPageTemplates.escapeHtml(row.post_title) + '</td>');
                $tr.append('<td>' + MassUpdaterPageTemplates.escapeHtml(row.post_name) + '</td>');
                $tr.append('<td>' + MassUpdaterPageTemplates.escapeHtml(row.post_status) + '</td>');
                $tr.append('<td>' + MassUpdaterPageTemplates.escapeHtml(row.pylon_id) + '</td>');
                $tr.append('<td>' + MassUpdaterPageTemplates.escapeHtml(row.rel_wp_post_id) + '</td>');
                $tr.append('<td>' + MassUpdaterPageTemplates.escapeHtml(row.pylon_archetype) + '</td>');
                $tbody.append($tr);
            });
        },

        handleAjaxRequest: function(action, data) {
            return $.ajax({
                url: mupt_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mass_updater_page_templates_' + action,
                    nonce: mupt_ajax.nonce,
                    data: data
                },
                dataType: 'json'
            });
        },

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

    window.MassUpdaterPageTemplates = MassUpdaterPageTemplates;
    MassUpdaterPageTemplates.init();

})(jQuery);
