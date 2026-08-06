<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class SlaPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'policies' => ['required', 'array', 'min:1'],
            'policies.*.service_type_id' => ['required', 'integer', 'distinct', 'exists:service_types,id'],
            'policies.*.uses_sla' => ['required', 'boolean'],
            'policies.*.target_working_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'policies.required' => 'Daftar kebijakan SLA wajib dikirim.',
            'policies.*.service_type_id.exists' => 'Layanan untuk kebijakan SLA tidak tersedia.',
            'policies.*.uses_sla.required' => 'Status SLA setiap layanan wajib ditentukan.',
            'policies.*.target_working_days.integer' => 'Target SLA harus berupa jumlah hari kerja.',
            'policies.*.target_working_days.min' => 'Target SLA minimal 1 hari kerja.',
            'policies.*.target_working_days.max' => 'Target SLA maksimal 365 hari kerja.',
        ];
    }

    /** @return list<array{service_type_id:int, uses_sla:bool, target_working_days:int|null}> */
    public function payload(): array
    {
        return collect($this->validated('policies'))
            ->map(fn (array $policy): array => [
                'service_type_id' => (int) $policy['service_type_id'],
                'uses_sla' => (bool) $policy['uses_sla'],
                'target_working_days' => $policy['uses_sla']
                    ? (int) ($policy['target_working_days'] ?? 0)
                    : null,
            ])
            ->values()
            ->all();
    }
}
