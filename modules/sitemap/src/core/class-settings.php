<?php

/**
 * Settings Manager Class
 * 
 * Related Files:
 * - includes/helpers.php
 * - admin/class-admin.php
 * - core/class-cache.php
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

class SSG_Settings
{

    private static $instance = null;
    private $settings = null;

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
        $this->load_settings();
    }

    //-------------------------------
    // Load Settings from DB
    private function load_settings()
    {
        if (null === $this->settings) {
            $this->settings = ssg_get_settings();
        }
    }

    //-------------------------------
    // Get All Settings
    public function get_all()
    {
        return $this->settings;
    }

    //-------------------------------
    // Get Single Setting
    public function get($key, $default = null)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }

    //-------------------------------
    // Update Settings
    public function update($new_settings)
    {
        // Get old settings
        $old_settings = $this->settings;

        // Sanitize before save
        $new_settings = ssg_sanitize_settings($new_settings);

        // Merge with existing
        $this->settings = array_merge($this->settings, $new_settings);

        // Check if mode changed
        $mode_changed = false;
        if (isset($old_settings['mode']) && isset($new_settings['mode'])) {
            if ($old_settings['mode'] !== $new_settings['mode']) {
                $mode_changed = true;
            }
        }

        // Save to database
        $result = update_option('ssg_settings', $this->settings);

        if ($result) {
            // STEP 1: Clear all cache first
            ssg_clear_cache();

            // STEP 2: Handle mode change
            if ($mode_changed) {
                $this->handle_mode_change($old_settings['mode'], $new_settings['mode']);
            }

            // STEP 3: Rebuild if static mode
            if ($this->get('mode') === 'static') {
                $this->rebuild_static_file();
            }

            // STEP 4: Flush rewrite rules
            flush_rewrite_rules();
        }

        return $result;
    }

    //-------------------------------
    // Handle Mode Change
    private function handle_mode_change($old_mode, $new_mode)
    {
        require_once SSG_PATH . 'core/class-builder.php';
        $builder = new SSG_Builder();

        if ($old_mode === 'static' && $new_mode === 'dynamic') {
            // Switch from Static to Dynamic
            // Remove ALL static files (index + separate sitemaps)
            $builder->delete_static_files();

            // Add rewrite rules for dynamic mode BEFORE flush
            add_rewrite_rule('^sitemap\.xml$', 'index.php?ssg_sitemap=main', 'top');
            add_rewrite_rule('^sitemap-([a-z0-9_-]+)\.xml$', 'index.php?ssg_sitemap_name=$matches[1]', 'top');

        } elseif ($old_mode === 'dynamic' && $new_mode === 'static') {
            // Switch from Dynamic to Static
            // Create static files (index + separate sitemaps)
            $builder->rebuild_static_file();
        }
    }

    //-------------------------------
    // Reset to Defaults
    public function reset()
    {
        $defaults = ssg_get_defaults();
        $this->settings = $defaults;

        delete_option('ssg_settings');
        add_option('ssg_settings', $defaults);

        ssg_clear_cache();

        // Delete ALL static files (index + separate sitemaps)
        require_once SSG_PATH . 'core/class-builder.php';
        $builder = new SSG_Builder();
        $builder->delete_static_files();

        flush_rewrite_rules();

        return true;
    }

    //-------------------------------
    // Check if Homepage is Enabled
    public function is_homepage_enabled()
    {
        $homepage = $this->get('homepage', array());
        return isset($homepage['enabled']) && ssg_normalize_bool($homepage['enabled']);
    }

    //-------------------------------
    // Check if Post Type is Enabled
    public function is_post_type_enabled($post_type)
    {
        $post_types = $this->get('post_types', array());

        if (!isset($post_types[$post_type])) {
            return false;
        }

        return isset($post_types[$post_type]['enabled']) && ssg_normalize_bool($post_types[$post_type]['enabled']);
    }

    //-------------------------------
    // Check if Taxonomy is Enabled (INDEPENDENT)
    public function is_taxonomy_enabled($taxonomy)
    {
        $taxonomies = $this->get('taxonomies', array());

        if (!isset($taxonomies[$taxonomy])) {
            return false;
        }

        return isset($taxonomies[$taxonomy]['enabled']) && ssg_normalize_bool($taxonomies[$taxonomy]['enabled']);
    }

    //-------------------------------
    // Check if Authors is Enabled
    public function is_authors_enabled()
    {
        $authors = $this->get('authors', array());
        return isset($authors['enabled']) && ssg_normalize_bool($authors['enabled']);
    }

    //-------------------------------
    // Check if Dates is Enabled
    public function is_dates_enabled()
    {
        $dates = $this->get('dates', array());
        return isset($dates['enabled']) && ssg_normalize_bool($dates['enabled']);
    }

    //-------------------------------
    // Get Enabled Post Types
    public function get_enabled_post_types()
    {
        $post_types = $this->get('post_types', array());
        $enabled = array();

        foreach ($post_types as $name => $config) {
            if (isset($config['enabled']) && ssg_normalize_bool($config['enabled'])) {
                $enabled[$name] = $config;
            }
        }

        return $enabled;
    }

    //-------------------------------
    // Get Enabled Taxonomies (INDEPENDENT)
    public function get_enabled_taxonomies()
    {
        $taxonomies = $this->get('taxonomies', array());
        $enabled = array();

        foreach ($taxonomies as $name => $config) {
            if (isset($config['enabled']) && ssg_normalize_bool($config['enabled'])) {
                $enabled[$name] = $config;
            }
        }

        return $enabled;
    }

    //-------------------------------
    // Get Post Type Config
    public function get_post_type_config($post_type)
    {
        $post_types = $this->get('post_types', array());

        if (!isset($post_types[$post_type])) {
            return array(
                'enabled' => false,
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'include_archive' => false
            );
        }

        return $post_types[$post_type];
    }

    //-------------------------------
    // Get Taxonomy Config
    public function get_taxonomy_config($taxonomy)
    {
        $taxonomies = $this->get('taxonomies', array());

        if (!isset($taxonomies[$taxonomy])) {
            return array(
                'enabled' => false,
                'priority' => '0.5',
                'changefreq' => 'weekly'
            );
        }

        return $taxonomies[$taxonomy];
    }

    //-------------------------------
    // Export Settings as JSON
    public function export()
    {
        return json_encode($this->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    //-------------------------------
    // Import Settings from JSON
    public function import($json)
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        return $this->update($data);
    }

    //-------------------------------
    // Rebuild Static File
    private function rebuild_static_file()
    {
        require_once SSG_PATH . 'core/class-builder.php';
        $builder = new SSG_Builder();
        return $builder->rebuild_static_file();
    }

    //-------------------------------
    // Validate Settings
    public function validate()
    {
        $errors = array();

        // Check if at least one section is enabled
        $has_enabled = false;

        if ($this->is_homepage_enabled()) {
            $has_enabled = true;
        }

        if (!empty($this->get_enabled_post_types())) {
            $has_enabled = true;
        }

        if (!empty($this->get_enabled_taxonomies())) {
            $has_enabled = true;
        }

        if ($this->is_authors_enabled()) {
            $has_enabled = true;
        }

        if ($this->is_dates_enabled()) {
            $has_enabled = true;
        }

        if (!$has_enabled) {
            $errors[] = __('حداقل یک بخش باید فعال باشد (صفحه اصلی، نوع پست، تکسونومی، نویسندگان یا تاریخ‌ها).', 'seo-sitemap-generator');
        }

        return $errors;
    }
}
