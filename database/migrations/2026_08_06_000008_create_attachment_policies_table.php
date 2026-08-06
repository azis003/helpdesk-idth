<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachment_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type_key', 60);
            $table->string('label', 150);
            $table->unsignedInteger('max_file_size_kb');
            $table->unsignedSmallInteger('max_file_count');
            $table->json('allowed_mimes')->nullable();
            $table->json('allowed_extensions')->nullable();
            $table->string('visibility', 20)->default('both');
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['service_type_id', 'type_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachment_policies');
    }
};
