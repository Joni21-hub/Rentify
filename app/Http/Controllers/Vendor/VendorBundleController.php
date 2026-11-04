<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Cabang;
use App\Models\PaketSewa;
use App\Models\PaketSewaItem;
use App\Services\BundleInventoryService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorBundleController extends Controller
{
    protected BundleInventoryService $inventoryService;

    public function __construct(BundleInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Requirement 1.1:
     * Daftar paket sewa milik vendor.
     */
    public function index(Request $request)
    {
        $vendorId = Auth::id();
        $bundles = PaketSewa::where('vendor_id', $vendorId)
            ->with(['cabang', 'items.barang'])
            ->latest()
            ->paginate(15);

        if ($request->wantsJson() || $request->is('api/*')) {
            return Response::json([
                'status'  => 'success',
                'data'    => $bundles,
            ]);
        }

        if (View::exists('vendor.bundle.index')) {
            return View::make('vendor.bundle.index', compact('bundles'));
        }

        return Response::json([
            'status'  => 'success',
            'data'    => $bundles,
        ]);
    }

    /**
     * Requirement 1.1:
     * Form pembuatan paket sewa baru.
     */
    public function create()
    {
        $vendor = Auth::user();
        $cabangs = Cabang::where('vendor_id', $vendor->id)->get();
        $availableItems = Barang::where('vendor_id', $vendor->id)
            ->where('is_approved', 1)
            ->where('status', 'tersedia')
            ->orderBy('nama')
            ->get();

        if (View::exists('vendor.bundle.create')) {
            return View::make('vendor.bundle.create', compact('cabangs', 'availableItems', 'vendor'));
        }

        return Response::json([
            'cabangs'        => $cabangs,
            'availableItems' => $availableItems,
        ]);
    }

    /**
     * Requirement 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7:
     * Menyimpan paket sewa baru beserta komponen penyusunnya.
     */
    public function store(Request $request)
    {
        $vendorId = Auth::id();

        $validated = $this->validateBundleRequest($request, $vendorId);

        DB::beginTransaction();
        try {
            // Tangani cover photo
            $coverPath = null;
            if ($request->hasFile('cover_photo')) {
                $file = $request->file('cover_photo');
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $coverPath = StorageService::storeMedia($file, 'paket_sewa');
                }
            } elseif ($request->filled('cover_photo')) {
                $coverPath = (string) $request->cover_photo;
            } else {
                // Fallback ke cover photo barang pertama
                $firstBarang = $validated['items_models']->first();
                $coverPath = $firstBarang?->cover_photo;
            }

            // Slug unik
            $baseSlug = Str::slug($request->nama);
            $slug = $baseSlug . '-' . Str::lower(Str::random(6));

            // Simpan header paket_sewa
            $bundle = PaketSewa::create([
                'vendor_id'               => $vendorId,
                'cabang_id'               => $validated['cabang_id'],
                'nama'                    => $request->nama,
                'slug'                    => $slug,
                'deskripsi'               => $request->deskripsi,
                'cover_photo'             => $coverPath,
                'harga_sewa_harian_paket' => $validated['harga_sewa_harian_paket'],
                'status'                  => 'tersedia',
                'is_delivery_supported'   => $validated['is_delivery_supported'],
            ]);

            // Simpan item-item penyusun ke paket_sewa_items
            foreach ($validated['items_input'] as $itemData) {
                PaketSewaItem::create([
                    'paket_sewa_id' => $bundle->id,
                    'barang_id'     => $itemData['barang_id'],
                    'quantity'      => $itemData['quantity'],
                ]);
            }

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return Response::json([
                    'status'  => 'success',
                    'message' => 'Paket sewa berhasil dibuat.',
                    'data'    => $bundle->load('items.barang'),
                ], 201);
            }

            return Redirect::route('vendor.bundle.index')
                ->with('success', 'Paket sewa berhasil dibuat.');

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Requirement 1.1:
     * Form edit paket sewa.
     */
    public function edit(int $id)
    {
        $vendorId = Auth::id();
        $bundle = PaketSewa::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->with(['cabang', 'items.barang'])
            ->firstOrFail();

        $cabangs = Cabang::where('vendor_id', $vendorId)->get();
        $availableItems = Barang::where('vendor_id', $vendorId)
            ->where('is_approved', 1)
            ->where('status', 'tersedia')
            ->orderBy('nama')
            ->get();

        if (View::exists('vendor.bundle.edit')) {
            return View::make('vendor.bundle.edit', compact('bundle', 'cabangs', 'availableItems'));
        }

        return Response::json([
            'bundle'         => $bundle,
            'cabangs'        => $cabangs,
            'availableItems' => $availableItems,
        ]);
    }

    /**
     * Requirement 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7:
     * Memperbarui paket sewa dan relasi item penyusunnya.
     */
    public function update(Request $request, int $id)
    {
        $vendorId = Auth::id();
        $bundle = PaketSewa::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->firstOrFail();

        $validated = $this->validateBundleRequest($request, $vendorId);

        DB::beginTransaction();
        try {
            // Update cover photo jika diunggah baru
            if ($request->hasFile('cover_photo')) {
                $file = $request->file('cover_photo');
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $bundle->cover_photo = StorageService::storeMedia($file, 'paket_sewa');
                }
            } elseif ($request->filled('cover_photo')) {
                $bundle->cover_photo = (string) $request->cover_photo;
            }

            $bundle->nama                    = $request->nama;
            $bundle->cabang_id               = $validated['cabang_id'];
            $bundle->deskripsi               = $request->deskripsi ?? $bundle->deskripsi;
            $bundle->harga_sewa_harian_paket = $validated['harga_sewa_harian_paket'];
            $bundle->is_delivery_supported   = $validated['is_delivery_supported'];
            $bundle->save();

            // Sync items: hapus item lama, sisipkan item baru
            PaketSewaItem::where('paket_sewa_id', $bundle->id)->delete();
            foreach ($validated['items_input'] as $itemData) {
                PaketSewaItem::create([
                    'paket_sewa_id' => $bundle->id,
                    'barang_id'     => $itemData['barang_id'],
                    'quantity'      => $itemData['quantity'],
                ]);
            }

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return Response::json([
                    'status'  => 'success',
                    'message' => 'Paket sewa berhasil diperbarui.',
                    'data'    => $bundle->fresh()->load('items.barang'),
                ]);
            }

            return Redirect::route('vendor.bundle.index')
                ->with('success', 'Paket sewa berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Requirement 1.7:
     * Toggle status paket sewa antara 'tersedia' dan 'tidak_aktif'.
     * Menonaktifkan visibilitas di marketplace tanpa memodifikasi barang penyusun.
     */
    public function toggleStatus(Request $request, int $id)
    {
        $vendorId = Auth::id();
        $bundle = PaketSewa::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->firstOrFail();

        $bundle->status = ($bundle->status === 'tersedia') ? 'tidak_aktif' : 'tersedia';
        $bundle->save();

        if ($request->wantsJson() || $request->is('api/*')) {
            return Response::json([
                'status'  => 'success',
                'message' => "Status paket berhasil diubah menjadi {$bundle->status}.",
                'data'    => [
                    'id'     => $bundle->id,
                    'status' => $bundle->status,
                ],
            ]);
        }

        return Redirect::back()->with('success', "Status paket sewa berhasil diubah menjadi {$bundle->status}.");
    }

    /**
     * Requirement 1.1:
     * Menghapus paket sewa.
     */
    public function destroy(Request $request, int $id)
    {
        $vendorId = Auth::id();
        $bundle = PaketSewa::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->firstOrFail();

        $bundle->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return Response::json([
                'status'  => 'success',
                'message' => 'Paket sewa berhasil dihapus.',
            ]);
        }

        return Redirect::route('vendor.bundle.index')
            ->with('success', 'Paket sewa berhasil dihapus.');
    }

    /**
     * Validasi mendalam aturan bisnis paket sewa vendor.
     *
     * @param Request $request
     * @param int $vendorId
     * @return array
     * @throws ValidationException
     */
    protected function validateBundleRequest(Request $request, int $vendorId): array
    {
        $request->validate([
            'nama'                    => 'required|string|max:255',
            'cabang_id'               => 'required|exists:cabangs,id',
            'harga_sewa_harian_paket' => 'nullable|numeric|min:0',
            'harga_sewa_harian'       => 'nullable|numeric|min:0',
            'deskripsi'               => 'nullable|string',
            'items'                   => 'required|array',
            'items.*.barang_id'       => 'nullable|integer',
            'items.*.id'              => 'nullable|integer',
            'items.*.quantity'        => 'nullable|integer|min:1',
        ], [
            'items.required' => 'Pilih minimal 2 item untuk menyusun paket sewa.',
        ]);

        $hargaSewa = $request->harga_sewa_harian_paket ?? $request->harga_sewa_harian;
        if ($hargaSewa === null || $hargaSewa < 0) {
            throw ValidationException::withMessages([
                'harga_sewa_harian_paket' => 'Harga sewa harian paket wajib diisi dan bernilai non-negatif.',
            ]);
        }

        // Verifikasi kepemilikan cabang oleh vendor
        $cabang = Cabang::where('id', $request->cabang_id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$cabang) {
            throw ValidationException::withMessages([
                'cabang_id' => 'Cabang yang dipilih tidak valid atau bukan milik toko Anda.',
            ]);
        }

        // Ekstraksi dan sanitasi items
        $itemsInput = [];
        $rawItems = (array) $request->items;
        foreach ($rawItems as $item) {
            $barangId = $item['barang_id'] ?? $item['id'] ?? null;
            $qty = isset($item['quantity']) ? max(1, (int) $item['quantity']) : 1;
            if ($barangId) {
                $itemsInput[] = [
                    'barang_id' => (int) $barangId,
                    'quantity'  => $qty,
                ];
            }
        }

        // Aturan 1.3: Minimal 2 item berbeda
        $barangIds = array_column($itemsInput, 'barang_id');
        if (count($itemsInput) < 2 || count($barangIds) < 2) {
            throw ValidationException::withMessages([
                'items' => 'Paket sewa harus memiliki minimal 2 item berbeda.',
            ]);
        }

        // Aturan 1.3: Tidak boleh ada ID barang duplikat
        if (count($barangIds) !== count(array_unique($barangIds))) {
            throw ValidationException::withMessages([
                'items' => 'Item penyusun paket sewa tidak boleh duplikat.',
            ]);
        }

        $uniqueIds = array_values(array_unique($barangIds));
        $itemsModels = Barang::whereIn('id', $uniqueIds)->get()->keyBy('id');

        if ($itemsModels->count() !== count($uniqueIds)) {
            throw ValidationException::withMessages([
                'items' => 'Satu atau lebih item penyusun tidak ditemukan di basis data.',
            ]);
        }

        // Aturan 1.2: Kepemilikan vendor, status aktif ('tersedia'), dan persetujuan admin (is_approved == 1)
        foreach ($itemsModels as $item) {
            if ((int) $item->vendor_id !== (int) $vendorId) {
                throw ValidationException::withMessages([
                    'items' => 'Seluruh item penyusun paket harus milik toko Anda sendiri.',
                ]);
            }

            if (!$item->is_approved) {
                throw ValidationException::withMessages([
                    'items' => "Item '{$item->nama}' belum disetujui oleh admin.",
                ]);
            }

            if ($item->status !== 'tersedia') {
                throw ValidationException::withMessages([
                    'items' => "Item '{$item->nama}' sedang tidak aktif atau tidak tersedia.",
                ]);
            }

            // Aturan 1.4: Kesamaan cabang fisik (barang.cabang_id === paket_sewa.cabang_id)
            if ($item->cabang_id && (int) $item->cabang_id !== (int) $request->cabang_id) {
                throw ValidationException::withMessages([
                    'items' => "Item '{$item->nama}' berada di cabang yang berbeda dengan cabang paket sewa.",
                ]);
            }
        }

        // Aturan 1.5: Verifikasi kesesuaian koordinat GPS dan alamat penjemputan fisik
        $firstItem = $itemsModels->first();
        foreach ($itemsModels as $item) {
            $diffLat = abs((float) ($item->latitude ?? 0) - (float) ($firstItem->latitude ?? 0));
            $diffLng = abs((float) ($item->longitude ?? 0) - (float) ($firstItem->longitude ?? 0));
            $alamatA = trim(strtolower((string) ($item->alamat ?? '')));
            $alamatB = trim(strtolower((string) ($firstItem->alamat ?? '')));

            if ($diffLat > 0.0001 || $diffLng > 0.0001 || ($alamatA !== '' && $alamatB !== '' && $alamatA !== $alamatB)) {
                throw ValidationException::withMessages([
                    'items' => 'Seluruh item penyusun harus memiliki koordinat GPS dan alamat penjemputan fisik yang identik.',
                ]);
            }
        }

        // Aturan 1.6 & 4.1: Warisan dukungan pengiriman (delivery support inheritance)
        $isDeliverySupported = $this->inventoryService->resolveDeliverySupport($uniqueIds);

        return [
            'cabang_id'               => (int) $request->cabang_id,
            'harga_sewa_harian_paket' => (float) $hargaSewa,
            'items_input'             => $itemsInput,
            'items_models'            => $itemsModels,
            'is_delivery_supported'   => $isDeliverySupported,
        ];
    }
}
