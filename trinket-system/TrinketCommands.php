<?php
/**
 * TrinketCommands
 * Maps trinket command strings to render callbacks.
 * Each command returns HTML output via output buffering so the
 * original render functions (which echo) can be reused as-is.
 *
 * @package Ruplin
 * @subpackage TrinketSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class TrinketCommands {

    /**
     * Registry of command string => callable
     */
    private static $commands = null;

    /**
     * Build the registry on first use.
     */
    private static function init() {
        if (self::$commands !== null) {
            return;
        }

        self::$commands = array(
            'trinket_get_avg_rating_box' => array(__CLASS__, 'render_avg_rating_box'),
        );
    }

    /**
     * Execute a command and return its HTML.
     *
     * @param string $command The command string stored in trinketNcommand.
     * @return string|null Rendered HTML, or null if command not found.
     */
    public static function execute($command) {
        self::init();

        $command = trim($command);
        if (empty($command) || !isset(self::$commands[$command])) {
            return null;
        }

        ob_start();
        call_user_func(self::$commands[$command]);
        return ob_get_clean();
    }

    /**
     * Check whether a command string is registered.
     */
    public static function exists($command) {
        self::init();
        return isset(self::$commands[trim($command)]);
    }

    // ------------------------------------------------------------------
    // Command render callbacks
    // ------------------------------------------------------------------

    /**
     * trinket_get_avg_rating_box
     * Delegates to the staircase theme function so changes to
     * the avg rating box are automatically reflected here.
     */
    private static function render_avg_rating_box() {
        if (function_exists('staircase_render_avg_rating_box')) {
            staircase_render_avg_rating_box();
        }
    }
}
