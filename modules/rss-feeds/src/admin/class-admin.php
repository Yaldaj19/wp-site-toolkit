<?php
/**
 * Admin Page — صفحه تنظیمات RSS Feeds
 *
 * زیر منوی Settings (تنظیمات) قرار می‌گیره، کنار SEO Sitemap.
 *
 * @package Yj19\RSS
 */

if (!defined('ABSPATH')) {
    exit;
}

class YJ19_RSS_Admin
{
    const PAGE_SLUG = 'yj19-rss-feeds';
    const NONCE     = 'yj19_rss_nonce';

    /** @var string هوک صفحه برای گیت کردن enqueue */
    private $hook_suffix = '';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_yj19_rss_save', array($this, 'ajax_save'));
    }

    public function add_menu()
    {
        $parent = defined('WP_SITE_TOOLKIT_MENU_SLUG') ? WP_SITE_TOOLKIT_MENU_SLUG : 'options-general.php';
        $this->hook_suffix = add_submenu_page(
            $parent,
            __('تنظیمات RSS و فیدها', 'yj19-panel'),
            __('RSS Feeds', 'yj19-panel'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_page')
        );
    }

    public function enqueue_assets($hook)
    {
        if ($hook !== $this->hook_suffix) {
            return;
        }

        wp_enqueue_style(
            'yj19-rss-admin',
            YJ19_RSS_URL . '/assets/admin.css',
            array(),
            YJ19_RSS_VERSION
        );

        wp_enqueue_script(
            'yj19-rss-admin',
            YJ19_RSS_URL . '/assets/admin.js',
            array('jquery'),
            YJ19_RSS_VERSION,
            true
        );

        wp_localize_script('yj19-rss-admin', 'yj19RssAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce(self::NONCE),
            'strings' => array(
                'saving' => __('در حال ذخیره...', 'yj19-panel'),
                'saved'  => __('تنظیمات با موفقیت ذخیره شد.', 'yj19-panel'),
                'error'  => __('خطا در ذخیره‌سازی.', 'yj19-panel'),
            ),
        ));
    }

    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('دسترسی غیرمجاز.', 'yj19-panel'));
        }

        $post_types = yj19_rss_get_indexable_post_types();
        $settings   = yj19_rss_get_settings();

        include YJ19_RSS_DIR . '/admin/views/settings-page.php';
    }

    /**
     * ذخیره تنظیمات از طریق AJAX.
     */
    public function ajax_save()
    {
        check_ajax_referer(self::NONCE, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('دسترسی غیرمجاز.', 'yj19-panel')));
        }

        $raw = isset($_POST['post_types']) && is_array($_POST['post_types'])
            ? wp_unslash($_POST['post_types'])
            : array();

        $indexable = yj19_rss_get_indexable_post_types();
        $clean     = array();

        foreach ($indexable as $pt_slug => $pt_obj) {
            $enabled = !empty($raw[$pt_slug]['enabled']);
            $clean[$pt_slug] = array(
                'enabled' => $enabled ? 1 : 0,
            );
        }

        $settings = yj19_rss_get_settings();
        $settings['post_types'] = $clean;

        update_option(YJ19_RSS_OPTION, $settings);

        wp_send_json_success(array(
            'message' => __('تنظیمات با موفقیت ذخیره شد.', 'yj19-panel'),
        ));
    }
}
