<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class OperationalSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'sla_warning_percent' => ['required', 'integer', 'between:1,100'],
            'requester_wait_working_days' => ['required', 'integer', 'between:1,365'],
            'confirmation_wait_working_days' => ['required', 'integer', 'between:1,365'],
            'reopen_window_working_days' => ['required', 'integer', 'between:1,365'],
            'max_reopen_count' => ['required', 'integer', 'between:1,20'],
        ];
    }

    public function messages(): array
    {
        return [
            'sla_warning_percent.between' => 'Ambang mendekati batas SLA harus antara 1 sampai 100 persen.',
            '*.required' => 'Semua batas operasional wajib diisi.',
            '*.integer' => 'Nilai batas operasional harus berupa angka bulat.',
            'requester_wait_working_days.between' => 'Batas Menunggu Pemohon harus antara 1 sampai 365 hari kerja.',
            'confirmation_wait_working_days.between' => 'Batas Menunggu Konfirmasi harus antara 1 sampai 365 hari kerja.',
            'reopen_window_working_days.between' => 'Jendela buka kembali harus antara 1 sampai 365 hari kerja.',
            'max_reopen_count.between' => 'Maksimum buka kembali harus antara 1 sampai 20 kali.',
        ];
    }

    /** @return array<string, int> */
    public function payload(): array
    {
        return collect($this->validated())
            ->map(fn ($value): int => (int) $value)
            ->all();
    }
}
