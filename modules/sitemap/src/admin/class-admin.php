<?php

/**
 * Admin Panel Class
 * 
 * Related Files:
 * - custom-sitemap.php
 * - admin/views/dashboard.php
 * - admin/views/tabs.php
 * - admin/views/guide.php
 * - admin/assets/admin.css
 * - admin/assets/admin.js
 * - core/class-manager.php
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

class SSG_Admin
{

    private $manager;

    /** @var string هوک صفحه برای گیت کردن enqueue */
    private $hook_suffix = '';

    //-------------------------------
    // Constructor
    public function __construct()
    {
        $this->manager = SSG_Manager::instance();
        $this->init();
    }

    //-------------------------------
    // Initialize
    private function init()
    {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_ssg_save', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_ssg_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_ssg_rebuild', array($this, 'ajax_rebuild'));
        add_action('wp_ajax_ssg_ping', array($this, 'ajax_ping'));
        add_action('wp_ajax_ssg_export', array($this, 'ajax_export'));
        add_action('wp_ajax_ssg_import', array($this, 'ajax_import'));
    }

    //-------------------------------
    // Add Admin Menu
    public function add_menu()
    {
        $parent = defined('WP_SITE_TOOLKIT_MENU_SLUG') ? WP_SITE_TOOLKIT_MENU_SLUG : 'options-general.php';
        $this->hook_suffix = add_submenu_page(
            $parent,
            __('SEO Sitemap Generator', 'seo-sitemap-generator'),
            __('SEO Sitemap', 'seo-sitemap-generator'),
            'manage_options',
            'seo-sitemap-generator',
            array($this, 'render_page')
        );
    }

    //-------------------------------
    // Enqueue Assets
    public function enqueue_assets($hook)
    {
        if ($hook !== $this->hook_suffix) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'ssg-admin-layout',
            SSG_URL . 'admin/assets/admin-layout.css',
            array(),
            SSG_VERSION
        );

        wp_enqueue_style(
            'ssg-admin-components',
            SSG_URL . 'admin/assets/admin-components.css',
            array('ssg-admin-layout'),
            SSG_VERSION
        );

        // JavaScript
        wp_enqueue_script(
            'ssg-admin',
            SSG_URL . 'admin/assets/admin.js',
            array('jquery'),
            SSG_VERSION,
            true
        );

        // Localize script
        wp_localize_script('ssg-admin', 'ssgAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ssg_nonce'),
            'strings' => array(
                'saving' => __('Saving...', 'seo-sitemap-generator'),
                'saved' => __('Settings saved successfully!', 'seo-sitemap-generator'),
                'error' => __('Error occurred. Please try again.', 'seo-sitemap-generator'),
                'clearing' => __('Clearing cache...', 'seo-sitemap-generator'),
                'cleared' => __('Cache cleared successfully!', 'seo-sitemap-generator'),
                'rebuilding' => __('Rebuilding sitemap...', 'seo-sitemap-generator'),
                'rebuilt' => __('Sitemap rebuilt successfully!', 'seo-sitemap-generator'),
                'pinging' => __('Pinging search engines...', 'seo-sitemap-generator'),
                'pinged' => __('Search engines pinged successfully!', 'seo-sitemap-generator'),
                'confirm' => __('Are you sure?', 'seo-sitemap-generator')
            )
        ));
    }

    //-------------------------------
    // Render Admin Page
    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'seo-sitemap-generator'));
        }

        require_once SSG_PATH . 'admin/views/dashboard.php';
    }

    //-------------------------------
    // AJAX: Save Settings
    public function ajax_save_settings()
    {
        check_ajax_referer('ssg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'seo-sitemap-generator')));
        }

        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();

        if (empty($settings)) {
            wp_send_json_error(array('message' => __('No settings provided.', 'seo-sitemap-generator')));
        }

        // Parse settings (they come as serialized form data)
        parse_str($settings, $parsed_settings);

        // Update settings
        $result = $this->manager->get_settings()->update($parsed_settings);

        if ($result) {
            wp_send_json_success(array('message' => __('Settings saved successfully!', 'seo-sitemap-generator')));
        } else {
            wp_send_json_error(array('message' => __('Failed to save settings.', 'seo-sitemap-generator')));
        }
    }

    //-------------------------------
    // AJAX: Clear Cache
    public function ajax_clear_cache()
    {
        check_ajax_referer('ssg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'seo-sitemap-generator')));
        }

        $result = $this->manager->clear_cache();

        if ($result) {
            wp_send_json_success(array('message' => __('Cache cleared successfully!', 'seo-sitemap-generator')));
        } else {
            wp_send_json_error(array('message' => __('Failed to clear cache.', 'seo-sitemap-generator')));
        }
    }

    //-------------------------------
    // AJAX: Rebuild Sitemap
    public function ajax_rebuild()
    {
        check_ajax_referer('ssg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'seo-sitemap-generator')));
        }

        $result = $this->manager->rebuild();

        if ($result) {
            wp_send_json_success(array('message' => __('Sitemap rebuilt successfully!', 'seo-sitemap-generator')));
        } else {
            wp_send_json_error(array('message' => __('Failed to rebuild sitemap.', 'seo-sitemap-generator')));
        }
    }

    //-------------------------------
    // AJAX: Ping Search Engines
    public function ajax_ping()
    {
        check_ajax_referer('ssg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'seo-sitemap-generator')));
        }

        $result = $this->manager->ping_search_engines();

        if ($result) {
            wp_send_json_success(array('message' => __('Search engines pinged successfully!', 'seo-sitemap-generator')));
        } else {
            wp_send_json_error(array('message' => __('Failed to ping search engines.', 'seo-sitemap-generator')));
        }
    }

    //-------------------------------
    // AJAX: Export Settings
    public function ajax_export()
    {
        check_ajax_referer('ssg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'seo-sitemap-generator')));
        }

        $json = $this->manager->get_settings()->export();

        wp_send_json_success(array('data' => $json));
    }

    //-------------------------------
    // AJAX: Import Settings
    public function ajax_import()
    {
        check_ajax_referer('ssg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'seo-sitemap-generator')));
        }

        $json = isset($_POST['json']) ? $_POST['json'] : '';

        if (empty($json)) {
            wp_send_json_error(array('message' => __('No data provided.', 'seo-sitemap-generator')));
        }

        $result = $this->manager->get_settings()->import($json);

        if ($result) {
            wp_send_json_success(array('message' => __('Settings imported successfully!', 'seo-sitemap-generator')));
        } else {
            wp_send_json_error(array('message' => __('Failed to import settings. Invalid JSON format.', 'seo-sitemap-generator')));
        }
    }
}
