/**
 * YJ19 PWA — Service Worker
 *
 * استراتژی کش:
 *  - HTML  → network-first با fallback به کش (crawler-friendly: همیشه نسخه fresh)
 *  - Static (CSS/JS/font/image) → cache-first
 *  - APIها و wp-admin/wp-login → bypass کامل (هیچ‌وقت کش نشن)
 *
 * Placeholderها در زمان serve با مقادیر واقعی جایگزین می‌شن.
 */

const CACHE_NAME = '%%CACHE_NAME%%';
const CACHE_VERSION = '%%CACHE_VERSION%%';
const OFFLINE_URL = '%%OFFLINE_URL%%';

const STATIC_EXT = /\.(css|js|woff2?|ttf|otf|eot|svg|png|jpe?g|gif|webp|avif|ico)(\?.*)?$/i;

const BYPASS_PATTERNS = [
    /\/wp-admin\//,
    /\/wp-login\.php/,
    /\/wp-json\//,
    /\/wp-cron\.php/,
    /preview=true/,
    /yj19_pwa=/,
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.add(new Request(OFFLINE_URL, { cache: 'reload' })))
            .catch(() => {})
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(
            keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
        );
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    if (url.origin !== self.location.origin) return;

    for (const p of BYPASS_PATTERNS) {
        if (p.test(url.pathname) || p.test(url.search)) return;
    }

    const accept = req.headers.get('Accept') || '';
    const isHTML = req.mode === 'navigate' || accept.includes('text/html');

    if (isHTML) {
        event.respondWith(networkFirst(req));
        return;
    }

    if (STATIC_EXT.test(url.pathname)) {
        event.respondWith(cacheFirst(req));
        return;
    }
});

async function networkFirst(req) {
    const cache = await caches.open(CACHE_NAME);
    try {
        const fresh = await fetch(req);
        if (fresh && fresh.ok) {
            cache.put(req, fresh.clone()).catch(() => {});
        }
        return fresh;
    } catch (err) {
        const cached = await cache.match(req);
        if (cached) return cached;
        const offline = await cache.match(OFFLINE_URL);
        if (offline) return offline;
        return new Response('Offline', { status: 503, statusText: 'Offline' });
    }
}

async function cacheFirst(req) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(req);
    if (cached) return cached;
    try {
        const fresh = await fetch(req);
        if (fresh && fresh.ok) {
            cache.put(req, fresh.clone()).catch(() => {});
        }
        return fresh;
    } catch (err) {
        return new Response('Resource unavailable offline', { status: 503 });
    }
}

self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') self.skipWaiting();
});
