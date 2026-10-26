<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMomentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'media' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value->isValid()) {
                        return $fail('Berkas media gagal diunggah ke server atau melebihi batas upload.');
                    }

                    $mime = $value->getMimeType() ?: '';
                    $ext = strtolower($value->getClientOriginalExtension() ?: '');

                    // Semua jenis foto: jpeg, png, webp, heic, heif, gif, bmp, tiff, svg, avif, raw, cr2, nef, arw, dng, psd, dsb.
                    $imageExtensions = [
                        'jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'gif', 'bmp', 'tiff', 'tif',
                        'svg', 'avif', 'ico', 'raw', 'cr2', 'nef', 'arw', 'dng', 'psd', 'eps', 'ai'
                    ];

                    // Semua jenis video: mp4, mov, quicktime, webm, mkv, avi, wmv, flv, 3gp, 3g2, m4v, ts, mpeg, mpg, ogv, vob, m2ts, divx, rm, rmvb, asf, dsb.
                    $videoExtensions = [
                        'mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp',
                        '3g2', 'm4v', 'ts', 'mpeg', 'mpg', 'ogv', 'vob', 'm2ts', 'divx', 'rm', 'rmvb', 'asf', 'm4p', 'f4v'
                    ];

                    $isImage = str_starts_with($mime, 'image/') || in_array($ext, $imageExtensions);
                    $isVideo = str_starts_with($mime, 'video/') || in_array($ext, $videoExtensions);

                    // Menolak format non-media/dokumen non-visual
                    if (!$isImage && !$isVideo) {
                        return $fail('Format media tidak didukung. Harap unggah berkas foto atau video.');
                    }

                    // Bebas tanpa batasan ukuran file (foto & video)
                },
            ],
            'media_type' => ['nullable', 'string', 'in:image,video'],
            'video_duration_seconds' => ['nullable', 'numeric', 'min:0'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'product_ids' => ['nullable', 'array', 'max:3'],
            'product_ids.*' => ['integer', 'exists:barang,id'],
        ];
    }

    /**
     * Custom message for validation errors.
     */
    public function messages(): array
    {
        return [
            'media.required' => 'Berkas media (foto/video) wajib diunggah.',
            'media.file' => 'Berkas media harus berupa file valid.',
            'media.uploaded' => 'Berkas media gagal diunggah ke server. Pastikan ukuran file tidak melebihi batas konfigurasi server PHP/Nginx.',
            'caption.max' => 'Deskripsi momen maksimal 1000 karakter.',
            'product_ids.max' => 'Maksimal produk yang dapat ditandai adalah 3 produk.',
            'product_ids.*.exists' => 'Produk yang ditandai tidak ditemukan di katalog.',
        ];
    }
}
