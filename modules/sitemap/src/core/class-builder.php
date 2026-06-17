<?php
/**
 * XML Sitemap Builder Class
 * 
 * Related Files:
 * - core/class-detector.php
 * - core/class-cache.php
 * - core/class-settings.php
 * - templates/sitemap.xsl
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

class SSG_Builder {
    
    private $settings;
    private $cache;
    private $detector;
    
    //-------------------------------
    // Constructor
    public function __construct() {
        $this->settings = SSG_Settings::instance();
        $this->cache = SSG_Cache::instance();
        $this->detector = SSG_Detector::instance();
    }
    
    //-------------------------------
    // Generate Sitemap by Name (Auto-detect type)
    public function generate_by_name($name) {
        if ($name === 'homepage') {
            return $this->generate('homepage', '');
        }
        
        if ($name === 'authors') {
            return $this->generate('authors', '');
        }
        
        if ($name === 'dates') {
            return $this->generate('dates', '');
        }
        
        if (post_type_exists($name)) {
            return $this->generate('post_type', $name);
        }
        
        if (taxonomy_exists($name)) {
            return $this->generate('taxonomy', $name);
        }
        
        return '';
    }
    
    //-------------------------------
    // Generate Sitemap
    public function generate($type = 'main', $subtype = '') {
        $cache_key = ssg_get_cache_key($type, $subtype);
        
        $xml = $this->cache->remember($cache_key, function() use ($type, $subtype) {
            return $this->build_sitemap($type, $subtype);
        });
        
        return $xml;
    }
    
    //-------------------------------
    // Build Sitemap
    private function build_sitemap($type, $subtype) {
        switch ($type) {
            case 'main':
                return $this->build_index();
                
            case 'homepage':
                return $this->build_homepage();
                
            case 'post_type':
                return $this->build_post_type($subtype);
                
            case 'taxonomy':
                return $this->build_taxonomy($subtype);
                
            case 'authors':
                return $this->build_authors();
                
            case 'dates':
                return $this->build_dates();
                
            default:
                return '';
        }
    }
    
    //-------------------------------
    // Build Main Index
    private function build_index() {
        $xml = $this->get_xml_header(true);
        
        $sitemaps = array();
        
        if ($this->settings->is_homepage_enabled()) {
            $sitemaps[] = array(
                'loc' => home_url('/sitemap-homepage.xml'),
                'lastmod' => ssg_format_date(get_lastpostmodified('gmt'))
            );
        }
        
        foreach ($this->settings->get_enabled_post_types() as $post_type => $config) {
            $sitemaps[] = array(
                'loc' => home_url('/sitemap-' . $post_type . '.xml'),
                'lastmod' => ssg_format_date(get_lastpostmodified('gmt', $post_type))
            );
        }
        
        foreach ($this->settings->get_enabled_taxonomies() as $taxonomy => $config) {
            $sitemaps[] = array(
                'loc' => home_url('/sitemap-' . $taxonomy . '.xml'),
                'lastmod' => ssg_format_date(current_time('mysql'))
            );
        }
        
        if ($this->settings->is_authors_enabled()) {
            $sitemaps[] = array(
                'loc' => home_url('/sitemap-authors.xml'),
                'lastmod' => ssg_format_date(current_time('mysql'))
            );
        }
        
        if ($this->settings->is_dates_enabled()) {
            $sitemaps[] = array(
                'loc' => home_url('/sitemap-dates.xml'),
                'lastmod' => ssg_format_date(current_time('mysql'))
            );
        }
        
        foreach ($sitemaps as $sitemap) {
            $xml .= "\t<sitemap>\n";
            $xml .= "\t\t<loc>" . esc_url($sitemap['loc']) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . $sitemap['lastmod'] . "</lastmod>\n";
            $xml .= "\t</sitemap>\n";
        }
        
        $xml .= "</sitemapindex>";
        
        return $xml;
    }
    
    //-------------------------------
    // Build Combined Sitemap (for Static mode)
    private function build_combined_sitemap() {
        $xml = $this->get_xml_header(false);
        
        // Homepage
        if ($this->settings->is_homepage_enabled()) {
            $homepage = $this->settings->get('homepage');
            $xml .= $this->build_url(
                home_url('/'),
                ssg_format_date(get_lastpostmodified('gmt')),
                $homepage['priority'],
                $homepage['changefreq']
            );
        }
        
        // Post Types
        foreach ($this->settings->get_enabled_post_types() as $post_type => $config) {
            $posts = $this->detector->get_posts_for_sitemap($post_type, $config);
            
            foreach ($posts as $post) {
                $changefreq = $config['changefreq'];
                if ($changefreq === 'auto') {
                    $changefreq = ssg_calc_auto_changefreq($post->post_modified);
                }
                
                $xml .= $this->build_url(
                    get_permalink($post),
                    ssg_format_date($post->post_modified),
                    $config['priority'],
                    $changefreq
                );
            }
        }
        
        // Taxonomies
        foreach ($this->settings->get_enabled_taxonomies() as $taxonomy => $config) {
            $terms = $this->detector->get_terms_for_sitemap($taxonomy, $config);
            
            foreach ($terms as $term) {
                $url = get_term_link($term);
                if (is_wp_error($url)) {
                    continue;
                }
                
                $xml .= $this->build_url(
                    $url,
                    ssg_format_date(current_time('mysql')),
                    $config['priority'],
                    $config['changefreq']
                );
            }
        }
        
        // Authors
        if ($this->settings->is_authors_enabled()) {
            $config = $this->settings->get('authors');
            $authors = $this->detector->get_authors_for_sitemap();
            
            foreach ($authors as $author) {
                $xml .= $this->build_url(
                    get_author_posts_url($author->ID),
                    ssg_format_date(current_time('mysql')),
                    $config['priority'],
                    $config['changefreq']
                );
            }
        }
        
        // Dates
        if ($this->settings->is_dates_enabled()) {
            $config = $this->settings->get('dates');
            $dates = $this->detector->get_dates_for_sitemap();
            
            foreach ($dates as $date) {
                $xml .= $this->build_url(
                    get_month_link($date->year, $date->month),
                    ssg_format_date($date->last_modified),
                    $config['priority'],
                    $config['changefreq']
                );
            }
        }
        
        $xml .= "</urlset>";
        
        return $xml;
    }
    
    //-------------------------------
    // Build Homepage Sitemap
    private function build_homepage() {
        $xml = $this->get_xml_header();
        
        $homepage = $this->settings->get('homepage');
        
        $xml .= $this->build_url(
            home_url('/'),
            ssg_format_date(get_lastpostmodified('gmt')),
            $homepage['priority'],
            $homepage['changefreq']
        );
        
        $xml .= "</urlset>";
        
        return $xml;
    }
    
    //-------------------------------
    // Build Post Type Sitemap
    private function build_post_type($post_type) {
        if (!$this->settings->is_post_type_enabled($post_type)) {
            return '';
        }
        
        $config = $this->settings->get_post_type_config($post_type);
        $posts = $this->detector->get_posts_for_sitemap($post_type, $config);
        
        $xml = $this->get_xml_header();
        
        foreach ($posts as $post) {
            $changefreq = $config['changefreq'];
            if ($changefreq === 'auto') {
                $changefreq = ssg_calc_auto_changefreq($post->post_modified);
            }
            
            $xml .= $this->build_url(
                get_permalink($post),
                ssg_format_date($post->post_modified),
                $config['priority'],
                $changefreq
            );
        }
        
        $xml .= "</urlset>";
        
        return $xml;
    }
    
    //-------------------------------
    // Build Taxonomy Sitemap
    private function build_taxonomy($taxonomy) {
        if (!$this->settings->is_taxonomy_enabled($taxonomy)) {
            return '';
        }
        
        $config = $this->settings->get_taxonomy_config($taxonomy);
        $terms = $this->detector->get_terms_for_sitemap($taxonomy, $config);
        
        $xml = $this->get_xml_header();
        
        foreach ($terms as $term) {
            $url = get_term_link($term);
            if (is_wp_error($url)) {
                continue;
            }
            
            $xml .= $this->build_url(
                $url,
                ssg_format_date(current_time('mysql')),
                $config['priority'],
                $config['changefreq']
            );
        }
        
        $xml .= "</urlset>";
        
        return $xml;
    }
    
    //-------------------------------
    // Build Authors Sitemap
    private function build_authors() {
        if (!$this->settings->is_authors_enabled()) {
            return '';
        }
        
        $config = $this->settings->get('authors');
        $authors = $this->detector->get_authors_for_sitemap();
        
        $xml = $this->get_xml_header();
        
        foreach ($authors as $author) {
            $xml .= $this->build_url(
                get_author_posts_url($author->ID),
                ssg_format_date(current_time('mysql')),
                $config['priority'],
                $config['changefreq']
            );
        }
        
        $xml .= "</urlset>";
        
        return $xml;
    }
    
    //-------------------------------
    // Build Dates Sitemap
    private function build_dates() {
        if (!$this->settings->is_dates_enabled()) {
            return '';
        }
        
        $config = $this->settings->get('dates');
        $dates = $this->detector->get_dates_for_sitemap();
        
        $xml = $this->get_xml_header();
        
        foreach ($dates as $date) {
            $xml .= $this->build_url(
                get_month_link($date->year, $date->month),
                ssg_format_date($date->last_modified),
                $config['priority'],
                $config['changefreq']
            );
        }
        
        $xml .= "</urlset>";
        
        return $xml;
    }
    
    //-------------------------------
    // Build Single URL Entry
    private function build_url($loc, $lastmod, $priority, $changefreq) {
        $xml = "\t<url>\n";
        $xml .= "\t\t<loc>" . esc_url($loc) . "</loc>\n";
        $xml .= "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
        $xml .= "\t\t<priority>" . ssg_format_priority($priority) . "</priority>\n";
        $xml .= "\t\t<changefreq>" . esc_attr($changefreq) . "</changefreq>\n";
        $xml .= "\t</url>\n";
        
        return $xml;
    }
    
    //-------------------------------
    // Get XML Header
    private function get_xml_header($is_index = false) {
        $mode = $this->settings->get('mode');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        
        // Add XSL stylesheet ONLY for dynamic mode
        if ($mode === 'dynamic') {
            $xsl_file = $is_index ? 'sitemap-index.xsl' : 'sitemap.xsl';
            $xml .= '<?xml-stylesheet type="text/xsl" href="' . SSG_URL . 'templates/' . $xsl_file . '?ver=' . SSG_VERSION . '"?>' . "\n";
        }
        
        if ($is_index) {
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        } else {
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        }
        
        return $xml;
    }
    
    //-------------------------------
    // Rebuild Static Files (Index + Separate Sitemaps)
    public function rebuild_static_file() {
        // Clear cache first
        $this->cache->clear_all();

        // Delete old static files first
        $this->delete_static_files();

        $files_created = array();

        // 1. Create Homepage sitemap
        if ($this->settings->is_homepage_enabled()) {
            $xml = $this->build_homepage();
            if (!empty($xml)) {
                $file_path = ABSPATH . 'sitemap-homepage.xml';
                if (file_put_contents($file_path, $xml) !== false) {
                    $files_created[] = 'sitemap-homepage.xml';
                }
            }
        }

        // 2. Create Post Type sitemaps
        foreach ($this->settings->get_enabled_post_types() as $post_type => $config) {
            $xml = $this->build_post_type($post_type);
            if (!empty($xml)) {
                $file_path = ABSPATH . 'sitemap-' . $post_type . '.xml';
                if (file_put_contents($file_path, $xml) !== false) {
                    $files_created[] = 'sitemap-' . $post_type . '.xml';
                }
            }
        }

        // 3. Create Taxonomy sitemaps
        foreach ($this->settings->get_enabled_taxonomies() as $taxonomy => $config) {
            $xml = $this->build_taxonomy($taxonomy);
            if (!empty($xml)) {
                $file_path = ABSPATH . 'sitemap-' . $taxonomy . '.xml';
                if (file_put_contents($file_path, $xml) !== false) {
                    $files_created[] = 'sitemap-' . $taxonomy . '.xml';
                }
            }
        }

        // 4. Create Authors sitemap
        if ($this->settings->is_authors_enabled()) {
            $xml = $this->build_authors();
            if (!empty($xml)) {
                $file_path = ABSPATH . 'sitemap-authors.xml';
                if (file_put_contents($file_path, $xml) !== false) {
                    $files_created[] = 'sitemap-authors.xml';
                }
            }
        }

        // 5. Create Dates sitemap
        if ($this->settings->is_dates_enabled()) {
            $xml = $this->build_dates();
            if (!empty($xml)) {
                $file_path = ABSPATH . 'sitemap-dates.xml';
                if (file_put_contents($file_path, $xml) !== false) {
                    $files_created[] = 'sitemap-dates.xml';
                }
            }
        }

        // 6. Create Main Index (sitemap.xml)
        if (!empty($files_created)) {
            $xml = $this->build_index();
            if (!empty($xml)) {
                $file_path = ABSPATH . 'sitemap.xml';
                if (file_put_contents($file_path, $xml) !== false) {
                    $files_created[] = 'sitemap.xml';
                }
            }
        }

        return !empty($files_created);
    }

    //-------------------------------
    // Delete All Static Sitemap Files
    public function delete_static_files() {
        // Main index
        $main_file = ABSPATH . 'sitemap.xml';
        if (file_exists($main_file)) {
            @unlink($main_file);
        }

        // Find and delete all sitemap-*.xml files
        $pattern = ABSPATH . 'sitemap-*.xml';
        $files = glob($pattern);

        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}