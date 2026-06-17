<?php
/**
 * YJ19 PWA — Service Worker Server
 *
 * سرو فایل service worker از endpoint /?yj19_pwa=sw با scope='/'.
 *
 * چرا از root URL سرو می‌کنیم؟ چون اگه SW از inc/tools/pwa/src/... سرو بشه،
 * scope محدود به همون پوشه میشه و کل سایت رو نمی‌تونه کنترل کنه.
 *
 * @package Yj19\PWA
 */

if (!defined('ABSPATH')) {
    exit;
}

class YJ19_PWA_ServiceWorker
{
    public function __construct()
    {
        add_action('parse_request', array($this, 'maybe_serve'));
    }

    public function maybe_serve($wp)
    {
        if (!isset($_GET[YJ19_PWA_QUERY_VAR])) {
            return;
        }

        $type = sanitize_key($_GET[YJ19_PWA_QUERY_VAR]);

        if ($type === 'sw') {
            $this->serve_sw();
        }
    }

    private function serve_sw()
    {
        if (!YJ19_PWA_Settings::get('enabled') || !YJ19_PWA_Settings::get('sw_enabled')) {
            status_header(404);
            exit;
        }

        $template_path = YJ19_PWA_DIR . '/assets/sw-template.js';
        if (!file_exists($template_path)) {
            status_header(500);
            exit;
        }

        $template = file_get_contents($template_path);
        $output   = $this->render_template($template);

        header('Content-Type: application/javascript; charset=utf-8');
        // Service-Worker-Allowed بدون trailing space (می‌تونه باعث ignore بشه در بعضی مرورگرها).
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $output;
        exit;
    }

    /**
     * جایگزینی placeholderها در template.
     */
    private function render_template($template)
    {
        $s = YJ19_PWA_Settings::all();

        $vars = array(
            '%%CACHE_VERSION%%' => (int) $s['cache_version'],
            '%%CACHE_NAME%%'    => sanitize_key(parse_url(home_url(), PHP_URL_HOST)) . '-v' . (int) $s['cache_version'],
            '%%SITE_HOME%%'     => esc_url_raw(home_url('/')),
            '%%OFFLINE_URL%%'   => esc_url_raw(home_url('/')),
        );

        return strtr($template, $vars);
    }

    /**
     * URL کامل service worker endpoint.
     */
    public static function endpoint_url()
    {
        return add_query_arg(YJ19_PWA_QUERY_VAR, 'sw', home_url('/'));
    }
}
