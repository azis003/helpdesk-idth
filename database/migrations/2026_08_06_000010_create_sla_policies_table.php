<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('target_working_days')->nullable();
            $table->boolean('uses_sla')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('effective_from')->useCurrent();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['service_type_id', 'version']);
            $table->index(['service_type_id', 'is_active']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            $predicate = $driver === 'pgsql' ? 'is_active = true' : 'is_active = 1';
            DB::statement("CREATE UNIQUE INDEX sla_policies_one_active_per_service ON sla_policies (service_type_id) WHERE {$predicate}");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS sla_policies_one_active_per_service');
        }

        Schema::dropIfExists('sla_policies');
    }
};
