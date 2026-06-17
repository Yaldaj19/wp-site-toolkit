<?php

/**
 * Plugin Name: SEO Sitemap Generator
 * Plugin URI: https://developer.developer
 * Description: Professional XML Sitemap Generator for WordPress with full support for all Post Types, Taxonomies, and WooCommerce products.
 * Version: 1.0.1
 * Author: Developer
 * Author URI: https://developer.developer
 * Text Domain: seo-sitemap-generator
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

//-------------------------------
// Related Files:
// - includes/helpers.php
// - includes/hooks.php
// - core/class-manager.php
// - admin/class-admin.php

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}
//-------------------------------
// Define Constants
define('SSG_VERSION', '1.0.1');
define('SSG_FILE', __FILE__);
define('SSG_PATH', plugin_dir_path(__FILE__));
define('SSG_URL', plugin_dir_url(__FILE__));
define('SSG_BASENAME', plugin_basename(__FILE__));
//-------------------------------
// Autoload Classes
spl_autoload_register(function ($class) {
    $prefix = 'SSG_';
    $base_dir = SSG_PATH . 'core/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

//-------------------------------
// Load Required Files
require_once SSG_PATH . 'includes/helpers.php';
require_once SSG_PATH . 'includes/hooks.php';

//-------------------------------
// Admin Panel
if (is_admin()) {
    require_once SSG_PATH . 'admin/class-admin.php';
    new SSG_Admin();
}

//-------------------------------
// Initialize Plugin
function ssg_init() {
    require_once SSG_PATH . 'core/class-manager.php';
    SSG_Manager::instance();
}
add_action('init', 'ssg_init');

//-------------------------------
// Activation Hook
function ssg_activate() {
    // Set default settings with auto-enabled core post types
    $defaults = array(
        'version' => SSG_VERSION,
        'mode' => 'dynamic',
        'cache_duration' => 24,
        'homepage' => array(
            'enabled' => true,
            'priority' => '1.0',
            'changefreq' => 'daily'
        ),
        'post_types' => array(
            'post' => array(
                'enabled' => true,
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'include_archive' => false
            ),
            'page' => array(
                'enabled' => true,
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'include_archive' => false
            )
        ),
        'taxonomies' => array(
            'category' => array(
                'enabled' => true,
                'priority' => '0.5',
                'changefreq' => 'weekly'
            ),
            'post_tag' => array(
                'enabled' => true,
                'priority' => '0.5',
                'changefreq' => 'weekly'
            )
        ),
        'authors' => array(
            'enabled' => false,
            'priority' => '0.5',
            'changefreq' => 'monthly'
        ),
        'dates' => array(
            'enabled' => false,
            'priority' => '0.3',
            'changefreq' => 'yearly'
        ),
        'excluded_urls' => array(),
        'exclude_password' => true,
        'exclude_redirects' => true,
        'exclude_noindex' => true,
        'auto_ping' => true
    );

    // Check if WooCommerce is active and add product settings
    if (class_exists('WooCommerce')) {
        $defaults['post_types']['product'] = array(
            'enabled' => true,
            'priority' => '0.8',
            'changefreq' => 'weekly',
            'include_archive' => true
        );
        $defaults['taxonomies']['product_cat'] = array(
            'enabled' => true,
            'priority' => '0.6',
            'changefreq' => 'weekly'
        );
        $defaults['taxonomies']['product_tag'] = array(
            'enabled' => true,
            'priority' => '0.5',
            'changefreq' => 'weekly'
        );
    }

    add_option('ssg_settings', $defaults);

    // Flush rewrite rules
    flush_rewrite_rules();

    // Disable conflicting SEO plugins sitemaps
    ssg_disable_conflicts();
}
// چرخه‌ی حیات توسط هسته‌ی WP Site Toolkit مدیریت می‌شود (register_activation_hook حذف شد)

//-------------------------------
// Deactivation Hook
function ssg_deactivate() {
    // Clear all cache
    ssg_clear_cache();

    // Delete ALL static files (index + separate sitemaps)
    require_once SSG_PATH . 'core/class-builder.php';
    $builder = new SSG_Builder();
    $builder->delete_static_files();

    // Flush rewrite rules
    flush_rewrite_rules();
}
// register_deactivation_hook حذف شد — مدیریت توسط هسته‌ی WP Site Toolkit

//-------------------------------
// Uninstall Hook
function ssg_uninstall() {
    // Delete options
    delete_option('ssg_settings');

    // Clear all transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ssg_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ssg_%'");

    // Delete ALL static sitemap files
    $main_file = ABSPATH . 'sitemap.xml';
    if (file_exists($main_file)) {
        @unlink($main_file);
    }

    // Delete all sitemap-*.xml files
    $pattern = ABSPATH . 'sitemap-*.xml';
    $files = glob($pattern);
    if ($files) {
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}
// register_uninstall_hook حذف شد — پاک‌سازی توسط uninstall.php پلاگین انجام می‌شود