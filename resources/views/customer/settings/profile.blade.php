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

                <!-- FOTO PROFIL DENGAN PREVIEW INSTAN & PILIHAN KAMERA/GALERI -->
                <div class="flex flex-col items-center">
                    <div class="relative group cursor-pointer mb-2" onclick="openPhotoModal()">
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

                        <!-- Badge Icon Edit di Pojok Kanan Bawah Foto -->
                        <div class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-sky-500 text-white flex items-center justify-center shadow-md border-2 border-white">
                            <i class="fa-solid fa-camera text-xs"></i>
                        </div>
                    </div>

                    <!-- Hidden Inputs: Kamera, Galeri, Form Input -->
                    <input type="file" id="inputKamera" accept="image/*" capture="user" class="hidden" onchange="handleFilePicked(this)">
                    <input type="file" id="inputGaleri" accept="image/*" class="hidden" onchange="handleFilePicked(this)">
                    <input type="file" id="fotoProfilInput" name="foto_profil" class="hidden">

                    <!-- Label Panduan -->
                    <span class="text-xs text-slate-500 font-medium cursor-pointer" onclick="openPhotoModal()">Ketuk foto untuk ganti foto profil</span>

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

    <!-- MODAL PILIH SUMBER FOTO (KAMERA / GALERI) -->
    <div id="photoSourceModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-200">
        <!-- Backdrop click to close -->
        <div class="absolute inset-0" onclick="closePhotoModal()"></div>

        <!-- Modal Card -->
        <div class="relative w-full max-w-sm bg-white rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl z-10 transform translate-y-full sm:translate-y-0 sm:scale-95 transition-all duration-200" id="photoModalCard">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-800 text-sm">Pilih Foto Profil</h3>
                </div>
                <button type="button" onclick="closePhotoModal()" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-xs transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Opsi Pilihan -->
            <div class="py-4 space-y-2.5">
                <!-- Opsi 1: Kamera -->
                <button type="button" onclick="chooseSource('kamera')" 
                        class="w-full flex items-center gap-3.5 p-3.5 rounded-2xl bg-slate-50 hover:bg-sky-50/80 border border-slate-200/80 hover:border-sky-300 transition-all text-left group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 text-white flex items-center justify-center text-lg shadow-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <div class="flex-1">
                        <span class="block font-black text-slate-800 text-xs">Ambil Foto (Kamera)</span>
                        <span class="block text-[11px] text-slate-500 mt-0.5">Buka kamera dan ambil foto langsung</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-xs group-hover:text-sky-600 transition"></i>
                </button>

                <!-- Opsi 2: Galeri HP -->
                <button type="button" onclick="chooseSource('galeri')" 
                        class="w-full flex items-center gap-3.5 p-3.5 rounded-2xl bg-slate-50 hover:bg-sky-50/80 border border-slate-200/80 hover:border-sky-300 transition-all text-left group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-lg shadow-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-images"></i>
                    </div>
                    <div class="flex-1">
                        <span class="block font-black text-slate-800 text-xs">Pilih dari Galeri</span>
                        <span class="block text-[11px] text-slate-500 mt-0.5">Pilih foto tersimpan dari galeri perangkat</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-xs group-hover:text-sky-600 transition"></i>
                </button>
            </div>

            <!-- Tombol Batal -->
            <button type="button" onclick="closePhotoModal()" class="w-full py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                Batal
            </button>
        </div>
    </div>

    <!-- JAVASCRIPT REAL-TIME PREVIEW & CAMERA/GALLERY HANDLING -->
    <script>
        function openPhotoModal() {
            const modal = document.getElementById('photoSourceModal');
            const card = document.getElementById('photoModalCard');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            card.classList.remove('translate-y-full', 'sm:scale-95');
            card.classList.add('translate-y-0', 'sm:scale-100');
        }

        function closePhotoModal() {
            const modal = document.getElementById('photoSourceModal');
            const card = document.getElementById('photoModalCard');
            card.classList.remove('translate-y-0', 'sm:scale-100');
            card.classList.add('translate-y-full', 'sm:scale-95');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
        }

        function chooseSource(source) {
            closePhotoModal();
            setTimeout(() => {
                if (source === 'kamera') {
                    document.getElementById('inputKamera').click();
                } else {
                    document.getElementById('inputGaleri').click();
                }
            }, 150);
        }

        function handleFilePicked(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Sinkronkan ke input form utama
                const targetInput = document.getElementById('fotoProfilInput');
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    targetInput.files = dt.files;
                } catch (e) {
                    // Fallback browser jadul
                    document.getElementById('inputKamera').removeAttribute('name');
                    document.getElementById('inputGaleri').removeAttribute('name');
                    input.setAttribute('name', 'foto_profil');
                }

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