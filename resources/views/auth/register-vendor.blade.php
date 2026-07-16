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
                
                <h2 class="text-2xl md:text-3xl font-extrabold leading-tight mb-4">
                    Kembangkan Bisnis Sekarang!
                </h2>
                <p class="text-blue-100/90 text-xs md:text-sm leading-relaxed mb-6">
                    Bergabunglah dengan Rentify. Kelola inventaris, terima pesanan otomatis, dan pantau penyewaan barangmu dengan mudah dalam satu platform.
                </p>
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
                    <div>
                        <h4 class="font-semibold text-xs">Integrasi WhatsApp</h4>
                        <p class="text-[10px] text-blue-200">Notifikasi pesanan langsung ke HP Anda.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-7 p-8 md:p-10 bg-white flex flex-col justify-center">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-1">Daftar Akun Vendor</h3>
                <p class="text-slate-400 text-xs">Lengkapi formulir di bawah ini untuk membuka toko rental Anda.</p>
            </div>

            <form action="/vendor/register" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Lengkap Pemilik</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" name="name" required 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" 
                            placeholder="Sesuai kartu identitas">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Toko / Vendor</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">
                            <i class="fa-solid fa-store"></i>
                        </span>
                        <input type="text" name="vendor_name" required 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" 
                            placeholder="Contoh: Rentify">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Email Aktif</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" name="email" required 
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" 
                                placeholder="vendor@email.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">No. WhatsApp Toko</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">
                                <i class="fa-brands fa-whatsapp"></i>
                            </span>
                            <input type="text" name="whatsapp_vendor" required 
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" 
                                placeholder="081234567xxx">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="password" required 
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" 
                                placeholder="Minimal 8 karakter">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Konfirmasi Sandi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="password_confirmation" required 
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all text-slate-700" 
                                placeholder="Ulangi kata sandi">
                        </div>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full mt-4 bg-gradient-to-r from-[#0B2E83] to-[#1E4DAA] hover:from-blue-700 hover:to-blue-600 text-white font-bold py-3 rounded-xl transition-all transform active:scale-[0.99] shadow-lg shadow-blue-900/10 flex items-center justify-center space-x-2 text-xs">
                    <span>Buka Toko Vendor Sekarang</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>