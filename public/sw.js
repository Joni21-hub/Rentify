// =========================================================================
// RENTIFY SMART SERVICE WORKER & PWA AUTO-UPDATE SYSTEM
// =========================================================================

// Ambil versi dinamis dari URL query parameter jika ada (misal /sw.js?v=1.14.0.2026...), atau fallback
const swParams = new URL(self.location.href).searchParams;
const swVersion = swParams.get('v') || '1.14.0-auto';
const CACHE_NAME = 'rentify-pwa-' + swVersion;

// File statis inti yang di-cache saat install
const urlsToCache = [
    '/manifest.json'
];

// 1. Install & Langsung Aktifkan Tanpa Menunggu (Skip Waiting)
self.addEventListener('install', event => {
    self.skipWaiting(); 
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(urlsToCache);
        }).catch(err => {
            console.warn('[Rentify SW] Cache init warning:', err);
        })
    );
});

// 2. Bersihkan Cache Lama Saat Versi Baru Aktif & Ambil Alih Klien Seketika
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('[Rentify SW] Menghapus cache lama:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim()).then(() => {
            // Beritahu seluruh tab & window aplikasi yang terbuka bahwa versi baru telah aktif
            return self.clients.matchAll({ type: 'window' }).then(clients => {
                clients.forEach(client => {
                    client.postMessage({ type: 'SW_UPDATED', version: swVersion });
                });
            });
        })
    );
});

// 3. Listener pesan langsung dari aplikasi
self.addEventListener('message', event => {
    if (!event.data) return;

    if (event.data.action === 'skipWaiting') {
        self.skipWaiting();
    } else if (event.data.action === 'clearCache') {
        caches.keys().then(keys => Promise.all(keys.map(k => caches.delete(k)))).then(() => {
            if (event.ports && event.ports[0]) {
                event.ports[0].postMessage({ success: true });
            }
        });
    }
});

// 4. STRATEGI FETCH & CACHING PINTAR
self.addEventListener('fetch', event => {
    // 1. Abaikan request ke domain eksternal (Cloudinary, Tailwind CDN, Google Fonts, FontAwesome, jsDelivr)
    // agar native browser cache bekerja secara paralel tanpa mengunci Service Worker
    if (!event.request.url.startsWith(self.location.origin)) return;

    // 2. Dilarang meng-cache POST, AJAX, API, admin, dan rute dinamis transaksi
    if (
        event.request.method !== 'GET' || 
        event.request.url.includes('/api/') || 
        event.request.url.includes('/admin/') ||
        event.request.url.includes('/webhook/')
    ) {
        return;
    }

    // 3. Untuk Navigasi HTML: Gunakan Network-First dengan header no-cache agar selalu dapat view Blade terbaru
    // Fallback ke cache hanya jika perangkat sedang offline tanpa internet
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request, { cache: 'no-cache' })
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

    // 4. Untuk Aset Statis Lokal (CSS, JS, gambar logo): Stale-While-Revalidate yang aman dalam CACHE_NAME
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
