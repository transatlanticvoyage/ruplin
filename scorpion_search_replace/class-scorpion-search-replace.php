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
        if ($hook !== 'pylon_page_scorpion_search_replace_mar') {
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
            <h1>Scorpion Search & Replace - wp_pylons Table</h1>
            <p>Search and replace text across all columns in the wp_pylons database table.</p>
            
            <div id="scorpion-messages" style="margin: 20px 0;"></div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin: 20px 0;">
                <h2>Step 1: Search</h2>
                <p>Enter text to search for in the wp_pylons table:</p>
                
                <div style="margin: 15px 0;">
                    <label for="search_text" style="display: block; font-weight: bold; margin-bottom: 5px;">Search Text:</label>
                    <input type="text" id="search_text" style="width: 100%; max-width: 500px; padding: 8px;" placeholder="Enter text to search for...">
                </div>
                
                <button type="button" id="search_btn" class="button button-primary" style="margin-top: 10px;">
                    Search wp_pylons Table
                </button>
                
                <div id="search_results" style="margin-top: 20px; display: none;">
                    <h3>Search Results</h3>
                    <div id="search_results_content"></div>
                </div>
            </div>
            
            <div id="replace_section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin: 20px 0; display: none;">
                <h2>Step 2: Replace</h2>
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
                
                $.ajax({
                    url: scorpion_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'scorpion_search',
                        search_text: searchText,
                        nonce: scorpion_ajax.nonce
                    },
                    success: function(response) {
                        $('#search_btn').prop('disabled', false).text('Search wp_pylons Table');
                        
                        if (response.success) {
                            $('#search_results_content').html(response.data.html);
                            $('#search_results').show();
                            
                            if (response.data.total_matches > 0) {
                                $('#replace_section').show();
                            }
                            
                            showMessage(response.data.message, 'success');
                        } else {
                            showMessage(response.data || 'Search failed.', 'error');
                        }
                    },
                    error: function() {
                        $('#search_btn').prop('disabled', false).text('Search wp_pylons Table');
                        showMessage('AJAX error occurred during search.', 'error');
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
                
                if (!confirm('Are you sure you want to replace "' + searchText + '" with "' + replaceText + '" in the wp_pylons table? This cannot be undone!')) {
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
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'scorpion_search_replace_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $search_text = sanitize_text_field($_POST['search_text']);
        
        if (empty($search_text)) {
            wp_send_json_error('Search text is required');
        }
        
        try {
            // Get table structure to build dynamic search query
            $table_name = $wpdb->prefix . 'pylons';
            $columns = $wpdb->get_results("DESCRIBE {$table_name}", ARRAY_A);
            
            if (empty($columns)) {
                wp_send_json_error('Could not retrieve table structure');
            }
            
            // Build search conditions for all columns
            $search_conditions = array();
            $column_names = array();
            
            foreach ($columns as $column) {
                $column_name = $column['Field'];
                $column_names[] = $column_name;
                $search_conditions[] = $wpdb->prepare("{$column_name} LIKE %s", '%' . $wpdb->esc_like($search_text) . '%');
            }
            
            // Execute search query
            $search_query = "SELECT id, " . implode(', ', $column_names) . " FROM {$table_name} WHERE " . implode(' OR ', $search_conditions);
            $results = $wpdb->get_results($search_query, ARRAY_A);
            
            // Count total matches
            $total_matches = count($results);
            
            // Build results HTML
            $html = "<div style='padding: 15px; background: #f9f9f9; border: 1px solid #ddd;'>";
            $html .= "<strong>Total rows with matches: {$total_matches}</strong><br><br>";
            
            if ($total_matches > 0) {
                // Count matches per column
                $column_matches = array();
                foreach ($column_names as $column_name) {
                    $count_query = $wpdb->prepare(
                        "SELECT COUNT(*) FROM {$table_name} WHERE {$column_name} LIKE %s",
                        '%' . $wpdb->esc_like($search_text) . '%'
                    );
                    $count = $wpdb->get_var($count_query);
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
                    $html .= "<strong>Row ID {$row['id']}:</strong><br>";
                    
                    foreach ($row as $column => $value) {
                        if ($column === 'id') continue;
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
            
            wp_send_json_success(array(
                'message' => "Search completed. Found {$total_matches} rows containing '{$search_text}'",
                'total_matches' => $total_matches,
                'html' => $html
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Database error: ' . $e->getMessage());
        }
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
        $search_text = sanitize_text_field($_POST['search_text']);
        $replace_text = sanitize_text_field($_POST['replace_text']);
        
        if (empty($search_text)) {
            wp_send_json_error('Search text is required');
        }
        
        try {
            // Get table structure
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
                
                // Skip ID and timestamp columns
                if (in_array($column_name, ['id', 'created_at', 'updated_at'])) {
                    continue;
                }
                
                // Perform the replacement
                $result = $wpdb->query($wpdb->prepare(
                    "UPDATE {$table_name} SET {$column_name} = REPLACE({$column_name}, %s, %s) WHERE {$column_name} LIKE %s",
                    $search_text,
                    $replace_text,
                    '%' . $wpdb->esc_like($search_text) . '%'
                ));
                
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
}

// Initialize the class
new Scorpion_Search_Replace();