<?php
/**
 * Independent DynoMar Page - Complete BeamRayMar Feature Parity
 * Completely rebuilt to match BeamRayMar functionality with shared CSS
 */

if (!defined('ABSPATH')) {
    exit;
}

// Make sure we can access WordPress functions and globals
global $wpdb;

/**
 * Main dynolan page function - completely independent with full BeamRayMar parity
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
    <link rel="stylesheet" href="<?php echo SNEFURU_PLUGIN_URL; ?>assets/css/shared-table-styles.css">
    <div class="wrap table-wrapper">
        <!-- Top Controls -->
        <div class="top-controls">
            <div class="controls-left">
                <div class="banner-title">
                    <svg class="table-logo" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L4 7v10l8 5 8-5V7l-8-5zm0 2.18L18.18 8 12 11.82 5.82 8 12 4.18zM6 9.42l5 2.5v8.16l-5-2.5V9.42zm12 0v8.16l-5 2.5v-8.16l5-2.5z"/>
                        <circle cx="12" cy="8" r="1.5" fill="white"/>
                        <line x1="12" y1="8" x2="12" y2="16" stroke="white" stroke-width="1" opacity="0.6"/>
                        <line x1="8" y1="10" x2="16" y2="14" stroke="white" stroke-width="0.5" opacity="0.4"/>
                        <line x1="16" y1="10" x2="8" y2="14" stroke="white" stroke-width="0.5" opacity="0.4"/>
                    </svg>
                    DynoMar Table
                </div>
                <div class="info-text">
                    <span id="dynolan-results-info">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
                
                <div class="pagination-controls">
                    <div class="pagination-bar" id="dynolan-per-page-bar">
                        <a href="#" class="pagination-btn" data-per-page="10">10</a>
                        <a href="#" class="pagination-btn" data-per-page="20">20</a>
                        <a href="#" class="pagination-btn" data-per-page="50">50</a>
                        <a href="#" class="pagination-btn active" data-per-page="100">100</a>
                        <a href="#" class="pagination-btn" data-per-page="200">200</a>
                        <a href="#" class="pagination-btn" data-per-page="500">500</a>
                        <a href="#" class="pagination-btn" data-per-page="all">All</a>
                    </div>
                    
                    <div class="pagination-divider"></div>
                    
                    <div class="pagination-bar" id="dynolan-page-bar">
                        <a href="#" class="pagination-btn" data-page="first">First</a>
                        <a href="#" class="pagination-btn" data-page="prev">Prev</a>
                        <a href="#" class="pagination-btn active" data-page="1">1</a>
                        <a href="#" class="pagination-btn" data-page="next">Next</a>
                        <a href="#" class="pagination-btn" data-page="last">Last</a>
                    </div>
                </div>
                
                <div class="search-container">
                    <input type="text" id="dynolan-search" class="search-input" placeholder="Search all fields...">
                    <button class="clear-btn" id="dynolan-clear">CL</button>
                </div>
            </div>
            
            <div class="controls-right">
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <?php
                    global $wpdb;
                    $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren WHERE wppma_id = 1");
                    ?>
                    <div style="font-size: 16px; font-weight: bold; text-transform: lowercase; margin-bottom: 10px;">
                        <span style="font-weight: bold;"><?php echo esc_html($wpdb->prefix); ?></span>zen_sitespren.sitespren_base: <?php echo esc_html($sitespren_base ?: ''); ?>
                    </div>
                    <div class="create-buttons">
                        <button class="create-btn create-inline-post" id="create-post-inline">Create New (Inline)</button>
                        <button class="create-btn create-inline-page" id="create-page-inline">Create New (Inline) WP Page</button>
                        <button class="create-btn create-popup" id="create-popup">Create New (Popup)</button>
                        <button class="create-btn" id="duplicate-selected">Duplicate</button>
                    </div>
                </div>
                <div class="nubra-kite">nubra-tableface-kite</div>
                
                <!-- Column Pagination Controls -->
                <div class="column-pagination-controls">
                    <div class="column-pagination-bar" id="dynolan-column-group-bar">
                        <a href="#" class="column-pagination-btn active" data-column-group="1">Columns 1-7</a>
                        <a href="#" class="column-pagination-btn" data-column-group="2">Columns 8-14</a>
                        <a href="#" class="column-pagination-btn" data-column-group="3">Columns 15-21</a>
                        <a href="#" class="column-pagination-btn" data-column-group="4">Columns 22-28</a>
                        <a href="#" class="column-pagination-btn" data-column-group="5">Columns 29-35</a>
                    </div>
                    
                    <div class="column-pagination-bar" id="dynolan-column-nav-bar">
                        <a href="#" class="column-pagination-btn" data-column-nav="first">First</a>
                        <a href="#" class="column-pagination-btn" data-column-nav="prev">Prev</a>
                        <a href="#" class="column-pagination-btn active" data-column-nav="1">1</a>
                        <a href="#" class="column-pagination-btn" data-column-nav="2">2</a>
                        <a href="#" class="column-pagination-btn" data-column-nav="3">3</a>
                        <a href="#" class="column-pagination-btn" data-column-nav="4">4</a>
                        <a href="#" class="column-pagination-btn" data-column-nav="5">5</a>
                        <a href="#" class="column-pagination-btn" data-column-nav="next">Next</a>
                        <a href="#" class="column-pagination-btn" data-column-nav="last">Last</a>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="filter-bar" id="dynolan-post-type-filter">
                        <a href="#" class="filter-btn" data-filter-type="post_type" data-filter-value="page">Page</a>
                        <a href="#" class="filter-btn" data-filter-type="post_type" data-filter-value="post">Post</a>
                    </div>
                    
                    <div class="filter-bar" id="dynolan-post-status-filter">
                        <a href="#" class="filter-btn" data-filter-type="post_status" data-filter-value="publish">Published</a>
                        <a href="#" class="filter-btn" data-filter-type="post_status" data-filter-value="draft">Draft</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-scroll">
                <table class="data-table utg_beamray" id="dynolan-table">
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
                        <tr class="main-header-row">
                            <th class="checkbox-cell column_checkbox row_obtain_db_column">
                                <div class="tcell_inner_wrapper_div"><input type="checkbox" class="row-checkbox" id="select-all"></div>
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

        <!-- Bottom Controls -->
        <div class="bottom-controls">
            <div class="controls-left">
                <div class="info-text">
                    <span id="dynolan-results-info-bottom">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
                
                <div class="pagination-controls">
                    <div class="pagination-bar" id="dynolan-per-page-bar-bottom">
                        <a href="#" class="pagination-btn" data-per-page="10">10</a>
                        <a href="#" class="pagination-btn" data-per-page="20">20</a>
                        <a href="#" class="pagination-btn" data-per-page="50">50</a>
                        <a href="#" class="pagination-btn active" data-per-page="100">100</a>
                        <a href="#" class="pagination-btn" data-per-page="200">200</a>
                        <a href="#" class="pagination-btn" data-per-page="500">500</a>
                        <a href="#" class="pagination-btn" data-per-page="all">All</a>
                    </div>
                    
                    <div class="pagination-divider"></div>
                    
                    <div class="pagination-bar" id="dynolan-page-bar-bottom">
                        <a href="#" class="pagination-btn" data-page="first">First</a>
                        <a href="#" class="pagination-btn" data-page="prev">Prev</a>
                        <a href="#" class="pagination-btn active" data-page="1">1</a>
                        <a href="#" class="pagination-btn" data-page="next">Next</a>
                        <a href="#" class="pagination-btn" data-page="last">Last</a>
                    </div>
                </div>
                
                <div class="search-container">
                    <input type="text" id="dynolan-search-bottom" class="search-input" placeholder="Search all fields...">
                    <button class="clear-btn" id="dynolan-clear-bottom">CL</button>
                </div>
            </div>
            <div></div>
        </div>

        <!-- Modals -->
        <!-- Create/Edit Modal -->
        <div id="dynolan-modal" class="table-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="dynolan-modal-title">Create New Post/Page</h2>
                    <button class="modal-close" id="dynolan-modal-close">&times;</button>
                </div>
                
                <form id="dynolan-modal-form">
                    <input type="hidden" id="modal-post-id" name="post_id">
                    <input type="hidden" id="modal-action" name="action" value="create">
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="form-label">Post Type</label>
                            <select id="modal-post-type" name="post_type" class="form-select">
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label class="form-label">Status</label>
                            <select id="modal-post-status" name="post_status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="publish">Published</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="form-label">Title</label>
                            <input type="text" id="modal-post-title" name="post_title" class="form-input">
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="form-label">Content</label>
                            <textarea id="modal-post-content" name="post_content" class="form-textarea"></textarea>
                        </div>
                        
                        <div class="form-field">
                            <label class="form-label">Slug</label>
                            <input type="text" id="modal-post-name" name="post_name" class="form-input">
                        </div>
                        
                        <div class="form-field">
                            <label class="form-label">Parent ID</label>
                            <input type="number" id="modal-post-parent" name="post_parent" class="form-input" value="0">
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn secondary" id="dynolan-modal-cancel">Cancel</button>
                        <button type="submit" class="modal-btn primary" id="dynolan-modal-save">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Post Content Editor Modal -->
        <div id="dynolan-content-editor-modal" class="content-editor-modal">
            <div class="content-editor-content">
                <div class="content-editor-header">post_content</div>
                <textarea id="dynolan-content-editor-textarea" class="content-editor-textarea"></textarea>
                <div class="content-editor-actions">
                    <button type="button" class="content-editor-btn cancel" id="dynolan-content-editor-cancel">Cancel</button>
                    <button type="button" class="content-editor-btn save" id="dynolan-content-editor-save">Save</button>
                </div>
            </div>
        </div>

        <!-- Elementor Data Editor Modal -->
        <div id="dynolan-elementor-editor-modal" class="content-editor-modal">
            <div class="content-editor-content">
                <div class="content-editor-header">_elementor_data</div>
                <textarea id="dynolan-elementor-editor-textarea" class="content-editor-textarea"></textarea>
                <div class="content-editor-actions">
                    <button type="button" class="content-editor-btn cancel" id="dynolan-elementor-editor-cancel">Cancel</button>
                    <button type="button" class="content-editor-btn save" id="dynolan-elementor-editor-save">Save</button>
                </div>
            </div>
        </div>
        
        <script>
            // DynoMar JavaScript functionality - Complete BeamRayMar parity
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
                
                // Column pagination variables
                let currentColumnGroup = 1;
                const columnsPerGroup = 7;
                
                // Initialize page
                $(document).ready(function() {
                    initializeEventHandlers();
                    updateTable();
                    updateColumnVisibility(); // Initialize column pagination
                    initializeIconButtons(); // Initialize icon button states
                });
                
                function initializeEventHandlers() {
                    // Pagination controls
                    $('.pagination-btn').on('click', function(e) {
                        e.preventDefault();
                        const $this = $(this);
                        
                        if ($this.data('per-page')) {
                            // Per page selection
                            const perPage = $this.data('per-page');
                            itemsPerPage = perPage === 'all' ? filteredData.length : parseInt(perPage);
                            currentPage = 1;
                            
                            $this.siblings('.pagination-btn').removeClass('active');
                            $this.addClass('active');
                            
                            // Update both top and bottom bars
                            $('[data-per-page="' + perPage + '"]').addClass('active').siblings().removeClass('active');
                        } else if ($this.data('page')) {
                            // Page navigation
                            const pageAction = $this.data('page');
                            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                            
                            switch(pageAction) {
                                case 'first':
                                    currentPage = 1;
                                    break;
                                case 'prev':
                                    if (currentPage > 1) currentPage--;
                                    break;
                                case 'next':
                                    if (currentPage < totalPages) currentPage++;
                                    break;
                                case 'last':
                                    currentPage = totalPages;
                                    break;
                                default:
                                    if (!isNaN(pageAction)) {
                                        currentPage = parseInt(pageAction);
                                    }
                            }
                        }
                        
                        updateTable();
                    });
                    
                    // Column pagination controls
                    $('.column-pagination-btn').on('click', function(e) {
                        e.preventDefault();
                        const $this = $(this);
                        
                        if ($this.data('column-group')) {
                            // Column group selection
                            const columnGroup = parseInt($this.data('column-group'));
                            setActiveColumnGroup(columnGroup);
                            
                            $this.siblings('.column-pagination-btn').removeClass('active');
                            $this.addClass('active');
                            
                        } else if ($this.data('column-nav')) {
                            // Column navigation
                            const navAction = $this.data('column-nav');
                            handleColumnNavigation(navAction);
                            
                            // Update active state for numbered buttons only
                            if (!isNaN(navAction)) {
                                $this.siblings('[data-column-nav]').removeClass('active');
                                $this.addClass('active');
                            }
                        }
                    });
                    
                    // Filter button controls
                    $('.filter-btn').on('click', function(e) {
                        e.preventDefault();
                        const $this = $(this);
                        
                        // Toggle active state
                        $this.toggleClass('active');
                        
                        // Reset to page 1 and apply filters
                        currentPage = 1;
                        filterData();
                        updateTable();
                    });
                    
                    // Search functionality
                    $('#dynolan-search, #dynolan-search-bottom').on('input', function() {
                        currentSearch = $(this).val().toLowerCase();
                        $('#dynolan-search, #dynolan-search-bottom').val(currentSearch);
                        currentPage = 1;
                        filterData();
                        updateTable();
                    });
                    
                    // Clear search
                    $('#dynolan-clear, #dynolan-clear-bottom').on('click', function() {
                        $('#dynolan-search, #dynolan-search-bottom').val('');
                        currentSearch = '';
                        filterData();
                        updateTable();
                    });
                    
                    // Create buttons
                    $('#create-post-inline').on('click', function() {
                        createNewInline('post');
                    });
                    
                    $('#create-page-inline').on('click', function() {
                        createNewInline('page');
                    });
                    
                    $('#create-popup').on('click', function() {
                        openModal('create');
                    });
                    
                    $('#duplicate-selected').on('click', function() {
                        if (selectedRows.size === 0) {
                            alert('Please select pages/posts to duplicate.');
                            return;
                        }
                        
                        if (confirm('Are you sure you want to duplicate ' + selectedRows.size + ' selected item(s)?')) {
                            const selectedIds = Array.from(selectedRows);
                            
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'snefuru_duplicate_page',
                                    post_ids: selectedIds,
                                    nonce: '<?php echo wp_create_nonce('snefuru_duplicate_page_nonce'); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        alert('Pages/posts duplicated successfully!');
                                        location.reload();
                                    } else {
                                        alert('Error duplicating pages/posts: ' + (response.data || 'Unknown error'));
                                    }
                                },
                                error: function() {
                                    alert('Failed to duplicate pages/posts. Please try again.');
                                }
                            });
                        }
                    });
                    
                    // Select all checkbox
                    $('#select-all').on('change', function() {
                        const isChecked = $(this).is(':checked');
                        $('.row-checkbox').prop('checked', isChecked);
                        
                        if (isChecked) {
                            $('.row-checkbox').each(function() {
                                selectedRows.add(parseInt($(this).val()));
                            });
                        } else {
                            selectedRows.clear();
                        }
                    });
                    
                    // Modal handlers
                    $('#dynolan-modal-close, #dynolan-modal-cancel').on('click', function() {
                        closeModal();
                    });
                    
                    $('#dynolan-modal').on('click', function(e) {
                        if (e.target === this) {
                            closeModal();
                        }
                    });
                    
                    $('#dynolan-modal-form').on('submit', function(e) {
                        e.preventDefault();
                        saveModalData();
                    });
                    
                    // Post content editor handlers
                    $(document).on('click', '.content-edit-btn', function(e) {
                        e.stopPropagation();
                        const postId = $(this).data('post-id');
                        const editorType = $(this).data('editor-type');
                        
                        if (editorType === 'elementor') {
                            openElementorEditor(postId);
                        } else {
                            openContentEditor(postId);
                        }
                    });
                    
                    $('#dynolan-content-editor-cancel').on('click', function() {
                        closeContentEditor();
                    });
                    
                    $('#dynolan-content-editor-save').on('click', function() {
                        saveContentEditor();
                    });
                    
                    // Elementor data editor handlers
                    $('#dynolan-elementor-editor-cancel').on('click', function() {
                        closeElementorEditor();
                    });
                    
                    $('#dynolan-elementor-editor-save').on('click', function() {
                        saveElementorEditor();
                    });
                    
                    // Table column sorting
                    $('.data-table th[data-field]').on('click', function() {
                        const field = $(this).data('field');
                        sortData(field);
                        updateTable();
                    });
                }
                
                function filterData() {
                    filteredData = allData.filter(item => {
                        // Apply search filter
                        let matchesSearch = true;
                        if (currentSearch) {
                            matchesSearch = Object.values(item).some(value => {
                                if (value === null || value === undefined) return false;
                                return value.toString().toLowerCase().includes(currentSearch);
                            });
                        }
                        
                        // Apply post type filter
                        let matchesPostType = true;
                        const activePostTypes = $('.filter-btn[data-filter-type="post_type"].active');
                        if (activePostTypes.length > 0) {
                            const activeValues = activePostTypes.map(function() {
                                return $(this).data('filter-value');
                            }).get();
                            matchesPostType = activeValues.includes(item.post_type);
                        }
                        
                        // Apply post status filter
                        let matchesPostStatus = true;
                        const activePostStatuses = $('.filter-btn[data-filter-type="post_status"].active');
                        if (activePostStatuses.length > 0) {
                            const activeValues = activePostStatuses.map(function() {
                                return $(this).data('filter-value');
                            }).get();
                            matchesPostStatus = activeValues.includes(item.post_status);
                        }
                        
                        return matchesSearch && matchesPostType && matchesPostStatus;
                    });
                }
                
                function setActiveColumnGroup(groupNumber) {
                    currentColumnGroup = groupNumber;
                    updateColumnVisibility();
                }
                
                function handleColumnNavigation(action) {
                    const totalGroups = 5; // 5 total groups
                    
                    switch(action) {
                        case 'first':
                            currentColumnGroup = 1;
                            break;
                        case 'prev':
                            if (currentColumnGroup > 1) currentColumnGroup--;
                            break;
                        case 'next':
                            if (currentColumnGroup < totalGroups) currentColumnGroup++;
                            break;
                        case 'last':
                            currentColumnGroup = totalGroups;
                            break;
                        default:
                            if (!isNaN(action)) {
                                currentColumnGroup = parseInt(action);
                            }
                    }
                    
                    updateColumnVisibility();
                    updateColumnGroupButtons();
                }
                
                function updateColumnVisibility() {
                    const startColumn = (currentColumnGroup - 1) * columnsPerGroup + 1; // +1 for checkbox column
                    const endColumn = startColumn + columnsPerGroup - 1;
                    
                    // Hide all columns except checkbox (index 0)
                    $('.data-table th, .data-table td').each(function(index) {
                        const columnIndex = $(this).index();
                        if (columnIndex === 0) {
                            // Always show checkbox column
                            $(this).show();
                        } else if (columnIndex >= startColumn && columnIndex <= endColumn) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
                
                function updateColumnGroupButtons() {
                    // Update column group bar
                    $('#dynolan-column-group-bar .column-pagination-btn').removeClass('active');
                    $(`[data-column-group="${currentColumnGroup}"]`).addClass('active');
                    
                    // Update column nav bar active state
                    $('#dynolan-column-nav-bar .column-pagination-btn').removeClass('active');
                    $(`[data-column-nav="${currentColumnGroup}"]`).addClass('active');
                }
                
                function updateTable() {
                    const startIndex = (currentPage - 1) * itemsPerPage;
                    const endIndex = itemsPerPage === filteredData.length ? filteredData.length : startIndex + itemsPerPage;
                    const pageData = filteredData.slice(startIndex, endIndex);
                    
                    // Update table rows
                    renderTableRows(pageData);
                    
                    // Update info text
                    const infoText = `${startIndex + 1}-${Math.min(endIndex, filteredData.length)} of ${filteredData.length} posts/pages`;
                    $('#dynolan-results-info, #dynolan-results-info-bottom').text(infoText);
                    
                    // Update pagination buttons
                    updatePaginationButtons();
                    
                    // Reattach event handlers for new rows
                    attachRowEventHandlers();
                    
                    // Apply column pagination
                    updateColumnVisibility();
                }
                
                function getFrontendUrl(item) {
                    const baseUrl = '<?php echo home_url('/'); ?>';
                    if (item.post_status === 'draft') {
                        return baseUrl + '?p=' + item.ID + '&preview=true';
                    } else {
                        if (item.post_name) {
                            return baseUrl + item.post_name + '/';
                        } else {
                            return baseUrl + '?p=' + item.ID;
                        }
                    }
                }
                
                function getAdminEditUrl(item) {
                    const adminUrl = '<?php echo admin_url('post.php'); ?>';
                    return adminUrl + '?post=' + item.ID + '&action=edit';
                }
                
                function getElementorEditUrl(item) {
                    const adminUrl = '<?php echo admin_url('post.php'); ?>';
                    return adminUrl + '?post=' + item.ID + '&action=elementor';
                }
                
                function isElementorPost(item) {
                    return item._elementor_data && item._elementor_data !== '' && item._elementor_data !== '[]';
                }
                
                function renderTableRows(data) {
                    let html = '';
                    
                    data.forEach(item => {
                        html += `<tr data-id="${item.ID}">
                            <td class="checkbox-cell column_checkbox">
                                <div class="tcell_inner_wrapper_div"><input type="checkbox" class="row-checkbox" value="${item.ID}"></div>
                            </td>
                            <td class="readonly-cell column_tool_buttons"><div class="tcell_inner_wrapper_div"><a href="${getAdminEditUrl(item)}" target="_blank" class="pendulum-btn">&#9675;&#124;</a>${isElementorPost(item) ? `<a href="${getElementorEditUrl(item)}" target="_blank" class="elementor-btn">E</a>` : '<span class="elementor-btn disabled">E</span>'}<a href="${getFrontendUrl(item)}" target="_blank" class="tool-btn">F</a><span class="c-btn">C1</span><span class="c-btn">C2</span><span class="c-btn">C3</span></div></td>
                            <td class="readonly-cell column_wp_posts_id"><div class="tcell_inner_wrapper_div">${item.ID}</div></td>
                            <td class="editable-cell column_wp_posts_post_status" data-field="post_status" data-type="select" data-status="${item.post_status}"><div class="tcell_inner_wrapper_div">${item.post_status === 'publish' ? '<strong>' + item.post_status + '</strong>' : item.post_status}</div></td>
                            <td class="editable-cell column_wp_posts_post_title" data-field="post_title" data-type="text"><div class="tcell_inner_wrapper_div">${item.post_title || ''}</div></td>
                            <td class="editable-cell column_wp_posts_post_name" data-field="post_name" data-type="text"><div class="tcell_inner_wrapper_div">${item.post_name || ''}</div></td>
                            <td class="readonly-cell column_replex_submit"><div class="tcell_inner_wrapper_div"></div></td>
                            <td class="readonly-cell column_wp_posts_post_content"><div class="tcell_inner_wrapper_div">${truncatePostContent(item.post_content || '')}<button class="content-edit-btn" data-post-id="${item.ID}">ED</button></div></td>
                            <td class="readonly-cell column_wp_postmeta_meta_key_elementor_data"><div class="tcell_inner_wrapper_div">${formatElementorData(item._elementor_data)}<button class="content-edit-btn" data-post-id="${item.ID}" data-editor-type="elementor">ED</button></div></td>
                            <td class="readonly-cell column_wp_posts_post_type"><div class="tcell_inner_wrapper_div">${item.post_type}</div></td>
                            <td class="readonly-cell column_wp_posts_post_date"><div class="tcell_inner_wrapper_div">${formatDate(item.post_date)}</div></td>
                            <td class="readonly-cell column_wp_posts_post_modified"><div class="tcell_inner_wrapper_div">${formatDate(item.post_modified)}</div></td>
                            <td class="editable-cell column_wp_posts_post_author" data-field="post_author" data-type="integer"><div class="tcell_inner_wrapper_div">${item.post_author}</div></td>
                            <td class="editable-cell column_wp_posts_post_parent" data-field="post_parent" data-type="integer"><div class="tcell_inner_wrapper_div">${item.post_parent || '0'}</div></td>
                            <td class="editable-cell column_wp_posts_menu_order" data-field="menu_order" data-type="integer"><div class="tcell_inner_wrapper_div">${item.menu_order || '0'}</div></td>
                            <td class="editable-cell column_wp_posts_comment_status" data-field="comment_status" data-type="select"><div class="tcell_inner_wrapper_div">${item.comment_status}</div></td>
                            <td class="editable-cell column_wp_posts_ping_status" data-field="ping_status" data-type="select"><div class="tcell_inner_wrapper_div">${item.ping_status}</div></td>
                            <td class="readonly-cell column_zen_orbitposts_rel_wp_post_id"><div class="tcell_inner_wrapper_div">${item.rel_wp_post_id || ''}</div></td>
                            <td class="readonly-cell column_zen_orbitposts_orbitpost_id"><div class="tcell_inner_wrapper_div">${item.orbitpost_id || ''}</div></td>
                            <td class="readonly-cell column_zen_orbitposts_redshift_datum"><div class="tcell_inner_wrapper_div">${item.redshift_datum || ''}</div></td>
                            <td class="readonly-cell column_zen_orbitposts_rover_datum"><div class="tcell_inner_wrapper_div">${item.rover_datum || ''}</div></td>
                            <td class="readonly-cell column_zen_orbitposts_hudson_imgplanbatch_id"><div class="tcell_inner_wrapper_div">${item.hudson_imgplanbatch_id || ''}</div></td>
                            <td class="readonly-cell column_zen_orbitposts_is_pinned"><div class="tcell_inner_wrapper_div"><span class="icon-btn" data-field="is_pinned" data-post-id="${item.ID}" data-value="${item.is_pinned || 0}">📌</span></div></td>
                            <td class="readonly-cell column_zen_orbitposts_is_flagged"><div class="tcell_inner_wrapper_div"><span class="icon-btn" data-field="is_flagged" data-post-id="${item.ID}" data-value="${item.is_flagged || 0}">🚩</span></div></td>
                            <td class="readonly-cell column_zen_orbitposts_is_starred"><div class="tcell_inner_wrapper_div"><span class="icon-btn" data-field="is_starred" data-post-id="${item.ID}" data-value="${item.is_starred || 0}">⭐</span></div></td>
                            <td class="readonly-cell column_zen_orbitposts_is_squared"><div class="tcell_inner_wrapper_div"><span class="icon-btn" data-field="is_squared" data-post-id="${item.ID}" data-value="${item.is_squared || 0}">⬜</span></div></td>
                            <td class="readonly-cell column_zen_orbitposts_created_at"><div class="tcell_inner_wrapper_div">${formatDate(item.created_at)}</div></td>
                            <td class="readonly-cell column_zen_orbitposts_updated_at"><div class="tcell_inner_wrapper_div">${formatDate(item.updated_at)}</div></td>
                        </tr>`;
                    });
                    
                    $('#dynolan-tbody').html(html);
                    
                    // Initialize icon button states
                    initializeIconButtons();
                }
                
                function attachRowEventHandlers() {
                    // Row checkbox handling
                    $('.row-checkbox').on('change', function() {
                        const id = parseInt($(this).val());
                        if ($(this).is(':checked')) {
                            selectedRows.add(id);
                        } else {
                            selectedRows.delete(id);
                        }
                    });
                    
                    // Inline editing
                    $('.editable-cell').on('click', function() {
                        if (editingCell) return; // Don't start new edit if already editing
                        
                        const $cell = $(this);
                        const field = $cell.data('field');
                        const type = $cell.data('type');
                        const currentValue = $cell.text().trim();
                        const postId = $cell.closest('tr').data('id');
                        
                        startInlineEdit($cell, field, type, currentValue, postId);
                    });
                    
                    // Icon button handling
                    $('.icon-btn').on('click', function() {
                        const $icon = $(this);
                        const field = $icon.data('field');
                        const postId = $icon.data('post-id');
                        const currentValue = parseInt($icon.data('value'));
                        const newValue = currentValue ? 0 : 1;
                        
                        // Update zen_orbitposts field
                        updateZenOrbitpostField(postId, field, newValue, $icon);
                    });
                }
                
                function startInlineEdit($cell, field, type, currentValue, postId) {
                    editingCell = { $cell, field, type, postId, originalValue: currentValue };
                    
                    let input;
                    if (type === 'longtext') {
                        input = $(`<textarea class="editing-textarea">${currentValue}</textarea>`);
                    } else if (type === 'select') {
                        let options = '';
                        if (field === 'post_status') {
                            options = '<option value="draft">draft</option><option value="publish">publish</option><option value="private">private</option>';
                        } else if (field === 'comment_status' || field === 'ping_status') {
                            options = '<option value="open">open</option><option value="closed">closed</option>';
                        }
                        input = $(`<select class="editing-input">${options}</select>`);
                        input.val(currentValue);
                    } else {
                        input = $(`<input type="text" class="editing-input" value="${currentValue}">`);
                    }
                    
                    $cell.html(input);
                    input.focus();
                    
                    // Handle save on blur or Enter key
                    input.on('blur', function() {
                        saveInlineEdit();
                    });
                    
                    input.on('keydown', function(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            saveInlineEdit();
                        } else if (e.key === 'Escape') {
                            cancelInlineEdit();
                        }
                    });
                }
                
                function saveInlineEdit() {
                    if (!editingCell) return;
                    
                    const $input = editingCell.$cell.find('input, textarea, select');
                    const newValue = $input.val();
                    
                    // Update UI immediately
                    editingCell.$cell.text(newValue);
                    
                    // Update data
                    const itemIndex = allData.findIndex(item => item.ID == editingCell.postId);
                    if (itemIndex !== -1) {
                        allData[itemIndex][editingCell.field] = newValue;
                    }
                    
                    // Save to server
                    saveFieldToServer(editingCell.postId, editingCell.field, newValue);
                    
                    editingCell = null;
                }
                
                function cancelInlineEdit() {
                    if (!editingCell) return;
                    
                    editingCell.$cell.text(editingCell.originalValue);
                    editingCell = null;
                }
                
                function saveFieldToServer(postId, field, value) {
                    $.post('<?php echo admin_url('admin.php?page=dynolan'); ?>', {
                        action: 'update_post_field',
                        post_id: postId,
                        field: field,
                        value: value,
                        nonce: '<?php echo wp_create_nonce('dynolan_nonce'); ?>'
                    }).fail(function(xhr, status, error) {
                        console.log('Save failed:', xhr.responseText);
                        alert('Failed to save changes. Please try again.');
                    });
                }
                
                function createNewInline(postType) {
                    $.post('<?php echo admin_url('admin.php?page=dynolan'); ?>', {
                        action: 'create_new_post',
                        post_type: postType,
                        nonce: '<?php echo wp_create_nonce('dynolan_nonce'); ?>'
                    }).done(function(response) {
                        console.log('Server response:', response);
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                // Add new post to beginning of data array
                                allData.unshift(data.post);
                                filterData();
                                currentPage = 1;
                                updateTable();
                            } else {
                                alert('Failed to create new ' + postType + ': ' + (data.message || 'Unknown error'));
                            }
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            console.log('Raw response:', response);
                            alert('Failed to create new ' + postType + '. Please try again.');
                        }
                    }).fail(function(xhr, status, error) {
                        console.log('Request failed:', xhr.responseText);
                        alert('Failed to create new ' + postType + '. Please try again.');
                    });
                }
                
                function openModal(mode, postData = null) {
                    if (mode === 'create') {
                        $('#dynolan-modal-title').text('Create New Post/Page');
                        $('#modal-action').val('create');
                        $('#dynolan-modal-form')[0].reset();
                    } else {
                        $('#dynolan-modal-title').text('Edit Post/Page');
                        $('#modal-action').val('edit');
                        // Populate form with post data
                        Object.keys(postData).forEach(key => {
                            $(`#modal-${key}`).val(postData[key]);
                        });
                    }
                    
                    $('#dynolan-modal').addClass('active');
                }
                
                function closeModal() {
                    $('#dynolan-modal').removeClass('active');
                }
                
                function saveModalData() {
                    const formData = new FormData($('#dynolan-modal-form')[0]);
                    formData.append('nonce', '<?php echo wp_create_nonce('dynolan_nonce'); ?>');
                    
                    $.post('<?php echo admin_url('admin.php?page=dynolan'); ?>', formData, {
                        processData: false,
                        contentType: false
                    }).done(function(response) {
                        console.log('Modal save response:', response);
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                closeModal();
                                // Refresh data
                                location.reload();
                            } else {
                                alert('Failed to save: ' + (data.message || 'Unknown error'));
                            }
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            alert('Failed to save. Please try again.');
                        }
                    }).fail(function(xhr, status, error) {
                        console.log('Modal save failed:', xhr.responseText);
                        alert('Failed to save. Please try again.');
                    });
                }
                
                function updatePaginationButtons() {
                    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                    
                    // Update page number buttons (simplified for demo)
                    $('.pagination-btn[data-page]').each(function() {
                        const $btn = $(this);
                        const page = $btn.data('page');
                        
                        if (page === 'first' || page === 'prev') {
                            $btn.toggleClass('disabled', currentPage <= 1);
                        } else if (page === 'next' || page === 'last') {
                            $btn.toggleClass('disabled', currentPage >= totalPages);
                        } else if (!isNaN(page)) {
                            $btn.removeClass('active');
                            if (parseInt(page) === currentPage) {
                                $btn.addClass('active');
                            }
                        }
                    });
                }
                
                function sortData(field) {
                    // Simple sorting implementation
                    filteredData.sort((a, b) => {
                        const aVal = a[field] || '';
                        const bVal = b[field] || '';
                        return aVal.toString().localeCompare(bVal.toString());
                    });
                }
                
                // Utility functions
                function truncateText(text, length) {
                    if (!text) return '';
                    return text.length > length ? text.substring(0, length) + '...' : text;
                }
                
                function truncatePostContent(text) {
                    if (!text) return '';
                    
                    // Strip HTML tags to get plain text
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = text;
                    const plainText = tempDiv.textContent || tempDiv.innerText || '';
                    
                    // Get first line only
                    let firstLine = plainText.split(/\r?\n/)[0] || '';
                    
                    // Truncate to 50 characters with conditional ellipsis
                    if (firstLine.length > 50) {
                        return firstLine.substring(0, 50) + '...';
                    }
                    return firstLine;
                }
                
                function formatDate(dateString) {
                    if (!dateString) return '';
                    return new Date(dateString).toLocaleString();
                }
                
                function formatElementorData(elementorData) {
                    if (elementorData === null || elementorData === undefined) {
                        return 'none';
                    }
                    if (elementorData === '' || elementorData === '[]') {
                        return '0 lines';
                    }
                    const lineCount = (elementorData.match(/\n/g) || []).length + 1;
                    return lineCount + ' lines';
                }
                
                // Icon button functions
                function initializeIconButtons() {
                    $('.icon-btn').each(function() {
                        const $icon = $(this);
                        const value = parseInt($icon.data('value'));
                        if (value) {
                            $icon.addClass('active');
                        } else {
                            $icon.removeClass('active');
                        }
                    });
                }
                
                function updateZenOrbitpostField(postId, field, newValue, $icon) {
                    // Show loading state
                    $icon.css('opacity', '0.5');
                    
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'dynolan_update_zen_orbitpost_field',
                            post_id: postId,
                            field: field,
                            value: newValue,
                            nonce: '<?php echo wp_create_nonce('dynolan_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update the icon state
                                $icon.data('value', newValue);
                                if (newValue) {
                                    $icon.addClass('active');
                                } else {
                                    $icon.removeClass('active');
                                }
                                $icon.css('opacity', '');
                            } else {
                                alert('Error updating field: ' + (response.data || 'Unknown error'));
                                $icon.css('opacity', '');
                            }
                        },
                        error: function() {
                            alert('Error updating field');
                            $icon.css('opacity', '');
                        }
                    });
                }
                
                // Elementor data editor functions
                function openElementorEditor(postId) {
                    currentElementorEditPostId = postId;
                    const post = allData.find(item => item.ID == postId);
                    
                    if (post) {
                        $('#dynolan-elementor-editor-textarea').val(post._elementor_data || '');
                        $('#dynolan-elementor-editor-modal').addClass('active');
                    }
                }
                
                function closeElementorEditor() {
                    $('#dynolan-elementor-editor-modal').removeClass('active');
                    $('#dynolan-elementor-editor-textarea').val('');
                    currentElementorEditPostId = null;
                }
                
                function saveElementorEditor() {
                    if (!currentElementorEditPostId) return;
                    
                    const newElementorData = $('#dynolan-elementor-editor-textarea').val();
                    
                    // Send AJAX request to save
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'dynolan_update_elementor_data',
                            post_id: currentElementorEditPostId,
                            elementor_data: newElementorData,
                            nonce: '<?php echo wp_create_nonce('dynolan_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update local data
                                const post = allData.find(item => item.ID == currentElementorEditPostId);
                                if (post) {
                                    post._elementor_data = newElementorData;
                                }
                                
                                // Refresh table
                                updateTable();
                                
                                // Close editor
                                closeElementorEditor();
                            } else {
                                alert('Failed to save: ' + (response.data || 'Unknown error'));
                            }
                        },
                        error: function() {
                            alert('Failed to save elementor data');
                        }
                    });
                }
                
                // Post content editor functions
                function openContentEditor(postId) {
                    currentContentEditPostId = postId;
                    const post = allData.find(item => item.ID == postId);
                    
                    if (post) {
                        $('#dynolan-content-editor-textarea').val(post.post_content || '');
                        $('#dynolan-content-editor-modal').addClass('active');
                    }
                }
                
                function closeContentEditor() {
                    $('#dynolan-content-editor-modal').removeClass('active');
                    $('#dynolan-content-editor-textarea').val('');
                    currentContentEditPostId = null;
                }
                
                function saveContentEditor() {
                    if (!currentContentEditPostId) return;
                    
                    const newContent = $('#dynolan-content-editor-textarea').val();
                    
                    // Send AJAX request to save
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'dynolan_update_post_content',
                            post_id: currentContentEditPostId,
                            post_content: newContent,
                            nonce: '<?php echo wp_create_nonce('dynolan_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update local data
                                const post = allData.find(item => item.ID == currentContentEditPostId);
                                if (post) {
                                    post.post_content = newContent;
                                }
                                
                                // Refresh table
                                updateTable();
                                
                                // Close editor
                                closeContentEditor();
                            } else {
                                alert('Failed to save: ' + (response.data || 'Unknown error'));
                            }
                        },
                        error: function() {
                            alert('Failed to save content');
                        }
                    });
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
 * Helper function to handle AJAX requests - Complete BeamRayMar parity
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
            snefuru_ajax_create_new_post();
            break;
            
        case 'update_post_field':
            snefuru_ajax_update_post_field();
            break;
            
        case 'create':
        case 'edit':
            snefuru_ajax_create_or_edit_post();
            break;
            
        default:
            echo json_encode(array('success' => false, 'message' => 'Invalid action'));
    }
    
    wp_die();
}

/**
 * AJAX handler for creating new post inline
 */
