<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('action', 60);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['ticket_id', 'occurred_at']);
        });

        Schema::create('ticket_assignment_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_tier', 40)->nullable();
            $table->string('to_tier', 40)->nullable();
            $table->string('action', 60);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('suggestions')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['ticket_id', 'occurred_at']);
        });

        Schema::create('ticket_priority_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('from_priority', 20)->nullable();
            $table->string('to_priority', 20);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['ticket_id', 'occurred_at']);
        });

        Schema::create('ticket_category_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_category_id')->nullable()->constrained('problem_categories')->nullOnDelete();
            $table->foreignId('to_category_id')->nullable()->constrained('problem_categories')->nullOnDelete();
            $table->string('from_category_name', 150)->nullable();
            $table->string('to_category_name', 150)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['ticket_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_category_histories');
        Schema::dropIfExists('ticket_priority_histories');
        Schema::dropIfExists('ticket_assignment_histories');
        Schema::dropIfExists('ticket_status_histories');
    }
};
