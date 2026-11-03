<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerVoucher;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerVoucherController extends Controller
{
    /**
     * Menampilkan halaman promo voucher customer.
     * Semua voucher aktif publik tampil langsung (tidak perlu klaim lewat toko).
     * Pelanggan juga dapat mencari & mengklaim kode voucher promo.
     */
    public function index(Request $request)
    {
        // 1. Cek pencarian kode voucher
        $searchedVoucher = null;
        $searchMessage = null;
        $searchStatus = null; // 'success' | 'error'

        if ($request->filled('kode') || $request->filled('kode_voucher')) {
            $inputKode = strtoupper(trim($request->input('kode', $request->input('kode_voucher'))));

            $found = Voucher::with('vendor')
                ->where('kode_voucher', $inputKode)
                ->where('is_active', 1)
                ->whereDate('tanggal_mulai', '<=', now())
                ->whereDate('tanggal_selesai', '>=', now())
                ->first();

            if (!$found) {
                $searchStatus = 'error';
                $searchMessage = "Kode voucher \"{$inputKode}\" tidak ditemukan atau sudah kadaluarsa.";
            } elseif ($found->kuota_terpakai >= $found->kuota_total) {
                $searchStatus = 'error';
                $searchMessage = "Kuota pemakaian voucher \"{$inputKode}\" sudah habis.";
            } else {
                $searchStatus = 'success';
                $searchedVoucher = $found;
                $searchMessage = "Voucher \"{$inputKode}\" berhasil ditemukan!";

                if (Auth::check()) {
                    CustomerVoucher::firstOrCreate(
                        [
                            'user_id' => Auth::id(),
                            'voucher_id' => $found->id,
                        ],
                        [
                            'status' => 'aktif',
                            'claimed_at' => now(),
                        ]
                    );
                }
            }
        }

        // 2. Tampilkan semua voucher aktif publik tanpa mewajibkan klaim lewat toko
        // Serta voucher yang sudah diklaim pelanggan
        $claimedVoucherIds = collect();
        if (Auth::check()) {
            $claimedVoucherIds = CustomerVoucher::where('user_id', Auth::id())
                ->where('status', 'aktif')
                ->pluck('voucher_id');
        }

        $vouchers = Voucher::with('vendor')
            ->where('is_active', 1)
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->whereColumn('kuota_terpakai', '<', 'kuota_total')
            ->where(function ($q) {
                $q->whereNull('vendor_id')
                  ->orWhereHas('vendor', function ($vq) {
                      $vq->where('vendor_status', '!=', 'suspended')
                         ->orWhereNull('vendor_status');
                  });
            })
            ->where(function ($q) use ($claimedVoucherIds) {
                $q->where(function ($sub) {
                    $sub->whereNull('visibilitas')
                        ->orWhere('visibilitas', '!=', 'rahasia');
                });
                if ($claimedVoucherIds->isNotEmpty()) {
                    $q->orWhereIn('id', $claimedVoucherIds);
                }
            })
            ->latest()
            ->get();

        return view('customer.voucher.index', compact('vouchers', 'searchedVoucher', 'searchMessage', 'searchStatus'));
    }

    /**
     * Endpoint AJAX untuk klaim voucher (publik maupun rahasia) oleh pelanggan.
     */
    public function claim(Request $request)
    {
        $inputKode = strtoupper(trim($request->input('kode_voucher', $request->input('kode', ''))));

        if (empty($inputKode)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan kode voucher terlebih dahulu.',
            ], 422);
        }

        $voucher = Voucher::with('vendor')
            ->where('kode_voucher', $inputKode)
            ->where('is_active', 1)
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => "Kode voucher \"{$inputKode}\" tidak valid atau sudah kadaluarsa.",
            ], 404);
        }

        if ($voucher->kuota_terpakai >= $voucher->kuota_total) {
            return response()->json([
                'success' => false,
                'message' => "Kuota voucher \"{$inputKode}\" sudah habis terpakai.",
            ], 422);
        }

        $isLoggedIn = Auth::check();
        if ($isLoggedIn) {
            CustomerVoucher::firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'voucher_id' => $voucher->id,
                ],
                [
                    'status' => 'aktif',
                    'claimed_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Voucher \"{$voucher->kode_voucher}\" berhasil diklaim & siap dipakai!",
            'is_logged_in' => $isLoggedIn,
            'voucher' => [
                'id' => $voucher->id,
                'kode_voucher' => $voucher->kode_voucher,
                'tipe_diskon' => $voucher->tipe_diskon,
                'nilai_diskon' => $voucher->nilai_diskon,
                'maksimal_diskon' => $voucher->maksimal_diskon,
                'minimal_belanja' => $voucher->minimal_belanja,
                'visibilitas' => $voucher->visibilitas,
                'tanggal_selesai' => $voucher->tanggal_selesai ? $voucher->tanggal_selesai->format('d M Y') : null,
                'vendor_id' => $voucher->vendor_id,
                'vendor_name' => $voucher->vendor->vendor_name ?? ($voucher->vendor->name ?? 'Vendor Rentify'),
            ],
        ]);
    }
}
