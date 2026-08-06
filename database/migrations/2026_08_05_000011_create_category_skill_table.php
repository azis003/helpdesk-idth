<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_skill', function (Blueprint $table) {
            $table->foreignId('problem_category_id')->constrained('problem_categories')->restrictOnDelete();
            $table->foreignId('skill_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->primary(['problem_category_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_skill');
    }
};
