<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('organization_name', 150);
            $table->string('application_name', 100);
            $table->string('tagline', 160)->nullable();
            $table->string('footer_text', 255)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('logo_disk', 50)->default('local');
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
            DB::statement("CREATE UNIQUE INDEX organization_settings_one_active ON organization_settings (is_active) WHERE {$predicate}");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS organization_settings_one_active');
        }

        Schema::dropIfExists('organization_settings');
    }
};
