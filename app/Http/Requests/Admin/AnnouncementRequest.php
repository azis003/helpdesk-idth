<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true
            && $this->user()->hasAnyRole([Role::SuperAdmin, Role::AgenTier1]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'starts_at' => ['required', 'date_format:Y-m-d\\TH:i'],
            'ends_at' => ['nullable', 'date_format:Y-m-d\\TH:i', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'body.required' => 'Isi pengumuman wajib diisi.',
            'starts_at.required' => 'Waktu mulai wajib diisi.',
            'starts_at.date_format' => 'Format waktu mulai tidak valid.',
            'ends_at.date_format' => 'Format waktu berakhir tidak valid.',
            'ends_at.after_or_equal' => 'Waktu berakhir harus setelah waktu mulai.',
            'is_active.boolean' => 'Status pengumuman tidak valid.',
        ];
    }
}
