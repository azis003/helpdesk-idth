<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $role = (string) env('DB_APP_ROLE', '');

        if ($role === '' || ! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $role)) {
            return;
        }

        $quotedRole = '"'.str_replace('"', '""', $role).'"';
        DB::statement("REVOKE UPDATE, DELETE ON TABLE audit_logs FROM {$quotedRole}");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $role = (string) env('DB_APP_ROLE', '');

        if ($role === '' || ! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $role)) {
            return;
        }

        $quotedRole = '"'.str_replace('"', '""', $role).'"';
        DB::statement("GRANT UPDATE, DELETE ON TABLE audit_logs TO {$quotedRole}");
    }
};
