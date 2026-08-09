<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateServiceTypeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $code = strtoupper(trim((string) $this->input('code')));
            $this->merge(['code' => $code]);

            if ($code === 'SVC-07') {
                $this->merge([
                    'uses_sla' => false,
                    'target_working_days' => null,
                ]);
            }
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('service_types', 'code')],
            'name' => ['required', 'string', 'max:150'],
            'ticket_class' => ['required', Rule::in(['INC', 'REQ', 'CHG'])],
            'uses_sla' => ['required', 'boolean'],
            'target_working_days' => ['required_if:uses_sla,1', 'nullable', 'integer', 'min:1', 'max:365'],
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['integer', 'distinct', 'exists:skills,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fields' => ['nullable', 'array', 'max:100'],
            'fields.*.key' => ['required', 'string', 'max:100', 'alpha_dash', 'distinct'],
            'fields.*.label' => ['required', 'string', 'max:150'],
            'fields.*.help_text' => ['nullable', 'string', 'max:500'],
            'fields.*.field_type' => ['required', Rule::in(array_keys(\App\Services\ServiceCatalogService::FIELD_TYPES))],
            'fields.*.visibility' => ['required', Rule::in(array_keys(\App\Services\ServiceCatalogService::VISIBILITIES))],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'fields.*.options_text' => ['nullable', 'string', 'max:5000'],
            'fields.*.validation_rules_text' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode layanan wajib diisi.',
            'code.alpha_dash' => 'Kode layanan hanya boleh berisi huruf, angka, garis bawah, atau tanda hubung.',
            'code.unique' => 'Kode layanan sudah digunakan.',
            'name.required' => 'Jenis layanan wajib diisi.',
            'ticket_class.required' => 'Kategori layanan wajib dipilih.',
            'ticket_class.in' => 'Kategori layanan harus INC, REQ, atau CHG.',
            'uses_sla.required' => 'Status SLA wajib ditentukan.',
            'target_working_days.required_if' => 'Target SLA wajib diisi jika SLA digunakan.',
            'target_working_days.integer' => 'Target SLA harus berupa jumlah hari kerja.',
            'target_working_days.min' => 'Target SLA minimal 1 hari kerja.',
            'target_working_days.max' => 'Target SLA maksimal 365 hari kerja.',
            'skill_ids.array' => 'Daftar keahlian tidak valid.',
            'skill_ids.*.exists' => 'Salah satu keahlian tidak ditemukan.',
            'fields.*.key.required' => 'Kunci teknis field wajib diisi.',
            'fields.*.key.distinct' => 'Kunci teknis field tidak boleh sama.',
            'fields.*.label.required' => 'Nama field wajib diisi.',
            'fields.*.field_type.required' => 'Jenis field wajib dipilih.',
            'fields.*.visibility.required' => 'Visibilitas field wajib dipilih.',
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $data = $this->validated();
        $data['uses_sla'] = $data['code'] === 'SVC-07' ? false : (bool) $data['uses_sla'];
        $data['target_working_days'] = $data['uses_sla']
            ? (int) $data['target_working_days']
            : null;
        $data['skill_ids'] = collect($data['skill_ids'] ?? [])->map(fn ($id): int => (int) $id)->values()->all();
        $data['fields'] = collect($data['fields'] ?? [])
            ->values()
            ->map(function (array $field, int $index): array {
                try {
                    $options = $this->parseOptions($field['options_text'] ?? '');
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages([
                        "fields.{$index}.options_text" => $exception->errors()['options_text'][0] ?? 'Format pilihan field tidak valid.',
                    ]);
                }

                if (! in_array($field['field_type'], ['select', 'multiselect'], true) && $options !== []) {
                    throw ValidationException::withMessages([
                        "fields.{$index}.options_text" => 'Pilihan hanya dapat digunakan pada field dropdown.',
                    ]);
                }

                if (in_array($field['field_type'], ['select', 'multiselect'], true) && $options === []) {
                    throw ValidationException::withMessages([
                        "fields.{$index}.options_text" => 'Field dropdown wajib memiliki minimal satu pilihan.',
                    ]);
                }

                return [
                    'key' => $field['key'],
                    'label' => $field['label'],
                    'help_text' => $field['help_text'] ?? null,
                    'field_type' => $field['field_type'],
                    'visibility' => $field['visibility'],
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'sort_order' => (int) ($field['sort_order'] ?? ($index + 1)),
                    'options' => $options,
                    'validation_rules' => $this->parseRules($field['validation_rules_text'] ?? ''),
                ];
            })
            ->all();

        return $data;
    }

    /** @return list<array{value:string,label:string,sort_order:int,is_active:bool}> */
    private function parseOptions(string $text): array
    {
        $options = [];

        foreach (preg_split('/\r?\n/', $text) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            [$value, $label] = array_pad(explode('|', $line, 2), 2, null);
            $value = trim((string) $value);
            $label = trim((string) ($label ?? $value));

            if ($value === '' || $label === '') {
                throw ValidationException::withMessages([
                    'options_text' => 'Setiap pilihan harus ditulis dengan format nilai|label.',
                ]);
            }

            $options[] = [
                'value' => Str::slug($value, '_'),
                'label' => $label,
                'sort_order' => count($options),
                'is_active' => true,
            ];
        }

        return $options;
    }

    /** @return list<string> */
    private function parseRules(string $text): array
    {
        return collect(preg_split('/\r?\n/', $text) ?: [])
            ->map(fn (string $rule): string => trim($rule))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
