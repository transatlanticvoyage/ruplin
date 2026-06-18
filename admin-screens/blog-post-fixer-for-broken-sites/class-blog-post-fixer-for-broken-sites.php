<?php
/**
 * Blog Post Fixer For Broken Sites Admin Page
 *
 * Child page of Ruplin Hub 3.
 * URL: /wp-admin/admin.php?page=blog_post_fixer_for_broken_sites
 *
 * Page is intentionally blank for now — feature content to be added later.
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Blog_Post_Fixer_For_Broken_Sites {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Priority 30 keeps this as the LAST child under Ruplin Hub 3
        // (parent registers at 14, warbler at 15, silkweaver at 25).
        add_action('admin_menu', array($this, 'add_admin_menu'), 30);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'ruplin_hub_3_mar',                       // Parent slug — appears under Ruplin Hub 3
            'Blog Post Fixer For Broken Sites',       // Page title
            'Blog Post Fixer For Broken Sites',       // Menu title
            'manage_options',
            'blog_post_fixer_for_broken_sites',       // Menu slug
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page() {
        $this->suppress_admin_notices();

        // ---- Handle f8372 run (re-assign post dates + change URLs) ----
        $keep_past_value = 12; // default for the "# of past posts to keep same date" input
        $result = null;
        if (isset($_POST['bpffbs_run_f8372']) && check_admin_referer('bpffbs_f8372', 'bpffbs_f8372_nonce')) {
            $keep_past_value = isset($_POST['bpffbs_keep_past']) ? max(0, intval($_POST['bpffbs_keep_past'])) : 12;
            $result = $this->run_f8372($keep_past_value);
        } elseif (isset($_POST['bpffbs_keep_past'])) {
            $keep_past_value = max(0, intval($_POST['bpffbs_keep_past']));
        }

        // Pull every post regardless of status (publish, future, draft, pending,
        // private, trash). Default order = most recent first, which places
        // future-scheduled posts (post_date in the future) at the very top.
        $posts = get_posts(array(
            'post_type'        => 'post',
            'post_status'      => 'any',
            'numberposts'      => -1,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'suppress_filters' => true,
        ));

        $th_style = 'font-size:16px;font-weight:700;text-transform:lowercase;text-align:left;padding:10px 14px;background:#f1f1f1;border:1px solid #ccd0d4;cursor:pointer;user-select:none;white-space:nowrap;';
        $td_style = 'padding:8px 14px;border:1px solid #ccd0d4;vertical-align:top;';
        ?>
        <div class="wrap blog-post-fixer-for-broken-sites-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($result !== null): ?>
            <div style="background:#46b450;color:#fff;padding:12px 16px;border-radius:4px;margin:16px 0;">
                <strong>f8372 complete.</strong>
                Processed <?php echo intval($result['total']); ?> posts.
                URLs changed: <?php echo intval($result['urls_changed']); ?>
                (method&nbsp;A: <?php echo intval($result['method_a']); ?>,
                 method&nbsp;B: <?php echo intval($result['method_b']); ?>,
                 unchanged: <?php echo intval($result['url_unchanged']); ?>).
                Dates kept: <?php echo intval($result['kept_dates']); ?>.
                Dates re-assigned: <?php echo intval($result['dates_reassigned']); ?>.
                <?php if (!empty($result['errors'])): ?>
                <span style="color:#ffd9d9;">Errors: <?php echo intval($result['errors']); ?>.</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- f8372 controls -->
            <form method="post" id="bpffbs-f8372-form" style="margin:16px 0;padding:16px;border:1px solid #ccd0d4;background:#fff;border-radius:4px;max-width:760px;">
                <?php wp_nonce_field('bpffbs_f8372', 'bpffbs_f8372_nonce'); ?>
                <div style="margin-bottom:12px;">
                    <label for="bpffbs_keep_past" style="display:block;font-weight:600;margin-bottom:4px;"># of past posts to keep same date</label>
                    <input type="number" min="0" step="1" name="bpffbs_keep_past" id="bpffbs_keep_past" value="<?php echo esc_attr($keep_past_value); ?>" style="width:90px;">
                </div>
                <button type="submit" name="bpffbs_run_f8372" value="1" id="bpffbs-run-f8372" class="button button-primary">
                    run f8372 - re assign post dates and change urls
                </button>
                <p style="color:#666;margin:10px 0 0;font-size:12px;">
                    Affects <strong>all</strong> posts automatically — no selection needed.
                    Changes each post's URL slug (Part&nbsp;1) and re-drips post dates (Part&nbsp;2).
                </p>
            </form>

            <p style="color:#666;margin:8px 0 16px;"><?php echo count($posts); ?> posts</p>

            <table id="bpffbs-table" class="widefat" style="border-collapse:collapse;width:100%;">
                <thead>
                    <tr>
                        <th style="<?php echo esc_attr($th_style); ?>cursor:default;width:32px;text-align:center;">
                            <input type="checkbox" id="bpffbs-select-all" title="Select all">
                        </th>
                        <th style="<?php echo esc_attr($th_style); ?>" data-col="0" data-type="text">post_date <span class="bpffbs-arrow"></span></th>
                        <th style="<?php echo esc_attr($th_style); ?>" data-col="1" data-type="text">post_status <span class="bpffbs-arrow"></span></th>
                        <th style="<?php echo esc_attr($th_style); ?>" data-col="2" data-type="text">post_title <span class="bpffbs-arrow"></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr><td colspan="4" style="<?php echo esc_attr($td_style); ?>">No posts found.</td></tr>
                    <?php else: foreach ($posts as $i => $post):
                        $bg = ($i % 2 === 0) ? '#fff' : '#f9f9f9';
                    ?>
                        <tr style="background:<?php echo $bg; ?>;">
                            <td style="<?php echo esc_attr($td_style); ?>text-align:center;">
                                <input type="checkbox" class="bpffbs-row-check" value="<?php echo esc_attr($post->ID); ?>">
                            </td>
                            <td style="<?php echo esc_attr($td_style); ?>font-family:monospace;white-space:nowrap;" data-sort="<?php echo esc_attr($post->post_date); ?>"><?php echo esc_html($post->post_date); ?></td>
                            <td style="<?php echo esc_attr($td_style); ?>" data-sort="<?php echo esc_attr($post->post_status); ?>"><?php echo esc_html($post->post_status); ?></td>
                            <td style="<?php echo esc_attr($td_style); ?>" data-sort="<?php echo esc_attr($post->post_title); ?>"><?php echo esc_html($post->post_title); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <script>
        (function() {
            // --- f8372 confirmation popup ---
            var f8372form = document.getElementById('bpffbs-f8372-form');
            if (f8372form) {
                f8372form.addEventListener('submit', function(e) {
                    var msg = 'Run f8372?\n\n' +
                        'This will re-assign post dates AND change the URL (slug) of ALL ' +
                        'posts on this site. Every post is affected automatically — you do ' +
                        'not need to select any.\n\n' +
                        'This rewrites permalinks and publication dates and cannot be easily undone.\n\n' +
                        'Proceed?';
                    if (!window.confirm(msg)) {
                        e.preventDefault();
                    }
                });
            }

            var table = document.getElementById('bpffbs-table');
            if (!table) return;
            var tbody = table.tBodies[0];

            // --- select all ---
            var selectAll = document.getElementById('bpffbs-select-all');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    tbody.querySelectorAll('.bpffbs-row-check').forEach(function(cb) {
                        cb.checked = selectAll.checked;
                    });
                });
            }

            // --- click-to-sort on any sortable th ---
            // Default visual state: post_date, descending (matches server order).
            var sortState = { col: 0, dir: 'desc' };

            function restripe() {
                Array.prototype.forEach.call(tbody.rows, function(row, idx) {
                    row.style.background = (idx % 2 === 0) ? '#fff' : '#f9f9f9';
                });
            }

            function updateArrows() {
                table.querySelectorAll('th[data-col] .bpffbs-arrow').forEach(function(span) {
                    span.textContent = '';
                });
                var active = table.querySelector('th[data-col="' + sortState.col + '"] .bpffbs-arrow');
                if (active) active.textContent = (sortState.dir === 'asc') ? ' ▲' : ' ▼';
            }

            function sortBy(col, dir) {
                var rows = Array.prototype.slice.call(tbody.rows);
                // data-col 0/1/2 maps to td index 1/2/3 (offset by checkbox column).
                var cellIndex = col + 1;
                rows.sort(function(a, b) {
                    var av = (a.cells[cellIndex].getAttribute('data-sort') || '').toLowerCase();
                    var bv = (b.cells[cellIndex].getAttribute('data-sort') || '').toLowerCase();
                    if (av < bv) return dir === 'asc' ? -1 : 1;
                    if (av > bv) return dir === 'asc' ? 1 : -1;
                    return 0;
                });
                rows.forEach(function(r) { tbody.appendChild(r); });
                restripe();
            }

            table.querySelectorAll('th[data-col]').forEach(function(th) {
                th.addEventListener('click', function() {
                    var col = parseInt(th.getAttribute('data-col'), 10);
                    if (sortState.col === col) {
                        sortState.dir = (sortState.dir === 'asc') ? 'desc' : 'asc';
                    } else {
                        sortState.col = col;
                        sortState.dir = 'asc';
                    }
                    sortBy(sortState.col, sortState.dir);
                    updateArrows();
                });
            });

            updateArrows();
        })();
        </script>
        <?php
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
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites .notice,
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites .notice-error,
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites .notice-warning,
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites .notice-success,
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites .notice-info,
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites .error,
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites .updated,
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites .update-nag,
                body.ruplin-hub-3_page_blog_post_fixer_for_broken_sites #message {
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

    /* =====================================================================
     * f8372 — re-assign post dates and change URLs
     * ===================================================================== */

    /**
     * Main entry point. Runs Part 1 (URL change, all posts) and
     * Part 2 (date re-drip, drip subset) in a single pass.
     *
     * @param int $keep_past Number of most-recent past PUBLISHED posts to leave untouched (dates).
     * @return array Summary counts.
     */
    private function run_f8372($keep_past) {
        $result = array(
            'total'           => 0,
            'urls_changed'    => 0,
            'method_a'        => 0,
            'method_b'        => 0,
            'url_unchanged'   => 0,
            'kept_dates'      => 0,
            'dates_reassigned'=> 0,
            'errors'          => 0,
        );

        $filler    = $this->get_filler_words();      // lookup set: token => true
        $protected = $this->get_protected_tokens();  // lookup set: token => true

        // Working set: real posts only (skip trash / auto-draft / revisions).
        $posts = get_posts(array(
            'post_type'        => 'post',
            'post_status'      => array('publish', 'future', 'draft', 'pending', 'private'),
            'numberposts'      => -1,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'suppress_filters' => true,
        ));
        $result['total'] = count($posts);

        // ---- Part 2: choose which dates to keep, and pre-compute new dates ----
        $now_ts = current_time('timestamp'); // site-local pseudo-timestamp

        // Candidates to keep: published posts whose date is in the past.
        $past_published = array();
        foreach ($posts as $p) {
            if ($p->post_status === 'publish' && strtotime($p->post_date) <= $now_ts) {
                $past_published[] = $p;
            }
        }
        // Keep a RANDOM N of those (not freshest, not oldest). The kept posts
        // still get their URL slug altered in Part 1 — only their date is left as-is.
        shuffle($past_published);
        $keep_ids = array();
        foreach (array_slice($past_published, 0, max(0, (int) $keep_past)) as $p) {
            $keep_ids[$p->ID] = true;
        }
        $result['kept_dates'] = count($keep_ids);

        // Drip set: everything that is NOT a draft and NOT in the keep set.
        $drip = array();
        foreach ($posts as $p) {
            if ($p->post_status !== 'draft' && empty($keep_ids[$p->ID])) {
                $drip[] = $p;
            }
        }
        // Random drip order — the now/future schedule is assigned in random sequence.
        shuffle($drip);

        // Compute new date/status for each drip post.
        //   - first post  => NOW          (status: publish)
        //   - each next   => previous + random 1-6 whole days, at a random
        //                    time of day (h/m/s) => (status: future / scheduled)
        $new_dates = array();
        $cursor_ts = $now_ts;
        foreach (array_values($drip) as $i => $p) {
            if ($i === 0) {
                $cursor_ts = $now_ts;
                $date_str  = gmdate('Y-m-d H:i:s', $cursor_ts);
                $status    = 'publish';
            } else {
                $days      = mt_rand(1, 6);
                $cursor_ts = $cursor_ts + ($days * DAY_IN_SECONDS);
                $midnight  = strtotime(gmdate('Y-m-d', $cursor_ts) . ' 00:00:00 UTC');
                $cursor_ts = $midnight + (mt_rand(0, 23) * HOUR_IN_SECONDS)
                                       + (mt_rand(0, 59) * MINUTE_IN_SECONDS)
                                       + mt_rand(0, 59);
                $date_str  = gmdate('Y-m-d H:i:s', $cursor_ts);
                $status    = 'future';
            }
            $new_dates[$p->ID] = array(
                'date'   => $date_str,
                'gmt'    => get_gmt_from_date($date_str),
                'status' => $status,
            );
        }

        // ---- Apply per post: Part 1 (all) + Part 2 (drip set) ----
        foreach ($posts as $p) {
            $args = array('ID' => $p->ID);

            // Part 1 — change URL slug for every post.
            $slug = $this->modify_slug($p->post_name, $protected, $filler);
            if ($slug !== null && $slug['slug'] !== '' && $slug['slug'] !== $p->post_name) {
                $args['post_name'] = $slug['slug'];
                $result['urls_changed']++;
                if ($slug['method'] === 'A') {
                    $result['method_a']++;
                } else {
                    $result['method_b']++;
                }
            } else {
                $result['url_unchanged']++;
            }

            // Part 2 — re-drip date for drip-set posts.
            if (isset($new_dates[$p->ID])) {
                $nd = $new_dates[$p->ID];
                $args['post_date']     = $nd['date'];
                $args['post_date_gmt'] = $nd['gmt'];
                $args['post_status']   = $nd['status'];
                $args['edit_date']     = true; // required so wp_update_post honors a new post_date
                $result['dates_reassigned']++;
            }

            if (count($args) > 1) {
                $updated = wp_update_post($args, true);
                if (is_wp_error($updated)) {
                    $result['errors']++;
                }
            }
        }

        return $result;
    }

    /**
     * Decide a slightly-shortened slug for a post.
     *
     * METHOD A (preferred): if the slug contains any filler word, remove one.
     * METHOD B (fallback): otherwise remove a random NON-protected word.
     *
     * @return array|null array('slug' => string, 'method' => 'A'|'B') or null if no change is possible.
     */
    private function modify_slug($slug, $protected, $filler) {
        if (empty($slug)) {
            return null;
        }
        $words = explode('-', $slug);
        if (count($words) <= 1) {
            return null; // single-word slug — nothing safe to remove
        }

        // METHOD A — remove a filler word if present.
        $filler_positions = array();
        foreach ($words as $idx => $w) {
            if (isset($filler[strtolower($w)])) {
                $filler_positions[] = $idx;
            }
        }
        if (!empty($filler_positions)) {
            $idx = $filler_positions[array_rand($filler_positions)];
            unset($words[$idx]);
            $new = implode('-', $words);
            return ($new !== '') ? array('slug' => $new, 'method' => 'A') : null;
        }

        // METHOD B — remove a random word that is NOT protected.
        $removable = array();
        foreach ($words as $idx => $w) {
            if (!isset($protected[strtolower($w)])) {
                $removable[] = $idx;
            }
        }
        if (!empty($removable)) {
            $idx = $removable[array_rand($removable)];
            unset($words[$idx]);
            $new = implode('-', $words);
            return ($new !== '') ? array('slug' => $new, 'method' => 'B') : null;
        }

        return null; // every word is protected — leave slug unchanged
    }

    /**
     * Filler words as a lookup set (token => true), lowercased.
     */
    private function get_filler_words() {
        $words = require __DIR__ . '/filler-words.php';
        $set = array();
        foreach ((array) $words as $w) {
            $set[strtolower($w)] = true;
        }
        return $set;
    }

    /**
     * Protected tokens as a lookup set (token => true): US states + top
     * English-speaking countries (from protected-words.php) merged with the
     * single zen_sitespren row's driggs_brand_name / driggs_city /
     * driggs_state_code, all tokenized.
     */
    private function get_protected_tokens() {
        $lists  = require __DIR__ . '/protected-words.php';
        $tokens = array();

        foreach (array('us_states', 'countries') as $key) {
            if (!empty($lists[$key]) && is_array($lists[$key])) {
                foreach ($lists[$key] as $name) {
                    foreach ($this->tokenize($name) as $t) {
                        $tokens[$t] = true;
                    }
                }
            }
        }

        // The single zen_sitespren row (brand/city/state code).
        global $wpdb;
        $table  = $wpdb->prefix . 'zen_sitespren';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists === $table) {
            $row = $wpdb->get_row(
                "SELECT driggs_brand_name, driggs_city, driggs_state_code FROM `{$table}` ORDER BY wppma_id ASC LIMIT 1",
                ARRAY_A
            );
            if ($row) {
                foreach (array('driggs_brand_name', 'driggs_city', 'driggs_state_code') as $col) {
                    if (!empty($row[$col])) {
                        foreach ($this->tokenize($row[$col]) as $t) {
                            $tokens[$t] = true;
                        }
                    }
                }
            }
        }

        return $tokens;
    }

    /**
     * Lowercase a string and split it into alphanumeric tokens.
     */
    private function tokenize($str) {
        $parts = preg_split('/[^a-z0-9]+/', strtolower((string) $str), -1, PREG_SPLIT_NO_EMPTY);
        return is_array($parts) ? $parts : array();
    }
}

Ruplin_Blog_Post_Fixer_For_Broken_Sites::get_instance();
