/**
 * YJ19 RSS Feeds — Admin JS
 * ذخیره تنظیمات با AJAX
 */
(function ($) {
    'use strict';

    $(function () {
        var $form   = $('#yj19-rss-form');
        var $btn    = $('#yj19-rss-save-btn');
        var $status = $('#yj19-rss-status');

        if (!$form.length) {
            return;
        }

        $form.on('submit', function (e) {
            e.preventDefault();

            $btn.prop('disabled', true);
            $status
                .removeClass('is-success is-error')
                .addClass('is-saving')
                .text(yj19RssAdmin.strings.saving);

            // جمع‌آوری وضعیت چک‌باکس‌ها
            var postTypes = {};
            $form.find('input[type="checkbox"][name^="post_types["]').each(function () {
                var match = this.name.match(/^post_types\[([^\]]+)\]\[enabled\]$/);
                if (!match) return;
                var slug = match[1];
                postTypes[slug] = { enabled: this.checked ? 1 : 0 };
            });

            $.post(yj19RssAdmin.ajaxUrl, {
                action: 'yj19_rss_save',
                nonce: yj19RssAdmin.nonce,
                post_types: postTypes
            })
                .done(function (res) {
                    if (res && res.success) {
                        $status
                            .removeClass('is-saving is-error')
                            .addClass('is-success')
                            .text(res.data.message || yj19RssAdmin.strings.saved);
                    } else {
                        var msg = (res && res.data && res.data.message)
                            ? res.data.message
                            : yj19RssAdmin.strings.error;
                        $status
                            .removeClass('is-saving is-success')
                            .addClass('is-error')
                            .text(msg);
                    }
                })
                .fail(function () {
                    $status
                        .removeClass('is-saving is-success')
                        .addClass('is-error')
                        .text(yj19RssAdmin.strings.error);
                })
                .always(function () {
                    $btn.prop('disabled', false);
                    // پاک کردن پیام بعد از ۴ ثانیه (فقط موفقیت)
                    setTimeout(function () {
                        if ($status.hasClass('is-success')) {
                            $status.fadeOut(300, function () {
                                $(this).text('').removeClass('is-success').show();
                            });
                        }
                    }, 4000);
                });
        });
    });
})(jQuery);
