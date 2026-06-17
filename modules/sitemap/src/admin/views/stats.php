<?php

/**
 * Statistics View
 * Statistics and reports page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ✅ چک کردن وجود متغیرها
if (!isset($stats) || !isset($validation) || !isset($sitemap_urls)) {
?>
    <div class="wrap">
        <h1>آمار و گزارشات</h1>
        <div class="notice notice-error">
            <p><strong>خطا:</strong> داده‌های آماری یافت نشد. لطفاً صفحه را رفرش کنید.</p>
        </div>
    </div>
<?php
    return;
}
?>

<div class="wrap ssg-stats">
    <h1>
        <?php echo esc_html(get_admin_page_title()); ?>
        <span class="ssg-version">نسخه <?php echo SSG_VERSION; ?></span>
    </h1>

    <!-- Validation Status -->
    <div class="ssg-card ssg-validation-card">
        <h2>
            <span class="dashicons dashicons-shield-alt"></span>
            وضعیت اعتبارسنجی
        </h2>

        <?php if ($validation['valid']) : ?>
            <div class="notice notice-success inline">
                <p>
                    <strong>✅ سایت‌مپ شما سالم است!</strong>
                    همه چیز به درستی کار می‌کند.
                </p>
            </div>
        <?php else : ?>
            <div class="notice notice-error inline">
                <p><strong>❌ مشکلاتی یافت شد:</strong></p>
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach ($validation['errors'] as $error) : ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($validation['warnings'])) : ?>
            <div class="notice notice-warning inline">
                <p><strong>⚠️ هشدارها:</strong></p>
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach ($validation['warnings'] as $warning) : ?>
                        <li><?php echo esc_html($warning); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($validation['info'])) : ?>
            <div class="notice notice-info inline">
                <p><strong>ℹ️ اطلاعات:</strong></p>
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach ($validation['info'] as $info) : ?>
                        <li><?php echo esc_html($info); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- Statistics Grid -->
    <div class="ssg-stats-grid">

        <!-- Homepage Stats -->
        <div class="ssg-card">
            <h3>
                <span class="dashicons dashicons-admin-home"></span>
                صفحه اصلی
            </h3>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number <?php echo $stats['homepage']['enabled'] ? 'ssg-stat-active' : ''; ?>">
                    <?php echo $stats['homepage']['enabled'] ? '✓' : '✗'; ?>
                </div>
                <div class="ssg-stat-label">
                    <?php echo $stats['homepage']['enabled'] ? 'فعال' : 'غیرفعال'; ?>
                </div>
            </div>
            <?php if ($stats['homepage']['enabled']) : ?>
                <p style="text-align: center; margin-top: 10px; font-size: 12px;">
                    <code><?php echo esc_url($stats['homepage']['url']); ?></code>
                </p>
            <?php endif; ?>
        </div>

        <!-- Post Types Stats -->
        <div class="ssg-card">
            <h3>
                <span class="dashicons dashicons-admin-post"></span>
                آمار محتوا (Post Types)
            </h3>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number"><?php echo esc_html($stats['post_types']['total']); ?></div>
                <div class="ssg-stat-label">کل Post Types</div>
            </div>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number ssg-stat-active"><?php echo esc_html($stats['post_types']['enabled']); ?></div>
                <div class="ssg-stat-label">فعال شده</div>
            </div>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number" style="font-size: 24px;"><?php echo number_format($stats['post_types']['total_items']); ?></div>
                <div class="ssg-stat-label">تعداد کل آیتم‌ها</div>
            </div>

            <?php if (!empty($stats['post_types']['items'])) : ?>
                <div class="ssg-stat-items">
                    <h4>جزئیات:</h4>
                    <ul>
                        <?php foreach ($stats['post_types']['items'] as $type => $data) : ?>
                            <li>
                                <strong><?php echo esc_html($data['label']); ?> <code><?php echo esc_html($type); ?></code>:</strong>
                                <span><?php echo number_format($data['count']); ?> مورد</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else : ?>
                <p class="ssg-no-items">هیچ Post Type فعالی وجود ندارد</p>
            <?php endif; ?>
        </div>

        <!-- Taxonomies Stats -->
        <div class="ssg-card">
            <h3>
                <span class="dashicons dashicons-category"></span>
                آمار دسته‌بندی (Taxonomies)
            </h3>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number"><?php echo esc_html($stats['taxonomies']['total']); ?></div>
                <div class="ssg-stat-label">کل Taxonomies</div>
            </div>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number ssg-stat-active"><?php echo esc_html($stats['taxonomies']['enabled']); ?></div>
                <div class="ssg-stat-label">فعال شده</div>
            </div>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number" style="font-size: 24px;"><?php echo number_format($stats['taxonomies']['total_items']); ?></div>
                <div class="ssg-stat-label">تعداد کل Term ها</div>
            </div>

            <?php if (!empty($stats['taxonomies']['items'])) : ?>
                <div class="ssg-stat-items">
                    <h4>جزئیات:</h4>
                    <ul>
                        <?php foreach ($stats['taxonomies']['items'] as $taxonomy => $data) : ?>
                            <li>
                                <strong><?php echo esc_html($data['label']); ?> <code><?php echo esc_html($taxonomy); ?></code>:</strong>
                                <span><?php echo number_format($data['count']); ?> مورد</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else : ?>
                <p class="ssg-no-items">هیچ Taxonomy فعالی وجود ندارد</p>
            <?php endif; ?>
        </div>

        <!-- Authors Stats -->
        <div class="ssg-card">
            <h3>
                <span class="dashicons dashicons-admin-users"></span>
                نویسندگان
            </h3>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number <?php echo $stats['authors']['enabled'] ? 'ssg-stat-active' : ''; ?>">
                    <?php echo $stats['authors']['enabled'] ? '✓' : '✗'; ?>
                </div>
                <div class="ssg-stat-label">
                    <?php echo $stats['authors']['enabled'] ? 'فعال' : 'غیرفعال'; ?>
                </div>
            </div>

            <?php if ($stats['authors']['enabled']) : ?>
                <div class="ssg-stat-box">
                    <div class="ssg-stat-number"><?php echo count($stats['authors']['items']); ?></div>
                    <div class="ssg-stat-label">تعداد نویسندگان</div>
                </div>

                <?php if (!empty($stats['authors']['items'])) : ?>
                    <div class="ssg-stat-items">
                        <h4>لیست نویسندگان:</h4>
                        <ul>
                            <?php foreach ($stats['authors']['items'] as $author_id => $author) : ?>
                                <li>
                                    <strong><?php echo esc_html($author['name']); ?></strong>
                                    <span><?php echo esc_html($author['posts']); ?> پست</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Date Archives Stats -->
        <div class="ssg-card">
            <h3>
                <span class="dashicons dashicons-calendar-alt"></span>
                آرشیو تاریخ
            </h3>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number <?php echo $stats['dates']['enabled'] ? 'ssg-stat-active' : ''; ?>">
                    <?php echo $stats['dates']['enabled'] ? '✓' : '✗'; ?>
                </div>
                <div class="ssg-stat-label">
                    <?php echo $stats['dates']['enabled'] ? 'فعال' : 'غیرفعال'; ?>
                </div>
            </div>

            <?php if ($stats['dates']['enabled']) : ?>
                <div class="ssg-stat-items">
                    <h4>جزئیات:</h4>
                    <ul>
                        <?php if ($stats['dates']['yearly'] > 0) : ?>
                            <li>
                                <strong>سالانه:</strong>
                                <span><?php echo number_format($stats['dates']['yearly']); ?> مورد</span>
                            </li>
                        <?php endif; ?>
                        <?php if ($stats['dates']['monthly'] > 0) : ?>
                            <li>
                                <strong>ماهانه:</strong>
                                <span><?php echo number_format($stats['dates']['monthly']); ?> مورد</span>
                            </li>
                        <?php endif; ?>
                        <?php if ($stats['dates']['daily'] > 0) : ?>
                            <li>
                                <strong>روزانه:</strong>
                                <span><?php echo number_format($stats['dates']['daily']); ?> مورد</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cache Stats -->
        <div class="ssg-card">
            <h3>
                <span class="dashicons dashicons-database"></span>
                آمار کش
            </h3>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number"><?php echo esc_html($stats['cache']['total_cached']); ?></div>
                <div class="ssg-stat-label">تعداد کش‌ها</div>
            </div>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number"><?php echo esc_html($stats['cache']['total_size_formatted']); ?></div>
                <div class="ssg-stat-label">حجم کل</div>
            </div>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number"><?php echo esc_html($stats['cache']['duration_formatted']); ?></div>
                <div class="ssg-stat-label">مدت اعتبار</div>
            </div>

            <?php if (!empty($stats['cache']['items'])) : ?>
                <div class="ssg-cache-items">
                    <h4>جزئیات کش:</h4>
                    <table class="widefat" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>نوع</th>
                                <th>حجم</th>
                                <th>انقضا</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['cache']['items'] as $type => $item) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($type); ?></code></td>
                                    <td><?php echo size_format($item['size']); ?></td>
                                    <td>
                                        <?php
                                        if ($item['time_remaining']) {
                                            echo human_time_diff(time(), $item['expiration']);
                                        } else {
                                            echo '<span style="color: #dc3232;">منقضی شده</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p class="ssg-no-cache">هیچ کشی موجود نیست</p>
            <?php endif; ?>
        </div>

        <!-- Generation Method Stats -->
        <div class="ssg-card">
            <h3>
                <span class="dashicons dashicons-admin-settings"></span>
                روش تولید
            </h3>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number ssg-stat-active">
                    <?php echo $stats['generation']['dynamic'] ? '✓' : '✗'; ?>
                </div>
                <div class="ssg-stat-label">Dynamic (پویا)</div>
            </div>
            <div class="ssg-stat-box">
                <div class="ssg-stat-number <?php echo $stats['generation']['static'] ? 'ssg-stat-active' : ''; ?>">
                    <?php echo $stats['generation']['static'] ? '✓' : '✗'; ?>
                </div>
                <div class="ssg-stat-label">Static (استاتیک)</div>
            </div>

            <?php if ($stats['generation']['static']) : ?>
                <p style="text-align: center; margin-top: 10px; font-size: 12px;">
                    <strong>وضعیت فایل:</strong>
                    <?php if ($stats['generation']['static_file_exists']) : ?>
                        <span style="color: #46b450;">✓ فایل وجود دارد</span>
                    <?php else : ?>
                        <span style="color: #dc3232;">✗ فایل وجود ندارد</span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

    </div>

    <!-- Sitemap URLs -->
    <div class="ssg-card">
        <h2>
            <span class="dashicons dashicons-admin-links"></span>
            لینک‌های سایت‌مپ
        </h2>

        <div class="ssg-url-section">
            <h3>سایت‌مپ اصلی</h3>
            <div class="ssg-url-box">
                <input
                    type="text"
                    value="<?php echo esc_url($sitemap_urls['main']); ?>"
                    readonly
                    class="large-text ssg-url-input">
                <button type="button" class="button ssg-copy-url" data-url="<?php echo esc_url($sitemap_urls['main']); ?>">
                    <span class="dashicons dashicons-admin-page"></span>
                    کپی
                </button>
                <a href="<?php echo esc_url($sitemap_urls['main']); ?>" class="button" target="_blank">
                    <span class="dashicons dashicons-external"></span>
                    مشاهده
                </a>
            </div>
        </div>

        <?php if ($sitemap_urls['homepage']) : ?>
            <div class="ssg-url-section">
                <h3>صفحه اصلی</h3>
                <div class="ssg-url-item">
                    <span class="ssg-url-label">Homepage:</span>
                    <input type="text" value="<?php echo esc_url($sitemap_urls['homepage']); ?>" readonly class="regular-text ssg-url-input">
                    <button type="button" class="button button-small ssg-copy-url" data-url="<?php echo esc_url($sitemap_urls['homepage']); ?>">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                    <a href="<?php echo esc_url($sitemap_urls['homepage']); ?>" class="button button-small" target="_blank">
                        <span class="dashicons dashicons-external"></span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($sitemap_urls['post_types'])) : ?>
            <div class="ssg-url-section">
                <h3>Post Types</h3>
                <?php foreach ($sitemap_urls['post_types'] as $type => $url) : ?>
                    <div class="ssg-url-item">
                        <span class="ssg-url-label"><?php echo esc_html($type); ?>:</span>
                        <input type="text" value="<?php echo esc_url($url); ?>" readonly class="regular-text ssg-url-input">
                        <button type="button" class="button button-small ssg-copy-url" data-url="<?php echo esc_url($url); ?>">
                            <span class="dashicons dashicons-admin-page"></span>
                        </button>
                        <a href="<?php echo esc_url($url); ?>" class="button button-small" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sitemap_urls['taxonomies'])) : ?>
            <div class="ssg-url-section">
                <h3>Taxonomies</h3>
                <?php foreach ($sitemap_urls['taxonomies'] as $taxonomy => $url) : ?>
                    <div class="ssg-url-item">
                        <span class="ssg-url-label"><?php echo esc_html($taxonomy); ?>:</span>
                        <input type="text" value="<?php echo esc_url($url); ?>" readonly class="regular-text ssg-url-input">
                        <button type="button" class="button button-small ssg-copy-url" data-url="<?php echo esc_url($url); ?>">
                            <span class="dashicons dashicons-admin-page"></span>
                        </button>
                        <a href="<?php echo esc_url($url); ?>" class="button button-small" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($sitemap_urls['authors']) : ?>
            <div class="ssg-url-section">
                <h3>نویسندگان</h3>
                <div class="ssg-url-item">
                    <span class="ssg-url-label">Authors:</span>
                    <input type="text" value="<?php echo esc_url($sitemap_urls['authors']); ?>" readonly class="regular-text ssg-url-input">
                    <button type="button" class="button button-small ssg-copy-url" data-url="<?php echo esc_url($sitemap_urls['authors']); ?>">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                    <a href="<?php echo esc_url($sitemap_urls['authors']); ?>" class="button button-small" target="_blank">
                        <span class="dashicons dashicons-external"></span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($sitemap_urls['dates']) : ?>
            <div class="ssg-url-section">
                <h3>آرشیو تاریخ</h3>
                <div class="ssg-url-item">
                    <span class="ssg-url-label">Dates:</span>
                    <input type="text" value="<?php echo esc_url($sitemap_urls['dates']); ?>" readonly class="regular-text ssg-url-input">
                    <button type="button" class="button button-small ssg-copy-url" data-url="<?php echo esc_url($sitemap_urls['dates']); ?>">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                    <a href="<?php echo esc_url($sitemap_urls['dates']); ?>" class="button button-small" target="_blank">
                        <span class="dashicons dashicons-external"></span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Search Console Integration -->
    <div class="ssg-card ssg-info-card">
        <h2>
            <span class="dashicons dashicons-google"></span>
            راهنمای Google Search Console
        </h2>
        <div class="ssg-instructions">
            <p>برای ثبت سایت‌مپ در گوگل:</p>
            <ol>
                <li>به <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a> بروید</li>
                <li>سایت خود را انتخاب کنید</li>
                <li>از منوی سمت چپ، گزینه "Sitemaps" را انتخاب کنید</li>
                <li>لینک سایت‌مپ اصلی را وارد کنید: <code><?php echo esc_url($sitemap_urls['main']); ?></code></li>
                <li>روی دکمه "Submit" کلیک کنید</li>
            </ol>
            <p class="description">
                <strong>نکته:</strong> معمولاً 24 تا 48 ساعت طول می‌کشد تا گوگل سایت‌مپ شما را پردازش کند.
            </p>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Copy URL to clipboard
        $('.ssg-copy-url').on('click', function() {
            var url = $(this).data('url');
            var $button = $(this);
            var originalHtml = $button.html();

            // Create temporary input
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(url).select();

            try {
                document.execCommand('copy');
                $button.html('<span class="dashicons dashicons-yes"></span> کپی شد!');

                setTimeout(function() {
                    $button.html(originalHtml);
                }, 2000);
            } catch (err) {
                alert('خطا در کپی کردن');
            }

            $temp.remove();
        });
    });
</script>