<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_field_value_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_field_definition_id')
                ->nullable()
                ->constrained('service_field_definitions')
                ->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('field_key', 100);
            $table->string('label_snapshot', 150);
            $table->string('field_type_snapshot', 30);
            $table->string('visibility_snapshot', 20);
            $table->unsignedInteger('version_snapshot');
            $table->string('change_type', 20);
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            $table->index(['ticket_id', 'occurred_at']);
            $table->index(['ticket_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_field_value_histories');
    }
};
