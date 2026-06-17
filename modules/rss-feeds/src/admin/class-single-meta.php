<?php
/**
 * Single Post Metabox — تنظیم اختصاصی هر پست برای فید
 *
 * فقط روی post typeهایی نشون داده میشه که فید RSS اون‌ها در تنظیمات کلی
 * فعال شده باشه. یه چک‌باکس داره: «حذف این پست از فید RSS / Google Discover».
 *
 * @package Yj19\RSS
 */

if (!defined('ABSPATH')) {
    exit;
}

class YJ19_RSS_Single_Meta
{
    const NONCE_FIELD  = 'yj19_rss_meta_nonce';
    const NONCE_ACTION = 'yj19_rss_meta_save';

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'register_metabox'));
        add_action('save_post', array($this, 'save_metabox'), 10, 2);
    }

    /**
     * فقط برای post typeهایی که RSS اون‌ها فعاله، metabox رو اضافه می‌کنه.
     */
    public function register_metabox()
    {
        $post_types = yj19_rss_get_indexable_post_types();

        foreach ($post_types as $pt_slug => $pt_obj) {
            if (!yj19_rss_is_post_type_enabled($pt_slug)) {
                continue;
            }

            add_meta_box(
                'yj19-rss-feed-options',
                __('فید RSS و Google Discover', 'yj19-panel'),
                array($this, 'render_metabox'),
                $pt_slug,
                'side',
                'default'
            );
        }
    }

    public function render_metabox($post)
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD);

        $excluded = get_post_meta($post->ID, YJ19_RSS_META_EXCLUDE, true) === '1';
        ?>
        <p style="margin-top:0;">
            <label style="display:flex; align-items:flex-start; gap:8px; line-height:1.6;">
                <input type="checkbox" name="yj19_rss_exclude" value="1" <?php checked($excluded, true); ?> style="margin-top:3px;" />
                <span>
                    <strong><?php esc_html_e('این پست از فید RSS حذف بشه', 'yj19-panel'); ?></strong>
                    <br>
                    <span class="description" style="color:#666; font-size:12px;">
                        <?php esc_html_e('اگه فعال کنی، این پست در RSS و Google Discover نشون داده نمی‌شه (ولی همچنان در سایت قابل دسترسیه).', 'yj19-panel'); ?>
                    </span>
                </span>
            </label>
        </p>
        <?php
    }

    /**
     * @param int     $post_id
     * @param WP_Post $post
     */
    public function save_metabox($post_id, $post)
    {
        // بررسی‌های امنیتی استاندارد
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST[self::NONCE_FIELD])) {
            return;
        }

        if (!wp_verify_nonce($_POST[self::NONCE_FIELD], self::NONCE_ACTION)) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // فقط روی post typeهای فعال‌شده اعمال میشه
        if (!yj19_rss_is_post_type_enabled($post->post_type)) {
            return;
        }

        if (!empty($_POST['yj19_rss_exclude'])) {
            update_post_meta($post_id, YJ19_RSS_META_EXCLUDE, '1');
        } else {
            delete_post_meta($post_id, YJ19_RSS_META_EXCLUDE);
        }
    }
}
