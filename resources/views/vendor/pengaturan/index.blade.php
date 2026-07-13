@extends('layouts.vendor')

@section('title', 'Pengaturan Toko - Vendor Rentify')

@section('content')
<div class="p-4 md:p-8">
    <header class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Pengaturan Toko <span class="text-2xl">⚙️</span></h1>
        <p class="text-slate-500 mt-2 font-medium">Kelola informasi profil, kontak WhatsApp, dan keamanan akun Anda.</p>
    </header>

    @if(session('success'))
        <div class="mb-8 px-5 py-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-check"></i></div>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif
    
    @if ($errors->any())
        <div class="mb-8 px-5 py-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl shadow-sm">
            <ul class="list-disc list-inside text-sm font-semibold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- PERHATIKAN: Tag form sekarang membungkus SELURUH halaman (kiri & kanan) -->
    <form action="{{ route('vendor.pengaturan.update') }}" method="POST" enctype="multipart/form-data">
        @csrf <!-- Ini adalah kunci pencegah Error 419! -->
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KOLOM KIRI: Profil & Upload Foto Ala Shopee -->
            <div class="lg:col-span-1 space-y-6">
                <div class="glass-card rounded-3xl p-8 shadow-sm border border-slate-100 bg-gradient-to-br from-brand-deep to-brand-main text-white text-center relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                    
                    <!-- UI UPLOAD FOTO ALA SHOPEE -->
                    <div class="relative w-28 h-28 mx-auto mb-4 group cursor-pointer" onclick="document.getElementById('foto_profil_input').click()">
                        
                        <!-- Lingkaran Foto -->
                        <div class="w-full h-full bg-white/20 border-2 border-white/50 backdrop-blur-sm rounded-full flex items-center justify-center text-4xl shadow-lg overflow-hidden relative">
                            
                            <!-- Gambar Preview (Akan muncul foto asli / foto yang baru dipilih) -->
                            <img id="preview_image" src="{{ $user->foto_profil ? asset($user->foto_profil) : '' }}" class="{{ $user->foto_profil ? '' : 'hidden' }} w-full h-full object-cover">
                            
                            <!-- Icon Default (Muncul jika belum punya foto sama sekali) -->
                            <i id="default_icon" class="fa-solid fa-store text-white {{ $user->foto_profil ? 'hidden' : '' }}"></i>

                            <!-- Overlay Kamera Hitam (Muncul saat mouse diarahkan ke foto) -->
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fa-solid fa-camera text-white text-xl"></i>
                            </div>
                        </div>
                        
                        <!-- Ikon + Kecil Ala Shopee di Pojok Kanan Bawah -->
                        <div class="absolute bottom-0 right-0 w-8 h-8 bg-brand-sky border-2 border-brand-deep rounded-full flex items-center justify-center text-white font-bold shadow-md hover:scale-110 transition-transform">
                            <i class="fa-solid fa-plus text-sm"></i>
                        </div>
                    </div>

                    <!-- INPUT FILE ASLINYA DISEMBUNYIKAN DI SINI -->
                    <input type="file" name="foto_profil" id="foto_profil_input" class="hidden" accept="image/*" onchange="previewImage(event)">

                    <h2 class="text-2xl font-black tracking-tight mt-2">{{ $user->vendor_name ?? 'Nama Toko' }}</h2>
                    <p class="text-brand-sky font-bold text-sm mt-1">Pemilik: {{ $user->name }}</p>
                    
                    <div class="mt-6 pt-6 border-t border-white/20 flex justify-center gap-4 text-sm font-medium">
                        <span class="bg-white/20 px-3 py-1.5 rounded-lg"><i class="fa-solid fa-shield-halved mr-1 text-emerald-300"></i> Terverifikasi</span>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Form Pengaturan Data -->
            <div class="lg:col-span-2">
                <div class="glass-card rounded-3xl p-6 md:p-10 shadow-sm border border-slate-100 bg-white">
                    <h3 class="text-lg font-extrabold text-slate-800 mb-4 border-b border-slate-100 pb-2"><i class="fa-solid fa-address-card text-brand-main mr-2"></i> Informasi Dasar</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-2">Nama Toko (Etalase)</label>
                            <input type="text" name="vendor_name" value="{{ old('vendor_name', $user->vendor_name) }}" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand-main/20 outline-none shadow-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-2">Nama Pemilik Akun</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand-main/20 outline-none shadow-sm transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-2">Alamat Email Login</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand-main/20 outline-none shadow-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-2">Nomor WhatsApp Toko</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fa-brands fa-whatsapp text-emerald-500 text-lg"></i>
                                </div>
                                <input type="text" name="whatsapp_vendor" value="{{ old('whatsapp_vendor', $user->whatsapp_vendor) }}" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand-main/20 outline-none shadow-sm transition-all" placeholder="Contoh: 08123456789">
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-extrabold text-slate-800 mt-10 mb-4 border-b border-slate-100 pb-2"><i class="fa-solid fa-lock text-rose-500 mr-2"></i> Keamanan & Kata Sandi</h3>
                    <p class="text-xs text-slate-500 mb-4 font-medium">* Kosongkan kolom di bawah ini jika Anda tidak ingin mengubah kata sandi Anda.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-2">Kata Sandi Baru</label>
                            <input type="password" name="password" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand-main/20 outline-none shadow-sm transition-all" placeholder="Minimal 6 karakter">
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-2">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand-main/20 outline-none shadow-sm transition-all" placeholder="Ulangi sandi baru">
                        </div>
                    </div>

                    <div class="pt-6 mt-8 border-t border-slate-100 text-right">
                        <button type="submit" class="inline-flex items-center gap-2 px-8 py-4 bg-brand-main hover:bg-brand-deep text-white font-bold rounded-2xl transition-all shadow-lg shadow-brand-main/30 group">
                            Simpan Pengaturan <i class="fa-solid fa-floppy-disk group-hover:scale-125 transition-transform"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<!-- Script Javascript untuk menampilkan preview foto secara langsung tanpa loading page -->
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const preview = document.getElementById('preview_image');
            const icon = document.getElementById('default_icon');
            
            preview.src = reader.result;
            preview.classList.remove('hidden');
            
            if(icon) {
                icon.classList.add('hidden');
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection