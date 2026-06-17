<?php
/**
 * YJ19 PWA — Module Loader (WP Site Toolkit)
 *
 * هدف: تبدیل سایت به Progressive Web App برای موبایل با دکمه نصب
 * قابل سفارشی‌سازی، manifest پویا و service worker با کش هوشمند.
 *
 * این loader توسط هسته‌ی WP Site Toolkit به‌صورت خودکار لود میشه.
 *
 * @package WPSiteToolkit
 */

if (!defined('ABSPATH')) {
    exit;
}

if (defined('YJ19_PWA_VERSION')) {
    return;
}

define('YJ19_PWA_VERSION', '1.0.0');
define('YJ19_PWA_DIR', __DIR__ . '/src');
define('YJ19_PWA_URL', trailingslashit(plugin_dir_url(__FILE__)) . 'src');
define('YJ19_PWA_OPTION', 'yj19_pwa_settings');
define('YJ19_PWA_QUERY_VAR', 'yj19_pwa');

$yj19_pwa_entry = YJ19_PWA_DIR . '/pwa.php';

if (!file_exists($yj19_pwa_entry)) {
    if (is_admin()) {
        add_action('admin_notices', function () {
            if (!current_user_can('manage_options')) {
                return;
            }
            echo '<div class="notice notice-error"><p><strong>YJ19 PWA:</strong> ';
            echo 'فایل <code>modules/pwa/src/pwa.php</code> پیدا نشد.';
            echo '</p></div>';
        });
    }
    return;
}

require_once $yj19_pwa_entry;
unset($yj19_pwa_entry);
