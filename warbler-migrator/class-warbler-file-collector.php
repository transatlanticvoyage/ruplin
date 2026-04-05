<?php
/**
 * Warbler Migrator - File Collector
 * Adds theme, plugin, and upload directories into a ZipArchive.
 */

if (!defined('ABSPATH')) exit;

class Warbler_File_Collector {

    /** Directories/files to always skip when zipping */
    private static $skip_segments = ['.git', 'node_modules', '.DS_Store', '__MACOSX'];

    /**
     * Add selected content into $zip under the 'files/' prefix.
     *
     * @param ZipArchive $zip
     * @param array      $options  Keys: include_themes, include_plugins, include_uploads
     */
    public static function add_to_zip(ZipArchive $zip, array $options) {
        $content_dir = WP_CONTENT_DIR;

        if (!empty($options['include_themes'])) {
            $theme_path = $content_dir . '/themes/staircase';
            if (is_dir($theme_path)) {
                self::add_dir($zip, $theme_path, 'files/themes/staircase');
            }
        }

        if (!empty($options['include_plugins'])) {
            foreach (['ruplin', 'grove', 'axiom', 'aardvark'] as $plugin) {
                $plugin_path = $content_dir . '/plugins/' . $plugin;
                if (is_dir($plugin_path)) {
                    self::add_dir($zip, $plugin_path, 'files/plugins/' . $plugin);
                }
            }
        }

        if (!empty($options['include_uploads'])) {
            $uploads_path = $content_dir . '/uploads';
            if (is_dir($uploads_path)) {
                self::add_dir($zip, $uploads_path, 'files/uploads');
            }
        }
    }

    /**
     * Recursively add $src_dir into $zip under $zip_prefix.
     */
    private static function add_dir(ZipArchive $zip, $src_dir, $zip_prefix) {
        $src_dir = rtrim($src_dir, '/\\');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $abs_path = $item->getPathname();

            if (self::should_skip($abs_path)) continue;

            $relative = substr($abs_path, strlen($src_dir) + 1);
            $relative = str_replace('\\', '/', $relative);
            $zip_path = $zip_prefix . '/' . $relative;

            if ($item->isDir()) {
                $zip->addEmptyDir($zip_path);
            } else {
                $zip->addFile($abs_path, $zip_path);
            }
        }
    }

    private static function should_skip($path) {
        $path = str_replace('\\', '/', $path);
        foreach (self::$skip_segments as $seg) {
            if (strpos($path, '/' . $seg) !== false) return true;
        }
        return false;
    }

    // -------------------------------------------------------------------------
    // Restore helpers (used by importer)
    // -------------------------------------------------------------------------

    /**
     * Copy files from extracted $files_dir back into wp-content.
     */
    public static function restore_files($files_dir) {
        $content_dir = WP_CONTENT_DIR;

        $map = [
            $files_dir . '/themes'  => $content_dir . '/themes',
            $files_dir . '/plugins' => $content_dir . '/plugins',
            $files_dir . '/uploads' => $content_dir . '/uploads',
        ];

        foreach ($map as $src => $dst) {
            if (is_dir($src)) {
                self::copy_dir($src, $dst);
            }
        }
    }

    private static function copy_dir($src, $dst) {
        if (!is_dir($dst)) wp_mkdir_p($dst);

        $src = rtrim($src, '/\\');
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($src));
            $target   = $dst . $relative;

            if ($item->isDir()) {
                if (!is_dir($target)) mkdir($target, 0755, true);
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }
}
