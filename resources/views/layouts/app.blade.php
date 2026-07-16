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

    @stack('scripts')
</body>
</html>