<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\FotoBarang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\StorageService;
use App\Rules\ValidMedia;

class AdminBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengarahkan ke tab Inventaris Produk di Dashboard Utama.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $query = Barang::with(['vendor', 'kategori', 'fotos'])->latest();
            if ($request->filled('status_barang')) {
                $query->where('status_barang', $request->status_barang);
            }
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('nama', 'like', '%' . $request->search . '%')
                      ->orWhereHas('vendor', function($v) use ($request) {
                          $v->where('vendor_name', 'like', '%' . $request->search . '%')
                            ->orWhere('name', 'like', '%' . $request->search . '%');
                      });
                });
            }
            return response()->json($query->paginate(20));
        }

        return redirect()->route('admin.dashboard');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = Kategori::where('is_active', 1)->get();
        $vendors = User::where('role', 'vendor')->orderBy('vendor_name')->get();
        return view('admin.barang.create', compact('kategoris', 'vendors'));
    }

    /**
     * Store a newly created resource in storage (God-Mode create by Admin).
     */
    public function store(Request $request)
    {
        if (empty($request->harga_sewa_harian) && !empty($request->input('spesifikasi.harga_bulanan'))) {
            $request->merge([
                'harga_sewa_harian' => round(((float)$request->input('spesifikasi.harga_bulanan')) / 30)
            ]);
        }

        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'kategori_id' => 'required|exists:kategoris,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa_harian' => 'required|numeric|min:0',
            'deposit' => 'required|numeric|min:0',
            'denda_per_hari' => 'required|numeric|min:0',
            'kondisi' => 'required|string',
            'stok_total' => 'required|integer|min:0',
            'alamat' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'fotos' => 'required|array|min:1',
            'fotos.*' => ['required', new ValidMedia(102400, true)],
        ]);

        /** @var array<int, \Illuminate\Http\UploadedFile> $fotos */
        $fotos = $request->file('fotos', []);
        $fileCover = $fotos[0] ?? null;
        if (!$fileCover instanceof \Illuminate\Http\UploadedFile) {
            return back()->withErrors(['fotos' => 'Foto cover wajib diunggah.']);
        }

        $pathCover = StorageService::storeMedia($fileCover, 'barang');

        $barang = Barang::create([
            'vendor_id' => $request->vendor_id,
            'kategori_id' => $request->kategori_id,
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama) . '-' . rand(1000, 9999),
            'deskripsi' => $request->deskripsi,
            'harga_sewa_harian' => $request->harga_sewa_harian,
            'deposit' => $request->deposit,
            'denda_per_hari' => $request->denda_per_hari,
            'kondisi' => $request->kondisi,
            'stok_total' => $request->stok_total,
            'status' => $request->input('status', 'tersedia'),
            'status_barang' => $request->input('status_barang', 'disetujui'),
            'is_approved' => $request->input('status_barang', 'disetujui') === 'disetujui' ? 1 : 0,
            'approved_at' => $request->input('status_barang', 'disetujui') === 'disetujui' ? now() : null,
            'cover_photo' => $pathCover,
            'is_delivery_supported' => $request->boolean('is_delivery_supported') ? 1 : 0,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'spesifikasi' => $request->input('spesifikasi', []),
        ]);

        if (count($fotos) > 1) {
            for ($i = 1; $i < count($fotos); $i++) {
                $foto = $fotos[$i];
                if ($foto instanceof \Illuminate\Http\UploadedFile) {
                    $pathGaleri = StorageService::storeMedia($foto, 'barang/galeri');
                    FotoBarang::create([
                        'barang_id' => $barang->id,
                        'foto_path' => $pathGaleri
                    ]);
                }
            }
        }

        return redirect()->route('admin.dashboard')->with('success', "Produk '{$barang->nama}' berhasil ditambahkan langsung oleh Admin.");
    }

    /**
     * Display the specified resource in full details (100% data inspeksi).
     */
    public function show(Request $request, string $id)
    {
        $barang = Barang::with(['vendor', 'kategori', 'fotos'])->findOrFail($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $barang
            ]);
        }

        return view('admin.barang.show', compact('barang'));
    }

    /**
     * Show the form for editing the specified resource (God-Mode Edit).
     */
    public function edit(string $id)
    {
        $barang = Barang::with(['vendor', 'kategori', 'fotos'])->findOrFail($id);
        $kategoris = Kategori::where('is_active', 1)->get();
        $vendors = User::where('role', 'vendor')->orderBy('vendor_name')->get();

        return view('admin.barang.edit', compact('barang', 'kategoris', 'vendors'));
    }

    /**
     * Update the specified resource in storage (God-Mode Update by Admin).
     */
    public function update(Request $request, string $id)
    {
        $barang = Barang::findOrFail($id);

        if (empty($request->harga_sewa_harian) && !empty($request->input('spesifikasi.harga_bulanan'))) {
            $request->merge([
                'harga_sewa_harian' => round(((float)$request->input('spesifikasi.harga_bulanan')) / 30)
            ]);
        }

        $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'vendor_id' => 'sometimes|exists:users,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa_harian' => 'required|numeric|min:0',
            'deposit' => 'required|numeric|min:0',
            'denda_per_hari' => 'required|numeric|min:0',
            'kondisi' => 'required|string',
            'stok_total' => 'required|integer|min:0',
            'alamat' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'status_barang' => 'required|in:pending,disetujui,ditolak',
            'status' => 'required|in:tersedia,tidak_tersedia,disewa',
            'cover_photo' => ['nullable', new ValidMedia(102400, true)],
            'fotos' => 'nullable|array',
            'fotos.*' => ['nullable', new ValidMedia(102400, true)],
            'delete_fotos' => 'nullable|array',
            'delete_fotos.*' => 'integer|exists:foto_barangs,id',
        ]);

        $statusApproval = $request->status_barang;
        $isApproved = ($statusApproval === 'disetujui') ? 1 : 0;
        $approvedAt = ($statusApproval === 'disetujui') ? ($barang->approved_at ?? now()) : null;

        $updateData = [
            'kategori_id' => $request->kategori_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'harga_sewa_harian' => $request->harga_sewa_harian,
            'deposit' => $request->deposit,
            'denda_per_hari' => $request->denda_per_hari,
            'kondisi' => $request->kondisi,
            'stok_total' => $request->stok_total,
            'status' => $request->status,
            'status_barang' => $statusApproval,
            'is_approved' => $isApproved,
            'approved_at' => $approvedAt,
            'is_delivery_supported' => $request->boolean('is_delivery_supported') ? 1 : 0,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'spesifikasi' => $request->input('spesifikasi', []),
        ];

        if ($request->filled('vendor_id')) {
            $updateData['vendor_id'] = $request->vendor_id;
        }

        // 1. Cover Photo baru jika diunggah
        if ($request->hasFile('cover_photo')) {
            $fileCover = $request->file('cover_photo');
            if ($fileCover instanceof \Illuminate\Http\UploadedFile) {
                $pathCover = StorageService::storeMedia($fileCover, 'barang');
                if ($barang->cover_photo && $barang->cover_photo !== $pathCover) {
                    StorageService::deleteFile($barang->cover_photo);
                }
                $updateData['cover_photo'] = $pathCover;
            }
        }

        // 2. Foto Galeri Tambahan baru jika diunggah
        if ($request->hasFile('fotos')) {
            /** @var array<int, \Illuminate\Http\UploadedFile> $newFotos */
            $newFotos = $request->file('fotos', []);
            foreach ($newFotos as $fotoItem) {
                if ($fotoItem instanceof \Illuminate\Http\UploadedFile) {
                    $pathGaleri = StorageService::storeMedia($fotoItem, 'barang/galeri');
                    FotoBarang::create([
                        'barang_id' => $barang->id,
                        'foto_path' => $pathGaleri
                    ]);
                }
            }
        }

        // 3. Hapus foto galeri tertentu jika dipilih
        if ($request->filled('delete_fotos')) {
            foreach ($request->delete_fotos as $fotoId) {
                $foto = FotoBarang::where('barang_id', $barang->id)->find($fotoId);
                if ($foto) {
                    $foto->delete(); // Boot method removes file from storage/Cloudinary
                }
            }
        }

        $barang->update($updateData);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data produk '{$barang->nama}' berhasil diperbarui oleh Admin (God-Mode)!",
                'data' => $barang->fresh(['vendor', 'kategori', 'fotos'])
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', "Data produk '{$barang->nama}' berhasil diperbarui dengan otoritas Admin penuh.");
    }

    /**
     * Quick Stock adjustment langsung dari Dashboard Admin.
     */
    public function quickStock(Request $request, string $id)
    {
        $request->validate([
            'stok_total' => 'required|integer|min:0',
            'status' => 'nullable|string|in:tersedia,tidak_tersedia,disewa',
        ]);

        $barang = Barang::findOrFail($id);
        $oldStock = $barang->stok_total;
        $barang->stok_total = (int) $request->stok_total;

        if ($request->filled('status')) {
            $barang->status = $request->status;
        } elseif ($barang->stok_total == 0 && $barang->status === 'tersedia') {
            // Jika stok diset 0, tanyakan atau biarkan tersedia/habis
        }

        $barang->save();

        $msg = "Stok '{$barang->nama}' berhasil diubah dari {$oldStock} menjadi {$barang->stok_total} unit.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'new_stock' => $barang->stok_total,
                'status' => $barang->status
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Hapus foto galeri spesifik dari produk.
     */
    public function destroyFoto(string $barangId, string $fotoId)
    {
        $foto = FotoBarang::where('barang_id', $barangId)->findOrFail($fotoId);
        $foto->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Foto galeri berhasil dihapus.'
            ]);
        }

        return redirect()->back()->with('success', 'Foto galeri produk berhasil dihapus.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $barang = Barang::findOrFail($id);
        $nama = $barang->nama;
        $barang->delete(); // Model deleting event cleans up files

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Produk '{$nama}' telah dihapus permanen oleh Admin."
            ]);
        }

        return redirect()->back()->with('success', "Produk '{$nama}' berhasil dihapus secara permanen dari sistem.");
    }
}
