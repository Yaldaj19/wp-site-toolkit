<?php
/**
 * Plugin Name:       WP Site Toolkit
 * Plugin URI:        https://yaldajahanshahi.ir
 * Description:        مجموعه ابزارهای سایت — PWA، فیدهای RSS و نقشه سایت (Sitemap) در یک پنل واحد و ماژولار، قابل توسعه برای ابزارهای آینده.
 * Version:           1.0.0
 * Author:            Yalda Jahanshahi
 * Author URI:        https://yaldajahanshahi.ir
 * Author Email:      yaldaj.619@gmail.com
 * Text Domain:       wp-site-toolkit
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * License:           GPL v2 or later
 *
 * @package WPSiteToolkit
 */

if (!defined('ABSPATH')) {
    exit;
}

if (defined('WP_SITE_TOOLKIT_VERSION')) {
    return;
}

define('WP_SITE_TOOLKIT_VERSION', '1.0.0');
define('WP_SITE_TOOLKIT_FILE', __FILE__);
define('WP_SITE_TOOLKIT_DIR', plugin_dir_path(__FILE__));
define('WP_SITE_TOOLKIT_URL', plugin_dir_url(__FILE__));
define('WP_SITE_TOOLKIT_BASENAME', plugin_basename(__FILE__));

/* اسلاگ منوی والد «ابزارها» — ماژول‌ها زیرمنوهاشون رو به همین وصل می‌کنن */
define('WP_SITE_TOOLKIT_MENU_SLUG', 'wp-site-toolkit');
/* قابلیت لازم برای دیدن پنل */
define('WP_SITE_TOOLKIT_CAP', 'manage_options');

require_once WP_SITE_TOOLKIT_DIR . 'includes/class-toolkit.php';

WP_Site_Toolkit::instance();

/**
 * Load translations (English source strings, .mo files under /languages).
 * بارگذاری ترجمه‌ها — رشته‌های مبدأ انگلیسی، فایل‌های .mo در پوشه‌ی /languages
 */
add_action('init', function () {
    load_plugin_textdomain(
        'wp-site-toolkit',
        false,
        dirname(WP_SITE_TOOLKIT_BASENAME) . '/languages'
    );
});

/* فعال‌سازی: seed تنظیمات پیش‌فرض ماژول‌ها (sitemap و ...) */
register_activation_hook(__FILE__, array('WP_Site_Toolkit', 'on_activate'));
register_deactivation_hook(__FILE__, array('WP_Site_Toolkit', 'on_deactivate'));
