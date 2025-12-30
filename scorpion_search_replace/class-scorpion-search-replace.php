<?php
/**
 * Scorpion Search and Replace functionality for wp_pylons table
 * 
 * @package Ruplin
 * @subpackage ScorpionSearchReplace
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Scorpion_Search_Replace {
    
    /**
     * Initialize the search and replace functionality
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_scorpion_search', array($this, 'handle_search'));
        add_action('wp_ajax_scorpion_replace', array($this, 'handle_replace'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add aggressive notice suppression for this page
        add_action('current_screen', array($this, 'suppress_admin_notices_on_scorpion_page'), 1);
        add_action('admin_init', array($this, 'early_notice_suppression_scorpion'), 1);
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=pylon',
            'Scorpion Search & Replace',
            'Search & Replace',
            'manage_options',
            'scorpion_search_replace_mar',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        // Check for our page in multiple ways
        if (strpos($hook, 'scorpion_search_replace_mar') === false && 
            (!isset($_GET['page']) || $_GET['page'] !== 'scorpion_search_replace_mar')) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'scorpion_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('scorpion_search_replace_nonce')
        ));
    }
    
    /**
     * Render the admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Scorpion Search & Replace</h1>
            <p>Search and replace text in WordPress database tables.</p>
            
            <?php
            // Quick status check
            global $wpdb;
            $pylons_table = $wpdb->prefix . 'pylons';
            $posts_table = $wpdb->prefix . 'posts';
            $pylons_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $pylons_table));
            $pylons_count = 0;
            $posts_count = 0;
            if ($pylons_exists) {
                $pylons_count = $wpdb->get_var("SELECT COUNT(*) FROM {$pylons_table}");
            }
            $posts_count = $wpdb->get_var("SELECT COUNT(*) FROM {$posts_table}");
            ?>
            
            <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 10px; margin: 20px 0; border-radius: 4px;">
                <strong>Database Status:</strong><br>
                wp_pylons: <?php echo $pylons_exists ? '<span style="color: green;">✓ ' . number_format($pylons_count) . ' rows</span>' : '<span style="color: red;">✗ Not found</span>'; ?><br>
                wp_posts: <span style="color: green;">✓ <?php echo number_format($posts_count); ?> rows</span>
            </div>
            
            <div id="scorpion-messages" style="margin: 20px 0;"></div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin: 20px 0;">
                <h2>Step 1: Select Table</h2>
                <p>Choose which table to search in:</p>
                
                <div style="margin: 15px 0;">
                    <label style="display: block; margin-bottom: 10px; cursor: pointer;">
                        <input type="radio" name="search_table" value="wp_pylons" checked style="margin-right: 8px;">
                        <strong>Search in wp_pylons</strong> (all database columns)
                    </label>
                    <label style="display: block; margin-bottom: 10px; cursor: pointer;">
                        <input type="radio" name="search_table" value="wp_posts" style="margin-right: 8px;">
                        <strong>Search in wp_posts</strong> (post_title and post_content only)
                    </label>
                    <label style="display: block; margin-bottom: 10px; cursor: pointer;">
                        <input type="radio" name="search_table" value="wp_posts_slug" style="margin-right: 8px;">
                        <strong>Search in wp_posts</strong> (post_name field only - url slug)
                    </label>
                </div>
            </div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin: 20px 0;">
                <h2>Step 2: Search</h2>
                <p>Enter text to search for in the selected table:</p>
                
                <div style="margin: 15px 0;">
                    <label for="search_text" style="display: block; font-weight: bold; margin-bottom: 5px;">Search Text:</label>
                    <input type="text" id="search_text" style="width: 100%; max-width: 500px; padding: 8px;" placeholder="Enter text to search for...">
                </div>
                
                <button type="button" id="search_btn" class="button button-primary" style="margin-top: 10px;">
                    Search Selected Table
                </button>
                
                <div id="search_results" style="margin-top: 20px; display: none;">
                    <h3>Search Results</h3>
                    <div id="search_results_content"></div>
                </div>
            </div>
            
            <div id="replace_section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin: 20px 0; display: none;">
                <h2>Step 3: Replace</h2>
                <p>Enter replacement text and confirm the replacement:</p>
                
                <div style="margin: 15px 0;">
                    <label for="replace_text" style="display: block; font-weight: bold; margin-bottom: 5px;">Replace With:</label>
                    <input type="text" id="replace_text" style="width: 100%; max-width: 500px; padding: 8px;" placeholder="Enter replacement text...">
                </div>
                
                <div style="margin: 15px 0; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7;">
                    <strong>⚠️ Warning:</strong> This will permanently modify data in your database. Make sure you have a backup!
                </div>
                
                <button type="button" id="replace_btn" class="button button-secondary" style="margin-top: 10px; background: #d63638; border-color: #d63638; color: white;">
                    Perform Replacement
                </button>
            </div>
            
            <div id="replace_results" style="margin-top: 20px; display: none;">
                <h3>Replacement Results</h3>
                <div id="replace_results_content"></div>
            </div>
        </div>
        
        <script>
        // Define AJAX variables inline to ensure they're always available
        var scorpion_ajax = {
            ajax_url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
            nonce: '<?php echo esc_js(wp_create_nonce('scorpion_search_replace_nonce')); ?>'
        };
        
        jQuery(document).ready(function($) {
            $('#search_btn').click(function() {
                var searchText = $('#search_text').val().trim();
                
                if (!searchText) {
                    showMessage('Please enter text to search for.', 'error');
                    return;
                }
                
                $('#search_btn').prop('disabled', true).text('Searching...');
                $('#search_results').hide();
                $('#replace_section').hide();
                
                console.log('Starting search for:', searchText);
                console.log('AJAX URL:', scorpion_ajax.ajax_url);
                console.log('Nonce:', scorpion_ajax.nonce);
                
                $.ajax({
                    url: scorpion_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'scorpion_search',
                        search_text: searchText,
                        search_table: $('input[name="search_table"]:checked').val(),
                        nonce: scorpion_ajax.nonce
                    },
                    timeout: 30000, // 30 second timeout
                    success: function(response) {
                        console.log('AJAX Success:', response);
                        $('#search_btn').prop('disabled', false).text('Search Selected Table');
                        
                        if (response.success) {
                            $('#search_results_content').html(response.data.html);
                            $('#search_results').show();
                            
                            if (response.data.total_matches > 0) {
                                $('#replace_section').show();
                            }
                            
                            showMessage(response.data.message, 'success');
                        } else {
                            console.log('Search failed:', response.data);
                            showMessage(response.data || 'Search failed.', 'error');
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.log('AJAX Error:', textStatus, errorThrown);
                        console.log('XHR:', xhr);
                        $('#search_btn').prop('disabled', false).text('Search Selected Table');
                        showMessage('AJAX error: ' + textStatus + ' - ' + errorThrown, 'error');
                    }
                });
            });
            
            $('#replace_btn').click(function() {
                var searchText = $('#search_text').val().trim();
                var replaceText = $('#replace_text').val();
                
                if (!searchText) {
                    showMessage('Search text is required.', 'error');
                    return;
                }
                
                var tableName = $('input[name="search_table"]:checked').val();
                if (!confirm('Are you sure you want to replace "' + searchText + '" with "' + replaceText + '" in the ' + tableName + ' table? This cannot be undone!')) {
                    return;
                }
                
                $('#replace_btn').prop('disabled', true).text('Replacing...');
                $('#replace_results').hide();
                
                $.ajax({
                    url: scorpion_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'scorpion_replace',
                        search_text: searchText,
                        replace_text: replaceText,
                        search_table: $('input[name="search_table"]:checked').val(),
                        nonce: scorpion_ajax.nonce
                    },
                    success: function(response) {
                        $('#replace_btn').prop('disabled', false).text('Perform Replacement');
                        
                        if (response.success) {
                            $('#replace_results_content').html(response.data.html);
                            $('#replace_results').show();
                            $('#replace_section').hide();
                            $('#search_results').hide();
                            
                            showMessage(response.data.message, 'success');
                        } else {
                            showMessage(response.data || 'Replacement failed.', 'error');
                        }
                    },
                    error: function() {
                        $('#replace_btn').prop('disabled', false).text('Perform Replacement');
                        showMessage('AJAX error occurred during replacement.', 'error');
                    }
                });
            });
            
            function showMessage(message, type) {
                var alertClass = type === 'error' ? 'notice-error' : 'notice-success';
                var html = '<div class="notice ' + alertClass + ' is-dismissible"><p>' + message + '</p></div>';
                $('#scorpion-messages').html(html);
            }
        });
        </script>
        <?php
    }
    
    /**
     * Handle search AJAX request
     */
    public function handle_search() {
        // Set time limit to prevent hanging
        set_time_limit(30);
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'scorpion_search_replace_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        
        // CUSTOM sanitization that preserves special characters like backslashes
        // Do NOT use sanitize_text_field() as it strips backslashes
        $search_text = $_POST['search_text'];
        
        // Store the raw input to preserve intentional backslashes
        $raw_search_text = $search_text;
        
        // Only apply minimal sanitization to preserve user-intended special characters
        $search_text = trim($search_text);              // Remove whitespace only
        
        // Remove dangerous HTML/script tags but preserve ALL special chars including \ ' "
        $search_text = wp_strip_all_tags($search_text);
        $search_text = str_replace(['<script>', '</script>', 'javascript:', 'data:'], '', $search_text);
        
        // If the result is empty but raw input wasn't, it means we need the raw input
        if (empty($search_text) && !empty($raw_search_text)) {
            $search_text = trim($raw_search_text);
            // Still remove dangerous script content but preserve everything else
            $search_text = str_replace(['<script>', '</script>', 'javascript:', 'data:'], '', $search_text);
        }
        
        if (empty($search_text)) {
            wp_send_json_error('Search text is required');
        }
        
        // Get selected table
        $search_table = isset($_POST['search_table']) ? $_POST['search_table'] : 'wp_pylons';
        
        // Log search attempt with actual character representation
        error_log("Scorpion Search: Starting search for '" . $search_text . "' in table: " . $search_table);
        
        try {
            // Determine which table to search
            if ($search_table === 'wp_posts') {
                $this->search_wp_posts($search_text);
                return;
            }
            
            if ($search_table === 'wp_posts_slug') {
                $this->search_wp_posts_slug($search_text);
                return;
            }
            
            // Default to wp_pylons search
            $table_name = $wpdb->prefix . 'pylons';
            
            // First check if table exists
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if (!$table_exists) {
                wp_send_json_error("Table {$table_name} does not exist. Please ensure the Ruplin plugin is properly installed.");
            }
            
            $columns = $wpdb->get_results("DESCRIBE {$table_name}", ARRAY_A);
            
            if (empty($columns)) {
                wp_send_json_error("Could not retrieve table structure for {$table_name}. Database error: " . $wpdb->last_error);
            }
            
            if ($wpdb->last_error) {
                wp_send_json_error("Database error during table description: " . $wpdb->last_error);
            }
            
            // Build search conditions for all columns
            $search_conditions = array();
            $column_names = array();
            
            foreach ($columns as $column) {
                $column_name = $column['Field'];
                $column_names[] = $column_name;
                
                // CRITICAL: Custom escaping that preserves backslashes for search
                // Do NOT use $wpdb->esc_like() as it double-escapes backslashes
                $escaped_search = $search_text;
                
                // Only escape the SQL wildcards that could break LIKE queries
                $escaped_search = str_replace(['%', '_'], ['\%', '\_'], $escaped_search);
                
                // Use $wpdb->prepare() for SQL injection protection but preserve our search term
                $search_conditions[] = $wpdb->prepare("{$column_name} LIKE %s", '%' . $escaped_search . '%');
                
                // Debug log for special characters
                if (strpos($search_text, '\\') !== false) {
                    error_log("Scorpion Search: Searching column {$column_name} for backslash - escaped term: '{$escaped_search}'");
                    
                    // Test what's actually in the database
                    $test_sql = "SELECT {$column_name} FROM {$table_name} WHERE {$column_name} LIKE '%\\\\%' LIMIT 1";
                    $sample = $wpdb->get_var($test_sql);
                    if ($sample) {
                        error_log("Sample data in {$column_name}: '" . substr($sample, 0, 100) . "'");
                        // Find position of backslash and show surrounding context
                        $pos = strpos($sample, '\\');
                        if ($pos !== false) {
                            $context = substr($sample, max(0, $pos-10), 20);
                            error_log("Context around backslash: '" . $context . "' (hex: " . bin2hex($context) . ")");
                        }
                    }
                }
            }
            
            // Execute search query with error checking
            // Use pylon_id as the primary key instead of id
            $primary_key = 'pylon_id';
            $search_query = "SELECT {$primary_key}, " . implode(', ', array_diff($column_names, [$primary_key])) . " FROM {$table_name} WHERE " . implode(' OR ', $search_conditions);
            
            // Add LIMIT to prevent timeouts on large tables
            $search_query .= " LIMIT 1000";
            
            $results = $wpdb->get_results($search_query, ARRAY_A);
            
            // Check for database errors
            if ($wpdb->last_error) {
                wp_send_json_error("Database error during search: " . $wpdb->last_error . " | Query: " . $search_query);
            }
            
            if ($results === null) {
                wp_send_json_error("Search query returned null. This may indicate a database connection issue.");
            }
            
            // Count total matches
            $total_matches = count($results);
            
            // Debug logging for backslash search
            if ($search_text === '\\') {
                error_log("=== SEARCH PHASE DEBUG ===");
                error_log("Search text: '" . $search_text . "' (hex: " . bin2hex($search_text) . ")");
                error_log("Total rows found: " . $total_matches);
                error_log("Search query used: " . substr($search_query, 0, 500));
            }
            
            // Build results HTML
            $html = "<div style='padding: 15px; background: #f9f9f9; border: 1px solid #ddd;'>";
            $html .= "<strong>Total rows with matches: {$total_matches}</strong><br><br>";
            
            if ($total_matches > 0) {
                // Count matches per column (simplified to avoid timeouts)
                $column_matches = array();
                foreach ($column_names as $column_name) {
                    // Skip large text columns that might cause timeouts
                    if (in_array($column_name, ['pylon_id', 'created_at', 'updated_at'])) {
                        continue;
                    }
                    
                    // Use same custom escaping as main search
                    $escaped_search = str_replace(['%', '_'], ['\%', '\_'], $search_text);
                    
                    $count_query = $wpdb->prepare(
                        "SELECT COUNT(*) FROM {$table_name} WHERE {$column_name} LIKE %s LIMIT 100",
                        '%' . $escaped_search . '%'
                    );
                    $count = $wpdb->get_var($count_query);
                    
                    // Debug logging for backslash
                    if ($search_text === '\\') {
                        error_log("Column {$column_name} count query: " . $count_query);
                        error_log("Column {$column_name} matches: " . $count);
                        
                        // Get a sample for this column
                        if ($count > 0) {
                            $sample_query = "SELECT {$column_name} FROM {$table_name} WHERE {$column_name} LIKE '%\\\\%' LIMIT 1";
                            $sample_value = $wpdb->get_var($sample_query);
                            if ($sample_value) {
                                error_log("Sample from {$column_name}: '" . substr($sample_value, 0, 100) . "'");
                                // Check what character is actually there
                                for ($i = 0; $i < min(strlen($sample_value), 50); $i++) {
                                    if ($sample_value[$i] === '\\') {
                                        error_log("Found backslash at position {$i}, surrounding: '" . substr($sample_value, max(0, $i-2), 5) . "'");
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Check for errors after each query
                    if ($wpdb->last_error) {
                        error_log("Scorpion Search Error on column {$column_name}: " . $wpdb->last_error);
                        continue;
                    }
                    
                    if ($count > 0) {
                        $column_matches[$column_name] = $count;
                    }
                }
                
                $html .= "<strong>Matches by column:</strong><br>";
                foreach ($column_matches as $column => $count) {
                    $html .= "• {$column}: {$count} rows<br>";
                }
                
                $html .= "<br><strong>Preview (first 5 rows):</strong><br>";
                $preview_count = 0;
                foreach ($results as $row) {
                    if ($preview_count >= 5) break;
                    
                    $html .= "<div style='margin: 10px 0; padding: 10px; background: white; border: 1px solid #ccc;'>";
                    $html .= "<strong>Row ID {$row['pylon_id']}:</strong><br>";
                    
                    foreach ($row as $column => $value) {
                        if ($column === 'pylon_id') continue;
                        if (!empty($value) && strpos($value, $search_text) !== false) {
                            $highlighted = str_replace($search_text, "<mark style='background: yellow;'>{$search_text}</mark>", esc_html($value));
                            $html .= "<small><strong>{$column}:</strong> {$highlighted}</small><br>";
                        }
                    }
                    
                    $html .= "</div>";
                    $preview_count++;
                }
                
                if ($total_matches > 5) {
                    $html .= "<em>... and " . ($total_matches - 5) . " more rows</em>";
                }
            }
            
            $html .= "</div>";
            
            error_log("Scorpion Search: Search completed successfully. Found {$total_matches} rows.");
            
            wp_send_json_success(array(
                'message' => "Search completed. Found {$total_matches} rows containing '{$search_text}'",
                'total_matches' => $total_matches,
                'html' => $html
            ));
            
        } catch (Exception $e) {
            error_log("Scorpion Search: Exception occurred - " . $e->getMessage());
            wp_send_json_error('Database error: ' . $e->getMessage());
        }
    }
    
    /**
     * Search in wp_posts table (post_title and post_content only)
     */
    private function search_wp_posts($search_text) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'posts';
        
        // Custom escaping that preserves backslashes for search
        $escaped_search = str_replace(['%', '_'], ['\%', '\_'], $search_text);
        
        // Build query for post_title and post_content only
        $search_query = $wpdb->prepare(
            "SELECT ID, post_title, post_content, post_status, post_type 
             FROM {$table_name} 
             WHERE (post_title LIKE %s OR post_content LIKE %s)
             LIMIT 1000",
            '%' . $escaped_search . '%',
            '%' . $escaped_search . '%'
        );
        
        $results = $wpdb->get_results($search_query, ARRAY_A);
        
        if ($wpdb->last_error) {
            wp_send_json_error("Database error: " . $wpdb->last_error);
        }
        
        $total_matches = count($results);
        
        // Build HTML output
        $html = "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;'>";
        
        if ($total_matches === 0) {
            $html .= "<p>No matches found.</p>";
        } else {
            $html .= "<p><strong>Found {$total_matches} posts with matching text.</strong></p>";
            $preview_count = 0;
            
            foreach ($results as $result) {
                if ($preview_count >= 10) break; // Show max 10 previews
                
                $html .= "<div style='border-bottom: 1px solid #ddd; padding: 10px 0;'>";
                $html .= "<strong>Post ID:</strong> " . esc_html($result['ID']) . " | ";
                $html .= "<strong>Type:</strong> " . esc_html($result['post_type']) . " | ";
                $html .= "<strong>Status:</strong> " . esc_html($result['post_status']) . "<br>";
                
                // Show matching content with highlighting
                if (strpos($result['post_title'], $search_text) !== false) {
                    $highlighted = str_replace($search_text, "<mark style='background: yellow;'>{$search_text}</mark>", esc_html($result['post_title']));
                    $html .= "<small><strong>Title:</strong> {$highlighted}</small><br>";
                }
                
                if (strpos($result['post_content'], $search_text) !== false) {
                    // Show snippet of content around the match
                    $pos = stripos($result['post_content'], $search_text);
                    $start = max(0, $pos - 50);
                    $snippet = substr($result['post_content'], $start, 200);
                    $snippet = esc_html($snippet);
                    $highlighted = str_replace($search_text, "<mark style='background: yellow;'>{$search_text}</mark>", $snippet);
                    $html .= "<small><strong>Content:</strong> ...{$highlighted}...</small><br>";
                }
                
                $html .= "</div>";
                $preview_count++;
            }
            
            if ($total_matches > 10) {
                $html .= "<em>... and " . ($total_matches - 10) . " more posts</em>";
            }
        }
        
        $html .= "</div>";
        
        wp_send_json_success(array(
            'message' => "Search completed in wp_posts. Found {$total_matches} posts containing '{$search_text}'",
            'total_matches' => $total_matches,
            'html' => $html
        ));
    }
    
    /**
     * Search in wp_posts table (post_name field only - URL slug)
     */
    private function search_wp_posts_slug($search_text) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'posts';
        
        // Custom escaping that preserves backslashes for search
        $escaped_search = str_replace(['%', '_'], ['\%', '\_'], $search_text);
        
        // Build query for post_name only
        $search_query = $wpdb->prepare(
            "SELECT ID, post_title, post_name, post_status, post_type 
             FROM {$table_name} 
             WHERE post_name LIKE %s
             LIMIT 1000",
            '%' . $escaped_search . '%'
        );
        
        $results = $wpdb->get_results($search_query, ARRAY_A);
        
        if ($wpdb->last_error) {
            wp_send_json_error("Database error: " . $wpdb->last_error);
        }
        
        $total_matches = count($results);
        
        // Build HTML output
        $html = "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;'>";
        
        if ($total_matches === 0) {
            $html .= "<p>No matches found in post_name (URL slug) field.</p>";
        } else {
            $html .= "<p><strong>Found {$total_matches} posts with matching URL slugs.</strong></p>";
            $preview_count = 0;
            
            foreach ($results as $result) {
                if ($preview_count >= 10) break; // Show max 10 previews
                
                $html .= "<div style='border-bottom: 1px solid #ddd; padding: 10px 0;'>";
                $html .= "<strong>Post ID:</strong> " . esc_html($result['ID']) . " | ";
                $html .= "<strong>Type:</strong> " . esc_html($result['post_type']) . " | ";
                $html .= "<strong>Status:</strong> " . esc_html($result['post_status']) . "<br>";
                
                // Show post title for context
                $html .= "<small><strong>Title:</strong> " . esc_html($result['post_title']) . "</small><br>";
                
                // Show matching post_name with highlighting
                if (strpos($result['post_name'], $search_text) !== false) {
                    $highlighted = str_replace($search_text, "<mark style='background: yellow;'>{$search_text}</mark>", esc_html($result['post_name']));
                    $html .= "<small><strong>URL Slug:</strong> {$highlighted}</small><br>";
                }
                
                $html .= "</div>";
                $preview_count++;
            }
            
            if ($total_matches > 10) {
                $html .= "<em>... and " . ($total_matches - 10) . " more posts</em>";
            }
        }
        
        $html .= "</div>";
        
        wp_send_json_success(array(
            'message' => "Search completed in wp_posts post_name field. Found {$total_matches} posts containing '{$search_text}'",
            'total_matches' => $total_matches,
            'html' => $html
        ));
    }
    
    /**
     * Handle replace AJAX request
     */
    public function handle_replace() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'scorpion_search_replace_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        
        // CUSTOM sanitization that preserves special characters like backslashes
        // Same logic as search function
        $search_text = $_POST['search_text'];
        $raw_search_text = $search_text;
        $search_text = trim($search_text);
        $search_text = wp_strip_all_tags($search_text);
        $search_text = str_replace(['<script>', '</script>', 'javascript:', 'data:'], '', $search_text);
        if (empty($search_text) && !empty($raw_search_text)) {
            $search_text = trim($raw_search_text);
            $search_text = str_replace(['<script>', '</script>', 'javascript:', 'data:'], '', $search_text);
        }
        
        $replace_text = $_POST['replace_text'];
        $raw_replace_text = $replace_text;
        $replace_text = trim($replace_text);
        $replace_text = wp_strip_all_tags($replace_text);
        $replace_text = str_replace(['<script>', '</script>', 'javascript:', 'data:'], '', $replace_text);
        if (empty($replace_text) && !empty($raw_replace_text)) {
            $replace_text = trim($raw_replace_text);
            $replace_text = str_replace(['<script>', '</script>', 'javascript:', 'data:'], '', $replace_text);
        }
        
        if (empty($search_text)) {
            wp_send_json_error('Search text is required');
        }
        
        // Get selected table
        $search_table = isset($_POST['search_table']) ? $_POST['search_table'] : 'wp_pylons';
        
        // Log replace attempt
        error_log("Scorpion Replace: Replacing '{$search_text}' with '{$replace_text}' in table: " . $search_table);
        
        try {
            // Determine which table to replace in
            if ($search_table === 'wp_posts') {
                $this->replace_wp_posts($search_text, $replace_text);
                return;
            }
            
            if ($search_table === 'wp_posts_slug') {
                $this->replace_wp_posts_slug($search_text, $replace_text);
                return;
            }
            
            // Default to wp_pylons replace
            $table_name = $wpdb->prefix . 'pylons';
            $columns = $wpdb->get_results("DESCRIBE {$table_name}", ARRAY_A);
            
            if (empty($columns)) {
                wp_send_json_error('Could not retrieve table structure');
            }
            
            $total_updates = 0;
            $column_updates = array();
            
            // Perform replacement on each column
            foreach ($columns as $column) {
                $column_name = $column['Field'];
                
                // Skip primary key and timestamp columns
                if (in_array($column_name, ['pylon_id', 'created_at', 'updated_at'])) {
                    continue;
                }
                
                // Perform the replacement using custom escaping
                // Use same custom escaping as search to ensure consistency
                $escaped_search = str_replace(['%', '_'], ['\%', '\_'], $search_text);
                
                // Debug logging - write to a file we can control
                $debug_file = WP_CONTENT_DIR . '/scorpion_debug.txt';
                $debug_msg = "\n\n=== Scorpion Replace Debug " . date('Y-m-d H:i:s') . " ===\n";
                $debug_msg .= "Column: {$column_name}\n";
                $debug_msg .= "Original search_text: '" . $search_text . "' (length: " . strlen($search_text) . ")\n";
                $debug_msg .= "Original search_text bytes: " . bin2hex($search_text) . "\n";
                $debug_msg .= "Escaped search: '" . $escaped_search . "'\n";
                $debug_msg .= "Replace with: '" . $replace_text . "'\n";
                
                // Additional debugging for backslash
                if (bin2hex($search_text) === '5c5c') {
                    $debug_msg .= ">>> BACKSLASH DETECTED via hex check!\n";
                    $debug_msg .= ">>> Comparison test: search_text === '\\\\' = " . var_export($search_text === '\\', true) . "\n";
                    $debug_msg .= ">>> String contents char by char:\n";
                    for ($i = 0; $i < strlen($search_text); $i++) {
                        $debug_msg .= ">>>   Position $i: '" . $search_text[$i] . "' (hex: " . bin2hex($search_text[$i]) . ")\n";
                    }
                }
                
                file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                
                error_log("=== Scorpion Replace Debug for column: {$column_name} ===");
                error_log("Original search_text: '" . $search_text . "' (length: " . strlen($search_text) . ")");
                error_log("Original search_text bytes: " . bin2hex($search_text));
                error_log("Escaped search: '" . $escaped_search . "'");
                error_log("Replace with: '" . $replace_text . "'");
                
                // Special handling for backslash character
                // When user types \ in the form, it arrives as two literal backslash characters
                // We need to detect this and search for a single \ in the database
                if (bin2hex($search_text) === '5c5c') {
                    $debug_msg = "DETECTED: Backslash search (received as \\\\ with length 2)\n";
                    $debug_msg .= "Will search for single backslash in database\n";
                    file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                    
                    // We're looking for a SINGLE backslash in the database
                    // In SQL, a single backslash is represented as \\
                    
                    // First, check if there are matches
                    // Need 4 backslashes in the SQL string to match a single backslash in the database
                    $count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$column_name} LIKE '%\\\\\\\\%'";
                    $match_count = $wpdb->get_var($count_query);
                    $debug_msg = "Match count query: " . $count_query . "\n";
                    $debug_msg .= "Matches found: " . $match_count . "\n";
                    file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                    
                    if ($match_count > 0) {
                        // Get a sample to verify
                        $test_query = "SELECT {$column_name} FROM {$table_name} WHERE {$column_name} LIKE '%\\\\\\\\%' LIMIT 1";
                        $sample_data = $wpdb->get_var($test_query);
                        if ($sample_data) {
                            $debug_msg = "Sample data: '" . substr($sample_data, 0, 100) . "'\n";
                            // Find the backslash
                            $bs_pos = strpos($sample_data, '\\');
                            if ($bs_pos !== false) {
                                $debug_msg .= "Found backslash at position {$bs_pos}\n";
                                $context = substr($sample_data, max(0, $bs_pos - 5), 11);
                                $debug_msg .= "Context: '" . $context . "' (hex: " . bin2hex($context) . ")\n";
                            }
                            file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                        }
                        
                        // Prepare replacement
                        $safe_column = esc_sql($column_name);
                        $safe_table = esc_sql($table_name);
                        $safe_replace = esc_sql($replace_text);
                        
                        // Use CHAR(92) to represent backslash unambiguously
                        // CHAR(92) is the ASCII code for backslash
                        // The WHERE clause needs 4 backslashes to match a single backslash
                        $sql_char = "UPDATE {$safe_table} SET {$safe_column} = REPLACE({$safe_column}, CHAR(92), '{$safe_replace}') WHERE {$safe_column} LIKE '%\\\\\\\\%'";
                        $debug_msg = "Trying CHAR(92) method: " . $sql_char . "\n";
                        file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                        $result = $wpdb->query($sql_char);
                        $debug_msg = "CHAR(92) result: " . var_export($result, true) . "\n";
                        file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                        
                        if ($wpdb->last_error) {
                            $debug_msg .= "Database error: " . $wpdb->last_error . "\n";
                            file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                        }
                        
                        // Check for any errors
                        if ($wpdb->last_error) {
                            $debug_msg = "Database error: " . $wpdb->last_error . "\n";
                            file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                            error_log("Database error: " . $wpdb->last_error);
                        }
                    } else {
                        $debug_msg = "No matches found for backslash in column {$column_name}\n";
                        file_put_contents($debug_file, $debug_msg, FILE_APPEND);
                        error_log("No matches found for backslash in column {$column_name}");
                        $result = 0;
                    }
                } else {
                    // Normal replacement for non-backslash characters
                    error_log("Normal replacement for: '" . $search_text . "'");
                    $sql = $wpdb->prepare(
                        "UPDATE {$table_name} SET {$column_name} = REPLACE({$column_name}, %s, %s) WHERE {$column_name} LIKE %s",
                        $search_text,
                        $replace_text,
                        '%' . $escaped_search . '%'
                    );
                    error_log("Prepared SQL: " . $sql);
                    $result = $wpdb->query($sql);
                    error_log("Query result: " . var_export($result, true));
                    
                    if ($wpdb->last_error) {
                        error_log("Database error: " . $wpdb->last_error);
                    }
                }
                
                error_log("Final result for column {$column_name}: " . var_export($result, true));
                
                if ($result > 0) {
                    $column_updates[$column_name] = $result;
                    $total_updates += $result;
                }
            }
            
            // Build results HTML
            $html = "<div style='padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb;'>";
            $html .= "<strong>Replacement completed successfully!</strong><br><br>";
            $html .= "<strong>Total rows updated: {$total_updates}</strong><br><br>";
            
            if (!empty($column_updates)) {
                $html .= "<strong>Updates by column:</strong><br>";
                foreach ($column_updates as $column => $count) {
                    $html .= "• {$column}: {$count} rows updated<br>";
                }
            } else {
                $html .= "No rows were updated. The search text may not have been found.";
            }
            
            $html .= "<br><br><em>Replaced all instances of '{$search_text}' with '{$replace_text}'</em>";
            $html .= "</div>";
            
            wp_send_json_success(array(
                'message' => "Replacement completed! Updated {$total_updates} rows.",
                'total_updates' => $total_updates,
                'html' => $html
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Database error: ' . $e->getMessage());
        }
    }
    
    /**
     * Replace in wp_posts table (post_title and post_content only)
     */
    private function replace_wp_posts($search_text, $replace_text) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'posts';
        $total_updates = 0;
        $column_updates = array('post_title' => 0, 'post_content' => 0);
        
        // Custom escaping that preserves backslashes
        $escaped_search = str_replace(['%', '_'], ['\%', '\_'], $search_text);
        
        // Special handling for backslash replacement
        // Check using hex like we do for wp_pylons
        if (bin2hex($search_text) === '5c5c') {
            // Replace in post_title with special backslash handling
            $safe_table = esc_sql($table_name);
            $safe_replace = esc_sql($replace_text);
            
            // Use CHAR(92) for the replacement and 4 backslashes in the WHERE clause
            $sql_title = "UPDATE {$safe_table} SET post_title = REPLACE(post_title, CHAR(92), '{$safe_replace}') WHERE post_title LIKE '%\\\\\\\\%'";
            $title_result = $wpdb->query($sql_title);
            
            if ($title_result !== false) {
                $column_updates['post_title'] = $title_result;
                $total_updates += $title_result;
            }
            
            // Replace in post_content with special backslash handling
            $sql_content = "UPDATE {$safe_table} SET post_content = REPLACE(post_content, CHAR(92), '{$safe_replace}') WHERE post_content LIKE '%\\\\\\\\%'";
            $content_result = $wpdb->query($sql_content);
            
            if ($content_result !== false) {
                $column_updates['post_content'] = $content_result;
                $total_updates += $content_result;
            }
        } else {
            // Normal replacement for non-backslash characters
            // Replace in post_title
            $title_result = $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name} SET post_title = REPLACE(post_title, %s, %s) WHERE post_title LIKE %s",
                $search_text,
                $replace_text,
                '%' . $escaped_search . '%'
            ));
            
            if ($title_result !== false) {
                $column_updates['post_title'] = $title_result;
                $total_updates += $title_result;
            }
            
            // Replace in post_content
            $content_result = $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s",
                $search_text,
                $replace_text,
                '%' . $escaped_search . '%'
            ));
            
            if ($content_result !== false) {
                $column_updates['post_content'] = $content_result;
                $total_updates += $content_result;
            }
        }
        
        // Build result HTML
        $html = "<div style='padding: 10px; background: #f9f9f9; border: 1px solid #ddd;'>";
        $html .= "<strong>Replacement Summary for wp_posts:</strong><br>";
        $html .= "<ul style='margin: 10px 0;'>";
        $html .= "<li>post_title: " . $column_updates['post_title'] . " rows updated</li>";
        $html .= "<li>post_content: " . $column_updates['post_content'] . " rows updated</li>";
        $html .= "</ul>";
        $html .= "<strong>Total rows affected:</strong> " . $total_updates;
        $html .= "</div>";
        
        error_log("Scorpion Replace: wp_posts replacement completed. Total updates: {$total_updates}");
        
        wp_send_json_success(array(
            'message' => "Replacement completed in wp_posts! Updated {$total_updates} rows.",
            'total_updates' => $total_updates,
            'html' => $html
        ));
    }
    
    /**
     * Replace in wp_posts table (post_name field only - URL slug)
     */
    private function replace_wp_posts_slug($search_text, $replace_text) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'posts';
        $total_updates = 0;
        
        // Custom escaping that preserves backslashes
        $escaped_search = str_replace(['%', '_'], ['\%', '\_'], $search_text);
        
        // Log replacement attempt
        error_log("Scorpion Replace: Replacing '{$search_text}' with '{$replace_text}' in wp_posts post_name field");
        
        // Replace in post_name field only
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$table_name} SET post_name = REPLACE(post_name, %s, %s) WHERE post_name LIKE %s",
            $search_text,
            $replace_text,
            '%' . $escaped_search . '%'
        ));
        
        if ($result !== false) {
            $total_updates = $result;
        }
        
        // Check for database errors
        if ($wpdb->last_error) {
            wp_send_json_error("Database error: " . $wpdb->last_error);
        }
        
        // Build result HTML
        $html = "<div style='padding: 10px; background: #f9f9f9; border: 1px solid #ddd;'>";
        $html .= "<strong>Replacement Summary for wp_posts post_name (URL slug):</strong><br>";
        $html .= "<ul style='margin: 10px 0;'>";
        $html .= "<li>post_name: " . $total_updates . " rows updated</li>";
        $html .= "</ul>";
        $html .= "<strong>Total rows affected:</strong> " . $total_updates;
        
        if ($total_updates > 0) {
            $html .= "<br><br><em style='color: #d63638;'><strong>Important:</strong> URL slug changes may affect SEO and existing bookmarks. You may want to set up redirects.</em>";
        }
        
        $html .= "</div>";
        
        error_log("Scorpion Replace: wp_posts post_name replacement completed. Total updates: {$total_updates}");
        
        wp_send_json_success(array(
            'message' => "Replacement completed in wp_posts post_name field! Updated {$total_updates} rows.",
            'total_updates' => $total_updates,
            'html' => $html
        ));
    }
    
    /**
     * Suppress admin notices on Scorpion Search Replace page
     */
    public function suppress_admin_notices_on_scorpion_page() {
        $screen = get_current_screen();
        
        if (!$screen) {
            return;
        }
        
        // Check if we're on the Scorpion Search Replace page
        if ($screen->id === 'pylon_page_scorpion_search_replace_mar' || 
            (isset($_GET['page']) && $_GET['page'] === 'scorpion_search_replace_mar')) {
            
            // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices'); 
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Add empty handlers to prevent notices
            add_action('admin_notices', '__return_false', 999);
            add_action('all_admin_notices', '__return_false', 999);
            add_action('network_admin_notices', '__return_false', 999);
            add_action('user_admin_notices', '__return_false', 999);
            
            // CSS suppression as final backup
            add_action('admin_head', array($this, 'add_notice_suppression_css'), 1);
        }
    }
    
    /**
     * Early notice suppression for Scorpion page
     */
    public function early_notice_suppression_scorpion() {
        // Check if we're on Scorpion Search Replace page
        if (isset($_GET['page']) && $_GET['page'] === 'scorpion_search_replace_mar') {
            
            // Immediate notice suppression
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            remove_all_actions('user_admin_notices');
            
            // Add our suppression hooks very early
            add_action('admin_notices', '__return_false', 1);
            add_action('all_admin_notices', '__return_false', 1);
            add_action('network_admin_notices', '__return_false', 1);
            add_action('user_admin_notices', '__return_false', 1);
            
            // CSS suppression
            add_action('admin_head', array($this, 'add_notice_suppression_css'), 1);
        }
    }
    
    /**
     * Add CSS to hide any remaining notices
     */
    public function add_notice_suppression_css() {
        echo '<style type="text/css">
            /* AGGRESSIVE NOTICE SUPPRESSION - Hide all WordPress admin notices */
            .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
            .updated, .error, .update-nag, .admin-notice, .settings-error,
            div.notice, div.updated, div.error, div.update-nag, div.settings-error,
            .wrap > .notice, .wrap > .updated, .wrap > .error, .wrap > .settings-error,
            #adminmenu + .notice, #adminmenu + .updated, #adminmenu + .error,
            .update-php, .php-update-nag, .update-core-php,
            .plugin-update-tr, .theme-update-message, .update-message,
            .updating-message, #update-nag, #deprecation-warning,
            .notice-alt, .activated, .deactivated, .plugin-deleting,
            .inline.notice, .inline.updated, .inline.error,
            .wp-header-end + .notice, .wp-header-end + .updated, .wp-header-end + .error,
            .form-table .notice, .form-table .updated, .form-table .error,
            .postbox .notice, .postbox .updated, .postbox .error,
            #poststuff .notice, #poststuff .updated, #poststuff .error,
            .metabox-holder .notice, .metabox-holder .updated, .metabox-holder .error,
            .media-upload-form .notice, .media-upload-form .updated, .media-upload-form .error {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            
            /* Hide any dynamically added notices */
            [class*="notice"], [class*="updated"], [class*="error"] {
                display: none !important;
            }
            
            /* Ensure our Scorpion messages are still visible */
            #scorpion-messages .notice {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                height: auto !important;
                margin: 5px 0 15px !important;
                padding: 1px 12px !important;
                overflow: visible !important;
            }
        </style>';
    }
}

// Initialize the class
new Scorpion_Search_Replace();