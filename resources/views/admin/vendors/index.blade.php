<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Vendor - Rentify Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0D1B3E',
                        brandBlue: '#1E4DAA',
                        sky: '#5C9EE8',
                        ice: '#C8DFF8',
                        bgSoft: '#F4F8FF'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-bgSoft flex h-screen overflow-hidden text-slate-800 font-sans">

    <aside class="w-72 bg-navy text-white flex flex-col justify-between hidden md:flex shadow-2xl z-10">
        <div>
            <div class="p-6 border-b border-white/10 flex items-center space-x-3">
                <div class="w-10 h-10 bg-brandBlue rounded-lg flex items-center justify-center font-bold text-xl shadow-lg">R</div>
                <div>
                    <h1 class="text-base font-black tracking-wider">RENTIFY</h1>
                    <p class="text-[10px] text-ice font-medium tracking-widest uppercase">Admin Workspace</p>
                </div>
            </div>

            <nav class="p-4 space-y-1">
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-xs font-semibold text-white/70 hover:bg-white/5 hover:text-white transition duration-150">
                    <i class="fas fa-th-large text-sm"></i> <span>Dashboard Utama</span>
                </a>
                <div class="pt-4 pb-1 px-4 text-[10px] font-bold text-white/40 uppercase tracking-widest">Sistem Validasi</div>
                <a href="/admin/vendors-validation" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-xs font-bold bg-gradient-to-r from-brandBlue to-sky text-white shadow-md transition duration-150">
                    <i class="fas fa-user-shield text-sm"></i> <span>Validasi Vendor Baru</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-white/10">
            <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/5">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-sky/20 flex items-center justify-center text-sky font-bold text-xs">AD</div>
                    <div>
                        <p class="text-xs font-bold truncate max-w-[120px]">Super Admin</p>
                        <p class="text-[9px] text-white/40 uppercase font-black">Full Access</p>
                    </div>
                </div>
                <form action="/logout" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-white/40 hover:text-rose-400 rounded-lg hover:bg-rose-500/10 transition">
                        <i class="fas fa-power-off text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-brandBlue/5 rounded-xl text-brandBlue md:hidden">
                    <i class="fas fa-bars"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-navy tracking-tight">Validasi Pendaftaran Vendor Baru</h2>
                    <p class="text-[11px] text-slate-400 font-medium">Tinjau, setujui, atau tolak permohonan kemitraan pemilik toko sewaan baru</p>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-2xl flex items-center space-x-2 shadow-xs">
                <i class="fas fa-circle-check text-sm text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-2xl flex items-center space-x-2 shadow-xs">
                <i class="fas fa-circle-exclamation text-sm text-rose-500"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <div class="bg-white border border-slate-200/60 rounded-3xl shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-xs font-black text-navy uppercase tracking-wider">Daftar Antrean Permohonan</h3>
                    <span class="text-[10px] font-bold bg-brandBlue/10 text-brandBlue px-2.5 py-1 rounded-full border border-brandBlue/20">
                        {{ $vendors->count() }} Pendaftar
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-wider bg-slate-50/30">
                                <th class="p-4 pl-6">Nama Pengaju</th>
                                <th class="p-4">Alamat Email</th>
                                <th class="p-4 pr-6 text-center">Aksi Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-xs text-slate-600 font-medium">
                            @foreach($vendors as $vendor)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-4 pl-6 font-bold text-navy">{{ $vendor['name'] }}</td>
                                <td class="p-4 text-slate-500 font-mono text-[11px]">{{ $vendor['email'] }}</td>
                                <td class="p-4 pr-6 text-center">
                                    @if(($vendor['vendor_status'] ?? 'pending') == 'pending')
                                    <form action="/admin/vendors/{{ $vendor['id'] }}/approve" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-brandBlue hover:bg-navy text-white text-xs font-semibold px-4 py-2 rounded-xl transition shadow-sm hover:shadow-md flex items-center space-x-1.5 mx-auto">
                                            <i class="fas fa-check"></i> <span>Setujui Kemitraan</span>
                                        </button>
                                    </form>
                                    @else
                                    <button disabled class="bg-slate-100 text-slate-400 text-xs font-semibold px-4 py-2 rounded-xl cursor-not-allowed mx-auto flex items-center space-x-1.5">
                                        <i class="fas fa-user-check"></i> <span>Mitra Sah</span>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</body>
