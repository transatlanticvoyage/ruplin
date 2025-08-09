/**
 * Zen Media Library Workaround
 * Adds custom zen data tabs to WordPress media library for Elementor integration
 */

(function($) {
    'use strict';
    
    // Wait for media views to be available
    $(document).ready(function() {
        if (typeof wp !== 'undefined' && wp.media) {
            initZenMediaWorkaround();
        }
    });
    
    function initZenMediaWorkaround() {
        // Extend the media library frame
        var originalMediaFrame = wp.media.view.MediaFrame.Select;
        
        wp.media.view.MediaFrame.Select = originalMediaFrame.extend({
            
            initialize: function() {
                originalMediaFrame.prototype.initialize.apply(this, arguments);
                
                // Check if we're in Elementor context
                if (this.isElementorContext()) {
                    this.addZenTabs();
                }
            },
            
            isElementorContext: function() {
                // Check for Elementor indicators
                return window.elementor || 
                       $('body').hasClass('elementor-editor-active') ||
                       window.location.href.indexOf('elementor') > -1 ||
                       $('.elementor-control-media').length > 0;
            },
            
            addZenTabs: function() {
                var self = this;
                
                // Add Zen Services tab
                this.states.add([
                    new wp.media.controller.Library({
                        id: 'zen-services',
                        title: '🚀 Zen Services',
                        priority: 200,
                        toolbar: 'zen-services-toolbar',
                        filterable: 'uploaded',
                        library: wp.media.query(),
                        multiple: false,
                        editable: false,
                        allowLocalEdits: false
                    }),
                    
                    // Add Zen Locations tab
                    new wp.media.controller.Library({
                        id: 'zen-locations',
                        title: '📍 Zen Locations',
                        priority: 201,
                        toolbar: 'zen-locations-toolbar',
                        filterable: 'uploaded',
                        library: wp.media.query(),
                        multiple: false,
                        editable: false,
                        allowLocalEdits: false
                    })
                ]);
                
                // Listen for state changes
                this.on('content:create:zen-services', this.createZenServicesContent, this);
                this.on('content:create:zen-locations', this.createZenLocationsContent, this);
                this.on('toolbar:create:zen-services-toolbar', this.createZenServicesToolbar, this);
                this.on('toolbar:create:zen-locations-toolbar', this.createZenLocationsToolbar, this);
            },
            
            createZenServicesContent: function() {
                var view = new ZenServicesView();
                this.content.set(view);
            },
            
            createZenLocationsContent: function() {
                var view = new ZenLocationsView();
                this.content.set(view);
            },
            
            createZenServicesToolbar: function(toolbar) {
                toolbar.set('zen-services-select', {
                    style: 'primary',
                    priority: 80,
                    text: 'Use Selected Service Image',
                    click: function() {
                        // Handle service selection
                    }
                });
            },
            
            createZenLocationsToolbar: function(toolbar) {
                toolbar.set('zen-locations-select', {
                    style: 'primary',
                    priority: 80,
                    text: 'Use Selected Location Image',
                    click: function() {
                        // Handle location selection
                    }
                });
            }
        });
        
        // Create Zen Services view
        var ZenServicesView = wp.Backbone.View.extend({
            className: 'zen-services-view',
            template: wp.template('zen-services-tab'),
            
            events: {
                'click .zen-select-service': 'selectService'
            },
            
            initialize: function() {
                this.loadServices();
            },
            
            loadServices: function() {
                var self = this;
                
                $.post(zenMediaAjax.ajaxurl, {
                    action: 'zen_get_services_for_media',
                    nonce: zenMediaAjax.nonce
                }, function(response) {
                    if (response.success) {
                        self.services = response.data;
                        self.render();
                    }
                });
            },
            
            render: function() {
                if (this.services) {
                    this.$el.html(this.template({
                        services: this.services
                    }));
                } else {
                    this.$el.html('<div class="zen-loading">Loading services...</div>');
                }
                return this;
            },
            
            selectService: function(e) {
                e.preventDefault();
                var serviceId = $(e.currentTarget).data('service-id');
                var self = this;
                
                // Get service image data
                $.post(zenMediaAjax.ajaxurl, {
                    action: 'zen_get_service_image_data',
                    service_id: serviceId,
                    size: 'full',
                    nonce: zenMediaAjax.nonce
                }, function(response) {
                    if (response.success) {
                        // Trigger selection event that Elementor can catch
                        var imageData = response.data;
                        self.triggerImageSelection(imageData);
                    }
                });
            },
            
            triggerImageSelection: function(imageData) {
                // Close the modal and return the image data
                var frame = wp.media.frame;
                
                // Create a fake attachment model that Elementor expects
                var attachment = new wp.media.model.Attachment(imageData);
                
                // Trigger the select event
                frame.state().get('selection').reset([attachment]);
                frame.close();
                
                // Additional trigger for Elementor
                if (window.elementor) {
                    $(document).trigger('zen:image:selected', [imageData]);
                }
            }
        });
        
        // Create Zen Locations view
        var ZenLocationsView = wp.Backbone.View.extend({
            className: 'zen-locations-view',
            template: wp.template('zen-locations-tab'),
            
            events: {
                'click .zen-select-location': 'selectLocation'
            },
            
            initialize: function() {
                this.loadLocations();
            },
            
            loadLocations: function() {
                var self = this;
                
                $.post(zenMediaAjax.ajaxurl, {
                    action: 'zen_get_locations_for_media',
                    nonce: zenMediaAjax.nonce
                }, function(response) {
                    if (response.success) {
                        self.locations = response.data;
                        self.render();
                    }
                });
            },
            
            render: function() {
                if (this.locations) {
                    this.$el.html(this.template({
                        locations: this.locations
                    }));
                } else {
                    this.$el.html('<div class="zen-loading">Loading locations...</div>');
                }
                return this;
            },
            
            selectLocation: function(e) {
                e.preventDefault();
                var locationId = $(e.currentTarget).data('location-id');
                var self = this;
                
                // Get location image data
                $.post(zenMediaAjax.ajaxurl, {
                    action: 'zen_get_location_image_data',
                    location_id: locationId,
                    size: 'full',
                    nonce: zenMediaAjax.nonce
                }, function(response) {
                    if (response.success) {
                        var imageData = response.data;
                        self.triggerImageSelection(imageData);
                    }
                });
            },
            
            triggerImageSelection: function(imageData) {
                var frame = wp.media.frame;
                var attachment = new wp.media.model.Attachment(imageData);
                
                frame.state().get('selection').reset([attachment]);
                frame.close();
                
                if (window.elementor) {
                    $(document).trigger('zen:image:selected', [imageData]);
                }
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
    
    // Fallback: also try to hook into media modal directly
    $(document).on('click', '.media-modal .media-router .media-menu-item', function() {
        var $item = $(this);
        
        // If this is our zen tab being clicked, load the appropriate content
        if ($item.text().indexOf('Zen Services') > -1) {
            setTimeout(function() {
                loadZenServicesInModal();
            }, 100);
        } else if ($item.text().indexOf('Zen Locations') > -1) {
            setTimeout(function() {
                loadZenLocationsInModal();
            }, 100);
        }
    });
    
    function loadZenServicesInModal() {
        var $content = $('.media-frame-content .attachments-browser');
        if ($content.length && !$content.hasClass('zen-services-loaded')) {
            $content.addClass('zen-services-loaded');
            
            $.post(zenMediaAjax.ajaxurl, {
                action: 'zen_get_services_for_media',
                nonce: zenMediaAjax.nonce
            }, function(response) {
                if (response.success) {
                    var html = '<div class="zen-services-grid">';
                    $.each(response.data, function(i, service) {
                        html += '<div class="zen-service-item" data-service-id="' + service.id + '">';
                        if (service.image_url) {
                            html += '<img src="' + service.image_url + '" alt="' + service.name + '" />';
                        }
                        html += '<h4>' + service.name + '</h4>';
                        if (service.placard) {
                            html += '<p>' + service.placard + '</p>';
                        }
                        html += '<button class="button zen-select-service" data-service-id="' + service.id + '">Select</button>';
                        html += '</div>';
                    });
                    html += '</div>';
                    
                    $content.html(html);
                }
            });
        }
    }
    
    function loadZenLocationsInModal() {
        var $content = $('.media-frame-content .attachments-browser');
        if ($content.length && !$content.hasClass('zen-locations-loaded')) {
            $content.addClass('zen-locations-loaded');
            
            $.post(zenMediaAjax.ajaxurl, {
                action: 'zen_get_locations_for_media',
                nonce: zenMediaAjax.nonce
            }, function(response) {
                if (response.success) {
                    var html = '<div class="zen-locations-grid">';
                    $.each(response.data, function(i, location) {
                        html += '<div class="zen-location-item" data-location-id="' + location.id + '">';
                        if (location.image_url) {
                            html += '<img src="' + location.image_url + '" alt="' + location.name + '" />';
                        }
                        html += '<h4>' + location.name + '</h4>';
                        if (location.address) {
                            html += '<p>' + location.address + '</p>';
                        }
                        html += '<button class="button zen-select-location" data-location-id="' + location.id + '">Select</button>';
                        html += '</div>';
                    });
                    html += '</div>';
                    
                    $content.html(html);
                }
            });
        }
    }
    
})(jQuery);