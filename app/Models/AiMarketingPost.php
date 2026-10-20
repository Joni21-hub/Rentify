<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AiMarketingPost extends Model
{
    use HasFactory;

    protected $table = 'ai_marketing_posts';

    protected $fillable = [
        'tanggal',
        'day_number',
        'day_type',
        'target_audience',
        'tema',
        'kategori_barang',
        'persona_vendor',
        'hook_masalah',
        'naskah_video',
        'teks_di_layar',
        'caption',
        'visual_prompt',
        'visual_media_url',
        'ai_source',
        'status',
        'posted_to_instagram',
        'posted_to_tiktok',
        'instagram_post_id',
        'tiktok_post_id',
        'posted_at',
        'log_info',
    ];

    protected $casts = [
        'tanggal'             => 'date',
        'naskah_video'        => 'array',
        'teks_di_layar'       => 'array',
        'posted_to_instagram' => 'boolean',
        'posted_to_tiktok'    => 'boolean',
        'posted_at'           => 'datetime',
    ];

    public function isCustomerTarget(): bool
    {
        return $this->target_audience === 'customer';
    }

    public function isVendorTarget(): bool
    {
        return $this->target_audience === 'vendor';
    }

    public function getTargetBadgeAttribute(): array
    {
        if ($this->isCustomerTarget()) {
            return [
                'text'  => 'Penyewa (Customer)',
                'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'icon'  => 'fa-users',
                'desc'  => 'Edukasi Sewa Hemat vs Beli Mahal'
            ];
        }

        return [
            'text'  => 'Pemilik Barang (Vendor)',
            'color' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'icon'  => 'fa-store',
            'desc'  => 'Monetisasi Barang Nganggur'
        ];
    }
}
