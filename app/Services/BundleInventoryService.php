<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\PaketSewa;

class BundleInventoryService
{
    /**
     * Requirement 1.6, 4.1:
     * Menghitung stok maksimal paket sewa berdasarkan integer floor division dari kapasitas komponen terkecil.
     * Formula: max(0, floor(min_i(stok_tersedia_i / qty_i)))
     *
     * @param PaketSewa $paketSewa
     * @return int
     */
    public function calculateMaxStock(PaketSewa $paketSewa): int
    {
        $items = $paketSewa->relationLoaded('items')
            ? $paketSewa->items
            : $paketSewa->items()->with('barang')->get();

        if ($items->isEmpty()) {
            return 0;
        }

        $minKapasitas = null;

        foreach ($items as $item) {
            $barang = $item->relationLoaded('barang') ? $item->barang : ($item->barang ?? Barang::find($item->barang_id));

            if (!$barang) {
                return 0;
            }

            // Jika barang konstituen tidak aktif atau belum disetujui, paket tidak memiliki stok
            if (isset($barang->status) && $barang->status !== 'tersedia') {
                return 0;
            }

            if (isset($barang->is_approved) && !$barang->is_approved) {
                return 0;
            }

            $qtyPerPaket = max(1, (int) $item->quantity);
            $stokTersedia = isset($barang->stok_tersedia)
                ? (int) $barang->stok_tersedia
                : (int) ($barang->getRawOriginal('stok_total') ?? $barang->stok_total ?? 0);

            if ($stokTersedia <= 0) {
                return 0;
            }

            $kapasitas = (int) floor($stokTersedia / $qtyPerPaket);
            $minKapasitas = ($minKapasitas === null) ? $kapasitas : min($minKapasitas, $kapasitas);
        }

        return max(0, (int) ($minKapasitas ?? 0));
    }

    /**
     * Requirement 4.1:
     * Memeriksa warisan dukungan pengiriman (delivery support inheritance).
     * Mengembalikan false jika salah satu barang konstituen memiliki is_delivery_supported == false,
     * atau jika daftar barang kosong.
     *
     * @param array<int> $barangIds
     * @return bool
     */
    public function resolveDeliverySupport(array $barangIds): bool
    {
        if (empty($barangIds)) {
            return false;
        }

        $uniqueIds = array_values(array_unique($barangIds));
        $barangs = Barang::whereIn('id', $uniqueIds)->get();

        if ($barangs->count() !== count($uniqueIds)) {
            return false;
        }

        foreach ($barangs as $barang) {
            if (!$barang->is_delivery_supported) {
                return false;
            }
        }

        return true;
    }
}
