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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <!-- Area Konten Utama (Bersih tanpa gangguan navbar/footer lama) -->
    <main>
        @yield('content')
    </main>

    @if(session('success'))
    <script>
        Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil',
            text: '{{ session("success") }}', 
            timer: 2500, 
            showConfirmButton: false 
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>