<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ubah Profil - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
        .bg-flowing {
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 pb-16 bg-flowing overflow-x-hidden w-full max-w-full">

    <div class="max-w-md mx-auto min-h-screen relative pb-8">

        <!-- HEADER (TANPA KATA SAYA) -->
        <header class="rentify-navbar sticky top-0 z-50 px-5 py-3.5 flex items-center justify-between shadow-sm">
            <a href="{{ route('customer.settings') }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/70 hover:bg-white text-slate-600 hover:text-sky-600 transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-base font-black text-slate-800 flex-1 text-center tracking-tight pr-9">Ubah Profil</h1>
        </header>

        <div class="px-4 py-6">

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-100 border border-rose-300 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-2xl bg-sky-100 border border-sky-300 text-sky-800 text-xs font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-circle-check text-sky-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-100 border border-rose-300 text-rose-800 text-xs font-bold shadow-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="profileForm" action="{{ route('customer.settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="rentify-card p-6 rounded-3xl shadow-sm space-y-6" onsubmit="handleFormSubmit()">
                @csrf

                <!-- FOTO PROFIL DENGAN PREVIEW INSTAN -->
                <div class="flex flex-col items-center">
                    <div class="relative group cursor-pointer mb-2">
                        <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-white shadow-lg bg-sky-100 flex items-center justify-center text-sky-600 text-3xl font-black relative">
                            <!-- Image Element (Tampil jika sudah ada foto atau dipilih foto baru) -->
                            <img id="avatarPreviewImage" 
                                 src="{{ $user->foto_profil ? (str_starts_with($user->foto_profil, 'http') ? $user->foto_profil : Storage::url($user->foto_profil)) : '' }}" 
                                 alt="Foto" 
                                 class="w-full h-full object-cover {{ $user->foto_profil ? '' : 'hidden' }}">
                            
                            <!-- Placeholder Inisial (Tampil jika belum ada foto profil) -->
                            <span id="avatarPlaceholder" class="{{ $user->foto_profil ? 'hidden' : '' }}">
                                {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                            </span>

                            <!-- Overlay Kamera Hover / Tap -->
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-full">
                                <i class="fa-solid fa-camera text-white text-2xl"></i>
                            </div>
                        </div>

                        <!-- Input File Tersembunyi (Langsung Bereaksi Saat Dipilih) -->
                        <input type="file" id="fotoProfilInput" name="foto_profil" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" onchange="previewAvatar(event)">
                    </div>

                    <!-- Label Panduan -->
                    <span class="text-xs text-slate-500 font-medium">Ketuk foto untuk memilih gambar baru</span>

                    <!-- Badge Indikator Pratinjau (Muncul Seketika Saat File Dipilih) -->
                    <div id="previewBadge" class="hidden mt-2 inline-flex items-center gap-1.5 px-3 py-1 bg-sky-100 text-sky-800 text-[11px] font-bold rounded-full border border-sky-300 shadow-sm animate-pulse">
                        <i class="fa-solid fa-sparkles text-sky-600"></i>
                        <span>Pratinjau Foto Baru &bull; Klik Simpan jika cocok</span>
                    </div>
                </div>

                <!-- NAMA -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 ml-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="rentify-input input-glass w-full px-4 py-3.5 rounded-2xl font-bold text-sm focus:outline-none transition">
                </div>

                <!-- SIMPAN PERUBAHAN -->
                <div class="pt-2">
                    <button id="btnSubmit" type="submit" class="rentify-btn w-full py-3.5 rounded-2xl text-xs font-black tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-sm"></i>
                        <span id="btnSubmitText">Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT REAL-TIME PREVIEW & LOADING FEEDBACK -->
    <script>
        function previewAvatar(event) {
            const file = event.target.files[0];
            if (file) {
                // Buat Object URL instan tanpa delay
                const previewUrl = URL.createObjectURL(file);
                const img = document.getElementById('avatarPreviewImage');
                const placeholder = document.getElementById('avatarPlaceholder');
                const badge = document.getElementById('previewBadge');
                const btnText = document.getElementById('btnSubmitText');

                if (img) {
                    img.src = previewUrl;
                    img.classList.remove('hidden');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
                if (badge) {
                    badge.classList.remove('hidden');
                }
                if (btnText) {
                    btnText.innerText = 'Simpan Perubahan';
                }
            }
        }

        function handleFormSubmit() {
            const btn = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnSubmitText');
            if (btn && btnText) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                btnText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-sm mr-1"></i> Menyimpan Foto...';
            }
        }
    </script>
</body>
</html>