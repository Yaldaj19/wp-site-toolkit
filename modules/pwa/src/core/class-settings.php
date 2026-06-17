<?php
/**
 * YJ19 PWA — Settings
 *
 * مدیریت option ها، defaults و sanitize.
 *
 * @package Yj19\PWA
 */

if (!defined('ABSPATH')) {
    exit;
}

class YJ19_PWA_Settings
{
    /**
     * مقادیر پیش‌فرض تنظیمات.
     *
     * رنگ پایه از constantهای YJ19_PWA_THEME_COLOR و YJ19_PWA_THEME_COLOR_HOVER
     * گرفته می‌شه (در functions.php قالب تعریف می‌شن). اگه تعریف نشده باشن
     * (مثلاً قبل از لود قالب)، یک رنگ پیش‌فرض استفاده می‌شه.
     */
    public static function defaults()
    {
        $blog_name   = get_bloginfo('name');
        $base_color  = defined('YJ19_PWA_THEME_COLOR') ? YJ19_PWA_THEME_COLOR : '#ff0066';
        $hover_color = defined('YJ19_PWA_THEME_COLOR_HOVER') ? YJ19_PWA_THEME_COLOR_HOVER : self::lighten($base_color, 15);

        return array(
            // عمومی
            'enabled'              => 0,
            'app_name'             => $blog_name,
            'short_name'           => mb_substr($blog_name, 0, 12),
            'description'          => get_bloginfo('description'),
            'theme_color'          => $base_color,
            'background_color'     => '#ffffff',
            'icon_192'             => '',
            'icon_512'             => '',
            'start_url'            => '/',

            // دکمه نصب
            'button_text'          => 'نصب اپلیکیشن',
            'button_icon'          => '',
            'button_icon_svg'      => '',
            'button_style'         => 'gradient',
            'button_color'         => $base_color,
            'button_text_color'    => '#ffffff',
            'button_gradient_from' => $base_color,
            'button_gradient_to'   => $hover_color,
            'button_position'      => 'bottom-center',
            'button_radius'        => 14,
            'button_shadow'        => 1,

            // محل قرارگیری (placements)
            'placement_floating'         => 1,
            'placement_mobile_sidebar'   => 0,
            'placement_mobile_navbar'    => 0,
            'placement_desktop_header'   => 0,
            'selector_mobile_sidebar'    => '.mobile-menu, .nav-mobile, .offcanvas-menu',
            'selector_mobile_navbar'     => '.mobile-bottom-nav, .nav-mobile-bar, .bottom-nav',
            'selector_desktop_header'    => '.header-actions, .header-account, .header-user',

            // رفتار
            'sw_enabled'           => 1,
            'show_delay_sec'       => 3,
            'mobile_only'          => 1,
            'cache_version'        => 1,
        );
    }

