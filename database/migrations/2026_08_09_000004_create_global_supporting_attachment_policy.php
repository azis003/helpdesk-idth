<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'application/rtf',
        ];
        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf',
        ];

        DB::table('attachment_policies')->updateOrInsert(
            [
                'service_type_id' => null,
                'type_key' => 'supporting',
            ],
            [
                'label' => 'Lampiran pendukung',
                'max_file_size_kb' => 10240,
                'max_file_count' => 5,
                'allowed_mimes' => json_encode($allowedMimes),
                'allowed_extensions' => json_encode($allowedExtensions),
                'visibility' => 'both',
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        DB::table('attachment_policies')
            ->whereNull('service_type_id')
            ->where('type_key', 'supporting')
            ->delete();
    }
};
