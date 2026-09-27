const CACHE_NAME = 'rentify-pwa-v4-auto-update';

// File statis utama yang di-cache saat install
const urlsToCache = [
    '/',
    '/manifest.json'
];

// 1. Install & Langsung Aktifkan Tanpa Menunggu
self.addEventListener('install', event => {
    self.skipWaiting(); 
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('Rentify PWA: Memasang smart cache...');
            return cache.addAll(urlsToCache);
        })
    );
});

// 2. Bersihkan Cache Lama Saat Versi Naik (v3 ke v4)
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('Rentify PWA: Menghapus cache usang ' + cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. LOGIKA CACHING CERDAS UNTUK UPDATE OTOMATIS
self.addEventListener('fetch', event => {
    // Abaikan request ke domain lain atau request non-HTTP
    if (!event.request.url.startsWith('http')) return;

    // A. NETWORK-ONLY: Dilarang keras meng-cache API dan POST (Form Submit, AJAX)
    if (event.request.method !== 'GET' || event.request.url.includes('/api/')) {
        return; // Biarkan browser yang tangani langsung ke server
    }

    // B. NETWORK-FIRST: Untuk Halaman HTML (Navigasi)
    // Selalu coba ambil halaman terbaru dari Vercel. Jika offline/gagal, baru ambil dari cache.
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(networkResponse => {
                    // Simpan halaman HTML terbaru ke cache secara diam-diam
                    return caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                })
                .catch(() => {
                    // Jika offline, ambil dari memori HP
                    return caches.match(event.request);
                })
        );
        return;
    }

    // C. STALE-WHILE-REVALIDATE: Untuk Aset (CSS, JS, Gambar)
    // Langsung tampilkan dari cache agar kilat, TAPI diam-diam download versi baru dari Vercel untuk update memori HP.
    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            const fetchPromise = fetch(event.request).then(networkResponse => {
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    // Update memori HP dengan versi terbaru dari internet
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, networkResponse.clone());
                    });
                }
                return networkResponse;
            }).catch(err => console.log('Rentify PWA: Gagal fetch aset ' + event.request.url));

            // Jika ada di cache, langsung tampilkan (ngebut!), sementara fetchPromise berjalan di background.
            // Jika belum ada di cache, tunggu download selesai.
            return cachedResponse || fetchPromise;
        })
    );
});