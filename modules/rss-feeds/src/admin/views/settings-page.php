<?php
/**
 * Settings Page View
 *
 * متغیرهای ورودی:
 *   $post_types : WP_Post_Type[]  (post typeهای ایندکس‌پذیر)
 *   $settings   : array           (تنظیمات ذخیره‌شده)
 *
 * @package Yj19\RSS
 */

if (!defined('ABSPATH')) {
    exit;
}

$home_feed_url = home_url('/feed/');
?>
<div class="wrap yj19-rss-wrap">
    <h1><?php esc_html_e('تنظیمات RSS و فیدها', 'yj19-panel'); ?></h1>

    <!-- ============================================== -->
    <!--             بخش راهنما / توضیحات              -->
    <!-- ============================================== -->
    <div class="yj19-rss-help">
        <h2><?php esc_html_e('این بخش برای چیه؟ (راهنما)', 'yj19-panel'); ?></h2>

        <div class="yj19-rss-help-content">
            <p>
                <strong><?php esc_html_e('Google Discover چیه؟', 'yj19-panel'); ?></strong>
                <?php esc_html_e('یه فید شخصی‌سازی‌شده‌ست که گوگل در اپ موبایلش (و صفحه اصلی Chrome موبایل) به کاربرها نشون می‌ده. برخلاف جستجوی معمولی، اینجا کاربر چیزی سرچ نکرده — گوگل خودش بر اساس علاقمندی کاربر محتواهای جدید رو پیشنهاد می‌ده. ترافیک Discover معمولاً انفجاری و بسیار پربازدیده.', 'yj19-panel'); ?>
            </p>

            <p>
                <strong><?php esc_html_e('RSS و Feed چی هستن؟', 'yj19-panel'); ?></strong>
                <?php esc_html_e('Feed یه فایل XML استاندارده که فهرست آخرین محتواهای سایت رو منتشر می‌کنه. RSS یکی از فرمت‌های معروف Feed هست. گوگل، اپ‌های خبرخوان و ربات‌های Discover از این فایل استفاده می‌کنن تا سریع بفهمن چه محتوای جدیدی روی سایت منتشر شده.', 'yj19-panel'); ?>
            </p>

            <p>
                <strong><?php esc_html_e('چرا این تنظیمات؟', 'yj19-panel'); ?></strong>
                <?php esc_html_e('وردپرس به‌صورت پیش‌فرض برای همه post typeهای عمومی فید RSS می‌سازه. ولی همیشه نمی‌خوای همه چیز در Discover ظاهر بشه — مثلاً ممکنه post typeهای صفحات استاتیک، محصولات، یا محتوای داخلی رو نخوای در فید بیاد. اینجا می‌تونی برای هر post type جداگانه فید رو روشن/خاموش کنی.', 'yj19-panel'); ?>
            </p>

            <p>
                <strong><?php esc_html_e('چه فرقی با Sitemap داره؟', 'yj19-panel'); ?></strong>
                <?php esc_html_e('Sitemap یه نقشه کلی از همه URLهای سایته که به گوگل می‌گه "این صفحات رو ایندکس کن". Feed/RSS برعکس، یه فهرست زنده از جدیدترین محتواهاست که برای Discover و خبرخوان‌ها استفاده میشه. هر دو لازمن، ولی کاربردشون فرق داره.', 'yj19-panel'); ?>
            </p>

            <p>
                <strong><?php esc_html_e('تنظیمات سینگل پست:', 'yj19-panel'); ?></strong>
                <?php esc_html_e('وقتی فید یه post type رو روشن می‌کنی، در صفحه ویرایش هر پست از اون نوع یه چک‌باکس کوچک پیدا میشه که می‌تونی همون پست خاص رو از فید حذف کنی (بدون اینکه کل فید خاموش بشه). مفید برای پست‌های آرشیوی یا کم‌اهمیت.', 'yj19-panel'); ?>
            </p>

            <div class="yj19-rss-help-urls">
                <strong><?php esc_html_e('آدرس فیدها:', 'yj19-panel'); ?></strong>
                <ul>
                    <li><code><?php echo esc_html($home_feed_url); ?></code> — <?php esc_html_e('فید اصلی (پست‌های وبلاگ)', 'yj19-panel'); ?></li>
                    <li><code><?php echo esc_html(home_url('/?feed=rss2&post_type=POST_TYPE_SLUG')); ?></code> — <?php esc_html_e('فید مخصوص هر post type', 'yj19-panel'); ?></li>
                </ul>
                <p class="description">
                    <?php esc_html_e('post typeهایی که در پایین خاموش بشن، فید اون‌ها 404 برمی‌گردونه و گوگل از ایندکس فید حذفش می‌کنه.', 'yj19-panel'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!--          فرم تنظیمات اصلی                     -->
    <!-- ============================================== -->
    <form id="yj19-rss-form" method="post" autocomplete="off">

        <h2><?php esc_html_e('فید کدوم post typeها فعال باشه؟', 'yj19-panel'); ?></h2>
        <p class="description">
            <?php esc_html_e('فقط post typeهای عمومی و ایندکس‌پذیر اینجا لیست شدن. تیک هر کدوم رو که می‌خوای فید RSS داشته باشه بزن.', 'yj19-panel'); ?>
        </p>

        <table class="wp-list-table widefat striped yj19-rss-table">
            <thead>
                <tr>
                    <th class="column-toggle"><?php esc_html_e('فعال', 'yj19-panel'); ?></th>
                    <th class="column-name"><?php esc_html_e('post type', 'yj19-panel'); ?></th>
                    <th class="column-slug"><?php esc_html_e('شناسه (slug)', 'yj19-panel'); ?></th>
                    <th class="column-url"><?php esc_html_e('آدرس فید', 'yj19-panel'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($post_types)) : ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:20px;">
                            <?php esc_html_e('هیچ post type ایندکس‌پذیری پیدا نشد.', 'yj19-panel'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($post_types as $slug => $obj) :
                        $enabled = !empty($settings['post_types'][$slug]['enabled']);

                        // ساخت URL فید
                        if ($slug === 'post') {
                            $feed_url = home_url('/feed/');
                        } else {
                            $feed_url = home_url('/?feed=rss2&post_type=' . $slug);
                        }
                    ?>
                        <tr>
                            <td class="column-toggle">
                                <label class="yj19-rss-switch">
                                    <input type="checkbox"
                                        name="post_types[<?php echo esc_attr($slug); ?>][enabled]"
                                        value="1"
                                        <?php checked($enabled, true); ?> />
                                    <span class="yj19-rss-slider"></span>
                                </label>
                            </td>
                            <td class="column-name">
                                <strong><?php echo esc_html($obj->labels->name); ?></strong>
                            </td>
                            <td class="column-slug">
                                <code><?php echo esc_html($slug); ?></code>
                            </td>
                            <td class="column-url">
                                <a href="<?php echo esc_url($feed_url); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html($feed_url); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary" id="yj19-rss-save-btn">
                <?php esc_html_e('ذخیره تنظیمات', 'yj19-panel'); ?>
            </button>
            <span id="yj19-rss-status" class="yj19-rss-status"></span>
        </p>
    </form>
</div>
