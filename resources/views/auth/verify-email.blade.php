<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Rentify</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <h2 class="text-2xl font-bold mb-4">Verifikasi Email Anda</h2>
        
        <p class="text-gray-600 mb-6">
            Terima kasih telah mendaftar! Sebelum mulai menggunakan Rentify, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke email Anda.
        </p>

        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 py-2 rounded">
                {{ session('success') }}
            </div>
        @endif

        <p class="text-gray-500 mb-4 text-sm">Tidak menerima email?</p>
        
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="text-sm text-red-500 hover:text-red-700 underline">
                Logout
            </button>
        </form>
    </div>
</body>
</html>
