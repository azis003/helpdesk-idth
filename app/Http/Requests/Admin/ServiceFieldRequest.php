<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Services\ServiceCatalogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceFieldRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $field = $this->route('serviceFieldDefinition');

        if ($field !== null && ! $this->filled('key')) {
            $this->merge(['key' => $field->key]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:100', 'alpha_dash'],
            'label' => ['required', 'string', 'max:150'],
            'help_text' => ['nullable', 'string', 'max:500'],
            'field_type' => ['required', Rule::in(array_keys(ServiceCatalogService::FIELD_TYPES))],
            'visibility' => ['required', Rule::in(array_keys(ServiceCatalogService::VISIBILITIES))],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'options_text' => ['nullable', 'string', 'max:5000'],
            'validation_rules_text' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $data = $this->validated();
        $data['options'] = $this->parseOptions($data['options_text'] ?? '');
        $data['validation_rules'] = $this->parseRules($data['validation_rules_text'] ?? '');
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    /** @return list<array{value:string,label:string,sort_order:int,is_active:bool}> */
    private function parseOptions(string $text): array
    {
        $options = [];

        foreach (preg_split('/\r?\n/', $text) ?: [] as $index => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            [$value, $label] = array_pad(explode('|', $line, 2), 2, null);
            $label = trim((string) ($label ?? $value));
            $value = trim((string) $value);

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
