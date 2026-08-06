<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->foreignId('problem_category_id')->nullable()->after('service_type_variant_id')->constrained('problem_categories')->nullOnDelete();
            $table->foreignId('last_triaged_by_id')->nullable()->after('assigned_to_id')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('description');
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex('tickets_status_submitted_at_index');
            $table->dropForeign(['problem_category_id']);
            $table->dropForeign(['last_triaged_by_id']);
            $table->dropColumn(['problem_category_id', 'last_triaged_by_id', 'rejection_reason']);
        });
    }
};
