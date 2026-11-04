<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BundleLockService
{
    /**
     * Requirement 4.5:
     * Mengakuisisi database row lock pesimistik (SELECT ... FOR UPDATE) secara deterministik
     * dengan mengurutkan ID barang secara menaik (ascending) terlebih dahulu guna mengeliminasi
     * kondisi race dan MySQL deadlock (SQLSTATE[40001]).
     *
     * Kunci baris database fisik akan ditahan selama transaksi berjalan dan otomatis dilepaskan
     * saat DB::commit() atau DB::rollBack() dieksekusi.
     *
     * @param array<int|string> $barangIds Daftar ID barang yang akan dikunci
     * @return Collection<int, Barang>
     */
    public function lockItemsForUpdate(array $barangIds): Collection
    {
        if (empty($barangIds)) {
            return new Collection();
        }

        // Sanitasi, deduplikasi, dan konversi ke integer
        $sanitizedIds = array_values(array_unique(array_filter(array_map('intval', $barangIds), fn($id) => $id > 0)));

        if (empty($sanitizedIds)) {
            return new Collection();
        }

        // Urutkan ID secara deterministik menaik (ascending) untuk mencegah siklus kunci deadlock
        sort($sanitizedIds, SORT_NUMERIC);

        return Barang::whereIn('id', $sanitizedIds)
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();
    }

    /**
     * Static proxy helper untuk kemudahan pemanggilan langsung.
     *
     * @param array<int|string> $barangIds
     * @return Collection<int, Barang>
     */
    public static function lock(array $barangIds): Collection
    {
        return (new static())->lockItemsForUpdate($barangIds);
    }

    /**
     * Magic static call forwarder.
     */
    public static function __callStatic(string $method, array $arguments)
    {
        return (new static())->$method(...$arguments);
    }
}
