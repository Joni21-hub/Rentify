<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Customer — Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
        .animate-float-1 { animation: float 8s ease-in-out infinite; }
        .animate-float-2 { animation: float 12s ease-in-out infinite alternate; }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 20px 50px rgba(14, 165, 233, 0.12), 0 0 20px rgba(255, 255, 255, 0.6);
        }

        .input-sky {
            background: rgba(240, 249, 255, 0.7);
            border: 1px solid rgba(186, 230, 253, 0.8);
            transition: all 0.3s ease;
        }
        .input-sky:focus {
            background: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.25);
        }

        @keyframes shimmer {
            100% { left: 125%; }
        }
        .btn-shimmer { position: relative; overflow: hidden; }
        .btn-shimmer::after {
            content: ''; position: absolute; top: -50%; left: -60%;
            width: 30%; height: 200%;
            background: rgba(255, 255, 255, 0.35);
            transform: rotate(30deg);
            animation: shimmer 3.5s infinite ease-in-out;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-gradient-to-br from-cyan-50 via-sky-100 to-blue-200 text-slate-700">

    <div class="absolute top-[10%] left-[15%] w-72 h-72 bg-gradient-to-tr from-cyan-300/40 to-sky-400/40 rounded-full blur-3xl pointer-events-none animate-float-1"></div>
    <div class="absolute bottom-[10%] right-[15%] w-80 h-80 bg-gradient-to-bl from-blue-300/40 via-sky-300/30 to-teal-200/40 rounded-full blur-3xl pointer-events-none animate-float-2"></div>
    <div class="absolute top-[40%] right-[30%] w-48 h-48 bg-cyan-200/50 rounded-full blur-2xl pointer-events-none"></div>

    <div class="w-full max-w-md glass-card rounded-[2.5rem] p-8 sm:p-10 relative z-10 transition-all duration-300">
        
        <div class="text-center mb-6">
            <div class="inline-block px-3 py-1 rounded-full bg-sky-100/80 border border-sky-200 text-[#0284c7] text-[10px] font-bold uppercase tracking-widest mb-2 shadow-sm">
                <i class="fa-solid fa-water sm:mr-1 animate-bounce"></i> Customer Portal
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 via-sky-600 to-blue-600 drop-shadow-sm">
                RENTIFY
            </h1>
            <p class="text-xs sm:text-sm text-sky-700/80 font-medium mt-1">Mulai petualangan serumu bersama kami.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-4 bg-rose-50/90 border border-rose-200 rounded-2xl text-rose-600 text-xs shadow-sm backdrop-blur-md">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-rose-700">
                    <i class="fa-solid fa-circle-exclamation"></i> Periksa kembali inputanmu:
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf 

            <div class="space-y-1">
                <label class="text-[11px] font-bold text-sky-800 uppercase tracking-wider block ml-1">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-400 text-sm"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-sky w-full pl-11 pr-4 py-3 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium shadow-inner" placeholder="Masukkan nama lengkap Anda">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-bold text-sky-800 uppercase tracking-wider block ml-1">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-400 text-sm"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" required class="input-sky w-full pl-11 pr-4 py-3 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium shadow-inner" placeholder="contoh@email.com">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-bold text-sky-800 uppercase tracking-wider block ml-1">Kata Sandi (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-400 text-sm"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="passInput" name="password" required class="input-sky w-full pl-11 pr-11 py-3 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium shadow-inner" placeholder="Minimal 8 karakter">
                    <button type="button" onclick="togglePass('passInput', 'eye1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-sky-400 hover:text-sky-600 transition"><i id="eye1" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-bold text-sky-800 uppercase tracking-wider block ml-1">Konfirmasi Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-400 text-sm"><i class="fa-solid fa-shield-halved"></i></span>
                    <input type="password" id="passConfirmInput" name="password_confirmation" required class="input-sky w-full pl-11 pr-11 py-3 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium shadow-inner" placeholder="Ulangi kata sandi Anda">
                    <button type="button" onclick="togglePass('passConfirmInput', 'eye2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-sky-400 hover:text-sky-600 transition"><i id="eye2" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>
            </div>

            <!-- KOTAK CENTANG S&K TERKUNCI -->
            <div class="flex items-start pt-1 pb-1">
                <div class="flex items-center h-5 relative group">
                    <input id="terms_customer" name="terms" type="checkbox" required disabled
                        class="w-4 h-4 border border-slate-300 rounded focus:ring-3 focus:ring-sky-300 checked:bg-sky-600 text-sky-600 transition opacity-50 cursor-not-allowed">
                </div>
                <div class="ml-3 text-[11px]">
                    <label class="font-medium text-sky-900/80 leading-tight block">
                        Saya telah membaca dan menyetujui seluruh 
                        <button type="button" onclick="openModal()" class="font-bold text-sky-600 hover:text-sky-800 underline transition cursor-pointer">Syarat & Ketentuan</button> 
                        Rentify.
                    </label>
                    <p id="scrollAlert" class="text-[9px] text-rose-500 font-bold mt-0.5 animate-pulse">
                        <i class="fa-solid fa-lock mr-0.5"></i> Baca dokumen untuk membuka centang
                    </p>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-shimmer w-full py-4 bg-gradient-to-r from-cyan-500 via-sky-500 to-blue-600 hover:from-cyan-600 hover:via-sky-600 hover:to-blue-700 text-white rounded-2xl font-bold text-sm shadow-lg shadow-sky-500/25 hover:shadow-sky-500/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0 tracking-wide flex items-center justify-center gap-2">
                    <span>Daftar Akun Sekarang</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>

        <div class="text-center mt-6 pt-5 border-t border-sky-100/80">
            <p class="text-xs text-slate-500 font-medium">Sudah punya akun Rentify? 
                <a href="{{ route('login') }}" class="text-sky-600 font-bold hover:text-cyan-600 hover:underline transition ml-1">Masuk di sini</a>
            </p>
        </div>
    </div>

    <!-- MODAL POPUP SYARAT & KETENTUAN (UNTUK CUSTOMER) -->
    <div id="termsModal" class="fixed inset-0 bg-slate-900/70 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full flex flex-col shadow-2xl max-h-[85vh] overflow-hidden border-t-4 border-sky-500">
            <!-- Header Modal -->
            <div class="bg-sky-600 px-6 py-4 flex justify-between items-center shrink-0">
                <div>
                    <h3 class="font-black text-white text-lg tracking-wider">Syarat & Ketentuan Rentify</h3>
                    <p class="text-[10px] text-sky-100">Silakan gulir hingga akhir untuk menyetujui.</p>
                </div>
                <button onclick="closeModal()" class="text-sky-100 hover:text-white transition text-2xl">&times;</button>
            </div>
            
            <!-- Konten Bisa Di-scroll -->
            <div id="termsContent" onscroll="checkScroll(this)" class="p-6 overflow-y-auto space-y-5 text-xs text-slate-600 leading-relaxed">
                <p>Selamat datang di Rentify. Dengan mendaftar dan menggunakan platform ini, Anda menyatakan tunduk dan terikat pada syarat dan ketentuan berikut sesuai dengan hukum yang berlaku di Republik Indonesia, termasuk namun tidak terbatas pada KUHPerdata dan UU ITE.</p>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1">1. Status dan Peran Platform</h3>
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>Rentify Hanya Perantara:</strong> Rentify adalah platform Penyelenggara Sistem Elektronik yang berfungsi secara eksklusif untuk mempertemukan pemilik barang (Vendor) dengan penyewa (Customer).</li>
                        <li><strong>Bukan Pemilik Barang:</strong> Rentify tidak memiliki, menguasai, menyimpan, atau mendistribusikan barang-barang yang disewakan. Seluruh barang adalah milik Vendor yang terdaftar.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1">2. Pelepasan Tanggung Jawab Hukum (Disclaimer)</h3>
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>Kerusakan dan Kehilangan:</strong> Rentify terlepas dari segala bentuk tanggung jawab hukum, baik perdata maupun pidana, atas kerusakan, kehilangan, cacat fisik, atau malfungsi pada barang sewaan yang terjadi selama masa penyewaan, pengiriman, maupun pengembalian.</li>
                        <li><strong>Kualitas dan Keamanan Barang:</strong> Segala jaminan mengenai kualitas, kelayakan pakai, dan keamanan barang adalah tanggung jawab mutlak pihak Vendor. Rentify tidak memberikan garansi atas kondisi barang.</li>
                        <li><strong>Tindak Pidana:</strong> Apabila terjadi tindak pidana seperti penggelapan barang oleh penyewa, penipuan, atau penyalahgunaan identitas, hal tersebut merupakan sengketa langsung antara Vendor dan Customer. Rentify dibebaskan dari tuntutan ganti rugi materiil dan imateriil.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1">3. Kesepakatan Sewa-Menyewa</h3>
                    <p>Hubungan hukum sewa-menyewa murni terjadi antara Vendor dan Customer. Transaksi yang disetujui di dalam Rentify berlaku sebagai undang-undang bagi mereka yang membuatnya (Psl 1338 KUHPerdata). Kebijakan mengenai Uang Jaminan (Deposit), Denda Keterlambatan, dan Ganti Rugi Kerusakan tunduk pada aturan yang ditetapkan oleh masing-masing Vendor.</p>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1">4. Penyelesaian Sengketa</h3>
                    <p>Apabila terjadi sengketa akibat wanprestasi, kedua belah pihak sepakat untuk menyelesaikannya secara musyawarah mufakat atau menempuh jalur hukum secara mandiri. Berdasarkan UU ITE, Rentify bersedia bekerja sama dengan pihak berwajib dengan memberikan data riwayat transaksi digital jika diminta melalui prosedur hukum yang sah.</p>
                </div>
                
                <div class="h-10"></div> <!-- Ruang ekstra agar scroll sampai benar-benar bawah -->
            </div>
            
            <!-- Footer Modal -->
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                <span id="scrollProgress" class="text-[10px] font-bold text-rose-500 animate-pulse"><i class="fa-solid fa-arrow-down mr-1"></i> Gulir ke bawah untuk setuju</span>
                <button onclick="closeModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-5 rounded-lg transition text-xs">Kembali</button>
            </div>
        </div>
    </div>

    <!-- Skrip Aksi Interaktif -->
    <script>
        // Fitur Intip Password
        function togglePass(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            } else {
                input.type = 'password';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            }
        }

        // Fitur Modal & Wajib Scroll
        function openModal() {
            document.getElementById('termsModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('termsModal').classList.add('hidden');
        }

        // Pendeteksi Scroll Mentok Bawah
        function checkScroll(element) {
            // Toleransi jarak ~15px agar tetap terdeteksi meski layar di-zoom
            if (element.scrollHeight - element.scrollTop <= element.clientHeight + 15) {
                // Buka Kunci Checkbox
                const checkbox = document.getElementById('terms_customer');
                checkbox.disabled = false;
                checkbox.classList.remove('opacity-50', 'cursor-not-allowed');
                
                // Ubah Teks Peringatan menjadi Sukses
                const alertText = document.getElementById('scrollAlert');
                alertText.innerHTML = '<i class="fa-solid fa-check-circle mr-0.5"></i> Syarat dibaca, silakan centang.';
                alertText.classList.replace('text-rose-500', 'text-emerald-500');
                alertText.classList.remove('animate-pulse');

                // Ubah status di Modal
                const progressText = document.getElementById('scrollProgress');
                progressText.innerHTML = '<i class="fa-solid fa-check text-emerald-500 mr-1"></i> Telah disetujui';
                progressText.classList.remove('text-rose-500', 'animate-pulse');
                progressText.classList.add('text-slate-500');
            }
        }
    </script>
</body>
</html>