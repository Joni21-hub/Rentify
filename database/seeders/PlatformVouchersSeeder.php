<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class PlatformVouchersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Provisions 204 master platform vouchers according to the official Rentify Voucher Matrix
     * using the 204 authentic voucher codes from the official physical voucher card design:
     * - 125 Percentage Vouchers (limited by max item count, not max price)
     * - 79 Nominal Vouchers (limited by max nominal discount, no item quantity limit)
     * Total: 204 master platform vouchers.
     *
     * Invariants:
     * - Satu kode satu voucher, satu kali pakai (kuota_total = 1).
     * - Visibilitas 'rahasia' (eksklusif bagi pemilik fisik kartu voucher melalui input klaim kode).
     * - Uses idempotent firstOrNew pattern to preserve runtime counters and admin suspensions.
     */
    public function run(): void
    {
        $catalog = $this->getCatalog();
        $validCodes = array_column($catalog, 'kode_voucher');

        // Clean up legacy synthetic placeholder platform vouchers without transactional redemptions
        Voucher::where('funding_source', 'platform')
            ->whereNotIn('kode_voucher', $validCodes)
            ->whereDoesntHave('redemptions')
            ->delete();

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
            $voucher->max_quantity = $item['max_quantity'] ?? null;
            $voucher->kuota_total = $item['kuota_total'];
            $voucher->anggaran_maksimal_platform = $item['anggaran_maksimal_platform'];
            $voucher->tanggal_mulai = $item['tanggal_mulai'];
            $voucher->tanggal_selesai = $item['tanggal_selesai'];
            $voucher->visibilitas = $item['visibilitas'] ?? 'rahasia';
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
     * Build the deterministic 204 master platform voucher catalog using the 204 unique codes
     * from the official Rentify voucher card design (Desain Rentify_20261019_161509_0000.pdf).
     * Total: 204 vouchers (125 Persentase + 79 Nominal).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCatalog(): array
    {
        $codes = $this->getOfficialVoucherCodes();
        $items = [];
        $codeIndex = 0;
        $startDate = '2026-01-01';
        $endDate = '2026-12-31';

        // =========================================================================
        // 1. VOUCHER PERSENTASE (%) - Total 125 voucher
        // Ketentuan: Menggunakan batas maksimal jumlah barang (max_quantity),
        // bukan maksimal harga (maksimal_diskon = null). Min belanja Rp0. Kuota = 1 (1x pakai).
        // =========================================================================
        $persenDefinitions = [
            ['count' => 10, 'diskon' => 100.00, 'max_qty' => 1],
            ['count' => 10, 'diskon' => 100.00, 'max_qty' => 2],
            ['count' => 10, 'diskon' => 100.00, 'max_qty' => 3],
            ['count' => 5,  'diskon' => 100.00, 'max_qty' => 5],
            ['count' => 5,  'diskon' => 90.00,  'max_qty' => 1],
            ['count' => 8,  'diskon' => 80.00,  'max_qty' => 2],
            ['count' => 10, 'diskon' => 75.00,  'max_qty' => 2],
            ['count' => 12, 'diskon' => 50.00,  'max_qty' => 3],
            ['count' => 12, 'diskon' => 45.00,  'max_qty' => 3],
            ['count' => 12, 'diskon' => 40.00,  'max_qty' => 3],
            ['count' => 12, 'diskon' => 35.00,  'max_qty' => 2],
            ['count' => 8,  'diskon' => 30.00,  'max_qty' => 3],
            ['count' => 6,  'diskon' => 25.00,  'max_qty' => 2],
            ['count' => 5,  'diskon' => 20.00,  'max_qty' => 1],
        ];

        foreach ($persenDefinitions as $def) {
            for ($i = 0; $i < $def['count']; $i++) {
                $items[] = [
                    'kode_voucher' => $codes[$codeIndex++],
                    'tipe_diskon' => 'persen',
                    'nilai_diskon' => $def['diskon'],
                    'maksimal_diskon' => null, // Tidak ada batas maksimal harga rupiah
                    'min_quantity' => 1,
                    'max_quantity' => $def['max_qty'], // Dibatasi jumlah maksimal barang
                    'kuota_total' => 1, // Satu voucher satu kali pakai
                    'anggaran_maksimal_platform' => null, // Subsidi platform
                    'tanggal_mulai' => $startDate,
                    'tanggal_selesai' => $endDate,
                    'visibilitas' => 'rahasia',
                    'cakupan_produk' => 'seluruh_toko',
                ];
            }
        }

        // =========================================================================
        // 2. VOUCHER NOMINAL (Rp) - Total 79 voucher
        // Ketentuan: Menggunakan batas maksimal harga potongan (nilai_diskon = rupiah),
        // bukan batas jumlah barang (max_quantity = null). Min belanja Rp0. Kuota = 1 (1x pakai).
        // =========================================================================
        $nominalDefinitions = [
            ['count' => 5,  'potongan' => 50000.00],
            ['count' => 7,  'potongan' => 39000.00],
            ['count' => 7,  'potongan' => 35000.00],
            ['count' => 8,  'potongan' => 30000.00],
            ['count' => 8,  'potongan' => 27000.00],
            ['count' => 8,  'potongan' => 25000.00],
            ['count' => 8,  'potongan' => 23000.00],
            ['count' => 8,  'potongan' => 20000.00],
            ['count' => 8,  'potongan' => 17000.00],
            ['count' => 12, 'potongan' => 15000.00],
        ];

        foreach ($nominalDefinitions as $def) {
            for ($i = 0; $i < $def['count']; $i++) {
                $items[] = [
                    'kode_voucher' => $codes[$codeIndex++],
                    'tipe_diskon' => 'nominal',
                    'nilai_diskon' => $def['potongan'],
                    'maksimal_diskon' => $def['potongan'], // Plafon potongan nominal
                    'min_quantity' => 1,
                    'max_quantity' => null, // Bebas jumlah barang
                    'kuota_total' => 1, // Satu voucher satu kali pakai
                    'anggaran_maksimal_platform' => null, // Subsidi platform
                    'tanggal_mulai' => $startDate,
                    'tanggal_selesai' => $endDate,
                    'visibilitas' => 'rahasia',
                    'cakupan_produk' => 'seluruh_toko',
                ];
            }
        }

        return $items;
    }

    /**
     * The official 204 unique codes extracted from the physical card design (17 pages x 12 cards/page).
     *
     * @return array<int, string>
     */
    public function getOfficialVoucherCodes(): array
    {
        return [
            // Page 1
            'R729BLF', 'R72CH75', 'R72JEWN', 'R73FTGS', 'R73LJRN', 'R73U34Q',
            'R74HBL7', 'R74N9QF', 'R74Y3L2', 'R75M8WZ', 'R75U4V7', 'R75VZ66',

            // Page 2
            'R76GLTP', 'R77AUW8', 'R77DZPZ', 'R77QCYE', 'R788QFK', 'R78NV7K',
            'R78TBMZ', 'R79BS8B', 'R79GANL', 'R7ATCF4', 'R7AWKNJ', 'R7B9LW4',

            // Page 3
            'R7D8WBB', 'R7DGJPB', 'R7DRFY2', 'R7E64F3', 'R7F6YLR', 'R7FARA3',
            'R7G89Q3', 'R7GBMP2', 'R7GLQV5', 'R7H62N4', 'R7J3EW6', 'R7JMY2S',

            // Page 4
            'R7JZHBP', 'R7L8ETA', 'R7LG3H3', 'R7MJCUB', 'R7MRRK8', 'R7N5CES',
            'R7NML3W', 'R7NN7UU', 'R7NXNDD', 'R7NZZCG', 'R7PW5SE', 'R7QWW7C',

            // Page 5
            'R7RMW9Q', 'R7SJ4VQ', 'R7SLV6B', 'R7T9MJ4', 'R7TSCHA', 'R7U2NK8',
            'R7UARUT', 'R7UPYZ4', 'R7VVC5U', 'R7X4TRC', 'R7X98N6', 'R7XLT8S',

            // Page 6
            'R7XQJEN', 'R7Y68H3', 'R7YNTTW', 'R7Z4CPN', 'R7ZJLJ6', 'R7ZNTMC',
            'R7ZTVZH', 'RK225AN', 'RK2FTTS', 'RK34E87', 'RK363XK', 'RK3QQDX',

            // Page 7
            'RK47TWA', 'RK4FXTL', 'RK4WZF6', 'RK5345Q', 'RK6QZ5M', 'RK7DRZE',
            'RK7UY7P', 'RK7WJAP', 'RK8BFQD', 'RK94DL4', 'RKA3M8T', 'RKAB6U3',

            // Page 8
            'RKAEWTG', 'RKBREK5', 'RKCCDTF', 'RKD4UX4', 'RKD5VM2', 'RKD7NBE',
            'RKD8QQK', 'RKDFVHS', 'RKDSXHD', 'RKDT9ZK', 'RKF58DF', 'RKFJGBK',

            // Page 9
            'RKG6EU2', 'RKGJ3JL', 'RKGZ5L5', 'RKH7DNR', 'RKHCP47', 'RKHJAYQ',
            'RKJ2GJ2', 'RKJAC93', 'RKJBD2V', 'RKJVFWF', 'RKL3WMT', 'RKL8BTQ',

            // Page 10
            'RKLETBM', 'RKLFNQK', 'RKLZBL9', 'RKM3TBL', 'RKM7JJG', 'RKMPVQ6',
            'RKMYWH3', 'RKN37WA', 'RKND6QY', 'RKNX5BC', 'RKNY5FQ', 'RKPGFSW',

            // Page 11
            'RKPP2Q9', 'RKPQPP5', 'RKQBV2W', 'RKQFXVL', 'RKQQK8P', 'RKR35UZ',
            'RKRN2US', 'RKRPELT', 'RKS7QM2', 'RKSRG5S', 'RKSTQNT', 'RKT5J57',

            // Page 12
            'RKTCQMP', 'RKTPRC9', 'RKUZVSB', 'RKVBRX5', 'RKVMEMY', 'RKW95CS',
            'RKWAD6M', 'RKWC6MZ', 'RKX9GQ7', 'RKXQVWW', 'RKYAKL4', 'RKYLCPN',

            // Page 13
            'RKYTEGQ', 'RKZ77DQ', 'RKZ9FVP', 'RKZAF2N', 'RKZCBNQ', 'RKZCW62',
            'RX23T86', 'RX2CQQD', 'RX2HB2L', 'RX3JMFV', 'RX3XXSL', 'RX3YCYJ',

            // Page 14
            'RX3ZD34', 'RX45AXQ', 'RX49N9B', 'RX4DBWZ', 'RX4LEXP', 'RX4N7US',
            'RX4WB8W', 'RX5H43W', 'RX68VBX', 'RX6TGPC', 'RX772QY', 'RX7QN94',

            // Page 15
            'RX8WB78', 'RX9GYL3', 'RX9JRBR', 'RX9VSS3', 'RXAP5N5', 'RXAV7LV',
            'RXBBA8V', 'RXBXVEP', 'RXBYQ3F', 'RXC29NE', 'RXCHNEH', 'RXD4BZM',

            // Page 16
            'RXD6SEX', 'RXD9J9P', 'RXDJBB8', 'RXDS4DH', 'RXDTXDL', 'RXEAMSZ',
            'RXEF32L', 'RXELKJG', 'RXF9H2E', 'RXFKGM3', 'RXFUENP', 'RXFWGPS',

            // Page 17
            'RXGE27P', 'RXHK7DD', 'RXHX68Y', 'RXHZL9C', 'RXJ3C4D', 'RXJ8P53',
            'RXJ93FE', 'RXJEQMM', 'RXK2VUC', 'RXK7AYK', 'RXKHL6N', 'RXKZZ8Z',
        ];
    }
}
