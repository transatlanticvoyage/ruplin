<?php
/**
 * Silkweaver Robust Services Child Area Settings Admin Page
 *
 * URL: /wp-admin/admin.php?page=silkweaver_robust_services_child_area_settings
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Silkweaver_Robust_Services_Child_Area_Settings_Admin {

    private static $instance = null;

    /**
     * All option keys managed by this page, with their defaults and adjunct type.
     * adjunct: 'color' | 'text_hint'
     * default: reflects the base CSS value so the page looks correct out of the box
     */
    private static $fields = array(
        array(
            'key'     => 'silkweaver_robust_services_child_area_broader_area_bg_color',
            'default' => '#ffffff',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_category_tiles_bg_color',
            'default' => '#fafafa',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_category_image_thumbnail_area_bg_color',
            'default' => '#e8e8e8',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_category_tiles_border',
            'default' => '1px solid #e0e0e0',
            'adjunct' => 'text_hint',
            'hint'    => 'e.g. 1px solid #e0e0e0',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_border_broader_area',
            'default' => '1px solid #ddd',
            'adjunct' => 'text_hint',
            'hint'    => 'e.g. 1px solid #ddd',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_category_name_text_color',
            'default' => '#111111',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_category_name_text_size',
            'default' => '14px',
            'adjunct' => 'text_hint',
            'hint'    => 'e.g. 14px',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_text_color',
            'default' => '#444444',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_text_size',
            'default' => '12px',
            'adjunct' => 'text_hint',
            'hint'    => 'e.g. 12px',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_text_color_on_hover',
            'default' => '#000000',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_underline_on_hover',
            'default' => 'underline',
            'adjunct' => 'text_hint',
            'hint'    => 'underline | none',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_every_bg_color_on_hover',
            'default' => '#f0f0f0',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_bullet_yes_no',
            'default' => 'no',
            'adjunct' => 'text_hint',
            'hint'    => 'yes | no',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_bullet_color',
            'default' => '#444444',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_bullet_size',
            'default' => '12px',
            'adjunct' => 'text_hint',
            'hint'    => 'e.g. 12px',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_oscillate_bg_color_yes_no',
            'default' => 'no',
            'adjunct' => 'text_hint',
            'hint'    => 'yes | no',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_oscillate_bg_color_bg_color_1',
            'default' => '#ffffff',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_oscillate_bg_color_bg_color_2',
            'default' => '#f5f5f5',
            'adjunct' => 'color',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_row_padding_top_and_bottom',
            'default' => '4px',
            'adjunct' => 'text_hint',
            'hint'    => 'e.g. 8px or 8 (px auto-added)',
        ),
        array(
            'key'     => 'silkweaver_robust_services_child_area_servicepagepylons_moniker_row_padding_left_and_right',
            'default' => '12px',
            'adjunct' => 'text_hint',
            'hint'    => 'e.g. 16px or 16 (px auto-added)',
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
            'Silkweaver Robust Services Child Area Settings',
            'silkweaver robust services child area settings',
            'manage_options',
            'silkweaver_robust_services_child_area_settings',
            array($this, 'render_admin_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'ruplin-hub-2_page_silkweaver_robust_services_child_area_settings') {
            return;
        }
        // Assets can be enqueued here in future
    }

    public function render_admin_page() {
        $this->suppress_admin_notices();

        // Handle save
        $saved = false;
        if (isset($_POST['srsca_save']) && check_admin_referer('srsca_save_settings', 'srsca_nonce')) {
            $css_dim_keys = array(
                'silkweaver_robust_services_child_area_servicepagepylons_moniker_row_padding_top_and_bottom',
                'silkweaver_robust_services_child_area_servicepagepylons_moniker_row_padding_left_and_right',
            );
            foreach (self::$fields as $field) {
                $key = $field['key'];
                $val = isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
                if (in_array($key, $css_dim_keys, true)) {
                    $val = self::sanitize_css_dimension($val);
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

        $save_button = '<input type="submit" name="srsca_save" class="button button-primary" value="Save Changes">';
        ?>
        <div class="wrap srsca-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($saved): ?>
            <div style="background:#46b450;color:#fff;padding:10px 16px;border-radius:4px;margin:16px 0;display:inline-block;">
                Settings saved.
            </div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('srsca_save_settings', 'srsca_nonce'); ?>

                <!-- Save button — top -->
                <div style="margin:16px 0;">
                    <?php echo $save_button; ?>
                </div>

                <table class="widefat srsca-table" style="border-collapse:collapse;width:100%;">
                    <thead>
                        <tr>
                            <th style="font-weight:700;text-transform:lowercase;padding:10px 14px;background:#f1f1f1;border:1px solid #ccd0d4;width:45%;">field-name</th>
                            <th style="font-weight:700;text-transform:lowercase;padding:10px 14px;background:#f1f1f1;border:1px solid #ccd0d4;width:30%;">datum-house</th>
                            <th style="font-weight:700;text-transform:lowercase;padding:10px 14px;background:#f1f1f1;border:1px solid #ccd0d4;width:25%;">adjunct 51</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (self::$fields as $i => $field):
                            $key      = $field['key'];
                            $adjunct  = $field['adjunct'];
                            $current  = $values[$key];
                            $bg       = ($i % 2 === 0) ? '#fff' : '#f9f9f9';
                        ?>
                        <tr style="background:<?php echo $bg; ?>;">
                            <td style="padding:10px 14px;border:1px solid #ccd0d4;font-weight:700;font-size:12px;word-break:break-all;">
                                <?php echo esc_html($key); ?>
                            </td>
                            <td style="padding:10px 14px;border:1px solid #ccd0d4;">
                                <input
                                    type="text"
                                    name="<?php echo esc_attr($key); ?>"
                                    id="srsca_<?php echo esc_attr($key . '_' . $i); ?>"
                                    class="srsca-text-input"
                                    value="<?php echo esc_attr($current); ?>"
                                    placeholder="<?php echo esc_attr($field['default']); ?>"
                                    style="width:100%;font-family:monospace;"
                                >
                            </td>
                            <td style="padding:10px 14px;border:1px solid #ccd0d4;">
                                <?php if ($adjunct === 'color'): ?>
                                    <input
                                        type="color"
                                        class="srsca-color-picker"
                                        data-target="srsca_<?php echo esc_attr($key . '_' . $i); ?>"
                                        value="<?php echo esc_attr($this->to_hex($current, $field['default'])); ?>"
                                        style="width:48px;height:32px;padding:2px;border:1px solid #ccd0d4;border-radius:4px;cursor:pointer;vertical-align:middle;"
                                        title="Pick a color"
                                    >
                                    <span style="font-size:11px;color:#666;margin-left:6px;">color picker</span>
                                <?php elseif ($adjunct === 'text_hint'): ?>
                                    <span style="font-size:11px;color:#888;"><?php echo esc_html($field['hint'] ?? ''); ?></span>
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
            // Color picker syncs to text input
            document.querySelectorAll('.srsca-color-picker').forEach(function(picker) {
                var targetId = picker.getAttribute('data-target');
                var textInput = document.getElementById(targetId);
                if (!textInput) return;

                picker.addEventListener('input', function() {
                    textInput.value = picker.value;
                });

                // Also sync back: when user types a valid hex into text input, update picker
                textInput.addEventListener('input', function() {
                    var val = textInput.value.trim();
                    if (/^#[0-9a-fA-F]{3,6}$/.test(val)) {
                        picker.value = val;
                    }
                });
            });
        })();
        </script>
        <?php
    }

    /**
     * Validate and normalize a CSS dimension value.
     * - Bare integers/decimals (e.g. "8", "1.5") auto-get "px" appended.
     * - Spaces between number and unit are stripped (e.g. "8 px" → "8px").
     * - Allowed units: px, em, rem, %, vh, vw.
     * - Bare "0" is valid (unitless zero is legal in CSS).
     * - Returns '' if the value is empty or cannot be made valid.
     */
    private static function sanitize_css_dimension($value) {
        $v = trim($value);
        if ($v === '') {
            return '';
        }
        if ($v === '0') {
            return '0';
        }
        // Strip any space between number and unit
        $v = preg_replace('/^(\d+(?:\.\d+)?)\s*(px|em|rem|%|vh|vw)$/i', '$1$2', $v);
        // Auto-append px for bare numbers
        if (preg_match('/^\d+(\.\d+)?$/', $v)) {
            $v = $v . 'px';
        }
        if (preg_match('/^\d+(\.\d+)?(px|em|rem|%|vh|vw)$/i', $v)) {
            return strtolower($v);
        }
        return ''; // invalid — treat as unset
    }

    /**
     * Convert a stored value to a valid 6-digit hex for the color input.
     * Falls back to the field default if value is empty or not a plain hex.
     */
    private function to_hex($value, $default) {
        $v = trim($value);
        if (preg_match('/^#[0-9a-fA-F]{3,6}$/', $v)) {
            // Expand 3-char hex to 6-char
            if (strlen($v) === 4) {
                $v = '#' . $v[1].$v[1] . $v[2].$v[2] . $v[3].$v[3];
            }
            return $v;
        }
        // Fall back to default
        $d = trim($default);
        if (preg_match('/^#[0-9a-fA-F]{3,6}$/', $d)) {
            if (strlen($d) === 4) {
                $d = '#' . $d[1].$d[1] . $d[2].$d[2] . $d[3].$d[3];
            }
            return $d;
        }
        return '#ffffff';
    }

    private function suppress_admin_notices() {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');

        add_action('admin_head', function() {
            ?>
            <style>
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings .notice,
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings .notice-error,
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings .notice-warning,
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings .notice-success,
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings .notice-info,
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings .error,
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings .updated,
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings .update-nag,
                body.ruplin-hub-2_page_silkweaver_robust_services_child_area_settings #message {
                    display: none !important;
                }
            </style>
            <?php
        }, 999);

        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.notice, .error, .updated, .update-nag').not('.silkweaver-robust-services-settings-notice').remove();
            });
            </script>
            <?php
        }, 999);
    }

    /**
     * Output inline CSS on the front end that applies any saved option overrides.
     * Only outputs a rule when the option has been explicitly saved (non-empty).
     * Called from silkweaver_init.php via wp_head.
     */
    public static function output_frontend_css() {
        $opts = array(
            'silkweaver_robust_services_child_area_broader_area_bg_color'                      => array('sel' => '.silkweaver-robust-child-area',      'prop' => 'background'),
            'silkweaver_robust_services_child_area_category_tiles_bg_color'                    => array('sel' => '.silkweaver-robust-tile',             'prop' => 'background'),
            'silkweaver_robust_services_child_area_category_image_thumbnail_area_bg_color'     => array('sel' => '.silkweaver-robust-tile-image',        'prop' => 'background'),
            'silkweaver_robust_services_child_area_category_tiles_border'                      => array('sel' => '.silkweaver-robust-tile',             'prop' => 'border'),
            'silkweaver_robust_services_child_area_border_broader_area'                        => array('sel' => '.silkweaver-robust-child-area',      'prop' => 'border'),
            'silkweaver_robust_services_child_area_category_name_text_color'                   => array('sel' => '.silkweaver-robust-tile-name',        'prop' => 'color'),
            'silkweaver_robust_services_child_area_category_name_text_size'                    => array('sel' => '.silkweaver-robust-tile-name',        'prop' => 'font-size'),
            'silkweaver_robust_services_child_area_servicepagepylons_moniker_text_color'       => array('sel' => '.silkweaver-robust-child-pages a',    'prop' => 'color'),
            'silkweaver_robust_services_child_area_servicepagepylons_moniker_text_size'       => array('sel' => '.silkweaver-robust-child-pages a',    'prop' => 'font-size'),
            'silkweaver_robust_services_child_area_servicepagepylons_moniker_text_color_on_hover' => array('sel' => '.silkweaver-robust-child-pages a:hover', 'prop' => 'color'),
            'silkweaver_robust_services_child_area_servicepagepylons_moniker_underline_on_hover'  => array('sel' => '.silkweaver-robust-child-pages a:hover', 'prop' => 'text-decoration'),
        );

        $css = '';
        foreach ($opts as $key => $rule) {
            $val = get_option($key, '');
            if ($val !== '') {
                $css .= sprintf(
                    '%s { %s: %s !important; }',
                    $rule['sel'],
                    $rule['prop'],
                    esc_attr($val)
                ) . "\n";
            }
        }

        // Bullet yes/no — controls visibility of .silkweaver-robust-moniker-bullet span
        $bullet_yn = get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_bullet_yes_no', '');
        if ($bullet_yn === 'yes') {
            $bullet_color = get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_bullet_color', '');
            if ($bullet_color !== '') {
                $css .= ".silkweaver-robust-moniker-bullet { background: " . esc_attr($bullet_color) . " !important; }\n";
            }
            $bullet_size = get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_bullet_size', '');
            if ($bullet_size !== '') {
                $css .= ".silkweaver-robust-moniker-bullet { width: " . esc_attr($bullet_size) . " !important; height: " . esc_attr($bullet_size) . " !important; }\n";
            }
        }

        // Oscillating row background colors — applied to .silkweaver-robust-moniker-row
        $oscillate_yn = get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_oscillate_bg_color_yes_no', '');
        if ($oscillate_yn === 'yes') {
            $color1 = get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_oscillate_bg_color_bg_color_1', '');
            $color2 = get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_oscillate_bg_color_bg_color_2', '');
            if ($color1 !== '') {
                $css .= ".silkweaver-robust-child-pages li:nth-child(odd) .silkweaver-robust-moniker-row { background: " . esc_attr($color1) . " !important; }\n";
            }
            if ($color2 !== '') {
                $css .= ".silkweaver-robust-child-pages li:nth-child(even) .silkweaver-robust-moniker-row { background: " . esc_attr($color2) . " !important; }\n";
            }
        }

        // Hover bg color for the entire moniker row item.
        // Uses li:hover .silkweaver-robust-moniker-row (specificity 0,3,2) placed after the oscillate rules
        // (same specificity 0,3,2) so it wins by source order and overrides oscillate bg on hover.
        // The <a> inside is set to transparent so the row bg shows through it cleanly.
        $moniker_hover_bg = get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_every_bg_color_on_hover', '');
        if ($moniker_hover_bg !== '') {
            $css .= ".silkweaver-robust-child-pages li:hover .silkweaver-robust-moniker-row { background: " . esc_attr($moniker_hover_bg) . " !important; }\n";
            $css .= ".silkweaver-robust-child-pages li:hover a { background: transparent !important; }\n";
        }

        // Moniker row padding — output as a single padding shorthand so it cleanly overrides
        // the theme's own "padding: 4px 12px !important" shorthand (avoids longhand vs shorthand
        // cascade ambiguity). Falls back to the theme default for whichever dimension isn't set.
        // sanitize_css_dimension is run here too so stale/invalid DB values are never used.
        $pad_tb = self::sanitize_css_dimension(get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_row_padding_top_and_bottom', ''));
        $pad_lr = self::sanitize_css_dimension(get_option('silkweaver_robust_services_child_area_servicepagepylons_moniker_row_padding_left_and_right', ''));
        if ($pad_tb !== '' || $pad_lr !== '') {
            $tb = ($pad_tb !== '') ? esc_attr($pad_tb) : '4px';
            $lr = ($pad_lr !== '') ? esc_attr($pad_lr) : '12px';
            $css .= ".silkweaver-robust-moniker-row { padding: {$tb} {$lr} !important; }\n";
        }

        if ($css !== '') {
            echo "<style id=\"srsca-overrides\">\n" . $css . "</style>\n";
        }
    }
}

Ruplin_Silkweaver_Robust_Services_Child_Area_Settings_Admin::get_instance();
