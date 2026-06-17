<?php
/**
 * هسته‌ی WP Site Toolkit
 *
 * - منوی والد «ابزارهای سایت» رو می‌سازه
 * - همه‌ی ماژول‌های modules/<slug>/loader.php رو لود می‌کنه
 * - activation/deactivation رو به ماژول‌ها وصل می‌کنه
 *
 * @package WPSiteToolkit
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Site_Toolkit
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // منوی والد قبل از زیرمنوهای ماژول‌ها ثبت بشه (اولویت ۹ < ۱۰)
        add_action('admin_menu', array($this, 'register_parent_menu'), 9);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_filter('plugin_action_links_' . WP_SITE_TOOLKIT_BASENAME, array($this, 'action_links'));

        // لود ماژول‌ها (هر کدوم زیرمنوی خودش رو با اولویت پیش‌فرض ۱۰ ثبت می‌کنه)
        $this->load_modules();
    }

    /**
     * هر پوشه‌ی modules/<slug>/loader.php رو لود می‌کنه.
     * افزودن ماژول جدید: یه پوشه بساز + loader.php بذار. تمام.
     */
    private function load_modules()
    {
        $loaders = glob(WP_SITE_TOOLKIT_DIR . 'modules/*/loader.php');
        if (!is_array($loaders) || empty($loaders)) {
            return;
        }
        sort($loaders, SORT_STRING);
        foreach ($loaders as $loader) {
            require_once $loader;
        }
    }

    public function register_parent_menu()
    {
        add_menu_page(
            __('ابزارهای سایت', 'wp-site-toolkit'),
            __('ابزارهای سایت', 'wp-site-toolkit'),
            WP_SITE_TOOLKIT_CAP,
            WP_SITE_TOOLKIT_MENU_SLUG,
            array($this, 'render_dashboard'),
            'dashicons-admin-tools',
            58
        );

        // زیرمنوی اول هم‌اسلاگ والد = داشبورد (با لیبل «داشبورد»)
        add_submenu_page(
            WP_SITE_TOOLKIT_MENU_SLUG,
            __('داشبورد ابزارها', 'wp-site-toolkit'),
            __('داشبورد', 'wp-site-toolkit'),
            WP_SITE_TOOLKIT_CAP,
            WP_SITE_TOOLKIT_MENU_SLUG,
            array($this, 'render_dashboard')
        );
    }

    public function render_dashboard()
    {
        require_once WP_SITE_TOOLKIT_DIR . 'includes/class-dashboard.php';
        WP_Site_Toolkit_Dashboard::render();
    }

    public function enqueue_admin_assets($hook)
    {
        // فقط روی صفحه‌ی داشبورد توولکیت
        if (strpos((string) $hook, WP_SITE_TOOLKIT_MENU_SLUG) === false) {
            return;
        }
        wp_enqueue_style(
            'wp-site-toolkit-admin',
            WP_SITE_TOOLKIT_URL . 'assets/admin/toolkit.css',
            array(),
            WP_SITE_TOOLKIT_VERSION
        );
    }

    public function action_links($links)
    {
        $url  = admin_url('admin.php?page=' . WP_SITE_TOOLKIT_MENU_SLUG);
        $link = '<a href="' . esc_url($url) . '">' . esc_html__('ابزارها', 'wp-site-toolkit') . '</a>';
        array_unshift($links, $link);
        return $links;
    }

    /* ===== چرخه‌ی حیات ===== */

    public static function on_activate()
    {
        // ماژول‌ها هنگام include شدن فایل اصلی پلاگین لود شده‌اند، پس توابع‌شان در دسترس است.
        // seed تنظیمات پیش‌فرض sitemap (activation hook خودش دیگر fire نمی‌شود)
        if (function_exists('ssg_activate') && !get_option('ssg_settings')) {
            ssg_activate();
        }

        do_action('wp_site_toolkit_activate');
        flush_rewrite_rules();
    }

    public static function on_deactivate()
    {
        if (function_exists('ssg_deactivate')) {
            ssg_deactivate();
        }

        do_action('wp_site_toolkit_deactivate');
        flush_rewrite_rules();
    }
}
