<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_type_skill', function (Blueprint $table) {
            $table->foreignId('service_type_id')->constrained('service_types')->restrictOnDelete();
            $table->foreignId('skill_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->primary(['service_type_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_type_skill');
    }
};
