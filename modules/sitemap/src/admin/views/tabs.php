<?php

/**
 * Tab Content Views
 * 
 * Related Files:
 * - admin/views/dashboard.php
 * - core/class-detector.php
 * - core/class-settings.php
 */

//-------------------------------
// Security Check
if (!defined('ABSPATH')) {
    exit;
}

$manager = SSG_Manager::instance();
$settings = $manager->get_settings()->get_all();
$detector = $manager->get_detector();

$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'homepage';

//-------------------------------
// Homepage Tab
if ($current_tab === 'homepage'):
    $homepage = isset($settings['homepage']) ? $settings['homepage'] : array();
?>
    <div class="ssg-tab-content">
        <h2>🏠 <?php _e('Homepage Settings', 'seo-sitemap-generator'); ?></h2>
        <p><?php _e('Configure your homepage in the sitemap.', 'seo-sitemap-generator'); ?></p>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Enable Homepage', 'seo-sitemap-generator'); ?></th>
                <td>
                    <label class="ssg-toggle">
                        <input type="checkbox" name="homepage[enabled]" value="1" <?php checked(isset($homepage['enabled']) && $homepage['enabled']); ?>>
                        <span class="ssg-toggle-slider"></span>
                    </label>
                    <p class="description"><?php _e('Include homepage in sitemap (recommended).', 'seo-sitemap-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Priority', 'seo-sitemap-generator'); ?></th>
                <td>
                    <input type="number" name="homepage[priority]" value="<?php echo isset($homepage['priority']) ? esc_attr($homepage['priority']) : '1.0'; ?>" step="0.1" min="0" max="1" class="small-text">
                    <p class="description"><?php _e('Recommended: 1.0 (highest priority)', 'seo-sitemap-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Change Frequency', 'seo-sitemap-generator'); ?></th>
                <td>
                    <select name="homepage[changefreq]">
                        <?php
                        $freqs = ssg_get_changefreq_options();
                        $current = isset($homepage['changefreq']) ? $homepage['changefreq'] : 'daily';
                        foreach ($freqs as $value => $label):
                        ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('How often the homepage is updated.', 'seo-sitemap-generator'); ?></p>
                </td>
            </tr>
        </table>
    </div>

<?php
//-------------------------------
// Post Types Tab
elseif ($current_tab === 'post-types'):
    $post_types = $detector->get_post_types();
    $post_types_settings = isset($settings['post_types']) ? $settings['post_types'] : array();