function snefuru_ajax_create_new_post() {
    if (!isset($_POST['post_type'])) {
        echo json_encode(array('success' => false, 'message' => 'Post type not provided'));
        wp_die();
    }
    
    $post_type = sanitize_text_field($_POST['post_type']);
    
    if (!in_array($post_type, array('post', 'page'))) {
        echo json_encode(array('success' => false, 'message' => 'Invalid post type: ' . $post_type));
        wp_die();
    }
    
    $post_data = array(
        'post_type' => $post_type,
        'post_title' => '',
        'post_content' => '',
        'post_status' => 'draft',
        'post_author' => get_current_user_id()
    );
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        echo json_encode(array('success' => false, 'message' => $post_id->get_error_message()));
        wp_die();
    }
    
    // Get the created post data
    $post = get_post($post_id, ARRAY_A);
    $response_data = array(
        'ID' => $post['ID'],
        'post_title' => $post['post_title'],
        'post_content' => $post['post_content'],
        'post_type' => $post['post_type'],
        'post_status' => $post['post_status'],
        'post_name' => $post['post_name'],
        'post_date' => $post['post_date'],
        'post_modified' => $post['post_modified'],
        'post_author' => $post['post_author'],
        'post_parent' => $post['post_parent'],
        'menu_order' => $post['menu_order'],
        'comment_status' => $post['comment_status'],
        'ping_status' => $post['ping_status']
    );
    
    echo json_encode(array('success' => true, 'post' => $response_data));
    wp_die();
}

