<?php

if (!defined('ABSPATH')) {
    exit;
}

class Ai1wm_Backup_Renamer {

    public function __construct() {
        add_action('ai1wm_backups_left_end', array($this, 'render_rename_ui'));
        add_action('wp_ajax_ruplin_rename_backup', array($this, 'handle_rename'));
        add_action('wp_ajax_ruplin_save_custom_naming_option', array($this, 'handle_save_custom_naming_option'));

        // Hook into AI1WM export to override filename if custom naming is enabled
        if (get_option('ruplin_enable_custom_backup_naming_for_ai1wm', 'FALSE') === 'TRUE') {
            add_filter('ai1wm_export', array($this, 'override_backup_filename'), 6);
        }
    }

    /**
     * Override AI1WM backup filename with custom naming convention.
     * Format: {prefix}_{siteurl}_for_{sitespren_base}.wpress
     * Prefix: 3-digit starts at 113, 4-digit starts at 2001. Default is 3-digit.
     * If numbered files already exist, uses highest existing + 1.
     */
    public function override_backup_filename($params) {
        $backups_path = defined('AI1WM_BACKUPS_PATH') ? AI1WM_BACKUPS_PATH : WP_CONTENT_DIR . '/ai1wm-backups';
        $prefix_length = get_option('ruplin_custom_backup_naming_prefix_length', '3');
        $default_start = ($prefix_length === '4') ? 2000 : 112; // so first will be 2001 or 113

        // Determine next prefix number
        $highest = $default_start;
        if (is_dir($backups_path)) {
            foreach (new DirectoryIterator($backups_path) as $item) {
                if ($item->isDot() || $item->isDir()) {
                    continue;
                }
                $filename = $item->getFilename();
                // Match 3 or 4+ digit prefix followed by underscore
                if (preg_match('/^(\d{3,})_/', $filename, $matches)) {
                    $num = intval($matches[1]);
                    if ($num > $highest) {
                        $highest = $num;
                    }
                }
            }
        }
        $prefix = $highest + 1;

        // Get siteurl, strip protocol and sanitize
        $siteurl_raw = get_option('siteurl', '');
        $siteurl_clean = preg_replace('#^https?://#', '', $siteurl_raw);
        $siteurl_clean = preg_replace('/[^A-Za-z0-9.\-]/', '_', $siteurl_clean);
        $siteurl_clean = strtolower($siteurl_clean);

        // Get sitespren_base
        global $wpdb;
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM $sitespren_table LIMIT 1");
        $sitespren_clean = preg_replace('/[^A-Za-z0-9.\-]/', '_', $sitespren_base ?: 'unknown');
        $sitespren_clean = strtolower($sitespren_clean);

        $params['archive'] = sprintf('%s_%s_for_%s.wpress', $prefix, $siteurl_clean, $sitespren_clean);

        return $params;
    }

    public function render_rename_ui() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $backups_path = defined('AI1WM_BACKUPS_PATH') ? AI1WM_BACKUPS_PATH : WP_CONTENT_DIR . '/ai1wm-backups';
        if (!is_dir($backups_path)) {
            return;
        }

        $files = array();
        foreach (new DirectoryIterator($backups_path) as $item) {
            if ($item->isDot() || $item->isDir()) {
                continue;
            }
            if (strtolower($item->getExtension()) !== 'wpress') {
                continue;
            }
            $files[] = array(
                'name' => $item->getFilename(),
                'mtime' => $item->getMTime(),
            );
        }

        usort($files, function($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });

        // Fetch sitespren_base value
        global $wpdb;
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        $sitespren_base_value = $wpdb->get_var("SELECT sitespren_base FROM $sitespren_table LIMIT 1");

        $nonce = wp_create_nonce('ruplin_rename_backup_nonce');
        $saved_naming_source = get_option('ruplin_naming_source_for_ai1wm', 'sitespren_base');
        ?>
        <div id="ruplin-backup-renamer" style="margin-top:20px; padding:15px; background:#fff; border:1px solid #ccd0d4; border-radius:4px;">

            <div style="margin-bottom:12px; text-align:right;">
                <button type="button" id="ruplin-save-naming-settings" class="button button-primary">Save Settings</button>
                <span id="ruplin-save-status" style="margin-right:8px; font-size:13px; color:#46b450;"></span>
            </div>

