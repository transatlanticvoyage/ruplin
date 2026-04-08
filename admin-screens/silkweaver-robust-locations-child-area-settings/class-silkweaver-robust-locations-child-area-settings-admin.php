<?php
/**
 * Silkweaver Robust Locations Child Area Settings Admin Page
 *
 * URL: /wp-admin/admin.php?page=silkweaver_robust_locations_child_area_settings
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Silkweaver_Robust_Locations_Child_Area_Settings_Admin {

    private static $instance = null;

    /**
     * All option keys managed by this page, with their defaults and adjunct type.
     * adjunct: 'color' | 'text_hint' | 'textarea' | 'image_picker'
     */
    private static $fields = array(
        array(
            'key'     => 'silkweaver_robust_locations_child_area_custom_html_snippet_1_activate',
            'default' => 'no',
            'adjunct' => 'text_hint',
            'hint'    => 'yes | no',
        ),
        array(
            'key'     => 'silkweaver_robust_locations_child_area_custom_html_snippet_1_content',
            'default' => '',
            'adjunct' => 'textarea',
        ),
        array(
            'key'     => 'silkweaver_robust_locations_child_area_main_featured_image_id',
            'default' => '',
            'adjunct' => 'image_picker',
        ),
    );

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 23);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_2_mar',
            'Silkweaver Robust Locations Child Area Settings',
            'silkweaver robust locations child area settings',
            'manage_options',
            'silkweaver_robust_locations_child_area_settings',
            array($this, 'render_admin_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings') {
            return;
        }
        wp_enqueue_media(); // required for WP native media popup
    }

    public function render_admin_page() {
        $this->suppress_admin_notices();

        // Handle save
        $saved = false;
        if (isset($_POST['srlca_save']) && check_admin_referer('srlca_save_settings', 'srlca_nonce')) {
            foreach (self::$fields as $field) {
                $key = $field['key'];
                $raw = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
                if ($field['adjunct'] === 'textarea') {
                    $val = wp_kses_post($raw);
                } elseif ($field['adjunct'] === 'image_picker') {
                    $val = absint($raw); // store as integer ID; 0 means unset
                    $val = $val > 0 ? (string) $val : '';
                } else {
                    $val = sanitize_text_field($raw);
                }
                update_option($key, $val);
            }
            $saved = true;
        }

        // Load current values
        $values = array();
        foreach (self::$fields as $field) {
            $key = $field['key'];
            $values[$key] = get_option($key, '');
        }

        $save_button = '<input type="submit" name="srlca_save" class="button button-primary" value="Save Changes">';
        ?>
        <div class="wrap srlca-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($saved): ?>
            <div style="background:#46b450;color:#fff;padding:10px 16px;border-radius:4px;margin:16px 0;display:inline-block;">
                Settings saved.
            </div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('srlca_save_settings', 'srlca_nonce'); ?>

                <!-- Save button — top -->
                <div style="margin:16px 0;">
                    <?php echo $save_button; ?>
                </div>

                <table class="widefat srlca-table" style="border-collapse:collapse;width:100%;">
                    <thead>
                        <tr>
                            <th style="font-weight:700;text-transform:lowercase;padding:10px 14px;background:#f1f1f1;border:1px solid #ccd0d4;width:45%;">field-name</th>
                            <th style="font-weight:700;text-transform:lowercase;padding:10px 14px;background:#f1f1f1;border:1px solid #ccd0d4;width:30%;">datum-house</th>
                            <th style="font-weight:700;text-transform:lowercase;padding:10px 14px;background:#f1f1f1;border:1px solid #ccd0d4;width:25%;">adjunct 51</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (self::$fields as $i => $field):
                            $key     = $field['key'];
                            $adjunct = $field['adjunct'];
                            $current = $values[$key];
                            $bg      = ($i % 2 === 0) ? '#fff' : '#f9f9f9';
                        ?>
                        <tr style="background:<?php echo $bg; ?>;">
                            <td style="padding:10px 14px;border:1px solid #ccd0d4;font-weight:700;font-size:12px;word-break:break-all;">
                                <?php echo esc_html($key); ?>
                            </td>
                            <td style="padding:10px 14px;border:1px solid #ccd0d4;">
                                <?php if ($adjunct === 'textarea'): ?>
                                    <textarea
                                        name="<?php echo esc_attr($key); ?>"
                                        id="srlca_<?php echo esc_attr($key . '_' . $i); ?>"
                                        class="srlca-textarea-input"
                                        rows="6"
                                        style="width:100%;font-family:monospace;font-size:12px;resize:vertical;"
                                    ><?php echo esc_textarea($current); ?></textarea>
                                <?php elseif ($adjunct === 'image_picker'):
                                    $input_id    = 'srlca_' . esc_attr($key . '_' . $i);
                                    $preview_id  = 'srlca_preview_' . esc_attr($key . '_' . $i);
                                    $btn_id      = 'srlca_btn_' . esc_attr($key . '_' . $i);
                                    $preview_url = '';
                                    if (!empty($current)) {
                                        $src = wp_get_attachment_image_src(intval($current), 'medium');
                                        if ($src) {
                                            $preview_url = $src[0];
                                        }
                                    }
                                ?>
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                        <input
                                            type="text"
                                            name="<?php echo esc_attr($key); ?>"
                                            id="<?php echo $input_id; ?>"
                                            class="srlca-image-id-input"
                                            value="<?php echo esc_attr($current); ?>"
                                            placeholder="image id"
                                            style="width:90px;font-family:monospace;"
                                        >
                                        <button
                                            type="button"
                                            id="<?php echo $btn_id; ?>"
                                            class="button srlca-select-image-btn"
                                            data-input-id="<?php echo $input_id; ?>"
                                            data-preview-id="<?php echo $preview_id; ?>"
                                        >Select Image</button>
                                    </div>
                                    <img
                                        id="<?php echo $preview_id; ?>"
                                        src="<?php echo esc_url($preview_url); ?>"
                                        style="display:<?php echo $preview_url ? 'block' : 'none'; ?>;max-height:100px;width:auto;border:1px solid #ccd0d4;border-radius:3px;"
                                        alt=""
                                    >
                                <?php else: ?>
                                    <input
                                        type="text"
                                        name="<?php echo esc_attr($key); ?>"
                                        id="srlca_<?php echo esc_attr($key . '_' . $i); ?>"
                                        class="srlca-text-input"
                                        value="<?php echo esc_attr($current); ?>"
                                        placeholder="<?php echo esc_attr($field['default']); ?>"
                                        style="width:100%;font-family:monospace;"
                                    >
                                <?php endif; ?>
                            </td>
                            <td style="padding:10px 14px;border:1px solid #ccd0d4;">
                                <?php if ($adjunct === 'color'): ?>
                                    <input
                                        type="color"
                                        class="srlca-color-picker"
                                        data-target="srlca_<?php echo esc_attr($key . '_' . $i); ?>"
                                        value="<?php echo esc_attr($this->to_hex($current, $field['default'])); ?>"
                                        style="width:48px;height:32px;padding:2px;border:1px solid #ccd0d4;border-radius:4px;cursor:pointer;vertical-align:middle;"
                                        title="Pick a color"
                                    >
                                    <span style="font-size:11px;color:#666;margin-left:6px;">color picker</span>
                                <?php elseif ($adjunct === 'text_hint'): ?>
                                    <span style="font-size:11px;color:#888;"><?php echo esc_html($field['hint'] ?? ''); ?></span>
                                <?php elseif ($adjunct === 'textarea'): ?>
                                    <span style="font-size:11px;color:#888;">html content</span>
                                <?php elseif ($adjunct === 'image_picker'): ?>
                                    <span style="font-size:11px;color:#888;">image id</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Save button — bottom -->
                <div style="margin:16px 0;">
                    <?php echo $save_button; ?>
                </div>

            </form>
        </div>

        <script>
        (function() {
            // Color picker sync
            document.querySelectorAll('.srlca-color-picker').forEach(function(picker) {
                var targetId = picker.getAttribute('data-target');
                var textInput = document.getElementById(targetId);
                if (!textInput) return;
                picker.addEventListener('input', function() {
                    textInput.value = picker.value;
                });
                textInput.addEventListener('input', function() {
                    var val = textInput.value.trim();
                    if (/^#[0-9a-fA-F]{3,6}$/.test(val)) {
                        picker.value = val;
                    }
                });
            });

            // WP media popup for image_picker fields
            document.querySelectorAll('.srlca-select-image-btn').forEach(function(btn) {
                var mediaUploader = null;
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var inputId   = btn.getAttribute('data-input-id');
                    var previewId = btn.getAttribute('data-preview-id');
                    var idInput   = document.getElementById(inputId);
                    var preview   = document.getElementById(previewId);

                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }

                    mediaUploader = wp.media({
                        title: 'Select Image',
                        button: { text: 'Use this image' },
                        multiple: false,
                        library: { type: 'image' }
                    });

                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        idInput.value = attachment.id;
                        if (preview) {
                            preview.src = attachment.url;
                            preview.style.display = 'block';
                        }
                    });

                    mediaUploader.open();
                });
            });
        })();
        </script>
        <?php
    }

    /**
     * Convert a stored value to a valid 6-digit hex for the color input.
     */
    private function to_hex($value, $default) {
        $v = trim($value);
        if (preg_match('/^#[0-9a-fA-F]{3,6}$/', $v)) {
            if (strlen($v) === 4) {
                $v = '#' . $v[1].$v[1] . $v[2].$v[2] . $v[3].$v[3];
            }
            return $v;
        }
        $d = trim($default);
        if (preg_match('/^#[0-9a-fA-F]{3,6}$/', $d)) {
            if (strlen($d) === 4) {
                $d = '#' . $d[1].$d[1] . $d[2].$d[2] . $d[3].$d[3];
            }
            return $d;
        }
        return '#ffffff';
    }

    /**
     * Returns true if custom_html_snippet_1 is activated.
     * Called from silkweaver_renderer.php.
     */
    public static function is_snippet_1_active() {
        return get_option('silkweaver_robust_locations_child_area_custom_html_snippet_1_activate', 'no') === 'yes';
    }

    /**
     * Returns the stored HTML content for custom_html_snippet_1.
     * Called from silkweaver_renderer.php.
     */
    public static function get_snippet_1_content() {
        return get_option('silkweaver_robust_locations_child_area_custom_html_snippet_1_content', '');
    }

    /**
     * Returns the WP attachment ID for the main featured image, or 0 if unset.
     * Called from silkweaver_renderer.php.
     */
    public static function get_main_featured_image_id() {
        return absint(get_option('silkweaver_robust_locations_child_area_main_featured_image_id', 0));
    }

    private function suppress_admin_notices() {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');

        add_action('admin_head', function() {
            ?>
            <style>
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice-error,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice-warning,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice-success,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .notice-info,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .error,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .updated,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings .update-nag,
                body.ruplin-hub-2_page_silkweaver_robust_locations_child_area_settings #message {
                    display: none !important;
                }
            </style>
            <?php
        }, 999);

        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.notice, .error, .updated, .update-nag').not('.silkweaver-robust-locations-settings-notice').remove();
            });
            </script>
            <?php
        }, 999);
    }
}

Ruplin_Silkweaver_Robust_Locations_Child_Area_Settings_Admin::get_instance();
