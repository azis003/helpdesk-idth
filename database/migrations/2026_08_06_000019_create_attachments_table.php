<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attachment_policy_id')->nullable()->constrained('attachment_policies')->nullOnDelete();
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type_key', 60);
            $table->string('type_label_snapshot', 150);
            $table->string('original_name', 255);
            $table->string('storage_disk', 50)->default('local');
            $table->string('storage_path', 500);
            $table->unsignedBigInteger('size_bytes');
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('sha256', 64)->nullable();
            $table->string('visibility', 20)->default('both');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['ticket_id', 'type_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
