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
        
        // Add any click handlers or interactions here
        $('.snefuru-hurricane-content').on('click', function() {
            console.log('Hurricane clicked - ready for future functionality');
        });
        
        // Ensure Hurricane metabox stays at top of sidebar
        moveHurricaneToTop();
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