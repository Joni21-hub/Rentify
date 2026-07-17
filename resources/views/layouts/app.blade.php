<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'RENTIFY') — Rental Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { 
                extend: {
                    colors: {
                        navy: { DEFAULT: '#0D1B3E', mid: '#1A2F5E' },
                        rentify: { blue: '#1E4DAA', sky: '#5C9EE8' },
                    }
                }
            }
        }
    </script>
    <!-- CDN SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- ================================================================= -->
    <!-- PWA RENTIFY META TAGS -->
    <!-- ================================================================= -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1E4DAA">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784260498/ukuran_satu_g4ihwu.png">
    
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <!-- Area Konten Utama -->
    <main>
        @yield('content')
    </main>

    <!-- ================================================================= -->
    <!-- GLOBAL SWEETALERT2 NOTIFICATION (TEMA RENTIFY MODERN) -->
    <!-- ================================================================= -->
    <script>
        // Pengaturan default tombol agar senada dengan Tailwind Rentify
        const swalRentify = Swal.mixin({
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-gray-100 p-6',
                confirmButton: 'bg-[#1E4DAA] hover:bg-[#0D1B3E] text-white font-semibold py-2.5 px-6 rounded-xl transition-all shadow-md',
                cancelButton: 'bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-6 rounded-xl transition-all ml-2'
            },
            buttonsStyling: false
        });

        // 1. Menangkap Pesan Berhasil (Contoh: Masuk Keranjang, Pesanan Dibuat, Login Berhasil)
        @if(session('success'))
            swalRentify.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{!! session("success") !!}',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        @endif

        // 2. Menangkap Pesan Eror (Contoh: Gagal Login, Kata Sandi Salah, Akses Ditolak)
        @if(session('error'))
            swalRentify.fire({
                icon: 'error',
                title: 'Oops... Terjadi Kesalahan!',
                text: '{!! session("error") !!}',
                confirmButtonText: 'Coba Lagi'
            });
        @endif

        // 3. Menangkap Pesan Info/Peringatan (Contoh: Stok Barang Menipis, Sesi Habis)
        @if(session('info') || session('warning'))
            swalRentify.fire({
                icon: '{{ session("info") ? "info" : "warning" }}',
                title: 'Perhatian',
                text: '{!! session("info") ?? session("warning") !!}',
                confirmButtonText: 'Mengerti'
            });
        @endif

        // 4. Menangkap Eror Validasi Form (Contoh: Lupa Isi Email, Format Salah saat Daftar)
        @if($errors->any())
            swalRentify.fire({
                icon: 'warning',
                title: 'Periksa Inputanmu',
                html: `
                    <div class="text-left text-sm text-gray-600 mt-2 bg-red-50 p-3 rounded-xl border border-red-100">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                `,
                confirmButtonText: 'Perbaiki'
            });
        @endif
    </script>

    <!-- ================================================================= -->
    <!-- TOMBOL INSTALL PWA & REGISTRASI SERVICE WORKER -->
    <!-- ================================================================= -->
    <div id="installPwaContainer" style="display: none;" class="fixed bottom-6 right-6 z-50">
        <button id="installPwaBtn" class="bg-[#1E4DAA] hover:bg-[#0D1B3E] text-white font-bold py-3 px-6 rounded-2xl shadow-2xl border-2 border-white flex items-center gap-3 transition-all transform hover:scale-105">
            <i class="fa-solid fa-download"></i>
            <span>Install Aplikasi Rentify</span>
        </button>
    </div>

    <script>
        // 1. Mendaftarkan Service Worker ke Browser
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Rentify PWA: Service Worker Berhasil Didaftarkan'))
                    .catch(err => console.error('Rentify PWA: Gagal Daftar Service Worker', err));
            });
        }

        // 2. Logika Menampilkan Tombol "Install Aplikasi"
        let deferredPrompt;
        const installContainer = document.getElementById('installPwaContainer');
        const installBtn = document.getElementById('installPwaBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            // Mencegah Chrome menampilkan prompt mini otomatis
            e.preventDefault();
            deferredPrompt = e;
            // Tampilkan tombol install melayang di pojok kanan bawah
            installContainer.style.display = 'block';
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    console.log('User menyetujui instalasi aplikasi');
                }
                deferredPrompt = null;
                installContainer.style.display = 'none';
            }
        });

        // Menyembunyikan tombol jika aplikasi sudah berhasil diinstal
        window.addEventListener('appinstalled', () => {
            installContainer.style.display = 'none';
            deferredPrompt = null;
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Diinstal!',
                text: 'Aplikasi Rentify sekarang ada di layar utama HP Anda.',
                timer: 3000,
                showConfirmButton: false
            });
        });
    </script>

    @stack('scripts')
</body>
</html>