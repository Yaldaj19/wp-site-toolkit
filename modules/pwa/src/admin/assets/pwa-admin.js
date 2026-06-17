/* YJ19 PWA Admin */
jQuery(function ($) {

    // ---- Tabs ----
    $('.yj19-pwa-tab').on('click', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        $('.yj19-pwa-tab').removeClass('active');
        $(this).addClass('active');
        $('.yj19-pwa-tab-pane').removeClass('active');
        $('#' + target).addClass('active');
        try { sessionStorage.setItem('yj19_pwa_admin_tab', target); } catch (e) {}
    });
    try {
        var saved = sessionStorage.getItem('yj19_pwa_admin_tab');
        if (saved && $('#' + saved).length) {
            $('.yj19-pwa-tab').removeClass('active').filter('[data-target="' + saved + '"]').addClass('active');
            $('.yj19-pwa-tab-pane').removeClass('active');
            $('#' + saved).addClass('active');
        }
    } catch (e) {}

    // ---- Color pickers ----
    $('.yj19-pwa-color').wpColorPicker({
        change: function () { setTimeout(updatePreview, 50); },
        clear: function () { setTimeout(updatePreview, 50); }
    });

    // ---- Media uploader ----
    $('.yj19-pwa-upload').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var targetId = $btn.data('target');
        var $input = $btn.siblings('input[type="text"][data-preview="' + targetId + '"]');

        var frame = wp.media({
            title: 'انتخاب تصویر',
            button: { text: 'انتخاب' },
            multiple: false
        });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            $input.val(att.url).trigger('change');
            $('#' + targetId).html('<img src="' + att.url + '" alt="">');
            updatePreview();
        });
        frame.open();
    });

    $('.yj19-pwa-remove').on('click', function (e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        var $input = $(this).siblings('input[type="text"][data-preview="' + targetId + '"]');
        $input.val('').trigger('change');
        $('#' + targetId).empty();
        updatePreview();
    });

    // ---- Style picker (radio cards) ----
    $('input[name="yj19_pwa[button_style]"]').on('change', function () {
        $('.yj19-pwa-style-option').removeClass('active');
        $(this).closest('.yj19-pwa-style-option').addClass('active');
        toggleStyleFields();
        updatePreview();
    });

    function toggleStyleFields() {
        var current = $('input[name="yj19_pwa[button_style]"]:checked').val();
        $('.yj19-pwa-style-field').each(function () {
            var show = ($(this).data('show-for') + '').split(' ');
            $(this).toggle(show.indexOf(current) !== -1);
        });
    }
    toggleStyleFields();

    // ---- Range slider value ----
    $('#yj19_pwa_button_radius').on('input change', function () {
        $('.yj19-pwa-range-value').text($(this).val() + 'px');
        updatePreview();
    });

    // ---- Text/position changes ----
    $('#yj19_pwa_button_text').on('input', updatePreview);
    $('select[name="yj19_pwa[button_position]"]').on('change', updatePreview);
    $('input[name="yj19_pwa[button_shadow]"]').on('change', updatePreview);
    $('input[name="yj19_pwa[button_icon]"]').on('change', updatePreview);
    $('#yj19_pwa_button_icon_svg').on('input', updatePreview);

    // ---- Live preview ----
    function getColor(name) {
        return $('input[name="yj19_pwa[' + name + ']"]').val() || '';
    }

    function updatePreview() {
        var $btn = $('#yj19-pwa-preview-btn');
        if (!$btn.length) return;

        var style    = $('input[name="yj19_pwa[button_style]"]:checked').val() || 'gradient';
        var position = $('select[name="yj19_pwa[button_position]"]').val() || 'bottom-center';
        var text     = $('#yj19_pwa_button_text').val() || 'نصب اپلیکیشن';
        var radius   = parseInt($('#yj19_pwa_button_radius').val(), 10) || 14;
        var shadow   = $('input[name="yj19_pwa[button_shadow]"]').is(':checked');
        var icon     = $('input[name="yj19_pwa[button_icon]"]').val() || '';
        var iconSvg  = $('#yj19_pwa_button_icon_svg').val() || '';

        var color    = getColor('button_color');
        var txtColor = getColor('button_text_color');
        var gFrom    = getColor('button_gradient_from');
        var gTo      = getColor('button_gradient_to');

        var bg;
        if (style === 'solid')         bg = color || '#F37021';
        else if (style === 'gradient') bg = 'linear-gradient(135deg, ' + (gFrom || '#F37021') + ', ' + (gTo || '#ff8a4c') + ')';
        else                           bg = 'rgba(' + hexToRgb(color || '#F37021').join(',') + ',0.25)';

        $btn.attr('data-style', style)
            .attr('data-position', position)
            .css({
                background: bg,
                color: txtColor || '#fff',
                borderRadius: radius + 'px',
                boxShadow: shadow ? '0 8px 22px -6px rgba(0,0,0,.25)' : 'none',
                backdropFilter: style === 'glass' ? 'blur(10px)' : 'none',
                webkitBackdropFilter: style === 'glass' ? 'blur(10px)' : 'none',
                border: style === 'glass' ? '1px solid rgba(255,255,255,.4)' : '0'
            });

        $btn.find('.yj19-pwa-install-btn__text').text(text);

        var $icon = $btn.find('.yj19-pwa-install-btn__icon');
        $icon.removeClass('yj19-pwa-install-btn__icon--svg');

        if (iconSvg && /<svg[\s\S]*<\/svg>/i.test(iconSvg)) {
            var safeSvg = sanitizeSvgClient(iconSvg);
            $icon.attr('data-custom', 'svg').attr('style', '').empty()
                .addClass('yj19-pwa-install-btn__icon--svg')
                .html(safeSvg);
        } else if (icon) {
            $icon.empty().attr('data-custom', '1').css({
                '--yj19-pwa-icon-bg': 'url(' + icon + ')',
                background: 'url(' + icon + ') center / contain no-repeat',
                webkitMask: 'none',
                mask: 'none',
                width: '22px',
                height: '22px'
            });
        } else {
            $icon.empty().removeAttr('data-custom').attr('style', '');
        }
    }

    // Client-side sanitize: حذف script/event handlerها در preview
    function sanitizeSvgClient(raw) {
        var m = raw.match(/<svg[\s\S]*<\/svg>/i);
        if (!m) return '';
        var svg = m[0];
        svg = svg.replace(/<(script|style|foreignObject|iframe|object|embed)\b[\s\S]*?<\/\1>/gi, '');
        svg = svg.replace(/<(script|style|foreignObject|iframe|object|embed)\b[^>]*\/?>/gi, '');
        svg = svg.replace(/\s+on[a-z]+\s*=\s*("[^"]*"|'[^']*'|[^\s>]+)/gi, '');
        svg = svg.replace(/\s+(href|xlink:href)\s*=\s*("\s*javascript:[^"]*"|'\s*javascript:[^']*')/gi, '');
        return svg;
    }

    function hexToRgb(hex) {
        hex = (hex || '').replace('#', '');
        if (hex.length === 3) hex = hex.split('').map(function (c) { return c + c; }).join('');
        var n = parseInt(hex, 16);
        return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
    }

    updatePreview();
});
