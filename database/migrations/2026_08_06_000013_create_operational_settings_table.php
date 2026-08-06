<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100);
            $table->json('value');
            $table->string('value_type', 30)->default('integer');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('effective_from')->useCurrent();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['key', 'version']);
            $table->index(['key', 'is_active']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            $predicate = $driver === 'pgsql' ? 'is_active = true' : 'is_active = 1';
            DB::statement("CREATE UNIQUE INDEX operational_settings_one_active_per_key ON operational_settings (key) WHERE {$predicate}");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS operational_settings_one_active_per_key');
        }

        Schema::dropIfExists('operational_settings');
    }
};
