<?php
/**
 * Main admin dashboard page
 */
function snefuru_admin_page() {
    // FORCE IMMEDIATE ERROR OUTPUT TO SCREEN
    echo '<div style="background: yellow; padding: 20px; margin: 20px; border: 3px solid red;">';
    echo '<h1>SNEFURU DEBUG: Admin Page Loading...</h1>';
    echo '<p>If you see this, the page is executing. Timestamp: ' . date('Y-m-d H:i:s') . '</p>';
    echo '</div>';
    
    // DEBUGGING: Start error capture
    error_log('=== SNEFURU ADMIN PAGE DEBUG START ===');
    
    try {
        global $snefuru_admin;
        
        // Debug: Check if admin object exists
        if (!$snefuru_admin) {
            error_log('SNEFURU DEBUG: $snefuru_admin is null/undefined');
            echo '<div style="background: red; color: white; padding: 20px; margin: 20px;">';
            echo '<h2>DEBUG: Snefuru Admin Object Missing</h2>';
            echo '<p>The global $snefuru_admin object is not available.</p>';
            echo '</div>';
            return;
        }
        
        // Debug: Check if method exists
        if (!method_exists($snefuru_admin, 'admin_page')) {
            error_log('SNEFURU DEBUG: admin_page method does not exist');
            echo '<div style="background: orange; color: white; padding: 20px; margin: 20px;">';
            echo '<h2>DEBUG: Admin Page Method Missing</h2>';
            echo '<p>The admin_page method does not exist on the Snefuru_Admin class.</p>';
            echo '</div>';
            return;
        }
        
        error_log('SNEFURU DEBUG: About to call admin_page method');
        
        // Call the original method from the admin class
        $snefuru_admin->admin_page();
        
        error_log('SNEFURU DEBUG: admin_page method completed successfully');
        
    } catch (Error $e) {
        error_log('SNEFURU DEBUG: Fatal Error in admin page: ' . $e->getMessage());
        error_log('SNEFURU DEBUG: Error trace: ' . $e->getTraceAsString());
        echo '<div style="background: red; color: white; padding: 20px; margin: 20px;">';
        echo '<h2>DEBUG: Fatal Error Caught</h2>';
        echo '<p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . esc_html($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '</div>';
    } catch (Exception $e) {
        error_log('SNEFURU DEBUG: Exception in admin page: ' . $e->getMessage());
        error_log('SNEFURU DEBUG: Exception trace: ' . $e->getTraceAsString());
        echo '<div style="background: orange; color: white; padding: 20px; margin: 20px;">';
        echo '<h2>DEBUG: Exception Caught</h2>';
        echo '<p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . esc_html($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '</div>';
    }
    
    error_log('=== SNEFURU ADMIN PAGE DEBUG END ===');
}