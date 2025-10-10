/**
 * Hurricane Feature JavaScript
 * Handles interactions for the Hurricane interface element
 */

// Define global functions first (outside of jQuery scope)
function snefuruOpenLightningPopup() {
    console.log('Opening lightning popup...');
    var popup = jQuery('#snefuru-lightning-popup');
    if (popup.length) {
        popup.show().fadeIn(300);
        jQuery('body').addClass('snefuru-popup-open');
        console.log('Lightning popup opened successfully');
    } else {
        console.error('Popup element not found!');
    }
}

function snefuruCloseLightningPopup() {
    console.log('Closing lightning popup...');
    jQuery('#snefuru-lightning-popup').fadeOut(300);
    jQuery('body').removeClass('snefuru-popup-open');
    console.log('Lightning popup closed');
}

// Make functions available on window object
window.snefuruOpenLightningPopup = snefuruOpenLightningPopup;
window.snefuruCloseLightningPopup = snefuruCloseLightningPopup;

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initHurricane();
    });
    
    // Also try to initialize when the page is fully loaded
    $(window).on('load', function() {
        initHurricane();
    });
    
    function initHurricane() {
        console.log('Hurricane feature initializing...');
        
        // Initialize Stellar Chamber tabs
        initStellarTabs();
        
        // Check if elements exist
        if ($('.snefuru-lightning-popup-btn').length) {
            console.log('Lightning button found');
        } else {
            console.log('Lightning button NOT found');
        }
        
        if ($('#snefuru-lightning-popup').length) {
            console.log('Lightning popup found');
        } else {
            console.log('Lightning popup NOT found');
        }
        
        // Use event delegation to handle dynamically loaded content
        $(document).off('click.hurricane').on('click.hurricane', '.snefuru-lightning-popup-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Lightning button clicked via jQuery!');
            snefuruOpenLightningPopup();
        });
        
        // Add popup close handlers using delegation
        $(document).off('click.hurricane-close').on('click.hurricane-close', '.snefuru-popup-close', function(e) {
            e.preventDefault();
            console.log('Close button clicked');
            snefuruCloseLightningPopup();
        });
        
        // Close popup when clicking overlay background
        $(document).off('click.hurricane-overlay').on('click.hurricane-overlay', '.snefuru-popup-overlay', function(e) {
            if (e.target === this) {
                console.log('Overlay clicked');
                snefuruCloseLightningPopup();
            }
        });
        
        // Close popup with Escape key
        $(document).off('keydown.hurricane').on('keydown.hurricane', function(e) {
            if (e.keyCode === 27 && $('#snefuru-lightning-popup').is(':visible')) {
                console.log('Escape pressed');
                snefuruCloseLightningPopup();
            }
        });
        
        // Ensure Hurricane metabox stays at top of sidebar
        setTimeout(moveHurricaneToTop, 500);
        
        console.log('Hurricane feature initialized');
    }
    
    // Fallback function for copying text in older browsers
    function fallbackCopyText(text, $button) {
        // Create a temporary textarea
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        
        try {
            textarea.select();
            textarea.setSelectionRange(0, 99999);
            var successful = document.execCommand('copy');
            
            if (successful) {
                $button.addClass('copied');
                setTimeout(function() {
                    $button.removeClass('copied');
                }, 2000);
                console.log('URL copied using fallback method:', text);
            } else {
                console.error('Fallback copy failed');
            }
        } catch (err) {
            console.error('Fallback copy error:', err);
        } finally {
            document.body.removeChild(textarea);
        }
    }
    
    function moveHurricaneToTop() {
        // Move the Hurricane metabox to the top of the side-sortables area
        const hurricaneBox = $('#snefuru-hurricane');
        const sideArea = $('#side-sortables');
        
        if (hurricaneBox.length && sideArea.length) {
            hurricaneBox.prependTo(sideArea);
        }
    }
    
    function initStellarTabs() {
        console.log('Initializing Stellar Chamber tabs...');
        
        // Handle tab button clicks
        $(document).off('click.stellar-tabs').on('click.stellar-tabs', '.snefuru-stellar-tab-button', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var tabName = $button.data('tab');
            
            // Don't do anything if already active
            if ($button.hasClass('active')) {
                return;
            }
            
            console.log('Switching to tab:', tabName);
            
            // Remove active class from all buttons and panels
            $('.snefuru-stellar-tab-button').removeClass('active');
            $('.snefuru-stellar-tab-panel').removeClass('active');
            
            // Add active class to clicked button
            $button.addClass('active');
            
            // Show the corresponding panel
            $('.snefuru-stellar-tab-panel[data-panel="' + tabName + '"]').addClass('active');
            
            // Initialize driggs tab when it's first activated
            if (tabName === 'driggs' && $('#driggs-stellar-data').attr('data-initialized') === 'false') {
                initDriggsTab();
            }
        });
        
        // Handle copy button clicks for textboxes
        $(document).off('click.copy-btn').on('click.copy-btn', '.snefuru-copy-btn, .snefuru-copy-btn-right', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var targetId = $button.data('target');
            var $textbox = $('#' + targetId);
            
            if ($textbox.length && $textbox.val()) {
                // Select the text
                $textbox[0].select();
                $textbox[0].setSelectionRange(0, 99999); // For mobile devices
                
                // Copy the text
                try {
                    var successful = document.execCommand('copy');
                    if (successful) {
                        // Change button text temporarily
                        var originalText = $button.text();
                        $button.text('Copied!').addClass('copied');
                        
                        setTimeout(function() {
                            $button.text(originalText).removeClass('copied');
                        }, 2000);
                        
                        console.log('Textbox data copied to clipboard');
                    } else {
                        console.error('Failed to copy text');
                    }
                } catch (err) {
                    console.error('Copy error:', err);
                    
                    // Fallback for modern browsers
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText($textbox.val()).then(function() {
                            var originalText = $button.text();
                            $button.text('Copied!').addClass('copied');
                            
                            setTimeout(function() {
                                $button.text(originalText).removeClass('copied');
                            }, 2000);
                            
                            console.log('Textbox data copied to clipboard (modern method)');
                        }).catch(function(err) {
                            console.error('Clipboard write failed:', err);
                        });
                    }
                }
                
                // Deselect the text
                window.getSelection().removeAllRanges();
            } else {
                console.log('No textbox data to copy');
            }
        });
        
        // Handle refresh redshift data button clicks
        $(document).off('click.refresh-redshift').on('click.refresh-redshift', '#refresh-redshift-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var postId = $button.data('post-id');
            var originalText = $button.text();
            
            // Disable button and show loading
            $button.prop('disabled', true).text('refreshing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'refresh_redshift_data',
                    post_id: postId,
                    nonce: $('#hurricane-nonce').val() || '<?php echo wp_create_nonce("hurricane_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.content) {
                        $('#frontend-content-textbox').val(response.data.content);
                        console.log('Redshift data refreshed successfully');
                    } else {
                        console.error('Failed to refresh redshift data:', response.data);
                        alert('Failed to refresh redshift data: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error refreshing redshift data:', error);
                    alert('Error refreshing redshift data: ' + error);
                },
                complete: function() {
                    // Re-enable button
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // Handle refresh blueshift data button clicks
        $(document).off('click.refresh-blueshift').on('click.refresh-blueshift', '#refresh-blueshift-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var postId = $button.data('post-id');
            var originalText = $button.text();
            
            // Disable button and show loading
            $button.prop('disabled', true).text('refreshing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'refresh_blueshift_data',
                    post_id: postId,
                    nonce: $('#hurricane-nonce').val() || '<?php echo wp_create_nonce("hurricane_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.content_format1) {
                            $('#blueshift-content-textbox').val(response.data.content_format1);
                        }
                        if (response.data.content_format2) {
                            $('#blueshift-content-textbox-2').val(response.data.content_format2);
                        }
                        if (response.data.content_format3) {
                            $('#blueshift-content-textbox-3').val(response.data.content_format3);
                        }
                        console.log('Blueshift data refreshed successfully');
                    } else {
                        console.error('Failed to refresh blueshift data:', response.data);
                        alert('Failed to refresh blueshift data: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error refreshing blueshift data:', error);
                    alert('Error refreshing blueshift data: ' + error);
                },
                complete: function() {
                    // Re-enable button
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // Handle cobalt submit button clicks
        $(document).off('click.cobalt-submit').on('click.cobalt-submit', '#snefuru-cobalt-submit-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var content = $('#snefuru-cobalt-content').val();
            var postId = $button.data('post-id');
            var autoUpdateTitle = $('#snefuru-auto-title-toggle-cobalt').is(':checked');
            var originalText = $button.text();
            var $result = $('#snefuru-cobalt-result');
            
            if (!content.trim()) {
                $result.removeClass('success error').addClass('error');
                $result.html('<strong>Error:</strong> Please enter content to inject.');
                $result.show();
                return;
            }
            
            // Disable button and show loading
            $button.prop('disabled', true).text('Processing...');
            $result.hide();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cobalt_inject_content',
                    post_id: postId,
                    content: content,
                    auto_update_title: autoUpdateTitle,
                    nonce: $('#hurricane-nonce').val() || '<?php echo wp_create_nonce("hurricane_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $result.removeClass('error').addClass('success');
                        $result.html('<strong>Success:</strong> ' + response.data);
                        console.log('Cobalt injection successful:', response.data);
                    } else {
                        $result.removeClass('success').addClass('error');
                        $result.html('<strong>Error:</strong> ' + response.data);
                        console.error('Cobalt injection failed:', response.data);
                    }
                    $result.show();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error during cobalt injection:', error);
                    $result.removeClass('success').addClass('error');
                    $result.html('<strong>Error:</strong> AJAX request failed: ' + error);
                    $result.show();
                },
                complete: function() {
                    // Re-enable button
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // Handle copy button clicks for locations URL
        $(document).off('click.locations-copy-btn').on('click.locations-copy-btn', '.snefuru-locations-copy-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var urlToCopy = $button.data('copy-url');
            
            if (urlToCopy) {
                // Try modern clipboard API first
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(urlToCopy).then(function() {
                        $button.addClass('copied');
                        
                        setTimeout(function() {
                            $button.removeClass('copied');
                        }, 2000);
                        
                        console.log('Locations URL copied to clipboard:', urlToCopy);
                    }).catch(function(err) {
                        console.error('Clipboard write failed:', err);
                        fallbackCopyText(urlToCopy, $button);
                    });
                } else {
                    // Fallback for older browsers
                    fallbackCopyText(urlToCopy, $button);
                }
            } else {
                console.log('No URL to copy');
            }
        });
        
        // Check if tabs exist
        if ($('.snefuru-stellar-tab-button').length) {
            console.log('Stellar tabs found:', $('.snefuru-stellar-tab-button').length);
        } else {
            console.log('No Stellar tabs found');
        }
    }
    
    /**
     * Initialize the Driggs tab functionality
     * Separated from main template for better maintainability
     */
    function initDriggsTab() {
        console.log('Initializing driggs tab...');
        
        var driggsData = {};
        var nonce = $('#driggs-stellar-data').data('nonce');
        
        // Mark as initialized
        $('#driggs-stellar-data').attr('data-initialized', 'true');
        
        // Load initial driggs data
        loadDriggsStellarData();
        
        function loadDriggsStellarData() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rup_driggs_get_data',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        driggsData = response.data;
                        displayDriggsStellarData();
                        console.log('Driggs data loaded successfully');
                    } else {
                        console.error('Error loading driggs data:', response.data);
                        alert('Error loading driggs data: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error loading driggs data:', error);
                    alert('Error loading driggs data: ' + error);
                }
            });
        }
        
        function displayDriggsStellarData() {
            var tbody = $('#driggs-stellar-table-body');
            tbody.empty();
            
            // Only fields with "driggs_" in the name, in order from rup_driggs_mar page
            var driggsFields = [
                {key: 'driggs_industry', label: 'driggs_industry', type: 'text'},
                {key: 'driggs_keywords', label: 'driggs_keywords', type: 'textarea'},
                {key: 'driggs_category', label: 'driggs_category', type: 'text'},
                {key: 'driggs_city', label: 'driggs_city', type: 'text'},
                {key: 'driggs_brand_name', label: 'driggs_brand_name', type: 'text'},
                {key: 'driggs_site_type_purpose', label: 'driggs_site_type_purpose', type: 'text'},
                {key: 'driggs_email_1', label: 'driggs_email_1', type: 'email'},
                {key: 'driggs_phone_1', label: 'driggs_phone_1', type: 'text'},
                {key: 'driggs_address_full', label: 'driggs_address_full', type: 'textarea'},
                {key: 'driggs_street_1', label: 'driggs_street_1', type: 'text'},
                {key: 'driggs_street_2', label: 'driggs_street_2', type: 'text'},
                {key: 'driggs_state_code', label: 'driggs_state_code', type: 'text'},
                {key: 'driggs_zip', label: 'driggs_zip', type: 'text'},
                {key: 'driggs_state_full', label: 'driggs_state_full', type: 'text'},
                {key: 'driggs_country', label: 'driggs_country', type: 'text'},
                {key: 'driggs_payment_methods', label: 'driggs_payment_methods', type: 'textarea'},
                {key: 'driggs_social_media_links', label: 'driggs_social_media_links', type: 'textarea'},
                {key: 'driggs_hours', label: 'driggs_hours', type: 'textarea'},
                {key: 'driggs_owner_name', label: 'driggs_owner_name', type: 'text'},
                {key: 'driggs_short_descr', label: 'driggs_short_descr', type: 'textarea'},
                {key: 'driggs_long_descr', label: 'driggs_long_descr', type: 'textarea'},
                {key: 'driggs_year_opened', label: 'driggs_year_opened', type: 'number'},
                {key: 'driggs_employees_qty', label: 'driggs_employees_qty', type: 'number'},
                {key: 'driggs_special_note_for_ai_tool', label: 'driggs_special_note_for_ai_tool', type: 'textarea'},
                {key: 'driggs_logo_url', label: 'driggs_logo_url', type: 'text'},
                {key: 'driggs_phone1_platform_id', label: 'driggs_phone1_platform_id', type: 'number'},
                {key: 'driggs_cgig_id', label: 'driggs_cgig_id', type: 'number'},
                {key: 'driggs_revenue_goal', label: 'driggs_revenue_goal', type: 'number'},
                {key: 'driggs_address_species_id', label: 'driggs_address_species_id', type: 'number'},
                {key: 'driggs_address_species_note', label: 'driggs_address_species_note', type: 'textarea'},
                {key: 'driggs_citations_done', label: 'driggs_citations_done', type: 'boolean'},
                {key: 'driggs_social_profiles_done', label: 'driggs_social_profiles_done', type: 'boolean'}
            ];
            
            driggsFields.forEach(function(field) {
                var tr = $('<tr></tr>');
                tr.hover(function() {
                    $(this).css('background-color', '#f9f9f9');
                }, function() {
                    $(this).css('background-color', '');
                });
                
                // Field name column
                var fieldNameTd = $('<td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;"></td>');
                fieldNameTd.text(field.label);
                tr.append(fieldNameTd);
                
                // Value column
                var valueTd = $('<td style="padding: 8px; border: 1px solid #ddd;" data-field="' + field.key + '"></td>');
                var currentValue = driggsData[field.key] || '';
                
                if (field.type === 'boolean') {
                    var toggleHtml = createStellarToggleSwitch(field.key, currentValue == '1');
                    valueTd.html(toggleHtml);
                } else if (field.type === 'textarea') {
                    valueTd.html('<div class="stellareditable-field" data-field="' + field.key + '" data-type="textarea">' + 
                               $('<div>').text(currentValue).html() + '</div>');
                } else {
                    valueTd.html('<div class="stellareditable-field" data-field="' + field.key + '" data-type="' + field.type + '">' + 
                               $('<div>').text(currentValue).html() + '</div>');
                }
                
                tr.append(valueTd);
                tbody.append(tr);
            });
            
            // Make fields editable
            makeStellarFieldsEditable();
            setupStellarToggleSwitches();
        }
        
        function createStellarToggleSwitch(fieldKey, isChecked) {
            return '<label class="stellar-driggs-toggle-switch" data-field="' + fieldKey + '">' +
                  '<input type="checkbox" ' + (isChecked ? 'checked' : '') + '>' +
                  '<span class="stellar-driggs-toggle-slider">' +
                  '<span class="stellar-driggs-toggle-knob"></span>' +
                  '</span>' +
                  '</label>';
        }
        
        function makeStellarFieldsEditable() {
            $(document).off('click.stellar-editable').on('click.stellar-editable', '.stellareditable-field', function() {
                var $this = $(this);
                var fieldKey = $this.data('field');
                var fieldType = $this.data('type');
                var currentValue = $this.text();
                
                if ($this.find('input, textarea').length > 0) {
                    return; // Already editing
                }
                
                var input;
                if (fieldType === 'textarea') {
                    input = $('<textarea style="width: 100%; min-height: 60px; padding: 4px; border: 1px solid #ddd; border-radius: 3px; font-family: inherit; font-size: inherit;"></textarea>');
                } else {
                    input = $('<input type="' + fieldType + '" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px; font-family: inherit; font-size: inherit;">');
                }
                
                input.val(currentValue);
                $this.empty().append(input);
                input.focus().select();
                
                function saveField() {
                    var newValue = input.val();
                    updateStellarDriggsField(fieldKey, newValue, function() {
                        $this.empty().text(newValue);
                        driggsData[fieldKey] = newValue;
                    });
                }
                
                input.on('blur', saveField);
                input.on('keydown', function(e) {
                    if (e.which === 13 && fieldType !== 'textarea') { // Enter key for non-textarea
                        saveField();
                    } else if (e.which === 27) { // Escape key
                        $this.empty().text(currentValue);
                    }
                });
            });
        }
        
        function setupStellarToggleSwitches() {
            $(document).off('click.stellar-toggle').on('click.stellar-toggle', '.stellar-driggs-toggle-switch', function(e) {
                e.preventDefault();
                var $label = $(this);
                var $checkbox = $label.find('input[type="checkbox"]');
                var fieldKey = $label.data('field');
                
                // Toggle the checkbox state
                var newValue = !$checkbox.prop('checked');
                $checkbox.prop('checked', newValue);
                
                // Update the database
                updateStellarDriggsField(fieldKey, newValue ? '1' : '0', function() {
                    driggsData[fieldKey] = newValue ? '1' : '0';
                });
            });
        }
        
        function updateStellarDriggsField(fieldKey, value, callback) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rup_driggs_update_field',
                    nonce: nonce,
                    field: fieldKey,
                    value: value
                },
                success: function(response) {
                    if (response.success && callback) {
                        callback();
                        console.log('Field updated successfully:', fieldKey, '=', value);
                    } else {
                        console.error('Error saving field:', response.data);
                        alert('Error saving field: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error saving field:', error);
                    alert('Error saving field: ' + error);
                }
            });
        }
        
        // Save button functionality
        $(document).off('click.driggs-save').on('click.driggs-save', '#save-driggs-btn', function() {
            alert('All changes are saved automatically when you edit fields.');
        });
    }
    
})(jQuery);