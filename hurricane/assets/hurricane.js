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
    
    function moveHurricaneToTop() {
        // Move the Hurricane metabox to the top of the side-sortables area
        const hurricaneBox = $('#snefuru-hurricane');
        const sideArea = $('#side-sortables');
        
        if (hurricaneBox.length && sideArea.length) {
            hurricaneBox.prependTo(sideArea);
        }
    }
    
})(jQuery);