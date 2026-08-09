<?php

namespace App\Http\Requests;

use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class CompleteTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        $ticket = $this->route('ticket');
        $isDataExport = $ticket instanceof Ticket
            && ($ticket->service_type_code_snapshot === 'SVC-02'
                || $ticket->serviceType?->code === 'SVC-02');

        return [
            'solution' => ['required', 'string', 'max:20000'],
            'data_export_result' => $isDataExport
                ? ['required', 'file', 'max:'.Attachment::DATA_EXPORT_RESULT_MAX_SIZE_KB]
                : ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'solution.required' => 'Solusi wajib diisi sebelum tiket menunggu konfirmasi.',
            'solution.max' => 'Solusi maksimal 20.000 karakter.',
            'data_export_result.required' => 'Hasil tarik data wajib diunggah sebelum tiket menunggu konfirmasi.',
            'data_export_result.file' => 'Berkas hasil tarik data tidak valid.',
            'data_export_result.max' => 'Ukuran hasil tarik data maksimal 10 MB.',
            'data_export_result.prohibited' => 'Pengunggahan hasil tarik data hanya tersedia untuk layanan Permintaan tarik data.',
        ];
    }
}
