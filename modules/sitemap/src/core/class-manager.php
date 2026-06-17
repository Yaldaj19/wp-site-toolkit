<?php

/**
 * Main Manager Class
 * 
 * Related Files:
 * - custom-sitemap.php
 * - core/class-settings.php
 * - core/class-cache.php
 * - core/class-detector.php
 * - core/class-builder.php
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

class SSG_Manager
{

    private static $instance = null;
    private $settings;
    private $cache;
    private $detector;
    private $builder;

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
        $this->init();
    }

    //-------------------------------
    // Initialize
    private function init()
    {
        $this->settings = SSG_Settings::instance();
        $this->cache = SSG_Cache::instance();
        $this->detector = SSG_Detector::instance();
        $this->builder = new SSG_Builder();

        $mode = $this->settings->get('mode', 'dynamic');

        if ($mode === 'static') {
            $this->setup_static_mode();
        } else {
            $this->setup_dynamic_mode();
        }
    }

    //-------------------------------
    // Setup Dynamic Mode
    private function setup_dynamic_mode()
    {
        // Rewrite rules already added in hooks.php
    }

    //-------------------------------
    // Setup Static Mode
    private function setup_static_mode()
    {
        $static_file = ABSPATH . 'sitemap.xml';

        if (!file_exists($static_file)) {
            $this->builder->rebuild_static_file();
        }
    }

    //-------------------------------
    // Get Settings
    public function get_settings()
    {
        return $this->settings;
    }

    //-------------------------------
    // Get Cache
    public function get_cache()
    {
        return $this->cache;
    }

    //-------------------------------
    // Get Detector
    public function get_detector()
    {
        return $this->detector;
    }

    //-------------------------------
    // Get Builder
    public function get_builder()
    {
        return $this->builder;
    }

    //-------------------------------
    // Clear All Cache
    public function clear_cache()
    {
        return $this->cache->clear_all();
    }

    //-------------------------------
    // Rebuild Sitemap
    public function rebuild()
    {
        $this->clear_cache();

        $mode = $this->settings->get('mode');

        if ($mode === 'static') {
            return $this->builder->rebuild_static_file();
        } else {
            return true;
        }
    }

    //-------------------------------
    // Switch Mode
    public function switch_mode($new_mode)
    {
        $old_mode = $this->settings->get('mode');

        if ($old_mode === $new_mode) {
            return true;
        }

        $this->clear_cache();

        $static_file = ABSPATH . 'sitemap.xml';

        if ($old_mode === 'static' && $new_mode === 'dynamic') {
            if (file_exists($static_file)) {
                @unlink($static_file);
            }
        } elseif ($old_mode === 'dynamic' && $new_mode === 'static') {
            $this->builder->rebuild_static_file();
        }

        $this->settings->update(array('mode' => $new_mode));

        flush_rewrite_rules();

        return true;
    }

    //-------------------------------
    // Ping Search Engines
    public function ping_search_engines()
    {
        $auto_ping = $this->settings->get('auto_ping', true);

        if (!$auto_ping) {
            return false;
        }

        return ssg_ping_search_engines();
    }

    //-------------------------------
    // Get Sitemap URL (UPDATED - No more tax- prefix)
    public function get_sitemap_url($type = 'main', $subtype = '')
    {
        $base_url = home_url('/');

        switch ($type) {
            case 'main':
                return $base_url . 'sitemap.xml';

            case 'homepage':
                return $base_url . 'sitemap-homepage.xml';

            case 'post_type':
                return $base_url . 'sitemap-' . $subtype . '.xml';

            case 'taxonomy':
                return $base_url . 'sitemap-' . $subtype . '.xml';

            case 'authors':
                return $base_url . 'sitemap-authors.xml';

            case 'dates':
                return $base_url . 'sitemap-dates.xml';

            default:
                return '';
        }
    }

    //-------------------------------
    // Check Health
    public function check_health()
    {
        $issues = array();

        $validation_errors = $this->settings->validate();
        if (!empty($validation_errors)) {
            $issues = array_merge($issues, $validation_errors);
        }

        $permalink_structure = get_option('permalink_structure');
        if (empty($permalink_structure)) {
            $issues[] = __('ساختار پیوند یکتا باید تنظیم شود (نه پیش‌فرض). به تنظیمات ← پیوندهای یکتا بروید.', 'seo-sitemap-generator');
        }

        $mode = $this->settings->get('mode');
        if ($mode === 'static') {
            $static_file = ABSPATH . 'sitemap.xml';
            if (file_exists($static_file) && !is_writable($static_file)) {
                $issues[] = __('فایل سایت‌مپ استاتیک قابل نوشتن نیست. مجوزهای فایل را بررسی کنید.', 'seo-sitemap-generator');
            } elseif (!is_writable(ABSPATH)) {
                $issues[] = __('دایرکتوری اصلی قابل نوشتن نیست. نمی‌توان فایل سایت‌مپ استاتیک ایجاد کرد.', 'seo-sitemap-generator');
            }
        }

        $conflicts = array();

        if (class_exists('WPSEO_Options')) {
            $yoast = get_option('wpseo_xml');
            if (is_array($yoast) && isset($yoast['enablexmlsitemap']) && $yoast['enablexmlsitemap']) {
                $conflicts[] = 'Yoast SEO';
            }
        }

        if (class_exists('RankMath')) {
            $rank = get_option('rank-math-options-sitemap');
            if (is_array($rank) && isset($rank['sitemap']) && $rank['sitemap'] !== 'off') {
                $conflicts[] = 'Rank Math';
            }
        }

        if (!empty($conflicts)) {
            $issues[] = sprintf(
                __('سایت‌مپ متداخل تشخیص داده شد: %s. لطفاً ویژگی سایت‌مپ آن‌ها را غیرفعال کنید.', 'seo-sitemap-generator'),
                implode(', ', $conflicts)
            );
        }

        return array(
            'healthy' => empty($issues),
            'issues' => $issues
        );
    }

    //-------------------------------
    // Get Statistics
    public function get_statistics()
    {
        $stats = array(
            'mode' => $this->settings->get('mode'),
            'total_sitemaps' => 0,
            'total_urls' => 0,
            'cache_stats' => $this->cache->get_stats(),
            'sections' => array()
        );

        if ($this->settings->is_homepage_enabled()) {
            $stats['sections']['homepage'] = array(
                'enabled' => true,
                'count' => 1
            );
            $stats['total_sitemaps']++;
            $stats['total_urls'] += 1;
        }

        foreach ($this->settings->get_enabled_post_types() as $post_type => $config) {
            $count = ssg_count_urls('post_type', $post_type);
            $stats['sections']['post_types'][$post_type] = array(
                'enabled' => true,
                'count' => $count
            );
            $stats['total_sitemaps']++;
            $stats['total_urls'] += $count;
        }

        foreach ($this->settings->get_enabled_taxonomies() as $taxonomy => $config) {
            $count = ssg_count_urls('taxonomy', $taxonomy);
            $stats['sections']['taxonomies'][$taxonomy] = array(
                'enabled' => true,
                'count' => $count
            );
            $stats['total_sitemaps']++;
            $stats['total_urls'] += $count;
        }

        if ($this->settings->is_authors_enabled()) {
            $count = ssg_count_urls('authors');
            $stats['sections']['authors'] = array(
                'enabled' => true,
                'count' => $count
            );
            $stats['total_sitemaps']++;
            $stats['total_urls'] += $count;
        }

        if ($this->settings->is_dates_enabled()) {
            $count = ssg_count_urls('dates');
            $stats['sections']['dates'] = array(
                'enabled' => true,
                'count' => $count
            );
            $stats['total_sitemaps']++;
            $stats['total_urls'] += $count;
        }

        return $stats;
    }
}
