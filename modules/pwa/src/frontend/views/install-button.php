<?php
/**
 * YJ19 PWA — Install Button Markup
 *
 * متغیرهای موجود: $settings
 *
 * @package Yj19\PWA
 */
if (!defined('ABSPATH')) {
    exit;
}

$style_attrs = array();

if ($settings['button_style'] === 'solid') {
    $style_attrs[] = 'background:' . esc_attr($settings['button_color']);
} elseif ($settings['button_style'] === 'gradient') {
    $style_attrs[] = 'background:linear-gradient(135deg,' . esc_attr($settings['button_gradient_from']) . ',' . esc_attr($settings['button_gradient_to']) . ')';
} else {
    // glass — رنگ پایه با شفافیت
    $hex  = ltrim($settings['button_color'], '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $rgb = array_map('hexdec', str_split($hex, 2));
    $style_attrs[] = sprintf('background:rgba(%d,%d,%d,.25)', $rgb[0], $rgb[1], $rgb[2]);
}

$style_attrs[] = 'color:' . esc_attr($settings['button_text_color']);
$style_attrs[] = 'border-radius:' . (int) $settings['button_radius'] . 'px';
if (!empty($settings['button_shadow'])) {
    $style_attrs[] = 'box-shadow:0 8px 22px -6px rgba(0,0,0,.25)';
}
?>
<button type="button"
        id="yj19-pwa-install-btn"
        class="yj19-pwa-install-btn"
        data-position="<?php echo esc_attr($settings['button_position']); ?>"
        data-style="<?php echo esc_attr($settings['button_style']); ?>"
        hidden
        aria-label="<?php echo esc_attr($settings['button_text']); ?>"
        style="<?php echo esc_attr(implode(';', $style_attrs)); ?>">
    <?php if (!empty($settings['button_icon_svg'])) : ?>
        <span class="yj19-pwa-install-btn__icon yj19-pwa-install-btn__icon--svg" data-custom="svg" aria-hidden="true"><?php echo $settings['button_icon_svg']; // already sanitized in Settings::sanitize ?></span>
    <?php elseif (!empty($settings['button_icon'])) : ?>
        <span class="yj19-pwa-install-btn__icon" data-custom="1"
              style="background:url('<?php echo esc_url($settings['button_icon']); ?>') center / contain no-repeat;-webkit-mask:none;mask:none;width:22px;height:22px;"></span>
    <?php else : ?>
        <span class="yj19-pwa-install-btn__icon" aria-hidden="true"></span>
    <?php endif; ?>
    <span class="yj19-pwa-install-btn__text"><?php echo esc_html($settings['button_text']); ?></span>
    <button type="button" class="yj19-pwa-install-btn__close" aria-label="بستن" data-dismiss>&times;</button>
</button>
