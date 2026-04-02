/**
 * Ruplin Admin Screens Searcher - Admin JavaScript
 * 
 * Handles client-side functionality for Admin Screens Searcher
 */

(function($) {
    'use strict';
    
    // Ruplin Admin Screens Searcher Handler
    var RuplinAdminScreensSearcher = {
        
        screens: [],
        filteredScreens: [],
        
        /**
         * Initialize
         */
        init: function() {
            // Initialize when DOM is ready
            $(document).ready(function() {
                RuplinAdminScreensSearcher.bindEvents();
                RuplinAdminScreensSearcher.loadCachedScreens();
            });
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Scan button click
            $('#ruplin-scan-screens').on('click', this.handleScan.bind(this));
            
            // Search input
            $('#ruplin-screen-search').on('keyup', this.handleSearch.bind(this));
            
            // Clear search on ESC
            $('#ruplin-screen-search').on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC key
                    $(this).val('');
                    RuplinAdminScreensSearcher.handleSearch();
                }
            });
        },
        
        /**
         * Load cached screens on page load
         */
        loadCachedScreens: function() {
            var self = this;
            
            $.ajax({
                url: ruplin_admin_screens_searcher_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ruplin_get_admin_screens',
                    nonce: ruplin_admin_screens_searcher_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.screens.length > 0) {
                        self.screens = response.data.screens;
                        self.filteredScreens = response.data.screens;
                        self.renderTable();
                        self.updateScreenCount();
                        
                        if (response.data.last_scan && response.data.last_scan !== 'Never') {
                            $('.scan-status').text('Last scan: ' + response.data.last_scan).addClass('active');
                        }
                    }
                },
                error: function() {
                    console.error('Failed to load cached screens');
                }
            });
        },
        
        /**
         * Handle scan button click
         */
        handleScan: function(e) {
            e.preventDefault();
            
            var self = this;
            var $button = $('#ruplin-scan-screens');
            var $status = $('.scan-status');
            
            // Disable button and show loading
            $button.prop('disabled', true).text('Scanning...');
            $status.text('Scanning for admin screens...').addClass('active');
            
            $.ajax({
                url: ruplin_admin_screens_searcher_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ruplin_scan_admin_screens',
                    nonce: ruplin_admin_screens_searcher_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.screens = response.data.screens;
                        self.filteredScreens = response.data.screens;
                        self.renderTable();
                        self.updateScreenCount();
                        
                        $status.text('Scan complete! Found ' + response.data.count + ' screens. Last scan: ' + response.data.last_scan);
                        
                        setTimeout(function() {
                            $status.text('Last scan: ' + response.data.last_scan);
                        }, 3000);
                    } else {
                        $status.text('Scan failed. Please try again.').addClass('active');
                    }
                },
                error: function() {
                    $status.text('Error during scan. Please check console.').addClass('active');
                    console.error('Scan failed');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Scan for Admin Screens');
                }
            });
        },
        
        /**
         * Handle search input
         */
        handleSearch: function() {
            var searchTerm = $('#ruplin-screen-search').val().toLowerCase();
            
            if (searchTerm === '') {
                this.filteredScreens = this.screens;
            } else {
                this.filteredScreens = this.screens.filter(function(screen) {
                    return screen.slug.toLowerCase().indexOf(searchTerm) !== -1 ||
                           screen.menu_title.toLowerCase().indexOf(searchTerm) !== -1 ||
                           screen.page_title.toLowerCase().indexOf(searchTerm) !== -1;
                });
            }
            
            this.renderTable();
            this.updateScreenCount();
        },
        
        /**
         * Render the screens table
         */
        renderTable: function() {
            var $tbody = $('#ruplin-screens-table tbody');
            $tbody.empty();
            
            if (this.filteredScreens.length === 0) {
                $tbody.append(
                    '<tr>' +
                    '<td colspan="7" class="no-items">' +
                    (this.screens.length === 0 ? 'Click "Scan for Admin Screens" to populate the table.' : 'No screens match your search.') +
                    '</td>' +
                    '</tr>'
                );
                return;
            }
            
            var self = this;
            this.filteredScreens.forEach(function(screen, index) {
                var rowClass = '';
                if (screen.menu_title === '(Unregistered)' || screen.menu_title === '(Potential)') {
                    rowClass = 'unregistered';
                }
                
                var row = '<tr class="' + rowClass + '">' +
                    '<td>' + screen.id + '</td>' +
                    '<td>' + self.escapeHtml(screen.menu_title) + '</td>' +
                    '<td>' + self.escapeHtml(screen.page_title) + '</td>' +
                    '<td><code>' + self.escapeHtml(screen.slug) + '</code></td>' +
                    '<td><span class="parent-badge">' + self.escapeHtml(screen.parent) + '</span></td>' +
                    '<td><span class="file-path">' + self.escapeHtml(screen.file_path) + '</span></td>' +
                    '<td>' +
                    '<a href="' + screen.url + '" class="action-button view" target="_blank">View</a>' +
                    '</td>' +
                    '</tr>';
                
                $tbody.append(row);
            });
        },
        
        /**
         * Update screen count display
         */
        updateScreenCount: function() {
            var countText = '';
            
            if (this.screens.length > 0) {
                if (this.filteredScreens.length === this.screens.length) {
                    countText = 'Showing all ' + this.screens.length + ' screens';
                } else {
                    countText = 'Showing ' + this.filteredScreens.length + ' of ' + this.screens.length + ' screens';
                }
            }
            
            $('.screen-count').text(countText);
        },
        
        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    // Initialize the module
    window.RuplinAdminScreensSearcher = RuplinAdminScreensSearcher;
    RuplinAdminScreensSearcher.init();
    
})(jQuery);