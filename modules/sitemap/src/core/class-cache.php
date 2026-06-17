<?php

/**
 * Cache Manager Class
 * 
 * Related Files:
 * - includes/helpers.php
 * - core/class-builder.php
 * - includes/hooks.php
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

class SSG_Cache
{

    private static $instance = null;
    private static $memory_cache = array();

    //-------------------------------
    // Singleton Instance
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //-------------------------------
    // Constructor
    private function __construct()
    {
        // Nothing here
    }

    //-------------------------------
    // Get Cached Data (3 Levels)
    public function get($key)
    {
        // Level 1: PHP Memory (fastest)
        if (isset(self::$memory_cache[$key])) {
            return self::$memory_cache[$key];
        }

        // Level 2: WordPress Transients
        $data = get_transient($key);

        if (false !== $data) {
            // Validate cache with settings hash
            if ($this->is_cache_valid($data)) {
                // Store in memory for this request
                self::$memory_cache[$key] = $data;
                return $data;
            } else {
                // Invalid cache, delete it
                delete_transient($key);
                return false;
            }
        }

        return false;
    }

    //-------------------------------
    // Set Cache Data
    public function set($key, $data, $expiration = null)
    {
        if (null === $expiration) {
            $settings = ssg_get_settings();
            $expiration = isset($settings['cache_duration']) ? $settings['cache_duration'] : 24;
            $expiration = absint($expiration) * HOUR_IN_SECONDS;
        }

        // Add settings hash for validation
        if (is_array($data)) {
            $data['_settings_hash'] = ssg_get_settings_hash();
        }

        // Level 1: Store in memory
        self::$memory_cache[$key] = $data;

        // Level 2: Store in transients
        return set_transient($key, $data, $expiration);
    }

    //-------------------------------
    // Delete Cached Data
    public function delete($key)
    {
        // Remove from memory
        if (isset(self::$memory_cache[$key])) {
            unset(self::$memory_cache[$key]);
        }

        // Remove from transients
        return delete_transient($key);
    }

    //-------------------------------
    // Clear All Cache
    public function clear_all()
    {
        // Clear memory cache
        self::$memory_cache = array();

        // Clear transients
        return ssg_clear_cache();
    }

    //-------------------------------
    // Clear Cache by Type
    public function clear_by_type($type, $subtype = '')
    {
        $key = ssg_get_cache_key($type, $subtype);
        $this->delete($key);

        // Also clear main index
        $this->delete(ssg_get_cache_key('main'));

        return true;
    }

    //-------------------------------
    // Validate Cache (check settings hash)
    private function is_cache_valid($data)
    {
        if (!is_array($data)) {
            return true;
        }

        if (!isset($data['_settings_hash'])) {
            return false;
        }

        $current_hash = ssg_get_settings_hash();
        return $data['_settings_hash'] === $current_hash;
    }

    //-------------------------------
    // Get or Set with Callback
    public function remember($key, $callback, $expiration = null)
    {
        $data = $this->get($key);

        if (false !== $data) {
            return $data;
        }

        // Prevent race condition with lock
        $lock_key = $key . '_lock';

        if (get_transient($lock_key)) {
            // Another process is generating, wait and try again
            sleep(1);
            $data = $this->get($key);
            if (false !== $data) {
                return $data;
            }
        }

        // Set lock (60 seconds)
        set_transient($lock_key, true, 60);

        // Generate data
        $data = call_user_func($callback);

        // Save to cache
        $this->set($key, $data, $expiration);

        // Release lock
        delete_transient($lock_key);

        return $data;
    }

    //-------------------------------
    // Get Cache Stats
    public function get_stats()
    {
        global $wpdb;

        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_ssg_%'"
        );

        $size = $wpdb->get_var(
            "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_ssg_%'"
        );

        return array(
            'count' => absint($count),
            'size' => $this->format_bytes($size)
        );
    }

    //-------------------------------
    // Format Bytes
    private function format_bytes($bytes)
    {
        $units = array('B', 'KB', 'MB', 'GB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    //-------------------------------
    // Check if Cache is Enabled
    public function is_enabled()
    {
        $settings = ssg_get_settings();
        return isset($settings['cache_duration']) && absint($settings['cache_duration']) > 0;
    }

    //-------------------------------
    // Get Cache Expiration Time
    public function get_expiration()
    {
        $settings = ssg_get_settings();
        $hours = isset($settings['cache_duration']) ? absint($settings['cache_duration']) : 24;
        return $hours * HOUR_IN_SECONDS;
    }
}
