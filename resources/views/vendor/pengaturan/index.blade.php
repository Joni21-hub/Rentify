@extends('layouts.vendor')

@section('title', 'Pengaturan Toko — Vendor Rentify')

@section('content')
<div class="px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-4xl mx-auto space-y-4 sm:space-y-6">

    <!-- Header Bersih & Profesional -->
    <div class="flex items-center justify-between bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight">Pengaturan Toko</h1>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">Kelola identitas etalase, nomor kontak, dan keamanan akun vendor</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-black rounded-xl">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Terdaftar
        </span>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2.5 text-xs font-bold shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    
    @if ($errors->any())
        <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-medium space-y-1 shadow-xs">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('vendor.pengaturan.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">
            
            <!-- KOLOM KIRI: Foto & Identitas Toko -->
            <div class="lg:col-span-1 bg-white p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs text-center space-y-3">
                <div class="relative w-24 h-24 mx-auto cursor-pointer group" onclick="document.getElementById('foto_profil_input').click()">
                    <div class="w-full h-full rounded-2xl bg-slate-100 border-2 border-slate-200 overflow-hidden flex items-center justify-center">
                        <img id="preview_image" src="{{ $user->foto_profil ? asset($user->foto_profil) : '' }}" class="{{ $user->foto_profil ? '' : 'hidden' }} w-full h-full object-cover">
                        <i id="default_icon" class="fa-solid fa-store text-2xl text-slate-400 {{ $user->foto_profil ? 'hidden' : '' }}"></i>
                        <div class="absolute inset-0 bg-slate-900/40 rounded-2xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            <i class="fa-solid fa-camera text-white text-base"></i>
                        </div>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-lg bg-sky-500 text-white flex items-center justify-center text-xs shadow-sm">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>

                <input type="file" name="foto_profil" id="foto_profil_input" class="hidden" accept="image/*" onchange="previewImage(event)">

                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-800">{{ $user->vendor_name ?? 'Nama Toko' }}</h2>
                    <p class="text-xs text-slate-400 font-medium">Pemilik: {{ $user->name }}</p>
                </div>
            </div>

            <!-- KOLOM KANAN: Form Pengaturan -->
            <div class="lg:col-span-2 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                
                <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2.5">
                    Informasi Profil & Kontak
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Toko (Etalase)</label>
                        <input type="text" name="vendor_name" value="{{ old('vendor_name', $user->vendor_name) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Pemilik Akun</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email Login</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">WhatsApp Toko</label>
                        <input type="text" name="whatsapp_vendor" value="{{ old('whatsapp_vendor', $user->whatsapp_vendor) }}" placeholder="08123456789" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>
                </div>

                <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2.5 pt-3">
                    Keamanan Akun
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Sandi Baru (Opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Konfirmasi Sandi Baru</label>
                        <input type="password" name="password_confirmation" placeholder="Ulangi sandi baru" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-800 focus:outline-none focus:border-sky-500 shadow-xs">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 text-right">
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 text-white font-black text-xs rounded-xl shadow-xs transition active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('preview_image');
            const icon = document.getElementById('default_icon');
            preview.src = reader.result;
            preview.classList.remove('hidden');
            if (icon) icon.classList.add('hidden');
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection