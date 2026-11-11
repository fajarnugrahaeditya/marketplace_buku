<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        // akses sudah dijaga middleware role:author, kepemilikan buku dicek di controller
        return true;
    }

    public function rules(): array
    {
        $minPrice = (int) Setting::get('min_price', 10000);
        $isUpdate = $this->route('book') !== null;

        return [
            'title' => ['required', 'string', 'max:200'],
            'synopsis' => ['nullable', 'string', 'max:5000'],
            'price' => [
                'required', 'integer', 'min:0', 'max:100000000',
                function (string $attribute, mixed $value, \Closure $fail) use ($minPrice) {
                    if ((int) $value !== 0 && (int) $value < $minPrice) {
                        $fail('Harga harus Rp0 (gratis) atau minimal Rp'.number_format($minPrice, 0, ',', '.').'.');
                    }
                },
            ],
            'license_type' => ['required', Rule::in(['own_work', 'creative_commons', 'public_domain', 'with_permission'])],
            'source_url' => [
                Rule::requiredIf(fn () => $this->input('license_type') !== 'own_work'),
                'nullable', 'url', 'max:255',
            ],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'cover' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'full_file' => [$isUpdate ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:20480'],
            'preview_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.max' => 'Judul maksimal 200 karakter.',
            'synopsis.max' => 'Sinopsis maksimal 5000 karakter.',
            'price.required' => 'Harga wajib diisi (isi 0 untuk buku gratis).',
            'price.integer' => 'Harga harus berupa angka bulat.',
            'price.min' => 'Harga tidak boleh negatif.',
            'license_type.required' => 'Jenis lisensi wajib dipilih.',
            'license_type.in' => 'Jenis lisensi tidak valid.',
            'source_url.required' => 'Tautan sumber atau bukti izin wajib diisi untuk karya yang bukan milik sendiri.',
            'source_url.url' => 'Tautan sumber harus berupa alamat URL yang valid (diawali http:// atau https://).',
            'category_ids.required' => 'Pilih minimal satu kategori.',
            'category_ids.min' => 'Pilih minimal satu kategori.',
            'category_ids.*.exists' => 'Kategori yang dipilih tidak valid.',
            'cover.required' => 'Cover wajib diunggah.',
            'cover.image' => 'Cover harus berupa gambar.',
            'cover.mimes' => 'Cover harus berformat JPG, PNG, atau WEBP.',
            'cover.max' => 'Ukuran cover maksimal 2 MB.',
            'full_file.required' => 'File buku (PDF) wajib diunggah.',
            'full_file.mimes' => 'File buku harus berformat PDF.',
            'full_file.max' => 'Ukuran file buku maksimal 20 MB.',
            'preview_file.mimes' => 'File pratinjau harus berformat PDF.',
            'preview_file.max' => 'Ukuran file pratinjau maksimal 5 MB.',
        ];
    }
}
