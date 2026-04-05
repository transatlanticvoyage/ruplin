<?php
/**
 * Warbler Migrator - Bootstrap
 *
 * Entry point loaded by ruplin.php via a single require_once.
 * Loads all sub-classes and initialises the admin page.
 *
 * Usage in ruplin.php load_dependencies():
 *   require_once SNEFURU_PLUGIN_PATH . 'warbler-migrator/class-warbler-migrator.php';
 */

if (!defined('ABSPATH')) exit;

// Define the backups directory path (wp-content/warbler-backups/)
if (!defined('WARBLER_BACKUPS_DIR')) {
    define('WARBLER_BACKUPS_DIR', WP_CONTENT_DIR . '/warbler-backups');
}

// Auto-create the backups folder on every site where this plugin is active
add_action('plugins_loaded', function() {
    if (!is_dir(WARBLER_BACKUPS_DIR)) {
        wp_mkdir_p(WARBLER_BACKUPS_DIR);
        // Prevent directory listing
        $index = WARBLER_BACKUPS_DIR . '/index.php';
        if (!file_exists($index)) {
            file_put_contents($index, '<?php // Silence is golden.');
        }
    }
}, 20);

// Load sub-classes
require_once __DIR__ . '/class-warbler-search-replace.php';
require_once __DIR__ . '/class-warbler-db-dump.php';
require_once __DIR__ . '/class-warbler-db-restore.php';
require_once __DIR__ . '/class-warbler-file-collector.php';
require_once __DIR__ . '/class-warbler-exporter.php';
require_once __DIR__ . '/class-warbler-importer.php';
require_once __DIR__ . '/class-warbler-admin-page.php';

// Boot the admin page (hooks registered inside constructor)
new Warbler_Admin_Page();
