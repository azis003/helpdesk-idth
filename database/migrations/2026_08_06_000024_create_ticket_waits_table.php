<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_waits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30)->index();
            $table->foreignId('started_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 40);
            $table->foreignId('from_assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_assigned_tier', 40)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('ended_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('end_reason', 40)->nullable();
            $table->boolean('timed_out')->default(false);
            $table->string('third_party_name', 150)->nullable();
            $table->date('follow_up_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['ticket_id', 'ended_at']);
            $table->index(['kind', 'ended_at', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_waits');
    }
};
