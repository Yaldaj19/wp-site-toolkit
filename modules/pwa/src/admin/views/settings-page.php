<?php
/**
 * YJ19 PWA — Admin Settings Page View
 *
 * متغیرهای موجود از class-admin.php:
 *  $settings, $manifest_url, $sw_url
 *
 * @package Yj19\PWA
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap yj19-pwa-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-smartphone" style="font-size:28px;vertical-align:middle;margin-left:6px;"></span>
        <?php esc_html_e('تنظیمات PWA (اپ موبایل)', 'yj19-panel'); ?>
    </h1>

    <?php settings_errors('yj19_pwa_messages'); ?>

    <p class="description" style="max-width:780px;">
        <?php esc_html_e('این بخش سایت رو به یک Progressive Web App تبدیل می‌کنه. کاربران موبایل می‌تونن سایت رو به‌عنوان اپلیکیشن نصب کنن و بدون اپ‌نویسی، تجربه‌ی نزدیک به اپ native داشته باشن.', 'yj19-panel'); ?>
    </p>

    <form method="post" action="">
        <?php wp_nonce_field(YJ19_PWA_Admin::NONCE_ACTION, YJ19_PWA_Admin::NONCE_NAME); ?>

        <nav class="yj19-pwa-tabs" role="tablist">
            <a href="#tab-general" class="yj19-pwa-tab active" data-target="tab-general">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e('عمومی', 'yj19-panel'); ?>
            </a>
            <a href="#tab-button" class="yj19-pwa-tab" data-target="tab-button">
                <span class="dashicons dashicons-button"></span>
                <?php esc_html_e('دکمه نصب', 'yj19-panel'); ?>
            </a>
            <a href="#tab-placements" class="yj19-pwa-tab" data-target="tab-placements">
                <span class="dashicons dashicons-layout"></span>
                <?php esc_html_e('محل قرارگیری', 'yj19-panel'); ?>
            </a>
            <a href="#tab-behavior" class="yj19-pwa-tab" data-target="tab-behavior">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php esc_html_e('رفتار', 'yj19-panel'); ?>
            </a>
        </nav>

        <!-- ============================ تب عمومی ============================ -->
        <div id="tab-general" class="yj19-pwa-tab-pane active">

            <div class="yj19-pwa-card">
                <h2><?php esc_html_e('فعال‌سازی', 'yj19-panel'); ?></h2>
                <label class="yj19-pwa-switch">
                    <input type="checkbox" name="yj19_pwa[enabled]" value="1" <?php checked($settings['enabled'], 1); ?>>
                    <span class="yj19-pwa-switch-slider"></span>
                    <span class="yj19-pwa-switch-label"><?php esc_html_e('فعال‌سازی PWA روی سایت', 'yj19-panel'); ?></span>
                </label>
                <p class="description"><?php esc_html_e('بعد از فعال‌سازی، manifest و service worker روی سایت سرو می‌شن.', 'yj19-panel'); ?></p>
            </div>

            <div class="yj19-pwa-card">
                <h2><?php esc_html_e('اطلاعات اپلیکیشن', 'yj19-panel'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><label for="yj19_pwa_app_name"><?php esc_html_e('نام اپلیکیشن', 'yj19-panel'); ?></label></th>
                        <td>
                            <input type="text" id="yj19_pwa_app_name" name="yj19_pwa[app_name]"
                                value="<?php echo esc_attr($settings['app_name']); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('نامی که زیر آیکن اپ روی home screen نمایش داده می‌شه.', 'yj19-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="yj19_pwa_short_name"><?php esc_html_e('نام کوتاه', 'yj19-panel'); ?></label></th>
                        <td>
                            <input type="text" id="yj19_pwa_short_name" name="yj19_pwa[short_name]"
                                value="<?php echo esc_attr($settings['short_name']); ?>" class="regular-text" maxlength="14">
                            <p class="description"><?php esc_html_e('حداکثر ۱۴ کاراکتر — برای فضاهای محدود.', 'yj19-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="yj19_pwa_description"><?php esc_html_e('توضیحات', 'yj19-panel'); ?></label></th>
                        <td>
                            <input type="text" id="yj19_pwa_description" name="yj19_pwa[description]"
                                value="<?php echo esc_attr($settings['description']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="yj19_pwa_start_url"><?php esc_html_e('Start URL', 'yj19-panel'); ?></label></th>
                        <td>
                            <input type="text" id="yj19_pwa_start_url" name="yj19_pwa[start_url]"
                                value="<?php echo esc_attr($settings['start_url']); ?>" class="regular-text" placeholder="/">
                            <p class="description"><?php esc_html_e('وقتی اپ از home screen باز شد، به این مسیر هدایت می‌شه.', 'yj19-panel'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="yj19-pwa-card">
                <h2><?php esc_html_e('آیکن‌ها', 'yj19-panel'); ?></h2>
                <p class="description"><?php esc_html_e('آیکن مربعی PNG با پس‌زمینه شفاف توصیه می‌شه. اگه ست نشن، دکمه نصب در مرورگرهای کروم/اج نمایش داده نمی‌شه.', 'yj19-panel'); ?></p>

                <div class="yj19-pwa-media-row">
                    <div>
                        <label><?php esc_html_e('آیکن ۱۹۲×۱۹۲', 'yj19-panel'); ?></label>
                        <div class="yj19-pwa-media-field">
                            <input type="text" name="yj19_pwa[icon_192]" data-preview="icon-192-preview"
                                value="<?php echo esc_attr($settings['icon_192']); ?>" class="regular-text">
                            <button type="button" class="button yj19-pwa-upload" data-target="icon-192-preview"><?php esc_html_e('انتخاب', 'yj19-panel'); ?></button>
                            <button type="button" class="button yj19-pwa-remove" data-target="icon-192-preview"><?php esc_html_e('حذف', 'yj19-panel'); ?></button>
                        </div>
                        <div class="yj19-pwa-media-preview" id="icon-192-preview">
                            <?php if (!empty($settings['icon_192'])) : ?>
                                <img src="<?php echo esc_url($settings['icon_192']); ?>" alt="">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label><?php esc_html_e('آیکن ۵۱۲×۵۱۲', 'yj19-panel'); ?></label>
                        <div class="yj19-pwa-media-field">
                            <input type="text" name="yj19_pwa[icon_512]" data-preview="icon-512-preview"
                                value="<?php echo esc_attr($settings['icon_512']); ?>" class="regular-text">
                            <button type="button" class="button yj19-pwa-upload" data-target="icon-512-preview"><?php esc_html_e('انتخاب', 'yj19-panel'); ?></button>
                            <button type="button" class="button yj19-pwa-remove" data-target="icon-512-preview"><?php esc_html_e('حذف', 'yj19-panel'); ?></button>
                        </div>
                        <div class="yj19-pwa-media-preview" id="icon-512-preview">
                            <?php if (!empty($settings['icon_512'])) : ?>
                                <img src="<?php echo esc_url($settings['icon_512']); ?>" alt="">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="yj19-pwa-card">
                <h2><?php esc_html_e('رنگ‌های اپ', 'yj19-panel'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label><?php esc_html_e('Theme Color', 'yj19-panel'); ?></label></th>
                        <td>
                            <input type="text" name="yj19_pwa[theme_color]" class="yj19-pwa-color"
                                value="<?php echo esc_attr($settings['theme_color']); ?>">
                            <p class="description"><?php esc_html_e('رنگ navbar مرورگر و splash screen.', 'yj19-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php esc_html_e('Background Color', 'yj19-panel'); ?></label></th>
                        <td>
                            <input type="text" name="yj19_pwa[background_color]" class="yj19-pwa-color"
                                value="<?php echo esc_attr($settings['background_color']); ?>">
                            <p class="description"><?php esc_html_e('رنگ پس‌زمینه splash screen هنگام لود.', 'yj19-panel'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ============================ تب دکمه نصب ============================ -->
        <div id="tab-button" class="yj19-pwa-tab-pane">
            <div class="yj19-pwa-card yj19-pwa-grid-2">
                <div>
                    <h2><?php esc_html_e('متن و آیکن دکمه', 'yj19-panel'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><label><?php esc_html_e('متن دکمه', 'yj19-panel'); ?></label></th>
                            <td>
                                <input type="text" id="yj19_pwa_button_text" name="yj19_pwa[button_text]"
                                    value="<?php echo esc_attr($settings['button_text']); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php esc_html_e('آیکن دکمه', 'yj19-panel'); ?></label></th>
                            <td>
                                <p style="margin:0 0 6px;font-weight:500;"><?php esc_html_e('روش ۱ — فایل (URL):', 'yj19-panel'); ?></p>
                                <input type="text" name="yj19_pwa[button_icon]" data-preview="btn-icon-preview"
                                    value="<?php echo esc_attr($settings['button_icon']); ?>" class="regular-text">
                                <button type="button" class="button yj19-pwa-upload" data-target="btn-icon-preview"><?php esc_html_e('انتخاب SVG/PNG', 'yj19-panel'); ?></button>
                                <button type="button" class="button yj19-pwa-remove" data-target="btn-icon-preview"><?php esc_html_e('حذف', 'yj19-panel'); ?></button>
                                <div class="yj19-pwa-media-preview yj19-pwa-icon-preview" id="btn-icon-preview">
                                    <?php if (!empty($settings['button_icon'])) : ?>
                                        <img src="<?php echo esc_url($settings['button_icon']); ?>" alt="">
                                    <?php endif; ?>
                                </div>

                                <p style="margin:14px 0 6px;font-weight:500;"><?php esc_html_e('روش ۲ — کد SVG inline (اولویت بالاتر):', 'yj19-panel'); ?></p>
                                <textarea name="yj19_pwa[button_icon_svg]" id="yj19_pwa_button_icon_svg"
                                    rows="5" class="large-text code"
                                    placeholder='<?php echo esc_attr('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>'); ?>'
                                    style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;direction:ltr;text-align:left;"><?php echo esc_textarea($settings['button_icon_svg']); ?></textarea>
                                <p class="description">
                                    <?php esc_html_e('کد کامل SVG رو از سایت‌هایی مثل Heroicons / Tabler / Lucide کپی و اینجا paste کن. تگ‌های خطرناک (script, event handlerها) خودکار حذف می‌شن. اگه پر باشه، روی URL اولویت داره. برای رنگ متناسب با دکمه، در path از', 'yj19-panel'); ?>
                                    <code>stroke="currentColor"</code>
                                    <?php esc_html_e('یا', 'yj19-panel'); ?>
                                    <code>fill="currentColor"</code>
                                    <?php esc_html_e('استفاده کن.', 'yj19-panel'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h2><?php esc_html_e('استایل دکمه', 'yj19-panel'); ?></h2>
                    <div class="yj19-pwa-style-picker">
                        <?php foreach (array('solid' => 'ساده', 'glass' => 'گلس‌مورفیسم', 'gradient' => 'گرادیان') as $val => $label) : ?>
                            <label class="yj19-pwa-style-option <?php echo $settings['button_style'] === $val ? 'active' : ''; ?>">
                                <input type="radio" name="yj19_pwa[button_style]" value="<?php echo esc_attr($val); ?>"
                                    <?php checked($settings['button_style'], $val); ?>>
                                <span class="yj19-pwa-style-preview yj19-pwa-style-preview--<?php echo esc_attr($val); ?>"></span>
                                <span class="yj19-pwa-style-label"><?php echo esc_html($label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <table class="form-table">
                        <tr>
                            <th><label><?php esc_html_e('رنگ متن دکمه', 'yj19-panel'); ?></label></th>
                            <td><input type="text" name="yj19_pwa[button_text_color]" class="yj19-pwa-color" value="<?php echo esc_attr($settings['button_text_color']); ?>"></td>
                        </tr>
                        <tr class="yj19-pwa-style-field" data-show-for="solid glass">
                            <th><label><?php esc_html_e('رنگ پس‌زمینه دکمه', 'yj19-panel'); ?></label></th>
                            <td><input type="text" name="yj19_pwa[button_color]" class="yj19-pwa-color" value="<?php echo esc_attr($settings['button_color']); ?>"></td>
                        </tr>
                        <tr class="yj19-pwa-style-field" data-show-for="gradient">
                            <th><label><?php esc_html_e('گرادیان (از → به)', 'yj19-panel'); ?></label></th>
                            <td>
                                <input type="text" name="yj19_pwa[button_gradient_from]" class="yj19-pwa-color" value="<?php echo esc_attr($settings['button_gradient_from']); ?>">
                                <input type="text" name="yj19_pwa[button_gradient_to]" class="yj19-pwa-color" value="<?php echo esc_attr($settings['button_gradient_to']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php esc_html_e('شعاع گردی (px)', 'yj19-panel'); ?></label></th>
                            <td>
                                <input type="range" id="yj19_pwa_button_radius" name="yj19_pwa[button_radius]"
                                    min="0" max="50" value="<?php echo esc_attr($settings['button_radius']); ?>"
                                    style="width:200px;vertical-align:middle;">
                                <span class="yj19-pwa-range-value"><?php echo esc_html($settings['button_radius']); ?>px</span>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php esc_html_e('سایه', 'yj19-panel'); ?></label></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="yj19_pwa[button_shadow]" value="1" <?php checked($settings['button_shadow'], 1); ?>>
                                    <?php esc_html_e('نمایش سایه نرم زیر دکمه', 'yj19-panel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php esc_html_e('موقعیت دکمه', 'yj19-panel'); ?></label></th>
                            <td>
                                <select name="yj19_pwa[button_position]">
                                    <option value="bottom-right" <?php selected($settings['button_position'], 'bottom-right'); ?>><?php esc_html_e('پایین-راست', 'yj19-panel'); ?></option>
                                    <option value="bottom-left" <?php selected($settings['button_position'], 'bottom-left'); ?>><?php esc_html_e('پایین-چپ', 'yj19-panel'); ?></option>
                                    <option value="bottom-center" <?php selected($settings['button_position'], 'bottom-center'); ?>><?php esc_html_e('پایین-وسط', 'yj19-panel'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="yj19-pwa-preview-wrap">
                    <h2><?php esc_html_e('پیش‌نمایش زنده', 'yj19-panel'); ?></h2>
                    <div class="yj19-pwa-phone-frame">
                        <div class="yj19-pwa-phone-screen">
                            <div class="yj19-pwa-phone-content">
                                <div class="yj19-pwa-phone-line"></div>
                                <div class="yj19-pwa-phone-line short"></div>
                                <div class="yj19-pwa-phone-line"></div>
                                <div class="yj19-pwa-phone-line"></div>
                                <div class="yj19-pwa-phone-line short"></div>
                            </div>
                            <button type="button" id="yj19-pwa-preview-btn" class="yj19-pwa-install-btn"
                                data-position="<?php echo esc_attr($settings['button_position']); ?>"
                                data-style="<?php echo esc_attr($settings['button_style']); ?>">
                                <span class="yj19-pwa-install-btn__icon"></span>
                                <span class="yj19-pwa-install-btn__text"><?php echo esc_html($settings['button_text']); ?></span>
                            </button>
                        </div>
                    </div>
                    <p class="description"><?php esc_html_e('این پیش‌نمایش دقیقاً همون چیزی هست که کاربر روی موبایل می‌بینه.', 'yj19-panel'); ?></p>
                </div>
            </div>
        </div>

        <!-- ============================ تب محل قرارگیری ============================ -->
        <div id="tab-placements" class="yj19-pwa-tab-pane">
            <div class="yj19-pwa-card">
                <h2><?php esc_html_e('محل نمایش دکمه نصب', 'yj19-panel'); ?></h2>
                <p class="description">
                    <?php esc_html_e('می‌تونی دکمه نصب رو در چندین مکان به‌صورت همزمان نمایش بدی. برای هر کدوم یه CSS selector پیش‌فرض داره؛ اگه قالب تو از selector دیگه‌ای استفاده می‌کنه، اون رو وارد کن.', 'yj19-panel'); ?>
                </p>

                <!-- 1. شناور -->
                <div class="yj19-pwa-placement">
                    <label class="yj19-pwa-switch">
                        <input type="checkbox" name="yj19_pwa[placement_floating]" value="1" <?php checked($settings['placement_floating'], 1); ?>>
                        <span class="yj19-pwa-switch-slider"></span>
                        <span class="yj19-pwa-switch-label"><?php esc_html_e('دکمه شناور (Floating)', 'yj19-panel'); ?></span>
                    </label>
                    <p class="description"><?php esc_html_e('دکمه‌ای که در پایین صفحه شناور می‌مونه. موقعیت و استایلش رو از تب «دکمه نصب» تنظیم کن.', 'yj19-panel'); ?></p>
                </div>

                <!-- 2. Mobile Sidebar -->
                <div class="yj19-pwa-placement">
                    <label class="yj19-pwa-switch">
                        <input type="checkbox" name="yj19_pwa[placement_mobile_sidebar]" value="1" <?php checked($settings['placement_mobile_sidebar'], 1); ?>>
                        <span class="yj19-pwa-switch-slider"></span>
                        <span class="yj19-pwa-switch-label"><?php esc_html_e('داخل سایدبار موبایل (منوی کشویی ریسپانسیو)', 'yj19-panel'); ?></span>
                    </label>
                    <p class="description"><?php esc_html_e('دکمه به انتهای منوی کشویی موبایل اضافه می‌شه.', 'yj19-panel'); ?></p>
                    <label class="yj19-pwa-selector-label">
                        <?php esc_html_e('CSS Selector سایدبار موبایل:', 'yj19-panel'); ?>
                        <input type="text" name="yj19_pwa[selector_mobile_sidebar]" class="regular-text code"
                            value="<?php echo esc_attr($settings['selector_mobile_sidebar']); ?>"
                            placeholder=".mobile-menu, .nav-mobile">
                    </label>
                </div>

                <!-- 3. Mobile Navigation Bar -->
                <div class="yj19-pwa-placement">
                    <label class="yj19-pwa-switch">
                        <input type="checkbox" name="yj19_pwa[placement_mobile_navbar]" value="1" <?php checked($settings['placement_mobile_navbar'], 1); ?>>
                        <span class="yj19-pwa-switch-slider"></span>
                        <span class="yj19-pwa-switch-label"><?php esc_html_e('داخل Navigation Bar موبایل (نوار پایین ریسپانسیو)', 'yj19-panel'); ?></span>
                    </label>
                    <p class="description"><?php esc_html_e('دکمه به نوار ناوبری پایین صفحه‌ی موبایل اضافه می‌شه.', 'yj19-panel'); ?></p>
                    <label class="yj19-pwa-selector-label">
                        <?php esc_html_e('CSS Selector Navigation Bar:', 'yj19-panel'); ?>
                        <input type="text" name="yj19_pwa[selector_mobile_navbar]" class="regular-text code"
                            value="<?php echo esc_attr($settings['selector_mobile_navbar']); ?>"
                            placeholder=".mobile-bottom-nav, .nav-mobile-bar">
                    </label>
                </div>

                <!-- 4. Desktop Header -->
                <div class="yj19-pwa-placement">
                    <label class="yj19-pwa-switch">
                        <input type="checkbox" name="yj19_pwa[placement_desktop_header]" value="1" <?php checked($settings['placement_desktop_header'], 1); ?>>
                        <span class="yj19-pwa-switch-slider"></span>
                        <span class="yj19-pwa-switch-label"><?php esc_html_e('در هدر دسکتاپ (کنار دکمه حساب کاربری)', 'yj19-panel'); ?></span>
                    </label>
                    <p class="description"><?php esc_html_e('دکمه به ناحیه‌ی اکشن‌های هدر دسکتاپ، در ردیف اول کنار حساب کاربری اضافه می‌شه.', 'yj19-panel'); ?></p>
                    <label class="yj19-pwa-selector-label">
                        <?php esc_html_e('CSS Selector ناحیه‌ی هدر دسکتاپ:', 'yj19-panel'); ?>
                        <input type="text" name="yj19_pwa[selector_desktop_header]" class="regular-text code"
                            value="<?php echo esc_attr($settings['selector_desktop_header']); ?>"
                            placeholder=".header-actions, .header-account">
                    </label>
                </div>

                <p class="description" style="background:#fff7ed;border-right:3px solid #2271b1;padding:10px 14px;border-radius:6px;margin-top:14px;">
                    <strong><?php esc_html_e('نکته:', 'yj19-panel'); ?></strong>
                    <?php esc_html_e('selectorها CSS استاندارد هستن (چندتایی با کاما). اولین selector که در صفحه پیدا بشه استفاده می‌شه. اگه شناور و سایر placementها هر دو فعال باشن، می‌تونی شناور رو فقط روی صفحاتی که سایر placementها در دسترس نیستن نگه داری.', 'yj19-panel'); ?>
                </p>
            </div>
        </div>

        <!-- ============================ تب رفتار ============================ -->
        <div id="tab-behavior" class="yj19-pwa-tab-pane">
            <div class="yj19-pwa-card">
                <h2><?php esc_html_e('شرایط نمایش دکمه', 'yj19-panel'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('فقط روی موبایل', 'yj19-panel'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="yj19_pwa[mobile_only]" value="1" <?php checked($settings['mobile_only'], 1); ?>>
                                <?php esc_html_e('دکمه فقط روی دستگاه‌های موبایل نمایش داده بشه', 'yj19-panel'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('PWA معمولاً برای موبایل معنی داره. اگه این رو غیرفعال کنی، روی دسکتاپ هم تلاش میشه دکمه نصب نمایش داده بشه (اگه مرورگر پشتیبانی کنه).', 'yj19-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php esc_html_e('تأخیر نمایش (ثانیه)', 'yj19-panel'); ?></label></th>
                        <td>
                            <input type="number" name="yj19_pwa[show_delay_sec]" min="0" max="60"
                                value="<?php echo esc_attr($settings['show_delay_sec']); ?>" class="small-text">
                            <p class="description"><?php esc_html_e('دکمه بعد از این تعداد ثانیه از باز شدن صفحه ظاهر می‌شه.', 'yj19-panel'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="yj19-pwa-card">
                <h2><?php esc_html_e('کش و حالت آفلاین', 'yj19-panel'); ?></h2>
                <label class="yj19-pwa-switch">
                    <input type="checkbox" name="yj19_pwa[sw_enabled]" value="1" <?php checked($settings['sw_enabled'], 1); ?>>
                    <span class="yj19-pwa-switch-slider"></span>
                    <span class="yj19-pwa-switch-label"><?php esc_html_e('فعال‌سازی Service Worker (کش آفلاین)', 'yj19-panel'); ?></span>
                </label>
                <p class="description">
                    <?php esc_html_e('با فعال‌سازی، فایل‌های استاتیک سایت کش می‌شن و کاربر در حالت آفلاین صفحات قبلاً بازدید شده رو می‌بینه. صفحات HTML با استراتژی network-first سرو می‌شن — یعنی crawlerها همیشه نسخه‌ی fresh می‌گیرن و SEO آسیب نمی‌بینه.', 'yj19-panel'); ?>
                </p>

                <p style="margin-top:14px;">
                    <strong><?php esc_html_e('نسخه فعلی کش:', 'yj19-panel'); ?></strong>
                    <code>v<?php echo (int) $settings['cache_version']; ?></code>
                    —
                    <em><?php esc_html_e('با هر ذخیره این عدد افزایش پیدا می‌کنه تا کش مرورگر کاربران invalidate بشه.', 'yj19-panel'); ?></em>
                </p>
            </div>

            <div class="yj19-pwa-card">
                <h2><?php esc_html_e('Endpoints', 'yj19-panel'); ?></h2>
                <p>
                    <strong>Manifest:</strong>
                    <a href="<?php echo esc_url($manifest_url); ?>" target="_blank" rel="noopener"><code><?php echo esc_html($manifest_url); ?></code></a>
                </p>
                <p>
                    <strong>Service Worker:</strong>
                    <a href="<?php echo esc_url($sw_url); ?>" target="_blank" rel="noopener"><code><?php echo esc_html($sw_url); ?></code></a>
                </p>
                <p class="description"><?php esc_html_e('این URLها فقط وقتی PWA فعال باشه پاسخ می‌دن. می‌تونی برای دیباگ مستقیم تستشون کنی.', 'yj19-panel'); ?></p>
            </div>
        </div>

        <p class="submit">
            <button type="submit" name="yj19_pwa_save" class="button button-primary button-hero">
                <?php esc_html_e('ذخیره تنظیمات', 'yj19-panel'); ?>
            </button>
        </p>
    </form>
</div>
