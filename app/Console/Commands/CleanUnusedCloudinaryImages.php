<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StorageService;
use App\Models\Barang;
use App\Models\FotoBarang;
use App\Models\Banner;
use App\Models\User;
use App\Models\Moment;
use Illuminate\Support\Facades\Schema;

class CleanUnusedCloudinaryImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudinary:clean-unused 
                            {--force : Hapus permanen berkas tak terpakai dari Cloudinary} 
                            {--folder=rentify : Prefix folder Cloudinary yang di-scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembersih aset/gambar tak terpakai di Cloudinary yang produk/datanya sudah dihapus di aplikasi Rentify';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("==========================================================");
        $this->info("   RENTIFY CLOUDINARY UNUSED MEDIA CLEANUP & AUDITOR     ");
        $this->info("==========================================================");

        $cloudinaryUrl = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
        if (empty($cloudinaryUrl)) {
            $this->error("CLOUDINARY_URL tidak ditemukan di file .env!");
            return 1;
        }

        $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);
        $cloudName = $cloudinary->configuration->cloud->cloudName ?? 'unknown';
        $prefix = (string) $this->option('folder');
        $isForce = (bool) $this->option('force');

        $this->line("Cloud Name: <comment>{$cloudName}</comment>");
        $this->line("Folder Prefix: <comment>{$prefix}/</comment>");
        $this->line("Mode: " . ($isForce ? "<fg=red;options=bold>EKSEKUSI HAPUS PERMANEN (--force)</>" : "<fg=yellow;options=bold>DRY RUN (SIMULASI AMAN)</>"));
        $this->newLine();

        // Safety Guard: Larang eksekusi --force di localhost agar tidak menghapus aset milik production
        if ($isForce && !app()->isProduction()) {
            $this->error("PERINGATAN KEAMANAN: Eksekusi --force dilarang di lingkungan local/non-production!");
            $this->line("Database lokal tidak memiliki daftar aset lengkap seperti server production. Perintah ini hanya boleh dieksekusi langsung di server production untuk mencegah hilangnya aset aktif.");
            return 1;
        }

        // 1. Kumpulkan seluruh Public ID yang masih aktif di Database
        $this->info("[1/4] Mengumpulkan referensi media aktif dari Database Rentify...");
        $activePublicIds = $this->gatherActiveDatabasePublicIds();
        $this->line("Total asset aktif yang terdaftar di database: <info>" . count($activePublicIds) . "</info>");
        $this->newLine();

        // 2. Scan seluruh aset di Cloudinary
        $this->info("[2/4] Memindai aset di Cloudinary dengan prefix '{$prefix}'...");
        $cloudinaryAssets = $this->fetchCloudinaryAssets($cloudinary, $prefix);
        $this->line("Total asset ditemukan di Cloudinary: <info>" . count($cloudinaryAssets) . "</info>");
        $this->newLine();

        // 3. Identifikasi Orphaned / Unused Assets
        $this->info("[3/4] Mengidentifikasi gambar/media yang tidak terpakai...");
        $orphanedAssets = [];
        $totalOrphanBytes = 0;

        foreach ($cloudinaryAssets as $asset) {
            $publicId = $asset['public_id'];
            if (!isset($activePublicIds[$publicId])) {
                $orphanedAssets[] = $asset;
                $totalOrphanBytes += ($asset['bytes'] ?? 0);
            }
        }

        $orphanCount = count($orphanedAssets);
        $orphanMb = round($totalOrphanBytes / 1024 / 1024, 2);

        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total Aset di Cloudinary', count($cloudinaryAssets)],
                ['Aset Aktif Terpakai', count($activePublicIds)],
                ['Aset Yatim / Tak Terpakai (Orphaned)', $orphanCount],
                ['Penyimpanan yang Terbuang', "{$orphanMb} MB (" . number_format($totalOrphanBytes) . " bytes)"],
            ]
        );

        if ($orphanCount === 0) {
            $this->info("Selamat! Tidak ditemukan gambar tak terpakai di Cloudinary. Semua aset bersih dan sinkron 100%.");
            return 0;
        }

        // Tampilkan sampel daftar orphaned assets
        $sampleCount = min(20, $orphanCount);
        $this->newLine();
        $this->warn("Daftar sampel {$sampleCount} dari {$orphanCount} berkas yang sudah tidak terpakai:");
        $tableData = [];
        foreach (array_slice($orphanedAssets, 0, $sampleCount) as $idx => $item) {
            $tableData[] = [
                $idx + 1,
                $item['public_id'],
                $item['format'] ?? '-',
                round(($item['bytes'] ?? 0) / 1024, 1) . ' KB',
                $item['created_at'] ?? '-',
            ];
        }
        $this->table(['No', 'Public ID', 'Format', 'Ukuran', 'Tanggal Unggah'], $tableData);

        // 4. Tindakan Penghapusan atau Edukasi Dry Run
        $this->newLine();
        $this->info("[4/4] Status Tindakan:");

        if (!$isForce) {
            $this->warn("--------------------------------------------------------------------------------");
            $this->warn(" [DRY RUN] TIDAK ADA BERKAS YANG DIHAPUS.");
            $this->warn(" Ditemukan {$orphanCount} berkas tak terpakai ({$orphanMb} MB) di Cloudinary.");
            $this->warn(" Untuk menghapus seluruh berkas tak terpakai ini secara permanen, jalankan:");
            $this->warn("   php artisan cloudinary:clean-unused --force");
            $this->warn("--------------------------------------------------------------------------------");
            return 0;
        }

        $this->alert("MEMULAI PENGHAPUSAN {$orphanCount} BERKAS TAK TERPAKAI DARI CLOUDINARY...");

        $deletedCount = 0;
        $failedCount = 0;
        $bar = $this->output->createProgressBar($orphanCount);
        $bar->start();

        foreach ($orphanedAssets as $asset) {
            $pid = $asset['public_id'];
            $resourceType = ($asset['resource_type'] ?? 'image') === 'video' ? 'video' : 'image';

            try {
                $res = $cloudinary->uploadApi()->destroy($pid, ['resource_type' => $resourceType]);
                if (($res['result'] ?? '') === 'ok' || ($res['result'] ?? '') === 'not found') {
                    $deletedCount++;
                } else {
                    // Coba fallback resource_type video jika image gagal
                    if ($resourceType === 'image') {
                        $resVideo = $cloudinary->uploadApi()->destroy($pid, ['resource_type' => 'video']);
                        if (($resVideo['result'] ?? '') === 'ok') {
                            $deletedCount++;
                            $bar->advance();
                            continue;
                        }
                    }
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $failedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("==========================================================");
        $this->info("   PEMBERSIHAN SELESAI DENGAN SUKSES!                     ");
        $this->info("==========================================================");
        $this->line("Berhasil dihapus: <info>{$deletedCount}</info> berkas");
        if ($failedCount > 0) {
            $this->line("Gagal dihapus: <error>{$failedCount}</error> berkas");
        }
        $this->line("Ruang penyimpanan dibebaskan: <info>{$orphanMb} MB</info>");
        $this->newLine();

        return 0;
    }

    /**
     * Mengumpulkan semua Public ID aktif dari seluruh tabel database Rentify.
     */
    protected function gatherActiveDatabasePublicIds(): array
    {
        $activePublicIds = [];

        $register = function (?string $url, string $label) use (&$activePublicIds) {
            if (empty($url)) {
                return;
            }
            $pid = StorageService::extractCloudinaryPublicId($url);
            if ($pid) {
                $activePublicIds[$pid] = $label;
            }
        };

        // 1. Barang Cover
        if (Schema::hasTable('barang')) {
            foreach (Barang::pluck('cover_photo') as $u) {
                $register($u, 'Barang cover_photo');
            }
        }

        // 2. Foto Galeri Barang
        if (Schema::hasTable('foto_barangs')) {
            foreach (FotoBarang::pluck('foto_path') as $u) {
                $register($u, 'FotoBarang galeri');
            }
        }

        // 3. Banner Promosi
        if (Schema::hasTable('banners')) {
            foreach (Banner::pluck('gambar_url') as $u) {
                $register($u, 'Banner gambar_url');
            }
        }

        // 4. Foto Profil User & Banner Profil User
        if (Schema::hasTable('users')) {
            foreach (User::whereNotNull('foto_profil')->pluck('foto_profil') as $u) {
                $register($u, 'User foto_profil');
            }
            if (Schema::hasColumn('users', 'banner_profil')) {
                foreach (User::whereNotNull('banner_profil')->pluck('banner_profil') as $u) {
                    $register($u, 'User banner_profil');
                }
            }
        }

        // 5. Moments Media & Thumbnails
        if (Schema::hasTable('moments')) {
            foreach (Moment::all() as $m) {
                $register($m->media_url, 'Moment media_url');
                $register($m->thumbnail_url, 'Moment thumbnail_url');
                if (!empty($m->media_urls) && is_array($m->media_urls)) {
                    foreach ($m->media_urls as $singleUrl) {
                        $register($singleUrl, 'Moment media_urls item');
                    }
                }
            }
        }

        // 6. Bukti Pembayaran
        if (Schema::hasTable('pembayarans') && Schema::hasColumn('pembayarans', 'bukti_pembayaran')) {
            foreach (\Illuminate\Support\Facades\DB::table('pembayarans')->whereNotNull('bukti_pembayaran')->pluck('bukti_pembayaran') as $u) {
                $register($u, 'Pembayaran bukti');
            }
        }

        // 7. Komplain Foto Bukti
        if (Schema::hasTable('komplains') && Schema::hasColumn('komplains', 'bukti_foto')) {
            foreach (\Illuminate\Support\Facades\DB::table('komplains')->whereNotNull('bukti_foto')->pluck('bukti_foto') as $u) {
                $register($u, 'Komplain bukti');
            }
        }

        // 8. AI Marketing Posts
        if (Schema::hasTable('ai_marketing_posts') && Schema::hasColumn('ai_marketing_posts', 'visual_media_url')) {
            foreach (\Illuminate\Support\Facades\DB::table('ai_marketing_posts')->whereNotNull('visual_media_url')->pluck('visual_media_url') as $u) {
                $register($u, 'AI Marketing visual');
            }
        }

        // 9. Sistem & Template Fallback Assets (Hardcoded di Views & Services)
        $systemFallbacks = [
            'rentify/moments/naikdt1lgug5qgylrk1l' => 'Fallback Moment Image (Index & Api)',
            'rentify/barang/epir3hg5l8m80lkoxbpb' => 'Fallback Barang Bola/Olahraga (StorageService)',
            'gambar_logo_trerjo' => 'Logo Utama Rentify (Blade Views)',
            'logolimaduabelas_gjelaz' => 'Logo Asset Sekunder',
            'Qris_kqb63q' => 'Gambar QRIS Pembayaran',
            'background_lqct46' => 'Background Asset',
        ];
        foreach ($systemFallbacks as $fallbackPid => $label) {
            $activePublicIds[$fallbackPid] = $label;
        }

        return $activePublicIds;
    }

    /**
     * Memindai aset dari Cloudinary secara lengkap (images + videos) dengan pagination cursor.
     */
    protected function fetchCloudinaryAssets(\Cloudinary\Cloudinary $cloudinary, string $prefix): array
    {
        $allAssets = [];

        // Scan images
        $nextCursor = null;
        do {
            $params = [
                'type'        => 'upload',
                'prefix'      => $prefix,
                'max_results' => 100,
            ];
            if ($nextCursor) {
                $params['next_cursor'] = $nextCursor;
            }

            try {
                $res = $cloudinary->adminApi()->assets($params);
                foreach ($res['resources'] ?? [] as $r) {
                    $r['resource_type'] = 'image';
                    $allAssets[$r['public_id']] = $r;
                }
                $nextCursor = $res['next_cursor'] ?? null;
            } catch (\Throwable $e) {
                $this->warn("Gagal memindai sebagian aset image: " . $e->getMessage());
                break;
            }
        } while ($nextCursor);

        // Scan videos
        $nextCursor = null;
        do {
            $params = [
                'type'          => 'upload',
                'resource_type' => 'video',
                'prefix'        => $prefix,
                'max_results'   => 100,
            ];
            if ($nextCursor) {
                $params['next_cursor'] = $nextCursor;
            }

            try {
                $res = $cloudinary->adminApi()->assets($params);
                foreach ($res['resources'] ?? [] as $r) {
                    $r['resource_type'] = 'video';
                    $allAssets[$r['public_id']] = $r;
                }
                $nextCursor = $res['next_cursor'] ?? null;
            } catch (\Throwable $e) {
                // Folder mungkin tidak memiliki video
                break;
            }
        } while ($nextCursor);

        return array_values($allAssets);
    }
}