/**
 * AJAX handler for updating post field inline
 */
function snefuru_ajax_update_post_field() {
    $post_id = intval($_POST['post_id']);
    $field = sanitize_text_field($_POST['field']);
    $value = $_POST['value']; // Don't sanitize yet, depends on field type
    
    // Validate post exists
    if (!get_post($post_id)) {
        echo json_encode(array('success' => false, 'message' => 'Post not found'));
        wp_die();
    }
    
    // Sanitize value based on field type
    switch ($field) {
        case 'post_title':
        case 'post_name':
            $value = sanitize_text_field($value);
            break;
        case 'post_content':
            $value = wp_kses_post($value);
            break;
        case 'post_status':
            $value = in_array($value, array('draft', 'publish', 'private')) ? $value : 'draft';
            break;
        case 'comment_status':
        case 'ping_status':
            $value = in_array($value, array('open', 'closed')) ? $value : 'closed';
            break;
        case 'post_author':
        case 'post_parent':
        case 'menu_order':
            $value = intval($value);
            break;
        default:
            echo json_encode(array('success' => false, 'message' => 'Invalid field'));
            wp_die();
    }
    
    $update_data = array(
        'ID' => $post_id,
        $field => $value
    );
    
    $result = wp_update_post($update_data);
    
    if (is_wp_error($result)) {
        echo json_encode(array('success' => false, 'message' => $result->get_error_message()));
        wp_die();
    }
    
    echo json_encode(array('success' => true));
    wp_die();
}

