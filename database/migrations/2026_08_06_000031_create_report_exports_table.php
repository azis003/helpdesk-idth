<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 50);
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('format', 10);
            $table->string('status', 20)->default('completed');
            $table->string('file_name', 255)->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->index(['type', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
