<?php
/**
 * YJ19 PWA — Frontend
 *
 * تزریق meta tags، manifest link، رجیستر service worker و نمایش دکمه نصب.
 *
 * @package Yj19\PWA
 */

if (!defined('ABSPATH')) {
    exit;
}

class YJ19_PWA_Frontend
{
    public function __construct()
    {
        add_action('wp_head', array($this, 'inject_head'), 1);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'inject_button'), 99);
        add_action('wp_footer', array($this, 'inject_sw_register'), 100);
    }

    /**
     * آیا روی این صفحه باید PWA فعال باشه؟
     *
     * توجه: حتی اگه mobile_only فعاله، در صورتی که placement_desktop_header فعال باشه،
     * روی دسکتاپ هم لود می‌شه (تا کاربر دسکتاپ هم بتونه نصب کنه).
     */
    private function should_run()
    {
        if (!YJ19_PWA_Settings::get('enabled')) {
            return false;
        }

        if (is_admin() || is_preview() || is_customize_preview()) {
            return false;
        }

        $mobile_only       = (bool) YJ19_PWA_Settings::get('mobile_only');
        $desktop_placement = (bool) YJ19_PWA_Settings::get('placement_desktop_header');
        $is_mobile         = wp_is_mobile();

        if ($mobile_only && !$is_mobile && !$desktop_placement) {
            return false;
        }

        return true;
    }

    /**
     * meta tags و manifest link در <head>.
     */
    public function inject_head()
    {
        if (!$this->should_run()) {
            return;
        }

        $s = YJ19_PWA_Settings::all();
        $manifest_url = YJ19_PWA_Manifest::endpoint_url();

        printf("\n<!-- YJ19 PWA -->\n");
        printf('<link rel="manifest" href="%s">' . "\n", esc_url($manifest_url));
        printf('<meta name="theme-color" content="%s">' . "\n", esc_attr($s['theme_color']));
        printf('<meta name="apple-mobile-web-app-capable" content="yes">' . "\n");
        printf('<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n");
        printf('<meta name="apple-mobile-web-app-title" content="%s">' . "\n", esc_attr($s['short_name']));
        printf('<meta name="mobile-web-app-capable" content="yes">' . "\n");
        printf('<meta name="application-name" content="%s">' . "\n", esc_attr($s['app_name']));

        if (!empty($s['icon_192'])) {
            printf('<link rel="apple-touch-icon" href="%s">' . "\n", esc_url($s['icon_192']));
        }
        printf("<!-- /YJ19 PWA -->\n");
    }

    /**
     * Enqueue assets فرانت.
     */
    public function enqueue_assets()
    {
        if (!$this->should_run()) {
            return;
        }

        $css_file = YJ19_PWA_DIR . '/frontend/assets/pwa-button.css';
        $js_file  = YJ19_PWA_DIR . '/frontend/assets/pwa.js';

        wp_enqueue_style(
            'yj19-pwa-button',
            YJ19_PWA_URL . '/frontend/assets/pwa-button.css',
            array(),
            file_exists($css_file) ? filemtime($css_file) : YJ19_PWA_VERSION
        );

        wp_register_script(
            'yj19-pwa',
            YJ19_PWA_URL . '/frontend/assets/pwa.js',
            array(),
            file_exists($js_file) ? filemtime($js_file) : YJ19_PWA_VERSION,
            true
        );

        $s = YJ19_PWA_Settings::all();
        wp_localize_script('yj19-pwa', 'YJ19_PWA', array(
            'swUrl'         => YJ19_PWA_ServiceWorker::endpoint_url(),
            'swEnabled'     => (bool) $s['sw_enabled'],
            'showDelay'     => (int) $s['show_delay_sec'] * 1000,
            'mobileOnly'    => (bool) $s['mobile_only'],
            'storageKey'    => 'yj19_pwa_dismissed',
            'cacheVersion'  => (int) $s['cache_version'],
            'placements'    => array(
                'floating'         => (bool) $s['placement_floating'],
                'mobile_sidebar'   => array(
                    'enabled'  => (bool) $s['placement_mobile_sidebar'],
                    'selector' => (string) $s['selector_mobile_sidebar'],
                ),
                'mobile_navbar'    => array(
                    'enabled'  => (bool) $s['placement_mobile_navbar'],
                    'selector' => (string) $s['selector_mobile_navbar'],
                ),
                'desktop_header'   => array(
                    'enabled'  => (bool) $s['placement_desktop_header'],
                    'selector' => (string) $s['selector_desktop_header'],
                ),
            ),
        ));

        wp_enqueue_script('yj19-pwa');
    }

    /**
     * تزریق دکمه نصب در footer.
     */
    public function inject_button()
    {
        if (!$this->should_run()) {
            return;
        }

        $settings = YJ19_PWA_Settings::all();
        include YJ19_PWA_DIR . '/frontend/views/install-button.php';
    }

    /**
     * رجیستر service worker در footer (inline).
     */
    public function inject_sw_register()
    {
        if (!$this->should_run() || !YJ19_PWA_Settings::get('sw_enabled')) {
            return;
        }

        $sw_url = esc_js(YJ19_PWA_ServiceWorker::endpoint_url());
        ?>
        <script>
        (function () {
            if (!('serviceWorker' in navigator)) return;
            window.addEventListener('load', function () {
                navigator.serviceWorker.register(<?php echo json_encode($sw_url); ?>, { scope: '/' })
                    .catch(function (err) { console.warn('YJ19 PWA SW register failed:', err); });
            });
        })();
        </script>
        <?php
    }
}