/**
 * AJAX handler for create/edit post via modal
 */
function snefuru_ajax_create_or_edit_post() {
    $action = sanitize_text_field($_POST['action']);
    $post_data = array(
        'post_type' => sanitize_text_field($_POST['post_type']),
        'post_title' => sanitize_text_field($_POST['post_title']),
        'post_content' => wp_kses_post($_POST['post_content']),
        'post_status' => sanitize_text_field($_POST['post_status']),
        'post_name' => sanitize_title($_POST['post_name']),
        'post_parent' => intval($_POST['post_parent'])
    );
    
    if ($action === 'edit') {
        $post_data['ID'] = intval($_POST['post_id']);
        $result = wp_update_post($post_data);
    } else {
        $post_data['post_author'] = get_current_user_id();
        $result = wp_insert_post($post_data);
    }
    
    if (is_wp_error($result)) {
        echo json_encode(array('success' => false, 'message' => $result->get_error_message()));
        wp_die();
    }
    
    echo json_encode(array('success' => true, 'post_id' => $result));
    wp_die();
}

/**
 * Helper function to get posts and pages data - Complete BeamRayMar parity
 */
function snefuru_get_posts_and_pages_data() {
    global $wpdb;
    
    $results = $wpdb->get_results(
        "SELECT p.ID, p.post_title, p.post_content, p.post_type, p.post_status, p.post_name, 
                p.post_date, p.post_modified, p.post_author, p.post_parent, p.menu_order, 
                p.comment_status, p.ping_status,
                pm.meta_value as _elementor_data,
                zop.rel_wp_post_id, zop.orbitpost_id, zop.redshift_datum, zop.rover_datum, 
                zop.hudson_imgplanbatch_id, zop.is_pinned, zop.is_flagged, zop.is_starred, zop.is_squared,
                zop.created_at, zop.updated_at
         FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_elementor_data'
         LEFT JOIN {$wpdb->prefix}zen_orbitposts zop ON p.ID = zop.rel_wp_post_id
         WHERE p.post_type IN ('post', 'page') 
         AND p.post_status NOT IN ('trash', 'auto-draft') 
         ORDER BY p.post_modified DESC",
        ARRAY_A
    );
    
    return $results ? $results : array();
}

