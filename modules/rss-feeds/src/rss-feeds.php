<?php
/**
 * RSS Feeds Manager — Entry Point
 *
 * این فایل کلاس‌های اصلی ابزار رو لود و instantiate می‌کنه.
 *
 * @package Yj19\RSS
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once YJ19_RSS_DIR . '/core/class-feed-manager.php';

// Feed Manager همیشه لود میشه (هم فرانت هم ادمین) چون باید روی query فید اثر بذاره
new YJ19_RSS_Feed_Manager();

add_action('send_headers', function () {
    if (!headers_sent()) {
        header('X-YJ19-RSS-Tool: loaded');
    }
}, 1);

if (is_admin()) {
    require_once YJ19_RSS_DIR . '/admin/class-admin.php';
    require_once YJ19_RSS_DIR . '/admin/class-single-meta.php';

    new YJ19_RSS_Admin();
    new YJ19_RSS_Single_Meta();
}

/**
 * Helper: لیست post typeهای ایندکس‌پذیر که در این ابزار قابل تنظیم هستن.
 *
 * فقط post typeهایی که public + publicly_queryable هستن و attachment نیستن.
 * این همون منطقیه که برای ایندکس‌پذیری در سایت‌مپ هم استفاده میشه.
 *
 * @return WP_Post_Type[] آرایه‌ای از آبجکت‌های post type
 */
function yj19_rss_get_indexable_post_types()
{
    $post_types = get_post_types(
        array(
            'public'             => true,
            'publicly_queryable' => true,
        ),
        'objects'
    );

    // attachment رو حذف می‌کنیم — برای Discover معنی نداره
    unset($post_types['attachment']);

    /**
     * فیلتر برای حذف/اضافه post type دستی.
     *
     * @param WP_Post_Type[] $post_types لیست post typeها
     */
    return apply_filters('yj19_rss_indexable_post_types', $post_types);
}

/**
 * Helper: گرفتن تنظیمات با مقادیر پیش‌فرض.
 *
 * @return array
 */
function yj19_rss_get_settings()
{
    $settings = get_option(YJ19_RSS_OPTION, array());

    if (!is_array($settings)) {
        $settings = array();
    }

    if (!isset($settings['post_types']) || !is_array($settings['post_types'])) {
        $settings['post_types'] = array();
    }

    return $settings;
}

/**
 * Helper: آیا فید RSS برای یه post type خاص فعاله؟
 *
 * @param string $post_type
 * @return bool
 */
function yj19_rss_is_post_type_enabled($post_type)
{
    $settings = yj19_rss_get_settings();
    return !empty($settings['post_types'][$post_type]['enabled']);
}
