<?php
/**
 * Feed Manager — منطق اصلی فعال/غیرفعال‌سازی فیدها
 *
 * این کلاس روی query فید RSS اثر می‌ذاره:
 *   1. اگه post type غیرفعال باشه، فید اون 404 می‌شه
 *   2. پست‌هایی که با metabox «حذف از فید» علامت خوردن، از feed خارج میشن
 *
 * @package Yj19\RSS
 */

if (!defined('ABSPATH')) {
    exit;
}

class YJ19_RSS_Feed_Manager
{
    public function __construct()
    {
        add_action('template_redirect', array($this, 'maybe_disable_feed'), 1);
        add_action('pre_get_posts', array($this, 'exclude_marked_posts'));
    }

    /**
     * اگه فید درخواست‌شده برای یه post type غیرفعال باشه، 404 برمی‌گردونه.
     */
    public function maybe_disable_feed()
    {
        if (!is_feed()) {
            if (!headers_sent()) {
                header('X-YJ19-Feed-Debug: not-a-feed');
            }
            return;
        }

        // post_type رو از چند منبع بخون:
        //   1. $_GET (وقتی URL از نوع ?feed=rss2&post_type=X باشه)
        //   2. query var وردپرس (وقتی rewrite اعمال شده باشه)
        //   3. fallback به 'post' (فید اصلی /feed/)
        $post_type = '';

        if (!empty($_GET['post_type'])) {
            $raw = wp_unslash($_GET['post_type']);
            if (is_array($raw)) {
                $raw = reset($raw);
            }
            $post_type = sanitize_key((string) $raw);
        }

        if (empty($post_type)) {
            $qv = get_query_var('post_type');
            if (is_array($qv)) {
                $qv = reset($qv);
            }
            $post_type = (string) $qv;
        }

        if (empty($post_type)) {
            $post_type = 'post';
        }

        $indexable = yj19_rss_get_indexable_post_types();
        $is_indexable = isset($indexable[$post_type]) ? '1' : '0';
        $is_enabled = yj19_rss_is_post_type_enabled($post_type) ? '1' : '0';

        if (!headers_sent()) {
            header(sprintf(
                'X-YJ19-Feed-Debug: pt=%s indexable=%s enabled=%s',
                $post_type, $is_indexable, $is_enabled
            ));
        }

        if (!isset($indexable[$post_type])) {
            return;
        }

        if (!yj19_rss_is_post_type_enabled($post_type)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_query_template('404');
            exit;
        }
    }

    /**
     * پست‌هایی که با متای _yj19_exclude_from_feed علامت خوردن از همه feedها حذف میشن.
     *
     * @param WP_Query $query
     */
    public function exclude_marked_posts($query)
    {
        if (is_admin() || !$query->is_feed()) {
            return;
        }

        $meta_query = $query->get('meta_query');
        if (!is_array($meta_query)) {
            $meta_query = array();
        }

        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key'     => YJ19_RSS_META_EXCLUDE,
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => YJ19_RSS_META_EXCLUDE,
                'value'   => '1',
                'compare' => '!=',
            ),
        );

        $query->set('meta_query', $meta_query);
    }
}
