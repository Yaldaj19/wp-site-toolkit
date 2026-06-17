<?php
/**
 * YJ19 PWA — Admin Page
 *
 * صفحه تنظیمات زیر منوی Settings (کنار RSS Feeds).
 *
 * @package Yj19\PWA
 */

if (!defined('ABSPATH')) {
    exit;
}

class YJ19_PWA_Admin
{
    const PAGE_SLUG = 'yj19-pwa';
    const NONCE_ACTION = 'yj19_pwa_save';
    const NONCE_NAME = 'yj19_pwa_nonce';

    /** @var string هوک صفحه برای گیت کردن enqueue */
    private $hook_suffix = '';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'maybe_save'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function add_menu()
    {
        $parent = defined('WP_SITE_TOOLKIT_MENU_SLUG') ? WP_SITE_TOOLKIT_MENU_SLUG : 'options-general.php';
        $this->hook_suffix = add_submenu_page(
            $parent,
            __('تنظیمات PWA', 'yj19-panel'),
            __('PWA', 'yj19-panel'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_page')
        );
    }

    public function maybe_save()
    {
        if (!isset($_POST['yj19_pwa_save']) || !current_user_can('manage_options')) {
            return;
        }

        check_admin_referer(self::NONCE_ACTION, self::NONCE_NAME);

        $input    = isset($_POST['yj19_pwa']) && is_array($_POST['yj19_pwa']) ? wp_unslash($_POST['yj19_pwa']) : array();
        $sanitized = YJ19_PWA_Settings::sanitize($input);

        update_option(YJ19_PWA_OPTION, $sanitized);

        add_settings_error(
            'yj19_pwa_messages',
            'yj19_pwa_saved',
            __('تنظیمات PWA ذخیره شد.', 'yj19-panel'),
            'updated'
        );
    }

    public function enqueue_assets($hook)
    {
        if ($hook !== $this->hook_suffix) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');

        $css_file = YJ19_PWA_DIR . '/admin/assets/pwa-admin.css';
        $js_file  = YJ19_PWA_DIR . '/admin/assets/pwa-admin.js';

        wp_enqueue_style(
            'yj19-pwa-admin',
            YJ19_PWA_URL . '/admin/assets/pwa-admin.css',
            array(),
            file_exists($css_file) ? filemtime($css_file) : YJ19_PWA_VERSION
        );

        wp_enqueue_script(
            'yj19-pwa-admin',
            YJ19_PWA_URL . '/admin/assets/pwa-admin.js',
            array('jquery', 'wp-color-picker'),
            file_exists($js_file) ? filemtime($js_file) : YJ19_PWA_VERSION,
            true
        );
    }

    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = YJ19_PWA_Settings::all();
        $manifest_url = YJ19_PWA_Manifest::endpoint_url();
        $sw_url       = YJ19_PWA_ServiceWorker::endpoint_url();

        include YJ19_PWA_DIR . '/admin/views/settings-page.php';
    }
}
