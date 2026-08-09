<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_types')
            ->whereNull('ticket_class')
            ->update(['ticket_class' => 'REQ']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE service_types ALTER COLUMN ticket_class SET NOT NULL');

            return;
        }

        Schema::table('service_types', function (Blueprint $table): void {
            $table->string('ticket_class', 3)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE service_types ALTER COLUMN ticket_class DROP NOT NULL');

            return;
        }

        Schema::table('service_types', function (Blueprint $table): void {
            $table->string('ticket_class', 3)->nullable()->change();
        });
    }
};