            <div style="margin-bottom:15px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span style="font-size:13px;">wp_options item: <b>ruplin_enable_custom_backup_naming_for_ai1wm</b></span>
                <input type="text"
                       id="ruplin-custom-naming-toggle"
                       value="<?php echo esc_attr(get_option('ruplin_enable_custom_backup_naming_for_ai1wm', 'FALSE')); ?>"
                       style="width:80px; font-size:13px; text-align:center;"
                       readonly />
                <span class="ruplin-pill-btn" data-value="TRUE" style="display:inline-block; padding:4px 14px; font-size:13px; font-weight:600; border-radius:999px; cursor:pointer; border:1px solid #0073aa; color:#0073aa; background:#fff; transition:all 0.15s; user-select:none;">TRUE</span>
                <span class="ruplin-pill-btn" data-value="FALSE" style="display:inline-block; padding:4px 14px; font-size:13px; font-weight:600; border-radius:999px; cursor:pointer; border:1px solid #0073aa; color:#0073aa; background:#fff; transition:all 0.15s; user-select:none;">FALSE</span>
            </div>

            <div id="ruplin-naming-source" style="margin-bottom:15px; display:flex; gap:15px; flex-wrap:wrap;">
                <label class="ruplin-naming-option" style="display:block; padding:10px; border:1px solid #ccd0d4; border-radius:4px; cursor:pointer; transition:background 0.15s;">
                    <p style="margin:0 0 5px 0; font-size:13px;">wp_zen_sitespren.<b>sitespren_base</b></p>
                    <input type="text"
                           id="ruplin-sitespren-base"
                           value="<?php echo esc_attr($sitespren_base_value); ?>"
                           style="width:280px; font-size:13px;" />
                    <div style="margin-top:8px; text-align:center;">
                        <input type="radio" name="ruplin_naming_source" value="sitespren_base" style="width:20px; height:20px; cursor:pointer;" <?php checked($saved_naming_source, 'sitespren_base'); ?> />
                    </div>
                </label>
                <label class="ruplin-naming-option" style="display:block; padding:10px; border:1px solid #ccd0d4; border-radius:4px; cursor:pointer; transition:background 0.15s;">
                    <p style="margin:0 0 5px 0; font-size:13px;">wp_options.<b>siteurl</b></p>
                    <input type="text"
                           value="<?php echo esc_attr(get_option('siteurl')); ?>"
                           style="width:280px; font-size:13px;"
                           readonly />
                    <div style="margin-top:8px; text-align:center;">
                        <input type="radio" name="ruplin_naming_source" value="siteurl" style="width:20px; height:20px; cursor:pointer;" <?php checked($saved_naming_source, 'siteurl'); ?> />
                    </div>
                </label>
                <label class="ruplin-naming-option" style="display:block; padding:10px; border:1px solid #ccd0d4; border-radius:4px; cursor:pointer; transition:background 0.15s;">
                    <p style="margin:0 0 5px 0; font-size:13px;">wp_options.<b>home</b></p>
                    <input type="text"
                           value="<?php echo esc_attr(get_option('home')); ?>"
                           style="width:280px; font-size:13px;"
                           readonly />
                    <div style="margin-top:8px; text-align:center;">
                        <input type="radio" name="ruplin_naming_source" value="home" style="width:20px; height:20px; cursor:pointer;" <?php checked($saved_naming_source, 'home'); ?> />
                    </div>
                </label>
            </div>

            <?php $saved_prefix_length = get_option('ruplin_custom_backup_naming_prefix_length', '3'); ?>
            <div style="margin-bottom:15px;">
                <p style="margin:0 0 8px 0; font-size:13px;">wp_options item: <b>ruplin_custom_backup_naming_prefix_length</b></p>
                <p style="margin:0 0 8px 0; font-size:13px; font-weight:600;">Qty of numbers in prefix</p>
                <div style="display:flex; gap:15px;">
                    <label class="ruplin-prefix-option" style="display:block; padding:10px 20px; border:1px solid #ccd0d4; border-radius:4px; cursor:pointer; transition:background 0.15s;">
                        <input type="radio" name="ruplin_prefix_length" value="3" style="width:20px; height:20px; cursor:pointer; vertical-align:middle;" <?php checked($saved_prefix_length, '3'); ?> />
                        <span style="font-size:13px; vertical-align:middle;">3 digit (start with 113)</span>
                    </label>
                    <label class="ruplin-prefix-option" style="display:block; padding:10px 20px; border:1px solid #ccd0d4; border-radius:4px; cursor:pointer; transition:background 0.15s;">
                        <input type="radio" name="ruplin_prefix_length" value="4" style="width:20px; height:20px; cursor:pointer; vertical-align:middle;" <?php checked($saved_prefix_length, '4'); ?> />
                        <span style="font-size:13px; vertical-align:middle;">4 digit (start with 2001)</span>
                    </label>
                </div>
            </div>

