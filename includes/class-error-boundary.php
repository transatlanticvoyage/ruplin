<?php

/**
 * Snefuru Error Boundary Class
 * Provides safe execution boundaries to prevent cascade failures
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snefuru_Error_Boundary {
    
    /**
     * Execute a callback safely with error handling
     * 
     * @param callable $callback The function to execute
     * @param callable|null $error_handler Custom error handler
     * @return mixed|null Result of callback or null on error
     */
    public static function guard($callback, $error_handler = null) {
        try {
            return $callback();
        } catch (Throwable $e) {
            error_log('Snefuru Error Boundary: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            if ($error_handler && is_callable($error_handler)) {
                $error_handler($e);
            } else {
                self::default_error_handler($e);
            }
            
            return null;
        }
    }
    
    /**
     * Default error handler - shows admin notice
     * 
     * @param Throwable $error The caught error
     */
    private static function default_error_handler($error) {
        add_action('admin_notices', function() use ($error) {
            echo '<div class="notice notice-warning"><p>';
            echo '<strong>Snefuru:</strong> A feature failed to load but the plugin is still active. ';
            echo 'Error: ' . esc_html($error->getMessage());
            echo '</p></div>';
        });
    }
    
    /**
     * Execute multiple callbacks safely
     * 
     * @param array $callbacks Array of callables to execute
     * @param callable|null $error_handler Custom error handler for all
     * @return array Results array (null for failed callbacks)
     */
    public static function guard_multiple($callbacks, $error_handler = null) {
        $results = array();
        
        foreach ($callbacks as $key => $callback) {
            $results[$key] = self::guard($callback, $error_handler);
        }
        
        return $results;
    }
    
    /**
     * Safe class instantiation with error boundary
     * 
     * @param string $class_name The class to instantiate
     * @param array $args Constructor arguments
     * @param callable|null $error_handler Custom error handler
     * @return object|null Instance or null on error
     */
    public static function safe_instantiate($class_name, $args = array(), $error_handler = null) {
        return self::guard(function() use ($class_name, $args) {
            if (!class_exists($class_name)) {
                throw new Exception("Class {$class_name} does not exist");
            }
            
            if (empty($args)) {
                return new $class_name();
            } else {
                $reflection = new ReflectionClass($class_name);
                return $reflection->newInstanceArgs($args);
            }
        }, $error_handler);
    }
    
    /**
     * Check if a class exists and can be safely instantiated
     * 
     * @param string $class_name The class to check
     * @return bool True if class exists and is instantiable
     */
    public static function can_instantiate($class_name) {
        if (!class_exists($class_name)) {
            return false;
        }
        
        try {
            $reflection = new ReflectionClass($class_name);
            return $reflection->isInstantiable();
        } catch (ReflectionException $e) {
            return false;
        }
    }
}