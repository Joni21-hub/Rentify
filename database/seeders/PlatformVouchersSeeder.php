<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class PlatformVouchersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Provisions 204 master platform vouchers with total 15,200 quota.
     * Uses idempotent firstOrNew pattern to preserve runtime counters and admin suspensions.
     */
    public function run(): void
    {
        $catalog = $this->getCatalog();

        foreach ($catalog as $item) {
            $voucher = Voucher::firstOrNew(['kode_voucher' => $item['kode_voucher']]);

            // Update static promotional definitions
            $voucher->funding_source = 'platform';
            $voucher->tipe_voucher = 'platform';
            $voucher->vendor_id = null;
            $voucher->minimal_belanja = 0.00;
            $voucher->tipe_diskon = $item['tipe_diskon'];
            $voucher->nilai_diskon = $item['nilai_diskon'];
            $voucher->maksimal_diskon = $item['maksimal_diskon'];
            $voucher->min_quantity = $item['min_quantity'];
            $voucher->kuota_total = $item['kuota_total'];
            $voucher->anggaran_maksimal_platform = $item['anggaran_maksimal_platform'];
            $voucher->tanggal_mulai = $item['tanggal_mulai'];
            $voucher->tanggal_selesai = $item['tanggal_selesai'];
            $voucher->visibilitas = $item['visibilitas'] ?? 'publik';
            $voucher->cakupan_produk = $item['cakupan_produk'] ?? 'seluruh_toko';

            // Initialize runtime counters ONLY if record does not exist
            if (!$voucher->exists) {
                $voucher->kuota_terpakai = 0;
                $voucher->kuota_reserved = 0;
                $voucher->anggaran_terpakai = 0.00;
                $voucher->anggaran_reserved = 0.00;
                $voucher->is_active = true;
                $voucher->suspended_by_admin = false;
                $voucher->auto_exhausted = false;
            }

            // Save without overwriting existing runtime counters or manual admin suspension flags
            $voucher->save();
        }
    }

    /**
     * Build the deterministic 204 master platform voucher catalog.
     * Total Quota: 15,200 units across 3 main promotional categories.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCatalog(): array
    {
        $items = [];
        $startDate = '2026-01-01';
        $endDate = '2026-12-31';

        // 1. FREE RENTAL & BOGO (100 records, 5,000 quota)
        // 1a. Free Item (25 records: FREEITEM01 - FREEITEM25)
        for ($i = 1; $i <= 25; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('FREEITEM%02d', $i),
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 100.00,
                'maksimal_diskon' => 150000.00,
                'min_quantity' => 1,
                'kuota_total' => 50,
                'anggaran_maksimal_platform' => 7500000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 1b. Free Package (25 records: FREEPKG01 - FREEPKG25)
        for ($i = 1; $i <= 25; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('FREEPKG%02d', $i),
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 100.00,
                'maksimal_diskon' => 300000.00,
                'min_quantity' => 1,
                'kuota_total' => 50,
                'anggaran_maksimal_platform' => 15000000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 1c. BOGO Sewa 2 Bayar 1 (25 records: BOGO2B01 - BOGO2B25)
        for ($i = 1; $i <= 25; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('BOGO2B%02d', $i),
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 50.00,
                'maksimal_diskon' => 200000.00,
                'min_quantity' => 2,
                'kuota_total' => 50,
                'anggaran_maksimal_platform' => 10000000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 1d. BOGO Sewa 3 Bayar 2 (25 records: BOGO3B01 - BOGO3B25)
        for ($i = 1; $i <= 25; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('BOGO3B%02d', $i),
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 33.3333,
                'maksimal_diskon' => 150000.00,
                'min_quantity' => 3,
                'kuota_total' => 50,
                'anggaran_maksimal_platform' => 7500000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 2. PERCENTAGE (64 records, 5,200 quota)
        // 2a. Cohort A (16 records: DISKON10P01 - DISKON10P16)
        for ($i = 1; $i <= 16; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('DISKON10P%02d', $i),
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 10.00,
                'maksimal_diskon' => 25000.00,
                'min_quantity' => 1,
                'kuota_total' => 100,
                'anggaran_maksimal_platform' => 2500000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 2b. Cohort B (16 records: DISKON20P01 - DISKON20P16)
        for ($i = 1; $i <= 16; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('DISKON20P%02d', $i),
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 20.00,
                'maksimal_diskon' => 50000.00,
                'min_quantity' => 1,
                'kuota_total' => 100,
                'anggaran_maksimal_platform' => 5000000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 2c. Cohort C (16 records: DISKON30P01 - DISKON30P16)
        for ($i = 1; $i <= 16; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('DISKON30P%02d', $i),
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 30.00,
                'maksimal_diskon' => 75000.00,
                'min_quantity' => 1,
                'kuota_total' => 75,
                'anggaran_maksimal_platform' => 5625000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 2d. Cohort D (16 records: DISKON50P01 - DISKON50P16)
        for ($i = 1; $i <= 16; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('DISKON50P%02d', $i),
                'tipe_diskon' => 'persen',
                'nilai_diskon' => 50.00,
                'maksimal_diskon' => 100000.00,
                'min_quantity' => 1,
                'kuota_total' => 50,
                'anggaran_maksimal_platform' => 5000000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 3. NOMINAL (40 records, 5,000 quota)
        // 3a. Cohort A (10 records: POTONG10K01 - POTONG10K10)
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('POTONG10K%02d', $i),
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 10000.00,
                'maksimal_diskon' => null,
                'min_quantity' => 1,
                'kuota_total' => 200,
                'anggaran_maksimal_platform' => 2000000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 3b. Cohort B (10 records: POTONG25K01 - POTONG25K10)
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('POTONG25K%02d', $i),
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 25000.00,
                'maksimal_diskon' => null,
                'min_quantity' => 1,
                'kuota_total' => 150,
                'anggaran_maksimal_platform' => 3750000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 3c. Cohort C (10 records: POTONG50K01 - POTONG50K10)
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('POTONG50K%02d', $i),
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 50000.00,
                'maksimal_diskon' => null,
                'min_quantity' => 1,
                'kuota_total' => 100,
                'anggaran_maksimal_platform' => 5000000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        // 3d. Cohort D (10 records: POTONG100K01 - POTONG100K10)
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'kode_voucher' => sprintf('POTONG100K%02d', $i),
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 100000.00,
                'maksimal_diskon' => null,
                'min_quantity' => 1,
                'kuota_total' => 50,
                'anggaran_maksimal_platform' => 5000000.00,
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'visibilitas' => 'publik',
                'cakupan_produk' => 'seluruh_toko',
            ];
        }

        return $items;
    }
}