/**
 * Helper function to render dynolan table rows - Complete BeamRayMar parity
 */
function snefuru_render_dynolan_table_rows($posts_pages) {
    if (empty($posts_pages)) {
        echo '<tr><td colspan="28" class="table-loading">No posts or pages found.</td></tr>';
        return;
    }

    $count = 0;
    foreach ($posts_pages as $item) {
        if ($count >= 100) break; // Default pagination limit
        
        // Strip all HTML tags to get plain text
        $plain_text = strip_tags($item['post_content']);
        
        // Get first line only
        $first_line = explode("\n", $plain_text)[0];
        $first_line = explode("\r", $first_line)[0]; // Handle Windows line breaks
        
        // Truncate to 50 characters with conditional ellipsis
        if (strlen($first_line) > 50) {
            $content_preview = substr($first_line, 0, 50) . '...';
        } else {
            $content_preview = $first_line;
        }
        
        echo '<tr data-id="' . esc_attr($item['ID']) . '">';
        echo '<td class="checkbox-cell column_checkbox">';
        echo '<div class="tcell_inner_wrapper_div"><input type="checkbox" class="row-checkbox" value="' . esc_attr($item['ID']) . '"></div>';
        echo '</td>';
        
        // Frontend URL logic
        $frontend_url = '';
        if ($item['post_status'] === 'draft') {
            $frontend_url = home_url('/?p=' . $item['ID'] . '&preview=true');
        } else {
            if ($item['post_name']) {
                $frontend_url = home_url('/' . $item['post_name'] . '/');
            } else {
                $frontend_url = home_url('/?p=' . $item['ID']);
            }
        }
        $admin_edit_url = admin_url('post.php?post=' . $item['ID'] . '&action=edit');
        $elementor_edit_url = admin_url('post.php?post=' . $item['ID'] . '&action=elementor');
        $is_elementor = !empty($item['_elementor_data']) && $item['_elementor_data'] !== '[]';
        
        echo '<td class="readonly-cell column_tool_buttons"><div class="tcell_inner_wrapper_div">';
        echo '<a href="' . esc_url($admin_edit_url) . '" target="_blank" class="pendulum-btn">&#9675;&#124;</a>';
        if ($is_elementor) {
            echo '<a href="' . esc_url($elementor_edit_url) . '" target="_blank" class="elementor-btn">E</a>';
        } else {
            echo '<span class="elementor-btn disabled">E</span>';
        }
        echo '<a href="' . esc_url($frontend_url) . '" target="_blank" class="tool-btn">F</a>';
        echo '<span class="c-btn">C1</span>';
        echo '<span class="c-btn">C2</span>';
        echo '<span class="c-btn">C3</span>';
        echo '</div></td>';
        echo '<td class="readonly-cell column_wp_posts_id"><div class="tcell_inner_wrapper_div">' . esc_html($item['ID']) . '</div></td>';
        $status_display = $item['post_status'] === 'publish' ? '<strong>' . esc_html($item['post_status']) . '</strong>' : esc_html($item['post_status']);
        echo '<td class="editable-cell column_wp_posts_post_status" data-field="post_status" data-type="select" data-status="' . esc_attr($item['post_status']) . '"><div class="tcell_inner_wrapper_div">' . $status_display . '</div></td>';
        echo '<td class="editable-cell column_wp_posts_post_title" data-field="post_title" data-type="text"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_title']) . '</div></td>';
        echo '<td class="editable-cell column_wp_posts_post_name" data-field="post_name" data-type="text"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_name']) . '</div></td>';
        echo '<td class="readonly-cell column_replex_submit"><div class="tcell_inner_wrapper_div"></div></td>';
        echo '<td class="readonly-cell column_wp_posts_post_content"><div class="tcell_inner_wrapper_div">' . esc_html($content_preview) . '<button class="content-edit-btn" data-post-id="' . esc_attr($item['ID']) . '">ED</button></div></td>';
        
        // Calculate elementor data line count
        $elementor_display = 'none';
        if (isset($item['_elementor_data']) && $item['_elementor_data'] !== null) {
            if (empty($item['_elementor_data']) || $item['_elementor_data'] === '[]') {
                $elementor_display = '0 lines';
            } else {
                $line_count = substr_count($item['_elementor_data'], "\n") + 1;
                $elementor_display = $line_count . ' lines';
            }
        }
        echo '<td class="readonly-cell column_wp_postmeta_meta_key_elementor_data"><div class="tcell_inner_wrapper_div">' . esc_html($elementor_display) . '<button class="content-edit-btn" data-post-id="' . esc_attr($item['ID']) . '" data-editor-type="elementor">ED</button></div></td>';
        
        echo '<td class="readonly-cell column_wp_posts_post_type"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_type']) . '</div></td>';
        echo '<td class="readonly-cell column_wp_posts_post_date"><div class="tcell_inner_wrapper_div">' . esc_html(date('Y-m-d H:i:s', strtotime($item['post_date']))) . '</div></td>';
        echo '<td class="readonly-cell column_wp_posts_post_modified"><div class="tcell_inner_wrapper_div">' . esc_html(date('Y-m-d H:i:s', strtotime($item['post_modified']))) . '</div></td>';
        echo '<td class="editable-cell column_wp_posts_post_author" data-field="post_author" data-type="integer"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_author']) . '</div></td>';
        echo '<td class="editable-cell column_wp_posts_post_parent" data-field="post_parent" data-type="integer"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_parent'] ?: '0') . '</div></td>';
        echo '<td class="editable-cell column_wp_posts_menu_order" data-field="menu_order" data-type="integer"><div class="tcell_inner_wrapper_div">' . esc_html($item['menu_order'] ?: '0') . '</div></td>';
        echo '<td class="editable-cell column_wp_posts_comment_status" data-field="comment_status" data-type="select"><div class="tcell_inner_wrapper_div">' . esc_html($item['comment_status']) . '</div></td>';
        echo '<td class="editable-cell column_wp_posts_ping_status" data-field="ping_status" data-type="select"><div class="tcell_inner_wrapper_div">' . esc_html($item['ping_status']) . '</div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_rel_wp_post_id"><div class="tcell_inner_wrapper_div">' . esc_html($item['rel_wp_post_id'] ?: '') . '</div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_orbitpost_id"><div class="tcell_inner_wrapper_div">' . esc_html($item['orbitpost_id'] ?: '') . '</div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_redshift_datum"><div class="tcell_inner_wrapper_div">' . esc_html($item['redshift_datum'] ?: '') . '</div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_rover_datum"><div class="tcell_inner_wrapper_div">' . esc_html($item['rover_datum'] ?: '') . '</div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_hudson_imgplanbatch_id"><div class="tcell_inner_wrapper_div">' . esc_html($item['hudson_imgplanbatch_id'] ?: '') . '</div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_is_pinned"><div class="tcell_inner_wrapper_div"><span class="icon-btn" data-field="is_pinned" data-post-id="' . esc_attr($item['ID']) . '" data-value="' . esc_attr($item['is_pinned'] ?: 0) . '">📌</span></div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_is_flagged"><div class="tcell_inner_wrapper_div"><span class="icon-btn" data-field="is_flagged" data-post-id="' . esc_attr($item['ID']) . '" data-value="' . esc_attr($item['is_flagged'] ?: 0) . '">🚩</span></div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_is_starred"><div class="tcell_inner_wrapper_div"><span class="icon-btn" data-field="is_starred" data-post-id="' . esc_attr($item['ID']) . '" data-value="' . esc_attr($item['is_starred'] ?: 0) . '">⭐</span></div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_is_squared"><div class="tcell_inner_wrapper_div"><span class="icon-btn" data-field="is_squared" data-post-id="' . esc_attr($item['ID']) . '" data-value="' . esc_attr($item['is_squared'] ?: 0) . '">⬜</span></div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_created_at"><div class="tcell_inner_wrapper_div">' . esc_html($item['created_at'] ? date('Y-m-d H:i:s', strtotime($item['created_at'])) : '') . '</div></td>';
        echo '<td class="readonly-cell column_zen_orbitposts_updated_at"><div class="tcell_inner_wrapper_div">' . esc_html($item['updated_at'] ? date('Y-m-d H:i:s', strtotime($item['updated_at'])) : '') . '</div></td>';
        echo '</tr>';
        
        $count++;
    }
}

