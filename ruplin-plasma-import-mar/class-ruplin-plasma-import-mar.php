<?php

/**
 * Ruplin Plasma Import Mar Page Class
 * Handles the Plasma Import Mar admin page functionality
 */
class Ruplin_Plasma_Import_Mar {
    
    public function __construct() {
        // Constructor can be used for initialization if needed
    }
    
    /**
     * Display the Ruplin Plasma Import Mar admin page
     */
    public function ruplin_plasma_import_mar_page() {
        global $wpdb;
        
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        // Load Jezel Navigation component
        require_once plugin_dir_path(__FILE__) . 'jezel-navigation/class-jezel-navigation.php';
        
        // Get post counts
        $page_counts = wp_count_posts('page');
        $post_counts = wp_count_posts('post');
        
        // Calculate totals
        $pages_published = $page_counts->publish ?? 0;
        $pages_draft = $page_counts->draft ?? 0;
        $pages_all = $pages_published + $pages_draft + ($page_counts->private ?? 0) + ($page_counts->future ?? 0) + ($page_counts->pending ?? 0);
        
        $posts_published = $post_counts->publish ?? 0;
        $posts_draft = $post_counts->draft ?? 0;
        $posts_all = $posts_published + $posts_draft + ($post_counts->private ?? 0) + ($post_counts->future ?? 0) + ($post_counts->pending ?? 0);
        
        // Get total pylons count
        $pylons_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pylons");
        if ($pylons_count === null) {
            $pylons_count = 0; // Table might not exist
        }
        
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <?php
            // Render Jezel Navigation
            Ruplin_Jezel_Navigation::render(array(
                'position_left' => '170px',
                'position_top' => '120px'
            ));
            ?>
            
            <div style="padding: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 20px; margin-bottom: 20px;">
                    <h1 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <img src="<?php echo plugin_dir_url(__FILE__) . '../ruplin-assets/ruplin-logo.png'; ?>" alt="Ruplin Logo" style="height: 40px; width: auto;">
                        Plasma Import Mar
                    </h1>
                    
                    <!-- Fundamental Image Setter Button -->
                    <a href="<?php echo admin_url('admin.php?page=fundamental_image_setter'); ?>" 
                       class="button ruplin-button" 
                       style="background: #8B4513; color: white; text-decoration: none; padding: 12px 20px; font-size: 16px; font-weight: bold; border: none; border-radius: 4px; display: inline-block; transition: background-color 0.3s ease; align-self: center;">
                        <span style="color: #FFD700;">go to ruplin</span> - <span style="color: white;">fundamental_image_setter</span>
                    </a>
                    
                    <!-- Counter Badges Container -->
                    <div style="display: flex; gap: 15px; align-items: center; margin-left: auto;">
                        <!-- Pages Counter Badge -->
                        <div style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <a href="<?php echo admin_url('edit.php?post_type=page'); ?>" 
                               style="color: white; font-weight: 600; font-size: 13px; text-decoration: none; transition: opacity 0.2s;"
                               title="View all pages"
                               onmouseover="this.style.opacity='0.8';"
                               onmouseout="this.style.opacity='1';">Pages:</a>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <span style="color: rgba(255,255,255,0.8); font-size: 11px; font-weight: normal;">pub:</span>
                                <a href="<?php echo admin_url('edit.php?post_type=page&post_status=publish'); ?>" 
                                   style="background: rgba(255,255,255,0.9); color: #667eea; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s;" 
                                   title="View published pages"
                                   onmouseover="this.style.background='rgba(255,255,255,1)'; this.style.transform='scale(1.05)';"
                                   onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.transform='scale(1)';">
                                   <?php echo number_format($pages_published); ?>
                                </a>
                                <span style="color: rgba(255,255,255,0.8); font-size: 11px; font-weight: normal;">drafts:</span>
                                <a href="<?php echo admin_url('edit.php?post_type=page&post_status=draft'); ?>" 
                                   style="background: rgba(255,255,255,0.7); color: #764ba2; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s;" 
                                   title="View draft pages"
                                   onmouseover="this.style.background='rgba(255,255,255,0.9)'; this.style.transform='scale(1.05)';"
                                   onmouseout="this.style.background='rgba(255,255,255,0.7)'; this.style.transform='scale(1)';">
                                   <?php echo number_format($pages_draft); ?>
                                </a>
                                <span style="color: rgba(255,255,255,0.8); font-size: 11px; font-weight: normal;">all:</span>
                                <a href="<?php echo admin_url('edit.php?post_type=page'); ?>" 
                                   style="background: rgba(255,255,255,0.5); color: #333; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s;" 
                                   title="View all pages"
                                   onmouseover="this.style.background='rgba(255,255,255,0.7)'; this.style.transform='scale(1.05)';"
                                   onmouseout="this.style.background='rgba(255,255,255,0.5)'; this.style.transform='scale(1)';">
                                   <?php echo number_format($pages_all); ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Posts Counter Badge -->
                        <div style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <a href="<?php echo admin_url('edit.php'); ?>" 
                               style="color: white; font-weight: 600; font-size: 13px; text-decoration: none; transition: opacity 0.2s;"
                               title="View all posts"
                               onmouseover="this.style.opacity='0.8';"
                               onmouseout="this.style.opacity='1';">Posts:</a>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <span style="color: rgba(255,255,255,0.8); font-size: 11px; font-weight: normal;">pub:</span>
                                <a href="<?php echo admin_url('edit.php?post_status=publish'); ?>" 
                                   style="background: rgba(255,255,255,0.9); color: #10b981; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s;" 
                                   title="View published posts"
                                   onmouseover="this.style.background='rgba(255,255,255,1)'; this.style.transform='scale(1.05)';"
                                   onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.transform='scale(1)';">
                                   <?php echo number_format($posts_published); ?>
                                </a>
                                <span style="color: rgba(255,255,255,0.8); font-size: 11px; font-weight: normal;">drafts:</span>
                                <a href="<?php echo admin_url('edit.php?post_status=draft'); ?>" 
                                   style="background: rgba(255,255,255,0.7); color: #059669; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s;" 
                                   title="View draft posts"
                                   onmouseover="this.style.background='rgba(255,255,255,0.9)'; this.style.transform='scale(1.05)';"
                                   onmouseout="this.style.background='rgba(255,255,255,0.7)'; this.style.transform='scale(1)';">
                                   <?php echo number_format($posts_draft); ?>
                                </a>
                                <span style="color: rgba(255,255,255,0.8); font-size: 11px; font-weight: normal;">all:</span>
                                <a href="<?php echo admin_url('edit.php'); ?>" 
                                   style="background: rgba(255,255,255,0.5); color: #333; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s;" 
                                   title="View all posts"
                                   onmouseover="this.style.background='rgba(255,255,255,0.7)'; this.style.transform='scale(1.05)';"
                                   onmouseout="this.style.background='rgba(255,255,255,0.5)'; this.style.transform='scale(1)';">
                                   <?php echo number_format($posts_all); ?>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Pylons Counter Badge -->
                        <div style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: linear-gradient(135deg, #ff6f00 0%, #ff9100 100%); border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <span style="color: white; font-weight: 600; font-size: 13px;">Total Pylons</span>
                            <span style="background: rgba(255,255,255,0.9); color: #ff6f00; padding: 4px 10px; border-radius: 4px; font-size: 14px; font-weight: bold;">
                                <?php echo number_format($pylons_count); ?>
                            </span>
                        </div>
                        
                        <!-- Checkbox Container -->
                        <div style="display: flex; align-items: center; padding: 8px 12px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">
                            <label style="display: flex; align-items: center; cursor: pointer; font-size: 13px; margin: 0;">
                                <input type="checkbox" id="disable-slash-removal" style="margin-right: 8px;">
                                <span style="color: #666;">Disable slash removal (for "\" characters on db insert)</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Error Reporting Section (initially hidden) -->
                <div id="error-reporting-container" style="margin-bottom: 20px; display: none;">
                    <div style="background: #fff2cd; border: 1px solid #f1c40f; padding: 15px; border-radius: 5px; border-left: 4px solid #f39c12;">
                        <h4 style="margin: 0 0 10px 0; color: #d68910;">Import Error Details</h4>
                        <textarea id="error-details-text" readonly style="width: 100%; height: 200px; font-family: monospace; font-size: 12px; background: #fefefe; border: 1px solid #ddd; padding: 10px; resize: vertical;" placeholder="Error details will appear here..."></textarea>
                        <p style="margin: 10px 0 0 0; font-size: 11px; color: #666;">
                            You can copy this error information to share with support or for debugging.
                        </p>
                    </div>
                </div>
                
                <!-- Tab Navigation -->
                <div style="border-bottom: 1px solid #ddd; margin-bottom: 20px;">
                    <div style="display: flex; gap: 0;">
                        <button id="tab-main" class="plasma-tab active" style="padding: 12px 20px; background: #fff; border: 1px solid #ddd; border-bottom: none; border-top-left-radius: 5px; border-top-right-radius: 5px; cursor: pointer; font-weight: 500; color: #0073aa;">
                            Main Tab 1
                        </button>
                        <button id="tab-error" class="plasma-tab" style="padding: 12px 20px; background: #f1f1f1; border: 1px solid #ddd; border-bottom: none; border-top-left-radius: 5px; border-top-right-radius: 5px; cursor: pointer; font-weight: 500; color: #333; margin-left: -1px;">
                            Error Reporting
                        </button>
                    </div>
                </div>
                
                <!-- Tab Content Container -->
                <div id="tab-content">
                    
                    <!-- Main Tab Content -->
                    <div id="main-tab-content" class="tab-content active">
                
                <!-- JSON Upload Section -->
                <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0;">Import JSON File</h3>
                    
                    <!-- Import Method Selection -->
                    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba; border-radius: 3px;">
                        <h4 style="margin: 0 0 10px 0;">Import Method Selection</h4>
                        <label style="display: flex; align-items: center; margin-bottom: 8px; cursor: pointer;">
                            <input type="radio" name="import_method" value="direct" id="import-method-direct" checked style="margin-right: 8px;">
                            <strong>Direct Import (Default)</strong> - Load JSON in browser memory, send via AJAX
                            <span style="color: #666; font-size: 12px; margin-left: 10px;">(Works for most files, may fail on large datasets due to PHP limits)</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="import_method" value="file_based" id="import-method-file" style="margin-right: 8px;">
                            <strong>File-Based Import (Large Files)</strong> - Upload file to server, process in batches
                            <span style="color: #666; font-size: 12px; margin-left: 10px;">(Recommended for files >500KB or >50 pages, bypasses PHP limits)</span>
                        </label>
                    </div>
                    
                    <form id="plasma-upload-form" method="post" enctype="multipart/form-data" style="margin-bottom: 15px;">
                        <?php wp_nonce_field('plasma_import_nonce', 'plasma_import_nonce'); ?>
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <input type="file" id="plasma-json-file" name="plasma_json_file" accept=".json" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <button type="button" id="import-json-btn" class="button button-primary">Import JSON File</button>
                            <button type="button" id="import-all-btn" class="button button-primary" style="background: #28a745; border-color: #28a745;" disabled>
                                run f47 and f51 both - import plasma_pages plus driggs data
                            </button>
                        </div>
                    </form>
                    <div id="upload-status" style="margin-top: 10px;"></div>
                    <div id="consolidated-import-status" style="margin-top: 10px; display: none;">
                        <div style="padding: 10px; background: #f0f8ff; border-left: 4px solid #0073aa; border-radius: 3px;">
                            <strong>Consolidated Import Progress:</strong>
                            <div id="import-progress-details" style="margin-top: 8px; font-size: 13px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Data Preview Section (Hidden initially) -->
                <div id="data-preview-section" style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 5px; display: none;">
                    <h3 style="margin: 0 0 15px 0;">Preview Imported Data - plasma_pages data</h3>
                    
                    <!-- Homepage Assignment Option -->
                    <div style="margin-bottom: 15px; padding: 10px; background-color: #f0f8ff; border-left: 4px solid #0073aa; border-radius: 3px;">
                        <label style="font-weight: 500; display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" id="set-homepage-option" checked style="margin-right: 8px;">
                            Set the page with "page_archetype" of "homepage" as the assigned front page in WordPress native settings
                        </label>
                        <div style="font-size: 12px; color: #666; margin-top: 5px; margin-left: 20px;">
                            When checked: The page with page_archetype="homepage" will be set as the WordPress front page<br>
                            Note: Import will fail if multiple pages have page_archetype="homepage"
                        </div>
                    </div>
                    
                    <!-- F582 Date Processing Option -->
                    <div style="margin-bottom: 15px; padding: 10px; background-color: #fff8e1; border-left: 4px solid #ff9800; border-radius: 3px;">
                        <label style="font-weight: 500; display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" id="run-f582-option" checked style="margin-right: 8px;">
                            Run "run f582 - set blog post dates" on all items with "page_type" of "post"
                        </label>
                        <div style="font-size: 12px; color: #666; margin-top: 5px; margin-left: 20px;">
                            This will automatically find 8 blog posts and back date them, and then future date the rest, using a random interval (down to day, hour, minute, second) of between 4 and 11 days
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <button id="create-pages-btn" class="button button-primary">f47 - Create Selected Pages/Posts in WP</button>
                            
                            <!-- Range Selection Buttons -->
                            <div style="display: flex; align-items: center; gap: 5px; margin-left: 20px; flex-wrap: wrap;">
                                <span style="font-weight: 500; color: #666; font-size: 12px; margin-right: 5px;">Quick Select (20s):</span>
                                <button type="button" class="button button-secondary range-select-btn" data-start="1" data-end="20" style="font-size: 11px; padding: 3px 8px;">1-20</button>
                                <button type="button" class="button button-secondary range-select-btn" data-start="21" data-end="40" style="font-size: 11px; padding: 3px 8px;">21-40</button>
                                <button type="button" class="button button-secondary range-select-btn" data-start="41" data-end="60" style="font-size: 11px; padding: 3px 8px;">41-60</button>
                                <button type="button" class="button button-secondary range-select-btn" data-start="61" data-end="80" style="font-size: 11px; padding: 3px 8px;">61-80</button>
                                <button type="button" class="button" id="clear-all-btn" style="font-size: 11px; padding: 3px 8px; color: #d63384;">Clear All</button>
                            </div>
                            
                            <!-- Range Selection Buttons - Increments of 10 -->
                            <div style="display: flex; align-items: center; gap: 5px; margin-left: 20px; margin-top: 5px; flex-wrap: wrap;">
                                <span style="font-weight: 500; color: #666; font-size: 12px; margin-right: 5px;">Quick Select (10s):</span>
                                <button type="button" class="button button-secondary range-select-btn-10" data-start="1" data-end="10" style="font-size: 11px; padding: 3px 8px;">1-10</button>
                                <button type="button" class="button button-secondary range-select-btn-10" data-start="11" data-end="20" style="font-size: 11px; padding: 3px 8px;">11-20</button>
                                <button type="button" class="button button-secondary range-select-btn-10" data-start="21" data-end="30" style="font-size: 11px; padding: 3px 8px;">21-30</button>
                                <button type="button" class="button button-secondary range-select-btn-10" data-start="31" data-end="40" style="font-size: 11px; padding: 3px 8px;">31-40</button>
                                <button type="button" class="button button-secondary range-select-btn-10" data-start="41" data-end="50" style="font-size: 11px; padding: 3px 8px;">41-50</button>
                                <button type="button" class="button button-secondary range-select-btn-10" data-start="51" data-end="60" style="font-size: 11px; padding: 3px 8px;">51-60</button>
                                <button type="button" class="button button-secondary range-select-btn-10" data-start="61" data-end="70" style="font-size: 11px; padding: 3px 8px;">61-70</button>
                                <button type="button" class="button button-secondary range-select-btn-10" data-start="71" data-end="80" style="font-size: 11px; padding: 3px 8px;">71-80</button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="data-table-container">
                        <!-- Table will be populated via JavaScript -->
                    </div>
                </div>

                <!-- Driggs Data Preview Section (Hidden initially) -->
                <div id="driggs-preview-section" style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-top: 20px; display: none;">
                    <h3 style="margin: 0 0 15px 0;">Preview Imported Data - driggs data</h3>
                    
                    <!-- Driggs Data Import Button -->
                    <div style="margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                            <button id="import-driggs-data" type="button" class="button button-primary" style="background: #d54e21; border-color: #d54e21; padding: 8px 16px; font-size: 14px; font-weight: 500;">
                                f51 - import driggs data
                            </button>
                            
                            <!-- Auto Import Option -->
                            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #e8f5e8; border: 1px solid #4caf50; border-radius: 4px;">
                                <label style="display: flex; align-items: center; cursor: pointer; margin: 0; font-size: 13px; font-weight: 500;">
                                    <input type="radio" name="driggs_import_mode" value="auto" id="driggs-import-auto" checked style="margin-right: 6px;">
                                    Use all db columns in import file for driggs import
                                </label>
                            </div>
                            
                            <!-- Manual Selection Option -->
                            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #fff8e1; border: 1px solid #ff9800; border-radius: 4px;">
                                <label style="display: flex; align-items: center; cursor: pointer; margin: 0; font-size: 13px; font-weight: 500;">
                                    <input type="radio" name="driggs_import_mode" value="manual" id="driggs-import-manual" style="margin-right: 6px;">
                                    Use only selected fields from table below
                                </label>
                            </div>
                        </div>
                        <div id="driggs-import-status" style="margin-top: 8px; font-size: 13px;"></div>
                    </div>
                    
                    <div style="margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #007cba; border-radius: 3px;">
                        <label style="font-weight: 500; display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" id="update-empty-fields" checked style="margin-right: 8px;">
                            Update all selected items to empty (if empty)
                        </label>
                        <div style="font-size: 12px; color: #666; margin-top: 5px; margin-left: 20px;">
                            When checked: Empty values from import will set database columns to empty<br>
                            When unchecked: Empty values from import will be ignored (existing data preserved)
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px; padding: 10px; background-color: #f0f8ff; border-left: 4px solid #2196F3; border-radius: 3px;">
                        <label style="font-weight: 500; display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" id="update-site-title" checked style="margin-right: 8px;">
                            Update WP Native Site Title with "driggs_brand_name" value
                        </label>
                        <div style="font-size: 12px; color: #666; margin-top: 5px; margin-left: 20px;">
                            When checked: The WordPress site title (Settings > General) will be updated with the driggs_brand_name value from the import
                        </div>
                    </div>
                    
                    <div id="driggs-table-container">
                        <!-- Driggs data table will be populated via JavaScript -->
                    </div>
                    </div>
                    
                    <!-- Error Reporting Tab Content -->
                    <div id="error-tab-content" class="tab-content" style="display: none;">
                        <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 5px;">
                            <h3 style="margin-top: 0;">Detailed Error Information</h3>
                            <div style="margin-bottom: 15px;">
                                <p>This tab shows detailed error information from import attempts. Error details will automatically appear here when imports fail.</p>
                            </div>
                            
                            <div id="error-log-container">
                                <textarea id="error-log-display" readonly style="width: 100%; height: 400px; font-family: 'Courier New', monospace; font-size: 12px; background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; resize: vertical; line-height: 1.4;" placeholder="No errors recorded yet. Import errors will appear here with full details including:&#10;- Error messages&#10;- Stack traces&#10;- Request data&#10;- Server responses&#10;- Timestamps"></textarea>
                            </div>
                            
                            <div style="margin-top: 15px; display: flex; gap: 10px;">
                                <button id="clear-error-log" class="button" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 3px;">Clear Error Log</button>
                                <button id="copy-error-log" class="button" style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 3px;">Copy to Clipboard</button>
                                <span id="copy-feedback" style="color: #28a745; font-size: 12px; display: none; align-self: center;">Copied to clipboard!</span>
                            </div>
                        </div>
                    </div>
                    
                </div> <!-- End tab-content -->
                
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let importedData = null;
            let errorLog = [];
            
            // Tab switching functionality
            $('.plasma-tab').click(function() {
                const tabId = $(this).attr('id');
                
                // Remove active class from all tabs and content
                $('.plasma-tab').removeClass('active').css({
                    'background': '#f1f1f1',
                    'color': '#333'
                });
                $('.tab-content').removeClass('active').hide();
                
                // Add active class to clicked tab
                $(this).addClass('active').css({
                    'background': '#fff',
                    'color': '#0073aa'
                });
                
                // Show corresponding content
                if (tabId === 'tab-main') {
                    $('#main-tab-content').addClass('active').show();
                } else if (tabId === 'tab-error') {
                    $('#error-tab-content').addClass('active').show();
                }
            });
            
            // Error reporting functions
            function addErrorToLog(error) {
                const timestamp = new Date().toISOString();
                const errorEntry = {
                    timestamp: timestamp,
                    error: error,
                    userAgent: navigator.userAgent,
                    url: window.location.href
                };
                
                errorLog.push(errorEntry);
                updateErrorDisplay();
                
                // Also show the compact error at top
                showCompactError(error);
            }
            
            function updateErrorDisplay() {
                let logText = '';
                errorLog.forEach((entry, index) => {
                    logText += `=== ERROR ${index + 1} [${entry.timestamp}] ===\n`;
                    logText += `URL: ${entry.url}\n`;
                    logText += `User Agent: ${entry.userAgent}\n`;
                    logText += `\nERROR DETAILS:\n`;
                    if (typeof entry.error === 'object') {
                        logText += JSON.stringify(entry.error, null, 2);
                    } else {
                        logText += entry.error;
                    }
                    logText += '\n\n' + '='.repeat(80) + '\n\n';
                });
                
                $('#error-log-display').val(logText);
            }
            
            function showCompactError(error) {
                let errorText = '';
                if (typeof error === 'object') {
                    errorText = JSON.stringify(error, null, 2);
                } else {
                    errorText = error;
                }
                
                $('#error-details-text').val(errorText);
                $('#error-reporting-container').show();
            }
            
            // Clear error log
            $('#clear-error-log').click(function() {
                if (confirm('Are you sure you want to clear the error log?')) {
                    errorLog = [];
                    updateErrorDisplay();
                    $('#error-reporting-container').hide();
                }
            });
            
            // Copy error log to clipboard
            $('#copy-error-log').click(function() {
                const logText = $('#error-log-display').val();
                navigator.clipboard.writeText(logText).then(function() {
                    $('#copy-feedback').show().delay(2000).fadeOut();
                });
            });

            // Handle file import
            $('#import-json-btn').click(function() {
                const fileInput = $('#plasma-json-file')[0];
                if (!fileInput.files[0]) {
                    alert('Please select a JSON file first.');
                    return;
                }

                const file = fileInput.files[0];
                if (!file.name.endsWith('.json')) {
                    alert('Please select a valid JSON file.');
                    return;
                }

                // Check selected import method
                const importMethod = $('input[name="import_method"]:checked').val();
                
                if (importMethod === 'file_based') {
                    handleFileBasedImport(file);
                } else {
                    handleDirectImport(file);
                }
            });
            
            // Direct import method (existing)
            function handleDirectImport(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const jsonData = JSON.parse(e.target.result);
                        
                        if (!jsonData.pages || !Array.isArray(jsonData.pages)) {
                            alert('Invalid JSON format. Expected "pages" array.');
                            return;
                        }

                        importedData = jsonData;
                        displayDataTable(jsonData.pages);
                        
                        // Handle driggs data if present
                        if (jsonData.driggs_data) {
                            displayDriggsDataTable(jsonData.driggs_data);
                            $('#driggs-preview-section').show();
                        }
                        
                        $('#upload-status').html('<span style="color: green;">✓ JSON file loaded successfully! ' + jsonData.pages.length + ' pages found. (Direct method)</span>');
                        $('#data-preview-section').show();
                        
                        // Enable the consolidated import button after successful file load
                        $('#import-all-btn').prop('disabled', false);

                    } catch (error) {
                        alert('Error parsing JSON file: ' + error.message);
                        $('#upload-status').html('<span style="color: red;">✗ Error parsing JSON file.</span>');
                        $('#import-all-btn').prop('disabled', true);
                    }
                };
                reader.readAsText(file);
            }
            
            // File-based import method (new)
            function handleFileBasedImport(file) {
                $('#upload-status').html('<span style="color: blue;">📤 Uploading file to server for processing...</span>');
                
                const formData = new FormData();
                formData.append('action', 'ruplin_upload_json_file');
                formData.append('json_file', file);
                formData.append('disable_slash_removal', $('#disable-slash-removal').is(':checked') ? 'true' : 'false');
                formData.append('nonce', '<?php echo wp_create_nonce("grove_file_upload"); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // File uploaded successfully, parse the data for preview
                            importedData = response.data.json_data;
                            importedData.file_id = response.data.file_id; // Store file ID for batch processing
                            
                            displayDataTable(importedData.pages);
                            
                            // Handle driggs data if present
                            if (importedData.driggs_data) {
                                displayDriggsDataTable(importedData.driggs_data);
                                $('#driggs-preview-section').show();
                            }
                            
                            $('#upload-status').html('<span style="color: green;">✓ File uploaded successfully! ' + importedData.pages.length + ' pages found. (File-based method - will process in batches)</span>');
                            $('#data-preview-section').show();
                            $('#import-all-btn').prop('disabled', false);
                        } else {
                            alert('Upload failed: ' + response.data);
                            $('#upload-status').html('<span style="color: red;">✗ File upload failed.</span>');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Upload error: ' + error);
                        $('#upload-status').html('<span style="color: red;">✗ Upload error occurred.</span>');
                    }
                });
            }

            // Update range selection buttons based on page count
            function updateRangeSelectionButtons(totalPages) {
                const rangeContainer = $('.range-select-btn').parent();
                if (rangeContainer.length === 0) return; // Exit if container not found
                
                // Clear existing range buttons
                $('.range-select-btn').remove();
                
                // Generate new range buttons based on total pages
                let rangeButtonsHTML = '';
                const rangeSize = 20;
                
                for (let start = 1; start <= totalPages; start += rangeSize) {
                    const end = Math.min(start + rangeSize - 1, totalPages);
                    rangeButtonsHTML += `<button type="button" class="button button-secondary range-select-btn" data-start="${start}" data-end="${end}" style="font-size: 11px; padding: 3px 8px;">${start}-${end}</button>`;
                }
                
                // Insert the new buttons before the "Clear All" button
                $('#clear-all-btn').before(rangeButtonsHTML);
                
                // Re-attach event handlers for new buttons
                $('.range-select-btn').click(handleRangeButtonClick);
                
                // Also generate and handle 10-increment buttons
                updateRange10Buttons(totalPages);
            }
            
            // Update 10-increment range selection buttons
            function updateRange10Buttons(totalPages) {
                // Find the 10s container by looking for the span that says "Quick Select (10s):"
                const container10 = $('span:contains("Quick Select (10s):")').parent();
                
                if (container10.length > 0) {
                    // Clear existing 10-increment buttons in this container
                    container10.find('.range-select-btn-10').remove();
                    
                    // Generate new 10-increment range buttons
                    let range10ButtonsHTML = '';
                    const range10Size = 10;
                    
                    for (let start = 1; start <= Math.min(totalPages, 80); start += range10Size) {
                        const end = Math.min(start + range10Size - 1, totalPages, 80);
                        range10ButtonsHTML += `<button type="button" class="button button-secondary range-select-btn-10" data-start="${start}" data-end="${end}" style="font-size: 11px; padding: 3px 8px;">${start}-${end}</button>`;
                    }
                    
                    // Append the new buttons to the container
                    container10.append(range10ButtonsHTML);
                    
                    // Attach event handlers for 10-increment buttons
                    $('.range-select-btn-10').click(handleRangeButtonClick);
                    
                    console.log(`Generated ${Math.ceil(Math.min(totalPages, 80) / 10)} dynamic 10-increment buttons for ${totalPages} total pages`);
                } else {
                    console.log('Could not find 10s container - buttons may not be generated');
                }
            }
            
            // Unified range button click handler
            function handleRangeButtonClick() {
                const startRange = parseInt($(this).data('start'));
                const endRange = parseInt($(this).data('end'));
                
                // First uncheck all
                $('.page-checkbox').prop('checked', false);
                $('#select-all-pages').prop('checked', false);
                
                // Then check the range (convert from 1-based to 0-based indexing)
                $('.page-checkbox').each(function(index) {
                    const pageNumber = index + 1; // Convert to 1-based
                    if (pageNumber >= startRange && pageNumber <= endRange) {
                        $(this).prop('checked', true);
                    }
                });
                
                // Provide visual feedback
                const selectedCount = $('.page-checkbox:checked').length;
                $(this).css('background-color', '#0073aa');
                $(this).css('color', '#fff');
                
                // Reset other range buttons (both types)
                $('.range-select-btn, .range-select-btn-10').not(this).css('background-color', '').css('color', '');
                
                // Show temporary feedback
                const originalText = $(this).text();
                $(this).text(`✓ ${selectedCount}`);
                setTimeout(() => {
                    $(this).text(originalText);
                }, 1500);
            }

            // Display data in table
            function displayDataTable(pages) {
                if (!pages || pages.length === 0) {
                    $('#data-table-container').html('<p>No pages found in the JSON file.</p>');
                    return;
                }

                // Get all unique column names from all pages
                const allColumns = new Set();
                pages.forEach(page => {
                    Object.keys(page).forEach(key => allColumns.add(key));
                });
                
                // Create ordered columns array with specific order: page_type, moniker, page_archetype, page_status, page_title, staircase_page_template_desired, others
                const allColumnsArray = Array.from(allColumns);
                const columns = [];
                const priorityColumns = ['page_type', 'moniker', 'page_archetype', 'page_status', 'page_title', 'staircase_page_template_desired'];
                
                // Add priority columns in order if they exist
                priorityColumns.forEach(col => {
                    if (allColumnsArray.includes(col)) {
                        columns.push(col);
                    }
                });
                
                // Add staircase_page_template_desired even if it doesn't exist in data (new column)
                if (!allColumnsArray.includes('staircase_page_template_desired') && !columns.includes('staircase_page_template_desired')) {
                    columns.push('staircase_page_template_desired');
                }
                
                // Then add all other columns (excluding priority columns since they're already added)
                allColumnsArray.forEach(col => {
                    if (!priorityColumns.includes(col)) {
                        columns.push(col);
                    }
                });

                // Build table
                let tableHTML = '<table class="widefat striped" style="margin-top: 15px;">';
                
                // Header
                tableHTML += '<thead><tr>';
                tableHTML += '<th style="width: 40px;"><input type="checkbox" id="select-all-pages"></th>';
                columns.forEach(col => {
                    tableHTML += '<th style="padding: 8px; font-weight: bold;">' + escapeHtml(col) + '</th>';
                });
                tableHTML += '</tr></thead>';
                
                // Body
                tableHTML += '<tbody>';
                pages.forEach((page, index) => {
                    tableHTML += '<tr>';
                    tableHTML += '<td style="padding: 8px;"><input type="checkbox" class="page-checkbox" data-index="' + index + '"></td>';
                    columns.forEach(col => {
                        let value = page[col] || '';
                        
                        if (['page_type', 'moniker', 'page_archetype', 'page_status', 'page_title', 'staircase_page_template_desired'].includes(col)) {
                            // Make these columns editable with input fields
                            let maxWidth = '150px';
                            if (col === 'moniker') {
                                maxWidth = '180px';
                            } else if (col === 'page_archetype') {
                                maxWidth = '140px';
                            } else if (col === 'page_status') {
                                maxWidth = '120px';
                            } else if (col === 'page_title') {
                                maxWidth = '200px';
                            } else if (col === 'staircase_page_template_desired') {
                                maxWidth = '160px';
                            }
                            
                            tableHTML += '<td style="padding: 4px; max-width: ' + maxWidth + ';" class="editable-cell" data-column="' + col + '" data-row-index="' + index + '">';
                            tableHTML += '<input type="text" class="editable-input ' + col + '-input" value="' + escapeHtml(String(value)) + '" ';
                            tableHTML += 'style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px; font-size: 12px;" ';
                            tableHTML += 'data-original-value="' + escapeHtml(String(value)) + '" data-column="' + col + '" />';
                            tableHTML += '</td>';
                        } else {
                            // Regular non-editable cells
                            // Truncate long content for display
                            let displayValue = value;
                            if (typeof value === 'string' && value.length > 20) {
                                displayValue = value.substring(0, 20) + '...';
                            } else if (typeof value === 'object') {
                                displayValue = JSON.stringify(value).substring(0, 20) + '...';
                            }
                            
                            tableHTML += '<td style="padding: 8px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + escapeHtml(page[col] || '') + '">';
                            tableHTML += escapeHtml(String(displayValue));
                            tableHTML += '</td>';
                        }
                    });
                    tableHTML += '</tr>';
                });
                tableHTML += '</tbody></table>';

                $('#data-table-container').html(tableHTML);
                
                // Initialize staircase_page_template_desired field in data if it doesn't exist
                if (importedData && importedData.pages) {
                    importedData.pages.forEach(page => {
                        if (!page.hasOwnProperty('staircase_page_template_desired')) {
                            page.staircase_page_template_desired = '';
                        }
                    });
                }

                // Update range selection buttons dynamically based on page count
                updateRangeSelectionButtons(pages.length);

                // Handle select all checkbox
                $('#select-all-pages').change(function() {
                    $('.page-checkbox').prop('checked', this.checked);
                });
                
                // Handle clear all button
                $('#clear-all-btn').click(function() {
                    $('.page-checkbox').prop('checked', false);
                    $('#select-all-pages').prop('checked', false);
                    $('.range-select-btn, .range-select-btn-10').css('background-color', '').css('color', '');
                    
                    // Visual feedback
                    const originalText = $(this).text();
                    $(this).text('✓ Cleared');
                    $(this).css('color', '#28a745');
                    setTimeout(() => {
                        $(this).text(originalText);
                        $(this).css('color', '#d63384');
                    }, 1000);
                });
                
                // Handle editable input changes to update data for all editable columns
                $('.editable-input').on('change input', function() {
                    const rowIndex = $(this).closest('.editable-cell').data('row-index');
                    const columnName = $(this).data('column');
                    const newValue = $(this).val();
                    
                    // Update the importedData with new value
                    if (importedData && importedData.pages && importedData.pages[rowIndex]) {
                        importedData.pages[rowIndex][columnName] = newValue;
                    }
                    
                    // Visual feedback for changed values
                    const originalValue = $(this).data('original-value');
                    if (newValue !== originalValue) {
                        $(this).css('background-color', '#fff3cd'); // Light yellow background for changed values
                        $(this).css('border-color', '#ffeaa7');
                    } else {
                        $(this).css('background-color', '');
                        $(this).css('border-color', '#ddd');
                    }
                });
            }

            // Display driggs data in table
            function displayDriggsDataTable(driggsData) {
                if (!driggsData || Object.keys(driggsData).length === 0) {
                    $('#driggs-table-container').html('<p>No driggs data found in the JSON file.</p>');
                    return;
                }

                // Dynamic field detection - get all fields from the JSON driggs_data
                const sitesprenFields = Object.keys(driggsData).filter(field => {
                    // Basic validation: field names should be alphanumeric with underscores
                    return /^[a-zA-Z0-9_]+$/.test(field);
                }).sort(); // Sort alphabetically for consistent display
                
                console.log(`Dynamic Driggs Import: Found ${sitesprenFields.length} fields in JSON data:`, sitesprenFields);

                // Build summary info
                let summaryHTML = '<div style="margin-bottom: 15px; padding: 10px; background-color: #e1f5fe; border-left: 4px solid #0277bd; border-radius: 3px;">';
                summaryHTML += '<strong>🔄 Dynamic Field Detection:</strong> ';
                summaryHTML += `Found <strong>${sitesprenFields.length} fields</strong> in JSON driggs_data that will be matched against wp_zen_sitespren database columns by exact name.`;
                summaryHTML += '</div>';

                // Build table
                let tableHTML = summaryHTML + '<table class="widefat striped" style="margin-top: 15px;">';
                
                // Header
                tableHTML += '<thead><tr>';
                tableHTML += '<th style="width: 40px;"><input type="checkbox" id="select-all-driggs"></th>';
                tableHTML += '<th style="padding: 8px; font-weight: bold;">Actual Field</th>';
                tableHTML += '<th style="padding: 8px; font-weight: bold;">Your Values</th>';
                tableHTML += '<th style="padding: 8px; font-weight: bold; text-align: center;">-</th>';
                tableHTML += '</tr></thead>';
                
                // Body
                tableHTML += '<tbody>';
                sitesprenFields.forEach((field, index) => {
                    let value = driggsData[field] || '';
                    
                    // Truncate long content
                    let displayValue = value;
                    if (typeof value === 'string' && value.length > 50) {
                        displayValue = value.substring(0, 50) + '...';
                    } else if (typeof value === 'object') {
                        displayValue = JSON.stringify(value).substring(0, 50) + '...';
                    }
                    
                    tableHTML += '<tr>';
                    tableHTML += '<td style="padding: 8px;"><input type="checkbox" class="driggs-checkbox" data-field="' + field + '"></td>';
                    tableHTML += '<td style="padding: 8px; font-weight: bold; font-size: 14px; text-transform: lowercase;">' + escapeHtml(field) + '</td>';
                    tableHTML += '<td style="padding: 8px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + escapeHtml(String(value)) + '">';
                    tableHTML += escapeHtml(String(displayValue));
                    tableHTML += '</td>';
                    tableHTML += '<td style="padding: 8px; text-align: center;">-</td>';
                    tableHTML += '</tr>';
                });
                tableHTML += '</tbody></table>';

                $('#driggs-table-container').html(tableHTML);

                // Handle select all checkbox for driggs data
                $('#select-all-driggs').change(function() {
                    $('.driggs-checkbox').prop('checked', this.checked);
                });
            }

            // Handle create pages button
            $('#create-pages-btn').click(function() {
                const selectedIndexes = [];
                $('.page-checkbox:checked').each(function() {
                    selectedIndexes.push(parseInt($(this).data('index')));
                });

                if (selectedIndexes.length === 0) {
                    alert('Please select at least one page to create.');
                    return;
                }

                // Check homepage assignment option
                const setHomepageOption = $('#set-homepage-option').is(':checked');
                
                // Get the empty fields update setting
                const updateEmptyFields = $('#update-empty-fields').is(':checked');
                
                // Get the F582 date processing setting
                const runF582Option = $('#run-f582-option').is(':checked');
                
                // Check if we're using file-based import
                const isFileBased = importedData && importedData.file_id;
                
                if (isFileBased) {
                    // File-based import: process in batches
                    handleFileBasedPageImport(selectedIndexes, setHomepageOption, updateEmptyFields, runF582Option);
                } else {
                    // Direct import: use existing method
                    handleDirectPageImport(selectedIndexes, setHomepageOption, updateEmptyFields, runF582Option);
                }
            });
            
            // Direct page import (existing method)
            function handleDirectPageImport(selectedIndexes, setHomepageOption, updateEmptyFields, runF582Option) {
                // Prepare the data for import
                const selectedPages = [];
                selectedIndexes.forEach(function(index) {
                    if (importedData.pages[index]) {
                        selectedPages.push(importedData.pages[index]);
                    }
                });
                
                if (setHomepageOption) {
                    // Count pages with page_archetype = "homepage" among selected pages
                    const homepagePages = selectedPages.filter(function(page) {
                        return page.page_archetype === 'homepage';
                    });
                    
                    if (homepagePages.length > 1) {
                        alert('Error: Multiple pages have page_archetype set to "homepage". Please ensure only one page is marked as homepage, or uncheck the "Set as front page" option.');
                        return;
                    }
                }

                if (!confirm('Create ' + selectedIndexes.length + ' pages/posts in WordPress? (Direct method)')) {
                    return;
                }

                // Disable button during processing
                const $btn = $('#create-pages-btn');
                const originalText = $btn.text();
                $btn.prop('disabled', true).text('Creating pages...');

                // Make AJAX call to import pages
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ruplin_plasma_import',
                        pages: selectedPages,
                        update_empty_fields: updateEmptyFields ? 'true' : 'false',
                        set_homepage_option: setHomepageOption ? 'true' : 'false',
                        run_f582_option: runF582Option ? 'true' : 'false',
                        disable_slash_removal: $('#disable-slash-removal').is(':checked') ? 'true' : 'false',
                        nonce: '<?php echo wp_create_nonce("ruplin_plasma_import"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('✅ Success: ' + response.data.message);
                            
                            // Show detailed results if available
                            if (response.data.details) {
                                const details = response.data.details;
                                let detailMsg = '';
                                if (details.success && details.success.length > 0) {
                                    detailMsg += 'Successfully created:\n';
                                    details.success.forEach(function(item) {
                                        detailMsg += '- ' + item.title + ' (Post ID: ' + item.post_id + ')\n';
                                    });
                                }
                                if (details.errors && details.errors.length > 0) {
                                    detailMsg += '\nErrors:\n';
                                    details.errors.forEach(function(item) {
                                        detailMsg += '- ' + (item.title || 'Unknown') + ': ' + item.message + '\n';
                                    });
                                    
                                    // If there were errors, log them for detailed analysis
                                    addErrorToLog({
                                        type: 'Import Partial Success with Errors',
                                        message: response.data.message,
                                        details: details,
                                        requestData: {
                                            pages: selectedPages,
                                            update_empty_fields: updateEmptyFields
                                        }
                                    });
                                }
                                if (detailMsg) {
                                    console.log('Import Details:', detailMsg);
                                }
                            }
                        } else {
                            // Import failed - log detailed error
                            const errorData = {
                                type: 'Import Failed',
                                message: response.data ? response.data.message : 'Unknown error occurred',
                                fullResponse: response,
                                requestData: {
                                    pages: selectedPages,
                                    update_empty_fields: updateEmptyFields
                                }
                            };
                            
                            addErrorToLog(errorData);
                            alert('❌ Error: ' + errorData.message + '\n\nDetailed error information has been logged. Check the Error Reporting tab for full details.');
                        }
                    },
                    error: function(xhr, status, error) {
                        // AJAX request failed - log comprehensive error
                        const errorData = {
                            type: 'AJAX Request Failed',
                            status: status,
                            error: error,
                            statusCode: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            requestData: {
                                pages: selectedPages,
                                update_empty_fields: updateEmptyFields
                            }
                        };
                        
                        addErrorToLog(errorData);
                        alert('❌ AJAX Error: ' + error + '\n\nFull error details have been logged. Check the Error Reporting tab for complete information.');
                        console.error('AJAX Error:', xhr, status, error);
                    },
                    complete: function() {
                        // Re-enable button
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            }
            
            // File-based page import (new method)
            function handleFileBasedPageImport(selectedIndexes, setHomepageOption, updateEmptyFields, runF582Option) {
                if (!confirm('Create ' + selectedIndexes.length + ' pages/posts in WordPress? (File-based method - will process in batches)')) {
                    return;
                }

                // Disable button during processing
                const $btn = $('#create-pages-btn');
                const originalText = $btn.text();
                $btn.prop('disabled', true).text('Processing in batches...');
                
                // Show progress
                $('#consolidated-import-status').show();
                $('#import-progress-details').html('Starting file-based batch import...');
                
                const batchSize = 15; // Process 15 pages per batch
                let currentBatch = 0;
                let totalProcessed = 0;
                let totalErrors = 0;
                
                function processBatch() {
                    const batchStart = currentBatch * batchSize;
                    const batchEnd = Math.min(batchStart + batchSize, selectedIndexes.length);
                    
                    if (batchStart >= selectedIndexes.length) {
                        // All batches complete
                        $btn.prop('disabled', false).text(originalText);
                        const finalMsg = `✅ File-based import completed! ${totalProcessed} pages processed with ${totalErrors} errors.`;
                        $('#import-progress-details').append('<br><br><strong>' + finalMsg + '</strong>');
                        alert(finalMsg);
                        return;
                    }
                    
                    $('#import-progress-details').append('<br>Processing batch ' + (currentBatch + 1) + ' (pages ' + (batchStart + 1) + '-' + batchEnd + ')...');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'grove_process_file_batch',
                            file_id: importedData.file_id,
                            batch_start: batchStart,
                            batch_size: batchSize,
                            selected_indexes: selectedIndexes.slice(batchStart, batchEnd),
                            update_empty_fields: updateEmptyFields ? 'true' : 'false',
                            set_homepage_option: setHomepageOption ? 'true' : 'false',
                            run_f582_option: runF582Option ? 'true' : 'false',
                            disable_slash_removal: $('#disable-slash-removal').is(':checked') ? 'true' : 'false',
                            nonce: '<?php echo wp_create_nonce("ruplin_plasma_import"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                totalProcessed += response.data.pages_processed;
                                totalErrors += response.data.error_count || 0;
                                
                                $('#import-progress-details').append('<br>✅ Batch ' + (currentBatch + 1) + ': ' + response.data.message);
                                
                                // Process next batch
                                currentBatch++;
                                setTimeout(processBatch, 500); // Small delay between batches
                            } else {
                                $('#import-progress-details').append('<br>❌ Batch ' + (currentBatch + 1) + ' failed: ' + response.data);
                                totalErrors += batchSize; // Assume all failed
                                
                                // Continue with next batch anyway
                                currentBatch++;
                                setTimeout(processBatch, 500);
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#import-progress-details').append('<br>❌ Batch ' + (currentBatch + 1) + ' AJAX error: ' + error);
                            totalErrors += batchSize;
                            
                            // Continue with next batch
                            currentBatch++;
                            setTimeout(processBatch, 500);
                        }
                    });
                }
                
                // Start processing
                processBatch();
            }

            // Handle driggs data import button
            $('#import-driggs-data').click(function() {
                if (!importedData || !importedData.driggs_data) {
                    alert('No driggs data found. Please import a JSON file with driggs_data first.');
                    return;
                }

                // Check import mode (auto or manual)
                const importMode = $('input[name="driggs_import_mode"]:checked').val();
                let selectedFields = [];
                let driggsDataForImport = {};
                
                if (importMode === 'auto') {
                    // Auto mode: use ALL fields from JSON driggs_data
                    selectedFields = Object.keys(importedData.driggs_data).filter(field => {
                        // Same validation as the dynamic table display
                        return /^[a-zA-Z0-9_]+$/.test(field);
                    });
                    
                    // Use all validated fields
                    selectedFields.forEach(function(field) {
                        driggsDataForImport[field] = importedData.driggs_data[field];
                    });
                    
                    console.log('Auto mode: importing all', selectedFields.length, 'fields from JSON:', selectedFields);
                    
                } else {
                    // Manual mode: use only selected fields from table
                    $('.driggs-checkbox:checked').each(function() {
                        selectedFields.push($(this).data('field'));
                    });

                    if (selectedFields.length === 0) {
                        alert('Please select at least one driggs data field to import, or switch to "Auto" mode.');
                        return;
                    }
                    
                    // Prepare driggs data for import
                    selectedFields.forEach(function(field) {
                        driggsDataForImport[field] = importedData.driggs_data[field];
                    });
                    
                    console.log('Manual mode: importing', selectedFields.length, 'selected fields:', selectedFields);
                }

                const modeText = importMode === 'auto' ? 'ALL available' : 'selected';
                if (!confirm(`Import ${selectedFields.length} ${modeText} driggs data fields to wp_zen_sitespren table?`)) {
                    return;
                }

                // Disable button during processing
                const $btn = $('#import-driggs-data');
                const originalText = $btn.text();
                $btn.prop('disabled', true).text('Importing driggs data...');
                
                // Clear status
                $('#driggs-import-status').html('');

                // Make AJAX call to import driggs data
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ruplin_driggs_data_import',
                        driggs_data: driggsDataForImport,
                        disable_slash_removal: $('#disable-slash-removal').is(':checked') ? 'true' : 'false',
                        update_site_title: $('#update-site-title').is(':checked') ? 'true' : 'false',
                        nonce: '<?php echo wp_create_nonce("grove_driggs_import"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#driggs-import-status').html('<span style="color: #46b450;">✅ ' + response.data.message + '</span>');
                        } else {
                            const errorMsg = response.data ? response.data.message : 'Unknown error occurred';
                            $('#driggs-import-status').html('<span style="color: #dc3232;">❌ Error: ' + errorMsg + '</span>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#driggs-import-status').html('<span style="color: #dc3232;">❌ AJAX Error: ' + error + '</span>');
                        console.error('AJAX Error:', xhr, status, error);
                    },
                    complete: function() {
                        // Re-enable button
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Handle consolidated import button (f47 + f51)
            $('#import-all-btn').click(function() {
                if (!importedData || !importedData.pages) {
                    alert('Please import a JSON file first.');
                    return;
                }
                
                // Check if we have pages to import
                if (importedData.pages.length === 0) {
                    alert('No pages found in the imported data.');
                    return;
                }
                
                // Auto-select all pages
                $('.page-checkbox').prop('checked', true);
                
                // Get all selected pages (which is all of them now)
                const selectedPages = importedData.pages;
                
                // Check homepage assignment option
                const setHomepageOption = $('#set-homepage-option').is(':checked');
                
                if (setHomepageOption) {
                    // Count pages with page_archetype = "homepage"
                    const homepagePages = selectedPages.filter(function(page) {
                        return page.page_archetype === 'homepage';
                    });
                    
                    if (homepagePages.length > 1) {
                        alert('Error: Multiple pages have page_archetype set to "homepage". Please ensure only one page is marked as homepage, or uncheck the "Set as front page" option.');
                        return;
                    }
                }
                
                // Prepare driggs data if it exists
                let driggsDataForImport = null;
                if (importedData.driggs_data && Object.keys(importedData.driggs_data).length > 0) {
                    // Check import mode for driggs data
                    const importMode = $('input[name="driggs_import_mode"]:checked').val();
                    
                    if (importMode === 'auto') {
                        // Auto mode: use ALL fields from JSON driggs_data  
                        const validFields = Object.keys(importedData.driggs_data).filter(field => {
                            return /^[a-zA-Z0-9_]+$/.test(field);
                        });
                        
                        driggsDataForImport = {};
                        validFields.forEach(function(field) {
                            driggsDataForImport[field] = importedData.driggs_data[field];
                        });
                        
                        console.log('Consolidated import - Auto mode: using all', validFields.length, 'driggs fields');
                    } else {
                        // Manual mode: use only selected fields
                        const selectedFields = [];
                        $('.driggs-checkbox:checked').each(function() {
                            selectedFields.push($(this).data('field'));
                        });
                        
                        if (selectedFields.length > 0) {
                            driggsDataForImport = {};
                            selectedFields.forEach(function(field) {
                                driggsDataForImport[field] = importedData.driggs_data[field];
                            });
                            console.log('Consolidated import - Manual mode: using', selectedFields.length, 'selected driggs fields');
                        } else {
                            console.log('Consolidated import - Manual mode: no driggs fields selected');
                        }
                    }
                }
                
                const totalOperations = driggsDataForImport ? 2 : 1;
                const confirmMessage = driggsDataForImport 
                    ? `This will import ${selectedPages.length} pages/posts AND all driggs data fields. Continue?`
                    : `This will import ${selectedPages.length} pages/posts. Continue?`;
                
                if (!confirm(confirmMessage)) {
                    return;
                }
                
                // Show progress container
                $('#consolidated-import-status').show();
                $('#import-progress-details').html('Starting consolidated import...');
                
                // Disable button during processing
                const $btn = $('#import-all-btn');
                const originalText = $btn.text();
                $btn.prop('disabled', true).text('Importing...');
                
                // Track results
                let pagesImported = false;
                let driggsImported = false;
                let completedOperations = 0;
                
                // Function to check if all operations are complete
                function checkAllComplete() {
                    completedOperations++;
                    if (completedOperations === totalOperations) {
                        $btn.prop('disabled', false).text(originalText);
                        
                        let finalStatus = '';
                        if (pagesImported && driggsImported) {
                            finalStatus = '✅ Successfully imported pages/posts and driggs data!';
                        } else if (pagesImported && !driggsDataForImport) {
                            finalStatus = '✅ Successfully imported pages/posts!';
                        } else if (pagesImported && !driggsImported) {
                            finalStatus = '⚠️ Pages imported successfully, but driggs data import failed.';
                        } else {
                            finalStatus = '❌ Import failed. Check error details above.';
                        }
                        
                        $('#import-progress-details').append('<br><br><strong>' + finalStatus + '</strong>');
                    }
                }
                
                // Get settings
                const updateEmptyFields = $('#update-empty-fields').is(':checked');
                const runF582Option = $('#run-f582-option').is(':checked');
                
                // Step 1: Import Pages
                $('#import-progress-details').html('Step 1: Importing pages/posts...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ruplin_plasma_import',
                        pages: selectedPages,
                        update_empty_fields: updateEmptyFields ? 'true' : 'false',
                        set_homepage_option: setHomepageOption ? 'true' : 'false',
                        run_f582_option: runF582Option ? 'true' : 'false',
                        disable_slash_removal: $('#disable-slash-removal').is(':checked') ? 'true' : 'false',
                        nonce: '<?php echo wp_create_nonce("ruplin_plasma_import"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            pagesImported = true;
                            $('#import-progress-details').append('<br>✅ Pages: ' + response.data.message);
                            
                            // If we have driggs data, import it next
                            if (driggsDataForImport) {
                                $('#import-progress-details').append('<br><br>Step 2: Importing driggs data...');
                                
                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'ruplin_driggs_data_import',
                                        driggs_data: driggsDataForImport,
                                        nonce: '<?php echo wp_create_nonce("grove_driggs_import"); ?>'
                                    },
                                    success: function(driggsResponse) {
                                        if (driggsResponse.success) {
                                            driggsImported = true;
                                            $('#import-progress-details').append('<br>✅ Driggs: ' + driggsResponse.data.message);
                                        } else {
                                            const errorMsg = driggsResponse.data ? driggsResponse.data.message : 'Unknown error';
                                            $('#import-progress-details').append('<br>❌ Driggs: ' + errorMsg);
                                        }
                                        checkAllComplete();
                                    },
                                    error: function(xhr, status, error) {
                                        $('#import-progress-details').append('<br>❌ Driggs AJAX Error: ' + error);
                                        checkAllComplete();
                                    }
                                });
                            } else {
                                checkAllComplete();
                            }
                        } else {
                            const errorMsg = response.data ? response.data.message : 'Unknown error';
                            $('#import-progress-details').append('<br>❌ Pages: ' + errorMsg);
                            checkAllComplete();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#import-progress-details').append('<br>❌ Pages AJAX Error: ' + error);
                        checkAllComplete();
                    }
                });
            });

            // Utility function to escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
        </script>

        <style>
        .ruplin-button:hover {
            background: #A0522D !important;
        }
        .widefat th, .widefat td {
            border: 1px solid #ddd;
        }
        .widefat th {
            background: #f9f9f9;
        }
        .widefat tr:nth-child(even) {
            background: #f9f9f9;
        }
        .widefat tr:hover {
            background: #f0f8ff;
        }
        
        /* Tab styling */
        .plasma-tab {
            transition: all 0.2s ease;
        }
        .plasma-tab:hover {
            background: #e9ecef !important;
            color: #0073aa !important;
        }
        .plasma-tab.active:hover {
            background: #fff !important;
        }
        
        /* Tab content animation */
        .tab-content {
            animation: fadeIn 0.2s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Error reporting styles */
        #error-details-text, #error-log-display {
            font-family: 'Courier New', Consolas, 'Monaco', monospace !important;
        }
        </style>
        <?php
    }
    
    /**
     * AGGRESSIVE NOTICE SUPPRESSION
     * Removes all WordPress admin notices to prevent interference with our custom interface
     */
    private function suppress_all_admin_notices() {
        // Remove all admin notices immediately
        add_action('admin_print_styles', function() {
            // Remove all notice actions
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Remove specific plugin notices
            remove_all_actions('admin_notices', 10);
            remove_all_actions('admin_notices', 20);
            
            // Hide notices with CSS as backup
            echo '<style>
                .notice, .error, .updated, .update-nag, 
                .admin-notice, .plugin-update-tr,
                div[class*="notice"], div[class*="error"], div[class*="updated"],
                .wrap > .notice, .wrap > .error, .wrap > .updated,
                #message, .message, .settings-error {
                    display: none !important;
                }
                
                /* Hide specific plugin update notices */
                .plugin-update-tr, .plugin-install-php-update-required,
                .update-message, .updating-message,
                .plugin-update-tr td, .plugin-update-tr th {
                    display: none !important;
                }
                
                /* Hide theme notices */
                .theme-update, .theme-info, .available-theme,
                .theme-overlay, .theme-screenshot {
                    display: none !important;
                }
                
                /* Hide core update notices */
                .update-core-php, .update-php,
                .update-core, .core-updates {
                    display: none !important;
                }
            </style>';
        }, 1);
        
        // Additional suppression for late-loading notices
        add_action('admin_head', function() {
            echo '<style>
                .notice, .error, .updated, .update-nag,
                .admin-notice, .settings-error {
                    display: none !important;
                }
            </style>';
        }, 999);
        
        // Remove notices via JavaScript as final fallback
        add_action('admin_footer', function() {
            echo '<script>
                jQuery(document).ready(function($) {
                    // Hide all notice elements
                    $(".notice, .error, .updated, .update-nag, .admin-notice, .settings-error").hide();
                    
                    // Remove notice elements completely
                    setTimeout(function() {
                        $(".notice, .error, .updated, .update-nag, .admin-notice, .settings-error").remove();
                    }, 100);
                });
            </script>';
        }, 999);
    }
}