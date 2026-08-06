<?php

namespace App\Services;

use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DynamicFieldValidator
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, array{definition:ServiceFieldDefinition,value:mixed}>
     */
    public function validate(ServiceType $serviceType, array $input): array
    {
        $definitions = $serviceType->activeFieldDefinitions
            ->filter(fn (ServiceFieldDefinition $field): bool => in_array($field->visibility, ['requester', 'both'], true))
            ->values();
        $knownKeys = $definitions->pluck('key')->all();
        $errors = [];

        foreach (array_keys($input) as $key) {
            if (! in_array($key, $knownKeys, true)) {
                $errors["fields.{$key}"] = 'Field formulir yang dikirim tidak tersedia pada layanan yang dipilih.';
            }
        }

        $values = [];

        foreach ($definitions as $field) {
            $value = $input[$field->key] ?? null;
            $rules = $this->rulesFor($field);
            $validator = Validator::make(
                ['value' => $value],
                ['value' => $rules],
                $this->messagesFor($field),
            );

            if ($validator->fails()) {
                $errors["fields.{$field->key}"] = $validator->errors()->first('value');

                continue;
            }

            if ($field->field_type === 'multiselect' && is_array($value)) {
                $allowed = $field->options->where('is_active', true)->pluck('value')->all();

                if (array_diff($value, $allowed) !== []) {
                    $errors["fields.{$field->key}"] = "Pilihan {$field->label} tidak valid.";

                    continue;
                }
            }

            $values[$field->key] = [
                'definition' => $field,
                'value' => $this->normalise($field, $value),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $values;
    }

    /**
     * @return list<mixed>
     */
    private function rulesFor(ServiceFieldDefinition $field): array
    {
        $rules = [];

        if ($field->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        $rules[] = match ($field->field_type) {
            'text', 'textarea' => 'string',
            'number' => 'numeric',
            'date' => 'date',
            'datetime' => 'date_format:Y-m-d\\TH:i',
            'select' => Rule::in($field->options->where('is_active', true)->pluck('value')->all()),
            'multiselect' => 'array',
            'boolean' => 'boolean',
            default => 'string',
        };

        foreach ($field->validation_rules ?? [] as $rule) {
            if (is_string($rule) && ! in_array($rule, $rules, true)) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function messagesFor(ServiceFieldDefinition $field): array
    {
        $label = $field->label;

        return [
            'value.required' => "{$label} wajib diisi.",
            'value.string' => "{$label} harus berupa teks.",
            'value.numeric' => "{$label} harus berupa angka.",
            'value.integer' => "{$label} harus berupa angka bulat.",
            'value.date' => "{$label} harus berupa tanggal yang valid.",
            'value.date_format' => "Format {$label} tidak valid.",
            'value.array' => "{$label} harus berupa pilihan yang valid.",
            'value.boolean' => "{$label} harus berupa pilihan Ya atau Tidak.",
            'value.in' => "Pilihan {$label} tidak valid.",
            'value.email' => "{$label} harus berupa alamat email yang valid.",
            'value.url' => "{$label} harus berupa URL yang valid.",
            'value.min' => "Nilai {$label} belum memenuhi batas minimal.",
            'value.max' => "Nilai {$label} melebihi batas maksimal.",
            'value.between' => "Nilai {$label} berada di luar batas yang diizinkan.",
        ];
    }

    private function normalise(ServiceFieldDefinition $field, mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if ($field->field_type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        if ($field->field_type === 'number' && is_numeric($value)) {
            return str_contains((string) $value, '.') ? (float) $value : (int) $value;
        }

        if ($field->field_type === 'multiselect' && is_array($value)) {
            return array_values($value);
        }

        return $value;
    }
}
