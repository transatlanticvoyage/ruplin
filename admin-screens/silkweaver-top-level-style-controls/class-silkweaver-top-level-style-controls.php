<?php
/**
 * Silkweaver Top Level Style Controls Admin Page
 *
 * Admin page for controlling top-level styling of silkweaver nav menus
 * URL: /wp-admin/admin.php?page=silkweaver_top_level_style_controls
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Silkweaver_Top_Level_Style_Controls {

    private static $instance = null;

    private static $fields = array(
        array(
            'key'     => 'silkweaver_top_level_cell_bg_color_on_hover',
            'default' => '#f8f9fa',
            'adjunct' => 'color',
        ),
    );

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 25);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_3_mar',
            'Silkweaver Top Level Style Controls',
            'Silkweaver Top Level Styling Contorls',
            'manage_options',
            'silkweaver_top_level_style_controls',
            array($this, 'render_admin_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'ruplin-hub-3_page_silkweaver_top_level_style_controls') {
            return;
        }
    }

    public function render_admin_page() {
        $this->suppress_admin_notices();

        // Handle save
        $saved = false;
        if (isset($_POST['stlsc_save']) && check_admin_referer('stlsc_save_settings', 'stlsc_nonce')) {
            foreach (self::$fields as $field) {
                $key = $field['key'];
                $val = isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
                if ($field['adjunct'] === 'color' && $val !== '') {
                    if ($val[0] !== '#' && preg_match('/^[0-9a-fA-F]{3,6}$/', $val)) {
                        $val = '#' . $val;
                    }
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

        $save_button = '<input type="submit" name="stlsc_save" class="button button-primary" value="Save Changes">';
        ?>
        <div class="wrap stlsc-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($saved): ?>
            <div style="background:#46b450;color:#fff;padding:10px 16px;border-radius:4px;margin:16px 0;display:inline-block;">
                Settings saved.
            </div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('stlsc_save_settings', 'stlsc_nonce'); ?>

                <!-- Save button — top -->
                <div style="margin:16px 0;">
                    <?php echo $save_button; ?>
                </div>

                <table class="widefat stlsc-table" style="border-collapse:collapse;width:100%;">
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
                                    id="stlsc_<?php echo esc_attr($key . '_' . $i); ?>"
                                    class="stlsc-text-input"
                                    value="<?php echo esc_attr($current); ?>"
                                    placeholder="<?php echo esc_attr($field['default']); ?>"
                                    style="width:100%;font-family:monospace;"
                                >
                            </td>
                            <td style="padding:10px 14px;border:1px solid #ccd0d4;">
                                <?php if ($adjunct === 'color'): ?>
                                    <input
                                        type="color"
                                        class="stlsc-color-picker"
                                        data-target="stlsc_<?php echo esc_attr($key . '_' . $i); ?>"
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
            document.querySelectorAll('.stlsc-color-picker').forEach(function(picker) {
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

    private function suppress_admin_notices() {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');

        add_action('admin_head', function() {
            ?>
            <style>
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls .notice,
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls .notice-error,
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls .notice-warning,
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls .notice-success,
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls .notice-info,
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls .error,
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls .updated,
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls .update-nag,
                body.ruplin-hub-3_page_silkweaver_top_level_style_controls #message {
                    display: none !important;
                }
            </style>
            <?php
        }, 999);

        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.notice, .error, .updated, .update-nag').not('.stlsc-notice').remove();
            });
            </script>
            <?php
        }, 999);
    }

    /**
     * Output inline CSS on the front end for top-level silkweaver nav overrides.
     * Called via wp_head hook from silkweaver_init.php.
     */
    public static function output_frontend_css() {
        $css = '';

        $hover_bg = get_option('silkweaver_top_level_cell_bg_color_on_hover', '');
        if ($hover_bg !== '') {
            $css .= ".silkweaver-menu > li:hover { background: " . esc_attr($hover_bg) . " !important; }\n";
        }

        if ($css !== '') {
            echo "<style id=\"stlsc-overrides\">\n" . $css . "</style>\n";
        }
    }
}

Ruplin_Silkweaver_Top_Level_Style_Controls::get_instance();
