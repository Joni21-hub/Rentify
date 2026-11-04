<?php

namespace App\Observers;

use App\Events\VendorBundleAlertEvent;
use App\Models\Barang;
use App\Models\OrderItemComponent;
use App\Models\PaketSewa;
use App\Models\PaketSewaItem;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BarangObserver
{
    /**
     * Requirement 1.8, 4.9:
     * Hook updating: verifikasi perubahan sebelum persistensi jika diperlukan.
     */
    public function updating(Barang $barang): void
    {
        // Menyediakan extension point untuk pre-update validation
    }

    /**
     * Requirement 1.8, 1.9, 3.1, 3.2:
     * Hook updated:
     * 1. Menurunkan status bundle menjadi 'tidak_aktif' jika barang konstituen menjadi tidak tersedia / unapproved.
     * 2. Menghitung ulang agregat deposit dan denda_per_hari pada bundle terkait jika terjadi perubahan tarif.
     */
    public function updated(Barang $barang): void
    {
        // 1. Cascade penonaktifan bundle jika status berubah menjadi tidak tersedia atau belum disetujui
        $becameUnavailable = ($barang->wasChanged('status') && $barang->status !== 'tersedia');
        $becameUnapproved = ($barang->wasChanged('is_approved') && !$barang->is_approved);

        if ($becameUnavailable || $becameUnapproved) {
            $reason = $becameUnapproved ? 'tidak disetujui (unapproved)' : 'tidak aktif / tidak tersedia';
            $this->cascadeBundleDeactivation($barang, $reason);
        }

        // 2. Update agregat deposit & denda per hari jika tarif berubah
        if ($barang->wasChanged(['deposit', 'denda_per_hari'])) {
            $this->recalculateBundleRates($barang);
        }
    }

    /**
     * Requirement 1.8, 3.3, 4.9:
     * Hook deleting:
     * Memeriksa order_item_components apakah barang sedang digunakan dalam pesanan aktif
     * ('Menunggu Pembayaran', 'Disetujui', 'Sedang Disewa').
     * Jika aktif, batalkan penghapusan dengan HTTP 422.
     * Jika aman untuk dihapus, nonaktifkan seluruh bundle terkait dan kirim alert vendor.
     */
    public function deleting(Barang $barang): void
    {
        // Cek apakah barang sedang aktif dalam pesanan bundle
        $hasActiveOrders = OrderItemComponent::where('barang_id', $barang->id)
            ->whereHas('orderItem.penyewaan', function ($query) {
                $query->whereIn('status', ['Menunggu Pembayaran', 'Disetujui', 'Sedang Disewa']);
            })
            ->exists();

        if ($hasActiveOrders) {
            App::abort(422, 'Tidak dapat menghapus barang master karena sedang digunakan dalam transaksi pesanan bundle yang sedang aktif.');
        }

        // Nonaktifkan seluruh bundle penyusun sebelum master item dihapus
        $this->cascadeBundleDeactivation($barang, 'dihapus dari sistem');

        // Bersihkan entri paket_sewa_items agar tidak melanggar foreign key restrict
        PaketSewaItem::where('barang_id', $barang->id)->delete();
    }

    /**
     * Nonaktifkan seluruh paket sewa (dan produk bundle legacy) yang memuat barang ini.
     *
     * @param Barang $barang
     * @param string $reason
     * @return void
     */
    protected function cascadeBundleDeactivation(Barang $barang, string $reason): void
    {
        // 1. PaketSewa (Tabel paket_sewa)
        $bundleIds = PaketSewaItem::where('barang_id', $barang->id)
            ->whereNotNull('paket_sewa_id')
            ->pluck('paket_sewa_id')
            ->filter()
            ->unique();

        if ($bundleIds->isNotEmpty()) {
            $bundles = PaketSewa::whereIn('id', $bundleIds)
                ->where('status', '!=', 'tidak_aktif')
                ->get();

            foreach ($bundles as $bundle) {
                $bundle->status = 'tidak_aktif';
                $bundle->saveQuietly();
                $this->dispatchVendorAlert($bundle, $barang, $reason);
            }
        }

        // 2. Legacy bundle barang (is_paket = 1)
        $legacyPaketIds = PaketSewaItem::where('barang_id', $barang->id)
            ->whereNotNull('paket_id')
            ->pluck('paket_id')
            ->filter()
            ->unique();

        if ($legacyPaketIds->isNotEmpty()) {
            $legacyBundles = Barang::whereIn('id', $legacyPaketIds)
                ->where('is_paket', 1)
                ->get();

            foreach ($legacyBundles as $legacyBundle) {
                $legacyBundle->status = 'tidak_tersedia';
                if (Schema::hasColumn('barang', 'status_barang')) {
                    $legacyBundle->status_barang = 'tidak_aktif';
                }
                $legacyBundle->saveQuietly();
                $this->dispatchVendorAlert($legacyBundle, $barang, $reason);
            }
        }
    }

    /**
     * Hitung ulang agregat deposit dan denda pada seluruh paket terkait.
     *
     * @param Barang $barang
     * @return void
     */
    protected function recalculateBundleRates(Barang $barang): void
    {
        // 1. PaketSewa
        $bundleIds = PaketSewaItem::where('barang_id', $barang->id)
            ->whereNotNull('paket_sewa_id')
            ->pluck('paket_sewa_id')
            ->filter()
            ->unique();

        foreach ($bundleIds as $bundleId) {
            $bundle = PaketSewa::find($bundleId);
            if ($bundle) {
                $bundle->recalculateDepositAndDenda();
                if (Schema::hasColumn('paket_sewa', 'deposit') && Schema::hasColumn('paket_sewa', 'denda_per_hari')) {
                    $bundle->deposit = $bundle->deposit;
                    $bundle->denda_per_hari = $bundle->denda_per_hari;
                    $bundle->saveQuietly();
                }
            }
        }

        // 2. Legacy bundle barang (is_paket = 1)
        $legacyPaketIds = PaketSewaItem::where('barang_id', $barang->id)
            ->whereNotNull('paket_id')
            ->pluck('paket_id')
            ->filter()
            ->unique();

        foreach ($legacyPaketIds as $paketId) {
            $bundleBarang = Barang::where('id', $paketId)->where('is_paket', 1)->first();
            if ($bundleBarang) {
                $items = PaketSewaItem::where('paket_id', $paketId)->with('barang')->get();
                $totalDeposit = $items->sum(fn($it) => ($it->barang?->deposit ?? 0) * (int) $it->quantity);
                $totalDenda = $items->sum(fn($it) => ($it->barang?->denda_per_hari ?? 0) * (int) $it->quantity);

                $bundleBarang->deposit = $totalDeposit;
                $bundleBarang->denda_per_hari = $totalDenda;
                $bundleBarang->saveQuietly();
            }
        }
    }

    /**
     * Mengirim alert notifikasi log, event, dan WhatsApp ke vendor pemilik bundle.
     *
     * @param mixed $bundle
     * @param Barang $barang
     * @param string $reason
     * @return void
     */
    protected function dispatchVendorAlert($bundle, Barang $barang, string $reason): void
    {
        Log::warning("[BUNDLE-REACTIVITY-ALERT] Paket sewa '{$bundle->nama}' (ID: {$bundle->id}) otomatis dinonaktifkan karena komponen '{$barang->nama}' (ID: {$barang->id}) {$reason}.");

        Event::dispatch(new VendorBundleAlertEvent($bundle, $barang, $reason));

        try {
            if (!App::environment('testing')) {
                $vendorId = $bundle->vendor_id;
                $vendor = $bundle->vendor ?? User::find($vendorId);
                $phone = $vendor?->whatsapp_vendor ?? $vendor?->whatsapp;

                if (!empty($phone)) {
                    $msg = "⚠️ *Pemberitahuan Rentify*: Paket sewa \"{$bundle->nama}\" Anda otomatis dinonaktifkan karena barang \"{$barang->nama}\" {$reason}. Silakan cek katalog Anda.";
                    WhatsAppService::send($phone, $msg);
                }
            }
        } catch (\Throwable $e) {
            Log::error("[BUNDLE-REACTIVITY-ALERT] Gagal mengirim pesan WhatsApp alert vendor: " . $e->getMessage());
        }
    }
}
