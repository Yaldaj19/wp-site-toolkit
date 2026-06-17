<?php

/**
 * Item Configuration Template
 */

if (!defined('ABSPATH')) exit;

// Variables: $item_type, $item_name, $item_data, $config

$field_prefix = $item_type === 'post_type' ? 'post_types' : 'taxonomies';
$is_post_type = ($item_type === 'post_type');
$is_taxonomy = ($item_type === 'taxonomy');
?>

<div class="ssg-item" data-type="<?php echo esc_attr($item_type); ?>" data-name="<?php echo esc_attr($item_name); ?>">
    <div class="ssg-item-header">
        <div class="ssg-item-title">
            <label class="ssg-toggle">
                <!-- Hidden input برای ارسال 0 وقتی checkbox تیک نخورده -->
                <input type="hidden"
                    name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][enabled]"
                    value="0">
                <input
                    type="checkbox"
                    name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][enabled]"
                    value="1"
                    <?php
                    $enabled_value = isset($config['enabled']) ? $config['enabled'] : false;

                    $is_enabled = (
                        $enabled_value === true ||
                        $enabled_value === '1' ||
                        $enabled_value === 1 ||
                        $enabled_value === 'true'
                    );

                    checked($is_enabled, true);
                    ?>
                    class="ssg-enable-toggle">
                <span class="ssg-toggle-slider"></span>
            </label>
            <span class="ssg-item-label">
                <strong><?php echo esc_html($item_data['label']); ?></strong>
                <code><?php echo esc_html($item_name); ?></code>
            </span>
        </div>

        <div class="ssg-item-meta">
            <span class="ssg-count">
                <span class="dashicons dashicons-admin-post"></span>
                <?php echo number_format($item_data['count']); ?> مورد
            </span>
            <button type="button" class="ssg-toggle-settings" aria-expanded="false" title="نمایش/مخفی کردن تنظیمات">
                <span class="dashicons dashicons-arrow-down-alt2"></span>
            </button>
        </div>
    </div>

    <div class="ssg-item-settings" style="display: none;">

        <?php if ($is_post_type) : ?>
            <!-- POST TYPE: Single + Archive -->

            <div class="ssg-sub-tabs">
                <button type="button" class="ssg-sub-tab active" data-target="single-<?php echo esc_attr($item_name); ?>">
                    📄 تک پست‌ها
                </button>
                <?php if ($item_data['has_archive']) : ?>
                    <button type="button" class="ssg-sub-tab" data-target="archive-<?php echo esc_attr($item_name); ?>">
                        📦 صفحه آرشیو
                    </button>
                <?php endif; ?>
            </div>

            <!-- Single Posts Settings -->
            <div id="single-<?php echo esc_attr($item_name); ?>" class="ssg-sub-content active">
                <h4>تنظیمات تک پست‌ها</h4>
                <div class="ssg-settings-grid">

                    <!-- Priority -->
                    <div class="ssg-field">
                        <label>
                            <span class="ssg-label-text">اولویت (Priority)</span>
                            <span class="ssg-help dashicons dashicons-editor-help"
                                title="مقدار بین 0.0 تا 1.0 - هرچه بالاتر، مهم‌تر برای گوگل"></span>
                        </label>
                        <input
                            type="number"
                            name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][priority]"
                            value="<?php echo esc_attr($config['priority'] ?? 0.6); ?>"
                            min="0"
                            max="1"
                            step="0.1"
                            class="small-text">
                        <p class="description">
                            پیشنهاد:
                            <span class="ssg-suggestion" data-value="0.8">مهم: 0.8</span> |
                            <span class="ssg-suggestion" data-value="0.6">عادی: 0.6</span>
                        </p>
                    </div>

                    <!-- Changefreq -->
                    <div class="ssg-field">
                        <label>
                            <span class="ssg-label-text">تکرار تغییر (Changefreq)</span>
                            <span class="ssg-help dashicons dashicons-editor-help"
                                title="میزان تکرار بروزرسانی محتوا"></span>
                        </label>
                        <select
                            name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][changefreq]"
                            class="regular-text">
                            <option value="auto" <?php selected($config['changefreq'] ?? 'auto', 'auto'); ?>>خودکار (Auto) ⭐</option>
                            <option value="always" <?php selected($config['changefreq'] ?? 'auto', 'always'); ?>>همیشه (Always)</option>
                            <option value="hourly" <?php selected($config['changefreq'] ?? 'auto', 'hourly'); ?>>ساعتی (Hourly)</option>
                            <option value="daily" <?php selected($config['changefreq'] ?? 'auto', 'daily'); ?>>روزانه (Daily)</option>
                            <option value="weekly" <?php selected($config['changefreq'] ?? 'auto', 'weekly'); ?>>هفتگی (Weekly)</option>
                            <option value="monthly" <?php selected($config['changefreq'] ?? 'auto', 'monthly'); ?>>ماهانه (Monthly)</option>
                            <option value="yearly" <?php selected($config['changefreq'] ?? 'auto', 'yearly'); ?>>سالانه (Yearly)</option>
                            <option value="never" <?php selected($config['changefreq'] ?? 'auto', 'never'); ?>>هرگز (Never)</option>
                        </select>
                        <p class="description">
                            "خودکار" بر اساس آخرین تغییر محتوا محاسبه می‌شود.
                        </p>
                    </div>

                </div>
            </div>

            <!-- Archive Settings -->
            <?php if ($item_data['has_archive']) : ?>
                <div id="archive-<?php echo esc_attr($item_name); ?>" class="ssg-sub-content" style="display: none;">
                    <h4>تنظیمات صفحه آرشیو</h4>

                    <div class="ssg-field" style="margin-bottom: 15px;">
                        <label>
                            <input
                                type="checkbox"
                                name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][include_archive]"
                                value="1"
                                <?php checked($config['include_archive'] ?? true, true); ?>>
                            نمایش صفحه آرشیو در سایت‌مپ
                        </label>
                        <p class="description" style="margin-right: 25px;">
                            URL: <code><?php echo esc_url($item_data['archive_url'] ?? ''); ?></code>
                        </p>
                    </div>

                    <div class="ssg-settings-grid">

                        <!-- Archive Priority -->
                        <div class="ssg-field">
                            <label>
                                <span class="ssg-label-text">اولویت آرشیو</span>
                            </label>
                            <input
                                type="number"
                                name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][archive_priority]"
                                value="<?php echo esc_attr($config['archive_priority'] ?? 0.8); ?>"
                                min="0"
                                max="1"
                                step="0.1"
                                class="small-text">
                        </div>

                        <!-- Archive Changefreq -->
                        <div class="ssg-field">
                            <label>
                                <span class="ssg-label-text">تکرار تغییر آرشیو</span>
                            </label>
                            <select
                                name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][archive_changefreq]"
                                class="regular-text">
                                <option value="always" <?php selected($config['archive_changefreq'] ?? 'daily', 'always'); ?>>همیشه</option>
                                <option value="hourly" <?php selected($config['archive_changefreq'] ?? 'daily', 'hourly'); ?>>ساعتی</option>
                                <option value="daily" <?php selected($config['archive_changefreq'] ?? 'daily', 'daily'); ?>>روزانه ⭐</option>
                                <option value="weekly" <?php selected($config['archive_changefreq'] ?? 'daily', 'weekly'); ?>>هفتگی</option>
                                <option value="monthly" <?php selected($config['archive_changefreq'] ?? 'daily', 'monthly'); ?>>ماهانه</option>
                                <option value="yearly" <?php selected($config['archive_changefreq'] ?? 'daily', 'yearly'); ?>>سالانه</option>
                                <option value="never" <?php selected($config['archive_changefreq'] ?? 'daily', 'never'); ?>>هرگز</option>
                            </select>
                        </div>

                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($is_taxonomy) : ?>
            <!-- TAXONOMY: Landing + Terms -->

            <div class="ssg-sub-tabs">
                <button type="button" class="ssg-sub-tab active" data-target="landing-<?php echo esc_attr($item_name); ?>">
                    🏠 صفحه لندینگ
                </button>
                <button type="button" class="ssg-sub-tab" data-target="terms-<?php echo esc_attr($item_name); ?>">
                    🏷️ تک Term ها
                </button>
            </div>

            <!-- Landing Page Settings -->
            <div id="landing-<?php echo esc_attr($item_name); ?>" class="ssg-sub-content active">
                <h4>تنظیمات صفحه لندینگ</h4>

                <div class="ssg-field" style="margin-bottom: 15px;">
                    <label>
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][include_landing]"
                            value="1"
                            <?php checked($config['include_landing'] ?? true, true); ?>>
                        نمایش صفحه لندینگ در سایت‌مپ
                    </label>
                    <p class="description" style="margin-right: 25px;">
                        URL: <code><?php echo esc_url($item_data['landing_url'] ?? ''); ?></code>
                    </p>
                </div>

                <div class="ssg-settings-grid">

                    <!-- Landing Priority -->
                    <div class="ssg-field">
                        <label>
                            <span class="ssg-label-text">اولویت لندینگ</span>
                        </label>
                        <input
                            type="number"
                            name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][landing_priority]"
                            value="<?php echo esc_attr($config['landing_priority'] ?? 0.7); ?>"
                            min="0"
                            max="1"
                            step="0.1"
                            class="small-text">
                    </div>

                    <!-- Landing Changefreq -->
                    <div class="ssg-field">
                        <label>
                            <span class="ssg-label-text">تکرار تغییر لندینگ</span>
                        </label>
                        <select
                            name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][landing_changefreq]"
                            class="regular-text">
                            <option value="always" <?php selected($config['landing_changefreq'] ?? 'daily', 'always'); ?>>همیشه</option>
                            <option value="hourly" <?php selected($config['landing_changefreq'] ?? 'daily', 'hourly'); ?>>ساعتی</option>
                            <option value="daily" <?php selected($config['landing_changefreq'] ?? 'daily', 'daily'); ?>>روزانه ⭐</option>
                            <option value="weekly" <?php selected($config['landing_changefreq'] ?? 'daily', 'weekly'); ?>>هفتگی</option>
                            <option value="monthly" <?php selected($config['landing_changefreq'] ?? 'daily', 'monthly'); ?>>ماهانه</option>
                            <option value="yearly" <?php selected($config['landing_changefreq'] ?? 'daily', 'yearly'); ?>>سالانه</option>
                            <option value="never" <?php selected($config['landing_changefreq'] ?? 'daily', 'never'); ?>>هرگز</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- Individual Terms Settings -->
            <div id="terms-<?php echo esc_attr($item_name); ?>" class="ssg-sub-content" style="display: none;">
                <h4>تنظیمات تک Term ها</h4>
                <div class="ssg-settings-grid">

                    <!-- Priority -->
                    <div class="ssg-field">
                        <label>
                            <span class="ssg-label-text">اولویت (Priority)</span>
                        </label>
                        <input
                            type="number"
                            name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][priority]"
                            value="<?php echo esc_attr($config['priority'] ?? 0.6); ?>"
                            min="0"
                            max="1"
                            step="0.1"
                            class="small-text">
                        <p class="description">
                            پیشنهاد:
                            <span class="ssg-suggestion" data-value="0.6">دسته: 0.6</span> |
                            <span class="ssg-suggestion" data-value="0.5">تگ: 0.5</span>
                        </p>
                    </div>

                    <!-- Changefreq -->
                    <div class="ssg-field">
                        <label>
                            <span class="ssg-label-text">تکرار تغییر (Changefreq)</span>
                        </label>
                        <select
                            name="<?php echo esc_attr($field_prefix); ?>[<?php echo esc_attr($item_name); ?>][changefreq]"
                            class="regular-text">
                            <option value="auto" <?php selected($config['changefreq'] ?? 'auto', 'auto'); ?>>خودکار (Auto) ⭐</option>
                            <option value="always" <?php selected($config['changefreq'] ?? 'auto', 'always'); ?>>همیشه</option>
                            <option value="hourly" <?php selected($config['changefreq'] ?? 'auto', 'hourly'); ?>>ساعتی</option>
                            <option value="daily" <?php selected($config['changefreq'] ?? 'auto', 'daily'); ?>>روزانه</option>
                            <option value="weekly" <?php selected($config['changefreq'] ?? 'auto', 'weekly'); ?>>هفتگی</option>
                            <option value="monthly" <?php selected($config['changefreq'] ?? 'auto', 'monthly'); ?>>ماهانه</option>
                            <option value="yearly" <?php selected($config['changefreq'] ?? 'auto', 'yearly'); ?>>سالانه</option>
                            <option value="never" <?php selected($config['changefreq'] ?? 'auto', 'never'); ?>>هرگز</option>
                        </select>
                    </div>

                </div>
            </div>

        <?php endif; ?>

        <!-- Additional Info -->
        <div class="ssg-item-info">
            <?php if ($is_post_type) : ?>
                <?php if ($item_data['has_archive']) : ?>
                    <span class="ssg-badge ssg-badge-archive" title="این Post Type صفحه آرشیو دارد">
                        <span class="dashicons dashicons-archive"></span>
                        دارای صفحه آرشیو
                    </span>
                <?php endif; ?>

                <?php
                $taxonomies = get_object_taxonomies($item_name);
                if (!empty($taxonomies)) :
                ?>
                    <span class="ssg-badge ssg-badge-tax"
                        title="Taxonomy های مرتبط: <?php echo esc_attr(implode(', ', $taxonomies)); ?>">
                        <span class="dashicons dashicons-category"></span>
                        <?php echo count($taxonomies); ?> Taxonomy مرتبط
                    </span>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($is_taxonomy) : ?>
                <?php if (!empty($item_data['object_type'])) : ?>
                    <span class="ssg-badge ssg-badge-related"
                        title="مرتبط با این Post Type ها">
                        <span class="dashicons dashicons-admin-links"></span>
                        مرتبط با: <?php echo esc_html(implode(', ', array_slice($item_data['object_type'], 0, 3))); ?>
                        <?php if (count($item_data['object_type']) > 3) : ?>
                            و <?php echo count($item_data['object_type']) - 3; ?> مورد دیگر
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            <?php endif; ?>

            <span class="ssg-badge ssg-badge-url">
                <span class="dashicons dashicons-admin-links"></span>
                <a href="<?php echo esc_url(ssg_get_sitemap_url($item_name)); ?>" target="_blank" title="مشاهده سایت‌مپ این بخش">
                    مشاهده سایت‌مپ
                </a>
            </span>

            <?php if (($config['enabled'] ?? false) === false) : ?>
                <span class="ssg-badge ssg-badge-disabled" style="background: #dc3232; color: white;">
                    <span class="dashicons dashicons-warning"></span>
                    غیرفعال
                </span>
            <?php endif; ?>
        </div>

        <!-- Help Text -->
        <div class="ssg-item-help">
            <p class="description">
                <strong>💡 راهنما:</strong>
                <?php if ($is_post_type) : ?>
                    این بخش تمام <?php echo esc_html($item_data['label']); ?> منتشرشده را در سایت‌مپ قرار می‌دهد.
                    <?php if ($item_data['has_archive']) : ?>
                        صفحه آرشیو نیز به صورت خودکار اضافه می‌شود.
                    <?php endif; ?>
                <?php else : ?>
                    این بخش تمام ترم‌های (دسته‌ها، برچسب‌ها) <?php echo esc_html($item_data['label']); ?> را در سایت‌مپ قرار می‌دهد.
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>