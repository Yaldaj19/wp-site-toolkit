/* YJ19 PWA — Frontend */
(function () {
    'use strict';

    var cfg = window.YJ19_PWA || {};
    var floatingBtn = document.getElementById('yj19-pwa-install-btn');
    if (!floatingBtn) return;

    var deferredPrompt = null;
    var STORAGE_KEY = cfg.storageKey || 'yj19_pwa_dismissed';
    var placements = cfg.placements || {};
    var clones = [];

    if (sessionStorage.getItem(STORAGE_KEY) === '1') {
        return;
    }

    if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
        return;
    }
    if (window.navigator.standalone === true) {
        return;
    }

    // ----- Build placements -----
    if (!placements.floating || (cfg.mobileOnly && !isMobileViewport())) {
        floatingBtn.parentNode && floatingBtn.parentNode.removeChild(floatingBtn);
    } else {
        clones.push(floatingBtn);
    }

    // Mobile sidebar
    injectInto(placements.mobile_sidebar, 'sidebar', true);
    // Mobile navigation bar
    injectInto(placements.mobile_navbar, 'navbar', true);
    // Desktop header
    injectInto(placements.desktop_header, 'header', false);

    if (clones.length === 0) return;

    function injectInto(placement, variant, mobileScope) {
        if (!placement || !placement.enabled || !placement.selector) return;

        // اگه قراره روی موبایل باشه ولی الان روی دسکتاپ هست، رد شو
        if (mobileScope && !isMobileViewport()) return;
        if (!mobileScope && isMobileViewport()) return;

        var target = findFirstSelector(placement.selector);
        if (!target) return;

        var clone = floatingBtn.cloneNode(true);
        clone.id = 'yj19-pwa-install-btn--' + variant;
        clone.classList.add('yj19-pwa-install-btn--inline');
        clone.classList.add('yj19-pwa-install-btn--' + variant);
        clone.removeAttribute('hidden');
        // فقط شناور fixed داره؛ inlineها باید static باشن
        clone.style.position = 'static';
        clone.style.inset = 'auto';
        clone.style.transform = 'none';

        target.appendChild(clone);
        clones.push(clone);
    }

    function findFirstSelector(selectorList) {
        var parts = selectorList.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        for (var i = 0; i < parts.length; i++) {
            try {
                var el = document.querySelector(parts[i]);
                if (el) return el;
            } catch (e) {}
        }
        return null;
    }

    function isMobileViewport() {
        return window.matchMedia && window.matchMedia('(max-width: 991px)').matches;
    }

    function showButtons() {
        clones.forEach(function (el) {
            el.hidden = false;
            if (el.classList.contains('yj19-pwa-install-btn--inline')) {
                el.classList.add('is-visible');
            } else {
                requestAnimationFrame(function () { el.classList.add('is-visible'); });
            }
        });
    }

    function hideButtons(persist) {
        clones.forEach(function (el) {
            el.classList.remove('is-visible');
            if (!el.classList.contains('yj19-pwa-install-btn--inline')) {
                setTimeout(function () { el.hidden = true; }, 250);
            } else {
                el.style.display = 'none';
            }
        });
        if (persist) {
            try { sessionStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
        }
    }

    // inlineها از همون اول visible باشن
    clones.forEach(function (el) {
        if (el.classList.contains('yj19-pwa-install-btn--inline')) {
            el.hidden = false;
            el.classList.add('is-visible');
        }
    });

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        setTimeout(function () {
            // برای شناور با تأخیر
            clones.forEach(function (el) {
                if (!el.classList.contains('yj19-pwa-install-btn--inline')) {
                    el.hidden = false;
                    requestAnimationFrame(function () { el.classList.add('is-visible'); });
                }
            });
        }, cfg.showDelay || 3000);
    });

    window.addEventListener('appinstalled', function () {
        hideButtons(true);
        deferredPrompt = null;
    });

    function handleClick(e) {
        var t = e.target;
        if (t && t.matches && t.matches('[data-dismiss]')) {
            e.stopPropagation();
            hideButtons(true);
            return;
        }

        if (!deferredPrompt) {
            if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
                alert('برای نصب: روی دکمه «اشتراک‌گذاری» در پایین مرورگر بزن، سپس «Add to Home Screen» رو انتخاب کن.');
            }
            return;
        }

        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function (choice) {
            if (choice.outcome === 'accepted' || choice.outcome === 'dismissed') {
                hideButtons(choice.outcome === 'dismissed');
            }
            deferredPrompt = null;
        });
    }

    clones.forEach(function (el) { el.addEventListener('click', handleClick); });
})();
