<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $table = 'refunds';

    protected $attributes = [
        'status' => 'MENUNGGU_DATA_REKENING',
    ];

    protected $fillable = [
        'order_id',
        'penyewaan_id',
        'user_id',
        'nominal_refund',
        'tipe_refund',
        'metode_refund',
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'status',
        'bukti_transfer_refund',
        'processed_by',
        'processed_at',
        'catatan',
    ];

    protected $casts = [
        'nominal_refund' => 'decimal:2',
        'processed_at'   => 'datetime',
    ];

    /**
     * Relasi ke Order / Penyewaan
     */
    public function order()
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Alias relasi ke Penyewaan
     */
    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'order_id');
    }

    /**
     * Relasi ke Pelanggan / User yang menerima refund
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Admin / User yang memproses refund
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
