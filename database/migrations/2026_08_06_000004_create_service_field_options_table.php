<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_field_definition_id')->constrained()->restrictOnDelete();
            $table->string('value', 100);
            $table->string('label', 150);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['service_field_definition_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_field_options');
    }
};
