@php
    $currentAppVersion = $appVersion ?? \App\Helpers\AppVersion::get();
@endphp

<!-- ========================================================================= -->
<!-- TOAST NOTIFIKASI PEMBARUAN OTOMATIS RENTIFY (HP / BROWSER / PWA) -->
<!-- ========================================================================= -->
<div id="rentify-update-banner" class="hidden fixed top-5 left-1/2 -translate-x-1/2 z-[99999] bg-slate-900/95 text-white text-xs font-bold px-4 py-3 rounded-full shadow-[0_10px_30px_rgba(0,0,0,0.35)] border border-sky-400/80 flex items-center gap-3 backdrop-blur-md transition-all duration-300">
    <div class="w-6 h-6 rounded-full bg-sky-500/20 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-arrows-rotate fa-spin text-sky-400 text-xs"></i>
    </div>
    <span id="rentify-update-text">Memperbarui aplikasi ke versi terbaru...</span>
</div>

<script>
(function() {
    const APP_VERSION = "{{ $currentAppVersion }}";
    const SW_SCRIPT_URL = "/sw.js?v=" + encodeURIComponent(APP_VERSION);
    let isReloading = false;

    // Fungsi menampilkan notifikasi pembaruan dan memuat ulang halaman otomatis
    function triggerAutoUpdateReload(message = 'Memperbarui aplikasi ke versi terbaru...') {
        if (isReloading) return;
        isReloading = true;

        const banner = document.getElementById('rentify-update-banner');
        const text = document.getElementById('rentify-update-text');
        if (banner) {
            if (text && message) text.textContent = message;
            banner.classList.remove('hidden');
        }

        // Simpan versi terkini ke localStorage
        try {
            localStorage.setItem('rentify_app_version', APP_VERSION);
        } catch (e) {}

        setTimeout(() => {
            // Force reload dari server tanpa cache browser
            window.location.reload(true);
        }, 900);
    }

    // 1. REGISTRASI SERVICE WORKER PINTAR (updateViaCache: 'none')
    if ('serviceWorker' in navigator) {
        // Tangkap saat Service Worker baru mengambil kendali (controllerchange)
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            triggerAutoUpdateReload('Aplikasi berhasil diperbarui!');
        });

        // Tangkap pesan siaran SW_UPDATED langsung dari sw.js
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'SW_UPDATED') {
                console.log('[Rentify PWA] Versi baru aktif:', event.data.version);
                triggerAutoUpdateReload('Versi baru telah siap!');
            }
        });

        window.addEventListener('load', () => {
            navigator.serviceWorker.register(SW_SCRIPT_URL, { updateViaCache: 'none' })
                .then(registration => {
                    // Paksa periksa update setiap kali halaman dibuka
                    registration.update();

                    registration.onupdatefound = () => {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.onstatechange = () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // Worker baru siap, langsung aktifkan
                                    newWorker.postMessage({ action: 'skipWaiting' });
                                }
                            };
                        }
                    };
                })
                .catch(err => console.warn('[Rentify PWA] SW Init Warning:', err));
        });
    }

    // 2. PEMERIKSAAN VERSI SERVER SECARA BERKALA & OTOMATIS (REALTIME)
    async function checkServerAppVersion(isManual = false) {
        if (!navigator.onLine && !isManual) return;

        try {
            const response = await fetch('/api/app-version?t=' + Date.now(), { 
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                if (isManual && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal',
                        text: 'Tidak dapat memeriksa versi aplikasi saat ini. Cek jaringan Anda.',
                        confirmButtonColor: '#0284c7'
                    });
                }
                return;
            }

            const data = await response.json();
            const storedVersion = localStorage.getItem('rentify_app_version');

            // Jika belum ada versi tersimpan, inisialisasi dengan versi server
            if (!storedVersion) {
                localStorage.setItem('rentify_app_version', data.version);
                if (isManual && window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Aplikasi Terkini',
                        text: 'Aplikasi Rentify Anda sudah menggunakan versi paling baru (' + data.version + ').',
                        confirmButtonColor: '#0284c7'
                    });
                }
                return;
            }

            // Jika versi server berbeda dari yang tersimpan di HP / browser
            if (data.version && data.version !== storedVersion) {
                console.log('[Rentify PWA] Pembaruan baru terdeteksi:', data.version, '(Sebelumnya:', storedVersion, ')');
                localStorage.setItem('rentify_app_version', data.version);

                // Bersihkan cache storage lokal agar tidak ada aset lama yang tersisa
                if ('caches' in window) {
                    const cacheKeys = await caches.keys();
                    await Promise.all(cacheKeys.map(key => caches.delete(key)));
                }

                // Perbarui service worker
                if ('serviceWorker' in navigator) {
                    const reg = await navigator.serviceWorker.getRegistration();
                    if (reg) await reg.update();
                }

                triggerAutoUpdateReload('Pembaruan baru tersedia! Menerapkan otomatis...');
            } else if (isManual && window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Aplikasi Versi Terbaru',
                    text: 'Aplikasi Anda sudah berada pada pembaruan terbaru (' + data.version + ').',
                    confirmButtonColor: '#0284c7'
                });
            }
        } catch (error) {
            console.warn('[Rentify PWA] Check version failed:', error);
            if (isManual && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memeriksa',
                    text: 'Terjadi kendala saat memeriksa pembaruan server.',
                    confirmButtonColor: '#0284c7'
                });
            }
        }
    }

    // Cek versi otomatis saat user kembali membuka tab atau aplikasi PWA di HP
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            checkServerAppVersion(false);
        }
    });

    // Cek otomatis saat koneksi kembali online
    window.addEventListener('online', () => {
        checkServerAppVersion(false);
    });

    // Pengecekan awal 2 detik setelah halaman dimuat (agar rendering halaman cepat selesai)
    setTimeout(() => {
        checkServerAppVersion(false);
    }, 2000);

    // Cek berkala setiap 60 detik di background
    setInterval(() => {
        checkServerAppVersion(false);
    }, 60000);

    // Buka fungsi manual ke window untuk tombol 'Cek Pembaruan'
    window.rentifyCheckUpdate = function() {
        if (window.Swal) {
            Swal.fire({
                title: 'Memeriksa Pembaruan...',
                text: 'Menghubungkan ke server Rentify...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        checkServerAppVersion(true);
    };

    // Bersihkan cache jika user menekan tombol force refresh
    window.rentifyForceClearCache = async function() {
        if ('caches' in window) {
            const keys = await caches.keys();
            await Promise.all(keys.map(k => caches.delete(k)));
        }
        try {
            localStorage.removeItem('rentify_app_version');
        } catch (e) {}
        window.location.reload(true);
    };
})();
</script>
