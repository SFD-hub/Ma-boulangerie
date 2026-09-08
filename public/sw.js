/**
 * Service Worker — Ma Boulangerie PWA
 *
 * Stratégie :
 *  - Assets statiques (images) → Cache-first
 *  - Pages HTML                → Network-first (évite les problèmes auth / CSRF)
 *  - Requêtes non-GET          → Réseau direct, jamais mis en cache
 */

const CACHE_NAME    = 'ma-boulangerie-v1';
const STATIC_ASSETS = [
    '/images/logo.png',
    '/images/icon-192.png',
    '/images/icon-512.png',
];

// ── Installation : précache des assets statiques ──────────────────────────
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// ── Activation : suppression des anciens caches ───────────────────────────
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(keys) {
            return Promise.all(
                keys
                    .filter(function(key) { return key !== CACHE_NAME; })
                    .map(function(key)    { return caches.delete(key); })
            );
        })
    );
    self.clients.claim();
});

// ── Fetch : stratégie selon le type de ressource ─────────────────────────
self.addEventListener('fetch', function(event) {
    var request = event.request;

    // Ignorer les requêtes non-GET (POST, PUT, DELETE…)
    if (request.method !== 'GET') return;

    // Ignorer les requêtes vers des domaines externes
    if (!request.url.startsWith(self.location.origin)) return;

    var isHtml   = request.headers.get('Accept') &&
                   request.headers.get('Accept').indexOf('text/html') !== -1;
    var isImage  = request.url.match(/\.(png|jpg|jpeg|gif|svg|webp|ico)(\?|$)/i);

    if (isImage) {
        // Cache-first pour les images
        event.respondWith(
            caches.match(request).then(function(cached) {
                if (cached) return cached;
                return fetch(request).then(function(response) {
                    if (response && response.ok) {
                        var clone = response.clone();
                        caches.open(CACHE_NAME).then(function(cache) {
                            cache.put(request, clone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    if (isHtml) {
        // Network-first pour les pages (auth, CSRF)
        event.respondWith(
            fetch(request).catch(function() {
                return caches.match(request);
            })
        );
        return;
    }

    // Autres ressources : réseau direct
    event.respondWith(fetch(request));
});
