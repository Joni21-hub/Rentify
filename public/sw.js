const CACHE_NAME = 'rentify-pwa-v1';
const urlsToCache = [
    '/',
    '/manifest.json',
    'https://res.cloudinary.com/fnf8f1pm/image/upload/v1784260498/ukuran_satu_g4ihwu.png',
    'https://res.cloudinary.com/fnf8f1pm/image/upload/v1784260422/ukuran_lima_xub40q.png'
];

// 1. Install Service Worker & Simpan Cache
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
        .then(cache => {
            console.log('Service Worker: Menyinpan cache');
            return cache.addAll(urlsToCache);
        })
    );
});

// 2. Intercept Request dari Browser
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
        .then(response => {
            // Jika ada di cache, gunakan itu. Jika tidak, ambil dari internet.
            return response || fetch(event.request);
        })
    );
});

// 3. Membersihkan Cache Lama jika ada Update
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('Service Worker: Menghapus cache lama');
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});