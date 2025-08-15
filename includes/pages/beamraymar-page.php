<?php
/**
 * Independent beamraymar page
 * Extracted from class-admin.php for better maintainability
 */

if (!defined('ABSPATH')) {
    exit;
}

// Make sure we can access WordPress functions and globals
global $wpdb;

/**
 * Main beamraymar page function - completely independent
 */
function snefuru_beamraymar_page() {
    // AGGRESSIVE NOTICE SUPPRESSION
    snefuru_suppress_all_admin_notices();
    
    // Handle AJAX requests
    if (isset($_POST['action']) && in_array($_POST['action'], ['create_new_post', 'update_post_field', 'create', 'edit'])) {
        snefuru_handle_beamraymar_ajax();
        return;
    }
    
    // Get posts and pages data
    $posts_pages = snefuru_get_posts_and_pages_data();
    
    ?>
    <div class="wrap beamraymar-wrapper">
        <style>
            /* Beamraymar Custom Styles - Mimicking FileJar Design */
            .beamraymar-wrapper {
                background: white;
                padding: 0;
                margin: 0 0 0 -20px;
            }
            
            .beamraymar-top-controls {
                background-color: white;
                border-bottom: 1px solid #ddd;
                padding: 12px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 16px;
            }
            
            .beamraymar-controls-left {
                display: flex;
                align-items: center;
                gap: 24px;
            }
            
            .beamray_banner1 {
                background: black;
                color: white;
                font-size: 18px;
                font-weight: bold;
                padding: 8px 12px;
                border: 1px solid gray;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                white-space: nowrap;
            }
            
            .beamray-logo {
                width: 20px;
                height: 20px;
                display: inline-block;
            }
            
            .beamraymar-controls-right {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            
            .beamraymar-info-text {
                font-size: 14px;
                color: #666;
            }
            
            .beamraymar-pagination-controls {
                display: flex;
                align-items: center;
            }
            
            .beamraymar-pagination-bar {
                display: flex;
            }
            
            .beamraymar-pagination-btn {
                padding: 10px 12px;
                font-size: 14px;
                border: 1px solid #ddd;
                background: white;
                cursor: pointer;
                margin-right: -1px;
                text-decoration: none;
                color: #333;
                transition: background-color 0.2s;
            }
            
            .beamraymar-pagination-btn:hover {
                background-color: #f5f5f5;
            }
            
            .beamraymar-pagination-btn.active {
                background-color: #0073aa;
                color: white;
            }
            
            .beamraymar-pagination-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .beamraymar-pagination-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            .beamraymar-pagination-divider {
                width: 1px;
                height: 20px;
                background: #ddd;
                margin: 0 12px;
            }
            
            .beamraymar-search-container {
                position: relative;
            }
            
            .beamraymar-search-input {
                width: 320px;
                padding: 8px 40px 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .beamraymar-clear-btn {
                position: absolute;
                right: 6px;
                top: 50%;
                transform: translateY(-50%);
                background: #ffd700;
                color: black;
                border: none;
                padding: 4px 8px;
                border-radius: 2px;
                font-size: 12px;
                font-weight: bold;
                cursor: pointer;
            }
            
            .beamraymar-clear-btn:hover {
                background: #ffed4e;
            }
            
            .beamraymar-create-buttons {
                display: flex;
                gap: 8px;
            }
            
            .beamraymar-create-btn {
                padding: 8px 16px;
                font-size: 14px;
                font-weight: 600;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
            }
            
            .beamraymar-create-inline-post {
                background: #16a085;
                color: white;
            }
            
            .beamraymar-create-inline-post:hover {
                background: #138d75;
            }
            
            .beamraymar-create-inline-page {
                background: #2980b9;
                color: white;
            }
            
            .beamraymar-create-inline-page:hover {
                background: #2471a3;
            }
            
            .beamraymar-create-popup {
                background: #8e44ad;
                color: white;
            }
            
            .beamraymar-create-popup:hover {
                background: #7d3c98;
            }
            
            .beamraymar-nubra-kite {
                background: #f39c12;
                color: black;
                padding: 6px 12px;
                border: 1px solid black;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            
            .beamraymar-table-container {
                background: white;
                overflow: hidden;
            }
            
            .beamraymar-table-scroll {
                overflow-x: auto;
            }
            
            .beamraymar-table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid #ddd;
                min-width: 1600px;
            }
            
            .beamraymar-table thead {
                background: #f8f9fa;
            }
            
            .beamraymar-table th,
            .beamraymar-table td {
                border: 1px solid #ddd;
                padding: 8px 12px;
                text-align: left;
                font-size: 14px;
            }
            
            .beamraymar-table th {
                font-weight: bold;
                font-size: 12px;
                text-transform: lowercase;
                cursor: pointer;
                position: relative;
            }
            
            .beamraymar-table th:hover {
                background: #e9ecef;
            }
            
            .beamraymar-table tbody tr:hover {
                background: #f8f9fa;
            }
            
            .beamraymar-checkbox-cell {
                width: 40px;
                text-align: center;
                cursor: pointer;
            }
            
            .beamraymar-checkbox {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
            
            .beamraymar-editable-cell {
                cursor: pointer;
                min-height: 20px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            
            .beamraymar-editable-cell:hover {
                background: #f0f8ff;
                outline: 1px solid #cce7ff;
            }
            
            .beamraymar-editing-input {
                width: 100%;
                padding: 4px 6px;
                border: 2px solid #0073aa;
                border-radius: 3px;
                font-size: 14px;
                background: white;
            }
            
            .beamraymar-editing-textarea {
                width: 100%;
                padding: 4px 6px;
                border: 2px solid #0073aa;
                border-radius: 3px;
                font-size: 14px;
                background: white;
                resize: none;
                min-height: 60px;
            }
            
            .beamraymar-toggle-switch {
                width: 48px;
                height: 24px;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s;
                position: relative;
                display: inline-block;
            }
            
            .beamraymar-toggle-switch.on {
                background: #16a085;
            }
            
            .beamraymar-toggle-switch.off {
                background: #bdc3c7;
            }
            
            .beamraymar-toggle-handle {
                width: 20px;
                height: 20px;
                background: white;
                border-radius: 50%;
                position: absolute;
                top: 2px;
                transition: transform 0.3s;
                box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            }
            
            .beamraymar-toggle-switch.on .beamraymar-toggle-handle {
                transform: translateX(24px);
            }
            
            .beamraymar-toggle-switch.off .beamraymar-toggle-handle {
                transform: translateX(2px);
            }
            
            .beamraymar-sort-indicator {
                margin-left: 8px;
                color: #666;
            }
            
            .beamraymar-loading {
                text-align: center;
                padding: 40px;
                font-size: 16px;
                color: #666;
            }
            
            /* Bottom controls */
            .beamraymar-bottom-controls {
                background-color: white;
                border-top: 1px solid #ddd;
                padding: 12px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            /* Modal styles */
            .beamraymar-modal {
                display: none;
                position: fixed;
                z-index: 999999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
            }
            
            .beamraymar-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .beamraymar-modal-content {
                background: white;
                padding: 30px;
                border-radius: 6px;
                max-width: 800px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .beamraymar-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 15px;
            }
            
            .beamraymar-modal-title {
                font-size: 20px;
                font-weight: bold;
                margin: 0;
            }
            
            .beamraymar-modal-close {
                font-size: 24px;
                background: none;
                border: none;
                cursor: pointer;
                color: #666;
            }
            
            .beamraymar-modal-close:hover {
                color: #333;
            }
            
            .beamraymar-form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .beamraymar-form-field {
                display: flex;
                flex-direction: column;
            }
            
            .beamraymar-form-field.full-width {
                grid-column: 1 / -1;
            }
            
            .beamraymar-form-label {
                font-weight: 600;
                margin-bottom: 6px;
                color: #333;
            }
            
            .beamraymar-form-input,
            .beamraymar-form-textarea,
            .beamraymar-form-select {
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .beamraymar-form-textarea {
                min-height: 100px;
                resize: vertical;
            }
            
            .beamraymar-modal-actions {
                display: flex;
                justify-content: flex-end;
                gap: 12px;
                margin-top: 24px;
            }
            
            .beamraymar-modal-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .beamraymar-modal-btn.primary {
                background: #0073aa;
                color: white;
            }
            
            .beamraymar-modal-btn.primary:hover {
                background: #005a87;
            }
            
            .beamraymar-modal-btn.secondary {
                background: #f1f1f1;
                color: #333;
                border: 1px solid #ddd;
            }
            
            .beamraymar-modal-btn.secondary:hover {
                background: #e0e0e0;
            }
            
            .beamraymar-table td .tcell_inner_wrapper_div {
                height: 38px;
            }
            
            .column_tool_buttons {
                min-width: 230px;
                white-space: nowrap;
            }
            
            .column_tool_buttons .tcell_inner_wrapper_div {
                display: flex;
                align-items: center;
                gap: 2px;
                height: auto;
            }
            
            .beamraymar-table td.column_wp_posts_post_title {
                font-size: 18px;
                font-weight: bold;
            }
            
            .beamraymar-table td.column_wp_posts_post_status[data-status="publish"] {
                background-color: #efddbb;
            }
            
            /* Column pagination styles */
            .beamraymar-column-pagination-controls {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                margin-left: 15px;
            }
            
            .beamraymar-column-pagination-bar {
                display: flex;
            }
            
            .beamraymar-column-pagination-btn {
                padding: 10px 12px;
                font-size: 14px;
                border: 1px solid #ddd;
                background: white;
                cursor: pointer;
                margin-right: -1px;
                text-decoration: none;
                color: #333;
                transition: background-color 0.2s;
            }
            
            .beamraymar-column-pagination-btn:hover {
                background-color: #f5f5f5;
            }
            
            .beamraymar-column-pagination-btn.active {
                background-color: #ffd700;
                color: black;
            }
            
            .beamraymar-column-pagination-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .beamraymar-column-pagination-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            /* Filter button bars */
            .beamraymar-filter-controls {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                margin-left: 15px;
            }
            
            .beamraymar-filter-bar {
                display: flex;
            }
            
            .beamraymar-filter-btn {
                padding: 10px 12px;
                font-size: 14px;
                border: 1px solid #ddd;
                background: white;
                cursor: pointer;
                margin-right: -1px;
                text-decoration: none;
                color: #333;
                transition: background-color 0.2s;
            }
            
            .beamraymar-filter-btn:hover {
                background-color: #f5f5f5;
            }
            
            .beamraymar-filter-btn.active {
                background-color: #0073aa;
                color: white;
            }
            
            .beamraymar-filter-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .beamraymar-filter-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            /* ED button for post_content */
            .beamraymar-content-edit-btn {
                width: 20px;
                height: 20px;
                background: #0073aa;
                color: white;
                border: none;
                cursor: pointer;
                font-size: 10px;
                font-weight: bold;
                float: right;
                margin-left: 8px;
            }
            
            .beamraymar-content-edit-btn:hover {
                background: #005a87;
            }
            
            /* Post content editor popup */
            .beamraymar-content-editor-modal {
                display: none;
                position: fixed;
                z-index: 999999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
            }
            
            .beamraymar-content-editor-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .beamraymar-content-editor-content {
                background: white;
                padding: 30px;
                border-radius: 6px;
                width: 90%;
                height: 85%;
                display: flex;
                flex-direction: column;
            }
            
            .beamraymar-content-editor-header {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 15px;
            }
            
            .beamraymar-content-editor-textarea {
                width: 100%;
                flex: 1;
                padding: 10px;
                border: 1px solid #ddd;
                font-family: monospace;
                font-size: 14px;
                resize: none;
            }
            
            .beamraymar-content-editor-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 15px;
            }
            
            .beamraymar-content-editor-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 600;
            }
            
            .beamraymar-content-editor-btn.save {
                background: #0073aa;
                color: white;
            }
            
            .beamraymar-content-editor-btn.cancel {
                background: #f1f1f1;
                color: #333;
            }
            
            /* Tool button styling */
            .beamraymar-tool-btn {
                width: 36px;
                height: 36px;
                background: #0073aa;
                color: white;
                border: none;
                cursor: pointer;
                font-size: 10px;
                font-weight: bold;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                border-radius: 2px;
            }
            
            .beamraymar-tool-btn:hover {
                background: #005a87;
                color: white;
                text-decoration: none;
            }
            
            /* Pendulum button styling */
            .beamraymar-pendulum-btn {
                width: 36px;
                height: 36px;
                background: #0073aa;
                color: white;
                border: none;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                cursor: pointer;
                margin-right: 2px;
                border-radius: 2px;
            }
            
            .beamraymar-pendulum-btn:hover {
                background: #005a87;
                color: white;
                text-decoration: none;
            }
            
            /* Elementor button styling */
            .beamraymar-elementor-btn {
                width: 36px;
                height: 36px;
                background: #800020;
                color: white;
                border: none;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                cursor: pointer;
                margin-right: 2px;
                border-radius: 2px;
            }
            
            .beamraymar-elementor-btn:hover {
                background: #5c0016;
                color: white;
                text-decoration: none;
            }
            
            .beamraymar-elementor-btn.disabled {
                background: #ccc;
                color: #999;
                cursor: not-allowed;
                pointer-events: none;
            }
            
            /* C1, C2, C3 button styling */
            .beamraymar-c-btn {
                width: 36px;
                height: 36px;
                background: #f0f0f0;
                color: #333;
                border: 1px solid #999;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                cursor: pointer;
                margin-right: 2px;
                border-radius: 2px;
            }
            
            /* Icon button styling */
            .beamray-icon-btn {
                display: inline-block;
                font-size: 16px;
                cursor: pointer;
                padding: 2px;
                opacity: 0.3;
                transition: all 0.2s ease;
                user-select: none;
            }
            
            .beamray-icon-btn:hover {
                opacity: 0.7;
                transform: scale(1.1);
            }
            
            .beamray-icon-btn.active {
                opacity: 1.0;
            }
            
            .beamray-icon-btn.active:hover {
                opacity: 1.0;
                transform: scale(1.2);
            }
        </style>
        
        <!-- Top Controls -->
        <div class="beamraymar-top-controls">
            <div class="beamraymar-controls-left">
                <div class="beamray_banner1">
                    <svg class="beamray-logo" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L4 7v10l8 5 8-5V7l-8-5zm0 2.18L18.18 8 12 11.82 5.82 8 12 4.18zM6 9.42l5 2.5v8.16l-5-2.5V9.42zm12 0v8.16l-5 2.5v-8.16l5-2.5z"/>
                        <circle cx="12" cy="8" r="1.5" fill="white"/>
                        <line x1="12" y1="8" x2="12" y2="16" stroke="white" stroke-width="1" opacity="0.6"/>
                        <line x1="8" y1="10" x2="16" y2="14" stroke="white" stroke-width="0.5" opacity="0.4"/>
                        <line x1="16" y1="10" x2="8" y2="14" stroke="white" stroke-width="0.5" opacity="0.4"/>
                    </svg>
                    BeamRay Table
                </div>
                <div class="beamraymar-info-text">
                    <span id="beamraymar-results-info">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
                
                <div class="beamraymar-pagination-controls">
                    <div class="beamraymar-pagination-bar" id="beamraymar-per-page-bar">
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="10">10</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="20">20</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="50">50</a>
                        <a href="#" class="beamraymar-pagination-btn active" data-per-page="100">100</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="200">200</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="500">500</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="all">All</a>
                    </div>
                    
                    <div class="beamraymar-pagination-divider"></div>
                    
                    <div class="beamraymar-pagination-bar" id="beamraymar-page-bar">
                        <a href="#" class="beamraymar-pagination-btn" data-page="first">First</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="prev">Prev</a>
                        <a href="#" class="beamraymar-pagination-btn active" data-page="1">1</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="next">Next</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="last">Last</a>
                    </div>
                </div>
                
                <div class="beamraymar-search-container">
                    <input type="text" id="beamraymar-search" class="beamraymar-search-input" placeholder="Search all fields...">
                    <button class="beamraymar-clear-btn" id="beamraymar-clear">CL</button>
                </div>
            </div>
            
            <div class="beamraymar-controls-right">
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <?php
                    global $wpdb;
                    $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren WHERE wppma_id = 1");
                    ?>
                    <div style="font-size: 16px; font-weight: bold; text-transform: lowercase; margin-bottom: 10px;">
                        <span style="font-weight: bold;"><?php echo esc_html($wpdb->prefix); ?></span>zen_sitespren.sitespren_base: <?php echo esc_html($sitespren_base ?: ''); ?>
                    </div>
                    <div class="beamraymar-create-buttons">
                        <button class="beamraymar-create-btn beamraymar-create-inline-post" id="create-post-inline">Create New (Inline)</button>
                        <button class="beamraymar-create-btn beamraymar-create-inline-page" id="create-page-inline">Create New (Inline) WP Page</button>
                        <button class="beamraymar-create-btn beamraymar-create-popup" id="create-popup">Create New (Popup)</button>
                        <button class="beamraymar-create-btn beamraymar-duplicate-btn" id="duplicate-selected">Duplicate</button>
                    </div>
                </div>
                <div class="beamraymar-nubra-kite">nubra-tableface-kite</div>
                
                <!-- Column Pagination Controls -->
                <div class="beamraymar-column-pagination-controls">
                    <div class="beamraymar-column-pagination-bar" id="beamraymar-column-group-bar">
                        <a href="#" class="beamraymar-column-pagination-btn active" data-column-group="1">Columns 1-7</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-group="2">Columns 8-14</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-group="3">Columns 15-21</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-group="4">Columns 22-28</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-group="5">Columns 29-35</a>
                    </div>
                    
                    <div class="beamraymar-column-pagination-bar" id="beamraymar-column-nav-bar">
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="first">First</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="prev">Prev</a>
                        <a href="#" class="beamraymar-column-pagination-btn active" data-column-nav="1">1</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="2">2</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="3">3</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="4">4</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="5">5</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="next">Next</a>
                        <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="last">Last</a>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                <div class="beamraymar-filter-controls">
                    <div class="beamraymar-filter-bar" id="beamraymar-post-type-filter">
                        <a href="#" class="beamraymar-filter-btn" data-filter-type="post_type" data-filter-value="page">Page</a>
                        <a href="#" class="beamraymar-filter-btn" data-filter-type="post_type" data-filter-value="post">Post</a>
                    </div>
                    
                    <div class="beamraymar-filter-bar" id="beamraymar-post-status-filter">
                        <a href="#" class="beamraymar-filter-btn" data-filter-type="post_status" data-filter-value="publish">Published</a>
                        <a href="#" class="beamraymar-filter-btn" data-filter-type="post_status" data-filter-value="draft">Draft</a>
                    </div>
                </div>
            </div>
        </div>
        </div>
        
        <!-- Table Container -->
        <div class="beamraymar-table-container">
            <div class="beamraymar-table-scroll">
                <table class="beamraymar-table utg_beamray" id="beamraymar-table">
                    <thead>
                        <tr>
                            <th class="column_checkbox row_obtain_db_table"><div class="tcell_inner_wrapper_div"></div></th>
                            <th class="column_tool_buttons row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong>misc-uicol-type</strong></div></th>
                            <th class="column_wp_posts_id row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_status row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_title row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_name row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_replex_submit row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong>non db ui column</strong></div></th>
                            <th class="column_wp_posts_post_content row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_postmeta_meta_key_elementor_data row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>postmeta</strong></div></th>
                            <th class="column_wp_posts_post_type row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_date row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_modified row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_author row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_parent row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_menu_order row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_comment_status row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_ping_status row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_zen_orbitposts_rel_wp_post_id row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_orbitpost_id row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_redshift_datum row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_rover_datum row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_hudson_imgplanbatch_id row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_is_pinned row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_is_flagged row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_is_starred row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_is_squared row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_created_at row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_updated_at row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                        </tr>
                        <tr class="beamraymar-main-header-row">
                            <th class="beamraymar-checkbox-cell column_checkbox row_obtain_db_column">
                                <div class="tcell_inner_wrapper_div"><input type="checkbox" class="beamraymar-checkbox" id="select-all"></div>
                            </th>
                            <th class="column_tool_buttons row_obtain_db_column"><div class="tcell_inner_wrapper_div">tool_buttons</div></th>
                            <th data-field="ID" data-type="integer" class="column_wp_posts_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">id</div></th>
                            <th data-field="post_status" data-type="text" class="column_wp_posts_post_status row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_status</div></th>
                            <th data-field="post_title" data-type="text" class="column_wp_posts_post_title row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_title</div></th>
                            <th data-field="post_name" data-type="text" class="column_wp_posts_post_name row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_name</div></th>
                            <th class="column_replex_submit row_obtain_db_column"><div class="tcell_inner_wrapper_div">replex_submit</div></th>
                            <th data-field="post_content" data-type="longtext" class="column_wp_posts_post_content row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_content</div></th>
                            <th data-field="_elementor_data" data-type="text" class="column_wp_postmeta_meta_key_elementor_data row_obtain_db_column"><div class="tcell_inner_wrapper_div"><strong>meta_key:_elementor_data</strong></div></th>
                            <th data-field="post_type" data-type="text" class="column_wp_posts_post_type row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_type</div></th>
                            <th data-field="post_date" data-type="datetime" class="column_wp_posts_post_date row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_date</div></th>
                            <th data-field="post_modified" data-type="datetime" class="column_wp_posts_post_modified row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_modified</div></th>
                            <th data-field="post_author" data-type="integer" class="column_wp_posts_post_author row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_author</div></th>
                            <th data-field="post_parent" data-type="integer" class="column_wp_posts_post_parent row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_parent</div></th>
                            <th data-field="menu_order" data-type="integer" class="column_wp_posts_menu_order row_obtain_db_column"><div class="tcell_inner_wrapper_div">menu_order</div></th>
                            <th data-field="comment_status" data-type="text" class="column_wp_posts_comment_status row_obtain_db_column"><div class="tcell_inner_wrapper_div">comment_status</div></th>
                            <th data-field="ping_status" data-type="text" class="column_wp_posts_ping_status row_obtain_db_column"><div class="tcell_inner_wrapper_div">ping_status</div></th>
                            <th data-field="rel_wp_post_id" data-type="integer" class="column_zen_orbitposts_rel_wp_post_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">rel_wp_post_id</div></th>
                            <th data-field="orbitpost_id" data-type="integer" class="column_zen_orbitposts_orbitpost_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">orbitpost_id</div></th>
                            <th data-field="redshift_datum" data-type="text" class="column_zen_orbitposts_redshift_datum row_obtain_db_column"><div class="tcell_inner_wrapper_div">redshift_datum</div></th>
                            <th data-field="rover_datum" data-type="text" class="column_zen_orbitposts_rover_datum row_obtain_db_column"><div class="tcell_inner_wrapper_div">rover_datum</div></th>
                            <th data-field="hudson_imgplanbatch_id" data-type="integer" class="column_zen_orbitposts_hudson_imgplanbatch_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">hudson_imgplanbatch_id</div></th>
                            <th data-field="is_pinned" data-type="boolean" class="column_zen_orbitposts_is_pinned row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_pinned</div></th>
                            <th data-field="is_flagged" data-type="boolean" class="column_zen_orbitposts_is_flagged row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_flagged</div></th>
                            <th data-field="is_starred" data-type="boolean" class="column_zen_orbitposts_is_starred row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_starred</div></th>
                            <th data-field="is_squared" data-type="boolean" class="column_zen_orbitposts_is_squared row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_squared</div></th>
                            <th data-field="created_at" data-type="datetime" class="column_zen_orbitposts_created_at row_obtain_db_column"><div class="tcell_inner_wrapper_div">created_at</div></th>
                            <th data-field="updated_at" data-type="datetime" class="column_zen_orbitposts_updated_at row_obtain_db_column"><div class="tcell_inner_wrapper_div">updated_at</div></th>
                        </tr>
                    </thead>
                    <tbody id="beamraymar-tbody">
                        <?php snefuru_render_beamraymar_table_rows($posts_pages); ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        
        <!-- Bottom Controls -->
        <div class="beamraymar-bottom-controls">
            <!-- Content will be added in next steps -->
        </div>
        
        <!-- Modals -->
        <!-- Content will be added in next steps -->
        
        <script>
            // This will contain all the JavaScript from the original method
            // Content will be added in next steps
        </script>
    </div>
    <?php
}

/**
 * Helper function to suppress admin notices
 */
function snefuru_suppress_all_admin_notices() {
    // Implementation will be added
}

/**
 * Helper function to handle AJAX requests
 */
function snefuru_handle_beamraymar_ajax() {
    // Implementation will be added
}

/**
 * Helper function to get posts and pages data
 */
function snefuru_get_posts_and_pages_data() {
    // Implementation will be added
    return array();
}