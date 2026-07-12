<?php
/**
 * Blog Post URL Cleanup W/ Redirects Admin Page
 *
 * Child page of Ruplin Hub 3.
 * URL: /wp-admin/admin.php?page=blog_post_url_cleanup_w_redirects
 *
 * --- Claude Code output-file convention for this page's "step 1 / step 2" workflow ---
 * When asked to generate a redirect mapping (the {"old_url","new_url"} JSON array)
 * from this page's step-1 "dead link code" payload, save the result as a file in
 * the user's ~/Downloads folder (not just in chat), named:
 *     claude_blog_link_rewriting_<n>
 * where <n> is one higher than the highest existing claude_blog_link_rewriting_*
 * suffix already in ~/Downloads (start at 1 if none exist). Directory listing of
 * ~/Downloads is blocked by macOS TCC in the Claude Code sandbox (ls / shell glob /
 * python glob.glob all silently return empty even when files exist) — direct access
 * to a KNOWN filename works fine, so find the next number by probing
 * os.path.exists() upward from 1, not by listing the directory.
 * Note: the step-1 payload's `dead_urls` field is a best-effort filter and may be
 * wrong/incomplete — always cross-check against the payload's
 * `target_urls_found_in_blog_content` (the raw, unfiltered hrefs found in blog post
 * content — this is the actual ground truth of what needs fixing) and
 * `active_permalinks` before producing the mapping. In particular,
 * target_urls_found_in_blog_content entries often use the [root_for_inner_links]
 * shortcode instead of a full URL (resolves to the site root at render time) — this
 * page's own JS already treats it as internal when computing dead_urls.
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Blog_Post_Url_Cleanup_W_Redirects {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Priority 31 places this directly below Blog Post Fixer For Broken
        // Sites (30) and above Set Default Sitewide Header And Footer (35,
        // kept as the LAST child under Ruplin Hub 3).
        // (parent registers at 14, warbler at 15, silkweaver at 25.)
        add_action('admin_menu', array($this, 'add_admin_menu'), 31);

        add_action('wp_ajax_ruplin_bpuc_scan_target_urls', array($this, 'ajax_scan_target_urls'));
        add_action('wp_ajax_ruplin_bpuc_execute_url_replacements', array($this, 'ajax_execute_url_replacements'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_3_mar',                        // Parent slug — appears under Ruplin Hub 3
            'Blog Post URL Cleanup W/ Redirects',      // Page title
            'Blog Post URL Cleanup W/ Redirects',      // Menu title
            'manage_options',
            'blog_post_url_cleanup_w_redirects',       // Menu slug
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page() {
        $this->suppress_admin_notices();

        $permalinks = $this->get_all_blog_post_permalinks();
        $page_permalinks = $this->get_all_published_page_permalinks();
        $ajax_nonce = wp_create_nonce('ruplin_bpuc_scan_target_urls');
        $exec_nonce = wp_create_nonce('ruplin_bpuc_execute_url_replacements');
        ?>
        <div class="wrap blog-post-url-cleanup-w-redirects-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div style="margin:20px 0;">
                <button type="button" id="bpuc-scan-target-urls" class="button button-primary">
                    scan all blog posts and grab target urls of links inside them
                </button>
                <span id="bpuc-scan-status" style="margin-left:10px;color:#646970;"></span>

                <p style="margin:12px 0 4px;">
                    <textarea id="bpuc-target-urls-output" readonly rows="14" style="width:100%;max-width:900px;font-family:monospace;font-size:13px;box-sizing:border-box;"></textarea>
                </p>
            </div>

            <hr style="margin:30px 0;">

            <div>
                <p style="font-weight:600;margin-bottom:4px;">permalinks of all current blog posts</p>
                <textarea id="bpuc-blog-permalinks-output" readonly rows="14" style="width:100%;max-width:900px;font-family:monospace;font-size:13px;box-sizing:border-box;"><?php echo esc_textarea(implode("\n", $permalinks)); ?></textarea>
            </div>

            <hr style="margin:30px 0;">

            <div>
                <p style="font-weight:600;margin-bottom:4px;">permalinks of all current published pages on site</p>
                <textarea id="bpuc-page-permalinks-output" readonly rows="14" style="width:100%;max-width:900px;font-family:monospace;font-size:13px;box-sizing:border-box;"><?php echo esc_textarea(implode("\n", $page_permalinks)); ?></textarea>
            </div>

            <hr style="margin:30px 0;">

            <div>
                <h2 style="margin:0 0 6px;font-size:16px;">execute updates in all blog posts post_content area, change target_url to new target_url</h2>

                <div style="margin:16px 0;padding:16px;border:1px solid #ccd0d4;background:#fff;border-radius:4px;max-width:900px;">
                    <p style="margin:0 0 8px;font-weight:600;">step 1 — generate dead-link code to give to Claude Code</p>
                    <p style="margin:0 0 10px;color:#646970;">
                        Bundles the full raw list of link targets found in blog post content (top box above — the ground truth of what needs fixing), the published post + page permalinks shown above (the "active" URLs on the site), and a best-effort filtered list of same-site links that don't match anything live. Copy the generated code below and hand it to Claude Code, and ask it to figure out the correct new permalink for each broken/renamed URL.
                    </p>
                    <button type="button" id="bpuc-find-dead-links" class="button">find dead blog post links</button>
                    <span id="bpuc-deadlink-status" style="margin-left:10px;color:#646970;"></span>
                    <p style="margin:12px 0 4px;">
                        <textarea id="bpuc-dead-link-code" readonly rows="10" style="width:100%;font-family:monospace;font-size:13px;box-sizing:border-box;"></textarea>
                    </p>
                </div>

                <div style="margin:16px 0;padding:16px;border:1px solid #ccd0d4;background:#fff;border-radius:4px;max-width:900px;">
                    <p style="margin:0 0 8px;font-weight:600;">step 2 — paste Claude Code's redirect mapping and execute</p>
                    <p style="margin:0 0 10px;color:#646970;">
                        Paste back a JSON array of <code>{"old_url": "...", "new_url": "..."}</code> pairs, e.g.
                        <code>[{"old_url":"https://example.com/in-house-repair/","new_url":"https://example.com/house-repair/"}]</code>.
                        On execute, every published blog post's post_content is scanned for <code>href="old_url"</code> links and rewritten to <code>new_url</code>.
                    </p>
                    <textarea id="bpuc-mapping-input" rows="10" placeholder='[{"old_url":"...","new_url":"..."}]' style="width:100%;font-family:monospace;font-size:13px;box-sizing:border-box;"></textarea>
                    <p style="margin:12px 0 0;">
                        <button type="button" id="bpuc-execute-changes" class="button button-primary">execute changes</button>
                        <span id="bpuc-execute-status" style="margin-left:10px;color:#646970;"></span>
                    </p>
                    <div id="bpuc-execute-report" style="margin-top:12px;"></div>
                </div>
            </div>
        </div>

        <script>
        (function($) {
            $(document).ready(function() {
                var siteHomeUrl = <?php echo wp_json_encode(home_url('/')); ?>;

                // [root_for_inner_links] is a shortcode used throughout post_content
                // in place of the site root (resolves to https://{site}/slug/ at
                // render time) — it must be treated as an internal/same-site link.
                var ROOT_SHORTCODE = /^\[root_for_inner_links\]/i;

                function normalizeUrl(u) {
                    u = (u || '').trim();
                    u = u.replace(/#.*$/, '');
                    u = u.replace(/^https?:\/\//i, '');
                    u = u.replace(/^www\./i, '');
                    u = u.replace(/\/+$/, '');
                    return u.toLowerCase();
                }

                function isSameSiteUrl(u, homeNormalized) {
                    u = (u || '').trim();
                    if (u === '') {
                        return false;
                    }
                    if (ROOT_SHORTCODE.test(u)) {
                        return true; // [root_for_inner_links]/slug
                    }
                    if (u.charAt(0) === '/' && u.charAt(1) !== '/') {
                        return true; // site-relative path
                    }
                    if (/^https?:\/\//i.test(u)) {
                        return normalizeUrl(u).indexOf(homeNormalized) === 0;
                    }
                    return false; // mailto:, tel:, javascript:, protocol-relative, external, etc.
                }

                // Reduces any same-site URL form (full https://host/slug/,
                // site-relative /slug/, or [root_for_inner_links]/slug) down to
                // just "/slug" so the three forms can be compared for equality.
                function toComparablePath(u) {
                    u = (u || '').trim();
                    u = u.replace(/#.*$/, '');
                    if (ROOT_SHORTCODE.test(u)) {
                        u = u.replace(ROOT_SHORTCODE, '');
                    } else if (/^https?:\/\//i.test(u)) {
                        u = u.replace(/^https?:\/\//i, '').replace(/^www\./i, '');
                        var slashIdx = u.indexOf('/');
                        u = (slashIdx === -1) ? '' : u.substring(slashIdx);
                    }
                    if (u.charAt(0) !== '/') {
                        u = '/' + u;
                    }
                    u = u.replace(/\/+$/, '');
                    return u.toLowerCase();
                }

                function linesFrom(text) {
                    return (text || '').split('\n').map(function(s) { return s.trim(); }).filter(Boolean);
                }

                /* ---- scan all blog posts and grab target urls of links inside them ---- */
                var scanBtn    = $('#bpuc-scan-target-urls');
                var statusEl   = $('#bpuc-scan-status');
                var outputBox  = $('#bpuc-target-urls-output');

                scanBtn.on('click', function() {
                    scanBtn.prop('disabled', true);
                    statusEl.text('Scanning...');
                    outputBox.val('');

                    $.post(ajaxurl, {
                        action: 'ruplin_bpuc_scan_target_urls',
                        nonce: '<?php echo esc_js($ajax_nonce); ?>'
                    }).done(function(response) {
                        if (response && response.success) {
                            outputBox.val(response.data.text);
                            statusEl.text(response.data.count + ' unique URL(s) found across ' + response.data.post_count + ' post(s).');
                        } else {
                            var msg = (response && response.data && response.data.message) ? response.data.message : 'Scan failed.';
                            statusEl.text(msg);
                        }
                    }).fail(function() {
                        statusEl.text('Scan failed — request error.');
                    }).always(function() {
                        scanBtn.prop('disabled', false);
                    });
                });

                /* ---- step 1: find dead blog post links ---- */
                var findDeadBtn     = $('#bpuc-find-dead-links');
                var deadLinkStatus  = $('#bpuc-deadlink-status');
                var deadLinkCodeBox = $('#bpuc-dead-link-code');

                findDeadBtn.on('click', function() {
                    var targetUrls = linesFrom(outputBox.val());
                    if (!targetUrls.length) {
                        deadLinkStatus.text('Click "scan all blog posts..." above first — that box is empty.');
                        deadLinkCodeBox.val('');
                        return;
                    }

                    var activeRaw = linesFrom($('#bpuc-blog-permalinks-output').val()).concat(linesFrom($('#bpuc-page-permalinks-output').val()));
                    var activePathSet = {};
                    activeRaw.forEach(function(u) { activePathSet[toComparablePath(u)] = true; });

                    var homeNormalized = normalizeUrl(siteHomeUrl);
                    var deadUrls = targetUrls.filter(function(u) {
                        return isSameSiteUrl(u, homeNormalized) && !activePathSet[toComparablePath(u)];
                    });

                    var payload = {
                        site_url: siteHomeUrl,
                        instructions: 'target_urls_found_in_blog_content is EVERY link target currently found inside blog post content (unfiltered — this is the ground truth of what needs fixing). dead_urls is a same-site subset of that list which does not match anything in active_permalinks (this filtering is best-effort and may be wrong/incomplete, e.g. due to trailing-slash or scheme mismatches — always double check against target_urls_found_in_blog_content and active_permalinks directly, do not trust dead_urls alone). For each broken/renamed link you find, match it to the best entry in active_permalinks (the old link is usually the same slug with one or two words removed/changed, e.g. a filler word like "in") and return a JSON array of {"old_url": <broken url exactly as it appears in target_urls_found_in_blog_content>, "new_url": <matching active permalink>} pairs. Only include pairs where old_url != new_url and old_url actually appears in target_urls_found_in_blog_content.',
                        target_urls_found_in_blog_content: targetUrls,
                        dead_urls: deadUrls,
                        active_permalinks: activeRaw
                    };

                    deadLinkCodeBox.val(JSON.stringify(payload, null, 2));
                    deadLinkStatus.text(deadUrls.length + ' dead same-site link(s) found out of ' + targetUrls.length + ' scanned URL(s).');
                });

                /* ---- step 2: paste mapping and execute changes ---- */
                var execBtn       = $('#bpuc-execute-changes');
                var execStatus    = $('#bpuc-execute-status');
                var execReport    = $('#bpuc-execute-report');
                var mappingInput  = $('#bpuc-mapping-input');

                execBtn.on('click', function() {
                    var raw = mappingInput.val().trim();
                    if (!raw) {
                        execStatus.text('Paste a redirect mapping JSON array first.');
                        return;
                    }

                    var parsed;
                    try {
                        parsed = JSON.parse(raw);
                    } catch (e) {
                        execStatus.text('That is not valid JSON.');
                        return;
                    }
                    if (!Array.isArray(parsed) || !parsed.length) {
                        execStatus.text('Expected a non-empty JSON array of {"old_url","new_url"} pairs.');
                        return;
                    }

                    if (!window.confirm('This will rewrite matching href links inside ALL published blog posts\' content (' + parsed.length + ' mapping pair(s)). This cannot be easily undone.\n\nProceed?')) {
                        return;
                    }

                    execBtn.prop('disabled', true);
                    execStatus.text('Executing...');
                    execReport.empty();

                    $.post(ajaxurl, {
                        action: 'ruplin_bpuc_execute_url_replacements',
                        nonce: '<?php echo esc_js($exec_nonce); ?>',
                        mapping_json: raw
                    }).done(function(response) {
                        if (response && response.success) {
                            var d = response.data;
                            execStatus.text('Done.');
                            execReport.html(
                                '<p style="margin:0;">Posts scanned: ' + d.posts_scanned +
                                ' &nbsp;|&nbsp; Posts updated: ' + d.posts_updated +
                                ' &nbsp;|&nbsp; Links replaced: ' + d.links_replaced + '</p>'
                            );
                        } else {
                            var msg = (response && response.data && response.data.message) ? response.data.message : 'Execution failed.';
                            execStatus.text(msg);
                        }
                    }).fail(function() {
                        execStatus.text('Execution failed — request error.');
                    }).always(function() {
                        execBtn.prop('disabled', false);
                    });
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * AJAX handler: scan every blog post's post_content for <a href="...">
     * links, extract the target URLs, and return a de-duped list.
     */
    public function ajax_scan_target_urls() {
        check_ajax_referer('ruplin_bpuc_scan_target_urls', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $posts = get_posts(array(
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'numberposts'      => -1,
            'suppress_filters' => true,
        ));

        $urls = array();
        foreach ($posts as $post) {
            if (preg_match_all('/<a\b[^>]*\shref\s*=\s*(["\'])(.*?)\1/is', $post->post_content, $matches)) {
                foreach ($matches[2] as $href) {
                    $href = trim(html_entity_decode($href, ENT_QUOTES));
                    if ($href === '' || $href[0] === '#') {
                        continue;
                    }
                    $urls[$href] = true; // de-dupe, preserves first-seen order
                }
            }
        }

        $url_list = array_keys($urls);

        wp_send_json_success(array(
            'text'       => implode("\n", $url_list),
            'count'      => count($url_list),
            'post_count' => count($posts),
        ));
    }

    /**
     * AJAX handler: apply a pasted old_url => new_url redirect mapping by
     * rewriting matching <a href="..."> targets inside every published blog
     * post's post_content.
     */
    public function ajax_execute_url_replacements() {
        check_ajax_referer('ruplin_bpuc_execute_url_replacements', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $raw = isset($_POST['mapping_json']) ? wp_unslash($_POST['mapping_json']) : '';
        $mapping = json_decode($raw, true);

        if (!is_array($mapping) || empty($mapping)) {
            wp_send_json_error(array('message' => 'Could not parse mapping JSON. Expected a non-empty array of {"old_url":"...","new_url":"..."} objects.'));
        }

        $pairs = array();
        foreach ($mapping as $row) {
            if (!is_array($row) || empty($row['old_url']) || empty($row['new_url'])) {
                continue;
            }
            $old = trim($row['old_url']);
            $new = trim($row['new_url']);
            if ($old === '' || $new === '' || $old === $new) {
                continue;
            }
            $pairs[] = array('old' => $old, 'new' => $new);
        }

        if (empty($pairs)) {
            wp_send_json_error(array('message' => 'No valid old_url/new_url pairs found in the pasted mapping.'));
        }

        $posts = get_posts(array(
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'numberposts'      => -1,
            'suppress_filters' => true,
        ));

        $posts_updated  = 0;
        $links_replaced = 0;

        foreach ($posts as $post) {
            $content  = $post->post_content;
            $original = $content;

            foreach ($pairs as $pair) {
                $pattern = '/href\s*=\s*(["\'])' . preg_quote($pair['old'], '/') . '\1/i';
                $count   = 0;
                $content = preg_replace_callback(
                    $pattern,
                    function ($m) use ($pair) {
                        return 'href=' . $m[1] . $pair['new'] . $m[1];
                    },
                    $content,
                    -1,
                    $count
                );
                $links_replaced += $count;
            }

            if ($content !== $original) {
                wp_update_post(array(
                    'ID'           => $post->ID,
                    'post_content' => $content,
                ));
                $posts_updated++;
            }
        }

        wp_send_json_success(array(
            'posts_scanned'  => count($posts),
            'posts_updated'  => $posts_updated,
            'links_replaced' => $links_replaced,
        ));
    }

    /**
     * Permalinks for every currently-published blog post.
     *
     * @return string[]
     */
    private function get_all_blog_post_permalinks() {
        $posts = get_posts(array(
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'numberposts'      => -1,
            'suppress_filters' => true,
        ));

        $permalinks = array();
        foreach ($posts as $post) {
            $permalinks[] = get_permalink($post);
        }

        return $permalinks;
    }

    /**
     * Permalinks for every currently-published page (post_type 'page').
     *
     * @return string[]
     */
    private function get_all_published_page_permalinks() {
        $pages = get_posts(array(
            'post_type'        => 'page',
            'post_status'      => 'publish',
            'numberposts'      => -1,
            'suppress_filters' => true,
        ));

        $permalinks = array();
        foreach ($pages as $page) {
            $permalinks[] = get_permalink($page);
        }

        return $permalinks;
    }

    /**
     * Aggressive WP admin notice / message / warning suppression,
     * matching the pattern used on our other Ruplin admin pages.
     */
    private function suppress_admin_notices() {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');

        add_action('admin_head', function() {
            ?>
            <style>
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects .notice,
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects .notice-error,
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects .notice-warning,
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects .notice-success,
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects .notice-info,
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects .error,
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects .updated,
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects .update-nag,
                body.ruplin-hub-3_page_blog_post_url_cleanup_w_redirects #message {
                    display: none !important;
                }
            </style>
            <?php
        }, 999);

        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.notice, .error, .updated, .update-nag').remove();
            });
            </script>
            <?php
        }, 999);
    }
}

Ruplin_Blog_Post_Url_Cleanup_W_Redirects::get_instance();
