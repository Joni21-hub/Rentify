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
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('media_type')) {
            $type = strtolower((string) $this->input('media_type'));
            if ($type === 'photo') {
                $this->merge(['media_type' => 'image']);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $imageExtensions = [
            'jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'gif', 'bmp', 'tiff', 'tif',
            'svg', 'avif', 'ico', 'raw', 'cr2', 'nef', 'arw', 'dng', 'psd', 'eps', 'ai'
        ];

        $videoExtensions = [
            'mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp',
            '3g2', 'm4v', 'ts', 'mpeg', 'mpg', 'ogv', 'vob', 'm2ts', 'divx', 'rm', 'rmvb', 'asf', 'm4p', 'f4v'
        ];

        return [
            'media' => [
                'required',
                function ($attribute, $value, $fail) use ($imageExtensions, $videoExtensions) {
                    $validateSingleFile = function ($file, bool $allowVideo = true) use ($imageExtensions, $videoExtensions, $fail) {
                        if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                            return $fail('Berkas media gagal diunggah ke server atau melebihi batas upload.');
                        }

                        $mime = $file->getMimeType() ?: '';
                        $ext = strtolower($file->getClientOriginalExtension() ?: '');

                        $isImage = str_starts_with($mime, 'image/') || in_array($ext, $imageExtensions);
                        $isVideo = str_starts_with($mime, 'video/') || in_array($ext, $videoExtensions);

                        if (!$isImage && !$isVideo) {
                            return $fail('Format media tidak didukung. Harap unggah berkas foto atau video.');
                        }

                        if (!$allowVideo && $isVideo) {
                            return $fail('Unggah lebih dari satu berkas hanya diperbolehkan untuk foto/gambar.');
                        }
                    };

                    if (is_array($value)) {
                        if (count($value) === 0) {
                            return $fail('Berkas media (foto/video) wajib diunggah.');
                        }
                        if (count($value) > 10) {
                            return $fail('Maksimal foto yang dapat diunggah dalam satu momen adalah 10 foto.');
                        }
                        $allowVideo = (count($value) === 1);
                        foreach ($value as $file) {
                            $validateSingleFile($file, $allowVideo);
                        }
                    } else {
                        $validateSingleFile($value, true);
                    }
                },
            ],
            'media.*' => ['nullable'],
            'media_type' => ['nullable', 'string', 'in:image,video,photo'],
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
