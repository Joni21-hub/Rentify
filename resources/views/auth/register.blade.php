<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Daftar Akun Customer — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#38bdf8">

    <link rel="stylesheet" href="{{ asset('rentify-theme.css') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Latar Belakang Gradasi Melingkar */
        .bg-flowing {
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed !important;
        }

        .glass-panel {
            background: linear-gradient(to right, #FFFDF5 0%, #CBE0F5 100%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            border-radius: 2rem;
        }

        .input-glass {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: #0f172a;
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.7);
            border-color: #ffffff;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }
        .input-glass::placeholder { color: rgba(15, 23, 42, 0.5); font-weight: 500; }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 py-8 relative overflow-x-hidden bg-flowing text-slate-800">

    <div class="w-full max-w-[390px] relative z-10 my-auto">
        
        <div class="glass-panel rentify-card p-6 sm:p-8 relative">
            
            <!-- HEADER DAFTAR AKUN (STANDAR MARKETPLACE) -->
            <div class="text-center mb-5">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">Daftar Akun</h1>
                <p class="text-xs text-slate-500 font-medium mt-1">Lengkapi data diri Anda untuk memulai</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-md">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" id="registerForm" class="space-y-3.5">
                @csrf 

                <!-- DAFTAR DENGAN GOOGLE -->
                <button type="button" onclick="handleGoogleLogin()" class="w-full flex items-center justify-center gap-3 py-3 bg-white/60 hover:bg-white/80 border border-slate-300 rounded-2xl transition-all font-extrabold text-[#475569] text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-5 h-5 bg-white rounded-full p-0.5">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                    </svg>
                    <span>Daftar dengan Google</span>
                </button>

                <div class="flex items-center gap-3 my-3">
                    <div class="h-px bg-slate-300 flex-1"></div>
                    <span class="text-[10px] font-bold text-[#475569] tracking-widest">ATAU</span>
                    <div class="h-px bg-slate-300 flex-1"></div>
                </div>

                <!-- NAMA -->
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500 group-focus-within:text-sky-500 transition">
                        <i class="fa-solid fa-user text-xs"></i>
                    </span>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           class="input-glass rentify-input w-full pl-11 pr-4 py-3 rounded-2xl text-sm focus:outline-none font-bold" 
                           placeholder="Nama Lengkap">
                </div>

                <!-- NO WHATSAPP -->
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500 group-focus-within:text-emerald-500 transition">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                    </span>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" required 
                           class="input-glass rentify-input w-full pl-11 pr-4 py-3 rounded-2xl text-sm focus:outline-none font-bold" 
                           placeholder="No. WhatsApp Aktif">
                </div>

                <!-- KATA SANDI -->
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500 group-focus-within:text-sky-500 transition">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </span>
                    <input type="password" id="passInput" name="password" required 
                           class="input-glass rentify-input w-full pl-11 pr-11 py-3 rounded-2xl text-sm focus:outline-none font-bold" 
                           placeholder="Kata Sandi (Min. 8 karakter)">
                    <button type="button" onclick="togglePass('passInput', 'eye1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-sky-500 transition">
                        <i id="eye1" class="fa-regular fa-eye-slash text-xs"></i>
                    </button>
                </div>

                <!-- KONFIRMASI SANDI -->
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500 group-focus-within:text-sky-500 transition">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                    </span>
                    <input type="password" id="passConfirmInput" name="password_confirmation" required 
                           class="input-glass rentify-input w-full pl-11 pr-11 py-3 rounded-2xl text-sm focus:outline-none font-bold" 
                           placeholder="Ulangi Kata Sandi">
                    <button type="button" onclick="togglePass('passConfirmInput', 'eye2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-sky-500 transition">
                        <i id="eye2" class="fa-regular fa-eye-slash text-xs"></i>
                    </button>
                </div>

                <!-- KOTAK SYARAT & KETENTUAN (LEBIH BESAR, BERSIH, RAMAH PENGGUNA) -->
                <div class="bg-white/40 border border-slate-200/80 rounded-2xl p-3 flex items-start gap-3 transition">
                    <input id="terms_customer" name="terms" type="checkbox" required
                           class="w-5 h-5 rounded-lg border-2 border-slate-300 text-sky-500 focus:ring-sky-400 mt-0.5 cursor-pointer accent-sky-500 transition flex-shrink-0">
                    <div class="text-xs text-slate-600 leading-snug">
                        <label for="terms_customer" class="cursor-pointer font-medium">
                            Saya menyetujui
                        </label>
                        <button type="button" onclick="openModal()" class="font-extrabold text-sky-600 hover:text-sky-700 underline transition cursor-pointer">
                            Syarat & Ketentuan
                        </button>
                        <span class="font-medium text-slate-600">Rentify.</span>
                    </div>
                </div>

                <!-- TOMBOL BUAT AKUN -->
                <div class="pt-1">
                    <button type="submit" class="w-full bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2">
                        <span>BUAT AKUN</span>
                    </button>
                </div>
            </form>

            <div class="mt-5 text-center">
                <p class="text-xs text-[#475569] font-medium">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-sky-600 font-extrabold hover:text-sky-700 hover:underline transition ml-1">Masuk di sini</a>
                </p>
            </div>
            
        </div>
    </div>

    <!-- MODAL POPUP SYARAT & KETENTUAN (TERANG) -->
    <div id="termsModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-2xl w-full flex flex-col shadow-2xl max-h-[85vh] overflow-hidden">
            <!-- Header Modal -->
            <div class="bg-slate-50 px-6 py-4 flex justify-between items-center shrink-0 border-b border-slate-200">
                <div>
                    <h3 class="font-black text-slate-800 text-sm tracking-wide">Syarat & Ketentuan Rentify</h3>
                    <p class="text-[11px] text-slate-500">Ketentuan umum penggunaan layanan sewa</p>
                </div>
                <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 hover:bg-slate-300 transition flex items-center justify-center text-lg">&times;</button>
            </div>
            
            <!-- Konten Bisa Di-scroll -->
            <div id="termsContent" class="p-6 overflow-y-auto space-y-4 text-xs text-slate-600 leading-relaxed custom-scrollbar">
                <p>Selamat datang di Rentify. Dengan mendaftar dan menggunakan platform ini, Anda menyatakan tunduk dan terikat pada syarat dan ketentuan berikut sesuai hukum yang berlaku di Republik Indonesia.</p>

                <div>
                    <h4 class="font-bold text-slate-800 mb-1">1. Ketentuan Umum & Definisi</h4>
                    <ul class="list-disc pl-4 space-y-1">
                        <li><strong>Rentify</strong> adalah platform perantara yang mempertemukan pihak yang ingin menyewakan barang (Vendor) dengan pihak yang ingin menyewa (Customer).</li>
                        <li>Rentify tidak memiliki, menguasai, atau menyimpan barang yang disewakan di dalam platform ini.</li>
                        <li>Pengguna wajib berusia minimal 17 tahun dan/atau memiliki identitas resmi (KTP/SIM/Paspor) yang sah menurut hukum Republik Indonesia.</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-800 mb-1">2. Tanggung Jawab & Kewajiban Customer</h4>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Customer wajib memberikan informasi identitas asli dan alamat yang valid saat melakukan transaksi penyewaan barang.</li>
                        <li>Customer dilarang keras menggadaikan, menjual, merusak dengan sengaja, atau menghilangkan barang sewaan.</li>
                        <li>Keterlambatan pengembalian barang akan dikenakan denda sesuai kebijakan masing-masing Vendor.</li>
                        <li>Kerusakan barang di luar batas wajar pemakaian (wear and tear) wajib diganti rugi oleh Customer sesuai nilai barang atau kesepakatan dengan Vendor.</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-800 mb-1">3. Tanggung Jawab & Hak Vendor</h4>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Vendor bertanggung jawab penuh atas keakuratan deskripsi, kondisi asli, dan kualitas barang yang disewakan.</li>
                        <li>Vendor berhak menolak pesanan jika Customer dinilai mencurigakan atau tidak memenuhi syarat verifikasi identitas.</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-800 mb-1">4. Batasan Tanggung Jawab</h4>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify terlepas dari segala tuntutan hukum atas kerusakan, kehilangan, kecurian, atau penggelapan barang sewaan yang dilakukan oleh pengguna.</li>
                        <li>Segala sengketa atau wanprestasi adalah tanggung jawab penuh antara Vendor dan Customer.</li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Modal -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-end items-center shrink-0">
                <button onclick="acceptTermsFromModal()" class="rentify-btn py-2.5 px-6 rounded-xl text-xs font-black tracking-wider uppercase shadow-md transition">
                    Saya Mengerti & Setuju
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL PERINGATAN GOOGLE (TERANG) -->
    <div id="googleAlertModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl transform transition-all scale-95 opacity-0" id="googleAlertBox">
            <div class="w-14 h-14 bg-sky-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-sky-600">
                <i class="fa-solid fa-shield-halved text-2xl"></i>
            </div>
            <h3 class="text-base font-black text-slate-800 mb-1.5">Persetujuan Diperlukan</h3>
            <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                Silakan centang kotak persetujuan <b>Syarat & Ketentuan</b> terlebih dahulu untuk melanjutkan pendaftaran dengan akun Google Anda.
            </p>
            <div class="flex gap-2">
                <button onclick="closeGoogleAlert()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 rounded-xl transition text-xs">
                    Batal
                </button>
                <button onclick="agreeAndGoogle()" class="flex-1 rentify-btn py-2.5 rounded-xl text-xs font-black tracking-wider uppercase transition shadow-md">
                    Setujui & Lanjut
                </button>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                input.type = 'password';
                eye.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        function openModal() { 
            document.getElementById('termsModal').classList.remove('hidden'); 
        }

        function closeModal() { 
            document.getElementById('termsModal').classList.add('hidden'); 
        }

        function acceptTermsFromModal() {
            document.getElementById('terms_customer').checked = true;
            closeModal();
        }

        function handleGoogleLogin() {
            const checkbox = document.getElementById('terms_customer');
            if (checkbox && checkbox.checked) {
                window.location.href = "{{ route('google.login') }}?agreed=1";
            } else {
                const modal = document.getElementById('googleAlertModal');
                const box = document.getElementById('googleAlertBox');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        }

        function agreeAndGoogle() {
            const checkbox = document.getElementById('terms_customer');
            if (checkbox) checkbox.checked = true;
            window.location.href = "{{ route('google.login') }}?agreed=1";
        }

        function closeGoogleAlert() {
            const modal = document.getElementById('googleAlertModal');
            const box = document.getElementById('googleAlertBox');
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }
    </script>
</body>
</html>