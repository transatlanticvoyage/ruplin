<?php
/**
 * Warbler Migrator - Search & Replace
 * Handles serialization-aware URL replacement across all DB tables.
 */

if (!defined('ABSPATH')) exit;

class Warbler_Search_Replace {

    /**
     * Run search-replace across all wp_ tables.
     * Returns array of [ table => rows_updated ] or WP_Error.
     */
    public static function replace_in_database($from, $to) {
        global $wpdb;

        $tables = $wpdb->get_col("SHOW TABLES LIKE '" . esc_sql($wpdb->prefix) . "%'");
        $report = [];

        foreach ($tables as $table) {
            $columns    = $wpdb->get_results("DESCRIBE `{$table}`");
            $primary    = null;
            $text_cols  = [];

            foreach ($columns as $col) {
                if ($col->Key === 'PRI') {
                    $primary = $col->Field;
                }
                $type = strtolower($col->Type);
                if (
                    strpos($type, 'char')  !== false ||
                    strpos($type, 'text')  !== false ||
                    strpos($type, 'blob')  !== false ||
                    strpos($type, 'json')  !== false ||
                    strpos($type, 'mediumtext') !== false ||
                    strpos($type, 'longtext')   !== false
                ) {
                    $text_cols[] = $col->Field;
                }
            }

            if (empty($text_cols) || !$primary) {
                $report[$table] = 0;
                continue;
            }

            $rows  = $wpdb->get_results("SELECT * FROM `{$table}`", ARRAY_A);
            $count = 0;

            foreach ($rows as $row) {
                $updates = [];

                foreach ($text_cols as $col) {
                    if (!isset($row[$col]) || $row[$col] === null || $row[$col] === '') continue;

                    $original = $row[$col];
                    $replaced = self::replace_value($from, $to, $original);

                    if ($replaced !== $original) {
                        $updates[$col] = $replaced;
                    }
                }

                if (!empty($updates)) {
                    $wpdb->update($table, $updates, [$primary => $row[$primary]]);
                    $count++;
                }
            }

            $report[$table] = $count;
        }

        return $report;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private static function replace_value($from, $to, $value) {
        if (!is_string($value)) return $value;

        // Attempt to unserialize
        if (self::is_serialized($value)) {
            $data = @unserialize($value);
            if ($data === false && $value !== 'b:0;') {
                // Unserialize failed — fall through to plain string replace
                return str_replace($from, $to, $value);
            }
            $replaced = self::replace_recursive($from, $to, $data);
            return serialize($replaced);
        }

        // Attempt JSON
        if (self::looks_like_json($value)) {
            $data = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $replaced = self::replace_recursive($from, $to, $data);
                return json_encode($replaced, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }

        // Plain string
        return str_replace($from, $to, $value);
    }

    private static function replace_recursive($from, $to, $data) {
        if (is_array($data)) {
            $out = [];
            foreach ($data as $key => $val) {
                $new_key      = is_string($key) ? str_replace($from, $to, $key) : $key;
                $out[$new_key] = self::replace_recursive($from, $to, $val);
            }
            return $out;
        }
        if (is_string($data)) {
            // Nested serialized strings (common in WP options)
            if (self::is_serialized($data)) {
                $inner = @unserialize($data);
                if ($inner === false && $data !== 'b:0;') {
                    return str_replace($from, $to, $data);
                }
                $replaced = self::replace_recursive($from, $to, $inner);
                return serialize($replaced);
            }
            return str_replace($from, $to, $data);
        }
        return $data;
    }

    private static function is_serialized($value) {
        if (!is_string($value)) return false;
        $value = trim($value);
        if ($value === 'N;') return true;
        if (strlen($value) < 4) return false;
        if ($value[1] !== ':') return false;
        $end = substr($value, -1);
        if ($end !== ';' && $end !== '}') return false;
        $token = $value[0];
        switch ($token) {
            case 's':
            case 'i':
            case 'd':
            case 'b':
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $value);
            case 'C':
                return (bool) preg_match('/^C:[0-9]+:"[^"]*":[0-9]+:\{/', $value);
        }
        return false;
    }

    private static function looks_like_json($value) {
        $v = ltrim($value);
        return isset($v[0]) && ($v[0] === '{' || $v[0] === '[');
    }
}
