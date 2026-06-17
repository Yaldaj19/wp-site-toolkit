<?php

/**
 * Helper Functions
 *
 * Related Files:
 * - custom-sitemap.php (main file)
 * - core/class-settings.php
 * - core/class-cache.php
 * - core/class-builder.php
 * - admin/class-admin.php
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

//-------------------------------
// Normalize Boolean Values
function ssg_normalize_bool($value)
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_string($value)) {
        $value = strtolower(trim($value));
        return in_array($value, array('1', 'true', 'yes', 'on'), true);
    }

    return (bool) $value;
}

//-------------------------------
// Sanitize Settings Array
function ssg_sanitize_settings($settings)
{
    if (!is_array($settings)) {
        return array();
    }

    // Sanitize mode
    if (isset($settings['mode'])) {
        $settings['mode'] = in_array($settings['mode'], array('dynamic', 'static')) ? $settings['mode'] : 'dynamic';
    }

    // Sanitize cache duration
    if (isset($settings['cache_duration'])) {
        $settings['cache_duration'] = absint($settings['cache_duration']);
        $settings['cache_duration'] = max(1, min(168, $settings['cache_duration']));
    }

    // Sanitize excluded URLs
    if (isset($settings['excluded_urls'])) {
        if (is_string($settings['excluded_urls'])) {
            $settings['excluded_urls'] = array_filter(array_map('trim', explode("\n", $settings['excluded_urls'])));
        }
        $settings['excluded_urls'] = array_map('esc_url_raw', (array) $settings['excluded_urls']);
    }

    // Normalize booleans
    $bool_fields = array('exclude_password', 'exclude_redirects', 'exclude_noindex', 'auto_ping', 'exclude_out_of_stock', 'exclude_hidden');
    foreach ($bool_fields as $field) {
        if (isset($settings[$field])) {
            $settings[$field] = ssg_normalize_bool($settings[$field]);
        }
    }

    // Sanitize post types settings
    if (isset($settings['post_types']) && is_array($settings['post_types'])) {
        foreach ($settings['post_types'] as $key => $value) {
            if (isset($value['enabled'])) {
                $settings['post_types'][$key]['enabled'] = ssg_normalize_bool($value['enabled']);
            }
            if (isset($value['priority'])) {
                $settings['post_types'][$key]['priority'] = ssg_format_priority($value['priority']);
            }
            if (isset($value['changefreq'])) {
                $valid_freqs = array_keys(ssg_get_changefreq_options());
                if (!in_array($value['changefreq'], $valid_freqs)) {
                    $settings['post_types'][$key]['changefreq'] = 'weekly';
                }
            }
        }
    }

    // Sanitize taxonomies settings (INDEPENDENT from post types)
    if (isset($settings['taxonomies']) && is_array($settings['taxonomies'])) {
        foreach ($settings['taxonomies'] as $key => $value) {
            if (isset($value['enabled'])) {
                $settings['taxonomies'][$key]['enabled'] = ssg_normalize_bool($value['enabled']);
            }
            if (isset($value['priority'])) {
                $settings['taxonomies'][$key]['priority'] = ssg_format_priority($value['priority']);
            }
            if (isset($value['changefreq'])) {
                $valid_freqs = array_keys(ssg_get_changefreq_options());
                if (!in_array($value['changefreq'], $valid_freqs)) {
                    $settings['taxonomies'][$key]['changefreq'] = 'weekly';
                }
            }
        }
    }

    return $settings;
}

//-------------------------------
// Get Default Settings
function ssg_get_defaults()
{
    return array(
        'version' => SSG_VERSION,
        'mode' => 'dynamic',
        'cache_duration' => 24,
        'homepage' => array(
            'enabled' => true,
            'priority' => '1.0',
            'changefreq' => 'daily'
        ),
        'post_types' => array(),
        'taxonomies' => array(),
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
}

//-------------------------------
// Get Settings with Dynamic Detection
function ssg_get_settings()
{
    $settings = get_option('ssg_settings', array());
    $defaults = ssg_get_defaults();

    // Merge with defaults
    $settings = wp_parse_args($settings, $defaults);

    // WooCommerce post types that should be auto-enabled
    $woo_post_types = array('product');
    $woo_taxonomies = array('product_cat', 'product_tag');

    // Core post types that should be auto-enabled
    $auto_enable_post_types = array('post', 'page');
    $auto_enable_taxonomies = array('category', 'post_tag');

    // Dynamic detection of post types
    $post_types = get_post_types(array('public' => true), 'objects');
    foreach ($post_types as $post_type) {
        if (in_array($post_type->name, array('attachment', 'revision', 'nav_menu_item'))) {
            continue;
        }

        // Add if not exists
        if (!isset($settings['post_types'][$post_type->name])) {
            // Auto-enable core post types and WooCommerce products
            $should_enable = in_array($post_type->name, $auto_enable_post_types) ||
                           (ssg_is_woocommerce_active() && in_array($post_type->name, $woo_post_types));

            $settings['post_types'][$post_type->name] = array(
                'enabled' => $should_enable,
                'priority' => ($post_type->name === 'product') ? '0.8' : '0.6',
                'changefreq' => 'weekly',
                'include_archive' => false
            );
        }
    }

    // Dynamic detection of taxonomies (INDEPENDENT)
    $taxonomies = get_taxonomies(array('public' => true), 'objects');
    foreach ($taxonomies as $taxonomy) {
        if (in_array($taxonomy->name, array('post_format', 'nav_menu', 'link_category'))) {
            continue;
        }

        // Add if not exists (NO dependency on post types)
        if (!isset($settings['taxonomies'][$taxonomy->name])) {
            // Auto-enable core taxonomies and WooCommerce taxonomies
            $should_enable = in_array($taxonomy->name, $auto_enable_taxonomies) ||
                           (ssg_is_woocommerce_active() && in_array($taxonomy->name, $woo_taxonomies));

            $settings['taxonomies'][$taxonomy->name] = array(
                'enabled' => $should_enable,
                'priority' => '0.5',
                'changefreq' => 'weekly'
            );
        }
    }

    return $settings;
}

//-------------------------------
// Clear All Cache
function ssg_clear_cache()
{
    global $wpdb;

    // Delete all sitemap transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ssg_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ssg_%'");

    return true;
}

//-------------------------------
// Get Cache Key
function ssg_get_cache_key($type = 'main', $subtype = '')
{
    $key = 'ssg_' . sanitize_title_with_dashes($type, '', 'save');

    if (!empty($subtype)) {
        // Use preg_replace to keep underscores and alphanumeric chars
        $safe_subtype = preg_replace('/[^a-z0-9_-]/', '', strtolower($subtype));
        $key .= '_' . $safe_subtype;
    }

    return $key;
}

//-------------------------------
// Get Settings Hash (for cache validation)
function ssg_get_settings_hash()
{
    $settings = ssg_get_settings();
    return md5(serialize($settings));
}

//-------------------------------
// Check if URL is Excluded
function ssg_is_url_excluded($url)
{
    $settings = ssg_get_settings();
    $excluded = isset($settings['excluded_urls']) ? $settings['excluded_urls'] : array();

    if (empty($excluded) || !is_array($excluded)) {
        return false;
    }

    foreach ($excluded as $pattern) {
        if (empty($pattern)) {
            continue;
        }

        // Convert wildcard to regex
        $pattern = str_replace(array('*', '/'), array('.*', '\/'), preg_quote($pattern, '/'));

        if (preg_match('/^' . $pattern . '$/i', $url)) {
            return true;
        }
    }

    return false;
}

//-------------------------------
// Disable Conflicting SEO Plugins Sitemaps
function ssg_disable_conflicts()
{
    // Yoast SEO
    if (class_exists('WPSEO_Options')) {
        $yoast_options = get_option('wpseo_xml');
        if (is_array($yoast_options)) {
            $yoast_options['enablexmlsitemap'] = false;
            update_option('wpseo_xml', $yoast_options);
        }
    }

    // Rank Math
    if (class_exists('RankMath')) {
        $rank_options = get_option('rank-math-options-sitemap');
        if (is_array($rank_options)) {
            $rank_options['sitemap'] = 'off';
            update_option('rank-math-options-sitemap', $rank_options);
        }
    }

    // All in One SEO
    if (function_exists('aioseo')) {
        $aioseo_options = get_option('aioseo_options');
        if (is_array($aioseo_options)) {
            $aioseo_options['sitemap']['general']['enable'] = false;
            update_option('aioseo_options', $aioseo_options);
        }
    }

    // SEOPress
    if (function_exists('seopress_activation')) {
        update_option('seopress_xml_sitemap_option_name', array('xml_sitemap_general_enable' => ''));
    }
}

//-------------------------------
// Format Priority Value
function ssg_format_priority($priority)
{
    $priority = (float) $priority;
    return number_format(max(0.0, min(1.0, $priority)), 1);
}

//-------------------------------
// Get Valid Changefreq Values
function ssg_get_changefreq_options()
{
    return array(
        'always' => __('Always', 'seo-sitemap-generator'),
        'hourly' => __('Hourly', 'seo-sitemap-generator'),
        'daily' => __('Daily', 'seo-sitemap-generator'),
        'weekly' => __('Weekly', 'seo-sitemap-generator'),
        'monthly' => __('Monthly', 'seo-sitemap-generator'),
        'yearly' => __('Yearly', 'seo-sitemap-generator'),
        'never' => __('Never', 'seo-sitemap-generator'),
        'auto' => __('Auto', 'seo-sitemap-generator')
    );
}

//-------------------------------
// Calculate Auto Changefreq
function ssg_calc_auto_changefreq($last_modified)
{
    if (empty($last_modified)) {
        return 'monthly';
    }

    $now = current_time('timestamp');
    $modified = is_numeric($last_modified) ? $last_modified : strtotime($last_modified);
    $diff = $now - $modified;

    $day = 86400;

    if ($diff < $day) {
        return 'daily';
    } elseif ($diff < $day * 7) {
        return 'weekly';
    } elseif ($diff < $day * 30) {
        return 'monthly';
    } else {
        return 'yearly';
    }
}

//-------------------------------
// Get Post Type Icon
function ssg_get_icon($type)
{
    $icons = array(
        'post' => '📝',
        'page' => '📄',
        'product' => '🛒',
        'category' => '📁',
        'post_tag' => '🏷️',
        'product_cat' => '📁',
        'product_tag' => '🏷️',
        'author' => '👤',
        'date' => '📅',
        'homepage' => '🏠'
    );

    return isset($icons[$type]) ? $icons[$type] : '📄';
}

//-------------------------------
// Ping Search Engines
function ssg_ping_search_engines()
{
    $sitemap_url = home_url('/sitemap.xml');

    // Google
    wp_remote_get('https://www.google.com/ping?sitemap=' . urlencode($sitemap_url), array('timeout' => 5));

    // Bing
    wp_remote_get('https://www.bing.com/ping?sitemap=' . urlencode($sitemap_url), array('timeout' => 5));

    return true;
}

//-------------------------------
// Check if WooCommerce is Active
function ssg_is_woocommerce_active()
{
    return class_exists('WooCommerce');
}

//-------------------------------
// Format Date to ISO 8601
function ssg_format_date($date)
{
    if (empty($date)) {
        $date = current_time('mysql');
    }
    return date('c', strtotime($date));
}

//-------------------------------
// Get Total URLs Count
function ssg_count_urls($type, $subtype = '')
{
    switch ($type) {
        case 'homepage':
            return 1;

        case 'post_type':
            $count = wp_count_posts($subtype);
            return isset($count->publish) ? $count->publish : 0;

        case 'taxonomy':
            return wp_count_terms(array(
                'taxonomy' => $subtype,
                'hide_empty' => false
            ));

        case 'authors':
            $users = get_users(array(
                'who' => 'authors',
                'has_published_posts' => true
            ));
            return count($users);

        case 'dates':
            global $wpdb;
            $count = $wpdb->get_var(
                "SELECT COUNT(DISTINCT CONCAT(YEAR(post_date), '-', LPAD(MONTH(post_date), 2, '0')))
                FROM {$wpdb->posts}
                WHERE post_status = 'publish'
                AND post_type = 'post'"
            );
            return $count ? $count : 0;

        default:
            return 0;
    }
}

//-------------------------------
// Check if Post should be Excluded
function ssg_should_exclude_post($post)
{
    $settings = ssg_get_settings();

    // Check password protected (use post_password property for reliability)
    if ($settings['exclude_password'] && !empty($post->post_password)) {
        return true;
    }

    // Check noindex meta (Yoast, Rank Math, etc.)
    if ($settings['exclude_noindex']) {
        $noindex = get_post_meta($post->ID, '_yoast_wpseo_meta-robots-noindex', true);
        if ($noindex === '1') {
            return true;
        }

        $rank_noindex = get_post_meta($post->ID, 'rank_math_robots', true);
        if (is_array($rank_noindex) && in_array('noindex', $rank_noindex)) {
            return true;
        }
    }

    // Check excluded URLs
    $permalink = get_permalink($post);
    if (ssg_is_url_excluded($permalink)) {
        return true;
    }

    return false;
}

//-------------------------------
// Migration: Update Old Settings
function ssg_migrate_settings()
{
    // First, migrate from old option name if exists
    $old_settings = get_option('yj19_sitemap_settings', array());
    if (!empty($old_settings)) {
        // Copy old settings to new option
        update_option('ssg_settings', $old_settings);
        // Delete old option
        delete_option('yj19_sitemap_settings');
        // Also delete old rewrite version
        delete_option('yj19_sitemap_rewrite_version');
    }

    $settings = get_option('ssg_settings', array());

    if (empty($settings)) {
        return;
    }

    $current_version = isset($settings['version']) ? $settings['version'] : '0.0.0';
    $needs_save = false;

    // Migration: Auto-enable WooCommerce for existing installations
    if (ssg_is_woocommerce_active()) {
        // Enable products if not set or disabled
        if (!isset($settings['post_types']['product']) ||
            (isset($settings['post_types']['product']['enabled']) && !$settings['post_types']['product']['enabled'])) {
            $settings['post_types']['product'] = array(
                'enabled' => true,
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'include_archive' => true
            );
            $needs_save = true;
        }

        // Enable product_cat if not set or disabled
        if (!isset($settings['taxonomies']['product_cat']) ||
            (isset($settings['taxonomies']['product_cat']['enabled']) && !$settings['taxonomies']['product_cat']['enabled'])) {
            $settings['taxonomies']['product_cat'] = array(
                'enabled' => true,
                'priority' => '0.6',
                'changefreq' => 'weekly'
            );
            $needs_save = true;
        }

        // Enable product_tag if not set
        if (!isset($settings['taxonomies']['product_tag'])) {
            $settings['taxonomies']['product_tag'] = array(
                'enabled' => true,
                'priority' => '0.5',
                'changefreq' => 'weekly'
            );
            $needs_save = true;
        }
    }

    // Migration: Ensure exclude_noindex is set
    if (!isset($settings['exclude_noindex'])) {
        $settings['exclude_noindex'] = true;
        $needs_save = true;
    }

    // No migration needed for version
    if (version_compare($current_version, SSG_VERSION, '>=') && !$needs_save) {
        return;
    }

    // Update version
    $settings['version'] = SSG_VERSION;

    update_option('ssg_settings', $settings);

    // Clear cache after migration
    ssg_clear_cache();
}
add_action('admin_init', 'ssg_migrate_settings');
