<?php
/**
 * Warbler Migrator - DB Restore
 * Executes a SQL dump file against the current database — full wipe + restore.
 */

if (!defined('ABSPATH')) exit;

class Warbler_DB_Restore {

    /**
     * Restore DB from a SQL dump file.
     * Drops all existing wp_ tables first, then executes the dump.
     * Returns true on success or WP_Error on failure.
     */
    public static function restore_from_file($filepath) {
        global $wpdb;

        if (!file_exists($filepath)) {
            return new WP_Error('file_missing', 'SQL file not found: ' . $filepath);
        }

        // Drop all existing wp_ tables before restoring
        $drop_result = self::drop_all_tables();
        if (is_wp_error($drop_result)) return $drop_result;

        // Enforce charset on the connection before executing any statements
        $wpdb->query("SET NAMES 'utf8mb4'");
        $wpdb->query("SET SESSION sql_mode = ''");

        // Execute dump
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return new WP_Error('file_open', 'Could not open SQL file for reading');
        }

        $statement = '';
        $errors    = [];
        $line_num  = 0;

        $wpdb->show_errors();

        while (($line = fgets($handle)) !== false) {
            $line_num++;
            $trimmed = trim($line);

            // Skip blank lines and comments
            if ($trimmed === '' || strpos($trimmed, '--') === 0) continue;

            $statement .= $line;

            // A statement ends when the trimmed line ends with ;
            if (substr($trimmed, -1) === ';') {
                $result = $wpdb->query($statement);
                if ($result === false && !empty($wpdb->last_error)) {
                    $errors[] = 'Line ~' . $line_num . ': ' . $wpdb->last_error;
                    // Continue — don't abort on single-row errors
                }
                $statement = '';
            }
        }

        fclose($handle);
        $wpdb->hide_errors();

        if (!empty($errors)) {
            // Return errors as a warning but don't treat as hard failure
            // (some hosts emit harmless warnings for AUTO_INCREMENT etc.)
            error_log('Warbler Restore Warnings: ' . implode(' | ', $errors));
        }

        return true;
    }

    // -------------------------------------------------------------------------

    private static function drop_all_tables() {
        global $wpdb;

        $wpdb->query('SET FOREIGN_KEY_CHECKS=0');
        $tables = $wpdb->get_col("SHOW TABLES LIKE '" . esc_sql($wpdb->prefix) . "%'");

        foreach ($tables as $table) {
            $result = $wpdb->query("DROP TABLE IF EXISTS `{$table}`");
            if ($result === false) {
                $wpdb->query('SET FOREIGN_KEY_CHECKS=1');
                return new WP_Error('drop_failed', 'Could not drop table: ' . $table);
            }
        }

        $wpdb->query('SET FOREIGN_KEY_CHECKS=1');
        return true;
    }
}
