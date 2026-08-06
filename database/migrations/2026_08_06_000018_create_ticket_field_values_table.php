<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_field_definition_id')->nullable()->constrained('service_field_definitions')->nullOnDelete();
            $table->string('field_key', 100);
            $table->string('label_snapshot', 150);
            $table->string('field_type_snapshot', 30);
            $table->unsignedInteger('version_snapshot')->default(1);
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['ticket_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_field_values');
    }
};
