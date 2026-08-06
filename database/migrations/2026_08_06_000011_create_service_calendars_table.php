<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_calendars', function (Blueprint $table): void {
            $table->id();
            $table->string('timezone', 64);
            $table->json('working_days');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('effective_from')->useCurrent();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique('version');
        });

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            $predicate = $driver === 'pgsql' ? 'is_active = true' : 'is_active = 1';
            DB::statement("CREATE UNIQUE INDEX service_calendars_one_active ON service_calendars (is_active) WHERE {$predicate}");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS service_calendars_one_active');
        }

        Schema::dropIfExists('service_calendars');
    }
};
