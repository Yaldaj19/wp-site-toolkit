<?php
/**
 * YJ19 PWA — Entry Point
 *
 * Bootstrap همه کلاس‌های ماژول PWA.
 *
 * @package Yj19\PWA
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once YJ19_PWA_DIR . '/core/class-settings.php';
require_once YJ19_PWA_DIR . '/core/class-manifest.php';
require_once YJ19_PWA_DIR . '/core/class-service-worker.php';

// Manifest و Service Worker در فرانت + ادمین لود می‌شن چون endpoint عمومی دارن.
new YJ19_PWA_Manifest();
new YJ19_PWA_ServiceWorker();

if (is_admin()) {
    require_once YJ19_PWA_DIR . '/admin/class-admin.php';
    new YJ19_PWA_Admin();
} else {
    require_once YJ19_PWA_DIR . '/frontend/class-frontend.php';
    new YJ19_PWA_Frontend();
}
