<?php
/**
 * Warbler Migrator - Importer
 * Extracts a .warbler file, restores DB (full wipe), runs search-replace, copies files.
 */

if (!defined('ABSPATH')) exit;

class Warbler_Importer {

    /**
     * Import a .warbler file.
     *
     * @param string $zip_path    Full path to the uploaded .warbler file
     * @param string $target_url  The destination site URL (e.g. https://pretzel.ksit.me)
     * @param array  $options     restore_files (bool)
     * @return true|WP_Error
     */
    public static function import($zip_path, $target_url, array $options = []) {
        set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $options = array_merge(['restore_files' => true], $options);

        // 1. Extract archive
        $tmp_dir = trailingslashit(sys_get_temp_dir()) . 'warbler_import_' . uniqid();
        wp_mkdir_p($tmp_dir);

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) {
            self::cleanup($tmp_dir);
            return new WP_Error('zip_open', 'Could not open .warbler file. Is it a valid archive?');
        }
        $zip->extractTo($tmp_dir);
        $zip->close();

        // 2. Read manifest
        $manifest_path = $tmp_dir . '/manifest.json';
        if (!file_exists($manifest_path)) {
            self::cleanup($tmp_dir);
            return new WP_Error('no_manifest', 'Missing manifest.json — this does not appear to be a valid .warbler file.');
        }

        $manifest   = json_decode(file_get_contents($manifest_path), true);
        $source_url = isset($manifest['source_url']) ? rtrim($manifest['source_url'], '/') : '';
        $target_url = rtrim($target_url, '/');

        if (empty($source_url)) {
            self::cleanup($tmp_dir);
            return new WP_Error('no_source_url', 'manifest.json is missing source_url.');
        }

        // 3. Restore database (full wipe + restore)
        $sql_path = $tmp_dir . '/database.sql';
        if (!file_exists($sql_path)) {
            self::cleanup($tmp_dir);
            return new WP_Error('no_sql', 'Missing database.sql inside the archive.');
        }

        $result = Warbler_DB_Restore::restore_from_file($sql_path);
        if (is_wp_error($result)) {
            self::cleanup($tmp_dir);
            return $result;
        }

        // 4. Search-replace URLs — all protocol variants
        if ($source_url !== $target_url) {
            // Build all variants for source and target
            $source_http     = str_replace('https://', 'http://',  $source_url);
            $source_https    = str_replace('http://',  'https://', $source_url);
            $target_http     = str_replace('https://', 'http://',  $target_url);
            $target_https    = str_replace('http://',  'https://', $target_url);

            // Protocol-relative (e.g. //saltwater.local)
            $source_relative = preg_replace('#^https?://#', '//', $source_url);
            $target_relative = preg_replace('#^https?://#', '//', $target_url);

            // Main replacement (exact protocol match)
            Warbler_Search_Replace::replace_in_database($source_url, $target_url);

            // Opposite protocol variant
            if ($source_http !== $source_url) {
                Warbler_Search_Replace::replace_in_database($source_http, $target_http);
            }
            if ($source_https !== $source_url) {
                Warbler_Search_Replace::replace_in_database($source_https, $target_https);
            }

            // Protocol-relative variant (common in serialized theme/plugin data)
            if ($source_relative !== $source_url) {
                Warbler_Search_Replace::replace_in_database($source_relative, $target_relative);
            }
        }

        // 5. Restore files (optional)
        if (!empty($options['restore_files'])) {
            $files_dir = $tmp_dir . '/files';
            if (is_dir($files_dir)) {
                Warbler_File_Collector::restore_files($files_dir);
            }
        }

        // 6. Cleanup
        self::cleanup($tmp_dir);

        // 7. Flush caches and rewrite rules so the site reflects the new data immediately
        wp_cache_flush();
        flush_rewrite_rules(true);

        return true;
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
