<?php

/**
 * WordPress Hooks & Filters
 * 
 * Related Files:
 * - custom-sitemap.php
 * - includes/helpers.php
 * - core/class-manager.php
 * - core/class-builder.php
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

//-------------------------------
// Add Rewrite Rules for Dynamic Mode
function ssg_add_rewrite_rules()
{
    $settings = ssg_get_settings();

    // Only add rewrite rules in dynamic mode
    if (isset($settings['mode']) && $settings['mode'] === 'dynamic') {
        // Main sitemap index
        add_rewrite_rule('^sitemap\.xml$', 'index.php?ssg_sitemap=main', 'top');

        // All sitemaps (homepage, post types, taxonomies, authors, dates)
        add_rewrite_rule('^sitemap-([a-z0-9_-]+)\.xml$', 'index.php?ssg_sitemap_name=$matches[1]', 'top');
    }
}
add_action('init', 'ssg_add_rewrite_rules', 1);

//-------------------------------
// Add Query Vars
function ssg_query_vars($vars)
{
    $vars[] = 'ssg_sitemap';
    $vars[] = 'ssg_sitemap_name';
    return $vars;
}
add_filter('query_vars', 'ssg_query_vars');

//-------------------------------
// Delete static file if in dynamic mode
function ssg_maybe_delete_static_file()
{
    $settings = ssg_get_settings();

    if (isset($settings['mode']) && $settings['mode'] === 'dynamic') {
        $static_file = ABSPATH . 'sitemap.xml';
        if (file_exists($static_file)) {
            @unlink($static_file);
        }
    }
}
add_action('init', 'ssg_maybe_delete_static_file', 0);

//-------------------------------
// Handle Sitemap Request - Works on wp_loaded (after all post types registered)
function ssg_handle_sitemap_request()
{
    // Get request URI
    $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    // Quick check - must contain sitemap and .xml
    if (strpos($request, 'sitemap') === false || strpos($request, '.xml') === false) {
        return;
    }

    $settings = ssg_get_settings();

    // Only for dynamic mode
    if (!isset($settings['mode']) || $settings['mode'] !== 'dynamic') {
        return;
    }

    // Detect sitemap type from URL
    $sitemap_type = '';
    $sitemap_name = '';

    if (preg_match('/sitemap-([a-z0-9_-]+)\.xml/i', $request, $matches)) {
        $sitemap_name = $matches[1];
    } elseif (preg_match('/sitemap\.xml/i', $request)) {
        $sitemap_type = 'main';
    }

    if (empty($sitemap_type) && empty($sitemap_name)) {
        return;
    }

    // Generate sitemap
    require_once SSG_PATH . 'core/class-builder.php';
    $builder = new SSG_Builder();

    $xml = '';

    if ($sitemap_type === 'main') {
        $xml = $builder->generate('main', '');
    } elseif (!empty($sitemap_name)) {
        $xml = $builder->generate_by_name($sitemap_name);
    }

    // Output headers
    if (!headers_sent()) {
        status_header(200);
        nocache_headers();
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');
    }

    // Output XML
    echo $xml;
    exit;
}
add_action('wp_loaded', 'ssg_handle_sitemap_request', 0);

//-------------------------------
// Handle Sitemap Request (Template Redirect - Backup)
function ssg_template_redirect()
{
    global $wp_query;

    $sitemap_main = get_query_var('ssg_sitemap');
    $sitemap_name = get_query_var('ssg_sitemap_name');

    // Check if it's a sitemap request
    if (empty($sitemap_main) && empty($sitemap_name)) {
        return;
    }

    $settings = ssg_get_settings();

    // Only for dynamic mode
    if ($settings['mode'] !== 'dynamic') {
        return;
    }

    // Get sitemap content
    require_once SSG_PATH . 'core/class-builder.php';
    $builder = new SSG_Builder();

    $xml = '';

    // Main index
    if ($sitemap_main === 'main') {
        $xml = $builder->generate('main', '');
    }
    // Named sitemaps
    elseif (!empty($sitemap_name)) {
        // Detect type and generate
        $xml = $builder->generate_by_name($sitemap_name);
    }

    if (empty($xml)) {
        status_header(404);
        exit;
    }

    // Set headers
    status_header(200);
    header('Content-Type: application/xml; charset=utf-8');
    header('X-Robots-Tag: noindex, follow');

    // Output XML
    echo $xml;
    exit;
}
add_action('template_redirect', 'ssg_template_redirect', 1);

//-------------------------------
// Clear Cache on Post Publish/Update
function ssg_clear_cache_on_post_save($post_id, $post, $update)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ($post->post_status !== 'publish') {
        return;
    }

    $cache_key = ssg_get_cache_key('post_type', $post->post_type);
    delete_transient($cache_key);
    delete_transient(ssg_get_cache_key('main'));

    $settings = ssg_get_settings();
    if (isset($settings['auto_ping']) && $settings['auto_ping']) {
        wp_schedule_single_event(time() + 60, 'ssg_ping_engines');
    }

    if (isset($settings['mode']) && $settings['mode'] === 'static') {
        wp_schedule_single_event(time() + 30, 'ssg_rebuild_static');
    }
}
add_action('save_post', 'ssg_clear_cache_on_post_save', 10, 3);

//-------------------------------
// Clear Cache on Post Delete
function ssg_clear_cache_on_post_delete($post_id)
{
    $post = get_post($post_id);

    if (!$post) {
        return;
    }

    $cache_key = ssg_get_cache_key('post_type', $post->post_type);
    delete_transient($cache_key);
    delete_transient(ssg_get_cache_key('main'));

    $settings = ssg_get_settings();
    if (isset($settings['mode']) && $settings['mode'] === 'static') {
        wp_schedule_single_event(time() + 30, 'ssg_rebuild_static');
    }
}
add_action('before_delete_post', 'ssg_clear_cache_on_post_delete');

//-------------------------------
// Clear Cache on Term Create/Update
function ssg_clear_cache_on_term_save($term_id, $tt_id, $taxonomy)
{
    $cache_key = ssg_get_cache_key('taxonomy', $taxonomy);
    delete_transient($cache_key);
    delete_transient(ssg_get_cache_key('main'));

    $settings = ssg_get_settings();
    if (isset($settings['mode']) && $settings['mode'] === 'static') {
        wp_schedule_single_event(time() + 30, 'ssg_rebuild_static');
    }
}
add_action('create_term', 'ssg_clear_cache_on_term_save', 10, 3);
add_action('edit_term', 'ssg_clear_cache_on_term_save', 10, 3);

//-------------------------------
// Clear Cache on Term Delete
function ssg_clear_cache_on_term_delete($term_id, $tt_id, $taxonomy)
{
    $cache_key = ssg_get_cache_key('taxonomy', $taxonomy);
    delete_transient($cache_key);
    delete_transient(ssg_get_cache_key('main'));

    $settings = ssg_get_settings();
    if (isset($settings['mode']) && $settings['mode'] === 'static') {
        wp_schedule_single_event(time() + 30, 'ssg_rebuild_static');
    }
}
add_action('delete_term', 'ssg_clear_cache_on_term_delete', 10, 3);

//-------------------------------
// Add Sitemap to robots.txt
function ssg_robots_txt($output)
{
    $sitemap_url = home_url('/sitemap.xml');
    $output .= "\nSitemap: " . $sitemap_url . "\n";
    return $output;
}
add_filter('robots_txt', 'ssg_robots_txt', 999);

//-------------------------------
// Disable Conflicting SEO Plugins
function ssg_disable_seo_conflicts()
{
    add_filter('wpseo_sitemap_index', '__return_false', 999);
    add_filter('wpseo_build_sitemap_post_type', '__return_false', 999);
    add_filter('rank_math/sitemap/enable', '__return_false', 999);
    add_filter('aioseo_sitemap_indexes', '__return_empty_array', 999);
}
add_action('init', 'ssg_disable_seo_conflicts', 1);

//-------------------------------
// Schedule Events
function ssg_ping_engines_scheduled()
{
    ssg_ping_search_engines();
}
add_action('ssg_ping_engines', 'ssg_ping_engines_scheduled');

function ssg_rebuild_static_scheduled()
{
    require_once SSG_PATH . 'core/class-builder.php';
    $builder = new SSG_Builder();
    $builder->rebuild_static_file();
}
add_action('ssg_rebuild_static', 'ssg_rebuild_static_scheduled');

//-------------------------------
// Force Flush Rewrite Rules
function ssg_maybe_flush_rewrite()
{
    $version = get_option('ssg_rewrite_version', '0.0.0');

    // Force flush if version changed OR if old version option exists (migration)
    $old_version = get_option('yj19_sitemap_rewrite_version', false);
    $needs_flush = version_compare($version, SSG_VERSION, '<') || $old_version !== false;

    if ($needs_flush) {
        flush_rewrite_rules();
        update_option('ssg_rewrite_version', SSG_VERSION);
        if ($old_version !== false) {
            delete_option('yj19_sitemap_rewrite_version');
        }
    }
}
add_action('admin_init', 'ssg_maybe_flush_rewrite');
