<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attachment_policies')
            ->whereNull('service_type_id')
            ->where('type_key', 'supporting')
            ->update([
                'allowed_mimes' => json_encode([
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
                ]),
                'allowed_extensions' => json_encode([
                    'jpg', 'jpeg', 'png', 'gif', 'webp',
                    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf',
                ]),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('attachment_policies')
            ->whereNull('service_type_id')
            ->where('type_key', 'supporting')
            ->update([
                'allowed_mimes' => json_encode([]),
                'allowed_extensions' => json_encode([]),
                'updated_at' => now(),
            ]);
    }
};
