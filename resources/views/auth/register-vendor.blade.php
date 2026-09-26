<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mitra Vendor — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#38bdf8">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-flowing {
            background: linear-gradient(-45deg, #0284c7, #38bdf8, #0ea5e9, #0369a1);
            background-size: 300% 300%;
            animation: gradientFlow 15s ease infinite;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-top: 1px solid rgba(255, 255, 255, 0.7);
            border-left: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
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

        .sparkle {
            position: absolute;
            width: 4px; height: 4px;
            background-color: white;
            border-radius: 50%;
            opacity: 0;
            animation: twinkle 4s infinite ease-in-out;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0; transform: scale(0.5); }
            50% { opacity: 0.8; transform: scale(1.5); box-shadow: 0 0 12px rgba(255,255,255,1); }
        }
        
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-flowing text-slate-800">

    <!-- Efek Bintang Berkedip -->
    <div class="sparkle top-[10%] left-[10%]" style="animation-delay: 0s;"></div>
    <div class="sparkle top-[30%] right-[10%]" style="animation-delay: 1.5s;"></div>
    <div class="sparkle bottom-[20%] left-[15%]" style="animation-delay: 0.7s;"></div>
    <div class="sparkle bottom-[10%] right-[20%]" style="animation-delay: 2s;"></div>
    <div class="sparkle top-[60%] left-[5%]" style="animation-delay: 2.5s;"></div>

    <div class="w-full max-w-[850px] relative z-10 my-[2vh] lg:my-0">
        
        <div class="glass-panel relative flex flex-col md:flex-row overflow-hidden">
            
            <!-- BAGIAN KIRI (Info Vendor) -->
            <div class="md:w-5/12 p-8 sm:p-10 flex flex-col justify-center bg-white/10 border-b md:border-b-0 md:border-r border-white/30 text-white relative overflow-hidden">
                <!-- Aksen cahaya latar -->
                <div class="absolute -top-20 -left-20 w-64 h-64 bg-white/20 blur-[80px] rounded-full"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-widest drop-shadow-[0_5px_5px_rgba(0,0,0,0.3)] mb-2" style="text-shadow: 0 0 20px rgba(255,255,255,0.4);">
                        RENTIFY<br>VENDOR
                    </h1>
                    <p class="text-xs text-white/90 font-medium tracking-wide mb-8 leading-relaxed">Bergabunglah menjadi mitra resmi Rentify dan kembangkan bisnis rental Anda ke level selanjutnya.</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0 shadow-lg border border-white/30 text-white">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-extrabold mb-1">Jangkauan Luas</h4>
                                <p class="text-[10px] text-white/80 leading-relaxed">Temukan ribuan pelanggan baru yang siap menyewa barang Anda setiap harinya.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0 shadow-lg border border-white/30 text-white">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-extrabold mb-1">Keamanan Transaksi</h4>
                                <p class="text-[10px] text-white/80 leading-relaxed">Sistem pembayaran otomatis yang aman, tercatat, dan dapat diandalkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN KANAN (Form Registrasi) -->
            <div class="md:w-7/12 p-8 sm:p-10 relative">
                
                <div class="mb-6">
                    <h2 class="text-2xl font-extrabold text-white drop-shadow-md">Daftar Akun Mitra</h2>
                    <p class="text-[11px] text-white/90">Lengkapi data di bawah ini untuk membuka toko.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-[10px] font-bold shadow-lg">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('vendor.register') }}" method="POST" class="space-y-4">
                    @csrf 

                    <!-- Grid untuk 2 Kolom Input -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Lengkap Pemilik -->
                        <div class="relative group">
                            <label class="block text-[10px] font-bold text-white/90 mb-1.5 ml-1">Nama Pemilik Sesuai KTP</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-600 group-focus-within:text-blue-600 transition"><i class="fa-solid fa-user-tie text-xs"></i></span>
                                <input type="text" name="name" value="{{ old('name') }}" required class="input-glass w-full pl-9 pr-4 py-2.5 rounded-xl text-[11px] focus:outline-none font-bold" placeholder="Cth: Budi Santoso">
                            </div>
                        </div>

                        <!-- Nama Toko -->
                        <div class="relative group">
                            <label class="block text-[10px] font-bold text-white/90 mb-1.5 ml-1">Nama Toko Rental</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-600 group-focus-within:text-blue-600 transition"><i class="fa-solid fa-store text-xs"></i></span>
                                <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" required class="input-glass w-full pl-9 pr-4 py-2.5 rounded-xl text-[11px] focus:outline-none font-bold" placeholder="Cth: Budi Kamera">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="relative group">
                            <label class="block text-[10px] font-bold text-white/90 mb-1.5 ml-1">Email Aktif (Utama)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-600 group-focus-within:text-blue-600 transition"><i class="fa-solid fa-envelope text-xs"></i></span>
                                <input type="email" name="email" value="{{ old('email') }}" required class="input-glass w-full pl-9 pr-4 py-2.5 rounded-xl text-[11px] focus:outline-none font-bold" placeholder="vendor@email.com">
                            </div>
                        </div>

                        <!-- WhatsApp -->
                        <div class="relative group">
                            <label class="block text-[10px] font-bold text-white/90 mb-1.5 ml-1">No. WhatsApp Toko</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-600 group-focus-within:text-green-600 transition"><i class="fa-brands fa-whatsapp text-xs"></i></span>
                                <input type="text" name="whatsapp_vendor" value="{{ old('whatsapp_vendor') }}" required class="input-glass w-full pl-9 pr-4 py-2.5 rounded-xl text-[11px] focus:outline-none font-bold" placeholder="081234567xxx">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="relative group">
                            <label class="block text-[10px] font-bold text-white/90 mb-1.5 ml-1">Kata Sandi</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-600 group-focus-within:text-blue-600 transition"><i class="fa-solid fa-lock text-xs"></i></span>
                                <input type="password" id="passInput" name="password" required class="input-glass w-full pl-9 pr-9 py-2.5 rounded-xl text-[11px] focus:outline-none font-bold" placeholder="Min 8 karakter">
                                <button type="button" onclick="togglePass('passInput', 'eye1')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600 hover:text-blue-600 transition"><i id="eye1" class="fa-regular fa-eye-slash text-xs"></i></button>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="relative group">
                            <label class="block text-[10px] font-bold text-white/90 mb-1.5 ml-1">Konfirmasi Sandi</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-600 group-focus-within:text-blue-600 transition"><i class="fa-solid fa-shield-halved text-xs"></i></span>
                                <input type="password" id="passConfirmInput" name="password_confirmation" required class="input-glass w-full pl-9 pr-9 py-2.5 rounded-xl text-[11px] focus:outline-none font-bold" placeholder="Ulangi sandi">
                                <button type="button" onclick="togglePass('passConfirmInput', 'eye2')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600 hover:text-blue-600 transition"><i id="eye2" class="fa-regular fa-eye-slash text-xs"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Syarat & Ketentuan -->
                    <div class="flex items-start pt-3 pb-1">
                        <div class="flex items-center h-4 relative group mt-0.5">
                            <input id="terms_vendor" name="terms" type="checkbox" required disabled
                                class="w-3.5 h-3.5 border border-white/50 rounded focus:ring-2 focus:ring-blue-300 bg-white/30 checked:bg-blue-600 transition opacity-50 cursor-not-allowed">
                        </div>
                        <div class="ml-2 text-[10px]">
                            <label class="font-bold text-white/90 leading-tight block drop-shadow-sm">
                                Saya menyatakan data di atas asli dan menyetujui seluruh 
                                <button type="button" onclick="openModal()" class="font-extrabold text-blue-200 hover:text-white underline transition cursor-pointer">Syarat & Ketentuan Vendor</button>.
                            </label>
                            <p id="scrollAlert" class="text-[8.5px] text-rose-300 font-extrabold mt-0.5 animate-pulse drop-shadow-sm">
                                <i class="fa-solid fa-lock mr-0.5"></i> Baca dokumen untuk membuka kunci pendaftaran
                            </p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-white hover:bg-gray-50 text-blue-700 font-extrabold py-3.5 rounded-xl text-xs tracking-widest uppercase shadow-[0_10px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_25px_rgba(0,0,0,0.2)] transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2">
                            <span>BUKA TOKO SEKARANG</span>
                            <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-[11px] text-white/90 font-medium">Bukan Vendor? 
                        <a href="{{ route('register') }}" class="text-white font-extrabold hover:text-blue-100 hover:underline transition ml-1">Daftar sebagai Customer</a>
                    </p>
                </div>
                
            </div>
        </div>
    </div>

    <!-- MODAL POPUP SYARAT & KETENTUAN VENDOR (TERANG) -->
    <div id="termsModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full flex flex-col shadow-2xl max-h-[85vh] overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 flex justify-between items-center shrink-0 border-b border-slate-200">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm tracking-wide">Syarat & Ketentuan Vendor Rentify</h3>
                    <p class="text-[10px] text-slate-500">Silakan gulir hingga akhir untuk menyetujui.</p>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition text-xl">&times;</button>
            </div>
            
            <div id="termsContent" onscroll="checkScroll(this)" class="p-6 overflow-y-auto space-y-4 text-[11px] text-slate-600 leading-relaxed custom-scrollbar">
                <p>Selamat datang calon Mitra Vendor Rentify! Mohon baca Syarat dan Ketentuan berikut dengan saksama sebelum membuka toko Anda.</p>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">1. Hak dan Kewajiban Vendor</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Vendor wajib memberikan informasi data diri (KTP) dan data toko yang akurat dan dapat dipertanggungjawabkan di hadapan hukum.</li>
                        <li>Vendor menjamin bahwa seluruh barang yang disewakan adalah hak milik sah Vendor dan bebas dari sengketa hukum.</li>
                        <li>Vendor bertanggung jawab penuh atas kualitas, kebersihan, dan fungsi barang sebelum diserahkan kepada penyewa.</li>
                        <li>Vendor wajib mematuhi seluruh standar operasional (SOP) serah terima barang yang ditetapkan oleh Rentify.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">2. Biaya Layanan & Penarikan Dana</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify berhak memotong biaya layanan (Platform Fee) sebesar <strong>5% (lima persen)</strong> dari total nilai setiap transaksi sewa yang berhasil.</li>
                        <li>Untuk pembayaran Non-Tunai (Payment Gateway), dana akan ditampung sementara oleh Rentify dan diteruskan ke Saldo Vendor (dikurangi potongan 5%).</li>
                        <li>Untuk pembayaran Tunai (COD), uang sepenuhnya diterima Vendor secara tunai, namun Vendor otomatis memiliki "Utang Fee 5%" ke sistem Rentify yang wajib dilunasi.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">3. Kebijakan Barang Rusak / Hilang</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify adalah penyedia sistem (Platform) dan <strong>TIDAK</strong> bertanggung jawab atas kehilangan, kerusakan, atau penggelapan barang milik Vendor oleh Customer.</li>
                        <li>Segala risiko bisnis sepenuhnya ditanggung oleh Vendor. Rentify akan memfasilitasi data diri lengkap Customer (seperti KTP) untuk membantu pelaporan ke Kepolisian jika diperlukan.</li>
                        <li>Vendor sangat disarankan meminta uang jaminan (Deposit) tambahan saat serah terima barang fisik jika nilai barang sangat tinggi.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">4. Barang Terlarang & Sanksi</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Dilarang keras menyewakan barang-barang ilegal, berbahaya, senjata, narkotika, atau barang hasil tindak kejahatan pencurian.</li>
                        <li>Apabila terdeteksi pelanggaran, Rentify berhak memblokir, menghapus, atau menahan dana Vendor tanpa pemberitahuan sebelumnya, serta melaporkannya ke pihak berwajib.</li>
                    </ul>
                </div>
                
                <div class="h-10"></div>
            </div>
            
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center shrink-0">
                <span id="scrollProgress" class="text-[10px] font-bold text-rose-500 animate-pulse"><i class="fa-solid fa-arrow-down mr-1"></i> Gulir ke bawah</span>
                <button onclick="closeModal()" class="bg-sky-100 hover:bg-sky-200 text-sky-700 font-bold py-1.5 px-4 rounded-lg transition text-[10px]">Tutup</button>
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
        function openModal() { document.getElementById('termsModal').classList.remove('hidden'); }
        function closeModal() { document.getElementById('termsModal').classList.add('hidden'); }
        function checkScroll(element) {
            // Beri toleransi 15px agar tidak sulit dicapai
            if (element.scrollHeight - element.scrollTop <= element.clientHeight + 15) {
                const checkbox = document.getElementById('terms_vendor');
                checkbox.disabled = false;
                checkbox.classList.remove('opacity-50', 'cursor-not-allowed');
                
                const alertText = document.getElementById('scrollAlert');
                alertText.innerHTML = '<i class="fa-solid fa-check-circle mr-0.5"></i> Syarat dibaca, silakan centang.';
                alertText.classList.replace('text-rose-300', 'text-emerald-300');
                alertText.classList.remove('animate-pulse');

                const progressText = document.getElementById('scrollProgress');
                progressText.innerHTML = '<i class="fa-solid fa-check text-emerald-500 mr-1"></i> Disetujui';
                progressText.classList.remove('text-rose-500', 'animate-pulse');
                progressText.classList.add('text-slate-600');
            }
        }
    </script>
</body>
</html>