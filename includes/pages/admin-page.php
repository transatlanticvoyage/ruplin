<?php
/**
 * Main admin dashboard page
 */
function snefuru_admin_page() {
    global $snefuru_admin;
    
    // Call the original method from the admin class
    $snefuru_admin->admin_page();
}