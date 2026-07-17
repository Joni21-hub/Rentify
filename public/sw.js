const CACHE_NAME = 'rentify-pwa-v3-speed';

// Hanya cache halaman utama dan konfigurasi identitas
const urlsToCache = [
    '/',
    '/manifest.json'
];

// 1. Install & Langsung Aktifkan Tanpa Menunggu
self.addEventListener('install', event => {
    self.skipWaiting(); 
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('Rentify PWA: Memasang turbo cache...');
            return cache.addAll(urlsToCache);
        })
    );
});

// 2. Bersihkan Cache Lama Saat Ada Update
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

// 3. LOGIKA NGEBUT: Utamakan Memori HP untuk Gambar, CSS, dan Ikon
self.addEventListener('fetch', event => {
    // Abaikan request yang bukan HTTP/HTTPS
    if (!event.request.url.startsWith('http')) return;

    // A. Jika user klik link/pindah halaman: Ambil dari internet dulu agar data selalu baru, jika offline ambil dari cache
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(event.request))
        );
        return;
    }

    // B. Jika memuat Gambar, Font, atau Script: LANGSUNG AMBIL DARI MEMORI HP (sangat cepat!)
    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            if (cachedResponse) {
                // Tampilkan langsung dari cache HP tanpa delay loading
                return cachedResponse;
            }
            // Jika belum ada di memori HP, baru download dari internet dan simpan ke cache
            return fetch(event.request).then(networkResponse => {
                // Simpan salinan ke cache untuk dibuka di kemudian hari
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            });
        })
    );
});