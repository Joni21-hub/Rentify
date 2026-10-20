<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Voucher;
use App\Services\AiMarketingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VendorAiMarketingController extends Controller
{
    protected AiMarketingService $aiService;

    public function __construct(AiMarketingService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Dashboard AI Marketing & Auto-Promosi Vendor
     */
    public function index()
    {
        $vendorId = (int)Auth::id();
        $products = Barang::where('vendor_id', $vendorId)
            ->with(['kategori'])
            ->orderBy('nama', 'asc')
            ->get();

        $activeVouchers = Voucher::where('vendor_id', $vendorId)
            ->where('is_active', 1)
            ->latest()
            ->take(5)
            ->get();

        // Jalankan Audit Stok & Rekomendasi Auto-Promosi Cerdas
        $auditResult = $this->aiService->auditAndRecommendPromos($vendorId);
        $isAiOnline = $this->aiService->isAiEnabled();

        return view('vendor.ai-marketing.index', compact('products', 'activeVouchers', 'auditResult', 'isAiOnline'));
    }

    /**
     * Generate Copywriting Iklan / Konten Promosi via AI
     */
    public function generateCopy(Request $request)
    {
        $request->validate([
            'barang_id'   => 'nullable|exists:barang,id',
            'platform'    => 'required|in:all,whatsapp,instagram,tiktok,banner',
            'tone'        => 'required|in:fomo,casual,professional,solution',
            'diskon'      => 'nullable|string|max:100',
            'custom_note' => 'nullable|string|max:255',
        ]);

        $vendorId = Auth::id();
        $productData = [];

        if ($request->filled('barang_id')) {
            $barang = Barang::where('vendor_id', $vendorId)
                ->with('kategori')
                ->findOrFail($request->barang_id);

            $productData = [
                'nama'        => $barang->nama,
                'kategori'    => $barang->kategori->nama ?? 'Umum',
                'harga_sewa'  => $barang->harga_sewa_harian,
                'deskripsi'   => Str::limit(strip_tags($barang->deskripsi), 200),
                'diskon'      => $request->diskon,
            ];
        } else {
            $productData = [
                'nama'        => $request->input('manual_nama', 'Paket Sewa Spesial'),
                'kategori'    => $request->input('manual_kategori', 'Umum'),
                'harga_sewa'  => (float)$request->input('manual_harga', 50000),
                'deskripsi'   => $request->input('manual_deskripsi', ''),
                'diskon'      => $request->diskon,
            ];
        }

        $result = $this->aiService->generateCopywriting(
            $productData,
            $request->platform,
            $request->tone,
            $request->custom_note
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data'    => $result,
                'product' => $productData,
            ]);
        }

        return back()->with('generated_copy', $result)->with('selected_product_id', $request->barang_id);
    }

    /**
     * 1-Click Terapkan Voucher Rekomendasi AI
     */
    public function autoApplyVoucher(Request $request)
    {
        $request->validate([
            'kode_voucher'    => 'required|string|max:50',
            'tipe_diskon'     => 'required|in:persen,nominal',
            'nilai_diskon'    => 'required|numeric|min:1',
            'minimal_belanja' => 'required|numeric|min:0',
            'maksimal_diskon' => 'nullable|numeric|min:0',
            'durasi_hari'     => 'nullable|integer|min:1|max:90',
        ]);

        $vendorId = (int)Auth::id();
        $code = strtoupper((string)preg_replace('/[^A-Za-z0-9]/', '', (string)$request->input('kode_voucher')));

        // Hindari duplikasi kode voucher jika sudah pernah dibuat
        if (Voucher::where('kode_voucher', $code)->exists()) {
            $code .= rand(10, 99);
        }

        $durasiHari = (int)($request->durasi_hari ?: 7);
        $mulai = Carbon::today();
        $selesai = Carbon::today()->addDays($durasiHari);

        $voucher = Voucher::create([
            'vendor_id'       => $vendorId,
            'kode_voucher'    => $code,
            'tipe_diskon'     => $request->tipe_diskon,
            'nilai_diskon'    => $request->nilai_diskon,
            'maksimal_diskon' => $request->maksimal_diskon ?: ($request->tipe_diskon === 'persen' ? 50000 : null),
            'minimal_belanja' => $request->minimal_belanja,
            'kuota_total'     => 50,
            'kuota_terpakai'  => 0,
            'tanggal_mulai'   => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'is_active'       => 1,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Voucher {$code} berhasil diaktifkan dengan diskon " . 
                             ($voucher->tipe_diskon === 'persen' ? $voucher->nilai_diskon . '%' : 'Rp ' . number_format($voucher->nilai_diskon, 0, ',', '.')) . "!",
                'voucher' => $voucher,
            ]);
        }

        return back()->with('success', "Voucher promosi {$code} berhasil dibuat dan langsung aktif!");
    }
}
