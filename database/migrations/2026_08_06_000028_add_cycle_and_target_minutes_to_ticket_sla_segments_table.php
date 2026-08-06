<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_sla_segments', function (Blueprint $table): void {
            $table->unsignedSmallInteger('cycle')->default(1)->after('ticket_id');
            $table->unsignedInteger('target_working_minutes')->nullable()->after('target_working_days');
            $table->index(['ticket_id', 'cycle', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ticket_sla_segments', function (Blueprint $table): void {
            $table->dropIndex('ticket_sla_segments_ticket_id_cycle_started_at_index');
            $table->dropColumn(['cycle', 'target_working_minutes']);
        });
    }
};
