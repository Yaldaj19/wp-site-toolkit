<?php

/**
 * Admin Dashboard View
 * 
 * Related Files:
 * - admin/class-admin.php
 * - admin/views/tabs.php
 * - admin/views/guide.php
 * - admin/assets/admin-layout.css
 * - admin/assets/admin-components.css
 * - admin/assets/admin.js
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

$manager = SSG_Manager::instance();
$settings = $manager->get_settings();
$detector = $manager->get_detector();
$stats = $manager->get_statistics();
$health = $manager->check_health();

$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'homepage';
?>

<div class="wrap ssg-wrap">

    <!-- Header -->
    <div class="ssg-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="ssg-version">
            <?php printf(__('Version %s', 'seo-sitemap-generator'), SSG_VERSION); ?>
        </div>
    </div>

    <!-- Health Status -->
    <?php if (!$health['healthy']): ?>
        <div class="notice notice-error">
            <p><strong><?php _e('Configuration Issues:', 'seo-sitemap-generator'); ?></strong></p>
            <ul>
                <?php foreach ($health['issues'] as $issue): ?>
                    <li><?php echo esc_html($issue); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Success Message -->
    <div id="ssg-message" class="notice" style="display:none;"></div>

    <div class="ssg-container">

        <!-- Main Content -->
        <div class="ssg-main">

            <!-- Tabs Navigation -->
            <nav class="ssg-tabs">
                <a href="?page=seo-sitemap-generator&tab=homepage" class="ssg-tab <?php echo $current_tab === 'homepage' ? 'active' : ''; ?>">
                    🏠 <?php _e('Homepage', 'seo-sitemap-generator'); ?>
                </a>
                <a href="?page=seo-sitemap-generator&tab=post-types" class="ssg-tab <?php echo $current_tab === 'post-types' ? 'active' : ''; ?>">
                    📝 <?php _e('Post Types', 'seo-sitemap-generator'); ?>
                </a>
                <a href="?page=seo-sitemap-generator&tab=taxonomies" class="ssg-tab <?php echo $current_tab === 'taxonomies' ? 'active' : ''; ?>">
                    📁 <?php _e('Taxonomies', 'seo-sitemap-generator'); ?>
                </a>
                <a href="?page=seo-sitemap-generator&tab=authors" class="ssg-tab <?php echo $current_tab === 'authors' ? 'active' : ''; ?>">
                    👤 <?php _e('Authors', 'seo-sitemap-generator'); ?>
                </a>
                <a href="?page=seo-sitemap-generator&tab=dates" class="ssg-tab <?php echo $current_tab === 'dates' ? 'active' : ''; ?>">
                    📅 <?php _e('Dates', 'seo-sitemap-generator'); ?>
                </a>
                <?php if (ssg_is_woocommerce_active()): ?>
                    <a href="?page=seo-sitemap-generator&tab=woocommerce" class="ssg-tab <?php echo $current_tab === 'woocommerce' ? 'active' : ''; ?>">
                        🛒 <?php _e('WooCommerce', 'seo-sitemap-generator'); ?>
                    </a>
                <?php endif; ?>
                <a href="?page=seo-sitemap-generator&tab=advanced" class="ssg-tab <?php echo $current_tab === 'advanced' ? 'active' : ''; ?>">
                    ⚙️ <?php _e('Advanced', 'seo-sitemap-generator'); ?>
                </a>
                <a href="?page=seo-sitemap-generator&tab=guide" class="ssg-tab <?php echo $current_tab === 'guide' ? 'active' : ''; ?>">
                    📚 <?php _e('Guide', 'seo-sitemap-generator'); ?>
                </a>
            </nav>

            <!-- Tab Content -->
            <form id="ssg-form" method="post">
                <?php wp_nonce_field('ssg_nonce', 'ssg_nonce'); ?>

                <div class="ssg-content">
                    <?php
                    if ($current_tab === 'guide') {
                        require_once SSG_PATH . 'admin/views/guide.php';
                    } else {
                        require_once SSG_PATH . 'admin/views/tabs.php';
                    }
                    ?>
                </div>

                <?php if ($current_tab !== 'guide'): ?>
                    <div class="ssg-actions">
                        <button type="submit" class="button button-primary button-large">
                            💾 <?php _e('Save Settings', 'seo-sitemap-generator'); ?>
                        </button>
                        <button type="button" id="ssg-clear-cache" class="button button-secondary">
                            🗑️ <?php _e('Clear Cache', 'seo-sitemap-generator'); ?>
                        </button>
                        <button type="button" id="ssg-rebuild" class="button button-secondary">
                            🔄 <?php _e('Rebuild Sitemap', 'seo-sitemap-generator'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </form>

        </div>

        <!-- Sidebar -->
        <div class="ssg-sidebar">

            <!-- Quick Stats -->
            <div class="ssg-sidebar-card">
                <h3>📊 <?php _e('Statistics', 'seo-sitemap-generator'); ?></h3>
                <div class="ssg-stat">
                    <span class="ssg-stat-label"><?php _e('Mode:', 'seo-sitemap-generator'); ?></span>
                    <span class="ssg-stat-value">
                        <?php echo $stats['mode'] === 'dynamic' ? __('Dynamic', 'seo-sitemap-generator') : __('Static', 'seo-sitemap-generator'); ?>
                    </span>
                </div>
                <div class="ssg-stat">
                    <span class="ssg-stat-label"><?php _e('Total Sitemaps:', 'seo-sitemap-generator'); ?></span>
                    <span class="ssg-stat-value"><?php echo esc_html($stats['total_sitemaps']); ?></span>
                </div>
                <div class="ssg-stat">
                    <span class="ssg-stat-label"><?php _e('Total URLs:', 'seo-sitemap-generator'); ?></span>
                    <span class="ssg-stat-value"><?php echo esc_html($stats['total_urls']); ?></span>
                </div>
                <div class="ssg-stat">
                    <span class="ssg-stat-label"><?php _e('Cache Size:', 'seo-sitemap-generator'); ?></span>
                    <span class="ssg-stat-value"><?php echo esc_html($stats['cache_stats']['size']); ?></span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="ssg-sidebar-card">
                <h3>🔗 <?php _e('Quick Links', 'seo-sitemap-generator'); ?></h3>
                <ul class="ssg-quick-links">
                    <li>
                        <a href="<?php echo esc_url($manager->get_sitemap_url('main')); ?>" target="_blank">
                            🗺️ <?php _e('View Main Sitemap', 'seo-sitemap-generator'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(home_url('/robots.txt')); ?>" target="_blank">
                            🤖 <?php _e('View robots.txt', 'seo-sitemap-generator'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://search.google.com/search-console" target="_blank">
                            🔍 <?php _e('Google Search Console', 'seo-sitemap-generator'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.bing.com/webmasters" target="_blank">
                            🔎 <?php _e('Bing Webmaster Tools', 'seo-sitemap-generator'); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="ssg-sidebar-card">
                <h3>⚡ <?php _e('Quick Actions', 'seo-sitemap-generator'); ?></h3>
                <button type="button" id="ssg-ping" class="button button-secondary button-block">
                    🔔 <?php _e('Ping Search Engines', 'seo-sitemap-generator'); ?>
                </button>
                <button type="button" id="ssg-export" class="button button-secondary button-block">
                    📥 <?php _e('Export Settings', 'seo-sitemap-generator'); ?>
                </button>
                <button type="button" id="ssg-import" class="button button-secondary button-block">
                    📤 <?php _e('Import Settings', 'seo-sitemap-generator'); ?>
                </button>
            </div>

            <!-- Support -->
            <div class="ssg-sidebar-card">
                <h3>💬 <?php _e('Support', 'seo-sitemap-generator'); ?></h3>
                <p><?php _e('Need help? Check our guide or contact support.', 'seo-sitemap-generator'); ?></p>
                <a href="?page=seo-sitemap-generator&tab=guide" class="button button-secondary button-block">
                    📖 <?php _e('Read Guide', 'seo-sitemap-generator'); ?>
                </a>
            </div>

        </div>

    </div>

</div>

<!-- Import Modal -->
<div id="ssg-import-modal" class="ssg-modal" style="display:none;">
    <div class="ssg-modal-content">
        <span class="ssg-modal-close">&times;</span>
        <h2><?php _e('Import Settings', 'seo-sitemap-generator'); ?></h2>
        <p><?php _e('Paste your exported JSON settings below:', 'seo-sitemap-generator'); ?></p>
        <textarea id="ssg-import-data" rows="10" style="width:100%;"></textarea>
        <div style="margin-top:20px;">
            <button type="button" id="ssg-import-confirm" class="button button-primary">
                <?php _e('Import', 'seo-sitemap-generator'); ?>
            </button>
            <button type="button" class="button ssg-modal-close">
                <?php _e('Cancel', 'seo-sitemap-generator'); ?>
            </button>
        </div>
    </div>
</div>