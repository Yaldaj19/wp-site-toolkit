<?php
/**
 * YJ19 RSS Feeds Manager — Module Loader (WP Site Toolkit)
 *
 * هدف: مدیریت فیدهای RSS برای Google Discover و سایر اپ‌های خوانش فید.
 *
 * این loader توسط هسته‌ی WP Site Toolkit لود میشه.
 * منبع کد اصلی: modules/rss-feeds/src/
 *
 * @package WPSiteToolkit
 */

if (!defined('ABSPATH')) {
    exit;
}

// جلوگیری از بارگذاری دوباره
if (defined('YJ19_RSS_VERSION')) {
    return;
}

define('YJ19_RSS_VERSION', '1.0.0');
define('YJ19_RSS_DIR', __DIR__ . '/src');
define('YJ19_RSS_URL', trailingslashit(plugin_dir_url(__FILE__)) . 'src');
define('YJ19_RSS_OPTION', 'yj19_rss_feeds_settings');
define('YJ19_RSS_META_EXCLUDE', '_yj19_exclude_from_feed');

$yj19_rss_entry = YJ19_RSS_DIR . '/rss-feeds.php';

if (!file_exists($yj19_rss_entry)) {
    if (is_admin()) {
        add_action('admin_notices', function () {
            if (!current_user_can('manage_options')) {
                return;
            }
            echo '<div class="notice notice-error"><p><strong>YJ19 RSS Feeds:</strong> ';
            echo 'فایل <code>modules/rss-feeds/src/rss-feeds.php</code> پیدا نشد.';
            echo '</p></div>';
        });
    }
    return;
}

require_once $yj19_rss_entry;
unset($yj19_rss_entry);
