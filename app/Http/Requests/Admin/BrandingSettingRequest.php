<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class BrandingSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:150'],
            'application_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=1600,max_height=600'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization_name.required' => 'Nama instansi wajib diisi.',
            'organization_name.max' => 'Nama instansi maksimal 150 karakter.',
            'application_name.required' => 'Nama aplikasi wajib diisi.',
            'application_name.max' => 'Nama aplikasi maksimal 100 karakter.',
            'tagline.max' => 'Tagline maksimal 160 karakter.',
            'footer_text.max' => 'Teks footer maksimal 255 karakter.',
            'logo.image' => 'Logo harus berupa gambar yang valid.',
            'logo.mimes' => 'Logo hanya boleh berformat JPG, PNG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'logo.dimensions' => 'Ukuran logo maksimal 1600 × 600 piksel.',
            'remove_logo.boolean' => 'Pilihan penghapusan logo tidak valid.',
        ];
    }

    /** @return array{organization_name:string,application_name:string,tagline:?string,footer_text:?string,remove_logo:bool} */
    public function payload(): array
    {
        $data = $this->validated();

        return [
            'organization_name' => trim((string) $data['organization_name']),
            'application_name' => trim((string) $data['application_name']),
            'tagline' => filled($data['tagline'] ?? null) ? trim((string) $data['tagline']) : null,
            'footer_text' => filled($data['footer_text'] ?? null) ? trim((string) $data['footer_text']) : null,
            'remove_logo' => (bool) ($data['remove_logo'] ?? false),
        ];
    }
}
