/**
 * Cherry Page Section Controller Grid - Admin JavaScript
 *
 * Handles client-side functionality for Cherry Page Section Controller Grid
 * Includes Rocket Chamber pagination, search, and wolf exclusion band (sticky columns)
 */

(function($) {
    'use strict';

    // Cherry Controller Grid Handler
    var CherryControllerGrid = {

        changedFields: {},

        // PAGINATION STATE (Rocket Chamber)
        currentRowsPerPage: 10,
        currentColsPerPage: 4,
        currentRowPage: 1,
        currentColPage: 1,
        totalRowPages: 1,
        totalColPages: 1,
        totalDataRows: 0,
        totalDataCols: 0,
        allRows: [],       // all <tr> from tbody
        filteredRows: [],   // after search filter

        // Wolf Exclusion Band: sticky columns always visible (tools, post_id, post_title)
        wolfBandIndices: [0, 1, 2],
        wolfBandCount: 3,

        /**
         * Initialize
         */
        init: function() {
            $(document).ready(function() {
                CherryControllerGrid.bindEvents();
                CherryControllerGrid.setupInterface();
            });
        },

        /**
         * Setup initial interface
         */
        setupInterface: function() {
            // Capture all rows from the server-rendered table
            this.allRows = $('#cherry-controller-table tbody tr').toArray();
            this.filteredRows = this.allRows.slice();

            // Initial counts
            this.totalDataRows = this.filteredRows.length;
            var totalTableCols = $('#cherry-controller-table thead th').length;
            this.totalDataCols = Math.max(0, totalTableCols - this.wolfBandCount);

            // Apply initial pagination
            this.applyPagination();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Existing: Track changes in text editable fields
            $(document).on('input', '.cherry-controller-grid input.editable-field[type="text"]', this.handleFieldChange.bind(this));
            // Track changes in toggle (checkbox) fields
            $(document).on('change', '.cherry-controller-grid input.ccg-toggle-input', this.handleToggleChange.bind(this));
            $(document).on('click', '.cherry-controller-grid #save-changes', this.saveChanges.bind(this));

            // Search
            $(document).on('input', '#ccg-search', this.handleSearch.bind(this));

            // Row pagination buttons
            $(document).on('click', '.ccg-rows-per-page-btn', this.handleRowsPerPageClick.bind(this));
            $(document).on('click', '#ccg-first-row-page', function() { CherryControllerGrid.currentRowPage = 1; CherryControllerGrid.applyPagination(); });
            $(document).on('click', '#ccg-prev-row-page', function() {
                if (CherryControllerGrid.currentRowPage > 1) { CherryControllerGrid.currentRowPage--; } else { CherryControllerGrid.currentRowPage = CherryControllerGrid.totalRowPages; }
                CherryControllerGrid.applyPagination();
            });
            $(document).on('click', '#ccg-next-row-page', function() {
                if (CherryControllerGrid.currentRowPage < CherryControllerGrid.totalRowPages) { CherryControllerGrid.currentRowPage++; } else { CherryControllerGrid.currentRowPage = 1; }
                CherryControllerGrid.applyPagination();
            });
            $(document).on('click', '#ccg-last-row-page', function() { CherryControllerGrid.currentRowPage = CherryControllerGrid.totalRowPages; CherryControllerGrid.applyPagination(); });

            // Column pagination buttons
            $(document).on('click', '.ccg-cols-per-page-btn', this.handleColsPerPageClick.bind(this));
            $(document).on('click', '#ccg-first-col-page', function() { CherryControllerGrid.currentColPage = 1; CherryControllerGrid.applyPagination(); });
            $(document).on('click', '#ccg-prev-col-page', function() {
                if (CherryControllerGrid.currentColPage > 1) { CherryControllerGrid.currentColPage--; } else { CherryControllerGrid.currentColPage = CherryControllerGrid.totalColPages; }
                CherryControllerGrid.applyPagination();
            });
            $(document).on('click', '#ccg-next-col-page', function() {
                if (CherryControllerGrid.currentColPage < CherryControllerGrid.totalColPages) { CherryControllerGrid.currentColPage++; } else { CherryControllerGrid.currentColPage = 1; }
                CherryControllerGrid.applyPagination();
            });
            $(document).on('click', '#ccg-last-col-page', function() { CherryControllerGrid.currentColPage = CherryControllerGrid.totalColPages; CherryControllerGrid.applyPagination(); });
        },

        // ===================== SEARCH =====================

        handleSearch: function(e) {
            var searchTerm = $(e.target).val().toLowerCase();
            if (searchTerm === '') {
                this.filteredRows = this.allRows.slice();
            } else {
                this.filteredRows = this.allRows.filter(function(tr) {
                    return $(tr).text().toLowerCase().indexOf(searchTerm) !== -1;
                });
            }
            this.currentRowPage = 1;
            this.applyPagination();
        },

        // ===================== PAGINATION =====================

        calculatePagination: function() {
            this.totalDataRows = this.filteredRows.length;
            var totalTableCols = $('#cherry-controller-table thead th').length;
            this.totalDataCols = Math.max(0, totalTableCols - this.wolfBandCount);

            if (this.currentRowsPerPage === 'all') {
                this.totalRowPages = 1;
            } else {
                this.totalRowPages = Math.max(1, Math.ceil(this.totalDataRows / this.currentRowsPerPage));
            }

            if (this.currentColsPerPage === 'all') {
                this.totalColPages = 1;
            } else {
                this.totalColPages = Math.max(1, Math.ceil(this.totalDataCols / this.currentColsPerPage));
            }

            this.currentRowPage = Math.min(this.currentRowPage, Math.max(1, this.totalRowPages));
            this.currentColPage = Math.min(this.currentColPage, Math.max(1, this.totalColPages));
        },

        updatePaginationDisplays: function() {
            this.calculatePagination();

            $('#ccg-current-row-page').text(this.currentRowPage);
            $('#ccg-total-row-pages').text(this.totalRowPages);
            $('#ccg-current-col-page').text(this.currentColPage);
            $('#ccg-total-col-pages').text(this.totalColPages);

            // Showing counts
            var rowStart = 0;
            var rowEnd = this.totalDataRows;
            if (this.currentRowsPerPage !== 'all') {
                rowStart = (this.currentRowPage - 1) * this.currentRowsPerPage;
                rowEnd = Math.min(rowStart + this.currentRowsPerPage, this.totalDataRows);
            }
            $('#ccg-services-showing').text(rowEnd - rowStart);
            $('#ccg-services-total').text(this.totalDataRows);

            var colsShowing = this.currentColsPerPage === 'all' ? this.totalDataCols : Math.min(this.currentColsPerPage, this.totalDataCols);
            $('#ccg-columns-showing').text(colsShowing);
            $('#ccg-columns-total').text(this.totalDataCols);

            // Button states
            $('#ccg-first-row-page').prop('disabled', this.currentRowPage <= 1);
            $('#ccg-last-row-page').prop('disabled', this.currentRowPage >= this.totalRowPages);
            $('#ccg-first-col-page').prop('disabled', this.currentColPage <= 1);
            $('#ccg-last-col-page').prop('disabled', this.currentColPage >= this.totalColPages);
        },

        applyPagination: function() {
            this.calculatePagination();

            var startRow = 0;
            var endRow = this.totalDataRows;
            if (this.currentRowsPerPage !== 'all') {
                startRow = (this.currentRowPage - 1) * this.currentRowsPerPage;
                endRow = Math.min(startRow + this.currentRowsPerPage, this.totalDataRows);
            }

            // Show/hide rows
            var $tbody = $('#cherry-controller-table tbody');
            $tbody.empty();
            for (var i = 0; i < this.filteredRows.length; i++) {
                if (i >= startRow && i < endRow) {
                    $tbody.append(this.filteredRows[i]);
                }
            }

            // Apply column pagination
            this.applyColumnPagination();

            // Update displays
            this.updatePaginationDisplays();
        },

        applyColumnPagination: function() {
            var self = this;
            var wolfBandIndices = this.wolfBandIndices;

            if (this.currentColsPerPage !== 'all') {
                var startCol = this.wolfBandCount + (this.currentColPage - 1) * this.currentColsPerPage;
                var endCol = startCol + this.currentColsPerPage - 1;

                $('#cherry-controller-table thead tr').each(function() {
                    $(this).find('th').each(function(index) {
                        if (wolfBandIndices.indexOf(index) !== -1) {
                            $(this).show().addClass('wolf-exclusion-band');
                        } else if (index >= startCol && index <= endCol) {
                            $(this).show().removeClass('wolf-exclusion-band');
                        } else {
                            $(this).hide().removeClass('wolf-exclusion-band');
                        }
                    });
                });

                $('#cherry-controller-table tbody tr').each(function() {
                    $(this).find('td').each(function(index) {
                        if (wolfBandIndices.indexOf(index) !== -1) {
                            $(this).show().addClass('wolf-exclusion-band');
                        } else if (index >= startCol && index <= endCol) {
                            $(this).show().removeClass('wolf-exclusion-band');
                        } else {
                            $(this).hide().removeClass('wolf-exclusion-band');
                        }
                    });
                });
            } else {
                // Show all columns
                $('#cherry-controller-table thead tr').each(function() {
                    $(this).find('th').each(function(index) {
                        $(this).show();
                        if (wolfBandIndices.indexOf(index) !== -1) {
                            $(this).addClass('wolf-exclusion-band');
                        } else {
                            $(this).removeClass('wolf-exclusion-band');
                        }
                    });
                });

                $('#cherry-controller-table tbody tr').each(function() {
                    $(this).find('td').each(function(index) {
                        $(this).show();
                        if (wolfBandIndices.indexOf(index) !== -1) {
                            $(this).addClass('wolf-exclusion-band');
                        } else {
                            $(this).removeClass('wolf-exclusion-band');
                        }
                    });
                });
            }
        },

        handleRowsPerPageClick: function(e) {
            var $btn = $(e.currentTarget);
            $('.ccg-rows-per-page-btn').removeClass('active').css({
                'background': 'white', 'color': 'black', 'border': '1px solid #D1D5DB'
            });
            $btn.addClass('active').css({
                'background': '#3B82F6', 'color': 'white', 'border': '1px solid #3B82F6'
            });

            var val = $btn.data('rows');
            this.currentRowsPerPage = (val === 'all') ? 'all' : parseInt(val);
            this.currentRowPage = 1;
            this.applyPagination();
        },

        handleColsPerPageClick: function(e) {
            var $btn = $(e.currentTarget);
            $('.ccg-cols-per-page-btn').removeClass('active').css({
                'background': 'white', 'color': 'black', 'border': '1px solid #000'
            });
            $btn.addClass('active').css({
                'background': '#f8f782', 'color': 'black', 'border': '1px solid #000'
            });

            var val = $btn.data('cols');
            this.currentColsPerPage = (val === 'all') ? 'all' : parseInt(val);
            this.currentColPage = 1;
            this.applyPagination();
        },

        // ===================== FIELD EDITING =====================

        handleToggleChange: function(e) {
            var $input = $(e.target);
            var $row = $input.closest('tr');
            var pylonId = $row.data('pylon-id');
            var field = $input.data('field');
            var originalValue = parseInt($input.data('original'));
            var currentValue = $input.is(':checked') ? 1 : 0;

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
            var $saveButton = $('.cherry-controller-grid #save-changes');

            if (hasChanges) {
                $saveButton.prop('disabled', false).removeClass('button-disabled');
            } else {
                $saveButton.prop('disabled', true).addClass('button-disabled');
            }
        },

        saveChanges: function(e) {
            e.preventDefault();

            var $button = $(e.target);
            var $status = $('.cherry-controller-grid #save-status');

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

                        for (var key in CherryControllerGrid.changedFields) {
                            var change = CherryControllerGrid.changedFields[key];
                            var $input = $('.cherry-controller-table tr[data-pylon-id="' + change.pylon_id + '"] .editable-field[data-field="' + change.field + '"]');
                            if ($input.is(':checkbox')) {
                                $input.data('original', change.value);
                            } else {
                                $input.data('original', change.value);
                            }
                        }

                        CherryControllerGrid.changedFields = {};
                        $button.text('Save Changes');
                        CherryControllerGrid.updateSaveButton();

                        setTimeout(function() { $status.text(''); }, 3000);
                    } else {
                        var errMsg = (response.data && response.data.message) ? response.data.message : (typeof response.data === 'string' ? response.data : 'Error saving changes');
                        $status.text(errMsg).css('color', 'red');
                        $button.prop('disabled', false).text('Save Changes');
                    }
                })
                .fail(function(jqXHR, textStatus) {
                    $status.text('Failed to save: ' + textStatus).css('color', 'red');
                    $button.prop('disabled', false).text('Save Changes');
                });
        },

        escapeHtml: function(text) {
            if (text === null || text === undefined) { return ''; }
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    window.CherryControllerGrid = CherryControllerGrid;
    CherryControllerGrid.init();

})(jQuery);