/**
 * AJAX handler for updating elementor data from dynolan editor
 */
function snefuru_dynolan_update_elementor_data() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dynolan_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    // Get and validate post ID
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
        return;
    }
    
    // Get new elementor data
    $new_elementor_data = isset($_POST['elementor_data']) ? $_POST['elementor_data'] : '';
    
    // Update the post meta
    if ($new_elementor_data === '' || $new_elementor_data === null) {
        delete_post_meta($post_id, '_elementor_data');
        wp_send_json_success('Elementor data cleared');
    } else {
        update_post_meta($post_id, '_elementor_data', $new_elementor_data);
        wp_send_json_success('Elementor data updated successfully');
    }
}

/**
 * AJAX handler for updating zen_orbitposts boolean fields
 */
function snefuru_dynolan_update_zen_orbitpost_field() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dynolan_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    // Get and validate post ID
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
        return;
    }
    
    // Get and validate field name
    $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
    $allowed_fields = array('is_pinned', 'is_flagged', 'is_starred', 'is_squared');
    if (!in_array($field, $allowed_fields)) {
        wp_send_json_error('Invalid field name');
        return;
    }
    
    // Get and validate value
    $value = isset($_POST['value']) ? intval($_POST['value']) : 0;
    $value = $value ? 1 : 0; // Ensure boolean 0 or 1
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'zen_orbitposts';
    
    // Check if record exists
    $existing_record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE rel_wp_post_id = %d",
        $post_id
    ));
    
    if ($existing_record) {
        // Update existing record
        $result = $wpdb->update(
            $table_name,
            array($field => $value),
            array('rel_wp_post_id' => $post_id),
            array('%d'),
            array('%d')
        );
    } else {
        // Create new record
        $result = $wpdb->insert(
            $table_name,
            array(
                'rel_wp_post_id' => $post_id,
                $field => $value
            ),
            array('%d', '%d')
        );
    }
    
    if ($result !== false) {
        wp_send_json_success('Field updated successfully');
    } else {
        wp_send_json_error('Database update failed');
    }
}

/**
 * AJAX handler for updating post content from dynolan editor
 */
function snefuru_dynolan_update_post_content() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dynolan_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    // Get and validate post ID
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
        return;
    }
    
    // Get new content
    $new_content = isset($_POST['post_content']) ? wp_kses_post($_POST['post_content']) : '';
    
    // Update the post
    $updated = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content
    ));
    
    if (is_wp_error($updated)) {
        wp_send_json_error($updated->get_error_message());
    } else {
        wp_send_json_success('Content updated successfully');
    }
}

// Register AJAX handlers
add_action('wp_ajax_dynolan_update_elementor_data', 'snefuru_dynolan_update_elementor_data');
add_action('wp_ajax_dynolan_update_zen_orbitpost_field', 'snefuru_dynolan_update_zen_orbitpost_field');
add_action('wp_ajax_dynolan_update_post_content', 'snefuru_dynolan_update_post_content');