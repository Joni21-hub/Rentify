<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background: radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%);
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
<body class="min-h-screen w-full flex items-start sm:items-center justify-center p-4 pt-16 sm:pt-4 relative overflow-hidden bg-flowing text-slate-800">

    <!-- Efek Bintang Berkedip -->
    <div class="w-full max-w-[380px] relative z-10 ">
        
        <div class="glass-panel rentify-card p-8 sm:p-10 relative">
            
            <div class="text-center mb-8">
                <h1 class="text-5xl sm:text-6xl font-black tracking-tighter text-sky-500 mb-2">Rentify</h1>
                <p class="text-[12px] text-[#475569] font-medium tracking-wide">Mulai petualangan serumu bersama kami.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-rose-500/90 backdrop-blur-md border border-rose-400 text-white px-4 py-3 rounded-xl text-[10px] font-bold shadow-lg">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" id="registerForm" class="space-y-4">
                @csrf 

                <!-- Tombol Google di-intercept oleh JS untuk memastikan S&K dicentang -->
                <button type="button" onclick="handleGoogleLogin()" class="w-full flex items-center justify-center gap-3 py-3.5 bg-white/60 hover:bg-white/80 border border-slate-300 rounded-2xl transition-all font-extrabold text-[#475569] text-sm shadow-sm mb-2"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-5 h-5 bg-white rounded-full p-0.5">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                    </svg>Daftar dengan Google</button>

                <div class="flex items-center gap-3 my-5">
                    <div class="h-px bg-slate-300 flex-1"></div>
                    <span class="text-[10px] font-bold text-[#475569] tracking-widest">ATAU</span>
                    <div class="h-px bg-slate-300 flex-1"></div>
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-sky-500 transition"><i class="fa-solid fa-user text-xs"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" required class="input-glass rentify-input w-full pl-11 pr-4 py-3.5 rounded-2xl text-sm focus:outline-none font-bold" placeholder="Nama Lengkap">
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-green-600 transition"><i class="fa-brands fa-whatsapp text-xs"></i></span>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" required class="input-glass rentify-input w-full pl-11 pr-4 py-3.5 rounded-2xl text-sm focus:outline-none font-bold" placeholder="No. WhatsApp">
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-sky-500 transition"><i class="fa-solid fa-lock text-xs"></i></span>
                    <input type="password" id="passInput" name="password" required class="input-glass rentify-input w-full pl-11 pr-11 py-3.5 rounded-2xl text-sm focus:outline-none font-bold" placeholder="Kata Sandi (Min 8)">
                    <button type="button" onclick="togglePass('passInput', 'eye1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600 hover:text-sky-500 transition"><i id="eye1" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-600 group-focus-within:text-sky-500 transition"><i class="fa-solid fa-shield-halved text-xs"></i></span>
                    <input type="password" id="passConfirmInput" name="password_confirmation" required class="input-glass rentify-input w-full pl-11 pr-11 py-3.5 rounded-2xl text-sm focus:outline-none font-bold" placeholder="Konfirmasi Kata Sandi">
                    <button type="button" onclick="togglePass('passConfirmInput', 'eye2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600 hover:text-sky-500 transition"><i id="eye2" class="fa-regular fa-eye-slash text-xs"></i></button>
                </div>

                <!-- Bagian Syarat & Ketentuan -->
                <div class="flex items-start pt-1 pb-1">
                    <div class="flex items-center h-4 relative group mt-0.5">
                        <input id="terms_customer" name="terms" type="checkbox" required disabled
                            class="w-3.5 h-3.5 border border-white/50 rounded focus:ring-2 focus:ring-sky-300 bg-white/30 checked:bg-sky-500 transition opacity-50 cursor-not-allowed">
                    </div>
                    <div class="ml-2 text-[10px]">
                        <label class="font-bold text-[#475569] leading-tight block drop-shadow-sm">
                            Menyetujui 
                            <button type="button" onclick="openModal()" class="font-extrabold text-sky-600 hover:text-sky-700 underline transition cursor-pointer">Syarat & Ketentuan</button>.
                        </label>
                        <p id="scrollAlert" class="text-[8.5px] text-rose-300 font-extrabold mt-0.5 animate-pulse drop-shadow-sm">
                            <i class="fa-solid fa-lock mr-0.5"></i> Baca dokumen untuk menyetujui
                        </p>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-gradient-to-br from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold py-3.5 rounded-2xl text-sm tracking-widest uppercase shadow-md transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-2">
