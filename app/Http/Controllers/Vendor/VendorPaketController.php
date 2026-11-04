<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\FotoBarang;
use App\Models\PaketSewaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\StorageService;
use App\Rules\ValidMedia;

class VendorPaketController extends Controller
{
    /**
     * Form pembuatan Paket Sewa baru
     */
    public function create()
    {
        $vendor = Auth::user();
        $vendorId = $vendor->id;

        // Ambil daftar barang satuan milik vendor yang sudah aktif
        $myProducts = Barang::where('vendor_id', $vendorId)
            ->where('is_paket', 0)
            ->orderBy('nama', 'asc')
            ->get();

        $kategoris = Kategori::where('is_active', 1)->get();

        return view('vendor.paket.create', compact('myProducts', 'kategoris', 'vendor'));
    }

    /**
     * Simpan Paket Sewa baru ke database
     */
    public function store(Request $request)
    {
        $vendor = Auth::user();
        $vendorId = $vendor->id;

        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'deskripsi' => 'required|string',
            'harga_sewa_harian' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'denda_per_hari' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.quantity' => 'required|integer|min:1',
            'fotos' => 'nullable|array',
            'fotos.*' => ['nullable', new ValidMedia(102400, true)],
            'alamat' => 'nullable|string',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
        ], [
            'items.required' => 'Pilih minimal 1 barang untuk menyusun paket sewa.',
            'items.min' => 'Pilih minimal 1 barang untuk menyusun paket sewa.',
        ]);

        // Validasi bahwa seluruh barang yang dipilih benar milik vendor ini dan bukan paket
        $selectedIds = collect($request->items)->pluck('barang_id')->unique()->toArray();
        $validItems = Barang::where('vendor_id', $vendorId)
            ->where('is_paket', 0)
            ->whereIn('id', $selectedIds)
            ->get()
            ->keyBy('id');

        if (count($selectedIds) !== $validItems->count()) {
            return back()->withInput()->withErrors(['items' => 'Barang yang dipilih tidak valid atau bukan milik toko Anda.']);
        }

        DB::beginTransaction();
        try {
            /** @var array<int, \Illuminate\Http\UploadedFile> $fotos */
            $fotos = $request->file('fotos', []);

            $pathCover = null;
            if (!empty($fotos) && isset($fotos[0]) && $fotos[0] instanceof \Illuminate\Http\UploadedFile) {
                $pathCover = StorageService::storeMedia($fotos[0], 'barang');
            } else {
                // Fallback: gunakan foto cover dari barang pertama dalam komposisi paket
                $firstItem = $validItems->first();
                $pathCover = $firstItem ? $firstItem->cover_photo : null;
            }

            // Lokasi fallback dari profil vendor atau barang pertama jika tidak diisi
            $alamat = $request->alamat ?: ($vendor->alamat_lengkap ?? $validItems->first()?->alamat ?? 'Alamat Toko');
            $latitude = $request->latitude ?: ($vendor->latitude ?? $validItems->first()?->latitude ?? 0);
            $longitude = $request->longitude ?: ($vendor->longitude ?? $validItems->first()?->longitude ?? 0);

            // Buat entri Barang untuk Paket Sewa
            $paket = Barang::create([
                'vendor_id' => $vendorId,
                'kategori_id' => $request->kategori_id,
                'nama' => $request->nama,
                'slug' => Str::slug($request->nama) . '-' . rand(1000, 9999),
                'deskripsi' => $request->deskripsi,
                'harga_sewa_harian' => $request->harga_sewa_harian,
                'deposit' => $request->deposit ?? 0,
                'denda_per_hari' => $request->denda_per_hari ?? 0,
                'kondisi' => 'Sangat Baik',
                'stok_total' => 0, // Dihitung dinamis via getStokTotalAttribute
                'status' => 'tersedia',
                'status_barang' => 'disetujui',
                'is_approved' => 1,
                'approved_at' => now(),
                'is_paket' => 1,
                'cover_photo' => $pathCover,
                'is_delivery_supported' => $request->is_delivery_supported ? 1 : 0,
                'alamat' => $alamat,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'spesifikasi' => [
                    'tipe' => 'paket_sewa',
                    'catatan' => 'Paket bundling resmi vendor',
                ],
            ]);

            // Simpan foto galeri tambahan jika ada diunggah
            if (count($fotos) > 1) {
                for ($i = 1; $i < count($fotos); $i++) {
                    $foto = $fotos[$i];
                    if ($foto instanceof \Illuminate\Http\UploadedFile) {
                        $pathGaleri = StorageService::storeMedia($foto, 'barang/galeri');
                        FotoBarang::create([
                            'barang_id' => $paket->id,
                            'foto_path' => $pathGaleri,
                        ]);
                    }
                }
            }

            // Simpan komposisi barang penyusun paket
            foreach ($request->items as $itemData) {
                $barangId = (int) $itemData['barang_id'];
                $qty = max(1, (int) $itemData['quantity']);

                PaketSewaItem::create([
                    'paket_id' => $paket->id,
                    'barang_id' => $barangId,
                    'quantity' => $qty,
                ]);
            }

            DB::commit();

            return redirect()->route('vendor.barang.index', ['tipe' => 'paket'])
                ->with('success', "Paket sewa '{$paket->nama}' berhasil dibuat!");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('[VendorPaketController@store] Gagal menyimpan paket: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['error' => 'Gagal membuat paket sewa: ' . $e->getMessage()]);
        }
    }

    /**
     * Form edit Paket Sewa
     */
    public function edit($id)
    {
        $vendor = Auth::user();
        $paket = Barang::with(['paketItems.barang', 'fotos'])
            ->where('vendor_id', $vendor->id)
            ->where('is_paket', 1)
            ->findOrFail($id);

        $myProducts = Barang::where('vendor_id', $vendor->id)
            ->where('is_paket', 0)
            ->orderBy('nama', 'asc')
            ->get();

        $kategoris = Kategori::where('is_active', 1)->get();

        return view('vendor.paket.edit', compact('paket', 'myProducts', 'kategoris', 'vendor'));
    }

    /**
     * Perbarui Paket Sewa dan komposisinya
     */
    public function update(Request $request, $id)
    {
        $vendor = Auth::user();
        $paket = Barang::where('vendor_id', $vendor->id)
            ->where('is_paket', 1)
            ->findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'deskripsi' => 'required|string',
            'harga_sewa_harian' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'denda_per_hari' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.quantity' => 'required|integer|min:1',
            'fotos' => 'nullable|array',
            'fotos.*' => ['nullable', new ValidMedia(102400, true)],
            'alamat' => 'nullable|string',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
        ]);

        $selectedIds = collect($request->items)->pluck('barang_id')->unique()->toArray();
        $validItems = Barang::where('vendor_id', $vendor->id)
            ->where('is_paket', 0)
            ->whereIn('id', $selectedIds)
            ->get()
            ->keyBy('id');

        if (count($selectedIds) !== $validItems->count()) {
            return back()->withInput()->withErrors(['items' => 'Barang yang dipilih tidak valid atau bukan milik toko Anda.']);
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'nama' => $request->nama,
                'kategori_id' => $request->kategori_id,
                'deskripsi' => $request->deskripsi,
                'harga_sewa_harian' => $request->harga_sewa_harian,
                'deposit' => $request->deposit ?? 0,
                'denda_per_hari' => $request->denda_per_hari ?? 0,
                'is_delivery_supported' => $request->is_delivery_supported ? 1 : 0,
            ];

            if ($request->filled('alamat')) {
                $updateData['alamat'] = $request->alamat;
            }
            if ($request->filled('latitude')) {
                $updateData['latitude'] = $request->latitude;
            }
            if ($request->filled('longitude')) {
                $updateData['longitude'] = $request->longitude;
            }

            /** @var array<int, \Illuminate\Http\UploadedFile> $fotos */
            $fotos = $request->file('fotos', []);
            if (!empty($fotos) && isset($fotos[0]) && $fotos[0] instanceof \Illuminate\Http\UploadedFile) {
                $pathCover = StorageService::storeMedia($fotos[0], 'barang');
                if ($paket->cover_photo && $paket->cover_photo !== $pathCover) {
                    StorageService::deleteFile($paket->cover_photo);
                }
                $updateData['cover_photo'] = $pathCover;
            }

            $paket->update($updateData);

            // Simpan foto galeri tambahan jika ada diunggah
            if (count($fotos) > 1) {
                for ($i = 1; $i < count($fotos); $i++) {
                    $foto = $fotos[$i];
                    if ($foto instanceof \Illuminate\Http\UploadedFile) {
                        $pathGaleri = StorageService::storeMedia($foto, 'barang/galeri');
                        FotoBarang::create([
                            'barang_id' => $paket->id,
                            'foto_path' => $pathGaleri,
                        ]);
                    }
                }
            }

            // Sinkronisasi ulang komposisi paket: hapus item lama, masukkan komposisi baru
            PaketSewaItem::where('paket_id', $paket->id)->delete();
            foreach ($request->items as $itemData) {
                $barangId = (int) $itemData['barang_id'];
                $qty = max(1, (int) $itemData['quantity']);

                PaketSewaItem::create([
                    'paket_id' => $paket->id,
                    'barang_id' => $barangId,
                    'quantity' => $qty,
                ]);
            }

            DB::commit();

            return redirect()->route('vendor.barang.index', ['tipe' => 'paket'])
                ->with('success', "Paket sewa '{$paket->nama}' berhasil diperbarui!");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('[VendorPaketController@update] Gagal memperbarui paket: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui paket sewa: ' . $e->getMessage()]);
        }
    }

    /**
     * Hapus Paket Sewa
     */
    public function destroy($id)
    {
        $vendor = Auth::user();
        $paket = Barang::where('vendor_id', $vendor->id)
            ->where('is_paket', 1)
            ->findOrFail($id);

        $nama = $paket->nama;
        $paket->delete(); // Otomatis cascade delete di paket_sewa_items

        return redirect()->route('vendor.barang.index')
            ->with('success', "Paket sewa '{$nama}' berhasil dihapus.");
    }
}
