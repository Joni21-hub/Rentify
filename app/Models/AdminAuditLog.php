<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    use HasFactory;

    protected $table = 'admin_audit_logs';

    /**
     * Disable standard updated_at column since schema only tracks created_at
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'admin_id',
        'action',
        'auditable_type',
        'auditable_id',
        'payload_before',
        'payload_after',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payload_before' => 'array',
        'payload_after'  => 'array',
        'created_at'     => 'datetime',
    ];

    /**
     * Relasi ke Admin (User) yang melakukan tindakan
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relasi polymorphic ke model yang diaudit (Voucher, Penyewaan, Refund, dll)
     */
    public function auditable()
    {
        return $this->morphTo();
    }
}
