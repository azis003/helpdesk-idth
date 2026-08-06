<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'requester_id' => ['nullable', 'integer', 'exists:users,id'],
            'service_type_id' => ['required', 'integer', 'exists:service_types,id'],
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:10000'],
            'priority' => ['required', Rule::in(array_keys(Priority::labels()))],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'fields' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'array'],
            'attachments.*.*' => ['nullable', 'file', 'max:1048576'],
        ];
    }

    public function messages(): array
    {
        return [
            'requester_id.integer' => 'Pemohon yang dipilih tidak valid.',
            'requester_id.exists' => 'Pemohon yang dipilih tidak ditemukan.',
            'service_type_id.required' => 'Layanan wajib dipilih.',
            'service_type_id.exists' => 'Layanan yang dipilih tidak ditemukan.',
            'subject.required' => 'Ringkasan permintaan wajib diisi.',
            'subject.max' => 'Ringkasan permintaan maksimal 150 karakter.',
            'description.required' => 'Deskripsi permintaan wajib diisi.',
            'description.max' => 'Deskripsi permintaan maksimal 10.000 karakter.',
            'priority.required' => 'Prioritas usulan wajib dipilih.',
            'priority.in' => 'Prioritas usulan tidak valid.',
            'room_id.exists' => 'Lokasi yang dipilih tidak ditemukan.',
            'fields.array' => 'Format field layanan tidak valid.',
            'attachments.array' => 'Format lampiran tidak valid.',
            'attachments.*.array' => 'Format kelompok lampiran tidak valid.',
            'attachments.*.*.file' => 'Salah satu lampiran tidak valid.',
            'attachments.*.*.max' => 'Ukuran salah satu lampiran terlalu besar.',
        ];
    }
}
