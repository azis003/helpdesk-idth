<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approver_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->string('replacement_reason', 500)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            $predicate = $driver === 'pgsql' ? 'is_active = true' : 'is_active = 1';
            DB::statement("CREATE UNIQUE INDEX approver_assignments_one_active ON approver_assignments (is_active) WHERE {$predicate}");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS approver_assignments_one_active');
        }

        Schema::dropIfExists('approver_assignments');
    }
};
