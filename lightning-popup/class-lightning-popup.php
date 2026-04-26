<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lightning Popup — central reusable component.
 *
 * Renders the same Lightning button + modal across:
 *   - WP native post/page edit screen (via Hurricane metabox)
 *   - Telescope content editor (page=telescope_content_editor)
 *   - Cashew editor          (page=cashew_editor)
 *
 * Other features add content to the popup body by hooking
 *   add_action('ruplin_lightning_popup_content', $cb, 10, 1)
 * The hook receives the resolved post ID as its only argument.
 *
 * To add a new screen:
 *   Ruplin_Lightning_Popup::register_screen($hook_id, $post_id_resolver);
 * where $post_id_resolver is a callable() returning the post ID for that screen.
 */
class Ruplin_Lightning_Popup {

    private static $instance = null;
    private $screens = array();   // [ hook_id => callable $post_id_resolver ]

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'maybe_enqueue_assets'));
    }

    /**
     * Register an admin screen that will host the Lightning popup.
     *
     * @param string   $hook_id            The admin page hook (e.g. 'post.php',
     *                                     'admin_page_cashew_editor',
     *                                     'toplevel_page_telescope_content_editor').
     * @param callable $post_id_resolver   Callable returning the post ID for that screen.
     */
    public static function register_screen($hook_id, $post_id_resolver) {
        $self = self::instance();
        $self->screens[$hook_id] = $post_id_resolver;
    }

    /**
     * Resolve the current post ID for whichever Lightning host screen is rendering.
     * Falls back to global $post or 0.
     */
    public static function resolve_post_id() {
        $self = self::instance();
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $hook = $screen ? $screen->id : '';
        if ($hook && isset($self->screens[$hook]) && is_callable($self->screens[$hook])) {
            $resolved = call_user_func($self->screens[$hook]);
            if ($resolved) {
                return (int) $resolved;
            }
        }
        global $post;
        if ($post && isset($post->ID)) {
            return (int) $post->ID;
        }
        return 0;
    }

    /**
     * Hook: admin_enqueue_scripts. Loads CSS+JS only on registered screens.
     */
    public function maybe_enqueue_assets($hook) {
        if (!isset($this->screens[$hook])) {
            return;
        }

        $url = plugin_dir_url(__FILE__);
        $version = defined('SNEFURU_PLUGIN_VERSION') ? SNEFURU_PLUGIN_VERSION : '1.0.0';

        wp_enqueue_style(
            'ruplin-lightning-popup',
            $url . 'assets/lightning-popup.css',
            array(),
            $version
        );

        wp_enqueue_script(
            'ruplin-lightning-popup',
            $url . 'assets/lightning-popup.js',
            array('jquery'),
            $version,
            true
        );

        // Resolve post ID using the registered resolver for this screen.
        $resolver = $this->screens[$hook];
        $post_id = is_callable($resolver) ? (int) call_user_func($resolver) : 0;

        wp_localize_script('ruplin-lightning-popup', 'snefuruLightningPopup', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('ruplin_lightning_popup'),
            'post_id' => $post_id,
        ));
    }

    /**
     * Render the Lightning button. Pass the resolved post ID so click handlers
     * can include it in any data attributes if needed.
     *
     * @param int|null $post_id Optional explicit post ID; resolved automatically if null.
     */
    public static function render_button($post_id = null) {
        if ($post_id === null) {
            $post_id = self::resolve_post_id();
        }
        ?>
        <button type="button"
                class="button button-primary snefuru-lightning-popup-btn"
                data-post-id="<?php echo esc_attr($post_id); ?>"
                onclick="window.snefuruOpenLightningPopup()">
            ⚡ Lightning Popup
        </button>
        <?php
    }

    /**
     * Render the Lightning modal. Sets up $GLOBALS['post'] from the post ID so
     * existing content providers using `global $post` keep working unchanged,
     * and also passes the post ID as the action argument for new providers.
     *
     * @param int|null $post_id Optional explicit post ID; resolved automatically if null.
     */
    public static function render_modal($post_id = null) {
        if ($post_id === null) {
            $post_id = self::resolve_post_id();
        }

        // Save and set up global $post so legacy providers using `global $post` work.
        global $post;
        $previous_post = $post;
        if ($post_id) {
            $maybe_post = get_post($post_id);
            if ($maybe_post) {
                $post = $maybe_post;
                setup_postdata($post);
            }
        }
        ?>
        <div id="snefuru-lightning-popup"
             class="snefuru-popup-overlay"
             data-post-id="<?php echo esc_attr($post_id); ?>"
             style="display: none;">
            <div class="snefuru-popup-container">
                <div class="snefuru-popup-header">
                    <h2 class="snefuru-popup-title">Lightning Popup</h2>
                    <button type="button"
                            class="snefuru-popup-close"
                            onclick="window.snefuruCloseLightningPopup()">&times;</button>
                </div>
                <div class="snefuru-popup-content">
                    <?php
                    do_action('ruplin_lightning_popup_content', $post_id);
                    ?>
                </div>
            </div>
        </div>
        <?php
        // Restore previous global state.
        wp_reset_postdata();
        $post = $previous_post;
    }
}

// Boot the singleton so admin_enqueue_scripts is hooked.
Ruplin_Lightning_Popup::instance();
