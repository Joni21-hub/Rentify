<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Rentify (rentify21.my.id) adalah platform marketplace penyewaan alat, kendaraan, dan barang dengan mudah, cepat, dan aman.">
    <meta name="keywords" content="Rentify, rentify21, sewa alat, platform penyewaan, marketplace sewa barang">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}" />
    <meta name="google-site-verification" content="4y_McbDs1dvq3scZmY9q_XMoaPfzxNrcNR94o3N0nEc" />
    <title>@yield('title', 'RENTIFY') — Marketplace Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <script>
        tailwind.config = {
            theme: { 
                extend: {
                    colors: {
                        navy: { DEFAULT: '#0369a1', mid: '#0284c7' },
                        rentify: { blue: '#0284c7', sky: '#38bdf8', primary: '#0ea5e9' },
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
    <!-- FAVICON & PWA RENTIFY META TAGS -->
    <!-- ================================================================= -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    @stack('head')
</head>
<body class="text-gray-800 antialiased">

    <!-- Area Konten Utama -->
    <main>
        @yield('content')
    </main>

    <!-- ================================================================= -->
    <!-- GLOBAL SWEETALERT2 NOTIFICATION (TEMA RENTIFY MODERN) -->
    <!-- ================================================================= -->
    <script>
        const swalRentify = Swal.mixin({
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-gray-100 p-6',
                confirmButton: 'bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold py-2.5 px-6 rounded-2xl transition-all shadow-md',
                cancelButton: 'bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-6 rounded-xl transition-all ml-2'
            },
            buttonsStyling: false
        });

        @if(session('success') && !str_contains(strtolower(session('success')), 'masuk keranjang'))
            swalRentify.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{!! session("success") !!}',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            swalRentify.fire({
                icon: 'error',
                title: 'Oops... Terjadi Kesalahan!',
                text: '{!! session("error") !!}',
                confirmButtonText: 'Coba Lagi'
            });
        @endif

        @if(session('info') || session('warning'))
            swalRentify.fire({
                icon: '{{ session("info") ? "info" : "warning" }}',
                title: 'Perhatian',
                text: '{!! session("info") ?? session("warning") !!}',
                confirmButtonText: 'Mengerti'
            });
        @endif

        @if(isset($errors) && $errors->any())
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
    <!-- REGISTRASI SERVICE WORKER (TANPA TOMBOL INSTALL) -->
    <!-- ================================================================= -->
    <script>
        // Tetap mendaftarkan PWA di background agar aplikasi berjalan cepat
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        registration.update();
                    })
                    .catch(err => console.error('Rentify PWA: Gagal', err));
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
