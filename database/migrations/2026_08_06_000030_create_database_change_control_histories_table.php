<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_change_control_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('database_change_control_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('action', 80);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['ticket_id', 'occurred_at']);
            $table->index(['database_change_control_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_change_control_histories');
    }
};
