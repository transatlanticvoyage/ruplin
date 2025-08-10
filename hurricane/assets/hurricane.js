/**
 * Hurricane Feature JavaScript
 * Handles interactions for the Hurricane interface element
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initHurricane();
    });
    
    function initHurricane() {
        // Initialize Hurricane functionality
        console.log('Hurricane feature initialized');
        
        // Add lightning popup button handler
        $('.snefuru-lightning-popup-btn').on('click', function(e) {
            e.preventDefault();
            openLightningPopup();
        });
        
        // Add popup close handlers
        $('.snefuru-popup-close').on('click', function(e) {
            e.preventDefault();
            closeLightningPopup();
        });
        
        // Close popup when clicking overlay background
        $('.snefuru-popup-overlay').on('click', function(e) {
            if (e.target === this) {
                closeLightningPopup();
            }
        });
        
        // Close popup with Escape key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && $('#snefuru-lightning-popup').is(':visible')) {
                closeLightningPopup();
            }
        });
        
        // Ensure Hurricane metabox stays at top of sidebar
        moveHurricaneToTop();
    }
    
    function openLightningPopup() {
        $('#snefuru-lightning-popup').fadeIn(300);
        $('body').addClass('snefuru-popup-open');
        console.log('Lightning popup opened');
    }
    
    function closeLightningPopup() {
        $('#snefuru-lightning-popup').fadeOut(300);
        $('body').removeClass('snefuru-popup-open');
        console.log('Lightning popup closed');
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