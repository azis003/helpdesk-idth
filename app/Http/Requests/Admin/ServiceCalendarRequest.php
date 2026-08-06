<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'timezone' => ['required', 'string', Rule::in(['Asia/Jakarta'])],
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*' => ['integer', 'distinct', 'between:1,7'],
            'opens_at' => ['required', 'date_format:H:i'],
            'closes_at' => ['required', 'date_format:H:i'],
            'holidays_text' => ['nullable', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'timezone.in' => 'Zona waktu kebijakan harus Asia/Jakarta.',
            'working_days.required' => 'Pilih minimal satu hari kerja.',
            'working_days.min' => 'Pilih minimal satu hari kerja.',
            'working_days.*.between' => 'Hari kerja tidak valid.',
            'opens_at.date_format' => 'Jam mulai harus menggunakan format HH:MM.',
            'closes_at.date_format' => 'Jam selesai harus menggunakan format HH:MM.',
        ];
    }

    /** @return array{timezone:string,working_days:list<int>,opens_at:string,closes_at:string,holidays:list<array{holiday_date:string,name:string}>} */
    public function payload(): array
    {
        $data = $this->validated();
        $opensAt = (string) $data['opens_at'];
        $closesAt = (string) $data['closes_at'];

        if ($opensAt >= $closesAt) {
            throw ValidationException::withMessages([
                'closes_at' => 'Jam selesai harus setelah jam mulai.',
            ]);
        }

        return [
            'timezone' => $data['timezone'],
            'working_days' => collect($data['working_days'])->map(fn ($day): int => (int) $day)->sort()->values()->all(),
            'opens_at' => $opensAt,
            'closes_at' => $closesAt,
            'holidays' => $this->parseHolidays((string) ($data['holidays_text'] ?? '')),
        ];
    }

    /** @return list<array{holiday_date:string,name:string}> */
    private function parseHolidays(string $text): array
    {
        $holidays = [];
        $dates = [];

        foreach (preg_split('/\r?\n/', $text) ?: [] as $lineNumber => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            [$date, $name] = array_pad(explode('|', $line, 2), 2, null);
            $date = trim((string) $date);
            $name = trim((string) $name);
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date);

            if ($parsed === false || $parsed->format('Y-m-d') !== $date || $name === '' || mb_strlen($name) > 150) {
                throw ValidationException::withMessages([
                    'holidays_text' => 'Hari libur baris '.($lineNumber + 1).' harus menggunakan format YYYY-MM-DD|Nama hari libur dan nama maksimal 150 karakter.',
                ]);
            }

            if (in_array($date, $dates, true)) {
                throw ValidationException::withMessages([
                    'holidays_text' => "Tanggal hari libur {$date} tidak boleh dicatat lebih dari sekali.",
                ]);
            }

            $dates[] = $date;
            $holidays[] = ['holiday_date' => $date, 'name' => $name];
        }

        return $holidays;
    }
}
