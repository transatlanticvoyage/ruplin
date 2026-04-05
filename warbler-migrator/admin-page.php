<?php
/**
 * Warbler Migrator - Admin Page Template
 */

if (!defined('ABSPATH')) exit;

$active_tab    = isset($_GET['warbler_tab']) ? sanitize_key($_GET['warbler_tab']) : 'export';
$current_url   = get_option('siteurl');
$notice        = '';
$notice_type   = 'success';

if (isset($_GET['warbler_imported']) && $_GET['warbler_imported'] === '1') {
    $notice = 'Import completed successfully. Search-replace has been run automatically.';
}
if (isset($_GET['warbler_error'])) {
    $notice      = urldecode($_GET['warbler_error']);
    $notice_type = 'error';
}
?>

<style>
    .warbler-wrap { max-width: 780px; }
    .warbler-tabs { display: flex; gap: 0; margin-bottom: 0; border-bottom: 1px solid #c3c4c7; }
    .warbler-tab-btn {
        padding: 10px 22px;
        background: #f0f0f1;
        border: 1px solid #c3c4c7;
        border-bottom: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        color: #50575e;
        text-decoration: none;
        margin-right: 4px;
        border-radius: 3px 3px 0 0;
    }
    .warbler-tab-btn.active { background: #fff; color: #1d2327; border-bottom: 1px solid #fff; margin-bottom: -1px; }
    .warbler-panel { background: #fff; border: 1px solid #c3c4c7; border-top: none; padding: 28px 28px 32px; }
    .warbler-section-title { font-size: 15px; font-weight: 600; color: #1d2327; margin: 0 0 18px; }
    .warbler-field { margin-bottom: 18px; }
    .warbler-field label { display: block; font-weight: 500; margin-bottom: 6px; color: #1d2327; }
    .warbler-field input[type="text"],
    .warbler-field input[type="url"] { width: 100%; max-width: 480px; }
    .warbler-checkbox-row { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 14px; }
    .warbler-checkbox-row input { margin: 0; }
    .warbler-note { font-size: 12px; color: #646970; margin-top: 4px; }
    .warbler-divider { border: none; border-top: 1px solid #f0f0f1; margin: 24px 0; }
    .warbler-danger-box { background: #fff8e5; border: 1px solid #f0c33c; border-radius: 4px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #614200; }
    .warbler-btn-primary { background: #2271b1; color: #fff; border: none; padding: 9px 20px; border-radius: 3px; font-size: 14px; font-weight: 600; cursor: pointer; }
    .warbler-btn-primary:hover { background: #135e96; }
    .warbler-btn-danger { background: #d63638; color: #fff; border: none; padding: 9px 20px; border-radius: 3px; font-size: 14px; font-weight: 600; cursor: pointer; }
    .warbler-btn-danger:hover { background: #b32d2e; }
    .warbler-notice { padding: 12px 16px; margin-bottom: 20px; border-radius: 3px; font-size: 14px; }
    .warbler-notice.success { background: #edfaef; border: 1px solid #68de7c; color: #1d5c26; }
    .warbler-notice.error   { background: #fce8e8; border: 1px solid #f86368; color: #8a1f1f; }
</style>

<div class="wrap warbler-wrap">
    <h1 style="margin-bottom:20px;">Warbler Migrator</h1>

    <?php if ($notice): ?>
    <div class="warbler-notice <?php echo esc_attr($notice_type); ?>">
        <?php echo esc_html($notice); ?>
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="warbler-tabs">
        <a href="?page=warbler-migrator&warbler_tab=export"
           class="warbler-tab-btn <?php echo $active_tab === 'export' ? 'active' : ''; ?>">
            Export
        </a>
        <a href="?page=warbler-migrator&warbler_tab=import"
           class="warbler-tab-btn <?php echo $active_tab === 'import' ? 'active' : ''; ?>">
            Import
        </a>
    </div>

    <!-- ======================================================
         EXPORT TAB
    ====================================================== -->
    <?php if ($active_tab === 'export'): ?>
    <div class="warbler-panel">
        <p class="warbler-section-title">Export this site as a .warbler file</p>
        <p style="font-size:13px;color:#646970;margin-bottom:20px;">
            Creates a single archive containing the full database and selected files.
            Source URL recorded: <strong><?php echo esc_html($current_url); ?></strong>
        </p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('warbler_export', 'warbler_nonce'); ?>
            <input type="hidden" name="action" value="warbler_export">

            <p class="warbler-section-title" style="margin-top:4px;">Include in archive</p>

            <div class="warbler-checkbox-row">
                <input type="checkbox" name="include_themes" id="inc_themes" value="1" checked>
                <label for="inc_themes">Theme — <code>staircase</code></label>
            </div>
            <div class="warbler-checkbox-row">
                <input type="checkbox" name="include_plugins" id="inc_plugins" value="1" checked>
                <label for="inc_plugins">Plugins — <code>ruplin, grove, axiom, aardvark</code></label>
            </div>
            <div class="warbler-checkbox-row">
                <input type="checkbox" name="include_uploads" id="inc_uploads" value="1" checked>
                <label for="inc_uploads">Uploads — <code>wp-content/uploads</code></label>
                <span class="warbler-note">(uncheck to reduce file size if uploads are large)</span>
            </div>

            <hr class="warbler-divider">

            <button type="submit" class="warbler-btn-primary">
                Download .warbler
            </button>
            <p class="warbler-note" style="margin-top:10px;">
                Generation may take a minute on larger sites. The file will download automatically when ready.
            </p>
        </form>
    </div>

    <!-- ======================================================
         IMPORT TAB
    ====================================================== -->
    <?php else: ?>
    <div class="warbler-panel">
        <p class="warbler-section-title">Import a .warbler file into this site</p>

        <div class="warbler-danger-box">
            <strong>Warning:</strong> Importing will <strong>completely wipe the current database</strong> and replace it with the contents of the archive. This cannot be undone. Make sure you want to do this.
        </div>

        <form method="post"
              action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
              enctype="multipart/form-data">
            <?php wp_nonce_field('warbler_import', 'warbler_nonce'); ?>
            <input type="hidden" name="action" value="warbler_import">

            <div class="warbler-field">
                <label for="warbler_file">Select .warbler file</label>
                <input type="file" name="warbler_file" id="warbler_file" accept=".warbler,.zip" required>
            </div>

            <div class="warbler-field">
                <label for="warbler_target_url">Destination URL (this site)</label>
                <input type="url" name="warbler_target_url" id="warbler_target_url"
                       value="<?php echo esc_attr($current_url); ?>" required>
                <p class="warbler-note">
                    The source URL is read automatically from manifest.json inside the archive.
                    Search-replace will run from source → this URL.
                </p>
            </div>

            <div class="warbler-checkbox-row" style="margin-bottom:20px;">
                <input type="checkbox" name="restore_files" id="restore_files" value="1" checked>
                <label for="restore_files">Also restore files (themes, plugins, uploads) from archive</label>
            </div>

            <hr class="warbler-divider">

            <button type="submit" class="warbler-btn-danger"
                    onclick="return confirm('Are you sure? This will wipe the entire database on this site and cannot be undone.');">
                Restore from .warbler
            </button>
        </form>
    </div>
    <?php endif; ?>

</div>
