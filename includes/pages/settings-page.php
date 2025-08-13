<?php
/**
 * Settings page
 */
function snefuru_settings_page() {
    global $snefuru_admin;
    
    // Call the original method from the admin class
    $snefuru_admin->settings_page();
}