<span>BUAT AKUN</span>
</button>
                </div>
            </form>

            <div class="mt-8 space-y-4 text-center">
                <p class="text-[11px] text-[#475569] font-medium">Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-sky-600 font-extrabold hover:text-sky-700 hover:underline transition ml-1">Masuk di sini</a>
                </p>
            </div>
            
        </div>
    </div>

    <!-- MODAL POPUP SYARAT & KETENTUAN (TERANG) -->
    <div id="termsModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full flex flex-col shadow-2xl max-h-[85vh] overflow-hidden">
            <!-- Header Modal -->
            <div class="bg-slate-50 px-6 py-4 flex justify-between items-center shrink-0 border-b border-slate-200">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm tracking-wide">Syarat & Ketentuan</h3>
                    <p class="text-[10px] text-slate-500">Silakan gulir hingga akhir untuk menyetujui.</p>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition text-xl">&times;</button>
            </div>
            
            <!-- Konten Bisa Di-scroll -->
            <div id="termsContent" onscroll="checkScroll(this)" class="p-6 overflow-y-auto space-y-4 text-[11px] text-slate-600 leading-relaxed custom-scrollbar">
                <p>Selamat datang di Rentify. Dengan mendaftar dan menggunakan platform ini (baik secara manual maupun melalui Google), Anda menyatakan tunduk dan terikat pada syarat dan ketentuan berikut sesuai dengan hukum yang berlaku di Republik Indonesia.</p>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">1. Ketentuan Umum & Definisi</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li><strong>Rentify</strong> adalah platform perantara yang mempertemukan pihak yang ingin menyewakan barang (Vendor) dengan pihak yang ingin menyewa (Customer).</li>
                        <li>Rentify tidak memiliki, menguasai, atau menyimpan satupun barang yang disewakan di dalam platform ini.</li>
                        <li>Pengguna wajib berusia minimal 17 tahun dan/atau memiliki identitas resmi (KTP/SIM/Paspor) yang sah menurut hukum Republik Indonesia.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">2. Tanggung Jawab & Kewajiban Customer</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Customer wajib memberikan informasi identitas asli dan alamat yang valid saat melakukan transaksi penyewaan barang.</li>
                        <li>Customer dilarang keras menggadaikan, menjual, merusak dengan sengaja, atau menghilangkan barang sewaan.</li>
                        <li>Keterlambatan pengembalian barang akan dikenakan denda sesuai dengan kebijakan masing-masing Vendor yang tertera di halaman produk.</li>
                        <li>Kerusakan barang di luar batas wajar pemakaian (wear and tear) wajib diganti rugi oleh Customer sesuai nilai barang atau kesepakatan dengan Vendor.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">3. Tanggung Jawab & Hak Vendor</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Vendor bertanggung jawab penuh atas keakuratan deskripsi, kondisi asli, dan kualitas barang yang disewakan.</li>
                        <li>Vendor berhak menolak pesanan jika Customer dinilai mencurigakan atau tidak memenuhi syarat verifikasi identitas.</li>
                        <li>Rentify akan memotong biaya layanan (Platform Fee) sebesar persentase yang disepakati dari setiap transaksi yang berhasil.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">4. Batasan & Pelepasan Tanggung Jawab Rentify</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Rentify <strong>terlepas dari segala tuntutan hukum</strong> atas kerusakan, kehilangan, kecurian, atau penggelapan barang sewaan yang dilakukan oleh Customer.</li>
                        <li>Segala bentuk sengketa, wanprestasi, atau tindak pidana (penipuan) adalah tanggung jawab penuh antara Vendor dan Customer.</li>
                        <li>Rentify hanya bertindak sebagai fasilitator penyedia data riwayat transaksi jika sewaktu-waktu dibutuhkan oleh Pihak Berwajib (Kepolisian).</li>
                        <li>Rentify berhak membekukan atau menghapus akun pengguna secara sepihak jika terdeteksi aktivitas mencurigakan, penipuan, atau pelanggaran S&K tanpa pemberitahuan sebelumnya.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[12px] font-bold text-slate-800 mb-1">5. Barang yang Dilarang</h3>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Dilarang menyewakan barang-barang ilegal, berbahaya, senjata api, senjata tajam tanpa izin, obat-obatan terlarang, atau barang hasil tindak kejahatan.</li>
                        <li>Pelanggaran terhadap aturan ini akan langsung dilaporkan kepada pihak Kepolisian.</li>
                    </ul>
                </div>
                
                <div class="h-10"></div>
            </div>
            
            <!-- Footer Modal -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center shrink-0">
                <span id="scrollProgress" class="text-[10px] font-bold text-rose-500 animate-pulse"><i class="fa-solid fa-arrow-down mr-1"></i> Gulir ke bawah</span>
                <button onclick="closeModal()" class="bg-sky-100 hover:bg-sky-200 text-sky-700 font-bold py-1.5 px-4 rounded-lg transition text-[10px]">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL PERINGATAN GOOGLE (TERANG) -->
    <div id="googleAlertModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl transform transition-all scale-95 opacity-0" id="googleAlertBox">
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-rose-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-extrabold text-slate-800 mb-2">Tindakan Diperlukan</h3>
            <p class="text-xs text-slate-600 mb-8 leading-relaxed">
                Untuk alasan keamanan dan hukum, Anda <b>wajib membaca dan menyetujui Syarat & Ketentuan</b> kami di bawah formulir ini sebelum dapat melanjutkan pendaftaran menggunakan Google.
            </p>
            <button onclick="closeGoogleAlert()" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-3 rounded-xl transition text-sm shadow-md">
                Mengerti
            </button>
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
            // Cek langsung jika layar sangat panjang sehingga tidak ada scrollbar
            setTimeout(() => {
                const content = document.getElementById('termsContent');
                if (content && content.scrollHeight <= content.clientHeight + 15) {
                    checkScroll(content);
                }
            }, 100);
        }
        function closeModal() { document.getElementById('termsModal').classList.add('hidden'); }
        function checkScroll(element) {
            if (element.scrollHeight - element.scrollTop <= element.clientHeight + 15) {
                const checkbox = document.getElementById('terms_customer');
                checkbox.disabled = false;
                checkbox.classList.remove('opacity-50', 'cursor-not-allowed');
                
                const alertText = document.getElementById('scrollAlert');
                alertText.innerHTML = '<i class="fa-solid fa-check-circle mr-0.5"></i> Syarat dibaca, silakan centang.';
                alertText.classList.replace('text-rose-300', 'text-emerald-300');
                alertText.classList.remove('animate-pulse');

                const progressText = document.getElementById('scrollProgress');
                progressText.innerHTML = '<i class="fa-solid fa-check text-emerald-300 mr-1"></i> Disetujui';
                progressText.classList.remove('text-rose-300', 'animate-pulse');
                progressText.classList.add('text-[#475569]');
            }
        }

        // FUNGSI CEGAH LOGIN GOOGLE JIKA BELUM CENTANG S&K
        function handleGoogleLogin() {
            const checkbox = document.getElementById('terms_customer');
            if (checkbox.checked) {
                // Jika sudah dicentang, arahkan ke Google Auth dengan parameter agreed=1
                window.location.href = "{{ route('google.login') }}?agreed=1";
            } else {
                // Jika belum, tampilkan peringatan
                const modal = document.getElementById('googleAlertModal');
                const box = document.getElementById('googleAlertBox');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
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