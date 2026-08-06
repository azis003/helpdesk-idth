<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->text('solution')->nullable()->after('description');
            $table->timestamp('confirmation_started_at')->nullable()->after('solution');
            $table->timestamp('confirmation_due_at')->nullable()->after('confirmation_started_at')->index();
            $table->timestamp('closed_at')->nullable()->after('confirmation_due_at')->index();
            $table->string('closed_reason', 40)->nullable()->after('closed_at');
            $table->unsignedSmallInteger('reopen_count')->default(0)->after('closed_reason');
            $table->unsignedSmallInteger('sla_cycle')->default(1)->after('reopen_count');
            $table->boolean('sla_compliant')->nullable()->after('sla_cycle');
            $table->unsignedInteger('sla_elapsed_working_minutes')->nullable()->after('sla_compliant');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex('tickets_confirmation_due_at_index');
            $table->dropIndex('tickets_closed_at_index');
            $table->dropColumn([
                'solution',
                'confirmation_started_at',
                'confirmation_due_at',
                'closed_at',
                'closed_reason',
                'reopen_count',
                'sla_cycle',
                'sla_compliant',
                'sla_elapsed_working_minutes',
            ]);
        });
    }
};
