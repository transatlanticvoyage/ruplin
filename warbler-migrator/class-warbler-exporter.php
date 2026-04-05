<?php
/**
 * Warbler Migrator - Exporter
 * Builds a .warbler archive (renamed zip) containing the DB dump + selected files.
 */

if (!defined('ABSPATH')) exit;

class Warbler_Exporter {

    /**
     * Build the .warbler file and return its full path on success, or WP_Error.
     *
     * @param array $options  include_themes, include_plugins, include_uploads (booleans)
     * @return string|WP_Error  Path to generated .warbler file
     */
    public static function export(array $options = []) {
        set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $options = array_merge([
            'include_themes'  => true,
            'include_plugins' => true,
            'include_uploads' => true,
        ], $options);

        // Build filename
        $host     = parse_url(get_option('siteurl'), PHP_URL_HOST);
        $slug     = sanitize_file_name($host);
        $filename = $slug . '-' . date('Y-m-d-His') . '.warbler';

        // Temp working directory
        $tmp_dir = trailingslashit(sys_get_temp_dir()) . 'warbler_export_' . uniqid();
        wp_mkdir_p($tmp_dir);

        $zip_path = $tmp_dir . '/' . $filename;

        // Open zip
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            self::cleanup($tmp_dir);
            return new WP_Error('zip_create', 'Could not create zip archive at: ' . $zip_path);
        }

        // 1. Database dump
        $sql_file = $tmp_dir . '/database.sql';
        $result   = Warbler_DB_Dump::dump_to_file($sql_file);
        if (is_wp_error($result)) {
            $zip->close();
            self::cleanup($tmp_dir);
            return $result;
        }
        $zip->addFile($sql_file, 'database.sql');

        // 2. Files
        Warbler_File_Collector::add_to_zip($zip, $options);

        // 3. Manifest
        $includes = [];
        if (!empty($options['include_themes']))  $includes[] = 'themes';
        if (!empty($options['include_plugins'])) $includes[] = 'plugins';
        if (!empty($options['include_uploads'])) $includes[] = 'uploads';

        $manifest = [
            'warbler_version' => '1.0',
            'created_at'      => date('c'),
            'source_url'      => get_option('siteurl'),
            'wp_version'      => get_bloginfo('version'),
            'table_prefix'    => $GLOBALS['wpdb']->prefix,
            'includes'        => $includes,
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip->close();

        // Remove temp SQL file (it's already inside the zip)
        @unlink($sql_file);

        return $zip_path;
    }

    // -------------------------------------------------------------------------

    private static function cleanup($dir) {
        if (!is_dir($dir)) return;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $f) {
            $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
        }
        rmdir($dir);
    }
}
