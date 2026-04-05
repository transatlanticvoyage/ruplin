<?php
/**
 * Warbler Migrator - DB Dump
 * Dumps all wp_ tables to a SQL file using $wpdb (no exec/mysqldump required).
 */

if (!defined('ABSPATH')) exit;

class Warbler_DB_Dump {

    /**
     * Dump all wp_ tables to $filepath.
     * Returns true on success or WP_Error on failure.
     */
    public static function dump_to_file($filepath) {
        global $wpdb;

        $handle = fopen($filepath, 'w');
        if (!$handle) {
            return new WP_Error('file_open', 'Could not open file for writing: ' . $filepath);
        }

        fwrite($handle, "-- Warbler Migrator DB Dump\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Source: " . get_option('siteurl') . "\n");
        fwrite($handle, "-- Prefix: " . $wpdb->prefix . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        $tables = $wpdb->get_col("SHOW TABLES LIKE '" . esc_sql($wpdb->prefix) . "%'");

        foreach ($tables as $table) {
            // --- DROP + CREATE -------------------------------------------------
            $create_row = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);
            fwrite($handle, "-- Table: {$table}\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $create_row[1] . ";\n\n");

            // --- Rows ----------------------------------------------------------
            $rows = $wpdb->get_results("SELECT * FROM `{$table}`", ARRAY_N);

            if (!empty($rows)) {
                $col_names = $wpdb->get_col("DESCRIBE `{$table}`", 0);
                $col_list  = '`' . implode('`, `', $col_names) . '`';

                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $val) {
                        if ($val === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . self::escape($val) . "'";
                        }
                    }
                    fwrite($handle, "INSERT INTO `{$table}` ({$col_list}) VALUES (" . implode(', ', $values) . ");\n");
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return true;
    }

    /**
     * Escape a value for safe inclusion in a SQL INSERT statement.
     */
    private static function escape($value) {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace("'",  "\\'",  $value);
        $value = str_replace("\n", "\\n",  $value);
        $value = str_replace("\r", "\\r",  $value);
        $value = str_replace("\0", "\\0",  $value);
        return $value;
    }
}
