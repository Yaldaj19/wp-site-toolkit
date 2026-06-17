<?php
/**
 * SEO Sitemap Generator — Module Loader (WP Site Toolkit)
 *
 * چون این ماژول حالا داخل یک پلاگین واقعی اجرا می‌شود، دیگر نیازی به
 * hackهای قبلی (fix کردن plugins_url، activation hook جعلی، uninstall blocker)
 * نیست. custom-sitemap.php از plugin_dir_url(__FILE__) به‌صورت بومی درست کار می‌کند.
 *
 * چرخه‌ی حیات (activation/deactivation) توسط هسته‌ی WP Site Toolkit مدیریت می‌شود —
 * به همین دلیل register_*_hook ها از custom-sitemap.php حذف شده‌اند.
 *
 * منبع کد: modules/sitemap/src/
 *
 * @package WPSiteToolkit
 */

if (!defined('ABSPATH')) {
    exit;
}

// اگر به‌عنوان پلاگین مستقل واقعی هم نصب شده باشد، دوباره لود نشه (جلوگیری از redeclare)
if (defined('SSG_VERSION') || class_exists('SSG_Manager', false)) {
    return;
}

$yj19_ssg_entry = __DIR__ . '/src/custom-sitemap.php';

if (!file_exists($yj19_ssg_entry)) {
    if (is_admin()) {
        add_action('admin_notices', function () {
            if (!current_user_can('manage_options')) {
                return;
            }
            echo '<div class="notice notice-error"><p><strong>WP Site Toolkit — Sitemap:</strong> ';
            echo 'فایل <code>modules/sitemap/src/custom-sitemap.php</code> پیدا نشد.';
            echo '</p></div>';
        });
    }
    return;
}

require_once $yj19_ssg_entry;
unset($yj19_ssg_entry);
