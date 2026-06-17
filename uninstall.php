<?php
/**
 * Uninstall — WP Site Toolkit
 *
 * فقط هنگام «حذف» پلاگین از وردپرس اجرا می‌شود (نه هنگام غیرفعال‌سازی).
 * option های همه‌ی ماژول‌ها و فایل‌های ساکن sitemap را پاک می‌کند.
 *
 * توجه: اگر این option keyها توسط ابزار دیگری (مثلاً نسخه‌ی داخل قالب) هم
 * استفاده می‌شوند، با حذف پلاگین پاک خواهند شد.
 *
 * @package WPSiteToolkit
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

/* ---- option های ماژول‌ها ---- */
$wp_site_toolkit_options = array(
    'ssg_settings',                 // Sitemap
    'yj19_pwa_settings',            // PWA
    'yj19_rss_feeds_settings',      // RSS Feeds
    'yj19_ssg_uninstall_cleaned',   // legacy flag
);
foreach ($wp_site_toolkit_options as $wp_site_toolkit_opt) {
    delete_option($wp_site_toolkit_opt);
}

/* ---- transient های sitemap ---- */
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ssg_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ssg_%'");

/* ---- post meta استثنای فید RSS ---- */
delete_post_meta_by_key('_yj19_exclude_from_feed');

/* ---- فایل‌های ساکن sitemap در ریشه ---- */
$wp_site_toolkit_main = ABSPATH . 'sitemap.xml';
if (file_exists($wp_site_toolkit_main)) {
    @unlink($wp_site_toolkit_main);
}
$wp_site_toolkit_files = glob(ABSPATH . 'sitemap-*.xml');
if (is_array($wp_site_toolkit_files)) {
    foreach ($wp_site_toolkit_files as $wp_site_toolkit_file) {
        @unlink($wp_site_toolkit_file);
    }
}

flush_rewrite_rules();
