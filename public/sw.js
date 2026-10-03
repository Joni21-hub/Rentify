const CACHE_NAME = 'rentify-pwa-v5-performance';

// File statis ringan yang di-cache saat install
const urlsToCache = [
    '/manifest.json'
];

// 1. Install & Langsung Aktifkan Tanpa Menunggu
self.addEventListener('install', event => {
    self.skipWaiting(); 
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(urlsToCache);
        })
    );
});

// 2. Bersihkan Cache Lama Saat Versi Naik
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. LOGIKA CACHING RINGAN (HANYA UNTUK ASET INTERNAL DOMAIN SENDIRI)
self.addEventListener('fetch', event => {
    // 1. Abaikan request ke domain luar (Cloudinary, CDN Tailwind, FontAwesome, Google Fonts)
    // agar browser memakai native browser cache secara paralel tanpa membebani Service Worker
    if (!event.request.url.startsWith(self.location.origin)) return;

    // 2. Dilarang meng-cache POST, AJAX, API, dan rute dinamis transaksi
    if (event.request.method !== 'GET' || event.request.url.includes('/api/') || event.request.url.includes('/admin/')) {
        return;
    }

    // 3. Untuk Navigasi HTML: Gunakan Network-First cepat dengan fallback cache
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                    }
                    return networkResponse;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // 4. Untuk Aset Statis Lokal (CSS, JS, logo lokal): Stale-While-Revalidate
    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            const fetchPromise = fetch(event.request).then(networkResponse => {
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                }
                return networkResponse;
            }).catch(() => cachedResponse);

            return cachedResponse || fetchPromise;
        })
    );
});