?>
    <div class="ssg-tab-content">
        <h2>📝 <?php _e('Post Types Settings', 'seo-sitemap-generator'); ?></h2>
        <p><?php _e('Select which post types to include in sitemap.', 'seo-sitemap-generator'); ?></p>

        <?php foreach ($post_types as $post_type):
            $details = $detector->get_post_type_details($post_type->name);
            $config = isset($post_types_settings[$post_type->name]) ? $post_types_settings[$post_type->name] : array();
            $enabled = isset($config['enabled']) && $config['enabled'];
        ?>
            <div class="ssg-item-card">
                <div class="ssg-item-header">
                    <label class="ssg-toggle">
                        <input type="checkbox" name="post_types[<?php echo esc_attr($post_type->name); ?>][enabled]" value="1" <?php checked($enabled); ?> class="ssg-toggle-item">
                        <span class="ssg-toggle-slider"></span>
                    </label>
                    <div class="ssg-item-info">
                        <h3><?php echo esc_html($details['icon'] . ' ' . $details['label']); ?></h3>
                        <div class="ssg-item-meta">
                            📊 <?php printf(__('%d items', 'seo-sitemap-generator'), $details['count']); ?>
                            <?php if ($enabled): ?>
                                | <a href="<?php echo esc_url($manager->get_sitemap_url('post_type', $post_type->name)); ?>" target="_blank">
                                    🔗 <?php _e('View Sitemap', 'seo-sitemap-generator'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="button" class="ssg-toggle-settings">⚙️ <?php _e('Settings', 'seo-sitemap-generator'); ?></button>
                </div>
                <div class="ssg-item-settings" style="display:none;">
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Priority', 'seo-sitemap-generator'); ?></th>
                            <td>
                                <input type="number" name="post_types[<?php echo esc_attr($post_type->name); ?>][priority]" value="<?php echo isset($config['priority']) ? esc_attr($config['priority']) : '0.6'; ?>" step="0.1" min="0" max="1" class="small-text">
                                <span class="description"><?php _e('Recommended: 0.6-0.8', 'seo-sitemap-generator'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Change Frequency', 'seo-sitemap-generator'); ?></th>
                            <td>
                                <select name="post_types[<?php echo esc_attr($post_type->name); ?>][changefreq]">
                                    <?php
                                    $freqs = ssg_get_changefreq_options();
                                    $current = isset($config['changefreq']) ? $config['changefreq'] : 'weekly';
                                    foreach ($freqs as $value => $label):
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <?php if ($details['has_archive']): ?>
                            <tr>
                                <th><?php _e('Include Archive', 'seo-sitemap-generator'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="post_types[<?php echo esc_attr($post_type->name); ?>][include_archive]" value="1" <?php checked(isset($config['include_archive']) && $config['include_archive']); ?>>
                                        <?php _e('Include archive page', 'seo-sitemap-generator'); ?>
                                    </label>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php
//-------------------------------
// Taxonomies Tab (INDEPENDENT)
elseif ($current_tab === 'taxonomies'):
    $taxonomies = $detector->get_taxonomies();
    $taxonomies_settings = isset($settings['taxonomies']) ? $settings['taxonomies'] : array();
?>
    <div class="ssg-tab-content">
        <h2>📁 <?php _e('Taxonomies Settings', 'seo-sitemap-generator'); ?></h2>
        <p><?php _e('Select which taxonomies to include in sitemap (independent from post types).', 'seo-sitemap-generator'); ?></p>

        <?php foreach ($taxonomies as $taxonomy):
            $details = $detector->get_taxonomy_details($taxonomy->name);
            $config = isset($taxonomies_settings[$taxonomy->name]) ? $taxonomies_settings[$taxonomy->name] : array();
            $enabled = isset($config['enabled']) && $config['enabled'];
        ?>
            <div class="ssg-item-card">
                <div class="ssg-item-header">
                    <label class="ssg-toggle">
                        <input type="checkbox" name="taxonomies[<?php echo esc_attr($taxonomy->name); ?>][enabled]" value="1" <?php checked($enabled); ?> class="ssg-toggle-item">
                        <span class="ssg-toggle-slider"></span>
                    </label>
                    <div class="ssg-item-info">
                        <h3><?php echo esc_html($details['icon'] . ' ' . $details['label']); ?></h3>
                        <div class="ssg-item-meta">
                            📊 <?php printf(__('%d terms', 'seo-sitemap-generator'), $details['count']); ?>
                            <?php if ($enabled): ?>
                                | <a href="<?php echo esc_url($manager->get_sitemap_url('taxonomy', $taxonomy->name)); ?>" target="_blank">
                                    🔗 <?php _e('View Sitemap', 'seo-sitemap-generator'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="button" class="ssg-toggle-settings">⚙️ <?php _e('Settings', 'seo-sitemap-generator'); ?></button>
                </div>
                <div class="ssg-item-settings" style="display:none;">
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Priority', 'seo-sitemap-generator'); ?></th>
                            <td>
                                <input type="number" name="taxonomies[<?php echo esc_attr($taxonomy->name); ?>][priority]" value="<?php echo isset($config['priority']) ? esc_attr($config['priority']) : '0.5'; ?>" step="0.1" min="0" max="1" class="small-text">
                                <span class="description"><?php _e('Recommended: 0.5-0.6', 'seo-sitemap-generator'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Change Frequency', 'seo-sitemap-generator'); ?></th>
                            <td>
                                <select name="taxonomies[<?php echo esc_attr($taxonomy->name); ?>][changefreq]">
                                    <?php
                                    $freqs = ssg_get_changefreq_options();
                                    $current = isset($config['changefreq']) ? $config['changefreq'] : 'weekly';
                                    foreach ($freqs as $value => $label):
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php
//-------------------------------
// Authors Tab
elseif ($current_tab === 'authors'):
    $authors_config = isset($settings['authors']) ? $settings['authors'] : array();
    $enabled = isset($authors_config['enabled']) && $authors_config['enabled'];
    $count = ssg_count_urls('authors');
?>
    <div class="ssg-tab-content">
        <h2>👤 <?php _e('Authors Settings', 'seo-sitemap-generator'); ?></h2>
        <p><?php _e('Include author archive pages in sitemap.', 'seo-sitemap-generator'); ?></p>

        <div class="ssg-item-card">
            <div class="ssg-item-header">
                <label class="ssg-toggle">
                    <input type="checkbox" name="authors[enabled]" value="1" <?php checked($enabled); ?>>
                    <span class="ssg-toggle-slider"></span>
                </label>
                <div class="ssg-item-info">
                    <h3><?php _e('Authors Archive', 'seo-sitemap-generator'); ?></h3>
                    <div class="ssg-item-meta">
                        📊 <?php printf(__('%d authors', 'seo-sitemap-generator'), $count); ?>
                        <?php if ($enabled): ?>
                            | <a href="<?php echo esc_url($manager->get_sitemap_url('authors')); ?>" target="_blank">
                                🔗 <?php _e('View Sitemap', 'seo-sitemap-generator'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Priority', 'seo-sitemap-generator'); ?></th>
                <td>
                    <input type="number" name="authors[priority]" value="<?php echo isset($authors_config['priority']) ? esc_attr($authors_config['priority']) : '0.5'; ?>" step="0.1" min="0" max="1" class="small-text">
                    <p class="description"><?php _e('Recommended: 0.5', 'seo-sitemap-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Change Frequency', 'seo-sitemap-generator'); ?></th>
                <td>
                    <select name="authors[changefreq]">
                        <?php
                        $freqs = ssg_get_changefreq_options();
                        $current = isset($authors_config['changefreq']) ? $authors_config['changefreq'] : 'monthly';
                        foreach ($freqs as $value => $label):
                        ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
    </div>

<?php
//-------------------------------
// Dates Tab
elseif ($current_tab === 'dates'):
    $dates_config = isset($settings['dates']) ? $settings['dates'] : array();
    $enabled = isset($dates_config['enabled']) && $dates_config['enabled'];
    $count = ssg_count_urls('dates');
?>
    <div class="ssg-tab-content">
        <h2>📅 <?php _e('Date Archives Settings', 'seo-sitemap-generator'); ?></h2>
        <p><?php _e('Include date-based archives in sitemap (recommended for news sites only).', 'seo-sitemap-generator'); ?></p>

        <div class="ssg-item-card">
            <div class="ssg-item-header">
                <label class="ssg-toggle">
                    <input type="checkbox" name="dates[enabled]" value="1" <?php checked($enabled); ?>>
                    <span class="ssg-toggle-slider"></span>
                </label>
                <div class="ssg-item-info">
                    <h3><?php _e('Date Archives', 'seo-sitemap-generator'); ?></h3>
                    <div class="ssg-item-meta">
                        📊 <?php printf(__('%d archives', 'seo-sitemap-generator'), $count); ?>
                        <?php if ($enabled): ?>
                            | <a href="<?php echo esc_url($manager->get_sitemap_url('dates')); ?>" target="_blank">
                                🔗 <?php _e('View Sitemap', 'seo-sitemap-generator'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Priority', 'seo-sitemap-generator'); ?></th>
                <td>
                    <input type="number" name="dates[priority]" value="<?php echo isset($dates_config['priority']) ? esc_attr($dates_config['priority']) : '0.3'; ?>" step="0.1" min="0" max="1" class="small-text">
                    <p class="description"><?php _e('Recommended: 0.3', 'seo-sitemap-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Change Frequency', 'seo-sitemap-generator'); ?></th>
                <td>
                    <select name="dates[changefreq]">
                        <?php
                        $freqs = ssg_get_changefreq_options();
                        $current = isset($dates_config['changefreq']) ? $dates_config['changefreq'] : 'yearly';
                        foreach ($freqs as $value => $label):
                        ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current, $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
    </div>

<?php
//-------------------------------
// WooCommerce Tab
elseif ($current_tab === 'woocommerce' && ssg_is_woocommerce_active()):
?>
    <div class="ssg-tab-content">
        <h2>🛒 <?php _e('WooCommerce Settings', 'seo-sitemap-generator'); ?></h2>
        <p><?php _e('WooCommerce post types and taxonomies are managed in Post Types and Taxonomies tabs.', 'seo-sitemap-generator'); ?></p>

        <div class="notice notice-info inline">
            <p><?php _e('This tab shows WooCommerce-specific settings. To enable/disable products and categories, use Post Types and Taxonomies tabs.', 'seo-sitemap-generator'); ?></p>
        </div>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Exclude Out of Stock', 'seo-sitemap-generator'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="exclude_out_of_stock" value="1" <?php checked(isset($settings['exclude_out_of_stock']) && $settings['exclude_out_of_stock']); ?>>
                        <?php _e('Exclude products that are out of stock', 'seo-sitemap-generator'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Exclude Hidden Products', 'seo-sitemap-generator'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="exclude_hidden" value="1" <?php checked(isset($settings['exclude_hidden']) && $settings['exclude_hidden']); ?>>
                        <?php _e('Exclude products hidden from catalog', 'seo-sitemap-generator'); ?>
                    </label>
                </td>
            </tr>
        </table>
    </div>

<?php
//-------------------------------
// Advanced Tab
elseif ($current_tab === 'advanced'):
    $mode = isset($settings['mode']) ? $settings['mode'] : 'dynamic';
    $cache_duration = isset($settings['cache_duration']) ? $settings['cache_duration'] : 24;
    $excluded_urls = isset($settings['excluded_urls']) ? $settings['excluded_urls'] : array();
?>
    <div class="ssg-tab-content">
        <h2>⚙️ <?php _e('Advanced Settings', 'seo-sitemap-generator'); ?></h2>

        <h3><?php _e('Generation Method', 'seo-sitemap-generator'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Mode', 'seo-sitemap-generator'); ?></th>
                <td>
                    <label>
                        <input type="radio" name="mode" value="dynamic" <?php checked($mode, 'dynamic'); ?>>
                        <?php _e('Dynamic', 'seo-sitemap-generator'); ?>
                        <span class="description"><?php _e('- Beautiful UI with XSL stylesheet (recommended)', 'seo-sitemap-generator'); ?></span>
                    </label>
                    <br><br>
                    <label>
                        <input type="radio" name="mode" value="static" <?php checked($mode, 'static'); ?>>
                        <?php _e('Static', 'seo-sitemap-generator'); ?>
                        <span class="description"><?php _e('- Physical XML file (raw code only)', 'seo-sitemap-generator'); ?></span>
                    </label>
                </td>
            </tr>
        </table>

        <h3><?php _e('Cache Settings', 'seo-sitemap-generator'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Cache Duration', 'seo-sitemap-generator'); ?></th>
                <td>
                    <input type="number" name="cache_duration" value="<?php echo esc_attr($cache_duration); ?>" min="1" max="168" class="small-text"> <?php _e('hours', 'seo-sitemap-generator'); ?>
                    <p class="description"><?php _e('Recommended: 24 hours. Min: 1, Max: 168 (1 week)', 'seo-sitemap-generator'); ?></p>
                </td>
            </tr>
        </table>

        <h3><?php _e('Excluded URLs', 'seo-sitemap-generator'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('URLs to Exclude', 'seo-sitemap-generator'); ?></th>
                <td>
                    <textarea name="excluded_urls" rows="8" class="large-text code"><?php echo esc_textarea(is_array($excluded_urls) ? implode("\n", $excluded_urls) : ''); ?></textarea>
                    <p class="description">
                        <?php _e('One URL per line. Supports wildcards (*). Examples:', 'seo-sitemap-generator'); ?><br>
                        <code>https://example.com/private-page/</code><br>
                        <code>/category/uncategorized/</code><br>
                        <code>*/cart/</code><br>
                        <code>/test/*</code>
                    </p>
                </td>
            </tr>
        </table>

        <h3><?php _e('Exclusion Rules', 'seo-sitemap-generator'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Automatic Exclusions', 'seo-sitemap-generator'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="exclude_password" value="1" <?php checked(isset($settings['exclude_password']) && $settings['exclude_password']); ?>>
                        <?php _e('Exclude password-protected pages', 'seo-sitemap-generator'); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="exclude_redirects" value="1" <?php checked(isset($settings['exclude_redirects']) && $settings['exclude_redirects']); ?>>
                        <?php _e('Exclude redirects', 'seo-sitemap-generator'); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="exclude_noindex" value="1" <?php checked(isset($settings['exclude_noindex']) && $settings['exclude_noindex']); ?>>
                        <?php _e('Exclude pages with noindex meta tag', 'seo-sitemap-generator'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <h3><?php _e('Search Engine Ping', 'seo-sitemap-generator'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Auto Ping', 'seo-sitemap-generator'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="auto_ping" value="1" <?php checked(isset($settings['auto_ping']) && $settings['auto_ping']); ?>>
                        <?php _e('Automatically ping search engines on content changes', 'seo-sitemap-generator'); ?>
                    </label>
                </td>
            </tr>
        </table>
    </div>

<?php endif; ?>