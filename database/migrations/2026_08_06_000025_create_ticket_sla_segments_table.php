<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_sla_segments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('state', 20)->index();
            $table->string('reason', 40)->nullable();
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->nullOnDelete();
            $table->unsignedSmallInteger('target_working_days')->nullable();
            $table->foreignId('calendar_id')->nullable()->constrained('service_calendars')->nullOnDelete();
            $table->unsignedInteger('calendar_version')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['ticket_id', 'started_at']);
            $table->index(['ticket_id', 'state', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_sla_segments');
    }
};
