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
        const swalRentify = Swal.mixin({
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-gray-100 p-6',
                confirmButton: 'bg-[#1E4DAA] hover:bg-[#0D1B3E] text-white font-semibold py-2.5 px-6 rounded-xl transition-all shadow-md',
                cancelButton: 'bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-6 rounded-xl transition-all ml-2'
            },
            buttonsStyling: false
        });

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
    <!-- REGISTRASI SERVICE WORKER (TANPA TOMBOL INSTALL) -->
    <!-- ================================================================= -->
    <script>
        // Tetap mendaftarkan PWA di background agar aplikasi berjalan cepat
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .catch(err => console.error('Rentify PWA: Gagal', err));
            });
        }
    </script>

    @stack('scripts')
</body>
</html>