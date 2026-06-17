<?php
/**
 * YJ19 PWA — Manifest Generator
 *
 * تولید پویای manifest.json از options.
 * Endpoint: /?yj19_pwa=manifest
 *
 * @package Yj19\PWA
 */

if (!defined('ABSPATH')) {
    exit;
}

class YJ19_PWA_Manifest
{
    public function __construct()
    {
        add_action('init', array($this, 'register_query_var'));
        add_action('parse_request', array($this, 'maybe_serve'));
    }

    public function register_query_var()
    {
        global $wp;
        if ($wp instanceof WP) {
            $wp->add_query_var(YJ19_PWA_QUERY_VAR);
        }
    }

    public function maybe_serve($wp)
    {
        if (!isset($_GET[YJ19_PWA_QUERY_VAR])) {
            return;
        }

        $type = sanitize_key($_GET[YJ19_PWA_QUERY_VAR]);

        if ($type === 'manifest') {
            $this->serve_manifest();
        }
    }

    /**
     * خروجی JSON manifest را با هدرهای مناسب می‌فرستد و خاتمه می‌دهد.
     */
    private function serve_manifest()
    {
        if (!YJ19_PWA_Settings::get('enabled')) {
            status_header(404);
            exit;
        }

        nocache_headers();
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        echo wp_json_encode($this->build_manifest(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * ساختار manifest استاندارد W3C.
     */
    private function build_manifest()
    {
        $s = YJ19_PWA_Settings::all();

        $start_url = $s['start_url'] !== '' ? $s['start_url'] : '/';

        $manifest = array(
            'name'             => $s['app_name'],
            'short_name'       => $s['short_name'] !== '' ? $s['short_name'] : $s['app_name'],
            'description'      => $s['description'],
            'start_url'        => $start_url,
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait-primary',
            'theme_color'      => $s['theme_color'],
            'background_color' => $s['background_color'],
            'lang'             => 'fa',
            'dir'              => 'rtl',
            'icons'            => $this->build_icons($s),
        );

        return apply_filters('yj19_pwa_manifest', $manifest, $s);
    }

    private function build_icons($s)
    {
        $icons = array();

        if (!empty($s['icon_192'])) {
            $icons[] = array(
                'src'     => $s['icon_192'],
                'sizes'   => '192x192',
                'type'    => $this->guess_mime($s['icon_192']),
                'purpose' => 'any maskable',
            );
        }

        if (!empty($s['icon_512'])) {
            $icons[] = array(
                'src'     => $s['icon_512'],
                'sizes'   => '512x512',
                'type'    => $this->guess_mime($s['icon_512']),
                'purpose' => 'any maskable',
            );
        }

        return $icons;
    }

    private function guess_mime($url)
    {
        $ext = strtolower(pathinfo(wp_parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        switch ($ext) {
            case 'png':  return 'image/png';
            case 'jpg':
            case 'jpeg': return 'image/jpeg';
            case 'svg':  return 'image/svg+xml';
            case 'webp': return 'image/webp';
            default:     return 'image/png';
        }
    }

    /**
     * URL کامل manifest endpoint.
     */
    public static function endpoint_url()
    {
        return add_query_arg(YJ19_PWA_QUERY_VAR, 'manifest', home_url('/'));
    }
}
