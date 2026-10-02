@extends('layouts.vendor')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-20">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('vendor.barang.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:text-sky-700 transition mb-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Produk
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Edit Produk Sewa</h1>
            <p class="text-xs text-slate-500 font-medium">Perbarui informasi, spesifikasi kategori, tarif, dan foto barang.</p>
        </div>
        <span class="px-3 py-1.5 rounded-full text-xs font-black {{ $barang->status_barang == 'disetujui' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
            {{ ucfirst($barang->status_barang ?? 'Pending') }}
        </span>
    </div>

    <!-- Alert Validasi Eror -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
            <div class="font-black flex items-center gap-2 text-sm text-rose-800">
                <i class="fa-solid fa-triangle-exclamation"></i> Terjadi Kesalahan Input:
            </div>
            <ul class="list-disc pl-5 font-semibold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendor.barang.update', $barang->id) }}" method="POST" enctype="multipart/form-data" id="formEditBarang">
        @csrf
        @method('PUT')

        @php
            $spek = $barang->spesifikasi ?? [];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- ============================================== -->
            <!-- KOLOM KIRI (2 SPAN): FOTO, INFORMASI & SPESIFIKASI -->
            <!-- ============================================== -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. FOTO PRODUK -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-camera text-sky-500"></i> Foto Produk
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Biarkan kosong jika tidak ingin mengganti foto cover.</p>
                        </div>
                    </div>

                    <!-- Foto Saat Ini -->
                    @if($barang->cover_photo)
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-3">
                        <img src="{{ asset(str_replace('public/', '', $barang->cover_photo)) }}" class="w-16 h-16 rounded-xl object-cover border border-slate-200" alt="Cover Saat Ini">
                        <div>
                            <span class="text-xs font-black text-slate-800 block">Foto Cover Saat Ini</span>
                            <span class="text-[11px] text-slate-400">Pilih foto baru di bawah jika ingin memperbarui.</span>
                        </div>
                    </div>
                    @endif

                    <!-- Hidden Input -->
                    <input type="file" name="fotos[]" id="fotos" multiple accept="image/jpeg,image/png,image/jpg" class="hidden">

                    <label for="fotos" class="cursor-pointer p-4 rounded-2xl border-2 border-dashed border-sky-400/80 bg-sky-50/60 hover:bg-sky-100/70 transition flex items-center justify-center gap-3 text-sky-700">
                        <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-base shadow-sm">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div class="text-left">
                            <span class="block text-xs font-black">Unggah Foto Baru (Opsional)</span>
                            <span class="block text-[11px] text-sky-600 font-medium">Bisa pilih foto tambahan baru</span>
                        </div>
                    </label>
                </div>

                <!-- 2. INFORMASI DASAR -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-black text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-sky-500"></i> Informasi Produk
                    </h2>

                    <!-- Nama Barang -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Barang / Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" value="{{ old('nama', $barang->nama) }}" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                    </div>

                    <!-- Kategori & Kondisi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select name="kategori_id" id="kategori_id" required onchange="handleKategoriChange()" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition bg-white">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id }}" data-nama="{{ strtolower($k->nama) }}" {{ old('kategori_id', $barang->kategori_id) == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Kondisi Fisik <span class="text-rose-500">*</span>
                            </label>
                            <select name="kondisi" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition bg-white">
                                <option value="Sangat Baik" {{ old('kondisi', $barang->kondisi) == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik (Mulus / Seperti Baru)</option>
                                <option value="Baik" {{ old('kondisi', $barang->kondisi) == 'Baik' ? 'selected' : '' }}>Baik (Normal & Berfungsi Penuh)</option>
                                <option value="Cukup" {{ old('kondisi', $barang->kondisi) == 'Cukup' ? 'selected' : '' }}>Cukup (Ada Minus Lecet Wajar)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deskripsi Barang -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Deskripsi & Kelengkapan Unit <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="deskripsi" rows="4" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition leading-relaxed">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- 2.5 SPESIFIKASI KHUSUS KATEGORI (DINAMIS) -->
                <!-- ============================================== -->

                <!-- PANEL 1: KOS / KAMAR -->
                <div id="panel-kos-kamar" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-sky-300 shadow-sm space-y-5 transition-all">
                    <div class="border-b border-sky-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-house-chimney-window"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Kos & Kamar</h3>
                                <p class="text-[11px] text-slate-400">Atur skema sewa bulanan/harian & fasilitas kamar kos</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-sky-50 text-sky-600 rounded-full text-[10px] font-black border border-sky-200">
                            Fitur Kos Profesional
                        </span>
                    </div>

                    <!-- Skema Sewa -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Skema Periode Sewa Kos <span class="text-rose-500">*</span>
                        </label>
                        @php $tipeSewa = old('spesifikasi.tipe_sewa', $spek['tipe_sewa'] ?? 'keduanya'); @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="keduanya" {{ $tipeSewa == 'keduanya' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
                                <div class="p-3 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 text-center transition">
                                    <div class="text-xs font-black text-slate-800"><i class="fa-solid fa-calendar-check text-sky-500 mr-1"></i> Harian & Bulanan</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Bisa harian dan bulanan</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="bulanan" {{ $tipeSewa == 'bulanan' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
                                <div class="p-3 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 text-center transition">
                                    <div class="text-xs font-black text-slate-800"><i class="fa-solid fa-calendar-days text-sky-500 mr-1"></i> Khusus Bulanan</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Sistem kos per bulan</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="spesifikasi[tipe_sewa]" value="harian" {{ $tipeSewa == 'harian' ? 'checked' : '' }} onchange="toggleKosPricing()" class="peer sr-only">
                                <div class="p-3 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 text-center transition">
                                    <div class="text-xs font-black text-slate-800"><i class="fa-solid fa-clock text-sky-500 mr-1"></i> Khusus Per Malam</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Penginapan harian</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Input Tarif Bulanan Kos -->
                    <div id="wrapper_harga_bulanan" class="p-4 rounded-2xl bg-sky-50/70 border border-sky-200 space-y-1.5">
                        <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            Tarif Sewa Per Bulan (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="spesifikasi[harga_bulanan]" id="input_harga_bulanan" value="{{ old('spesifikasi.harga_bulanan', $spek['harga_bulanan'] ?? '') }}" min="0" placeholder="Contoh: 1500000" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>

                    <!-- Tipe Kos, Kamar Mandi, Ukuran Kamar -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @php
                            $tipeKos = old('spesifikasi.tipe_kos', $spek['tipe_kos'] ?? 'Campur');
                            $tipeKM = old('spesifikasi.tipe_kamar_mandi', $spek['tipe_kamar_mandi'] ?? 'Dalam');
                        @endphp
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kos</label>
                            <select name="spesifikasi[tipe_kos]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500 bg-white">
                                <option value="Campur" {{ $tipeKos == 'Campur' ? 'selected' : '' }}>Kos Campur</option>
                                <option value="Putri" {{ $tipeKos == 'Putri' ? 'selected' : '' }}>Khusus Putri</option>
                                <option value="Putra" {{ $tipeKos == 'Putra' ? 'selected' : '' }}>Khusus Putra</option>
                                <option value="Pasutri" {{ $tipeKos == 'Pasutri' ? 'selected' : '' }}>Boleh Pasutri</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kamar Mandi</label>
                            <select name="spesifikasi[tipe_kamar_mandi]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500 bg-white">
                                <option value="Dalam" {{ $tipeKM == 'Dalam' ? 'selected' : '' }}>Kamar Mandi Dalam</option>
                                <option value="Luar" {{ $tipeKM == 'Luar' ? 'selected' : '' }}>Kamar Mandi Luar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Ukuran Kamar</label>
                            <input type="text" name="spesifikasi[ukuran_kamar]" value="{{ old('spesifikasi.ukuran_kamar', $spek['ukuran_kamar'] ?? '3 x 4 m') }}" placeholder="Contoh: 3 x 4 m" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-sky-500">
                        </div>
                    </div>

                    <!-- Fasilitas Kamar & Bersama -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Fasilitas Kamar & Kos
                        </label>
                        @php
                            $fasilitasKosList = [
                                ['AC', 'fa-snowflake'],
                                ['WiFi Cepat', 'fa-wifi'],
                                ['Kamar Mandi Dalam', 'fa-bath'],
                                ['Kasur & Springbed', 'fa-bed'],
                                ['Lemari Pakaian', 'fa-door-closed'],
                                ['Meja & Kursi Belajar', 'fa-chair'],
                                ['Water Heater', 'fa-temperature-arrow-up'],
                                ['Dapur Bersama', 'fa-utensils'],
                                ['Parkir Motor Aman', 'fa-motorcycle'],
                                ['Parkir Mobil', 'fa-car'],
                                ['CCTV 24 Jam', 'fa-video'],
                                ['Listrik Termasuk', 'fa-bolt'],
                            ];
                            $savedFasKos = (array)old('spesifikasi.fasilitas', $spek['fasilitas'] ?? []);
                        @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($fasilitasKosList as [$fas, $icon])
                            <label class="cursor-pointer relative flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-sky-300 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/70 transition">
                                <input type="checkbox" name="spesifikasi[fasilitas][]" value="{{ $fas }}" {{ in_array($fas, $savedFasKos) ? 'checked' : '' }} class="mr-2 rounded text-sky-500 focus:ring-sky-400">
                                <i class="fa-solid {{ $icon }} text-sky-500 mr-2 text-xs"></i>
                                <span class="text-xs font-semibold text-slate-700">{{ $fas }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- PANEL 2: KENDARAAN -->
                <div id="panel-kendaraan" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-cyan-300 shadow-sm space-y-5 transition-all">
                    <div class="border-b border-cyan-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-cyan-400 to-sky-600 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-car-side"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Rental Kendaraan</h3>
                                <p class="text-[11px] text-slate-400">Standar profesional rental mobil, motor, dan transportasi</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-cyan-50 text-cyan-600 rounded-full text-[10px] font-black border border-cyan-200">
                            Rental Kendaraan
                        </span>
                    </div>

                    @php
                        $tipeKendaraan = old('spesifikasi.tipe_kendaraan', $spek['tipe_kendaraan'] ?? 'Mobil');
                        $transmisi = old('spesifikasi.transmisi', $spek['transmisi'] ?? 'Automatic');
                        $bahanBakar = old('spesifikasi.bahan_bakar', $spek['bahan_bakar'] ?? 'Bensin');
                        $kapasitas = old('spesifikasi.kapasitas_penumpang', $spek['kapasitas_penumpang'] ?? '4-5 Kursi');
                        $opsiDriver = old('spesifikasi.opsi_driver', $spek['opsi_driver'] ?? 'Lepas Kunci Saja');
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kendaraan</label>
                            <select name="spesifikasi[tipe_kendaraan]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Mobil" {{ $tipeKendaraan == 'Mobil' ? 'selected' : '' }}>Mobil Penumpang</option>
                                <option value="Motor" {{ $tipeKendaraan == 'Motor' ? 'selected' : '' }}>Sepeda Motor</option>
                                <option value="Pickup / Box" {{ $tipeKendaraan == 'Pickup / Box' ? 'selected' : '' }}>Mobil Pickup / Box Niaga</option>
                                <option value="Sepeda" {{ $tipeKendaraan == 'Sepeda' ? 'selected' : '' }}>Sepeda Gowes / Listrik</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Transmisi</label>
                            <select name="spesifikasi[transmisi]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Automatic" {{ $transmisi == 'Automatic' ? 'selected' : '' }}>Automatic (Matic)</option>
                                <option value="Manual" {{ $transmisi == 'Manual' ? 'selected' : '' }}>Manual</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Bahan Bakar</label>
                            <select name="spesifikasi[bahan_bakar]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Bensin" {{ $bahanBakar == 'Bensin' ? 'selected' : '' }}>Bensin (Pertalite / Pertamax)</option>
                                <option value="Solar / Diesel" {{ $bahanBakar == 'Solar / Diesel' ? 'selected' : '' }}>Solar / Diesel</option>
                                <option value="Listrik (EV)" {{ $bahanBakar == 'Listrik (EV)' ? 'selected' : '' }}>Listrik (EV)</option>
                                <option value="Hybrid" {{ $bahanBakar == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kapasitas Kursi</label>
                            <select name="spesifikasi[kapasitas_penumpang]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="2 Orang" {{ $kapasitas == '2 Orang' ? 'selected' : '' }}>2 Orang (Motor/City Car)</option>
                                <option value="4-5 Kursi" {{ $kapasitas == '4-5 Kursi' ? 'selected' : '' }}>4 - 5 Kursi (Sedan/Hatchback)</option>
                                <option value="7-8 Kursi" {{ $kapasitas == '7-8 Kursi' ? 'selected' : '' }}>7 - 8 Kursi (MPV/SUV)</option>
                                <option value="12+ Kursi" {{ $kapasitas == '12+ Kursi' ? 'selected' : '' }}>12+ Kursi (HiAce / Elf)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Opsi Layanan Driver</label>
                            <select name="spesifikasi[opsi_driver]" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 bg-white">
                                <option value="Lepas Kunci Saja" {{ $opsiDriver == 'Lepas Kunci Saja' ? 'selected' : '' }}>Lepas Kunci Saja</option>
                                <option value="Dengan Driver / Supir" {{ $opsiDriver == 'Dengan Driver / Supir' ? 'selected' : '' }}>Dengan Driver / Supir</option>
                                <option value="Bisa Keduanya (Lepas Kunci / Driver)" {{ $opsiDriver == 'Bisa Keduanya (Lepas Kunci / Driver)' ? 'selected' : '' }}>Bisa Lepas Kunci / Dengan Driver</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Tahun Kendaraan</label>
                            <input type="number" name="spesifikasi[tahun_kendaraan]" value="{{ old('spesifikasi.tahun_kendaraan', $spek['tahun_kendaraan'] ?? '2023') }}" placeholder="2023" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                </div>

                <!-- PANEL 3: ELEKTRONIK -->
                <div id="panel-elektronik" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-blue-300 shadow-sm space-y-4 transition-all">
                    <div class="border-b border-blue-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-600 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Elektronik & Gear</h3>
                                <p class="text-[11px] text-slate-400">Kamera, laptop, gadget, drone, dan lighting studio</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black border border-blue-200">
                            Gear Elektronik
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Merek / Brand</label>
                            <input type="text" name="spesifikasi[merek]" value="{{ old('spesifikasi.merek', $spek['merek'] ?? '') }}" placeholder="Contoh: Sony, Canon, Apple, DJI" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kondisi Fungsi & Lensa</label>
                            <input type="text" name="spesifikasi[kondisi_detail]" value="{{ old('spesifikasi.kondisi_detail', $spek['kondisi_detail'] ?? 'Fungsi Normal 100%, Sensor Bersih') }}" placeholder="Normal 100%, Sensor Bersih" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- PANEL 4: OUTDOOR -->
                <div id="panel-outdoor" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-teal-300 shadow-sm space-y-4 transition-all">
                    <div class="border-b border-teal-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-campground"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Outdoor & Camping</h3>
                                <p class="text-[11px] text-slate-400">Tenda, sleeping bag, carrier, dan perlengkapan mendaki</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-teal-50 text-teal-600 rounded-full text-[10px] font-black border border-teal-200">
                            Outdoor Gear
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kapasitas / Ukuran</label>
                            <input type="text" name="spesifikasi[kapasitas_outdoor]" value="{{ old('spesifikasi.kapasitas_outdoor', $spek['kapasitas_outdoor'] ?? '4 Orang (Dome Tent)') }}" placeholder="Contoh: 4 Orang / 60 Liter" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Ketahanan Cuaca & Material</label>
                            <input type="text" name="spesifikasi[fitur_outdoor]" value="{{ old('spesifikasi.fitur_outdoor', $spek['fitur_outdoor'] ?? 'Waterproof PU 3000mm, Double Layer') }}" placeholder="Waterproof PU 3000mm, Double Layer" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-teal-500">
                        </div>
                    </div>
                </div>

                <!-- PANEL 5: PERALATAN HAVE FUN -->
                <div id="panel-have-fun" class="hidden bg-white p-5 sm:p-6 rounded-3xl border-2 border-indigo-300 shadow-sm space-y-4 transition-all">
                    <div class="border-b border-indigo-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-600 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-gamepad"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Spesifikasi Peralatan Have Fun</h3>
                                <p class="text-[11px] text-slate-400">PlayStation, karaoke set, sound system pesta, nobar gear</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black border border-indigo-200">
                            Have Fun & Party
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Jenis Paket Hiburan</label>
                            <input type="text" name="spesifikasi[jenis_hiburan]" value="{{ old('spesifikasi.jenis_hiburan', $spek['jenis_hiburan'] ?? 'Paket PS5 + 2 Stik + Game Lengkap') }}" placeholder="Contoh: Paket Karaoke Portable, PS5" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Kelengkapan Game / Aksesoris</label>
                            <input type="text" name="spesifikasi[kelengkapan_have_fun]" value="{{ old('spesifikasi.kelengkapan_have_fun', $spek['kelengkapan_have_fun'] ?? '2 Stik DualSense, 2 Mic Wireless, Kabel HDMI') }}" placeholder="2 Stik, 2 Mic Wireless, Kabel HDMI" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- 3. LOKASI GUDANG / PRODUK -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-black text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-sky-500"></i> Titik Lokasi Gudang & Opsi Antar
                    </h2>

                    <!-- Pilihan Layanan Antar Kurir -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Layanan Antar ke Penyewa (Kurir Toko) <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer relative">
                                <input type="radio" name="is_delivery_supported" value="1" {{ old('is_delivery_supported', $barang->is_delivery_supported) == '1' ? 'checked' : '' }} class="peer sr-only">
                                <div class="p-3.5 rounded-2xl border-2 border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/50 flex items-center gap-3 transition">
                                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-sm font-bold">
                                        <i class="fa-solid fa-motorcycle"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">Sedia Kurir Antar</p>
                                        <p class="text-[11px] text-slate-500">Dihitung otomatis per KM</p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer relative">
                                <input type="radio" name="is_delivery_supported" value="0" {{ old('is_delivery_supported', $barang->is_delivery_supported) == '0' ? 'checked' : '' }} class="peer sr-only">
                                <div class="p-3.5 rounded-2xl border-2 border-slate-200 peer-checked:border-rose-400 peer-checked:bg-rose-50/50 flex items-center gap-3 transition">
                                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-sm font-bold">
                                        <i class="fa-solid fa-person-walking"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">Ambil Mandiri</p>
                                        <p class="text-[11px] text-slate-500">Penyewa datang ke toko</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Alamat Input -->
                    <div>
                        <input type="hidden" name="latitude" id="lat_produk" value="{{ old('latitude', $barang->latitude) }}">
                        <input type="hidden" name="longitude" id="lon_produk" value="{{ old('longitude', $barang->longitude) }}">

                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat Lengkap Toko / Gudang Penjemputan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat" id="alamat_produk" rows="2" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-slate-800 text-xs font-medium focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">{{ old('alamat', $barang->alamat) }}</textarea>
                    </div>
                </div>

            </div>

            <!-- ============================================== -->
            <!-- KOLOM KANAN (1 SPAN): HARGA, STOK & SUBMIT -->
            <!-- ============================================== -->
            <div class="space-y-6">
                
                <!-- Harga & Ketentuan Keuangan -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-black text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-money-bill-wave text-emerald-500"></i> Tarif & Stok
                    </h2>

                    <!-- Harga Sewa / Hari -->
                    <div>
                        <label id="label_harga_harian" class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Tarif Sewa / Hari <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="harga_sewa_harian" id="harga_sewa_harian" value="{{ old('harga_sewa_harian', (int)$barang->harga_sewa_harian) }}" required min="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                        <p id="hint_harga_harian" class="text-[10px] text-slate-400 mt-1">Tarif dasar harian untuk sewa barang.</p>
                    </div>

                    <!-- Stok Unit Fisik -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Jumlah Stok Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="stok_total" value="{{ old('stok_total', $barang->stok_total) }}" required min="1" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-black focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                    </div>

                    <!-- Deposit Jaminan -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Deposit Jaminan (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="deposit" value="{{ old('deposit', (int)$barang->deposit) }}" required min="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>

                    <!-- Denda Keterlambatan -->
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                            Denda Telat / Hari <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="denda_per_hari" value="{{ old('denda_per_hari', (int)$barang->denda_per_hari) }}" required min="0" class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                    <button type="submit" id="btnSubmitBarang" class="w-full py-4 px-5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 active:scale-98 text-white font-black text-sm rounded-2xl shadow-lg shadow-sky-500/25 flex items-center justify-center gap-2 transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-base"></i>
                        <span>SIMPAN PERUBAHAN PRODUK</span>
                    </button>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    function handleKategoriChange() {
        const select = document.getElementById('kategori_id');
        if (!select) return;
        const selectedOption = select.options[select.selectedIndex];
        const katNama = (selectedOption ? (selectedOption.getAttribute('data-nama') || selectedOption.text) : '').toLowerCase();

        const panels = ['panel-kos-kamar', 'panel-kendaraan', 'panel-elektronik', 'panel-outdoor', 'panel-have-fun'];
        panels.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });

        const labelHarian = document.getElementById('label_harga_harian');
        const hintHarian = document.getElementById('hint_harga_harian');

        if (katNama.includes('kos') || katNama.includes('kamar')) {
            document.getElementById('panel-kos-kamar')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa / Malam (Harian) <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif per malam untuk penyewa harian.';
            toggleKosPricing();
        } else if (katNama.includes('kendaraan')) {
            document.getElementById('panel-kendaraan')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa Kendaraan / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif sewa per 24 jam (sesuai opsi lepas kunci / supir).';
        } else if (katNama.includes('elektronik')) {
            document.getElementById('panel-elektronik')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa Gear / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif sewa per hari untuk 1 paket kelengkapan unit.';
        } else if (katNama.includes('outdoor')) {
            document.getElementById('panel-outdoor')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa Alat Camping / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif per hari pendakian/camping.';
        } else if (katNama.includes('have fun')) {
            document.getElementById('panel-have-fun')?.classList.remove('hidden');
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa Paket Fun / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif sewa per hari untuk paket pesta & hiburan.';
        } else {
            if (labelHarian) labelHarian.innerHTML = 'Tarif Sewa / Hari <span class="text-rose-500">*</span>';
            if (hintHarian) hintHarian.innerText = 'Tarif dasar harian untuk sewa barang.';
        }
    }

    function toggleKosPricing() {
        const radios = document.getElementsByName('spesifikasi[tipe_sewa]');
        let selected = 'keduanya';
        for (let r of radios) {
            if (r.checked) { selected = r.value; break; }
        }

        const wrapperBulanan = document.getElementById('wrapper_harga_bulanan');
        const inputBulanan = document.getElementById('input_harga_bulanan');

        if (selected === 'bulanan') {
            if (wrapperBulanan) wrapperBulanan.style.display = 'block';
            if (inputBulanan) inputBulanan.required = true;
        } else if (selected === 'harian') {
            if (wrapperBulanan) wrapperBulanan.style.display = 'none';
            if (inputBulanan) { inputBulanan.required = false; }
        } else {
            if (wrapperBulanan) wrapperBulanan.style.display = 'block';
            if (inputBulanan) inputBulanan.required = true;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleKategoriChange();
    });
</script>
@endpush
