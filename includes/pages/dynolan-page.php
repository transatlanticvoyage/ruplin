<?php
/**
 * Independent dynolan page
 * Extracted from class-admin.php for better maintainability
 */

if (!defined('ABSPATH')) {
    exit;
}

// Make sure we can access WordPress functions and globals
global $wpdb;

/**
 * Main dynolan page function - completely independent
 */
function snefuru_dynolan_page() {
    // AGGRESSIVE NOTICE SUPPRESSION
    snefuru_suppress_all_admin_notices();
    
    // Handle AJAX requests
    if (isset($_POST['action']) && in_array($_POST['action'], ['create_new_post', 'update_post_field', 'create', 'edit'])) {
        snefuru_handle_dynolan_ajax();
        return;
    }
    
    // Get posts and pages data
    $posts_pages = snefuru_get_posts_and_pages_data();
    
    ?>
    <div class="wrap dynolan-wrapper">
        <style>
            /* Beamraymar Custom Styles - Mimicking FileJar Design */
            .dynolan-wrapper {
                background: white;
                padding: 0;
                margin: 0 0 0 -20px;
            }
            
            .dynolan-top-controls {
                background-color: white;
                border-bottom: 1px solid #ddd;
                padding: 12px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 16px;
            }
            
            .dynolan-controls-left {
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
            
            .dynolan-controls-right {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            
            .dynolan-info-text {
                font-size: 14px;
                color: #666;
            }
            
            .dynolan-pagination-controls {
                display: flex;
                align-items: center;
            }
            
            .dynolan-pagination-bar {
                display: flex;
            }
            
            .dynolan-pagination-btn {
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
            
            .dynolan-pagination-btn:hover {
                background-color: #f5f5f5;
            }
            
            .dynolan-pagination-btn.active {
                background-color: #0073aa;
                color: white;
            }
            
            .dynolan-pagination-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .dynolan-pagination-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            .dynolan-pagination-divider {
                width: 1px;
                height: 20px;
                background: #ddd;
                margin: 0 12px;
            }
            
            .dynolan-search-container {
                position: relative;
            }
            
            .dynolan-search-input {
                width: 320px;
                padding: 8px 40px 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .dynolan-clear-btn {
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
            
            .dynolan-clear-btn:hover {
                background: #ffed4e;
            }
            
            .dynolan-create-buttons {
                display: flex;
                gap: 8px;
            }
            
            .dynolan-create-btn {
                padding: 8px 16px;
                font-size: 14px;
                font-weight: 600;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
            }
            
            .dynolan-create-inline-post {
                background: #16a085;
                color: white;
            }
            
            .dynolan-create-inline-post:hover {
                background: #138d75;
            }
            
            .dynolan-create-inline-page {
                background: #2980b9;
                color: white;
            }
            
            .dynolan-create-inline-page:hover {
                background: #2471a3;
            }
            
            .dynolan-create-popup {
                background: #8e44ad;
                color: white;
            }
            
            .dynolan-create-popup:hover {
                background: #7d3c98;
            }
            
            .dynolan-nubra-kite {
                background: #f39c12;
                color: black;
                padding: 6px 12px;
                border: 1px solid black;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            
            .dynolan-table-container {
                background: white;
                overflow: hidden;
            }
            
            .dynolan-table-scroll {
                overflow-x: auto;
            }
            
            .dynolan-table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid #ddd;
                min-width: 1600px;
            }
            
            .dynolan-table thead {
                background: #f8f9fa;
            }
            
            .dynolan-table th,
            .dynolan-table td {
                border: 1px solid #ddd;
                padding: 8px 12px;
                text-align: left;
                font-size: 14px;
            }
            
            .dynolan-table th {
                font-weight: bold;
                font-size: 12px;
                text-transform: lowercase;
                cursor: pointer;
                position: relative;
            }
            
            .dynolan-table th:hover {
                background: #e9ecef;
            }
            
            .dynolan-table tbody tr:hover {
                background: #f8f9fa;
            }
            
            .dynolan-checkbox-cell {
                width: 40px;
                text-align: center;
                cursor: pointer;
            }
            
            .dynolan-checkbox {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
            
            .dynolan-editable-cell {
                cursor: pointer;
                min-height: 20px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            
            .dynolan-editable-cell:hover {
                background: #f0f8ff;
                outline: 1px solid #cce7ff;
            }
            
            .dynolan-editing-input {
                width: 100%;
                padding: 4px 6px;
                border: 2px solid #0073aa;
                border-radius: 3px;
                font-size: 14px;
                background: white;
            }
            
            .dynolan-editing-textarea {
                width: 100%;
                padding: 4px 6px;
                border: 2px solid #0073aa;
                border-radius: 3px;
                font-size: 14px;
                background: white;
                resize: none;
                min-height: 60px;
            }
            
            .dynolan-toggle-switch {
                width: 48px;
                height: 24px;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s;
                position: relative;
                display: inline-block;
            }
            
            .dynolan-toggle-switch.on {
                background: #16a085;
            }
            
            .dynolan-toggle-switch.off {
                background: #bdc3c7;
            }
            
            .dynolan-toggle-handle {
                width: 20px;
                height: 20px;
                background: white;
                border-radius: 50%;
                position: absolute;
                top: 2px;
                transition: transform 0.3s;
                box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            }
            
            .dynolan-toggle-switch.on .dynolan-toggle-handle {
                transform: translateX(24px);
            }
            
            .dynolan-toggle-switch.off .dynolan-toggle-handle {
                transform: translateX(2px);
            }
            
            .dynolan-sort-indicator {
                margin-left: 8px;
                color: #666;
            }
            
            .dynolan-loading {
                text-align: center;
                padding: 40px;
                font-size: 16px;
                color: #666;
            }
            
            /* Bottom controls */
            .dynolan-bottom-controls {
                background-color: white;
                border-top: 1px solid #ddd;
                padding: 12px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            /* Modal styles */
            .dynolan-modal {
                display: none;
                position: fixed;
                z-index: 999999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
            }
            
            .dynolan-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .dynolan-modal-content {
                background: white;
                padding: 30px;
                border-radius: 6px;
                max-width: 800px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .dynolan-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 15px;
            }
            
            .dynolan-modal-title {
                font-size: 20px;
                font-weight: bold;
                margin: 0;
            }
            
            .dynolan-modal-close {
                font-size: 24px;
                background: none;
                border: none;
                cursor: pointer;
                color: #666;
            }
            
            .dynolan-modal-close:hover {
                color: #333;
            }
            
            .dynolan-form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .dynolan-form-field {
                display: flex;
                flex-direction: column;
            }
            
            .dynolan-form-field.full-width {
                grid-column: 1 / -1;
            }
            
            .dynolan-form-label {
                font-weight: 600;
                margin-bottom: 6px;
                color: #333;
            }
            
            .dynolan-form-input,
            .dynolan-form-textarea,
            .dynolan-form-select {
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .dynolan-form-textarea {
                min-height: 100px;
                resize: vertical;
            }
            
            .dynolan-modal-actions {
                display: flex;
                justify-content: flex-end;
                gap: 12px;
                margin-top: 24px;
            }
            
            .dynolan-modal-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .dynolan-modal-btn.primary {
                background: #0073aa;
                color: white;
            }
            
            .dynolan-modal-btn.primary:hover {
                background: #005a87;
            }
            
            .dynolan-modal-btn.secondary {
                background: #f1f1f1;
                color: #333;
                border: 1px solid #ddd;
            }
            
            .dynolan-modal-btn.secondary:hover {
                background: #e0e0e0;
            }
            
            .dynolan-table td .tcell_inner_wrapper_div {
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
            
            .dynolan-table td.column_wp_posts_post_title {
                font-size: 18px;
                font-weight: bold;
            }
            
            .dynolan-table td.column_wp_posts_post_status[data-status="publish"] {
                background-color: #efddbb;
            }
            
            /* Column pagination styles */
            .dynolan-column-pagination-controls {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                margin-left: 15px;
            }
            
            .dynolan-column-pagination-bar {
                display: flex;
            }
            
            .dynolan-column-pagination-btn {
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
            
            .dynolan-column-pagination-btn:hover {
                background-color: #f5f5f5;
            }
            
            .dynolan-column-pagination-btn.active {
                background-color: #ffd700;
                color: black;
            }
            
            .dynolan-column-pagination-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .dynolan-column-pagination-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            /* Filter button bars */
            .dynolan-filter-controls {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                margin-left: 15px;
            }
            
            .dynolan-filter-bar {
                display: flex;
            }
            
            .dynolan-filter-btn {
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
            
            .dynolan-filter-btn:hover {
                background-color: #f5f5f5;
            }
            
            .dynolan-filter-btn.active {
                background-color: #0073aa;
                color: white;
            }
            
            .dynolan-filter-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .dynolan-filter-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            /* ED button for post_content */
            .dynolan-content-edit-btn {
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
            
            .dynolan-content-edit-btn:hover {
                background: #005a87;
            }
            
            /* Post content editor popup */
            .dynolan-content-editor-modal {
                display: none;
                position: fixed;
                z-index: 999999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
            }
            
            .dynolan-content-editor-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .dynolan-content-editor-content {
                background: white;
                padding: 30px;
                border-radius: 6px;
                width: 90%;
                height: 85%;
                display: flex;
                flex-direction: column;
            }
            
            .dynolan-content-editor-header {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 15px;
            }
            
            .dynolan-content-editor-textarea {
                width: 100%;
                flex: 1;
                padding: 10px;
                border: 1px solid #ddd;
                font-family: monospace;
                font-size: 14px;
                resize: none;
            }
            
            .dynolan-content-editor-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 15px;
            }
            
            .dynolan-content-editor-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 600;
            }
            
            .dynolan-content-editor-btn.save {
                background: #0073aa;
                color: white;
            }
            
            .dynolan-content-editor-btn.cancel {
                background: #f1f1f1;
                color: #333;
            }
            
            /* Tool button styling */
            .dynolan-tool-btn {
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
            
            .dynolan-tool-btn:hover {
                background: #005a87;
                color: white;
                text-decoration: none;
            }
            
            /* Pendulum button styling */
            .dynolan-pendulum-btn {
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
            
            .dynolan-pendulum-btn:hover {
                background: #005a87;
                color: white;
                text-decoration: none;
            }
            
            /* Elementor button styling */
            .dynolan-elementor-btn {
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
            
            .dynolan-elementor-btn:hover {
                background: #5c0016;
                color: white;
                text-decoration: none;
            }
            
            .dynolan-elementor-btn.disabled {
                background: #ccc;
                color: #999;
                cursor: not-allowed;
                pointer-events: none;
            }
            
            /* C1, C2, C3 button styling */
            .dynolan-c-btn {
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
        <div class="dynolan-top-controls">
            <div class="dynolan-controls-left">
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
                <div class="dynolan-info-text">
                    <span id="dynolan-results-info">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
                
                <div class="dynolan-pagination-controls">
                    <div class="dynolan-pagination-bar" id="dynolan-per-page-bar">
                        <a href="#" class="dynolan-pagination-btn" data-per-page="10">10</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="20">20</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="50">50</a>
                        <a href="#" class="dynolan-pagination-btn active" data-per-page="100">100</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="200">200</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="500">500</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="all">All</a>
                    </div>
                    
                    <div class="dynolan-pagination-divider"></div>
                    
                    <div class="dynolan-pagination-bar" id="dynolan-page-bar">
                        <a href="#" class="dynolan-pagination-btn" data-page="first">First</a>
                        <a href="#" class="dynolan-pagination-btn" data-page="prev">Prev</a>
                        <a href="#" class="dynolan-pagination-btn active" data-page="1">1</a>
                        <a href="#" class="dynolan-pagination-btn" data-page="next">Next</a>
                        <a href="#" class="dynolan-pagination-btn" data-page="last">Last</a>
                    </div>
                </div>
                
                <div class="dynolan-search-container">
                    <input type="text" id="dynolan-search" class="dynolan-search-input" placeholder="Search all fields...">
                    <button class="dynolan-clear-btn" id="dynolan-clear">CL</button>
                </div>
            </div>
            
            <div class="dynolan-controls-right">
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <?php
                    global $wpdb;
                    $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren WHERE wppma_id = 1");
                    ?>
                    <div style="font-size: 16px; font-weight: bold; text-transform: lowercase; margin-bottom: 10px;">
                        <span style="font-weight: bold;"><?php echo esc_html($wpdb->prefix); ?></span>zen_sitespren.sitespren_base: <?php echo esc_html($sitespren_base ?: ''); ?>
                    </div>
                    <div class="dynolan-create-buttons">
                        <button class="dynolan-create-btn dynolan-create-inline-post" id="create-post-inline">Create New (Inline)</button>
                        <button class="dynolan-create-btn dynolan-create-inline-page" id="create-page-inline">Create New (Inline) WP Page</button>
                        <button class="dynolan-create-btn dynolan-create-popup" id="create-popup">Create New (Popup)</button>
                        <button class="dynolan-create-btn dynolan-duplicate-btn" id="duplicate-selected">Duplicate</button>
                    </div>
                </div>
                <div class="dynolan-nubra-kite">nubra-tableface-kite</div>
                
                <!-- Column Pagination Controls -->
                <div class="dynolan-column-pagination-controls">
                    <div class="dynolan-column-pagination-bar" id="dynolan-column-group-bar">
                        <a href="#" class="dynolan-column-pagination-btn active" data-column-group="1">Columns 1-7</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-group="2">Columns 8-14</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-group="3">Columns 15-21</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-group="4">Columns 22-28</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-group="5">Columns 29-35</a>
                    </div>
                    
                    <div class="dynolan-column-pagination-bar" id="dynolan-column-nav-bar">
                        <a href="#" class="dynolan-column-pagination-btn" data-column-nav="first">First</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-nav="prev">Prev</a>
                        <a href="#" class="dynolan-column-pagination-btn active" data-column-nav="1">1</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-nav="2">2</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-nav="3">3</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-nav="4">4</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-nav="5">5</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-nav="next">Next</a>
                        <a href="#" class="dynolan-column-pagination-btn" data-column-nav="last">Last</a>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                <div class="dynolan-filter-controls">
                    <div class="dynolan-filter-bar" id="dynolan-post-type-filter">
                        <a href="#" class="dynolan-filter-btn" data-filter-type="post_type" data-filter-value="page">Page</a>
                        <a href="#" class="dynolan-filter-btn" data-filter-type="post_type" data-filter-value="post">Post</a>
                    </div>
                    
                    <div class="dynolan-filter-bar" id="dynolan-post-status-filter">
                        <a href="#" class="dynolan-filter-btn" data-filter-type="post_status" data-filter-value="publish">Published</a>
                        <a href="#" class="dynolan-filter-btn" data-filter-type="post_status" data-filter-value="draft">Draft</a>
                    </div>
                </div>
            </div>
        </div>
        </div>
        
        <!-- Table Container -->
        <div class="dynolan-table-container">
            <div class="dynolan-table-scroll">
                <table class="dynolan-table utg_beamray" id="dynolan-table">
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
                        <tr class="dynolan-main-header-row">
                            <th class="dynolan-checkbox-cell column_checkbox row_obtain_db_column">
                                <div class="tcell_inner_wrapper_div"><input type="checkbox" class="dynolan-checkbox" id="select-all"></div>
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
                    <tbody id="dynolan-tbody">
                        <?php snefuru_render_dynolan_table_rows($posts_pages); ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        
        <!-- Bottom Controls -->
        <div class="dynolan-bottom-controls">
            <div class="dynolan-controls-left">
                <div class="dynolan-info-text">
                    <span id="dynolan-results-info-bottom">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
                
                <div class="dynolan-pagination-controls">
                    <div class="dynolan-pagination-bar" id="dynolan-per-page-bar-bottom">
                        <a href="#" class="dynolan-pagination-btn" data-per-page="10">10</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="20">20</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="50">50</a>
                        <a href="#" class="dynolan-pagination-btn active" data-per-page="100">100</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="200">200</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="500">500</a>
                        <a href="#" class="dynolan-pagination-btn" data-per-page="all">All</a>
                    </div>
                    
                    <div class="dynolan-pagination-divider"></div>
                    
                    <div class="dynolan-pagination-bar" id="dynolan-page-bar-bottom">
                        <a href="#" class="dynolan-pagination-btn" data-page="first">First</a>
                        <a href="#" class="dynolan-pagination-btn" data-page="prev">Prev</a>
                        <a href="#" class="dynolan-pagination-btn active" data-page="1">1</a>
                        <a href="#" class="dynolan-pagination-btn" data-page="next">Next</a>
                        <a href="#" class="dynolan-pagination-btn" data-page="last">Last</a>
                    </div>
                </div>
                
                <div class="dynolan-search-container">
                    <input type="text" id="dynolan-search-bottom" class="dynolan-search-input" placeholder="Search all fields...">
                    <button class="dynolan-clear-btn" id="dynolan-clear-bottom">CL</button>
                </div>
            </div>
            <div></div>
        </div>
        </div>
        
        <!-- Modals -->
        <!-- Create/Edit Modal -->
        <div id="dynolan-modal" class="dynolan-modal">
            <div class="dynolan-modal-content">
                <div class="dynolan-modal-header">
                    <h2 class="dynolan-modal-title" id="dynolan-modal-title">Create New Post/Page</h2>
                    <button class="dynolan-modal-close" id="dynolan-modal-close">&times;</button>
                </div>
                
                <form id="dynolan-modal-form">
                    <input type="hidden" id="modal-post-id" name="post_id">
                    <input type="hidden" id="modal-action" name="action" value="create">
                    
                    <div class="dynolan-form-grid">
                        <div class="dynolan-form-field">
                            <label class="dynolan-form-label">Post Type</label>
                            <select id="modal-post-type" name="post_type" class="dynolan-form-select">
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                            </select>
                        </div>
                        
                        <div class="dynolan-form-field">
                            <label class="dynolan-form-label">Status</label>
                            <select id="modal-post-status" name="post_status" class="dynolan-form-select">
                                <option value="draft">Draft</option>
                                <option value="publish">Published</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                        
                        <div class="dynolan-form-field full-width">
                            <label class="dynolan-form-label">Title</label>
                            <input type="text" id="modal-post-title" name="post_title" class="dynolan-form-input">
                        </div>
                        
                        <div class="dynolan-form-field full-width">
                            <label class="dynolan-form-label">Content</label>
                            <textarea id="modal-post-content" name="post_content" class="dynolan-form-textarea"></textarea>
                        </div>
                        
                        <div class="dynolan-form-field">
                            <label class="dynolan-form-label">Slug</label>
                            <input type="text" id="modal-post-name" name="post_name" class="dynolan-form-input">
                        </div>
                        
                        <div class="dynolan-form-field">
                            <label class="dynolan-form-label">Parent ID</label>
                            <input type="number" id="modal-post-parent" name="post_parent" class="dynolan-form-input" value="0">
                        </div>
                    </div>
                    
                    <div class="dynolan-modal-actions">
                        <button type="button" class="dynolan-modal-btn secondary" id="dynolan-modal-cancel">Cancel</button>
                        <button type="submit" class="dynolan-modal-btn primary" id="dynolan-modal-save">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Post Content Editor Modal -->
        <div id="dynolan-content-editor-modal" class="dynolan-content-editor-modal">
            <div class="dynolan-content-editor-content">
                <div class="dynolan-content-editor-header">post_content</div>
                <textarea id="dynolan-content-editor-textarea" class="dynolan-content-editor-textarea"></textarea>
                <div class="dynolan-content-editor-actions">
                    <button type="button" class="dynolan-content-editor-btn cancel" id="dynolan-content-editor-cancel">Cancel</button>
                    <button type="button" class="dynolan-content-editor-btn save" id="dynolan-content-editor-save">Save</button>
                </div>
            </div>
        </div>

        <!-- Elementor Data Editor Modal -->
        <div id="dynolan-elementor-editor-modal" class="dynolan-content-editor-modal">
            <div class="dynolan-content-editor-content">
                <div class="dynolan-content-editor-header">_elementor_data</div>
                <textarea id="dynolan-elementor-editor-textarea" class="dynolan-content-editor-textarea"></textarea>
                <div class="dynolan-content-editor-actions">
                    <button type="button" class="dynolan-content-editor-btn cancel" id="dynolan-elementor-editor-cancel">Cancel</button>
                    <button type="button" class="dynolan-content-editor-btn save" id="dynolan-elementor-editor-save">Save</button>
                </div>
            </div>
        </div>
        
        <script>
            // Beamraymar JavaScript functionality
            (function($) {
                'use strict';
                
                let currentPage = 1;
                let itemsPerPage = 100;
                let currentSearch = '';
                let allData = <?php echo json_encode($posts_pages); ?>;
                let filteredData = [...allData];
                let selectedRows = new Set();
                let editingCell = null;
                let currentContentEditPostId = null;
                let currentElementorEditPostId = null;
                
                // Initialize page
                $(document).ready(function() {
                    initializeBeamraymar();
                });
                
                function initializeBeamraymar() {
                    bindEventHandlers();
                    renderTable();
                    updatePaginationInfo();
                }
                
                function bindEventHandlers() {
                    // Pagination buttons
                    $(document).on('click', '.dynolan-pagination-btn', function(e) {
                        e.preventDefault();
                        const $this = $(this);
                        
                        // Handle per-page buttons
                        if ($this.data('per-page')) {
                            itemsPerPage = $this.data('per-page') === 'all' ? filteredData.length : parseInt($this.data('per-page'));
                            currentPage = 1;
                            $('.dynolan-pagination-btn[data-per-page]').removeClass('active');
                            $this.addClass('active');
                            renderTable();
                            updatePaginationInfo();
                        }
                        
                        // Handle page navigation
                        if ($this.data('page')) {
                            const pageAction = $this.data('page');
                            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                            
                            switch(pageAction) {
                                case 'first':
                                    currentPage = 1;
                                    break;
                                case 'prev':
                                    currentPage = Math.max(1, currentPage - 1);
                                    break;
                                case 'next':
                                    currentPage = Math.min(totalPages, currentPage + 1);
                                    break;
                                case 'last':
                                    currentPage = totalPages;
                                    break;
                                default:
                                    currentPage = parseInt(pageAction);
                            }
                            renderTable();
                            updatePaginationInfo();
                        }
                    });
                    
                    // Search functionality
                    $(document).on('input', '.dynolan-search-input', function() {
                        currentSearch = $(this).val().toLowerCase();
                        filterData();
                        currentPage = 1;
                        renderTable();
                        updatePaginationInfo();
                    });
                    
                    // Clear search buttons
                    $(document).on('click', '.dynolan-clear-btn', function(e) {
                        e.preventDefault();
                        $('.dynolan-search-input').val('');
                        currentSearch = '';
                        filterData();
                        currentPage = 1;
                        renderTable();
                        updatePaginationInfo();
                    });
                }
                
                function filterData() {
                    if (!currentSearch) {
                        filteredData = [...allData];
                        return;
                    }
                    
                    filteredData = allData.filter(item => {
                        return Object.values(item).some(value => 
                            String(value).toLowerCase().includes(currentSearch)
                        );
                    });
                }
                
                function renderTable() {
                    const start = (currentPage - 1) * itemsPerPage;
                    const end = itemsPerPage === filteredData.length ? filteredData.length : start + itemsPerPage;
                    const pageData = filteredData.slice(start, end);
                    
                    // Basic table rendering would go here
                    // For now just log to prevent errors
                    console.log('Rendering', pageData.length, 'items on page', currentPage);
                }
                
                function updatePaginationInfo() {
                    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                    const start = (currentPage - 1) * itemsPerPage + 1;
                    const end = Math.min(currentPage * itemsPerPage, filteredData.length);
                    
                    // Update pagination display
                    $('.dynolan-pagination-btn[data-page]').removeClass('active');
                    $(`.dynolan-pagination-btn[data-page="${currentPage}"]`).addClass('active');
                }
                
            })(jQuery);
        </script>
    </div>
    <?php
}

/**
 * Helper function to suppress admin notices
 */
function snefuru_suppress_all_admin_notices() {
    // Remove all admin notices immediately
    add_action('admin_print_styles', function() {
        // Remove all notice actions
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        
        // Remove user admin notices
        global $wp_filter;
        if (isset($wp_filter['user_admin_notices'])) {
            unset($wp_filter['user_admin_notices']);
        }
    }, 0);
    
    // Additional cleanup for persistent notices
    add_action('admin_head', function() {
        // Hide any notices that slip through via CSS
        echo '<style type="text/css">
            .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
            .updated, .error, .update-nag, .admin-notice,
            .wrap > .notice, .wrap > .error, .wrap > .updated {
                display: none !important;
            }
        </style>';
    });
}

/**
 * Helper function to handle AJAX requests
 */
function snefuru_handle_dynolan_ajax() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dynolan_nonce')) {
        echo json_encode(array('success' => false, 'message' => 'Security check failed'));
        wp_die();
    }

    $action = sanitize_text_field($_POST['action']);

    switch ($action) {
        case 'create_new_post':
            $post_data = array(
                'post_title' => sanitize_text_field($_POST['post_title']),
                'post_content' => wp_kses_post($_POST['post_content']),
                'post_status' => sanitize_text_field($_POST['post_status']),
                'post_type' => sanitize_text_field($_POST['post_type']),
                'post_name' => sanitize_text_field($_POST['post_name']),
                'post_parent' => intval($_POST['post_parent'])
            );

            $post_id = wp_insert_post($post_data);
            if ($post_id && !is_wp_error($post_id)) {
                echo json_encode(array('success' => true, 'message' => 'Post created successfully', 'post_id' => $post_id));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to create post'));
            }
            break;

        case 'update_post_field':
            $post_id = intval($_POST['post_id']);
            $field = sanitize_text_field($_POST['field']);
            $value = sanitize_textarea_field($_POST['value']);

            $update_data = array(
                'ID' => $post_id,
                $field => $value
            );

            $result = wp_update_post($update_data);
            if ($result && !is_wp_error($result)) {
                echo json_encode(array('success' => true, 'message' => 'Field updated successfully'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to update field'));
            }
            break;

        default:
            echo json_encode(array('success' => false, 'message' => 'Invalid action'));
    }
    
    wp_die();
}

/**
 * Helper function to get posts and pages data
 */
function snefuru_get_posts_and_pages_data() {
    global $wpdb;
    
    $results = $wpdb->get_results(
        "SELECT p.ID, p.post_title, p.post_content, p.post_type, p.post_status, p.post_name, 
                p.post_date, p.post_modified, p.post_author, p.post_parent, p.menu_order,
                p.comment_status, p.ping_status, pm._elementor_data,
                op.rel_wp_post_id, op.orbitpost_id, op.redshift_datum, op.rover_datum,
                op.hudson_imgplanbatch_id, op.is_pinned, op.is_flagged, op.is_starred, op.is_squared,
                op.created_at, op.updated_at
         FROM {$wpdb->prefix}posts p
         LEFT JOIN (
             SELECT post_id, meta_value as _elementor_data
             FROM {$wpdb->prefix}postmeta
             WHERE meta_key = '_elementor_data'
         ) pm ON p.ID = pm.post_id
         LEFT JOIN {$wpdb->prefix}zen_orbitposts op ON p.ID = op.rel_wp_post_id
         WHERE p.post_type IN ('post', 'page')
         ORDER BY p.post_modified DESC",
        ARRAY_A
    );
    
    return $results ?: array();
}

/**
 * Helper function to render dynolan table rows
 */
function snefuru_render_dynolan_table_rows($posts_pages) {
    if (empty($posts_pages)) {
        echo '<tr><td colspan="28" class="dynolan-loading">No posts or pages found.</td></tr>';
        return;
    }

    $count = 0;
    foreach ($posts_pages as $item) {
        if ($count >= 100) break; // Limit initial display
        
        $post_id = $item['ID'];
        $content_preview = wp_trim_words(strip_tags($item['post_content'] ?: ''), 10, '...');
        
        echo '<tr data-post-id="' . esc_attr($post_id) . '">';
        
        // Checkbox
        echo '<td class="dynolan-checkbox-cell column_checkbox"><div class="tcell_inner_wrapper_div">';
        echo '<input type="checkbox" class="dynolan-checkbox" value="' . esc_attr($post_id) . '">';
        echo '</div></td>';
        
        // Tool buttons
        echo '<td class="readonly-cell column_tool_buttons"><div class="tcell_inner_wrapper_div">';
        echo '<a href="' . get_edit_post_link($post_id) . '" class="dynolan-tool-btn" title="Edit">ED</a>';
        echo '<a href="' . get_permalink($post_id) . '" class="dynolan-pendulum-btn" title="View">VW</a>';
        if ($item['_elementor_data']) {
            echo '<button class="dynolan-elementor-btn" data-post-id="' . esc_attr($post_id) . '" title="Elementor">EL</button>';
        } else {
            echo '<button class="dynolan-elementor-btn disabled" title="No Elementor Data">EL</button>';
        }
        echo '<button class="dynolan-c-btn" title="C1">C1</button>';
        echo '<button class="dynolan-c-btn" title="C2">C2</button>';
        echo '<button class="dynolan-c-btn" title="C3">C3</button>';
        echo '</div></td>';
        
        // ID
        echo '<td class="readonly-cell column_wp_posts_id"><div class="tcell_inner_wrapper_div">' . esc_html($post_id) . '</div></td>';
        
        // Status  
        echo '<td class="dynolan-editable-cell column_wp_posts_post_status" data-field="post_status" data-type="text" data-status="' . esc_attr($item['post_status']) . '">';
        echo '<div class="tcell_inner_wrapper_div">' . esc_html($item['post_status']) . '</div></td>';
        
        // Title
        echo '<td class="dynolan-editable-cell column_wp_posts_post_title" data-field="post_title" data-type="text"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_title']) . '</div></td>';
        
        // Name/Slug
        echo '<td class="dynolan-editable-cell column_wp_posts_post_name" data-field="post_name" data-type="text"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_name']) . '</div></td>';
        
        // Replex Submit (empty UI column)
        echo '<td class="readonly-cell column_replex_submit"><div class="tcell_inner_wrapper_div"></div></td>';
        
        // Content with ED button
        echo '<td class="readonly-cell column_wp_posts_post_content"><div class="tcell_inner_wrapper_div">' . esc_html($content_preview) . '<button class="dynolan-content-edit-btn" data-post-id="' . esc_attr($post_id) . '">ED</button></div></td>';
        
        // Add remaining columns with basic data
        $remaining_fields = [
            '_elementor_data', 'post_type', 'post_date', 'post_modified', 'post_author', 
            'post_parent', 'menu_order', 'comment_status', 'ping_status',
            'rel_wp_post_id', 'orbitpost_id', 'redshift_datum', 'rover_datum', 
            'hudson_imgplanbatch_id', 'is_pinned', 'is_flagged', 'is_starred', 
            'is_squared', 'created_at', 'updated_at'
        ];
        
        foreach ($remaining_fields as $field) {
            $value = $item[$field] ?? '';
            if (in_array($field, ['is_pinned', 'is_flagged', 'is_starred', 'is_squared'])) {
                $value = $value ? '1' : '0';
            } elseif ($field === '_elementor_data') {
                $value = $value ? 'Yes' : 'No';
            }
            echo '<td class="readonly-cell"><div class="tcell_inner_wrapper_div">' . esc_html($value) . '</div></td>';
        }
        
        echo '</tr>';
        $count++;
    }
}