            <h3 style="margin-top:0;">Rename Backups <span style="font-size:12px; font-weight:normal; color:#666;">(via Ruplin)</span></h3>
            <?php if (empty($files)) : ?>
                <p>No .wpress backup files found.</p>
            <?php else : ?>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:6px; border-bottom:1px solid #ddd;">Current Filename</th>
                            <th style="text-align:left; padding:6px; border-bottom:1px solid #ddd;">New Filename</th>
                            <th style="padding:6px; border-bottom:1px solid #ddd; width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file) : ?>
                        <tr class="ruplin-rename-row">
                            <td style="padding:6px; border-bottom:1px solid #eee; word-break:break-all; font-size:13px;">
                                <?php echo esc_html($file['name']); ?>
                            </td>
                            <td style="padding:6px; border-bottom:1px solid #eee;">
                                <input type="text"
                                       class="ruplin-new-filename"
                                       data-original="<?php echo esc_attr($file['name']); ?>"
                                       value="<?php echo esc_attr(str_replace('.wpress', '', $file['name'])); ?>"
                                       style="width:100%; font-size:13px;" />
                                <span style="font-size:12px; color:#666;">.wpress</span>
                            </td>
                            <td style="padding:6px; border-bottom:1px solid #eee; text-align:center;">
                                <button type="button" class="button ruplin-rename-btn">Rename</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <div id="ruplin-rename-status" style="margin-top:10px;"></div>
        </div>

        <script>
        (function($) {
            // Pill buttons
            function updatePillStyles() {
                var currentVal = $('#ruplin-custom-naming-toggle').val();
                $('.ruplin-pill-btn').each(function() {
                    var $pill = $(this);
                    if ($pill.data('value') === currentVal) {
                        $pill.css({background: '#0073aa', color: '#fff'});
                    } else {
                        $pill.css({background: '#fff', color: '#0073aa'});
                    }
                });
            }
            updatePillStyles();

            $('.ruplin-pill-btn').on('click', function() {
                $('#ruplin-custom-naming-toggle').val($(this).data('value'));
                updatePillStyles();
            });

            // Prefix length highlight
            function updatePrefixHighlight() {
                $('.ruplin-prefix-option').each(function() {
                    var $label = $(this);
                    var isChecked = $label.find('input[type="radio"]').is(':checked');
                    $label.css('background', isChecked ? '#e7f3fe' : 'transparent');
                });
            }
            updatePrefixHighlight();
            $('input[name="ruplin_prefix_length"]').on('change', updatePrefixHighlight);

            // Save settings
            $('#ruplin-save-naming-settings').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Saving...');
                $.post(ajaxurl, {
                    action: 'ruplin_save_custom_naming_option',
                    nonce: '<?php echo esc_js($nonce); ?>',
                    enable_custom_naming: $('#ruplin-custom-naming-toggle').val(),
                    naming_source: $('input[name="ruplin_naming_source"]:checked').val(),
                    prefix_length: $('input[name="ruplin_prefix_length"]:checked').val(),
                    sitespren_base: $('#ruplin-sitespren-base').val()
                }, function(response) {
                    if (response.success) {
                        $('#ruplin-save-status').text('Saved!').show();
                        setTimeout(function() { $('#ruplin-save-status').fadeOut(); }, 2000);
                    } else {
                        alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                    }
                    $btn.prop('disabled', false).text('Save Settings');
                }).fail(function() {
                    alert('AJAX request failed.');
                    $btn.prop('disabled', false).text('Save Settings');
                });
            });

            function updateNamingHighlight() {
                $('#ruplin-naming-source .ruplin-naming-option').each(function() {
                    var $label = $(this);
                    var isChecked = $label.find('input[type="radio"]').is(':checked');
                    $label.css('background', isChecked ? '#e7f3fe' : 'transparent');
                });
            }
            updateNamingHighlight();
            $('#ruplin-naming-source').on('change', 'input[type="radio"]', updateNamingHighlight);

