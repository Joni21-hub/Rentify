<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gabung Mitra Vendor - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">

    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 border border-slate-100">
        
        <div class="md:col-span-5 bg-gradient-to-br from-[#0B2E83] via-[#1E4DAA] to-blue-600 p-8 md:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -top-20 -left-20 w-48 h-48 bg-white opacity-5 rounded-full blur-xl"></div>
            <div class="absolute -bottom-20 -right-20 w-60 h-60 bg-blue-400 opacity-20 rounded-full blur-2xl"></div>

            <div class="relative z-10">
                <div class="flex items-center space-x-3 mb-10">
                    <div class="w-11 h-11 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/30 shadow-lg overflow-hidden">
                       <img src="https://res.cloudinary.com/fnf8f1pm/image/upload/v1784199454/gambar_logo_trerjo.png" alt="Logo Rentify">
                    </div>
                    <span class="text-xl font-bold tracking-widest uppercase">Rentify</span>
                </div>
                
                <h2 class="text-2xl md:text-3xl font-extrabold leading-tight mb-4">Kembangkan Bisnis Sekarang!</h2>
                <p class="text-blue-100/90 text-xs md:text-sm leading-relaxed mb-6">Bergabunglah dengan Rentify. Kelola inventaris, terima pesanan otomatis, dan pantau penyewaan barangmu dengan mudah dalam satu platform.</p>
            </div>
            
            <div class="space-y-4 relative z-10 mt-6 md:mt-0 border-t border-white/10 pt-6">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-chart-line text-blue-200"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-xs">Jangkauan Lebih Luas</h4>
                        <p class="text-[10px] text-blue-200">Temukan lebih banyak pelanggan baru.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-xs">
                        <i class="fa-brands fa-whatsapp text-blue-200 text-sm"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-7 p-8 md:p-10 bg-white flex flex-col justify-center">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-1">Daftar Akun Vendor</h3>
                <p class="text-slate-400 text-xs">Lengkapi formulir di bawah ini untuk membuka toko rental Anda.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-600 text-xs shadow-sm">
                    <div class="font-bold flex items-center gap-1.5 mb-1"><i class="fa-solid fa-circle-exclamation"></i> Periksa kembali inputan:</div>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/vendor/register" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Lengkap Pemilik</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs"><i class="fa-regular fa-user"></i></span>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" placeholder="Sesuai kartu identitas">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Toko / Vendor</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs"><i class="fa-solid fa-store"></i></span>
                        <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" required class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" placeholder="Contoh: Rentify">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Email Aktif</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" placeholder="vendor@email.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">No. WhatsApp Toko</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs"><i class="fa-brands fa-whatsapp"></i></span>
                            <input type="text" name="whatsapp_vendor" value="{{ old('whatsapp_vendor') }}" required class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" placeholder="081234567xxx">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" required class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" placeholder="Minimal 8 karakter">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Konfirmasi Sandi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password_confirmation" required class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" placeholder="Ulangi kata sandi">
                        </div>
                    </div>
                </div>

                <!-- KOTAK CENTANG S&K VENDOR TERKUNCI -->
                <div class="flex items-start mt-4 mb-2">
                    <div class="flex items-center h-5">
                        <input id="terms_vendor" name="terms" type="checkbox" required disabled
                            class="w-4 h-4 border border-slate-300 rounded focus:ring-3 focus:ring-blue-500 checked:bg-blue-600 text-blue-600 transition opacity-50 cursor-not-allowed">
                    </div>
                    <div class="ml-3 text-[11px]">
                        <label class="font-medium text-slate-500 leading-tight block">
                            Saya menyatakan data di atas asli, dan menyetujui seluruh 
                            <button type="button" onclick="openModal()" class="font-bold text-blue-600 hover:text-blue-800 underline transition cursor-pointer">Syarat & Ketentuan</button> 
                            Vendor Rentify.
                        </label>
                        <p id="scrollAlertVendor" class="text-[9px] text-rose-500 font-bold mt-0.5 animate-pulse">
                            <i class="fa-solid fa-lock mr-0.5"></i> Baca dokumen hukum untuk menyetujui pendaftaran.
                        </p>
                    </div>
                </div>

                <button type="submit" class="w-full mt-4 bg-gradient-to-r from-[#0B2E83] to-[#1E4DAA] hover:from-blue-700 hover:to-blue-600 text-white font-bold py-3 rounded-xl transition-all transform active:scale-[0.99] shadow-lg shadow-blue-900/10 flex items-center justify-center space-x-2 text-xs">
                    <span>Buka Toko Vendor Sekarang</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP SYARAT & KETENTUAN (UNTUK VENDOR) -->
    <div id="termsModalVendor" class="fixed inset-0 bg-slate-900/70 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full flex flex-col shadow-2xl max-h-[85vh] overflow-hidden border-t-4 border-[#0B2E83]">
            <!-- Header Modal -->
            <div class="bg-[#0B2E83] px-6 py-4 flex justify-between items-center shrink-0">
                <div>
                    <h3 class="font-black text-white text-lg tracking-wider">Syarat & Ketentuan Vendor</h3>
                    <p class="text-[10px] text-blue-200">Silakan gulir hingga akhir untuk menyetujui.</p>
                </div>
                <button onclick="closeModal()" class="text-blue-200 hover:text-white transition text-2xl">&times;</button>
            </div>
            
            <!-- Konten Bisa Di-scroll -->
            <div id="termsContentVendor" onscroll="checkScrollVendor(this)" class="p-6 overflow-y-auto space-y-5 text-xs text-slate-600 leading-relaxed">
                <p>Selamat datang di Rentify. Dengan mendaftar sebagai Mitra Vendor, Anda menyatakan tunduk dan terikat pada syarat dan ketentuan hukum berikut.</p>

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
                        <li><strong>Kualitas dan Keamanan Barang:</strong> Segala jaminan mengenai kualitas, kelayakan pakai, dan keamanan barang adalah tanggung jawab mutlak Anda sebagai pihak Vendor.</li>
                        <li><strong>Tindak Pidana:</strong> Apabila terjadi tindak pidana seperti penggelapan barang oleh penyewa, penipuan, atau penyalahgunaan identitas, hal tersebut merupakan sengketa langsung antara Vendor dan Customer. Rentify dibebaskan dari tuntutan ganti rugi materiil dan imateriil.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1">3. Kesepakatan Sewa-Menyewa</h3>
                    <p>Hubungan hukum sewa-menyewa murni terjadi antara Vendor dan Customer. Transaksi yang disetujui di dalam Rentify berlaku sebagai undang-undang bagi mereka yang membuatnya (Psl 1338 KUHPerdata). Kebijakan mengenai Uang Jaminan (Deposit), Denda Keterlambatan, dan Ganti Rugi Kerusakan Anda tentukan sendiri secara mandiri saat mengunggah produk.</p>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1">4. Potongan / Komisi Platform (Fee)</h3>
                    <p>Rentify berhak mengambil komisi sebesar <strong>5% (lima persen)</strong> dari total nilai penyewaan (di luar ongkos kirim) atas setiap transaksi yang berstatus Selesai, sebagai biaya pemeliharaan dan layanan sistem elektronik (Platform Fee).</p>
                </div>
                
                <div class="h-10"></div> <!-- Ruang ekstra agar scroll sampai benar-benar bawah -->
            </div>
            
            <!-- Footer Modal -->
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                <span id="scrollProgressVendor" class="text-[10px] font-bold text-rose-500 animate-pulse"><i class="fa-solid fa-arrow-down mr-1"></i> Gulir ke bawah untuk setuju</span>
                <button onclick="closeModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-5 rounded-lg transition text-xs">Kembali</button>
            </div>
        </div>
    </div>

    <!-- Skrip Aksi Interaktif Vendor -->
    <script>
        // Fitur Modal & Wajib Scroll
        function openModal() {
            document.getElementById('termsModalVendor').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('termsModalVendor').classList.add('hidden');
        }

        // Pendeteksi Scroll Mentok Bawah
        function checkScrollVendor(element) {
            if (element.scrollHeight - element.scrollTop <= element.clientHeight + 15) {
                // Buka Kunci Checkbox
                const checkbox = document.getElementById('terms_vendor');
                checkbox.disabled = false;
                checkbox.classList.remove('opacity-50', 'cursor-not-allowed');
                
                // Ubah Teks Peringatan menjadi Sukses
                const alertText = document.getElementById('scrollAlertVendor');
                alertText.innerHTML = '<i class="fa-solid fa-check-circle mr-0.5"></i> Syarat dibaca, silakan centang kotak di atas.';
                alertText.classList.replace('text-rose-500', 'text-[#0B2E83]');
                alertText.classList.remove('animate-pulse');

                // Ubah status di Modal
                const progressText = document.getElementById('scrollProgressVendor');
                progressText.innerHTML = '<i class="fa-solid fa-check text-emerald-500 mr-1"></i> Telah disetujui';
                progressText.classList.remove('text-rose-500', 'animate-pulse');
                progressText.classList.add('text-slate-500');
            }
        }
    </script>
</body>
</html>