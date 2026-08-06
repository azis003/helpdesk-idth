<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->string('key', 100);
            $table->string('label', 150);
            $table->text('help_text')->nullable();
            $table->string('field_type', 30);
            $table->string('visibility', 20)->default('requester');
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->json('validation_rules')->nullable();
            $table->json('visibility_rules')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('supersedes_id')->nullable()->constrained('service_field_definitions')->nullOnDelete();
            $table->timestamps();
            $table->unique(['service_type_id', 'key', 'version']);
            $table->index(['service_type_id', 'key', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_field_definitions');
    }
};
