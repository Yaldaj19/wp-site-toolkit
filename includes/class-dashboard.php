<?php
/**
 * صفحه‌ی داشبورد ابزارها — کارت هر ماژول
 *
 * @package WPSiteToolkit
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Site_Toolkit_Dashboard
{
    /**
     * لیست ماژول‌های ثبت‌شده. ماژول‌ها می‌تونن با فیلتر
     * `wp_site_toolkit_modules` خودشون رو اضافه کنن.
     */
    private static function modules()
    {
        $modules = array(
            'pwa' => array(
                'title' => __('PWA — وب‌اپ نصب‌شونده', 'wp-site-toolkit'),
                'desc'  => __('تبدیل سایت به Progressive Web App با manifest پویا، service worker و دکمه نصب روی موبایل.', 'wp-site-toolkit'),
                'icon'  => 'dashicons-smartphone',
                'page'  => 'yj19-pwa',
                'active' => defined('YJ19_PWA_VERSION'),
            ),
            'rss-feeds' => array(
                'title' => __('RSS / فیدها', 'wp-site-toolkit'),
                'desc'  => __('مدیریت فیدهای RSS برای Google Discover و اپ‌های خوانش فید، با امکان استثنا کردن نوشته‌ها.', 'wp-site-toolkit'),
                'icon'  => 'dashicons-rss',
                'page'  => 'yj19-rss-feeds',
                'active' => defined('YJ19_RSS_VERSION'),
            ),
            'sitemap' => array(
                'title' => __('نقشه سایت (Sitemap)', 'wp-site-toolkit'),
                'desc'  => __('تولید XML Sitemap حرفه‌ای برای همه‌ی Post Typeها، Taxonomyها و محصولات ووکامرس.', 'wp-site-toolkit'),
                'icon'  => 'dashicons-networking',
                'page'  => 'seo-sitemap-generator',
                'active' => defined('SSG_VERSION'),
            ),
        );

        return apply_filters('wp_site_toolkit_modules', $modules);
    }

    public static function render()
    {
        if (!current_user_can(WP_SITE_TOOLKIT_CAP)) {
            return;
        }
        $modules = self::modules();
        ?>
        <div class="wrap wp-site-toolkit-wrap">
            <h1 class="rt-title">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e('ابزارهای سایت', 'wp-site-toolkit'); ?>
            </h1>
            <p class="rt-subtitle">
                <?php esc_html_e('مجموعه ابزارهای سایت در یک پنل واحد و ماژولار. هر ابزار رو از کارت زیر باز کن.', 'wp-site-toolkit'); ?>
            </p>

            <div class="rt-grid">
                <?php foreach ($modules as $mod) :
                    $url = admin_url('admin.php?page=' . rawurlencode($mod['page']));
                    ?>
                    <div class="rt-card<?php echo empty($mod['active']) ? ' rt-card--off' : ''; ?>">
                        <div class="rt-card__icon">
                            <span class="dashicons <?php echo esc_attr($mod['icon']); ?>"></span>
                        </div>
                        <h2 class="rt-card__title"><?php echo esc_html($mod['title']); ?></h2>
                        <p class="rt-card__desc"><?php echo esc_html($mod['desc']); ?></p>
                        <div class="rt-card__footer">
                            <?php if (!empty($mod['active'])) : ?>
                                <a class="button button-primary" href="<?php echo esc_url($url); ?>">
                                    <?php esc_html_e('باز کردن', 'wp-site-toolkit'); ?>
                                </a>
                                <span class="rt-badge rt-badge--on"><?php esc_html_e('فعال', 'wp-site-toolkit'); ?></span>
                            <?php else : ?>
                                <span class="rt-badge rt-badge--off"><?php esc_html_e('بارگذاری نشد', 'wp-site-toolkit'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="rt-footnote">
                <?php
                printf(
                    /* translators: %s: plugin version */
                    esc_html__('WP Site Toolkit نسخه %s — برای افزودن ابزار جدید کافیست یک پوشه در modules/ بسازید.', 'wp-site-toolkit'),
                    esc_html(WP_SITE_TOOLKIT_VERSION)
                );
                ?>
            </p>
        </div>
        <?php
    }
}