    /**
     * روشن کردن رنگ hex (lighten by percent).
     */
    private static function lighten($hex, $percent)
    {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return '#' . $hex;
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $factor = max(0, min(100, $percent)) / 100;
        $r = min(255, (int) ($r + (255 - $r) * $factor));
        $g = min(255, (int) ($g + (255 - $g) * $factor));
        $b = min(255, (int) ($b + (255 - $b) * $factor));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * گرفتن یک مقدار با fallback به default.
     */
    public static function get($key)
    {
        $opts     = get_option(YJ19_PWA_OPTION, array());
        $defaults = self::defaults();

        if (!is_array($opts)) {
            $opts = array();
        }

        if (array_key_exists($key, $opts) && $opts[$key] !== '') {
            return $opts[$key];
        }

        return isset($defaults[$key]) ? $defaults[$key] : null;
    }

    /**
     * تمام تنظیمات با merge defaults.
     */
    public static function all()
    {
        $opts = get_option(YJ19_PWA_OPTION, array());
        if (!is_array($opts)) {
            $opts = array();
        }
        return array_merge(self::defaults(), $opts);
    }

    /**
     * Sanitize ورودی از فرم ادمین.
     *
     * @param array $input
     * @return array
     */
    public static function sanitize($input)
    {
        if (!is_array($input)) {
            return self::defaults();
        }

        $out = array();

        $out['enabled']    = !empty($input['enabled']) ? 1 : 0;
        $out['sw_enabled'] = !empty($input['sw_enabled']) ? 1 : 0;
        $out['mobile_only'] = !empty($input['mobile_only']) ? 1 : 0;
        $out['button_shadow'] = !empty($input['button_shadow']) ? 1 : 0;

        // placements
        $out['placement_floating']        = !empty($input['placement_floating']) ? 1 : 0;
        $out['placement_mobile_sidebar']  = !empty($input['placement_mobile_sidebar']) ? 1 : 0;
        $out['placement_mobile_navbar']   = !empty($input['placement_mobile_navbar']) ? 1 : 0;
        $out['placement_desktop_header']  = !empty($input['placement_desktop_header']) ? 1 : 0;
        $out['selector_mobile_sidebar']   = isset($input['selector_mobile_sidebar']) ? sanitize_text_field($input['selector_mobile_sidebar']) : '';
        $out['selector_mobile_navbar']    = isset($input['selector_mobile_navbar']) ? sanitize_text_field($input['selector_mobile_navbar']) : '';
        $out['selector_desktop_header']   = isset($input['selector_desktop_header']) ? sanitize_text_field($input['selector_desktop_header']) : '';

        $out['app_name']    = isset($input['app_name']) ? sanitize_text_field($input['app_name']) : '';
        $out['short_name']  = isset($input['short_name']) ? sanitize_text_field($input['short_name']) : '';
        $out['description'] = isset($input['description']) ? sanitize_text_field($input['description']) : '';
        $out['button_text'] = isset($input['button_text']) ? sanitize_text_field($input['button_text']) : '';

        $out['icon_192']    = isset($input['icon_192']) ? esc_url_raw($input['icon_192']) : '';
        $out['icon_512']    = isset($input['icon_512']) ? esc_url_raw($input['icon_512']) : '';
        $out['button_icon'] = isset($input['button_icon']) ? esc_url_raw($input['button_icon']) : '';
        $out['button_icon_svg'] = isset($input['button_icon_svg']) ? self::sanitize_svg($input['button_icon_svg']) : '';
        $out['start_url']   = isset($input['start_url']) ? esc_url_raw($input['start_url']) : '/';

        $out['theme_color']          = self::sanitize_color($input['theme_color'] ?? '');
        $out['background_color']     = self::sanitize_color($input['background_color'] ?? '');
        $out['button_color']         = self::sanitize_color($input['button_color'] ?? '');
        $out['button_text_color']    = self::sanitize_color($input['button_text_color'] ?? '');
        $out['button_gradient_from'] = self::sanitize_color($input['button_gradient_from'] ?? '');
        $out['button_gradient_to']   = self::sanitize_color($input['button_gradient_to'] ?? '');

        $allowed_styles = array('solid', 'glass', 'gradient');
        $style = isset($input['button_style']) ? $input['button_style'] : 'gradient';
        $out['button_style'] = in_array($style, $allowed_styles, true) ? $style : 'gradient';

        $allowed_positions = array('bottom-right', 'bottom-left', 'bottom-center');
        $pos = isset($input['button_position']) ? $input['button_position'] : 'bottom-center';
        $out['button_position'] = in_array($pos, $allowed_positions, true) ? $pos : 'bottom-center';

        $out['button_radius']  = isset($input['button_radius']) ? max(0, min(50, (int) $input['button_radius'])) : 14;
        $out['show_delay_sec'] = isset($input['show_delay_sec']) ? max(0, min(60, (int) $input['show_delay_sec'])) : 3;

        // افزایش cache_version باعث invalidate شدن service worker در مرورگر می‌شه.
        $current = get_option(YJ19_PWA_OPTION, array());
        $prev_version = is_array($current) && isset($current['cache_version']) ? (int) $current['cache_version'] : 1;
        $out['cache_version'] = $prev_version + 1;

        return $out;
    }

    /**
     * Sanitize کد SVG inline:
     *   - script/style/foreignObject/iframe و dangerous tagها حذف میشن
     *   - تمام attribute هایی که با on شروع میشن (onclick, onload, ...) حذف میشن
     *   - href های javascript: حذف میشن
     *
     * @param string $svg خام
     * @return string
     */
    public static function sanitize_svg($svg)
    {
        $svg = trim((string) $svg);
        if ($svg === '') {
            return '';
        }

        // فقط محتوای داخل <svg>...</svg> رو نگه می‌داریم
        if (!preg_match('/<svg\b[^>]*>[\s\S]*<\/svg>/i', $svg, $m)) {
            return '';
        }
        $svg = $m[0];

        // حذف tag های خطرناک
        $svg = preg_replace('/<(script|style|foreignObject|iframe|object|embed|link|meta)\b[^>]*>[\s\S]*?<\/\1>/i', '', $svg);
        $svg = preg_replace('/<(script|style|foreignObject|iframe|object|embed|link|meta)\b[^>]*\/?>/i', '', $svg);

        // حذف event handlerها (on*)
        $svg = preg_replace('/\s+on[a-z]+\s*=\s*"[^"]*"/i', '', $svg);
        $svg = preg_replace("/\s+on[a-z]+\s*=\s*'[^']*'/i", '', $svg);
        $svg = preg_replace('/\s+on[a-z]+\s*=\s*[^\s>]+/i', '', $svg);

        // حذف href/xlink:href های javascript:
        $svg = preg_replace('/\s+(href|xlink:href)\s*=\s*"\s*javascript:[^"]*"/i', '', $svg);
        $svg = preg_replace("/\s+(href|xlink:href)\s*=\s*'\s*javascript:[^']*'/i", '', $svg);

        // حذف CDATA و comment ها
        $svg = preg_replace('/<!\[CDATA\[[\s\S]*?\]\]>/', '', $svg);
        $svg = preg_replace('/<!--[\s\S]*?-->/', '', $svg);

        // حداکثر طول معقول
        if (strlen($svg) > 50000) {
            return '';
        }

        return $svg;
    }

    private static function sanitize_color($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', $value)) {
            return $value;
        }
        return '';
    }
}