            $('#ruplin-backup-renamer').on('click', '.ruplin-rename-btn', function() {
                var $btn = $(this);
                var $row = $btn.closest('.ruplin-rename-row');
                var $input = $row.find('.ruplin-new-filename');
                var originalName = $input.data('original');
                var newBase = $.trim($input.val());

                if (!newBase) {
                    alert('Filename cannot be empty.');
                    return;
                }

                var newName = newBase + '.wpress';
                if (newName === originalName) {
                    return;
                }

                if (/[<>:"|?*\\]/.test(newBase)) {
                    alert('Filename contains invalid characters: < > : " | ? * \\');
                    return;
                }

                $btn.prop('disabled', true).text('Renaming...');

                $.post(ajaxurl, {
                    action: 'ruplin_rename_backup',
                    nonce: '<?php echo esc_js($nonce); ?>',
                    old_name: originalName,
                    new_name: newName
                }, function(response) {
                    if (response.success) {
                        $row.find('td:first').text(newName);
                        $input.data('original', newName);
                        $input.val(newBase);
                        $btn.text('Done!');
                        setTimeout(function() { $btn.prop('disabled', false).text('Rename'); }, 1500);
                    } else {
                        alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                        $btn.prop('disabled', false).text('Rename');
                    }
                }).fail(function() {
                    alert('AJAX request failed.');
                    $btn.prop('disabled', false).text('Rename');
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    public function handle_rename() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        check_ajax_referer('ruplin_rename_backup_nonce', 'nonce');

        $old_name = isset($_POST['old_name']) ? sanitize_file_name($_POST['old_name']) : '';
        $new_name = isset($_POST['new_name']) ? sanitize_file_name($_POST['new_name']) : '';

        if (empty($old_name) || empty($new_name)) {
            wp_send_json_error(array('message' => 'Missing filename'));
        }

        if (strtolower(pathinfo($new_name, PATHINFO_EXTENSION)) !== 'wpress') {
            wp_send_json_error(array('message' => 'Filename must end with .wpress'));
        }

        $backups_path = defined('AI1WM_BACKUPS_PATH') ? AI1WM_BACKUPS_PATH : WP_CONTENT_DIR . '/ai1wm-backups';
        $old_path = $backups_path . DIRECTORY_SEPARATOR . $old_name;
        $new_path = $backups_path . DIRECTORY_SEPARATOR . $new_name;

        if (!file_exists($old_path)) {
            wp_send_json_error(array('message' => 'Original file not found'));
        }

        if (file_exists($new_path)) {
            wp_send_json_error(array('message' => 'A file with that name already exists'));
        }

        // Ensure paths stay within the backups directory
        if (realpath(dirname($old_path)) !== realpath($backups_path)) {
            wp_send_json_error(array('message' => 'Invalid file path'));
        }

        if (!rename($old_path, $new_path)) {
            wp_send_json_error(array('message' => 'Failed to rename file on disk'));
        }

        // Migrate AI1WM label if one exists
        $labels = get_option('ai1wm_backups_labels', array());
        if (is_array($labels) && isset($labels[$old_name])) {
            $labels[$new_name] = $labels[$old_name];
            unset($labels[$old_name]);
            update_option('ai1wm_backups_labels', $labels);
        }

        wp_send_json_success(array('message' => 'Backup renamed successfully'));
    }

    public function handle_save_custom_naming_option() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        check_ajax_referer('ruplin_rename_backup_nonce', 'nonce');

        $enable = isset($_POST['enable_custom_naming']) ? sanitize_text_field($_POST['enable_custom_naming']) : 'FALSE';
        if (!in_array($enable, array('TRUE', 'FALSE'), true)) {
            $enable = 'FALSE';
        }
        update_option('ruplin_enable_custom_backup_naming_for_ai1wm', $enable);

        $source = isset($_POST['naming_source']) ? sanitize_text_field($_POST['naming_source']) : 'sitespren_base';
        if (!in_array($source, array('sitespren_base', 'siteurl', 'home'), true)) {
            $source = 'sitespren_base';
        }
        update_option('ruplin_naming_source_for_ai1wm', $source);

        $prefix_length = isset($_POST['prefix_length']) ? sanitize_text_field($_POST['prefix_length']) : '3';
        if (!in_array($prefix_length, array('3', '4'), true)) {
            $prefix_length = '3';
        }
        update_option('ruplin_custom_backup_naming_prefix_length', $prefix_length);

        // Update sitespren_base in DB if provided
        if (isset($_POST['sitespren_base'])) {
            global $wpdb;
            $sitespren_table = $wpdb->prefix . 'zen_sitespren';
            $new_sitespren_base = sanitize_text_field($_POST['sitespren_base']);
            $row_id = $wpdb->get_var("SELECT id FROM $sitespren_table LIMIT 1");
            if ($row_id) {
                $wpdb->update($sitespren_table, array('sitespren_base' => $new_sitespren_base), array('id' => $row_id));
            }
        }

        wp_send_json_success(array('message' => 'Settings saved'));
    }
}

new Ai1wm_Backup_Renamer();
