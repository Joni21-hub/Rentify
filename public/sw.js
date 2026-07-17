const CACHE_NAME = 'rentify-pwa-v2';

// Kita hanya menyimpan file inti milik domain kita sendiri agar tidak terkena blokir CORS
const urlsToCache = [
    '/',
    '/manifest.json'
];

// 1. Install Service Worker & Simpan Cache Inti
self.addEventListener('install', event => {
    // Memaksa Service Worker langsung aktif saat itu juga tanpa menunggu
    self.skipWaiting(); 
    
    event.waitUntil(
        caches.open(CACHE_NAME)
        .then(cache => {
            console.log('Rentify PWA: Berhasil menyimpan cache inti');
            return cache.addAll(urlsToCache);
        })
        .catch(err => {
            console.error('Rentify PWA: Ada masalah saat menyimpan cache:', err);
        })
    );
});

// 2. Mengambil Data (Utamakan dari Internet, jika Offline baru ambil dari Cache)
self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request)
        .catch(() => {
            // Jika HP sedang offline / tidak ada sinyal, ambil tampilan dari cache
            return caches.match(event.request);
        })
    );
});

// 3. Membersihkan Cache Versi Lama jika ada Update Aplikasi
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('Rentify PWA: Menghapus cache versi lama:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});