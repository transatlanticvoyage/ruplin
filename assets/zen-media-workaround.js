/**
 * Zen Media Library Workaround
 * Adds Gopher Dynamic Tab to WordPress media library sidebar for Elementor integration
 */

(function($) {
    'use strict';
    
    var zenData = {
        services: [],
        locations: [],
        currentMode: 'services'
    };
    
    // Wait for media views to be available
    $(document).ready(function() {
        if (typeof wp !== 'undefined' && wp.media) {
            initZenMediaWorkaround();
        }
        
        // Also hook into media modal directly
        $(document).on('click', '.media-modal-backdrop, .media-modal-close', function() {
            // Clean up when modal closes
            $('.zen-gopher-interface').remove();
        });
    });
    
    function initZenMediaWorkaround() {
        // Hook into media modal after it opens
        $(document).on('DOMNodeInserted', '.media-modal', function(e) {
            if ($(e.target).hasClass('media-modal') || $(e.target).find('.media-modal').length) {
                setTimeout(function() {
                    addGopherDynamicTab();
                }, 500);
            }
        });
        
        // Fallback - check periodically for media modal
        var checkInterval = setInterval(function() {
            if ($('.media-modal').length > 0 && $('.zen-gopher-tab').length === 0) {
                addGopherDynamicTab();
            }
        }, 1000);
        
        // Stop checking after 30 seconds
        setTimeout(function() {
            clearInterval(checkInterval);
        }, 30000);
    }
    
    function addGopherDynamicTab() {
        // Check if we're in a context where this should be added
        if (!isElementorOrMediaContext()) {
            return;
        }
        
        var $sidebar = $('.media-frame .media-frame-menu .media-menu');
        
        if ($sidebar.length > 0 && $('.zen-gopher-tab').length === 0) {
            // Add Gopher Dynamic Tab to sidebar
            var gopherTabHtml = '<a href="#" class="media-menu-item zen-gopher-tab" data-zen-action="gopher-dynamic">' +
                               '<span class="dashicons dashicons-database-view" style="margin-right: 8px;"></span>' +
                               'Gopher Dynamic Tab</a>';
            
            $sidebar.append(gopherTabHtml);
            
            // Add click handler for Gopher Dynamic Tab
            $('.zen-gopher-tab').on('click', function(e) {
                e.preventDefault();
                
                // Remove active class from other tabs
                $('.media-menu .media-menu-item').removeClass('active');
                
                // Add active class to our tab
                $(this).addClass('active');
                
                // Show Gopher interface
                showGopherInterface();
            });
        }
    }
    
    function isElementorOrMediaContext() {
        // Check for Elementor indicators or general media library usage
        return window.elementor || 
               $('body').hasClass('elementor-editor-active') ||
               window.location.href.indexOf('elementor') > -1 ||
               $('.elementor-control-media').length > 0 ||
               $('.media-modal').length > 0;  // Always show in media library
    }
    
    function showGopherInterface() {
        var $content = $('.media-frame-content');
        
        if ($content.length > 0) {
            // Create Gopher interface
            var interfaceHtml = createGopherInterfaceHTML();
            $content.html(interfaceHtml);
            
            // Load initial data
            loadZenData();
            
            // Add event handlers
            bindGopherEvents();
        }
    }
    
    function createGopherInterfaceHTML() {
        return `
            <div class="zen-gopher-interface">
                <div class="zen-gopher-header">
                    <h2>🔗 Gopher Dynamic Tab - Zen Data Selector</h2>
                    <p>Select images from your zen services or locations database.</p>
                </div>
                
                <div class="zen-mode-switcher">
                    <button type="button" class="button zen-mode-btn active" data-mode="services">
                        🚀 Services
                    </button>
                    <button type="button" class="button zen-mode-btn" data-mode="locations">
                        📍 Locations
                    </button>
                </div>
                
                <div class="zen-data-container">
                    <div class="zen-loading-state">
                        <p>⏳ Loading zen data...</p>
                    </div>
                </div>
                
                <div class="zen-gopher-footer">
                    <button type="button" class="button button-primary zen-use-selected" disabled>
                        Use Selected Image
                    </button>
                    <button type="button" class="button zen-cancel">
                        Cancel
                    </button>
                </div>
            </div>
        `;
    }
    
    function bindGopherEvents() {
        // Mode switcher
        $('.zen-mode-btn').on('click', function() {
            $('.zen-mode-btn').removeClass('active');
            $(this).addClass('active');
            
            zenData.currentMode = $(this).data('mode');
            renderZenData();
        });
        
        // Cancel button
        $('.zen-cancel').on('click', function() {
            $('.media-modal-backdrop').click();
        });
        
        // Use selected button
        $('.zen-use-selected').on('click', function() {
            var selectedItem = $('.zen-item.selected');
            if (selectedItem.length > 0) {
                var itemId = selectedItem.data('id');
                var itemType = zenData.currentMode;
                
                useSelectedZenImage(itemType, itemId);
            }
        });
    }
    
    function loadZenData() {
        // Load services
        $.post(zenMediaAjax.ajaxurl, {
            action: 'zen_get_services_for_media',
            nonce: zenMediaAjax.nonce
        }, function(response) {
            if (response.success) {
                zenData.services = response.data;
                if (zenData.currentMode === 'services') {
                    renderZenData();
                }
            }
        });
        
        // Load locations
        $.post(zenMediaAjax.ajaxurl, {
            action: 'zen_get_locations_for_media',
            nonce: zenMediaAjax.nonce
        }, function(response) {
            if (response.success) {
                zenData.locations = response.data;
                if (zenData.currentMode === 'locations') {
                    renderZenData();
                }
            }
        });
    }
    
    function renderZenData() {
        var data = zenData.currentMode === 'services' ? zenData.services : zenData.locations;
        var $container = $('.zen-data-container');
        
        if (data.length === 0) {
            $container.html('<div class="zen-no-data"><p>No ' + zenData.currentMode + ' found.</p></div>');
            return;
        }
        
        var html = '<div class="zen-items-grid">';
        
        $.each(data, function(i, item) {
            var imageUrl = item.image_url || zenMediaAjax.plugin_url + 'assets/placeholder.png';
            var title = item.name || 'Unnamed Item';
            var subtitle = item.placard || item.address || '';
            var pinned = item.is_pinned ? '<span class="zen-pinned-badge">📌</span>' : '';
            
            html += `
                <div class="zen-item" data-id="${item.id}" data-type="${zenData.currentMode}">
                    <div class="zen-item-image">
                        <img src="${imageUrl}" alt="${title}" />
                        ${pinned}
                    </div>
                    <div class="zen-item-info">
                        <h4>${title}</h4>
                        ${subtitle ? '<p>' + subtitle + '</p>' : ''}
                        <span class="zen-item-id">ID: ${item.id}</span>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        $container.html(html);
        
        // Add click handlers for items
        $('.zen-item').on('click', function() {
            $('.zen-item').removeClass('selected');
            $(this).addClass('selected');
            
            // Enable use button
            $('.zen-use-selected').prop('disabled', false);
        });
    }
    
    function useSelectedZenImage(type, id) {
        var action = type === 'services' ? 'zen_get_service_image_data' : 'zen_get_location_image_data';
        var idField = type === 'services' ? 'service_id' : 'location_id';
        
        var requestData = {
            action: action,
            size: 'full',
            nonce: zenMediaAjax.nonce
        };
        requestData[idField] = id;
        
        $.post(zenMediaAjax.ajaxurl, requestData, function(response) {
            if (response.success) {
                var imageData = response.data;
                
                // Create a fake attachment model that media library expects
                var attachment = new wp.media.model.Attachment(imageData);
                
                // Get the current media frame
                var frame = wp.media.frame;
                if (frame && frame.state() && frame.state().get('selection')) {
                    frame.state().get('selection').reset([attachment]);
                    
                    // Close the modal
                    frame.close();
                }
                
                // Trigger additional events for Elementor
                $(document).trigger('zen:image:selected', [imageData]);
                
                console.log('Zen image selected:', imageData);
            } else {
                alert('Error loading image data: ' + (response.data || 'Unknown error'));
            }
        });
    }
    
    // Additional Elementor-specific handling
    $(document).on('zen:image:selected', function(e, imageData) {
        console.log('Zen image selected:', imageData);
        
        // If we can access Elementor's panel
        if (window.elementor && window.elementor.getPanelView) {
            var panelView = window.elementor.getPanelView();
            if (panelView && panelView.currentPageView) {
                // Try to find and update the current image control
                var currentControl = panelView.currentPageView.getCurrentEditedElement();
                if (currentControl) {
                    // Update the image control value
                    currentControl.setSettings('image', {
                        url: imageData.url,
                        id: imageData.id
                    });
                }
            }
        }
    });
    
})(jQuery);