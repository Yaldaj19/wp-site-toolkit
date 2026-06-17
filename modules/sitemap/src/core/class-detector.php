<?php

/**
 * Post Types & Taxonomies Detector Class
 * 
 * Related Files:
 * - core/class-settings.php
 * - admin/class-admin.php
 * - includes/helpers.php
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

class SSG_Detector
{

    private static $instance = null;

    //-------------------------------
    // Singleton Instance
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //-------------------------------
    // Constructor
    private function __construct()
    {
        // Nothing here
    }

    //-------------------------------
    // Get All Public Post Types
    public function get_post_types()
    {
        $args = array(
            'public' => true,
            '_builtin' => false
        );

        $post_types = get_post_types($args, 'objects');

        // Add built-in post types
        $builtin = array('post', 'page');
        foreach ($builtin as $type) {
            $obj = get_post_type_object($type);
            if ($obj && $obj->public) {
                $post_types[$type] = $obj;
            }
        }

        // Remove unwanted types
        $exclude = array('attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation');

        foreach ($exclude as $type) {
            if (isset($post_types[$type])) {
                unset($post_types[$type]);
            }
        }

        return $post_types;
    }

    //-------------------------------
    // Get All Public Taxonomies (INDEPENDENT)
    public function get_taxonomies()
    {
        $args = array(
            'public' => true
        );

        $taxonomies = get_taxonomies($args, 'objects');

        // Remove unwanted taxonomies
        $exclude = array('post_format', 'nav_menu', 'link_category', 'wp_theme', 'wp_template_part_area');

        foreach ($exclude as $tax) {
            if (isset($taxonomies[$tax])) {
                unset($taxonomies[$tax]);
            }
        }

        return $taxonomies;
    }

    //-------------------------------
    // Get Post Type Details
    public function get_post_type_details($post_type)
    {
        $obj = get_post_type_object($post_type);

        if (!$obj) {
            return false;
        }

        $count = wp_count_posts($post_type);
        $total = isset($count->publish) ? $count->publish : 0;

        return array(
            'name' => $post_type,
            'label' => $obj->label,
            'singular_name' => $obj->labels->singular_name,
            'icon' => ssg_get_icon($post_type),
            'count' => $total,
            'has_archive' => $obj->has_archive,
            'hierarchical' => $obj->hierarchical
        );
    }

    //-------------------------------
    // Get Taxonomy Details (INDEPENDENT)
    public function get_taxonomy_details($taxonomy)
    {
        $obj = get_taxonomy($taxonomy);

        if (!$obj) {
            return false;
        }

        $count = wp_count_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ));

        return array(
            'name' => $taxonomy,
            'label' => $obj->label,
            'singular_name' => $obj->labels->singular_name,
            'icon' => ssg_get_icon($taxonomy),
            'count' => $count,
            'hierarchical' => $obj->hierarchical,
            'object_type' => $obj->object_type
        );
    }

    //-------------------------------
    // Get WooCommerce Post Types
    public function get_woocommerce_types()
    {
        if (!ssg_is_woocommerce_active()) {
            return array();
        }

        return array(
            'product' => $this->get_post_type_details('product'),
            'product_variation' => $this->get_post_type_details('product_variation')
        );
    }

    //-------------------------------
    // Get WooCommerce Taxonomies
    public function get_woocommerce_taxonomies()
    {
        if (!ssg_is_woocommerce_active()) {
            return array();
        }

        return array(
            'product_cat' => $this->get_taxonomy_details('product_cat'),
            'product_tag' => $this->get_taxonomy_details('product_tag')
        );
    }

    //-------------------------------
    // Check if Post Type Exists
    public function post_type_exists($post_type)
    {
        return post_type_exists($post_type);
    }

    //-------------------------------
    // Check if Taxonomy Exists
    public function taxonomy_exists($taxonomy)
    {
        return taxonomy_exists($taxonomy);
    }

    //-------------------------------
    // Get Posts for Sitemap
    public function get_posts_for_sitemap($post_type, $config)
    {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false
        );

        // Exclude password protected
        $settings = ssg_get_settings();
        if (isset($settings['exclude_password']) && $settings['exclude_password']) {
            $args['has_password'] = false;
        }

        // WooCommerce specific: exclude out of stock products
        if ($post_type === 'product' && ssg_is_woocommerce_active()) {
            if (isset($settings['exclude_out_of_stock']) && $settings['exclude_out_of_stock']) {
                $args['meta_query'][] = array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '!='
                );
            }

            // Exclude hidden products from catalog
            if (isset($settings['exclude_hidden']) && $settings['exclude_hidden']) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'product_visibility',
                    'field' => 'name',
                    'terms' => array('exclude-from-catalog', 'exclude-from-search'),
                    'operator' => 'NOT IN'
                );
            }
        }

        $posts = get_posts($args);

        // Filter excluded posts
        $posts = array_filter($posts, function ($post) {
            return !ssg_should_exclude_post($post);
        });

        return $posts;
    }

    //-------------------------------
    // Get Terms for Sitemap (INDEPENDENT)
    public function get_terms_for_sitemap($taxonomy, $config)
    {
        $args = array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'count',
            'order' => 'DESC'
        );

        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            return array();
        }

        // Filter excluded terms
        $terms = array_filter($terms, function ($term) {
            $url = get_term_link($term);
            if (is_wp_error($url)) {
                return false;
            }
            return !ssg_is_url_excluded($url);
        });

        return $terms;
    }

    //-------------------------------
    // Get Authors for Sitemap
    public function get_authors_for_sitemap()
    {
        $args = array(
            'who' => 'authors',
            'has_published_posts' => true,
            'orderby' => 'post_count',
            'order' => 'DESC'
        );

        return get_users($args);
    }

    //-------------------------------
    // Get Date Archives for Sitemap
    public function get_dates_for_sitemap()
    {
        global $wpdb;

        $dates = $wpdb->get_results(
            "SELECT YEAR(post_date) as year, MONTH(post_date) as month, COUNT(*) as count, MAX(post_modified) as last_modified
            FROM {$wpdb->posts}
            WHERE post_status = 'publish' AND post_type = 'post'
            GROUP BY YEAR(post_date), MONTH(post_date)
            ORDER BY year DESC, month DESC"
        );

        return $dates;
    }

    //-------------------------------
    // Get All Sitemap Data
    public function get_all_sitemap_data()
    {
        $data = array();

        $settings = SSG_Settings::instance();

        // Homepage
        if ($settings->is_homepage_enabled()) {
            $data['homepage'] = array(
                'url' => home_url('/'),
                'lastmod' => get_lastpostmodified('gmt'),
                'priority' => $settings->get('homepage')['priority'],
                'changefreq' => $settings->get('homepage')['changefreq']
            );
        }

        // Post Types
        foreach ($settings->get_enabled_post_types() as $post_type => $config) {
            $data['post_types'][$post_type] = $this->get_posts_for_sitemap($post_type, $config);
        }

        // Taxonomies (INDEPENDENT)
        foreach ($settings->get_enabled_taxonomies() as $taxonomy => $config) {
            $data['taxonomies'][$taxonomy] = $this->get_terms_for_sitemap($taxonomy, $config);
        }

        // Authors
        if ($settings->is_authors_enabled()) {
            $data['authors'] = $this->get_authors_for_sitemap();
        }

        // Dates
        if ($settings->is_dates_enabled()) {
            $data['dates'] = $this->get_dates_for_sitemap();
        }

        return $data;
    }
}
