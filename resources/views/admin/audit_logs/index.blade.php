<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Jejak Trail Audit Admin - Rentify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex h-screen overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col flex-shrink-0 z-20">
        <div class="h-16 flex items-center px-6 border-b border-slate-100">
            <a href="/admin/dashboard" class="flex items-center space-x-2">
                <span class="text-xl font-extrabold text-[#0369a1]">Rentify</span>
                <span class="text-[10px] bg-sky-100 text-[#0369a1] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Admin</span>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
            <nav class="space-y-1">
                <a href="/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-home text-slate-400 w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.penarikan.index') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-money-bill-transfer text-slate-400 w-5"></i>
                    <span>Pencairan Dana</span>
                </a>
                <a href="{{ route('admin.pembayaran') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-credit-card text-slate-400 w-5"></i>
                    <span>Verifikasi Pembayaran</span>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-bold bg-[#e0f2fe] text-[#0369a1] rounded-xl transition duration-200">
                    <i class="fas fa-shield-halved text-[#0369a1] w-5"></i>
                    <span>Trail Audit Admin</span>
                </a>
                <a href="{{ route('admin.mutasi.index') }}" class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition duration-200">
                    <i class="fas fa-bolt text-amber-500 w-5"></i>
                    <span>Auto-Detect Mutasi</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-[#0369a1] rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">AD</div>
                    <div class="truncate">
                        <h4 class="text-xs font-bold text-slate-800 truncate">Super Admin</h4>
                        <p class="text-[9px] text-slate-400 truncate">Online</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 px-8 flex items-center justify-between bg-white border-b border-slate-200">
            <div class="flex items-center space-x-3">
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Rekam Jejak Trail Audit Keuangan & Administratif</h2>
                <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center shadow-sm">
                    <i class="fas fa-fingerprint mr-1 text-emerald-600"></i> Immutable Audit Logs
                </span>
            </div>
            <div class="text-xs font-bold text-slate-500 flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                <i class="far fa-calendar-alt text-[#0369a1]"></i>
                <span>{{ date('d M Y') }}</span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            <!-- FILTER & SEARCH -->
            <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-4 items-center justify-between">
                <div class="flex flex-wrap gap-3 items-center">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ID, Admin, atau IP..." class="text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0369a1] w-64">

                    <select name="action" class="text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0369a1]">
                        <option value="">Semua Jenis Aksi</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $act)) }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2.5 bg-[#0369a1] text-white text-xs font-bold rounded-xl hover:bg-[#0284c7] transition flex items-center">
                        <i class="fas fa-filter mr-1.5"></i> Filter
                    </button>
                    @if(request('action') || request('search'))
                        <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-semibold underline">Reset</a>
                    @endif
                </div>
                <div class="text-xs text-slate-500 font-bold">
                    Total Rekaman: <span class="text-[#0369a1] font-black">{{ $logs->total() }}</span> entri
                </div>
            </form>

            <!-- TABLE AUDIT LOGS -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-black text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Waktu & Tanggal</th>
                            <th class="py-3.5 px-4">Admin Pelaksana</th>
                            <th class="py-3.5 px-4">Aksi Audit</th>
                            <th class="py-3.5 px-4">Target Entitas</th>
                            <th class="py-3.5 px-4">Nominal / Nilai Sebelum</th>
                            <th class="py-3.5 px-4">Nominal / Nilai Sesudah</th>
                            <th class="py-3.5 px-4">IP Address & Browser</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $log->admin->name ?? 'System (ID ' . $log->admin_id . ')' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $log->admin->email ?? 'admin@rentify.id' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $actionBadge = match($log->action) {
                                        'refund_pembayaran' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'pencairan_penarikan_vendor' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'penolakan_penarikan_vendor' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'pembatalan_pesanan' => 'bg-red-50 text-red-700 border-red-200',
                                        'verifikasi_manual_pembayaran' => 'bg-sky-50 text-sky-700 border-sky-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $actionBadge }}">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono text-[11px]">
                                <span class="font-bold text-slate-700">#{{ $log->auditable_id }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ class_basename($log->auditable_type) }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="bg-rose-50/50 border border-rose-100 rounded-lg p-2 text-[10px] font-mono text-slate-700 max-w-xs break-all">
                                    @if(is_array($log->payload_before))
                                        @foreach($log->payload_before as $k => $v)
                                            <div><strong class="text-slate-500">{{ $k }}:</strong> {{ is_array($v) ? json_encode($v) : $v }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="bg-emerald-50/50 border border-emerald-100 rounded-lg p-2 text-[10px] font-mono text-slate-700 max-w-xs break-all">
                                    @if(is_array($log->payload_after))
                                        @foreach($log->payload_after as $k => $v)
                                            <div><strong class="text-slate-500">{{ $k }}:</strong> {{ is_array($v) ? json_encode($v) : $v }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                <div class="font-bold text-slate-700">{{ $log->ip_address ?: '127.0.0.1' }}</div>
                                <div class="text-[9px] text-slate-400 truncate max-w-[140px]" title="{{ $log->user_agent }}">{{ $log->user_agent ?: 'Unknown User-Agent' }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fas fa-shield-alt text-3xl mb-2 text-slate-300 block"></i>
                                Belum ada rekam jejak audit yang tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </main>

</body>
</html>
