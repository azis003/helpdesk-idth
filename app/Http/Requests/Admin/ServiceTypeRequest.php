<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'ticket_class' => ['required', Rule::in(['INC', 'REQ', 'CHG'])],
            'uses_sla' => ['sometimes', 'boolean'],
            'target_working_days' => ['required_if:uses_sla,1', 'nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama layanan wajib diisi.',
            'ticket_class.required' => 'Kelas nomor wajib dipilih.',
            'ticket_class.in' => 'Kelas nomor harus INC, REQ, atau CHG.',
            'target_working_days.required_if' => 'Target SLA wajib diisi jika SLA digunakan.',
            'target_working_days.integer' => 'Target SLA harus berupa jumlah hari kerja.',
            'target_working_days.min' => 'Target SLA minimal 1 hari kerja.',
            'target_working_days.max' => 'Target SLA maksimal 365 hari kerja.',
        ];
    }
}
