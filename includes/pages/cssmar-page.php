<?php
/**
 * CSS Editor page (cssmar)
 */
function snefuru_cssmar_page() {
    // Get the admin instance for accessing helper methods
    global $snefuru_admin;
    
    // AGGRESSIVE NOTICE SUPPRESSION
    $snefuru_admin->suppress_all_admin_notices();
    
    // Include the full cssmar page implementation
    require_once SNEFURU_PLUGIN_DIR . 'includes/pages/cssmar-page-content.php';
}