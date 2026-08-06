<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('previous_status', 40)->nullable();
            $table->foreignId('previous_assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('previous_tier', 40)->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->foreignId('transferred_from_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();
            $table->index(['approver_id', 'status']);
            $table->index(['ticket_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
