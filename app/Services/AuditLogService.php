<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Catat rekam jejak audit (trail audit) administratif untuk aksi finansial dan operasional penting.
     *
     * @param string $action Nama aksi (misal: 'refund_pembayaran', 'pencairan_penarikan_vendor', 'pembatalan_pesanan')
     * @param string $auditableType Tipe model yang diaudit (misal: 'App\Models\Pembayaran')
     * @param int|string $auditableId ID dari entitas yang diaudit
     * @param array|null $before Data status/nominal/saldo sebelum aksi dieksekusi
     * @param array|null $after Data status/nominal/saldo sesudah aksi dieksekusi
     * @param int|null $adminId ID admin yang mengeksekusi
     * @param Request|null $request Instance request untuk menangkap IP & User Agent
     * @return AdminAuditLog|null
     */
    public static function log(
        string $action,
        string $auditableType,
        int|string $auditableId,
        ?array $before = null,
        ?array $after = null,
        ?int $adminId = null,
        ?Request $request = null
    ): ?AdminAuditLog {
        try {
            $req = $request ?: request();
            $currentAdminId = $adminId ?: (Auth::check() ? Auth::id() : 1);

            return AdminAuditLog::create([
                'admin_id'       => $currentAdminId,
                'action'         => $action,
                'auditable_type' => $auditableType,
                'auditable_id'   => (int) $auditableId,
                'payload_before' => $before,
                'payload_after'  => $after,
                'ip_address'     => $req ? $req->ip() : null,
                'user_agent'     => $req ? substr((string) $req->userAgent(), 0, 500) : null,
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[AuditLogService] Gagal mencatat admin audit log: ' . $e->getMessage(), [
                'action'       => $action,
                'auditable_id' => $auditableId,
            ]);
            return null;
        }
    }
}
