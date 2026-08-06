<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'assigned_to_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to_id.required' => 'Teknisi Tier 2 wajib dipilih.',
            'assigned_to_id.integer' => 'Teknisi Tier 2 yang dipilih tidak valid.',
            'assigned_to_id.exists' => 'Teknisi Tier 2 yang dipilih tidak ditemukan.',
            'reason.max' => 'Alasan penugasan maksimal 1.000 karakter.',
        ];
    }
}