</html><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Vendor - Rentify Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0D1B3E',
                        brandBlue: '#1E4DAA',
                        sky: '#5C9EE8',
                        ice: '#C8DFF8',
                        bgSoft: '#F4F8FF'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-bgSoft flex h-screen overflow-hidden text-slate-800 font-sans">

    <aside class="w-72 bg-navy text-white flex flex-col justify-between hidden md:flex shadow-2xl z-10">
        <div>
            <div class="p-6 border-b border-white/10 flex items-center space-x-3">
                <div class="w-10 h-10 bg-brandBlue rounded-lg flex items-center justify-center font-bold text-xl shadow-lg">R</div>
                <div>
                    <h1 class="text-base font-black tracking-wider">RENTIFY</h1>
                    <p class="text-[10px] text-ice font-medium tracking-widest uppercase">Admin Workspace</p>
                </div>
            </div>

            <nav class="p-4 space-y-1">
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-xs font-semibold text-white/70 hover:bg-white/5 hover:text-white transition duration-150">
                    <i class="fas fa-th-large text-sm"></i> <span>Dashboard Utama</span>
                </a>
                <div class="pt-4 pb-1 px-4 text-[10px] font-bold text-white/40 uppercase tracking-widest">Sistem Validasi</div>
                <a href="/admin/vendors-validation" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-xs font-bold bg-gradient-to-r from-brandBlue to-sky text-white shadow-md transition duration-150">
                    <i class="fas fa-user-shield text-sm"></i> <span>Validasi Vendor Baru</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-white/10">
            <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/5">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-sky/20 flex items-center justify-center text-sky font-bold text-xs">AD</div>
                    <div>
                        <p class="text-xs font-bold truncate max-w-[120px]">Super Admin</p>
                        <p class="text-[9px] text-white/40 uppercase font-black">Full Access</p>
                    </div>
                </div>
                <form action="/logout" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-white/40 hover:text-rose-400 rounded-lg hover:bg-rose-500/10 transition">
                        <i class="fas fa-power-off text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-brandBlue/5 rounded-xl text-brandBlue md:hidden">
                    <i class="fas fa-bars"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-navy tracking-tight">Validasi Pendaftaran Vendor Baru</h2>
                    <p class="text-[11px] text-slate-400 font-medium">Tinjau, setujui, atau tolak permohonan kemitraan pemilik toko sewaan baru</p>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-2xl flex items-center space-x-2 shadow-xs">
                <i class="fas fa-circle-check text-sm text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-2xl flex items-center space-x-2 shadow-xs">
                <i class="fas fa-circle-exclamation text-sm text-rose-500"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <div class="bg-white border border-slate-200/60 rounded-3xl shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-xs font-black text-navy uppercase tracking-wider">Daftar Antrean Permohonan</h3>
                    <span class="text-[10px] font-bold bg-brandBlue/10 text-brandBlue px-2.5 py-1 rounded-full border border-brandBlue/20">
                        {{ $vendors->count() }} Pendaftar
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-wider bg-slate-50/30">
                                <th class="p-4 pl-6">Nama Pengaju</th>
                                <th class="p-4">Alamat Email</th>
                                <th class="p-4 pr-6 text-center">Aksi Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-xs text-slate-600 font-medium">
                            @foreach($vendors as $vendor)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-4 pl-6 font-bold text-navy">{{ $vendor['name'] }}</td>
                                <td class="p-4 text-slate-500 font-mono text-[11px]">{{ $vendor['email'] }}</td>
                                <td class="p-4 pr-6 text-center">
                                    @if(($vendor['vendor_status'] ?? 'pending') == 'pending')
                                    <form action="/admin/vendors/{{ $vendor['id'] }}/approve" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-brandBlue hover:bg-navy text-white text-xs font-semibold px-4 py-2 rounded-xl transition shadow-sm hover:shadow-md flex items-center space-x-1.5 mx-auto">
                                            <i class="fas fa-check"></i> <span>Setujui Kemitraan</span>
                                        </button>
                                    </form>
                                    @else
                                    <button disabled class="bg-slate-100 text-slate-400 text-xs font-semibold px-4 py-2 rounded-xl cursor-not-allowed mx-auto flex items-center space-x-1.5">
                                        <i class="fas fa-user-check"></i> <span>Mitra Sah</span>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</body>
</html>